<?php
require_model('tb/TbModel');
class FxTaobaoRefundModel extends TbModel{
    protected $table = "api_taobao_fx_refund";

    
    function get_by_page($filter){
		if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
			$filter[$filter['keyword_type']] = $filter['keyword'];
		}
    	//print_r($filter);die;
    
        //导出
        if ($filter['ctl_type'] == 'export') {
            return $this->taobao_refund_search_csv($filter);
        }
    	$sql_main1 = $sql_main = "FROM {$this->table} rl 
    	           LEFT JOIN api_taobao_fx_trade r2 on rl.purchase_order_id = r2.fenxiao_id WHERE 1";
    	$sql_values = array();
    	//商店权限1
    	//load_model('base/ShopModel')->get_purview_shop()
    	$filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
    	$sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code',$filter_shop_code,'get_purview_tbfx_shop');

    	//申请时间
    	if (isset($filter['refund_create_time_start']) && $filter['refund_create_time_start'] != '') {
    		$sql_main .= " AND (rl.refund_create_time >= :refund_create_time_start )";
    		$sql_values[':refund_create_time_start'] = $filter['refund_create_time_start'] . ' 00:00:00';
    	}
    	if (isset($filter['refund_create_time_end']) && $filter['refund_create_time_end'] != '') {
    		$sql_main .= " AND (rl.refund_create_time<= :refund_create_time_end )";
    		$sql_values[':refund_create_time_end'] = $filter['refund_create_time_end'] . ' 23:59:59';
    	}
    	
    	//分销商
    	if (isset($filter['distributor_nick']) && $filter['distributor_nick'] != '') {
    	
    		$sql_main .= " AND rl.distributor_nick LIKE :distributor_nick ";
    		$sql_values[':distributor_nick'] = $filter['distributor_nick'].'%';
    	}
    
    	
    	//转单状态
    	if (isset($filter['is_change']) && $filter['is_change'] != '') {
    		$sql_main .= " AND rl.is_change = :is_change ";
    		$sql_values[':is_change'] = $filter['is_change'];
    	}
         

		
    	//店铺
    	/*if (isset($filter['shop_code']) && $filter['shop_code'] <> '' ) {
    		$arr = explode(',',$filter['shop_code']);
    		$str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
    		$sql_main .= " AND rl.shop_code in ({$str}) ";
    	}*/
   
    	//交易号
    	if (isset($filter['purchase_order_id']) && $filter['purchase_order_id'] != '') {
    		$sql_values = array();
    		$sql_main = $sql_main1;
    		$sql_main .= " AND rl.purchase_order_id LIKE :purchase_order_id ";
    		$sql_values[':purchase_order_id'] = $filter['purchase_order_id'].'%';
    	}
    	//退单
    	if (isset($filter['sub_order_id']) && $filter['sub_order_id'] != '') {
    		$sql_values = array();
    		$sql_main = $sql_main1;
    		$sql_main .= " AND rl.sub_order_id LIKE :sub_order_id ";
    		$sql_values[':sub_order_id'] = $filter['sub_order_id'].'%';
    	}
       	//退单编号
    	if (isset($filter['refund_record_code']) && $filter['refund_record_code'] != '') {
    		$sql_values = array();
    		$sql_main = $sql_main1;
    		$sql_main .= " AND rl.refund_record_code LIKE :refund_record_code ";
    		$sql_values[':refund_record_code'] = $filter['refund_record_code'].'%';
    	}
   
    	if (isset($filter['buyer_nick']) && $filter['buyer_nick'] != '') {
    		$sql_values = array();
    		$sql_main = $sql_main1;
    		$sql_main .= " AND r2.buyer_nick LIKE :buyer_nick ";
    		$sql_values[':buyer_nick'] = $filter['buyer_nick'].'%';
    	}	
       //增值服务
       // $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.source');

    	$select = 'rl.*';
    	//echo $sql_main;
		$sql_main .= " group by rl.id order by rl.refund_create_time desc";
    	//echo $sql_main;
    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

    	//$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
    	filter_fk_name($data['data'], array('shop_code|shop', ));
    	//print_r($data);
    	$ret_status = OP_SUCCESS;
    	$ret_data = $data;
//
//    	foreach($ret_data['data'] as $k=>$sub_data){
//	    	if ($sub_data['pay_type'] == 1){
//		    	$ret_data['data'][$k]['pay_time'] = '';
//	    	}
//    	}
    	
