<?php
/**
 *  模拟设置session在缓存中的值和状态
 */
require './RedisSession.php';
//设置缓存服务器地址、端口、密码
//$_SESSION值失效时间默认为php.ini中设置的时间
$cache_servers_setting = array('addr'=>'192.168.164.201',
    'port'=>6378,
    'auth'=>'3C2471E2B1222627416336D21C84FF22');
$redis_session = new RedisSession($cache_servers_setting,3600);
echo session_id();
echo '</br>';
$username = "xiaoli";
$redis_session->set_usersession($username,array('shopping_cart'=>'2件'));
$redis_session->set_session_state($username,1000); //设置用户名为xiaozhang的session状态，过期时间为1000秒