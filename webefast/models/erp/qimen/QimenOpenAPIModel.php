<?php

require_model('erp/qimen/QimenAPIModel');

class QimenOpenAPIModel extends QimenAPIModel {

    protected $db;

    function __construct($token = array()) {
        parent::__construct($token);
        $this->db = CTX()->db;
    }

    function exec_api($request) {
        $ret = $this->check_sign($request);
		$ret = true;
        if ($ret !== true) {
            return $ret;
        }
	
        $method = $request['method'];
		$method = ltrim(strstr($method, '.'), '.');
        $action_method = str_replace('.', '_', $method);
		
        if (method_exists($this, $action_method)) {
            return $this->$action_method($request);
        } else {
            return $this->return_info(-1, '找不到指定方法');
        }
    }

    /**
     * 获取商品库存
     * @param array $data 回传数据
     * @return array 处理结果
     */
    function taobao_oms_item_inventory_get($data) {
        $req_data = array();

		$item = $data['items'];
		$goods_info = json_decode($item, true);

		$sql = "select barcode from goods_barcode where goods_id = :goods_id and spec1_code = :spec1_code and spec2_code = :spec2_code";
        $barcode_info = $this->db->get_row($sql, array(':goods_id' => $goods_info['items']['itemId'], ':spec1_code' => $goods_info['items']['SizeCode'],':spec2_code' => $goods_info['items']['ColorCode']));

		$req_data['barcode'] = $barcode_info['barcode'];
        $req_data['shop_code'] = $goods_info['items']['warehouseCode'];


        CTX()->db->begin_trans();
        var_dump($req_data);
		$ret = load_model('api/BaseInvModel')->get_shop_inv($req_data);
        var_dump($ret);die;
        CTX()->db->commit();
		
        return $this->return_info($ret['status'], $ret['message']);
    }
	
	function check_sign($request) {

    }
}
