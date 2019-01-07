<?php

require_model('wms/WmsBaseModel');

class WmsRecordModel extends WmsBaseModel {

    function __construct() {
        parent::__construct();
    }

    //更新为上传中
    function uploadtask_uploading($record_code, $record_type, $wms_record_code) {
        $task_info = $this->get_task_info($record_code, $record_type);
        $order_status = $task_info['order_status'];
        if (!in_array($order_status, array('wait_upload', 'upload_fail'))) {
            return $this->format_ret(1);
        }
        $act = "upload_request_wms";
        $time = date('Y-m-d H:i:s');
        $sql = "update wms_oms_trade set wms_record_code = '{$wms_record_code}',upload_request_flag = 10,upload_request_time = '{$time}',cancel_response_flag = 0,upload_response_flag = 0 where record_code ='{$record_code}' and record_type = '{$record_type}';";
        ctx()->db->query($sql);

        $action_no = $act;
        $this->act_wms_log($record_code, $record_type, $action_no);
        return $this->format_ret(1);
    }

    //更新为上传成功
    function uploadtask_upload_success($record_code, $record_type, $wms_record_code) {
        if ($this->api_sync != 1) {
            $task_info = $this->get_task_info($record_code, $record_type);
            $order_status = $task_info['order_status'];
            if (!in_array($order_status, array('uploading'))) {
                return $this->format_ret(1);
            }
        }
        $wms_record_tbl = $this->get_wms_record_tbl($record_type);
        $act = "wms_response_upload_success";
        $time = date('Y-m-d H:i:s');

        $sql = "update {$wms_record_tbl} set wms_record_code = '{$wms_record_code}',upload_request_flag = 10,upload_request_time = '{$time}',upload_response_flag=10,upload_response_time = '{$time}',cancel_response_flag = 0,cancel_request_flag = 0 where record_code ='{$record_code}' and record_type = '{$record_type}'";
        ctx()->db->query($sql);
        $action_no = $act;
        $this->act_wms_log($record_code, $record_type, $action_no);
        //上传接口日志
        if ($record_type == 'sell_record') {
            //上传成功更新订单表上传时间
            $this->update_exp('oms_sell_record', ['wms_request_time' => time()], ['sell_record_code' => $record_code]);
            $this->set_tb_api_log($record_code);
        }

        return $this->format_ret(1);
    }

    private function set_tb_api_log($record_code) {
        $sql = "select deal_code as deal_code_list,sale_channel_code from wms_oms_trade  WHERE record_type='sell_record' AND record_code='{$record_code}'";
        $row = $this->db->get_row($sql);
        if ($row['sale_channel_code'] == 'taobao') {
            //御城河日志
            $trade = array($row);
            load_model('common/TBlLogModel')->set_log_multi($trade, 'WMS接口', 'sendOrder');
        }
    }

    //更新为上传失败
    function uploadtask_upload_fail($record_code, $record_type, $err_msg) {
        $task_info = $this->get_task_info($record_code, $record_type);
        $order_status = $task_info['order_status'];
        if (!in_array($order_status, array('wait_upload', 'uploading', 'upload_fail'))) {
            return $this->format_ret(1);
        }

        $act = "wms_response_upload_fail";
        $upload_response_err_msg = addslashes($err_msg);
        $time = date('Y-m-d H:i:s');
        $wms_record_tbl = $this->get_wms_record_tbl($record_type);


        $data = array(
            'upload_request_flag' => 0,
            'upload_request_time' => $time,
            'upload_response_flag' => 20,
            'upload_response_time' => $time,
            'upload_response_err_msg' => $upload_response_err_msg,
            'upload_fail_num' => $task_info['upload_fail_num'] + 1,
        );
        $where = " record_code ='{$record_code}' and record_type = '{$record_type}' AND upload_response_flag<> 10 ";

        $this->db->update($wms_record_tbl, $data, $where);

//        $sql = "update {$wms_record_tbl} set "
//        . "upload_request_flag = 0, upload_request_time='{$time}',"
//        . "upload_response_flag = 20,upload_response_time='{$time}',"
//        . "upload_response_err_msg = '{$upload_response_err_msg}',upload_fail_num = upload_fail_num+1 "
//        . "where record_code ='{$record_code}' and record_type = '{$record_type}' AND upload_response_flag<> 10 ";
        // ctx()->db->query($sql);
        // $num = CTX()->db->affected_rows();
        $action_no = $act;
        $this->act_wms_log($record_code, $record_type, $action_no, $err_msg);
        return $this->format_ret(1);
    }

    //更新为取消中
    function uploadtask_canceling($record_code, $record_type) {
        $task_info = $this->get_task_info($record_code, $record_type);
        $order_status = $task_info['order_status'];
        if (!in_array($order_status, array('upload_success', 'cancel_fail'))) {
            return $this->format_ret(1);
        }
        $act = "cancel_request_wms";
        $time = date('Y-m-d H:i:s');
        $wms_record_tbl = $this->get_wms_record_tbl($record_type);
        $sql = "update {$wms_record_tbl} set cancel_request_flag = 10,cancel_request_time = '{$time}',cancel_response_flag=0 where record_code ='{$record_code}' and record_type = '{$record_type}'";
        //echo $sql;die;
        ctx()->db->query($sql);

        $action_no = $act;
        $this->act_wms_log($record_code, $record_type, $action_no);
        return $this->format_ret(1);
    }

    /* 更新为取消成功
      is_cancel_tag = array('act'=>'quehuo | invalid_order | refund | force','msg'=>'WMS缺货取消 | 发现存在xxx退单 | 已上传的订单的状态不是已确认已通知配货 | 客服强制取消');
     */

    function uploadtask_cancel_success($record_code, $record_type, $is_cancel_tag = null, $is_force = 0, $ret_cancel = array()) {
        $task_info = $this->get_task_info($record_code, $record_type);
        if ($this->api_sync != 1) {
            $order_status = $task_info['order_status'];
            if (!in_array($order_status, array('canceling'))) {
                return $this->format_ret(1);
            }
        }

        $act = "wms_response_cancel_success";
        ctx()->db->begin_trans();

        $is_cancel_tag_act = isset($is_cancel_tag['act']) ? $is_cancel_tag['act'] : '';

        $cancel_after_invalid_order = $this->cancel_after_invalid_order;
        if ($is_cancel_tag_act == 'force_invalid_order') {
            $cancel_after_invalid_order = 1;
        }

        if ($cancel_after_invalid_order == 0) {
            //自动反确认单据
            if ($record_type == 'sell_record' && ($is_cancel_tag_act == '' || $is_cancel_tag_act == 'refund')) {
                $ret = load_model('oms/SellRecordOptModel')->opt_unconfirm($record_code);
            }

            if ($record_type == 'order_return') {
                $ret = load_model('oms/SellReturnOptModel')->opt_unconfirm($record_code);
            }
        }

        if ($cancel_after_invalid_order == 1) {
            if ($record_type == 'sell_record') {
                $skip_lock_check = 1;
                $ret = load_model('oms/SellRecordModel')->opt_cancel($record_code, $skip_lock_check);
            }

            if ($record_type == 'order_return') {
                $ret = load_model('oms/SellReturnModel')->opt_cancel($record_code);
            }
        }

        if (isset($ret['status']) && $ret['status'] < 0) {
            return $ret;
        }
        $time = date('Y-m-d H:i:s');
        $wms_record_tbl = $this->get_wms_record_tbl($record_type);


        $sql = "update {$wms_record_tbl} set cancel_request_flag = 10,cancel_request_time = '{$time}',cancel_response_flag=10,cancel_flag = 1,cancel_response_time='{$time}',new_record_code=''  where record_code ='{$record_code}' and record_type = '{$record_type}'";
        if ($ret_cancel['status'] == 10) {
            $sql .= " AND upload_request_flag=0  ";
        }
        CTX()->db->query($sql);
        $num = CTX()->db->affected_rows();
        if ($num == 0) {
            CTX()->db->rollback();
            return $this->format_ret(-1, '', '取消失败，单据状态变化...');
        }


        if ($is_force == 0) {
            $action_no = 'wms_response_cancel_success';
        } else {
            $action_no = 'wms_response_force_cancel_success';
        }


        $this->act_wms_log($record_code, $record_type, $action_no, '', $is_cancel_tag);
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
        $act = "wms_response_cancel_fail";
        $cancel_response_err_msg = addslashes($err_msg);
        $time = date('Y-m-d H:i:s');
        $wms_record_tbl = $this->get_wms_record_tbl($record_type);
        $sql = "update {$wms_record_tbl} set cancel_request_flag=10,cancel_request_time='{$time}', cancel_response_flag = 20,cancel_response_time='{$time}',cancel_response_err_msg='{$cancel_response_err_msg}' where record_code ='{$record_code}' and record_type = '{$record_type}'";

        ctx()->db->query($sql);
        $action_no = $act;
        $this->act_wms_log($record_code, $record_type, $action_no, $err_msg);
        return $this->format_ret(1);
    }

