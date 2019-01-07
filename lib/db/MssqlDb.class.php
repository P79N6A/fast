<?php
include_once ROOT_PATH."conf/crm_config.php";
class MssqlDb implements IRequestTool {
    private $conf;
	private $pdo;
	private $sth;
	private $dbid;
	private $sel_db;		//当前选择主数据库
	
	private $col_info_prefix='pdodb/col_info/';	//table col info cache key prefix 
	private $nest_trans=false;					//nested transaction support
	//end private
	
	public 	$options;
	/**
	 * @var integer  get_*返回数组row的记录数组key是数字，还是字符串，默认仅为字符串。
	 * <br> -1：仅为数字，
	 * <br>  0：仅为字符串，
	 * <br>  1：同时包含数字和字符串；
	 */
	public	$row_key_is=self::KEY_TEXT;
	const   KEY_DIGIT=-1; //for $row_key_is
	const   KEY_TEXT=0;
	const   KEY_BOTH=1;
	
	public  $adapter;
	
	public  $dbtype;
	public  $QUOTE_CHAR = '`';
	
	/**
	 * @var string 上次执行的sql
	 */
	public	$last_query;
	/**
	 * @var array 上次执行的数值
	 */
	public	$last_values;

	private $cache_valid=false;
	
	public	$next_no_execute=false;
	
	//map
	protected $cols=array();
	
	public 	 $quote_col=true;
	
	protected $tab_alias=NULL;
	
    function __construct(){
        
        $this->options = array(
			//PDO::ATTR_CASE				=> PDO::CASE_LOWER,
			PDO::ATTR_ERRMODE			=> PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_ORACLE_NULLS		=> PDO::NULL_NATURAL,
			PDO::ATTR_STRINGIFY_FETCHES	=> false
			);
		$name = $GLOBALS['context']->mssql['mssql_name'];
        $this->select_db($name,isset($this->conf['region']) ?  $this->conf['region'] : NULL);
    }
	
	function select_db($tname_or_sel_db,$region=NULL){
		if($region !== NULL) $this->sel_db=$this->get_db($tname_or_sel_db,$region); 
		else $this->sel_db=$tname_or_sel_db;
		
		return $this->sel_db;
	}	
	
    function get_readonly(& $sql){
		return strncasecmp($sql,"select",6)==0 || strncasecmp($sql,'desc',4)==0 || strncasecmp($sql,'show',4)==0;
	}
	
	protected function get_host($readonly,$db,& $host,& $user,& $pwd){
	    $host = $GLOBALS['context']->mssql['mssql_host'];
        $user = $GLOBALS['context']->mssql['mssql_user'];
        $pwd = $GLOBALS['context']->mssql['mssql_pass'];		
	}
	
	function get_dsn(array & $conf,array & $options) {
	    if(PATH_SEPARATOR==':'){
	        return "dblib:host=".$conf['host'].";dbname=".$conf['name'];
	    }else{
	        return "sqlsrv:Server=".$conf['host'].";Database=".$conf['name'];
	    }
	}
	
	function get_pdo($readonly=true){
		if(! $this->sel_db) throw new Exception(lang('db_err_dbname_null'));
		
		$host=$user=$pwd=NULL;
		$this->get_host($readonly,$this->sel_db,$host,$user,$pwd);
		
		$dbid=crc32("{$host};{$this->sel_db}@{$user}@{$pwd}");
		
		if($this->pdo && $dbid===$this->dbid)	return $this->pdo;
		
		$this->dbid=$dbid;
		$conf=$this->conf;
		$conf['host']=$host;
		$conf['name']=$this->sel_db;
		
		$this->cache_valid=false;	//clear info cache
		
		$dsn=$this->get_dsn($conf,$this->options);
		
		$this->pdo = new PDO($dsn, $user, $pwd);
		
		//$this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		//$this->init_pdo($this,$this->pdo);
		return $this->pdo;
	}
	
	function prepare($sql,$ro_hint=NULL){
    	if($ro_hint===NULL){
    		$sql=trim($sql);
    		$readonly=$this->get_readonly($sql);
    	}else $readonly= $ro_hint=== true ? true :false;
    	
		$this->last_query =& $sql;
		if($this->next_no_execute) return true;
		
		try {
			$this->get_pdo($readonly);
		} catch (Exception $e) {
			$err=lang('db_err_connect').$this->conf['name'];
			if(isset($GLOBALS['context'])) $GLOBALS['context']->log_error($err);
			throw new Exception($err);			
		}		
		try {
			$this->sth = $this->pdo->prepare($sql);
		} catch (Exception $e) {
			$err=lang('db_err_prepare')."[{$e->getCode()}],{$e->getMessage()}";
			$GLOBALS['context']->log_error($err.','.$this->last_query);
			throw new Exception($err);
		}
		return $this->sth;
	}
	
