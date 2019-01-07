<?php
require_model('tb/TbModel');

class SysParamsModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'sys_params';
    }

    /**
    * 更新记录 $data = arrray('param_code'=>'value')
    */
    function save($data) {
        $ret = parent :: insert_dup($data,'UPDATE','value');
        return $ret;
    }

    
    function update($data,$where) {
        $ret = parent :: update_exp('sys_params',$data,$where);
        return $ret;
    }
    /**
    * 根据 param_code 来取记录  
    * $ext_info = 0 只返回key=>value,否则返回一行数据 key=>row
    * $is_parent = 0 说明code是具体设置项，否则就是一整个group,如code是group的，数组会分为2层
    */
   function get_val_by_code($code,$is_parent = 0,$ext_info = 0){
       $code_arr = array();
       static $code_data = array();

	    if (is_array($code)){
        	$code_list = "'".join("','",$code)."'";	
                $code_arr = $code;
	    }else{
		    $code_list = "'".$code."'";
                    $code_arr[] =$code; 
	    }
        $ret_data = array();    
        $is_find = 0;
        foreach($code_arr as $v_code){
            if(isset($code_data[$v_code])){
                $ret_data[$v_code] = $code_data[$v_code];
                $is_find = 1;
            }else{
                $is_find = 0;
                break;
            }
        }   
        //内存中查找到
       if($is_find==1){
           return $ret_data;
       }    
            

        if ($ext_info){
            $fld = "param_id,param_code,parent_code,param_name,type,value,remark";
        }else{
            $fld = "param_code,parent_code,value";
        }
        $tbl = $this->get_table();
        $sql = "select {$fld} from {$tbl} where ";
        if ($is_parent == 1){
            $sql .= " parent_code in($code_list)";
        }else{
            $sql .= " param_code in($code_list)";            
        }

        $db_arr = $this->db->getAll($sql);
        $result = array();
        foreach($db_arr as $sub_arr){
            if ($ext_info){
                $v = $sub_arr;
            }else{
                 $v = $sub_arr['value'];                   
            }
            if ($is_parent){
                $result[$sub_arr['parent_code']][$sub_arr['param_code']] = $v;
            }else{
                $result[$sub_arr['param_code']] = $v;                   
            }
        }
        
        $code_data = array_merge($code_data,$result);
        

        
        
        return $result;
   }

   function save_kc_sync_cfg($request){
    	$sql = "select shop_code from base_shop";
    	$shop_code_arr = ctx()->db->getAll($sql);
    	$ins_data = array();
    	foreach($shop_code_arr as $sub_shop){
	    	$_v = (int)$request['kc_sync_cfg_'.$sub_shop['shop_code']];
	    	$_v = $_v<0 ? 0 : $_v;
	    	$ins_data[] = array('shop_code'=>$sub_shop['shop_code'],'value'=>$_v);
    	}

		$ret = M('sys_kc_sync_cfg')->insert_dup($ins_data,$mode='update',$dup_update_fld='value');
                //执行全量库存同步
                $this->db->query("update sys_schedule_record set all_exec_time=0 where type_code='update_inv'");
		return $ret;	   
   }

    /**
     * 更新ag菜单，定时器
     * @param $aligenius_enable
     * @return array
     */
    function update_ag_info($aligenius_enable) {
        if (!in_array($aligenius_enable, array(0, 1))) {
            return $this->format_ret(-1);
        }
        if ($aligenius_enable == 0) {
            $action_status = 0;
            $cli_status = 0;
        } else {
            $action_status = 1;
            $cli_status = 1;
        }
        //更新菜单
        $this->update_exp('sys_action', array('status' => $action_status), array('action_id' => '4070000'));
        //更新定时器
        $this->update_exp('sys_schedule', array('status' => $cli_status), array('code' => 'cli_ag_record'));
    }
    /**
     * 更新定时任务
     * @param type $cli_code
     * @param type $status
     * @return type
     */
    function update_ag_cli($cli_code, $status) {
        if (!in_array($aligenius_enable, array(0, 1))) {
            return $this->format_ret(-1);
        }
        //更新定时器
        $this->update_exp('sys_schedule', array('status' => $status), array('code' => 'cli_'.$cli_code));
    }

}
