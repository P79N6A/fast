<?php
require_lib ( 'openapi/TaobaoAPIModel' );
require_lib ( 'net/HttpEx,util/xml_util' );
class TaobaoQimenAPIModel extends TaobaoAPIModel {
	const V1 = '1.0';
	const V2 = '2.0';
	private $version = '2.0';
	function __construct($token) {
		parent::__construct ( $token );
		$this->gate = 'http://qimenapi.tbsandbox.com/router/qimen/service?';
		$this->customerId = $token ['customerId'];
	}
	/**
	 * <?xml version="1.0" encoding="UTF-8"?>
	 * <request>
	 * <entryOrderCode>入库单编码, String(50)，必填</entryOrderCode>
	 * <ownerCode>货主编码, String(50)</ownerCode>
	 * <warehouseCode>仓库编码, String(50)</warehouseCode>
	 * </request>
	 */
	function entryorder_query($data) {
		$m = 'taobao.qimen.entryorder.query';
		return $this->_do_request ( $this->_get_method($m), $data );
	}
	/**
	 * 4.1.	商品同步接口
	 * @param unknown $data
	 * @return multitype:
	 */
	function item_sync($data) {
		$m = array (
				self::V2 => 'taobao.qimen.singleitem.synchronize',
				self::V1 => 'taobao.qimen.item.synchronize' 
		);
		return $this->_do_request ( $this->_get_method($m), $data );
	}
	
	/**
	 * 4.2.	入库单创建接口 
	 * @param unknown $data
	 * @return multitype:
	 */
	function entryorder_create($data) {
		$m = 'taobao.qimen.entryorder.create';
		return $this->_do_request ($this->_get_method($m), $data );
	}
	
	/**
	 * 4.4.	退货入库单创建接口
	 * @param unknown $data
	 * @return multitype:
	 */
	function returnorder_create($data) {
		$m = 'taobao.qimen.returnorder.create';
		return $this->_do_request ($this->_get_method($m), $data );
	}
	
	/**
	 * 4.6.	发货单创建接口
	 * @param unknown $data
	 * @return multitype:
	 */
	function deliveryorder_create($data) {
		$m = 'taobao.qimen.deliveryorder.create';
		return $this->_do_request ($this->_get_method($m), $data );
	}
	
	/**
	 * 4.10.	单据取消接口  
	 * @param unknown $data
	 * @return multitype:
	 */
	function order_cancel($data) {
		$m = 'taobao.qimen.order.cancel';
		return $this->_do_request ($this->_get_method($m), $data );
	}
	
	private function _get_method($m) {
		if (is_string ( $m )) {
			return $m;
		}
		return $m [$this->version];
	}
	private function _do_request($api, $params) {
		$params = array2xml ( $params, 'request' );
		// 增加系统级参数
		$data ['method'] = $api;
		$data ['timestamp'] = date ( 'Y-m-d H:i:s' );
		$data ['format'] = 'xml';
		$data ['app_key'] = $this->app_key;
		$data ['v'] = $this->version;
		$data ['sign_method'] = 'md5';
		$data ['session'] = $this->session;
		$data ['customerId'] = $this->customerId;
		$data['test_type'] = 'normal';
		
		$sign = $this->sign ( $data );
		$data ['sign'] = $sign;
		
		$url = $this->gate . http_build_query ( $data );
		$errno = '';
		$errnmsg = '';
		$ret = do_http ( $url, $params, $errno, $errmsg );
		$arr = array ();
		
		xml2array ( $ret, $arr );
		
		return $arr;
	}
	private function _build_query($opts) {
		$str = '';
		foreach ( $opts as $k => $v ) {
			$str .= '&' . $k . '=' . $v;
		}
		
		return substr ( $str, 1 );
	}
}