<?php
require_model('tb/TbModel');
class FxTaobaoTradeModel extends TbModel{
    protected $table = "api_taobao_fx_trade";
    protected $detail_table = "api_taobao_fx_order";
    
    function get_by_page($filter){
    	//print_r($filter);die;
    	if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
    		$filter[$filter['keyword_type']] = $filter['keyword'];
    	}
    	$sql_join = "";

    	$sql_main1 = $sql_main = "FROM {$this->table} rl 
    	           LEFT JOIN api_taobao_fx_order r2 on rl.ttid = r2.ttid WHERE 1";
    	$sql_values = array();
    	//商店权限1
    	//load_model('base/ShopModel')->get_purview_shop()
    	$filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
    	$sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code',$filter_shop_code,'get_purview_tbfx_shop');

    	//编码
    	if (isset($filter['goods_code']) && $filter['goods_code'] != ''){
    		$sql_main .= " AND r2.item_outer_id = :item_outer_id";
    		$sql_values[':item_outer_id'] = $filter['goods_code'];
    	}
    	
    	//条码
    	if (isset($filter['goods_barcode']) && $filter['goods_barcode'] != ''){
    		$sql_main .= " AND r2.sku_outer_id = :sku_outer_id";
    		$sql_values[':sku_outer_id'] = $filter['goods_barcode'];
    	}
    	
    	//下单时间
    	if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
    		$sql_main .= " AND (rl.created >= :record_time_start )";
    		$sql_values[':record_time_start'] = $filter['record_time_start'];
    	}   
    	if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
    		$sql_main .= " AND (rl.created <= :record_time_end )";
    		$sql_values[':record_time_end'] = $filter['record_time_end'];
    	}
    	
    	//支付时间
    	if (isset($filter['pay_time_start']) && $filter['pay_time_start'] != '') {
    		$sql_main .= " AND (rl.pay_time >= :pay_time_start )";
    		$sql_values[':pay_time_start'] = $filter['pay_time_start'] . ' 00:00:00';
    	}
    	if (isset($filter['pay_time_end']) && $filter['pay_time_end'] != '') {
    		$sql_main .= " AND (rl.pay_time <= :pay_time_end )";
    		$sql_values[':pay_time_end'] = $filter['pay_time_end'] . ' 23:59:59';
    	}

    	
    	//买家昵称
//     	if (isset($filter['buyer_nick']) && $filter['buyer_nick'] != '') {

//     		$sql_main .= " AND rl.buyer_nick LIKE :buyer_nick ";
//     		$sql_values[':buyer_nick'] = $filter['buyer_nick'].'%';
//     	}

    	//分销商
    	if (isset($filter['distributor_username']) && $filter['distributor_username'] != '') {
    	
    		$sql_main .= " AND rl.distributor_username LIKE :distributor_username ";
    		$sql_values[':distributor_username'] = $filter['distributor_username'].'%';
    	}
    	//是否允许转单
    	if (isset($filter['is_invo']) && $filter['is_invo'] != '' && $filter['is_invo'] != 'all') {
    		$sql_main .= " AND rl.is_invo = :is_invo ";
    		$sql_values[':is_invo'] = $filter['is_invo'];
    	}
    	
    	//转单状态
    	if (isset($filter['is_change']) && $filter['is_change'] != '') {
    		$sql_main .= " AND rl.is_change = :is_change ";
    		$sql_values[':is_change'] = $filter['is_change'];
    	}
         
