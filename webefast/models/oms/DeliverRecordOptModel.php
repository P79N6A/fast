<?php

require_model('tb/TbModel');

/**
 * 订单发货操作业务
 * @author WMH
 */
class DeliverRecordOptModel extends TbModel {

    protected $table = 'oms_deliver_record';
    protected $detail_table = 'oms_deliver_record_detail';

    /**
     * 扫描快递单号校验
     * @param string $express_no 快递单号
     * @return array 订单数据
     */
    public function scan_express_check($express_no) {
        //校验订单状态
        $sql = "SELECT dr.deliver_record_id,dr.sell_record_code,dr.deal_code,dr.deal_code_list,dr.waves_record_id,dr.sale_channel_code,
                dr.store_code,dr.shop_code,dr.goods_num,dr.sku_num,dr.record_time,dr.express_code,dr.express_no,dr.pay_type,
                dr.is_deliver,dr.is_cancel,sr.shipping_status,sr.order_status
                FROM {$this->table} AS dr INNER JOIN oms_sell_record AS sr ON dr.sell_record_code=sr.sell_record_code
                WHERE dr.express_no = :express_no AND dr.is_cancel = 0";
        $record = $this->db->get_all($sql, array('express_no' => $express_no));
        if (empty($record)) {
            return $this->format_ret(-1, array(), '快递单号未匹配到订单');
        }
        if (count($record) > 1) {
            return $this->format_ret(-1, array(), '存在相同物流单号的订单');
        }
        $record = $record[0];

        if ($record['order_status'] == 3 || $record['is_cancel'] != 0) {
            return $this->format_ret(-1, array(), '订单已作废');
        }
        if ($record['is_deliver'] == 1) {
            return $this->format_ret(-1, array(), '订单已发货');
        }
        if ($record['shipping_status'] != 3) {
            return $this->format_ret(-1, array(), '订单未完成拣货');
        }
        $status = load_model('mid/MidBaseModel')->check_is_mid('scan', 'sell_record', $record['store_code']);
        if ($status !== FALSE) {
            return $this->format_ret(-1, array(), '仓库对接' . $status . '，不允许手工发货');
        }

        $sql = "SELECT COUNT(1) FROM oms_sell_return WHERE return_order_status<>3 AND sell_record_code = :sell_record_code";
        $return_check = $this->db->get_value($sql, array(':sell_record_code' => $record['sell_record_code']));
        if ($return_check > 0) {
            return $this->format_ret(-1, array(), '订单已发生退货');
        }

        //校验波次单
        if (!empty($record['waves_record_id'])) {
            $sql = "SELECT waves_record_id,record_code,is_accept,is_cancel FROM oms_waves_record WHERE waves_record_id = :waves_record_id";
            $wave_data = $this->db->get_row($sql, array('waves_record_id' => $record['waves_record_id']));
            if (empty($wave_data)) {
                return $this->format_ret(-1, array(), '订单对应的波次单不存在');
            }
            if ($wave_data['is_accept'] != 1) {
                return $this->format_ret(-1, array(), '订单对应的波次单未验收');
            }
            if ($wave_data['is_cancel'] == 1) {
                return $this->format_ret(-1, array(), '订单对应的波次单已作废');
            }
        }
        //校验明细
        $sql = "SELECT COUNT(1) FROM {$this->detail_table} WHERE deliver_record_id = :deliver_record_id";
        $detail_count = $this->db->get_value($sql, array('deliver_record_id' => $record['deliver_record_id']));
        if ($detail_count === FALSE) {
            return $this->format_ret(-1, array(), '订单明细为空');
        }

        $record['express_name'] = $this->db->get_value(
                'SELECT express_name FROM base_express WHERE express_code = :code', array(':code' => $record['express_code'])
        );

        return $this->format_ret(1, $record, '快递扫描校验成功');
    }

