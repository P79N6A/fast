<?php

require_model("wms/WmsSellReturnModel");

class HwwmsSellReturnModel extends WmsSellReturnModel {
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
        $TotalQty = 0;
        $wms_order = array();
        
//         $sql = "select sku,lof_no,production_date,num from oms_sell_record_lof where record_code = :record_code and record_type = 2";
//         $order_lof_goods = $this->db->get_all($sql, array(":record_code" => $order['sell_return_code']));
        
        foreach ($order['goods'] as $row) {
        	$key_arr = array('goods_name');
        	$sku_info = load_model('goods/SkuCModel')->get_sku_info($row['sku'],$key_arr);
        	$items = array();
        	$items['Item'] = $row['barcode'];
        	$items['ItemName'] = $sku_info['goods_name'];
        	$items['ItemCount'] = $row['num'];
        	$items['Sprice'] = $row['goods_price'];
        	$wms_order['Items'][]['Item'] = $items;
        	$TotalQty += $row['num'];
        }
        
        
        $wms_order['Company'] = $this->wms_cfg['Company'];
        $wms_order['WareHouse'] = $this->wms_cfg['wms_store_code'];
        $wms_order['TotalQty'] = $TotalQty;
        $wms_order['TotalLines'] = count($order['goods']);
        $wms_order['ReceiptId'] = $this->is_canceled($record_code);
        $wms_order['ReceiptType'] = "退货入库";
        $wms_order['OrderDate'] = '';//预计到货日期
        $wms_order['Remark'] = $order['return_remark'];
        $wms_order['UserDef1'] = $order['return_name'];
        $method = 'ReceiptRequest';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status']>0){
        	return $this->format_ret(1,$record_code,'退货通知推送成功');
        }
        return $this->format_ret(-1,$ret['data']['Response'],'退货通知推送失败');
    }

    function cancel($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0){
        	return $this->format_ret(-1,$ret);
        }
        $order = $ret['data'];
        $wms_order = array();
        $wms_order['Company'] = $this->wms_cfg['Company'];
        $wms_order['WareHouse'] = $this->wms_cfg['wms_store_code'];
        $wms_order['ReceiptId'] = $this->is_canceled($record_code);
        $wms_order['Remark'] = $order['return_remark'];
        $method = 'ReceiptCancelRequest';
        $ret = $this->biz_req($method, $wms_order);
       	if ($ret['status']>0){
        	return $this->format_ret(1,$record_code,'退货通知推送成功');
        }
        return $this->format_ret(-1,$ret['data']['Response'],'退货通知推送失败');
    }
    
    function wms_record_info($record_code,$efast_store_code){
    	$this->get_wms_cfg($efast_store_code);
    	$wms_order = array();
    	$wms_order['CustomerID'] = $this->wms_cfg['Company'];
        $wms_order['WareHouse'] = $this->wms_cfg['wms_store_code'];
        $wms_order['ID'] = $record_code;
    	$method = 'GetReceipts';
    	$ret = $this->biz_req($method,$wms_order);
    	if (!empty($ret['data']['RECEIPTS'])){
    		$ret = $this->conv_wms_record_info($ret['data']['RECEIPTS']);
    	} else {
    		return $this->format_ret(-1,'','wms业务处理未完成');
    	}
    	return $ret;
    }
    
    function conv_wms_record_info($result){
    	$data = $result['RECEIPT'];
    	if (!isset($data[0])) {
    		$data = array($data);
    	}
    	$data = $data[0];
    	$ret['efast_record_code'] = $data['ReceiptId'];
    	$ret['wms_record_code'] = '';
    	$ret['wms_store_code'] = $data['WareHouse'];
    	 
    	$ret['order_status'] = 'flow_end';
    	$ret['order_status_txt'] = '已收发货';
    	$flow_end_time = explode("T", $data['ReceiptDate']);
    	$time = explode(".", $flow_end_time[1]);
    	
    	$ret['flow_end_time'] = $flow_end_time[0]." ".$time[0];
    	$goods = $data['Items']['Item'];
    	if (!isset($goods[0])) {
    		$goods = array($goods);
    	}
    	foreach($goods as $sub_goods){
    		$ret['goods'][] = array(
    				'barcode'=>$sub_goods['Item'],
    				'sl'=>$sub_goods['ItemCount'],
    				'lof_no'=> $sub_goods['Lot']
    		);
    	}
    	return $this->format_ret(1,$ret);
    }
    
    private function is_canceled($record_code){
    	$sql = "select new_record_code from wms_oms_trade where record_code = :record_code and record_type = :record_type";
    	$new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_return'));
    	return !empty($new_record_code)?$new_record_code:$record_code;
    }
}
