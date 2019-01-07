<?php

/**
 * 订单拆分基类
 * 2014/12/29
 * @author jia.ceng
 */
require_lang("oms");

class OrderSplitBaseModel extends TbModel {

    public function order_split_logic($split_arr, $sell_record, $sell_record_detail_list, $skip_lock_check = 0, $add_log_msg = '', $skip_problem_check = 0) {
        //问题单可以拆分
        $is_problem = $sell_record['is_problem'];
        $sell_record_code = $sell_record['sell_record_code'];
        //判断主单据是否满足拆分条件
        $check_flag = $this->check_condition($sell_record, $skip_problem_check);
        //echo '<hr/>$arr<xmp>'.var_export($check_flag,true).'</xmp>';die;
        if ($check_flag['status'] < 0) {
            return $check_flag;
        }

        $sell_record_detail_map = array();
        foreach ($sell_record_detail_list as $k => $sell_record_detail) {
            $detail_id = $sell_record_detail['sell_record_detail_id'];
            $sell_record_detail['sub_num'] = $sell_record_detail['num'];
            $sell_record_detail['sub_payment_ft'] = 0;
            $sell_record_detail_map[$detail_id] = $sell_record_detail;
        }

        //拆分明细
        $new_sell_detail = $this->split_detail($sell_record_detail_map, $split_arr);
        //根据商品是否有货，计划发货时间数据，排序新订单，取有货的计划发货时间最小的单(明细)当作主单
        $new_sell_detail = $this->sort_split_detail($sell_record, $new_sell_detail);

        //echo '<hr/>$new_sell_detail<xmp>'.var_export($new_sell_detail,true).'</xmp>';die;
        //拼接新单据
        $new_sell_record_info = $this->joint_record($new_sell_detail, $sell_record);
        //echo '<hr/>$new_sell_record_info<xmp>'.var_export($new_sell_record_info,true).'</xmp>';die;

        $this->begin_trans();
        try {
            //作废原单/跳过作废权限判断
            $ret = load_model("oms/SellRecordOptModel")->opt_cancel($sell_record['sell_record_code'], $skip_lock_check, '', 1);
            if ($ret['status'] != '1') {
                $this->rollback();
                return $ret;
            }
            //问题单组装数据
            if ($is_problem == 1) {
                $sql = "SELECT tag_type,tag_v,tag_desc FROM oms_sell_record_tag WHERE sell_record_code='{$sell_record_code}' AND tag_type='problem'";
                $problem_tag = $this->db->get_all($sql);
            }
            /*
              echo '<hr/>$new_info<xmp>'.var_export($new_sell_record_info,true).'</xmp>';
              die; */
            //生成新单
            $split_sell_record_code = array();
            $split_sell_record_info = array();
            foreach ($new_sell_record_info as & $new_info) {
                $new_info['sell_record_code'] = load_model("oms/SellRecordModel")->new_code();
                $new_info['goods_weigh'] = (load_model('op/PolicyExpressModel')->get_record_detail_goods_weigh($new_info['mx'])) * 0.001;
                $result = $this->db->insert('oms_sell_record', $new_info);
                if (!$result) {
                    $this->rollback();
                    return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_ERROR');
                }
                if($new_info['invoice_type'] == 'vat_invoice') { //如果是增值税发票，把发票信息带过来
                    $sql = "SELECT company_name,taxpayers_code,registered_country,registered_province,registered_city,registered_district,registered_street,registered_addr,phone,bank,bank_account FROM oms_sell_invoice WHERE sell_record_code = :sell_record_code ";
                    $invoice_arr = $this->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
                    $ret = load_model('oms/SellRecordModel')->insert_vat_invoict($new_info['sell_record_code'], $invoice_arr);
                    if($ret['status'] < 0) {
                        $this->rollback();
                        return $this->format_ret(-1, '', '生成合并后订单发票信息失败');
                    }
                }
                //问题单设问
                if ($is_problem == 1) {
                    foreach ($problem_tag as &$value) {
                        $value['sell_record_code'] = $new_info['sell_record_code'];
                    }
                    $ret = $this->insert_multi_exp('oms_sell_record_tag', $problem_tag);
                    if ($ret['status'] < 0) {
                        $this->rollback();
                        return $this->format_ret(-1, '', '订单设为问题单出错');
                    }
                }

                foreach ($new_info['mx'] as &$new_detail) {
                    $new_detail['sell_record_code'] = $new_info['sell_record_code'];
                    $result = $this->db->insert('oms_sell_record_detail', $new_detail);
                    if (!$result) {
                        $this->rollback();
                        return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_DETAIL_ERROR');
                    }
                }
                $new_mx_arr = load_model('oms/SellRecordOptModel')->get_detail_list_by_code($new_info['sell_record_code']);
                //新单占用库存
                load_model("oms/SellRecordOptModel")->set_sell_record_is_lock($new_info['sell_record_code'], false);

                $ret = load_model("oms/SellRecordOptModel")->lock_detail($new_info, $new_mx_arr, 1); //重新锁定

                if ($ret['status'] < 1) {
                    $this->rollback();
                    $is_short = $this->is_short_inv($new_mx_arr, $new_info['store_code']);
                    $ret['message'] .= $is_short;

                    return $ret;
                }
                load_model('oms/SellRecordActionModel')->add_action_info($new_info, '拆分生成新单', '此订单是' . load_model("oms/SellRecordOptModel")->sell_record_code_href($sell_record['sell_record_code']) . '拆分生成的新单。' . $add_log_msg);
                $split_sell_record_code[] = $new_info['sell_record_code'];
            }
            //更新旧单据
            $ret = load_model("oms/SellRecordOptModel")->update(array("is_split" => 1, "split_new_orders" => implode(",", $split_sell_record_code)), array('sell_record_code' => $sell_record['sell_record_code']));
            $log_split_sell_record_code = array();
            foreach ($split_sell_record_code as $code) {
                $log_split_sell_record_code[] = load_model("oms/SellRecordOptModel")->sell_record_code_href($code);
            }
            //当生成订单号多余三个后每三个换一次行,方便操作日志显示
            $arr_num = count($log_split_sell_record_code);
            if ($arr_num <= 3) {
                load_model('oms/SellRecordActionModel')->add_action_info($sell_record, '拆分订单', '订单拆分生成' . implode(",", $log_split_sell_record_code) . '.' . $add_log_msg);
            } else {
                for ($i = 0; $i < $arr_num; $i++) {
                    if (($i + 1) % 3 === 0) {
                        $log_split_sell_record_code[$i] = $log_split_sell_record_code[$i] . "</br>";
                    }
                }
                load_model('oms/SellRecordActionModel')->add_action_info($sell_record, '拆分订单', '订单拆分生成' . implode(",", $log_split_sell_record_code) . $add_log_msg);
            }
            $this->commit();
            return $this->format_ret(1, $new_sell_record_info);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    private function is_short_inv($new_mx_arr, $store_code) {
        $error_str = '';
        foreach ($new_mx_arr as $mx) {
            $sql = "select lock_num,stock_num from goods_inv where sku=:sku and store_code = :store_code";
            $inv_num_row = $this->db->get_row($sql, array(':sku' => $mx['sku'], ':store_code' => $store_code));
            if ($inv_num_row['stock_num'] < $inv_num_row['lock_num']) {
                $error_str .= $mx['sku'] . ',';
            }
        }
        if (!empty($error_str)) {
            $error_str = rtrim($error_str, ',');
            $error_str .= '库存不足';
        }
        return $error_str;
    }

    /**
     * 判断条件是否满足
     * @param type $sell_record
     * @return boolean
     */
    function check_condition($record, $skip_problem_check = 0) {
        $sysuser = load_model('oms/SellRecordOptModel')->sys_user();
        if ($record['pay_type'] == 'cod') {
            return $this->format_ret(-1, '', '货到付款单的订不能进行此操作');
        }
        if ($record['pay_status'] != 2) {
            return $this->format_ret(-1, '', '未付款的订单不能进行此操作');
        }
        if ($record['order_status'] != 0) {
            return $this->format_ret(-1, '', '只有未确认的订单才能进行此操作');
        }
//	    if ($record['is_problem'] > 0 && $skip_problem_check == 0){
//		    return $this->format_ret(-1,'','问提单不能进行此操作');		    
//	    }	
        if ($record['is_pending'] > 0) {
            return $this->format_ret(-1, '', '挂起订单不能进行此操作');
        }
        if ($record['is_lock'] > 0 && $sysuser['user_code'] != $record['is_lock_person'] && $sysuser['is_manage'] != 1) {
            return $this->format_ret(-1, '', '锁定订单不能进行此操作');
        }
        return $this->format_ret(1);
    }

    function sort_split_detail($sell_record, $new_sell_record_detail) {
        $result = array();
        foreach ($new_sell_record_detail as $key => $sub_detail) {
            $ret = load_model('oms/SellRecordOptModel')->js_sell_plan_send_time($sell_record, $sub_detail['data']);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $plan_send_time = $ret['data']['plan_send_time'];
            $sub_detail['data'] = $ret['data']['mx'];

            $stock_out_tag = 0; //0 有货 1缺货
            foreach ($sub_detail['data'] as $ks => $row) {
                if ($row['num'] > $row['yet_lock_num']) {
                    $stock_out_tag = 1;
                    break;
                }
            }
            $stock_tag = 'sort_' . $stock_out_tag . '_' . strtotime($plan_send_time) . '_' . $key;
            $sub_detail['plan_send_time'] = $plan_send_time;
            $result[$stock_tag] = $sub_detail;
        }

        ksort($result);
        //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';die;
        return $result;
    }

    /**
     * 拆分明细
     * @param type $sell_record_detail_map 明细列表
     * @param type $split_arr 要拆分成的格式
     * @return type 返回拆分后的明细
     */
    private function split_detail($sell_record_detail_map, $split_arr) {
        $new_sell_record_detail = array();
        $count = 0;
        foreach ($split_arr as $k => $sub_split) {
            $store_code = $sub_split['store_code'];
            foreach ($sub_split['data'] as $sub_row) {
                $sell_record_detail_id = $sub_row['sell_record_detail_id'];
                $num = $sub_row['num'];
                $sell_record_detail = $sell_record_detail_map[$sell_record_detail_id];
                $_yet_lock_num = $sell_record_detail['lock_num']; //已经锁定了多少件
                //判断拆分程度，明细级是否拆分
                if ($sell_record_detail['num'] > $num) {
                    //判断是否为该商品的最后一个
                    $sell_record_detail_map[$sell_record_detail_id]['sub_num'] -= $num;
                    $sell_record_detail['yet_lock_num'] = $_yet_lock_num > $num ? $num : $_yet_lock_num;
                    $sell_record_detail_map[$sell_record_detail_id]['lock_num'] -= $sell_record_detail['lock_num'];
                    if ($sell_record_detail['sub_num'] > $num) {
                        //根据数量占比分配金额
                        $cur_payment_ft = (float) $sell_record_detail['avg_money'] * ($num / $sell_record_detail['num']);
                        $cur_payment_ft = number_format($cur_payment_ft, 2, '.', '');
                        $taobao_fx_amount = (float) $num * $sell_record_detail['trade_price'];
                        $taobao_fx_amount = number_format($taobao_fx_amount, 2, '.', '');
                    } else {
                        //最后一个直接减去前面所有金额之和
                        $cur_payment_ft = bcsub($sell_record_detail['avg_money'], $sell_record_detail['sub_payment_ft'], 2);
                        $taobao_fx_amount = (float) $num * $sell_record_detail['trade_price'];
                        $taobao_fx_amount = number_format($taobao_fx_amount, 2, '.', '');
                    }
                } else {
                    //明细不用拆分，金额不必分配
                    $cur_payment_ft = (float) $sell_record_detail['avg_money'];
                    $sell_record_detail['yet_lock_num'] = $_yet_lock_num;
                    $taobao_fx_amount = (float) $num * $sell_record_detail['trade_price'];
                    $taobao_fx_amount = number_format($taobao_fx_amount, 2, '.', '');
                }
                //累计已经计算出的金额，为最后一个相减做准备
                $_sub_payment_ft = $sell_record_detail_map[$sell_record_detail_id]['sub_payment_ft'];
                $sell_record_detail_map[$sell_record_detail_id]['sub_payment_ft'] = bcadd($_sub_payment_ft, $cur_payment_ft, 2);
                //形成新的明细
                $sell_record_detail['num'] = $num;
                $sell_record_detail['avg_money'] = $cur_payment_ft;
                $sell_record_detail['fx_amount'] = $taobao_fx_amount;
                $new_sell_record_detail[$count]['data'][] = $sell_record_detail;
                $new_sell_record_detail[$count]['store_code'] = $store_code;
            }
            $count++;
        }
        return $new_sell_record_detail;
    }

    /**
     * 拼接单据
     * @param type $new_sell_detail
     * @param type $sell_record
     */
    private function joint_record($new_sell_detail, $sell_record) {
        $flag = 0;
        $result = array();
        $_paid_money = $sell_record['paid_money'];
        $_express_money = $sell_record['express_money'];
        $_invoice_money = $sell_record['invoice_money'];
        $_avg_money = $sell_record['payable_money']-$sell_record['express_money']-$sell_record['delivery_money'];
        $arr_count = count($new_sell_detail);
        $last_money = $_invoice_money;
        //优惠金额
        $_count_fee = $sell_record['coupon_fee'];
        $last_fee = $_count_fee;
        $num = 0;
        foreach ($new_sell_detail as $key => &$info) {
            $num++;
            if ($flag == 0) {
                $add_record_fld = 'is_problem,pay_time,pay_status,order_status,shipping_status,invoice_type,invoice_title,invoice_content,invoice_money,invoice_status,payable_money,order_money,goods_money,must_occupy_inv,express_money,delivery_money,plan_send_time,is_fenxiao,is_buyer_remark,is_seller_remark,is_rush,alipay_no,sale_mode,alipay_no,invoice_number,store_remark';
            } else {
                $add_record_fld = 'is_problem,pay_time,pay_status,order_status,shipping_status,payable_money,order_money,goods_money,must_occupy_inv,plan_send_time,is_fenxiao,is_buyer_remark,is_seller_remark,is_rush,alipay_no,sale_mode,alipay_no,invoice_type,invoice_title,invoice_content,invoice_money,invoice_status,invoice_number,store_remark';
            }
            $_info = load_model('oms/SellRecordOptModel')->get_sell_base_info($sell_record, $info['data'], $add_record_fld, 'plan_send_time');
            $_info = load_model('oms/SellRecordOptModel')->js_record_price($_info, $_info['mx']);
            //拆单开票金额按商品的均摊金额进行均摊
            if($_avg_money > 0){
                if($num < $arr_count){
                    $_info_avg_money = $_info['data']['payable_money']-$_info['data']['express_money']-$_info['data']['delivery_money'];
                    $_info['data']['invoice_money'] = number_format(($_info_avg_money/$_avg_money)*$_invoice_money,2);
                    $last_money -= $_info['data']['invoice_money'];
                }elseif($num = $arr_count){
                    $_info['data']['invoice_money'] = $last_money;
                }
            }
            //拆单时优惠金额需要
            if(($sell_record['sale_channel_code'] === 'taobao' || $sell_record['sale_channel_code'] === 'jingdong') && $_avg_money > 0){
                if($num < $arr_count){
                    $_info['data']['coupon_fee'] = number_format(($_info_avg_money/$_avg_money)*$_count_fee,2);
                    $last_fee -= $_info['data']['coupon_fee'];
                }elseif($num = $arr_count){
                    $_info['data']['coupon_fee'] = $last_fee;
                }
            }
            if ($_info['status'] < 0) {
                return $_info;
            }
            $_t = time() . rand(10, 999) . $flag;
            $_info['data']['deal_code'] = load_model('oms/SellRecordOptModel')->get_guid_deal_code($sell_record['deal_code'], $_t);
            $_info['data']['is_split_new'] = 1;
            $_info['data']['split_order'] = $sell_record['sell_record_code'];
            $_info['data']['create_time'] = date('Y-m-d H:i:s');
            $_info['data']['store_code'] = $info['store_code'];
            if ($_info['data']['is_fenxiao'] == 1) {
                $_info['data']['is_fx_settlement'] = 1;
            }
            $_info['data']['sale_mode'] = load_model('oms/SellRecordOptModel')->get_sale_mode($_info['data']['mx']);
            if ($flag != 0) {
                $_info['data']['payable_money'] -= $_info['data']['express_money'];
                $_info['data']['express_money'] = 0;
                $_info['data']['fx_express_money'] = 0;
            }
            if ($_paid_money > 0) {
                if ($arr_count - 1 == $flag) {
                    $_info['data']['paid_money'] = $_paid_money;
                } else {
                    $_info['data']['paid_money'] = $_info['data']['payable_money'];
                    $_paid_money -= $_info['data']['payable_money'];
                }
            }
            $result[$flag] = $_info['data'];
            $flag++;
        }
        return $result;
    }

}
