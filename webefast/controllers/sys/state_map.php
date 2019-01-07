<?php
require_lib ('util/web_util', true);
class state_map {
    function do_list(array &$request, array &$response, array &$app) {
    	

    }
    
	function sys_state_list(array & $request, array & $response, array & $app) {
	
		$response['id'] = $request['id'];
		$response['link_name'] = $request['link_name'];
		$response['ES_frmId'] = $request['ES_frmId'];
		
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
        $data = get_array_vars($request, array('sys_state'));
        
        $ret = load_model('sys/StateMapModel')->update($data, $request['id']);
        
        exit_json_response($ret);
    }
    
}
