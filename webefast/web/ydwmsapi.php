<?php
define('RUN_FROM_INDEX',true);
define('APP_MODE_CLI_CHECK',TRUE);

//上线需要配置
$kh_conf = array(
    //customerid
	 '201765001'=>'2061',//上海力弘
	 //'EFAST001'=>'2061',

);


$_REQUEST['app_act']="wms/wmsapi/ydwms_api";

include dirname(dirname(dirname(__FILE__))).'/boot/req_init.php';

$context->fire_request_handle();





