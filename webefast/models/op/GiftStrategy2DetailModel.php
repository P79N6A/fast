<?php
require_model('tb/TbModel');

class GiftStrategy2DetailModel extends TbModel{
    public $gift_type = array(
        0 => '满送',
        1 => '买送',
        2 => '排名送',
    );
            
    function  get_table(){
            return 'op_gift_strategy_detail';
    }

    function add(array $data){ 
    	if (!isset($data['sort'])){
    		//$sql = "select max(sort) from {$this->table} where strategy_code='{$data['strategy_code']}'";
    		//$sort = $this->db->getOne($sql);
    		$data['sort'] =1;//默认为1
    		
    	}
    	if (!isset($data['level'])) {
    		$sql = "select max(level) from {$this->table} where strategy_code='{$data['strategy_code']}'";
    		$level = $this->db->getOne($sql);
    		$data['level'] = $level+1;
    	}
    	if (!isset($data['name'])){
            if($data['type'] == 0){
                $data['name'] = "满送"."规则".$data['sort'];
            }
            if($data['type'] == 1){
                $data['name'] = "买送"."规则".$data['sort'];
            }
            if($data['type'] == 2){
                $data['name'] = "排名送"."规则".$data['sort'];
            }
    	
    	}
        $data['ranking_time_type']=1;
    	parent::insert($data);
        $sql = "select op_gift_strategy_detail_id from op_gift_strategy_detail where strategy_code = :strategy_code and type = :type and level = :level";
        $flg = $this->db->get_value($sql,array(':strategy_code'=>$data['strategy_code'],':type'=>$data['type'],':level'=>$data['level']));
        return $flg;
    }
    
    function ranking_edit($data){
        $detail = $this->get_by_id($data['op_gift_strategy_detail_id']);
        if($detail['status'] != 1){
            return $this->format_ret(-1, '','规则信息不存在！');
        }
        $sql = "SELECT * FROM op_gift_strategy_range WHERE op_gift_strategy_detail_id = '{$data['op_gift_strategy_detail_id']}';";
        $renge_data = $this->db->get_all($sql);
        if(empty($renge_data)) {
            return $this->format_ret(-1, '','请保存范围');
        }
        if(isset($data['is_goods_money']) && $data['is_goods_money'] == 1){
            $data['money_min'] = !empty($data['money_min']) ? $data['money_min'] : '0';
        } else {
            $data['money_min'] = 0;
        }
        foreach ($data as &$d){
            $d = trim($d);
        }
        $ret = $this->update($data, array('op_gift_strategy_detail_id'=>$data['op_gift_strategy_detail_id']));
        return $ret;
    }
            
    function edit(array $data){
        if($data['range_type']!=1 && $data["goods_condition"] != 2){
            $sql = "SELECT * FROM op_gift_strategy_range WHERE op_gift_strategy_detail_id = '{$data['op_gift_strategy_detail_id']}';";
            $renge_data = $this->db->get_all($sql);
            if(empty($renge_data)) {
                return $this->format_ret(-1, '','请保存范围');
            }
        }
        
    	$detail = $this->get_by_id($data['op_gift_strategy_detail_id']);
    	$up = array(
    			'name'=>$data['name'],
    			'level'=>$data['level'],//优先级
    			'is_mutex'=>$data['is_mutex'],//互溶 互斥
    			//'time_type'=>$data['time_type'],
            
    			'sort'=>$data['sort'],//分组
    			'range_type'=>$data['range_type'],//手工 、倍增
    			);
    	//满赠
    	if ($detail['data']['type'] == 0){
    		
    	} else {
    		//买赠
    		$up['goods_condition'] = $data['goods_condition'];
    		if ($up['goods_condition'] == 1){
    			//随机商品与数量
    			$up['buy_num'] = $data['buy_num'];
    		}
    	}
    	if (isset($data['is_contain_delivery_money'])){
    		$up['is_contain_delivery_money'] = $data['is_contain_delivery_money'];
    	} else {
    		$up['is_contain_delivery_money'] = 0;
    	}
      if (isset($data['is_goods_money'])){
    		$up['is_goods_money'] = $data['is_goods_money'];
    	} else {
    		$up['is_goods_money'] = 0;
    	}
    	//倍增
    	if ($data['range_type'] == 1){
    		$up['doubled'] = $data['doubled'];
    	}
  
        
        
    	$ret = $this->update($up, array('op_gift_strategy_detail_id'=>$data['op_gift_strategy_detail_id']));
    	if ($data['range_type'] == 1) {
    		//删除设定的金额/数量范围
    		$ret = load_model('op/GiftStrategyRangeModel')->del_by_detail_id($data['op_gift_strategy_detail_id']);
    	}
    	if($ret['status'] == -1) {
    		return $this->format_ret(-1, '','操作失败');
    	}
    	
    	return $this->format_ret(1, '','操作成功');
    	
    }
    function update_active($active, $id) {
    	if (!in_array($active, array(0, 1))) {
    		return $this->format_ret('error_params');
    	}
          $action_name = ($active==1)?'规则启用':'规则停用';
          $ret_strategy =  $this->get_by_id($id);
          $data = array(
                'strategy_code'=>$ret_strategy['data']['strategy_code'],
                'action_name'=>$action_name,
           );
          load_model('op/GiftStrategyLogModel')->insert($data);
          $up = array('status' => $active);
          $ret = parent :: update($up, array('op_gift_strategy_detail_id' => $id));
    	  return $ret;
    }
    function get_by_id($id) {
    	$data = $this->get_row(array('op_gift_strategy_detail_id' => $id));
    	return $data;
    }
    function get_by_page($filter) {
    	$sql_main = "FROM {$this->table} rl WHERE 1";
    	
    	$sql_values = array();
    	//策略代码
    	if (isset($filter['strategy_code']) && $filter['strategy_code'] != '') {
            $sql_main .= " AND rl.strategy_code = :strategy_code ";
            $sql_values[':strategy_code'] = $filter['strategy_code'];
    	}
    	$select = 'rl.*';
    	$sql_main .= " order by sort";
    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
    	foreach ($data['data'] as $key => $value) {
            if($value['type'] == 2){
                $data['data'][$key]['is_mutex_txt'] = '';
            } else {
                $data['data'][$key]['is_mutex_txt'] = $value['is_mutex']==0?'互斥':'互溶';
            }
            $data['data'][$key]['type_txt'] = $this->gift_type[$value['type']];
    	}
    	$ret_status = OP_SUCCESS;
    	$ret_data = $data;
    	return $this->format_ret($ret_status, $ret_data);
    }
    
    
    function del_detail($id){
    	
       $ret = load_model('op/GiftStrategyGoodsModel')->del_goods('op_gift_strategy_detail_id',$id);
       $ret = parent::delete(array('op_gift_strategy_detail_id'=>$id));
    	return $ret;
    }
    
