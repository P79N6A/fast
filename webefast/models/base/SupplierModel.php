<?php

/**
 * 供应商类型相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('base');
require_lib('util/oms_util', true);

class SupplierModel extends TbModel {

    private $user_id = 0;
    private $is_manage = -1;

    function get_table() {
        return 'base_supplier';
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
            $sql_main .= " AND (rl.supplier_code LIKE :code_name or rl.supplier_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        //快速选择供应商受角色的权限控制，通过传参来判断
        if($filter['supplier_power']){
            $supplier_arr = $this->get_purview_supplier();
            if(!empty($supplier_arr)){
                $supplier_code_arr =array();
                foreach($supplier_arr as $key => $val){
                    $supplier_code_arr[] = $val['supplier_code'];
                }
                $code_arr = $this->arr_to_in_sql_value($supplier_code_arr, 'supplier_code', $sql_values);
                $sql_main .= " AND rl.supplier_code in({$code_arr}) ";
            }else{
                $sql_main .= " AND 1=2 ";
            }
        }

        $select = 'rl.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            $country = oms_tb_val('base_area', 'name', array('id' => $val['country']));
            $province = oms_tb_val('base_area', 'name', array('id' => $val['province']));
            $city = oms_tb_val('base_area', 'name', array('id' => $val['city']));
            $district = oms_tb_val('base_area', 'name', array('id' => $val['district']));
            $street = oms_tb_val('base_area', 'name', array('id' => $val['street']));
            $val['addr'] = $country .$province . $city . $district . $street . $val['address'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * @param $id
     * @return array
     */
    function get_by_id($id) {

        return $this->get_row(array('supplier_id' => $id));
    }

    /**
     * @param $code
     * @return array
     */
    function get_by_code($code) {
        return $this->get_row(array('supplier_code' => $code));
    }

    /**
     * 通过field_name查询
     *
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

    /*
     * 添加新纪录
     */

    function insert($spec1) {
        $status = $this->valid($spec1);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($spec1['supplier_code']);

        if (!empty($ret['data'])) {
            return $this->format_ret('-1', '', 'SUPPLIER_TYPE_ERROR_UNIQUE_CODE');
        }
        /*
          $ret = $this->is_exists($spec1['spec1_name'], 'spec1_name');
          if (!empty($ret['data'])) return $this->format_ret(SPEC1_ERROR_UNIQUE_NAME);
         */

        return parent::insert($spec1);
    }

    /*
     * 删除记录
     * */

    function delete($supplier_id) {
        $used = $this->is_used_by_id($supplier_id);
        if ($used) {
            return $this->format_ret(-1, array(), '已经在业务系统中使用，不能删除！');
        }
        $ret = parent::delete(array('supplier_id' => $supplier_id));
        return $ret;
    }

    /*
     * 修改纪录
     */

    function update($spec1, $spec1_id, $type = 'base') {
        $status = $this->valid($spec1, true, $type);
        if ($status < 1) {
            return $this->format_ret($status);
        }


        $ret = parent::update($spec1, array('supplier_id' => $spec1_id));
        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false, $type = 'base') {
        if (!$is_edit && (!isset($data['supplier_code']) || !valid_input($data['supplier_code'], 'required')))
            return SUPPLIER_ERROR_CODE;
        if( $type == 'base'){
            if (!isset($data['supplier_name']) || !valid_input($data['supplier_name'], 'required'))
                return SUPPLIER_ERROR_NAME;
        }
        return 1;
    }

    function is_exists($value, $field_name = 'supplier_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    /**
     * 根据id判断在业务系统是否使用
     * @param int $id
     * @return boolean 已使用返回true, 未使用返回false
     */
    public function is_used_by_id($id) {
        $result = $this->get_value("select supplier_code from {$this->table} where supplier_id=:id", array(':id' => $id));
        $code = $result['data'];
        $planned_record = $this->get_num('select * from pur_planned_record where supplier_code=:code', array(':code' => $code));
        $order_record = $this->get_num('select * from pur_order_record where supplier_code=:code', array(':code' => $code));
        $purchaser_record = $this->get_num('select * from pur_purchaser_record where supplier_code=:code', array(':code' => $code));
        $return_notice_record = $this->get_num('select * from pur_return_notice_record where supplier_code=:code', array(':code' => $code));
        $return_record = $this->get_num('select * from pur_return_record where supplier_code=:code', array(':code' => $code));
        if (
                isset($planned_record['data']) && $planned_record['data'] > 0 ||
                isset($order_record['data']) && $order_record['data'] > 0 ||
                isset($purchaser_record['data']) && $purchaser_record['data'] > 0 ||
                isset($return_notice_record['data']) && $return_notice_record['data'] > 0 ||
                isset($return_record['data']) && $return_record['data'] > 0
        ) {
            //已经在业务系统使用
            return true;
        } else {
            //尚未在业务系统使用
            return false;
        }
    }

    /*
     * 获取用户信息
     */

    function set_user_manage() {
        if ($this->is_manage < 0) {
            $this->user_id = CTX()->get_session('user_id');
            if (empty($this->user_id)) {
                $user_code = load_model('sys/UserTaskModel')->get_user_code();
                $sql_user = "select user_id,is_manage from sys_user where user_code=:user_code";
                $sql_values = array(':user_code' => $user_code);
                $user_row = $this->db->get_row($sql_user, $sql_values);
                $this->user_id = $user_row['user_id'];
            }
            $this->is_manage = 0;
            $sql_role = "select r.role_code from  sys_role r
                    INNER JOIN sys_user_role u ON r.role_id=u.role_id
                    where r.role_code='manage' AND u.user_id=:user_id ";
            $sql_values2 = array(':user_id' => $this->user_id);
            $role_row = $this->db->get_row($sql_role, $sql_values2);
            if (!empty($role_row)) {
                $this->is_manage = 1;
            }
        }
    }

    /**
     * 取出有权限的供应商
     */
    function get_purview_supplier($fld = 'supplier_code,supplier_name') {
        $this->set_user_manage();

        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('supplier_power'));
        $supplier_power = $ret_cfg['supplier_power'];

        $sql = "select $fld FROM {$this->table} t where 1=1";
        $version_no = load_model('sys/SysAuthModel')->product_version_no();
        if ((int) $this->is_manage == 0 && $supplier_power == 1 && $version_no > 0) {
            $supplier_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id, 4);
            $supplier_code = '';
            if (!empty($supplier_code_arr)) {
                $supplier_code = deal_array_with_quote($supplier_code_arr);
            }

            if (empty($supplier_code)) {
                return array();
            } else {
                $sql .=" and supplier_code in ({$supplier_code})";
            }
        }

        $rs = $this->db->get_all($sql);
        return $rs;
    }

    /**
     * 取出有权限的供应商 拼装SQL时用
     * $fld sql字段名 多表查的要传参如r1.supplier_code ,
     * $req_code 客户端传来的supplier_code（要去掉客户端传来没权限的supplier_code）
     * @return array()
     */
    function get_sql_purview_supplier($fld = 'supplier_code', $req_code = null) {

        $this->set_user_manage();

        if ((int) $this->is_manage == 1 && empty($req_code)) {
            return '';
        }

        $ret = $this->get_purview_supplier();

        $req_supplier_code_arr = array();
        if (!empty($req_code)) {
            $req_supplier_code_arr = explode(',', $req_code);
        }

        $supplier_code_arr = array();
        foreach ($ret as $sub_ret) {
            $supplier_code_arr[] = $sub_ret['supplier_code'];
        }
        if (empty($supplier_code_arr)) {
            $str = " and 1!=1 ";
        } else {
            if (!empty($req_supplier_code_arr)) {
                $supplier_code_arr = array_intersect($supplier_code_arr, $req_supplier_code_arr);
            }
            if (empty($supplier_code_arr)) {
                $str = " and 1!=1 ";
            } else {
                $str = ' and ' . $fld . ' in (\'' . join("','", $supplier_code_arr) . '\')';
            }
        }
        return $str;
    }

    /**
     * 取出有权限的供应商，详情页显示
     * @return type
     */
    function get_view_select() {
        $rs = $this->get_purview_supplier();
        $supplier_arr = array();
        foreach ($rs as $val) {
            $supplier_arr[$val['supplier_code']] = $val['supplier_name'];
        }
        return json_encode(bui_bulid_select($supplier_arr));
    }

    /**
     * 取出有权限的供应商
     * @return type
     */
    function get_select($type = 0) {
        $data = $this->get_purview_supplier();
        if ($type == 1)
            $data = array_merge(array(array('', '全部')), $data);
        else if ($type == 2)
            $data = array_merge(array(array('', '请选择')), $data);
        else if ($type == 3)
            $data = array_merge(array(array('', '...')), $data);
        return $data;
    }

    /**
     * @todo 获取系统中存在的所有供货商
     */
    function get_all_supplier() {
        $new_db = array();
        $sql = "SELECT supplier_code FROM {$this->table}";
        $db = $this->db->get_all($sql);
        foreach ($db as $key => $supplier) {
            $new_db[$key] = $supplier['supplier_code'];
        }
        return $new_db;
    }
    function get_supplier_select($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} bs where 1";

        if (isset($filter['supplier_name']) && $filter['supplier_name'] != '') {
            $sql_main .= " AND ( bs.supplier_name LIKE :supplier_name or bs.supplier_code LIKE :supplier_name ) ";
            $sql_values[':supplier_name'] = "%{$filter['supplier_name']}%";
        }
        //快速选择供应商检索项受角色的权限控制，通过传参来判断
        if(isset($filter['supplier_power']) && $filter['supplier_power'] == 1){
            $sql_main .= $this->get_sql_purview_supplier('bs.supplier_code'); 
        }
        $sql_main .= " ORDER BY bs.supplier_code ";
        $select = 'bs.supplier_name,bs.supplier_code';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    /**
     * @todo 获取系统中存在的所有供货商
     */
    function get_supplier_arr() {
        $new_db = array();
        $sql = "SELECT supplier_code,supplier_name FROM {$this->table}";
        $db = $this->db->get_all($sql);
        foreach ($db as $supplier) {
            $new_db[$supplier['supplier_code']] = $supplier['supplier_name'];
        }
        return $new_db;
    }
}
