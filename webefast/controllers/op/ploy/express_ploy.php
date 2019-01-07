<?php

/**
 * 企业版快递策略
 * @author WMH
 */
class express_ploy {

    /**
     * 策略列表
     */
    function do_list(array &$request, array &$response, array &$app) {
        $presell_priv = load_model('sys/SysParamsModel')->get_val_by_code('express_ploy');
        if ($presell_priv['express_ploy'] != 1) {
            $response['tips'] = "快递策略功能未开启，请先开启";
            $response['tips_name'] = '系统参数设置 > 运营 > 快递策略（新）';
            $response['tips_url'] = '?app_act=sys/params/do_list&page_no=op';
            $response['tab_name'] = '系统参数设置';
            $app['tpl'] = 'common/page_power';
        }
        $response['change_status_priv'] = load_model('sys/PrivilegeModel')->check_priv('op/ploy/express_ploy/update_ploy_active');
    }

    /**
     * 策略列表展开查看关联快递数据
     */
    function get_ploy_express(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = load_model('op/ploy/ExpressPloyExpModel')->get_express_by_page($request);
        $response = array('rows' => $data['data']['data']);
    }

    /**
     * 策略基本信息页面
     */
    function detail(array &$request, array &$response, array &$app) {
        $response['ploy_info'] = '{}';
        if ($app['scene'] != 'add') {
            $ploy_info = load_model('op/ploy/ExpressPloyModel')->get_ploy_info_by_code($request['ploy_code']);
            $response['ploy_info'] = json_encode($ploy_info, true);
        }
    }

    /**
     * 策略基本信息新增/编辑页面
     */
    function ploy_detail(array &$request, array &$response, array &$app) {
        $this->detail($request, $response, $app);
        $app['tpl'] = 'op/ploy/express_ploy_detail';
    }

    /**
     * 策略基本信息查看页面
     */
    function ploy_view(array &$request, array &$response, array &$app) {
        $this->detail($request, $response, $app);
        $app['tpl'] = 'op/ploy/express_ploy_detail';
    }

    /**
     * 策略基本信息新增
     */
    function do_add(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('ploy_name', 'shop_code', 'store_code', 'order_pay_type', 'default_express', 'min_freight_judge', 'send_adapt_ratio', 'adapt_days', 'order_status', 'order_num'));
        $response = load_model('op/ploy/ExpressPloyModel')->ploy_add($data);
    }

    /**
     * 策略基本信息编辑
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('ploy_code', 'ploy_name', 'shop_code', 'store_code', 'order_pay_type', 'default_express', 'min_freight_judge', 'send_adapt_ratio', 'adapt_days', 'order_status', 'order_num'));
        $response = load_model('op/ploy/ExpressPloyModel')->ploy_update($data);
    }

    /**
     * 更新策略状态
     */
    function update_ploy_active(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('ploy_code', 'active'));
        $response = load_model('op/ploy/ExpressPloyModel')->ploy_update_active($params);
    }

    /**
     * 策略删除
     */
    function ploy_delete(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('ploy_code'));
        $response = load_model('op/ploy/ExpressPloyModel')->ploy_delete($params['ploy_code']);
    }

    /**
     * 快递配置页面
     */
    function exp_list(array &$request, array &$response, array &$app) {
        $response = load_model('op/ploy/ExpressPloyModel')->exists_ploy($request['ploy_code']);
        $response['change_status_priv'] = load_model('sys/PrivilegeModel')->check_priv('op/ploy/express_ploy/update_ploy_active');
    }

    /**
     * 快递配置新增
     */
    function ploy_exp_add(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('ploy_code', 'express'));
        $response = load_model('op/ploy/ExpressPloyExpModel')->express_add($data);
    }

    /**
     * 更新快递启用状态
     */
    function update_express_active(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request['params'], array('ploy_express_id', 'ploy_code', 'express_code', 'express_name', 'active'));
        $response = load_model('op/ploy/ExpressPloyExpModel')->express_active_update($params);
    }

    /**
     * 快递配置删除
     */
    function ploy_exp_delete(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('ploy_express_id', 'ploy_code', 'express_code', 'express_name'));
        $response = load_model('op/ploy/ExpressPloyExpModel')->express_delete($params);
    }

    /**
     * 快递配置删除
     */
    function ploy_exp_edit(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('ploy_express_id', 'ploy_code', 'express_code', 'express_name', 'express_level', 'express_ratio'));
        $response = load_model('op/ploy/ExpressPloyExpModel')->express_edit($params);
    }

    /**
     * 可达区域及运费配置
     */
    function area_fare_set(array &$request, array &$response, array &$app) {
        $response = load_model('op/ploy/ExpressPloyModel')->exists_ploy($request['ploy_code']);
    }

    function get_nodes(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $express_set_id = empty($request['express_set_id']) ? 0 : $request['express_set_id'];
        $response = load_model('op/ploy/ExpressPloyExpSetModel')->get_child($request['id'], $express_set_id, $request['ploy_express_id']);
    }

    function get_freight(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        if (empty($request['express_set_id'])) {
            $response['rows'] = array('{}');
        } else {
            $response['rows'] = load_model('op/ploy/ExpressPloyExpSetModel')->get_freight($request['express_set_id']);
        }
    }

    function save_express_set(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('op/ploy/ExpressPloyExpSetModel')->save_express_set($request['params']);
    }
    
    function delete_express_set(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('op/ploy/ExpressPloyExpSetModel')->delete_express_set($request);
    }

    /**
     * 快递分布
     */
    function exp_census(array &$request, array &$response, array &$app) {
        
    }

    /**
     * 操作日志
     */
    function log(array &$request, array &$response, array &$app) {
        
    }

}
