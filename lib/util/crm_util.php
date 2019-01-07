<?php 
function object_to_array($obj) {
    $arr = array();
    if (is_object($obj) || is_array($obj)) {
        foreach ($obj as $key => $val) {
            if (!is_object($val)) {
                if (is_array($val)) {
                    $arr[$key] = object_to_array($val);
                } else {
                    $arr[$key] = $val;
                }
            } else {
                $arr[$key] = object_to_array($val);
            }
        }
    }
    return $arr;
}

function return_value($status, $message = '', $data = '') {
    return array('status' => $status, 'message' => $message, 'data' => $data);
}

/**
 * POST远程提交
 * $url 远程链接
 * $post_data 参数
 */
function post_submit($url, $post_data = "", $followlocation = false) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //跳过SSL证书检查
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followlocation); //用来跟随重定向页面
    curl_setopt($ch, CURLOPT_HEADER, 0);

    curl_setopt($ch, CURLOPT_URL, $url);
    if (isset($parameter['referer_url']))
        curl_setopt($ch, CURLOPT_REFERER, $parameter['referer_url']);
    if (isset($parameter['save_cookie']))
        curl_setopt($ch, CURLOPT_COOKIEJAR, $parameter['save_cookie']);
    if (isset($parameter['use_cookie']))
        curl_setopt($ch, CURLOPT_COOKIEFILE, $parameter['use_cookie']);
    if ($post_data != "") {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }

    $info = curl_exec($ch); //执行一个cURL会话,并把它传递给浏览器
    curl_close($ch);
    return $info;
}

function json_encode_zh($str_arr) {
	if(substr(PHP_VERSION, 0, strrpos(PHP_VERSION,'.')) >= 5.4){
		$jsonStr = json_encode($str_arr, JSON_UNESCAPED_UNICODE);
	}else{
		$jsonStr = json_encode($str_arr);
		$search  = "#\\\u([0-9a-f]{4}+)#ie";
		$replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";
		$jsonStr = preg_replace($search, $replace, $jsonStr);
	}
	return $jsonStr;
}

function add_time() {
    return date("Y-m-d H:i:s");
}

function add_day_time() {
    return date("Y-m-d");
}

function get_record_code($id,$l=4) {
    $table = array(
        "44"=>array("table"=>"crm_coupon"),
        "30"=>array("table"=>"crm_customer"),
        "1"=>array("table"=>"crm_consume"),
    );
    $table = $table[$id];
    $record_code = "";

    $db = $GLOBALS['context']->db;

    //$now = date("s");
    $now = rand(10, 99);
    /*if($db->dbtype == 0){
        $sql = "show table status where name = '" . $table['table'] . "' ";
        $data = $db->get_all($sql);
        $code = $data[0]['Auto_increment'];
    }else{
        $code = $db->get_seq_next_value(get_oracle_seq($table['table']));
    }*/
    
    /*$sql = "show table status where name = '" . $table['table'] . "' ";
    $data = $db->get_all($sql);
    $code = $data[0]['Auto_increment'];*/
    if($code = get_cache($table['table'])){
        $code++;
    }else{
        $code = 1;
    }
    add_cache($table['table'], $code);
    if (strlen($code) > $l) {
        substr($code, $l);
        $record_code = $now . $code;
    } else {
        $length = $l - strlen($code);
        $html = "";
        for ($i = 0; $i < $length; $i++) {
            $html .= "0";
        }
        $record_code = $now . $html . $code;
    }

    //目前直接返回，后续扩展
    return $record_code;
    $sql = "select * from sys_code_rule where code_rule_id = " . $id;
    $rule = $db->get_row($sql);

    // 没有数据时返回
    if (empty($rule)) {
        return $record_code;
    }
    $record_code .= $rule['code_prefix'];

    /*
     *  '0'=>'YYYYMMDD',
      '1'=>'YYMMDD',
      '2'=>'YYYYMM',
      '3'=>'YYMM',
      '4'=>'MMDD'
     */
    if ($rule['is_time'] == 1) {
        switch ($rule['time_style']) {
            case 0:
                $record_code .= date("Ymd");
                break;
            case 1:
                $record_code .= date("ymd");
                break;
            case 2:
                $record_code .= date("Ym");
                break;
            case 3:
                $record_code .= date("ym");
                break;
            case 4:
                $record_code .= date("md");
                break;
        }
    }
    $record_code .= $code;
    if(strlen($record_code) == 12){
       $record_code = generate_order_sn_barcode_check_code($record_code);
    }
    return $record_code;
}

/**
* 获得13位barcode的校验码
* Enter description here ...
* @param unknown_type $code
*/
function generate_order_sn_barcode_check_code($code) {

       //$ncode = '0' . $code;
       $ncode = $code;
       $length = strlen($ncode);
       $lsum = $rsum = 0;
       for($i=0; $i < $length; $i++) {
               if($i % 2) {
                       $lsum += intval($ncode[$i]);
               } else {
                       $rsum += intval($ncode[$i]);
               }
       }
       $tsum = $lsum * 3 + $rsum;
       $code .= (10-($tsum % 10)) % 10;
       return $code;
}

//生成代码
function filler_num($str, $length, $num = 0) {
    $length = $length - strlen($str);
    for ($i = 0; $i < $length; $i++) {
        $str = "0" . $str;
    }
    return $str;
}

