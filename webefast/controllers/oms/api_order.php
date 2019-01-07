<?php
require_lib('util/web_util', true);
require_lib('business_util',true);
require_model("common/TaskModel");
class api_order {
    function down(array &$request, array &$response, array &$app){
        $sale_channel=load_model('base/SaleChannelModel')->get_select();
        $sale_channel_arr=array();
        foreach($sale_channel as $key=>$value){
            if ($value[0] != 'houtai' && $value[0] != 'yoho') {
                $sale_channel_arr[] = $value;
            }
        }
        $response['sale_channel']=$sale_channel_arr;
        $sale_channel_code=$sale_channel_arr[0][0];
        $response['shop'] = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code($sale_channel_code);
    }

    function down_task_action(array &$request, array &$response, array &$app){
        $new_arr = array();
        $start_time = $request['parameter']['start_time'];
        $end_time = date("Y-m-d H:i:s",strtotime($request['parameter']['end_time']." +1 day"));

        $time_arr = split_time($start_time,$end_time,80000);
        $shop_arr = explode(",", $request['parameter']['shop_code']);
        foreach ($shop_arr as $shop){
            foreach ($time_arr as $time){
                $time['shop_code'] = $shop;
                $new_arr[] = $time;
            }
        }
        $response = $new_arr;
    }

    function down_action(array &$request, array &$response, array &$app){
        $task_id_str = "";
        $mdl_auto = new TaskModel();
        $task_data['code'] = 'order_taobao_list';
        $request['app_act'] = "sys/task/get_order";
        $task_data['request'] = $request;

        $time_arr = split_time($request['parameter']['start_time']." 00:00:00",$request['parameter']['end_time']." 23:59:59",80000);
        foreach ($time_arr as $time){
            $task_data['request']['start_time'] = $time['start'];
            $task_data['request']['end_time'] = $time['end'];
            $task_data['request']['platform'] = $request['parameter']['platform'];
            $task_data['request']['shop_code'] = $request['parameter']['shop_code'];
            $ret = $mdl_auto->save_task($task_data);
            $task_id_str .= $ret['data'].",";
        }

        $task_data['code'] = 'order_taobao_list';
        $request['app_act'] = "sys/task/get_detail";
        $task_data['request'] = $request;
        $ret = $mdl_auto->save_task($task_data);
        $task_id_str .= $ret['data'].",";
        $response['status'] = 1;
        $response['data'] = substr($task_id_str,0,strlen($task_id_str)-1);
    }

    function get_down_action_log(array &$request, array &$response, array &$app){
        $mdl_auto = new TaskModel();
        if(!isset($request['num']) || empty($request['num'])){
            $request['num'] = 0;
        }
        $response = $mdl_auto->read_task_log($request['task_id'], $request['num']);
    }
    //-键转单界面
    function change(array &$request, array &$response, array &$app){
    	//#############权限
    	if (!load_model('sys/PrivilegeModel')->check_priv('oms/api_order/change')) {
    		exit_json_response(-1, array(), '无权访问');
    	}
    	//###########
        $response['shop_api'] = get_shop_api_list();
    }

    function change_task_action(array &$request, array &$response, array &$app){
	    $shop_code = $request['shop_code'];
	    $pay_time = @$request['pay_time'];
	    $id = @$request['id'];
	    $_wh = $wh = 'status = 1 and is_change<=0 and shop_code = :shop_code';
	    if (!empty($pay_time)){
		    $wh .= ' and pay_time = :pay_time and id>:id';
	    }
	    $sql = "select id,pay_time,tid from api_order where {$wh} order by pay_time asc,id asc";
	    $_sql_params = $sql_params = array(':shop_code'=>$shop_code);
	    if (!empty($pay_time)){
	    	$sql_params[':pay_time'] = $pay_time;
	    	$sql_params[':id'] = $id;
	    }
		$row = ctx()->db->get_row($sql,$sql_params);

		if (empty($row) && !empty($pay_time)){
			$_wh .= ' and pay_time > :pay_time';
			$_sql_params[':pay_time'] = $pay_time;
			$sql = "select id,pay_time,tid from api_order where {$_wh} order by pay_time asc,id asc";
			//echo $sql."<br/>";
			//echo '<hr/>$_sql_params<xmp>'.var_export($_sql_params,true).'</xmp>';
			$row = ctx()->db->get_row($sql,$_sql_params);
		}

        if(!empty($row)){
            $ret = load_model("oms/TranslateOrderModel")->translate_order($row['tid']);
            $ret['data'] = $row;
            $response = $ret;
        }else{
            $order_info['data'] = get_shop_name_by_code($request['shop_code']);
            $response = $order_info;
        }
    }



        //交易下载
    function down_trade(array &$request, array &$response, array &$app){
    	$ret = load_model('oms/ApiOrderModel')->down_trade($request);
    	 exit_json_response($ret);
    }

     //下载进度
    function down_trade_check(array &$request, array &$response, array &$app){
        $ret = load_model('oms/ApiOrderModel')->down_trade_check($request);
        exit_json_response($ret);
    }

     //联动效果
     function get_shop_by_sale_channel(array &$request, array &$response, array &$app){
        $sale_channel_code=$request['sale_channel_code'];
        $ret= load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code($sale_channel_code);
        exit_json_response($ret);
    }
    
    //米家推送订单
    function create_mijia_order(array &$request, array &$response, array &$app) {
        //目前仅润米一家使用，固定客户id
        $status = load_model('api/ApiKehuModel')->change_db_conn('2289');
        //连接客户库失败
		if ($status === FALSE) {
            exit_json_response(-1,'', 'NO DATABASE CONNECTED');
        }
        $ret = load_model('oms/ApiOrderModel')->api_add_mijia_order();
        exit_json_response($ret);
    }
}
