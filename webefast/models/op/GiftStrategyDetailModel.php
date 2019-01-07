<?php
require_model('tb/TbModel');

class GiftStrategyDetailModel extends TbModel{
    
    function  get_table(){
        return 'op_gift_strategy_detail';
    }

     //其他规则明细
    function get_other_rule($op_gift_strategy_detail_id,$strategy_code,$sort){
    	//$sql = "select op_gift_strategy_detail_id from op_gift_strategy_detail where op_gift_strategy_detail_id <> '{$op_gift_strategy_detail_id}' and strategy_code = '{$strategy_code}' and type = '{$sort}' ";
    	//$sql = "select op_gift_strategy_detail_id from op_gift_strategy_detail where  strategy_code = '{$strategy_code}' and type = '{$sort}' ";
    	$sql = "select op_gift_strategy_detail_id from op_gift_strategy_detail where  strategy_code = '{$strategy_code}' ";
    	$data = $this->db->get_all($sql);
    	$ret_status = 1;
    	if (empty($data)) {
    		$ret_status = -1;
    	}
    	return $this->format_ret($ret_status, $data);
    }
    //赠品数据
    function get_gift_list($arr) {
    	$sql = "select * FROM op_gift_strategy_detail where 1 ";
    	foreach($arr as $key1 => $value1){
    		$key = substr($key1,1);
    		$sql .= " and {$key} = {$key1} ";
    	}
    	
    	$rs = $this->db->get_all($sql,$arr);
        foreach($rs as $k=>$v){
        	$sql2 = "select r1.*,r4.barcode,r4.spec1_code,r4.spec2_code,r4.spec2_name,r4.spec1_name,r3.goods_code,r3.goods_name FROM op_gift_strategy_goods r1
        			
		            INNER JOIN goods_sku r4 on r1.sku = r4.sku
		            INNER JOIN base_goods r3 on r3.goods_code = r4.goods_code
        	         where op_gift_strategy_detail_id = '{$v['op_gift_strategy_detail_id']}' and  strategy_code  = '{$v['strategy_code']}' ";
        	$goods = $this->db->get_all($sql2);
        	$gift = array();
        	$buy_goods = array();
        	foreach($goods as $k1=>$v1){
        		if($v1['is_gift'] == '1'){
        		   $gift[] = $v1;	
        		}else{
        			$buy_goods[] = $v1; 
        		}	
        	}
        	$rs[$k]['gift'] = $gift;
        	$rs[$k]['goods'] = $buy_goods;
        }
    	return $rs;
    }
    
