<?php

require_lib('util/web_util', true);

class role_profession {

    function do_list(array & $request, array & $response, array & $app) {
        //店铺权限是否开启
        $arr = array('shop_power');
        $arr_shop = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        //print_r($arr_shop);
        $response['power'] = isset($arr_shop['shop_power']) ? $arr_shop['shop_power'] : '';

        if (empty($request['role_code'])) {
            $app['fmt'] = 'json';
            $ret = array(
                'status' => -1,
                'data' => '',
                'message' => '角色代码不能为空'
            );
            $response = $ret;
        } else {
            $response['role_code'] = $request['role_code'];
        }
        $response['keyword'] = isset($request['keyword']) ? $request['keyword'] : '';

        $response['version_no'] = load_model('sys/SysAuthModel')->product_version_no();
        $role_model = load_model('sys/RoleModel');
        $role_id = $request['role_id'];
        $acl_role = $role_model->get_by_id($role_id);
        $response['role'] = $acl_role['data'];
    }

    function sensitive_list(array & $request, array & $response, array & $app) {
        $arr = array('sensitive_power');
        $arr_shop = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['power'] = isset($arr_shop['sensitive_power']) ? $arr_shop['sensitive_power'] : '';
        $response['role_code'] = isset($request['role_code']) ? $request['role_code'] : '';
        $response['select_role_code'] = $response['role_code'];
        $response['role_list'] = load_model('sys/SensitiveModel')->role_list();
        //$response['sensitive_list'] = load_model('sys/SensitiveModel')->get_by_page(array('role_code'=>$response['select_role_code']));
        $response['version_no'] = load_model('sys/SysAuthModel')->product_version_no();
        $role_model = load_model('sys/RoleModel');
        $role_id = $request['role_id'];
        $acl_role = $role_model->get_by_id($role_id);
        $response['role'] = $acl_role['data'];
    }
    
    function manage_price(array & $request, array & $response, array & $app) {
        $arr = array('manage_price');
        $response['version_no'] = load_model('sys/SysAuthModel')->product_version_no();
        $arr_shop = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['power'] = isset($arr_shop['manage_price']) ? $arr_shop['manage_price'] : '';
        $response['role_code'] = isset($request['role_code']) ? $request['role_code'] : '';
        $response['role_list'] = load_model('sys/RoleManagePriceModel')->get_list_by_role($request);
        $role_model = load_model('sys/RoleModel');
        $role_id = $request['role_id'];
        $acl_role = $role_model->get_by_id($role_id);
        $response['role'] = $acl_role['data'];
    }
    
    function manage_update_status(array & $request, array & $response, array & $app){
        $ret = load_model('sys/RoleManagePriceModel')->update_status($request);
        exit_json_response($ret);
    }
    
    function update_active(array &$request, array &$response, array &$app) {
    	$arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('sys/RoleManagePriceModel')->update_active($arr[$request['type']], $request['param_code']);
    	exit_json_response($ret);
    }

    function sensitive_do_list(array & $request, array & $response, array & $app) {

        $response['sensitive_list'] = load_model('sys/SensitiveModel')->get_by_page(array('role_code' => $request['role_code']));
    }

    function sensitive_save(array & $request, array & $response, array & $app) {
        //var_dump($request);exit;
        if (isset($request['ids'])) {
            $ret = load_model('sys/SensitiveModel')->save($request['ids'], $request['select_role_code']);
        } else {
            $ret = load_model('sys/SensitiveModel')->delete_role($request['select_role_code']);
        }
        exit_json_response($ret);
    }

    function store_list(array & $request, array & $response, array & $app) {
        //仓库权限是否开启
        $arr = array('store_power');
        $arr_shop = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['power'] = isset($arr_shop['store_power']) ? $arr_shop['store_power'] : '';
        $response['keyword'] = isset($request['keyword']) ? $request['keyword'] : '';
        $response['role_code'] = isset($request['role_code']) ? $request['role_code'] : '';
        $response['version_no'] = load_model('sys/SysAuthModel')->product_version_no();
        $role_model = load_model('sys/RoleModel');
        $role_id = $request['role_id'];
        $acl_role = $role_model->get_by_id($role_id);
        $response['role'] = $acl_role['data'];
    }

    function brand_list(array & $request, array & $response, array & $app) {
        //品牌权限是否开启
        $arr = array('brand_power');
        $arr_shop = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['power'] = isset($arr_shop['brand_power']) ? $arr_shop['brand_power'] : '';
        $response['keyword'] = isset($request['keyword']) ? $request['keyword'] : '';
        $response['role_code'] = isset($request['role_code']) ? $request['role_code'] : '';
        $response['version_no'] = load_model('sys/SysAuthModel')->product_version_no();
        $role_model = load_model('sys/RoleModel');
        $role_id = $request['role_id'];
        $acl_role = $role_model->get_by_id($role_id);
        $response['role'] = $acl_role['data'];
    }

    /*
     * 供应商权限控制
     */

    function supplier_list(array & $request, array & $response, array & $app) {
        $arr = array('supplier_power');
        $arr_supplier = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['power'] = isset($arr_supplier['supplier_power']) ? $arr_supplier['supplier_power'] : '';
        $response['keyword'] = isset($request['keyword']) ? $request['keyword'] : '';
        $response['role_code'] = isset($request['role_code']) ? $request['role_code'] : '';
        $response['version_no'] = load_model('sys/SysAuthModel')->product_version_no();
        $role_model = load_model('sys/RoleModel');
        $role_id = $request['role_id'];
        $acl_role = $role_model->get_by_id($role_id);
        $response['role'] = $acl_role['data'];
    }

    function role_remove(array & $request, array & $response, array & $app) {
        if (isset($request['role_code']) || !empty($request['role_code'])) {
            $ret = load_model('sys/RoleProfessionModel')->role_remove($request['role_code'], $request['profession_type'], $request['sel_shop_code']);
        } else {
            $ret = array('status' => '0', 'data' => 'false', 'message' => '角色代码不能为空');
        }
        exit_json_response($ret);
    }

    /**
     * 加入业务角色
     * */
    function role_add(array & $request, array & $response, array & $app) {
        //print_r($request);exit;
        if (isset($request['role_code']) || !empty($request['role_code'])) {
            $role_code = $request['role_code'];
            $ret = load_model('sys/RoleProfessionModel')->role_add($request['role_code'], $request['profession_type'], $request['sel_shop_code']);
        } else {
            $ret = array('status' => '0', 'data' => 'false', 'message' => '角色代码不能为空');
        }

        exit_json_response($ret);
    }
    function custom_list(array & $request, array & $response, array & $app) {
        //分销权限是否开启
        $arr = array('custom_power');
        $arr_shop = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['power'] = isset($arr_shop['custom_power']) ? $arr_shop['custom_power'] : '';
        $response['keyword'] = isset($request['keyword']) ? $request['keyword'] : '';
        $response['role_code'] = isset($request['role_code']) ? $request['role_code'] : '';
        $response['version_no'] = load_model('sys/SysAuthModel')->product_version_no();
        $role_model = load_model('sys/RoleModel');
        $role_id = $request['role_id'];
        $acl_role = $role_model->get_by_id($role_id);
        $response['role'] = $acl_role['data'];
    }
}
