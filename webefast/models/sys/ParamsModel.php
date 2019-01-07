<?php

/**
 * 参数配置
 *
 * @author dfr
 */
require_model('tb/TbModel');

class ParamsModel extends TbModel {

    function get_table() {
        return 'sys_params';
    }

    /**
     * 修改纪录
     */
    function save($data) {
        if (isset($data['jiazhuang_trade_shipping']) && $data['jiazhuang_trade_shipping'] == 1 && (!isset($data['jiazhuang_shop']) || empty($data['jiazhuang_shop']))) {
            return $this->format_ret(-1, '', '请选择家装店铺');
        }
        if (isset($data['jiazhuang_trade_shipping']) && $data['jiazhuang_trade_shipping'] == 0) {
            $data['jiazhuang_shop'] = array();
        }
        if(isset($data['invoice_msg'])){//发票系统参数
            $invoice_msg = $data['invoice_msg'];
            unset($data['invoice_msg']);
        }
        $action_param = array(
            'unique_status' => array('cote' => 'goods', 'action_code' => 'goods-unique-code'),
            'goods_spec1' => array('cote' => 'goods'),
            'goods_spec2' => array('cote' => 'goods'),
            'inv_sync' => array('cote' => 'operate', 'action_code' => 'op/inv_sync/do_list'),
            'is_policy_store' => array('cote' => 'operate', 'action_code' => 'op/policy_store/do_list'),
            'buyer_remark' => array('cote' => 'operate', 'action_code' => 'crm/express_strategy/get_op_express_by_remark'),
            'fx_finance_manage' => array('cote' => 'finance', 'action_code' => 'fx_manage'),
            'procurement_accounts' => array('cote' => 'finance', 'action_code' => 'pur_manage')
        );
        $action_param['is_more_deliver_package'] = array('action_code' => 'oms/deliver_record/search_package');
        $action_param['lof_status'] = array('action_code' => 'prm/inv_lof/do_list');
        $ret_data = array();
        $action_arr = array();

        foreach ($data as $key => $value) {
            $arr_param = array();
            $arr_param['value'] = $value;

            $ret = $this->get_row(array('param_code' => $key));
            if ($key == 'fx_finance_account_manage' && $value == 0) {
                $this->delete_exp('fx_income_pay');
                $this->update_exp('base_custom', array('yck_account_capital' => 0, 'arrears_money' => 0));
            }
            if ($ret['data']) {
                if (isset($action_param[$key])) {
                    $ret_data[] = $action_param[$key]['cote']; //更新菜单使用
                    if (isset($action_param[$key]['action_code'])) {//更新菜单
                        $status = ($arr_param['value'] != 0) ? 1 : 0;
                        $action_arr[] = array(
                            'data' => array('status' => $status),
                            'where' => array('action_code' => $action_param[$key]['action_code'])
                        );
                    }
                }
                if ($key == 'jiazhuang_shop') {
                    $arr_param['value'] = implode(',', $arr_param['value']);
                }
                if ($key == 'default_invoice') { //修改发票
                    $arr_param['data'] = isset($invoice_msg)?$invoice_msg:'';//修改data字段
                }
                $ret1 = parent::update($arr_param, array('param_code' => $key));
            } else {
                $arr_param['param_code'] = $key;
                $ret1 = parent::insert($arr_param);
            }
        }
        $ret1['data'] = array_unique($ret_data);
        if (!empty($action_arr)) {
            foreach ($action_arr as $val) {
                load_model('sys/PrivilegeModel')->update_action_status($val['data'], $val['where']);
            }
        }

        if (isset($data['notice_email'])) {
            $kh_id = CTX()->saas->get_saas_key();
            load_model('common/KhInfoModel')->set_mail($kh_id, $data['notice_email']);
        }

        return $ret1;
    }

