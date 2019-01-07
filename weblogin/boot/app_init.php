<?php
function app_init(){
	//add your app init code
	require_lang('general');
	require_lib('util/common_util,util/web_util,util/bui_util', true);
	app_init_set_debug_status();
}