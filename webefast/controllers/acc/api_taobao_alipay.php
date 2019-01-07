<?php
/*
 * 支付宝收支流水
 */
/**
 * Description of retail_settlement_detail
 *
 * @author user
 */
class api_taobao_alipay {
    function do_list(array & $request, array & $response, array & $app){
    	//$response['total'] = load_model('acc/ApiTaobaoAlipayModel')->total_amount_search($request);
    }
    //支付宝核销查询
    function search_list(array & $request, array & $response, array & $app){
    	//$response['total'] = load_model('acc/ApiTaobaoAlipayModel')->total_amount_search($request);
    }
    //支付宝流水合计
    function alipay_total_amount_search(array & $request, array & $response, array & $app){
    	$response = load_model('acc/ApiTaobaoAlipayModel')->alipay_total_amount_search($request);
    	//print_r($response);die;
    }
    //合计
    function total_amount_search(array & $request, array & $response, array & $app){
    	$response = load_model('acc/ApiTaobaoAlipayModel')->total_amount_search($request);
    	//print_r($response);die;
    }
    function do_check_account(array & $request, array & $response, array & $app){
    	$res = load_model('acc/ApiTaobaoAlipayModel')->do_update_check_status($request['id']);
    	exit_json_response($res);
    }
    function import(array & $request, array & $response, array & $app){
    	//店铺
    	$response['shop'] = ds_get_select('shop',2,array('sale_channel_code'=>'taobao'));
    }
    function do_import(array & $request, array & $response, array & $app){
    	$ret = check_ext_execl();
        set_uplaod($request, $response, $app);
        if($ret['status']<0){
          //  exit_json_response($ret);
             $response = $ret;
            return ;
            
        }
    	$file = $_FILES['fileData']['tmp_name'];
    	//print_r($request);
    	//print_r($_FILES);
    	$ret = load_model('acc/ApiTaobaoAlipayModel')->imoprt_detail($request['shop_code'],$file);
    	//print_r($ret);
    	//$response = $ret;
    	//$response['url'] = $_FILES['fileData']['name'];
    	$ret['url'] = $_FILES['fileData']['name'];
    	$ret1['status'] = '1';
    	$ret1['data'] = '';
    	$ret1['url'] = $_FILES['fileData']['name'];
        $response = $ret;
    }


    /**
     * 更新
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function update_alipay_info(array & $request, array & $response, array & $app){
        $res = load_model('acc/ApiTaobaoAlipayModel')->update_alipay_info($request['aid'],$request);
        exit_json_response($res);
    }


}