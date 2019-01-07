<?php
require_lib('tool/Cache.class',false);
require_lib('apiclient/taobao/top/SpiUtils', false);
/**
 * 接口中间层的加密发送、接受
 */
class Validation {

    static protected $sid = array('efast5');
    static protected $platform_code = array('taobao', 'jd', 'jingdong', 'dangdang', 'youzan', 'iwms');
    static protected $secret_key = '';

    /**
     * 加密发送
     * @param string $api_url 接口地址
     * @param string $kh_id 客户ID
     * @param string $sid 业务系统代码
     * @param string $source 平台代码
     * @param array $params 额外参数
     * @return array('curl'=>'','response'=>'')
     */
    static public function send($api_url, $kh_id, $sid, $source = 'taobao', $params = array()) {
        if (empty($api_url) || empty($sid) || empty($kh_id)) {
            return array('status' => -1, 'message' => '必需存在接口地址、客户ID、业务系统标识符');
        }
        if (!in_array($sid, self::$sid)) {
            return array('status' => -2, 'message' => '业务系统代码错误');
        }
        if (!in_array($source, self::$platform_code)) {
            return array('status' => -3, 'message' => '平台代码错误');
        }
        $params['sid'] = $sid;
        $params['kh_id'] = $kh_id;
        $params['source'] = $source;
        $data = self::fast_encode($params);
        //dump($data,1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'data=' . $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $result_str = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        /*
          if ($result_str) {
          $result = json_decode($result_str, true);
          return return_value(1, "", $result);
          }
         * 
         */

        $ack = array(
            'curl' => $api_url . '&data=' . $data,
            'response' => $result_str
        );
        return $ack;
    }

    /*
     * 对接受到的token解密
     * @param string $token
     */

    static public function receive($data) {
        $decode_data = self::fast_decode($data);
        return $decode_data;
    }

    static private function fast_encode($param, $time = '360', $salt = '') {
        if ($salt != '') {
            $pass = $salt;
        } elseif (defined('APP_SALT') && APP_SALT != '') {
            $pass = APP_SALT;
        } else {
            $pass = 'FAST_APP';
        }
        $data = array('data' => $param, 'timestamp' => time() + $time);
        $data_str = json_encode($data);
        $return = urlencode(mcrypt_encrypt(MCRYPT_3DES, $pass, $data_str, "ecb"));
        return $return;
    }

    static private function fast_decode($param, $salt = '') {
        if ($salt != '') {
            $pass = $salt;
        } elseif (defined('APP_SALT') && APP_SALT != '') {
            $pass = APP_SALT;
        } else {
            $pass = 'FAST_APP';
        }

        $uncode = mcrypt_decrypt(MCRYPT_3DES, $pass, $param, "ecb");
        $uncode = substr($uncode, 0, strrpos($uncode, '}') + 1); //除去json字符串后异常的字符
        $uncode = @json_decode($uncode, 1);
        return $uncode['data'];
        if (time() > $uncode['timestamp']) {
            //过期了
            return false;
        } else {
            return $uncode['data'];
        }
    }

    /**
     * 从外部地址接收请求，并验证是否有效，有效直接返回请求参数，无效返回失败信息和状态
     * @todo 可以加上APP_KEY的权限验证
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-19
     * @param array $data 接收的数组
     */
    static public function receive_form_outside($data) {
        //unset($data['PHPSESSID']);
        if (!isset($data['key']) || empty($data['key'])) {
            return array('status' => false, 'message' => 'KEY不存在');
        }
        
        
        $key = $data['key'];
        $kh_id = self::get_kh_id($key);
        

        
        
//        if (!empty($osp_kh_id)) {
//            $kh_id = $osp_kh_id;
//        } else {
//            $kh_id = isset($data['kh_id']) ? $data['kh_id'] : '';
//        }

        if (empty($kh_id)) {
            return array('status' => false, 'message' => '客户ID不存在');
        }

        if (!isset($data['sign']) || empty($data['sign'])) {
            return array('status' => false, 'message' => '签名不存在');
        }
        $sign = $data['sign'];

        if (!isset($data['timestamp']) || empty($data['timestamp'])) {
            return array('status' => false, 'message' => '时间戳不存在');
        }

        $secret = self::get_secret_by_key($key, $kh_id);
        //$data中去掉fast_app强制添加的额外信息 ==================================
        unset($data['sign']);
        unset($data['fastappsid']);
        unset($data['s_c_c_k']);
        
        //校验签名 =============================================================
        $sign_check = self::createSign($data, $secret);
        if ($sign != $sign_check) {
            $return = array('status' => false, 'message' => '签名错误');
            return $return;
        }

        //校验时间戳 ===========================================================
        $timeline = time() - strtotime($data['timestamp']);

        if (360 < abs($timeline)) {
            $return = array('status' => false, 'message' => '请求超时');
            return $return;
        }
        $data['kh_id'] = $kh_id;
        //返回 ================================================================
        $return = array('status' => true, 'message' => '校验成功', 'data' => $data);
        return $return;
    }

