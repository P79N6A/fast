<?php
/*
 * 短信客户端抽象类
 * @author  wq 
 * @date 2018-3-19
 */
abstract class AbsSmsClient {
    
    public $send_channel = '';
    
    /**
     * 批量发送短信
     */
    abstract public function sendBatchSms(array $data, $other);

    /**
     * 单条发送短信
     */
    abstract public function sendSms($phone, $content, $other);
    

    /**
     * 获取短信可发送条数
     */
    abstract public function getSmsNum();

    /**
     * 获取发送结果（有可能是被动接口）
     */
    abstract public function getSmsResult($params);
    
    function format_ret($status, $data = '', $msg_key = NULL) {
        require_model('common/BaseModel');
        $m = new BaseModel();
        return $m->format_ret($status, $data, $msg_key);
    }
}
