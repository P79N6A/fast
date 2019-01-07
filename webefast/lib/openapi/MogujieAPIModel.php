<?php

require_once 'AbsAPIModel.php';
require_lib('Exceptions/ExtException');

/**
 * 蘑菇街API处理类
 * @author jhua.zuo<jhua.zuo@baisonmail.com>
 * @date 2015-03-30
 */
class MogujieAPIModel extends AbsAPIModel {

	public $gate = 'https://www.mogujie.com/openapi/api_v1_api/index';
	private $app_key;
	private $app_secret;
	private $access_token;

	public function __construct($token) {
		$this->app_key = $token ['app_key'];
		$this->app_secret = $token ['secret'];
		$this->access_token = $token ['session'];

		$this->order_pk = 'tid';
		$this->order_page_size = 50;

		$this->goods_pk = 'item_id';
		$this->goods_page_size = 30;
	}

	/**
	 * 第三方平台请求发送
	 */
	public function request_send($api, $param = array()) {
		$data = $param;

		$data['app_key'] = $this->app_key;
		$data['app_secret'] = $this->app_secret;
		$data['access_token'] = $this->access_token;
		$data['method'] = $api;

		$result = $this->exec($this->gate, $data);
		return $result;
	}

	/**
	 * 批量发送第三方平台请求
	 */
	public function request_send_multi($api, $params = array()) {
		// TODO: Implement request_send_multi() method.
	}

	/**
	 * 生成签名
	 * @param array $param 待签名参数
	 * @return string 返回签名值
	 */
	public function sign($param = array()) {
		// TODO: Implement sign() method.
	}

	/**
	 * 批量下载商品信息
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_list_download(array $data) {
		$params = array();

		$params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
		$params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 20;

		$result = $this->json_decode($this->request_send('youdian.item.queryList', $params));
		if ($result['status']['code'] != '10001') {
			$msg = $result['status']['msg'];
			throw new ExtException($msg);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'total_results' => $result['result']['data']['list_total'],
				'items' => array('item' => $result['result']['data']['list_items']
				)
			);
			return $return;
		}
	}

	/**
	 * 单个商品信息下载
	 * @author hunter
	 * @date 2015-05-19
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_info_download($data) {
		$params = array();
		$params['itemId'] = $data;

		$result = $this->json_decode($this->request_send('youdian.item.getItemInfo', $params));
		if ($result['status']['code'] != '10001') {
			$msg = $result['status']['msg'];
			throw new ExtException($msg);
		} else {
			$return = $result['result']['data'];
			return $return;
		}
	}

	/**
	 * 批量商品信息下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-14
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_info_download_multi($ids, $data = array()) {
		$return = array();
		foreach ($ids as $item_id) {
			$return[] = $this->goods_info_download($item_id);
		}
		return $return;
	}

	/**
	 * 订单列表下载
	 * @param $data
	 * @author hunter
	 * @date 2015-05-06
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function order_list_download($data) {
		$params = array();

		$params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
		$params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;

		$params['status'] = '0';
		if (isset($data['start_modified']) && !empty($data['start_modified'])) {
			$params['start_updated'] = $data['start_modified'];
		} else {
			$params['start_updated'] = date('Y-m-d H:i:s', strtotime('-6 day'));
		}

		if (isset($data['end_modified']) && !empty($data['end_modified'])) {
			$params['end_updated'] = $data['end_modified'];
		} else {
			$params['end_updated'] = date('Y-m-d H:i:s');
		}

		$result = $this->json_decode($this->request_send('youdian.trade.sold.get', $params));

		if ($result['status']['code'] != '10001') {
			$msg = $result['status']['msg'];
			throw new ExtException($msg);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'trades' => array('trade' => $result['result']['data']['trades'])
			);

			if (0 == $result['result']['data']['has_next']) {
				//0表示有下一页
				$return['total_results'] = ($params['page_no'] + 1) * $this->order_page_size;
			} else {
				$return['total_results'] = $this->order_page_size;
			}

			return $return;
		}
	}

	/**
	 * 单个订单下载
	 * @param $id
	 * @param $data
	 * @author hunter
	 * @date 2015-03-09
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function order_info_download($id, $data = array()) {
		$params = array();
		$params['tid'] = $id;

		$result = $this->json_decode($this->request_send('youdian.trade.get', $params));

		if ($result['status']['code'] != '10001') {
			$msg = $result['status']['msg'];
			throw new ExtException($msg);
		} else {
			//转成与淘宝类似的返回格式
			$return = $result['result']['data'];
			return $return;
		}
	}

	/**
	 * 退单列表下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-04-10
	 * @return array 返回退单列表信息
	 */
	public function refund_list_download(array $data) {
		// TODO: Implement refund_list_download() method.
	}

