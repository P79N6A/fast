<?php
require_model('tb/TbModel');
class O2oMgrModel extends TbModel {

    function cli_o2o_record_info() {

        $sql = "select id,record_code from o2o_oms_trade where 1 and api_order_flow_end_flag = 0 and upload_response_flag = 10 AND cancel_response_flag<>10";
        $db_arr = ctx()->db->get_all($sql);
        $result = $this->format_ret(1);
        foreach ($db_arr as $sub_arr) {
            try {
                $ret = $this->get_api_record_info($sub_arr['id']);
            }catch (Exception $e) {
                $_err = '接口请求报错:' . $e->getMessage();
                return $this->format_ret(-1, '', $_err);
            }
            echo 'api_record_info ' . $sub_arr['record_code'];
            print_r($ret);
        }
        return $result;
    }

    //定时器任务：上传单据到接口
    function upload_record_cli() {

        $sql = "select id,record_code from o2o_oms_trade where 1 and upload_response_flag in(0,20) AND cancel_request_flag=0 AND cancel_response_flag=0";

        $db_arr = ctx()->db->get_all($sql);

        foreach ($db_arr as $sub_arr) {
            $ret = $this->upload($sub_arr['id']);
            echo 'upload ' . $sub_arr['record_code'];
            print_r($ret);
        }
    }

    function cli_order_shipping() {

        $this->cli_order_shipping_status();
        //$this->auto_cancel_by_record_status();
    }

    //处理收发货状态后，自动跑验证逻辑,如果订单已确认，已通知配货，要自动取消订单
    function auto_cancel_by_record_status() {
        $sql = "SELECT
                    t2.*
                FROM
                    o2o_oms_trade t1
                INNER JOIN  oms_sell_record t2 ON t1.record_code = t2.sell_record_code
                where t1.record_type = 'sell_record' and t1.upload_response_flag = 10 and t1.cancel_response_flag<>10 and t1.api_order_flow_end_flag = 0
                and (t2.order_status<>1 or t2.shipping_status<>1)";
        $db_api = ctx()->db->get_all($sql);


        if (empty($db_api)) {
            return $this->format_ret(1);
        }
        foreach ($db_api as $sub_api) {
            $ret = load_model('oms/SellRecordOptModel')->biz_intercept($sub_api, 1, '');

            if ($ret['status'] < 0) {
                $action_name = '订单拦截失败';
                $action_note = '订单状态不是通知配货状态.' . $ret['message'];
            } else {
                $action_name = '订单拦截';
                $action_note = '订单状态不是通知配货状态,进行订单拦截成功.';
            }
            load_model('oms/SellRecordModel')->add_action($sub_api['sell_record_code'], $action_name, $action_note);
        }
        return $this->format_ret(1);
    }

    function cli_order_shipping_status() {
        //$process_fail_num = 3;

        $sql = "select id,record_code,process_fail_num from o2o_oms_trade where 
                 api_order_flow_end_flag = 1 and (process_flag = 0 or (process_flag = 20 and process_fail_num<3)) ";
        $db_arr = ctx()->db->get_all($sql);

        foreach ($db_arr as $sub_arr) {
            $ret = $this->order_shipping($sub_arr['id']);
            echo 'order_shipping ' . $sub_arr['record_code'];
            print_r($ret);
        }
    }

