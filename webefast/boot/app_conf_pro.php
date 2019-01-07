<?php
$base_url		=	'';
$pub_url		=	'webpub/';

$memcache_host=array("192.168.176.32","192.168.176.33");

/////////////////////////////////////////////
#include ROOT_PATH.'app_conf.php';

//$common_http_url = '/webpub/';

// database server type
//if (!isset($db_server)) $db_server = "mysql";
//
//// database host
if (!isset($db_host)) $db_host   = "";
//
//// database name
if (!isset($db_name)) $db_name   = "";
//
//// database username
if (!isset($db_user)) $db_user   = "";
//
//// database password

if (!isset($db_pass)) $db_pass   = "";

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

//efast_api taobao接口代理模式 设置
$taobao_trade_gateway_url = 'http://223.4.54.191/taobao_trans_req.php?';

//TAOBAO 订单下载要从哪个时间点开始
define('EFAST_API_TRADE_DOWNLOAD_MIN_CREATED','2010-11-11 00:00:00');

//测试app_key
$app_key = '12651526';
$app_secret = '11b9128693bfb83d095ad559f98f2b07';
$app_session = '6100817179921acb5271ad6651f0afdc7f5c44a157ee13358123788';
$app_nick = 'shopping_attb';

$cloud_url = 'http://fuwu.taobao.com/using/serv_using.htm?service_code=FW_GOODS-1960308&item_code=FW_GOODS-1960308-1';

$app_key = '12651526';
$app_secret = '11b9128693bfb83d095ad559f98f2b07';
$app_session = '6101f221baae395d0fc1d3fb161b9d1e7c67caccae069f61842158565';
$app_nick = 'monton脉腾专卖店';

$cloud_url = "http://efast365.yishangonline.com/";

/**
 * API 开发模式：1生产模式 2中转模式 3沙箱模式
 */
$api_dev_mode = 2;


$default_product_id = 21;//产品id


/**
 * 登陆服务入口地址
 */
$login_server = 'http://login.yishangonline.com/weblogin/web/?app_act=index/login';

/**
 * 资源文件路径
 */
$common_http_url = '/webpub/';

/**
 * API 开发模式：1生产模式 2中转模式 3沙箱模式
 */
$api_dev_mode = 1;

/**
 * 是否将SESSION保存到缓存中
 * 可选值:true、false
 * 如果为true，需要在conf/cache.conf.php中配置session的存储方式，目前只支持memcache
 * -->只有设为true，才支持“控制同一个用户不能重复登录”的效果
 */
$store_session_in_cache = false;