    //获取参数
    function get_params($arr) {
        $version_arr = array('is_policy_store', 'is_policy_store_safe_inv', 'presell_plan', 'express_ploy');
        $version_where = "";
        $product_version_no = load_model('sys/SysAuthModel')->product_version_no();
        if ($product_version_no == 0) {
            $version_where = " AND  param_code not in('" . implode("','", $version_arr) . "') ";
        }

        $sql = "select * FROM {$this->table} where 1 and (";
        $k = 1;

        foreach ($arr as $key1 => $value1) {
            if ($k > 1) {
                $sql .= " or parent_code = {$key1} ";
            } else {
                $sql .= " parent_code = {$key1} ";
            }
            $k++;
        }
        $sql .= ")";
        $where = $this->check_acl_param_where();

        $sql .= $where . $version_where;

        $rs = $this->db->get_all($sql, $arr);
        //是否有批次库存
        $sql = "SELECT count(*) from goods_lof WHERE lof_no != 'default_lof';";
        $is_lof = $this->db->getOne($sql, $sql_values = array());
        $sql = "SELECT count(*) FROM b2b_lof_datail WHERE lof_no != 'default_lof';";
        $is_lof += $this->db->getOne($sql, $sql_values = array());

        $data = array();
        foreach ($rs as $value) {
            if ($value['form_desc'] <> '') {
                $value['form_desc'] = json_decode($value['form_desc'], true);
                $value['is_lof'] = $is_lof != 0 ? $is_lof : '';
            }
            $data[$value['parent_code']][] = $value;
        }
        return $data;
    }

    function check_acl_param_where() {
        $check_params = array('shop_power', 'store_power', 'brand_power', 'oms_notice', 'off_deliver_time', 'fanance_money', 'tran_order_auto_split', 'sys_params_is_policy_store');

        //sys_params_store_power
        $no_params = array();
        foreach ($check_params as $val) {
            $key = 'sys_params_' . $val;
            if (load_model('sys/PrivilegeModel')->check_priv($key, 1) === false) {
                $no_params[] = $val;
            }
        }
        if (empty($no_params)) {
            return '';
        } else {
            return "  AND  param_code not in('" . implode("','", $no_params) . "')";
        }
    }

    //获取父类
    function get_parent() {
        $sql = "select param_code,param_name,memo FROM {$this->table} where parent_code = '0' ";
        $rs = $this->db->get_all($sql);
        $data = array();
        foreach ($rs as $value) {
            $data[$value['param_code']]['param_name'] = $value['param_name'];
            $data[$value['param_code']]['memo'] = $value['memo'];
        }

        return $data;
    }

    //启用停用
    function update_active($active, $param_code) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent :: update(array('value' => $active), array('param_code' => $param_code));
        return $ret;
    }

    /**
     * 通过field_name查询
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
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
     * 服务器端验证
     */
    private function valid($data, $is_edit = false) {
        return 1;
    }

    /**
     * 获取指定配置模块下的子参数
     * @param string $module_code 父级参数代码
     * @return array
     */
    public function get_params_calss($module_code) {
        $sql = "SELECT param_code,param_name,memo FROM {$this->table} WHERE parent_code=:parent_code";
        $params = $this->db->get_all($sql, [':parent_code' => $module_code]);
        if (empty($params)) {
            return [];
        }
        $params = load_model('util/ViewUtilModel')->get_map_arr($params, 'param_code');

        return $params;
    }

    /**
     * 行业特性设置参数更新
     * @param array $param
     */
    public function update_param_value($param) {
        $where = ['param_code' => $param['param_code']];
        unset($param['param_code']);
        $ret = $this->update($param, $where);
        return $ret;
    }

    public function get_param_set($param_code) {
        $sql = 'SELECT data FROM sys_params WHERE param_code=:param_code';
        $data = $this->db->get_value($sql, [':param_code' => $param_code]);
        $data = empty($data) ? '' : $data;

        return $data;
    }
    /**
     * 排序三维数组
     * @param type $array 排序数组
     * @param type $field 排序字段
     * @param type $sort_type 排序类型
     */
    function array_sort($array, $field = 'sort', $sort_type = 'asc') {
        $field_arr = array();
        $sort_type = $sort_type == 'asc' ? SORT_ASC : SORT_DESC;
        foreach ($array as &$val) {
            foreach ($val as $key => $v) {
                $field_arr[$key] = $v[$field];
            }
            array_multisort($field_arr, $sort_type, $val);
        }
        return $array;
    }

}
