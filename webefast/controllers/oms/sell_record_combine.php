<?php
class sell_record_combine {

    function do_list(array &$request, array &$response, array &$app) {
        //spec别名
        $arr = array('goods_spec1','goods_spec2');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
        
        $response['sale_channel']  =  load_model('base/SaleChannelModel')->get_select();
       foreach($response['sale_channel'] as $key=>$val ){
           if($val[0]=='houtai'){
               unset($response['sale_channel'][$key]);
               break;
           }
       }

    }

    function opt_batch_combine(array &$request, array &$response, array &$app) {
		$response = load_model('oms/OrderCombineViewModel')->batch_combine_by_record_code($request['sell_record_code'],'byhand');
    }

    function cli_combine(array &$request, array &$response, array &$app) {
		load_model('oms/OrderCombineModel')->cli_combine();
		$response['status'] = 1;
    }
    
}