<?php

require_lib('util/web_util', true);

class wms_express_config {
    public function do_list(array &$request,array &$response,array &$app){
        $system = load_model('sys/WmsConfigModel')->get_data_list();
        $response['express'] = $this->get_express_list();
        $response['system'] = $system;
    }
    public function get_express_list(){
        $express = ds_get_select('express');
        foreach ($express as &$val){
            $val['express_name'] = $val['express_name'].'('.$val['express_code'].')';
        }
        return $express;
    }
    public function detail(array &$request,array &$response,array &$app){
        $response['system_arr'] = load_model('sys/WmsConfigModel')->get_data_list();
        $response['express_code_arr'] = $this->get_express_list();
        array_unshift($response['express_code_arr'],array('express_code'=>'','express_name'=>'请选择配送方式'));
        array_unshift($response['system_arr'],array('wms_config_id'=>'','wms_config_name'=>'请选择wms配置名称'));
        $config_info = load_model('sys/WmsExpressConfigModel')->get_record_by_id($request['_id']);
        $response['wms_config_id'] = isset($config_info['data']['wms_config_id']) ? $config_info['data']['wms_config_id'] : '';
        $response['express_code'] = isset($config_info['data']['express_code']) ? $config_info['data']['express_code'] : '';
        $response['out_express_code'] = isset($config_info['data']['out_express_code']) ? $config_info['data']['out_express_code'] : '';
        $response['desc'] = isset($config_info['data']['desc']) ? $config_info['data']['desc'] : '';
        $response['wms_id'] = isset($config_info['data']['wms_id']) ? $config_info['data']['wms_id'] : '';
    }
    public function do_add(array &$request,array &$response,array &$app){
        $ret = load_model('sys/WmsExpressConfigModel')->add($request);
        exit_json_response($ret);
    }
    public function do_edit(array &$request,array &$response,array &$app){
        $ret = load_model('sys/WmsExpressConfigModel')->edit($request);
        exit_json_response($ret);
    }
    public function show_log(array &$request,array &$response,array &$app){

    }
}