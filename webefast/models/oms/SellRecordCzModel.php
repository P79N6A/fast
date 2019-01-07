<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class SellRecordCzModel extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'oms_sell_record_cz';

    //称重设置
    function config_detail(){
    	$sql = "select param_code,param_name,value from sys_params where param_code in('cz_com_name','cz_baud_rate');";
    	$param_ret = $this->db->get_all($sql);
    	$config = array();
    	foreach ($param_ret as $param_row) {
    		$config[$param_row['param_code']] = $param_row['value'];
    	}
    	return $config;
    }
    function save_config($request){
    	$cz_com_name = $request['cz_com_name'];
    	$up_data = array('value'=> $cz_com_name) ;
    	$this->db->update('sys_params', $up_data,"param_code='cz_com_name'");
    	
    	$cz_baud_rate = $request['cz_baud_rate'];
    	$up_data = array('value'=> $cz_baud_rate) ;
    	$this->db->update('sys_params', $up_data,"param_code='cz_baud_rate'");
    	
    	return $this->format_ret(1,'');
    	
    }
    //根据快递单号查询订单
    function get_sell_record_by_express_no($express_no){
    	$sql = "select * from oms_sell_record where express_no=:express_no ";
    	$sql_values[':express_no'] = $express_no;
    	$record_ret = $this->db->get_row($sql,$sql_values);
    	return $record_ret;
    }
    function search_sell_record($express_no){
    	//是否启用多包裹
    	$ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
    	$is_more_deliver_package =isset($ret_pararm['is_more_deliver_package'])?$ret_pararm['is_more_deliver_package']:'0' ;
    	if($is_more_deliver_package == 1){
    		//查询多包裹单
    		$record_ret = $this->get_package_by_express_no($express_no);
    	} else {
    		$record_ret = $this->get_sell_record_by_express_no($express_no);
    	}
    	if (empty($record_ret)){
    		return $this->format_ret(-1,'','订单不存在');
    	}
    	
    	if ($record_ret['shipping_status'] != 4){
    		return $this->format_ret(-1,'','订单还未发货,无需称重');
    	}
        $store_name = oms_tb_val('base_store', 'store_name', array('store_code' => $record_ret['store_code']));
    	$record_ret['store_name'] = !empty($store_name)?$store_name:"";
        $express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $record_ret['express_code']));
        $record_ret['express_name'] = !empty($express_name)?$express_name:"";
        $detail = load_model('oms/SellRecordModel')->get_detail_list_by_code($record_ret['sell_record_code']);
        foreach ($detail as &$d){
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($d['sku'], $key_arr);
            $d = array_merge($d, $sku_info);
        }
        $record_ret['detail'] = $detail;
        //是否已称重
    	$cz_row = $this->get_by_express_no($express_no);
    	if (!empty($cz_row)){
    		return $this->format_ret(-2,$record_ret,'已称重');
    	}
    	return $this->format_ret(1,$record_ret);
    }
    //计算理论重量
    function get_record_weight($sell_record_code){
    	$weight = 0;
    	$detail_record = load_model('oms/SellRecordModel')->get_detail_list_by_code($sell_record_code);
    	foreach ($detail_record as $detail){
    		$sku_info = load_model('goods/SkuCModel')->get_sku_info_by_sku($detail['sku']);
                
    		if ($sku_info['weight']<= 0){
    			$goods_info = load_model('goods/GoodsCModel')->get_goods_by_goods_code($detail['goods_code']);
    			$weight += $goods_info['weight'];
    		} else {
    			$weight += $sku_info['weight'];
    		}
    	}
    	return $this->format_ret(1,$weight/1000);
    }

    function get_record_goods_weight($sell_record_code){
        $record = load_model('oms/SellRecordModel')->get_row(array('sell_record_code'=>$sell_record_code));
        return $this->format_ret(1,$record['data']['goods_weigh']);
    }

    
    //根据快递单号查包裹
    function get_by_express_no($express_no){
    	$sql = "select * from $this->table where express_no=:express_no";
    	$sql_value = array(":express_no"=>$express_no);
    	$row = $this->db->get_row($sql,$sql_value);
    	return $row;
    }
    //计算运费
    function get_weigh_express_money($sell_record_code,$cz_weight){
    	$weigh_express_money = 0;
    	$sell_record  = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);
    	$district = $sell_record['receiver_district'];
        $city = $sell_record['receiver_city'];
        $is_city = 0;
        if(in_array($district, ['441901000000','442001000000'])){
            $district = 0;
        }
        if (empty($district)){
            $district = $city;
            $is_city = 1;
        }

    	$express_code = $sell_record['express_code'];
    	$express_rule = load_model('crm/PolicyExpressRuleModel')->get_by_express_and_district($district,$express_code,$is_city);
    	
    	$express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $express_code));
    	if (empty($express_rule)) {
    		//快递策略中顺丰的可达区域未设置
    		$sql = "select name from base_area where id='{$district}'";
                $area_name = $this->db->getOne($sql);
    		return $this->format_ret(-1, array('express_name' => $express_name, 'area_name' => '（' . $area_name . '）'), "快递策略中配送方式{$express_name}的可达区域未设置$area_name");
    	}
    	if ($express_rule['first_weight'] == 0 ||  $express_rule['added_weight']  == 0 || $express_rule['first_weight_price'] == 0  || $express_rule['added_weight_price'] == 0 ){
    		return $this->format_ret(-2,'',"{$express_name}的首重、续重、首重单价、续重单价必须要设置");
    	}
    	//包裹重量小于首重
    	if ($cz_weight <= $express_rule['first_weight']){
    		$weigh_express_money = $express_rule['first_weight_price'];
    	} else {
    		$added_weight = $cz_weight-$express_rule['first_weight'];
    		//续重规则 0实重 1半重 2过重
    		
    		if ($express_rule['added_weight_type'] == 'g0'){
    			//实重【超出首重的重量 * 续重单价】
    			$added = $added_weight;
    		} elseif ($express_rule['added_weight_type'] == 'g1'){
    			//半重【超出首重的重量不足0.5Kg时讲按照0.5Kg进行收费,超过则按照1Kg的进行收费】
    			$xiaoshu = $added_weight - floor($added_weight);
    			//无小数,则
    			if($xiaoshu == 0){
    				$added = $added_weight;
    			}elseif ($xiaoshu >= 0.5) {
    				$added = floor($added_weight) + 1;
    			}else{
    				$added = floor($added_weight) + 0.5;
    			}
    			
    		}elseif ($express_rule['added_weight_type'] == 'g2') {
                //过重【无论超出首重多少都按照1Kg进行收费】
                $added = ceil($added_weight);
            } 
            $weigh_express_money = $express_rule['first_weight_price'] + number_format(($added/$express_rule['added_weight'])*$express_rule['added_weight_price'], 2, '.', '');
            
    	}
    	return $this->format_ret(1,$weigh_express_money,'');
    	
    }
    //多包裹
    function get_package_by_express_no($express_no){
    	$sql = "select c.* from oms_deliver_record_package a,oms_waves_record b,oms_sell_record c where a.waves_record_id=b.waves_record_id and a.sell_record_code=c.sell_record_code and b.is_cancel=0  and a.express_no=:express_no";
    	$sql_value = array(":express_no"=>$express_no);
    	$row = $this->db->get_row($sql,$sql_value);
    	return $row;
    }
    

   //确认称重出库单
   function confirm($request){
       $sell_record_code = $request['sell_record_code'];
       $request['express_no'] = str_replace('-1-1-','',trim($request['express_no']));
       $express_no = $request['express_no'];
       $cz_weight = $request['cz_weight'];
       $yunfei = $request['yunfei'];
       $this->begin_trans();
       //新增已称重订单表
       $ret = $this->add($request);
       if ($ret['status'] == -1){
           $this->rollback();
       	   return $ret;
       }
       //更新订单表的称重状态
       $ret = $this->update_cz_status($sell_record_code,$express_no,$cz_weight,$yunfei);
       if ($ret['status'] == -1){
       	   $this->rollback();
       	   return $ret;
       }
       $this->commit();
   	   return $ret;
   }
   //更新称重状态
   function update_cz_status($sell_record_code,$express_no,$cz_weight,$yunfei){
	   	//是否启用多包裹
	   	$ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
	   	$is_more_deliver_package =isset($ret_pararm['is_more_deliver_package'])?$ret_pararm['is_more_deliver_package']:'0' ;
	   	//查询多包裹单
	   	if ($is_more_deliver_package == 1){
	   		$record_ret = $this->get_package_by_express_no($express_no);
	   	} else {
	   		$record_ret = $this->get_sell_record_by_express_no($express_no);
	   	}
	   	
	   	if (empty($record_ret)){
	   		return $this->format_ret(-1,'','订单不存在');
	   	}	
   	
   	   $up_data = array('real_weigh'=>$cz_weight,'weigh_express_money'=>$yunfei,"is_weigh"=>1,
   	   				'weigh_time'=>date('Y-m-d H:i:s'),'weigh_person' => ctx()->get_session('user_code'));
   	   
   	   if($is_more_deliver_package == 1){
   	       $this->db->update("oms_deliver_record_package", $up_data,array('sell_record_code'=>$sell_record_code,'express_no'=> $express_no));
   	       //获取该订单下所有包裹的称重运费
   	       $sql = "select sum(real_weigh) as real_weigh,sum(weigh_express_money) as weigh_express_money from oms_sell_record_cz where sell_record_code=:sell_record_code ";
   	   	   $package_row = $this->db->get_row($sql,array('sell_record_code'=>$sell_record_code));
   	   	   $up_data['real_weigh'] = $package_row['real_weigh'];
   	   	   $up_data['weigh_express_money'] = $package_row['weigh_express_money'];
   	   	   //更新销售订单
   	   	   $this->db->update("oms_sell_record", $up_data,array('sell_record_code'=>$sell_record_code));
   	   	   $this->db->update("oms_deliver_record", $up_data,array('sell_record_code'=>$sell_record_code));
   	   } else {
   	       $this->db->update("oms_sell_record", $up_data,array('sell_record_code'=>$sell_record_code,'express_no'=> $express_no));
   	   	   $this->db->update("oms_deliver_record", $up_data,array('sell_record_code'=>$sell_record_code,'express_no'=> $express_no));
   	   }
   	   return $this->format_ret(1,'');
       
   }
   function add($request){
   		$sell_record  = load_model('oms/SellRecordModel')->get_record_by_code($request['sell_record_code']);
	   	$data = array(
	   			'sell_record_code' => $sell_record['sell_record_code'],
	   			'deal_code' => $sell_record['deal_code'],
	   			'deal_code_list' => $sell_record['deal_code_list'],
	   			'sale_channel_code' => $sell_record['sale_channel_code'],
	   			'store_code' => $sell_record['store_code'],
	   			'shop_code' => $sell_record['shop_code'],
	   			'pay_type' => $sell_record['pay_type'],
	   			'pay_code' => $sell_record['pay_code'],
	   			'pay_status' => $sell_record['pay_status'],
	   			'customer_code' => $sell_record['customer_code'],
                                'customer_address_id' => $sell_record['customer_address_id'],
	   			'buyer_name' => $sell_record['buyer_name'],
	   			'receiver_name' => $sell_record['receiver_name'],
	   			'receiver_country' => $sell_record['receiver_country'],
	   			'receiver_province' => $sell_record['receiver_province'],
	   			'receiver_city' => $sell_record['receiver_city'],
	   			'receiver_district' => $sell_record['receiver_district'],
	   			'receiver_street' => $sell_record['receiver_street'],
	   			'receiver_address' => $sell_record['receiver_address'],
	   			'receiver_addr' => $sell_record['receiver_addr'],
	   			'receiver_zip_code' => $sell_record['receiver_zip_code'],
	   			'receiver_mobile' => $sell_record['receiver_mobile'],
	   			'receiver_phone' => $sell_record['receiver_phone'],
	   			'express_code' => $sell_record['express_code'],
	   			'express_money' => $sell_record['express_money'],
	   			'order_money' => $sell_record['order_money'],
	   			'payable_money' => $sell_record['payable_money'],
	   			'paid_money' => $sell_record['paid_money'],
	   			'goods_money' => $sell_record['goods_money'],
	   			'record_time' => $sell_record['record_time'],
	   			'record_date' => $sell_record['record_date'],
	   			'delivery_time' => $sell_record['delivery_time'],
	   			'delivery_date' => $sell_record['delivery_date'],
	   			'create_time' => $sell_record['create_time'],
	   			'is_notice_time' => $sell_record['is_notice_time'],
	   			'check_time' => $sell_record['check_time'],
	   			'goods_weigh' => $sell_record['goods_weigh'],
	   			'payable_money' => $sell_record['express_money'],
	   			'express_no' => $request['express_no'],
	   			'real_weigh' => $request['cz_weight'],
	   			'weigh_express_money' => $request['yunfei'],
	   			'weigh_time' => date('Y-m-d H:i:s'),
	   			'weigh_person' => ctx()->get_session('user_code'),
	   			
	   	);
	   	//$this->db->insert('oms_sell_record_cz', $data);
	   	$ret = $this->insert_dup($data,'UPDATE','real_weigh,weigh_express_money,weigh_time,weigh_person');
	   	return $ret;
   }
   function get_by_page($filter, $onlySql = false, $select = 'rl.*') {
	   	foreach ($filter as  $key=>$v) {
	   		$filter[$key] = trim($v);
	   	}
	   	if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
	   		$filter[$filter['keyword_type']] = trim($filter['keyword']);
	   	}
	   	$sql_values = array();
	   
	   	$sql_main = " FROM {$this->table} rl  WHERE 1  ";
	   
	   	//商店仓库权限
	   	$filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
	   	$sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
	   	$filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
	   	$sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
	   	 
	   	//sell_record_code
	   	if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
	   		$sql_main .= " AND rl.sell_record_code=:sell_record_code ";
	   		$sql_values[':sell_record_code'] = $filter['sell_record_code'];
	   	}
	   	//交易号
	   	if (isset($filter['deal_code']) && $filter['deal_code'] !== '') {
	   		$sql_main .= " AND rl.deal_code_list =:deal_code ";
	   		$sql_values[':deal_code'] = $filter['deal_code'];
	   	}
	   
	   	//快递单号
	   	if (isset($filter['express_no']) && $filter['express_no'] !== '') {
	   		$sql_main .= " AND rl.express_no like :express_no ";
	   		$sql_values[':express_no'] = "%" . $filter['express_no'] . "%";
	   	}
	   	//店铺
	   	if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
	           	    $arr = explode(',',$filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
	   		$sql_main .= " AND rl.shop_code in ( " .$str . " ) ";
	   	}
	   	//仓库
	   	if (isset($filter['store_code']) && $filter['store_code'] !== '') {
	   	           	    $arr = explode(',',$filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
	   		$sql_main .= " AND rl.store_code in ( " . $str. " ) ";
	   	}
	   	//收货人
            if (isset($filter['receiver_name']) && $filter['receiver_name'] !== '') {
//	   		$sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
//	   		$sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';

                $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
                if (!empty($customer_address_id)) {
                    $customer_address_id_str = implode(",", $customer_address_id);
                    $sql_main .= " AND ( rl.receiver_name LIKE :receiver_name  OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                    $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
                } else {
                    $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
                    $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
                }
            }
        //配送方式
	   	if (isset($filter['express_code']) && $filter['express_code'] !== '') {
	   	   	           	    $arr = explode(',',$filter['express_code']);
            $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
	   		$sql_main .= " AND rl.express_code in ( " . $str . " ) ";
	   	}
	  
	   	//称重时间
	   	if (!empty($filter['weigh_time_start'])) {
	   		$sql_main .= " AND rl.weigh_time >= :weigh_time_start ";
                    $weigh_time_start = strtotime(date("Y-m-d", strtotime($filter['weigh_time_start'])));
                    if ($weigh_time_start == strtotime($filter['weigh_time_start'])) {
                        $sql_values[':weigh_time_start'] = $filter['weigh_time_start'] . ' 00:00:00';
                    } else {
                        $sql_values[':weigh_time_start'] = $filter['weigh_time_start'];
                    }
	   	}
	   	if (!empty($filter['weigh_time_end'])) {
	   		$sql_main .= " AND rl.weigh_time <= :weigh_time_end ";
                    $weigh_time_end = strtotime(date("Y-m-d", strtotime($filter['weigh_time_end'])));
                    if ($weigh_time_end == strtotime($filter['weigh_time_end'])) {
                        $sql_values[':weigh_time_end'] = $filter['weigh_time_end'] . ' 23:59:59';
                    } else {
                        $sql_values[':weigh_time_end'] = $filter['weigh_time_end'];
                    }
	   	}
	   	//发货时间
	   	if (!empty($filter['delivery_time_start'])) {
	   		$sql_main .= " AND rl.delivery_time >= :delivery_time_start ";
                    $delivery_time_start = strtotime(date("Y-m-d", strtotime($filter['delivery_time_start'])));
                    if ($delivery_time_start == strtotime($filter['delivery_time_start'])) {
                        $sql_values[':delivery_time_start'] = date('Y-m-d', $delivery_time_start) . ' 00:00:00';
                    } else {
                        $sql_values[':delivery_time_start'] = $filter['delivery_time_start'];
                    }
	   	}
	   	if (!empty($filter['delivery_time_end'])) {
	   		$sql_main .= " AND rl.delivery_time <= :delivery_time_end ";
                    $delivery_time_end = strtotime(date("Y-m-d", strtotime($filter['delivery_time_end'])));
                    if ($delivery_time_end == strtotime($filter['delivery_time_end'])) {
                        $sql_values[':delivery_time_end'] = $filter['delivery_time_end'] . ' 23:59:59';
                    } else {
                        $sql_values[':delivery_time_end'] = $filter['delivery_time_end'];
                    }
	   	}
	  
	   
	   	//增值服务
	   	$sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');
	   
	   	$order_by = " ORDER BY rl.weigh_time desc";
	   
	   	$sql_main .= $order_by;
	   	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        if($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view'){
            foreach ($data['data'] as &$value){
                $value['receiver_name']=$this->name_hidden($value['receiver_name']);
                $value['receiver_mobile']=$this->phone_hidden($value['receiver_mobile']);
                $value['receiver_address'] = $this->address_hidden($value['receiver_address']);
                
            }
        }
	   	$tbl_cfg = array(
	   			'base_shop' => array('fld' => 'shop_name', 'relation_fld' => 'shop_code+shop_code'),
	   			'base_store' => array('fld' => 'store_name', 'relation_fld' => 'store_code+store_code'),
	   			'base_express' => array('fld' => 'express_name', 'relation_fld' => 'express_code+express_code'),
	   	);
	   	require_model('util/GetDataBySqlRelModel');
	   	$obj = new GetDataBySqlRelModel();
	   	$obj->tbl_cfg = $tbl_cfg;
	   	$data['data'] = $obj->get_data_by_cfg(null, $data['data']);
	   	return $this->format_ret(1, $data);
   }
   //波次单称重校验（用于聚划算活动，买的都是单款SKU，数量也一样，只需称一次就行，将重量回写到该波次下所有发货订单中）
   function wave_weight_check($wave_record_id){
       //是否单款
            $sql = "select od.sell_record_code,od.sku,sum(od.num) as num from oms_deliver_record o,oms_deliver_record_detail od where o.deliver_record_id=od.deliver_record_id and o.sell_record_code=od.sell_record_code and o.waves_record_id=$wave_record_id and  o.is_weigh=0 and o.is_deliver=1 group by  od.sell_record_code,od.sku";
   	    $ret = $this->db->getAll($sql);
   	    if (empty($ret)){
   	    	return $this->format_ret(-1,'','无数据（已发货未称重）');
   	    }
   	    $delivery_detail = array();
   	    foreach ($ret as $d_row) {
   	    	/*
   	    	$d = array('sku'=>$d_row['sku'],
   	    			'num' => $d_row['num'],
   	    			);*/
   	    	$delivery_detail[$d_row['sell_record_code']][] = $d_row['sku'].'_'.$d_row['num'];
   	    }
   	    $count_arr = array();
   	    foreach ($delivery_detail as $sell_record_code=>$record_detail) {
   	    	$num = count($record_detail);
   	    	$count_arr[$num] = $sell_record_code;
   	    }
   	    if (count($count_arr) > 1){
   	    	return $this->format_ret(-1,'','波次单订单商品必须一致');
   	    }
   	    $diff_arr = array();
   	    foreach ($delivery_detail as $sell_record_code=>$record_detail) {
   	    	if (empty($diff_arr)){
   	    		$diff_arr = $record_detail;
   	    		continue;
   	    	}
   	    	$ret_dif = array_diff($diff_arr, $record_detail);
   	    	if (empty($ret_dif)){
   	    		continue;
   	    	}
   	    	return $this->format_ret(-1,'','波次单订单商品必须一致');
   	    }
   	    return $this->format_ret(1,'');
   }
   //波次单 整单称重
    function wave_weight($wave_record_id, $weight) {
        $sql = "select o.sell_record_code,o.express_no from oms_deliver_record o where  o.waves_record_id=$wave_record_id and  o.is_weigh=0 and o.is_deliver=1 ";
        $sell_records = $this->db->getAll($sql);
        $msg = "";
        foreach ($sell_records as $record_row) {
            $ret = $this->get_weigh_express_money($record_row['sell_record_code'], $weight);
            if ($ret['status'] != 1) {
                $msg .= "订单" . $record_row['sell_record_code'] . "称重失败，" . $ret['message'];
                continue;
            }
            $request = array(
                'sell_record_code' => $record_row['sell_record_code'],
                'express_no' => $record_row['express_no'],
                'cz_weight' => $weight,
                'yunfei' => $ret['data'],
            );
            $ret = $this->confirm($request);
            if ($ret['status'] != 1) {
                $msg .= "订单" . $record_row['sell_record_code'] . "称重失败，" . $ret['message'];
                continue;
            }
            load_model('oms/SellRecordActionModel')->add_action($record_row['sell_record_code'], '整单称重', "包裹重量：{$weight}Kg，运费：{$request['yunfei']}元");
        }
        if (!empty($msg)) {
            return $this->format_ret(-1, '', $msg);
        }
        return $this->format_ret(1, '');
    }

    function no_weighing_page($filter) {
        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();

        $sql_main = " FROM oms_sell_record rl  WHERE 1 and rl.shipping_status = 4 and rl.order_status != 3 and rl.is_weigh = 0 ";

        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);

        //sell_record_code
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
            $sql_main .= " AND rl.sell_record_code=:sell_record_code ";
            $sql_values[':sell_record_code'] = $filter['sell_record_code'];
        }
        //交易号
        if (isset($filter['deal_code']) && $filter['deal_code'] !== '') {
            $sql_main .= " AND rl.deal_code_list =:deal_code ";
            $sql_values[':deal_code'] = $filter['deal_code'];
        }

        //快递单号
        if (isset($filter['express_no']) && $filter['express_no'] !== '') {
            $sql_main .= " AND rl.express_no like :express_no ";
            $sql_values[':express_no'] = "%" . $filter['express_no'] . "%";
        }
		//收货人
		if (isset($filter['receiver_name']) && $filter['receiver_name'] !== '') {

            $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND ( rl.receiver_name LIKE :receiver_name  OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            } else {
                $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            }
//			$sql_main .= " AND rl.receiver_name like :receiver_name ";
//			$sql_values[':receiver_name'] = "%" . $filter['receiver_name'] . "%";
        }
        //手机号
        if (isset($filter['receiver_mobile']) && $filter['receiver_mobile'] !== '') {
        $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_mobile'], 'tel');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND ( rl.receiver_mobile = :receiver_mobile OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':receiver_mobile'] = $filter['receiver_mobile'];
            } else {
                $sql_main .= " AND rl.receiver_mobile = :receiver_mobile ";
                $sql_values[':receiver_mobile'] = $filter['receiver_mobile'];
            }
