<?php
require_lib ('util/web_util', true);
class sms_config {
    /**
     * 短信通用配置列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array &$request, array &$response, array &$app) {
        $arr = array(':parent_code' => "sms_config");
        $sms_config = load_model('sys/ParamsModel')->get_params($arr);
        $data = array();
        foreach ($sms_config as $val) {
            if (!is_array($val)){
                continue;
            }
            foreach ($val as $val_2) {
                $parent_code = $val_2['param_code'];
                $arr = array(':parent_code' => $parent_code);
                $ret = load_model('sys/ParamsModel')->get_params($arr);
                $data[$parent_code] = $ret;
            }
        }
        
        $response = $data;
    }
    /**
     * ajax 保存通用配置
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function save_sms_config_common(array &$request, array &$response, array &$app) {
        //参数校验
        $params = get_array_vars($request, array('sms_send_timeout_time'));
        trim_array($params);
        $ret_valid = $this->_valid($params);
        if ($ret_valid['status'] != 1) {
            exit_json_response($ret_valid);
        }
        $ret = load_model('sys/ParamsModel')->save($request);
        exit_json_response($ret);
    }
    
    /**
     *  验证表单字段
     * @param type $param
     */
    private function _valid(&$param) {
        $model = load_model('sys/ParamsModel');
        if (isset($param['sms_send_timeout_time']) && (empty($param['sms_send_timeout_time']) || !preg_match('/^(0?[1-9]|1[0-9]|2[0-4])$/', $param['sms_send_timeout_time']))){
            return $model->format_ret(-1, '', '短信发送超时参数范围:1-24小时');
        }
        if (isset($param['sms_send_interval_time']) && (empty($param['sms_send_interval_time']) || !preg_match('/^\d+$/', $param['sms_send_interval_time']))){
            return $model->format_ret(-1, '', '短信发送间隔时间参数范围:不低于1秒');
        }
        return $model->format_ret(1);
    }
}
