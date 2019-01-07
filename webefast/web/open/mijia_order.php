<?php

define('RUN_FROM_INDEX',true);
define('APP_MODE_CLI_CHECK',TRUE);

$_REQUEST['app_act']="oms/api_order/create_mijia_order";
include dirname(dirname(dirname(dirname(__FILE__)))).'/boot/req_init.php';
$context->fire_request_handle();