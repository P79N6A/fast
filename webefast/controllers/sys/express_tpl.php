<?php

require_lib('util/web_util', true);
require_model('sys/PrintTemplatesModel');

class express_tpl {

    function do_list(array &$request, array &$response, array &$app) {
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('clodop_print'));
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) && !empty($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑快递单模板', 'add' => '添加快递单模板');
        $app['title'] = $title_arr[$app['scene']];
        if ($app['scene'] == 'edit') {
            $ret = load_model('sys/ExpressTplModel')->get_by_id($request['_id']);
        }
        $response['data'] = $ret['data'];
    }

    function do_delete(array &$request, array &$response, array &$app) {
        if ($request['is_buildin'] == 1) {
            $ret = array('status' => -1, 'message' => "系统默认模板，不允许删除");
        } else {
            $ret = load_model('sys/ExpressTplModel')->delete($request['id'], $request['is_buildin']);
        }
        exit_json_response($ret);
    }

    function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('sys/SmsTplModel')->update_active($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }

    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('tpl_type', 'tpl_name', 'is_active', 'sms_info', 'remark'));
        $ret = load_model('sys/SmsTplModel')->insert($data, $request['id']);
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('tpl_name', 'is_active', 'sms_info', 'remark'));

        $ret = load_model('sys/SmsTplModel')->update($data, $request['id']);
        exit_json_response($ret);
    }

    /**
     * @todo 选择获取菜鸟云打印模版的店铺，目前只限制淘宝店铺
     */
    public function shop_selector(array &$request, array &$response, array &$app) {
        $response = ds_get_select('shop', 2, array('sale_channel_code' => 'taobao'));
    }

    /**
     * @todo 选择获取菜鸟云打印模版
     */
    public function get_cloud_express_tpl(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/PrintTemplatesModel')->get_cloud_express_tpl($request['shop_code']);
        exit_json_response($ret);
    }

    /**
     * @todo 获取已经设置好的商品信息用于页面
     */
    public function edit_goods_info(array &$request, array &$response, array &$app) {
        $response = load_model('sys/PrintTemplatesModel')->get_goods_info_tpl($request['_id']);
    }

    /**
     * @todo 保存设置的商品信息
     */
    public function save_goods_info(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('single_goods_a_line', 'selected_goods_info', 'input_goods_info', 'print_templates_id'));
        $ret = load_model('sys/PrintTemplatesModel')->save_goods_info($params);
        exit_json_response($ret);
    }

    /**
     * @todo 获取已经设置好的商品信息用于页面
     */
    public function edit_clodop(array &$request, array &$response, array &$app) {
        $m = new PrintTemplatesModel();
        $ret = $m->get_row_by_id(array('print_templates_id' => $request['print_templates_id']));
        $response['record'] = $ret['data'];
        if ($ret['data']['company_code'] == 'SF') {
            $m->variables = array_merge_recursive($m->variables, $m->sf_rm_variables);
        }
        $response['variables'] = $m->variables;
        $response['variables_all'] = array_merge($m->variables['delivery'], $m->variables['receiving'], $m->variables['detail'], $m->variables['record']);
        if (in_array($ret['data']['print_templates_code'],array('alpha','alpha_sf'))) {
            $response['variables_all'] = array_merge($response['variables_all'],$m->jd_express_info);
        }
        $response['record_type'] = empty($request['record_type']) ? 100 : $request['record_type']; //默认模板
        // 偏移量
        preg_match('/LODOP\.PRINT_INITA\("([^"]+)"\,"([^"]+)"\,\d+\,\d+\,"[^"]*"\);/', $response['record']['template_body'], $matches);
        $response['record']['offset_top'] = isset($matches[1]) ? $matches[1] : '';
        $response['record']['offset_left'] = isset($matches[2]) ? $matches[2] : '';

        // 宽高
        preg_match('/LODOP\.SET_PRINT_PAGESIZE\(\d+\,(\d+)\,(\d+)\,"[^"]*"\);/', $response['record']['template_body'], $matches);
        $response['record']['paper_width'] = isset($matches[1]) ? $matches[1] : '';
        $response['record']['paper_height'] = isset($matches[2]) ? $matches[2] : '';

        $response['record_index'] = empty($request['record_index']) ? $response['record']['print_templates_id'] : $request['record_index'];

        $response['record']['template_body'] = $m->printCastContent($response['record']['template_body'], $response['variables_all']);

        $response['preview_data'] = $m->printCastContent($response['record']['template_body'], $response['variables_all'], 'printCastContent_ToVar');


        if (!empty($response['record']['template_val'])) {
            $response['template_val'] = json_decode($response['record']['template_val'], true);
        } else {
            $response['template_val'] = $m->tpl_val;
        }


        if (empty($response['record']['template_body_default'])) {
            $response['record']['template_body_default'] = $response['record']['template_body'];
        }
//        var_dump($response);exit;
    }

}
