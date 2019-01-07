<?php

$base_url		=	'';
$pub_url		=	'/webpub/';

$memcache_host=array("192.168.176.32","192.168.176.33");

/////////////////////////////////////////////
//include ROOT_PATH.'app_conf.php';

// database server type
if (!isset($db_server)) $db_server = "mysql";

// database host
if (!isset($db_host)) $db_host   = "127.0.0.1";

// database name
if (!isset($db_name)) $db_name   = "efast365_portals";
//if (!isset($db_name)) $db_name   = "fastapp_dev";
// database username
if (!isset($db_user)) $db_user   = "root";

// database password
if (!isset($db_pass)) $db_pass   = "root";


/**
 * 设置日志文件目录，默认为应用的logs目录，也可设置在其他目录，如 $log_path="/var/log/fastshop";
 * 必须设置$log_path目录为可写，特别在*ix系统中.
 */
//$log_path	= '/var/log/fastshop/';
$log_split	= false;

$charset	= 'UTF-8';
$php_ext	= 'php';

$common_http_url = '/efast/webpub/';

//支持的model子目录
$model_sub_dirs = '';

$appid=5;
//bsportal用户机构数据下载地址
$PortalService = "";
//附件上传路径
$file_upload_path = ROOT_PATH.CTX()->app_name.'/web/uploads/';
//图片查看地址
$img_show_path = "http://localhost:8080/fastapp/weboperate/web/uploads/";

//企业SESSION刷新回调地址
$jushita_backurl = '';

//RTX推送消息地址
$rtx_send_url = '';
//efast5跳转地址
$efast_redurl ='';
//在线订购客户扫描件地址
$orderurl ='';

//上传设置客户反馈图片上传配置
$xqfkimg=array(
    'arrType'=>array('image/jpg','image/gif','image/png','image/bmp','image/pjpeg','image/jpeg'),
    'max_size'=>5242880,
    'upfile'=>'uploads',
);


