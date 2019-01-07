<?php
//require_lib('util/web_util');
//require_lib('keylock_util');
require_lib('apiclient/Validation');
require_lib('openapi/OpenAPIModel');
//require_lib('Exceptions/ExtException');
//require_lib('DecryptRequest');

//require_model('ApiKehuModel');
require_model('open/ApiShopModel');
//require_model('ApiScheduleQueueModel');

require_model('tb/TbModel');

class OpenModel extends TbModel{
    
    protected $kh_id;
    
    public function __construct() {
    }
    /**
     * 代理接口
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-27
     * @return 
     */
    public function proxy($param){
        $arr_deal = $param;
        unset($param);
        $send_arr = array();
        
        //删除API中无需字段
        $del_key = array(
            'timestamp', 'format', 'key', 'v', 'sign_method', 'method'
        );
        foreach ($arr_deal as $key => $val) {
            if (in_array($key, $del_key)) {
                continue;
            } else {
                $send_arr[$key] = $val;
            }
        }
        //通过店铺代码获取API数据
        $API = OpenApiModel::Factory_by_shopcode($arr_deal['shop_code']);
        //直接转发
        try {
            return $return = $API->request_send($arr_deal['api'], $send_arr);
        } catch(ExtException $e) {
            return $this->format_ret($e->getCode(), null, $e->getMessage());
        }
    }
}