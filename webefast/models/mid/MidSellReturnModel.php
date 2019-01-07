<?php

require_model('mid/MidAbs');

class MidSellReturnModel extends MidAbs {
    /*
     * 获取主单信息
     */

    function get_order_info($record_code) {

        $sql = "select * from oms_sell_return where sell_return_code=:record_code ";
        $sql_values = array(
            ':record_code' => $record_code,
        );
        $data = $this->db->get_row($sql, $sql_values);
        if (empty($data)) {
            return $this->format_ret(-1, '', '未找到单据数据');
        }
        $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_return_decrypt_info($data['sell_return_code']);
        $data = array_merge($data, $record_decrypt_info);
        return $this->format_ret(1, $data);
    }

    /*
     * 获取明细信息
     */

    function get_order_detail($record_code) {
        $sql = "select * from oms_sell_return_detail where sell_return_code=:record_code ";
        $sql_values = array(
            ':record_code' => $record_code,
        );
        $data = $this->db->get_all($sql, $sql_values);
        if (empty($data)) {
            return $this->format_ret(-1, '', '未找到单据数据');
        }
        return $this->format_ret(1, $data);
    }

    /*
     * 获取中间信息
     */

    function get_mid_data($record_code, $base_info = array()) {
        $ret = $this->get_order_info($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $mid_data = array(
            'record_code' => $ret['data']['sell_return_code'],
            'express_code' => $ret['data']['return_express_code'],
            'express_no' => $ret['data']['return_express_no'],
            'efast_store_code' => $ret['data']['store_code'],
            'deal_code' => $ret['data']['deal_code_list'],
        );

        $mid_data = array_merge($base_info, $mid_data);
        return $this->format_ret(1, $mid_data);
    }

    function order_shipping(&$order_info) {

        $sell_return_code = $order_info['record_code'];
        $sql = "select return_shipping_status,deal_code,store_code from oms_sell_return where sell_return_code = :sell_return_code";
        $ret_data = ctx()->db->get_row($sql, array(':sell_return_code' => $sell_return_code));
        $shipping_status = $ret_data['return_shipping_status'];

        if ($shipping_status == 1) {
            return $this->format_ret(1);
        }

        $record_time = ($order_info['order_time'] == 0) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $order_info['order_time']);

        $sql = "update oms_sell_return set return_express_code = :express_code,return_express_no = :express_no where sell_return_code = :sell_return_code";
        $sql_values = array(':express_code' => $order_info['express_code'], ':express_no' => $order_info['express_no'], ':sell_return_code' => $sell_return_code);
        ctx()->db->query($sql, $sql_values);

        $ret = load_model("oms/SellReturnOptModel")->opt_return_shipping($sell_return_code, array('receive_time' => $record_time), 1);
        return $ret;
    }

    function set_mid_order_detail($record_code, $api_product, $api_data, $is_end = 1) {
        $order_detail = $this->get_order_detail($record_code);
        if (empty($order_detail['data'])) {
            return $this->format_ret(-10002, '', '找不到对应单据');
        }
        $order_detail = $order_detail['data'];
        $record_type = 'sell_return';
        $mid_order_detail = array();
        foreach ($order_detail as $val) {
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], array('barcode'));
            if (isset($mid_order_detail[$sku_info['barcode']])) {
                $mid_order_detail[$sku_info['barcode']]['sys_sl'] +=$val['num'];
            } else {
                $mid_order_detail[$sku_info['barcode']] = array(
                    'record_code' => $record_code,
                    'record_type' => 'sell_record',
                    'api_product' => $api_product,
                    'barcode' => $sku_info['barcode'],
                    'sys_sl' => $val['num'],
                    'api_sl' => 0,
                    'sys_sl' => $val['num'],
                    'record_code' => $record_code,
                );
            }
        }
        $no_find_barcode = array();
        foreach ($api_data['detail'] as $val) {
            if (!isset($mid_order_detail[$val['barcode']])) {
                $no_find_barcode[] = $val['barcode'];
            } else {
                $mid_order_detail[$val['barcode']]['api_sl'] +=$val['num'];
            }
        }
        if (!empty($no_find_barcode)) {
            return $this->format_ret(-10002, '', '无效商品条码' . implode(',', $no_find_barcode));
        }

        $mid_detail_data = array_values($mid_order_detail);
        $update_str = " api_sl = VALUES(api_sl)+api_sl ";
        $this->insert_multi_duplicate('mid_order_detail', $mid_detail_data, $update_str);
        if ($is_end === 1) {
            load_model('mid/MidBaseModel')->set_process_flow_end($record_code, $record_type, $api_product, $api_data);
        }
        return $this->format_ret(1);
    }

}
