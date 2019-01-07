<?php
/**
 * MSSQL数据库操作异常
 * @author wzd
 *
 */
class MSSQLException extends Exception {}
/**
 * mssql数据库连接失败异常
 * @author wzd
 *
 */
class MSSQLConnectFailedException extends MSSQLException {}
/**
 * mssql查询异常
 * @author wzd
 *
 */
class MSSQLQueryFailedException extends MSSQLException {}

class Mssql {   
    private $link;   
    private $querynum = 0;   
  
  	private static $instance = null;

    private function __construct() {
    }

    /**
     * 获取mssql示例
     */
    public static function getInstance() {
        if(self::$instance == null) {
            self::$instance = new Mssql();
        }

        return self::$instance;
    }
    
    /**
     * 连接MSSql数据库，参数： 
     * @parameter dbsn->数据库服务器地址，
     * @param dbun->登陆用户名，
     * @param dbpw->登陆密码，
     * @param dbname->数据库名字
     */
    function connect($dbsn, $dbun, $dbpw, $dbname) { 
    	if ($this->link) {
    		return false;
    	}
        if($this->link = @mssql_connect($dbsn, $dbun, $dbpw, true)) {   
            $query = $this->query('SET TEXTSIZE 2147483647');   
            if (@mssql_select_db($dbname, $this->link)) {   
            } else {   
                throw new MSSQLException("无法选择数据库：$dbname！");
            }   
        } else {
        	throw new MSSQLException('SQLSERVER 连接失败！:'.mssql_get_last_message()); 
        }   
    }   
  
    /**
     * 执行sql语句，返回对应的结果标识
     * 执行失败将抛出一个异常
     * @param string $sql
     */
    function query($sql) { 
        if($query = mssql_query($sql, $this->link)) {   
            $this->querynum++;   
            return $query;   
        } else {  
        	echo $sql; 
            $this->querynum++;
			throw new MSSQLException("MSSQL Error:$sql',\r\n''=>'\t".mssql_get_last_message());
        }   
        
    }  

	function fetchRow($query)
    {
        $row =  mssql_fetch_array($query);
        return $this->converRow($row);
    }
  
    /**
     * 执行一个sql语句并返回所有结果集
     * @param $sql
     * @param $key_index 如果指定则返回只包含该字段的结果集
     */
    function getAll($sql, $key_index='')
    {
        $res = $this->query($sql);
        if ($res !== false)
        {
            $arr = array();
            if ($key_index == '') {
	            while ($row = mssql_fetch_assoc($res))
	            {
	                $arr[] = $this->converRow($row);
	            }
            } else {
            	$row = mssql_fetch_assoc($res);
            	if ($row) {
            		if (!array_key_exists ($key_index, $row)) {
            			throw new Exception("$key_index is not in the columns");
            		} else {
            			$index = $row[$key_index];
            			$arr[$index] = $this->converRow($row);
            		}
	            	while ($row = mssql_fetch_assoc($res))
		            {
		            	$index = $row[$key_index];
		                $arr[$index] = $this->converRow($row);
		            }
            	}            	
            }
            return $arr;
        }
        else
        {
            return false;
        }
    }   

    function insert_id() {
    	$r = $this->getRow('SELECT @@IDENTITY AS [insertid];');
    	if ($r) {
    		return $r['insertid'];
    	}
    	
    	return 0;
    }
    
    /*执行Insert Into语句，并返回最后的insert操作所产生的自动增长的id*/  
    function insert($table, $iarr) {
    	
    	$f_to_v = array();
    	foreach ($iarr as $k=>$v) {
    		$k = $this->encode($k);
        	$v = $this->encode($v);
        	$f_to_v[$k] = $v;
        }
           
        $keys = array_keys($f_to_v);
        
        
        $tmp_str1 = '(['.implode("], [", $keys).'])';
        $tmp_str2 =  "('".implode("', '", $f_to_v)."')";

        $query = $this->query("INSERT INTO $table $tmp_str1 VALUES $tmp_str2;");   
		
        //$this->clear($query);   
        
        return $this->insert_id();
    }   
  
    /**
     * 执行Update语句，并返回最后的update操作所影响的行数
     * 
     * @param strng $table
     * @param array $uarr 要修改的字段和值的数组
     * @param string $condition 更新条件
     */
    function update($table, $uarr, $condition = '') {   
        $value = $this->UpdateSql($uarr);   
        if ($condition) {   
            $condition = ' WHERE ' . $condition;   
        }   
        $query = $this->query('UPDATE ' . $table . ' SET ' . $value . $condition . '; ');   
        $record = $this->getRow('SELECT @@ROWCOUNT AS [rowcount];');   
        
        //$this->clear($query);   

        return $record['rowcount'];
    }   
  
