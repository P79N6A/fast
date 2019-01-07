<?php

require_model("sys/DanjuPrintModel");
//require_lib('sq/phpQuery');
//require_model("base/goods_barcode_rule");
//require_model('sys/sys_params');
require_lib('util/crm_util');
ini_set('display_errors', 0);
class danju_print {

	function __construct() {

	}

	/**
	 * 列出所有类型模板
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_list(array & $request, array & $response, array & $app) {
		$app['tpl'] = "sys/danju_print/danju_print_do_index_new";
	}

	/**
	 * 通过类型获取明细 (新框架)
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function get_list_by_type(array & $request, array & $response, array & $app) {
//		$app['page'] = 'NULL';
//		$app['tpl'] = "sys/danju_print/print_list";
//
//		$mdl_print = new MdlDanjuPrint();
//
//		$response['sql'] = $mdl_print->return_sql_by_type($request['click_value']);


		$ret = load_model('sys/DanjuPrintModel')->get_list_by_type($request['print_data_type'], array());
		$dataset = $ret['data'];
		foreach($dataset['data'] as $_key => $_val) {
			$dataset['data'][$_key]['template_page_style'] = get_page_style_name($_val['template_page_style']);
		}

		exit_table_data_json($dataset['data'], $dataset['filter']['record_count']);
	}

	/**
	 * 获取商店打印列表通过打印类型
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_danju_shop_index_by_print_data_type(array & $request, array & $response, array & $app) {

		$print_date_type_name = get_print_data_type_name($request['print_data_type']);

		$app['title'] = '商店单据模版设置-'.$print_date_type_name;

		$app['tpl'] = "common/danju_print/danju_print_shop_index_by_print_data_type";

		$mdl_print = new MdlDanjuPrint();
		$danju_print_list = $mdl_print->get_list_by_print_data_type($request['print_data_type']);

		$shop_sql = "select * from base_shop where 1=1 ";
		$shop_data = CTX()->db->get_all($shop_sql);

		if ($shop_data) {
			foreach($shop_data as $_shop_val) {

				foreach($danju_print_list as $_print_val) {

					$mdl_print = new MdlDanjuPrint();

					$shop_print_info = $mdl_print->get_shop_print_by_code($_print_val['danju_print_code'], $_shop_val['distributor_code']);

					//插入默认商店打印模版
					if (!$shop_print_info) {

						$_data = $_print_val;
						unset($_data['print_id']);
						$_data['shop_code'] = $_shop_val['shop_code'];
						$mdl_print = new MdlDanjuPrint();
						$mdl_print->add_danju_shop_print($_data);


					}
				}
			}
		}

		$mdl_print = new MdlDanjuPrint();

		$print_data_type = $request['print_data_type'];

		$data = $mdl_print->do_danju_shop_index_by_print_data_type($print_data_type);

		$response['sql'] = $data['sql'];
	}

	/**
	 *
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function get_list_by_type_and_shop_code(array & $request, array & $response, array & $app) {
		$app['page'] = 'NULL';
		$app['tpl'] = "common/danju_print/shop_print_list";

		$click_value = json_decode(stripslashes($request['click_value']), true);

		$mdl_print = new MdlDanjuPrint();

		$response['sql'] = $mdl_print->return_sql_by_type_and_shop_code($click_value['print_data_type'], $click_value['shop_code']);
	}

	/**
	 * 打开编辑单据编辑模版
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function edit_print(array & $request, array & $response, array & $app) {

		$app['tpl'] = "sys/danju_print/danju_print_edit_print";

		$danju_print_code = $request['danju_print_code'];

		if ('goods_barcode' == $danju_print_code || 'goods_barcode1' == $danju_print_code || 'goods_barcode2' == $danju_print_code || 'goods_barcode_hangtag' == $danju_print_code || 'pur_purchaser_record_barcode' == $danju_print_code || 'pur_store_in_record_barcode'== $danju_print_code || 'dim_allocate_order_record_barcode' == $danju_print_code || 'dim_allocate_record_barcode' == $danju_print_code || 'pur_store_in_record_barcode_hangtag' == $danju_print_code) {
			$app['tpl'] = "sys/danju_print/danju_print_edit_print_by_goods_barcode";
		}

		$mdl_danju_print = new DanjuPrintModel();
		$danju_print_conf = $mdl_danju_print->danju_print_conf[$danju_print_code];

		$response['shop_code'] = '';
		if (not_null($request['shop_code'])) {
			$response['shop_code'] = $request['shop_code'];
			$danju_print_data = $mdl_danju_print->get_shop_print_by_code($danju_print_code, $request['shop_code']);
		} else {

			//01.10,去处只属于商店级打印部分
			foreach($danju_print_conf['main_conf'] as $_conf_key => $_conf_val) {
				if (array_key_exists('is_shop_print', $_conf_val)) {
					unset($danju_print_conf['main_conf'][$_conf_key]);
				}
			}
			$danju_print_data = $mdl_danju_print->get_print_by_code($danju_print_code);
		}

		$response['danju_print_data'] = $danju_print_data['data'];
		$response['danju_print_code'] = $danju_print_code;

		$response['danju_print_conf'] = $danju_print_conf;
	}

	/*
	 * 编辑单据打印模板
	 */
	function do_save_print(array & $request, array & $response, array & $app) {

		$app['fmt'] = 'json';

		$cond['danju_print_code'] = $request['danju_print_code'];

		$param['danju_print_content'] = stripslashes(trim($request['danju_print_content']));

		$param['danju_print_content'] = str_replace('<a onclick="dlg_grid_table_td_style(this)">[编]</a>', '', $param['danju_print_content']);
		$param['danju_print_content'] = str_replace('<A onclick=dlg_grid_table_td_style(this)>[编]</A>', '', $param['danju_print_content']);

		if (not_null($request['customer_print_conf'])) {
			$param['customer_print_conf'] = serialize($request['customer_print_conf']);
		}

		$param['print_html'] = stripslashes(trim($request['print_html']));
		$param['print_html_style'] = json_encode($request['print_html_style']);

		$print_mdl = new DanjuPrintModel();

		if (not_null($request['shop_code'])) {

			$cond['shop_code'] = $request['shop_code'];
			$ret = $print_mdl->save_danju_shop_print($param, $cond);
		} else {
			$ret = $print_mdl->save_danju_print($param, $cond);
		}

		exit_json_response($ret);
	}


