<?php
require_lib('util/web_util', true);
require_lib('util/taobao_util', true);
require_model('api/ApiClientModel');
require_model('api/ApiShopModel');
require_model('api/ApiClientloginModel');
require_model('api/ApiProductorderModel');


//ini_set('display_errors', 0);

class tbauth {

	function __construct() {

		$this->debug = false;

		$this->init_pw = 'baison@2014';

		$this->auth_conf = require_conf('auth');

//		$this->app_key = '23052403';
//		$this->app_secret = '7233cccc5ffc1f4b13f9929b5c0f669b';
//
//		//测试app_key
//		$this->app_key = '12651526';
//		$this->app_secret = '11b9128693bfb83d095ad559f98f2b07';

		$this->store_code = '002';

		$this->server_conf = $this->auth_conf['servers'];
		$this->top_app_keys = $this->auth_conf['top_app_keys'];

		//订购服务商品编码
		$this->article_code = null;
		//订购服务收费项目代码
		$this->item_code = null;

		$this->taobao_nick = null;

		$this->login_name = null;

		//用户session
		$this->access_token = null;

		//回调，用于从产品去授权
		$this->_call_back_kehu_code = null;
		$this->_call_back_sd_name = null;

		$this->tb_shop_type = 'C';//默认店铺类型C店
	}

	/**
	 * 淘宝回调入口
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function auth(array & $request, array & $response, array & $app) {

		$app['tpl'] = 'tbauth_auth'; //显式指定view页面

		$request['order_type'] = '_by_time'; //收费类型

		if (isset($request['state']) && '' == $request['state']) {
			$state = urldecode($request['state']);

			$state_arr = explode('|,--,|', $state);
			if (count($state_arr)>1) {
				$this->_call_back_kehu_code = explode('=', $state_arr[0]);
				$this->_call_back_kehu_code = $this->_call_back_kehu_code[1];
				$this->_call_back_sd_name = explode('=', $state_arr[1]);
				$this->_call_back_sd_name = $this->_call_back_sd_name[1];
			}
		}

		$request['_call_back_kehu_code'] = $this->_call_back_kehu_code;
		$request['_call_back_sd_name'] = $this->_call_back_sd_name;

		CTX()->set_session('request', $request);

		$flag = $this->_check_status($request, $response, $app);
		$response = $flag;
	}

	/**
	 * 淘宝回调入口
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function auth_by_order(array & $request, array & $response, array & $app) {

//		$app['tpl'] = 'tbauth_auth'; //显式指定view页面
//
//		$request['order_type'] = '_by_order'; //收费类型
//
//		CTX()->set_session('request', $request);
//
//		$flag = $this->_check_status($request, $response, $app);
//		$response = $flag;

		$app['tpl'] = 'tbauth_auth'; //显式指定view页面

		$request['order_type'] = '_by_order'; //收费类型

		if (isset($request['state']) && '' == $request['state']) {
			$state = urldecode($request['state']);

			$state_arr = explode('|,--,|', $state);
			if (count($state_arr)>1) {
				$this->_call_back_kehu_code = explode('=', $state_arr[0]);
				$this->_call_back_kehu_code = $this->_call_back_kehu_code[1];
				$this->_call_back_sd_name = explode('=', $state_arr[1]);
				$this->_call_back_sd_name = $this->_call_back_sd_name[1];
			}
		}

		$request['_call_back_kehu_code'] = $this->_call_back_kehu_code;
		$request['_call_back_sd_name'] = $this->_call_back_sd_name;

		CTX()->set_session('request', $request);

		$flag = $this->_check_status($request, $response, $app);
		$response = $flag;
	}

	/**
	 * 监测是否系统内已经存在该用户
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 * @return array
	 */
	function _check_status(array & $request, array & $response, array & $app) {

		//用哪个app_key
		$this->order_type = $request['order_type'];

		//支持多个key  2014-09-04
		$this->app_key = $this->top_app_keys[$this->order_type]['top_app_key'];
		$this->app_secret = $this->top_app_keys[$this->order_type]['top_app_secret'];

		$result_info = $this->tb_authorize($request, $response, $app);

		$result_info = json_decode($result_info, true);

//		$result_info = array(
//			'taobao_user_nick' => 'shopping_attb',
//			'refresh_token' => '6202823a9fec7013cb61dcd50339a56ZZ18da2ef3591f9758123788',
//			'access_token' => '6202823a9fec7013cb61dcd50339a56ZZ18da2ef3591f9758123788'
//		);

		CTX()->log_error('result_info'.print_r($result_info,true));

		if (!isset($result_info['taobao_user_nick']) || !$result_info['taobao_user_nick']) {
			return $response = $this->return_value(-2, '授权失败!!');
		}

		//淘宝授权数据
		CTX()->set_session('result_info', $result_info);

		$result_info['taobao_user_nick'] = urldecode($result_info['taobao_user_nick']);

		$this->login_name = $this->taobao_nick = $result_info['taobao_user_nick'];

		//判断是否存在!
		$mdl_shop = new ApiShopModel();
		$__shop_info = $mdl_shop->is_exists($this->taobao_nick, 'sd_nick');

		if (!empty($__shop_info['data'])) {
			return $response = $this->return_value(1, '登录中，请稍等');
		} else {
			return $response = $this->return_value(1, '初次登录，系统需要做如下操作：1、创建数据库，2、初始化数据，3、初始化用户，此过程大概持续2分钟，还请耐心等待！');
		}
	}

