<?php

class Jdpdb{
    
        //版本列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    function detail(array & $request, array & $response, array & $app) {

//            $ret = load_model('products/ProductEditionModel')->get_by_id($request['_id']);
//            $response['data'] = $ret['data'];
    }
    
    function do_add(array & $request, array & $response, array & $app){

        $ret = load_model('products/ShopTbjdpDbModel')->insert($request);
        exit_json_response($ret);
    }
        
    function do_del(array & $request, array & $response, array & $app){

        $ret = load_model('products/ShopTbjdpDbModel')->del($request['id']);
        exit_json_response($ret);
    }
    
}