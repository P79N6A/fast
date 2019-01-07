<?php

set_time_limit(0);
date_default_timezone_set('Asia/Shanghai'); // 设置时区
define('ROOT_WEB_PATH', dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR);
$web_path = ROOT_WEB_PATH.'webefast'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR;
ini_set("soap.wsdl_cache_enabled", 0); //关闭缓存   
require_once(ROOT_WEB_PATH."lib/nusoap/nusoap.php"); //加载nusoap文件   



$server = new soap_server();
$server->configureWSDL('bais',false,false,'document', 'http://schemas.xmlsoap.org/soap/http', false); //设定服务的名称，使用的wsdl来通信，如果不适用wsdl将会更简单，网上有很多的例子    nusoasp
//$config = require $web_path.'wms/lifeng/config.php';
//注册接口

$webservice_type = 'document'; //false 
//foreach ($config as $key => $val) {
    //$method = str_replace('.', '_', $key);
    //$method = $key;
    $documentation = '';
//    //需要调整
   $server->register('GetESBDataPacketsByStream',array('PacketMarked'=>'xsd:string','Packet'=>"xsd:string",), array('GetESBDataPacketsByStreamResult'=>'xsd:string'), 'bais', false, $webservice_type, 'literal', 'efast365');
    //          register($name,$in=array(),$out=array(),$namespace=false,$soapaction=false,$style=false,$use=false,$documentation='',$encodingStyle=''){
//}

//class..method
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
//include_once $web_path.'wms/lifeng/webserver_func.php';
define('RUN_FROM_INDEX', true);
define('APP_MODE_CLI_CHECK', TRUE);
include ROOT_WEB_PATH . '/boot/req_init.php';
       $filepath = '/www/webroot/efast365/webefast/logs/sss_api_'.date('Ymd').'.log';

			      file_put_contents($filepath, "_REQUEST:".var_export($_REQUEST,TRUE), FILE_APPEND);
				  	      file_put_contents($filepath, "HTTP_RAW_POST_DATA:".var_export($HTTP_RAW_POST_DATA,TRUE), FILE_APPEND);

$server->service($HTTP_RAW_POST_DATA);

//var_dump($server);die;

function GetESBDataPacketsByStream($PacketMarked,$Packet) {
    //安全验证  配置帐号密码在 user_cconfig
//    if (!check_login()) {
//        $ret = array("Msgty" => -1, "message" => "2017-08-10安全验证失败");
//        return json_encode($ret);
//    }

    $_REQUEST["app_act"] = "wms/wmsapi/lifeng";

    $_REQUEST["PacketMarked"] = $PacketMarked;
	    $_REQUEST["Packet"] = $Packet;
    CTX()->prepare_request_handle();
    CTX()->fire_request_handle();
         $filepath = '/www/webroot/efast365/webefast/logs/sss_api_'.date('Ymd').'.log';
             file_put_contents($filepath, "time:".date('Y-m-d H:i:s'), FILE_APPEND);
             file_put_contents($filepath, "PacketMarked:".var_export($PacketMarked,TRUE), FILE_APPEND);
     file_put_contents($filepath, "Packet:".var_export($Packet,TRUE), FILE_APPEND);
   $response =  CTX()->response;
  file_put_contents($filepath, "response:".var_export($response ,TRUE), FILE_APPEND);
         return    array('GetESBDataPacketsByStreamResult'=>$response);

//    global $config;
//    global $webservice_type;
//    if ($webservice_type == 'document') {
//        $response_key = $config[$_REQUEST["method"]]['response'];
//        //转换成JSON字符串
//        return array($response_key => CTX()->response);
//    } else {
     
    //}
}

function check_login() {
    $user_config = require 'webserver/lib/user_cconfig.php';

    if ($_SERVER['PHP_AUTH_USER'] != $user_config['user'] || $_SERVER['PHP_AUTH_PW'] != $user_config['password']) {
        return false;
    }
    return TRUE;
}
