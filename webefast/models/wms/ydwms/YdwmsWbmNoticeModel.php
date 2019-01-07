<?php

require_model("wms/WmsWbmNoticeModel");

class YdwmsWbmNoticeModel extends WmsWbmNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'wbm_notice'));
        $order = json_decode($json_data, true);
        $this->get_wms_cfg($order['store_code']);
        $wms_order = array();
        $wms_order['OrderNo'] = $this->is_canceled($record_code);
        $wms_order['OrderType'] = 'TO';
        $wms_order['WarehouseID'] = $this->wms_cfg['wms_store_code'];
        $wms_order['OrderTime'] = $order['is_add_time'];

        $wms_order['CustomerID'] = $this->wms_cfg['customerid'];
        $wms_order['ConsigneeID'] = $order['distributor_code']; //分销商代码
        $wms_order['UserDefine4'] = 'ERP';

        $wms_order['H_EDI_02'] = $order['money'];


        foreach ($order['goods'] as $row) {
            $detailsItem = array();
            $detailsItem['CustomerID'] = $this->wms_cfg['customerid'];
            $detailsItem['SKU'] = $row['barcode'];
            $detailsItem['QtyOrdered'] = $row['num'];
            $detailsItem['Price'] = $row['price'];
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
            'OrderNo' => $this->is_canceled($record_code),
            'OrderType' => 'SO',
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

    function wms_record_info($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'ewms.batchoutstatus.get';
        $ret = $this->biz_req($method, array('BillId' => $record_code));
        /* $ret = array (
          'WarehouseCode' => 'ck1',
          'AsnCode' => 'PFTZ20150410000014',
          'AsnStatus' => 'FULFILLED',
          'OperateorTime' => '2015-05-22 14:20:28',
          'ExceptionCode' => NULL,
          'Memo' => '',
          'ChargeDate' => '2015-05-22 14:20:28',
          'BillStockGoods' =>
          array (
          0 =>
          array (
          'SkuCode' => 'SP001001002',
          'normalQuantity' => '4',
          ),
          1 =>
          array (
          'SkuCode' => 'SP001002000',
          'normalQuantity' => '8',
          ),
          ),
          ); */
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->conv_wms_record_info($ret['data']);
        return $ret;
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
            //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
            return $this->format_ret(1, $ret);
        }

        $ret['efast_record_code'] = $result['AsnCode'];
        $ret['wms_record_code'] = '';
        $ret['wms_store_code'] = $result['WarehouseCode'];
        $order_status = $result['AsnStatus'];

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
    	$sql = "select new_record_code from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
    	$new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'wbm_notice'));
    	return !empty($new_record_code)?$new_record_code:$record_code;
    }

}
