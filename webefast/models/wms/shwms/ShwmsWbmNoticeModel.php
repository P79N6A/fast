<?php

require_model("wms/WmsWbmNoticeModel");

class ShwmsWbmNoticeModel extends WmsWbmNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'wbm_notice'));
        $order = json_decode($json_data, true);
        $this->get_wms_cfg($order['store_code']);
  //              'ref': '1847951466255879-5',
//          'channel_code': 'ec',
//          'trade_pattern': 'b1',
//          'date_order': '2016-07-01 10:20:46',
//          'buyer': 'hxy@qq.com', 
//          'seller': '18623651929',
//          'receive_info': '黄*友|50038419****200316|159****3573|中国|重庆|重庆市|南岸区|重庆市重庆市南岸区南坪亚太商谷*栋***',
//          'total_import_taxes': 4.64,
//          'amount_untaxed': 29.0,
//          'amount_total': 43.64,
//          'delivery_fee': 10.0,
//          'buyer_reg_no': '15923373573',
//          'buyer_name': '黄学友',
//          'buyer_id_type': '1',
//          'buyer_id_card': '500384198602200316',
        $store_info = $this->db->get_row("select * from base_store where store_code='{$order['store_code']}'");
        $wms_order = array();
        $wms_order['ref'] = $record_code;
        $wms_order['channel_code'] = 'ec'; //需要确定
        $wms_order['trade_pattern'] = 'n2'; //需要确定
        $wms_order['buyer'] = $order['distributor']['mobile']; //分销商
        $wms_order['seller'] = $store_info['contact_phone']; //仓库
        $wms_order['receive_info'] = $order['distributor']['contact_person']."|".$order['distributor']['remark']."|".$order['distributor']['mobile']."|".$order['distributor']['address']; 
        $wms_order['date_order'] = $order['order_time'];
        $wms_order['delivery_fee'] = 0;
        $wms_order['amount_untaxed'] = 0;
        $wms_order['total_import_taxes'] = 0;

        $wms_order['amount_total'] = $order['money'];
        $wms_order['buyer_reg_no'] =  $order['distributor']['custom_code'];
        $wms_order['buyer_name'] = $order['distributor']['custom_name'];
        $wms_order['buyer_id_type'] = 1;
        $wms_order['buyer_id_card'] = '500384198602200316';

        $sql = "select s.barcode,d.lof_no from b2b_lof_datail d
            INNER JOIN goods_sku s ON d.sku=s.sku
            where d.order_code='{$record_code}' AND d.order_type ='wbm_notice'";
        $data_detail = $this->db->get_all($sql);
        $lof_barcode = array();
        foreach($data_detail as $v){
            $lof_barcode[$v['barcode']][] = $v['lof_no'];
        }

       	$order_goods = array();

        foreach ($order['goods'] as $row) {
                    $t_row = array();

            $t_row['sku'] = $row['barcode'];
             //   $t_row['sku'] = 'P0051592'; //测试
            $b2b_lots = $lof_barcode[$row['barcode']];
            $t_row['num'] = $row['num'];
            $t_row['price'] = $row['price'];
            $t_row['b2b_lots'] = implode(",", $b2b_lots);
			//$t_row['b2b_lots'] =  'b2b_lots': '1111-none,2222-b,333-a';
            $t_row['sub_total_untaxed'] = $row['price']*$row['num'];
            $t_row['sub_total'] = $t_row['sub_total_untaxed'];
            $t_row['tax_fee'] = 0;
            
            $wms_order['order_line'][] = $t_row; 
           // break;//test

        }
        $wms_order['payment_line'][] = array(
               'payment_account'=>'bs01',
               'payamount'=>$order['money'],
               'payment_no'=>$record_code,
        );

    
        
        $data = array('order' => $wms_order);
        return $this->format_ret(1, $data);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
	if ($ret['status']<0){
			return $ret;
		}
		$wms_order = $ret['data'];
        $method = 'erp.trade.add';
        //  var_dump($method, $wms_order);die;
        $ret = $this->biz_req($method, $wms_order);

        if ($ret['status']>0&&isset($ret['data']['trade']['id'])){
            return $this->format_ret(1,$ret['data']['trade']['id']);
        }

	return $ret;
    }

    function cancel($record_code, $efast_store_code) {
	$this->get_wms_cfg($efast_store_code);
		$method = 'taobao.qimen.order.cancel';
                $wms_record_code = $this->get_wms_id($record_code);
		$req = array('warehouseCode'=>$this->wms_cfg['wms_store_code'],'ownerCode'=>$this->wms_cfg['owner_code'],'orderCode'=>$record_code,'orderId'=>$wms_record_code,'orderType'=>'B2BCK');
		$ret = $this->biz_req($method,$req);
	
             	if ($ret['status']<0){
			return $this->format_ret(-1,'', $ret['message']);
		}
           
                if ($ret['data']['flag']=='success'){
			$ret =  $this->format_ret(1,$ret['data']);
                }else{
                    $ret =  $this->format_ret(-1,'',$ret['data']['message']);
                }
		return $ret;
    }

    function wms_record_info($record_code, $efast_store_code) {
        
       $ret =  array('status'=>-1,'data'=>array(),'message'=>'不支持获取');
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
          function get_wms_id($record_code){
           return $this->db->get_value("select wms_record_code from wms_b2b_trade where (record_code='{$record_code}'  OR new_record_code='{$record_code}'  )  and record_type = 'wbm_notice'" );
        
            
        }

    private function is_canceled($record_code){
    	$sql = "select new_record_code from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
    	$new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'wbm_notice'));
    	return !empty($new_record_code)?$new_record_code:$record_code;
    }
    private function check_area($address,$type=2,$parent_id = 1){
        $sql = "select id,name from base_area where  type=:type AND parent_id=:parent_id ";
        $sql_value = array(':type'=>$type,':parent_id'=>$parent_id);
        $data = $this->db->get_all($sql,$sql_value);
        $area_arr = array();
        foreach($data as $val){
            if(strpos($address, $val['name'])!==false){
               $area_arr = $val;
               break;
            }
        }
        return $area_arr;
    }
    
}
