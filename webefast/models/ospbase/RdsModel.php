<?php

/**
 * 基础数据-云数据库相关业务
 *
 * @author zyp,wkq
 *
 */
require_model('tb/TbModel');
class RdsModel extends TbModel {

    function get_table() {
        return 'osp_aliyun_rds';
    }

    function get_rds_by_kh_id($kh_id){
        //从绑定库中返回用户所在的rds_id 和数据库名称
        $rem_data = load_model('ospbase/RdsExtManageDB')->get_dbname_dbid($kh_id);
        $rds_id = $rem_data['rem_db_pid'];
        $db_name = $rem_data['rem_db_name'];
        $sql = "
        SELECT
            *
        FROM
            osp_aliyun_rds
        WHERE
	        rds_id =:rds_id";
        $sql_value[':rds_id'] = $rds_id;
        $data = $this->db->get_all($sql,$sql_value);

        //$params = array('rds_id'=>$rds_id);
        //$data = $this->create_mapper($this->table)->where($params)->find_by();
        $data[0]['rds_dbname'] = $db_name;
        if (isset($data)) {
            return $data[0];
        } else {
            return "";
        }
    }

    //加密密码
    function encrypt($data){
        if(isset($data)){
            $ret = parent::encrypt($data);
            return $ret;
        }
    }
    //解密密码
    function decrypt($data){
        if(isset($data)){
            $ret = parent::decrypt($data);
            return $ret;
        }
    }
    
    //rds服务器连接测试
    function rds_net_test($ip, $user, $pwd) {
        if (isset($ip) && isset($ip) && isset($pwd)) {
//            print_r($ip);print_r($user);print_r($pwd);die;
            $connection = mysql_connect("$ip","$user","$pwd");
            if (!$connection) {
                return $this->format_ret("-1", '', '连接失败,可能密码不对');
            } else {
                mysql_close($connection);
                return $this->format_ret("1", "", '连接成功');
            }
        } else {
            return $this->format_ret("-1", '', '账号密码不能为空.');
        }
    }
}
