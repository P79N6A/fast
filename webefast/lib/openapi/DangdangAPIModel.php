<?php
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');


/**
 * 一号店API类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class DangdangAPIModel extends AbsAPIModel {

	public $gate = 'http://api.open.dangdang.com/openapi/rest';

	private $app_key;
	private $secret;
	private $session;

	public function __construct($token) {
		$this->app_key = $token['app_key'];
		$this->secret = $token['secret'];
		$this->session = $token['session'];

		$this->order_page_size = 20;
		$this->goods_page_size = 20;
		$this->refund_page_size = 10;

		$this->order_pk = 'orderID';
		$this->goods_pk = 'itemID';
		$this->refund_pk = 'returnExchangeCode'; //没有独立的refund_id
		$this->refund_ext = true;
	}

	/**
	 * api发送请求
	 * @param $api
	 * @param array $param
	 */
	public function request_send($api, $param = array()) {

		$data = array();
		$data['app_key'] = $this->app_key;
		$data['session'] = $this->session;
		$data['format'] = 'xml';
		$data['v'] = '1.0';
		$data['method'] = $api;
		$data['timestamp'] = date('Y-m-d H:i:s');
		$data['sign_method'] = 'md5';
		$sign = $this->sign($data);
		$data['sign'] = $sign;
		$data = array_merge($data, $param);
		$url = $this->gate;
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
		return strtoupper(md5($sign));
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
		$params = array();

		$params['p'] = isset($data['page_no']) ? $data['page_no'] : 1;
		$params['pageSize'] = isset($data['page_size']) ? $data['page_size'] : 20;
		$params['datatype'] = '8990';//审核状态
		$params['its'] = '9999';

		$result = $this->xml2array($this->request_send('dangdang.items.list.get', $params));
		if (isset($result['errorResponse'])) {
			$msg = $result['errorResponse']['errorMessage'];
			throw new ExtException($msg, $result['errorResponse']['errorCode']);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'total_results' => $result['response']['totalInfo']['itemsCount'],
				'items' => array('item' => $result['response']['ItemsList']['ItemInfo']
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
		$params = array();
		$params['it'] = $data;

		$result = $this->xml2array($this->request_send('dangdang.item.get', $params));
		if (isset($result['errorResponse'])) {
			$msg = $result['errorResponse']['errorMessage'];
			throw new ExtException($msg, $result['errorResponse']['errorCode']);
		} else {
			$return = $result['response']['ItemDetail'];
			return $return;
		}
	}

	/**
	 * 批量商品信息下载
	 * @author hunter
	 * @date 2015-03-14
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function goods_info_download_multi($ids, $data = array()) {
		$return = array();
		foreach($ids as $itemID) {
			$return[] = $this->goods_info_download($itemID);
		}
		return $return;
	}

	/**
	 * 订单列表下载
	 * @author hunter
	 * @date 2015-04-14
	 * @return array 返回共享表信息和平台原始数据
	 * @link http://open.dangdang.com/index.php?c=documentCenterG4&f=show&page_id=132
	 */
	public function order_list_download($data) {
		$params = array();

		if (isset($data['start_modified']) && !empty($data['start_modified'])) {
			$params['lastModifyTime_start'] = $data['start_modified'];
		} else {
			$params['lastModifyTime_start'] = date('Y-m-d H:i:s', strtotime('-3 day'));
		}
		//查询结束时间(查询开始、结束时间跨度不能超过15天)
		if (isset($data['end_modified']) && !empty($data['end_modified'])) {
			$params['lastModifyTime_end'] = $data['end_modified'];
		}else {
			$params['lastModifyTime_end'] = date('Y-m-d H:i:s');
		}

		$params['p'] = isset($data['page_no']) ? $data['page_no'] : 1;
		$params['pageSize'] = isset($data['page_size']) ? $data['page_size'] : 20;

		$result = $this->xml2array($this->request_send('dangdang.orders.list.get', $params));

		if (isset($result['errorResponse'])) {
			$msg = $result['errorResponse']['errorMessage'];
			throw new ExtException($msg, $result['errorResponse']['errorCode']);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'trades' => array('trade' => $result['response']['OrdersList']['OrderInfo']),
				'total_results' => $result['response']['totalInfo']['orderCount'],
			);
			return $return;
		}
	}

	/**
	 * 单个订单下载
	 * @author hunter
	 * @date 2015-04-14
	 * @param $id
	 * @param $data
	 * @return array 返回共享表信息和平台原始数据
	 * @link http://open.dangdang.com/index.php?c=documentCenterG4&f=show&page_id=133
	 */
	public function order_info_download($id, $data = array()) {
		$params = array();
		$params['o'] = $id;

		$result = $this->xml2array($this->request_send('dangdang.order.details.get', $params));
		if (isset($result['errorResponse'])) {
			$msg = $result['errorResponse']['errorMessage'];
			throw new ExtException($msg, $result['errorResponse']['errorCode']);
		} else {
			//转成与淘宝类似的返回格式
			$return = $result['response'];
			return $return;
		}
	}

	/**
	 * 退单列表下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-04-10
	 * @return array 返回退单列表信息
	 * @link http://open.dangdang.com/index.php?c=documentCenterG4&f=show&page_id=138
	 */
	public function refund_list_download(array $data) {
		$params = array();
		$params['p'] = isset($data['page_no']) ? $data['page_no'] : 1;
		$params['pageSize'] = isset($data['page_size']) ? $data['page_size'] : 20;

		$params['isNeedReverseReason'] = 1;

		if (isset($data['start_modified']) && !empty($data['start_modified'])) {
			$params['resd'] = $data['start_modified'];
		} else {
			$params['resd'] = date('Y-m-d H:i:s', strtotime('-3 day'));
		}
		if (isset($data['end_modified']) && !empty($data['end_modified'])) {
			$params['reed'] = $data['end_modified'];
		}else {
			$params['reed'] = date('Y-m-d H:i:s');
		}

		$result = $this->xml2array($this->request_send('dangdang.1orders.exchange.return.list.get', $params));
		if (isset($result['errorResponse'])) {
			$msg = $result['errorResponse']['errorMessage'];
			throw new ExtException($msg, $result['errorResponse']['errorCode']);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'refunds' => array(
					'refund' => isset($result['response']['OrdersList']['OrderInfo'][0]) ? $result['response']['OrdersList']['OrderInfo'] : array($result['response']['OrdersList']['OrderInfo'])),
				'total_results' => $result['response']['totalInfo']['totalOrderCount'],
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
	 * @author hunter
	 * @date 2015-03-09
	 * @return array 返回结果和第三方平台原始信息
	 */
	public function inv_upload(array $data) {
		$params = array();
		$params['oit'] = $data['goods_barcode'];
		$params['stk'] = $data['inv_num'];

		$result = $this->xml2array($this->request_send('dangdang.item.stock.update', $params));
		if (isset($result['errorResponse'])) {
			$msg = $result['errorResponse']['errorMessage'];
			throw new ExtException($msg, $result['errorResponse']['errorCode']);
		} else {
			//转成与淘宝类似的返回格式
			$return = $result['response'];
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
	 * @author hunter
	 * @date 2015-04-15
	 * @param array $data 第三方平台商品信息
	 */
	public function _trans_goods(array $data) {
		$return = array();
		$return['goods_name'] = $data['itemName'];
		$return['goods_code'] = $data['outerItemID'];
		$return['goods_from_id'] = $data['itemID'];
		$return['num'] = $data['stockCount'];
		//TODO seller_nick暂无
		$return['seller_nick'] = '';
		$return['source'] = 'dangdang';
		$return['status'] = $data['itemState'] == '上架' ? 1 : 0;
		//TODO stock_type暂无
		$return['stock_type'] = 2;
		//TODO onsale_time暂无
		$return['onsale_time'] = '';
		$return['has_sku'] = empty($data['SpecilaItemInfo']) ? 0 : 1;
		$return['price'] = $data['unitPrice'];
		$return['goods_img'] = $data['pic1'];
		$return['goods_desc'] = $data['itemDetail'];
		return $return;
	}

	/**
	 * 转换SKU信息为标准信息
	 * @author hunter
	 * @date 2015-03-12
	 * @param array $data 第三方平台商品SKU信息
	 */
	public function _trans_sku(array $data) {
		$return = array();
		if (isset($data['SpecilaItemInfo'])) {
			if (isset($data['SpecilaItemInfo'][0])) {
				foreach ($data['SpecilaItemInfo'] as $value) {
					$sku = array();
					$sku['goods_from_id'] = $data['itemID'];
					$sku['source'] = 'dangdang';
					$sku['sku_id'] = $value['subItemID'];
					$sku['goods_barcode'] = isset($value['outerItemID']) ? $value['outerItemID'] : '';

					$sku['status'] = (isset($data['itemState']) && $data['itemState'] == '上架') ? 1 : 0;
					$sku['num'] = $value['stockCount'];
					$sku['price'] = $value['unitPrice'];

					$sku['stock_type'] = 2;
					$sku['with_hold_quantity'] = 0;

					$sku['sku_properties'] = $value['specialAttribute'];

					$color = $size = '';
					$color_and_size_array = explode(';',$value['specialAttribute']);
					if ($color_and_size_array) {
						$color_array = explode('>>',$color_and_size_array[0]);
						if ($color_array) {
							$color = $color_array[1];
						}
						if (isset($color_and_size_array[1])) {
							$size_array = explode('>>',$color_and_size_array[1]);
							if ($size_array) {
								$size = $size_array[1];
							}
						}
					}

					$sku['sku_properties_name'] = $color.','.$size;
					$return[] = $sku;
				}
			}else {
				$value = $data['SpecilaItemInfo'];
				$sku = array();
				$sku['goods_from_id'] = $data['itemID'];
				$sku['source'] = 'dangdang';
				$sku['sku_id'] = $value['subItemID'];
				$sku['goods_barcode'] = isset($value['outerItemID']) ? $value['outerItemID'] : '';

				$sku['status'] = (isset($data['itemState']) && $data['itemState'] == '上架') ? 1 : 0;
				$sku['num'] = $value['stockCount'];
				$sku['price'] = $value['unitPrice'];

				$sku['stock_type'] = 2;
				$sku['with_hold_quantity'] = 0;

				$sku['sku_properties'] = $value['specialAttribute'];

				$color = $size = '';
				$color_and_size_array = explode(';',$value['specialAttribute']);
				if ($color_and_size_array) {
					$color_array = explode('>>',$color_and_size_array[0]);
					if ($color_array) {
						$color = $color_array[1];
					}
					if (isset($color_and_size_array[1])) {
						$size_array = explode('>>',$color_and_size_array[1]);
						if ($size_array) {
							$size = $size_array[1];
						}
					}
				}

				$sku['sku_properties_name'] = $color.','.$size;
				$return[] = $sku;
			}
		}
		return $return;
	}

	/**
	 * 转换订单信息为标准订单
	 * @author hunter
	 * @date 2015-04-15
	 * @param array $data 第三方平台订单信息
	 */
	public function _trans_order($shop_code, array $data) {
		$datetime = date('Y-m-d H:i:s');

		$buyInfo = $data['buyerInfo'];
		$sendGoodsInfo = $data['sendGoodsInfo'];
		$return = array();
		$return['tid'] = $data['orderID'];
		$return['source'] = 'dangdang';
		$return['shop_code'] = $shop_code;
		$return['status'] = $data['orderState'] == '101' ? 1 : 0;

		$return['trade_from'] = '';
		$return['pay_type'] = $buyInfo['buyerPayMode'] == '货到付款' ? 1 : 0;

		$return['pay_time'] = $data['paymentDate'];

		$return['seller_nick'] = '';
		$return['buyer_nick'] = '';

		//收货信息
		$return['receiver_name'] = $sendGoodsInfo['consigneeName'];
		$return['receiver_country'] = $sendGoodsInfo['consigneeAddr_State'];
		$return['receiver_province'] = $sendGoodsInfo['consigneeAddr_Province'];
		$return['receiver_city'] = $sendGoodsInfo['consigneeAddr_City'];
		$return['receiver_district'] = $sendGoodsInfo['consigneeAddr_Area'];
		$return['receiver_street'] = '';
		//receiver_address是需要去掉国家的地址
		$return['receiver_address'] = str_replace(array($sendGoodsInfo['consigneeAddr_State']),'',$sendGoodsInfo['consigneeAddr']);
		$return['receiver_address'] = trim(str_replace('，',' ',$return['receiver_address']));
		//receiver_addr是需要去掉国家省市区的地址
		$return['receiver_addr'] = str_replace(array($sendGoodsInfo['consigneeAddr_State'],$sendGoodsInfo['consigneeAddr_Province'],$sendGoodsInfo['consigneeAddr_City'],$sendGoodsInfo['consigneeAddr_Area']),'',$sendGoodsInfo['consigneeAddr']);
		$return['receiver_addr'] = str_replace('，','',$return['receiver_addr']);

		$return['receiver_zip_code'] = $sendGoodsInfo['consigneePostcode'];

		$return['receiver_mobile'] = $sendGoodsInfo['consigneeMobileTel'];
		$return['receiver_phone'] = $sendGoodsInfo['consigneeTel'];
		$return['receiver_email'] = '';
		$return['express_code'] = $sendGoodsInfo['sendCompany'];
		$return['express_no'] = $sendGoodsInfo['sendOrderID'];
		$return['hope_send_time'] = '';

		//订单总数量
		$num = $sku_num = 0;
		if (isset($data['ItemsList']['ItemInfo'][0])) {
			foreach ($data['ItemsList']['ItemInfo'] as $value) {
				$num += $value['orderCount'];
			}
			$sku_num = count($data['ItemsList']['ItemInfo']);
		} else {
			$num = $data['ItemsList']['ItemInfo']['orderCount'];
			$sku_num = 1;
		}
		$return['num'] = $num;//订单总数量
		$return['sku_num'] = $sku_num; //平台sku种类数量
		$return['goods_weight'] = 0;

		$return['buyer_remark'] = $data['message'];
		$return['seller_remark'] = $data['remark'];
		$return['seller_flag'] = '';



		$return['express_money'] = $buyInfo['postage'];
		$return['delivery_money'] = 0;
		$return['gift_coupon_money'] = $buyInfo['giftCertMoney'];
		$return['gift_money'] = $buyInfo['giftCardMoney'];
		$return['integral_change_money'] = 0;

		if ('货到付款' == $buyInfo['buyerPayMode']) {
			$order_money = $buyInfo['giftCertMoney'] + $buyInfo['giftCardMoney'] + $buyInfo['accountBalance'];
		} else {
			$order_money = $buyInfo['giftCertMoney'] + $buyInfo['giftCardMoney'] + $buyInfo['accountBalance'] + $buyInfo['goodsMoney'];
		}
		$return['order_money'] = $order_money;
		$return['buyer_money'] = '货到付款' == $buyInfo['buyerPayMode'] ? 0: $buyInfo['goodsMoney'];
		$return['alipay_no'] = '';
		$return['coupon_change_money'] = $buyInfo['activityDeductAmount'];
		$return['balance_change_money'] = $buyInfo['accountBalance'];

		$return['is_lgtype'] = '';
		$return['seller_rate'] = '';
		$return['buyer_rate'] = '';

		//发票
		$receiptInfo = $data['receiptInfo'];
		$return['invoice_type'] = '';
		$return['invoice_title'] = $receiptInfo['receiptName'];
		$return['invoice_content'] = $receiptInfo['receiptDetails'];
		$return['invoice_money'] = $receiptInfo['receiptMoney'];
		$return['invoice_pay_type'] = '';

		$OrderOperateList = $data['OrderOperateList'];
		$order_first_insert_time = date('Y-m-d H:i:s');
		foreach($OrderOperateList['OperateInfo'] as $operate) {
			if ('顾客下单' == $operate['operateDetails']) {
				$order_first_insert_time = $operate['operateTime'];
			}
		}

		$return['order_last_update_time'] = $return['last_update_time'] = $data['lastModifyTime'];
		$return['order_first_insert_time'] = $order_first_insert_time;
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
		$buyInfo = $data['buyerInfo'];
		$base = $buyInfo['giftCertMoney'] + $buyInfo['giftCardMoney'] + $buyInfo['accountBalance'] + $buyInfo['goodsMoney'];

		if (isset($data['ItemsList']['ItemInfo'][0])) {
			foreach ($data['ItemsList']['ItemInfo'] as $value) {
				$total += $value['unitPrice'] * $value['orderCount'];
			}
		} else {
			$total = $data['ItemsList']['ItemInfo']['unitPrice'] * $data['ItemsList']['ItemInfo']['orderCount'];
		}

		if (isset($data['ItemsList']['ItemInfo'][0])) {
			foreach ($data['ItemsList']['ItemInfo'] as $key => $value) {
				$detail = array();
				$detail['tid'] = $data['orderID'];
				$detail['oid'] = $data['orderID'].'_'.$value['itemID'];
				$detail['source'] = 'dangdang';
				//$detail['return_status'] = '';
				$detail['title'] = $value['itemName'];
				$detail['price'] = $value['unitPrice'];
				$detail['num'] = $value['orderCount'];
				$detail['goods_code'] = '';
				$detail['sku_id'] = '';
				$detail['goods_barcode'] = $value['outerItemID'];
				$detail['total_fee'] = $value['unitPrice'] * $value['orderCount'];
				$detail['payment'] = 0;
				$detail['discount_fee'] = 0;
				$detail['adjust_fee'] = 0;

				$detail['avg_money'] = $base - $avg_total;

				$avg_total += $detail['avg_money'];
				$detail['end_time'] = '';

				$return[] = $detail;
			}
		} else {
			$value = $data['ItemsList']['ItemInfo'];
			$detail = array();
			$detail['tid'] = $data['orderID'];
			$detail['oid'] = $data['orderID'].'_'.$value['itemID'];
			$detail['source'] = 'dangdang';
			//$detail['return_status'] = '';
			$detail['title'] = $value['itemName'];
			$detail['price'] = $value['unitPrice'];
			$detail['num'] = $value['orderCount'];
			$detail['goods_code'] = '';
			$detail['sku_id'] = '';
			$detail['goods_barcode'] = $value['outerItemID'];
			$detail['total_fee'] = $value['unitPrice'] * $value['orderCount'];
			$detail['payment'] = 0;
			$detail['discount_fee'] = 0;
			$detail['adjust_fee'] = 0;

			$detail['avg_money'] = $base * ($value['unitPrice'] * $value['orderCount']) / $total;

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
	 * @author hunter
	 * @date 2015-03-23
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_refund($shop_code, array $data) {
		$refund = array();
		$refund['refund_id'] = $data['returnExchangeCode'];
		$refund['refund_type'] = $data['returnExchangeStatus'] == 1 ? 0 : 3;
		$refund['tid'] = $data['orderID'];
		$refund['oid'] = '';
		$refund['source'] = 'dangdang';
		$refund['shop_code'] = $shop_code;
		$refund['status'] = 1;
		$refund['is_change'] = 0;
		//商家联系人(回寄联系人)
		$refund['seller_nick'] = '';
		//顾客姓名
		$refund['buyer_nick'] = '';
		$refund['has_good_return'] = 'TRUE';
		$refund['refund_fee'] = $data['orderMoney'];
		$refund['payment'] = 0;

		$refund_reason = $refund_desc = '';
		if (isset($data['itemsList']['ItemInfo'][0])) {
			foreach ($data['itemsList']['ItemInfo'] as $value) {
				$refund_reason = $value['oneLevelReverseReason'];
				$refund_desc .= '|'.$value['reverseDetailReason'];
			}
		} else {
			$value = $data['itemsList']['ItemInfo'];
			$refund_reason = $value['oneLevelReverseReason'];
			$refund_desc .= $value['reverseDetailReason'];
		}

		$refund['refund_reason'] = $refund_reason;
		$refund['refund_desc'] = $refund_desc;

		$refund['refund_express_code'] = '';
		$refund['refund_express_no'] = '';
		$refund['attribute'] = '';
		$refund['change_remark'] = '';
		return $refund;
	}

	/**
	 * 转换退单明细信息为标准退单明细信息
	 * @author hunter
	 * @date 2015-04-16
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_refund_detail(array $data) {
		$return = array();
		if (isset($data['itemsList']['ItemInfo'])) {

			if(isset($data['itemsList']['ItemInfo'][0])) {
				foreach ($data['itemsList']['ItemInfo'] as $v) {
					$detail = array();
					$detail['refund_id'] = $data['returnExchangeCode'];
					$detail['tid'] = $data['orderID'];
					$detail['oid'] = '';
					$detail['goods_code'] = $v['itemID'];
					$detail['title'] = $v['itemName'];
					$detail['price'] = $v['unitPrice'];
					$detail['num'] = $v['orderCount'];
					$detail['refund_price'] = '0';
					$return[] = $detail;
				}
			} else {
				$v = $data['itemsList']['ItemInfo'];
				$detail = array();
				$detail['refund_id'] = $data['returnExchangeCode'];
				$detail['tid'] = $data['orderID'];
				$detail['oid'] = '';
				$detail['goods_code'] = $v['itemID'];
				$detail['title'] = $v['itemName'];
				$detail['price'] = $v['unitPrice'];
				$detail['num'] = $v['orderCount'];
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
		return $ret = load_model('source/ApiDangdangTradeModel')->save_trade_and_order($shop_code, $data);
	}

	/**
	 * 保存原始退单和退单数据
	 * @param $shop_code
	 * @param $data
	 * @return mixed
	 */
	public function save_source_refund($shop_code, $data) {
		return $ret = load_model('source/ApiDangdangRefundModel')->save_dangdang_refund($shop_code, $data);
	}

	/**
	 * 保存原始商品和sku数据
	 * @param $shop_code
	 * @param $data
	 * @return mixed
	 */
	public function save_source_goods_and_sku($shop_code, $data) {
		return $ret = load_model('source/ApiDangdangGoodsModel')->save_goods_and_sku($shop_code, $data);
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

	/**
	 * 下载退款列表
	 * @author hunter
	 * @date 2015-04-10
	 * @param array $data
	 * @return array $results 返回数组中包含主单据列表和明细
	 */
	public function refund_download_ext($data){
		$page_index = 1;
		$page_size = $this->refund_page_size;
		$results = $result = array();
		do{
			$params = array(
				'pageIndex' => $page_index,
				'pageSize' => $page_size,
		//		'refundApp_Status' => 2 //审核通过
			);
			if (isset($data['start_modified']) && !empty($data['start_modified'])) {
				$params['lastModifyTime_start'] = $data['start_modified'];
			} else {
				$params['lastModifyTime_start'] = date('Y-m-d H:i:s', strtotime('-3 day'));
			}
			if (isset($data['end_modified']) && !empty($data['end_modified'])) {
				$params['lastModifyTime_end'] = $data['end_modified'];
			}else {
				$params['lastModifyTime_end'] = date('Y-m-d H:i:s');
			}

			$result = $this->refund_list_download_ext($params);

			$pages = ceil($result['count'] / $page_size);
			$results = array_merge($results, $result['refunds']['refund']);
			$page_index++;
		}while ($page_index <= $pages);

		return $results;
	}

	/**
	 * 下载退款列表
	 * @author hunter
	 * @date 2015-04-16
	 * @param array $param 查询条件
	 * @throws ExtException
	 * @link http://open.dangdang.com/index.php?c=documentCenterG4&f=show&page_id=169
	 */
	private function refund_list_download_ext($param){
		$result = $this->xml2array($this->request_send('dangdang.orders.refund.list', $param));

		if (isset($result['errorResponse'])) {
			$msg = $result['errorResponse']['errorMessage'];
			throw new ExtException($msg, $result['errorResponse']['errorCode']);
		} else {
			$return = array(
				//转成与淘宝类似的返回格式
				'refunds' => array(
					'refund' => isset($result['response']['refundInfos']['refundInfoList']['refundInfo'][0]) ? $result['response']['refundInfos']['refundInfoList']['refundInfo'] : array($result['response']['refundInfos']['refundInfoList']['refundInfo'])),
				'count' => $result['response']['refundInfos']['totalSize'],
			);
			return $return;
		}
	}

	/**
	 * 保存退款的原始数据
	 * @author hunter
	 * @date 2015-04-16
	 * @param string $shop_code
	 * @param array $data
	 */
	public function save_source_refund_ext($shop_code, $data){
		return load_model('source/ApiDangdangRefundModel')->save_dangdang_refund_ext($shop_code, $data);
	}

	/**
	 * 转化退款到标准数据
	 * @author hunter
	 * @date 2015-04-10
	 * @param string $shop_code 店铺代码
	 * @param array $data 待转换数据
	 */
	public function _trans_refund_ext($shop_code, $data){
		$refund = array();
		$refund['refund_id'] = $data['orderId'];
		$refund['refund_type'] = 1;
		$refund['tid'] = $data['orderId'];
		$refund['oid'] = '';
		$refund['source'] = 'dangdang';
		$refund['shop_code'] = $shop_code;
		$refund['status'] = 1;
		$refund['is_change'] = 0;
		//商家联系人(回寄联系人)
		$refund['seller_nick'] = '';
		//顾客姓名
		$refund['buyer_nick'] = '';
		$refund['has_good_return'] = 'FALSE';
		$refund['refund_fee'] = $data['refundAmount'];
		$refund['payment'] = $data['totalAmount'] - $data['refundAmount'];

		$refund['refund_reason'] = '';
		$refund['refund_desc'] = '';

		$refund['refund_express_code'] = '';
		$refund['refund_express_no'] = '';
		$refund['attribute'] = '';
		$refund['change_remark'] = '';
		return $refund;
	}

	/**
	 * 转化退款明细到标准数据
	 * @author hunter
	 * @date 2015-04-10
	 * @param array $data 待转换数据
	 */
	public function _trans_refund_detail_ext($data){
		//售前退款没有明细，整单退， 返回空
		return array();
	}
}