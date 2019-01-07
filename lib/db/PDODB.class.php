<?php
require_once ROOT_PATH.'boot/req_inc.php';

class PDODB implements IDB,IRequestTool{
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

	const DBTYPE_MYSQL=0;
	const DBTYPE_ORACLE=1;
	/**
	 * @var int 数据库类型
	 */
	public  $dbtype=self::DBTYPE_MYSQL;
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

	/**
	 * @var array $dbconf 数据库参数，包括host,user,pwd,name,type,port,charset 。 oracle 还包括:tns
	 */
	function __construct(array $dbconf){
		$this->options = array(
			//PDO::ATTR_CASE				=> PDO::CASE_LOWER,
			PDO::ATTR_ERRMODE			=> PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_ORACLE_NULLS		=> PDO::NULL_NATURAL,
			PDO::ATTR_STRINGIFY_FETCHES	=> false
			);

		$this->set_conf($dbconf);

	}
	function set_conf(array $conf){
		$this->conf=$conf;
		$t=isset($this->conf['type']) ?  $this->conf['type']: 'mysql' ;
		if($t=='mysql')
			$this->dbtype=self::DBTYPE_MYSQL;
		elseif($this->dbtype=='oracle' || $this->dbtype=='oci')
			$this->dbtype=self::DBTYPE_ORACLE;
		else $this->dbtype=self::DBTYPE_MYSQL;

		if($this->dbtype==self::DBTYPE_MYSQL){
			require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'PDOAdapterMysql.class.php' ;
			$this->adapter=new PDOAdapterMysql();
		}else if($this->dbtype==self::DBTYPE_ORACLE){
			require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'PDOAdapterOci.class.php';
			$this->adapter=new PDOAdapterOci();
		}else{
			$GLOBALS['context']->put_error(10000,lang('db_err_invalid_type').$t);
			throw new Exception(lang('db_err_invalid_type') . $t);
		}
		$this->adapter->db=$this;
		$this->QUOTE_CHAR= $this->adapter->QUOTE_CHAR;
		$this->nest_trans= $this->adapter->nest_trans;
		$this->dbid=NULL;
		$this->select_db($this->conf['name'],isset($this->conf['region']) ?  $this->conf['region'] : NULL);
	}
	function alias($alias){
		return $this->adapter->alias($alias);
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

		$dsn=$this->adapter->get_dsn($conf,$this->options);

		$this->pdo = new PDO($dsn, $user, $pwd, $this->options);
		$this->adapter->init_pdo($this,$this->pdo);
		return $this->pdo;
	}
	/**
	 * 得到$readonly,$db对应的数据库服务器，PDODB通过数据库名称，读写参数找到实际数据库服务器。
	 * @param bool $readonly	是否仅读
	 * @param string $db		数据库名称
	 * @param string $host		数据库服务器
	 * @param string $user		数据库用户名
	 * @param string $pwd		数据库密码
	 */
	protected function get_host($readonly,$db,& $host,& $user,& $pwd){
		$host= $this->conf['host'] ;
		$user= $this->conf['user'];
     	$pwd = $this->conf['pwd'];
	}
	/**
	 * 得到$table,$region对应的数据库名称，PDODB通过返回的数据库名称找到实际数据库服务器，实现分库分表时需要重载此函数。
	 * @param $table			表名称
	 * @param $region_value		分库分表关键字
	 */
	protected function get_db($table,$region_value=NULL){
	 	return $this->conf['name'];
	}
	/**
	 * 选择当前主数据库
	 * @param string $tname_or_sel_db 	需要选择对应的表名或数据库名称，如果$region==NULL，为数据库名称，否则为表名
	 * @param string $region  			如果从表名选择数据库，对应的分库分表的关键值，默认不使用
	 */
	function select_db($tname_or_sel_db,$region=NULL){
		if($region !== NULL) $this->sel_db=$this->get_db($tname_or_sel_db,$region);
		else $this->sel_db=$tname_or_sel_db;

		return $this->sel_db;
	}
	/**
	 * 得到表对应的包括数据库名的完整表名。
	 * @param string $table			表名
	 * @param string $region  		对应的表的分库关键值，无效
	 * @return string 返回包括数据库名的完整表名。
	 */
	function table($table,$region=NULL){
		$table=trim($table);
		if($table[0]=='(') return $table;		//if sql clause () ,return

		if($this->dbtype==self::DBTYPE_ORACLE){
		   $name = $this->conf['user'];
		}else{
			$name=$this->sel_db;
			//if($region===NULL) $name=$this->sel_db;
			//else $name=$this->get_db($table,$region);
		}

		return ($name ? $this->quote($name).'.' : '')  . $this->quote($table);
	}
	public	$next_no_execute=false;
	/**
	 * 设置下一次不实际执行sql语句，调用执行方法后自动为false。
	 */
	function set_next_no_execute(){
		$this->next_no_execute=true;
	}
	/**handle origin db sql**/
	/**
	 * 准备sql语句
	 * @param string $sql sql语句，建议采用占位符或?，防止sql注入，提高执行速度，如mysql  proc 加 call proc()，oracle proc 加begin  proc(); end;。
	 * @param boolean|NULL $ro_hint readonly_hint sql语句只读提示，NULL：忽略此提示，分析sql语句是否查询，true：只读，false：可写。
	 */
	function prepare($sql,$ro_hint=NULL){
    	if($ro_hint===NULL){
    		$sql=trim($sql);
    		$readonly=$this->adapter->get_readonly($sql);
    	}else $readonly= $ro_hint=== true ? true :false;

		$this->last_query =& $sql;
		if($this->next_no_execute) return true;


		try {
			$this->get_pdo($readonly);
		} catch (Exception $e) {
			if(isset($GLOBALS['context'])) $err=lang('db_err_connect');
			$err .= $this->conf['name']."[{$e->getCode()}],{$e->getMessage()}";

			if(isset($GLOBALS['context'])) $GLOBALS['context']->log_error($err);
			throw new Exception($err);
		}
		try {
			$this->sth = $this->pdo->prepare($sql);
		} catch (Exception $e) {
			if(isset($GLOBALS['context'])) $err=lang('db_err_prepare');
			$err .= "[{$e->getCode()}],{$e->getMessage()}";
			if(isset($GLOBALS['context']))
				$GLOBALS['context']->log_error($err.','.$this->last_query);
			throw new Exception($err);
		}
		return $this->sth;
	}