//            $sql_main .= " AND rl.receiver_mobile like :receiver_mobile ";
//            $sql_values[':receiver_mobile'] = "%" . $filter['receiver_mobile'] . "%";
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] !== '') {
        	   	           	    $arr = explode(',',$filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ( " . $str . " ) ";
        }
        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] !== '') {
            	   	           	    $arr = explode(',',$filter['express_code']);
            $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
            $sql_main .= " AND rl.express_code in ( " . $str . " ) ";
        }
        //发货时间
        if (!empty($filter['delivery_time_start'])) {
            $sql_main .= " AND rl.delivery_time >= :delivery_time_start ";
            $sql_values[':delivery_time_start'] = $filter['delivery_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['delivery_time_end'])) {
            $sql_main .= " AND rl.delivery_time <= :delivery_time_end ";
            $sql_values[':delivery_time_end'] = $filter['delivery_time_end'] . ' 23:59:59';
        }

        //增值服务
        //$sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');

        $order_by = " ORDER BY rl.delivery_time desc";
        $select = " rl.*";
        $sql_main .= $order_by;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
         $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        if($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view'){
            foreach ($data['data'] as &$value){
                $value['receiver_name']=$this->name_hidden($value['receiver_name']);
                $value['receiver_mobile']=$this->phone_hidden($value['receiver_mobile']);
                $value['receiver_address'] = $this->address_hidden($value['receiver_address']);
                
            }
        }
        $tbl_cfg = array(
            'base_shop' => array('fld' => 'shop_name', 'relation_fld' => 'shop_code+shop_code'),
            'base_store' => array('fld' => 'store_name', 'relation_fld' => 'store_code+store_code'),
            'base_express' => array('fld' => 'express_name', 'relation_fld' => 'express_code+express_code'),
        );
        require_model('util/GetDataBySqlRelModel');
        $obj = new GetDataBySqlRelModel();
        $obj->tbl_cfg = $tbl_cfg;
        $data['data'] = $obj->get_data_by_cfg(null, $data['data']);
        return $this->format_ret(1, $data);
    }
    //计算运费
    function get_trade_weigh_express_money($sell_record,$cz_weight,$type = ''){
    	$weigh_express_money = 0;
    //	$sell_record  = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);
    	$district = $sell_record['receiver_district'];
        $city = $sell_record['receiver_city'];
        if($type == 'jx') {
            $district = $sell_record['district'];
            $city = $sell_record['city'];
        }
        $is_city = 0;
        if (empty($district)){
            $district = $city;
            $is_city = 1;
        }
    	$express_code = $sell_record['express_code'];
    	$express_rule = load_model('crm/PolicyExpressRuleModel')->get_by_express_and_district($district,$express_code,$is_city);
    	
    	$express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $express_code));
    	if (empty($express_rule)) {
    		//快递策略中顺丰的可达区域未设置
    		$sql = "select name from base_area where id='{$district}'";
                $area_name = $this->db->getOne($sql);
    		return $this->format_ret(-1,'',"快递策略中配送方式{$express_name}的可达区域未设置$area_name");
    	}
    	if ($express_rule['first_weight'] == 0 ||  $express_rule['added_weight']  == 0 || $express_rule['first_weight_price'] == 0  || $express_rule['added_weight_price'] == 0 ){
    		return $this->format_ret(-1,'',"{$express_name}的首重、续重、首重单价、续重单价必须要设置");
    	}
    	//包裹重量小于首重
    	if ($cz_weight <= $express_rule['first_weight']){
    		$weigh_express_money = $express_rule['first_weight_price'];
    	} else {
    		$added_weight = $cz_weight-$express_rule['first_weight'];
    		//续重规则 0实重 1半重 2过重
    		
    		if ($express_rule['added_weight_type'] == 'g0'){
    			//实重【超出首重的重量 * 续重单价】
    			$added = $added_weight;
    		} elseif ($express_rule['added_weight_type'] == 'g1'){
    			//半重【超出首重的重量不足0.5Kg时讲按照0.5Kg进行收费,超过则按照1Kg的进行收费】
    			$xiaoshu = $added_weight - floor($added_weight);
    			//无小数,则
    			if($xiaoshu == 0){
    				$added = $added_weight;
    			}elseif ($xiaoshu >= 0.5) {
    				$added = floor($added_weight) + 1;
    			}else{
    				$added = floor($added_weight) + 0.5;
    			}
    			
    		}elseif ($express_rule['added_weight_type'] == 'g2') {
                //过重【无论超出首重多少都按照1Kg进行收费】
                $added = ceil($added_weight);
            } 
            $weigh_express_money = $express_rule['first_weight_price'] + number_format(($added/$express_rule['added_weight'])*$express_rule['added_weight_price'], 2, '.', '');
            
    	}
    	return $this->format_ret(1,$weigh_express_money,'');
    	
    }


    /**
     * 检查重量是否超过预警重量
     * @param $params
     * @return array
     */
    function warn_weight_check($params) {
        $cz_weight = $params['cz_weight'];
        $warn_weight = oms_tb_val('sys_params', 'value', array('param_code' => 'warn_weight'));
        if ($cz_weight > $warn_weight) {
            return $this->format_ret('-1', '', '当前重量超过系统设置的预警重量！');
        }
        return $this->format_ret(1);
    }

}
