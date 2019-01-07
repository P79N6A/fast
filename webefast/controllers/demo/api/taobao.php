<?php
header('Content-Type: text/html;charset=utf-8');
require_lib ( 'apiclient/TaobaoClient', true );
require_lib('apiclient/PlatformApiClient');
require_lib('apiclient/TaobaoClient');
class taobao {
	function seller(array & $request, array & $response, array & $app) {
		$m = new TaobaoClient();
		$a = $m->getSeller();
		print_r($a);
		//$app['fmt'] = "json";
	}
	
	function test(array & $request, array & $response, array & $app){
	    $db = $GLOBALS['context']->db;
	    $sql = "select * from api_order_detail";
	    $order_list = $db->get_all($sql);
	    foreach ($order_list as $order){
	        
	    }
	    $order_list[0]['source'] = 1;
	    $db->autoReplace("api_order_detail",$order_list,true);
	}
	
	function test1(array & $request, array & $response, array & $app){
	    $db = $GLOBALS['context']->db;
	    $sql = "select * from api_order";
	    $order = $db->get_row($sql);
	    $sql = "select * from api_order_detail where tid = '".$order['tid']."'";
	    $detail = $db->get_all($sql);
	    $detail1 = $this->get_avg_money($order['order_money'],$detail);
	    print_r($detail1);
	}
	
	function test3(array & $request, array & $response, array & $app){
	    $mdl_api = new TaobaoClient("taobao","c0002");
	    $ret = $mdl_api->getSeller();
	    print_r($ret);
	}
	
    function get_avg_money($order_money,$order_detail){
        $order_money = $this->format_money($order_money);
        
        $detail_count = count($order_detail);
        
        $order_detail_money = 0;//订单详情总金额
        $avg_money_count = 0;////已经被分摊的金额
        
        foreach ($order_detail as &$detail){
            $payment = $this->format_money($detail['payment']);
            $order_detail_money += $payment;
        }
        
        foreach ($order_detail as &$detail){
            $payment = $this->format_money($detail['payment']);
            if ($detail_count != 1) {
				$avg_money = $order_money * ($payment / $order_detail_money);
				$avg_money_count = $this->format_money($avg_money);
				$detail_count--;
			} else {
				$avg_money = $order_detail_money - $avg_money_count;
			}
			$detail['avg_money'] = $avg_money;
        }
        return $order_detail;
    }
    
    function format_money($parameter) {
        return sprintf("%.2f", $parameter);
    }
}