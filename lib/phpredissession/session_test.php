<?php
/**
 * 模拟登陆时判断是否已经登陆，如果之前已经登陆，则redis缓存中的"sessionid".md5($username)的
 * value值(为session_id())与当前登陆的不一致，则返回false，需要重新登陆
 *
 */
require './RedisSession.php';
//设置缓存服务器地址、端口、密码
//$_SESSION值失效时间默认为php.ini中设置的时间
$cache_servers_setting = array('addr'=>'192.168.164.201',
    'port'=>6378,
    'auth'=>'3C2471E2B1222627416336D21C84FF22');
$redis_session = new RedisSession($cache_servers_setting,3600); //$_SESSION值失效时间
echo session_id();
echo '</br>';
$username = 'xiaoli';
echo md5($username).'</br>';

if($redis_session->get_session_state($username)){
    echo "session value of shopping_cart ".$redis_session->get_usersession('shopping_cart')."</br>";
    echo "session value of user ".$redis_session->get_usersession('username')."</br>";
} else {
    echo "需要登陆"."</br>"."</br>";
    //重新设置状态和相应的session值
    $redis_session->set_usersession($username,array('shopping_cart'=>'3件'));
    $redis_session->set_session_state($username,1000);
    echo "重新登录成功"."</br>";
}