	/**
	 * 授权流程
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_auth(array & $request, array & $response, array & $app) {

		$app['page'] = 'NULL';
		$app['fmt'] = 'json';

		$request = CTX()->get_session('request');
		//用哪个app_key
		$this->order_type = $request['order_type'];

		$this->_call_back_kehu_code = $request['_call_back_kehu_code'];
		$this->_call_back_sd_name = $request['_call_back_sd_name'];

		//支持多个key  2014-09-04
		$this->app_key = $this->top_app_keys[$this->order_type]['top_app_key'];
		$this->app_secret = $this->top_app_keys[$this->order_type]['top_app_secret'];
		//订购服务商品编码
		$this->article_code = $this->top_app_keys[$this->order_type]['article_code'];
		//订购服务收费项目代码
		$this->item_code = $this->top_app_keys[$this->order_type]['item_code'];

		$result_info = CTX()->get_session('result_info');

		$result_info['taobao_user_nick'] = urldecode($result_info['taobao_user_nick']);

		$this->login_name = $this->taobao_nick = $result_info['taobao_user_nick'];

		if (isset($result_info['sub_taobao_user_nick']) && '' != $result_info['sub_taobao_user_nick']) {
			$result_info['sub_taobao_user_nick'] = urldecode($result_info['sub_taobao_user_nick']);
			$this->login_name = $result_info['sub_taobao_user_nick'];
		}

		if (!isset($result_info['access_token']) || !$result_info['access_token']) {
			return $response = $this->return_value(-1, '授权失败!');
		}

		$this->access_token = $result_info['access_token'];

		//业务开始

		$_tb_user_info = $this->taobao_user_seller_get();
		if (1 == $_tb_user_info['status'] && $_tb_user_info['data']['user']) {
			$this->tb_shop_type = $_tb_user_info['data']['user']['type'];
		}

		//推送业务
		$this->jushita_jdp_user_add();

		CTX()->db->begin_trans();

		//判断是否有归属客户  用于区分单个商户多店铺
		if ($this->_call_back_kehu_code) {
			//已有客户，走店铺绑定同一个商户流程
			$mdl_kehu = new ApiClientModel();
			$kehu_info = $mdl_kehu->is_exists($this->_call_back_kehu_code, 'kh_code');
			if (empty($kehu_info['data'])) {

				return $response = $this->return_value(-1, '未找到对应客户!');
			}

			$_kehu_id = $kehu_info['data']['kh_id'];

			$mdl_shop = new ApiShopModel();
			$__shop_info = $mdl_shop->is_exists($this->taobao_nick, 'sd_nick');

			if (empty($__shop_info['data'])) {
				//店铺不存在
				$_tb_shop_data = $this->taobao_shop_get();

				//添加店铺数据
				$mdl_shop = new ApiShopModel();

				$_shop_info = array(
					'sd_code' => $this->rand_shop_code(),
					'sd_name' => $_tb_shop_data['data']['shop']['title'],
					'sd_kh_id' => $_kehu_id,
					'sd_login_name' => $this->taobao_nick,
					'sd_nick' => $this->taobao_nick,
					'sd_top_session' => $this->access_token,
					'sd_session_expired' => 0,
					'sd_createdate' => date('Y-m-d H:i:s'),
					'sd_end_time' => date('Y-m-d H:i:s'),
					'sd_order_type' => $this->order_type
				);
				$mdl_shop->insert($_shop_info);

//				$this->init_365data($_kehu_id);
			} else {
				//店铺存在
				$_kehu_id = $__shop_info['data']['sd_kh_id'];
				$this->update_shop();
			}
		} else {
			//客户不存在, 单个客户数据绑定
			$mdl_shop = new ApiShopModel();
			$__shop_info = $mdl_shop->is_exists($this->taobao_nick, 'sd_nick');

			if (empty($__shop_info['data'])) {
				//店铺不存在, 第一次授权
				$_tb_shop_data = $this->taobao_shop_get();

				$shift_server = array();
				//随机取一台服务器
				foreach ($this->server_conf['web'] as $_key => $_web) {
					if (isset($_web['jushita_key']) && $_web['jushita_key'] == $this->app_key) {
						$shift_server[$_key] = $_web;
					}
				}

				$this->web_key = array_rand($shift_server);

				$_kehu_info = array(
					'kh_code' => $this->rand_kehu_code(),
					'kh_name' => $this->taobao_nick, //客户名称
					'kh_is_rds' => 1,
					'kh_is_ykh' => 1,
					'kh_web_host' => $this->web_key,
					'kh_createdate' => date('Y-m-d H:i:s'), //创建时间
				);

				//添加客户数据
				$mdl_kefu = new ApiClientModel();
				$mdl_kefu->insert($_kehu_info);
				$_kehu_id = CTX()->db->insert_id();

				//创建数据库
				$mdl_kefu = new ApiClientModel();
				$_install_status = $mdl_kefu->install_by_rds($_kehu_id);
				if (false === $_install_status) {
					CTX()->db->roll_back();

					return $response = $this->return_value(-1, '授权失败!!db!');
				}

				//添加店铺数据
				$mdl_shop = new ApiShopModel();

				$_shop_info = array(
					'sd_code' => $this->rand_shop_code(),
					'sd_name' => $_tb_shop_data['data']['shop']['title'],
					//'sd_name' => '唐江镇',
					'sd_kh_id' => $_kehu_id,
					'sd_login_name' => $this->taobao_nick,
					'sd_nick' => $this->taobao_nick,
					'sd_top_session' => $this->access_token,
					'sd_session_expired' => 0,
					'sd_createdate' => date('Y-m-d H:i:s'),
					'sd_end_time' => date('Y-m-d H:i:s'),
					'sd_order_type' => $this->order_type
				);

				$mdl_shop->insert($_shop_info);

				$this->send_mail($_tb_shop_data, $_tb_user_info);
				//$this->init_365data($_kehu_id);

			} else {
				//店铺存在
				$_kehu_id = $__shop_info['data']['sd_kh_id'];
				$this->update_shop();
			}
		}

		CTX()->db->commit();

		$this->init_365data($_kehu_id);

		//添加登录信息(子账号)
		$this->add_login($_kehu_id);

		//初始化365数据
		$this->init_365User($_kehu_id, $this->taobao_nick);

		//订购关系
		$this->edit_client_order($_kehu_id);

		//是从淘宝登录
		if (!$this->_call_back_kehu_code) {
			//登录到系统
			$url_efast = $this->login_efast($_kehu_id, $this->login_name);
			return $response = $this->return_value(2, '登录成功', $url_efast);
		}

		return $response = $this->return_value(1, '授权成功!');
	}

	/**
	 * 登录efast
	 * @param $renter_id
	 */
	function login_efast($kehu_id, $user_name) {

		$mdl_kehu = new ApiClientModel();
		//CTX()->log_error('result_info'.print_r($kehu_id,true));
		$_kehu_info = $mdl_kehu->is_exists($kehu_id, 'kh_id');
		//CTX()->log_error('result_info'.print_r($_kehu_info,true));
		$servers = $this->auth_conf['servers'];
		$web = $servers['web'][$_kehu_info['data']['kh_web_host']];

		$token = load_model('api/ApiClientModel')->check_login_without_password($_kehu_info['data']['kh_code'], $user_name);
		$url = "http://{$web['ip']}/?app_act=cloud_login&token={$token}";

		return $url;
	}

