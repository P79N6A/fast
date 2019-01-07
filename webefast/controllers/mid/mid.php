<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class mid {

    function do_list(array & $request, array & $response, array & $app) {

        $response['service_data'] = load_model('mid/MidApiConfigModel')->get_service_arr();
        $response['user_id'] = CTX()->get_session('user_id');
    }

    function do_add(array & $request, array & $response, array & $app) {
        $ret = load_model('mid/MidApiConfigModel')->add_info($request);
        exit_json_response($ret);
    }

    function do_edit(array & $request, array & $response, array & $app) {
        $ret = load_model('mid/MidApiConfigModel')->edit_info($request);
        exit_json_response($ret);
    }
    function test_api(array & $request, array & $response, array & $app) {
        $ret = load_model('mid/MidApiConfigModel')->test_api($request);
        exit_json_response($ret);
    }
    
    
    function config_detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑mid配置', 'add' => '添加mid配置');
        $config_id = isset($request['_id']) ? $request['_id'] : 0;
        $ret_store = load_model("mid/MidApiConfigModel")->get_select($config_id, 1);
        $ret_shop = load_model("mid/MidApiConfigModel")->get_select($config_id,0);
        $ret_custom = load_model("mid/MidApiConfigModel")->get_select($config_id,2);
        $response['store'] = $ret_store['data'];
        $response['shop'] = $ret_shop['data'];
        $response['custom'] = $ret_custom['data'];
        if ($app['scene'] == 'add') {
            $response['info']['api_product'] = 'mes';
        }
        $response['erp_type'] = 1;
        if ($app['scene'] == 'edit') {
            $ret = load_model('mid/MidApiConfigModel')->get_by_page($request);
            $response['info'] = $ret['data']['data'][0];
            $response['erp_type'] =  $response['info']['erp_type'];
            $auto_fill['id'] = $ret['data']['data'][0]['id'];
            $auto_fill['api_name'] = $ret['data']['data'][0]['api_name'];
            $auto_fill['online_time'] = $ret['data']['data'][0]['online_time'];
            $auto_fill['mid_code'] = $ret['data']['data'][0]['mid_code'];
            $auto_fill['api_product_flg'] = $ret['data']['data'][0]['api_product'];
            if (!empty($ret['data']['data'][0]['api_param_json'])) {
                $erp_params = json_decode($ret['data']['data'][0]['api_param_json'], true);
                $response['api_param_json'] = $erp_params;
            }
            $response['form1_data_source'] = json_encode($auto_fill);
            $store = load_model('mid/MidApiConfigModel')->get_type_data($response['info']['mid_code'], 1);
            $shop = load_model('mid/MidApiConfigModel')->get_type_data($response['info']['mid_code'], 0);
            $custom = load_model('mid/MidApiConfigModel')->get_type_data($response['info']['mid_code'], 2);
            $response['mid_shop'] = $shop['data'];
            $response['mid_store'] = $store['data'];
            $response['mid_custom'] = $custom['data'];
            $response['is_edit_onlinetime'] = load_model('mid/MidApiConfigModel')->is_edit_onlinetime($response['info']['mid_code']);
        }
        $response['app_scene'] = $app['scene'];
        $response['service_data'] = load_model('mid/MidApiConfigModel')->get_service_arr();
    }

    function get_mid_system(array & $request, array & $response, array & $app) {
        $params = require_conf('mid/api_param');
        if (!isset($params[$request['system_code']])) {
            $response['status'] = -1;
        } else {
            $conf = $params[$request['system_code']];
            $ret = load_model('mid/MidApiConfigModel')->get_mid_system($request['config_id']);
            if (!empty($ret['data'])) {
                foreach ($conf['config'] as $key => &$val) {
                    $val['val'] = isset($ret['data'][$val['key']]) ? $ret['data'][$val['key']] : $val['val'];
                }
            }
            $response['status'] = 1;
            foreach ($conf['config'] as $value) {
                $data[$value['key']]['name'] = $value['name'];
                $data[$value['key']]['val'] = $value['val'];
            }
            $response['data'] = $data;
        }
    }

    function upload(array & $request, array & $response, array & $app) {

        $response = load_model('mid/MidOptModel')->opt_order($request['task_id'], 0);
    }

    function order_shipping(array & $request, array & $response, array & $app) {
        $response = load_model('mid/MidOptModel')->opt_order($request['task_id'], 2);
    }
    
    /**
     * 隐藏自动服务，用于生成新ERP上传的零售单据
     */
    function cli_sys_to_mid(array & $request, array & $response, array & $app) {
        load_model('mid/MidOptModel')->sys_to_mid();
        $response['status'] = 1;
    }
    
    function cli_upload(array & $request, array & $response, array & $app) {
        load_model('mid/MidOptModel')->mid_order_upload_all();
        $response['status'] = 1;
    }

    function cli_order_shipping(array & $request, array & $response, array & $app) {
        load_model('mid/MidOptModel')->mid_order_shipping_all();
        $response['status'] = 1;
    }
        function cli_sync_archive(array & $request, array & $response, array & $app) {
        load_model('mid/MidArchiveModel')->sync_archive();
        $response['status'] = 1;
    }
    function cli_sync_inv(array & $request, array & $response, array & $app) {
        load_model('mid/MidInvModel')->sync_inv();
        $response['status'] = 1;
    }
    
    /**
     * @todo 销售订单锁定库存上传到ERP
     */
    function cli_upload_lock_inv(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        load_model('mid/MidInvModel')->cli_upload_lock_inv();
        $response['status'] = 1;
    }
    
    function test(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        load_model('mid/MidInvModel')->get_no_upload_num('001', 'bserp2');
        $response['status'] = 1;
    }
}
