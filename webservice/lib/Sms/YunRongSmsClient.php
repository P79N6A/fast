<?php

require_lib('Sms/AbsSmsClient', true);
require_lib('apiclient/sms/YunRongClient');
/*
 * 云融正通短信客户端
 * @author  wq 
 * @date 2018-3-19
 */

class YunRongSmsClient extends AbsSmsClient {

    private $client = null;
    private $api_param;
            
    function __construct($api_param) {
        //实例化客户端口
        $this->client = new YunRongClient();
        $this->send_channel = 'yunrong';
    }

    /**
     * 批量发送短信
     * @param array $data
     */
    public function sendBatchSms(array $data, $other=array()) {
        $content_arr = array();
        $phone_arr = array();
        foreach ($data as $val) {
            $phone_arr[] = $val['phone'];
            $content_arr[] = str_replace(',', '，', $val['content']);
        }
        $content = implode(',', $content_arr);
        $phone = implode(',', $phone_arr);
        $ret = $this->client->sendData($content, $phone, $other);
        return $ret;
    }

    /**
     * 单条发送短信
     * @param type $phone
     * @param type $content
     */
    public function sendSms($phone, $content, $other = array()) {
        $ret = $this->client->smsSend($content, $phone, $other);
        return $ret;
    }
    
    /**
     * 获取短信可发送条数
     */
    public function getSmsNum() {
        $ret = $this->client->balanceQuery();
        return $ret;
    }

    /**
     *  获取发送结果（有可能是被动接口）
     */
    public function getSmsResult($params) {
        $ret = $this->client->smsReport($params);
        return $ret;
    }

}
