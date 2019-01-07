<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
ini_set('memory_limit', '1024M'); //内存限制 
set_time_limit(0);

class OrderCombineModel extends TbModel {

    public function __construct() {
        parent::__construct();
    }

    /**
      锁定单、非锁定单合并后，为非锁定单；
      预售单、非预售单合并后，为预售单

      1.订单合并规则：
      店铺ID、购买人昵称、收货人姓名、地址相同：
      非问题单、非缺货单、非锁定单、非预售单：
      有效单(非挂起、非作废单)：
      已付款、未发货单：
      非货到付款单且付款方式一致：
      非vjia、非优购、非NCM单、非openshop：
      非京东单(除非启用自定义参数--京东sop订单允许合并)：
      仓库启用第三方物流，订单通知配货后不能合并：
      2.订单合并：
      原订单作废，释放库存；
      合并单，占库存；
      合并单，表结构中，需记录合并单具体由哪几个订单合并而来；
      订单操作日志，’被合并的订单号：412050000044,412040000177‘；
      3.合并订单列表显示的订单：订单的‘收货人、收货地址、电话’一致，且已付款未确认；
     */
    function combine_order($sell_sell_record_code_arr, $where_str = '', $type = '') {
        require_model('oms/OrderCombineClModel');
        $OrderCombineCl = new OrderCombineClModel();
        //淘分销是否参与合并
        $cfg_data = load_model('oms/OrderCombineStrategyModel')->get_val_by_code(array('order_combine_is_taofx'));
        $order_combine_is_taofx_arr = $cfg_data['order_combine_is_taofx'];
        if (empty($where_str)) {
            $where_str = $OrderCombineCl->get_where_str('', $type);
        }
    //    $cl = $OrderCombineCl->get_cl();

        $temp = $sell_sell_record_code_arr;
        $first_sell_record_code = array_shift($temp);
        $sell_sell_record_code_list = "'" . join("','", $sell_sell_record_code_arr) . "'";

        $sql = "select * from oms_sell_record where sell_record_code in($sell_sell_record_code_list) $where_str order by record_time desc";
        //echo $sql;die;
        $order_info_arr = ctx()->db->getAll($sql);
        //echo '<hr/>$sell_sell_record_code_arr<xmp>'.var_export($sell_sell_record_code_arr,true).'</xmp>';
        //echo '<hr/>$order_info_arr<xmp>'.var_export($order_info_arr,true).'</xmp>';die;
        if (count($sell_sell_record_code_arr) != count($order_info_arr)) {
            return $this->format_ret(-1, '', '合并失败，可能含有不允许合并的订单或订单状态已发生改变');
        }
        $is_fx = false;
        // 验证合单规则
        $check_flag = $this->check_order_combine($order_info_arr, $is_fx, $type);
        if ($check_flag['status'] < 0) {
            return $check_flag;
        }
        //验证是否分销订单合并（只支持普通分销）
        $order_data = $this->check_is_fx_settlement($order_info_arr);
        if ($order_data['status'] < 0) {
            return $order_data;
        }
        $is_fenxiao = $order_data['status'];

        $ret = $this->combine_order_intercept($order_data['data']);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $order_info_arr = $ret['data'];
        $temp = $order_info_arr;
        $fld = 'sale_channel_code,store_code,shop_code,user_code,pay_type,pay_code,buyer_name,buyer_name,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,receiver_street,receiver_address,receiver_addr,receiver_zip_code,receiver_mobile,receiver_phone,receiver_email,express_code,seller_flag,is_wap,is_jhs,point_fee,alipay_point_fee,coupon_fee,yfx_fee,is_fenxiao,record_time,must_occupy_inv,order_status,pay_status,shipping_status,alipay_no,pay_time,is_problem,customer_code,customer_address_id,invoice_type';
        if (($order_combine_is_taofx_arr['rule_status_value'] == 1 || $order_combine_is_taofx_arr['rule_scene_value'] == 1) && $is_fenxiao != 2) { //淘分销参与合并
            $fld .= ',fenxiao_code,fenxiao_name';
        }
        if ($is_fenxiao == 2) { //普通分销
            $fld .= ',fenxiao_code,fenxiao_name,is_fx_settlement,fx_express_money,fx_payable_money';
        }
        $first_order_row = load_model('util/ViewUtilModel')->copy_arr_by_fld(array_shift($temp), $fld);


        $sell_record_code_arr = array();
        //重新生成交易号 合并 express_money,paid_money,seller_remark,buyer_remark
        $express_money_array = array();
        $paid_money_array = array();
        $seller_remark_array = array();
        $buyer_remark_array = array();
        $order_remark_array = array();
        $store_remark_array = array();
        $invoice_title_array = array();
        $is_change_record_array = array();
        $is_rush_array = array();
        $real_money = array();
        $fx_payable_money = array();
        $fx_express_money = array();
        $is_problem_array = array();

        foreach ($order_info_arr as $row) {
            $is_change_record_array[] = (int) $row['is_change_record'];
            $sell_record_code_arr[] = $row['sell_record_code'];
            $express_money_array[] = $row['express_money']; //邮费
            $paid_money_array[] = $row['paid_money']; //已付款
            $delivery_money_arr[] = $row['delivery_money']; //cod服务费
            $payable_money_arr[] = $row['payable_money']; //财务应收
            $goods_weigh_arr[] = $row['goods_weigh'];
            $fx_payable_money[] = $row['fx_payable_money']; //分销商结算金额
            $fx_express_money[] = $row['fx_express_money']; //分销商运费金额

            if (trim($row['seller_remark']) != '') {
                $seller_remark_array[] = $row['seller_remark'];
            }
            if (trim($row['buyer_remark']) != '') {
                $buyer_remark_array[] = $row['buyer_remark'];
            }
            if (trim($row['order_remark']) != '') {
                $order_remark_array[] = $row['order_remark'];
            }
            if (trim($row['store_remark']) != '') {
                $store_remark_array[] = $row['store_remark'];
            }
            if (trim($row['invoice_title']) != '') {
                $invoice_title_array[] = $row['invoice_title'];
            }
            $is_rush_array[] = $row['is_rush'];
            $is_problem_array[] = $row['is_problem'];
        }

        // 生成唯一订单号
        $first_order_row['sell_record_code'] = load_model('oms/SellRecordModel')->new_code();

        //读取合并单明细
        $sql = "select sell_record_detail_id,sell_record_code,deal_code,sub_deal_code,goods_code,spec1_code,spec2_code,sku_id,sku,goods_price,num,lock_num,goods_weigh,avg_money,platform_spec,cost_price,is_gift,sale_mode,delivery_mode,delivery_days_or_time,plan_send_time,is_delete,pic_path,combo_sku,sale_mode,fx_amount,trade_price,api_refund_num,api_refund_desc  from oms_sell_record_detail where sell_record_code in($sell_sell_record_code_list)";
        $db_mx = ctx()->db->getAll($sql);
        $ins_mx = array();
        $mx_data = array();
        $deal_code_arr = array();
        foreach ($db_mx as $sub_arr) {
            $mx_data[$sub_arr['sell_record_code']][] = $sub_arr;
            unset($sub_arr['sell_record_detail_id']);
            $sub_arr['lock_num'] = 0;
            $sub_arr['sell_record_code'] = $first_order_row['sell_record_code'];

            $cur_num = $sub_arr['num'];
            $cur_avg_money = $sub_arr['avg_money'];
            $fx_amount = $sub_arr['fx_amount'];
            $api_refund_num =  $sub_arr['api_refund_num'];

            //以deal_code 和 SKU 为维度，合并相同的明细
            $ks = "{$sub_arr['deal_code']},{$sub_arr['sku']},{$sub_arr['is_gift']}";
            $combo_sku = $sub_arr['combo_sku'];
            if (isset($ins_mx[$ks])) {
                $sub_arr = $ins_mx[$ks];
                $sub_arr['num'] += $cur_num;
                $sub_arr['avg_money'] += $cur_avg_money;
                $sub_arr['fx_amount'] += $fx_amount;
                $sub_arr['api_refund_num'] += $api_refund_num;
               if($api_refund_num>0&&$sub_arr['api_refund_num']>$api_refund_num){
                   $sub_arr['api_refund_num'] = "订单商品数量:{$sub_arr['num']},接口退单退货：{$api_refund_num}";
               }
                 
                if (!empty($combo_sku)) {
                    $sub_arr['combo_sku'] = empty($sub_arr['combo_sku']) ? $combo_sku : $sub_arr['combo_sku'] . "," . $combo_sku;
                }
            }
            $ins_mx[$ks] = $sub_arr;
            $deal_code_arr[$sub_arr['deal_code']] = $sub_arr['deal_code'];
        }

        // 设置合并订单标识
        $first_order_row['is_combine_new'] = 1;
        $first_order_row['order_status'] = 0; //未确认状态
        $first_order_row['combine_orders'] = join(',', $sell_sell_record_code_arr);
        $first_order_row['deal_code_list'] = join(',', $deal_code_arr);
        $deal_code_str = load_model('oms/SellRecordOptModel')->get_guid_deal_code($first_order_row['deal_code_list']);
        $first_order_row['deal_code'] = (strlen($deal_code_str) > 200) ? md5($deal_code_str) : $deal_code_str;
        $first_order_row['express_money'] = array_sum($express_money_array);

        $first_order_row['paid_money'] = array_sum($paid_money_array);
        $first_order_row['delivery_money'] = array_sum($delivery_money_arr);
        $first_order_row['seller_remark'] = join('|', $seller_remark_array);
        $first_order_row['buyer_remark'] = join('|', $buyer_remark_array);
        $first_order_row['invoice_title'] = implode(',', $invoice_title_array);
        $first_order_row['is_seller_remark'] = empty($first_order_row['seller_remark']) ? 0 : 1;
        $first_order_row['is_buyer_remark'] = empty($first_order_row['buyer_remark']) ? 0 : 1;

        $first_order_row['order_remark'] = join('|', $order_remark_array);

        $first_order_row['store_remark'] = join('|', $store_remark_array);

        if (in_array(1, $is_rush_array)) {
            $first_order_row['is_rush'] = 1;
        }
        //判断是否为问题单
        // $is_problem = 0;
        // if (in_array(1, $is_problem_array)) {
        //     $is_problem = 1;
        //     $first_order_row['is_problem'] = 1;
        // }
        $is_problem = 0;
        if(in_array(1, $is_problem_array)) {
            $is_problem = 1;
            $first_order_row['is_problem'] = 1;
        }
        $sell_record_tag = load_model('oms/SellRecordModel')->get_problem_data($sell_record_code_arr);
        $tag_v = array();
        foreach ($sell_record_tag as $record => $tag) {
            foreach ($tag['tag_v'] as $k => $v) {
                $tag_v[] = $v;
            }
        }
        $tag_v = array_unique($tag_v);
        $first_order_row['create_time'] = date('Y-m-d H:i:s', time());

        unset($first_order_row['sell_record_id']);

        $ret = load_model('oms/SellRecordOptModel')->js_sell_plan_send_time($first_order_row, $ins_mx);
        if ($ret['status'] < 0) {
            return $ret;
        }

        $ins_mx = array_values($ret['data']['mx']);
        unset($ret['data']['mx']);
        $first_order_row = $ret['data'];

        $ret = load_model('oms/SellRecordOptModel')->js_record_price($first_order_row, $ins_mx, 1);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $first_order_row = $ret['data'];

        //取消云栈，目前考虑未生成波次情况
        foreach ($order_info_arr as $combine_sell_record_info) {
            $record_info = load_model('oms/SellRecordOptModel')->get_record_by_code($combine_sell_record_info['sell_record_code']);
            if (empty($record_info['waves_record_id'])) {
                load_model('oms/DeliverLogisticsModel')->cancel_waybill($record_info);
            }
        }

        ctx()->db->begin_trans();
        //赠品策略升档
        $ret_gift = $this->combine_gift_strategy($first_order_row, $ins_mx);
        $gift_log = '';
        if (!empty($ret_gift['data'])) {
            $gift_log = $ret_gift['message'];
        }
        if ($first_order_row['customer_address_id'] == 0||empty($first_order_row['customer_code'])) {
            $this->create_customer_address($first_order_row);
        }

        $first_order_row['fx_payable_money'] = array_sum($fx_payable_money); //分销商结算
        $first_order_row['fx_express_money'] = array_sum($fx_express_money); //运费结算
        $first_order_row['goods_weigh'] = (load_model('op/PolicyExpressModel')->get_record_detail_goods_weigh($ins_mx)) * 0.001; //订单理论重量
        if ($first_order_row['is_fenxiao'] == 1) {
            $first_order_row['is_fx_settlement'] = 1;
        }
        $ins_ret1 = ctx()->db->create_mapper('oms_sell_record')->insert($first_order_row);
        if ($ins_ret1 != true) {
            ctx()->db->rollback();
            return $this->format_ret(-1, '', '生成合并后订单主单信息失败');
        }
        //如果是增值税发票，把发票信息带过来
        if($first_order_row['invoice_type'] == 'vat_invoice') {
            $sell_record_code = $sell_sell_record_code_arr[0];
            $sql = "SELECT company_name,taxpayers_code,registered_country,registered_province,registered_city,registered_district,registered_street,registered_addr,phone,bank,bank_account FROM oms_sell_invoice WHERE sell_record_code = :sell_record_code ";
            $invoice_arr = $this->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
            $ret = load_model('oms/SellRecordModel')->insert_vat_invoict($first_order_row['sell_record_code'], $invoice_arr);
            if($ret['status'] < 0) {
                ctx()->db->rollback();
                return $this->format_ret(-1, '', '生成合并后订单发票信息失败');
            }
        }

        $ins_ret2 = ctx()->db->create_mapper('oms_sell_record_detail')->insert($ins_mx);
        if ($ins_ret2 != true) {
            ctx()->db->rollback();
            return $this->format_ret(-1, '', '生成合并后订单明细信息失败');
        }
        // if ($is_problem == 1) {
        //         $tag_type = 'problem';
        //     } else {
        //         $tag_type = 'order_tag';
        //     }
        //问题单合并后为问题单
        // if ($is_problem == 1) {
            $tag_data = array();
            foreach ($tag_v as $key => $value) {
                $tag_data[] = array(
                    'sell_record_code' => $first_order_row['sell_record_code'],
                    'tag_type' => oms_tb_val('oms_sell_record_tag', 'tag_type', array('tag_v' => $value)),
                    'tag_v' => $value,
                    'tag_desc' => oms_tb_val('oms_sell_record_tag', 'tag_desc', array('tag_v' => $value)),
                );
            }
            $this->insert_multi_exp('oms_sell_record_tag', $tag_data);
        // }

        //先作废原来订单,把占用的库存先放出来,再进行新订单的库存占用
        require_model('oms/SellRecordOptModel');
        $o_order = new SellRecordOptModel();
        $sys_user = load_model('oms/SellRecordOptModel')->sys_user();
        foreach ($order_info_arr as $sell_record_info) {
            $find_mx = isset($mx_data[$sell_record_info['sell_record_code']]) ? $mx_data[$sell_record_info['sell_record_code']] : array();
            if (empty($find_mx)) {
                ctx()->db->rollback();
                return $this->format_ret(-1, '', '订单明细为空' . $sell_record_info['sell_record_code']);
            }
            $sell_info = $o_order->get_record_by_code($sell_record_info['sell_record_code']);
//            if($sell_info['is_problem']>0){
//                CTX()->db->rollback();
//              return $this->format_ret(-1, '', '生成合并后订单失败'.$sell_record_info['sell_record_code'].'是问题单');      
//            }

            $ret = $o_order->biz_cancel($sell_info, $find_mx, $sys_user);
            if ($ret['status'] < 0) {
                ctx()->db->rollback();
                return $ret;
            }
        }
        //die;

        $sql = "update oms_sell_record set is_combine = 1,combine_new_order = {$first_order_row['sell_record_code']} where sell_record_code in($sell_sell_record_code_list)";
        ctx()->db->query($sql);

        //新订单库存占用
        //
        //默认未占用库存
        $o_order->set_sell_record_is_lock($first_order_row['sell_record_code'], false);

        $ret = $o_order->lock_detail($first_order_row, $ins_mx, 1);
        if ($ret['status'] < 1 && $ret['status'] != 10) {
            ctx()->db->rollback();
            return $ret;
        }

        // 写入log
        $action_note = '被合并的订单号：' . join(',', $sell_record_code_arr);
        load_model('oms/SellRecordActionModel')->add_action_info($first_order_row, '订单合并', $action_note);

        $action_note = '已合并到订单：' . $first_order_row['sell_record_code'];
        foreach ($order_info_arr as $sell_record_info) {
            load_model('oms/SellRecordActionModel')->add_action_info($sell_record_info, '订单合并', $action_note);
        }

        if (!empty($gift_log)) {
            load_model("oms/SellRecordActionModel")->add_action_info($first_order_row, '策略', $gift_log);
        }


        ctx()->db->commit();

        return $this->format_ret(1, $first_order_row['sell_record_code']);
    }

