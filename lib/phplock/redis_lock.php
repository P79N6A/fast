<?php
/**
 * Class lock_redis
 * 分布式锁单实例简化版，未测试性能，存在误删风险
 */
/*
$redis1 = new Redis();
$redis1->connect("192.168.164.201",6378);
$redis1->auth("3C2471E2B1222627416336D21C84FF22");
$redis1->set("firstefast5", "sksksksk");
echo $redis1->get("firstefast5");
*/


class lock_redis {

    //锁的超时时间
    private $TIMEOUT = 20;
    private $SLEEP = 100000;
    private $auth_pass = "3C2471E2B1222627416336D21C84FF22";
    /**
     *
     * 当前锁的过期时间
     * @var int
     */
    protected $expire;
    public function __construct(){

    }
    public function init_instance(){
        //$this->expire = 
        $redis = new Redis();
        $redis->connect("192.168.164.201",6378);
        $redis->auth($this->auth_pass);
        return $redis;
    }

    /**
     * Gets a lock or waits for it to become available
     * 获得锁，如果锁被占用，阻塞，直到获得锁或者超时
     *
     * 如果$timeout参数为0，则立即返回锁。
     *
     * @param  string    $key
     * @param  int        $timeout    Time to wait for the key (seconds)
     * @return boolean    成功，true；失败，false
     */
    public function lock($key, $timeout = null){
        if(!$key){
            return false;
        }
        $start = time();
        $redis = $this->init_instance();
        do{
            $this->$expire = $this->timeout();
            if($acquired = ($redis->setnx("Lock:{$key}", $this->$expire))){
                break;
            }

            if($acquired = ($this->recover($key))){
                break;
            }

            if($timeout === 0){
                //如果超时时间为0，即为
                break;
            }
            usleep($this->SLEEP);
        } while(!is_numeric($timeout) || time() < $start + $timeout);

        if(!$acquired){
            //超时
            return false;
        }
        return true;
    }

    /**
     * Releases the lock
     * 释放锁
     * @param  mixed    $key    Item to lock
     * @throws LockException If the key is invalid
     */
    public function unlock($key){
        if(!$key){
            return false;
        }
        $redis = $this->init_instance();
        // 
        if($this->$expire > time()){
            $redis->del("Lock:{$key}");
        }
    }

    /**
     * 生成过期时间
     * @return int    timeout
     */
    protected function timeout(){
        return (int) (time() + $this->TIMEOUT + 1);
    }

    /**
     * Recover an abandoned lock
     * @param  mixed    $key    Item to lock
     * @return bool    Was the lock acquired?
     */
    protected function recover($key){
        $redis = $this->init_instance();
        if(($lock_timeout = $redis->get("Lock:{$key}")) > time())
        {
            //锁还没有过期
            return false;
        }

        $timeout = $this->timeout();
        $current_timeout = $redis->getset("Lock:{$key}", $timeout);
        if($current_timeout != $lock_timeout)
        {
            return false;
        }

        $this->$expire = $timeout;
        return true;
    }
    //测试
    public function get($key){
        return $this->init_instance()->get("Lock:{$key}");
    }
}
$lock_redis = new lock_redis();

$lock_redis->lock("lock_key", 10000);
echo $lock_redis->get("lock_key");