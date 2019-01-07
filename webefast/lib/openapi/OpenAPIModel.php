<?php

/**
 * 向第三方平台通信的工厂类
 *
 * @author jhua.zuo<jhua.zuo@baisonmail.com>
 * @date 2015-03-09
 */
class OpenAPIModel {
    /**
     * 第三方平台标识符
     * @var array
     */
    static public $source = array(
        'Taobao' => '淘宝',
        'Jingdong' => '京东',
        'Vipshop' => '唯品会',
        'Yihaodian' => '一号店',
        'Youzan' => '有赞',//之前的口袋通
        'Weidian' => '微店',
        'Weimob' => '微盟',
        'Beibei' => '贝贝网',
        'Aliexpress' => '速卖通',
        'Yintai' => '银泰',
        'Yamaxun' => '亚马逊',
        'Meilishuo' => '美丽说',
        'Dangdang' => '当当',
        'Mogujie' => '蘑菇街',
    );
    
    /**
     * 第三方平台API接口工厂方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @param string $source API平台标识符
     * @param array $token 第三方平台授权信息
     * @return AbsAPIModel 实例化后的第三方平台API类
     */
    static public function Factory($source, $token=array()){
        $Source = ucfirst($source); //将taobao转化为Taobao
        $className = $Source.'APIModel';
        require_lib('openapi/'.$className);
        $M = new $className($token);
        return $M;
    }
    
    /**
     * 根据店铺code实例化API接口
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-13
     * @param string $kh_id 运营中心客户ID
     * @param string $shop_code 店铺代码
     * @param string $source 店铺类型，可以不填
     * @return object $API
     */
    static public function Factory_by_shopcode($shop_code){
        $M = load_model('open/ApiShopModel');;
        $shop = $M->get_row(array('shop_code' => $shop_code));
        $token = $M->get_token_by_kehu_and_code($shop['data']['kh_id'], $shop_code, $shop['data']['source']);
        $API = self::Factory($shop['data']['source'], $token);
        return $API;
    }

	/**
	 * 根据store_code实例化API接口(iwms使用)
	 * @param $store_code
	 * @return AbsAPIModel
	 * @throws Exception
	 */
	static public function Factory_by_storecode($store_code) {

		$M = load_model('open/ApiShopModel');
		$token = $M->get_token_by_store_code($store_code);
		$API = self::Factory($token['source'], $token);
		return $API;
	}
        /**
	 * 根据store_code实例化API接口(iwms使用)
	 * @param $store_code
	 * @return AbsAPIModel
	 * @throws Exception
	 */
	static public function Factory_by_storecode_for_bs($store_code) {
		$M = load_model('open/ApiShopModel');
		$token = $M->get_token_by_store_code_for_bs($store_code);
		$API = self::Factory($token['source'], $token);
		return $API;
	}
}