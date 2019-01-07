<?php
define('RUN_FROM_INDEX',true);
define('APP_MODE_CLI_CHECK',TRUE);



$_REQUEST['app_act']="wms/wmsapi/qimei_api";

include dirname(dirname(dirname(dirname(__FILE__)))).'/boot/req_init.php';

$context->fire_request_handle();