//生成静态文件
function create_cms_html($filepath, $string, $type = "") {
    $string = stripSlashes($string);
    make_folder($filepath);
    write_text($filepath, $string, $type);
}

//创建路径的所有文件夹
function make_folder($path) {
    $dir = "";
    $arr = explode("/", $path);
    for ($i = 0; $i < count($arr) - 1; $i++) {
        $dir .= $arr[$i] . "/";
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }
}
//写入文件
function write_text($filepath, $string, $type = "") {
    if ($type == "UTF-8")
        $string = "\xEF\xBB\xBF" . $string;
    $fp = @fopen($filepath, "w");
    @fputs($fp, $string);
    @fclose($fp);
    if (empty($filechmod)) {
        @chmod($filepath, 0777);
    }
}

//读取文件
function read_text($filepath) {
    $string = "";
    $htmlfp = @fopen($filepath, "r");
    while ($data = @fread($htmlfp, 1000)) {
        $string .= $data;
    }
    @fclose($htmlfp);
    return $string;
}

function add_cache($key, $value, $type = 'text') {
    $path = ROOT_PATH . "cache/";
    $file = $key . ".txt";
    if (!is_dir($path)) {
        make_folder($path);
    }
    write_text($path . $file, base64_encode($value));
}

function get_cache($key, $second = 0, $type = 'text') {
    $path = ROOT_PATH . "cache/";
    $file = $key . ".txt";
    if (!file_exists($path . $file))
        return false;
    $file_time = filemtime($path . $file);
    if (isset($second) && $second != "") {
        if (strtotime("now") - $file_time < $second) {
            return base64_decode(read_text($path . $file));
        } else {
            return false;
        }
    } else {
        return base64_decode(read_text($path . $file));
    }
}

//获取session值
function get_session($name, $pub = false) {
    if (!isset($_SESSION)) {
        session_start();
    }
    //$this->init_session();
    //if(! $pub) $name='fAp'.$this->app_name . $name;
    return isset($_SESSION[$name]) ? $_SESSION[$name] : NULL;
}

//设置session值
function set_session($name, $value, $pub = false) {
    if (!isset($_SESSION)) {
        session_start();
    }
    //if(! $pub) $name='fAp'.$this->app_name . $name;
    $_SESSION[$name] = $value;
}

function del_session($name) {
    unset($_SESSION[$name]);
}

//清除session
function clean_session_c() {
    if (!isset($_SESSION)) {
        session_start();
    }
    session_destroy();
}

function create_sign($app_key,$app_secret,$data){
    unset($data['sign']);
    ksort($data);
    	
	$param_str = "";
	foreach ($data as $k=>$v){
		$param_str .= $k.$v;
	}
	
	$param_str = md5_3($app_key . $param_str . $app_secret);
	return $param_str;
}

function md5_3($str) {
    return strtoupper(md5(md5(md5($str, true), true)));
}

function check_taobao_sign($param){
    require_model("sys/TaobaoAccountModel");
    $mdl_taobao_account = new TaobaoAccountModel();
    
    if(!isset($param['user_code'])){
        return return_value(-1,"账号不能为空");
    }
    $account = $mdl_taobao_account->get_account_by_code($param['user_code']);

    if(!$account){
        return return_value(-1,"开启中心服务器模式，请配置中心服务器账号，账号不存在".$param['user_code']);
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    if($ip != "127.0.0.1" && $ip != trim($account['vm_ip'])){
        return return_value(-1,"ip不正确".$ip);
    }
    
    $param_str = create_sign($account['user_code'],$account['password'],$param);
    
    if($param_str != $param['sign']){
        return return_value(-1,"sign错误");
    }
    
    if($param['timestamp']+60 < time()){
        return return_value(-1,"时间错误");
    }
    $account['sign'] = $param_str;
    
    return return_value(1,"验证通过",$account);
}

/**
 * 根据会员积分/累计消费次数/累计消费金额，获得对应会员等级记录
 * @param string $int
 * @param boolean $strict 客户的积分小于最低积分或是大于最大积分的情况下，如果该参数为true，则返回“没有查询到对应的会员等级”；该参数为false,则返回最低等级或是最高等级
 * @return array|string
 */
function get_vip_level_by_int($int, $strict = false) {
    $db = $GLOBALS['context']->db;
    //获得会员等级表所有数据
    $sql = "select * from crm_vip_level where is_ec = 1 order by start_integral ";
    $data = $db->get_all($sql);
    if (!empty($data)) {
        if ($int < $data[0]['start_integral']) {
            return $strict ? '没有查询到对应的会员等级' : $data[0];
        }
        $last_level = end($data);
        if ($int > $last_level['start_integral']) {
            return $strict ? '没有查询到对应的会员等级' : $last_level;
        }
        reset($data);
        foreach ($data as $k => $v) {
            if ($int >= $v['start_integral'] && $int <= $v['end_integral']) {
                return $data[$k];
            }
        }
    } else {
        return '会员等级表没有数据';
    }
}

/**
 * 通过VIP代码获取VIP
 * @param $vip_code
 * @return array
 */
function get_vip_by_code($vip_code) {
    $db = $GLOBALS['context']->db;
    $sql = "select * from crm_vip where vip_code = :vip_code";
    $data = $db->get_row($sql, array(":vip_code" => $vip_code));
    if (!empty($data)) {
        return $data;
    } else {
        return "";
    }
}