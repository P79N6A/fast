<?php

/**
 * 基础档案-云主机相关
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');

class HostModel extends TbModel {
    public $type_name = array(
        '1' => '独享',
        '2' => '共享',
    );
    function get_table() {
        return 'osp_aliyun_host';
    }

    function get_host_info($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
//        $sql_join = "left join osp_kehu kh on h.kh_id=kh.kh_id  ";
        $sql_value=array();
        $sql_join = "left join osp_kehu kh on h.kh_id=kh.kh_id  ";
        $sql_main = "FROM {$this->table} h $sql_join WHERE 1";
        
        //客户名称搜索条件
        if (isset($filter['client_name']) && $filter['client_name'] != '') {
            $sql_main .= " AND kh.kh_name LIKE '%" . $filter['client_name'] . "%'";
        }
        //服务用途搜索条件
        if (isset($filter['server_use']) && $filter['server_use'] != '') {
            $sql_main .= " AND h.ali_server_use = " . $filter['server_use'];
        }
        //IP地址搜索条件
        if (isset($filter['ipaddr']) && $filter['ipaddr'] != '') {
            $sql_main .= " AND h.ali_outip LIKE '%" . $filter['ipaddr'] . "%'";
        }
        //内网IP地址搜索条件
        if (isset($filter['ali_inip']) && $filter['ali_inip'] != '') {
            $sql_main .= " AND h.ali_inip LIKE '%" . $filter['ali_inip'] . "%'";
        }
        //主机类型
        if (isset($filter['ali_type']) && $filter['ali_type'] != '') {
            $sql_main .= " AND h.ali_type =" . $filter['ali_type'];
        }
        //到期时间
        /*if (isset($filter['ali_endtime']) && $filter['ali_endtime'] != '') {
            $sql_main .= " AND ali_endtime <='" . $filter['ali_endtime'] . "'";
        }*/
        //到期时间
        if (!empty($filter['ali_endtime_start'])) {
            $sql_main .= " AND h.ali_endtime >= '".$filter['ali_endtime_start'] . " 00:00:00'";
        }
        if (!empty($filter['ali_endtime_end'])) {
            $sql_main .= " AND h.ali_endtime <= '".$filter['ali_endtime_end'] . " 23:59:59'";
        }
        //部署条件
        if (isset($filter['ali_deployment']) && $filter['ali_deployment'] != '') {
            $sql_main .= " AND h.ali_deployment='{$filter['ali_deployment']}'";
        }
        //操作系统搜索
        if (isset($filter['system_type']) && $filter['system_type'] != '') {
            $sql_main .= " AND h.ali_operate_system = " . $filter['system_type'];
        }
        //状态
        if (isset($filter['ali_state']) && $filter['ali_state'] != '') {
            $sql_main .= " AND h.ali_state='{$filter['ali_state']}'";
        }
        //模式
        if (isset($filter['ali_share_type']) && $filter['ali_share_type'] != '') {
            $sql_main .= " AND h.ali_share_type=:ali_share_type ";
            $sql_value[':ali_share_type'] = $filter['ali_share_type'];
        }
        //型号
        if (isset($filter['ali_server_model']) && $filter['ali_server_model'] != '') {
            $sql_main .= " AND h.ali_server_model=:ali_server_model ";
            $sql_value[':ali_server_model'] = $filter['ali_server_model'];
        }
        //别名
        if (isset($filter['ali_another_name']) && $filter['ali_another_name'] != '') {
            $sql_main .= " AND h.ali_another_name=:ali_another_name ";
            $sql_value[':ali_another_name'] = $filter['ali_another_name'];
        }
        //备注
        if (isset($filter['ali_notes']) && $filter['ali_notes'] != '') {
            $sql_main .= " AND h.ali_notes=:ali_notes ";
            $sql_value[':ali_notes'] = $filter['ali_notes'];
        }
        //未绑定的客户
//        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
//            $sql_main .= " AND h.kh_id=:kh_id ";
//            $sql_value[':kh_id'] = $filter['kh_id'];
//        }
        //排序条件
        if(isset($filter['order']) && $filter['order'] == 'ali_endtime'){
            $sql_main .= " order by h.ali_endtime desc";
        }else{
            $sql_main .= " order by h.host_id desc";
        }

        $select = 'h.*';
        $data = $this->get_page_from_sql($filter,$sql_main,$sql_value,$select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('ali_type|osp_cloud_server','ali_server_model|osp_cloud_type'));
        foreach($ret_data['data'] as &$value){
            $value['ali_share_type_name']=$this->type_name[$value['ali_share_type']];
        }
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('host_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
//        filter_fk_name($data, array('kh_id|osp_kh','ali_type|osp_cloud_server','ali_server_model|host_model'));
//        return $this->format_ret($ret_status, $data);
//        return $this->get_row(array('host_id' => $id));
        
        //处理关联代码表
        filter_fk_name($data, array('ali_createuser|osp_user_id','ali_updateuser|osp_user_id','kh_id|osp_kh'));
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加新主机
     */

    function insert($host) {
        if (isset($host)) {
            $this->format_ret($host);
        }
        $ret = $this->is_exists($host['ali_outip']);
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(USER_ERROR_UNIQUE_CODE);
            return parent::insert($host);
    }

    /*
     * 修改客户信息。
     */

    function update($host, $id) {
        if (isset($host)) {
            $ret = parent::update($host, array('host_id' => $id));
            return $ret;
        }
    }

    /*
     * 部署操作
     */

    function update_deployment($id) {
        $ret = parent::update(array('ali_deployment' => '1'), array('host_id' => $id));
        return $ret;
    }

//    /*
//     * 服务器端验证
//     */
//    private function valid($data, $is_edit = false) {
//        if (!$is_edit && (!isset($data['oCode']) || !valid_input($data['oCode'], 'required')))
//            return KH_ERROR_CODE;
//        if (!isset($data['kh_name']) || !valid_input($data['kh_name'], 'required'))
//            return KH_ERROR_NAME;
//            return 1;
//    }

    private function is_exists($value, $field_name = 'ali_outip') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
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
