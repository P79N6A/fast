<?php

/**
 * 基础数据-云数据管理
 *
 * @author wq
 *
 */
require_model('tb/TbModel');

class RdsDataModel extends TbModel {

    var $rds_db = FALSE;
    var $db_name = 'sysdb';

    function get_table() {
        return 'osp_aliyun_rds';
    }

    function create_rds_db($rds_id) {
        $ret = $this->get_row(array('rds_id' => $rds_id));
        if (empty($ret['data'])) {
            return FALSE;
        }
        $keylock = load_model('sys/OspCryptModel')->get_keylock_string($ret['data']['rds_createdate']);
        $ret['data']['rds_pass'] = load_model('sys/OspCryptModel')->create_aes_decrypt($ret['data']['rds_pass'], $keylock);

        $config = array(
            'name' => $this->db_name,
            'user' => $ret['data']['rds_user'],
            'pwd' => $ret['data']['rds_pass'],
            'host' => $ret['data']['rds_link'],
            'type' => 'mysql',
        );
        $this->rds_db = create_db($config);
        return TRUE;
    }

    function get_kh_rds($kh_id) {
        $row = $this->db->get_row("select rem_db_pid from osp_rdsextmanage_db where rem_db_khid='{$kh_id}'");
        if (empty($row)) {
            return FALSE;
        }
        return $row['rem_db_pid'];
    }

    function update_kh_data($kh_id, $rds_id=0, $type='') {
        if ($rds_id == 0 && !empty($kh_id)) {
            $rds_id = $this->get_kh_rds($kh_id);
        }

        if ($rds_id === FALSE) {
            return $this->format_ret(-1, array(), '找不到客户对应的数据库RDS');
        }
        $cp_info = $this->db->get_row("select cp_id from osp_chanpin where cp_code='efast5'");
        if (empty($cp_info)) {
            return $this->format_ret(-1, array(), '产品信息异常，找不到EFAST产品');
        }
        $cp_id = $cp_info['cp_id'];

        $rds_status = $this->create_rds_db($rds_id);

        if ($rds_status === FALSE) {
            return $this->format_ret(-1, array(), '找不到对应的RDS');
        }

        $kh_type_arr = array(
            'osp_rdsextmanage_db' =>
            array(
                'where' => " rem_db_pid='{$rds_id}' and rem_db_khid='{$kh_id}' ",
            ),
            'osp_valueorder' => array(
                'where' => " val_cp_id='{$cp_id}' and val_kh_id='{$kh_id}'   ",
            ),
            'osp_valueorder_auth' =>
            array(
                'where' => " vra_cp_id={$cp_id} and vra_kh_id='{$kh_id}' ",
            ),
        );

        $type_arr = array(
            'osp_rdsextmanage_db' =>
            array(
                'where' => " rem_db_pid='{$rds_id}' ",
            ),
            'osp_valueorder' => array(
                'where' => " val_cp_id='{$cp_id}' and val_kh_id in(select rem_db_khid from osp_rdsextmanage_db where rem_db_pid={$rds_id})  ",
            ),
            'osp_valueorder_auth' =>
            array(
                'where' => " pra_cp_id={$cp_id} and vra_kh_id in(select rem_db_khid from osp_rdsextmanage_db where rem_db_pid={$rds_id})  ",
            ),
            'osp_vmextmanage_ver' => array(
                'where' => " vem_vm_id in(select asa_vm_id from osp_autoservice_acc where asa_rds_id='{$rds_id}' and asa_cp_id={$cp_id} ) ",
            ),
            'osp_valueserver' => array(
                'where' => " value_cp_id='{$cp_id}' ",
            ),
            'osp_valueserver_category' =>
            array(
                'where' => " vc_cp_id={$cp_id} ",
            ),
            'osp_valueserver_detail' =>
            array(
                'where' => " value_id in(select value_id from osp_valueserver where  value_cp_id='{$cp_id}')",
            ),
        );

        if (!empty($kh_id)) {//客户纬度
            $type_arr = array_merge($type_arr, $kh_type_arr);
        }

        $all_type = array();
        if (!empty($type)) {
            $all_type[$type] = $type_arr[$type];
        } else {
            $all_type = $type_arr;
        }
        $arr = $this->sync_data($all_type);
        if (!empty($arr)) {
            return $this->format_ret(-1, $arr);
        }
        return $this->format_ret(1);
    }

    function sync_data($all_type) {
        $arr = array();
        foreach ($all_type as $tb => $val) {
            $sql = "select * from $tb where {$val['where']}";
            $data = $this->db->get_all($sql);
            $updata[0] = $data[0];

            $status = $this->update_multi_duplicate($tb, $data);

            if ($status == FALSE) {
                $arr[$tb] = $val;
            }
        }
        return $arr;
    }

    function update_multi_duplicate($table, $row_arr) {
        $row_arr = array_values($row_arr);

        $sql_mx = '';
        $key_idex = 0;
        $_new_row = array();

        $key_arr = array_keys(current($row_arr));

        //*******************************************************************************
        //处理当前表中的不存在的字段
        $cols = $this->rds_db->query_for_cols($table);
        foreach ((array) $key_arr as $_key => $_value) {
            if (!empty($cols) && !isset($cols[$_value]) && !in_array($_value, $cols)) {
                unset($key_arr[$_key]);
            }
        }

        //$key_arr = array_keys($row_arr[0]);
        foreach ((array) $key_arr as $_key => $_value) {
            if (!empty($cols) && !isset($cols[$_value]) && !in_array($_value, $cols)) {
                unset($key_arr[$_key]);
            }
        }
        $update_str = "";

        foreach ($key_arr as $k => $v) {
            $update_str .= $v . " = VALUES(" . $v . "),";
        }

        $update_str = substr($update_str, 0, strlen($update_str) - 1);

        //*******************************************************************************

        foreach ($row_arr as $row) {
            foreach ($key_arr as $key) {
                if (isset($row[$key])) {
                    $_new_row[$key] = strip_tags(addslashes($row[$key]));
                } else {
                    $_new_row[$key] = '';
                }
            }
            $sql_mx .= ",('" . implode("','", $_new_row) . "')";
            $key_idex++;
        }
        $sql_mx = substr($sql_mx, 1);

        $sql = 'INSERT  INTO ' . $table . '(' . implode(',', $key_arr) . ') VALUES' . $sql_mx . " ";
        $sql .=" ON DUPLICATE KEY UPDATE  ";
        $sql .= $update_str;

        $ret = $this->rds_db->query($sql);
        return $ret;
    }


}