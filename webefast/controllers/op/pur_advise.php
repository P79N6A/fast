<?php

require_lib('util/oms_util', true);

class pur_advise {

    function do_list(array & $request, array & $response, array & $app) {
        $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
        $response['store'] = load_model('base/StoreModel')->get_select_purview_store(0);
        $this->get_spec_rename($response);
        $ret = load_model('op/PurAdviseModel')->get_record_new();
        $response['record'] = $ret['data'];
    }

    function create_pur_advise_data(array & $request, array & $response, array & $app) {
        $date = date("m-d H:i:s", time());
        if (($date > "11-10 21:00:00") && ($date < "11-12 00:00:00")) {
            $msg = "双十一期间库存不允许同步";
            $response = array('status' => 1, 'msg' => $msg);
            return;
        }
        load_model('op/PurAdviseModel')->create_pur_advise_data();
        $response['status'] = 1;
    }

    private function get_spec_rename(array &$response) {
        //spec别名
        $arr = array('goods_spec1', 'goods_spec2');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
    }

    function create_pur_record(array & $request, array & $response, array & $app) {
        
    }

    function exec_create_detail_inv(array & $request, array & $response, array & $app) {
        
    }

    function get_create_detail_inv_status(array & $request, array & $response, array & $app) {
        
    }

    function get_param(array & $request, array & $response, array & $app) {
        $response = load_model('op/PurAdviseModel')->get_param();
    }

    function save_param(array & $request, array & $response, array & $app) {
        $response = load_model('op/PurAdviseModel')->save_param($request);
    }

    function get_rate(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('op/PurAdviseModel')->get_rate($request['param']);
    }

}
