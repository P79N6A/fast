<?php
require_model("wms/WmsPurNoticeModel");
class SfwmsPurNoticeModel extends WmsPurNoticeModel {
	function __construct()
	{
		parent::__construct();
	}

	function convert_data($record_code){
		$sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
		$json_data = ctx()->db->getOne($sql,array(':record_code'=>$record_code,':record_type'=>'pur_notice'));
		$order = json_decode($json_data,true);
		$this->get_wms_cfg($order['store_code']);
		$wms_order['header'] = array(
			'company' => $this->wms_cfg['company'],//货主代码
			'warehouse' => $this->wms_cfg['wms_store_code'],
			'source_id' => $this->wms_cfg['company'],//供应商
			'erp_order_num' => $order['record_code'],
			'erp_order_type' => '采购入库',
			'order_date' => $order['order_time'],
			'scheduled_receipt_date' => $order['in_time'],
			'note_to_receiver'=>$order['remark'],
		);
		$order_goods = array();
		$index=0;
		foreach ($order['goods'] as $row) {
			$index++;
			$t_row = array();
			$t_row['erp_order_line_num']=$index;
			$t_row['item']= strtoupper($row['barcode']);
			$t_row['total_qty']=$row['num'];
			$order_goods[]= array('item'=>$t_row) ;
		}
		$wms_order['detailList'] = $order_goods;
		return $this->format_ret(1,$wms_order);
	}

	function upload($record_code){
		$ret = $this->convert_data($record_code);
		if ($ret['status']<0){
			return $ret;
		}
		$wms_order = $ret['data'];
		$method = 'wmsPurchaseOrderService';
		$ret = $this->biz_req($method,$wms_order);
		if ($ret['status']>0){
			return $this->format_ret(1,$ret['data']['orderid']);
		}
		return $ret;
	}
	
	function cancel($record_code, $efast_store_code) {
		return $this->format_ret(-1,'','顺丰仓储不支持入库单的取消');
	}

	function wms_record_info($record_code,$efast_store_code){
		$this->get_wms_cfg($efast_store_code);
		$method = 'wmsPurchaseOrderQueryService';
		
		$data = array('company'=>$this->wms_cfg['company'],'warehouse' => $this->wms_cfg['wms_store_code'],'orderid'=>$record_code);
		$ret = $this->biz_req($method,$data);
	
		if ($ret['status']<0){
			return $ret;
		}
		$ret = $this->conv_wms_record_info($ret['data']);
		return $ret;
	}

	function conv_wms_record_info($result){
		if(isset($result['header'])){
			$order = $result['header'];
			$ret['efast_record_code'] = $order['erp_order_num'];
			//关闭时间
			if (!empty($order['close_date'])) {
				$ret['wms_record_code'] = $order['erp_order_num'];
				$ret['wms_store_code'] = $this->wms_cfg['wms_store_code'];
				$ret['flow_end_time'] = $order['close_date'];
				$ret['order_status'] = 'flow_end';
				$ret['order_status_txt'] = '已收发货';
				if (!isset($result['detailList']['item'][0])) {
					$goods[] = $result['detailList']['item'];
				} else {
					$goods = $result['detailList']['item'];
				}
				$skus = array();
				foreach($goods as $sub_goods){
					$skus[$sub_goods['sku_no']] += $sub_goods['qty'];
				}
				foreach ($skus as $sku_no=>$qty) {
					$ret['goods'][] = array('barcode'=>strtolower($sku_no),'sl'=>$qty);
				}
			} else {
				$ret['efast_record_code'] = $order['erp_order_num'];
				$ret['order_status'] = 'upload';
				$ret['order_status_txt'] = '已上传';
			}
		}
		return $this->format_ret(1,$ret);
	}

}