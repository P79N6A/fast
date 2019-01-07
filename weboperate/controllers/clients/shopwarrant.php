<?php
/**
 * 客户中心-店铺授权列表
 */
//require_lib ( 'util/web_util', true );
class shopwarrant {
//    public function __construct() {
//        //parent::__construct();
//        $this->mdl = load_model('sys/RdsModel_ex');
//    }
    
    /**
     * 系统参数列表
     */
    public function do_list(array & $request, array & $response,  array & $app){
    }
    
    
    function detail(array & $request, array & $response, array & $app) {
            $title_arr = array('edit'=>'编辑店铺授权', 'add'=>'新建店铺授权', 'view'=>'查看店铺授权');
            $app['title'] = $title_arr[$app['scene']];
            $ret = load_model('clients/ShopwarrantModel')->get_by_id($request['_id']);
            $response['data'] = $ret['data'];
    }
    
    //编辑产品平台KEY
    function do_edit(array & $request, array & $response, array & $app) {
            $shop_warrant = get_array_vars($request, array('sw_shop_session','sw_valid_date'));
            $ret = load_model('clients/ShopwarrantModel')->update($shop_warrant, $request['sw_id']);
            exit_json_response($ret);
    }
    //添加产品平台KEY    
    function do_add(array & $request, array & $response, array & $app) {
            $shop_warrant = get_array_vars($request, array('sw_cp_id','sw_kh_id','sw_pt_id','sw_sd_id','sw_shop_session','sw_valid_date'));
            $shop_warrant['sw_update_date']=date('Y-m-d H:i:s');
            $ret = load_model('clients/ShopwarrantModel')->insert($shop_warrant);
            exit_json_response($ret);
    }
    
    //推送业务库
    function  do_push(array & $request, array & $response, array & $app) {
            $sw_sd_id=$request['sw_sd_id'];
            $ret = load_model('busoperation/BusinessOperModel')->shopsession_push($sw_sd_id);
            exit_json_response($ret);
    }
}