<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms/WavesRecordModel');
require_model('oms/DeliverRecordModel');

class waves_record {

    function do_list(array &$request, array &$response, array &$app) {
        //拣货单扫描验收
        $arr = array('wave_check', 'print_delivery_record_template', 'clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['wave_check'] = isset($ret_arr['wave_check']) ? $ret_arr['wave_check'] : '0';
        $response['print_delivery_record_template'] = isset($ret_arr['print_delivery_record_template']) ? $ret_arr['print_delivery_record_template'] : 0;
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
        $response['picker'] = '';
        $response['do_list_tab'] = 'tabs_all';
        $response['tabs_all'] = true;
        $response['tabs_sended'] = false;
        if (isset($request['staff_id'])) {
            $staff = load_model('base/StoreStaffModel')->get_row(array('staff_id' => $request['staff_id']));
            $response['picker'] = $staff['data'];
            $response['do_list_tab'] = 'tabs_sended';
            $response['tabs_all'] = false;
            $response['tabs_sended'] = true;
        }
    }

    function view(array &$request, array &$response, array &$app) {
        $m = new WavesRecordModel();
        $response = $m->get_row(array('waves_record_id' => $request['waves_record_id']));
        //波次单订单是否全部验收或取消
//        load_model('oms/DeliverRecordModel')->deliver_list($request['waves_record_id']);
        $response['deliver_ids'] = load_model('oms/DeliverRecordModel')->get_deliver_detail_id($request['waves_record_id']);
        $order_data_count = load_model('oms/DeliverRecordModel')->get_order_num($request['waves_record_id']);
        $response['data']['sell_record_count_all'] = $order_data_count['cnt'];
        //拣货单扫描验收
        $arr = array('wave_check', 'is_more_deliver_package', 'clodop_print', 'is_valuation', 'print_delivery_record_template');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['wave_check'] = isset($ret_arr['wave_check']) ? $ret_arr['wave_check'] : '0';
        $response['is_more_deliver_package'] = isset($ret_arr['is_more_deliver_package']) ? $ret_arr['is_more_deliver_package'] : '0';
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
        $response['wave_match_express'] = oms_tb_val('sys_params', 'value', array('param_code' => 'wave_match_express'));
        $response['print_delivery_record_template'] = isset($ret_arr['print_delivery_record_template']) ? $ret_arr['print_delivery_record_template'] : 0;

        //是否开启保价
        $response['is_valuation'] = isset($ret_arr['is_valuation']) ? $ret_arr['is_valuation'] : 0;

        //是否含楚楚街订单
//        $response['data']['is_chuchujie'] = load_model('oms/DeliverRecordModel')->is_channel_chuchu($request['waves_record_id']);
        //print_r($response);
        //打印发票增值
        $auth_print_invoice = load_model('common/ServiceModel')->check_is_auth_by_value('print_invoice');
        $response['data']['auth_print_invoice'] = $auth_print_invoice;

        $response['package_data'] = load_model('oms/DeliverRecordModel')->package_data;
        $ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
        $response['is_more_deliver_package'] = isset($ret_pararm['is_more_deliver_package']) ? $ret_pararm['is_more_deliver_package'] : '0';
        $exist_ret = load_model('oms/DeliverRecordModel')->exist_empty_express($request['waves_record_id']);
        $response['no_exist_express_no'] = $exist_ret['no_exist_express_no'];
        $response['no_print_express'] = $exist_ret['no_print_express'];
        //判断是否开启导出子条码参数
        $response['goods_sub_barcode'] = oms_tb_val('sys_params', 'value', array('param_code' => 'goods_sub_barcode'));
        //拣货员
        $response['data']['pick_member_name'] = (empty($response['data']['picker'])) ? '' : oms_tb_val('base_store_staff', 'staff_name', array('staff_code' => $response['data']['picker']));
        $response['is_allow_do_cancel'] = load_model('sys/PrivilegeModel')->check_priv('oms/waves_record/do_cancel') == TRUE ? 1 : 0;
        $response['is_allow_do_cancel_waves'] = load_model('sys/PrivilegeModel')->check_priv('oms/waves_record/do_cancel_waves') == TRUE ? 1 : 0;
        $response['data']['record_time'] = date('Y-m-d', strtotime($response['data']['record_time']));
    }

    function create_waves(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $m = new WavesRecordModel();
        $response = $m->create_waves($request['sell_record_id_list'], $request['is_check']);
    }

    //(批量)取消波次单
    function opt_cancel_waves(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ret = load_model('oms/WavesRecordModel')->opt_cancel_waves($request);
        exit_json_response($ret);
    }

    function accept(array &$request, array &$response, array &$app) {
        $mw = new WavesRecordModel();
        $record = $mw->get_row(array('waves_record_id' => $request['waves_record_id']));
        $response['waves'] = $record['data'];

        $md = new DeliverRecordModel();
        $deliver_list = $md->get_detail_list_by_waves_id($request['waves_record_id']);
        // $response['deliver_list'] = $deliver_list;

        $deliver_list_new = array();
        foreach ($deliver_list as $key => $value) {
            $num = 0;
            if (isset($deliver_list_new[$value['barcode']])) {
                $num = $deliver_list_new[$value['barcode']]['num'];
                $value['num'] = $num + $value['num'];
                $deliver_list_new[$value['barcode']] = $value;
            } else {
                $deliver_list_new[$value['barcode']] = $value;
            }
        }
        $response['deliver_list'] = $deliver_list_new;

        $response['sound'] = $md->get_sound();
    }

    function accept_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $mw = new WavesRecordModel();
        // $request['is_scan'] = isset ($request['is_scan'])?$request['is_scan']:0;
        $opt_user = CTX()->get_session('user_name');
        $response = $mw->accept($request['waves_record_id'], $opt_user);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $mw = new WavesRecordModel();
        $response = $mw->do_delete($request['waves_record_id']);
    }

