<?php
require_lib('util/web_util', true);
require_lib('apiclient/AlipaymClient', true);

class soonbuy {

    function check_user_info(array & $request, array & $response, array & $app) {
        if (CTX()->get_session("LoginState") != true) {
            exit_json_response(array('status' => '-1', 'data' => '', 'message' => '未登录'));
        } else {
            exit_json_response(array('status' => '1', 'data' => '', 'message' => '已经登录'));
        }
    }
    
    function show_order_info(array & $request, array & $response, array & $app) {
        $order['data']=CTX()->get_session('order_data');
        $response['data'] = $order['data'];
    }

    //订单确认页面
    function show_order_info_go(array & $request, array & $response, array & $app) {
        if (CTX()->get_session("LoginState") != true) {
            CTX()->redirect('index/do_index');
        }
        //产品版本
        switch ($request['p_version']) {
            case "1":
                $order['data']['pro_product_version'] = '标准版';
                $order['data']['p_version'] = '1';
                break;
            case "2":
                $order['data']['pro_product_version'] = '企业版';
                $order['data']['p_version'] = '2';
                break;
            case "3":
                $order['data']['pro_product_version'] = '旗舰版';
                $order['data']['p_version'] = '3';
                break;
        }
        //租用期限
        switch ($request['pro_hire_limit']) {
            case "3":
                $order['data']['pro_hire_limit'] = '3个月';
                $order['data']['p_limit'] = '3';
                break;
            case "6":
                $order['data']['pro_hire_limit'] = '6个月';
                $order['data']['p_limit'] = '6';
                break;
            case "12":
                $order['data']['pro_hire_limit'] = '12个月';
                $order['data']['p_limit'] = '12';
                break;
            case "24":
                $order['data']['pro_hire_limit'] = '24个月';
                $order['data']['p_limit'] = '24';
                break;
            default :
                $order['data']['pro_hire_limit'] = $request['pro_hire_limit'].'个月';
                $order['data']['p_limit'] = $request['pro_hire_limit'];
        }
        $order['data']['cpid']=$request['cpid'];   //产品ID
        $order['data']['cpname']=$request['cpname'];  //产品名称
        $order['data']['stid']=$request['stid'];  //营销类型
        $order['data']['stname']=$request['stname'];  //营销类型名称
        $order['data']['priceid']=$request['priceid'];  //报价方案ID
        $order['data']['pro_dot_num'] = $request['pro_dot_num'];  //默认点数
        $order['data']['pro_price'] = $request['pro_price'];      //价格
        $order['data']['kh_code'] = CTX()->get_session('kh_code');   //客户代码
        $order['data']['kh_name'] = CTX()->get_session('kh_name');   //客户名称
        $response['data'] = $order['data'];
        CTX()->set_session('order_data', $order['data']);
        exit_json_response(array('status' => '1', 'data' => '', 'message' => 'success'));
    }

    //确认提交订购
    function submit_order_info(array & $request, array & $response, array & $app) {
        $khcode = CTX()->get_session('kh_code');
        $khdata = load_model('product/SoonbuyModel')->get_clientinfo($khcode);
        if(empty($khdata['kh_verify_status'])){
           exit_json_response(array('status' => '-1', 'data' => '', 'message' => '客户未审核'));             
        }
        $kh_order['pro_kh_id'] = $khdata['kh_id'];
        $kh_order['pro_cp_id'] = $request['cpid'];
        //pro_sell_price是售价
        $kh_order['pro_sell_price'] = $request['pro_price'];  //标准售价
        $kh_order['pro_rebate_price']= '0';   //折扣价格
        $kh_order['pro_real_price']= $request['pro_price'];   //实际价格
        $kh_order['pro_channel_id'] = '85'; //销售渠道，85是翼商在线订购固定id
        $kh_order['pro_dot_num'] = $request['pro_dot_num'];
        $kh_order['pro_product_version'] = $request['p_version'];
        $kh_order['pro_price_id'] = $request['p_priceid'];   //报价方案id,12是官网年租版的id后续根据正式环境里面的固定id
        $kh_order['pro_st_id'] = $request['p_st_id'];  //营销策略类型id，2代表租用型id
        $kh_order['pro_hire_limit'] = $request['pro_hire_limit'];
        $kh_order['pro_orderdate'] = date('Y-m-d H:i:s'); //订购日期
        $ret = load_model('product/SoonbuyModel')->insert_order($kh_order);
        exit_json_response($ret);
    }

