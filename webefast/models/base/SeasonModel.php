<?php
/**
 * 季节相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('base');
require_lib('util/oms_util', true);
require_lib('comm_util', true);
class SeasonModel extends TbModel {
	function get_table() {
		return 'base_season';
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
			$sql_main .= " AND (rl.season_code LIKE :code_name or rl.season_name LIKE :code_name)";
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
	
	function get_by_id($season_id) {
		
		return  $this->get_row(array('season_id'=>$season_id));
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
	
	//季节
	function get_season(){
		$sql = "select season_id,season_code,season_name FROM base_season ";
		$rs = $this->db->get_all($sql);
		return $rs;
	}
	/*
	 * 添加新纪录
	 */
	function insert($season) {
		$status = $this->valid($season);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		
		$ret = $this->is_exists($season['season_code']);
		
		if (!empty($ret['data'])) return $this->format_ret(SEASON_ERROR_UNIQUE_CODE);

		$ret = $this->is_exists($season['season_name'], 'season_name');
		if (!empty($ret['data'])) return $this->format_ret(SEASON_ERROR_UNIQUE_NAME);
		
		return parent::insert($season);
	}
	
	/*
	 * 删除记录
	 * */
	function delete($season_id) {
            $data = oms_tb_val('base_season', 'season_id', array('season_id'=>$season_id));
            if (isset($data) && $data == '') {
                return $this->format_ret(-1,array(),'季节数据已删除！');
            }
            $used = $this->is_used_by_id($season_id);
            if($used['status']==1){
                return $this->format_ret(-1,$used['data'],'已经在业务系统中使用，不能删除！');
            }
            $ret = parent::delete(array('season_id'=>$season_id));
            return $ret;
	}
	
	/*
	 * 修改纪录
	 */
	function update($season, $season_id) {
		$status = $this->valid($season, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		$ret2 = $this->get_row(array('season_id'=>$season_id));
		if(isset($season['season_code']) && $season['season_code'] != $ret2['data']['season_code']){
			$ret1 = $this->is_exists($season['season_code'], 'season_code');
			if (!empty($ret1['data'])) return $this->format_ret(SEASON_ERROR_UNIQUE_CODE);
		}
		
		if($season['season_name'] != $ret2['data']['season_name']){
			$ret = $this->is_exists($season['season_name'], 'season_name');
			if (!empty($ret['data'])) return $this->format_ret(SEASON_ERROR_UNIQUE_NAME);
		}
		$ret = parent::update($season, array('season_id'=>$season_id));
                
               $data = array(
                    //'season_code'=>$season['season_code'],
                    'season_name'=>$season['season_name']
                );
                load_model('prm/GoodsModel')->update_property($data, array('season_code'=>$ret2['data']['season_code']) );
                
                
                
		return $ret;
	}

	

	/*
	 * 服务器端验证
	 */
	private function valid($data, $is_edit = false) {
		if (!$is_edit && (!isset($data['season_code']) || !valid_input($data['season_code'], 'required'))) return SEASON_ERROR_CODE;
		if (!isset($data['season_name']) || !valid_input($data['season_name'], 'required')) return SEASON_ERROR_NAME;

		return 1;
	}
	
	private function is_exists($value, $field_name='season_code') {
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
    public function get_code_name($filter=array('status'=>'1')){
        $result = $this->get_by_page($filter);
        $return = array();
        if(isset($result['data']['data'])&&is_array($result['data']['data'])){
            foreach($result['data']['data'] as $value){
                $return[$value['season_code']] = $value['season_name'];
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
    	$result = $this->get_value("select season_code from {$this->table} where season_id=:id", array(':id' => $id));
    	$code = $result['data'];
    	$num = $this->get_num('select * from base_goods where season_code=:code', array(':code' => $code));
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
     * 方法名                               api_season_update
     *
     * 功能描述                           更新季节数据
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-15
     * @param       array $param
     *              array(
     *                  必选: 'season_code', 'season_name',
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_season_update($param) {
        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
                's' => array('season_code', 'season_name')
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
            $ret = $this->is_exists($arr_deal['season_code']);
            if (1 == $ret['status']) {
                unset($arr_deal['season_code']);
                //更新数据
                $ret = $this->update($arr_deal, $ret['data']['season_id']);
            } else {
                //插入数据
                $ret = $this->insert($arr_deal);
            }
            return $ret;
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
    }
    
    function api_season_get($params) {
        $key_option = array(
            's' => array('page','page_size','start_lastchanged', 'end_lastchanged', 'season_code', 'season_name'),
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
        //是否存在季节代码
        if(!empty($r_option['season_code'])){
            $ret = $this->is_exists($r_option['season_code'],'season_code');
            if($ret['status'] !=1 ){
                return $this->format_ret(-10006, array('season_code' => $r_option['season_code']), '季节代码不存在');
            }
        }
        //是否存在季节名称
        if(!empty($r_option['season_name'])){
            $res = $this->is_exists($r_option['season_name'],'season_name');
            if($res['status'] != 1){
                return $this->format_ret(-10006, array('season_name' => $r_option['season_name']), '季节名称不存在');
            }
        }
        $sql_values = [];
        $select = 'bs.season_code,bs.season_name,bs.remark,bs.lastchanged';
        $sql_main = 'FROM `base_season` AS bs WHERE 1 ';
        //组装SQL
        $this->get_record_sql_where($r_option, $sql_main, $sql_values,'bs.');
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
     * 组装sql
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

