<?php
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');

/**
 * 银泰API类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class YintaiAPIModel extends AbsAPIModel {

	public $gate = 'http://api.yintai.com/openapi/service';

	private $AppKey;
	private $SecrectKey;
	private $ClientId;
	private $ClientName;

	public function __construct($token) {

		$this->AppKey = $token['AppKey'];
		$this->SecrectKey = $token['SecrectKey'];
		$this->ClientId = $token['ClientId'];
		$this->ClientName = $token['ClientName'];
		$this->vendorID = $token['vendorID'];
		$this->verdorName = $token['verdorName'];

		$this->order_page_size = 100;
		$this->refund_page_size = 20;

		$this->order_pk = 'SONumber';
		$this->goods_pk = '';
		$this->refund_pk = 'SoNumber';
	}

	/**
	 * 第三方平台请求发送
	 */
	public function request_send($api, $param = array()) {
		$sys_param = array();
		$sys_param['ClientName'] = $this->ClientName;
		$sys_param['ClientId'] = $this->ClientId;
		$sys_param['Content_type'] = 'json';
		$sys_param['sip_http_method'] = 'post';
		$sys_param['signtype'] = 0;
		$sys_param['signMethod'] = 0;
		$sys_param['Date'] = date('YmdHis');
		$sys_param['TimeReq'] = date('YmdHis');
		$sys_param['ver'] = '1.0';
		$sys_param['method'] = $api;
		$sys_param['Language'] = "Chinese";

		$data = array_merge($sys_param, $param);
		$strParam_s1 = "APPKEY=$this->AppKey&SECRECTKEY=$this->SecrectKey&TIMEREQ={$data['TimeReq']}";
		foreach ($param as $key => $value) {
			if ('' == $key || !isset($value)) {
				continue;
			}
			if ("@" == substr($value, 0, 1)) {
				continue;
			}
			$strParam_s1 .= "&" . strtoupper($key) . "=" . urlencode($value);
		}
		$strParam_s1 = rtrim($strParam_s1, '&');

		$strParam_s2 = strrev($strParam_s1);

		$strParam = '';
		$strlen = strlen($strParam_s1);
		for ($i = 0; $i < $strlen; $i++) {
			$strParam .= ord($strParam_s1[$i]) ^ ord($strParam_s2[$i]);
		}

		$sign = md5($strParam);

		$data['sign'] = $sign;

		$url = $this->gate;
		$result = $this->exec($url, $data);
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
		// TODO: Implement goods_list_download() method.
	}

	/**
	 * 单个商品信息下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_info_download($data) {

	}

	/**
	 * 批量商品信息下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-14
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_info_download_multi($ids, $data = array()) {

	}

	/**
	 * 订单列表下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @param $data
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function order_list_download($data) {
		$params = array();
		$params['vendorID'] = $this->vendorID;
		$params['verdorName'] = $this->verdorName;
		$params['orderStatus'] = 20;

		if (isset($data['start_modified']) && !empty($data['start_modified'])) {
			$params['startTime'] = date('YmdHis', strtotime($data['start_modified']));
		} else {
			$params['startTime'] = date('YmdHis', strtotime('-7 day'));
		}
		if (isset($data['end_modified']) && !empty($data['end_modified'])) {
			$params['endTime'] = date('YmdHis', strtotime($data['end_modified']));
		} else {
			$params['endTime'] = date('YmdHis');
		}

		$result = $this->json_decode($this->request_send('Yintai.OpenApi.Vendor.GetOrderList', $params));
		if (200 != $result['statusCode']) {
			$msg = $result['description'];
			throw new ExtException($msg, $result['statusCode']);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'trades' => array('trade' => $result['Data']),
				'total_results' => count($result['Data']) > 0 ? $this->order_page_size : 0, //默认100，是为了跟每页获取数据一致，银泰无分页获取数据
			);
			return $return;
		}
	}

	/**
	 * 单个订单下载
	 * @author hunter
	 * @date 2015-03-09
	 * @param $id
	 * @param $data
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function order_info_download($id, $data = array()) {
		$params = array();
		$params['soNumber'] = $id;

		$result = $this->json_decode($this->request_send('Yintai.OpenApi.Vendor.GetVendorOrderInfo', $params));
		if (200 != $result['statusCode']) {
			$msg = $result['description'];
			throw new ExtException($msg, $result['statusCode']);
		}

		//商品明细
		$detal_result = $this->json_decode($this->request_send('Yintai.OpenApi.Vendor.GetOrderDetail', $params));
		if (200 != $detal_result['statusCode']) {
			$msg = $detal_result['description'];
			throw new ExtException($msg, $detal_result['statusCode']);
		}

		$return = $result['Data'];
		$return['orderDetail'] = $detal_result['Data'];
		return $return;
	}

	/**
	 * 退单列表下载
	 * @author hunter
	 * @date 2015-04-10
	 * @param $data
	 * @return array 返回退单列表信息
	 */
	public function refund_list_download(array $data) {
		$params = array();
		$params['rmaType'] = 0;

		$params['currentPage'] = isset($data['page_no']) ? $data['page_no'] : 1;

		if (isset($data['start_modified']) && !empty($data['start_modified'])) {
			$params['startTime'] = date('YmdHis', strtotime($data['start_modified']));
		} else {
			$params['startTime'] = date('YmdHis', strtotime('-3 day'));
		}

		if (isset($data['end_modified']) && !empty($data['end_modified'])) {
			$params['endTime'] = date('YmdHis', strtotime($data['end_modified']));
		} else {
			$params['endTime'] = date('YmdHis');
		}

		$result = $this->json_decode($this->request_send('Yintai.OpenApi.Vendor.GetRMAOrderList', $params));
		if (200 != $result['statusCode']) {
			$msg = $result['description'];
			throw new ExtException($msg, $result['statusCode']);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'refunds' => array(
					'refund' => $result['Data']),
				'total_results' => count($result['Data']) > 0 ? $this->refund_page_size : 0,
			);

			return $return;
		}
	}

	/**
	 * 单个退单明细下载
	 * @author hunter
	 * @date 2015-04-10
	 * @return array 返回退单明细信息
	 */
	public function refund_info_download($refund_id, $refund_info) {
		return $refund_info;
	}

	/**
	 * 库存信息回传
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回结果和第三方平台原始信息
	 */
	public function inv_upload(array $data) {
		$params = array();
		$params['vendorID'] = $this->vendorID;
		$params['vendorItemCode'] = $data['goods_barcode'];
		$params['quantity'] = $data['inv_num'];

		$result = $this->json_decode($this->request_send('Yintai.OpenApi.Vendor.InventorySync', $params));
		if (200 != $result['statusCode']) {
			$msg = $result['description'];
			throw new ExtException($msg, $result['statusCode']);
		} else {
			//转成与淘宝类似的返回格式
			$return = $result;
			return $return;
		}
	}

	/**
	 * 批量库存信息回传
	 * @author hunter
	 * @date 2015-03-10
	 * @return array 返回结果和第三方平台原始信息
	 */
	public function inv_upload_multi(array $data) {
		$return = array();
		foreach ($data as $item) {
			$return[] = $this->inv_upload($item);
		}
		return $return;
	}

	/**
	 * 发货信息回传
	 * @author hunter
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
	 * @date 2015-03-12
	 * @param array $data 第三方平台订单信息
	 */
	public function _trans_order($shop_code, array $data) {
		$datetime = date('Y-m-d H:i:s');
		$return = array();
		$return['tid'] = $data['SONumber'];
		$return['source'] = 'yintai';
		$return['shop_code'] = $shop_code;
		$return['status'] = $data['OrderStatus'] == '20' ? 1 : 0;
		$return['trade_from'] = '';
		$return['pay_type'] = $data['IsCod'] == 1 ? 1 : 0;
		$return['pay_time'] = '';

		//昵称暂无
		$return['seller_nick'] = '';
		$return['buyer_nick'] = $data['BuyerName'];
		$return['receiver_name'] = $data['ShippingContactWith'];

		$return['receiver_country'] = '中国';
		$return['receiver_province'] = $data['ShippingProvince'];
		$return['receiver_city'] = $data['ShippingCity'];
		$return['receiver_district'] = $data['ShippingArea'];

		$return['receiver_street'] = '';
		$return['receiver_address'] = $data['ShippingProvince'] . ' ' . $data['ShippingCity'] . ' ' . $data['ShippingArea'] . ' ' . $data['ShippingAddress'];
		//receiver_addr是需要去掉省市区的地址
		$return['receiver_addr'] = $data['ShippingAddress'];
		$return['receiver_zip_code'] = $data['ShippingPostCode'];
		$return['receiver_mobile'] = $data['ShippingMobilePhone'];
		$return['receiver_phone'] = $data['ShippingMobilePhone'];
		$return['receiver_email'] = '';
		$return['express_code'] = '';
		$return['express_no'] = '';
		$return['hope_send_time'] = '';

		//订单总数量
		$num = 0;
		foreach ($data['orderDetail'] as $i) {
			$num = $num + $i['BuyCount'];
		}
		$return['num'] = $num;
		$return['sku_num'] = count($data['orderDetail']); //平台sku种类数量
		$return['goods_weight'] = 0;
		$return['buyer_remark'] = '';
		$return['seller_remark'] = '';

		$return['seller_flag'] = '';
		$return['order_money'] = $data['PayAmount'];
		$return['express_money'] = $data['ShippingVARCHARge'];
		$return['delivery_money'] = 0;
		$return['gift_coupon_money'] = '';
		$return['gift_money'] = 0;
		$return['integral_change_money'] = 0;
		$return['buyer_money'] = $data['PaidAmount'];
		$return['alipay_no'] = '';
		$return['coupon_change_money'] = '';

		$return['balance_change_money'] = 0;
		$return['is_lgtype'] = '';
		$return['seller_rate'] = '';
		$return['buyer_rate'] = '';
		$return['invoice_type'] = '';
		$return['invoice_title'] = '';
		$return['invoice_content'] = '';

		$return['invoice_money'] = '';
		$return['invoice_pay_type'] = '';

		$return['order_last_update_time'] = $return['last_update_time'] = $data['UpdateTime'];
		$return['order_first_insert_time'] = $data['TradeCreateTime'];
		$return['first_insert_time'] = $datetime;
		return $return;
	}

	/**
	 * 转换订单明细信息为标准订单明细
	 * @author hunter
	 * @date 2015-03-12
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_order_detail(array $data) {
		$return = array();

		$total = 0;
		$avg_total = 0;
		$base = $data['ScoreAmout'] + $data['GoodsAmount'];

		foreach ($data['orderDetail'] as $value) {
			$total += $value['GoodPrice'] * $value['BuyCount'];
		}

		foreach ($data['orderDetail'] as $key => $value) {
			$detail = array();
			$detail['tid'] = $value['SONumber'];
			$detail['oid'] = $value['ChildOrderNumber'];
			$detail['source'] = 'yintai';
			//$detail['return_status'] = '';
			$detail['title'] = $value['GoodName'];
			$detail['price'] = $value['GoodPrice'];
			$detail['num'] = $value['BuyCount'];
			$detail['goods_code'] = $value['VendorItemCode'];
			$detail['sku_id'] = '';
			$detail['goods_barcode'] = $value['VendorItemCode'];
			$detail['total_fee'] = $value['GoodPrice'] * $value['BuyCount'];
			$detail['payment'] = $value['PayAmount'];
			$detail['discount_fee'] = 0;
			$detail['adjust_fee'] = 0;

			if ($key < count($data['orderDetail']) - 1) {
				$detail['avg_money'] = $base * ($value['PayAmount']) / $total;
			} else {
				$detail['avg_money'] = $base - $avg_total;
			}
			$avg_total += $detail['avg_money'];

			$detail['end_time'] = '';
			$detail['sku_properties'] = $value['PropertyDescription'];

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
		$refund = array();
		$refund['refund_id'] = $data['SoNumber'];
		$refund['refund_type'] = 1;
		$refund['tid'] = $data['SoNumber'];
		$refund['oid'] = '';
		$refund['source'] = 'yintai';
		$refund['shop_code'] = $shop_code;
		$refund['status'] = 1;
		$refund['is_change'] = 0;
		//商家联系人(回寄联系人)
		$refund['seller_nick'] = '';
		//顾客姓名
		$refund['buyer_nick'] = '';
		$refund['has_good_return'] = count($data['RMAItems']) > 0 ? 'TRUE' : 'FALSE';
		$refund['refund_fee'] = 0;//暂无
		$refund['payment'] = 0;

		$refund['refund_reason'] = '';
		$refund['refund_desc'] = '';

		$refund['refund_express_code'] = '';
		$refund['refund_express_no'] = '';
		$refund['attribute'] = '';
		$refund['change_remark'] = '';
		return $refund;
	}

	/**
	 * 转换退单明细信息为标准退单明细信息
	 * @author hunter
	 * @date 2015-03-23
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_refund_detail(array $data) {
		$return = array();
		if (isset($data['RMAItems'])) {

			foreach ($data['RMAItems'] as $v) {
				$detail = array();
				$detail['refund_id'] = $data['SONumber'];
				$detail['tid'] = $data['SONumber'];
				$detail['oid'] = '';
				$detail['goods_code'] = $v['ItemCode'];
				$detail['title'] = $v['ItemName'];
				$detail['price'] = $v['UnitPrice'];
				$detail['num'] = $v['Quantity'];
				$detail['refund_price'] = '0';
				$return[] = $detail;
			}
		}
		return $return;
	}

	/**
	 * 保存原始订单和订单明细数据
	 * @param $shop_code
	 * @param $data
	 * @return mixed
	 */
	public function save_source_order_and_detail($shop_code, $data) {
		return $ret = load_model('source/ApiYintaiTradeModel')->save_trade_and_order($shop_code, $data);
	}

	/**
	 * 保存原始退单和退单数据
	 * @param $shop_code
	 * @param $data
	 * @return mixed
	 */
	public function save_source_refund($shop_code, $data) {
		return $ret = load_model('source/ApiYintaiRefundModel')->save_yintai_refund($shop_code, $data);
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