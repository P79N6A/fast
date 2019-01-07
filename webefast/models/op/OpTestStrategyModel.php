<?php
require_model('oms/TranslateOrderModel');

class OpTestStrategyModel extends TranslateOrderModel{
    public $sys_param = array();
    public $test_info = array();
    public function __construct()
    {
        parent::__construct();
    }

    public function test_gift_strategy($request){
        //交易单id
        $tid = isset($request['tid']) ? $request['tid'] : '';
        //策略id
        $id = isset($request['id'])?$request['id']:'';
        if($tid == '' || $id == ''){
            return $this->format_ret(-1,'','信息有误');
        }
        $tid_arr = $this->get_tid_info($tid)[0];
        $gift_strategy_sql = 'select * from op_gift_strategy  where op_gift_strategy_id = :id';
        $strategy_data = $this->db->get_row($gift_strategy_sql,array(':id'=>$id));
        $error_info = array();
        //测试转单主方法
        $test_gift_arr = array('is_error'=>0,'data' => array());
        if(empty($tid)){
            $test_gift_arr['is_error'] = 1;
            $test_gift_arr['data'] = '交易号不存在';
            return $test_gift_arr;
        }
        $ret = array('status'=>1);
        if($tid_arr['is_error'] == 0){
            //是否符合店铺
            $shop_arr = explode(',',$strategy_data['shop_code']);
            if(!in_array($tid_arr['shop_code'],$shop_arr)){
                $test_gift_arr['is_error'] = 1;
                $test_gift_arr['data'] = '店铺不在活动店铺中';
                return $test_gift_arr;
            }
            $ret = $this->translate_order_by_data($tid_arr);
        }elseif($tid_arr['is_error'] == 1){
            $test_gift_arr['is_error'] = 1;
            $test_gift_arr['data'] = $tid_arr['message'];
            return $test_gift_arr;
        }
        if($ret['status'] < 1) {
            $test_gift_arr['is_error'] = 1;
            $test_gift_arr['data'] = $ret['message'];
            return $test_gift_arr;
        }
        if($this->is_postage == 0){
            //执行礼品策略GiftStrategyOpModel
            $gift_strategy_op = load_model('op/GiftStrategy/TestGiftStrategyModel');
            $trade_mx = $this->get_trade_mx();
            $test_ret = $gift_strategy_op->set_strategy_data($this->sell_record_data,$this->sell_record_mx_data,$trade_mx,$strategy_data);
            if($test_ret['status']== 1){
                $detail_id_arr = array();
                foreach ($gift_strategy_op->strategy_gift_result as $v){
                    $detail_id_arr = array_merge($detail_id_arr,$v);
                }
                $test_gift_arr['data'] = array_merge($test_gift_arr['data'],$test_ret);
                //获取规则信息
                $test_detail_values = array();
                $test_detail_str = $this->arr_to_in_sql_value($detail_id_arr,'op_gift_strategy_detail_id',$test_detail_values);
                $test_detail_ret = $this->db->get_all('select od.sort,os.strategy_name,od.name,od.type,od.level,od.is_mutex from op_gift_strategy_detail od inner join op_gift_strategy os on od.strategy_code = os.strategy_code  where op_gift_strategy_detail_id in ('.$test_detail_str.')',$test_detail_values);
                $test_gift_arr['rule'] =$test_detail_ret;
            }else{
                $test_gift_arr['is_error'] = 1;
                $test_gift_arr['data'] = $test_ret['message'];
            }
        }
        unset($this->sell_record_data,$this->sell_record_mx_data);
        return $test_gift_arr;
    }
    //获取交易信息
    public function  get_tid_info($tids) {
        $tid_arr_ext = array();
        $online_date = date('Y-m-d H:i:s', strtotime($this->sys_param['online_date']));
        $err_arr = array();
        if (is_array($tids)) {
            $tid_arr = $tids;
        } else {
            $tid_arr = array($tids);
        }
        $tid_list = "'" . join("','", $tid_arr) . "'";
        $sql = "select tid,source,shop_code,status,pay_type,pay_code,pay_time,seller_nick,buyer_nick,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,receiver_street,receiver_address,receiver_addr,receiver_zip_code,receiver_mobile,receiver_phone,receiver_email,express_code,express_no,buyer_remark,seller_remark,seller_flag,order_money,express_money,alipay_no,integral_change_money,coupon_change_money,balance_change_money,invoice_type,invoice_title,invoice_content,invoice_money,invoice_pay_type,is_change,seller_nick,delivery_money,order_first_insert_time,api_data,buyer_alipay_no,sale_mode,customer_address_id,customer_code,taxpayers_code from api_order where tid in($tid_list)";

        $db_order = ctx()->db->get_all($sql);
// sku_properties,num,sku_id
        $sql = "select source,tid,oid,return_status,sum(num) as num,sku_id,goods_barcode,sum(avg_money) as avg_money,sku_properties,price,pic_path,sale_mode,is_gift from api_order_detail where tid in($tid_list) group by goods_barcode,tid"; //
        $db_order_detail = ctx()->db->get_all($sql);
//获取店铺信息
        $shop_code_arr = array_column($db_order, 'shop_code');
        $shop_data = $this->get_shop_entity_type($shop_code_arr);
        $shop_info_arr = load_model('util/ViewUtilModel')->get_map_arr($shop_data, 'shop_code');
        $order_arr = load_model('util/ViewUtilModel')->get_map_arr($db_order, 'tid');
        $order_detail_arr = load_model('util/ViewUtilModel')->get_map_arr($db_order_detail, 'source,tid', 1);
//判断参数是否开启买家留言匹配
//$param_code = 'buyer_remark';
//$ret = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
        if ($this->sys_param['buyer_remark'] == 1) {
            $this->is_buyer_remark_cs = 1;
        }
        foreach ($tid_arr as $tid) {
            $find_order = isset($order_arr[$tid]) ? $order_arr[$tid] : null;
            if (empty($find_order)) {
                $err_arr[] = array('status' => -3, 'message' => $tid . ' 找不到此交易号');
                $api_data = array('deal_code'=>$tid);
                $api_data['mx'] = array();
                $api_data['message'] = ' 找不到此交易号的商品明细';
                $api_data['is_error'] = 1;
                $this->check_encrypt_order($api_data);
                $tid_arr_ext[] = $api_data;
                continue;
            }
            $ks = $find_order['source'] . ',' . $find_order['tid'];
            $find_order_detail = isset($order_detail_arr[$ks]) ? $order_detail_arr[$ks] : null;
            if (empty($find_order_detail)) {
                $err_arr[] = array('status' => -4, 'message' => $tid . ' 找不到此交易号的商品明细');
                $api_data = $find_order;
                $api_data['mx'] = array();
                $api_data['message'] = ' 找不到此交易号的商品明细';
                $api_data['is_error'] = 1;
                $this->check_encrypt_order($api_data);
                $tid_arr_ext[] = $api_data;
                continue;
            }
            if ($find_order['source'] == 'taobao') {
                $ret = $this->check_app_nick($find_order['shop_code'], $find_order['seller_nick']);
                if ($ret['status'] < 0) {
                    $api_data = $find_order;
                    $api_data['mx'] = $find_order_detail;
                    $api_data['message'] = $ret['message'];
                    $api_data['is_error'] = 1;
                    $this->check_encrypt_order($api_data);
                    $tid_arr_ext[] = $api_data;
                    continue;
                }
            }
            //订单的时间要比系统上线时间大，用 下单时间
            $_order_time = $find_order['order_first_insert_time'];
            $_order_time_tips = '下单时间';
            if ($_order_time < $online_date) {
                $err_arr[] = array('status' => -7, 'message' => "{$tid}的 {$_order_time_tips} 小于 {$online_date}(系统上线时间)");
                continue;
            }

            //过滤地址中的特殊字符(主要过滤水平和垂直制表符，单引号，双引号，换行符,`)
            $specialchars = "\"\'\n\b`";
            $chars = '/[' . $specialchars . ']/u';
            $find_order['receiver_address'] = preg_replace($chars, '', $find_order['receiver_address']);
            $find_order['receiver_addr'] = preg_replace($chars, '', $find_order['receiver_addr']);

            // $find_order['receiver_district'] = !empty($find_order['receiver_district']) ? $find_order['receiver_district'] : 0;
            //分销订单过滤品牌
            if (!empty($this->filter_code) && ($shop_info_arr[$find_order['shop_code']]['entity_type'] == 2)) {
                $filter_detail_money = array();
                $oid = array();
                foreach ($find_order_detail as $key => $datail) {
                    $is_filter = $this->check_is_filter($datail['sku_properties'], $this->filter_code);
                    if ($is_filter == 0) {
                        $oid[] = $datail['oid'];
                        $filter_detail_money[] = $datail['avg_money'];
                        unset($find_order_detail[$key]);
                    }
                }
                if (empty($find_order_detail)) {
                    $api_data = $find_order;
                    $api_data['mx'] = $find_order_detail;
                    $api_data['message'] = '分销订单过滤品牌后明细为空';
                    $api_data['is_error'] = 1;
                    $this->check_encrypt_order($api_data);
                    $tid_arr_ext[] = $api_data;
                    continue;
                }
                //处理金额
                if (!empty($filter_detail_money)) {
                    $avg_money_all = array_sum($filter_detail_money);
                    $find_order['order_money'] = $find_order['order_money'] - $avg_money_all;
                    $find_order['order_money'] = $find_order['order_money'] - $find_order['express_money'];
                    $find_order['express_money'] = 0;
                }
            }


            $api_data = $find_order;
            $api_data['mx'] = $find_order_detail;
            $ret_check_encrypt = $this->check_encrypt_order($api_data);
            $tid_arr_ext[] = $api_data;
        }
        return $tid_arr_ext;
    }

