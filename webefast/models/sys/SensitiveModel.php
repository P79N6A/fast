<?php

require_model('tb/TbModel');

class SensitiveModel extends TbModel {

    function get_table() {
        return 'sys_sensitive_data';
    }

    function get_by_page($filter) {
        //$sql_join = "";
        
    	$sql_main = "  FROM {$this->table} rl";
    	//LEFT JOIN sys_role_sensitive_data r2 on rl.sensitive_code = r2.sensitive_code
    	//WHERE 1";
        $sql_values = array();
       
        
        $select = 'select rl.*';
        //$sql_main .= " group by rl.sensitive_code ";
       // echo $select.$sql_main;
        $data = $this->db->get_all($select.$sql_main);
        $sql = "select * from sys_role_sensitive_data where role_code = :role_code";
        $data2 = $this->db->get_all($sql,array("role_code"=>$filter['role_code']));;
        
        foreach ($data as $key => $value) {
        	foreach($data2 as $value2){
        		if($value2['sensitive_code'] == $value['sensitive_code']){
        			$data[$key]['sys_role_sensitive_data_id'] = $value2['sys_role_sensitive_data_id'];
        			$data[$key]['role_code'] = $value2['role_code'];
        			$data[$key]['status'] = $value2['status'];
        		}
        	}
        	
        	
        }
       
        return $data;
        /*
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        
        filter_fk_name($data['data'], array('shop_code|shop'));
        // print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
        */
    }
    function role_list(){
    	$sql = "select role_name,role_code from sys_role where sys = :sys and status = :status ";
    	$data = $this->db->get_all($sql, array(":sys" =>'0',":status" =>'1'));
    	return $data;
    }
    function save($ids,$select_role_code){
    	$this->begin_trans();
    	try{
    		
    		$sql = "select * from sys_role_sensitive_data where role_code = :role_code";
    		$data = $this->db->get_row($sql, array(":role_code" => $select_role_code));
    		
    		if($data){
    			$this->db->create_mapper('sys_role_sensitive_data')->delete(array('role_code'=>$select_role_code));
    		}
    		
    		//增加敏感数据
    		foreach ($ids as $sensitive_code) {
    			
    			//$sql_mx_category .= ",('" . implode("','", $_new_row_category) . "')";
    		
    			$sql_mx .= ",('" . $select_role_code . "','" . $sensitive_code ."','".'1'. "')";
    		}
    		$sql_mx = substr($sql_mx, 1);
    		$is_filter_repeat = true;
    		$sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  sys_role_sensitive_data ' . '(role_code,sensitive_code,status) VALUES' . $sql_mx . ";";
    		
    		$ret = $this->db->query($sql);
    		if (!$ret) {
    			$this->rollback(); //事务回滚
    			return $this->format_ret("-1", '', 'insert_error');
    		}
    		
    		$this->commit();
    		
    		return $this->format_ret(1);
    	}catch (Exception $e){
    		$this->rollback();
    		return $this->format_ret(-1,array(),'DATABASE_ERROR'.$e->getMessage());
    	}
    }

    function delete_role($select_role_code){
    	$sql = "select * from sys_role_sensitive_data where role_code = :role_code";
    	$data = $this->db->get_row($sql, array(":role_code" => $select_role_code));
    	
    	if($data){
    		$this->db->create_mapper('sys_role_sensitive_data')->delete(array('role_code'=>$select_role_code));
    	}
    	return $this->format_ret(1);
    }


}
