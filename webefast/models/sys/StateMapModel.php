<?php
/**
* 订单全链路 相关业务
*
* @author dfr
*/
require_model('tb/TbModel');


class StateMapModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }
    

    function get_table() {
        return 'state_map';
    }


    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
		
        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        
        foreach($data['data'] as $key => &$value){
        	
            //全链路状态
            $link_state = require_conf('sys/order_link');
        	$value['status_text'] = $link_state[$value['link_state']];
        	$sys_state = require_conf('sys/link_state');
        	$value['sys_text'] = $sys_state[$value['sys_state']];
  
    	}           
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_sys_state($state) {
        $arr = $this->get_row(array('sys_state' => $state));
        return $arr;
    }
     /**
     * 修改纪录
     */
    function update($data, $id) {
        $status = $this->valid($data, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

//        $ret = $this->get_row(array('id' => $id));
//        if ($data['sys_state'] != $ret['data']['sys_state']) {
//            $ret = $this->is_exists($data['sys_state'], 'sys_state');
//            if ($ret['status'] > 0 && !empty($ret['data'])) {
//                return $this->format_ret('sys_state_error_unique');
//            }
//        }

        $ret = parent::update($data, array('id' => $id));
        return $ret;
    }

   private function is_exists($value, $field_name = 'tpl_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    

    /**
    * 删除记录
    */
    function delete($id) {
        $ret = parent :: delete(array('id' => $id));
        return $ret;
    }
    

}
    