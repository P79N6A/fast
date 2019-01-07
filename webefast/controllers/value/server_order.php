<?php

/*
 * 增值服务订单业务控制器
 */
require_lib('util/web_util', true);
require_lib('apiclient/AlipaymClient', true);
require_lib('util/oms_util', true);
require_model('value/ValueServerModel', true);

class server_order {

    /**服务订购列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array &$request, array &$response, array &$app) {
        $response['kh_id'] = CTX()->saas->get_saas_key();
        //默认标签
        $response['tabs_type'] = isset($request['tabs_type']) ? $request['tabs_type'] : 'tabs_pay';
        $response['tabs_pay'] = ($response['tabs_type'] == 'tabs_pay') ? TRUE : FALSE;
        $response['tabs_remark'] = ($response['tabs_type'] == 'tabs_remark') ? TRUE : FALSE;
    }

    /**支付宝充值
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function order_ali_pay(array & $request, array & $response, array & $app) {
        $param = get_array_vars($request, array('id'));
        $param['kh_id'] = CTX()->saas->get_saas_key();
        $param['user_code'] = CTX()->get_session('user_code');
        $ret = load_model('value/ValueServerModel')->order_ali_pay($param);
        exit_json_response($ret);
    }


    /**处理充值结果
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function pay_return(array & $request, array & $response, array & $app) {
        $ret = load_model('value/ValueServerModel')->handle_info($request);
        if ($ret['status'] == 1) {
            echo 'success';
        } else {
            echo 'fail';
        }
        die;
    }

    /**支付完成后跳转的页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function skip_new_url(array & $request, array & $response, array & $app) {
        $present_url = load_model('value/ValueServerModel')->get_url();
        echo "<script>";
        echo "window.location.href='{$present_url}?app_act=value/server_order/recharge_success_jump'";
        echo "</script>";
        die;
    }

    /**立即支付
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function immediate_pay(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('kh_id', 'server_remind'));
        $params['user_code'] = CTX()->get_session('user_code');
        $ret = load_model('value/ValueServerModel')->immediate_pay($params);
        exit_json_response($ret);
    }


    //验证支付是否成功
    function check_pay_status(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('pay_out_trade_no'));
        $ret = load_model('value/ValueServerModel')->check_pay_status($params);
        exit_json_response($ret);
    }

    /*
     * 评价页面
     */
    function edit_remark(array &$request, array &$response, array &$app) {

    }

    /**添加评价
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function add_remark(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('id', 'remark','score'));
        $params['user_code'] = CTX()->get_session('user_code');
        $ret = load_model('value/ValueServerModel')->add_order_remark($params);
        exit_json_response($ret);
    }

    function recharge_success_jump(array & $request, array & $response, array & $app) {

    }


    /**
     * 获取订单明细
     */
    function get_detail_list_by_order_code(array &$request, array &$response, array &$app) {
        $data = load_model('value/ValueServerModel')->get_detail_list_by_order_code($request['order_code']);
        $response = array('rows' => $data);
    }


    function do_order_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('value/ValueServerModel')->do_order_delete($request['id']);
        exit_json_response($ret);
    }
    
    /**
     * 订单详情
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function view(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('id'));
        $ret = load_model('value/ValueServerModel')->get_order_info($params);
        $response['data'] = $ret['data'];
    }
    
    /**
     * 删除明细
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete_detail(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('val_num'));
        $params['user_code'] = CTX()->get_session('user_code');
        $ret = load_model('value/ValueServerModel')->do_delete_order_detail($params);
        exit_json_response($ret);
    }
    
    /**
     * 增加订单明细
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function add_deatil_action(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('id', 'data'));
        $params['user_code'] = CTX()->get_session('user_code');
        $ret = load_model('value/ValueServerModel')->add_deatil_action($params);
        exit_json_response($ret);
    }

    /**
     * 详情编辑
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function edit_order_action(array & $request, array & $response, array & $app) {
        $params['user_code'] = CTX()->get_session('user_code');
        $params['id'] = $request['parameterUrl']['id'];
        $params['val_desc'] = $request['parameter']['val_desc'];
        $ret = load_model('value/ValueServerModel')->edit_order_action($params);
        exit_json_response($ret);
    }

    /**
     * 页面无刷新
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_order_info(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('id'));
        $ret = load_model('value/ValueServerModel')->get_order_info($params);
        $response['data'] = $ret['data'];
    }

}