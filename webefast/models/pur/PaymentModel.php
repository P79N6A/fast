<?php

/**
 * 付款明细相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');

class PaymentModel extends TbModel {

    function get_table() {
        return 'pur_payment';
    }
    private $status_arr = array(
        0 => '',
        1 => '已付款',
        2 => '已作废'
    );
            
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_main = " FROM pur_payment AS rl WHERE 1 = 1 ";
        //供应商
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $sql_main .= load_model('base/SupplierModel')->get_sql_purview_supplier('rl.supplier_code', $filter_supplier_code);
        //单据编号
        if(isset($filter['record_code']) && $filter['record_code'] != '') {
            if(strpos($filter['record_code'], 'JH') !== false ) {
                $sql_main .= " AND rl.purchaser_record_code LIKE :record_code ";
                $sql_values[':record_code'] = "%" . $filter['record_code'] . "%";
            } else {
                $sql_main .= " AND rl.planned_record_code LIKE :record_code ";
                $sql_values[':record_code'] = "%" . $filter['record_code'] . "%";
            }
        }
        //流水号
        if(isset($filter['serial_number']) && $filter['serial_number'] != '') {
            $sql_main .= " AND rl.serial_number LIKE :serial_number ";
            $sql_values[':serial_number'] = "%" . $filter['serial_number'] . "%";
        }
        //付款时间
        if (isset($filter['payment_time_start']) && $filter['payment_time_start'] != '') {
            $filter['payment_time_start'] = strtotime($filter['payment_time_start']);
            $sql_main .= " AND (rl.payment_time >= :payment_time_start )";
            $sql_values[':payment_time_start'] = $filter['payment_time_start'];
        }
        if (isset($filter['payment_time_end']) && $filter['payment_time_end'] != '') {
            $filter['payment_time_end'] = strtotime($filter['payment_time_end']);
            $sql_main .= " AND (rl.payment_time < :payment_time_end )";
            $sql_values[':payment_time_end'] = $filter['payment_time_end'];
        }
        $select = "rl.*";
        $sql_main .= " ORDER BY rl.create_time DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => &$val) {
            //查询供应商名称
            $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier', 'supplier_code', $val['supplier_code'], 'supplier_name');
            $val['supplier_name'] = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
            $val['status_str'] = $this->status_arr[$val['status']];
            $val['payment_time'] = date('Y-m-d H:i:s',$val['payment_time']);
            $val['record_code'] = $val['detail_type'] == 1 ? $val['purchaser_record_code'] : $val['planned_record_code'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //添加付款明细
    function add_payment($params) {
        $data = array();
        $pur_data = array();
        $status = 1; //已付款
        //获取当前用户
        $operator = CTX()->get_session('user_code');
//        $pay_type_code = '线下转账';
        $abstract = '订单付款';
        $pay_time = strtotime($params['pay_time']);
        foreach ($params['record_data'] as $value) {
            //获取流水号
            $serial_number = $this->create_fast_bill_sn();
            $data[] = array(
                'serial_number' => $serial_number,
                'planned_record_code' => isset($value['planned_record_code']) && !empty($value['planned_record_code']) ? $value['planned_record_code'] : '',
                'purchaser_record_code' => isset($value['purchaser_record_code']) && !empty($value['purchaser_record_code']) ? $value['purchaser_record_code'] : '',
                'status' => $status,
                'supplier_code' => $params['supplier_code'],
                'payment_time' => $pay_time,
                'money' => $value['current_payment_money'],
                'abstract' => $abstract,
                'remark' => str_replace(array("\r\n", "\r", "\n"), '', $params['remark']),
                'operator' => $operator,
                'create_time' => time(),
                'detail_type' => isset($value['record_type']) && $value['record_type'] == 'purchaser' ? 1 : 2
            );
            $pur_data[] = array(
                'payment_money' => $value['current_payment_money'],
                'record_code' => isset($value['record_type']) && $value['record_type'] == 'purchaser' ? $value['purchaser_record_code'] : $value['planned_record_code'],
                'record_type' => isset($value['record_type']) && !empty($value['record_type']) ? $value['record_type'] : ''
            );
        }
        $update_str = ' money=VALUES(money) ';
        $this->begin_trans();
        $ret = $this->insert_multi_duplicate('pur_payment', $data, $update_str);
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1,'','添加失败');
        }
        //回写付款金额和状态
        $ret = $this->back_to_write_amount($pur_data);
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1,'','回写付款金额失败');
        }
                
        $this->commit();
        return $this->format_ret(1,'','添加成功');
    }

    /**
     * 生成流水号
     */
    function create_fast_bill_sn() {
        $sql = "select id  from pur_payment order by id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $i = mt_rand(0000, 9999);
        $jdh = date('ymdHis') . $i . $djh;
        return $jdh;
    }
    /**
     * 回写数量和金额
     */
    function back_to_write_amount($pur_data) {
        $err = 0;
        foreach ($pur_data as $val) {
            if(empty($val['record_type'])) {
                ++$err;
                continue;
            }
            //判断是订单还是入库单
            if($val['record_type'] == 'purchaser') {
                $ret = $this->back_purchaser($val);
            } else if ($val['record_type'] == 'planned') {
                $ret = $this->back_planned($val);
            } else {
                ++$err;
            }
            if($ret['status'] < 0){
                return $ret;
            }
        }
        if($err > 0) {
            return $this->format_ret(1,'','回写采购订单或采购入库单失败');
        } else {
            return $this->format_ret(1,'','回写成功');
        }
    }
    /**
     * 回写入库单
     */
    function back_purchaser($data,$type = 'payment') {
        $purchaser_data = load_model('pur/PurchaseRecordModel')->get_by_field('record_code',$data['record_code']);
        if($purchaser_data['status'] < 0) {
            return $this->format_ret(-1,'','入库数据为空');
        }
        //付款金额
        $payment_money = 0;
        if($type == 'payment') {
            $payment_money = $purchaser_data['data']['payment_money'] + $data['payment_money'];            
        } else if($type == 'cancellation') {
            $payment_money = $purchaser_data['data']['payment_money'] - $data['payment_money'];   
        }
        $is_payment = 0;
        //付款状态
        if($payment_money == $purchaser_data['data']['money'] && $payment_money != 0) {
            $is_payment = 1;
        } else if($payment_money < $purchaser_data['data']['money']) {
            $is_payment = 2; 
        } if($payment_money == 0){
            $is_payment = 0; 
        }
        //回写入库单付款金额
        $payment_money = ($payment_money < 0) ? 0 : $payment_money;
        $update_sql = "UPDATE pur_purchaser_record SET is_payment = :is_payment, payment_money = :payment_money WHERE record_code = :record_code AND money>={$payment_money}";
        $values = array(':is_payment' => $is_payment,':payment_money' => $payment_money,':record_code' => $data['record_code']);
        $ret = $this->query($update_sql, $values);
        $rows = $this->affected_rows();
        if ($ret['status'] < 0 || $rows == 0) {
            return $this->format_ret(-1, '', '回写入库单付款金额失败');
        } else {
            return $this->format_ret(1, '', '回写入库单付款金额成功');
        }
    }  
    /**
     * 回写采购订单付款金额
     * @param type $data 订单信息
     * @param type $type 付款类型
     */
    function back_planned($data,$type = 'payment') {
        $ret = load_model('pur/PlannedRecordModel')->get_by_code($data['record_code']);
        if($ret['status'] < 0) {
            return $this->format_ret(-1,'','入库数据为空');
        }
        $planned_data = $ret['data'];
        //付款金额
        $payment_money = 0;
        if($type == 'payment') {
            $payment_money = $planned_data['payment_money'] + $data['payment_money'];            
        } else if($type == 'cancellation') {
            $payment_money = $planned_data['payment_money'] - $data['payment_money'];   
        }
        $is_payment = 0;
        //付款状态
        if($payment_money == $planned_data['money'] && $payment_money != 0) {
            $is_payment = 1;
        } else if($payment_money < $planned_data['money']) {
            $is_payment = 2; 
        } if($payment_money == 0){
            $is_payment = 0; 
        }
        //回写订单单付款金额
        $update_sql = "UPDATE pur_planned_record SET is_payment = :is_payment, payment_money = :payment_money WHERE record_code = :record_code";
        $values = array(':is_payment' => $is_payment,':payment_money' => $payment_money,':record_code' => $data['record_code']);
        $ret = $this->query($update_sql, $values);
        if($ret['status'] < 0) {
            return $this->format_ret(-1,'','回写采购订单付款金额失败');
        } else {
            return $this->format_ret(1,'','回写采购订单付款金额成功');
        }
    }
    /**
     * 查询单据的流水记录
     * @param type $record
     */
    function get_by_page_record($record) {
        $filter = array('record_code' => $record);
        $data = $this->get_by_page($filter);
        return $data['data']['data'];
    }
    /**
     * 查询付款明细信息
     * @param type $serial_number 流水号 
     */
    function get_by_code($serial_number,$select = '*') {
        $sql = "SELECT {$select} FROM pur_payment WHERE serial_number = :serial_number";
        $data = $this->db->get_row($sql,array(':serial_number' => $serial_number));
        return $data;
    }
    /*
     * 作废付款单
     * @param type $serial_number 流水号
     */
    function do_cancellation($serial_number){
        $payment_data = $this->get_by_code($serial_number);
        if(empty($payment_data)) {
            return $this->format_ret(-1,'','付款记录不存在');
        }
        $this->begin_trans();
        //作废流水
        $ret = $this->update(array('status' => 2), array('serial_number' => $serial_number, 'status' => 1));
        $rows = $this->affected_rows();
        if($ret['status'] < 0 || $rows != 1) {
            $this->rollback();
            return $this->format_ret(-1,'', '作废流水失败，状态可能已变更！');
        }
        //回写采购入库单金额
        $data = array(
            'record_code' => $payment_data['detail_type'] == 1 ? $payment_data['purchaser_record_code'] : $payment_data['planned_record_code'],
            'payment_money' => $payment_data['money']
        );
        if($payment_data['detail_type'] == 1) {
            $ret = $this->back_purchaser($data,'cancellation');
        } else if ($payment_data['detail_type'] == 2){
            $ret = $this->back_planned($data,'cancellation');
        } else {
            $this->rollback();
            return $this->format_ret(-1,'','流水信息异常');
        }
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1,'','作废流水失败');
        }
        $this->commit();
        return $this->format_ret(1,'','操作成功');
    }
    function do_delete($serial_number) {
        $payment_data = $this->get_by_code($serial_number);
        if(empty($payment_data)) {
            return $this->format_ret(-1,'','付款记录不存在');
        }
        if($payment_data['status'] == 1) {
            return $this->format_ret(-1,'','订单是已付款状态不能删除');
        }
        $ret = $this->delete(array('serial_number' => $serial_number));
        return $ret;
    }
    /**
     * 付款统计
     * @param type $filter
     */
    function payment_statistics($filter) {
        $sql_join_pur = '';
        $sql_join_pla = '';
        $where = '';
        $sql_values = array();
        $sql_val = array();
        //付款时间 维护时间段内付款金额
        if (isset($filter['pay_time_start']) && $filter['pay_time_start'] != '') {
            $is_jon = TRUE;
            $pay_time_start = strtotime($filter['pay_time_start']);
            $where .= " AND pt.payment_time>=:pay_time_start";
            $sql_val[':pay_time_start'] = $pay_time_start;
        }
        if (isset($filter['pay_time_end']) && $filter['pay_time_end'] != '') {
            $is_jon = TRUE;
            $pay_time_end = strtotime($filter['pay_time_end']);
            $where .= " AND pt.payment_time<=:pay_time_end";
            $sql_val[':pay_time_end'] = $pay_time_end;
        }
        //维护时间段内付款金额
        if ($is_jon === TRUE) {
            $pay_time_pla = "SELECT ppr.record_code,ppr.supplier_code,sum(pt.money) AS pay_time_money FROM pur_payment pt INNER JOIN pur_planned_record ppr ON pt.planned_record_code = ppr.record_code WHERE pt.`status` = 1 AND ppr.is_notify_payment = 1 {$where} ";
            $pay_time_pur = "SELECT pr.record_code,pr.supplier_code,sum(pt.money) AS pay_time_money FROM pur_payment pt INNER JOIN pur_purchaser_record pr ON pt.purchaser_record_code = pr.record_code WHERE pt.`status` = 1 AND pr.is_notify_payment <> 1 AND pr.is_check_and_accept = 1 {$where} ";
        }
        $sql_planned = "SELECT ppr.record_code,sum(ppr.money) AS record_sum_money,ppr.supplier_code,sum(ppr.payment_money) AS pay_sum_money FROM pur_planned_record AS ppr WHERE ppr.is_notify_payment = 1 ";
        $sql_purchaser = "SELECT pr.record_code,sum(pr.money) AS record_sum_money,pr.supplier_code,sum(pr.payment_money) AS pay_sum_money FROM pur_purchaser_record AS pr WHERE pr.is_notify_payment <> 1 AND pr.is_check_and_accept = 1 ";       
        //供应商
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $sql_purchaser .= load_model('base/SupplierModel')->get_sql_purview_supplier('pr.supplier_code', $filter_supplier_code);
        $sql_planned .= load_model('base/SupplierModel')->get_sql_purview_supplier('ppr.supplier_code', $filter_supplier_code);
        //供应商  维护时间段内付款金额
        if (!empty($filter_supplier_code) && isset($filter['pay_time_start']) && $is_jon === TRUE) {
            $pay_time_pur .= load_model('base/SupplierModel')->get_sql_purview_supplier('pr.supplier_code', $filter_supplier_code);
            $pay_time_pla .= load_model('base/SupplierModel')->get_sql_purview_supplier('ppr.supplier_code', $filter_supplier_code);
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_purchaser .= " AND pr.record_code LIKE :record_code ";
            $sql_planned .= " AND ppr.record_code LIKE :record_code ";
            $sql_values[':record_code'] = "%" . $filter['record_code'] . "%";
        }
        //单据编号 维护时间段内付款金额
        if (isset($filter['record_code']) && $filter['record_code'] != '' && $is_jon === TRUE) {
            $pay_time_pur .= " AND pr.record_code LIKE :record_code ";
            $pay_time_pla .= " AND ppr.record_code LIKE :record_code ";
            $sql_val[':record_code'] = "%" . $filter['record_code'] . "%";
        }
        // 付款状态
        if (isset($filter['is_payment']) && $filter['is_payment'] != '') {
            $is_payment_arr = explode(",", $filter['is_payment']);
            $is_payment_str = $this->arr_to_in_sql_value($is_payment_arr,'is_payment',$sql_values);
            $sql_purchaser .= " AND pr.is_payment in ({$is_payment_str}) ";
            $sql_planned .= " AND ppr.is_payment in ({$is_payment_str}) ";
        }
        // 付款状态 维护时间段内付款金额
        if (isset($filter['is_payment']) && $filter['is_payment'] != '' && $is_jon === TRUE) {
            $is_payment_arr = explode(",", $filter['is_payment']);
            $is_payment_str = $this->arr_to_in_sql_value($is_payment_arr,'is_payment',$sql_val);
            $pay_time_pur .= " AND pr.is_payment in ({$is_payment_str}) ";
            $pay_time_pla .= " AND ppr.is_payment in ({$is_payment_str}) ";
        }
        
        $sql_purchaser .= " GROUP BY pr.supplier_code ";
        $sql_planned .= " GROUP BY ppr.supplier_code ";
        $sql_main = "FROM (({$sql_planned}) UNION ALL ({$sql_purchaser})) AS rl WHERE 1 ";
        if($filter['list_type'] == 'sum_money') {
            $ret_1 = array('sql' => $sql_main , 'values' => $sql_values);
        }
        $sql_main .= " GROUP BY rl.supplier_code ";
        $select = " sum(rl.record_sum_money) AS record_sum_money,sum(rl.pay_sum_money) AS pay_sum_money,rl.supplier_code,rl.record_code ";
//        var_dump($sql_main,$select,$sql_values);die;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //维护时间段内付款金额
        if ($is_jon === TRUE) {
            $pay_time_pur .= " GROUP BY pr.record_code ";
            $pay_time_pla .= " GROUP BY ppr.record_code ";
            $sql_main_1 = "FROM (({$pay_time_pla}) UNION ALL ({$pay_time_pur})) AS rl WHERE 1 ";           
            $sql_main_1 .= " GROUP BY rl.supplier_code ";
            $select_1 = " SUM(rl.pay_time_money) AS pay_sum_paytime_money,rl.supplier_code,rl.record_code ";
            $data_pay_time = $this->get_page_from_sql($filter, $sql_main_1, $sql_val, $select_1);
            foreach ($data_pay_time['data'] as $value) {
                $sum_pay_time_money[$value['supplier_code']] = $value['pay_sum_paytime_money'];
            }
            if($filter['list_type'] == 'sum_money') {
                $ret = array('pay_sum_paytime_money' => $data_pay_time['data']);
            }
        }
        if ($filter['list_type'] == 'sum_money') {
            return empty($ret) ? $ret_1 : array_merge($ret_1, $ret);
        }
        foreach ($data['data'] as &$val) {//var_dump($sum_pay_time_money[$val['supplier_code']]);
            $val['record_sum_money'] = empty($val['record_sum_money']) ? 0 : $val['record_sum_money'];
            $val['pay_sum_money'] = empty($val['pay_sum_money']) ? 0 : $val['pay_sum_money'];
            $val['money_sum_payable'] = (float)$val['record_sum_money'] - $val['pay_sum_money'];
            $val['money_sum_payable'] = sprintf('%.3f',$val['money_sum_payable']);
            //查询供应商名称
            $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier', 'supplier_code', $val['supplier_code'], 'supplier_name');
            $val['supplier_name'] = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
            if ($is_jon === TRUE) {
                $val['pay_sum_paytime_money'] = !empty($sum_pay_time_money[$val['supplier_code']]) ? $sum_pay_time_money[$val['supplier_code']] : 0;
            }else{
                $val['pay_sum_paytime_money'] = $val['pay_sum_money'];
            }
        }
        
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    function payment_count($filter) {
        $filter['list_type'] = 'sum_money';
        $ret = $this->payment_statistics($filter);
        $sql = "SELECT sum(rl.record_sum_money) AS record_sum_money,sum(rl.pay_sum_money) AS pay_sum_money " . $ret['sql'];
        $data = $this->db->get_row($sql, $ret['values']);
        $money_sum_payable = $data['record_sum_money'] - $data['pay_sum_money'];
        //时间段内付款总金额
        $pay_paytime_money = '';
        if ((!empty($filter['pay_time_start']) || !empty($filter['pay_time_end']))) {
            if (empty($ret['pay_sum_paytime_money']) ) {
                $pay_paytime_money = 0;
            }else{
                foreach ($ret['pay_sum_paytime_money'] as $value) {
                    $pay_paytime_money += $value['pay_sum_paytime_money'];
                }
            }           
        }
        $pay_sum_paytime_money = $pay_paytime_money === '' ? $data['pay_sum_money'] : $pay_paytime_money;
      //  $money_sum_payable = sprintf('%.3f',$$money_sum_payable);
        $str = "<span>待付金额：{$money_sum_payable}</span><span style='margin-left: 5%;'>订单金额：{$data['record_sum_money']}</span><span style='margin-left: 5%;'>已付金额：{$data['pay_sum_money']}</span><span style='margin-left: 5%;'>时间段内付款总金额：{$pay_sum_paytime_money}</span>";
        $str .= '<img height="23" width="23" data-align="top-right" class="tip" src="assets/images/tip.png" title="为付款时间在查询条件“付款时间”所设定的时间段内的付款总金额。若查询条件“付款时间”为空，则此字段值应与已付总金额相等" />';
        return $this->format_ret(1,$str,'');
    }
    /**
     * 采购订单或入库单号，查询未作废的付款记录
     * @param type $code
     * @param type $detail_type 生成类型（订单、入库单）
     */
    function get_planned_or_purchaser_code($code,$detail_type = '2') {
        $where = $detail_type == '2' ? 'planned_record_code' : 'purchaser_record_code';
        $sql = "SELECT * FROM pur_payment WHERE status != 2 AND {$where} = :{$where} AND detail_type = :detail_type ";
        $data = $this->db->get_all($sql,array(':'.$where => $code, ':detail_type' => $detail_type));
        return $data;
    }
}
