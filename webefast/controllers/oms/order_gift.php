<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms/TaobaoRecordModel', true);
require_model('oms/SellRecordFixModel', true);
require_model('oms/SellRecordOptModel', true);

class order_gift {
    

    //平台订单详情
    function td_view(array &$request, array &$response, array &$app) {
    	$ret = load_model('oms/ApiOrderModel')->get_by_id($request['id']);
    	$response['record'] = $ret['data'];
    	if(isset($ret['data']) && !empty($ret['data'])){
    		$detail_list  = load_model('oms/ApiOrderDetailModel')->get_by_field_all('tid',$ret['data']['tid'], $select = "*");
    		//print_r($detail_list);
    		$mingxi = '';
    		foreach ($detail_list as  $value ){
    			$mingxi .= $value['detail_id']."_";
    		}
    		$mingxi = substr($mingxi,0,strlen($mingxi)-1);
    		$response['record']['mingxi'] = $mingxi;
    		$response['record']['detail_list'] = $detail_list;
    	}

    	//取得国家数据
    	$response['area']['country'] = load_model('base/TaobaoAreaModel')->get_area('0');
    	$response['area']['province'] = array();
    	$area_ids = load_model('base/TaobaoAreaModel')->get_by_field_all($response['record']['receiver_country'],$response['record']['receiver_province'],$response['record']['receiver_city'],$response['record']['receiver_district'],$response['record']['receiver_street']);
    	$response['record']['ids'] = $area_ids;
    	$response['area']['province'] = load_model('base/TaobaoAreaModel')->get_area($area_ids['country_id']);
    	$response['area']['city'] = load_model('base/TaobaoAreaModel')->get_area($area_ids['province']);
    	$response['area']['district'] = load_model('base/TaobaoAreaModel')->get_area($area_ids['city']);
    	$response['area']['street'] = load_model('base/TaobaoAreaModel')->get_area($area_ids['district']);;



    }

    //平台订单转单
    function td_tran(array &$request, array &$response, array &$app) {
//        $app['fmt'] = 'json';
//        $m = new SellRecordFixModel();
//        $response = $m->fix_record($request['sell_record_code']);
		$sql = "select tid from api_order where id=".(int)$request['api_order_id'];
		$tid = ctx()->db->getOne($sql);
        $response = load_model("oms/TranslateOrderModel")->translate_order($tid);
		if ($response['status'] >0){
			$response['status'] = 1;
			$response['message'] = '转单成功';
		}
    }

    //平台订单标记已转单
    function td_traned(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
		//td_traned($ids,$is_change=1) 
		$is_change = isset($request['is_change'])?$request['is_change']:1;
		$response = load_model('oms/ApiOrderModel')->td_traned($request['id'],$is_change);
    }

    //订单列表
    function do_list(array &$request, array &$response, array &$app) {
        $this->get_spec_rename($response);
    }
	
	

    //详情
    function view(array &$request, array &$response, array &$app) {
        $m = new SellRecordModel();
        $response['record'] = $m->get_record_by_code($request['sell_record_code']);
    }


    //新增
    function add(array &$request, array &$response, array &$app) {

    	$id_arr = explode(",",$request['id']);
        $response['count'] = count($id_arr);
        $response['id'] = $request['id'];
        
    }

    //新增
    function add_action(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        
        $response = load_model('oms/OrderGiftModel')->add_order_gift($request);
        
    	
				
    }
    
    
    //删除
    function delete(array &$request, array &$response, array &$app) {

    	$id_arr = explode(",",$request['id']);
        $response['count'] = count($id_arr);
        $response['id'] = $request['id'];
        
    }
    
