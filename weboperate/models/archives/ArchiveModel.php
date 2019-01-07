<?php

/**
 * 用户相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');

//require_lang('sys');

class ArchiveModel extends TbModel {

    function get_table() {
        return 'osp_post';
    }

    /*
     * 获取岗位信息方法
     */

    function get_jobs_info($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        if (isset($filter['poststate']) && $filter['poststate'] != '') {
            $sql_main .= " AND post_state='{$filter['poststate']}'";
        }
        
        //关键字
        if (isset($filter['keyword']) && $filter['keyword']!='' ) {
                $sql_main .= " AND (post_code LIKE '%". $filter['keyword'] .
                        "%' OR post_name LIKE '%" . $filter['keyword'] . "%') ";
        }
        
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        return $this->get_row(array('post_id' => $id));
    }

    /*
     * 添加新岗位
     */

    function insert($jobs) {
        $status = $this->valid($jobs);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($jobs['post_code']);
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('code_is_exist');

        $ret = $this->is_exists($jobs['post_name'], 'post_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');

        return parent::insert($jobs);
    }

    //更新岗位状态（启用/禁用）
    function update_job_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }

        $ret = parent::update(array('post_state' => $active), array('post_id' => $id));
        return $ret;
    }

    /*
     * 修改岗位信息。
     */

    function update($jobs, $id) {
        $status = $this->valid($jobs, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('post_id' => $id));
        if ($jobs['post_name'] != $ret['data']['post_name']) {
            $ret = $this->is_exists($jobs['post_name'], 'post_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret('name_is_exist');
        }
        $ret = parent::update($jobs, array('post_id' => $id));
        return $ret;
    }

//	/*
//	 * 判断角色代码是否唯一
//	 */
//	private function is_unique($user_code) {
//		$ret = $this->get_row( array('user_code'=>$user_code));
//
//		$status = $ret['status'] == 1 ? USER_ERROR_UNIQUE_CODE : $ret['status'];
//
//		return $this->format_ret($status);
//	}

    /*
     * 服务器端验证
     */
    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['post_code']) || !valid_input($data['post_code'], 'required')))
            return USER_ERROR_CODE;
        if (!isset($data['post_name']) || !valid_input($data['post_name'], 'required'))
            return USER_ERROR_NAME;
        return 1;
    }

    private function is_exists($value, $field_name = 'post_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    static function get_top_menu() {
        $arr_nav_cote = array();
        $_default_nav = array(
            'action_name' => '关于',
            'action_id' => '999999',
            'action_code' => '',
            'sort_order' => 1,
            'other_priv_type' => 6
        );

        $cid_list = array();
        $no_priv_arr = array();
        $db = CTX()->db;

        $sql = "SELECT action_id, action_name, action_code, sort_order";
        $sql .= " FROM sys_action WHERE type='cote' AND parent_id = 0 AND status = 1 ORDER BY sort_order ASC";
        $rs = $db->getAll($sql, 'action_id');

        $rs_cote = array();
        foreach ($rs as $cote) {
            $rs_cote[$cote['action_id']] = $cote;
        }

        return $rs_cote;
    }

    function get_menu_tree() {
        $sql = "SELECT action_id, action_name, action_code, sort_order, parent_id, type";
        $sql .= " FROM sys_action WHERE status = 1 ORDER BY sort_order ASC";
        $rs = $this->db->get_all($sql);

        $menu_cote = $this->array_search_by_key($rs, 'type', 'cote');

        $ret = array();
        foreach ($menu_cote as &$cote) {
            $cote['_child'] = $this->array_search_by_key($rs, 'parent_id', $cote['action_id']);
            foreach ($cote['_child'] as &$group) {
                $group['_child'] = $this->array_search_by_key($rs, 'parent_id', $group['action_id']);
            }
        }

        return $menu_cote;
    }

    private function array_search_by_key($arr, $key, $keyvalue) {
        $rs = array();
        foreach ($arr as $v) {
            if ($v[$key] == $keyvalue) {
                $rs[] = $v;
            }
        }

        return $rs;
    }

}
