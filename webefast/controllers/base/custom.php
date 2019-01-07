<?php

require_lib('util/web_util', true);

class custom {

    function do_list(array & $request, array & $response, array & $app) {
        //增值服务
        $response['service_custom'] = load_model('common/ServiceModel')->check_is_auth_by_value('CDKZ');
        $kh_id = CTX()->saas->get_saas_key();
        $response['kh_id'] = $kh_id;
    }

    function detail(array & $request, array & $response, array & $app) {
        //增值服务
        $response['service_custom'] = load_model('common/ServiceModel')->check_is_auth_by_value('CDKZ');
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('base/CustomModel')->get_by_id($request['_id']);
            $response['app_scene'] = 'edit';
            $shop_arr = load_model('base/ShopModel')->get_by_code($ret['data']['shop_code']);
            $response['shop_name'] = $shop_arr['data']['shop_name'];
        } else {
            $response['app_scene'] = 'add';
            $ret['data']['custom_code'] = load_model('base/CustomModel')->create_fast_bill_sn();
            //$ret['data']['custom_code'] = '999';
        }
        $response['settlement_method'] = load_model('base/CustomModel')->get_s1ettlement_method();
        $response['custom_type'] = load_model('base/CustomModel')->get_custom_type();
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
        //分销商账号密码
//        $user_data = load_model('sys/UserModel')->get_by_code($response['data']['user_code'],'password');
//        $response['data']['password'] = $user_data['password'];
        
        //取得国家数据
        $arr_area_country = load_model('base/TaobaoAreaModel')->get_area('0');
        $key = 0;
        $arr_country = array();
        foreach ($arr_area_country as $value) {
            $arr_country[$key][0] = $value['id'];
            $arr_country[$key][1] = $value['name'];
            $key++;
        }
        $response['area']['country'] = $arr_country;
        
        //取得省数据
        $arr_province[0][0] = '';
        $arr_province[0][1] = '请选择省';
        $arr_area_province = load_model('base/TaobaoAreaModel')->get_area($ret['data']['country']);
        $key = 1;
        foreach ($arr_area_province as $value) {
            $arr_province[$key][0] = $value['id'];
            $arr_province[$key][1] = $value['name'];
            $key++;
        }
        $response['area']['province'] = $arr_province;
        //取得市数据
        $arr_city[0][0] = '';
        $arr_city[0][1] = '请选择城市';
        if (isset($ret['data']['province'])) {
            $arr_area_city = load_model('base/TaobaoAreaModel')->get_area($ret['data']['province']);
            $key = 1;
            foreach ($arr_area_city as $value) {
                $arr_city[$key][0] = $value['id'];
                $arr_city[$key][1] = $value['name'];
                $key++;
            }
        }
        $response['area']['city'] = $arr_city;

        //取得区县数据
        $arr_district = array();
        $arr_district[0][0] = '';
        $arr_district[0][1] = '请选择区/县';
        if (isset($ret['data']['city'])) {
            $arr_area_district = load_model('base/TaobaoAreaModel')->get_area($ret['data']['city']);
            $key = 1;
            foreach ($arr_area_district as $value) {
                $arr_district[$key][0] = $value['id'];
                $arr_district[$key][1] = $value['name'];
                $key++;
            }
        }
        $response['area']['district'] = $arr_district;
        
