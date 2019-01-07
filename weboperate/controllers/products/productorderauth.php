<?php

/*
 * 产品中心-产品订购授权
 */
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
class productorderauth {
    
    //产品订购授权
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //产品中心-产品订购显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑平台', 'add'=>'新建平台');
                $app['title'] = $title_arr[$app['scene']];
                if($app['scene']=="edit"){
                    $app['tpl']="products/productorderauth_detail_edit";
                }
                if($app['scene']=="view"){
                    $app['tpl']="products/productorderauth_detail_show";
                }
                
		$ret = load_model('products/ProductorderauthModel')->get_by_id($request['_id']);
                $response['data'] = $ret['data'];
	}
        
        function update_server(array & $request, array & $response, array & $app) {
            $data = array('pra_serverpath'=>$request['pra_serverpath']);
            $response = load_model('products/ProductorderauthModel')->update($data, array('pra_id' =>$request['pra_id']));
     
        }
       function do_kh_list(array & $request, array & $response, array & $app) {
 
     
        }
        function switch_kh_program(array & $request, array & $response, array & $app) {
           
            $ret = load_model('products/ProductorderauthModel')->get_by_id( $request['pra_id']);
            $response['data'] = $ret['data'];
            
        }
        function do_switch_kh_program(array & $request, array & $response, array & $app) {
            $app['fmt'] = 'json';
            $response = load_model('products/ProductorderauthModel')->update_pra_program_version($request);
        }


    /**
     * 续费
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_renew(array & $request, array & $response, array & $app) {
        $ret = load_model('products/ProductorderauthModel')->get_by_id($request['pra_id']);
        $ret['data']['kh_name'] = oms_tb_val('osp_kehu', 'kh_name', array('kh_id' => $ret['data']['pra_kh_id']));
        $response['data'] = $ret['data'];
    }

    /**
     * 保存续费信息
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_renew_save(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('pra_id', 'pro_hire_limit', 'pro_dot_num', 'pro_real_price'));
        $ret = load_model('products/ProductorderauthModel')->do_renew_save($params['pra_id'], $params);
        exit_json_response($ret);
    }

}