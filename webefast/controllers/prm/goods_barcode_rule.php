<?php
/**
 * 商品控制器相关业务
 * @author dfr
 *
 */
require_lib ( 'util/web_util', true );
class Goods_barcode_rule {
    
    	function create_barcode(array & $request, array & $response, array & $app) {
            
             $ret = load_model('prm/GoodsBarcodeRuleModel')->get_list();
             $response['rule_list'] = $ret['data'];
        }
   	function do_create_barcode(array & $request, array & $response, array & $app) {
            $request['page'] = isset($request['page'])?$request['page']:1;
            $response = load_model('prm/GoodsBarcodeRuleModel')->create_barcode($request);
            
        }
}

?>
