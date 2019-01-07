<?php

require_lib('util/web_util', true);

class kisdee_config {

    function do_list(array &$request, array &$response, array &$app) {

    }

    /**
     * 配置详情
     */
    function detail(array &$request, array &$response, array &$app) {
        //获取供选择的仓库
        $config_id = isset($request['_id']) ? $request['_id'] : 0;
        $ret_store = load_model("sys/ShopStoreModel")->get_select_store($config_id, 2);
        $response['sys_store'] = $ret_store['data'];

        $response['form_kis_data_source'] = '{}';
        if (in_array($app['scene'], array('edit', 'view'))) {
            $response['data'] = load_model('sys/KisdeeConfigModel')->get_config_edit_info($request['_id']);
            $response['form_kis_data_source'] = json_encode($response['data']);
            $store = load_model('sys/ShopStoreModel')->get_type_data($request['_id'], 2, 1);
            $response['kis_store'] = $store['data'];
        }

        $response['store_select'] = json_encode($response['sys_store']);
        $response['app_scene'] = $app['scene'];
    }

    /**
     * 添加配置
     */
    function do_add(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('config_name', 'online_time', 'kis_eid', 'AccountDB', 'kis_auth_token', 'kis_server_url', 'kis_netid', 'store'));
        $response = load_model('sys/KisdeeConfigModel')->opt_config($data, 'add');
    }

    /**
     * 编辑配置
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('config_id', 'config_name', 'online_time', 'kis_eid', 'AccountDB', 'kis_auth_token', 'kis_server_url', 'kis_netid', 'store'));
        $response = load_model('sys/KisdeeConfigModel')->opt_config($data, 'edit');
    }

    /**
     * 列表删除
     */
    function do_delete(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('sys/KisdeeConfigModel')->delete($request['id']);
    }

    /**
     * 更新启用状态
     */
    function update_active(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('sys/KisdeeConfigModel')->update_active($request['id'], $request['type']);
    }

    /**
     * 金蝶kis接口连通测试
     */
    function api_test(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('kis_eid', 'kis_auth_token'));
        $response = load_model('sys/KisdeeConfigModel')->api_test($params);
    }

}
