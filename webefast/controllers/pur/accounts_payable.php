<?php

require_lib('util/web_util', true);
require_lib('business_util', true);
require_model('tb/TbModel');
class accounts_payable {

    function do_list(array & $request, array & $response, array & $app) {
        
    }
    function add_payment_money(array & $request, array & $response, array & $app) {
        $accounts = load_model('pur/AccountsPayableModel');
        $response['data'] = $accounts->get_record_info($request['params']);
    }
    function set_payment_money(array & $request, array & $response, array & $app) {
        //获取单据信息
        $response['data'] =  load_model('pur/AccountsPayableModel')->get_record_info($request['record_code_str']);
        //当前支付金额
        $response['current_payment_money'] = sprintf('%.3f',$request['current_payment_money']);
    }
    
    function edit_remark(array &$request, array &$response, array &$app) {

    }
    
    /**
     * 批量备注
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function edit_remark_action(array &$request, array &$response, array &$app) {   
        $app['fmt'] = 'json';
        $error_msg = array();
        $filter = array();        
        if (empty($request['remark'])) {
            $ret = array(
                'status' => -1,
                'data' => '',
                'message' => "备注内容不能为空！",
            );
            exit_json_response($ret);
        }
        foreach ($request['purchaser_record_code_list'] as $k => $value) {
            $type = strstr($value, "_", TRUE);
            $record_code = substr(strstr($value, "_"),1);
            $sql_purchaser = "FROM pur_{$type}_record WHERE record_code = :record_code";
            $sql_values = array();
            $sql_values[':record_code'] = $record_code;            
            $yl_remark = load_model('common/BaseModel')->get_page_from_sql($filter, $sql_purchaser, $sql_values, $select='remark');
            $ret = load_model('pur/AccountsPayableModel')->update_remark($yl_remark,$request['remark'],$type,$record_code);
            if ($ret['status'] != 1) {
                $error_msg[] = array($filter['purchaser_record_code'] => $ret['message']);
            }
        }                       
        if (!empty($error_msg)) {
            $sum_num = count($request['purchaser_record_code_list']);
            $error_num = count($error_msg);
            $success_num = $sum_num - $error_num;
            $message = "成功{$success_num}条，失败{$error_num}";
            $ret = array(
                'status' => -1,
                'data' => '',
                'message' => $message,
            );
        } else {
            $ret = array(
                'status' => 1,
                'data' => '',
                'message' => '添加成功！',
            );
        }
        exit_json_response($ret);
    }
    
    function get_by_page_record(array & $request, array & $response, array & $app) {
       $app['fmt'] = 'json';
       $params = get_array_vars($request, array('list_type', 'record_code_str', 'current_payment_money'));
       $data = load_model('pur/AccountsPayableModel')->get_by_page_record($params);
       $response['rows'] = $data;
       $response['hasError'] = false;
       $response['error'] = '';
    }
    function save_info(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('record_data', 'pay_time', 'supplier_code','remark'));
        $ret = load_model('pur/PaymentModel')->add_payment($params);
	exit_json_response($ret);
    }
}
