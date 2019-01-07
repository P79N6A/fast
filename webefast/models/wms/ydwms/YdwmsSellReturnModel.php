<?php

require_model("wms/WmsSellReturnModel");

class YdwmsSellReturnModel extends WmsSellReturnModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_return'));
        $order = json_decode($json_data, true);
        $check_order = $this->get_record_data($order);
        if ($check_order === false) {
            return $this->format_ret(-1, '', '解密失败，稍后再处理...');
        } 
        $this->get_wms_cfg($order['store_code']);
       
        $wms_order = array();
        //$wms_order['OrderNo'] = $order['sell_return_code'];
        $wms_order['OrderNo'] = $this->is_canceled($record_code);
        $wms_order['OrderType'] = 'RS';
        $wms_order['CustomerID'] = $this->wms_cfg['customerid'];
        $wms_order['WarehouseID'] = $this->wms_cfg['wms_store_code'];
        $wms_order['ASNCreationTime'] = $order['create_time'];
        $wms_order['ASNReference2'] = $order['return_express_no'];
        $wms_order['ASNReference3'] = $order['deal_code'];
        $wms_order['ASNReference4'] = $order['shop_code'];
        $wms_order['ASNReference5'] = $order['return_mobile'];
        $wms_order['IssuePartyName'] = $order['buyer_name'];
        
        
        $wms_order['PONO'] = $order['sell_record_code'];
        $wms_order['I_Contact'] = $order['return_name'];

        $wms_order['UserDefine1'] = $order['return_express_code'];
        $wms_order['UserDefine2'] = $order['return_express_no'];
        if(!empty($order['return_reason_code'])){
            $wms_order['UserDefine3'] =  $this->db->get_value("select return_reason_name from base_return_reason where  return_reason_code = '{$order['return_reason_code']}' AND return_reason_type=1 ");//  ;  // 退货原因 代码 可用转换
        }
    
        $wms_order['UserDefine4'] = 'ERP';
        
        //新增平台名
        $wms_order['UserDefine6'] =   $this->db->get_value("select sale_channel_name from base_sale_channel where  sale_channel_code = '{$order['sale_channel_code']}' ");
        


        foreach ($order['goods'] as $row) {
            $detailsItem = array();
            $detailsItem['CustomerID'] = $this->wms_cfg['customerid'];
            $detailsItem['SKU'] = $row['barcode'];
            $detailsItem['ExpectedQty'] = $row['num'];
            $detailsItem['TotalPrice'] = $row['avg_money'];
            $wms_order[]['detailsItem'] = $detailsItem;
        }


        $ret_data = array('header' => $wms_order);
        return $this->format_ret(1, $ret_data);
    }

    function upload($record_code) {

        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        
        
        $method = 'putASNData';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['data']['return']['returnCode'] == '0000') {
            $ret = $this->format_ret(1); //$this->format_ret(1, $ret['data']['wmsid'])
        } else {
            $message = isset( $ret['data']['return']['returnDesc'])?$ret['data']['return']['returnDesc']:'接口返回为空';
            $message .= isset($ret['data']['return']['resultInfo']['errordescr'])?':'.$ret['data']['return']['resultInfo']['errordescr']:'';
            $ret = $this->format_ret(-1, $ret['data'], $message); //$this->format_ret(1, $ret['data']['wmsid'])
            
            if($ret['data']['return']['returnCode']=='0001'){
            $check  = strpos( $ret['data']['return']['resultInfo']['errordescr'],'已经存在符合条件');
             if($check!==false){
                  $ret = $this->format_ret(1);
             }                
           }

        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'cancelASNData';
        $req['header'] = array(
            'OrderNo' => $this->is_canceled($record_code),
            'OrderType' => 'RS',
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
    
    //回传是被动接口
    function wms_record_info($record_code, $efast_store_code) {
        
          return $this->format_ret(-1); 
//        $this->get_wms_cfg($efast_store_code);
//        $method = 'ewms.orderreturnstatus.get';
//        $ret = $this->biz_req($method, array('BillId' => $record_code));
        /* $ret = array (
          'WarehouseCode' => 'ck1',
          'OrderCode' => '1505220001800',
          'OrderStatus' => 'FULFILLED',
          'OperateorTime' => '2015-05-22 16:32:23',
          'ExceptionCode' => NULL,
          'Note' => '',
          'ChargeDate' => '2015-05-22 16:32:23',
          'WMSBillCode' => 'AXCK1150522000001',
          'BillStockGoods' =>
          array (
          0 =>
          array (
          'SkuCode' => 'SP001002000',
          'normalQuantity' => '1',
          ),
          ),
          ); */
//        if ($ret['status'] < 0) {
//            return $ret;
//        }
//        $ret = $this->conv_wms_record_info($ret['data']);
//        return $ret;
    }

    function conv_wms_record_info($result) {
        //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';
        $status_map = array('FULFILLED' => 'flow_end', 'NotAvailableStatus' => 'upload', 'NotExist' => 'wait_upload', 'NotExistOrIsCancel' => 'wait_upload');
        $status_txt_map = array('flow_end' => '已收发货', 'upload' => '已上传', 'wait_upload' => '未上传');

        $ret = array();
        if (isset($result['bizid'])) {
            $ret['efast_record_code'] = $result['bizid'];
            $order_status = $result['state'];
            $ret['order_status'] = isset($status_map[$order_status]) ? $status_map[$order_status] : $ret['order_status'];
            $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
            $ret['msg'] = $result['msg'];
            return $this->format_ret(1, $ret);
        }

        $ret['efast_record_code'] = $result['OrderCode'];
        $ret['wms_record_code'] = $result['WMSBillCode'];
        $ret['wms_store_code'] = $result['WarehouseCode'];
        $order_status = $result['OrderStatus'];

        $ret['order_status'] = is_null($status_map[$order_status]) ? $order_status : $status_map[$order_status];
        $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
        $ret['flow_end_time'] = $result['ChargeDate'];

        $goods = $result['BillStockGoods'];
        foreach ($goods as $sub_goods) {
            $ret['goods'][] = array('barcode' => $sub_goods['SkuCode'], 'sl' => $sub_goods['normalQuantity']);
        }
        return $this->format_ret(1, $ret);
    }

    private function is_canceled($record_code){
    	$sql = "select new_record_code from wms_oms_trade where record_code = :record_code and record_type = :record_type";
    	$new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_return'));
    	return !empty($new_record_code)?$new_record_code:$record_code;
    }
}
