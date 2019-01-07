<?php

/**
 * 客户中心-云主机相关
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');

class AliModel extends TbModel {

    function get_table() {
        return 'osp_aliyun_host';
    }

    function get_aliserver_info($filter) {
        $sql_join = "left join osp_kehu kh on h.kh_id=kh.kh_id  ";
        $sql_main = "FROM {$this->table} h $sql_join WHERE 1";

        //客户名称搜索条件
        if (isset($filter['client_name']) && $filter['client_name'] != '') {
            $sql_main .= " AND kh.kh_name LIKE '%" . $filter['client_name'] . "%'";
        }
        //IP地址搜索条件
        if (isset($filter['ipaddr']) && $filter['ipaddr'] != '') {
            $sql_main .= " AND h.ali_outip LIKE '%" . $filter['ipaddr'] . "%'";
        }

        //主机类型
        if (isset($filter['ali_type']) && $filter['ali_type'] != '') {
            $sql_main .= " AND h.ali_type =" . $filter['ali_type'];
        }
        //到期时间
        /* if (isset($filter['ali_endtime']) && $filter['ali_endtime'] != '') {
          $sql_main .= " AND h.ali_endtime <='" . $filter['ali_endtime'] . "'";
          } */
        //到期时间
        if (!empty($filter['ali_endtime_start'])) {
            $sql_main .= " AND h.ali_endtime >= '" . $filter['ali_endtime_start'] . " 00:00:00'";
        }
        if (!empty($filter['ali_endtime_end'])) {
            $sql_main .= " AND h.ali_endtime <= '" . $filter['ali_endtime_end'] . " 23:59:59'";
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

        //排序条件及过滤客户
        $sql_main .= " AND h.kh_id !=0 order by host_id desc";

        $select = 'h.*,kh.kh_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('ali_type|osp_cloud_server', 'ali_server_model|osp_cloud_type'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('host_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('kh_id|osp_kh', 'ali_createuser|osp_user_id', 'ali_updateuser|osp_user_id'));
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加新岗位
     */

    function insert($host) {
        if (isset($host)) {
            $this->format_ret($host);
        }
        $ret = $this->is_exists($host['ali_outip'], 'ali_outip');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret("wip_is_exist");

        /*
          $ret = $this->is_exists($host['ali_inip'], 'ali_inip');
          if ($ret['status'] > 0 && !empty($ret['data']))
          return $this->format_ret("nip_is_exist");
         */

        return parent::insert($host);
    }

    /*
     * 修改客户信息。
     */

    function update($host, $id) {
        if (isset($host)) {
            $ret = $this->get_row(array('host_id' => $id));
            if ($host['ali_outip'] != $ret['data']['ali_outip']) {
                $retoutip = $this->is_exists($host['ali_outip'], 'ali_outip');
                if ($retoutip['status'] > 0 && !empty($retoutip['data']))
                    return $this->format_ret('wip_is_exist');
            }
            /*
              if ($host['ali_inip'] != $ret['data']['ali_inip']) {
              $retinip = $this->is_exists($host['ali_inip'], 'ali_inip');
              if ($retinip['status'] > 0 && !empty($retinip['data']))
              return $this->format_ret('nip_is_exist');
              }
             */
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
    function get_hosts_pwd($id) {
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
            return $ret['data']["ali_root"];
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

    //自动智能重置密码
    function update_user_pwd($pwd, $host_id) {
        if (isset($pwd)) {
            $result = parent::update($pwd, array('host_id' => $host_id));
            return $result;
        }
    }

    //获取所有主机信息
    function get_all_host_data() {
        $sql_main = "select ali_outip,ali_root,ali_user,ali_createdate from osp_aliyun_host where kh_id !=0 ";
        $data = $this->db->get_all($sql_main);
        return($data);
    }

    //操作日志记录
    function save_pwd_log($title, $detail, $ip) {
        if (!empty($title)) {
            $loginfo = array();
            $loginfo['log_operate_title'] = $title;
            $loginfo['log_operate_user'] = CTX()->get_session("user_id");
            $loginfo['log_operate_detail'] = $detail;
            $loginfo['log_operate_date'] = date('Y-m-d H:i:s');
            $loginfo['log_operate_ip'] = $ip;
            $logdata = $this->db->create_mapper('osp_passwd_log')->insert($loginfo);
            return $logdata;
        } else {
            return false;
        }
    }

    function change_webuser_pwd($hostinfo, $oldpass, $newpass,$host_id) {
        $chpass_path = ROOT_PATH . 'weboperate/models/clients/change_passwd.py';
        $host_json = json_encode($hostinfo);
        $command = "/usr/bin/python $chpass_path  '$host_json'";
        exec($command, $out, $return);
        if ($return == '0') {
            $outdata = json_decode($out['0'], true);
            if ($outdata['0']['info']['0']['status'] == 'success') {
                $passwd = array();
                //$passwd['ali_pass'] = $this->encrypt($outdata['0']['info']['0']['message']);  //加密操作  
                $passwd['ali_pass'] = $newpass;
                $passwd['ali_old_pass'] = $oldpass;
                $passwd['ali_pass_updatedate'] = date('Y-m-d H:i:s'); //密码修改日期
                $result = $this->update_user_pwd($passwd, $host_id);
                if ($result) {
                    $title = 'WEB用户重置密码成功';
                    $ip = $outdata['0']['ipaddr'];
                    $detail = "密码修改成功";
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

    function change_rootuser_pwd($hostinfo, $oldpass, $newpass,$host_id) {
        $chpass_path = ROOT_PATH . 'weboperate/models/clients/change_passwd.py';
        $host_json = json_encode($hostinfo);
        $command = "/usr/bin/python $chpass_path  '$host_json'";
        exec($command, $out, $return);
        if ($return == '0') {
            $outdata = json_decode($out['0'], true);
            if ($outdata['0']['info']['0']['status'] == 'success') {
                $passwd = array();
                //$passwd['ali_root'] = $this->encrypt($outdata['0']['info']['0']['message']);  //加密操作  
                $passwd['ali_root'] = $newpass;
                $passwd['ali_old_root'] = $oldpass;
                $passwd['ali_pass_updatedate'] = date('Y-m-d H:i:s'); //密码修改日期
                $result = $this->update_user_pwd($passwd, $host_id);
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

    function gethostById($hostid) {
        $sql_main = "SELECT * FROM {$this->table} WHERE host_id=:hostid ";
        $sql_values[':hostid'] = $hostid;

        $ret = $this->db->get_row($sql_main, $sql_values);

        return $ret;
    }

    function gethostByIP($ip) {
        $sql_main = "SELECT * FROM {$this->table} WHERE ali_outip=:ali_outip ";
        $sql_values[':ali_outip'] = $ip;

        $ret = $this->db->get_row($sql_main, $sql_values);

        return $ret;
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

    
    //批量获取所有服务器上的文件。并保存到/www/scanfile目录。
    function run_allserver_command($ip, $user, $pwd) {
        if (isset($ip) && isset($user) && isset($pwd)) {
            $connection = ssh2_connect("$ip", 22);
            $filesh = '/osp/fastapp/weboperate/models/clients/scan_report.sh';
            $filesh2 = '/osp/fastapp/weboperate/models/clients/install.sh';
            if (!$connection) {
                return $this->format_ret("-1", '', '连接失败,可能不是Linux服务器');
            } else {
                if (ssh2_auth_password($connection, "$user", "$pwd")) {
                    ##$af=ssh2_scp_send($connection,  $filesh, '/data/scan_report.sh', 0744);
                    #$af2 = ssh2_scp_send($connection, $filesh2, '/data/install.sh', 0744);
                    #$stream1 = ssh2_exec($connection, 'bash /data/install.sh');
                    #$ff=stream_set_blocking($stream1, true);
                    #$stdout=stream_get_contents($stream1);
                    ##$stream= ssh2_exec($connection, 'cat /usr/local/aegis/globalcfg/reports/scanfiles.txt|grep "yes_webshell" |wc -l');
                    $lorun = exec("/bin/mkdir /www/scanfile/{$ip}_file");
                    $getfiles = ssh2_scp_recv($connection, '/usr/local/aegis/globalcfg/reports/webshell.txt', "/www/scanfile/{$ip}_file/webshell.txt");
                    if ($getfiles) {
                        $title = "获取webshell成功";
                        $detail = "获取webshell保存成功";
                        $this->save_san_log($title, $detail, $ip);
                    } else {
                        $title = "获取webshell失败";
                        $detail = "获取webshell保存失败";
                        $this->save_san_log($title, $detail, $ip);
                        #continue;
                    }
                    $dir = "/www/scanfile/{$ip}_file/";
                    $filename = "{$dir}webshell.txt";
                    $str = file_get_contents($filename);
                    $lines = explode("\n", $str);

                    $msg = '';
                    $count = 0;
                    foreach ($lines as $line) {
                        if (empty($line))
                            continue;
                        list($_a, $filepath) = explode(':', $line, 2);
                        $_localfile = basename($filepath);
                        $_ret = ssh2_scp_recv($connection, $filepath, $dir . $_localfile);
                        $count++;
                        $msg .= $filepath . ' ' . ($_ret ? 'success' : 'fail') . "\r\n";
                    }

                    $title = "共{$count}个文件";
                    #$detail = $cmd_data;
                    $this->save_san_log($title, $msg, $ip);
                    return $this->format_ret("-1", "", 'service_error');
                }else {
                    $title = "服务器连接失败";
                    $detail = "此服务器无法连接";
                    $this->save_san_log($title, $detail, $ip);
                    return $this->format_ret("-1", "", 'service_error');
                }
            }
        } else {
            return $this->format_ret("-1", '', '账号密码不能为空.');
        }
        exit;
    }

    //command操作日志记录
    function save_rcommand_log($title, $detail, $ip) {
        if (!empty($title)) {
            $loginfo = array();
            $loginfo['rg_operate_title'] = $title;
            $loginfo['rg_operate_user'] = CTX()->get_session("user_id");
            $loginfo['rg_operate_detail'] = $detail;
            $loginfo['rg_operate_date'] = date('Y-m-d H:i:s');
            $loginfo['rg_operate_ip'] = $ip;
            $logdata = $this->db->create_mapper('osp_runcommand_log')->insert($loginfo);
            return $logdata;
        } else {
            return false;
        }
    }

    //command操作日志记录
    function save_san_log($title, $detail, $ip) {
        if (!empty($title)) {
            $loginfo = array();
            $loginfo['sg_operate_title'] = $title;
            $loginfo['sg_operate_user'] = CTX()->get_session("user_id");
            $loginfo['sg_operate_detail'] = $detail;
            $loginfo['sg_operate_date'] = date('Y-m-d H:i:s');
            $loginfo['sg_operate_ip'] = $ip;
            $logdata = $this->db->create_mapper('osp_scanreport_log')->insert($loginfo);
            return $logdata;
        } else {
            return false;
        }
    }

    function _log($msg) {
        error_log(date('Y-m-d H:i:s') . "\t{$msg}\r\n", 3, '/tmp/mv_log');
    }

    
    //批量移动所有服务器上面以gz、back、zip、sql等结尾的文件并将文件统一移动到/www/filebackup目录里面。
    function mv_serverfile_command($ip, $user, $pwd) {
        if (isset($ip) && isset($user) && isset($pwd)) {
            $this->_log($ip . '-> connecting...');
            $connection = ssh2_connect("$ip", 22);
            $filesh = '/osp/fastapp/weboperate/models/clients/scan_report.sh';
            $filesh2 = '/osp/fastapp/weboperate/models/clients/install.sh';
            if (!$connection) {
                return $this->format_ret("-1", '', '连接失败,可能不是Linux服务器');
            } else {
                $this->_log($ip . '-> auth...');
                if (ssh2_auth_password($connection, "$user", "$pwd")) {
                    $lserfile = '/data/filebackup';
                    $rspath = '/data/www';
                    $stream = ssh2_exec($connection, "mv $rspath/*.gz $lserfile;mv $rspath/*.back $lserfile;mv $rspath/*.back2 $lserfile;mv $rspath/*.tar $lserfile;mv $rspath/*.bz2 $lserfile;mv $rspath/*.zip $lserfile;mv $rspath/*.rar $lserfile;mv $rspath/*.bak $lserfile;echo ok");
                    $this->_log($ip . '-> begin to move...');
                    $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
                    // Enable blocking for both streams
                    stream_set_blocking($errorStream, true);
                    stream_set_blocking($stream, true);

                    $cmd_data = stream_get_contents($stream);
                    $cmd_err_msg = stream_get_contents($errorStream);

                    // Close the streams
                    fclose($errorStream);
                    fclose($stream);
                    file_put_contents('/tmp/servers/' . $ip, $cmd_data);
                    $this->_log($ip . '-> result:' . (strpos($cmd_data, 'ok') === FALSE ? 'true' : 'false'));
                    if (strpos($cmd_data, 'ok') !== FALSE) {
                        $title = "mv命令执行成功";
                        #$detail = $cmd_data;
                        $this->save_rcommand_log($title, $cmd_data, $ip);
                        return $this->format_ret("-1", "", 'service_error');
                    } else {
                        $title = "mv失败";
                        $detail = "执行服务器命令失败";
                        $this->save_rcommand_log($title, $detail, $ip);
                        return $this->format_ret("1", "", 'update_success');
                        #continue;
                    }
                } else {
                    $title = "服务器连接失败";
                    $detail = "此服务器无法连接";
                    $this->save_san_log($title, $detail, $ip);
                    return $this->format_ret("-1", "", 'service_error');
                }
            }
        } else {
            return $this->format_ret("-1", '', '账号密码不能为空.');
        }
        exit;
    }
    
    //更新主机状态（启用/停用）
    function update_host_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }

        $ret = parent::update(array('ali_state' => $active), array('host_id' => $id));
        return $ret;
    }

}
