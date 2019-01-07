<?php

require_model("wms/WmsInvModel");

class BswmsInvModel extends WmsInvModel {

    function __construct() {
        parent::__construct();
    }

    function inv_search($efast_store_code, $params) {
        $this->get_wms_cfg($efast_store_code);
        $new_data = array();

        $result = array();
        $error = array();
        $method = 'GetProductInventory';
        $params = $this->set_params($params);
        $ret = $this->biz_req($method, $params);
        
        if ($ret['status'] > 0) {//total_page
            if($ret['data']['ProductInventory']['flag'] == 'SUCCESS'){
                $products = $ret['data']['ProductInventory']['products']['product'];
                foreach ($product as $product){
                    if(!empty($product['normalQuantity'])){
                        $inv_row = array('barcode' => $product['skuCode'], 'num' => $product['normalQuantity'],'cp_num' => 'defectiveQuantity');
                    }
                    $result[$product['skuCode']] = (isset($result[$product['skuCode']])) ? array_merge($result[$product['skuCode']], $inv_row) : $inv_row;
                }
            } else {
                $error[] = $ret['data']['ProductInventory']['note'];
            }
        } else {
            $error[] = $ret['data'];
        }
        if (!empty($result)) {
            return $this->format_ret(1, $result);
        } else {
            return $this->format_ret(1, $error);
        }
    }

    function set_params($params) {
        $data = array(
            'customerCode' => $this->wms_cfg['customerCode'],
            'warehouseCode' => $this->wms_cfg['wms_store_code'],
        );
        foreach ($params as $param) {
            $data['products'][]['product']['skuCode'] = $param;
        }
        return $data;
    }

}
