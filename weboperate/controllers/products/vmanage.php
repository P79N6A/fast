<?php

/*
 * 系统产品档案-产品RDS扩展管理
 */
require_lib ( 'util/web_util', true );
class Vmanage {
    
    //产品RDS扩展管理列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
     
    //导入主机功能列表
    function  do_importhost_list(array & $request, array & $response, array & $app) {
        
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
        if (isset($request['hostdata'])) {
            $ret = load_model('products/VmanageModel')->import_vmhost($request);
            exit_json_response($ret);
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
}