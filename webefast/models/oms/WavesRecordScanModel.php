<?php
/**
 * 波次单扫描验货模型类
 * 2017/04/25
 * @author zwj
 */
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class WavesRecordScanModel extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'oms_waves_record';
    protected $table_record = 'oms_deliver_record';
    protected $table_record_detail = 'oms_deliver_record_detail';

    //获取扫描提示音
    public function get_sound(){
        $res = array('success' => '', 'error' => '');
        $sql = "select value from sys_params where param_code = :param_code";
        $sql_val = array(':param_code' => 'scan_voice_ok');
        $res['success'] = $this->db->get_value($sql, $sql_val);
        $sql_val = array(':param_code' => 'scan_voice_fail');
        $res['error'] = $this->db->get_value($sql, $sql_val);
        return $res;
    }

    public function check_waves($waves_record){
        $sql = "SELECT * FROM {$this->table} WHERE record_code = :waves_record";
        $sql_val = array(':waves_record' => $waves_record);
        $waves = $this->db->get_row($sql, $sql_val);
        if (empty($waves)) {
            return $this->format_ret(-1, '', "波次单：{$waves_record}不存在！");
        }
        if($waves['is_accept'] == 0){
            return $this->format_ret(-1, '', "波次单：{$waves_record}未验收！");
        }
        if($waves['is_cancel'] == 1) {
            return $this->format_ret(-1, '', "波次单：{$waves_record}已取消！");
        }
        if ($waves['is_deliver'] == 1){
            return $this->format_ret(-1, '', "波次单：{$waves_record}已发货！");
        }
        if ($waves['sell_num_type'] != 1){
            return $this->format_ret(-1, '', "波次单：{$waves_record}不是一单一品！");
        }
        //获取已发货订单数
        $sql = "SELECT COUNT(*) FROM {$this->table_record} WHERE is_deliver = :is_deliver AND waves_record_id = :waves_record_id";
        $sql_shipped = array(':waves_record_id' => $waves['waves_record_id'], ':is_deliver' => '1');
        $shipped_num = $this->db->get_value($sql, $sql_shipped);
        //获取有效订单数
        $sql = "SELECT COUNT(*) FROM {$this->table_record} WHERE waves_record_id = :waves_record_id AND is_cancel = :is_cancel";
        $sql_valid = array(':waves_record_id' => $waves['waves_record_id'], ':is_cancel' => '0');
        $valid_num = $this->db->get_value($sql, $sql_valid);
        //获取退单数
        $sql = "SELECT COUNT(tid) FROM api_refund WHERE tid IN (SELECT deal_code_list FROM {$this->table_record} WHERE waves_record_id = :waves_record_id)";
        $sql_refund = array(':waves_record_id' => $waves['waves_record_id']);
        $refund_num = $this->db->get_value($sql, $sql_refund);

        $ret['shipped_num'] = $shipped_num;
        $ret['valid_num'] = $valid_num - $refund_num;
        $ret['refund_num'] = $refund_num;
        return $this->format_ret(1, $ret);
    }

    public function check_barcode($waves_record, $goods_barcode){
        $sql = "SELECT rd.deliver_record_id, rd.sell_record_code, gs.barcode FROM {$this->table_record_detail} rd INNER JOIN {$this->table_record} r ON rd.deliver_record_id = r.deliver_record_id INNER JOIN {$this->table} w ON r.waves_record_id = w.waves_record_id LEFT JOIN goods_sku AS gs ON gs.sku = rd.sku  WHERE w.record_code = :waves_record AND gs.barcode = :goods_barcode AND r.is_deliver = :is_deliver AND r.is_cancel = :is_cancel ";
        $sql_val = array(':waves_record' => $waves_record, ':goods_barcode' => $goods_barcode, ':is_deliver' => 0,  ':is_cancel' => 0,);
        $delivery_detail = $this->db->get_row($sql, $sql_val);
        if (empty($delivery_detail)) {
            return $this->format_ret(-1, '', "波次单中：{$waves_record}不存在扫描条码：{$goods_barcode}！");
        }

        //获取订单信息
        $sql = "SELECT tr.*, o.shipping_status, o.order_status FROM {$this->table_record} tr, oms_sell_record o WHERE tr.sell_record_code = o.sell_record_code AND tr.deliver_record_id = :deliver_record_id AND tr.is_cancel = :is_cancel AND tr.is_deliver = :is_deliver ";
        $sql_record = array(':deliver_record_id' => $delivery_detail['deliver_record_id'], ':is_cancel' => 0, ':is_deliver' => 0);
        $record_info = $this->db->get_row($sql, $sql_record);
        if ($record_info['shipping_status'] == 4) {
            return $this->format_ret(-1, '', "订单：{$record_info['sell_record_code']}已发货！");
        }
        if ($record_info['order_status'] == 3) {
            return $this->format_ret(-1, '', "订单：{$record_info['sell_record_code']}已作废！");
        }
        $record_info['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $record_info['express_code']));

        //获取订单明细信息
        $sql = "SELECT * FROM {$this->table_record_detail} WHERE deliver_record_id = :deliver_record_id ";
        $sql_detail = array(':deliver_record_id' => $delivery_detail['deliver_record_id']);
        $detail_info = $this->db->get_all($sql, $sql_detail);
        foreach ($detail_info as $key => &$val){
            $sql = "SELECT SUM(num) AS sum_num, SUM(scan_num) AS sum_scan_num FROM oms_deliver_record_detail WHERE deliver_record_id = :deliver_record_id ";
            $sql_val = array(':deliver_record_id' => $val['deliver_record_id']);
            $num_info = $this->db->get_row($sql, $sql_val);
            $val['sum_num'] = $num_info['sum_num'];
            $val['sum_scan_num'] = $num_info['sum_scan_num'];
            $key_arr = array('goods_name','spec1_code','spec1_name','spec2_code','spec2_name','barcode');
            $sku_info =  load_model('goods/SkuCModel')->get_sku_info($val['sku'],$key_arr);
            $val = array_merge($val,$sku_info);
            $detail_info[$key] = $val;
        }

        $ret['record_info'] = $record_info;
        $ret['detail_info'] = $detail_info;
        return $this->format_ret(1, $ret);
    }

    public function cancel_express_no($deliver_record_id)
    {
        $sql = "SELECT express_no, sell_record_code, waves_record_id FROM {$this->table_record} WHERE deliver_record_id = :deliver_record_id ";
        $sql_val = array(':deliver_record_id' => $deliver_record_id);
        $delivery_info = $this->db->get_row($sql, $sql_val);
        if($delivery_info){
            //取消云栈单号
            load_model('oms/DeliverRecordModel')->cancel_express_no_all($delivery_info['sell_record_code'], $delivery_info['waves_record_id']);

            $sql = "UPDATE {$this->table_record} SET express_no = '' WHERE deliver_record_id = '{$deliver_record_id}' ";
            $this->db->query($sql);
            $sql = "UPDATE oms_sell_record s, {$this->table_record} d SET s.express_no = '' WHERE s.sell_record_code = d.sell_record_code AND d.deliver_record_id = '{$deliver_record_id}' ";
            $this->db->query($sql);

            return $this->format_ret(1, '', '清除物流单号成功');
        } else {
            return $this->format_ret(1, '', '无物流单号');
        }
    }

    public function get_express_no($deliver_record_id)
    {
        $sql = "SELECT express_no FROM {$this->table_record} WHERE deliver_record_id = :deliver_record_id ";
        $sql_val = array(':deliver_record_id' => $deliver_record_id);
        $express_no = $this->db->get_value($sql, $sql_val);

        return $this->format_ret(1, $express_no, '获取物流单号成功');
    }

    public function get_is_shipped($waves_record_id)
    {
        $sql = "SELECT is_deliver FROM {$this->table} WHERE waves_record_id = :waves_record_id";
        $sql_val = array(':waves_record_id' => $waves_record_id);
        $is_shipped = $this->db->get_value($sql, $sql_val);

        return $this->format_ret(1, $is_shipped, '获取发货状态成功');
    }
}
