<?php
/**
 * 用于生成bui的select数据items的方法, 返回json字符串, 注意配置字段的field里面, 第一个是id或code, 第二个是name
 *
 * @author jhua.zuo<jhua.zuo@baisonmail.com>
 * @since 2014-11-12
 * @param string $key 参考ds_get_select
 * @param int $type 参考ds_get_select
 * @param array $where 参考ds_get_select
 * @param boolean $json 是否json_encode, 默认是
 * @return string|array 返回json_encode以后的字符串或者数组
 */
function bui_get_select($key, $type=0, $where=array(), $json=true){
    $result = ds_get_select($key, $type, $where);
    $return = array();
    if(empty($result)||!is_array($result)){
        return $json?json_encode($return):$return;
    }
    $array_keys = array_keys($result[0]);
    foreach($result as $data){
        $return[] = array(
            'value'=>$data[$array_keys[0]],
            'text'=>$data[$array_keys[1]],
        );
    }
    return $json?json_encode($return):$return;
}

/**
 * 用于将key=>value类型的数组，转换成BUI生成下拉列表的text/value数组
 * @author jhua.zuo<jhua.zuo@baisonmail.com>
 * @since 2014-11-14
 * @param array $array
 * @return array
 */
function bui_bulid_select($array){
    $return = array();
    foreach($array as $key=>$value){
        $return[] = array(
            'text'=>$value,
            'value'=>$key,
        );
    }
    return $return;
}