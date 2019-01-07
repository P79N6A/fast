<?php

//namespace session;

class SessionManager {
    function __construct() {
        if (CTX()->get_app_conf('store_session_in_cache') == false) {
        	return ;
        }
        require_lib('session/MemcacheSession');

        $session = new MemcacheSession();
        $session->init();
        
//        $cache_conf = require_conf('cache');
//        if (isset($cache_conf['session'])) {
//            $cfg = $cache_conf['session'];
//            $host = '';
//            if (isset($cfg['hosts']) && is_array($cfg['hosts'])) {
//                foreach ($cfg['host'] as $row) {
//                    $host .= ',tcp://'.$row['host'].':'.$row['port'];
//                }
//                if ($host != '') {
//                    $host = substr($host, 1);
//                }
//            } else {
//                $host = 'tcp://'.$cfg['host'].':'.$cfg['port'];
//            }
//            ini_set("session.save_handler", "memcache");
//            ini_set("session.save_path", $host);
//            
//            
//        } else {
//        	CTX()->log_error('feature store_session_in_cache is enable but session-cache is not config, please check conf/cache.conf.php');
//        }
    }
}