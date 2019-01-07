<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class sell_record_notice {
    
    function do_list(array &$request, array &$response, array &$app){
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] =isset($arr_spec1['goods_spec1'])?$arr_spec1['goods_spec1']:'' ;
        //spec2别名
        $arr2 = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr2);
        $response['goods_spec2_rename'] =isset($arr_spec2['goods_spec2'])?$arr_spec2['goods_spec2']:'' ;
        
    }
    function multi_do_list(array &$request, array &$response, array &$app){
        //spec1别名
    	$arr = array('goods_spec1');
    	$arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
    	$response['goods_spec1_rename'] =isset($arr_spec1['goods_spec1'])?$arr_spec1['goods_spec1']:'' ;
        //spec2别名
    	$arr2 = array('goods_spec2');
    	$arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr2);
    	$response['goods_spec2_rename'] =isset($arr_spec2['goods_spec2'])?$arr_spec2['goods_spec2']:'' ;
    }
    function ex_list(array &$request, array &$response, array &$app){
        
        
    } 
    function create_wave(array &$request, array &$response, array &$app){
       $response =  load_model('oms/SellRecordNoticeModel')->create_waves_record($request);
        
    }
    
    function create_wave_combine(array &$request, array &$response, array &$app){
        $request['create_type'] = 'combine';
        $response =  load_model('oms/SellRecordNoticeModel')->create_waves_record($request);
    }
    
    function create_wave_more(array &$request, array &$response, array &$app){
         $response =  load_model('oms/SellRecordNoticeModel')->create_waves_record_more($request);
    }
    
    function create_wave_more_combine(array &$request, array &$response, array &$app){
        $request['create_type'] = 'combine';
        $response =  load_model('oms/SellRecordNoticeModel')->create_waves_record_more($request);
    }

    function save_wave_strategy_name(array &$request, array &$response, array &$app){
    	$wave_strategy_name = trim($request['wave_strategy_name']);
    	if (empty($request['wave_strategy_name'])){
    		$response = array('status' => -1,'data'=> '','message' => '波次策略名称不能为空');
    	} else {
    		$is_exist_name = load_model('oms/SellRecordNoticeModel')->is_exist_name($wave_strategy_name,$request['type']);
    		if (!empty($is_exist_name)){
    			$response = array('status' => 2,'data'=> '','message' => '波次策略名称已存在是否替换？');
    		} else{
    			$response = load_model('oms/SellRecordNoticeModel')->save_wave_strategy_name($request);
    		}
    	}
    }
    //删除波次策略
    function delete_wave_strategy_name(array &$request, array &$response, array &$app){
        $wave_strategy_name = $request['wave_strategy_name'];
        $is_exist_name = load_model('oms/SellRecordNoticeModel')->is_exist_name($wave_strategy_name,$request['type']);
        if (empty($is_exist_name)){
            $response = array('status' => -1,'data'=> '','message' => '波次策略不存在');
        }else{
            $response = load_model('oms/SellRecordNoticeModel')->delete_wave_strategy_name($request);
        }
        exit_json_response($response);
    }
    
    function replace_name(array &$request, array &$response, array &$app){
    	$response = load_model('oms/SellRecordNoticeModel')->update_wave_strategy_name($request);
    }
    
    function get_waves_strategy(array &$request, array &$response, array &$app){
    	$ret = load_model('oms/SellRecordNoticeModel')->get_waves_strategy($request['type']);
    	$waves_name = array();
    	foreach ($ret as $r) {
    		$waves_name[] = $r['name'];
    	}
    	$response = $waves_name;
    }


    function get_waves_params_by_name(array &$request, array &$response, array &$app){
    	$name = $request['name'];
    	if (!$name){
    		$response = array();
    	} else {
    		$ret = load_model('oms/SellRecordNoticeModel')->is_exist_name($name,$request['type']);
    		$waves_strategy_params = $ret['condition'];
    		$response = json_decode($waves_strategy_params,true);
    	}
    }
    
    function create_sku_all(){
        load_model("oms/SellRecordNoticeModel")->create_sku_by_reocrd(); 
        echo 'ok';die;
    }
    function edit_express_code(array &$request, array &$response, array &$app){
        
    }
    function edit_express_code_action(array &$request, array &$response, array &$app){
        $response = load_model('oms/SellRecordNoticeModel')->edit_express_code($request);
        exit_json_response($response);
    }
    function edit_express_code_multi_action(array &$request, array &$response, array &$app){
        $response = load_model('oms/SellRecordNoticeModel')->edit_express_multi_code($request);
        exit_json_response($response);
    }

    /**菜鸟智能
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function cainiao_intelligent_delivery(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/SellRecordNoticeModel')->cainiao_intelligent_delivery($request['sell_record_code_list']);
        exit_json_response($ret);
    }
    function set_combine_num(array &$request, array &$response, array &$app){
        CTX()->set_session('combine_num', $request['num']);
    }
}