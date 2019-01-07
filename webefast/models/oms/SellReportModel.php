<?php
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
class SellReportModel extends TbModel {

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
        if(isset($filter['keyword_type'])&&$filter['keyword_type']!==''){
            $filter[$filter['keyword_type']]=trim($filter['keyword']);
        }
    	$sql_main = "FROM {$this->table} rl INNER join  {$this->detail_table} rr on  rl.sell_record_code = rr.sell_record_code 
    	 INNER join  base_goods bg on rr.goods_code =  bg.goods_code $sql_join WHERE 1 AND  rl.order_status =1 AND  rl.shipping_status=4   ";
    	//商店仓库权限
    	$filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
    	$sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code',$filter_store_code);
    	$filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
    	$sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code',$filter_shop_code);


    	//订单号
    	if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
         $arr = explode(',', $filter['sell_record_code']);
        $str = $this->arr_to_in_sql_value($arr, 'sell_record_code', $sql_values);
            $sql_main .= " AND rl.sell_record_code in ( ".$str." ) ";
        }

        //交易号
        if (isset($filter['deal_code']) && $filter['deal_code'] !== '') {
            $sql_main .= " AND rl.deal_code like :deal_code ";
            $sql_values[':deal_code'] = "%".$filter['deal_code']."%";
        }
        //支付方式
        if (isset($filter['pay_type']) && $filter['pay_type'] !== '') {
    $arr = explode(',', $filter['pay_type']);
        $str = $this->arr_to_in_sql_value($arr, 'pay_type', $sql_values);
            $sql_main .= " AND rl.pay_code in ( ".$str." ) ";
        }
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
           $arr = explode(',', $filter['sale_channel_code']);
        $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND rl.sale_channel_code in ( ".$str." ) ";
        }
        //店铺
//        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
//            $filter['shop_code'] = deal_strs_with_quote($filter['shop_code']);
//            $sql_main .= " AND rl.shop_code in ( ".$filter['shop_code']." ) ";
//        }
        //仓库
