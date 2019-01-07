<?php

header("Content-Type: text/html; charset=utf8");
date_default_timezone_set('Asia/Shanghai');

//数据库查询
function get_sql_data($sql) {
    $mysqli = new mysqli(
        '192.168.150.93',        //dbhost
        'osauser',               //dbuser
        'osauser',               //dbpasswd
        'fastapp_dev_test'       //dbname
    );
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        die("数据库连接失败" . $mysqli->connect_error);
    } else {
        $res = $mysqli->query($sql, MYSQLI_USE_RESULT);
        if ($res !== false) {
            $arr = array();
            while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        } else {
            die("数据库查询错误");
        }
    }
    return $arr;
}
//数据库更新
function get_query($sql){
    $mysqli = new mysqli(
        '192.168.150.93',        //dbhost
        'osauser',               //dbuser
        'osauser',               //dbpasswd
        'fastapp_dev_test'       //dbname
    );
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        die("数据库连接失败" . $mysqli->connect_error);
    } else {
        $res = $mysqli->query($sql, MYSQLI_USE_RESULT);
        return $res;
    }  
}


/**
 * 获取有效期内的密钥key
 * @param   date   $keydate   加密日期
 * @access  public
 * @return  string   返回key字符串
 */
function get_keylock_string($keydate){
    if(!$keydate){
        return '';
    }
    //获取key
    $strSQL = "select key_string from osp_keylock where key_startdate<='".$keydate."' and key_enddate>='".$keydate."'";
    $key = get_sql_data($strSQL);
    if(!$key){
        //生成有效期内的key
        $key=uniqid();
        //保存keylock表，默认日期段一个月
        $y = date('Y', strtotime($keydate));
        $m = date('m', strtotime($keydate));
        $mindate = date('Y-m-d', mktime(0, 0, 0, $m, 1, $y));
        $maxdate = date('Y-m-d', mktime(0, 0, 0, $m+1, 1, $y) - 1);
        $strSQL = "insert into osp_keylock (key_startdate,key_enddate,key_string) values ('".$mindate."','".$maxdate."','".$key."')";
        get_query($strSQL);
        return getmd5_tobase64($key);
    }else{
        return getmd5_tobase64($key[0]['key_string']);
    }
}

/**
* 生成mysql加密密码
*/
function create_aes_encrypt($str,$key){
    $strSQL = "select HEX(AES_ENCRYPT('".mysql_escape_string($str)."','".$key."')) as a";
    $enkey = get_sql_data($strSQL);
    return $enkey[0]['a'];
}

/**
* 解密mysql加密密码
*/
function create_aes_decrypt($str,$key){
    $strSQL = "select AES_DECRYPT(UNHEX('".mysql_escape_string($str)."'),'".$key."') as a";
    $enkey = get_sql_data($strSQL);
    return $enkey[0]['a'];
}

/**
* 获取md5加密,base64,二进制数据包组合
*/
function getmd5_tobase64($str){
    return base64_encode(pack("H32",md5($str)));
}

