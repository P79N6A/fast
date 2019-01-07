<?php

/**
 * 基础数据-云数据管理
 *
 * @author wq
 *
 */
require_model('tb/TbModel');
require_lib("keylock_util");

class RdsDataModel extends TbModel {

    var $rds_db = FALSE;
    var $db_name = 'sysdb';
    private $id_del = 0;
    private $tb_id_list = array();

    function get_table() {
        return 'osp_aliyun_rds';
    }

    function set_tb_id_list() {
        $this->tb_id_list['osp_notice'] = array("id" => 'not_id');
        $this->tb_id_list['osp_rdsextmanage_db'] = array("id" => 'rem_db_id');
        // $this->tb_id_list['osp_valueauth_key'] = array("id" => 'id');
        $this->tb_id_list['osp_valueorder'] = array("id" => 'val_num');
        $this->tb_id_list['osp_valueorder_auth'] = array("id" => 'vra_id');
        $this->tb_id_list['osp_valueserver'] = array("id" => 'value_id');
        $this->tb_id_list['osp_valueserver_category'] = array("id" => 'vc_id');
        $this->tb_id_list['osp_valueserver_detail'] = array("id" => 'vd_id');
        $this->tb_id_list['osp_vmextmanage_ver'] = array("id" => 'vem_id');
    }

    function create_rds_db($rds_id) {
        $ret = $this->get_row(array('rds_id' => $rds_id));
        if (empty($ret['data'])) {
            return FALSE;
        }

        $keylock = get_keylock_string($ret['data']['rds_createdate']);
        $ret['data']['rds_pass'] = create_aes_decrypt($ret['data']['rds_pass'], $keylock);

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
        $row = $this->db->get_row("select rem_db_pid from osp_rdsextmanage_db where rem_db_khid='{$kh_id}' AND rem_db_is_bindkh=1 ");
        if (empty($row)) {
            return FALSE;
        }
        return $row['rem_db_pid'];
    }

    function update_rds_all($type = '') {

        $ret_data = array();
        $data = $this->get_rds_all();
        foreach ($data as $val) {
            $ret = $this->update_kh_data(0, $val['rds_id'], $type);
            if ($ret['status'] < 1) {
                $ret_data[] = $ret;
            }
        }
        if (!empty($ret_data)) {
            return $this->format_ret(-1, $ret_data);
        }
        return $this->format_ret(1);
    }

    function update_rds_all_sql($sql_name) {
        $ret_data = array();
        $data = $this->get_rds_all();

        $sql_path = ROOT_PATH . CTX()->app_name . "/data/sysdb/" . $sql_name . ".php";
        if (!file_exists($sql_path)) {
            echo '为找到文件' . $sql_name;
            return false;
        }
        require_once $sql_path;


        foreach ($data as $val) {
            $ret = $this->create_rds_db($val['rds_id']);
            if ($ret['status'] < 1) {
                $ret_data[] = $ret;
            }
            foreach ($u as $sql) {
                $status = $this->exec_rds_sql($sql);
                if ($status == false) {
                    echo "{$val['rds_id']}执行失败:" . $sql . "\n;";
                }
            }
        }

        return $this->format_ret(1);
    }

    function exec_rds_sql($sql) {
        $status = false;
        try {
            $status = $this->rds_db->query($sql);
        } catch (Exception $ex) {
            $status = false;
            echo "sql:{$sql} \n异常:" . $ex->getMessage() . "\n";
        }

        return $status;
    }

