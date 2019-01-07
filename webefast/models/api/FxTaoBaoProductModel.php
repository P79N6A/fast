<?php
require_model('tb/TbModel');
class FxTaoBaoProductModel extends TbModel{
    protected $table = "api_taobao_fx_product";

    
    function get_by_page($filter){
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
    	$sql_join = "";
    	//$sql_main = "FROM {$this->table} rl  WHERE 1";
    	$sql_main = "FROM {$this->table} rl LEFT JOIN api_taobao_fx_product_sku r2 on rl.pid = r2.pid WHERE 1";
    	$sql_values = array();
    	

    	//商品名称
    	if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
    	
    		$sql_main .= " AND rl.name LIKE :goods_name ";
    		$sql_values[':goods_name'] = '%'.$filter['goods_name'].'%';
    	}
    	//商品条形码
    	if (isset($filter['goods_barcode']) && $filter['goods_barcode'] != '') {
    		 
    		$sql_main .= " AND r2.outer_id LIKE :goods_barcode ";
    		$sql_values[':goods_barcode'] = $filter['goods_barcode'].'%';
    	}
            	//商品编码
    	if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
    		$goods_code_arr = explode(',',$filter['goods_code']);
    		//$goods_code_wh = array();
               $sql_str = $this->arr_to_like_sql_value($goods_code_arr, 'outer_id', $sql_values, 'rl.');
//    		foreach($goods_code_arr as $each_goods_code){
//	    		$goods_code_wh[] = " rl.outer_id like '%{$each_goods_code}%'";
//    		}
    		$sql_main .= " AND  {$sql_str} ";
    	}
        
    	//商品状态
    	if (isset($filter['status']) && $filter['status'] <> '') {
    		$arr = explode(',',$filter['status']);
    		$str = $this->arr_to_in_sql_value($arr, 'status', $sql_values);
    		$sql_main .= " AND rl.status in ({$str}) ";
    		
    	}
    	
    	//是否库存同步
    	if (isset($filter['is_snyc']) && $filter['is_snyc'] <> '') {
    		$arr = explode(',',$filter['is_snyc']);
    		$str = $this->arr_to_in_sql_value($arr, 'is_allow_sync_inv', $sql_values);
    		$sql_main .= " AND r2.is_allow_sync_inv  in ({$str}) ";
    		//$sql_values[':is_sync_inv'] = $str;
    	}
    	//销售平台
//    	if (isset($filter['source']) && $filter['source'] <> '') {
//    		
//    		$arr = explode(',',$filter['source']);
//    		$str = "'".join("','",$arr)."'";
//    		$sql_main .= " AND rl.source  in ({$str}) ";
//    		//$sql_values[':sale_channel_code'] = $str;
//    	}
    	
    	//店铺
    	$filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
    	$sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code',$filter_shop_code,'get_purview_tbfx_shop');