//     	//销售平台  
//     	if (isset($filter['source']) && !empty($filter['source'])&&$filter['source']!='select_all') {
//     		$sql_main .= " AND rl.distributor_from = :sale_channel_code ";
//     		$sql_values[':sale_channel_code'] = $filter['source'];
//     	}
    	
		
    	//店铺
    	/*if (isset($filter['shop_code']) && $filter['shop_code'] <> '' ) {
    		$arr = explode(',',$filter['shop_code']);
    		$str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
    		$sql_main .= " AND rl.shop_code in ({$str}) ";
    	}*/
   
    	//交易号
    	if (isset($filter['fenxiao_id']) && $filter['fenxiao_id'] != '') {
    		$sql_values = array();
    		$sql_main = $sql_main1;
    		$sql_main .= " AND rl.fenxiao_id LIKE :fenxiao_id ";
    		$sql_values[':fenxiao_id'] = $filter['fenxiao_id'].'%';
    	}

    	
       //增值服务
       // $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.source');
        //导出
        if ($filter['ctl_type'] == 'export') {
            return $this->taobao_trade_search_csv($sql_main, $sql_values, $filter);
        }
    	$select = 'rl.*';
    	//echo $sql_main;
    	$sql_main .= " group by rl.ttid order by rl.created desc";
    	//echo $sql_main;
    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

    	//$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
    	filter_fk_name($data['data'], array('shop_code|shop', ));
    	//print_r($data);
    	$ret_status = OP_SUCCESS;
    	$ret_data = $data;

    	foreach($ret_data['data'] as $k=> &$sub_data){
            if ($sub_data['pay_type'] == 1){
                    $ret_data['data'][$k]['pay_time'] = '';
            }
            //系统抓单、变更时间
            if (empty($sub_data['last_update_time'])) {
                $sub_data['last_update_time'] = $sub_data['first_insert_time'];
            }
    	}
    	
    	return $this->format_ret($ret_status, $ret_data);
    	/*
        $sql_values = array();
    	$select = 'tl.*';
    	$sql_main = "FROM api_order tl WHERE 1 ";
    	$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
    	return $this->format_ret(1, $data);
    	*/
    }
    
    
    //导出
    public function taobao_trade_search_csv($sql_main, $sql_values, $filter) {  
    	$select = 'rl.*,r2.sku_properties,r2.auction_price,r2.bill_fee,r2.num,r2.discount_fee,r2.price,r2.tc_preferential_type,r2.sku_outer_id,r2.title,r2.item_outer_id,r2.buyer_payment as buyer_payment_mx,r2.distributor_payment as distributor_payment_mx';//,r2.distributor_payment
        
       $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

    	filter_fk_name($data['data'], array('shop_code|shop', ));

    	$ret_status = OP_SUCCESS;
    	$ret_data = $data;

    	foreach($ret_data['data'] as $k=>&$sub_data){
	    	if ($sub_data['pay_type'] == 1){
		    	$ret_data['data'][$k]['pay_time'] = '';
	    	}
                 $sub_data['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $sub_data['logistics_company_name']));       
    	}
    	
    	return $this->format_ret($ret_status, $ret_data);
     
    }
    
    
    
    
    
    
    //根据id查询
    function get_by_id($id) {
    	return  $this->get_row(array('ttid'=>$id));
    }
    
    
    public function barcode_update($params){
    	$sql = "select o.sku_outer_id,o.sku_id,o.oid from api_taobao_fx_trade t "
    		. " INNER JOIN api_taobao_fx_order o ON o.fenxiao_id=t.fenxiao_id"
    		. " where t.is_invo<>1 and t.fenxiao_id=:fenxiao_id AND t.shop_code=:shop_code ";
    	$sql_values = array(':fenxiao_id'=>$params['fenxiao_id'],':shop_code'=>$params['shop_code']);
    	$api_fx_detail_info = $this->db->get_all($sql,$sql_values);
    	$num = 0;
    	if (!empty($api_fx_detail_info)){
    		foreach ($api_fx_detail_info as $detail){
    			if ($detail['sku_id']){
    				$sql = "select outer_id from api_taobao_fx_product_sku where id = '{$detail['sku_id']}'";
    				$outer_id = $this->db->getOne($sql);
    				if ($outer_id != '' && $outer_id != $detail['outer_id']){
    					$update_sql = "update api_taobao_fx_order set sku_outer_id = '{$outer_id}' where detail_id = {$detail['oid']}";
    					$res = $this->db->query($update_sql);
    					if ($res == true){
    						$num++;
    					}
    				}
    			}
    		}
    		return array('status' => 1,'data' => $num,'message' => '更新完成，成功更新'.$num.'条');
    	}
    	return array('status' => -1,'data' => $num,'message' => '更新失败');
    }
    
    function get_trade_info($id){
    	$sql = "select rl.*, count(r2.num) total_num FROM {$this->table} rl 
    	           LEFT JOIN api_taobao_fx_order r2 on rl.ttid = r2.ttid WHERE rl.ttid = :id group by rl.ttid";
    	$ret = $this->db->get_row($sql,array(':id' => $id));
    	return $ret;
    }
    
    public function get_by_field_order_all($field_name,$value, $select = "*") {
    	$sql = "select {$select} from {$this->detail_table} where {$field_name} = :{$field_name}";
    	$data = $this -> db -> get_all($sql, array(":{$field_name}" => $value));
    	return $data;
    }
    function td_traned($ids,$is_change=1) {
    	$user_name = CTx()->get_session('user_name');
    	$data = array('is_change'=>$is_change,'change_remark'=>$user_name.'设置为已转单');
    	$where = " ttid in({$ids})";
    	$ret = parent::update($data, $where);
    	return $ret;
    }
    function td_no_traned($ids,$is_change=0){
    	$user_name = CTx()->get_session('user_name');
        $change_remark =  "批量置为未转单";
        $data = array('is_change' => $is_change, 'change_remark' => $change_remark);
        $where = " fenxiao_id in({$ids})";
        $ret = parent::update($data, $where);
        return $ret;
    }
    
    //保存barcode
    public function save($b){
    	$this->begin_trans();
    	try{
    		foreach($b as $id => $barcode){
    			$r = $this->db->update('api_taobao_fx_order', array('sku_outer_id'=>$barcode), array('oid'=>$id));
    			if($r !== true){
    				throw new Exception('保存失败');
    			}
    		}
    
    		$this->commit();
    		return array('status'=>1, 'message'=>'更新成功');
    	} catch(Exception $e){
    		$this->rollback();
    		return array('status'=>-1, 'message'=>$e->getMessage());
    	}
    }
    
    
    
     function get_fail_order_num() {
		$shop_code_str=load_model('base/ShopModel')->get_sql_purview_shop('shop_code');
        $sql = "select count(1) from api_taobao_fx_trade where is_change=-1" . $shop_code_str;
        return $this->db->get_value($sql);
    }
    
    function down_trade($request) {
        $params=array();
        $params['shop_code']=$request['shop_code'];
        $params['start_time']=$request['start_time'];
        $params['end_time']=$request['end_time'];
        $params['method']='fenxiao_trade_sync';
        $result = load_model('sys/EfastApiTaskModel')->request_api('sync', $params);

        return $result;

    }

    //下载进度
    function down_trade_check($request){
        $params=array();
        $params['task_sn']=$request['task_sn'];
        $result = load_model('sys/EfastApiTaskModel')->request_api('check', $params);

        return $result;

    }
}