<?php
require_model('tb/TbModel');
class ReportBaseOrderCollectModel extends TbModel
{
    protected $table = 'report_base_order_collect';

    public function shop_report_data_analyse($filter){
    	//去除查询条件为全部的   	
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] == 'select_all') {
            unset($filter['sale_channel_code']);
    	}
    	if (isset($filter['shop_code']) && $filter['shop_code'] == 'all') {
            unset($filter['shop_code']);
    	}
    	$sql_values = array();
    	$sql_main = "FROM {$this->table} r1 WHERE 1 ";
    	//店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('r1.shop_code', $filter_shop_code);
    	//销售平台
    	if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
    	        $arr = explode(',', $filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
    		$sql_main .= " AND r1.sale_channel_code in ( " . $str. " ) ";
    	}
    	if (isset($filter['pay_time_start']) && $filter['pay_time_start'] !== '') {
    		$sql_main .= " AND r1.biz_date >= :pay_time_start ";
    		$sql_values[':pay_time_start'] = $filter['pay_time_start'];
    	}
    	if (isset($filter['pay_time_end']) && $filter['pay_time_end'] !== '') {
    		$sql_main .= " AND r1.biz_date <= :pay_time_end ";
    		$sql_values[':pay_time_end'] = $filter['pay_time_end'];
    	}
    	$select = 'r1.*';
    	$sql_main .= "ORDER BY biz_date DESC";
    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach($data['data'] as &$val){
            $ret =  load_model('base/ShopModel')->get_by_code($val['shop_code']);
            $val['shop_name'] = $ret['data']['shop_name'];
        }
    	return $this->format_ret(1, $data);
    }
}
?>