	function execute(array $values=array()){
		$this->last_values = & $values;
		if($this->next_no_execute){
			$this->next_no_execute=false;
			return true;
		}
		if(! $this->sth) return false;
		if(isset($GLOBALS['context']) && $GLOBALS['context']->is_debug()){
		 	$GLOBALS['context']->log_debug('query sql：'.$this->last_query."\n".print_r($values,true));
		 	reset($values);
		}
		try {
			$this->sth->closeCursor();
			if(count($values)>0){
				foreach($values as $key=>$val){
					if(is_bool($val))	$bmode=PDO::PARAM_BOOL;
					elseif(is_int($val) || is_float($val)) $bmode=PDO::PARAM_INT;
					elseif(is_string($val)) $bmode=PDO::PARAM_STR;
					elseif(is_null($val)) $bmode=PDO::PARAM_NULL;
					else{
						//pump first value
						if( is_object( $val ) ) $val=get_object_vars($val);
						if ( is_array ( $val ) ){
							reset($val);
							$val = count ( $val ) > 0  ? $val[key($val)] : NULL;
						}						
						if(isset($GLOBALS['context']))
							$GLOBALS['context']->log_error('values item is array or object,query sql：'.$this->last_query."\n".print_r($values,true));
						
						if(is_bool($val))	$bmode=PDO::PARAM_BOOL;
						elseif(is_int($val) || is_float($val)) $bmode=PDO::PARAM_INT;
						elseif(is_string($val)) $bmode=PDO::PARAM_STR;
						elseif(is_null($val)) $bmode=PDO::PARAM_NULL;
						else 	$bmode=PDO::PARAM_STR;					
					} 
					if(is_string($key)) $this->sth->bindValue($key, $val, $bmode);
					else $this->sth->bindValue($key+1, $val, $bmode);
				}
			}	
			return $this->sth->execute();
		} catch (Exception $e) {
			$err="[{$e->getCode()}],{$e->getMessage()}";
			if(isset($GLOBALS['context'])){
				$err=lang('db_err_execute').$err;
				$GLOBALS['context']->log_error($err.','.$this->last_query);
				throw new Exception($err);
			}else	throw new Exception($err);
		}		
	}

	function query($sql,$values=array(),$ro_hint=NULL){
		$this->prepare($sql,$ro_hint);
		return $this->execute($values);
	}
	
	function get_all($sql,$values=array(),$ro_hint=NULL){
		return $this->get_limit($sql,$values,false,false,$ro_hint);
	}
	
	function get_limit($sql,$values=array(),$limit=20, $offset=0,$ro_hint=NULL){
		if($limit !== false && $limit > 1){
		    $offset = intval($offset);
		    $e = $offset + intval($limit);
		    
		    preg_match_all("/order\s+by\s+([\s\S]*?)$/i", $sql, $order);
		    
		    $order = $order[0][0];
		    
		    $order_id = preg_replace('/order\s+by\s+/i', '', $order, 1);
		    $order_id = preg_replace('/\s+desc/i', '', $order_id, 1);
		    $order_id = preg_replace('/\s+asc/i', '', $order_id, 1);

		    $sql1 = preg_replace('/select\s+/i', 'select top '.$limit." ", $sql, 1);
		    
		    $sql1 = str_replace($order, '', $sql1);

		    $sql2 = preg_replace('/select\s+/i', 'select top '.$offset.' '.$order_id.' ' , $sql, 1);
		    
		    $sql2 = preg_replace('/select([\s\S]*?)from/i', 'select top '.$offset.' '.$order_id.' from ' , $sql, 1);
		    
            if(false!==strstr($sql1, 'where')){
                $sql = "{$sql1} and {$order_id} not in ($sql2) {$order}";
            }else{
                $sql = "{$sql1} where {$order_id} not in ($sql2) {$order}";
            }

		    if(count($values) != 0){
		        $values_temp = array();
		        foreach ($values as $k => $v){
		            $count = substr_count($sql,$k);
		            for($i=0;$i<$count;$i++){
		                $r = rand(1000000, 9999999);
		                $sql = preg_replace('/'.$k.'/', ":".$r.$i, $sql,1);
		                $values_temp[":".$r.$i] = $v;
		            }
		        }
		        $values = $values_temp;
		    }
		}

		if($values === NULL) $values=array();
		if($this->next_no_execute){
			 $this->query($sql,$values,$ro_hint);
			 return true;
		}
		if(! $this->query($sql,$values,$ro_hint)) return false;
		if($this->row_key_is<0) return $this->sth->fetchAll(PDO::FETCH_NUM);
		else if($this->row_key_is>0) return $this->sth->fetchAll();
		else	return $this->sth->fetchAll(PDO::FETCH_ASSOC);
	}
	
