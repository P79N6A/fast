<?php

/*
 * 系统档案-rds档案类
 */
require_lib("keylock_util");
class Alirds {

    //聚石塔rds档案列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }

    //新建、编辑rds显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑客户云数据库信息', 'add' => '新建客户云数据库信息');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('clients/AlirdsModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }
    
    
    //设置数据库状态处理。
    function set_active(array & $request, array & $response, array & $app) {
        $arr = array('enable'=>1, 'disable'=>0);
        $ret = load_model('clients/AlirdsModel')->update_rds_active($arr[$request['type']], $request['rds_id']);
        exit_json_response($ret);
    }
    
     //设置数据库状态处理。
    function set_active_enable(array & $request, array & $response, array & $app) {
        $this->set_active($request,$response,$app);
    }
    function set_active_disable(array & $request, array & $response, array & $app) {
        $this->set_active($request,$response,$app);
    }

    //编辑rds信息数据处理。
    function rds_edit(array & $request, array & $response, array & $app) {
        $rds = get_array_vars($request,
        array(
                'kh_id',
                'rds_user',
                'rds_link',
                'rds_dbname',
                'rds_dbtype',
                'rds_pass',
                'rds_starttime',
                'rds_endtime',
                'rds_server_model',
                'rds_mem',
                'rds_disk',
                'rds_con',
                'rds_qps',
                'rds_iops',
                'rds_notes',
        ));
//        $data = load_model('clients/AlirdsModel')->get_by_id($request['rds_id']);
//        $current_pass = $data['data']['rds_pass'];

        //修改web密码，如果密码没有修改则不加密。
//        if ($current_pass === $request['rds_pass']) {
//            $rds['rds_pass'] = $request['rds_pass'];
//        } else {
//            $rds['rds_pass'] = load_model('clients/AlirdsModel')->encrypt($request['rds_pass']);
//        }
        $rds['rds_updateuser'] = CTX()->get_session("user_id");
        $rds['rds_updatedate'] = date('Y-m-d H:i:s');
        $ret = load_model('clients/AlirdsModel')->update($rds, $request['rds_id']);
        exit_json_response($ret);
    }

    //添加rds信息数据处理。    
    function rds_add(array & $request, array & $response, array & $app) {
        $rds = get_array_vars($request,
        array(
                'kh_id',
                'rds_user',
                'rds_link',
                'rds_dbname',
                'rds_dbtype',
                'rds_pass',
                'rds_starttime',
                'rds_endtime',
                'rds_server_model',                
                'rds_mem',
                'rds_disk',
                'rds_con',
                'rds_qps',
                'rds_iops',
                'rds_notes',
        ));
        //$rds['rds_pass'] = load_model('clients/AlirdsModel')->encrypt($request['rds_pass']);
        $keylock=get_keylock_string(date('Y-m-d'));
        $rds['rds_pass'] = create_aes_encrypt($request['rds_pass'],$keylock);
        $rds['rds_createuser'] = CTX()->get_session("user_id");
        $rds['rds_createdate'] = date('Y-m-d H:i:s');
        $rds['rds_updateuser'] = CTX()->get_session("user_id");
        $rds['rds_updatedate'] = date('Y-m-d H:i:s');
        $rds['rds_state'] = '1'; //默认启用
        $ret = load_model('clients/AlirdsModel')->insert($rds);
        exit_json_response($ret);
    }

    //查看密码方法
    function viewpass(array & $request, array & $response, array & $app) {
//        $title_arr = array('edit' => '编辑RDS信息', 'add' => '新建RDS');
//        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('clients/AlirdsModel')->get_by_id($request['_id']);
        $keylock=get_keylock_string($ret['data']['rds_createdate']);
        $ret['data']['rds_pass'] = create_aes_decrypt($ret['data']['rds_pass'],$keylock);
        $response['data'] = $ret['data'];
    }
    
    function forreset_pass(array & $request, array & $response, array & $app) {
        if (isset($request['do'])) {
            $ret = load_model('clients/AlirdsModel')->get_rds_pwd($request['rds_id']);
            $keylock=get_keylock_string($ret['rds_createdate']);
            if ($ret != "") {
                //更新密码
               $newpwd=create_aes_encrypt($request["rds_newpass"],$keylock);
               $result = load_model('clients/AlirdsModel')->update_rds_pass($newpwd, $request['rds_id']);
               exit_json_response($result);
            }else {
                //原密码验证不通过
                exit_json_response(load_model('clients/AlirdsModel')->format_ret("-1", '', '获取数据错误'));
            }
        }else {
            $response['data'] = array('rds_id' => $request['_id']);
        }
    }

    function change_pass(array & $request, array & $response, array & $app) {
        if (isset($request['do'])) {
//            $oldpass = $request['rds_oldpass'];
            $ret = load_model('clients/AlirdsModel')->get_rds_pwd($request['rds_id']);
            $keylock=get_keylock_string($ret['rds_createdate']);
            $old_pass = create_aes_encrypt($request['rds_oldpass'],$keylock);
            if ($ret != "") {
                if ($old_pass != $ret['rds_pass']) {
                    exit_json_response(load_model('clients/AlirdsModel')->format_ret("-1", '', '原密码错误'));
                } else {
                    //更新密码
                    $newpwd=create_aes_encrypt($request["rds_newpass"],$keylock);
                    $result = load_model('clients/AlirdsModel')->update_rds_pass($newpwd, $request['rds_id']);
                    exit_json_response($result);
                }
            } else {
                //原密码验证不通过
                exit_json_response(load_model('clients/AlirdsModel')->format_ret("-1", '', '原密码错误了'));
            }
        } else {
            $response['data'] = array('rds_id' => $request['_id']);
        }
    }
    
    
    //客户中心-云数据库-连接测试
    function do_rds_test(array & $request, array & $response, array & $app){
        $ret = load_model('basedata/RdsModel')->get_by_id($request['_id']);
        $keylock=get_keylock_string($ret['data']['rds_createdate']);
        $rdspwd= create_aes_decrypt($ret['data']['rds_pass'],$keylock);
        if (isset($ret['data'])){
            $ip = $ret['data']['rds_link'];
            $user =$ret['data']['rds_user'];
            $pwd = $rdspwd;
            $status = load_model('basedata/RdsModel')->rds_net_test($ip,$user,$pwd);
            exit_json_response($status);
        }else{
            exit_json_response(load_model('basedata/RdsModel')->format_ret("-1", '', '查询数据错误'));
        }    
    }
}