//        if (isset($filter['store_code']) && $filter['store_code'] !== '') {
//            $filter['store_code'] = deal_strs_with_quote($filter['store_code']);
//            $sql_main .= " AND rl.store_code in ( ".$filter['store_code']." ) ";
//        }
        //季节
        if (isset($filter['season_code']) && $filter['season_code'] !== '') {
          $arr = explode(',', $filter['season_code']);
        $str = $this->arr_to_in_sql_value($arr, 'season_code', $sql_values);
            $sql_main .= " AND bg.season_code in ( ".$str." ) ";
        }
        
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] !== '') {
            $arr = explode(',', $filter['brand_code']);
            $str = $this->arr_to_in_sql_value($arr, 'brand_code', $sql_values);
            $sql_main .= " AND bg.brand_code in ( ".$str." ) ";
        }else{
            $sql_main .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        }
        
        //分类
        if (isset($filter['category_code']) && $filter['category_code'] !== '') {
        $arr = explode(',', $filter['category_code']);
        $str = $this->arr_to_in_sql_value($arr, 'category_code', $sql_values);
            $sql_main .= " AND bg.category_code in ( ".$str." ) ";
        }
        
        //支付宝交易号
        if (isset($filter['alipay_no']) && $filter['alipay_no'] !== '') {
            if($filter['alipay_no']=='0'){
                $sql_main .= " AND rl.alipay_no = '' ";
            }else{
                $sql_main .= " AND rl.alipay_no <> '' ";
            }
        }
        //买家昵称
        if (isset($filter['buyer_name']) && $filter['buyer_name'] !== '') {

         $customer_code_arr= load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                if(!empty($customer_code_arr)){
                       $customer_code_str = "'".implode("','", $customer_code_arr)."'";

                       $sql_main .= " AND ( rl.customer_code in ({$customer_code_str}) ) ";  
                }else{
                        $sql_main .= " AND rl.buyer_name = :buyer_name ";
                        $sql_values[':buyer_name'] = $filter['buyer_name'];
                }    
//            $sql_main .= " AND rl.buyer_name LIKE :buyer_name ";
//            $sql_values[':buyer_name'] = "%" . $filter['buyer_name'] . "%";
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND rr.goods_code = :goods_code ";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
//            $sql_main .= " AND rr.barcode LIKE :barcode ";
//            $sql_values[':barcode'] = "%" . $filter['barcode'] . "%";
//            
	    $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
      
            if(empty($sku_arr)){
                   $sql_main .= " AND 1=2 ";
            }else{
                  $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND rr.sku in({$sku_str}) ";
            }
        }
        // 套餐条形码
        if (isset($filter['combo_barcode']) && $filter['combo_barcode'] !== '') {
            $sku_arr = load_model('prm/GoodsComboOpModel')->get_sku_by_combo_barcode($filter['combo_barcode']);
            if(empty($sku_arr)) {
                $sql_main .= " AND 1 != 1";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr,'combo_sku',$sql_values);
                $sql_main .= " AND rr.combo_sku IN ($sku_str) ";
            }
        }
        //分销商名称
        if(isset($filter['fenxiao_name']) && $filter['fenxiao_name'] != '') {
            $sql_main .= " AND rl.fenxiao_name LIKE :fenxiao_name ";
            $sql_values[':fenxiao_name'] = '%' . $filter['fenxiao_name'] . '%';
        }
        
        //时间查询
        if (isset($filter['start_time']) && $filter['start_time'] != '') {
            switch ($filter['time_type']) {
                //下单时间
                case 'record_time':
                    $sql_main .= " AND rl.record_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time'];
                    break;
                //付款时间
                case 'pay_time':
                    $sql_main .= " AND rl.pay_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time'];
                    break;
                //发货时间
                case 'plan_time':
                    $sql_main .= " AND rl.delivery_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time'];
                    break;
            }
        }
        if (isset($filter['end_time']) && $filter['end_time'] != '') {
            switch ($filter['time_type']) {
                //下单时间
                case 'record_time':
                    $sql_main .= " AND rl.record_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time'];
                    break;
                //付款时间
                case 'pay_time':
                    $sql_main .= " AND rl.pay_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time'];
                    break;
                //发货时间
                case 'plan_time':
                    $sql_main .= " AND rl.delivery_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time'];
                    break;
            }
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql_main .= " AND bg.goods_name  LIKE :goods_name ";
            $sql_values[':goods_name'] = '%'.$filter['goods_name'].'%';
        }

        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] !== '') {
             $arr = explode(',', $filter['express_code']);
        $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
            $sql_main .= " AND rl.express_code in ( ".$str." ) ";
        }

    	//下单时间
    	if (isset($filter['record_time_start']) && $filter['record_time_start'] !== '') {
            $sql_main .= " AND rl.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'] . ' 00:00:00';
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] !== '') {
            $sql_main .= " AND rl.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
        }
        //支付时间
        if (!empty($filter['pay_time_start'])) {
            $sql_main .= " AND rl.pay_time >= :pay_time_start ";
            $sql_values[':pay_time_start'] = $filter['pay_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['pay_time_end'])) {
            $sql_main .= " AND rl.pay_time <= :pay_time_end ";
            $sql_values[':pay_time_end'] = $filter['pay_time_end'] . ' 23:59:59';
        }
        //发货时间
        if (!empty($filter['send_time_start'])) {
            $sql_main .= " AND rl.delivery_time >= :send_time_start ";
            $sql_values[':send_time_start'] = $filter['send_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['send_time_end'])) {
            $sql_main .= " AND rl.delivery_time <= :send_time_end ";
            $sql_values[':send_time_end'] = $filter['send_time_end'] . ' 23:59:59';
        }

           //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code'); 
        if($onlySql){
            $sql = array('select'=>$select, 'from'=>$sql_main, 'params'=>$sql_values);
            return array('status'=>'1', 'data'=>$sql, 'message'=>'仅返回SQL');
        }
        
        $group_by = '';
        if(!empty($filter['group_by'])){
            $group_by = " group by ".$filter['group_by']." ";
        }
        $order_by = " ORDER BY sell_record_id DESC ";

        return array(
            'filter' => $filter,
            'sql_main' => $sql_main,
            'sql_values' => $sql_values,
            'group_by'=>$group_by,
            'order_by'=>$order_by,
        );
    }
    
    //设置订单性质arr
    function set_order_attr($sell_record_attr_ary){
        $sql_attr_arr = array();
        foreach ($sell_record_attr_ary as $attr) {
            if ($attr == 'attr_lock') {
                $sql_attr_arr[] = " rl.is_lock = 1";
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
        }
        return $sql_attr_arr;
    }
    
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $ret = $this->sell_by_page($filter);
        $filter = $ret['filter'];
        //订单性质查询
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            $sell_record_attr_ary = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = $this->set_order_attr($sell_record_attr_ary);
            $ret['sql_main'] .= ' AND ( ' . join(' or ', $sql_attr_arr) .' )' ;
        }
        $sql_main = $ret['sql_main'].$ret['group_by'].$ret['order_by'];
        $sql_values = $ret['sql_values'];
        $select = 'rl.*,rr.* ';
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
    	foreach($data['data'] as &$value){         
           // $value['average_money'] = oms_tb_val('oms_sell_record_detail', 'avg_money', array('sell_record_code'=>$value['sell_record_code'], 'barcode' => $value['barcode']));
            $value['paid_money'] = sprintf("%.2f", $value['paid_money']);
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['pay_name'] = oms_tb_val('base_pay_type', 'pay_type_name', array('pay_type_code'=>$value['pay_code']));
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code'=>$value['express_code']));
            //省+市
            $value['province_name'] = oms_tb_val('base_area', 'name', array('id'=>$value['receiver_province']));
            $value['city_name'] = oms_tb_val('base_area', 'name', array('id'=>$value['receiver_city']));
//            $value['province_city'] = $value['province_name'].' '.$value['city_name'];
            $key_arr = array(
             'spec1_code','spec1_name','spec2_code','spec2_name','barcode','goods_name','season_code','season_name'
             ,'brand_code','brand_name','category_code','category_name','sell_price','cost_price','year_code'
            );
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'],$key_arr);
            $value = array_merge($value,$sku_info);
            //年份
            $value['year_name'] = oms_tb_val('base_year', 'year_name', array('year_code'=>$sku_info['year_code']));
            if ($value['is_fenxiao'] == 1 || $value['is_fenxiao'] == 2) {
                //$value['avg_money'] = $value['fx_amount'];
                $value['express_money'] = $value['fx_express_money'];
                $value['buyer_name'] = isset($value['fenxiao_name']) && !empty($value['fenxiao_name']) ? $value['fenxiao_name'] : $value['buyer_name'];
            }else{
                $value['fx_amount'] = $value['avg_money'];
            }
            //获取扩展属性
            $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
            $property_power = $ret_cfg['property_power'];
            if ($property_power) {
                $goods_property = load_model('prm/GoodsModel')->get_export_property($value['goods_code']);
                $value = $goods_property != -1 && is_array($goods_property) ? array_merge($value, $goods_property) : $value;
            }
        }
        //订单图标
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
         foreach ($data['data'] as &$val) {
             $val['status_text'] = load_model('oms/SellRecordModel')->get_sell_record_tag_img($val, $sys_user);
         }
    	return $this->format_ret(1, $data);
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
    
     
    function get_sale_channel($filter){
        $filter['group_by'] = 'rl.sale_channel_code';
        $ret = $this->sell_by_page($filter);
        //订单性质查询
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            $sell_record_attr_ary = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = $this->set_order_attr($sell_record_attr_ary);
            $ret['sql_main'] .= ' AND ( ' . join(' or ', $sql_attr_arr) .' )' ;
        }
        $sql_main = $ret['sql_main']." GROUP BY rl.sell_record_code,rl.sale_channel_code ";
        $select = "t.sale_channel_code,count(t.sell_record_code) record_count_num,sum(t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.sale_channel_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} ) AS t GROUP BY t.sale_channel_code ";
        
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
    	}
    	return $this->format_ret(1, $data);
    }

    function get_shop_data($filter){
        $filter['group_by'] = 'rl.shop_code';
        $ret = $this->sell_by_page($filter);
        //订单性质查询
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            $sell_record_attr_ary = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = $this->set_order_attr($sell_record_attr_ary);
            $ret['sql_main'] .= ' AND ( ' . join(' or ', $sql_attr_arr) .' )' ;
        }      
        $sql_main = $ret['sql_main']." GROUP BY rl.sell_record_code,rl.shop_code ";        
        $select = "t.sale_channel_code,t.shop_code,count(t.sell_record_code) record_count_num,sum( t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.sale_channel_code,rl.shop_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} ) AS t GROUP BY t.shop_code ORDER BY t.sale_channel_code DESC";
        
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
    	}
    	return $this->format_ret(1, $data);
    }

    function get_store_data($filter){
        $filter['group_by'] = 'rl.store_code';
        $ret = $this->sell_by_page($filter);
        //订单性质查询
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            $sell_record_attr_ary = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = $this->set_order_attr($sell_record_attr_ary);
            $ret['sql_main'] .= ' AND ( ' . join(' or ', $sql_attr_arr) .' )' ;
        }
        $sql_main = $ret['sql_main']." GROUP BY rl.sell_record_code,rl.store_code ";
        $select = " t.store_code,count(t.sell_record_code) record_count_num,sum( t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.store_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} ) AS t GROUP BY t.store_code ";
