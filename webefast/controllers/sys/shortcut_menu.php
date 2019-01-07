<?php
require_lib ('util/web_util', true);
class shortcut_menu {
    function do_list(array &$request, array &$response, array &$app) {
        
    }
    
    public function update_active(array &$request, array &$response, array &$app) {
        $ret = load_model('sys/ShortcutMenuModel')->update_active($request['action_code'], $request['type']);
		exit_json_response($ret);
    }
}

