<?php

$base_url		=	'';
$pub_url		=	'/webpub/';

$memcache_host=array("192.168.176.32","192.168.176.33");

/////////////////////////////////////////////
//include ROOT_PATH.'app_conf.php';

// database server type
if (!isset($db_server)) $db_server = "mysql";

// database host
if (!isset($db_host)) $db_host   = "192.168.150.93";

// database name
if (!isset($db_name)) $db_name   = "fastapp_dev_test";

// database username
if (!isset($db_user)) $db_user   = "osauser";

// database password
if (!isset($db_pass)) $db_pass   = "osauser";


/**
 * 设置日志文件目录，默认为应用的logs目录，也可设置在其他目录，如 $log_path="/var/log/fastshop";
 * 必须设置$log_path目录为可写，特别在*ix系统中.
 */
//$log_path	= '/var/log/fastshop/';
$log_split	= false;

$charset	= 'UTF-8';
$php_ext	= 'php';


//支持的model子目录
$model_sub_dirs = '';

$priv_type = 1;

//用户邮件激活地址
$mailserpath = "";

//用户扫描件上传设置
$licenceimg=array(
    'arrType'=>array('image/jpg','image/gif','image/png','image/bmp','image/pjpeg','image/jpeg'),
    'max_size'=>5242880,
    'upfile'=>'licenceimg',
    );