    //部分收发货 更新商品数量
    function uploadtask_order_goods_update($record_code, $record_type, $goods_data, $item_type = 1) {
        //获取上传到中间表的商品数据
        $b2c_record_type_arr = explode(',', 'sell_record,sell_return');
        if (in_array($record_type, $b2c_record_type_arr)) {
            $wms_mx_tbl = "wms_oms_order";
            $wms_tbl = "wms_oms_trade";
        } else {
            $wms_mx_tbl = "wms_b2b_order";
            $wms_tbl = "wms_b2b_trade";
        }
        $sql = "select efast_store_code,json_data from {$wms_tbl} where record_code =:record_code and record_type =:record_type";
        $sql_values = array(':record_code' => $record_code, ':record_type' => $record_type);
        $record_data = ctx()->db->get_row($sql, $sql_values);
        $ori_record_code = $record_code;
        if (empty($record_data)) {
            //原单号检索不到，则根据新单号检索数据
            $sql = "select efast_store_code,record_code,json_data from {$wms_tbl} where new_record_code =:record_code and record_type =:record_type";
            $record_data = ctx()->db->get_row($sql, $sql_values);
            $record_code = $record_data['record_code'];
        }
        if (empty($record_data)) {
            return $this->format_ret(-1, '', "未找到此单据或此单据未上传[{$ori_record_code}]");
        }
        //检查单据明细
        $json_data = empty($record_data['json_data']) ? "{}" : $record_data['json_data'];
        $json_data_arr = json_decode($json_data, true);
        if (empty($json_data_arr['goods'])) {
            return $this->format_ret(-1, '', "单据[{$record_code}]缺少明细,请检查是否已上传WMS");
        }
        $efast_mx = $json_data_arr['goods'];
        //初始化配置
        $efast_store_code = $record_data['efast_store_code'];
        $this->get_wms_cfg($efast_store_code, $record_type);

        //回传数据处理
        $barcode_arr = array();
        foreach ($goods_data as $sub_barcode) {
            $barcode = strtolower($sub_barcode['barcode']);
            if (isset($barcode_arr[$barcode])) {
                $sub_barcode['sl'] += $barcode_arr[$barcode]['sl'];
            }
            
            $barcode_arr[$barcode] = $sub_barcode;
        }

        $ins_arr = array();
        $create_time = date('Y-m-d H:i:s');
        foreach ($efast_mx as $sub_mx) {
             $barcode = strtolower($sub_mx['barcode']);
            if (isset($barcode_arr[$barcode])) {
                $efast_sl = $sub_mx['num'];
                $wms_sl = $barcode_arr[$barcode]['sl'];
                if (in_array($record_type, array('stm_diy', 'stm_split'))) {
                    $wms_sl = $efast_sl < 0 ? 0 - $wms_sl : $wms_sl;
                }

                $ins_arr[] = array('record_code' => $record_code,
                    'record_type' => $record_type,
                    'barcode' => $sub_mx['barcode'],
                    'efast_sl' => $efast_sl,
                    'wms_sl' => $wms_sl,
                    'create_time' => $create_time);
                unset($barcode_arr[$barcode]);
            }
        }
        if (!empty($barcode_arr)) {
            $no_barcode_arr = implode(',', array_keys($barcode_arr));
            return $this->format_ret(-1, '', "单据[{$record_code}],回传的商品[{$no_barcode_arr}]在原系统单据中不存在");
        }
        $update_str = "wms_sl = VALUES(wms_sl)+wms_sl";
        $ret = $this->insert_multi_duplicate($wms_mx_tbl, $ins_arr, $update_str);
        if ($ret['status'] != 1) {
            return $ret;
        }

        if ($this->wms_cfg['is_lof'] == 1) {
            $ret = $this->update_order_goods_lof($record_code, $record_type, $efast_mx, $goods_data);
            if ($ret['status'] != 1) {
                return $ret;
            }
        }

        //生成多次入库数据
        $this->create_api_return_order_detail($record_code, $record_type, $goods_data, $item_type);
        return $ret;
    }

    /**
     * 处理回传批次
     * @param array $lof_data 处理后批次数据
     * @param array $return_data 原批次数据
     */
    private function deal_lof(&$lof_data, $return_data) {
        $lof_arr = explode(';', $return_data['lof_no']);
        foreach ($lof_arr as $lof) {
            $pos = strpos($lof, ':');
            if ($pos !== FALSE) {
                //20170310:2(批次:数量)
                $return_data['lof_no'] = substr($lof, 0, $pos); //截取批次
                $return_data['wms_sl'] = substr($lof, $pos + 1); //截取数量
            } else {
                $return_data['lof_no'] = $lof;
                $return_data['wms_sl'] = $return_data['sl'];
            }
            unset($return_data['sl']);
            $k = $return_data['barcode'] . ',' . $return_data['lof_no'];
            if (isset($lof_data[$k])) {
                $lof_data[$k]['wms_sl'] += $return_data['wms_sl'];
            } else {
                $lof_data[$k] = $return_data;
            }
        }
    }

    /**
     * 更新回传批次明细
     * @param array $efast_mx 系统单据明细
     * @param array $goods_lof_data 回传商品明细
     */
    function update_order_goods_lof($record_code, $record_type, $efast_mx, $return_lof_data) {
        $wms_lof_table = in_array($record_type, array('sell_record', 'sell_return')) ? 'wms_oms_order_lof' : 'wms_b2b_order_lof';
        //转换数组键，处理批次号，重复数据的数量合并
        $lof_data = array();
        foreach ($return_lof_data as $val) {
            $this->deal_lof($lof_data, $val);
        }

        //组装批次数据
        $goods_arr = array();
        foreach ($efast_mx as $mx) {
            $barcode = $mx['barcode'];
            foreach ($mx['batchs'] as $batch) {
                $k = $barcode . ',' . $batch['lof_no'];
                if (isset($lof_data[$k])) {
                    $temp = $lof_data[$k];
                    $goods_arr[] = array(
                        'record_code' => $record_code,
                        'record_type' => $record_type,
                        'barcode' => $barcode,
                        'lof_no' => $temp['lof_no'],
                        'production_date' => $temp['production_date'],
                        'efast_sl' => $batch['num'],
                        'wms_sl' => $temp['wms_sl'],
                    );
                    unset($lof_data[$k]);
                } else {
                    $goods_arr[] = array(
                        'record_code' => $record_code,
                        'record_type' => $record_type,
                        'barcode' => $barcode,
                        'lof_no' => $batch['lof_no'],
                        'production_date' => $batch['production_date'],
                        'efast_sl' => $batch['num'],
                        'wms_sl' => 0,
                    );
                }
            }
        }

        if (!empty($lof_data)) {
            foreach ($lof_data as $val) {
                $goods_arr[] = array(
                    'record_code' => $record_code,
                    'record_type' => $record_type,
                    'barcode' => $val['barcode'],
                    'lof_no' => $val['lof_no'],
                    'production_date' => $val['production_date'],
                    'efast_sl' => 0,
                    'wms_sl' => $val['wms_sl'],
                );
            }
        }

        $update_str = "wms_sl = VALUES(wms_sl)+wms_sl";
        $ret = $this->insert_multi_duplicate($wms_lof_table, $goods_arr, $update_str);
        return $ret;
    }

    private function get_record_code_by_new(&$record_code, $record_type) {
        $record_type_arr = array('sell_record', 'sell_return');

        if (in_array($record_type, $record_type_arr)) {
            $sql = "select  record_code from  wms_oms_trade where record_code =:record_code AND record_type =:record_type   ";
        } else {
            $sql = "select  record_code from wms_b2b_trade where record_code =:record_code AND record_type =:record_type   ";
        }
        $sql_values = array(':record_code' => $record_code, ':record_type' => $record_type);
        $record_code = $this->db->get_value($sql, $sql_values);
        if (!empty($record_code)) {
            return true;
        }


        if (in_array($record_type, $record_type_arr)) {
            $sql = "select  record_code from  wms_oms_trade where new_record_code =:record_code AND record_type =:record_type   ";
        } else {
            $sql = "select  record_code from wms_b2b_trade where new_record_code =:record_code AND record_type =:record_type   ";
        }
        $old_record_code = $this->db->get_value($sql, $sql_values);
        if (!empty($old_record_code) && $old_record_code !== false) {
            $record_code = $old_record_code;
        }
    }

