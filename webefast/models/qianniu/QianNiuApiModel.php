<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of QianNiuApiModel
 *
 * @author wq
 */
class QianNiuApiModel {

    function exec_api($request) {
        $check_status = $this->check_api_data($request);
        if ($check_status !== true) {
            return $check_status;
        }
        $method_arr = array(
            'taobao.qianniu.worklink.ticketsystem.isv.create' => 'create_ticket',
            'taobao.qianniu.worklink.ticketsystem.isv.cancel' => 'cancel_tickets',
        );
        $method = $request['method'];
        if (!isset($method_arr[$method])) {
            return array('error_code' => -2000, 'error_message' => '接口请求方法找不到！');
        }
        $action = $method_arr[$method];

        $kh_id = load_model('qianniu/QnEnterpriseModel')->get_kh_id_by_qn_id($request['enterprise_id']);
        if (empty($kh_id)) {
            return array('error_code' => -3000, 'error_message' => '未找到对应企业');
        }

        load_model('api/ApiKehuModel')->change_db_conn($kh_id);

        $ticket_form =  isset($request['ticket_form'])?json_decode($request['ticket_form'],true):array();
         
        
//       if(!empty($body)){
//           $request['ticket_form'] = $body;
////           if(!empty($arr)){
////                $request  = array_merge($request,$arr);
////           }
//           $ticket_form = json_decode($body,true);
//       }
 
        $ret_data = load_model('qianniu/TicketModel')->$action($request,$ticket_form);
        return $ret_data;
    }

    function check_api_data($request) {
//        if ($request['method'] != $method) {
//            return array('error_code' => -1000, 'error_message' => '接口方法异常！');
//        }
//        if ($request['method'] != $method) {
//            return array('error_code' => -1000, 'error_message' => '接口方法异常！');
//        }
        $api_time = strtotime($request['timestamp']);
        $time = abs($api_time - time());
        if ($time > 120) {
            return array('error_code' => -1000, 'error_message' => '接口请求超时！');
        }
        return true;
    }

    //先不实现
    function check_sign($request) {

        return true;
    }

}
