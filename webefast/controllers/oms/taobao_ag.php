<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class taobao_ag {

    /**
     * ag列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array &$request, array &$response, array &$app) {
        $param_code = array(
            'aligenius_enable',
            'aligenius_sendgoods_cancel',
            'aligenius_refunds_check',
            'aligenius_warehouse_update',
        );
        $response['sys_params'] = load_model('sys/SysParamsModel')->get_val_by_code($param_code);

        $purview_sale_channel = array('taobao');
        $purview_shop_arr = array();
        foreach ($purview_sale_channel as $sale_channel_code) {
            $shop_info = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code($sale_channel_code);
            $purview_shop_arr = array_merge($purview_shop_arr, $shop_info);
        }
        $response['purview_shop'] = $purview_shop_arr;
    }

    /**
     * 定时器处理退单
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function aligenius_cli(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/TaobaoAgModel')->aligenius_cli();
        exit_json_response($ret);
    }
    
    /**
     * 定时器上传未发货订单取消结果
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function cli_aligenius_sendgoods_cancel(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/TaobaoAgModel')->cli_aligenius_sendgoods_cancel();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }
    
    /**
     * 定时器上传已发货退货入库结果
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function cli_aligenius_warehouse_update(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/TaobaoAgModel')->cli_aligenius_warehouse_update();
        $app['fmt'] = "json";
        $response['status'] = 1;        
    }
    
    /**
     * 定时器上传审核信息
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function cli_aligenius_upload_check(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/TaobaoAgModel')->cli_aligenius_upload_check();
        $app['fmt'] = "json";
        $response['status'] = 1;        
    }
    
    /**
     * 已处理页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function set_process(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/TaobaoAgModel')->get_ag_by_refund_id($request['refund_id']);
        $response = $ret;
    }

    /**
     * 设为已处理
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_set_process(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('push_val', 'refund_id', 'ag_record_type'));
        $ret = load_model('oms/TaobaoAgModel')->do_set_process($params);
        exit_json_response($ret);
    }

    /**
     * 强制完成
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function enforce_complete(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('refund_id'));
        $ret = load_model('oms/TaobaoAgModel')->enforce_complete($params['refund_id']);
        exit_json_response($ret);
    }

    /**
     *同步
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_sync(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('refund_id'));
        $ret = load_model('oms/TaobaoAgModel')->do_sync($params['refund_id']);
        exit_json_response($ret);
    }

    /**
     * 审核
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_check(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('refund_id'));
        $ret = load_model('oms/TaobaoAgModel')->do_check($params['refund_id']);
        exit_json_response($ret);
    }

    /**
     * 推送日志
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function push_log(array &$request, array &$response, array &$app) {
        $response['refund_id'] = $request['refund_id'];
    }
}
