<?php

require_lib('comm_util', true);
require_lib('business_util', true);
require_lib('util/oms_util', true);
require_model("stm/TakeStockRecordModel");
require_model("sys/SysParamsModel");
require_model('stm/TakeStockRecordModel');
require_model('prm/GoodsLofModel');
require_model('stm/GoodsInvLofRecordModel');
require_model('prm/InvModel');

class take_stock_record {

    function do_list(array & $request, array & $response, array & $app) {

    }

    function add(array & $request, array & $response, array & $app) {
        //$response['data']['record_code'] = create_fast_bill_sn('check');
        $response['data']['record_code'] = load_model('stm/TakeStockRecordModel')->create_fast_bill_sn();
        // $ret['data']['record_code'] = load_model('stm/StockAdjustRecordModel')->create_fast_bill_sn();
    }

    function do_add(array & $request, array & $response, array & $app) {
        $mdl_take_stock = new TakeStockRecordModel();
        $request['is_add_time'] = date('Y-m-d H:i:s');
        $user_id = CTX()->get_session('user_id');
        $user_name = oms_tb_val('sys_user', 'user_name', array('user_id' => $user_id));
        $request['is_add_person'] = $user_name;
        $response = $mdl_take_stock->insert($request);
        if (isset($response['data']) && $response['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "", 'finish_status' => '未确认', 'action_name' => "创建", 'module' => "take_stock_record", 'pid' => $response['data']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($response);
    }

    function view(array & $request, array & $response, array & $app) {
        $mdl_take_stock = new TakeStockRecordModel();
        $mdl_sys_params = new SysParamsModel();
        $response['data'] = $mdl_take_stock->get_take_stock_by_id($request['_id']);
        $response['data']['take_stock_pd_status'] = ($response['data']['is_pre_profit_and_loss'] != 0) ? '已盘点' : '未盘点';
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    function do_edit(array & $request, array & $response, array & $app) {
        $mdl_take_stock = new TakeStockRecordModel();
        $response = $mdl_take_stock->edit_action($request['parameterUrl']['_id'], $request['parameter']);
        if ($response['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未确认', 'action_name' => '修改', 'module' => "take_stock_record", 'pid' => $request['parameterUrl']['_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
    }

    function do_add_detail(array & $request, array & $response, array & $app) {
        $mdl_stock = new TakeStockRecordModel();
        $mdl_goods_lof = new GoodsLofModel();
        $mdl_inv_lof = new GoodsInvLofRecordModel();

        //批次档案维护
        $ret = $mdl_goods_lof->add_detail_action($request['pid'], $request['data'], 'take_stock');
        //单据批次添加
        $ret = $mdl_inv_lof->add_detail_action($request['pid'], $request['store_code'], 'take_stock', $request['data']);
        if ($ret['status'] < 1) {
            return $ret;
        }
        //调整单明细添加
        $ret = $mdl_stock->add_detail_action($request['pid'], $request['data'], 'take_stock');
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未确认', 'action_name' => '增加明细', 'module' => "take_stock_record", 'pid' => $request['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //验收
    function do_sure(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $id = isset($request['id']) ? $request['id'] : $request['take_stock_record_id'];
        $mdl_take_stock = new TakeStockRecordModel();
        $response = $mdl_take_stock->update_by_id($id, array("take_stock_record_id" => $id), array("status" => 1, "is_sure" => 1));
        if ($response['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '已确认', 'action_name' => '已确认', 'action_note' => isset($request['is_detail_sure']) && $request['is_detail_sure'] == 1 ? '' : '扫描确认', 'module' => "take_stock_record", 'pid' => $id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
    }

    function do_stop(array & $request, array & $response, array & $app) {
        $mdl_take_stock = new TakeStockRecordModel();
        //$response = $mdl_take_stock->update_by_id($request['id'],array("is_stop"=>1));
        $response = $mdl_take_stock->update_by_id($request['id'], array("take_stock_record_id" => $request['id'], "is_pre_profit_and_loss" => '0'), array("is_stop" => 1));
        if ($response['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '已验收', 'action_name' => '终止', 'module' => "take_stock_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $mdl_take_stock = new TakeStockRecordModel();
        $response = $mdl_take_stock->delete_record($request['id']);
    }

    function do_delete_detail(array & $request, array & $response, array & $app) {
        $mdl_take_stock = new TakeStockRecordModel();
        $response = $mdl_take_stock->delete_detail($request['id'], $request['pid']);
    }

    function do_delete_detail_lof(array & $request, array & $response, array & $app) {
        $mdl_take_stock = new TakeStockRecordModel();
        $response = $mdl_take_stock->delete_detail_lof($request['id'], $request['pid']);
    }

    function view_unconfirmed(array & $request, array & $response, array & $app) {
        $mdl_take_stock = new TakeStockRecordModel();
        $response = $mdl_take_stock->get_unconfirmed_record($request['_id']);
    }

    function pre_profit(array & $request, array & $response, array & $app) {
        $mdl_take_stock = new TakeStockRecordModel();

        $response['detail'] = $mdl_take_stock->deal_pre_profit($request['_id']);

        $arr = array('goods_spec1', 'goods_spec2', 'lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
    }

    function profit_and_loss(array & $request, array & $response, array & $app) {
        $mdl_take_stock = new TakeStockRecordModel();
        $response['record_list'] = $mdl_take_stock->get_take_stock(array("is_sure" => 1, "is_stop" => 0, "is_pre_profit_and_loss" => 0));
    }

//
//	function clear_inv(array & $request, array & $response, array & $app){
//	    $mdl_take_stock = new TakeStockRecordModel();
//	    $response = $mdl_take_stock->clean_up();
//	}
//
//	function count_inv_num(array & $request, array & $response, array & $app){
//	    $mdl_take_stock = new TakeStockRecordModel();
//	    $response = $mdl_take_stock->count_inv_num($request['id']);
//	}
//
//	function create_adjust_record(array & $request, array & $response, array & $app){
//	    $mdl_take_stock = new TakeStockRecordModel();
//	    $response = $mdl_take_stock->create_adjust_record($request['id'],$request['type']);
//	}

    function take_stock_inv(array & $request, array & $response, array & $app) {
        $response = load_model('stm/TakeStockRecordModel')->take_stock_inv($request);
    }

    function set_take_stock_inv(array & $request, array & $response, array & $app) {
        //后台执行
        //($id,$type=1,$status=1)
        $response = load_model('stm/TakeStockRecordModel')->set_take_stock_inv($request);
    }

    function get_take_stock_inv(array & $request, array & $response, array & $app) {
        $response = load_model('stm/TakeStockRecordModel')->get_take_stock_inv($request['recode_code_list']);
    }

    function get_take_stock_info(array & $request, array & $response, array & $app) {
        $response = load_model('stm/TakeStockRecordModel')->get_take_stock_info($request['store_date']);
    }

    function get_stock_info(array & $request, array & $response, array & $app) {
        $response['data'] = load_model('stm/TakeStockRecordModel')->get_take_stock_by_id($request['id']);
    }

    //导入商品
    function importGoods(array & $request, array & $response, array & $app) {
        $response['id'] = $request['id'];
        $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $response['lof_status'] = $lof_status['lof_status'];
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
        $ret = load_model('stm/TakeStockRecordModel')->imoprt_detail($request['id'], $file, $request['is_lof']);
        $response = $ret;
    }

    function import_goods(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
        set_uplaod($request, $response, $app);
    }

    //扫描单据添加数量
    function update_scan_num(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/TakeStockRecordModel')->update_scan_num($request['record_code'], $request['num'], $request['id']);
        exit_json_response($ret);
    }

    function export_csv_list(array & $request, array & $response, array & $app) {
        $detail_result = load_model('stm/TakeStockRecordModel')->get_detail_by_record_code($request['record_code'], $request['lof_status'], $request['goods_code']);
        $plus_str = ($request['lof_status'] == 1) ? "批次号,生产日期,盘点数量" : "盘点数量";
        $str = "确认,盘点,终止,单据编号,业务时间,盘点仓库,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,{$plus_str}\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($detail_result as $value) {
            $value['is_sure'] = iconv('utf-8', 'gbk', $value['is_sure']);
            $value['is_pre_profit_and_loss'] = iconv('utf-8', 'gbk', $value['is_pre_profit_and_loss']);
            $value['is_stop'] = iconv('utf-8', 'gbk', $value['is_stop']);
            $value['status'] = iconv('utf-8', 'gbk', $value['status']);
            $value['store_name'] = iconv('utf-8', 'gbk', $value['store_name']);
            $value['goods_name'] = mb_convert_encoding(str_replace("\xC2\xA0", ' ', $value['goods_name']), 'GBK', 'UTF-8'); //中英文混合并且带空格的
            $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
            $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
            $str_plus = ($request['lof_status'] == 1) ? $value['lof_no'] . "," . $value['production_date'] . "," . $value['num'] : $value['num'];
            $str .= $value['is_sure'] . "," . $value['is_pre_profit_and_loss'] . "," . $value['is_stop'] . "," . $value['record_code'] . "," . $value['take_stock_time'] .
                    "," . $value['store_name'] . "," . $value['goods_name'] . "," . $value['goods_code'] . "," . $value['spec1_code'] . "," . $value['spec1_name'] .
                    "," . $value['spec2_code'] . "," . $value['spec2_name'] . "," . $value['barcode'] . "," . $str_plus . "\n"; //用引文逗号分开
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

}
