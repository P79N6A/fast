<?php
require_lib('util/oms_util', true);
class gift_strategy {
	function do_list(array & $request, array & $response, array & $app) {
                
		$response['start_time'] = date('Y-m-d', strtotime('-1 month'));
	}
	/*
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
	}*/
	//规则列表
	function rule_do_list(array & $request, array & $response, array & $app) {
	
		$ret = array();
		$ret = load_model('op/GiftStrategy2Model')->get_by_id($request['_id']);
		$response['strategy'] = $ret['data'];
		$response['_id'] = $request['_id'];
		
	}
	//启用 禁用规则
	function update_rule_enable(array & $request, array & $response, array & $app) {
                $ret = load_model('op/GiftStrategy2DetailModel')->update_active($request['status'], $request['op_gift_strategy_detail_id']);
                exit_json_response($ret);
	}
	//保存基础数据
	function do_add(array & $request, array & $response, array & $app) {
		$request['start_time'] = strtotime($request['start_time']);
		$request['end_time'] = strtotime($request['end_time']);
		$request['create_time'] = time();
		//走新的策略逻辑
        $request['strategy_new_type'] = 1;
		$data = get_array_vars($request, array('strategy_code', 'strategy_name','shop_code','time_type','is_once_only','is_continue_no_inv','combine_upshift','set_gifts_num','start_time','end_time','create_time','strategy_new_type'));
		$ret = load_model('op/GiftStrategy2Model')->insert($data);
		exit_json_response($ret);
	}
	//修改
	function do_edit(array &$request, array &$response, array &$app) {
		$request['start_time'] = strtotime($request['start_time']);
		$request['end_time'] = strtotime($request['end_time']);
		//print_r($request);exit;
		$data = get_array_vars($request, array('strategy_code', 'strategy_name','shop_code','time_type','is_once_only','is_continue_no_inv','set_gifts_num','combine_upshift','start_time','end_time'));
                $ret = load_model('op/GiftStrategy2Model')->update($data, $request['op_gift_strategy_id']);
		$ret['data'] = $request['op_gift_strategy_id'];
		exit_json_response($ret);
	}
	//策略详情
	function view(array &$request, array &$response, array &$app) {
		
		$ret = array();
		if(isset($request['_id']) && $request['_id'] != ''){
			$ret = load_model('op/GiftStrategyModel')->get_by_id($request['_id']);
			$ret['data']['start_time'] =  date('Y-m-d H:i:s',$ret['data']['start_time']);
			$ret['data']['end_time'] =  date('Y-m-d H:i:s',$ret['data']['end_time']);
			//设置的店铺
			$response['gift_shop'] = load_model('op/GiftStrategyShopModel')->get_shops_by_strategy_code($ret['data']['strategy_code']);
		}else{
			$ret['data']['strategy_code'] = load_model('op/GiftStrategy2Model')->create_fast_bill_sn();
		}
		$response['data'] = $ret['data'];
		$response['app_scene'] = $_GET['app_scene'];
		$response['_id'] = $request['_id'];
		
		$response['shop'] = load_model('base/ShopModel')->get_purview_shop();
        $response['_url'] = base64_encode('?app_act=op/op_gift_strategy/test_gift_strategy&_id='.$response['_id']);
        $response['url'] = '?app_act=op/op_gift_strategy/test_gift_strategy&_id='.$response['_id'];
		$response['can_test'] = $ret['data']['status'] == 0 && strtotime($ret['data']['start_time']) > time() ? 1 : 0;
	}       
        //删除活动商品
         function delete_gift_goods (array & $request, array & $response, array & $app) {
             $ret = load_model('op/GiftStrategy2GoodsModel')->delete_gift_goods($request['gift_goods_id'],$request['gift_detail_id'],$request['strategy_code'],$request['barcode']);
             exit_json_response($ret);
         }
        
