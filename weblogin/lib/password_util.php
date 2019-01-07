<?php
function get_passwod($str){
    $key_str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    $val_str = strrev($key_str);
    $arr_key =  str_split($key_str);  
    $arr_val =  str_split($val_str);  
    $arr =array_combine($arr_key,$arr_val);
    $str_arr = str_split($str);
    $new_str = '';
    foreach($str_arr as $val){
        $new_str .= $arr[$val];
    }
    return  base64_decode($new_str);
}
