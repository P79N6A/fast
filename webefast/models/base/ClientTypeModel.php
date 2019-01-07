<?php
/**
* 批发销货类型 相关业务
*
* @author huanghy
*/
require_model('tb/TbModel');
require_lang('sys');

class ClientTypeModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this -> get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'base_record_type';
    }

   /**
    * 根据条件查询数据
    */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
        if (isset($filter['record_type_code']) && $filter['record_type_code'] != '') {
            $sql_main .= " AND (record_type_code LIKE :record_type_code  OR record_type_name LIKE :record_type_code )";
            $sql_values[':record_type_code'] = $filter['record_type_code'] . '%';
        }

        
        //限制为批发销货类型
        $sql_main .= " AND record_type_property = 16";
        
        $select = '*';

        $data = $this -> get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this -> format_ret($ret_status, $ret_data);
    }

    /**
    * 添加新纪录
    */
    function insert($store_adjust_type) {
        $status = $this -> valid($store_adjust_type);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> is_exists($store_adjust_type['record_type_code']);
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(STOREADJUSTTYPE_ERROR_UNIQUE_CODE);

        $ret = $this -> is_exists($store_adjust_type['record_type_name'], 'record_type_name');
        if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(STOREADJUSTTYPE_ERROR_UNIQUE_NAME);

        return parent :: insert($store_adjust_type);
    }

    /**
    * 修改纪录
    */
    function update($store_adjust_type, $id) {
        $status = $this -> valid($store_adjust_type, true);
        if ($status < 1) {
            return $this -> format_ret($status);
        }

        $ret = $this -> get_row(array('record_type_id' => $id));
        if ($store_adjust_type['record_type_name'] != $ret['data']['record_type_name']) {
            $ret = $this -> is_exists($store_adjust_type['record_type_name'], 'record_type_name');
            if ($ret['status'] > 0 && !empty($ret['data'])) return $this -> format_ret(STOREADJUSTTYPE_ERROR_UNIQUE_NAME);
        }
        $ret = parent :: update($store_adjust_type, array('record_type_id' => $id));
        return $ret;
    }

    /**
    * 删除纪录
    */
    function delete($id) {
    	$used = $this->is_used_by_id($id);
    	if($used){
    		return $this->format_ret(-1,array(),'已经在业务系统中使用，不能删除！');
    	}
        $ret = parent :: delete(array('record_type_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'record_type_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }
    
    /**
     * 生成用于控件下来列表用的键值对数组
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-10-30
     */
    function get_code_to_conf(){
    	$filter = array();
        $result = $this->get_by_page($filter);
        $arr = array();
        foreach($result['data']['data'] as $val){
            $tmp = array();
            $tmp[0] = $val['record_type_code'];
            $tmp[1] = $val['record_type_name'];
            $arr[] = $tmp;
        }
        return $arr;
    }
    /**
     * 根据id判断在业务系统是否使用
     * @param int $id
     * @return boolean 已使用返回true, 未使用返回false
     */
    public function is_used_by_id($id) {
    	$result = $this->get_value("select record_type_code from {$this->table} where record_type_id=:id", array(':id' => $id));
    	$code = $result['data'];
    	$num = $this->get_num('select * from stm_stock_adjust_record where adjust_type=:code', array(':code' => $code));
    	if(isset($num['data'])&&$num['data']>0){
    		//已经在业务系统使用
    		return true;
    	}else{
    		//尚未在业务系统使用
    		return false;
    	}
    }
}
