<?php

define('RUN_FROM_INDEX', true);
define('APP_MODE_CLI_CHECK', TRUE);
$_REQUEST['app_act'] = "value/server_order/skip_new_url";
include dirname(dirname(dirname(__FILE__))) . '/boot/req_init.php';
$context->fire_request_handle();


