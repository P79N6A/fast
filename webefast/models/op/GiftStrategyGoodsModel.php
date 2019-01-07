<?php
require_model('tb/TbModel');

class GiftStrategyGoodsModel extends TbModel{
    

    function  get_table(){
        return 'op_gift_strategy_goods';
    }

    function update($data, $op_gift_strategy_goods_id) {
    	
    	$ret = parent::update($data, array('op_gift_strategy_goods_id' => $op_gift_strategy_goods_id));
    	return $ret;
    }
    function add_goods($param){
        $goods_arr = array();
        $detail_id = &$param['detail_id'];
        $strategy_code = &$param['strategy_code'];
        $is_gift= &$param['is_gift'];
        foreach ($param['data'] as $val){
            $goods = array(
                'strategy_code'=>$strategy_code,
                'op_gift_strategy_detail_id'=>$detail_id,
                'is_gift'=>$is_gift,
                'sku'=>$val['sku'],
                );
            $goods['num'] = isset($val['num'])?$val['num']:1;
            $goods_arr[] = $goods ;
        }
        $update_str = " num = VALUES(num) ";
        return  $this->insert_multi_duplicate($this->table, $goods_arr, $update_str);
 
    }
    
    

    function imoprt_detail($id,$sku_arr,$import_data,$is_lof=0,$sort=1){
    	//判断主单据的pid是否存在
    	//print_r($import_data);
    	//print_r($sort);
    	//exit;
    	$record = $this->is_exists($id, 'op_gift_strategy_detail_id');
    	if (empty($record['data'])) {
    		return $this->format_ret(false,array(),'此赠品策略明细不存在!');
    	}
    	$ret_lof =  load_model('prm/GoodsLofModel')->get_sys_lof();
    	$ret['data'] = array();
    	
    	$sku_str = "'" . implode("','", $sku_arr) . "'";
    	$detail_data = $this->db->get_all("select DISTINCT b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.price,g.sell_price,g.trade_price  from
    			goods_sku b
    			inner join  base_goods g ON g.goods_code = b.goods_code

    			where b.barcode in({$sku_str}) ");//sell_price
    			$detail_data_lof = array();
    	
    	foreach ($detail_data as &$val) {
    			$num =  $import_data[$val['barcode']]['num'];
    			if(is_numeric($num)&&$num>0){
    				
	    			$val['num'] = round($num);
	    			$key = array_search($val['barcode'],$sku_arr);
	    			/*
	    			$lof_val = $val;
	    			$lof_val['lof_no'] = $ret_lof['data']['lof_no'];
	    			$lof_val['production_date'] = $ret_lof['data']['production_date'];
	    			$detail_data_lof[] = $val;
	    			*/
	    			unset ($sku_arr[$key]);
    			}
        }
    	
    			$result['success'] = count($detail_data);
    			//"行<br>导入失败列表:<br>"+result.data.fail
    	
    			
    	//print_r($detail_data);
    	//print_r($id);
    	//exit;
    	//明细添加
    	$ret = $this->add_detail_action($id,$detail_data,$sort);
    	
    	$ret['data'] = '';
    	if(!empty($sku_arr)){
    		$result['fail'] = implode(',', $sku_arr);
    	}
    	$ret['data'] = $result;
    	return $ret;
    }
    /**
     * 新增多条明细记录
     */
    public function add_detail_action($pid, $ary_details,$is_gift=1) {
    	//判断主单据的pid是否存在
    	$record = $this->is_exists($pid, 'op_gift_strategy_detail_id');
    	 
    	if (empty($record['data'])) {
    		return $this->format_ret(false,array(),'此赠品策略明细不存在!');
    	}
    	
    	$this->begin_trans();
    	try{
    		
    		foreach($ary_details as $ary_detail){
    			
    			if(!isset($ary_detail['num'])||$ary_detail['num']==0){
    				continue;
    			}
    			
    			$ary_detail['op_gift_strategy_detail_id'] = $pid;
    			$ary_detail['strategy_code'] = $record['data']['strategy_code'];
    			$ary_detail['is_gift'] = $is_gift;
    			//todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
    			
    			//判断SKU是否已经存在
    			$check = $this->is_detail_exists($pid,$ary_detail['sku'],$ary_detail['is_gift']);
    			//print_r($check);
    			if($check){
    				
    				//更新明细数据
    				$ret = parent::update($ary_detail, array(
    						'op_gift_strategy_detail_id'=>$pid,'is_gift'=>$ary_detail['is_gift'],'sku'=>$ary_detail['sku']
    				));
    				
    			}else{
    				
    				//插入明细数据
    				$ret = $this->insert($ary_detail);
    			}
    			//if(1 != $ret['status']){
    				//return $ret;
    			//}
    			
    		}
    		
    		$this->commit();
    		return $this->format_ret(1);
    	}catch (Exception $e){
    		$this->rollback();
    		return $this->format_ret(-1,array(),'数据库执行出错:'.$e->getMessage());
    	}
    }
    /**
     * 根据明细的sku和主单据id,判断明细是否已经存在
     * @param   int     $pid    主单据ID
     * @param   string  $sku    SKU编号
     * @return  boolean 存在返回true
     */
    public function is_detail_exists($pid, $sku,$is_gift){
    	$ret = $this->get_row(array(
    			'op_gift_strategy_detail_id'=>$pid,
    			'is_gift'=>$is_gift,
    			'sku'=>$sku
    	));
    	
    	if($ret['status'] <> 'op_no_data'){
    		return true;
    	}else{
    		return false;
    	}
    }
    
    public function is_exists($value, $field_name = 'record_code') {
    
    	$m = load_model('op/GiftStrategyDetailModel');
    	$ret = $m->get_row(array($field_name => $value));
    	return $ret;
    }
    
    public function import_other_rule_goods($op_gift_strategy_detail_id_new,$op_gift_strategy_detail_id,$sort){
    	$detail = load_model('op/GiftStrategyDetailModel')->get_row(array('op_gift_strategy_detail_id'=>$op_gift_strategy_detail_id));
    	
    	if (empty($detail['data'])) {
    		return $this->format_ret('-1', array(), '关联的规则不存在!');
    	}
    	
    	$sql = "insert into op_gift_strategy_goods (op_gift_strategy_detail_id,strategy_code,sku,is_gift,num)
    	select '{$detail['data']['op_gift_strategy_detail_id']}' , '{$detail['data']['strategy_code']}' ,sku,is_gift,num from op_gift_strategy_goods where   op_gift_strategy_detail_id ='{$op_gift_strategy_detail_id_new}' and is_gift = '1'  ";
    	//echo $sql;
    	$ret = $this->db->query($sql);
    	if (!$ret) {
    		return $this->format_ret("-1", '', 'insert_error');
    	}
    	return $this->format_ret("1", '', '');;
    }
    public function  del_goods($fileld,$value){
    	$result = parent::delete(array($fileld=>$value));
    	return $result;
    }
    
    function get_goods_by_detail_range_id($detail_id,$range_id) {
        $data = $this->get_all(array('op_gift_strategy_detail_id' => $detail_id,'op_gift_strategy_range_id' => $range_id));
        return $data;
    }
    function get_goods_by_range_id($detail_id,$type) {
        $sql = "SELECT goods_condition FROM op_gift_strategy_detail WHERE op_gift_strategy_detail_id = '{$detail_id}'";
        $goods_condition = $this->db->get_value($sql);
        $error = '';
        $error_no = 0; 
        if($type == 1 && $goods_condition != 2) {
            $sql = "SELECT * FROM op_gift_strategy_goods WHERE op_gift_strategy_detail_id = '{$detail_id}' AND is_gift = 0";
            $data = $this->db->get_all($sql);
            if(empty($data)) {
                $error_no = 1;
            }
        }
        $sql = "SELECT * FROM op_gift_strategy_goods WHERE op_gift_strategy_detail_id = '{$detail_id}' AND is_gift = 1";
        $data = $this->db->get_all($sql);
        if (empty($data) && $error_no == 1) {
            $error = '请设置活动商品和赠品';
        } else if(empty($data) && $error_no == 0) {
            $error = '请设置赠品';
        } else if(!empty($data) && $error_no == 1) {
            $error = '请设置活动商品';
        }
        if($type == 2) {
            $sql = "SELECT ranking_hour FROM op_gift_strategy_detail WHERE op_gift_strategy_detail_id = '{$detail_id}'";
            $data = $this->db->get_value($sql);
            if(empty($data)) {
                $error = "请设置指定时间";
            }
        }
        if(!empty($error)) {
            return $this->format_ret(-1,'' , $error);
        }
        return $this->format_ret(1);
        
    }

}