	/**
	 * 条码高度保存
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 * @return array|bool
	 */
	function do_save_barcode_setting(array & $request, array & $response, array & $app) {

		$cond['danju_print_code'] = $request['danju_print_code'];

		$barcode_height = trim($request['barcode_height']);
		$barcode_width = trim($request['barcode_width']);
		$barcode_top_offset = trim($request['barcode_top_offset']);
		$barcode_left_offset = trim($request['barcode_left_offset']);
		$codeStyle = trim($request['codeStyle']);
		$barcode_space_between = trim($request['barcode_space_between']);//条码与内容间距
		$barcode_col = trim($request['barcode_col']);
		$barcode_col_width = trim($request['barcode_col_width']);

		$param['extend_attr'] = json_encode(array('barcode_height'=>$barcode_height,'barcode_width'=>$barcode_width, 
			'barcode_top_offset'=>$barcode_top_offset, 'barcode_left_offset'=>$barcode_left_offset, 'codeStyle'=>$codeStyle, 
			'barcode_space_between' => $barcode_space_between,'barcode_col'=>$barcode_col,'barcode_col_width'=>$barcode_col_width));

		$print_mdl = new DanjuPrintModel();

		if (not_null($request['shop_code'])) {

			$cond['shop_code'] = $request['shop_code'];
			$ret = $print_mdl->save_danju_shop_print($param, $cond);
		} else {
			$ret = $print_mdl->save_danju_print($param, $cond);
		}
		exit_json_response($ret);
	}

	/*
	 * 预览单据打印模板
	 */
	function view_print(array & $request, array & $response, array & $app) {

		$app['title'] = "预览单据模板";
		if (true !== ($check_flag = check_param($request, 'danju_print_code')))
			return $response = $check_flag;

		$danju_print_code = $request['danju_print_code'];

		$mdl_print = new MdlDanjuPrint();

		$danju_view_print = $mdl_print->get_print_by_code($danju_print_code);
		$danju_view_print['danju_print_content'] = preg_replace("/<!--<replace_empty>-->(.*?)<!--<\/replace_empty>-->/is", '', $danju_view_print['danju_print_content']);
		$danju_view_print['danju_print_content'] = preg_replace("/<a onclick=\"clean_drop(.*?)\">X<\/a>/is", '', $danju_view_print['danju_print_content']);

		return $response['view_print'] = $danju_view_print;
	}

	/**
	 * 打印test
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 * @return array|bool
	 */
	function do_print(array & $request, array & $response, array & $app) {

		$app['page'] = 'null';

		$app['title'] = '打印';
		if (true !== ($check_flag = check_param($request, 'danju_print_code')))
			return $response = $check_flag;

		$danju_print_code = $request['danju_print_code'];

		$to_print_data = parse_print($danju_print_code, explode(',', $request['fhd_id']));

		return $response['do_print'] = $to_print_data;
	}

	/*
	 *  单据打印
	 */
	function do_print_record(array & $request, array & $response, array & $app) {
		$app['page'] = 'null';

		$app['tpl'] = 'sys/danju_print/danju_print_do_print';

		$app['title'] = '打印';

		$print_data_type = $print_code = $is_record_ext_print = null;
		if (not_null($request['print_data_type'])) {
			$print_data_type = $request['print_data_type'];
		}

		if (not_null($request['print_code'])) {
			$print_code = $request['print_code'];
		}

		if ($print_data_type) {
			$mdl_danju_print = new DanjuPrintModel();
			$_print_info = $mdl_danju_print->get_default_by_print_data_type($print_data_type);
			if (1 == $_print_info['status']) {

				$print_info = $_print_info['data'];

				$print_code = $print_info['danju_print_code'];
				$is_record_ext_print = $print_info['is_record_ext_print'];
			}
		}

		$record_ids = $request['record_ids'];

		//是否扩展打印
		if ($is_record_ext_print) {
			$mdl=new MdlSysParams();
			$size_drop=$mdl->getParams(78);
			$to_print_data = parse_ext_record_print($print_code, explode(',', $record_ids),array(),$size_drop['data']);
		} else {
			$mdl_danju_print = new DanjuPrintModel();
			$to_print_data = $mdl_danju_print->parse_print($print_code, explode(',', $record_ids));
		}
		$response['print_data_type'] = $request['print_data_type'];
		$response['record_ids'] = $request['record_ids'];
		return $response['do_print'] = $to_print_data;
	}

	/**
	 *  单据打印
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_print_record_no_preview(array & $request, array & $response, array & $app) {
		$app['page'] = 'null';

		$app['tpl'] = 'common/danju_print/danju_print_do_print_no_preview';

		$app['title'] = '打印';

		$print_data_type = $print_code = $is_record_ext_print = null;
		if (not_null($request['print_data_type'])) {
			$print_data_type = $request['print_data_type'];
		}

		if (not_null($request['print_code'])) {
			$print_code = $request['print_code'];
		}

		if ($print_data_type) {
			$mdl_danju_print = new MdlDanjuPrint();
			$print_info = $mdl_danju_print->get_default_by_print_data_type($print_data_type);
			if ($print_info) {
				$print_code = $print_info['danju_print_code'];
				$is_record_ext_print = $print_info['is_record_ext_print'];
			}
		}

		$record_ids = $request['record_ids'];

		//是否扩展打印
		if ($is_record_ext_print) {
			$mdl=new MdlSysParams();
			$size_drop=$mdl->getParams(78);
			$to_print_data = parse_ext_record_print($print_code, explode(',', $record_ids),array(),$size_drop['data']);
		} else {
			$to_print_data = parse_print($print_code, explode(',', $record_ids));
		}

		return $response['do_print'] = $to_print_data;
	}

	/**
	 * 打印商品条码
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 * @return array|bool
	 */
	function do_print_goods_barcode(array & $request, array & $response, array & $app) {

		require_model('base/goods_barcode');
		$app['page'] = 'null';

		$app['tpl'] = 'common/danju_print/danju_print_do_print';
		if (true !== ($check_flag = check_param($request, 'print_code')))
			return $response = $check_flag;

		$print_code = $request['print_code'];

		$params = object_to_array(json_decode($request['params']));
		$mdl_goods_barcode = new MdlGoodsBarcode();

		$goods_barcode_list = $mdl_goods_barcode->get_barcode_by_cond($params['search']);

		$barcode_ids = array();
		foreach ($goods_barcode_list as $barcode_val) {

			$barcode_ids[] = $barcode_val['barcode_id'];
		}

		$to_print_data = parse_print($print_code, $barcode_ids);

		$response['do_print'] = $to_print_data;
	}

