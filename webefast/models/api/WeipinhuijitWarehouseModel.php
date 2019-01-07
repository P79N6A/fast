<?php

/**
 * 唯品会JIT仓库管理业务
 */
require_model('tb/TbModel');

class WeipinhuijitWarehouseModel extends TbModel {

    function __construct() {
        parent::__construct('api_weipinhuijit_warehouse');
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} r1 WHERE 1";
        $sql_values = array();
        //仓库名称或代码
        if (isset($filter['warehouse_name']) && $filter['warehouse_name'] != '') {
            $sql_main .= " AND r1.warehouse_name LIKE :warehouse_name";
            $sql_values[':warehouse_name'] = '%' . $filter['warehouse_name'] . '%';
        }

        $select = 'r1.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['is_active'] = $val['status'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * @todo 更新启用状态
     */
    function update_active($params) {
        $active = array('enable' => 1, 'disable' => 0);
        if (!isset($active[$params['active']])) {
            return $this->format_ret(-1, '', '参数有误');
        }
        $status = $active[$params['active']];

        $ret = parent::update(array('status' => $status), array('warehouse_id' => $params['id']));
        return $ret;
    }

    /**
     * 获取仓库，供列表查询选择使用
     */
    function get_warehouse_select($is_check = 1) {
        $sql = "SELECT warehouse_code,warehouse_name,warehouse_no FROM {$this->table} ";
        if ($is_check == 1) {
            $sql .=" WHERE status=1";
        }
        $rs = $this->db->get_all($sql);
        return $rs;
    }

    /**
     * 根据指定字段查询
     */
    public function get_by_field($field_name, $value, $select = "*") {
        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }
    
    function edit_custom($data) {
        if($data['custom_code'] === '0') {
            $data['custom_code'] = '';
        }
        $ret = $this->update(array('custom_code' => $data['custom_code']), array('warehouse_id' => $data['warehouse_id']));
        return $ret;
    }
    
    /**
     * 校验送货仓是否启用
     * @param string $warehouse_code 送货仓代码
     * @return array
     */
    function check_warehouse($warehouse_code) {
        $warehouse_data = $this->get_by_field('warehouse_code', $warehouse_code, 'status');
        if ($warehouse_data['status'] < 1) {
            return $this->format_ret(-1, '', '送货仓库不存在');
        }
        if ($warehouse_data['data']['status'] != 1) {
            return $this->format_ret(-1, '', '送货仓库未启用');
        }
        return $this->format_ret(1);
    }

}
