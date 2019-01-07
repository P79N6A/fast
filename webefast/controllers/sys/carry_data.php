<?php

require_lib('util/web_util', true);

class carry_data {

    function sell_record_list(array &$request, array &$response, array &$app) {
            $this->get_spec_rename($response);
    }

    function sell_return_list(array &$request, array &$response, array &$app) {
            $this->get_spec_rename($response);
    }

    function get_record_detail(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/CarrySeachModel')->get_record_detail($request['sell_record_code'], 'oms_sell_record');

        $response['rows'] = $ret['data'];
    }

    function get_return_detail(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/CarrySeachModel')->get_record_detail($request['sell_return_code'], 'oms_sell_return');

        $response['rows'] = $ret['data'];
    }
    private function get_spec_rename(array &$response) {
        //spec别名
        $arr = array('goods_spec1', 'goods_spec2');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
    }

}