	/**
	 * 打印颜色
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_print_base_color(array & $request, array & $response, array & $app) {

		require_model('base/color');
		$app['page'] = 'null';
//		$app['tpl'] = 'common/danju_print/danju_print_do_print';

		$app['tpl'] = 'common/danju_print/danju_print_do_print_no_preview';

		if (true !== ($check_flag = check_param($request, 'print_code')))
			return $response = $check_flag;

		$mdl_color = new MdlColor();
		$color_list = $mdl_color->get_color_by_cond(array());

		$color_ids_arr = array();
		foreach($color_list as $color_val) {
			$color_ids_arr[$color_val['color_id']] = $color_val['color_id'];
		}

		$data['detail_area_list'] = $color_list;

		$print_code = $request['print_code'];
		$to_print_data = parse_print_by_data($print_code, $data);

		return $response['do_print'] = $to_print_data;
	}

	/**
	 * 打印条码通过ids
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_print_goods_barcode_by_ids(array & $request, array & $response, array & $app) {

		require_model('base/goods_barcode');

		$app['page'] = 'null';
		$app['tpl'] = 'common/danju_print/danju_print_do_print';

		if (true !== ($check_flag = check_param($request, 'print_code')))
			return $response = $check_flag;

		$print_code = $request['print_code'];

		$mdl_print = new MdlDanjuPrint();
		$print_conf_info = $mdl_print->get_print_by_code($print_code);

		if ('goods_barcode' == $print_code || 'goods_barcode_hangtag') {
			$app['tpl'] = 'common/danju_print/danju_print_do_print_by_goods_barcode';
		}

		$params = object_to_array(json_decode(stripslashes($request['params'])));

		$goods_barcode_ids = $params['goods_barcode_ids'];

		if (!$goods_barcode_ids) {
			return $response['do_print']['data'] = null;
		}

		//打印数量
		$goods_barcode_print_num = array();
		$params_print_num = explode(',', $params['str_print_num']);
		foreach ($params_print_num as $num_val) {
			$tmp_arr = explode(':', $num_val);
			$goods_barcode_print_num[$tmp_arr[0]] = intval($tmp_arr[1]);
		}

		$mdl_goods_barcode = new MdlGoodsBarcode();

		$goods_barcode_list = array();

		$tmp_goods_barcode_list = $mdl_goods_barcode->get_barcode_by_ids($goods_barcode_ids);
		foreach ($tmp_goods_barcode_list as $goods_barcode_val) {
			$goods_barcode_list[$goods_barcode_val['barcode_id']] = $goods_barcode_val;
		}

		$barcode_serial_num = array();

		$tmp_to_print_data = array();

		foreach ($goods_barcode_list as $goods_barcode_val) {

			$mdl_goods_barcode_rule = new MdlGoodsBarcodeRule();
			$barcode_rule_info = $mdl_goods_barcode_rule->get_info_by_id($goods_barcode_val['barcode_rule_id']);

			//原始barcode
			$barcode = $goods_barcode_val['barcode'];

			//每个sku打印次数
			$print_num = $goods_barcode_print_num[$goods_barcode_val['barcode_id']];

			//循环拼接流水号
			for($t = 0; $t < $print_num; ++$t) {

				$serial_num = null;
				if ($goods_barcode_val['serial_num']) {
					if (array_key_exists($goods_barcode_val['barcode_id'], $barcode_serial_num)) {
						$barcode_serial_num[$goods_barcode_val['barcode_id']] += 1;
					} else {
						$barcode_serial_num[$goods_barcode_val['barcode_id']] = $goods_barcode_val['serial_num'];
					}
					//01.16 超过流水号，重新循环流水
					if (strlen($barcode_serial_num[$goods_barcode_val['barcode_id']]) > intval($goods_barcode_val['serial_num_length'])) {
						$barcode_serial_num[$goods_barcode_val['barcode_id']] = $barcode_rule_info['serial_num'];
					}

					$serial_num = $this->add_barcode_serial(intval($barcode_serial_num[$goods_barcode_val['barcode_id']]), intval($goods_barcode_val['serial_num_length']));
				}

				$goods_barcode_val['barcode'] = $barcode . $serial_num;

				$data['main_area_info'][$goods_barcode_val['barcode_id']] = $goods_barcode_val;
				$data['barcode_area_info'][$goods_barcode_val['barcode_id']] = $goods_barcode_val;
				$tmp_to_print_data[] = parse_print($print_code, array($goods_barcode_val['barcode_id']), $data);
			}
		}

		//更新流水号
		foreach ($barcode_serial_num as $key => $serial_num) {

			$mdl_goods_barcode = new MdlGoodsBarcode();
			$data['serial_num'] = (int)$serial_num + 1; //01.16
			$cond['barcode_id'] = $key;

			$mdl_goods_barcode->edit($data, $cond);
		}

		$to_print_data = array(
			'config' => '',
			'data' => ''
		);
		foreach ($tmp_to_print_data as $print_data) {
			$to_print_data['config'] = $print_data['config'];

			foreach($print_data['data'] as $print_data_val) {
				$to_print_data['data'][] = $print_data_val;
			}
		}

		$this->set_barcode_param($response,$print_conf_info);
		$response['do_print'] = $to_print_data;
	}



	private function set_barcode_param(& $response,& $print_conf_info){
		$response['barcode_height'] = 8;
		$response['barcode_width'] = $print_conf_info['template_page_width'];
		$response['barcode_top_offset'] = $response['barcode_left_offset'] =  1;
		$response['codeStyle'] = '128A';
		$response['barcode_col'] = 1;
		$response['barcode_col_width'] = 100;
	//	$response['dpi'] = '';

		$response['barcode_space_between'] = 6;

		if (not_null($print_conf_info['extend_attr'])) {
			$extend_attr = object_to_array(json_decode($print_conf_info['extend_attr']));
			if (not_null($extend_attr['barcode_height'])) {
				$response['barcode_height'] = $extend_attr['barcode_height'];
			}

			if (not_null($extend_attr['barcode_width'])) {
				$response['barcode_width'] = $extend_attr['barcode_width'];
			}

			if (not_null($extend_attr['barcode_top_offset'])) {
				$response['barcode_top_offset'] = $extend_attr['barcode_top_offset'];
			}

			if (not_null($extend_attr['barcode_left_offset'])) {
				$response['barcode_left_offset'] = $extend_attr['barcode_left_offset'];
			}

			if (not_null($extend_attr['codeStyle'])) {
				$response['codeStyle'] = $extend_attr['codeStyle'];
			}

			//条码与内容间距
			if (not_null($extend_attr['barcode_space_between'])) {
				$response['barcode_space_between'] = $extend_attr['barcode_space_between'];
			}
			if (not_null($extend_attr['barcode_col_width'])) 
				$response['barcode_col_width'] = $extend_attr['barcode_col_width'];
				
			if (not_null($extend_attr['barcode_col'])) 
				$response['barcode_col'] = $extend_attr['barcode_col'];			
			
//			if (not_null($extend_attr['dpi'])) {
//				$response['dpi'] = $extend_attr['dpi'];
//			}
		}
		
	}

	/**
	 * 计算打印总数量(单据)
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function get_print_total_page_goods_barcode_by_id_and_print_code(array & $request, array & $response, array & $app) {

		require_model('base/goods_barcode, pur/purchaser_record, pur/store_in_record,  base/goods_barcode_rule');
		require_model('dim/allocate_record_detail, dim/allocate_record_detail');

		$app['page'] = 'null';
		$app['tpl'] = 'pur/purchaser_record/dlg_print_barcode_page';

		$record_id = $request['record_id'];

		$detail_list = array();
		$print_code = $request['print_code'];

		switch($print_code) {
			case 'pur_purchaser_record_barcode':

				$mdl_purchase_record = new MdlPurchaserRecord();
				$detail_list = $mdl_purchase_record->get_detail_list_by_pid($record_id);
				break;
			case 'pur_store_in_record_barcode':
			case 'pur_store_in_record_barcode_hangtag':

				$mdl_store_in_record = new MdlStoreInRecord();
				$detail_list = $mdl_store_in_record->get_detail_list_by_pid($record_id);
				break;
			case 'dim_allocate_order_record_barcode':

				$mdl_allocate_order_record_detail = new MdlAllocateOrderRecordDetail();
				$detail_list = $mdl_allocate_order_record_detail->get_detail_by_pid($record_id);
				break;
			case 'dim_allocate_record_barcode':

				$mdl_allocate_record_detail = new MdlAllocateRecordDetail();
				$detail_list = $mdl_allocate_record_detail->get_detail_by_pid($record_id);
				break;
		}

		$total_num = 0;

		$page_size = 50;//每页50个

		foreach($detail_list as $detail_val) {

			$total_num += $detail_val['num'];
		}

		$response = return_value(1, 'success', array('total_page'=> ceil($total_num/$page_size), 'page_size' => $page_size, 'total_num' => $total_num, 'record_id' => $record_id, 'print_code' => $print_code));
	}

	/**
	 * 单据条码打印
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_print_record_goods_barcode_by_id(array & $request, array & $response, array & $app) {

		require_model('base/goods_barcode, pur/purchaser_record, pur/store_in_record, base/goods_barcode_rule');
		require_model('dim/allocate_record_detail, dim/allocate_record_detail');

		$app['page'] = 'null';
		$app['tpl'] = 'common/danju_print/danju_print_do_print_by_goods_barcode';

		$print_code = $request['print_code'];
		$record_id = $request['record_id'];

		//2014-09-15
		$page_no = null;
		$page_size = null;//每页50个
		$barcode_from_num = null;//分页条码开始位置
		$barcode_end_num = null;//分页条码结束位置
		if (not_null($request['page_no'])) {
			$page_size = 50;//每页50个
			$page_no = $request['page_no'];
			$barcode_from_num = ($page_no - 1) * $page_size;
			$barcode_end_num = $page_no * $page_size;
		}

		$detail_list = array();
		switch($print_code) {
			case 'pur_purchaser_record_barcode':

				$mdl_purchase_record = new MdlPurchaserRecord();
				$detail_list = $mdl_purchase_record->get_detail_list_by_pid($record_id);
				break;
			case 'pur_store_in_record_barcode':
			case 'pur_store_in_record_barcode_hangtag':

				$mdl_store_in_record = new MdlStoreInRecord();
				$detail_list = $mdl_store_in_record->get_detail_list_by_pid($record_id);
				break;
			case 'dim_allocate_order_record_barcode':

				$mdl_allocate_order_record_detail = new MdlAllocateOrderRecordDetail();
				$detail_list = $mdl_allocate_order_record_detail->get_detail_by_pid($record_id);
				break;
			case 'dim_allocate_record_barcode':

				$mdl_allocate_record_detail = new MdlAllocateRecordDetail();
				$detail_list = $mdl_allocate_record_detail->get_detail_by_pid($record_id);
				break;
		}

		//获取主条码
		$mdl_barcode_rule = new MdlGoodsBarcodeRule();
		$barcode_rule_info = $mdl_barcode_rule->get_main_info();

		$tmp_to_print_data = array();

		$_tt = 0;//2014-09-15

		foreach($detail_list  as $detail_val) {

			$mdl_goods_barcode = new MdlGoodsBarcode();
			$goods_barcode = $mdl_goods_barcode->get_info_by_goods_color_size_code($detail_val['goods_code'], $detail_val['color_code'], $detail_val['size_code'], $barcode_rule_info['barcode_rule_id']);

			for($t = 1; $t <= $detail_val['num']; ++$t) {

				//2014-09-15
				++$_tt;//所有明细数量的总和
				if ($page_no) {
					if ($_tt <= $barcode_from_num)
						continue;

					if ($_tt > $barcode_end_num)
						continue;
				}

				$detail_val['barcode'] = null;

				if ($goods_barcode) {

					$serial_num = null;

					if ($goods_barcode['serial_num']!==null) {
						$serial_num = $goods_barcode['serial_num'] + $t;

						//01.16 超过流水号，重新循环流水
						if (strlen($serial_num) > intval($goods_barcode['serial_num_length'])) {
							$serial_num = $barcode_rule_info['serial_num'];
						}

						$serial_num = $this->add_barcode_serial(intval($serial_num), intval($goods_barcode['serial_num_length']));

						//更新流水号
						$mdl_goods_barcode = new MdlGoodsBarcode();
						$data['serial_num'] = (int)$serial_num;
						$cond['barcode_id'] = $goods_barcode['barcode_id'];
						$mdl_goods_barcode->edit($data, $cond);
					}

					$detail_val['barcode'] = $goods_barcode['barcode'].$serial_num;
				}

				$data['main_area_info'][$detail_val['purchaser_record_detail_id']] = $detail_val;
				$data['barcode_area_info'][$detail_val['purchaser_record_detail_id']] = $detail_val;

				$tmp_to_print_data[] = parse_print($print_code, array($detail_val['purchaser_record_detail_id']), $data);
			}
		}
		$to_print_data = array(
			'config' => '',
			'data' => ''
		);
		foreach ($tmp_to_print_data as $print_data) {
			$to_print_data['config'] = $print_data['config'];

			foreach($print_data['data'] as $print_data_val) {
				$to_print_data['data'][] = $print_data_val;
			}
		}
		$mdl_print = new MdlDanjuPrint();
		$print_conf_info = $mdl_print->get_print_by_code($print_code);		
		$this->set_barcode_param($response,$print_conf_info);
		$response['do_print'] = $to_print_data;
	}

	/**
	 * 采购单条码打印（已作废-2014-09-16）
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_print_pur_store_in_record_goods_barcode_by_id(array & $request, array & $response, array & $app) {

		require_model('base/goods_barcode, pur/store_in_record, base/goods_barcode_rule');

		$app['page'] = 'null';
		$app['tpl'] = 'common/danju_print/danju_print_do_print_by_goods_barcode';

		$print_code = $request['print_code'];
		$record_id = $request['record_id'];

		//2014-09-15
		$page_no = null;
		$page_size = null;//每页50个
		$barcode_from_num = null;//分页条码开始位置
		$barcode_end_num = null;//分页条码结束位置
		if (not_null($request['page_no'])) {
			$page_size = 50;//每页50个
			$page_no = $request['page_no'];
			$barcode_from_num = ($page_no - 1) * $page_size;
			$barcode_end_num = $page_no * $page_size;
		}

		$mdl_store_in_record = new MdlStoreInRecord();

		$detail_list = $mdl_store_in_record->get_detail_list_by_pid($record_id);

		//获取主条码
		$mdl_barcode_rule = new MdlGoodsBarcodeRule();
		$barcode_rule_info = $mdl_barcode_rule->get_main_info();

		$tmp_to_print_data = array();

		$_tt = 0;

		foreach($detail_list  as $detail_val) {

			$mdl_goods_barcode = new MdlGoodsBarcode();
			$goods_barcode = $mdl_goods_barcode->get_info_by_goods_color_size_code($detail_val['goods_code'], $detail_val['color_code'], $detail_val['size_code'], $barcode_rule_info['barcode_rule_id']);

			for($t = 1; $t <= $detail_val['num']; ++$t) {

				//2014-09-15
				++$_tt;//所有明细数量的总和
				if ($page_no) {
					if ($_tt <= $barcode_from_num)
						continue;

					if ($_tt > $barcode_end_num)
						continue;
				}

				$detail_val['barcode'] = null;

				if ($goods_barcode) {

					$serial_num = null;

					if ($goods_barcode['serial_num']) {
						$serial_num = $goods_barcode['serial_num'] + $t;

						//01.16 超过流水号，重新循环流水
						if (strlen($serial_num) > intval($goods_barcode['serial_num_length'])) {
							$serial_num = $barcode_rule_info['serial_num'];
						}

						$serial_num = $this->add_barcode_serial(intval($serial_num), intval($goods_barcode['serial_num_length']));

						//更新流水号
						$mdl_goods_barcode = new MdlGoodsBarcode();
						$data['serial_num'] = $serial_num;
						$cond['barcode_id'] = $goods_barcode['barcode_id'];
						$mdl_goods_barcode->edit($data, $cond);
					}

					$detail_val['barcode'] = $goods_barcode['barcode'].$serial_num;
				}

				$data['main_area_info'][$detail_val['store_in_record_detail_id']] = $detail_val;
				$data['barcode_area_info'][$detail_val['store_in_record_detail_id']] = $detail_val;

				$tmp_to_print_data[] = parse_print($print_code, array($detail_val['store_in_record_detail_id']), $data);
			}
		}
		$to_print_data = array(
			'config' => '',
			'data' => ''
		);
		foreach ($tmp_to_print_data as $print_data) {
			$to_print_data['config'] = $print_data['config'];

			foreach($print_data['data'] as $print_data_val) {
				$to_print_data['data'][] = $print_data_val;
			}
		}
		$mdl_print = new MdlDanjuPrint();
		$print_conf_info = $mdl_print->get_print_by_code($print_code);		
		$this->set_barcode_param($response,$print_conf_info);
		$response['do_print'] = $to_print_data;
	}

	/**
	 * 商品库存账条码打印
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_print_stm_query_goods_barcode_by_cond(array & $request, array & $response, array & $app) {

		require_model('base/goods_barcode, goods/goods_inv, base/goods_barcode_rule');

		$app['page'] = 'null';
		$app['tpl'] = 'common/danju_print/danju_print_do_print_by_goods_barcode';

		$params = object_to_array(json_decode($request['params']));

		$print_code = 'goods_barcode';

		$mdl_print = new MdlDanjuPrint();
		$print_conf_info = $mdl_print->get_print_by_code($print_code);


		if (isset($params['search'])) {
			$params = $params['search'];
		}

		$print_code = $request['print_code'];

		$print_goods_num = $request['print_goods_num'];

		$mdl_goods_inv = new MdlGoodsInv();
		
		$params['order_by']=true;	//排序
		$data_sql = $mdl_goods_inv->get_goods_inv_price_by_param($params, true);
		if ($print_goods_num) {
			$data_sql['sql'] = $data_sql['sql'].' limit '.$print_goods_num;
		}

		$detail_list = CTX()->db->get_all($data_sql['sql'], $data_sql['sql_value']);

		//获取主条码
		$mdl_barcode_rule = new MdlGoodsBarcodeRule();
		$barcode_rule_info = $mdl_barcode_rule->get_main_info();

		$tmp_to_print_data = array();

		foreach($detail_list  as $detail_val) {

			$detail_val['num'] = 1;
			$mdl_goods_barcode = new MdlGoodsBarcode();
			$goods_barcode = $mdl_goods_barcode->get_info_by_goods_color_size_code($detail_val['goods_code'], $detail_val['color_code'], $detail_val['size_code'], $barcode_rule_info['barcode_rule_id']);

			for($t = 1; $t <= $detail_val['num']; ++$t) {

				$detail_val['barcode'] = null;

				if ($goods_barcode) {

					$serial_num = null;

					if ($goods_barcode['serial_num']) {
						$serial_num = $goods_barcode['serial_num'] + $t;
						//01.16 超过流水号，重新循环流水
						if (strlen($serial_num) > intval($goods_barcode['serial_num_length'])) {
							$serial_num = $barcode_rule_info['serial_num'];
						}

						$serial_num = $this->add_barcode_serial(intval($serial_num), intval($goods_barcode['serial_num_length']));

						//更新流水号
						$mdl_goods_barcode = new MdlGoodsBarcode();
						$data['serial_num'] = $serial_num;
						$cond['barcode_id'] = $goods_barcode['barcode_id'];
						$mdl_goods_barcode->edit($data, $cond);
					}

					$detail_val['barcode'] = $goods_barcode['barcode'].$serial_num;
				}

				$data['main_area_info'][$detail_val['goods_inv_id']] = $detail_val;
				$data['barcode_area_info'][$detail_val['goods_inv_id']] = $detail_val;

				$tmp_to_print_data[] = parse_print($print_code, array($detail_val['goods_inv_id']), $data);
			}
		}
		$to_print_data = array(
			'config' => '',
			'data' => ''
		);
		foreach ($tmp_to_print_data as $print_data) {
			$to_print_data['config'] = $print_data['config'];

			foreach($print_data['data'] as $print_data_val) {
				$to_print_data['data'][] = $print_data_val;
			}
		}


		$this->set_barcode_param($response,$print_conf_info);
		$response['do_print'] = $to_print_data;
	}

	/**
	 * 生成条形码
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function generate_barcode(array & $request, array & $response, array & $app) {

		ob_clean();
		$app['page'] = 'null';

//		require_lib('sq/db,sq/common,sq/verification,sq/code_128');
//
//		$code = $request['code'];
//		$o = new BarCode128($code, '', 'B');
////		$o->setUnitWidth(1);
//		$o->createBarCode('png');
//		exit;

		require_lib('sq/db,sq/common,sq/verification,sq/barcode/code128');
		$code = $request['code'];

		$c_code = new Code128();
		$c_code->setScale(1); // Resolution
		$c_code->setThickness(30); // Thickness
		$c_code->setStart('C');

		$font = new BCFontFile(ROOT_PATH .'lib/sq/barcode/Arial.ttf', 8);
		$c_code->setFont($font);

		$c_code->parse($code); // Text
		header('Content-Type:image/png');
		$c_code->output();
		//UPCAbarcode($code);
		exit;
	}

	/**
	 * 设置打印纸张
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function set_page_style(array & $request, array & $response, array & $app) {

		$app['tpl'] = "sys/danju_print/danju_print_set_page_style";

		$mdl_danju_print = new DanjuPrintModel();
		$_page_style = $mdl_danju_print->page_style;

		$page_style = array();
		foreach($_page_style as $_key => $_val) {
			$_t = array($_key, $_key);
			$page_style[] = $_t;
		}

		$_t = array('custom_pager', '自定义');
		$page_style[] = $_t;

		$response['page_style'] = $page_style;

		$print_id = $request['print_id'];
		$ret = $mdl_danju_print->get_print_by_id($print_id);
		$response['data'] = $ret['data'];
	}

	/**
	 * 设置打印纸张(商店)
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function set_shop_page_style(array & $request, array & $response, array & $app) {

		$mdl_danju_print = new MdlDanjuPrint();
		$response['page_style'] = $mdl_danju_print->page_style;

		$shop_print_id = $request['shop_print_id'];
		$danju_print_info = $mdl_danju_print->get_shop_print_by_id($shop_print_id);
		$response['danju_print_info'] = return_value(1, 'success', $danju_print_info);
	}

	/**
	 * 执行设置纸张类型
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_set_page_style(array & $request, array & $response, array & $app) {

		$mdl_danju_print = new DanjuPrintModel();
		$template_page_style = $request['template_page_style'];

		$template_page_width = $template_page_height = 0;
		if (not_null($request['template_page_width']))
			$template_page_width = $request['template_page_width'];
		if (not_null($request['template_page_height']))
			$template_page_height = $request['template_page_height'];

		if ('custom_pager' != $template_page_style) {
			$pager_data = $mdl_danju_print->page_style[$template_page_style];
			$template_page_width = $pager_data['width'];
			$template_page_height = $pager_data['height'];
		}

		$print_id = $request['print_id'];

		$ret = $mdl_danju_print->set_pager_style_by_id($print_id, $template_page_style, $template_page_width, $template_page_height);
		exit_json_response($ret);
	}

	/**
	 * 执行设置纸张类型
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_set_shop_page_style(array & $request, array & $response, array & $app) {

		$check_flag = check_param($request, 'shop_print_id', 'template_page_style');
		if ($check_flag !== true) {
			return $response = $check_flag;
		}

		$mdl_danju_print = new MdlDanjuPrint();
		$template_page_style = $request['template_page_style'];

		$template_page_width = $template_page_height = 0;
		if (not_null($request['template_page_width']))
			$template_page_width = $request['template_page_width'];
		if (not_null($request['template_page_height']))
			$template_page_height = $request['template_page_height'];

		if ('custom_pager' != $template_page_style) {
			$pager_data = $mdl_danju_print->page_style[$template_page_style];
			$template_page_width = $pager_data['width'];
			$template_page_height = $pager_data['height'];
		}

		$shop_print_id = $request['shop_print_id'];

		$flag = $mdl_danju_print->set_shop_pager_style_by_id($shop_print_id, $template_page_style, $template_page_width, $template_page_height);
		$response = return_value(1, '设置成功');
	}

	/**
	 * 获取纸张类型
	 * @param   array $request
	 * @param   array $response
	 * @param   array $app
	 * @return  array
	 */
	function get_page_style(array & $request, array & $response, array & $app) {

		$mdl_danju_print = new DanjuPrintModel();
		$result = array();
		if ($request['page_style'] != 'custom_pager') {
			$page_style = $request['page_style'];
			$result = $mdl_danju_print->page_style[$page_style];
		}
		$response = $result;
	}

