<?php
require_once ROOT_PATH.'boot/req_inc.php';

class Mysql implements IDB,IRequestTool {
	
    var $link_id    = NULL;
    var $settings   = array();
    private $dbid=NULL; 
    
    ////////////
    var $_escape_char = '`';
    var $tb_pre = '';
    
    private $dbconnsel_loaded=false;
    private $sample_db=NULL;

    function __construct($dbhost, $dbuser, $dbpw, $dbname, $charset = 'utf8', $tb_pre='', $pconnect = 0, $quiet = 0)
    {
		$this->dbconnsel_loaded =extension_loaded('dbconnsel');

		$this->tb_pre = $tb_pre;
        $this->connect($dbhost, $dbuser, $dbpw, $dbname, $charset, $pconnect, $quiet);
    }
	function __destruct(){
		if($this->link_id)	 mysql_close($this->link_id);
	}
    function connect($dbhost, $dbuser, $dbpw, $dbname, $charset = 'utf8', $pconnect = 0, $quiet = 0){
        $this->settings = array('dbhost'   => $dbhost,'dbuser'   => $dbuser,
                                 'dbpw'     => $dbpw,'dbname'   => $dbname,
                                 'charset'  => $charset,'pconnect' => $pconnect);
                                     
    }
    private function _connect($readonly,$dbhost, $dbuser, $dbpw, $dbname = '', $charset = 'utf8', $pconnect = 0, $quiet = 0)
    {
		if($this->dbconnsel_loaded ){
			
			if($this->sample_db) $sample_db=$this->sample_db; 
			else $sample_db=$dbname;
     		$result=dbconn_select($readonly,$sample_db);
     		if($result){
				$this->settings['dbhost']=$dbhost = $result["connect"] ;
				if($this->sample_db){
					$this->settings['dbuser']=$dbuser = $result["user"];
     				$this->settings['dbpw']=$dbpw = $result["password"]; 
				}else{
					if(! $dbuser)	$dbuser=$this->settings['dbuser'];
					if(! $dbpw)		$dbpw=$this->settings['dbpw'];
				}
        	} 
		}
		else if($dbhost==NULL){
     			$dbhost=$this->settings['dbhost'];
     			$dbuser=$this->settings['dbuser'];
     			$dbpw=$this->settings['dbpw']; 			
		}
		$dbid="{$dbhost}@{$dbuser}@{$dbpw}";
		if($this->link_id && $dbid===$this->dbid)	return true;
		
        if ($pconnect){
            if (!($this->link_id = @mysql_pconnect($dbhost, $dbuser, $dbpw))){
                if (!$quiet)  $this->ErrorMsg("Can't pConnect MySQL Server($dbhost)!");
                return false;
            }
        }else{
           $this->link_id = @mysql_connect($dbhost, $dbuser, $dbpw, true);
            if (! $this->link_id){
                if (!$quiet)    $this->ErrorMsg("Can't Connect MySQL Server($dbhost)!");
                return false;
            }
        }
		$this->dbid=$dbid;

        mysql_query("SET character_set_connection=$charset, character_set_results=$charset, character_set_client=binary", $this->link_id);
        mysql_query("SET sql_mode=''", $this->link_id);

        if (! $dbname || mysql_select_db($dbname, $this->link_id) !== false ) return true;
        if (!$quiet)   $this->ErrorMsg("Can't select MySQL database($dbname)!");
        return false;
    }
    //function query($sql,$values=array(), $type = '')
    function query($sql,$values=array())
    {
    	//pre handle 
    	$sql=trim($sql);
    	$readonly=strncasecmp($sql,"select",6)==0 || strncasecmp($sql,'desc',4)==0 || strncasecmp($sql,'show',4)==0;
    	 
        $this->_connect($readonly,$this->settings['dbhost'], $this->settings['dbuser'], $this->settings['dbpw'], $this->settings['dbname'], $this->settings['charset'], $this->settings['pconnect']);
     	
    	if (!empty($values) && is_array($values)) {
    		foreach ($values as $key=>$value) {
    			$value = $this->escape_string($value);
    			$sql = str_replace('@'.$key, "'$value'", $sql); 
    		}
    		   		
    	}
    	if(! $this->link_id) return false;

        if (!($query = mysql_query($sql, $this->link_id)))
        {
        	$GLOBALS['context']->log_error("sql error[".mysql_errno($this->link_id)."]{$sql}  ".mysql_error($this->link_id));
			$this->ErrorMsg("sql error ".mysql_errno($this->link_id)." ");
            return false;
        }

        return $query;
    }

