<?php
require_lib ('util/web_util', true);
class order_link {
    function do_list(array &$request, array &$response, array &$app) {
    	
    $b = Array();	
    foreach (require_conf('sys/order_link') as $key => $value) {
    	$b[]=Array($key,$value);
	}
    $b[]=Array('','请选择');		
    $response['link_state'] = $b;

    }
    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑短信模板', 'add' => '添加短信模板');
        $app['title'] = $title_arr[$app['scene']];
        if ($app['scene'] == 'edit') {
            $ret = load_model('sys/SmsTplModel')->get_by_id($request['_id']);
        }
        $response['data'] = $ret['data'];
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/SmsTplModel')->delete($request['id']);
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
    
}
