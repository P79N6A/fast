<?php
class PDOAdapterOci{
	public $QUOTE_CHAR = '';
	public $nest_trans=false;
	
	public $db;
	private $pdo;
	function init_pdo(PDODB $db,PDO $pdo){
		$this->db=$db;
		$this->pdo=$pdo;
		
		//set nls_date_format for date string,default 2009-01-01
		$f=NULL;
		if(isset($GLOBALS['context']) ) $f=$GLOBALS['context']->get_app_conf('nls_date_format');
		if(! $f) $f='yyyy-mm-dd hh24:mi:ss';
		$pdo->exec("alter session set nls_date_format='{$f}'");
	}
	function get_dsn(array & $conf,array & $options) {
		if (isset ( $conf ['tns'] ))
			$dbname = "dbname={$conf['name']}";
		else {
			$host = isset ( $conf ['host'] ) ? $conf ['host'] : 'localhost';
			$dbname = "dbname=//{$host}";
			if (isset ( $conf ['port'] ))	$dbname .= ":{$conf['port']}";
			$dbname .= "/{$conf['name']}";
		}
		
		if (isset ( $conf ['charset'] ))	$dbname .= ";charset={$conf['charset']}";
		else $dbname .=';charset=utf8';
		return "oci:{$dbname}";
	}
	function get_readonly(& $sql){
		return strncasecmp($sql,"select",6)==0 || strncasecmp($sql,'desc',4)==0;
	}
	function alias($alias){
		return ' '.$alias;
	}	
	function limit(& $sql, $limit=20, $offset=0){
		$offset = intval($offset);
		$e = $offset + intval($limit);
		$sql= 	"SELECT * FROM (SELECT a.*, rownum fa__num__ FROM ({$sql}) a " .
				"WHERE rownum <= {$e}) WHERE fa__num__ > {$offset}";
	}	
	function  get_tables_sql(){
		return 'SELECT table_name FROM user_tables';
	}		
	function  get_col_info_sql($table){
		$table=strtoupper($table);
		return "SELECT c.column_name, c.data_type, c.data_length, c.data_scale, c.data_default, c.nullable, " .
				"(SELECT a.constraint_type " .
				"FROM all_constraints a, all_cons_columns b " .
				"WHERE a.constraint_type='P' " .
				"AND a.constraint_name=b.constraint_name " .
				"AND a.table_name = t.table_name AND b.column_name=c.column_name) AS pk " .
			"FROM user_tables t " .
			"INNER JOIN user_tab_columns c on(t.table_name=c.table_name) " .
			"WHERE t.table_name='{$table}'";
	}
	function get_key_cols($table,array & $cols,array & $pks){
		$cols_info=$this->db->query_col_info($table);
	    foreach ($cols_info as $row){
            $cols[] =strtolower($row['column_name']);
            if ($row['pk'] == 'P')  $pks[] = strtolower($row['column_name']);
        }		
	}	
	function save($table,& $fields,& $params,& $update,& $pk,&$value_list){
		$s ="BEGIN UPDATE {$this->db->table($table)} SET ".implode(', ', $update) .' WHERE '. implode(' AND ', $pk) . 
			";IF SQL%ROWCOUNT = 0 THEN  INSERT INTO {$this->db->table($table)}(".
			implode(', ', $fields).') VALUES('. implode(', ', $params).'); END IF;  END; ';
		$this->db->prepare($s,false);
		$result=false;
		foreach($value_list as $row) $result = $this->db->execute($row) || $result;
		return $result;	
	}
	function seq_next($seq) { 
		if(! $seq) throw new Exception('get_seq_next_value function $seq param MUST not null for oracle');
		$id=$this->db->get_all("SELECT {$this->db->table($seq)}.NEXTVAL FROM DUAL");
		if(is_array($id) && count($id)>0) $id=$id[0]['nextval'];
		return 	$id;	
	}
    function insert_id($seq=null){
    	if(! $seq) throw new Exception("insert_id function {$seq} param MUST not null for oracle");
		$seq=$this->db->table($seq);
		$id=$this->db->get_all("SELECT {$seq}.CURRVAL FROM DUAL");
		if(is_array($id) && count($id)>0) $id=$id[0]['currval'];
		return 	$id;
    }
    function quote_date($date,$only_date=false){
    	if($only_date) return "to_date('{$date}','yyyy-mm-dd')";
    	else return "to_date('{$date}','yyyy-mm-dd hh24:mi:ss')";
    }    		
}