<?php

require_model('wms/ydwms/YdwmsAPIModel');

class YdwmsOpenAPIModel extends YdwmsAPIModel {

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
        $ret = $this->check_sign($request);
        if ($ret !== true) {
            return $ret;
        }
        $method = $request['method'];
        if (method_exists($this, $method)) {
            $data = $this->xml2array($request['data']);

            return $this->$method($data);
        } else {
            return $this->return_info('0001', '找不到指定方法');
        }
    }

//固定值 “inventoryReport”
//固定值 “IRC”

    function confirmSOStatus(&$data) {
        //暂时不实现
        return $this->return_info('0000');
    }

    /*
     * 出库单据回传
     */

    function confirmSOData(&$data) {

        $order_info_data = &$data['xmldata']['data']['orderinfo'];
        if (isset($order_info_data['CustomerID'])) {
            $order_info_data = array($order_info_data);
        }
        $errer_data = array();
        foreach ($order_info_data as $order_info) {
            $ret = $this->save_send_info($order_info);
            if ($ret['status'] < 0) {
                $errer_data[] = array('OrderNo' => $order_info['OMSOrderNo'],
                    'OrderType' => $order_info['OrderType'],
                    'CustomerID' => $order_info['CustomerID'],
                    'WarehouseID' => $order_info['WarehouseID'],
                    'errorcode' => '0001',
                    'errordescr' => $ret['message']);
            }
        }
        if (!empty($errer_data)) {
            return $this->return_info('0001', '存在部分错误', $errer_data);
        } else {
            return $this->return_info('0000');
        }
    }

    /*
     * 入库单据回传
     */

    function confirmASNData(&$data) {

        $order_info_data = &$data['xmldata']['data']['orderinfo'];

        if (isset($order_info_data['CustomerID'])) {
            $order_info_data = array($order_info_data);
        }

        //return $this->return_info('0000');
        $errer_data = array();
        foreach ($order_info_data as $order_info) {

            $ret = $this->save_order_in_info($order_info);
            if ($ret['status'] < 0) {
                $errer_data[] = array('OrderNo' => $order_info['OMSOrderNo'],
                    'OrderType' => $order_info['OrderType'],
                    'CustomerID' => $order_info['CustomerID'],
                    'WarehouseID' => $order_info['WarehouseID'],
                    'errorcode' => '0001',
                    'errordescr' => $ret['message']);
            }
        }
        if (!empty($errer_data)) {
            return $this->return_info('0001', '存在部分错误', $errer_data);
        } else {
            return $this->return_info('0000');
        }
    }

    function save_order_in_info(&$order_info) {

        $type_arr = array('PO' => 'pur_notice', 'RS' => 'sell_return');
        if (isset($type_arr[$order_info['OrderType']])) {
            $record_type = $type_arr[$order_info['OrderType']];
        } else {
            return array('status' => -1, 'message' => '暂时不支持此类型回传');
        }
        //如果是取消上传的  需匹配原始单号
		$record_code = $this->is_canceled($order_info['OMSOrderNo'],$record_type);
		$order_info['OMSOrderNo'] = $record_code;
        //$record_code = $order_info['OMSOrderNo'];
        $ret_data = array();

        $ret_data['data']['efast_record_code'] = $record_code;
        $ret_data['data']['wms_record_code'] = $order_info['WMSOrderno'];
        $ret_data['data']['wms_store_code'] = $order_info['WarehouseID'];

        //99 强制完成  40完全收货
        if ($order_info['Status'] == '40' || $order_info['Status'] == '99') {
            $ret_data['data']['order_status'] = 'flow_end';
            $ret_data['data']['order_status_txt'] = '已收发货';
        } else {
            //不准确，属于部分发货
            $ret_data['data']['order_status'] = 'upload';
            $ret_data['data']['order_status_txt'] = '已上传';
        }

        $ret_data['data']['flow_end_time'] = date('Y-m-d H:i:s');
        //  $ret_data['data']['goods'] = array();
        $item = $order_info['item'];
        if (isset($item['SKU'])) {
            $order_info['item'] = array($item);
        }
        $goods_info_zp = array();
         $goods_info_cp = array();
      //  <UserDefine3>ZP</UserDefine3>
        foreach ($order_info['item'] as $val) {
            if(isset($val['UserDefine3'])&&$val['UserDefine3']=='CC'){
                $goods_info_cp[] = array('sl' => $val['ReceivedQty'], 'barcode' => $val['SKU']);
            }else{
                $goods_info_zp[] = array('sl' => $val['ReceivedQty'], 'barcode' => $val['SKU']);
            }
        }

        if (!empty($goods_info_zp)) {
            //收货商品同步
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_zp);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }
       if (!empty($goods_info_cp)) {
            //收货商品同步
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_cp,0);
            if ($ret['status'] < 0) {
                return $ret;
            }
        } 
        

        //完成入库回传
        if ($ret_data['data']['order_status'] == 'flow_end') {
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data, 1);
        }

        return $ret;
    }

    function save_send_info(&$order_info) {

        $type_arr = array('SO' => 'sell_record', 'RP' => 'pur_return_notice', 'TO' => 'wbm_notice');
        if (isset($type_arr[$order_info['OrderType']])) {
            $record_type = $type_arr[$order_info['OrderType']];
        } else {
            return array('status' => -1, 'message' => '暂时不支持此类型回传');
        }
        $record_code = $this->is_canceled($order_info['OMSOrderNo'],$record_type);
        $order_info['OMSOrderNo'] = $record_code;
        //$record_code = $order_info['OMSOrderNo'];
        $ret_data = array();
        $ret_data['data']['express_code'] = $order_info['CarrierId'];
        $ret_data['data']['express_no'] = $order_info['DeliveryNo'];
        $ret_data['data']['flow_end_time'] = date('Y-m-d H:i:s');

        $ret_data['data']['order_weight'] = isset($order_info['Weight']) ? $order_info['Weight'] : 0;


        $ret_data['data']['goods'] = array();
        $item = $order_info['item'];
        if (isset($item['OrderNo'])) {
            $order_info['item'] = array($item);
        }
        foreach ($order_info['item'] as $val) {
            $ret_data['data']['goods'][] = array('sl' => $val['QtyShipped'], 'barcode' => $val['SKU']);
        }
        return load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data);
    }

    //调整单对接
    function inventoryReport(&$data) {
        $order_info_data = &$data['xmldata']['data']['orderinfo'];
        $record_data['wms_record_code'] = $order_info_data['checkOrderCode'];
        $record_data['process_time'] = $order_info_data['checkTime'];
        $record_data['wms_store_code'] = $order_info_data['WarehouseID'];
        $order_zp = array();
        $order_cp = array();
        $item = $order_info_data['items']['item'];
        if (isset($item['itemCode'])) {
            $item = array($item);
        }

        foreach ($item as $val) {
            if (strtoupper($val['inventoryType']) == 'ZP') {
                $order_zp[] = array('barcode' => $val['itemCode'], 'sl' => $val['quantity']);
            } else {
                $order_cp[] = array('barcode' => $val['itemCode'], 'sl' => $val['quantity']);
            }
        }
        if (!empty($order_zp)) {
            load_model('wms/WmsInvModel')->create_inv_order($record_data, $order_zp, 'adjust');
        }

        if (!empty($order_cp)) {
            load_model('wms/WmsInvModel')->create_inv_order($record_data, $order_cp, 'adjust', 0);
        }

        return $this->return_info('0000');
    }

    function check_sign($request) {

        $this->save_log($request);


        if (empty($request['warehouseid'])) {
            return $this->return_info('0001', '找不到对应仓库');
        }

        $status = $this->set_wms_params($request['customerid']);
        if ($status === FALSE) {
            return $this->return_info('0001', '找不到对应仓库');
        }
        $sign = $this->sign($request['data']);

        if ($sign != $request['sign']) {
            return $this->return_info('0001', '加密效验失败');
        }

        return true;
    }

    function set_wms_params($customerid) {
        $sql = "select wms_params from wms_config w 
            INNER JOIN sys_api_shop_store a ON w.wms_config_id=a.p_id
            where a.p_type=1 AND w.wms_system_code=:wms_system_code and a.store_type = 1  ";

        $data = $this->db->get_all($sql, array(':wms_system_code' => 'ydwms'));

        if (!empty($data)) {
            foreach ($data as $val) {
                $api_data = $val['wms_params'];
                $token = json_decode($api_data, true);
                if ($token['customerid'] == $customerid) {
                    $this->set_token($token);
                    return true;
                }
            }
        }
        return FALSE;
    }

    function save_log($request) {

        $data = array('type' => 'ydwms', 'method' => $request['method'], 'url' => $request['method'], 'add_time' => date('Y-m-d H:i:s'));
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
            '0000' => array('returnCode' => '0000', 'returnDesc' => 'ok', 'returnFlag' => '1'),
            '0001' => array('returnCode' => '0001', 'returnDesc' => '', 'returnFlag' => '0'),
        );

        $ret['return'] = &$return[$status];
        if (!empty($message)) {
            $ret['return']['returnDesc'] = $message;
        }

        if (!empty($ret_data)) {
            $ret['return']['returnFlag'] = 2;
            foreach ($ret_data as $val) {
                $ret['return'][] = array('resultInfo' => $val);
            }
        }

        $ret = $this->array2xml($ret, 'Response');
        $this->update_log($ret);
        return $ret;
    }

	private function is_canceled($record_code,$record_type){
		$type_arr = array('sell_return','sell_record');
		if (in_array($record_type, $type_arr)){
			$table = 'wms_oms_trade';
		} else {
			$table = 'wms_b2b_trade';
		}
		$sql = "select record_code from {$table} where new_record_code = :new_record_code and record_type = :record_type";
		$old_record_code = ctx()->db->getOne($sql, array(':new_record_code' => $record_code, ':record_type' => $record_type));
		return !empty($old_record_code)?$old_record_code:$record_code;
	}
    
}
