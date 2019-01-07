<?php
require_lib ( 'util/web_util', true );
class taobao_item {
	function do_list(array & $request, array & $response, array & $app) {
		//店铺
		 $response['shop'] = $this ->get_shop();
	}
	//获取表格数据
	function do_list_js(array & $request, array & $response, array & $app){
		$ret = load_model('api/TaobaoItemModel')->get_by_page($request);
		//print_r($ret);
		exit_json_response($ret);
		
	}
        function update_barcode(array & $request, array & $response, array & $app){
            if(isset($request['shop_code'])){
                require_model("api/taobao/ApiBarcodeModel");
                //$shop_code = 'tb004';
                $mdl = new ApiBarcodeModel();
                $response = $mdl->set_api_update($request['shop_code']);  
            }

        }    
	//库存同步
	function store_synchro(){
		echo "dong";
		exit;
	}
	//店铺
	function get_shop(){
		$arr_shop = load_model('api/TaobaoItemModel')->get_shop();
		$key = 0;
		foreach ($arr_shop as $value){
			$arr_shop[$key][0] = $value['shop_code'];
			$arr_shop[$key][1] = $value['shop_name'];
			$key++;
		}
		return $arr_shop;
	}
}
