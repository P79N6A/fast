<?php

require_model("wms/WmsWbmNoticeModel");

class YdwmsPurReturnNoticeModel extends WmsWbmNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'pur_return_notice'));
        $order = json_decode($json_data, true);
        $this->get_wms_cfg($order['store_code']);
        $wms_order = array();
        $wms_order['OrderNo'] = $order['record_code'];
        $wms_order['OrderType'] = 'RP';
        $wms_order['WarehouseID'] = $this->wms_cfg['wms_store_code'];
        $wms_order['OrderTime'] = $order['record_time'];

        $wms_order['CustomerID'] = $this->wms_cfg['customerid'];
        $wms_order['ConsigneeID'] = $order['supplier']['supplier_name'];
        $wms_order['ConsigneeName'] = $order['supplier']['contact_person'];
        $wms_order['UserDefine2'] = $order['relation_code'];  
        $wms_order['UserDefine4'] = 'ERP';
        $wms_order['C_Tel1'] = $order['supplier']['mobile']; 
         $wms_order['C_Tel2'] = $order['supplier']['tel'];  
        
       $wms_order['C_Address1'] = $order['supplier']['address'];  
         

        $wms_order['H_EDI_02'] = $order['money'];

        foreach ($order['goods'] as $row) {
            $detailsItem = array();
            $detailsItem['CustomerID'] = $this->wms_cfg['customerid'];
            $detailsItem['SKU'] = $row['barcode'];
            $detailsItem['QtyOrdered'] = $row['num'];
          
            $wms_order[]['detailsItem'] = $detailsItem;
        }
        return array('header' => $wms_order);
    }

    function upload($record_code) {
        $wms_order = $this->convert_data($record_code);

        $method = 'putSOData';
        //  var_dump($method, $wms_order);die;
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['data']['return']['returnCode'] == '0000') {
            $ret = $this->format_ret(1); //$this->format_ret(1, $ret['data']['wmsid'])
        } else {
            $message = isset( $ret['data']['return']['returnDesc'])?$ret['data']['return']['returnDesc']:'接口返回为空';
            $message .= isset($ret['data']['return']['resultInfo']['errordescr'])?':'.$ret['data']['return']['resultInfo']['errordescr']:'';
            
            $ret = $this->format_ret(-1, $ret['data'], $message); //$this->format_ret(1, $ret['data']['wmsid'])

            $check  = strpos( $ret['data']['return']['resultInfo']['errordescr'],'已经存在符合条件');
                if($check!==false){
                     $ret = $this->format_ret(1);
                }    
            
            
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'cancelSOData';
        $req['header'] = array(
            'OrderNo' => $record_code,
            'OrderType' => 'RP',
            'CustomerID' => $this->wms_cfg['customerid'],
            'WarehouseID' => $this->wms_cfg['wms_store_code'],
        );
        $ret = $this->biz_req($method, $req);
        if ($ret['data']['return']['returnCode'] == '0000') {
            $ret = $this->format_ret(1); //$this->format_ret(1, $ret['data']['wmsid'])
        } else {
            $message = isset( $ret['data']['return']['returnDesc'])?$ret['data']['return']['returnDesc']:'接口返回为空';
            $message .= isset($ret['data']['return']['resultInfo']['errordescr'])?':'.$ret['data']['return']['resultInfo']['errordescr']:'';
            
            $ret = $this->format_ret(-1, $ret['data'], $message); //$this->format_ret(1, $ret['data']['wmsid'])
            
        }
        return $ret;
    }

}
