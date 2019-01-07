<?php
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');

/**
 * 美丽说API类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class MeilishuoAPIModel extends AbsAPIModel {

	//public $gate = 'http://api.open.meilishuo.com/router/rest';
        public $gate = 'https://api.open.meilishuo.com/router/rest';
        
	private $app_key;
	private $secret;
	private $session;

	public function __construct($token) {
		$this->app_key = $token ['app_key'];
		$this->secret = $token ['secret'];
		$this->session = $token ['session'];

		$this->order_pk = 'order_id';
		$this->order_page_size = 100;

		$this->goods_pk = 'goods_id';
		$this->goods_page_size = 50;
	}

	/**
	 * @param $api
	 * @param $param
	 * 第三方平台请求发送
	 */
	public function request_send($api, $param = array()) {
		$data = $param;
		$data['method'] = $api;
		$data['timestamp'] = date('Y-m-d H:i:s');
		$data['format'] = 'json';
		$data['app_key'] = $this->app_key;
		$data['v'] = '1.0';
		$data['session'] = $this->session;
		$data['sign_method'] = 'md5';
		$sign = $this->sign($data);
		$data['sign'] = $sign;
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
		$sign = $this->secret;

		ksort($param);

		foreach ($param as $key => $val) {
			$sign .= $key . $val;
		}

		$sign .= $this->secret;

		$sign = strtoupper(md5($sign));

		return $sign;
	}

	/**
	 * 批量下载商品信息
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_list_download(array $data) {
		$params = array();

		$params['page'] = isset($data['page_no']) ? $data['page_no'] : 1;
		$params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 20;

		$result = $this->json_decode($this->request_send('meilishuo.items.list.get', $params));
		if (isset($result['error_response'])) {
			$msg = $result['error_response']['message'];
			throw new ExtException($msg);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'total_results' => $result['items_list_get_response']['total_num'],
				'items' => array('item' => $result['items_list_get_response']['info']
				)
			);
			return $return;
		}
	}

	/**
	 * 单个商品信息下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_info_download($data) {
		// TODO: Implement goods_info_download() method.
	}

	/**
	 * 批量商品信息下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-14
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_info_download_multi($ids, $data = array()) {
		$return = array();
		foreach ($ids as $goods_id) {
			$return[] = $data[$goods_id];
		}
		return $return;
	}

	/**
	 * 订单列表下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function order_list_download($data) {
		$params = array();

		$params['page'] = isset($data['page_no']) ? $data['page_no'] : 1;
		$params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;

		$params['status'] = '0';
		if (isset($data['start_modified']) && !empty($data['start_modified'])) {
			$params['uptime_start'] = $data['start_modified'];
		} else {
			$params['uptime_start'] = date('Y-m-d H:i:s', strtotime('-7 day'));
		}
		//查询结束时间(查询开始、结束时间跨度不能超过15天)
		if (isset($data['end_modified']) && !empty($data['end_modified'])) {
			$params['uptime_end'] = $data['end_modified'];
		} else {
			$params['uptime_end'] = date('Y-m-d H:i:s');
		}

		$result = $this->json_decode($this->request_send('meilishuo.order.list.get', $params));

		if (isset($result['error_response'])) {
			$msg = $result['error_response']['message'];
			throw new ExtException($msg);
		} else {

			$_result = array();
			foreach($result['order_list_get_response']['info'] as $_order) {
				$_t = $_order['order'];
				$_t['goods'] = $_order['goods'];
				$_t['address'] = $_order['address'];
				$_t['service'] = $_order['service'];
				$_result[] = $_t;
			}
			$return = array(
				//转成与淘宝类似的返回格式
				'trades' => array('trade' => $_result),
				'total_results' => $result['order_list_get_response']['total_num'],
			);
			return $return;
		}
	}

	/**
	 * 单个订单下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function order_info_download($id, $data = array()) {
		$params = array();
		$params['order_id'] = $id;

		$result = $this->json_decode($this->request_send('meilishuo.order.detail.get', $params));

		if (isset($result['error_response'])) {
			$msg = $result['error_response']['message'];
			throw new ExtException($msg);
		} else {
			//转成与淘宝类似的返回格式
			$return = $result['order_detail_get_response']['info'];
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
		$params['twitter_id'] = $data['goods_from_id'];
		$params['sku_id'] = $data['sku_id'];
		$params['modify_type'] = 'set';
		$params['modify_value'] = $data['inv_num'];

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
	 * @author hunter
	 * @date 2015-05-05
	 * @return array 返回结果和第三方平台原始信息
	 */
	public function logistics_upload(array $data) {
		$params = array();
		$params['order_id'] = $data['tid'];
		$params['express_company'] = $data['logistics_id'];
		$params['express_id'] = $data['express_no'];

		$result = $this->json_decode($this->request_send('meilishuo.order.deliver', $params));

		if (isset($result['error_response'])) {
			$msg = $result['error_response']['message'];
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
		$return['goods_name'] = $data['goods_title'];
		$return['goods_code'] = $data['goods_no'];
		$return['goods_from_id'] = $data['twitter_id'];
		$return['num'] = '';
		//TODO seller_nick暂无
		$return['seller_nick'] = '';
		$return['source'] = 'meilishuo';
		$return['status'] = $data['goods_status'] == '1' ? 1 : 0;
		//TODO stock_type暂无
		$return['stock_type'] = 2;
		//TODO onsale_time暂无
		$return['onsale_time'] = '';
		$return['has_sku'] = empty($data['stock']) ? 0 : 1;
		$return['price'] = $data['goods_price'];
		$return['goods_img'] = $data['goods_img'];
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
		if (isset($data['stocks'])) {
			foreach ($data['stocks'] as $value) {
				$sku = array();
				$sku['goods_from_id'] = $data['twitter_id'];
				$sku['source'] = 'meilishuo';
				$sku['sku_id'] = $value['sku_id'];
				$sku['goods_barcode'] = isset($value['goods_code']) ? $value['goods_code'] : '';

				$sku['status'] = (isset($data['goods_status']) && $data['goods_status'] == '1') ? 1 : 0;
				$sku['num'] = $value['repertory'];
				$sku['price'] = $data['goods_price'];

				$sku['stock_type'] = 2;
				$sku['with_hold_quantity'] = 0;

				$sku['sku_properties'] = $value['color'].'|'.$value['size'];

				$sku['sku_properties_name'] = $value['color'].'|'.$value['size'];
				$return[] = $sku;
			}
		}
		return $return;
	}

	/**
	 * 转换订单信息为标准订单
	 * @author hunter
	 * @date 2015-05-05
	 * @param array $data 第三方平台订单信息
	 */
	public function _trans_order($shop_code, array $data) {
		$datetime = date('Y-m-d H:i:s');
		$return = array();
		$return['tid'] = $data['order']['order_id'];
		$return['source'] = 'meilishuo';
		$return['shop_code'] = $shop_code;
		$return['status'] = $data['order']['status_text'] == '等待发货' ? 1 : 0;
		$return['trade_from'] = '';

		$return['pay_type'] = 0;

		$return['pay_time'] = $data['order']['pay_time'];

		//昵称暂无
		$return['seller_nick'] = '';
		$return['buyer_nick'] = $data['order']['buyer_nickname'];

		$return['receiver_name'] = $data['address']['nickname'];

		$return['receiver_country'] = '中国';
		$return['receiver_province'] = $data['address']['province'];
		$return['receiver_city'] = $data['address']['city'];
		$return['receiver_district'] = $data['address']['district'];

		$return['receiver_street'] = $data['address']['street'];
		$return['receiver_address'] = $data['address']['address'];
		//receiver_addr是需要去掉省市区的地址
		$return['receiver_addr'] = $data['address']['address'];

		$return['receiver_zip_code'] = $data['address']['postcode'];
		$return['receiver_mobile'] = '';
		$return['receiver_phone'] = $data['address']['phone'];
		$return['receiver_email'] = '';
		$return['express_code'] = $data['order']['express_company'];
		$return['express_no'] = $data['order']['express_id'];
		$return['hope_send_time'] = '';

		//订单总数量
		$num = 0;
		foreach ($data['goods'] as $i) {
			$num = $num + $i['amount'];
		}
		$return['num'] = $num;
		$return['sku_num'] = count($data['goods']); //平台sku种类数量
		$return['goods_weight'] = 0;
		$return['buyer_remark'] = $data['order']['comment'];
		$return['seller_remark'] = '';

		$return['seller_flag'] = '';
		$return['order_money'] = $data['order']['total_price'];
		$return['express_money'] = $data['order']['express_price'];
		$return['delivery_money'] = 0;
		$return['gift_coupon_money'] = $data['order']['coupon_platform'];
		$return['gift_money'] = 0;
		$return['integral_change_money'] = 0;
		$return['buyer_money'] = $data['order']['total_price'];
		$return['alipay_no'] = '';
		$return['coupon_change_money'] = $data['order']['coupon_platform'];

		$return['balance_change_money'] = 0;
		$return['is_lgtype'] = '';
		$return['seller_rate'] = '';
		$return['buyer_rate'] = '';
		$return['invoice_type'] = '';
		$return['invoice_title'] = '';
		$return['invoice_content'] = '';

		$return['invoice_money'] = 0;
		$return['invoice_pay_type'] = '';

		$return['order_last_update_time'] = $return['last_update_time'] = $data['order']['ctime'];
		$return['order_first_insert_time'] = $data['order']['ctime'];
		$return['first_insert_time'] = $datetime;
		return $return;
	}

	/**
	 * 转换订单明细信息为标准订单明细
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-12
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_order_detail(array $data) {
		$return = array();

		$total = 0;
		$avg_total = 0;
		$base = $data['order']['total_price'] - $data['order']['coupon_shop'] - $data['order']['coupon_platform'] - $data['order']['weixin_coupon_price'];
		foreach ($data['goods'] as $value) {
			$total += $value['price'] * $value['amount'];
		}

		if ($data['goods']) {
			foreach ($data['goods'] as $key => $value) {
				$detail = array();
				$detail['tid'] = $data['order']['order_id'];
				$detail['oid'] = $value['mid'];
				$detail['source'] = 'meilishuo';
				//$detail['return_status'] = '';
				$detail['title'] = $value['goods_title'];
				$detail['price'] = $value['price'];
				$detail['num'] = $value['amount'];
				$detail['goods_code'] = $value['goods_no'];
				$detail['sku_id'] = $value['sku_id'];
				$detail['goods_barcode'] = $value['goods_code'];
				$detail['total_fee'] = $value['orderItemPrice'] * $value['orderItemNum'];
				$detail['payment'] = $value['orderItemPrice'] * $value['orderItemNum'];
				$detail['discount_fee'] = $value['goods_platform_coupon'] + $value['goods_shop_coupon'];
				$detail['adjust_fee'] = 0;

				if ($key < count($data['goods']) - 1) {
					$detail['avg_money'] = $base * ($value['price'] * $value['amount']) / $total;
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
		return $ret = load_model('source/meilishuo/ApiMeilishuoTradeModel')->save_trade_and_order($shop_code, $data);
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
		return $ret = load_model('source/meilishuo/ApiMeilishuoGoodsModel')->save_goods_and_sku($shop_code, $data);
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