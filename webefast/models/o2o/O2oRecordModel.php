<?php
require_model('tb/TbModel');
class O2oRecordModel extends TbModel {

    function __construct() {
        parent::__construct();
    }

    //更新为收发货成功
    function uploadtask_order_end($record_code, $record_type, $ret, $allow_empty_barcode = 1) {
        if ($ret['status'] < 0) {
            return $ret;
        }


        $express_code = isset($ret['data']['express_code']) ? $ret['data']['express_code'] : 'KHZT';
        $express_no = isset($ret['data']['express_no']) ? $ret['data']['express_no'] : '';
        $api_order_time = isset($ret['data']['flow_end_time']) ? $ret['data']['flow_end_time'] : '';
        $barcode_data = isset($ret['data']['goods']) ? $ret['data']['goods'] : array();
        $api_record_code = isset($ret['data']['api_record_code']) ? $ret['data']['api_record_code'] : '';
        $order_weight = isset($ret['data']['order_weight']) ? $ret['data']['order_weight'] : 0;

        //$this->create_api_return_order_detail($record_code,$record_type,$barcode_data);
        //todo $record_type 单据类型，和             $this->api_product 类型判断 支持 多次收货 另外保存明细



        $is_update_data = 0; //是否更新过数据
        if ($allow_empty_barcode == 0) {
            //合并相同barcode的数量
            $barcode_arr = array();
            foreach ($barcode_data as $k => $sub_barcode) {
                if (isset($barcode_arr[$sub_barcode['barcode']])) {
                    $sub_barcode['sl'] = $barcode_arr[$sub_barcode['barcode']]['sl'] + $sub_barcode['sl'];
                }
                $barcode_arr[$sub_barcode['barcode']] = $sub_barcode;
            }

            $task_info = $this->get_task_info($record_code, $record_type);
            $order_status = $task_info['order_status'];

            //状态验证
            if (!in_array($order_status, array('uploading', 'upload_success', 'canceling', 'cancel_fail', 'order_status_sync_fail', 'order_end'))) {
                return $this->format_ret(1);
            }
            if (count($barcode_arr) == 0) {
                return $this->format_ret(1);
            }
            //如果数据没改变，不要更新 -- end
            $sql = "delete from o2o_oms_order where record_code = '{$record_code}' and record_type = '{$record_type}'";
            ctx()->db->query($sql);
            $ins_arr = array();
            foreach ($barcode_arr as $sub_barcode) {
                $ins_arr[] = "('{$record_code}','{$record_type}','{$sub_barcode['barcode']}','-1','{$sub_barcode['sl']}')";
            }

            $sql = "insert into o2o_oms_order(record_code,record_type,barcode,sys_sl,api_sl) values" . join(',', $ins_arr);
            $ret = ctx()->db->query($sql);
            //生成明细的EFAST数量
            $this->create_record_efast_mx($record_code, $record_type);
            $is_update_data = 1;
        }

        $api_order_time = strtotime($api_order_time);

        // $sql = "update wms_{$wms_record_mode}_trade set upload_request_flag = 10,wms_record_code = '{$wms_record_code}',upload_response_flag = 10,wms_order_flow_end_flag = 1,express_code = '$express_code',express_no = '$express_no',wms_order_time = '{$wms_order_time}' where record_code ='{$record_code}' and record_type = '{$record_type}'";
        //  ctx()->db->query($sql);
        $up_data['upload_request_flag'] = 10;
        $up_data['api_record_code'] = $api_record_code;
        $up_data['upload_response_flag'] = 10;
        $up_data['api_order_flow_end_flag'] = 1;
        $up_data['express_code'] = $express_code;
        $up_data['express_no'] = $express_no;
        $up_data['api_order_time'] = $api_order_time;
        //单据重量
        if ($record_type == 'sell_record') {
            $up_data['order_weight'] = $order_weight;
        }
        $where = " record_code ='{$record_code}' and record_type = '{$record_type}' ";
        ctx()->db->update("o2o_oms_trade", $up_data, $where);



        return $this->format_ret(1, $ret);
    }