    //删除
    function delete_action(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        
        $tip = "";
        
        $sql = "select status,r2.sku from base_goods r1 INNER JOIN goods_sku r2 on r1.goods_code = r2.goods_code where r2.barcode= '".$request['sku']."' ";

        $goods_row = ctx()->db->getRow($sql);
        
        if(empty($goods_row) || $goods_row['status'] == 1){
            $response = array('status'=>-1, 'message'=>'');
        }else{
            $id_arr = explode(',', $request['id']);

    		foreach($id_arr as $id){
				
			    $sql = "select sell_record_detail_id,is_gift from oms_sell_record_detail where sell_record_code= '".$id."' and sku = '".$goods_row['sku']."' and is_gift = 1";
                $info = ctx()->db->getRow($sql);
                $is_gift = 1;
    			$sell_record_detail_id = $info['sell_record_detail_id'];
				if(empty($sell_record_detail_id) || $info['is_gift'] == 0){
					$response = array('status'=>-2, 'message'=>'');
				}else{
				    $ret = load_model('oms/SellRecordOptModel')->opt_delete_detail($id, $sell_record_detail_id,$is_gift);
                    $response = array('status'=>1, 'message'=>'');
				}
				
			}
            
        }   
    	
    }

    //读取取详情各部分
    function component(array &$request, array &$response, array &$app) {
        $types = $request['components'];
        if($request['type'] != 'all'){
            $types = array($request['type']);
        }


        $mdlSellRecord = new SellRecordModel();

        //读取订单
        $response = $mdlSellRecord->component($request['sell_record_code'], $types);
        
        
        
        
        $response['add_his'] = $request['add_his'];
        if(empty($response['record'])){
            die(json_encode(array()));
        }
		
        $result = array();
        $arr = array();
        foreach($types as $type){
            ob_start();
            $app['scene'] = $request['opt'];
            $path = get_tpl_path('oms/sell_record/get_'.$type);
            //echo $path;
            include $path;
            $ret = ob_get_contents();
            ob_end_clean();
            $arr[$type] = $ret;
        }

        die(json_encode($arr));
    }
    //保存收货地址
    function save_component_ship(array &$request, array &$response, array &$app) {
    	$app['fmt'] = 'json';
    
    	$mdlSellRecord = new SellRecordModel();
    	$response = $mdlSellRecord->save_component_ship($request['sell_record_code'], $request['type'],$request['data']);
    }
    //保存详情各部分
    function save_component(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $mdlSellRecord = new SellRecordModel();
        $response = $mdlSellRecord->save_component($request['sell_record_code'], $request['type'],$request['data']);
    }

    function edit_express_no(array &$request, array &$response, array &$app){
        $m = new SellRecordModel();
        $response['sell_record_list'] = $m->get_record_list_by_ids(explode(',', $request['sell_record_code_list']));
        $response['express_arr'] = array();
        foreach($response['sell_record_list'] as $record) {
            $response['express_arr'][$record['express_code']] = $record['express_code'];
        }
    }

    function edit_express_no_action(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';

        $check_the_no = empty($request['check_the_no']) ? false : true;
        $m = new SellRecordOptModel();
        $err = '';
        foreach($request['express_no'] as $id => $no){
            $s = $m->edit_express_no($id, $no, $check_the_no);
            if($s['status'] != 1){
                $err .= " ". $s['message'];
            }
        }
        if(empty($err)){
            $response = array('status'=>1, 'message'=>'更新成功', 'data'=>array());
        } else {
            $response = array('status'=>-1, 'message'=>$err, 'data'=>array());
        }

    }

    function next_express_no(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $m = new SellRecordModel();

        if(!empty($request['check_the_no'])){
            $s = $m->check_express_no($request['express_code'], $request['express_no']);
            if($s == false){
                return $response = array('status'=>-1, 'message'=>'快递单号不合法', 'data'=>array());
            }
        }

        $data = array();
        $data[0] = $m->get_next_express_no($request['express_no'], $request['express_code']);
        for($i = 1; $i < $request['rows']; $i++){
            $data[] = $m->get_next_express_no($data[$i-1], $request['express_code']);
        }

        $response = array('status'=>1, 'message'=>'', 'data'=>$data);
    }

    function edit_express(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->edit_express($request['sell_record_code'], array('express_code'=>$request['express_code'], 'express_no'=>$request['express_no']));
    }

    function edit_express_code(array &$request, array &$response, array &$app){

    }

    function edit_store_code(array &$request, array &$response, array &$app){

    }

    function cancel_all(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $err = "";
        foreach($request['sell_record_id_list'] as $code){
            $r = $mdl->opt_cancel($code,1);
            if($r['status'] != '1') $err .= '作废失败('.$code.'): '.$r['message']."\n";
        }
        if(!empty($err)){
            $response = array('status'=>-1, 'message'=>$err);
        } else {
            $response = array('status'=>1, 'message'=>'作废成功');
        }
    }