	function get_value($sql,$values=array(),$ro_hint=NULL){
		$row_key_is=$this->row_key_is;
		$this->row_key_is=self::KEY_DIGIT;
		$result=$this->get_row($sql,$values,$ro_hint);
		$this->row_key_is=$row_key_is;
		if($result && count($result)>0)	return $result[0];
		else  return false;
	}
	
	function get_row($sql,$values=array(),$ro_hint=NULL){
		$result=$this->get_limit($sql,$values,1,0,$ro_hint);
		if($result && count($result)>0)	return $result[0];
		else  return $result;
	}
	
	function get_col($sql,$values=array(),$limit=20, $offset=0 ,$ro_hint=NULL){
		$row_key_is=$this->row_key_is;
		$this->row_key_is=self::KEY_DIGIT;
		$data=$this->get_limit($sql,$values,$limit,$offset,$ro_hint);
		$this->row_key_is=$row_key_is;	
		$result=array();
		foreach($data as $row)	$result[]=$row[0];
		return $result;
	}
	
    function get_num($sql, $values = array(), $active_filter = false) {
        if (strstr($sql, "group by")) {
            $this->query($sql, $values);
            $data = $this->get_value("select found_rows()", $values);
        } else {
            $sql = preg_replace('/select([\s\S]*?)from/i', 'select count(*) from', $sql, 1);
            
            $sql = preg_replace('/order\s+by\s+([\s\S]*?)$/i', '', $sql, 1);
            $data = $this->get_value($sql, $values, $active_filter);
        }

        if ($data == "")
            $data = 0;
        if ($data > -1) {
            return $data;
        } else {
            throw new Exception('查询失败');
        }
    }
    
    function page($sql, $sql_value = array()) {
        $request = $GLOBALS['context']->request;
        $total_field = array();
        if (!empty($_COOKIE['cookie_page_num']))
            $request['num'] = $_COOKIE['cookie_page_num'];
        if (!array_key_exists('pageNum', $request))
            $request['pageNum'] = 1;
        if (!array_key_exists('num', $request) || empty($request['num'])){
            if(isMobile()){
                $request['num'] = 10;
            }else{
                $request['num'] = 15;
            }
        }
        if (!array_key_exists('size', $request))
            $request['size'] = 0;
        if (!array_key_exists('td_num', $request))
            $request['td_num'] = 0;

        $count = $this->get_num($sql, $sql_value);

        $countPage = ceil($count / $request["num"]); //分页总数

        if ($request['pageNum'] < 1)
            $request['pageNum'] = 1;
        if ($request['pageNum'] > $countPage)
            $request['pageNum'] = $countPage;

        if ($count == 0) {
            $data = array();
            $countPage = 0;
        } else {
            /****************排序******************/
            if (isset($request['range_value']) && $request['range_value'] != "") {
                $sql = preg_replace('/order\s+([\s\S]*)/i', '', $sql, 1);
                $sql .= " order by " . $request['range_value'] . " " . $request['range_order'];
            }
            /****************排序******************/
            $data = $this->get_limit($sql, $sql_value, $request['num'], ($request['pageNum'] - 1) * $request["num"]);

            /****************统计******************/
            if (isset($request['total_field']) && $request['total_field'] != "") {
                for ($i = 0; $i < count($request['total_field']); $i++) {
                    $sql = preg_replace("/select(.*?)from/i", "select sum(" . $request['total_field'][$i]['key'] . ") from", $sql, 1);
                    $total_value = $this->db->get_value($sql);
                    $total_field[] = array("key" => $request['total_field'][$i]['value'], "value" => $total_value);
                }
            }
            /****************统计******************/
        }

        return array('count' => $count, 'data' => $data, 'num' => $request['num'], 'countPage' => $countPage, 'pageNum' => $request['pageNum'], 'total_field' => $total_field, 'size' => $request['size'], "td_num" => $request['td_num']);
    }
    
