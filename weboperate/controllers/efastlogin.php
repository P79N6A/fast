<?php
require_lib ( 'util/web_util', true );
class Efastlogin {
        
    function init(array & $request, array & $response, array & $app) {
        $app['tpl'] = 'efastlogin_init';
        $app['page']= 'null';
    }
    
    function login_efast(array & $request, array & $response, array & $app) {
    	$user = array();
    	$user['customer_code'] = $request['customer_code'];
    	$user['user_code'] = $request['user_code'];
    	$user['password'] = $request['password'];
        $response = load_model('sys/EfastloginModel')->login_efast($user);
    }

}