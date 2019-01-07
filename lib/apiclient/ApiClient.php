<?php

require_lib('net/HttpClient');

/**
 * Interface ApiClientInterface
 * @author liud
 */
interface ApiClientInterface {

    /**
     * @param $apiName
     * @param $parameters
     */
    public function newHandle($apiName, $parameters);
}

/**
 * Class ApiClient
 * @author liud
 */
class ApiClient {

    /**
     * @var array 接口请求所需参数
     */
    protected $parameters = array();

    /**
     * 直接初始化接口所需参数
     * @param array $parameters
     * @throws Exception
     */
    public function __construct($parameters = array()) {
        $this->parameters = $parameters;
    }

    /**
     * @param $apiName
     * @param $parameters
     * @return array
     */
    public function newHandle($apiName, $parameters) {

    }

    /**
     * 执行单个API请求
     * @param $apiName
     * @param $parameters
     * @return mixed
     * @throws Exception
     */
    public function exec($apiName, $parameters) {
        $m = new HttpClient();

        $handle = $this->newHandle($apiName, $parameters);
        $type = isset($handle['type']) ? $handle['type'] : 'get';
        $url = isset($handle['url']) ? $handle['url'] : '';
        $headers = isset($handle['headers']) ? $handle['headers'] : array();
        $body = isset($handle['body']) ? $handle['body'] : array();
        $other = array('timeout' => 120);
     //   var_dump($type, $url, $headers, $body, $other);die;
        $m->newHandle('0', $type, $url, $headers, $body, $other);
        //$h->newHandle('0', $type, $url, $headers, $parameters,$other);
        $m->exec();

        $result = $m->responses();
        if (!isset($result['0'])) {
            throw new Exception('请求出错, 返回结果错误');
        }

        return $result['0'];
    }

    /**
     * 执行批量API请求
     * @param $handles
     * @return array
     */
    public function multiExec($handles) {
        $m = new HttpClient();

        foreach ($handles as $key => $handle) {
            $type = isset($handle['type']) ? $handle['type'] : 'get';
            $url = isset($handle['url']) ? $handle['url'] : '';
            $headers = isset($handle['headers']) ? $handle['headers'] : array();
            $body = isset($handle['body']) ? $handle['body'] : array();
            $m->newHandle($key, $type, $url, $headers, $body);
        }

        $m->exec();

        return $m->responses();
    }

    function format_ret($status, $data = '', $msg_key = NULL) {
        require_model('common/BaseModel');
        $m = new BaseModel();
        return $m->format_ret($status, $data, $msg_key);
    }

    /**
     * Json Decoder.
     * @param $str
     * @return mixed
     */
    public function jsonDecode($str) {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return json_decode($str, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            // 当PHP < 5.4.0时, 使用与正则表达示把长整型加上引号，变成字符串
            return json_decode(preg_replace('/:\s?(\d{14,})/', ': "${1}"', $str), true);
        }
    }

}
