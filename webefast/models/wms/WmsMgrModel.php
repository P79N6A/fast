<?php

require_model('wms/WmsBaseModel');

class WmsMgrModel extends WmsBaseModel {

    public $biz_wms;
    public $biz_dj;
    public $dj_type_arr;
    private $is_cli = false;

    function __construct() {
        parent::__construct();
        $this->dj_type_arr = explode(',', 'sell_record,sell_return,pur_notice,pur_return_notice,wbm_notice,wbm_return_notice,shift_in,shift_out,stm_diy,stm_split');
    }

    function __destruct() {
        
    }

    function get_biz_wms($api_product, $record_type) {
        $ks = "{$api_product}-{$record_type}";
        if (!isset($this->biz_wms[$ks])) {
            $class_name = ucfirst($api_product) . join('', array_map('ucfirst', explode('_', $record_type))) . "Model";

            require_model("wms/{$api_product}/{$class_name}");
            if (class_exists($class_name)) {
                $this->biz_wms[$ks] = new $class_name;
            } else {
                return false;
            }
        }
        return $this->biz_wms[$ks];
    }

    function get_biz_dj($record_type) {
        $ks = "{$record_type}";
        if (!isset($this->biz_dj[$ks])) {
            $class_name = 'Wms' . join('', array_map('ucfirst', explode('_', $record_type))) . "Model";
            require_model("wms/{$class_name}");
            //echo '<hr/>$class_name<xmp>'.var_export($class_name,true).'</xmp>';
            $this->biz_dj[$ks] = new $class_name;
        }
        return $this->biz_dj[$ks];
    }

