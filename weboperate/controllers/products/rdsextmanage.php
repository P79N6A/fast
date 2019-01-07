<?php

/*
 * 系统产品档案-产品RDS扩展管理
 */
require_lib ( 'util/web_util', true );
require_lib("keylock_util");
class Rdsextmanage {
    
    //产品RDS扩展管理列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑产品RDS扩展管理显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
            $title_arr = array('edit'=>'编辑RDS扩展管理', 'add'=>'新建RDS扩展管理');
            $app['title'] = $title_arr[$app['scene']];
            $ret = load_model('products/RdsextmanageModel')->get_by_id($request['_id']);
            $response['data'] = $ret['data'];   
    }
    
    //编辑产品RDS扩展管理信息数据处理。
    function do_edit(array & $request, array & $response, array & $app) {

    }
    //添加产品RDS扩展管理信息数据处理。    
    function do_add(array & $request, array & $response, array & $app) {

    }
    
    //导入rds功能列表
    function  do_importrds_list(array & $request, array & $response, array & $app) {
        
    }
    
    function create_conf(array & $request, array & $response, array & $app) {
        $app['fmt'] = "json";
        $response = load_model('products/dbextmanageModel')->create_conf();
    }
    
    //获取产品信息
    function get_chanpin(array & $request, array & $response, array & $app) {
        $ret = load_model('products/RdsextmanageModel')->get_chanpin();
        exit_json_response($ret);
    }
    
    //获取产品信息
    function get_dbtype(array & $request, array & $response, array & $app) {
        $ret = load_model('products/RdsextmanageModel')->get_dbtype();
        exit_json_response($ret);
    }
    
    //导入RDS数据
    function do_importrds (array & $request, array & $response, array & $app) {
        if (isset($request['rdsdata'])) {
            //$request['rdsdata'] = json_decode($request['rdsdata'], true);
            $ret = load_model('products/RdsextmanageModel')->import_rdslist($request);
            exit_json_response($ret);
        }else{
            
        }
    }
    
    //数据库管理列表
    function do_rdsdb_list(array & $request, array & $response, array & $app) {
        
    }
    
    //删除数据库明细
    function do_delete(array & $request, array & $response, array & $app) {
        //执行删除物理数据库操作
        //删除数据库记录，更新数量
        $ret = load_model('products/RdsextmanageModel')->delete_db($request['rem_db_id'],$request['rem_db_is_bindkh'],$request['rem_db_pid']);
        exit_json_response($ret);
    }
    
    //选择版本生成数据库
    function do_batch_createdb(array & $request, array & $response, array & $app) {
        if(isset($request["do"])){
            //标识post请求
            $rem_cp_id=$request['rem_cp_id'];  //产品
            $rem_db_version=$request['rem_db_version'];  //版本ID
            $rem_num=$request['rem_num'];  //生成数量
            $rem_remark=$request['rem_remark'];  //备注
            $rem_list=json_decode($request['hdrdslist'], true);//所选择的RDS
            if(!isset($rem_list)){
                //表示全部
                $rem_all=load_model('products/RdsextmanageModel')->get_all(array('rem_cp_id'=>$rem_cp_id));
                $rem_list=$rem_all['data'];
            }
            foreach ($rem_list as $val){
                //获取RDS和对应产品应用参数
                $rdsinfo= load_model('products/RdsextmanageModel')->getrdsinfo($val['rem_rds_id']);
                $rdskeyinfo=load_model('products/RdsextmanageModel')->getrdskeyinfo($val['rem_bindcpkey']);
                //构造创库参数
                $params=array(
                    'app_key'=>$rdskeyinfo['app_key'],
                    'app_secret'=>$rdskeyinfo['app_secret'],
                    'instance_name'=>$rdsinfo['rds_dbname'],
                    'session'=>$rdskeyinfo['access_token'],
                );
                
                //首先判断系统管理库sysdb是否存在
                $keylock=get_keylock_string($rdsinfo['rds_createdate']);
                $rdspwd= create_aes_decrypt($rdsinfo['rds_pass'],$keylock);
                $rdsconinfo=array('link'=>$rdsinfo['rds_link'],'user'=>$rdsinfo['rds_user'],'pwd'=>$rdspwd);
                $constate=load_model('products/RdsextmanageModel')->rds_db_exists($rdsconinfo);
                if($constate==false){
                    //不存在先创建sysdb
                    $sydbname="sysdb";
                    $params['db_name']=$sydbname;
                    //生成sysdb数据库
                    $createstate_sysdb=load_model('products/RdsextmanageModel')->install_rds($params);
                    if($createstate_sysdb){
                        //系统管理库,需要执行默认脚本
                        $init_sysdb=array(
                            'db_name'=>'sysdb',
                            'rds_host'=>$rdsinfo['rds_link'],
                            'rds_account'=>$rdsinfo['rds_user'],
                            'rds_password'=>$rdspwd,
                        );
                        $sql_path = ROOT_PATH . 'install' . DIRECTORY_SEPARATOR . 'efast5' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'sysdb.sql';
                        load_model('products/RdsextmanageModel')->init_sql($init_sysdb,$sql_path);
                    } 
                }

                for ($i = 1; $i <= $rem_num; $i++) {
                    //$dbname=uniqid();
                    $dbname = 'efast5_' . uniqid();
                    $params['db_name']=$dbname;
                    //生成数据库
                    $createstate=load_model('products/RdsextmanageModel')->install_rds($params);
                    //插入数据库明细记录
                    if($createstate){
                        $rdsdb=array(
                            'rem_db_pid'=>$val['rem_rds_id'],
                            'rem_db_name'=>$dbname,
                            'rem_db_version'=>$rem_db_version,
                            'rem_db_is_bindkh'=>'0',
                            'rem_try_kh'=>'0',
                            'rem_db_bindtype'=>'0',
                            'rem_db_khid'=>'',
                            'rem_db_sys_version'=>$request['rem_db_sys_version'],
                            'rem_db_createdate'=>date('Y-m-d H:i:s'),
                            'rem_db_bz'=>$rem_remark,
                        );
                        load_model('products/RdsextmanageModel')->insert_db($rdsdb);
                    }
                }
                //更新rds扩展主表数据库数量
                load_model('products/RdsextmanageModel')->updatedbnum($val['rem_rds_id']);
            }
            exit_json_response(load_model('products/RdsextmanageModel')->format_ret("1", '', '创建完成')); 
        }else{
             
        }
    }

    
    function do_add_rds_db(array & $request, array & $response, array & $app) {
          if(isset($request["do"])){
                        $rdsdb=array(
                            'rem_db_pid'=>$request['rem_rds_id'],
                            'rem_db_name'=>$request['rem_db_name'],
                            'rem_db_version'=>$request['rem_db_version'],
                            'rem_db_is_bindkh'=>'0',
                            'rem_db_version_ip'=>isset($request['rem_db_version_ip'])?$request['rem_db_version_ip']:'',
                            'rem_try_kh'=>'0',
                            'rem_db_bindtype'=>'0',
                            'rem_db_khid'=>'',
                            'rem_db_sys_version'=>$request['rem_db_sys_version'],
                            'rem_db_createdate'=>date('Y-m-d H:i:s'),
                            'rem_db_bz'=>$request['rem_remark'],
                        );
                    load_model('products/RdsextmanageModel')->insert_db($rdsdb);
        
      
                //更新rds扩展主表数据库数量
                load_model('products/RdsextmanageModel')->updatedbnum($request['rem_rds_id']);
               exit_json_response(load_model('products/RdsextmanageModel')->format_ret("1", '', '添加完成')); 
          }else{
              $ret =  load_model('products/RdsextmanageModel')->get_row(array('rem_rds_id'=>$request['_id']));
              $response['rem_cp_id'] = $ret['data']['rem_cp_id'];  
              $response['ip_data'] = load_model('products/RdsextmanageModel')->get_row(array('rem_rds_id'=>$request['_id']));
              $app['scene'] ='add';

          }
    }
    
       function  get_pt_key(array & $request, array & $response, array & $app) {
            $response = load_model('products/RdsextmanageModel')->get_cp_key($request['cp_id'],$request['pt_id']);
       }
       
       function get_ip_by_version(array & $request, array & $response, array & $app){
           $version = $request['version'];
           $response = load_model('products/VhostModel')->get_version_ip($version);
           
       }


       //生成随机字串,可生成校验码, 默认长度4位,0 字母和数字混合,1 数字,-1 字母
        function rand_str($len = 4, $only_digit = 0) {
            switch ($only_digit) {
                    case -1:
                            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                            break;
                    case 1:
                            $chars = str_repeat('0123456789', 3);
                            break;
                    default :
                            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'; //rm 0,o
                            break;
            }
            if ($len > 10) $chars = $only_digit == 0 ? str_repeat($chars, $len) : str_repeat($chars, 5); //位数过长重复字符串一定次数
            $chars = str_shuffle($chars);
            return substr($chars, 0, $len);
    }
    
    function sys_rds_data(array & $request, array & $response, array & $app) {
        $response = load_model('basedata/RdsDataModel')->update_rds($request['rds_id']);
    }
}