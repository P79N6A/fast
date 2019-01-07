<?php
include_once 'req_def.php';
if (file_exists(ROOT_PATH.'vendor/autoload.php')) @require 'vendor/autoload.php';
$context->prepare_request_handle();
define('APP_PATH', ROOT_PATH.$context->app_name.'/');

// register tool,filter,render;init app run mode and const.
if (file_exists(ROOT_PATH . $context->app_name . '/boot/app_reg.php')) // webapp private register
	include ROOT_PATH . $context->app_name . '/boot/app_reg.php';
else
	include ROOT_PATH . 'boot/req_reg.php';

if (! defined('DEBUG'))
	define('DEBUG', false);
if (! defined('RUN_SAFE'))
	define('RUN_SAFE', true);
if (! defined('RUN_FAST'))
	define('RUN_FAST', false);
$app_debug = defined('DEBUG') && DEBUG;
if (! defined('RUN_FAST') || ! RUN_FAST) {
	date_default_timezone_set('Asia/Shanghai'); // 设置时区
	if (DEBUG) {
		@ini_set('log_errors', 1);
		@ini_set('display_errors', 1);
		@ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	} else {
		@ini_set('log_errors', 1);
		@ini_set('display_errors', 0);
        @ini_set('display_startup_errors', 0);
		error_reporting(E_ALL & ~ (E_STRICT | E_NOTICE | E_WARNING));
	}
}

PluginUtil::handle_plugins();
// app common init
$context->do_app_init();