    public function uploadtask_add($record_code, $record_type, $efast_store_code, $info = array()) {
        $wms_cfg = $this->get_wms_cfg($efast_store_code, $record_type);
        $api_product = $wms_cfg['api_product'];
        $task_info = array('record_code' => $record_code, 'record_type' => $record_type, 'efast_store_code' => $efast_store_code);
        if (count($info) > 0) {
            $task_info = array_merge($task_info, $info);
        }
        if ($api_product == 'ydwms') {
            $status = $this->get_record_is_cancel($record_code, $record_type);
            if ($status) {
                return $this->format_ret(-1, '', '请作废订单，复制修改后重新推送韵达！');
            }
        }
        if ($api_product == 'shunfeng' && $record_type == 'pur_notice') {
            $status = $this->get_record_is_cancel($record_code, $record_type);
            if ($status) {
                return $this->format_ret(-1, '', '顺丰暂不支持一单多次上传，请重新建单上传');
            }
        }
        if (in_array($record_type, array('stm_diy', 'stm_split')) && in_array($api_product, array('iwms', 'iwmscloud'))) {
            $record_type = 'goods_diy_record';
        }

        $ret = $this->get_biz_dj($record_type)->get_record_info($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        if (in_array($api_product, array('iwms', 'iwmscloud', 'qimen')) && in_array($record_type, array('shift_out', 'shift_in'))) {
            $store_arr = array('shift_out' => $ret['data']['shift_out_store_code'], 'shift_in' => $ret['data']['shift_in_store_code']);
            $store = $this->get_outside_code($store_arr, $api_product);
            if (isset($store[$store_arr['shift_out']]) && isset($store[$store_arr['shift_in']]) && $store[$store_arr['shift_out']] == $store[$store_arr['shift_in']]) {
                return $this->format_ret(2, '', '移入和移出仓外部编码一致，不上传中间表，仅系统内部操作');
            }
        }

        $json_data_arr = $ret['data'];
        $task_info['json_data'] = json_encode($json_data_arr);
        $task_info['api_product'] = $api_product;

        foreach ($json_data_arr['goods'] as $code) {
            if (isset($code['deal_code'])) {
                $deal_code_arr[] = $code['deal_code'];
            }
        }
        $task_info['buyer_name'] = isset($json_data_arr['buyer_name']) ? (string) $json_data_arr['buyer_name'] : '';
        $task_info['sale_channel_code'] = isset($json_data_arr['sale_channel_code']) ? (string) $json_data_arr['sale_channel_code'] : '';
        $task_info['shop_code'] = isset($json_data_arr['shop_code']) ? (string) $json_data_arr['shop_code'] : '';

        //echo '<hr/>$task_info<xmp>'.var_export($task_info,true).'</xmp>';die;
        $ret = load_model('wms/WmsRecordModel')->uploadtask_add($task_info, $deal_code_arr);
        return $ret;
    }

    function upload($task_id, $type = 'b2b') {
        $sql = "select record_code,upload_request_flag,upload_request_time,upload_response_flag,record_type,efast_store_code,api_product,efast_store_code,cancel_request_flag,cancel_response_flag,cancel_flag from wms_{$type}_trade where id = :id";
        $db_oi = $this->db->get_row($sql, array(':id' => $task_id));

        $upload_request_flag = (int) $db_oi['upload_request_flag'];

        $upload_response_flag = (int) $db_oi['upload_response_flag'];
        $record_code = (string) $db_oi['record_code'];
        $upload_request_time = $db_oi['upload_request_time'];

        if ($upload_response_flag == 10) {
            return $this->format_ret(-1, '', ' 已上传，不能重复上传');
        }
        $cancel_response_flag = (int) $db_oi['cancel_response_flag'];
        if ($cancel_response_flag == 10) {
            return $this->format_ret(-1, '', ' 已取消，不能上传');
        }
        $cancel_request_flag = (int) $db_oi['cancel_request_flag'];
        if ($cancel_request_flag == 10) {
            return $this->format_ret(-1, '', ' 取消请求发出成功,不能上传');
        }

        $record_type = $db_oi['record_type'];
        $api_product = $db_oi['api_product'];
        $efast_store_code = $db_oi['efast_store_code'];

        if ($upload_request_flag == 10) {
            $upload_request_time_int = strtotime($upload_request_time);
            $time_cha = time() - $upload_request_time_int;
            if ($time_cha > 600) {
                $sql_request = "update wms_{$type}_trade set upload_request_flag=0,upload_request_time='0000-00-00 00:00:00' where  id = '{$task_id}' AND cancel_request_flag=0 AND upload_request_flag=10";
                $this->db->query($sql_request);
            } else {
                return $this->format_ret(-1, '', ' 上传中,不能重复上传');
            }
        }

        if (in_array($record_type, array('stm_diy', 'stm_split')) && in_array($api_product, array('iwms', 'iwmscloud'))) {
            $record_type_temp = 'goods_diy_record';
        } else {
            $record_type_temp = $record_type;
        }

        $wms_obj = $this->get_biz_wms($api_product, $record_type_temp);
        if ($wms_obj == FALSE) {
            return $this->format_ret(-1, '', ' 数据异常接口类型：' . $api_product . '不存在');
        }
        $wms_obj->wms_cfg = array();
        $wms_obj->get_wms_cfg($efast_store_code, $record_type);
        if ($this->is_cli == TRUE && $record_type == 'sell_record') {
            $is_stop = $this->check_is_pause_time($efast_store_code, $wms_obj->wms_cfg);
            if ($is_stop === TRUE) {
                return $this->format_ret(1, '', ' 截单时间已到，不能上传');
            }
        }

        $new_record_code = '';
        //iwms、qimen自动为单号增加配置的前缀,并存入新单号
        if (in_array($api_product, ['qimen', 'iwms']) && !empty($wms_obj->wms_cfg['prefix'])) {
            $prefix = $wms_obj->wms_cfg['prefix'];
            $new_record_code = $prefix . $record_code;

            $sql = "UPDATE wms_{$type}_trade SET new_record_code=:_code where id=:_id";
            $this->db->query($sql, [':_code' => $new_record_code, ':_id' => $task_id]);
        }

        //当取消成功，需要重新上传时，生成新的单据号上传
        $api_product_arr = array('qimen', 'hwwms', 'ydwms', 'jdwms');
        if ($type == 'oms' && $api_product == 'shunfeng') {
            $api_product_arr[] = 'shunfeng';
        }
        //如果是取消单据再次取消 取新单号
        if ($db_oi['cancel_flag'] == 1) {
            $kh_id_arr = array();
            $kh_id_arr[] = 2253; //左西
            $kh_id = CTX()->saas->get_saas_key();
            if (in_array($api_product, $api_product_arr) && !in_array($kh_id, $kh_id_arr)) {
                $is_create = $this->create_new_record_code($record_code, $record_type, $type, $new_record_code);
                if ($is_create !== false) {
                    $new_record_code = $is_create;
                }
            }
        }

        $new_upload_request_time = date('Y-m-d H:i:s');
        $sql_request = "update wms_{$type}_trade set upload_request_flag=10,upload_request_time='{$new_upload_request_time}' where  id = '{$task_id}' AND cancel_request_flag=0 AND upload_request_flag=0";
        $this->db->query($sql_request);
        $num = $this->db->affected_rows();
        if ($num == 0) {
            return $this->format_ret(-1, '', ' 单据状态变化，暂时不能上传.');
        }
        $ret = $wms_obj->upload($record_code);
        if ($new_record_code != '') { //上传后再次更新
            $sql = "update wms_{$type}_trade set new_record_code = '{$new_record_code}'   where id = '{$task_id}' ";
            ctx()->db->query($sql);
        }


        $obj_wms_record = load_model('wms/WmsRecordModel');
        $obj_wms_record->wms_cfg = array();
        $obj_wms_record->get_wms_cfg($efast_store_code, $record_type);
        $obj_wms_record->process_upload_after($record_code, $record_type, $ret);
        return $ret;
    }

    function check_is_pause_time($efast_store_code, $cfg) {
        static $is_stop_store = NULL;
        if (!isset($is_stop_store[$efast_store_code])) {
            $is_stop_store[$efast_store_code] = FALSE;
            if (isset($cfg['wms_cut_time']) && !empty($cfg['wms_cut_time'])) { //18:00
                $cfg['wms_cut_time'] = str_replace("：", ':', $cfg['wms_cut_time']);
                $stop_time = strtotime(date('Y-m-d') . " " . $cfg['wms_cut_time'] . ":00");
                $now_time = time();
                $is_stop_store[$efast_store_code] = ($now_time >= $stop_time) ? TRUE : FALSE;
            }
        }

        return $is_stop_store[$efast_store_code];
    }

    function create_new_record_code($record_code, $record_type, $type, $pre_record_code = '') {
        $_code = $pre_record_code !== '' ? $pre_record_code : $record_code;
        $new_record_code = "N" . rand(100, 999) . $_code;
        $sql_values = [':record_code' => $record_code, ':record_type' => $record_type, ':new_code' => $new_record_code];
        $sql = "UPDATE wms_{$type}_trade SET new_record_code=:new_code WHERE record_code=:record_code AND record_type=:record_type ";
        if ($pre_record_code === '') {
            $sql .= "AND new_record_code=''";
        } else {
            $sql .= "AND (new_record_code='' OR new_record_code=:pre_code)";
            $sql_values[':pre_code'] = $pre_record_code;
        }

        $this->db->query($sql, $sql_values);
        $num = CTX()->db->affected_rows();
        if ($num == 0) {
            return false;
        }

        $action = '取消再次上传WMS';
        $log = '取消再次上传WMS，生成新单号：' . $new_record_code;
        if ($record_type == 'sell_record') {
            load_model('oms/SellRecordActionModel')->add_action($record_code, $action, $log);
        } elseif ($record_type == 'sell_return') {
            $return_record = load_model('oms/SellReturnModel')->get_return_by_return_code($record_code);
            $this->add_action($return_record, $action, $log);
        }
        return $new_record_code;
    }

    function cancel($task_id, $type = 'b2b', $is_cancel_tag = '') {
        $is_cancel_tag = empty($is_cancel_tag) ? '' : $is_cancel_tag;
        $sql = "select record_code,record_type,efast_store_code,api_product,efast_store_code,cancel_response_flag,upload_request_flag,upload_response_flag from wms_{$type}_trade where id = :id";
        $db_oi = $this->db->get_row($sql, array(':id' => $task_id));
        //echo '<hr/>$db_oi<xmp>'.var_export($db_oi,true).'</xmp>';die;
        $cancel_response_flag = (int) $db_oi['cancel_response_flag'];
        $api_product = $db_oi['api_product'];
        $record_type = $db_oi['record_type'];
        $record_code = (string) $db_oi['record_code'];
        $api_product_arr = array('qimen', 'hwwms', 'ydwms');
        if ($type == 'oms' && $api_product == 'shunfeng') {
            $api_product_arr[] = 'shunfeng';
        }
        //如果是取消单据再次取消 取新单号
        if (in_array($api_product, $api_product_arr)) {
            $new_record_code = $this->is_canceled($record_code, $record_type);
        } else {
            $new_record_code = $record_code;
        }

        if ($cancel_response_flag == 10) {
            return $this->format_ret(-1, '', $record_code . ' 已取消，不能重复取消');
        }

        $efast_store_code = $db_oi['efast_store_code'];

        if (in_array($record_type, array('stm_diy', 'stm_split')) && in_array($api_product, array('iwms', 'iwmscloud'))) {
            $record_type_temp = 'goods_diy_record';
        } else {
            $record_type_temp = $record_type;
        }
        $wms_obj = $this->get_biz_wms($api_product, $record_type_temp);

        if ($db_oi['upload_request_flag'] == 10 && $db_oi['upload_response_flag'] == 0) {
            return $this->format_ret(-1, '', $record_code . ' 单据上传中，暂时不能取消..,');
        }

        if ($record_type == 'wbm_notice') {
            $jit_check = load_model('api/WeipinhuijitPickModel')->is_jit_notice_record($record_code);
            if ($jit_check === true) {
                return $this->format_ret(-1, '', '关联jit拣货单，并对接WMS暂不支持取消');
            }
        }


        if ($db_oi['upload_response_flag'] == 10) {
            if ($wms_obj == FALSE) {
                return $this->format_ret(-1, '', ' 数据异常接口类型：' . $api_product . '不存在');
            }
            $wms_obj->wms_cfg = array();
            $wms_obj->get_wms_cfg($efast_store_code, $record_type);
            $ret = $wms_obj->cancel($new_record_code, $efast_store_code);
            if ($api_product == 'shunfeng') {
                if ($ret['status'] > 0) {
                    $this->update_record_status($record_code);
                }
            }
        } else {
            $ret = array('status' => 10, 'message' => '未上传，直接取消');
        }



        $obj_wms_record = load_model('wms/WmsRecordModel');
        $obj_wms_record->get_wms_cfg($efast_store_code);
        $ret = $obj_wms_record->process_cancel_after($record_code, $record_type, $ret, $is_cancel_tag);
        return $ret;
    }

    function update_record_status($record_code) {
        $sql = "update pur_order_record set is_check = 0 where record_code = :record_code";
        $this->db->query($sql, array(":record_code" => $record_code));
        $id = $this->db->get_value("select order_record_id from pur_order_record where record_code = :record_code", array(":record_code" => $record_code));
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '取消确认', 'module' => "order_record", 'pid' => $id, 'action_note' => '顺丰暂不支持一单多次上传，请重新建单上传');
        load_model('pur/PurStmLogModel')->insert($log);
    }

