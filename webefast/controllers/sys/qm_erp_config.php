<?php
require_lib('util/web_util', true);

class qm_erp_config {

    /**
     * 配置列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array &$request, array &$response, array &$app) {

    }

    /**
     * 配置详情
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function detail(array &$request, array &$response, array &$app) {
        $config_id = isset($request['_id']) ? $request['_id'] : 0;
        //其他配置没有选过的系统店铺
        $ret_shop = load_model("sys/ShopStoreModel")->get_select_shop($config_id, 4);
        $response['sys_shop'] = $ret_shop['data'];
        //其他配置没有选过的系统仓库
        $ret_store = load_model("sys/ShopStoreModel")->get_select_store($config_id, 4);
        $response['sys_store'] = $ret_store['data'];
        //获取系统分销商
        $response['sys_fx'] =  load_model('base/CustomModel')->get_purview_custom_select('pt_fx');;

        $obj = load_model('sys/QmErpConfigModel');
        $response['outside_fx'] = array();
        $response['outside_store'] = array();
        $response['form_data_source'] = '{}';
        if($app['scene'] == 'edit'){
            //获取配置信息

            $ret_config = $obj->get_by_id($config_id);
            $config_info = $ret_config['data'];
            $response['config_data'] = $config_info;
            $response['form_data_source'] = json_encode($config_info);

            //获取外部分销商档案
            $response['outside_fx'] = $obj->get_custom_by_id($config_info['customer_id']);
            //获取已绑定的分销商
            $response['qm_fx'] = $obj->get_custom_by_config_id($config_id);

            //获取已绑定的店铺
            $qm_shop= load_model('sys/ShopStoreModel')->get_type_data($config_id, 4, 0);
            $response['qm_shop'] = $qm_shop['data'];

            //获取外部仓库档案
            $response['outside_store'] = $obj->get_store_by_id($config_info['customer_id']);
            //获取已绑定的仓库
            $qm_store= load_model('sys/ShopStoreModel')->get_type_data($config_id, 4, 1);
            $response['qm_store'] = $qm_store['data'];
        }

        //用于页面新增效果
        $response['store_select'] = json_encode($response['sys_store']);
        $response['outside_store_select'] = json_encode($response['outside_store']);

        $response['fx_select'] = json_encode($response['sys_fx']);
        $response['outside_fx_select'] = json_encode($response['outside_fx']);

        $response['shop_select'] = json_encode($response['sys_shop']);
        $response['app_scene'] = $app['scene'];
       // dump($response);exit;
    }


    /**
     * 删除
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/QmErpConfigModel')->do_delete($request['id']);
        exit_json_response($ret);
    }

    /**
     * 接口测试
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function api_test(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('target_key','customer_id'));
        $response = load_model('sys/QmErpConfigModel')->api_test($params);
    }


    /**
     * 新增配置
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_add(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array( 'qm_erp_config_name','online_time', 'target_key', 'customer_id', 'shop', 'store', 'fx', 'item_infos_download', 'manage_stock', 'trade_sync', 'qm_erp_system'));
        $ret = load_model('sys/QmErpConfigModel')->opt_config($data, 'add');
        exit_json_response($ret);
    }

    /**
     * 编辑
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('qm_erp_config_id','qm_erp_config_name','online_time', 'target_key', 'customer_id', 'shop', 'store', 'fx', 'item_infos_download', 'manage_stock', 'trade_sync', 'qm_erp_system'));
        $ret = load_model('sys/QmErpConfigModel')->opt_config($data, 'edit');
        exit_json_response($ret);
    }


    /**
     * 获取外部仓库
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_outside_store_api(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('target_key', 'customer_id'));
        $ret = load_model('sys/QmErpConfigModel')->get_outside_store($data);
        exit_json_response($ret);
    }

    /**
     * 获取外部分销商接口
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_outside_costomer_api(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('target_key', 'customer_id'));
        $ret = load_model('sys/QmErpConfigModel')->get_outside_customer($data);
        exit_json_response($ret);
    }


}
