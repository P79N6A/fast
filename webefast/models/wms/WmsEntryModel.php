<?php
// load_model('wms/WmsEntryModel')->add($record['sell_record_code'],'sell_record',$record['store_code']);
class WmsEntryModel extends BaseModel
{
	private $exists_wms_store_code_arr;

	function __construct(){
		parent::__construct();
	}

	function check_wms_store($efast_store_code,$record_type = ''){
		if (!isset($this->exists_wms_store_code_arr)){
			$this->exists_wms_store_code_arr = load_model('wms/WmsBaseModel')->get_exists_wms_store_code_arr($record_type);
		}
		if (in_array($efast_store_code,$this->exists_wms_store_code_arr)){
			return $this->format_ret(1);
		}else{ 
			return $this->format_ret(-1,'',$efast_store_code.'不是WMS仓库');
		}
	}

	function add($record_code,$record_type,$efast_store_code,$info = array())
	{
		$ret = $this->check_wms_store($efast_store_code,$record_type);
		if ($ret['status']<0){
			return $this->format_ret(10,'',$efast_store_code.'不是wms仓库');
		}
                //$record_type
               $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($efast_store_code);
               
               //利丰特殊处理
                $record_type_arr = array('sell_record','sell_return');
                if($wms_system_code=='lifeng'&& !in_array($record_type, $record_type_arr)){
                        return $this->format_ret(10,'',$efast_store_code.'利丰只对接B2C');
                }
                
		$ret = load_model('wms/WmsMgrModel')->uploadtask_add($record_code,$record_type,$efast_store_code,$info);
		return $ret;
	}

	function upload($record_code,$record_type,$efast_store_code,$info = array())
	{
		if ($record_type == 'sell_record' || $record_type == 'sell_return'){
			$tbl = "wms_oms_trade";
			$type = 'oms';
		}else{
			$tbl = "wms_b2b_trade";
			$type = 'b2b';
		}
		$ret = $this->check_wms_store($efast_store_code);
		if ($ret['status']<0){
			return $this->format_ret(1,'',$efast_store_code.'不是wms仓库');
		}

		$sql = "select id from {$tbl} where record_code = :record_code and record_type = :record_type";
        $task_id = ctx()->db->getOne($sql,array(':record_code'=>$record_code,':record_type'=>$record_type));

		$ret = load_model('wms/WmsMgrModel')->upload($task_id,$type);
		return $ret;
	}

	function cancel($record_code,$record_type,$efast_store_code,$is_cancel_tag = array('act'=>'unnotice_shipping'),$is_force = 0){
		if ($record_type == 'sell_record' || $record_type == 'sell_return'){
			$tbl = "wms_oms_trade";
			$type = 'oms';
		}else{
			$tbl = "wms_b2b_trade";
			$type = 'b2b';
		}
		$sql = "select id,cancel_response_flag,wms_order_flow_end_flag from {$tbl} where record_code = :record_code and record_type = :record_type";
		$row = ctx()->db->get_row($sql,array(':record_code'=>$record_code,':record_type'=>$record_type));
		if (empty($row)){
			return $this->format_ret(100,'','不是WMS单据');
		}
                
               $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($efast_store_code);
               $record_type_arr = array('sell_record','sell_return');
                if($wms_system_code=='lifeng'&& !in_array($record_type, $record_type_arr)){
                        return $this->format_ret(10,'',$efast_store_code.'利丰只对接B2C');
                }
                
                
		if ($row['wms_order_flow_end_flag'] > 0){
			return $this->format_ret(-1,'','wms已经收发货完成');
		}
		if ($row['cancel_response_flag'] == 10){
			return $this->format_ret(100,'','单据已取消成功');
		} 
          
                if($is_force==0){
                    $ret = load_model('wms/WmsMgrModel')->cancel($row['id'],$type,$is_cancel_tag);
                }else{
                 
                     $ret = load_model('wms/WmsMgrModel')->force_cancel($row['id'],$type,$is_cancel_tag);
              
                }
 
		if ($ret['status'] == 2){
			//用于异步模式的wms
			return $this->format_ret(-2,'','单据取消中，请等待取消成功，再进行操作');
		}
		if ($ret['status'] == 10){
			return $this->format_ret(10,'','单据未上传wms,取消wms单据成功');
		}
		if ($ret['status'] > 0 && $ret['status'] < 10){
			return $this->format_ret(10,'','单据已上传wms,调用wms取消接口成功');
		}
		return $ret;
	}
        function check_is_wms_record($record_code,$record_type ,$efast_store_code){
		if (!isset($this->exists_wms_store_code_arr)){
			$this->exists_wms_store_code_arr = load_model('wms/WmsBaseModel')->get_exists_wms_store_code_arr($record_type);
		}
		if (in_array($efast_store_code,$this->exists_wms_store_code_arr)){
			$sql = "select id from wms_b2b_trade where record_code=:record_code and record_type=:record_type ";
                        $sql_values = array(
                            ':record_code'=>$record_code,
                            ':record_type'=>$record_type,
                        );
                       $check  =  $this->db->get_value($sql,$sql_values);
                       if(!empty($check)){
                           return $this->format_ret(-1,'','WMS单据不能操作');
                       }else{
                           return $this->format_ret(1,'',$efast_store_code.'不是WMS仓库');
                       }
                        
                        
		}else{
			return $this->format_ret(1,'',$efast_store_code.'不是WMS仓库');
		}
	}
        

}