    function force_cancel($task_id, $type = 'b2b', $is_cancel_tag = '') {
        $is_cancel_tag = empty($is_cancel_tag) ? '' : $is_cancel_tag;
        $sql = "select record_code,record_type,efast_store_code,api_product,efast_store_code,cancel_response_flag,upload_request_flag,upload_response_flag from wms_{$type}_trade where id = :id";
        $db_oi = $this->db->get_row($sql, array(':id' => $task_id));
        $record_code = (string) $db_oi['record_code'];
        if ($db_oi['cancel_response_flag'] == 10) {
            return $this->format_ret(-1, '', $record_code . ' 已取消，不能重复取消');
        }
        if ($db_oi['upload_request_flag'] == 10 && $db_oi['upload_response_flag'] == 0) {
            return $this->format_ret(-1, '', $record_code . ' 单据上传中，暂时不能取消..,');
        }

        $api_product = $db_oi['api_product'];
        $record_type = $db_oi['record_type'];
        $efast_store_code = $db_oi['efast_store_code'];

        $api_product_arr = array('qimen', 'hwwms', 'ydwms');
        if ($type == 'oms' && $api_product == 'shunfeng') {
            $api_product_arr[] = 'shunfeng';
        }

        //如果是取消单据再次取消 取新单号
        if (in_array($api_product, $api_product_arr)) {
            $new_record_code = $this->is_canceled($record_code, $record_type);
        } else {
            $new_record_code = $record_code;
        }

        $wms_obj = $this->get_biz_wms($api_product, $record_type);
        if ($db_oi['upload_response_flag'] == 10 && $wms_obj != FALSE) {
            $wms_obj->wms_cfg = array();
            $wms_obj->get_wms_cfg($efast_store_code, $record_type);
            $ret = $wms_obj->cancel($new_record_code, $efast_store_code);
            if ($api_product == 'shunfeng' && $ret['status'] > 0) {
                $this->update_record_status($record_code);
            }
        }

        $obj_wms_record = load_model('wms/WmsRecordModel');
        $obj_wms_record->get_wms_cfg($efast_store_code);
        $ret = $obj_wms_record->uploadtask_cancel_success($record_code, $record_type, $is_cancel_tag, 1);

        return $ret;
    }