    //修改规则1
    function update_rule1_save($b,$goodsbuy){
        $ret=  $this->check_money($b);
        if($ret['status']<0){
            return $ret;
        }
        $arr_data = array();
        $arr_data1 = array();
        foreach($b as $key => $v){
        	 
        	$arr = explode('|',$key);
        	$id = $arr[0];
        	$field = $arr[1];
        	// if($field == 'is_contain_delivery_money' ){
        	//	$v = '1';
        	// }
        	$arr_data[$id][$field] = $v;
        }
        foreach($arr_data as $k3 => $v3){
        	if($v3['type'] == '0'){
        		$arr_data1[] = $v3;
        	}	
        } 
        $cnt  = count($arr_data1);
        for($i = 0;$i <= $cnt ; $i++  ){
        	for($j = $i+1 ; $j <= $cnt ;  $j++ ){
        		if(($arr_data1[$i]['money_min'] == $arr_data1[$j]['money_min']) || ($arr_data1[$i]['money_max'] == $arr_data1[$j]['money_max'])){
        			return $this->format_ret(-1,'',"不能有重复");
        		}		
        	}
        }
       
    	$this->begin_trans();
    	try{    
            //print_r($b);    
    		//print_r($arr_data);exit;
    		foreach($arr_data as $k2 => $v2){
    			if($v2['type'] == '0'){ //满赠	
	    			if(isset($v2['is_contain_delivery_money']) ){
	    				$is_contain_delivery_money = '1';
	    			}else{
	    				$is_contain_delivery_money = '0';
	    			}
	    			
	    			$r = $this->db->update('op_gift_strategy_detail',
	    					 array('money_min'=>$v2['money_min'],
	    					 		'money_max'=>$v2['money_max'],
	    					 		'gift_num'=>$v2['gift_num'],
	    					 		'give_way'=>$v2['give_way'],
	    					 		'is_mutex'=>$v2['is_mutex'],
	    					 		'is_contain_delivery_money'=>$is_contain_delivery_money,
                                                                 'is_fixed_customer'=>$v2['is_fixed_customer'],
	    					 		), 
	    					array('op_gift_strategy_detail_id'=>$k2));
	    			if(!empty($goodsbuy[$k2]['gift']) && $v2['give_way'] == '0' ){
	    				foreach($goodsbuy[$k2]['gift'] as $gift_k=>$gift_v){
	    					$r1 = $this->db->update('op_gift_strategy_goods',
	    							array('num'=>$gift_v['num']),
	    							array('op_gift_strategy_goods_id'=>$gift_k));
	    				}
	    			}
    			}else{
    				//买赠
    				$r = $this->db->update('op_gift_strategy_detail',
    						array('goods_condition'=>$v2['goods_condition'],
    								'give_way'=>$v2['give_way'],
    								'is_mutex'=>$v2['is_mutex'],
    								'buy_num'=>$v2['buy_num'],
    								'gift_num'=>$v2['gift_num'],
                                                                'is_repeat'=>$v2['is_repeat'],
                                                                 'is_fixed_customer'=>$v2['is_fixed_customer'],
    						),
    						array('op_gift_strategy_detail_id'=>$k2));
    				if(!empty($goodsbuy[$k2]['gift']) && $v2['give_way'] == '0' ){
    					foreach($goodsbuy[$k2]['gift'] as $gift_k=>$gift_v){
    						$r1 = $this->db->update('op_gift_strategy_goods',
    								array('num'=>$gift_v['num']),
    								array('op_gift_strategy_goods_id'=>$gift_k));
    					}
    				}
    				if(!empty($goodsbuy[$k2]['goods']) && $v2['goods_condition'] == '0' ){
    					foreach($goodsbuy[$k2]['goods'] as $goods_k=>$goods_v){
    						$r1 = $this->db->update('op_gift_strategy_goods',
    								array('num'=>$goods_v['num']),
    								array('op_gift_strategy_goods_id'=>$goods_k));
    					}
    				}
    			}
    			
    			if($r !== true){
    				throw new Exception('保存失败');
    			}
    		}
    		//print_r($arr_data);exit;
    		$this->commit();
    		return array('status'=>1, 'message'=>'更新成功');
    	} catch(Exception $e){
    		$this->rollback();
    		return array('status'=>-1, 'message'=>$e->getMessage());
    	}
    }
    
    function check_money($b){
        $money_all = array();
        $sort_min_arr = array();
        foreach($b as $key=>$val){
             $arr = explode('|',$key);
             if($arr[1]=='money_min'){
               $money_all[$arr[0]]['money_min'] = $val ; 
               $sort_min_arr[$val] = $arr[0];
             }
             if($arr[1]=='money_max'){
               $money_all[$arr[0]]['money_max'] = $val ; 
             }
        }
        ksort($sort_min_arr);
        $min_arr = array();
       
        foreach($sort_min_arr as $key){
            $money_arr = $money_all[$key];
              if($money_arr['money_min']>$money_arr['money_max']){
                   return $this->format_ret(-1,'',"最小金额{$min_arr['money_max']}不能大于最大金额{$money_arr['money_max']}");
              }
            if(empty($min_arr)){
                $min_arr = $money_arr;
            }else{
                if($min_arr['money_max']>=$money_arr['money_min']){
                    return $this->format_ret(-1,'',"金额不能交叉最大金额{$min_arr['money_max']}大于最小金额{$money_arr['money_min']}");
                }else{
                     $min_arr = $money_arr;
                }
            }
        }
    }
    private function  sort_arr($money_all){
        $new_array = array();
        foreach($money_all as $val){
            
        }
        
        
    }
    
    function get_detail_by_code($strategy_code) {
        $data = $this->get_all(array('strategy_code' => $strategy_code));
        return $data;
    }
    
    function del_detail($id){
    	
       $ret = load_model('op/GiftStrategyGoodsModel')->del_goods('op_gift_strategy_detail_id',$id);
       $ret = parent::delete(array('op_gift_strategy_detail_id'=>$id));
    	return $ret;
    }
}
