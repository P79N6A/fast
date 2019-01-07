<?php
require_model("wms/WmsPurReturnNoticeModel");
class HwwmsPurReturnNoticeModel extends WmsPurReturnNoticeModel {
	function __construct()
	{
		parent::__construct();
	}

	function convert_data($record_code) {
        $sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql,array(':record_code'=>$record_code,':record_type'=>'pur_return_notice'));
        if (empty($json_data)){
        	return $this->format_ret(-1,'','通知单号不存在');
        }
        $order = json_decode($json_data, true);
        $this->get_wms_cfg($order['store_code']);
        return $this->format_ret(1,$order);
    }

	function upload($record_code){
		$ret = $this->convert_data($record_code);
		if ($ret['status'] < 0){
			return $this->format_ret(-1,$ret);
		}
		$order = $ret['data'];
		$wms_order = array();
		$wms_order['Company'] = $this->wms_cfg['Company'];
        $wms_order['WareHouse'] = $this->wms_cfg['wms_store_code'];
		$wms_order['ShipmentId'] = $this->is_canceled($record_code);
		$wms_order['TorderCode'] = '';//淘宝订单号
		$wms_order['ShipmentType'] = '调仓出库';
		//发货信息

		$wms_order['Name'] = $order['supplier']['contact_person'];
		$wms_order['PostCode'] = $order['supplier']['zipcode'];
		$wms_order['Phone'] = $order['supplier']['phone'];
		$wms_order['Mobile'] = $order['supplier']['mobile'];
		$wms_order['Address'] = $order['supplier']['address'];
		$wms_order['Invoice'] = "N";
		$wms_order['Remark'] = $order['remark'];
		
		$sql = "select sku,lof_no,production_date,num from b2b_lof_datail where order_code = :order_code and order_type = 'pur_return_notice'";
        $order_lof_goods = $this->db->get_all($sql, array(":order_code" => $order['record_code']));
		//商品详细信息
		$TotalQty = 0;
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
		$wms_order['TotalQty'] = $TotalQty;
		$wms_order['TotalLines'] = count($order_lof_goods);
		$method = 'ShipmentRequest';
		$ret = $this->biz_req($method,$wms_order);
		if ($ret['status']>0){
			return $this->format_ret(1,'','采购退货通知单推送成功');
		}
		return $this->format_ret(-1,$ret['data']['Response'],'采购退货通知单推送失败');//   return  xiugai
	}

	function cancel($record_code,$efast_store_code){
		$ret = $this->convert_data($record_code);
        if ($ret['status'] < 0){
        	return $this->format_ret(-1,$ret);
        }
        $order = $ret['data'];
        $wms_order = array();
        $wms_order['Company'] = $this->wms_cfg['Company'];
        $wms_order['WareHouse'] = $this->wms_cfg['wms_store_code'];
        $wms_order['ShipmentId'] = $this->is_canceled($record_code);
        $wms_order['Remark'] = $order['remark'];
        $method = 'ShipmentCancelRequest';
        $ret = $this->biz_req($method, $wms_order);
		if ($ret['status']>0){
        	return $this->format_ret(1,'','采购退货通知单取消推送成功');
        }
        return $this->format_ret(-1,$ret['data']['Response'],'采购退货通知单取消推送失败');
	}
	
	function wms_record_info($record_code,$efast_store_code){
		//var_dump($record_code);var_dump($efast_store_code);exit;
		$this->get_wms_cfg($efast_store_code);
		$wms_order = array();
		$wms_order['CustomerID'] = $this->wms_cfg['Company'];
		$wms_order['WareHouse'] = $this->wms_cfg['wms_store_code'];
		$wms_order['ID'] = $record_code;
		$method = 'GetShipments';
		$ret = $this->biz_req($method,$wms_order);
		if (!empty($ret['data']['SHIPMENTS'])){
			$ret = $this->conv_wms_record_info($ret['data']['SHIPMENTS']);
		} else {
			return $this->format_ret(-1,'','wms业务处理未完成');
		}
		return $ret;
	}
	
	function conv_wms_record_info($result){
		$ret = array();
		$data = $result['SHIPMENT'];
		if (!isset($data[0])) {
			$data = array($data);
		}
		$data = $data[0];
		$ret['efast_record_code'] = $data['ShipmentId'];
		$ret['wms_record_code'] = '';
		$ret['wms_store_code'] = $data['WareHouse'];
		$ret['order_status'] = 'flow_end';
		$ret['order_status_txt'] = '已收发货';
		$flow_end_time = explode("T", $data['ShipmentDate']);
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
		$new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'pur_return_notice'));
		return !empty($new_record_code)?$new_record_code:$record_code;
	}
}