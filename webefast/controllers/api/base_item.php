<?php
require_lib('util/web_util', true);
require_lib('util/taobao_util', true);
require_model('base/ShopApiModel');
class base_item {
	function do_list(array & $request, array & $response, array & $app) {
		

	}

	/**
	 * 通过类型获取明细 (新框架)
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function get_sku_list_by_item_id(array & $request, array & $response, array & $app) {

		$ret = load_model('api/BaseSkuModel')->get_list_by_item_id($request['base_item_id'], array());
		$dataset = $ret['data'];

		exit_table_data_json($dataset['data'], $dataset['filter']['record_count']);
	}


	/**
	 * 库存同步
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function store_synchro_by_sku_id(array & $request, array & $response, array & $app) {

		$sku_id = $request['sku_id'];

		$sku_info = load_model('api/BaseSkuModel')->get_by_sku_id($sku_id);
		$item_info = load_model('api/BaseItemModel')->get_by_item_id($sku_info['data']['item_id']);

		$ret = load_model('api/BaseItemModel')->store_synchro($sku_info['data']['quantit'], $sku_info['data']['item_id'], $sku_info['data']['sku_id'], $item_info['data']['shop_code']);

		if ($ret['status'] == 1) {
			$ret['message'] = '更新成功!';
		} else {
			$ret['message'] = '更新失败!';
		}

		exit_json_response($ret);
	}

	/**
	 * 批量库存同步
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function batch_store_synchro(array & $request, array & $response, array & $app) {
		$app['tpl'] = "api/base_item_batch_store_synchro";

		$_shops = load_model('base/ShopModel')->get_list();

		$shops = array();

		foreach ($_shops as $_key => $_val) {

			$_t = array($_val['shop_code'], $_val['shop_name']);
			$shops[] = $_t;
		}
		$response['shop'] = $shops;
		$response['data'] = array();
	}

	/**
	 * 执行批量库存同步
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_batch_store_synchro(array & $request, array & $response, array & $app) {

		$shop_code = $request['shop'];

		$ret = load_model('api/BaseItemModel')->batch_store_synchro($shop_code);

		if ($ret['status'] == 1) {
			$ret['message'] = '更新成功!';
		} else {
			$ret['message'] = '更新失败!';
		}

		exit_json_response($ret);
	}

	/**
	 * 一键关联
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function one_click_relation_goods(array & $request, array & $response, array & $app) {

		$app['tpl'] = "api/base_item_one_click_relation_goods";
		$response['data'] = array();
	}

	/**
	 * 一键建档
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function one_click_create_goods(array & $request, array & $response, array & $app) {

		$app['tpl'] = "api/base_item_one_click_create_goods";
		$response['data'] = array();
	}

	/**
	 * 执行一键关联
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_one_click_relation_goods(array & $request, array & $response, array & $app) {

		$ret = load_model('api/BaseItemModel')->one_click_relation_goods();

		exit_json_response($ret);
	}


	/**
	 * 执行一键建档
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_one_click_create_goods(array & $request, array & $response, array & $app) {

		$ret = load_model('api/BaseItemModel')->one_click_create_goods();

		exit_json_response($ret);
	}

	/**
	 * 批量上架
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_batch_listing(array & $request, array & $response, array & $app) {

		$item_ids = $request['item_ids'];

		$ret = load_model('api/BaseItemModel')->batch_listing($item_ids);

		exit_json_response($ret);
	}

	/**
	 * 批量下架
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_batch_delisting(array & $request, array & $response, array & $app) {
		$item_ids = $request['item_ids'];

		$ret = load_model('api/BaseItemModel')->batch_delisting($item_ids);

		exit_json_response($ret);
	}

	/**
	 * 下载淘宝商品(页面)
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function dl_taobao_items(array & $request, array & $response, array & $app) {
		$app['tpl'] = "api/base_item_download";

		$_shops = load_model('base/ShopModel')->get_list();

		$shops = array();

		foreach ($_shops as $_key => $_val) {

			$_t = array($_val['shop_code'], $_val['shop_name']);
			$shops[] = $_t;
		}
		$response['shop'] = $shops;
		$response['data'] = array();
	}

	/**
	 * 执行下载动作
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_dl_taobao_items(array & $request, array & $response, array & $app) {

		$_SESSION['dl_over'] = 0;//下载是否结束
		$_SESSION['dl_propress'] = 0;//下载进度
		session_write_close();
		session_start();

		$mdl_shop_api = new ShopApiModel();
		$shop_data = $mdl_shop_api->get_shop_api_by_shop_code($request['shop']);

		$api_params = $shop_data['api'];

		$taobao_util = new taobao_util($api_params['app'], $api_params['secret'], $api_params['session'], $api_params['nick']);

		$arr_num_iid = load_model('api/BaseItemModel')->get_onsale_items($taobao_util);

		$item_datas = load_model('api/BaseItemModel')->batch_get_items($taobao_util,$arr_num_iid);

		//插入数据
		load_model('api/BaseItemModel')->insert_data($item_datas, $request['shop']);

		$_SESSION['dl_over'] = 1;
		session_write_close();
		session_start();

		exit_json_response(array('status' => '1', 'data'=>'','message'=>'下载成功'));
	}



	/**
	 * 监测下载进度
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function check_dl_progress(array & $request, array & $response, array & $app) {

		$data['dl_progress'] = $_SESSION['dl_progress'];
		$data['dl_over'] = $_SESSION['dl_over'];
		exit_json_response(array('status' => '1', 'data'=>$data,'message'=>'success'));
	}

}

