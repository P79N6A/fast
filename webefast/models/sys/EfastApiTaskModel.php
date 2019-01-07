<?php
    /*
     * @example
     * $apiName = 'chuchujie_api/trade_shipping_sync';
     * $params['sd_id'] = 39 ;
     * $params['tid'] = 135 ;
     * 
     *  load_model('sys/EfastApiModel')->request_api($apiName, $params);
     */
require_lib('apiclient/ApiClient');
require_lib('util/crm_util');
class EfastApiTaskModel  extends ApiClient implements ApiClientInterface{
    protected $api_url = '';
    function __construct() {
        parent::__construct();
        $this->set_api_url();
    }
    function set_api_url(){
        $conf = require_conf('api_task_url');
        if(isset($conf['efast_api'])){
            $this->api_url = $conf['efast_api'];
        }
    }
 
    function request_api($apiName, $params) {
        if(empty($apiName)){
            return $this->format_ret(-1,'','缺少方法参数');
            
        }
        
         if(empty($this->api_url)){
              return $this->format_ret(-1,'','请配置efast_api');
         }
         
        $response = $this->exec($apiName, $params);
        
        return $this->jsonDecode($response);
      
    }
    public function newHandle($apiName, $parameters) {

        $arr['act'] = $apiName;
        $arr['kh_id'] = CTX()->saas->get_saas_key();

        $handle = array();
        $handle['type'] = "post";
        $handle['url'] = $this->api_url;
        $arr = array_merge($arr,$parameters);
        
        $handle['body'] = $arr;
        return $handle;
    } 

}
?>
