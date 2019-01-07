<?php

require_model("wms/WmsSellReturnModel");

class BswmsSellReturnModel extends WmsSellReturnModel {
    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_return'));
        if (empty($json_data)){
        	return $this->format_ret(-1,'','订单号不存在');
        }
        $order = json_decode($json_data, true);
    //   $this->get_record_data($order);
        $check_order = $this->get_record_data($order);
        if($check_order===false){
             return $this->format_ret(-1, '','解密失败，稍后再处理...');
        }
        
        
        $this->get_wms_cfg($order['store_code']);
        return $this->format_ret(1,$order);
       
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0){
        	return $ret;
        }
        $order = $ret['data'];
        $wms_order = array();
        $wms_order['customerCode'] = $this->wms_cfg['customerCode'];
        $wms_order['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $wms_order['rmaCode'] = $order['sell_return_code'];
        $wms_order['actionType'] = 'ADD';//操作类型： ADD-新增 CANCEL-取消
        //$wms_order['extOrderType'] = '';
        $wms_order['extTradeId'] = $order['sell_record_code'];
        $wms_order['orderTime'] = $order['create_time'];
        $wms_order['tmsCompany'] = $this->db->get_value("select express_name from base_express where express_code = '{$order['return_express_code']}'");
        $wms_order['tmsShippingNo'] = $order['return_express_no'];
        
        $sender = array();
        $sender['name'] = $order['return_name'];
        $sender['postalCode'] = $order['return_zip_code'];
        $sender['phoneNumber'] = $order['return_phone'];
        $sender['mobileNumber'] = $order['return_mobile'];
        $sender['province'] = $this->get_area_name($order['return_province']);
        $sender['city'] = $this->get_area_name($order['return_city']);
        $sender['district'] = $this->get_area_name($order['return_district']);
        $sender['shippingAddress'] = $order['return_addr'];
        $sender['email'] = $order['return_email'];
        $wms_order['sender'] = $sender;
        
        foreach ($order['goods'] as $row) {
//         	$key_arr = array('goods_name','spec1_code','spec2_code','spec1_name','spec2_name');
//         	$sku_info =  load_model('goods/SkuCModel')->get_sku_info($row['sku'],$key_arr);
        	$items = array();
        	$items['itemSkuCode'] = $row['barcode'];
        	$items['itemName'] = $row['goods_name'];
        	$items['itemQuantity'] = $row['num'];
        	$items['sellingPrice'] = $row['goods_price']*$row['num'];
        	$items['itemNote'] = "颜色:".$row['spec1_name'].";尺码:".$row['spec2_name'];
        	$wms_order['items'][]['item'] = $items;
        }
        	
        $method = 'SyncRmaInfo';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status']>0){
            if($ret['data']['RmaInfo']['flag'] == 'SUCCESS'){
        	return $this->format_ret(1,'','退货通知推送成功');
            }
        }
        return $this->format_ret(-1,$ret['data']['RmaInfo'],'退货通知推送失败');
    }

    function cancel($record_code, $efast_store_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0){
        	return $this->format_ret(-1,$ret);
        }
        $order = $ret['data'];
        $wms_order = array();
        $wms_order['customerCode'] = $this->wms_cfg['customerCode'];
        $wms_order['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $wms_order['rmaCode'] = $order['sell_return_code'];
        $wms_order['actionType'] = 'CANCEL';//操作类型： ADD-新增 CANCEL-取消
        $method = 'SyncRmaInfo';
        $ret = $this->biz_req($method, $wms_order);
       	if ($ret['status']>0){
            if($ret['data']['RmaInfo']['flag'] == 'SUCCESS'){
        	return $this->format_ret(1,'','退货通知推送成功');
            }
        }
        return $this->format_ret(-1,$ret['data']['RmaInfo'],'退货通知推送失败');
      
    }
    
    private function get_area_name($id) {
    	$sql = "select name from base_area where id=:id";
    	return $this->db->get_value($sql, array(':id' => $id));
    }
    
    //状态回传
    function wms_record_info($record_code, $efast_store_code) {
        $this->wms_cfg = array();
        $this->get_wms_cfg($efast_store_code);
        $method = 'GetRmaStatus';
        $data = array();
        $data['customerCode'] = $this->wms_cfg['customerCode'];
        $data['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['rmaCode'] = $record_code;
        $ret = $this->biz_req($method, $data);
        if ($ret['status'] < 0) {
            return $ret;
        }
 
        if($ret['data']['RmaStatus']['flag'] == 'SUCCESS'){
            $ret = $this->conv_wms_record_info($ret['data']['RmaStatus']['rma'], $efast_store_code);
        } else {
            return $this->format_ret(-1,$ret,$ret['data']['RmaStatus']['note']);
        }
            
   
        return $ret;
    }

    function conv_wms_record_info($result, $efast_store_code) {
        $order_status = $result['RmaStatus'];
        if ($order_status == 'FULFILLED') {
            $ret['order_status'] = 'flow_end';
            $ret['order_status_txt'] = '收货完成';
            $ret['efast_record_code'] = $result['rmaCode'];
            //$ret['wms_record_code'] = '';
            $ret['wms_store_code'] = $efast_store_code;

            //发货时间
            $ret['flow_end_time'] = isset($result['receiveTime'])?$result['receiveTime']:'';
            $goods_ret = $result['products']['product'];
            if (isset($goods_ret[0])) {
                $goods = $goods_ret;
            } else {
                $goods[0] = $goods_ret;
            }
            foreach ($goods as $sub_goods) {
                $ret['goods'][] = array('barcode' => $sub_goods['skuCode'], 'sl' => $sub_goods['normalQuantity']);
            }
        }
        return $this->format_ret(1, $ret);
    }

}
