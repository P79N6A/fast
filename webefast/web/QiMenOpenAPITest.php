<?php
ini_set('date.timezone','Asia/Shanghai');
header("Content-type: text/html; charset=utf-8");
error_reporting(E_ERROR);


//奇门ERP正向接口测试
define('ROOT_PATH', str_replace('webefast/web/QiMenOpenAPITest.php', '', str_replace('\\', '/', __FILE__)));
include dirname(dirname(dirname(__FILE__))).'/boot/req_init.php';
include(ROOT_PATH . 'lib/apiclient/QmErpClient.php');

$_client = new QmErpClient();
$response = $_client->get_item_inventory();

echo '<pre>';
var_dump($response);die;


/*
//奇门ERP反向接口
define('ROOT_PATH', str_replace('webefast/web/QiMenOpenAPITest.php', '', str_replace('\\', '/', __FILE__)));
include dirname(dirname(dirname(__FILE__))).'/boot/req_init.php';
include(ROOT_PATH . 'lib/apiclient/QmCloudClient.php');

$config = array('api_key' =>'23316736', 'api_secret' =>'5feb28f6a28613ace02fe261c6b6c294', 'api_url' =>'http://qimen.api.taobao.com/router/qmtest', 'target_key' =>'23300032');
$_client = new QmCloudClient($config);
$response = $_client->execute('taobao.oms.item.inventory.get', array());

echo '<pre>';
var_dump($response);die;
*/

/*
//奇门OPENAPI接口测试
//API接口地址
$url = 'http://qimen.api.taobao.com/router/qmtest';
//系统级参数
$appKey = '23300032';
$appSecret = 'fc0c155345cf996ba9257bc7bd877770';
$format = 'xml';
$targetAppKey = '23316736';

define('ROOT_PATH', str_replace('webefast/web/QiMenOpenAPITest.php', '', str_replace('\\', '/', __FILE__)));
include(ROOT_PATH . '/lib/apiclient/taobao/TopSdk.php');
//include(ROOT_PATH . '/lib/apiclient/taobao/QimenCloud/QimenCloudClient.php');
include(ROOT_PATH . '/lib/apiclient/taobao/QimenCloud/top/request/PrmGoodsListRequest.php');

$_client = new QimenCloudClient($url, $appKey, $appSecret, $format, $targetAppKey);

$_request = new PrmGoodsListRequest();
$_request->setPage('1');
$_request->setPageSize('100');

$response = $_client->execute($_request);

echo '<pre>';
var_dump($response);die;*/