	/**
	 * 单个退单明细下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-04-10
	 * @return array 返回退单明细信息
	 */
	public function refund_info_download($refund_id, $refund_info) {
		// TODO: Implement refund_info_download() method.
	}

	/**
	 * 库存信息回传
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回结果和第三方平台原始信息
	 */
	public function inv_upload(array $data) {
		$params = array();
		$params['skuId'] = $data['sku_id'];
		$params['stock'] = $data['inv_num'];

		$result = $this->json_decode($this->request_send('youdian.item.update.skuStock', $params));
		if ($result['status']['code'] != '10001') {
			$msg = $result['status']['msg'];
			throw new ExtException($msg);
		} else {
			//转成与淘宝类似的返回格式
			$return = $result['result'];
			return $return;
		}
	}

	/**
	 * 批量库存信息回传
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-10
	 * @return array 返回结果和第三方平台原始信息
	 */
	public function inv_upload_multi(array $data) {
		$return = array();
		foreach($data as $item) {
			$return[] = $this->inv_upload($item);
		}
		return $return;
	}

	/**
	 * 发货信息回传
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回结果和第三方平台原始信息
	 */
	public function logistics_upload(array $data) {
		$params = array();
		$params['tid'] = $data['tid'];
		$params['company_code'] = $data['logistics_id'];
		$params['out_sid'] = $data['express_no'];

		$result = $this->json_decode($this->request_send('youdian.logistics.send', $params));

		if ($result['status']['code'] != '10001') {
			$msg = $result['status']['msg'];
			throw new ExtException($msg);
		} else {
			$oms_log['is_back'] = $send_log['status'] = 1;
			$oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
		}
		$result['send_log'] = $send_log;
		$result['oms_log'] = $oms_log;
		return $result;
	}

	/**
	 * 下载物流公司原始数据
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-28
	 * @return array
	 */
	public function logistics_company_download() {
		// TODO: Implement logistics_company_download() method.
	}

	/**
	 * 转换平台商品为标准信息
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-12
	 * @param array $data 第三方平台商品信息
	 */
	public function _trans_goods(array $data) {
		$return = array();
		$return['goods_name'] = $data['item_name'];
		$return['goods_code'] = $data['item_code'];
		$return['goods_from_id'] = $data['item_id'];
		$return['num'] = $data['item_stock'];
		//TODO seller_nick暂无
		$return['seller_nick'] = '';
		$return['source'] = 'mogujie';
		$return['status'] = $data['item_isShelf'] == '0' ? 1 : 0;
		//TODO stock_type暂无
		$return['stock_type'] = 2;
		//TODO onsale_time暂无
		$return['onsale_time'] = '';
		$return['has_sku'] = empty($data['item_skus']) ? 0 : 1;
		$return['price'] = $data['item_price'] / 100;
		$return['goods_img'] = '';
		$return['goods_desc'] = '';
		return $return;
	}

