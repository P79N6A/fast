<?php

/**
 * 短信模板 相关业务
 *
 * @author dfr
 */
require_model('tb/TbModel');

class ExpressTplModel extends TbModel {

    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'sys_print_templates';
    }

    function get_data_list($fld = 'id,tpl_name') {
        $sql = "select $fld from {$this->table} ";
        $arr = $this->db->get_all($sql);
        return $arr;
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} t LEFT JOIN  base_express_company c ON t.company_code=c.company_code  WHERE t.type = 1";
        if (isset($filter['company_code']) && $filter['company_code'] != '') {
            $sql_main .= " AND t.company_code = :company_code";
            $sql_values[':company_code'] = $filter['company_code'];
        }
        if (isset($filter['print_templates_name']) && $filter['print_templates_name'] != '') {
            $sql_main .= " AND t.print_templates_name LIKE :print_templates_name";
            $sql_values[':print_templates_name'] = $filter['print_templates_name'] . '%';
        }
        //判断批发快递单模板是否开启
        $arr = array('pur_express_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $pur_express_print = isset($ret_arr['pur_express_print']) ? $ret_arr['pur_express_print'] : '';
        if ($pur_express_print == 0) {
            $sql_main .= " AND t.is_buildin <> 4 ";
        }

        $select = 't.*,c.company_name';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $is_buildin_arr = array('0' => '自定义', '1' => '系统内置', '2' => '云栈', '3' => '菜鸟云打印', '4' => '批发打印', '5' => '无界');
        foreach ($data['data'] as &$val) {
            $val['is_buildin_name'] = $is_buildin_arr[$val['is_buildin']];
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $arr = $this->get_row(array('print_templates_id' => $id));
        return $arr;
    }

    /**
     * 修改纪录
     */
    function update($supplier, $id) {
        $status = $this->valid($supplier, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('id' => $id));
        if ($supplier['print_templates_name'] != $ret['data']['print_templates_name']) {
            $ret = $this->is_exists($supplier['print_templates_name'], 'print_templates_name');
            if ($ret['status'] > 0 && !empty($ret['data'])) {
                return $this->format_ret('express_tpl_error_unique_name');
            }
        }

        $ret = parent::update($supplier, array('id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'print_templates_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 添加新纪录
     */
    function insert($supplier) {
        $status = $this->valid($supplier);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($supplier['print_templates_name'], 'print_templates_name');
        if ($ret['status'] > 0 && !empty($ret['data'])) {
            return $this->format_ret('express_tpl_error_unique_name');
        }

        return parent::insert($supplier);
    }

    /**
     * 删除记录
     */
    function delete($id, $is_buildin) {
        if ($is_buildin == 3) {
            $data = $this->db->get_row("SELECT express_code FROM base_express WHERE rm_id = :rm_id ", array(':rm_id'=>$id));
            if (!empty($data['express_code'])) {
                $ret = array('status' => -1, 'message' => '已有配送方式关联此模版，不允许删除！');
                return $ret;
            }else{
                $ret = parent :: delete(array('print_templates_id' => $id));
            }
        } else {
            $ret = parent :: delete(array('print_templates_id' => $id));
        }
        return $ret;
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('is_active' => $active), array('id' => $id));
        return $ret;
    }

}