    /**
     * 对于已通知配货的，自动拦截
     */
    function combine_order_intercept($order_info_arr) {
        foreach ($order_info_arr as $k => $sub_info) {
            if ($sub_info['shipping_status'] > 0) {

                $ret = load_model('oms/SellRecordOptModel')->biz_intercept($sub_info, 0, '此订单需要进行合并');
                if ($ret['status'] < 0) {
                    return $ret;
                } else {
                    $order_info_arr[$k]['order_status'] = 0;
                    $order_info_arr[$k]['shipping_status'] = 0;
                }
            }
        }
        return $this->format_ret(1, $order_info_arr);
    }

    /**
     * 验证合单规则
     */
    function check_order_combine($order_info_arr, &$is_fx = false, $type = '') {
        if (count($order_info_arr) < 2) {
            return $this->format_ret(-1, '', '数据错误，参与合并订单数不能少于2');
            //  return flase;
        }
        /*
          $OrderCombineCl = new OrderCombineCl();
          $cl = $OrderCombineCl->get_cl();
         */
        $cl = array();
        /**
         * 若允许换货单参与合并，合并后一律为换货单；
         * 若其中含有未确认单，合并后一律为未确认单；
         */
        $is_change_record_change = 0;
        $order_status_change = 0;
        $is_presell = 0;
         $is_problem1 = 0;
        $sell_record_code_arr = array();
        foreach ($order_info_arr as $v) {
            $sell_record_code_arr[]= $v['sell_record_code'] ;
            if ($v['is_change_record'] > 0) {
                $is_change_record_change = 1;
            }
            if ($v['order_status'] == 0) {
                $order_status_change = 1;
            }
            if ($v['sale_mode'] == 'presale') {
                $is_presell = 1;
            }
            if ($v['is_problem'] == 1) {
                $is_problem1 = 1;
            }
            
        }
       // $sell_record_code_arr = substr($sell_record_code_str, 0, -1);

        $first_order_info = array_shift($order_info_arr);

        if (empty($order_info_arr)) {
            return $this->format_ret('-1', '', '数据错误，参与合并订单数不能少于2');
        }
        $deal_code1 = trim($first_order_info['deal_code']);
        $receiver_name1 = trim($first_order_info['receiver_name']);
        $receiver_address1 = $first_order_info['receiver_province'] . $first_order_info['receiver_city'] . $first_order_info['receiver_district'] . trim($first_order_info['receiver_addr']);
        $customer_address_id1 = $first_order_info['customer_address_id'];

        $shop_code1 = $first_order_info['shop_code'];
   
        $is_fenxiao1 = (int) $first_order_info['is_fenxiao'];
        $pay_code1 = $first_order_info['pay_code'];
        $order_status1 = (int) $first_order_info['order_status'];

        $shipping_status1 = (int) $first_order_info['shipping_status'];
        $pay_status1 = (int) $first_order_info['pay_status'];

        $is_change_record1 = (int) $first_order_info['is_change_record'];
        $order_status1 = (int) $first_order_info['order_status'];
        $buyer_name1 = (string) $first_order_info['buyer_name'];

        $customer_code1 = (string) $first_order_info['customer_code'];

        $sale_channel_code1 = (string) $first_order_info['sale_channel_code'];
        $store_code1 = $first_order_info['store_code'];
        $is_split_new1 = (int) $first_order_info['is_split_new'];
        $lock_inv_status1 = (int) $first_order_info['lock_inv_status'];
        $is_print_sellrecord = (int) $first_order_info['is_print_sellrecord'];
        $is_print_express = (int) $first_order_info['is_print_express'];

        $ret_check_wms_store = load_model('wms/WmsEntryModel')->check_wms_store($first_order_info['store_code']);
        
      
//        if (in_array('FULL_REFUND', $tag_record)) {
//            return $this->format_ret(-1, '', '买家申请退款(全部退)不能参与合并, 主订单：' . $first_order_info['sell_record_code']);
//         }
         
        if ($first_order_info['shipping_status'] > 0 && $ret_check_wms_store['status'] > 0) {
            return $this->format_ret(-1, '', '订单仓库启用了第三方物流,订单通知配货后不能合并, 主订单：' . $first_order_info['sell_record_code']);
        }

        $cfg_data = load_model('oms/OrderCombineStrategyModel')->get_val_by_code(array('order_outo_combine', 'order_combine_is_change', 'order_combine_is_split', 'order_combine_is_houtai', 'order_combine_is_taofx', 'order_combine_is_problem', 'order_combine_is_short', 'order_combine_is_presell','order_combine_is_problem_reimburse'));

        if (( $cfg_data['order_outo_combine']['rule_status_value'] == 0 && $type == 'byhand') || ($cfg_data['order_outo_combine']['rule_scene_value'] == 0 && $type <> 'byhand' )) {
            if ($is_print_express == 1 || $is_print_sellrecord == 1) {
                return $this->format_ret(-1, '', '已打印的订单不参与合并（快递单打印或发货单打印，系统均认为为已打印）' . $first_order_info['sell_record_code']);
            }
        }
        if (($cfg_data['order_combine_is_change']['rule_status_value'] == 0 && $type == 'byhand') || ($cfg_data['order_combine_is_change']['rule_scene_value'] == 0 && $type <> 'byhand')) {
            if ($is_change_record1 != 0) {
                return $this->format_ret(-1, '', '换货单不参与合并' . $first_order_info['sell_record_code']);
            }
        }
        if (($cfg_data['order_combine_is_split']['rule_status_value'] == 0 && $type == 'byhand') || ($cfg_data['order_combine_is_split']['rule_scene_value'] == 0 && $type <> 'byhand')) {
            if ($is_split_new1 != 0) {
                return $this->format_ret(-1, '', '拆分单订单不参与合并' . $first_order_info['sell_record_code']);
            }
        }
        if (($cfg_data['order_combine_is_houtai']['rule_status_value'] == 0 && $type == 'byhand') || ($cfg_data['order_combine_is_houtai']['rule_scene_value'] == 0 && $type <> 'byhand')) {
            if ($sale_channel_code1 == 'houtai') {
                return $this->format_ret(-1, '', '后台订单不参与合并' . $first_order_info['sell_record_code']);
            }
        }
        if (($cfg_data['order_combine_is_taofx']['rule_status_value'] == 0 && $type == 'byhand') || ($cfg_data['order_combine_is_taofx']['rule_scene_value'] == 0 && $type <> 'byhand')) {
            if ($is_fenxiao1 == 1) {
                return $this->format_ret(-1, '', '淘分销订单不参与合并' . $first_order_info['sell_record_code']);
            }
        }

        
        if (($cfg_data['order_combine_is_problem']['rule_status_value'] == 0 && $type == 'byhand') || ($cfg_data['order_combine_is_problem']['rule_scene_value'] == 0 && $type <> 'byhand')) {
       
                    //买家申请退款(部分退)的问题单参与合并（合并后为问题单）
            if ($is_problem1 != 0) {
             if (($cfg_data['order_combine_is_problem_reimburse']['rule_status_value'] == 1 && $type == 'byhand') || ($cfg_data['order_combine_is_problem_reimburse']['rule_scene_value'] == 1 && $type <> 'byhand')) {

             //     $refund_record = load_model('oms/SellRecordTagModel')->get_problem_refund_by_record($sell_record_code_arr);
                  $no_refund_problem_record = load_model('oms/SellRecordTagModel')->get_problem_no_refund_by_record($sell_record_code_arr);
                  if (!empty($no_refund_problem_record)) {
                     $no_refund_problem_record = array_unique($no_refund_problem_record);
                      return $this->format_ret(-1, '', '问题单不参与合并' . implode(',',$no_refund_problem_record));
                  } 
              }else{
                  $ret_problem_record = load_model('oms/SellRecordTagModel')->get_tag_by_sell_record($sell_record_code_arr,'problem', "sell_record_code");
                  $problem_record = array_column($ret_problem_record['data'], 'sell_record_code');
                  $problem_record = array_unique($problem_record);
                  return $this->format_ret(-1, '', '问题单不参与合并'. implode(',',$problem_record ));
              }
            }
        }
        
        if (($cfg_data['order_combine_is_problem']['rule_status_value'] == 1 && $type == 'byhand') || ($cfg_data['order_combine_is_problem']['rule_scene_value'] == 1 && $type <> 'byhand')) {

            //买家申请退款(部分退)的问题单参与合并（合并后为问题单）
            if ($is_problem1 != 0) {
                if (($cfg_data['order_combine_is_problem_reimburse']['rule_status_value'] == 1 && $type == 'byhand') || ($cfg_data['order_combine_is_problem_reimburse']['rule_scene_value'] == 1 && $type <> 'byhand')) {
                    $refund_record = load_model('oms/SellRecordTagModel')->get_problem_full_refund_by_record($sell_record_code_arr);
                    if (!empty($refund_record)) {
                        return $this->format_ret(-1, '', '包含整单退的问题单' . implode(',',$refund_record));
                    }
                } else {
                    $refund_record = load_model('oms/SellRecordTagModel')->get_problem_have_refund_by_record($sell_record_code_arr);
                    if (!empty($refund_record)) {
                        return $this->format_ret(-1, '', '问题单不参与合并' . implode(',',$refund_record));
                    }
                }
            }
        }


        if (($cfg_data['order_combine_is_short']['rule_status_value'] == 0 && $type == 'byhand') || ($cfg_data['order_combine_is_short']['rule_scene_value'] == 0 && $type <> 'byhand')) {
            if ($lock_inv_status1 != 1) {
                return $this->format_ret(-1, '', '缺货订单不参与合并' . $first_order_info['sell_record_code']);
            }
        }
        if (($cfg_data['order_combine_is_presell']['rule_status_value'] == 0 && $type == 'byhand') || ($cfg_data['order_combine_is_presell']['rule_scene_value'] == 0 && $type <> 'byhand')) {
            if ($is_presell == 1) {
                return $this->format_ret(-1, '', '预售订单不参与合并' . $first_order_info['sell_record_code']);
            }
        }

        // 只允许已付款、确认状态一致、付款方式一致、非问题单、非缺货订单、未通知配货、未发货订单合并 (挂起单可以。换货单可以，合并后一律为换货单.京东订单,货到付款单不参与合并)
        if ($sale_channel_code1 == 'vjia') {
            return $this->format_ret(-1, '', 'vjia订单不参与合并, 主订单：' . $first_order_info['sell_record_code']);
        }
        if ($sale_channel_code1 == 'yougou') {
            return $this->format_ret(-1, '', '优购订单不参与合并, 主订单：' . $first_order_info['sell_record_code']);
        }
        if ($sale_channel_code1 == 'ncm') {
            return $this->format_ret(-1, '', 'NCM订单不参与合并, 主订单：' . $first_order_info['sell_record_code']);
        }
        if ($sale_channel_code1 == 'openshop') {
            return $this->format_ret(-1, '', 'openshop订单不参与合并, 主订单：' . $first_order_info['sell_record_code']);
        }
//        if ($lock_inv_status1 != 1) {
//            return $this->format_ret(-1,'','缺货订单不参与合并, 主订单：' . $first_order_info['sell_record_code']);
//        }
//        if ($is_problem1 != 0) {
//            return $this->format_ret(-1,'','问题单不参与合并, 主订单：' . $first_order_info['sell_record_code']);
//        }
        if ($order_status1 == 3) {
            return $this->format_ret(-1, '', '无效单不参与合并, 主订单：' . $first_order_info['sell_record_code']);
        }

        if ($shipping_status1 >= 4) {
            return $this->format_ret(-1, '', '已发货的不参与合并, 主订单：' . $first_order_info['sell_record_code']);
        }
        if ($pay_code1 == 'cod') {
            return $this->format_ret(-1, '', '货到付款单不参与合并, 主订单：' . $first_order_info['sell_record_code']);
        }
        if ($pay_status1 != 2) {
            return $this->format_ret(-1, '', '未付款单不参与合并, 主订单：' . $first_order_info['sell_record_code']);
        }
        if ($receiver_name1 == '' || $receiver_address1 == '') {
            return $this->format_ret(-1, '', '购买人姓名和收货地址为空不参与合并，主订单：' . $first_order_info['sell_record_code']);
        }
        if ($buyer_name1 == '') {
            return $this->format_ret(-1, '', '购买人昵称(buyer_name)为空不参与合并, 主订单：' . $first_order_info['sell_record_code']);
        }
        // 参数控制（换货单可以参与合并，合并后一律为换货单）
        /*
          if (! @$cl['is_change_record_can_merge']) {
          if ($is_change_record1 != 0) {
          return $this->format_ret(-1,'','换货单不参与合并(自定义参数)， 主订单：' . $first_order_info['sell_record_code']);
          }
          }
          if (! @$cl['combine_split_order']) {
          if ($is_split_new1 != 0) {
          return $this->format_ret(-1,'','拆分订单不参与合并(自定义参数)， 主订单：' . $first_order_info['sell_record_code']);
          }
          } */

        for ($j = 0; $j < count($order_info_arr); $j++) {
            $deal_code2 = trim($order_info_arr[$j]['deal_code']);
            $receiver_name2 = trim($order_info_arr[$j]['receiver_name']);
            $receiver_address2 = $order_info_arr[$j]['receiver_province'] . $order_info_arr[$j]['receiver_city'] . $order_info_arr[$j]['receiver_district'] . trim($order_info_arr[$j]['receiver_addr']);
            $customer_address_id2 = $order_info_arr[$j]['customer_address_id'];
            $shop_code2 = $order_info_arr[$j]['shop_code'];
            $is_fenxiao2 = $order_info_arr[$j]['is_fenxiao'];

            $lock_inv_status2 = (int) $order_info_arr[$j]['lock_inv_status'];
            $is_problem2 = (int) $order_info_arr[$j]['is_problem'];

            $pay_code2 = (string) $order_info_arr[$j]['pay_code'];
            $order_status2 = (int) $order_info_arr[$j]['order_status'];
            $shipping_status2 = (int) $order_info_arr[$j]['shipping_status'];
            $pay_status2 = (int) $order_info_arr[$j]['pay_status'];

            $is_change_record2 = (int) $order_info_arr[$j]['is_change_record'];
            $order_status2 = (int) $order_info_arr[$j]['order_status'];
            $buyer_name2 = (string) $order_info_arr[$j]['buyer_name'];
            $customer_code2 = (string) $order_info_arr[$j]['customer_code'];
            $sale_channel_code2 = (string) $order_info_arr[$j]['sale_channel_code'];
            $store_code2 = (string) $order_info_arr[$j]['store_code'];
            $is_split_new2 = (int) $order_info_arr[$j]['is_split_new'];
            $is_fenxiao2 = (int) $order_info_arr[$j]['is_fenxiao'];
            // 付款状态不一致  付款方式不一致  确认状态不一致   存在挂起、问题  都不允许合并.
            // 如果已经通知配货 、  已发货  都不允许合并  系统应提示
            $ret_check_wms_store = load_model('wms/WmsEntryModel')->check_wms_store($order_info_arr[$j]['store_code']);
            if ($order_info_arr[$j]['shipping_status'] > 0 && $ret_check_wms_store['status'] > 0) {
                return $this->format_ret(-1, '', '订单仓库启用了第三方物流,订单通知配货后不能合并, 从订单：' . $order_info_arr[$j]['sell_record_code']);
            }
            if ($sale_channel_code2 == 'vjia') {
                return $this->format_ret('-1', '', 'vjia订单不参与合并, 从订单：' . $order_info_arr[$j]['sell_record_code']);
            }
            if ($sale_channel_code2 == 'yougou') {
                return $this->format_ret('-1', '', '优购订单不参与合并, 从订单：' . $order_info_arr[$j]['sell_record_code']);
            }
            if ($sale_channel_code2 == 'ncm') {
                return $this->format_ret('-1', '', 'NCM订单不参与合并, 从订单：' . $order_info_arr[$j]['sell_record_code']);
            }
//            if ($lock_inv_status2 != 1) {
//                return $this->format_ret('-1','','缺货订单不参与合并, 从订单：' . $order_info_arr[$j]['sell_record_code']);
//            }
//            if ($is_problem2 != 0) {
//                return $this->format_ret('-1','','问题单不参与合并, 从订单：' . $order_info_arr[$j]['sell_record_code']);
//            }
            if ($order_status2 == 3) {
                return $this->format_ret('-1', '', '无效单不参与合并, 从订单：' . $order_info_arr[$j]['sell_record_code']);
            }

            if ($shipping_status2 >= 4) {
                return $this->format_ret('-1', '', '已发货的不参与合并, 从订单：' . $order_info_arr[$j]['sell_record_code']);
            }
            if ($pay_code2 != $pay_code1) {
                return $this->format_ret('-1', '', '付款方式必须一致, 从订单：' . $order_info_arr[$j]['sell_record_code']);
            }
            if ($pay_status2 != 2) {
                return $this->format_ret('-1', '', '未付款单不参与合并, 从订单：' . $order_info_arr[$j]['sell_record_code']);
            }

            if ($customer_address_id1 != $customer_address_id2) {
                return $this->format_ret('-1', '', '购买人姓名和收货地址必须一致，从订单：' . $order_info_arr[$j]['sell_record_code']);
            }

            if ($customer_code1 != $customer_code2) {
                return $this->format_ret('-1', '', '购买人昵称(buyer_name)必须一致, 从订单：' . $order_info_arr[$j]['sell_record_code']);
            }

            if ($shop_code2 != $shop_code1) {
                return $this->format_ret('-1', '', '所属商店必须一致, 从订单：' . $order_info_arr[$j]['sell_record_code']);
            }
            if ($store_code2 != $store_code1) {
                return $this->format_ret('-1', '', '发货仓库必须一致, 从订单：' . $order_info_arr[$j]['sell_record_code']);
            }
            // 参数控制（换货单可以参与合并，合并后一律为换货单）
            /*
              if (! @$cl['is_change_record_can_merge']) {
              if ($is_change_record1 != 0) {
              return $this->format_ret('-1','','换货单不参与合并(自定义参数)， 从订单：' . $order_info_arr[$j]['sell_record_code']);
              }
              }
              if (! @$cl['combine_split_order']) {
              if ($is_split_new2 != 0) {
              return $this->format_ret('-1','','拆分订单不参与合并(自定义参数)， 从订单：' . $order_info_arr[$j]['sell_record_code']);
              }
              } */
        }
        $data = array('is_change_record_change' => $is_change_record_change, 'order_status_change' => $order_status_change);
        return $this->format_ret(1, $data, '');
    }

