<?php
require_model('tb/TbModel');

class GiftStrategyRangeModel extends TbModel{
    
    function  get_table(){
        return 'op_gift_strategy_range';
    }

    function add(array $data){
    	
    	$ret = $this->check($data);
    	if ($ret['status'] == -1){
    		return $ret;
    	}
    	unset($data['strategy_new_type']);
    	return parent::insert($data);
    }
    
    //校验区间是否有重叠
    function check($data){
        //判断是新策略还是旧策略
        if ($data['strategy_new_type'] == 0) {
            if (intval($data['range_start']) >= intval($data['range_end'])) {
                return $this->format_ret(-1, '', '数据范围设置值不能相等！');
            }
            $sql = "select count(1) from $this->table where op_gift_strategy_detail_id={$data['op_gift_strategy_detail_id']} and ((range_start<{$data['range_start']} and range_end>{$data['range_start']}) or (range_start<{$data['range_end']} and range_end>={$data['range_end']}) or (range_start>{$data['range_start']} and range_end<={$data['range_end']}) or (range_start={$data['range_start']})) ";
        } else {
            if (intval($data['range_start']) > intval($data['range_end'])) {
                return $this->format_ret(-1, '', '数据范围设置值不能相等！');
            }
            $sql = "select count(1) from $this->table where op_gift_strategy_detail_id={$data['op_gift_strategy_detail_id']} and ((range_start<={$data['range_start']} and range_end>={$data['range_start']}) or (range_start<={$data['range_end']} and range_end>={$data['range_end']}) or (range_start>={$data['range_start']} and range_end<={$data['range_end']}) or (range_end={$data['range_start']})) ";
        }
        $num = $this->db->getOne($sql);
    	if ($num > 0){
    		return $this->format_ret(-1,'','与其他区间有重叠');
    		
    	}
    	return $this->format_ret(1,'');
    }
    function get_by_id($id) {
    	$data = $this->get_row(array('id' => $id));
    	return $data;
    }
    function get_by_detail_id($id) {
    	$data = $this->get_all(array('op_gift_strategy_detail_id' => $id));
    	return $data;
    }
    
    function get_last_range_by_id($id){
        $sql = "select range_end from {$this->table} where op_gift_strategy_detail_id = :op_gift_strategy_detail_id order by id desc";
        $range_end = $this->db->get_value($sql,array(":op_gift_strategy_detail_id" => $id));
        if($range_end === FALSE){
            $range_end = 0;
        }
        return $range_end;
    }
    function get_by_page($filter) {
    	$sql_main = "FROM {$this->table} rl WHERE 1";
    	
    	$sql_values = array();
    	//策略代码
    	if (isset($filter['op_gift_strategy_detail_id']) && $filter['op_gift_strategy_detail_id'] != '') {
            $sql_main .= " AND rl.op_gift_strategy_detail_id = :op_gift_strategy_detail_id ";
            $sql_values[':op_gift_strategy_detail_id'] = $filter['op_gift_strategy_detail_id'];
    	}
    	$select = 'rl.*';
    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
    	foreach ($data['data'] as $key => $value) {
    	}
    	$ret_status = OP_SUCCESS;
    	$ret_data = $data;
    	return $this->format_ret($ret_status, $ret_data);
    }
    function remove($id){
    	$ret = $this->del_by_id($id);
    	if ($ret['status'] == 1){
    		//删除该范围下的的赠品
    		$ret = load_model('op/GiftStrategy2GoodsModel')->del_goods('op_gift_strategy_range_id',$id);
    	}
    	if($ret['status'] == 1){
    		return $this->format_ret(1,'','操作成功');
    	} else {
    		return $this->format_ret(-1,'','操作失败');
    	}
    }
    function del_by_id($id){
    	 
    	$ret = parent::delete(array('id'=>$id));
    	return $ret;
    }
    function del_by_detail_id($id){
    	
       $ret = parent::delete(array('op_gift_strategy_detail_id'=>$id));
    	return $ret;
    }
    //修改赠送方式  固定、随机
    function edit($data){
        $ret_way = $this->get_by_id($data['range_id']);
    	$up = array();
    	//赠品随机数
    	if (isset($data['gift_num']) && $data['give_way'] == 1){
    		$up['gift_num'] = $data['gift_num'];
    	}
    	if (isset($data['give_way'])){
    		$up['give_way'] = $data['give_way'];
    	}
    	
    	$ret = load_model('op/GiftStrategy2GoodsModel')->get_by_detail_id($data['op_gift_strategy_detail_id'],$data['range_id'],1);
        if (!empty($ret) && ($ret_way['data']['give_way'] != $data['give_way'])) {
    		return $this->format_ret(-1,'','已添加的此类型的赠品商品，不允许修改赠送类型');
    	}
    	//倍增
    	if ($data['range_type'] == 1 || $data['goods_condition'] == 2) {
    		
    	
    		$ret = load_model('op/GiftStrategy2DetailModel')->update($up, array('op_gift_strategy_detail_id'=>$data['op_gift_strategy_detail_id']));
    	
    	} else {
    		//手工
    		$ret = $this->update($up, array('id'=>$data['range_id']));
    	}
    	return $ret;
    }
    
    function get_rank_rule(){
        $sql = "select r2.* from op_gift_strategy_detail r1 inner join {$this->table} r2 on r1.op_gift_strategy_detail_id = r2.op_gift_strategy_detail_id where r1.type = 2";
        $rank_rule = $this->db->get_all($sql);
        return $rank_rule;
    }
    
    
}
