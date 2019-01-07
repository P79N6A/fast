<?php

require_once ROOT_PATH . 'boot/req_inc.php';

/**
 * cache基类
 *
 * @author zengjf
 */
abstract class Cache implements ICache {

    /**
     *
     * @var string key的前缀
     */
    public $prefix = '';
    public $default_ttl = 300; //default ttl,$ttl==NULL , use the value

    function __construct() {
        $t = $GLOBALS['context']->get_app_conf('default_ttl');
        if ($t)
            $this->default_ttl = $t;
    }

}

/**
 * 空cache类，即数据没有缓存，一般用于调试，测试场景。
 *
 * @author zengjf
 */
class EmptyCache extends Cache implements IRequestTool {

    static function register($prop) {
        $GLOBALS['context']->log_debug('EmptyCache create');
        return new EmptyCache();
    }

    function &get($key) {
        $r = null;
        return $r;
    }

    function set($key, $value, $ttl = null) {
        
    }

    function delete($key) {
        
    }

    function clear() {
        
    }

}

/**
 * 文件缓存
 *
 * @author zengjf
 */
class FileCache extends Cache implements IRequestTool {

    private $cache = 'cache/data';
    private $cache_path;
    private $enable = false;

    function __construct($is_saas = true) {
        global $context;
        parent:: __construct();
        $this->cache_path = ROOT_PATH . "{$GLOBALS['context']->app_name}/{$this->cache}/";
        if ($is_saas == true) {
            if (defined('RUN_SAAS') && RUN_SAAS) {
                //$kh_id = $GLOBALS['context']->get_session("kh_id");
                $saas_key = CTX()->saas->get_saas_key();

                if (!empty($saas_key)) {
                    $this->cache_path = $this->cache_path . $saas_key . '/';
                }
            }
        } else {
            $this->prefix = '';
        }

        if (!file_exists($this->cache_path)) {
            if ($context->is_debug())
                $context->log_debug('data cache mkdir:' . $this->cache_path);
            mkdir($this->cache_path, 0777, true);
        }
        $this->enable = file_exists($this->cache_path);
    }

    static function register($prop) {
        global $context;
        if ($context->is_debug())
            $context->log_debug('FileCache create');
        return new FileCache();
    }

    function set_app_path($is_saas = true) {
        if ($is_saas) {
            $saas_key = CTX()->saas->get_saas_key();
            $this->cache_path = ROOT_PATH . "{$GLOBALS['context']->app_name}/data/cache/{$saas_key}/";
        } else {
            $this->cache_path = ROOT_PATH . "{$GLOBALS['context']->app_name}/data/cache/";
        }
        if (!file_exists($this->cache_path)) {
            mkdir($this->cache_path, 0777, true);
        }
    }

    function &get($key) {
        if (!$this->enable) {
            $r = null;
            return $r;
        };
        $file_path = $this->cache_path . $this->prefix . $key . '.php';
        if (DIRECTORY_SEPARATOR === '\\')
            $file_path = str_replace('/', DIRECTORY_SEPARATOR, $file_path);
        if (!file_exists($file_path)) {
            $r = null;
            return $r;
        }

        $data = null;
        $_the_file_ttl = null;
        include $file_path;
        if ($data !== null && $_the_file_ttl && $_the_file_ttl > time())
            return $data;
        
       if(file_exists($file_path)) {
           unlink($file_path);
        }
   
        $r = null;
        return $r;
    }

    function set($key, $value, $ttl = null) {
        if (!$this->enable)
            return false;
        if (!$ttl)
            $ttl = $this->default_ttl;
        global $context;
        if ($context->is_debug())
            $context->log_debug("data cache set:{$key}[{$ttl}]");
        $rpos = strrpos($key, '/');
        if ($rpos !== false) {
            $key_path = $this->cache_path . $this->prefix . substr($key, 0, $rpos);
            if (!file_exists($key_path)) {
                if ($context->is_debug())
                    $context->log_debug('data cache mkdir:' . $key_path);
                mkdir($key_path, 0777, true);
            }
        }
        $file_path = $this->cache_path . $this->prefix . $key . '.php';

        $lines = "<?php \n if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');\n";
        $lines = $lines . '$data=' . var_export($value, true) . ";\n";
        $lines .= '$_the_file_ttl=' . ($ttl + time()) . ';';
        $s = file_put_contents($file_path, $lines, LOCK_EX);
        $status = $s!==false?true:false;
        return $status;
    }

    function delete($key) {
        if (!$this->enable)
            return false;
        $file_path = $this->cache_path . $this->prefix . $key . '.php';
        clearstatcache();
        if (file_exists($file_path))
            unlink($file_path);
    }