    /*
     * 获取发货信息
     */

    function wms_record_info($task_id, $type = 'b2b', $is_mode_show = 0) {
        $sql = "select record_code,upload_response_flag,record_type,efast_store_code,api_product,efast_store_code,wms_order_flow_end_flag,cancel_response_flag from wms_{$type}_trade where id = :id";
        $db_oi = $this->db->get_row($sql, array(':id' => $task_id));
        //echo '<hr/>$db_oi<xmp>'.var_export($db_oi,true).'</xmp>';die;
        $upload_response_flag = (int) $db_oi['upload_response_flag'];
        if ($upload_response_flag <> 10) {
            return $this->format_ret(-1, '', $task_id . ' 未上传成功，不能查询wms订单状态');
        }
        if ($db_oi['wms_order_flow_end_flag'] == 1) {
            return $this->format_ret(-1, '', $task_id . '已经收发货!');
        }
        if ($db_oi['cancel_response_flag'] == 10) {
            return $this->format_ret(-1, '', $task_id . '单据已经被取消!');
        }


        $record_code = (string) $db_oi['record_code'];
        $record_type = $db_oi['record_type'];
        $api_product = $db_oi['api_product'];
        $efast_store_code = $db_oi['efast_store_code'];


        $no_record_info = array('ydwms', 'qimen');
        if (in_array($api_product, $no_record_info)) {
            return $this->format_ret(-1, '', $api_product . '推送回传，不支持拉取回传信息!');
        }

        if (in_array($record_type, array('stm_diy', 'stm_split')) && in_array($api_product, array('iwms', 'iwmscloud'))) {
            $record_type_temp = 'goods_diy_record';
        } else {
            $record_type_temp = $record_type;
        }
        $wms_obj = $this->get_biz_wms($api_product, $record_type_temp);
        if ($wms_obj == FALSE) {
            return $this->format_ret(-1, '', ' 数据异常接口类型：' . $api_product . '不存在');
        }
        $api_product_arr = array('hwwms', 'ydwms', 'qimen');

        if ($type == 'oms' && $api_product == 'shunfeng') {
            $api_product_arr[] = 'shunfeng';
        }
        //如果是取消单据再次上传 取新单号
        if (in_array($api_product, $api_product_arr)) {
            $new_record_code = $this->is_canceled($record_code, $record_type);
        } else {
            $new_record_code = $record_code;
        }

        $wms_obj->wms_cfg = array();
        $ret_wms_info = $wms_obj->wms_record_info($new_record_code, $efast_store_code);
        if ($ret_wms_info['status'] < 0) {
            return $ret_wms_info;
        } else {
            //如果是取消单据再次上传 把新单号换成原来单号
            if (in_array($api_product, $api_product_arr)) {
                $ret_wms_info['data']['efast_record_code'] = $record_code;
            }
            //echo '<hr/>$ret_wms_info<xmp>'.var_export($ret_wms_info,true).'</xmp>';
            if ($ret_wms_info['data']['order_status'] == 'flow_end') {
                $obj_wms_record = load_model('wms/WmsRecordModel');
                $obj_wms_record->get_wms_cfg($efast_store_code);
                $ret = $obj_wms_record->uploadtask_order_end($record_code, $record_type, $ret_wms_info);
                if ($ret['status'] > 0 && $is_mode_show == 1) {
                    $sql = "select record_code,wms_record_code,express_code,express_no,wms_order_time from wms_{$type}_trade where record_code = '{$record_code}' and record_type = '{$record_type}'";
                    $result = ctx()->db->get_row($sql);
                    $sql = "select barcode,efast_sl,wms_sl from wms_{$type}_order where record_code = '{$record_code}' and record_type = '{$record_type}'";
                    $result['goods'] = ctx()->db->get_all($sql);
                    $result['order_status_txt'] = $ret_wms_info['data']['order_status_txt'];
                    $result['wms_order_time'] = date('Y-m-d H:i:s', $result['wms_order_time']);
                    return $this->format_ret(1, $result);
                }
            }
        }
        return $ret_wms_info;
    }

