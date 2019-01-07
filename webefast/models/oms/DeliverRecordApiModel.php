<?php

require_model('tb/TbModel');
require_lang('api');

/**
 * 发货单业务接口
 * @author WMH
 */
class DeliverRecordApiModel extends TbModel {

    /**
     * 快递单号扫描发货
     * @author wmh
     * @date 2017-06-15
     * @param array $param 接口参数
     * @return array 操作结果
     */
    public function api_record_express_scan($param) {
        $key_required = array(
            's' => array('express_no', 'opt_user_code'),
        );

        $r_required = array();
        $ret_required = valid_assign_array($param, $key_required, $r_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
        $express_no = $r_required['express_no'];
        $user_code = $r_required['opt_user_code'];

        $user_name = $this->get_user_name_by_code($user_code);
        if (empty($user_name)) {
            return $this->format_ret(-1, array('opt_user_code' => $user_code), '用户不存在');
        }

        require_model('oms/DeliverRecordOptModel');
        $obj_deliver = new DeliverRecordOptModel();
        //校验发货单
        $ret_record = $obj_deliver->scan_express_check($express_no);
        if ($ret_record['status'] < 1) {
            $ret_record['data'] = array('express_no' => $express_no);
            return $ret_record;
        }
        $record = $ret_record['data'];
        //获取明细
        $detail = $obj_deliver->get_deliver_detail($record['deliver_record_id']);

        //校验退单
        $ret = $obj_deliver->check_refund(array('detail' => $detail, 'record' => $record));
        if ($ret['status'] < 1) {
            $ret['data'] = array('express_no' => $express_no);
            return $ret;
        }

        $log_data = array(
            'action_name' => '扫描出库',
            'action_note' => 'API-扫描物流单号：' . $express_no,
            'user_code' => $user_code,
            'user_name' => $user_name,
        );
        load_model('oms/SellRecordActionModel')->api_add_action($record['sell_record_code'], $log_data);

        $scan_num = array_sum(array_column($detail, 'scan_num'));

        $revert_data = array(
            'sell_record_code' => $record['sell_record_code'],
            'express_code' => $record['express_code'],
            'express_name' => $record['express_name'],
            'express_no' => $record['express_no'],
            'goods_num' => $record['goods_num'],
            'scan_num' => $scan_num,
        );

        return $this->format_ret(1, $revert_data, '扫描成功');
    }

    /**
     * 扫描条码记录
     * @author wmh
     * @date 2017-06-15
     * @param array $param 接口参数
     * @return array 操作结果
     */
    public function api_record_barcode_scan($param) {
        $key_required = array(
            's' => array('express_no', 'barcode', 'opt_user_code'),
        );

        $r_required = array();
        $ret_required = valid_assign_array($param, $key_required, $r_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
        $express_no = $r_required['express_no'];
        $barcode = $r_required['barcode'];
        $user_code = $r_required['opt_user_code'];
        unset($param, $r_required);
        $user_name = $this->get_user_name_by_code($user_code);
        if (empty($user_name)) {
            return $this->format_ret(-1, array('opt_user_code' => $user_code), '用户不存在');
        }

        require_model('oms/DeliverRecordOptModel');
        $obj_deliver = new DeliverRecordOptModel();
        //校验发货单
        $ret_record = $obj_deliver->scan_express_check($express_no);
        if ($ret_record['status'] < 1) {
            $ret_record['data'] = array('express_no' => $express_no);
            return $ret_record;
        }
        $record = $ret_record['data'];

        $revert_data = array();

        $ret_barcode = $obj_deliver->scan_barcode_check($record, $barcode);
        if ($ret_barcode['status'] < 1) {
            return $ret_barcode;
        }

        $log_data = array(
            'action_name' => '扫描出库',
            'action_note' => 'API-扫描条码：' . $barcode,
            'user_code' => $user_code,
            'user_name' => $user_name,
        );
        load_model('oms/SellRecordActionModel')->api_add_action($record['sell_record_code'], $log_data);

        $revert_data = $this->get_data_by_sku($ret_barcode['data']['sku']);
        return $this->format_ret(1, $revert_data, '扫描成功');
    }

    private function get_user_name_by_code($user_code) {
        $sql = 'SELECT user_name FROM sys_user WHERE user_code=:user_code';
        return $this->db->get_value($sql, array(':user_code' => $user_code));
    }

    private function get_data_by_sku($sku) {
        $sql = "SELECT bg.goods_code,bg.goods_name,CONCAT_WS('；',gs.spec1_name,gs.spec2_name) AS goods_spec,gs.barcode 
                FROM goods_sku AS gs INNER JOIN base_goods AS bg ON gs.goods_code = bg.goods_code
                WHERE gs.sku = :sku";
        return $this->db->get_row($sql, array(':sku' => $sku));
    }

}
