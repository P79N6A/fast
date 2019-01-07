<?php

require_model('tb/TbModel');

/**
 * 奇门官方接口业务
 *
 * @author WMH
 */
class QimenOmsModel extends TbModel {

    public function taobao_qianniu_cloudkefu_address_self_modify($param) {
        $sql = 'SELECT * FROM oms_sell_record WHERE sell_record_code=:code';
        $record = $this->db->get_all($sql, [':code' => $param['bizOrderId']]);
        if (empty($record)) {
            return $this->format_ret(-1, '', '订单不存在');
        }
        if ($record['order_status'] == 3) {
            return $this->format_ret(-1, '', '订单已取消，不能修改地址');
        }
        if ($record['shipping_status'] > 1) {
            return $this->format_ret(-1, '', '订单已发货，不能修改地址');
        }

        $modifiedAddress = json_decode($param['modifiedAddress'], TRUE);
        if (empty($modifiedAddress)) {
            return $this->format_ret(-1, '', '地址信息错误');
        }
        $api_addr = [];
        $api_addr['source'] = 'taobao';
        $api_addr['receiver_province'] = str_replace(' ', '', $modifiedAddress['province']);
        $api_addr['receiver_city'] = str_replace(' ', '', $modifiedAddress['city']);
        $api_addr['receiver_district'] = str_replace(' ', '', $modifiedAddress['area']);
        $api_addr['receiver_addr'] = str_replace(' ', '', $modifiedAddress['addressDetail']);
        $api_addr['receiver_address'] = $modifiedAddress['province'] . $modifiedAddress['city'] . $modifiedAddress['area'] . $modifiedAddress['addressDetail'];

        $ret_addr = load_model('oms/trans_order/AddrCommModel')->match_addr($api_addr);
        if ($ret_addr['status'] < 1) {
            return $this->format_ret(-1, '', $ret_addr['message']);
        }
        $this->begin_trans();
        $ret = $this->update_exp('oms_sell_record', $ret_addr['data'], ['sell_record_code' => $param['bizOrderId']]);
        if ($ret['status'] < 1 || $this->affected_rows() <> 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '地址修改更新出错');
        }
        $this->commit();
        return $this->format_ret(1);
    }

}
