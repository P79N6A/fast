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
class PayMoneyDetailModel extends TbModel {
    public $record_type_arr = array(
        'sales_settlement' => '网单预扣款',
        'un_sales_settlement'=>'网单预扣款取消',
        'sales_settlemented' => '网单扣款'
    );
    
    function get_table() {
        return 'fx_pay_money_detail';
    }
    /*
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_join = "";
        $sql_main = " FROM {$this->table} rl $sql_join WHERE 1";
        $sql_values = array();
        //仓库名称或代码
        if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
            $sql_main .= " AND rl.custom_code = :custom_code";
            $sql_values[':custom_code'] = $filter['custom_code'];
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
        $select = 'rl.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value){
            $value['custom_name'] = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $value['custom_code']));
            $value['record_type_name'] = $this->record_type[$value['record_type']];
            $value['status_name'] = $value['status'] == 1? '已结算':'预结算';
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    function get_fx_money_handle_detail($sell_record_code){
        $sql = "SELECT * FROM fx_income_pay WHERE record_code = :record_code ORDER BY create_time DESC";
        $handle_detail = $this->db->get_all($sql,array(":record_code" => $sell_record_code));
        if(!empty($handle_detail)){
            foreach ($handle_detail as &$detail){
                //$detail['record_type_name'] = $this->record_type_arr[$detail['record_type']];
                $detail['status_str'] = $detail['state'] == 1 ? '正常' : '已作废';
                $detail['abstract'] = load_model('fx/BalanceOfPaymentsModel')->abstract[$detail['abstract']];
            }
        }
        return $handle_detail;
    }
    
    function fx_money_detail_insert($record,$type='',$remark = ''){
        $params2 = array();
        if(empty($record)){
            return $this->format_ret(-1,'','单据信息为空！');
        }
        $type_arr = array('un_sales_settlement','sales_settlemented');
        $fx_amount = $record['fx_payable_money'] + $record['fx_express_money'];
        if(in_array($type, $type_arr)){
            $fx_amount = 0 - $fx_amount;
        }
        $params2['sell_record_code'] = $record['sell_record_code'];
        $params2['custom_code'] = $record['fenxiao_code'];
        $params2['record_time'] = date("Y-m-d H:i:s");
        $params2['record_type'] = $type;
        $params2['money'] = $fx_amount;
        $sql = "select account_money,frozen_money,settlement_amount from base_custom where custom_code = :custom_code";
        $custom_info = $this->db->get_row($sql,array(":custom_code" => $record['fenxiao_code']));
        $params2['frozen_money'] = $custom_info['frozen_money'] + $fx_amount;
        $params2['remaining_money'] = $custom_info['settlement_amount'] - $fx_amount;
        $params2['record_time'] = date("Y-m-d H:i:s");
        $params2['remark'] = $remark;
        $result = parent::insert($params2);
        if($result['status'] < 0){
            return $result;
        }
        $update_arr = array('frozen_money' => $params2['frozen_money'],'settlement_amount' => $params2['remaining_money']);
        $ret = load_model('base/CustomModel')->update_custom_by_code($update_arr, array("custom_code" => $record['fenxiao_code']));
        return $ret;
    }      
}
