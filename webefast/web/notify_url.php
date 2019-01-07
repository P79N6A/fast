<?php

define('RUN_FROM_INDEX', true);
define('APP_MODE_CLI_CHECK', TRUE);
$_REQUEST['app_act'] = "fx/account/pay_return";
include dirname(dirname(dirname(__FILE__))) . '/boot/req_init.php';
$context->fire_request_handle();