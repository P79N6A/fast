<?php

/*
 * 基础数据-云主机VM列表
 */
require_lib("keylock_util");
class Hostinfo {

    //云主机列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }

    //新建、编辑主机显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑主机(VM)信息', 'add' => '新建主机(VM)主机',);
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('basedata/HostModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }

    //设置主机状态处理。
    function set_active(array & $request, array & $response, array & $app) {
        $arr = array('enable'=>1, 'disable'=>0);
        $ret = load_model('clients/AliModel')->update_host_active($arr[$request['type']], $request['host_id']);
        exit_json_response($ret);
    }
    
     //设置主机状态处理。
    function set_active_enable(array & $request, array & $response, array & $app) {
        $this->set_active($request,$response,$app);
    }
    function set_active_disable(array & $request, array & $response, array & $app) {
        $this->set_active($request,$response,$app);
    }

    
    //编辑主机信息数据处理。
    function ali_edit(array & $request, array & $response, array & $app) {
        if(($request['ali_share_type']==1)&&(empty($request['kh_id']))){
            $ret=array(
                'status'=>'-1',
                'data'=>'',
                'message'=>'独享模式，客户名称必填'
            );
        }else{
            $host = get_array_vars($request, array(
                'kh_id',
                'ali_inip',
                'ali_outip',
                'ali_notes',
                'ali_type',
                'ali_user',
                'ali_apache',
                'ali_php',
                'ali_mysql',
                'ali_mem',
                'ali_cpu',
                'ali_net',
                'ali_disk',
                'ali_starttime',
                'ali_endtime',
                'ali_server_model',
                'ali_stan_price',
                'ali_settle_price',
                'ali_cost_price',
                'ali_sales_price',
                'ali_server_use',
                'ali_operate_system',
                'ali_another_name',
                'ali_share_type'
            ));
            $host['ali_updateuser'] = CTX()->get_session("user_id");
            $host['ali_updatedate'] = date('Y-m-d H:i:s');
            $ret = load_model('basedata/HostModel')->update($host, $request['host_id']);
        }
        exit_json_response($ret);
    }

    //添加岗位信息数据处理。    
    function ali_add(array & $request, array & $response, array & $app) {
        if(($request['ali_share_type']==1)&&(empty($request['kh_id']))){
            $ret=array(
                'status'=>'-1',
                'data'=>'',
                'message'=>'独享模式，客户名称必填'
            );
        }else{
            $host = get_array_vars($request, array(
                'kh_id',
                'ali_inip',
                'ali_outip',
                'ali_notes',
                'ali_type',
                'ali_user',
                'ali_apache',
                'ali_php',
                'ali_mysql',
                'ali_mem',
                'ali_cpu',
                'ali_net',
                'ali_disk',
                'ali_starttime',
                'ali_endtime',
                'ali_server_model',
                'ali_stan_price',
                'ali_settle_price',
                'ali_cost_price',
                'ali_sales_price',
                'ali_server_use',
                'ali_operate_system',
                'ali_another_name',
                'ali_share_type'
            ));
            $host['ali_createdate'] = date('Y-m-d H:i:s');
            $host['ali_createuser'] = CTX()->get_session("user_id");
            $keylock=get_keylock_string(date('Y-m-d'));
            $host['ali_pass'] = create_aes_encrypt($request['ali_pass'],$keylock);
            $host['ali_root'] = create_aes_encrypt($request['ali_root'],$keylock);
            $host['ali_state'] = '1';  //默认启用
            $ret = load_model('basedata/HostModel')->insert($host);
        }
        exit_json_response($ret);
    }

    //查看密码方法
    function viewpass(array & $request, array & $response, array & $app) {
        $ret = load_model('basedata/HostModel')->get_by_id($request['_id']);
        $keylock=get_keylock_string($ret['data']['ali_createdate']);
        $ret['data']['ali_pass'] = create_aes_decrypt($ret['data']['ali_pass'],$keylock);
        $ret['data']['ali_root'] = create_aes_decrypt($ret['data']['ali_root'],$keylock);
        $response['data'] = $ret['data'];
    }

     //强制重置WEB和ROOT密码方法
    function forreset_pass(array & $request, array & $response, array & $app) {
        if (isset($request['do'])) {
            if (!empty($request['pass_type'])) {
                if ($request['pass_type'] == 1) {     //pass_type=1是修改WEB的密码
                    $ret = load_model('clients/AliModel')->get_hosts_pwd($request['host_id']);
                    $keylock=get_keylock_string($ret['ali_createdate']);
                    //更新web密码
                    $rootpwd=create_aes_decrypt($ret['ali_root'],$keylock);
                    $newpass=create_aes_encrypt($request["newpass"],$keylock);
                    $hostinfo = array();
                   //阿里改成外网
                    $ipaddr = $ret['ali_type']==1?$ret['ali_inip']:$ret['ali_outip'];
                    $hostinfo[] = array(
                        'ipaddr' => $ipaddr,
                        'rootpwd' => $rootpwd,
                        'username' => array($ret['ali_user']),
                        'passwd' => array($request["newpass"]),
                        'port' => '22',
                    );
                    $run_webuser = load_model('clients/AliModel')->change_webuser_pwd($hostinfo, $ret['ali_pass'],$newpass,$request['host_id']);
                    exit_json_response($run_webuser);
                }else{
                    $ret = load_model('clients/AliModel')->get_hosts_pwd($request['host_id']);
                    $keylock=get_keylock_string($ret['ali_createdate']);
                    //更新root密码
                    $newpass=create_aes_encrypt($request["newpass"],$keylock);
                    $hostinfo = array();
                                    //阿里改成外网
                    $ipaddr = $ret['ali_type']==1?$ret['ali_inip']:$ret['ali_outip'];
                    $hostinfo[] = array(
                        'ipaddr' => $ipaddr,
                        'rootpwd' => $request["newpass"],
                        'username' => array("root"),
                        'passwd' => array($request["newpass"]),
                        'port' => '22',
                    );
                    $run_rootuser = load_model('clients/AliModel')->change_rootuser_pwd($hostinfo, $ret['ali_root'],$newpass,$request['host_id']);
                    exit_json_response($run_rootuser);
                }
            }else {
                exit_json_response(load_model('clients/AliModel')->format_ret("-1", '', '请选择密码类型'));
            }
        }else {
            $response['data'] = array('host_id' => $request['_id']);
        }
    }
   
     //修改WEB和ROOT密码方法
    function change_pass(array & $request, array & $response, array & $app) {
        if (isset($request['do'])) {
            if (!empty($request['pass_type'])) {
                if ($request['pass_type'] == 1) {     //pass_type=1是修改WEB的密码
                    $ret = load_model('clients/AliModel')->get_hosts_pwd($request['host_id']);
                    $keylock=get_keylock_string($ret['ali_createdate']);
                    $old_pass = create_aes_encrypt($request['oldpass'],$keylock);
                    if (isset($ret)) {
                        if ($old_pass != $ret['ali_pass']) {
                            exit_json_response(load_model('clients/AliModel')->format_ret("-1", '', '原密码错误'));
                        } else {
                            //更新web密码
                            $rootpwd=create_aes_decrypt($ret['ali_root'],$keylock);
                            $newpass=create_aes_encrypt($request["newpass"],$keylock);
                            $hostinfo = array();
                                                              //阿里改成外网
                    $ipaddr = $ret['ali_type']==1?$ret['ali_inip']:$ret['ali_outip'];
                            $hostinfo[] = array(
                                'ipaddr' => $ipaddr,
                                'rootpwd' => $rootpwd,
                                'username' => array($ret['ali_user']),
                                'passwd' => array($request["newpass"]),
                                'port' => '22',
                            );
                            $run_webuser = load_model('clients/AliModel')->change_webuser_pwd($hostinfo, $old_pass,$newpass,$request['host_id']);
                            exit_json_response($run_webuser);
                        }
                    }
                } else {
//                  //pass_type=2是修改root的密码
                    $ret = load_model('clients/AliModel')->get_hosts_pwd($request['host_id']);
                    $keylock=get_keylock_string($ret['ali_createdate']);
                    $old_root_pass = create_aes_encrypt($request['oldpass'],$keylock);
                    if (isset($ret)) {
                        if ($old_root_pass != $ret['ali_root']) {
                            exit_json_response(load_model('clients/AliModel')->format_ret("-1", '', '原密码错误'));
                        } else {
                            //更新root密码
                            $rootpwd=create_aes_decrypt($ret['ali_root'],$keylock);
                            $newpass=create_aes_encrypt($request["newpass"],$keylock);
                                                                           //阿里改成外网
                            $ipaddr = $ret['ali_type']==1?$ret['ali_inip']:$ret['ali_outip'];
                            $hostinfo = array();
                            $hostinfo[] = array(
                                'ipaddr' => $ipaddr,
                                'rootpwd' => $rootpwd,
                                'username' => array("root"),
                                'passwd' => array($request["newpass"]),
                                'port' => '22',
                            );
                            $run_rootuser = load_model('clients/AliModel')->change_rootuser_pwd($hostinfo, $old_root_pass,$newpass,$request['host_id']);
                            exit_json_response($run_rootuser);
                        }
                    }
                }
            } else {
                exit_json_response(load_model('clients/AliModel')->format_ret("-1", '', '请选择密码类型'));
            }
        } else {
            $response['data'] = array('host_id' => $request['_id']);
        }
    }

//    //主机部署操作
//    function do_deployment(array & $request, array & $response, array & $app) {
//        $ret=load_model('basedata/HostModel')->update_deployment($request['host_id']);
//        exit_json_response($ret);
//    }
    
    //基础数据-云主机列表-连接测试
    function net_test(array & $request, array & $response, array & $app){
        $ret = load_model('basedata/HostModel')->get_by_id($request['_id']);
        $keylock=get_keylock_string($ret['data']['ali_createdate']);
        $rootpwd= create_aes_decrypt($ret['data']['ali_root'],$keylock);
        if (isset($ret['data'])){
            $ip = $ret['data']['ali_outip'];
            $user = 'root';
            $pwd = $rootpwd;
            $status = load_model('basedata/HostModel')->host_net_test($ip,$user,$pwd);
            exit_json_response($status);
        }else{
            exit_json_response(load_model('basedata/HostModel')->format_ret("-1", '', '查询数据错误'));
        }    
    }
    
   //重置单条记录的密码
    function do_reset_pass(array & $request, array & $response, array & $app) {
            $ret = load_model('clients/AliModel')->gethostById($request['host_id']);
            $keylock=get_keylock_string($ret['ali_createdate']);
            $rootpwd=create_aes_decrypt($ret['ali_root'],$keylock);
            $hostinfo = array();
            $hostinfo[] = array(
                'ipaddr' => $ret['ali_outip'],
                'rootpwd' => $rootpwd,
                'username' => array("root", $ret['ali_user']),
                'passwd' => array("", ""),
                'port' => '22',
            );
            $chpass_path = ROOT_PATH . 'weboperate/models/clients/change_passwd.py';
            $host_json = json_encode($hostinfo);
            $command = "/usr/bin/python $chpass_path  '$host_json'";
            exec($command, $out, $return);
            if ($return == '0') {
                $outdata = json_decode($out['0'], true);
                if ($outdata['0']['info']['0']['status'] == 'success') {
                    $newrootpwd=create_aes_encrypt($outdata['0']['info']['0']['message'],$keylock);
                    $passwd = array();
                    //$passwd['ali_root'] = load_model('clients/AliModel')->encrypt($outdata['0']['info']['0']['message']);  //加密操作  
                    $passwd['ali_root'] = $newrootpwd;
                    $passwd['ali_old_root'] = $ret['ali_root'];
                    $passwd['ali_pass_updatedate'] =date('Y-m-d H:i:s'); //密码修改日期
                    $result = load_model('clients/AliModel')->update_user_pwd($passwd, $outdata['0']['ipaddr']);
                    if ($result) {
                        $title = 'ROOT用户智能重置密码成功';
                        $ip = $outdata['0']['ipaddr'];
                        $detail = "密码修改成功" . $outdata['0']['info']['0']['message'];
                        load_model('clients/AliModel')->save_pwd_log($title, $detail, $ip);
                        $retout['rootstate']="成功";
                        //记得要更新密码最近修改日期
                    }
                } else {
                    $title = 'ROOT用户智能重置密码失败';
                    $detail = $value['info']['0']['message'];
                    $ip = $outdata['0']['ipaddr'];
                    load_model('clients/AliModel')->save_pwd_log($title, $detail, $ip);
                    $retout['rootstate']="失败";
                }
                if ($outdata['0']['info']['1']['status'] == 'success') {
                    $newwebpwd=create_aes_encrypt($outdata['0']['info']['1']['message'],$keylock);
                    $passwd = array();
                    //$passwd['ali_pass'] = load_model('clients/AliModel')->encrypt($outdata['0']['info']['1']['message']);  //加密操作  
                    $passwd['ali_pass'] = $newwebpwd;
                    $passwd['ali_old_pass'] = $ret['ali_pass'];
                    $passwd['ali_pass_updatedate'] =date('Y-m-d H:i:s'); //密码修改日期
                    $result = load_model('clients/AliModel')->update_user_pwd($passwd, $outdata['0']['ipaddr']);
                    if ($result) {
                        $title = 'WEB用户智能重置密码成功';
                        $ip = $outdata['0']['ipaddr'];
                        $detail = "密码修改成功" . $outdata['0']['info']['1']['message'];
                        load_model('clients/AliModel')->save_pwd_log($title, $detail, $ip);
                        $retout['webstate']="成功";
                    }
                } else {
                    $title = 'WEB用户智能重置密码失败';
                    $detail = $outdata['0']['info']['1']['message'];
                    $ip = $outdata['0']['ipaddr'];
                    load_model('clients/AliModel')->save_pwd_log($title, $detail, $ip);
                    $retout['webstate']="失败";
                }
                exit_json_response("1", $retout, "执行完成");
            }
            else {
                exit_json_response(load_model('clients/AliModel')->format_ret("-1", '', '命令执行失败'));
            }

    }
    
    

}