    private function is_canceled($record_code, $record_type) {
        $type_arr = array('sell_return', 'sell_record');
        if (in_array($record_type, $type_arr)) {
            $table = 'wms_oms_trade';
        } else {
            $table = 'wms_b2b_trade';
        }
        $sql = "select new_record_code from {$table} where record_code = :record_code and record_type = :record_type";
        $new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
        return !empty($new_record_code) ? $new_record_code : $record_code;
    }

    public function order_shipping($task_id, $type = 'b2b') {
        if ($type == 'b2b') {
            $sql = "select record_code,record_type,efast_store_code,api_product,efast_store_code,wms_order_flow_end_flag,express_code,express_no,wms_order_time from wms_{$type}_trade where id = :id";
        } else {
            $sql = "select record_code,record_type,efast_store_code,api_product,efast_store_code,wms_order_flow_end_flag,express_code,express_no,wms_order_time,order_weight from wms_{$type}_trade where id = :id";
        }
        $db_oi = $this->db->get_row($sql, array(':id' => $task_id));
        //echo '<hr/>$db_oi<xmp>'.var_export($db_oi,true).'</xmp>';die;
        $wms_order_flow_end_flag = (int) $db_oi['wms_order_flow_end_flag'];
        if ($wms_order_flow_end_flag <> 1) {
            return $this->format_ret(-1, array('data' => $db_oi['record_code']), $db_oi['record_code'] . ' 未完成收发的单据，不能处理');
        }
        $record_type = $db_oi['record_type'];
        if (in_array($record_type, array('stm_diy', 'stm_split'))) {
            $record_type_temp = 'goods_diy_record';
        } else {
            $record_type_temp = $record_type;
        }
        $record_code = $db_oi['record_code'];
        $express_code = $db_oi['express_code'];
        $express_no = $db_oi['express_no'];
        if($type == 'oms'){
            //快递方式映射匹配
            $ret = $this->get_sys_express($record_code,$express_code,$record_type);
            if($ret['status'] == -1){
                $express_code = $ret['data'];
            }
        }
        $order_weight = isset($db_oi['order_weight']) ? $db_oi['order_weight'] : 0;

        $record_time = ($db_oi['wms_order_time'] == 0) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $db_oi['wms_order_time']);

        $sql_str = $record_type_temp == 'goods_diy_record' ? '' : 'AND wms_sl>0';
        $sql = "select barcode,wms_sl as num from wms_{$type}_order where record_code = :record_code and record_type = :record_type {$sql_str}";
        $order_mx = ctx()->db->get_all($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
        if ($db_oi['record_type'] == 'sell_record') {
            $ret = $this->get_biz_dj($record_type_temp)->order_shipping($record_code, $record_time, $express_code, $express_no, $order_weight);
        } else {
            $ret = $this->get_biz_dj($record_type_temp)->order_shipping($record_code, $record_time, $order_mx, $express_code, $express_no);
        }
        if ($ret['status'] > 0) {
            load_model('wms/WmsRecordModel')->uploadtask_order_status_sync_success($record_code, $record_type);
        } else {
            load_model('wms/WmsRecordModel')->uploadtask_order_status_sync_fail($record_code, $record_type, $ret['message']);
        }
        return $ret;
    }

    /**
     * 以下是定时器任务
     */
    function upload_record_cli() {

        $sys_param = load_model('sys/SysParamsModel')->get_val_by_code(array('wms_upload_max'));

        foreach ($this->dj_type_arr as $v) {
            if ($v == 'sell_record') {
                if (isset($sys_param['wms_upload_max']) && $sys_param['wms_upload_max'] > 0) {

                    $this->create_cli_upload_task($v, $sys_param['wms_upload_max']);
                } else {
                    $this->cli_upload_batch_by_record_type($v);
                }
            } else {
                $this->cli_upload_batch_by_record_type($v);
            }
        }
    }

