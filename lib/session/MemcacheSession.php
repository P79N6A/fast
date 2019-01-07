<?php
/**
* 类名:    MemcacheSession Class
* 功能:    自主实现基于Memcache存储的 Session 功能
* 描述:    这个类就是实现Session的功能，基本上是通过
*          设置客户端的Cookie来保存SessionID，
*          然后把用户的数据保存在服务器端，最后通过
*          Cookie中的Session Id来确定一个数据是否是用户的，
*          然后进行相应的数据操作
*
*          本方式适合Memcache内存方式存储Session数据的方式，
*          同时如果构建分布式的Memcache服务器，
*          能够保存相当多缓存数据，并且适合用户量比较多并发比较大的情况
*
* 注意: 本类必须要求PHP安装了Memcache扩展或者必须有Memcache的PHP API
*       获取Memcache扩展请访问: http://pecl.php.net
*/

// 设定 SESSION 有效时间，单位是 秒
define('SESS_LIFTTIME',  get_cfg_var ( "session.gc_maxlifetime" ));
require_lib('util/common_util');
class MemcacheSession {
    static $_savePath;
    static $_sessName;
    static $_cacheObj;
    public function __construct() {
        if (! empty(self::$_cacheObj) && is_object(self::$_cacheObj)) {
            return false;
        }
        
        self::$_cacheObj = app_get_cache('session');
        return TRUE;
    }
    public function open($pSavePath = '', $pSessName = '') {
        self::$_savePath = $pSavePath;
        self::$_sessName = $pSessName;
        
        return TRUE;
    }
    public static function close() {
        return TRUE;
    }
    public function read($wSessId = '') {
        $wData = self::$_cacheObj->get($wSessId);
        CTX()->log_error('**session read:' . $wSessId.'->'.$wData);
        // 先读数据，如果没有，就初始化一个
        if (! empty($wData)) {
            return $wData;
        } else {
            // 初始化一条空记录
            $ret = self::$_cacheObj->set($wSessId, '', 0, SESS_LIFTTIME);
            
            if (TRUE != $ret) {
                die("Fatal Error: Session ID $wSessId init failed!");
                
                return FALSE;
            }
            
            return TRUE;
        }
    }
    public static function write($wSessId = '', $wData = '') {
        $ret = self::$_cacheObj->set($wSessId, $wData, 0, SESS_LIFTTIME);
        CTX()->log_error('**session write:' . $wSessId . ', ' . json_encode($wData));
        if (TRUE != $ret) {
            CTX()->log_error("Fatal Error: SessionID $wSessId Save data failed!");
            
            return FALSE;
        }
        
        return TRUE;
    }
    public static function destroy($wSessId = '') {
        self::$_cacheObj->delete($wSessId);
        
        return FALSE;
    }
    public function gc() {
        // 无需额外回收,memcache有自己的过期回收机制
        return TRUE;
    }
    public function init() {
        // 将 session.save_handler 设置为 user，而不是默认的 files
        session_module_name('user');
        
        // 定义 SESSION 各项操作所对应的方法名：
        session_set_save_handler(array(
                'MemcacheSession',
                'open'
        ),         // 对应于静态方法 My_Sess::open()，下同。
        array(
                'MemcacheSession',
                'close'
        ), array(
                'MemcacheSession',
                'read'
        ), array(
                'MemcacheSession',
                'write'
        ), array(
                'MemcacheSession',
                'destroy'
        ), array(
                'MemcacheSession',
                'gc'
        ));
        
        return TRUE;
    }
} 