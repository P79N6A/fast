<?php
/**
 * 营销策略类型业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');

class StrategyTypeModel extends TbModel {
    
        function get_table() {
            return 'osp_market_strategytype';
        }
        
        /*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {
	    $sql_values = array();
		$sql_join = "";
		$sql_main = "FROM {$this->table} t $sql_join WHERE 1";
	   
	    if (isset($filter['is_buildin']) && $filter['is_buildin']!='' ) {
			$sql_main .= " AND is_buildin=:is_buildin";
			$sql_values[':is_buildin'] = $filter['is_buildin'];
		}
		//关键字
		if (isset($filter['keyword']) && $filter['keyword']!='' ) {
		    $sql_main .= " AND (t.st_code LIKE :keyword or t.st_name LIKE :keyword)";
			$sql_values[':keyword'] = '%'.$filter['keyword'].'%';
		}

		if(isset($filter['__sort']) && $filter['__sort'] != '' ){
			$filter['__sort_order'] = $filter['__sort_order'] =='' ? 'asc':$filter['__sort_order'];
			$sql_main .= ' order by '.trim($filter['__sort']).' '.$filter['__sort_order'];
		}
		
		$select = 't.*';
		
		$data =  $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

		$ret_status = OP_SUCCESS;
		$ret_data = $data;

		return $this->format_ret($ret_status, $ret_data);
	}
        
        
        function get_by_id($id) {
                return $this->get_row(array('st_id' => $id));
        }
        
        
        function insert($sts) {
                $status = $this->valid($sts);
                if ($status < 1) {
                    return $this->format_ret($status);
                }

                $ret = $this->is_exists($sts['st_code']);
                if ($ret['status'] > 0 && !empty($ret['data']))
                    return $this->format_ret(USER_ERROR_UNIQUE_CODE);

                $ret = $this->is_exists($jobs['st_code'], 'st_code');
                if ($ret['status'] > 0 && !empty($ret['data']))
                    return $this->format_ret(USER_ERROR_UNIQUE_NAME);

                return parent::insert($sts);
        }
        
        function update($sts, $id) {
                $status = $this->valid($sts, true);
                if ($status < 1) {
                    return $this->format_ret($status);
                }

                $ret = $this->get_row(array('st_id' => $id));
                if ($sts['st_name'] != $ret['data']['st_name']) {
                    $ret = $this->is_exists($sts['st_name'], 'st_name');
                    if ($ret['status'] > 0 && !empty($ret['data']))
                        return $this->format_ret(USER_ERROR_UNIQUE_NAME);
                }
                $ret = parent::update($sts, array('st_id' => $id));
                return $ret;
        }
        
        /*
        * 服务器端验证
        */
        private function valid($data, $is_edit = false) {
                if (!$is_edit && (!isset($data['st_code']) || !valid_input($data['st_code'], 'required')))
                    return USER_ERROR_CODE;
                if (!isset($data['st_name']) || !valid_input($data['st_name'], 'required'))
                    return USER_ERROR_NAME;
                return 1;
        }

        private function is_exists($value, $field_name = 'st_code') {
                $ret = parent::get_row(array($field_name => $value));
                return $ret;
        }
}