	/**
	 * 修改打印机
	 * @param   array $request
	 * @param   array $response
	 * @param   array $app
	 * @return  array
	 */
	function modify_printer(array & $request, array & $response, array & $app) {

		$data['printer_name'] = strip_tags($request['printer_name']);
		$cond['print_id'] = strip_tags($request['print_id']);
		$mdl_danju_print = new DanjuPrintModel();
		$ret = $mdl_danju_print->save_danju_print($data, $cond);

		exit_json_response($ret);
	}

	/**
	 * 选择打印机(商店)
	 * @param   array $request
	 * @param   array $response
	 * @param   array $app
	 * @return  array
	 */
	function shop_select_printer(array & $request, array & $response, array & $app) {

		$check_flag = check_param($request, 'shop_print_id', 'printer_name');
		if ($check_flag !== true) {
			return $response = $check_flag;
		}

		$data['printer_name'] = strip_tags($request['printer_name']);
		$cond['shop_print_id'] = strip_tags($request['shop_print_id']);
		$mdl_danju_print = new MdlDanjuPrint();
		$flag = $mdl_danju_print->save_danju_shop_print($data, $cond);

		if (!$flag) return $response = return_value(-101, '操作失败');
		$response = return_value(1, '操作成功');
	}

	/**
	 * 设置默认
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function set_default(array & $request, array & $response, array & $app) {

		$app['fmt'] = 'json';
		$print_data_type = $request['print_data_type'];
		$print_id = $request['print_id'];

		$mdl_print = new DanjuPrintModel();

		$ret = $mdl_print->set_default($print_data_type, $print_id);

		exit_json_response($ret);
	}

	/**
	 * 设置默认（店铺）
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function set_shop_default(array & $request, array & $response, array & $app) {
		$app['fmt'] = 'json';
		$print_data_type = $request['print_data_type'];
		$shop_print_id = $request['shop_print_id'];
		$shop_code = $request['shop_code'];

		$mdl_print = new MdlDanjuPrint();

		$flag = $mdl_print->set_shop_default($print_data_type,$shop_code, $shop_print_id);

		if (!$flag)
			return $response = return_value(-101, '操作失败');
		$response = return_value(1, '操作成功');
	}

	/**
	 * 设置启用
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 * @return array
	 */
	function set_shop_enable(array & $request, array & $response, array & $app) {
		$app['fmt'] = 'json';
		$shop_print_id = $request['shop_print_id'];

		$mdl_print = new MdlDanjuPrint();

		$flag = $mdl_print->set_shop_enable_status($shop_print_id, 1);

		if (!$flag)
			return $response = return_value(-101, '操作失败');
		$response = return_value(1, '操作成功');
	}

