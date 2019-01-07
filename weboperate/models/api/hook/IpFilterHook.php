<?php

class IpFilterHook {
	function handle() {
		$cfg = require_conf('api/ip_filter');
                //白名单可以查询数据库
		$ip = get_client_ip();
		if (in_array($ip, $cfg['block_ip']) || !in_array($ip, $cfg['allow_ip'])) {
			throw new ApiException('your ip is blocked', 'ip_blocked');
		}
                
		$server_id = $_SERVER['HTTP_HOST'];
                
         if (!in_array($server_id, $cfg['server_ip'])) {
			throw new ApiException('request url is error', 'ip_error');
		} 
                
		return array('status'=>1);
	}
}