	/**
	 * 更新商店信息（运营平台）
	 */
	function update_shop() {

		$mdl_shop = new ApiShopModel();

		$params = array(
			'sd_top_session' => $this->access_token,
			'sd_order_type' => $this->order_type
		);

		$mdl_shop->editshop($params, array('sd_nick' => $this->taobao_nick));
	}

	/**
	 * 添加登录信息(子帐号)
	 * @param $kehu_id
	 */
	function add_login($kehu_id) {
		//添加login表数据
		//主帐号
		$mdl_kehu = new ApiClientModel();
		$_kehu_info = $mdl_kehu->is_exists($kehu_id, 'kh_id');

		if (!$_kehu_info['data']) {
			return false;
		}

		$mdl_login = new ApiClientloginModel();
		$login_info = $mdl_login->get_info_by_user_code_and_kh_id($this->taobao_nick, $_kehu_info['data']['kh_id']);
		if (empty($login_info['data'])) {
			//先添加店铺管理员
			$mdl_login = new ApiClientloginModel();

			$mdl_login->addclogin(array(
				'kh_id' => $_kehu_info['data']['kh_id'],
				'kh_code' => $_kehu_info['data']['kh_code'],
				'user_code' => $this->taobao_nick,
				//'user_password' => md5(md5($this->init_pw).$this->init_pw),
				'user_password' => md5($this->init_pw),
				'status' => '1',
				'remark' => '系统默认管理员',
				'sys_create_time' => date('Y-m-d h:i:s'),
			));
		}


		//淘宝子账号
		$subusers = $this->sellercenter_subusers_get();
		//子账号
		if ($subusers['status'] == 1 && $subusers['data']['subusers']) {

			foreach ($subusers['data']['subusers']['sub_user_info'] as $_user) {
				$mdl_login = new ApiClientloginModel();
				$sub_login_info = $mdl_login->get_info_by_user_code_and_kh_id($_user['nick'], $_kehu_info['data']['kh_id']);
				if (empty($sub_login_info['data'])) {
					$mdl_login = new ApiClientloginModel();
					$mdl_login->addclogin(array(
						'kh_id' => $_kehu_info['data']['kh_id'],
						'kh_code' => $_kehu_info['data']['kh_code'],
						'user_code' => $_user['nick'],
						//'user_password' => md5(md5($this->init_pw).$this->init_pw),
						'user_password' => md5($this->init_pw),
						'status' => '1',
						'sys_create_time' => date('Y-m-d h:i:s'),
					));
				}
			}
		}
	}

