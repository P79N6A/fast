<?php

/**
 * 营销中心相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');
require_lib("comm_util");

class ValueorderauthModel extends TbModel {

    function get_table() {
        return 'osp_valueorder_auth';
    }

    /*
     * 获取增值服务信息方法
     */
    function get_value_auth($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        $sql_values=array();
        //关联产品搜索条件
        if (isset($filter['cp_id']) && $filter['cp_id'] != '') {
           $sql_main .= " AND vra_cp_id =" . $filter['cp_id'];
        }
        //客户名称
        if (isset($filter['kh_name']) && $filter['kh_name'] != '') {
            $filter['kh_name']=trim($filter['kh_name']);
            $sql_kehu = "SELECT kh_id FROM osp_kehu WHERE kh_name LIKE :kh_name";
            $sql_value[':kh_name'] = '%' . $filter['kh_name'] . '%';
            $ret = $this->db->get_all($sql_kehu, $sql_value);
            $kehu_arr = array();
            foreach ($ret as $value) {
                $kehu_arr[] = $value['kh_id'];
            }
            $key='vra_kh_id';
            $kh_id=$this->arr_to_in_sql_value($kehu_arr, $key, $sql_values);
            $sql_main .= " AND vra_kh_id IN ({$kh_id})";
        }
        //增值服务
        if (isset($filter['vra_server_id']) && $filter['vra_server_id'] != '') {
            $sql_main .= " AND vra_server_id = :vra_server_id";
            $sql_values[':vra_server_id'] = $filter['vra_server_id'];
        }
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values,$select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('vra_kh_id|osp_kh','vra_server_id|osp_valueserver','vra_cp_id|osp_chanpin'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('vra_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('vra_kh_id|osp_kh','vra_server_id|osp_valueserver','vra_cp_id|osp_chanpin'));
        return $this->format_ret($ret_status, $data);
    }
    
    function get_values_info($otherinfo) {
        $params = $otherinfo;
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
    
    //增值授权审核操作。
   function insert_value_auth($valueauth) {
        if (isset($valueauth)) {
            $upstr = 'server_code,vra_startdate,vra_enddate,val_orderdate,vra_state,vra_bz';
             $data = parent::insert_dup($valueauth,'UPDATE',$upstr);
             return $this->format_ret("1", '1', '审核成功');
        }else{
            return $this->format_ret("-1", '', '审核失败');
        }
    }
    
    //增值授权更新审核操作
   function update_value_auth($valueauth,$id) {
        if (isset($valueauth) && isset($id)) {
             $data = parent::update($valueauth, array('vra_id' => $id));
             return $this->format_ret("1", '1', '审核成功');
        }else{
            return $this->format_ret("-1", '', '审核失败');
        }
    }


}
