<?php
require_model('tb/TbModel');
class Bs3000jModel extends TbModel{
    protected $table = "api_bs3000j_trade";
    function get_ck_sds(){
    	$sql = "select p_id,outside_code,shop_store_code,outside_type from sys_api_shop_store where  p_type=0  ";
    	$out_sd_cks = $this->db->get_all($sql);
    	$out_sds = array();
    	$out_cks = array();
    	foreach ($out_sd_cks as $sd_ck_row) {
    		//商店
    		if ($sd_ck_row['outside_type'] == 0){
    			$out_sds[$sd_ck_row['shop_store_code']] = $sd_ck_row;
    		}
    		//仓库
    		if ($sd_ck_row['outside_type'] == 1){
    			$out_cks[$sd_ck_row['shop_store_code']] = $sd_ck_row;
    		}
    	}
    	return array('sd' => $out_sds,'ck' =>$out_cks);
    }
    function get_erp_shop(){
    	$config_ck_sds = $this->get_ck_sds();
    	$shop_code_arr = array_keys($config_ck_sds['sd']);
        $sql_values = array();
    	$code_str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
    	$sql = "select shop_code,shop_name from base_shop where shop_code in($code_str)";
    	$rs = $this->db->get_all($sql,$sql_values);
    	return $rs;
    }
    function get_by_page($filter){
    	$config_ck_sds = $this->get_ck_sds();
    	$where = "";
    	if (empty($config_ck_sds['ck']) || empty($config_ck_sds['ck'])) {
    		$where .= " and 1=2 ";
    	}
    	$efast_cks = array_keys($config_ck_sds['ck']);
    	$efast_sds = array_keys($config_ck_sds['sd']);
    	$efast_ck_str = "'".join("','",$efast_cks)."'";
    	$efast_sd_str = "'".join("','",$efast_sds)."'";
        
        $sql_values = array();
    	$where .= " and store_code in($efast_ck_str) and shop_code in ($efast_sd_str) ";
    	
    	//店铺
    	if (isset($filter['shop_code']) && $filter['shop_code'] <> '' ) {
            
                $arr = explode(',',$filter['shop_code']);
                $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
    		$where .= " AND shop_code in ({$str}) ";
    	}
    	if (isset($filter['sell_record_code']) && $filter['sell_record_code'] <> '' ) {

    		$where .= " AND sell_record_code = :sell_record_code ";
                $sql_values[':sell_record_code'] = $filter['sell_record_code'];
    	}
    	//是否上传
    	if ($filter['upload_tab'] == 'upload'){
    		//未上传
    		$where .= " AND upload_status = 1 ";
    	} else {
    		//已上传
    		$where .= " AND upload_status != 1 ";
    	}
        
        //上线时间
        $online_time = $this->get_online_time();
        if (!empty($online_time)){
            $where .= " AND delivery_time>= '{$online_time}'";
        }
        
        
    	//after_service_list_tab
        $record_sql = "select om.sell_record_code,'销售' as order_type,om.delivery_time,om.payable_money,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status ,bs.upload_time,bs.upload_msg from oms_sell_record om left join api_bs3000j_trade bs on om.sell_record_code=bs.sell_record_code and bs.order_type=1 where om.shipping_status=4 ";
        $return_sql = "select om.sell_return_code as sell_record_code,'退货' as order_type,om.receive_time as delivery_time,om.refund_total_fee as payable_money,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status,bs.upload_time,bs.upload_msg from oms_sell_return om left join api_bs3000j_trade bs on om.sell_return_code=bs.sell_record_code and  bs.order_type=2 where om.return_shipping_status=1 and om.return_type!=1 ";
        $sql_main = "FROM ($record_sql union all $return_sql) as tmp WHERE 1 $where";


    	$select = '*';
    	$sql_main .= " order by delivery_time desc ";
    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
		foreach ($data['data'] as &$row) {
		}
    	filter_fk_name($data['data'], array('shop_code|shop','store_code|store', ));
    	$ret_data = $data;
    	
    	return $this->format_ret(1, $ret_data);
    }
    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*"){
    
    	$sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
    	$data = $this->db->get_row($sql, array(":{$field_name}" => $value));
    
