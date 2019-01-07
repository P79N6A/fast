<?php
define('WMS_DATE_FORMAT', 'Y-m-d');
define('WMS_TIME_FORMAT', 'Y-m-d H:i:s');
define('WMS_TIME', time());
define('WMS_DATE', strtotime(date(WMS_DATE_FORMAT, WMS_TIME)));

function app_init(){
	//add your app init code
	
	// for test
	$_SESSION['user_id'] = '1';
	$_SESSION['username'] = 'admin';
	$_SESSION['role_name'] = '系统管理员';
}	