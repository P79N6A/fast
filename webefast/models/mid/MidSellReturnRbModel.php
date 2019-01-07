<?php

require_model('mid/MidAbs');

class MidSellReturnRbModel extends MidAbs {

    function get_order_info($record_code) {
        $sql = "SELECT * FROM bsapi_trade WHERE record_code=:record_code AND record_type='sell_return'";
        $sql_values = array(':record_code' => $record_code);
        $data = $this->db->get_row($sql, $sql_values);
        if (empty($data)) {
            return $this->format_ret(-1, '', '未找到单据数据');
        }
        return $this->format_ret(1, $data);
    }

    function get_order_detail($record_code) {
        $sql = "SELECT * FROM bsapi_trade_detail WHERE record_code=:record_code ";
        $sql_values = array(':record_code' => $record_code);
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
        
    }

    function order_shipping(&$order_info) {
        
    }

    function check_detail($order_info) {
        //存在缺陷，同一个环境对应多个流程
        $sql = "select * from mid_order_detail where record_code=:record_code AND record_type=:record_type AND status=:status";
        $sql_values = array(
            ':record_code' => $order_info['record_code'],
            ':record_type' => $order_info['record_type'],
            ':status' => 0,
        );
        $data = $this->db->get_all($sql, $sql_values);
        if (empty($data)) {
            $this->format_ret(-1, '', '收到可发货明细为空！');
        }
        $error_sku = array();
        foreach ($data as $val) {
            if ($val['api_sl'] != $val['api_sl']) {
                $error_sku[] = $val['barcode'] . "收到明细为" . $val['api_sl'] . ",应该收到明细" . $val['sys_sl'];
            }
        }
        if (!empty($error_sku)) {
            return $this->format_ret(-1, '', implode(',', $error_sku));
        }
        return $this->format_ret(1);
    }

    function set_mid_order_detail($record_code, $api_product, $api_data, $is_end = 1) {
        $order_detail = $this->get_order_detail($record_code);
        if (empty($order_detail['data'])) {
            return $this->format_ret(-10002, '', '找不到对应单据');
        }
        $order_detail = $order_detail['data'];
        $record_type = 'sell_record';
        $mid_order_detail = array();
        foreach ($order_detail as $val) {
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], array('barcode'));
            if (isset($mid_order_detail[$sku_info['barcode']])) {
                $mid_order_detail[$sku_info['barcode']]['sys_sl'] += $val['num'];
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
                $mid_order_detail[$val['barcode']]['api_sl'] += $val['num'];
            }
        }
        if (!empty($no_find_barcode)) {
            return $this->format_ret(-10002, '', '无效商品条码' . implode(',', $no_find_barcode));
        }

        $mid_detail_data = array_values($mid_order_detail);
        $update_str = " api_sl = VALUES(api_sl)+api_sl ";
        $this->insert_multi_duplicate('mid_order_detail', $mid_detail_data, $update_str);
        if ($is_end == 1) {
            load_model('mid/MidBaseModel')->set_process_flow_end($record_code, $record_type, $api_product, $api_data);
        }
        return $this->format_ret(1);
    }

}