    /**
     * 执行Delete语句，并返回最后的Delete操作所影响的行数
     * 
     * @param string $table
     * @param string $condition
     */ 
    function delete($table, $condition = '') {   
        if ($condition) {   
            $condition = ' WHERE ' . $condition;   
        }   
        $query = $this->query('DELETE ' . $table . $condition . ';');   
        $record = $this->getRow(' SELECT @@ROWCOUNT AS [rowcount];');  
         
        //$this->clear($query);
         
        return $record['rowcount'];   
    }   
  
    /*将字符转为可以安全保存的mssql值，比如a'a转为a''a*/  
    function encode($str) {   
        return str_replace("'" , "''", str_replace('', '', $str));   
    }   
  
    /*将可以安全保存的mssql值转为正常的值，比如a''a转为a'a*/  
    function decode($str) {   
        return str_replace("''", "'", $str);   
    }   
  
    /*将对应的列和值生成对应的insert语句，如：array('id' => 1, 'name' => 'name')返回([id], [name]) VALUES (1, 'name')*/  
    private function InsertSql($iarr) {   
        if (is_array($iarr)) {   
            $fstr = '';   
            $vstr = '';   
            foreach ($iarr as $key => $val) {   
                $fstr .= '[' . $key . '], ';   
                $vstr .= "'" . $val . "',";   
            }   
            if ($fstr) {   
                $fstr = '(' . substr($fstr, 0, -2) . ')';   
                $vstr = '(' . substr($vstr, 0, -2) . ')';   
                return $fstr . ' VALUES ' . $vstr;   
            } else {   
                return '';   
            }   
        } else {   
            return '';   
        }   
    }   
  
    /*将对应的列和值生成对应的insert语句，如：array('id' => 1, 'name' => 'name')返回[id] = 1, [name] = 'name'*/  
    private function UpdateSql($uarr) {   
        if (is_array($uarr)) {   
            $ustr = '';   
            foreach ($uarr as $key => $val) {   
                $ustr .= "[" . $this->encode($key) . "] = '" . $this->encode($val) . "', ";   
            }   
            if ($ustr) {   
                return substr($ustr, 0, -2);   
            } else {   
                return '';   
            }   
        } else {   
            return '';   
        }   
    }   
  
    
    /*返回对应的查询标识的结果的一行*/  
    function getRow($sql) { 
    	$res = $this->query($sql);  
        $row =  mssql_fetch_array($res);   
        
        return $this->converRow($row);
    } 
      
   	/*执行sql语句，返回单值*/
    function getOne($sql)
    {
        $res = $this->getRow($sql);
        if ($res !== false)
        {
           $value = $res[0];            
           return $this->convert($value);
        }
        else
        {
            return false;
        }
    } 
    
    function converRow($arr) {
    	if (is_array($arr)) {
	    	$new_arr = array();
	    	foreach ($arr as $k=>$v) {
	    		$new_arr[$k] = $this->convert( $v);
	    	}
	    	return $new_arr;
    	} else {
    		return $arr;
    	}
    	
    	
    }
    function convert($data, $to='UTF-8') {
    	//FIXME MSSQL 的时间戳类型，转换会有问题，这里使用了ignore选项来屏蔽了此NOTICE信息
    	return @iconv("gbk", "utf-8//ignore", $data);
    	
    	/*$encode_arr = array('ASCII','GB2312','GBK','UTF-8','BIG5');
	    $encoded = mb_detect_encoding($data, $encode_arr);
	
	    if($encoded != 'UTF-8') {
	        $data = mb_convert_encoding( $data, $to, $encoded );
	    }
	    
	    return $data;*/
    }
    
    /*清空查询结果所占用的内存资源*/  
    function clear($query) {   
        return mssql_free_result($query);   
    }   
  
    /*关闭数据库*/  
    function close() {   
        return mssql_close($this->link);   
    }   
  
    function halt($message = '', $sql = '') {   
        $message .= '<br />MSSql Error:' . mssql_get_last_message();   
        if ($sql) {   
            $sql = '<br />sql:' . $sql;   
        }   
        exit("DataBase Error.<br />Message:$message $sql");   
    }
    // 针对 mssql 增加事务
	/**
	 * Begin Transaction
	 *
	 * @access	public
	 * @return	bool		
	 */	
	public function trans_begin()
	{		
		$this->query("BEGIN TRANSACTION DEPS02_DEL");  // can also be BEGIN or BEGIN WORK
		return TRUE;
	}

	/**
	 * Commit Transaction
	 *
	 * @access	public
	 * @return	bool		
	 */	
	public function trans_commit()
	{
		$this->query("COMMIT TRANSACTION DEPS02_DEL"); 
		return TRUE;
	}

	/**
	 * Rollback Transaction
	 *
	 * @access	public
	 * @return	bool		
	 */	
	public function trans_rollback()
	{
		$this->query("ROLLBACK TRANSACTION DEPS02_DEL"); 
		return TRUE;
	}
    
}   