    function cli_combine() {
        header("Content-type: text/html; charset=utf-8");
        $wh = load_model('oms/OrderCombineClModel')->get_where_str('rl.');
        $is_create = $this->is_create_data();
        if ($is_create === true) {
            $this->create_combine_data();
        }


//      $sql = "select rl.sell_record_id,rl.sale_channel_code,rl.sell_record_code,rl.deal_code_list,rl.store_code,rl.shop_code,rl.pay_code,rl.buyer_name,rl.receiver_province,rl.receiver_city,rl.receiver_district,rl.receiver_addr,rl.customer_code,rl.receiver_name,rl.receiver_address,rl.seller_remark,rl.buyer_remark,rl.paid_money,rl.receiver_mobile,rl.deal_code,rl.is_fenxiao,rl.fenxiao_code from oms_sell_record rl ";
//                $sql .=" INNER JOIN  oms_sell_record_combine r2 ON  " ;
//                $sql .=" rl.shop_code=r2.shop_code AND rl.store_code=r2.store_code AND rl.pay_code=r2.pay_code AND rl.buyer_name=r2.buyer_name 
//                        AND rl.receiver_name=r2.receiver_name AND rl.receiver_province=r2.receiver_province AND rl.receiver_city=r2.receiver_city 
//                        AND rl.receiver_district=r2.receiver_district AND rl.receiver_addr=r2.receiver_addr  where 1 ".$wh ;
//    
//      $db_data = ctx()->db->get_all($sql);

        $sql = "select rl.sell_record_id,rl.sale_channel_code,rl.sell_record_code,rl.deal_code_list,rl.store_code,rl.shop_code,rl.pay_code,rl.buyer_name,rl.receiver_province,rl.receiver_city,rl.receiver_district,rl.receiver_addr,rl.customer_code,rl.receiver_name,rl.customer_address_id,rl.receiver_address,rl.seller_remark,rl.buyer_remark,rl.paid_money,rl.receiver_mobile,rl.deal_code,rl.is_fenxiao,rl.fenxiao_code from oms_sell_record rl ";
        $sql .=" INNER JOIN  oms_sell_record_combine r2 ON  ";
        $sql .=" rl.shop_code=r2.shop_code AND rl.store_code=r2.store_code AND rl.pay_code=r2.pay_code AND rl.customer_address_id=r2.customer_address_id    where  rl.is_replenish = 0 " . $wh;

        $db_data = ctx()->db->get_all($sql);

        if (empty($db_data)) {
            echo "没有可合并的订单";
            return;
        }
//        $code_arr = array();
//        foreach ($db_data as $key => $record){
//            $code_arr[] = $record['sell_record_code'];
//        }
//        //获取wms回传订单接单成功的订单号（upload_response_flag=10），这样的订单不能合并
//        $code_str = deal_array_with_quote($code_arr);
//        $fileter_sql = "SELECT record_code FROM wms_oms_trade WHERE upload_response_flag = 10 AND record_type = 'sell_record' AND record_code IN($code_str)";
//        $filter_code_arr = $this->db->get_all_col($fileter_sql);
//        foreach ($db_data as $key => $record){
//            if(in_array($record['sell_record_code'], $filter_code_arr)){
//                unset($db_data[$key]);
//            }
//        }
        $ret = load_model('oms/OrderCombineViewModel')->get_combine_data($db_data, 1);
        $data = $ret['data'];
        if (empty($data)) {
            echo "没有可合并的订单";
            return;
        }
        //echo '<hr/>$data<xmp>'.var_export($data,true).'</xmp>';die;
        require_model('oms/OrderCombineModel');
        $mdl = new OrderCombineModel();
        $log = array();
        foreach ($data as $sub_data) {
            try {
                $sell_record_code_list = join(',', $sub_data);
                $ret = $mdl->combine_order($sub_data);
                if ($ret['status'] < 1) {
                    echo $sell_record_code_list . $ret['message'] . "\n";
                } else {
                    echo $sell_record_code_list . '合并成功,新生成订单为' . $ret['data'] . "\n";
                }
            } catch (Exception $e) {
                echo $sell_record_code_list . '合并失败' . $e->getMessage() . "\n";
            }
        }
    }