    function cancel($task_id, $type = 'sell_record', $is_cancel_tag = '') {
        $is_cancel_tag = empty($is_cancel_tag) ? '' : $is_cancel_tag;
        $sql = "select record_code,record_type,api_product,sys_store_code,cancel_response_flag,upload_request_flag,upload_response_flag from o2o_oms_trade where id = :id";
        $db_oi = $this->db->get_row($sql, array(':id' => $task_id));
        //echo '<hr/>$db_oi<xmp>'.var_export($db_oi,true).'</xmp>';die;
        $cancel_response_flag = (int) $db_oi['cancel_response_flag'];
        $api_product = $db_oi['api_product'];
        $record_type = $db_oi['record_type'];
        $record_code = (string) $db_oi['record_code'];


        if ($cancel_response_flag == 10) {
            return $this->format_ret(-1, '', $record_code . ' 已取消，不能重复取消');
        }

        $sys_store_code = $db_oi['sys_store_code'];

        $api_obj = $this->get_api_obj($api_product, $record_type);



        if ($db_oi['upload_request_flag'] == 10 && $db_oi['upload_response_flag'] == 10) {
            if ($api_obj == FALSE) {
                return $this->format_ret(-1, '', ' 数据异常接口类型：' . $api_product . '不存在');
            }
            $new_record_code = $this->is_canceled($record_code, $record_type);
            $ret = $api_obj->cancel($new_record_code, $sys_store_code);
        } else {
            $ret = array('status' => 10, 'message' => '未上传，直接取消');
        }

        load_model('o2o/O2oRecordModel')->process_cancel_after($record_code, $record_type, $ret, $is_cancel_tag);
        return $ret;
    }

    function get_api_record_info($task_id) {
        $sql = "select record_code,upload_response_flag,record_type,api_product,sys_store_code from o2o_oms_trade where id = :id";
        $db_oi = $this->db->get_row($sql, array(':id' => $task_id));
        //echo '<hr/>$db_oi<xmp>'.var_export($db_oi,true).'</xmp>';die;
        $upload_response_flag = (int) $db_oi['upload_response_flag'];
        if ($upload_response_flag <> 10) {
            return $this->format_ret(-1, '', $task_id . ' 未上传成功，不能查询接口订单状态');
        }
        $record_code = (string) $db_oi['record_code'];
        $record_type = $db_oi['record_type'];
        $api_product = $db_oi['api_product'];
        $sys_store_code = $db_oi['sys_store_code'];
        $api_obj = $this->get_api_obj($api_product, $record_type);
        if ($api_obj == FALSE) {
            return $this->format_ret(-1, '', ' 数据异常接口类型：' . $api_product . '不存在');
        }

        $new_record_code = $this->is_canceled($record_code, $record_type);

        $ret_api_info = $api_obj->_record_info($new_record_code, $sys_store_code);
        if ($ret_api_info['status'] < 0) {
            return $ret_api_info;
        } else {

            //echo '<hr/>$ret_wms_info<xmp>'.var_export($ret_wms_info,true).'</xmp>';
            if ($ret_api_info['data']['order_status'] == 'flow_end') {

                $ret = load_model('o2o/O2oRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_api_info);
            }
        }
        return $ret_api_info;
    }

    public function order_shipping($task_id) {

        $sql = "select record_code,record_type,sys_store_code,api_product,sys_store_code,api_order_flow_end_flag,express_code,express_no,api_order_time,order_weight from o2o_oms_trade where id = :id";

        $db_oi = $this->db->get_row($sql, array(':id' => $task_id));
        //echo '<hr/>$db_oi<xmp>'.var_export($db_oi,true).'</xmp>';die;
        $api_order_flow_end_flag = (int) $db_oi['api_order_flow_end_flag'];
        if ($api_order_flow_end_flag <> 1) {
            return $this->format_ret(-1, '', $db_oi['record_code'] . ' 未完成收发的单据，不能处理');
        }
        $record_type = $db_oi['record_type'];
        $record_code = $db_oi['record_code'];
        $express_code = $db_oi['express_code'];
        $express_no = $db_oi['express_no'];
        $order_weight = isset($db_oi['order_weight']) ? $db_oi['order_weight'] : 0;

        $record_time = ($db_oi['api_order_time'] == 0) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $db_oi['api_order_time']);
/*
        $sql = "select barcode,api_sl as num from o2o_oms_order where record_code = :record_code and record_type = :record_type AND api_sl>0";
        $order_mx = ctx()->db->get_all($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
*/
        $ret = $this->get_o2o_obj($record_type)->order_shipping($record_code, $record_time, $express_code, $express_no, $order_weight);

        if ($ret['status'] > 0) {
            load_model('o2o/O2oRecordModel')->uploadtask_order_status_sync_success($record_code, $record_type);
        } else {
            load_model('o2o/O2oRecordModel')->uploadtask_order_status_sync_fail($record_code, $record_type, $ret['message']);
        }
        return $ret;
    }

