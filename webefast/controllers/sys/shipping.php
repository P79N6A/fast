<?php
require_lib ( 'util/web_util', true );
class Shipping {

	function do_list(array & $request, array & $response, array & $app) {

	}

	/**
	 * 编辑快递打印模版
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function edit_print(array & $request, array & $response, array & $app) {

		$app['tpl'] = 'base/shipping_edit_print';

		$ret = load_model('base/ShippingModel')->get_by_id($request['_id']);

		$response['data']['express'] = $ret['data'];

		$response['data']['print_vars'] = load_model('base/ShippingModel')->print_vars;
		$keys = array_keys(load_model('base/ShippingModel')->print_vars);
		$vars = array_values(load_model('base/ShippingModel')->print_vars);
		foreach ($keys as & $_key) {
			$_key = "c['" . $_key . "']";
		}
		foreach ($vars as & $_key) {
			$_key = "[" . $_key . "]";
		}

		$response['data']['print_vars'] = $vars;

		$response['data']['express']['print'] = str_replace($keys, $vars, $response['data']['express']['print']);
	}

	/**
	 * 执行编辑快递打印模版
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_edit_print(array & $request, array & $response, array & $app) {
		$print = $request['print'];
		$express_id = $request['express_id'];

		//防止拥护修改快递单模板的时候，修改编辑框内的名称
		$print_line_arr = explode(';', $print);
		foreach ($print_line_arr as $line_key=>$line_value) {
			if (empty($line_value))
				continue;
			if (false !== strpos($line_value,'ADD_PRINT_TEXTA')) {
				$in_brackets_str_arr= explode(',', substr($line_value, 24,-1));
				$print = str_replace($in_brackets_str_arr[count($in_brackets_str_arr)-1], $in_brackets_str_arr[0],$print);
			}
		}

		$vars = array_values(load_model('base/ShippingModel')->print_vars);
		foreach ($vars as &$_key1) {
			$_key1 = '[' . $_key1 . ']';
		}
		$keys = array_keys(load_model('base/ShippingModel')->print_vars);
		foreach ($keys as &$_key) {
			$_key = "c['".$_key."']";
		}
		$print = str_replace($vars, $keys, $print);
		$print = str_replace('\\', '', $print);

		$data['print'] = $print;

		$ret = load_model('base/ShippingModel')->update_by_id($data, $express_id);
		exit_json_response($ret);
	}

	/**
	 * 修改打印机
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function modify_printer(array & $request, array & $response, array & $app) {

		$data['printer_name'] = strip_tags($request['printer_name']);
		$express_id = strip_tags($request['express_id']);

		$ret = load_model('base/ShippingModel')->update_by_id($data, $express_id);
		exit_json_response($ret);
	}
}