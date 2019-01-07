<?php
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');


/**
 * iwmsAPI类
 * @author hunter
 * Date: 4/2/15
 * Time: 11:34 AM
 */
class IwmsAPIModel extends AbsAPIModel {

	public $api_url;
	private $accesskey;
	private $app_secret;

	public function __construct($token) {
		$this->app_secret = $token ['app_secret'];
		$this->api_url = $token ['api_url'];
		$this->accesskey = $token ['accesskey'];
	}

	/**
	 * 第三方平台请求发送
	 */
	public function request_send($api, $param = array()) {

		$data = array();
		$data['accesskey'] = $this->accesskey;
		$data['service'] = $api;
		$data['timestamp'] = date('Y-m-d H:i:s');
		$data['v'] = '1.0';
		$data['service_ver'] = '1.0';
		$data = array_merge($data, array('bizdata'=>json_encode($param)));
		$sign = $this->sign($data);
		$data['sign'] = $sign;
		$result = $this->exec($this->api_url, $data);
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
		$sign = $this->app_secret;

		ksort($param);

		foreach ($param as $key => $val) {
			$sign .= $key . $val;
		}

		$sign .= $this->app_secret;

		$sign = md5($sign);

		return $sign;
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
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function order_list_download($data) {
		// TODO: Implement order_list_download() method.
	}

	/**
	 * 单个订单下载
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-09
	 * @return array 返回共享表信息和平台原始数据
	 */
	public function order_info_download($id, $data = array()) {
		// TODO: Implement order_info_download() method.
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
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-12
	 * @param array $data 第三方平台订单信息
	 */
	public function _trans_order($shop_code, array $data) {
		// TODO: Implement _trans_order() method.
	}

	/**
	 * 转换订单明细信息为标准订单明细
	 * @author jhua.zuo<jhua.zuo@baisonmail.com>
	 * @date 2015-03-12
	 * @param array $data 第三方平台订单明细信息
	 */
	public function _trans_order_detail(array $data) {
		// TODO: Implement _trans_order_detail() method.
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
		// TODO: Implement save_source_order_and_detail() method.
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