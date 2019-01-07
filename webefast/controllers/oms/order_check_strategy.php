<?php

require_lib('util/web_util', true);

class order_check_strategy {

    function do_list(array &$request, array &$response, array &$app) {

    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '配置订单审核规则', 'add' => '添加订单审核规则');
        $app['title'] = $title_arr[$app['scene']];
    }

    function do_add_goods(array &$request, array &$response, array &$app) {
        $response = load_model('oms/OrderCheckStrategyDetailModel')->add_goods($request);
    }

    function goods_do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/OrderCheckStrategyDetailModel')->delete_goods($request['id']);
        exit_json_response($ret);
    }

    function goods_do_delete_all(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/OrderCheckStrategyDetailModel')->delete_all_goods('not_auto_confirm_with_goods');
        exit_json_response($ret);
    }

    function detail_shop(array &$request, array &$response, array &$app) {
        $response['shop'] = load_model('oms/OrderCheckStrategyModel')->get_shop_info();
        $response['shoped'] = load_model('oms/OrderCheckStrategyDetailModel')->get_shoped_info();
    }

    function shop_do_add(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/OrderCheckStrategyDetailModel')->shop_do_add($request);
        exit_json_response($ret);
    }

    function detail_time(array &$request, array &$response, array &$app) {
        $response['execut_time'] = load_model('oms/OrderCheckStrategyDetailModel')->detail_time();
    }

    function do_add_time(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/OrderCheckStrategyDetailModel')->time_do_add($request);
        exit_json_response($ret);
    }

    function protect_time(array &$request, array &$response, array &$app) {
        $response['protect_time'] = load_model('oms/OrderCheckStrategyDetailModel')->protect_time();
    }

    function protect_time_edit(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/OrderCheckStrategyDetailModel')->protect_time_edit($request);
        exit_json_response($ret);
    }

    function update_active(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/OrderCheckStrategyModel')->update_active($request['type'], $request['id']);
        exit_json_response($ret);
    }

    function detail_store(array &$request, array &$response, array &$app) {
        $response['store'] = load_model('base/StoreModel')->get_list();
        $response['stored'] = load_model('oms/OrderCheckStrategyDetailModel')->get_stored_info();
    }

    function store_do_add(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/OrderCheckStrategyDetailModel')->store_do_add($request);
        exit_json_response($ret);
    }

    /**
     * 订单限制金额范围设置页面
     */
    function detail_money(array &$request, array &$response, array &$app) {
        $response['data'] = load_model('oms/OrderCheckStrategyDetailModel')->get_strategy_detail('not_auto_confirm_with_money');
    }

    /**
     * 规则明细统一设置
     */
    function strategy_detail_set(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/OrderCheckStrategyDetailModel')->set_strategy_detail($request['strategy_code'], $request['param']);
        exit_json_response($ret);
    }

}
