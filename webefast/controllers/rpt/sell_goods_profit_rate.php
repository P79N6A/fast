<?php
/**
 * Description of sell_report
 *
 * @author user
 */
class sell_goods_profit_rate {
    //put your code here
    function data_analyse(array & $request, array & $response, array & $app){
    	$response['category'] = load_model('prm/CategoryModel')->get_category_trees();
		$response['brand'] = $this->get_purview_brand();
    }
    
    
    function report_count(array & $request, array & $response, array & $app){
        $app['fmt'] = 'json';
        $response = load_model('oms/SellGoodsProfitRateModel')->report_count($request);
    }
    
     function report_count_and_return(array & $request, array & $response, array & $app){	
        $app['fmt'] = 'json';
        $response = load_model('oms/SellGoodsProfitRateModel')->report_count_and_return($request);
    }
    
    
	function get_purview_brand(){
		//品牌  start
		$arr_brand = load_model('prm/BrandModel')->get_purview_brand();
		
		$key = 0;
		foreach ($arr_brand as $value){
			$arr_brand[$key][0] = $value['brand_code'];
			$arr_brand[$key][1] = $value['brand_name'];
			$key++;
		}
		//print_r($arr_brand);
		return $arr_brand;
		
	}
        
     //订单
    function sell_record(array &$request, array &$response, array &$app) {
        //task#1907 颜色尺码别名
        $arr = array('goods_spec1','goods_spec2');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
        $app['page'] = 'NULL';
    }
    
    //
    function sell_record_and_return(array &$request, array &$response, array &$app) {
        //task#1907 颜色尺码别名
        $arr = array('goods_spec1','goods_spec2');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
        $app['page'] = 'NULL';
    }
        
}