    function edit_store_remark(array &$request, array &$response, array &$app){

    }

    function edit_express_code_action(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $msg = '';
        $mdl = new SellRecordOptModel();
        //$okk = '';
        $err = '';
        foreach($request['sell_record_code_list'] as $id) {
            $ret = $mdl->edit_express_code($id, $request['express_code'],1);
            //$msg .= $id.': '.($ret['status'] == 1 ? '更新成功' : $ret['message'])."\n";
            if($ret['status']==1){
                $mdl->add_action($id, "修改配送方式", "批量修改");
                //$okk .= "订单: ".$id.': 更新成功<br>';
            } else {
                $err .= "订单: ".$id.': 更新失败('.$ret['message'].")<br>";
            }
            $m = new SellRecordModel();
            $m->update_express($id);
        }

        //$response = array('status'=>1, 'message'=>$msg);
        if(!empty($err)){
            $response = array('status'=>-1, 'message'=>$err);
        } else {
            $response = array('status'=>1, 'message'=>'更新成功');
        }
    }

    function edit_store_code_action(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $err = '';
        $mdl = new SellRecordOptModel();
        foreach($request['sell_record_code_list'] as $id) {
            $ret = $mdl->save_component($id, 'store_code', array('store_code' => $request['store_code']));
            if($ret['status'] < 1){
                $err .= $id.': '.$ret['message']."<br>";
                continue;
            }
            $mdl->add_action($id, "修改发货仓库", "批量修改");
            //$msg .= $id.": 成功\n";
        }

        if(!empty($err)){
            $response = array('status'=>-1, 'message'=>$err);
        } else {
            $response = array('status'=>1, 'message'=>'更新成功');
        }
    }

    function edit_store_remark_action(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $msg = '';
        $mdl = new SellRecordOptModel();
        foreach($request['sell_record_code_list'] as $id) {
            $ret = $mdl->save_component($id, 'store_remark', array('store_remark' => $request['store_remark']));
            $msg .= $id.': '.($ret['status'] == 1 ? '成功' : $ret['message'])."\n";
            if($ret['status']==1){
                $mdl->add_action($id, "修改仓库留言", "批量修改");
            }
        }

        $response = array('status'=>1, 'message'=>$msg);
    }

    function pay(array &$request, array &$response, array &$app){
        $mdl = new SellRecordModel();
        $response['record'] = $mdl->get_record_by_code($request['sell_record_code']);
    }

    function opt_pay(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_pay($request['sell_record_code'],$request['paid_money']);
    }

    function send(array &$request, array &$response, array &$app){
        $mdl = new SellRecordModel();
        $response['record'] = $mdl->get_record_by_code($request['sell_record_code']);
    }

    function opt_send(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_send($request['sell_record_code'], $request);
    }

    //详情操作
    function opt(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $mdlSellRecord = new SellRecordOptModel();
        $func = $request['type'];
        if($func == 'opt_pay'){
            $response = $mdlSellRecord->$func($request['sell_record_code'],$request['paid_money']);
        }else{
            $response = $mdlSellRecord->$func($request['sell_record_code']);
        }
        //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';die;
    }

    //详情操作
    function opt_batch(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $msgSuccess = '';
        $msgFaild = '';
        $mdlSellRecord = new SellRecordOptModel();
        foreach($request['sell_record_code_list'] as $code){
            if(empty($code)) continue;
            if($request['type'] != 'opt_lock' && $request['type'] != 'opt_unlock') {
                //执行前先锁定
                $unlock = false;
                $record = $mdlSellRecord->get_record_by_code($code);
                if ($record['is_lock'] == '0') {
                    $lock_ret = $mdlSellRecord->opt_lock($code);
                    if ($lock_ret['status'] != '1') {
                        $msgFaild .= $code . ',';
                        continue;
                    } else {
                        $unlock = true;
                    }
                }
            }

            //执行时
            $func = $request['type'];
            $ret = array();
            if($func == 'opt_pay'){
                $ret = $mdlSellRecord->$func($code,$record['payable_money'],$request);
            }else{
                $ret = $mdlSellRecord->$func($code,$request);
            }
//            $record = $mdlSellRecord->get_record_by_id($id);
//            $code = isset($record['record_code']) ? $record['record_code'] : '';
            if($ret['status'] == '1'){
                $msgSuccess .= $code . '  执行成功,';
            } else {
                $msgFaild .= $code . '  '.$ret['message'].',';
            }
            //执行后要解锁（执行前是锁定的执行后不需解锁）
            if  ($request['type'] != 'opt_lock' && $request['type'] != 'opt_unlock') {
                if ($unlock) {
                    $lock_ret = $mdlSellRecord->opt_unlock($code);
                }
            }
        }

        $msg = '';
        if(!empty($msgSuccess)){
            $msg .= sprintf("订单: %s <br>", rtrim($msgSuccess, ','));
        }
        if(!empty($msgFaild)){
            $msg .= sprintf("订单: %s <br>", rtrim($msgFaild, ','));
        }

        $response = array('status'=>1, 'message'=>$msg);
    }

