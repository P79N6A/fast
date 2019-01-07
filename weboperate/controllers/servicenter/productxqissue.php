<?php

/*
 * 服务中心-提单管理-产品需求提单列表
 */

class Productxqissue {
    
    //产品需求提单列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑,查看产品需求提单
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑需求提单', 'add' => '新建需求提单','view'=>'查看需求提单');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('servicenter/ProductxqissueModel')->get_by_id($request['_id']);
        $xqsue_difficulty_arr = load_model('servicenter/ProductxqissueModel')->xqsue_difficulty;
        $ret['data']['xqsue_difficulty_name'] = $xqsue_difficulty_arr[$ret['data']['xqsue_difficulty']];
        $response['data'] = $ret['data'];
        
        $acceptstate = '';    //受理状态
        $denystatus = '';      //拒绝
        $ideastatus = '';    //已审批
        $unablestatus = '';    //无法解决
        $passtatus = '';        //已解决
        $statusdata = array();
        if($response['data']['xqsue_status'] !=='1' ){
            $statusdata['acceptstate'] = "disabled=disabled";
            $statusdata['onlinestatus'] = "disabled=disabled";
        }
        if($response['data']['xqsue_status'] =='1' ){
            $statusdata['denystatus'] = "disabled=disabled";
            $statusdata['ideastatus'] = "disabled=disabled";
            $statusdata['unablestatus'] = "disabled=disabled";
            $statusdata['passtatus'] = "disabled=disabled";
            $statusdata['onlinestatus'] = "disabled=disabled";
        }
        if($response['data']['xqsue_status'] =='2' || $response['data']['xqsue_status'] =='5' || $response['data']['xqsue_status'] =='6' || $response['data']['xqsue_status'] =='7'){
            $statusdata['acceptstate'] = "disabled=disabled";
            $statusdata['denystatus'] = "disabled=disabled";
            $statusdata['ideastatus'] = "disabled=disabled";
            $statusdata['unablestatus'] = "disabled=disabled";
            $statusdata['passtatus'] = "disabled=disabled";
            $statusdata['onlinestatus'] = "disabled=disabled";
            if($response['data']['xqsue_status'] =='5'){
                $statusdata['onlinestatus'] = "";
            }
            
        }
        if($response['data']['xqsue_status'] =='4'){
            $statusdata['acceptstate'] = "disabled=disabled";
            $statusdata['denystatus'] = "disabled=disabled";
            $statusdata['ideastatus'] = "disabled=disabled";
            $statusdata['onlinestatus'] = "disabled=disabled";
        }
        $response['stateinfo'] = $statusdata;
    }
    
    //添加产品需求提单   
    function do_add(array & $request, array & $response, array & $app) {
        $xqissue = get_array_vars($request, array(
            'xqsue_kh_id',
            'xqsue_cp_id',
            'xqsue_pv_id',
            'xqsue_other_pv',
            'xqsue_kh_contact',
            'xqsue_kh_phone',
            'xqsue_submit_source',
            'xqsue_product_fun',
            'xqsue_title',
            'xqsue_background',
            'xqsue_detail',
        ));
        $xqissue['xqsue_user'] = CTX()->get_session("user_id");
        $xqissue['xqsue_status'] = '1';
        $xqissue['xqsue_submit_time'] = date('Y-m-d H:i:s');
        $ret = load_model('servicenter/ProductxqissueModel')->insert($xqissue,$request["file"]);
        exit_json_response($ret);
    }
    
    //编辑需求提单
    function do_edit(array & $request, array & $response, array & $app) {
        $xqissue = get_array_vars($request, array(
            'xqsue_kh_id',
            'xqsue_cp_id',
            'xqsue_pv_id',
            'xqsue_other_pv',
            'xqsue_kh_contact',
            'xqsue_kh_phone',
            'xqsue_submit_source',
            'xqsue_product_fun',
            'xqsue_title',
            'xqsue_background',
            'xqsue_detail',
        ));
        $ret = load_model('servicenter/ProductxqissueModel')->update($xqissue, $request['xqsue_number']);
        exit_json_response($ret);
    }
    
    //选择客户带出客户联系人和联系方式
    function get_clients_info(array & $request, array & $response, array & $app) {
        
         $ret = load_model('servicenter/ProductxqissueModel')->get_clients($request['xqsue_kh_id']);
         exit_json_response($ret);
    }
    
    //需求受理
    function do_xqissue_accept(array & $request, array & $response, array & $app) {
        $subdata =  array();
        $subdata['xqsue_status'] = '3';   //已受理，正在审批
        $subdata['xqsue_accept_user'] = CTX()->get_session("user_id");   //受理时间
        $subdata['xqsue_accept_time'] = date('Y-m-d H:i:s');             //受理人
        $ret = load_model('servicenter/ProductxqissueModel')->update_accept_status($request['xqsue_number'],$subdata);
        exit_json_response($ret);
    }
    //显示已经解决页面显示
    function do_show_online(array & $request, array & $response, array & $app) {
        $app['scene'] = "add";
        $ret['data']['xqsue_number'] = $request['xqsue_number'];
        $ret['data']['type'] = $request['type'];//1单个2批量
        $response['data'] = $ret['data'];
    }
    //需求上线
    function do_xqissue_online(array & $request, array & $response, array & $app) {
        if ($request['type'] == 1){
            //单个
            $ret = load_model('servicenter/ProductxqissueModel')->update_online_status($request['xqsue_number'],$request['xqsue_idea']);
        } else {
            //批量
            $xqsue_numbers = explode(',', $request['xqsue_number']);
            $ret = load_model('servicenter/ProductxqissueModel')->batch_update_online_status($xqsue_numbers,$request['xqsue_idea']);
        }
        
        exit_json_response($ret);
    }
     //批量需求上线
    function batch_do_xqissue_online(array & $request, array & $response, array & $app) {
        $ret = load_model('servicenter/ProductxqissueModel')->batch_update_online_status($request['xqsue_numbers']);
        exit_json_response($ret);
    }
    
    //显示需求无法解决显示
     function do_show_unable(array & $request, array & $response, array & $app) {
         $app['scene']="add";
         if(!empty($request['djbh'])){
            $ret['data']['xqsue_number'] = $request['djbh'];
            $ret['data']['type'] = "1";
        }
        $response['data'] = $ret['data'];
     }
     
     //显示已经解决页面显示
    function do_show_unable_pass(array & $request, array & $response, array & $app) {
        $app['scene'] = "add";
        $app["tpl"] = "servicenter/productxqissue_do_show_unable";
        if (!empty($request['djbh'])) {
            $ret['data']['xqsue_number'] = $request['djbh'];
            $ret['data']['type'] = "2";
        }
        $response['data'] = $ret['data'];
    }

    //显示需求拒绝页面显示
    function do_show_unable_deny(array & $request, array & $response, array & $app) {
        $app['scene'] = "add";
        $app["tpl"] = "servicenter/productxqissue_do_show_unable";
        if (!empty($request['djbh'])) {
            $ret['data']['xqsue_number'] = $request['djbh'];
            $ret['data']['type'] = "3";
        }
        $response['data'] = $ret['data'];
    }

    /**
     * 批量已解决
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function multi_do_show_unable_pass(array & $request, array & $response, array & $app) {
        $app['scene'] = "add";
        $app["tpl"] = "servicenter/productxqissue_multi_do_show_unable";
        $ret['data']['xqsue_number'] = $request['xqsue_numbers'];
        $ret['data']['type'] = "2";
        $response['data'] = $ret['data'];
    }

    
    //问题无法解决、问题已经解决处理、问题拒绝
    function do_xqissueunable(array & $request, array & $response, array & $app) {
        $unabledata = get_array_vars($request, array(
            'xqsue_idea',
        ));
        $unabledata['xqsue_idea_user'] = CTX()->get_session("user_id");
        $unabledata['xqsue_idea_time'] = date('Y-m-d H:i:s');
        if($request['type'] == 1){  //无法解决
             $unabledata['xqsue_status'] = '6';
        }elseif($request['type'] == 2){  //已解决
             $unabledata['xqsue_status'] = '5';
        }else{
            $unabledata['xqsue_status'] = '2';
        }
        $ret = load_model('servicenter/ProductxqissueModel')->update_unable_status($request['xqsue_number'],$unabledata,$request['type']);
        exit_json_response($ret);
    }

    /**
     * 批量操作
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function multi_do_xqissueunable(array & $request, array & $response, array & $app) {
        $unabledata = get_array_vars($request, array(
            'xqsue_idea',
        ));
        $unabledata['xqsue_idea_user'] = CTX()->get_session("user_id");
        $unabledata['xqsue_idea_time'] = date('Y-m-d H:i:s');
        if($request['type'] == 1){  //无法解决
            $unabledata['xqsue_status'] = '6';
        }elseif($request['type'] == 2){  //已解决
            $unabledata['xqsue_status'] = '5';
        }else{
            $unabledata['xqsue_status'] = '2';
        }
        $xqsue_number_arr = explode(',', $request['xqsue_number']);
        $xqsue = load_model('servicenter/ProductxqissueModel')->get_xqsue_info($xqsue_number_arr);
        $xqsue_status=array();
        foreach ($xqsue as $value){
            $xqsue_status[$value['xqsue_number']]=$value['xqsue_status'];
        }
        $err_num = 0;
        foreach ($xqsue_number_arr as $xqsue_number) {
            if ($unabledata['xqsue_status'] == 5 && $xqsue_status[$xqsue_number] != 4) {
                $err_num++;
                continue;
            }
            $result = load_model('servicenter/ProductxqissueModel')->update_unable_status($xqsue_number, $unabledata, $request['type']);
        }
        $ret = array('status' => 1, 'data' => '', 'message' => '操作成功！');
        if ($err_num > 0) {
            $success = count($xqsue_number_arr) - $err_num;
            $message = "成功{$success}条，失败{$err_num}条";
            $ret = array('status' => -1, 'data' => '', 'message' => $message);
        }
        exit_json_response($ret);
    }

    //显示需求审批窗体
    function do_show_idea(array & $request, array & $response, array & $app) {
        $app['scene'] = "add";
        $app["tpl"] = "servicenter/productxqissue_do_show_idea";
        if (!empty($request['djbh'])) {
            $ret['data']['xqsue_number'] = $request['djbh'];
        }
        $response['data'] = $ret['data'];
    }
    
    //需求审批操作
    function do_xqissueidea(array & $request, array & $response, array & $app){
        
        $ret = load_model('servicenter/ProductxqissueModel')->do_xqissueidea($request);
        exit_json_response($ret);
    }

    /**
     * 修改预返日期
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_xqissue_edit(array & $request, array & $response, array & $app) {
        $xqissue = get_array_vars($request, array('xqsue_number', 'xqsue_return_time_after', 'reason'));
        $xqissue_info = load_model('servicenter/ProductxqissueModel')->get_by_id($xqissue['xqsue_number']);
        $update['xqsue_return_time'] = $xqissue['xqsue_return_time_after'];
        $ret = load_model('servicenter/ProductxqissueModel')->update($update, $request['xqsue_number']);
        if ($ret['status'] == 1) {
            //日志
            $num = $request['xqsue_number'];
            $opera = "期返时间由{$xqissue_info['data']['xqsue_return_time']}变更为{$xqissue['xqsue_return_time_after']}，变更原因：{$xqissue['reason']}";
            $status = $xqissue_info['data']['xqsue_status'];
            $note = '操作成功';
            $data = load_model('servicenter/ProductxqissueModel')->save_log($num, $opera, $status, $note);
        }
        exit_json_response($ret);
    }


    function do_edit_return_time(array & $request, array & $response, array & $app) {
        $ret = load_model('servicenter/ProductxqissueModel')->get_by_id($request['xqsue_number']);
        $response['data'] = $ret['data'];
    }

    /**
     * 计划周次
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_plan_week(array & $request, array & $response, array & $app) {
        $ret = load_model('servicenter/ProductxqissueModel')->get_by_id($request['xqsue_number']);
        $response['data'] = $ret['data'];
    }

    /**
     * 附注
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_remark(array & $request, array & $response, array & $app) {
        $ret = load_model('servicenter/ProductxqissueModel')->get_by_id($request['xqsue_number']);//dump($ret);exit;
        $response['data'] = $ret['data'];
    }

    function do_xqissue_edit_plan_week(array & $request, array & $response, array & $app) {
        $xqissue = get_array_vars($request, array('xqsue_number', 'xqsue_plan_week'));
        $xqissue_info = load_model('servicenter/ProductxqissueModel')->get_by_id($xqissue['xqsue_number']);
        $update['xqsue_plan_week'] = $xqissue['xqsue_plan_week'];
        $ret = load_model('servicenter/ProductxqissueModel')->update($update, $request['xqsue_number']);
        if ($ret['status'] == 1) {
            //日志
            $num = $request['xqsue_number'];
            $opera = "计划周次由{$xqissue_info['data']['xqsue_plan_week']}变更为{$xqissue['xqsue_plan_week']}";
            $status = $xqissue_info['data']['xqsue_status'];
            $note = '操作成功';
            $data = load_model('servicenter/ProductxqissueModel')->save_log($num, $opera, $status, $note);
        }
        exit_json_response($ret);
    }

    /**
     * 编辑附注
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_xqissue_edit_reark(array & $request, array & $response, array & $app) {
        $xqissue = get_array_vars($request, array('xqsue_number', 'xqsue_urgency', 'xqsue_difficulty', 'xqsue_remark'));
        $xqissue_info = load_model('servicenter/ProductxqissueModel')->get_by_id($xqissue['xqsue_number']);
        $update['xqsue_urgency'] = $xqissue['xqsue_urgency'];
        $update['xqsue_difficulty'] = $xqissue['xqsue_difficulty'];
        $update['xqsue_remark'] = $xqissue['xqsue_remark'];
        $ret = load_model('servicenter/ProductxqissueModel')->update($update, $request['xqsue_number']);
        if ($ret['status'] == 1) {
            //日志
            $num = $request['xqsue_number'];
            $opera = '';
            if ($xqissue['xqsue_urgency'] != $xqissue_info['data']['xqsue_urgency']) {
                $old_xqsue_urgency = empty($xqissue_info['data']['xqsue_urgency']) ? '空' : $xqissue_info['data']['xqsue_urgency'];
                $opera .= "紧急程度由{$old_xqsue_urgency}修改为{$xqissue['xqsue_urgency']}; ";
            }
            if ($xqissue['xqsue_difficulty'] != $xqissue_info['data']['xqsue_difficulty']) {
                $xqsue_difficulty_arr = load_model('servicenter/ProductxqissueModel')->xqsue_difficulty;
                $old_xqsue_difficulty = isset($xqsue_difficulty_arr[$xqissue_info['data']['xqsue_difficulty']]) ? $xqsue_difficulty_arr[$xqissue_info['data']['xqsue_difficulty']] : '空';
                $new_xqsue_difficulty = isset($xqsue_difficulty_arr[$xqissue['xqsue_difficulty']]) ? $xqsue_difficulty_arr[$xqissue['xqsue_difficulty']] : '空';
                $opera .= "难易度由{$old_xqsue_difficulty}修改为{$new_xqsue_difficulty}; ";
            }
            if ($xqissue['xqsue_remark'] != $xqissue_info['data']['xqsue_remark']) {
                $old_xqsue_remark = empty($xqissue_info['data']['xqsue_remark']) ? '空' : $xqissue_info['data']['xqsue_remark'];
                $old_xqsue_remark = strip_tags($old_xqsue_remark);
                $xqissue['xqsue_remark'] = strip_tags($xqissue['xqsue_remark']);
                $opera .= "备注由{$old_xqsue_remark}修改为{$xqissue['xqsue_remark']}; ";
            }
            if (!empty($opera)) {
                $status = $xqissue_info['data']['xqsue_status'];
                $note = '操作成功';
                $data = load_model('servicenter/ProductxqissueModel')->save_log($num, $opera, $status, $note);
            }
        }
        exit_json_response($ret);
    }

}
