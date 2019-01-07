<?php
require_model("wms/WmsShiftInModel");
class HwwmsShiftInModel extends WmsShiftInModel {
	function __construct()
	{
		parent::__construct();
	}

	function convert_data($record_code){
		$sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
		$json_data = ctx()->db->getOne($sql,array(':record_code'=>$record_code,':record_type'=>'shift_in'));
		if (empty($json_data)){
        	return $this->format_ret(-1,'','移仓单不存在');
        }
        $order = json_decode($json_data, true);
        $this->get_wms_cfg($order['shift_in_store_code']);
        return $this->format_ret(1,$order);
	}

	function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0){
        	return $this->format_ret(-1,$ret);
        }
        $order = $ret['data'];
        $TotalQty = 0;
        $wms_order = array();
        
        $sql = "select sku,lof_no,production_date,num from b2b_lof_datail where order_code = :order_code and order_type = 'shift_out'";
        $order_lof_goods = $this->db->get_all($sql, array(":order_code" => $record_code));
       
        foreach ($order_lof_goods as $row) {
        	$key_arr = array('goods_name','purchase_price','barcode');
        	$sku_info =  load_model('goods/SkuCModel')->get_sku_info($row['sku'],$key_arr);
        	$items = array();
        	$items['Item'] = $sku_info['barcode'];
        	$items['ItemName'] = $sku_info['goods_name'];
        	$items['ItemCount'] = $row['num'];
        	$items['Sprice'] = $sku_info['purchase_price'];
        	$items['Lot'] = $sku_info['lof_no'];
        	$wms_order['Items'][]['Item'] = $items;
        	$TotalQty += $row['num'];
        }
        $wms_order['Company'] = $this->wms_cfg['Company'];
        $wms_order['WareHouse'] = $this->wms_cfg['wms_store_code'];
        $wms_order['TotalQty'] = $TotalQty;
        $wms_order['TotalLines'] = count($order_lof_goods);
        $wms_order['ReceiptId'] = $this->is_canceled($record_code);
        $wms_order['ReceiptType'] = "调仓入库";
        $wms_order['Remark'] = $order['remark'];
        $method = 'ReceiptRequest';var_dump($wms_order);
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status']>0){
        	return $this->format_ret(1,'','移仓入库推送成功');
        }
        return $this->format_ret(-1,$ret['data']['Response'],'移仓入库推送失败');
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
        $wms_order['Remark'] = $order['remark'];
        $method = 'ReceiptCancelRequest';
        $ret = $this->biz_req($method, $wms_order);
       	if ($ret['status']>0){
        	return $this->format_ret(1,'','移仓入库取消推送成功');
        }
        return $this->format_ret(-1,$ret['data']['Response'],'移仓入库取消推送失败');
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
    	$sql = "select new_record_code from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
    	$new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'shift_in'));
    	return !empty($new_record_code)?$new_record_code:$record_code;
    }
}