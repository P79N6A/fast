<?php

require_model('tb/TbModel');

/**
 * 成本月结单业务
 */
class CostMonthModel extends TbModel {

    function get_table() {
        return 'cost_month';
    }

    /**
     * @todo 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} r1 WHERE 1";
        $sql_values = array();
        // 月结月份
        if (isset($filter['ymonth']) && $filter['ymonth'] != '') {
            $sql_main .= " AND (r1.ymonth=:ymonth )";
            $sql_values[':ymonth'] = $filter['ymonth'];
        }
        //审核状态
        if (isset($filter['is_check']) && $filter['is_check'] != '') {
            $sql_main .= " AND (r1.is_check=:is_check )";
            $sql_values[':is_check'] = $filter['is_check'];
        }
        //月结单号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r1.record_code = :record_code )";
            $sql_values[':record_code'] = $filter['record_code'];
        }
        $select = 'r1.*';
        $sql_main .= " ORDER BY ymonth desc";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $store_name = load_model('base/StoreModel')->get_store_by_code_arr($value['store_code']);
            $value['store_name'] = implode('，', $store_name);
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * @todo 创建成本月结单
     */
    public function insert_cost_month($param) {
        $cur_date = date('Y-m', time());
        if ($param['ymonth'] > $cur_date) {
            return $this->format_ret(-1, '', '不能创建当前月之后的月结单');
        }
        $this->begin_trans();
        $ret_cost = $this->insert($param); //添加主单据
        if ($ret_cost['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', $ret_cost['message']);
        }
        $ret_detail = load_model('acc/CostMonthDetailModel')->create_cost_month_detail($param); //添加详情单据
        if ($ret_detail['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', $ret_detail['message']);
        }
        $this->commit();
        load_model('acc/CostMonthLogModel')->add_log($param['record_code'], '创建单据');
        return $this->format_ret(1);
    }

    /**
     * @todo 添加成本月结单主单据
     */
    public function insert($param) {
        $status = $this->valid($param);
        if ($status != 1) {
            return $this->format_ret($status);
        }
        if (empty($param['store_code'])) {
            return $this->format_ret(-1, '', '未选择仓库');
        }
        $ret1 = $this->is_exists_month_store($param['ymonth'], $param['store_code']);
        if (!empty($ret1['data'])) {
            return $this->format_ret('-1', '', "您选择的部分仓库已生成该月月结单");
        }
        $ret2 = $this->is_exists($param['record_code']);

        if (!empty($ret2['data'])) {
            return $this->format_ret('-1', '', '月结单号已存在');
        }
        $param['record_time'] = date('Y-m-d H:i:s');

        return parent::insert($param);
    }

    /**
     * @todo 删除记录
     */
    function delete($cost_month_id) {
        $sql = "SELECT * FROM {$this->table} where cost_month_id = :cost_month_id";
        $data = $this->db->get_row($sql, array(":cost_month_id" => $cost_month_id));
        if ($data['is_check'] == 1) {
            return $this->format_ret('-1', array(), '月结单已经审核，不能删除！');
        }
        $detail_tbl = load_model('acc/CostMonthDetailModel')->get_cost_detail_tbl($data['ymonth']);
        $this->begin_trans();
        $ret_detail = array();
        if ($detail_tbl['status'] == 1) {
            $ret_detail = $this->delete_exp($detail_tbl['data'], array('record_code' => "{$data['record_code']}"));
            if ($ret_detail == false) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除失败');
            }
        }
        $ret_main = parent::delete(array('cost_month_id' => $cost_month_id));
        if ($ret_main['status'] != 1) {
            $this->rollback();
            return $ret_main;
        }
        $this->commit();
        return $ret_main;
    }

    /**
     * @todo 通过field_name查询
     * @param string $field_name 条件字段名
     * @param string $value 值
     * @param string $select 查询字段
     * @return array (status, data, message)
     */
    function get_by_field($field_name, $value, $select = "*") {
        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    /**
     * @todo 根据id获取数据
     */
    function get_by_id($id) {
        $data = $this->get_row(array('cost_month_id' => $id));
//        filter_fk_name($data['data'], array('adjust_type|record_type', 'store_code|store'));
        return $data;
    }

    /**
     * @todo 根据年月和仓库判断月结单是否存在
     */
    public function is_exists_month_store($ymonth, $store_code) {
        $store_code_arr = explode(',', $store_code);
        $sql_values = array();
        $sql_str = $this->arr_to_like_sql_value($store_code_arr, 'store_code', $sql_values);

        $sql = "SELECT record_code FROM {$this->table} WHERE ymonth=:ymonth AND {$sql_str}";
        $sql_values[':ymonth'] = $ymonth;
        $ret = $this->get_limit($sql, $sql_values, 1);
        return $ret;
    }

    /**
     * @todo 判断单据是否存在
     */
    public function is_exists($value, $field_name = 'record_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * @todo 服务器端验证
     */
    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['record_code']) || !valid_input($data['record_code'], 'required')))
            return RECORD_ERROR_CODE;
        return 1;
    }

    /**
     * @todo 生成月结单号
     */
    function create_fast_bill_sn() {
        $sql = "select cost_month_id  from {$this->table} order by cost_month_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['cost_month_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "CM" . date("Ymd") . add_zero($djh);
        return $jdh;
    }

    /**
     * @todo 更新确认状态
     */
    function update_sure($active, $id) {
        $ret_cost = $this->is_exists($id, 'cost_month_id');
        if (empty($ret_cost['data'])) {
            return $this->format_ret(-1, '', '月结单不存在');
        }
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'error_params');
        }
        $ret = parent:: update(array('is_sure' => $active), array('cost_month_id' => $id));
        if ($ret['status'] == 1) {
            load_model('acc/CostMonthLogModel')->add_log($ret_cost['data']['record_code'], $active == 1 ? '确认' : '取消确认');
        }
        return $ret;
    }

    /**
     * @todo 更新审核状态
     */
    function update_check($active, $id) {
        $ret_cost = $this->is_exists($id, 'cost_month_id');
        if (empty($ret_cost['data'])) {
            return $this->format_ret(-1, '', '月结单不存在');
        }
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'error_params');
        }
        $ret = parent:: update(array('is_check' => $active, 'check_time' => date('Y-m-d h:i:s')), array('cost_month_id' => $id));
        if ($ret['status'] == 1) {
            load_model('acc/CostMonthLogModel')->add_log($ret_cost['data']['record_code'], '审核');
        }
        return $ret;
    }

    /**
     * @todo 修改主单据信息
     */
    public function edit_action($data, $where) {
        if (!isset($where['record_code'])) {
            return $this->format_ret(false, array(), '修改时缺少主单据ID或code!');
        }
        $result = $this->get_row($where);
        if ($result['status'] != 1) {
            return $this->format_ret(false, array(), '没找到单据!');
        }

        //更新主表数据
        $ret = parent::update($data, $where);
        if ($ret['status'] == 1) {
            load_model('acc/CostMonthLogModel')->add_log($where['record_code'], '修改单据', "备注修改为{$data['remark']}");
        }
        return $ret;
    }

}
