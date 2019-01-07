<?php

require_model('wms/iwmscloud/IwmscloudAPIModel');

class IwmscloudOpenAPIModel extends IwmscloudAPIModel {

    protected $db;
    private $log_id = 0;
    private $req_data_xml;
    private $log_unid = '';

    function __construct($token = array()) {
        parent::__construct($token);
        $this->db = CTX()->db;
    }

    function get_wms_params_by_out_store($out_store) {

        $sql = " select  c.wms_params  from wms_config c  
    INNER JOIN sys_api_shop_store s ON c.wms_config_id=s.p_id
    where s.outside_code='{$out_store}' AND s.p_type=1 AND s.outside_type=1  ";

        $row = $this->db->get_row($sql);
        $data = array();
        if (!empty($row)) {
            $data = json_decode($row['wms_params'], true);
            $data ['outside_code'] = $out_store;
        }
        return $data;
    }

    function get_efast_store_code($wms_store_code) {
        $sql = " select  s.shop_store_code  from wms_config c  
    INNER JOIN sys_api_shop_store s ON c.wms_config_id=s.p_id
    where s.outside_code='{$wms_store_code}' AND s.p_type=1 AND s.outside_type=1  AND c.wms_sys_code='iwmscloud'   ";

        $row = $this->db->get_row($sql);
        return isset($row['shop_store_code']) ? $row['shop_store_code'] : '';
    }

    function exec($request) {

        $ret = $this->check_sign($request);
        if ($ret !== true) {
            return $ret;
        }
        //entryorder.confirm 

        $method = $request['service'];
        $action_method = str_replace('.', '_', $method);

        if (method_exists($this, $action_method)) {
            $data = &$request['bizdata'];
            return $this->$action_method($data);
        } else {
            return $this->return_info(-1, '找不到指定方法');
        }
    }

    //入库单确认接口 
    function ewms_orderreturnstatus_get(&$data) {

        $data = &$data['request'];
        $ret_data = load_model("wms/iwmscloud/IwmscloudSellReturnModel")->conv_wms_record_info($data);

        //完成入库回传
        if ($ret_data['data']['order_status'] == 'flow_end') {
            $efast_record_code = $ret_data['data']['efast_record_code'];
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($efast_record_code, 'sell_return', $ret_data);
        }
        return $this->return_info($ret['status'], $ret['message']);
    }

    //出库单确认接口
    function ewms_orderstatus_get(&$data) {
        $data = &$data['request'];
        $ret_data = load_model("wms/iwmscloud/IwmscloudSellRecordModel")->conv_wms_record_info($data);

        //完成入库回传
        if ($ret_data['data']['order_status'] == 'flow_end') {
            $efast_record_code = $ret_data['data']['efast_record_code'];
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($efast_record_code, 'sell_record', $ret_data);
        }
        return $this->return_info($ret['status'], $ret['message']);
    }

    //退货入库单确认接口 
    function returnorder_confirm(&$data) {
        $data = &$data['request'];

        $check_un = $this->check_log_unid($data['returnOrder']['outBizCode']);
        if ($check_un) {
            return $this->return_info(-1, '重复请求！');
        }

        $ret_data = array();

        $ret_data['data']['order_status'] = 'flow_end';
        $ret_data['data']['order_status_txt'] = '已收发货';


        $record_code = $data['returnOrder']['returnOrderCode'];
        $record_type = 'sell_return';

        $ret_data['data']['efast_record_code'] = $data['returnOrder']['returnOrderCode'];
        $ret_data['data']['wms_record_code'] = $data['returnOrder']['returnOrderId'];
        $ret_data['data']['wms_store_code'] = $data['returnOrder']['warehouseCode'];
        $ret_data['data']['flow_end_time'] = $data['returnOrder']['orderConfirmTime'];
        $ret_data['data']['express_code'] = $data['returnOrder']['logisticsCode'];
        $ret_data['data']['express_no'] = $data['returnOrder']['expressCode'];

        if (isset($data['orderLines']['orderLine']['itemCode'])) {
            $data['orderLines']['orderLine'] = array($data['orderLines']['orderLine']);
        }
//        foreach ($data['orderLines']['orderLine'] as $val) {
//            $ret_data['data']['goods'][] = array('sl' => $val['actualQty'], 'barcode' => $val['itemCode']);
//        }
//        //收货商品同步
//        $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $ret_data['data']['goods']);
//        
        foreach ($data['orderLines']['orderLine'] as $val) {

            $batchs_arr = $val['batchs']['batch'];
            if (isset($batchs_arr['inventoryType'])) {
                $batchs_arr = array($batchs_arr);
            }
            foreach ($batchs_arr as $item) {
                if (isset($item['inventoryType']) && $item['inventoryType'] == 'ZP') {
                    $goods_info_zp[] = array('sl' => $item['actualQty'], 'barcode' => $val['itemCode']);
                } else {
                    $goods_info_cp[] = array('sl' => $item['actualQty'], 'barcode' => $val['itemCode']);
                }
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
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_cp, 0);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }



        $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data, 1);

