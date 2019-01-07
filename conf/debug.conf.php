<?php

// 当 debug=TRUE时，如果开启ip_range、user_range检查，符合才会打开debug模式，否则还是正常模式
return array(
	'debug'=>false,	// TRUE or FALSE
	'enable_ip_range'	=> 0,	// 是否开启ip限制		可选值0,1 为1时在 ip_range 范围里的ip地址访问时才会开启debug
	'enable_user_range'	=> 0,	// 是否开启用户限制		可选值0,1 为1时在 user_range 范围里的用户访问时才会开启debug
	'ip_range'		=>array(
		'127.0.0.1',
	),
	'user_range'	=>array(	// 员工id
		1,
	),
);
