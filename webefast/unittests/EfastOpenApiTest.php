<?php
header("Content-type: text/html; charset=utf-8");
error_reporting(E_ERROR);
$root_path = substr(__FILE__, 0, strpos(__FILE__, "webefast"));
include $root_path.'/fastapp/boot/req_def.php';
CTX()->app_name = 'webefast';
include $root_path.'/fastapp/boot/req_init.php';

require_lib('util/common_util,util/web_util,util/bui_util', true);
require_lib ( 'net/HttpEx' );

class EfastOpenApiTest  {
	private $serverUrl;
	private $app_key;
	private $secret;
	
	function __construct() {
		$this->serverUrl = $this->getCurrentUrl();
		$this->app_key = 'demo';
		$this->secret = 'demo';
		$this->version = '1.0';
	}
	protected function setUp() {
		parent::setUp();

		$this->serverUrl = $this->getCurrentUrl();
		$this->app_key = 'demo';
		$this->secret = '';
		$this->version = '1.0';
	}
    
    private function getCurrentUrl()
    {
        $url = 'http://'.$_SERVER['SERVER_NAME'] . ((isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 80) ? '' : ':' . $_SERVER["SERVER_PORT"]) . $_SERVER["REQUEST_URI"];
        echo substr(dirname($url), 0, -9) . 'web/?app_act=openapi/router';
        return substr(dirname($url), 0, -9) . 'web/?app_act=openapi/router';
    }
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {

		parent::tearDown();
	}
	
