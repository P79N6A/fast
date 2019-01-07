<?php

$base_url		=	'';
$pub_url		=	'/webpub/';

$memcache_host=array("192.168.176.32","192.168.176.33");

// database server type
$db_server = "mysql";

// database host
$db_host   = "localhost";

// database name
$db_name   = "efast";

// database username
$db_user   = "root";

// database password
$db_pass   = "";

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