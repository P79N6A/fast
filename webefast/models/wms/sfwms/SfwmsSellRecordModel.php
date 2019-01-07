<?php

require_model("wms/WmsSellRecordModel");

class SfwmsSellRecordModel extends WmsSellRecordModel {
	public $shipping = null;
    function __construct() {
        parent::__construct();
        $sql = "select express_code,express_name from base_express";
        $shipping_ret = $this->db->get_all($sql);
        $shipps = array();
        foreach ($shipping_ret as $row){
        	$shipps[$row['express_code']] = $row['express_name'];
        }
        $this->shipping = $shipps;
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
        $order = json_decode($json_data, true);
        $check_order = $this->get_record_data($order);
        if ($check_order === false) {
            return $this->format_ret(-1, '', '解密失败，稍后再处理...');
        } 
        $this->get_wms_cfg($order['store_code']);
        
        if (strtolower($order['pay_type']) == 'cod') {
            $payable_money = $order['payable_money'] - $order['paid_money'];
        } else {
            $payable_money = $order['payable_money'];
        }
        $sql = "select pay_type_name from base_pay_type where pay_type_code = '{$order['pay_code']}'";
        $pay_name = (string) $this->db->getOne($sql);

        $sql = "select shop_id,shop_name from base_shop where shop_code = '{$order['shop_code']}'";
        $shop_row = ctx()->db->get_row($sql);
        if (empty($shop_row)) {
            return $this->format_ret(-1, '', '找不到订单对应的网店');
        }


        $wms_order['company'] = $this->wms_cfg['company'];
        $wms_order['warehouse'] = $this->wms_cfg['wms_store_code'];

        $wms_order['shop_name'] = $shop_row['shop_name'];

        $wms_order['erp_order'] = $order['sell_record_code'];
        $wms_order['order_type'] = '销售订单';

        $wms_order['order_date'] = $order['record_time'];

        $wms_order['ship_to_name'] = '个人';
        $wms_order['ship_to_attention_to'] = $this->html_decode($order['receiver_name']);

        $wms_order['ship_to_country'] = $this->get_area_name($order['receiver_country']);
        $wms_order['ship_to_province'] = $this->get_area_name($order['receiver_province']);
        $wms_order['ship_to_city'] = $this->get_area_name($order['receiver_city']);
        $wms_order['ship_to_area'] = $this->get_area_name($order['receiver_district']);
        $wms_order['ship_to_address'] = $this->html_decode($order['receiver_address']);
        $wms_order['ship_to_postal_code'] = $order['receiver_zip_code'];
        $wms_order['ship_to_phone_num'] = $order['receiver_mobile'];
        $wms_order['ship_to_tel_num'] = $order['receiver_phone'];
        $wms_order['freight'] = $order['express_money'];
        $wms_order['cod'] = 'N';
        $wms_order['carrier_service'] = '';
        $wms_order['carrier']=$this->shipping[$order['express_code']];
        if (strtolower($order['pay_type']) == 'cod') {
            $wms_order['cod'] = 'Y';
            $wms_order['amount'] = number_format($order['payable_money'] - $order['paid_money'], 2, '.', '');
        }
        $wms_order['delivery_date'] = $order['plan_send_time']; //发货时间
        if ($wms_order['delivery_date'] == '0000-00-00 00:00:00') {
        	$wms_order['delivery_date'] = date('Y-m-d H:i:s',strtotime($order['record_time'])+3600*24);
        }  
        /*
        //签回单
        $wms_order['return_receipt_service'] = 'N';
        //是否保价
        $wms_order['value_insured'] = 'N';*/
        $wms_order['invoice'] = 'N';
        if (!empty($wms_order['invoice_title'])) {
            $wms_order['invoice'] = 'Y';
            $wms_order['invoice_type'] = $order['invoice_type'];
            $wms_order['invoice_title'] = $order['invoice_title'];
            $wms_order['invoice_content'] = $order['invoice_content'];
        }
        $wms_order['order_note'] = $order['order_remark'];
        $wms_order['company_note'] = $order['seller_remark'];

        $wms_order['order_total_amount'] = $order['payable_money'];

        $wms_order['actual_amount'] = $order['paid_money'];

        if (isset($this->wms_cfg['monthly_account'])) {
            $wms_order['monthly_account'] = $this->wms_cfg['monthly_account'];
        }

        $detailList = array();
        $line_num = 1;
        foreach ($order['goods'] as $row) {
            $order_goods = array();

            $order_goods['erp_order_line_num'] = $line_num;

            $order_goods['item'] = strtoupper($row['barcode']);
            $order_goods['item_name'] = $row['goods_name'];
            $order_goods['uom'] = '个'; //商品单位
            $order_goods['qty'] = $row['num'];
            $item_price = $row['goods_price'];
            $allow_ft = true; //3.0有参数
            if ($allow_ft) {
                $item_price = number_format($row['avg_money'] / $row['num'], '2', '.', '');
            }
            $order_goods['item_price'] = $item_price;
            $order_goods['item_discount'] = number_format($row['goods_price'] - $row['avg_money'] / $row['num'], '2', '.', ''); //优惠价


            $detailList[] = array('item'=>$order_goods);
            $line_num++;
        }
        $data = array('header' => $wms_order, 'detailList' => $detailList);
        //echo '<hr/>$wms_order<xmp>'.var_export($wms_order,true).'</xmp>';die;
        return $this->format_ret(1, $data);
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
        if (strpos($ret['message'], '订单号已存在') !== false){
            return $this->format_ret(1,$ret['data']['orderid']);
        }
        return $ret;
    }

