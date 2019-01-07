<?php
require_model('tb/TbModel');

class GiftStrategyShopModel extends TbModel{
    
    function  get_table(){
        return 'op_gift_strategy_shop';
    }
    
    function add($strategy_code,$shops){
    	$this->del_by_strategy_code($strategy_code);
    	foreach ($shops as $shop_code) {
    		$data = array(
    				'strategy_code' =>$strategy_code,
    				'shop_code'=>$shop_code
    				);
    		$ret = parent::insert($data);
    	}
    	if ($ret['status'] == -1){
    		return $this->format_ret(-1,'','店铺添加失败');
    	}
    	return $this->format_ret(1,'');
    }
    function get_shops_by_strategy_code($strategy_code) {
    	$ret = $this->get_all(array('strategy_code' => $strategy_code));
    	$shops = array();
    	if (!empty($ret['data'])){
    		foreach ($ret['data'] as $shop_row) {
    			$shops[] = $shop_row['shop_code'];
    		}
    	}
    	return $shops;
    }
    function get_shops_info_by_strategy_code($strategy_code) {
    	$sql = "select rl.shop_code,s.shop_name from $this->table as rl,base_shop as s where rl.shop_code=s.shop_code and rl.strategy_code='{$strategy_code}'";
    	$shops = $this->db->get_all($sql);
    	$shop_arr = array();
    	foreach ($shops as $shop_row){
    		$shop_arr['shop_code'][] = $shop_row['shop_code'];
    		$shop_arr['shop_name'][] = $shop_row['shop_name'];
    	}
    	return $shop_arr;
    }
    
    function get_shops_info_by_strategy_code_rank($strategy_code){
        $sql = "select rl.shop_code,s.shop_name from $this->table as rl,base_shop as s where rl.shop_code=s.shop_code and rl.strategy_code='{$strategy_code}'";
        $shops = $this->db->get_all($sql);
        $shop_arr = array();
        $i = 0;
        foreach ($shops as $shop_row){
            $shop_arr[$i]['shop_code'] = $shop_row['shop_code'];
            $shop_arr[$i]['shop_name'] = $shop_row['shop_name'];
            $i++;
    	}
    	return $shop_arr;
    }
            
    
    function del_by_strategy_code($strategy_code){
    	 
    	$ret = parent::delete(array('strategy_code'=>$strategy_code));
    	return $ret;
    }
    //修改赠送方式  固定、随机
    function edit($data){
    	$up = array();
    	//赠品随机数
    	if (isset($data['gift_num']) && $data['give_way'] == 1){
    		$up['gift_num'] = $data['gift_num'];
    	}
    	if (isset($data['give_way'])){
    		$up['give_way'] = $data['give_way'];
    	}
    	
    	$ret = load_model('op/GiftStrategy2GoodsModel')->get_by_detail_id($data['op_gift_strategy_detail_id'],$data['range_id'],1);
    	if (!empty($ret)) {
    		return $this->format_ret(-1,'','已添加的此类型的赠品商品，不允许修改赠送类型');
    	}
    	//倍增
    	if ($data['range_type'] == 1) {
    		
    	
    		$ret = load_model('op/GiftStrategy2DetailModel')->update($up, array('op_gift_strategy_detail_id'=>$data['op_gift_strategy_detail_id']));
    	
    	} else {
    		//手工
    		$ret = $this->update($up, array('id'=>$data['range_id']));
    	}
    	return $ret;
    }
    
    function get_shop_by_code($strategy_code) {
        $data = $this->get_all(array('strategy_code' => $strategy_code));
        return $data;
    }
    
}
