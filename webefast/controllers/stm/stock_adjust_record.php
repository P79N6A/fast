<?php

require_lib('util/web_util', true);
require_lib('comm_util');
require_lib('util/oms_util', true);

class stock_adjust_record {

    function do_list(array & $request, array & $response, array & $app) {
        //调整类型
        $response['adjust_type'] = load_model('base/StoreAdjustTypeModel')->get_code_to_conf();
    }

    function entity_shop(array &$request, array &$response, array &$app) {
        $response['is_entity_shop'] = '1';
        $response['adjust_type'] = load_model('base/StoreAdjustTypeModel')->get_code_to_conf();
        $response['login_type'] = CTX()->get_session('login_type');
        if ($response['login_type'] > 0) {
            $shop_code = empty(CTX()->get_session('oms_shop_code')) ? '' : CTX()->get_session('oms_shop_code');
            $response['store'] = load_model('base/StoreModel')->get_store_by_shop($shop_code);
        } else {
            $response['store'] = load_model('base/StoreModel')->get_entity_store();
        }
    }

    //仓库
    function get_store() {
        $arr_store = load_model('base/StoreModel')->get_list();
        $key = 0;
        foreach ($arr_store as $value) {
            $arr_store[$key][0] = $value['store_code'];
            $arr_store[$key][1] = $value['store_name'];
            $key++;
        }
        return $arr_store;
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('stm/StockAdjustRecordModel')->get_by_id($request['_id']);
        }
        //调整仓库
        $response['login_type'] = CTX()->get_session('login_type');
        if ($response['login_type'] > 0) {
            $shop_code = empty(CTX()->get_session('oms_shop_code')) ? '' : CTX()->get_session('oms_shop_code');
            $response['store'] = load_model('base/StoreModel')->get_store_by_shop($shop_code);
        } else if (isset($request['shop_type']) && $request['shop_type'] == 'entity_shop') {
            $response['store'] = load_model('base/StoreModel')->get_entity_store();
            $store = array(array('', '请选择'));
            $response['store'] = array_merge($store, $response['store']);
        } else {
            $response['store'] = load_model('base/StoreModel')->get_select(2);
        }
        //调整类型, 动态获取调整类型
        $response['adjust_type'] = load_model('base/StoreAdjustTypeModel')->get_code_to_conf();
        $ret['data']['record_code'] = load_model('stm/StockAdjustRecordModel')->create_fast_bill_sn();
        $response['data'] = $ret['data'];
    }

    /**
     * 查看库存调整单详情页, 包含基本信息和调整明细信息
     * @param array $request
     * @param array $response
     * @param array $app
     * @throws Exception
     */
    function view(array & $request, array & $response, array & $app) {
        //主单据信息
        $ret = load_model('stm/StockAdjustRecordModel')->get_by_id($request['stock_adjust_record_id']);
        if (isset($request['stm_record_code']) && $request['stm_record_code'] != '') {
            $ret = load_model('stm/StockAdjustRecordModel')->get_by_code($request['stm_record_code']);
        }
        //
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        if ($ret['data']['is_entity_shop'] == 1) {
            $response['selection']['store'] = load_model('base/StoreModel')->get_entity_store_view_select();
        }
        $response['selection']['adjust_type'] = bui_get_select('record_type', 0, array('record_type_property' => 8));
        $response['data'] = $ret['data'];
        //spec1别名
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $response['login_type'] = CTX()->get_session('login_type');
        $response['is_entity'] = 0;

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    function entity_view(array & $request, array & $response, array & $app) {
        $app['act'] = 'view';
        $this->view($request, $response, $app);
        $response['is_entity'] = 1;
    }

    function do_add(array & $request, array & $response, array & $app) {
        $stock_adjus = get_array_vars($request, array('record_code', 'init_code', 'record_time', 'adjust_type', 'reason', 'store_code', 'remark'));
        $stock_adjus_data = load_model('stm/StockAdjustRecordModel')->get_by_code($stock_adjus['record_code']); //获取调整单数数据
        if (!empty($stock_adjus_data['data'])) {
            exit_json_response('-1', $stock_adjus_data, '此单据已存在，请重新添加');
        }
        $stock_adjus['is_entity_shop'] = 0;
        if (isset($request['shop_type']) && $request['shop_type'] == 'entity_shop') {
            $stock_adjus['is_entity_shop'] = 1;
        }
        $user_id = CTX()->get_session('user_id');
        $user_name = oms_tb_val('sys_user', 'user_name', array('user_id' => $user_id));
        $stock_adjus['is_add_person'] = $user_name;
        $ret = load_model('stm/StockAdjustRecordModel')->insert($stock_adjus);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "", 'finish_status' => '未验收', 'action_name' => "创建", 'module' => "stock_adjust_record", 'pid' => $ret['data']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 库存调整单增加明细
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array json
     */
    function do_add_detail(array & $request, array & $response, array & $app) {
        //print_r($request);exit;
        $data = $request['data'];
        $adjust_info = load_model('stm/StockAdjustRecordModel')->get_by_id($request['id']);
        $store_code = $adjust_info['data']['store_code'];
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($request['id'], $data);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($request['id'], $store_code, 'adjust', $data);
        if ($ret['status'] < 1) {
            return $ret;
        }
        //调整单明细添加
        $ret = load_model('stm/StmStockAdjustRecordDetailModel')->add_detail_action($request['id'], $data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '增加明细', 'module' => "stock_adjust_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 修改单据明细数量
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-11
     *
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array json
     */
    function do_edit_detail(array & $request, array & $response, array & $app) {
        $detail = array(
            array('sku' => $request['sku'], 'num' => $request['num'], 'sell_price' => $request['sell_price']),
        );
        $ret = load_model('stm/StmStockAdjustRecordDetailModel')->add_detail_action($request['pid'], $detail);
        exit_json_response($ret);
    }

    /**
     * 修改单据明细数量
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-11
     *
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array json
     */
    function do_edit_detail_lof() {
        $detail = array(
            array('sku' => $request['sku'], 'lof_no' => $request['lof_no'], 'production_date' => $request['production_date'], 'num' => $request['num'], 'sell_price' => $request['sell_price']),
        );
        $ret = load_model('stm/StmStockAdjustRecordDetailModel')->add_detail_action_lof($request['pid'], $detail);
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StockAdjustRecordModel')->delete($request['stock_adjust_record_id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '删除单据', 'module' => "stock_adjust_record", 'pid' => $request['stock_adjust_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 调整单验收
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-11
     * @param array $request
     * @param array $response
     * @param array $app
     * @return string json
     */
    function do_checkin(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StockAdjustRecordModel')->checkin($request['id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '已验收', 'action_name' => '已验收', 'module' => "stock_adjust_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 调整单扫描验收
     */
    function do_scan_checkin(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StockAdjustRecordModel')->checkin($request['stock_adjust_record_id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '已验收', 'action_name' => '已验收', 'module' => "stock_adjust_record", 'pid' => $request['stock_adjust_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    function do_entity_checkin(array & $request, array & $response, array & $app) {
        $this->do_checkin($request, $response, $app);
    }

    /**
     * 删除单据明细
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-10
     * @param array $request
     * @param array $response
     * @param array $app
     * @throws Exception
     */
    function do_delete_detail(array & $request, array & $response, array & $app) {

        $ret = load_model('stm/StmStockAdjustRecordDetailModel')->delete($request['stock_adjust_record_detail_id']);
        //批次删除
        $ret1 = load_model('stm/GoodsInvLofRecordModel')->delete_pid($request['pid'], $request['sku'], 'adjust');
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '删除商品', 'module' => "stock_adjust_record", 'pid' => $request['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    function do_delete_detail_lof(array & $request, array & $response, array & $app) {

        $ret = load_model('stm/StmStockAdjustRecordDetailModel')->delete_lof($request['id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '删除商品批次', 'module' => "stock_adjust_record", 'pid' => $request['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    function update_check(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('stm/StockAdjustRecordModel')->update_check($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }

    /**
     * 修改调整单主单据
     * @param array $request
     * @param array $response
     * @param array $app
     * @return string json
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $ret = load_model('stm/StockAdjustRecordModel')->edit_action($request['parameter'], array('stock_adjust_record_id' => $request['parameterUrl']['stock_adjust_record_id']));
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '修改', 'module' => "stock_adjust_record", 'pid' => $request['parameterUrl']['stock_adjust_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 库存调整单导入
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function import(array & $request, array & $response, array & $app) {
        //调整仓库
        $response['store'] = $this->get_store();
        //调整类型
        $response['adjust_type'] = load_model('base/StoreAdjustTypeModel')->get_code_to_conf();
    }

    function importGoods(array & $request, array & $response, array & $app) {
        $response['id'] = $request['id'];
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
    }

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

    function do_import_goods(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }

        $ret = load_model('stm/StockAdjustRecordModel')->imoprt_detail($request['id'], $file, $request['is_lof']);
        $response = $ret;
    }

    function export_csv_list(array & $request, array & $response, array & $app) {
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('cost_price');
        $detail_result = load_model('stm/StockAdjustRecordModel')->get_detail_by_record_code($request['record_code'], $request['lof_status'], $request['goods_code']);
        $plus_str = ($request['lof_status'] == 1) ? "批次号,生产日期,调整数量" : "调整数量";
        $str = "验收,单据编号,原单号,调整类型,业务日期,仓库,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,成本价,吊牌价,{$plus_str},调整金额\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($detail_result as $value) {
            if ($status['status'] != 1) {
                $cost_price = '****';
            } else {
                $cost_price = $value['cost_price'];
            }
            $value['is_check_and_accept'] = iconv('utf-8', 'gbk', $value['is_check_and_accept']);
            $value['adjust_type_name'] = iconv('utf-8', 'gbk', $value['adjust_type_name']);
            $value['store_name'] = iconv('utf-8', 'gbk', $value['store_name']);
            $value['goods_name'] = mb_convert_encoding(str_replace("\xC2\xA0", ' ', $value['goods_name']), 'GBK', 'UTF-8'); //中英文混合并且带空格的
            $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
            $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
            $str_plus = ($request['lof_status'] == 1) ? $value['lof_no'] . "," . $value['production_date'] . "," . $value['d_num'] : $value['d_num'];
            $str .= $value['is_check_and_accept'] . "," . $value['record_code'] . "," . $value['init_code'] . "," . $value['adjust_type_name'] . "," . $value['record_time'] .
                    "," . $value['store_name'] . "," . $value['goods_name'] . "," . "\t" . $value['goods_code'] . "," . "\t" . $value['spec1_code'] . "," . $value['spec1_name'] .
                    "," . "\t" . $value['spec2_code'] . "," . $value['spec2_name'] . "," . "\t" . $value['barcode'] . "," . $cost_price . "," . $value['price'] . "," . $str_plus . "," . $value['money'] . "\n"; //用引文逗号分开
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

    //扫描单据添加数量
    function update_scan_num(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StockAdjustRecordModel')->update_scan_num($request['record_code'], $request['num'], $request['id']);
        exit_json_response($ret);
    }

}
