<?php

/**
 * 云服务供应商配置明细表相关业务
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');

class CloudserverModel extends TbModel {

    function get_table() {
        return 'osp_cloud_module';
    }

    /*
     * 获取云主机详细配置
     */
    function get_by_page_host($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";


        //云主机服务器类型
        if (isset($filter['cd_id']) && $filter['cd_id'] != '') {
            $sql_main .= " AND (cm_cd_id =" . $filter['cd_id'] . ")";
        }
        $sql_main .= " AND cm_type =1";
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_page_db($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";

        //云数据库服务类型
        if (isset($filter['cd_id']) && $filter['cd_id'] != '') {
            $sql_main .= " AND (cm_cd_id =" . $filter['cd_id'] . ")";
        }
        $sql_main .= " AND cm_type =2";
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('cm_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        //filter_fk_name($data, array('cp_createuser|osp_user_id','cp_updateuser|osp_user_id'));
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加云主机详细
     */
    function insert_host($cloudserver) {
        $status = $this->valid($cloudserver);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        return parent::insert($cloudserver);
    }

    /*
     * 修改云主机详细
     */
    function update_host($cloudserver, $id) {
        $status = $this->valid($cloudserver, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = parent::update($cloudserver, array('cm_id' => $id));
        return $ret;
    }

    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id) {
        $result = parent::delete(array('cm_id' => $id));
        return $result;
    }

    /*
     * 服务器端验证提交的数据是否重复
     */
    private function valid_host($data, $is_edit = false) {
        if (!isset($data['cm_host_type']) || !valid_input($data['cm_host_type'], 'required'))
            return name_is_exist;
        return 1;
    }

    private function is_exists_host($value, $field_name = 'cm_host_type') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /*
     * 添加云数据库详细
     */
    function insert_db($cloudserver) {
        $status = $this->valid($cloudserver);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        return parent::insert($cloudserver);
    }

    /*
     * 修改云数据库详细
     */
    function update_db($cloudserver, $id) {
        $status = $this->valid($cloudserver, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = parent::update($cloudserver, array('cm_id' => $id));
        return $ret;
    }

    /*
     * 服务器端验证提交的数据是否重复
     */
    private function valid_db($data, $is_edit = false) {
        if (!isset($data['cm_db_type']) || !valid_input($data['cm_db_type'], 'required'))
            return name_is_exist;
        return 1;
    }

    private function is_exists_db($value, $field_name = 'cm_db_type') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    
    
    
}
