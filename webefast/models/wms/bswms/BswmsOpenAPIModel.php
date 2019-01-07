<?php

require_model('wms/bswms/BswmsAPIModel');

class BswmsOpenAPIModel extends BswmsAPIModel {

    protected $db;
    private $log_id = 0;

    function __construct() {
        parent::__construct();
        $this->db = CTX()->db;
    }

    function get_wms_params_by_out_store($out_store) {

        $sql = " select  c.wms_params  from wms_config c  
    INNER JOIN sys_api_shop_store s ON c.wms_config_id=s.p_id
    where s.outside_code='{$out_store}' AND s.p_type=1 AND s.outside_type=1 and s.store_type = 1 ";

        $row = $this->db->get_row($sql);
        $data = array();
        if (!empty($row)) {
            $data = json_decode($row['wms_params'], true);
            $data ['outside_code'] = $out_store;
        }
        return $data;
    }

    function exec_api($request) { 
//     	var_dump($request);exit;
        //$ret = $this->check_sign($request);//未完成 （未测试）
//         if ($ret !== true) {
//             return $ret;
//         }
        $method = $request['method'];
        if (method_exists($this, $method)) {
            $data = $this->xml2array($request['bizData']);
            $request['data'] = $data;
            $ret = $this->check_sign($request,$method);
            if ($ret !== true) {
				return $ret;
			}
            return $this->$method($data);
        } else {
            return $this->return_info('0001', '找不到指定方法');
        }
    }

    /*
     * 零售接收发货信息，采购退货信息接收
     */

    function UpdateSalesOrderStatus(&$data) {
    	//var_dump($data);exit();
        $order_info_data = &$data['UpdateSalesOrderStatus'];
        $errer_data = array();
        $ret = $this->save_send_info($order_info_data);
        if ($ret['status'] < 0) {
        	$errer_data[] = array(
        			'errorCode' => 500,
        			'errorDescription' => $ret['message']
        	);
        }

        if (!empty($errer_data)) {
            return $this->return_info('0001', '存在部分错误', $errer_data);
        } else {
            return $this->return_info('0000');
        }
    }

    /*
     * 退货单信息同步
     */

    function UpdateRmaStatus(&$data) {

        $order_info_data = &$data['UpdateRmaStatus'];
    	if (isset($order_info_data['customerCode'])) {
            $order_info_data = array($order_info_data);
        }
          //return $this->return_info('0000');
        $errer_data = array();
        foreach ($order_info_data as $order_info) {
            $ret = $this->save_order_in_info($order_info);
            if ($ret['status'] < 0) {
                $errer_data[] = array(
                    'errorCode' => 500,
                	'errorDescription' => $ret['message']
                );
            }
        }
        if (!empty($errer_data)) {
            return $this->return_info('0001', '存在部分错误', $errer_data);
        } else {
            return $this->return_info('0000');
        }
    }

