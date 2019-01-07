<?php
/**
 * 仓库库位相关业务
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('store');

class ShelfModel extends TbModel {
	function __construct() {
		parent::__construct('base_shelf', 'shelf_id');
	}
	
    private function str_chunk($s){
        $arr = explode(',', $s);
        foreach($arr as &$code){
            $code = "'".$this->db->escape($code)."'";
        }
        return implode(",", $arr);
    }
	/*
	 * 根据条件查询数据(库位管理)
	 */
	function get_by_page($filter) {
		$sql_join = "";
		$sql_main = "FROM base_store rl  LEFT JOIN {$this->table} r2 on rl.store_code = r2.store_code   WHERE rl.status=1";
		$sql_values = array();
		//仓库名称或代码
		if (isset($filter['code_name']) && $filter['code_name'] != '') {
			//$sql_main .= " AND (rl.store_code LIKE '%" . $filter['code_name'] . "%' or rl.store_name LIKE '%" . $filter['code_name'] . "%' )";
			$sql_main .= " AND (rl.store_code LIKE :code_name or rl.store_name LIKE :code_name)";
			$sql_values[':code_name'] = $filter['code_name'].'%';
		}

		if(isset($filter['__sort']) && $filter['__sort'] != '' ){
			$filter['__sort_order'] = $filter['__sort_order'] =='' ? 'asc':$filter['__sort_order'];
			$sql_main .= ' order by '.trim($filter['__sort']).' '.$filter['__sort_order'];
		}
		$select = 'rl.store_id,rl.store_code,rl.store_name,count(r2.shelf_id) as num';
		$sql_main .= " GROUP BY rl.store_id order by rl.store_id";
		//echo $sql_main;exit;
		//$data =  $this->get_page_from_sql($filter, $sql_main, $select);
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
                $is_del = load_model('sys/PrivilegeModel')->check_priv('base/shelf/do_delete_store');
                foreach($data['data'] as &$value){
                    $value['is_del'] = $is_del == '' ? 0 : 1;
                }
                $ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);
	}
	/*
	 * 根据条件查询数据(库位管理)
	*/
	function get_by_page_detail($filter) {
		
		$sql_join = "";
		$sql_main = "FROM {$this->table} rl LEFT JOIN base_store r2 on rl.store_code = r2.store_code  WHERE 1";
		$sql_values = array();
                $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
                $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
                
                
		//库位名称或代码
		if (isset($filter['code_name']) && $filter['code_name'] != '') {
			$sql_main .= " AND (rl.shelf_code LIKE :code_name or rl.shelf_name LIKE :code_name)";
			$sql_values[':code_name'] = '%' . $filter['code_name'].'%';
		}
//		//仓库代码
//		if (isset($filter['store_code']) && $filter['store_code'] != '') {
//			$sql_main .= " AND (rl.store_code = :store_code)";
//			$sql_values[':store_code'] = $filter['store_code'];
//		}
		
	    //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {

             $arr = explode(',', $filter['store_code']);
             $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code IN ($str)";
            
        }
        $sql_main.=' ORDER BY rl.shelf_code,rl.shelf_id';
        
		$select = 'rl.*,r2.store_name';

		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);

		$ret_status = OP_SUCCESS;
		$ret_data = $data;
	
		return $this->format_ret($ret_status, $ret_data);
	}
	//根据查询条件查询规格
    function get_goods_spec1($filter){
        $sql_main="FROM base_spec1 WHERE 1 ";
        if(isset($filter['spec1_name'])&&$filter['spec1_name']!=''){
            $sql_main.="AND (spec1_name LIKE :spec1_name OR spec1_code LIKE :spec1_code)";
            $sql_values[':spec1_name'] ='%'.$filter['spec1_name'] . '%';
            $sql_values[':spec1_code'] ='%'.$filter['spec1_name'] . '%';
        }
        $sql_main.="order by spec1_code asc";
        $select="spec1_code,spec1_name";
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status, $data);
    }
    //根据查询条件查询规格
    function get_goods_spec2($filter){
        $sql_main="FROM base_spec2 WHERE 1 ";
        if(isset($filter['spec2_name'])&&$filter['spec2_name']!=''){
            $sql_main.="AND (spec2_name LIKE :spec2_name OR spec2_code LIKE :spec2_code)";
            $sql_values[':spec2_name'] ='%'.$filter['spec2_name'] . '%';
            $sql_values[':spec2_code'] ='%'.$filter['spec2_name'] . '%';
        }
        $sql_main.="order by spec2_code asc";
        $select="spec2_code,spec2_name";
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status, $data);
    }
	/*
	 * 根据条件查询数据(库位商品管理)
	*/
	function get_by_page_goods($filter) {
		$sql_join = "";
		$sql_main = "FROM goods_shelf r1 
		           LEFT JOIN {$this->table} r2 on r1.shelf_code = r2.shelf_code and r1.store_code = r2.store_code 
		           LEFT JOIN base_goods r3 on r3.goods_code = r1.goods_code 
		           LEFT JOIN base_store r4 on r4.store_code = r1.store_code 
				   LEFT JOIN goods_sku r7 on r7.sku = r1.sku
		         WHERE 1";
		$sql_values = array();
		//库位代码
		if (isset($filter['shelf_code']) && $filter['shelf_code'] != '') {
			$shelf_code_arr = explode(',',$filter['shelf_code']);
			if(!empty($shelf_code_arr)){
				$sql_main .= " AND (";
				foreach($shelf_code_arr as $key=> $value){
					$param_shelf = 'param_shelf'.$key;
					if($key == 0){
						$sql_main .= " r1.shelf_code = :{$param_shelf} ";
					}else{
						$sql_main .= " or r1.shelf_code = :{$param_shelf} ";
					}
						
					$sql_values[':'.$param_shelf] = $value;
				}
				$sql_main .= ")";
			}
				
		}
                if (isset($filter['store_code']) && $filter['store_code'] != '') {
			$sql_main .= " and r1.store_code = :store_code ";
                        $sql_values[':store_code'] = $filter['store_code'];
		}
		$sql_main .=" group by r1.goods_shelf_id ";
		$select = 'r1.*,r2.shelf_name ,r3.goods_name ,r4.store_name,r7.spec1_name,r7.spec2_name,r7.barcode ';
		//echo $select.$sql_main;
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,true);
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
	
		return $this->format_ret($ret_status, $ret_data);
	}
	//批量插入
	function insert_list($data){
		/*
		foreach($data as $k=>$v){
			$num = $k+2;
				
			//不能为空项
			if (empty($v['shelf_code']) || empty($v['store_code'])  || empty($v['status'])) {
				$errMsg = "第{$num}行：必填项（货架名称,仓库代码,有效）不能为空，请重新填写";
				//continue;
				return return_value("-1", $errMsg);
			}
				
		}*/
		$ret = parent :: insert_multi($data);
		return $ret;
	}
	function get_by_id($shelf_id) {
	
		return  $this->get_row(array('shelf_id'=>$shelf_id));
	}
	
	/**
	 * 新增
	 * @param $skuCode
	 * @param $storeCode
	 * @param $shelfCode
	 * @param string $batchNumber
	 * @return array
	 */
	function add($shelfName, $storeCode, $shelfCode){
		
		if(empty($shelfName)){
			return array('status'=>-1, 'message'=>'库位名称为空:');
		}
		
		$sql = "select * from base_store where store_code = :code";
		$store = $this->db->get_row($sql, array('code'=>$storeCode));
		if(empty($store)){
			return array('status'=>-1, 'message'=>'仓库不存在:');
		}
	
		$sql = "select * from base_shelf where store_code = :store_code and shelf_code = :shelf_code";
		$shelf = $this->db->get_row($sql, array('store_code'=>$storeCode,'shelf_code'=>$shelfCode));
		if(!empty($shelf)){
			return array('status'=>-1, 'message'=>'该仓库此库位已存在:');
		}
		
		$d = array(
				'shelf_name' => $shelfName,
				'store_code' => $storeCode,
				'shelf_code' => $shelfCode,
		);
	
		return $this->insert($d);
	}
	/*
	 * 添加新纪录
	*/
	function insert($data) {
		$status = $this->valid($data);
		if ($status < 1) {
			return $this->format_ret($status);
		}
	
		$ret = $this->is_exists($data['shelf_code'],$data['store_code']);
	
		if (!empty($ret['data'])) return $this->format_ret(SHELF_ERROR_UNIQUE_CODE);
		 
		return parent::insert($data);
	}
	/**
	 * 修改纪录
	 */
	function update($data, $id) {
//		$status = $this->valid($data, true);
//		if ($status < 1) {
//			return $this->format_ret($status);
//		}
		/*
		$ret = $this->get_row(array('shelf_id' => $id));
		if (isset($data['shelf_name']) && isset($ret['data']['shelf_name']) && ($data['shelf_name'] != $ret['data']['shelf_name'])) {
			$ret = $this->is_exists($data['shelf_name'], 'shelf_name');
			if ($ret['status'] > 0 && !empty($ret['data'])) return $this->format_ret('SHELF_ERROR_UNIQUE_NAME');
		}*/
		$ret = parent :: update($data, array('shelf_id' => $id));
		return $ret;
	}
	/**
	 * 服务器端验证
	*/
	private function valid($data, $is_edit = false) {
		if (!$is_edit && (!isset($data['shelf_code']) || !valid_input($data['shelf_code'], 'required'))) return SHELF_ERROR_CODE;
		if (!isset($data['store_code']) || !valid_input($data['store_code'], 'required')) return STORE_ERROR_CODE;
	
		return 1;
	}
	/**
	 * 是否存在
	 */
	function is_exists($shelf_code, $store_code) {
		$ret = parent::get_row(array('shelf_code'=>$shelf_code,'store_code'=>$store_code));
	
		return $ret;
	}
	
	/*
	 * 删除记录
	* */
	function delete($shelf_id) {
		$used = $this->is_used_by_id($shelf_id);
		if($used){
			return $this->format_ret(-1,array(),'该库位已经关联到商品，不能删除！');
		}
		$ret = parent::delete(array('shelf_id'=>$shelf_id));
		return $ret;
	}
	
	/**
	 * 根据id判断在业务系统是否使用
	 * @param int $id
	 * @return boolean 已使用返回true, 未使用返回false
	 */
	public function is_used_by_id($id) {
		$result = $this->db->get_all('select shelf_code,store_code from base_shelf where shelf_id=:id', array(':id' => $id));
                $code = $result[0]['shelf_code'];
                $store = $result[0]['store_code'];
		$num = $this->get_num('select * from goods_shelf where shelf_code=:code and store_code=:store', array(':code' => $code,':store'=>$store));
		if(isset($num['data'])&&$num['data']>0){
			//已经在业务系统使用
			return true;
		}else{
			//尚未在业务系统使用
			return false;
		}
	}

	/**
	 *
	 * 方法名                               api_shelf_update
	 *
	 * 功能描述                           更新库位数据
	 *
	 * @author      BaiSon PHP R&D
	 * @date        2015-06-19
	 * @param       array $param
	 *              array(
	 *                  必选: 'store_code', 'shelf_code', 'shelf_name',
	 *                 )
	 * @return      json [string status, obj data, string message]
	 *              {"status":"1","message":"保存成功"," data":"10146"}
	 */
	public function api_shelf_update($param) {
	    //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
	    $key_required = array(
	            's' => array('store_code', 'shelf_code', 'shelf_name'),
	    );
	    $arr_required = array();
	    //验证必选字段是否为空并提取必选字段数据
	    $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
	    
	    //必填项检测通过
	    if (TRUE == $ret_required['status']) {
	        //真实数据
	        $arr_deal = $arr_required;
	        
	        //清空无用数据
	        unset($arr_required);
	        
	        //检测仓库是否存在并获取仓库数据
	        $filter = array('store_code' => $arr_deal['store_code']);
	        $ret = load_model('base/StoreModel')->check_exists_by_condition($filter);
	        
	        if (1 != (int) $ret['status']) {
	            return $this -> format_ret("-10002", $filter, "API_RETURN_MESSAGE_10002");
	        }
	        $store = $ret['data'];
	        
	        //清空无用数据
	        unset($filter);
	        unset($ret);
	        
	        //检测库位是否存在
	        $filter = array('store_code' => $arr_deal['store_code'], 'shelf_code' => $arr_deal['shelf_code']);
	        $ret = $this->check_exists_by_condition($filter);
	        
	        $filter = array('store_code' => $arr_deal['store_code'], 'shelf_code' => $arr_deal['shelf_code'], 'shelf_name' => $arr_deal['shelf_name']);
	        $key = array_keys($filter);
	        if (1 == (int) $ret['status']) {
	            $filter['shelf_id'] = $ret['data']['shelf_id'];
	            $key[] = 'shelf_id';
	        }
	        
	        //插入或更新数据	        
	        return $ret = $this->save_or_update($this->table, $filter, $key);
	    } else {
	        return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
	    }
	    return $arr_required;
	}
        function delete_store($store){
            $sql = "select count(1) from base_shelf r1 inner join goods_shelf r2 on r1.shelf_code=r2.shelf_code where r2.store_code=:store_code";
            $a = $this->db->get_value($sql, array(':store_code'=>$store));
            if($a != 0){
                return $this->format_ret(-1,array(),'该仓库尚有已经关联的商品，不能删除！');
            }
            $ret = parent::delete(array("store_code"=>$store));
            return $this->format_ret(1,"",'删除成功');
        }
}