//        var_dump($select,$sql);die;
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
    	}
    	return $this->format_ret(1, $data);
    }
    
    function get_brand_data($filter){
        $filter['group_by'] = 'bg.brand_code';
        $ret = $this->sell_by_page($filter);
        //订单性质查询
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            $sell_record_attr_ary = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = $this->set_order_attr($sell_record_attr_ary);
            $ret['sql_main'] .= ' AND ( ' . join(' or ', $sql_attr_arr) .' )' ;
        }
        $sql_main = $ret['sql_main'];
        $select = "t.brand_name,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.sell_record_code,bg.brand_code,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money,bg.brand_name {$sql_main} "
                . " ) AS t GROUP BY t.brand_code ORDER BY all_goods_money DESC";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);

    	return $this->format_ret(1, $data);
    }
    
    function get_season_data($filter) {
        $filter['group_by'] = 'bg.season_code';
        $ret = $this->sell_by_page($filter);
        //订单性质查询
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            $sell_record_attr_ary = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = $this->set_order_attr($sell_record_attr_ary);
            $ret['sql_main'] .= ' AND ( ' . join(' or ', $sql_attr_arr) .' )' ;
        }
        $sql_main = $ret['sql_main'];
        $select = "t.season_name,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.sell_record_code,bg.season_code,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money,bg.season_name {$sql_main} "
                . " ) AS t GROUP BY t.season_code ORDER BY all_goods_money DESC";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        return $this->format_ret(1, $data);
    }

    function get_category_data($filter) {
        $filter['group_by'] = 'bg.category_code';
        $ret = $this->sell_by_page($filter);
        //订单性质查询
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            $sell_record_attr_ary = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = $this->set_order_attr($sell_record_attr_ary);
            $ret['sql_main'] .= ' AND ( ' . join(' or ', $sql_attr_arr) .' )' ;
        }
        $sql_main = $ret['sql_main'];
        $select = "t.category_name,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.sell_record_code,bg.category_code,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money,bg.category_name {$sql_main} "
                . " ) AS t GROUP BY t.category_code ORDER BY all_goods_money DESC";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        return $this->format_ret(1, $data);
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
        $sqlArr = $this->sell_record_by_page($filter, true);
        $sqlArr = $sqlArr['data'];
        $sql_main=$sqlArr['from'];
        $sql  = "  SELECT sum(paid_money) as paid_money ,sum(express_money) as express_money,sum(goods_num) as goods_num , sum(t.return_money) as return_money,sum(t.return_num) as return_num 
            from ( select if(rl.is_fenxiao=0,sum(rr.avg_money),sum(rr.fx_amount)) as  paid_money  , if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money  , sum(rr.num) as  goods_num , sum(rr.return_money) return_money, sum(rr.return_num) return_num {$sql_main} GROUP BY  rl.sell_record_code"
            . ") as t";

        $row = $this->db->get_row($sql, $sqlArr['params']);
        $sql2 = "select DISTINCT rl.sell_record_code {$sqlArr['from']} ";

        $data = $this->db->get_all($sql2,$sqlArr['params']);
        $row['record_count'] = count($data);

        return array_merge($row);
    }
   
    
        function data_report_count($filter){
        $filter['ref'] = 'do';
        //订单性质查询
        $where = '';
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            $sell_record_attr_ary = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = $this->set_order_attr($sell_record_attr_ary);
            $where .= ' AND ( ' . join(' or ', $sql_attr_arr) .' )' ;
        }
        // 汇总
        $sqlArr = $this->sell_by_page($filter, true);
        $sqlArr = $sqlArr['data'];
        $sql  = "  SELECT sum(paid_money) as paid_money ,sum(express_money) as express_money,sum(goods_num) as goods_num , sum(t.return_money) as return_money,sum(t.return_num) as return_num 
            from ( select if(rl.is_fenxiao=0,sum(rr.avg_money),sum(rr.fx_amount)) as  paid_money  , if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money  , sum(rr.num) as  goods_num , sum(rr.return_money) return_money, sum(rr.return_num) return_num {$sqlArr['from']} {$where} GROUP BY  rl.sell_record_code) as t";
        

        $row = $this->db->get_row($sql, $sqlArr['params']);
        $sql2 = "select DISTINCT rl.sell_record_code {$sqlArr['from']} {$where} ";

        $data = $this->db->get_all($sql2,$sqlArr['params']);
        $row['record_count'] = count($data);

        return array_merge($row);
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
    
    function get_barcode_data($filter){
        $filter['group_by'] = 'rr.barcode';
        $ret = $this->sell_by_page($filter);
        
        $select = " 
            rr.barcode,
            sum(rr.avg_money) all_goods_money,
            sum(rl.express_money) express_money_all,
            sum(rr.num) goods_count,
            sum(rr.return_num) return_count_num,
            sum(rr.return_money) return_money_all";
        $sql_main = $ret['sql_main'].$ret['group_by'].$ret['order_by'];
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
    	return $this->format_ret(1, $data);
    }
    
    
     function get_goods_code_data($filter){
        $filter['group_by'] = 'rr.goods_code';
        $ret = $this->sell_by_page($filter);
        
        $select = " 
            rr.goods_code,
            sum(rr.avg_money) all_goods_money,
            sum(rl.express_money) express_money_all,
            sum(rr.num) goods_count,
            sum(rr.return_num) return_count_num,
            sum(rr.return_money) return_money_all";
        $sql_main = $ret['sql_main'].$ret['group_by'].$ret['order_by'];
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
    	return $this->format_ret(1, $data);
    }
    
    
    //平台和店铺组合查询
        function get_sale_channel_shop_data($filter){
        $filter['group_by'] = 'rl.shop_code,rl.sale_channel_code';
        $ret = $this->sell_by_page($filter);
              
        $sql_main = $ret['sql_main']." GROUP BY rl.sell_record_code,rl.shop_code,rl.sale_channel_code";        
        $select = "t.order_money as order_money_all,t.sale_channel_code,t.shop_code,count(t.sell_record_code) record_count_num,sum( t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.order_money,rl.sale_channel_code,rl.shop_code,rl.sell_record_code,rl.express_money,sum(rr.num) sum_goods_num,sum(rr.avg_money) sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} ) AS t GROUP BY t.shop_code,t.sale_channel_code ORDER BY t.sale_channel_code DESC";
        
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
    	}
    	return $this->format_ret(1, $data);
    }
    
    //平台和商品编码组合查询
     function get_sale_channel_goods_code_data($filter){
        $filter['group_by'] = 'rr.goods_code,rl.sale_channel_code';
        $ret = $this->sell_by_page($filter);
        
        $select = " 
            rr.goods_code,
            sum(rr.avg_money) all_goods_money,
            rl.sale_channel_code,
            sum(rl.express_money) express_money_all,
            sum(rr.num) goods_count,
            sum(rr.return_num) return_count_num,
            sum(rr.return_money) return_money_all";
        $sql_main = $ret['sql_main'].$ret['group_by'].$ret['order_by'];
        $sql_values = $ret['sql_values'];   
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
            foreach($data['data'] as &$value){
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
    	}
    	return $this->format_ret(1, $data);
    }
    
    
      function get_shop_goods_code_data($filter){
        $filter['group_by'] = 'rr.goods_code,rl.shop_code';
        $ret = $this->sell_by_page($filter);
        
        $select = " 
            rr.goods_code,
            sum(rr.avg_money) all_goods_money,
            rl.shop_code,
            sum(rl.express_money) express_money_all,
            sum(rr.num) goods_count,
            sum(rr.return_num) return_count_num,
            sum(rr.return_money) return_money_all";
        $sql_main = $ret['sql_main'].$ret['group_by'].$ret['order_by'];
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
    	}
    	return $this->format_ret(1, $data);
    }
    
    
      function get_sale_channel_shop_goods_code_data($filter){
        $filter['group_by'] = 'rr.goods_code,rl.shop_code,rl.sale_channel_code';
        $ret = $this->sell_by_page($filter);
        
        $select = " 
            rr.goods_code,
            sum(rr.avg_money) all_goods_money,
            rl.shop_code,
            rl.sale_channel_code,
            sum(rl.express_money) express_money_all,
            sum(rr.num) goods_count,
            sum(rr.return_num) return_count_num,
            sum(rr.return_money) return_money_all";
        $sql_main = $ret['sql_main'].$ret['group_by'].$ret['order_by'];
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
    	}
    	return $this->format_ret(1, $data);
    }
    
    //订单查询
    function get_record_data($filter) {
        $filter['group_by'] = 'rl.sell_record_code';
        $ret = $this->sell_by_page($filter);
        $filter = $ret['filter'];
        $sql_main = $ret['sql_main'].$ret['group_by'].'ORDER BY avg_money DESC';
        $sql_values = $ret['sql_values'];
        $select = "rl.*,SUM(rr.avg_money) as avg_money ";
       
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
    	foreach($data['data'] as &$value){
            $value['paid_money'] = sprintf("%.2f", $value['paid_money']);
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['pay_name'] = oms_tb_val('base_pay_type', 'pay_type_name', array('pay_type_code'=>$value['pay_code']));
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code'=>$value['express_code']));
            $value['order_money_all'] =$value['avg_money']+$value['express_money'];
    	}
    	return $this->format_ret(1, $data);
    }
    
    //平台和仓库组合查询
      function get_sale_channel_store_data($filter){
        $filter['group_by'] = 'rl.store_code,rl.sale_channel_code';
        $ret = $this->sell_by_page($filter);
        
        $select = " 
            rr.goods_code,
            sum(rr.avg_money) all_goods_money,
            rl.shop_code,
            rl.store_code,
            rl.sale_channel_code,
            sum(rl.order_money) order_money_all,
            sum(rl.express_money) express_money_all,
            sum(rr.num) goods_count,
            sum(rr.return_num) return_count_num,
            sum(rr.return_money) return_money_all";
        $sql_main = $ret['sql_main'].$ret['group_by'].$ret['order_by'];
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
        foreach($data['data'] as &$value){
          //  $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
    	}
    	return $this->format_ret(1, $data);
 
    }
    
        //店铺和仓库组合查询
      function get_shop_store_data($filter){
        $filter['group_by'] = 'rl.store_code,rl.shop_code';
        $ret = $this->sell_by_page($filter);
        
        $select = " 
            rr.goods_code,
            sum(rr.avg_money) all_goods_money,
            rl.shop_code,
            rl.store_code,
            rl.sale_channel_code,
            sum(rl.order_money) order_money_all,
            sum(rl.express_money) express_money_all,
            sum(rr.num) goods_count,
            sum(rr.return_num) return_count_num,
            sum(rr.return_money) return_money_all";
        $sql_main = $ret['sql_main'].$ret['group_by'].$ret['order_by'];
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
          //  $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
    	}
    	return $this->format_ret(1, $data);
 
    }
    
       //平台，店铺和仓库组合查询
      function get_sale_channel_shop_store_data($filter){
        $filter['group_by'] = 'rl.store_code,rl.shop_code,rl.sale_channel_code';
        $ret = $this->sell_by_page($filter);
        
        $select = " 
            rr.goods_code,
            sum(rr.avg_money) all_goods_money,
            rl.shop_code,
            rl.store_code,
            rl.sale_channel_code,
            sum(rl.order_money) order_money_all,
            sum(rl.express_money) express_money_all,
            sum(rr.num) goods_count,
            sum(rr.return_num) return_count_num,
            sum(rr.return_money) return_money_all";
        $sql_main = $ret['sql_main'].$ret['group_by'].$ret['order_by'];
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
    	}
    	return $this->format_ret(1, $data);
 
    }
    
    
    //销售订单平台查询
       function get_statistic_sale_channel($filter) {
        $filter['group_by'] = 'rl.sale_channel_code';
           $ret = $this->sell_record_by_page($filter,false,0);

        $sql_main = $ret['sql_main'] . " GROUP BY rl.sell_record_code,rl.sale_channel_code ";
        $select = "t.sale_channel_code,count(t.sell_record_code) record_count_num,sum(t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";      
        $sql = " FROM(SELECT rl.sale_channel_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main}) AS t GROUP BY t.sale_channel_code ";
         
        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['order_money_all'] = $value['all_goods_money'] + $value['express_money_all'];
        }
        return $this->format_ret(1, $data);
    }


    //销售订单平台和店铺组合查询
        function get_statistic_sale_channel_shop_data($filter){
        $filter['group_by'] = 'rl.shop_code,rl.sale_channel_code';
            $ret = $this->sell_record_by_page($filter, false, 0);
              
        $sql_main = $ret['sql_main']." GROUP BY rl.sell_record_code,rl.shop_code,rl.sale_channel_code";       
        $select = "t.sale_channel_code,t.shop_code,count(t.sell_record_code) record_count_num,sum( t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.sale_channel_code,rl.shop_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} "
        . " ) AS t GROUP BY t.shop_code,t.sale_channel_code ";
       
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['order_money_all'] =$value['all_goods_money']+$value['express_money_all'];
    	}
    	return $this->format_ret(1, $data);
    }
    
     //销售订单平台，店铺和仓库组合查询
      function get_statistic_sale_channel_shop_store_data($filter) {
        $filter['group_by'] = 'rl.shop_code,rl.sale_channel_code,rl.store_code';
          $ret = $this->sell_record_by_page($filter, false, 0);

        $sql_main = $ret['sql_main'] . " GROUP BY rl.sell_record_code,rl.shop_code,rl.sale_channel_code,rl.store_code";
        $select = "t.sale_channel_code,t.store_code,t.shop_code,count(t.sell_record_code) record_count_num,sum( t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.store_code,rl.sale_channel_code,rl.shop_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} "
                . " ) AS t GROUP BY t.store_code,t.shop_code,t.sale_channel_code ";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['order_money_all'] = $value['all_goods_money'] + $value['express_money_all'];
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
        }
        return $this->format_ret(1, $data);
    }

    //销售订单平台和仓库组合查询
      function get_statistic_sale_channel_store_data($filter){
        $filter['group_by'] = 'rl.store_code,rl.sale_channel_code';
          $ret = $this->sell_record_by_page($filter, false, 0);
              
        $sql_main = $ret['sql_main']." GROUP BY rl.sell_record_code,rl.store_code,rl.sale_channel_code";     
        $select = "t.sale_channel_code,t.store_code,count(t.sell_record_code) record_count_num,sum( t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.sale_channel_code,rl.store_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} "
        . " ) AS t GROUP BY t.store_code,t.sale_channel_code ";
        
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['order_money_all'] =$value['all_goods_money']+$value['express_money_all'];
    	}
    	return $this->format_ret(1, $data);
          
    }
    
    //销售订单店铺
        function get_statistic_shop_data($filter) {
        $filter['group_by'] = 'rl.shop_code';
            $ret = $this->sell_record_by_page($filter, false, 0);

        $sql_main = $ret['sql_main'] . " GROUP BY rl.sell_record_code,rl.shop_code ";
        $select = "t.sale_channel_code,t.shop_code,count(t.sell_record_code) record_count_num,sum( t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.sale_channel_code,rl.shop_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} "
                . " ) AS t GROUP BY t.shop_code";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['order_money_all'] = $value['all_goods_money'] + $value['express_money_all'];
        }
        return $this->format_ret(1, $data);
    }

    //销售订单店铺和仓库组合查询
      function get_statistic_shop_store_data($filter) {
        $filter['group_by'] = 'rl.shop_code,rl.store_code';
          $ret = $this->sell_record_by_page($filter, false, 0);

        $sql_main = $ret['sql_main'] . " GROUP BY rl.sell_record_code,rl.shop_code,rl.store_code";
        $select = "t.store_code,t.shop_code,count(t.sell_record_code) record_count_num,sum( t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.store_code,rl.shop_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(rr.fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} "
        . ") AS t GROUP BY t.shop_code,t.store_code ";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['order_money_all'] = $value['all_goods_money'] + $value['express_money_all'];
        }
        return $this->format_ret(1, $data);
    }

    //销售订单仓库
    function get_statistic_store_data($filter) {
        $filter['group_by'] = 'rl.store_code';
        $ret = $this->sell_record_by_page($filter, false, 0);

        $sql_main = $ret['sql_main'] . " GROUP BY rl.sell_record_code,rl.store_code ";
        $select = "t.store_code,count(t.sell_record_code) record_count_num,sum( t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.store_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} "
                . " ) AS t GROUP BY t.store_code ";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['order_money_all'] = $value['all_goods_money'] + $value['express_money_all'];
        }
        return $this->format_ret(1, $data);
    }

    //销售商品 商品条形码
       function get_goods_barcode_data($filter) {
        $filter['group_by'] = 'rr.sku';
        $ret = $this->sell_record_by_page($filter);

        $sql_main = $ret['sql_main'];
        $select = "t.sku,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.sell_record_code,rr.sku,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money {$sql_main} "
                . " ) AS t GROUP BY t.sku ";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $sku_key_arr = array('barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $sku_key_arr);
            $value = array_merge($value, $sku_info);
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
            }

        return $this->format_ret(1, $data);
    }
    
    function get_export_property($goods_code) {
        $proprety = load_model('prm/GoodsPropertyModel')->get_property_val('property_val');
        $select = '';
        if(empty($proprety)) {
            return -1;
        }
        foreach ($proprety as $val) {
            $select .= $val['property_val'].",";
        }
        $select = substr($select, 0,-1);
        $sql = "SELECT {$select} FROM base_property WHERE property_val_code=:property_val_code AND property_type='goods'";
        $result1 = $this->db->get_row($sql, array(':property_val_code' => $goods_code));
        $result =array();
        foreach($result1 as $k=>$v){
            $result[$k] = "\t".$v;
        }
        return $result;
    }
    //销售商品 品牌
        function get_goods_brand_data($filter) {
        $filter['group_by'] = 'bg.brand_code';
        $ret = $this->sell_record_by_page($filter);

        $sql_main = $ret['sql_main'];
        $select = "t.brand_name,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.sell_record_code,bg.brand_code,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money,bg.brand_name {$sql_main} "
                . " ) AS t GROUP BY t.brand_code ";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
            }
        return $this->format_ret(1, $data);
    }

    //销售商品分类
        function get_goods_category_data($filter) {
        $filter['group_by'] = 'bg.category_code';
        $ret = $this->sell_record_by_page($filter);

        $sql_main = $ret['sql_main'];
        $select = "t.category_name,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.sell_record_code,bg.category_code,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money,bg.category_name {$sql_main} "
                . " ) AS t GROUP BY t.category_code ";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
            }
        return $this->format_ret(1, $data);
    }

    //销售商品商品编码
        function get_goods_goods_code_data($filter){
        $filter['group_by'] = 'rr.goods_code';
        $ret = $this->sell_record_by_page($filter);
 
        $sql_main= $ret['sql_main'];
        $select = "t.goods_name,t.goods_code,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.sell_record_code,rr.goods_code,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money,bg.goods_name {$sql_main} "
        . ") AS t GROUP BY t.goods_code";

        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        foreach ($data['data'] as $key=>&$value) {
            if($property_power) {
                $goods_property = $this->get_export_property($value['goods_code']);
                $data['data'][$key] = $goods_property != -1 && is_array($goods_property) ? array_merge($data['data'][$key], $goods_property) : $data['data'][$key];
            }
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
        }
        return $this->format_ret(1, $data);
    }
    
    //销售商品，平台
      function get_goods_sale_channel($filter){
        $filter['group_by'] = 'rl.sale_channel_code';
        $ret = $this->sell_record_by_page($filter);
        
        $sql_main = $ret['sql_main']." GROUP BY rl.sell_record_code,rl.sale_channel_code ";
        $select = "sum(t.order_money) AS order_money_all,t.sale_channel_code,count(t.sell_record_code) record_count_num,sum(t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.order_money,rl.sale_channel_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} "
        . " ) AS t GROUP BY t.sale_channel_code ";
        
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
    	
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
        }
    	return $this->format_ret(1, $data);
    }  
    

        //销售商品 平台和商品编码组合查询
     function get_goods_sale_channel_goods_code_data($filter) {
        $filter['group_by'] = 'rr.goods_code,rl.sale_channel_code';
        $ret = $this->sell_record_by_page($filter);

        $sql_main= $ret['sql_main'];
        $select = "t.sale_channel_code,t.goods_name,t.goods_code,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.sale_channel_code,rl.sell_record_code,rr.goods_code,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money,bg.goods_name {$sql_main} "
                . " ) AS t GROUP BY t.goods_code,t.sale_channel_code";
        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];  
        foreach ($data['data'] as $key=>&$value) {
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            if($property_power) {
                $goods_property = $this->get_export_property($value['goods_code']);
                $data['data'][$key] = $goods_property != -1 && is_array($goods_property) ? array_merge($data['data'][$key], $goods_property) : $data['data'][$key];
            }
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
        }
        return $this->format_ret(1, $data);
    }

    //销售商品平台和店铺组合查询
        function get_goods_sale_channel_shop_data($filter){
        $filter['group_by'] = 'rl.shop_code,rl.sale_channel_code';
        $ret = $this->sell_record_by_page($filter);
              
        $sql_main = $ret['sql_main']." GROUP BY rl.sell_record_code,rl.shop_code,rl.sale_channel_code";      
        $select = "t.order_money as order_money_all,t.sale_channel_code,t.shop_code,count(t.sell_record_code) record_count_num,sum( t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.order_money,rl.sale_channel_code,rl.shop_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main}"
        . " ) AS t GROUP BY t.shop_code,t.sale_channel_code";
        
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
    	}
    	return $this->format_ret(1, $data);
    }
    
    //销售商品平台，店铺，编码组合
     function get_goods_sale_channel_shop_goods_code_data($filter) {
        $filter['group_by'] = 'rr.goods_code,rl.shop_code,rl.sale_channel_code';
        $ret = $this->sell_record_by_page($filter);

        $sql_main= $ret['sql_main'];
        $select = "t.sale_channel_code,t.shop_code,t.goods_name,t.goods_code,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.sale_channel_code,rl.shop_code,rl.sell_record_code,rr.goods_code,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money,bg.goods_name {$sql_main} "
                . " ) AS t GROUP BY t.goods_code,t.sale_channel_code,t.shop_code";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];        
        foreach ($data['data'] as $key=>&$value) {
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            if($property_power) {
                $goods_property = $this->get_export_property($value['goods_code']);
                $data['data'][$key] = $goods_property != -1 && is_array($goods_property) ? array_merge($data['data'][$key], $goods_property) : $data['data'][$key];
            }
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
        }
        return $this->format_ret(1, $data);
    }

    //销售商品,季节
       function get_goods_season_data($filter){
        $filter['group_by'] = 'bg.season_code';
        $ret = $this->sell_record_by_page($filter);
        
        $sql_main= $ret['sql_main'];
        $select = "t.season_name,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.sell_record_code,bg.season_code,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money,bg.season_name {$sql_main} "
        . " ) AS t GROUP BY t.season_code";

        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        foreach($data['data'] as &$value){   	
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
        }
    	return $this->format_ret(1, $data);
    } 
    
  //销售商品，明细
      function get_goods_sell_record($filter) {
        $ret = $this->sell_record_by_page($filter);
        $filter = $ret['filter'];
        $sql_main = $ret['sql_main'].$ret['group_by'];
        $sql_values = $ret['sql_values'];
        $select = 'rl.express_code,rl.store_code,rl.delivery_time,rl.pay_time,rl.record_time,rl.shop_code,rl.sale_channel_code,rl.is_fenxiao,rl.fenxiao_name,rl.pay_code,rr.deal_code,rl.buyer_name,rl.sell_record_code,rr.sku,rr.avg_money,rr.fx_amount,rr.num,rr.return_num,rr.return_money,rr.goods_price,rr.goods_code';
       
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];        
    	foreach($data['data'] as $key=>&$value){
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['pay_name'] = oms_tb_val('base_pay_type', 'pay_type_name', array('pay_type_code'=>$value['pay_code']));
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code'=>$value['express_code']));
            $key_arr = array(
             'spec1_name','spec2_name','barcode','goods_name','goods_code','season_name'
             ,'brand_name','category_name','year_code'
            );
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($value,$sku_info);
            $value['year_name'] = oms_tb_val('base_year', 'year_name', array('year_code' => $value['year_code']));
            if($value['is_fenxiao']==1 || $value['is_fenxiao']==2){
                $value['avg_money']= $value['fx_amount'];
                //会员昵称取值分销商名称
                $value['buyer_name']=$value['fenxiao_name'];
            }
            if($property_power) {
                $goods_property = $this->get_export_property($value['goods_code']);
                $data['data'][$key] = $goods_property != -1 && is_array($goods_property) ? array_merge($data['data'][$key], $goods_property) : $data['data'][$key];
            }
            $value['real_count'] = $value['num'] - $value['return_num'];
    	}
    	return $this->format_ret(1, $data);
    }
    
    //销售商品，店铺
        function get_goods_shop_data($filter){
        $filter['group_by'] = 'rl.shop_code';
        $ret = $this->sell_record_by_page($filter);
              
        $sql_main= $ret['sql_main']." GROUP BY rl.sell_record_code,rl.shop_code ";    
        $select = "sum(t.order_money) AS order_money_all,t.sale_channel_code,t.shop_code,count(t.sell_record_code) record_count_num,sum( t.express_money) AS express_money_all,sum(t.sum_goods_num) goods_count,sum(t.sum_avg_money) all_goods_money,sum(t.sum_return_num) return_count_num,sum(t.sum_return_money) return_money_all ";
        $sql = " FROM( SELECT rl.order_money,rl.sale_channel_code,rl.shop_code,rl.sell_record_code,if(rl.is_fenxiao=0,rl.express_money,rl.fx_express_money) AS express_money,sum(rr.num) sum_goods_num,if(rl.is_fenxiao=0,sum(rr.avg_money),sum(fx_amount)) AS sum_avg_money,sum(rr.return_num) sum_return_num,sum(rr.return_money) sum_return_money {$sql_main} "
        . " ) AS t GROUP BY t.shop_code";
        
        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
    	}
    	return $this->format_ret(1, $data);
    }
    