    /**
     * 从外部地址接收请求，并验证是否有效，有效直接返回请求参数，无效返回失败信息和状态
     * @todo 可以加上APP_KEY的权限验证
     * @author jhua.zuo<wqian@baisonmail.com>
     * @date 2017-03-19
     * @param array $data 接收的数组
     */
    static public function receive_form_server($data) {
        //unset($data['PHPSESSID']);
        if (!isset($data['key']) || empty($data['key'])) {
            return array('status' => false, 'message' => 'KEY不存在');
        }

//        if (empty($kh_id)) {
//            return array('status' => false, 'message' => '客户ID不存在');
//        }

        if (!isset($data['sign']) || empty($data['sign'])) {
            return array('status' => false, 'message' => '签名不存在');
        }

        $sign = $data['sign'];

        if (!isset($data['timestamp']) || empty($data['timestamp'])) {
            return array('status' => false, 'message' => '时间戳不存在');
        }

        //$data中去掉fast_app强制添加的额外信息 ==================================
        unset($data['sign']);
        unset($data['fastappsid']);
        //校验签名 =============================================================
        $sign_check =   self::createServerSign($data);
        if ($sign != $sign_check) {
            $return = array('status' => false, 'message' => '签名错误');
            return $return;
        }

        //校验时间戳 ===========================================================
        $timeline = time() - strtotime($data['timestamp']);

        if (360 < abs($timeline)) {
            $return = array('status' => false, 'message' => '请求超时');
            return $return;
        }
        //返回 ================================================================
        $return = array('status' => true, 'message' => '校验成功', 'data' => $data);
        return $return;
    }

    //奇门数据验证
    static public function receive_form_qimen($data) {
        if (!isset($data['key']) || empty($data['key'])) {
            return array('status' => false, 'message' => 'KEY不存在');
        }   
        
        $key = $data['key'];
        $kh_id = self::get_kh_id($key);

        if (empty($kh_id)) {
            return array('status' => false, 'message' => '客户ID不存在');
        }

        if (!isset($data['sign']) || empty($data['sign'])) {
            return array('status' => false, 'message' => '签名不存在');
        }

        if (!isset($data['timestamp']) || empty($data['timestamp'])) {
            return array('status' => false, 'message' => '时间戳不存在');
        }
        //校验时间戳 ===========================================================
         $timeline = time() - strtotime($data['timestamp']);

        if (360 < abs($timeline)) {
            $return = array('status' => false, 'message' => '请求超时');
            return $return;
        }

        //$data中去掉fast_app强制添加的额外信息 ==================================
        unset($data['fastappsid']);
        unset($data['s_c_c_k']);
        
        //校验签名 =============================================================
        // $spi = new SpiUtils();
        // $secret = '11b9128693bfb83d095ad559f98f2b07';
        // $sign_check = $spi->checkSign4TextRequest($data, $secret);
        // if (!$sign_check) {
        //     $return = array('status' => false, 'message' => '签名错误');
        //     return $return;
        // }

        $data['kh_id'] = $kh_id;

        $data['method'] = ltrim(strstr($data['method'], '.'), '.');
        $_method = array(
                        'prm.goods.speco.update' => 'prm.goods.spec1.update',
                        'prm.goods.spect.update' => 'prm.goods.spec2.update',
                        'btb.box.record.produce' => 'b2b.box.record.produce',
                        'btb.box.record.detail.update' => 'b2b.box.record.detail.update',
                        'btb.box.record.accept' => 'b2b.box.record.accept',
                        'btb.box.record.print' => 'b2b.box.record.print',
                        'btb.box.record.mark.print' => 'b2b.box.record.mark.print'
                    );

        if(isset($_method[$data['method']])){ 
          $data['method'] = $_method[$data['method']];
        }
        
        //返回 ================================================================
        $return = array('status' => true, 'message' => '校验成功', 'data' => $data);
        return $return;
    }