	/**
	 * 执行sql语句，如果执行存储过程，特别是需要参数返回，不使用此函数，需要使用PDO绑定参数，直接调用PDO函数，如：<br/>
	 * $sth=$db->prepare('call proc1(?,?)');
	 * $sth->bindParam(1, $input1, PDO::PARAM_STR, strlen($input1));
	 * $sth->bindParam(2, $output2, PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT, strlen($output2));
	 * $sth->execute();
	 * echo $output2;
	 * @param array $values  占位符或?对应的值
	 */
	function execute(array $values=array()){
		$this->last_values = & $values;
		if($this->next_no_execute){
			$this->next_no_execute=false;
			return true;
		}
		if(! $this->sth) return false;
                
//               $sql_log = '['.date('Y-m-d H:i:s').'] '.$this->last_query."\n".print_r($values,true);
//               $sql_log_path = APP_PATH.'logs/sql'.date('Y-m-d').'.log';
//		error_log($sql_log, 3,$sql_log_path);
//		 //$GLOBALS['context']->log_debug('query sql：'.$this->last_query."\n".print_r($values,true));
//              	reset($values);
//                
//		if(isset($GLOBALS['context']) && $GLOBALS['context']->is_debug()){
//		 	$GLOBALS['context']->log_debug('query sql：'.$this->last_query."\n".print_r($values,true));
//                        dev_log($sql_log);
//		}
                $this->sql_log($this->last_query,$values);
                
                
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
                        $t1 = $this->msectime();
			$execute =  $this->sth->execute();
                        $t2 = $this->msectime()-$t1;
                         
                        $this->sql_log('time:'.$t2,$values,1);
                        return $execute;
		} catch (Exception $e) {
			$err="[{$e->getCode()}],{$e->getMessage()}";
			if(isset($GLOBALS['context'])){
				$err=lang('db_err_execute').$err;
                $GLOBALS['context']->log_error($e->getMessage()."\r\n".$e->getTraceAsString()."\r\n".$this->last_query."\r\n".var_export($values, true));
				throw new Exception($err);
			}else	throw new Exception($err);
		}
	}
	/**
	 * 运行sql语句
	 * @param string $sql sql语句，建议采用占位符或?，防止sql注入，提高执行速度
	 * @param array $values 占位符或?对应的值
	 * @param boolean|NULL $ro_hint readonly_hint sql语句只读提示，NULL：忽略此提示，分析sql语句是否查询，true：只读，false：可写。
	 */
	function query($sql,$values=array(),$ro_hint=NULL){
	    foreach ($values as $k=>$v)  {
	        if (!is_array($v)) {
	            continue;
	        }
	        
	        $new_k_arr = array();
	        $i = 0;
	        foreach ($v as $key=>$item) {
	            if (is_array($item)) {
	                throw new Exception('values cannot be array 2', -1);
	            }
	            $_k = $k.'__'.($i++);
	            $new_k_arr[] = $_k;
	            $values[$_k] = $item;
	        }
	        $sql = str_replace($k, implode(',', $new_k_arr), $sql);
	        unset($values[$k]);
	    }	    
	   
		//try{
			$this->prepare($sql,$ro_hint);
			$v = $this->execute($values);
		//} catch ( Exception $e ) {
			//echo '<hr/>$e<xmp>'.var_export($e->getMessage(),true).'</xmp>';
		//}
		return $v;
	}
	/**end handle origin db sql**/

	/**
	 * 得到in子句和对应值的数组，仅将对应值添加到$in_values。
	 * <br>例：$s=$db->get_in_sql('order_id','12232,121',$vals);或$s=$db->get_in_sql('order_id',array(12232,121),$vals);
	 * $s 返回 in子句”:order_id1,:order_id2“  $vals返回 数组”:order_id1=>12232,:order_id2=>121“
	 * @param string $col				字段名称
	 * @param array|string $col_value   字段值 ,强烈建议为数组，包括数组和字符串，
	 * <br>字符串形式：数值型12,343,56，字符型 '12',"343",'56 ，字符型的项中不得包含英文逗号,
	 * @param array $in_values   返回值数组
	 * @param boolean $bind_name   是采用名称绑定还是?绑定，默认名称绑定，:col or ?
	 * @return string 	        返回in子句
	 */
	function get_in_sql($col,$col_value,array & $in_values,$bind_name=true){
		if(is_string($col_value) && strpos($col_value,',')!==false){
			$vals=explode(',',$col_value);
			$col_value=array();
			foreach ($vals as $v){
				$v=trim($v);
				if(strpos($v,'"')===false && strpos($v,"'")===false)
					$col_value[]=(int) $v;
				else
					$col_value[] = trim($v,'\'"');
			}
		}else if(!is_array($col_value)){
			if(is_object($col_value)) $col_value=get_object_vars($col_value);
			else $col_value=array($col_value);
		}
		$idx=1;
		$in_sql='';
		foreach ($col_value as $val){
			if($bind_name){
				$n=':' . $col .$idx;
				if($idx ++ ==1) 	$in_sql =  $n ;
				else $in_sql .=  ','. $n ;
				$in_values [$n] = $val;
			}else{
				if($idx ++ ==1) $in_sql = '?';
				else $in_sql .= ',?';
				$in_values[] = $val;
			}
		}
		return $in_sql;
	}

	/**get data from db**/

	/*get**方法，查询数据库数据*/
	/**
	 * 返回sql对应的分页记录列表
	 * @param string $sql		要执行的sql语句
	 * @param array $values		要执行的sql对应的值 ，默认为没有。
	 * @param integer   $limit	分页记录条数 ，默认为20，如果此值===false，等同于get_all
	 * @param integer   $offset	偏移量 ，默认为0，即首条
	 * @param boolean|NULL $ro_hint readonly_hint sql语句只读提示，NULL：忽略此提示，分析sql语句是否查询，true：只读，false：可写。
	 */
	function get_limit($sql,$values=array(),$limit=20, $offset=0,$ro_hint=NULL){
		if($limit !== false)	$this->adapter->limit($sql,$limit,$offset);
		if($values === NULL) $values=array();
		if($this->next_no_execute){
			 $this->query($sql,$values,$ro_hint);
			 return true;
		}
		if(! $this->query($sql,$values,$ro_hint)) return false;

		if($this->row_key_is<0)  $r= $this->sth->fetchAll(PDO::FETCH_NUM);
		else if($this->row_key_is>0) $r= $this->sth->fetchAll();
		else	$r= $this->sth->fetchAll(PDO::FETCH_ASSOC);
		if($this->dbtype==self::DBTYPE_ORACLE) {
			$l=array();
			foreach($r as $row){
			  $l[]= array_change_key_case($row,CASE_LOWER);
			}
			return $l;
		}
		else return $r;
	}
	/**
	 * 返回sql对应的记录列表
	 * @param string $sql		要执行的sql语句
	 * @param array $values		要执行的sql对应的值 ，默认为没有。
	 * @param boolean|NULL $ro_hint readonly_hint sql语句只读提示，NULL：忽略此提示，分析sql语句是否查询，true：只读，false：可写。
	 */
	function get_all($sql,$values=array(),$ro_hint=NULL){
		return $this->get_limit($sql,$values,false,false,$ro_hint);
	}
	/**
	 * 返回sql对应的分页首列结果值列表
	 * @param string $sql		要执行的sql语句
	 * @param array $values		要执行的sql对应的值 ，默认为没有。
	 * @param integer   $limit	分页记录条数 ，默认为20，如果此值===false，等同于get_all
	 * @param integer   $offset	页数 ，默认为0，即首页
	 * @param boolean|NULL $ro_hint readonly_hint sql语句只读提示，NULL：忽略此提示，分析sql语句是否查询，true：只读，false：可写。
	 */
	function get_col($sql,$values=array(),$limit=20, $offset=0 ,$ro_hint=NULL){
		$row_key_is=$this->row_key_is;
		$this->row_key_is=self::KEY_DIGIT;
		try{
			$data=$this->get_limit($sql,$values,$limit,$offset,$ro_hint);
			$this->row_key_is=$row_key_is;
		}catch(Exception $e){
			$this->row_key_is=$row_key_is;
			throw $e;
		}
		$result=array();
		foreach($data as $row)	$result[]=$row[0];

		return $result;
	}
	/**
	 * 返回sql对应的全部首列结果值列表
	 * @param string $sql		要执行的sql语句
	 * @param array $values		要执行的sql对应的值 ，默认为没有。
	 * @param boolean|NULL $ro_hint readonly_hint sql语句只读提示，NULL：忽略此提示，分析sql语句是否查询，true：只读，false：可写。
	 */
	function get_all_col($sql,$values=array(),$ro_hint=NULL){
		return $this->get_col($sql,$values,false,false,$ro_hint);
	}
	/**
	 * 返回sql对应的首行记录
	 * @param string $sql		要执行的sql语句
	 * @param array $values		要执行的sql对应的值 ，默认为没有。
	 * @param boolean|NULL $ro_hint readonly_hint sql语句只读提示，NULL：忽略此提示，分析sql语句是否查询，true：只读，false：可写。
	 */
	function get_row($sql,$values=array(),$ro_hint=NULL){
		$result=$this->get_limit($sql,$values,1,0,$ro_hint);
		if($result && count($result)>0)	return $result[0];
		else  return $result;
	}
	/**
	 * 返回sql对应的首行记录，首列的结果值
	 * @param string $sql		要执行的sql语句
	 * @param array $values		要执行的sql对应的值 ，默认为没有。
	 * @param boolean|NULL $ro_hint readonly_hint sql语句只读提示，NULL：忽略此提示，分析sql语句是否查询，true：只读，false：可写。
	 */
	function get_value($sql,$values=array(),$ro_hint=NULL){
		$row_key_is=$this->row_key_is;
		$this->row_key_is=self::KEY_DIGIT;
		try{
		  $result=$this->get_row($sql,$values,$ro_hint);
		  $this->row_key_is=$row_key_is;
		}catch(Exception $e){
			$this->row_key_is=$row_key_is;
			throw $e;
		}
		if($result && count($result)>0)	return $result[0];
		else  return false;
	}


    /**end get data from db**/

	/**
	 * 得到序列的下一个值，oracle为实际序列，mysql为序列表。
	 * <br>mysql序列表结构:CREATE TABLE $seq (`seq` bigint(20) NOT NULL AUTO_INCREMENT,PRIMARY KEY (`seq`)) ENGINE=InnoDB
	 * @param string $seq_name  序列名称，oracle为实际序列名，mysql为序列表名。
	 */
	function get_seq_next_value($seq_name){
		return $this->adapter->seq_next($seq_name);
	}
	/**
	 * 返回当前自动递增字段插入值。
	 * @param string $seq 自动递增字段对应sequence名称，mysql不需要传，oracle等必需
	 */
    function insert_id($seq=null){
    	if(! $this->pdo) $this->get_pdo(false);
    	return $this->adapter->insert_id($seq);
    }
    /**
     * 返回sql语句执行影响记录行数
     */
    function affected_rows(){
    	if(! $this->pdo || !$this->sth) return -1;
    	return $this->sth->rowCount();
    }

	function append_limit_clause(&$sql, $limit=20, $offset=0){
	 	$this->adapter->limit($sql,$limit,$offset);
	}

	/***transcation***/
	private $trans_count=0;		//nested transcation ref counter,when ==0,execute transaction op.
	function begin_trans(){
		$this->get_pdo(false);
              // var_dump('b',$this->trans_count);
		if(! $this->nest_trans && $this->trans_count++ >0) return;
		$this->pdo->beginTransaction();
	}
        function is_transaction(){
            return (!$this->nest_trans && $this->trans_count>0)?true:false;
        }
        
	function commit(){
		if($this->pdo){
                   //var_dump('c',$this->trans_count);
			if(! $this->nest_trans && --$this->trans_count >0) return false;
			$this->pdo->commit();
                        return true;
		}
                return false;
	}
	function rollback(){
              //   var_dump('r',$this->trans_count);
		if($this->pdo){
			if(! $this->nest_trans && --$this->trans_count >0) return false;
			$this->pdo->rollBack();
                          return true;
		}
                return false;
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
    /**
     * 为数据库语句安全，对语句进行处理，建议使用预定义语句模式，而不是使用此方法。
     * @param $string
     */
    function escape($string){
    	if(! $this->pdo) $this->get_pdo();
    	return trim($this->pdo->quote($string),"'");
    }
    /**
     * 对数据库表名，列名进行·处理，此方法兼容mysq、oracle。
     * @param string $string 表名或者列名
     * @return string 返回处理后的名称
     */
	function quote($str){
		if(! $str)  return $str;
		return $str[0] === $this->QUOTE_CHAR || $str[strlen($str)-1] === $this->QUOTE_CHAR ?
			$str : $this->QUOTE_CHAR . $str . $this->QUOTE_CHAR;
	}
	/**
	 * 拼接sql中日期字符串转换到对应DB的日期字符串
	 * @param $date  日期字符串，日期格式必须为标准格式，如'2004-05-07 13:23:44
	 * @param $only_date 是否仅日期
	 */
	function quote_date($date,$only_date=false){
		if(! $this->pdo) $this->get_pdo();
		return $this->adapter->quote_date($date);
	}
	/**
	 * 从普通时间得到标准时间字符串
	 * @param DateTime|int|string|NULL $date 时间日期参数   DateTime：时间日期对象，int：时间戳timestamp，
	 * string：时间日期字符串，NULL：当前系统时间
	 * @param $only_date 是否仅日期
	 * @return string 标准时间字符串
	 */
	static function to_date($date=NULL,$only_date=false){
		require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'date'.DIRECTORY_SEPARATOR.'DateEx.php';
		$d=new DateEx($date);
		return $d->toString(! $only_date);
	}
	/**
	 * 返回上次sql和数据的MD5数据摘要
	 */
	function get_last_key(){
		$str=$this->last_query;
		foreach($this->last_values as $key=>$val) $str .=$key.$val;
		return md5($str);
	}
	/**
	 * 得到当前pdo错误参数
	 * @param int $code	错误吗
	 * @param string $msg 错误消息
	 */
	function get_error(& $code,& $msg){
		if(! $this->pdo){
			$code=0;
			$msg='';
			return ;
		}
		$code=$this->pdo->errorCode();
		if($code==NULL && $this->sth){
			$code=$this->sth->errorCode();
			$code=implode(',',$this->sth->errorInfo());
			return;
		}
		$msg=implode(',',$this->pdo->errorInfo());
	}

	/**query col info,will cache the data**/
	/**
	 * 返回表对应字段的数组，系统将缓存查询结果
	 * @param string $table 表名
	 * @return array 字段的列表
	 */
	function query_for_cols($table){
        $cols = array();
		$cols_info=$this->query_col_info($table);
	    foreach ($cols_info as $row)  	$cols[]=strtolower(current($row));
        return 	$cols;
	}
	/**
	 * 返回表对应字段、关键字段的数组，系统将缓存查询结果
	 * @param string $table 表名
	 * @return array 字段的列表
	 */
	function query_for_key_cols($table,array & $cols,array & $pks){
		$this->adapter->get_key_cols($table,$cols,$pks);
	}

	function query_col_info($table){
		$key=$this->col_info_prefix.strtolower($table);
		if(! isset($GLOBALS['context']->cache)) $cache=NULL;
		else	$cache=$GLOBALS['context']->cache;

		if($cache && $this->cache_valid){
			$result=$cache->get($key);
			if($result) return $result;
		}
		$sql= $this->adapter->get_col_info_sql($table);
		$result=$this->get_all($sql,array(),false,true);
		$this->cache_valid=true;

		if($cache && $result) $cache->set($key,$result);	//cache table col info
		return $result;
	}
	/**end query col info**/
	function query_for_tables(){
		$sql=& $this->adapter->get_tables_sql();
		$tabs=$this->get_col($sql,array(),false);
		if($this->dbtype==self::DBTYPE_ORACLE){
			require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'collection'.DIRECTORY_SEPARATOR.'ArrayEx.php';
			$tabs=ArrayEx::changeCase($tabs);
		}
		return $tabs;
	}
	//*******lob函数***************
	/**
	 * 返回sql对应lob的单条记录，用于获得lob字段值
	 * @param string $sql		要执行的sql语句
	 * @param array $values		要执行的sql对应的值 ，默认为没有。
	 */
	function & get_lob($sql,$values=array()){
		$d=$this->get_limit($sql,$values,1,0,true);
		$r=array();
		$i=0;
		foreach($d as $row){
			$r[]=array();
			foreach($row as $k=>$v){
				if(is_resource($v))
					$r[$i][$k]=stream_get_contents($v);
				else $r[$i][$k]=$v;
			}
			++$i;
		}
		return $r;
	}
	/**
	 * 插入单行记录数组或单个简单对象到表中，用于含lob字段数据
	 * @param array|object $data 记录数组，记录必须为key=>vale对，不能是数字索引。
	 * @return boolean 执行结果，如果成功返回true。
	 */
	function insert_lob($table,$lob_col,$data){
		if (! $data || !$table || (! is_object($data) && ! is_array($data))) return false;

		if(is_object($data)) $data=get_object_vars($data);

		$cols=$this->query_for_cols($table);

		if(isset($data[0]) && is_array($data[0])) //is mutil record use top record
			$row=& $data[0];
		else $row=& $data;
		$is_lob=false;
		$fields =$params = $values= array();
		foreach($row as $key =>$v) {
			if($cols!==false &&  !in_array($key,$cols) )	continue;
			$fields[] 	= 	$key;
			$values[]	=	$v;
			$params[] = "?";
		}
		$sql = "INSERT INTO {$this->table($table)} (".implode(', ', $fields).") VALUES (".implode(', ', $params).")";

		$this->prepare($sql,false);
		for($i=0;$i<count($values);++$i){
			$this->sth->bindParam($i+1,$values[$i]);
		}
		$r= $this->sth->execute();
		return $r;
	}
	/**
	 * 更新记录数组或简单对象到表中，用于含lob字段数据
	 * @param array|object $data 记录数组或对象，记录必须为key=>vale对，不能是数字索引。
	 * @param NULL|array|string $where where子句  如果为空，不使用条件
	 * @return bool 执行结果，如果成功返回true。
	 */
	function update_lob($table,$data,$where=NULL){
		$result=false;
		if (! $data || !$table || (! is_object($data) && ! is_array($data)) ) return $result;
		if(is_object($data)) $data=get_object_vars($data);
		if (! $data ) return $result;

		$cols=$this->query_for_cols($table);
		$fields =$values= array();
		foreach($data as $key => $val){
			if( $cols &&  ! in_array($key,$cols))	continue;
			$fields[]=$this->quote($key) .'=?';
			$values[]=$val;
		}
		$sql = "UPDATE {$this->table($table)} SET " . implode(', ', $fields);
		if($where) $sql .=   " WHERE   {$where}" ;
		$this->prepare($sql,false);
		for($i=0;$i<count($values);++$i)
			$this->sth-> bindParam($i+1,$values[$i],PDO::PARAM_STR,strlen($values[$i]));
			//$this->sth->bindParam($i+1,$values[$i]);
		$result= $this->sth->execute();
 		return $result;
	}
	//************lob函数end*****

	/*register default db to $context*/
	static function register($prop) {
		$context=$GLOBALS['context'];
		$db_type=$context->get_app_conf('db_server');
		$db_host=$context->get_app_conf('db_host');
		$db_user=$context->get_app_conf('db_user');
		$db_pass=$context->get_app_conf('db_pass');
		$db_name=$context->get_app_conf('db_name');
		$conf=array('type'=>$db_type,'host'=>$db_host,'user'=>$db_user, 'pwd'=>$db_pass, 'name'=>$db_name);
		$db_port=$context->get_app_conf('db_port');
		if(isset($db_port))	$conf['port']=$db_port;
		$db = new PDODB($conf);
		$GLOBALS ['context']->log_debug ( "pdodb object [type,host,db,user] create:{$db_type},{$db_host},{$db_name},{$db_user}" );
		return $db;
	}

	//以下都是辅助函数

	/**
	 * 创建TableMapper对象,数据库对象为$this.
	 * @param string $table	数据表名称
	 * @return TableMapper  返回$table对应的表映射对象.
	 */
	function create_mapper($table,$alias=NULL){
		if($this->table_mapper_none){
			require_once  dirname(__FILE__).DIRECTORY_SEPARATOR.'TableMapper.class.php';
			$this->table_mapper_none=false;
		}
		return new TableMapper($table,$this,$alias);
	}
	private $table_mapper_none=true;
	/**
	 * @deprecated for old Mysql.class,use TableMapper.class
	 */
    function escape_string($unescaped_string){
    	return $this->escape($unescaped_string);
    }
    function getAll($sql, $sql_values = array()){
    	return $this->get_all($sql,$sql_values);
    }
    function getOne($sql, $sql_values = array()){
    	return $this->get_value($sql,$sql_values);
    }
    function getRow($sql,$sql_values = array()){
    	return $this->get_row($sql,$sql_values);
    }
	public function trans_rollback(){
		$this->rollback();
	}
	public function trans_commit(){
		$this->commit();
	}
	public function trans_begin(){
		$this->begin_trans();
	}
	function getCol($sql){
		return $this->get_col($sql,array(),true);
	}
	function insert($table_name, $data){
		return $this->create_mapper($table_name)->insert($data);
	}
	function update($table_name, $data, $where='',array $is_func_key=NULL){
		return $this->create_mapper($table_name)->update($data,$where,$is_func_key);
	}
	function autoExecute($table, $field_values, $mode = 'INSERT', $where = '', $querymode = ''){
		$cols_filter=$this->query_for_cols($table);
		if ($mode == 'INSERT') $this->insert($table, $field_values,$cols_filter);
		else $this->update($table, $field_values,$where,$cols_filter);
	}
	function autoReplace($table, $field_values, $update_values, $where = '', $querymode = ''){
		 $this->create_mapper($table)->save($field_values);
	}
        
        function sql_log($sql,$values,$type=0){
                 static $pid = NULL;
                 if($pid===NULL){
                    $pid = md5(microtime());
                 }
                 
                 
                 if($type==0){
                    $sql_log = '['.date('Y-m-d H:i:s').']#'.$pid."\n".$sql."\n".print_r($values,true);
                 }else{
                     $sql_log = $pid.':'.$sql."\n";
                 }
                 
                 $date_str = date('Y-m-d');
                 $sql_log_path = ROOT_PATH.'logs/sql'.DIRECTORY_SEPARATOR;
                 if (!file_exists($sql_log_path)){
                        mkdir($sql_log_path);
                 }  
                $sql_log_path .= $date_str.DIRECTORY_SEPARATOR;
                if (!file_exists($sql_log_path)){
                        mkdir($sql_log_path);
                 }  
                 $sql_log_path.= 'sql_'.$date_str;
                  if (defined('RUN_SAAS') && RUN_SAAS) {
                // if(isset($GLOBALS['context']->saas)){
                     $khid = $GLOBALS['context']->saas->get_saas_key();
                     $sql_log_path.="_".$khid;
                 }
                 $sql_log_path.=".log";
                 
		 error_log($sql_log, 3,$sql_log_path);
 
//		if(isset($GLOBALS['context']) && $GLOBALS['context']->is_debug()){
//		 	$GLOBALS['context']->log_debug('query sql：'.$this->last_query."\n".print_r($values,true));
//                        dev_log($sql_log); //开发日志调试使用
//		}
        }
        
        
        function msectime() {
            list($tmp1, $tmp2) = explode(' ', microtime());
            return (float)sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
        }
        /***end for old Mysql.class***/
}