<?php

require_model("wms/WmsInvModel");

class ShunfengInvModel extends WmsInvModel {

    function __construct() {
        parent::__construct();
    }

    function inv_search($efast_store_code, $params) {
        $this->get_wms_cfg($efast_store_code);
        $new_data = array();
        foreach ($params as $key => $p){
            $i = $key%10;
            $j = floor($key/10);
            $new_data[$j][$i] = $p;
        }
        $result = array();
        $error = array();
        foreach ($new_data as $data){
            $method = 'RT_INVENTORY_QUERY_SERVICE';
            $params = $this->set_params($data);
            $ret = $this->biz_req($method, $params);
            if ($ret['status'] > 0) {//total_page
                if(isset($ret['data']['RTInventorys']['RTInventory']['Result'])){
                 //   $ret['data']['list']['item'] = array($ret['data']['list']['item']);
                   $ret['data']['RTInventorys']['RTInventory'] = array($ret['data']['RTInventorys']['RTInventory']);
                }
                foreach ($ret['data']['RTInventorys']['RTInventory'] as $sku_inv) {
                    if ($sku_inv['Result'] == 2) {//没有查询到库存信息
                        continue;
                    }
                    if($sku_inv['Header']['InventoryStatus'] == '10'){
                        $inv_row = array('barcode' => $sku_inv['Header']['SkuNo'], 'num' => $sku_inv['Header']['OnHandQty']);
                    } else {
                        $inv_row = array('barcode' => $sku_inv['Header']['SkuNo'], 'cp_num' => $sku_inv['Header']['OnHandQty']);
                    }
                    
                    $result[$sku_inv['Header']['SkuNo']] = (isset($result[$sku_inv['Header']['SkuNo']]))?array_merge( $result[$sku_inv['Header']['SkuNo']],$inv_row):$inv_row;
                }
            } else {
                $error[] = $ret['data'];
            }
        }
        if(!empty($result)){
            return $this->format_ret(1,$result);
        } else {
            return $this->format_ret(1,$error);
        }
        
    }

    
    function set_params($params) {
        $data = array(
            'CompanyCode' => $this->wms_cfg['company'],
            'WarehouseCode' => $this->wms_cfg['wms_store_code'],
        );
        foreach ($params as $param){
            $data['RTInventorys'][]['RTInventory']['SkuNo'] = $param; 
        }
        return $data;
    }


    
    
}
