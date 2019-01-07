<?php

/**
 * 营销中心-报价方案
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');
require_lib("comm_util");

class PlanpriceModel extends TbModel {

    function get_table() {
        return 'osp_plan_price';
    }

    /*
     * 获取报价方案
     */

    function get_plan_list($filter) {
//        $sql_join = " left join osp_user u on p.sue_user=u.user_id  ";
        $sql_main = "FROM {$this->table}   WHERE 1";
        //营销类型
        if (isset($filter['strategy_type']) && $filter['strategy_type'] != '') {
            $sql_main .= " AND price_stid = '" . $filter['strategy_type'] . "'";
        }
        //产品搜索条件
        if (isset($filter['product']) && $filter['product'] != '') {
            $sql_main .= " AND price_cpid = '" . $filter['product'] . "'";
        }
        //排序条件
        $sql_main .= " order by price_id desc";

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联营销类型
        filter_fk_name($ret_data['data'], array('price_stid|market_plan','price_cpid|osp_chanpin'));
        return $this->format_ret($ret_status, $ret_data);
    }
    
    function get_plan_info($filter) {
        $sql_main = "FROM {$this->table}   WHERE 1";
        //产品搜索条件
        if (isset($filter['cpid']) && $filter['cpid'] != '') {
            $sql_main .= " AND price_cpid = '" . $filter['cpid'] . "'";
        }
        //营销类型
        if (isset($filter['stid']) && $filter['stid'] != '') {
            $sql_main .= " AND price_stid = '" . $filter['stid'] . "'";
        }
        //排序条件
        $sql_main .= " order by price_id desc";

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联营销类型
        filter_fk_name($ret_data['data'], array('price_stid|market_plan','price_cpid|osp_chanpin'));
        return $this->format_ret($ret_status, $ret_data);
    }
    
    
    
    

    function get_by_id($id) {
        $params = array('price_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        //filter_fk_name($data, array('price_stid|market_plan'));
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加报价方案
     */
    function insert($plan) {
        $status = $this->valid($plan);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->is_exists($plan['price_name'], 'price_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');
            return parent::insert($plan);
    }

    /*
     * 修改报价方案
     */
    function update($plan, $id) {
        $status = $this->valid($plan, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->get_row(array('price_id' => $id));
        if ($plan['price_name'] != $ret['data']['price_name']) {
            $retname = $this->is_exists($plan['price_name'], 'price_name');
            if ($retname['status'] > 0 && !empty($retname['data']))
                return $this->format_ret('name_is_exist');
        }
        $ret = parent::update($plan, array('price_id' => $id));
        return $ret;
    }
    
    
       /*
     * 服务器端验证提交的数据是否重复
     */
    private function valid($data, $is_edit = false) {
        if (!isset($data['price_name']) || !valid_input($data['price_name'], 'required'))
            return 'name_is_exist';
            return 1;
    }

    private function is_exists($value, $field_name = 'price_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    //更新报价模版状态（启用/禁用）
    function update_planprice_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }

        $ret = parent::update(array('price_status' => $active), array('price_id' => $id));
        return $ret;
    }
}
