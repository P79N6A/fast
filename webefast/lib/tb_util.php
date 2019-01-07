<?php
require_lib('util/taobao_util', true);
require_lib('comm_util', true);
/**
 * 库存同步
 * @param array $parameter
 */
function taobao_item_quantity_update($parameter = array()) {

	$taobao = new taobao_util($parameter['app'],$parameter['secret'], $parameter['session']);

	$params = array();

	$params['num_iid'] = $parameter['num_iid'];

	if (not_null($parameter['sku_id'])) {
		$params['sku_id'] = $parameter['sku_id'];
	}

	$params['quantity'] = $parameter['quantity'];

	$data = $taobao->post('taobao.item.quantity.update', $params);
	return $data;
}

/**
 * 上架
 * @param array $parameter
 */
function taobao_item_update_listing($parameter = array()) {
	$taobao = new taobao_util($parameter['app'],$parameter['secret'], $parameter['session']);

	$params = array();

	$params['num_iid'] = $parameter['num_iid'];
	$params['num'] = $parameter['num'];
	$data = $taobao->post('taobao.item.update.listing',$params);
	return $data;
}

/**
 * 下架
 * @param array $parameter
 */
function taobao_item_update_delisting($parameter = array()) {

	$taobao = new taobao_util($parameter['app'],$parameter['secret'], $parameter['session']);

	$params = array();

	$params['num_iid'] = $parameter['num_iid'];
	$data = $taobao->post('taobao.item.update.delisting', $params);
	return $data;
}

/**
 * 区域下载
 * @param array $parameter
 */
function taobao_areas_get($parameter = array()) {
	$taobao = new taobao_util($parameter['app'],$parameter['secret'], $parameter['session']);

	$params = array();
	$params['fields'] = 'id,type,name,parent_id,zip';
	$data = $taobao->post('taobao.areas.get', $params);
	return $data;
}

/**
 * 查询商家被授权品牌列表和类目列表
 */
function taobao_itemcats_authorize_get($parameter = array()) {
	$taobao = new taobao_util($parameter['app'],$parameter['secret'], $parameter['session']);

	$params = array();
	$params['fields'] = 'brand.vid, brand.name, item_cat.cid, item_cat.name, item_cat.status,item_cat.sort_order,item_cat.parent_cid,item_cat.is_parent, xinpin_item_cat.cid, xinpin_item_cat.name, xinpin_item_cat.status, xinpin_item_cat.sort_order, xinpin_item_cat.parent_cid, xinpin_item_cat.is_parent';

//	$params['fields'] =  'brands';
	$data = $taobao->post('taobao.itemcats.authorize.get', $params);
	return $data;
}

/**
 * 获取后台供卖家发布商品的标准商品类目
 * @param array $parameter
 */
function taobao_itemcats_get($parameter = array()) {
	$taobao = new taobao_util($parameter['app'],$parameter['secret'], $parameter['session']);

	$params = array();

	if (not_null($parameter['parent_cid'])) {
		$params['parent_cid'] = $parameter['parent_cid'];
	}

	if (not_null($parameter['cids'])) {
		$params['cids'] = $parameter['cids'];
	}

	$params['fields'] = 'cid,parent_cid,name,is_parent,status,sort_order';
	$data = $taobao->post('taobao.itemcats.get', $params);
	return $data;
}

/**
 *获取标准商品类目属性
 */
function taobao_itemprops_get($parameter = array()) {
	$taobao = new taobao_util($parameter['app'],$parameter['secret'], $parameter['session']);

	$params = array();

	if (not_null($parameter['cid'])) {
		$params['cid'] = $parameter['cid'];
	}

	if (not_null($parameter['pid'])) {
		$params['pid'] = $parameter['pid'];
	}
	if (not_null($parameter['parent_pid'])) {
		$params['parent_pid'] = $parameter['parent_pid'];
	}

	$params['fields'] = 'pid,parent_pid,parent_vid,name,is_sale_prop,is_color_prop,is_item_prop,prop_values,is_allow_alias,is_input_prop,cid';
	$data = $taobao->post('taobao.itemprops.get', $params);
	return $data;
}

/**
 * 获取标准类目属性值
 * @param array $parameter
 */
function taobao_itempropvalues_get($parameter = array()) {
	$taobao = new taobao_util($parameter['app'],$parameter['secret'], $parameter['session']);

	$params = array();

	if (not_null($parameter['cid'])) {
		$params['cid'] = $parameter['cid'];
	}

	$params['fields'] = 'cid,pid,prop_name,vid,name,name_alias,status,sort_order';
	$data = $taobao->post('taobao.itempropvalues.get', $params);
	return $data;
}

/**
 * 更新商品信息
 * @param array $parameter
 */
function taobao_item_update($parameter = array()) {
	$taobao = new taobao_util($parameter['app'],$parameter['secret'], $parameter['session']);

	$params = array();

	$params['num_iid'] = $parameter['num_iid'];

	if (not_null($parameter['outer_id'])) {
		$params['outer_id'] = $parameter['outer_id'];
	}

	$data = $taobao->post('taobao.item.update', $params);
	return $data;
}


