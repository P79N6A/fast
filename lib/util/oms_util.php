<?php

/**
 * oms util
 * author: liud
 */

/**
 * read scale value.
 * ex: field_val('oms_sell_record', 'record_code', array('sell_record_id'=>'1'));
 * @param string $tb
 * @param string $f
 * @param array $w
 * @return string
 */
function oms_tb_val($tb, $f, $w) {
    global $context;
    $sql = "SELECT * FROM {$tb} WHERE 1 ";
    $p = array();
    foreach ($w as $k => $v) {
        $sql .= " AND $k = :$k ";
        $p[$k] = $v;
    }
    $r = $context->db->get_row($sql, $p);
    if (empty($r) || !isset($r[$f])) {
        return '';
    } else {
        return $r[$f];
    }
}

/**
 * Read rows.
 * @param string $tb
 * @param array $w
 * @return mixed
 */
function oms_tb_all($tb, $w) {
    global $context;
    $sql = "SELECT * FROM {$tb} WHERE 1 ";
    $p = array();
    foreach ($w as $k => $v) {
        $sql .= " AND $k = :$k ";
        $p[$k] = $v;
    }
    return $context->db->get_all($sql, $p);
}

/**
 * array to map
 * @param $tb
 * @param $fk
 * @param $fv
 * @param array $w
 * @return array
 */
function oms_dict_by_tb($tb, $fk, $fv, $w = array()) {
    $r = oms_tb_all($tb, $w);
    $d = array();
    foreach ($r as $v) {
        $d[$v[$fk]] = $v[$fv];
    }
    return $d;
}

/**
 * @param string $tb table name
 * @param string $fk field key
 * @param string $fv field value key
 * @param array $w where array
 * @param int $t 1:全部,2请选择
 * @return array
 */
function oms_opts_by_tb($tb, $fk, $fv, $w = array(), $t = 0) {
    return oms_opts_by_val(oms_dict_by_tb($tb, $fk, $fv, $w), $t);
}

/**
 * map to array, by model->method
 * @param $m
 * @param $f
 * @param int $t
 * @return array
 */
function oms_opts_by_md($m, $f, $t = 0) {
    return oms_opts_by_val(load_model($m)->$f, $t);
}

/**
 * map to array
 * @param array $val
 * @param int $t
 * @return array
 */
function oms_opts_by_val($val, $t = 0) {
    return array_from_dict(oms_opts2_by_val($val, $t));
}

/**
 * @param $tb
 * @param $fk
 * @param $fv
 * @param array $w
 * @param int $t
 * @return array
 */
function oms_opts2_by_tb($tb, $fk, $fv, $w = array(), $t = 0) {
    return oms_opts2_by_val(oms_dict_by_tb($tb, $fk, $fv, $w), $t);
}

/**
 * map to map
 * @param array $val
 * @param int $t
 * @return array
 */
function oms_opts2_by_val($val, $t = 0) {
    $d = array();
    switch ($t) {
        case 1: $d[''] = '全部';
            break;
        case 2: $d[''] = '请选择';
            break;
    }
    foreach ($val as $k => $v) {
        $d[$k] = $v;
    }

    return $d;
}

/**
 * 比较两个浮点型数字的大小
 * value1>value2 1
 * value1=value2 0
 * value1<value2 -1
 * @param $value1
 * @param $value2
 * @return int
 */
function compare_float($value1, $value2) {
    $compare_result = 0;
    $compare_diff = 0.01;

    $diff = (double) ($value1 - $value2);

    if (abs($diff) < $compare_diff) {
        $compare_result = 0;
    } else if ($diff >= $compare_diff) {
        $compare_result = 1;
    } else if ($diff <= (-1 * $compare_diff)) {
        $compare_result = -1;
    }

    return $compare_result;
}

