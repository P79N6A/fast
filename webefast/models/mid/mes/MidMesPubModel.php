<?php

require_model('tb/TbModel');

class MidMesPubModel extends TbModel {

    function mes_shipping($filer) {

        if (empty($filer['record_code']) || empty($filer['record_type'])) {
            return $this->format_ret(-10001, '', '单据类型或变化异常');
        }

        $detail_data = json_decode($filer['detail'], true);

        if (empty($detail_data)) {
            return $this->format_ret(-10001, '', '回传明细异常');
        }

        $api_data['detail'] = array();
        foreach ($detail_data as $val) {
            $api_data['detail'][] = array(
                'barcode' => $val['item_code'],
                'num' => $val['num'],
            );
        }
        if ($filer['record_type'] == 'sell_record') {
            $ret = load_model('mid/MidSellRecordModel')->set_mid_order_detail($filer['record_code'], 'mes', $api_data);
        } else {
            $ret = load_model('mid/MidSellReturnModel')->set_mid_order_detail($filer['record_code'], 'mes', $api_data);
        }
        return $ret;
    }

    function get_goods_unit($goods_code) {
        static $goods_arr = null;
        if (!isset($goods_arr[$goods_code])) {
            $sql = "select property_val1 from base_property where property_type=:property_type AND property_val_code=:property_val_code ";
            $sql_values = array(
                ':property_type' => 'goods',
                ':property_val_code' => $goods_code,
            );
            $goods_arr[$goods_code] = $this->db->get_value($sql, $sql_values);
        }
        return $goods_arr[$goods_code];
    }

    function get_shop_name($shop_code) {
        static $shop_arr = null;
        if (!isset($shop_arr[$shop_code])) {
            $sql = "select shop_name from base_shop where shop_code=:shop_code ";
             $sql_values = array(
                ':shop_code' =>$shop_code,

            );
            $shop_arr[$shop_code] = $this->db->get_value($sql,$sql_values);
        }
        return $shop_arr[$shop_code];
    }

}