	/**
	 * 转换SKU信息为标准信息
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-12
	 * @param array $data 第三方平台商品SKU信息
	 */
	public function _trans_sku(array $data) {
		$return = array();
		if (isset($data['item_skus'])) {
			foreach ($data['item_skus'] as $value) {
				$sku = array();
				$sku['goods_from_id'] = $data['item_id'];
				$sku['source'] = 'mogujie';
				$sku['sku_id'] = $value['sku_id'];
				$sku['goods_barcode'] = isset($value['sku_code']) ? $value['sku_code'] : '';

				$sku['status'] = (isset($data['item_isShelf']) && $data['item_isShelf'] == '0') ? 1 : 0;
				$sku['num'] = $value['sku_stock'];
				$sku['price'] = $value['sku_price']/100;

				$sku['stock_type'] = 2;
				$sku['with_hold_quantity'] = 0;

				$sku['sku_properties'] = '';

				$sku['sku_properties_name'] = '';
				$return[] = $sku;
			}
		}
		return $return;
	}

	/**
	 * 转换订单信息为标准订单
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-12
	 * @param array $data 第三方平台订单信息
	 */
	public function _trans_order($shop_code, array $data) {
		$datetime = date('Y-m-d H:i:s');
		$return = array();
		$return['tid'] = $data['tid'];
		$return['source'] = 'mogujie';
		$return['shop_code'] = $shop_code;
		$return['status'] = ($data['status'] == 'TRADE_ACTIVE' && $data['pay_status'] == 'PAY_FINISH' && $data['ship_status'] == 'SHIP_NO') ? 1 : 0;
		$return['trade_from'] = '';

		$return['pay_type'] = 0;

		$return['pay_time'] = $data['pay_time'];

		//昵称暂无
		$return['seller_nick'] = $data['seller_uname'];
		$return['buyer_nick'] = $data['buyer_uname'];

		$return['receiver_name'] = $data['receiver_name'];

		$return['receiver_country'] = '中国';
		$return['receiver_province'] = $data['receiver_state'];
		$return['receiver_city'] = $data['receiver_city'];
		$return['receiver_district'] = $data['receiver_district'];

		$return['receiver_street'] = '';

		$return['receiver_address'] = $data['receiver_state'].$data['receiver_city'].$data['receiver_district'].$data['receiver_address'];
		//receiver_addr是需要去掉省市区的地址
		$return['receiver_addr'] = $data['receiver_address'];

		$return['receiver_zip_code'] = $data['receiver_zip'];
		$return['receiver_mobile'] = $data['receiver_mobile'];
		$return['receiver_phone'] = $data['receiver_phone'];
		$return['receiver_email'] = isset($data['receiver_email']) ? $data['receiver_email'] : '';
	//	$return['express_code'] = $data['logistics_name'];
		$return['express_code'] = load_model('source/mogujie/ApiMogujieTradeModel')->get_logistics_companies_by_logistics_id($data['logistics_name']);
		$return['express_no'] = $data['logistics_no'];
		$return['hope_send_time'] = '';

		//订单总数量
		$num = 0;
		foreach ($data['orders'] as $i) {
			$num = $num + $i['items_num'];
		}
		$return['num'] = $num;
		$return['sku_num'] = count($data['orders']); //平台sku种类数量
		$return['goods_weight'] = 0;
		$return['buyer_remark'] = $data['buyer_memo'];
		$return['seller_remark'] = $data['trade_memo'];

		$return['seller_flag'] = '';
		$return['order_money'] = $data['total_trade_fee'] / 100;
		$return['express_money'] = '';
		$return['delivery_money'] = 0;
		$return['gift_coupon_money'] = 0;
		$return['gift_money'] = 0;
		$return['integral_change_money'] = 0;
		$return['buyer_money'] = $data['payed_fee'] / 100;
		$return['alipay_no'] = isset($data['payment_tid']) ? $data['payment_tid'] : '';
		$return['coupon_change_money'] = 0;

		$return['balance_change_money'] = 0;
		$return['is_lgtype'] = '';
		$return['seller_rate'] = '';
		$return['buyer_rate'] = isset($data['buyer_rate']) ? $data['buyer_rate'] : '';
		$return['invoice_type'] = '';
		$return['invoice_title'] = '';
		$return['invoice_content'] = '';

		$return['invoice_money'] = 0;
		$return['invoice_pay_type'] = isset($data['payment_type']) ? $data['payment_type'] : '';

		$return['order_last_update_time'] = $return['last_update_time'] = $data['lastmodify'];
		$return['order_first_insert_time'] = $data['created'];
		$return['first_insert_time'] = $datetime;
		return $return;
	}

