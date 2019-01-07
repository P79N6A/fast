<?php

/**
 * 基础档案-云主机相关
 *
 * @author zyp,wkq
 *
 */
require_model('tb/TbModel');

class ShopModel extends TbModel {

    function get_table() {
        return 'osp_shangdian';
    }
    function get_shop_by_kh_id($kh_id){
        //TODO 未考虑同平台不同app_key app_secret 的情况
        //sql join以osp_rds 表为准，不存在api_key api_secret 的店铺则不返回
        $sql = "SELECT
                os.*,
                op.pt_code,
                ortable.app_key,
                ortable.app_secret
            FROM
                osp_shangdian os
            RIGHT JOIN osp_platform op ON os.sd_pt_id = op.pt_id
            RIGHT JOIN osp_rds ortable ON os.sd_pt_id = ortable.relation_platform
            WHERE
                os.sd_kh_id =:sd_kh_id and ortable.relation_product = 21" ; //21为efast5的产品代码
        $sql_values[':sd_kh_id'] = $kh_id;

        $data = $this->db->get_all($sql,$sql_values);


        if (isset($data)) {
            return $data;
        } else {
            return "";
        }
    }

    //加密密码
    function encrypt($data) {
        if (isset($data)) {
            $ret = parent::encrypt($data);
            return $ret;
        }
    }

    //解密密码
    function decrypt($data) {
        if (isset($data)) {
            $ret = parent::decrypt($data);
            return $ret;
        }
    }

    //获取web用户密码
    function get_web_pwd($id) {
        $ret = $this->get_row(array('host_id' => $id));
        if (isset($ret)) {
            return $ret['data'];
        } else {
            return "";
        }
    }

    //更新web密码
    function update_web_pass($pwd, $id) {
        $data = array('ali_pass' => $pwd);
        $result = parent::update($data, array('host_id' => $id));
        if ($result) {
            return $this->format_ret("1", "", 'update_success');
        } else {
            return $this->format_ret("-1", '', 'update_error');
        }
    }

    //获取root用户密码
    function get_root_pwd($id) {
        $ret = $this->get_row(array('host_id' => $id));
        if (isset($ret)) {
            return $ret['data'];
        } else {
            return "";
        }
    }

    //更新root密码
    function update_root_pass($pwd, $id) {
        $data = array('ali_root' => $pwd);
        $result = parent::update($data, array('host_id' => $id));
        if ($result) {
            return $this->format_ret("1", "", 'update_success');
        } else {
            return $this->format_ret("-1", '', 'update_error');
        }
    }
    
    //重置web用户密码。
    function change_webuser_pwd($hostinfo,$oldpass,$newpass) {
        $chpass_path = ROOT_PATH . 'weboperate/models/clients/change_passwd.py';
        $host_json = json_encode($hostinfo);
        $command = "/usr/bin/python26 $chpass_path  '$host_json'";
        exec($command, $out, $return);
        if ($return == '0') {
            $outdata = json_decode($out['0'], true);
            if ($outdata['0']['info']['0']['status'] == 'success') {
                $passwd = array();
                //$passwd['ali_pass'] = $this->encrypt($outdata['0']['info']['0']['message']);  //加密操作  
                $passwd['ali_pass'] = $newpass;
                $passwd['ali_old_pass'] = $oldpass;
                $passwd['ali_pass_updatedate'] =date('Y-m-d H:i:s'); //密码修改日期
                $result = $this->update_user_pwd($passwd, $outdata['0']['ipaddr']);
                if ($result) {
                    $title = 'WEB用户重置密码成功';
                    $ip = $outdata['0']['ipaddr'];
                    $detail = "密码修改成功" ;
                    $this->save_pwd_log($title, $detail, $ip);
                    return $this->format_ret("1", "", 'update_success');
                }
            } else {
                $title = 'WEB用户重置密码失败';
                $detail = $outdata['0']['info']['0']['message'];
                $ip = $outdata['0']['ipaddr'];
                $this->save_pwd_log($title, $detail, $ip);
                return $this->format_ret("-1", '', 'update_error');
            }
        }
    }
    
    
     function change_rootuser_pwd($hostinfo,$oldpass,$newpass) {
        $chpass_path = ROOT_PATH . 'weboperate/models/clients/change_passwd.py';
        $host_json = json_encode($hostinfo);
        $command = "/usr/bin/python26 $chpass_path  '$host_json'";
        exec($command, $out, $return);
        if ($return == '0') {
            $outdata = json_decode($out['0'], true);
            if ($outdata['0']['info']['0']['status'] == 'success') {
                $passwd = array();
                //$passwd['ali_root'] = $this->encrypt($outdata['0']['info']['0']['message']);  //加密操作  
                $passwd['ali_root'] = $newpass;
                $passwd['ali_old_root'] = $oldpass;
                $passwd['ali_pass_updatedate'] =date('Y-m-d H:i:s'); //密码修改日期
                $result = $this->update_user_pwd($passwd, $outdata['0']['ipaddr']);
                if ($result) {
                    $title = 'ROOT用户重置密码成功';
                    $ip = $outdata['0']['ipaddr'];
                    $detail = "密码修改成功";
                    $this->save_pwd_log($title, $detail, $ip);
                    return $this->format_ret("1", "", 'update_success');
                }
            } else {
                $title = 'ROOT用户重置密码失败';
                $detail = $outdata['0']['info']['0']['message'];
                $ip = $outdata['0']['ipaddr'];
                $this->save_pwd_log($title, $detail, $ip);
                return $this->format_ret("-1", '', 'update_error');
                
                
            }
        }
    }
    
    
    
    //连接服务器测试
    function host_net_test($ip, $user, $pwd) {
        if (isset($ip) && isset($ip) && isset($pwd)) {
            $connection = ssh2_connect("$ip", 22);
            if (!$connection) {
                return $this->format_ret("-1", '', '连接失败,可能不是Linux服务器');
            } else {
                if (ssh2_auth_password($connection, "$user", "$pwd")) {
                    return $this->format_ret("1", "", '连接成功');
                } else {
                    return $this->format_ret("-1", '', '连接失败，可能密码错了');
                }
            }
        } else {
            return $this->format_ret("-1", '', '账号密码不能为空.');
        }
    }

}
