<?php
require_lib('util/oms_util', true);
require_model('oms/SellRecordModel', true);

class SellShopDataAnalysisModel extends TbModel
{
	protected $table = 'shop_data_analysis';
    private $filter = array();

    public function shop_report_data_analyse($filter){ 
        $this->filter = $filter;
        $this->check_date();//按日期店铺查询表，看是否有数据，无该天数据创建
    	//去除查询条件为全部的   	
    	if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] == 'select_all') {
    		unset($filter['sale_channel_code']);
    	}
    	if (isset($filter['shop_code']) && $filter['shop_code'] == 'all') {
    		unset($filter['shop_code']);
    	}
    	$sql_values = array();
    	$sql_join = "";
    	$sql_main = "FROM {$this->table} r1 $sql_join WHERE 1 ";
    	
    	//销售平台
    	if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
    		       $arr = explode(',', $filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
    		$sql_main .= " AND r1.sale_channel_code in ( " . $str . " ) ";
    	}
    	//店铺
    	if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
    	             $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
    		$sql_main .= " AND r1.shop_code in ( " . $str. " ) ";
    	}
    	
    	if (isset($filter['pay_time_start']) && $filter['pay_time_start'] !== '') {
    		$sql_main .= " AND r1.summary_date >= :pay_time_start ";
    		$sql_values[':pay_time_start'] = $filter['pay_time_start'];
    	}
    	if (isset($filter['pay_time_end']) && $filter['pay_time_end'] !== '') {
    		$sql_main .= " AND r1.summary_date <= :pay_time_end ";
    		$sql_values[':pay_time_end'] = $filter['pay_time_end'];
    	}
    	$select = 'r1.*';
    	$order_by = "ORDER BY summary_date DESC";
    	$sql_main .= $order_by;
//     	var_dump($sql_main);var_dump($sql_values);
    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
    	foreach ($data['data'] as $key => &$value) {
    		$value['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code'=>$value['sale_channel_code']));
    		$value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
    	}
