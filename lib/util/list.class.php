<?php

/**
 * 将包含主从关系普遍数组转换成树型数组
 * @param $list  	包含主从关系普遍数组
 * @param $pk	  	关键字段
 * @param $pid	  	主关键字段
 * @param $child 	结果中从数组的key
 * @param $root	  	关键字段根值
 * @return array	树型数组
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0) {
	$tree = array ();
	if (! is_array ( $list ))	return $tree;
	$refer = array ();
	foreach ( $list as $key => $data ) // 创建基于主键的数组引用
		$refer [$data [$pk]] = & $list [$key];
	
	foreach ( $list as $key => $data ) {
		$parentId = $data [$pid];
		if ($root == $parentId) {
			$tree [] = & $list [$key];
		} else {
			if (isset ( $refer [$parentId] )) { // 判断是否存在parent
				$parent = & $refer [$parentId];
				$parent [$child] [] = & $list [$key];
			}
		}
	}
	return $tree;
}

/**
 * 对查询结果集进行排序
 * @param array $list		数据数组
 * @param string $field		排序字段
 * @param array $sortby		排序类型   asc正向排序 desc逆向排序 nat自然排序
 * @return array 排序后数组
 */
function list_sort_by($list,$field, $sortby='asc') {
	if (! is_array ( $list ))	return false;
	$refer = $result = array ();
	foreach ( $list as $i => $data )	$refer [$i] = &$data [$field];
	switch ($sortby) {
		case 'asc' : // 正向排序
			asort ( $refer );
			break;
		case 'desc' : // 逆向排序
			arsort ( $refer );
			break;
		case 'nat' : // 自然排序
			natcasesort ( $refer );
			break;
	}
	foreach ( $refer as $key => $val )	$result [] = &$list [$key];
	return $result;
}

/**
 * 搜索数据列表
 * @param array $list 	数据列表
 * @param array|string $condition 查询条件  支持 array('name'=>$value) 或者 name=$value
 * @return array 搜索到的数据
 */
function list_search($list,$condition) {
    if(is_string($condition))	parse_str($condition,$condition);
    $result = array();
    foreach ($list as $key=>$data){
        $valid   =   false;
        foreach ($condition as $field=>$value){
            if(isset($data[$field])) {
                if(0 === strpos($value,'/')) {	//regex
                    $valid   =   preg_match($value,$data[$field]);
                }elseif($data[$field]==$value){
                    $valid = true;
                }
            }
        }
        if($valid)  $result[]     =   &$list[$key];
    }
    return $result;
}

