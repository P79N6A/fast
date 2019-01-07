<?php

/**
 * 基础数据-云数据库相关业务
 *
 * @author zyp,wkq
 *
 */
require_model('tb/TbModel');
class KehuModel extends TbModel {

    function get_table() {
        return 'osp_kehu';
    }

    function get_basickehu_by_kh_id($kh_id){
        $params = array('kh_id'=>$kh_id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        if (isset($data)) {
            return $data;
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