	/**
	 * 新增修改订购记录
	 */
	function edit_client_order($kehu_id) {
		return;
		$pro_endtime = '';
		try {
			//订购关系
			$ArticleUserSubscribe = $this->vas_subscribe_get();
			if (1 == $ArticleUserSubscribe['status'] && $ArticleUserSubscribe['data']['article_user_subscribes']) {

				foreach ($ArticleUserSubscribe['data']['article_user_subscribes'] as $_val) {
					if ($_val[0]['item_code'] == $this->item_code) {
						$pro_endtime = $_val[0]['deadline'];
					}
				}
			}
		} catch (Exception $e) {

		}

		if (!$pro_endtime) {
			return false;
		}

		$mdl_kehu = new ApiClientModel();
		$_kehu_info = $mdl_kehu->is_exists($kehu_id, 'kh_id');

		if (!$_kehu_info['data']) {
			return false;
		}

		$_insert_data = array(
			'pro_kh_id' => $kehu_id,
			'pro_cp_id' => '19',
			'pro_type' => 0,//买断型
			'pro_endtime' => $pro_endtime,
			'pro_status' => '1',
			'pro_enable' => 1,
			'pro_desc' => '淘宝订购efast365'
		);

		$mdl_product_order = new ApiProductorderModel();
		$_product_order_info = $mdl_product_order->is_exists($kehu_id, 'pro_kh_id');

		if (!$_product_order_info['data']) {

			$mdl_product_order = new ApiProductorderModel();
			$mdl_product_order->insert($_insert_data);
		} else {
			$mdl_product_order = new ApiProductorderModel();
			$mdl_product_order->editporder($_insert_data, array('pro_kh_id' => $kehu_id, 'pro_cp_id' => 19));
		}
	}

