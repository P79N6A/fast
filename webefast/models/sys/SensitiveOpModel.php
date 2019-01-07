<?php

require_model('tb/TbModel');

class SensitiveOpModel extends TbModel {

    function get_table() {
        return 'sys_sensitive_data';
    }
    private $sensitive_power  = 0;
    function __construct($table = '', $pk = '', $db = '') {
        parent::__construct();
        $arr = load_model('sys/SysParamsModel')->get_val_by_code(array('sensitive_power'));
        $this->sensitive_power = $arr['sensitive_power'];
    }
    
    function get_role_sensitive_data(){
       if($this->sensitive_power==0){
            return array();
        }
        static  $sensitive_data = NULL;
        if($sensitive_data==NULL){
            $role_arr = $this->get_user_role();
            $role_code_str = "";
            if(empty($role_arr)){
                $sensitive_data = array();
            }else{
                $role_code_str = "'".implode("','", $role_arr)."'";
                $sql = "select DISTINCT d.* from sys_role_sensitive_data  r
                    INNER JOIN sys_sensitive_data   d on r.sensitive_code = d.sensitive_code 
                    where r.status=1 and  role_code in({$role_code_str})";
                $sql_values = array(':relation_table'=>$table);
                $data = $this->db->get_all($sql,$sql_values);
                foreach($data as $val){
                    $relation_table_data = json_decode($val['relation_table'],true);
                    foreach($relation_table_data as $tb=>$col){
                        $sensitive_data[$tb][$col] = $val;
                    }
                }
            }
       }
      return $sensitive_data;
        
    }
    private function get_user_role(){
        $role = CTX()->get_session('role',true);
    
        $role_arr = array();
        if(!empty($role['data'])){
            foreach($role['data'] as $val){
                $role_arr[] =  $val['role_code'];
            }
        }
        
        return $role_arr;
        
    }
    
    function convert_column($table,$column,&$val){
        if($this->sensitive_power==0){
            return $val;
        }
        
        $sensitive_data = $this->get_role_sensitive_data();
        if(isset($sensitive_data[$table][$column])){
               $val = $this->convert_sensitive_data($sensitive_data[$table][$column],$val);
        }
    }
    
    function convert_row($table,&$row){
        if($this->sensitive_power==0){
            return $row;
        }
        foreach($row as $key=>&$val){
            $val = $this->convert_column($table,$key,$val);
        }
    }
    
    function convert_data($table,&$data){
         if($this->sensitive_power==0){
            return $row;
        }
         foreach($data as &$row){
             $this->convert_row($table,$row);
         }
    }
    
    private function convert_sensitive_data($rule,$val){
        $new_val = "";
        if($rule['type']==0){
            if($rule['start_len']>0){
                //$new_val = substr($val,0,$rule['start_len']);
            	require_lib ( 'comm_util', true );
                $new_val = left($val,$rule['start_len']);
            }
        }else{
           $find =  $this->seach_province($val);
           
           $new_val = substr($val,0,$find);
        }
        $new_val .="***";
        return $new_val;
    }
    private function seach_province($str){

        $seach_arr = array(
            '0'=>'省',
            '1'=>'自治区',
            '2'=>'北京',
            '3'=>'上海',
            '4'=>'天津',
            '5'=>'重庆',
            '6'=>'行政区',
            '7'=>'台湾');
        $find = 0 ;
        foreach($seach_arr as $search){
            $seach_find = strpos($str,$search);
            if($seach_find!==false){
                $seach_find =$seach_find+strlen($search);
                if($seach_find<$find||$find==0){
                    $find = $seach_find;
                }
            }
        }
        return ($find==0)?5:$find;
        
    }
    

}