<?php
require_lib ( 'util/web_util', true );
class inv_record {
    function do_list(array & $request, array & $response, array & $app) {
		//类别 start
		/*
		$response['category'] = $this ->get_category();
		//品牌  start
		$response['brand'] = $this->get_brand();
		//年份 start
		$response['year'] = $this->get_year();
		//季节 start
		$response['season'] = $this->get_season();
		*/

        if (isset($request['is_entity']) && $request['is_entity'] = 1) {
            $response['store'] = load_model('base/StoreModel')->get_entity_store();
            $conf = 'sys/lof_order_type_entity';
        } else {
            $response['store'] = load_model('base/StoreModel')->get_select(0, 2);
            $conf = 'sys/lof_order_type';
        }

        $lof_order_type = require_conf($conf);
        $lof_arr = array();
        foreach ($lof_order_type as $key => $value) {
            $lof_arr[$key][0] = $key;
            $lof_arr[$key][1] = $value['name'];
        }
        $response['lof_order_type'] = $lof_arr;
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] =isset($arr_spec1['goods_spec1'])?$arr_spec1['goods_spec1']:'' ;
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] =isset($arr_spec2['goods_spec2'])?$arr_spec2['goods_spec2']:'' ;
        //批次是否开启
        $arr = array('lof_status');
        $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['lof'] =isset($arr_lof['lof_status'])?$arr_lof['lof_status']:'' ;
        //流水类型
        //$response['record_type'] = load_model('prm/InvRecordModel')->record_type;

	}

    function entity_do_list(array & $request, array & $response, array & $app) {
        $request['is_entity'] = 1;
        $this->do_list($request, $response, $app);
        $login_type = CTX()->get_session('login_type');
        if ($login_type > 0) {
            $response['store_code'] = CTX()->get_session('oms_shop_code');
        } else {
            $response['store_code'] = '';
        }
        $response['is_entity'] = 1;
    }

    //品牌数据
	function get_brand(){
		//品牌  start
		$arr_brand = load_model('prm/BrandModel')->get_brand();
		$key = 0;
		foreach ($arr_brand as $value){
			$arr_brand[$key][0] = $value['brand_code'];
			$arr_brand[$key][1] = $value['brand_name'];
			$key++;
		}
		return $arr_brand;

	}
	//季节
	function get_season(){
		$arr_season = load_model('base/SeasonModel')->get_season();
		$key = 0;
		foreach ($arr_season as $value){
			$arr_season[$key][0] = $value['season_code'];
			$arr_season[$key][1] = $value['season_name'];
			$key++;
		}
		return $arr_season;
	}
	//年份
	function get_year(){
		$arr_year = load_model('base/YearModel')->get_year();
		$key = 0;
		foreach ($arr_year as $value){
			$arr_year[$key][0] = $value['year_code'];
			$arr_year[$key][1] = $value['year_name'];
			$key++;
		}
		return $arr_year;
	}


}