    function combine_gift_strategy(&$record_data, &$detail_data) {
        $is_combine_gift = $this->is_combine_gift($record_data['shop_code']);
        //没开启升档赠送
        if ($is_combine_gift == 0) {
            return $this->format_ret(-1);
        }

        $no_gift_detail = array();
        $deal_code_arr = array();
        $del_gift_log = '';
        foreach ($detail_data as $val) {
            if ($val['is_gift'] == 0) {
                $no_gift_detail[] = $val;
            } else {
                $barcode = $this->get_barcode_by_sku($val['sku']);
                $del_gift_log .="单据{$val['sell_record_code']}删除赠品:{$barcode}({$val['num']} 件),";
            }
            $deal_code_arr[$val['deal_code']] = $val['deal_code'];
        }
        $deal_code_str = "'" . implode("','", $deal_code_arr) . "'";
        $sql = "select  detail_id,source,tid,price,sum(num) as num,sku_id,goods_barcode,sum(total_fee) as  total_fee,sum(payment) as  payment,sum(avg_money) as  avg_money, sale_mode  from api_order_detail  where tid in({$deal_code_str}) GROUP BY goods_barcode ";
        $trade_detail = $this->db->get_all($sql);
        $this->set_trade_detail($trade_detail);

        $ret = load_model('op/GiftStrategy/GiftStrategyOpModel')->set_trade_gift($record_data, $no_gift_detail, $trade_detail);
        //赠送成功
        if (!empty($ret['data'])) {
            $detail_data = $no_gift_detail;
            //重新维护主表sku数量、商品数量
            $goods_num = 0;
            $sku_num = array();
            foreach ($detail_data as $value) {
                $goods_num += $value['num'];
                $sku_num[$value['sku']] = 1;
            }
            $record_data['goods_num'] = $goods_num;
            $record_data['sku_num'] = count($sku_num);
    

            $ret['message'] .=$del_gift_log;
//            //缺少日志保存
//            $log_data = load_model("op/GiftStrategy/GiftStrategyOpModel")->get_strategy_log();
//            if (!empty($log_data)) {
//                load_model("op/StrategyLogModel")->insert_multi($log_data);
//            }
        }
       load_model('op/GiftStrategy/GiftStrategyOpModel')->save_strategy_log();
        return $ret;
    }

