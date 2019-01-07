<?php

/*
 * api接口-ecs档案
 */
require_lib("sign_util");

class Ali_ecs {

    function do_list(array & $request, array & $response, array & $app) {
        //验证签名
        $data=array();
        $data['key'] = $request['key'];               //key
        $data['secret'] = $request['secret'];         //secret
        $data['app_act'] = $app['path'].$app['grp'].'/'.$app['act'];               //method
        $data['timestamp'] =$request['timestamp'];    //时间戳(日期格式)
        $data['sign'] =$request['sign'];   
        $state=ver_sign($data);
        
        if($state['status']<0){
            exit_json_response($state);
        }
        $ret = load_model('api/Ali_ecsModel')->get_by_page($request);
        exit_json_response($ret);
    }
}