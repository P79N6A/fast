<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class shipping {

    function do_list(array &$request, array &$response, array &$app) {
        
    }

    /**
     * 加载页签
     */
    function get_tab(array &$request, array &$response, array &$app) {
        $type = $request['type'];

        ob_start();
        $path = get_tpl_path('base/shipping/' . $type);
        include $path;
        $ret = ob_get_contents();
        ob_end_clean();
        die($ret);
    }

    /**
     * 详情页
     */
    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑配送方式', 'add' => '添加配送方式', 'view' => '查看配送方式');
        $app['title'] = $title_arr[$app['scene']];

        if ($app['scene'] != 'add') {
            $express_info = load_model('base/ShippingModel')->get_express_info($request['_id']);
            $response['form_data_source'] = json_encode($express_info);
            $response['data'] = $express_info;
        }
        $response['action'] = isset($request['action']) ? $request['action'] : '';
    }

    /**
     * 获取快递模板、快递公司、店铺选择
     */
    function get_data_select(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $action = "get_express_{$request['data_type']}_select";
        $template = load_model('base/ShippingModel')->$action($request['_type']);
        $response = format_bui($template);
        echo $response;
        exit;
    }

    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('express_code', 'express_name', 'company_code', 'print_type', 'pt_id', 'df_id', 'rm_id', 'status', 'remark'));
        $ret = load_model('base/ShippingModel')->insert($data);
        $response = $ret;
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('express_code', 'express_name', 'company_code', 'print_type', 'pt_id', 'df_id', 'rm_id', 'status', 'remark'));
        $ret = load_model('base/ShippingModel')->update($data, $request['express_id']);
        $response = $ret;
    }

    function do_edit_freight(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('base_weight', 'base_fee', 'per_weight', 'free_per_weight', 'per_fee', 'per_rule', 'free_fee', 'zk', 'remark'));
        $ret = load_model('base/ShippingModel')->update($data, $request['express_id']);
        $response = $ret;
    }

    function do_edit_shop(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('rm_shop_code'));
        $ret = load_model('base/ShippingModel')->update($data, $request['express_id']);
        $response = $ret;
    }
    
    function do_update_sign(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('sign_id'));
        $response = load_model('base/ShippingModel')->update($data, $request['express_id']);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/ShippingModel')->delete($request['express_id']);
        exit_json_response($ret);
    }

    /**
     * 编辑快递打印模版
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function edit_print(array & $request, array & $response, array & $app) {

        $app['tpl'] = 'base/shipping_edit_print';

        $ret = load_model('base/ShippingModel')->get_by_id($request['_id']);

        $response['data']['print'] = $ret['data'];

        $response['data']['print_vars'] = load_model('base/ShippingModel')->print_vars;

        $print_var = load_model('base/ShippingModel')->print_vars;

        $response['data']['print']['print'] = $this->printCastContent($response['data']['print']['print'], $print_var);
    }

    /**
     * 转换打印项内容，默认打印项文本内容转换为示例文本
     * @param $print
     * @param $print_var
     * @param null $callback
     * @return string
     */
    private function printCastContent($print, $print_var, $callback = null) {
        $print_var2 = $print_var;
        $print_line_arr = explode("\r\n", $print);
        $print_arr = array();

        $_prefix = 'LODOP.ADD_PRINT_TEXTA(';
        $_suffix = ");";
        $_start = strlen($_prefix);
        $_end = - strlen($_suffix);
        foreach ($print_line_arr as $line_key => $line_value) {
            if (empty($line_value) || strpos($line_value, $_prefix) === false || substr($line_value, 0, strlen($_prefix . '"_txt:')) == $_prefix . '"_txt:' // 自定义文本
            ) {
                $print_arr[] = $line_value;
                continue;
            }
            $_arr = explode(',', substr($line_value, $_start, $_end));
            $_k = substr($_arr[0], 1, -1);
//			$_k = explode('-', $_k)[0];
            $_k = explode('-', $_k);
            if ($callback == null) {
                $_arr[5] = !isset($print_var2[$_k]) ? "'{$_k}'" : '"' . $print_var2[$_k] . '"';
            } else {
                $this->$callback($_arr, $_k);
            }

            $print_arr[] = $_prefix . implode(',', $_arr) . $_suffix;
        }

        return implode("\r\n", $print_arr);
    }

    /**
     * 执行编辑快递打印模版
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_edit_print(array & $request, array & $response, array & $app) {
        $print = $request['print'];
        $express_id = $request['express_id'];

        $print_var = load_model('base/ShippingModel')->print_vars;
        $data['print'] = $this->printCastContent($print, $print_var, 'printCastContent_ToVar');

        $ret = load_model('base/ShippingModel')->update_by_id($data, $express_id);
        exit_json_response($ret);
    }

    /**
     * 将打印项文本内容转换为变量
     * @param unknown $_arr 单行打印代码
     */
    private function printCastContent_ToVar(&$_arr, $name) {
        $_arr[5] = 'c["' . $name . '"]';
    }

    /**
     * 修改打印机
     */
    function modify_printer(array & $request, array & $response, array & $app) {

        $data['printer_name'] = strip_tags($request['printer_name']);
        $express_id = strip_tags($request['express_id']);

        $ret = load_model('base/ShippingModel')->update_by_id($data, $express_id);
        exit_json_response($ret);
    }

    function get_print_code(array & $request, array & $response, array & $app) {
        $ret = load_model('base/ShippingModel')->get_by_id($request['express_id']);
        $print = $ret['data']['print'];

        $print_var = load_model('base/ShippingModel')->print_vars;
        $code = $this->printCastContent($print, $print_var, 'printCastContent_ToVar');

        $data = array(
            'record_code' => '2014000001',
            'money' => '234.00',
            'sender' => '张三',
            'sender_tel' => '0571-2876876',
            'sender_mobile' => '1567829171',
            'sender_shop_name' => '电脑之家',
            'sender_address' => '浙江省杭州市西湖路10号',
            'sender_zip' => '200101',
            'receiver_name' => '李四',
            'buy_name' => '五非',
            'receiver_tel' => '13472828961',
            'receiver_province' => '上海',
            'receiver_city' => '上海市',
            'receiver_district' => '浦东新区',
            'receiver_address' => '峨山路95弄2号楼5层',
            'receiver_zip_code' => '200100',
            'num' => '2',
            'buy_remark' => '买家备注111',
            'sell_remark' => '卖家备注222',
        );
        $code = 'var c = ' . json_encode($data) . ';' . $code;
        exit_json_response(array('status' => 1, 'data' => $code));
    }

    function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('base/ShippingModel')->update_active($arr[$request['type']], $request['express_id']);
        exit_json_response($ret);
    }

    function update_type(array &$request, array &$response, array &$app) {
        $ret = load_model('base/ShippingModel')->update_type($request['type'], $request['express_id']);
        exit_json_response($ret);
    }

    function update_tpl(array &$request, array &$response, array &$app) {
        $ret = load_model('base/ShippingModel')->update_tpl($request['type'], $request['express_id'], $request['value']);
        exit_json_response($ret);
    }

    /**
     * 获取快递，供BUI选择使用
     */
    function get_bui_select_shipping(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $parmas = get_array_vars($request, array('first_type', 'first_val'));
        $response = load_model('base/ShippingModel')->get_bui_select_shipping($parmas['first_type'], $parmas['first_val']);
    }

}
