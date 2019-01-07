<?php
require_lib ('util/web_util', true);
require_model ('SmsModel', true);
class sms {
    
    public function send_batch_sms(array & $request, array & $response, array & $app) {
        $model = load_model('SmsModel');
        if (isset($request['is_one_by_one']) && !empty($request['is_one_by_one'])){
            $model->sendBatchSmsOneByOne();
        }else{
            $model->sendBatchSms();
        }
        exit_json_response(1);
    }
    
    /**
     * 获取发送结果
     * $request['report'] = '15915243216|DELIVRD|555555||2018-03-23 11:19:17;15994949996|UNDELIV|333333||2018-03-23 11:19:17;';
     */
    public function get_sms_result(array & $request, array & $response, array & $app) {
        $ret = load_model('SmsModel')->getSmsResult($request);
        exit_json_response($ret);
    }
    /**
     * 推送短信报告到客户表
     */
    public function push_sms_report(array & $request, array & $response, array & $app) {
        $ret = load_model('SmsModel')->pushSmsReport();
        exit_json_response(1);
    }
}