    	if ($data) {
    		return $this->format_ret('1', $data);
    	} else {
    		return $this->format_ret('-1', '', 'get_data_fail');
    	}
    }
    
    public function upload_trade($record_codes){
    	$record_code_arr = explode(',', $record_codes);
    	$record_code_str = "'".join("','",$record_code_arr)."'";
    	$record_sql = "select om.sell_record_code,1 as order_type,om.delivery_time,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status  from oms_sell_record om left join api_bs3000j_trade bs on om.sell_record_code=bs.sell_record_code where om.shipping_status=4 ";
    	$return_sql = "select om.sell_return_code as sell_record_code,2 as order_type,om.receive_time as delivery_time,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status from oms_sell_return om left join api_bs3000j_trade bs on om.sell_return_code=bs.sell_record_code where om.return_shipping_status=1  ";
    	$sql = "SELECT * FROM ($record_sql union all $return_sql) as tmp WHERE  upload_status != 1 AND sell_record_code IN($record_code_str)";
    	$record_ret = $this->db->get_all($sql);
    	//获取erp配置
    	$config_ck_sds = $this->get_ck_sds();
    	foreach ($record_ret as $record_row) {
    		$config_id = $config_ck_sds['ck'][$record_row['store_code']]['p_id'];
    		$params['record_code'] = $record_row['sell_record_code'];
    		if (empty($config_id)) {
    			continue;
    		}
    		$params['erp_config_id'] = $config_id;
    		//销售订单
    		if ($record_row['order_type'] == 1){
    			$result = load_model('sys/EfastApiModel')->request_api('bs3000j_api/record_upload_one', $params);
    		} else {
    			//销售退单
    			$result = load_model('sys/EfastApiModel')->request_api('bs3000j_api/return_upload_one', $params);
    		}
    	}
    	return $this->format_ret('1', '', '上传完成');
    } 
    
    function get_wbm_by_page($filter) {
        $where = '';
        $config_ck_sds = $this->get_ck_sds();
              $sql_values = array();
    	if (!empty($config_ck_sds['ck'])) {
    		$efast_cks = array_keys($config_ck_sds['ck']);
            $efast_ck_str = "'".join("','",$efast_cks)."'";
            $where .= " and store_code in($efast_ck_str) ";
    	}
    	
        
        if (isset($filter['record_code']) && $filter['record_code'] <> '') {
            $where .= " AND record_code = :record_code ";
                        $sql_values[':record_code'] = $filter['record_code'];
        }
        if (isset($filter['store_code']) && $filter['store_code'] <> '') {
            $where .= " AND store_code = :store_code ";
                   $sql_values[':store_code'] = $filter['store_code'];
        }
        //是否上传
    	if ($filter['upload_tab'] == 'upload'){
    		//未上传
    		$where .= " AND upload_status = 1 ";
    	} else {
    		//已上传
    		$where .= " AND upload_status != 1 ";
    	}
        if(isset($filter['order_type']) && $filter['order_type'] == 'all'){
             unset($filter['order_type']);
        }
        if(isset($filter['order_type']) && $filter['order_type'] <> ''){
            if($filter['order_type'] == 1){
                 $where .= " AND order_type = 1 ";
             }else{
                 $where .= " AND order_type = 2 ";
             }
        }
        //上线时间
        $online_time = $this->get_online_time();
        if (!empty($online_time)){
           $where .= " AND record_time>= '{$online_time}'";
        }   
       
        $record_sql = "select r.store_out_record_id as record_id,r.record_code,1 as order_type,r.record_time,r.money,r.store_code,r.num,IFNULL(w.upload_status,0) as upload_status,IFNULL(w.upload_time,'') as upload_time,w.upload_msg  from wbm_store_out_record r left join api_bs3000j_wbm_record as w on r.record_code=w.record_code and w.order_type=1 where r.is_sure=1 and r.is_store_out=1 ";
        $return_sql = "select r.return_record_id as record_id,r.record_code,2 as order_type,r.record_time,r.money,r.store_code,r.num,IFNULL(w.upload_status,0) as upload_status,IFNULL(w.upload_time,'') as upload_time,w.upload_msg  from wbm_return_record r left join api_bs3000j_wbm_record as w on r.record_code=w.record_code and w.order_type=2 where r.is_sure=1 and r.is_store_in=1  ";
        $sql_main = " FROM ($record_sql union all $return_sql) as tmp WHERE 1 {$where}";
  

        $select = '*';
        $sql_main .= " order by record_time desc ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $outside_store = $this->get_outside_store_code();
        $store_code_name = load_model('base/StoreModel')->get_code_name();
        foreach ($data['data'] as &$value) {
            $value['order_type'] = $value['order_type'] == 1 ? '批发销货单' : '批发退货单';
            $value['upload_status_name'] = $value['upload_status'] == 1 ? '已上传' : '未上传';
            $value['store_name'] = $store_code_name[$value['store_code']] . '[' . $outside_store[$value['store_code']] . ']';
            $value['record_code_str'] = "'" . $value['record_code'] . ',' . $value['record_id'] . "'";
		}
        return $this->format_ret(1, $data);
    }
    function get_online_time(){
        //上线时间
        $sql = "select online_time from erp_config where erp_system=1 ";
        $online_time_arr = $this->db->getAll($sql);
        $online_time= NULL;
        foreach ($online_time_arr as $time_row){
            if(!empty($time_row['online_time']) && (empty($online_time) || $online_time<$time_row['online_time'])){
              
                $online_time = $time_row['online_time']; 
            }
        }
       return $online_time;
    }
    /**
     * @todo 获取bs3000j对应的仓库
     */
    function get_erp_store_code(){
        $sql = "select outside_code,bs.store_code,bs.store_name from sys_api_shop_store ss left join base_store bs on ss.shop_store_code=bs.store_code where  p_type=0 and outside_type = 1";
        $data = $this->db->get_all($sql);
        foreach ($data as &$value){
            $value['store_name'] = $value['store_name'].'[BS3000J-'.$value['outside_code'].']';
            unset($value['outside_code']);
        }
        $ret_data = array_merge(array(array('', '请选择')), $data);
        return $ret_data;
    }
    function get_outside_store_code(){
        $sql = "select p_id,outside_code,shop_store_code,outside_type,o2o_store from sys_api_shop_store where  p_type=0  and outside_type = 1";
        $data = $this->db->get_all($sql);
        $ret = array();
        foreach ($data as $value){
            $ret[$value['shop_store_code']] = $value['outside_code'];
        }
        return $ret;
    }
    /**
     * @todo 批发单据上传
     */
    function wbm_upload($record_codes) {
        $record_code_arr = explode(',', $record_codes);
    	$record_code_str = "'" . join("','",$record_code_arr) . "'";
        //获取仓库代码
        $config_ck_sds = $this->get_ck_sds();
        $config_ck_arr = array_keys($config_ck_sds['ck']);
        $config_ck_str = "'" . join("','",$config_ck_arr) . "'";
        $record_sql = "select r.record_code,1 as order_type,r.record_time,r.money,r.store_code,r.num,IFNULL(w.upload_status,0) as upload_status,IFNULL(w.upload_time,'') as upload_time  from wbm_store_out_record r left join api_bs3000j_wbm_record as w on r.record_code=w.record_code and w.order_type=1 where r.is_sure=1 and r.is_store_out=1 ";
        $return_sql = "select r.record_code,2 as order_type,r.record_time,r.money,r.store_code,r.num,IFNULL(w.upload_status,0) as upload_status,IFNULL(w.upload_time,'') as upload_time  from wbm_return_record r left join api_bs3000j_wbm_record as w on r.record_code=w.record_code and w.order_type=2 where r.is_sure=1 and r.is_store_in=1  ";
        $sql = "SELECT * FROM ($record_sql union all $return_sql) as tmp WHERE record_time>= '{$this->erp_conf['online_time']}' and upload_status != 1 and store_code in({$config_ck_str}) AND record_code IN($record_code_str)";
        $record_ret = $this->db->get_all($sql);
        //获取erp配置
        if(!empty($record_ret)){
            foreach ($record_ret as $record_row) {
                $config_id = $config_ck_sds['ck'][$record_row['store_code']]['p_id'];
                if (empty($config_id)) {
                    continue;
                }
                $params['erp_config_id'] = $config_id;
                $params['record_code'] = $record_row['record_code'];
                if ($record_row['order_type'] == 1){
                    //批发销货单
                    $result = load_model('sys/EfastApiModel')->request_api('bs3000j_api/wbm_record_upload_one', $params);
                } else {
                    //批发退货单
                    $result = load_model('sys/EfastApiModel')->request_api('bs3000j_api/wbm_return_upload_one', $params);
                }
            }
            if($result['resp_data']['code'] != 0){
                return $this->format_ret('-1', '', '上传失败');
            }
        }else{
            return $this->format_ret('-1', '', '上传失败');
        }
        
        return $this->format_ret('1', '', '上传完成');
    }
}