    function insert_id($sequence=null)
    {
        return mysql_insert_id($this->link_id);
    }

     function escape_string($unescaped_string)
    {
    	if(! $this->link_id)  $this->_connect(true,NULL,NULL,NULL);
        return mysql_real_escape_string($unescaped_string,$this->link_id);
    }
	function affected_rows(){
		return mysql_affected_rows($this->link_id);
	}    
    function ErrorMsg($message = '', $sql = '')
    {
		if(isset($GLOBALS['context'])){
			$GLOBALS['context']->put_error(10000,'数据库访问失败：'.$message);
		}
    }

    function getOne($sql, $limited = false)
    {
        if ($limited == true)  $sql = trim($sql . ' LIMIT 1');
        $res = $this->query($sql);
        if ($res !== false) {
            $row = mysql_fetch_row($res);
            if ($row !== false)  return $row[0];
            else  return '';
        } else  return false;
    }

     function getAll($sql, $key_index='')
    {
        $res = $this->query($sql);
        if ($res === false) return false;
        $arr = array();
        if ($key_index == '')   while ($row = mysql_fetch_assoc($res))   $arr[] = $row;
        else {
            	$row = mysql_fetch_assoc($res);
            	if ($row) {
            		if (!array_key_exists ($key_index, $row)) throw new Exception("$key_index is not in the columns");
            		else {
            			$index = $row[$key_index];
            			$arr[$index] = $row;
            		}
	            	while ($row = mysql_fetch_assoc($res))
		            {
		            	$index = $row[$key_index];
		                $arr[$index] = $row;
		            }
            	}            	
         }
         return $arr;
    }

    function getRow($sql, $limited = false)
    {
        if ($limited == true)  $sql = trim($sql . ' LIMIT 1');
        $res = $this->query($sql);
        if ($res === false) return false;
        return mysql_fetch_assoc($res);
    }

    function getCol($sql)
    {
        $res = $this->query($sql);
        if ($res === false) return false;
        $arr = array();
        while ($row = mysql_fetch_row($res))
            $arr[] = $row[0];
        return $arr;
    }

    function autoExecute($table, $field_values, $mode = 'INSERT', $where = '', $querymode = '')
    {
        $field_names = $this->getCol('DESC ' . $table);
        $sql = '';
        if ($mode == 'INSERT')
        {
            $fields = $values = array();
            foreach ($field_names AS $value) {
                if (array_key_exists($value, $field_values) == true) {
                    $fields[] = $value;
                    $values[] = "'" . $field_values[$value] . "'";
                }
            }
            if (!empty($fields))
                $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
        }
        else
        {
            $sets = array();
            foreach ($field_names AS $value) {
                if (array_key_exists($value, $field_values) == true)
                    $sets[] = $value . " = '" . $field_values[$value] . "'";
            }
            if (!empty($sets))
                $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
        }
        
        if (! $sql) return false;
        return $this->query($sql, $querymode);
    }

