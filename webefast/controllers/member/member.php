<?php
/*
 * 会员相关业务控制器
 */
require_lib ( 'util/web_util', true );
class Member {
	function do_list(array & $request, array & $response, array & $app) {
		$arr_sale_channel = load_model('member/MemberModel')->get_sale_channel();
		$arr_channel[0][0] = '';
		$arr_channel[0][1] = '请选择来源渠道';
		$key = 1;
		foreach ($arr_sale_channel as $value){
			$arr_channel[$key][0] = $value['sale_channel_id'];
			$arr_channel[$key][1] = $value['sale_channel_name'];
			$key++;
		}
		//print_r($arr_channel);
		$response['sale_channel'] = $arr_channel;
	}
	function detail(array & $request, array & $response, array & $app) {
		
		$ret = load_model('cangku/CangkuModel')->get_by_id($request['_id']);
		//取得省数据
		$arr_area_province= load_model('cangku/CangkuModel')->get_area('1');
		$arr_province[0][0] = '';
		$arr_province[0][1] = '请选择省';
		$key = 1;
		foreach ($arr_area_province as $value){
			$arr_province[$key][0] = $value['id'];
			$arr_province[$key][1] = $value['name'];
			$key++;
		}
		
		$response['area']['province'] = $arr_province;
		//取得市数据
		$arr_city[0][0] = '';
		$arr_city[0][1] = '请选择城市';
		if($ret['data']['province']){
			$arr_area_city= load_model('cangku/CangkuModel')->get_area($ret['data']['province']);
			$key = 1;
			foreach ($arr_area_city as $value){
				$arr_city[$key][0] = $value['id'];
				$arr_city[$key][1] = $value['name'];
				$key++;
			}
		}
		$response['area']['city'] = $arr_city;
		
		//取得区县数据
		$arr_district = array();
		$arr_district[0][0] = '';
		$arr_district[0][1] = '请选择区/县';
		if($ret['data']['city']){
			$arr_area_district= load_model('cangku/CangkuModel')->get_area($ret['data']['city']);
			$key = 1;
			foreach ($arr_area_district as $value){
				$arr_district[$key][0] = $value['id'];
				$arr_district[$key][1] = $value['name'];
				$key++;
			}
		}
		$response['area']['district'] = $arr_district;
		$response['data'] = $ret['data'];
	}
    function get_area(array & $request, array & $response, array & $app){
    	
    	$ret = load_model('cangku/CangkuModel')->get_area($request['parent_id']);
    	//print_r($ret);
    	exit_json_response($ret);
    }
	function do_edit(array & $request, array & $response, array & $app) {
		$store = get_array_vars($request, array('store_code', 'store_name','shop_name','shop_contact_person','contact_person','contact_phone','province','city','district','address','zipcode','shop_note','shop_note2','ship_area_code'));
		$ret = load_model('cangku/CangkuModel')->update($store, $request['store_id']);
		exit_json_response($ret);
	}

	function do_add(array & $request, array & $response, array & $app) {
		$store = get_array_vars($request, array('store_code', 'store_name','shop_name','shop_contact_person','contact_person','contact_phone','province','city','district','address','zipcode','shop_note','shop_note2','ship_area_code'));
		$ret = load_model('cangku/CangkuModel')->insert($store);
		exit_json_response($ret);
	}

	function do_delete(array & $request, array & $response, array & $app) {
		$ret = load_model('cangku/CangkuModel')->delete($request['store_id']);
		exit_json_response($ret);
	}
}

