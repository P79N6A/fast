<?php
/**
 * 淘宝商品相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
//require_lang('api');
require_lib('util/taobao_util', true);
require_lib('tb_util', true);
require_model('base/ShopApiModel');

class BaseItemModel extends TbModel {

	function __construct() {
		parent::__construct('api_base_item', 'id');
	}

	/*
	 * 根据条件查询数据
	 */
	function get_by_page($filter = array()) {
		//print_r($filter);
		$sql_values = array();
		$sql_join = "";
		$sql_main = "FROM {$this->table} rl  LEFT JOIN api_base_sku r2 on rl.item_id = r2.item_id  WHERE 1";
		//商品标题
		if (isset($filter['title']) && $filter['title'] != '') {
			$sql_main .= " AND rl.title LIKE :title ";
			$sql_values[':title'] = $filter['title'] . '%';
		}
		//状态
		if (isset($filter['approve_status']) && $filter['approve_status'] != '') {
			$sql_main .= " AND rl.approve_status = :approve_status ";
			$sql_values[':approve_status'] = $filter['approve_status'];
		}
		//商品外部ID
		if (isset($filter['outer_id']) && $filter['outer_id'] != '') {
			$sql_main .= " AND rl.outer_id = :outer_id ";
			$sql_values[':outer_id'] = $filter['outer_id'];
		}
		// 店铺
		if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
			$shop_code_arr = explode(',', $filter['shop_code']);
			if (!empty($shop_code_arr)) {
				$sql_main .= " AND (";
				foreach ($shop_code_arr as $key => $value) {
					$param_shop_code = 'param_shop_code' . $key;
					if ($key == 0) {
						$sql_main .= " rl.shop_code = :{$param_shop_code} ";
					} else {
						$sql_main .= " or rl.shop_code = :{$param_shop_code} ";
					}

					$sql_values[':' . $param_shop_code] = $value;
				}
				$sql_main .= ")";
			}

		}
		$select = 'rl.id as base_id,rl.item_id as base_item_id, rl.outer_id as base_item_outer_id,rl.pic_url,rl.shop_code,rl.title,rl.quantit as base_item_quantit,rl.with_hold_quantity,rl.approve_status,rl.response_conten as base_item_response_conten,r2.id as base_sku_id,r2.sku_id,r2.outer_id as base_sku_outer_id,r2.quantit as base_sku_quantit,r2.price,r2.status';
		//echo $sql_main;
		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

		foreach ($data['data'] as $key => $value) {
			$arr_shop = load_model('base/ShopModel')->get_by_field('shop_code', $value['shop_code'], 'shop_name');
			$data['data'][$key]['shop_name'] = isset($arr_shop['data']['shop_name']) ? $arr_shop['data']['shop_name'] : '';
			if ($value['pic_url'] != '') {

				$base_item_response_conten = json_decode($value['base_item_response_conten'], true);

				$data['data'][$key]['pic_url'] = "<span><a href='{$base_item_response_conten['detail_url']}' target='_blank'><img src='" . $value['pic_url'] . "'/ width=50 height=50></a></span>";
			}
		}
		//print_r($data);
		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);
	}

	/**
	 * 获取主表
	 * @param array $filter
	 * @return array
	 */
	function get_main_by_page($filter = array()) {
		//print_r($filter);
		$sql_values = array();
		$sql_join = "";
		$sql_main = "FROM {$this->table}  WHERE 1";
		//商品标题
		if (isset($filter['title']) && $filter['title'] != '') {
			$sql_main .= " AND title LIKE :title ";
			$sql_values[':title'] = $filter['title'] . '%';
		}
		//状态
		if (isset($filter['approve_status']) && $filter['approve_status'] != '') {
			$sql_main .= " AND approve_status = :approve_status ";
			$sql_values[':approve_status'] = $filter['approve_status'];
		}

		//是否生成库存
		if (isset($filter['is_generate_inv']) && $filter['is_generate_inv'] != '') {
			$sql_main .= " AND is_generate_inv = :is_generate_inv ";
			$sql_values[':is_generate_inv'] = $filter['is_generate_inv'];
		}

		//商品外部ID
		if (isset($filter['outer_id']) && $filter['outer_id'] != '') {
			$sql_main .= " AND outer_id = :outer_id ";
			$sql_values[':outer_id'] = $filter['outer_id'];
		}
		// 店铺
		if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
			$shop_code_arr = explode(',', $filter['shop_code']);
			if (!empty($shop_code_arr)) {
				$sql_main .= " AND (";
				foreach ($shop_code_arr as $key => $value) {
					$param_shop_code = 'param_shop_code' . $key;
					if ($key == 0) {
						$sql_main .= " shop_code = :{$param_shop_code} ";
					} else {
						$sql_main .= " or shop_code = :{$param_shop_code} ";
					}

					$sql_values[':' . $param_shop_code] = $value;
				}
				$sql_main .= ")";
			}

		}
		$select = 'id as base_id,item_id as base_item_id, outer_id as base_item_outer_id,pic_url,shop_code,title,quantit as base_item_quantit,with_hold_quantity,approve_status,response_conten as base_item_response_conten';
		//echo $sql_main;
		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

		foreach ($data['data'] as $key => $value) {
			$arr_shop = load_model('base/ShopModel')->get_by_field('shop_code', $value['shop_code'], 'shop_name');
			$data['data'][$key]['shop_name'] = isset($arr_shop['data']['shop_name']) ? $arr_shop['data']['shop_name'] : '';
			if ($value['pic_url'] != '') {

				$base_item_response_conten = json_decode($value['base_item_response_conten'], true);

				$data['data'][$key]['pic_url'] = "<span><a href='{$base_item_response_conten['detail_url']}' target='_blank'><img src='" . $value['pic_url'] . "'/ width=50 height=50></a></span>";
			}
		}
		//print_r($data);
		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);
	}

	/**
	 * 获取未生成库存的
	 * $shop_code
	 * @param $is_generate_inv
	 * @return array
	 */
	function get_list_by_is_generate_inv($shop_code,$is_generate_inv) {
		$data = $this->get_all(array('shop_code'=>$shop_code, 'is_generate_inv' => $is_generate_inv));
		return $data;
	}

	/**
	 * 下载在库
	 * @param $taobao
	 */
	function get_onsale_items($taobao_util) {
		$result = array();
		$params = array();
		$params['fields'] = "cid,num_iid,modified";
		$params['page_no'] = 1;
		$params['page_size'] = 200;
		$params['order_by'] = "modified:asc";
		$total_num = 0;
		do {
			if ($params['page_no'] * $params['page_size'] < 20000) {
				$data = $taobao_util->post('taobao.items.onsale.get', $params);

				if ($data['status'] != '1') {
					//错误处理
					return $this->format_ret(-1, '', "淘宝商品信息获取失败！");
				}
				if ($params['page_no'] == 1) {
					$total_num = $data['data']['total_results'];
				}
				foreach ($data['data']['items']['item'] as $item) {
					array_push($result, $item);
				}
			} else {
				$last_data = end($result);
				$params['start_modified'] = $last_data['modified'];
				$params['end_modified'] = add_time();
				$params['page_no'] = 0;
			}
			$params['page_no']++;
		} while (($params['page_no'] - 1) * $params['page_size'] < $total_num);

		//	$cid = array();
		$num_iid = array();
		foreach ($result as $item) {
			array_push($num_iid, $item['num_iid']);
			//		array_push($cid, $item['cid']);
		}

		return $num_iid;
	}

	/**
	 * 获取详细
	 * @param $taobao_util
	 * @param $num_iids
	 */
	function batch_get_items($taobao_util, $arr_num_iid = array()) {

		$num_iid_count = count($arr_num_iid);

		$item_datas = array(); //所有商品总数据

		$page_get_count = 20; //每次获取的个数

		$params = array();
		$params['fields'] = "detail_url,num_iid,title,barcode,cid,props_name,desc,pic_url,num,price,property_alias,sku,outer_id,postage_id,list_time,modified,approve_status,created,is_fenxiao,sku.sku_id,sku.iid,sku.num_iid,sku.properties,sku.quantity,sku.price,sku.created,sku.modified,sku.properties,sku.properties_name,sku.outer_id,sku.barcode";
		for ($i = 0; $i < $num_iid_count / $page_get_count; ++$i) {
//			sleep(5);

			$_SESSION['dl_progress'] = (($i + 1) / ($num_iid_count / $page_get_count)) * 100;

			session_write_close();
			session_start();

			$num_iids = implode(array_slice($arr_num_iid, $i * $page_get_count, $page_get_count), ',');
			if ($num_iids) {
				$params['num_iids'] = $num_iids;
				$data = $taobao_util->post('taobao.items.list.get', $params);
				if ($data['status'] == '1') {

					$item_datas = array_merge($item_datas, $data['data']['items']['item']);
				}
			}
		}

		return $item_datas;
	}

	/**
	 * 插入数据
	 * @param $items_data
	 */
	function insert_data($items_data, $shop_code) {
		//成功获取数据
		if ($items_data) {
			foreach($items_data as $_item) {

				$_data = array(
					'item_id' => $_item['num_iid'],
					'shop_code' => $shop_code,
					'seller_nick' => 'seller_nick',
					'pic_url' => $_item['pic_url'],
					'quantit' => $_item['num'],
					'price' => $_item['price'],
					'modified_on_shop' => $_item['modified'],
					'approve_status' => $_item['approve_status'] == 'onsale' ? 0 : 1,
					//	'created_on_shop' => $_item['created'], 这个api未返回，未知原因
					'is_fenxiao' => $_item['is_fenxiao'],
//					'sub_stock' => '0',//未知这个哪里获取value
//					'with_hold_quantity' => '0',//未知这个哪里获取value
					'has_sku' => isset($_item['sku']) ? 1 : 0,
					'outer_id' => not_null($_item['outer_id'])?$_item['outer_id']:null,
					'title' => $_item['title'],
					'response_conten' => json_encode($_item),
				);

				$ret = $this->is_exists($_data['item_id']);
				if ($ret['status'] > 0 && !empty($ret['data'])){

					$_data['updated'] = date('Y-m-d H:i:s');
					$this->update($_data, array('item_id' => $_data['item_id']));
				} else {

					$_data['created'] = date('Y-m-d H:i:s');
					$this->insert($_data);
				}

				if (isset($_item['skus'])) {
					foreach($_item['skus']['sku'] as $_sku) {

						$_sku_data = array(
							'item_id' => $_item['num_iid'],
							'sku_id' => $_sku['sku_id'],
							'property' => $_sku['properties'],
							'quantit' => $_sku['quantity'],
							'price' => $_sku['price'],
							'modified_on_shop' => $_sku['modified'],
							//		'status' => $_sku['num_iid'], //未知属性
							'created_on_shop' => $_sku['created'],
							//		'with_hold_quantity' => $_sku['num_iid'],// 未知属性
							'outer_id' => not_null($_sku['outer_id'])?$_sku['outer_id']:null,
							'response_conten' => json_encode($_sku)
						);

						$ret = load_model('api/BaseSkuModel')->is_exists($_sku_data['sku_id']);

						if ($ret['status'] > 0 && !empty($ret['data'])) {
							$_sku_data['updated'] = date('Y-m-d H:i:s');
							load_model('api/BaseSkuModel')->update($_sku_data, array('sku_id' => $_sku_data['sku_id']));
						} else {
							$_sku_data['created'] = date('Y-m-d H:i:s');
							load_model('api/BaseSkuModel')->insert($_sku_data);
						}
					}
				}
			}
		}
	}

	/**
	 * 添加新纪录
	 */
	function insert($data) {

		$ret = $this->is_exists($data['item_id']);
		if ($ret['status'] > 0 && !empty($ret['data']))
			return $this->format_ret(-1);

		return parent :: insert($data);
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
	 * 获取
	 * @param $id
	 */
	function get_by_id($id) {

		$data = $this->get_row(array('id' => $id));
		return $data;
	}

	/**
	 * @param $item_id
	 */
	function get_by_item_id($item_id) {

		$data = $this->get_row(array('item_id' => $item_id));
		return $data;
	}


	/**
	 * 监测是否存在
	 * @param $value
	 * @param string $field_name
	 * @return array
	 */
	function is_exists($value, $field_name = 'item_id') {
		$ret = parent :: get_row(array($field_name => $value));

		return $ret;
	}

	/**
	 * 同步库存
	 * @param $quantit
	 * @param $num_iid
	 * @param $sku_id
	 *
	 */
	function store_synchro($quantit, $num_iid, $sku_id, $shop_code) {
		CTX()->log_error('$quantit---'.print_r($quantit,true));
//		$params['app'] = CTX()->get_app_conf('app_key');
//		$params['secret'] = CTX()->get_app_conf('app_secret');
//		$params['session'] = CTX()->get_app_conf('app_session');

		$mdl_shop_api = new ShopApiModel();
		$shop_data = $mdl_shop_api->get_shop_api_by_shop_code($shop_code);
		CTX()->log_error('$shop_data---'.print_r($shop_data,true));
		$params = $shop_data['api'];

		$params['num_iid'] = $num_iid;
		$params['sku_id'] = $sku_id;
		$params['quantity'] = $quantit;

		$ret = taobao_item_quantity_update($params);
CTX()->log_error('ret---'.print_r($ret,true));
		return $ret;
	}

	/**
	 * 批量库存同步
	 * @param $shop_code
	 */
	function batch_store_synchro($shop_code) {

		$data = $this->get_by_page(array('shop_code' => $shop_code));

		$ret = array();
		foreach ($data['data']['data'] as $_val) {
			$ret = $this->store_synchro($_val['base_sku_quantit'], $_val['base_item_id'], $_val['sku_id'], $shop_code);
		}

		return $ret;
	}

	/**
	 * 一键关联
	 */
	function one_click_relation_goods() {

		$result = parent::get_all(array());

		foreach ($result['data'] as $_val) {

			if (!$_val['outer_id'])
				continue;

			if ($_val['goods_id'])
				continue;

			$data = load_model('prm/GoodsModel')->get_by_outer_code($_val['outer_id']);
			if (1 == $data['status']) {
				$this->update(array('goods_id' => $data['data']['goods_id']), array('id' => $_val['id']));
			}
		}

		$ret_status = OP_SUCCESS;
		$ret_data = array();

		return $this->format_ret($ret_status, $ret_data);
	}

	/**
	 * 一键建档
	 */
	function one_click_create_goods() {

		$item_list = parent::get_all(array());

		$brands = array();
		$spec1 = array();
		$spec2 = array();
		$goods = array();
		$cats = array();

		foreach ($item_list['data'] as $_item) {

			$sku_list = load_model('api/BaseSkuModel')->get_list_by_item_id($_item['item_id'], array());

			$item_response_content = json_decode($_item['response_conten'], true);
			$cats[] = $item_response_content['cid']; //分类

			$iid = $_item['item_id'];
			$property = $item_response_content['props_name'];
			$arr = explode(";", $property);
			$info = array();
			foreach ($arr as $a) {
				$_arr = explode(":", $a);
				switch ($_arr[0]) {
					case "20000":
						array_push($brands, $_arr[3]);
						$info['brand_name'] = $_arr[3];
						break;
					case "1627207":
						$spec1[$iid . "_" . $_arr[1]] = $_arr[3];
						break;
					//尺码有两个pid
					case "20509":
					case "20549":
						$spec2[$iid . "_" . $_arr[1]] = $_arr[3];
						break;
				}
			}
			//颜色尺码如果有别名用别名替换原名称
			if (isset($item_response_content['property_alias']) && !empty($item_response_content['property_alias'])) {
				$alias = $item_response_content['property_alias'];
				$alias_piece = explode(";", $alias);
				foreach ($alias_piece as $piece) {
					$_piece = explode(":", $piece);
					switch ($_piece[0]) {
						case "1627207":
							$spec1[$iid . "_" . $_piece[1]] = $_piece[2];
							break;
						//尺码有两个pid
						case "20509":
						case "20549":
							$spec2[$iid . "_" . $_piece[1]] = $_piece[2];
							break;
					}
				}
			}
			$info ['goods_name'] = $_item['title'];
			$info ['goods_desc'] = $item_response_content['desc'];
			$info ['goods_outer_code'] = $_item['outer_id'];
			$info ['goods_code'] = $_item['outer_id'];
			$info ['price'] = $_item['price'];
			$info ['cost_price'] = $_item['price'];
			$info ['sell_price'] = $_item['price'];
			$info ['status'] = 1;
			$info ['cid'] = $item_response_content['cid']; //分类

//			$info ['start_num'] = 1;
			$info ['goods_img'] = $_item['pic_url'];
			$info ['sku'] = $sku_list['data']['data'];
//			$info ['category_code'] = $this->categories[$good['cid']];
//			$info ['num_iid'] = $_item['item_id'];
//			$info ['property_alias'] = $good['property_alias'];
//			if (isset($good['skus']['sku'])) {
//				$info ['goods_sku'] = $good['skus']['sku'];
//			}
			array_push($goods, $info);
		}

		$return_spec1 = $this->headle_spec1($spec1);
		$return_spec2 = $this->headle_spec2($spec2);
		$return_brand = $this->headle_brand($brands);
		$return_cat = $this->headle_cat($cats); //key=>value 结构的分类

		//本地基础档案结构（包括规格1，规格2，品牌，分类）
		$property_arr = array(
			'spec1' => $return_spec1,
			'spec2' => $return_spec2,
			'brand' => $return_brand,
			'cat' => $return_cat
		);

		//处理商品
		$this->headle_goods($goods, $property_arr);

		$ret_status = OP_SUCCESS;
		$ret_data = array();

		return $this->format_ret($ret_status, $ret_data);
	}

	/**
	 * 处理sku
	 * @param $goods
	 * @param $propery_arr
	 */
	private function headle_sku($goods, $skus, $propery_arr) {


	}

	/**
	 * 处理商品
	 * @param $goods
	 * @param $property_arr 本地基础档案结构（包括规格1，规格2，品牌，分类）
	 */
	private function headle_goods($goods,$property_arr) {

		if (!$goods) {
			return false;
		}

		foreach ($goods as $_good) {

			$_flag = load_model('prm/GoodsModel')->is_exists($_good['goods_outer_code'], 'goods_outer_code');
			if (!empty($_flag['data']))
				continue;

			$_good['brand_code'] = $property_arr['brand'][$_good['brand_name']]['brand_code'];
			$_good['category_code'] = $property_arr['cat'][$_good['cid']]['category_code'];
			load_model('prm/GoodsModel')->insert($_good);

			$this->headle_sku($_good, $_good['sku'], $property_arr);
		}

		return true;
	}

	/**
	 * 处理规格1
	 * @param $spec1
	 */
	private function headle_spec1($spec1s) {

		if (!$spec1s)
			return false;

		$return_spec1 = array();
		foreach ($spec1s as $_spec) {

			$_flag = load_model('prm/Spec1Model')->is_exists($_spec, 'spec1_name');
			if (!empty($_flag['data'])) {
				$return_spec1[$_spec] = array('spec1_id'=>$_flag['data']['spec1_id'],'spec1_name' => $_spec,'spec1_code'=>$_flag['data']['spec1_code']);
				continue;
			}

			$_data = array('spec1_code' => rand(), 'spec1_name' => $_spec);
			load_model('prm/Spec1Model')->insert($_data);
			$return_spec1[$_spec] = array('spec1_id'=>$this->db->insert_id(),'spec1_name' => $_spec,'spec1_code' => $_data['spec1_code']);
		}

		return $return_spec1;
	}

	/**
	 * 处理规格2
	 * @param $spec2
	 */
	private function headle_spec2($spec2s) {

		if (!$spec2s)
			return false;

		$return_spec2 = array();

		foreach ($spec2s as $_spec) {

			$_flag = load_model('prm/Spec2Model')->is_exists($_spec, 'spec2_name');
			if (!empty($_flag['data'])) {

				$return_spec2[$_spec] = array('spec2_id'=>$_flag['data']['spec2_id'],'spec2_name' => $_spec,'spec2_code'=>$_flag['data']['spec2_code']);
				continue;
			}

			$_data = array('spec2_code' => rand(), 'spec2_name' => $_spec);
			load_model('prm/Spec2Model')->insert($_data);
			$return_spec2[$_spec] = array('spec2_id'=>$this->db->insert_id(),'spec2_name' => $_spec,'spec2_code' => $_data['spec2_code']);
		}

		return $return_spec2;
	}

	/**
	 * 处理品牌
	 * @param $brand
	 */
	private function headle_brand($brands) {
		if (!$brands)
			return false;

		$return_brand = array();

		foreach ($brands as $_brand) {

			$_flag = load_model('prm/BrandModel')->is_exists($_brand, 'brand_name');
			if (!empty($_flag['data'])) {
				$return_brand[$_brand] = array('brand_id'=>$_flag['data']['brand_id'],'brand_name' => $_brand,'brand_code'=>$_flag['data']['brand_code']);
				continue;
			}

			$_data = array('brand_code' => rand(), 'brand_name' => $_brand);
			load_model('prm/BrandModel')->insert($_data);
			$return_brand[$_brand] = array('brand_id'=>$this->db->insert_id(),'brand_name' => $_brand,'brand_code' => $_data['brand_code']);
		}

		return $return_brand;
	}

	/**
	 * 处理分类 (暂时废气)
	 * @param $cats
	 * @return bool
	 */
	private function headle_cat($cats) {
		if (!$cats)
			return false;

		$app_key = CTX()->get_app_conf('app_key');
		$app_secret = CTX()->get_app_conf('app_secret');
		$app_session = CTX()->get_app_conf('app_session');
		$app_nick = CTX()->get_app_conf('app_nick');

		$taobao_util = new taobao_util($app_key, $app_secret, $app_session, $app_nick);

		$params = array();
		$params['fields'] = "cid,parent_cid,name";
		$params['cids'] = implode(',', $cats);
		$data = $taobao_util->post('taobao.itemcats.get', $params);

		$return_cats = array();

		if ($data['status'] == '1') {
			foreach ($data['data']['item_cats']['item_cat'] as $_cat) {

				$_flag = load_model('prm/CategoryModel')->is_exists($_cat['name'], 'category_name');

				if (!empty($_flag['data'])) {
					$return_cats[$_cat['cid']] = array('category_id'=>$_flag['data']['category_id'],'category_name' => $_cat['name'],'category_code'=>$_flag['data']['category_code']);
					continue;
				}

				$_data = array('category_code' => rand(), 'category_name' => $_cat['name']);
				load_model('prm/CategoryModel')->insert($_data);

				$return_cats[$_cat['cid']] = array('category_id'=>$this->db->insert_id(),'category_name' => $_cat['name'],'category_code'=>$_data['category_code']);
			}
		}
		return $return_cats;
	}

	/**
	 * 批量上架
	 * @param $item_ids
	 */
	function batch_listing($item_ids) {

		if ($item_ids) {
			foreach ($item_ids as $_item_id) {

				$_data = $this->get_by_item_id($_item_id);

				$mdl_shop_api = new ShopApiModel();
				$shop_data = $mdl_shop_api->get_shop_api_by_shop_code($_data['data']['shop_code']);

				$params = $shop_data['api'];

//				$params['app'] = CTX()->get_app_conf('app_key');
//				$params['secret'] = CTX()->get_app_conf('app_secret');
//				$params['session'] = CTX()->get_app_conf('app_session');

				$params['num_iid'] = $_data['data']['item_id'];
				$params['num'] = $_data['data']['quantit'];

				$ret = taobao_item_update_listing($params);
				if (1 == $ret['status']) {
					$this->update(array('approve_status' => 0), array('id' => $_data['data']['id']));
				}
			}
		}

		return $this->format_ret(OP_SUCCESS, array());
	}

	/**
	 * 批量下架
	 * @param $items_iids
	 */
	function batch_delisting($item_ids) {
		if ($item_ids) {
			foreach ($item_ids as $_item_id) {

				$_data = $this->get_by_item_id($_item_id);

				$mdl_shop_api = new ShopApiModel();
				$shop_data = $mdl_shop_api->get_shop_api_by_shop_code($_data['data']['shop_code']);

				$params = $shop_data['api'];

//				$params['app'] = CTX()->get_app_conf('app_key');
//				$params['secret'] = CTX()->get_app_conf('app_secret');
//				$params['session'] = CTX()->get_app_conf('app_session');

				$params['num_iid'] = $_data['data']['item_id'];

				$ret = taobao_item_update_delisting($params);
				if (1 == $ret['status']) {
					$this->update(array('approve_status' => 1), array('id' => $_data['data']['id']));
				}
			}
		}

		return $this->format_ret(OP_SUCCESS, array());
	}


	/**
	 * 批量获取goods_sku
	 * @param $item_ids
	 */
	function update_is_generate_inv_by_item_ids($is_generate_inv, $item_ids) {
		if (is_null($item_ids))
			return false;

		$sqlValue = array();
		$in_sql = CTX()->db->get_in_sql('item_id', $item_ids, $sqlValue);
		$sql = 'update ' . $this->table . " set is_generate_inv={$is_generate_inv} where item_id in (" . $in_sql . ")";
		return CTX()->db->query($sql, $sqlValue);
	}

}
