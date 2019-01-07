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

    public function add_action($params) {
        $ret = $this->get_by_field('warehouse_code', $params['warehouse_code']);
        if ($ret['status'] == 1) {
            return $this->format_ret('-1', '', '仓库编码已存在！');
        }
        $params['create_time'] = date('Y-m-d H:i:s');
        $ret = $this->insert($params);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '新增失败！');
        }
        return $this->format_ret('1', '', '新增成功！');
    }

}
