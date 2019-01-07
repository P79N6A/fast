<?php
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');

/**
 * 亚马逊API类
 * @author hunter
 * Date: 4/27/15
 * Time: 11:34 AM
 */
class YamaxunAPIModel extends AbsAPIModel {

	public $gate = 'http://api.yintai.com/openapi/service';

	private $AWSAccessKeyId;
	private $AWSAccessKeyValue;
	private $MWSAuthToken;
	private $SellerId;
	private $MarketplaceId;

	public function __construct($token) {

		$this->AWSAccessKeyId = $token['AWSAccessKeyId'];
		$this->AWSAccessKeyValue = $token['AWSAccessKeyValue'];
		$this->MWSAuthToken = $token['MWSAuthToken'];
		$this->SellerId = $token['SellerId'];
		$this->MarketplaceId = $token['MarketplaceId'];

		$this->order_page_size = 100;

		$this->order_pk = 'AmazonOrderId';
		$this->goods_pk = '';
		$this->refund_pk = '';
	}

	/**
	 * 第三方平台请求发送
	 */
	public function request_send($api, $param = array()) {

		switch ($param['api_type']) {
			case 'order':
				return $this->order_request_send($api, $param);
				break;
		}
	}

	/**
	 * @param $url
	 * @param $param
	 */
	public function order_request_send($url, $param) {

		unset($param['api_type']);

		$param['Timestamp'] = date(DATE_ISO8601, time());

		$param['SignatureVersion'] = 2;
		$param['SignatureMethod'] = 'HmacSHA256';
		$param['SellerId'] = $this->SellerId;
		$param['AWSAccessKeyId'] = $this->AWSAccessKeyId;
		$param['MWSAuthToken'] = $this->MWSAuthToken;

		$url = $url . $param['api_action'];

		$param['url'] = $url;
		$param['Signature'] = $this->sign($param);

		unset($param['api_action']);
		$query = $this->_getParametersAsString($param);

		$result = $this->_httpPost($query, $url);
		return $result;
	}

	/**
	 * 生成签名
	 * @param array $param 待签名参数
	 * @return string 返回签名值
	 */
	public function sign($param = array()) {
		$url = $param['url'];
		$algorithm = "HmacSHA256";
		$stringToSign = null;

		$data = 'POST';
		$data .= "\n";
		$endpoint = parse_url($url);
		$data .= $endpoint['host'];
		$data .= "\n";

		$data .= $param['api_action'];
		unset($param['api_action']);
		$data .= "\n";
		uksort($param, 'strcmp');

		$data .= $this->_getParametersAsString($param);

		if ($algorithm === 'HmacSHA1') {
			$hash = 'sha1';
		} else if ($algorithm === 'HmacSHA256') {
			$hash = 'sha256';
		}
		return base64_encode(hash_hmac($hash, $data, $this->AWSAccessKeyValue, true));
	}

	/**
	 * array2str
	 * @param array $params
	 * @return string
	 */
	private function _getParametersAsString(array $params) {
		$queryParameters = array();
		foreach ($params as $key => $value) {
			$queryParameters[] = $key . '=' . str_replace('%7E', '~', rawurlencode($value));
		}
		return implode('&', $queryParameters);
	}

	/**
	 * 亚马逊订制请求(socket)
	 * @param $query
	 * @param $url
	 */
	public function _httpPost($query, $url) {
		$url = parse_url($url);

		$uri = array_key_exists('path', $url) ? $url['path'] : null;
		if (!isset($uri)) {
			$uri = "/";
		}
		$post = "POST " . $uri . " HTTP/1.0\r\n";
		$post .= "Host: " . $url['host'] . "\r\n";
		$post .= "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n";
		$post .= "Content-Length: " . strlen($query) . "\r\n";
		$post .= "User-Agent: " .
			'eFAST/(Language=PHP/5.4.0;)' .
			"\r\n";
		$post .= "\r\n";
		$post .= $query;
		$port = array_key_exists('port', $url) ? $url['port'] : null;
		switch ($url['scheme']) {
			case 'https':
				$scheme = 'ssl://';
				$port = $port === null ? 443 : $port;
				break;
			default:
				$scheme = '';
				$port = $port === null ? 80 : $port;
		}
		$response = '';

		if ($socket = @fsockopen($scheme . $url['host'], $port, $errno, $errstr, 10)) {

			fwrite($socket, $post);

			while (!feof($socket)) {
				$response .= fgets($socket, 1160);
			}
			fclose($socket);

			list($other, $responseBody) = explode("\r\n\r\n", $response, 2);
			$other = preg_split("/\r\n|\n|\r/", $other);
			list($protocol, $code, $text) = explode(' ', trim(array_shift($other)), 3);
		} else {
			throw new Exception("Unable to establish connection to host " . $url['host'] . " $errstr");
		}

		return array('code' => $code, 'response' => $responseBody);
	}

