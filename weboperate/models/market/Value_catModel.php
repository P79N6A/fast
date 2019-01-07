<?php

/**
 *  产品中心-增值服务类别
 *
 * @author wsc
 *
 */
require_model('tb/TbModel');

class Value_catModel extends TbModel {

    function get_table() {
        return 'osp_valueserver_category';
        //数据库修改了value_num的字段类型，记得提交
    }

    /*
     * 获取增值服务类别列表方法
     */
    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} rl  WHERE 1";
        
        //代码名称搜索条件
        if (isset($filter['keyword']) && $filter['keyword'] != '') {
            $sql_main .= " AND (rl.vc_code LIKE '%". $filter['keyword'] .
		"%' OR rl.vc_name LIKE '%" . $filter['keyword'] . "%') ";
        }
        //状态
        if (isset($filter['vc_enable']) && $filter['vc_enable'] != '') {
            $sql_main .= " AND value_enable = " . $filter['vc_enable'] ;
        }
        $select = '* ';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        
        //处理关联代码表
        filter_fk_name($ret_data['data'], array('vc_cp_id|osp_chanpin'));  
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params=array('vc_id'=>$id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        //filter_fk_name($data, array('value_require|osp_chanpin_version'));

        return $this->format_ret($ret_status, $data);
    }
    
    //更新状态（启用/禁用）
    function update_value_enable($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }

        $ret = parent::update(array('vc_enable' => $active), array('vc_id' => $id));
        return $ret;
    }
    

    /*
     * 添加增值服务类别
     */
    function insert($values) {
        $status = $this->valid($values);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($values['vc_name'],'vc_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');

        $ret = $this->is_exists($values['vc_code'], 'vc_code');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('code_is_exist');
            return parent::insert($values);
    }



    /*
     * 修改增值服务类别。
     */
    function update($values, $id) {
        $status = $this->valid($values, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('vc_id' => $id));
        if ($values['vc_name'] != $ret['data']['vc_name']) {
            $ret = $this->is_exists($values['vc_name'], 'vc_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret('name_is_exist');
        }
        $ret = parent::update($values, array('vc_id' => $id));
        return $ret;
    }

    
    /*
     * 服务器端验证提交的数据是否重复
     */
    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['vc_code']) || !valid_input($data['vc_code'], 'required')))
            return VL_ERROR_CODE;
        if (!isset($data['vc_name']) || !valid_input($data['vc_name'], 'required'))
            return VL_ERROR_NAME;
            return 1;
    }

    private function is_exists($value, $field_name = 'vc_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

   

}
