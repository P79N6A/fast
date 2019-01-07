<?php
/**
 * 角色业务权限相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('sys');

class RoleProfessionModel extends TbModel {

	function __construct(){
		parent::__construct('sys_role_profession', 'sys_role_profession_id');
	}

        

	/**
	 * 获取角色的数据权限
	 */
    function get_user_data_auth($role_code){
        $sql = "select * from sys_role_profession";
        M('sys_role_profession')->get_all();
    }

	/**
	 * 获取可选店铺
	 */
	function get_shop_list_noset($filter){
		//print_r($filter);exit;
		$role_code = isset($filter['role_code'])?$filter['role_code']:'';
		if (empty($role_code)) {
			return $this->format_ret(OP_ERROR);
		}
		$wh = '';
		if (!empty($filter['keyword'])){
			//$wh = "(role_code like '%{$filter['keyword']}%' or role_name like '%{$filter['keyword']}%') and ";
			$wh = "  shop_name LIKE :keyword and ";
			$sql_values[':keyword'] = $filter['keyword'].'%';
		}
		$select = 'shop_id,shop_code as relate_code,shop_name';
		$sql_main = "from base_shop where {$wh} shop_code not in(select relate_code from {$this->table} where role_code = :role_code and profession_type = '1')";
		$sql_values[':role_code'] = $role_code;
		//print_r($sql_values);
		//echo $sql_main;die;
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		//print_r($ret_data);
		return $this->format_ret($ret_status, $ret_data);
	}
	function get_store_list_noset($filter){
		//print_r($filter);exit;
		$role_code = isset($filter['role_code'])?$filter['role_code']:'';
		if (empty($role_code)) {
			return $this->format_ret(OP_ERROR);
		}
		$wh = '';
		$sql_values = array();
		if (!empty($filter['keyword'])){
			//$wh = "(role_code like '%{$filter['keyword']}%' or role_name like '%{$filter['keyword']}%') and ";
			$wh = "  store_name LIKE :keyword and ";
			$sql_values[':keyword'] = $filter['keyword'].'%';
		}
		$select = 'store_id,store_code as relate_code,store_name';
		$sql_main = "from base_store where {$wh} store_code not in(select relate_code from {$this->table} where role_code = :role_code and profession_type = '2')";
		$sql_values[':role_code'] = $role_code;
		//echo $sql_main;die;
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}

	function get_brand_list_noset($filter){
		//print_r($filter);exit;
		$role_code = isset($filter['role_code'])?$filter['role_code']:'';
		if (empty($role_code)) {
			return $this->format_ret(OP_ERROR);
		}
		$wh = '';
		if (!empty($filter['keyword'])){
			//$wh = "(role_code like '%{$filter['keyword']}%' or role_name like '%{$filter['keyword']}%') and ";
			$wh = "  brand_name LIKE :keyword and ";
			$sql_values[':keyword'] = $filter['keyword'].'%';
		}
		$select = 'brand_id,brand_code as relate_code,brand_name';
		$sql_main = "from base_brand where {$wh} brand_code not in(select relate_code from {$this->table} where role_code = :role_code and profession_type = '3')";
		$sql_values[':role_code'] = $role_code;
		//echo $sql_main;die;
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}
    /*
     * 获取可选供应商列表
     */
    function get_supplier_list_noset($filter){
        $role_code = isset($filter['role_code'])?$filter['role_code']:'';
        if (empty($role_code)) {
                return $this->format_ret(OP_ERROR);
        }
        $wh = '';
        if (!empty($filter['keyword'])) {
            $wh = "  supplier_name LIKE :keyword and ";
            $sql_values[':keyword'] = $filter['keyword'] . '%';
        }
        $select = 'supplier_id,supplier_code as relate_code,supplier_name';
        $sql_main = "from base_supplier where {$wh} supplier_code not in(select relate_code from {$this->table} where role_code = :role_code and profession_type = '4')";
        $sql_values[':role_code'] = $role_code;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
	/**
	 * 已选店铺
	 */
	function get_shop_list($filter) {
		$role_code = isset($filter['role_code'])?$filter['role_code']:'';
		if (empty($role_code)) {
			return $this->format_ret(OP_ERROR);
		}
		$select = 'sur.*,sr.shop_name';
		$sql_main = "FROM {$this->table} sur,base_shop sr where sur.relate_code = sr.shop_code and sur.role_code= :role_code  and profession_type = '1' ";

		$sql_values[':role_code'] = $role_code;
		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		//filter_fk_name($ret_data['data'], array('role_id'));

		return $this->format_ret($ret_status, $ret_data);
	}

	function get_store_list($filter) {
		$role_code = isset($filter['role_code'])?$filter['role_code']:'';
		if (empty($role_code)) {
			return $this->format_ret(OP_ERROR);
		}
		$select = 'sur.*,sr.store_name';
		$sql_main = "FROM {$this->table} sur,base_store sr where sur.relate_code = sr.store_code and sur.role_code= :role_code  and profession_type = '2' ";

		$sql_values[':role_code'] = $role_code;
		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}

	function get_brand_list($filter) {
		$role_code = isset($filter['role_code'])?$filter['role_code']:'';
		if (empty($role_code)) {
			return $this->format_ret(OP_ERROR);
		}
		$select = 'sur.*,sr.brand_name';
		$sql_main = "FROM {$this->table} sur,base_brand sr where sur.relate_code = sr.brand_code and sur.role_code= :role_code  and profession_type = '3' ";

		$sql_values[':role_code'] = $role_code;
		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}
        
    /*
     * 获取已选供应商列表
     */
    function get_supplier_list($filter){
        $role_code = isset($filter['role_code'])?$filter['role_code']:'';
        if (empty($role_code)) {
                return $this->format_ret(OP_ERROR);
        }
        $select = 'sur.*,sr.supplier_name';
        $sql_main = "FROM {$this->table} sur,base_supplier sr where sur.relate_code = sr.supplier_code and sur.role_code= :role_code  and profession_type = '4' ";
        $sql_values[':role_code'] = $role_code;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
	/**
	 * 加入业务角色
	 */
	function role_add($role_code,$profession_type,$role_id_list){
		if (empty($role_id_list)){
			return;
		}
		$role_id_arr = explode(',', $role_id_list);
		$ins_arr = array();
		foreach($role_id_arr as $relate_code){
			$ins_arr[] = "('{$role_code}','{$relate_code}','{$profession_type}')";
		}
		$sql = "insert ignore {$this->table}(role_code,relate_code,profession_type) values".join(',',$ins_arr);
		
		$ret = CTX()->db->query($sql);
		return $ret;
	}
	/**
	 * 删除业务角色
	 */
	function role_remove($role_code,$profession_type,$role_id_list){
		if (empty($role_id_list)){
			return;
		}
		$role_id_arr = explode(',', $role_id_list);
		$ins_arr = array();
		foreach($role_id_arr as $relate_code){
			$ins_arr[] = "'{$relate_code}'";
		}
		//$sql = "delete from {$this->table} where role_code = '{$role_code}' and profession_type = '{$profession_type}' and role_id in($role_id_list)";
		$sql = "delete from {$this->table} where role_code = '{$role_code}' and profession_type = '{$profession_type}' and relate_code in(".join(',',$ins_arr).")";
		$ret = CTX()->db->query($sql);
		return $ret;
	}
        
        
        
        function get_user_profession( $user_id,$type ){
     
            
            $sql = "select p.relate_code from sys_role r
                    INNER JOIN   sys_user_role  ur  ON ur.role_id=r.role_id
                    INNER JOIN sys_role_profession p  ON p.role_code= r.role_code
                    where p.profession_type =:type AND ur.user_id=:user_id ";
            $sql_values = array(':type'=>$type,':user_id'=>$user_id);
            $data = $this->db->get_all($sql,$sql_values);
            $arr = array();
            foreach($data as $val){
                $arr[] = $val['relate_code'];
            }
            return $arr;
        }
        /*
     * 获取可选分销商列表
     */
    function get_custom_list_noset($filter){
        $role_code = isset($filter['role_code'])?$filter['role_code']:'';
        if (empty($role_code)) {
                return $this->format_ret(OP_ERROR);
        }
        $wh = '';
        if (!empty($filter['keyword'])) {
            $wh = " custom_name LIKE :keyword AND ";
            $sql_values[':keyword'] = "%" . $filter['keyword'] . '%';
        }
        $select = 'custom_id,custom_code as relate_code,custom_name';
        $sql_main = "from base_custom where {$wh} custom_code NOT IN(select relate_code from {$this->table} where role_code = :role_code and profession_type = '7') AND custom_type = 'pt_fx' AND is_effective = 1";
        $sql_values[':role_code'] = $role_code;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    /*
     * 获取已选分销商列表
     */
    function get_custom_list($filter){
        $role_code = isset($filter['role_code'])?$filter['role_code']:'';
        if (empty($role_code)) {
                return $this->format_ret(OP_ERROR);
        }
        $select = 'sur.*,sr.custom_name';
        $sql_main = "FROM {$this->table} sur,base_custom sr where sur.relate_code = sr.custom_code and sur.role_code= :role_code  and profession_type = '7' ";
        $sql_values[':role_code'] = $role_code;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

}
