<?php
/**
 * 商品规格2相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('prm');

class GoodsSpec2Model extends TbModel {
	function get_table() {
		return 'goods_spec2';
	}



	function get_by_id($id) {

		return  $this->get_row(array('goods_spec2_id'=>$id));
	}
	function get_by_code($code) {
//		$ret = $this->get_all(array('goods_code' => $code));
//		filter_fk_name($ret['data'], array( 'spec1_code|spec1_code','spec2_code|spec2_code'));
//		return $ret;
                $sql = "select DISTINCT (spec2_code),goods_code,spec2_name from goods_sku where goods_code=:goods_code";
                $data = $this->db->get_all($sql,array(':goods_code'=>$code));
                
		return $this->format_ret(1,$data);  
            
	}
	/*
	 * 修改纪录
	 */
	function save($goods_code,$spec1_code) {
		$status = $this->valid($goods_code,$spec1_code, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		$sql = "select goods_spec2_id,goods_code,spec2_code FROM {$this->table} where goods_code ='{$goods_code}' ";
		$data = $this->db->get_all($sql);
		if (count($data) != 0) {
			$sql = "delete from {$this->table} where goods_code ='{$goods_code}'";
			$ret = $this -> db -> query($sql);

		}

		$spec1_code_arr = explode(',',$spec1_code);
		$sql_mx = '';
		foreach ($spec1_code_arr as $row) {
			$sql_mx .= ",('" . $row . "','".$goods_code."')";
		}
		if(!empty($sql_mx)){
			$sql_mx = substr($sql_mx,1);
		}
		$sql = 'INSERT  INTO ' . $this -> table . '(spec2_code,goods_code) VALUES' . $sql_mx ;
		$ret = $this -> db -> query($sql);
		if ($ret) {
			$id = $this -> db -> insert_id();
			return $this -> format_ret("1", $id, 'insert_success');
		} else {
			return $this -> format_ret("-1", '', 'insert_error');
		}
		exit;
		return $ret;
	}

	/*
	 * 服务器端验证
	*/
	private function valid($goods_code,$spec1_code, $is_edit = false) {
		if (!$is_edit && (!isset($goods_code) || !valid_input($goods_code, 'required'))) return GOODS_ERROR_CODE;
		if (!isset($spec1_code) || !valid_input($spec1_code, 'required')) return GOODS_ERROR_NAME;

		return 1;
	}
	/**
	 *
	 * 方法名                               is_exists
	 *
	 * 功能描述                           通过产品代码和规格代码查询记录
	 *
	 * @author      BaiSon PHP R&D
	 * @date        2015-06-11
	 * @param       array $param
	 *
	 */
	function is_exists($param) {
	    $ret = parent::get_row($param);
	    return $ret;
	}

  function get_spec2_realname(){
    return '规格2';
  }

}