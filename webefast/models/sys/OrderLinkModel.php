<?php
/**
* 订单全链路 相关业务
*
* @author dfr
*/
require_lib('util/oms_util', true);
require_model('tb/TbModel');


class OrderLinkModel extends TbModel {
	
	//上传状态
    public $status = array(
        0 => '未上传',
        1 => '上传成功 ',
        2 => '上传失败',
    );
    
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'api_taobao_trade_trace';
    }

    function get_data_list($fld = 'id,tpl_name') {
        $sql = "select $fld from {$this->table} where 1";
        $arr = $this->db->get_all($sql);
        return $arr;
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
        
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
           $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND shop_code in ( ".$str." ) ";
        }

        if (isset($filter['efast_process_flag']) && $filter['efast_process_flag'] != '') {
            $sql_main .= " AND efast_process_flag = :efast_process_flag";
            $sql_values[':efast_process_flag'] = $filter['efast_process_flag'];
        }
        
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND status = :status";
            $sql_values[':status'] = $filter['status'];
        }

        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        
        foreach($data['data'] as $key => &$value){
        	
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));

            //上传状态
            $value['upload_status'] = $this->status[$value['efast_process_flag']];
            
            $link_state = require_conf('sys/order_link');
            $value['link_status'] =  $link_state[$value['status']];
            
    	}   
    	     
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $arr = $this->get_row(array('id' => $id));
        return $arr;
    }


   private function is_exists($value, $field_name = 'tpl_name') {
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

//        $ret = $this->is_exists($supplier['supplier_code']);
//        if ($ret['status'] > 0 && !empty($ret['data'])) {
//            return $this->format_ret('sms_supplier_error_unique_code');
//        }

        $ret = $this->is_exists($supplier['tpl_name'], 'tpl_name');
        if ($ret['status'] > 0 && !empty($ret['data'])) {
            return $this->format_ret('sms_tpl_error_unique_name');
        }

        return parent::insert($supplier);
    }


    /**
    * 删除记录
    */
    function delete($id) {
        $ret = parent :: delete(array('id' => $id));
        return $ret;
    }
    

}
    