<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
class mo_shop {

    function show_all(array & $request, array & $response, array & $app) {
        $ret = load_model('moniter/MoShopModel')->get_info_all();
        //$response = $ret['data'];
        $data[0] = array('title' => '接口授权', 'desc' => "共监控到店铺数{$ret['data']['shop_num']}家<br/>其中授权异常店铺数 <a href=\"javascript:link('shop_fail')\"> {$ret['data']['auth_fail_num']}</a>家，近一个月接口授权即将过期的店铺<a href=\"javascript:link('shop_expires')\">{$ret['data']['expires_num']}</a> 家");
        $data[0]['show_tip'] = ($ret['data']['auth_fail_num'] > 0) ? '<span class="red"> .</span> ' : '<span class="green"> .</span> ';
        $data[1] = array('title' => '交易漏单', 'show_tip', 'desc' => "共监控到店铺数{$ret['data']['shop_num']}家，其中近8小时存在漏单店铺数<a href=\"javascript:link('order')\"> {$ret['data']['order']}</a>家，剩余店铺均未发现异常  ");
        $data[1]['show_tip'] = ($ret['data']['order'] > 0) ? '<span class="red"> .</span> ' : '<span class="green"> .</span> ';
        $data[2] = array('title' => '交易转单', 'show_tip', 'desc' => "共监控到店铺数{$ret['data']['shop_all_num']} 家，其中存在交易转单失败店铺数<a href=\"javascript:link('trans_order')\"> {$ret['data']['trans_order']}</a> 家，剩余店铺均未发现异常");
        $data[2]['show_tip'] = ($ret['data']['trans_order'] > 0) ? '<span class="red"> .</span> ' : '<span class="green"> .</span> ';
        $data[3] = array('title' => '网单回写', 'show_tip', 'desc' => "共监控到店铺数{$ret['data']['shop_all_num']} 家，其中存在回写失败的店铺数<a href=\"javascript:link('order_send')\">{$ret['data']['order_send']}</a> 家，剩余店铺均未发现异常");
        $data[3]['show_tip'] = ($ret['data']['order_send'] > 0) ? '<span class="red"> .</span> ' : '<span class="green"> .</span> ';
        $response['data'] = $data;
    }

    function do_list(array & $request, array & $response, array & $app) {
        
    }

    function create_moniter(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $ret = load_model('moniter/MoShopModel')->create_moniter();
        $response['status'] = 1;
    }

    function sync_email(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $ret = load_model('moniter/MoShopModel')->sync_kh_notice_email();
        $response['status'] = 1;
    }

    function del_log(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $ret = load_model('moniter/MoKhModel')->del_sys_log_cli();
        $response['status'] = 1;
    }
    function get_kh_db_info(array & $request, array & $response, array & $app) {
        
    }
    
    function get_sql_data(array & $request, array & $response, array & $app){
           $response = load_model('moniter/MoKhModel')->select_data($request['sql']);
    }
    
    function do_order_list(array & $request, array & $response, array & $app){
        $source=ds_get_select('shop_platform',0);
        foreach($source as &$value){
            $value['pt_code']=  oms_tb_val('osp_platform', 'pt_code', array('pt_id'=>$value['pt_id']));
        }
        $response['data']['source']=$source;
    }
    
    function get_order_data(array & $request, array & $response, array & $app){
       $response =  load_model('moniter/MoKhModel')->get_11_data($request);
    }
    

}
