<?php

/**
 * 配发货
 * 2014/12/18
 * @author jia.ceng
 */
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms/DeliverRecordModel');

class deliver_record {

    function get_detail_by_pid(array &$request, array &$response, array &$app) {
        $deliver_record_id = $request['deliver_record_id'];
        $result = load_model("oms/DeliverModel")->get_row(array("deliver_record_id" => $deliver_record_id));
        $response['data'] = $result['data'];
        $response['detail'] = load_model("oms/DeliverModel")->get_detail_by_pid($deliver_record_id);
        $app['page'] = 'NULL';
        $app['tpl'] = "oms/deliver_record_detail";
    }

    function edit_express_no(array &$request, array &$response, array &$app) {
        $m = new DeliverRecordModel();
        $response['deliver_record_list'] = $m->get_record_list_by_ids(explode(',', $request['deliver_record_id_list']));
        $response['express_arr'] = array();
        foreach ($response['deliver_record_list'] as $record) {
            $response['express_arr'][$record['express_code']] = $record['express_code'];
        }

        // 移除热敏快递方式
        foreach ($response['express_arr'] as $expressCode => $express_code) {
            $e = load_model('base/ShippingModel')->get_row(array('express_code' => $expressCode));
            if ($e['data']['print_type'] > 0) {
                unset($response['express_arr'][$expressCode]);
            }
        }
    }

    function edit_express_no_all(array &$request, array &$response, array &$app) {
        $m = new DeliverRecordModel();
        $response['deliver_record_list'] = $m->get_record_list_by_items($request);
        $response['express_arr'] = array();
        foreach ($response['deliver_record_list'] as $record) {
            $response['express_arr'][$record['express_code']] = $record['express_code'];
        }

        // 移除热敏快递方式
        foreach ($response['express_arr'] as $expressCode => $express_code) {
            $e = load_model('base/ShippingModel')->get_row(array('express_code' => $expressCode));
            if ($e['data']['print_type'] > 0) {
                unset($response['express_arr'][$expressCode]);
            }
        }
        $app['tpl'] = "oms/deliver_record_edit_express_no";
    }

    function edit_express_no_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $check_the_no = empty($request['check_the_no']) ? false : true;
        $m = new DeliverRecordModel();
        $err = '';
        foreach ($request['express_no'] as $id2 => $no2) {
            $record = load_model('oms/DeliverRecordModel')->get_record_by_id($id2);
            if ($record['express_code'] <> $request['express_code']) {
                unset($request['express_no'][$id2]);
            }
            if (trim($no2) == '') {
                unset($request['express_no'][$id2]);
            }
        }