//销售商品，店铺，编码    
      function get_goods_shop_goods_code_data($filter) {
        $filter['group_by'] = 'rr.goods_code,rl.shop_code';
        $ret = $this->sell_record_by_page($filter);

        $sql_main= $ret['sql_main'];
        $select = "t.shop_code,t.goods_name,t.goods_code,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.shop_code,rl.sell_record_code,rr.goods_code,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money,bg.goods_name {$sql_main} "
                . " ) AS t GROUP BY t.goods_code,t.shop_code";

        $sql_values = $ret['sql_values'];
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];        
        foreach ($data['data'] as $key=>&$value) {
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            if($property_power) {
                $goods_property = $this->get_export_property($value['goods_code']);
                $data['data'][$key] = $goods_property != -1 && is_array($goods_property) ? array_merge($data['data'][$key], $goods_property) : $data['data'][$key];
            }
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
        }
        return $this->format_ret(1, $data);
    }

    //销售商品 年份
    function get_goods_years_data($filter) {
        $filter['group_by'] = 'bg.year_code';
        $ret = $this->sell_record_by_page($filter);

        $sql_main = $ret['sql_main'];
        $select = "t.year_name,sum(t.avg_money) all_goods_money,sum(t.num) goods_count,sum(t.return_num) AS return_count_num,sum(t.return_money) return_money_all";
        $sql = " FROM( SELECT rl.sell_record_code,bg.year_code,if(rl.is_fenxiao=0,rr.avg_money,rr.fx_amount) AS avg_money,rr.num,rr.return_num,rr.return_money,bg.year_name {$sql_main} "
        . ") AS t GROUP BY t.year_code";

        $sql_values = $ret['sql_values'];
        $data =  $this->get_page_from_sql($filter, $sql,$sql_values, $select,true);
        foreach($data['data'] as &$value){
            $value['real_count'] = $value['goods_count'] - $value['return_count_num'];
    	}
        return $this->format_ret(1, $data);
    }

    /**
     * 根据条件查询数据
     * @param $filter
     * @param $onlySql
     * @return array
     */
     function sell_record_by_page($filter, $onlySql = false,$select_type=1) {
        $filter['ref'] = 'do';
        $sql_values = array();
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
         $sql_join = '';
         if ($select_type == 1) {
             $sql_join.=" INNER JOIN  base_goods bg ON rr.goods_code =  bg.goods_code ";
         }
        $sql_main = "FROM {$this->table} rl LEFT join  {$this->detail_table} rr on  rl.sell_record_code = rr.sell_record_code 
    	   ".$sql_join." WHERE 1 AND  rl.order_status <>3  ";
        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);

        //订单号
         if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
             $arr = explode(',', $filter['sell_record_code']);
             $str = $this->arr_to_in_sql_value($arr, 'sell_record_code', $sql_values);
             $sql_main .= " AND rl.sell_record_code in ( " . $str . " ) ";
         }
        //单据状态
        if (isset($filter['record_type']) && $filter['record_type'] != 1) {
            if ($filter['record_type'] == 0) {
                $sql_main .= " AND rl.shipping_status=4 ";
            } else {
                $sql_main .= " AND rl.shipping_status<>4 ";
            }
        }
        //时间查询
        if (isset($filter['start_time']) && $filter['start_time'] != '') {
            switch ($filter['time_type']) {
                //下单时间
                case 'record_time':
                    $sql_main .= " AND rl.record_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time'];
                    break;
                //付款时间
                case 'pay_time':
                    $sql_main .= " AND rl.pay_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time'];
                    break;
                //发货时间
                case 'plan_time':
                    $sql_main .= " AND rl.delivery_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time'];
                    break;
            }
        }
        if (isset($filter['end_time']) && $filter['end_time'] != '') {
            switch ($filter['time_type']) {
                //下单时间
                case 'record_time':
                    $sql_main .= " AND rl.record_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time'];
                    break;
                //付款时间
                case 'pay_time':
                    $sql_main .= " AND rl.pay_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time'];
                    break;
                //计划发货时间
                case 'plan_time':
                    $sql_main .= " AND rl.delivery_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time'];
                    break;
            }
        }
        //交易号
        if (isset($filter['deal_code']) && $filter['deal_code'] !== '') {
            $sql_main .= " AND rl.deal_code like :deal_code ";
            $sql_values[':deal_code'] = "%" . $filter['deal_code'] . "%";
        }
        //支付方式
         if (isset($filter['pay_type']) && $filter['pay_type'] !== '') {
             $arr = explode(',', $filter['pay_code']);
             $str = $this->arr_to_in_sql_value($arr, 'pay_code', $sql_values);
             $sql_main .= " AND rl.pay_code in ( " . $str . " ) ";
         }
        //销售平台
         if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
             $arr = explode(',', $filter['sale_channel_code']);
             $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
             $sql_main .= " AND rl.sale_channel_code in ( " . $str . " ) ";
         }
        //店铺
