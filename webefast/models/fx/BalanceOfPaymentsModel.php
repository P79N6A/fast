<?php

/**
 * 供应商类型相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('base');
require_lib('util/oms_util', true);

class BalanceOfPaymentsModel extends TbModel {

    public $pay_type_arr = array(
        'bank' => '线下转账',
        'balance' => '账户余额',
        'alipay' => '支付宝',
        'weixinpay' => '微信支付',
    );

    function get_table() {
        return 'fx_income_pay';
    }

    public $abstract = array(
        'cash_recharge' => '现金充值',
        'other_recharge' => '其他充值',
        'refund' => '退款',
        'order_pay' => '订单付款',
        'other_pay' => '其他付款',
        'tag_red' => '红冲',
    );

    /**
     * 生成单据号
     */
    function create_fast_bill_sn() {
        $sql = "select id  from fx_income_pay order by id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = date('YmdHis') . add_zero($djh, 5);
        return $jdh;
    }

    //分销商充值总额、扣款总额
    function sum_money($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql = "SELECT sum(money) FROM fx_income_pay AS rl WHERE 1 ";
        //分销商
        $sql_values = array();
        if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
            $arr = explode(",", $filter['custom_code']);
            $str = $this->arr_to_in_sql_value($arr, 'custom_code', $sql_values);
            $sql .= " AND rl.distributor in ({$str}) ";
        }
        //支付流水号
        if (isset($filter['serial_number']) && $filter['serial_number'] != '') {
            $sql .= " AND (rl.serial_number LIKE :serial_number OR rl.relevance_serial_number LIKE :serial_number)";
            $sql_values[':serial_number'] = $filter['serial_number'] . '%';
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql .= " AND rl.record_code LIKE :record_code ";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        //业务日期
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $filter['record_time_start'] = strtotime($filter['record_time_start']);
            $sql .= " AND rl.record_time >= :record_time_start";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $filter['record_time_end'] = strtotime($filter['record_time_end']);
            $sql .= " AND rl.record_time <= :record_time_end";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }

        //充值
        $sql1 = $sql . ' AND rl.capital_type = 1 ';
        $recharge_money = $this->db->get_value($sql1, $sql_values);
        $recharge_money = empty($recharge_money) ? 0 : $recharge_money;
        //扣款
        $sql2 = $sql . " AND rl.capital_type = 0 ";
        $deduct_money = $this->db->get_value($sql2, $sql_values);
        $deduct_money = empty($deduct_money) ? 0 : $deduct_money;
        return array('recharge_money' => $recharge_money, 'deduct_money' => $deduct_money);
    }

    function get_by_account($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1 ";
        $sql_values = array();
        
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) { //分销商登录
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            if (!empty($custom['custom_code'])) {
                $sql_main .= " AND rl.distributor = :custom_code";
                $sql_values[':custom_code'] = $custom['custom_code'];
            } else {
                $sql_main .= " AND 1 != 1";
            }
        } else {
            //分销商
            if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
                $arr = explode(",", $filter['custom_code']);
                $str = $this->arr_to_in_sql_value($arr, 'custom_code', $sql_values);
                $sql_main .= " AND rl.distributor in ({$str}) ";
            }
        }
        //页面类型
        if(isset($filter['list_type']) && $filter['list_type'] == 'account') { //资金账户
            $sql_main .= " AND (rl.detail_type = 0 OR ((rl.income_type <> 1 OR rl.income_type IS NULL) AND rl.detail_type = 1) OR rl.detail_type = 2)";
        } else if(isset($filter['list_type']) && $filter['list_type'] == 'balance_of_payments' || $filter['ctl_type'] == 'export'){ //收支明细
            $sql_main .= " AND rl.abstract != 'tag_red' ";
        }
        //明细类型
        if (isset($filter['detail_type']) && $filter['detail_type'] !== '') {
            $sql_main .= " AND rl.detail_type = :detail_type ";
            $sql_values[':detail_type'] = $filter['detail_type'];
        }
        //业务流水单据类型
        if (isset($filter['record_type']) && $filter['record_type'] !== '') {
            $sql_main .= " AND rl.record_type = :record_type ";
            $sql_values[':record_type'] = $filter['record_type'];
        }
        //支付流水号
        if (isset($filter['serial_number']) && $filter['serial_number'] != '') {
            $sql_main .= " AND (rl.serial_number LIKE :serial_number OR rl.relevance_serial_number LIKE :serial_number)";
            $sql_values[':serial_number'] = $filter['serial_number'] . '%';
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND rl.record_code LIKE :record_code ";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        //业务日期
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $filter['record_time_start'] = strtotime($filter['record_time_start']);
            $sql_main .= " AND rl.record_time >= :record_time_start";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $filter['record_time_end'] = strtotime($filter['record_time_end']);
            $sql_main .= " AND rl.record_time <= :record_time_end";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        //摘要
        if (isset($filter['abstract']) && $filter['abstract'] != '') {
            $sql_main .= " AND rl.abstract = :abstract ";
            $sql_values[':abstract'] = $filter['abstract'];
        }
        //收款账户
        if (isset($filter['income_account']) && $filter['income_account'] != '') {
            $sql_main .= " AND rl.income_account = :income_account ";
            $sql_values[':income_account'] = $filter['income_account'];
        }

        $select = 'rl.*';
        $sql_main .= " order by rl.create_time desc,rl.id desc";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            if(isset($filter['list_type']) && $filter['list_type'] == 'balance_of_payments' || $filter['ctl_type'] == 'export'){ //收支明细
                //判断是增还是减
                if($val['abstract'] == 'cash_recharge' || $val['abstract'] == 'other_recharge' || $val['abstract'] == 'refund') {
                    $val['money'] = '+'.$val['money'];
                } else if($val['abstract'] == 'order_pay' || $val['abstract'] == 'other_pay'){
                    $val['money'] = '-'.$val['money'];
                }
            }
            $val['record_time'] = date('Y-m-d H:i:s', $val['record_time']);
            $val['capital_account'] = $val['capital_account'] == 'yck' ? '预存款账户' : '';
            if ($val['capital_type'] == 0) {
                $val['deduct_money'] = $val['money'];
            } else {
                $val['recharge_money'] = $val['money'];
            }
            $val['pay_type'] = $this->pay_type_arr[$val['pay_type_code']];
            $val['status'] = $val['state'] == 1 ? '正常' : '作废';
            $val['abstract_name'] = $this->abstract[$val['abstract']];
            //分销商
            $val['custom_name'] = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $val['distributor']));
            //收款账户
            $val['account_name'] = oms_tb_val('payment_account', 'account_name', array('account_code' => $val['income_account']));
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        $ret = $this->format_ret($ret_status, $ret_data);
        return $ret;
    }

    function account_count($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_main = "SELECT COUNT(*) AS count FROM {$this->table} rl WHERE 1 ";
        
        $login_type = CTX()->get_session('login_type');
        if ($login_type == 2) { //分销商登录
            $user_code = CTX()->get_session('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            if (!empty($custom['custom_code'])) {
                $sql_main .= " AND rl.distributor = :custom_code";
                $sql_values[':custom_code'] = $custom['custom_code'];
            } else {
                $sql_main .= " AND 1 != 1 ";
            }
        } else {
            //分销商
            if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
                $arr = explode(",", $filter['custom_code']);
                $str = $this->arr_to_in_sql_value($arr, 'custom_code', $sql_values);
                $sql_main .= " AND rl.distributor in ({$str}) ";
               
            }
        }
        //页面类型
        if(isset($filter['list_type']) && $filter['list_type'] == 'account') { //资金账户
            $sql_main .= " AND (rl.detail_type = 0 OR ((rl.income_type <> 1 OR rl.income_type IS NULL) AND rl.detail_type = 1) OR rl.detail_type = 2)";
        } else if(isset($filter['list_type']) && $filter['list_type'] == 'balance_of_payments'){ //收支明细
            $sql_main .= " AND rl.abstract != 'tag_red' ";
        }
        //明细类型
        if (isset($filter['detail_type']) && $filter['detail_type'] !== '') {
            $sql_main .= " AND rl.detail_type = :detail_type ";
            $sql_values[':detail_type'] = $filter['detail_type'];
        }
        //支付流水号
        if (isset($filter['serial_number']) && $filter['serial_number'] != '') {
            $sql_main .= " AND (rl.serial_number LIKE :serial_number OR rl.relevance_serial_number LIKE :serial_number)";
            $sql_values[':serial_number'] = $filter['serial_number'] . '%';
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND rl.record_code LIKE :record_code ";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        //业务日期
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $filter['record_time_start'] = strtotime($filter['record_time_start']);
            $sql_main .= " AND rl.record_time >= :record_time_start";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $filter['record_time_end'] = strtotime($filter['record_time_end']);
            $sql_main .= " AND rl.record_time <= :record_time_end";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        //摘要
        if (isset($filter['abstract']) && $filter['abstract'] != '') {
            $sql_main .= " AND rl.abstract = :abstract ";
            $sql_values[':abstract'] = $filter['abstract'];
        }
        //收款账户
        if (isset($filter['income_account']) && $filter['income_account'] != '') {
            $sql_main .= " AND rl.income_account = :income_account ";
            $sql_values[':income_account'] = $filter['income_account'];
        }
        $ret = $this->db->get_row($sql_main, $sql_values);
        return $ret;
    }

    /**
     * 添加收支明细
     * @param array $data 数据
     * @return array 新增结果
     */
    function add_detail($data,$type = '') {
        $data['serial_number'] = $this->create_fast_bill_sn();
        $data['remark'] = empty($data['remark']) ? '' : trim($data['remark']);
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), "；", $data['remark']);
        $data['create_time'] = time();
        if(empty($type)) {
            $isDeamon = false;
            if(CTX()->app['mode'] == 'cli') {
                $isDeamon = true;
            }
            
            if($isDeamon){
                $user_name =  load_model('sys/UserTaskModel')->get_user_name();
                $data['operator'] = !empty($user_name) ? $user_name:'计划任务';
            } else {
                $data['operator'] = CTX()->get_session('user_name');
            }
        }
        $data['record_time'] = empty($data['record_time']) ? time() : strtotime($data['record_time']);
        $data['distributor'] = $data['custom_code'];
        $data['state'] = 1;
        unset($data['custom_code']);
        return parent::insert($data);
    }

    //作废流水
    function cancellation($id) {
        $sql = "SELECT * FROM fx_income_pay WHERE id = :id";
        $record_data = $this->db->get_row($sql, array(':id' => $id));
        //批发销货单信息
        $sql = "SELECT * FROM wbm_store_out_record WHERE record_code = :record_code";
        $wbm_record_data = $this->db->get_row($sql, array(':record_code' => $record_data['record_code']));
        //验证单据
        $check = $this->cancellation_check($record_data, $wbm_record_data);
        if ($check['status'] < 0) {
            return $check;
        }
        //判断类型
        if ($record_data['income_type'] == 1 && $record_data['detail_type'] == 1) {
            //线下付款
            $ret = $this->invalid_offline_documents($record_data, $wbm_record_data);
            return $ret;
        } else if($record_data['income_type'] == 2 && $record_data['detail_type'] == 1) {
            //处理账户余额
            $ret = $this->balance_payment($record_data, $wbm_record_data);
            return $ret;
        } else if($record_data['detail_type'] == 0) {
            //资金流水生成的
            $ret = $this->red_bankroll($record_data);
            return $ret;
        }
    }

    //验证单据
    function cancellation_check($data, $wbm_record_data) {
        //不是资金流水，验证
        if($data['detail_type'] != 0) {
            //批发销货单是否付款
            if ($wbm_record_data['pay_status'] == 0) {
                return $this->format_ret(-1, '', '关联的批发销货单未付款');
            }
            if ($data['detail_type'] != 1) {
                return $this->format_ret(-1, '', '该单据不是业务单据');
            }
        }
        if ($data['state'] == 2) {
            return $this->format_ret(-1, '', '该单据已作废');
        }
        $ret = $this->format_ret(1,'','');
        return $ret;
    }

    //组合资金流水信息
    function get_capital($data) {
        //查询分销商余额
        if($data['capital_account'] == 'yck') {
            $select = 'yck_account_capital';
        }
        //作废流水关联流水号
        $params['relevance_serial_number'] = $data['serial_number'];
        $custom_date = load_model('fx/AccountModel')->get_by_remainder($data['distributor'],$select);
        $serial_number = $this->create_fast_bill_sn();
        $params['serial_number'] = $serial_number;
        $params['capital_account'] = $data['capital_account'];
        $params['pay_type_code'] = $data['pay_type_code'];
        $params['distributor'] = $data['distributor'];
        $params['money'] = $data['money'];
        $params['operator'] = CTX()->get_session('user_name');
        $params['create_time'] = time();
        $params['record_time'] = time();
        $params['abstract'] = 'tag_red';
        if ($data['detail_type'] == 1) {//业务单据生成信息
            $params['record_code'] = $data['record_code'];
            //明细类型 1业务流水
            $params['detail_type'] = 1;
            $params['remark'] = '由流水号为：' . $data['serial_number'] . '作废生成的。';
            //充值或扣款 1充值 0扣款
            $params['capital_type'] = $data['record_type'] == 0 ? 1 : 0 ;
//            if ($data['record_type'] == 0) {
//                //资金流水 1充值
//                $params['capital_type'] = 1;
//            } else {
//                //资金流水 0扣款
//                $params['capital_type'] = 0;
//            }
        } else if($data['detail_type'] == 0) {//资金流水生成信息
            $params['detail_type'] = 0;
            $params['remark'] = '由流水号为：' . $data['serial_number'] . '红冲生成的。';
            //充值或扣款 1充值 0扣款 
            $params['capital_type'] = $data['capital_type'] == 0 ? 1 : 0;
        }
        if($params['capital_type'] == 1) {
            //充值的分销商余额
            $params['balance_money'] = $custom_date[$select] + $data['money'];
        } else {
            //扣款的分销商余额
            $params['balance_money'] = $custom_date[$select] - $data['money'];
        }
        return $params;
    }

    //作废线下单据
    function invalid_offline_documents($data, $wbm_record_data) {
        $difference = $wbm_record_data['pay_money'] - $data['money'];
        //0未付款 1部分付款
        $pay_status = $difference == 0 ? 0 : 1;
        $this->begin_trans();
        try {
            if ($difference < 0) {
                throw new Exception('扣减批发销货单金额异常', -1);
            }
            //修改批发销货单付款金额
            $update_num = $this->update_wbm_store_out($difference, $pay_status, $wbm_record_data['record_code']);
            if ($update_num != 1) {
                throw new Exception('回写批发销货单失败', -1);
            }

            //作废流水
            $update_num = $this->cancel_income($data['id']);
            if ($update_num != 1) {
                throw new Exception('操作失败', -1);
            }

            $this->commit();
            return $this->format_ret(1, '', '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            $msg = $e->getCode() == 0 ? '操作异常' : $e->getMessage();
            return $this->format_ret(-1, '', $msg);
        }
    }

    //修改批发销货单状态和付款金额
    function update_wbm_store_out($difference, $pay_status, $reocrd_code) {
        $this->update_exp('wbm_store_out_record', array('pay_status' => $pay_status, 'pay_money' => $difference), array('record_code' => $reocrd_code));
        $update_num = $this->affected_rows();
        return $update_num;
    }

    //作废流水
    //$serial_number 关联作废（红冲）流水号
    function cancel_income($id,$serial_number = '') {
        $ret = $this->update(array('state' => 2,'relevance_red_serial_number' => $serial_number), array('id' => $id));
        $update_num = $this->affected_rows();
        return $update_num;
    }

    //作废账户余额付款
    function balance_payment($record_data, $wbm_record_data) {
        $difference = $wbm_record_data['pay_money'] - $record_data['money'];
        //0未付款 1部分付款
        $pay_status = $difference == 0 ? 0 : 1;
        //资金流水信息
        $capital_data = $this->get_capital($record_data);
        $this->begin_trans();
        try {
            if ($difference < 0) {
                throw new Exception('扣减批发销货单金额异常', -1);
            }
            //修改批发销货单
            $update_num = $this->update_wbm_store_out($difference, $pay_status, $wbm_record_data['record_code']);
            if ($update_num != 1) {
                throw new Exception('回写批发销货单失败', -1);
            }
            //新增一条新的流水
            $this->insert($capital_data);
            $update_num = $this->affected_rows();
            if ($update_num != 1) {
                throw new Exception('新增收支明细失败', -1);
            }
            //修改分销商余额
            $update_num = load_model('base/CustomModel')->update_money($capital_data);
            if ($update_num != 1) {
                throw new Exception('回写分销商余额失败', -1);
            }
            //作废流水
            $update_num = $this->cancel_income($record_data['id'],$capital_data['serial_number']);
            if ($update_num != 1) {
                throw new Exception('操作失败', -1);
            }
            $this->commit();
            return $this->format_ret(1, '', '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            $msg = $e->getCode() == 0 ? '操作异常' : $e->getMessage();
            return $this->format_ret(-1, '', $msg);
        }
    }

    /**
     * 删除收支明细（只能删除业务流水线下支付明细）
     * @param int $id 明细id
     * @return array 操作结果
     */
    function delete_detail($id) {
        $msg = '';
        if (empty($id)) {
            $msg = '参数错误，请刷新页面重试！';
        } else {
            $ret = $this->get_row(array('id' => $id));
            $detail = $ret['data'];
            if ($ret['status'] != 1 || empty($detail)) {
                $msg = '明细不存在，不能删除！';
            } else if ($detail['state'] != 2) {
                $msg = '请先作废记录再操作删除！';
            } else if ($detail['detail_type'] != 1 || $detail['income_type'] != 1) {
                $msg = '非线下转账业务收款记录，不允许删除！';
            }
        }
        if ($msg != '') {
            return $this->format_ret(-1, '', $msg);
        }
        $this->begin_trans();
        $ret = $this->delete(array('id' => $id));
        $affect_row = $this->affected_rows();
        if ($ret['status'] != 1 || $affect_row != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除失败');
        }
        $this->commit();
        return $this->format_ret(1, '', '删除成功');
    }

    /**
     * 获取摘要
     * @param int $type 1-充值;0-扣款
     * @return array 摘要数组
     */
    function get_abstract_select($type) {
        $arr = $type == 1 ? array('cash_recharge', 'other_recharge', 'refund') : array('order_pay', 'other_pay');
        $abstract = get_array_vars($this->abstract, $arr);
        $select = array();
        foreach ($abstract as $key => $val) {
            $select[] = array($key, $val);
        }
        return $select;
    }
    /**
     * 判断是否支持红冲
     */
    function is_custom_money_red($data) {
        if($data['detail_type'] == 0 && $data['capital_type'] == 1) {//红冲充值的流水，查询分销商余额（加上欠款）
            //查询分销商余额
            $custom_data = load_model('fx/AccountModel')->get_by_remainder($data['distributor'], 'yck_account_capital,arrears_money');
            $custom_money = (float)$custom_data['yck_account_capital'] + $custom_data['arrears_money'];
            if($custom_money < $data['money']) {
                return $this->format_ret(-1,'','分销商余额不足，不能红冲');
            }
        }
        return $this->format_ret(1);
    }
    /**
     * 红冲资金账户
     * @param type $data 收支明细数据
     */
    function red_bankroll($data) {
        //资金流水信息
        $capital_data = $this->get_capital($data);
        $this->begin_trans();
        try{
            //分销商余额是否支持红冲
            $ret = $this->is_custom_money_red($data);
            if($ret['status'] < 0) {
                return $ret;
            }
            //新增一条新的流水
            $this->insert($capital_data);
            $update_num = $this->affected_rows();
            if ($update_num != 1) {
                throw new Exception('新增收支明细失败', -1);
            }
            //修改分销商余额
            $update_num = load_model('base/CustomModel')->update_money($capital_data);
            if ($update_num != 1) {
                throw new Exception('回写分销商余额失败', -1);
            }
            //作废流水
            $update_num = $this->cancel_income($data['id'],$capital_data['serial_number']);
            if ($update_num != 1) {
                throw new Exception('操作失败', -1);
            }
            $this->commit();
            return $this->format_ret(1, '', '操作成功');
            
        } catch (Exception $e) {
            $this->rollback();
            $msg = $e->getCode() == 0 ? '操作异常' : $e->getMessage();
            return $this->format_ret(-1, '', $msg);
        }
    }
    //分销结算（取消结算）自动生成收支明细
    function create_fx_income_pay($account_data) {
        if($account_data['money'] == 0) {
            return $this->format_ret(1, '', '');
        }
        $params = array(
            'capital_account' => 'yck',
            'money' => $account_data['money'],
            'custom_code' => $account_data['custom_code'],
            'record_time' => date('Y-m-d H:i:s'),
            'record_code' => $account_data['record_code'],
            'detail_type' => 2 //自动生成资金流水
        );
        if($account_data['type'] == 'record_settlement' || $account_data['type'] == 'fx_purchase_settlement') { //代销订单分销结算、经销订单结算
            $params['capital_type'] = 0;
            $params['abstract'] = 'order_pay';
            $params['remark'] = $account_data['type'] == 'record_settlement' ? '由分销订单结算生成' : '由经销订单结算生成';
        } else if($account_data['type'] == 'record_unsettlement' || $account_data['type'] == 'fx_purchase_unsettlement') { //代销订单取消分销结算、经销订单取消结算
            $params['capital_type'] = 1;
            $params['abstract'] = 'refund';
            $params['remark'] = $account_data['type'] == 'record_unsettlement' ? '由分销订单取消结算生成' : '由经销订单取消结算生成';
        } else if($account_data['type'] == 'intercept' || $account_data['type'] == 'combine'){ //订单拦截、合并订单取消结算
            $params['capital_type'] = 1;
            $params['abstract'] = 'refund';
            $params['remark'] = $account_data['type'] == 'intercept' ? '由分销订单拦截生成' : '由合并订单取消结算生成';
        } else if($account_data['type'] == 'return_finance_confirm' || $account_data['type'] == 'fx_purchase_return_settlement') { //代销退单确认退款 、结算退单结算
            $params['capital_type'] = 1;
            $params['abstract'] = 'refund';
            $params['remark'] = $account_data['type'] == 'return_finance_confirm' ? '由分销退单确认退款生成' : '由经销退单结算生成';
        } else if($account_data['type'] == 'notice_record_stop_refund' || $account_data['type'] == 'fx_purchase_return_finish') { 
            // 采购通知单终止，生成退款 、经销退单完成，自动结算
            $params['capital_type'] = 1;
            $params['abstract'] = 'refund';
            $params['remark'] = $account_data['type'] == 'notice_record_stop_refund' ? '由采购通知单终止生成的退款单' : '由批发退货通知单完成，生成的退款单';
        } else if($account_data['type'] == 'notice_record_stop_payment') { //采购通知单终止，生成付款
            $params['capital_type'] = 0;
            $params['abstract'] = 'order_pay';
            $params['remark'] = '由采购通知单终止生成的付款单';
        }
        //添加资金账户
        $ret = load_model('fx/AccountModel')->opt_balance($params);
        return $ret;
    }
}
