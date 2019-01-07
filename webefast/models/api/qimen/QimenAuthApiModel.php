<?php

/**
 * 奇门官方接口业务
 *
 * @author WMH
 */
class QimenAuthApiModel {

    private $db;
    private $log_id;
    private $req_data;
    private $secret = '123456789';

    function __construct() {
        $this->db = CTX()->db;
    }

    public function exec_api($request) {
        $sys_param = get_array_vars($request, ['method', 'sign', 'timestamp', 'target_app_key', 'app_key', 'v', 'format', 'sign_method']);
        $this->req_data = array_diff($request, $sys_param);

        $this->save_log($sys_param);

        $ret = $this->checkSign($request);
        if ($ret !== true) {
            return $ret;
        }

        $method = $sys_param['method'];
        $action_method = str_replace('.', '_', $method);

        $qimenoms = load_model('oms/QimenOmsModel');

        if (method_exists($qimenoms, $action_method)) {
            $ret = $qimenoms->$action_method($this->req_data);
            return $this->return_info($ret['status'], $ret['message']);
        } else {
            return $this->return_info(-1, '找不到指定方法');
        }
    }

    private function checkSign($request) {
        $ori_sign = $request['sign'];
        unset($request['sign']);

        $sign = $this->sign($request);

        if ($sign != $ori_sign) {
            return $this->return_info(-10);
        }

        return true;
    }

    private function return_info($status, $message = '', $ret_data = []) {
        $return = ['success' => true, 'errorCode' => '', 'errorMsg' => ''];

        $return['success'] = $status < 0 ? FALSE : TRUE;
        switch ($status) {
            case 1:
                break;
            case -10:
                //验签失败
                $return['errorCode'] = 'sign-check-failure';
                $return['errorMsg'] = 'Illegal request';
                break;
            default:
                $return['errorCode'] = 'modify-address-failed ';
                break;
        }

        $return['errorMsg'] = empty($return['errorMsg']) ? $message : $return['errorMsg'];

        if (!empty($ret_data)) {
            $return = array_merge($return, $ret_data);
        }

        $jsonstr = json_encode($return);
        $this->update_log($jsonstr, $status);
        return $jsonstr;
    }

    private function sign($params) {
        ksort($params);

        $stringToBeSigned = $this->secret;
        $stringToBeSigned .= self::getParamStrFromMap($params);

        $stringToBeSigned .= $this->secret;
        return strtoupper(md5($stringToBeSigned));
    }

    private function getParamStrFromMap($params) {
        ksort($params);
        $stringToBeSigned = "";
        foreach ($params as $k => $v) {
            if (strcmp("sign", $k) != 0) {
                $stringToBeSigned .= "$k$v";
            }
        }

        return $stringToBeSigned;
    }

    private function save_log($request) {
        $request['method'] = empty($request['method']) ? '' : $request['method'];
        $data = array('type' => 'qmauth', 'method' => $request['method'], 'add_time' => date('Y-m-d H:i:s'));
        $data['post_data'] = json_encode($this->req_data);
        $data['post_data'] = empty($data['post_data']) ? '' : $data['post_data'];
        $data['url'] = json_encode($request);
        $data['url'] = empty($data['url']) ? '' : $data['url'];
        ;
        $this->db->insert('api_open_logs', $data);
        $this->log_id = $this->db->insert_id();
    }

    function update_log($return, $status) {
        if ($this->log_id != 0) {
            $up_data = array('return_data' => $return);
            if ($status > 0) {
                $up_data['key_id'] = $this->log_unid;
            } else {
                $up_data['key_id'] = NULL;
            }
            $this->db->update('api_open_logs', $up_data, "id = '{$this->log_id }'");
        }
    }

}