    function save_order_in_info(&$order_info) {
        $record_type = 'sell_return';
        $record_code = $order_info['rmaCode'];
        $ret_data = array();
        $ret_data['data']['efast_record_code'] = $order_info['rmaCode'];
        //$ret_data['data']['wms_record_code'] = $order_info['rmaCode'];
        $ret_data['data']['wms_store_code'] = $order_info['wareHouseCode'];
        $ret_data['data']['express_code'] = $order_info['logisticsProviderCode'];
        $ret_data['data']['express_no'] = $order_info['shippingOrderNo'];
		//99 强制完成  40完全收货
        if ($order_info['rmaStatus'] == 'FULFILLED' || $order_info['rmaStatus'] == 'CLOSED') {
            $ret_data['data']['order_status'] = 'flow_end';
            $ret_data['data']['order_status_txt'] = '已收发货';
        } else {
            //不准确，属于部分发货
            $ret_data['data']['order_status'] = 'upload';
            $ret_data['data']['order_status_txt'] = '已上传';
        }

        $ret_data['data']['flow_end_time'] = date('Y-m-d H:i:s');
        //  $ret_data['data']['goods'] = array();
        $item = $order_info['products']['product'];
        if (isset($item['skuCode'])) {
        	$order_info['products']['product'] = array($item);
        }
        $goods_info = array();
        foreach ($order_info['products']['product'] as $val) {
        	$goods_info[] = array('sl' => ($val['normalQuantity']+$val['defectiveQuantity']), 'barcode' => $val['skuCode']);
        }
        if(!empty($goods_info)){
            //收货商品同步
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }
        //完成入库回传
        if ($ret_data['data']['order_status'] == 'flow_end') {
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data,1);
        }
        return $ret;
    }

    //接收入库单收货信息
    function UpdateAsnStatus(&$data){
    	$order_info_data = &$data['UpdateAsnStatus'];
    	$errer_data = array();
    	$ret = $this->save_pur_notice_info($order_info_data,1);
    	if ($ret['status'] < 0) {
    		$errer_data[] = array(
    			'errorCode' => 500,
    			'errorDescription' => $ret['message']
    		);
    	}
    	
    	
    	if (!empty($errer_data)) {
    		return $this->return_info('0001', '存在部分错误', $errer_data);
    	} else {
    		return $this->return_info('0000');
    	}
    }
    
    function save_pur_notice_info(&$order_info){
    	$record_type = 'pur_notice';
    	$record_code = $order_info['asnCode'];
    	$ret_data = array();
    	//$ret_data['data']['efast_record_code'] = $order_info['rmaCode'];
    	//$ret_data['data']['wms_record_code'] = $order_info['rmaCode'];
    	$ret_data['data']['wms_store_code'] = $order_info['wareHouseCode'];
    	$ret_data['data']['express_code'] = isset($order_info['logisticsProviderCode'])?$order_info['logisticsProviderCode']:'';
    	$ret_data['data']['express_no'] = isset($order_info['shippingOrderNo'])?$order_info['shippingOrderNo']:'';
    	//99 强制完成  40完全收货
    	if ($order_info['asnStatus'] == 'FULFILLED' || $order_info['asnStatus'] == 'CLOSED') {
    		$ret_data['data']['order_status'] = 'flow_end';
    		$ret_data['data']['order_status_txt'] = '收货完成';
    	} else {
    		//不准确，属于部分发货
    		$ret_data['data']['order_status'] = 'upload';
    		$ret_data['data']['order_status_txt'] = '收货中';
    	}
    	
    	$ret_data['data']['flow_end_time'] = date('Y-m-d H:i:s');
    	
    	$item = $order_info['products']['product'];
    	if (isset($item['skuCode'])) {
    		$order_info['products']['product'] = array($item);
    	}
    	$goods_info = array();
    	foreach ($order_info['products']['product'] as $val) {
            $goods_info[] = array('sl' => ($val['normalQuantity']+$val['defectiveQuantity']), 'barcode' => $val['skuCode']);
        }
    	if(!empty($goods_info)){
    		//收货商品同步
    		$ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info);
    		if ($ret['status'] < 0) {
    			return $ret;
    		}
    	}
    	
    	//完成入库回传
    	if ($ret_data['data']['order_status'] == 'flow_end') {
    		$ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data,1);
    	}
    	return $ret;
    }
    
    function save_send_info(&$order_info) {
        $type_arr = array('NORMAL' => 'sell_record', 'WDO' => 'pur_return_notice');//需要问
        if (isset($type_arr[$order_info['orderType']])) {
            $record_type = $type_arr[$order_info['orderType']];
        } else {
            return array('status' => -1, 'message' => '暂时不支持此类型回传');
        }
        $record_code = $order_info['orderCode'];
        $ret_data = array();
        $ret_data['data']['express_code'] = $order_info['logisticsProviderCode'];
        $ret_data['data']['express_no'] = $order_info['shippingOrderNo'];
        $ret_data['data']['flow_end_time'] = date('Y-m-d H:i:s');
        $ret_data['data']['order_weight'] =  isset($order_info['weight'])?$order_info['weight']:0;
        $ret_data['data']['goods'] = array();
        $item = $order_info['products']['product'];
        if (isset($item['skuCode'])) {
            $order_info['products']['product'] = array($item);
        }
        foreach ($order_info['products']['product'] as $val) {
            $ret_data['data']['goods'][] = array('sl' => ($val['normalQuantity']+$val['defectiveQuantity']), 'barcode' => $val['skuCode']);
        }

        return load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data);
    }

    function check_sign($data,$method) {
        $this->save_log($data,$method);
        if (empty($data['data']['UpdateRmaStatus']['wareHouseCode'])) {
            return $this->return_info('0001', '找不到对应仓库');
        }
 
       $status =  $this->set_wms_params($data['data']['UpdateRmaStatus']['customerCode']);
        if($status===FALSE){
             return $this->return_info('0001', '找不到对应客户');
        }
        $sign = $this->sign($data);
    
        if ($sign != $data['sign']) {
            return $this->return_info('0001', '加密效验失败');
        }

        return true;
    }
    function set_wms_params($customerid){
        $sql = "select wms_params from wms_config w 
            INNER JOIN sys_api_shop_store a ON w.wms_config_id=a.p_id
            where a.p_type=1 AND w.wms_system_code=:wms_system_code and a.store_type = 1  ";

        $data = $this->db->get_all($sql,array(':wms_system_code'=>'bswms'));

        if(!empty($data)){
            foreach($data as  $val){
                $api_data = $val['wms_params'];
                $token  = json_decode($api_data,true);
                if($token['customerCode']==$customerid){
                    $this->set_token($token);
                    return true;                    
                }
            }
        }
        return FALSE;
    }

    function save_log($request,$method) {
        
        $data = array('type' => 'bswms', 'method' => $method, 'url' => $method,'add_time'=>  date('Y-m-d H:i:s'));
        $data['post_data'] = $request['data'];
        $data['params'] = json_decode($request);
        
        $this->db->insert('api_open_logs', $data);
        $this->log_id = $this->db->insert_id();
    }

    function update_log($return) {
        if ($this->log_id != 0) {
            $this->db->update('api_open_logs', array('return_data' => $return), "id = '{$this->log_id }'");
        }
    }

    function return_info($status, $message = '', $ret_data = array()) {

        $return = array(
            '0000' => array('flag' => 'SUCCESS'),
            '0001' => array('flag' => 'FAILURE'),
        );

        $ret = &$return[$status];
        if (!empty($message)) {
            $ret['note'] = $message;
        }

        if (!empty($ret_data)) {
            foreach ($ret_data as $val) {
                $ret['errors'][] = array('error' => $val);
            }
        }

        $ret = $this->array2xml($ret, 'UpdateSalesOrderStatusRsp');
        $this->update_log($ret);
        return $ret;
    }

}