    private function get_rds_all() {
        $cp_info = $this->db->get_row("select cp_id from osp_chanpin where cp_code='efast365'");
        if (empty($cp_info)) {
            return $this->format_ret(-1, array(), '产品信息异常，找不到EFAST产品');
        }
        $cp_id = $cp_info['cp_id'];
        $data = $this->db->get_all("select DISTINCT m.rem_rds_id as rds_id from osp_rdsextmanage m inner 
                join osp_rdsextmanage_db d ON m.rem_rds_id = d.rem_db_pid
                where m.rem_cp_id='{$cp_id}' AND d.rem_db_is_bindkh=1 ");
        return $data;
    }

    function update_rds($rds_id) {
        $this->id_del = 1;
        $ret = $this->update_kh_data(0, $rds_id);
        $this->id_del = 0;
        return $ret;
    }

    function update_kh_data($kh_id, $rds_id = 0, $type = '') {
        $this->set_tb_id_list();
        if ($rds_id == 0 && !empty($kh_id)) {
            $rds_id = $this->get_kh_rds($kh_id);
        }

        if ($rds_id === FALSE) {
            return $this->format_ret(-1, array(), '找不到客户对应的数据库RDS');
        }
        $cp_info = $this->db->get_row("select cp_id from osp_chanpin where cp_code='efast365'");
        if (empty($cp_info)) {
            return $this->format_ret(-1, array(), '产品信息异常，找不到EFAST产品');
        }
        $cp_id = $cp_info['cp_id'];

        $rds_status = $this->create_rds_db($rds_id);

        if ($rds_status === FALSE) {
            return $this->format_ret(-1, array(), '找不到对应的RDS');
        }
        //取消掉客户 级别同步
//        $kh_type_arr = array(
//            'osp_rdsextmanage_db' =>
//            array(
//                'where' => " rem_db_pid='{$rds_id}' and rem_db_khid='{$kh_id}' ",
//            ),
//            'osp_valueorder' => array(
//                'where' => " val_cp_id='{$cp_id}' and val_kh_id='{$kh_id}'   ",
//            ),
//            'osp_valueorder_auth' =>
//            array(
//                'where' => " vra_cp_id={$cp_id} and vra_kh_id='{$kh_id}' ",
//            ),
//        );

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
                'where' => " vra_cp_id={$cp_id} and vra_kh_id in(select rem_db_khid from osp_rdsextmanage_db where rem_db_pid={$rds_id})  ",
            ),
            'osp_vmextmanage_ver' => array(
                'where' => " vem_cp_version_ip in(select rem_db_version_ip from osp_rdsextmanage_db  where rem_db_pid ='{$rds_id}' ) ",
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
            'osp_notice' =>
            array(
                'where' => " 1=1 ",
            ),
        );

//        if (!empty($kh_id)) {//客户纬度
//            $type_arr = array_merge($type_arr, $kh_type_arr);
//        }

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
            // $updata[0] = $data[0];
            if (!empty($data)) {
                $status = $this->update_multi_duplicate($tb, $data);
                if ($status === FALSE) {
                    $arr[$tb] = $val;
                }
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

        $this->rds_db->begin_trans();



        if (isset($this->tb_id_list[$table])) {

            $id = $this->tb_id_list[$table]['id'];
            $id_all = array();
            foreach ($row_arr as $val) {
                if (isset($val[$id])) {
                    $id_all[] = $val[$id];
                }
            }

            if (!empty($id_all)) {
                $data = $this->rds_db->get_all("select {$id} as id from {$table}");
                $del_arr = array();

                foreach ($data as $v) {
                    if (!in_array($v['id'], $id_all)) {
                        $del_arr[] = $v['id'];
                    }
                }
                if (!empty($del_arr)) {
                    $del_str = "'" . implode("','", $del_arr) . "'";
                    $del_sql = "delete from {$table} where {$id} in({$del_str}) ";
                    $this->rds_db->query($del_sql);
                }
            }
        }
        //风险大，取消掉
//        if ($this->id_del == 1) {
//            $del_sql = "TRUNCATE {$table}";
//            $this->rds_db->query($del_sql);
//        }


        $ret = $this->rds_db->query($sql);
        if (!$ret) {
            $this->rollback();
            return false;
        }
        $this->rds_db->commit();
        return true;
    }

    function delete_rds_data($type, $id) {
        $data = $this->get_rds_all();
        foreach ($data as $val) {
            $ret = $this->del_rds_sysdb_data(0, $val['rds_id'], $type, $id);
            if ($ret['status'] < 1) {
                $ret_data[] = $ret;
            }
        }
    }

    function del_rds_sysdb_data($kh_id, $rds_id, $type, $id) {
        if ($kh_id == 0) {
            $rds_status = $this->create_rds_db($rds_id);
        }
        if ($rds_status === FALSE) {
            return $this->format_ret(-1, array(), '找不到对应的RDS');
        }

        $type_arr = array(
            'osp_valueserver_detail' => array(
                'id_key' => 'vd_id',
            ),
        );
        $tb = $type;
        $key_id = $type_arr[$type]['id_key'];
        $sql = "DELETE from   {$tb} where {$key_id}='{$id}' ";
        return $this->rds_db->query($sql);
    }

    function set_rds_all_cache() {
        $cp_info = $this->db->get_row("select cp_id from osp_chanpin where cp_code='efast365'");
        if (empty($cp_info)) {
            return $this->format_ret(-1, array(), '产品信息异常，找不到EFAST产品');
        }
        $cp_id = $cp_info['cp_id'];
        $sql = "select * from {$this->table} where rds_id in(select DISTINCT rem_rds_id from osp_rdsextmanage where rem_cp_id='{$cp_id}')";
        $data = $this->db->get_all($sql);
        $ret_data = array();

        $status = TRUE;
        foreach ($data as $rds_info) {
            $status = $this->set_rds_cache($rds_info);
            if ($status === FALSE) {
                break;
            }
        }
        return $status;
    }

    function set_rds_cache(&$rds_info) {
        static $appCenter = NULL;
        if ($appCenter == NULL) {
            require_model('sys/AppCenterModel');
            $appCenter = new AppCenterModel();
        }
        $keylock = get_keylock_string($rds_info['rds_createdate']);
        $rds_info['rds_pass'] = create_aes_decrypt($rds_info['rds_pass'], $keylock);
        $config = array(
            'name' => $this->db_name,
            'user' => $rds_info['rds_user'],
            'pwd' => $rds_info['rds_pass'],
            'host' => $rds_info['rds_link'],
            'type' => 'mysql',
            'rds_name' => $rds_info['rds_dbname'], //实例名
        );
        return $appCenter->setRdsInfo($rds_info['rds_id'], $config);
    }

    function setAllVmRdsRelation() {
        $cp_info = $this->db->get_row("select cp_id from osp_chanpin where cp_code='efast365'");
        if (empty($cp_info)) {
            return $this->format_ret(-1, array(), '产品信息异常，找不到EFAST产品');
        }
        $cp_id = $cp_info['cp_id'];
        $sql = " select host_id,ali_outip from osp_aliyun_host where host_id in(select DISTINCT asa_vm_id from osp_autoservice_acc where asa_cp_id='{$cp_id}') ";
        $data = $this->db->get_all($sql);
        $ret_data = array();

        $status = TRUE;
        foreach ($data as $vm_info) {
            $status = $this->set_rds_cache($vm_info['host_id']);
            if ($status === FALSE) {
                break;
            }
        }
        return $status;
    }

    function setVmRdsRelation($host_id) {
        static $appCenter = NULL;
        if ($appCenter == NULL) {
            require_model('sys/AppCenterModel');
            $appCenter = new AppCenterModel();
        }
        $sql = "select au.asa_rds_id as rds_id,vm.ali_outip from osp_autoservice_acc au
            inner join osp_aliyun_host vm ON au.asa_vm_id = vm.host_id where au.asa_vm_id ='{$host_id}'";
        $data = $this->db->get_all($sql);
        if (empty($data)) {
            return FALSE;
        }
        $vm_ip = $data[0]['ali_outip'];
        return $appCenter->setVmRdsRelation($vm_ip, $data);
    }

}
