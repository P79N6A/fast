<?php
require_lib('util/oms_util', true);
class op_gift_strategy {
	function do_list(array & $request, array & $response, array & $app) {
                
		$response['start_time'] = date('Y-m-')."01";
	}
	function detail(array & $request, array & $response, array & $app) {
		
		$ret = array();
		if(isset($request['_id']) && $request['_id'] != ''){
			$ret = load_model('op/GiftStrategyModel')->get_by_id($request['_id']);
			$ret['data']['start_time'] =  date('Y-m-d H:i',$ret['data']['start_time']);
			$ret['data']['end_time'] =  date('Y-m-d H:i',$ret['data']['end_time']);
		}else{
			$ret['data']['strategy_code'] = load_model('op/GiftStrategyModel')->create_fast_bill_sn();
		}
		
		$response['data'] = $ret['data'];
		$response['app_scene'] = $_GET['app_scene'];
		$response['_id'] = $request['_id'];
	}
	//保存基础数据
	function do_add(array & $request, array & $response, array & $app) {
		$request['start_time'] = strtotime($request['start_time']);
		$request['end_time'] = strtotime($request['end_time']);
		$request['create_time'] = time();
		$data = get_array_vars($request, array('strategy_code', 'strategy_name','shop_code','is_once_only','is_stop_no_inv','start_time','end_time','create_time'));
		$ret = load_model('op/GiftStrategyModel')->insert($data);
		exit_json_response($ret);
	}
	//修改
	function do_edit(array &$request, array &$response, array &$app) {
		$request['start_time'] = strtotime($request['start_time']);
		$request['end_time'] = strtotime($request['end_time']);
		//print_r($request);exit;
		$data = get_array_vars($request, array('strategy_code', 'strategy_name','shop_code','is_once_only','is_stop_no_inv','start_time','end_time'));
		$ret = load_model('op/GiftStrategyModel')->update($data, $request['op_gift_strategy_id']);
		$ret['data'] = $request['op_gift_strategy_id'];
		exit_json_response($ret);
	}
	
	function view(array &$request, array &$response, array &$app) {
		if(isset($request['strategy_code']) && $request['strategy_code'] != ''){
			$ret = load_model('op/GiftStrategyModel')->get_by_code($request['strategy_code']);
		}
		$response['strategy'] = $ret['data'];
		$response['_id'] = $request['_id'];
	}
	
	function show_rule1(array &$request, array &$response, array &$app){
		
		$arr1 = array(':strategy_code' => $request['strategy_code']);
		$gift = load_model('op/GiftStrategyDetailModel')->get_gift_list($arr1);
		//print_r($gift);
		$response['gift'] = $gift;
	}
	
	//保存
   function rule1_save(array &$request, array &$response, array &$app){
   		//print_r($request);exit;
   		
   		$app['fmt'] = 'json';
   		if(!empty($request['gift'])){
   			//修改
   			$ret = load_model('op/GiftStrategyDetailModel')->update_rule1_save($request['gift'],$request['goodsbuy']);
   		}
   		
   		$response = $ret;
   }
   function do_edit_detail_one(array &$request, array &$response, array &$app){
   	   //print_r($request);exit;
   	   $data = get_array_vars($request, array('sku'));
   	   if(isset($request['op_gift_strategy_goods_id']) && $request['op_gift_strategy_goods_id'] <> ''){
   	  	 $ret = load_model('op/GiftStrategyGoodsModel')->update($data, $request['op_gift_strategy_goods_id']);
   	   }
   	   exit_json_response($ret);
   }
   //新增加规则
   function do_add_detail(array & $request, array & $response, array & $app) {
	   	$data = get_array_vars($request, array('strategy_code','type'));
	   	$ret = load_model('op/GiftStrategyDetailModel')->insert($data);
	   	exit_json_response($ret);
   }
   //添加赠品和商品
   function do_add_goods(array & $request, array & $response, array & $app) {
       
       $response = load_model('op/GiftStrategyGoodsModel')->add_goods($request);
   }
   
   
   //其他规则
   function get_other_rule(array & $request, array & $response, array & $app){
	   	$ret = load_model('op/GiftStrategyDetailModel')->get_other_rule($request['op_gift_strategy_detail_id'],$request['strategy_code'],$request['sort']);
	   	//print_r($ret);
	   	exit_json_response($ret);
   }
  //导入其他规则赠品 
  function  import_other_rule_goods(array & $request, array & $response, array & $app){
  	 
  	  $ret = load_model('op/GiftStrategyGoodsModel')->import_other_rule_goods($request['op_gift_strategy_detail_id_new'],$request['op_gift_strategy_detail_id'],$request['sort']);
  	  exit_json_response($ret);
  }
  function del_goods(array & $request, array & $response, array & $app){
  	  $ret = load_model('op/GiftStrategyGoodsModel')->del_goods('op_gift_strategy_goods_id',$request['op_gift_strategy_goods_id']);
  	  exit_json_response($ret);
  }
  function del_detail(array & $request, array & $response, array & $app){
  	 // print_r($request);exit;
  	  $ret = load_model('op/GiftStrategyDetailModel')->del_detail($request['op_gift_strategy_detail_id']);
  	  exit_json_response($ret);
  }
  
