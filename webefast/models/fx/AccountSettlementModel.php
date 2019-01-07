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
class AccountSettlementModel extends TbModel {
    public $record_type = array(
        'pre_deposits' => '预存款',
        'sales_settlement' => '销售结算',
        'sales_refund' => '销售退款',
        'purchase_settlement' => '采购结算',
        'purchase_refund' => '采购退款'
    );
    
    function get_table() {
        return 'fx_settlement';
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
                $sql_main .= " AND 1 != 1 ";
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
    
    function insert_settlement($data){
        if(empty($data)){
            return $this->format_ret(-1,'','单据信息不能为空');
        }
        $params = array();
        $params['custom_code'] = $data['custom_code'];
        $params['record_type'] = isset($data['record_type']) ? $data['record_type'] : '';
        $params['status'] = isset($data['status']) &&$data['status'] == 1  ? 1 : 0;
        $params['advance_payment'] = $data['advance_payment'];
        $params['relation_code'] = $data['relation_code'];
        return parent::insert($params);
    }
    
    /**
     * @param $id
     * @return array
     */
    function get_by_id($id) {
        return $this->get_row(array('account_id' => $id));
    }

    function update_status($record_code){
        $sql = "select * from {$this->table} where relation_code = :relation_code";
        $row = $this->db->get_row($sql,array(":relation_code" => $record_code));
        if(empty($row)){
            return $this->format_ret(-1,'','分销结算单不存在');
        }
        $ret = $this->update(array("status" => 1), array("relation_code" => $record_code));
        return $ret;
    }

    /*
     * 删除记录
     * */

    function delete($relation_code) {
        $ret = parent::delete(array('relation_code' => $relation_code));
        return $ret;
    }

}