    function insert($table,$data,$mutil_ingore_err=false){
		if (! $data || !$table || (! is_object($data) && ! is_array($data))) return false;
		
		
		if(is_object($data)) $data=get_object_vars($data);
		else if(is_array($data) && isset($data[0]) && is_object($data[0])){
			$d=$data;
			$data=array();
			foreach($d as $row){
				if(is_object($row))	$data[]=get_object_vars($row);
			}
				
			unset($d);
		}
		$cols = $this->get_cols(); 
		if(! $cols) $cols=$this->query_for_cols($table);
					
		$is_mutil=isset($data[0]) && is_array($data[0]);
		if($is_mutil)		$row=& $data[0];
		else $row=& $data;
		
		$result=array();
		if($is_mutil){
			foreach($data as $row){
				$fields =$params = $values= array();
				foreach($row as $key => $val){
					if($cols!==false && ! isset($cols[$key]) && ! in_array($key,$cols))
						continue;	
					$fields[] = $key;
					$params[] =$key_a= ":{$key}";
					$values[$key_a]=$val;
				}	
				$sql = "INSERT INTO ".table($table)." (".implode(', ', $fields).") VALUES (".implode(', ', $params).")";
				$this->db->prepare($sql,false);				
				if($mutil_ingore_err){
					$e0=error_reporting(0);
					try{
						$dbresult=@$this->execute($values);
						if($dbresult===false)	$result[]=$row;
					}catch (Exception $e){
						$result[]=$row;
						if(isset($GLOBALS['context']))
							$GLOBALS['context']->log_error('TableMapper Mutil Insert Error Ignore:'. $e->getMessage());
					}
					error_reporting($e0);
				}else{
					$dbresult = $this->db->execute($values);	
					if($dbresult===false){
						$result[]=$row;
						break;
					}	
				}
			}
			return $dbresult? true : $result;
		}else{
			$fields =$params = $values= array();
			foreach($row as $key => $val) {
				if($cols!==false && ! isset($cols[$key]) && ! in_array($key,$cols))
					continue;
				$fields[] = $key;
				$params[] =$key_a= ":{$key}";
				$values[$key_a]=$val;
			}
			$sql = "INSERT INTO {$table} (".implode(', ', $fields).") VALUES (".implode(', ', $params).")";
			$this->prepare($sql,false);
			
			if($this->execute($values)) return true;
			else {
				$result[]=$row;
				return $result;
			}
		} 
	}
	
	function update($table,$data, $where,array $is_func_key=NULL){
		if (! $data || !$table || (! is_object($data) && ! is_array($data)) ) return false;
		if(is_object($data)) $data=get_object_vars($data);
		
		if(is_array($data)){
			if(empty($data)) return false;
			
			$cols=& $this->get_cols(); 
			if(! $cols) $cols=$this->query_for_cols($table);	
			$fields =$values= array();
			foreach($data as $key => $val){ 
				if( $cols && ! isset($cols[$key]) && ! in_array($key,$cols))
					continue;
			if(is_string($val) && $is_func_key!==NULL  && in_array($key,$is_func_key) ) {  //|| ($is_func_key===NULL && $this->contain_func($val,$m) )
				 $fields[]=$key ."=".$val;
			}else{
					$fields[]=$key .'=?';
					$values[]=$val;
				}
			}
		}else if(!$data ||! is_string($data) ) return false;
			
		$single=false;
		if (is_array($where)) $this->get_where_by_hash($where,$values,true,$single);	
		
		$sql = "UPDATE {$table} SET ";
		if(is_string($data)) $sql .= $data; 
		else $sql .= implode(', ', $fields);

		$sql .=  $where ? " WHERE {$where}": '';
        
		$this->prepare($sql,false);
			
		if($this->execute($values)) return true;
		else {
			$result[]=$values;
			return $result;
		}
		return $this->query($sql,$values,false);	 	
	}
	
	function delete($table,$where){
		if (!$table) return false;	//omit delete from [table] 
		$values=array();	
		$single=false;
		if (is_array($where)) 	$this->get_where_by_hash($where,$values,true,$single);	
		$sql = "DELETE FROM  ".$table ;
		if($this->tab_alias) $sql .= $this->alias($this->tab_alias);
		$sql .= ($where ? "  WHERE  {$where}" : '');
		return $this->query($sql,$values,false);	  	
	}
	
	/***transcation***/
	private $trans_count=0;		//nested transcation ref counter,when ==0,execute transaction op.
	function begin_trans(){	
		$this->get_pdo(false);
		if(! $this->nest_trans && $this->trans_count++ >0) return;
		$this->pdo->beginTransaction();	
	}
	function commit(){
		if($this->pdo){
			if(! $this->nest_trans && --$this->trans_count >0) return;
			$this->pdo->commit();	
		} 
	}
	function rollback(){
		if($this->pdo){
			if(! $this->nest_trans && --$this->trans_count >0) return;
			$this->pdo->rollBack();
		} 
	}    
	function transaction($closure){
		try{
			$this->trans_begin();
			$param=func_get_args();
			array_shift($param);
			call_user_func_array($closure,$param);
			$this->commit();
		}catch (Exception $e){
			$this->rollback();
			throw $e;
		}
	}	
	/***end transcation***/
	
