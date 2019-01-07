<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class planned_record {

    function do_list(array & $request, array & $response, array & $app) {
        $response['user_id'] = CTX()->get_session('user_id');
        //二维表导入通过增值服务 + 参数 + 权限控制
        $service_auth = load_model('common/ServiceModel')->check_is_auth_by_value('import_planned_by_size_layer');
        if ($service_auth) {
            $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
            $priv = load_model('sys/PrivilegeModel')->check_priv('pur/planned_record/layer_import');
            $response['layer_import_priv'] = ($param_auth['size_layer'] == 1 && $priv) ? TRUE : FALSE;
        }

        //$response['layer_import_priv'] = true;//测试使用
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('pur/PlannedRecordModel')->get_by_id($request['_id']);
        }
        $response['pur_type'] = bui_get_select('record_type', 0, array('record_type_property' => 0));
        ;
        $response['pur_type'] = json_decode($response['pur_type'], 1);
        //供应商
        $response['supplier'] = load_model('base/SupplierModel')->get_select(2);
        //调整仓库
        $response['store'] = load_model('base/StoreModel')->get_select(2);
        $ret['data']['record_code'] = load_model('pur/PlannedRecordModel')->create_fast_bill_sn();
        $response['data'] = $ret['data'];
    }

    function do_add(array & $request, array & $response, array & $app) {
        $planned_record = get_array_vars($request, array('record_code', 'record_time', 'init_code', 'planned_time', 'in_time', 'store_code', 'pur_type_code', 'supplier_code', 'rebate', 'remark'));

        $detail_data = isset($request['detail_data']) ? $request['detail_data'] : '';


        if (empty($detail_data)) {

            $ret = load_model('pur/PlannedRecordModel')->insert($planned_record);
        } else {
            $detail_data = htmlspecialchars_decode($detail_data);
            $detail_data = htmlspecialchars_decode($detail_data);
            $data = json_decode($detail_data, true);
            $ret = load_model('pur/PlannedRecordModel')->insert_data($planned_record, $data);
        }

        exit_json_response($ret);
    }

    function multi_import(array & $request, array & $response, array & $app) {
        
    }

    function select_supplier(array & $request, array & $response, array & $app) {
        $app['page'] = 'NULL';
    }

    /**
     * 删除单据明细
     */
    function do_delete_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/PlannedRecordDetailModel')->delete($request['planned_record_detail_id']);
        exit_json_response($ret);
    }

    /**
     * 删除总单据以及单据明细
     */
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/PlannedRecordModel')->delete($request['planned_record_id']);
        exit_json_response($ret);
    }

    //增加明细
    function do_add_detail(array & $request, array & $response, array & $app) {
        //print_r($request);exit;
        $data = $request['data'];
        //调整单明细添加
        $ret = load_model('pur/PlannedRecordDetailModel')->add_detail_action($request['id'], $data);
        exit_json_response($ret);
    }

    /**
     * 修改单据明细数量
     */
    function do_edit_detail(array & $request, array & $response, array & $app) {
        $detail = array('sku' => $request['sku'], 'num' => $request['num'], 'sell_price' => $request['sell_price'], 'finish_num' => $request['finish_num'], 'barcode' => $request['barcode']);
        $ret = load_model('pur/PlannedRecordDetailModel')->do_edit_detail($request['pid'], $detail);
        $res = load_model('pur/PlannedRecordModel')->get_by_id($request['pid']);
        $ret['res'] = $res['data'];
        exit_json_response($ret);
    }

    //采购订单生成采购通知单
    function execute(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/PlannedRecordModel')->get_by_id($request['planned_record_id']);
        $response['data'] = $ret['data'];
    }

    /**
     * 采购订单生成采购通知单
     */
    function create_order_record(array &$request, array &$response, array &$app) {
        $planned_record = get_array_vars($request, array('planned_record_id', 'create_type', 'record_code'));
        $ret = load_model('pur/PlannedRecordModel')->create_order_record($planned_record);
        exit_json_response($ret);
    }

    /**
     * 审核
     */
    function do_check(array &$request, array &$response, array &$app) {
        //$app['fmt'] = 'json';
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('pur/PlannedRecordModel')->update_check($arr[$request['type']], 'is_check', $request['id']);

        if ($ret['status'] == '1') {
            $plan_data = load_model('pur/PlannedRecordModel')->get_by_id($request['id']);
            if ($request['type'] == 'enable') {
                //确认采购订单加在途
                load_model('prm/InvOpRoadModel')->set_road_inv($plan_data['data']['record_code'], 1);
            }
            if ($request['type'] == 'disable') {
                //取消采购订单确认，减在途
                load_model('prm/InvOpRoadModel')->set_road_inv($plan_data['data']['record_code'], -1);
            }
            //日志
            if ($request['type'] == 'disable') {
                $action_name = '取消确认';
                $sure_status = '未确认';
            }
            if ($request['type'] == 'enable') {
                $action_name = '确认';
                $sure_status = '确认';
            }
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未完成', 'action_name' => $action_name, 'module' => "planned_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 完成
     */
    function do_finish(array &$request, array &$response, array &$app) {
        $plan_data = load_model('pur/PlannedRecordModel')->get_by_code($request['record_code']);
        $ret1 = load_model('pur/PlannedRecordModel')->update_finish($request['record_code'], 1);
        if ($ret1['status'] == '1') {
            $ret = load_model('pur/PlannedRecordModel')->update_check('1', 'is_finish', $plan_data['data']['planned_record_id']);
            if ($ret['status'] == '1') {

                //强制完成
                load_model('prm/InvOpRoadModel')->set_road_inv($request['record_code'], 0);
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '完成', 'action_name' => '完成', 'module' => "planned_record", 'pid' => $request['id']);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
        } else {
            $ret = $ret1;
        }

        exit_json_response($ret);
    }

    /**
     * 修改计划单主单据
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $ret = load_model('pur/PlannedRecordModel')->edit_action($request['record_code'], $request['parameter'], array('planned_record_id' => $request['parameterUrl']['planned_record_id']));
        $record = load_model('pur/PlannedRecordModel')->is_exists($request['record_code']);
        $record_data = $record['data'];
        if ($ret['status'] == '1') {
            //日志
            $sure_status = ($record_data['is_check'] == 0) ? '未确认' : '已确认';
            $finish_status = ($record_data['is_finish'] == 0) ? '未完成' : '已完成';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => $finish_status, 'action_name' => '修改', 'module' => "planned_record", 'pid' => $request['parameterUrl']['planned_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //查看详情
    function view(array & $request, array & $response, array & $app) {
        //主单据信息
        $ret = load_model('pur/PlannedRecordModel')->get_by_id($request['planned_record_id']);
        $arr = array('pur_express_print', 'clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['pur_express_print'] = isset($ret_arr['pur_express_print']) ? $ret_arr['pur_express_print'] : '';
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $response['selection']['supplier'] = load_model('base/SupplierModel')->get_view_select();
        $response['selection']['adjust_type'] = bui_get_select('record_type', 0, array('record_type_property' => 8));
        $response['selection']['record_type'] = bui_get_select('record_type', 0, array('record_type_property' => 0));
        ;
        $ret['data']['planned_time'] = isset($ret['data']['planned_time']) ? date('Y-m-d', strtotime($ret['data']['planned_time'])) : '';
        $ret['data']['in_time'] = isset($ret['data']['in_time']) ? date('Y-m-d', strtotime($ret['data']['in_time'])) : '';
        $ret['data']['record_time'] = isset($ret['data']['record_time']) ? date('Y-m-d H:i:s', strtotime($ret['data']['record_time'])) : '';
        $ok = get_theme_url('images/ok.png');
        $no = get_theme_url('images/no.gif');
        if ($ret['data']['is_check'] == '1') {
            $is_check_src = $ok;
        } else {
            $is_check_src = $no;
        }
        $ret['data']['is_check_src'] = "<img src='{$is_check_src}'>";

        if ($ret['data']['is_execute'] == '1') {
            $is_is_execute_src = $ok;
        } else {
            $is_is_execute_src = $no;
        }
        $ret['data']['is_execute_src'] = "<img src='{$is_is_execute_src}'>";

        if ($ret['data']['is_finish'] == '1') {
            $is_finish_src = $ok;
        } else {
            $is_finish_src = $no;
        }
        $ret['data']['is_finish_src'] = "<img src='{$is_finish_src}'>";
        $ret['data']['record_type_code'] = $ret['data']['pur_type_code'];
        filter_fk_name($ret['data'], array('record_type_code|record_type'));
        $response['data'] = $ret['data'];
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $response['price_status'] = $price_status['status'];
        //是否生成通知单
        $notify = load_model('pur/OrderRecordModel')->is_exists($ret['data']['record_code'], 'relation_code');
        $response['is_notify_code'] = !empty($notify['data']) ? 1 : 0;
        //是否开启系统参数采购账务
        $procurement_accounts = load_model('sys/SysParamsModel')->get_val_by_code('procurement_accounts');
        $response['is_pur_payment'] = $procurement_accounts['procurement_accounts'];

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    /**
     * 导入-页面
     */
    function importGoods(array & $request, array & $response, array & $app) {
        $response['id'] = $request['id'];
        $response['import_type'] = $request['import_type'];
    }

    /**
     * 导入-上传文件
     */
    function import_goods(array & $request, array & $response, array & $app) {
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('pur/PlannedRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
        set_uplaod($request, $response, $app);
    }

    /**
     * 导入-入库
     */
    function import_goods_upload(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $import_type = isset($request['import_type']) ? $request['import_type'] : 1;
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $fun = $import_type == 1 ? 'imoprt_detail' : 'import_complete_num';
        $param = get_array_vars($request, array('id', 'type'));
        $ret = load_model('pur/PlannedRecordModel')->$fun($param, $file);
        $response = $ret;
    }

    /**
     * 导出明细
     */
    function exprot_detail(array &$request, array &$response, array &$app) {
        if ($request['type'] == 'view_export') {
            $filter['record_code'] = $request['record_code'];
            $filter['goods_code'] = $request['goods_code'];
        } else {
            $filter = $request;
        }
        $filter['ctl_type'] = 'export_detail';
        $filter['page'] = 1;
        $filter['page_size'] = 10000;

        $res = load_model('pur/PlannedRecordDetailModel')->get_by_page($filter);
        $detail_result = $res['data']['data'];
        $str = "单据编号,原单号,供货商,仓库,采购类型,计划日期,入库期限,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,系统SKU码,进货价,单价,数量,商品总金额,完成数量,差异数,是否完成,完成金额\n";
        $str = iconv('utf-8', 'gbk', $str);

        foreach ($detail_result as $value) {
            $value['supplier_code_name'] = iconv('utf-8', 'gbk', $value['supplier_code_name']);
            $value['store_code_name'] = iconv('utf-8', 'gbk', $value['store_code_name']);
            $value['record_type_code_name'] = iconv('utf-8', 'gbk', $value['record_type_code_name']);
            $value['goods_name'] = mb_convert_encoding(str_replace("\xC2\xA0", ' ', $value['goods_name']), 'GBK', 'UTF-8'); //中英文混合并且带空格的
            $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
            $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
            $value['is_finish'] = iconv('utf-8', 'gbk', $value['is_finish']);
            $value['barcode'] = iconv('utf-8', 'gbk', $value['barcode']);
            $value['sku'] = iconv('utf-8', 'gbk', $value['sku']);
            $value['goods_code'] = iconv('utf-8', 'gbk', $value['goods_code']);
            $str .= $value['record_code'] . "\t," . $value['init_code'] . "\t," . $value['supplier_code_name'] . "\t," . $value['store_code_name'] . "\t," . $value['record_type_code_name'] . "\t," . $value['planned_time'] . "\t," . $value['in_time'] . "\t," . $value['goods_name'] . "\t," . $value['goods_code'] . "\t," . $value['spec1_code'] . "\t," . $value['spec1_name'] . "," . $value['spec2_code'] . "\t," . $value['spec2_name'] . "\t," . $value['barcode'] . "\t," . $value['sku'] . "\t," . $value['price'] . "\t," . $value['price1'] . "\t," . $value['num'] . "\t," . $value['money'] . "\t," . $value['finish_num'] . "\t," . $value['difference_num'] . "\t," . $value['is_finish'] . "\t," . $value['finish_money'] . "\n"; //用引文逗号分开
        }
        $filename = date('Ymd') . '.csv'; //设置文件名
        $this->export_csv($filename, $str); //导出
    }

    /**
     * 导出
     */
    function export_csv($filename, $data) {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
        die;
    }

    function multi_import_record(array &$request, array &$response, array &$app) {
        $response = load_model('pur/PlannedRecordModel')->multi_import_record($request['url']);
    }

    //通知财务付款
    function do_notify_payment(array &$request, array &$response, array &$app) {
        $ret = load_model('pur/PlannedRecordModel')->do_notify_payment($request['planned_record_id'], $request['type']);
        exit_json_response($ret);
    }

    function check_is_print(array & $request, array & $response, array & $app) {
        $response = load_model('pur/PlannedRecordModel')->check_is_print($request['planned_record_id']);
    }

    /**
     * 尺码层二维表导入采购单
     */
    function layer_import(array &$request, array &$response, array &$app) {

    }

    /**
     * 尺码层二维表导入采购单
     */
    function layer_import_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('pur/PlannedRecordModel')->layer_import_action($request['url']);
    }

    /**
     * 根据二维表生成模版
     */
    function get_excel_tpl(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('pur/PlannedRecordModel')->create_layer_import_tpl();
    }

}
