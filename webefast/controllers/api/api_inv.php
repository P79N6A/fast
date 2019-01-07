<?php
require_lib('util/web_util', true);
require_lib('util/taobao_util', true);

class api_inv{
	function update_shop_inv(array & $request, array & $response, array & $app) {
		$app['fmt'] = 'json';
                if(!isset($request['shop_code'] )){
                    $request['shop_code'] ='shopping_attb';//测试
                }
                load_model('api/BaseInvModel')->update_shop_sku($request['shop_code']);
                $response = array('status'=>1,'message'=>'run is ok');
	}
        
        function  update_inv(array & $request, array & $response, array & $app) {
                load_model('api/BaseInvModel')->update_all_shop();
                $response = array('status'=>1,'message'=>'run is ok');
        }
        
        function update_inv_increment(array & $request, array & $response, array & $app){
                  load_model('api/BaseInvModel')->update_inv_shop();
                $response = array('status'=>1,'message'=>'run is ok');
        }
        
}