<?php

require_model("wms/WmsInvModel");

class QimenInvModel extends WmsInvModel {

    function __construct() {
        parent::__construct();
    }

    function inv_search($efast_store_code, $barcode_arr) {

        $this->get_wms_cfg($efast_store_code);
        $result = array();
        $error_arr = array();
        $method = 'taobao.qimen.inventory.query';
        $params = $this->set_params($barcode_arr);

        $ret = $this->biz_req($method, $params);

        if ($ret['data']['flag'] == 'success') {

            if (isset($ret['data']['items']['item']['itemCode'])) {
                $ret['data']['items']['item'] = array($ret['data']['items']['item']);
            }

            foreach ($ret['data']['items']['item'] as $sku_inv) {
                if (!isset($sku_inv['itemCode']) || !isset($sku_inv['quantity'])) {
                    continue;
                }
                $inv_row = array();
                if ($sku_inv['inventoryType'] == 'ZP') {
                    $inv_row = array('barcode' => $sku_inv['itemCode'], 'num' => $sku_inv['quantity']);
                    if ($this->is_compare === true) {
                        $inv_row['num'] += isset($sku_inv['lockQuantity']) ? $sku_inv['lockQuantity'] : 0;
                    }
                } else {
                    $inv_row = array('barcode' => $sku_inv['itemCode'], 'cp_num' => $sku_inv['quantity']);
                    if ($this->is_compare === true) {
                        $inv_row['num'] += isset($sku_inv['lockQuantity']) ? $sku_inv['lockQuantity'] : 0;
                    }
                }



                $result[$sku_inv['itemCode']] = isset($result[$sku_inv['itemCode']]) ? array_merge($result[$sku_inv['itemCode']], $inv_row) : $inv_row;
            }
        } else {
              $error_arr[] = !empty($ret['data']['message'])?$ret['data']['message']:$ret['message'];
        }

        if (!empty($result)) {
            $ret = $this->format_ret(1, $result);
        } else {
            $ret = $this->format_ret(-1, $result, implode(",", $error_arr));
        }
        return $ret;
    }

    function set_params($barcode_arr) {
        $data = array();
        $itemsIds = $this->get_item_ids('qimen', $this->efast_store_code, $barcode_arr);
        foreach ($barcode_arr as $barcode) {
            $data[] = array('criteria' => array(
                    'warehouseCode' => $this->wms_cfg['wms_store_code'], //warehouse_code
                    'ownerCode' => $this->wms_cfg['owner_code'],
                    'itemCode' => $barcode,
                    //  'inventoryType' =>'ZP',
                    'itemId' => isset($itemsIds[$barcode]) ? $itemsIds[$barcode] : '',
                ),
            );
        }
        return array('criteriaList' => $data);
    }

    function get_item_ids($api_product, $store_code, $barcodes = array()) {
        $sql = "select api_code,sys_code from wms_archive where type=:type AND api_product=:api_product AND wms_config_id=:wms_config_id";
        $sql_value = array(
            ':type' => 'goods_barcode',
            ':api_product' => $api_product,
            ':wms_config_id' => $this->wms_cfg['wms_config_id'],
        );
        if (!empty($barcodes)) {
            $bar_str = "'" . implode("','", $barcodes) . "'";
            $sql .= " AND sys_code IN($bar_str)";
        }
        $bar_ret = $this->db->get_all($sql, $sql_value);
        $itemids = array();
        foreach ($bar_ret as $bar_row) {
            $itemids[$bar_row['sys_code']] = $bar_row['api_code'];
        }
        return $itemids;
    }

}
