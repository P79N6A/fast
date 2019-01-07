<?php

require_lib('util/web_util', true);

class sap_config {

    function do_list(array &$request, array &$response, array &$app) {
        
    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑asp配置', 'add' => '添加asp配置');
        $app['title'] = $title_arr[$app['scene']];
        $config_id = isset($request['_id']) ? $request['_id'] : 0;
	$response['store'] =  load_model('base/StoreModel')->get_select();
        $ret_shop = load_model("sys/ShopStoreModel")->get_select_shop($config_id,0);
        $response['shop'] = load_model('base/ShopModel')->get_purview_shop();
        if ($app['scene'] == 'edit') {
            $ret = load_model('sys/SapConfigModel')->get_by_id($request['_id']);
            $response['info'] = $ret['data'];
            $auto_fill['sap_config_id'] = $ret['data']['sap_config_id'];
            $auto_fill['sap_config_name'] = $ret['data']['sap_config_name'];
            $auto_fill['online_time'] = $ret['data']['online_time'];
            $auto_fill['sap_address'] = $ret['data']['sap_address'];
            $auto_fill['instance_number'] = $ret['data']['instance_number'];
            $auto_fill['client'] = $ret['data']['client'];
            $auto_fill['account'] = $ret['data']['account'];
            $auto_fill['password'] = $ret['data']['password'];
            $auto_fill['efast_store_code'] = $ret['data']['efast_store_code'];
            $shop = load_model('sys/ShopStoreModel')->get_type_data($request['_id'],3,0);
            $store = load_model('sys/ShopStoreModel')->get_type_data($request['_id'],3,1);
            $response['sap_shop'] = $shop['data'];
            $response['sap_store'] = $store['data'];
            
            $response['form1_data_source'] = json_encode($auto_fill);
        }
        $response['app_scene'] = $app['scene'];
    }

    function do_add(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/SapConfigModel')->insert($request);
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('sap_config_name','online_time','sap_address','instance_number','client','account','password','shop','store'));	
        $ret = load_model('sys/SapConfigModel')->update($data,$request['sap_config_id']);
        exit_json_response($ret);
    }
    
    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/SapConfigModel')->delete($request['id']);
        exit_json_response($ret);
    }
}
