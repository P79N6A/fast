<?php

require_lib('util/web_util', true);

class wms_config {

    function do_list(array &$request, array &$response, array &$app) {
        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $response['lof_status'] = $lof_manage['lof_status'];
    }

    function del_list(array &$request, array &$response, array &$app) {
        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $response['lof_status'] = $lof_manage['lof_status'];
    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑wms配置', 'add' => '添加wms配置');
        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $response['lof_status'] = $lof_manage['lof_status'];

        $app['title'] = $title_arr[$app['scene']];
        $response['wms_store'] = array();

        $system = load_model('sys/WmsConfigModel')->get_select();
        $response['system'] = $system;

        $wms_config_if = isset($request['_id']) ? $request['_id'] : 0;
        $ret_store = load_model("sys/ShopStoreModel")->get_select_store($wms_config_if);
        $response['store'] = $ret_store['data'];
        $response['shop'] = load_model("base/ShopModel")->get_purview_shop();
        //wms类型
        $response['wms_sys_type'] = require_conf('wms/wms_sys_type')['qimen'];
        if ($app['scene'] == 'edit') {
            $ret = load_model('sys/WmsConfigModel')->get_by_id($request['_id']);
            $response['wms_system_code'] = $ret['data']['wms_system_code'];
            $response['wms_prefix'] = $ret['data']['wms_prefix'];
            if (!empty($ret['data']['wms_params'])) {
                $wms_key = json_decode($ret['data']['wms_params'], true);
                $ret['data'] = array_merge($ret['data'], $wms_key);
            }

            $ret_wms_store = load_model("sys/ShopStoreModel")->get_type_data($request['_id'], 1);
            $response['wms_store'] = $ret_wms_store['data'];
            $ret_wms_store = load_model("sys/ShopStoreModel")->get_type_data($request['_id'], 1, 0);
            $response['wms_shop'] = $ret_wms_store['data'];

            $response['form1_data_source'] = json_encode($ret['data']);
        }
        $response['app_scene'] = $app['scene'];
    }

    function get_wms_system(array &$request, array &$response, array &$app) {
        $params = require_conf('sys/wms_params');
        if (!isset($params[$request['wms_system_code']])) {
            $response['status'] = -1;
        } else {
            $conf = $params[$request['wms_system_code']];
            $ret = load_model('sys/WmsConfigModel')->get_wms_system($request['wms_config_id']);
            $wms_conf = require_conf('sys/wms');
            $conf_effect_inv_type = isset($wms_conf[$request['wms_system_code']]['effect_inv_type']) ? $wms_conf[$request['wms_system_code']]['effect_inv_type'] : 0;
            if (!empty($ret['data'])) {
                foreach ($conf as $key => &$val) {
                    $val['val'] = isset($ret['data'][$key]) ? $ret['data'][$key] : $val['val'];
                }
            }
            $response['status'] = 1;
            $response['data'] = $conf;
            $response['effect_inv_type'] = isset($ret['data']['effect_inv_type']) && $ret['data']['effect_inv_type'] <> '' ? $ret['data']['effect_inv_type'] : $conf_effect_inv_type;
            if ($request['wms_system_code'] == 'jdwms' || $request['wms_system_code'] == 'jdwmscloud') {
                $response['select_shop'] = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('jingdong');
            }
        }
        //$ret = load_model('sys/WmsConfigModel')->get_wms_system($request['wms_system']);
        //$response = $ret;
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/WmsConfigModel')->delete($request['id']);
        $ret = load_model('sys/ShopStoreModel')->delete_store_config($request['id'],1);
        exit_json_response($ret);
    }
    function do_new_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/WmsConfigModel')->do_new_delete($request);
        exit_json_response($ret);
    }
    function do_add(array &$request, array &$response, array &$app) {

        $ret = load_model('sys/WmsConfigModel')->add_info($request);
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        if (empty($request['item_sync'])) {
            $request['item_sync'] = 0;
        }
        $pattern = "/^([0-1][0-9]|[2][0-3])(:)([0-5][0-9])$/";
        if (isset($request['wms_cut_time']) && $request['wms_cut_time'] != '' && !preg_match($pattern, $request['wms_cut_time'])) {
            exit_json_response(-1, '', '时间格式为：17:00<br/><span style="color:red">注:（":"为英文字符）</span>');
        } else {
            $ret = load_model('sys/WmsConfigModel')->edit_info($request);
            exit_json_response($ret);
        }
    }

    function add_upload_goods(array &$request, array &$response, array &$app) {
        
    }

    function upload_file(array &$request, array &$response, array &$app) {
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
        set_uplaod($request, $response, $app);
    }

    function import_goods(array &$request, array &$response, array &$app) {
        
    }

    function do_import_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        $ret = load_model('sys/WmsConfigModel')->do_imoprt_goods($request['wms_config_id'], $file);
        $response = $ret;
    }

    function do_delete_goods(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('wms_config_id', 'barcode'));
        $ret = load_model('sys/WmsConfigModel')->do_delete_goods($params);
        exit_json_response($ret);
    }

    /**
     * iwms接口连通测试
     */
    function iwms_api_test(array &$request, array &$response, array &$app) {
        $api_url = $request['api_url'];
        if (stripos($api_url, 'http://') !== 0 && stripos($api_url, 'https://') !== 0) {
            $response = array('status' => -1, 'message' => 'URL地址格式错误，请重新填写！');
            exit_json_response($response);
        }

        $headers = array(
            "Content-Type:application/x-www-form-urlencoded",
        );
        require_lib('net/HttpClient');
        $h = new HttpClient();
        $h->newHandle('0', 'post', $api_url, $headers);
        $h->exec();

        $result = $h->responses();
        if (!isset($result['0'])) {
            $response = array('status' => -1, 'message' => '请求出错, 返回结果错误');
            exit_json_response($response);
        }

        $result = json_decode($result[0], TRUE);
        $status = 1;
        $msg = '接口测试成功';
        if ($result['flag'] != 'ACK') {
            $status = -1;
            $msg = '接口测试失败';
        }
        $response = array('status' => $status, 'message' => $msg);
        exit_json_response($response);
    }

}
