<?php

require_model('oms/SellRecordModel');
require_lang('oms');
require_model('prm/InvOpModel');
require_lib('apiclient/TaobaoClient');

class SellRecordOptModel extends SellRecordModel {

    private $msg = '';
    public $is_all_lock = 0;

    function edit_baseinfo_check($record, $detail, $sysuser) {
        /* if (false == get_operate_purview("order/sell_record/edit_base_info")) {
          return $this->return_value(-1, '无订单编辑基本信息权限');
         */
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] == 0) {
            return $this->return_value(-1, '未锁定订单不能操作');
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }

        if ($record['order_status'] > 0) {
            return $this->return_value(-1, '只有未确认的订单才能操作');
        }
        if (($record['is_fenxiao'] == 1 || $record['is_fenxiao'] == 2) && $record['is_fx_settlement'] == 1) {
            return $this->return_value(-1, '已结算订单不能操作');
        }

        return $this->return_value(1, '');
    }

    function edit_express_check($record, $detail, $sysuser) {
        return $this->edit_shipping_check($record, $detail, $sysuser);
    }

    function edit_express($sellRecordCode, $arr) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = array(); //$this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->edit_express_check($record, $detail, $sys_user);

        if ($check['status'] != '1') {
            return $check;
        }

        return $this->save_component($sellRecordCode, 'shipping', $arr);
    }

    function edit_express_code_check($record, $detail, $sysuser, $skip_lock_check) {
        return $this->edit_shipping_check($record, $detail, $sysuser, $skip_lock_check);
    }

    function edit_express_code($sellRecordCode, $expressCode, $skip_lock_check = 0) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = array(); //$this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        //已结算分销订单不允许修改配送方式
