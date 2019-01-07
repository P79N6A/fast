<?php

require_model('tb/TbModel');

class SysCarryModel extends TbModel {

    function add_carry_db($param) {
        $data = array();


        $data['rds_id'] = $param['rds_id'];
        $data['db_name'] = $param['db_name'];
        $data['carry_name'] = $param['carry_name'];
        $data['create_time'] = date('Y-m-d H:i:s');
        $this->insert_multi_exp('sys_carry_db', array($data), true);


        return $this->format_ret(1);
    }

    function add_kh_carry_db($param) {

        $data['carry_db_id'] = $param['carry_db_id'];
        $data['kh_id'] = $param['kh_id'];
        $data['create_time'] = date('Y-m-d H:i:s');



        $this->insert_multi_exp('sys_carry_kh_db', array($data), true);
        return $this->format_ret(1);
    }

    function get_by_page($filter) {
        $sql_main = "FROM sys_carry_kh_db kd "
                . " INNER JOIN sys_carry_db cd ON kd.carry_db_id = cd.carry_db_id"
                . " INNER JOIN osp_kehu kh ON kh.kh_id = kd.kh_id"
                . " INNER JOIN osp_aliyun_rds rd ON rd.rds_id = cd.rds_id"
                . " WHERE 1";

        //产品搜索条件
        if (isset($filter['kh_name']) && $filter['kh_name'] != '') {
            $sql_main .= " AND kh.kh_name  like '%" . $filter['kh_name'] . "%'";
        }
        //排序条件
        $select = "kd.id,kd.carry_name,kh.kh_name,kh.kh_id,rd.rds_id,sys_carry_db.db_name,rd.rds_dbname,rd.rds_notes";
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function select_carry_db() {
        $sql = "select carry_db_id,rds_id,carry_name from sys_carry_db";
        $data = $this->db->get_all($sql);
        $arr = array();
        foreach ($data as $val) {
            $arr[] = array(
                $val['carry_db_id'],
                $val['carry_name'] . "({$val['rds_id']})",
            );
        }
        return $arr;
    }

}