//    	if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
//    		$arr = explode(',',$filter['shop_code']);
//    		$str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
//    		$sql_main .= " AND rl.shop_code in ({$str}) ";
//    	}
        //产品ID
        if (isset($filter['pid']) && $filter['pid'] <> '') {
            $sql_main .= " AND rl.pid LIKE :pid ";
            $sql_values[':pid'] = '%' . $filter['pid'] . '%';
        }
        //平台SKUID
        if (isset($filter['sku_id']) && $filter['sku_id'] <> '') {
            $sql_main .= " AND r2.id LIKE :sku_id ";
            $sql_values[':sku_id'] = '%' . $filter['sku_id'] . '%';
        }
        //导出
        if ($filter['ctl_type'] == 'export') {
            return $this->api_fx_goods_export_csv($sql_main, $sql_values, $filter);
        }

        $select = 'rl.*';
    	$sql_main .= " group by rl.api_taobao_fx_product_id ";
    	//echo $sql_main;
    	//print_r($sql_values);

    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
    	//$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
    	filter_fk_name($data['data'], array('shop_code|shop', ));
    	
    	$ret_status = OP_SUCCESS;
    	$ret_data = $data;
    	
    	return $this->format_ret($ret_status, $ret_data);
    }
    
    //导出
    function api_fx_goods_export_csv($sql_main, $sql_values, $filter) {
        $sql = "SELECT rl.shop_code,rl.pid,rl.outer_id,rl.name AS goods_name,rl.cost_price,r2.id,r2.outer_id AS detail_outer_id,rl.quantity,r2.dealer_cost_price,r2.inv_num,r2.inv_update_time,r2.is_allow_sync_inv,r2.quantity AS sku_quantity ";
        $sql .= $sql_main;
        $data = $this->db->get_all($sql, $sql_values);
        filter_fk_name($data, array('shop_code|shop',));
        foreach ($data as &$row) {
            $row['is_allow_sync_inv_txt'] = $row['is_allow_sync_inv'] == '1' ? '是' : '否';
            $row['inv_num'] = ($row['inv_num'] == -1) ? '' : $row['inv_num'];
        }
        $ret['data'] = $data;
        return $this->format_ret(1, $ret);
    }
    
      function get_by_page_sku($filter){
    	$sql_join = "";
    	$sql_main = "FROM {$this->table} rl LEFT JOIN api_taobao_fx_product_sku r2 on rl.pid = r2.pid WHERE 1";
    	$sql_values = array();
    	

      //商品名称
    	if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
    	
    		$sql_main .= " AND rl.name LIKE :goods_name ";
    		$sql_values[':goods_name'] = '%'.$filter['goods_name'].'%';
    	}
    	//商品条形码
    	if (isset($filter['goods_barcode']) && $filter['goods_barcode'] != '') {
    		 
    		$sql_main .= " AND r2.outer_id LIKE :goods_barcode ";
    		$sql_values[':goods_barcode'] = $filter['goods_barcode'].'%';
    	}
            	//商品编码
    	if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
    		$goods_code_arr = explode(',',$filter['goods_code']);
    		$goods_code_wh = array();
    		foreach($goods_code_arr as $each_goods_code){
	    		$goods_code_wh[] = " rl.outer_id like :goods_code";
                        $sql_values[':goods_code'] = '%'.$each_goods_code.'%';
    		}
    		$sql_main .= " AND (".join(' or ',$goods_code_wh).")";
    	}
        
    	//商品状态
    	if (isset($filter['status']) && $filter['status'] <> '') {
    		$arr = explode(',',$filter['status']);
    		$str = $this->arr_to_in_sql_value($arr, 'status', $sql_values);
    		$sql_main .= " AND rl.status in ({$str}) ";
    		
    	}
    	
    	//是否库存同步
    	if (isset($filter['is_snyc']) && $filter['is_snyc'] <> '') {
    		$arr = explode(',',$filter['is_snyc']);
    		$str = $this->arr_to_in_sql_value($arr, 'is_allow_sync_inv', $sql_values);
    		$sql_main .= " AND r2.is_allow_sync_inv  in ({$str}) ";
    		//$sql_values[':is_sync_inv'] = $str;
    	}
    	
    	//店铺
    	if (isset($filter['shop_code']) && !empty($filter['shop_code'])) {
    		$sql_main .= " AND rl.shop_code in (:shop_code) ";
    		$sql_values[':shop_code'] = $filter['shop_code'];
    	}
    	$select = 'r2.*';
    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
    	filter_fk_name($data['data'], array('shop_code|shop', ));
    	$ret_status = OP_SUCCESS;
    	$ret_data = $data;
    	
    	return $this->format_ret($ret_status, $ret_data);
    }
        
    function get_sku_info_by_id($id){
        $sql = "select * from api_taobao_fx_product_sku where api_taobao_fx_product_sku_id=:id";
        $row = $this->db->get_row($sql,array(':id'=>$id));
        $row['sku_id'] = $row['pid']."|".$row['properties'];
        return $row;
    }

    
    
    function get_product_sku_list_by_pid($pid, $filter = array()) {
		$data = $this->db->get_all("select * FROM api_taobao_fx_product_sku WHERE pid='{$pid}'");
        foreach ($data as &$value) {
            $value['status'] = ($value['sku_status'] == 1) ? 0 : 1;
            $value['inv_num'] = ($value['inv_num'] == -1) ? '' : $value['inv_num'];
        }
		return $data;
	}
	
	function update_inv($shop_code = '', $barcode_inv=array(),$is_increment=1) {
		$where = "1";
		if(!empty($shop_code)){
			$where .= " AND api_taobao_fx_product_sku.shop_code ='{$shop_code}' ";
		}
		foreach($barcode_inv as $inv){
			if(!empty($inv)){
				$sql = "update api_taobao_fx_product_sku set api_taobao_fx_product_sku.inv_num = {$inv['num']},api_taobao_fx_product_sku.inv_update_time=now(),api_taobao_fx_product_sku.sys_update_time='{$inv['inv_update_time']}'
				where {$where} AND api_taobao_fx_product_sku.outer_id='{$inv['barcode']}' ";
				if($is_increment==1){
					$sql .="  AND api_taobao_fx_product_sku.sys_update_time<'{$inv['inv_update_time']}'";
				}
				$this->db->query($sql);
			}
		}
		return true;
	
	}
	//批量设置允许库存同步
	function p_update_active($active, $pid) {
		$pid_str= "'".implode("','", $pid)."'";
		$sql = " update  api_taobao_fx_product_sku set is_allow_sync_inv = '$active'  where pid in ($pid_str)";
		$ret = $this->db->query($sql);
		
		if($ret === true){
			return $this->format_ret(1,'','批量设置成功');
		}else{
			return $this->format_ret(-1,'','批量设置失败');
		}
	
	}
	
	//单个设置允许库存同步
	function update_active($active, $id) {
		$sql = " update  api_taobao_fx_product_sku set is_allow_sync_inv = '$active'  where id ='$id'";
		$ret = $this->db->query($sql);
	
		if($ret === true){
			return $this->format_ret(1,'','批量设置成功');
		}else{
			return $this->format_ret(-1,'','批量设置失败');
		}
	
	}
        /**
     * @下载商品
     */
    function down_goods($request) {
        $params=array();
        $params['shop_code']=$request['shop_code'];
        $params['start_time']=$request['start_time'];
        $params['end_time']=$request['end_time'];
        $params['method']='item_sync';
        $result = load_model('sys/EfastApiTaskModel')->request_api('sync', $params);

        return $result;

    }
	/**
	 * @todo 删除分销商品
	 */
	function do_delete($pid) {
		$sql = "SELECT * FROM {$this->table} where pid = :pid";
		$data = $this->db->get_row($sql, array(":pid" => $pid));
		$goods_from_id = $data['goods_from_id'];

		$this->begin_trans();
		$ret_main = parent::delete(array('pid' => $pid));
		if ($ret_main['status'] != 1) {
			$this->rollback();
			return $ret_main;
		}
		$ret_detail = $this->delete_exp('api_taobao_fx_product_sku', array('pid' => "{$pid}"));
		if ($ret_detail == false) {
			$this->rollback();
			return $this->format_ret(-1, '', '删除失败');
		}
		$this->commit();
		//添加操作日志
		$log_xq = '删除分销商品，分销商品ID：' . $pid ;
		$this->add_operate_log('淘宝分销商品', $pid, '删除', $log_xq);
		return $this->format_ret(1, '', '删除成功');
	}
	/*
* 添加系统操作日志
* */
	function add_operate_log($module, $yw_code, $operate_type, $operate_xq) {
		$log = array(
				'user_id' => CTX()->get_session('user_id'),
				'user_code' => CTX()->get_session('user_code'),
				'ip' => '', 'add_time' => date('Y-m-d H:i:s'),
				'module' => $module,
				'yw_code' => $yw_code,
				'operate_type' => $operate_type,
				'operate_xq' => $operate_xq
		);
		$ret = load_model('sys/OperateLogModel')->insert($log);
		return $ret;
	}


	//下载进度
    function down_goods_check($request){
        $params=array();
        $params['task_sn']=$request['task_sn'];
        $result = load_model('sys/EfastApiTaskModel')->request_api('check', $params);

        return $result;

    }
}