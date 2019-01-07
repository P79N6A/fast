<?php

require_lib('common_util');
require_lib('keylock_util');
require_model('tb/TbModel');

class InitLoginModel extends TbModel {

    function create_rds_db($kh_id) {
        $rem_sql = "SELECT rem_db_name,rem_db_pid FROM osp_rdsextmanage_db WHERE rem_db_khid=:rem_db_khid";
        $rem_sql_value = array(":rem_db_khid" => $kh_id);
        $res = $this->db->get_row($rem_sql, $rem_sql_value);
        $sql = "SELECT * FROM osp_aliyun_rds WHERE rds_id=:rds_id";
        $sql_value = array("rds_id" => $res['rem_db_pid']);
        $ret = $this->db->get_row($sql, $sql_value);
        $keylock = get_keylock_string($ret['rds_createdate']);
        $ret['rds_pass'] = create_aes_decrypt($ret['rds_pass'], $keylock);
        $config = array(
            'name' => $res['rem_db_name'],
            'user' => $ret['rds_user'],
            'pwd' => $ret['rds_pass'],
            'host' => $ret['rds_link'],
            'type' => 'mysql',
        );
        return create_db($config);
    }

    /**
     * @todo 检测是否存在用户名
     */
    function get_user_info($obj, $user_code) {
        $sql = "SELECT user_id FROM sys_user WHERE user_code=:user_code";
        $sql_value = array(":user_code" => $user_code);
        $res = $obj->get_row($sql, $sql_value);
        return $res;
    }

    /**
     * @todo 添加用户信息,同时默认设置用户为超级管理员
     */
    function add_user($obj, $user_info, $kh_id) {
        $status = $this->is_strong($user_info['password']);
        if ($status) {
            $user_info['is_strong'] = '2';
        }
        $user_info['password'] = $this->encode_pwd($user_info['password']);
        $user_info['is_manage'] = 1;//超级管理员
        $res = $obj->insert('sys_user', $user_info);
        //添加用户角色,默认为超级管理员
        if ($res) {
            $sql = "SELECT user_id FROM sys_user WHERE user_code=:user_code";
            $sql_value = array(":user_code" => $user_info['user_code']);
            $user_id = $obj->get_row($sql, $sql_value);
            $sys_user_role = array('role_id' => 1, 'user_id' => $user_id);
            $ret = $obj->insert('sys_user_role', $sys_user_role);
            if ($ret) {
                $data = array('pra_kh_status' => 1);
                $where = array('pra_kh_id' => $kh_id);
                $re = $this->db->update('osp_productorder_auth', $data, $where);
                return $re;
            }
        } else {
            return $res;
        }
    }

    /**
     * @todo 加密密码
     */
    function encode_pwd($pwd) {
        return md5(md5($pwd) . $pwd);
    }

    /**
     * @todo 服务器端验证密码强度
     */
    private function is_strong($code) {
        if (preg_match("/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/", $code) == true) {
            return 1;
        } else {
            return FALSE;
        }
    }

}
