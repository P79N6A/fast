<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_model('oms/invoice/OmsSellInvoiceModel', true);
class order_invoice {

    //订单开票列表
    function do_list(array &$request, array &$response, array &$app) {
        $response['do_list_tab'] = 'tabs_wait_invoice';
    }
    
    //正票红票确认页
    function zheng_invoice(array & $request, array & $response, array & $app) {
        $ret = load_model('oms/invoice/OmsSellInvoiceModel')->invoice_record($request['sell_record_code']);
        foreach ($ret as $v) {
             if($v['is_invoice'] == 2 && $v['is_red'] == 2){
                $res = load_model('oms/invoice/OmsSellInvoiceModel')->invoice_again($v['sell_record_code']);
                if($res['status']<0){
                    continue;
                }
            }
        }
        $response['data'] = load_model('oms/invoice/OmsSellInvoiceModel')->get_sell_invoice_detail($ret);
 
    }

    function confirm(array & $request, array & $response, array & $app) {
        set_time_limit(0);
        $err = array();
        foreach ($request['list'] as $v) {
            $v['type'] = isset($v['type']) ? $v['type'] : 0;
            $v['data_info'] = isset($v['data_info']) ? $v['data_info'] : '';
            $v['chyy'] = isset($v['chyy']) ? $v['chyy'] : '';
            $res = load_model('oms/invoice/OmSellInvoiceRecordModel')->create_invoice($v['sell_record_code'],$v['data_info'],$v['type'],$v['chyy']);
            if($res['status'] < 0){
                if(empty($res['message'])){
                    $res['message'] = '接口返回异常';
                }
                $err[] = "订单号".$v['sell_record_code'].":".$res['message'];
            }
        }
         $all_num = count($request['list']);
         $err_num = count($err);
            $rs['data'] = '';
            $rs['status'] = '1';
            $success_num = $all_num - $err_num;
            $message = '提交开票请求成功' . $success_num;
            if ($err_num > 0 || !empty($err)) {
                $rs['status'] = '-1';
                $message .=',' . '失败数量:' . $err_num;
                $file_name = $this->create_import_fail_files($err, 'invoice_list');
                $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            }
            $rs['message'] = $message;
           $response =  $rs;
    }
    
    function create_import_fail_files($msg_arr, $name) {
        $fail_top = array('错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg_arr as $key => $val) {
            $file_str .= $val . "\r\n";
        }
        $filename = md5($name . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function get_invoice_result(array & $request, array & $response, array & $app) {

        $response = load_model('oms/invoice/OmSellInvoiceRecordModel')->get_invoice_result($request['id']);
    }

    //订单查询记录
    function do_seach(array &$request, array &$response, array &$app) {
        
    }
    //获取交易号
    function get_sell_record_code(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new OmsSellInvoiceModel();
        $response = $mdl->opt_sell_record($request['invoice_id']);

    }
    
    /**
     * 修改开票金额
     */
    
    function edit_pay_money(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
       $response = load_model('oms/invoice/OmsSellInvoiceModel')->edit_pay_money($request['sell_record_code'],$request['pay_money']);
    }
    
    /**
     * 修改其他优惠金额
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function edit_other_amount(array &$request, array &$response, array &$app) {
         $app['fmt'] = 'json';
         $response = load_model('oms/invoice/OmsSellInvoiceModel')->edit_other_amount($request['sell_record_code'],$request['other_money']);
    }
    
    //结案
    function update_finish_status(array &$request, array &$response, array &$app) {
        $invoice_id = $request['invoice_id'];
        if (is_array($invoice_id)) {
            foreach ($invoice_id as $value) {
                $response = load_model('oms/invoice/OmsSellInvoiceModel') -> update_finish_status($value);
            }
            exit_json_response($response);
        } else {
            $response = load_model('oms/invoice/OmsSellInvoiceModel') -> update_finish_status($invoice_id);
            exit_json_response($response);
        }
    }
}
