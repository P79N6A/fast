<?php
/**
 * 商品控制器相关业务
 * @author dfr
 *
 */
require_lib ( 'util/web_util', true );
class goods_diy {
	//组合商品
	function detail(array & $request, array & $response, array & $app){
                $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code('spec_power');
                $response['spec_power'] = isset($arr_spec) ? $arr_spec : '';
		$goods_arr = load_model('prm/GoodsModel')->get_by_id($request['goods_id']);
		$goods_code = $goods_arr['data']['goods_code'];
                if($arr_spec['spec_power'] == 0){
                    $ret = load_model('prm/GoodsBarcodeModel')->set_spec($goods_code);
                }
		$arr = array(':goods_code' =>$goods_code );
		$barcord = load_model('prm/GoodsBarcodeModel')->get_barcode_list($arr);
		$response['goods_code'] = $goods_code;
		$response['goods_id'] = $request['goods_id'];
		//$response['sku'] = $request['sku'];
		filter_fk_name($barcord, array('goods_code|goods_code', 'spec1_code|spec1_code','spec2_code|spec2_code'));
		//价格

		$arr_price = load_model('prm/GoodsModel')->get_by_field('goods_code',$goods_code,'sell_price','goods_price');;
		if(isset($arr_price['data']['sell_price'])){
			$sell_price = round($arr_price['data']['sell_price'],2);
			if($sell_price == 0){
				$sell_price = '';
			}
		}

		foreach ($barcord as $key=> $v){
            $sql = "select price from goods_sku where barcode = :barcode";
            $sql_val = array(':barcode' => $v['barcode']);
            $price = ctx()->db->get_value($sql, $sql_val);
			$barcord[$key]['sell_price'] = empty($price) ? $sell_price : $price;
			//if($v['sku'] == $request['sku']){
				$arr1 = array(':p_sku' => $v['sku'],':p_goods_code' => $goods_code);
				$diy = load_model('prm/GoodsDiyModel')->get_diy_list($arr1);

				foreach($diy as $k =>$v1){
					$arr_price1 = load_model('goods/SkuCModel')->get_sku_info($v1['sku']);
					$diy[$k]['sell_price'] = empty($v1['price']) ? empty($arr_price1['price']) ? $arr_price1['sell_price'] : $arr_price1['price'] : $v1['price'];
					$diy[$k]['barcode'] = $arr_price1['barcode'] ;

				}
				filter_fk_name($diy, array('goods_code|goods_code', 'spec1_code|spec1_code','spec2_code|spec2_code'));
				$barcord[$key]['diy'] = $diy;

			//}
		}
                    $response['barcord'] = $barcord;


		$response['goods_code'] = $goods_code;
		//spec1别名
		$arr = array('goods_spec1');
		$arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
		$response['goods_spec1_rename'] =isset($arr_spec1['goods_spec1'])?$arr_spec1['goods_spec1']:'' ;
		//spec2别名
		$arr = array('goods_spec2');
		$arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
		$response['goods_spec2_rename'] =isset($arr_spec2['goods_spec2'])?$arr_spec2['goods_spec2']:'' ;
	}
	//ajax 组合
	function  show_detail(array &$request, array &$response, array &$app){
		//print_r($request);
		$arr1 = array(':p_sku' => $request['p_sku'],':p_goods_code' => $request['p_goods_code']);
		$diy = load_model('prm/GoodsDiyModel')->get_diy_list($arr1);

		filter_fk_name($diy, array('goods_code|goods_code', 'spec1_code|spec1_code','spec2_code|spec2_code'));
		//print_r($diy);
		$response['diy'] = $diy;
	}
	//添加组合商品
	function do_add_detail(array &$request, array &$response, array &$app){
		//print_r($request);
		$data = $request['data'];
		//调整单明细添加
		$ret = load_model('prm/GoodsDiyModel')->add_detail_action($request['p_sku'],$request['p_goods_code'],$data);
		exit_json_response($ret);
	}
	//保存
	function save(array &$request, array &$response, array &$app){
		$app['fmt'] = 'json';
		if(!empty($request['barcode_barcode'])){
			 //条码修改
			$ret = load_model('prm/GoodsBarcodeModel')->update_save($request['barcode_barcode']);

		}
		if(!empty($request['diy'])){
			//组装商品数量修改
			$ret = load_model('prm/GoodsDiyModel')->update_save($request['diy']);
		}

		$response = $ret;
	}
	//删除条码相关
	function del_barcord(array &$request, array &$response, array &$app){
		//print_r($request);	exit;
		$app['fmt'] = 'json';
		$ret = load_model('prm/GoodsDiyModel')->del_barcord($request['sku'],$request['goods_code']);
		$response = $ret;
	}
	//del_diy删除组合
	 function del_diy(array &$request, array &$response, array &$app){
	 	$app['fmt'] = 'json';
	 	$ret = load_model('prm/GoodsDiyModel')->del_diy($request['goods_diy_id'],$request['p_goods_code'],$request['p_sku']);
	 	$response = $ret;
	 }
}