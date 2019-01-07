<?php
require_lib ('util/web_util', true);
class cloud_express_tpl {
	//系统参数配置
    function do_list(array &$request, array &$response, array &$app) {
        
    }
    
    function get_cloud_express_tpl(array &$request, array &$response, array &$app){
        $response = load_model('sys/PrintTemplatesModel')->get_cloud_express_tpl();
    }
	
}