//导入Excel数据（呼叫中心模版）
function import_aliinfo(){
    $strSQL = "select * from temp_aliinfo";
    $aliinfo = get_sql_data($strSQL);
    foreach ($aliinfo as $value) {
        //首先匹配判断客户是否存在
        $strSQL = "select * from osp_kehu where kh_name='".$value['客户名称']."'";
        $khinfo = get_sql_data($strSQL);
        if(!empty($khinfo)){
            $kh_id=$khinfo[0]['kh_id'];
            //判断RDS信息
            if( trim($value['连接地址'])!=''){
                $strSQL="select * from osp_aliyun_rds where rds_link='".trim($value['连接地址'])."'";
                $rdsinfo=get_sql_data($strSQL);
                if(empty($rdsinfo)){
                    //插入rds
                    //生成密码
                    $keylock = get_keylock_string(date('Y-m-d'));
                    $passwd = create_aes_encrypt(trim($value['RDS密码']), $keylock);
                    $rds_createuser='2';
                    $rds_createdate=date('Y-m-d H:i:s');
                    $rds_updateuser='2';
                    $rds_updatedate=date('Y-m-d H:i:s');
                    $strSQL="insert into osp_aliyun_rds(kh_id,rds_user,rds_pass,rds_link,rds_dbname,rds_dbtype,rds_deployment,rds_server_use,rds_createdate,rds_createuser,rds_updatedate,rds_updateuser) "
                            . "values('".$kh_id."','".trim($value['RDS用户名'])."','".$passwd."','".trim($value['连接地址'])."','".trim($value['RDS实例名'])."','1','1','1','".$rds_createdate."','".$rds_createuser."','".$rds_updatedate."','".$rds_updateuser."')";
                    get_query($strSQL);
                }
            }
            //判断VM信息
            $strSQL="select * from osp_aliyun_host where ali_outip='".trim($value['VM连接地址'])."'";
            $vminfo=get_sql_data($strSQL);
            if(empty($vminfo)){
                //插入vm
                //生成密码
                $keylock = get_keylock_string(date('Y-m-d'));
                $rootpasswd = create_aes_encrypt(trim($value['新VM连接的密码root']), $keylock);
                $webpasswd = create_aes_encrypt(trim($value['新VM连接密码efast']), $keylock);
                $ali_createuser='2';
                $ali_createdate=date('Y-m-d H:i:s');
                $ali_updateuser='2';
                $ali_updatedate=date('Y-m-d H:i:s');
                $strSQL="insert into osp_aliyun_host(kh_id,ali_outip,ali_type,ali_user,ali_pass,ali_root,ali_deployment,ali_server_use,ali_operate_system,ali_createdate,ali_createuser,ali_updatedate,ali_updateuser) "
                        . "values('".$kh_id."','".trim($value['VM连接地址'])."','1','efast','".$webpasswd."','".$rootpasswd."','1','1','1','".$ali_createdate."','".$ali_createuser."','".$ali_updatedate."','".$ali_updateuser."')";
                get_query($strSQL);
            }
        }else
        {
            //先插入客户信息
            $kh_code=uniqid();
            $kh_name=$value['客户名称'];
            //匹配区域
            $strSQL = "select * from osp_sell_channel where channel_name like'%".$value['区域']."%'";
            $placeinfo=get_sql_data($strSQL);
            $kh_place=$placeinfo[0]['channel_id'];
            //匹配服务工程师
            $strSQL = "select * from osp_user where user_name='".$value['服务工程师']."'";
            $fwinfo=get_sql_data($strSQL);
            $kh_fwuser=$fwinfo[0]['user_id'];
            //其他信息
            $kh_createuser='2';
            $kh_createdate=date('Y-m-d H:i:s');
            $kh_updateuser='2';
            $kh_updatedate=date('Y-m-d H:i:s');
            $kh_verify_status='1';
            $kh_check_user='2';
            $kh_check_date=date('Y-m-d H:i:s');
            
            $strSQL = "insert into osp_kehu(kh_code,kh_name,kh_place,kh_fwuser,kh_createuser,kh_createdate,kh_updateuser,kh_updatedate,kh_verify_status,kh_check_user,kh_check_date"
                    . ") values('".$kh_code."','".$kh_name."','".$kh_place."','".$kh_fwuser."','".$kh_createuser."','".$kh_createdate."','".$kh_updateuser."','".$kh_updatedate."','".$kh_verify_status."','".$kh_check_user."','".$kh_check_date."')";
            get_query($strSQL);
            $strSQL="SELECT kh_id FROM osp_kehu where kh_name='".$kh_name."'";
            $kh_idinfo=get_sql_data($strSQL);
            $kh_id=$kh_idinfo[0]['kh_id'];
            //判断RDS信息
            if( trim($value['连接地址'])!=''){
                $strSQL="select * from osp_aliyun_rds where rds_link='". trim($value['连接地址'])."'";
                $rdsinfo=get_sql_data($strSQL);
                if(empty($rdsinfo)){
                    //插入rds
                    //生成密码
                    $keylock = get_keylock_string(date('Y-m-d'));
                    $passwd = create_aes_encrypt(trim($value['RDS密码']), $keylock);
                    $rds_createuser='2';
                    $rds_createdate=date('Y-m-d H:i:s');
                    $rds_updateuser='2';
                    $rds_updatedate=date('Y-m-d H:i:s');
                    $strSQL="insert into osp_aliyun_rds(kh_id,rds_user,rds_pass,rds_link,rds_dbname,rds_dbtype,rds_deployment,rds_server_use,rds_createdate,rds_createuser,rds_updatedate,rds_updateuser) "
                            . "values('".$kh_id."','".trim($value['RDS用户名'])."','".$passwd."','".trim($value['连接地址'])."','".trim($value['RDS实例名'])."','1','1','1','".$rds_createdate."','".$rds_createuser."','".$rds_updatedate."','".$rds_updateuser."')";
                    get_query($strSQL);
                }
            }
            //判断VM信息
            $strSQL="select * from osp_aliyun_host where ali_outip='".trim($value['VM连接地址'])."'";
            $vminfo=get_sql_data($strSQL);
            if(empty($vminfo)){
                //插入vm
                //生成密码
                $keylock = get_keylock_string(date('Y-m-d'));
                $rootpasswd = create_aes_encrypt(trim($value['新VM连接的密码root']), $keylock);
                $webpasswd = create_aes_encrypt(trim($value['新VM连接密码efast']), $keylock);
                $ali_createuser='2';
                $ali_createdate=date('Y-m-d H:i:s');
                $ali_updateuser='2';
                $ali_updatedate=date('Y-m-d H:i:s');
                $strSQL="insert into osp_aliyun_host(kh_id,ali_outip,ali_type,ali_user,ali_pass,ali_root,ali_deployment,ali_server_use,ali_operate_system,ali_createdate,ali_createuser,ali_updatedate,ali_updateuser) "
                        . "values('".$kh_id."','".trim($value['VM连接地址'])."','1','efast','".$webpasswd."','".$rootpasswd."','1','1','1','".$ali_createdate."','".$ali_createuser."','".$ali_updatedate."','".$ali_updateuser."')";
                get_query($strSQL);
            }
        }
    }
}

import_aliinfo();