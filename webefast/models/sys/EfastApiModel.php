<?php

/*
 * @example
 * $apiName = 'chuchujie_api/trade_shipping_sync';
 * $params['sd_id'] = 39 ;
 * $params['tid'] = 135 ;
 *
 *  load_model('sys/EfastApiModel')->request_api($apiName, $params);
 */

//   http://121.41.163.99/efast_api/webservice/web/index.php?app_fmt=json&app_act=chuchujie_api/trade_shipping_sync&sd_id=39&kh_id=1256&mode=web&tid=123
require_lib('apiclient/ApiClient');
require_lib('util/crm_util');

class EfastApiModel extends ApiClient implements ApiClientInterface {

    protected $api_url = '';

    function __construct() {
        parent::__construct();
        $this->set_api_url();
    }

    function set_api_url() {
        $conf = require_conf('api_url');
        if (isset($conf['efast_api'])) {
            $this->api_url = $conf['efast_api'];
        }
    }

    function request_api($apiName, $params) {
        if (empty($apiName)) {
            return $this->format_ret(-1, '', '缺少方法参数');
        }

        if (empty($this->api_url)) {
            return $this->format_ret(-1, '', '请配置efast_api');
        }

        $response = $this->exec($apiName, $params);

        return $this->jsonDecode($response);
    }

    public function newHandle($apiName, $parameters) {

        $arr['app_fmt'] = 'json';

        $arr['app_act'] = $apiName;
        $arr['kh_id'] = CTX()->saas->get_saas_key();
        $arr['mode'] = 'web';

        $handle = array();
        $handle['type'] = "post";
        $handle['url'] = $this->api_url;
        $arr = array_merge($arr, $parameters);

        $handle['body'] = $arr;
        return $handle;
    }

}

?>
