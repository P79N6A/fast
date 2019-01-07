<?php


//通灵商品库存查询
require_lib('util/web_util', true);

class goods_inv_tl{
    function do_list(array & $request, array & $response, array & $app){
        
    }
    
    //唯一吗库存数量统计
    function get_inv_summary(array & $request, array & $response, array & $app){
        //var_dump($request);die;
        $response = load_model('prm/GoodsUniqueCodeTLModel')->get_summary($request);
    }
}