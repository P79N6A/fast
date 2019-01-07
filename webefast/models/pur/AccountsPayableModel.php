<?php

/**
 * 待付款相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');

class AccountsPayableModel extends TbModel {
    private $is_parment_arr = array(
        0 => '未付款',
        1 => '已付款',
        2 => '部分付款'
    );
    
    //更新 备注信息
    function update_remark($filter ,$remark,$type,$record_code){
        if (empty($remark)) {
            return $this->format_ret(-1, '备注信息不能为空！');
        }elseif (empty($filter['data'][0]['remark'])) {
            $remark = $remark;
        }else{
             $remark = $filter['data'][0]['remark'].'；'.$remark;
        }             
        $ret = $this->update_exp("pur_{$type}_record", array('remark'=>$remark), array('record_code'=>$record_code));
        return $ret;
    }
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        //查询批发入库单已入库未付款未通知付款的订单
        $sql_purchaser = " FROM
((SELECT 'planned' as record_type,record_code AS planned_record_code,purchaser_record_code,supplier_code,money AS record_money,payment_money,num AS finish_num,is_payment,in_time AS enter_time,is_add_time AS create_time,remark FROM pur_planned_record WHERE is_notify_payment = 1 AND is_payment <> 1)
UNION ALL
(SELECT 'purchaser' as record_type,planned_record_code,record_code AS purchaser_record_code,supplier_code,money AS record_money,payment_money,finish_num,is_payment,enter_time,enter_time AS create_time,remark FROM pur_purchaser_record WHERE is_notify_payment <> 1 AND is_payment <> 1 AND is_check_and_accept = 1 AND money <> 0))
 AS rl WHERE 1 ";
        $sql_values = array();
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $sql_purchaser .= load_model('base/SupplierModel')->get_sql_purview_supplier('rl.supplier_code', $filter_supplier_code);
        //入库日期
        if (isset($filter['create_time_start']) && $filter['create_time_start'] != '') {
            $sql_purchaser .= " AND (rl.create_time >= :create_time_start )";
            $sql_values[':create_time_start'] = $filter['create_time_start'];
        }
        if (isset($filter['create_time_end']) && $filter['create_time_end'] != '') {
            $sql_purchaser .= " AND (rl.create_time < :create_time_end )";
            $date = new DateTime($filter['create_time_end']);
            $date->add(new DateInterval('P1D'));
            $sql_values[':create_time_end'] = $date->format('Y-m-d');
        }
        //入库单编号
        if(isset($filter['pur_record_code']) && $filter['pur_record_code'] != '') {
            $sql_purchaser .= " AND rl.purchaser_record_code LIKE :pur_record_code ";
            $sql_values[':pur_record_code'] = "%" . $filter['pur_record_code'] . "%";
        }
        //订单编号
        if(isset($filter['planned_record_code']) && $filter['planned_record_code'] != '') {
            $sql_purchaser .= " AND rl.planned_record_code LIKE :planned_record_code ";
            $sql_values[':planned_record_code'] = "%" . $filter['planned_record_code'] . "%";
        }
        //备注
        if(isset($filter['remark']) && $filter['remark'] != '') {
            $sql_purchaser .= " AND rl.remark LIKE :remark ";
            $sql_values[':remark'] = "%" . $filter['remark'] . "%";
        }
        
        if(isset($filter['record_code_str']) && $filter['record_code_str'] != '') {
            $arr =  $this->screening_record($filter['record_code_str']);
            //拼接采购入库单
            $purchaser_record_str = $this->arr_to_in_sql_value($arr['JH'],'purchaser_record_code',$sql_values);
            //拼接采购订单
            $planned_record_str = $this->arr_to_in_sql_value($arr['CG'],'planned_record_code',$sql_values);
            if(!empty($arr['JH']) && !empty($arr['CG'])) {
                $sql_purchaser .= " AND (rl.purchaser_record_code in ({$purchaser_record_str}) OR rl.planned_record_code in ({$planned_record_str}))";
            } else if(!empty($arr['JH'])) {
                $sql_purchaser .= " AND rl.purchaser_record_code in ({$purchaser_record_str}) ";
            } else if(!empty($arr['CG'])) {
                $sql_purchaser .= " AND rl.planned_record_code in ({$planned_record_str}) ";
            }
        }
        // 付款状态
        if (isset($filter['is_payment']) && $filter['is_payment'] != '') {
            $is_payment_arr = explode(",", $filter['is_payment']);
            $is_payment_str = $this->arr_to_in_sql_value($is_payment_arr,'is_payment',$sql_values);
            $sql_purchaser .= " AND rl.is_payment in ({$is_payment_str}) ";
        }
        $selete_purchaser = "rl.* ";
        $sql_purchaser .= " ORDER BY rl.enter_time ";
        if(!empty($filter['list_type']) && $filter['list_type'] == 'set_payment_money') {
            $sql = 'SELECT ' . $selete_purchaser . $sql_purchaser;
            return array('sql' => $sql, 'sql_value' => $sql_values); 
        }
        $data = $this->get_page_from_sql($filter, $sql_purchaser, $sql_values, $selete_purchaser);
        foreach($data['data'] as $key => $value) {
//            if($value['record_money'] == 0) {
//                unset($data['data'][$key]);
//            }
            //查询供应商名称
            $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier', 'supplier_code', $value['supplier_code'], 'supplier_name');
            $data['data'][$key]['supplier_name'] = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
            //订单金额
            $data['data'][$key]['record_money'] = !empty($value['record_money']) ? $value['record_money'] : '0.000';
            //待付金额
            $diff_money = !empty($value['record_money']) ? $value['record_money'] - $value['payment_money'] : '0.000';
            $data['data'][$key]['diff_money'] = sprintf('%.3f',$diff_money);
            //付款状态
            $data['data'][$key]['is_payment_str'] = $this->is_parment_arr[$value['is_payment']];
            //采购订单编号
            /*$data['data'][$key]['planned_record_code'] = '';
            if(!empty($value['relation_code'])) {
                $planned_code = $this->get_planned_code($value['relation_code']);
                $data['data'][$key]['planned_record_code'] = !empty($planned_code['record_code']) ? $planned_code['record_code'] : '';
                $data['data'][$key]['planned_record_id'] = !empty($planned_code['planned_record_id']) ? $planned_code['planned_record_id'] : '';
            }*/
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
        
    }
    // 根据通知单号获取关联订单号
    function get_planned_code($notify_code) {
        $order_data = load_model('pur/OrderRecordModel')->is_exists($notify_code);
        $planned_code = array();
        if(!empty($order_data['data']['relation_code'])) {
            $planned_data = load_model('pur/PlannedRecordModel')->get_by_code($order_data['data']['relation_code']);
            $planned_code = $planned_data['data'];
        }
        return $planned_code;
    }
    //获取订单信息和入库单信息
    function get_record_info($params) {
        //筛选订单
        $arr =  $this->screening_record($params);
        
        $planned_arr = $arr['CG'];
        $purchaser_arr = $arr['JH'];
        $data = array('record_money' => 0 ,'payment_money' => 0 ,'diff_money' => 0);
        $record_money = 0;
        $payment_money = 0;
        $diff_money = 0;
        if(!empty($planned_arr)) {
            $sql_values = array();
            $record_code_str = $this->arr_to_in_sql_value($planned_arr,'record_code',$sql_values);
            $sql = "SELECT sum(rl.money) AS record_money ,sum(rl.payment_money) AS payment_money,rl.supplier_code FROM pur_planned_record AS rl WHERE rl.record_code IN ({$record_code_str})";
            $pla_data = $this->db->get_row($sql,$sql_values);
            // 单据金额
            $record_money += $pla_data['record_money'];
            $record_money = sprintf('%.3f',$record_money);
            // 已付金额
            $payment_money += $pla_data['payment_money'];
            $payment_money = sprintf('%.3f',$payment_money);
            // 应付金额
            $diff_money += ($pla_data['record_money'] - $pla_data['payment_money']);
            $diff_money = sprintf('%.3f',$diff_money);
            //供应商
            $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier', 'supplier_code', $pla_data['supplier_code'], 'supplier_name');
            $supplier_name = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
            $supplier_code = $pla_data['supplier_code'];
        }
        if(!empty($purchaser_arr)) {
            $sql_values = array();
            $record_code_str = $this->arr_to_in_sql_value($purchaser_arr,'record_code',$sql_values);
            $sql = "SELECT sum(rl.money) AS record_money ,sum(rl.payment_money) AS payment_money,rl.supplier_code FROM pur_purchaser_record AS rl WHERE rl.record_code IN ({$record_code_str})";
            $pur_data = $this->db->get_row($sql,$sql_values);
            // 单据金额
            $record_money += $pur_data['record_money'];
            $record_money = sprintf('%.3f',$record_money);
            // 已付金额
            $payment_money += $pur_data['payment_money'];
            $payment_money = sprintf('%.3f',$payment_money);
            // 应付金额
            $diff_money += ($pur_data['record_money'] - $pur_data['payment_money']);
            $diff_money = sprintf('%.3f',$diff_money);
            //供应商
            $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier', 'supplier_code', $pur_data['supplier_code'], 'supplier_name');
            $supplier_name = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
            $supplier_code = $pur_data['supplier_code'];
        }
        $data = array(
            'record_money' => $record_money,
            'payment_money' => $payment_money,
            'diff_money' => $diff_money,
            'supplier_name' => $supplier_name,
            'supplier_code' => $supplier_code
        );
        return $data;
    }
    function screening_record($params) {
        $record_arr = explode(",", $params);
        $arr = array();
        $i = 1;
        foreach ($record_arr as $val) {
            if(strpos($val, 'JH') !== false) {
                $arr['JH'][] = $val;
            } else if(strpos($val, 'CG') !== false) {
                $arr['CG'][] = $val;
            }
            $i++;
        }
        return $arr;
    }
    function get_by_page_record($filter) {
        $sql = $this->get_by_page($filter);
        
        $data = $this->db->getAll($sql['sql'],$sql['sql_value']);
        //将当前付款金额，均摊到每个订单
        $current_payment_money = $filter['current_payment_money'];
        
        foreach ($data as &$val) {
            //订单金额
            $val['record_money'] = !empty($val['record_money']) ? $val['record_money'] : '0.000';
            //待付金额
            $diff_money = !empty($val['record_money']) ? $val['record_money'] - $val['payment_money'] : 0;
            $val['diff_money'] = sprintf('%.3f',$diff_money);
            //当前付款金额
            if($current_payment_money > $val['diff_money']) {
                $val['current_payment_money'] = $val['diff_money'];
                $current_payment_money -= $val['diff_money'];
            } else {
                $val['current_payment_money'] = $current_payment_money;
                $current_payment_money = 0;
            }
        }
        
        return $data;
    }
}
