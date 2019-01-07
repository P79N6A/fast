<?php

require_lib('apiclient/ApiClient');
require_lib('util/crm_util');

class JdClient extends ApiClient implements ApiClientInterface {

    /**
     * 应用的app_key
     * @var string
     */
    private $app_key;

    /**
     * 加密密钥
     * @var string
     */
    private $app_secret;

    /**
     * 采用OAuth授权方式为必填参数
     * @var string
     */
    private $access_token;

    /**
     * API协议版本，可选值:2.0
     * @var string 
     */
    private $version = "2.0";
    private $api_url = 'https://api.jd.com/routerjson';

    /**
     * 店铺昵称
     * @var string
     */
    private $nick = '';

    /**
     * 商家编码(POP商家ID)
     * @var string
     */
    public $vender_code;

    /**
     * @var PDODB
     */
    private $db;
    private $api_response_conf = [];

    /**
     * 构造函数
     * @param string $shop_code 店铺代码
     */
    public function __construct($shop_code = '') {
        $this->db = $GLOBALS['context']->db;
        if (!empty($shop_code)) {
            $sql = "SELECT * FROM base_shop_api WHERE shop_code = :shop_code";
            $shop_api = $this->db->get_row($sql, [':shop_code' => $shop_code]);
            if (empty($shop_api['api'])) {
                return; // 无商店参数时不处理赋值
            }
            $api = json_decode($shop_api['api']);

            if (isset($api->app_key)) {
                $this->app_key = $api->app_key;
            }
            if (isset($api->app_secret)) {
                $this->app_secret = $api->app_secret;
            }
            if (isset($api->session)) {
                $this->access_token = $api->session;
            }
            if (isset($api->nick)) {
                $this->nick = $api->nick;
            }
            if (isset($shop_api['vender_code'])) {
                $this->vender_code = $shop_api['vender_code'];
            }
        }

        $this->setResponseConf();
    }

    private function setResponseConf() {
        $this->api_response_conf = array(
            'jingdong.seller.vender.info.get' => 'jingdong_seller_vender_info_get_responce',
            'jingdong.ldop.alpha.provider.sign.success.info.get' => 'jingdong_ldop_alpha_provider_sign_success_info_get_responce',
            'jingdong.ldop.alpha.vendor.stock.queryByProviderCode' => 'jingdong_ldop_alpha_vendor_stock_queryByProviderCode_responce',
            'jingdong.ldop.alpha.waybill.receive' => 'jingdong_ldop_alpha_waybill_receive_response',
            'jingdong.ldop.alpha.waybill.api.unbind' => 'jingdong_ldop_alpha_waybill_api_unbind_responce',
            'jingdong.ldop.alpha.vendor.bigshot.query' => 'jingdong_ldop_alpha_vendor_bigshot_query_responce',
        );
    }

    /**
     * 获取商家编码(POP商家ID)
     * @param $params
     * @return array
     */
    public function sellerVenderInfoGet($params = []) {
        $params = ['ext_json_param' => ''];
        $method = 'jingdong.seller.vender.info.get';
        $resp = $this->exec($method, $params);

        $this->method = $method;
        return $this->get_response($resp);
    }

    /**
     * 根据商家编码查询商家所有审核成功的签约信息
     * @param $params
     * @return array
     */
    public function ldopAlphaProviderSignSuccessInfoGet($params = []) {
        $params['venderCode'] = $this->vender_code;
        $method = 'jingdong.ldop.alpha.provider.sign.success.info.get';
        $resp = $this->exec($method, $params);

        $this->method = $method;
        return $this->get_response($resp);
    }

    /**
     * 商家单号库存查询
     * @param $params
     * @return array
     */
    public function ldopAlphaVendorStockQueryByProviderCode($params) {
        $params['vendorCode'] = $this->vender_code;
        $method = 'jingdong.ldop.alpha.vendor.stock.queryByProviderCode';
        $resp = $this->exec($method, $params);

        $this->method = $method;
        return $this->get_response($resp);
    }

