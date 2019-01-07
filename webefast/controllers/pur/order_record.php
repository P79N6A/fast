<?php

require_lib('util/oms_util', true);

class order_record {

    function do_list(array & $request, array & $response, array & $app) {
        $response['user_id'] = CTX()->get_session('user_id');
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('pur/OrderRecordModel')->get_by_id($request['_id']);
        }
        //采购类型
        $response['pur_type'] = $this->get_pur_type();
        //供应商
        $response['supplier'] = load_model('base/SupplierModel')->get_select(2);
        //调整仓库
        $response['store'] = load_model('base/StoreModel')->get_select(2);

        $ret['data']['record_code'] = load_model('pur/OrderRecordModel')->create_fast_bill_sn();
        $response['data'] = $ret['data'];
    }

    //暂时采购类型
    function get_pur_type() {
        $arr = load_model('pur/OrderRecordModel')->get_pur_type_list();
        $key = 0;
        $arr1 = array();
        foreach ($arr as $value) {
            $arr1[$key][0] = $value['record_type_code'];
            $arr1[$key][1] = $value['record_type_name'];
            $key++;
        }
        return $arr1;
    }

    function do_add(array & $request, array & $response, array & $app) {
        $request['order_time'] = date('Y-m-d H:i:s', time());
        $planned_record = get_array_vars($request, array('record_code', 'init_code', 'order_time', 'in_time', 'pur_type_code', 'supplier_code', 'store_code', 'rebate', 'remark'));
        $ret = load_model('pur/OrderRecordModel')->insert($planned_record);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "创建", 'module' => "order_record", 'pid' => $ret['data']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 修改计划单主单据
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $ret = load_model('pur/OrderRecordModel')->edit_action($request['parameter'], array('order_record_id' => $request['parameterUrl']['order_record_id']));
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '修改', 'module' => "order_record", 'pid' => $request['parameterUrl']['order_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //增加明细
    function do_add_detail(array & $request, array & $response, array & $app) {
        $data = $request['data'];
        //调整单明细添加
        $ret = load_model('pur/OrderRecordDetailModel')->add_detail_action($request['id'], $data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '增加明细', 'module' => "order_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 删除单据明细
     */
    function do_delete_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/OrderRecordDetailModel')->delete($request['order_record_detail_id']);
        exit_json_response($ret);
    }

    /**
     * 删除总单据以及单据明细
     */
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/OrderRecordModel')->delete($request['order_record_id']);
        exit_json_response($ret);
    }

    /**
     * 审核
     */
    function do_check(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('pur/OrderRecordModel')->update_check($arr[$request['type']], 'is_check', $request['id']);
        if ($ret['status'] == '1') {
            //日志
            if ($request['type'] == 'disable') {
                $action_name = '取消确认';
                $sure_status = '未确认';
            }
            if ($request['type'] == 'enable') {
                $action_name = '确认';
                $sure_status = '确认';
            }
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未完成', 'action_name' => $action_name, 'module' => "order_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 完成
     */
    function do_finish(array &$request, array &$response, array &$app) {
        $ret1 = load_model('pur/OrderRecordModel')->check_exists_no_accept_pur($request['record_code']);
        if ($ret1['status'] == '1') {
            $ret = load_model('pur/OrderRecordModel')->update_check('1', 'is_finish', $request['id']);
            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '完成', 'action_name' => '完成', 'module' => "order_record", 'pid' => $request['id']);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
        } else {
            $ret = $ret1;
        }
        exit_json_response($ret);
    }

    //采购通知单生成采购入库单
    function execute(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/OrderRecordModel')->get_by_id($request['order_record_id']);
        $response['data'] = $ret['data'];
    }

    /**
     * 采购通知单生成采购入库单
     */
    function create_purchaser_record(array &$request, array &$response, array &$app) {
        $order_record = get_array_vars($request, array('order_record_id', 'create_type', 'record_code'));
        $ret = load_model('pur/OrderRecordModel')->create_purchaser_record($order_record);
        exit_json_response($ret);
    }

    /**
     * 修改单据明细数量
     */
    function do_edit_detail(array & $request, array & $response, array & $app) {

        $detail = array('sku' => $request['sku'], 'num' => $request['num'], 'rebate' => $request['rebate'], 'sell_price' => $request['sell_price']);

        $ret = load_model('pur/OrderRecordDetailModel')->edit_detail_action($request['pid'], $detail);
        $res = load_model('pur/OrderRecordModel')->get_by_id($request['pid']);
        $ret['res'] = $res['data'];
        exit_json_response($ret);
    }

    //查看详情
    function view(array & $request, array & $response, array & $app) {
        //主单据信息
        $ret = load_model('pur/OrderRecordModel')->get_by_id($request['order_record_id']);
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $response['selection']['supplier'] = load_model('base/SupplierModel')->get_view_select();
        $pur_type = $this->get_pur_type();
        $response['selection']['pur_type'] = $this->bui_pur_type($pur_type);
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
        $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($ret['data']['store_code']);
        if ($wms_system_code !== FALSE) {
            $ret['data']['is_wms'] = '1';
        } else {
            $ret['data']['is_wms'] = '0';
        }
        $ret['data']['diff_num'] = $ret['data']['num'] - $ret['data']['finish_num']; //差异数量 = 总数-完成数
        $response['data'] = $ret['data'];
        //spec1别名
        $arr = array('goods_spec1', 'lof_status');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        $response['lof_status'] = $arr_spec1['lof_status'];
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $response['price_status'] = $price_status['status'];

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    function bui_pur_type($pur_type) {
        $result = $pur_type;
        $return = array();
        if (empty($result) || !is_array($result)) {
            return json_encode($return);
        }
        $array_keys = array_keys($result[0]);
        foreach ($result as $data) {
            $return[] = array(
                'value' => $data[$array_keys[0]],
                'text' => $data[$array_keys[1]],
            );
        }
        return json_encode($return);
    }

    //导入商品
    function import_goods(array & $request, array & $response, array & $app) {
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
        set_uplaod($request, $response, $app);
    }

    function import_goods_upload(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        //$response = load_model('prm/GoodsImportModel')->import_base_spec1($file);

        $ret = load_model('pur/OrderRecordModel')->imoprt_detail($request['id'], $file);
        $response = $ret;
    }

    function importGoods(array & $request, array & $response, array & $app) {
        $response['id'] = $request['id'];
        // $arr = array('lof_status');
        // $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        // $response['lof_status'] =isset($ret_arr['lof_status'])?$ret_arr['lof_status']:'' ;
    }

    /**
     * 导出主表csv
     */
    function export_main_list(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        //var_dump($request['record_code']);die;
        $main_result = load_model('pur/OrderRecordModel')->get_export_data($request);

        $str = "单据编号,原单号,采购订单号,供货商,仓库,采购类型,下单日期,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,系统SKU码,标准进价,数量,金额,完成数,差异数\n";
        $str = iconv('utf-8', 'gbk', $str);

        foreach ($main_result as $value) {
            $value['supplier_code_name'] = iconv('utf-8', 'gbk', $value['supplier_code_name']);
            $value['store_code_name'] = iconv('utf-8', 'gbk', $value['store_code_name']);
            $pur_type_name = oms_tb_val('base_record_type', 'record_type_name', array('record_type_code' => $value['pur_type_code']));
            $pur_type_name = iconv('utf-8', 'gbk', $pur_type_name);
            $spec1_name = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));
            $spec2_name = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));
            $value['goods_name'] = iconv('utf-8', 'gbk', $value['goods_name']);
            $spec1_name = iconv('utf-8', 'gbk', $spec1_name);
            $spec2_name = iconv('utf-8', 'gbk', $spec2_name);

            $str .= $value['record_code'] . "," . $value['init_code'] . "," . $value['relation_code'] . "," . $value['supplier_code_name'] . "," . $value['store_code_name'] . "," . $pur_type_name .
                    "," . $value['order_time'] . "," . $value['goods_name'] . "\t" . "," . $value['goods_code'] . "\t" . "," . $value['spec1_code'] . "\t" . "," . $spec1_name . "," . $value['spec2_code'] . "\t" . "," . $spec2_name . "," . $value['barcode'] . "\t" . "," . $value['sku'] . "\t" . "," . $value['price'] . "\t" . "," . $value['num'] . "\t" . "," . $value['money'] . "\t" . "," . $value['finish_num'] . "\t" . "," . $value['different_num'] . "\t" . "\n"; //用引文逗号分开
        }

        //print_r($str);
        $filename = date('Ymd') . '.csv'; //设置文件名
        $this->export_csv($filename, $str); //导出
    }

    /**
     * 导出明细csv
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $ret = load_model('pur/OrderRecordModel')->get_by_id($request['id']);
        $main_result = $ret['data'];
        $pur_type_name = oms_tb_val('base_record_type', 'record_type_name', array('record_type_code' => $main_result['pur_type_code']));
        //print_r($pur_type_name);exit;

        $filter['record_code'] = $request['record_code'];
        $filter['page'] = 1;
        $filter['page_size'] = load_model('pur/OrderRecordDetailModel')->get_num($filter);
        $res = load_model('pur/OrderRecordDetailModel')->get_by_page($filter);

        $detail_result = $res['data']['data'];
        //print_r($detail_result);exit;
        $str = "单据编号,原单号,采购订单号,供货商,仓库,采购类型,下单日期,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,标准进价,数量,金额,完成数,差异数\n";
        $str = iconv('utf-8', 'gbk', $str);
        $main_result['supplier_code_name'] = iconv('utf-8', 'gbk', $main_result['supplier_code_name']);
        $main_result['store_code_name'] = iconv('utf-8', 'gbk', $main_result['store_code_name']);
        $pur_type_name = iconv('utf-8', 'gbk', $pur_type_name);
        foreach ($detail_result as $value) {
            $v = str_replace(array(chr(7), chr(10), chr(13), ','), array('', '', '', '，'), $v);
            // $v = iconv('utf-8','gbk',$v);
            //  $v = iconv('utf-8','GB2312//IGNORE',$v);

            $value['goods_code'] = iconv('utf-8', 'gbk', $value['goods_code']);
            $value['goods_name'] = iconv('utf-8', 'gbk', $value['goods_name']);
            $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
            $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
            $value['barcode'] = iconv('utf-8', 'gbk', $value['barcode']);
            $str .= $main_result['record_code'] . "," . $main_result['init_code'] . "," . $main_result['relation_code'] . "," . $main_result['supplier_code_name'] . "," . $main_result['store_code_name'] . "," . $pur_type_name .
                    "," . $main_result['order_time'] . "," . $value['goods_name'] . "," . $value['goods_code'] . "\t" . "," . $value['spec1_code'] . "\t" . "," . $value['spec1_name'] .
                    "," . $value['spec2_code'] . "\t" . "," . $value['spec2_name'] . "," . $value['barcode'] . "\t" . "," . $value['price'] . "," . $value['num'] . "," . $value['money'] . "," . $value['finish_num'] . "," . $value['difference_num'] . "\n"; //用引文逗号分开
        }

        $filename = date('Ymd') . '.csv'; //设置文件名
        $this->export_csv($filename, $str); //导出
    }

    function export_csv($filename, $data) {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
        die;
    }

    function detail_supplier(array &$request, array &$response, array &$app) {
        
    }

    //是否有未入库的采购入库单
    function out_relation(array &$request, array &$response, array &$app) {
        $ret = load_model('pur/OrderRecordModel')->out_relation($request['id']);
        exit_json_response($ret);
    }

    function select_supplier(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    function select_action(array &$request, array &$response, array &$app) {
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('base/SupplierModel')->get_supplier_select($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    /**
     * 修改扫描数量
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function update_scan_num(array & $request, array & $response, array & $app) {
        if ($request['is_lof'] == 1) {
            $ret = array('status' => 1, 'data' => '', 'message' => '采购通知单无批次数据！');
        } else {
            $ret = load_model('pur/OrderRecordDetailModel')->update_scan_num($request['record_code'], $request['num'], $request['id']);
        }
        exit_json_response($ret);
    }

    /**
     * 扫描页面确认
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function scan_do_check(array &$request, array &$response, array &$app) {
        $ret = load_model('pur/OrderRecordModel')->scan_do_check($request);
        exit_json_response($ret);
    }

}