    function create_record_efast_mx($record_code, $record_type) {

        $api_mx_tbl = "o2o_oms_order";
        $api_tbl = "o2o_oms_trade";

        $sql = "select json_data from {$api_tbl} where record_code =:record_code and record_type =:record_type";
        $sql_values = array(':record_code' => $record_code, ':record_type' => $record_type);
        $json_data = ctx()->db->getOne($sql, $sql_values);
        $json_data = empty($json_data) ? "{}" : $json_data;
        $json_data_arr = json_decode($json_data, true);
        if (empty($json_data_arr['goods'])) {
            return $this->format_ret(-1, '', $record_code . ' 的单据明细不能为空');
        }
        $efast_mx = $json_data_arr['goods'];
        //echo '<hr/>$efast_mx<xmp>'.var_export($efast_mx,true).'</xmp>';
        $ins_arr = array();
        $create_time = date('Y-m-d H:i:s');
        foreach ($efast_mx as $sub_mx) {
            $ins_arr[] = array('record_code' => $record_code,
                'record_type' => $record_type,
                'barcode' => $sub_mx['barcode'],
                'sys_sl' => $sub_mx['num'],
                'api_sl' => -1,
                'create_time' => $create_time);
        }
        $update_str = "sys_sl = VALUES(sys_sl)";
        $ret = $this->insert_multi_duplicate($api_mx_tbl, $ins_arr, $update_str);
        return $ret;
    }

    //上传后的处理
    function process_upload_after($record_code, $record_type, $ret) {
        if ($ret['status'] < 0) {
            $err_msg = $ret['message'];
            $this->uploadtask_upload_fail($record_code, $record_type, $err_msg);
            return $this->format_ret(-1, '', $err_msg);
        } else {
            $api_record_code = $ret['data'];

            $this->uploadtask_upload_success($record_code, $record_type, $api_record_code);

            return $ret;
        }
    }

    //取消后的处理 如果存在取消要作废原单据的情况下，要传 $efast_store_code
    function process_cancel_after($record_code, $record_type, $ret, $is_cancel_tag = null, $sys_store_code = null) {
        if ($ret['status'] < 0) {
            $err_msg = $ret['message'];
            $this->uploadtask_cancel_fail($record_code, $record_type, $err_msg);
            return $this->format_ret(-1, '', $err_msg);
        } else {


            $this->uploadtask_cancel_success($record_code, $record_type, $is_cancel_tag, $sys_store_code);


            return $ret;
        }
    }

    //更新为上传失败
    function uploadtask_upload_fail($record_code, $record_type, $err_msg) {
        $task_info = $this->get_task_info($record_code, $record_type);
        $order_status = $task_info['order_status'];
        if (!in_array($order_status, array('wait_upload', 'uploading', 'upload_fail'))) {
            return $this->format_ret(1);
        }
        $upload_response_err_msg = addslashes($err_msg);
        $time = date('Y-m-d H:i:s');

        $sql = "update o2o_oms_trade set upload_request_flag = 10, upload_request_time='{$time}',upload_response_flag = 20,upload_response_time='{$time}',upload_response_err_msg = '{$upload_response_err_msg}' where record_code ='{$record_code}' and record_type = '{$record_type}'";
        ctx()->db->query($sql);

        return $this->format_ret(1);
    }

    //更新为上传成功
    function uploadtask_upload_success($record_code, $record_type, $api_record_code) {


        $time = date('Y-m-d H:i:s');
        $sql = "update o2o_oms_trade set api_record_code = '{$api_record_code}',upload_request_flag = 10,upload_request_time = '{$time}',upload_response_flag=10,upload_response_time = '{$time}',cancel_response_flag = 0,cancel_request_flag = 0 where record_code ='{$record_code}' and record_type = '{$record_type}'";
        ctx()->db->query($sql);


        return $this->format_ret(1);
    }