    function create_cli_upload_task($record_type, $wms_upload_max) {
        require_model('common/TaskModel');
        $task = new TaskModel();
        $task_data = array();
        $request['app_act'] = 'wms/wms_mgr/cli_upload_batch_by_record_type';
        for ($i = 0; $i < $wms_upload_max; $i++) {
            $request['app_fmt'] = 'json';
            $task_data['code'] = "cli_upload_batch_by_record_type_" . $i;
            $request['update_index'] = $i;
            $request['record_type'] = $record_type;

            $task_data['start_time'] = time();
            $task_data['request'] = $request;
            $task->save_task($task_data);
        }
    }

    //定时器任务：上传单据到WMS
    function cli_upload_batch_by_record_type($record_type, $update_index = -1) {
        $tag = load_model('wms/WmsRecordModel')->get_wms_record_mode($record_type);
        $sql = "select id,record_code from wms_{$tag}_trade where record_type = '{$record_type}' and upload_response_flag in(0,20) AND cancel_request_flag=0 AND cancel_response_flag=0";
        if ($tag == 'oms') {
            $sql .= " AND upload_fail_num<3 ";
        }


        $sys_param = load_model('sys/SysParamsModel')->get_val_by_code(array('wms_upload_max'));

        if (isset($sys_param['wms_upload_max']) && $update_index > -1 && $sys_param['wms_upload_max'] > 0) {
            $sql .= "  AND (id%{$sys_param['wms_upload_max']}) = {$update_index} ";
        }
        $sql .= " order by id  limit 10000";

        $db_arr = ctx()->db->get_all($sql);
        $this->is_cli = TRUE;
        foreach ($db_arr as $sub_arr) {
            $ret = $this->upload($sub_arr['id'], $tag);
            echo 'upload ' . $sub_arr['record_code'];
            print_r($ret);
        }
    }

    function cli_wms_record_info() {
        foreach ($this->dj_type_arr as $v) {
            $this->cli_wms_record_info_by_record_type($v);
        }
    }

    function cli_wms_record_info_by_record_type($record_type) {
        $tag = load_model('wms/WmsRecordModel')->get_wms_record_mode($record_type);
        $sql = "select id,record_code from wms_{$tag}_trade where record_type = :record_type and wms_order_flow_end_flag = 0 and upload_response_flag = 10 AND cancel_response_flag<>10 ";
        $sql_value = array();
        //只有售后服务单才会只获取在一个月内上传的入库状态,其他单据无限制
        if ($record_type == 'sell_return') {
            $pre_one_month = date('Y-m-d H:i:s', strtotime('-1 month'));
            $sql .= " AND upload_request_time>=:pre_time";
            $sql_value[':pre_time'] = $pre_one_month;
        }
        $sql_value[':record_type'] = $record_type;
        $db_arr = $this->db->get_all($sql, $sql_value);
        foreach ($db_arr as $sub_arr) {
            $ret = $this->wms_record_info($sub_arr['id'], $tag);
            echo 'wms_record_info ' . $sub_arr['record_code'];
            print_r($ret);
        }
    }

    function cli_order_shipping() {
        foreach ($this->dj_type_arr as $v) {
            $this->cli_order_shipping_by_record_type($v);
        }
        $this->auto_cancel_by_record_status();
        $this->order_shipping_fix();
    }

    function cli_order_shipping_by_record_type($record_type) {
        //$process_fail_num = 3;
        $tag = load_model('wms/WmsRecordModel')->get_wms_record_mode($record_type);
        $sql = "select id,record_code,process_fail_num from wms_{$tag}_trade where record_type = '{$record_type}'
                and wms_order_flow_end_flag = 1 and (process_flag = 0 or (process_flag = 20 and process_fail_num<3)) ";
        $db_arr = ctx()->db->get_all($sql);

        foreach ($db_arr as $sub_arr) {
            $ret = $this->order_shipping($sub_arr['id'], $tag);
            echo 'order_shipping ' . $sub_arr['record_code'];
            print_r($ret);
        }
    }

    //处理收发货状态后，自动跑验证逻辑,如果订单已确认，已通知配货，要自动取消订单
    function auto_cancel_by_record_status() {
        $sql = "SELECT
                    t2.*
                FROM
                    wms_oms_trade t1
                INNER JOIN  oms_sell_record t2 ON t1.record_code = t2.sell_record_code
                where t1.record_type = 'sell_record' and t1.upload_response_flag = 10 and t1.cancel_response_flag<>10 and t1.wms_order_flow_end_flag = 0
                and (t2.order_status<>1 or t2.shipping_status<>1)";
        $db_wms = ctx()->db->get_all($sql);
        //echo '<hr/>$db_wms<xmp>'.var_export($db_wms,true).'</xmp>';

        if (empty($db_wms)) {
            return $this->format_ret(1);
        }
        foreach ($db_wms as $sub_wms) {
            $ret = load_model('oms/SellRecordOptModel')->biz_intercept($sub_wms, 1, '');
            //echo '<hr/>$sub_wms<xmp>'.var_export($sub_wms,true).'</xmp>';
            //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
            if ($ret['status'] < 0) {
                $action_name = '订单拦截失败';
                $action_note = '订单状态不是通知配货状态.' . $ret['message'];
            } else {
                $action_name = '订单拦截';
                $action_note = '订单状态不是通知配货状态,进行订单拦截成功.';
            }
            load_model('oms/SellRecordModel')->add_action($sub_wms['sell_record_code'], $action_name, $action_note);
        }
        return $this->format_ret(1);
    }

