<?php

/**
 * 唯品会JIT仓库管理业务
 */
require_model('tb/TbModel');

class WmsQimenModel extends TbModel {

    function __construct() {
        parent::__construct('wms_qimen_config');
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} r1 WHERE 1";
        $sql_values = array();
        /** 根据客户名称查询客户id start */
        if (!empty($filter['customer'])) {
            global $context;
            $sql = "SELECT `kh_id` FROM `osp_kehu` WHERE `kh_name` LIKE \"%{$filter['customer']}%\"";
            $filter['kh_id'] = array_map(function ($item) {
                return $item['kh_id'];
            }, $context->db->get_all($sql));
        }
        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            $sql_main .= ' AND kh_id IN ("' . implode('","', $filter['kh_id']) . '")';
        }
        $select = 'r1.*';

        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        filter_fk_name($ret_data['data'], array('kh_id|osp_kh',));
        $ret_status = OP_SUCCESS;
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

    /**
     * 新增
     * @param $params
     * @return array
     */
    public function add_action($params) {
        $ret = $this->get_row(array('kh_id' => $params['kh_id'], 'qimen_id' => $params['qimen_id']));
        if ($ret['status'] == 1) {
            return $this->format_ret('-1', '', '该配置已存在！');
        }
        $params['add_time'] = date('Y-m-d H:i:s');
        $params['add_person'] = CTX()->get_session("user_code");
        $ret = $this->insert($params);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '新增失败！');
        }
        return $this->format_ret('1', '', '新增成功！');
    }

    /**
     * 删除
     * @param $params
     * @return array
     */
    public function delete_action($params) {
        $wms_config_id = $params['wms_config_id'];
        $ret = $this->delete(array('wms_config_id' => $wms_config_id));
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '删除失败！');
        }
        return $this->format_ret('1', '', '删除成功！');
    }


}
