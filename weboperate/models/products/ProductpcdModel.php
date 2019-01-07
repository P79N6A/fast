<?php

/**
 * 补丁明细相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');
class ProductpcdModel extends TbModel {

    function get_table() {
        return 'osp_version_patch_sql';
    }

    /*
     * 获取补丁明细方法
     */
    function get_by_page($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";

        //版本编号
        if (isset($filter['v_no']) && $filter['v_no'] != '') {
            $sql_main .= " AND (version_no ='{$filter['v_no']}')";
        }
        //
        if (isset($filter['v_pt']) && $filter['v_pt'] != '') {
            $sql_main .= " AND (version_patch ='{$filter['v_pt']}')";
        }

        $select = '*';
//        var_dump($sql_main,$select);
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
//        filter_fk_name($ret_data['data'], array('version_no|osp_pdt_bh',));   
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        //return $this->get_row(array('cp_id' => $id));
        $params=array('id'=>$id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        //filter_fk_name($data, array('cp_createuser|osp_user_id','cp_updateuser|osp_user_id'));

        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加SQL补丁模块信息
     */
    function insert($prouctpcd) {
        if(isset($prouctpcd)){
            return parent::insert($prouctpcd);
    }
    }


    /*
     * 修改产品模块信息。
     */
    function update($prouctpcd, $id) {
        if(isset($prouctpcd)){
        $ret = parent::update($prouctpcd, array('id' => $id));
        return $ret;
    }
    }
    
    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id){
        $result = parent::delete(array('id'=>$id));
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
