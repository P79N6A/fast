<?php

require_model("wms/WmsPurNoticeModel");

class YdwmsPurNoticeModel extends WmsPurNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'pur_notice'));
        $order = json_decode($json_data, true);

        $this->get_wms_cfg($order['store_code']);
        $wms_order = array();
        $wms_order['OrderNo'] = $this->is_canceled($record_code);
        $wms_order['OrderType'] = 'PO';
        $wms_order['CustomerID'] = $this->wms_cfg['customerid'];
        $wms_order['WarehouseID'] = $this->wms_cfg['wms_store_code'];
        $wms_order['ASNCreationTime'] = $order['is_add_time'];
        $wms_order['SupplierID'] = $order['supplier_code'];
        $wms_order['Supplier_Name'] = $order['supplier']['supplier_name'];


        $wms_order['UserDefine4'] = 'ERP';

        
        foreach ($order['goods'] as $row) { 
            $detailsItem = array();
            $detailsItem['CustomerID'] = $this->wms_cfg['customerid'];
            $detailsItem['SKU'] = $row['barcode'];
            $detailsItem['ExpectedQty'] = $row['num'];
            $wms_order[]['detailsItem'] = $detailsItem;
        }
        return array('header' => $wms_order);
    }

    function upload($record_code) {
        $wms_order = $this->convert_data($record_code);

        $method = 'putASNData';

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
        $method = 'cancelASNData';
        $req['header'] = array(
            'OrderNo' => $this->is_canceled($record_code),
            'OrderType' => 'PO',
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

    private function is_canceled($record_code){
    	$sql = "select new_record_code from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
    	$new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'pur_notice'));
    	return !empty($new_record_code)?$new_record_code:$record_code;
    }
}
