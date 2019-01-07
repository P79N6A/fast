<?php

require_model('tb/TbModel');

class ApiShopModel extends TbModel {

    public function get_table() {
        return 'base_shop_api';
    }

    /**
     * 暂时废弃
     * @param $shops
     * @return array
     */
    public function add($shops) {
        foreach ($shops as $item) {
            $common = array();
            $common['kh_id'] = $item['sd_kh_id'];
            $common['shop_code'] = $item['sd_name'];//注意，该字段与shop_name 一致
            $common['shop_name'] = $item['sd_name'];
            $common['nick'] = $item['sd_nick'];
            $common['source'] =  $item['pt_code']; //店铺所在平台类型
            $common['extra_params'] = $this->generate_extra_params($item['pt_code']);
            $common['app_key'] = $item['app_key']; //key
            $common['app_secret']= $item['app_secret']; //secret
            $common['session_key'] = $item['sd_top_session']; //店铺相关session
            $return = $this->insert($common);
        }
        return $return;
    }
            
    /**
     * 根据客户ID和店铺code获取客户该店铺所在平台的的授权信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-11
     * @param int $kh_id 运营平台客户ID
     * @param string $shop_code 店铺Code
     * @param string $source 来源
     * @return array 返回授权信息
     */
    public function get_token_by_kehu_and_code($kh_id, $shop_code, $source = 'taobao'){
        $shop = $this->get_row(array(
            'kh_id' => $kh_id,
            'shop_code' => $shop_code,
            'source' => $source,
        ));
        //@TODO shop信息没取到需要异常处理
        
        //根据不同平台组装授权信息数组 此字段已作废，统一修改为从base_shop_api的api字段中进行获取
        //$extra_params = empty($shop['data']['extra_params']) ? array() : json_decode($shop['data']['extra_params'], 1);
        $api = empty($shop['data']['api']) ? array() : json_decode($shop['data']['api'], 1);
        
        switch ($shop['data']['source']){
            case 'jingdong':
            case 'Jingdong':
            case 'jd':
                $token = array(
                    'source' => 'jingdong',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'access_token' => $api['session'],
                    //订单处理类型
                    'type' => $api['type'],
                    //热敏打印CODE
                    'customerCode' => isset($api['customerCode'])?$api['customerCode']:'',
                );
                break;
            case 'yihaodian':
                $token = array(
                    'source' => 'yihaodian',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'session' => $api['session'],
                );
                break;
            case 'dangdang':
                $token = array(
                    'source' => 'dangdang',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'session' => $api['session'],
                );
                break;
            case 'youzan':
                $token = array(
                    'source' => 'youzan',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'session' => $api['session'],
                    'seller_nick' => $api['nick'],
                );
                break;
            case 'weidian':
                $token = array(
                    'source' => 'weidian',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'access_token' => $api['session'],
                    'seller_nick' => $api['nick'],
                );
                break;
            case 'yintai':
                $token = array(
                    'source' => 'yintai',
                    'AppKey' => $api['app_key'],
                    'SecrectKey' => $api['app_secret'],
                    'ClientId' => $api['ClientId'],
                    'ClientName' => $api['ClientName'],
                    'vendorID' => $api['vendorID'],
                    'vendorName' => $api['vendorName'],
                    'nick' => $api['nick']
                );
                break;
            case 'weimob':
                $token = array(
                    'source' => 'weimob',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'access_token' => $api['session'],
                    'seller_nick' => $api['nick'],
                );
                break;
            case 'beibei':
                $token = array(
                    'source' => 'beibei',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'session' => $api['session'],
                    'seller_nick' => $api['nick'],
                );
                break;
            case 'aliexpress':
                $token = array(
                    'source' => 'aliexpress',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'access_token' => $api['session'],
                );
                break;
            case 'vipshop':
                $token = array(
                    'source' => 'vipshop',
                    'vis_sid' => $api['app_key'],
                    'vis_source' => $api['session'],
                    'vendor_id' => $api['app_secret'],
                    'type' =>$api['type'],
                );
                break;
            case 'yamaxun':
                $token = array(
                    'source' => 'yamaxun',
                    'AWSAccessKeyId' => $api['app_key'],
                    'AWSAccessKeyValue' => $api['app_secret'],
                    'MWSAuthToken' => $api['session'],
                    'SellerId'=> $api['SellerId'],
                    'MarketplaceId' => $api['MarketplaceId']
                );
                break;
            case 'jumei':
                $token = array(
                    'source' => 'jumei',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'session' => $api['session'],
                    'seller_nick' => $api['nick'],
                );
                break;
            case 'paipai':
                $token = array(
                    'source' => 'paipai',
                    'uin' =>$api['uin'],//qq号
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'session' => $api['session'],
                    'seller_nick' => $api['nick'],
                );
                break;
            case 'iwms':
                $token = array(
                    'source' => 'iwms',
                    'accesskey' => $api['accesskey'],
                    'app_secret' => $api['app_secret'],
                    'api_url' => $api['api_url']
                );
                break;
            case 'meilishuo':
                $token = array(
                    'source' => 'meilishuo',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'session' => $api['session'],
                );
                break;
            case 'mogujie':
                $token = array(
                    'source' => 'mogujie',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'session' => $api['session'],
                );
                break;
			case 'zhe800':
                $token = array(
                    'source' => 'zhe800',
                    'authorize_token' => $api['authorize_token'],
                    'api_token'       => $api['api_token'],
                );
                break;
            case 'taobao':
            case 'Taobao':
            default :
                $token = array(
                    'source' => 'taobao',
                    'app_key' => $api['app_key'],
                    'secret' => $api['app_secret'],
                    'session' => $api['session'],
                    'nick' => $api['nick'],
                    //使用API模式还是RDS推送模式
                    'mode' => isset($api['current_mode'])?$api['current_mode']:'rds',
                    'current_mode' => isset($api['current_mode'])?$api['current_mode']:'rds',
                );
                //沙盒测试判断
                if(isset($api['sandbox'])&&$api['sandbox']){
                    $token['sandbox'] = true;
                }
                break;
        }
        
        return $token;
    }

	/**
	 * iwms使用
	 * @param $store_code
	 */
	public function get_token_by_store_code($store_code){
		//$this -> table = 'sys_api_shop_store';
		$sql = 'select * from sys_api_shop_store as ss left join wms_config as wc on ss.p_id=wc.wms_config_id
                    where ss.shop_store_code=:store_code  and ss.p_type=1 AND ss.shop_store_type=1';
		$data = CTX()->db->get_row($sql, array(':store_code'=>$store_code));

		$api = empty($data['wms_params']) ? array() : json_decode($data['wms_params'], true);
		$token = array(
			'source' => 'iwms',
			'accesskey' => $api['accesskey'],
			'app_secret' => $api['secretcode'],
			'api_url' => $data['wms_address']
		);
		return $token;
	}
  	/**
	 * bserp|bs3000+使用
	 * @param $store_code
	 */
	public function get_token_by_store_code_for_bs($store_code){
		//$this -> table = 'sys_api_shop_store';
		$sql = "select * from sys_api_shop_store  ss left join bserp_config  bc on ss.p_id=bc.bserp_config_id
                    where ss.shop_store_code=:store_code and ss.p_type=0 AND ss.shop_store_type=1";
		$data = CTX()->db->get_row($sql, array(':store_code'=>$store_code));

		$api = empty($data['bserp_params']) ? array() : json_decode($data['bserp_params'], true);
		$token = array(
			'source' => $data['bserp_system_code'],
			'app_key' => $api['app_key'],
			'api_url' => $api['api_url']
		);
		return $token;
	}
  
}