    function get_by_page_list($filter) {
        $sql_join = " INNER JOIN op_gift_strategy as r2 ON rl.strategy_code=r2.strategy_code ";
        $sql_main = " FROM {$this->table} rl $sql_join WHERE 1 AND rl.type = 2 and rl.status = 1 and r2.is_check = 1 and r2.status = 1 ";
        $sql_values = array();
        //名称或代码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
                $sql_main .= " AND (rl.name LIKE :code_name or r2.strategy_name LIKE :code_name)";
                $sql_values[':code_name'] = '%' .$filter['code_name'].'%';
        }
       
        $select = 'rl.*,r2.start_time,r2.end_time,r2.strategy_name,r2.shop_code ';
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        $ret_status = OP_SUCCESS;
        foreach($data['data'] as &$sub_data){
            $sub_data['active_time'] = date("Y-m-d H:i:s",$sub_data['start_time']).'~'.date("Y-m-d H:i:s",$sub_data['end_time']);
            $sub_data['shop_code'] = load_model('op/GiftStrategyShopModel')->get_shops_info_by_strategy_code_rank($sub_data['strategy_code']);
        }
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    /*设置赠送数量*/
    function edit_num($id,$num,$goods_id,$shop){
       // $sku = $this->db->get_value('select sku from goods_sku where barcode=:barcode',array(':barcode'=>$barcode));
        $inv_num = $this->get_effec_num_common($shop,$barcode);
        $allow = $this->db->get_value('select is_allow_sync_inv from api_goods_sku where sys_goods_barcode=:barcode',array(':barcode'=>$barcode));
        if($num > $inv_num && $allow == 1){
            $ret = parent::update_exp('op_gift_strategy_goods',array('gifts_num'=>$num),array('op_gift_strategy_detail_id'=>$id,'op_gift_strategy_goods_id'=>$goods_id));
            return $this->format_ret(2, '','修改成功，注：当前设置库存大于商品可用，请确保商品不会超卖！');
        }else{
            $ret = parent::update_exp('op_gift_strategy_goods',array('gifts_num'=>$num),array('op_gift_strategy_detail_id'=>$id,'op_gift_strategy_goods_id'=>$goods_id));
            return $this->format_ret(1, '','修改成功');
        }
    }
    /*获取商品可用库存*/
    function get_effec_num_common($shop,$goods){
        $data = array();
        $data_shop = explode(',',$shop);
        $num_effec = 0;
        foreach($data_shop as $value){
            $store_sql = "select stock_source_store_code from base_shop where shop_code= :shop";
            $ret = $this->db->get_row($store_sql,array(':shop'=>$value));
            foreach(explode(',',$ret['stock_source_store_code']) as $s){
                $data[] = $s;
            }
        }
        $data = array_unique($data);
        foreach($data as $val){
            $sql_num = "select rl.stock_num,rl.lock_num from goods_inv rl inner join goods_sku r2 on rl.sku=r2.sku where rl.store_code= :val and r2.barcode= :goods ";
            $num = $this->db->get_row($sql_num,array(':val'=>$val,':goods'=>$goods));
            $num_new = $num['stock_num'] - $num['lock_num'];
            $num_effec += $num_new;
        }
        return $num_effec;
    }
    function is_set_gift_num($id,$is_set_gift){
        if($is_set_gift == 0){
            return $this->format_ret(1, '','');
        }else{
            $sql = "select count(1) from op_gift_strategy_goods where is_gift = 1 and op_gift_strategy_detail_id = :id and gifts_num = 0";
            $data = $this->db->get_value($sql,array(':id'=>$id));
                if($data != 0){
                    return $this->format_ret(-1, '','请设置赠品限量赠送数量');
                }
        }
        return $this->format_ret(1, '','');
    }
}