	/**
	 * 批量发送第三方平台请求
	 */
	public function request_send_multi($api, $params = array()) {
		// TODO: Implement request_send_multi() method.
	}

	/**
	 * 批量下载商品信息
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_list_download(array $data) {
		// TODO: Implement goods_list_download() method.
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
		// TODO: Implement goods_info_download_multi() method.
	}

	/**
	 * 订单列表下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @param $data
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function order_list_download($data) {

		$data['start_modified'] = '2014-04-01';
		$page_no = $data['page_no'];

		$params = $this->list_order_common_param();

		$params['MarketplaceId.Id.1'] = $this->MarketplaceId;
		$params['Action'] = 'ListOrders';

		$params['MaxResultsPerPage'] = $this->order_page_size;

		if (isset($data['start_modified']) && !empty($data['start_modified'])) {
			$params['CreatedAfter'] = date(DATE_ISO8601, strtotime($data['start_modified']));
		} else {
			$params['CreatedAfter'] = date(DATE_ISO8601, strtotime('-7 day'));
		}

		//下一页的请求
		if (isset($data['NextToken'])) {
			$params = $this->list_order_common_param();

			$params['NextToken'] = $data['NextToken'];
			$params['Action']='ListOrdersByNextToken';
		}

		$result = $this->request_send('https://mws.amazonservices.com.cn', $params);

		$_return = $this->xml2array($result['response']);

		if (isset($_return['ErrorResponse'])) {
			$msg = $_return['ErrorResponse']['Error']['Message'];
			throw new ExtException($msg);
		}

		$return = array(
			//转成与淘宝类似的返回格式
			'trades' => array('trade' => $_return[$params['Action'].'Response'][$params['Action'].'Result']['Orders']['Order']),
		);
		if (isset($_return[$params['Action'].'Response'][$params['Action'].'Result']['NextToken'])) {
			$return['total_results'] = ($page_no + 1) * $this->order_page_size;
			$return['extra_data'] = array('NextToken' => $_return[$params['Action'].'Response'][$params['Action'].'Result']['NextToken']);
		} else {
			$return['total_results'] = $this->order_page_size;
			$return['extra_data'] = array();
		}

		return $return;
	}

	/**
	 * 订单列表公共参数
	 */
	private function list_order_common_param() {
		$params['api_type'] = 'order';
		$params['api_action'] = '/Orders/2013-09-01';
		$params['Version'] = '2013-09-01';
		return $params;
	}

	/**
	 * 单个订单下载
	 * @param $id
	 * @param $data
	 * @author hunter
	 * @date 2015-04-29
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function order_info_download($id, $data = array()) {

		$params = array();
		$params['AmazonOrderId'] = $id;
		$params['api_type'] = 'order';
		$params['api_action'] = '/Orders/2013-09-01';
		$params['Version'] = '2013-09-01';
		$params['Action'] = 'ListOrderItems';
		$result = $this->request_send('https://mws.amazonservices.com.cn', $params);
		$_return = $this->xml2array($result['response']);

		if (isset($_return['ErrorResponse'])) {
			$msg = $_return['ErrorResponse']['Error']['Message'];
			throw new ExtException($msg);
		}

		$data['OrderItems'] = $_return['ListOrderItemsResponse']['ListOrderItemsResult']['OrderItems'];
		return $data;
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
		// TODO: Implement inv_upload() method.
	}

	/**
	 * 批量库存信息回传
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-10
	 * @return array 返回结果和第三方平台原始信息
	 */
	public function inv_upload_multi(array $data) {
		// TODO: Implement inv_upload_multi() method.
	}

