<?php
if(!class_exists('PDODB')){
          require_once ROOT_PATH . 'lib/db/PDODB.class.php';
}

class Saas  implements IRequestTool {
    static  $saas_client_id =null;
    static $saas_client_arr;
    static $now_rds_id;
    static $client; //当前客户saas信息
    static $saas_sys_db;
    private static $saas_mode;
    var $saas_client_cache_key ;
    var $osp_db;
    var $osp_config;
    function __construct() {
        $this->init();
  
	
    }
	static function register($prop) {
		global $context;
		if ($context->is_debug()) $context->log_debug('Saas create');
		return new Saas();
	}
    protected function init(){
        //saas 使用三种模式 cli(命令行) ,web（系统应用）,api(api请求接口)
        if(CTX()->is_in_cli()===TRUE){
           self::$saas_mode = 'cli';
        }else{
            self::$saas_mode = 'web';
          //  CTX()->init_session();//saas_client_cache_key_
            $this->saas_client_cache_key = "s_c_c_k";
        }  
      $this->product_version_map = array('1'=>'efast5_Standard','2'=>'efast5_Enterprise','3'=>'efast5_Ultimate');
        
    }
    
    function init_saas_client($init_info = array()){
        
       if(!empty($init_info['client_id'])){
            $this->set_saas_key($init_info['client_id']);
             $client_id = $init_info['client_id'] ;
       }else{
            $client_id = $this->get_saas_key();
       }

       if(empty($client_id)){
                return array('status'=>-1,'message'=>'没有客户ID');
       }

       if(self::$saas_mode=='web'&&empty($init_info['db_conf'])){
           $init_info = $this->get_client_cache($client_id);
       }
        
//        if(!empty($init_info['client_id'])){
//            return array('status'=>-1,'message'=>'没有客户ID');
//        }
        
        if(empty($init_info['rds_id'])){
            return array('status'=>-1,'message'=>'没有客户rds_id');
        } 
        
//        if(!empty($init_info['product_version'])){
//            return array('status'=>-1,'message'=>'没有客户产品版本');
//        }  
       
        if(empty($init_info['db_conf'])){
            return array('status'=>-1,'message'=>'数据库配置参数');
        }    
    
       if(self::$saas_mode=='web'){
           $this->set_client_cache($client_id,$init_info);
       }     
       $this->create_saas_client($init_info);
       return array('status'=>1);
    }


    function create_saas_client($init_info,$is_now = true){
 
        
        $init_info['db'] = $this->create_db($init_info['db_conf']);
        $this->create_sys_db($init_info['db_conf'],$init_info['rds_id']);

        self::$saas_client_arr[$init_info['client_id']] = new SaasClient($init_info);
        if(true === $is_now){
            self::$saas_client_id = $init_info['client_id'];
            self::$client =  &self::$saas_client_arr[$init_info['client_id']];
            CTX()->db =  self::$client->db;
        }
      
    }
    function cil_create_saas_client($rds_id,$client_id){
         $init_info = CTX()->saas->get_client_cache($client_id);
           if(empty($init_info)){
               
               $config = require_conf('db');
               $conf = isset($config[$rds_id])?$config[$rds_id]:array();
               if(!empty ($init_info['db_conf'])){
                    return false;
               }
       
               $this->create_sys_db($conf,$rds_id);
        
               $client_db_name = $this->get_clien_db_name($rds_id,$client_id);
               if($client_db_name=== false){
                     return false;
               }
               $conf['name'] = $client_db_name;
               $init_info['client_id'] = $client_id;
               $init_info['rds_id'] = $rds_id;
               $init_info['db_conf'] = $conf;
           }     

           $ret_status = $this->init_saas_client($init_info);
           if($ret_status['status']<0){
                return false;
            }    
    }
    
