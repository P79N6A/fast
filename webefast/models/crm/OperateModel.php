<?php
/**
 * 会员相关业务
 *
 * @author huanghy
 *
 */
require_model('tb/TbModel');
require_lang('crm');
require_lib('util/oms_util', true);

class OperateModel extends TbModel {
	function get_report_data(){
		/*
		$sql = "
		select order_sale_money FROM report_base_order_collect
		WHERE biz_date BETWEEN  '2015-07-13' AND '2015-07-13'  ;";
		$ret = $this->db->getAll($sql);
		$a = 0;
		foreach ($ret as $row) {
			$a += $row['order_sale_money'];
		}
		echo $a;die;
		$sql = "select sum(order_sale_money) as order_sale_money FROM report_base_order_collect
		WHERE biz_date BETWEEN  '2015-07-13' AND '2015-07-13'";
		$ret = $this->db->getRow($sql);
		print_r($ret);die;*/
		
		
		$end_time = date('Y-m-d',strtotime('-1 day'));
		$start_time = date('Y-m-d',strtotime('-7 day'));
		$sql = "SELECT * FROM 
			(
			SELECT biz_date, 
			SUM(order_sale_count) AS order_sale_count,
			SUM(report_base_order_collect.order_sale_money) AS order_sale_money,
			SUM(report_base_order_collect.goods_sale_count) AS goods_sale_count,
			SUM(report_base_order_collect.order_shipping_goods_count) AS order_shipping_goods_count,  
			SUM(report_base_order_collect.order_shipping_count) AS order_shipping_count,
			SUM(report_base_order_collect.order_shipping_money) AS order_shipping_money, 
			SUM(report_base_order_collect.refund_apply_count) AS refund_apply_count,
			SUM(report_base_order_collect.refund_apply_money) AS refund_apply_money,
			SUM(report_base_order_collect.refund_return_goods_order_count) AS refund_return_goods_order_count,
			SUM(report_base_order_collect.refund_return_goods_count) AS refund_return_goods_count,
			SUM(report_base_order_collect.refund_actual_money) AS refund_actual_money,
			SUM(report_base_order_collect.order_un_shipping_count) AS order_un_shipping_count,
			SUM(report_base_order_collect.order_un_shipping_goods_count) AS order_un_shipping_goods_count,
			SUM(report_base_order_collect.order_un_shipping_money) AS order_un_shipping_money
			FROM report_base_order_collect 
			WHERE biz_date BETWEEN  '{$start_time}' AND '{$end_time}'  
			GROUP BY biz_date 
			) AS tmp ORDER BY tmp.biz_date DESC";
		$ret = $this->db->getAll($sql);
		$report_data = array();
		$ret_date = array();
		foreach ($ret as $row) {
			$ret_date[$row['biz_date']] = $row;
		}
		$init_v = array(0,0,0,0,0,0,0);
		
		
		//$report_data['order_shipping_goods_count'] = array('name'=>'近七天商品销售排行','data' =>$init_v);//近七天商品销售排行
		
		
		$refund_actual_money = array('name'=>'近七天实际退款曲线','data' =>$init_v);//近七天实际退款曲线
		$refund_return_goods_count = array('name'=>'近七天退货商品数量曲线','data' =>$init_v);// 近七天退货商品数量曲线
		$order_shipping_goods_count = array('name'=>'近七天发货商品数量曲线','data' =>$init_v);//近七天发货商品数量曲线
		$goods_sale_count = array('name'=>'近七天销量曲线（按商品数量）','data' =>$init_v);// 近七天销量曲线（按商品数量）
		$order_sale_money = array('name'=>'近七天销售曲线（销售金额）','data' =>$init_v);//近七天销售曲线（销售金额）
		$date_arr = array();
		for($i=0;$i<7;$i++){
			$value = 0;
			$current_date = date('Y-m-d',strtotime($start_time)+3600*24*$i);
			$date_arr[] = date('m-d',strtotime($current_date));
			if (isset($ret_date[$current_date])){
				$order_sale_money['data'][$i] = (float)$ret_date[$current_date]['order_sale_money'];
				$goods_sale_count['data'][$i] = (int)$ret_date[$current_date]['goods_sale_count'];
				
				$order_shipping_goods_count['data'][$i] =(int) $ret_date[$current_date]['order_shipping_goods_count'];
				$refund_return_goods_count['data'][$i] = (int)$ret_date[$current_date]['refund_return_goods_count'];
				$refund_actual_money['data'][$i] = (float)$ret_date[$current_date]['refund_actual_money'];
			}
		}
		$json_report_data = array(
				array('name' => $order_sale_money['name'],'data' =>json_encode($order_sale_money['data'])),
				array('name' => $goods_sale_count['name'],'data' =>json_encode($goods_sale_count['data'])),
				array('name' => $order_shipping_goods_count['name'],'data' =>json_encode($order_shipping_goods_count['data'])),
				array('name' => $refund_return_goods_count['name'],'data' =>json_encode($refund_return_goods_count['data'])),
				array('name' => $refund_actual_money['name'],'data' =>json_encode($refund_actual_money['data'])),
				
				);
		//商品销售排行(按销售数量)
		$sql = "SELECT * FROM (
		SELECT goods_name,goods_code,spec1_name,spec1_code,spec2_name,spec2_code,goods_barcode,SUM(sale_count) AS sale_count,SUM(sale_money) AS sale_money FROM report_base_goods_collect 
		WHERE biz_date BETWEEN  '{$start_time}' AND '{$end_time}'  
		group by goods_barcode
		) as tmp order by sale_count desc limit 10";
		$sale_count_ret = $this->db->getAll($sql);
		
