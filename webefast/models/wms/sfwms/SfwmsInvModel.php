<?php

require_model("wms/WmsInvModel");

class SfwmsInvModel extends WmsInvModel {

    function __construct() {
        parent::__construct();
    }

    function inv_search($efast_store_code, $params) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'wmsPageQueryInventoryBalanceService';
                    //wmsInventoryBalanceQueryService
        $params = $this->set_params($params);

        $ret = $this->biz_req($method, $params);

        if ($ret['data']['result']== 1) {//total_page
            if(isset($ret['data']['list']['item']['item'])){
                $ret['data']['list']['item'] = array($ret['data']['list']['item']);
            }

            foreach ($ret['data']['list']['item'] as $sku_inv) {
                if (!isset($sku_inv['item']) || !isset($sku_inv['on_hand_qty'])) {
                    continue;
                }
                
                if(isset($sku_inv['inventory_sts'])&&$sku_inv['inventory_sts']==20){
                     continue;
                }
     
                $result[] = array('barcode' => $sku_inv['item'], 'num' => $sku_inv['on_hand_qty']);
                
                
            }

            $ret_data['data'] = $result;
            $ret_data['pagecount'] = (int)$ret['data']['total_page']; 
            
            $ret = $this->format_ret(1, $ret_data);
        } else {
            $ret = $this->format_ret(-1, $ret['data'], $ret['data']['remark']);
        }
        return $ret;
    }

    function set_params($params) {
            $data = array(
                    'company' => $this->wms_cfg['company'],
                    'warehouse' => $this->wms_cfg['wms_store_code'],
                   // 'PageSize' => $params['pagesize'],
                    'page_index' => $params['page'],
            );

        return $data;
    }


    
    
}
