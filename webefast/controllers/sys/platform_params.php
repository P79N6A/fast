<?php
require_lib ('util/web_util', true);
class platform_params {
	//系统参数配置
    function do_list(array &$request, array &$response, array &$app) {
    	
    	$arr = array(':sort' => "oms_taobao");
    	$res = load_model('sys/ParamsModel')->get_params($arr);
    	$response['order'] = $res;
    	//ERP
    	$arr = array(':sort' => "erp");
    	$res = load_model('sys/ParamsModel')->get_params($arr);
    	$response['erp'] = $res;
        $arr = array(':sort' => "oms_common");
    	$res = load_model('sys/ParamsModel')->get_params($arr);
        $response['common'] = $res;
        //京东
        $arr = array(':sort' => "oms_jingdong");
    	$res = load_model('sys/ParamsModel')->get_params($arr);
        $response['jingdong'] = $res;
        //AG
        $arr = array(':sort' => "ag");
        $res = load_model('sys/ParamsModel')->get_params($arr);
        $response['ag'] = $res;
        $response['ag'] = load_model('sys/ParamsModel')->array_sort($response['ag']); //排序三维数组
        //淘宝平台的店铺
        $response['taobao_shop'] = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao');
        //获取开启AG的店铺
        $response['taobao_ag_shop']=load_model('base/ShopModel')->get_taobao_ag_shop();
        //寺库
        $arr = array(':sort' => "oms_siku");
        $res = load_model('sys/ParamsModel')->get_params($arr);
        $response['siku'] = $res;

        //校验增值服务
        $ag_check = load_model('common/ServiceModel')->check_is_auth_by_value('taobao_ag_params');
        $response['ag_check'] = ($ag_check != true) ? 0 : 1;

    }
    
    
    function update_params(array &$request, array &$response, array &$app) {
    	/*
    	$where = "param_code = 'tmall_return' ";
    	$data = array('value'=> $request['tmall_return']);
    	$ret = load_model('sys/SysParamsModel')->update($data,$where);
    	
    	$where = "param_code = 'order_link' ";
    	$data = array('value'=> $request['order_link']);
    	$ret = load_model('sys/SysParamsModel')->update($data,$where);
    	
    	//订单标签
    	$where = "param_code = 'order_tag' ";
    	$data = array('value'=> $request['order_tag']);
    	$ret = load_model('sys/SysParamsModel')->update($data,$where);*/
    	$all_params = explode(',', $request['param_code_all']);
    	$check_status = 0;
        $check_cli_update = array('aligenius_sendgoods_cancel', 'aligenius_warehouse_update', 'aligenius_upload_check');
    	foreach ($all_params as $param_name) {
    		$where = "param_code = '{$param_name}' ";
    		$data = array('value'=> $request[$param_name]);
                if($param_name == 'aligenius_refunds_check') {
                    $check_status = $data['value'];
                }
                if($param_name == 'aligenius_upload_check') { // 是否开启审核参数
                    $data['value'] = $check_status == 1 ? $data['value'] : '0';
                }
    		$ret = load_model('sys/SysParamsModel')->update($data,$where);
                if(in_array($param_name, $check_cli_update)) { //开启/关闭定时任务
                    load_model('sys/SysParamsModel')->update_ag_cli($param_name, $request[$param_name]);
                }
    	}
    	//淘宝AG店铺
        if (isset($request['ag_shop'])) {
            load_model('base/ShopModel')->set_taobao_ag_shop($request['ag_shop']);
        }

        //ag启用，停用(自动服务停用启用)
        if (isset($request['aligenius_enable'])) {
            load_model('sys/SysParamsModel')->update_ag_info($request['aligenius_enable']);
        }
        exit_json_response($ret);
    }
    
}

