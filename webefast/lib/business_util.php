<?php
/**
 *
 * 把列表的格式转成bui格式
 * @param unknown_type $arr
 */
function format_bui($arr){
    $temp_arr = array();
    foreach ($arr as $data){
        $data = array_values($data);
        $temp = array();
        $temp['text'] = $data[1];
        $temp['value'] = $data[0];
        $temp_arr[] = $temp;
    }
    return json_encode($temp_arr);
}

/**
 * 将HTML传递的带逗号分割的字符串统一加上单引号
 * @author zuo <jhua.zuo@baisonmail.com>
 * @date 2013-09-07
 * @param string $str 逗号分割的字符串
 * @return string 返回处理后的字符串，可以直接丢给sql用作in条件
 */
function deal_strs_with_quote($str) {
    $arr = explode(',', $str);
    foreach ($arr as $key => $val) {
        $arr[$key] = "'" . $val . "'";
    }
    return implode(',', $arr);
}

/**
 * 将数组统一加上单引号
 * @author jhua.zuo <jhua.zuo@baisonmail.com>
 * @date 2013-11-02
 * @param array $array 待处理的数组
 * @return string 返回处理后的字符串，可以直接丢给sql用作in条件
 */
function deal_array_with_quote($array) {
    if (empty($array)) {
        return '0';
    }
    foreach ($array as $key => $val) {
        $array[$key] = "'" . $val . "'";
    }
    return implode(',', $array);
}

function split_time($start,$end,$split){
    $time_arr = array();
    $start = strtotime($start);
    $end = strtotime($end);
    $count = ceil(($end-$start)/$split);

    for($i = 0;$i < $count;$i++){
        $temp_arr = array();
        $temp_arr['start'] = date("Y-m-d H:i:s",$start);
        $temp_arr['end'] = date("Y-m-d H:i:s",$start+$split);
        $time_arr[] = $temp_arr;
        $start = $start+$split;
    }
    $time_arr[$count-1]['end'] = date("Y-m-d H:i:s",$end);
    return $time_arr;
}

function NumToStr($num){
    if (stripos($num,'e')===false) return $num;
    $num = trim(preg_replace('/[=\'"]/','',$num,1),'"');//出现科学计数法，还原成字符串
    $result = "";
    while ($num > 0){
        $v = $num - floor($num / 10)*10;
        $num = floor($num / 10);
        $result   =   $v . $result;
    }
    return $result;
}

function get_name_by_code($param,$key){
    static  $app_common =NULL;
    if(empty($app_common)){
        $path = ROOT_PATH.($GLOBALS['context']->app_name)."/conf/field.conf.php";
        include_once $path;
        $app_common = $common;
    }
    return isset($app_common[$key][$param])?$app_common[$key][$param]:'';
}
/**
 *
 * 获取用户信息
 * @param unknown_type $type 0：全部 1：业务员
 */
function get_user($type = 0){
    $db = $GLOBALS['context']->db;
    if($type == 1){
        $sql = "select user_code,user_name from sys_user where is_salesman = 1";
        $data = $db->get_all($sql);
        return $data;
    }
}

function get_user_by_id($id){
    $db = $GLOBALS['context']->db;
    $sql = "select * from sys_user where user_id = :user_id";
    return $db->get_row($sql,array(":user_id"=>$id));
}

function get_shop_api_list(){
    $db = $GLOBALS['context']->db;
    $sql = "select * from base_shop_api";
    return $db->get_all($sql);
}

function get_shop_name_by_code($shop_code){
    $db = $GLOBALS['context']->db;
    $sql = "select shop_name from base_shop where shop_code = :shop_code";
    return $db->get_value($sql, array(":shop_code" => $shop_code));
}

function get_store_name_by_code($store_code){
    $db = $GLOBALS['context']->db;
    $sql = "select store_name from base_store where store_code = :store_code";
    $data = $db->get_value($sql, array(":store_code" => $store_code));
    if ($data) {
        return $data;
    } else {
        return "";
    }
}

//获取快递公司名称
function get_express_name_by_code($express_code){
	$db = $GLOBALS['context']->db;
	$sql = "select express_name from base_express where express_code = :express_code";
	$data = $db->get_value($sql, array(":express_code" => $express_code));
	if ($data) {
		return $data;
	} else {
		return "";
	}
}

//获取分销商名称
function get_custom_name_by_code($custom_code){
	$db = $GLOBALS['context']->db;
	$sql = "select custom_name from base_custom where custom_code = :custom_code";
	$data = $db->get_value($sql, array(":custom_code" => $custom_code));
	if ($data) {
		return $data;
	} else {
		return "";
	}
}


function get_goods_name_by_code($goods_code){
    $db = $GLOBALS['context']->db;
    $sql = "select goods_name from base_goods where goods_code = :goods_code";
    return $db->get_value($sql, array(":goods_code" => $goods_code));
}

function get_barcode_by_sku($sku){
    $db = $GLOBALS['context']->db;
    $sql = "select barcode from goods_barcode where sku = :sku";
    return $db->get_value($sql, array(":sku" => $sku));
}

function get_spec1_name_by_code($code){
    $db = $GLOBALS['context']->db;
    $sql = "select spec1_name from base_spec1 where spec1_code = :spec1_code";
    return $db->get_value($sql, array(":spec1_code" => $code));
}

function get_spec2_name_by_code($code){
    $db = $GLOBALS['context']->db;
    $sql = "select spec2_name from base_spec2 where spec2_code = :spec2_code";
    return $db->get_value($sql, array(":spec2_code" => $code));
}

function get_area_name_by_id($id){
    $db = $GLOBALS['context']->db;
    $sql = "select name from base_area where id = :id";
    return $db->get_value($sql, array(":id" => $id));
}