    	return $this->format_ret($ret_status, $ret_data);

    }
    

    //导出
    public function taobao_refund_search_csv( $filter) {  
    
       $sql_main = "FROM {$this->table} rl 
    	           LEFT JOIN api_taobao_fx_trade r2 on rl.purchase_order_id = r2.fenxiao_id LEFT JOIN api_taobao_fx_order r3 on r3.ttid = r2.ttid WHERE 1";
    	$sql_values = array();
    	//商店权限1
    	$filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
    	$sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code',$filter_shop_code);

    	//申请时间
    	if (isset($filter['refund_create_time_start']) && $filter['refund_create_time_start'] != '') {
    		$sql_main .= " AND (rl.refund_create_time >= :refund_create_time_start )";
    		$sql_values[':record_time_start'] = $filter['refund_create_time_start'] . ' 00:00:00';
    	}   
    	if (isset($filter['refund_create_time_end']) && $filter['refund_create_time_end'] != '') {
    		$sql_main .= " AND (rl.refund_create_time<= :refund_create_time_end )";
    		$sql_values[':refund_create_time_end'] = $filter['refund_create_time_end'] . ' 23:59:59';
    	}
    	
    	//分销商
    	if (isset($filter['distributor_nick']) && $filter['distributor_nick'] != '') {
    	
    		$sql_main .= " AND rl.distributor_nick LIKE :distributor_nick ";
    		$sql_values[':distributor_nick'] = $filter['distributor_nick'].'%';
    	}
     	
    	//转单状态
    	if (isset($filter['is_change']) && $filter['is_change'] != '') {
    		$sql_main .= " AND rl.is_change = :is_change ";
    		$sql_values[':is_change'] = $filter['is_change'];
    	}
	
    	//店铺
//    	if (isset($filter['shop_code']) && $filter['shop_code'] <> '' ) {
//    		$arr = explode(',',$filter['shop_code']);
//    		$str = "'".join("','",$arr)."'";
//    		$sql_main .= " AND rl.shop_code in ({$str}) ";
//    	}
   
    	//交易号
    	if (isset($filter['purchase_order_id']) && $filter['purchase_order_id'] != '') {
    		$sql_main .= " AND rl.purchase_order_id LIKE :purchase_order_id ";
    		$sql_values[':purchase_order_id'] = $filter['purchase_order_id'].'%';
    	}
    	//退单
    	if (isset($filter['sub_order_id']) && $filter['sub_order_id'] != '') {
    		$sql_main .= " AND rl.sub_order_id LIKE :sub_order_id ";
    		$sql_values[':sub_order_id'] = $filter['sub_order_id'].'%';
    	}
       	//退单编号
    	if (isset($filter['refund_record_code']) && $filter['refund_record_code'] != '') {
    		$sql_main .= " AND rl.refund_record_code LIKE :refund_record_code ";
    		$sql_values[':refund_record_code'] = $filter['refund_record_code'].'%';
    	}	
   
    	if (isset($filter['buyer_nick']) && $filter['buyer_nick'] != '') {
    		$sql_main .= " AND r2.buyer_nick LIKE :buyer_nick ";
    		$sql_values[':buyer_nick'] = $filter['buyer_nick'].'%';
    	}
     
    	$select = 'rl.*,r3.sku_properties,r3.auction_price,r3.bill_fee,r3.num,r3.discount_fee,r3.price,r3.tc_preferential_type,r3.sku_outer_id,r3.distributor_payment as distributor_payment_mx,r3.title,r3.item_outer_id';

    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

    	filter_fk_name($data['data'], array('shop_code|shop', ));

    	$ret_status = OP_SUCCESS;
    	$ret_data = $data;
        foreach($ret_data['data'] as $k=>&$sub_data){
            if($sub_data['is_change']==0){
                $sub_data['is_change_status']='未转单';
            }elseif($sub_data['is_change']==1){
                $sub_data['is_change_status']='已转单';
            }else{
                 $sub_data['is_change_status']='转单失败';
            }
        }
    	return $this->format_ret($ret_status, $ret_data);     
     
    }

    
    function td_traned($ids){
        $sql_values = array();
        $id_arr = explode(',', $ids);
        $id_str = $this->arr_to_in_sql_value($id_arr, 'id', $sql_values);
        $sql = " update {$this->table} set  is_change = 1 where id in({$id_str})";
        $this->db->query($sql, $sql_values);
        return $this->format_ret(1);
    }
	function td_traned_one($ids,$is_change=1) {
		$user_name = CTx()->get_session('user_name');
		$data = array('is_change'=>$is_change,'change_remark'=>$user_name.'设为已处理');
		$where = " sub_order_id in({$ids})";
		$ret = parent::update($data, $where);
		return $ret;
	}
    function tran(){
        //$request['ids']
         $id_arr = explode(',', $ids);
         $error_msg = '';
         $ret_status = 1;
         foreach($id_arr as $id){
            $ret =   load_model('oms/TranslateRefundModel')->translate_fx_refund($id);
            if($ret['status']<0){
                $ret_status = -1;
                $error_msg .= "失败退单".$id.':'.$ret['message'].",";
            }
         }
         if($error_msg==''){
             $error_msg = "转单成功";
         }
         
        return $this->format_ret($ret_status,'',$error_msg);
    }
    
    
     function get_fail_order_num() {
        $sql = "select count(1) from api_taobao_fx_refund where is_change=-1";
        return $this->db->get_value($sql);
    }
    
    
}