    static public function createServerSign($data){
        $str  = md5($data['key']).$data['method'].$data['timestamp'];
         return md5($str);
    }

    /**
     * 生成efast5对外接口的签名，参考淘宝的签名规则
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-19
     * @param type $param
     * @param type $secret
     * @return type
     */
    static private function createSign($param, $secret) {
        $sign = $secret;
        ksort($param);
        foreach ($param as $k => $v) {
            if ("@" != substr($v, 0, 1)) {
                $sign .= "$k$v";
            }
        }
        unset($k, $v);
        $sign .= $secret;
        return strtoupper(md5($sign));
    }

    /**
     * 根据第三方平台分发出去的key获取到相应key的secret
     * @todo 此secret应统一进行管理并与客户、接口权限等进行关联
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-19
     * @param string $key 第三方应用的KEY
     * @param int $kh_id 运营平台客户ID
     */
    static private function get_secret_by_key($key, $kh_id = 0) {
        //TODO
        return self::$secret_key;
        //  return 'demo';
    }

    /**
     * 从运行平台获取kh_id
     * @param $pra_authkey
     * Author: yb.ding<ybd312@163.com>
     */
    static private function get_kh_id($pra_authkey) {
        
        $cache = new FileCache();
        $kh_id = null;
        CTX()->db = PDODB::register(array());
        /*
          //切换到运行平台
          CTX()->db->set_conf(array(
          'name' => 'osp',//测试库
          'host' => 'jconncccwmh5v.mysql.rds.aliyuncs.com',
          'user' => 'jusrqe3kdssa',
          'pwd' => 'XN47504969bs',
          'type' => 'mysql',
          'port' => '3306',
          ));
         */
        $kh_data_str = $cache->get($pra_authkey);
        if(empty($kh_data_str)){
            $kh_data = self::get_kh_secret_info($pra_authkey);
            if(!empty($kh_data)){
               $kh_data_str = json_encode($kh_data);
               $cache->set($pra_authkey, $kh_data_str, 7200);   
            }

        }else{
            $kh_data = json_decode($kh_data_str,TRUE);
//            if(!isset($kh_data['kh_id'])||!isset($kh_data['secret_key'])){
//                 $kh_data = self::get_kh_secret_info($pra_authkey);
//            }
        }   
        if(!empty($kh_data)){
                $kh_id = $kh_data['kh_id'];
                self::$secret_key = $kh_data['secret_key'];
        }
        
        //切换回原数据链接
//        CTX()->db->set_conf(array(
//            'name' => CTX()->get_app_conf('db_name'),
//            'host' => CTX()->get_app_conf('db_host'),
//            'user' => CTX()->get_app_conf('db_user'),
//            'pwd' => CTX()->get_app_conf('db_pass'),
//            'type' => 'mysql',
//            'port' => '3306',
//        ));
        return $kh_id;
    }
    static function get_kh_secret_info($pra_authkey){
             $sql = 'select pra_kh_id from osp_productorder_auth where pra_authkey = :pra_authkey and pra_state="1" ';
            //TODO 添加授权码过期时间过滤
            $sql_val[':pra_authkey'] = $pra_authkey;
            $kh_id = CTX()->db->get_value($sql, $sql_val);
            if(!empty($kh_id)){
                $sql = "select authkey from osp_valueauth_key where kh_id=:kh_id";
                $secret_key = CTX()->db->get_value($sql, array(':kh_id' => $kh_id)); 
            }else{
                return array();
            }
            return array(
                'kh_id'=>$kh_id,
                'secret_key'=>$secret_key,
            );
    }

}