    function get_task_info($record_code, $record_type, $task_id = 0) {

        $sql = "SELECT
							id,
							record_code,
							record_type,
							upload_request_flag,
							upload_response_flag,
							cancel_request_flag,
							cancel_response_flag,
							upload_request_time,
							cancel_request_time,
							upload_response_err_msg,
							cancel_response_err_msg,
							process_flag,
							process_err_msg,
							api_order_flow_end_flag,
							sys_store_code
						FROM
							o2o_oms_trade where ";
        if ($task_id == 0) {
            $sql .= "record_code = '{$record_code}' and record_type = '{$record_type}'";
        } else {
            $sql .= " id = {$task_id}";
        }
        $db_awt = $this->db->get_row($sql);
        $state_arr = $this->get_status_exp($db_awt);
        $db_awt['order_status'] = $state_arr['status'];
        $db_awt['order_status_txt'] = $state_arr['status_txt'];
        $db_awt['order_status_txt_ex'] = $state_arr['status_txt_ex'];
        return $db_awt;
    }

    function get_status_exp($info) {
        if (!is_array($info)) {
            $ret = array('status' => 'no_found', 'status_txt' => '未找到任务', 'status_txt_ex' => '未找到任务');
            return $ret;
        }
        /*
          echo "<xmp>";debug_print_backtrace();echo "</xmp>";
          echo '<hr/>info<xmp>'.var_export($info,true).'</xmp>';
          die; */
        $upload_request_flag = $info['upload_request_flag'];
        $upload_response_flag = $info['upload_response_flag'];
        $cancel_request_flag = $info['cancel_request_flag'];
        $cancel_response_flag = $info['cancel_response_flag'];
        $api_order_flow_end_flag = $info['api_order_flow_end_flag'];
        $process_flag = $info['process_flag'];
        $ret = null;

        $upload_status = '未上传';
        if ($upload_request_flag == '10') {
            $upload_status = '上传中';
        }
        if ($upload_response_flag == '10') {
            $upload_status = '上传成功';
        }
        if ($upload_response_flag == '20') {
            $upload_status = '上传失败';
        }

        $cancel_status = '未取消';
        if ($cancel_request_flag == '10') {
            $cancel_status = '取消中';
        }
        if ($cancel_response_flag == '10') {
            $cancel_status = '取消成功';
        }
        if ($cancel_response_flag == '20') {
            $cancel_status = '取消失败';
        }

        if ($process_flag == 20) {
            $ret = array('status' => 'order_status_sync_fail', 'status_txt' => '处理失败', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 已收发货 | 处理失败");
        }
        if ($process_flag == 30 && is_null($ret)) {
            $ret = array('status' => 'order_status_sync_success', 'status_txt' => '处理成功', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 已收发货 | 处理成功");
        }
        if ($api_order_flow_end_flag == 1 && is_null($ret)) {
            $ret = array('status' => 'order_end', 'status_txt' => '已收发货', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 已收发货 | 未处理");
        }
        if ($api_order_flow_end_flag == 2 && is_null($ret)) {
            $ret = array('status' => 'order_close', 'status_txt' => '已关闭', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 已关闭 | 未处理");
        }
        if ($cancel_response_flag == 10 && is_null($ret)) {
            $ret = array('status' => 'cancel_success', 'status_txt' => '取消成功', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($cancel_response_flag == 20 && is_null($ret)) {
            $ret = array('status' => 'cancel_fail', 'status_txt' => '取消失败', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($cancel_request_flag == 10 && is_null($ret)) {
            $ret = array('status' => 'canceling', 'status_txt' => '取消中', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($upload_response_flag == 10 && is_null($ret)) {
            $ret = array('status' => 'upload_success', 'status_txt' => '上传成功', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($upload_response_flag == 20 && is_null($ret)) {
            $ret = array('status' => 'upload_fail', 'status_txt' => '上传失败', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($upload_request_flag == 10 && is_null($ret)) {
            $ret = array('status' => 'uploading', 'status_txt' => '上传中', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($upload_request_flag == 0 && is_null($ret)) {
            $ret = array('status' => 'wait_upload', 'status_txt' => '未上传', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        return $ret;
    }

    /* 更新为取消成功
      is_cancel_tag = array('act'=>'quehuo | invalid_order | refund | force','msg'=>'WMS缺货取消 | 发现存在xxx退单 | 已上传的订单的状态不是已确认已通知配货 | 客服强制取消');
     */

    function uploadtask_cancel_success($record_code, $record_type, $is_cancel_tag = null, $is_force = 0) {
        $task_info = $this->get_task_info($record_code, $record_type);
        

        // $act = "api_response_cancel_success";
        ctx()->db->begin_trans();

        $is_cancel_tag_act = isset($is_cancel_tag['act']) ? $is_cancel_tag['act'] : '';

        //自动反确认单据
        if ($record_type == 'sell_record' && ($is_cancel_tag_act == '' || $is_cancel_tag_act == 'refund')) {
            $ret = load_model('oms/SellRecordOptModel')->opt_unconfirm($record_code);
        }

        if ($record_type == 'order_return') {
            $ret = load_model('oms/SellReturnOptModel')->opt_unconfirm($record_code);
        }
  

        if (isset($ret['status']) && $ret['status'] < 0) {
            return $ret;
        }
        $time = date('Y-m-d H:i:s');

        $sql = "update o2o_oms_trade  set cancel_request_flag = 10,cancel_request_time = '{$time}',cancel_response_flag=10,cancel_flag = 1,cancel_response_time='{$time}'  where record_code ='{$record_code}' and record_type = '{$record_type}'";


        ctx()->db->query($sql);
        if ($is_force == 0) {
            $action_no = 'api_response_cancel_success';
        } else {
            $action_no = 'api_response_force_cancel_success';
        }



        ctx()->db->commit();

        return $this->format_ret(1);
    }

    //更新为取消失败
    function uploadtask_cancel_fail($record_code, $record_type, $err_msg) {
        $task_info = $this->get_task_info($record_code, $record_type);
        $order_status = $task_info['order_status'];
        if (!in_array($order_status, array('canceling', 'upload_success', 'order_end', 'order_status_sync_success', 'order_status_sync_fail'))) {
            return $this->format_ret(1);
        }

        $cancel_response_err_msg = addslashes($err_msg);
        $time = date('Y-m-d H:i:s');

        $sql = "update o2o_oms_trade set cancel_request_flag=10,cancel_request_time='{$time}', cancel_response_flag = 20,cancel_response_time='{$time}',cancel_response_err_msg='{$cancel_response_err_msg}' where record_code ='{$record_code}' and record_type = '{$record_type}'";

        ctx()->db->query($sql);

        return $this->format_ret(1);
    }

    //验证单据明细的数据完整性
    function check_mx($data) {
        $err_arr = array();
        foreach ($data as $sub_data) {
            $_sku = $sub_data['sku'];
            $_err = '';
            if (empty($sub_data['goods_name'])) {
                $_err .= $_sku . " sku缺少商品名称.";
            }
            if (empty($sub_data['barcode'])) {
                $_err .= $_sku . " sku缺少条码.";
            }
            if (empty($sub_data['spec1_name'])) {
                $_err .= $_sku . " sku缺少" . $this->goods_spec1_name . '.';
            }
            if (empty($sub_data['spec2_name'])) {
                $_err .= $_sku . " sku缺少" . $this->goods_spec2_name . '.';
            }
            if (!empty($_err)) {
                $err_arr[] = $_err;
            }
        }
        if (empty($err_arr)) {
            return $this->format_ret(1);
        }
        return $this->format_ret(-1, '', join(',', $err_arr));
    }

    //更新O2O_API单据状态同步到EFAST成功
    function uploadtask_order_status_sync_success($record_code, $record_type) {

        $time = date('Y-m-d H:i:s');

        //process_flag

        $sql = "update o2o_oms_trade set process_time = '{$time}',process_flag = 30 where record_code = '{$record_code}' and record_type = '{$record_type}'";
        $ret = ctx()->db->query($sql);
        return $this->format_ret(1);
    }

    //更新O2O_API单据状态同步到EFAST失败
    function uploadtask_order_status_sync_fail($record_code, $record_type, $err_msg) {

        $err_msg = addslashes($err_msg);
        $time = date('Y-m-d H:i:s');

        $sql = "update o2o_oms_trade set process_time = '{$time}',process_fail_num=process_fail_num+1,process_flag = 20,process_err_msg = '$err_msg' where record_code = '{$record_code}' and record_type = '{$record_type}'";
        //echo $sql;
        ctx()->db->query($sql);

        return $this->format_ret(1);
    }

}
