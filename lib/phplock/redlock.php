<?php
//多redis实例redlock算法分布式锁
class redlock
{
    private $retry_delay;
    private $retry_count;
    private $shift_factor = 0.01;
    private $quorum;
    private $servers = array();
    private $instances = array();
    function __construct(array $servers, $retry_delay = 200, $retry_count = 3){
        $this->servers = $servers;
        $this->retry_delay = $retry_delay;
        $this->retry_count = $retry_count;
        //少数服从多数，>= n/2+1 个实例都锁住视为获取锁成功
        $this->quorum  = min(count($servers), (count($servers) / 2 + 1));
    }
    //ttl，锁失效时间
    public function lock($key, $ttl){
        $this->init_instances();
        $token = uniqid();
        $retry = $this->retry_count;
        do {
            $n = 0;
            $startTime = microtime(true) * 1000;
            foreach ($this->instances as $instance) {
                if ($this->lock_instance($instance, $key, $token, $ttl)) {
                    $n++;
                }
            }
            //访问延迟
            $shift = ($ttl * $this->shift_factor) + 2;
            $validity_time = $ttl - (microtime(true) * 1000 - $startTime) - $shift;
            //锁未失效，返回有效时间，
            if ($n >= $this->quorum && $validity_time > 0) {
                return [
                    'validity' => $validity_time,
                    'key' => "Lock:{$key}",
                    'token'    => $token,
                ];
            } else {
                foreach ($this->instances as $instance) {
                    $this->unlock_instance($instance, $key, $token);
                }
            }
            // 重试前随机等待，防止多个客户端同时访问导致失败
            $delay = mt_rand(floor($this->retry_delay / 2), $this->retry_delay);
            usleep($delay * 1000);
            $retry--;
        } while ($retry > 0);
        return false;
    }

    public function unlock(array $lock){
        $this->init_instances();
        $key = $lock['key'];
        $token = $lock['token'];
        foreach ($this->instances as $instance) {
            $this->unlock_instance($instance, $key, $token);
        }
    }

    private function init_instances(){
        if (empty($this->instances)) {
            foreach ($this->servers as $server) {
                list($host, $port, $timeout, $auth_pass) = $server;
                $redis = new Redis();

                $redis->connect($host, $port, $timeout);
                $redis->auth($auth_pass);
                $this->instances[] = $redis;
            }
        }
    }

    private function lock_instance($instance, $key, $token, $ttl){
        return $instance->set("Lock:{$key}", $token, ['NX', 'PX' => $ttl]);
    }

    private function unlock_instance($instance, $key, $token){
        //调用redis内部lua脚本，当token一致时判断为同一把锁，防止删除其他客户的锁。
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        return $instance->eval($script, ["Lock:{$key}", $token], 1);
    }
}

//测试用例
$servers = [
    ['192.168.164.201', 6378, 0.1, '3C2471E2B1222627416336D21C84FF22'],// 地址，端口，超时时间，redis密码
    //['127.0.0.1', 6389, 0.1, 'pass2'],
    //['127.0.0.1', 6399, 0.1, 'pass3'],
];
$red_lock = new redlock($servers);
//while (true) {
$lock = $red_lock->lock('test', 10000); //
if ($lock) {
    print_r($lock);
}
//$unlock = $red_lock->unlock($lock);
//print_r($unlock);
//else {
//    print "waiting lock\n";
//}
//}
