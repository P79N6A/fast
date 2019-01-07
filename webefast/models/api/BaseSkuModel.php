<?php
/**
 * 淘宝商品相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('api');
require_lib('util/taobao_util', true);

class BaseSkuModel extends TbModel {

	function __construct(){
		parent::__construct('api_base_sku', 'id');
	}

	/**
	 * 添加新纪录
	 */
	function insert($data) {

		$ret = $this->is_exists($data['sku_id']);
		if ($ret['status'] > 0 && !empty($ret['data'])) return $this->format_ret(-1);

		return parent :: insert($data);
	}

	/**
	 * @param $sku_id
	 * @return array
	 */
	function get_by_sku_id($sku_id) {
		$data = $this -> get_row(array('sku_id' => $sku_id));
		return $data;
	}

	/**
	 * @param $id
	 */
	function get_by_id($id) {
		$data = $this->get_row(array('id' => $id));
		return $data;
	}

	/**
	 * @param $item_id 新框架
	 * @param array $filter
	 * @return array
	 */
	function get_list_by_item_id($item_id, $filter = array()) {

		$select = '*, status as item_sku_status';
		$sql_main = " FROM {$this->table} WHERE item_id='{$item_id}'";
		$data =  $this->get_page_from_sql($filter, $sql_main, array(), $select);

		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);
	}

	/**
	 * 更新
	 * @param $data
	 * @param $cond
	 * @return array|void
	 */
	function update($data, $cond) {
		return parent::update($data, $cond);
	}

	/**
	 * 监测是否存在
	 * @param $value
	 * @param string $field_name
	 * @return array
	 */
	function is_exists($value, $field_name = 'sku_id') {
		$ret = parent :: get_row(array($field_name => $value));

		return $ret;
	}

	/**
	 * 批量获取goods_sku
	 * @param $item_ids
	 */
	function get_goods_sku_list_by_item_ids($item_ids) {
		if (is_null($item_ids))
			return false;

		$sqlValue = array();
		$in_sql = CTX()->db->get_in_sql('item_id', $item_ids, $sqlValue);
		$sql = 'select a.quantit,b.* from ' . $this->table . ' as a left join goods_sku as b on a.goods_sku_id=b.sku_id where a.item_id in (' . $in_sql . ")";
		$result = CTX()->db->get_all($sql, $sqlValue);

		return $result;
	}
}