    //读取详情按钮权限
    function btn_check(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $response = array();
        $mdlSellRecord = new SellRecordOptModel();
        $next_opt = $mdlSellRecord->btn_nav($request['sell_record_code']);
        $response['next_opt'] = $next_opt['data'];
		
        $record = $mdlSellRecord->get_record_by_code($request['sell_record_code']);
        $detail = $mdlSellRecord->get_detail_by_sell_record_code($request['sell_record_code']);
        $sys_user = $mdlSellRecord->sys_user();
	
        foreach ($request['fields'] as $key=>&$status) {
            $func = $key.'_check';
			if($func == 'opt_send_check' ){
				$s = $mdlSellRecord->$func($record, $detail, $sys_user,'handwork_send');
			}else{
            	$s = $mdlSellRecord->$func($record, $detail, $sys_user);
			}
            $response['comp'][$key]['status'] = $s['status'] == 1 ? 1 : 0;
            $response['comp'][$key]['message'] = (string)$s['message'];
        }
        $ret = $mdlSellRecord->opt_problem_check($record, $detail, $sys_user);
        
//      echo '<hr/>$retxx<xmp>'.var_export($ret,true).'</xmp>';die;
    }

    //规格
    function spec_list_by_goods(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new SellRecordModel();
        $response = $mdl->spec_list_by_goods($request['sell_record_code'], $request['goods_code']);
    }

    //新增明细
    function opt_new_detail(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_new_detail($request['sell_record_code'], $request['sku'], $request['num'], $request['sum_money']);
    }

    //新增明细
    function opt_new_multi_detail(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_new_multi_detail($request);
    }

    //保存明细
    function opt_save_detail(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_save_detail($request['sell_record_code'], $request['sell_record_detail_id'], $request['num'], $request['avg_money'], $request['deal_code']);
    }

    //删除明细
    function opt_delete_detail(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_delete_detail($request['sell_record_code'], $request['sell_record_detail_id']);
    }



    function download(array &$request, array &$response, array &$app){
        $response['arr_shop'] = load_model('base/ShopModel')->get_list();
    }

    function download_action(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';

        $request['created_min'] = $request['created_min'] . ' 00:00:00';
        $request['created_max'] = $request['created_max'] . ' 23:59:59';

        $m = new TaobaoRecordModel();
        $response['down'] = $m->download_cloud($request);

        //TODO: 转单
        $response['tran'] = $m->transfer($request);
    }

