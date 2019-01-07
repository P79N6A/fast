<?php

header("Content-Type: text/html; charset=utf8");
date_default_timezone_set('Asia/Shanghai');

//数据库查询
function get_sql_data($sql) {
    $mysqli = new mysqli(
        'jconncccwmh5v.mysql.rds.aliyuncs.com',        //dbhost
        'jusrqe3kdssa',               //dbuser
        'XN47504969bs',               //dbpasswd
        'osp'       //dbname
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
        'jconncccwmh5v.mysql.rds.aliyuncs.com',        //dbhost
        'jusrqe3kdssa',               //dbuser
        'XN47504969bs',               //dbpasswd
        'osp'       //dbname
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
    $keydate = date('Y-m-d',  strtotime($keydate));
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

//解密VM档案
function show_vminfo(){
    $strSQL = "select kh_id,ali_outip,ali_pass,ali_root,ali_createdate from osp_aliyun_host"; 
    $host_pass = get_sql_data($strSQL);
    foreach ($host_pass as $value) {
        $keylock = get_keylock_string($value['ali_createdate']);
        $webpass = create_aes_decrypt($value['ali_pass'],$keylock);
        $rootpass = create_aes_decrypt($value['ali_root'],$keylock);
        
        $strSQL="insert into temp_vminfo (khid,vmip,vmrootpass,vmwebpass) values('".$value['kh_id']."','".$value['ali_outip']."','".$rootpass."','".$webpass."')";
        get_query($strSQL);
    }
}

show_vminfo();