    //测试模拟转单
    public function translate_order_by_data($api_data, $type = '') {
        $this->translate_msg = array();
        $this->sell_record_op_log = array();
        $this->sell_record_data = array();
        $this->invoice_info = array();
        $this->is_postage = 1;
        $this->check_encrypt_order($api_data);
        //检测交易号是否存在
        if (!$api_data['tid']) {
            return $this->format_ret(-11, $api_data['tid'], '请填写交易单号');
        }
        //检测手机号必填
        if (empty($api_data['receiver_mobile']) && empty($api_data['receiver_phone'])) {
            return $this->format_ret(-12, $api_data['tid'], '手机和电话不能同时为空');
        }

        //付款日期要控制到必填，其中付款日期有两种方式
        //（1）“是否货到付款”为“否”以及为空时必填
        //（2）“是否货到付款”为“是“的时候不必判断付款日期是否填写，导入后值为空
        if ($api_data['pay_type'] == 0) {
            if (!$api_data['pay_time']) {
                return $this->format_ret(-13, $api_data['tid'], '请填写付款时间');
            }
        }
        $order_first_insert_time = strtotime($api_data['order_first_insert_time']);
        //判断时间不合法
        if (!$order_first_insert_time || $order_first_insert_time = 0) {
            return $this->format_ret(-14, $api_data['tid'], '下单日期不合法');
        }

        //验证当前用户是否有这个店铺的转单权限
        $login_type = CTX()->get_session('login_type'); //分销商登录
        if ($login_type != 2) {//不是分销商登录
            if ($this->is_cli_trans == 0) {
                $ret = $this->check_user_shop_priv($api_data['shop_code']);
                if ($ret['status'] < 1) {
                    return $ret;
                }
            }
        }
        //订单信息预检查
        $ret = $this->check_api_data($api_data);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $this->crm_customer_address = array();
        if (!empty($api_data['customer_address_id'])) {
            $this->crm_customer_address = $this->get_crm_customer_address($api_data['customer_address_id']);
        }

        //地址处理
        $ret = $this->match_addr($api_data);
        if ($ret['status'] < 1) {
            return $ret;
        }

        //条码匹配 同时生成订单明细
        $ret = $this->match_barcode($api_data, $type);
        if ($ret['status'] < 1) {
            return $ret;
        }

        //生成主单信息，并初始化主单有明细的单号
        $ret = $this->create_sell_record_info($api_data, $type);

        if ($ret['status'] < 1) {
            return $ret;
        }
        //仓库匹配
        $ret = $this->match_store($api_data);
        if ($ret['status'] < 1) {
            return $ret;
        }

        //快递匹配
        $ret = $this->match_express($api_data);
        if ($ret['status'] < 1) {
            return $ret;
        }

        if (isset($api_data['api_data']) && !empty($api_data['api_data'])) {
            $this->check_api_data_content($api_data['api_data']);
        }

        if ($api_data['source'] == 'weipinhui' && !empty($api_data['order_remark']) && $this->incr_service['wph_remark_control'] == TRUE) {
            $this->sell_record_data['order_remark'] = empty($this->sell_record_data['order_remark']) ? $api_data['order_remark'] : $this->sell_record_data['order_remark'] . ';' . $api_data['order_remark'];
        }

        //计划发货时间
        if ($this->sys_param['presell_plan'] == 1) {
            $ret = load_model('oms/SellRecordOptModel')->js_plan_send_time_by_presell($this->sell_record_data, $this->sell_record_mx_data);
        } else {
            $ret = load_model('oms/SellRecordOptModel')->js_sell_plan_send_time($this->sell_record_data, $this->sell_record_mx_data);
        }
        if ($ret['status'] < 0) {
            return $ret;
        }
        $this->log('js_sell_plan_send_time');
        $this->sell_record_data = $ret['data'];
        //主单价格
        $ret = load_model('oms/SellRecordOptModel')->js_record_price($this->sell_record_data, $this->sell_record_mx_data, 0, $type);
        if ($ret['status'] < 0) {
            return $ret;
        }

        $this->sell_record_mx_data = $ret['data']['mx'];

        unset($ret['data']['mx']);
        $this->sell_record_data = $ret['data'];
        //补邮自动处理
        $this->is_postage = $this->postage_auto($this->sell_record_mx_data);
        return $this->format_ret(1);
    }
}