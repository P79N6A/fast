<?php

require_model('oms/SellProblemModel');

class BelowNormalPriceModel extends SellProblemModel{
	//低于最低售价
	function handler($sell_record_data){
            $detail_data = $sell_record_data['mx'];
            $goods_code_arr = array();
            foreach($detail_data as $val) {
                if($val['is_gift'] == 0) {
                    $goods_code_arr[] = $val['goods_code'];
                }
            }
            $goods_code_arr = array_unique($goods_code_arr);
            if(!empty($goods_code_arr)) {
                $sql_values = array();
                $goods_code_str = $this->arr_to_in_sql_value($goods_code_arr,'sku',$sql_values);
                $sql = "SELECT goods_code,min_price FROM base_goods WHERE goods_code IN ({$goods_code_str}) ";
                $goods_min_price = $this->db->get_all($sql, $sql_values);
                $goods_min_price = load_model('util/ViewUtilModel')->get_map_arr($goods_min_price, 'goods_code');
                $min_prince_goods_arr = array();
                foreach ($detail_data as $val) {
                    if($val['is_gift'] == 1) {
                        continue;
                    }
                    $key = $val['goods_code'];
                    if(isset($goods_min_price[$key]) && $goods_min_price[$key]['min_price'] > bcdiv($val['avg_money'], $val['num'], 3) && $goods_min_price[$key]['min_price'] > 0) {
                        $min_prince_goods_arr[] = $val['barcode'];
                        //return $this->format_ret(1, $val['goods_code']);
                    }
                }
                if(!empty($min_prince_goods_arr)) {
                    $goods_arr = array_unique($min_prince_goods_arr);
                    return $this->format_ret(1, $goods_arr);
                }
            }
	    return $this->format_ret(-1);
	}
}
