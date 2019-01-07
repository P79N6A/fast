<?php
/**
 * 商品价格相关业务
 *
 * @author hunter
 *
 */
require_model('tb/TbModel');
require_lang('prm');

class GoodsPriceModel extends TbModel {
	function get_table() {
		return 'goods_price';
	}

	/*
	 * 添加纪录
	 */
	function insert($goods_price = array()) {
		$ret = parent::insert($goods_price);
		return $ret;
	}

	/**
	 * @param $value
	 * @param string $field_name
	 * @return array
	 */
	function is_exists($goods_code, $spec1_code, $spec2_code) {
		$ret = parent::get_row(array('goods_code'=>$goods_code, 'color_code'=>$spec1_code, 'size_code'=>$spec2_code));

		return $ret;
	}

	/**
	 * @param $get_type cost_price | sell_price | buy_price | trade_price | purchase_price,多个price以逗号分开
	 * @param $sku_map = array('sku'=>'goods_code') 指定当前SKU关联的货号，这样如果SKU级没价格，可以取商品级的
	 * @param $pm_goods_code 如果不取sku的价格那么指定 goods_code 就行
	 */	
	function get_goods_price($get_type,$sku_map,$pm_goods_code = null){
		$fld = $get_type;
		$get_type_arr = explode(',',$get_type);

		if (!empty($pm_goods_code)){
			$goods_code_arr = explode(',',$pm_goods_code);		
		}else{
			$goods_code_arr = array_unique($sku_map);
		}
		if (empty($goods_code_arr)){
			return array();
		}
		$goods_code_list = "'".join("','",$goods_code_arr)."'";
		$wh = "goods_code in ({$goods_code_list})";					
		
		$sql = "select goods_code,sku,{$fld} from goods_price where {$wh}";
		//echo $sql;die;
		$db_arr = ctx()->db->get_all($sql);
		$goods_price_arr = array();
		$sku_price_arr = array();
		foreach($db_arr as $sub_arr){
			if (empty($sub_arr['sku'])){
				$goods_price_arr[$sub_arr['goods_code']] = $sub_arr;
			}else{
				$sku_price_arr[$sub_arr['sku']] = $sub_arr;				
			}
		}
		$result = array();
		$c = count($get_type_arr);		
		if (!empty($sku_map)){
			foreach($sku_map as $sku=>$goods_code){
				$find_sku_price_row = !empty($sku_price_arr[$sku]) ? $sku_price_arr[$sku] : array();
				$find_goods_price_row = !empty($goods_price_arr[$goods_code]) ? $goods_price_arr[$goods_code] : array();		
				foreach($get_type_arr as $_type){
					if ($c == 1){
						$result[$sku] = (float)@$find_sku_price_row[$_type];
						$result[$sku] = empty($result[$sku]) ? 	@$find_goods_price_row[$_type] : $result[$sku];						
					}else{
						$result[$sku][$_type] = (float)@$find_sku_price_row[$_type];
						$result[$sku][$_type] = empty($result[$sku][$_type]) ? 	$find_goods_price_row[$_type] : $result[$sku][$_type];						
					}
				}
			}
		}else{
			foreach($goods_code_arr as $goods_code){
				$find_goods_price_row = !empty($goods_price_arr[$goods_code]) ? $goods_price_arr[$goods_code] : array();				
				foreach($get_type_arr as $_type){
					if ($c == 1){
						$result[$goods_code] = (float)@$find_goods_price_row[$_type];						
					}else{
						$result[$goods_code][$_type] = (float)@$find_goods_price_row[$_type];
					}
				}
			}			
		}

		return $this->format_ret(1,$result);
	}
	/**
	 *
	 * 方法名                               api_goods_price_update
	 *
	 * 功能描述                           添加或更新产品价格
	 *
	 * @author      BaiSon PHP R&D
	 * @date        2015-06-13
	 * @param       array $param
	 *              array(
	 *                  必填: 'goods_code', 'sell_price'
	 *                  选填: 'cost_price', 'purchase_price', 'trade_price'
	 *                 )
	 * @return      json [string status, obj data, string message]
	 *              {"status":"1","message":"保存成功"," data":"10146"}
	 */
	public function api_goods_price_update($param) {
	    //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
	    $key_required = array(
	            's' => array('goods_code'),
	            'i' => array('sell_price')
	    );
	    
	    //可选字段
	    $key_option = array(
	            'i' => array('cost_price', 'purchase_price', 'trade_price')
	    );
	    
	    $arr_required = array();
	    $arr_option = array();
	    //验证必选字段是否为空并提取必选字段数据
	    $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
	    
	    //必填项检测通过
	    if (TRUE == $ret_required['status']) {
	        $goods_code = $param['goods_code'];
	        
	        //根据产品code查询数据是否存在该数据
	        $goods = load_model('prm/GoodsModel')->get_by_goods_code($goods_code);
	        if (1 != $goods['status']) {
	            return $this -> format_ret("-10002", array('goods_code' => $goods_code), "API_RETURN_MESSAGE_10002");
	        }
	        
	        //提取可选字段中已赋值数据
	        $ret_option = valid_assign_array($param, $key_option, $arr_option);
	        
	        //合并必选和可选两项数据
	        $arr_deal = array_merge($arr_required, $arr_option);
	        
	        //销毁无用数据
	        unset($arr_required);
	        unset($arr_option);
	        unset($param);
	        
	        //检查价格数据是否存在
	        $arr_key = array_keys($arr_deal);
	        return $ret = $this->save_or_update('base_goods', $arr_deal, $arr_key);
	    } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
	}	
}