function safe_data(&$data, $is_view = 1) {

   $is_encrypt = load_model('sys/security/OmsSecurityOptModel')->is_encrypt_record($data,'sell_record');
   if($is_encrypt==0){
       return false;
   } 
   
    if ( $is_encrypt==1||( (!isset($data['customer_address_id']) || $data['customer_address_id'] == 0)&&$is_encrypt==2) ) {
        //fenxiao_name
        $name_arr = array('buyer_name','receiver_name');
        foreach($name_arr as $key){
            if (isset($data[$key])) {
                $len = strlen_utf8($data[$key]);
                if ($len < 2) {
                    $data[$key] = '*';
                }else {
                    $data[$key] = substr_utf8($data[$key], 0, 1) . "***";
                }
            }
        }

        if (isset($data['receiver_mobile'])) {
            $len = strlen($data['receiver_mobile']);
            $data['receiver_mobile'] = substr($data['receiver_mobile'], 0, $len - 8) . "*******";
        }
        if (isset($data['receiver_address'])) {
            //$len = strlen_utf8($data['receiver_address']);
            $data['receiver_address'] = str_replace($data['receiver_addr'], "*****", $data['receiver_address']);
           
        }
        //  echo $data['receiver_address'];die;
        if (isset($data['receiver_phone'])&&!empty($data['receiver_phone'])) {
            $len = strlen($data['receiver_phone']);
            if($len>5){
                $data['receiver_phone'] = substr($data['receiver_phone'], 0, $len - 5) . "*****";
            }
        }
    }
    if ($is_view == 1) {
        $key_arr = array('buyer_name','receiver_name' ,'receiver_mobile', 'receiver_address', 'receiver_phone');
        $sell_record_code = $data['sell_record_code'];
        foreach ($key_arr as $key) {
            if(!empty($data[$key])){
                $data[$key] ='<span class="like_link" onclick = "show_safe_info(this,\'' . $sell_record_code . '\',\'' . $key . '\')">'.$data[$key].'</span>';
            }
        }
    }
}

function safe_return_data(&$data, $is_view = 1) {
   $is_encrypt = load_model('sys/security/OmsSecurityOptModel')->is_encrypt_record($data,'sell_return');
   if($is_encrypt==0){
       return false;
   } 
   
    if ( $is_encrypt==1||( (!isset($data['customer_address_id']) || $data['customer_address_id'] == 0)&&$is_encrypt==2) ) {

        $name_arr = array('buyer_name','return_name','change_name');
        foreach($name_arr as $key){
            if (isset($data[$key])) {
                $len = strlen_utf8($data[$key]);
                if ($len < 2) {
                    $data[$key] = '*';
                }else {
                    $data[$key] = substr_utf8($data[$key], 0, 1) . "***";
                }
            }
        }
         $tel_arr = array('return_mobile','return_phone','change_phone','change_mobile');
         foreach ($tel_arr as $t_key) {
            if (isset($data[$t_key]) && !empty($data[$t_key])) {

                $data[$t_key] = substr($data[$t_key], 0, 3) . "*****";
            }
        }
        if (isset($data['return_address'])) {
            $data['return_address'] = str_replace($data['return_addr'], "*****", $data['return_address']);
        }
        if (isset($data['change_address'])) {
            $data['change_address'] = str_replace($data['change_addr'], "*****", $data['change_address']);
        }
       if (isset($data['return_addr'])) {
            $data['return_addr'] = '*****';
        }
        if (isset($data['change_addr'])) {
            $data['change_addr'] = '*****';
        }
    }
    if ($is_view == 1) {
        $key_arr = array('buyer_name','return_name','change_name','return_mobile','return_phone','change_phone','change_mobile','return_address','change_address','return_addr','change_addr');
        $sell_return_code = $data['sell_return_code'];
        foreach ($key_arr as $key) {
            if(!empty($data[$key])){
                $data[$key] ='<span class="like_link" onclick = "show_safe_info(this,\'' . $sell_return_code . '\',\'' . $key . '\')">'.$data[$key].'</span>';
            }
        }
    }
}
function safe_return_package_data(&$data, $is_view = 1) {
     $is_encrypt = load_model('sys/security/OmsSecurityOptModel')->is_encrypt_record($data,'sell_return_package');
     if($is_encrypt==0){
         return false;
     } 
    if ( $is_encrypt==1||( (!isset($data['customer_address_id']) || $data['customer_address_id'] == 0)&&$is_encrypt==2) ) {

        $name_arr = array('buyer_name','return_name');
        foreach($name_arr as $key){
            if (isset($data[$key])) {
                $len = strlen_utf8($data[$key]);
                if ($len < 2) {
                    $data[$key] = '***';
                }else {
                    $data[$key] = substr_utf8($data[$key], 0, 1) . "***";
                }
            }
        }
         $tel_arr = array('return_mobile','return_phone');
         foreach ($tel_arr as $t_key) {
            if (isset($data[$t_key]) && !empty($data[$t_key])) {

                $data[$t_key] = substr($data[$t_key], 0, 3) . "*****";
            }
        }
        if (isset($data['return_address'])) {
            $data['return_address'] = str_replace($data['return_addr'], "*****", $data['return_address']);
        }

       if (isset($data['return_addr'])) {
            $data['return_addr'] = '*****';
        }
    
    }
    if ($is_view == 1) {
        $key_arr = array('buyer_name','return_name','return_mobile','return_phone','return_address','return_addr');
        $return_package_code = $data['return_package_code'];
        foreach ($key_arr as $key) {
            if(!empty($data[$key])){
                $data[$key] .='   &nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick = "show_safe_info(\'' . $return_package_code . '\',\'' . $key . '\')">显示</a>';
            }
        }
    }
}