        foreach ($request['express_no'] as $id => $no) {
            $s = $m->edit_express_no($id, $no, $check_the_no);
            if ($s['status'] != 1) {
                $err .= " " . $s['message'];
            }
        }
        if (empty($err)) {
            $response = array('status' => 1, 'message' => '更新成功', 'data' => array());
        } else {
            $response = array('status' => -1, 'message' => $err, 'data' => array());
        }
    }

    function do_cancel(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new DeliverRecordModel();
        $ret = $m->cancel($request['deliver_record_id'], $request['remark']);
        $response = $ret;
    }

    function edit_express(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $request['express_no'] = str_replace('-1-1-', '', trim($request['express_no']));
        $mdl = new DeliverRecordModel();
        $response = $mdl->edit_express($request['sell_record_code'], array('express_code' => $request['express_code'], 'express_no' => $request['express_no']));
    }

    function do_outofstock(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new DeliverRecordModel();
        $response = $m->outofstock($request['deliver_record_id']);
    }

    function edit_receiver(array &$request, array &$response, array &$app) {
        $m = new DeliverRecordModel();
        $ret = $m->get_row(array('deliver_record_id' => $request['deliver_record_id']));
        $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($ret['data']['sell_record_code']);
        $response['record'] = array_merge($ret['data'], $record_decrypt_info);
        $log_data['customer_address_id'] = $response['record']['customer_address_id'];
        $log_data['customer_code'] = $response['record']['customer_code'];
        $log_data['record_code'] = '无';
        $log_data['record_type'] = '无';
        $log_data['action_note'] = '波次单发货编辑地址';
        load_model('sys/security/CustomersSecurityLogModel')->add_log($log_data);
    }

    function edit_receiver_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new DeliverRecordModel();
        $p = $request;
        unset($p['deliver_record_id']);
        $response = $m->edit_receiver($request['deliver_record_id'], $p);
    }

    function edit_express_code(array &$request, array &$response, array &$app) {
        
    }

    function edit_express_code_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $msg = '';
        $mdl = new DeliverRecordModel();
        if ($request['type'] == 1) {
            $ret_list = $mdl->get_deliver_id_list_by_waves_code($request['record_code']);
            $request['deliver_record_id_list'] = $ret_list['data'];
        }
        foreach ($request['deliver_record_id_list'] as $id) {
            $ret = $mdl->edit_express_code($id, $request['express_code']);
            $msg .= $id . ': ' . ($ret['status'] == 1 ? '成功' : $ret['message']) . "\n";
            $response = $mdl->get_row(array('deliver_record_id' => $id));
        }

        load_model('oms/WavesRecordModel')->update_express($request['express_code'], $request['record_code']);
        $response = array('status' => 1, 'message' => $msg);
    }

    // 发货
    function check(array &$request, array &$response, array &$app) {
        $mdl = new DeliverRecordModel();
        $response['sound'] = $mdl->get_sound();
        $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status', 'deliver_record_direct_ship'));
        $response['unique_status'] = $unique_arr['unique_status'];
        $response['deliver_record_direct_ship'] = $unique_arr['deliver_record_direct_ship'];
        $response['selectable'] = ($response['deliver_record_direct_ship'] == 1) ? true : false;
        $arr = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $response['lof_status'] = $arr['lof_status'];
    }

    // 发货通灵
    function check_tl(array &$request, array &$response, array &$app) {
        $mdl = new DeliverRecordModel();
        $response['sound'] = $mdl->get_sound();
        $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status', 'deliver_record_direct_ship'));
        $response['unique_status'] = $unique_arr['unique_status'];
        $response['deliver_record_direct_ship'] = $unique_arr['deliver_record_direct_ship'];
        $response['selectable'] = ($response['deliver_record_direct_ship'] == 1) ? true : false;
        $arr = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $response['lof_status'] = $arr['lof_status'];
    }

    // 发货
    function check_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new DeliverRecordModel();
        if ($request['is_record'] == 1) {
            $response = $m->check($request['deliver_record_id'], 0);
        } else {
            $request['is_tl'] = isset($request['is_tl']) ? $request['is_tl'] : 0;
            $response = $m->check($request['deliver_record_id'], 1, $request['is_tl']);
        }
        /** 记录用户操作 */
        if (isset($request['is_record']) && $request['is_record']) {
            load_model('oms/SellRecordModel')
                    ->add_action($request['sell_record_code'], '直接发货');
        }
    }

    // 发货
    function scan_clear(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        if (isset($request['deliver_record_id']) && $request['deliver_record_id'] != '') {
            $m = new DeliverRecordModel();
            $response = $m->scan_clear($request['deliver_record_id']);
            /** 记录用户操作 */
            if (isset($request['is_record']) && $request['is_record']) {
                load_model('oms/SellRecordModel') -> add_action($request['sell_record_code'], '清除扫描记录');
            }
        } else {
            $response['status'] = -1;
        }
    }

    // 扫描物流单号
    function check_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $request['express_no'] = str_replace('-1-1-', '', trim($request['express_no']));
        $mdl = new DeliverRecordModel();
        $data = $mdl->get_record_by_express_no($request['express_no'], $request['no_is_duplicate']);
        //var_dump($data);die;
        if ($data['status'] == '1') {
            //验证退单
            $ret = $mdl->check_refund($data);
            if ($ret['status'] < 0) {
                $response = $ret;
                return;
            }
            // 扫描日志
            require_model('oms/SellRecordOptModel');
            $mdlSell = new SellRecordOptModel();
            $mdlSell->add_action($data['record']['sell_record_code'], '扫描出库', '扫描物流单号:' . $request['express_no']);

            ob_start();
            include get_tpl_path('oms/deliver_record_check_detail');
            $response['data'] = ob_get_contents();
            ob_end_clean();
            $response['status'] = '1';
        } else {
            $response = $data;
        }
    }

    // 扫描物流单号通灵
    function check_detail_tl(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $request['express_no'] = str_replace('-1-1-', '', trim($request['express_no']));
        $mdl = new DeliverRecordModel();
        $data = $mdl->get_record_by_express_no($request['express_no'], $request['no_is_duplicate'], $request['tl']);
        //var_dump($data);die;
        if ($data['status'] == '1') {
            //验证退单
            $ret = $mdl->check_refund($data);
            if ($ret['status'] < 0) {
                $response = $ret;
                return;
            }
            // 扫描日志
            require_model('oms/SellRecordOptModel');
            $mdlSell = new SellRecordOptModel();
            $mdlSell->add_action($data['record']['sell_record_code'], '扫描出库', '扫描物流单号:' . $request['express_no']);

            $response['unique_list'] = $mdl->get_unique_code_scan_temporary_log($data['record']['sell_record_code']);

            ob_start();
            include get_tpl_path('oms/deliver_record_check_detail_tl');
            $response['data'] = ob_get_contents();
            ob_end_clean();
            $response['status'] = '1';
        } else {
            $response = $data;
        }
    }

    function auto_shipping_fee(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = array('status' => 1, 'data' => '', 'message' => '');
    }

    function weigh(array &$request, array &$response, array &$app) {
        
    }

    function weigh_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new DeliverRecordModel();
        $response = $m->weigh($request['deliver_record_id'], $request['real_weight'], $request['express_money']);
    }

    function weigh_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new DeliverRecordModel();
        $response = $mdl->get_record_for_weigh($request['express_no']);
    }

    function scan_detail(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $request['is_unique'] = isset($request['is_unique']) ? $request['is_unique'] : 0;
        $m = new DeliverRecordModel();
        $response = $m->scan_detail($request['deliver_record_id'], $request['deliver_record_detail_id'], $request['barcode'], $request['is_unique']);
    }

    function jd_etms_waybillcode_get(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $request['record_ids'] = trim($request['record_ids'], ',');
        $idList = explode(',', $request['record_ids']);

        $m = new DeliverRecordModel();
        $response = $m->jd_etms_waybillcode_get($request['waves_record_id'], $idList, $request['guarantee_money']);
    }

    /**
     * 获取物流单号
     */
    function get_logistics(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $param = get_array_vars($request, ['waves_record_id', 'record_ids','express_pay_method','express_type']);
        $response = load_model('oms/DeliverLogisticsModel')->get_logistics($request['print_type'], $request['is_all'], $param);
    }

    function tb_wlb_waybill_get(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $idList = array();
        if ($request['type'] == 0) {
            $request['record_ids'] = trim($request['record_ids'], ',');
            $idList = explode(',', $request['record_ids']);
        }
        $m = new DeliverRecordModel();
        if ($request['type'] == 0) {
            if ($request['print_type'] == '2') {
                $response = $m->cn_wlb_waybill_get($request['waves_record_id'], $idList);
            } else {
                $response = $m->tb_wlb_waybill_get($request['waves_record_id'], $idList);
            }
        } else {
            if ($request['print_type'] == '2') {
                $response = $m->tb_wlb_waybill_get_all($request['waves_record_id'], $request['print_type']);
            } else {
                $response = $m->tb_wlb_waybill_get_all($request['waves_record_id']);
            }
        }
    }

    /**
     * 列表批量获取云栈热敏接口
     * @param array $request   print_type=2:代表云栈四期
     * @param array $response
     * @param array $app
     */
    function tb_wlb_waybill_get_multi(array & $request, array & $response, array & $app) {
        $ret = load_model('oms/DeliverRecordModel')->tb_wlb_waybill_get_multi($request['waves_record_ids'], $request['print_type']);
        exit_json_response($ret);
    }

    /**
     * @todo 检测获取云栈热敏的订单的状态
     */
    function check_status(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $request['record_ids'] = trim($request['record_ids'], ',');
        $idList = explode(',', $request['record_ids']);
        $m = new DeliverRecordModel();
        $ids_str = "'" . implode("','", $idList) . "'";
        $check = $m->check_status($ids_str);
        if ($check['status'] == -1) {
            $response = $check;
        } else {
            $response = array('status' => '1', 'data' => '', 'message' => '');
        }
    }

    /**
     * @todo 检测一键获取云栈热敏的订单的状态
     */
    function check_all_status(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $m = new DeliverRecordModel();
        $idList = $m->get_deliver_id_list_by_waves_id($request['waves_record_id']);
        $ids_str = "'" . implode("','", $idList) . "'";
        $check = $m->check_status($ids_str);
        if ($check['status'] == -1) {
            $response = $check;
        } else {
            $response = array('status' => '1', 'data' => '', 'message' => '');
        }
    }

    /**
     * 批量验证是否获取过云栈
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function check_all_status_multi(array & $request, array & $response, array & $app) {
        $ret = load_model('oms/DeliverRecordModel')->check_all_status_multi($request['waves_record_ids']);
        exit_json_response($ret);
    }

    function check_express_type(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        //var_dump($request['record_ids']);die;
        $response = load_model('oms/DeliverRecordModel')->check_express_type($request['record_ids'], $request['id_type']);
    }

    function print_range(array & $request, array & $response, array & $app) {
        $response['type'] = $request['print_type'] == 'express' ? '快递单' : '发货单';
        $arr = array('print_delivery_record_template', 'clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['print_delivery_record_template'] = isset($ret_arr['print_delivery_record_template']) ? $ret_arr['print_delivery_record_template'] : 0;
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
    }

    function print_range_action(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $m = new DeliverRecordModel();
        $response = $m->get_records_by_range($request['waves_record_id'], $request['min'], $request['max']);
    }

    //校验是否存在重复打印快递单
    function check_is_print_express(array & $request, array & $response, array & $app) {
        $response = load_model("oms/DeliverRecordModel")->check_is_print_express($request);
    }
    
    //校验是否存在重复打印发货单
    function check_is_print_sellrecord(array & $request, array & $response, array & $app) {
        $response = load_model("oms/DeliverRecordModel")->check_is_print_sellrecord($request);
    }

    function print_express(array & $request, array & $response, array & $app) {
        $arr = array('wave_print');
        $request['page'] = isset($request['page']) ? $request['page'] : 1;
        $response['print_express_name'] = $request['print_express_name']; //获取现需要打印的快递名称
        //是否选择打印机的参数
        $response['unable_printer'] = isset($request['unable_printer']) ? $request['unable_printer'] : 0;
        if (isset($request['wave_print'])) {
            $response['wave_print'] = $request['wave_print'];
        } else {
            $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
            $response['wave_print'] = isset($ret_arr['wave_print']) ? $ret_arr['wave_print'] : '';
        }
        $ret = load_model("oms/DeliverRecordModel")->check_record_express($request);
        $express_code = $ret['data'];
        if ($ret['status'] > 0) {
            $pay_type = load_model('oms/DeliverRecordModel')->get_templates_type($request['waves_record_ids']);
            $template_type_key = (isset($pay_type) && $pay_type == 'cod') ? 'df' : 'pt';
            $ret_tpl = load_model("base/ShippingModel")->get_shipping_tpl($ret['data']);
            if ($ret_tpl['status'] > 0) {
                $response['status'] = '1';
                $response['data'] = $ret['data'];
                $response['tpl'] = $ret_tpl['data'];
                $response['tpl']['printer'] = $ret_tpl['data'][$template_type_key]['printer'];
            } else {
                $response['status'] = '-1';
                $response['message'] = $ret_tpl['message'];
            }
        } else {
            $response['status'] = '-1';
            $response['message'] = $ret['message'];
        }
        if ($response['tpl']['rm']['is_buildin'] == 2) {
            $app['tpl'] = 'oms/deliver_record_print_express_cainiao';
            $response['tpl']['printer'] = $response['tpl']['rm']['printer'];
        }
        if ($request['print_type'] == 'cainiao_print' || $response['tpl']['rm']['is_buildin'] == 3) {
            $app['tpl'] = 'oms/deliver_record_cloud_print_express';
        }

        if (!isset($request['printer']) || empty($request['printer'])) {
            $request['printer'] = isset($response['tpl']['printer']) ? $response['tpl']['printer'] : '';
        }
        $response['print_one'] = 0;

        if (strtolower($express_code) == 'jd') {
            $response['print_one'] = 1;
        }
    }

    function get_print_express_data(array & $request, array & $response, array & $app) {

        $response = load_model("oms/DeliverRecordModel")->get_record_print_page($request);
    }

    // 打印发货单
    function print_deliver(array & $request, array & $response, array & $app) {
        $ret = load_model("sys/SendTemplatesModel")->get_templates_all();
        if ($ret['status'] > 0) {
            $response['tpl'] = $ret['data'];
        } else {
            $response['message'] = $ret['message'];
        }
    }

    // 读取打印发货单的模板所需数据
    function get_print_deliver_record_data(array & $request, array & $response, array & $app) {
        $response = load_model("oms/DeliverRecordModel")->get_print_deliver_record_data($request);
    }

    // 读取打印发货单的模板所需数据
    function get_sku_by_sub_barcode(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $request['sub_barcode'] = trim($request['sub_barcode']);
        $response = load_model("oms/DeliverRecordModel")->get_sku_by_sub_barcode($request['deliver_record_id'], $request['sub_barcode'], $request['tl']);
    }

    //通灵发货单
    function get_sku_by_barcode(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $request['sub_barcode'] = trim($request['sub_barcode']);
        $response = load_model("oms/DeliverRecordModel")->get_sku_by_barcode($request['deliver_record_id'], $request['sub_barcode']);
    }

    // 标记打印状态: 发货单
    function mark_print(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new DeliverRecordModel();
        // $response = $mdl->update(array('is_print_goods'=>'1'), array('wave_record_id'=>$request['wave_record_id']));
        // $request['print_type'] 可选值包括: deliver, express

        $wavesRecordIds = isset($request['wave_record_ids']) ? $request['wave_record_ids'] : 0;
        $deliverRecordIds = isset($request['deliver_record_ids']) ? $request['deliver_record_ids'] : 0;
        $min = isset($request['min']) ? $request['min'] : 0;
        $max = isset($request['max']) ? $request['max'] : 0;

        $response = $mdl->mark_print($wavesRecordIds, $deliverRecordIds, $min, $max);
    }

    function get_oms_selll_recode_lof_info(array & $request, array & $response, array & $app) {
        $response = load_model("oms/DeliverRecordModel")->get_scan_lof_data($request);
    }

    function set_select_barcode_lof(array & $request, array & $response, array & $app) {
        $response = load_model("oms/DeliverRecordModel")->set_select_barcode_lof($request);
    }

    function get_package_express_no(array & $request, array & $response, array & $app) {

        $response = load_model("oms/DeliverRecordModel")->get_package_express_no($request['sell_record_code'], $request['package_no']);
    }

    function search_package(array & $request, array & $response, array & $app) {
        
    }

    function insert_unique_barcode(array & $request, array & $response, array & $app) {
        $data = array();
        $data['sell_record_code'] = $request['record_code'];
        $data['unique_code'] = $request['unique_code'];
        $data['barcode_type'] = 'unique_code';
        $response = load_model('oms/UniqueCodeScanTemporaryLogModel')->insert($data);
    }

    function import_express_no(array & $request, array & $response, array & $app) {
        
    }

    function do_record_import(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $sku_arr = array();
        $read_data = array();

        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        load_model('oms/DeliverRecordModel')->read_csv_recode_sku($file, $sku_arr, $read_data);
        $ret = load_model('oms/DeliverRecordModel')->imoprt_detail($request['id'], $read_data, $request['check_express_no']);
        $response = $ret;
        //   $response['url'] = $_FILES['fileData']['name'];
    }

    //导入商品
    function import_express(array & $request, array & $response, array & $app) {
        set_uplaod($request, $response, $app);
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            exit_json_response($ret);
        }
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
    }

    //修改扫描数量
    function update_goods_scan_num(array & $request, array & $response, array & $app) {
        $ret = load_model('oms/DeliverRecordModel')->update_goods_scan_num($request);
        exit_json_response($ret);
    }

    /**
     * @todo 获取标准打印区域的数据
     */
    public function get_cloud_print_express_data(array & $request, array & $response, array & $app) {
        $ret = load_model('oms/DeliverRecordModel')->get_cloud_print_express_data($request['deliver_record_id']);
        exit_json_response($ret);
    }

    /**
     * @todo 更新成功打印的发货订单的快递单打印状态
     */
    public function update_cloud_print_express_status(array & $request, array & $response, array & $app) {
        $result = html_entity_decode($request['result']);
        $ret = load_model('oms/DeliverRecordModel')->update_cloud_print_express_status(json_decode($result, true), $request['fail_num']);
        exit_json_response($ret);
    }

    /**
     * @todo 检查默认打印机
     */
    public function check_printer(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/DeliverRecordModel')->check_printer($request['deliver_record_id']);
    }

    /**
     * @todo 选择打印机页面
     */
    public function choose_printer(array & $request, array & $response, array & $app) {
        $response['printer_list'] = explode(',', $request['printer_list']);
        $response['default_printer'] = $request['default_printer'];
        $response['print_express_name'] = $request['print_express_name'];
    }

    /**
     * @todo 更新默认打印机
     */
    public function choose_printer_action(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('sys/PrintTemplatesModel')->update_printer($request['printer'], $request['print_templates_id']);
    }

    function check_record_waves(array &$request, array &$response, array &$app) {
        $mdl = new DeliverRecordModel();
        $ret = $mdl->get_record_by_record_waves($request['record_waves_code']);
        exit_json_response($ret);
    }

    function check_barcode(array &$request, array &$response, array &$app) {
        $mdl = new DeliverRecordModel();
        $ret = $mdl->get_check_barcode_data($request);
        exit_json_response($ret);
    }

    function edit_picking_num(array &$request, array &$response, array &$app) {
        $mdl = new DeliverRecordModel();
        $ret = $mdl->edit_picking_num($request);
        exit_json_response($ret);
    }

    function choose_clodop_printer(array &$request, array &$response, array &$app) {
        
    }

}
