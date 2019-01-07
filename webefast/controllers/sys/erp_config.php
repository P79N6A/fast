<?php
require_lib ('util/web_util', true);
class erp_config {
    function do_list(array &$request, array &$response, array &$app) {

    }
    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑erp配置', 'add' => '添加erp配置');
        $app['title'] = $title_arr[$app['scene']];
        $config_id = isset($request['_id']) ? $request['_id'] : 0;
        $ret_store = load_model("sys/ShopStoreModel")->get_select_store($config_id,0);
        $response['store'] = $ret_store['data'];
        $ret_shop = load_model("sys/ShopStoreModel")->get_select_shop($config_id,0);
        $response['fx'] = load_model('base/CustomModel') -> get_purview_custom_select('pt_fx');
        $response['shop'] = $ret_shop['data'];
        $response['erp_type'] = 1;
        if ($app['scene'] == 'edit') {
            $ret = load_model('sys/ErpConfigModel')->get_by_id($request['_id']);
            $response['info'] = $ret['data'];
            $response['erp_type'] = $ret['data']['erp_type'];
            $auto_fill['erp_config_id'] = $ret['data']['erp_config_id'];
            $auto_fill['erp_config_name'] = $ret['data']['erp_config_name'];
            $auto_fill['online_time'] = $ret['data']['online_time'];
            if (!empty($ret['data']['erp_params'])) {
            	$erp_params = json_decode($ret['data']['erp_params'], true);
            	$response['erp_params'] = $erp_params;
            }
			$response['form1_data_source'] = json_encode($auto_fill);
			$shop = load_model('sys/ShopStoreModel')->get_type_data($request['_id'],0,0);
			$store = load_model('sys/ShopStoreModel')->get_type_data($request['_id'],0,1);
            $response['erp_fx'] = load_model('sys/ErpConfigModel')->get_by_pid($request['_id']);
			$response['erp_shop'] = $shop['data'];
			$response['erp_store'] = $store['data'];
        }
        $response['app_scene'] = $app['scene'];

        $response['shop_select'] = json_encode($response['shop']);
        $response['store_select'] = json_encode($response['store']);
        $response['fx_select'] = json_encode($response['fx']);

    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/ErpConfigModel')->delete($request['id']);
        $ret = load_model('sys/ShopStoreModel')->delete_store_config($request['id'],0);
        exit_json_response($ret);
    }

    /**
     * 清除erp缓存表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete_cache(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/ErpConfigModel')->delete_cache($request['id']);
        exit_json_response($ret);
    }


	function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('sys/ErpConfigModel')->update_active($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }
    
	function do_add(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/ErpConfigModel')->add_info($request);
        exit_json_response($ret);
    }
    
    
	function do_edit(array &$request, array &$response, array &$app) {
		$ret = load_model('sys/ErpConfigModel')->edit_info($request);
		exit_json_response($ret);
    }
    //接口测试
    function test(array &$request, array &$response, array &$app) {
    	$ret = load_model('sys/ErpConfigModel')->test($request);
    	exit_json_response($ret);
    }
    
}