        return $this->return_info($ret['status'], $ret['message']);
    }

    //发货单确认接口  
    function deliveryorder_confirm(&$data) {

        $data = &$data['request'];

        $check_un = $this->check_log_unid($data['deliveryOrder']['outBizCode']);
        if ($check_un) {
            return $this->return_info(-1, '重复请求！');
        }

        $ret_data = array();
        if (strtoupper($data['deliveryOrder']['status']) == 'DELIVERED') {
            $ret_data['data']['order_status'] = 'flow_end';
            $ret_data['data']['order_status_txt'] = '已收发货';
        } else {
            //不准确，属于部分发货
            $ret_data['data']['order_status'] = 'upload';
            $ret_data['data']['order_status_txt'] = '已上传';
        }

        $record_code = $data['deliveryOrder']['deliveryOrderCode'];
        $record_type = 'sell_record';

        $ret_data['data']['efast_record_code'] = $data['deliveryOrder']['deliveryOrderCode'];
        $ret_data['data']['wms_record_code'] = $data['deliveryOrder']['deliveryOrderId'];
        $ret_data['data']['wms_store_code'] = $data['deliveryOrder']['warehouseCode'];
        $ret_data['data']['flow_end_time'] = $data['deliveryOrder']['orderConfirmTime'];

        if (isset($data['packages']['package']['logisticsCode'])) {
            $data['packages']['package'] = array($data['packages']['package']);
        }
        foreach ($data['packages']['package'] as $p_val) {
            $ret_data['data']['express_code'] = $p_val['logisticsCode'];
            $ret_data['data']['express_no'] = $p_val['expressCode'];
        }

        if (isset($data['orderLines']['orderLine']['itemCode'])) {
            $data['orderLines']['orderLine'] = array($data['orderLines']['orderLine']);
        }
//        foreach ($data['orderLines']['orderLine'] as $val) {
//            $ret_data['data']['goods'][] = array('sl' => $val['actualQty'], 'barcode' => $val['itemCode']);
//        }
//
//        $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $ret_data['data']['goods']);

        foreach ($data['orderLines']['orderLine'] as $val) {

            foreach ($val['batchs']['batch'] as $item) {
                if (isset($item['inventoryType']) && $item['inventoryType'] == 'ZP') {
                    $goods_info_zp[] = array('sl' => $item['actualQty'], 'barcode' => $val['itemCode']);
                } else {
                    $goods_info_cp[] = array('sl' => $item['actualQty'], 'barcode' => $val['itemCode']);
                }
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
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info_cp, 0);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }

        if ($ret_data['data']['order_status'] == 'flow_end') {
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data, 1);
        }

        return $this->return_info($ret['status'], $ret['message']);
    }

    //发货单SN通知接口  
    function sn_report(&$data) {
        return $this->return_info(1, '');
    }

    function inventory_report(&$data) {
        $data = &$data['request'];
        $record_data['wms_record_code'] = $data['checkOrderCode'];
        $record_data['process_time'] = $data['checkTime'];
        $record_data['wms_store_code'] = $data['warehouseCode'];
        $order_zp = array();
        $order_cp = array();
        $item = $data['items']['item'];
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

        return $this->return_info(1);
    }

    function save_order_in_info(&$order_info) {

        $type_arr = array('PO' => 'pur_notice', 'RS' => 'sell_return');
        if (isset($type_arr[$order_info['OrderType']])) {
            $record_type = $type_arr[$order_info['OrderType']];
        } else {
            return array('status' => -1, 'message' => '暂时不支持此类型回传');
        }


        $record_code = $order_info['OMSOrderNo'];
        $ret_data = array();

        $ret_data['data']['efast_record_code'] = $order_info['OMSOrderNo'];
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
        $goods_info = array();
        foreach ($order_info['item'] as $val) {
            $goods_info[] = array('sl' => $val['ReceivedQty'], 'barcode' => $val['SKU']);
        }

        if (!empty($goods_info)) {
            //收货商品同步
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info);
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
        $record_code = $order_info['OMSOrderNo'];
        $ret_data = array();
        $ret_data['data']['express_code'] = $order_info['CarrierId'];
        $ret_data['data']['express_no'] = $order_info['DeliveryNo'];
        $ret_data['data']['flow_end_time'] = date('Y-m-d H:i:s');
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

    function check_sign($request) {

        $this->save_log($request);
        $status = $this->set_wms_params($request['accesskey']);
        if ($status === FALSE) {
            return $this->return_info(-1, '找不到对应仓库');
        }
        $url_data = array(
            'accesskey' => $request['accesskey'],
            'service_ver' => $request['service_ver'],
            'v' => $request['v'],
            'sign_method' => $request['sign_method'],
            'timestamp' => $request['timestamp'],
            'service' => $request['service'],
            'bizdata' => $request['bizdata'],
        );

        $sign = $this->sign($url_data);

        if ($sign != $request['sign']) {
            return $this->return_info(-1, '加密效验失败');
        }

        return true;
    }

    function set_wms_params($accesskey) {
        $sql = "select wms_params from wms_config w 
            INNER JOIN sys_api_shop_store a ON w.wms_config_id=a.p_id
            where a.p_type=1 AND w.wms_system_code=:wms_system_code ";

        $data = $this->db->get_all($sql, array(':wms_system_code' => 'iwmscloud'));
//    $this->app_secret = $token ['secretcode'];
//        $this->api_url = $token ['URL'];
//        $this->accesskey = $token ['accesskey'];
        if (!empty($data)) {
            foreach ($data as $val) {
                $api_data = $val['wms_params'];
                $token = json_decode($api_data, true);
                if ($token['accesskey'] == $accesskey) {
                    $this->set_token($token);
                    return true;
                }
            }
        }
        return FALSE;
    }

    function save_log($request) {
        $request['method'] = empty($request['method']) ? '' : $request['method'];
        $data = array('type' => 'qmwms', 'method' => $request['method'], 'add_time' => date('Y-m-d H:i:s'));
        $data['post_data'] = empty($this->req_data_xml) ? '' : $this->req_data_xml;
        $data['url'] = json_encode($request);
        $data['url'] = empty($data['url']) ? '' : $data['url'];

        $this->db->insert('api_open_logs', $data);
        $this->log_id = $this->db->insert_id();
    }

    function update_log($return, $status) {
        if ($this->log_id != 0) {
            $up_data = array('return_data' => $return);
            if ($status > 0) {
                $up_data['key_id'] = $this->log_unid;
            }
            $this->db->update('api_open_logs', $up_data, "id = '{$this->log_id }'");
        }
    }

    function check_log_unid($log_unid) {
        $this->log_unid = $log_unid;
        $sql = "select 1 from api_open_logs where key_id=:key_id order by id desc ";
        $check = $this->db->get_value($sql, array(':key_id' => $log_unid));
        $is_get = false;
        if ($check > 0) {
            $is_get = true;
        }

        return $is_get;
    }

    function return_info($status, $message = '', $ret_data = array()) {
        $return = array(
            '0' => array('flag' => 'FALSE'),
            '1' => array('flag' => 'ACK',),
        );
        if ($status < 0) {
            $status = 0;
        }
        $ret = &$return[$status];
        $ret['data'] = $ret_data;
        if (!empty($message)) {
            $ret['data']['msg'] = $message;
        }
        $data = json_encode($ret);
        $this->update_log($data , $status);
        return $data;
    }

}
