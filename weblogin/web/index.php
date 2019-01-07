<?php
define('RUN_FROM_INDEX',true);
include dirname(dirname(dirname(__FILE__))).'/boot/req_init.php';
$context->fire_request_handle();