	/**
	 * 发货信息回传
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回结果和第三方平台原始信息
	 */
	public function logistics_upload(array $data) {
		// TODO: Implement logistics_upload() method.
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
		// TODO: Implement _trans_goods() method.
	}

	/**
	 * 转换SKU信息为标准信息
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-12
	 * @param array $data 第三方平台商品SKU信息
	 */
	public function _trans_sku(array $data) {
		// TODO: Implement _trans_sku() method.
	}

	/**
	 * 转换订单信息为标准订单
	 * @author hunter
	 * @date 2015-04-29
	 * @param $shop_code
	 * @param array $data 第三方平台订单信息
	 */
	public function _trans_order($shop_code, array $data) {
		$datetime = date('Y-m-d H:i:s');

		$return = array();
		$return['tid'] = $data['AmazonOrderId'];
		$return['source'] = 'yamaxun';
		$return['shop_code'] = $shop_code;
		$return['status'] = $data['OrderStatus'] == 'Unshipped' ? 1 : 0;

		$return['trade_from'] = '';
		$return['pay_type'] = $data['PaymentMethod'] == 'COD' ? 1 : 0;

		$return['pay_time'] = $data['PaymentMethod'] == 'COD' ? '': date('Y-m-d H:i:s',strtotime($data['LastUpdateDate']));

		$return['seller_nick'] = '';
		$return['buyer_nick'] = '';

		//收货信息
		$return['receiver_name'] = $data['ShippingAddress']['Name'];
		$return['receiver_country'] = $data['ShippingAddress']['CountryCode'];
		$return['receiver_province'] = $data['ShippingAddress']['StateOrRegion'];
		$return['receiver_city'] = $data['ShippingAddress']['City'];
		$return['receiver_district'] = $data['ShippingAddress']['County'];
		$return['receiver_street'] = '';
		//receiver_address是需要去掉国家的地址
		$return['receiver_address'] = $data['ShippingAddress']['StateOrRegion'].' '.$data['ShippingAddress']['City'].' '.$data['ShippingAddress']['County'].' '.$data['ShippingAddress']['AddressLine1'].$data['ShippingAddress']['AddressLine2'];
		$return['receiver_address'] .= isset($data['ShippingAddress']['AddressLine3']) ? $data['ShippingAddress']['AddressLine3'] : '';

		//receiver_addr是需要去掉国家省市区的地址
		$return['receiver_addr'] = $data['ShippingAddress']['AddressLine1'].$data['ShippingAddress']['AddressLine2'];
		$return['receiver_addr'] .= isset($data['ShippingAddress']['AddressLine3']) ? $data['ShippingAddress']['AddressLine3'] : '';
		$return['receiver_zip_code'] = $data['ShippingAddress']['PostalCode'];

		$return['receiver_mobile'] = $data['ShippingAddress']['Phone'];
		$return['receiver_phone'] = '';
		$return['receiver_email'] = '';
		$return['express_code'] = '';
		$return['express_no'] = '';
		$return['hope_send_time'] = '';

		//订单总数量
		$num = $sku_num = $all_goods_money = 0;
		$receiptInfo = array();//发票
		if (isset($data['OrderItems']['OrderItem'][0])) {
			foreach ($data['OrderItems']['OrderItem'] as $value) {
				$num += $value['QuantityOrdered'];
				$all_goods_money += $value['ItemPrice']['Amount'];
				$receiptInfo = $value['InvoiceData'];
			}
			$sku_num = count($data['OrderItems']['OrderItem']);
		} else {
			$num = $data['OrderItems']['OrderItem']['QuantityOrdered'];
			$sku_num = 1;
			$all_goods_money += $data['OrderItems']['OrderItem']['ItemPrice']['Amount'];
			$receiptInfo = $data['OrderItems']['OrderItem']['InvoiceData'];
		}

		$return['num'] = $num;//订单总数量
		$return['sku_num'] = $sku_num; //平台sku种类数量
		$return['goods_weight'] = 0;

		$return['buyer_remark'] = '';
		$return['seller_remark'] = '';
		$return['seller_flag'] = '';

		$return['express_money'] = $data['OrderTotal']['Amount'] - $all_goods_money;
		$return['delivery_money'] = 0;
		$return['gift_coupon_money'] = '';
		$return['gift_money'] = '';
		$return['integral_change_money'] = 0;

		$return['order_money'] = $data['OrderTotal']['Amount'];
		$return['buyer_money'] = '';
		$return['alipay_no'] = '';
		$return['coupon_change_money'] = '';
		$return['balance_change_money'] = '';

		$return['is_lgtype'] = '';
		$return['seller_rate'] = '';
		$return['buyer_rate'] = '';

		//发票
		$return['invoice_type'] = '';
		$return['invoice_title'] = $receiptInfo['InvoiceTitle'];
		$return['invoice_content'] = $receiptInfo['BuyerSelectedInvoiceCategory'];
		$return['invoice_money'] = '';
		$return['invoice_pay_type'] = '';

		$order_first_insert_time = date('Y-m-d H:i:s',strtotime($data['PurchaseDate']));

		$return['order_last_update_time'] = $return['last_update_time'] = date('Y-m-d H:i:s', strtotime($data['LastUpdateDate']));
		$return['order_first_insert_time'] = $order_first_insert_time;
		$return['first_insert_time'] = $datetime;
		return $return;
	}

