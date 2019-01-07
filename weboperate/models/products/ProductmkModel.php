<?php

/**
 * 用户相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');

//require_lang('sys');

class ProductmkModel extends TbModel {

    function get_table() {
        return 'osp_chanpin_module';
    }

    /*
     * 获取产品模块方法
     */
    function get_by_page($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";

        //产品id搜索条件
        if (isset($filter['cpid']) && $filter['cpid'] != '') {
            $sql_main .= " AND (pm_cp_id =" . $filter['cpid'] . ")";
        }
        //产品名称搜索
        if (isset($filter['keyword']) && $filter['keyword'] != '') {
            $sql_main .= " AND pm_name like '%"  . $filter['keyword'] . "%'";
        }

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        //return $this->get_row(array('cp_id' => $id));
        $params=array('pm_id'=>$id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        //filter_fk_name($data, array('cp_createuser|osp_user_id','cp_updateuser|osp_user_id'));

        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加产品模块信息
     */
    function insert($productmk) {
        $status = $this->valid($productmk);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        /*$ret = $this->is_exists($product['pm_name'], 'pm_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');
        */ 
        return parent::insert($productmk);
    }



    /*
     * 修改产品模块信息。
     */
    function update($productmk, $id) {
        $status = $this->valid($productmk, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        /*$ret = $this->get_row(array('pm_id' => $id));
        if ($productmk['pm_name'] != $ret['data']['pm_name']) {
            $ret = $this->is_exists($productmk['pm_name'], 'pm_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret('name_is_exist');
        }*/
                        
        $ret = parent::update($productmk, array('pm_id' => $id));
        return $ret;
    }
    
    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id){
        $result = parent::delete(array('pm_id'=>$id));
        return $result;
    }

    
    /*
     * 服务器端验证提交的数据是否重复
     */
    private function valid($data, $is_edit = false) {
        if (!isset($data['pm_name']) || !valid_input($data['pm_name'], 'required'))
            return CP_ERROR_NAME;
            return 1;
    }

    private function is_exists($value, $field_name = 'pm_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

   

}
