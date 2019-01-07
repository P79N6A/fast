<?php

require_model("wms/WmsInvModel");

class YdwmsInvModel extends WmsInvModel {

    function __construct() {
        parent::__construct();
    }
       function inv_search($efast_store_code, $barcode_arr) {
         
         $this->get_wms_cfg($efast_store_code);
         $result = array();
         $error_arr = array();
         foreach ($barcode_arr as $barcode){
            $method = 'queryINVData';
            $params = $this->set_params($barcode);

            $ret = $this->biz_req($method, $params);

            if ($ret['data']['return']['returnCode'] == '0000') {
                
                if(isset($ret['data']['items']['item']['SKU'])){
                    $ret['data']['items']['item'] = array($ret['data']['items']['item'] );
                }

                foreach ($ret['data']['items']['item'] as $sku_inv) {
                    if (!isset($sku_inv['SKU']) || !isset($sku_inv['Qty_total'])) {
                        continue;
                    }
                    
                    if(!empty($sku_inv['udf10'])){
                        $inv_row = array();
                        $udf10 = strtoupper($sku_inv['udf10']);
                        if($udf10 =='ZP'){
                            $inv_row = array('barcode' => $sku_inv['SKU'], 'num' => $sku_inv['Qty_total']);
                        }else{
                             $inv_row = array('barcode' => $sku_inv['SKU'], 'cp_num' => $sku_inv['Qty_total']);
                        }
                        
                        $result[$sku_inv['SKU']] = (isset( $result[$sku_inv['SKU']]))?array_merge($result[$sku_inv['SKU']],$inv_row):$inv_row;
                        
                    }else{
                         $result[$sku_inv['SKU']] = array('barcode' => $sku_inv['SKU'], 'num' => $sku_inv['Qty_total']);
                    }
                   
                }
            } else {
                if(strpos($ret['data']['return']['returnDesc'],'无库存信息')!==false){
                    $result[$barcode] = array('barcode' => $barcode, 'num' => 0);
                }else{
                      $error_arr[]=$ret['data']['return']['returnDesc'];
                }
            }
         }
         if(!empty($result)){
          //  $ret_data['data'] = $result;
            $ret = $this->format_ret(1, $result);
         }  else {
               $ret = $this->format_ret(-1, $result,  implode(",", $error_arr) );
         }
        return $ret;
        
    }
    function inv_search2($efast_store_code, $params) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'queryINVData';
        $params = $this->set_params($params);

        $ret = $this->biz_req($method, $params);

        if ($ret['data']['return']['returnCode'] == '0000') {

            foreach ($ret['data']['items']['item'] as $sku_inv) {
                if (!isset($sku_inv['SKU']) || !isset($sku_inv['Qty_total'])) {
                    continue;
                }
                $result[] = array('barcode' => $sku_inv['SKU'], 'num' => $sku_inv['Qty_total']);
            }
        
            $ret_data['data'] = $result;
            $ret_data['pagecount'] = (int)$ret['data']['return']['resultInfo']; //
            
            $ret = $this->format_ret(1, $ret_data);
        } else {
            $ret = $this->format_ret(-1, $ret['data'], $ret['data']['return']['returnDesc']);
        }
        return $ret;
    }

    function set_params($barcode) {


            $data_header = array(
                'header' => array(
                    'CustomerID' => $this->wms_cfg['customerid'],
                    'WarehouseID' => $this->wms_cfg['wms_store_code'],
                    'SKU'=>$barcode,
                    'PageSize' =>100,// $params['pagesize'],
                    'PageNo' => 1,//$params['page'],
                )
            );


        return array('data' => $data_header);
    }
    //测试使用
    function inv_add($efast_store_code,$inv_barcode){
           $this->get_wms_cfg($efast_store_code);
        $order_sn = '2015081901';
        $header = array(
        'OrderNo' => $order_sn,
        'OrderType' => 'PO',
        'CustomerID' => $this->wms_cfg['customerid'],
        'WarehouseID' => $this->wms_cfg['wms_store_code'],
         'UserDefine4'=>'ERP',
        );
        foreach($inv_barcode as $val){
            $detailsItem = array(
                'CustomerID'=> $this->wms_cfg['customerid'],
                 'SKU'=> $val['barcode'],
                 'ExpectedQty'=> $val['num'],
            );
            $header[]['detailsItem'] = $detailsItem;
        }

         $method = 'putASNData';
         $data['header'][] = $header;
        $ret = $this->biz_req($method, $data); 
        var_dump($data,$ret);
        
    }
   function inv_add_test(){
            $efast_store_code = 'ydwms';
            $sql = "select b.barcode,i.stock_num as num from goods_inv i
                    INNER JOIN goods_sku b ON i.sku=b.sku
                    where i.store_code='{$efast_store_code}' AND b.barcode not in( select code from wms_archive where type='goods_barcode' and api_product='ydwms' and is_success=0
)";
            $inv_barcode = $this->db->get_all($sql);
            $this->inv_add($efast_store_code,$inv_barcode);
   }
    
    
}
