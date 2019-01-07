<?php
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
class SellGoodsProfitRateModel extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'oms_sell_record';
    protected $detail_table = 'oms_sell_record_detail';

    /**
     * 根据条件查询数据
     * @param $filter
     * @param $onlySql
     * @return array
     */
    function sell_by_page($filter, $onlySql = false) {
        $filter['ref'] = 'do';
        $sql_values = array();
    	$sql_join = "";
    	$sql_main = " FROM {$this->table} rl 
    				left join {$this->detail_table} rr on rl.sell_record_code = rr.sell_record_code 
    	 			left join base_goods bg on rr.goods_code =  bg.goods_code 
    				$sql_join WHERE 1 AND rl.order_status =1 AND rl.shipping_status=4 ";
    	//商店仓库权限
        $filter['time_type'] = 'record';
    	$ret = $this->get_query_condition($filter, $sql_main, $sql_values);
    	$select = 'rl.*,rr.*';
           //增值服务
       // $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code'); 
        $order_by = " ORDER BY sell_record_id DESC ";
//        var_dump($filter,$select,$sql_main,$sql_values);

        if($onlySql){
            $sql = array('select'=>$select, 'from'=>$sql_main, 'params'=>$sql_values);
            return array('status'=>'1', 'data'=>$sql, 'message'=>'仅返回SQL');
        }
		
        $sql_main .= $order_by;
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        $sell_record_key_arr = array();
    	foreach($data['data'] as $key => &$value){
            $sell_record_key_arr[$value['sell_record_code']] = $key;
           // $value['status_text'] = $this->get_sell_record_tag_img($value, $sys_user);
            if($value['is_fenxiao'] != 0){
                $value['buyer_name'] = isset($value['fenxiao_name']) && !empty($value['fenxiao_name']) ? $value['fenxiao_name'] : $value['buyer_name'];
                $value['avg_money'] = $value['fx_amount'];
            }
            $value['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code'=>$value['sale_channel_code']));
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code'=>$value['express_code']));
        	$key_arr = array('spec1_code','spec1_name','spec2_code','spec2_name','barcode','goods_name','season_code','season_name'
             ,'brand_code','brand_name','category_code','category_name','weight'
            );
        	$value['goods_cost_price'] = $value['cost_price'] * $value['num'];
        	$value['goods_gross_profit'] = $value['avg_money']-$value['goods_cost_price'];
        	$goods_gross_profit_rate = sprintf("%.4f", $value['goods_gross_profit']/$value['avg_money']);
        	$value['goods_gross_profit_rate'] = $goods_gross_profit_rate*100 . "%";
        	$sku_info =  load_model('goods/SkuCModel')->get_sku_info($value['sku'],$key_arr);
            $value = array_merge($value,$sku_info);
            $value['weight'] = $value['weight'] * $value['num'] / 1000;
            $value['weight'] = number_format($value['weight'], 3, ".", "");
    	}
        //if (!empty($sell_record_key_arr)) {
        //    $this->add_order_return_tag_img($data['data'], $sell_record_key_arr);
        //}
    	return $this->format_ret(1, $data);
    }
    private function add_order_return_tag_img(&$data, $sell_record_key_arr) {
        $sell_record_arr = array_keys($sell_record_key_arr);
        $sql_values = array();
        $sell_record_str = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record_code', $sql_values);
        $sql = "select DISTINCT sell_record_code,return_type from oms_sell_return where sell_record_code in({$sell_record_str}) AND return_order_status<>3";
        $return_data = $this->db->get_all($sql,$sql_values);
        foreach ($return_data as $val) {
            $key = $sell_record_key_arr[$val['sell_record_code']];
            if($val['return_type'] == 1){
                $data[$key]['status_text'] .= "<img src='assets/img/state_icon/tui_icon.png' title='存在退款' />";                
            }else if($val['return_type'] == 2){
                $data[$key]['status_text'] .= "<img src='assets/img/state_icon/tui_icon.png' title='存在退货' />";                
            }else{
                $data[$key]['status_text'] .= "<img src='assets/img/state_icon/tui_icon.png' title='存在退款退货' />"; 
            }
        }
    }
    function get_sell_record_tag_img($row, $sysuser) {
        $tag_arr = array();
        if ($row['invoice_status'] > 0) {
            $tag_arr[] = array('piao', '有发票');
        }
        if ($row['is_problem'] > 0) {
            //获取具体问题类型
            $problem_type = load_model("oms/SellRecordTagModel")->get_tag_by_sell_record(array($row['sell_record_code']), 'problem', 'tag_desc');
            foreach ($problem_type['data'] as $vlaue) {
                $tag[] = $vlaue['tag_desc'];
            }
            $tag_desc = implode('/', $tag);
            $tag_arr[] = array('wen', $tag_desc);
        }
        if ($row['is_copy'] > 0) {
            $tag_arr[] = array('fu', '复制单');
        }
        if ($row['shipping_status'] == 0 && $row['order_status'] <> 3 && $row['must_occupy_inv'] == 1 && $row['lock_inv_status'] <> 1 && $row['lock_inv_status'] <> 0) {
            $tag_arr[] = array('que', '缺货单');
        }
        if ($row['is_change_record'] > 0) {
            $tag_arr[] = array('huan', '换货单');
        }
        if ($row['is_split_new'] > 0) {
            $tag_arr[] = array('cai', '拆单');
        }
        if ($row['is_rush'] > 0) {
            $tag_arr[] = array('ji', '急');
        }
        if ($row['is_pending'] > 0) {
            $tag_arr[] = array('gua', '挂起');
        }
        if ($row['is_combine_new'] > 0) {
            $tag_arr[] = array('he', '合单');
        }
        if ($row['is_handwork'] > 0) {
            $tag_arr[] = array('shou', '手工单');
        }
        if ($row['sale_mode'] == 'presale') {
            $tag_arr[] = array('yue', '预售单');
        }
        if ($row['is_fenxiao'] == 1) {
            $tag_arr[] = array('fen', '淘宝分销订单');
        }
        if ($row['is_fenxiao'] == 2) {
            $tag_arr[] = array('fen', '分销订单');
        }
        if ($row['is_lock'] > 0 && $sysuser['user_code'] != $row['is_lock_person']) {
            $tag_arr[] = array('shuo', '锁定');
        }
//        $sell_return_code = $this->get_return_code_by_sell_record_code($row['sell_record_code']);
//        if (!empty($sell_return_code)) {
//            $tag_arr[] = array('tui', '存在退款/货');
//        }
        $html_arr = array();
        foreach ($tag_arr as $_tag) {
            $html_arr[] = "<img src='assets/img/state_icon/{$_tag[0]}_icon.png' title='{$_tag[1]}'/>";
        }
        return join('', $html_arr);
    }
    /**销售
     * 根据条件查询数据
     * @param $filter
     * @param $onlySql
     * @return array
     */
    function get_sell_by_page($filter, $onlySql = false) {
        $filter['ref'] = 'do';
        $sql_values = array();
    	$sql_join = "";
    	$sql_main = " FROM oms_sell_return rl  
                                left join oms_sell_return_detail rr on rl.sell_return_code = rr.sell_return_code
    	 			left join goods_sku bg on rr.sku =  bg.sku
                                left join base_goods bc on bc.goods_code=rr.goods_code
    				$sql_join WHERE 1 AND rl.return_order_status =1 AND rl.return_shipping_status=1 ";
    	//商店仓库权限
        //left join {$this->detail_table} rr on rl.sell_record_code = rr.sell_record_code
        $filter['time_type'] = 'return';
        $filter['sell_record_attr'] = '';
    	$ret = $this->get_query_condition($filter, $sql_main, $sql_values);
    	$select = 'rl.*,rr.*';

        $order_by = " ORDER BY sell_return_id DESC ";

        if($onlySql){
            $sql = array('select'=>$select, 'from'=>$sql_main, 'params'=>$sql_values);
            return array('status'=>'1', 'data'=>$sql, 'message'=>'仅返回SQL');
        }
		
        $sql_main .= $order_by;
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        $sell_record_key_arr = array();
    	foreach($data['data'] as $key => &$value){
            $sell_record_key_arr[$value['sell_record_code']] = $key;
            //$value['status_text'] = $this->get_sell_record_tag_img($value, $sys_user);
            if($value['is_fenxiao'] != 0){
                $value['buyer_name'] = isset($value['fenxiao_name']) && !empty($value['fenxiao_name']) ? $value['fenxiao_name'] : $value['buyer_name']; 
                $value['avg_money'] = $value['fx_amount'];
            }
            //读取退单明细的成本价
            /*if($value['cost_price'] == 0){
                $p = $this->get_base_price($value['goods_code']);
                $value['cost_price'] = $p;
            }*/
            $value['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code'=>$value['sale_channel_code']));
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code'=>$value['return_express_code']));
        	$key_arr = array('spec1_code','spec1_name','spec2_code','spec2_name','barcode','goods_name','season_code','season_name'
             ,'brand_code','brand_name','category_code','category_name','weight'
            );
            $value['goods_cost_price'] = $value['cost_price'] * $value['recv_num'];
            $avg_money = $value['avg_money'] - $value['return_money'];
            $value['avg_money'] = $avg_money;
        	$value['goods_gross_profit'] = $avg_money-$value['goods_cost_price'];
        	$goods_gross_profit_rate = sprintf("%.4f", $value['goods_gross_profit']/$avg_money);
        	$value['goods_gross_profit_rate'] = $goods_gross_profit_rate*100 . "%";
        	$sku_info =  load_model('goods/SkuCModel')->get_sku_info($value['sku'],$key_arr);
            $value = array_merge($value,$sku_info);
            $value['weight'] = $value['weight'] * $value['recv_num'] / 1000;
            $value['weight'] = number_format($value['weight'], 3, ".", "");
    	}
        //if (!empty($sell_record_key_arr)) {
        //    $this->add_order_return_tag_img($data['data'], $sell_record_key_arr);
        //}
    	return $this->format_ret(1, $data);
    }
    
    function get_base_price($code){
        $sql="select cost_price from base_goods where goods_code=:code";
        $data = $this->db->get_row($sql,array(':code'=>$code));
        return $data['cost_price'];
    }
    function get_query_condition($filter,&$sql_main,&$sql_values){
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
    	$filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
    	$sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code',$filter_store_code);
    	$filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
    	$sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code',$filter_shop_code);
    	
    	//订单号
//     	if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
//     		$filter['sell_record_code'] = $this->deal_strs($filter['sell_record_code']);
//     		$sql_main .= " AND rl.sell_record_code in ( ".$filter['sell_record_code']." ) ";
//     	}
    	
    	//交易号
//     	if (isset($filter['deal_code']) && $filter['deal_code'] !== '') {
//     		$sql_main .= " AND rl.deal_code like :deal_code ";
//     		$sql_values[':deal_code'] = "%".$filter['deal_code']."%";
//     	}
    
    	//销售平台
    	if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
    	    $arr = explode(',',$filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
    		$sql_main .= " AND rl.sale_channel_code in ( ".$str." ) ";
    	}
    	//店铺
    	if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
    	    $arr = explode(',',$filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
    		$sql_main .= " AND rl.shop_code in ( ".$str." ) ";
    	}
    	//商品编码
    	if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
    		$sql_main .= " AND rr.goods_code = :goods_code ";
    		$sql_values[':goods_code'] = $filter['goods_code'];
    	}
        //商品编码
    	if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
    		$sql_main .= " AND bg.goods_name = :goods_name ";
    		$sql_values[':goods_name'] = $filter['goods_name'];
    	}
    	//商品条形码
    	if (isset($filter['barcode']) && $filter['barcode'] !== '') {
    		$sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
    		if(empty($sku_arr)){
	    		$sql_main .= " AND 1=2 ";
	    	}else{
	    		$sku_str = "'".implode("','", $sku_arr). "'";
	    		$sql_main .= " AND rr.sku in({$sku_str}) ";
	    	}
    	}
        //订单编号
        if($filter['time_type'] == 'record'){
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
            $sql_main .= " AND rl.sell_record_code LIKE :sell_record_code ";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }            
        }else{
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
            $sql_main .= " AND rl.sell_return_code LIKE :sell_record_code ";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }             
        }
        //交易号
        if (isset($filter['deal_code']) && $filter['deal_code'] !== '') {
            $sql_main .= " AND rl.deal_code LIKE :deal_code ";
            $sql_values[':deal_code'] = '%' . $filter['deal_code'] . '%';
        }
    	
    	//配送方式
        if($filter['time_type'] == 'record'){
    	if (isset($filter['express_code']) && $filter['express_code'] !== '') {
        	$arr = explode(',',$filter['express_code']);
                $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
    		$sql_main .= " AND rl.express_code in ( ".$str." ) ";
    	}            
        }else{
        if (isset($filter['express_code']) && $filter['express_code'] !== ''){
        	$arr = explode(',',$filter['express_code']);
                $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
    		$sql_main .= " AND rl.return_express_code in ( ".$str." ) ";  
        }
        }

    	
    	//发货时间
        //print_r($filter['time']);
        if($filter['time_type'] == 'record'){
            if (isset($filter['time_start']) && $filter['time_start'] !== '') {
                    $sql_main .= " AND rl.delivery_time >= :time_start ";
                    $sql_values[':time_start'] = $filter['time_start'];
            }
            if (isset($filter['time_end']) && $filter['time_end'] !== '') {
                $sql_main .= " AND rl.delivery_time <= :time_end ";
                $sql_values[':time_end'] = $filter['time_end'];
            }            
        }else{
            if (isset($filter['time_start']) && $filter['time_start'] !== '') {
                    $sql_main .= " AND rl.receive_time >= :time_start ";
                    $sql_values[':time_start'] = $filter['time_start'];
            }
            if (isset($filter['time_end']) && $filter['time_end'] !== '') {
                $sql_main .= " AND rl.receive_time <= :time_end ";
                $sql_values[':time_end'] = $filter['time_end'];
            }            
        }
        //下单时间
    	if (isset($filter['record_time_start']) && $filter['record_time_start'] !== '') {
	    	$sql_main .= " AND rl.record_time >= :record_time_start ";
	    	$sql_values[':record_time_start'] = $filter['record_time_start'];
        }
    	if (isset($filter['record_time_end']) && $filter['record_time_end'] !== '') {
            $sql_main .= " AND rl.record_time <= :record_time_end ";
    	    $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        //付款时间
    	if (isset($filter['pay_time_start']) && $filter['pay_time_start'] !== '') {
	    	$sql_main .= " AND rl.pay_time >= :pay_time_start ";
	    	$sql_values[':pay_time_start'] = $filter['pay_time_start'];
        }
    	if (isset($filter['pay_time_end']) && $filter['pay_time_end'] !== '') {
            $sql_main .= " AND rl.pay_time <= :pay_time_end ";
    	    $sql_values[':pay_time_end'] = $filter['pay_time_end'];
        }

        //订单性质
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] !== '') {
            $sell_record_attr_arr = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = array();
            foreach ($sell_record_attr_arr as $attr) {
                if ($attr == 'attr_lock') {
                    $sql_attr_arr[] = " rl.is_lock = 1";
                }
                if ($attr == 'attr_pending') {
                    $sql_attr_arr[] = " rl.is_pending = 1";
                }
                if ($attr == 'attr_problem') {
                    $sql_attr_arr[] = " rl.is_problem = 1";
                }
                if ($attr == 'attr_bf_quehuo') {
                    $sql_attr_arr[] = " (rl.must_occupy_inv = 1 and rl.lock_inv_status = 2)";
                }
                if ($attr == 'attr_all_quehuo') {
                    $sql_attr_arr[] = " (rl.must_occupy_inv = 1 and rl.lock_inv_status = 3)";
                }
                if ($attr == 'attr_combine') {
                    $sql_attr_arr[] = " rl.is_combine_new = 1";
                }
                if ($attr == 'attr_split') {
                    $sql_attr_arr[] = " rl.is_split_new = 1";
                }
                if ($attr == 'attr_change') {
                    $sql_attr_arr[] = " rl.is_change_record = 1";
                }
                if ($attr == 'attr_handwork') {
                    $sql_attr_arr[] = " rl.is_handwork = 1";
                }
                if ($attr == 'attr_copy') {
                    $sql_attr_arr[] = " rl.is_copy = 1";
                }
                if ($attr == 'attr_presale') {
                    $sql_attr_arr[] = " rl.sale_mode = 'presale'";
                }
                if ($attr == 'attr_fenxiao') {
                    $sql_attr_arr[] = " (rl.is_fenxiao = 1 OR rl.is_fenxiao = 2) ";
                }
                if ($attr == 'is_rush') {
                    $sql_attr_arr[] = " rl.is_rush = 1";
                }
                if ($attr == 'is_replenish') {
                    $sql_attr_arr[] = " rl.is_replenish = 1";
                }
                if ($attr == 'is_problem') {
                    $sql_attr_arr[] = " (rl.must_occupy_inv = '1' AND rl.lock_inv_status = '1' AND rl.is_pending = '0' AND rl.is_problem = '0') ";
                }
            }
            $sql_main .= ' and (' . join(' or ', $sql_attr_arr) . ')';
        }

    }
    
    function get_oms_sell_return_data($sell_record_code_arr){
        $sell_record_code_str = "";
        if(!empty($sell_record_code_arr)){
            $sell_record_code_str = " AND r1.sell_record_code in('".implode("','", $sell_record_code_arr)."')";
        }
        $sql = "select r1.sell_record_code,r1.return_avg_money,sum(r2.recv_num) as return_num,sum(r2.avg_money) as return_money,r2.sku from oms_sell_return r1 
            LEFT JOIN oms_sell_return_detail r2 ON r2.sell_return_code = r1.sell_return_code
            where  r1.return_shipping_status=1 {$sell_record_code_str} GROUP by sell_record_code,r1.sell_record_code,r2.sku";
         return $this->db->get_all($sql);
    }
    function merge_return_data(&$sell_record_data,&$return_data,&$data_key_by_sell_record_code){
        if(!empty($return_data)){
            foreach($return_data as $val){
               if(isset($data_key_by_sell_record_code[$val['sell_record_code']][$val['sku']])){
               $sell_record_key = $data_key_by_sell_record_code[$val['sell_record_code']][$val['sku']];
           
               $sell_record_data [$sell_record_key]['return_money'] = empty($val['return_money'])?0:$val['return_money'];
               $sell_record_data [$sell_record_key]['return_num'] = empty($val['return_num'])?0:$val['return_num'];
               }
            }
        }
    }

    
    
    function report_count($filter){
        $filter['ref'] = 'do';

        // 汇总
        $sqlArr = $this->sell_by_page($filter, true);
        $sqlArr = $sqlArr['data'];
        $sql  = "  SELECT sum(avg_money) as all_avg_money ,sum(cost_price) as all_cost_price
            from ( select if(rl.is_fenxiao=0,sum(rr.avg_money),sum(rr.fx_amount)) as avg_money, sum(rr.cost_price*rr.num) as cost_price {$sqlArr['from']} GROUP BY rl.sell_record_code) as t";
         
        $row = $this->db->get_row($sql, $sqlArr['params']);
        $row['all_goods_gross_profit'] = $row['all_avg_money']-$row['all_cost_price'];
        $sql2 = "select DISTINCT rl.sell_record_code {$sqlArr['from']} ";

        $data = $this->db->get_all($sql2,$sqlArr['params']);
        $row['record_count'] = count($data);
        
        $record_code_arr = array();
        foreach($data as $val){
            $record_code_arr[] = $val['sell_record_code'];
        }
        $record_code_str = "'".implode("','", $record_code_arr)."'";
        
        $sql_return ="     WHERE r1.return_shipping_status=1 AND r1.sell_record_code in({$record_code_str}) ";
        $sql_values = array();
        $this->get_return_sql($filter,$sql_return,$sql_values);
        
        
        
//销售金额总计：200元     邮费总计：10元       销售数量总计：2件     退货数量总计：0件      退货金额总计：0元       订单总计：1单

        $row2 = $this->db->get_row($sql_return, $sql_values);

        return array_merge($row,$row2);
    }
    
    /*销售查询
     */
        function report_count_and_return($filter){
        $filter['ref'] = 'do';

        // 汇总
        $sqlArr = $this->get_sell_by_page($filter, true);
        $sqlArr = $sqlArr['data'];
        $sql  = "  SELECT sum(avg_money) as all_avg_money ,sum(cost_price) as all_cost_price
            from (select if(rl.is_fenxiao=0,sum(rr.avg_money),sum(rr.fx_amount)) as avg_money, sum(rr.cost_price*rr.recv_num) as cost_price {$sqlArr['from']} GROUP BY rl.sell_record_code) as t";

        $row = $this->db->get_row($sql, $sqlArr['params']);
        $row['all_goods_gross_profit'] = $row['all_avg_money']-$row['all_cost_price'];
        $sql2 = "select DISTINCT rl.sell_record_code {$sqlArr['from']} ";

        $data = $this->db->get_all($sql2,$sqlArr['params']);
        $row['record_count'] = count($data);
        
        $record_code_arr = array();
        foreach($data as $val){
            $record_code_arr[] = $val['sell_record_code'];
        }
        $record_code_str = "'".implode("','", $record_code_arr)."'";
        
        $sql_return ="     WHERE r1.return_shipping_status=1 AND r1.sell_record_code in({$record_code_str}) ";
        $sql_values = array();
        $this->get_return_sql($filter,$sql_return,$sql_values);
        
        $row2 = $this->db->get_row($sql_return, $sql_values);

        return array_merge($row,$row2);
    }
    
    function get_return_sql($filter,&$sql_main,&$sql_values){
        
        $is_goods = false;
            //季节
        if (isset($filter['season_code']) && $filter['season_code'] !== '') {
          	    $arr = explode(',',$filter['season_code']);
            $str = $this->arr_to_in_sql_value($arr, 'season_code', $sql_values);
            $sql_main .= " AND bg.season_code in ( ".$str." ) ";
            $is_goods = true;
        }
        
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] !== '') {
          	    $arr = explode(',',$filter['brand_code']);
            $str = $this->arr_to_in_sql_value($arr, 'brand_code', $sql_values);
            $sql_main .= " AND bg.brand_code in ( ".$str." ) ";
             $is_goods = true;
        }
        
        //分类
        if (isset($filter['category_code']) && $filter['category_code'] !== '') {
          	    $arr = explode(',',$filter['category_code']);
            $str = $this->arr_to_in_sql_value($arr, 'category_code', $sql_values);
            $sql_main .= " AND bg.category_code in ( ".$str." ) ";
             $is_goods = true;
        }  
             //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND r2.goods_code = :goods_code ";
            $sql_values[':goods_code'] = $filter['goods_code'];
             $is_goods = true;
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
   
	     $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
                            if(empty($sku_arr)){
                                   $sql_main .= " AND 1=2 ";
                            }else{
                                  $sku_str = "'".implode("','", $sku_arr). "'";
                                $sql_main .= " AND r2.sku in({$sku_str}) ";
                            }    
                             $is_goods = true;
        }
        $sql_select  = " select  sum(r1.return_avg_money) AS return_money ,sum(r2.recv_num) as return_num 
            from oms_sell_return r1
            INNER JOIN oms_sell_return_detail r2  ON r2.sell_return_code = r1.sell_return_code ";
        if($is_goods){
            $sql_select .="  LEFT JOIN  base_goods bg ON bg.goods_code=r2.goods_code ";
        }
        $sql_main =  "select sum(return_money) as return_money,sum(return_num) as return_num  from (".$sql_select.$sql_main."  GROUP BY  r1.sell_return_code) as t";
        
    }
    
    
    
    
    
    
    /**
     * 对用户输入的逗号分隔的字符串进行处理
     * @param type $str 要处理的字符串
     * @param type $quote 是否为每个加上引号 0：不加，1：加
     * @param type $caps 是否转换大小写，0：不转换，1：转小写，2：转大写
     */
    function deal_strs($str,$quote = 1,$caps = 0){
        $str = str_replace("，", ",", $str);//将中文逗号转成英文逗号
        $str = str_replace(" ", "", $str);//去掉空格
        $str = trim($str,',');//去掉前后多余的逗号
        if($quote=1){
            $str = "'".str_replace(",", "','", $str)."'";
        }
        if($caps = 1){
            $str = strtolower($str);
        }elseif($caps = 2){
            $str = strtoupper($str);
        }
        return $str;
    }
    
    
    function sell_ranking($day = 7){
        
    }
    
    

}