    private function get_clien_db_name($rds_id,$client_id){
	
           $row = self::$saas_sys_db[$rds_id]->get_row("select rem_db_name from osp_rdsextmanage_db where rem_db_khid='{$client_id}' ");
           if(empty($row['rem_db_name'])){
               return false;
           }
           return $row['rem_db_name'];
    }
    
    
  
    function set_client_cache($client_id,$info){
        $cache = $this->get_cache();
        $client_key = $this->get_client_key($client_id);
        $old_info = $this->get_client_cache($client_id);
        $old_info_str = '';
        if(!empty($old_info)){
            $old_info_str = md5(json_encode($old_info));
        }
        
        $new_info_str =  md5(json_encode($info));
        $ttl = 10000000000;//过期时间
        //不同时候设置
        if($old_info_str != $new_info_str){
            $info = $this->encode_info($info);
            $cache->set($client_key,$info,$ttl);
        }
    }
    
    function get_client_cache($client_id){
         $cache = $this->get_cache();
         $client_key = $this->get_client_key($client_id);
         $info = $cache->get($client_key);
         if(!empty($info)){
             $info = $this->decode_info($info);
         }
         return $info;
    }  
    
    private function decode_info($info){
        return json_decode(base64_decode($info) ,true);
    } 
    
    private function encode_info($info){
        return base64_encode(json_encode($info));
    }


    function get_client_key($client_id){
        return 'yswl_client_'.$client_id; 
    }
    function get_rds_id(){
        return self::$now_rds_id;
    }
    
    

    //web默认要设置
    function set_saas_key($client_id){
        self::$saas_client_id = &$client_id;
         if(self::$saas_mode == 'web'){
             $this->set_cache_saas_key($client_id);
         }        
    }
    
    private function set_cache_saas_key($client_id){
        //CTX()->set_session($this->saas_client_cache_key, $client_id,false);
          CTX()->set_cookie($this->saas_client_cache_key, $client_id,3600);
    }
    function out_saas(){
          CTX()->set_cookie($this->saas_client_cache_key, '0',-3600);
    }
    
    protected function  get_cache(){
        static $cache = null;
        if(empty($cache)){
            require_lib('tool/Cache.class');
            $cache = new FileCache(false);
        }
        return $cache;
    }

    function get_saas_key(){
         if(self::$saas_mode == 'web'&&empty(self::$saas_client_id)){
            //self::$saas_client_id = CTX()->get_session($this->saas_client_cache_key,false);   
            self::$saas_client_id = isset($_COOKIE[$this->saas_client_cache_key])?$_COOKIE[$this->saas_client_cache_key]:'';
            if(self::$saas_client_id !==''){
                $this->set_cache_saas_key(self::$saas_client_id);
            }
         }    
          return self::$saas_client_id;
    }
    

    //设置saas 为api模式
    function set_saas_mode_api(){
          self::$saas_mode = 'api';
    }
    function check_saas_mode($mode){
        if(self::$saas_mode == $mode){
            return TRUE;
        }
        return FALSE;
    }
    function  get_saas_mode(){
        return self::$saas_mode;
    }
    //设置Saas  为主DB
    function set_saas_for_main_db(){
        CTX()->db =  self::$client->db;
    }
    /*
     * 获取当前客户产品版本
     *  //1标准，2企业，3旗舰
     */
    function get_product_version(){
             $product_version = 1;
             if(!empty(self::$client)){
                 $product_version = array_search(self::$client->product_version, $this->product_version_map);
                 if($product_version===false){
                     $product_version = 1;
                 }
             }
             return $product_version;
    }

    private function create_osp_db(){
        $this->osp_config = require_conf('osp');
        $this->osp_db  = $this->create_db($this->osp_config['db']);
    }
    function get_osp_db(){
       if(empty( $this->osp_db)){
           $this->create_osp_db();
       }
       return  $this->osp_db;
    }

    function create_sys_db($conf,$rds_id){
        if(!isset(self::$saas_sys_db[$rds_id])){
            $conf['name'] = 'sysdb';
            self::$now_rds_id = $rds_id;
            self::$saas_sys_db[$rds_id] = $this->create_db($conf);
        }
    }
     

