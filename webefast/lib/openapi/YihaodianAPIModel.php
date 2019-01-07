<?php
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');

/**
 * 一号店API类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class YihaodianAPIModel extends AbsAPIModel {

	public $gate = 'http://openapi.yhd.com/app/api/rest/router';

	private $app_key;
	private $secret;
	private $session;

	public function __construct($token) {

		$this->app_key = $token['app_key'];
		$this->secret = $token['secret'];
		$this->session = $token['session'];

		$this->order_pk = 'orderCode';
		$this->goods_pk = 'productId';
		$this->refund_pk = 'refundCode';
		$this->order_page_size = 100;
		$this->goods_page_size = 100;
		$this->refund_page_size = 100;
		$this->goods_ext = true;
	}

	/**
	 * api发送请求
	 * @param $api
	 * @param array $param
	 */
	public function request_send($api, $param = array()) {
		$data = $param;
		$data['appKey'] = $this->app_key;
		$data['sessionKey'] = $this->session;
		$data['format'] = 'json';
		$data['ver'] = '1.0';
		$data['method'] = $api;
		$data['timestamp'] = date('Y-m-d H:i:s');
		$data['sdkType'] = 'php';

		$sign = $this->sign($data);
		$data['sign'] = $sign;

		$url = $this->gate;
		//sleep(1);//一号店限制每分钟调用接口不能超过150次
		$result = $this->exec($url, $data);
		return $result;
	}

	/**
	 * 签名
	 * @param array $param
	 */
	public function sign($param = array()) {
		$sign = $this->secret;
		ksort($param);
		foreach ($param as $k => $v) {
			$sign .= $k . $v;
		}
		unset($k, $v);
		$sign .= $this->secret;
		return md5($sign);
	}

	/**
	 * 订单下载
	 * @param $data
	 * @date 2015-04-02
	 * @link http://open.yhd.com/opendoc/yhd.orders.get.html
	 */
	public function order_list_download($data) {
		sleep(5);
		$params = array();

		$params['orderStatusList'] = 'ORDER_WAIT_PAY,ORDER_PAYED,ORDER_WAIT_SEND,ORDER_ON_SENDING,ORDER_RECEIVED,ORDER_FINISH,ORDER_CANCEL';

		if (isset($data['start_modified']) && !empty($data['start_modified'])) {
			$params['startTime'] = date('Y-m-d H:i:s', strtotime('-10 minute',strtotime($data['start_modified'])));
		} else {
			$params['startTime'] = date('Y-m-d H:i:s', strtotime('-15 day'));
		}
		//查询结束时间(查询开始、结束时间跨度不能超过15天)
		if (isset($data['end_modified']) && !empty($data['end_modified'])) {
			$params['endTime'] = $data['end_modified'];
		} else {
			$params['endTime'] = date('Y-m-d H:i:s', strtotime('+15 day', strtotime($params['startTime'])));
		}

		//按订单编辑时间
		$params['dateType'] = 5;

		$params['curPage'] = isset($data['page_no']) ? $data['page_no'] : 1;
		$params['pageRows'] = isset($data['page_size']) ? $data['page_size'] : 100;

		$result = $this->json_decode($this->request_send('yhd.orders.get', $params));

		if ($result['response']['errorCount'] > 0) {
			$msg = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
			//	throw new ExtException($msg, $result['response']['errInfoList']['errDetailInfo'][0]['errorCode']);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'trades' => array('trade' => $result['response']['orderList']['order']),
				'total_results' => $result['response']['totalCount'],
			);
			return $return;
		}
	}

	/**
	 * 批量发送第三方平台请求
	 */
	public function request_send_multi($api, $params = array()) {
		// TODO: Implement request_send_multi() method.
	}

	/**
	 * 批量下载商品信息
	 * @author hunter
	 * @date 2015-03-09
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_list_download(array $data) {
		// TODO: Implement goods_list_download() method.

		return $this->goods_list_download_by_serial($data);

		//return $this->goods_list_download_by_general($data);
	}

	/**
	 * 一号店商品下载（系列）
	 * @param $data
	 */
	public function goods_list_download_by_serial($data) {
		$params = array();

		$params['curPage'] = isset($data['page_no']) ? $data['page_no'] : 1;
		$params['pageRows'] = isset($data['page_size']) ? $data['page_size'] : 100;

		$params['verifyFlg'] = 2;//审核通过
		$result = $this->json_decode($this->request_send('yhd.serial.products.search', $params));

		if ($result['response']['errorCount'] > 0) {
			$msg = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'total_results' => $result['response']['totalCount'],
				'items' => array('item' => $result['response']['serialProductList']['serialProduct'])
			);
			return $return;
		}
	}

	/**
	 * 普通商品下载
	 * @param $data
	 */
	public function goods_list_download_by_general($data) {
		$params = array();

		$params['curPage'] = isset($data['curPage']) ? $data['curPage'] : 1;
		$params['pageRows'] = isset($data['pageRows']) ? $data['pageRows'] : 100;

		$params['verifyFlg'] = 2;//审核通过
		$result = $this->json_decode($this->request_send('yhd.general.products.search', $params));

		if ($result['response']['errorCount'] > 0) {
			$msg = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'total_results' => $result['response']['totalCount'],
				'items' => array('item' => $result['response']['productList']['product'])
			);
			return $return;
		}
	}

	/**
	 * 单个商品信息下载
	 * @author hunter
	 * @date 2015-03-09
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_info_download($data) {
		// TODO: Implement goods_info_download() method.
	}

	/**
	 * 单个商品信息下载 (系列)
	 * @param $productId
	 */
	public function goods_info_download_by_serial($productId) {

		//主商品
		$product_result = $this->json_decode($this->request_send('yhd.serial.products.search', array('productIdList' => $productId)));
		if ($product_result['response']['errorCount'] > 0) {
			$msg = $product_result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		}

		//商品属性
		$product_attr = $this->json_decode($this->request_send('yhd.product.attribute.single.get', array('productId' => $productId)));
		if ($product_attr['response']['errorCount'] > 0) {
			$msg = $product_attr['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		}

		//商品描述
		$product_desc = $this->json_decode($this->request_send('yhd.product.description.get', array('productIdList' => $productId)));
		if ($product_desc['response']['errorCount'] > 0) {
			$msg = $product_desc['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		}

		//sku
		$params = array();
		$params['productId'] = $productId;
		$sku_result = $this->json_decode($this->request_send('yhd.serial.product.get', $params));

		if ($sku_result['response']['errorCount'] > 0) {
			$msg = $sku_result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		}

		//处理批量下载价格id串
		$sku_productIds = array();
		foreach ($sku_result['response']['serialChildProdList']['serialChildProd'] as $sku) {
			$sku_productIds[] = $sku['productId'];
		}

		$str_sku_productIds = null;
		foreach($sku_productIds as $productId) {
			$str_sku_productIds .= $productId.',';
		}
		$str_sku_productIds = substr($str_sku_productIds, 0, strlen($str_sku_productIds)-1);
		$sku_products_prices = $this->json_decode($this->request_send('yhd.products.price.get', array('productIdList' => $str_sku_productIds)));
		if ($sku_products_prices['response']['errorCount'] > 0) {
			$msg = $sku_products_prices['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		}
		$_sku_prices = array();
		foreach($sku_products_prices['response']['pmPriceList']['pmPrice'] as $price) {
			$_sku_prices[$price['productId']] = $price;
		}

		foreach ($sku_result['response']['serialChildProdList']['serialChildProd'] as &$sku) {
			//属性（sku）颜色尺码
//			$sku_serial_product_attribute_result = $this->json_decode($this->request_send('yhd.serial.product.attribute.get', array('productId' => $sku['productId'])));
//			if ($sku_serial_product_attribute_result['response']['errorCount'] > 0) {
//				$msg = $sku_serial_product_attribute_result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
//				throw new ExtException($msg);
//			}
//			$sku['attributes'] = $sku_serial_product_attribute_result['response'];
			$sku['attributes'] = array();

//			//价格
//			$sku_products_price = $this->json_decode($this->request_send('yhd.products.price.get', array('productIdList' => $sku['productId'])));
//			if ($sku_products_price['response']['errorCount'] > 0) {
//				$msg = $sku_products_price['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
//				throw new ExtException($msg);
//			}
//			$sku['prices'] = $sku_products_price['response'];

			if (array_key_exists($sku['productId'], $_sku_prices)) {
				$sku['prices'] = $_sku_prices[$sku['productId']];
			}
		}

		$result = $product_result['response']['serialProductList']['serialProduct'][0];
		$result['skus'] = $sku_result['response']['serialChildProdList']['serialChildProd'];
		$result['attributes'] = $product_attr['response'];
		$result['desc'] = $product_desc['response'];
		return $result;
	}

	/**
	 * 普通商品详情
	 * @param $productId
	 */
	public function goods_info_download_by_general($productId) {

		//商品属性
		$product_attr = $this->json_decode($this->request_send('yhd.product.attribute.single.get', array('productId' => $productId)));
		if ($product_attr['response']['errorCount'] > 0) {
			$msg = $product_attr['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		}

		//商品描述
		$product_desc = $this->json_decode($this->request_send('yhd.product.description.get', array('productIdList' => $productId)));
		if ($product_desc['response']['errorCount'] > 0) {
			$msg = $product_desc['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		}

		//价格
		$products_price = $this->json_decode($this->request_send('yhd.products.price.get', array('productIdList' => $productId)));
		if ($products_price['response']['errorCount'] > 0) {
			$msg = $products_price['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		}

		//库存
		$products_stock = $this->json_decode($this->request_send('yhd.products.stock.get', array('productIdList' => $productId)));
		if ($products_stock['response']['errorCount'] > 0) {
			$msg = $products_stock['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		}

		$result = array();
		$result['attributes'] = $product_attr['response'];
		$result['desc'] = $product_desc['response'];
		$result['price'] = $products_price['response'];
		$result['stock'] = $products_stock['response'];
		return $result;
	}

	/**
	 * 批量商品信息下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-14
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_info_download_multi($ids, $data = array()) {
		// TODO: Implement goods_info_download_multi() method.
		return $this->goods_info_download_multi_by_serial($ids, $data);

		//return $this->goods_info_download_multi_by_general($ids, $data);
	}

	/**
	 * 批量商品信息下载（系列）
	 * @param $ids
	 * @param $data
	 */
	public function goods_info_download_multi_by_serial($ids, $data) {

		$result = array();
		foreach ($ids as $productId) {
			$_result = $this->goods_info_download_by_serial($productId);
			$result = array_merge($result, array($_result));
		}

		return $result;
	}

	/**
	 * 批量商品信息下载（系列）
	 * @param $ids
	 * @param $data
	 */
	public function goods_info_download_multi_by_general($ids, $data) {

		foreach ($ids as $productId) {
			$_result = $this->goods_info_download_by_general($productId);
			$data[$productId]['attributes'] = $_result['attributes'];
			$data[$productId]['desc'] = $_result['desc'];
			$data[$productId]['price'] = $_result['price'];
		}

		return $data;
	}

	/**
	 * 一号店单个订单下载
	 * @author hunter
	 * @date 2015-04-02
	 * @param $id
	 * @param $data
	 * @return array
	 * @link http://open.yhd.com/opendoc/yhd.order.detail.get.html
	 */
	public function order_info_download($id, $data = array()) {

		$params = array();
		$params['orderCode'] = $id;

		$result = $this->json_decode($this->request_send('yhd.order.detail.get', $params));

		//dump($result,1);
		if ($result['response']['errorCount'] > 0) {
			$msg = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
			//	throw new ExtException($msg, $result['response']['errInfoList']['errDetailInfo'][0]['errorCode']);
		} else {
			//转成与淘宝类似的返回格式
			$return = $result['response']['orderInfo'];
			return $return;
		}
	}

	/**
	 * 库存信息回传
	 * @author hunter
	 * @date 2015-03-09
	 * @return array 返回结果和第三方平台原始信息
	 */
	public function inv_upload(array $data) {
		$params = array();
		$params['productId'] = $data['sku_id'];
		$params['virtualStockNum'] = $data['inv_num'];

		$result = $this->json_decode($this->request_send('yhd.product.stock.update', $params));
		if ($result['response']['errorCount'] > 0) {
			$msg = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
		} else {
			return $result['response'];
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
		$params = array();
		$params['orderCode'] = $data['tid'];
		$params['deliverySupplierId'] = $data['logistics_id'];
		$params['expressNbr'] = $data['express_no'];

		$result = $this->json_decode($this->request_send('yhd.logistics.order.shipments.update', $params));

		if ($result['response']['errorCount'] > 0) {
			$msg = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
//			$send_log['status'] = -1;
//			$oms_log['is_back'] = 2;
			$oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
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
	 * @author hunter
	 * @date 2015-04-13
	 * @return array
	 */
	public function logistics_company_download() {
		$params = array();

		$result = $this->json_decode($this->request_send('yhd.logistics.deliverys.company.get', $params));

		//dump($result,1);
		if ($result['response']['errorCount'] > 0) {
			$msg = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
			//	throw new ExtException($msg, $result['response']['errInfoList']['errDetailInfo'][0]['errorCode']);
		} else {
			//转成与淘宝类似的返回格式
			$return = $result['response']['logisticsInfoList'];
			return $return;
		}
	}

	/**
	 * 转换平台商品为标准信息
	 * @author hunter
	 * @date 2015-04-10
	 * @param array $data 第三方平台商品信息
	 */
	public function _trans_goods(array $data) {
		$return = array();
		$return['goods_name'] = $data['productCname'];
		$return['goods_code'] = isset($data['mainOuterId']) ? $data['mainOuterId'] : '';
		$return['goods_from_id'] = $data['productId'];
		//TODO num暂无
		$return['num'] = load_model('source/ApiYihaodianGoodsModel')->get_sku_sum_num($data['productId']);
		//TODO seller_nick暂无
		$return['seller_nick'] = '';
		$return['source'] = 'yihaodian';
		$return['status'] = $data['canSale'] == '1' ? 1 : 0;
		//TODO stock_type暂无
		$return['stock_type'] = 2;
		//TODO onsale_time暂无
		$return['onsale_time'] = '';
		$return['has_sku'] = empty($data['skus']) ? 0 : 1;
		//TODO price暂无
		$return['price'] = '';
		//TODO goods_img暂无
		$return['goods_img'] = '';
		$return['goods_desc'] = $data['desc']['productDescInfoList']['productDescInfo'][0]['tabDetail'];

		return $return;
	}

	/**
	 * 转换SKU信息为标准信息
	 * @author hunter
	 * @date 2015-04-10
	 * @param array $data 第三方平台商品SKU信息
	 */
	public function _trans_sku(array $data) {

		$return = array();
		if (isset($data['skus'])) {
			foreach ($data['skus'] as $value) {
				$sku = array();
				$sku['goods_from_id'] = $data['productId'];
				$sku['source'] = 'yihaodian';
				$sku['sku_id'] = $value['productId'];
				$sku['goods_barcode'] = isset($value['outerId']) ? $value['outerId'] : '';

				$sku['status'] = (isset($value['canSale']) && $value['canSale'] == '1') ? 1 : 0;
				$sku['num'] = $value['allWareHouseStocList']['pmStockInfo'][0]['vs'];
				//市场价格http://open.yhd.com/opendoc/objproductPmPrice.html
				//$sku['price'] = $value['prices']['pmPriceList']['pmPrice'][0]['productListPrice'];
				$sku['price'] = isset($value['prices']['promNonMemberPrice']) ? $value['prices']['promNonMemberPrice'] : '0';

				$sku['stock_type'] = 1;
				$sku['with_hold_quantity'] = $value['allWareHouseStocList']['pmStockInfo'][0]['vsf'];

				if (isset($sku['attributes']['prodSerialAttributeInfo'])) {
					$sku['sku_properties'] = $value['attributes']['prodSerialAttributeInfo']['customProperties'];
					$color = $value['attributes']['prodSerialAttributeInfo']['serialProdAttributeInfoList']['serialProdAttributeInfo'][0]['serialAttributeInfoList']['serialAttributeInfo'][0]['itemLabel'];
					$size = $value['attributes']['prodSerialAttributeInfo']['serialProdAttributeInfoList']['serialProdAttributeInfo'][0]['serialAttributeInfoList']['serialAttributeInfo'][1]['itemLabel'];
					$sku['sku_properties_name'] = $color . ',' . $size;
				}

				$return[] = $sku;
			}
		} else {
		}
		return $return;
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
		$return['tid'] = $data['orderDetail']['orderCode'];
		$return['source'] = 'yihaodian';
		$return['shop_code'] = $shop_code;
		$return['status'] = $data['orderDetail']['orderStatus'] == 'ORDER_TRUNED_TO_DO' ? 1 : 0;
		$return['trade_from'] = $data['orderDetail']['isMobileOrder'] == '1' ? 'WAP' : '';

		if (in_array($data['orderDetail']['payServiceType'], array('2', '5', '9', '10', '12'))) {
			$return['pay_type'] = 1;
		} else {
			$return['pay_type'] = 0;
		}
		$return['pay_time'] = $data['orderDetail']['orderPaymentConfirmDate'];

		//昵称暂无
		$return['seller_nick'] = '';
		$return['buyer_nick'] = $data['orderDetail']['goodReceiverName'];
		$return['receiver_name'] = $data['orderDetail']['goodReceiverName'];

		$return['receiver_country'] = '中国';
		$return['receiver_province'] = $data['orderDetail']['goodReceiverProvince'];
		$return['receiver_city'] = $data['orderDetail']['goodReceiverCity'];
		$return['receiver_district'] = $data['orderDetail']['goodReceiverCounty'];

		$return['receiver_street'] = '';
		$return['receiver_address'] = $data['orderDetail']['goodReceiverProvince'] . ' ' . $data['orderDetail']['goodReceiverCity'] . ' ' . $data['orderDetail']['goodReceiverCounty'] . ' ' . $data['orderDetail']['goodReceiverAddress'];
		//receiver_addr是需要去掉省市区的地址
		$return['receiver_addr'] = $data['orderDetail']['goodReceiverAddress'];
		$return['receiver_zip_code'] = $data['orderDetail']['goodReceiverPostCode'];
		$return['receiver_mobile'] = $data['orderDetail']['goodReceiverMoblie'];
		$return['receiver_phone'] = isset($data['orderDetail']['goodReceiverPhone']) ? $data['orderDetail']['goodReceiverPhone'] : '';
		$return['receiver_email'] = '';
		$return['express_code'] = $data['orderDetail']['deliverySupplierId'];
		$return['express_no'] = $data['orderDetail']['merchantExpressNbr'];
		$return['hope_send_time'] = '';

		//订单总数量
		$num = 0;
		foreach ($data['orderItemList']['orderItem'] as $i) {
			$num = $num + $i['orderItemNum'];
		}
		$return['num'] = $num;
		$return['sku_num'] = count($data['orderItemList']['orderItem']); //平台sku种类数量
		$return['goods_weight'] = 0;
		$return['buyer_remark'] = isset($data['orderDetail']['deliveryRemark']) ? $data['orderDetail']['deliveryRemark'] : '';
		$return['seller_remark'] = isset($data['orderDetail']['merchantRemark']) ? $data['orderDetail']['merchantRemark'] : '';

		$return['seller_flag'] = '';
		$return['order_money'] = $data['orderDetail']['orderAmount'] + $data['orderDetail']['orderCouponDiscount'];
		$return['express_money'] = $data['orderDetail']['orderDeliveryFee'];
		$return['delivery_money'] = 0;
		$return['gift_coupon_money'] = $data['orderDetail']['orderPlatformDiscount'];
		$return['gift_money'] = 0;
		$return['integral_change_money'] = 0;
		$return['buyer_money'] = $data['orderDetail']['realAmount'];
		$return['alipay_no'] = '';
		$return['coupon_change_money'] = $data['orderDetail']['orderPlatformDiscount'];

		$return['balance_change_money'] = 0;
		$return['is_lgtype'] = '';
		$return['seller_rate'] = '';
		$return['buyer_rate'] = '';
		$return['invoice_type'] = '';
		$return['invoice_title'] = $data['orderDetail']['invoiceTitle'];
		$return['invoice_content'] = isset($data['orderDetail']['invoiceContent']) ? $data['orderDetail']['invoiceContent'] : '';

		$return['invoice_money'] = $data['orderDetail']['realAmount'];
		$return['invoice_pay_type'] = '';

		$return['order_last_update_time'] = $return['last_update_time'] = $data['orderDetail']['updateTime'];
		$return['order_first_insert_time'] = $data['orderDetail']['orderCreateTime'];
		$return['first_insert_time'] = $datetime;
		return $return;
	}

	/**
	 * 转换订单明细信息为标准订单明细
	 * @author hunter
	 * @date 2015-04-11
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_order_detail(array $data) {
		$return = array();

		$total = 0;
		$avg_total = 0;
		$base = $data['orderDetail']['orderAmount'] - $data['orderDetail']['orderAmount']['orderCouponDiscount'] - $data['orderDetail']['orderAmount']['orderDeliveryFee'];
		foreach ($data['orderItemList']['orderItem'] as $value) {
			$total += $value['orderItemAmount'];
		}

		if ($data['orderItemList']['orderItem']) {
			foreach ($data['orderItemList']['orderItem'] as $key => $value) {
				$detail = array();
				$detail['tid'] = $value['orderId'];
				$detail['oid'] = $value['id'];
				$detail['source'] = 'yihaodian';
				//$detail['return_status'] = '';
				$detail['title'] = $value['productCName'];
				$detail['price'] = $value['orderItemPrice'];
				$detail['num'] = $value['orderItemNum'];
				$detail['goods_code'] = '';
				$detail['sku_id'] = '';
				$detail['goods_barcode'] = $value['outerId'];
				$detail['total_fee'] = $value['orderItemPrice'] * $value['orderItemNum'];
				$detail['payment'] = $value['orderItemAmount'];
				$detail['discount_fee'] = $value['promotionAmount'] + $value['couponAmountMerchant'];
				$detail['adjust_fee'] = 0;

				if ($key < count($data['orderItemList']['orderItem']) - 1) {
					$detail['avg_money'] = $base * ($value['orderItemAmount']) / $total;
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
	 * @author hunter
	 * @date 2015-04-13
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_refund($shop_code, array $data) {
		$refund = array();
		$refund['refund_id'] = $data['refundDetail']['refundCode'];
		//暂无
		$refund['refund_type'] = '';
		$refund['tid'] = $data['refundDetail']['orderCode'];
		$refund['oid'] = '';
		$refund['source'] = 'yihaodian';
		$refund['shop_code'] = $shop_code;
		$refund['status'] = 1;
		$refund['is_change'] = 0;
		//商家联系人(回寄联系人)
		$refund['seller_nick'] = $data['refundDetail']['contactName'];
		//顾客姓名
		$refund['buyer_nick'] = $data['refundDetail']['receiverName'];
		$refund['has_good_return'] = 'TRUE';
		$refund['refund_fee'] = isset($data['refundDetail']['productAmount']) ? $data['refundDetail']['productAmount'] : 0;
		$refund['payment'] = 0;
		$refund['refund_reason'] = $data['refundDetail']['reasonMsg'];
		$refund['refund_desc'] = $data['refundDetail']['refundProblem'];
		$refund['refund_express_code'] = '';
		$refund['refund_express_no'] = '';
		$refund['attribute'] = '';
		$refund['change_remark'] = '';
		$refund['last_update_time'] = $data['refundDetail']['updateTime'];
		return $refund;
	}

	/**
	 * 转换退单明细信息为标准退单明细信息
	 * @author hunter
	 * @date 2015-04-13
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_refund_detail(array $data) {
		$return = array();
		if (isset($data['refundItemList']['refundItem']) && !empty($data['refundItemList']['refundItem'])) {
			foreach ($data['refundItemList']['refundItem'] as $v) {
				$detail = array();
				$detail['refund_id'] = $data['refundDetail']['refundCode'];
				$detail['tid'] = $data['refundDetail']['orderCode'];
				$detail['oid'] = '';
				$detail['goods_code'] = $v['productId'];
				$detail['title'] = $v['productCname'];
				$detail['price'] = $v['orderItemPrice'];
				$detail['num'] = $v['orderItemNum'];
				$detail['refund_price'] = '0'; //TODO
				$return[] = $detail;
			}
		}
		return $return;
	}

	/**
	 * 保存原始订单和订单明细数据(一号店)
	 * @param $shop_code
	 * @param $data
	 * @return mixed
	 */
	public function save_source_order_and_detail($shop_code, $data) {
		return $ret = load_model('source/ApiYihaodianTradeModel')->save_trade_and_order($shop_code, $data);
	}

	/**
	 * 保存原始退单和退单数据
	 * @param $shop_code
	 * @param $data
	 * @return mixed
	 */
	public function save_source_refund($shop_code, $data) {
		// TODO: Implement save_source_refund() method.
		return $ret = load_model('source/ApiYihaodianRefundModel')->save_yihaodian_refund($shop_code, $data);
	}

	/**
	 * 保存原始商品和sku数据
	 * @param $shop_code
	 * @param $data
	 * @return mixed
	 */
	public function save_source_goods_and_sku($shop_code, $data) {
		return $ret = load_model('source/ApiYihaodianGoodsModel')->save_goods_and_sku($shop_code, $data);
	}

	/**
	 * 保存物流公司原始数据
	 * @param string $shop_code 店铺代码
	 * @param array $data 物流公司原始数据
	 * @return array
	 */
	public function save_source_logistics_company($shop_code, $data) {
		return $ret = load_model('source/ApiLogisticsCompanyModel')->save_list($shop_code, $data, 'yihaodian');
	}

	/**
	 * 退单列表下载
	 * @author hunter
	 * @date 2015-04-10
	 * @return array 返回退单列表信息
	 */
	public function refund_list_download(array $data) {

		$param = array();

		$param['curPage'] = isset($data['page_no']) ? $data['page_no'] : 1;
		$param['pageRows'] = isset($data['page_size']) ? $data['page_size'] : 100;

		$param['dateType'] = 2;

		if (isset($data['start_modified']) && !empty($data['start_modified'])) {
			$param['startTime'] = date('Y-m-d H:i:s', strtotime('-10 minute',strtotime($data['start_modified'])));
		} else {
			$param['startTime'] = date('Y-m-d H:i:s', strtotime('-15 day'));
		}
		//查询结束时间(查询开始、结束时间跨度不能超过15天)
		if (isset($data['end_modified']) && !empty($data['end_modified'])) {
			$param['endTime'] = $data['end_modified'];
		} else {
			$param['endTime'] = date('Y-m-d H:i:s', strtotime('+15 day', strtotime($param['startTime'])));
		}

		$result = $this->json_decode($this->request_send('yhd.refund.get', $param));
		if ($result['response']['errorCount'] > 0) {
			$msg = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
			//	throw new ExtException($msg, $result['response']['errInfoList']['errDetailInfo'][0]['errorCode']);
		} else {
			$return['refunds']['refund'] = $result['response']['refundList']['refund'];
			$return['total_results'] = $result['response']['totalCount'];
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
		$params = array();
		$params['refundCode'] = $refund_id;

		$result = $this->json_decode($this->request_send('yhd.refund.detail.get', $params));

		//dump($result,1);
		if ($result['response']['errorCount'] > 0) {
			$msg = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
			throw new ExtException($msg);
			//	throw new ExtException($msg, $result['response']['errInfoList']['errDetailInfo'][0]['errorCode']);
		} else {
			//转成与淘宝类似的返回格式
			$return = $result['response']['refundInfoMsg'];
			return $return;
		}
	}

	/**
	 * 商品下载(额外)
	 * @param $data
	 * @return array
	 */
	public function goods_download_ext($data) {

		$page = 1;
		$page_size = $this->goods_page_size;
		$results = $result = array();
		do {
			$params = array(
				'page_no' => $page,
				'page_size' => $page_size,
				'start_modified' => $data['start_modified'],
			//	'seller_nick' => $data['nick'],
			);

			$result = $this->goods_list_download_by_general($params);

			$pages = ceil($result['total_results'] / $page_size);
			$results = array_merge($results, $result['items']['item']);
			$page++;
		} while ($page <= $pages);

		foreach ($results as &$item) {
			$_result = $this->goods_info_download_by_general($item['productId']);
			$item['attributes'] = $_result['attributes'];
			$item['desc'] = $_result['desc'];
			$item['price'] = $_result['price'];
			$item['stock'] = $_result['stock'];
		}

		return $results;
	}

	/**
	 * 保存原始商品和sku数据(额外)
	 * @param $shop_code
	 * @param $data
	 * @return array
	 */
	public function save_source_goods_and_sku_ext($shop_code, $data) {
		return $ret = load_model('source/ApiYihaodianGoodsModel')->save_goods_and_sku_ext($shop_code, $data);
	}

	/**
	 * 转换平台商品为标准信息
	 * @param $data
	 * @return array
	 */
	public function _trans_goods_ext($data) {
		$return = array();
		$return['goods_name'] = $data['productCname'];
		$return['goods_code'] = $data['outerId'];
		$return['goods_from_id'] = $data['productId'];
		$return['num'] = $data['stock']['pmStockList']['pmStock'][0]['vs'];
		//TODO seller_nick暂无
		$return['seller_nick'] = '';
		$return['source'] = 'yihaodian';
		$return['status'] = $data['canSale'] == '1' ? 1 : 0;
		//TODO stock_type暂无
		$return['stock_type'] = 2;
		//TODO onsale_time暂无
		$return['onsale_time'] = '';
		$return['has_sku'] = 0;
		$return['price'] = $data['price']['pmPriceList']['pmPrice'][0]['productListPrice'];
		$return['goods_img'] = $data['prodImg'];
		$return['goods_desc'] = $data['desc']['productDescInfoList']['productDescInfo'][0]['tabDetail'];
		return $return;
	}

	/**
	 * @param $data
	 * @return array
	 */
	public function _trans_sku_ext($data) {
		$sku = array();
		$sku['goods_from_id'] = $data['productId'];
		$sku['source'] = 'yihaodian';
		$sku['sku_id'] = $data['productId'];
		$sku['goods_barcode'] = $data['outerId'];
		$sku['status'] = 1;
		$sku['num'] = $data['stock']['pmStockList']['pmStock'][0]['vs'];
		$sku['price'] = $data['price']['pmPriceList']['pmPrice'][0]['productListPrice'];
		$sku['stock_type'] = 2;
		$sku['with_hold_quantity'] = 0;
		$sku['sku_properties_name'] = $sku['sku_properties'] = '';
		$return[] = $sku;
		return $return;
	}
}