	/**
	 * 转换订单明细信息为标准订单明细
	 * @author hunter
	 * @date 2015-05-07
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_order_detail(array $data) {
		$return = array();

		$total = 0;
		$avg_total = 0;
		$base = $data['total_trade_fee'] / 100 ;
		foreach ($data['orders'] as $value) {
			$total += $value['total_order_fee']/100;
		}

		if ($data['orders']) {
			foreach ($data['orders'] as $key => $value) {
				$detail = array();
				$detail['tid'] = $data['tid'];
				$detail['oid'] = $value['oid'];
				$detail['source'] = 'mogujie';
				//$detail['return_status'] = '';
				
				$detail['title'] = $value['title'];
				$detail['price'] = $value['total_order_fee'] / 100 / $value['items_num'] + $value['discount_fee'] / 100;

				$detail['num'] = $value['items_num'];
				$detail['goods_code'] = $value['iid'];
				$detail['sku_id'] = $value['sku_id'];
				$detail['goods_barcode'] = $value['sku_bn'];
				$detail['total_fee'] = $value['total_order_fee'] / 100;
				$detail['payment'] = $value['sale_price'] / 100;
				$detail['discount_fee'] = $value['discount_fee'] / 100;
				$detail['adjust_fee'] = 0;
				$detail['sku_properties'] = $value['sku_properties'];

				if ($key < count($data['orders']) - 1) {
					$detail['avg_money'] = $base * ($value['total_order_fee'] / 100) / $total;
				} else {
					$detail['avg_money'] = $base - $avg_total;
				}
				$avg_total += $detail['avg_money'];
				$detail['end_time'] = '';

				$return[] = $detail;
			}

			//校验均摊金额, 其实没必要
			if ($base != $avg_total) {
				throw new ExtException('订单均摊金额计算异常', -1);
			}
		}
		return $return;
	}

	/**
	 * 转换退单信息为标准退单信息
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-23
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_refund($shop_code, array $data) {
		// TODO: Implement _trans_refund() method.
	}

	/**
	 * 转换退单明细信息为标准退单明细信息
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-23
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_refund_detail(array $data) {
		// TODO: Implement _trans_refund_detail() method.
	}

	/**
	 * 保存原始订单和订单明细数据
	 * @param $shop_code
	 * @param $data
	 * @return mixed
	 */
	public function save_source_order_and_detail($shop_code, $data) {
		return $ret = load_model('source/mogujie/ApiMogujieTradeModel')->save_trade_and_order($shop_code, $data);

	}

	/**
	 * 保存原始退单和退单数据
	 * @param $shop_code
	 * @param $data
	 * @return mixed
	 */
	public function save_source_refund($shop_code, $data) {
		// TODO: Implement save_source_refund() method.
	}

	/**
	 * 保存原始商品和sku数据
	 * @param $shop_code
	 * @param $data
	 * @return mixed
	 */
	public function save_source_goods_and_sku($shop_code, $data) {
		return $ret = load_model('source/mogujie/ApiMogujieGoodsModel')->save_goods_and_sku($shop_code, $data);
	}

	/**
	 * 保存物流公司原始数据
	 * @param string $shop_code 店铺代码
	 * @param array $data 物流公司原始数据
	 * @return array
	 */
	public function save_source_logistics_company($shop_code, $data) {
		// TODO: Implement save_source_logistics_company() method.
	}

}