    function sync_quehuo() { //自动服务1小时1次
        $arr = $this->get_exists_wms_store_code_arr();
        foreach ($arr as $val) {
            $this->sync_quehuo_incr($val, 65); //5为防止时间差
        }
    }

    //获取增量缺货订单
    function sync_quehuo_incr($efast_store_code, $each_time = 30) {
        $wms_cfg = $this->get_wms_cfg($efast_store_code);
        $class_name = 'WmsSellRecordModel';
        $ret = require_model("wms/" . $class_name);
        if ($ret === false) {
            return false;
        }

        $wms_obj = new $class_name();

        $biz_code = $this->api_product . '_quehou_sync';
        $prev_end_time = $this->get_incr_service_end_time($efast_store_code, $biz_code);
        $now_zero_time = date('Y-m-d', time()) . ' 00:00:00';
        if (empty($prev_end_time)) {
            $start_time = $now_zero_time;
        } else {
            $start_time = $prev_end_time < $now_zero_time ? $now_zero_time : $prev_end_time;
        }
        while (1) {
            $end_time = date('Y-m-d H:i:s', strtotime($start_time) + $each_time * 60);
            $end_time = $end_time > date('Y-m-d H:i:s') ? date('Y-m-d H:i:s') : $end_time;
            /*
              echo '<hr/>$start_time111<xmp>'.var_export($start_time,true).'</xmp>';
              echo '<hr/>$end_time111<xmp>'.var_export($end_time,true).'</xmp>';//die; */
            $ins_row = array(
                'efast_store_code' => $efast_store_code,
                'biz_code' => $biz_code,
                'start_time' => $start_time,
                'end_time' => $end_time,
            );
            $ret = M('wms_incr_time_tag')->insert($ins_row);
            $ins_id = $ret['data'];

            $ret = $wms_obj->sync_wms_quehuo($efast_store_code, $start_time, $end_time);
            if ($ret['status'] > 0) {
                $sql = "update wms_incr_time_tag set status = 1 where id = {$ins_id}";
                ctx()->db->query($sql);
            }
            $_nt = date('Y-m-d H:i:s', time());
            /*
              echo '<hr/>$end_time<xmp>'.var_export($end_time,true).'</xmp>';
              echo '<hr/>$_nt<xmp>'.var_export($_nt,true).'</xmp>'; */
            if (strtotime($end_time) >= strtotime($_nt)) {
                break;
            }
            $start_time = $end_time;
            //die;
        }
        $wms_obj->process_quehuo();
        return $this->format_ret(1, 'sync_quehuo_incr_success');
    }

    function order_shipping_fix() {
        $sql = "select record_code from wms_oms_trade where process_flag = '20' and process_err_msg = '解除库存锁定失败:找不到数据'";
        $record_code_arr = ctx()->db->get_all_col($sql);
        //echo '<hr/>$order_shipping_fix record_code_arr<xmp>'.var_export($record_code_arr,true).'</xmp>';die;
        if (empty($record_code_arr)) {
            return $this->format_ret(1);
        }
        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
        $default_lof_no = $ret_lof['data']['lof_no'];
        $default_lof_production_date = $ret_lof['data']['production_date'];

        $record_code_list = "'" . join("','", $record_code_arr) . "'";
        $sql = "INSERT IGNORE INTO oms_sell_record_lof (
                    pid,
                    record_type,
                    record_code,
                    deal_code,
                    goods_code,
                    spec1_code,
                    spec2_code,
                    sku,
                    store_code,
                    lof_no,
                    production_date,
                    num,
                    occupy_type
                ) SELECT
                    t1.sell_record_id AS pid,
                    1 AS record_type,
                    t1.sell_record_code,
                    t2.deal_code,
                    t2.goods_code,
                    t2.spec1_code,
                    t2.spec2_code,
                    t2.sku,
                    t1.store_code,
                    '{$default_lof_no}' AS lof_no,
                    '{$default_lof_production_date}' AS production_date,
                    t2.num,
                    1 AS occupy_type
                FROM
                    oms_sell_record t1,
                    oms_sell_record_detail t2
                WHERE
                    t1.sell_record_code = t2.sell_record_code and t1.sell_record_code in({$record_code_list})";
        ctx()->db->query($sql);

        $sql = "select efast_store_code from wms_oms_trade where process_flag = '20' and process_err_msg = '解除库存锁定失败:找不到数据'";
        $store_code = ctx()->db->getOne($sql);
        load_model('prm/InvModel')->inv_maintain_lock($store_code);

