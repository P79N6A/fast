<?php

require_lib('apiclient/AlipaymClient', true);
/**
 * 资金帐户相关业务
 */
require_model('tb/TbModel');
require_lang('base');

class AccountModel extends TbModel {

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_join = "";
        $sql_main = " FROM base_custom rl $sql_join WHERE 1";
        $sql_values = array();
        //未审核的不显示
        $sql = "SELECT user_code FROM sys_user WHERE (status = 2 OR status = 0) AND login_type = 2;";
        $user_code = $this->db->get_all_col($sql);
        if (!empty($user_code)) {
            $user_str = $this->arr_to_in_sql_value($user_code, 'user_code', $sql_values);
            $sql_main .= " AND (rl.user_code NOT IN ({$user_str}) OR rl.user_code is null) ";
        }
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) {
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            if (!empty($custom['custom_code'])) {
                $sql_main .= " AND rl.custom_code = :custom_code";
                $sql_values[':custom_code'] = $custom['custom_code'];
            } else {
                $sql_main .= " AND 1 != 1";
            }
        } else {
            if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
                $custom_arr = explode(",", $filter['custom_code']);
                $custom_str = $this->arr_to_in_sql_value($custom_arr,'custom_code',$sql_values);
                $sql_main .= " AND rl.custom_code in ({$custom_str})";
            }
        }


        //创建时间
        if (isset($filter['create_time_start']) && $filter['create_time_start'] != '') {
            $sql_main .= " AND (rl.create_time >= :create_time_start )";
            $sql_values[':create_time_start'] = $filter['create_time_start'] . ' 00:00:00';
        }
        if (isset($filter['create_time_end']) && $filter['create_time_end'] != '') {
            $sql_main .= " AND (rl.create_time <= :create_time_end )";
            $sql_values[':create_time_end'] = $filter['create_time_end'] . ' 23:59:59';
        }

        $select = 'rl.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['create_time'] = $val['create_time'] == '0000-00-00 00:00:00' ? '' : $val['create_time'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_ali_key() {
        $sql = "SELECT * FROM alipay_key WHERE `status` = 1";
        $ret = $this->db->get_row($sql);
        return $ret;
    }
    
    function alipay_do_edit($key, $pid) {
        $sql = "SELECT `id`,`pid`,`key` FROM alipay_key WHERE `status` = 1";
        $ret = $this->db->get_row($sql);
        if (empty($ret['id'])) {
            $r = $this->insert_exp('alipay_key',array('pid'=>$pid, 'key'=>$key, 'status'=>'1'));
            $operate_type = '新增';
            $operate_xq = '新增支付宝收款账户参数';
        } else {
            $r = $this->update_exp('alipay_key',array('pid'=>$pid, 'key'=>$key, 'status'=>'1'));
            $operate_type = '编辑';
            $operate_xq = '修改支付宝收款账户参数';//操作详情
        }
        if ($r['status']==1) {
            $yw_code = ''; //业务编码          
            $module = '账务'; //模块名称
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
        }
        return $r;
    }

    //支付宝支付
    function alipay_recharge($data) {
        $out_trade_no = date('YmdHis') . mt_rand(000000, 999999);
        $alipay = $this->get_by_ali_key();
        $p = new AlipaymClient($alipay['pid'], $alipay['key']);
        //获取当前url
        $get_url = $this->get_url();
        $return_url = $get_url . 'return_url.php';
        $notify_url = $get_url . 'notify_url.php';
        $param = array(
            'out_trade_no' => $out_trade_no,
            'subject' => '支付宝充值',
            'total_fee' => $data['money'],
            'payment_type' => 1,
            'body' => '支付宝充值',
            'return_url' => $return_url,
            'notify_url' => $notify_url,
        );
        $ret = $p->create_direct_pay_by_user($param);
        if (!empty($ret)) {
            $data['pra_out_trade_no'] = $out_trade_no;
            //添加支付宝流水信息
            $params = array(
                'pra_out_trade_no' => $out_trade_no,
                'income_serial_number' => $data['serial_number'],
                'pra_total_fee' => $data['money'],
                'pay_begin_time' => time()          
            );
            $order_info = $this->insert_exp('fx_income_pay_serial', $params);
            //添加分销商支付中间表信息
            $data['record_time'] = strtotime($data['record_time']);
            $data['operator'] = CTX()->get_session('user_name');
            $this->insert_exp('fx_pay_temporary', $data);
            
        }
        $this->set_out_trade_no_cache($out_trade_no);

        return $ret;
    }

    function set_out_trade_no_cache($out_trade_no) {
        $m = app_get_cache('cache_pub');
        $key = $this->set_out_trade_no_cache_key($out_trade_no);
        $kh_id = CTX()->saas->get_saas_key();
        $m->set($key, $kh_id, 3600);
        return ;
    }

    private function set_out_trade_no_cache_key($out_trade_no) {
        return 'out_trade_no_cache' . md5($out_trade_no);
    }

    function get_out_trade_no_cache($out_trade_no) {
        $m = app_get_cache('cache_pub');
        $key = $this->set_out_trade_no_cache_key($out_trade_no);
        $kh_id = $m->get($key);
        return $kh_id;
    }

    //列表支付宝支付
    function do_list_ali_pay($data) {
        $sql = "SELECT pra_out_trade_no FROM fx_account_ali_pay WHERE account_code = '{$data['account_code']}'";
        $pra_out_trade_no = $this->db->get_row($sql);
        if (!empty($pra_out_trade_no)) {
            $data['pra_out_trade_no'] = $pra_out_trade_no['pra_out_trade_no'];
        }
        if ((int) $data['account_money'] == $data['account_money']) {
            $data['account_money'] = (int) $data['account_money'];
        } else {
            $data['account_money'] = rtrim($data['account_money'], 0);
        }
        $url = $this->alipay_recharge($data);
        if (empty($url)) {
            return $this->format_ret(-1, '', '路径不存在');
        }
        return $this->format_ret(2, $url, '');
    }

    //验证充值状态
    function check_pay_status($serial_number) {
        $sql = "SELECT * FROM fx_income_pay_serial WHERE income_serial_number = :income_serial_number";
        $account_ali_pay = $this->db->get_row($sql,array(':income_serial_number' => $serial_number));
        if ($account_ali_pay['pay_status'] == 1) {
            //更新收支明细，维护分销商预存款金额
            return $this->format_ret('1', '', '充值成功');
        }
        return $this->format_ret('-1', '', '充值失败');
    }
    
    function fx_pay_insert_income($params) { 
	$params['detail_type'] = 0;
        if (!is_numeric($params['money']) || $params['money'] <= 0) {
            return $this->format_ret(-1, '', '金额有误');
        }
        //计算账户余额
        $balance_money = $this->calculate_money($params);
        if ($balance_money['status'] != 1) {
            return $balance_money;
        }
        $params['balance_money'] = $balance_money['data'];
        $msg = $params['capital_type'] == 1 ? '充值' : '扣款';

        $this->begin_trans();
        //更新分销商账户余额
        $money_data = get_array_vars($params, array('capital_type', 'capital_account', 'money'));
        $money_data['distributor'] = $params['custom_code'];
        $update_no = load_model('base/CustomModel')->update_money($money_data);
        if ($update_no != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', $msg . '失败');
        }
        //添加资金收支明细
        $ret = load_model('fx/BalanceOfPaymentsModel')->add_detail($params,'ali_pay');
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', $msg . '失败');
        }

        $this->commit();
        return $this->format_ret(1, '', $msg . '成功');
        
    }

    //更新支付状态
    function handle_info($filter) {
        //根据支付宝交易号查询资金账户明细信息（中间表）
        $sql = "SELECT serial_number,custom_code,money,record_time,remark,pay_type_code,capital_account,capital_type,abstract,operator FROM fx_pay_temporary WHERE pra_out_trade_no = :pra_out_trade_no";
        $fx_income_data = $this->db->get_row($sql,array(':pra_out_trade_no' => $filter['out_trade_no']));
        //时间戳类型转化
        $fx_income_data['record_time'] = date('Y-m-d H:i:s', $fx_income_data['record_time']);
        $this->begin_trans();
        try {
            //支付宝可能多次回传,校验是否已支付,已支付则不再继续操作
            $sql = 'SELECT COUNT(1) FROM fx_income_pay_serial WHERE pra_out_trade_no=:_trade_no AND pay_status=1';
            $check_is_up = $this->db->get_value($sql,[':_trade_no' => $filter['out_trade_no']]);
            if($check_is_up > 0){
                return $this->format_ret(1,'','已付款');
            }
            $notify_time = strtotime($filter['notify_time']);
            $arr = array(
                'alipay_trade_no' => $filter['trade_no'],
                'buyer_email' => $filter['buyer_email'],
                'seller_email' => $filter['seller_email'],
                'pay_status' => 1,
                'pay_time' => $notify_time
            );
            parent::update_exp('fx_income_pay_serial', $arr, array('pra_out_trade_no' => $filter['out_trade_no']));
            $affect = $this->affected_rows();
            if ($affect != 1) {
                $this->rollback();
                return $this->format_ret('-1', '', '修改失败');
            }
            //新增资金账户
            $this->fx_pay_insert_income($fx_income_data);
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '保存失败:' . $e->getMessage());
        }
    }

    //获取当前URL路径
    function get_url() {
        $i = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $w = strstr($i, '/web/', true);
//        $w = strirpos($i, 'web');
//        $str = substr($i, 0,$w);
        $str = 'http://' . $w . '/web/';
        return $str;
    }

    //调用配置文件
    /* function require_conf($conf) {
      static $conf_data = array();
      $key = md5($conf);
      if (isset($conf_data[$key]) && !empty($conf_data[$key])) {
      return $conf_data[$key];
      }
      $conf_file = ROOT_PATH . CTX()->app_name . "/conf/{$conf}.conf.php";
      if (file_exists($conf_file)) {
      $conf_data[$key] = require $conf_file;
      } else {
      $conf_data[$key] = array();
      CTX()->log_error("require_conf fail,[{$conf_file}] not found");
      }
      return $conf_data[$key];
      } */

    /**
     * @param $code
     * @return array
     */
    function get_by_code($code) {
        return $this->get_row(array('custom_code' => $code));
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*") {

        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    public function get_s1ettlement_method() {
        $settlement_method = $this->settlement_method;
        $new_settlement_method = array();
        $i = 0;
        foreach ($settlement_method as $key => $method) {
            $new_settlement_method[$i]['settlement_method_code'] = $key;
            $new_settlement_method[$i]['settlement_method_name'] = $method;
            $i ++;
        }
        return $new_settlement_method;
    }

    public function get_custom_type() {
        $custom_type = $this->custom_type;
        $new_custom_type = array();
        $i = 0;
        foreach ($custom_type as $key => $type) {
            $new_custom_type[$i]['settlement_method_code'] = $key;
            $new_custom_type[$i]['settlement_method_name'] = $type;
            $i ++;
        }
        return $new_custom_type;
    }

    /**
     * 获取各个账户的余额
     * @param $code
     * @return array
     */
    function get_by_remainder($code, $select = '*') {
        $sql = "SELECT {$select} FROM base_custom WHERE custom_code = :custom_code";
        $custom_data = $this->db->get_row($sql, array(':custom_code' => $code));
        return $custom_data;
    }

    /**
     * 充值/扣款
     * @param array $params array('custom_code','detail_type','capital_type','capital_account','money','record_time','abstract','remark','record_code')
     * @return array 操作结果
     */
    function opt_balance($params) {
        if (!is_numeric($params['money']) || $params['money'] <= 0) {
            return $this->format_ret(-1, '', '金额有误');
        }

        $params['pay_type_code'] = $params['capital_type'] == 1 ? 'bank' : 'balance';
        //计算账户余额
        $balance_money = $this->calculate_money($params);
        if ($balance_money['status'] != 1) {
            return $balance_money;
        }
        $params['balance_money'] = $balance_money['data'];

        $msg = $params['capital_type'] == 1 ? '充值' : '扣款';

        $this->begin_trans();
        //更新分销商账户余额
        $money_data = get_array_vars($params, array('capital_type', 'capital_account', 'money'));
        $money_data['distributor'] = $params['custom_code'];
        $update_no = load_model('base/CustomModel')->update_money($money_data);
        if ($update_no != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', $msg . '失败');
        }
        //添加资金收支明细
        $ret = load_model('fx/BalanceOfPaymentsModel')->add_detail($params);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', $msg . '失败');
        }

        $this->commit();
        return $this->format_ret(1, '', $msg . '成功');
    }
    
    /**
     * 分销商支付宝充值
     */
    function fx_ali_pay($params) {
        $url = $this->alipay_recharge($params);
        $data['url'] = $url;
        $data['serial_number'] = $params['serial_number'];
        return $this->format_ret('2',$data,'');
    }
    /**
     * 计算最终金额
     */
    private function calculate_money($params) {
        $custom_data = $this->get_by_remainder($params['custom_code'], 'yck_account_capital,arrears_money');
        if (empty($custom_data)) {
            return $this->format_ret(-1, '', '分销商不存在');
        }
        if (($custom_data['yck_account_capital'] + $custom_data['arrears_money']) < $params['money'] && $params['capital_type'] == 0) {
            $difference_money = $params['money'] - ($custom_data['yck_account_capital'] + $custom_data['arrears_money']);
            $message = "预存款账户余额不足<br>账户余额：".$custom_data['yck_account_capital']."元<br>欠款额度：".$custom_data['arrears_money']."元<br>相差金额：".$difference_money."元";
            return $this->format_ret(-1, '' ,$message);
        }
        $char = $params['capital_type'] == 1 ? '+' : '-';
        $balance_money = 0;
        if ($params['capital_account'] == 'yck') {
            $str = "\$balance_money ={$custom_data['yck_account_capital']}{$char}{$params['money']};";
            eval($str);
        }

        return $this->format_ret(1, $balance_money);
    }
    //更新欠款
    function update_arrears($data) {
        $ret = load_model('base/CustomModel')->update_arrears($data['custom_code'],$data['arrears_money']);
        return $ret;
    }
}
