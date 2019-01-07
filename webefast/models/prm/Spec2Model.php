<?php
/**
 * 规格2相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('prm');

class Spec2Model extends TbModel {
	function get_table() {
		return 'base_spec2';
	}

	/*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {

		$sql_join = "";
		$sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
		$sql_values = array();
		//名称或代码
		if (isset($filter['code_name']) && $filter['code_name'] != '') {
			//$sql_main .= " AND (rl.store_code LIKE '%" . $filter['code_name'] . "%' or rl.store_name LIKE '%" . $filter['code_name'] . "%' )";
			$sql_main .= " AND (rl.spec2_code LIKE :code_name or rl.spec2_name LIKE :code_name)";
			$sql_values[':code_name'] = $filter['code_name'].'%';
		}
		$select = 'rl.*';

        //对外接口 增量下载
        if (isset($filter['changed_time_start']) && $filter['changed_time_start'] !== '') {
            $sql_main .= " AND rl.lastchanged > :changed_time_start ";
            $sql_values[':changed_time_start'] =  $filter['changed_time_start'] ;
        }
        if (isset($filter['changed_time_end']) && $filter['changed_time_end'] !== '') {
            $sql_main .= " AND rl.lastchanged < :changed_time_end ";
            $date = new DateTime($filter['changed_time_end']);
            $date->add(new DateInterval('P1D'));
            $sql_values[':changed_time_end'] = $date->format('Y-m-d');
        }

        $sql_main .= " order by rl.lastchanged desc";

		//$data =  $this->get_page_from_sql($filter, $sql_main, $select);
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
                
                foreach ($data['data'] as $key => &$value) {
                    $value['is_common'] = $value['spec2_code'] === '000' ? 0 : 1;
                }
		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);
	}
    /**
     * @param $code
     * @return string
     */
    function get_spec2_name($code){
        $sql="SELECT spec2_name FROM {$this->table} WHERE spec2_code = :spec2_code";
        $res=$this->db->get_row($sql,array(':spec2_code'=>$code));
        return $res['spec2_name'];
    }

	/**
	 * @param $id
	 * @return array
	 */
	function get_by_id($id) {

		return  $this->get_row(array('spec2_id'=>$id));
	}

	/**
	 * @param $code
	 */
	function get_by_code($code) {
		return $this->get_row(array('spec2_code'=>$code));
	}
	/**
	 * 通过field_name查询
	 *
	 * @param  $ :查询field_name
	 * @param  $select ：查询返回字段
	 * @return array (status, data, message)
	 */
	public function get_by_field($field_name,$value, $select = "*") {

		$sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
		$data = $this -> db -> get_row($sql, array(":{$field_name}" => $value));

		if ($data) {
			return $this -> format_ret('1', $data);
		} else {
			return $this -> format_ret('-1', '', 'get_data_fail');
		}
	}
	/*
	 * 添加新纪录
	 */
	function insert($spec2) {
		$status = $this->valid($spec2);
		if ($status < 1) {
			return $this->format_ret($status);
		}

		$ret = $this->is_exists($spec2['spec2_code']);

		if (!empty($ret['data'])) return $this->format_ret(SPEC2_ERROR_UNIQUE_CODE);
		//2017-12-14  bug#1824  规格一、规格二 去除空格
		$spec2_data = array(
			'spec2_code' => trim($spec2['spec2_code']),
            'spec2_name' => trim($spec2['spec2_name']),
            'remark' => trim($spec2['remark']),
		);
		return parent::insert($spec2_data);
	}

	/*
	 * 删除记录
	 * */
	function delete($spec2_id) {
		$used = $this->is_used_by_id($spec2_id);
		if($used){
			return $this->format_ret(-1,array(),'已经在业务系统中使用，不能删除！');
		}
		$ret = parent::delete(array('spec2_id'=>$spec2_id));
		return $ret;
	}

	/*
	 * 修改纪录
	 */
	function update($spec2, $spec2_id) {
		$status = $this->valid($spec2, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		$ret1 = $this->get_row(array('spec2_id'=>$spec2_id));
		if(isset($spec2['spec2_code']) && $spec2['spec2_code'] != $ret1['data']['spec2_code']){
			$ret2 = $this->is_exists($spec2['spec2_code'], 'spec2_code');
			if (!empty($ret2['data'])) return $this->format_ret(SPEC2_ERROR_UNIQUE_CODE);
		}
		//2017-12-14  bug#1824  规格一、规格二 去除空格
		$spec2_data = array(
			'spec2_name' => trim($spec2['spec2_name']),
            'remark' => trim($spec2['remark']),
		);
		$ret = parent::update($spec2_data, array('spec2_id'=>$spec2_id));

                load_model('prm/SkuModel')->update($spec2_data,array('spec2_code'=>$ret1['data']['spec2_code'])) ;

		return $ret;
	}

	//规格2
	function get_spec2(){
		$sql = "select spec2_id,spec2_code,spec2_name FROM {$this->table} order by spec2_code ";
		$rs = $this->db->get_all($sql);
		return $rs;
	}
        //规格2
	function get_by_code_spec2($spec2_str){
		$sql = "select spec2_id,spec2_code,spec2_name FROM {$this->table} WHERE spec2_code in ({$spec2_str}) order by spec2_code ";
		$rs = $this->db->get_all($sql);
//                $key = 0;
//                foreach ($rs as $val) {
//                    $rs[$key][0] = $val['spec1_code'];
//                    $rs[$key][1] = $val['spec1_name'];
//                    $key++;
//                }
		return $rs;
	}
	function add_check_name($params) {
		$sql = "select count(1) from {$this->table} where spec2_name=:spec2_name";
		$sql_values[':spec2_name'] = $params['spec2_name'];
		if (!empty($params['spec2_id'])){
			$sql .= " and spec2_id !=:spec2_id";
			$sql_values[':spec2_id'] = $params['spec2_id'];
		}
		$count_ret = $this->get_num($sql,$sql_values);
		if ($count_ret['data'] > 0){
			return $this->format_ret(-1,'规格编码已存在');
		}
		return $this->format_ret(1,'');
	}

	/*
	 * 服务器端验证
	 */
	private function valid(&$data, $is_edit = false) {
            trim_unsafe_html($data);
            reset($data);
            if (!$is_edit && (!isset($data['spec2_code']) || !valid_input($data['spec2_code'], 'required'))) return SPEC2_ERROR_CODE;
            if (!isset($data['spec2_name']) || !valid_input($data['spec2_name'], 'required')) return SPEC2_ERROR_NAME;
            return 1;
	}

	function is_exists($value, $field_name='spec2_code') {
		$ret = parent::get_row(array($field_name=>$value));

		return $ret;
	}

	function format_ret($status, $data = '', $msg_key = NULL){
		$ret = parent::format_ret($status, $data, $msg_key);
		$spec2_realname = load_model('prm/GoodsSpec2Model')->get_spec2_realname();
		$ret['message'] = str_replace('规格2',$spec2_realname, $ret['message']);
		return $ret;
	}

	/**
	 * 根据id判断在业务系统是否使用
	 * @param int $id
	 * @return boolean 已使用返回true, 未使用返回false
	 */
	public function is_used_by_id($id) {
		$result = $this->get_value("select spec2_code from {$this->table} where spec2_id=:id", array(':id' => $id));
		$code = $result['data'];
		$num = $this->get_num('select * from goods_sku where spec2_code=:code', array(':code' => $code));
		if(isset($num['data'])&&$num['data']>0){
			//已经在业务系统使用
			return true;
		}else{
			//尚未在业务系统使用
			return false;
		}
	}
	
    /**
     * API-更新商品规格2
     * @author BaiSon PHP R&D
     * @date 2015-06-15
     * @modify 2017-06-16 WMH 完善代码
     * @param array $param
     * @return array 操作结果
     */
    public function api_goods_spec2_update($param) {
        $key_required = array(
            's' => array('spec2_code', 'spec2_name')
        );
        //可选字段
        $key_option = array(
            's' => array('remark')
        );

        $arr_required = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
        //必填项检测通过
        if (TRUE !== $ret_required['status']) {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);

        //合并数据
        $arr_deal = array_merge($arr_required, $arr_option);

        //清空无用数据
        unset($arr_required, $arr_option);

        foreach ($arr_deal as &$val) {
            $val = trim($val);
        }
        
        //检测是否已经存在spec1
        $ret = $this->is_exists($arr_deal['spec2_code']);
        if (1 == $ret['status']) {
            unset($arr_deal['spec2_code']);
            //更新数据
            $ret = $this->update($arr_deal, $ret['data']['spec2_id']);
        } else {
            //插入数据
            $ret = $this->insert($arr_deal);
        }
        return $ret;
    }

    //查询总条数
        function spec2_count($filter){
            $sql = '';
            if (isset($filter['code_name']) && $filter['code_name'] != '') {
                $sql = 'SELECT COUNT(*) count FROM base_spec2 WHERE spec2_code LIKE "'.$filter['code_name'].'%" or spec2_name LIKE "'.$filter['code_name'].'%"';
            }else{
                $sql = 'SELECT COUNT(*) count FROM base_spec2 WHERE 1 ';
            }
            //判断以选择的规格
            if (isset($filter['spec2_code_list']) && $filter['spec2_code_list'] != ''){
                $filter['spec2_code_list'] = explode(',',$filter['spec2_code_list']);
                $filter["spec2_code_list"] = "'".implode("','",$filter["spec2_code_list"])."'"; 
                $sql .= " AND spec2_code NOT IN ({$filter["spec2_code_list"]})";
            }
            return $this->db->get_row($sql);
        }
    function get_spec2_page($filter) {
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
        $sql_values = array();
        //名称或代码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            //$sql_main .= " AND (rl.store_code LIKE '%" . $filter['code_name'] . "%' or rl.store_name LIKE '%" . $filter['code_name'] . "%' )";
            $sql_main .= " AND (rl.spec2_code LIKE :code_name or rl.spec2_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        
        //判断有没有以选择的规格
        $filter['spec2_code_list'] = explode(',', $filter['spec2_code_list']);
        $code_list = "'" . implode("','", $filter["spec2_code_list"]) . "'";
        if (isset($code_list) && $code_list != '') {
            $sql_main .= " AND rl.spec2_code NOT IN ({$code_list})";
        }

        $select = 'rl.*';
        $sql_main .= " order by rl.spec2_code";
        //$data =  $this->get_page_from_sql($filter, $sql_main, $select);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

}