    function autoReplace($table, $field_values, $update_values, $where = '', $querymode = '')
    {
        $field_descs = $this->getAll('DESC ' . $table);

        $primary_keys = array();
        foreach ($field_descs AS $value){
            $field_names[] = $value['Field'];
            if ($value['Key'] == 'PRI')  $primary_keys[] = $value['Field'];
        }

        $fields = $values = array();
        foreach ($field_names AS $value) {
            if (array_key_exists($value, $field_values) == true) {
                $fields[] = $value;
                $values[] = "'" . $field_values[$value] . "'";
            }
        }

        $sets = array();
        foreach ($update_values AS $key => $value)
        {
            if (array_key_exists($key, $field_values) == true) {
                if (is_int($value) || is_float($value))
                    $sets[] = $key . ' = ' . $key . ' + ' . $value;
                else
                    $sets[] = $key . " = '" . $value . "'";
            }
        }

        $sql = '';
        if (empty($primary_keys)){
            if (!empty($fields))  $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
        } else if (!empty($fields)){
			$sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
			if (!empty($sets))  $sql .=  'ON DUPLICATE KEY UPDATE ' . implode(', ', $sets);
        }
        
        if ($sql)  return $this->query($sql, $querymode);
        else   return false;
    }
    /* 获取 SQL 语句中最后更新的表的时间，有多个表的情况下，返回最新的表的时间 */
    private $timeline       = 0;
    private $timezone       = 0;  
    private $mysql_disable_cache_tables = array(); // 不允许被缓存的表，遇到将不会进行缓存  
    function table_lastupdate($tables)
    {
        if ($this->link_id === NULL)
        {
            $this->connect($this->settings['dbhost'], $this->settings['dbuser'], $this->settings['dbpw'], $this->settings['dbname'], $this->settings['charset'], $this->settings['pconnect']);
            $this->settings = array();
        }

        $lastupdatetime = '0000-00-00 00:00:00';

        $tables = str_replace('`', '', $tables);
        $this->mysql_disable_cache_tables = str_replace('`', '', $this->mysql_disable_cache_tables);

        foreach ($tables AS $table)
        {
            if (in_array($table, $this->mysql_disable_cache_tables) == true)
            {
                $lastupdatetime = '2037-12-31 23:59:59';

                break;
            }

            if (strstr($table, '.') != NULL)
            {
                $tmp = explode('.', $table);
                $sql = 'SHOW TABLE STATUS FROM `' . trim($tmp[0]) . "` LIKE '" . trim($tmp[1]) . "'";
            }
            else
            {
                $sql = "SHOW TABLE STATUS LIKE '" . trim($table) . "'";
            }
            $result = mysql_query($sql, $this->link_id);

            $row = mysql_fetch_assoc($result);
            if ($row['Update_time'] > $lastupdatetime)
            {
                $lastupdatetime = $row['Update_time'];
            }
        }
        $lastupdatetime = strtotime($lastupdatetime) - $this->timezone + $this->timeline;

        return $lastupdatetime;
    }

    function get_table_name($query_item)
    {
        $query_item = trim($query_item);
        $table_names = array();

        /* 判断语句中是不是含有 JOIN */
        if (stristr($query_item, ' JOIN ') == '')
        {
            /* 解析一般的 SELECT FROM 语句 */
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?(?:\s*,\s*(?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?)*)/is', $query_item, $table_names))
            {
                $table_names = preg_replace('/((?:`?\w+`?\s*\.\s*)?`?\w+`?)[^,]*/', '\1', $table_names[1]);

                return preg_split('/\s*,\s*/', $table_names);
            }
        }
        else
        {
            /* 对含有 JOIN 的语句进行解析 */
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)(?:(?:\s*AS)?\s*`?\w+`?)?.*?JOIN.*$/is', $query_item, $table_names))
            {
                $other_table_names = array();
                preg_match_all('/JOIN\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)\s*/i', $query_item, $other_table_names);

                return array_merge(array($table_names[1]), $other_table_names[1]);
            }
        }

