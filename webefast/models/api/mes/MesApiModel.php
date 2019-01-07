<?php

require_lib('net/HttpClient');

class MesApiModel {

    protected $username = 'admin';
    protected $password = '';
    protected $api_url = 'http://www.rifeng.cn:8081/mes/api.php';
    //protected $api_url = 'http://192.168.9.13:880/Server.svc/Api/Invoke';

    protected $ticket = '';

    function __construct($api_conf) {

        $this->username = isset($api_conf['username']) && !empty($api_conf['username']) ? $api_conf['username'] : $this->username;
        $this->password = isset($api_conf['password']) && !empty($api_conf['password']) ? $api_conf['password'] : $this->password;
        $this->api_url = isset($api_conf['api_url']) && !empty($api_conf['api_url']) ? $api_conf['api_url'] : $this->api_url;
    }

    public function request_send($api, $param = array()) {



        if ($api != 'Login') {
            $param['Context']['Ticket'] = $this->ticket;
        } else {
            $param['Context']['Ticket'] = NULL;
        }

        $header = array("Content-Type:application/json;charset=UTF-8");
        $body = json_encode($param);
        //请求接口

        $resp = $this->exec($this->api_url, $body, 'post', $header);


        return $this->get_response($resp);
    }

    public function get_response($resp) {
        $result = json_decode($resp, true);
        $this->ticket = $result['Context']['Ticket'];
        return $result;
    }

    /**
     * 执行单个API请求
     * @date 2015-03-12
     * @param string $url 请求的URL地址
     * @param array $parameters 请求参数
     * @param string $type post or get
     * @param array $headers header信息
     * @return mixed
     * @throws Exception
     * @see lib/apiclient/ApiClient::exec
     * @depends lib/net/HttpClient
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

}
