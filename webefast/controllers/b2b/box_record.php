<?php

require_lib('util/web_util', true);
require_lib('business_util', true);

class box_record {

    function do_list(array & $request, array & $response, array & $app) {
        $box = load_model('sys/SysParamsModel')->get_val_by_code(array('clodop_print'));
        $response['new_clodop_print'] = isset($box['clodop_print']) ? $box['clodop_print'] : 0;
        $response['task_code'] = $request['task_code'];
        $response['view_type'] = isset($request['view_type']) ? $request['view_type'] : 0; //=1时 装箱扫描查看装箱单
        $response['is_jit_execute'] = isset($request['is_jit_execute']) ? $request['is_jit_execute'] : 0; //=1时，装箱扫描，查看装箱单，销货单是由唯品会JIT生成的
        $response['is_print'] = isset($request['is_print']) ? $request['is_print'] : 0; //=all时，装箱扫描查看装箱单，不限制打印条件
    }

    function detail_list(array & $request, array & $response, array & $app) {
        $ret = load_model('b2b/BoxRecordModel')->get_list_by_item_id($request['record_code'], array());
        $dataset = $ret['data'];

        exit_table_data_json($dataset['data'], $dataset['filter']['record_count']);
    }

    //详情
    function view(array & $request, array & $response, array & $app) {
        //主单据信息
        $ret = load_model('b2b/BoxRecordModel')->get_by_id($request['id']);
        $response['data'] = $ret;

        //规格别名
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status','clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;

        $response['scene'] = $app['scene'];
    }

    /**
     * 装箱单明细编辑
     */
    function edit(array & $request, array & $response, array & $app) {
        $this->view($request, $response, $app);
        $app['act'] = 'view';
    }

    /**
     * 箱唛打印
     */
    function print_express(array & $request, array & $response, array & $app) {
        //参数 默认开启不开启
// 		$ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
// 		$response['wave_print'] =isset($ret_arr['wave_print'])?$ret_arr['wave_print']:'' ;
        $ret = load_model('sys/PrintTemplatesModel')->get_by_code($request['print_templates_code']);
        if (!empty($ret)) {
            $ret_tpl = load_model('sys/PrintTemplatesModel')->get_templates_by_id($ret['print_templates_id']);
            if ($ret_tpl['status'] > 0) {
                $response['status'] = '1';
                $response['data'] = $ret['data'];
                $response['tpl']['pt'] = $ret_tpl['data'];
            } else {
                $response['status'] = '-1';
                $response['message'] = $ret_tpl['message'];
            }
        } else {
            $response['status'] = '-1';
            $response['message'] = '模版不存在';
        }
    }

    /**
     * 获取箱唛打印数据
     */
    function get_print_express_data(array & $request, array & $response, array & $app) {
        $act = $request['print_templates_code'] == 'general_box_print' ? 'get_general_box_print_page' : 'get_record_print_page';
        $response = load_model("b2b/BoxRecordModel")->$act($request);
    }

    function do_edit_detail(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $field = array('record_code', 'sku', 'barcode', 'num', 'is_lof');
        if ($request['is_lof'] == 1) {
            $field = array_merge($field, array('lof_no', 'production_date'));
            $params = get_array_vars($request, $field);
        } else {
            $params = get_array_vars($request, $field);
        }
        $response = load_model('b2b/BoxRecordDatailModel')->update_detail($params);
    }

    function do_delete_detail(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $field = array('record_code', 'sku', 'barcode', 'is_lof');
        if ($request['is_lof'] == 1) {
            $field = array_merge($field, array('lof_no', 'production_date'));
            $params = get_array_vars($request, $field);
        } else {
            $params = get_array_vars($request, $field);
        }
        $response = load_model('b2b/BoxRecordDatailModel')->delete_detail($params);
    }

}