        return $table_names;
    }

	// --------------------------------------------------------------------
    
	function table($table_name,$region_value=NULL) {
        	return '`' . $this->get_db($table_name) . '`.`' . $this->tb_pre . $table_name . '`';			
	}
	function get_db($table_name) {
        	return $this->settings['dbname'] ;			
	}	
	// --------------------------------------------------------------------

	public function result_row($sql, $args=array()) {
	 	$res = $this->query($sql, $args);
        if ($res === false) return false;
        return mysql_fetch_assoc($res);
	}
	// --------------------------------------------------------------------
	
	public function result_all($sql, $args=array()) {
		$res = $this->query($sql, $args);
        if ($res === false) return false;
        $arr = array();
        while ($row = mysql_fetch_assoc($res))
             $arr[] = $row;
        return $arr;
	}
	
	public function trans_begin()
	{		
		$this->query('SET AUTOCOMMIT=0');
		$this->query('START TRANSACTION'); // can also be BEGIN or BEGIN WORK
		
		return TRUE;
	}
	public function trans_commit()
	{
		$this->query('COMMIT');
		$this->query('SET AUTOCOMMIT=1');
		
		return TRUE;
	}
	public function trans_rollback()
	{
		$this->query('ROLLBACK');
		$this->query('SET AUTOCOMMIT=1');
		
		return TRUE;
	}
	public function insert($table_name, $data) {
		$fields = array();
		$values = array();
		
		foreach($data as $key => $val) {
			$fields[] = $this->_escape_identifiers($key);
			$values[] = "'".$this->escape_string($val)."'";
		}
		$table_name = $this->_escape_identifiers($table_name);
		$sql = "INSERT INTO ".$table_name." (".implode(', ', $fields).") VALUES (".implode(', ', $values).")";
		
		return $this->query($sql);	
	}

	public function update($table_name,$data, $where='') {
		if (! $data || ! $where) return false;
		$fields = array();
		foreach($data as $key => $val)
			$fields[$this->_escape_identifiers($key)] = $this->escape_string($val);

		if ( ! is_array($where)) $dest = array($where);
		else {
			$dest = array();
			foreach ($where as $key => $val) {
				$prefix = (count($dest) == 0) ? '' : ' AND ';
	
				if ($val !== '') {
					if ( ! $this->_has_operator($key))
						$key .= ' =';
					$val = ' '.$this->escape_string($val);
				}
				$dest[] = $prefix.$key.$val;
			}
		}		
		$table_name = $this->_escape_identifiers($table_name);
		$sql =  $this->_update_string($table_name, $fields, $dest);
		
		return $this->query($sql);		
	}

	function _has_operator($str)
	{
		$str = trim($str);
		if ( ! preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str)) return FALSE;
		return TRUE;
	}

	function _update_string($table, $values, $where, $orderby = array(), $limit = FALSE)
	{
		foreach($values as $key => $val) $valstr[] = $key." = '".$val."'";
		
		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;
		$orderby = (count($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';

		$sql = "UPDATE ".$table." SET ".implode(', ', $valstr);
		$sql .= ($where != '' AND count($where) >=1) ? " WHERE ".implode(" ", $where) : '';
		$sql .= $orderby.$limit;
		return $sql;
	}
	function _escape_identifiers($item)
	{
		if (strpos($item, '.') !== FALSE)
			$str = $this->_escape_char.str_replace('.', $this->_escape_char.'.'.$this->_escape_char, $item).$this->_escape_char;			
		else
			$str = $this->_escape_char.$item.$this->_escape_char;
		// remove duplicates if the user already included the escape
		return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
	}
	static function register($prop) {
		$context=$GLOBALS['context'];
		$db_host=$context->get_app_conf('db_host');
		$db_user=$context->get_app_conf('db_user');
		$db_pass=$context->get_app_conf('db_pass');
		$db_name=$context->get_app_conf('db_name');
		$db = new Mysql ( $db_host, $db_user, $db_pass, $db_name );
		$GLOBALS ['context']->log_debug ( "mysql object [host,db,user] create:{$db_host},{$db_name},{$db_user}" );
		return $db;
	}
		
    function close()
    {
        return mysql_close($this->link_id);
    }
}