<?php

/**
 * 产品相关业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');
class ProductmdModel extends TbModel {

    function get_table() {
        return 'osp_chanpin_member';
    }

    /*
     * 获取产品成员方法
     */
    function get_by_page($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";

        //产品id搜索条件
        if (isset($filter['cpid']) && $filter['cpid'] != '') {
            $sql_main .= " AND (pcm_cp_id =" . $filter['cpid'] . ")";
        }

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        
         //处理关联产品名称
        filter_fk_name($ret_data['data'], array('pcm_user|osp_user_id','pcm_user_post|osp_post'));   
        
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params=array('pcm_id'=>$id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('pcm_user|osp_user_id_p'));

        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加产品成员信息
     */
    function insert($productmd) {
        return parent::insert($productmd);
    }



    /*
     * 修改产品成员信息。
     */
    function update($productmd, $id) {
        $ret = parent::update($productmd, array('pcm_id' => $id));
        return $ret;
    }
    
    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id){
        $result = parent::delete(array('pcm_id'=>$id));
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
