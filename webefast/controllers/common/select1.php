<?php
class select1{
	function order_goods(array & $request, array & $response, array & $app){
		//print_r($request);
                $key_arr = array('goods_code','spec1_code','spec2_code','spec1_name','spec2_name','goods_name');
                $sku_info =  load_model('goods/SkuCModel')->get_sku_info($request['sku'],$key_arr);
		$response['cur_goods'] = $sku_info;
		$response['goods'] = load_model('prm/GoodsModel')->order_select($request['goods_code'],$request['sku']);
		$response['sell_record_detail_id'] = isset($request['sell_record_detail_id'])?$request['sell_record_detail_id']:'';
		$response['sell_change_detail_id'] = isset($request['sell_change_detail_id'])?$request['sell_change_detail_id']:'';
		$response['deal_code'] = $request['deal_code'];
		$response['avg_money'] = $request['avg_money'];
		$response['is_gift'] = $request['is_gift'];
		$response['num'] = $request['num'];
		$arr = array('goods_spec1','goods_spec2');
		$ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
		$response['goods_spec1_rename'] =isset($ret_arr['goods_spec1'])?$ret_arr['goods_spec1']:'' ;
		$response['goods_spec2_rename'] =isset($ret_arr['goods_spec2'])?$ret_arr['goods_spec2']:'' ;
		
	}
    /**
	 *
	 * 方法名       return_goods
	 *
	 * 功能描述     根据商品信息返回商品的所有规格列表
	 *
	 * @author      BaiSon PHP R&D
	 * @date        2015-07-08
	 * @param       mixed &$request	 
     * @param       mixed &$response
	 * @return      mixed $response
	 */
    function return_goods(array & $request, array & $response, array & $app)
    {
        //echo '<pre>';print_r($request);
        $response['cur_goods'] = load_model('prm/GoodsModel')->get_sku_list($request['sku']);
		$response['goods'] = load_model('prm/GoodsModel')->order_select($request['goods_code'],$request['sku']);
		$response['sell_return_detail_id'] = $request['sell_return_detail_id'];
		//$response['sell_change_detail_id'] = $request['sell_change_detail_id'];
		$response['deal_code'] = $request['deal_code'];
		$response['avg_money'] = $request['avg_money'];
		$response['is_gift'] = $request['is_gift'];
		$response['num'] = $request['num'];
		$arr = array('goods_spec1','goods_spec2');
		$ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
		$response['goods_spec1_rename'] =isset($ret_arr['goods_spec1'])?$ret_arr['goods_spec1']:'' ;
		$response['goods_spec2_rename'] =isset($ret_arr['goods_spec2'])?$ret_arr['goods_spec2']:'' ;
    }
}