	/**
	 * 标记订单单已打印
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function mark_sell_record_print(array & $request,array & $response,array & $app){
		$sell_record_codes = $request['record_ids'];
		$sell_record_code_arr = explode(',', $sell_record_codes);
		foreach($sell_record_code_arr as $record_id) {
			CTX()->db->update("oms_sell_record",array("is_print_sellrecord"=>1),array('sell_record_code'=>$record_id));
		}

		$app['fmt']='json';
	}

    //发货回写
    function delivery_send(array & $request,array & $response,array & $app){
        $app['fmt'] = 'json';
        $params = array();
        $params['record_code']  = $request['record_code'];
        $params['user'] = CTX()->get_session('user_id');
        $response = load_model('oms/order_shipping/OrderShippingMgrModel')->send('OrderShippingTaobaoModel',$params);
    }

    //刷新商家备注
    function seller_remark_flush(array & $request,array & $response,array & $app){
        $app['fmt'] = 'json';
        $params = array();
        $record_code  = $request['record_code'];
        $response = load_model('oms/SellRecordModel')->seller_remark_flush($record_code);
        //echo '<hr/>response<xmp>'.var_export($response,true).'</xmp>';
    }

    //上传商家备注
    function seller_remark_upload(array & $request,array & $response,array & $app){
        $app['fmt'] = 'json';
        $params = array();
        $record_code  = $request['record_code'];
        $seller_remark = $request['seller_remark'];
        $response = load_model('oms/SellRecordModel')->seller_remark_upload($record_code,$seller_remark);
        //echo '<hr/>response<xmp>'.var_export($response,true).'</xmp>';
    }

    //刷新客户留言
    function buyer_remark_flush(array & $request,array & $response,array & $app){
        $app['fmt'] = 'json';
        $params = array();
        $record_code  = $request['record_code'];
        $response = load_model('oms/SellRecordModel')->seller_remark_flush($record_code);
        //echo '<hr/>response<xmp>'.var_export($response,true).'</xmp>';
    }

    //实际批次锁定情况
    function lock_detail(array & $request,array & $response,array & $app){
        $params['p_detail_id'] = $request['sell_record_detail_id'];
        $params['sku'] = $request['sku'];
        $params['occupy_type'] = '1';
        $response=load_model('oms/SellRecordLofModel')->get_list_by_params($params,true);
        $this->get_spec_rename($response);
    }

    //问题订单列表
    function question_list(array & $request,array & $response,array & $app){
        $response['problem_type'] = ds_get_select('problem_type');
        foreach($response['problem_type'] as $key=> &$value){
            $value['num'] = load_model("oms/SellRecordModel")->get_count_by_problem_type($value['question_label_code']);
        }
        $response['operate']['return_normal'] = "?app_act=oms/sell_record/return_normal&app_fmt=json";
        $this->get_spec_rename($response);
    }

    //通过record_code获取子订单详情
    function get_detail_list_by_sell_record_code(array & $request,array & $response,array & $app){
        $data = load_model("oms/SellRecordModel")->get_detail_by_sell_record_code($request['sell_record_code'],1);
        $response = array('rows'=>$data);
    }

    //缺货订单列表
    function short_list(array & $request,array & $response,array & $app){
        $response['operate']['remove_short'] = "?app_act=oms/sell_record/remove_short&app_fmt=json";
        $response['operate']['splite'] = "?app_act=oms/sell_record/split&app_fmt=json";
        $this->get_spec_rename($response);
    }

    //合并订单列表
    function merge_list(array & $request,array & $response,array & $app){

    }

    //已发货订单列表
    function shipped_list(array & $request,array & $response,array & $app){


    }

    //已发货订单详情
    function get_detail_by_sell_record_code(array &$request, array &$response, array &$app){
        $sell_record_code = $request['sell_record_code'];
        $result = load_model("oms/SellRecordModel")->get_row(array("sell_record_code"=>$sell_record_code));
        $response['data'] = $result['data'];
        $response['detail'] = load_model("oms/SellRecordModel")->get_detail_by_sell_record_code($sell_record_code);
        $this->get_spec_rename($response);
    }


    //缺货单解除缺货
    function remove_short(array &$request, array &$response, array &$app){
        $response = load_model("oms/SellRecordModel")->remove_short($request['sell_record_code']);
    }

    //订单拆分
    function split(array &$request, array &$response, array &$app){
        $return = array();
        switch($request['mode']){
            case '0':$return = load_model("oms/OrderSplitModel")->split_a_key();
                break;
            case '1':$return = load_model("oms/OrderSplitModel")->split_group($request['sell_record_code']);
                break;
            case '2':$return = load_model("oms/OrderSplitModel")->split_short($request['sell_record_code']);
                break;
            case '3':$return = load_model("oms/OrderSplitModel")->split_presale($request['sell_record_code']);
                break;
            default :$return = array("status"=>1,"data"=>'',"message"=>'操作失败');
        }
        $response = $return;
    }

    //订单复制
    function opt_copy(array &$request, array &$response, array &$app){
        $ret = load_model("oms/SellRecordOptModel")->opt_copy($request['sell_record_code']);
        $response  = $ret;
    }

    //生成退单界面
    function create_return_form(array &$request, array &$response, array &$app){
        $sell_record_code = $request['sell_record_code'];
        $response['detail_list'] = array();
        $response['record'] = load_model("oms/SellRecordModel")->get_record_by_code($sell_record_code);
        if(!empty($response['record'])){
            if(($response['record']['shipping_status']=='4')){
                if($response['record']['pay_status']=='0'){
                    $response['record']['return_type'] = '1';
                }else{
                    $response['record']['return_type'] = '2';
                }
            }
        }
        $response['detail_list'] = load_model("oms/SellRecordModel")->get_detail_by_sell_record_code($sell_record_code,1,1);
        $ret = load_model('oms/SellReturnOptModel')->get_mx_return_info($sell_record_code);
        $return_mx = $ret['data'];
        foreach($response['detail_list'] as $ks=>$row){
	        $_find_row = isset($return_mx[$ks]) ? $return_mx[$ks] : null;
	        if (empty($_find_row)){
		        //已退数量
		        $response['detail_list'][$ks]['return_num'] = 0;
		        //可退数量
		        $response['detail_list'][$ks]['returnable_num'] = 0;
	        }else{
		        $returnable_num = $row['num'] - $_find_row['recv_num'];
		        $returnable_num = $returnable_num > 0 ? $returnable_num : 0;
	        	$response['detail_list'][$ks]['return_num'] = $_find_row['recv_num'];
	        	$response['detail_list'][$ks]['returnable_num'] = $returnable_num;
	        }
        }
        $this->get_spec_rename($response);

    }

    //生成退货单
    function create_return(array &$request, array &$response, array &$app) {
        $sell_record_code = $request['sell_record_code'];
        $params_info_mx = array();

        if (isset($request['mx'])){
	        foreach ($request['mx'] as $key => $value) {
	            $temp['deal_code'] = $value['deal_code'];
	            $temp['sku'] = $value['sku'];
	            $temp['return_num'] = $value['return_num'];
	            $temp['avg_money'] = $value['avg_money'];
	            $params_info_mx[] = $temp;
	        }
        }

        $params_info = array();
        $params_info['mx'] = $params_info_mx;
//        $params_info['store_code'] = $request['return_store_code'];
        $params_info['return_type'] = $request['return_type'];
        $params_info['adjust_money'] = isset($request['adjust_money']) ? $request['adjust_money'] : 0;
        $params_info['seller_express_money'] = isset($request['seller_express_money']) ? $request['seller_express_money'] : 0;
        $params_info['compensate_money'] = isset($request['compensate_money']) ? $request['compensate_money'] : 0;
        $params_info['return_reason_code'] = isset($request['return_reason_code']) ? $request['return_reason_code'] : '';
        $params_info['return_remark'] = isset($request['return_remark']) ? $request['return_remark'] : '';
        $params_info['return_buyer_memo'] = isset($request['return_buyer_memo']) ? $request['return_buyer_memo'] : '';
        $params_info['return_pay_code'] = isset($request['return_pay_code']) ? $request['return_pay_code'] : '';
        $params_info['return_express_code'] = isset($request['return_express_code']) ? $request['return_express_code'] : '';
        $params_info['return_express_no'] = isset($request['return_express_no']) ? $request['return_express_no'] : '';
        $params_info['is_compensate'] = isset($request['is_compensate']) ? $request['is_compensate'] : 0;
        $params_info['is_package_out_stock'] = isset($request['is_package_out_stock']) ? $request['is_package_out_stock'] : 0;
        $params_info['sell_record_checkpay_status'] = isset($request['sell_record_checkpay_status']) ? $request['sell_record_checkpay_status'] : 'unpay';

        require_model('oms/SellReturnOptModel');
        $obj = new SellReturnOptModel();
        $response = $obj->create_return($params_info, $sell_record_code,$request['return_type'],$request['return_store_code']);
    }

    function pending_list(array &$request, array &$response, array &$app){
        $this->get_spec_rename($response);
    }

    function pending(array &$request, array &$response, array &$app){
        if(isset($request['sell_record_code_list'])){
            $request['sell_record_code'] = json_encode(explode(',', $request['sell_record_code_list']));
        }
    }

    function opt_pending(array &$request, array &$response, array &$app){
        $ret=array();
        if(is_array($request['sell_record_code'])){
            $msg = '';
            foreach($request['sell_record_code'] as $code){
                $ret_sub = load_model("oms/SellRecordOptModel")->opt_pending($code,$request['is_pending_code'],$request['is_pending_memo'],$request['is_pending_time'],$request);
                $msg .= $code.': '.($ret_sub['status'] == 1 ? '成功' : $ret_sub['message'])."\n";
            }
            $ret = array('status'=>1,'message'=>$msg);
        }else{
            $ret = load_model("oms/SellRecordOptModel")->opt_pending($request['sell_record_code'],$request['is_pending_code'],$request['is_pending_memo'],$request['is_pending_time'],$request);
        }
        $response  = $ret;
    }

    function opt_unpending(array &$request, array &$response, array &$app){
        $ret = load_model("oms/SellRecordOptModel")->opt_unpending($request['sell_record_code']);
        $response  = $ret;
    }

    function problem(array &$request, array &$response, array &$app){

    }

    function opt_problem(array &$request, array &$response, array &$app){
        $ret = load_model("oms/SellRecordOptModel")->opt_problem($request['sell_record_code'],$request['problem_code'],$request);
        $response  = $ret;
    }

    function opt_unproblem(array &$request, array &$response, array &$app){
        $ret = load_model("oms/SellRecordOptModel")->opt_unproblem($request['sell_record_code']);
        $response  = $ret;
    }

    private function get_spec_rename(array &$response){
        //spec别名
        $arr = array('goods_spec1','goods_spec2');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
    }

    function problem_list(array &$request, array &$response, array &$app){
        $response = load_model("oms/SellRecordModel")->get_record_by_code($request['sell_record_code']);
    }

    function a_key_confirm(array &$request, array &$response, array &$app) {
        //标识此任务类型的唯一CODE
        /*
        $task_data['code'] = 'oms_a_key_confirm';
        $task_data['start_time'] = time();

        $request['app_fmt'] = 'json';
        $request['app_act'] = 'oms/sell_record/start_confirm';
        $request['id'] = 100;

        $task_data['request'] = $request;

        $ret = load_model('common/TaskModel')->save_task($task_data);

        $task_id = load_model('common/TaskModel')-> get_task_id ($request);

        $response = load_model('common/TaskModel')->save_log($task_id, "开始一键确认");*/

