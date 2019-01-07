<?php
require_lib ('util/web_util', true);
class sms_queue {
    /**
     * 列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array &$request, array &$response, array &$app) {
        $SmsQueueModel = load_model('op/SmsQueueModel');
        $sms_status = array('' => '全部') + $SmsQueueModel->sms_status;
        $sms_type = array('' => '全部') + $SmsQueueModel->sms_type;
        $response['select']['sms_status'] = array_from_dict($sms_status);
        $response['select']['sms_type'] = array_from_dict($sms_type);
        //获取权限
        $priv_params = array(
            'op/sms_queue/export_list',//导出
            'op/sms_queue/do_preview',//查看
            'op/sms_queue/opt_send_sms',//发送（含批量）
            'op/sms_queue/opt_over_sms',//终止（含批量）
        );
        foreach ($priv_params as $val) {
            $response['priv'][$val] = load_model('sys/PrivilegeModel')->check_priv($val);
        }
    }
    /**
     * 获取统计数据
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_statistical_data(array &$request, array &$response, array &$app) {
        $ret = load_model('op/SmsQueueModel')->get_statistical_data($request);
        exit_json_response($ret);
    }
    /**
     * 发送短信
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function send_sms(array &$request, array &$response, array &$app) {
        if (isset($request['params']['id'])){
            $id = $request['params']['id'];
        }else{
            $id = isset($request['id']) ? $request['id'] : '';
        }
        $ret = load_model('op/SmsQueueModel')->send_sms($id);
        exit_json_response($ret);
    }
    /**
     * 终止短信任务
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function over_sms(array &$request, array &$response, array &$app) {
        if (isset($request['params']['id'])){
            $id = $request['params']['id'];
        }else{
            $id = isset($request['id']) ? $request['id'] : '';
        }
        $ret = load_model('op/SmsQueueModel')->over_sms($id);
        exit_json_response($ret);
    }
    /**
     * 一键发送短信(获取所有记录id)
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function get_all_sms_id(array &$request, array &$response, array &$app) {
        $ret = load_model('op/SmsQueueModel')->get_all_sms_id($request);
        exit_json_response($ret);
    }
    /**
     * 自动服务: 生成订单发货通知短信
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function create_delivered_sms(array &$request, array &$response, array &$app) {
        load_model('op/SmsQueueModel')->create_delivered_sms();
        exit_json_response(1);
    }
    /**
     * 自动服务: 自动发送短信
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function auto_send_sms(array &$request, array &$response, array &$app) {
        load_model('op/SmsQueueModel')->autoSendSms();
        exit_json_response(1);
    }
    /**
     * api: 更新短信发送结果 (暂未使用)
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function update_sms_status_api(array &$request, array &$response, array &$app) {
        $ret = load_model('op/SmsQueueModel')->update_sms_status_api($request);
        exit_json_response($ret);
    }
}
