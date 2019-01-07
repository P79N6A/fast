<?php
define('SDM_DATE_FORMAT', 'Y-m-d');
define('SDM_TIME_FORMAT', 'Y-m-d H:i:s');
define('SDM_TIME', time());
define('SDM_DATE', strtotime(date(SDM_DATE_FORMAT, SDM_TIME)));
define('E3_DATE_FORMAT', 'Y-m-d');
define('E3_TIME_FORMAT', 'Y-m-d H:i:s');
define('E3_TIME', time());
define('E3_DATE', SDM_DATE);

function app_init(){
	//add your app init code
	require_lang('general');
	require_lib('util/common_util,util/web_util', true);
	
	app_init_set_debug_status();
        session_cache('get');
}