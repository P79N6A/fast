<?php

require_lib('apiclient/ApiClient');
require_lib('util/crm_util');

class WphClient extends ApiClient implements ApiClientInterface {

       private $app_url = 'http://vipapis.com'; //正式
//    private $app_url = 'http://sandbox.vipapis.com/'; //测试
    private $service = 'vipapis.normal.ProductInventoryService';
    private $version = '1.0.0';

    /**
     * @var string
     */
//    private $appKey = 'a876c4cc';
    private $appKey = '8b16173d';

    /**
     * @var string
     */
//    private $appSecret = '77780A5819EC3CFBE648436DB9F95492';
    private $appSecret = '270501C3C0B085910764F1901545D3A1';

    /**
     * @var string
     */
    private $accessToken = '';

    /**
     * @var string
     */
    private $vendor_id = '';

    /**
     * @var PDODB
     */
    private $db;

    /**
     * @param string $shop_code
     */
    public function __construct($shop_code) {
        $this->db = &$GLOBALS['context']->db;

        $sql = "select * from base_shop_api where shop_code = :shop_code";
        $shop_api = $this->db->get_row($sql, array(":shop_code" => $shop_code));
        if (empty($shop_api['api'])) {
            return; // 无商店参数时不处理赋值
        }
        $this->shop_code = $shop_code;
        $api = json_decode($shop_api['api']);

        if (isset($api->app_key)) {
            $this->appKey = $api->app_key;
        }
        if (isset($api->app_secret)) {
            $this->appSecret = $api->app_secret;
        }
        if (isset($api->session)) {
            $this->accessToken = $api->session;
        }
        if (isset($api->vendor_id)) {
            $this->vendor_id = $api->vendor_id;
        }

        parent::__construct();
    }

    /**
     * @param $api
     * @param $param
     * @return mixed
     * @throws Exception
     */
    function getWphData($api, $param) {

        try {
            $result = $this->exec($api, $param);
        } catch (Exception $e) {

            $data['status'] = -1;
            $data['message'] = '唯品会接口请求出错:' . $e->getMessage();
            return $data;
        }
        $data = json_decode($result, TRUE);

        return $data;
    }

    /**
     * @param $apiName
     * @param $parameters
     * @return array
     */
    public function newHandle($apiName, $parameters) {

        $arr = $this->createUrlParam($apiName, $parameters);

        $baseStr = json_encode($parameters);
        $arr['sign'] = $this->createSign($arr, $baseStr);


        $handle = array();
        $handle['type'] = "post";
        $handle['url'] = $this->buildUrl($arr);
        $handle['body'] = $baseStr;

        $handle['headers'] = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($baseStr)
        );

        return $handle;
    }

    public function buildUrl($params) {
        $requestUrl = $this->app_url . "?";
        foreach ($params as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . $sysParamValue . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);
        return $requestUrl;
    }

    /**
     * 合并系统参数
     * @param $apiName
     * @param $param
     * @return array
     */
    function createUrlParam($apiName, $param) {
        list($t1, $t2) = explode(' ', microtime());
        $time = (float) (floatval($t1) + floatval($t2)) * 1000;
        $timestamp = round($time / 1000);
        $url_param = array(
            "service" => $this->service,
            "method" => $apiName,
            "version" => $this->version,
            "timestamp" => $timestamp,
            "format" => "JSON",
            "appKey" => $this->appKey,
        );
        if (!empty($this->accessToken)) {
            $url_param['accessToken'] = $this->accessToken;
        }

        return $url_param;
    }

    /**
     * 签名函数
     * @param $paramArr
     * @param $baseStr string
     * @return string
     */
    function createSign($paramArr, $baseStr) {
        ksort($paramArr);
        $sign_data = '';
        foreach ($paramArr as $key => $val) {
            $sign_data .= "$key$val";
        }
        $sign_data .= $baseStr;
        //  var_dump($sign_data, $this->appSecret);die;
        $sign = strtoupper(hash_hmac('md5', $sign_data, $this->appSecret));

        return $sign;
    }

    /**
     * 专场信息查询
     * @see http://vop.vip.com/apicenter/method?serviceName=vipapis.sales.SalesService-1.0.0&methodName=getSalesList
     * @author wq  2016-11-29 下午11:57:56
     * @param array $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function getSalesList($params) {
        //vendor_id	Integer	是	550	供应商id
        //st_query	Long	否		开始查询时间，以秒为单位，默认为最近30天
        //et_query	Long	否		结束查询时间，以秒为单位，默认为当前系统时间
        //page	Integer	否	1		页码
        //limit	Integer	否	50		每页查询条数
        if (!isset($params['vendor_id'])) {
            $params['vendor_id'] = $this->vendor_id;
        }
        $this->service = 'vipapis.sales.SalesService';
        $m = 'getSalesList';
        $outer_param = $params;

        $data = $this->getWphData($m, $outer_param);

        return $data;
    }

    /**
     * 获取专场sku列表
     * @see http://vop.vip.com/apicenter/method?serviceName=vipapis.sales.SalesService-1.0.0&methodName=getSalesSkuList
     * @author wq  2016-11-29 下午11:57:56
     * @param array $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function getSalesSkuList($params) {
        //vendor_id	Integer	是			供应商id
        //sales_no	Long	是			专场id
        //page	Integer	否			页码
        //limit	Integer	否			每页查询条数
        $this->service = 'vipapis.sales.SalesService';
        $params['vendor_id'] = $this->vendor_id;
        $m = 'getSalesSkuList';
        $outer_param = $params;

        $data = $this->getWphData($m, $outer_param);
        return $data;
    }

    /**
     * 获取待售专场SKU列表，目标SKU当前不在其他专场售卖
     * @see http://vop.vip.com/apicenter/method?serviceName=vipapis.sales.SalesService-1.0.0&methodName=getUpcomingSalesSkus
     * @author wq  2016-11-29 下午11:57:56
     * @param array $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function getUpcomingSalesSkus($params) {
        //vendor_id	Integer	是			供应商名称
        //brand_id	String	否			品牌ID
        //page	Integer	是			页码
        //limit	Integer	是			每页查询条数(最多支持200条)
        $params['vendor_id'] = $this->vendor_id;
        $this->service = 'vipapis.sales.SalesService';
        $m = 'getUpcomingSalesSkus';
        $outer_param = $params;

        $data = $this->getWphData($m, $outer_param);
        return $data;
    }

    /**
     * 更新专场SKU的库存，目标库存是独享库存
     * @see http://vop.vip.com/apicenter/method?serviceName=vipapis.sales.SalesService-1.0.0&methodName=updateSalesSkusInventory
     * @author wq  2016-11-29 下午11:57:56
     * @param array $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function updateSalesSkusInventory($params) {
        //batch_no	Integer	是			批次号
        //vendor_id	Integer	是	550		供应商id
        //is_full	Boolean	是	全量同步：true，增量同步：false		首次采用此接口进行库存同步必须是全量同步，再次同步必须是增量同步
        //warehouse_supplier	String	否			供应商仓库编码
        //inventories	List<BarcodeInventory>	是			商品库存数，最大50条记录
        $params['vendor_id'] = $this->vendor_id;
        $params['batch_no'] = $this->get_batch_no();
        $this->service = 'vipapis.sales.SalesService';
        $m = 'updateSalesSkusInventory';
        $outer_param = $params;

        $data = $this->getWphData($m, $outer_param);
        return $data;
    }

    private function get_batch_no() {
        return $this->vendor_id . substr(time(), -6) . uniqid();
    }

}
