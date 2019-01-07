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
require_lib('comm_util', true);
class RunningAccountModel extends TbModel {

    public $record_type = array(
        'pre_deposits' => '预存款',
        'sales_settlement' => '销售结算',
        'sales_refund' => '销售退款'
    );
            
    
    function get_table() {
        return 'fx_running_account';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";
        $sql_main = " FROM {$this->table} rl $sql_join WHERE 1";
        $sql_values = array();
        $login_type = CTX()->get_session('login_type');
        if($login_type == 2){
            $user_code = CTX()->get_session('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            if(!empty($custom['custom_code'])){
                $sql_main .= " AND rl.custom_code = :custom_code";
                $sql_values[':custom_code'] = $custom['custom_code'];
            } else {
                $sql_main .= " AND 1 != 1";
            }
        } else {
            //仓库名称或代码
            if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
                $sql_main .= " AND rl.custom_code = :custom_code";
                $sql_values[':custom_code'] = $filter['custom_code'];
            }
        }
        
        
        if (isset($filter['record_type']) && $filter['record_type'] !== '') {
            $arr = explode(',',$filter['record_type']);
            $str = $this->arr_to_in_sql_value($arr, 'record_type', $sql_values);
            $sql_main .= " AND rl.record_type in ({$str}) ";
        }
        
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND rl.remark LIKE :remark";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
        }
        //创建时间
        if (isset($filter['change_time_start']) && $filter['change_time_start'] != '') {
            $sql_main .= " AND (rl.change_time >= :change_time_start )";
            $sql_values[':change_time_start'] = $filter['change_time_start'].' 00:00:00';
        }
        if (isset($filter['change_time_end']) && $filter['change_time_end'] != '') {
            $sql_main .= " AND (rl.change_time <= :create_time_end )";
            $sql_values[':change_time_end'] = $filter['change_time_end'].' 23:59:59';
        }
        $select = 'rl.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value){
            $value['custom_name'] = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $value['custom_code']));
            $value['record_type_name'] = $this->record_type[$value['record_type']];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function insert_running_account($data){
        $params = array();
        $params['record_code'] = $this->create_fast_bill_sn();
        $params['relation_code'] = isset($data['relation_code']) ? $data['relation_code'] : '';
        $params['custom_code'] = isset($data['custom_code']) ? $data['custom_code'] : '';
        $params['account_money'] = isset($data['account_money']) ? $data['account_money'] : '';
        $params['record_type'] = isset($data['record_type']) ? $data['record_type'] : '';
        $params['account_money_end'] = $this->fx_account_money_end($params['custom_code']);
        $params['account_money_start'] = $params['account_money_end'] - $params['account_money'];
//        if($params['account_money_end'] < 0){
//            //信用额度
//        }
        $params['change_time'] = date("Y-m-d H:i:s");
        $params['remark'] = isset($data['remark']) ? $data['remark'] : '';
        return parent::insert($params);
    }
    
    function create_fast_bill_sn() {
        $sql = "select running_account_id from {$this->table} order by running_account_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['running_account_id']) + 1;
        } else {
            $djh = 1;
        }
        $jdh = "FXLS" .  date('Ymd'). add_zero($djh, 3);
        return $jdh;
    }
    
    function fx_account_money_end($custom_code){
        $sql = "select settlement_amount from base_custom where custom_code = :custom_code";
        $ret = $this->db->get_value($sql,array(":custom_code" => $custom_code));
        $account_money_end = $ret !== FALSE ? $ret : 0;
        return $account_money_end;
    }
   
    public function get_record_type() {
        $record_type = $this->record_type;
        $record_type_arr = array();
        $i = 0;
        foreach ($record_type as $key => $type) {
            $record_type_arr[$i]['record_type_code'] = $key;
            $record_type_arr[$i]['record_type_name'] = $type;
            $i ++;
        }
        return $record_type_arr;
    }

}