		//商品销售排行(按销售金额)
		$sql = "SELECT * FROM (
		SELECT goods_name,goods_code,spec1_name,spec1_code,spec2_name,spec2_code,goods_barcode,SUM(sale_money) AS sale_money,SUM(sale_count) AS sale_count FROM report_base_goods_collect
		WHERE biz_date BETWEEN  '{$start_time}' AND '{$end_time}'
		group by goods_barcode
		) as tmp order by sale_money desc limit 10";
		$sale_money_ret = $this->db->getAll($sql);
		$data['chart'] = $json_report_data;
		$data['date'] = json_encode($date_arr);
		$data['sale_rank'] = array('sale_count' => $sale_count_ret,'sale_money' => $sale_money_ret);
		
		return  $data;
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	function get_by_id($id) {
		
		return  $this->get_row(array('customer_id'=>$id));
	}

	/**
	 * @param $code
	 * @return array
	 */
	function get_by_code($code) {
		return $this->get_row(array('customer_code'=>$code));
	}
	/**
	 * 通过field_name查询
	 *
	 * @param  $ :查询field_name
	 * @param  $select ：查询返回字段
	 * @return array (status, data, message)
	 */
	public function get_by_field($field_name,$value, $select = "*") {
		$sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
		$data = $this -> db -> get_row($sql, array(":{$field_name}" => $value));
		if ($data) {
			return $this -> format_ret('1', $data);
		} else {
			return $this -> format_ret('-1', '', 'get_data_fail');
		}
	}
	/*
	 * 添加新纪录
	 */
	function insert($customer) {
		$status = $this->valid($customer);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		
		$ret = $this->is_exists($customer['customer_name']);

		if (!empty($ret['data'])) {
			return $this->format_ret(CUSTOMER_ERROR_UNIQUE_NAME);
		}
		return parent::insert($customer);
	}

	//转单时添加会员
	function add_customer($info){
		$status = $this->valid($info);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		$sql = "select customer_code from crm_customer where customer_name = :customer_name";
		$row = CTX()->db->getRow($sql,array('customer_name'=>$info['customer_name']));
		if (!empty($row)) {
                        return $this->format_ret(1,$row);
		} 
		$sql = "select max(customer_id) from crm_customer";
		$max_customer_id = CTX()->db->getOne($sql);
		$info['customer_code'] = (int)$max_customer_id + 1;
		$ret = $this->insert($info);
		$info['customer_id'] = $ret['data'];
		return   $this->format_ret(1,$info);
	}



	/*
	 * 修改纪录
	 */
	function update($customer, $customer_id) {
//		echo $customer_id;
//		print_r($customer);exit;
		$status = $this->valid($customer, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		$ret = $this->get_row(array('customer_id'=>$customer_id));
		if( isset($customer['customer_code']) && $customer['customer_code'] != $ret['data']['customer_code']){
			$ret1 = $this->is_exists($customer['customer_code'], 'customer_code');
			if (!empty($ret1['data'])) {
				return $this->format_ret(CUSTOMER_ERROR_UNIQUE_CODE);
			}
		}
		$ret = parent::update($customer, array('customer_id'=>$customer_id));
		return $ret;
	}

	
	function get_addr_list($customer_code){
		$sql = "select customer_address_id,customer_code,address,tel,name,zipcode,is_default,home_tel,province,city,district,street from crm_customer_address where customer_code = ".$customer_code." ";
		$data = CTX()->db->getAll($sql);
		return $data;
	}
        
        function get_default_addr($customer_code){
		$sql = "select * from crm_customer_address where is_default = 1 and customer_code = :customer_code";
		$data = CTX()->db->getRow($sql,array('customer_code'=>$customer_code));
                if($data){
                    return $this->format_ret(1,$data);
                }else{
                    return $this->format_ret(-1);
                }
	}

	function get_addr($customer_address_id){
		$sql = "select * from crm_customer_address where customer_address_id = :customer_address_id";
		$data = CTX()->db->getRow($sql,array('customer_address_id'=>$customer_address_id));
		return $data;		
	}

	function set_default($customer_address_id){
		$sql = "select customer_code from crm_customer_address where customer_address_id = :customer_address_id";
		$customer_code = CTX()->db->getOne($sql,array('customer_address_id'=>$customer_address_id));
		$sql = "update crm_customer_address set is_default = 0 where customer_code = :customer_code";
		CTX()->db->query($sql,array('customer_code'=>$customer_code));
		$sql = "update crm_customer_address set is_default = 1 where customer_address_id = :customer_address_id";
		CTX()->db->query($sql,array('customer_address_id'=>$customer_address_id));
		return true;
	}
	
	function clear_default($customer_code){
		$sql = "update crm_customer_address set is_default = 0 where customer_code = :customer_code";
		CTX()->db->query($sql,array('customer_code'=>$customer_code));
		return true;
	}

	//添加地址信息
	function insert_customer_address($info){
		$sql = "select name,address from crm_customer_address where customer_code = :customer_code";
		$old_info = CTX()->db->getRow($sql,array('customer_code'=>$info['customer_code']));
		if (!empty($old_info) && $old_info['address'] == $info['address'] && $old_info['name'] == $info['name']){
			return true;
		}
		$info['is_add_time'] = date('Y-m-d H:i:s');
//		$info['is_default'] = empty($old_info) ? 1 : 0;
		$result =  M('crm_customer_address')->insert($info);
		return $result;	
	}

	//修改地址信息
	function update_customer_address($info,$wh){
		//echo '<hr/>info<xmp>'.var_export($info,true).'</xmp>';
		//echo '<hr/>wh<xmp>'.var_export($wh,true).'</xmp>';
        $ret = M('crm_customer_address')->update($info,$wh);
        return $ret;
	}

	//删除地址信息
	function delete_customer_address($customer_address_id){
		$sql = "select customer_code from crm_customer_address where customer_address_id = :customer_address_id";
		$customer_code = CTX()->db->getOne($sql,array('customer_address_id'=>$customer_address_id));
		$sql = "select count(*) from crm_customer_address where customer_code = :customer_code";
		$c = CTX()->db->getOne($sql,array('customer_code'=>$customer_code));
		if ($c <= 1){
			$ret = array('status'=>-1,null,'message'=>'地址信息必须保留一条');
		}else{
			$sql = "delete from crm_customer_address where customer_address_id = :customer_address_id";
			CTX()->db->query($sql,array('customer_address_id'=>$customer_address_id));
			$ret = array('status'=>1);
		}
		return $ret;
	}
        /**
         * 新增订单是处理会员信息
         * @param type $customer
         */
        function handle_customer($customer){
            $ret = $this->add_customer($customer);
            if($ret['status']<1){
                return $ret;
            }
            $customer_code = $ret['data']['customer_code'];
            if($customer_code){
                $customer['customer_code'] = $customer_code;
                $ret = $this->insert_customer_address($customer);
                if(!$ret){
                    return $this->format_ret(-1,'','CUSTOMER_ADDR_INSERT_ERROR');
                }else{
                	$customer_address_id = $ret['data'];
                	$this->set_default($customer_address_id);
                }
            }else{
                return $this->format_ret(-1,'','CUSTOMER_INSERT_ERROR');
            }
            return $this->format_ret(1,$customer_code);
        }
    /**
    * 删除纪录
    */
    function delete($id) {
        $ret = parent :: delete(array('customer_id' => $id));
        return $ret;
    }
    
    function update_active($active, $id) {
        if (!in_array($active, array(1, 2))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('type' => $active), array('customer_id' => $id));
        return $ret;
    }
    
    function get_sell_analysis($day=7){
        $end_time = date('Y-m-d',strtotime('-1 day'));
	$start_time = date('Y-m-d',strtotime("-{$day} day"));
        $goods_data =  array();
        $ret_data['date_data'] = $this->get_date_data($day);
        $money_data = $goods_data;
        
        $sql = "select biz_date,SUM(order_sale_money) as sale_money ,SUM(goods_sale_count) as sale_num,modified from report_base_order_collect WHERE biz_date BETWEEN  '{$start_time}' AND '{$end_time}'  ";
        $sql .=" GROUP BY biz_date ";
        $sale_num = 0;
        $sale_money = 0;
        $data = $this->db->get_all($sql);
        $update_time =  date('Y-m-d',strtotime("-30 day"));
        foreach($data as $val){
            $val['sale_num'] = empty($val['sale_num']) ? 0 : $val['sale_num'];
            $val['sale_money'] = empty($val['sale_money']) ? 0 : $val['sale_money'];
            $sale_num +=$val['sale_num'];
            $sale_money +=$val['sale_money'];
            $money_data[$val['biz_date']] = $val['sale_money'];
            $goods_data[$val['biz_date']] = $val['sale_num'];
            $update_time = $this->max_date($update_time,$val['modified']);
        }
        $date=$this->getDateFromRange($start_time,$end_time);
        foreach($date as $value){
            $money_data_new[$value] = isset($money_data[$value]) ? $money_data[$value] : 0;
            $goods_data_new[$value] = isset($goods_data[$value]) ? $goods_data[$value] : 0;
        }
        $ret_data['goods_data'] = array_values($goods_data_new);
        $ret_data['money_data'] = array_values($money_data_new);
        $ret_data['sale_num'] = $sale_num;
        $ret_data['sale_money'] =  $sale_money;
        $ret_data['update_time'] =  $update_time;
//         if($day==7){
//               $ret_data['sale_num'] = 1210;
//                $ret_data['sale_money'] =  121033;
//                $ret_data['goods_data'] = array(30,45,56,34,23,62,23);
//                $ret_data['money_data'] =  array(0,0,0,200,200,900,1423);
//         }else{
//                $ret_data['goods_data'] = array(30,45,56,34,23,64,23,30,45,56,34,23,64,23,30,45,56,34,23,64,23,30,45,56,34,23,64,23,53,98);
//                $ret_data['money_data'] =  array(130,345,456,234,323,634,233,330,435,526,334,233,634,233,320,435,536,334,233,634,233,330,435,536,334,233,624,233,533,938);
//         }
        return  $this->format_ret(1,$ret_data);
    }
    private function get_date_data($day){
        $date_data = array();
         for($i=$day;$i>0;$i--){
            if($day==7){
                $date_data[] =  date('m-d',strtotime("-{$i} day"));
            }else if($i%5==0||$i==29){
                 $date_data[] =  date('m-d',strtotime("-{$i} day"));
            }
         }
         return $date_data;
    }
    private function max_date($date1,$date2){
        $time1 = strtotime($date1);
        $time2 = strtotime($date2);
        if($time1>$time2){
            return  $date1;
        }
        return  $date2;
    }
    

    
    
    /* 获取指定日期段内每一天的日期
 * @param  Date  $startdate 开始日期
 * @param  Date  $enddate   结束日期
 * @return Array
 */
function getDateFromRange($startdate, $enddate) {
        $stimestamp = strtotime($startdate);
        $etimestamp = strtotime($enddate);
        // 计算日期段内有多少天
        $days = ($etimestamp - $stimestamp) / 86400 + 1;
        // 保存每天日期
        $date = array();
        for ($i = 0; $i < $days; $i++) {
            $date[] = date('Y-m-d', $stimestamp + (86400 * $i));
        }
        return $date;
    }

}