	//规则详情
	function rule_view(array &$request, array &$response, array &$app) {
	
		$ret = load_model('op/GiftStrategy2DetailModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
		$ret1 = load_model('op/GiftStrategy2Model')->get_by_code($ret['data']['strategy_code']);
		$response['strategy'] = $ret1['data'];
		//范围
		if ($ret['data']['range_type'] == 0){
			$ret = load_model('op/GiftStrategyRangeModel')->get_by_detail_id($request['_id']);
			$response['range'] = $ret['data'];
		}
		
	
	}
        
        //规则详情
	function ranking_rule_view(array &$request, array &$response, array &$app) {
            $ret = load_model('op/GiftStrategy2DetailModel')->get_by_id($request['_id']);
            $response['data'] = $ret['data'];
            $ret1 = load_model('op/GiftStrategy2Model')->get_by_code($ret['data']['strategy_code']);
            $response['strategy'] = $ret1['data'];
            //范围
            if ($ret['data']['range_type'] == 0){
                    $ret = load_model('op/GiftStrategyRangeModel')->get_by_detail_id($request['_id']);
                    $response['range'] = $ret['data'];
                    $response['last_range'] = load_model('op/GiftStrategyRangeModel')->get_last_range_by_id($request['_id']);
            }
	}
        
	//赠品商品详情页面
	function gift_goods(array &$request, array &$response, array &$app) {
		$ret = array();
	
		$ret = load_model('op/GiftStrategy2DetailModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
		$ret = load_model('op/GiftStrategy2Model')->get_by_code($ret['data']['strategy_code']);
		$response['strategy'] = $ret['data'];
		//是否有范围区间
		$response['is_range'] = 1;
		//买送且全场买送
		if ($response['data']['type'] == 1 && $response['data']['goods_condition'] == 2 ){
			$response['range'][] = array('id'=>0,'give_way' => $response['data']['give_way'],'gift_num'=>$response['data']['gift_num']);
			$response['is_range'] = 0;
		} else {
			//范围
			if ($response['data']['range_type'] == 0){
				$ret = load_model('op/GiftStrategyRangeModel')->get_by_detail_id($request['_id']);
				$response['range'] = $ret['data'];
				$response['is_range'] = 1;
			} else {
				//倍增
				$response['is_range'] = 0;
				$response['range'][] = array('id'=>0,'give_way' => $response['data']['give_way'],'gift_num'=>$response['data']['gift_num']);
			}
		}
		
		//赠品商品
		$ret = load_model('op/GiftStrategy2GoodsModel')->get_by_detail_id($request['_id'],"",1);
		$response['gift_goods'] = $ret;
	
	}
        
    function ranking_gift_goods(array &$request, array &$response, array &$app) {
        $ret = array();
        $ranking_row = load_model('op/GiftStrategy2DetailModel')->get_by_id($request['_id']);
        $response['data'] = $ranking_row['data'];
        $rank_record = load_model('op/GiftStrategy2Model')->get_by_code($ranking_row['data']['strategy_code']);
        $response['strategy'] = $rank_record['data'];
        $ret = load_model('op/GiftStrategyRangeModel')->get_by_detail_id($request['_id']);
        $response['range'] = $ret['data'];
        //赠品商品
        $ret = load_model('op/GiftStrategy2GoodsModel')->get_by_detail_id($request['_id'], "", 1);
        $response['gift_goods'] = $ret;
    }

    //活动商品详情页面
	function rule_goods(array &$request, array &$response, array &$app) {
		$ret = array();
	
		$ret = load_model('op/GiftStrategy2DetailModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
		$ret = load_model('op/GiftStrategy2Model')->get_by_code($ret['data']['strategy_code']);
		$response['strategy'] = $ret['data'];
	
	}
	//定向会员详情页面
	function rule_customer(array &$request, array &$response, array &$app) {
		$ret = array();
	
		$ret = load_model('op/GiftStrategy2DetailModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
		$ret = load_model('op/GiftStrategy2Model')->get_by_code($ret['data']['strategy_code']);
		$response['strategy'] = $ret['data'];
	
	}
	
   //新增加规则
   function do_add_detail(array & $request, array & $response, array & $app) {
	   	$data = get_array_vars($request, array('strategy_code','type'));
	   	$ret = load_model('op/GiftStrategy2DetailModel')->add($data);
	   	exit_json_response($ret);
   }
   //编辑规则
   function do_edit_detail(array & $request, array & $response, array & $app) {
   	$data = get_exist_vars($request, array('op_gift_strategy_detail_id','name','sort','level','is_contain_delivery_money','doubled','range_type','is_mutex','buy_num','goods_condition','is_goods_money','ranking_time_type','ranking_hour','type','money_min','status'));
        if(isset($data['type']) && $data['type'] == 2){
            $ret = load_model('op/GiftStrategy2DetailModel')->ranking_edit($data);
        } else {
            $ret = load_model('op/GiftStrategy2DetailModel')->edit($data);
        }
        
   	exit_json_response($ret);
   }
   
  function del_detail(array & $request, array & $response, array & $app){
  	 // print_r($request);exit;
  	  $ret = load_model('op/GiftStrategyDetailModel')->del_detail($request['op_gift_strategy_detail_id']);
  	  exit_json_response($ret);
  }
  //添加金额/数量范围
  function add_range(array & $request, array & $response, array & $app){
	  $data = get_array_vars($request, array('op_gift_strategy_detail_id','range_start','range_end','strategy_new_type'));
	  $ret = load_model('op/GiftStrategyRangeModel')->add($data);
	  exit_json_response($ret);
  }
  //删除金额/数量范围
  function delete_range(array & $request, array & $response, array & $app){
  	$ret = load_model('op/GiftStrategyRangeModel')->remove($request['range_id']);
  	exit_json_response($ret);
  }
  
  //规格下的所有金额/商品范围
  function rule_range(array & $request, array & $response, array & $app){
  	$ret = load_model('op/GiftStrategyRangeModel')->get_by_detail_id($request['op_gift_strategy_detail_id']);
        $data = load_model('op/GiftStrategy2DetailModel')->get_by_id($request['op_gift_strategy_detail_id']);
  	$ret['type'] = $data['data']['type'];
        exit_json_response($ret);
  }
  function check_gift(array & $request, array & $response, array & $app){
      //校验是否已经设置赠品
  	$ret = load_model('op/GiftStrategy2GoodsModel')->check_exist_gift($request['op_gift_strategy_detail_id']);
  	exit_json_response($ret);
  }
  
  //添加赠品和商品
  function do_add_goods(array & $request, array & $response, array & $app) {
  	 
  	$response = load_model('op/GiftStrategy2GoodsModel')->add_goods($request);
  }
  //删除赠品/活动商品(单个)
  function del_goods(array & $request, array & $response, array & $app){
  	$ret = load_model('op/GiftStrategy2GoodsModel')->del_goods('op_gift_strategy_goods_id',$request['op_gift_strategy_goods_id']);
  	exit_json_response($ret);
  }
  //删除赠品/活动商品(一键)
  function del_goods_batch(array & $request, array & $response, array & $app){
  	$ret = load_model('op/GiftStrategy2GoodsModel')->del_batch($request);
  	exit_json_response($ret);
  }
  //修改赠品方式 固定、随机
  function upate_give_way(array & $request, array & $response, array & $app){
  	
  	$data = get_array_vars($request, array('op_gift_strategy_detail_id','range_id','gift_num','range_type','give_way','goods_condition'));
  	$ret = load_model('op/GiftStrategyRangeModel')->edit($data);
  	exit_json_response($ret);
  }
  //活动商品导入
  function rule_goods_import(array &$request, array &$response, array &$app){
  
  }
  //会员导入
  function rule_goods_import_action(array &$request, array &$response, array &$app){
  	$app['fmt'] = 'json';
  	$file = $request['url'];
  	if(empty($file)){
  		$response = array(
  				'status' => 0,
  				'type' => '',
  				'msg' => "请先上传文件"
  		);
  		return $response;
  	}
  
  	$ret = load_model('op/GiftStrategy2GoodsModel')->import_rule_goods($file,$request['strategy_code'],$request['op_gift_strategy_detail_id']);
  	$response = $ret;
  }
  //指定会员导入
  function customer_import(array &$request, array &$response, array &$app){
  
  }
  
  //会员导入
  function customer_import_action(array &$request, array &$response, array &$app){
  	$app['fmt'] = 'json';
  
  	$file = $request['url'];
  	if(empty($file)){
  		$response = array(
  				'status' => 0,
  				'type' => '',
  				'msg' => "请先上传文件"
  		);
  		return $response;
  	}
  
  	 
  	$ret = load_model('op/GiftStrategy2CustomerModel')->import_data($file,$request['strategy_code'],$request['op_gift_strategy_detail_id']);
  	$response = $ret;
  }
  //一键清空会员
  function clear_customer_data(array &$request, array &$response, array &$app){
  	$response = load_model('op/GiftStrategy2CustomerModel')->clear_data($request['op_gift_strategy_detail_id']);
  
  }
  function is_gift_strategy_goods(array &$request, array &$response, array &$app){
            $ret = load_model('op/GiftStrategyGoodsModel')->get_goods_by_range_id($request['op_gift_strategy_detail_id'],$request['type']);
        
//        if($request['type'] == 1) {
//            //买赠
//            $ret2 = load_model('op/GiftStrategyGoodsModel')->get_goods_by_range_id($request['op_gift_strategy_detail_id'],0);
//            if($ret2['status'] == -1 || $ret['status'] == -1) {
//                $ret['status'] = -1;
//                $ret['message'] = '赠品商品或活动商品没有设置';
//            }
//        }
        exit_json_response($ret);
  }
  function edit_num(array &$request, array &$response, array &$app){
      $ret = load_model('op/GiftStrategy2DetailModel')->edit_num($request['id'],$request['num'],$request['goods_id'],$request['shop']);
      exit_json_response($ret);
  }
  function is_set_gift_num(array &$request, array &$response, array &$app){
      $ret = load_model('op/GiftStrategy2DetailModel')->is_set_gift_num($request['op_gift_strategy_detail_id'],$request['set_gift']);
      exit_json_response($ret);
  }
  public function get_strategy_log(array &$request,array &$response,array &$app){

  }
}