//        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
//            $filter['shop_code'] = deal_strs_with_quote($filter['shop_code']);
//            $sql_main .= " AND rl.shop_code in ( ".$filter['shop_code']." ) ";
//        }
        //仓库
//        if (isset($filter['store_code']) && $filter['store_code'] !== '') {
//            $filter['store_code'] = deal_strs_with_quote($filter['store_code']);
//            $sql_main .= " AND rl.store_code in ( ".$filter['store_code']." ) ";
//        }
        //季节
         if (isset($filter['season_code']) && $filter['season_code'] !== '') {
             $arr = explode(',', $filter['season_code']);
             $str = $this->arr_to_in_sql_value($arr, 'season_code', $sql_values);
             $sql_main .= " AND bg.season_code in ( " . $str . " ) ";
         }
        //年份
         if (isset($filter['year_code']) && $filter['year_code'] !== '') {
             $arr = explode(',', $filter['year_code']);
             $str = $this->arr_to_in_sql_value($arr, 'year_code', $sql_values);
             $sql_main .= " AND bg.year_code in ( " . $str . " ) ";
         }
        //品牌
         if (isset($filter['brand_code']) && $filter['brand_code'] !== '') {
             $arr = explode(',', $filter['brand_code']);
             $str = $this->arr_to_in_sql_value($arr, 'brand_code', $sql_values);
             $sql_main .= " AND bg.brand_code in ( " . $str . " ) ";
         }else if ($select_type == 1){
            $sql_main .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
         }
        //分类
        if (isset($filter['category_code']) && $filter['category_code'] !== '') {
           $arr = explode(',', $filter['category_code']);
        $str = $this->arr_to_in_sql_value($arr, 'category_code', $sql_values);
            $sql_main .= " AND bg.category_code in ( " . $str . " ) ";
        }
        //支付宝交易号
        if (isset($filter['alipay_no']) && $filter['alipay_no'] !== '') {
            if ($filter['alipay_no'] == '0') {
                $sql_main .= " AND rl.alipay_no = '' ";
            } else {
                $sql_main .= " AND rl.alipay_no <> '' ";
            }
        }
        //买家昵称
        if (isset($filter['buyer_name']) && $filter['buyer_name'] !== '') {

         $customer_code_arr= load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                if(!empty($customer_code_arr)){
                        $customer_code_str = "'".implode("','", $customer_code_arr)."'";
                        $sql_main .= " AND ( rl.customer_code in ({$customer_code_str}) ) ";  
                }else{
                        $sql_main .= " AND rl.buyer_name = :buyer_name ";
                        $sql_values[':buyer_name'] = $filter['buyer_name'];
                }    
//            $sql_main .= " AND rl.buyer_name LIKE :buyer_name ";
//            $sql_values[':buyer_name'] = "%" . $filter['buyer_name'] . "%";
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND rr.goods_code = :goods_code ";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
//            $sql_main .= " AND rr.barcode LIKE :barcode ";
//            $sql_values[':barcode'] = "%" . $filter['barcode'] . "%";
//            
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);

            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, sku, $sql_values);
                $sql_main .= " AND rr.sku in({$sku_str}) ";
            }
        }
        
        // 套餐条形码
        if (isset($filter['combo_barcode']) && $filter['combo_barcode'] !== '') {
            $combo_sku_arr = load_model('prm/GoodsComboOpModel')->get_sku_by_combo_barcode($filter['combo_barcode']);
            if(empty($combo_sku_arr)) {
                $where .= " AND 1 != 1";
            } else {
                $combo_sku_str = $this->arr_to_in_sql_value($combo_sku_arr,'combo_sku',$sql_values);
                $sql_main .= " AND rr.combo_sku IN ($combo_sku_str) ";
            }
        }
        //分销商名称
        if(isset($filter['fenxiao_name']) && $filter['fenxiao_name'] != '') {
            $sql_main .= " AND rl.fenxiao_name LIKE :fenxiao_name ";
            $sql_values[':fenxiao_name'] = '%' . $filter['fenxiao_name'] . '%';
        }
        
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql_main .= " AND bg.goods_name  LIKE :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        //配送方式
         if (isset($filter['express_code']) && $filter['express_code'] !== '') {
             $arr = explode(',', $filter['express_code']);
             $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
             $sql_main .= " AND rl.express_code in ( " . $str . " ) ";
         }
        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');
        if ($onlySql) {
            $sql = array('select' => $select, 'from' => $sql_main, 'params' => $sql_values);
            return array('status' => '1', 'data' => $sql, 'message' => '仅返回SQL');
        }
        $group_by = '';
        if (!empty($filter['group_by'])) {
            $group_by = " group by " . $filter['group_by'] . " ";
        }
        $order_by = " ORDER BY sell_record_id DESC ";

        return array(
            'filter' => $filter,
            'sql_main' => $sql_main,
            'sql_values' => $sql_values,
            'group_by' => $group_by,
            'order_by' => $order_by,
        );
    }

    
    //销售订单 订单查询
    function get_statistic_record_data($filter) {
        $filter['group_by'] = 'rl.sell_record_code';
        $ret = $this->sell_record_by_page($filter, false, 0);
        $filter = $ret['filter'];
        $sql_main = $ret['sql_main'] . $ret['group_by'];
        $sql_values = $ret['sql_values'];
        $select = "rl.express_code,rl.store_code,rl.sale_channel_code,rl.shop_code,rl.record_time,rl.sell_record_code,rl.deal_code_list,rl.buyer_name,rl.fenxiao_name,rl.is_fenxiao,rl.express_money,rl.fx_express_money,rl.pay_time,rl.seller_remark,
        SUM(rr.avg_money) as avg_money,SUM(rr.fx_amount) as sum_fx_amount";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            $value['order_money_all'] = $value['avg_money'] + $value['express_money'];
            if ($value['is_fenxiao'] == 1 || $value['is_fenxiao'] == 2) {
                $value['avg_money'] = $value['sum_fx_amount'];
                $value['express_money'] = $value['fx_express_money'];
                $value['order_money_all'] = $value['sum_fx_amount'] + $value['fx_express_money'];
                $value['buyer_name'] = isset($value['fenxiao_name']) && !empty($value['fenxiao_name']) ? $value['fenxiao_name'] : $value['buyer_name'];
            }
        }
        return $this->format_ret(1, $data);
    }

    /**
     * 淘宝积分统计
     * @param $filter
     * @return array
     */
    function get_taobao_integral_data($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_main = " FROM api_taobao_trade AS r1 INNER JOIN oms_sell_record AS r2 ON r1.tid=r2.deal_code_list WHERE 1 AND r2.shipping_status=4 AND r2.sale_channel_code='taobao' ";
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $shop_code_arr = explode(',', $filter['shop_code']);
            $shop_code_str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
            $sql_main .= " AND r1.shop_code IN ({$shop_code_str}) ";
        } else {
            $purview_sale_channel = array('taobao');
            $purview_shop_arr = array();
            foreach ($purview_sale_channel as $sale_channel_code) {
                $shop_info = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code($sale_channel_code);
                $purview_shop_arr = array_merge($purview_shop_arr, array_column($shop_info, 'shop_code'));
            }
            if (!empty($purview_shop_arr)) {
                $purview_shop_str = $this->arr_to_in_sql_value($purview_shop_arr, 'purview_shop', $sql_values);
                $sql_main .= " AND r1.shop_code IN ({$purview_shop_str}) ";
            } else {
                $sql_main .= " AND 1=2";
            }
        }
        //交易结束时间
        if (isset($filter['delivery_time_start']) && $filter['delivery_time_start'] != '') {
            $sql_main .= " AND r2.delivery_time >= :delivery_time_start ";
            $sql_values[':delivery_time_start'] = $filter['delivery_time_start'];
        }
        if (isset($filter['delivery_time_end']) && $filter['delivery_time_end'] != '') {
            $sql_main .= " AND r2.delivery_time <= :delivery_time_end ";
            $sql_values[':delivery_time_end'] = $filter['delivery_time_end'];
        }
        
          //订单号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] != '') {
            $sql_main .= " AND r2.sell_record_code = :sell_record_code ";
            $sql_values[':sell_record_code'] = $filter['sell_record_code'];
        }
        //交易号
        if (isset($filter['deal_code_list']) && $filter['deal_code_list'] != '') {
            $sql_main .= " AND r2.deal_code_list = :deal_code_list ";
            $sql_values[':deal_code_list'] = $filter['deal_code_list'];
        }

        $select = "r1.*,r2.delivery_time";
        $sql_main .= "group by r1.tid";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $shop_name_arr = array();
        foreach ($data['data'] as &$value) {
            if (isset($shop_name_arr[$value['shop_code']])) {
                $shop_name = $shop_name_arr[$value['shop_code']];
            } else {
                $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
                $shop_name_arr[$value['shop_code']] = $shop_name;
            }
            $value['shop_name'] = $shop_name;
            $value['coupon_fee_percent'] = $value['coupon_fee'] / 100;  //红包
            $value['alipay_point_percent'] = $value['alipay_point'] / 100;  //集分宝
            //获取购物券
            $value['discount_fee'] = $this->get_tabao_discount_fee($value['promotion_details'], '天猫购物劵');
            $value['payment'] = round($value['payment']-$value['coupon_fee_percent']-$value['alipay_point_percent']-$value['discount_fee'],2);
    
        }
        return $this->format_ret(1, $data);
    }

    /**
     * 获取购物券
     * @param $tid
     * @param $promotion_name
     * @return int
     */
    function get_tabao_discount_fee($promotion_details, $promotion_name) {
        if (empty($promotion_details)) {
            return 0;
        }
        $promotion_details_arr = json_decode($promotion_details, TRUE);
        $discount_fee_arr = array();
        foreach ($promotion_details_arr['promotion_detail'] as $value) {
            if (strpos($value['promotion_name'], $promotion_name) !== false) {
                $discount_fee_arr[] = $value['discount_fee'];
            }
        }
        $discount_fee_sum = empty($discount_fee_arr) ? 0 : array_sum($discount_fee_arr);
        return $discount_fee_sum;
    }

    /**
     * 京东积分统计
     * @param $filter
     * @return array
     */
    function get_jingdong_integral_data($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_main = " FROM api_jingdong_trade AS r1 INNER JOIN oms_sell_record AS r2 ON r1.order_id=r2.deal_code_list WHERE 1 AND r2.shipping_status=4 AND r2.sale_channel_code='jingdong' ";
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $shop_code_arr = explode(',', $filter['shop_code']);
            $shop_code_str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
            $sql_main .= " AND r1.shop_code IN ({$shop_code_str}) ";
        } else {
            $purview_sale_channel = array('jingdong');
            $purview_shop_arr = array();
            foreach ($purview_sale_channel as $sale_channel_code) {
                $shop_info = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code($sale_channel_code);
                $purview_shop_arr = array_merge($purview_shop_arr, array_column($shop_info, 'shop_code'));
            }
            if (!empty($purview_shop_arr)) {
                $purview_shop_str = $this->arr_to_in_sql_value($purview_shop_arr, 'purview_shop', $sql_values);
                $sql_main .= " AND r1.shop_code IN ({$purview_shop_str}) ";
            } else {
                $sql_main .= " AND 1=2";
            }
        }
        //结束时间
        if (isset($filter['delivery_time_start']) && $filter['delivery_time_start'] != '') {
            $sql_main .= " AND r2.delivery_time >= :delivery_time_start ";
            $sql_values[':delivery_time_start'] = $filter['delivery_time_start'];
        }
        if (isset($filter['delivery_time_end']) && $filter['delivery_time_end'] != '') {
            $sql_main .= " AND r2.delivery_time <= :delivery_time_end ";
            $sql_values[':delivery_time_end'] = $filter['delivery_time_end'];
        }
        
           //订单号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] != '') {
            $sql_main .= " AND r2.sell_record_code = :sell_record_code ";
            $sql_values[':sell_record_code'] = $filter['sell_record_code'];
        }
        //交易号
        if (isset($filter['deal_code_list']) && $filter['deal_code_list'] != '') {
            $sql_main .= " AND r2.deal_code_list = :deal_code_list ";
            $sql_values[':deal_code_list'] = $filter['deal_code_list'];
        }
        
        $select = "r1.*,r2.delivery_time";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $shop_name_arr = array();
        foreach ($data['data'] as &$value) {
            if (isset($shop_name_arr[$value['shop_code']])) {
                $shop_name = $shop_name_arr[$value['shop_code']];
            } else {
                $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
                $shop_name_arr[$value['shop_code']] = $shop_name;
            }
            $value['shop_name'] = $shop_name;
     
            //获取京东券，京豆
            $jingdong_discount = $this->get_jingdong_discount($value['order_id']);
            $value['jingdong_coupon'] = isset($jingdong_discount['jingdong_coupon']) ? $jingdong_discount['jingdong_coupon'] : 0;
            $value['jingdong_bean'] = isset($jingdong_discount['jingdong_bean']) ? $jingdong_discount['jingdong_bean'] : 0;
            
            $value['pay_courtesy'] = number_format(($value['order_seller_price'] - $value['order_payment']- $value['jingdong_coupon']- $value['jingdong_bean']- $value['balance_used']), 2, '.', '');
        }
        return $this->format_ret(1, $data);
    }

    /**
     * 查询京东优惠信息
     * @param $order_id
     * @return array
     */
    function get_jingdong_discount($order_id) {
        $sql_value = array();
        $sql = "SELECT SUM(coupon_price) as coupon_price_all,coupon_type FROM api_jingdong_trade_coupon WHERE order_id=:order_id ";
        $sql_value[':order_id'] = $order_id;
        $coupon_type_arr = array('39-京豆优惠', '41-京东券优惠');
        $coupon_type_str = $this->arr_to_in_sql_value($coupon_type_arr, 'coupon_type', $sql_value);
        $sql .= " AND coupon_type IN({$coupon_type_str})";
        $sql .= " GROUP BY coupon_type";
        $ret = $this->db->get_all($sql, $sql_value);
        $coupon_name = array(
            '39-京豆优惠' => 'jingdong_bean',
            '41-京东券优惠' => 'jingdong_coupon',
        );
        $jingdong_discount = array();
        foreach ($ret as $value) {
            $key = $coupon_name[$value['coupon_type']];
            $jingdong_discount[$key] = $value['coupon_price_all'];
        }
        return $jingdong_discount;
    }

}

