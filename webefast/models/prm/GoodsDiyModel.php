<?php
/**
 * 商品条码管理相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('prm');


class GoodsDiyModel extends TbModel {
	function get_table() {
		return 'goods_diy';
	}
	public function is_exists($p_sku, $p_goods_code) {
		$sql = "select * FROM goods_sku  where goods_code = :goods_code and sku = :sku ";
		$arr = array(':sku' => $p_sku,':goods_code' => $p_goods_code);
		$rs = $this->db->get_row($sql,$arr);
		return $rs;
	}
	/**
	 * 新增多条组合商品
	 */
	public function add_detail_action($p_sku,$p_goods_code, $ary_details) {
		//判断主单据的pid是否存在
		$record = $this->is_exists($p_sku, $p_goods_code);
		
		if (empty($record)) {
			return $this->format_ret(false,array(),'商品条码不存在!');
		}
		
		$this->begin_trans();
		try{
			foreach($ary_details as $ary_detail){
				if(!isset($ary_detail['num'])||$ary_detail['num']==0){
					continue;
				}
				$ary_detail['p_sku'] = $p_sku;
				$ary_detail['p_goods_code'] = $p_goods_code ;
				//todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
				
				//判断SKU是否已经存在
				$check = $this->is_detail_exists($p_sku,$p_goods_code,$ary_detail['sku']);
				if($check){
					//更新明细数据
					$ret = $this->update($ary_detail,array(
							'p_sku'=>$p_sku,'p_goods_code'=>$p_goods_code,'sku'=>$ary_detail['sku']
					));
				}else{
					//插入明细数据
					$ret = $this->insert($ary_detail);
				}
				if(1 != $ret['status']){
					return $ret;
				}
			}
			
			$this->commit();
			return $this->format_ret(1);
		}catch (Exception $e){
			$this->rollback();
			return $this->format_ret(-1,array(),'数据库错误:'.$e->getMessage());
		}
	}
	//修改条形码
	function update_save($b){
		$this->begin_trans();
		try{
			foreach($b as $id => $num){
				 
				$r = $this->db->update('goods_diy', array('num'=>$num), array('goods_diy_id'=>$id));
				if($r !== true){
					throw new Exception('保存失败');
				}
			}
			 
			$this->commit();
			return array('status'=>1, 'message'=>'更新成功');
		} catch(Exception $e){
			$this->rollback();
			return array('status'=>-1, 'message'=>$e->getMessage());
		}
	}
	/**
	 * 根据明细的sku和主单据id,判断明细是否已经存在
	 * @param   int     $pid    主单据ID
	 * @param   string  $sku    SKU编号
	 * @return  boolean 存在返回true
	 */
	private function is_detail_exists($p_sku,$p_goods_code, $sku){
		$ret = $this->get_row(array(
				'p_sku'=>$p_sku,
				'p_goods_code'=>$p_goods_code,
				'sku'=>$sku
		));
		if($ret['status'] == 1 && !empty($ret['data'])){
			return true;
		}else{
			return false;
		}
	}
	//删除条码相关
	function del_barcord($sku,$goods_code){
		//删除组合表
		$data = $this -> db -> create_mapper("goods_diy") -> delete( array('p_sku'=>$sku,'p_goods_code'=>$goods_code));
		//删除条码表
		$data = $this -> db -> create_mapper("goods_barcode") -> delete(array('sku'=>$sku,'goods_code'=>$goods_code));
		//删除sku表
		$data = $this -> db -> create_mapper("goods_sku") -> delete(array('sku'=>$sku,'goods_code'=>$goods_code));
		if ($data) {
			return $this -> format_ret("1", $data, 'delete_success');
		} else {
			return $this -> format_ret("-1", '', 'delete_error');
		}
	}
	//删除组合商品
	function del_diy($goods_diy_id,$p_goods_code,$p_sku){
		$used = $this->is_used_by_id($p_goods_code,$p_sku);
		if($used){
			return $this->format_ret(-1,array(),'已经在业务系统中使用，不能删除！');
		}
		//删除组合表
		$data = $this -> db -> create_mapper("goods_diy") -> delete( array('goods_diy_id'=>$goods_diy_id));
		if ($data) {
			return $this -> format_ret("1", $data, 'delete_success');
		} else {
			return $this -> format_ret("-1", '', 'delete_error');
		}
	}
	/**
	 * 根据id判断在业务系统是否使用
	 * @param int $id
	 * @return boolean 已使用返回true, 未使用返回false
	 */
	public function is_used_by_id($p_goods_code,$p_sku) {
		$num = $this->get_value('select count(*) from stm_goods_diy_record_detail where goods_code=:goods_code and sku =:sku', array(':goods_code' => $p_goods_code,':sku' => $p_sku));
		if(isset($num['data'])&&$num['data']>0){
			//已经在业务系统使用
			return true;
		}else{
			//尚未在业务系统使用
			return false;
		}
	}
	//组合商品
	function get_diy_list($arr) {
		$sql = "select * FROM goods_diy where 1 ";
		foreach($arr as $key1 => $value1){
			$key = substr($key1,1);
			$sql .= " and {$key} = {$key1} ";
		}
	
		$rs = $this->db->get_all($sql,$arr);
	
		return $rs;
	}
	
	/**
	 *
	 * 方法名                               api_goods_diy_update
	 *
	 * 功能描述                           添加或更新产品价格
	 *
	 * @author      BaiSon PHP R&D
	 * @date        2015-06-15
	 * @param       array $param
	 *              array(
	 *                  必填: 'p_barcode', 'barcode', 'num'
	 *                 )
	 * @return      json [string status, obj data, string message]
	 *              {"status":"1","message":"保存成功"," data":"10146"}
	 */
	public function api_goods_diy_update($param) {
	    //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
	    $key_required = array(
	            's' => array('p_barcode', 'barcode'),
	            'i' => array('num')
	    );
	    
	    $arr_required = array();
	    //验证必选字段是否为空并提取必选字段数据
	    $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
	    //必填项检测通过
	    if (TRUE == $ret_required['status']) {
	        $arr_deal = $arr_required;
	        //销毁无用数据
	        unset($arr_required);
	        unset($param);
	        $p_barcode = array('barcode' => $arr_deal['p_barcode']);
	        $barcode = array('barcode' => $arr_deal['barcode']);
	         
	        //根据barcode查询是否存在该数据
	        $p_goods = load_model('prm/GoodsBarcodeModel')->check_exists_by_condition($p_barcode, 'goods_barcode');
	        if (1 != $p_goods['status']) {
	            return $this -> format_ret("-10002", array('p_barcode' => $arr_deal['p_barcode']), "API_RETURN_MESSAGE_10002");
	        }
	        $p_sku = $p_goods['data']['sku'];
	        //根据barcode查询是否存在该数据
	        $goods = load_model('prm/GoodsBarcodeModel')->check_exists_by_condition($barcode, 'goods_barcode');
	        if (1 != $goods['status']) {
	            return $this -> format_ret("-10002", array('barcode' => $barcode), "API_RETURN_MESSAGE_10002");
	        }
	        $sku = $goods['data']['sku'];
	        //检查价格数据是否存在
	        $goods_diy = array('p_sku' => $p_sku, 'sku' => $sku);
	        $ret = $this->check_exists_by_condition($goods_diy);
	        if (1 == $ret['status']) {
	            $ret = $this->delete(array('goods_diy_id' => $ret['data']['goods_diy_id']));
	            if (1 != $ret['status']) {
	                return $ret;
	            }
	        }
	        $goods_diy['goods_code'] = $goods['data']['goods_code'];
	        $goods_diy['spec1_code'] = $goods['data']['spec1_code'];
	        $goods_diy['spec2_code'] = $goods['data']['spec2_code'];
	        $goods_diy['sku'] = $sku;
	        $goods_diy['p_goods_code'] = $p_goods['data']['goods_code'];
	        $goods_diy['p_sku'] = $p_sku;
	        $goods_diy['num'] = $arr_deal['num'];
	        unset($arr_deal);
	        $ret = $this->insert($goods_diy);
	        return $ret;
	    } else {
	        return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
	    }
	}
}