  /**
   * 启用停用
   * @param array $request
   * @param array $response
   * @param array $app
   */
  function update_active(array &$request, array &$response, array &$app) {
  	//print_r($request);exit;
  	$arr = array('enable' => 1, 'disable' => 0);
  	$ret = load_model('op/GiftStrategyModel')->update_active($arr[$request['type']], $request['id']);
  	 
  	exit_json_response($ret);
  }
  //审核
  function do_check(array &$request, array &$response, array &$app){
  		
  		$ret = load_model('op/GiftStrategyModel')->update_check('1','is_check', $request);
  		exit_json_response($ret);
  }
  
  function check_repeat(array &$request, array &$response, array &$app){

       	$response = load_model('op/GiftStrategyModel')->check_repeat($request['strategy_code']);
  }
  
  function clear_customer_data(array &$request, array &$response, array &$app){
      	$response = load_model('op/GiftStrategyCustomerModel')->clear_data($request['strategy_code']);

  }
  function customer_import(array &$request, array &$response, array &$app){
      
  }
  

  function customer_import_action(array &$request, array &$response, array &$app){
    	$app['fmt'] = 'json';

//         $file = $_FILES['fileData']['tmp_name'];
        $file = $request['url'];
        if(empty($file)){
        	$response = array(
        			'status' => 0,
        			'type' => '',
        			'msg' => "请先上传文件"
        	);
                return $response;
        }
        
   
        $ret = load_model('op/GiftStrategyCustomerModel')->import_data($file,$request['strategy_code']);
        $response = $ret;  
  }
  
    /**
     * 订单赠品策略复制
     */
    function opt_copy(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ret = load_model("op/GiftStrategyModel")->opt_copy($request['strategy_code']);
        $response = $ret;
    }
    function update_end_time(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ret = load_model("op/GiftStrategyModel")->update_end_time($request['id'],$request['new_endtime'],$request['end_time']);
        exit_json_response($ret);
    }
    function do_delete(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        //print_r($request);
        $ret = load_model("op/GiftStrategyModel")->do_delete($request['strategy_code'],$request['id']);
        $data = array(
            'user_id'=> CTX()->get_session('user_id'),
            'user_code'=>  CTX()->get_session('user_code'),
            'add_time' => date('Y-m-d H:i:s'),
            'module' => '运营',
            'operate_type'=> '删除策略',
            'yw_code' => $request['strategy_code'],
            'operate_xq' => "删除策略：{$request['strategy_code']}",
        );
        load_model('sys/OperateLogModel')->insert($data);
        $response = $ret;
    }
    //礼品策略测试
    public function test_gift_strategy(array &$request,array &$response,array &$app){
        $ret = load_model('op/GiftStrategy2Model')->is_exists($request['_id'],'op_gift_strategy_id');
        //$response['shop_code'] = isset($ret['data']['shop_code']) ? $ret['data']['shop_code'] : '';
        $response['title'] = '测试赠品策略';
        $response['record_time_start'] = date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()-259200)));
        $response['record_time_end'] = date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()+86400))-1);
    }
}
