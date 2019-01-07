<?php
class Soonorder {
    
    function show_order(array & $request, array & $response, array & $app) {
        //订购判断客户信息是否审核
        $khcode = CTX()->get_session('kh_code');
        if(empty($khcode)){
           CTX()->redirect('index/do_index');   //跳转到首页
           exit;
        }
        $khdata = load_model('product/SoonbuyModel')->get_clientinfo($khcode);
        if(empty($khdata['kh_verify_status'])){
           //exit_json_response(array('status' => '-1', 'data' => '', 'message' => '客户未审核'));   
           CTX()->redirect('product/soonorder/show_remind');   //跳转到未审核的提醒页面
           exit;
        }
        $cpid=$request['cpid'];
        if(empty($cpid)){
            $cpid="21";  //默认为eFAST5
        }
        $plan = load_model('product/SoonbuyModel')->get_planprice($cpid);
        $response['data'] = $plan;
    }
    
    //客户未审核提醒页面
    function show_remind(array & $request, array & $response, array & $app) {
        $khcode = CTX()->get_session('kh_code');
        if(empty($khcode)){
           CTX()->redirect('index/do_index');   //跳转到首页
           exit;
        }
    }
    
    //通过条件筛选报价模版
    function get_planprice_by(array & $request, array & $response, array & $app) {
        $pro_product_version =$request['pro_product_version'];  //产品版本
        $pro_st_id =$request['pro_st_id'];  //购买类型
        $pro_cp_id =$request['pro_cp_id'];  //购买产品
        if(!empty($pro_product_version)){
           $ret = load_model('product/SoonbuyModel')->get_planprice_mby($pro_product_version,$pro_st_id,$pro_cp_id);
           exit_json_response($ret);
        }
    }
    
    //获取报价模版
    function get_planprice_one(array & $request, array & $response, array & $app) {
        $pro_price_id =$request['pro_price_id'];  //报价ID
        if(!empty($pro_price_id)){
           $ret = load_model('product/SoonbuyModel')->get_planprice_mone($pro_price_id);
           exit_json_response($ret);
        }
    }
}