	/**
	 * 淘宝授权
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 * @return mixed
	 */
	function tb_authorize(array & $request, array & $response, array & $app) {

		$url = 'https://oauth.taobao.com/token';

		if (defined('TBSANDBOX') && TBSANDBOX) {

			$url = 'https://oauth.tbsandbox.com/token';
		}

		$postfields = array(
			'grant_type' => 'authorization_code',
			'client_id' => $this->app_key,
			'client_secret' => $this->app_secret,
			'code' => $request['code'],
			'redirect_uri' => 'http://www.yishangonline.com/osp_test/?app_act=tbauth/auth');
		$post_data = '';

		foreach ($postfields as $key => $value) {
			$post_data .= "$key=" . urlencode($value) . "&";
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		//指定post数据
		curl_setopt($ch, CURLOPT_POST, true);
		//添加变量
		curl_setopt($ch, CURLOPT_POSTFIELDS, substr($post_data, 0, -1));
		$output = curl_exec($ch);
		$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);
//		var_dump($output);

		return $output;
	}

	function return_value($status, $message = '', $data = '') {
		return array('status' => $status, 'message' => $message, 'data' => $data);
	}

	/**
	 * 初始化365数据 (店铺数据)
	 * @param $renter_id
	 */
	function init_365data($kehu_id) {

		CTX()->register_tool('db_kh', 'lib/db/PDODB.class.php');
		$mdl_kehu = new ApiClientModel();
		$kehu_info = $mdl_kehu->is_exists($kehu_id, 'kh_id');

		if (!$kehu_info['data']) {
			return false;
		}

		$mdl_shop = new ApiShopModel();

		$shop_info = $mdl_shop->is_exists($this->taobao_nick, 'sd_nick');

		//设置连接数据库参数
		$server_conf = $this->auth_conf['servers'];

		$db_conf = $server_conf['jushita_servers'][$server_conf['web'][$kehu_info['data']['kh_web_host']]['jushita_key']];

		if ($this->debug) {
			//测试用
			$db_conf['jushita_key'] = '21814154';
			$db_conf['jushita_secret'] = 'a1d813f6289cbe91c00b42dd0dea6484';
			$db_conf['rds_instance'] = 'jrdsa5xw9yyv';

			$db_conf['rds_account'] = 'jusr3keeskxf';
			$db_conf['rds_password'] = 'CS69973677bs';
			$db_conf['rds_host'] = 'jconnervyg378.mysql.rds.aliyuncs.com';
		}

		CTX()->db_kh->set_conf(array(
			'name' => $kehu_info['data']['kh_code'],
			'host' => $db_conf['rds_host'],
			'user' => $db_conf['rds_account'],
			'pwd' => $db_conf['rds_password'],
			'type' => 'mysql',
		));

		$db = CTX()->db_kh;
		try {
			$db->query('set names utf8;');
		} catch (Exception $e) {
			CTX()->log_error('result_info' . print_r($e->getMessage(), true));
		}
		$db->begin_trans();

		//监测店铺是否存在
		$_check_base_distributor_sql = "select distributor_code from `base_distributor` where distributor_name='" . $shop_info['data']['sd_name'] . "'";
		$s_base_distributor = $db->get_row($_check_base_distributor_sql);
		if (!$s_base_distributor) {
			$distributor_code = strtolower($this->rand_str(4));
			//插入商店
			$db->query("INSERT INTO `base_distributor`(distributor_code, distributor_name, distributor_property, distributor_type,sale_channel_id,authorize_state,status,express_code,send_store_code) VALUES('" . $distributor_code . "', '" . $shop_info['data']['sd_name'] . "', 3, 2,9,1,1,'EMS','" . $this->store_code . "');");

		} else {
			$distributor_code = $s_base_distributor['distributor_code'];
		}

		//监测店铺api是否存在
		$_check_base_shop_api_sql = "select shop_code from `base_shop_api` where shop_code='" . $distributor_code . "'";
		$s_base_shop_api = $db->get_row($_check_base_shop_api_sql);

		//插入商店api
		$api = array(
//			'app' => CTX()->get_app_conf('top_app_key'),
//			'secret' => CTX()->get_app_conf('top_app_secret'),
			'session' => $shop_info['data']['sd_top_session'],
			'nick' => $shop_info['data']['sd_nick'],
			'order_type' => $this->order_type
		);

		if (!$s_base_shop_api) {

			$shop_api_data = array(
				'shop_code' => $distributor_code,
				'source' => 9,
				'api' => json_encode($api),
				'tb_shop_type' => $this->tb_shop_type
			);

			$db->insert('base_shop_api', $shop_api_data);
		} else {

			$sql = "UPDATE `base_shop_api` set api=:api,tb_shop_type=:tb_shop_type where shop_code=:shop_code";

			$db->query($sql, array(':api' => json_encode($api), ':shop_code' => $distributor_code, 'tb_shop_type' => $this->tb_shop_type));
		}

		try {
			//获取淘宝的发货地址2014-08-12
			$send_addresses = $this->logistics_address_search();

			if (1 == $send_addresses['status'] && $send_addresses['data']['addresses']) {
				$province = $send_addresses['data']['addresses']['address_result'][0]['province'];
				$city = $send_addresses['data']['addresses']['address_result'][0]['city'];
				$country = $send_addresses['data']['addresses']['address_result'][0]['country'];
				$addr = $send_addresses['data']['addresses']['address_result'][0]['addr'];

				$contact_name = $send_addresses['data']['addresses']['address_result'][0]['contact_name'];
				$mobile_phone = $send_addresses['data']['addresses']['address_result'][0]['mobile_phone'];
				$phone = $send_addresses['data']['addresses']['address_result'][0]['phone'];

				$_province_id = $_city_id = $_country_id = null;
				$_province_id = $db->getOne("select region_id from `base_region` where region_name='" . $province . "' and parent_id=1");

				if ($_province_id) {
					$_city_id = $db->getOne("select region_id from `base_region` where region_name='" . $city . "' and parent_id=" . $_province_id);
				}

				if ($_city_id) {
					$_country_id = $db->getOne("select region_id from `base_region` where region_name='" . $country . "' and parent_id=" . $_city_id);
				}

				$sql = "UPDATE `base_store` set province=:province, city=:city, district=:district, address=:address, contact_person=:contact_person,contact_tel=:contact_tel, contact_phone=:contact_phone where store_code=:store_code";

				$db->query($sql, array(':province' => $_province_id, ':city' => $_city_id, ':district' => $_country_id, ':address' => $addr, ':contact_person' => $contact_name, ':contact_tel' => $mobile_phone, ':contact_phone' => $phone, ':store_code' => $this->store_code));
			}
		} catch (Exception $e) {
			CTX()->log_error('result_info' . print_r($e->getMessage(), true));
		}

		$db->commit();
	}

