<?php

require_lib('apiclient/ApiClient');

/**
 * Kisdee API
 */
class KisApiModel extends ApiClient implements ApiClientInterface {

    protected $api_url = 'http://kd.cmcloud.cn/Kisoemapi/get_server_url';
    protected $client_id = '1473126105';
    protected $client_secret = 'Cs4M9tXkGwNs69XhkCRcHDy4nKw7YWqZbB9UKwCh';
    protected $auth_token, $state;

    function __construct() {
        parent::__construct();
    }

    function request_api($apiName, $params) {
        if (!isset($params['server_url']) || empty($params['server_url'])) {
            return $this->format_ret(-1, '', '接口地址错误');
        }
        $this->api_url = $params['server_url'];
        $this->auth_token = $params['auth_token'];
        unset($params['auth_token'], $params['server_url']);

        $response = $this->exec($apiName, $params);

        $ret = $this->deal_service_api_return($response);
        return $ret;
    }

    function request_api_test($params) {
        $this->auth_token = $params['auth_token'];
        unset($params['auth_token']);

        $response = $this->exec('', $params);
        $ret = $this->deal_system_api_return($response);
        return $ret;
    }

    function DealAcctPlatForm($params) {
        $params['server_url'] = 'http://119.29.169.16/Kisopenapi/router/';
        //https://kisgz.kingdee.com/

        $params['eid'] = '8854307';
        $params['client_id'] = '1473126105';
        $params['client_secret'] = 'Cs4M9tXkGwNs69XhkCRcHDy4nKw7YWqZbB9UKwCh';
        $params['auth_token'] = '128C564899D0411C8122016C546620EA';
        $params['AccountDB'] = 'AIS20161101043729';
        $params['ver'] = '2.0';
        $apiName = 'kis.APP004088.acctplatform.AcctController.DealAcctPlatForm';
        $params['netid'] = '8854307661059';
        $params['custdata'] = '{"ProductID":"S1S013S001","AccountDB":"AIS20161101043729","Data":{"Action":"SyncBill","Recordset":[{"FDate":"2016-10-01","FTypeID":"1","FID":"A0001","FRowIndex":"1","FCustID":"001","FSupplyID":"001","FDeptID":"","FSalesmanID":"","FSettleTypeID":"","FAmount_NoTax":"2000","FTax":"50","FExpense":"30","FDisAmount":"100","FExplanation":"xx","FCustomAmount":["1","","","","","","","","",""],"FCustomQty":["","","","",""],"FCustomText":["","","","",""],"FCustomItemID":["","","","","","","","","",""]},{"FDate":"2016-10-02","FTypeID":"1","FID":"A0002","FRowIndex":"2","FCustID":"002","FSupplyID":"002","FDeptID":"","FSalesmanID":"","FSettleTypeID":"","FAmount_NoTax":"80","FTax":"3","FExpense":"70","FDisAmount":"30","FExplanation":"yy","FCustomAmount":["","2","","","","","","","",""],"FCustomQty":["","","","",""],"FCustomText":["","","","",""],"FCustomItemID":["","","","","","","","","",""]}]}}';
        return $this->request_api($apiName, $params);
    }

    public function newHandle($apiName = '', $parameters = array()) {
        $parameters['state'] = $this->getRandChar(16);
        $parameters['timestamp'] = date('Y-m-d H:i:s');
        $arr['access_token'] = $this->sign($parameters);
        $arr['client_id'] = $this->client_id;

        $this->state = $parameters['state'];

        $handle = array();
        $handle['type'] = "post";
        $handle['url'] = $this->api_url;
        if (!empty($apiName)) {
            $arr['method'] = $apiName;
        }
        $arr = array_merge($arr, $parameters);

        $handle['body'] = $arr;
        return $handle;
    }

    /**
     * 生成access_token
     * @param array $parameters 参数
     */
    public function sign($parameters) {
        $sign = $parameters['timestamp'] . $this->client_id . $this->client_secret . $this->auth_token . $parameters['state'];
        return strtoupper(md5($sign));
    }

    public function get_state() {
        $str = md5(microtime());
        return substr($str, 0, 16);
    }

    /**
     * 生成状态值
     * @todo 接口参数描述：请求端的状态值（由大于等于16位大小写字母和数字组成，要求每次的随机数不能重复，必须唯一）,
     * @todo 最多128字节，此值将作为返回值原样返回
     */
    function getRandChar($length) {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str.=$strPol[rand(0, $max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    /**
     * 处理系统API返回数据
     * @param array $data 返回数据
     * @return array 返回处理结果
     */
    function deal_system_api_return($data) {
        $data = $this->jsonDecode($data);
        $msg = '';
        if (!isset($data['code'])) {
            $msg = '接口请求失败';
        }
        if ($data['state'] != $this->state) {
            $msg = '接口数据异常';
        }
        if ($data['code'] != '200') {
            $msg = $data['msg'];
        }
        if ($msg != '') {
            return $this->format_ret(-1, '', $msg);
        }
        return $this->format_ret(1, $data['data']);
    }

    /**
     * 处理业务API返回数据
     * @param array $data 返回数据
     * @return array 返回处理结果
     */
    function deal_service_api_return($data) {
        $data = $this->jsonDecode($data);
        $msg = '';
        if (!isset($data['Result'])) {
            $msg = '接口请求失败';
        }
        if ($data['State'] != $this->state) {
            $msg = '接口数据异常';
        }
        if ($data['Result'] != '200') {
            $msg = isset($data['msg']) ? $data['msg'] : $data['ErrMsg'];
        }
        if (isset($data['DataJson']['Result']) && $data['DataJson']['Result'] != '200') {
            $msg = $data['DataJson']['ErrMsg'];
        }
        if ($msg != '') {
            return $this->format_ret(-1, '', $msg);
        }
        return $this->format_ret(1, $data['DataJson']['Data']);
    }

}

//
//array(4) { ["code"]=> int(200) ["msg"]=> string(7) "success" ["state"]=> string(16) "PR8KmKvIu80NbCmk" ["data"]=> array(1) { [0]=> array(4) { ["netid"]=> string(13) "8854307661059" ["prod_name"]=> string(26) "金蝶KIS账务平台 14.0" ["server_url"]=> string(25) "https://kisgz.kingdee.com" ["logintime"]=> string(19) "2016-10-08 15:59:58" } } }
//
//{"code":200,"msg":"success","state":"HRJm2uSp0yGTDhEL","data":[{"netid":"8854307661059","prod_name":"\u91d1\u8776KIS\u8d26\u52a1\u5e73\u53f0 14.0","server_url":"119.29.169.16","logintime":"2016-11-05 09:41:34"}]}