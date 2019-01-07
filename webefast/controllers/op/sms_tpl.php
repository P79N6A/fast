<?php
require_lib ('util/web_util', true);
require_lib('util/oms_util', true);
class sms_tpl {
    /**
     * 短信模板列表页
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array &$request, array &$response, array &$app) {
        $SmsTplModel = load_model('op/SmsTplModel');
        $sms_tpl_type = array_merge(array(''=>'请选择'), $SmsTplModel->sms_tpl_type);
        $response['select']['sms_tpl_type'] = array_from_dict($sms_tpl_type);
        //获取权限
        $priv_params = array(
            'op/sms_tpl/detail#scene=add',//新增
            'op/sms_tpl/detail#scene=edit',//编辑
            'op/sms_tpl/do_preview',//预览
            'op/sms_tpl/do_delete',//删除
        );
        foreach ($priv_params as $val) {
            $response['priv'][$val] = load_model('sys/PrivilegeModel')->check_priv($val);
        }
    }
    /**
     * 详情编辑页(新增和编辑共用)
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑短信模板', 'add' => '新增短信模板');
        $app['title'] = $title_arr[$app['scene']];
        $SmsTplModel = load_model('op/SmsTplModel');
        if ($app['scene'] == 'edit') {
            $ret = $SmsTplModel->get_by_id($request['_id']);
        }
        $response['data'] = isset($ret['data'])? $ret['data']: array();
        $sms_tpl_type = $SmsTplModel->sms_tpl_type;
        $response['select']['sms_tpl_type'] = array_from_dict($sms_tpl_type);
        $sms_tpl_var = $SmsTplModel->sms_tpl_var;
        $sms_tpl_var_str = '<div style="padding-top:10px;">模板变量：';
        foreach ($sms_tpl_var as $key => $val) {
            preg_match_all("/[^{@].*[^}]/", $key, $matches);
            $new_val = isset($matches[0][0]) ? $matches[0][0] : '';
            $sms_tpl_var_str .= '<input type="button" class="btn_tpl_var button button-primary" value="' . $new_val . '" char-value="' . $key . '"/> ';
        }
        $sms_tpl_var_str .= '</div>';
        $response['select']['sms_tpl_var_str'] = $sms_tpl_var_str;
        //获取权限
        $priv_params = array(
            'op/sms_tpl/do_preview',//预览
            'op/sms_tpl/opt_word_count',//预计算字数与条数
            'op/sms_tpl/opt_send_test',//发送测试
        );
        foreach ($priv_params as $val) {
            $response['priv'][$val] = load_model('sys/PrivilegeModel')->check_priv($val);
        }
    }
    /**
     * ajax 删除
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('op/SmsTplModel')->delete($request['id']);
        exit_json_response($ret);
    }
    /**
     * ajax 启用/停用
     * @param array $request
     * @param array $response
     * @param array $app
     */
	function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('op/SmsTplModel')->update_active($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }
    /**
     * ajax 新增模板
     * @param array $request
     * @param array $response
     * @param array $app
     */
	function do_add(array &$request, array &$response, array &$app) {
        $ret = load_model('op/SmsTplModel')->insert($request);
        exit_json_response($ret);
    }
    /**
     * ajax 编辑模板
     * @param array $request
     * @param array $response
     * @param array $app
     */
	function do_edit(array &$request, array &$response, array &$app) {
        $ret = load_model('op/SmsTplModel')->update($request);
        exit_json_response($ret);
    }
    /**
     * 发送测试页
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function send_test(array &$request, array &$response, array &$app) {
        
    }
    /**
     * ajax 发送测试
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function opt_send_test(array &$request, array &$response, array &$app) {
        $ret = load_model('op/SmsTplModel')->send_test($request);
        exit_json_response($ret);
    }
    /**
     * 选择已发货订单页
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function select_order(array &$request, array &$response, array &$app) {
        
    }
    /**
     * ajax 替换模板变量 
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function replace_tpl_var(array &$request, array &$response, array &$app) {
        //批量获取订单解密数据
        $data['row'] = $request['row'];
        $data = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_list($data);
        $sms_info_new = load_model('op/SmsTplModel')->replace_tpl_var($request['sms_info'], $data['row']);
        exit_json_response(1,$sms_info_new);
    }
}
