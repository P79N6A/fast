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
//if (!isset($db_name)) $db_name   = "fastapp_dev";
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

$common_http_url = 'webpub/';

//支持的model子目录
$model_sub_dirs = '';

$appid=5;
//bsportal用户机构数据下载地址
$PortalService = "http://portal.baison.com.cn:81/erpservice-portlet/getOrgAndUser1";
//附件上传路径
$file_upload_path = ROOT_PATH.CTX()->app_name.'/web/uploads/';
//图片查看地址
$img_show_path = "http://localhost:8080/fastapp/weboperate/web/uploads/";

//企业SESSION刷新回调地址
//$jushita_backurl = 'http://cloud.sqzw.com/?app_act=api/api_rds/session';
$jushita_backurl = 'http://operate.yishangonline.com:81/osp_test/?app_act=sys/session/save_session';

//RTX推送消息地址
$rtx_send_url = 'http://develop.baison.com.cn/ys.web/sendmsg.ashx';
//efast5跳转地址
$efast_redurl ='http://efast.yishangwangluo.com/webefast/web/';
//在线订购客户扫描件地址
$orderurl ='http://localhost:8080/fastapp/weborder/web/';

//上传设置客户反馈图片上传配置
$xqfkimg=array(
    'arrType'=>array('image/jpg','image/gif','image/png','image/bmp','image/pjpeg','image/jpeg'),
    'max_size'=>5242880,
    'upfile'=>'uploads',
);


