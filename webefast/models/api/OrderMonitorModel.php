<?php
/**
 * 订单监控相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('api');
require_lib('util/taobao_util', true);

class OrderMonitorModel extends TbModel {

	protected $table = "api_order_monitor";
    protected $detail_table = "api_order_monitor_section";

    /*
     * 根据条件查询数据
    */
    function get_list_by_page($filter){
    	$wh = $this->get_list_where($filter);
    	$sql_main = $wh['sql_main'];
    	$sql_values = $wh['sql_values'];
    	$sql = "select sum(base_order_total) as base_order_total,sum(taobao_order_total) as taobao_order_total,monitor_start_time,monitor_end_time ";
    	$sql .= $sql_main;
    	$sql .= " GROUP BY monitor_start_time,monitor_end_time ";
    	$data = $this->db->get_all($sql,$sql_values);
    	$ret_status = OP_SUCCESS;
    	$ret_data['data'] = $data;
    	return $this->format_ret($ret_status, $ret_data);
    }
    
    function get_list_where($filter,$tb_name='detail_table'){
    	$sql_main = " FROM {$this->$tb_name} WHERE 1";
    	
    	$sql_values = array();
    	
    	// 店铺
    	if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
    		$arr = explode(',',$filter['shop_code']);
    		$str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
    		$sql_main .= " AND shop_code in ({$str}) ";
    	}
    	//监控日期
    	if (isset($filter['monitor_date']) && $filter['monitor_date'] != '') {
    		$sql_main .= " AND (monitor_date = :monitor_date )";
    		$sql_values[':monitor_date'] = $filter['monitor_date'];
    	} else {
    		$sql_main .= " AND (monitor_date = :monitor_date )";
    		$sql_values[':monitor_date'] = date('Y-m-d');
    	}
    			
    	return array('sql_main' => $sql_main,'sql_values' => $sql_values);
    }
    function total_amount_search($filter){
    	$sql = 'select sum(base_order_total) as base_order_total,sum(taobao_order_total) as taobao_order_total,max(insert_time) as insert_time';
    	$wh = $this->get_list_where($filter,'table');
    	$sql_main = $wh['sql_main'];
    	$sql_values = $wh['sql_values'];
    	$sql .= $sql_main;
    	$data = $this->db->get_row($sql,$sql_values);
    	$data['base_order_total'] = $data['base_order_total']?$data['base_order_total']:'';
    	$data['taobao_order_total'] = $data['taobao_order_total']?$data['taobao_order_total']:'';
    	$data['insert_time'] = $data['insert_time']?$data['insert_time']:'';
    	return $data;
    }
    function get_data_by_date($date){
                $sql_main = "select m.*,s.shop_name from api_order_monitor m "
                        . " INNER JOIN base_shop s ON s.shop_code=m.shop_code WHERE 1  ";
    		$sql_main .= " AND monitor_date = :monitor_date ";
    		$sql_values[':monitor_date'] = $date ;

               $data = $this->db->get_all($sql_main,$sql_values);
               return $this->format_ret(1,$data);
    }
    
	
	
}