    //更新为收发货成功
    function uploadtask_order_end($record_code, $record_type, $ret, $allow_empty_barcode = 0) {
        if ($ret['status'] < 0) {
            return $ret;
        }

        $this->get_record_code_by_new($record_code, $record_type);

        $express_code = isset($ret['data']['express_code']) ? $ret['data']['express_code'] : '';
        $express_no = isset($ret['data']['express_no']) ? $ret['data']['express_no'] : '';
        $wms_order_time = isset($ret['data']['flow_end_time']) ? $ret['data']['flow_end_time'] : '';
        $barcode_data = isset($ret['data']['goods']) ? $ret['data']['goods'] : array();
        $wms_record_code = isset($ret['data']['wms_record_code']) ? $ret['data']['wms_record_code'] : '';
        $order_weight = isset($ret['data']['order_weight']) ? $ret['data']['order_weight'] : 0;

        //$this->create_api_return_order_detail($record_code,$record_type,$barcode_data);
        //todo $record_type 单据类型，和             $this->api_product 类型判断 支持 多次收货 另外保存明细

        $wms_record_mode = $this->get_wms_record_mode($record_type);
        $act = "wms_trade_success";
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
            $sql = "delete from wms_{$wms_record_mode}_order where record_code = '{$record_code}' and record_type = '{$record_type}'";
            ctx()->db->query($sql);
            $ins_arr = array();
            foreach ($barcode_arr as $sub_barcode) {
                $ins_arr[] = "('{$record_code}','{$record_type}','{$sub_barcode['barcode']}','-1','{$sub_barcode['sl']}')";
            }

            $sql = "insert into wms_{$wms_record_mode}_order(record_code,record_type,barcode,efast_sl,wms_sl) values" . join(',', $ins_arr);
            $ret = ctx()->db->query($sql);
            //生成明细的EFAST数量
            $this->create_record_efast_mx($record_code, $record_type);
            $is_update_data = 1;

            //增加批次回传
            if ($this->wms_cfg['is_lof'] === true) {
                $this->create_record_efast_mx_lof($record_code, $record_type, $barcode_data);
            }
        }
        $time = date('Y-m-d H:i:s');
        $wms_order_time = strtotime($wms_order_time);

        // $sql = "update wms_{$wms_record_mode}_trade set upload_request_flag = 10,wms_record_code = '{$wms_record_code}',upload_response_flag = 10,wms_order_flow_end_flag = 1,express_code = '$express_code',express_no = '$express_no',wms_order_time = '{$wms_order_time}' where record_code ='{$record_code}' and record_type = '{$record_type}'";
        //  ctx()->db->query($sql);
        $up_data['upload_request_flag'] = 10;
        $up_data['wms_record_code'] = $wms_record_code;
        $up_data['upload_response_flag'] = 10;
        $up_data['wms_order_flow_end_flag'] = 1;
        $up_data['express_code'] = $express_code;
        $up_data['express_no'] = $express_no;
        $up_data['wms_order_time'] = $wms_order_time;
        //单据重量
        if ($record_type == 'sell_record') {
            $up_data['order_weight'] = $order_weight;
        }
        $where = " record_code ='{$record_code}' and record_type = '{$record_type}' ";
        ctx()->db->update("wms_{$wms_record_mode}_trade", $up_data, $where);

        $action_no = $act;
        if ($is_update_data == 1) {
            $this->act_wms_log($record_code, $record_type, $action_no);
        }

        return $this->format_ret(1, $ret);
    }

    //更新WMS单据状态同步到EFAST成功
    function uploadtask_order_status_sync_success($record_code, $record_type) {
        $act = 'wms_sync_efast_status_success';
        $time = date('Y-m-d H:i:s');

        //process_flag

        $wms_record_tbl = $this->get_wms_record_tbl($record_type);
        $sql = "update {$wms_record_tbl} set process_time = '{$time}',process_flag = 30 where record_code = '{$record_code}' and record_type = '{$record_type}'";
        $ret = ctx()->db->query($sql);

        $action_no = $act;
        $this->act_wms_log($record_code, $record_type, $action_no);


        return $this->format_ret(1);
    }

    //更新WMS单据状态同步到EFAST失败
    function uploadtask_order_status_sync_fail($record_code, $record_type, $err_msg) {
        $act = 'wms_sync_efast_status_fail';
        $err_msg = addslashes($err_msg);
        $time = date('Y-m-d H:i:s');
        $wms_record_tbl = $this->get_wms_record_tbl($record_type);
        $sql = "update {$wms_record_tbl} set process_time = '{$time}',process_fail_num=process_fail_num+1,process_flag = 20,process_err_msg = '$err_msg' where record_code = '{$record_code}' and record_type = '{$record_type}' AND ( process_flag = 20 OR process_flag = 0 )";
        //echo $sql;
        ctx()->db->query($sql);
        $num = CTX()->db->affected_rows();
        if ($num == 1) {
            $action_no = $act;
            $this->act_wms_log($record_code, $record_type, $action_no, $err_msg);
        }
        return $this->format_ret(1);
    }

    //上传时检查退单
    function check_refund($deal_code_list) {
        $deal_code_arr = array_filter(explode(',', $deal_code_list));
        $ret = load_model('oms/SellRecordOptModel')->check_refund_by_deal_code($deal_code_arr);
        return $ret;
    }

    /**
     * $record_type = sell_record | order_return | spjhd | spthd | pfxhd | pfthd
     * $act = allow_upload | allow_cancel
     */
    function chk_allow_act($order, $record_type, $act, $ck_id) {
        
    }

    //上传后的处理
    function process_upload_after($record_code, $record_type, $ret) {
        if ($ret['status'] < 0) {
            $err_msg = $ret['message'];
            $this->uploadtask_upload_fail($record_code, $record_type, $err_msg);
            return $this->format_ret(-1, '', $err_msg);
        } else {
            $wms_record_code = $ret['data'];
            if ($this->api_sync == 1) {
                $this->uploadtask_upload_success($record_code, $record_type, $wms_record_code);
                //采购入库通知单IWMS主动回传(参数开启时，下发采购通知单时自动标识，避免自动服务重新拉取)
                $notice_iwms = array('iwms', 'iwmscloud');
                if ($record_type == 'pur_notice' && in_array($this->wms_cfg['api_product'], $notice_iwms)) {
                    if (isset($this->wms_cfg['notice_iwms']) && $this->wms_cfg['notice_iwms'] == 1) {
                        $this->update_exp('wms_b2b_trade', array('wms_order_flow_end_flag' => 1, 'process_flag' => 30), array('record_code' => $record_code, 'record_type' => $record_type));
                        //日志
                        $action_no = "wms_response_upload_success";
                        $action_msg_add="多批入库参数开启,将单据设置为已收发货,已处理";
                        $this->act_wms_log($record_code, $record_type, $action_no,$action_msg_add);
                    }
                }
            } else {
                $this->uploadtask_uploading($record_code, $record_type, $wms_record_code);
            }
            return $ret;
        }
    }

    //取消后的处理 如果存在取消要作废原单据的情况下，要传 $efast_store_code
    function process_cancel_after($record_code, $record_type, $ret, $is_cancel_tag = null, $efast_store_code = null) {
        if ($ret['status'] < 0) {
            $err_msg = $ret['message'];
            $this->uploadtask_cancel_fail($record_code, $record_type, $err_msg);
            return $this->format_ret(-1, '', $err_msg);
        } else {
            if ($this->api_sync == 1) {

                if ($ret['status'] > 0 && $ret['status'] < 10) {
                    $this->get_is_cancel_tag($is_cancel_tag);
                }

                $ret_new = $this->uploadtask_cancel_success($record_code, $record_type, $is_cancel_tag, $efast_store_code, $ret);
            } else {
                $ret_new = $this->uploadtask_canceling($record_code, $record_type);
            }

            return $ret_new;
        }
    }

    function get_is_cancel_tag(&$is_cancel_tag) {
        $wms_conf = require_conf('sys/wms');
        if (isset($wms_conf[$this->api_product]['intercept_is_cancel']) && $wms_conf[$this->api_product]['intercept_is_cancel'] === true) {
            $is_cancel_tag['act'] = 'force_invalid_order';
        }
    }