    private function get_barcode_by_sku($sku) {
        $sku_info = load_model('goods/SkuCModel')->get_sku_info($sku, array('barcode'));
        return $sku_info['barcode'];
    }

    private function set_trade_detail(&$trade_detail) {
        foreach ($trade_detail as $key => &$val) {
            $row = $this->get_sku_data_by_barcode($val['goods_barcode']);
            if (!empty($row)) {
                $trade_detail[$key] = array_merge($val, $row);
            }
        }
    }

    private function get_sku_data_by_barcode($barcode) {

        $sql = "select goods_code,sku,barcode from goods_barcode where barcode=:barcode  ";
        $sql_values = array(':barcode' => $barcode);
        $row = $this->db->get_row($sql, $sql_values);
        if (empty($row)) {
            $sql = "select goods_code,sku,barcode from goods_combo_barcode where barcode=:barcode  ";
            $row = $this->db->get_row($sql, $sql_values);
        }
        if(empty($row)){
            $sql = "select b.goods_code,b.sku,b.barcode from goods_barcode_child c INNER JOIN goods_barcode b ON c.sku=b.sku where c.barcode=:barcode  ";
            $row = $this->db->get_row($sql, $sql_values);    
        }
        
        return $row;
    }

    private function is_combine_gift($shop_code) {
        static $combine_gift = null;
        if (!isset($combine_gift[$shop_code])) {
            $combine_gift[$shop_code] = 0;
            $sql = "SELECT count(1) from op_gift_strategy gs
                    INNER JOIN  op_gift_strategy_shop ss ON gs.strategy_code=ss.strategy_code
                    where gs.status=1 AND gs.combine_upshift=1 AND ss.shop_code=:shop_code ";
            $num = $this->db->get_value($sql, array(':shop_code' => $shop_code));
            if ($num > 0) {
                $combine_gift[$shop_code] = 1;
            }
        }
        return $combine_gift[$shop_code];
    }

