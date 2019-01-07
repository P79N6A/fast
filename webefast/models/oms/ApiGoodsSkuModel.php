<?php

/**
 * 淘宝商品sku相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');

require_lib('util/taobao_util', true);

class ApiGoodsSkuModel extends TbModel {

    function __construct() {
        parent::__construct('api_goods_sku', 'api_goods_sku_id');
    }

    /**
     * @param api_goods_sku_id
     * @param array $filter
     * @return array
     */
    function get_list_by_item_id($goods_from_id, $filter = array(), $is_allow_sync_inv_value = '') {

        $select = '*';
        $sql_main = " FROM {$this->table} WHERE goods_from_id='{$goods_from_id}'";
        if (isset($is_allow_sync_inv_value) && $is_allow_sync_inv_value != 'all') {
            $sql_main .= " AND is_allow_sync_inv = {$is_allow_sync_inv_value}";
        }
        $data = $this->get_page_from_sql($filter, $sql_main, array(), $select);

        foreach ($data['data'] as &$value) {
            $value['sku_status'] = ($value['status'] == 1) ? 0 : 1;
            if ($value['source'] == 'yamaxun') {
                $value['goods_barcode_html'] = "<div class='goods_barcode' onclick=update_goods_barcode(" . $value['api_goods_sku_id'] . ")  id='" . $value['api_goods_sku_id'] . "'><span style='width:100%;height:5%;'>" . $value['goods_barcode'] . "</span></div>";
            } else {
                $value['goods_barcode_html'] = $value['goods_barcode'];
            }
            $value['presell_status'] = $value['sale_mode'] == 'presale' ? 1 : 0;
            $value['presell_end_time'] = empty($value['presell_end_time']) ? '' : date('Y-m-d H:i:s', $value['presell_end_time']);
            $value['last_sync_inv_num'] = ($value['last_sync_inv_num'] == -1) ? '' : $value['last_sync_inv_num'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

}
