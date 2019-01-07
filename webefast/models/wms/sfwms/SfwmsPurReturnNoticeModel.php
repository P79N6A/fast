<?php
require_model("wms/WmsWbmNoticeModel");
class SfwmsPurReturnNoticeModel extends WmsWbmNoticeModel {
	function __construct()
	{
		parent::__construct();
	}

	function convert_data($record_code){
		$sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
		$json_data = ctx()->db->getOne($sql,array(':record_code'=>$record_code,':record_type'=>'pur_return_notice'));
		$order = json_decode($json_data,true);

		$this->get_wms_cfg($order['store_code']);
		$wms_order['header'] = array(
				'company' => $this->wms_cfg['company'],//货主代码
				'warehouse' => $this->wms_cfg['wms_store_code'],
				'erp_order' => $order['record_code'],
				'order_type' => '返厂订单',
				'order_date' => $order['record_time'],
				'ship_to_name'=>$order['supplier']['supplier_name'],
				'ship_to_attention_to'=>$order['supplier']['contact_person'],
				'ship_to_address'=>$order['supplier']['address'],
				'ship_to_postal_code'=>$order['supplier']['zipcode'],
				'ship_to_phone_num'=>$order['supplier']['mobile']?$order['supplier']['mobile']:$order['supplier']['tel'],
				'cod' => 'N',
		);
		if (isset($this->wms_cfg['monthly_account'])) {
			$wms_order['header']['monthly_account'] = $this->wms_cfg['monthly_account'];
		}
		$order_goods = array();
		$index=0;
		foreach ($order['goods'] as $row) {
			$index++;
			$t_row = array();
			$t_row['erp_order_line_num']=$index;
			$t_row['item']=strtoupper($row['barcode']);
			$t_row['item_name'] = $row['goods_name'];
			$t_row['qty']=$row['num'];
			$t_row['uom']='个';//商品单位
			$order_goods[] = array('item'=>$t_row);
		}
		$wms_order['detailList'] = $order_goods;
		
		return $this->format_ret(1,$wms_order);
	}

	function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 1) {
            return $ret;
        }

        $method = 'wmsSailOrderService';
        $ret = $this->biz_req($method, $ret['data']);
        if ($ret['status'] > 0) {
            return $this->format_ret(1,$ret['data']['orderid']); 
        }
        return $ret;
    }

	function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'wmsCancelSailOrderService';
        $req = array('orderid' => $record_code, 'company' => $this->wms_cfg['company']);
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0  && strpos($ret['data']['error'], "未找到") !== false) {
            return $this->format_ret(1, $ret['data']);
        }
        return $ret;
    }
   //状态回传
    function wms_record_info($record_code,$efast_store_code){
		$this->get_wms_cfg($efast_store_code);
		$method = 'wmsSailOrderStatusQueryNewService';
        
                $data = array('company'=>$this->wms_cfg['company'],'orderid'=>$record_code);
                
		$ret = $this->biz_req($method,$data);
	
		if ($ret['status']<0){
			return $ret;
		}
		$ret = $this->conv_wms_record_info($ret['data'],$record_code,$efast_store_code);
		return $ret;
	}
	function conv_wms_record_info($result,$record_code,$efast_store_code){
		//echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';
		$status_map = array('900'=>'flow_end','10012'=>'','10013'=>'cancel','10016'=>'flow_end');//10012 作废 10013 取消
		$status_txt_map = array('flow_end'=>'已收发货','upload'=>'已上传','wait_upload'=>'未上传');
		//根据顺丰返回的操作日志 解析当前订单所处的订单状态
		$result_status = $this->convert_sf_result_status($result);
		//是否已出库
		$order_status = $result_status['status_code'];
		$ret['order_status'] = is_null($status_map[$order_status]) ? 'upload' : $status_map[$order_status];
		$ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
		if (isset($status_map[$order_status]) && $status_map[$order_status] == 'flow_end' ) {
			//已出库获取商品明细
			$order = $this->get_wms_record_info_detail($record_code,$efast_store_code);
			$order_header = $order['data']['header'];
			$ret['efast_record_code'] = $record_code;
			$ret['wms_record_code'] = $order_header['shipment_id'];
			$ret['wms_store_code'] = $efast_store_code;
			$ret['express_code'] = array_search($order_header['carrier'], $this->shipping);
			$ret['express_no'] =  $order_header['waybill_no'];
			
			//发货时间
			if (isset($order['actual_ship_date_time'])) {
				$flow_end_time = $order_header['actual_ship_date_time'];
			} elseif (isset($order['status_time'])){
				$flow_end_time = $order_header['status_time'];
			}
			$ret['flow_end_time'] = $flow_end_time;
			
			$goods_ret =  $order['data']['detailList']['item'];
			if(isset($goods_ret[0])){
				$goods = $goods_ret;
			}else{
				$goods[0] = $goods_ret;
			} 
			foreach($goods as $sub_goods){
				$ret['goods'][] = array('barcode'=>strtolower($sub_goods['item']),'sl'=>$sub_goods['quantity']);
			}
		} 
		return $this->format_ret(1,$ret);
	}
	//根据顺丰返回的操作日志 解析当前订单所处的订单状态
	function convert_sf_result_status($result){
		$ret = array();
		$ret['erp_order'] = $result['order']['orderid'];
		$steps = $result['order']['steps']['step'];
		if(isset($steps[0])){
			$steps_new = array_reverse($steps);
		} else {
			$steps_new[] = $steps;
		}
		foreach ($steps_new as $step) {
			$row = explode(':', $step['status']);
			if (!empty($row[0]) && $row[0]!='null') {
				$ret['status_code'] = $row[0];
				break;
			}
		}
		return $ret;
	}
    function get_wms_record_info_detail($record_code,$efast_store_code){
        $this->get_wms_cfg($efast_store_code);
        $method = 'wmsSailOrderQueryService';
        $req = array( 'company' => $this->wms_cfg['company'],'warehouse'=>$this->wms_cfg['wms_store_code'],'orderid' => $record_code,);
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0) {
            return $this->format_ret(1, $ret['data']);
        }
        return $ret;  
    }

}