        $response = load_model('oms/SellRecordModel')->a_key_confirm_create_task();
    }

    function start_confirm(array &$request, array &$response, array &$app){
        $response = load_model("oms/SellRecordModel")->a_key_confirm($request);
    }

    function get_deliver_record_ids(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $mdl = new SellRecordModel();
        $response = $mdl->get_deliver_record_ids($request['record_ids']);
    }

    function import(array &$request, array &$response, array &$app){

    }

    function import_action(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        //var_dump($request);
        require_once ROOT_PATH.'lib/PHPExcel.php';
        $excelType = pathinfo($request['url'], PATHINFO_EXTENSION) == 'xlsx' ? 'Excel2007' : 'Excel5';
        $objReader = PHPExcel_IOFactory::createReader($excelType);
        $objPHPExcel = $objReader->load($request['url']);
        $arrExcel = $objPHPExcel->getActiveSheet()->toArray();
        //var_dump($arrExcel);

        //移除第一行
        array_shift($arrExcel);

        $success = 0;
        $faild = '';
        $m = new SellRecordModel();
        foreach($arrExcel as $k => $v){
            $r = $m->shipped_import($v[0], $v[1], $v[2]);
            if($r['status'] == '1'){
                $success++;
            } else {
                $faild .= sprintf("%s,%s,%s,%s\n<br>", $v[0], $v[1], $v[2],$r['message']);
            }
        }

        if($success > 0 && $faild == ''){
            $status = '1';
        } else {
            $status = '-1';
        }
        $response = array('status'=>$status, 'success'=>$success, 'faild'=>$faild);
    }

    function import_upload(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $files = array();
        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/";

        $fileInput = 'fileData';
        $dir = ROOT_PATH.'webefast/uploads/';
        $type = $_POST['type'];
    	$ret = check_ext_execl();
        if($ret['status']<0){
            $response = $ret;
            return ;
        }
        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        foreach($files_name_arr as $k=>$v){
            $pic = $_FILES[$v];
            $isExceedSize = $pic['size'] > 500000;
            if(!$isExceedSize){
                if(file_exists($dir.$pic['name'])){
                    @unlink($dir.$pic['name']);
                }
                // 解决中文文件名乱码问题
                //$pic['name'] = iconv('UTF-8', 'GBK', $pic['name']);
                $result = move_uploaded_file($pic['tmp_name'], $dir.$pic['name']);
                $files[$k] = $url.$dir.$pic['name'];
            }
        }
        if(!$isExceedSize && $result){
            $response = array(
                'status' => 1,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'url' => $dir.$_FILES[$fileInput]['name']
            );
        }else if($isExceedSize){
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "文件大小超过500kb！"
            );
        }else{
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！".$result
            );
        }
         set_uplaod($request, $response, $app);
    }

    function import_trade(array &$request, array &$response, array &$app) {
    	
    }

    function import_trade_action(array &$request, array &$response, array &$app) {
	    $app['fmt'] = 'json';
    	$response = load_model('oms/SellRecordModel')->import_trade_action($_FILES);
//    	header("Content-Type: text/html; charset=UTF-8");
//    	echo $ret_msg;
//    	die;
         set_uplaod($request, $response, $app);
    }

    public function import_tpl(array & $request, array & $response, array & $app){
        //获取url路径
        $path = APP_PATH.'data/excelDefault/sell_record_shipped.xlsx';
        header("Content-type:application/vnd.ms-excel;charset=utf8");
        header("Content-Disposition:attachment; filename=sell_record_shipped.xlsx");
        echo file_get_contents($path);
        die();
    }

    public function rank_list(array & $request, array & $response, array & $app){
        $response['rank_rule'] = load_model('op/GiftStrategy2DetailModel')->get_rank_rule();
        $response['rank_shop'] = load_model('base/ShopModel')->get_purview_shop();
       
        $response['rank_list'] = array();
    }
    
    public function get_rank_list(array & $request, array & $response, array & $app){
        $app['fmt'] = 'json';
        $ret = load_model('op/SellRecordRankModel')->get_rank_list($request);
        exit_json_response($ret);
    }
    
    public function send_gift(array & $request, array & $response, array & $app){
        $app['fmt'] = 'json';
        $ret = load_model('op/SellRecordRankModel')->send_gift($request);
        exit_json_response($ret);
    }
    
    
    function export_csv_list(array &$request, array &$response, array &$app){	
        $ret = load_model('op/SellRecordRankModel')->get_send_record($request);
        $str = "序号,订单号,交易号,下单时间,付款时间,买家昵称,订单应收金额,订单状态,是否已参与排名送\n";
        $str = iconv('utf-8','gbk',$str);  
        foreach($ret as $key => $value)  
        {
        	$order_status_str = $value['order_status'] == 1 ? '确认' : '未确认';
        	$order_status_str = iconv('utf-8','gbk',$order_status_str);
        	
        	$is_has_given_str = $value['is_has_given'] == 1 ? '已赠送' : '未赠送';
        	$is_has_given_str = iconv('utf-8','gbk',$is_has_given_str);
        	
            $sort = $key + 1;
            $value['buyer_name'] = iconv('utf-8','gbk',$value['buyer_name']);
            $str .= $sort."\t,".$value['sell_record_code']."\t,".$value['deal_code_list']."\t,".$value['record_time'].",".$value['pay_time'].",".$value['buyer_name'].",".$value['order_money'].",".$order_status_str.",".$is_has_given_str."\n"; //用引文逗号分开  
        }  
        $filename = date('Ymd').'.csv'; //设置文件名  
        $this ->export_csv($filename,$str); //导出  

    }
	
	
    function export_csv($filename,$data){  	
        header("Content-type:text/csv");  
        header("Content-Disposition:attachment;filename=".$filename);  
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');  
        header('Expires:0');  
        header('Pragma:public');  
        echo $data;  
        die;
    }
}