	/**
	 * 设置不启用
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 * @return array
	 */
	function set_shop_disable(array & $request, array & $response, array & $app) {
		$app['fmt'] = 'json';
		$shop_print_id = $request['shop_print_id'];

		$mdl_print = new MdlDanjuPrint();

		$flag = $mdl_print->set_shop_enable_status($shop_print_id, 0);

		if (!$flag)
			return $response = return_value(-101, '操作失败');
		$response = return_value(1, '操作成功');
	}

	/**
	 * 同步主模板
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function sync_main(array & $request, array & $response, array & $app) {
		$app['fmt'] = 'json';
		$shop_print_id = $request['shop_print_id'];

		$mdl_print = new MdlDanjuPrint();
		$shop_print_info = $mdl_print->get_shop_print_by_id($shop_print_id);

		$mdl_print = new MdlDanjuPrint();
		$print_info = $mdl_print->get_print_by_code($shop_print_info['danju_print_code']);

		$data = array(
			'danju_print_content' => $print_info['danju_print_content'],
			'customer_print_conf' => $print_info['customer_print_conf'],
			'template_page_width' => $print_info['template_page_width'],
			'template_page_height' => $print_info['template_page_height'],
			'template_page_style' => $print_info['template_page_style'],
			'print_html' => $print_info['print_html'],
			'print_html_style' => $print_info['print_html_style']
		);

		$cond = array(
			'shop_print_id' => $shop_print_id
		);

		$mdl_print = new MdlDanjuPrint();
		$flag = $mdl_print->save_danju_shop_print($data, $cond);
		if (!$flag)
			return $response = return_value(-101, '操作失败');

		$response = return_value(1, '操作成功');
	}

	/**
	 * 返回复制页面
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function return_html_dlg_copy_shop_print(array & $request, array & $response, array & $app) {

		$response['shop_print_id'] = $request['shop_print_id'];
		$app['page'] = 'null';
		$app['tpl'] = 'common/danju_print/dlg_copy_shop_print';
	}

	/**
	 * 复制动作
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_copy_shop_print(array & $request, array & $response, array & $app) {

		$app['fmt'] = 'json';

		$shop_print_id = $request['shop_print_id'];
		$remark = $request['remark'];

		$mdl_print = new MdlDanjuPrint();

		$flag = $mdl_print->copy_shop_print($shop_print_id, $remark);

		if (!$flag)
			return $response = return_value(-101, '操作失败');

		$response = return_value(1, '操作成功');
	}

	/**
	 * 粘贴动作
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_shop_print_paste(array & $request, array & $response, array & $app) {

		$app['fmt'] = 'json';
		$copy_id = $request['copy_id'];
		$shop_print_id = $request['shop_print_id'];

		$mdl_print = new MdlDanjuPrint();

		$copy_info = $mdl_print->get_shop_print_copy_info($copy_id);

		$copy_info = unserialize($copy_info['print_data']);

		$data = array(
			'danju_print_content' => $copy_info['danju_print_content'],
			'customer_print_conf' => $copy_info['customer_print_conf'],
			'template_page_width' => $copy_info['template_page_width'],
			'template_page_height' => $copy_info['template_page_height'],
			'template_page_style' => $copy_info['template_page_style'],
			'extend_attr' => $copy_info['extend_attr'],
			'print_html' => $copy_info['print_html'],
			'print_html_style' => $copy_info['print_html_style'],
		);

		$cond = array(
			'shop_print_id' => $shop_print_id
		);

		$mdl_print = new MdlDanjuPrint();
		$mdl_print->save_danju_shop_print($data, $cond);

		$response = return_value(1, '操作成功');
	}

	/**
	 * 保存整体设置
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 * @return array|bool
	 */
	function do_save_page_setting(array & $request, array & $response, array & $app) {

		$danju_print_code = $request['danju_print_code'];

		$cond['danju_print_code'] = $request['danju_print_code'];

		$page_zoom = trim($request['page_zoom']);
		$page_top_offset = trim($request['page_top_offset']);
		$page_left_offset = trim($request['page_left_offset']);

	//	$dpi = trim($request['dpi']);

		$mdl_print = new DanjuPrintModel();

		if (not_null($request['shop_code'])) {

			$print_info = $mdl_print->get_shop_print_by_code($danju_print_code, $request['shop_code']);
		} else {
			$print_info = $mdl_print->get_print_by_code($danju_print_code);
		}

		$extend_attr = object_to_array(json_decode($print_info['extend_attr']));

		$extend_attr['page_zoom'] = $page_zoom;
		$extend_attr['page_top_offset'] = $page_top_offset;
		$extend_attr['page_left_offset'] = $page_left_offset;

	//	$extend_attr['dpi'] = $dpi;

		$params = array(
			'extend_attr' => json_encode($extend_attr)
		);

		$mdl_print = new DanjuPrintModel();
		if (not_null($request['shop_code'])) {
			$cond['shop_code'] = $request['shop_code'];
			$ret = $mdl_print->save_danju_shop_print($params, $cond);
		} else {

			$ret = $mdl_print->save_danju_print($params, $cond);
		}

		exit_json_response($ret);
	}

