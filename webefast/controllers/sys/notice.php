<?php
require_lib ( 'util/web_util', true );
class Notice {
    
	function get_detail(array & $request, array & $response, array & $app) {
	    $ret = load_model('common/NoticeModel')->get_notice_detail();
            exit_json_response($ret);
	}
}
