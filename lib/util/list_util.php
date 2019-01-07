<?php
/**
 * 拷贝对象属性
 * @param $src  源对象
 * @param $dest 目标对象
 * @param $properties  需要拷贝的特性数组    如果为空，拷贝源对象全部属性
 */
function copy_obj($src,$dest,$properties=array()){
	if($properties){
		foreach ($properties as $key){
			if(isset($src->$key))  $dest->$key = $src->$key;
		}
	}else{
		foreach ($src as $key=>$val){
			$dest->$key=$val;
		}
	}
}
/**
 * 将查询结果集中$key_col，$value_col两列转换为hash表格式数组 array[$key_col]=value[$value_col]
 * @param array $data		查询结果集
 * @param string $key_col	返回结果数组中key的列名	
 * @param string $value_col 返回结果数组中value的列名，如果查询结果集仅2列，可以不设置此参数。
 * @return array 返回结果数组
 */
function & list_to_hash(array & $data,$key_col,$value_col=NULL){
		$b=array();
		foreach($data as $l){
			foreach($l as $key=>$val){
				if($key == $key_col) $k=$val;
				else if($value_col==$key) $v=$val;
				else  $o= $val;
			}
			if($value_col === null) $b[$k]=$o;
			else $b[$k]=$v;
		}
		return $b;	
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
	 * 普通表格的记录列表数据转换成父子关系数组, id --parent id-->pid , example:<pre>
	 * table pid,id,name->1,1,baison;1,11,cloud;11,111,alex;11,112,jack;1,12,dev;12,121,tom.<br>
	 * baison
	 * 	cloud	-> 		alex,jack
	 * 	dev		-> 		tom <pre>
	 * @param array $table_data  	普通表格的记录列表，array(array('id'=>,..),array('id'=>,..))
	 * @param string $id	  		子id的key
	 * @param string $pid	  		父id的key
	 * @param string $chi_key 		子数组的key
	 * @param int|string $root_val	包含父子关系数组的root pid的value
	 * @return array				包含父子关系数组 ，array('id'=>'','child'=>array('id'=>,'pid'=>,..),...）
	 */
	function table_to_parent_child(array $table_data, $id = 'id', $pid = 'pid', $chi_key = 'child', $root_val = 0) {
		$result = array ();
		if (! is_array ( $table_data ))	return $result;
		$refer = array ();
		foreach ( $table_data as $key => $data ) // 创建基于id的数组引用
			$refer [$data [$id]] = & $table_data [$key];
		
		foreach ( $table_data as $key => $data ) {
			$parent_id = $data [$pid];
			if ($root_val == $parent_id) {
				$result [] = & $table_data [$key];
			} else {
				if (isset ( $refer [$parent_id] )) { // 判断是否存在parent
					$parent = & $refer [$parent_id];
					$parent [$chi_key] [] = & $table_data [$key];
				}
			}
		}
		return $result;
	}
	/**已经排序的普通表格的记录列表数据映射为主从关系数组， 深度<TableMapper::$master_detail_deep;
	 * @param array $data	表数据	已经排序的普通表数据，记录必须为key=>vale对，不能是数字索引。
	 * @param array $cols_conf 映射配置 ，包括列，子数组名称('col1,col2,col3','detail'=>('col14,col15,col16','detail12'=>('col120,col121') ) )
	 * @return array 包含 主从关系的数组
	 */	
	function sorted_table_to_master_detail(array &$data,array $cols_conf) {
		$result = array ();
		if(! is_array ( $data ) || !is_array($cols_conf) )	return $result;	
		$cols_conf_1=$cols_conf;
		$stack=array();
		$deep=0;
		foreach($data as $row_data){ 
			$level=0;
			$stack[]=& $result;
			$stack[]=& $cols_conf_1;
			while(count($stack)>0 && ++$deep < TableMapper::$master_detail_deep){ 
				$cnt=count($stack);
				$conf=& $stack[--$cnt];
				$row=& $stack[--$cnt];
				array_splice($stack,$cnt);
				foreach($conf as $key => $val){
					if($key==='_fa_crc_') continue;
					if($key===0 ){
						if(is_string($val))	$cols=explode(',',$val);
						else $cols=& $val;
						$checksum='';
						$row_d=array();
						for($i=0;$i<count($cols);++$i){
							$idx=$cols[$i];
							$checksum .=$row_d[$idx]=$row_data[$idx];
						}					
						$cs=crc32($checksum);
						if(! isset($conf['_fa_crc_']) || $conf['_fa_crc_']!==$cs){
							if($level===0){
								$deep=0;
								$stack=array();
								$cols_conf_1=$cols_conf; 
								$result[]= $row_d;
								$cols_conf_1['_fa_crc_']=$cs;
								$stack[]=& $result;
								$stack[]=& $cols_conf_1;
							}else{
								$row[]= $row_d;
								$conf['_fa_crc_']=$cs;
							}
						}
					}else{
						$len=count($row)-1;
						if(! isset($row[$len][$key])) $row[$len][$key]=array();
						$stack[] = & $row[$len][$key];
						$stack[]=  & $conf[$key];
						++ $level;					
					}
				}
			}
		}
		return $result;
	}
	/**普通表格的记录列表数据映射为主从关系数组， 深度<TableMapper::$master_detail_deep;
	 * @param array $data	表数据	普通表数据，记录必须为key=>vale对，不能是数字索引。
	 * @param array $cols_conf 映射配置 ，包括列，子数组名称('col1,col2,col3','detail'=>('col14,col15,col16','detail12'=>('col120,col121') ) )
	 * @return array 包含 主从关系的数组
	 */
	function table_to_master_detail(array &$data,array $cols_conf) {
		$l_result = array ();
		if(! is_array ( $data ) || !is_array($cols_conf) )	return $l_result;
		//划分，计算checksum 6-1 =5
		foreach($data as $row_data){ 
			$t_data=$stack=array();
			$stack[]=& $cols_conf;
			$stack[]=& $t_data;
			$deep=0;
			
			while(count($stack)>0 && ++$deep < TableMapper::$master_detail_deep){
				$len=count($stack);
				$row= & $stack[$len-1];
				$conf=& $stack[$len-2];
				array_splice($stack,$len-2);
				
				foreach($conf as $key=>$val){
					if($key===0){
						if(is_string($val))	$cols=explode(',',$val);
						else $cols=& $val;
						$checksum='';
						for($i=0;$i<count($cols);++$i){
							$k=$cols[$i];
							$row[$k]=& $row_data[$k];
							$checksum .= $row[$k];
						}
						$row['_fa_crc_']=sprintf('_fa_crc_%u',crc32($checksum));
					}else{
						$row[$key]=array();
						$stack[]=& $conf[$key];
						$stack[]=& $row[$key];
					}
				}
			}
			$l_result[]= $t_data;
		}
		unset($conf);unset($checksum);unset($deep);unset($cols);
		//归并 	11-6  =5
		$t_result = array ();
		foreach($l_result as $row_data){
			$stack=array();
			$stack[]=& $t_result;
			$stack[]=& $row_data;
			
			while(count($stack)>0){
				$len=count($stack);
				$row=& $stack[$len-1];
				$t_row=& $stack[$len-2];
				array_splice($stack,$len-2);
				
				$crc=$row['_fa_crc_'];
				if(! isset($t_row[$crc])){
					$t_row[$crc]=array();
				}	
				$t_row= & $t_row[$crc];
				
				foreach($row as  $key=>$val){
					if($key != '_fa_crc_' && ! is_array($val)){
						$t_row[$key]=$val;
					} else if(is_array($val)){
						if(! isset($t_row[$key]))	$t_row[$key]=array();
						$stack[]=& $t_row[$key];
						$stack[]=& $row[$key];
					}
				}
			}
		}
		unset($crc);
		//去除checksum键  14-11  =3
		$result=array();
		foreach($t_result as $row_data){
			$stack=$t_data=array();
			$stack[]=& $t_data;
			$r_data=array('_fa_crc_1'=>$row_data);
			$stack[]=& $r_data;
			while(count($stack)>0){
				$len=count($stack);
				$row=& $stack[$len-1];
				$t_row=& $stack[$len-2];
				array_splice($stack,$len-2);
				
				if(is_array($row)){
					list($key,$val)=each($row);
					if(strncmp($key,'_fa_crc_',8)==0 && is_array($val)){
						foreach(array_values($row) as $row){
							$t_row[]=array();
							$t_row_d=& $t_row[count($t_row)-1];
							foreach($row as $key=>$val){
								if(is_array($val)) {
									$t_row_d[$key]=array();
									$stack[]=& $t_row_d[$key];
									$stack[]=& $row[$key];
								}else $t_row_d[$key]=$val;
							}
						}
					}
				}
				
			}
			$result[]=$t_data[0];			
		}
		return $result;
	}
