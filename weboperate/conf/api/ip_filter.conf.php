<?php

return array(
    'block_ip' => array(
    ),
    //加强安全可以设置内网白名单
    'allow_ip' => array(
        '127.0.0.1',
        '::1',
//        '192.168.148.137',
//        '218.242.57.204'
    ),
    //服务器ip URL只允许内网IP
    'server_ip' => array(
        '127.0.0.1',
        '::1',
    ),
);