    function upload($task_id) {
        $sql = "select record_code,upload_response_flag,record_type,sys_store_code,api_product,sys_store_code,cancel_request_flag,cancel_response_flag,cancel_flag from o2o_oms_trade where id = :id";
        $db_oi = $this->db->get_row($sql, array(':id' => $task_id));

        $upload_response_flag = (int) $db_oi['upload_response_flag'];
        $record_code = (string) $db_oi['record_code'];
        if ($upload_response_flag == 10) {
            return $this->format_ret(-1, '', $record_code . ' 已上传，不能重复上传');
        }
        $cancel_response_flag = (int) $db_oi['cancel_response_flag'];
        if ($cancel_response_flag == 10) {
            return $this->format_ret(-1, '', $record_code . ' 已取消，不能上传');
        }
        $cancel_response_flag = (int) $db_oi['cancel_request_flag'];
        if ($cancel_response_flag == 10) {
            return $this->format_ret(-1, '', $record_code . ' 取消请求发出成功,不能上传');
        }

        $record_type = $db_oi['record_type'];
        $api_product = $db_oi['api_product'];
        $sys_store_code = $db_oi['sys_store_code'];
        if ($db_oi['cancel_flag'] == 1){
            $this->create_new_record_code($record_code,$record_type);
        }

        $api_obj = $this->get_api_obj($api_product, $record_type);
        if ($api_obj == FALSE) {
            return $this->format_ret(-1, '', ' 数据异常接口类型：' . $api_product . '不存在');
        }
        $ret = $api_obj->upload($record_code, $sys_store_code);
        load_model('o2o/O2oRecordModel')->process_upload_after($record_code, $record_type, $ret);
        return $ret;
    }
    function is_canceled($record_code,$record_type){
        
        $sql = "select new_record_code from o2o_oms_trade where record_code = :record_code and record_type = :record_type";
        $new_record_code = $this->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
        return !empty($new_record_code)?$new_record_code:$record_code;
    }
    
    function create_new_record_code($record_code,$record_type){
        $new_record_code = "N".rand(100, 999).$record_code;
        $sql = "update o2o_oms_trade set new_record_code = '{$new_record_code}' where record_code ='{$record_code}' and record_type = '{$record_type}'";
        $this->db->query($sql);
        $action = '取消再次上传ERP';
        $log = '取消再次上传ERP，生成新单号：'.$new_record_code;
        if($record_type == 'sell_record'){
            load_model('oms/SellRecordActionModel')->add_action($record_code, $action, $log);
        } elseif ($record_type == 'sell_return') {
            $return_record = load_model('oms/SellReturnModel')->get_return_by_return_code($record_code);
            load_model('oms/SellReturnModel')->add_action($return_record, $action, $log);
        } 
    }

    function get_api_obj($api_product, $record_type) {
        static $mod_arr = null;
        if (!isset($mod_arr[$api_product][$record_type])) {

            $record_arr = explode("_", $record_type);
            $mod_str = ucfirst($api_product);
            foreach ($record_arr as $val) {
                $mod_str.=ucfirst($val);
            }

            $mod_path = 'o2o/' . $api_product . '/' . ucfirst($mod_str) . 'Model';
            $mod_arr[$api_product][$record_type] = load_model($mod_path);
        }
        return $mod_arr[$api_product][$record_type];
    }

    function get_o2o_obj($record_type) {
        static $mod_arr = null;
        if (!isset($mod_arr[$record_type])) {

            $record_arr = explode("_", $record_type);
            $mod_str = 'O2o';
            foreach ($record_arr as $val) {
                $mod_str.=ucfirst($val);
            }

            $mod_path = 'o2o/' . ucfirst($mod_str) . 'Model';
            $mod_arr[$record_type] = load_model($mod_path);
        }
        return $mod_arr[$record_type];
    }

}
