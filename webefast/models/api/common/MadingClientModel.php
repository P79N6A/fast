<?php

require_model('api/common/ApiServiceModel');

/**
 * 马丁博士接口
 * @author WMH
 */
class MadingClientModel extends ApiServiceModel {

    private $validPeriod = 3000; //接口token有效期1小时（此处设置50分钟）
    private $dateRegister;

    function __construct($api_param) {
        $api_param['token'] = $api_param['param_value1']; //token
        $api_param['token_time'] = $api_param['param_value2']; //token获取时间
        unset($api_param['param_value1'], $api_param['param_value2']);
        $this->dateRegister = date('Y-m-d H:i:s'); //当前时间，用来计算sign
        $this->header = array("Content-Type:application/json");
        parent::__construct($api_param);
    }

    /**
     * 取消接口
     * @param array $param
     * @return array
     */
    public function cancel($param) {
        $this->timeout = 10; //接口超时时间
        $ret = $this->check_api_param();
        if ($ret['status'] < 1) {
            return $ret;
        }
        if (empty($this->api_config['api_method_url'])) {
            return $this->format_resp(-1, '', '接口方法地址未配置');
        }
        $this->api_url = $this->api_config['api_method_url'];
        $this->create_url();

        $request_param = [
            'orderNumber' => $param['record_code'],
            'orderType' => 0,
            'cancelOrderNumber' => $param['record_code'],
            'cancelTime' => $this->dateRegister,
            'dealCode' => $param['deal_code'],
        ];

        $resp = $this->request_api(json_encode($request_param));

        $ret = $this->get_response($resp);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $data = $ret['data'];
        if ($data['cancelOrderNumber'] != $param['record_code']) {
            return $this->format_ret(-1, '', "接口返回订单号[{$data['cancelOrderNumber']}]与请求订单号[{$param['record_code']}]不一致");
        }
        if ($data['Status'] == 100) {
            $ret['message'] = '接口取消成功';
        } else {
            $ret['status'] = -1;
            $ret['message'] = "接口取消失败，状态码[{$data['Status']}],错误信息[{$data['ErrorMessage']}]";
        }

        return $ret;
    }

    /**
     * 校验接口参数
     * @return array
     */
    private function check_api_param() {
        if (time() < strtotime($this->api_config['online_date'])) {
            return $this->format_resp(-1, '', '接口服务暂未上线');
        }
        $api_param_json = json_decode($this->api_config['api_param_json'], TRUE);
        if (empty($api_param_json)) {
            return $this->format_resp(-1, '', '接口参数配置异常');
        }
        if (empty($this->api_url)) {
            return $this->format_resp(-1, '', '接口地址错误');
        }
        $this->api_config = array_merge($this->api_config, $api_param_json);

        //检查token是否有效
        $ret = $this->check_token();
        if ($ret['status'] < 1) {
            return $ret;
        }

        return $this->format_resp(1, '', '接口参数校验成功');
    }

    /**
     * 校验token是否有效
     * @return array
     */
    private function check_token() {
        //token失效日期
        $expiry_time = empty($this->api_config['token_time']) ? 0 : $this->api_config['token_time'] + $this->validPeriod;
        if (time() < $expiry_time) {
            //token有效
            return $this->format_resp(1, '', 'token有效');
        }
        //token失效重新获取
        $ret = $this->get_token();
        if ($ret['status'] == 1) {
            $dateRegister = strtotime($this->dateRegister);
            $this->api_config['token'] = $ret['data'];
            $this->api_config['token_time'] = $dateRegister;

            $update_arr = ['param_value1' => $ret['data'], 'param_value2' => $dateRegister];
            $where = ['service_code' => $this->api_config['service_code'], 'method_type' => $this->api_config['method_type']];
            load_model('sys/ApiServiceConfigModel')->update($update_arr, $where);
        }
        return $this->format_resp(1);
    }

    /**
     * 获取token
     * @return array
     */
    private function get_token($param = [], $is_test = 0) {
        if ($is_test == 0) {
            $param = [
                'userid' => $this->api_config['userid'],
                'dateRegister' => $this->dateRegister,
                'key' => $this->api_config['key']
            ];
        }
        $param['sign'] = $this->sign($param);
        unset($param['key']);

        $this->create_url($param);

        $resp = $this->request_api(['type' => 'get']);

        return $this->get_response($resp, 'token');
    }

    /**
     * 生成签名
     * @param string $param
     */
    private function sign($param) {
        $str = $param['userid'] . $param['key'] . $param['dateRegister'];
        //获取md5加密,base64,二进制数据包组合
        $sign = base64_encode(pack("H32", md5(utf8_encode($str))));
        return $sign;
    }

    /**
     * 组合请求url
     * @param array $param
     */
    private function create_url($param = []) {
        if (empty($param)) {
            $param = [
                'userid' => $this->api_config['userid'],
                'token' => $this->api_config['token'],
            ];
        }
        $this->api_url .= "?" . http_build_query($param, PHP_QUERY_RFC1738);
    }

    /**
     * 获取接口返回数据
     * @param array $resp
     * @param string $type
     * @return array
     */
    private function get_response($resp, $type = '') {
        $info = '';
        $data = '';
        if (empty($resp)) {
            $info = '接口请求超时';
        } else if (!isset($resp['Status']) && isset($resp['Message'])) {
            $info = "接口参数有误，错误信息[{$resp['Message']}]";
        } else if ($resp['Status'] != 100) {
            if ($type == 'token') {
                $info = "请求接口token失败，状态码[{$resp['Status']}]，错误信息[{$resp['ErrorMessage']}]";
            } else {
                $data = $resp['Data'];
            }
        } else if ($resp['Status'] == 100) {
            $data = $resp['Data'];
        } else {
            $info = '接口异常';
        }
        $status = 1;
        if ($info !== '') {
            $status = -1;
        }

        return $this->format_resp($status, $data, $info);
    }

    /**
     * 接口测试
     * @param array $param
     * @return array
     */
    public function api_test($param) {
        //$this->api_url = $param['api_url'];
        $this->api_url = 'http://116.236.112.27:3101/api/Account/Token';
        $param = [
            'userid' => '20000',
            'key' => '2191824B4F194A879AC',
            'dateRegister' => $this->dateRegister,
        ];
        $ret = $this->get_token($param, 1);

        return $ret;
    }

}
