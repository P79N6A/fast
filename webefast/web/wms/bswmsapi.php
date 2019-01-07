<?php
define('RUN_FROM_INDEX',true);
define('APP_MODE_CLI_CHECK',TRUE);

//上线需要配置
$kh_conf = array(
    //partnerId
    'WDGJ-GZYY'=>'1255',//对应我们系统ID
);


$_REQUEST['app_act']="wms/wmsapi/bswms_api";

include dirname(dirname(dirname(dirname(__FILE__)))).'/boot/req_init.php';
$context->fire_request_handle();