    function clear() {
        if (!$this->enable)
            return false;
        require_lib('util/file_util', true);
        @rmdir_r($this->cache_path . $this->prefix);
    }
  
    
    
}

class ApcCache extends Cache implements IRequestTool {

    private $enable = false;

    function __construct() {
        $this->enable = extension_loaded('apc');
        parent:: __construct();
    }

    static function register($prop) {
        if (extension_loaded('apc')) {
            $GLOBALS['context']->log_debug('ApcCache create');
            return new ApcCache();
        } else
            return FileCache:: register($prop);
    }

    function &get($key) {
        if (!$this->enable) {
            $r = null;
            return $r;
        }
        $exists = false;
        $data = apc_fetch($this->prefix . $key, $exists);
        if (!$exists) {
            $r = null;
            return $r;
        } else
            return $data;
    }

    function set($key, $value, $ttl = null) {
        if (!$this->enable)
            return false;
        if (!$ttl)
            $ttl = $this->default_ttl;
        apc_store($this->prefix . $key, $value, $ttl);
    }

    function delete($key) {
        if (!$this->enable)
            return false;
        apc_delete($this->prefix . $key);
    }

    function clear() {
        if (!$this->enable)
            return false;
        apc_clear_cache('user');
    }

}

/**
 * MemCache缓存
 *
 * @author zengjf
 */
class MemCacheCache extends Cache implements IRequestTool {

    private $enable = false;
    private $memcache = null;

    function __construct() {
        $this->enable = extension_loaded('memcache');
        parent:: __construct();
    }

    static function register($prop) {
        if (extension_loaded('memcache')) {
            $GLOBALS['context']->log_debug('MemCacheCache create');
            return new MemCacheCache();
        } else
            return FileCache:: register($prop);
    }

    function get_cache() {
        global $context;
        if ($this->enable && $this->memcache === null) {
            $app_conf_memcache_host = $context->get_app_conf('memcache_host');
            if (!$app_conf_memcache_host)
                return;
            if ($context->is_debug())
                $context->log_debug('MemCacheCache connect');
            $this->memcache = new Memcache();
            foreach ($app_conf_memcache_host as $host)
                $this->memcache->addServer($host);
            return $this->memcache;
        }
    }

    function &get($key) {
        $this->get_cache();
        if ($this->memcache === null) {
            $val = null;
            return $val;
        }
        $val = $this->memcache->get($this->prefix . $key);
        $val = $val === false ? null : $val;
        return $val;
    }

    function set($key, $value, $ttl = null) {
        $this->get_cache();
        if ($this->memcache === null)
            return false;
        if ($value === false)
            $value = 0;
        if (!$ttl)
            $ttl = $this->default_ttl;
        global $context;
        if ($context->is_debug())
            $context->log_debug("cache set:{$this->prefix}{$key}[{$ttl}]:{$value}");
        return $this->memcache->set($this->prefix . $key, $value, 0, $ttl);
    }

    function delete($key) {
        $this->get_cache();
        if ($this->memcache === null)
            return false;
        $this->memcache->delete($this->prefix . $key);
    }

    function clear() {
        $this->get_cache();
        if ($this->memcache === null)
            return false;
        $this->memcache->flush();
    }

}

/**
 * Secache 缓存
 *
 * @author huanghy
 */
class SeaCache extends Cache implements IRequestTool {

    static $cache_obj;
    static $cache_key_arr = array();

    function __construct() {
        parent:: __construct();
        $file_path = $this->cache_path . $this->prefix . 'seacache.cache';
        require_once ROOT_PATH . 'lib/cache/secache.php';
        $this->cache_obj = new Secache();
        $this->cache_obj->workat($file_path);
    }

    static function register($prop) {
        if (!isset(self:: $cache_obj)) {
            self:: $cache_obj = new SeaCache();
        }
        return self:: $cache_obj;
    }

    function &get($key) {
        $keystr = md5($key);
        $this->cache_obj->fetch($keystr, $cache_val);
        $t = explode(chr(1), $cache_val);
        $val = $t[0];
        $ttl = $t[1];
        $set_time = $t[2];
        if ($ttl > 0 && time() - $set_time > $ttl) {
            return '';
        } else {
            return $val;
        }
    }

    function set($key, $value, $ttl = null) {
        $keystr = md5($key);
        $ttl = isset($ttl) ? $ttl : -1;
        $value = str_replace(chr(1), '', $value) . chr(1) . $ttl . chr(1) . time();
        $this->cache_obj->store($keystr, $value);
    }

    function delete($key) {
        $keystr = md5($key);
        $this->cache_obj->store($keystr, '');
    }

    function clear() {
        foreach (self:: $cache_key_arr as $keystr) {
            $this->cache_obj->store($keystr, '');
        }
    }

}
