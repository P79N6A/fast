<?php

require_model('tb/TbModel');

/**
 * 分销账务管理-待付款业务
 */
class PendingPaymentModel extends TbModel {

    private $pay_status = array(0 => '未收款', 1 => '部分收款');

    /**
     * 根据条件查询数据
     */
    public function get_by_page($filter) {
        $sql_main = ' FROM wbm_store_out_record AS sr WHERE sr.is_store_out=1 AND sr.pay_status<>2 AND sr.is_cancel<>1';
        $select = 'sr.`store_out_record_id`,sr.`record_code`,sr.`distributor_code` AS custom_code,sr.`num`,sr.`money`,sr.`pay_money`,sr.`lastchanged`,sr.`pay_status`,sr.`remark`';
        $sql_values = array();
        
        $login_type = CTX()->get_session('login_type');
        if($login_type == 2){
            $user_code = CTX()->get_session('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            if(!empty($custom['custom_code'])){
                $sql_main .= " AND sr.distributor_code = :custom_code";
                $sql_values[':custom_code'] = $custom['custom_code'];
            } else {
                $sql_main .= " AND 1 != 1 ";
            }
            //$sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code,'get_fx_store');
        } else {
            //分销商代码
            if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
                $distributor_str = deal_strs_with_quote($filter['custom_code']);
                $sql_main .= " AND sr.distributor_code IN({$distributor_str})";
            }
        }

        //付款状态
        if (isset($filter['pay_status']) && $filter['pay_status'] != '') {
            $sql_main .= " AND sr.pay_status=:pay_status";
            $sql_values[':pay_status'] = $filter['pay_status'];
        }
        //出库时间
        if (isset($filter['lastchanged_start']) && $filter['lastchanged_start'] != '') {
            $sql_main .= " AND (sr.lastchanged >= :lastchanged_start )";
            $sql_values[':lastchanged_start'] = $filter['lastchanged_start'] . ' 00:00:00';
        }
        if (isset($filter['lastchanged_end']) && $filter['lastchanged_end'] != '') {
            $sql_main .= " AND (sr.lastchanged <= :lastchanged_end )";
            $sql_values[':lastchanged_end'] = $filter['lastchanged_end'] . ' 23:59:59';
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND sr.record_code = :record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        }

        $sql_main .= ' ORDER BY sr.lastchanged DESC ';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            $val['money'] = sprintf("%.2f", $val['money']);
            $val['pay_money'] = sprintf("%.2f", $val['pay_money']);
            $val['pending_money'] = sprintf("%.2f", $val['money'] - $val['pay_money']);
            $val['pay_status_txt'] = $this->pay_status[$val['pay_status']];
        }
        filter_fk_name($data['data'], array('custom_code|custom'));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 获取主单据信息
     * @param string $record_code 单据号
     * @return array 数据集
     */
    public function get_record($record_code) {
        $sql = 'SELECT sr.`store_out_record_id`,sr.`record_code`,sr.`distributor_code` AS custom_code,sr.`num`,sr.`money`,sr.`pay_money`,sr.`pay_status`,sr.`remark` FROM wbm_store_out_record AS sr WHERE record_code=:record_code';

        $data = $this->db->get_row($sql, array(':record_code' => $record_code));
        /*$data['money'] = sprintf("%.2f", $data['money']);
        $data['pay_money'] = sprintf("%.2f", $data['pay_money']);*/
        $data['pending_money'] = bcsub($data['money'],$data['pay_money'],4);
        $data['custom_name'] = get_custom_name_by_code($data['custom_code']);

        return $data;
    }

    public function add_record($data) {
        $this->begin_trans();
        $money = 0;
        $money +=empty($data['online_yck_money']) ? 0 : $data['online_yck_money'];
        $money +=empty($data['offline_money']) ? 0 : $data['offline_money'];
        if ($money > 0) {
            $ret_money = $this->check_wbm_record_status($data['record_code']);
            if ($ret_money['status'] != 1) {
                $this->rollback();
                return $ret_money;
            }
            $ret_money = $ret_money['data'];
            $money_diff = $ret_money['pay_money'] + $money;
            if ($money_diff > $ret_money['money']) {
                $this->rollback();
                return $this->format_ret(-1, '', '收款金额超过单据未付金额');
            }
            $pay_status = $money_diff == $ret_money['money'] ? 2 : 1;
            $ret = load_model('fx/BalanceOfPaymentsModel')->update_wbm_store_out($money_diff, $pay_status, $data['record_code']);
            if ($ret != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '收款失败');
            }
        } else {
            $this->rollback();
            return $this->format_ret(-1, '', '收款金额必须为大于0的数字');
        }

        $money = 0;
        $common = array();
        $common['record_code'] = $data['record_code'];
        $common['custom_code'] = $data['custom_code'];
        $common['detail_type'] = 1;
        $common['record_type'] = 0;
        $common['abstract'] = 'order_pay';

        if (!empty($data['online_yck_money'])) {
            $online = array();
            $online['money'] = $data['online_yck_money'];
            $online['capital_type'] = 0;
            $online['capital_account'] = 'yck';
            $online['income_type'] = 2;
            $online = array_merge($online, $common);
            $ret = load_model('fx/AccountModel')->opt_balance($online);
            if ($ret['status'] != 1 || $this->affected_rows() != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '添加失败');
            }

            unset($online);
        }
        if (!empty($data['offline_money'])) {
            $offline = array();
            $offline['money'] = $data['offline_money'];
            $offline['income_account'] = empty($data['offline_account_code']) ? '' : $data['offline_account_code'];
            $offline['record_time'] = $data['offline_pay_time'];
            $offline['remark'] = $data['offline_remark'];
            $offline['pay_type_code'] = 'bank';
            $offline['income_type'] = 1;
            $offline['img_url'] = $data['img_url'];
            $offline['thumb_img_url'] = $data['thumb_img_url'];
            $offline = array_merge($offline, $common);
            $ret = load_model('fx/BalanceOfPaymentsModel')->add_detail($offline);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '添加失败');
            }

            unset($offline);
        }

        $this->commit();
        return $this->format_ret(1, '', '添加成功');
    }

    /**
     * 检查批发销货单状态
     * @param string $record_code 单据号
     * @return array 检查结果
     */
    private function check_wbm_record_status($record_code) {
        $sql = "SELECT is_cancel,is_store_out,pay_status,money,pay_money FROM wbm_store_out_record WHERE record_code = :record_code";
        $wbm_record_data = $this->db->get_row($sql, array(':record_code' => $record_code));
        if ($wbm_record_data['is_cancel'] == 1) {
            return $this->format_ret(-1, '', '该单据已作废，不能添加收款记录');
        }
        if ($wbm_record_data['is_store_out'] != 1) {
            return $this->format_ret(-1, '', '该单据未出库，不能添加收款记录');
        }
        if ($wbm_record_data['pay_status'] == 2) {
            return $this->format_ret(-1, '', '该单据已完成付款，不能添加收款记录');
        }
        return $this->format_ret(1, $wbm_record_data);
    }

}