	/**
	 * 初始化365登录用户
	 * @param $renter_id
	 * @param $seller_nick 主帐号
	 */
	function init_365User($kh_id, $seller_nick) {

		$mdl_login = new ApiClientloginModel();
		$user_list = $mdl_login->get_list_by_kh_id($kh_id);

		$mdl_kehu = new ApiClientModel();
		$kehu_info = $mdl_kehu->is_exists($kh_id, 'kh_id');

		if (!$kehu_info['data']) {
			return false;
		}

		CTX()->register_tool('db_kh', 'lib/db/PDODB.class.php');

		//获取对应的数据库配置
		$server_conf = $this->auth_conf['servers'];

		$db_conf = $server_conf['jushita_servers'][$server_conf['web'][$kehu_info['data']['kh_web_host']]['jushita_key']];

		if ($this->debug) {
			//测试用
			$db_conf['jushita_key'] = '21814154';
			$db_conf['jushita_secret'] = 'a1d813f6289cbe91c00b42dd0dea6484';
			$db_conf['rds_instance'] = 'jrdsa5xw9yyv';

			$db_conf['rds_account'] = 'jusr3keeskxf';
			$db_conf['rds_password'] = 'CS69973677bs';
			$db_conf['rds_host'] = 'jconnervyg378.mysql.rds.aliyuncs.com';
		}

		CTX()->db_kh->set_conf(array(
			'name' => $kehu_info['data']['kh_code'],
			'host' => $db_conf['rds_host'],
			'user' => $db_conf['rds_account'],
			'pwd' => $db_conf['rds_password'],
			'type' => 'mysql',
		));

		$db = CTX()->db_kh;

		$db->begin_trans();

		$db->query('set names utf8;');

		//插入用户
		foreach ($user_list['data'] as $_user) {
			if ('admin' == $_user['kh_code'])
				continue;

			$_check_sql = "select user_code from `sys_user` where user_code='" . $_user['user_code'] . "'";
			$s = $db->get_row($_check_sql);
			if (!$s) {

				$role_id = 2;//订单处理角色
				if ($seller_nick == $_user['user_code']) {
					$role_id = 1; //主帐号
				}

				$_insert_sql = "INSERT INTO `sys_user`(role_id, user_code, user_name, password, is_taobao) VALUES(" . $role_id . ", '" . $_user['user_code'] . "', '" . $_user['user_code'] . "','" . $_user['user_password'] . "',1);";
				$db->query($_insert_sql);
				//用户和角色关系记录  jianbin.zheng
				$insert_id = $db->insert_id();
				$sql = "insert into sys_user_role(role_id,user_id) values(:role_id, :user_id)";
				$db->query($sql, array(":role_id" => $role_id, ":user_id" => $insert_id));
			}
		}

		$db->commit();
		return true;
	}

