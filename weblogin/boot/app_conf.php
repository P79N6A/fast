<?php

$base_url		=	'';
$pub_url		=	'/efast/webpub/';

$memcache_host=array("192.168.176.32","192.168.176.33");

/////////////////////////////////////////////
#include ROOT_PATH.'app_conf.php';

$common_http_url = '/efast/webpub/';

// database server type
//if (!isset($db_server)) $db_server = "mysql";
//
//// database host
//测试
/*
$db_host   = "jconnceaq7q88.mysql.rds.aliyuncs.com";//jconncccwmh5v.mysql.rds.aliyuncs.com
$db_name   = "osp";
$db_user   = "jusrhjzbexsv";//jusrqe3kdssa
$db_pass   = "YS67886073bs";//XN47504969bs
*/

// 正式

$db_host   = "rds0lqm64797976a00nb.mysql.rds.aliyuncs.com";
$db_name   = "osp";
$db_user   = "jusr26cu8bgx";
$db_pass   = "dsDerlrj3131rfdla";


/*
//// database host
if (!isset($db_host)) $db_host   = "192.168.150.30";

// database name
if (!isset($db_name)) $db_name   = "efast5.0.1";

// database username
if (!isset($db_user)) $db_user   = "efast";

// database password
if (!isset($db_pass)) $db_pass   = "efast";
*/
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

$appid = 1; // 应用编号

$file_upload_path = ROOT_PATH.CTX()->app_name.'/uploads/';

// 业务参数
$default_product = 'efast';	// 默认接入产品

$is_strong_safe = TRUE; //是否强安全模式模式

$is_baota = TRUE;