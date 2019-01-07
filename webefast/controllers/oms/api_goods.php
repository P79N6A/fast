<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
class api_goods {
	function do_list(array & $request, array & $response, array & $app) {
		
	}
	/**
	 * 通过类型获取明细
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function get_sku_list_by_item_id(array & $request, array & $response, array & $app) {
                if(isset($request['limit'])){
                     $filter['page_size'] = $request['limit'];
                }
		$ret = load_model('oms/ApiGoodsSkuModel')->get_list_by_item_id($request['goods_from_id'], $filter,$request['is_allow_sync_inv_value']);
		$dataset = $ret['data'];

		exit_table_data_json($dataset['data'], $dataset['filter']['record_count']);
	}
	function update_active(array &$request, array &$response, array &$app) {
		$arr = array('enable' => 1, 'disable' => 0);
		$ret = load_model('api/sys/ApiGoodsModel')->update_active($arr[$request['type']], $request['goods_from_id']);
		exit_json_response($ret);
	}
        function update_active_sku(array &$request, array &$response, array &$app) {

		$response = load_model('api/sys/ApiGoodsModel')->update_active_sku($request['type'], $request['id']);
		
        }
        
        
	//批量
	function p_update_active(array &$request, array &$response, array &$app) {
		$arr = array('enable' => 1, 'disable' => 0);
		$ret = load_model('api/sys/ApiGoodsModel')->p_update_active($arr[$request['type']], $request['id'], $request['ids']);
		exit_json_response($ret);
	}
        //一键
	function once_update_active(array &$request, array &$response, array &$app) {               
		$arr = array('enable' => 1, 'disable' => 0);
		$ret = load_model('api/sys/ApiGoodsModel')->once_update_active($arr[$request['type']]);
		exit_json_response($ret);
	}
        
	function down(array &$request, array &$response, array &$app){
        $response['shop_api'] = get_shop_api_list();
    }
    
    //允许上架
    function update_goods_onsale(array &$request, array &$response, array &$app) {

        $response = load_model('api/sys/ApiGoodsModel')->update_goods_onsale($request['type'], $request['id']);

}

	//批量允许上架
	function p_update_onsale(array &$request, array &$response, array &$app) {
		$arr = array('enable' => 1, 'disable' => 0);
		$ret = load_model('api/sys/ApiGoodsModel')->p_update_onsale($arr[$request['type']], $request['id']);
		exit_json_response($ret);
	}
    
    
        
      /**
     * @todo 删除
     */
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('api/sys/ApiGoodsModel')->do_delete($request['api_goods_id']);
        exit_json_response($ret);
    }
    
    
    
     /**
     * @todo 批量删除
     */
    function batch_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('api/sys/ApiGoodsModel')->batch_delete($request);
        exit_json_response($ret);
    }
    
     /*
     * @todo 修改平台规则编码
     */
  function update_goods_barcode(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('api_goods_sku_id', 'goods_barcode'));
        $ret = load_model('api/sys/ApiGoodsModel')->update_goods_barcode($params);
        exit_json_response($ret);
    }
	//内置自动服务  删除平台已删除的商品数据
	function do_delete_goods(array &$request, array &$response, array &$app){
		$response = load_model('api/sys/ApiGoodsModel')->do_delete_goods();
		$response['status'] = 1;
	}

}