	/**
	 * 生成客户CODE
	 * @return string
	 */
	public function rand_kehu_code() {

		$code = 'efast_' . strtolower($this->rand_str(4));

		$mdl_kehu = new ApiClientModel();
		$kehu_info = $mdl_kehu->is_exists($code, 'kh_code');

		if (empty($kehu_info['data'])) {
			//return 'efast_oms_v2';
			return $code;
			return 'efast_adiu';
		} else {
			$this->rand_kehu_code();
		}
	}

	/**
	 * 生成商店CODE
	 * @return string
	 */
	public function rand_shop_code() {

		$code = 'sd_' . strtolower($this->rand_str(4));

		$mdl_shop = new ApiShopModel();
		$shop_info = $mdl_shop->is_exists($code, 'sd_code');

		if (empty($shop_info['data'])) {
			return $code;
		} else {
			$this->rand_shop_code();
		}
	}

	/**
	 * 获取卖家子帐号
	 * @param array $parameter
	 */
	function sellercenter_subusers_get() {

		$app_key = $this->app_key;
		$app_secret = $this->app_secret;
		$app_session = $this->access_token;

		$taobao = new taobao_util($app_key, $app_secret, $app_session);

		$params = array();
		$params['nick'] = $this->taobao_nick;
		$params['fields'] = 'nick,seller_id,seller_nick,full_name,sub_id';
		$data = $taobao->post('taobao.sellercenter.subusers.get', $params);

		return $data;
	}

	/**
	 * 获取店铺信息
	 */
	function taobao_shop_get() {

		$taobao = new taobao_util($this->app_key, $this->app_secret, $this->access_token);
		$params = array();
		$params['nick'] = $this->taobao_nick;
		$params['fields'] = 'sid,nick,title,desc,bulletin,pic_path,shop_score';
		return $taobao->post('taobao.shop.get', $params);
	}

	/**
	 * 查询卖家地址库
	 */
	function logistics_address_search() {
		$taobao = new taobao_util($this->app_key, $this->app_secret, $this->access_token);

		$params = array();

		$params['rdef'] = 'send_def';

		$data = $taobao->post('taobao.logistics.address.search', $params);
		return $data;
	}

	/**
	 * rds推送
	 */
	function jushita_jdp_user_add() {

		$app_key = $this->app_key;
		$app_secret = $this->app_secret;
		$app_session = $this->access_token;

		$rds_name = $this->server_conf['jushita_servers'][$this->app_key]['rds_instance'];

		$taobao = new taobao_util($app_key, $app_secret, $app_session);

		$params = array();
		$params['rds_name'] = $rds_name;

		$data = $taobao->post('taobao.jushita.jdp.user.add', $params);
		return $data;
	}

	/**
	 * 获取卖家信息
	 */
	function taobao_user_seller_get() {

		$app_key = $this->app_key;
		$app_secret = $this->app_secret;
		$app_session = $this->access_token;

		$taobao = new taobao_util($app_key, $app_secret, $app_session);

		$params = array();
		$params['fields'] = 'user_id,nick,sex,seller_credit,type,has_more_pic,item_img_num,item_img_size,prop_img_num,prop_img_size,auto_repost,promoted_type,status,alipay_bind,consumer_protection,avatar,liangpin,sign_food_seller_promise,has_shop,is_lightning_consignment,has_sub_stock,is_golden_seller,vip_info,magazine_subscribe,vertical_market,online_gaming';
		$data = $taobao->post('taobao.user.seller.get', $params);

		return $data;
	}