    function edit_express_no(array &$request, array &$response, array &$app) {
        $m = new WavesRecordModel();
        $response['deliver_record_list'] = $m->get_deliver_record_list_by_ids(explode(',', $request['waves_record_id_list']));
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

    function edit_express_no_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $check_the_no = empty($request['check_the_no']) ? false : true;
        $m = new DeliverRecordModel();
        $err = '';
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

    function edit_express_code(array &$request, array &$response, array &$app) {

    }

    function get_print_express_data(array &$request, array &$response, array &$app) {
        $request['order_ids'];
        $mdl = new WavesRecordModel();
        $response = $mdl->edit_express_code($request['waves_record_id_list'], $request['express_code']);
    }

    function print_express_action(array &$request, array &$response, array &$app) {
        $request['order_ids'];
        $mdl = new WavesRecordModel();
        $response = $mdl->edit_express_code($request['waves_record_id_list'], $request['express_code']);
    }

    function edit_express_code_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new WavesRecordModel();
        $response = $mdl->edit_express_code($request['waves_record_id_list'], $request['express_code']);
    }

    function get_deliver_record_ids(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new WavesRecordModel();
        $response = $mdl->get_deliver_record_ids($request['record_ids']);
    }

    // 标记打印状态
    function mark_print(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new WavesRecordModel();
        //$response = $mdl->update(array('is_print_goods'=>'1'), array('wave_record_id'=>$request['wave_record_id']));
        $ids = trim($request['wave_record_ids'], ',');
        $r = $mdl->db->query("update oms_waves_record set is_print_goods = 1,is_print_waves=1 where waves_record_id in ({$ids})");

        // 全链路
        $sql = "select sale_channel_code, shop_code, deal_code FROM oms_deliver_record where waves_record_id in ($ids) and is_cancel = 0";
        $l = $mdl->db->get_all($sql);
        foreach ($l as $k => $v) {
            load_model('oms/SellRecordActionModel')->add_action_to_api($v['sale_channel_code'], $v['shop_code'], $v['deal_code'], 'print_wave');
        }

        $response = array('status' => '1', 'data' => $r, 'message' => '');
    }