    /**
     * 扫描条码校验
     * @param array $record 订单信息
     * @param string $barcode 条码
     * @return array 订单数据
     */
    public function scan_barcode_check(&$record, $barcode) {
        //扫描条码识别
        $ret_sku = load_model('oms/DeliverPackageOptModel')->get_barcode_ident($record['sell_record_code'], $barcode);
        if ($ret_sku['status'] < 1) {
            return $ret_sku;
        }

        $this->begin_trans();
        //添加唯一码跟踪中间表记录
        if ($ret_sku['data']['unique_flag'] == 1) {
            $unique_data = array('sell_record_code' => $record['sell_record_code'], 'unique_code' => $barcode, 'barcode_type' => 'unique_code');
            $ret = load_model('oms/UniqueCodeScanTemporaryLogModel')->insert($unique_data);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '添加唯一码跟踪记录失败');
            }
        }

        $sku = $ret_sku['data']['sku'];
        $sql = "SELECT deliver_record_detail_id,sku,num,scan_num FROM {$this->detail_table} 
                WHERE deliver_record_id=:id AND sku=:sku ORDER BY is_gift ASC";
        $deliver_detail = $this->db->get_all($sql, array(':id' => $record['deliver_record_id'], ':sku' => $sku));
        if (empty($deliver_detail)) {
            $this->rollback();
            return $this->format_ret(-1, '', '发货订单不存在此商品');
        }

        //扫描数量校验，更新发货单扫描数量
        $deliver_detail_id = 0;
        $up_scan_num = 0;
        foreach ($deliver_detail as $row) {
            if ($row['num'] == $row['scan_num']) {
                continue;
            }
            $deliver_detail_id = $row['deliver_record_detail_id'];
            $up_scan_num = $row['scan_num'] + 1;
            break;
        }
        if ($deliver_detail_id == 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '该商品已扫描完毕');
        }

        $ret = $this->update_exp($this->detail_table, array('scan_num' => $up_scan_num), array('deliver_record_detail_id' => $deliver_detail_id));
        if ($ret['status'] < 1 || $this->affected_rows() != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '更新发货数据失败');
        }

        $this->commit();
        
        $revert_data = array(
            'sku' => $sku
        );
        return $this->format_ret(1, $revert_data, '条码扫描校验成功');
    }

    /**
     * 清除扫描记录
     * @param array $record 订单信息
     * @return array
     */
    function clear_scan_record(&$record) {
        $sql = "DELETE FROM unique_code_scan_temporary_log WHERE barcode_type = 'unique_code' AND sell_record_code = :code";
        $ret = $this->query($sql, array(':code' => $record['sell_record_code']));
        $status = 1;
        $msg = '发货清楚扫描记录失败';
        if ($ret['status'] < 1) {
            $status = -1;
        }
        if ($status == 1) {
            $sql = 'UPDATE oms_deliver_record_detail SET scan_num = 0 WHERE deliver_record_id =:id';
            $ret = $this->query($sql, array(':id' => $record['deliver_record_id']));
            if ($ret['status'] < 1) {
                $status = -1;
            } else {
                $msg = '发货清除扫描记录成功';
            }
        }
        return $this->format_ret($status, array(), $msg);
    }

    /**
     * 获取发货单明细
     * @param int $deliver_record_id 发货单ID
     * @return array 明细数据
     */
    public function get_deliver_detail($deliver_record_id) {
        $sql = "SELECT deliver_record_detail_id,deliver_record_id,sell_record_code,deal_code,waves_record_id,goods_code,
                spec1_code,spec2_code,sku,num,scan_num,is_gift,picking_num 
                FROM {$this->detail_table} WHERE deliver_record_id = :id";
        return $this->db->get_all($sql, array(':id' => $deliver_record_id));
    }

    /**
     * 获取发货明细商品数量和扫描数量
     * @param int $deliver_record_id 发货单ID
     * @return array
     */
    public function get_detail_num($deliver_record_id) {
        $sql = "SELECT SUM(num) AS goods_num,SUM(scan_num) AS scan_num FROM {$this->detail_table} WHERE deliver_record_id = :id";
        return $this->db->get_row($sql, array(':id' => $deliver_record_id));
    }

    /**
     * 校验是否存在退单
     * @param array $data 发货订单数据
     * @return array
     */
    public function check_refund($data) {
        $sql_values = array();
        $deal_code_arr = array_column($data['detail'], 'deal_code');
        $deal_code_str = $this->arr_to_in_sql_value($deal_code_arr, 'tid', $sql_values);
        $sql = "SELECT tid FROM api_refund WHERE tid IN({$deal_code_str}) AND status = 1 and is_change = 0";
        $tid_arr = $this->db->get_all_col($sql, $sql_values);
        if (!empty($tid_arr)) {
            $tid_list = join(',', $tid_arr);
            return $this->format_ret(-1, '', "交易号{$tid_list}存在未处理的退单");
        }
        //淘宝货到付款关闭状态校验拦截
        $ret = load_model('oms/SellRecordOptModel')->check_trade_closed($data['record']);
        if ($ret['status'] != 1) {
            return $ret;
        }
        return $this->format_ret(1);
    }

}
