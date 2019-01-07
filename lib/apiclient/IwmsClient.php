<?php
require_lib('apiclient/ApiClient');
class IwmsClient extends ApiClient implements ApiClientInterface
{
    /**
     * @param array $parameters
     */
    public function __construct($parameters = array()) {
        parent::__construct($parameters);
    }

    /**
     * 初始化接口请求所需参数, 根据商店代码
     * @param string $shopCode 商店代码
     * @throws Exception
     */
    public function initByShopCode($shopCode) {
        if(!empty($shopCode)){
           $sql = "SELECT * FROM base_shop_api WHERE shop_code = :shop_code";
           $row = CTX()->db->get_row($sql, array('shop_code'=>$shopCode));
           if(empty($row)) {
               throw new Exception('店铺参数不存在');
           }
           $this->parameters = json_decode($row['api']);
       }
    }

    /**
     * @param $apiName
     * @param $parameters
     * @return array
     */
    public function newHandle($apiName, $parameters) {
        $handle = array();
        //$handle['type'] = 'get';
        $handle['url'] = 'http://localhost/';
        //$handle['headers'] = array();
        //$handle['body'] = array();

        return $handle;
    }

    /**
     * 读取库存(这是一个示例方法)
     * @param $param1
     * @param $param2
     * @return mixed
     * @throws Exception
     */
    public function getStock($param1, $param2) {
        $params = array(
            'param1'=>$param1,
            'param2'=>$param2,
        );

        return $this->exec('api_name', $params);
    }
}