    //个人中心-我的订单-付款
    function pay_order(array & $request, array & $response, array & $app) {
        if (isset($request['order_num'])) {
            $order['pro_pay_status'] = '1';
            $order['pro_paydate'] = date('Y-m-d H:i:s'); //付款日期
            $ret = load_model('product/SoonbuyModel')->update_order($order,$request['order_num']);
            exit_json_response($ret);
        }else{
            exit_json_response(array('status' => '-1', 'data' => '', 'message' => '未登录'));
        }
    }

    //订单提交成功
    function success_order(array & $request, array & $response, array & $app) {
        if(CTX()->get_session("LoginState")!=true){
            CTX()->redirect('index/do_index');
            exit;
        }
        $ret = load_model('product/SoonbuyModel')->get_order_byid($request['djbh']);
        if($ret['pro_pay_status']=="1"){
            CTX()->redirect('mycenter/myself/orderdetail&djbh='.$ret['pro_num']);   //跳转详细页面
            exit;
        }
        $ret['from'] = isset($request['from']) ? $request['from'] : '';
        $response['data'] = $ret;
    }
    
    /**
     * @todo 续费产品
     */
    function renew_product(array & $request, array & $response, array & $app) {
        if (CTX()->get_session("LoginState") != true) {
            CTX()->redirect('index/do_index');
            exit;
        }
        $order_info = load_model('product/SoonbuyModel')->get_order_info($request['pra_id']);
        $out_trade_no = date('YmdHis').mt_rand(000000, 999999);
        $pid = '2088021866361850';
        $key = 'lig7hcjxrhn31yepp1ucocfv4rxx4vcc';
        $p = new AlipaymClient($pid, $key);
        $param = array(
            'out_trade_no' => $out_trade_no,
            'subject' => $order_info['product_name'],
            'total_fee' => $order_info['order_price'],
            'payment_type' => 1,
            'body' => 'eFAST365产品续费',
            'return_url'=>'http://www.baotayun.com/online/weborder/web/return_url.php',
            'notify_url' => 'http://www.baotayun.com/online/weborder/web/notify_url.php',
        );
        $ret = $p->create_direct_pay_by_user($param);
        if(!empty($ret)){
            $data = array(
                'pra_id' => $request['pra_id'], 
                'pra_kh_id' => $order_info['pro_kh_id'], 
                'pra_out_trade_no' => $out_trade_no, 
                'pra_total_fee' => $order_info['order_price']
                    );
            $order_info = load_model('product/SoonbuyModel')->insert_exp('osp_productorder_renew', $data);
        }
        exit_json_response(1, $ret);
    }
    
    /**
     * 返回续费结果
     */
    function pay_return(array & $request, array & $response, array & $app) {
        $pid = '2088021866361850';
        $key = 'lig7hcjxrhn31yepp1ucocfv4rxx4vcc';
        $p = new AlipaymClient($pid, $key);   
        $status = $p->check_notify_data($request);
        if($status == 1){
            $khcode = CTX()->get_session('kh_code');
            $khdata = load_model('product/SoonbuyModel')->get_clientinfo($khcode);
            $ret = load_model('product/SoonbuyModel')->handle_info($request, $khdata['kd_id']);
            echo 'success';
        }else{
            echo 'fail';
        }
    }
    
    function skip_new_url(array & $request, array & $response, array & $app){
        echo "<script>";
        echo "window.location.href='http://www.baotayun.com/online/weborder/web/?app_act=mycenter/myself/orderauth_info'";
        echo "</script>";
        die;
    }
    /**
     * @todo 检查续费结果
     */
    function check_renew(array & $request, array & $response, array & $app) {
        $ret = load_model('product/SoonbuyModel')->get_renew_info($request['pra_id']);
        exit_json_response($ret);
    }

}
