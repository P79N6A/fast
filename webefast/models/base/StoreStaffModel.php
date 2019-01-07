<?php

/**
 * 仓库员工相关业务
 * @author dfr
 *
 */
require_model('tb/TbModel');

class StoreStaffModel extends TbModel {
    /** 员工类型
     * @var array
     */
    public $staff_type_name = array(
        '0' => '拣货员',
    );

    function get_table() {
        return 'base_store_staff';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} r1  WHERE 1";
        $sql_values = array();
        //员工名称或代码
        if (isset($filter['staff_name']) && $filter['staff_name'] != '') {
            $key_name = 'staff_name';
            $arr = array($filter['staff_name']);
            $staff_name = $this->arr_to_like_sql_value($arr, $key_name, $sql_values, 'r1.');
            $key_code = 'staff_code';
            $staff_code = $this->arr_to_like_sql_value($arr, $key_code, $sql_values, 'r1.');
            $sql_main .= " AND ({$staff_name} or {$staff_code})";
        }

        $sql_main .= " order by r1.lastchanged desc ";
        $select = 'r1.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            //获取类型名称
            $value['staff_type_name'] = $this->staff_type_name[$value['staff_type']];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($staff_id) {
        $ret = $this->get_row(array('staff_id' => $staff_id));
        return $ret;
    }
    function get_by_code($staff_code){
        $ret = $this->get_row(array('staff_code' => $staff_code));
        return $ret;        
    }
    /*
     * 添加新纪录
     */

    function insert($staff) {
        $status = $this->valid($staff);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->is_exists($staff['staff_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) {
            return $this->format_ret(-1, '', '员工代码不能重复');
        }
        return parent::insert($staff);
    }

    /**
     * 更新员工状态
     * @param array $params array('id','type')
     * @return array 更新结果
     */
    function update_active($id, $active) {
        if (!in_array($active, array(0, 1)) || empty($id)) {
            return $this->format_ret(-1, '', '参数错误，请刷新页面重试');
        }
        $ret = parent:: update(array('status' => $active), array('staff_id' => $id));
        return $ret;
    }

    //检测是否存在
    private function is_exists($value, $field_name = 'staff_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /*
     * 删除记录
     * */

    function delete($staff_id) {
        $ret = parent::delete(array('staff_id' => $staff_id));
        return $ret;
    }

    /*
     * 修改纪录
     */

    function update($staff, $staff_id) {
        $status = $this->valid($staff, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->get_row(array('staff_id' => $staff_id));
        if (isset($staff['staff_name']) && $staff['staff_name'] != $ret['data']['staff_name']) {
            $ret = $this->is_exists($staff['staff_name'], 'staff_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret(-1, '', '员工名称不能重复');
        }
        $ret = parent::update($staff, array('staff_id' => $staff_id));
        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['staff_code']) || !valid_input($data['staff_code'], 'required')))
            return 'STORE_ERROR_CODE';
        if (!$is_edit) {
            if (!isset($data['staff_name']) || !valid_input($data['staff_name'], 'required'))
                return 'STORE_ERROR_NAME';
        }
        return 1;
    }

    function get_select_store_staff() {
        $ret = oms_tb_all('base_store_staff', array('status' => 1));
        $params = array();
        foreach ($ret as $key => $value) {
            $params[$key]['staff_code'] = $value['staff_code'];
            $params[$key]['staff_name'] = $value['staff_name'];
        }
        return $params;
    }



}
