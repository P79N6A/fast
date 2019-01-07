<?php
require_lib('util/web_util', true);
class sale_channel {
    function do_list(array &$request, array &$response, array &$app) {
    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑销售平台', 'add' => '添加销售平台', 'view' => '查看销售平台');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('base/SaleChannelModel')->get_row(array('sale_channel_id'=>$request['_id']));
        $response['data'] = $ret['data'];

        $response['data']['is_active'] = isset($ret['data']['is_active'])?$ret['data']['is_active']:1;
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('sale_channel_code', 'sale_channel_name', 'is_system', 'is_active', 'remark'));
        $ret = load_model('base/SaleChannelModel')->update($data, $request['sale_channel_id']);
        exit_json_response($ret);
    }

    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('sale_channel_code', 'sale_channel_name', 'is_system', 'is_active', 'remark'));
        $ret = load_model('base/SaleChannelModel')->insert($data, $request['sale_channel_id']);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/SaleChannelModel')->delete($request['sale_channel_id']);
        exit_json_response($ret);
    }

    function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('base/SaleChannelModel')->update_active($arr[$request['type']], $request['sale_channel_id']);
        exit_json_response($ret);
    }
    
    /**
     * 获取销售平台树列表，供库存策略使用
     */
    function get_nodes(array & $request, array & $response, array & $app) {
        $response = load_model('base/SaleChannelModel')->get_nodes();
    }
    
    function get_erp_api_nodes (array & $request, array & $response, array & $app){
       $response = load_model('base/SaleChannelModel')->get_erp_api_nodes();
    }
}
