<?php

/*
 * 增值服务业务控制器
 */
require_lib('util/web_util', true);

class value_add {

    /**服务订购列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function server_list(array &$request, array &$response, array &$app) {

    }

    /**添加购物车
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function add_shopping_cart(array &$request, array &$response, array &$app) {
        $request['user_code'] = CTX()->get_session('user_code');
        $request['kh_id'] = CTX()->saas->get_saas_key();
        $request['num'] = 1;
        $ret = load_model('value/ValueServerModel')->add_shopping_cart($request);
        exit_json_response($ret);
    }

    /**立即订购
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function add_server_order(array &$request, array &$response, array &$app) {
        $request['kh_id'] = CTX()->saas->get_saas_key();
        $request['user_code'] = CTX()->get_session('user_code');
        $ret = load_model('value/ValueServerModel')->add_server_order($request);
        exit_json_response($ret);
    }

    /**购物车页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function shopping_cart(array &$request, array &$response, array &$app) {
        $response['kh_id'] = CTX()->saas->get_saas_key();
        $response['user_code'] = CTX()->get_session('user_code');
        $response['kh_name'] = load_model('value/ValueServerModel')->get_kh_name();
    }

    /**读取购物车数据
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array|type
     */
    function component(array &$request, array &$response, array &$app) {
        //购物车数据
        $ret = load_model('value/ValueServerModel')->get_shopping_cart($request);
        exit_json_response($ret);
    }

    /**购物车删除
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array(
            'shopping_id',
        ));
        $ret = load_model('value/ValueServerModel')->delete_shopping_cart($params);
        exit_json_response($ret);
    }

    /**增值服务页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function server_view(array &$request, array &$response, array &$app) {

    }
    
    
    //增值弹框
    function server_select_view(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
    
    /**
     * 获取服务
     * @param array $request
     * @param array $response
     * @param array $app
     */
        function get_service_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('value/ValueServerModel')->get_service_goods($request);
        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

}