	function query_for_cols($table){
		$cols_info=$this->query_col_info($table);
	    foreach ($cols_info as $row)  	$cols[]=current($row);
        return 	$cols;			
	}
	
	function query_col_info($table){
		$key=$this->col_info_prefix.strtolower($table);
		if(! isset($GLOBALS['context']->cache)) $cache=NULL;
		else	$cache=$GLOBALS['context']->cache;
		 	
		if($cache && $this->cache_valid){
			$result=$cache->get($key);
			if($result) return $result;
		}
		$sql= $this->get_col_info_sql($table);
		$result=$this->get_all($sql,array(),false,true);
		$this->cache_valid=true;
		
		if($cache && $result) $cache->set($key,$result);	//cache table col info
		return $result;
	}
	
	function get_col_info_sql($table){
	    return "select name from syscolumns where id=object_id('{$table}')";
	}
	
	//得到当前cols字段列表数组
	private  function & get_cols(){
		$a=array();
		foreach($this->cols as $k=>$v)
			if(is_int($k)) $a[]=$v;
			else $a[]=$k;
		return $a;
	}
	
	function quote($str){
		if(! $str)  return $str;
		return $str[0] === $this->QUOTE_CHAR || $str[strlen($str)-1] === $this->QUOTE_CHAR ?
			$str : $this->QUOTE_CHAR . $str . $this->QUOTE_CHAR;
	}
	
	function alias($alias){
		return ' '.$alias;
	}
	
	protected function contain_op_1($string){
		return  preg_match('/(IS\sNULL|IS\sNOT\sNULL)/i',$string);
	}		
	protected function contain_op_2($string,&$matches){
		return  preg_match('/(.*)(!=|>|<|=|\sNOT\sIN|\sNOT|\sLIKE|\sIN)/iU',$string,$matches);
	}	
	protected function contain_func($string,&$matches){
		return  preg_match('/\+|=|\*|\/|\(.*\)|NULL/i',$string,$matches);
	}
	
	protected function get_where_by_hash(&$where,&$values,$and,&$single){
		$clause = array();
		foreach ($where as $key => $val) {
			if($this->contain_op_1($key)) $clause[] = " {$key}" ;
			else{
				$matches=array();
				if($this->contain_op_2($key,$matches)){
					$op=trim(substr($key,strlen($matches[1])));
					$key=trim($matches[1]);
					if(strpos($key,'.')!==false){
						$key_a=explode('.',$key);
						$key_b=array();
						foreach($key_a as $k_row) $key_b[]=$this->quote($k_row);
						$key=implode('.',$key_b);
					}else $key=$key;


					$op=strtoupper($op);
					if(strpos($op,'IN')!==false && ( strcmp($op,'IN')==0 || strcmp($op,'NOT IN')==0 || preg_match('/NOT\sIN/',$op)) ){
                        $isstr=is_string($val);
						if($isstr) $val=trim($val);
						if($isstr &&  $val[0]==='(' && $val[strlen($val)-1]===')'){ //is sub select,omit,MUST escape select clause's value
                        	$clause[] =" {$key} {$op} {$val}";
                        }else{  
                        	if($isstr &&  strpos($val,',')!==false)  $val=explode(',',$val);                
							if(is_array($val) && count($val)>0){
								$opts =array();
								foreach($val as $item){
									$opts []='?';
									$values[]=$item;
								}
								$clause[] =" {$key} {$op} (". implode(',', $opts) .')';
							}else{							//in but val is not array,rewrite equal
								$clause[] = " {$key} = ?" ;
								$values[]=$val;
							}
                        }
					}else{
						$clause[] = " {$key} {$op}  ?" ;
						$values[]=$val;
					}	
				}else{
					if(strpos($key,'.')!==false){
						$key_a=explode('.',$key);
						$key_b=array();
						foreach($key_a as $k_row) $key_b[]=$this->quote($k_row);
						$key=implode('.',$key_b);
					}else $key=$key;					
					 $clause[] = " {$key} = ?" ;
					 $values[]=$val;
				}
			} 
		}
		$single=false;
		if($clause){
			$single=count($clause)==1;
			$where=implode(($and ? ' AND ' : ' OR '), $clause);
		} 
		else $where=NULL;		
	}
	
	static function register($prop) {
		$mssqldb = new MssqlDb();
		return $mssqldb;
	}
}