    function create_combine_data() {

        $where = load_model('oms/OrderCombineClModel')->get_where_str();
        $sql = " TRUNCATE oms_sell_record_combine ";
        $this->db->query($sql);
        $sql = "select MIN(sell_record_id) from oms_sell_record where 1   
 	AND order_status<>3 AND shipping_status < 4  AND pay_status = 2 ";
        $sell_record_id = $this->db->get_value($sql);
        if(empty($sell_record_id)){
            return FALSE;
        }
        
        
        $insert_sql = " insert IGNORE into oms_sell_record_combine (shop_code,store_code,pay_code,customer_address_id,num) ";
        $sql = " select shop_code,store_code,pay_code,customer_address_id ,count(1) as num "
                . "from oms_sell_record where 1 AND sell_record_id>={$sell_record_id}  {$where} GROUP BY shop_code,store_code,pay_code,customer_address_id HAVING num>1 ";
        $this->db->query($insert_sql . $sql);
    }

    function is_create_data() {
//            $tb =  $this->db->get_all_col("show TABLES like 'oms_sell_record_combine'");
//            if(empty($tb)){
//                $sql = "CREATE TABLE `oms_sell_record_combine` (
//                `id` int(11) NOT NULL AUTO_INCREMENT,
//                `shop_code` varchar(128) DEFAULT NULL,
//                `store_code` varchar(128) DEFAULT NULL,
//                `pay_code` varchar(128) DEFAULT NULL,
//                `buyer_name` varchar(128) DEFAULT NULL,
//                `receiver_name` varchar(128) DEFAULT NULL,
//                `receiver_province` varchar(50) DEFAULT NULL,
//                `receiver_city` varchar(50) DEFAULT NULL,
//                `receiver_district` varchar(50) DEFAULT NULL,
//                `receiver_addr` varchar(128) DEFAULT NULL,
//                `num` int(11) DEFAULT NULL,
//                `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
//                PRIMARY KEY (`id`),
//                UNIQUE KEY `_key` (`shop_code`,`pay_code`,`buyer_name`,`receiver_name`,`receiver_province`,`receiver_city`,`receiver_district`,`receiver_addr`) USING BTREE
//              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
//              ";    
//               $this->db->query($sql);
//            }
        $sql = "select MAX(lastchanged) from oms_sell_record_combine";
        $lastchanged = $this->db->get_value($sql);
        $is_create = false;
        if (empty($lastchanged)) {
            $is_create = true;
        } else {
            $now_time = time();
            $lastchanged = strtotime($lastchanged);
            if ($now_time - $lastchanged >= 600) {
                $is_create = true;
            }
        }
        return $is_create;
    }

