<?php

/*
 * 服务中心-提单管理-产品问题提单列表
 */

class productissue {

    //产品问题提单列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }

    //新建、编辑产品问题提单
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑问题提单', 'add' => '新建问题提单');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('servicenter/ProductissueModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
 
        $acceptstate = '';    //受理状态
        $denystatus = '';      //拒绝
        $researchstatus = '';    //研发介入
        $unablestatus = '';    //无法解决
        $requirestatus = '';    //转需求
        $passtatus = '';        //已解决
        $statusdata = array();
        if($response['data']['sue_status'] !=='1' ){
            $statusdata['acceptstate'] = "disabled=disabled";
        }
         if($response['data']['sue_status'] =='1' ){
            $statusdata['denystatus'] = "disabled=disabled";
             $statusdata['researchstatus'] = "disabled=disabled";
             $statusdata['unablestatus'] = "disabled=disabled";
             $statusdata['requirestatus'] = "disabled=disabled";
             $statusdata['passtatus'] = "disabled=disabled";
        }
        if($response['data']['sue_status'] =='2'|| $response['data']['sue_status'] =='4'||$response['data']['sue_status'] =='6'){
            $statusdata['acceptstate'] = "disabled=disabled";
            $statusdata['denystatus'] = "disabled=disabled";
            $statusdata['commu'] = "disabled=disabled";
            $statusdata['researchstatus'] = "disabled=disabled";
            $statusdata['unablestatus'] = "disabled=disabled";
            $statusdata['requirestatus'] = "disabled=disabled";
            $statusdata['passtatus'] = "disabled=disabled";
        }
         if($response['data']['sue_status'] =='5'){
            $statusdata['acceptstate'] = "disabled=disabled";
            $statusdata['researchstatus'] = "disabled=disabled";
        }
        $response['stateinfo'] = $statusdata;
    }

    //添加产品问题提单   
    function do_add(array & $request, array & $response, array & $app) {
        $issue = get_array_vars($request, array(
            'sue_kh_id',
            'sue_cp_id',
            'sue_pv_id',
            'sue_other_pv',
            'sue_kh_contact',
            'sue_kh_phone',
            'sue_submit_source',
            'sue_product_fun',
            'sue_product_url',
            'sue_title',
            'sue_detail',
        ));
        $issue['sue_user'] = CTX()->get_session("user_id");
        $issue['sue_status'] = '1';
        $issue['sue_submit_time'] = date('Y-m-d H:i:s');
        $ret = load_model('servicenter/ProductissueModel')->insert($issue,$request["file"]);
        exit_json_response($ret);
    }
    
    
    //编辑问题提单
    function do_edit(array & $request, array & $response, array & $app) {
        $issue = get_array_vars($request, array(
            'sue_kh_id',
            'sue_cp_id',
            'sue_pv_id',
            'sue_other_pv',
            'sue_kh_contact',
            'sue_kh_phone',
            'sue_submit_source',
            'sue_product_fun',
            'sue_product_url',
            'sue_title',
            'sue_detail',
        ));
        //$issue['sue_user'] = CTX()->get_session("user_id");
        //$issue['sue_submit_time'] = date('Y-m-d H:i:s');

        $ret = load_model('servicenter/ProductissueModel')->update($issue, $request['sue_number']);
        exit_json_response($ret);
    }

    //问题受理
    function do_issue_accept(array & $request, array & $response, array & $app) {
        $subdata =  array();
        $subdata['sue_status'] = '3';
        $subdata['sue_idea_user'] = CTX()->get_session("user_id");
        $subdata['sue_accept_time'] = date('Y-m-d H:i:s');
//      $ret = load_model('servicenter/ProductissueModel')->get_submiter($request['sue_number']); 
        $ret = load_model('servicenter/ProductissueModel')->update_accept_status($request['sue_number'],$subdata);
        exit_json_response($ret);
    }

    //问题拒绝
    function do_issue_deny(array & $request, array & $response, array & $app) {
        $denydata =  array();
        $denydata['sue_status'] = '2';
        $denydata['sue_idea_user'] = CTX()->get_session("user_id");
        $denydata['sue_accept_time'] = date('Y-m-d H:i:s');
        $denydata['sue_solve_time'] = date('Y-m-d H:i:s');
        $denydata['sue_idea'] = '已拒绝';
        $ret = load_model('servicenter/ProductissueModel')->update_deny_status($request['sue_number'],$denydata);
        exit_json_response($ret);
    }
    
    //显示问题无法解决显示
     function do_show_unable(array & $request, array & $response, array & $app) {
         $app['scene']="add";
         if(!empty($request['djbh'])){
            $ret['data']['sue_number'] = $request['djbh'];
            $ret['data']['type'] = "1";
        }
        $response['data'] = $ret['data'];
     }
     
     //显示问题已经解决页面显示
    function do_show_unable_pass(array & $request, array & $response, array & $app) {
        $app['scene'] = "add";
        $app["tpl"] = "servicenter/productissue_do_show_unable";
        if (!empty($request['djbh'])) {
            $ret['data']['sue_number'] = $request['djbh'];
            $ret['data']['type'] = "2";
        }
        $response['data'] = $ret['data'];
    }

    //显示问题已经解决页面显示
    function do_show_unable_deny(array & $request, array & $response, array & $app) {
        $app['scene'] = "add";
        $app["tpl"] = "servicenter/productissue_do_show_unable";
        if (!empty($request['djbh'])) {
            $ret['data']['sue_number'] = $request['djbh'];
            $ret['data']['type'] = "3";
        }
        $response['data'] = $ret['data'];
    }

    //问题无法解决、问题已经解决处理、问题拒绝
    function do_issueunable(array & $request, array & $response, array & $app) {
        $unabledata = get_array_vars($request, array(
            'sue_idea',
        ));
        $unabledata['sue_idea_user'] = CTX()->get_session("user_id");
        //$unabledata['sue_accept_time'] = date('Y-m-d H:i:s');
        $unabledata['sue_solve_time'] = date('Y-m-d H:i:s');
        if($request['type'] == 1){
             $unabledata['sue_status'] = '6';
        }elseif($request['type'] == 2){
             $unabledata['sue_status'] = '4';
        }else{
            $unabledata['sue_status'] = '2';
        }
        $ret = load_model('servicenter/ProductissueModel')->update_unable_status($request['sue_number'],$unabledata,$request['type']);
        exit_json_response($ret);
    }
    
     //显示研发介入人显示
     function do_show_research(array & $request, array & $response, array & $app) {
        $app['scene'] = "add";
        if(!empty($request['djbh'])){
            $ret['data']['sue_number'] = $request['djbh'];
        }
        $response['data'] = $ret['data'];
     }
     
    //问题拒绝、问题已经解决处理
    function do_research(array & $request, array & $response, array & $app) {
        $resdata = get_array_vars($request, array(
            'sue_research',
        ));
        $resdata['sue_idea_user'] = CTX()->get_session("user_id");
        $resdata['sue_accept_time'] = date('Y-m-d H:i:s');
        $resdata['sue_status'] = '5';
       
        $ret = load_model('servicenter/ProductissueModel')->update_research_status($request['sue_number'],$resdata);
        exit_json_response($ret);
    }
    
    
    //选择客户带出客户联系人和联系方式
    function get_clients_info(array & $request, array & $response, array & $app) {
        
         $ret = load_model('servicenter/ProductissueModel')->get_clients($request['sue_kh_id']);
         exit_json_response($ret);
    }
    
    //强制编辑
    function do_comedit(array & $request, array & $response, array & $app){
        $app['scene']="edit";
        $title_arr = array('edit' => '编辑问题提单', 'add' => '新建问题提单');
        $app['title'] = $title_arr[$app['scene']];
        $app["tpl"]="servicenter/productissue_detail";
        $ret = load_model('servicenter/ProductissueModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
        $response['edittype'] = "comedit";
    }
}