    /**
     * 京东无界电子面单接单接口
     * @param $params
     * @return array
     */
    public function ldopAlphaWaybillReceive($params) {
        $method = 'jingdong.ldop.alpha.waybill.receive';

        foreach ($params as $_key => $_value) {
            $_value['vendorCode'] = $this->vender_code;
            $_value['vendorName'] = $this->nick;
            $outer_params = ['content' => $_value];
            $handles[$_key] = $this->newHandle($method, $outer_params);
        }

        $resp = $this->multiExec($handles);

        return $resp;
    }

    /**
     * 大头笔信息查询接口
     * @param array $params
     * @return array
     */
    public function ldopAlphaVendorBigshotQuery($params) {
        $method = 'jingdong.ldop.alpha.vendor.bigshot.query';
        $resp = $this->exec($method, $params);

        $this->method = $method;
        return $this->get_response($resp);
    }

    /**
     * 订运关系解绑接口
     * @param array $params
     * @return array
     */
    public function ldopAlphaWaybillApiUnbind($params) {
        $method = 'jingdong.ldop.alpha.waybill.api.unbind';
        $resp = $this->exec($method, $params);

        $this->method = $method;
        return $this->get_response($resp);
    }

    /**
     * 请求数据
     * @param $method 接口名
     * @param $parameters 业务参数
     * @return array
     */
    public function newHandle($method, $parameters) {
        $jsonparams = !empty($parameters) ? json_encode($parameters) : '{}';
        $parameters = ["360buy_param_json" => $jsonparams];

        $sysParams = [
            'app_key' => $this->app_key,
            'access_token' => $this->access_token,
            'format' => 'json',
            'v' => $this->version,
            'method' => $method,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $sysParams = array_merge($sysParams, $parameters);
        $sysParams['sign'] = $this->sign($sysParams);

        $handle = [];
        $handle['url'] = $this->buildUrl($sysParams);
        $handle['body'] = $parameters;
        return $handle;
    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($param = array()) {
        //所有请求参数按照字母先后顺序排序
        ksort($param);
        //定义字符串开始 结尾所包括的字符串
        $stringToBeSigned = $this->app_secret;
        //把所有参数名和参数值串在一起
        foreach ($param as $k => $v) {
            $stringToBeSigned .= "$k$v";
        }
        unset($k, $v);
        //把venderKey夹在字符串的两端
        $stringToBeSigned .= $this->app_secret;
        //使用MD5进行加密，再转化成大写
        return strtoupper(md5($stringToBeSigned));
    }

    /**
     * 组装并生产url
     * @param  $params 系统级参数
     * @return void
     */
    public function buildUrl($params) {
        $requestUrl = $this->api_url . "?";
        foreach ($params as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, - 1);
        return $requestUrl;
    }

    public function get_response($resp) {
        $result = json_decode($resp, true);

        if (empty($result)) {
            return $this->format_ret(-1, '', '接口返回数据有错：' . $resp);
        }

        if (!empty($result['error_response'])) {
            return $this->format_ret(-1, '', '接口返回数据有错：' . $result['error_response']['zh_desc']);
        }

        $ret_status = 1;
        $ret_data = array();
        $msg = '';
        $key = $this->api_response_conf[$this->method];

        if (isset($result[$key])) {
            $ret_data = $result[$key];
        } else {
            $ret_status = -1;
            $msg = "接口返回数据解析异常";
        }

        if (isset($ret_data['resultInfo'])) {
            $resultInfo = $ret_data['resultInfo'];
            if ($resultInfo['statusCode'] != 0) {
                $ret_status = -1;
                $msg = '接口错误：' . $resultInfo['statusMessage'];
            } else {
                $ret_data = $resultInfo['data'];
            }
        }


        return $this->format_ret($ret_status, $ret_data, $msg);
    }

}
