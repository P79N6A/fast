<?php

/*
 * 系统管理-公告信息
 */

class Notice {

    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建公告、编辑公告显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
            $title_arr = array('edit'=>'编辑公告', 'add'=>'新建公告');
            $app['title'] = $title_arr[$app['scene']];
            $ret = load_model('sys/NoticeModel')->get_by_id($request['_id']);
            $response['data'] = $ret['data'];
    }
    
    //编辑公告信息数据处理。
    function do_edit(array & $request, array & $response, array & $app) {
            $notice = get_array_vars($request, array('not_title', 'not_type','not_enddate','not_detail','not_cp_id','not_detail_url'));
            $notice['not_updateuser']=CTX()->get_session("user_id");
            $notice['not_updatedate']=date('Y-m-d H:i:s');
            $ret = load_model('sys/NoticeModel')->update($notice, $request['not_id']);
            exit_json_response($ret);
    }
    
    //添加公告信息数据处理。    
    function do_add(array & $request, array & $response, array & $app) {
            $notice = get_array_vars($request, array('not_title', 'not_type','not_enddate','not_detail','not_cp_id','not_detail_url'));
            $notice['not_createuser']=CTX()->get_session("user_id");
            $notice['not_createdate']=date('Y-m-d H:i:s');
            $notice['not_updateuser']=CTX()->get_session("user_id");
            $notice['not_updatedate']=date('Y-m-d H:i:s');
            $ret = load_model('sys/NoticeModel')->insert($notice);
            exit_json_response($ret);
    }
    
    //审核公告信息   
    function do_check(array & $request, array & $response, array & $app) {
        if (isset($request['not_id'])) {
            $notinfo['not_sh'] = "1";
            $notinfo['not_shuser'] = CTX()->get_session("user_id");
            $notinfo['not_shdate'] = date('Y-m-d H:i:s');
            $ret = load_model('sys/NoticeModel')->do_check_notice($notinfo,$request['not_id']);
            if($ret['status']){
                //load_model('basedata/RdsDataModel')->update_kh_data(0,0,'osp_notice');
            }
            exit_json_response($ret);
        }
    }
    //产品授权过期提醒
    function auth_expired_notice(array & $request, array & $response, array & $app) {
        $ret = load_model('sys/NoticeModel')->do_auth_expired_notice();
        exit_json_response(1);
    }
}