	/**
	 * 转换订单明细信息为标准订单明细
	 * @author hunter
	 * @date 2015-04-29
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_order_detail(array $data) {
		$return = array();

		$total = 0;
		$avg_total = 0;

		if (isset($data['OrderItems']['OrderItem'][0])) {
			foreach ($data['OrderItems']['OrderItem'] as $value) {
				$total += $value['ItemPrice']['Amount'];
			}
		} else {
			$total = $data['OrderItems']['OrderItem']['ItemPrice']['Amount'];
		}

		$base = $total;

		if (isset($data['OrderItems']['OrderItem'][0])) {
			foreach ($data['OrderItems']['OrderItem'] as $key => $value) {
				$detail = array();
				$detail['tid'] = $data['AmazonOrderId'];
				$detail['oid'] = $value['OrderItemId'];
				$detail['source'] = 'yamaxun';
				//$detail['return_status'] = '';
				$detail['title'] = $value['Title'];
				$detail['price'] = $value['ItemPrice']['Amount']/$value['QuantityOrdered'];
				$detail['num'] = $value['QuantityOrdered'];
				$detail['goods_code'] = $value['ASIN'];
				$detail['sku_id'] = $value['SellerSKU'];
				$detail['goods_barcode'] = $value['SellerSKU'];
				$detail['total_fee'] = $value['ItemPrice']['Amount'];
				$detail['payment'] = 0;
				$detail['discount_fee'] = 0;
				$detail['adjust_fee'] = 0;

				$detail['avg_money'] = $base - $avg_total;

				$avg_total += $detail['avg_money'];
				$detail['end_time'] = '';

				$return[] = $detail;
			}
		} else {
			$value = $data['OrderItems']['OrderItem'];
			$detail = array();
			$detail['tid'] = $data['AmazonOrderId'];
			$detail['oid'] = $value['OrderItemId'];
			$detail['source'] = 'yamaxun';
			//$detail['return_status'] = '';
			$detail['title'] = $value['Title'];
			$detail['price'] = $value['ItemPrice']['Amount']/$value['QuantityOrdered'];
			$detail['num'] = $value['QuantityOrdered'];
			$detail['goods_code'] = $value['ASIN'];
			$detail['sku_id'] = $value['SellerSKU'];
			$detail['goods_barcode'] = $value['SellerSKU'];
			$detail['total_fee'] = $value['ItemPrice']['Amount'];
			$detail['payment'] = 0;
			$detail['discount_fee'] = 0;
			$detail['adjust_fee'] = 0;

			$detail['avg_money'] = $base - $avg_total;

			$avg_total += $detail['avg_money'];
			$detail['end_time'] = '';

			$return[] = $detail;
		}

		//校验均摊金额, 其实没必要
		if ($base != $avg_total) {
			throw new ExtException('订单均摊金额计算异常', -1);
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
		return $ret = load_model('source/yamaxun/ApiYamaxunTradeModel')->save_trade_and_order($shop_code, $data);
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
		// TODO: Implement save_source_goods_and_sku() method.
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