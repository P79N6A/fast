<?php

require_model('tb/TbModel');

class SellProblemModel extends TbModel{

	private $question_map;
	private $active_question_code = NULL;
	private $skip_problem_cfg = array('STOREHOUSE_CANCEL','CHANGE_GOODS_MAKEUP','WMS_SHORT_ORDER','FULL_REFUND','REFUND');
	
	function get_problem_list(){
            if($this->active_question_code===NULL){
                $sql = "select question_label_code,question_label_name from base_question_label where is_active = 1";
                $db_question = ctx()->db->get_all($sql);
                $this->question_map = load_model('util/ViewUtilModel')->get_map_arr($db_question,'question_label_code',0,'question_label_name');
                $this->active_question_code = array_keys($this->question_map);
            
            }
            
	    return $this->format_ret(1);
	}

	function set_problem($sell_record_data){
		$this->get_problem_list();
		$is_problem = 0;
		$sell_record_code = $sell_record_data['sell_record_code'];

	    foreach($this->active_question_code as $_code){
		    if (in_array($_code,$this->skip_problem_cfg)){
			    continue;
		    }
		    $class = 'oms/sell_problem/'.str_replace(' ','',ucwords(str_replace(array('/','_'),array(' ',' '),strtolower($_code)))).'Model';
			$ret = load_model($class)->handler($sell_record_data);
			//echo '<hr/>$class<xmp>'.var_export($class,true).'</xmp>';
			//echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
			
			if ($ret['status'] >0){
				$this->save_problem($sell_record_code,$_code, $ret['data']);
				$is_problem = 1;
			}
		}
        if ($is_problem == 1){
	        $prob = array('is_problem'=>1);
	        $ret = load_model("oms/SellRecordModel")->update($prob, array('sell_record_code' => $sell_record_code));
	        return $ret;
        }
        return $this->format_ret(1);		
	}

	function save_problem($sell_record_code,$q_code, $data = ''){
            if(!isset($this->question_map[$q_code])){
               $this->get_problem_list();
            }
	    $tag_desc = isset($this->question_map[$q_code])?$this->question_map[$q_code]:'';
            
        $ins = array('sell_record_code'=>$sell_record_code,'tag_type'=>'problem','tag_v'=>$q_code,'tag_desc'=>$tag_desc);
        $ret = load_model("oms/SellRecordTagModel")->insert_dup($ins);
        if($q_code == 'BELOW_NORMAL_PRICE') { //低于售价，添加操作日志
            $goods_str = implode(',', $data);
            load_model('oms/SellRecordModel')->add_action($sell_record_code, '设为问题单', $goods_str . '商品，商品价格低于最低售价，设为问题单');
        }
        //订单超重，添加操作日志
        if($q_code == 'SELL_RECORD_OVERWEIGHT') {
            load_model('oms/SellRecordModel')->add_action($sell_record_code, '设为问题单', '订单重量超过'.$data . 'kg,设为问题单');
        }
        return $ret;
	}
        
        function get_problem_content($q_code){
            $sql = "select content from base_question_label where question_label_code=:question_label_code ";
            return  $this->db->get_value($sql,array(':question_label_code'=>$q_code));
            
        }
	
}
