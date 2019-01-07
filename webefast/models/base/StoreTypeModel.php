<?php

/**
 * 仓库相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('store');

class StoreTypeModel extends TbModel {
  
    function get_table() {
        return 'base_store_type';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
        $sql_values = array();
        //仓库名称或代码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            //$sql_main .= " AND (rl.store_code LIKE '%" . $filter['code_name'] . "%' or rl.store_name LIKE '%" . $filter['code_name'] . "%' )";
            $sql_main .= " AND (rl.type_code LIKE :code_name or rl.type_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        $select = 'rl.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {

        return $this->get_row(array('id' => $id));
    }

    function get_by_code($store_code) {

        return $this->get_row(array('store_code' => $store_code));
    }

    /*
     * 添加新纪录
     */

    function insert($store_type) {
        $status = $this->valid($store_type);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($store_type['type_code']);
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(-1, '', '类别代码不能重复');

        $ret = $this->is_exists($store_type['type_name'], 'type_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(-1, '', '类别名称不能重复');


        return parent::insert($store_type);
    }

    //检测是否存在
    private function is_exists($value, $field_name = 'type_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    //列表数据
    function get_list($type=0) {
        $sql = "select type_code,type_name FROM {$this->table} ";
        $rs = $this->db->get_all($sql);
        $store_type = array();
        if($type == 0){
            $store_type[0]['type_code'] = '';
            $store_type[0]['type_name'] = '请选择';
        }
        $i = 1;
        if(!empty($rs)){
            foreach ($rs as $r){
                $store_type[$i]['type_code'] = $r['type_code'];
                $store_type[$i]['type_name'] = $r['type_name'];
                $i ++;
            }
        }
        return $store_type;
    }
    
    function get_select() {
        $sql = "select type_code,type_name FROM {$this->table} ";
        $rs = $this->db->get_all($sql);
        $store_type = array();
        $i = 0;
        if(!empty($rs)){
            foreach ($rs as $r){
                $store_type[$i]['type_code'] = $r['type_code'];
                $store_type[$i]['type_name'] = $r['type_name'];
                $i ++;
            }
        }
        return $store_type;
    }
    
    //仓库列表，删除总仓时，若已被店铺使用，删除时给予提示：仓库已被店铺X使用，请先清除店铺中设置的发货仓/退货仓/库存来源。
    function check_is_used($type_code){
        $store = $this->db->get_all("select store_code from base_store where store_type_code=:store_type_code", array(':store_type_code' => $type_code));
        if(!empty($store)){
            return $this->format_ret(-1,'','仓库类型已被使用，不能删除');
        }
        return $this->format_ret(1);
    }

    /*
     * 删除记录
     * */

    function delete($type_code) {
        $ret_used = $this->check_is_used($type_code);
        if ($ret_used['status'] < 0){
            return $ret_used;
        }
        $ret = parent::delete(array('type_code' => $type_code));
        return $ret;
    }

    /*
     * 修改纪录
     */

    function update($store_type, $id) {
        $status = $this->valid($store_type, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->get_row(array('id' => $id));
        if (isset($store_type['type_name']) && $store_type['type_name'] != $ret['data']['type_name']) {
            $ret = $this->is_exists($store_type['type_name'], 'type_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret(-1,'','类别名称不能重复');
        }
        $ret = parent::update($store_type, array('id' => $id));
        return $ret;
    }
    
     /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['type_code']) || !valid_input($data['type_code'], 'required')))
            return 'STORE_ERROR_CODE';
        if (!$is_edit) {
            if (!isset($data['type_name']) || !valid_input($data['type_name'], 'required'))
                return 'STORE_ERROR_NAME';
        }
        return 1;
    }

}
