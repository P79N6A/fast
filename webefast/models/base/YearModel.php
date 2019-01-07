<?php
/**
 * 年份相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('base');
require_lib('util/oms_util', true);
require_lib('comm_util', true);
class YearModel extends TbModel {
	function get_table() {
		return 'base_year';
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
			$sql_main .= " AND (rl.year_code LIKE :code_name or rl.year_name LIKE :code_name)";
			$sql_values[':code_name'] = $filter['code_name'].'%';
		}

        //对外接口 增量下载
        if (isset($filter['lastchanged']) && $filter['lastchanged'] !== '') {
            $sql_main .= " AND rl.lastchanged > :lastchanged ";
            $sql_values[':lastchanged'] =  $filter['lastchanged'] ;
        }
        if(isset($filter['is_api']) && $filter['is_api'] !== ''){
            $sql_main .= " order by rl.lastchanged desc ";
        }

		$select = 'rl.*';
		//$data =  $this->get_page_from_sql($filter, $sql_main, $select);
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		
		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);
	}
	
	function get_by_id($id) {
		
		return  $this->get_row(array('year_id'=>$id));
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
	//年份
	function get_year(){
		$sql = "select year_id,year_code,year_name FROM base_year ";
		$rs = $this->db->get_all($sql);
		return $rs;
	}
	/*
	 * 添加新纪录
	 */
	function insert($year) {
		$status = $this->valid($year);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		
		$ret = $this->is_exists($year['year_code']);
		
		if (!empty($ret['data'])) return $this->format_ret(YEAR_ERROR_UNIQUE_CODE);

		$ret = $this->is_exists($year['year_name'], 'year_name');
		if (!empty($ret['data'])) return $this->format_ret(YEAR_ERROR_UNIQUE_NAME);
		
		return parent::insert($year);
	}
	
	/*
	 * 删除记录
	 * */
	function delete($year_id) {
            $data = oms_tb_val('base_year', 'year_id', array('year_id'=>$year_id));
            if (isset($data) && $data == '') {
                return $this->format_ret(-1,array(),'年份数据已删除！');
            }
            $used = $this->is_used_by_id($year_id);
            if($used['status']==1){
                return $this->format_ret(-1,$used['data'],'已经在业务系统中使用，不能删除！');
            }
            $ret = parent::delete(array('year_id'=>$year_id));
            return $ret;
	}
	
	/*
	 * 修改纪录
	 */
	function update($year, $year_id) {
		$status = $this->valid($year, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		$ret2 = $this->get_row(array('year_id'=>$year_id));
		if($year['year_code'] != $ret2['data']['year_code']){
			$ret1 = $this->is_exists($year['year_code'], 'year_code');
			if (!empty($ret1['data'])) return $this->format_ret(YEAR_ERROR_UNIQUE_CODE);
		}
		
		if(isset($year['year_name']) && $year['year_name'] != $ret2['data']['year_name']){
			$ret = $this->is_exists($year['year_name'], 'year_name');
			if (!empty($ret['data'])) return $this->format_ret(YEAR_ERROR_UNIQUE_NAME);
		}
		$ret = parent::update($year, array('year_id'=>$year_id));
               $data = array(
                    //'year_code'=>$year['year_code'],
                    'year_name'=>$year['year_name']
                );
                load_model('prm/GoodsModel')->update_property($data, array('year_code'=>$ret2['data']['year_code']) );
                
                      
                
                
                
		return $ret;
	}

	

	/*
	 * 服务器端验证
	 */
	private function valid($data, $is_edit = false) {
		if (!$is_edit && (!isset($data['year_code']) || !valid_input($data['year_code'], 'required'))) return 	YEAR_ERROR_CODE;
		if (!isset($data['year_name']) || !valid_input($data['year_name'], 'required')) return YEAR_ERROR_NAME;

		return 1;
	}
	
	private function is_exists($value, $field_name='year_code') {
		$ret = parent::get_row(array($field_name=>$value));

		return $ret;
	}


    /**
     * 返回code=>name数组, 默认仅返回启用的仓库
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-06
     * @param   array   $filter  过滤条件, 如array('status'=>'1')
     * @return  array
     */
    public function get_code_name($filter=array()){
        $result = $this->get_by_page($filter);
        $return = array();
        if(isset($result['data']['data'])&&is_array($result['data']['data'])){
            foreach($result['data']['data'] as $value){
                $return[$value['year_code']] = $value['year_name'];
            }
        }
        return $return;
    }
    
    /**
     * 根据id判断在业务系统是否使用
     * @param int $id
     * @return boolean 已使用返回true, 未使用返回false
     */
    public function is_used_by_id($id) {
    	$result = $this->get_value("select year_code from {$this->table} where year_id=:id", array(':id' => $id));
    	$code = $result['data'];
    	$num = $this->get_num('select * from base_goods where year_code=:code', array(':code' => $code));
    	if(isset($num['data'])&&$num['data']>0){
    		//已经在业务系统使用
    		return $this->format_ret(1, $code, '');
    	}else{
    		//尚未在业务系统使用
    		return false;
    	}
    }
    
    /**
     *
     * 方法名                               api_year_update
     *
     * 功能描述                           更新年份数据
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-15
     * @param       array $param
     *              array(
     *                  必选: 'year_code', 'year_name',
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_year_update($param) {
        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
                's' => array('year_code', 'year_name')
        );
        $arr_required = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
    
        //必填项检测通过
        if (TRUE == $ret_required['status']) {
            $arr_deal = $arr_required;
    
            //清空无用数据
            unset($arr_required);
    
            //检测是否已经存在spec1
            $ret = $this->is_exists($arr_deal['year_code']);
            if (1 == $ret['status']) {
                unset($arr_deal['year_code']);
                //更新数据
                $ret = $this->update($arr_deal, $ret['data']['year_id']);
            } else {
                //插入数据
                $ret = $this->insert($arr_deal);
            }
            return $ret;
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
    }
    /**
     * 获取年份
     * @param type $params
     * @return type
     * {"status":1,"data":{"filter":{"page":1,"page_size":100,"page_count":1,"record_count":"1"},
     * "data":[{"year_code":"051","year_name":"42717","lastchanged":"2018-03-20 11:35:21"}]},
     * "message":"\u64cd\u4f5c\u6210\u529f"}
     */
    public function api_year_get($params) {
        $key_option = array(
            's' => array('page','page_size','start_lastchanged', 'end_lastchanged', 'year_code', 'year_name'),
        );
        $r_option = array();
        $ret_option = valid_assign_array($params, $key_option, $r_option);
        //检验传入的参数是否正确
       if (!empty($r_option['page'])) {
            if (!check_value_valid($r_option['page'], 'pint')) {
                return $this->format_ret(-10005, array('page' => $r_option['page']), '页码必须为正整数');
            }
        } else {
            $r_option['page'] = 1;
        }
       //页码
        if (!empty($r_option['page_size'])) {
            if (!check_value_valid($r_option['page_size'], 'pint')) {
                return $this->format_ret(-10005, array('page_size' => $r_option['page_size']), '页数必须为正整数');
            }
            if ($r_option['page_size'] > 100) {
                return $this->format_ret(-10005, array('page_size' => $r_option['page_size']), '每页最多100条');
            }
        } else {
            $r_option['page_size'] = 100;
        }
        //开始时间
        if (!empty($r_option['start_lastchanged'])) {
            $start_lastchanged = strtotime($r_option['start_lastchanged']);
            if ($start_lastchanged === FALSE) {
                return $this->format_ret(-10005, array('start_lastchanged' => $r_option['start_lastchanged']), '最后修改时间-开始格式错误');
            }
        }
        //结束时间
        if (!empty($r_option['end_lastchanged'])) {
            $end_lastchanged = strtotime($r_option['end_lastchanged']);
            if ($end_lastchanged === FALSE) {
                return $this->format_ret(-10005, array('end_lastchanged' => $r_option['end_lastchanged']), '最后修改时间-结束格式错误');
            }
        }
        //是否存在年份代码
        if(!empty($r_option['year_code'])){
            $ret = $this->is_exists($r_option['year_code'],'year_code');
            if($ret['status'] !=1 ){
                return $this->format_ret(-10006, array('year_code' => $r_option['year_code']), '年份代码不存在');
            }
        }
        //是否存在年份名称
        if(!empty($r_option['year_name'])){
            $res = $this->is_exists($r_option['year_name'],'year_name');
            if($res['status'] != 1){
                return $this->format_ret(-10006, array('year_name' => $r_option['year_name']), '年份名称不存在');
            }
        }
        $sql_values = [];
        $select = 'byr.year_code,byr.year_name,byr.lastchanged';
        $sql_main = 'FROM `base_year` AS byr WHERE 1 ';
        //组装SQL
        $this->get_record_sql_where($r_option, $sql_main, $sql_values,'byr.');
        //查询数据
        $rus = $this->get_page_from_sql($r_option, $sql_main, $sql_values, $select);
        $data = $rus['data'];
        if (empty($data)) {
            return $this->format_ret(-10002, (object) array(), '数据不存在');//数据不存在
        }
        $filter = get_array_vars($rus['filter'], array('page', 'page_size', 'page_count', 'record_count'));

        $revert_data = array(
            'filter' => $filter,
            'data' => $data,
        );
        return $this->format_ret(1, $revert_data);
    }
    /**
     * 组装查询sql
     * @param type $filter
     * @param type $sql_main
     * @param type $sql_values
     * @param type $ab
     */
    private function get_record_sql_where($filter, &$sql_main, &$sql_values,$ab) {
        foreach ($filter as $key => $val) {
            if (in_array($key, array('page', 'page_size')) || $val === '') {
                continue;
            }
            if ($key == 'start_lastchanged') {
                $sql_main .= " AND {$ab}lastchanged>=:{$key}";
            } else if ($key == 'end_lastchanged') {
                $sql_main .= " AND {$ab}lastchanged<=:{$key}";
            } else{
                 $sql_main .= " AND {$ab}{$key}=:{$key}";
            }
            $sql_values[":{$key}"] = $val;
        }

        if (!isset($filter['start_lastchanged'])) {
            $start_time = date('Y-m-d');
            $sql_main .= " AND {$ab}lastchanged >= :start_lastchanged";
            $sql_values[':start_lastchanged'] = $start_time;
        }
        if (!isset($filter['end_lastchanged'])) {
            $end_time = date('Y-m-d',strtotime('+1 day'));
            $sql_main .= " AND {$ab}lastchanged <= :end_lastchanged";
            $sql_values[':end_lastchanged'] = $end_time;
        }
    }
}


