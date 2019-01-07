<?php
require_model('tb/TbModel');
require_lib('util/oms_util', true);
class BserpModel extends TbModel{
    protected $table = "api_bserp_trade";
    
    private $record_type = array(
        'all' => '全部',
        'sell_record' => '销售订单',
        'sell_return' => '销售退单',
        'wbm_store_out' => '批发销货单',
        'wbm_return' => '批发退货单',
    );
    
    function get_ck_sds(){
    	$sql = "select p_id,outside_code,shop_store_code,outside_type,o2o_store from sys_api_shop_store where  p_type=0  ";
    	$out_sd_cks = $this->db->get_all($sql);
    	$out_sds = array();
    	$out_cks = array();
    	foreach ($out_sd_cks as $sd_ck_row) {
    		//商店
    		if ($sd_ck_row['outside_type'] == 0){
    			$out_sds[$sd_ck_row['shop_store_code']] = $sd_ck_row;
    		}
    		//仓库&& $sd_ck_row['o2o_store']==0
    		if ($sd_ck_row['outside_type'] == 1 ){
    			$out_cks[$sd_ck_row['shop_store_code']] = $sd_ck_row;
    		}
    	}
    	return array('sd' => $out_sds,'ck' =>$out_cks);
    }
    function get_erp_shop(){
    	$config_ck_sds = $this->get_ck_sds();
    	$shop_code_arr = array_keys($config_ck_sds['sd']);
    	$code_str = "'".implode("','", $shop_code_arr)."'";
    	$sql = "select shop_code,shop_name from base_shop where shop_code in($code_str)";
    	$rs = $this->db->get_all($sql);
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
        $sql_values = array();
    	$efast_ck_str = $this->arr_to_in_sql_value($efast_cks, 'store_code', $sql_values);
    	$efast_sd_str = $this->arr_to_in_sql_value($efast_sds, 'shop_code', $sql_values);
    	$where .= " and store_code in($efast_ck_str) and shop_code in ($efast_sd_str) ";
    	
    	//店铺
    	if (isset($filter['shop_code']) && $filter['shop_code'] <> '' ) {
    		$arr = explode(',',$filter['shop_code']);
    		$str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
    		$where .= " AND shop_code in ({$str}) ";
    	}
        //商品条码
        $select_join = '';
        $join_record = '';
        $join_return = '';
        $where_join='';
    	if (isset($filter['barcode']) && $filter['barcode'] <> '' ) {
    		$arr = explode(',',$filter['barcode']);
                foreach ($arr as $value) {
                    $sku[] = oms_tb_val('goods_barcode', 'sku', array('barcode'=>$value));
                }
    		$str = $this->arr_to_in_sql_value($sku, 'sku', $sql_values);
                $select_join= "omd.sku,";
                $join_record = " oms_sell_record_detail omd on om.sell_record_code = omd.sell_record_code left join ";
                $join_return = " oms_sell_return_detail omd on om.sell_return_code = omd.sell_return_code left join ";
    		$where_join .= " AND omd.sku in ({$str}) ";
    	}
    	if (isset($filter['sell_record_code']) && $filter['sell_record_code'] <> '' ) {
    		$arr = explode(',',$filter['sell_record_code']);
    		$str = $this->arr_to_in_sql_value($arr, 'sell_record_code', $sql_values);
    		$where .= " AND sell_record_code in ({$str})";
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
        $record_sql = "select {$select_join} om.sell_record_code,'销售' as order_type,om.delivery_time,om.payable_money,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status ,bs.upload_time,bs.upload_msg from oms_sell_record om left join {$join_record} api_bserp_trade bs on om.sell_record_code=bs.sell_record_code and bs.order_type=1 where om.shipping_status=4 {$where_join} ";
        $return_sql = "select {$select_join} om.sell_return_code as sell_record_code,'退货' as order_type,om.receive_time as delivery_time,om.refund_total_fee as payable_money,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status,bs.upload_time,bs.upload_msg from oms_sell_return om left join {$join_return} api_bserp_trade bs on om.sell_return_code=bs.sell_record_code and  bs.order_type=2 where om.return_shipping_status=1 and om.return_type!=1 {$where_join} ";
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
    
    function get_wbm_by_page($filter) {
        $where = '';
        $config_ck_sds = $this->get_ck_sds();
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
            $where .= " AND store_code = :store_code";
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
        $record_sql = "select r.store_out_record_id as record_id,r.record_code,1 as order_type,r.record_time,r.money,r.store_code,r.num,IFNULL(w.upload_status,0) as upload_status,IFNULL(w.upload_time,'') as upload_time,w.upload_msg  from wbm_store_out_record r left join api_bserp_wbm_record as w on r.record_code=w.record_code and w.order_type=1 where r.is_sure=1 and r.is_store_out=1 ";
        $return_sql = "select r.return_record_id as record_id,r.record_code,2 as order_type,r.record_time,r.money,r.store_code,r.num,IFNULL(w.upload_status,0) as upload_status,IFNULL(w.upload_time,'') as upload_time,w.upload_msg  from wbm_return_record r left join api_bserp_wbm_record as w on r.record_code=w.record_code and w.order_type=2 where r.is_sure=1 and r.is_store_in=1  ";
        $sql_main = " FROM ($record_sql union all $return_sql) as tmp WHERE 1 {$where}";
      //  $sql_values = array();

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
        $sql = "select online_time from erp_config where erp_system!=1 ";
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
    	$record_sql = "select om.sell_record_code,1 as order_type,om.delivery_time,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status  from oms_sell_record om left join api_bserp_trade bs on om.sell_record_code=bs.sell_record_code where om.shipping_status=4 ";
    	$return_sql = "select om.sell_return_code as sell_record_code,2 as order_type,om.receive_time as delivery_time,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status from oms_sell_return om left join api_bserp_trade bs on om.sell_return_code=bs.sell_record_code and  bs.order_type=2 where om.return_shipping_status=1  ";
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
    			$result = load_model('sys/EfastApiModel')->request_api('bserp_api/record_upload_one', $params);
    		} else {
    			//销售退单
    			$result = load_model('sys/EfastApiModel')->request_api('bserp_api/return_upload_one', $params);
    		}
    	}
    	return $this->format_ret('1', '', '上传完成');
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
        $record_sql = "select r.record_code,1 as order_type,r.record_time,r.money,r.store_code,r.num,IFNULL(w.upload_status,0) as upload_status,IFNULL(w.upload_time,'') as upload_time  from wbm_store_out_record r left join api_bserp_wbm_record as w on r.record_code=w.record_code and w.order_type=1 where r.is_sure=1 and r.is_store_out=1 ";
        $return_sql = "select r.record_code,2 as order_type,r.record_time,r.money,r.store_code,r.num,IFNULL(w.upload_status,0) as upload_status,IFNULL(w.upload_time,'') as upload_time  from wbm_return_record r left join api_bserp_wbm_record as w on r.record_code=w.record_code and w.order_type=2 where r.is_sure=1 and r.is_store_in=1  ";
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
                    $result = load_model('sys/EfastApiModel')->request_api('bserp_api/wbm_record_upload_one', $params);
                } else {
                    //批发退货单
                    $result = load_model('sys/EfastApiModel')->request_api('bserp_api/wbm_return_upload_one', $params);
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
    
    function get_erp_do_list($filter) {
        $sql_main = "FROM bsapi_trade WHERE 1 ";
        $sql_values = array();
        $config_store = $this->get_sys_api_store();
        if (empty($config_store)) {
            $sql_main .= " AND 1 = 2 ";
        }
        $config_store_arr = array_column($config_store, 'store_code');
        $config_store_str = deal_array_with_quote($config_store_arr);

        $sql_main .= " AND store_code IN($config_store_str) ";
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] <> '') {
            $sql_main .= " AND record_code = :record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        }
        //单据类型
        if (isset($filter['record_type']) && $filter['record_type'] <> 'all') {
            $sql_main .= " AND record_type = :record_type ";
            $sql_values[':record_type'] = $filter['record_type'];
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $shop_filter = explode(',', $filter['shop_code']);
            $shop_str = $this->arr_to_in_sql_value($shop_filter, 'shop_code', $sql_values);
            $sql_main .= " AND shop_code IN({$shop_str}) ";
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] <> '') {
            $store_filter = explode(',', $filter['store_code']);
            $store_str = $this->arr_to_in_sql_value($store_filter, 'store_code', $sql_values);
            $sql_main .= " AND store_code IN({$store_str}) ";
        }
         //是否分销
        if (isset($filter['is_fenxiao']) && $filter['is_fenxiao'] <> '') {
            $sql_main .= " AND is_fenxiao = :is_fenxiao ";
            $sql_values[':is_fenxiao'] = $filter['is_fenxiao'];
        }
        //业务日期
        if (isset($filter['record_date_start']) && !empty($filter['record_date_start'])) {
            $sql_main .= " AND record_date >= :record_date_start ";
            $sql_values[':record_date_start'] = $filter['record_date_start'];
        }
        if (isset($filter['record_date_end']) && !empty($filter['record_date_end'])) {
            $sql_main .= " AND record_date <= :record_date_end ";
            $sql_values[':record_date_end'] = $filter['record_date_end'];
        }
        $select = '*';
        $sql_main .= " ORDER BY create_time DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$row) {
            $row['record_type_name'] = $this->record_type[$row['record_type']];
            $row['create_time'] = date('Y-m-d H:i:s', $row['create_time']);
            $row['is_fenxiao_order'] = $row['is_fenxiao'] == 1 ? '是' : '否';
        }
        filter_fk_name($data['data'], array('shop_code|shop', 'store_code|store'));
        return $this->format_ret(1, $data);
    }
    
    function get_daily_report_detail($id){
        $sql = "SELECT * FROM bsapi_trade WHERE id=:id";
        $sql_value = array(':id' => $id);
        $daily = $this->db->get_row($sql, $sql_value);
        $daily['record_type_name'] = $this->record_type[$daily['record_type']];
        $daily['create_time'] = date('Y-m-d H:i:s', $daily['create_time']);
        filter_fk_name($daily, array('shop_code|shop', 'store_code|store'));
        return $daily;
    }


    /**
     * @todo 获取bserp对应的仓库
     */
    function get_erp_store_code(){
        $sql = "select outside_code,bs.store_code,bs.store_name from sys_api_shop_store ss left join base_store bs on ss.shop_store_code=bs.store_code where  p_type=0 and outside_type = 1";
        $data = $this->db->get_all($sql);
        foreach ($data as &$value){
            $value['store_name'] = $value['store_name'].'[BSERP2-'.$value['outside_code'].']';
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
     * 获取单据类型供选择
     */
    function get_select_record_type() {
        $record_type = $this->record_type;
        $new_record_type = array();
        foreach ($record_type as $key => $val) {
            $arr['record_type_code'] = $key;
            $arr['record_type_name'] = $val;
            $new_record_type[] = $arr;
        }
        return $new_record_type;
    }
    
    function get_sys_api_shop(){
        $sql = "SELECT
                    bs.shop_code,
                    bs.shop_name
                FROM
                    mid_api_join AS mid
                INNER JOIN base_shop AS bs ON mid.join_sys_code = bs.shop_code
                WHERE
                    mid.join_sys_type = 0
                AND mid.param_val1 = 2";
        $data = $this->db->get_all($sql);
        return $data;
    }
    
    function get_sys_api_store(){
        $sql = "SELECT
                    bs.store_code,
                    bs.store_name
                FROM
                    mid_api_join AS mid
                INNER JOIN base_store AS bs ON mid.join_sys_code = bs.store_code
                WHERE
                    mid.join_sys_type = 1
                AND mid.param_val1 = 2";
        $data = $this->db->get_all($sql);
        return $data;
    }
    
    /**
     * 生成销售日报
     * @param array $data 条件数组
     * @return array 生成结果
     */
    function create_daily_report_action($data) {

        try {
            $field_arr = array('record_date' => '业务日期', 'shop_code' => '店铺', 'store_code' => '仓库', 'record_type' => '日报类型', 'is_fenxiao' => '区分分销');
            $ret_check = $this->check_params($data, $field_arr);
            if ($ret_check['status'] != 1) {
                return $ret_check;
            }
            if($data['record_date'] >= date('Y-m-d')){
                return $this->format_ret(-1, '', '业务日期不能大于当天日期');
            }
            $one_store_code = explode(',', $data['store_code']);
            //获取配置的店铺数据
            $mid_sql = "SELECT mid_code, join_sys_code, join_sys_type FROM mid_api_join WHERE mid_code = (SELECT mid_code FROM mid_api_join WHERE join_sys_code=:store_code AND join_sys_type = 1) AND join_sys_type = 0";
            $config_data = $this->db->get_all($mid_sql, array(":store_code" => $one_store_code[0]));
            $mid_code = $config_data[0]['mid_code'];
            $value = $this->handle_record_date($data, $mid_code);
            if($value['status'] != 1) {
                return $value;
            }
            $sql_values = array(
                ':record_date_start' => $value['data']['record_date_start'],
                ':record_date_end' => $value['data']['record_date_end'],
            );
            $ss_arr = $this->comb_shop_store($data['shop_code'], $data['store_code'], $data['is_fenxiao']);
            $day_report_record = array();
            array_walk($ss_arr, function($val) use(&$data, &$sql_values, &$day_report_record, $mid_code) {
                $sql_values[':shop_code'] = $val['shop_code'];
                $sql_values[':store_code'] = $val['store_code'];
                $data = array_merge($data, $val);
                $day_record = array(
                    'report_day_date' => $data['record_date'],
                    'shop_code' => $val['shop_code'],
                    'store_code' => $val['store_code'],
                    'create_time' => time(),
                );
                if ($data['record_type'] == 'all') {
                    $data['new_record_type'] = 'sell_record';
                    $this->sell_daily($data, $sql_values, $mid_code);
                    $day_record['record_type'] = $data['record_type'];
                    $day_report_record[] = $day_record;

                    $data['new_record_type'] = 'sell_return';
                    $this->sell_daily($data, $sql_values, $mid_code);
                    $day_record['record_type'] = $data['record_type'];
                    $day_report_record[] = $day_record;
                } else {
                    $this->sell_daily($data, $sql_values, $mid_code);
                    $day_record['record_type'] = $data['record_type'];
                    $day_report_record[] = $day_record;
                }
            });

            $msg = $this->create_fail_file($this->_message);
            if (in_array(-1, $this->_status)) {
                throw new Exception('部分日报生成失败' . $msg);
            }
            if (in_array(-2, $this->_status) && !in_array(-1, $this->_status) && !in_array(1, $this->_status)) {
                throw new Exception('无单据记录');
            }
            $this->insert_multi_exp('mid_day_report', $day_report_record, true);
            
            
            return $this->format_ret(1, '', '生成成功');
        } catch (Exception $e) {
            
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }
    
    /**
     * 处理生成日报的业务日期和上线日期
     */
    function handle_record_date($data, $mid_code){
        $config_sql = "SELECT online_time FROM mid_api_config WHERE mid_code=:mid_code";
        $online_time = $this->db->get_value($config_sql, array(":mid_code" => $mid_code));
        //业务日期早于上线日期，如果业务日期是上线日期当天，取上线时间作为开始时间，如果业务日期不是上线日期当天，不允许生成
         //早于上线日期的业务日期且不是上线日期
        if($data['record_date'] < date('Y-m-d', strtotime($online_time))) {
            return $this->format_ret(-1, '', '上线日期为' . date('Y-m-d', strtotime($online_time)). ',</br>不能生成更早时间的日报');
        }
        //与上线时间为同一天,开始时间为上线时间
        if($data['record_date'] == date('Y-m-d', strtotime($online_time))) {
            $record_date_start = $online_time;
            $record_date_end = date('Y-m-d', strtotime($online_time)) . ' 23:59:59';
        }
        //业务日期晚于上线日期
        if($data['record_date'] > date('Y-m-d', strtotime($online_time))) {
            
   
            $record_date = $this->db->get_value("select report_day_date from mid_day_report ORDER BY report_day_date DESC");
            if(empty($record_date)){
                 $record_sql = "SELECT record_date FROM bsapi_trade ORDER BY record_date DESC";
                 $record_date = $this->db->get_value($record_sql);    
            }


            if(!empty($record_date) && $record_date != '0000-00-00') {
                $record_date_plus = date('Y-m-d', strtotime($record_date) + 24 * 3600);
                $record_date_pro_plus = date('Y-m-d', strtotime($record_date) + 48 * 3600);
            } else {
                return $this->format_ret(-1, '', '尚未生成日报，请先生成' .  date('Y-m-d', strtotime($online_time)) . '的日报');
            }

            if(isset($record_date_pro_plus) && $record_date_pro_plus == $data['record_date']) {
                return $this->format_ret(-1, '', '上一次生成日报业务日期为' . $record_date. ',</br>请先生成' . $record_date_plus . '的日报');
            }
            if(isset($record_date_plus) && $record_date_plus < $data['record_date']) {
                return $this->format_ret(-1, '', '上一次生成日报业务日期为' . $record_date. ',</br>请先生成' . $record_date_plus . '至' . $data['record_date'] .'之间未生成的日报');
            }
            $record_date_start = $data['record_date'] . ' 00:00:00';
            $record_date_end = $data['record_date'] . ' 23:59:59';
        }
        return $this->format_ret(1, array('record_date_start' => $record_date_start, 'record_date_end' => $record_date_end));
    }
    
     /**
     * 检查参数是否存在
     * @param array $params 参数
     * @param array $field_arr 要检查的字段 array('字段'=>'字段名')
     * @return array 检查结果
     */
    public function check_params($params, $field_arr = array()) {
        if (empty($params) || empty($field_arr)) {
            return $this->format_ret(-1, '', '内部参数错误');
        }
        $status = '1';
        $msg = '';
        foreach ($field_arr as $k => $v) {
            if (!isset($params[$k]) || (empty($params[$k]) && $params[$k] != 0)) {
                $status = '-1';
                $msg = $v . ' 不能为空';
                break;
            }
        }
        return $this->format_ret($status, array(), $msg);
    }
    
     /**
     * 店铺和仓库交叉生成数组
     * @return array 处理结果数组
     */
    private function comb_shop_store($shop_code_str, $store_code_str, $is_fenxiao) {
        $is_fenxiao_arr = $is_fenxiao == 1 ? array(0,1) : array(0);
        $store_arr = explode(',', $store_code_str);
        $shop_arr = explode(',', $shop_code_str);
        $ss_arr = array();
        foreach ($store_arr as $store) {
            foreach ($shop_arr as $shop) {
                foreach ($is_fenxiao_arr as $v) {
                    $arr['shop_code'] = $shop;
                    $arr['store_code'] = $store;
                    $arr['is_fenxiao_type'] = $v;
                    $ss_arr[] = $arr;
                }
            }
        }
        return $ss_arr;
    }
    
   /**
     * 生成日报主单据信息
     * @param array $data 条件数组
     * @param array $sql_values 查询条件占位符值
     * @return boolean 成功返回ture，失败返回false
     */
    private function sell_daily($data, $sql_values, $mid_code) {
        $record_type = $data['record_type'] = isset($data['new_record_type']) && !empty($data['new_record_type']) ? $data['new_record_type'] : $data['record_type'];
        unset($data['new_record_type']);
        $daily_info = get_array_vars($data, array('record_date', 'shop_code', 'store_code', 'is_fenxiao'));
        $record_type_name = $this->record_type[$record_type];
        $check_exists = $this->is_exists($data);
        if ($check_exists['count'] > 0) {
            $this->_status[] = -1;
            unset($daily_info['is_fenxiao']);
            $this->_message[] = array_merge($daily_info, array('daily_msg' => "{$record_type_name}已存在日报"));
            return FALSE;
        }

        $record = $this->get_daily_record($data['is_fenxiao_type'], $record_type, $sql_values);
        if (empty($record)) {
            $this->_status[] = -1;
            return FALSE;
        }
        if (empty($record['quantity']) && empty($record['amount'])) {
            $this->_status[] = 1;
            return true;
        }



        $data['record_code'] = $this->create_record_code();
        $data['create_time'] = time();
        $record['quantity'] = (int) $record['quantity'];
        $record['amount'] = (float) $record['amount'];
        $record['express_amount'] = (float) $record['express_amount'];
        $record['is_fenxiao'] = $data['is_fenxiao_type'];
        $daily = array_merge($data, $record);
        $this->begin_trans();
        $update_str = 'quantity = VALUES(quantity), amount = VALUES(amount), express_amount = VALUES(express_amount), create_time=VALUES(create_time)';
        $bsapi_ret = $this->insert_multi_duplicate('bsapi_trade', array($daily), $update_str);
        if ($bsapi_ret['status'] != 1) {
            $this->rollback();
            $this->_status[] = -1;
            $this->_message[] = array_merge($daily_info, array('daily_msg' => "{$record_type_name}日报生成失败"));
            return FALSE;
        }
        $ret = $this->sell_daily_detail($data, $sql_values);
        if($ret != TRUE) {
            $this->rollback();
            return FALSE;
        }
        $mid_ret = $this->create_mid_record($daily, $mid_code);
		unset($daily);
        if($mid_ret != TRUE) {
            $this->_status[] = -1;
            unset($daily_info['is_fenxiao']);
            $this->_message[] = array_merge($daily_info, array('daily_msg' => "{$record_type_name}日报生成失败"));
            $this->rollback();
            return FALSE;
        }        
        $this->commit();
        return TRUE;
        
        
    }
    
    /**
     * 根据条件判断记录是否存在
     * @param array $params 查询条件数组
     * @return array 记录集合
     */
    function is_exists($params = array()) {
        $param = get_array_vars($params, array('record_date', 'shop_code', 'store_code', 'record_type', 'is_fenxiao_type'));
        $sql = "SELECT COUNT(1) as count FROM bsapi_trade WHERE record_date=:record_date AND shop_code=:shop_code AND store_code=:store_code AND record_type=:record_type AND is_fenxiao=:is_fenxiao";
        $sql_values = array(":record_date" => $param['record_date'], ":shop_code" => $param['shop_code'], ":store_code" => $param['store_code'], ":record_type" => $param['record_type'], ":is_fenxiao" => $param['is_fenxiao_type'],);
        return $this->db->get_row($sql, $sql_values);
    }
    
    /**
     * 获取日报主单据信息
     * @param string $is_fenxiao 是否区分分销
     * @param string $record_type 单据类型
     * @param array $sql_values 查询条件
     * @return array 数据集
     */
    /**
        备注
        销售订单：
        总商品金额（普通订单）=商品均摊金额之和（payable_money - express_money）；
        总商品金额（分销订单）=商品结算金额之和（fx_payable_money）；
        上传运费：总运费（普通订单）=订单运费（express_money）；
                 总运费（分销订单）=结算运费（fx_express_money）；
        不上传运费：总运费=0

        销售退单：
        总商品金额（普通退单）=商品实退款金额之和（return_avg_money）；
        总商品金额（分销退单）=商品结算金额之和（fx_payable_money）；
        上传运费：总运费（普通退单）= 卖家承担运费+ 赔付金额+ 手工调整金额（seller_express_money+compensate_money+adjust_money）；
                 总运费（分销退单）= 分销运费（fx_express_money）；
        不上传运费：总运费（普通退单）=赔付金额+ 手工调整金额；
                   总运费（分销退单）=0
     */
    private function get_daily_record($is_fenxiao, $record_type, $sql_values) {
        $data = array();
        $sys_param = load_model('sys/SysParamsModel')->get_val_by_code(array('update_express_money_to_new_erp'));
        $update_express_money_to_new_erp = isset($sys_param['update_express_money_to_new_erp']) ? $sys_param['update_express_money_to_new_erp'] : 0;
        switch ($record_type) {
            case 'sell_record':
                if($is_fenxiao == 0) {
                    $express_money_sql = $update_express_money_to_new_erp == 1 ? ' sum(express_money) AS express_amount ' : ' 0 AS express_amount ';
                    $sql = "SELECT sum(goods_num) AS quantity,sum(payable_money-express_money) AS amount,{$express_money_sql}, is_fenxiao FROM oms_sell_record WHERE shipping_status=4 AND is_fenxiao=0 AND delivery_time>=:record_date_start AND delivery_time<=:record_date_end AND shop_code=:shop_code AND store_code=:store_code";
                    $data = $this->db->get_row($sql, $sql_values);
                }
                if($is_fenxiao == 1) {
                    $express_money_sql = $update_express_money_to_new_erp == 1 ? ' sum(fx_express_money) AS express_amount ' : ' 0 AS express_amount ';
                    $sql = "SELECT sum(goods_num) AS quantity,sum(fx_payable_money) AS amount,{$express_money_sql}, is_fenxiao FROM oms_sell_record WHERE shipping_status=4 AND is_fenxiao IN(1,2) AND delivery_time>=:record_date_start AND delivery_time<=:record_date_end AND shop_code=:shop_code AND store_code=:store_code";
                    $data = $this->db->get_row($sql, $sql_values);
                }
                break;
            case 'sell_return':
                
                if($is_fenxiao == 0) {
                    $express_money_sql = $update_express_money_to_new_erp == 1 ? ' sum(seller_express_money+compensate_money+adjust_money) AS express_amount ' : ' sum(compensate_money+adjust_money) AS express_amount ';
                    $sql = "SELECT sum(recv_num) AS quantity,sum(return_avg_money) AS amount, {$express_money_sql} FROM oms_sell_return WHERE return_shipping_status=1 AND is_fenxiao=0 AND receive_time>=:record_date_start AND receive_time<=:record_date_end AND shop_code=:shop_code AND store_code=:store_code";
                    $data = $this->db->get_row($sql, $sql_values);
                }
                if($is_fenxiao == 1){
                    $express_money_sql = $update_express_money_to_new_erp == 1 ? ' sum(fx_express_money) AS express_amount ' : ' 0 AS express_amount ';
                    $sql = "SELECT sum(recv_num) AS quantity,sum(return_avg_money) AS amount, {$express_money_sql} FROM oms_sell_return WHERE return_shipping_status=1 AND is_fenxiao in (1,2) AND receive_time>=:record_date_start AND receive_time<=:record_date_end AND shop_code=:shop_code AND store_code=:store_code";
                    $data = $this->db->get_row($sql, $sql_values);
                }
                break;
            default:
                $data = array();
                break;
        }
        return $data;
    }

    /**
     * 生成销售日报明细
     * @param array $data 条件数组
     * @param array $sql_values 查询条件占位符值
     * @return boolean 成功返回true，失败返回false
     */
    private function sell_daily_detail(&$data, $sql_values) {
        $record_type = $data['record_type'];
        $daily_info = get_array_vars($data, array('record_date', 'shop_code', 'store_code', 'is_fenxiao_type'));
        $record_type_name = $this->record_type[$record_type];
        $fenxiao_sql = $data['is_fenxiao_type'] == 0 ? ' AND is_fenxiao=0 ' : ' AND is_fenxiao IN(1,2) ';
        $fenxiao_sql1 = $data['is_fenxiao_type'] == 0 ? ' sum(rd.avg_money) AS money ' : ' sum(rd.fx_amount) AS money ';
        if ($record_type == 'sell_record') {
            $sql = "SELECT
                        rd.goods_code,
                        rd.sku,
                        sum(rd.num) AS num,
                        {$fenxiao_sql1}
                    FROM
                        oms_sell_record AS sr
                    INNER JOIN oms_sell_record_detail AS rd ON sr.sell_record_code = rd.sell_record_code
                    WHERE
                        sr.shipping_status = 4
                    AND sr.delivery_time >=:record_date_start
                    AND sr.delivery_time <=:record_date_end
                    AND sr.shop_code =:shop_code
                    AND sr.store_code =:store_code {$fenxiao_sql}
                    GROUP BY
                        rd.sku
                    ORDER BY
                        sku";
        } else {
            $sql = "SELECT
                        rd.goods_code,
                        rd.sku,
                        sum(rd.recv_num) AS num,
                        {$fenxiao_sql1}
                    FROM
                        oms_sell_return AS sr
                    INNER JOIN oms_sell_return_detail AS rd ON sr.sell_return_code = rd.sell_return_code
                    WHERE
                        sr.return_shipping_status = 1
                    AND sr.receive_time >=:record_date_start
                    AND sr.receive_time <=:record_date_end
                    AND sr.shop_code =:shop_code
                    AND sr.store_code =:store_code {$fenxiao_sql}
                    GROUP BY
                        rd.sku
                    ORDER BY
                        sku";
        }
        $detail = $this->db->get_all($sql, $sql_values);
        
        if (empty($detail)) {
            $this->_status[] = -2;
            return FALSE;
        }
        $record_code = $data['record_code'];
        $i = 1;
        array_walk($detail, function(&$val) use(&$i, $record_code) {
            $val['record_code'] = $record_code;
            $val['detail_no'] = $i;
            $i++;
        });
        $detail_arr = array_chunk($detail, 2000);
        foreach ($detail_arr as $val) {
            $update_str = "num = VALUES(num), money = VALUES(money)";
            $ret = $this->insert_multi_duplicate('bsapi_trade_detail', $val, $update_str);
            if ($ret['status'] != 1) {
                $this->_status[] = -1;
                unset($daily_info['is_fenxiao_type']);
                $this->_message[] = array_merge($daily_info, array('daily_msg' => "{$record_type_name}日报明细生成失败"));
                return FALSE;
            }
        }
        $this->_status[] = 1;
        return TRUE;
    }
    
    
    /**
     * 生成bserp单据号
     */
    private function create_record_code() {
        $sql = "SELECT id FROM bsapi_trade ORDER BY id DESC";
        $id = $this->db->get_value($sql);
        if (!empty($id)) {
            $id = intval($id) + 1;
        } else {
            $id = 1;
        }
        require_lib('comm_util', true);
        $code = 'LSRB' . date("Ymd") . add_zero($id, 3);
        return $code;
    }

    /**
     * 创建写入mid库的数据，用于上传
     */
    private function create_mid_record($daily, $mid_code) {
        $param = array();
        $param['record_code'] = $daily['record_code'];
        $param['record_type'] = $daily['record_type'] . '_rb';
        $param['order_status'] = '4';
        $param['efast_store_code'] = $daily['store_code'];
        $param['api_product'] = 'bserp2';
        $param['mid_code'] = $mid_code;
        $param['create_time'] = date('Y-m-d H:i:s');
        $mid_ret = $this->insert_exp('mid_order', $param);
        if ($mid_ret['status'] != 1) {
            return FALSE;
        }
        return TRUE;
    }
    
        /**
     * 创建导出信息csv文件
     * @param array $msg 信息数组
     * @return string 文件地址
     */
    private function create_fail_file($msg) {
        $fail_top = array('业务日期', '店铺', '仓库', '日报生成信息');
        require_lib('csv_util');
        $csv_obj = new execl_csv();
        $file_name = $csv_obj->create_fail_csv_files($fail_top, $msg, 'create_bserp_daily');
//        $message = "，日报生成信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "，日报生成信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }
}