//$is_cancel_tag

    /**
     * no_found
     * wait_upload uploading upload_success upload_fail
     * canceling cancel_success cancel_fail
     * order_end order_close order_status_sync_success order_status_sync_fail
     */
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
        $wms_order_flow_end_flag = $info['wms_order_flow_end_flag'];
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
        if ($wms_order_flow_end_flag == 1 && is_null($ret)) {
            $ret = array('status' => 'order_end', 'status_txt' => '已收发货', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 已收发货 | 未处理");
        }
        if ($wms_order_flow_end_flag == 2 && is_null($ret)) {
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

    function get_task_info($record_code, $record_type, $task_id = 0) {
        $wms_recode_mode = $this->get_wms_record_mode($record_type);
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
							wms_order_flow_end_flag,
							efast_store_code,
                                                        upload_fail_num
						FROM
							wms_{$wms_recode_mode}_trade where ";
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

    function get_new_record_code($record_type) {
        //   $this->get_new_record_code();
        $new_record_code = time() . '_' . rand(100, 999);
//        switch ($record_type) {
//            case 'pur_notice':
//                $new_record_code = load_model('pur/PurchaseRecordModel')->create_fast_bill_sn();
//                break;
//            default :
//                break;
//        }
        return $new_record_code;
    }
    
    //创建多次收货明细
    private function create_api_return_order_detail($record_code, $record_type, $barcode_data, $item_type = 1) {
        $product_arr = array('ydwms', 'qimen', 'shunfeng', 'bswms');
        $type_arr = array('pur_notice', 'sell_return', 'wbm_notice', 'shift_out');
        $check = false;
        if (in_array($this->api_product, $product_arr) && in_array($record_type, $type_arr) && !empty($barcode_data)) {
            $check = true;
            $new_record_code = $this->get_new_record_code($record_type);
            $record_detail = array();
            if ($this->wms_cfg['is_lof'] == 1) {
                foreach ($barcode_data as $val) {
                    $temp = array(
                        'record_code' => $record_code,
                        'record_type' => $record_type,
                        'barcode' => $val['barcode'],
                        'sl' => $val['sl'],
                        'new_record_code' => $new_record_code,
                        'item_type' => $item_type,
                        'lof_no' => $val['lof_no'],
                        'production_date' => $val['production_date'],
                    );
                    $this->deal_lof($record_detail, $temp);
                }
            } else {
                foreach ($barcode_data as $val) {
                    $temp = array(
                        'record_code' => $record_code,
                        'record_type' => $record_type,
                        'barcode' => $val['barcode'],
                        'wms_sl' => $val['sl'],
                        'new_record_code' => $new_record_code,
                        'item_type' => $item_type,
                    );
                    $record_detail[] = $temp;
                }
            }

            $this->insert_multi_exp('wms_b2b_order_detail', $record_detail, true);
            $this->set_record_process($record_code, $record_type);
        }
        return $check;
    }

    private function set_record_process($record_code, $record_type) {
        //零售不支持
        $data = array('process_flag' => 0, 'wms_order_flow_end_flag' => 1);
        $where = array('record_code' => $record_code, 'record_type' => $record_type);
        $this->update_exp('wms_b2b_trade', $data, $where);
    }

    function create_record_efast_mx($record_code, $record_type) {
        $b2c_record_type_arr = explode(',', 'sell_record,sell_return');
        if (in_array($record_type, $b2c_record_type_arr)) {
            $wms_mx_tbl = "wms_oms_order";
            $wms_tbl = "wms_oms_trade";
        } else {
            $wms_mx_tbl = "wms_b2b_order";
            $wms_tbl = "wms_b2b_trade";
        }
        $sql = "select json_data from {$wms_tbl} where record_code =:record_code and record_type =:record_type";
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
                'efast_sl' => $sub_mx['num'],
                'wms_sl' => -1,
                'create_time' => $create_time);
        }
        $update_str = "efast_sl = VALUES(efast_sl)";
        $ret = $this->insert_multi_duplicate($wms_mx_tbl, $ins_arr, $update_str);
        return $ret;
    }

    function create_record_efast_mx_lof($record_code, $record_type, $barcode_data) {
        $b2c_record_type_arr = explode(',', 'sell_record,sell_return');
        if (in_array($record_type, $b2c_record_type_arr)) {
            $wms_mx_tbl = "wms_oms_order_lof";
            $record_type_new = ($record_type == 'sell_record') ? 1 : 2;
            $sql = "select * from oms_sell_record_lof where record_code =:record_code and record_type =:record_type";
            $sql_values = array(':record_code' => $record_code, ':record_type' => $record_type_new);
            $efast_mx = ctx()->db->get_all($sql, $sql_values);
        } else {
            $wms_mx_tbl = "wms_b2b_order_lof";
            $wms_tbl = "b2b_lof_datail";
            $sql = "select * from {$wms_tbl} where order_code =:record_code and order_type =:record_type";
            $sql_values = array(':record_code' => $record_code, ':record_type' => $record_type);
            $efast_mx = ctx()->db->get_all($sql, $sql_values);
        }


        $ins_arr = array();
        $create_time = date('Y-m-d H:i:s');
        $barcode_lof_data = array();
        foreach ($efast_mx as $sub_mx) {
            $key_arr = array('barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_mx['sku'], $key_arr);
            $ins_arr[] = array('record_code' => $record_code,
                'record_type' => $record_type,
                'barcode' => $sku_info['barcode'],
                'lof_no' => $sub_mx['lof_no'],
                'production_date' => $sub_mx['production_date'],
                'efast_sl' => $sub_mx['num'],
                'wms_sl' => 0,
                'create_time' => $create_time);
            $barcode_lof_data[$sku_info['barcode']][$sub_mx['lof_no']] = array('lof_no' => $sub_mx['lof_no'], 'production_date' => $sub_mx['production_date'], 'num' => $sub_mx['num']);
        }
        $update_str = "efast_sl = VALUES(efast_sl)";
        $ret = $this->insert_multi_duplicate($wms_mx_tbl, $ins_arr, $update_str);
        $ins_arr_new = array();
        foreach ($barcode_data as $val) {
            $lof_no = trim($val['lof_no']);
            if (!isset($val['production_date']) && !empty($lof_no)) {
                $val['production_date'] = $this->get_production_date_by_barcode($val['lof_no'], $val['barcode'], $efast_mx[0]['store_code']);
                if (isset($barcode_lof_data[$val['barcode']][$val['lof_no']])) {
                    unset($barcode_lof_data[$val['barcode']][$val['lof_no']]);
                }
            } else {
                $this->get_lof_by_barcode($barcode_lof_data, $val);
            }

            $ins_arr_new[] = array('record_code' => $record_code,
                'record_type' => $record_type,
                'barcode' => $val['barcode'],
                'lof_no' => $val['lof_no'],
                'production_date' => $val['production_date'],
                'efast_sl' => 0,
                'wms_sl' => $val['sl'],
                'create_time' => $create_time);
        }



        $update_str = "wms_sl = VALUES(wms_sl)";
        $ret = $this->insert_multi_duplicate($wms_mx_tbl, $ins_arr_new, $update_str);

        return $ret;
    }

    private function get_lof_by_barcode(&$barcode_lof_data, &$row) {
        $lof_data = &$barcode_lof_data[$row['barcode']];
        foreach ($lof_data as $lof_no => $val) {
            if ($row['num'] == $row['sl']) {
                $row['lof_no'] = $val['lof_no'];
                $row['production_date'] = $val['production_date'];
                unset($lof_data[$lof_no]);
                break;
            }
        }
    }

    private function get_production_date_by_barcode($lof_no, $barcode, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $sku = $this->db->get_value("select sku from goods_sku where barcode = :barcode", array(':barcode' => $barcode));
        $sql = "select production_date from goods_lof where sku =:sku AND lof_no=:lof_no ";
        $production_date = $this->db->get_value($sql, array(':sku' => $sku, 'lof_no' => $lof_no));
        if ($this->api_product == 'hwwms') {
            $new_production_date = $this->hw_production_date($lof_no);
            if (empty($production_date)) {
                $insert_lof[] = array(
                    'sku' => $sku,
                    'lof_no' => $lof_no,
                    'production_date' => $new_production_date,
                );
                $update_str = "production_date = VALUES(production_date)";
                $ret = $this->insert_multi_duplicate('goods_lof', $insert_lof, $update_str);
            } elseif ($new_production_date != $production_date) {
                $this->db->update('goods_lof', array('production_date' => $new_production_date), array('sku' => $sku, 'lof_no' => $lof_no));
            }
            $production_date = $new_production_date;
        } else {
             $lof_data = load_model("prm/GoodsLofModel")->get_sys_lof();
            $production_date = empty($production_date) ? $lof_data['production_date'] : $production_date;
        }
        return $production_date;
    }

    function hw_production_date($lof_no) {
        $len = strlen($lof_no);
//        if($len==8){
//            $date = substr($lof_no, 0,4)."-".substr($lof_no, 4,2)."-".substr($lof_no, 6,2);
//        }
        return substr($lof_no, 0, 4) . "-" . substr($lof_no, 4, 2) . "-" . substr($lof_no, 6, 2);
    }

    function check_efast_wms_sku_match($record_code, $record_type, $is_match_sl = 0) {
        $b2c_record_type_arr = explode(',', 'sell_record,sell_return');
        if (in_array($record_type, $b2c_record_type_arr)) {
            $wms_mx_tbl = "wms_oms_order";
        } else {
            $wms_mx_tbl = "wms_b2b_order";
        }

        $sql = "select barcode,efast_sl,wms_sl from {$wms_mx_tbl} where record_code =:record_code and record_type =:record_type";
        $db_mx = ctx()->db->get_all($sql, array(':record_code' => $record_code, ':record_type' => $record_type));

        $err_arr = array();
        foreach ($db_mx as $sub_mx) {
            if ($sub_mx['efast_sl'] == -1) {
                $err_arr[] = "wms的条码" . $sub_mx['barcode'] . " 在efast中不存在";
            }
            if ($sub_mx['wms_sl'] == -1) {
                $err_arr[] = "efast的条码" . $sub_mx['barcode'] . " 在wms中不存在";
            }
            if ($sub_mx['efast_sl'] != $sub_mx['wms_sl'] && $is_match_sl == 1) {
                $err_arr[] = "条码" . $sub_mx['barcode'] . " 数量不匹配,efast数量" . $sub_mx['efast_sl'] . ",wms数量" . $sub_mx['wms_sl'];
            }
        }
        if (empty($err_arr)) {
            $err_msg = join(" \n ", $err_arr);
            return $this->format_ret(-1, '', $err_msg);
        }

        return $this->format_ret(1);
    }

    function force_wms_cancel($task_id, $tips) {
        $sql = "select record_code,record_type,efast_store_code from wms_oms_trade where upload_response_flag = 10 and cancel_request_flag = 0 and wms_order_flow_end_flag = 0 and id = $task_id";
        $row = ctx()->db->getRow($sql);
        $record_code = $row['record_code'];
        $record_type = $row['record_type'];
        $efast_store_code = $row['efast_store_code'];
        if (empty($row)) {
            return $this->format_ret(-1, '', '只有已上传未发货未取消的单据才能进行此操作');
        }
        if (trim((string) $tips) == '') {
            return $this->format_ret(-1, '', '请输入WMS取消的原因');
        }
        $is_cancel_tag = array('act' => 'force', 'msg' => '客服手工取消-' . $tips);
        $ret = $this->uploadtask_cancel_success($record_code, $record_type, 1, $is_cancel_tag, $efast_store_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
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

    /**
     * 生成统一待上传任务
     * record_code record_type
     */
    function uploadtask_add($task_info, $deal_code_arr) {
        $record_code = (string) $task_info['record_code'];
        $record_type = (string) $task_info['record_type'];
        $efast_store_code = (string) $task_info['efast_store_code'];
        $deal_code = (string) implode(',', array_unique($deal_code_arr));
        $buyer_name = addslashes((string) $task_info['buyer_name']);
        $api_product = (string) $task_info['api_product'];
        $json_data = addslashes((string) $task_info['json_data']);

        $sale_channel_code = addslashes((string) $task_info['sale_channel_code']);
        $shop_code = addslashes((string) $task_info['shop_code']);

        if ($record_code == '') {
            return $this->format_ret(-1, '', '单据编号不能为空');
        }
        if ($record_type == '') {
            return $this->format_ret(-1, '', '单据类型不能为空');
        }
        if ($efast_store_code == '') {
            return $this->format_ret(-1, '', 'efast仓库代码不能为空');
        }

        $tbl = $this->get_wms_record_tbl($record_type);

        $sql = "SELECT id,upload_request_flag,upload_response_flag,cancel_response_flag,wms_order_flow_end_flag,api_product FROM {$tbl} WHERE record_code='{$task_info['record_code']}' AND record_type='{$task_info['record_type']}'";
        $awt_row = $this->db->get_row($sql);

        if (!empty($awt_row)) {
            $upload_request_flag = (int) $awt_row['upload_request_flag'];
            $upload_response_flag = (int) $awt_row['upload_response_flag'];
            $cancel_response_flag = (int) $awt_row['cancel_response_flag'];
            $wms_order_flow_end_flag = (int) $awt_row['wms_order_flow_end_flag'];
            $awt_id = (int) $awt_row['id'];
            //echo "<xmp>";debug_print_backtrace();echo "</xmp>";
            if ($wms_order_flow_end_flag > 0) {
                return $this->format_ret(-1, '', '已收发货的单据不能生成WMS待上传任务');
            }
            if ($upload_response_flag == 10 && $cancel_response_flag <> 10) {
                return $this->format_ret(-1, '', '上传成功的单据不能生成WMS待上传任务');
            }
            if ($upload_request_flag == 10 && $cancel_response_flag <> 10) {
                return $this->format_ret(-1, '', '上传中的单据不能生成WMS待上传任务');
            }
        }

        $create_time = time();
        if ($tbl == 'wms_oms_trade') {
            $sql = "insert into wms_oms_trade(record_code,record_type,efast_store_code,upload_response_flag,cancel_response_flag,deal_code,buyer_name,create_time,api_product,json_data,sale_channel_code,shop_code,upload_fail_num) values('{$record_code}','{$record_type}','{$efast_store_code}',0,0,'{$deal_code}','{$buyer_name}','{$create_time}','{$api_product}','{$json_data}','{$sale_channel_code}','{$shop_code}',0) on duplicate key update upload_request_flag=0,cancel_request_flag=0,upload_response_flag=0,cancel_response_flag=0,create_time = values(create_time),efast_store_code = values(efast_store_code),api_product = values(api_product),json_data=values(json_data),shop_code = values(shop_code),sale_channel_code = values(sale_channel_code),upload_fail_num = values(upload_fail_num)";
        } else {
            $sql = "insert into wms_b2b_trade(record_code,record_type,efast_store_code,upload_response_flag,cancel_response_flag,create_time,api_product,json_data) values('{$record_code}','{$record_type}','{$efast_store_code}',0,0,'{$create_time}','{$api_product}','{$json_data}') on duplicate key update upload_request_flag=0,cancel_request_flag=0,upload_response_flag=0,cancel_response_flag=0,create_time = values(create_time),efast_store_code = values(efast_store_code),api_product = values(api_product),json_data=values(json_data)";
        }

        $this->db->query($sql);
        $insert_id = $this->db->insert_id();
        //echo '<hr/>$sql<xmp>'.var_export($sql,true).'</xmp>';
        //echo '<hr/>$insert_id<xmp>'.var_export($insert_id,true).'</xmp>';

        if (!empty($awt_row)) {
            return $this->format_ret(1, $awt_id);
        }
        return $this->format_ret(1, $insert_id);
    }

    //通过本地数据来显示wms的相关信息(如果是中间件对接的这个接口，则调用这个方法，用于显示WMS订单详情用)
    function show_wms_status_by_local($task_id) {
        $sql = "select * from wms_oms_trade where id = $task_id";
        $awt_row = ctx()->db->getRow($sql);
        $record_code = $awt_row['record_code'];
        $record_type = $awt_row['record_type'];

        $status_ret = $this->get_status_exp($awt_row);

        $ret['order_status_txt'] = $status_ret['status_txt'];
        $ret['shipping_name'] = $awt_row['shipping_name'];
        $ret['invoice_no'] = $awt_row['invoice_no'];
        $ret['flow_end_time'] = $awt_row['wms_order_time'];
        $ret['record_code'] = $awt_row['record_code'];
        $ret['record_code'] = $awt_row['record_code'];
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';

        $sql = "select barcode,wms_num as sl from api_wms_order where record_code = '{$record_code}' and record_type = '{$record_type}'";
        $ret['mx'] = ctx()->db->getAll($sql);

        //转换成标准格式返回
        return $ret;
    }

    //根据收发货的结果来更新wms中间表
    function update_wms_result($record_code, $record_type, $wms_mx = array(), $express_code = '', $express_no = '', $wms_empty_mx = 0) {
        $cur_wms_result_md5 = md5(json_encode($wms_mx) . ',' . $express_code . ',' . $express_no);
        $sql = "select wms_order_flow_end_flag,process_flag,wms_result_md5,json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $wms_row = ctx()->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
        if ($wms_row['process_flag'] == '30') {
            return $this->format_ret(1, '', '已处理完成的单据不能更新数据');
        }
        if ($wms_row['wms_result_md5'] == $cur_wms_result_md5) {
            return $this->format_ret(1, '', 'wms收发货数据没有改变，无需更新');
        }

        if ($wms_empty_mx == 0) {
            $json_data_arr = json_decode($wms_row['json_data'], true);
            $efast_mx = $json_data_arr['goods'];
            $efast_mx = load_model('util/ViewUtilModel')->get_map_arr($efast_mx, 'barcode', 0, 'num');
            $wms_mx = load_model('util/ViewUtilModel')->get_map_arr($wms_mx, 'barcode', 0, 'num');

            $barcode_arr = array_unique(array_merge(array_keys($efast_mx), array_keys($wms_mx)));

            $save_mx = array();
            foreach ($barcode_arr as $barcode) {
                $_efast_sl = isset($efast_mx[$barcode]) ? $efast_mx[$barcode] : -1;
                $_wms_sl = isset($wms_mx[$barcode]) ? $wms_mx[$barcode] : -1;
                $save_mx[] = array('record_code' => $record_code, 'record_type' => $record_type, 'barcode' => $barcode, 'efast_sl' => $_efast_sl, 'wms_sl' => $_wms_sl);
            }
        }

        if (!empty($wms_row['wms_result_md5'])) {
            $sql = "delete from wms_oms_order where record_code = :record_code and record_type = :record_type";
            ctx()->db->query($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
        }

        $sql = "update wms_oms_trade set wms_order_flow_end_flag = 1,express_code = :express_code,express_no = :express_no where record_code = :record_code and record_type = :record_type";
        ctx()->db->query($sql, array(':record_code' => $record_code, ':record_type' => $record_type));

        return $this->format_ret(1);
    }

    function append_mx_barcode_by_sku($mx_data, $check_no_match_sku = 1, $flds = '') {
        $mx_map = load_model('util/ViewUtilModel')->get_map_arr($mx_data, 'sku');
        $sku_list = "'" . join("','", array_keys($mx_map)) . "'";
        $sql = "select sku,barcode from goods_sku where sku in({$sku_list})";
        $db_barcode = ctx()->db->get_all($sql);
        $barcode_map = load_model('util/ViewUtilModel')->get_map_arr($db_barcode, 'sku');
        $flds_arr = explode(',', $flds);

        foreach ($mx_data as $k => $sub_map) {
            $_find_info = isset($barcode_map[$sub_map['sku']]) ? $barcode_map[$sub_map['sku']] : '';
            if (empty($_find_info) && $check_no_match_sku == 1) {
                return $this->format_ret(-1, '', $sub_map['sku'] . '找不到对应的条码');
            }
            $_find_info = array_merge($_find_info, $sub_map);
            $_row = array('sku' => $sub_map['sku'], 'barcode' => $_find_info['barcode'], 'num' => $sub_map['num']);
            foreach ($flds_arr as $_fld) {
                if (isset($sub_map[$_fld])) {
                    $_row[$_fld] = $sub_map[$_fld];
                }
            }
            $mx_data[$k] = $_row;
        }
        return $this->format_ret(1, $mx_data);
    }

    function act_wms_log($record_code, $record_type, $action_no, $action_msg_add = '') {
        $act_arr = array(
            'upload_request_wms' => array('wms上传中', 'EFAST发起：上传单据到WMS.'),
            'wms_response_upload_success' => array('wms上传成功', 'EFAST接收：WMS上传成功.'),
            'wms_response_upload_fail' => array('wms上传失败', 'EFAST接收：WMS上传失败.'),
            'cancel_request_wms' => array('wms取消中', 'EFAST发起：取消WMS单据请求.'),
            'wms_response_cancel_success' => array('wms取消成功', 'EFAST接收：WMS取消单据成功.'),
            'wms_response_force_cancel_success' => array('wms强制取消成功', 'EFAST接收：WMS强制取消单据成功.'),
            'wms_response_cancel_fail' => array('wms取消失败', 'EFAST接收：WMS取消失败.'),
            'wms_trade_success' => array('wms收发货成功', 'EFAST接收：WMS发货/收货成功.'),
            'wms_trade_close' => array('wms单据作废', 'EFAST接收：WMS关闭订单.'),
            'wms_sync_efast_status_success' => array('wms单据状态同步成功', 'EFAST同步：WMS收发货状态同步到efast成功.'),
            'wms_sync_efast_status_fail' => array('wms单据状态同步失败', 'EFAST同步：WMS收发货状态同步到efast失败.'),
        );

        $action_time = date('Y-m-d H:i:s');
        $act_row = $act_arr[$action_no];
        $action_name = $act_row[0];
        $action_note = $act_row[1];

        if ((string) $action_msg_add <> '') {
            $action_note .= $action_msg_add;
        }
        $this->save_wms_log($record_code, $record_type, $action_time, $action_no, $action_note);
        if ($record_type == 'sell_record') {
            load_model('oms/SellRecordActionModel')->add_action($record_code, $action_name, $action_note);
        }
        if ($record_type == 'sell_return') {
            $sql = "select * from oms_sell_return where sell_return_code = '{$record_code}'";
            $record = ctx()->db->get_row($sql);
            load_model('oms/SellReturnModel')->add_action($record, $action_note);
        }
        return $this->format_ret(1);
    }

    function save_wms_log($record_code, $record_type, $action_time, $action_no, $action_msg) {
        $user_name = $this->sys_user_name();
        $ins_arr = array(
            'record_code' => $record_code,
            'record_type' => $record_type,
            'action_time' => $action_time,
            'action' => $action_no,
            'action_msg' => $action_msg,
            'user_name' => $user_name,
        );
        $wms_recode_mode = $this->get_wms_record_mode($record_type);
        $this->db->autoExecute("wms_{$wms_recode_mode}_log", $ins_arr);
        return $this->format_ret(1);
    }

    function uploadtask_cancel_chk_allow($task_info) {
        $record_code = (string) $task_info['record_code'];
        $record_type = (string) $task_info['record_type'];
        if ($record_code == '') {
            return $this->format_ret(-1, '', '单据编号不能为空');
        }
        if ($record_type == '') {
            return $this->format_ret(-1, '', '单据类型不能为空');
        }
        $task_info = $this->get_task_info($record_code, $record_type);
        if (is_array($task_info)) {
            $order_status = $task_info['order_status'];
            if ($order_status == 'order_end') {
                return $this->format_ret(-1, '', '已收发货的单据不能取消WMS待上传任务');
            }
            if ($order_status == 'upload_success') {
                return $this->format_ret(-1, '', '上传成功的单据不能取消WMS待上传任务');
            }
            if ($order_status == 'uploading') {
                return $this->format_ret(-1, '', '上传中的单据不能取消WMS待上传任务');
            }
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-1, '', '找不到单号为' . $record_code . '的上传任务');
        }
    }

    /*     * 取消统一待上传任务(没上传时)
     * record_code record_type
     */

    function uploadtask_cancel($task_info, $user_id, $user_name) {
        $ret = $this->uploadtask_cancel_chk_allow($task_info);
        $record_code = (string) $task_info['record_code'];
        $record_type = (string) $task_info['record_type'];
        if ($ret['status'] < 0) {
            $err = $this->get_error();
            $err_msg = $err['msg'];
            return $this->uploadtask_cancel_fail($record_code, $record_type, $user_id, $user_name, $err_msg);
        } else {
            return $this->uploadtask_cancel_success($record_code, $record_type, $user_id, $user_name);
        }
    }

    //获取统一上传任务信息
    function uploadtask_get($task_info) {
        $sql = "select record_code,record_type,upload_request_flag,upload_response_flag,cancel_request_flag,cancel_response_flag,wms_order_flow_end_flag,process_flag from wms_oms_trade";
        if (is_array($task_info)) {
            $record_code = $task_info['record_code'];
            $record_type = $task_info['record_type'];
            $sql .= " where record_code = {$record_code} and record_type = {$record_type}";
            $awt_row = $this->db->getRow($sql);
            if (empty($awt_row)) {
                return $this->format_ret(-1, '', '找不到单据号为{$record_code}的任务');
            }
        } else {
            $task_id = $task_info;
            $sql .= " where id = $task_id";
            $awt_row = $this->db->getRow($sql);
            if (empty($awt_row)) {
                return $this->format_ret(-1, '', '找不到ID号为' . $task_id . '的任务');
            }
        }

        $record_code = (string) $awt_row['record_code'];
        $record_type = (string) $awt_row['record_type'];
        if ($record_code == '' || $record_type == '') {
            return $this->format_ret(-1, '', '单据号和单据类型不能为空');
        }
        return $awt_row;
    }

    function check_wms_order_status($task_info, $act, $allow_no_exists_dj = 0) {
        $awt_row = $this->uploadtask_get($task_info);
        if ($allow_no_exists_dj == 1 && $awt_row === false) {
            return $this->format_ret(1);
        }
        if ($awt_row === false) {
            return false;
        }
        $record_code = $awt_row['record_code'];
        $record_type = $awt_row['record_type'];
        $upload_request_flag = (int) $awt_row['upload_request_flag'];
        $upload_response_flag = (int) $awt_row['upload_response_flag'];
        $cancel_request_flag = (int) $awt_row['cancel_request_flag'];
        $cancel_response_flag = (int) $awt_row['cancel_response_flag'];
        $wms_order_flow_end_flag = (int) $awt_row['wms_order_flow_end_flag'];
        $process_flag = (int) $awt_row['process_flag'];

        if ($act == "upload_request_wms") {
            if ($upload_request_flag <> 0 || $wms_order_flow_end_flag <> 0) {
                return $this->format_ret(-1, '', '单据不是待上传状态');
            }
        }

        if ($act == "cancel_request_wms") {
            if ($upload_response_flag <> 10 || $wms_order_flow_end_flag <> 0 || $cancel_response_flag <> 10) {
                return $this->format_ret(-1, '', '单据不是上传成功状态，不允许取消');
            }
        }

        if ($act == "wms_trade_success" || $act == "wms_trade_close") {
            if ($process_flag == 30) {
                return $this->format_ret(-1, '', 'WMS单据收发货数据已同步到EFAST,不允许更新状态');
            }
        }
        return $awt_row;
    }

    function get_wms_record_tbl($record_type) {
        if ($record_type == 'sell_record' || $record_type == 'sell_return') {
            $tbl = "wms_oms_trade";
        } else {
            $tbl = "wms_b2b_trade";
        }
        return $tbl;
    }

    function get_wms_record_mode($record_type) {
        if ($record_type == 'sell_record' || $record_type == 'sell_return') {
            $tbl = "oms";
        } else {
            $tbl = "b2b";
        }
        return $tbl;
    }

    function check_mx_lof($record_code, $record_type, $store_code) {
        //调整移动仓库单据批次
        $inv_type_arr = array('sell_record' => 'oms', 'shift_out' => 'shift_out', 'pur_return_notice' => 'pur_return_notice', 'wbm_notice' => 'wbm_notice');

//                'shift_out' => array('occupy_type' => '0,1,2'),
//            'shift_in' => array('occupy_type' => '0,3'),

        $this->get_wms_cfg($store_code);
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;


        if ($is_lof == 1) {
            if ($inv_type_arr[$record_type] == 'oms') {
                $table_lof = 'wms_oms_order_lof';
            } else {
                $table_lof = 'wms_b2b_order_lof';
            }
            $sql = "select s.goods_code,s.spec1_code,s.spec2_code,s.sku,l.lof_no,l.production_date,l.wms_sl-l.efast_sl as num  "
                    . " from {$table_lof} l"
                    . " INNER JOIN goods_sku s ON l.barcode=s.barcode"
                    . " where l.record_code=:record_code AND l.record_type=:record_type AND l.efast_sl<>l.wms_sl";
            $sql_values = array(':record_code' => $record_code, ':record_type' => $record_type);
            $lof_data = $this->db->get_all($sql, $sql_values);
            if (!empty($lof_data)) {
                $record_type_new = $inv_type_arr[$record_type];
                $record_data = array('record_code' => $record_code, 'record_type' => $record_type_new, 'store_code' => $store_code);
                return load_model('inv/InvAjustModel')->adjust_record_lock_lof_inv($record_data, $lof_data);
            } else {
                $sql = "select count(1) from wms_b2b_order_lof where  record_code=:record_code AND record_type=:record_type ";
                $check_num = $this->db->get_value($sql, $sql_values);
                if ($check_num == 0) {
                    return $this->format_ret(-1, '', '移仓回传明细不能为空！');
                }
            }
        }
        return $this->format_ret(1);
    }

    /**
     * 检查移仓单状态
     * @param string $record_code 单据编号
     * @param string $record_type 单据类型
     * @return array 单据数据
     */
    protected function check_shift_record($record_code, $record_type) {
        $sql = "SELECT * FROM stm_store_shift_record WHERE record_code = :record_code";
        $notice_info = ctx()->db->get_row($sql, array(':record_code' => $record_code));
        $msg = '';
        if (empty($notice_info)) {
            $msg = '单据不存在';
        } else if ($notice_info['is_sure'] == 0) {
            $msg = '单据未确认';
        } else if ($notice_info['is_shift_out'] > 0 && $record_type == 'shift_out') {
            $msg = '单据已移出';
        } else if ($notice_info['is_shift_in'] > 0 && $record_type == 'shift_in') {
            $msg = '单据已完成';
        }
        if ($msg != '') {
            return $this->format_ret(-1, '', $record_code . $msg);
        }
        return $this->format_ret(1, $notice_info);
    }

    /**
     * 检查单据状态
     * @param string $record_code 单据编号
     * @param string $record_type 单据类型
     * @return array 单据数据
     */
    protected function check_record($record_code, $record_type) {
        switch ($record_type) {
            case 'pur_return_notice':
                $sql = "SELECT return_notice_record_id,record_code,record_type_code,supplier_code,org_code,user_code,order_time,record_time,store_code,rebate,init_code,out_time,relation_code,num,finish_num,money,is_sure,is_execute,is_stop,is_finish,remark FROM pur_return_notice_record WHERE record_code = :record_code";
                break;
            case 'wbm_notice':
                $sql = "SELECT notice_record_id,record_code,record_type_code,distributor_code,org_code,user_code,store_code,rebate,init_code,brand_code,is_sure,is_execute,is_stop,is_finish,jx_code FROM wbm_notice_record WHERE record_code = :record_code";
                break;
        }

        $record = ctx()->db->get_row($sql, array(':record_code' => $record_code));
        $msg = '';
        if (empty($record)) {
            $msg = '单据不存在';
        } else if ($record['is_sure'] == 0) {
            $msg = '单据未确认';
        } else if ($record['is_stop'] > 0) {
            $msg = '单据已终止';
        } else if ($record['is_finish'] > 0) {
            $msg = '单据已完成';
        }
        if ($msg != '') {
            return $this->format_ret(-1, '', $record_code . $msg);
        }
        return $this->format_ret(1, $record);
    }

    /**
     * 获取批次数据
     * @param string $record_code 通知单号
     * @param string $record_type 单据类型
     * @param string $new_record_code 生成新单据的单据号
     * @param string $store_code 仓库代码
     * @return array 数据集
     */
    protected function get_lof_data($record_code, $record_type, $new_record_code, $store_code) {
        $order_type_arr = array('pur_return_notice' => 'pur_return', 'wbm_notice' => 'wbm_store_out', 'shift_in' => 'shift_in');
        if (!isset($order_type_arr[$record_type])) {
            return array();
        }
        $order_type = $order_type_arr[$record_type];
        $sql = "SELECT gs.goods_code, gs.spec1_code, gs.spec2_code, gs.sku, bl.lof_no, bl.production_date, 
                    ld.init_num, bl.wms_sl AS num, 0 AS occupy_type ,
                    '{$store_code}' AS store_code, '{$new_record_code}' AS order_code ,'{$order_type}' AS order_type  
                    FROM wms_b2b_order_lof bl INNER JOIN b2b_lof_datail AS ld ON bl.record_code=ld.order_code AND  bl.lof_no=ld.lof_no
                    INNER JOIN goods_sku gs ON bl.barcode=gs.barcode AND gs.sku=ld.sku
                    WHERE bl.record_code=:record_code AND bl.record_type=:record_type AND bl.wms_sl>0";
        $sql_values = array(':record_code' => $record_code, ':record_type' => $record_type);

        $lof_data = $this->db->get_all($sql, $sql_values);
        return $lof_data;
    }

    /**
     * 获取通知单明细
     * @param string $record_code 单据编号
     * @param string $record_type 单据类型
     * @return array 数据集
     */
    protected function get_notcie_detail($record_code, $record_type) {
        $select = 'sku,price,rebate,refer_price,num';
        $table = '';
        switch ($record_type) {
            case 'pur_return_notice':
                $table = 'pur_return_notice_record_detail';
                break;
            case 'wbm_notice':
                $table = 'wbm_notice_record_detail';
                break;
        }
        $sql = "SELECT {$select} FROM {$table} WHERE record_code = :record_code";
        $detail = ctx()->db->get_all($sql, array(':record_code' => $record_code));
        $detail = load_model('util/ViewUtilModel')->get_map_arr($detail, 'sku');
        return $detail;
    }

    /**
     * 添加批次数据
     * @param string $record_code 单据编号
     * @param string $record_type 单据类型
     * @param string $store_code 仓库代码
     * @param array $goods 商品明细
     * @return array 数据集
     */
    protected function incr_lof_info($record_code, $record_type, $store_code, $goods) {
        $type = in_array($record_type, array('pur_return_notice')) ? 'pur_return_notice' : '';
        $this->get_wms_cfg($store_code, $type);

        $desc_fld = 'price,rebate,money,sku';
        //开启批次，则增加批次数据
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        if (in_array($this->api_product, array('qimen')) && $is_lof == 1) {
            $goods = load_model('util/ViewUtilModel')->get_map_arr($goods, 'sku');
            $lof_data = load_model('stm/GoodsInvLofRecordModel')->get_by_order_code($record_code, $record_type);
            foreach ($lof_data as $val) {
                $sku = $val['sku'];
                if (isset($goods[$sku])) {
                    $goods[$sku]['batchs'][] = get_array_vars($val, array('lof_no', 'production_date', 'num'));
                }
            }
            $desc_fld .= ',batchs';
        }

        $ret = $this->append_mx_barcode_by_sku($goods, 1, $desc_fld);

        return $ret;
    }

    /**
     * 校验配送方式在系统中是否存在
     * @param type $express_code
     * @return type
     */
    protected function check_express_exists($express_code) {
        $express_code = strtoupper($express_code);
        $sql = "SELECT COUNT(1) FROM base_express WHERE express_code = :express_code";
        $c = ctx()->db->getOne($sql, array(':express_code' => $express_code));
        if ($c == 0) {
            return $this->format_ret(-1, '', $express_code . '配送方式代码在EFAST中不存在');
        }
        return $this->format_ret(1, $express_code);
    }

    function get_item_id($api_product, $store_code, $barcode) {
        $sql = "select api_code from wms_archive where type=:type AND api_product=:api_product  AND sys_code=:sys_code ";
        $sql_value = array(
            ':type' => 'goods_barcode',
            ':api_product' => $api_product,
            ':sys_code' => $barcode,
        );


        if (!empty($this->wms_cfg) && !empty($this->wms_cfg['wms_config_id'])) {
            $sql .= " and wms_config_id=:wms_config_id";
            $sql_value[':wms_config_id'] = $this->wms_cfg['wms_config_id'];
        } else {
            $store_arr = $this->get_wms_store_by_store_code($store_code);
            if (!empty($store_arr)) {
                $store_str = "'" . implode("','", $store_arr) . "'";
                $sql .= " AND efast_store_code in ({$store_str}) ";
            }
        }

        return $this->db->get_value($sql, $sql_value);
    }

    /**
     * 获取sys_code(barcode)
     */
    function get_sys_code_old($store_code, $api_code) {
        $sql = 'SELECT sys_code FROM wms_archive WHERE efast_store_code=:efast_store_code AND api_code=:api_code';
        $sys_code = $this->db->get_value($sql, array(':efast_store_code' => $store_code, ':api_code' => $api_code));
        return $sys_code;
    }

    /**
     * 获取sys_code(barcode)
     */
    function get_sys_code($store_code, $api_code) {
//        if(CTX()->saas->get_saas_key()!='2061'){
//            return $this->get_sys_code_old($store_code, $api_code);
//        }
        $store_arr = $this->get_wms_store_by_store_code($store_code);
        $sys_code = '';
        if (!empty($store_arr)) {
            $store_str = "'" . implode("','", $store_arr) . "'";
            $sql = "SELECT sys_code FROM wms_archive WHERE efast_store_code in ({$store_str}) AND api_code=:api_code";
            $sys_code = $this->db->get_value($sql, array(':api_code' => $api_code));
        }

        return $sys_code;
    }

    function html_decode($str) {
        $str = htmlspecialchars_decode($str);
        $replace = array(
            '&ndash;' => '–',
            '&mdash;' => '—',
            '&amp;' => '',
            '#' => '',
            '&ldquo' => '',
            '&rdquo' => '',
            '&lsquo' => '',
            '&rsquo' => ''
        );
        foreach ($replace as $key => $val) {
            $str = str_replace($key, $val, $str);
        }
        $str = str_replace(array('&'), '', $str);
        return $str;
    }

    /**
     * 开启多包裹，将多包裹数据插入包裹单表
     * Table:oms_deliver_record_package
     */
    function more_package_add($record_code, $express_data) {
        if (isset($express_data['logisticsCode'])) {
            $express_data = array($express_data);
        }

        //查询订单号是否为新单号,如果回传单号为新单号，则取原单号
        $sql = 'SELECT record_code FROM wms_oms_trade WHERE new_record_code=:code';
        $code = $this->db->get_value($sql, array(':code' => $record_code));
        if ($code != FALSE) {
            $record_code = $code;
        }
        $package_data = array();
        foreach ($express_data as $key => $val) {
            $package['sell_record_code'] = $record_code;
            $package['express_code'] = $val['logisticsCode'];
            $package['express_no'] = $val['expressCode'];
            $package['real_weigh'] = $val['weight'];
            $package['is_weigh'] = 1;
            $package['package_no'] = $key + 1;
            $package_data[] = $package;
        }
        $update_str = " express_no= VALUES(express_no),express_code= VALUES(express_code) ";
        $ret = $this->insert_multi_duplicate('oms_deliver_record_package', $package_data, $update_str);
        if ($ret['status'] != 1) {
            $ret['message'] = '添加多包裹数据出现错误';
        }
        return $ret;
    }

    /**
     * 获取单据信息
     * @param string $record_code 单据编号
     * @param string $record_type 单据类型
     * @return array
     */
    function get_upload_record_data($record_code, $record_type) {
        $oms_type = ['sell_record', 'sell_return'];
        $type = in_array($record_type, $oms_type) ? 'oms' : 'b2b';
        $sql = "SELECT json_data,new_record_code FROM wms_{$type}_trade WHERE record_code=:record_code AND record_type=:record_type";
        return $this->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
    }

    /**
     * 生成新单号
     * @param string $record_code 单据编号
     * @param string $record_type 单据类型
     * @param string $type oms b2b
     * @param string $pre_record_code 上一次更新的新单号
     * @return string 本次生成的新单号
     */
    function create_new_record_code($record_code, $record_type, $type, $pre_record_code = '') {
        $_code = $pre_record_code !== '' ? $record_code : $pre_record_code;
        $new_record_code = "N" . rand(100, 999) . $_code;
        $sql_values = [':record_code' => $record_code, ':record_type' => $record_type, ':new_code' => $new_record_code];
        $sql = "UPDATE wms_{$type}_trade SET new_record_code=:new_code WHERE record_code=:record_code AND record_type=:record_type ";
        if ($new_record_code === '') {
            $sql .= "AND new_record_code=''";
        } else {
            $sql .= "AND (new_record_code='' OR new_record_code=:pre_code)";
            $sql_values[':pre_code'] = $pre_record_code;
        }

        $this->db->query($sql);

        return $new_record_code;
    }

}