        //获取分销商分类
        $grades = load_model('base/CustomGradesModel')->get_all_grades(1);
        $response['area']['grades'] = $grades;
    }

    function do_edit(array & $request, array & $response, array & $app) {
        //print_r($request);
        $spec1 = get_array_vars($request, array( 'custom_name','custom_type','custom_grade', 'contact_person','mobile', 'country','province','city','district', 'address','custom_price_type','custom_rebate','settlement_method','fixed_money','is_effective','tel'));
        if(isset($request['user_code']) && !empty($request['user_code'])) {
            $spec1['user_code'] = $request['user_code'];
        }
        if(isset($request['password']) && !empty($request['password'])) {
            $spec1['password'] = $request['password'];
        }
        $ret = load_model('base/CustomModel')->update($spec1, $request['custom_id']);
        exit_json_response($ret);
    }

    function do_add(array & $request, array & $response, array & $app) {
        $request['custom_code'] = trim($request['custom_code']);
        $spec1 = get_array_vars($request, array('custom_code', 'custom_name','custom_type','custom_grade', 'contact_person','mobile', 'country','province','city','district', 'address','custom_price_type','custom_rebate','settlement_method','fixed_money','is_effective','tel','user_code','password'));
        $ret = load_model('base/CustomModel')->insert($spec1);
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('base/CustomModel')->delete($request['custom_id']);
        exit_json_response($ret);
    }

    function add_user(array & $request, array & $response, array & $app){
    }

    function do_user_add(array & $request, array & $response, array & $app){
        $user = get_array_vars($request, array('user_code', 'user_name', 'phone','custom_code'));
        $user['login_type'] = 2;
        $ret = load_model('base/CustomModel')->set_login_user($user);
        exit_json_response($ret);
    }

    function reset_pwd(array & $request, array & $response, array & $app){
        $ret = load_model('sys/SysAuthModel')->check_is_auth();
        if($ret['status']<0){
           exit_json_response($ret);
        }
        $ret = load_model('base/CustomModel')->reset_pwd($request);
	exit_json_response($ret);
    }

    function select(array & $request, array & $response, array & $app){
        //获取仓库/品牌/年份/季节的下拉选框数据
        $request['is_select'] = isset($request['is_select']) ? $request['is_select'] : 0;
        $app['page'] = 'NULL';
    }

    function select_action(array & $request, array & $response, array & $app){
         $app['fmt'] = 'json';

        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $request['list_type'] = 'fx_custom_grades';
        $request['is_purview'] = 1;
        $result = load_model('base/CustomModel')->get_by_page($request);
        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    function register(array & $request, array & $response, array & $app){
        $response = load_model('base/CustomModel')->get_shop_info($request);
        $response['area']['province'] = load_model('base/TaobaoAreaModel')->get_area('1');
    }

    function do_register(array & $request, array & $response, array & $app){
        $user = get_array_vars($request, array('user_code', 'password', 'phone','company_name','user_name','kh_id','address','captcha','province','city','district'));
        $user['login_type'] = 2;
        $init_info['client_id'] = $user['kh_id'];      
        CTX()->saas->init_saas_client($init_info);
        $ret = load_model('base/CustomModel')->custom_register($user);
        exit_json_response($ret);
    }

    function register_search(array & $request, array & $response, array & $app){

    }

    function do_register_search(array & $request, array & $response, array & $app){
        $app['fmt'] = 'json';
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $ret = load_model('sys/UserModel')->register_search($request);
        $response['rows'] = $ret['data']['data'];
        $response['results'] = $ret['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    function review_list(array & $request, array & $response, array & $app){

    }

    //分销商审核
    function update_user_status(array & $request, array & $response, array & $app){
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('sys/UserModel')->update_status($arr[$request['type']],$request['user_id']);
        exit_json_response($ret);
    }
    function captcha(array & $request, array & $response, array & $app) {
        include ROOT_PATH .  CTX()->app_name . '/plugins/captcha/captcha.php';

        $code = (isset($request['code'])) ? $request['code'] : '';
        if ($code == 'code') {
            $gif = new GIF();
            echo $gif->init(90, 40, '26, 189, 157'); //参数依次是长、宽以及图片背景色
            die();
        }
    }

    function get_custom_by_code(array & $request, array & $response, array & $app){
        $app['fmt'] = 'json';
        $response = load_model('base/CustomModel')->get_by_code($request['custom_code']);
    }
    //停用、启用
    function is_effective(array & $request, array & $response, array & $app){
        $ret = load_model('base/CustomModel')->is_effective($request['custom_id'],$request['is_effective']);
        exit_json_response($ret);
    }
    //分销商修改基本信息
    function custom_do_edit(array & $request, array & $response, array & $app){
        $ret = load_model('base/CustomModel')->custom_do_edit($request['parameter'],$request['custom_id']);
        exit_json_response($ret);
    }
    //地址维护
    function address_do_list(array & $request, array & $response, array & $app) {
        $cusotm_model = load_model('base/CustomModel');
        $ret = $cusotm_model->get_by_id($request['_id']);
        $grade_data = load_model('base/CustomGradesModel')->get_by_code($ret['data']['custom_grade']);
        $settlement_price = $cusotm_model->custom_price_type[$ret['data']['custom_price_type']];
        $ret['data']['settlement_method_name'] = $cusotm_model->settlement_method[(int)$ret['data']['settlement_method']];
        $ret['data']['price_and_rebate'] = $settlement_price . ' / ' . $ret['data']['custom_rebate'];
        $ret['data']['custom_grade_name'] = $grade_data['grade_name'];
        $ret['data']['is_effective_str'] = $ret['data']['is_effective'] == 1 ? '启用' : '停用';
        $response['data'] = $ret['data'];
    }
    //添加地址
    function address_detail(array & $request, array & $response, array & $app) {
        $response['app_scene'] = $app['scene'];
        if($response['app_scene'] == 'edit') {
            $ret = load_model('base/CustomAddressModel')->get_by_data($request['addr_id']);
            $response['data'] = $ret;
        }
        
    }
    
    //添加地址
    function do_add_addr(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('country', 'province', 'city', 'district', 'address', 'zipcode', 'name', 'tel', 'home_tel', 'is_default'));
        $ret = load_model('base/CustomAddressModel')->do_add_addr($params, $request['custom_id']);
        exit_json_response($ret);
    }
    //修改地址
    function do_edit_addr(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('country', 'province', 'city', 'district', 'address', 'zipcode', 'name', 'tel', 'home_tel'));
        $ret = load_model('base/CustomAddressModel')->do_add_addr($params, $request['custom_id'], $request['custom_address_id']);
        exit_json_response($ret);
    }
    //删除地址
    function do_delete_address(array & $request, array & $response, array & $app) {
        $ret = load_model('base/CustomAddressModel')->do_delete_address($request['addr_id']);
        exit_json_response($ret);
    }
    //设为默认
    function do_set_default(array & $request, array & $response, array & $app) {
        $ret = load_model('base/CustomAddressModel')->do_set_default($request['addr_id']);
        exit_json_response($ret);
    }
    //固定运费按快递区分
    function express_money_detail(array & $request, array & $response, array & $app) {
        $cusotm_model = load_model('base/CustomModel');
        $ret = $cusotm_model->get_by_id($request['_id']);
        $ret['data']['settlement_method_name'] = $cusotm_model->settlement_method[(int)$ret['data']['settlement_method']];
        $response['express'] = ds_get_select('express');
        $response['custom_express'] = $cusotm_model->get_by_custom_express_data($ret['data']['custom_code']);
        $response['data'] = $ret['data'];
    }
    function save_express_money(array & $request, array & $response, array & $app) {
        $ret = load_model('base/CustomModel')->save_express_money($request['express'], $request['custom_code']);
        exit_json_response($ret);
    }
    
}
