<?php

require_lib('net/HttpClient');

/**
 * 标准接口访问类
 * 外部公共接口配置
 * @author WMH
 */
class ApiServiceModel {

    protected $api_config;
    protected $api_url;
    protected $header;
    protected $timeout = 30;

    public function __construct($api_param = []) {
        $this->api_config = $api_param;
        $this->api_url = isset($api_param['api_url']) ? $api_param['api_url'] : '';
    }

    public function request_api($param = []) {
        if (empty($this->api_url)) {
            return $this->format_ret(-1, '', '接口地址错误');
        }
        $other = ['timeout' => $this->timeout];
        $type = 'post';
        if (isset($param['type'])) {
            $type = $param['type'];
            unset($param['type']);
        }

        $resp = $this->exec($this->api_url, $param, $type, $this->header, $other);

        return json_decode($resp, TRUE);
    }

    /**
     * 执行单个API请求
     * @param string $url
     * @param array $parameters
     * @param string $type
     * @param array $headers
     * @param array $other
     * @return array
     * @throws Exception
     */
    public function exec($url, $parameters, $type = 'post', $headers = array(), $other = array()) {
        $h = new HttpClient();

        $h->newHandle('0', $type, $url, $headers, $parameters, $other);
        $h->exec();

        $result = $h->responses();
        if (!isset($result['0'])) {
            throw new Exception('请求出错, 返回结果错误');
        }

        return $result['0'];
    }

    public function format_resp($status, $data = '', $message = '') {
        return [
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ];
    }

}
