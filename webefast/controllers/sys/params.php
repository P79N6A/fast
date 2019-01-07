<?php

require_lib('util/web_util', true);

class params {

    //系统参数配置
    function do_list(array &$request, array &$response, array &$app) {
        $arr = array(':sort' => "sys_set");
        $ret = load_model('sys/ParamsModel')->get_params($arr);
        $response['data'] = $ret;
        $arr = array('oms_notice');
        $response['oms_notice'] = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $arr = array(':sort' => "oms_property");
        $res = load_model('sys/ParamsModel')->get_params($arr);
        $response['order'] = $res;
        //配发货
        $arr = array(':sort' => "waves_property");
        $res = load_model('sys/ParamsModel')->get_params($arr);
        $response['waves'] = $res;

        //商品管理 value = {shop_code,value}
        $sql = "select shop_code,value from sys_kc_sync_cfg";
        $kc_sync_cfg_arr = ctx()->db->getAll($sql);
        $kc_sync_cfg_arr = load_model('util/ViewUtilModel')->get_map_arr($kc_sync_cfg_arr, 'shop_code', 0, 'value');
        $sql = "select shop_code,shop_name from base_shop";
        $db_shop = ctx()->db->getAll($sql);
        $shop_arr = array();
        foreach ($db_shop as $sub_arr) {
            $sub_arr['value'] = isset($kc_sync_cfg_arr[$sub_arr['shop_code']]) ? $kc_sync_cfg_arr[$sub_arr['shop_code']] : '100';
            $shop_arr[$sub_arr['shop_code']] = $sub_arr;
        }
        $response['kc_sync_cfg'] = $shop_arr;
        //二次开发
        $arr = array(':sort' => "second_develop");
        $res = load_model('sys/ParamsModel')->get_params($arr);
        $response['second'] = $res;
        //高级应用
        $arr = array(':sort' => "app");
        $res = load_model('sys/ParamsModel')->get_params($arr);
        $response['app'] = $res;
        $response['invoice_data'] = load_model('sys/ParamsModel')->get_param_set('default_invoice');//获取发票的data值
        //运营
        $arr = array(':sort' => "op");
        $res = load_model('sys/ParamsModel')->get_params($arr);
        $response['op'] = $res;
        //进销存
        $arr = array(':sort' => "pur");
        $res = load_model('sys/ParamsModel')->get_params($arr);
        $response['pur'] = $res;
        //财务
        $arr = array(':sort' => "finance");
        $res = load_model('sys/ParamsModel')->get_params($arr);
        $response['finance'] = $res;
        $response['page_no'] = $request['page_no'];
        //淘宝平台的店铺
        $response['shop'] = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao');
        array_unshift($response['shop'], array('shop_code' => '', 'shop_name' => '请选择'));
    }

    /**
     * 门店参数设置
     */
    function shop_do_list(array &$request, array &$response, array &$app) {
        $arr = array(':sort' => "cashier_set"); //门店收银
        $ret = load_model('sys/ParamsModel')->get_params($arr);
        $response['data'] = $ret;
    }

    function update_params(array &$request, array &$response, array &$app) {
        $param_code_arr = array('order_return_huo', 'fanance_money', 'oms_notice', 'off_deliver_time', 'order_link', 'tmall_return');
        if (isset($request['param_code_all'])) {
            $param_code_arr = explode(",", $request['param_code_all']);
        }
        foreach ($param_code_arr as $param_code) {
            if (isset($request[$param_code])) {
                $where = "param_code = '{$param_code}' ";
                $data = array('value' => $request[$param_code]);
                $ret = load_model('sys/SysParamsModel')->update($data, $where);
            }
        }

        exit_json_response($ret);
    }

    /**
     * 启用停用
     */
    function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('sys/ParamsModel')->update_active($arr[$request['type']], $request['param_code']);

        exit_json_response($ret);
    }

    function detail(array &$request, array &$response, array &$app) {
        switch ($request['sort']) {
            case "hytx":
                $response['shop'] = ds_get_select('shop', 0, array('sale_channel_code' => 'taobao'));
                $selected_shop = load_model('sys/SysParamsModel')->get_val_by_code('jiazhuang_shop');
                if (!empty($selected_shop)) {
                    $response['selected_shop'] = explode(',', $selected_shop['jiazhuang_shop']);
                } else {
                    $response['selected_shop'] = array();
                }
                $arr = array(':goods_property' => 'goods_property', ':goods_unique_code' => 'goods_unique_code', ':jiazhuang_trade' => 'jiazhuang_trade');
                break;
            default:
                $arr = array(':' . $request['sort'] => "{$request['sort']}", ':notice_set' => 'notice_set');
                break;
        }

        $ret = load_model('sys/ParamsModel')->get_params($arr);
        $ret1 = load_model('sys/ParamsModel')->get_parent();

        $response['moban'] = $ret1;
        $response['data'] = $ret;
    }

    function do_save(array &$request, array &$response, array &$app) {
        unset($request['fastappsid']);
        if (!empty($request)) {
            $ret = load_model('sys/ParamsModel')->save($request);
        }

        exit_json_response($ret);
    }

    function do_add(array &$request, array &$response, array &$app) {
        $user = get_array_vars($request, array('tpl_type', 'tpl_name', 'is_active', 'sms_info', 'remark'));
        $ret = load_model('sys/SmsTplModel')->insert($user);
        exit_json_response($ret);
    }

    function goods_do_save(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/SysParamsModel')->save_kc_sync_cfg($request);
        exit_json_response($ret);
    }

    /**
     * 行业特性设置
     */
    function industry(array &$request, array &$response, array &$app) {
        $response['data'] = load_model('sys/ParamsModel')->get_params_calss('industry');
        $response['activetab'] = isset($request['activetab']) ? $request['activetab'] : 'currency';
    }

    /**
     * 获取特性设置下的参数
     */
    function get_industry_tab(array &$request, array &$response, array &$app) {
        $type = $request['type'];
        if ($type === 'industry_currency') {
            $request['sort'] = 'hytx';
            $this->detail($request, $response, $app);
        } else {
            $params = load_model('sys/ParamsModel')->get_params([':' . $type => $type]);
            $data = $params[$type];
            $data = load_model('util/ViewUtilModel')->get_map_arr($data, 'param_code');
            if ($type === 'industry_clothing') {
                $layer_data = json_decode($data['size_layer']['data'], true);
                $data['size_layer']['issizelayer'] = empty($layer_data) ? 0 : 1;
            }

            $response['data'] = $data;
        }

        ob_start();
        $path = get_tpl_path('sys/params/' . $type);
        include $path;
        $ret = ob_get_contents();
        ob_end_clean();
        die($ret);
    }

    /**
     * 行业特性设置参数更新
     */
    function update_param_value(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, ['param_code', 'value']);
        $response = load_model('sys/ParamsModel')->update_param_value($params);
    }

}
