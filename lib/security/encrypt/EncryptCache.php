<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EncrypCache
 *
 * @author wq
 */
class EncryptCache implements iTCache {

    function __construct() {
        if (empty(CTX()->pcache)) {
            CTX()->pcache = new FileCache();
            //暂时不开启app目录缓存
            CTX()->pcache->set_app_path();
        }
        $this->cache = &CTX()->pcache;
    }

    //获取缓存
    public function getCache($key) {
        $value = $this->cache->get($key);
        return unserialize($value);
    }

    //更新缓存
    public function setCache($key, $var) {
        $_c_time = 120;
        $value = serialize($var);
        $this->cache->set($key, $value, $_c_time);
    }

}