        $sql = "update wms_oms_trade set process_flag = 0 where process_flag = 20 and record_code  in({$record_code_list})";
        ctx()->db->query($sql);
        return $this->format_ret(1);
    }

    function get_record_is_cancel($record_code, $record_type) {
        $tb_type = 'oms';
        if ($record_type != 'sell_record') {
            $tb_type = 'b2b';
        }
        $sql = "select cancel_response_flag,upload_response_flag from wms_{$tb_type}_trade where record_code=:record_code  ";
        $row = $this->db->get_row($sql, array('record_code' => $record_code));
        if ($row['cancel_response_flag'] == 10 && $row['upload_response_flag'] == 10) {
            return TRUE;
        }
        return FALSE;
    }

    function get_wms_record_status($record_code, $record_type = 'sell_record') {
        $sql = "select record_code,upload_response_flag,record_type,efast_store_code,api_product,efast_store_code from wms_oms_trade where record_code = :record_code AND record_type=:record_type";
        $db_oi = $this->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => $record_type));

        $upload_response_flag = (int) $db_oi['upload_response_flag'];
        if ($upload_response_flag <> 10) {
            return $this->format_ret(-1, '', $record_code . ' 未上传成功，不能查询wms订单状态');
        }
        $record_code = (string) $db_oi['record_code'];
        $record_type = $db_oi['record_type'];
        $api_product = $db_oi['api_product'];
        $efast_store_code = $db_oi['efast_store_code'];

//                $record_type = 'sell_record';
//		$api_product =  'iwms';

        $wms_obj = $this->get_biz_wms($api_product, $record_type);


        if ($wms_obj == FALSE) {
            return $this->format_ret(-1, '', ' 数据异常接口类型：' . $api_product . '不存在');
        }
        return $wms_obj->get_record_flow($record_code, $efast_store_code);
    }

    function sync_jdwms_return() { //自动服务1小时1次
        $arr = $this->get_exists_wms_store_code_arr();
        foreach ($arr as $val) {
            $this->get_wms_cfg($val);
            if ($this->api_product == 'jdwms') {
                $this->sync_jd_return_incr($val, 65); //5为防止时间差
            }
        }
    }

    //获取增量缺货订单
    function sync_jd_return_incr($efast_store_code, $each_time) {
        $class_name = 'JdwmsSellReturnModel';
        $ret = require_model("wms/jdwms/" . $class_name);
        if ($ret === FALSE) {
            return FALSE;
        }
        $wms_obj = new $class_name();
        $biz_code = $this->api_product . '_return_sync';
        $prev_end_time = $this->get_incr_service_end_time($efast_store_code, $biz_code);
        $now_time = date('Y-m-d', time()) . ' 00:00:00';
        if (empty($prev_end_time)) {
            $start_time = $now_time;
        } else {
            $start_time = strtotime($prev_end_time) < strtotime($now_time) ? $prev_end_time : $now_time;
        }
        while (1) {
            $end_time = date('Y-m-d H:i:s', strtotime($start_time) + $each_time * 60);
            $end_time = strtotime($end_time) > time() ? date('Y-m-d H:i:s') : $end_time;
            $ins_row = array(
                'efast_store_code' => $efast_store_code,
                'biz_code' => $biz_code,
                'start_time' => $start_time,
                'end_time' => $end_time,
            );
            $ret = M('wms_incr_time_tag')->insert($ins_row);
            $ins_id = $ret['data'];
            $ret = $wms_obj->sync_jdwms_return_action($efast_store_code, $start_time, $end_time);
            if ($ret['status'] > 0) {
                $this->db->update('wms_incr_time_tag', array('status' => 1), array('id' => $ins_id));
            }
            $_nt = date('Y-m-d H:i:s');
            if (strtotime($end_time) >= strtotime($_nt)) {
                break;
            }
            $start_time = $end_time;
        }
        return $this->format_ret(1);
    }
    public function get_sys_express($record_code,$express_code,$record_type){
        if($record_type == 'sell_record'){
            $record = load_model('oms/SellRecordModel')->get_record_by_code($record_code,'store_code');
        }else if($record_type == 'sell_return'){
            $record = load_model('oms/SellReturnModel')->get_record_by_code($record_code,'store_code');
        }
        if(empty($record)) return $this->format_ret(1);
        //快递公司存不存在系统中
        $total = $this->db->get_value('select count(*) total from base_express where express_code = :express_code and status = 1',array(':express_code'=>$express_code));
        if($total > 0) return $this->format_ret(1);
        $wms_sql = 'select w.wms_config_id from wms_config w
                    INNER JOIN sys_api_shop_store s ON s.p_id=w.wms_config_id
                    where  s.p_type=1 AND s.shop_store_code= :shop_store_code AND s.shop_store_type=1';
        $data = $this->db->get_row($wms_sql,array(':shop_store_code'=>$record['store_code']));
        if(!empty($data)){
            //读取映射关系
            $wms_config_values = array(
                ':wms_config_id'=>$data['wms_config_id'],
                ':out_express_code'=>$express_code,
            );
            $wms_config_sql = 'select express_code 
                              from wms_express_config 
                              where wms_config_id = :wms_config_id 
                              and out_express_code = :out_express_code ';
            $express_code = $this->db->get_value($wms_config_sql,$wms_config_values);
            if($express_code != ''){
                return $this->format_ret(-1,$express_code);
            }
        }
        return $this->format_ret(1);

    }

}
