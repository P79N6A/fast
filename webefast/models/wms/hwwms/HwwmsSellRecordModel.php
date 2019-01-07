<?php
require_model("wms/WmsSellRecordModel");
class HwwmsSellRecordModel extends WmsSellRecordModel {
	function __construct()
	{
		parent::__construct();
	}

	function convert_data($record_code){
		$sql = "select json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
        if (empty($json_data)){
        	return $this->format_ret(-1,'','订单号不存在');
        }
        $order = json_decode($json_data, true);
        //$this->get_record_data($order);
          $check_order = $this->get_record_data($order);
        if($check_order===false){
             return $this->format_ret(-1, '','解密失败，稍后再处理...');
        }
        
        
        $this->get_wms_cfg($order['store_code']);
        return $this->format_ret(1,$order);
	}

	function upload($record_code){
		$ret = $this->convert_data($record_code);
		if ($ret['status'] < 0){
			return $ret;
		}
		$order = $ret['data'];
		$wms_order = array();
		$wms_order['Company'] = $this->wms_cfg['Company'];
        $wms_order['WareHouse'] = $this->wms_cfg['wms_store_code'];

		$wms_order['ShipmentId'] = $this->is_canceled($record_code);//?客户交易生成物流订单号，客户系统保证唯一（即外部单号）
		$wms_order['TorderCode'] = str_replace(',', '-',  $order['deal_code_list']);
		$wms_order['ShipmentType'] = '销售出库';
		//发货信息
		$wms_order['Carrier'] = $this->db->get_value("select company_code from base_express where express_code = '{$order['express_code']}'");
		$wms_order['Name'] = $this->html_decode($order['receiver_name']);
		$wms_order['PostCode'] = $order['receiver_zip_code'];
		$wms_order['Phone'] = $order['receiver_phone'];
		$wms_order['Mobile'] = $order['receiver_mobile'];
		$wms_order['State'] = $this->get_area_name($order['receiver_province']);
		$wms_order['City'] = $this->get_area_name($order['receiver_city']);
		$wms_order['District'] = $this->get_area_name($order['receiver_district']);
                
                $receiver_street = !empty($order['receiver_street'])?$this->get_area_name($order['receiver_street']):'';
                $receiver_addr = $receiver_street===FALSE?$order['receiver_addr']:$receiver_street.$order['receiver_addr'];

                $wms_order['Address'] = $this->html_decode($receiver_addr);
                
		$wms_order['ShipDate'] = '';
		//发票信息
		$wms_order['Invoice'] = ($order['invoice_status'] == 0)?"N":"Y";
		$wms_order['InvoiceAmount'] = $order['invoice_money'];
		$wms_order['InvoiceType'] = $order['invoice_type'];
		$wms_order['InvoiceName'] = $order['invoice_title'];
		$wms_order['InvoiceContent'] = $order['invoice_content'];
		
		$wms_order['ShopName'] = $this->db->get_value("select shop_name from base_shop where shop_code = '{$order['shop_code']}'");
		$wms_order['Remark'] = $order['order_remark'];
		//商品详细信息
		$sql = "select sku,lof_no,production_date,num from oms_sell_record_lof where record_code = :record_code and record_type = 1";
		$order_lof_goods = $this->db->get_all($sql, array(":record_code" => $order['sell_record_code']));
		//$order_lof_goods = $this->db->get_all($sql, array(":record_code" => '1505130009255'));var_dump($order_lof_goods);
		$TotalQty = 0;
		foreach ($order_lof_goods as $row) {
			$key_arr = array('goods_name','barcode','sell_price');
			$sku_info = load_model('goods/SkuCModel')->get_sku_info($row['sku'],$key_arr);
			$items = array();
			$items['Item'] = $sku_info['barcode'];
			$items['ItemName'] = $sku_info['goods_name'];
			$items['ItemCount'] = $row['num'];
			$TotalQty += $row['num'];
			$items['Sprice'] = $sku_info['sell_price'];
			$items['Lot'] = $row['lof_no'];
			$wms_order['Items'][]['Item'] = $items;
		}
		$wms_order['TotalQty'] = $TotalQty;
		$wms_order['TotalLines'] = count($order_lof_goods);
		
		$method = 'ShipmentRequest';
		$ret = $this->biz_req($method,$wms_order);
		if ($ret['status']>0){
			return $this->format_ret(1,$record_code,'订单发货推送成功');
		}
		return $this->format_ret(-1,$ret['data']['Response'],'订单发货推送失败');//   return  xiugai
	}

                
	function cancel($record_code,$efast_store_code){
//	$ret = $this->convert_data($record_code);
//        if ($ret['status'] < 0){
//        	return $this->format_ret(-1,$ret);
//        }
//        $order = $ret['data'];
        $this->get_wms_cfg($efast_store_code);
        $wms_order = array();
        $wms_order['Company'] = $this->wms_cfg['Company'];
        $wms_order['WareHouse'] = $this->wms_cfg['wms_store_code'];
        $wms_order['ShipmentId'] =  $record_code;
      //  $wms_order['Remark'] = $order['order_remark'];
        $method = 'ShipmentCancelRequest';
        $ret = $this->biz_req($method, $wms_order);
		if ($ret['status']>0){
        	return $this->format_ret(1,$record_code,'订单拦截成功');
        }
        if ($ret['status'] < 0 && strpos($ret['data']['Response']['Description'], "当前订单已经取消") !== false) {
            return $this->format_ret(1, $record_code,'订单拦截成功');
        }
        return $this->format_ret(-1,$ret['data']['Response'],'订单拦截失败');
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
                
                
	//	$ret['express_code'] = $data['Carrier'];
                
               $ret['express_code'] =  $this->get_express_code($data['Carrier']);
                
                
                
		$ret['express_no'] = $data['TrackingNumber'];
		 
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
        
        private function get_express_code($hw_express_code){
            
          $hwwms_express_all =  require_conf("wms/hwwms_express");
          $kh_id = CTX()->saas->get_saas_key();
          $store_code = $this->efast_store_code;
          if(isset($hwwms_express_all[$kh_id][$store_code][$hw_express_code])){
              
              return $hwwms_express_all[$kh_id][$store_code][$hw_express_code];
              
          }else{
              //TODO:记录错误日志
                $logPath = ROOT_PATH."logs/hwwms_express.log";
                error_log(date("Y-m-d H:i:s")."error:kh:".$kh_id."\n store:".$store_code."\n express_code:".$hw_express_code."\n\n", 3, $logPath);
              
          }
          return $hw_express_code;
            
        }

        
        private function get_area_name($id) {
		$sql = "select name from base_area where id=:id";
		return $this->db->get_value($sql, array(':id' => $id));
	}
	
	private function is_canceled($record_code){
		$sql = "select new_record_code from wms_oms_trade where record_code = :record_code and record_type = :record_type";
		$new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
		return !empty($new_record_code)?$new_record_code:$record_code;
	}
}