<?php
/**
 * 数组扩展类
 * @author zengjf
 */
class ArrayEx{
	private $data;
	/**
	 * @var integer $maxLevel 最大深度
	 */
	public  $maxLevel=40;
	/**
	 * 构建对象
	 * @param arrar $data 数组	
	 */
	function __construct(array & $data){
		$this->data=$data;
	}	
	/**
	 * 过滤出key包含在参数中的数组，即返回key包含在$keys中的数组项。
	 * <br>例如:
	 * <br>$this->data=array('a'=>1,'b'=>2,'c'=>3);
	 * <br>1、filter('a','b'),		 返回array('a'=>1,'b'=>2)
	 * <br>2、filter(array('a','b')),返回array('a'=>1,'b'=>2)
	 * @param array|string $keyList  1维数组或字符串列表
	 * @return array  返回结果
	 */
	function & filter($keyList){
		$r=array();
		$num = func_num_args();
		if ($num < 1) return $r;	
		$keyList = func_get_args();
		if (is_array($keyList[0])) return $keyList=$keyList[0];
		foreach ($this->data as $key=>$val)
			if(in_array($key,$keyList) || isset($keyList[$key]))
				 $r[$key]	= $val;
		return $r;
	}
	
	/**
	 * 对数据集进行排序
	 * @param string $col		排序字段
	 * @param array $mode		排序类型 ：
	 * <br>   asc	正向排序
	 * <br>   desc	逆向排序
	 * <br>   nat	自然排序
	 * @return array 返回排序后数组
	 */
	function & sort($col, $mode='asc') {
		$data=$this->data;
		$refer = $result = array ();
		foreach ( $data as $i => $row )	$refer [$i] = & $row [$col];
		switch ($mode) {
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
		foreach ( $refer as $key => $val )	$result [] = & $data [$key];
		return $result;
	}
	/**
	 * 将数组值改变为大写或小写，是array_change_key_case函数的补充
	 * @param array $input	输入的数组  一维数组
	 * @param int $case  大写或小写  默认小写， CASE_LOWER，小写，CASE_UPPER，大写。
	 */
	static function changeCase(array $input ,$case = CASE_LOWER){
		$r=array();
		foreach($input as $row)
		  if($case==CASE_LOWER) $r[]=strtolower($row);
		  else $r[]=strtoupper($row);
		return $r;
	}
	
	/**
	 * 数据集$key_col，$value_col两列数据转换为hash数组 array[$key_col]=value[$value_col]
	 * @param string $key_col	返回结果数组中key的列名	
	 * @param string $value_col 返回结果数组中value的列名，如果查询结果集仅2列，可以不设置此参数。
	 * @return array 返回结果
	 */
	function & toHash($key_col,$value_col=NULL){
			$b=array();
			foreach($this->data as $l){
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
	 * 数据集生成父子关系数组, example:<pre>
	 * pid,	id,		name
	 * 1,	1,		baison;
	 * 1,	11,		cloud;
	 * 11,	111,	alex;
	 * 11,	112,	jack;
	 * 1,	12,		dev;
	 * 12,	121,	tom
	 * baison
	 * 		cloud	-> 		alex,jack
	 * 		dev		-> 		tom <pre>
	 * @param string $id	  		子id的key
	 * @param string $pid	  		父id的key
	 * @param string $chi_key 		子数组的key
	 * @param int|string $root_val	包含父子关系数组的root pid的value
	 * @return array				包含父子关系数组 ，array('id'=>'','child'=>array('id'=>,'pid'=>,..),...）
	 */
	function & toParentChild( $id = 'id', $pid = 'pid', $child_key = 'child', $root_val = 0) {
		$result = array ();
		if (! is_array ( $this->data ))	return $result;
		$refer = array ();
		foreach ( $this->data as $key => $data ) // 创建基于id的数组引用
			$refer [$data [$id]] = & $this->data [$key];
		
		foreach ( $this->data as $key => $data ) {
			$parent_id = $data [$pid];
			if ($root_val == $parent_id) {
				$result [] = & $this->data [$key];
			} else {
				if (isset ( $refer [$parent_id] )) { // 判断是否存在parent
					$parent = & $refer [$parent_id];
					$parent [$child_key] [] = & $this->data [$key];
				}
			}
		}
		return $result;
	}
	
	/**
	 * 数据集生成主从关系数组， 深度< max_level;
	 * @param array $colMap 映射配置 ，列布局，如：
	 * <br>子数组名称('col1,col2,col3','detail'=>('col14,col15,col16','detail12'=>('col120,col121') ) )
	 * @return array 返回主从关系的数组
	 */
	function toMasterDetail(array $colMap) {
		$l_result = array ();
		if(! is_array ( $this->data ) || !is_array($colMap) )	return $l_result;
		//划分，计算checksum 6-1 =5
		foreach($this->data as $row_data){ 
			$t_data=$stack=array();
			$stack[]=& $colMap;
			$stack[]=& $t_data;
			$deep=0;
			
			while(count($stack)>0 && ++$deep < $this->maxLevel){
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
	
	/**已经排序数据集映射为主从关系数组， 深度<max_level;
	 * @param array $data	表数据	已经排序的普通表数据，记录必须为key=>vale对，不能是数字索引。
	 * @param array $colMap 列映射配置 ，列布局，
	 * <br>子数组名称('col1,col2,col3','detail'=>('col14,col15,col16','detail12'=>('col120,col121') ) )
	 * @return array   返回主从关系的数组
	 */	
	static function sortedDataToMasterDetail(array &$data,array $colMap) {
		$result = array ();
		if(! is_array ( $data ) || !is_array($colMap) )	return $result;	
		$cols_conf_1=$colMap;
		$stack=array();
		$deep=0;
		foreach($data as $row_data){ 
			$level=0;
			$stack[]=& $result;
			$stack[]=& $cols_conf_1;
			while(count($stack)>0 && ++$deep < $this->max_level){ 
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
								$cols_conf_1=$colMap; 
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
}