    //状态回传
    function wms_record_info($record_code,$efast_store_code){
                $this->wms_cfg = array();
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
		$status_map = array('900'=>'flow_end','10016'=>'flow_end','10013'=>'cancel');//10012 作废 10013 取消
		$status_txt_map = array('flow_end'=>'已收发货','upload'=>'已上传','wait_upload'=>'未上传');
		//根据顺丰返回的操作日志 解析当前订单所处的订单状态
		$result_status = $this->convert_sf_result_status($result);
		//是否已出库
		$order_status = $result_status['status_code'];
           
                
		$ret['order_status'] = !isset($status_map[$order_status]) ? 'upload' : $status_map[$order_status];
		$ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
		if (isset($status_map[$order_status]) && $status_map[$order_status] == 'flow_end' ) {
			//已出库获取商品明细
			$order = $this->get_wms_record_info_detail($record_code,$efast_store_code);
//                        //021DCR
//                        if($order['data']['remark']=='货主信息不准确！'){
//                            $order = $this->get_wms_record_info_detail($record_code,$efast_store_code);
//                        }
                        
			$order_header = $order['data']['header'];
			$ret['efast_record_code'] = $order_header['erp_order'];
			$ret['wms_record_code'] = $order_header['shipment_id'];
			$ret['wms_store_code'] = $efast_store_code;
			$ret['express_code'] = array_search($order_header['carrier'], $this->shipping);
			$ret['express_no'] =  $order_header['waybill_no'];
			
			//发货时间
			if (isset($order_header['actual_ship_date_time'])) {
				$flow_end_time = $order_header['actual_ship_date_time'];
			} elseif (isset($order_header['status_time'])){
				$flow_end_time = $order_header['status_time'];
			}
			$ret['flow_end_time'] = $flow_end_time;
			
			$goods_ret = $order['data']['detailList']['item'];
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
       $this->wms_cfg = array();
        $this->get_wms_cfg($efast_store_code);
        $method = 'wmsSailOrderQueryService';

        $wms_store_code = $this->wms_cfg['wms_store_code'];
 
        
        $req = array( 'company' => $this->wms_cfg['company'],'warehouse'=>$wms_store_code,'orderid' => $record_code,);
        $ret = $this->biz_req($method, $req);
        return $ret;  
    }
    
    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'wmsCancelSailOrderService';
        $req = array('orderid' => $record_code, 'company' => $this->wms_cfg['company']);
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0 && strpos($ret['data']['error'], "未找到") !== false) {
            return $this->format_ret(1, $ret['data']);
        }
        if ($ret['status'] < 0 && strpos($ret['data']['error'], "已作废") !== false) {
        	return $this->format_ret(1, $ret['data']);
        }
        return $ret;
    }

    
    
    
    private function get_area_name($id) {
        $sql = "select name from base_area where id=:id";
        return $this->db->get_value($sql, array(':id' => $id));
    }

}