//        if($record['is_fx_settlement'] == '1') {
//            return $this->format_ret(-1, '', '已结算分销订单不允许修改配送方式');
//        }

        // 订单确认前,需检查是否允许修改,
        // 订单确认后,允许直接修改.
        if ($record['order_status'] < 1) {
            $check = $this->edit_express_code_check($record, $detail, $sys_user, $skip_lock_check);
            if ($check['status'] != '1') {
                return $check;
            }
        }

        $d = array('express_code' => $expressCode, 'express_no' => '');

        //如果已经生成发货单, 同时修改发货单配送方式
        if ($record['waves_record_id'] > 0) {
            $a = array('waves_record_id' => $record['waves_record_id'], 'sell_record_code' => $record['sell_record_code']);
            $this->db->update('oms_deliver_record', $d, $a);
        }
        //修改波次生成临时表
        if ($record['waves_record_id'] == 0 && $record['shipping_status'] == 1) {
            $this->db->update('oms_sell_record_notice', $d, array('sell_record_code' => $record['sell_record_code']));
        }

        // 同时更新订单表
        $sql = "update oms_sell_record set  express_no = '', is_print_express = 0
        where sell_record_code = :sell_record_code";
        $this->query($sql, array('sell_record_code' => $sellRecordCode));

        //$ret =$this->save_component($sellRecordCode, 'express_code', $d);
        //存在热敏单号,取消云栈
        $param_code = array('opt_confirm_get_cainiao');
        $sys_params = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
        if (!empty($record['express_no']) && !empty($record['express_data']) && $sys_params['opt_confirm_get_cainiao'] == 1) {
            $this->cancle_cainiao_wlb_waybil_action($record);
        }
        return $this->format_ret(1);
    }

    function edit_express_no_check($record, $detail, $sysuser) {
        return $this->edit_shipping_check($record, $detail, $sysuser);
    }

    function edit_express_no($sellRecordCode, $expressNo, $check_the_no) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = array(); //$this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->edit_express_no_check($record, $detail, $sys_user);

        if ($check['status'] != '1') {
            return $check;
        }

        if ($check_the_no) {
            $s = $this->check_express_no($record['express_code'], $record['express_no']);
            if ($s == false) {
                return $this->return_value(-1, "快递单号不合法: " . $record['express_no']);
            }
        }

        return $this->save_component($sellRecordCode,'shipping', array('express_no' => $expressNo));
    }

    function edit_shipping_check($record, $detail, $sysuser, $skip_lock_check = 0) {
        /* if (false == get_operate_purview("order/sell_record/edit_shipping")) {
          return $this->return_value(-1, '无订单编辑配送信息权限');
         */
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('opt_edit_express_code')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] == 0 && $skip_lock_check == 0) {
            return $this->return_value(-1, '未锁定订单不能操作');
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        //if ($record['is_pending'] == 1) {
        //    return $this->return_value(-1, '挂起订单不能操作');
        //}

        if ($record['order_status'] == 3) {
            return $this->return_value(-1, '已作废订单不能操作');
        }

        if ($record['order_status'] == 5) {
            return $this->return_value(-1, '已完成订单不能操作');
        }

        return $this->return_value(1, '');
    }

    function edit_money_check($record, $detail, $sysuser) {
        /* if (false == get_operate_purview("order/sell_record/edit_money")) {
          return $this->return_value(-1, '无订单编辑金额权限');
         */
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] == 0) {
            return $this->return_value(-1, '未锁定订单不能操作');
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }

        if (in_array($record['order_status'], array(1, 3, 5))) {
            return $this->return_value(-1, '已确定, 已完成,作废订单不能操作');
        }
        if (($record['is_fenxiao'] == 1 || $record['is_fenxiao'] == 2) && $record['is_fx_settlement'] == 1) {
            return $this->return_value(-1, '已结算订单不能操作');
        }

        return $this->return_value(1, '');
    }

    function edit_fx_money_check($record, $detail, $sysuser) {
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] == 0) {
            return $this->return_value(-1, '未锁定订单不能操作');
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        $login_type = CTX()->get_session('login_type');
        if ($login_type == 2) {
            return $this->return_value(-1, '分销商登录不允许操作');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_fx_settlement'] == 1) {
            return $this->return_value(-1, '已结算订单不能操作');
        }
        if (in_array($record['order_status'], array(1, 3, 5))) {
            return $this->return_value(-1, '已确定, 已完成,作废订单不能操作');
        }
        return $this->return_value(1, '');
    }

    function add_detail_check($record, $detail, $sysuser) {
        return $this->edit_detail_check($record, $detail, $sysuser);
    }

    function add_combo_detail_check($record, $detail, $sysuser) {
        return $this->edit_detail_check($record, $detail, $sysuser);
    }

    function edit_detail_check($record, $detail, $sysuser) {
        /* if (false == get_operate_purview("order/sell_record/edit_goods")) {
          return $this->return_value(-1, '无订单编辑商品权限');
         */
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
//		if ($record['is_lock'] == 0) {
//			return $this->return_value(-1, '未锁定订单不能操作');
//		}
//		if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
//			return $this->return_value(-1, '已锁定订单不能操作');
//		}
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if (($record['is_fenxiao'] == 1 || $record['is_fenxiao'] == 2) && $record['is_fx_settlement'] == 1) {
            return $this->return_value(-1, '已结算订单不能操作');
        }

        if (in_array($record['order_status'], array(1, 3, 5))) {
            return $this->return_value(-1, '已确定, 已完成,作废订单不能操作');
        }

        return $this->return_value(1, '');
    }

    /**
     * 补单
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    public function opt_replenish_check($record, $detail, $sysuser,$opt_replenish_check = 0){
        //#############权限
        if ($opt_replenish_check == 0) {
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_replenish')) {
                return $this->return_value(-1, "无权访问");
            }
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['shipping_status'] != 4) {
            return $this->return_value(-1, '只有已发货的订单才能操作');
        }
        return $this->return_value(1, '');
    }
    /*
     * 补单
     */
    public function opt_replenish($sell_record_code){
        $record = $this->get_record_by_code($sell_record_code);
        $detail = $this->get_detail_list_by_code($sell_record_code);
        $sys_user = $this->sys_user();
        $check = $this->opt_replenish_check($record, $detail, $sys_user, 'opt_replenish');
        if($check['status'] != 1) return $check;
        $replenish_key_arr = array(
            'deal_code',
            'deal_code_list',
            'sale_channel_code',
            'alipay_no',
            'store_code',
            'shop_code',
            'user_code',
            'pay_type',
            'pay_code',
            'customer_code',
            'customer_address_id',
            'buyer_name',
            'receiver_name',
            'receiver_country',
            'receiver_province',
            'receiver_city',
            'receiver_district',
            'receiver_street',
            'receiver_address',
            'receiver_addr',
            'receiver_zip_code',
            'receiver_mobile',
            'receiver_phone',
            'receiver_email',
            'express_code',
            'plan_send_time',
            'goods_num',
            'sku_num',
            'goods_weigh',
            'real_weigh',
            'invoice_type',
            'invoice_title',
            'invoice_content',
            'invoice_status',
            'invoice_number',
            'taxpayers_code',
            'buyer_remark',
            'seller_remark',
            'seller_flag',
            'order_remark',
            'store_remark',
            'is_fenxiao',
            'fenxiao_id',
            'fenxiao_name',
            'fenxiao_power',
            'fenxiao_code',
            'yfx_fee',
            'create_time',
            'record_time',
            'is_jhs',
            'is_buyer_remark',
            'is_seller_remark',
            'is_print_invoice',
            'buyer_alipay_no',
            'sale_mode',
            'is_print_warranty',
            'invoice_title_type'
        );
        $replenish_arr = array();
        foreach ($replenish_key_arr as $key){
            if(isset($record[$key])){
                $replenish_arr[$key] = $record[$key];
             }
        }
        $new_record_code = load_model('oms/SellRecordOptModel')->new_code();
        $replenish_detail_key_arr = array(
            'deal_code',
            'sub_deal_code',
            'goods_code',
            'spec1_code',
            'spec2_code',
            'sku_id',
            'sku',
            'combo_sku',
            'barcode',
            'num',
            'return_num',
            'goods_weigh',
            'platform_spec',
            'lock_inv_status',
            'is_gift',
            'sale_mode',
            'delivery_mode',
            'delivery_days_or_time',
            'plan_send_time',
            'is_delete',
            'api_refund_num',
            'api_refund_desc',
            'lastchanged',
            'pic_path',
            'return_money',
            'platform_name'
        );
        $replenish_arr['sell_record_code'] = $new_record_code;
        $replenish_arr['is_replenish'] = 1;
        $replenish_arr['is_replenish_from'] = $sell_record_code;
        $replenish_arr['deal_code'] = $this->get_guid_deal_code($replenish_arr['deal_code']);
        $replenish_detail = array();
        foreach ($detail as $k=>$value){
            foreach ($replenish_detail_key_arr as $key){
                if(isset($value[$key])){
                    $replenish_detail[$k][$key] = $value[$key];
                }
            }
            $replenish_detail[$k]['sell_record_code'] = $new_record_code;
        }
        $this->begin_trans();
        try {
            $record_ret = $this->db->insert('oms_sell_record', $replenish_arr);
            if($record_ret !== true){
                $this->rollback();
                return $this->format_ret(-1,'','创建订单失败');
            }
            $detail_ret = $this->db->insert('oms_sell_record_detail',$replenish_detail);
            if($detail_ret !== true){
                $this->rollback();
                return $this->format_ret(-1,'','创建订单明细失败');
            }
            $this->commit();
            //补单日志
            $this->add_action($sell_record_code, '补单', "补单新订单编号：" . $this->sell_record_code_href($new_record_code));
            $this->add_action($new_record_code, '创建订单', "从" . $this->sell_record_code_href($sell_record_code) . "订单补单");
            $sql = "SELECT 
                        is_company,
                        customer_code,
                        buyer_name,
                        receiver_name,
                        shop_code,
                        is_company,
                        company_name,
                        taxpayers_code,
                        registered_country,
                        registered_province,
                        registered_city,
                        registered_district,
                        registered_street,registered_addr,
                        registered_address,
                        phone,
                        bank,
                        bank_account,
                        status,
                        invoice_type,
                        invoice_title,
                        invoice_content,
                        invoice_number,
                        receiver_address,
                        receiver_email
                    FROM oms_sell_invoice WHERE sell_record_code = :sell_record_code ";

            $invoice_arr = $this->db->get_row($sql,array(':sell_record_code'=>$sell_record_code));
            $invoice_arr['sell_record_code'] = $new_record_code;
            $invoice_arr['deal_code'] = $replenish_arr['deal_code'];
            $invoice_arr['deal_code_list'] = $replenish_arr['deal_code_list'];
            $this->db->insert('oms_sell_invoice',$invoice_arr);
            return $this->format_ret(1, $new_record_code);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function opt_delete_detail($sellRecordCode, $sellRecordDetailId, $is_gift = 0, $action_bath) {
        $record = $this->get_record_by_code($sellRecordCode, 'order_status,is_fenxiao,is_fx_settlement,is_replenish');
        if ($record['order_status'] != 0) {
            return $this->format_ret(-1, '', '订单已确认，不能删除商品');
        }
        if($record['is_fenxiao'] == 2 && $record['is_fx_settlement'] == 1) {
            return $this->format_ret(-1, '', '订单已结算，不能删除商品');            
        }
        $record_detail = $this->get_detail_list_by_code($sellRecordCode, 'sell_record_detail_id');
        if (empty($record_detail[$sellRecordDetailId])) {
            return $this->format_ret(1);
        }

        $this->begin_trans();
        try {
            $where = "";
            if ($is_gift == 1) {
                $where = "and is_gift = 1";
            }
            $detail_sql = "select * from oms_sell_record_detail where sell_record_detail_id = :sell_record_detail_id " . $where . "";
            $oms_sell_detail = $this->db->get_row($detail_sql, array('sell_record_detail_id' => $sellRecordDetailId));
            $sql = "delete from oms_sell_record_detail where sell_record_detail_id = :sell_record_detail_id " . $where . "";
            $result = $this->db->query($sql, array('sell_record_detail_id' => $sellRecordDetailId));
            if ($result === FALSE) {
                $this->rollback();
                return $this->format_ret("-1", '', 'SELL_RECORD_DETAIL_NO_DATA');
            }
            $ret = $this->edit_detail_after_flush_data(null, $record_detail, null, $sellRecordCode);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            $barcode_info = load_model('goods/SkuCModel')->get_sku_info($oms_sell_detail['sku'], array('barcode'));
            $log_msg = "商品条码:" . $barcode_info['barcode'] . ';数量:' . $oms_sell_detail['num'] . ';均摊金额' . $oms_sell_detail['avg_money'];
            if ($ret['status'] == 1) {
                $log_msg .= " 实物库存锁定：" . $ret['message'];
            }
            if ($action_bath == "批量") {
                $this->add_action($sellRecordCode, "批量删除商品", $log_msg);
            } else {
                $this->add_action($sellRecordCode, "删除商品", $log_msg);
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '保存失败:' . $e->getMessage());
        }
    }

    /*
     * 删除退款商品
     */

    function opt_delete_return_detail($sellRecordCode, $sellRecordDetailId) {
        $this->begin_trans();
        $record_detail = $this->get_detail_list_by_code($sellRecordCode, 'sell_record_detail_id');
        if (empty($record_detail[$sellRecordDetailId])) {
            return $this->format_ret(1);
        }

        try {
            $detail_sql = "select * from oms_sell_record_detail where sell_record_detail_id = :sell_record_detail_id ";
            $oms_sell_detail = $this->db->get_row($detail_sql, array('sell_record_detail_id' => $sellRecordDetailId));
            $sql = "delete from oms_sell_record_detail where sell_record_detail_id = :sell_record_detail_id ";
            $result = $this->db->query($sql, array('sell_record_detail_id' => $sellRecordDetailId));
            if ($result === FALSE) {
                $this->rollback();
                return $this->format_ret("-1", '', 'SELL_RECORD_DETAIL_NO_DATA');
            }
            //刷新订单数据
            $ret = $this->edit_detail_after_flush_data(null, $record_detail, null, $sellRecordCode);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            $barcode_info = load_model('goods/SkuCModel')->get_sku_info($oms_sell_detail['sku'], array('barcode'));
            $log_msg = "商品条码:" . $barcode_info['barcode'] . ';数量:' . $oms_sell_detail['num'] . ';均摊金额' . $oms_sell_detail['avg_money'];
            if ($ret['status'] == 1) {
                $log_msg .= " 实物库存锁定：" . $ret['message'];
            }
            $this->add_action($sellRecordCode, "批量删除退款商品", $log_msg);
            $this->commit();
            return array('status' => 1, 'message' => '');
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '保存失败:' . $e->getMessage());
        }
    }

    function opt_save_detail($sellRecordCode, $sellRecordDetailId, $num, $avg_money, $deal_code, $fx_amount = 0, $add_num = 0, $add_avg_money = 0, $action_bath) {
        if ($num <= 0) {
            return array('status' => -1, 'message' => '订单商品数量必须大于0');
        }

        $sell_record = $this->get_record_by_code($sellRecordCode);
        if (empty($sell_record)) {
            return array('status' => -1, 'message' => '订单不存在');
        }
        if($sell_record['is_fenxiao'] == 2 && $sell_record['is_fx_settlement'] == 1) {
            return array('status' => -1, 'message' => '订单已结算');
        }

        $record_detail = $this->get_detail_list_by_code($sellRecordCode, 'sell_record_detail_id');
        $cur_detail = $record_detail[$sellRecordDetailId];

        if (empty($record_detail)) {
            return array('status' => -1, 'message' => '订单明细不存在');
        }
        if (empty($cur_detail)) {
            return array('status' => -1, 'message' => '订单明细不存在');
        }
        $log_msg = '';
        $this->begin_trans();
        try {
            $d = array(
                'avg_money' => $avg_money,
                'num' => $num,
                'deal_code' => $deal_code,
                'fx_amount' => $fx_amount
            );
            if($sell_record['is_fenxiao'] == 2) {//普通分销订单修改，自动计算单价和金额，淘分销不自动计算
                if($cur_detail['num'] != $num && $fx_amount == $cur_detail['fx_amount']) {
                    $d['fx_amount'] = sprintf("%.3f", ($cur_detail['trade_price'] * $num));
                } else if (($cur_detail['num'] != $num && $fx_amount != $cur_detail['fx_amount']) || ($cur_detail['num'] == $num && $fx_amount != $cur_detail['fx_amount'])) {
                    $d['trade_price'] = sprintf("%.3f", ($fx_amount / $num));
                }
            }
            if ($deal_code != $cur_detail['deal_code']) {
                $deal_code_list = array();
                if ($sell_record['deal_code_list'] !== '') {
                    $deal_code_list = explode(",", $sell_record['deal_code_list']);
                }
                if (!in_array($deal_code, $deal_code_list)) {
                    array_push($deal_code_list, $deal_code);
                    $deal_code_list = implode(",", $deal_code_list);
                    $this->update(array("deal_code_list" => $deal_code_list), array("sell_record_code" => $sellRecordCode));
                }
            }

            $result = $this->db->update('oms_sell_record_detail', $d, array('sell_record_detail_id' => $sellRecordDetailId));
            if ($result !== true) {
                return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_DETAIL_ERROR');
            }

            //刷新订单数据
            $ret = $this->edit_detail_after_flush_data($sell_record, $record_detail, null, $sellRecordCode);

            if ($ret['status'] < 0) {
                return $ret;
            }

            //商品编码:1006034101 颜色:大红 尺码:42 數量:2 价格:99
            $fx_amount_log = !empty($fx_amount) ? ';结算金额：' . $d['fx_amount'] : '';
            if (empty($cur_detail['barcode'])) {
                $key_arr = array('barcode');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($cur_detail['sku'], $key_arr);
                $cur_detail['barcode'] = $sku_info['barcode'];
            }
            if ($add_num != 0 && $add_avg_money != 0) {
                $log_msg .= "商品条码:" . $cur_detail['barcode'] . ";数量:" . $add_num . ";均摊金额:" . $add_avg_money;
            } else {
                $log_msg .= "商品条码:" . $cur_detail['barcode'] . ";数量:" . $num . ";均摊金额:" . $avg_money . $fx_amount_log;
            }
            if ($ret['status'] == 1) {
                $log_msg .= " 实物库存锁定：" . $ret['message'];
            }
            if ($add_num != 0 && $add_avg_money != 0 && $action_bath != '批量') {
                $this->add_action($sellRecordCode, "新增商品", $log_msg);
            } elseif ($action_bath == '批量') {
                $this->add_action($sellRecordCode, "批量新增商品", $log_msg);
            } else {
                $this->add_action($sellRecordCode, "修改商品", $log_msg);
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => '保存失败:' . $e->getMessage());
        }

        return array('status' => 1, 'message' => '');
    }

    function opt_new_multi_detail($request, $is_skip_lock = 0, $type = 0) {
        $sell_record_code = $request['sell_record_code'];
        $record_detail = $this->get_detail_list_by_code($sell_record_code, 'sell_record_detail_id');
        $new_record_detail = $record_detail;

//        if (isset($request['order_status']) && isset($request['store_code'])) {
//            $record['order_status'] = $request['order_status'];
//            $record['store_code'] = $request['store_code'];
//        } else {
        $record = $this->get_record_by_code($sell_record_code, 'order_status,store_code,deal_code_list,is_fx_settlement,is_fenxiao,is_replenish');
        //  }

        if ($record['order_status'] == 1) {
            return $this->format_ret(-1, '', '订单被确认不能操作！');
        }
        if ($record['is_fenxiao'] == 2 && $record['is_fx_settlement'] == 1) {
            return $this->format_ret(-1, '', '订单已结算不能操作！');
        }
        $ret_store = load_model('base/StoreModel')->get_by_code($record['store_code']);
        $allow_negative_inv = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;
        $this->begin_trans();
        try {
            $log = '';
            foreach ($request['data'] as $k => $v) {
                //PHP 5.3's bug. fixed in 5.4.
                if (!is_array($v) || empty($v['num']) || $v['num'] < 0) {
                    continue;
                }

//				if ($allow_negative_inv == 0 && isset($v['available_mum'])) {
//					if ($v['num'] > $v['available_mum']) {
//						$v['num'] = $v['available_mum'];
//					}
//				}

                if (!isset($v['sum_money']) && $type == 0) {
                    $v['sum_money'] = -1;
                }
                $is_gift = isset($v['is_gift']) ? 1 : 0;
                //补发订单增加商品不需要锁定库存，价格统一为0
                $r = $this->opt_new_detail($sell_record_code, $v['sku'], $v['num'], $v['sum_money'], $request['deal_code'], $is_gift, $v['fx_amount']);
                if ($r['status'] < 0) {
                    $this->rollback();
                    return $this->format_ret(-1, '', $r['message']);
                }
                $new_record_detail = $this->get_detail_list_by_code($sell_record_code, 'sell_record_detail_id');

//				$spec1_name = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $v['spec1_code']));
//				$spec2_name = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $v['spec2_code']));

                if ($r['status'] != '1') {
                    $this->rollback();
                    return $this->format_ret(-1, '', $r['message']);
                }
                if ($request['add_sum_money']) {
                    $r['data']['avg_money'] = $request['add_sum_money'];
                }
                if ($request['add_num']) {
                    $v['num'] = $request['add_num'];
                }
                $log .= "商品条码:" . $v['barcode'] . ";数量:" . $v['num'] . ";均摊金额:" . $r['data']['avg_money'];
            }

            //刷新订单数据
            $ret = $this->edit_detail_after_flush_data(null, $record_detail, $new_record_detail, $sell_record_code, $is_skip_lock);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            if ($ret['status'] == 1) {
                $log .= " 实物库存锁定：" . $ret['message'];
            }
            if (isset($request['action_log'])) {
                $this->add_action($sell_record_code, '赠品工具添加', $request['action_log']);
            }
            if (isset($request['action_rank_log'])) {
                $this->add_action($sell_record_code, '排名送添加', $request['action_rank_log']);
            }
            if ($request['action_bath'] == '批量') {
                $this->add_action($sell_record_code, "批量新增商品", $log);
            } else {
                $this->add_action($sell_record_code, "新增商品", $log);
            }

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function opt_new_detail($sellRecordCode, $skuCode, $num, $sum, $deal_code, $is_gift = 0, $fx_amount = 0) {
        $record = $this->get_record_by_code($sellRecordCode);
        if (empty($record)) {
            return array('status' => -1, 'message' => '订单不存在');
        }
        if ($is_gift == 1) {
            $sum = 0;
        }
        $sql = "select * from goods_sku where sku = :code";
        $sku = $this->db->get_row($sql, array('code' => $skuCode));
        if (empty($sku)) {
            return array('status' => -1, 'message' => 'SKU不存在:' . $skuCode);
        }
        $sql_b = "select * from goods_sku where sku = :code";
        $barcode = get_barcode_by_sku($skuCode);

        $sql = "select a.* from base_goods a where a.goods_code = :code";
        $goods = $this->db->get_row($sql, array('code' => $sku['goods_code']));

        if (empty($goods)) {
            return array('status' => -1, 'message' => '商品不存在');
        }
        $cost_price = $sku['cost_price'];
        if ($cost_price <= 0 || empty($cost_price)) {
            $cost_price = $goods['cost_price'];
        }
        $goods_price = $sku['price'];
        if ($goods_price <= 0 || empty($goods_price)) {
            $goods_price = $goods['sell_price'];
        }
        try {
            if ($sum < 0) {
                //当转入价格小于0, 重新计算商品金额
                $sum = $goods_price * $num;
            }
            $d = array(
                'sell_record_code' => $record['sell_record_code'],
                'deal_code' => $deal_code,
                'shipping_time' => $record['plan_send_time'],
                'goods_code' => $sku['goods_code'],
                'spec1_code' => $sku['spec1_code'],
                'spec2_code' => $sku['spec2_code'],
                'sku_id' => $sku['sku_id'],
                'sku' => $sku['sku'],
                'barcode' => $barcode,
                'cost_price' => $cost_price,
                'refer_price' => $goods['sell_price'],
                'sell_price' => $goods['sell_price'],
                'goods_price' => $goods_price,
                'rebate' => '1',
                'is_delete' => 0,
                'avg_money' => $sum,
                'num' => $num,
                'is_gift' => $is_gift,
            );
            if ($record['is_fenxiao'] == 2 && $fx_amount == 0) {
                //取出分销结算单价
                $d['trade_price'] = load_model('fx/GoodsManageModel')->compute_fx_price($record['fenxiao_code'], $d, $record['record_time']);
                $d['fx_amount'] = $d['trade_price'] * $num;
            } else if ($fx_amount != 0) {
                $d['fx_amount'] = $fx_amount;
                $d['trade_price'] = $fx_amount / $num;
            }
            if ($is_gift == 0) {
                $update_str = "num = VALUES(num), avg_money = VALUES(avg_money), trade_price = VALUES(trade_price) , fx_amount = VALUES(fx_amount)";
            } else {
                $d['fx_amount'] = 0;
                $d['trade_price'] = 0;
                $update_str = "num = VALUES(num) + num , avg_money = VALUES(avg_money) + avg_money, trade_price = VALUES(trade_price) , fx_amount = VALUES(fx_amount)";
            }
            $ret = $this->insert_multi_duplicate('oms_sell_record_detail', array($d), $update_str);
            if ($ret['status'] < 1) {
                return $this->format_ret(-1, '', '保存订单明细出错');
            }
        } catch (Exception $e) {
            return array('status' => -1, 'message' => '保存失败:' . $e->getMessage());
        }

        $d['sell_record_detail_id'] = $ret['data'];

        return array('status' => 1, 'data' => $d);
    }

    /**
     * 锁定检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_lock_check($record, $detail, $sysuser) {
        /* if (false == get_operate_purview("order/sell_record/lock")) {
          return $this->return_value(-1, '无订单锁定权限');
         */
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_lock')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] == 1) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['shipping_status'] == 4) {
            return $this->return_value(-1, '已发货订单无需锁定');
        }

        if ($record['shipping_status'] > 0) {
            $ret = load_model('wms/WmsEntryModel')->check_wms_store($record['store_code']);
            if ($ret['status'] > 0) {
                return $this->format_ret(-1, '', '订单仓库对接wms,通知配货后,不允许锁定');
            }
        }
        if ($record['shipping_status'] > 0) {
            $ret_o2o = load_model('o2o/O2oEntryModel')->is_o2o_store_record($record['store_code']);
            if ($ret_o2o['status'] > 0) {
                return $this->format_ret(-1, '', '订单仓库对接外部系统发货,通知配货后,不能取消通知配货');
            }
        }

//        if ($record['shipping_status']>0) {
        //            return $this->return_value(-1, '已通知配货订单不能操作');
        //        }

        return $this->return_value(1, '');
    }

    /**
     * 订单锁定
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_lock($sellRecordCode, $request = array()) {
        $record_field = 'sell_record_code,is_lock,shipping_status,store_code';
        $record = $this->get_record_by_code($sellRecordCode, $record_field);
        //$detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_lock_check($record, '', $sys_user);

        if ($check['status'] != '1') {
            return $check;
        }

        $this->begin_trans();
        try {
            $data = array('is_lock' => 1, 'is_lock_person' => $sys_user['user_code'], 'is_lock_time' => $this->add_time());
            $ret = $this->update($data, array('sell_record_code' => $record['sell_record_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '锁定订单出错');
            }

            $this->add_action($record['sell_record_code'], '锁定');

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 解锁检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_unlock_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_unlock')) {
            //exit_json_response(-401, '', '无权访问');
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] != 1) {
            return $this->return_value(-1, '非锁定订单不能操作');
        }

        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
//        if ($record['shipping_status'] == 4) {
        //            return $this->return_value(-1, '已发货订单无需解锁');
        //        }

        return $this->return_value(1, '');
    }

    /**
     * 订单解锁
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_unlock($sellRecordCode, $request = array()) {
        $record_field = 'sell_record_code,is_lock,is_lock_person';
        $record = $this->get_record_by_code($sellRecordCode, $record_field);
        //$detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_unlock_check($record, '', $sys_user);

        if ($check['status'] != '1') {
            return $check;
        }

        $this->begin_trans();
        try {
            $data = array('is_lock' => 0, 'is_lock_person' => '', 'is_lock_time' => '');
            $ret = $this->update($data, array('sell_record_code' => $record['sell_record_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '解锁订单出错');
            }

            $this->add_action($record['sell_record_code'], '解锁');

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 沟通检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_commu_check($record, $detail, $sysuser) {
        /* if (false == get_operate_purview("order/sell_record/comm")) {
          return $this->return_value(-1, '无订单交流权限');
         */
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        /* if($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']){
          return $this->return_value(-1, '已锁定订单不能操作');
          }
          if ($record['is_pending'] == 1) {
          return $this->return_value(-1, '挂起订单不能操作');
          }

          if(in_array($record['order_status'], array(1,3,5))) {
          return $this->return_value(-1, '已确定, 已完成,作废订单不能操作');
         */

        return $this->return_value(1, '');
    }

    /**
     * 订单沟通
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_commu($sellRecordCode, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_commu_check($record, $detail, $sys_user);

        if ($check['status'] != '1') {
            return $check;
        }

        $this->begin_trans();
        try {
            $this->add_action($record['sell_record_code'], '沟通', $request['commu_body']);

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 作废检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_cancel_check($record, $detail, $sysuser, $skip_lock_check = 0, $skip_priv_check = 0) {
        //#############权限
        if ($skip_priv_check == 0) {
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_cancel')) {
                return $this->return_value(-1, "无权访问");
            }
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($skip_lock_check != 10) {
            if ($record['is_lock'] != 1 && $skip_lock_check == 0) {
                return $this->return_value(-1, '非锁定订单不能操作');
            }
            if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
                return $this->return_value(-1, '已锁定订单不能操作');
            }
        }

        if ($record['order_status'] == 3) {
            return $this->return_value(-1, '已作废订单不能操作');
        }

        if ($record['order_status'] == 5) {
            return $this->return_value(-1, '已完成订单不能操作');
        }

        if ($record['shipping_status'] >= 4) {
            return $this->return_value(-1, '订单已发货，无法作废');
        }

        return $this->return_value(1, '');
    }

    function biz_cancel($record, $detail, $sys_user, $type = '') {
        $this->begin_trans();
        $pay_status = $record['pay_status'];
        //释放锁定库存
        $ret = $this->lock_detail($record, $detail, 0); //取消付款释放锁定
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }

        //如果存在对应的有效的波次单，那么要先作废波次单
        if ((int) $record['waves_record_id'] > 0) {
            $w_ret = load_model('oms/WavesRecordModel')->cancel_waves_sell_record($record['waves_record_id'], $record['sell_record_code'], '作废订单前先取消波次单', 0);
            //echo '<hr/>$w_ret<xmp>'.var_export($w_ret,true).'</xmp>';
            if ($w_ret['status'] < 0) {
                $this->rollback();
                return $w_ret;
            }
        }else{//未生成波次，但是以获取到云栈
            $row = $this->get_record_by_code($record['sell_record_code']);
            load_model('oms/DeliverLogisticsModel')->cancel_waybill($row);
        }
        if ($record['shipping_status'] == 4 || $record['order_status'] == 3) {
            $this->rollback();
            return $this->format_ret(-1, '', '单据状态变化不能作废.');
        }

        $cancel_time=date('Y-m-d H:i:s',time());
        $sql = "update oms_sell_record set order_status = 3,lock_inv_status=0,must_occupy_inv=0,cancel_time='{$cancel_time}' where sell_record_code = '{$record['sell_record_code']}' and shipping_status='{$record['shipping_status']}' and order_status='{$record['order_status']}'";
        $ret = $this->db->query($sql);
        if ($ret != true) {
            $this->rollback();
            return $this->format_ret(-1, '', '作废订单出错.');
        }
        $aff_row = $this->db->affected_rows();
        if ($aff_row == 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '订单状态不允许作废.');
        }
        $wms_op_msg = '';
        $ret = load_model('wms/WmsEntryModel')->cancel($record['sell_record_code'], 'sell_record', $record['store_code']);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        } else {
            $wms_op_msg = '';
            if ($ret['status'] == 10) {
                $wms_op_msg = $ret['message'];
            }
        }
        $ret = load_model('mid/MidBaseModel')->cancel_mid_record($record['sell_record_code'], 'sell_record', $record['store_code'], $record['shop_code']);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        } else {
            $wms_op_msg = '';
            if ($ret['status'] == 10) {
                $wms_op_msg = $ret['message'];
            }
        }


        if ($record['shipping_status'] == 1) {
            load_model('oms/SellRecordNoticeModel')->delete_record_notice(array($record['sell_record_code']));
        }

        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('direct_cancel'));
        if ($cfg['direct_cancel'] == 1) {
            if ($type == 'direct' && $pay_status == 2) {
                $ret = load_model('oms/SellReturnOptModel')->create_return_record_by_cancel($record['sell_record_code'], 'direct_cancel');
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '已付款订单作废生成退单失败');
                }
                $wms_op_msg .= '已付款订单作废生成退单，退单号为：' . $ret['data'];
            }
        }
        if ($this->msg != '') {
            $this->add_action($record['sell_record_code'], '作废订单', $wms_op_msg . $this->msg);
        } else if($type == 'refund_all_cancel') {
            $this->add_action($record['sell_record_code'], '作废订单', $wms_op_msg . '发货前订单整单退款，作废订单');
        } else {
            $this->add_action($record['sell_record_code'], '作废订单', $wms_op_msg);
        }



        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 订单作废
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_cancel($sellRecordCode, $skip_lock_check = 0, $type = '', $skip_priv_check = 0) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_cancel_check($record, $detail, $sys_user, $skip_lock_check, $skip_priv_check);
        //不验证淘分销订单        
        if ($record['is_fenxiao'] == 2 && $record['is_fx_settlement'] == 1) {
            return $this->return_value(-1, '已结算订单不能操作');
        }

        if ($check['status'] != '1') {
            return $check;
        }

        try {
            $ret = $this->biz_cancel($record, $detail, $sys_user, $type);
            if ($ret['status'] < 0) {
                return $ret;
            }
//            //云栈取消接口，未生成波次情况
//            if (!empty($record['express_no']) && !empty($record['express_data'] && empty($record['waves_record_id']))) {
//                $this->cancle_cainiao_wlb_waybil_action($record);
//            }
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * @todo        订单作废接口
     * @author      BaiSon PHP
     * @date        2016-03-08
     * @param       array $param
     *               array(
     *                  必选: 'sell_record_code','cancel_flag'
     *                  可选: 'desc',
     *               )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_order_cancel($param) {
        $key_required = array(
            's' => array(
                'sell_record_code'
            ),
            'i' => array(
                'cancel_flag'
            )
        );
        $arr_required = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_required, $arr_required, true);
        if ($ret_option['status'] != TRUE) {
            return $this->format_ret('-10001', $ret_option['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
        $sell_record_code = $param['sell_record_code'];
        $cancel_flag = $param['cancel_flag'];

        if (!in_array($cancel_flag, array(0, 1))) {
            return $this->format_ret(0, array('cancel_flag'), '指定数据不匹配');
        }
        if ($cancel_flag == 0) {
            $record = $this->get_record_by_code($sell_record_code, 'sell_record_code');
            if (empty($record)) {
                return $this->format_ret('-10002', array('sell_record_code' => $sell_record_code), '订单号不存在');
            }
            $sql = "UPDATE {$this->table} SET order_remark=CONCAT(order_remark,'{$param['desc']}') WHERE sell_record_code='{$sell_record_code}'";
            $ret = $this->db->query($sql);
            if ($ret != TRUE) {
                return $this->format_ret(-1, '', '更新失败');
            }
            load_model('oms/SellRecordActionModel')->record_log_check = 0;
            $action_note = isset($param['desc']) && trim($param['desc']) != '' ? $param['desc'] : '';
            $this->add_action($sell_record_code, 'API-更新订单备注', $action_note);
            load_model('oms/SellRecordActionModel')->return_log_check = 1;
            return $this->format_ret(1);
        }
        load_model('oms/SellRecordActionModel')->record_log_check = 0;
        load_model('oms/SellReturnModel')->return_log_check = 0;
        if (isset($param['desc']) && !empty($param['desc'])) {
            $this->msg = $param['desc'];
        }
        $ret = $this->opt_cancel($sell_record_code, 10, 'direct', 1);
        load_model('oms/SellRecordActionModel')->record_log_check = 1;
        load_model('oms/SellReturnModel')->return_log_check = 1;
        return $ret;
    }

    /**
     * 付款检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_pay_check($record, $detail, $sysuser) {
        //当订单为货到付款并且已发货的情况下，可进行付款操作，但是仅更新支付状态和支付时间
        if ($record['pay_type'] == 'cod' && $record['shipping_status'] == 4 && $record['pay_status'] != 2) {
            return $this->return_value(1, '');
        }
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_pay')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if (empty($detail)) {
            return $this->return_value(-1, '订单无明细');
        }
        if ($record['is_lock'] != 1) {
            return $this->return_value(-1, '非锁定订单不能操作');
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_problem'] == 1) {
            return $this->return_value(-1, '问题订单不能操作');
        }
        if ($record['pay_type'] == 'cod') {
            return $this->return_value(-1, '货到付款订单不能操作');
        }
        if ($record['pay_type'] != 'cod' && $record['pay_status'] == 2) {
            return $this->return_value(-1, '已付款订单不能操作');
        }
        if ($record['order_status'] == 3) {
            return $this->return_value(-1, '已作废订单不能操作');
        }

        return $this->return_value(1, '');
    }

    //当订单为货到付款并且已发货的情况下，可进行付款操作，但是仅更新支付状态和支付时间
    function cod_pay($record) {
        $data = array();
        $data['pay_status'] = 2;
        $data['pay_time'] = date("Y-m-d H:i:s");
        return $this->update($data, array('sell_record_code' => $record['sell_record_code']));
    }

    /**
     * 订单付款
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_pay($sellRecordCode, $paid_money, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        //当订单为货到付款并且已发货的情况下，可进行付款操作，但是仅更新支付状态和支付时间
        if ($record['pay_type'] == 'cod' && $record['shipping_status'] == 4) {
            $cod_red = $this->cod_pay($record);
            if ($cod_red['status'] < 1) {
                return $cod_red;
            }
            return $this->format_ret(1);
        }
        $sys_user = $this->sys_user();
        $check = $this->opt_pay_check($record, $detail, $sys_user);
        $shop_info = load_model("base/ShopModel")->get_by_field("shop_code", $record['shop_code'], 'days');
//        $days = 0;
        //        if($shop_info['status']==1){
        //            $days = $shop_info['data']['days'];
        //        }

        if ($check['status'] != '1') {
            return $check;
        }
        $this->begin_trans();
        try {
            $data = array();
            $data['paid_money'] = $paid_money;
            $data['pay_status'] = 2;
            $data['pay_time'] = $this->add_time();
            $data['must_occupy_inv'] = 1;
//            $data['plan_send_time'] = date('Y-m-d H:i:s',strtotime("{$data['pay_time']} +{$days} day"));
            $where = " sell_record_code = {$record['sell_record_code']} and pay_status<>2";
            //付款锁定 锁定更新
            $record['pay_status'] = $data['pay_status'];
            $record['must_occupy_inv'] = 1;
            $lock_ret = $this->lock_detail($record, $detail, 1);

            if ($lock_ret['status'] < 1) {
                $this->rollback();
                return $lock_ret;
            }

            $ret = $this->update($data, array('sell_record_code' => $record['sell_record_code']));
            if ($ret['status'] != 1) {
                // return $this->format_ret(-1,'',$e->getMessage());
            }
            $result = $this->set_sell_plan_send_time($sellRecordCode, 0);
            $remark = isset($request['batch']) ? $request['batch'] : '';
            $log_msg = $remark . '付款金额:' . $paid_money;
            if ($ret['status'] == 1) {
                $log_msg .= " 实物库存锁定：" . $lock_ret['message'];
            }
            $this->add_action($record['sell_record_code'], '付款', $log_msg);

            //不是手工分销订单
            /* if (($record['is_fenxiao'] == 1 || $record['is_fenxiao'] == 2) && $record['is_handwork'] == 0) {
              $this->opt_settlement($sellRecordCode);
              } */
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function lock_detail_by_order_code($sellRecordCode) {

        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $ret = $this->lock_detail($record, $detail);
        return $ret;
    }

    function lock_detail($record, $param_detail, $occupy_type = 1, $skip_chk = 0, $force = 0) {
        //echo '<hr/>$arr<xmp>'.var_export($arr,true).'</xmp>';die;
        //must_occupy_inv = 1 && lock_inv_status<>1
        //如果没有明细的单据，要当成不缺货来处理
        
        
        
        
        if (empty($param_detail)) {
            $sql = "update oms_sell_record set lock_inv_status = 1 where sell_record_code = :sell_record_code";
            ctx()->db->query($sql, array(':sell_record_code' => $record['sell_record_code']));
            return $this->format_ret(1, '', '没有明细，无需实物锁定');
        }
        if ($occupy_type == 1 && $skip_chk == 0) {
            if ($record['must_occupy_inv'] === 0 || ($record['must_occupy_inv'] === 1 && $record['lock_inv_status'] === 1) || $record['order_status'] === 3) {
                return $this->format_ret(1, '', '无需实物锁定');
            }
        }

        if ($occupy_type == 0 && $skip_chk == 0) {
            if ($record['lock_inv_status'] == 0 || $record['order_status'] == 3) {
                return $this->format_ret(1, '', '无需释放锁定');
            }
        }

        $detail = array();
        foreach ($param_detail as $k => $row) {
            if (isset($row['is_delete']) && $row['is_delete'] == 1) {
                continue;
            }
            $row['p_detail_id'] = isset($row['sell_record_detail_id']) ? $row['sell_record_detail_id'] : 0;
            $row['record_code'] = $row['sell_record_code'];
            $detail[$k] = $row;
        }

        $ret = $this->format_ret('1');
        $details_data = array();
        //$details_data_unlock = array();
        //$new_detail = array();
        $stock_out_arr = array();
        $is_lock_inv = false;
        if ($occupy_type == 1) {
            //存在风险
            $is_lock_inv = $this->get_sell_record_is_lock($record['sell_record_code']);
        } else {
            $is_lock_inv = TRUE;
        }

        if ($occupy_type == 1) {
            $key_arr = array('sell_record_detail_id', 'sell_record_code', 'deal_code', 'goods_code', 'spec1_code', 'spec2_code', 'sku');
            foreach ($detail as $val) {
                $val['lock_num'] = isset($val['lock_num']) ? $val['lock_num'] : 0;
                if (($val['num'] - $val['lock_num']) != 0) {
                    $detail_val = array();
                    foreach ($key_arr as $key) {
                        $detail_val[$key] = isset($val[$key]) ? $val[$key] : null;
                    }
                    $detail_val['store_code'] = $record['store_code'];
                    $detail_val['num'] = $val['num'] - $val['lock_num'];
                    $key_inv = $val['sku'];
                    if (TRUE === $is_lock_inv) {
                        if (isset($stock_out_arr[$key_inv])) {
                            $stock_out_arr[$key_inv]['out_num'] += $detail_val['num'];
                        } else {
                            $stock_out_arr[$key_inv] = array(
                                'out_num' => $detail_val['num'],
                                'sku' => $detail_val['sku'],
                                'goods_code' => $detail_val['goods_code'],
                                'spec1_code' => $detail_val['spec1_code'],
                                'spec2_code' => $detail_val['spec2_code'],
                            );
                        }
                    }

                    if ($detail_val['num'] < 0) {
                        $detail_val['num'] = $val['num'];
                    }
                    if (isset($details_data[$key_inv])) {
                        $details_data[$key_inv]['num'] += $detail_val['num'];
                    } else {
                        $details_data[$key_inv] = $detail_val;
                    }
                }
            }
        } else {

            $sell_record_code = $record['sell_record_code'];
            $sql = "select * from oms_sell_record_lof where record_code = :sell_record_code AND  record_type = 1 ";
            $details_data = $this->db->get_all($sql, array(':sell_record_code' => $sell_record_code));

            foreach ($detail as $val) {
                if ($val['num'] > $val['lock_num']) {
                    $key_inv = $val['sku'];
                    if(!isset($stock_out_arr[$key_inv])){
                            $stock_out_arr[$key_inv] = array(
                            'out_num' => $val['lock_num'] - $val['num'],
                            'sku' => $val['sku'],
                            'goods_code' => $val['goods_code'],
                            'spec1_code' => $val['spec1_code'],
                            'spec2_code' => $val['spec2_code'],
                        );
                    }else{
                        $stock_out_arr[$key_inv]['out_num'] +=  $val['lock_num'] - $val['num'];
                    }
        
                }
            }
        }
        $this->is_all_lock = 0;

        if (!empty($details_data)) {
            $invobj = new InvOpModel($record['sell_record_code'], 'oms', $record['store_code'], $occupy_type, $details_data);
            if ($force == 1) {
                $invobj->lock_allow_negative_inv(1);
            }
            $ret = $invobj->adjust();
            //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
            if ($ret['status'] < 1) {
                return $ret;
            }
            if ($occupy_type == 1) {
                $this->is_all_lock = $invobj->is_all_lock;
            }
        }

        $lock_result_arr = array();
        if (is_array($ret['data'])) {
            foreach ($ret['data'] as $val) {
                $key_inv = $val['sku'];
                $lock_result_arr[$key_inv] = $val['lock_num'] < 0 ? 0 : $val['lock_num'];
            }
        }

//        if(empty($ret['data']) && $ret['status'] == -10){
        //	        foreach ($details_data as $val) {
        //	            $key_inv = $val['goods_code'] . ',' . $val['spec1_code'] . ',' . $val['spec2_code'];
        //	            $lock_result_arr[$key_inv] = 0;
        //	     	}
        //        }

        $occupy_msg_arr = array();
        $stock_out_mx = array();
        //根据锁定或释放锁定的情况，来更新订单商品明细
        //  var_dump($stock_out_arr,$detail);die;
        foreach ($detail as $k => $d_row) {
            $key_inv = $d_row['sku'];
            $inv_lock_num = 0; //本次锁定库存数量
            if ($occupy_type == 1) {

                $_modi_kc_num = isset($lock_result_arr[$key_inv]) ? $lock_result_arr[$key_inv] : 0;
                $d_row['lock_num'] = isset($d_row['lock_num']) ? $d_row['lock_num'] : 0;
                $_lock_num = 0; //锁定库存库存数量
                if ($_modi_kc_num > ($d_row['num'] - $d_row['lock_num'])) {
                    $_lock_num = $d_row['num'];
                    $inv_lock_num = $d_row['num'] - $d_row['lock_num'];
                } else {
                    $_lock_num = $_modi_kc_num + $d_row['lock_num'];
                    $inv_lock_num = $_modi_kc_num;
                }


                $_modi_kc_num -= $inv_lock_num;
                if ($is_lock_inv === true) {
                    if (isset($stock_out_arr[$key_inv]) && $inv_lock_num > 0) {
                        $stock_out_arr[$key_inv]['out_num'] = -$inv_lock_num;
                    } else if (isset($stock_out_arr[$key_inv]) && $inv_lock_num == 0) {
                        $stock_out_arr[$key_inv]['out_num'] -= $d_row['num'] - $_lock_num;
                    }
                } else {
                    if ($d_row['num'] > $_lock_num) {
                        if (!isset($stock_out_arr[$key_inv])) {
                            $stock_out_arr[$key_inv] = array(
                                'out_num' => 0,
                                'sku' => $d_row['sku'],
                                'goods_code' => $d_row['goods_code'],
                                'spec1_code' => $d_row['spec1_code'],
                                'spec2_code' => $d_row['spec2_code'],
                            );
                        }
                        $stock_out_arr[$key_inv]['out_num'] += $d_row['num'] - $_lock_num;
                    }
                }

                $sku_tips = empty($d_row['barcode']) ? "SKU {$d_row['sku']}" : "条码 {$d_row['barcode']}";
//                if ($_modi_kc_num > 0) {
//                    $occupy_msg_arr[] = $sku_tips . " 已锁定 {$_modi_kc_num} 件";
//                }
//
                $lock_result_arr[$key_inv] = $_modi_kc_num;
                if ($inv_lock_num > 0) {
                    if (!empty($d_row['sell_record_detail_id'])) {
                        $sql = "update oms_sell_record_detail set lock_num =lock_num+{$inv_lock_num} where sell_record_detail_id = {$d_row['sell_record_detail_id']} and sell_record_code = '{$record['sell_record_code']}' AND (lock_num+{$inv_lock_num})<=num ";
                    } else {
                        $is_gift = isset($d_row['is_gift']) ? $d_row['is_gift'] : 0;
                        $sql = "update oms_sell_record_detail set lock_num =lock_num+{$inv_lock_num} where sell_record_code = '{$record['sell_record_code']}' and deal_code = '{$d_row['deal_code']}' and sku='{$d_row['sku']}' AND is_gift='{$is_gift}' AND (lock_num+{$inv_lock_num})<=num  ";
                    }

                    $status = $this->db->query($sql);
                    $run_num = $this->affected_rows();
                    if ($run_num != 1 || $status === FALSE) {
                        return $this->format_ret(-1, '', '锁定库存异常！');
                    }
                }
            } else {
                $_lock_num = 0;
                if (!empty($d_row['sell_record_detail_id'])) {
                    $sql = "update oms_sell_record_detail set lock_num ={$_lock_num} where sell_record_detail_id = {$d_row['sell_record_detail_id']} and sell_record_code = '{$record['sell_record_code']}' ";
                } else {
                    $sql = "update oms_sell_record_detail set lock_num ={$_lock_num} where sell_record_code = '{$record['sell_record_code']}' and deal_code = '{$d_row['deal_code']}' and sku='{$d_row['sku']}'  ";
                }

                $this->db->query($sql);
            }
        }



        //echo '<hr/>$stock_out_mx<xmp>'.var_export($stock_out_mx,true).'</xmp>';
        //缺货数量维护更新 -- 包含增加和减少
        //error_log("\n--update_stock_out_inv--\n".var_export($detail,true),3,ROOT_PATH."/logs/xlog.log");

        if (!empty($stock_out_arr)) {
            load_model('prm/InvModel')->update_stock_out_inv($stock_out_arr, $record['store_code']);
        }

        //主单据缺货处理
        $ret_out = $this->set_stock_out($record['sell_record_code']);

        //单据已经执行锁定状态
        $is_lock = $occupy_type == 1 ? TRUE : FALSE;
        $this->set_sell_record_is_lock($record['sell_record_code'], $is_lock);

        //    $this->is_all_lock
        //2 为缺货
        return $this->format_ret(1, $ret_out['data'], join(',', $occupy_msg_arr));
    }

    private $sell_record_lock_arr = array();

    private function get_sell_record_is_lock($sell_record_code) {
        if (!isset($this->sell_record_lock_arr[$sell_record_code])) {
            $record = $this->db->get_row("select pay_type,pay_status from  oms_sell_record where  sell_record_code=:sell_record_code", array(':sell_record_code' => $sell_record_code));
            if ($record['pay_type'] == 'cod' || $record['pay_status'] > 0) {
                $this->sell_record_lock_arr[$sell_record_code] = TRUE;
            } else {
                $this->sell_record_lock_arr[$sell_record_code] = FALSE;
            }
        }
        return $this->sell_record_lock_arr[$sell_record_code];
    }

    //强制设置状态
    public function set_sell_record_is_lock($sell_record_code, $is_lock) {
        $this->sell_record_lock_arr[$sell_record_code] = $is_lock;
    }

    /**
     * 设置订单缺货状态
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function set_stock_out($sellRecordCode) {
        /*
         * 不缺 每条明细都是 num=lock_num
         * 全缺 每条明细都是 lock_num = 0
         * 其它的就是部分缺
         */
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $_stock_out_arr = array();
        foreach ($detail as $val) {
            if ($val['num'] <= $val['lock_num']) {
                $_stock_out_arr[1][] = $val; //不缺
                continue;
            }
            if ($val['num'] > $val['lock_num']) {
                $_stock_out_arr[0][] = $val; //缺
                continue;
            }
        }

        $lock_inv_status = 0;
        $mx_count = count($detail);
        //可折单的缺货
        if (isset($_stock_out_arr[0]) && isset($_stock_out_arr[1])) {
            $lock_inv_status = 2;
        }
        if (isset($_stock_out_arr[1]) && count($_stock_out_arr[1]) == $mx_count) {
            $lock_inv_status = 1;
        }
        //不可折单的缺货
        if ($lock_inv_status == 0 && isset($_stock_out_arr[0])) {
            $lock_inv_status = 3;
        }
        $this->update_exp('oms_sell_record', array('lock_inv_status' => $lock_inv_status), "sell_record_code='{$sellRecordCode}'");
        return $this->format_ret(1, $lock_inv_status);
    }

    /**
     * 取消付款检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_unpay_check($record, $detail, $sysuser) {
        /* if (false == get_operate_purview("order/sell_record/pay")) {
          return $this->return_value(-1, '无订单支付权限');
         */
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_unpay')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if (empty($detail)) {
            return $this->return_value(-1, '订单无明细');
        }
        if ($record['is_lock'] != 1) {
            return $this->return_value(-1, '非锁定订单不能操作');
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        //if ($record['is_pending'] == 1) {
        //    return $this->return_value(-1, '挂起订单不能操作');
        //}
        if ($record['is_problem'] == 1) {
            return $this->return_value(-1, '问题订单不能操作');
        }
        if ($record['pay_type'] == 'cod') {
            return $this->return_value(-1, '货到付款订单不能操作');
        }
        if ($record['pay_type'] == 0 && $record['pay_status'] == 0) {
            return $this->return_value(-1, '未付款订单不能操作');
        }
        if (in_array($record['order_status'], array(1, 3, 5))) {
            return $this->return_value(-1, '已确定, 已完成,作废订单不能操作');
        }

        return $this->return_value(1, '');
    }

    /**
     * 取消付款
     * @param $sellRecordCode
     * @param array $request
     * @return array
     */
    function opt_unpay($sellRecordCode, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_unpay_check($record, $detail, $sys_user);

        if ($check['status'] != '1') {
            return $check;
        }

        $this->begin_trans();
        try {
            $data = array();
//            $data['paid_money'] = 0;
            $data['pay_status'] = 0;
            $data['pay_time'] = '';
            $data['must_occupy_inv'] = $record['pay_type'] == 'cod' ? 1 : 0;
            $data['lock_inv_status'] = $record['pay_type'] == 'cod' ? $record['lock_inv_status'] : 0;
//            $data['plan_send_time'] = '';
            $ret = $this->update($data, array('sell_record_code' => $record['sell_record_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '订单取消付款出错');
            }

            /* $this->delete('order_sell_record_settlement', $order_sell_record_settlement); */

            $this->add_action($record['sell_record_code'], '取消付款', '取消付款金额:' . $record['paid_money']);
            //echo '<hr/>record<xmp>'.var_export($record,true).'</xmp>';
            //echo '<hr/>detail<xmp>'.var_export($detail,true).'</xmp>';

            if ($record['pay_type'] != 'cod') {
                $ret = $this->lock_detail($record, $detail, 0); //取消付款释放锁定
                if ($ret['status'] < 1) {
                    $this->rollback();
                    return $ret;
                }
            }

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 确认检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_confirm_check($record, $sysuser, $is_skip_lock = 0) {
        /* if (false == get_operate_purview("order/sell_record/sure")) {
          return $this->return_value(-1, '无订单确认权限');
         */
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_confirm')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['goods_num'] == 0) {
            return $this->return_value(-1, '订单无明细');
        }
//		if ($is_skip_lock == 0) {
//			if ($record['is_lock'] != 1) {
//				return $this->return_value(-1, '非锁定订单不能操作');
//			}
//			if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
//				return $this->return_value(-1, '已锁定订单不能操作');
//			}
//		}

        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_problem'] == 1) {
            return $this->return_value(-1, '问题订单不能操作');
        }

        if (in_array($record['order_status'], array(1, 3, 5))) {
            return $this->return_value(-1, '已确定, 已完成,作废订单不能操作');
        }
        if ($record['pay_type'] != 'cod' && $record['pay_status'] != 2) {
            return $this->return_value(-1, '未付款订单不能操作');
        }

        if (empty($record['store_code'])) {
            return $this->return_value(-1, '仓库不能为空');
        }
        if (empty($record['receiver_name'])) {
            return $this->return_value(-1, '收货人不能为空');
        }
        if (empty($record['express_code'])) {
            return $this->return_value(-1, '配送方式不能为空');
        }
        //0-未占用 2-部分缺货 3-完全缺货
        if ($record['lock_inv_status'] == 0 || $record['lock_inv_status'] == 2 || $record['lock_inv_status'] == 3) {
            return $this->return_value('-1', '此订单是缺货单，无法确认');
        }

        if ($record['pay_status'] == 2 && $record['payable_money'] > $record['paid_money']) {
            return $this->return_value(-1, '已付款和应付款不相等');
        }
        if (($record['is_fenxiao'] == 1 || $record['is_fenxiao'] == 2) && $record['is_fx_settlement'] != 1) {
            return $this->return_value(-1, '未结算订单不能操作');
        }
        return $this->return_value(1, '');
    }

    public function auto_notice() {
        $data = $this->get_unshipping_record("sell_record_code");
        //已确认，未发货
        $sys_user = $this->sys_user();
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('off_deliver_time'));
        if (!empty($data)) {
            //$msg = '';
            foreach ($data as $record) {
                $sell_record_code = $record['sell_record_code'];
                $record = $this->get_record_by_code($sell_record_code);
                //判断是否为预售单
                $_tm = strtotime($record['plan_send_time']) - time();
                if ($record['sale_mode'] == 'presale') {
                    if ($_tm > $cfg['off_deliver_time'] * 3600 * 24 && $cfg['off_deliver_time'] != 0) {
                        //$msg .= "订单{$record['sell_record_code']}中含有预售商品，未能通知配货！";
                        continue;
                    }
                }
                /*
                  $is_combine_order = $this->check_api_is_combine_order($record);
                  if ($is_combine_order === true) {
                  //合并单据
                  continue;
                  } */
                $this->biz_notice_shipping($record, '', $sys_user, '自动');
            }
//            if(!empty($msg)){
//                return $this->format_ret(-1, '', $msg);
//            }
        }
        return $this->format_ret(1);
    }

    function get_unshipping_record($field) {
        $sql = "SELECT {$field} FROM oms_sell_record WHERE order_status = 1 AND shipping_status = 0";
        return $this->db->get_all($sql);
    }

    public function auto_confirm() {
        $filter = array('is_normal' => 1, 'order_status' => 0, 'pay_status' => 2, 'shipping_status' => 0);
        $data = $this->get_record_by_condition($filter, array('sell_record_code,is_fenxiao,is_fx_settlement'));
        $error = array();

        if (!empty($data)) {
            foreach ($data as $record) {
                if (($record['is_fenxiao'] == 1 || $record['is_fenxiao'] == 2) && $record['is_fx_settlement'] != 1) { //分销订单未结算不自动确认
                    continue;
                }
                $sell_record_code = $record['sell_record_code'];
                $record = $this->get_record_by_code($sell_record_code);

                //取消2小时自动服务包含合并判断
//                $is_combine_order = $this->check_api_is_combine_order($record);
//                
//                if ($is_combine_order === true) {
//                    //合并单据
//                    continue;
//                }

                $record_detail = $this->get_detail_by_sell_record_code($sell_record_code);
                $check_confrim = load_model("oms/OrderCheckOpModel")->check_order($record, $record_detail);
                if (!empty($check_confrim)) {
                    continue;
                }
                if ($record['is_lock'] == 1) {
                    $ret = $this->opt_force_unlock($sell_record_code);
                    if ($ret['status'] < 1) {
                        $error[] = $ret['message'];
                        continue;
                    }
                }
                $this->auto_confirm_and_notice($sell_record_code, $record);
            }
        }
        return $this->format_ret(1, $error);
    }

    /**
     * @todo 批量确认方法，解决速度问题
     */
    function new_opt_confirm($sell_record_code_list) {
        $sell_record_code_arr = explode(',', $sell_record_code_list);
        $sell_record_code_str = join("','", $sell_record_code_arr);
        $sql = "SELECT sell_record_code,order_status,shipping_status,pay_status,is_fenxiao,is_fx_settlement
                    FROM oms_sell_record WHERE sell_record_code IN('{$sell_record_code_str}')
                        AND goods_num > 0
                        AND is_pending = 0
                        AND is_problem = 0
                        AND order_status = 0
                        AND ((pay_type='cod' and pay_status=0) OR (pay_status=2 AND payable_money <= paid_money))
                        AND store_code <> ''
                        AND receiver_name <> ''
                        AND express_code <> ''
                        AND lock_inv_status=1";
        $sell_record_data = $this->db->get_all($sql);
        $status = array();
        foreach ($sell_record_data as $key => $d) {
            if (($d['is_fenxiao'] == 1 || $d['is_fenxiao'] == 2) && $d['is_fx_settlement'] != 1) {
                unset($sell_record_data[$key]);
                continue;
            }
            $status[$d['sell_record_code']] = $d;
        }
        $code_str = join("','", array_keys($status));
        $sys_user = $this->sys_user();
        //获取系统参数
        $check_time = $this->add_time();
        $data = array('order_status' => '1', 'confirm_person' => $sys_user['user_name'], 'check_time' => $check_time);
        $where = " sell_record_code IN ('{$code_str}') AND order_status= 0 AND is_problem = 0 ";
        //更新状态
        $ret = $this->db->update('oms_sell_record', $data, $where);
        $successed = $this->affected_rows();
        if (!$ret) {
            return $this->format_ret(-1, '', '订单确认出错');
        }
        $sql = "SELECT sell_record_code,order_status,shipping_status,pay_status
                    FROM oms_sell_record WHERE sell_record_code IN('{$code_str}')
                        AND is_problem = 0 AND order_status=1";
        $sell_record_data = $this->db->get_all($sql);
        //满足获取菜鸟物流的单子
        $cainiao_record_code = array_column($sell_record_data, 'sell_record_code');
        foreach ($sell_record_data as &$d) {
            $d['order_status'] = '1';
            $d['action_name'] = '确认';
            $d['action_note'] = '批量操作';
            $d['user_code'] = $sys_user['user_code'];
            $d['user_name'] = $sys_user['user_name'];
        }
        $update_str = "sell_record_code = VALUES(sell_record_code),order_status = VALUES(order_status),shipping_status = VALUES(shipping_status),pay_status = VALUES(pay_status),action_name = VALUES(action_name),action_note = VALUES(action_note),user_code = VALUES(user_code),user_name = VALUES(user_name)";
        //批量写日志
        $this->insert_multi_duplicate('oms_sell_record_action', $sell_record_data, $update_str);
        $rs = array_diff($sell_record_code_arr, array_keys($status));
        if (empty($rs)) {
            $msg = '操作成功';
        } else {
            // $failed = count($rs);
            // $successed = count($sell_record_code_arr) - $failed;
            $failed = count($sell_record_code_arr) - $successed;
            $msg = '<h2 style="text-align:center">操作成功' . $successed . '单，失败' . $failed . '单！</h2><br><p style="color:red;text-align:center">友情提示：确认失败的订单请确认订单是非问题，非挂起，非缺货订单</p><p style="color:red;text-align:center">且订单已分配发货仓库和配送方式！</p>';
        }
//            $schedule_sql = "SELECT status FROM sys_schedule WHERE `code`='auto_notice'";
//            $schedule_ret = $this->db->getOne($schedule_sql);
//            if($schedule_ret == 1){
//                //查询已经被确认成功的订单号
//                $select_sql = "SELECT sell_record_code FROM oms_sell_record WHERE sell_record_code IN('{$code_str}') AND order_status=1 AND confirm_person='{$sys_user['user_name']}'";
//                $confirmed_data = $this->db->get_all($select_sql);
//                foreach($confirmed_data as $c){
//                    $confirmed[$c['sell_record_code']] = $c;
//                }
//                $confirmed_str = join(",", array_keys($confirmed));
//                //创建定时任务
//                require_model('common/TaskModel');
//                $task = new TaskModel();
//                $task_data = array();
//                $code = 'auto_notice_' . md5($confirmed_str);
//                $task_data['code'] = $code;
//                $request['app_act'] = 'oms/sell_record/auto_notice';
//                $request['type'] = 'batch';
//                $request['code'] = $confirmed_str;
//                $request['app_fmt'] = 'json';
//                $task_data['start_time'] = time();
//                $task_data['request'] = $request;
//                $task->save_task($task_data);
//            }

        //开启系统参数调用云栈四期接口获取物流单号
        $sys_params = load_model('sys/SysParamsModel')->get_val_by_code(array('opt_confirm_get_cainiao'));
        if ($sys_params['opt_confirm_get_cainiao'] == 1 && !empty($cainiao_record_code)) {
            $this->sell_record_get_cainiao_api($cainiao_record_code);
        }
        return $this->format_ret(1, '', $msg);
    }

    /**
     * @todo 已确认的订单自动通知配货
     */
    function confirmed_auto_notice($code_str) {
        $sys_user = $this->sys_user();
        $code_arr = explode(',', $code_str);
        foreach ($code_arr as $sell_record_code) {
            $record = $this->get_record_by_code($sell_record_code);
            $ret = $this->biz_notice_shipping($record, '', $sys_user, '自动');
        }
        return $this->format_ret(1);
    }

    private function check_api_is_combine_order($record) {
        $tid_arr = explode(",", $record['deal_code_list']);
        $create_time = date('Y-m-d H:i:s', strtotime('-2 hours'));

        $sql = "select * from api_order  where  shop_code=:shop_code AND buyer_nick=:buyer_nick AND receiver_name=:receiver_name and receiver_addr=:receiver_addr AND order_first_insert_time>:order_first_insert_time ";
        $sql_values = array(':shop_code' => $record['shop_code'],
            ':buyer_nick' => $record['buyer_name'],
            ':receiver_name' => $record['receiver_name'],
            ':receiver_addr' => $record['receiver_addr'],
            ':order_first_insert_time' => $create_time,
        );

        $data = $this->db->get_all($sql, $sql_values);

        $is_have = FALSE;
        foreach ($data as $val) {
            if (!in_array($val['tid'], $tid_arr) && $val['is_change'] == 0) {
                $is_have = TRUE;
                break;
            }
        }
        return $is_have;
    }

    /**
     * 订单确认
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_confirm($sellRecordCode, $request = array(), $is_skip_lock = 0) {
        //#############权限
        /*
          if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_confirm')) {
          exit_json_response(-401, '', '无权访问');
          } */
        //###########
        $record_field = 'sell_record_code,sale_channel_code,lock_inv_status,shop_code,store_code,receiver_name,express_code,plan_send_time,pay_status,payable_money,goods_num,paid_money,deal_code_list,is_lock,is_lock_person,is_pending,is_problem,order_status,pay_type,sale_mode,is_fenxiao,is_fx_settlement';
        //$detail_field = 'num,lock_num,is_delete';
        $record = $this->get_record_by_code($sellRecordCode, $record_field);
        //$detailList = $this->get_detail_list_by_code($sellRecordCode,'',$detail_field);
        $sys_user = $this->sys_user();
        $check = $this->opt_confirm_check($record, $sys_user, $is_skip_lock);

        if ($check['status'] != '1') {
            return $check;
        }

        //更新订单状态
        $data = array();
        $data['order_status'] = 1;
        $data['check_time'] = $this->add_time();
        $data['confirm_person'] = $sys_user['user_name'];
        $data['notice_person'] = $sys_user['user_name'];
        $is_auto_notice = 0;
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('oms_notice', 'off_deliver_time', 'order_link'));
//		$_tm = strtotime($record['plan_send_time']) - time();
//                if ($cfg['oms_notice'] == 1){
//                    if($record['sale_mode'] == 'presale'){
//                        if ($_tm <= $cfg['off_deliver_time'] * 3600 *24 || $cfg['off_deliver_time'] == 0) {
//                            $is_auto_notice = 1;
//                        }
//                    }else{
//                        $is_auto_notice = 1;
//                    }
//                }

        $this->begin_trans();
        try {
            $where_arr = array('sell_record_code' => $record['sell_record_code'], 'order_status' => 0, 'is_problem' => 0);
            $ret = $this->update($data, $where_arr);
            $rows = $this->affected_rows();
            if ($ret['status'] != 1 || $rows != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '订单确认出错');
            }
            $remark = isset($request['batch']) ? $request['batch'] : '';
            $this->add_action($record['sell_record_code'], '确认', $remark);
            if ($cfg['order_link'] == 1) {
                $this->add_action_to_api($record['sale_channel_code'], $record['shop_code'], $record['deal_code_list'], 'confirm');
            }
            $msg = '操作成功';
