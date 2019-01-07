<?php

require_lib('apiclient/ApiClient');
require_lib('util/crm_util');

class YunRongClient extends ApiClient implements ApiClientInterface {
    private $api_url = 'http://47.96.147.133:9001';
    private $username = 'baotayx';
    private $password = 'jB9lW7mI';
    private $ApiKEY = '';//md5(userName+md5(password))
    
    public function __construct() {
        $this->__setApiKEY();
        parent::__construct();
    }
    private $api_name = array(
        'balanceQuery' => 'balanceQuery.do',//余额查询接口
        'md5Digest' => 'md5Digest.do',//md5加密接口 (密码加密为 $ApiKEY)
        'passwordUpdate' => 'passwordUpdate.do', //更改密码接口
        'smsSend' => 'smsSend.do',//普通短信发送接口（支持post请求）
        'sendData' => 'sendData.do', //多个短信发送接口（支持post请求）
    );
    
    private $error_list = array(
//        1 => '修改成功',
        0 => '提交抛出异常 | 解析定时时间抛出异常',
        -1 => '账号或密码不正确 | 解析定时时间抛出异常',
        -2 => '必填选项为空 | 扣费条数小于0 | 号码数和短信数不相等',
        -3 => '内容长度为0',
        -4 => '0个有效号码',
        -5 => '余额不足',
        -10 => '账号状态错误',
        -11 => '内容过长',
        -12 => '扩展码权限错误 | 特服号小于等于0 | 特服号不是正整数 | 扩展号长度超过限定长度',
        -13 => 'IP鉴权失败',
        -24 => '手机号码超过限定个数',
        -25 => '没有提交权限',
    );
    private $report_status = array(
        'DELIVRD' => '状态成功',
        'UNDELIV' => '状态失败',
        'EXPIRED' => '因为用户长时间关机或者不在服务区等导致的短消息超时没有递交到用户手机上',
        'REJECTD' => '消息因为某些原因被拒绝',
        'MBBLACK' => '黑号',
        'NOPASS' => '审核驳回',
        'SUBFAIL' => '提交失败',
        'GATEBLA' => '网关屏蔽号段',
    );
    /**
     * 获取接口方法
     * @param string $code
     * @return string
     */
    private function __getApiName($code) {
        $api_name =  isset($this->api_name[$code]) ? $this->api_name[$code] : '';
        return $api_name;
    }
    /**
     * 获取错误码信息
     * @param string $error_code
     * @return string
     */
    private function __getErrorMsg($error_code) {
        $error_msg =  isset($this->error_list[$error_code]) ? $this->error_list[$error_code] : $error_code;
        return $error_msg;
    }
    /**
     * 获取状态码信息
     * @param string $code
     * @return string
     */
    private function __getReportMsg($code) {
        $msg =  isset($this->report_status[$code]) ? $this->report_status[$code] : '';
        return $msg;
    }
    /**
      * 初始化请求配置(覆盖父类)
      * @param type $apiName
      * @param type $params
      * @return type
      */
    public function newHandle($apiName, $params) {
        $handle = array();
        $handle['type'] = "post";
        $handle['url'] = $this->__buildUrl($apiName, $params);
        $handle['body'] = $params;
        $handle['headers'] = array();
        return $handle;
    }
    private function __buildUrl($apiName, $params) {
        $requestUrl = $this->api_url . "/" . $apiName . "?";
//        foreach ($params as $sysParamKey => $sysParamValue) {
//            $requestUrl .= "$sysParamKey=" . $sysParamValue . "&";
//        }
        $requestUrl = substr($requestUrl, 0, -1);
        return $requestUrl;
    }
    /**
     * 初始化ApiKEY
     * md5(userName+md5(password))
     */
    private function __setApiKEY() {
        $this->ApiKEY = md5($this->username . md5($this->password));
    }
    /**
     * 统一请求方法
     * @param type $apiName
     * @param type $params
     * @return string
     */
    public function getApiData($apiName, $params) {
        try {
//            $url = $this->__buildUrl($apiName, $params);
//            $ret = makeRequest($url, $params, 'post');
            $ret = $this->exec($apiName, $params);
            if(0 >= $ret && 'balanceQuery' != $apiName){
                $ret = $this->format_ret(-1, '', $this->__getErrorMsg($ret));
            }else{
                $ret = $this->format_ret(1, $ret);
            }
        } catch (Exception $e) {
            $ret = $this->format_ret(-1, '', '云融正通接口请求出错:' . $e->getMessage());
        }
        return $ret;
    }
    /**
     * 短信余额查询
     * @return type 返回剩余短信条数, 最小值为0
     */
    public function balanceQuery() {
        $params = array(
            'username' => $this->username,
            'password' => $this->ApiKEY
        );
        $apiName = $this->__getApiName('balanceQuery');
        $ret = $this->getApiData($apiName, $params);
        return $ret;
    }
    /**
     * 更改密码接口
     */
    public function passwordUpdate($newPassword) {
        $params = array(
            'username' => $this->username,
            'password' => $this->ApiKEY,
            'newpassword' => $newPassword
        );
        $apiName = $this->__getApiName('passwordUpdate');
        $ret = $this->getApiData($apiName, $params);
        return $ret;
    }
    /**
     * 普通短信发送接口
     * @param string $content 每条短信内容长度不超过500汉字、中英文混排不超过500字
     * @param string $mobile 多手机号英文逗号分隔,例如: 15555555555,15555555555,15555555555
     * @param array $other 其他非必填参数
     * @return type
     */
    public function smsSend($content, $mobile, $other = array()) {
        $params = array(
            'username' => $this->username,
            'password' => $this->ApiKEY,
            'content' => $content,
            'mobile' => $mobile,
            'ext' => isset($other['ext']) ? $other['ext'] : '',//非必填, 扩展号
            'msgfmt' => isset($other['msgfmt']) ? $other['msgfmt'] : 'UTF-8',//非必填, 字符编码（默认UTF-8）
            'dstime' => isset($other['dstime']) ? $other['dstime'] : '',//非必填, 定时时间（yyyy-MM-dd HH:mm:ss）
            'msgid' => isset($other['msgid']) ? $other['msgid'] : '',//非必填, 唯一标记
        );
        $apiName = $this->__getApiName('smsSend');
        $ret = $this->getApiData($apiName, $params);
        return $ret;
    }
    /**
     * 多个短信发送接口
     * @param string $content 多内容英文逗号分隔,例如: content1,content2,content3
     * @param string $mobile 多手机号英文逗号分隔,例如: 15555555555,15555555555,15555555555
     * @param array $other 其他非必填参数
     * @return type
     */
    public function sendData($content, $mobile, $other = array()) {
        $params = array(
            'username' => $this->username,
            'password' => $this->ApiKEY,
            'content' => $content,
            'mobile' => $mobile,
            'ext' => isset($other['ext']) ? $other['ext'] : '',//非必填, 扩展号
            'msgfmt' => isset($other['msgfmt']) ? $other['msgfmt'] : 'UTF-8',//非必填, 字符编码（默认UTF-8）
            'dstime' => isset($other['dstime']) ? $other['dstime'] : '',//非必填, 定时时间（yyyy-MM-dd HH:mm:ss）
            'msgid' => isset($other['msgid']) ? $other['msgid'] : '',//非必填, 唯一标记
        );
        $apiName = $this->__getApiName('sendData');
        $ret = $this->getApiData($apiName, $params);
        return $ret;
    }
    /**
     * 计算短信条数
     * @param type $content 超过70个字符算长短信，用67个字符计费一条，未超过70个字符（包含）则就是计费一条
     * @return type
     */
    public function getSmsCount($content) {
        $len = strlen_utf8($content);
        $num = (70 < $len) ? ceil($len/67) : 1;
        return $num;
    }
    /**
     * 接收短信状态报告 http post
     * @param type $report = 号码|状态码|短信ID|扩展码|接收时间;号码|状态码|短信ID|扩展码|接收时间
     * @return int
     */
    public function smsReport($params){
        if (!isset($params['report']) || empty($params['report'])){
            echo -1;exit;
        }
        $params['report'] = trim($params['report'],';');
        $report_arr = explode(';', $params['report']);
        $SmsTaskModel = load_model('common/SmsTaskModel');
        $SmsTaskModel->begin_trans();
        foreach ($report_arr as $val) {
            $val_arr = explode('|', $val);
            if (!isset($val_arr[0]) || !isset($val_arr[1]) || !isset($val_arr[2]) || !isset($val_arr[3]) || !isset($val_arr[4])){ //参数异常
                echo -1;exit;
            }
            $where = array(
                'mobile' => $val_arr[0],
                'send_channel' => 'yunrong',
                'send_channel_code' => $val_arr[2],
                //'ext' => $val_arr[3],//暂时未设置此字段, 不需要
            );
            $data = array(
                'status' => ('DELIVRD' == $val_arr[1]) ? 1 : 2,
                'report_time' => $val_arr[4],//客户接收时间
                'remark' => $this->__getReportMsg($val_arr[1])
            );
            $SmsTaskModel->update($data, $where);
            if (1 > $SmsTaskModel->affected_rows()){
                $SmsTaskModel->rollback();
                echo -1;exit;
            }
        }
        $SmsTaskModel->commit();
        echo 0;exit;
    }
    /**
     * 上行短信 (客户回复内容) http post (暂时未做)
     * @param type $params = 内容|扩展号|编码格式|号码|用户名|时间;内容|扩展号|编码格式|号码|用户名|时间
     * @return int
     */
    public function smsDeliver($params){
        return 0;
    }
    
}