//     	var_dump($data);
    	return $this->format_ret(1, $data);
    }
    
    private function check_date(){
    	
        $filter = $this->filter ;
        $time_start = $filter['pay_time_start'];
        $time_end = $filter['pay_time_end'];
        if ($time_start < $time_end) {
        	$day_length = ceil((strtotime($time_end)-strtotime($time_start))/(3600*24));
        }
        $data = array();
        //天数，查询 数据库表是否有数据
        for ($i = 0; $i <= $day_length; $i++ ) {  
        	$summary_time = strtotime($time_start) + 3600*24*$i;
        	$summary_day = date("Y-m-d", $summary_time);
        	//如果传了shop_code
        	if ($filter['shop_code']) {
        		$key = $summary_day."_".$filter['shop_code'];
        		$record = $this->get_shop_code_data($summary_day, $filter['shop_code']);
        		
        		if (!$record['summary_date']) {
        			$data[$key] = $this->create_shop_report($summary_day, $filter['shop_code']);
        		} else {
        			$this->update_summary_data($record, $summary_day, $filter['shop_code']);
        		}
        	} else {
        		//未传shop_code 获取每个店铺的汇总信息
        		$shops = load_model('base/ShopModel')->get_purview_shop();
        		foreach ($shops as $shop) {
        			$key = $summary_day."_".$shop['shop_code'];
        			$record = $this->get_shop_code_data($summary_day, $shop['shop_code']);
        			if (!$record['summary_date']) {
        				$data[$key] = $this->create_shop_report($summary_day, $shop['shop_code']);
        			} else {
        				//实时更新数据 剩余未发货订单量、剩余缺货订单量、当天汇总信息
        				$this->update_summary_data($record,$summary_day,$shop['shop_code']);
        			}
        		}
        	}
        }
        return $data;
    }
    
    private function update_summary_data($record,$summary_day,$shop_code) {
    	if ($record['no_send_order_num'] > 0) {
    		//实时更新剩余未发货订单量
    		$no_send_order_summary = $this->get_no_send_order_summary($summary_day, $filter['shop_code'], 'remaining');
    		$this->update(array('remaining_un_send_order_num' => $no_send_order_summary['no_send_order_num']),
    				array('summary_date' => $summary_day, 'shop_code' => $shop_code));
    	}
    	if ($record['short_order_num'] > 0) {
    		//实时更新剩余缺货订单量
    		$short_order_summary = $this->get_short_order_summary($summary_day, $filter['shop_code'], 'remaining');
    		$this->update(array('remaning_short_order_num' => $no_send_order_summary['short_order_num']),
    				array('summary_date' => $summary_day, 'shop_code' => $shop_code));
    	}
    	//更新当天汇总信息
    	if ($record['summary_date'] == date("Y-m-d")){
    		$this->create_shop_report($summary_day, $shop_code,'update');
    	
    	}
    }
    
    
   
    
    //查询该天店铺汇总信息
    public function get_summary_date_data($summary_day, $shop_code){
        $filter = $this->filter ;
        $sql = "select * from shop_data_analysis where summary_date = :summary_date and shop_code = :shop_code";
        $record = ctx()->db->get_row($sql, array(':summary_date' => $summary_day, ':shop_code' => $shop_code));
        return $record;
    }
    //查询该店铺是否有该天的汇总数据
    private function get_shop_code_data($summary_day, $shop_code){
        $sql = "select summary_date from shop_data_analysis where summary_date = :summary_date and shop_code = :shop_code";
        $record = ctx()->db->get_row($sql, array(':summary_date' => $summary_day, ':shop_code' => $shop_code));
        return $record;
        //多少天
    }
   
    //创建数据
    public function create_shop_report($date,$shop_code='', $type=''){
        $order_data = array();
        //总订单汇总信息
    	$order_summary = $this->get_order_summary($date,$shop_code);
    	$order_data['summary_date'] = isset($date)? $date : '' ;
    	$order_data['order_total_num'] = isset($order_summary['order_total_num']) ? $order_summary['order_total_num'] : 0;
    	$order_data['goods_total_num'] = isset($order_summary['goods_total_num']) ? $order_summary['goods_total_num'] : 0;
    	
    	$order_data['shipping_fee'] = isset($order_summary['shipping_fee']) ? $order_summary['shipping_fee'] : 0;
    	$order_data['sale_total_money'] = isset($order_summary['sale_total_money']) ? $order_summary['sale_total_money'] : 0;
    	$order_data['shop_code'] = isset($shop_code) ? $shop_code : '';
    	$sale_channel_code = load_model('base/ShopModel')->get_by_field('shop_code',$shop_code, 'sale_channel_code');
    	$order_data['sale_channel_code'] = isset($sale_channel_code['data']['sale_channel_code']) ? $sale_channel_code['data']['sale_channel_code'] : '';
    	
    	//已发货汇总信息
    	$send_order_summary = $this->get_send_order_summary($date, $shop_code);
    	$order_data['send_order_num'] = isset($send_order_summary['send_order_num']) ? $send_order_summary['send_order_num'] : 0;
    	$order_data['send_goods_num'] = isset($send_order_summary['send_goods_num']) ? $send_order_summary['send_goods_num'] : 0;
    	$order_data['send_sell_total_price'] = isset($send_order_summary['send_sell_total_price']) ? $send_order_summary['send_sell_total_price'] : 0;
    	$order_data['send_avg_total_money'] = isset($send_order_summary['send_avg_total_money']) ? $send_order_summary['send_avg_total_money'] : 0;

		//未发货汇总信息
		$no_send_order_summary = $this->get_no_send_order_summary($date, $shop_code);
		$order_data['no_send_order_num'] = isset($no_send_order_summary['no_send_order_num']) ? $no_send_order_summary['no_send_order_num'] : 0;
		$order_data['no_send_goods_distinct_num'] = isset($no_send_order_summary['no_send_goods_distinct_num']) ? $no_send_order_summary['no_send_goods_distinct_num'] : 0;
		$order_data['no_send_goods_num'] = isset($no_send_order_summary['no_send_goods_num']) ? $no_send_order_summary['no_send_goods_num'] : 0;
		$order_data['no_send_sell_total_price'] = isset($no_send_order_summary['no_send_sell_total_price']) ? $no_send_order_summary['no_send_sell_total_price'] : 0;
		$order_data['no_send_avg_total_money'] = isset($no_send_order_summary['no_send_avg_total_money']) ? $no_send_order_summary['no_send_avg_total_money'] : 0;
		$order_data['remaining_un_send_order_num'] = isset($no_send_order_summary['no_send_order_num']) ? $no_send_order_summary['no_send_order_num'] : 0;
		//缺货汇总信息
		$short_order_summary = $this->get_short_order_summary($date, $shop_code);
		$order_data['short_order_num'] = isset($short_order_summary['short_order_num']) ? $short_order_summary['short_order_num'] : 0;
		$order_data['short_goods_num'] = isset($short_order_summary['short_goods_num']) ? $short_order_summary['short_goods_num'] : 0;
		$order_data['short_sell_total_price'] = isset($short_order_summary['short_sell_total_price']) ? $short_order_summary['short_sell_total_price'] : 0;
		$order_data['short_avg_total_money'] = isset($short_order_summary['short_avg_total_money']) ? $short_order_summary['short_avg_total_money'] : 0;
		$order_data['remaning_short_order_num'] = isset($short_order_summary['short_order_num']) ? $short_order_summary['short_order_num'] : 0;
		//取消汇总信息
		$cancel_order_summary = $this->get_cancel_order_summary($date, $shop_code);
		$order_data['cancel_order_num'] = isset($cancel_order_summary['cancel_order_num']) ? $cancel_order_summary['cancel_order_num'] : 0;
		$order_data['cancel_sell_total_price'] = isset($cancel_order_summary['cancel_sell_total_price']) ? $cancel_order_summary['cancel_sell_total_price'] : 0;
		$order_data['cancel_avg_total_money'] = isset($cancel_order_summary['cancel_avg_total_money']) ? $cancel_order_summary['cancel_avg_total_money'] : 0;
		
		//退货汇总信息
		$return_order_summary = $this->get_return_order_summary($date, $shop_code);
		$order_data['return_order_num'] = isset($return_order_summary['return_order_num']) ? $return_order_summary['return_order_num'] : 0;
		$order_data['return_goods_num'] = isset($return_order_summary['return_goods_num']) ? $return_order_summary['return_goods_num'] : 0;
		$order_data['return_sell_total_price'] = isset($return_order_summary['return_sell_total_price']) ? $return_order_summary['return_sell_total_price'] : 0;
		$order_data['return_avg_total_money'] = isset($return_order_summary['return_avg_total_money']) ? $return_order_summary['return_avg_total_money'] : 0;
		
		//剩余未发货订单量
		
// 		if ($order_data['order_total_num'] > 0){
// 			$order_data['no_send_goods_percentage'] = sprintf("%.2f", $order_data['no_send_order_num']/$order_data['order_total_num']);
// 			$order_data['send_goods_percentage'] = sprintf("%.2f", $order_data['send_order_num']/$order_data['order_total_num']);
// 			$order_data['cancel_goods_percentage'] = sprintf("%.2f", $order_data['cancel_order_num']/$order_data['order_total_num']);
// 		} else {
// 			$order_data['no_send_goods_percentage'] = 0;
// 			$order_data['send_goods_percentage'] = 0;
// 			$order_data['cancel_goods_percentage'] = 0;
// 		}
// 		var_dump($order_data);exit();
		if ($type == 'update') {
			$this->update($order_data, array('summary_date' => $date, 'shop_code' => $shop_code));
		} else {
			$result = $this->db->insert('shop_data_analysis', $order_data);
		}
		
		
		return $order_data;
		
    }
    
   //总订单汇总信息
   private function get_order_summary($date, $shop_code)
   {
   		$sql = "SELECT count(*) as order_total_num,sum(t1.goods_num) as goods_total_num, sum(t1.payable_money-t1.express_money) as sale_total_money, sum(t1.express_money) as shipping_fee
    			FROM oms_sell_record t1
    			WHERE shop_code = :shop_code 
   				AND (t1.pay_time >= :pay_time_start 
	   				AND t1.pay_time <= :pay_time_end )
   				OR (t1.pay_code='cod' 
   					AND t1.record_time >= :pay_time_start 
	   				AND t1.record_time <= :pay_time_end )";
   		$order_summary = $this->db->get_row($sql, array(':pay_time_start' => $date . ' 00:00:00',
										   			':pay_time_end' => $date . ' 23:59:59',
										   			':shop_code' => $shop_code));
   		return $order_summary;
   }
   
   //已发货汇总信息
   private function get_send_order_summary ($date, $shop_code)
   {
   		$sql = "SELECT count(*) as send_order_num,
			    	sum(t1.goods_num) as send_goods_num,
			    	sum(t1.goods_money) as send_sell_total_price,
			    	sum(t1.payable_money-t1.express_money) as send_avg_total_money
			    FROM oms_sell_record t1
			    WHERE (t1.shipping_status = 4
	   				AND shop_code = :shop_code
	   				AND t1.pay_time >= :pay_time_start
	    			AND t1.pay_time <= :pay_time_end) 
   				OR (t1.shipping_status = 4
   					AND shop_code = :shop_code
   					AND t1.pay_code='cod' 
   					AND t1.record_time >= :pay_time_start 
	   				AND t1.record_time <= :pay_time_end )
    			";
   		$send_order_summary = CTX()->db->get_row($sql, array(':pay_time_start' => $date . ' 00:00:00',
												   			':pay_time_end' => $date . ' 23:59:59',
												   			':shop_code' => $shop_code));
   		return $send_order_summary;
   }
   
   //未发货汇总信息 $remaining  表示还剩余未发货的
   private function get_no_send_order_summary ($date, $shop_code, $remaining= '')
   {
   		$sql = "SELECT count(distinct t1.sell_record_code) as no_send_order_num
				FROM oms_sell_record t1,oms_sell_record_detail t2
				WHERE (t1.sell_record_code = t2.sell_record_code
   				AND t1.shipping_status < 4 and t1.order_status<>3
   				AND shop_code = :shop_code
				
    			";
   		$sql_detail = "SELECT count(distinct barcode) as no_send_goods_distinct_num,
					sum(t2.num) as no_send_goods_num,
					sum(t2.num*t2.goods_price) as no_send_sell_total_price,
					sum(t2.avg_money) as no_send_avg_total_money
				FROM oms_sell_record t1,oms_sell_record_detail t2
				WHERE (t1.sell_record_code = t2.sell_record_code
   				AND t1.shipping_status < 4 and t1.order_status<>3
   				AND shop_code = :shop_code
   				";
   		
   		$sql_values = array(':pay_time_start' => $date . ' 00:00:00',':shop_code' => $shop_code);
   		if (!$remaining){
   			$sql .= "AND t1.pay_time >= :pay_time_start AND t1.pay_time <= :pay_time_end) 
   					OR (
   					t1.sell_record_code = t2.sell_record_code
	   				AND t1.shipping_status < 4 and t1.order_status<>3
	   				AND shop_code = :shop_code
   					AND t1.pay_code='cod' 
   					AND t1.record_time >= :pay_time_start 
	   				AND t1.record_time <= :pay_time_end )";
   			$sql_detail .= "AND t1.pay_time >= :pay_time_start AND t1.pay_time <= :pay_time_end)
   					OR (t1.sell_record_code = t2.sell_record_code
	   				AND t1.shipping_status < 4 and t1.order_status<>3
	   				AND shop_code = :shop_code 
   					AND t1.pay_code='cod' 
   					AND t1.record_time >= :pay_time_start 
	   				AND t1.record_time <= :pay_time_end )";
   			$sql_values[':pay_time_end'] = $date . ' 23:59:59';
   		} else {
   			$sql .= "AND t1.pay_time >= :pay_time_start) 
   					OR (t1.sell_record_code = t2.sell_record_code
	   				AND t1.shipping_status < 4 and t1.order_status<>3
	   				AND shop_code = :shop_code
   					AND t1.pay_code='cod' 
   					AND t1.record_time >= :pay_time_start)";
   			$sql_detail .= "AND t1.pay_time >= :pay_time_start) 
   					OR (t1.sell_record_code = t2.sell_record_code
	   				AND t1.shipping_status < 4 and t1.order_status<>3
	   				AND shop_code = :shop_code 
   					AND t1.pay_code='cod' 
   					AND t1.record_time >= :pay_time_start)";
   		}
   		
   		$no_send_order_summary = CTX()->db->get_row($sql, $sql_values);
   		$no_send_order_detail_summary = CTX()->db->get_row($sql_detail, $sql_values);
   		$no_send_order_detail_summary['no_send_order_num'] = $no_send_order_summary['no_send_order_num'];
   		return $no_send_order_detail_summary;
   }
   
   //缺货汇总信息
   private function get_short_order_summary($date, $shop_code, $remaining= '')
   {
   		$sql = "SELECT
				   	count(DISTINCT(t1.sell_record_code)) as short_order_num,
				   	sum(t2.num-t2.lock_num) as short_goods_num,
				   	sum((t2.num-t2.lock_num)*t2.goods_price) as short_sell_total_price,
				   	sum(t2.avg_money/t2.num*(t2.num-t2.lock_num)) as short_avg_total_money
				   	FROM oms_sell_record t1,oms_sell_record_detail t2
				   	WHERE (t1.shipping_status < 4
				   	AND t1.sell_record_code = t2.sell_record_code
				   	AND t1.order_status <> 3
				   	AND must_occupy_inv = 1
				   	AND t1.lock_inv_status <>1
				   	AND t2.is_delete = 0
   					AND shop_code = :shop_code
				";
   		$sql_values = array(':pay_time_start' => $date . ' 00:00:00', ':shop_code' => $shop_code);
   		if (!$remaining){
   			$sql .= " AND t1.pay_time >= :pay_time_start
   					AND t1.pay_time <= :pay_time_end) 
   					OR (t1.shipping_status < 4
				   	AND t1.sell_record_code = t2.sell_record_code
				   	AND t1.order_status <> 3
				   	AND must_occupy_inv = 1
				   	AND t1.lock_inv_status <>1
				   	AND t2.is_delete = 0
   					AND shop_code = :shop_code
   					AND t1.pay_code='cod' 
   					AND t1.record_time >= :pay_time_start 
	   				AND t1.record_time <= :pay_time_end )";
   			$sql_values[':pay_time_end'] = $date . ' 23:59:59';
   		} else {
   			$sql .= " AND t1.pay_time >= :pay_time_start)
   					OR (t1.shipping_status < 4
				   	AND t1.sell_record_code = t2.sell_record_code
				   	AND t1.order_status <> 3
				   	AND must_occupy_inv = 1
				   	AND t1.lock_inv_status <>1
				   	AND t2.is_delete = 0
   					AND shop_code = :shop_code
   					AND t1.pay_code='cod' 
   					AND t1.record_time >= :pay_time_start)";
   		}
   		$short_order_summary = CTX()->db->get_row($sql, $sql_values);
   		return $short_order_summary;
   }
   
   //取消汇总信息
   private function get_cancel_order_summary($date, $shop_code)
   {
   		$sql = "SELECT
					count(*) as cancel_order_num,
					sum(t1.goods_money) as cancel_sell_total_price,
					sum(t1.payable_money-t1.express_money) as cancel_avg_total_money
				FROM oms_sell_record t1
				WHERE (t1.order_status = 3
   				AND shop_code = :shop_code 
				AND t1.pay_time >= :pay_time_start
    			AND t1.pay_time <= :pay_time_end)
   				OR (t1.order_status = 3
   				AND shop_code = :shop_code 
   				AND t1.pay_code='cod' 
   				AND t1.record_time >= :pay_time_start 
	   			AND t1.record_time <= :pay_time_end )";
   		$cancel_order_summary = CTX()->db->get_row($sql, array(':pay_time_start' => $date . ' 00:00:00',
													   			':pay_time_end' => $date . ' 23:59:59',
													   			':shop_code' => $shop_code));
   		return $cancel_order_summary;
   }
   
   //退货汇总信息
   private function get_return_order_summary($date, $shop_code)
   {
   		$sql = "SELECT
					sum(t2.note_num) as return_goods_num,
					sum(t2.note_num * t2.goods_price) as return_sell_total_price";
   		$sql_where = " FROM oms_sell_return t1,oms_sell_return_detail t2,oms_sell_record t3
				WHERE (t1.sell_return_code = t2.sell_return_code
				AND t1.sell_record_code = t3.sell_record_code
				AND t1.return_order_status < 3
   				AND t3.shop_code = :shop_code
				AND t3.pay_time >= :pay_time_start
    			AND t3.pay_time <= :pay_time_end) 
   				OR (t1.sell_record_code = t2.sell_record_code
					AND t1.sell_record_code = t3.sell_record_code
					AND t1.return_order_status < 3
	   				AND t3.shop_code = :shop_code
	   				AND t3.pay_code='cod' 
   					AND t3.record_time >= :pay_time_start 
	   				AND t3.record_time <= :pay_time_end )
    			";
   	
   		$short_order_summary = CTX()->db->get_row($sql.$sql_where, array(':pay_time_start' => $date . ' 00:00:00',
												   			':pay_time_end' => $date . ' 23:59:59',
												   			':shop_code' => $shop_code));
   	
   		$sql1 = "select COUNT(DISTINCT(c.a)) as return_order_num,SUM(c.b) as return_avg_total_money
   				 FROM
					(select t1.sell_record_code as a,t1.return_avg_money as b
					FROM oms_sell_return t1,oms_sell_return_detail t2,oms_sell_record t3
					WHERE (t1.sell_return_code = t2.sell_return_code
					AND t1.sell_record_code = t3.sell_record_code
					AND t1.return_order_status < 3
   					AND t3.shop_code = :shop_code
					AND t3.pay_time >= :pay_time_start
	    			AND t3.pay_time <= :pay_time_end)
   					OR (t1.sell_record_code = t2.sell_record_code
					AND t1.sell_record_code = t3.sell_record_code
					AND t1.return_order_status < 3
   					AND t3.shop_code = :shop_code
   					AND t3.pay_code='cod' 
   					AND t3.record_time >= :pay_time_start 
	   				AND t3.record_time <= :pay_time_end )
					GROUP BY t1.sell_return_code) as c";
   		$short_order_summary1 = CTX()->db->get_row($sql1, array(':pay_time_start' => $date . ' 00:00:00',
											   				':pay_time_end' => $date . ' 23:59:59',
											   				':shop_code' => $shop_code));
   		$short_order_summary['return_order_num'] = $short_order_summary1['return_order_num'];
   		$short_order_summary['return_avg_total_money'] = $short_order_summary1['return_avg_total_money'];
   		return $short_order_summary;
   }    
}