    function do_cancel(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        set_time_limit(0);
        $m = new WavesRecordModel();
        $response = $m->cancel($request['waves_record_id'], 1, $request['remark']);
    }

    // 读取打印发货单的模板所需数据
    function get_sku_by_sub_barcode(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $m = new WavesRecordModel();
        $sub_barcode = trim($request['sub_barcode']);
        $response = $m->get_sku_by_sub_barcode($request['waves_record_id'], $sub_barcode);
    }

    //整单发货
    function get_waves_send_sell_record(array &$request, array &$response, array &$app) {
        $response = load_model('oms/WavesRecordModel')->get_waves_send_sell_record($request['waves_record_code']);
    }

    function waves_send_sell_record(array &$request, array &$response, array &$app) {
        $response = load_model('oms/WavesRecordModel')->waves_send_sell_record($request['sell_record_code']);
    }

    //批量发货
    function get_waves_batch_status(array &$request, array &$response, array &$app) {
        $response = load_model('oms/WavesRecordModel')->get_waves_send_sell_record('', $request['record_ids']);
    }

    function waves_batch_send_sell_record(array &$request, array &$response, array &$app) {
        $response = load_model('oms/WavesRecordModel')->waves_batch_send_sell_record($request['sell_record_code']);
    }

    function get_waves_back_sell_record(array &$request, array &$response, array &$app) {
        $response = load_model('oms/WavesRecordModel')->get_waves_back_sell_record($request['waves_record_code']);
    }

    function waves_back_sell_record(array &$request, array &$response, array &$app) {
        $response = load_model('oms/WavesRecordModel')->waves_back_sell_record($request['sell_record_code']);
    }

    //波次打印日志
    function do_print_log(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new WavesRecordModel();
        $response = $m->do_print_log($request['record_ids']);
    }

    //整单称重
    function weight_view(array &$request, array &$response, array &$app) {
        
    }

    //称重校验（用于聚划算活动，买的都是单款SKU，数量也一样，只需称一次就行，将重量回写到该波次下所有发货订单中）
    function wave_weight_check(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/SellRecordCzModel')->wave_weight_check($request['wave_record_id']);
        exit_json_response($ret);
    }

    //整单称重
    function do_wave_weight(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/SellRecordCzModel')->wave_weight($request['wave_record_id'], $request['weight']);
        exit_json_response($ret);
    }

    function edit_remark(array &$request, array &$response, array &$app) {
        
    }

    //二次拣货
    function two_order_picking(array &$request, array &$response, array &$app) {
        $md = new DeliverRecordModel();
        $response['sound'] = $md->get_sound();
    }

    //二次分拣查看商品
    function picking_view(array &$request, array &$response, array &$app) {
//        $md = new DeliverRecordModel();  
//        $response['sound'] = $md->picking_view($request);
    }

    /*     * 分配拣货员页面
     * @param array $request
     * @param array $response
     * @param array $app
     */

    function distribute_pick_member(array &$request, array &$response, array &$app) {

    }

    /*     * 更新拣货员
     * @param array $request
     * @param array $response
     * @param array $app
     */

    function update_pick_member(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/WavesRecordModel')->update_pick_member($request);
        exit_json_response($ret);
    }

    function is_guarantee(array &$request, array &$response, array &$app) {
        
    }
    public function check_is_print_record(array &$request, array &$response, array &$app){
        $response = load_model('oms/WavesRecordModel')->check_is_print_record($request['wave_record_ids']);
        $type = isset($request['is_print_error']) ? $request['is_print_error'] : 0;
        if($type > 0 && $response['status'] < 1){
            $message = load_model('oms/WavesRecordModel')->get_error_message($response['data']['error']);
            $response['message'] = $message;
        }
    }
    
    public function opt_unbind_express(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $params = get_array_vars($request, ['waves_record_id','sell_record_code']);
        $response = load_model('oms/DeliverLogisticsModel')->unbind_express($params);
    }

}
