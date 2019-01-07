<?php
/**
缓存session类；
为防止并发修改的问题，建议配合RedLock类中的锁使用
 */

class RedisSession {
    public $server;
    private $redis;
    //private $default_ttl = 1200; //默认时间为1200秒 20分钟。
    /**
    @param @server redis 缓存地址$server['addr']，端口$server['port']，密码$server['auth']；
    目前为单实例初始化
     */
    public function __construct(array $server, $gc_maxlifetime){
        $this->server = array();
        if (isset($server['addr']) && isset($server['port']) && isset($server['auth'])){
            $this->server = $server;
            ini_set('session.gc_maxlifetime', $gc_maxlifetime);
            ini_set("session.save_handler","redis");
            ini_set("session.save_path","tcp://".$this->server['addr']
                .":".$this->server['port']
                ."?auth=".$this->server['auth']);
            session_start();
            $this->redis = new Redis();
            try {
                $this->redis->connect($this->server['addr'],$this->server['port']);
                $this->redis->auth($this->server['auth']);
            } catch (Exception $e) {
                echo "请检查缓存服务器配置";
            }
        } else {
            echo "缓存服务器配置错误";
        }
    }
    /**
     * 设置缓存中某用户名对应的session状态，防止重复登陆
     * @param $username 用户名,$ttl 超时时间，单位秒
     */
    public function set_session_state($username,$ttl){
        //设置state value为session_id(),通过对比value值确定是否为同一次的登陆
        $tmp_key = "sessionid".md5($username);
        $this->redis->set($tmp_key,session_id());
        $this->redis->expire($tmp_key,$ttl); //设置过期时间
    }

    /**
     * 取出缓存中某用户名对应的session状态，通过之前保存的sessionid来判断
     * 如果为空则返回false，需要用户重新登录和设置session
     */
    public function get_session_state($username){
        $previous_sessionid = $this->redis->get("sessionid".md5($username));
        if($previous_sessionid == ""){
            //如果之前没有设置，或者设置的已经失效
            return false;
        } else {
            if ($previous_sessionid == session_id()){
                return true; //同一次登陆
            } else {
                return false;
            }
        }
    }
    /**
     *根据用户名设置某key对应的session值
     */
    public function set_usersession($username, $key_values){
        //session_start();
        $_SESSION['username'] = $username;
        foreach($key_values as $key=>$value){
            $_SESSION[$key] = $value;
        }
    }

    /**
     * 返回
     */
    public function get_usersession($key){
        return $_SESSION[$key];
    }

}