<?php
require_lib ('util/web_util', true);

class order_scan {

    function view(array &$request, array &$response, array &$app) {

    
    }
    
    
    function getData(array &$request, array &$response, array &$app) {
    	
		$app['fmt'] = 'json';
	    $data['data'] = array();

            $date = date("Y-m-d",time());
            $datetime =  $date." 0:00:00";
	    $data_num = array();
	    $data_num['num'] = date("Y-m-d H:i:s",time());
	    $data_num['key'] = 'time';
	    $data['data'][] = $data_num;
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->pay_num($datetime);
	    $data_num['key'] = 'pay_num';
	    $data['data'][] = $data_num;
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->transform_num($date);
		$data_num['key'] = 'transform_num';
	    $data['data'][] = $data_num;
	    
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->unconfirm_num();
		$data_num['key'] = 'unconfirm_num';
	    $data['data'][] = $data_num;
	    $unconfirm_num =  $data_num['num'];
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->unnotice_num();
		$data_num['key'] = 'unnotice_num';
	    $data['data'][] = $data_num;
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->unpick_num();
		$data_num['key'] = 'unpick_num';
	    $data['data'][] = $data_num;    
	    
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->unscan_num();
		$data_num['key'] = 'unscan_num';
	    $data['data'][] = $data_num; 
	    
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->deliver_num($date);
		$data_num['key'] = 'deliver_num';
	    $data['data'][] = $data_num; 
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->back_num($date);
		$data_num['key'] = 'back_num';
	    $data['data'][] = $data_num;     
	
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->back_error_num();
		$data_num['key'] = 'back_error_num';
	    $data['data'][] = $data_num;    
	    
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->pending_num();
		$data_num['key'] = 'pending_num';
	    $data['data'][] = $data_num; 
	    $pending_num =  $data_num['num']; 
	    
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->problem_num();
		$data_num['key'] = 'problem_num';
	    $data['data'][] = $data_num; 
	    $problem_num =  $data_num['num']; 
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->stockout_num();
		$data_num['key'] = 'stockout_num';
	    $data['data'][] = $data_num;   
	    $stockout_num =  $data_num['num'];  
	
	    
	    $data_num = array();
	    $data_num['num'] = load_model('oms/SellRecordModel')->normal_num(); 
		$data_num['key'] = 'normal_num';
	    $data['data'][] = $data_num;  
	    
	    
	    $response = $data['data'];
    
    }
    
    /**
     * @todo 获取网络退单监控数据
     */
    function getRefundOrderData(array &$request, array &$response, array &$app) {

        $app['fmt'] = 'json';
        $data['data'] = array();

        $date = date("Y-m-d", time());
        $start_time = $date . " 00:00:00";
        $end_time = $date . " 23:59:59";
                
        $data_num = array();
        $data_num['num'] = date("Y-m-d H:i:s", time());
        $data_num['key'] = 'time_ro';
        $data['data'][] = $data_num;

        $data_num = array();
        $data_num['num'] = load_model('oms/SellReturnModel')->ro_num($start_time,$end_time);
        $data_num['key'] = 'ro_num';
        $data['data'][] = $data_num;

        $data_num = array();
        $data_num['num'] = load_model('oms/SellReturnModel')->tran_ro_num($start_time,$end_time);
        $data_num['key'] = 'tran_ro_num';
        $data['data'][] = $data_num;


        $data_num = array();
        $data_num['num'] = load_model('oms/SellReturnModel')->unconfirm_ro_num();
        $data_num['key'] = 'unconfirm_ro_num';
        $data['data'][] = $data_num;
        $unconfirm_num = $data_num['num'];

        $data_num = array();
        $data_num['num'] = load_model('oms/SellReturnModel')->unreceipt_ro_num();
        $data_num['key'] = 'unreceipt_ro_num';
        $data['data'][] = $data_num;

        $data_num = array();
        $data_num['num'] = load_model('oms/SellReturnModel')->unrefund_ro_num();
        $data_num['key'] = 'unrefund_ro_num';
        $data['data'][] = $data_num;


        $data_num = array();
        $data_num['num'] = load_model('oms/SellReturnModel')->receipt_ro_num($start_time,$end_time);
        $data_num['key'] = 'receipt_ro_num';
        $data['data'][] = $data_num;


        $data_num = array();
        $data_num['num'] = load_model('oms/SellReturnModel')->refund_ro_num($start_time,$end_time);
        $data_num['key'] = 'refund_ro_num';
        $data['data'][] = $data_num;

        $response = $data['data'];
    }

    function getDataByShop(array &$request, array &$response, array &$app) {
    	
		$app['fmt'] = 'json';
//	    $data['data'] = array();
//	    
//		$data_num = array();
//	    $data_num['num'] = load_model('oms/SellRecordModel')->category_num();
//		$data_num['key'] = 'category_num';
//	    $data['data'][] = $data_num;   
//	    
	    
	    $response = load_model('oms/SellRecordModel')->category_num();
    
    }
    
    
  
}