	/**
	 * 添加条码流水
	 * @param $code
	 * @param $length
	 * @return string
	 */
	function add_barcode_serial($code, $length) {

		if (!$length)
			return $code;

		if (strlen($code) > $length) {
			$code = '1';
		}

		//小于长度补0
		if (strlen($code) < $length) {

			$code_length = strlen($code);
			for ($i = 0; $i < ($length - strlen($code_length)); ++$i) {
				$code = '0' . $code;
			}
		}

		return $code;
	}
	
    /**
     * 插件下载
     */
    function ocx_download(array & $request, array & $response, array & $app){
    	//插件下载
		$file = ROOT_PATH."webpub/plugins/ocx/sq365ocx.exe";
		$fname = "sq365ocx.exe";
		if(!file_exists($file)){
			header('Content-Type: text/html;charset=utf-8');
			echo "<html><head></head><body>文件不存在</body></html>";
		}
		$f = fopen($file,"r");
		Header("Content-type:application/octet-stream");
		Header("Accept-Ranges:bytes");
		Header("Accept-Length:".filesize($file));
		Header("Content-Disposition:attachment;filename=".$fname);
		echo fread($f,filesize($file));
		fclose($f);
    }

	/**
	 * 本方法作废，现在前端直接下载
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function lodop_download(array & $request, array & $response, array & $app) {

		$version = $request['version'];

		$file = null;
		$fname = null;
		if (64 == $version) {
			$file = ROOT_PATH."webpub/js/print/lodop/install_lodop64.exe";
			$fname = "install_lodop64.exe";
		}
		if (32 == $version) {
			$file = ROOT_PATH."webpub/js/print/lodop/install_lodop32.exe";
			$fname = "install_lodop32.exe";
		}

		//插件下载
		require_lib('util/download_util');
		force_download($fname, file_get_contents($file));

//		if(!file_exists($file)){
//			header('Content-Type: text/html;charset=utf-8');
//			echo "<html><head></head><body>文件不存在</body></html>";
//		}
//		$f = fopen($file,"r");
//		Header("Content-type:application/octet-stream");
//		Header("Accept-Ranges:bytes");
//		Header("Accept-Length:".filesize($file));
//		Header("Content-Disposition:attachment;filename=".$fname);
//
//
//		echo fread($f,filesize($file));
//		fclose($f);
	}
}