<?php

/**
 * 基础数据-云数据管理
 *
 * @author wq
 *
 */
require_model('tb/TbModel');
require_lib("keylock_util");

class ShopTbjdpDbModel extends TbModel {

    function get_table() {
        return 'shop_tb_jdp_db';
    }

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} s
            INNER JOIN osp_kehu k ON k.kh_id=s.kh_id
            INNER JOIN osp_aliyun_rds r ON r.rds_id=s.rds_id WHERE 1";
        $sql_value = array();
        //备注
        if (isset($filter['kh_name']) && $filter['kh_name'] != '') {
            $sql_main .= " AND k.kh_name=:kh_name ";
            $sql_value[':kh_name'] = $filter['kh_name'];
        }
        //别名
        if (isset($filter['rds_name']) && $filter['rds_name'] != '') {
            $sql_main .= " AND r.rds_dbname=:rds_name ";
            $sql_value[':rds_name'] = $filter['rds_name'];
        }
        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            $sql_main .= " AND k.kh_id=:kh_id ";
            $sql_value[':kh_id'] = $filter['kh_id'];
        }
        //别名
        if (isset($filter['rds_id']) && $filter['rds_id'] != '') {
            $sql_main .= " AND r.rds_id=:rds_id ";
            $sql_value[':rds_id'] = $filter['rds_id'];
        }
        //别名
        if (isset($filter['nick']) && $filter['nick'] != '') {
            $sql_main .= " AND s.nick=:nick ";
            $sql_value[':nick'] = $filter['nick'];
        }
        $select = 's.id,s.nick,s.kh_id,s.rds_id,k.kh_name,r.rds_dbname';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function insert($data) {
        //nick,s.kh_id,s.rds_id,
        $key_arr = array('nick', 'kh_id', 'rds_id');
        $new_data = array();
        foreach ($key_arr as $key) {
            if (!isset($data[$key]) || empty($data[$key])) {
                return $this->format_ret(-1, '', $key . "不能为空");
            }
            $new_data[$key] = $data[$key];
        }

        $new_data['create_time'] = time();
        return parent::insert($new_data);
    }
    function del($id){
     return   $this->delete(array('id'=>$id));
    }
    
    

}
