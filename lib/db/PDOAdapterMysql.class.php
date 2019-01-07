<?php
class PDOAdapterMysql{
	public $QUOTE_CHAR = '`';
	public $nest_trans=false;
	
	public $db;
	private $pdo;
	private $chset;
	function init_pdo(PDODB $db,PDO $pdo){
		$this->db=$db;
		$this->pdo=$pdo;
		$pdo->exec("SET character_set_connection={$this->chset}, character_set_results={$this->chset}, character_set_client=binary");
	}
	function get_dsn(array & $conf,array & $options) {
		if (! isset ( $conf ['host']) || strcasecmp ( $conf ['host'], 'localhost')==0 ) {
			if (strncasecmp ( PHP_OS, 'WIN', 3 ) == 0)	$host = 'host=localhost';
			else	$host = 'unix_socket=/tmp/mysql.sock';
		}else	$host = "host={$conf['host']}";
		if (isset ( $conf ['port'] ))	$host .= ";port={$conf['port']}";
		
		$dbname=$conf['name'];
		if (isset ( $conf ['charset'] ))	$this->chset= strtolower($conf ['charset'])==='utf-8' ? 'utf8' : $conf ['charset'];
		else $this->chset='utf8';
		//$options[PDO::MYSQL_ATTR_FOUND_ROWS]=true;
		return "mysql:{$host};dbname={$dbname}";
	}
	function get_readonly(& $sql){
		return strncasecmp($sql,"select",6)==0 || strncasecmp($sql,'desc',4)==0 || strncasecmp($sql,'show',4)==0;
	}
	function alias($alias){
		return ' '.$alias;
	}		
	function limit(& $sql, $limit=20, $offset=0){
		$limit = intval($limit);
		$offset = intval($offset);
		if($offset===0) $sql .= " LIMIT {$limit}";
		else $sql .= " LIMIT {$offset},{$limit}";
	}
	function  get_tables_sql(){
		return 'SHOW TABLES';
	}	
	function  get_col_info_sql($table){
		return "SHOW COLUMNS FROM {$this->db->table($table)}";
	}
	function get_key_cols($table,array & $cols,array & $pks){
		$cols_info=$this->db->query_col_info($table);
	    foreach ($cols_info as $row){
            $cols[] = $row['Field'];
            if ($row['Key'] == 'PRI')  $pks[] = $row['Field'];
        }		
	}
	function save($table,& $fields,& $params,& $update,& $pk,&$value_list){
	    $s = "INSERT INTO {$this->db->table($table) }(" .implode(', ', $fields).') VALUES ('.implode(', ', $params).')';
		if ($update)  $s .=  'ON DUPLICATE KEY UPDATE ' . implode(', ', $update);
		
		$this->db->prepare($s);
		$result=false;
		foreach($value_list as $row) $result=$this->db->execute($row) || $result;
		return $result;
		//inalid 2013-9-4,valid only for primary key
		//$s = "INSERT INTO {$this->db->table($table) }(" .implode(', ', $fields).') VALUES ('.implode(', ', $params).')';
		//if ($update)  $s .=  'ON DUPLICATE KEY UPDATE ' . implode(', ', $update);  
		
		$s ="UPDATE {$this->db->table($table)} SET ".implode(', ', $update) .' WHERE '. implode(' AND ', $pk);
		$this->db->prepare($s);
		$insert=array();
		$result=false;
		foreach($value_list as $row){
			$result = $this->db->execute($row) || $result;
			if($this->db->affected_rows()<=0 )	$insert[]=$row;	
		} 
		if($insert){
			$s ="INSERT INTO {$this->db->table($table)}(".implode(', ', $fields).') VALUES('. implode(', ', $params).')';
			$this->db->prepare($s);
			foreach($insert as $row) $result=$this->db->execute($row) || $result;
		}
		return $result;		
	}
	private $sr_start=true;
	function seq_next($seq) { //table $seq create: CREATE TABLE $seq (`seq` bigint(20) NOT NULL AUTO_INCREMENT,PRIMARY KEY (`seq`)) ENGINE=InnoDB
		$this->db->query("INSERT INTO {$this->db->table($seq)}() values()");
		$id=$this->db->insert_id();
		if($this->sr_start){
			mt_srand();
			$this->sr_start=false;
		} 
		if((mt_rand() % 20)==0)	$this->db->query("DELETE FROM {$this->db->table($seq)}");
		return 	$id;	
	}
    function insert_id($seq=null){
    	return $this->pdo->lastInsertId($seq);
    }
    function quote_date($date,$only_date=false){
    	return "'".$date."'";
    }     	
}