    /**
     * 验证是否普通分销订单，并且取消结算
     * @param type $order_info_arr
     */
    function check_is_fx_settlement($order_info_arr) {
        $is_fenxiao = 0;
        foreach ($order_info_arr as $key => $value) {
            if ($value['is_fenxiao'] == 2) { // 是否普通分销订单合并
                $is_fenxiao = 2;
                if ($value['order_status'] > 0) { //未确认支持合并
                    return $this->format_ret(-1, '', '分销订单合并只支持未确认的订单' . $value['sell_record_code']);
                }
                //验证是否结算，若结算取消结算
                if ($value['is_fx_settlement'] == 1) {
                    $order_info_arr[$key]['is_fx_settlement'] = 0; //未结算
                    //取消结算并生成扣款单
                    $ret = load_model('oms/SellRecordOptModel')->opt_unsettlement($value['sell_record_code'], array(0 => 'combine'));
                    if ($ret['status'] < 0) {
                        return $this->format_ret(-1, '', $ret['message']);
                    }
                }
            }
        }
        return $this->format_ret($is_fenxiao, $order_info_arr);
    }

    function create_customer_address(&$record_data) {

        $customer_address_array['address'] = $record_data['receiver_addr'];
        $customer_address_array['country'] = $record_data['receiver_country'];
        $customer_address_array['province'] = $record_data['receiver_province'];
        $customer_address_array['city'] = $record_data['receiver_city'];
        $customer_address_array['district'] = $record_data['receiver_district'];
        $customer_address_array['street'] = $record_data['receiver_street'];
        $customer_address_array['tel'] = $record_data['receiver_mobile'];
        $customer_address_array['home_tel'] = $record_data['receiver_phone'];
        $customer_address_array['name'] = $record_data['receiver_name'];
       // $customer_address_array['customer_code'] = $record_data['customer_code'];
        $customer_address_array['is_add_time'] = date('Y-m-d H:i:s');
        $customer_address_array['customer_name'] =$record_data['buyer_name'];
        $customer_address_array['source'] =$record_data['sale_channel_code'];
        $ret_create = load_model('crm/CustomerOptModel')->handle_customer($customer_address_array);

        if ($ret_create['status'] < 1) {
            $this->encrypt_status = false;
            return array();
        }
        $record_data['customer_address_id'] = $ret_create['data']['customer_address_id'];
        $record_data['customer_code'] = $ret_create['data']['customer_code'];
        $customer_address = load_model('crm/CustomerOptModel')->get_customer_address($record_data['customer_address_id']);
        $record_data['receiver_addr'] = $customer_address['address'];
        $record_data['receiver_phone'] = $customer_address['home_tel'];
        $record_data['receiver_name'] = $customer_address['name'];
        $record_data['receiver_mobile'] = $customer_address['tel'];

        $country = oms_tb_val('base_area', 'name', array('id' => $record_data['receiver_country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $record_data['receiver_province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $record_data['receiver_city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $record_data['receiver_district']));
        $street = oms_tb_val('base_area', 'name', array('id' => $record_data['receiver_street']));
        $record_data['receiver_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $record_data['receiver_addr'];
    }

}
