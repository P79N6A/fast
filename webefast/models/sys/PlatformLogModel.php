<?php
/**
* 短信模板 相关业务
*
* @author dfr
*/
require_model('tb/TbModel');
require_lang('sys');

class PlatformLogModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'platform_log';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl  WHERE 1 ";
		
        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;

        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }



//   private function is_exists($value, $field_name = 'tpl_name') {
//        $ret = parent::get_row(array($field_name => $value));
//        return $ret;
//    }
    
    
 	/**
     * 添加新纪录
     */
    function insert($data) {
        $status = $this->valid($data);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $data['do_person'] = '';
        
//        $ret = $this->is_exists($supplier['supplier_code']);
//        if ($ret['status'] > 0 && !empty($ret['data'])) {
//            return $this->format_ret('sms_supplier_error_unique_code');
//        }

//        $ret = $this->is_exists($supplier['tpl_name'], 'tpl_name');
//        if ($ret['status'] > 0 && !empty($ret['data'])) {
//            return $this->format_ret('sms_tpl_error_unique_name');
//        }

        return parent::insert($data);
    }

}
    