	public function testLogin() {
		
	}
	
	
	function do_request($api, $params) {
		// 增加系统级参数
		$data = array();
		//$data ['m'] = $api;
		$data ['timestamp'] = date ( 'Y-m-d H:i:s' );
		$data ['format'] = 'json';
		$data ['key'] = $this->app_key;
		$data ['v'] = $this->version;
		$data ['sign_method'] = 'md5';
        $data ['kh_id'] = '888';
        $data ['shop_code'] = 'shopping_attb';
        /*$data ['param'] = array(
            'page_no' => 1,
            'page_size' =>50,
        );*/
		
		$sign_data = array_merge($params, $data);
		$sign = $this->sign ( $sign_data );
		$data ['sign'] = $sign;
		
		$url = $this->serverUrl .'&'. http_build_query ( $data );
		$errno = '';
		$errnmsg = '';
		
		$ret = do_http ( $url, $params, $errno, $errmsg );
        $arr = json_decode(trim($ret, chr(239) . chr(187) . chr(191)), true);
		
		//$arr = json_decode($ret, true);
		
        //return $ret;
		return $arr;
	}
	public function sign($param = array()) {
        $sign = $this->secret;
        ksort($param);
        foreach ($param as $k => $v) {
            if ("@" != substr($v, 0, 1)) {
                $sign .= "$k$v";
            }
        }
        unset($k, $v);
        $sign .= $this->secret;
        
        return strtoupper(md5($sign));
    }
}
$time = time();
$conf_execute = array(
    'base.shop.detail' => array('method' => 'base.shop.detail'),
    'prm.goods.add' => array(
        'method' => 'prm.goods.add', 
        'goods_code' => 'luck_code_' . $time, 'goods_name' => 'luck_name_' . $time, 'diy' => 0, 'category_code' => '002',   //必选
        'brand_code' => '003', 'goods_prop' => 0, 'state' => 0,                                                             //必选
        'goods_short_name' => 'luck_short_' . $time, 'goods_produce_name' => 'luck_produce_' . $time,                       //可选
        'season_code' => '002', 'year_code' => '001', 'weight' => '98', 'goods_days' => 10, 
        'goods_desc' => 'luck_desc_' . $time, 'status' => 0, 'is_add_person' => 'test',                                     //可选
        'is_add_time' => date('Y-m-d H:i:s')                                                                                //可选
    ),
    'prm.goods.update' => array(
        'method' => 'prm.goods.update',
        'goods_code' => 'luck_code_1434006533',                                                                             //必选
        'goods_name' => 'luck12345', 'goods_short_name' => '123456', 'goods_produce_name' => '1234', 'diy' => 1,            //可选
        'category_code' => '001', 'brand_code' => '001', 'season_code' => '003', 'year_code' => '002', 'goods_prop' => 1,   //可选
        'state' => 1, 'weight' => '100', 'goods_days' => 2, 'goods_desc' => 'h855', 'status' => 1                           //可选
    ),
    'prm.goods.barcode.add' => array(
        'method' => 'prm.goods.barcode.add',
        'goods_code' => 'test811', 'spec1_code' => '003', 'spec2_code' => '012', 'barcode' => 'test811003012',                //必选
        'weight' => 2.00, 'price' => 1.00, 'gb_code' => 'CN'                                                          //可选
    ),
    'prm.goods.barcode.update' => array(
        'method' => 'prm.goods.barcode.update',
        'goods_code' => 'SP003', 'spec1_code' => '000', 'spec2_code' => '002', 'barcode' => 'SP003000002',                //必选
        'weight' => 2.00, 'price' => 1.00, 'gb_code' => 'CN'                                                          //可选
    ),
    'prm.goods.price.update' => array(
        'method' => 'prm.goods.price.update',
        'goods_code' => 'SP002', 'sell_price' => 100.000,                                                                   //必选
        'cost_price' => 60.000, 'purchase_price' => 50.000, 'trade_price' => 80.000                                         //可选
    ),
    'prm.goods.diy.update' => array(
        'method' => 'prm.goods.diy.update',
        'p_barcode' => 'SP003004000', 'barcode' => 'SP004000001', 'num' => 5                                               //必选
    ),
    'prm.goods.spec1.update' => array(
        'method' => 'prm.goods.spec1.update',
        'spec1_code' => '000', 'spec1_name' => '通用',                                                                    //必选
        'remark' => '好吃001'                                                                                             //可选
    ),
    'prm.goods.spec2.update' => array(
        'method' => 'prm.goods.spec2.update',
        'spec2_code' => '000', 'spec2_name' => '通用',                                                                    //必选
        'remark' => '好玩'                                                                                                //可选
    ),
    'prm.goods.category.update' => array(
        'method' => 'prm.goods.category.update',
        'category_code' => '001', 'category_name' => '化妆水/爽肤水',                                                      //必选
        'remark' => '', 'p_code' => '0'                                                                                    //可选
    ),
    'prm.goods.brand.update' => array(
        'method' => 'prm.goods.brand.update',
        'brand_code' => '0001', 'brand_name' => '雅诗兰黛',                                                                 //必选
        'remark' => '123456'                                                                                                //可选
    ),
    'base.season.update' => array(
        'method' => 'base.season.update',
        'season_code' => '001', 'season_name' => '春季',                                                                   //必选
    ),
    'base.year.update' => array(
        'method' => 'base.year.update',
        'year_code' => '001', 'year_name' => '2015',                                                                       //必选
    ),
    'prm.goods.inv.update' => array(
        'method' => 'prm.goods.inv.update',
        'barcode' => '36453134102000000', 'store_code' => 'cs', 'stock_num' => 2,                                          //必选
        'lof_no' => 'default_lof', 'production_date' => '2030-01-01'                                                       //可选
    ),
    'base.store.update' => array(
        'method' => 'base.store.update',
        'store_code' => '001', 'store_name' => '总仓', 'allow_negative_inv' => 0,                                         //必选
        'shop_contact_person' => '总仓', 'contact_person' => '小A', 'contact_phone' => '13866669999',                     //可选
        'country' => '1', 'province' => '310000', 'city' => '310100000000',                                               //可选
        'district' => '310115000000', 'street' => '310115008000', 'address' => '峨山路66号',                              //可选
        'zipcode' => '20000', 'message' => '好评送优惠券', 'message2' => '扫一扫活动继续'                                 //可选
    ),
    'base.shelf.update' => array(
        'method' => 'base.shelf.update',
        'store_code' => '001', 'shelf_code' => 'kw007', 'shelf_name' => 'kw007',                                          //必选
    ),
    'prm.goods.shelf.add' => array(
        'method' => 'prm.goods.shelf.add',
        'barcode' => 'cssp002002002', 'store_code' => '001', 'shelf_code' => 'kw007',                                     //必选
        'batch_number' => 'default_lof'                                                                                   //可选
    ),
    'oms.order.list.get' => array(
        'method' => 'oms.order.list.get',
        'page' => 1, 'page_size' => 10,                                                                                    //可选
        'delivery_time_start' => '2015-07-01 10:23:25', 'delivery_time_end' => '2015-07-12 11:23:25'                       //可选
        //'sell_record_code' => '1503250001074', 'deal_code_list' => '123',                                                  //可选
        //'store_code', 'shopcode',                                                                                        //可选
        //'customer_code', 'buyer_name', 'receiver_name',                                                                   //可选
        //'receiver_country', 'receiver_province', 'receiver_city',
        //'receiver_district', 'receiver_street', 'receiver_address', 
        //'receiver_addr', 'receiver_zip_code', 'receiver_mobile',
        //'receiver_phone', 'receiver_email', 'express_code', 
        //'express_no', 'buyer_remark', 'goods_num',
        //'express_money', 'payable_money', 'paid_money',
        //'record_time', 'pay_time', 'delivery_time',
        //'page', 'page_size'
        ##
        //'sell_record_code', 'deal_code_list', 'store_code', 'shop_code', 'customer_code', 'buyer_name', 'receiver_name', 
        //'receiver_country', 'receiver_province', 'receiver_city', 'receiver_district', 'receiver_street', 'receiver_address', 'receiver_addr', 
        //'receiver_zip_code', 'receiver_mobile', 'receiver_phone', 'receiver_email', 'express_code', 'express_no', 'buyer_remark', 
        //'goods_num', 'express_money', 'payable_money', 'paid_money', 'record_time', 'pay_time', 'delivery_time',
    ),
    'oms.order.detail.get' => array(
        'method' => 'oms.order.detail.get',
        'sell_record_code' => '1503250001074'                                                                               //必选        
    ),
    'oms.order.return.list.get' => array(
        'method' => 'oms.order.return.list.get',
        'page' => 1, 'page_size' => 10,                                                                                      //可选
        'receive_time_start' => '2015-07-01 10:23:25', 'receive_time_end' => '2015-07-12 11:23:25'                          //可选
    ),
    'oms.order.return.detail.get' => array(
        'method' => 'oms.order.return.detail.get',
        'sell_return_code' => '1503140001153'                                                                               //必选        
    ),
    'open' => array(
        'method' => 'open',                                                                                                 //必选
        'api' => 'taobao.shop.get',                                                                                         //必选
        'shop_code' => 'shopping_attb',                                                                                     //必选
        'fields' => 'sid,cid,title,nick,desc,bulletin,pic_path,created,modified', 'nick' => 'shopping_attb'                 //必选
    ),
    'prm.goods.sku.update' => array(
        'method' => 'prm.goods.sku.update',
        'goods_code' => 'SP003', 'spec1_code' => '000', 'spec2_code' => '002',                                             //必选
        'weight' => 20.00, 'price' => 10.00, 'gb_code' => 'US'                                                               //可选
    ),
);
$e = isset($_REQUEST['e']) && !empty($_REQUEST['e']) ? $_REQUEST['e'] : 'base.shop.detail';
$e = isset($conf_execute[$e]) && !empty($conf_execute[$e]) ? $conf_execute[$e] : $conf_execute['base.shop.detail'];
$tester = new EfastOpenApiTest();
//var_export($tester->do_request('basic.shop_detail', array('kh_id'=>'888', 'shop_code'=>'tb000')));
echo'<pre>';print_r($tester->do_request('base.shop.detail', $e));
//local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=base.shop.detail
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.add                                           //添加商品
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.update                                        //更新商品
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.barcode.add                                   //添加条码
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.barcode.update                                //更新条码
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.price.update                                  //更新价格
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.diy.update                                    //更新组合明细
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.spec1.update                                  //更新规格1
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.spec2.update                                  //更新规格2
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.category.update                               //更新产品类别
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.brand.update                                  //更新产品品牌
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=base.season.update                                      //更新季节
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=base.year.update                                        //更新年份
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.inv.update                                    //更新产品库存
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=base.store.update                                       //更新仓库
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=base.shelf.update                                       //更新库位
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.shelf.add                                     //添加库位对产品关系
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=oms.order.list.get                                      //已发货订单列表
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=oms.order.detail.get                                    //已发货订单明细
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=oms.order.return.list.get                               //已收货退货单列表
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=oms.order.return.detail.get                             //已收货退货单明细
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=open                                                    //第三方接口测试
//http://local.fastpp.com/webefast/unittests/EfastOpenApiTest.php?e=prm.goods.sku.update                                    //更新产品明细