<?php
require_lib ( 'business_util', true );
/**
 * 
 * 获取带权限的sql，返回sql
 * @param unknown_type $sql
 * @param unknown_type $type_arr array(类型=>表别名,"shop"=>"t1","store"=>"t2")
 */
function get_privilege_sql($sql,$type_arr){
    $sql = strtolower($sql);
    if(!strpos($sql,'where')){
        $sql .= " where 1=1";
    }
    $user = get_user_by_id($_SESSION['user_id']);
    
    foreach ($type_arr as $key=>$table){
        if($key == "shop"){
            if(isset($user['shop_code']) && $user['shop_code'] != ""){
                $shop_code = deal_strs_with_quote($user['shop_code']);
                $sql .= " and ".$table.".shop_code in (".$shop_code.")";
            }
        }
        if($key == "store"){
            if(isset($user['store_code']) && $user['store_code'] != ""){
                $store_code = deal_strs_with_quote($user['store_code']);
                $sql .= " and ".$table.".store_code in (".$store_code.")";
            }
        }
    }

    return $sql;
}