	/**
	 * 订购关系查询
	 * @param array $parameter
	 */
	function vas_subscribe_get() {

		$app_key = $this->app_key;
		$app_secret = $this->app_secret;
		$app_session = $this->access_token;
		$app_nick = $this->taobao_nick;

		$article_code = $this->article_code;

		$taobao = new taobao_util($app_key, $app_secret, $app_session);

		$params = array();

		$params['article_code'] = $article_code;
		$params['nick'] = $app_nick;

		$params['fields'] = 'item_code,deadline';

		$data = $taobao->post('taobao.vas.subscribe.get', $params);
		return $data;
	}

	//生成随机字串,可生成校验码, 默认长度4位,0 字母和数字混合,1 数字,-1 字母
	function rand_str($len = 4, $only_digit = 0) {
		switch ($only_digit) {
			case -1:
				$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
				break;
			case 1:
				$chars = str_repeat('0123456789', 3);
				break;
			default :
				$chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'; //rm 0,o
				break;
		}
		if ($len > 10) $chars = $only_digit == 0 ? str_repeat($chars, $len) : str_repeat($chars, 5); //位数过长重复字符串一定次数
		$chars = str_shuffle($chars);
		return substr($chars, 0, $len);
	}

	/**
	 * 发送邮件
	 */
	function send_mail($_shop_info, $seller_info) {
		//新用户邮件提醒
		$email = CTX()->get_app_conf("new_user_notice_mail");
		if (isset($email) && !empty($email) && count($email) > 0) {
			//		$seller_info = user_seller_get(array('app' => $this->app_key, 'secret' => $this->app_secret, 'nick' => $this->taobao_nick, 'session' => $this->access_token));
			//		$seller_info = $this->taobao_user_seller_get();

			$pro_endtime = '';
			//订购关系
			$ArticleUserSubscribe = $this->vas_subscribe_get();
			if (1 == $ArticleUserSubscribe['status'] && $ArticleUserSubscribe['data']['article_user_subscribes']) {

				foreach ($ArticleUserSubscribe['data']['article_user_subscribes'] as $_val) {
					if ($_val[0]['item_code'] == $this->item_code) {
						$pro_endtime = $_val[0]['deadline'];
					}
				}
			}

			if (1 != $seller_info['status']) {
				$user_info = "未找到淘宝卖家信息<br/>";
			} else {

				if ('B'==$seller_info['data']['user']['type']) {
					$user_info = "淘宝卖家信用等级：天猫<br/>";
				}else {
					$user_info = "淘宝卖家信用等级：" . $this->tb_level($seller_info['data']['user']['seller_credit']['level']) . "<br/>";
				}
			}
			$user_info .= "店铺访问的url：shop" . $_shop_info['data']["shop"]['sid'] . ".taobao.com<br/>卖家旺旺号：" . $_shop_info['data']['shop']['nick'] . "<br/>试用时间：" . date("Y-m-d h:i:s") . "<br/>授权到期时间：" . $pro_endtime . "<br/>";

			require_lib('net/MailEx');
			$mail = new MailEx();
			$mail->setHtml(true)->notify_mail($email, '新用户提醒', $user_info);
		}
	}

	/**
	 * 解析淘宝信用等级
	 * @param $level
	 */
	function tb_level($level) {

		$_tb_level = array(
			'0' => '无',
			'1' => '一心',
			'2' => '二心',
			'3' => '三心',
			'4' => '四心',
			'5' => '五心',
			'6' => '一钻',
			'7' => '二钻',
			'8' => '三钻',
			'9' => '四钻',
			'10' => '五钻',
			'11' => '一皇冠',
			'12' => '二皇冠',
			'13' => '三皇冠',
			'14' => '四皇冠',
			'15' => '五皇冠',
		);

		if (array_key_exists($level,$_tb_level)) {
			return $_tb_level[$level];
		} else {
			return $level;
		}
	}
}