//			if ($is_auto_notice == 1) {
//				$ret = $this->biz_notice_shipping($record, $sys_user, '自动');
//				if ($ret['status'] < 1) {
//					$this->rollback();
//					return $ret;
//				}
//				$msg = ($ret['status'] == 10) ? $ret['message'] : $msg;
//			}
            $ret_data = array('is_auto_notice' => $is_auto_notice);
            $this->commit();
            //开启系统参数调用云栈四期接口获取物流单号
            $sys_params = load_model('sys/SysParamsModel')->get_val_by_code(array('opt_confirm_get_cainiao'));
            if ($sys_params['opt_confirm_get_cainiao'] == 1) {
                $this->sell_record_get_cainiao_api($sellRecordCode);
            }
            return $this->format_ret(1, $ret_data, $msg);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }


    /**
     * 配发货获取云栈的方法无法调用，重新写的
     * 订单获取菜鸟物流,支持批量
     * @param $sell_record_code
     */
    function sell_record_get_cainiao_api($sell_record_code) {
        //校验快递公司
        $sell_record_code_arr = is_array($sell_record_code) ? $sell_record_code : array($sell_record_code);
        $no_record_rm = $this->check_print_type_by_record($sell_record_code_arr);
        //过滤没开启云栈的单子
        if (!empty($no_record_rm)) {
            //foreach ($no_record_rm as $record_code) {
            //    $this->add_action($record_code, '获取云栈热敏物流', '获取失败，快递没有开启云栈热敏');
            //}
            $sell_record_code_arr = array_diff($sell_record_code_arr, $no_record_rm);
            if (empty($sell_record_code_arr)) {
                return $this->format_ret(-1);
            }
        }

        //获取订单信息
        $sql_value = array();
        $sell_record_code_str = $this->arr_to_in_sql_value($sell_record_code_arr, 'sell_record_code', $sql_value);
        $sql = "SELECT * FROM oms_sell_record WHERE sell_record_code IN ({$sell_record_code_str})";
        $multi_sell_record = $this->db->get_all($sql, $sql_value);

        //校验热敏店铺
        $express_code_arr = array_column($multi_sell_record, 'express_code');
        $express_sql_value = array();
        $express_code_str = $this->arr_to_in_sql_value($express_code_arr, 'express_code', $express_sql_value);
        $express_sql = "SELECT express_code,express_name,rm_shop_code FROM base_express WHERE express_code IN ({$express_code_str})";
        $rm_shop = array();
        $express = $this->db->get_all($express_sql, $express_sql_value);
        foreach ($express as $express_val) {
            $rm_shop[$express_val['express_code']] = $express_val;
        }

        //组装接口数据
        $_receiver_ids = array();
        $params = array();
        foreach ($multi_sell_record as $row) {
            $record_code = $row['sell_record_code'];
            $express_code = $row['express_code'];
            if (!isset($rm_shop[$express_code]) || empty($rm_shop[$express_code]['rm_shop_code'])) {
                $this->add_action($record_code, '获取云栈热敏物流', '获取失败，' . $rm_shop[$express_code]['express_name'] . '没有关联热敏店铺');
                continue;
            }

            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($row['sell_record_code']);
            if (empty($record_decrypt_info)) {
                $this->add_action($record_code, '获取云栈热敏物流', '获取失败，数据解密失败订单：' . $row['sell_record_code'] . '，稍后尝试！');
                continue;
                //return $this->format_ret(-1, '', '数据解密失败订单：'.$row['sell_record_code'].'，稍后尝试！');
            }
            $row = array_merge($row, $record_decrypt_info);

            //收货人地址
            $row['receiver_addr'] = addslashes($row['receiver_addr']);
            //获取商品信息
            $row['goods_list'] = $this->tb_wlb_waybill_get_record_detail($row['sell_record_code']);
            //电话
            $row['receiver_mobile'] = empty($row['receiver_mobile']) ? $row['receiver_phone'] : $row['receiver_mobile'];
            //获取发货人信息
            $sql = "SELECT * FROM base_store WHERE store_code = :store_code";
            $store = $this->db->get_row($sql, array('store_code' => $row['store_code']));

            $store_sender_info = $store;
            $store_sender_info['tel'] = $store['contact_phone'];
            $sql = "SELECT * FROM base_shop WHERE shop_code = :shop_code";
            $shop = $this->db->get_row($sql, array('shop_code' => $row['shop_code']));
            if (!empty($shop['province']) && !empty($shop['city']) && !empty($shop['contact_person']) && !empty($shop['tel']) && !empty($shop['address'])) {
                $store_sender_info = $shop;
            }
            $row['sender_province'] = $store_sender_info['province'];
            $row['sender_city'] = $store_sender_info['city'];
            $row['sender_district'] = $store_sender_info['district'];
            $row['sender_addr'] = $store_sender_info['address'];
            $row['sender_street'] = $store_sender_info['street'];
            $_receiver_ids[] = $store_sender_info['province'];
            $_receiver_ids[] = $store_sender_info['city'];
            $_receiver_ids[] = $store_sender_info['district'];
            $_receiver_ids[] = $store_sender_info['street'];
            $row['contact_phone'] = $store_sender_info['tel'];
            $row['contact_person'] = $store_sender_info['contact_person'];

            //收货地址
            $_receiver_ids[] = $row['receiver_province'];
            $_receiver_ids[] = $row['receiver_city'];
            $_receiver_ids[] = $row['receiver_district'];
            $_receiver_ids[] = $row['receiver_street'];

            //淘宝参数长度限制
            $row['receiver_addr'] = mb_substr($row['receiver_addr'], 0, 100, 'UTF-8');
            $row['receiver_tel'] = substr($row['receiver_mobile'], 0, 20);

            $row['order_channels_type'] = load_model('oms/DeliverRecordModel')->get_cainiao_sale_channel($row['sale_channel_code'], $row['shop_code']);

            //京东货到付款
            if ($row['pay_type'] == 'cod' && $row['sale_channel_code'] == 'jingdong') {
                $payable_money = load_model('oms/DeliverRecordModel')->get_jd_cod_payable_money($row['deal_code_list']);
                if ($payable_money !== false) {
                    $row['payable_money'] = $payable_money;
                }
            }

            //当当货到付款
            if ($row['pay_type'] == 'cod' && $row['sale_channel_code'] == 'dangdang') {
                $dangdang_row = load_model('oms/DeliverRecordModel')->get_dangdang_print($row['deal_code_list']);
                if ($dangdang_row !== false) {
                    $row['payable_money'] = $dangdang_row['totalBarginPrice'];
                }
                $row['contact_person'] = !empty($dangdang_row['consignerName']) ? $dangdang_row['consignerName'] : $row['contact_person'];
                $row['contact_phone'] = !empty($dangdang_row['consignerTel']) ? $dangdang_row['consignerTel'] : $row['contact_phone'];
            }

            $params[$row['sell_record_code']] = $row;
            $express_code_data[] = $row['express_code'];
        }

        //没有满足条件的参数
        if (empty($params)) {
            return $this->format_ret(-1);
        }

        //获取配送方式的标准模板url
        $express_code_str = deal_array_with_quote($express_code_data);
        $template_sql = "SELECT bs.express_code, bs.company_code, spt.template_body_default FROM base_express bs, sys_print_templates spt WHERE bs.rm_id=spt.print_templates_id AND bs.print_type=2 AND bs.rm_id!='' AND bs.express_code IN ({$express_code_str}) AND spt.is_buildin = 3";
        $template_data = $this->db->get_all($template_sql);
        $print_template_data = array();
        $company_code_arr = array();
        foreach ($template_data as $value) {
            $print_template_data[$value['express_code']] = $value['template_body_default'];
            $company_code_arr[$value['express_code']] = $value['company_code'];
        }

        //收货地址
        $_new_receiver_ids = implode("','", array_unique($_receiver_ids));
        $_region_data = $this->db->get_all("SELECT id region_id, name region_name FROM base_area WHERE id IN('{$_new_receiver_ids}')");
        foreach ($_region_data as $_region) {
            $_receiver_data[$_region['region_id']] = $_region['region_name'];
        }

        $rm_api = array();
        //将地址的code装换成名称
        foreach ($params as $key => $sellRecord) {
            $params[$key]['sender_province_name'] = isset($_receiver_data[$sellRecord['sender_province']]) ? $_receiver_data[$sellRecord['sender_province']] : '';
            $params[$key]['sender_district_name'] = isset($_receiver_data[$sellRecord['sender_district']]) ? $_receiver_data[$sellRecord['sender_district']] : '';
            $params[$key]['sender_city_name'] = isset($_receiver_data[$sellRecord['sender_city']]) && !in_array($_receiver_data[$sellRecord['sender_city']], array('省直辖县级行政区划', '自治区直辖县级行政区划')) ? $_receiver_data[$sellRecord['sender_city']] : '';
            $params[$key]['sender_street_name'] = isset($_receiver_data[$sellRecord['sender_street']]) && !in_array($_receiver_data[$sellRecord['sender_city']], array('区直辖村模拟镇')) ? $_receiver_data[$sellRecord['sender_street']] : '';

            $params[$key]['receiver_province_name'] = isset($_receiver_data[$sellRecord['receiver_province']]) ? $_receiver_data[$sellRecord['receiver_province']] : '';
            $params[$key]['receiver_city_name'] = isset($_receiver_data[$sellRecord['receiver_city']]) && !in_array($_receiver_data[$sellRecord['receiver_city']], array('省直辖县级行政区划', '自治区直辖县级行政区划')) ? $_receiver_data[$sellRecord['receiver_city']] : '';
            $params[$key]['receiver_district_name'] = isset($_receiver_data[$sellRecord['receiver_district']]) ? $_receiver_data[$sellRecord['receiver_district']] : '';
            $params[$key]['receiver_street_name'] = isset($_receiver_data[$sellRecord['receiver_street']]) && !in_array($_receiver_data[$sellRecord['receiver_street']], array('区直辖村模拟镇')) ? $_receiver_data[$sellRecord['receiver_street']] : '';

            $params[$key]['sender_name'] = $sellRecord['contact_person'];
            $params[$key]['sender_phone'] = $sellRecord['contact_phone'];

            $express_code = $sellRecord['express_code'];
            $params[$key]['template_url'] = $print_template_data[$express_code];
            $params[$key]['express_code'] = $company_code_arr[$express_code];
            //根据热敏店铺分组
            $rm_shop_code = $rm_shop[$express_code]['rm_shop_code'];
            $rm_api[$rm_shop_code][$key] = $params[$key];
        }

        //调取接口
        foreach ($rm_api as $api_rm_shop => $params_arr) {
            $client = new TaobaoClient($api_rm_shop);
            //分页处理
            $params_arr_chunk = array_chunk($params_arr, 10, true);
            foreach ($params_arr_chunk as $api_params) {
                $result = $client->cloudWlbWaybillPrint($api_params);
                foreach ($result as $sellRecordCode => $waybill) {
                    if ($waybill['status'] != 1 || !isset($waybill['data'][0]['waybill_code'])) {
                        $action_note = "获取失败,订单" . $sellRecordCode . ": " . $waybill['message'];
                        $this->add_action($sellRecordCode, '获取云栈热敏物流', $action_note);
                        continue;
                    }
                    $dat = json_encode($waybill['data'][0]);
                    if ($dat == null) {
                        $action_note = "获取失败，订单" . $sellRecordCode . ": json解析失败";
                        $this->add_action($sellRecordCode, '获取云栈热敏物流', $action_note);
                        continue;
                    }
                    $express_data = array('express_no' => $waybill['data'][0]['waybill_code'], 'express_data' => $dat);
                    $this->save_cainiao_express_no($sellRecordCode, $express_data);
                }
            }
        }
        return $this->format_ret(1);
    }

    /**
     *保存物流信息
     * @param $sell_record_code
     * @param $express_data
     * @param string $type
     * @return array
     */
    function save_cainiao_express_no($sell_record_code, $express_data, $type = 'yzrm') {
        $record = $this->db->get_row("select * from oms_sell_record where sell_record_code=:sell_record_code", array(':sell_record_code' => $sell_record_code));
        //修改单号是云栈要取消单号
        if ($express_data['express_no'] != $record['express_no'] && !empty($record['express_no'])) {
            $this->cancle_cainiao_wlb_waybil_action($record);
        }

        $this->update_exp('oms_sell_record', $express_data, array('sell_record_code' => $sell_record_code));
        $affected_rows = $this->db->affected_rows();
        if ($affected_rows != 1) {
            return $this->format_ret(-1, '获取物流信息失败，请检查订单状态');
        }

        if ($type == 'yzrm') {
            $action = '获取云栈热敏物流';
            $msg = '快递单号由' . $record['express_no'] . '修改为' . $express_data['express_no'];
            load_model('oms/SellRecordActionModel')->add_action($sell_record_code, $action, $msg);
        }
        if ($type == 'jdrm') {
            $action = '获取京东热敏物流';
            $msg = '快递单号由' . $record['express_no'] . '修改为' . $express_data['express_no'];
            load_model('oms/SellRecordActionModel')->add_action($sell_record_code, $action, $msg);
        }
        if ($type == 'sfrm') {
            $action = '获取顺丰热敏物流';
            $msg = '快递单号由' . $record['express_no'] . '修改为' . $express_data['express_no'];
            load_model('oms/SellRecordActionModel')->add_action($sell_record_code, $action, $msg);
        }
    }

    /**
     * 菜鸟取消接口
     * @param $row
     * @return array
     */
    function cancle_cainiao_wlb_waybil_action($row) {
        if (isset($row['express_data']) && !empty($row['express_data']) && !empty($row['express_no'])) {
            $d = json_decode($row['express_data'], true);
            //print_config为云栈二期，object_id为菜鸟云打印打印数据
            if (!empty($d) && (isset($d['print_config']) || isset($d['object_id']))) {//云栈
                $ret_shipping = load_model('base/ShippingModel')->get_row(array('express_code' => $row['express_code']));

                if (empty($ret_shipping['data']['rm_shop_code'])) {
                    return $this->format_ret(1, '', '找不到配送方式绑定的店铺');
                }
                $shop_code = &$ret_shipping['data']['rm_shop_code'];
                $param = array(
                    'real_user_id' => $d['trade_order_info']['real_user_id'],
                    'trade_order_list' => $row['sell_record_code'],
                    //  'cp_code'=>$row['express_code'],
                    'waybill_code' => $row['express_no'],
                );
                if (isset($row['deal_code_list']) && $row['sale_channel_code'] == 'taobao') {
                    $deal_code_list_arr = explode(",", $row['deal_code_list']);
                    foreach ($deal_code_list_arr as &$deal_code) {
                        $deal_code = (string)$deal_code;
                    }
                    $param['trade_order_list'] = $deal_code_list_arr;
                }

                $param['cp_code'] = load_model('oms/DeliverRecordModel')->get_express_company($row['express_code']);

                if (isset($d['trade_order_info']['package_id'])) {
                    $param['package_id'] = $d['trade_order_info']['package_id'];
                }
                $param['waybill_type'] = isset($d['object_id']) && !empty($d['object_id']) ? 'cainiao_cloud' : '';

                //接口取消快递
                $ret = $this->taobao_wlbwaybillicancle($shop_code, $row['sell_record_code'], $param);
                if ($ret['status'] != 1) {
                    return $this->format_ret(-1, '', '取消失败');
                }else{
                    $this->update_exp('oms_sell_record', array('express_no' => '', 'express_data' => ''), array('sell_record_code' => $row['sell_record_code']));
                    $this->update_exp('oms_sell_record_notice', array('express_no' => '', 'express_data' => ''), array('sell_record_code' => $row['sell_record_code']));
                }
            }
        }
        return $this->format_ret(1);
    }

    /**
     * 取消云栈接口
     * @param $shop_code
     * @param $sell_record_code
     * @param $param
     * @return array
     */
    function taobao_wlbwaybillicancle($shop_code, $sell_record_code, $param) {
        $client = new TaobaoClient($shop_code);
        if (isset($param['waybill_type']) && $param['waybill_type'] == 'cainiao_cloud') {
            $ret = $client->cloudWlbWaybillCancel($param['cp_code'], $param['waybill_code']);
        } else {
            $ret = $client->taobaoWlbWaybillICancel($param);
        }

        if (isset($ret['cancel_result']) && $ret['cancel_result'] === true) {
            load_model('oms/SellRecordModel')->add_action($sell_record_code, '取消云栈物流单号');
        } else {
            $msg = '';
            if (!empty($ret['error_response'])) {
                $msg = $ret['error_response']['sub_msg'];
            }
            load_model('oms/SellRecordModel')->add_action($sell_record_code, '取消云栈物流失败:' . $msg);
            return $this->format_ret(-1, '取消失败');
        }
        return $this->format_ret(1);
    }

    /**
     * 获取明细
     * @param $deliver_record_id
     * @return array
     */
    function tb_wlb_waybill_get_record_detail($sell_record_code) {
        $sql = "select d.sku,d.num from oms_sell_record_detail d WHERE d.sell_record_code=:sell_record_code";
        $data = $this->db->get_all($sql, array(':sell_record_code' => $sell_record_code));

        $_goods_data = array();
        if (!empty($data)) {
            foreach ($data as $val) {
                $key_arr = array('spec1_name', 'spec2_name', 'goods_name');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
                $goods_name = $sku_info['goods_name'] . "[{$sku_info['spec1_name']},{$sku_info['spec2_name']}]";
                $_goods_data[] = array('goods_name' => $goods_name, 'num' => $val['num']);
            }
        }
        return $_goods_data;
    }

    /**
     * 校验快递公司是否是否绑定云栈四期模板
     * @param $sell_record_code
     * @return array
     */
    function check_print_type_by_record($sell_record_code_arr) {
        //判断快递是绑定云栈四期
        $no_record_rm = array();
        $sql_values = array();
        $sell_record_code_str = $this->arr_to_in_sql_value($sell_record_code_arr, 'sell_record_code', $sql_values);
        $sql = "SELECT r1.sell_record_code,r2.print_type,r2.rm_id,r2.express_name FROM oms_sell_record  AS r1 INNER JOIN base_express AS r2 ON r1.express_code=r2.express_code WHERE r1.sell_record_code IN ({$sell_record_code_str})";
        $print_info = $this->db->get_all($sql, $sql_values);

        if (empty($print_info)) {
            return $sell_record_code_arr;
        }

        $rm_sell_record_code = array_column($print_info, 'sell_record_code');
        $no_record_rm = array_diff($sell_record_code_arr, $rm_sell_record_code);
        foreach ($print_info as $key => $value) {
            if ($value['print_type'] != 2) {
                unset($print_info[$key]);
                $no_record_rm[] = $value['sell_record_code'];
                continue;
            }
            if (empty($value['rm_id'])) {
                unset($print_info[$key]);
                $no_record_rm[] = $value['sell_record_code'];
                continue;
            }
        }
        //判断快递绑定的模板是否为云栈四期
        if (!empty($print_info)) {
            $rm_id_arr = array_unique(array_column($print_info, 'rm_id'));
            $sql_values = array();
            $print_templates_id_str = $this->arr_to_in_sql_value($rm_id_arr, 'print_templates_id', $sql_values);
            $sql = "SELECT print_templates_id FROM sys_print_templates WHERE is_buildin=3 AND print_templates_id IN ({$print_templates_id_str})";
            $print_templates_id_arr = $this->db->get_all_col($sql, $sql_values);
            foreach ($print_info as $value) {
                if (!in_array($value['rm_id'], $print_templates_id_arr)) {
                    $no_record_rm[] = $value['sell_record_code'];
                }
            }
        }

        return $no_record_rm;
    }


    function biz_notice_shipping($record, $detailList, $sys_user, $remark = '') {
        $data = array('shipping_status' => 1, 'is_notice_time' => date('Y-m-d H:i:s'), 'is_lock' => 0, 'notice_person' => $sys_user['user_name'], 'is_lock_person' => '');
        $where = array('sell_record_code' => $record['sell_record_code'], 'order_status' => 1, 'shipping_status' => 0);

        $check_num = $this->db->get_value("select count(1) from oms_sell_record_notice");
        $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($record['store_code']);

        if ($check_num > 100000 && $wms_system_code === FALSE) {
            return $this->format_ret(10, '', '通知配货待生成波次单数量超过10万单上限，暂不能通知配货！');
        }
        $this->begin_trans();
        $ret = $this->update($data, $where);
        //受影响行数为0，则更新失败，返回
        $run_num = $this->db->affected_rows();
        if ($ret['status'] < 1 || $run_num == 0) {
            $this->rollback();
            return $this->format_ret('-1', '', 'SELL_RECORD_NOTICE_ERROR');
        } else {
            $ret = load_model('wms/WmsEntryModel')->add($record['sell_record_code'], 'sell_record', $record['store_code']);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
        }
        //如果是门店仓发货数据推送到o2o_oms_trade表
        $ret = load_model('o2o/O2oEntryModel')->add($record['sell_record_code'], 'sell_record', $record['store_code']);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        //通知配货数据生成
        load_model('oms/SellRecordNoticeModel')->create_record_notice($record['sell_record_code']);

        $this->add_action($record['sell_record_code'], $remark . '通知配货');
        $this->add_action_to_api($record['sale_channel_code'], $record['shop_code'], $record['deal_code_list'], 'notice');
        $this->add_action($record['sell_record_code'], '自动解锁');
        $this->commit();
        return $this->format_ret(1);
    }

    //通知配货
    function opt_notice_shipping($sellRecordCode, $request = array(), $is_skip_lock = 0) {

        $record = $this->get_record_by_code($sellRecordCode);
        $detailList = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_notice_shipping_check($record, $detailList, $sys_user, $is_skip_lock);
        if ($check['status'] != '1') {
            return $check;
        }
        $remark = isset($request['batch']) ? $request['batch'] : '';
        $ret = $this->biz_notice_shipping($record, $detailList, $sys_user, $remark);
        return $ret;
    }

    function opt_notice_shipping_check($record, $detail, $sysuser, $is_skip_lock = 0) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_notice_shipping')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($is_skip_lock == 0) {
            if ($record['is_lock'] != 1) {
                return $this->return_value(-1, '非锁定订单不能操作');
            }
            if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
                return $this->return_value(-1, '已锁定订单不能操作');
            }
        }

        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_problem'] == 1) {
            return $this->return_value(-1, '问题订单不能操作');
        }

        if ($record['order_status'] != 1) {
            return $this->return_value(-1, '非已确定订单不能操作');
        }
        if ($record['pay_type'] != 'cod' && $record['pay_status'] != 2) {
            return $this->return_value(-1, '未付款订单不能操作');
        }
        if ($record['shipping_status'] > 0) {
            return $this->return_value(-1, '已通知配货订单不能操作');
        }
        return $this->return_value(1, '');
    }

    //取消通知配货
    function opt_unnotice_shipping($sellRecordCode, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detailList = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_unnotice_shipping_check($record, $detailList, $sys_user);
        if ($check['status'] != '1') {
            return $check;
        }

        $this->begin_trans();

        $data = array('shipping_status' => 0, 'is_notice_time' => '0000-00-00 00:00:00');
        $where = array('sell_record_code' => $sellRecordCode, 'order_status' => 1, 'shipping_status' => 1);

        $ret = $this->update($data, $where);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret('-1', '', 'SELL_RECORD_NOTICE_ERROR');
        }
        $ret = load_model('wms/WmsEntryModel')->cancel($record['sell_record_code'], 'sell_record', $record['store_code']);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        //门店发货仓
        $ret = load_model('o2o/O2oEntryModel')->cancel($record['sell_record_code'], 'sell_record', $record['store_code']);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }

        $ret = load_model('mid/MidBaseModel')->cancel_mid_record($record['sell_record_code'], 'sell_record', $record['store_code']);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }


        //删除通知配货数据

        load_model('oms/SellRecordNoticeModel')->delete_record_notice(array($record['sell_record_code']));

        $this->commit();
        $this->add_action($record['sell_record_code'], '取消通知配货');
        $ret['status'] = $ret['status'] > 0 ? 1 : 0;
        return $ret;
    }

    function opt_unnotice_shipping_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_unnotice_shipping')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] != 1) {
            return $this->return_value(-1, '非锁定订单不能操作');
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_problem'] == 1) {
            return $this->return_value(-1, '问题订单不能操作');
        }

        if ($record['order_status'] != 1) {
            return $this->return_value(-1, '非已确定订单不能操作');
        }
        if ($record['pay_type'] != 'cod' && $record['pay_status'] != 2) {
            return $this->return_value(-1, '未付款订单不能操作');
        }
        if ($record['shipping_status'] == 0) {
            return $this->return_value(-1, '没有通知配货订单不能操作');
        }
        if ($record['shipping_status'] >= 2 && $record['shipping_status'] < 4) {
            return $this->return_value(-1, '已生成波次不能取消通知配货');
        }
        if ($record['shipping_status'] == 4) {
            return $this->return_value(-1, '已发货订单不能取消通知配货');
        }

        if ($record['shipping_status'] > 0) {
            $ret_o2o = load_model('o2o/O2oEntryModel')->is_o2o_store_record($record['store_code']);
            if ($ret_o2o['status'] > 0) {
                return $this->format_ret(-1, '', '订单仓库对接外部系统发货,通知配货后,不能取消通知配货');
            }
        }


        return $this->return_value(1, '');
    }

    //缺货拆单
    function opt_short_split_check($record, $detail, $sys_user) {
        if ($record['lock_inv_status'] != 2) {
            return $this->return_value(-1, '缺货订单不能拆分');
        }
        return $this->return_value(1, '');
    }

    //拆单
    function opt_split_order_check($record, $detail, $sys_user) {
        if ($record['is_lock'] == 0) {
            return $this->return_value(-1, '未锁定订单不能操作');
        }
        if ($record['pay_type'] == 'cod') {
            return $this->format_ret(-1, '', '货到付款单的订不能进行此操作');
        }
        if ($record['order_status'] != 0) {
            return $this->format_ret(-1, '', '只有未确认的订单才能进行此操作');
        }
        if ($record['pay_type'] != 'cod' && $record['pay_status'] != 2) {
            return $this->return_value(-1, '未付款订单不能操作');
        }
//		if ($record['is_problem'] > 0) {
//			return $this->format_ret(-1, '', '问题单不能进行此操作');
//		}
        if ($record['is_pending'] > 0) {
            return $this->format_ret(-1, '', '挂起订单不能进行此操作');
        }
        if ($record['is_lock'] > 0 && $sys_user['user_code'] != $record['is_lock_person'] && $sys_user['is_manage'] != 1) {
            return $this->format_ret(-1, '', '锁定订单不能进行此操作');
        }
        if (($record['is_fenxiao'] == 1 || $record['is_fenxiao'] == 2) && $record['is_fx_settlement'] == 1) {
            return $this->return_value(-1, '已结算订单不能操作');
        }
        return $this->return_value(1, '');
    }

    //加急单
    function opt_set_rush_check($record, $detail, $sysuser) {
//        if ($record['is_lock'] == 0) {
        //            return $this->return_value(-1, '未锁定订单不能操作');
        //        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['order_status'] != 0 && $record['pay_type'] != 'cod') {
            return $this->return_value(-1, '非货到付款订单不是未确认状态');
        }
        if ($record['pay_status'] != 2 && $record['pay_type'] != 'cod') {
            return $this->return_value(-1, '非货到付款未付款订单不能操作加急按钮');
        }
        if ($record['pay_type'] == 'cod' && $record['order_status'] != 0) {
            return $this->return_value(-1, '货单付款未确认的订单才能操作加急按钮');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作加急按钮');
        }
        if ($record['is_problem'] == 1) {
            return $this->return_value(-1, '问题订单不能操作加急按钮');
        }
        if ($record['shipping_status'] == 0 && $record['order_status'] != 3 && $record['must_occupy_inv'] == 1 && $record['lock_inv_status'] != 1) {
            return $this->return_value(-1, '缺货订单不能操作加急按钮');
        }
        return $this->return_value(1, '');
    }

    function opt_unconfirm_check($record, $detail, $sysuser) {
        /* if (false == get_operate_purview("order/sell_record/return_sure")) {
          return $this->return_value(-1, '无订单重置权限');
         */
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_unconfirm')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] != 1) {
            return $this->return_value(-1, '非锁定订单不能操作');
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_problem'] == 1) {
            return $this->return_value(-1, '问题订单不能操作');
        }

        if ($record['order_status'] != 1) {
            return $this->return_value(-1, '非已确定订单不能操作');
        }
        if ($record['pay_type'] != 'cod' && $record['pay_status'] != 2) {
            return $this->return_value(-1, '未付款订单不能操作');
        }
        if ($record['shipping_status'] > 0) {
            return $this->return_value(-1, '已通知配货订单不能操作');
        }

        return $this->return_value(1, '');
    }

    /**
     * 重置订单状态
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_unconfirm($sellRecordCode, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_unconfirm_check($record, $detail, $sys_user);

        if ($check['status'] != '1') {
            return $check;
        }

        $this->begin_trans();
        try {
            //取消预分配
            /* $result = load_model('stm/stock_lock_record')->external_do_cancel($record['sell_record_code']);
              if ($result['status'] < 0) {
              throw new Exception("取消预分配库存失败:" . $result['message']);
             */
            $data = array();
            $data['order_status'] = 0;
            $data['wms_request_time'] = 0;
            $data['check_time'] = '0000-00-00 00:00:00';
            $ret = $this->update($data, array('sell_record_code' => $record['sell_record_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '订单重置出错');
            }

            $this->add_action($record['sell_record_code'], '取消确认');

            $this->commit();
            //调用云栈取消接口
            if (!empty($record['express_no']) && !empty($record['express_data'])) {
                $this->cancle_cainiao_wlb_waybil_action($record);
            }
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function opt_print_send_check($record, $detail, $sysuser) {
        return $this->opt_send_check($record, $detail, $sysuser);
    }

    function opt_print_express_check($record, $detail, $sysuser) {
        //TODO: 增加打印状态
        return $this->opt_send_check($record, $detail, $sysuser);
    }

    function opt_send_check($record, $detail, $sysuser, $send_mode = 'scan') {
        if ($send_mode == 'handwork_send') {
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_send')) {
                return $this->return_value(-1, "无权访问");
            }
        }

        $ret = load_model('wms/WmsEntryModel')->check_wms_store($record['store_code']);
        if ($ret['status'] > 0) {
            return $this->format_ret(-100, '', '订单仓库对接wms，不允许手工发货');
        }
        if ($record['shipping_status'] > 0) {
            $ret_o2o = load_model('o2o/O2oEntryModel')->is_o2o_store_record($record['store_code']);
            if ($ret_o2o['status'] > 0) {
                return $this->format_ret(-1, '', '订单仓库对接外部系统发货,通知配货后,不能取消通知配货');
            }
        }


        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if (empty($detail)) {
            return $this->return_value(-1, '订单无明细');
        }

        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_problem'] == 1) {
            return $this->return_value(-1, '问题订单不能操作');
        }
        if ($send_mode == 'handwork_send') {
            if ($record['is_lock'] != 1) {
                return $this->return_value(-1, '非锁定订单不能操作');
            } else {
                if ($sysuser['user_code'] != $record['is_lock_person']) {
                    return $this->return_value(-1, '他人锁定的订单不能操作');
                }
            }
        } else {
            /*
              if ($record['shipping_status'] < 2) {
              return $this->return_value(-2, '订单未生成波次单不能操作');
             */
        }

        if ($record['order_status'] != '1') {
            return $this->return_value(-1, '非已确定订单不能操作');
        }

        if ($record['shipping_status'] == 4) {
            return $this->return_value(-2, '已发货订单不能操作');
        }

        return $this->return_value(1, '');
    }

    function check_refund($detail) {
        $deal_code_arr = array();
        foreach ($detail as $sub_detail) {
            $deal_code_arr[] = $sub_detail['deal_code'];
        }
        $ret = $this->check_refund_by_deal_code($deal_code_arr);
        return $ret;
    }

    function check_refund_by_deal_code($deal_code_arr) {
        $deal_code_list = "'" . join("','", array_unique($deal_code_arr)) . "'";
        $sql = "select tid from api_refund where tid  in({$deal_code_list}) and status = 1 and is_change < 1";
        $tid_arr = ctx()->db->get_all_col($sql);
        if (!empty($tid_arr)) {
            $tid_list = join(',', array_unique($tid_arr));
            return $this->format_ret(-1, '', "交易号{$tid_list}存在未处理的退单");
        }
        return $this->format_ret(1);
    }

    function check_trade_closed($record) {
        if ($record['sale_channel_code'] != 'taobao') {
            return $this->format_ret(1, '');
        }
        if ($record['pay_type'] != 'cod') {
            return $this->format_ret(1, '');
        }
        //淘宝货到付款订单校验订单是否关闭
        $sql = "select status from api_taobao_trade where tid=:tid";
        $api_trade = $this->db->get_row($sql, array(':tid' => $record['deal_code_list']));
        if (!empty($api_trade) && in_array($api_trade['status'], array('TRADE_CLOSED', 'TRADE_CLOSED_BY_TAOBAO'))) {
            return $this->format_ret(-1, '', "交易号{$record['deal_code_list']}交易已关闭");
        }
        return $this->format_ret(1, '');
    }

    //订单发货核心类 $send_mode scan | waves_send | handwork_send | wms_send 扫描发货 波次单发货 手工发货
    function sell_record_send($record, $detail, $sys_user, $send_mode = 'scan', $is_check_sell = 1, $is_record = 1, $force_negative_inv = 0) {
        if (empty($record)) {
            return $this->format_ret(-1, '', '找不到此订单');
        }
        if (empty($detail)) {
            return $this->format_ret(-1, '', '订单明细不能为空');
        }
        $send_mode_map = array(
            'scan' => '扫描发货',
            'waves_send' => '波次单发货',
            'handwork_send' => '手工发货',
            'wms_send' => 'WMS发货',
            'api_send' => 'api发货',
            'o2o_send' => '门店发货',
        );
        $send_mode_name = isset($send_mode_map[$send_mode]) ? $send_mode_map[$send_mode] : $send_mode;
        if ($is_check_sell == 1) {
            $check = $this->opt_send_check($record, $detail, $sys_user, $send_mode);
            if ($check['status'] != '1') {
                return $check;
            }
        }
        //wms发货成功，不需要检测退单
        if ($send_mode != 'wms_send' && $send_mode != 'api_send') {
            $ret = $this->check_refund($detail);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $ret1 = $this->check_trade_closed($record);
            if ($ret1['status'] < 0) {
                return $ret1;
            }
        }
        if (empty($record['express_code'])) {
            return $this->format_ret(-1, '', '快递方式不正确');
        }
        //手工发货去掉检测快递单号
        if ($send_mode != 'handwork_send') {
            if ('KHZT' != $record['express_code'] && empty($record['express_no'])) {
                return $this->format_ret(-1, '', '快递单号不正确');
            }
        }

        if (($send_mode == 'scan' || $send_mode == 'waves_send') && $record['shipping_status'] != 3) {
            return $this->format_ret(-1, '', '订单未生成波次单');
        }
        if ($record['shipping_status'] == 3 && empty($record['waves_record_id'])) {
            return $this->format_ret(-1, '', '订单找不到对应波次单');
        }
        if (!empty($record['waves_record_id'])) {
            $sql = "select record_code,is_accept,is_cancel from oms_waves_record where waves_record_id = :waves_record_id";
            $db_waves = ctx()->db->get_row($sql, array(':waves_record_id' => $record['waves_record_id']));
//	        if ($db_waves['is_accept'] == 0){
            // 				return $this->format_ret(-1,'',$db_waves['record_code'].'波次单未验收');
            //	        }
            if ($db_waves['is_cancel'] == 1) {
                return $this->format_ret(-1, '', $db_waves['record_code'] . '波次单已作废');
            }
            $sql = "select is_cancel,is_deliver,express_code,express_no from oms_deliver_record where sell_record_code = :sell_record_code and waves_record_id = :waves_record_id";
            $db_deliver_record = ctx()->db->get_row($sql, array(':sell_record_code' => $record['sell_record_code'], ':waves_record_id' => $record['waves_record_id']));
            if (empty($db_deliver_record)) {
                return $this->format_ret(-1, '', '订单找不到对应波次单,波次单id=' . $record['waves_record_id']);
            }
            if ($db_deliver_record['is_cancel'] > 0) {
                return $this->format_ret(-1, '', '此订单在波次单里已作废');
            }
            if ($db_deliver_record['is_deliver'] > 0) {
                return $this->format_ret(-1, '', '此订单在波次单里已发货');
            }
            if ($db_deliver_record['express_code'] != $record['express_code']) {
                return $this->format_ret(-1, '', '订单和波次单里设定的快递方式不一样');
            }
            if ($db_deliver_record['express_no'] != $record['express_no']) {
                return $this->format_ret(-1, '', '订单和波次单里设定的快递单号不一样');
            }
        }
        $this->begin_trans();
        try {
            $outTime = $this->add_time();
            $outDate = date('Y-m-d');

            //解除锁定库存, 扣减库存
            $ret = load_model('oms/SellRecordLofModel')->deduction_inv($record['sell_record_code'], $record['store_code'], $outDate, $force_negative_inv);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '解除库存锁定失败:' . $ret['message']);
            }
            $deli_person = "delivery_person='" . CTX()->get_session('user_code') . "'";
            $sql = "update oms_sell_record set {$deli_person}, shipping_status = :shipping_status,delivery_time = :delivery_time,delivery_date = :delivery_date where sell_record_code = :sell_record_code and order_status<>4 and shipping_status<=4 and is_pending = 0 and is_problem = 0";
            $sql_v = array(
                ":shipping_status" => 4,
                ':delivery_time' => $outTime,
                ':delivery_date' => $outDate,
                ':sell_record_code' => $record['sell_record_code'],
            );
            $ret = ctx()->db->query($sql, $sql_v);
            if ($ret != true) {
                $this->rollback();
                return $this->format_ret(-1, '', '发货失败,请检查订单状态');
            }
            $aff_row = ctx()->db->affected_rows();
            if ($aff_row == 0) {
                $this->rollback();
                return $this->format_ret(-1, '', '发货失败,订单数据未更新');
            }

            if (!empty($record['waves_record_id'])) {
                $sql = "update oms_deliver_record set is_deliver = 1,delivery_time = :delivery_time,delivery_date = :delivery_date where is_cancel = 0 and is_deliver=0 and sell_record_code = :sell_record_code";
                $sql_v = array(
                    ':delivery_time' => $outTime,
                    ':delivery_date' => $outDate,
                    ':sell_record_code' => $record['sell_record_code'],
                );
                $ret = ctx()->db->query($sql, $sql_v);
                if ($ret != true) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '发货失败,请检查波次单中订单状态');
                }
                $aff_row = ctx()->db->affected_rows();
                if ($aff_row == 0) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '发货失败,波次单中订单数据未更新');
                }
            }

            //插入api_order_send 回写表
            $code_list = explode(',', $record['deal_code_list']);
            // $express_code = $this->get_logistics_companies($record['sale_channel_code'],$record['express_code']);
            $company_code = load_model('oms/DeliverRecordModel')->get_express_company($record['express_code']);
            foreach ($code_list as $deal_code) {
                //若销售平台为'后台',直接回写本地，默认为'未上传'
                $status = ($record['sale_channel_code'] == 'houtai') ? '2' : '0';
                $api_send_data[] = array(
                    'source' => $record['sale_channel_code'],
                    'shop_code' => $record['shop_code'],
                    'sell_record_code' => $record['sell_record_code'],
                    'tid' => $deal_code,
                    'express_code' => $record['express_code'],
                    'company_code' => $company_code,
                    'express_no' => $record['express_no'],
                    'send_time' => $outTime,
                    'status' => $status
                );
            }
            $ret = $this->insert_multi_exp('api_order_send', $api_send_data, true);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1, '', '订单数据插入回写队列出错');
            }
            if ($record['sale_channel_code'] == 'houtai') {
                $ret = $this->update_exp('oms_sell_record', array('is_back' => 2, 'is_back_time' => date('Y-m-d H:i:s')), array('sell_record_code' => $record['sell_record_code']));
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '后台订单本地回写失败');
                }
                load_model('oms/SellRecordActionModel')->add_action($record['sell_record_code'], '本地回写', '后台订单,系统自动设置为本地回写');
            }

            //删除多余数据
            load_model('oms/SellRecordNoticeModel')->delete_record_notice(array($record['sell_record_code']));
            $action_note = '';
            if (!empty($sys_user['user_source']) == 'PDA') {
                $action_note = '操作人：' . $sys_user['user_name'];
            }
            //订单发货成功，订单金额已付>应付，系统会自动生成一张退款类型的售后服务单
            $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('delivery_create_return'));
            if ($cfg['delivery_create_return'] == 1) {
                if ($record['paid_money'] > $record['payable_money']) {
                    $ret = load_model('oms/SellReturnOptModel')->create_return_record_by_cancel($record['sell_record_code'], 'delivery');
                    if ($ret['status'] < 0) {
                        $this->rollback();
                        return $this->format_ret(-1, '', '订单金额已付款大于应付款，系统自动生成一张退款类型的售后服务单失败');
                    }
                    $action_note .= '订单金额已付款大于应付款，系统自动生成一张退款类型的售后服务单，退单号为：' . $ret['data'];
                }
            }
            //分销订单结算更改状态 由预结算变为已结算
            $is_fenxiao_sql = "select is_fenxiao,fx_payable_money,fx_express_money,fenxiao_code from oms_sell_record where sell_record_code = :sell_record_code ";
            $fenxiao_info = $this->db->get_row($is_fenxiao_sql, array(":sell_record_code" => $record['sell_record_code']));
            /* if ($fenxiao_info['is_fenxiao'] == 1 ) {
              $ret = $this->create_fx_run_account($fenxiao_info, $record['sell_record_code']);
              //                            if($ret['status'] < 0){
              //                                $this->rollback();
              //                                return $ret;
              //                            }
              } */
            if ($is_record) {
                $this->add_action($record['sell_record_code'], $send_mode_name, $action_note);
            }
            $sql_customer = "select rl.consume_num,rl.consume_money,r2.payable_money,r2.customer_code from crm_customer rl inner join oms_sell_record r2 on rl.customer_code = r2.customer_code where r2.sell_record_code = :sell_record_code and shipping_status = 4";
            $result = $this->db->get_row($sql_customer, array(':sell_record_code' => $record['sell_record_code']));
            if (!empty($result)) {
                $ret = parent::update_exp('crm_customer', array('consume_num' => ($result['consume_num'] + 1), 'consume_money' => ($result['consume_money'] + $result['payable_money'])), array('customer_code' => $result['customer_code']));
            }
            //发票数据更新
            load_model('oms/invoice/OmsSellInvoiceModel')->set_sell_invoice($record['sell_record_code']);
            
            $this->commit();
            $kdniao = load_model('sys/SysParamsModel')->get_val_by_code('kdniao_enable');
            if ($kdniao['kdniao_enable'] == 1) {
                if (preg_match("/^[A-Za-z\d]{6,50}$/i", $record['express_no'])) {
                    //将订单快递信息插入到订阅中间表
                    $sub_data['OrderCode'] = $record['sell_record_code'];
                    $sub_data['CompanyCode'] = $company_code;
                    $sub_data['No'] = $record['express_no'];
                    $ret_sub = load_model('api/kdniao/ApiKdSubscribeModel', FALSE)->insert_subscribe(array($sub_data));
                }
            }
            $opt_send_flag = 1;
            $opt_send_msg = '发货成功';
        } catch (Exception $e) {
            $this->rollback();
            $opt_send_flag = -1;
            $opt_send_msg = '发货失败:' . $e->getMessage();
        }

        //订单回写 这个是调用接口的,不放在事务里
        if ($opt_send_flag == 1) {
            //补单生成一张已验收的调整单
            if($record['is_replenish'] == 1){
                //添加并验收调整单
                $this->create_stock_by_record($record);
            }
            if (!empty($record['waves_record_id'])) {
                $this->update_waves_record_deliver_status($record['waves_record_id']);
            }
            // 写入结算表
            //$r = load_model('oms/SellSettlementModel')->new_settlement_sell($record['sell_record_code']);
            $r = load_model('oms/SellSettlementModel')->generate_settlement_data($record['sell_record_code'], 1);
            //$this->rollback();
            //return $r;
            if ($r['status'] != '1') {
                return $this->format_ret(-1, '', '结算:' . $r['message']);
            }
            //写入全链路状态
            load_model('oms/SellRecordActionModel')->add_action_to_api($record['sale_channel_code'], $record['shop_code'], $record['deal_code_list'], 'scan');
            return $this->format_ret(1, $opt_send_msg);
        } else {
            return $this->format_ret(-1, $opt_send_msg);
        }
    }

    function create_fx_run_account($fenxiao_info, $sell_record_code) { // 废弃
        $ret = load_model('fx/AccountSettlementModel')->update_status($sell_record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $params = array();
        $params['relation_code'] = $sell_record_code;
        $params['custom_code'] = $fenxiao_info['fenxiao_code'];
        $params['account_money'] = -($fenxiao_info['fx_payable_money'] + $fenxiao_info['fx_express_money']);
        $params['record_type'] = 'sales_settlement';
        $params['remark'] = '分销订单结算,分销商余额扣款' . ($fenxiao_info['fx_payable_money'] + $fenxiao_info['fx_express_money']) . '元';
        $ret = load_model('fx/RunningAccountModel')->insert_running_account($params);
        if ($ret['status'] < 0) {
            return $ret;
        }

        $custom_ret = load_model('fx/AccountModel')->update_custom_account_money($params['custom_code'], $params['account_money'], 'sales_settlement');
        if ($custom_ret === FALSE) {
            return $this->format_ret(-1, '', '更改分销商账户金额失败！');
        }
        $record = $this->get_record_by_code($sell_record_code);
        $fx_moeny_detail_insert = load_model('fx/PayMoneyDetailModel')->fx_money_detail_insert($record, 'sales_settlemented', '订单发货，扣款成功');
        if ($fx_moeny_detail_insert['status'] < 0) {
            return $this->format_ret(-1, '', '分销结算，更改冻结金额失败！');
        }
    }

    //更新波次单发货状态
    function update_waves_record_deliver_status($waves_record_id) {
        $sql = "select count(*) from oms_deliver_record where is_cancel=0 and is_deliver=0 and waves_record_id=:waves_record_id";
        $no_deliver_num = ctx()->db->getOne($sql, array(':waves_record_id' => $waves_record_id));
        $sql = "select count(*) from oms_deliver_record where is_cancel=0 and is_deliver=1 and waves_record_id=:waves_record_id";
        $deliver_num = ctx()->db->getOne($sql, array(':waves_record_id' => $waves_record_id));
        if ($no_deliver_num == 0 || ($no_deliver_num > 0 && $deliver_num > 0)) {
            if ($no_deliver_num == 0 && $deliver_num > 0) {
                $data_waves = array('is_deliver' => '1', 'delivery_time' => time());
            }
            if ($no_deliver_num == 0 && $deliver_num == 0) {
                $data_waves = array('is_deliver' => '0');
            }
            if ($no_deliver_num > 0 && $deliver_num > 0) {
//部分发货
                $data_waves = array('is_deliver' => '2', 'delivery_time' => time());
            }
            $ret = $this->db->update('oms_waves_record', $data_waves, array("waves_record_id" => $waves_record_id));
            if ($ret != true) {
                return $this->format_ret(-1, '', '更新波次单发货状态失败');
            }
        }
        return $this->format_ret(1);
    }

    /**
     *
     * 方法名                               api_order_send
     *
     * 功能描述                           发货接口
     *
     * @author      BaiSon PHP
     * @date        2015-08-21
     * @param       array $param
     *              array(
     *                  必选: 'record_code',
     *                   必选: 'express_code',
     *                   必选: 'express_no',
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    function api_order_send(array $param) {
        $check_key = array(
            'express_code' => '快递公司代码',
            'express_no' => '快递单号',
            'sell_record_code' => '单据编号',
        );
        $msg = '';
        foreach ($check_key as $key => $val) {
            if (!isset($param[$key])) {
                $msg .= "缺少" . $val . ",";
            }
        }
        if ($msg != '') {
            return $this->format_ret(-10001, '', $msg);
        }
        $sell_record_code = $param['sell_record_code'];
        $record = $this->get_record_by_code($sell_record_code);
        if (!empty($record)) {
            $record['express_code'] = $param['express_code'];
            $record['express_no'] = $param['express_no'];
        }

        $data = array('express_code' => $param['express_code'], 'express_no' => $param['express_no']);
        $this->db->update('oms_sell_record', $data, array('sell_record_code' => $sell_record_code));

        load_model('oms/SellRecordActionModel')->record_log_check = 0;
        $detail = $this->get_detail_list_by_code($sell_record_code);

        $ret = $this->sell_record_send($record, $detail, 'open_api', 'api_send', 1);
        //wms发货，写入WMS中间表
        if ($ret['status'] == '-100') {
            //wms收发货
            $data = array('express_code' => $param['express_code'], 'express_no' => $param['express_no'], 'flow_end_time' => date('Y-m-d H:i:s'));
            $ret_data = array('status' => 1, 'data' => $data);
            $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($sell_record_code, 'sell_record', $ret_data, 1);
            if ($ret['status'] > 0) {
                $ret['message'] = '发货成功';
            }
        }
        load_model('oms/SellRecordActionModel')->record_log_check = 1;

        $revert_data = array('sell_record_code' => $param['sell_record_code']);
        if (!empty($record['waves_record_id'])) {
            $sql = 'SELECT record_code,is_deliver FROM oms_waves_record WHERE waves_record_id=:id';
            $wave_data = $this->db->get_row($sql, array(':id' => $record['waves_record_id']));
            $revert_data = array_merge($revert_data, $wave_data);
        }
        $msg = $ret['status'] == 1 ? '发货成功' : $ret['message'];
        return $this->format_ret($ret['status'], $revert_data, $ret['message']);
    }

    /**
     * 发货
     * @param $sellRecordCode
     * @param array $request
     * @return array
     */
    function opt_send($sellRecordCode, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $ret = load_model('wms/WmsEntryModel')->check_wms_store($record['store_code']);
        if ($ret['status'] > 0) {
            return $this->format_ret(-1, '', '订单仓库对接wms，不允许手工发货');
        }
        if ($record['shipping_status'] > 0) {
            $ret_o2o = load_model('o2o/O2oEntryModel')->is_o2o_store_record($record['store_code']);
            if ($ret_o2o['status'] > 0) {
                return $this->format_ret(-1, '', '订单仓库对接外部系统发货,通知配货后,不能取消通知配货');
            }
        }
        $status = load_model('mid/MidBaseModel')->check_is_mid('scan', 'sell_record', $record['store_code']);
        if ($status !== false) {
            return $this->format_ret(-1, '', '仓库对接' . $status . '，不允许手工发货');
        }


        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_send_check($record, $detail, $sys_user, 'handwork_send');
        if ($check['status'] != '1') {
            return $check;
        }
        if (empty($request['express_code'])) {
            return $this->format_ret(-1, '', '快递方式不正确');
        }
        //手工发货去掉检测快递单号
        if (!empty($request['check_express_no'])) {
            if ($this->check_express_no($request['express_code'], $request['express_no']) == false) {
                return $this->format_ret(-1, '', '快递单号不合法');
            }
        }
//         if ('KHZT' != $request['express_code'] && empty($request['express_no'])){
        // 			return $this->format_ret(-1,'','快递单号不正确');
        // 	    }else{
        // 		    if (!empty($request['check_express_no'])){
        // 		        if ($this->check_express_no($request['express_code'], $request['express_no']) == false) {
        // 		 			return $this->format_ret(-1,'','快递单号不合法');
        // 		        }
        // 		    }
        // 	    }
        $sql = "update oms_sell_record set express_code = :express_code,express_no = :express_no where order_status<>3 and shipping_status<=4 and sell_record_code = :sell_record_code";
        ctx()->db->query($sql, array(':express_code' => $request['express_code'], ':express_no' => $request['express_no'], ':sell_record_code' => $sellRecordCode));
        if (!empty($record['waves_record_id'])) {
            $sql = "update oms_deliver_record set express_code = :express_code,express_no = :express_no where is_cancel=0 and is_deliver = 0 and sell_record_code = :sell_record_code";
            ctx()->db->query($sql, array(':express_code' => $request['express_code'], ':express_no' => $request['express_no'], ':sell_record_code' => $sellRecordCode));
        }
        $record['express_code'] = $request['express_code'];
        $record['express_no'] = $request['express_no'];
        $ret = $this->sell_record_send($record, $detail, $sys_user, 'handwork_send', 0);

        if ($ret['status'] != 1) {
            return $ret;
        }
        //if (empty($record['waves_record_id']) && $ret['status'] == 1 ){
        //手工发货解锁
        $data = array('is_lock' => 0, 'is_lock_person' => '', 'is_lock_time' => '');
        $ret = $this->update($data, array('sell_record_code' => $record['sell_record_code']));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '解锁订单出错');
        }
        $this->add_action($record['sell_record_code'], '解锁');
        //}
        /* 手工发货暂时不插入表
          if (empty($record['waves_record_id']) && $ret['status'] == 1 ){

          $record['waves_record_id'] = '';
          $record['sort_no'] = 0;
          //保存发货订单
          $r = $this->db->insert('oms_deliver_record', $record);
          if(!$r){
          throw new Exception('保存发货订单失败', '-1');
          }
          //新的发货订单ID
          $deliverRecordID = $this->db->insert_id();

          //保存发货订单明细
          foreach($detail as $sub_detail){
          if($sub_detail['sell_record_code'] != $record['sell_record_code']){
          continue; //FIXME: 暂时方案
          }

          $sub_detail = array();
          $sub_detail['deliver_record_id'] = $deliverRecordID;
          $sub_detail['waves_record_id'] = '';

          //保存明细
          $r = $this->db->insert('oms_deliver_record_detail', $sub_detail);
          if(!$r){
          throw new Exception('保存发货订单明细失败', '-1');
          }
          }
         */
        if (!empty($record['waves_record_id']) && $ret['order_status'] == 1) {
            $data = $this->db->get_row("select sell_record_count from oms_waves_record where waves_record_id = '{$record['waves_record_id']}' ");
            if ($data['sell_record_count'] == 1) {
                $sql = " update oms_waves_record set is_accept = 1 where waves_record_id = '{$record['waves_record_id']}' ";
                ctx()->db->query($sql);
            }
        }

        return $ret;
    }

    function get_logistics_companies($sale_channel_code, $express_code) {
        $data = $this->db->get_row("select company_code from base_express where express_code='{$express_code}'");
        $company_code = $data['company_code'];
        $express_code = $company_code;
        if ($sale_channel_code == 'jingdong') {
            $data = $this->db->get_row("select logistics_id from api_jingdong_logistics_companies where company_code='{$company_code}'");
            $express_code = $data['logistics_id'];
        }
        return $express_code;
    }

    function opt_back_check($record, $detail, $sysuser) {
        /* if (false == get_operate_purview("order/sell_record/send")) {
          return $this->return_value(-1, '无订单发货权限');
         */
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if (empty($detail)) {
            return $this->return_value(-1, '订单无明细');
        }
        if ($record['is_lock'] == 1) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_problem'] == 1) {
            return $this->return_value(-1, '问题订单不能操作');
        }

        if ($record['order_status'] != '1') {
            return $this->return_value(-1, '非已确定订单不能操作');
        }

        if ($record['shipping_status'] != 7) {
            return $this->return_value(-1, '非已发货订单不能操作');
        }
        if ($record['is_back'] == 1) {
            return $this->return_value(-1, '非回写订单不能操作');
        }

        return $this->return_value(1, '');
    }

    /**
     * 订单回写
     * @param $sellRecordCode
     * @param bool $isLocal
     * @return array
     * @throws Exception
     */
    function opt_back($sellRecordCode, $isLocal = false) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_back_check($record, $detail, $sys_user);

        if ($check['status'] != '1') {
            return $check;
        }

        $this->begin_trans();
        try {
            if (!$isLocal) {
                //非标识回写
                $fn = 'opt_back_api_' . $record['source'];
                $ret = load_model('oms/SellRecordBackModel')->$fn($record, $detail, $sys_user);
                if ($ret['status'] != 1) {
                    throw new Exception($ret['message']);
                }
            }

            $data = array(
                "order_status" => '5',
                "is_back" => '1',
                //"is_back_try_num"=>$record['is_back_try_num']+1,
                //"is_back_reason"=>$ret['data'],
                "is_back_time" => $this->add_time(),
            );
            $ret = $this->update($data, array("sell_record_code" => $record['sell_record_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '保存订单出错');
            }

            $this->add_action($record['sell_record_code'], '回写', '成功');

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();

            $this->add_action($record['sell_record_code'], '回写失败:' . $e->getMessage());

            $data = array(
                "is_back" => '2',
                "is_back_try_num" => $record['is_back_try_num'] + 1,
                "is_back_reason" => $e->getMessage(),
                "is_back_time" => $this->add_time(),
            );
            $ret = $this->update($data, array("sell_record_code" => $record['sell_record_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '保存订单出错');
            }

            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function opt_problem_check($record, $detail, $sysuser, $type = 0, $is_skip_lock = 0) {
        //#############权限
        $sysuser['is_api'] = isset($sysuser['is_api']) ? $sysuser['is_api'] : 0;
        if ($sysuser['is_api'] != 1) {
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_problem')) {
                return $this->return_value(-1, "无权访问");
            }
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($is_skip_lock == 0) {
            if ($record['is_lock'] != 1) {
                return $this->return_value(-1, '非锁定订单不能操作');
            }
            if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
                return $this->return_value(-1, '已锁定订单不能操作');
            }
        }
        if ($record['is_problem'] == 1) {
            return $this->return_value(-1, '问题订单不能操作');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['shipping_status'] >= 4) {
            return $this->return_value(-1, '已发货订单不能操作');
        }
        if ($record['order_status'] == 3) {
            return $this->return_value(-1, '已作废订单不能操作');
        }
        if ($record['order_status'] > 0) {
            return $this->return_value(-1, '未确认的订单才能操作');
        }
        if (!$type || $type != 'CHANGE_GOODS_MAKEUP') {
            if ($record['pay_status'] < 2 && $record['pay_type'] != 'cod') {
                return $this->return_value(-1, '未付款的订单不能操作');
            }
        }
        return $this->return_value(1, '');
    }

    function opt_unproblem_check($record, $detail,$sysuser,$is_fx) {
        //#############权限
        $login_type = CTX()->get_session('login_type');
        if($is_fx == 1 || $login_type == 2){
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/fx_unproblem')) {
                   return $this->return_value(-1, "无权访问");
            }  
        }else{
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_unproblem') && $login_type != 2) {
                    return $this->return_value(-1, "无权访问");
            }
        }
        
        
        
        
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] != 1) {
            return $this->return_value(-1, '非锁定订单不能操作');
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_problem'] == 0) {
            return $this->return_value(-1, '非问题订单不能操作');
        }

        if ($record['shipping_status'] >= 4) {
            return $this->return_value(-1, '已发货订单不能操作');
        }
        if ($record['order_status'] == 3) {
            return $this->return_value(-1, '已作废订单不能操作');
        }

        return $this->return_value(1, '');
    }

    function opt_problem_get_tag($sellRecordCode, $problem_code, $request = array()) {
        $_problem_code_arr = array();
        if (is_array($problem_code)) {
            $_problem_code_arr = $problem_code;
        } else {
            $_problem_code_arr[0] = $problem_code;
        }

        $ql_arr = load_model('base/QuestionLabelModel')->get_map_data();
        
        $log = '';
        $ins_oms_sell_record_tag = array();
        $desc = isset($request['desc']) ? $request['desc'] : '';
        foreach ($_problem_code_arr as $t_code) {
            $tag = array(
                'sell_record_code' => $sellRecordCode,
                'tag_type' => 'problem',
                'tag_v' => $t_code,
                'tag_desc' => $ql_arr[$t_code],
                'desc' => $desc,
            );
            $log .= "{$ql_arr[$t_code]}";
            if (isset($request['problem_remark']) && $request['problem_remark'] !== '') {
//                $tag['tag_desc'] = $request['problem_remark'];
                $log .= ",{$request['problem_remark']}";
            } /* else {
              $tag['tag_desc'] = '';
              } */
            $log .= ";";
            $ins_oms_sell_record_tag[] = $tag;           
        }
        return $this->format_ret(1, array('tag_data' => $ins_oms_sell_record_tag, 'log' => $log));
    }

    //设为问题单
    function opt_problem($sellRecordCode, $problem_code, $request = array(), $is_skip_lock = 0, $is_api = 0) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        if ($is_api == 1) {
            $sys_user = array('user_code' => 'admin', 'is_api' => '1');
        } else {
            $sys_user = $this->sys_user();
        }

        if ($request['type'] && $request['type'] == 'CHANGE_GOODS_MAKEUP') {
            //如果是换货单 换货金额大于已付金额时，设问 生成问题单
            $check = $this->opt_problem_check($record, $detail, $sys_user, 'CHANGE_GOODS_MAKEUP', $is_skip_lock);
            unset($request['type']);
        } else {
            $check = $this->opt_problem_check($record, $detail, $sys_user, $problem_code, $is_skip_lock);
        }

        if ($check['status'] != '1') {
            return $check;
        }
        $ret = $this->opt_problem_get_tag($sellRecordCode, $problem_code, $request);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ins_oms_sell_record_tag = $ret['data']['tag_data'];
        $log = $ret['data']['log'];

        $this->begin_trans();
        try {
            $insert_ret = M('oms_sell_record_tag')->insert($ins_oms_sell_record_tag);
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '订单设为问题单出错');
            }
            $data = array();
            $data['is_problem'] = 1;
            $ret = M('oms_sell_record')->update($data, array('sell_record_code' => $record['sell_record_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '订单设为问题单出错');
            }

            $this->add_action($record['sell_record_code'], '设为问题单', $log);

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    //返回正常单
    function opt_unproblem($sellRecordCode,$request = array(),$is_fx = 0) {
        $ret = $this->opt_unproblem_fn($sellRecordCode,$request,$is_fx);
        if ($ret['status'] < 0) {
            return $ret;
        }

        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('unproblem_order_auto_confirm'));
        $login_type = CTX()->get_session('login_type');
        if ($cfg['unproblem_order_auto_confirm'] == 1 && $login_type != 2) {
            $record = $this->get_record_by_code($sellRecordCode);
            if ($record['pay_status'] == 2 || $record['pay_type'] == 'cod') {
                if(in_array($record['is_fenxiao'], array(1, 2))) { //分销订单，确认之前结算
                    $ret1 = $this->opt_settlement($sellRecordCode);
                    if($ret1['status'] == -2){
                        //记录订单修改地址日志
                        load_model("oms/SellRecordModel")->add_action($this->sell_record_data['sell_record_code'], '分销结算失败', $ret1['message']);
                    }
                }
                $ret = $this->opt_confirm($sellRecordCode, $request);
            }
        }
        return $ret;
    }

    function opt_unproblem_fn($sellRecordCode, $request = array(),$is_fx) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_unproblem_check($record, $detail, $sys_user,$is_fx);

        if ($check['status'] != '1') {
            return $check;
        }

        $this->begin_trans();
        try {
            $sql = "DELETE FROM oms_sell_record_tag WHERE sell_record_code=:code AND tag_type='problem'";
            $this->query($sql, array(':code' => $record['sell_record_code']));

            $data = array();
            $data['is_problem'] = 0;
            $data['order_status'] = 0;
            $data['check_time'] = '0000-00-00 00:00:00';
            $ret = M('oms_sell_record')->update($data, array('sell_record_code' => $record['sell_record_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '订单返回正常单出错');
            }
            
            $ret = $this->update_exp('oms_sell_record_detail', array('api_refund_num' => 0, 'api_refund_desc' => ''), array('sell_record_code' => $record['sell_record_code']));
            if ($ret['status'] < 1) {
                return $this->format_ret(-1, '', '订单返回正常单数据刷新出错');
            }

            $this->add_action($record['sell_record_code'], '返回正常单');

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function opt_copy_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_copy')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person'] && $record['order_status'] != 3) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['is_pending'] == 1 && $record['order_status'] != 3) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_problem'] == 1 && $record['order_status'] != 3) {
            return $this->return_value(-1, '问题订单不能操作');
        }
        if ($record['shipping_status'] < 4 && $record['order_status'] != 3) {
            return $this->return_value(-1, '只有发货订单或作废订单才能操作');
        }
        return $this->return_value(1, '');
    }

    /*
     * $add_record_fld
     * pay_time,pay_status,order_status,shipping_status,lock_inv_status,
     * invoice_type,invoice_title,invoice_content,invoice_money,invoice_status
     * payable_money,paid_money
     * order_money,goods_money
     */

    function get_sell_base_info($record, $detail, $add_record_fld = null, $add_record_mx_fld = null) {
        //sell_record_code deal_code is_handwork
        $sell_fld = 'deal_code,deal_code_list,sale_channel_code,store_code,shop_code,user_code,pay_type,pay_code,customer_code,customer_address_id,buyer_name,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,receiver_street,receiver_address,receiver_addr,receiver_zip_code,receiver_mobile,receiver_phone,receiver_email,express_code,goods_num,sku_num,goods_weigh,buyer_remark,seller_remark,seller_flag,order_remark,express_money,delivery_money,is_wap,is_jhs,point_fee,alipay_point_fee,coupon_fee,yfx_fee,is_fenxiao,create_time,record_time,fenxiao_id,fenxiao_name,fx_payable_money,fenxiao_code,alipay_no,fx_express_money,is_replenish,is_replenish_from';
        if (!empty($add_record_fld)) {
            $sell_fld = $sell_fld . ',' . $add_record_fld;
        }
        //sell_record_code
        $sell_mx_fld = 'deal_code,sub_deal_code,goods_code,sku_id,sku,goods_price,num,goods_weigh,avg_money,cost_price,platform_spec,is_gift,pic_path,combo_sku,plan_send_time,sale_mode,trade_price,fx_amount';
        if (!empty($add_record_mx_fld)) {
            $sell_mx_fld = $sell_mx_fld . ',' . $add_record_mx_fld;
        }
        $sell_fld_arr = explode(',', $sell_fld);
        $sell_mx_fld_arr = explode(',', $sell_mx_fld);
        $new_record = array();
        foreach ($sell_fld_arr as $sfld) {
            $new_record[$sfld] = $record[$sfld];
        }
        $deal_code_list = array();
        foreach ($detail as $k => $sub_detail) {
            foreach ($sell_mx_fld_arr as $sfld) {
                $new_record['mx'][$k][$sfld] = $sub_detail[$sfld];
            }
            if (!in_array($sub_detail['deal_code'], $deal_code_list) && !empty($sub_detail['deal_code'])) {
                array_push($deal_code_list, $sub_detail['deal_code']);
            }
        }
        $new_record['deal_code'] = empty($deal_code_list) ? $new_record['deal_code'] : implode(";", $deal_code_list);
        $new_record['mx'] = isset($new_record['mx']) ? $new_record['mx'] : array();
        return $new_record;
    }

    //订单交易号+guid + time 的处理
    function get_guid_deal_code($_deal_code, $time = null) {

        $_deal_code = preg_replace('/;guid\d+/i', '', $_deal_code);
        $time = empty($time) ? rand(99, 9999999999) : $time;
        $_deal_code = $_deal_code . ';guid' . str_pad($time, 10, STR_PAD_LEFT);

        return $_deal_code;
    }

    //拆合单 复制单 要设置的字段信息
    function copy_sell_base_info($record, $detail) {
        $new_record = $this->get_sell_base_info($record, $detail);
        $new_sell_record_code = load_model('oms/SellRecordModel')->new_code();
        $new_record['sell_record_code'] = $new_sell_record_code;
        $new_record['deal_code'] = $this->get_guid_deal_code($new_record['deal_code']);
        //echo '<hr/>new_record<xmp>'.var_export($new_record,true).'</xmp>';die;
        foreach ($new_record['mx'] as $k => $sub_record) {
            $new_record['mx'][$k]['sell_record_code'] = $new_sell_record_code;
        }
        return $new_record;
    }

    function opt_copy($sellRecordCode, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_copy_check($record, $detail, $sys_user);

        if ($check['status'] != '1') {
            return $check;
        }
        $new_record = $this->copy_sell_base_info($record, $detail);
        $new_record['is_copy'] = 1;
        $new_record['is_copy_from'] = $sellRecordCode;
        $new_sell_record_code = $new_record['sell_record_code'];
        $ret = $this->js_sell_plan_send_time($new_record, $new_record['mx']);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $new_record = $ret['data'];
        $ret = $this->js_record_price($new_record, $new_record['mx']);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $new_record = $ret['data'];
        $new_record['store_remark'] = $record['store_remark'];
        //echo '<hr/>new_record<xmp>'.var_export($new_record,true).'</xmp>';//die;
        $this->begin_trans();
        try {
            $new_record['must_occupy_inv'] = $new_record['pay_type'] == 'cod' ? 1 : 0;
            M('oms_sell_record')->insert($new_record);
            M('oms_sell_record_detail')->insert($new_record['mx']);
            $ret = $this->lock_detail($new_record, $new_record['mx'], 1);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $this->cancel_return_record($sellRecordCode, $record); //如果开启作废订单生成退款单参数，作废后复制订单成功，需要将生成的退款单作废
            $this->add_action($record['sell_record_code'], '复制订单', "复制新订单编号：" . $this->sell_record_code_href($new_sell_record_code));
            $this->add_action($new_sell_record_code, '创建订单', "从" . $this->sell_record_code_href($sellRecordCode) . "订单复制的新订单");
            $this->commit();
            return $this->format_ret(1, $new_sell_record_code);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 如果开启作废订单生成退款单参数，作废后复制订单成功，需要将生成的退款单作废
     */
    public function cancel_return_record($sellRecordCode, $record) {
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('direct_cancel'));
        if ($cfg['direct_cancel'] == 1 && $record['order_status'] == 3 && $record['pay_status'] == 2) {
            $sql = "select * from oms_sell_return where sell_record_code = :sell_record_code";
            $return_record = $this->db->get_row($sql, array(':sell_record_code' => $sellRecordCode));
            if ($return_record['return_type'] == 1 && !empty($return_record)) {
                $ret = load_model('oms/SellReturnModel')->update(array('return_order_status' => 3), array('sell_return_code' => $return_record['sell_return_code']));
                load_model('oms/SellReturnModel')->add_action($return_record, '作废订单', '开启作废订单生成退款单参数，作废后复制订单成功，生成的退款单作废');
            }
        }
    }

    function opt_pending_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_pending')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] != 1) {
            return $this->return_value(-1, '非锁定订单不能操作');
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }

        if ($record['shipping_status'] >= 4) {
            return $this->return_value(-1, '已发货订单不能操作');
        }
        if ($record['order_status'] == 3) {
            return $this->return_value(-1, '已作废订单不能操作');
        }
        if ($record['order_status'] != 0) {
            return $this->return_value(-1, '未确认的订单才能挂起');
        }
        return $this->return_value(1, '');
    }

    function opt_unpending_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_unpending')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($sysuser['user_code'] != 'sys_schedule' && $record['is_lock'] != 1) {
            return $this->return_value(-1, '非锁定订单不能操作');
        }
        if ($sysuser['user_code'] != 'sys_schedule' && $record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['is_pending'] == 0) {
            return $this->return_value(-1, '未挂起订单不能操作');
        }
        if ($record['shipping_status'] >= 4) {
            return $this->return_value(-1, '已发货订单不能操作');
        }
        if ($record['order_status'] == 3) {
            return $this->return_value(-1, '已作废订单不能操作');
        }
        return $this->return_value(1, '');
    }

    //挂起
    function opt_pending($sellRecordCode, $is_pending_code, $is_pending_reason, $is_unpending_time = null, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_pending_check($record, $detail, $sys_user);
        if ($check['status'] < 1) {
            return $check;
        }

        $this->begin_trans();
        try {
            //已通知配货的订单挂起后要改为已确认未通知配货
            $shipping_1 = false;
            if ($record['shipping_status'] > 0 && $record['shipping_status'] < 4) {
                $shipping_1 = true;
            }
            //如果已生成波次，先取消波次单
            if (!empty($record['waves_record_id'])) {
                $ret = load_model("oms/WavesRecordModel")->cancel($record['waves_record_id']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
            }
            $ret = $this->biz_pending($is_pending_code, $sellRecordCode, $is_unpending_time, $shipping_1, $record['store_code'], $is_pending_reason);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            $log_msg = "订单已挂起," . ($is_pending_reason !== '' ? $is_pending_reason . "," : '');
            if (!empty($is_unpending_time)) {
                $log_msg .= "订单将在 {$is_unpending_time} 自动解挂。";
            }
            $remark = isset($request['batch']) && $request['batch'] !== '' ? $request['batch'] . ',' : '';
            $this->add_action($record['sell_record_code'], '挂起', $remark . $log_msg . $ret['message']);

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->return_value(-1, '', $e->getMessage());
        }
    }

    //解挂
    function opt_unpending($sellRecordCode, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        if ($record['is_pending_code'] == 'wait_check_refund') {
            return $this->format_ret(-1, '', '等待财务审核退款单,不允许手工解挂。');
        }
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_unpending_check($record, $detail, $sys_user);

        if ($check['status'] != '1') {
            return $check;
        }

        $this->begin_trans();
        try {
            $ret = $this->biz_unpending($sellRecordCode);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $remark = isset($request['batch']) ? $request['batch'] : '';
            $this->add_action($record['sell_record_code'], '解挂', $remark);

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->return_value(-1, '', $e->getMessage());
        }
    }

    //解挂定时器
    function cli_unpending() {
        $status = "SELECT `value` FROM sys_params WHERE param_code = 'cli_decrypt_api_order_time';";
        $status_records = $this->db->get_row($status);
        if($status_records['value'] == 1){
            $sql = "select sell_record_code from oms_sell_record where is_pending_code!=:is_pending_code
         and is_pending=:is_pending and order_status=:order_status and is_unpending_time<=:is_unpending_time";
        } else {
            $sql = "select sell_record_code from oms_sell_record where is_pending_code!=:is_pending_code
         and is_pending=:is_pending and order_status=:order_status and is_unpending_time != '0000-00-00 00:00:00' and is_unpending_time<=:is_unpending_time";
        }
        $where = array(
            ':is_pending' => '1', //挂起状态
            ':order_status' => '0', //非作废订单 ,未确认订单
            ':is_pending_code' => 'wait_check_refund',
            ':is_unpending_time' => date('Y-m-d H:i:s'),
        );
        $sell_records = $this->db->get_all($sql, $where);
        if (empty($sell_records)) {
            echo "没有要解挂的订单";
            return;
        }
        foreach ($sell_records as $record_row) {
            $ret = $this->opt_unpending($record_row['sell_record_code']);
            if ($ret['status'] == 1) {
                echo "订单{$record_row['sell_record_code']}解挂成功\n";
            } else {
                echo "订单{$record_row['sell_record_code']}解挂失败" . $ret['message'] . "\n";
            }
        }
    }

    //挂起biz
    function biz_pending($is_pending_code, $sell_record_code, $is_unpending_time = null, $shipping_1 = false, $store_code = '', $is_pending_reason) {

        $data = array();
        $data['is_pending'] = 1;
        $data['is_pending_time'] = date('Y-m-d H:i:s');
        $data['is_pending_code'] = $is_pending_code;

        $sql_values = array(':is_pending' => 1,
            ':is_pending_time' => date('Y-m-d H:i:s'),
            ':is_pending_code' => $is_pending_code,
            ':sell_record_code' => $sell_record_code,
            ':is_pending_memo' => $is_pending_reason,
        );
        $sql_str = '';
        if ($shipping_1) {
            $sql_str .= ',shipping_status = :shipping_status';
            $sql_values[':shipping_status'] = 0;
        }

        if (!empty($is_unpending_time)) {
            $sql_str .= ',is_unpending_time = :is_unpending_time';
            $sql_values['is_unpending_time'] = $is_unpending_time;
        }

        $sql = " UPDATE oms_sell_record
				SET is_pending = :is_pending,
				 is_pending_time = :is_pending_time,
				 is_pending_code = :is_pending_code {$sql_str}
                                 ,is_pending_memo = :is_pending_memo
				WHERE
					sell_record_code = :sell_record_code
				AND order_status <> 3
				AND shipping_status < 4;
				and is_pending = 0";
        $this->begin_trans();
        $ret = $this->db->query($sql, $sql_values);
        if ($ret != true) {
            $this->rollback();
            return $this->format_ret(-1, '', '订单挂起失败');
        }
        $aff_row = ctx()->db->affected_rows();
        if ($aff_row == 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '订单挂起失败,未刷新数据');
        }

        $ret = load_model('wms/WmsEntryModel')->cancel($sell_record_code, 'sell_record', $store_code);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        } else {
            $wms_op_msg = '';
            if ($ret['status'] == 10) {
                $wms_op_msg = $ret['message'];
            }
        }

        $ret = load_model('mid/MidBaseModel')->cancel_mid_record($sell_record_code, 'sell_record', $store_code);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        $this->commit();

        return $this->format_ret(1, '', $wms_op_msg);
    }

    //解挂biz
    function biz_unpending($sell_record_code) {
        $data = array();
        $data['is_pending'] = 0;
        $data['is_pending_time'] = 0;
        $data['is_unpending_time'] = 0;
        $data['is_pending_code'] = 0;
        $data['is_pending_memo'] = 0;
        $ret = M('oms_sell_record')->update($data, array('sell_record_code' => $sell_record_code));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '订单解挂出错');
        }
        return $this->format_ret(1);
    }

    //打标
    function opt_label($sellRecordCode, $order_label_code, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $this->begin_trans();
        try {
            if (empty($order_label_code)) {
                //若订单已经没有标签则不需要再删除
                $check = $this->check_sell_record_tag($sellRecordCode);
                if (empty($check)) {
                    return $this->format_ret(1);
                }
                $ret = $this->delete_exp('oms_sell_record_tag', array('sell_record_code' => $sellRecordCode, 'tag_type' => 'order_tag'));
                //$this->update_exp('oms_sell_record',array('have_order_tag'=>0),array('sell_record_code' => $sellRecordCode,'order_status'=>1,'shipping_status'=>1));

                if (!$ret) {
                    return $this->format_ret('-1', '', '删除失败！');
                }
                $remark = "删除已打标签！";
            }else{
                //$this->update_exp('oms_sell_record',array('have_order_tag'=>1),array('sell_record_code =' => $sellRecordCode,'order_status'=>1,'shipping_status'=>1));
                $ret = load_model('oms/SellRecordTagModel')->add_record_tag($sellRecordCode, array($order_label_code));
                if ($ret['status'] < 0) {
                    return $ret;
                }
                $order_label_name = $this->db->get_value("select order_label_name from base_order_label where order_label_code = '{$order_label_code}'");
                $remark = "订单打标:" . $order_label_name;
            }
            //日志
            $this->add_action($record['sell_record_code'], '打标', $remark);
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->return_value(-1, '', $e->getMessage());
        }
    }

    //批量打标
    function opt_batch_label($sellRecordCode, $order_label_code, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $this->begin_trans();
        try {
            if (empty($order_label_code)) {//删除已打标签
                //若订单已经没有标签则不需要再删除
                $check = $this->check_sell_record_tag($sellRecordCode);
                if (empty($check)) {
                    $this->rollback();
                    return $this->format_ret(1);
                }
                $ret = $this->delete_exp('oms_sell_record_tag', array('sell_record_code' => $sellRecordCode, 'tag_type' => 'order_tag'));
                if (!$ret) {
                    $this->rollback();
                    return $this->format_ret('-1', '', '删除失败！');
                }
                $remark = "删除已打标签！";
            }else{
                $ret = load_model('oms/SellRecordTagModel')->add_record_tag($sellRecordCode, array($order_label_code));
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                $order_label_name = $this->db->get_value("select order_label_name from base_order_label where order_label_code = '{$order_label_code}'");
                $remark = "订单打标:" . $order_label_name;
            }
            //日志
            $this->add_action($record['sell_record_code'], '批量打标', $remark);
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->return_value(-1, '', $e->getMessage());
        }
    }

    /**
     * 验证订单标签是否已存在
     * @param $sellRecordCode
     * @return array|bool|mixed
     */
    function check_sell_record_tag($sellRecordCode) {
        $sql = "SELECT 1 FROM oms_sell_record_tag WHERE sell_record_code=:sell_record_code AND tag_type=:tag_type";
        $sql_value[':sell_record_code'] = $sellRecordCode;
        $sql_value[':tag_type'] = 'order_tag';
        $ret = $this->db->get_row($sql, $sql_value);
        return $ret;
    }


    //解挂biz
    function biz_label($sell_record_code, $order_label_code) {
        $data = array();
        $data['order_label_code'] = $order_label_code;
        $ret = M('oms_sell_record')->update($data, array('sell_record_code' => $sell_record_code));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '订单打标出错');
        }
        return $this->format_ret(1);
    }

    //均摊方法
    public function payment_ft($trade_ft_money, $goods) {
        $items_count = count($goods);
        if ($items_count < 1) {
            return $this->put_error(-1, '均摊缺少明细数据');
        }

        $total_ft_ed = 0; //已经分摊掉的金额
        $total_goods_payment = 0; //所有商品明细的总额
        foreach ($goods as $k => $sub_goods) {
            $total_goods_payment += $sub_goods['payment'];
        }

        foreach ($goods as $k => $data) {
            if ($items_count != 1) {
                $cur_ft = $trade_ft_money * ($data['payment'] / $total_goods_payment);
                $cur_ft = floor($cur_ft * 100) / 100;
                $total_ft_ed = bcadd($total_ft_ed, $cur_ft, 2);
                $items_count--;
            } else {
                $cur_ft = bcsub($trade_ft_money, $total_ft_ed, 2);
                $total_ft_ed = bcadd($total_ft_ed, $cur_ft, 2);
            }
            $goods[$k]['avg_money'] = $cur_ft;
        }

        if (bccomp($total_ft_ed, $trade_ft_money, 2) != 0) {
            return $this->put_error(-1, '均摊的数据总和验证失败');
        }

        return $goods;
    }

    /* 操作btn 导航
     * 如果是问题单 is_problem == 1, btn_opt_unproblem
     * 如果是挂起 is_pending == 1,btn_opt_unpending
     * 如果是锁定(通知配货自动解锁) is_lock == 1,如果非锁定人 btn_opt_force_unlock
     * 如果是未付款 pay_status == 0 && pay_type!='cod',btn_opt_pay
     * 如果是未确认 ((pay_status == 2 && pay_type!='cod') || (pay_status == 0 && pay_type=='cod')) && order_status == 0 ,btn_opt_confirm
     * 如果是未通知配货 order_status == 1 && shipping_status == 0,btn_opt_notice_shipping
     */

    function btn_nav($sell_record_code) {
        $record = $this->get_record_by_code($sell_record_code);
        if (empty($record)) {
            return $this->format_ret(-1, $record);
        }
        $sys_user = $this->sys_user();
        $is_problem = $record['is_problem'];
        $is_pending = $record['is_pending'];
        $is_lock = $record['is_lock'];

        $pay_type = $record['pay_type'];
        $pay_status = $record['pay_status'];
        $order_status = $record['order_status'];
        $shipping_status = $record['shipping_status'];

        $next_opt = '';
        if ($is_problem == 1) {
            $next_opt = 'opt_unproblem';
        }
        if ($next_opt == '' && $is_pending == 1) {
            $next_opt = 'opt_unpending';
        }

        if ($next_opt == '' && $is_lock == 1) {
            if ($sys_user['user_code'] != $record['is_lock_person']) {
                if ($sys_user['is_manage'] == 1) {
                    $next_opt = 'opt_force_unlock';
                } else {
                    $next_opt = '';
                }
                return $this->format_ret(1, $next_opt);
            }
        }

        if ($next_opt == '' && $pay_status == 0 && $pay_type != 'cod') {
            $next_opt = 'opt_pay';
        }
        //echo '<hr/>pay_type<xmp>'.var_export($pay_type,true).'</xmp>';
        //echo '<hr/>pay_status<xmp>'.var_export($pay_status,true).'</xmp>';
        //echo '<hr/>order_status<xmp>'.var_export($order_status,true).'</xmp>';
        if ($next_opt == '' && (($pay_status == 2 && $pay_type != 'cod') || ($pay_status == 0 && $pay_type == 'cod')) && $order_status == 0) {
            $next_opt = 'opt_confirm';
        }
        if ($next_opt == '' && $order_status == 1 && $shipping_status == 0) {
            $next_opt = 'opt_notice_shipping';
        }
        return $this->format_ret(1, $next_opt);
    }

    function sys_user() {

        if (CTX()->is_in_cli()) {
            $user_code = load_model('sys/UserTaskModel')->get_user_code();
            if (!empty($user_code)) {
                $sql = "select user_id,user_code,user_name ,is_manage from sys_user where user_code=:user_code ";
                $sql_values = array(':user_code' => $user_code);
                $user = $this->db->get_row($sql, $sql_values);
            } else {
                $user = array(
                    'user_id' => -1,
                    'user_code' => 'sys_schedule',
                    'user_name' => '系统定时器',
                    'is_manage' => 1,
                );
            }
            return $user;
        } else {
            $role = ctx()->get_session('role');
            $role_list = $role['data'];
            $is_manage = 0;
            foreach ($role_list as $sub_row) {
                if ($sub_row['role_code'] == 'manage') {
                    $is_manage = 1;
                    break;
                }
            }
            return array(
                'user_id' => CTX()->get_session('user_id'),
                'user_code' => CTX()->get_session('user_code'),
                'user_name' => CTX()->get_session('user_name'),
                'is_manage' => $is_manage,
            );
        }
    }

    private function get_shop_day($shop_code) {
        static $shop_day = null;
        if (!isset($shop_day[$shop_code])) {
            $ret = load_model('base/ShopModel')->get_by_code($shop_code);
            $shop_day[$shop_code] = $ret['data']['days'];
        }

        return $shop_day[$shop_code];
    }

    /**
     * @todo 新增订单计算计划发货时间，没有明细，使用主单的下单时间计算
     */
    function get_plan_time_by_main_record($record) {

        $days = $this->get_shop_day($record['shop_code']);

        $time = ($record['pay_time'] != '0000-00-00 00:00:00' && $record['pay_time'] != '1970-01-01 08:00:00') ? $record['pay_time'] : $record['record_time'];
        $record['plan_send_time'] = date('Y-m-d H:i:s', strtotime($time) + $days * 24 * 60 * 60); //计算计划发货时间
        return $this->format_ret(1, $record);
    }

    //设置计划发货时间
    function set_sell_plan_send_time($sell_record_code, $up_mx = 1) {
        $record = $this->get_record_by_code($sell_record_code);
        if (empty($record)) {
            return $this->format_ret(-1, "{$sell_record_code} 订单不存在");
        }
        $detail = $this->get_detail_list_by_code($sell_record_code);
        //当$up_mx=0时，明细中没有计划发货时间，应用于手动新增订单中计算计划发货时间；当$up_mx!=0时走正常逻辑
        if ($up_mx != 0) {
            $ret = $this->js_sell_plan_send_time($record, $detail);
        } else {
            $ret = $this->get_plan_time_by_main_record($record);
        }
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->update_sell_plan_send_time($ret['data']);
        return $ret;
    }

    //更新计划发货时间
    function update_sell_plan_send_time($result) {
        $plan_send_time = !empty($result['plan_send_time']) ? $result['plan_send_time'] : time();
        $sale_mode = !empty($result['sale_mode']) ? $result['sale_mode'] : 'stock';
        $upd = array('plan_send_time' => $plan_send_time, 'sale_mode' => $sale_mode);
        $ret = M('oms_sell_record')->update($upd, array('sell_record_code' => $result['sell_record_code'])); //要同时维护预售状态
        if ($ret['status'] < 0) {
            return $ret;
        }
        if (isset($result['mx']) && !empty($result['mx'])) {
            foreach ($result['mx'] as $sub_mx) {
                //要同时维护预售状态
                $ret = M('oms_sell_record_detail')->update(array('plan_send_time' => $sub_mx['plan_send_time'], 'sale_mode' => $sub_mx['sale_mode']), array('sell_record_detail_id' => $sub_mx['sell_record_detail_id']));
                if ($ret['status'] < 0) {
                    return $ret;
                }
            }
        }
        return $this->format_ret(1);
    }

    /**
     * 根据预售计划计算计划发货时间
     * @param array $record 主单数据
     * @param array $detail 明细数据
     * @return array 处理后的数据
     */
    public function js_plan_send_time_by_presell($record, $detail) {
        if (empty($record)) {
            return $this->format_ret(-1, '', '找不到商店代码为' . $record['shop_code'] . '的订单记录');
        }
        if (!empty($detail)) {
            $skuid_arr = array_column($detail, 'sku_id');
            $ret = load_model('op/presell/PresellDetailModel')->get_presell_plan_send_time($record['shop_code'], $record['record_time'], $skuid_arr);

            $plan_arr = $ret['data'];
            $time_arr = array();
            foreach ($detail as &$sub_detail) {
                $k = $sub_detail['sku'] . ',' . $sub_detail['sku_id'];
                if (!empty($plan_arr[$k])) {
                    $sub_detail['sale_mode'] = 'presale';
                    $sub_detail['plan_send_time'] = date('Y-m-d H:i:s', $plan_arr[$k]['plan_send_time']);
                    $plan_arr[$k]['sell_num'] = $sub_detail['num'];
                } else {
                    $sub_detail['sale_mode'] = 'stock';
                    $sub_detail['plan_send_time'] = $this->get_plan_time($record, $sub_detail);
                }

                $time_arr[] = strtotime($sub_detail['plan_send_time']);
            }
            rsort($time_arr);
            $max_time = $time_arr[0];

            if (!empty($plan_arr)) {
                //更新预售商品销售数量
                $this->insert_multi_duplicate('op_presell_plan_detail', $plan_arr, 'sell_num=VALUES(sell_num)+sell_num');
            }
        }
        if (isset($max_time) && !empty($max_time)) {
            $record['plan_send_time'] = date('Y-m-d H:i:s', $max_time);
        } else {
            $ret = $this->get_plan_time_by_main_record($record);
            $record['plan_send_time'] = $ret['data']['plan_send_time'];
        }
        // 订单明细中只要有一个商品为预售商品，整个订单就是预售订单，主要针对订单合并、拆分、复制
        $record['sale_mode'] = !empty($detail) ? $this->get_sale_mode($detail) : 'stock';
        $record['mx'] = $detail;
        return $this->format_ret(1, $record);
    }

    //计算计划发货时间
    public function js_sell_plan_send_time($record, $detail) {
        if (empty($record)) {
            return $this->format_ret(-1, '', '找不到商店代码为' . $record['shop_code'] . '的订单记录');
        }
        if (!empty($detail)) {
            foreach ($detail as $k => &$sub_detail) {
                //判断是否需要识别预售
                if (!isset($sub_detail['plan_send_time']) || empty($sub_detail['plan_send_time']) || $sub_detail['plan_send_time'] == '0000-00-00 00:00:00' || strtotime($sub_detail['plan_send_time']) == 0) {
                    //判断是否为预售商品
                    $ret = $this->identity_sale_mode($sub_detail);
                    if (!empty($ret['plan_send_time']) || $ret == 'special') {
                        //预售状态
                        $sub_detail['sale_mode'] = 'presale';
                        if ($ret == 'special') {
                            //只有预售两个字或者没有写明预售日期，计划发货时间也为付款时间加上承诺发货天数
                            $sub_detail['plan_send_time'] = $this->get_plan_time($record, $sub_detail);
                        } else {
                            //计划发货时间
                            $sub_detail['plan_send_time'] = $ret['plan_send_time'];
                        }
                    } else {
                        $sub_detail['sale_mode'] = $ret['sale_mode'];
                        $sub_detail['plan_send_time'] = $this->get_plan_time($record, $sub_detail);
                    }
                }
                //获取最大的计划发货时间
                $time_arr[] = strtotime($sub_detail['plan_send_time']);
            }
            sort($time_arr);
            $max_time = $time_arr[count($time_arr) - 1];
        }
        if (isset($max_time) && !empty($max_time)) {
            $record['plan_send_time'] = date('Y-m-d H:i:s', $max_time);
        } else {
            $ret = $this->get_plan_time_by_main_record($record);
            $record['plan_send_time'] = $ret['data']['plan_send_time'];
        }
        // 订单明细中只要有一个商品为预售商品，整个订单就是预售订单，主要针对订单合并、拆分、复制
        $record['sale_mode'] = !empty($detail) ? $this->get_sale_mode($detail) : 'stock';
        $record['mx'] = $detail;
        return $this->format_ret(1, $record);
    }

    /**
     * @todo 获取计划发货天数(非预售或者未写明预售时间时使用)
     */
    function get_plan_time($record, $sub_detail) {


        $days = $this->get_shop_day($record['shop_code']);

        $time = (!empty($record['pay_time']) && $record['pay_time'] != '0000-00-00 00:00:00' && strtotime($record['pay_time']) != 0) ? $record['pay_time'] : $record['record_time'];
        $sub_detail['plan_send_time'] = date('Y-m-d H:i:s', strtotime($time) + $days * 24 * 60 * 60); //计算计划发货时间
        return $sub_detail['plan_send_time'];
    }

    /**
     * @todo 识别预售商品
     * @return 若为预售商品，返回预售时间；否知返回FALSE
     * 假设sku_properties为'颜色分类:黑色【预售】;尺寸:XS[十二月三十一日后发出]'
     * 则$flag=1;为预售商品,并获取到中括号中的字符串'十二月三十一日后发出';
     * 同时若预售月份小于下单月份,则判断为次年预售,当前年份加一
     */
    function identity_sale_mode($detail) {
        $matches = array();
        $detail['sku_properties'] = (isset($detail['sku_properties']) && !empty($detail['sku_properties'])) ? $detail['sku_properties'] : '';
        //匹配sku_properties中的中括号,判断客户是否定义为预售商品
        $flag = preg_match_all("/(?:\[)(.*)(?:\])/i", $detail['sku_properties'], $matches);
        //没有'预售'两个字就不是预售
        $pos = mb_strpos($detail['sku_properties'], '预售', 0, 'utf-8');
        if (!empty($pos)) {
            if ($flag == 1) {
                if ($matches[1][0] == '预售') {
                    return 'special';
                } else {
                    $ret = $this->get_time($matches[1][0]);
                    return array('sale_mode' => 'presale', 'plan_send_time' => $ret);
                }
            } else {
                return array('sale_mode' => 'presale', 'plan_send_time' => '');
            }
        } else {
            return array('sale_mode' => 'stock', 'plan_send_time' => '');
        }
    }

    /**
     * @todo 解析计划发货时间，支持阿拉伯数字和汉字数字
     */
    function get_time($str) {
        //过滤特殊字符(不包含小数点)
        $specialchars = "\"\s'\t\n\r\b\fs~!@#$%^&*()_+,?|`！@#￥%……&*（）";
        $chars = '/[' . $specialchars . ']/u';
        $strs = preg_replace($chars, '', $str);
        //匹配数字日期
        $pattern = '/[1-9]\d*/';
        $matches = array();
        preg_match_all($pattern, $strs, $matches);
        $count = count($matches[0]);
        if ($matches && $count > 1) {
            $month = $matches[0][$count - 2];
            $day = $matches[0][$count - 1];
            if (($month < 1 || $month > 12) || ($day < 1 || $day > 31)) {
                return FALSE;
            } else {
                $now_month = date('m');
                $year = ($month < $now_month) ? date('Y') + 1 : date('Y');
                $plan_send_time = $year . '-' . $month . '-' . $day . ' ' . '00:00:00';
                return $plan_send_time;
            }
        } else {
            $plan_send_time = $this->get_hz_num($str);
            return $plan_send_time;
        }
    }

    /**
     * @todo 处理汉字日期，如十二月十二日
     */
    function get_hz_num($str) {
        //过滤特殊字符(包含小数点)
        $specialchars = "\"\s'\t\n\r\b\fs~!@#$%^&*()_+,.?|`！￥……（）";
        $chars = '/[' . $specialchars . ']/u';
        $str = preg_replace($chars, '', $str);
        //汉字数字日期不含'月',不解析
        $pos = mb_strpos($str, '月', 0, 'utf-8');
        if (!$pos) {
            return FALSE;
        }
        $ints = array('一' => 1, '二' => 2, '三' => 3, '四' => 4, '五' => 5, '六' => 6, '七' => 7, '八' => 8, '九' => 9, '十' => 10);
        //'月'前后不含有汉字数字,不解析
        $m = mb_substr($str, $pos - 1, 1, 'utf-8');
        $d = mb_substr($str, $pos + 1, 1, 'utf-8');
        if (!$ints[$m] || !$ints[$d]) {
            return FALSE;
        }
        $mt_1 = mb_substr($str, $pos - 2, 1, 'utf-8');
        $m_1 = $this->get_arab_num($mt_1, 4);

        $mt_2 = mb_substr($str, $pos - 1, 1, 'utf-8');
        $m_2 = $this->get_arab_num($mt_2);

        $dt_1 = mb_substr($str, $pos + 1, 1, 'utf-8');
        $dt_2 = mb_substr($str, $pos + 2, 1, 'utf-8');
        $dt_3 = mb_substr($str, $pos + 3, 1, 'utf-8');

        $d_3 = $this->get_arab_num($dt_3);
        if (!empty($d_3)) {
            $d_2 = $this->get_arab_num($dt_2, 2);
        } else {
            $d_2 = $this->get_arab_num($dt_2, 3);
        }
        if (!empty($d_2)) {
            $d_1 = $this->get_arab_num($dt_1, 4);
        } else {
            $d_1 = $this->get_arab_num($dt_1);
        }
        $month = $m_1 . $m_2;
        $day = $d_1 . $d_2 . $d_3;
        $now_month = date('m');
        $year = ($month < $now_month) ? date('Y') + 1 : date('Y');
        $plan_send_time = $year . '-' . $month . '-' . $day . ' ' . '00:00:00';
        if (strtotime($plan_send_time)) {
            return $plan_send_time;
        } else {
            return FALSE;
        }
    }

    function get_arab_num($hz, $type = 1) {
        $ints = array('一' => 1, '二' => 2, '三' => 3, '四' => 4, '五' => 5, '六' => 6, '七' => 7, '八' => 8, '九' => 9, '十' => 10);
        //任意汉字数字
        if ($type == 1) {
            if ($ints[$hz]) {
                return $ints[$hz];
            } else {
                return '';
            }
        }
        //三位汉字数字,如'二十五',中间'十'返回空
        if ($type == 2) {
            if ($ints[$hz] && $hz != '十') {
                return $ints[$hz];
            } else {
                return '';
            }
        }
        //两个汉字数字,如'二十','十'返回'0'
        if ($ints[$hz] && $hz != '十') {
            return $ints[$hz];
        } else if ($hz == '十') {
            if ($type == 3) {
                return '0';
            } else if ($type == 4) {
                return '1';
            }
        } else {
            return '';
        }
    }

    /**
     * @todo 获取订单的销售状态 stock现货销售 presale预售
     * @remark 订单明细中只要有一个商品为预售商品，整个订单就是预售订单
     */
    function get_sale_mode($detail) {
        foreach ($detail as $value) {
            if ($value['sale_mode'] == 'stock') {
                continue;
            } else {
                return 'presale';
            }
        }
        return 'stock';
    }

    function opt_create_return_check($record, $detail, $sysuser) {
        /* if (false == get_operate_purview("order/sell_record/edit_base_info")) {
          return $this->return_value(-1, '无订单编辑基本信息权限');
         */
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_create_return')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] != 1 && $record['shipping_status'] < 4) {
            return $this->return_value(-1, '非锁定订单不能操作');
        }
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->return_value(-1, '已锁定订单不能操作');
        }
        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }

        if ($record['pay_status'] == 0 && $record['pay_type'] != 'cod') {
            return $this->return_value(-1, '只有已付款的订单才能操作');
        }
        if ($record['order_status'] == 3) {
            return $this->return_value(-1, '只有未作废的订单才能操作');
        }

        if ($record['is_problem'] > 0) {
            return $this->return_value(-1, '只有非问题单的订单才能操作');
        }
        if ($record['shipping_status'] < 4) {
            return $this->return_value(-1, '只有已发货的订单才能操作');
        }
        return $this->return_value(1, '');
    }

    /**
     * 强制解锁检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_force_unlock_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_force_unlock')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if ($record['is_lock'] != 1) {
            return $this->return_value(-1, '非锁定订单不能操作');
        }
        return $this->return_value(1, '');
    }

    /**
     * 订单强制解锁
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_force_unlock($sellRecordCode, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_force_unlock_check($record, $detail, $sys_user);
        if ($check['status'] != '1') {
            return $check;
        }

        $this->begin_trans();
        try {
            $data = array('is_lock' => 0, 'is_lock_person' => '', 'is_lock_time' => '');
            $ret = $this->update($data, array('sell_record_code' => $record['sell_record_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '强制解锁订单出错');
            }
            $this->add_action($record['sell_record_code'], '强制解锁');
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function opt_intercept_check($record, $detail, $sysuser) {
        //#############权限
        $sysuser['is_api'] = isset($sysuser['is_api']) ? $sysuser['is_api'] : 0;
        if ($sysuser['is_api'] != 1) {
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_intercept')) {
                return $this->format_ret(-1, '', "无权访问");
            }
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', "没有找到匹配的订单");
        }
        /*
          if ($record['is_lock'] != 1) {
          return $this->format_ret(-1,'', '非锁定订单不能操作');
         */
        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person'] && $sysuser['is_api'] != 1) {
            return $this->format_ret(-1, '', '已锁定订单不能操作');
        }
        if ($record['shipping_status'] >= 4) {
            return $this->format_ret(-1, '', '已发货订单不能操作');
        }
        if ($record['order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废订单不能操作');
        }
        return $this->return_value(1, '');
    }

    function biz_intercept($record, $is_refund, $msg, $is_force = 0) {
        $this->begin_trans();
        try {
            //如果已生成波次，先取消波次单，已通知配货的订单拦截后要改为未确认未通知配货并自动锁定，如果因为退单，那要设问
            if (!empty($record['waves_record_id'])) {
                $ret = load_model('oms/WavesRecordModel')->cancel_waves_sell_record($record['waves_record_id'], $record['sell_record_code'], '拦截订单前先取消波次单', 0);
                if ($ret['status'] < 0 && $ret['status'] != -10) {
                    $this->rollback();
                    return $ret;
                }
                $ret = $this->update_waves_record_deliver_status($record['waves_record_id']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
            }else {//未生成波次，但是已存在云栈热敏,调用取消接口
                $row = $this->get_record_by_code($record['sell_record_code']);
                load_model('oms/DeliverLogisticsModel')->cancel_waybill($row);
            }
            //如果是退单，要设问
            //部分退
            if ($is_refund == 2) {
                $tag_req['desc'] = $msg;
                $ret = $this->opt_problem_get_tag($record['sell_record_code'], 'REFUND', $tag_req);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                $ins_oms_sell_record_tag = $ret['data']['tag_data'];
                $log = $ret['data']['log'];
                $ret = M('oms_sell_record_tag')->insert_dup($ins_oms_sell_record_tag);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                if (!empty($msg) && $msg != '发现存在退单，拦截') {
                    $msg = "退款说明：" . $msg .'。'. $log;
                }else{
                    $msg .= $log;
                }
            }
            //整单退
            if ($is_refund == 1) {
                $tag_req['desc'] = $msg;
                $ret = $this->opt_problem_get_tag($record['sell_record_code'], 'FULL_REFUND', $tag_req);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                $ins_oms_sell_record_tag = $ret['data']['tag_data'];
                $log = $ret['data']['log'];
                $ret = M('oms_sell_record_tag')->insert_dup($ins_oms_sell_record_tag);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                if(!empty($msg) && $msg != '发现存在退单，拦截'){
                    $msg = "退款说明：" . $msg .'。'. $log;
                }else{
                    $msg .= $log;
                }
            }

            $data = array('order_status' => 0, 'express_no' => '', 'shipping_status' => 0, 'waves_record_id' => 0, 'is_notice_time' => '0000-00-00 00:00:00', 'check_time' => '0000-00-00 00:00:00', 'express_data' => '','wms_request_time' => 0);
            $where = "  order_status<>3 and shipping_status<4 and sell_record_code = '{$record['sell_record_code']}'";

            $ret = $this->db->update('oms_sell_record', $data, $where);
            if ($ret != true) {
                $this->rollback();
                return $this->format_ret(-1, '', '拦截更新订单状态失败');
            }
            $aff_row = ctx()->db->affected_rows();
            //如果本来就是未确认的订单，affected_rows 就是等于0
            if ($aff_row == 0 && ($record['order_status'] != 0 || $record['shipping_status'] != 0)) {
                $this->rollback();
                return $this->format_ret(-1, '', '拦截更新订单状态失败,未刷新数据');
            }

            $sys_param = load_model('sys/SysParamsModel')->get_val_by_code(['fx_finance_account_manage','api_cancel']);
            //判断分销商是否是淘宝分销商
            $custom_data = load_model('base/CustomModel')->get_by_code($record['fenxiao_code']);
            $custom_data = empty($custom_data['data']) ? '' : $custom_data['data'];
            //开启资金账户并且已结算（不包含淘宝、淘分销）,取消结算，并生成扣款单
            if ($sys_param['fx_finance_account_manage'] == 1 && $record['is_fenxiao'] == 2 && $record['is_fx_settlement'] == 1) {
                //取消结算并生成扣款单
                $ret = $this->opt_unsettlement($record['sell_record_code'], array(0 => 'intercept'));
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $this->format_ret(-1, '', $ret['message']);
                }
            }
            if ($is_refund != 0) {
                $data['is_problem'] = 1;
                $where = "  order_status<>3 and shipping_status<4 and sell_record_code = '{$record['sell_record_code']}'";
                $ret = $this->db->update('oms_sell_record', $data, $where);
            }

            $ret = load_model('wms/WmsEntryModel')->cancel($record['sell_record_code'], 'sell_record', $record['store_code'], array('act' => 'unnotice_shipping'), $is_force);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            } else {
                $wms_op_msg = '';
                if ($ret['status'] == 10) {
                    $wms_op_msg = $ret['message'];
                }
            }
            //门店发货
            $ret = load_model('o2o/O2oEntryModel')->cancel($record['sell_record_code'], 'sell_record', $record['store_code'], array('act' => 'unnotice_shipping'), $is_force);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            } else {
                $wms_op_msg = '';
                if ($ret['status'] == 10) {
                    $wms_op_msg = $ret['message'];
                }
            }

            $ret = load_model('mid/MidBaseModel')->cancel_mid_record($record['sell_record_code'], 'sell_record', $record['store_code'], $record['shop_code']);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            $api_msg = '';
            if ($record['shipping_status'] > 0 && $sys_param['api_cancel'] == 1) {
                $ret = load_model('api/common/ApiRouteModel')->api_cancel(['record_code' => $record['sell_record_code'], 'deal_code' => $record['deal_code'], 'archives_code' => $record['store_code'], 'archives_type' => 2]);
                if ($ret['status'] <= 1) {
                    $api_msg = $ret['message'];
                }
            }

            //删除通知配货数据
            load_model('oms/SellRecordNoticeModel')->delete_record_notice(array($record['sell_record_code']));

            $msg = ($is_force == 1) ? '强制取消wms单据' : $msg . ' ' . $wms_op_msg;
            $this->add_action($record['sell_record_code'], '拦截', $msg . $api_msg);

            $this->commit();
            return $this->format_ret(1, '', $wms_op_msg);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    //退单拦截，拦截SKU,增加SKU备注信息
    function intercept_refund_sku($sell_record_code, $refund_data, $type = 'api') {

        if ($type == 'api') {
            $refund_data = $this->change_api_refund_detail($refund_data);
            if (empty($refund_data)) {
                return $this->format_ret(-1, '', '转换数据失败');
            }
        }

        $detail_data = $this->get_detail_list_by_code($sell_record_code);
        //var_dump($detail_data);die;
        $sell_record_detail_barcode = array();
        foreach ($detail_data as $val) {
            if($type == 'refund') { //合并订单生成退单情况
                $key = $val['deal_code'] . '-' . $val['sku'];
            } else {
                $key = $val['sku'];                
            }

            if (isset($refund_data[$key])) {
                $refund_barcode_info = &$refund_data[$key];

                if ($refund_barcode_info['deal_code'] == $val['deal_code']) {
//交易号匹配
                    $this->update_intercept_refund_sku_info($val, $refund_barcode_info, $type);
                    unset($refund_data[$key]);
                } else {
                    $sell_record_detail_barcode[$key] = $val;
                }
            }
        }

        //部分没找打 商品
        if (!empty($refund_data)) {
            foreach ($sell_record_detail_barcode as $key => $val) {
                $refund_barcode_info = &$refund_data[$key];
                $this->update_intercept_refund_sku_info($val, $refund_barcode_info, $type);
                unset($refund_data[$key]);
            }
        }
        return $this->format_ret(1);
    }

    private function change_api_refund_detail(&$refund_detail) {
        $refund_data = array();
        foreach ($refund_detail as $detail_info) {
            if (!isset($detail_info['deal_code']) && isset($detail_info['tid'])) {
                $detail_info['deal_code'] = $detail_info['tid'];
            }
            if (!isset($detail_info['sku']) && isset($detail_info['goods_barcode'])) {
                $detail_info['sku'] = $this->db->get_value("select sku from goods_sku where barcode='{$detail_info['goods_barcode']}'");
            }

            if (isset($detail_info['sku']) && !empty($detail_info['sku'])) {
                $refund_data[$detail_info['sku']] = $detail_info;
            }
        }
        return $refund_data;
    }

    private function update_intercept_refund_sku_info($sell_record_detail, $refund_barcode_info, $type = 'api') {
        if (!empty($sell_record_detail['api_refund_desc'])) {
            $sell_record_detail['api_refund_desc'] .= "<br />";
        }
        if ($type == 'api') {
            //需要核查 refund_id
            $refund_barcode_info['refund_id'] = isset($refund_barcode_info['refund_id']) ? $refund_barcode_info['refund_id'] : '';
            $api_refund_desc = "订单商品数量:" . $sell_record_detail['num'] . "," . "接口退单" . $refund_barcode_info['refund_id'] . "退货：" . $refund_barcode_info['num'];
        } else {
            $api_refund_desc = "订单商品数量:" . $sell_record_detail['num'] . "," . "退单" . $refund_barcode_info['sell_return_code'] . "退货：" . $refund_barcode_info['num'];
        }
        $up_data['api_refund_desc'] = $sell_record_detail['api_refund_desc'] . $api_refund_desc;
        $up_data['api_refund_num'] = $refund_barcode_info['num'] + $sell_record_detail['api_refund_num'];


        return $this->db->update('oms_sell_record_detail', $up_data, array('sell_record_detail_id' => $sell_record_detail['sell_record_detail_id']));
    }

    //拦截
    function opt_intercept($sellRecordCode, $is_refund = 0, $msg = '', $is_force = 0) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_intercept_check($record, $detail, $sys_user);
        $ret = $this->biz_intercept($record, $is_refund, $msg, $is_force);
        return $ret;
    }

    //根据明细，更新订单主单信息（主要是价格）
    function update_record_price($result) {
        $upd_fld_arr = explode(',', "payable_money,goods_money,goods_num,sku_num,goods_weigh,fx_payable_money");
        $upd = array();
        foreach ($upd_fld_arr as $fld) {
            $upd[$fld] = $result[$fld];
        }
        $ret = M('oms_sell_record')->update($upd, array('sell_record_code' => $result['sell_record_code']));
        return $ret;
    }

    //更新订单理论重量
    function update_goods_weigh($sell_record_code, $new_detail, $old_goods_weigh) {
        $goods_weigh = (load_model('op/PolicyExpressModel')->get_record_detail_goods_weigh($new_detail)) * 0.001;
        $goods_weigh = number_format($goods_weigh, 3, ".", "");
        if ($goods_weigh != $old_goods_weigh) {
            $where = "sell_record_code='{$sell_record_code}'";
            $upd_arr['goods_weigh'] = $goods_weigh;
            $status = $this->db->update('oms_sell_record', $upd_arr, $where);
            $num = $this->affected_rows();
            if ($status === false || $num != 1) {
                return $this->format_ret('-1', '', '更新失败');
            } else {
                return $this->format_ret(1);
            }
        }
        return $this->format_ret(1);
    }

    /**
     * 重新计算订单价格
     */
    public function js_record_price($record, $record_detail, $js_deal_code = 0, $type = '') {
        //订单应付款
        $record['payable_money'] = 0;
        //商品总额
        $record['goods_money'] = 0;
        //商品总数量
        $record['goods_num'] = 0;
        //商品SKU数量
        $record['sku_num'] = 0;
        //商品重量
        //$record['goods_weigh'] = 0;
        //获取分销商店
        $sql = "SELECT entity_type,custom_code,sale_channel_code FROM base_shop WHERE shop_code = '{$record['shop_code']}'";
        $shop_data = $this->db->get_row($sql);

        $sku_num_arr = array();
        $avg_money = 0;
        $fx_payable_money = 0;
        $deal_code_arr = array();
        foreach ($record_detail as $sub_detail) {
            if (isset($sub_detail['is_delete']) && $sub_detail['is_delete'] == 1) {
                continue;
            }
            $sku_num_arr[$sub_detail['sku']] = 1;
            $avg_money += $sub_detail['avg_money'];
            if (isset($sub_detail['fx_amount']) && !empty($sub_detail['fx_amount'])) {
                $fx_payable_money += $sub_detail['fx_amount'];
            }
            if (isset($sub_detail['goods_price'])) {
                $record['goods_money'] += $sub_detail['goods_price'] * $sub_detail['num'];
            }
//			if (isset($sub_detail['goods_weigh'])) {
//				$record['goods_weigh'] += $sub_detail['goods_weigh'] * $sub_detail['num'];
//			}

            $record['goods_num'] += $sub_detail['num'];
            $deal_code_arr[$sub_detail['deal_code']] = $sub_detail['deal_code'];
        }
        $record['payable_money'] = $avg_money + $record['express_money'] + $record['delivery_money'];
        $record['sku_num'] = count($sku_num_arr);
        $record['avg_money'] = $avg_money;
        $record['fx_payable_money'] = $fx_payable_money;

        //不是淘分销订单，但是是分销店铺订单，设置为分销订单 // && empty($record['fenxiao_name']) && empty($record['is_fenxiao'])
        if ($shop_data['entity_type'] == 2 && $type != 'import_fx') {
            $sql = "SELECT * FROM base_custom WHERE custom_code = '{$shop_data['custom_code']}'";
            $custom_data = $this->db->get_row($sql);
            $record['is_fenxiao'] = 2;
            $record['fenxiao_name'] = $custom_data['custom_name'];
            $record['fenxiao_code'] = $custom_data['custom_code'];
            $record['fx_payable_money'] = $fx_payable_money;
        }

        if (!empty($deal_code_arr)) {
            $record['deal_code_list'] = join(',', $deal_code_arr);
        }
        if ($js_deal_code == 1) {
            $record['deal_code'] = $this->get_guid_deal_code($record['deal_code_list']);
            $record['deal_code'] = (strlen($record['deal_code']) > 200) ? md5($record['deal_code']) : $record['deal_code'];
        }
        return $this->format_ret(1, $record);
    }

    /*
     * 订单明细增删改后，(如果应付>已付的非COD单子，要返回释放库存，返回未付款状态)
     * 先释放库存，再占用库存
     * 刷新主单价格
     * 更新计划发货时间
     * $record 主单 $old_detail 旧明细 $new_detail 新明细 $sell_record_code 如果不给出新旧明细，要给出$sell_record_code
     */

    function edit_detail_after_flush_data($record, $old_detail, $new_detail, $sell_record_code, $is_skip_lock = 0) {
        if (empty($record)) {
            $record = $this->get_record_by_code($sell_record_code);
        }
        //新的单据明细
        if (empty($new_detail)) {
            $new_detail = $this->get_detail_list_by_code($sell_record_code);
        }
        //计划发货时间
        $ret = $this->js_sell_plan_send_time($record, $new_detail);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->update_sell_plan_send_time($ret['data']);
        if ($ret['status'] < 0) {
            return $ret;
        }
        //主单价格
        $ret = $this->js_record_price($record, $new_detail);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $payable_money = $ret['data']['payable_money'];
        $ret = $this->update_record_price($ret['data']);
        if ($ret['status'] < 0) {
            return $ret;
        }
        //更新订单理论重量
        $ret = $this->update_goods_weigh($sell_record_code, $new_detail, $record['goods_weigh']);
        if ($ret['status'] < 0) {
            return $ret;
        }

        //释放库存
        if (!empty($old_detail) && $record['must_occupy_inv'] == 1) {
            $ret = $this->lock_detail($record, $old_detail, 0);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }
        //如果应付>已付的非COD单子，要返回释放库存，返回未付款状态
        if ($record['pay_type'] != 'cod' && bccomp(floatval($payable_money), floatval($record['paid_money']), 2) > 0 && $record['order_status'] != 3 && $record['shipping_status'] == 0) {
            $upd = array('must_occupy_inv' => 0, 'lock_inv_status' => 0, 'pay_status' => 0, 'pay_time' => '0000-00-00 00:00:00');
            $ret = M('oms_sell_record')->update($upd, array('sell_record_code' => $record['sell_record_code']));
            if ($ret['status'] < 0) {
                return $ret;
            }
//			if ($record['order_status'] == 0 && $record['pay_status'] == 2) {
//				$problem_remark = '修改商品明细，导致已付款小于应付款，自动设问换货单';
//				$ret = $this->set_problem_order('CHANGE_GOODS_MAKEUP', $problem_remark, $sell_record_code, $is_skip_lock);
//				if ($ret['status'] < 0) {
//					return $ret;
//				}
//			}
            $ret_status = 2;
        } else {
            //重新占用库存
            if (!empty($new_detail) && $record['must_occupy_inv'] == 1) {
                $record['lock_inv_status'] = 0;
                foreach ($new_detail as $k => $sub_detail) {
                    $new_detail[$k]['lock_num'] = 0;
                }

                $ret = $this->lock_detail($record, $new_detail, 1);
                if ($ret['status'] < 0) {
                    return $ret;
                }
            }
            $ret_status = 1;
        }
        /*if(isset($record['is_replenish']) && $record['is_replenish'] == 1){
            //对于补单的数据，金额和价格重置
            $record_ret = $this->reset_record($record['sell_record_code']);
            if($record_ret === false){
                $this->rollback();
                return $record_ret;
            }
        }*/
        //die;
        return $this->format_ret($ret_status, $ret['data'], $ret['message']);
    }

    function sell_record_code_href($sell_record_code) {
        $url = "?app_act=oms/sell_record/view&sell_record_code={$sell_record_code}";
        $_url = base64_encode($url);
        $u = "javascript:openPage('{$_url}', '{$url}', '订单详情')";
        return "<a onclick=\"$u\">" . $sell_record_code . "</a>";
    }

    /**
     * 根据单据号和单据类型取【批次库存表】的相关信息
     * record_type 1订单 2退货单 3换货单
     */
    function get_lof_info_by_record_code($record_code, $record_type, $map_key = 'sku', $vir_fld = '') {
        $fld = 'id,deal_code,goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,num,occupy_type';
        //根据批次号生产日期排序
        $sql = "select {$fld} from oms_sell_record_lof where record_code = :record_code and record_type = :record_type order by lof_no,production_date";
        $data = ctx()->db->get_all($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
        $result = array();
        if (empty($map_key)) {
            $result = &$data;
        } else {
            $map_key_arr = explode(',', $map_key);
            foreach ($data as $sub_data) {
                $ks = '';
                foreach ($map_key_arr as $_k) {
                    $ks .= $sub_data[$_k] . ',';
                }
                if (!empty($vir_fld)) {
                    $sub_data[$vir_fld] = 0;
                }
                $result[substr($ks, 0, -1)][] = $sub_data;
            }
        }
        return $result;
    }

    /**
     * 未确认的订单 设为急单，自动确认，自动通知配货，如果是wms的，自动上传wms
     */
    function set_rush($record_code) {
        $ret = $this->auto_confirm_and_notice($record_code,array(),1);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $result_msg = $ret['data'];

        $sql = "select store_code,is_rush from oms_sell_record where sell_record_code = :sell_record_code";
        $db_sell = ctx()->db->get_row($sql, array(':sell_record_code' => $record_code));
        if ($db_sell['is_rush'] == 0) {
            $sql = "update oms_sell_record set is_rush = 1 where sell_record_code = :sell_record_code";
            ctx()->db->query($sql, array(':sell_record_code' => $record_code));
            //修改通知配货单的加急状态
            $sql = "update oms_sell_record_notice set is_rush = 1 where sell_record_code = :sell_record_code";
            ctx()->db->query($sql, array(':sell_record_code' => $record_code));
        }
        $r = $this->add_action($record_code, '设为急单');
        $ret = load_model('wms/WmsEntryModel')->upload($record_code, 'sell_record', $db_sell['store_code']);
        if ($ret['status'] < 0) {
            $ret['message'] = join(',', $result_msg) . ',' . $ret['message'];
            return $ret;
        } else {
            $result_msg[] = '上传wms成功';
        }

        return $this->format_ret(1);
    }

    //自动确认 自动通知配货
    function auto_confirm_and_notice($record_code, $db_sell = array(),$is_rush=0) {
        //    $record_lastchanged = strtotime($record['lastchanged'])+7200;
        if (empty($db_sell)) {
            $sql = "select order_status,shipping_status,store_code,lastchanged,is_lock,shop_code from oms_sell_record where sell_record_code = :sell_record_code";
            $db_sell = ctx()->db->get_row($sql, array(':sell_record_code' => $record_code));
            $result_msg = array();
        }

        if ($db_sell['order_status'] == 0) {
            $ret = $this->opt_confirm($record_code, array(), 1);
            if ($ret['status'] < 0) {
                return $ret;
            } else {
                $result_msg[] = '确认订单成功';
                //if ($ret['data']['is_auto_notice'] == 1) {
                //	$result_msg[] = '通知配货成功';
                //}
            }
        }
 		if ($db_sell['shipping_status'] == 0 && $is_rush==1){
         			$ret = $this->opt_notice_shipping($record_code,array(),1);
         			if ($ret['status']<0){
         				$ret['message']  = join(',',$result_msg).','.$ret['message'];
         				return $ret;
         			}else{
         				$result_msg[] = '通知配货成功';
         			}
         		}

        return $this->format_ret(1, $result_msg);
    }

    //当生成换货单，订单详情修改商品明细，金额 导致已付金额小于应付金额 置为问题单
    public function set_problem_order($problem_code, $problem_remark, $sell_record_code, $is_skip_lock = 0) {
        $is_active = load_model('base/QuestionLabelModel')->get_is_active_value($problem_code);
        $ret = array('status' => 1);
        if ($is_active == 1) {
            $problem_params = array();
            $problem_params['sell_record_code'] = $sell_record_code;
            $problem_params['problem_code'] = $problem_code;
            $problem_params['problem_remark'] = $problem_remark;
            $problem_params['type'] = $problem_code;
            $ret = $this->opt_problem($sell_record_code, $problem_code, $problem_params, $is_skip_lock);
        }
        return $ret;
    }

//波次单取消时 进行订单拦截并设问
    public function deliver_cancel_and_set_problem($sell_record_code, $problem_code = 'STOREHOUSE_CANCEL') {
        $record = $this->get_record_by_code($sell_record_code);
        $this->begin_trans();
        try {
            $ret = $this->opt_intercept($sell_record_code); //先进行订单拦截操作
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
            $is_lock = 0;
            if ($record['is_lock'] == 0) {
                $is_lock = 1;
                $ret_2 = $this->opt_lock($sell_record_code); //锁定
                if ($ret_2['status'] < 1) {
                    $this->rollback();
                    return $ret_2;
                }
            }

            $is_active = load_model('base/QuestionLabelModel')->get_is_active_value('STOREHOUSE_CANCEL');
            if ($is_active == 1) {
                $ret = $this->opt_problem($sell_record_code, $problem_code, array('problem_remark' => '波次单取消自动设问'));
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
            }

            if ($is_lock == 1) {
                $ret_1 = $this->opt_unlock($sell_record_code); //解锁
                if ($ret_1['status'] < 1) {
                    return $ret_1;
                }
            }

            $this->add_action($sell_record_code, "波次单取消", '');
            $this->commit();
            return array('status' => '1', 'data' => '', 'message' => '取消成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    function set_tb_log($sell_record_code) {

        $is_strong_safe = CTX()->get_app_conf('is_strong_safe');

        if (!$is_strong_safe) {
            $record_data = $this->get_record_by_code($sell_record_code);
            $is_strong_safe = CTX()->get_app_conf('is_strong_safe');
            //御城河日志
            $trade_data = array($record_data);
            load_model('common/TBlLogModel')->set_log_multi($trade_data, 'edit');
        }
    }
    public function opt_settlement_new($sellRecordCode, $request = array()){
        $ret = $this->opt_settlement($sellRecordCode, $request);
        if($ret['status'] == 1){
            $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('fx_auto_confirm'));
            if($cfg['fx_auto_confirm']){
                $this->opt_confirm($sellRecordCode, $request);
            }
        }
        return $ret;
    }

    public function opt_settlement($sellRecordCode, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $check = $this->opt_settlement_check($record, $detail, $request);
        if ($check['status'] != '1') {
            return $check;
        }
        //0-未占用 2-部分缺货 3-完全缺货
        if (in_array($record['lock_inv_status'], array(0, 2, 3)) && $record['is_fenxiao'] == 2) {
            return $this->return_value('-1', '此订单是缺货单，无法结算');
        }
        $this->begin_trans();
        try {
            //分销订单包含非分销商品，如果开启了禁止结算则禁止结算，否则如果同意结算则结算
            $type = isset($request['allow_out']) ? $request['allow_out'] : 0;
            $out_ret = load_model('oms/SellRecordModel')->is_out_goods($sellRecordCode,$type);
            if($out_ret['status'] < 0){
                $this->db->rollback();
                return $this->format_ret(-2,'',$out_ret['message']);
            }
            $ret = $this->is_fx_finance_account_manage($record, 'record_settlement'); //生成资金流水
            if ($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1, '', $ret['message']);
            }

            $arr = array('is_fx_settlement' => 1,'settlement_time'=>date('Y-m-d H:i:s',time()));
            $ret = $this->db->update('oms_sell_record', $arr, array('sell_record_code' => $record['sell_record_code']));
            if ($ret !== true) {
                $this->rollback();
                return $this->format_ret(-1, '', '分销结算操作失败');
            }
            $action_name = $request['action_name'] == '批量结算' ? '批量结算' : '分销结算';
            $this->add_action($record['sell_record_code'], $action_name);
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function is_fx_finance_account_manage($record, $type) {
        //是否开启资金账户
        $fx_finance_account_manage = load_model('sys/SysParamsModel')->get_val_by_code('fx_finance_account_manage');
        //判断分销商是否是淘宝分销商
        $data = load_model('base/CustomModel')->get_by_code($record['fenxiao_code']);
        $custom_data = empty($data['data']) ? '' : $data['data'];
        //开启资金账户生成资金流水(不是淘分销订单)
        if ($fx_finance_account_manage['fx_finance_account_manage'] == 1 && $record['is_fenxiao'] == 2) {
//            if($type == 'return_finance_confirm') {
//                //金额
//                $change_ysje = number_format(($record['change_express_money'] + $record['change_avg_money']), 3, '.', '');
//                $ytk1 = bcadd($record['return_avg_money'], $record['seller_express_money'], 3);
//                $ytk2 = bcadd($record['compensate_money'], $record['adjust_money'], 3);
//                $ytk = bcadd($ytk1, $ytk2, 3);
//                $money = bcadd($ytk, -$change_ysje, 3);
//                $account_data = array(
//                    'money' => $money,
//                    'record_code' => $record['sell_return_code'],
//                    'custom_code' => $record['fenxiao_code'],
//                    'type' => $type
//                );
//            } else {
            //金额
            $money = $record['fx_payable_money'] + $record['fx_express_money'];
            $account_data = array(
                'money' => $money,
                'record_code' => $record['sell_record_code'],
                'custom_code' => $record['fenxiao_code'],
                'type' => $type
            );
//            }
            $ret = load_model('fx/BalanceOfPaymentsModel')->create_fx_income_pay($account_data);
            return $ret;
        } else {
            return $this->format_ret(1);
        }
    }

    function opt_settlement_check($record, $detail, $request) {
        //#############权限
        $request['type'] = isset($request['type']) ? $request['type'] : '';
        if ($request['type'] != 'auto_trans' && $request['batch'] !='批量操作') {
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_settlement')) {
                return $this->return_value(-1, "无权访问");
            }
        }

        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if (empty($detail)) {
            return $this->return_value(-1, '订单无明细');
        }
        if ($record['is_fenxiao'] == 0) {
            return $this->return_value(-1, '非分销订单不能操作');
        }
//        if ($record['fenxiao_power'] == 1) {
//            return $this->return_value(-1, '有货权的分销订单不走分销结算');
//        }
        if ($record['is_fx_settlement'] == 1) {
            return $this->return_value(-1, '已结算订单不能操作');
        }

        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_problem'] == 1 && $record['is_fenxiao'] != 1) {
            return $this->return_value(-1, '问题订单不能操作');
        }
        if ($record['pay_type'] != 'cod' && $record['pay_status'] != 2) {
            return $this->return_value(-1, '未付款订单不能操作');
        }
        if ($record['order_status'] == 3) {
            return $this->return_value(-1, '已作废订单不能操作');
        }
        if ($record['order_status'] >= 1) {
            return $this->return_value(-1, '已确认订单不能操作');
        }

        return $this->return_value(1, '');
    }

    public function opt_unsettlement($sellRecordCode, $request = array()) {
        $record = $this->get_record_by_code($sellRecordCode);
        $detail = $this->get_detail_list_by_code($sellRecordCode);
        $check = $this->opt_unsettlement_check($record, $detail);
        if ($check['status'] != '1') {
            return $check;
        }
        $this->begin_trans();
        try {
            /* if ($record['is_fenxiao'] == 1 && $record['is_handwork'] == 1) {
              $sql = "select * from fx_settlement where relation_code = :relation_code";
              $fx_settlement = $this->db->get_row($sql, array(":relation_code" => $sellRecordCode));
              if (empty($fx_settlement)) {
              return $this->format_ret(-1, '', '不存在分销结算单，无法取消结算');
              }
              if ($record['status'] == 1) {
              return $this->format_ret(-1, '', '分销结算单已结算，无法取消结算');
              }
              $delete_ret = load_model('fx/AccountSettlementModel')->delete($sellRecordCode);
              if ($delete_ret['status'] < 0) {
              $this->rollback();
              return $this->format_ret(-1, '', '删除结算单据信息失败，无法取消结算单');
              }
              $fx_moeny_detail_insert = load_model('fx/PayMoneyDetailModel')->fx_money_detail_insert($record, 'un_sales_settlement', '订单取消结算，预扣款取消');
              if ($fx_moeny_detail_insert['status'] < 0) {
              $this->rollback();
              return $this->format_ret(-1, '', '分销结算，更改冻结金额失败！');
              }
              } */
            if (!empty($request) && $request[0] == 'intercept') {
                $type = 'intercept';
            } else if (!empty($request) && $request[0] == 'combine') {
                $type = 'combine';
            } else {
                $type = 'record_unsettlement';
            }
            $ret = $this->is_fx_finance_account_manage($record, $type); //生成资金流水
            if ($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1, '', '取消结算，生成资金流水失败！');
            }

            $arr = array('is_fx_settlement' => 0,'settlement_time'=>'0000-00-00 00:00:00');
            $ret = $this->db->update('oms_sell_record', $arr, array('sell_record_code' => $record['sell_record_code']));
            if ($ret !== true) {
                return $this->format_ret(-1, '', '分销订单取消结算操作失败');
            }
            $this->add_action($record['sell_record_code'], '取消结算');
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function opt_unsettlement_check($record, $detail) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_unsettlement')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->return_value(-1, "没有找到匹配的订单");
        }
        if (empty($detail)) {
            return $this->return_value(-1, '订单无明细');
        }
        if ($record['is_fenxiao'] == 0) {
            return $this->return_value(-1, '非分销订单不能操作');
        }
//        if ($record['fenxiao_power'] == 1) {
//            return $this->return_value(-1, '有货权的分销订单不走分销结算');
//        }
        if ($record['is_fx_settlement'] == 0) {
            return $this->return_value(-1, '未结算订单不能操作');
        }

        if ($record['is_pending'] == 1) {
            return $this->return_value(-1, '挂起订单不能操作');
        }
        if ($record['is_problem'] == 1) {
            return $this->return_value(-1, '问题订单不能操作');
        }
        if ($record['pay_type'] != 'cod' && $record['pay_status'] != 2) {
            return $this->return_value(-1, '未付款订单不能操作');
        }
        if ($record['order_status'] == 3) {
            return $this->return_value(-1, '已作废订单不能操作');
        }
        if ($record['order_status'] >= 1) {
            return $this->return_value(-1, '已确认订单不能操作');
        }
        return $this->return_value(1, '');
    }

    function check_history_fx() {
        $sql = "select sell_record_code from oms_sell_record where is_fx_settlement = 0 and pay_status = 2 and shipping_status != 4 and ((is_fenxiao = 1 OR is_fenxiao = 2) || fenxiao_name != '')";
        $record_arr = $this->db->get_all($sql);
        $err_code_arr = array();
        foreach ($record_arr as $record) {
            $err = array();
            $ret = $this->opt_settlement($record['sell_record_code']);
            if ($ret['status'] < 0) {
                $err['sell_record_code'] = $record['sell_record_code'];
                $err['message'] = $ret;
                $err_code_arr[] = $err;
            }
        }
        return $err_code_arr;
    }

    /**
     * 更新淘宝卖家旗帜
     * @return array 操作结果
     */
    private function update_seller_flag() {
        $sql = "SELECT tid, seller_flag FROM api_order 
                WHERE source='taobao' AND seller_flag<>0 AND status=1 AND is_change=1 AND lastchanged>=:last_time ";
        $last_time = date('Y-m-d H:i:s', strtotime('-30 minutes'));
        $filter_data = $this->db->get_all($sql, array(":last_time" => $last_time));
        if (empty($filter_data)) {
            return $this->format_ret(1, '', '未找到符合条件的平台订单');
        }
        $data = array_column($filter_data, 'seller_flag', 'tid');
        $deal_code_arr = array_column($filter_data, 'tid');
        unset($filter_data);

        $sql_values = array();
        $deal_code_str = $this->arr_to_in_sql_value($deal_code_arr, 'deal_code', $sql_values);
        //获取未发货且在上一交易号查询结果集内的订单号
        $sql = "SELECT DISTINCT rd.sell_record_code, rd.deal_code, sr.seller_flag 
                FROM oms_sell_record sr, oms_sell_record_detail rd 
                WHERE sr.sell_record_code = rd.sell_record_code AND rd.deal_code IN ({$deal_code_str}) AND sr.order_status IN(0,1) AND sr.shipping_status<4 AND (sr.seller_flag=0 OR sr.seller_flag='') AND sr.sale_channel_code='taobao'";
        $record_data = $this->db->get_all($sql, $sql_values);
        if (empty($record_data)) {
            return $this->format_ret(1, '', '未找到需要处理的系统订单');
        }

        $seller_flag_arr = load_model('util/FormSelectSourceModel')->get_seller_flag();
        $seller_flag_arr = array_column($seller_flag_arr, 1, 0);
        foreach ($record_data as $r) {
            $seller_flag = $data[$r['deal_code']];
            $sql = "UPDATE oms_sell_record SET seller_flag=:seller_flag WHERE sell_record_code=:code";
            $this->db->query($sql, array(':seller_flag' => $seller_flag, ':code' => $r['sell_record_code']));
            $this->add_action($r['sell_record_code'], "更新卖家旗帜", '由无更新为' . $seller_flag_arr[$seller_flag]);
        }

        return $this->format_ret(1);
    }

    /**
     * 根据平台订单更新系统订单商家备注，订单拦截设问，增量
     * @author WMH
     * @version 2018-02-06
     */
    public function opt_record_by_seller_remark() {
        //更新淘宝卖家旗帜
        $this->update_seller_flag();
        //更新商家备注
        //获取上次执行时间
        $curr_time = time();
        $pre_exec_time = $this->db->get_value("SELECT `exec_time` FROM `sys_schedule_record` WHERE `type_code`='update_seller_remark'");
        if ($pre_exec_time === FALSE) {
            $last_time = strtotime('-1 days', $curr_time);
        } else {
            $last_time = $pre_exec_time;
        }
        $pre_data_time = $curr_time - (3600 * 24);
        if ($last_time < $pre_data_time) {
            $last_time = $pre_data_time;
        }

        //记录当次增量执行时间
        $sql = "INSERT INTO `sys_schedule_record` (`type_code`,`exec_time`) VALUES('update_seller_remark','{$curr_time}') ON DUPLICATE KEY UPDATE `exec_time`=VALUES(exec_time)";
        $this->query($sql);

        //获取已付款未发货且商家备注不为空的交易号和商家备注
        $sql = "SELECT tid,seller_remark_change_time FROM api_order WHERE seller_remark<>'' AND status=1 AND is_change=1 AND seller_remark_change_time>:last_time order by seller_remark_change_time limit 500 ";
        $deal_data = $this->db->get_all($sql, array(":last_time" => $last_time));
        if (empty($deal_data)) {
            return $this->format_ret(1, '', '未找到符合条件的平台订单');
        }
        $end_row = end($deal_data);
        $this->db->update('sys_schedule_record', array('exec_time' => $end_row['seller_remark_change_time']), " type_code='update_seller_remark' ");
        $deal_code_arr = array_column($deal_data, 'tid');

        //获取对应订单
        $sql_values = array();
        $deal_code_str = $this->arr_to_in_sql_value($deal_code_arr, 'deal_code', $sql_values);
        $sql = "SELECT DISTINCT sr.sell_record_code FROM oms_sell_record sr,oms_sell_record_detail rd  WHERE sr.sell_record_code=rd.sell_record_code AND rd.deal_code IN({$deal_code_str}) AND sr.order_status IN(0,1) AND sr.shipping_status<4";
        $sell_code_arr = $this->db->get_col($sql, $sql_values, false);
        if (empty($sell_code_arr)) {
            return $this->format_ret(1, '', '未找到需要处理的系统订单');
        }
        $sql_values = array();
        $sell_code_str = $this->arr_to_in_sql_value($sell_code_arr, 'sell_record_code', $sql_values);
        $sql = "SELECT DISTINCT rd.sell_record_code,rd.deal_code, sr.seller_remark FROM oms_sell_record sr, oms_sell_record_detail rd,api_order ao WHERE sr.sell_record_code = rd.sell_record_code AND rd.deal_code=ao.tid AND sr.sell_record_code IN ({$sell_code_str}) ORDER BY ao.order_first_insert_time_int DESC";
        $record_data = $this->db->get_all($sql, $sql_values);
        if (empty($record_data)) {
            return $this->format_ret(1, '', '未找到需要处理的系统订单');
        }
        //复查平台交易商家备注
        $sql_values = array();
        $deal_code_arr = array_column($record_data, 'deal_code');
        $deal_code_str = $this->arr_to_in_sql_value($deal_code_arr, 'tid', $sql_values);
        $sql = "SELECT tid,seller_remark FROM api_order WHERE tid IN({$deal_code_str})";
        $remark_data = $this->db->get_all($sql, $sql_values);
        $data = array_column($remark_data, 'seller_remark', 'tid');

        $up_remark_data = array(); //更新商家备注的数据
        $problem_record = array(); //需设问的订单
        foreach ($record_data as $val) {
            $seller_remark_new = $data[$val['deal_code']];
            $seller_remark_old = $val['seller_remark'];
            $record_code = $val['sell_record_code'];
            if (isset($up_remark_data[$record_code])) {
                $up_remark_data[$record_code]['remark_new'] .= '|' . $seller_remark_new;
                if ($up_remark_data[$record_code]['remark_new'] == $seller_remark_old) {
                    unset($up_remark_data[$record_code]);
                }
            } else {
                $up_remark_data[$record_code]['remark_old'] = empty($seller_remark_old) ? '未设置' : $seller_remark_old;
                $up_remark_data[$record_code]['remark_new'] = addslashes($seller_remark_new);
                $problem_record[] = $record_code;
            }
        }
        //过滤未更新商家备注的订单
        foreach ($up_remark_data as $key => $row) {
            if ($row['remark_old'] == $row['remark_new']) {
                unset($up_remark_data[$key]);
            }
        }

        if (empty($up_remark_data)) {
            return $this->format_ret(1, '', '没有需要更新商家备注的订单');
        }

        foreach ($up_remark_data as $code => $d) {
            $code = (string) $code;
            $sql = "UPDATE oms_sell_record SET seller_remark=:seller_remark,is_seller_remark=1 
                        WHERE sell_record_code=:code";
            $this->db->query($sql, array(':seller_remark' => $d['remark_new'], ':code' => $code));
            $msg = addslashes("由< {$d['remark_old']} >更新为< {$d['remark_new']} >");
            $this->add_action($code, "更新平台商家备注", $msg);
        }

        $problem_record = array_unique($problem_record);
        if (empty($problem_record)) {
            return $this->format_ret(1, '', '没有需要设问的订单');
        }

        unset($record_data);
        //获取更新商家备注设问节点，默认为未确认
        $sync_node = load_model("sys/SysParamsModel")->get_val_by_code('sync_seller_remark_node');
        $sync_node = $sync_node['sync_seller_remark_node'];
        if (!in_array($sync_node, array(0, 1, 2, 3))) {
            return $this->format_ret(-1, '', '更新商家备注，拦截设问节点有误');
        }
        if ($sync_node == 3) { //不拦截不设问
            return $this->format_ret(1);
        }
        $where = '';
        switch ($sync_node) {
            case 1:
                $where = ' AND order_status IN(0,1) AND shipping_status<2';
                break;
            case 2:
                $where = ' AND order_status IN(0,1) AND shipping_status<4';
                break;
            default:
                $where = ' AND order_status=0 AND shipping_status=0';
                break;
        }
        //将未确认的订单进行设问
        $sql_values = array();
        $problem_record_str = $this->arr_to_in_sql_value($problem_record, 'sell_record_code', $sql_values);
        $sql = "SELECT sell_record_code FROM oms_sell_record WHERE sell_record_code IN({$problem_record_str})";
        $sql .= $where;
        $set_problem_record = $this->db->get_all($sql, $sql_values);
        if (empty($set_problem_record)) {
            return $this->format_ret(1, '', '没有需要设问的订单');
        }
        array_walk($set_problem_record, function($record) use($sync_node) {
            $this->opt_intercept_and_problem($record['sell_record_code'], $sync_node);
        });

        return $this->format_ret(1);
    }

    /**
     * 更新商家备注，订单拦截设问
     * @param string $sell_record_code 订单号
     * @param int $sync_node 同步商家备注节点
     * @return array 拦截设问结果
     */
    function opt_intercept_and_problem($sell_record_code, $sync_node) {
        $this->begin_trans();
        $sql = 'SELECT order_status FROM oms_sell_record WHERE sell_record_code=:code';
        $record = $this->db->get_row($sql, [':code' => $sell_record_code]);
        //满足条件拦截，（拣货中、已拣货订单）
        if (in_array($sync_node, array(1, 2)) && $record['order_status'] == 1) {
            $ret = $this->opt_intercept($sell_record_code);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
        }

        //订单设问
        $is_active = load_model('base/QuestionLabelModel')->get_is_active_value('SELLER_REMARK');
        if ($is_active == 1) {
            $ret = $this->opt_problem($sell_record_code, 'SELLER_REMARK', array(), 1);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
        }

        $this->commit();
        return $this->format_ret(1);
    }

    //添加套餐商品
    function opt_new_combo_detail($request) {
        $combo_diy_arr = array();
        //获取套餐商品的子sku
        $log = '';
        foreach ($request['data'] as $key => $val) {
            $log .= "套餐条码:" . $val['barcode'] . ";数量:" . $val['num'];
            $data = $this->get_combo_diy($val['sku'], $val['num']);
            $combo_diy_arr = array_merge($combo_diy_arr, $data);
        }

        $record_detail = $this->get_detail_list_by_code($request['sell_record_code'], 'sell_record_detail_id');
        $record = $this->get_record_by_code($request['sell_record_code'], 'is_replenish');

        $this->begin_trans();
        try {
            foreach ($combo_diy_arr as $key => $val) {
                if (!is_array($val) || empty($val['num']) || $val['num'] < 0) {
                    continue;
                }
                //添加明细
                $r = $this->new_combo_detail($request['sell_record_code'], $val, $request['deal_code'], 0,$record['is_replenish']);
                if ($r['status'] < 0) {
                    $this->rollback();
                    return $this->format_ret(-1, $r['message']);
                }

                $new_record_detail = $this->get_detail_list_by_code($request['sell_record_code'], 'sell_record_detail_id');
            }
            //刷新订单数据
            $ret = $this->edit_detail_after_flush_data(null, $record_detail, $new_record_detail, $request['sell_record_code'], 0);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            if ($ret['status'] == 1) {
                $log .= " 实物库存锁定：" . $ret['message'];
            }
            $this->add_action($request['sell_record_code'], "新增套餐", $log);

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function get_combo_diy($sku, $num) {
        $sql = "SELECT sku,goods_code,spec1_code,spec2_code,num,price,p_sku FROM goods_combo_diy WHERE p_sku = '{$sku}'";
        $combo_diy_arr = $this->db->get_all($sql);
        foreach ($combo_diy_arr as &$val) {
            $val['num'] = $val['num'] * $num;
            //获取商品条码
            $val['barcode'] = oms_tb_val('goods_sku', 'barcode', array('sku' => $val['sku']));
        }
        return $combo_diy_arr;
    }

    function new_combo_detail($sellRecordCode, $combo_detail, $deal_code, $is_gift = 0,$is_replenish = 0) {
        $record = $this->get_record_by_code($sellRecordCode);
        if (empty($record)) {
            return array('status' => -1, 'message' => '订单不存在');
        }
        if ($is_gift == 1) {
            $sum = 0;
        }
        $sql = "select * from goods_sku where sku = :code";
        $sku = $this->db->get_row($sql, array('code' => $combo_detail['sku']));
        if (empty($sku)) {
            return array('status' => -1, 'message' => 'SKU不存在:' . $combo_detail['sku']);
        }

        $barcode = get_barcode_by_sku($combo_detail['sku']);

        $sql = "select a.* from base_goods a where a.goods_code = :code";
        $goods = $this->db->get_row($sql, array('code' => $sku['goods_code']));

        if (empty($goods)) {
            return array('status' => -1, 'message' => '商品不存在');
        }
        $cost_price = $sku['cost_price'];
        if ($cost_price <= 0 || empty($cost_price)) {
            $cost_price = $goods['cost_price'];
        }
        $goods_price = $combo_detail['price'];
        if (is_null($combo_detail['price'])) {
            $goods_price = $sku['price'];
            if ($goods_price <= 0 || empty($goods_price)) {
                $goods_price = $goods['sell_price'];
            }
        }

        try {
            $sum = $goods_price * $combo_detail['num'];
            $d = array(
                'sell_record_code' => $record['sell_record_code'],
                'deal_code' => $deal_code,
                'shipping_time' => $record['plan_send_time'],
                'goods_code' => $sku['goods_code'],
                'spec1_code' => $sku['spec1_code'],
                'spec2_code' => $sku['spec2_code'],
                'sku_id' => $sku['sku_id'],
                'sku' => $sku['sku'],
                'barcode' => $barcode,
                'cost_price' => $cost_price,
                'refer_price' => $goods['sell_price'],
                'sell_price' => $goods['sell_price'],
                'goods_price' => $goods_price,
                'rebate' => '1',
                'is_delete' => 0,
                'avg_money' => $sum,
                'num' => $combo_detail['num'],
                'is_gift' => $is_gift,
                'combo_sku' => $combo_detail['p_sku'],
            );
            if ($record['is_fenxiao'] == 2) {
                //取出分销结算单价
                $d['trade_price'] = load_model('fx/GoodsManageModel')->compute_fx_price($record['fenxiao_code'], $d, $record['record_time']);
                $d['fx_amount'] = $d['trade_price'] * $num;
            }
            if($is_replenish){
                $d['avg_money'] = 0;
                $d['sell_price'] = 0;
                $d['trade_price'] = 0;
                $d['fx_amount'] = 0;
            }
            $update_str = "num = VALUES(num) + num , avg_money = VALUES(avg_money) + avg_money";
            $ret = $this->insert_multi_duplicate('oms_sell_record_detail', array($d), $update_str);
            if ($ret['status'] < 1) {
                return $this->format_ret(-1, '', '保存订单明细出错');
            }
        } catch (Exception $e) {
            return array('status' => -1, 'message' => '保存失败:' . $e->getMessage());
        }

        $d['sell_record_detail_id'] = $ret['data'];

        return array('status' => 1, 'data' => $d);
    }

    /**
     * 更新理论重量
     */
    function update_record_goods_weigh($sell_record_code) {
        $detail_info = $this->get_detail_by_sell_record_code($sell_record_code);
        $goods_weigh = (load_model('op/PolicyExpressModel')->get_record_detail_goods_weigh($detail_info)) * 0.001;
        $goods_weigh_new = number_format($goods_weigh, 3, ".", "");
        $where = "sell_record_code='{$sell_record_code}'";
        $upd_arr['goods_weigh'] = $goods_weigh_new;
        $status = $this->db->update('oms_sell_record', $upd_arr, $where);
        return $status;
    }

    function create_fail_file($error_msg) {
        $fail_top = array('订单号', '错误信息');
        $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
        $message = '';
//        $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }
    
    /**
     * @author FBB 2017-05-18
     * @param int $sell_record_code 订单号
     * @return array 运单号和打印数据数组
     */
    function get_waybill($sell_record_code){
        $sellRecord = $this->get_record_by_code($sell_record_code);
        $sellDetail = $this->get_detail_by_sell_record_code($sell_record_code);
        //分析热敏数据获取方法，三期或四期
        $print_type = $this->check_print_type($sellRecord['express_code']);
        if(isset($print_type['status']) && $print_type['status'] < 0) {
            return $print_type;
        }
        //获取热敏数据主方法
        $result = $this->get_waybill_by_sell_record($sellRecord, $sellDetail, $print_type['type'], $print_type['rm_shop_code']);
        return $result;
    }
    
    
    /**
     * @todo 通过配送方式分析热敏数据获取采用的方法
     * @author FBB 2017-05-18
     * @param int $express_code 配送方式代码
     * @return string cloud 菜鸟云打印 oldcloud 云栈三期
     */
    function check_print_type($express_code){
        $sql = "SELECT print_type, rm_id, rm_shop_code FROM base_express WHERE express_code=:express_code";
        $sql_values = array(":express_code" => $express_code);
        $express = $this->db->get_row($sql, $sql_values);
        if($express['print_type'] == 2){
            $template_sql = "SELECT is_buildin FROM sys_print_templates WHERE print_templates_id = :print_templates_id";
            $template_sql_values = array("print_templates_id" => $express['rm_id']);
            $template_data = $this->db->get_row($template_sql, $template_sql_values);
            if($template_data['is_buildin'] == 3) {
                return array('type' => 'cloud', 'rm_shop_code' => $express['rm_shop_code']);
            }
            return array('type' => 'oldcloud', 'rm_shop_code' => $express['rm_shop_code']);
        } else {
            return $this->format_ret(-2, '', '配送方式中打印类型未选择云栈热敏，不能获取云栈数据');
        }
    }
    
     /**
     * @todo 通过订单主信息和明细以及获取模式获取热敏数据
     * @author FBB 2017-05-18
     * @param array $sellRecord 订单主信息
     * @param array $sellDetail 订单明细
     * @param string $print_type 获取类型 cloud 菜鸟云打印 oldcloud 云栈三期
     * @param string $rm_shop_code 绑定店铺
     * @return array 运单号和打印数据数组
     */
    function get_waybill_by_sell_record($sellRecord, $sellDetail, $print_type, $rm_shop_code) {
        $_receiver_ids = array();
        $params = array();
        //获取商品信息
        foreach ($sellDetail as $val) {
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
            $goods_name = $sku_info['goods_name'] . "[{$sku_info['spec1_name']},{$sku_info['spec2_name']}]";
            $sellRecord['goods_list'][] = array('goods_name' => $goods_name, 'num' => $val['num']);
        }
        //获取发货人信息（店铺信息不为空取店铺，否则取仓库）
        $store_sql = "SELECT * FROM base_store WHERE store_code = :store_code";
        $store = $this->db->get_row($store_sql, array('store_code' => $sellRecord['store_code']));
        $store_sender_info = $store;
        $store_sender_info['tel'] = $store['contact_phone'];
        
        $shop_sql = "SELECT * FROM base_shop WHERE shop_code = :shop_code";
        $shop = $this->db->get_row($shop_sql, array('shop_code' => $sellRecord['shop_code']));
        if (!empty($shop['province']) && !empty($shop['city']) && !empty($shop['contact_person']) && !empty($shop['tel']) && !empty($shop['address'])) {
            $store_sender_info = $shop;
        }
        
        $express_sql = "SELECT company_code FROM base_express WHERE express_code = :express_code";
        $express = $this->db->get_row($express_sql, array('express_code' => $sellRecord['express_code']));
        
        //发货人地址
        $sellRecord['sender_province'] = $store_sender_info['province'];
        $sellRecord['sender_city'] = $store_sender_info['city'];
        $sellRecord['sender_district'] = $store_sender_info['district'];
        $sellRecord['sender_addr'] = $store_sender_info['address'];
        $sellRecord['sender_street'] = $store_sender_info['street'];
        $sellRecord['contact_phone'] = $store_sender_info['tel'];
        $sellRecord['contact_person'] = $store_sender_info['contact_person'];
        
        //地址id合集
        $_receiver_ids[] = $store_sender_info['province'];
        $_receiver_ids[] = $store_sender_info['city'];
        $_receiver_ids[] = $store_sender_info['district'];
        $_receiver_ids[] = $store_sender_info['street'];
        $_receiver_ids[] = $sellRecord['receiver_province'];
        $_receiver_ids[] = $sellRecord['receiver_city'];
        $_receiver_ids[] = $sellRecord['receiver_district'];
        $_receiver_ids[] = $sellRecord['receiver_street'];

        //淘宝参数长度限制
        $sellRecord['receiver_addr'] = mb_substr(addslashes($sellRecord['receiver_addr']), 0, 100, 'UTF-8');
        //电话
        $sellRecord['receiver_mobile'] = empty($sellRecord['receiver_mobile']) ? $sellRecord['receiver_phone'] : $sellRecord['receiver_mobile'];
        $sellRecord['receiver_tel'] = substr($sellRecord['receiver_mobile'], 0, 20);

        $sellRecord['order_channels_type'] = load_model('oms/DeliverRecordModel')->get_cainiao_sale_channel($sellRecord['sale_channel_code'], $sellRecord['shop_code']);

        if($print_type == 'cloud') {
                //京东货到付款
            if ($sellRecord['pay_type'] == 'cod' && $sellRecord['sale_channel_code'] == 'jingdong') {
                $payable_money = load_model('oms/DeliverRecordModel')->get_jd_cod_payable_money($sellRecord['deal_code_list']);
                if ($payable_money !== false) {
                    $sellRecord['payable_money'] = $payable_money;
                }
            }

             //当当货到付款
            if ($sellRecord['pay_type'] == 'cod' && $sellRecord['sale_channel_code'] == 'dangdang') {
                $dangdang_row = load_model('oms/DeliverRecordModel')->get_dangdang_print($sellRecord['deal_code_list']);
                if ($dangdang_row !== false) {
                    $sellRecord['payable_money'] = $dangdang_row['totalBarginPrice'];
                }
                $sellRecord['contact_person']  = !empty($dangdang_row['consignerName']) ? $dangdang_row['consignerName'] : $sellRecord['contact_person'];
                $sellRecord['contact_phone'] = !empty($dangdang_row['consignerTel']) ? $dangdang_row['consignerTel'] : $sellRecord['contact_phone'];
            }
            
            //获取配送方式的标准模板url
            $template_sql = "SELECT bs.express_code, spt.template_body_default FROM base_express bs, sys_print_templates spt WHERE bs.rm_id=spt.print_templates_id AND bs.print_type=2 AND bs.rm_id!='' AND bs.express_code=:express_code AND spt.is_buildin = 3";
            $template_data = $this->db->get_row($template_sql, array("express_code"=>$sellRecord['express_code']));
        }
        
        //收货地址
        $_new_receiver_ids = implode("','", array_unique($_receiver_ids));
        $_region_data = $this->db->get_all("SELECT id region_id, name region_name FROM base_area WHERE id IN('{$_new_receiver_ids}')");
        foreach ($_region_data as $_region) {
            $_receiver_data[$_region['region_id']] = $_region['region_name'];
        }
        
        $params[$sellRecord['sell_record_code']] = $sellRecord;
        
        foreach ($params as &$sellRecordData) {
            $sellRecordData['sender_province_name'] = isset($_receiver_data[$sellRecord['sender_province']]) ? $_receiver_data[$sellRecord['sender_province']] : '';
            $sellRecordData['sender_district_name'] = isset($_receiver_data[$sellRecord['sender_district']]) ? $_receiver_data[$sellRecord['sender_district']] : '';
            $sellRecordData['sender_city_name'] = isset($_receiver_data[$sellRecord['sender_city']]) ? $_receiver_data[$sellRecord['sender_city']] : '';
            $sellRecordData['sender_street_name'] = isset($_receiver_data[$sellRecord['sender_street']]) ? $_receiver_data[$sellRecord['sender_street']] : '';

            $sellRecordData['receiver_province_name'] = isset($_receiver_data[$sellRecord['receiver_province']]) ? $_receiver_data[$sellRecord['receiver_province']] : '';
            $sellRecordData['receiver_city_name'] = isset($_receiver_data[$sellRecord['receiver_city']]) ? $_receiver_data[$sellRecord['receiver_city']] : '';
            $sellRecordData['receiver_district_name'] = isset($_receiver_data[$sellRecord['receiver_district']]) ? $_receiver_data[$sellRecord['receiver_district']] : '';
            $sellRecordData['receiver_street_name'] = isset($_receiver_data[$sellRecord['receiver_street']]) ? $_receiver_data[$sellRecord['receiver_street']] : '';
            
            $sellRecordData['sender_name'] = $sellRecord['contact_person'];
            $sellRecordData['sender_phone'] = $sellRecord['contact_phone'];
            
            $sellRecordData['company_express_code'] = $express['company_code'];        
            if($print_type == 'cloud') {
                 $sellRecordData['template_url'] = $template_data['template_body_default'];
            }
        }
        
        $client = new TaobaoClient($rm_shop_code);
        $waybill_data = $print_type == 'cloud' ? $client->cloudWlbWaybillPrint($params) : $client->multiWlbWaybillGet($params);
        $result = $this->handle_waybill_data($waybill_data, $print_type);
        return $result;
        
    }
    
   /**
     * @todo 处理面单数据，更新oms_sell_record表
     * @author FBB 2017-05-18
     * @param array $waybill_data 面单数据
     * @return array 运单号和打印数据数组
     */
    function handle_waybill_data($waybill_data){
        //更新订单面单号
        foreach ($waybill_data as $sellRecordCode => $waybill) {
            if (!isset($waybill['data'][0]['waybill_code'])) {
                return $this->format_ret(-1, '', "订单" . $sellRecordCode . ": " . $waybill['message']);
            }
            $dat = json_encode($waybill['data'][0]);
            if ($dat == null) {
                return $this->format_ret(-1, '', "订单" . $sellRecordCode . ": json解析失败");
            }
            $this->begin_trans();
            try {
                $express_data = array('express_no' => $waybill['data'][0]['waybill_code'], 'express_data' => $dat);
                $this->db->update('oms_sell_record', $express_data, array('sell_record_code' => $sellRecordCode));
                $action = $print_type == 'cloud' ? '获取菜鸟云打印热敏物流' : '获取热敏物流';
                if(empty($param[$sellRecordCode]['express_no'])) {
                    $msg = '快递单号更新为：' . $express_data['express_no'];
                } else {
                    $msg = '快递单号由' . $param[$sellRecordCode]['express_no'] . '修改为' . $express_data['express_no'];
                }
                load_model('oms/SellRecordActionModel')->add_action($sellRecordCode, $action, $msg);
                $this->commit();
                return $this->format_ret(1, $express_data);
            } catch (Exception $e) {
                $this->rollback();
                return $this->format_ret(-1, '', "订单" . $sellRecordCode . ": " . $e->getMessage());
            }
        }
    }
    /**
     * 更新订单商品数量
     * @param $sell_record_code
     */
    public function update_record_num($sell_record_code){
        $sql_values = array(':sell_record_code'=>$sell_record_code);
        $sql = 'select sum(num) as num from oms_sell_record_detail where is_delete = 0 and sell_record_code = :sell_record_code group by sku';
        $data = $this->db->get_all($sql,$sql_values);
        $record_values = array('goods_num'=>array_sum(array_column($data,'num')),'sku_num'=>count($data));
        return $this->db->update('oms_sell_record',$record_values,array('sell_record_code'=>$sell_record_code));
    }
    /**
     * 根据订单创建并验收调整单
     */
    public function create_stock_by_record($record){
        //获取批次信息
        $lof_sql = 'select 
                        ord.sell_record_code,
                        ord.goods_code,
                        ord.sku,
                        sum(ord.num) num,
                        osl.lof_no,
                        osl.spec1_id,
                        osl.spec1_code,
                        osl.spec2_id,
                        osl.spec2_code,
                        osl.store_code,
                        osl.production_date
                    from oms_sell_record_detail ord 
                    LEFT join oms_sell_record_lof osl on ord.sku = osl.sku and ord.sell_record_code = osl.record_code
                    where ord.sell_record_code = :sell_record_code 
                    and osl.record_type = 1
                    and ord.is_delete = 0 group by ord.sku,osl.lof_no';
        $data = $this->db->get_all($lof_sql,array(':sell_record_code'=>$record['sell_record_code']));
        $record_code = load_model('stm/StockAdjustRecordModel')->create_fast_bill_sn();
        $ajust_record = array('is_add_time'=>date('Y-m-d H:i:s'),'record_code'=>$record_code, 'record_time'=>date('Y-m-d'), 'adjust_type'=>'order_replenish', 'remark'=>'原订单号'.$record['is_replenish_from'].'漏发（补发单号'.$record['sell_record_code'].')', 'store_code'=>$record['store_code'], 'is_add_person'=>$record['confirm_person']);
        $this->begin_trans();
        $ret = $this->db->insert('stm_stock_adjust_record',$ajust_record);
        if($ret !== true){
            $this->rollback();
            return $this->format_ret(-1,'','创建调整单失败');
        }
        $id = $this->db->get_value('select stock_adjust_record_id from stm_stock_adjust_record where record_code = :record_code',array(':record_code'=>$record_code));
        $store_code=$record['store_code'];
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $data);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'adjust', $data);
        if($ret['status']<1){
            return $ret;
        }
        //调整单明细添加
        $ret = load_model('stm/StmStockAdjustRecordDetailModel')->add_detail_action($id, $data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '增加明细', 'module' => "stock_adjust_record", 'pid' => $id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }else{
            $this->db->rollback();
            return $ret;
        }
        //调整单验收
        $ret = load_model('stm/StockAdjustRecordModel')->checkin($id);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '已验收', 'action_name' => '已验收', 'module' => "stock_adjust_record", 'pid' => $id);
            $ret = load_model('pur/PurStmLogModel')->insert($log);
        }else{
            $this->db->rollback();
            return $ret;
        }
        $this->db->commit();
        return $ret;
    }
}