    function &get_sys_db(){
        
            if(!empty(self::$client)){
                $rds_id= self::$client->rds_id;
            }else{
                $rds_id = self::$now_rds_id;
            }
            
            if(isset(self::$saas_sys_db[$rds_id])){
                return self::$saas_sys_db[$rds_id];
            }
            return FALSE;
    }
    
     function is_purview_check(){
        if(self::$saas_mode == 'web'){
            return TRUE;
        }else if(!empty(self::$client->user_id)){
              return TRUE;
        }
        return false;
   }    
    
    function create_db($conf){
        return  new PDODB($conf);
    }
    
    function  set_user_id($user_id){
        self::$client->set_user_id($user_id);
    }
    
   private function get_crypt_key($client_id){
       return md5($client_id."yswl");
   }
   





//   private function setcrypt($string, $operation = 'DECODE', $key = '', $expiry = 0)
//  {
//	    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
//	    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
//	    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
//	    // 当此值为 0 时，则不产生随机密钥
//	    $ckey_length = 4;
//
//	    // 密匙
//	    $key = md5($key);
//
//	    // 密匙a会参与加解密
//	    $keya = md5(substr($key, 0, 16));
//	    // 密匙b会用来做数据完整性验证
//	    $keyb = md5(substr($key, 16, 16));
//	    // 密匙c用于变化生成的密文
//	    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
//	    // 参与运算的密匙
//	    $cryptkey = $keya . md5($keya . $keyc);
//	    $key_length = strlen($cryptkey);
//	    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
//	    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
//	    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
//	    $string_length = strlen($string);
//	    $result = '';
//	    $box = range(0, 255);
//	    $rndkey = array();
//	    // 产生密匙簿
//	    for ($i = 0; $i <= 255; $i++) {
//	        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
//	    }
//	    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上并不会增加密文的强度
//	    for ($j = $i = 0; $i < 256; $i++) {
//	        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
//	        $tmp = $box[$i];
//	        $box[$i] = $box[$j];
//	        $box[$j] = $tmp;
//	    }
//	    // 核心加解密部分
//	    for ($a = $j = $i = 0; $i < $string_length; $i++) {
//	        $a = ($a + 1) % 256;
//	        $j = ($j + $box[$a]) % 256;
//	        $tmp = $box[$a];
//	        $box[$a] = $box[$j];
//	        $box[$j] = $tmp;
//	        // 从密匙簿得出密匙进行异或，再转成字符
//	        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
//	    }
//	    if ($operation == 'DECODE') {
//	        // substr($result, 0, 10) == 0 验证数据有效性
//	        // substr($result, 0, 10) - time() > 0 验证数据有效性
//	        // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
//	        // 验证数据有效性，请看未加密明文的格式
//	        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
//	            return substr($result, 26);
//	        } else {
//	            return '';
//	        }
//	    } else {
//	        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
//	        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
//	        return $keyc . str_replace('=', '', base64_encode($result));
//	    }
//	}    
}

class SaasClient{
    public $db;
    public $rds_id;
    public $client_id;
    public $product_version;
    public $user_id=0;
    function __construct($init_info) {
        $this->init( $init_info );
        
    }
    function init( &$init_info ){
        $this->db  = $init_info['db'];
        $this->rds_id  = $init_info['rds_id'];
        $this->client_id  = $init_info['client_id'];
        if(isset($init_info['product_version'])){
            $this->product_version  = $init_info['product_version'];
        }else{
            $this->set_product_version();
        }
    }
    function set_user_id($user_id){
        $this->user_id = $user_id;
    }
   
    function set_product_version(){
             $sql = "select value from sys_auth where code='cp_code' ";
             $row = $this->db->get_row($sql);
             if(!empty($row)){
                 $this->product_version = $row['value'];
             }
    }
    
    
}


?>
