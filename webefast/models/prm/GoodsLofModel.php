<?php
/**
 * 规格1相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('prm');

class GoodsLofModel extends TbModel {
	function get_table() {
		return 'goods_lof';
	}

	function get_by_id($id) {
		
		return  $this->get_row(array('brand_id'=>$id));
	}
	/**
	 * 新增多条库存调整单明细记录
	 * @param array $ary_detail 单据明细数组
	 * @return array 返回新增结果
	 */
	public function add_detail_action($pid, &$ary_details,$type='') {
                $moren = $this->is_exists('1','type');
		foreach($ary_details as &$ary_detail){
			
			if((!isset($ary_detail['num'])||$ary_detail['num']==0)&& $type != 'take_stock'){
				continue;
			}
			$arr['lof_no'] = isset($ary_detail['lof_no'])?$ary_detail['lof_no']:'';
			$arr['production_date'] = isset($ary_detail['production_date'])?$ary_detail['production_date']:$moren['data']['production_date'];
			$arr['sku'] = $ary_detail['sku'];
			if(isset($ary_detail['lof_no']) && $ary_detail['lof_no'] <> ''){
				//判断批次是否已经存在
				$check = $this->is_exists_lof($arr['sku'],$ary_detail['lof_no'],$arr['production_date']);
				
				if($check['status'] == '1'){
                                        if($check['data']['production_date']!=$arr['production_date']){
                                            $arr['production_date'] = $check['data']['production_date'];
                                        }
				}else{
					//插入数据
					if($this->pici_exist($arr['sku'],$arr['lof_no'],$arr['production_date'])){
							
					}else{
						$ret = $this->insert($arr);
					}
					
				}
				
			}else{
				//默认批次值
				
				$arr1['lof_no'] = isset($moren['data']['lof_no'])?$moren['data']['lof_no']:'';
				$arr1['production_date'] = isset($ary_detail['production_date'])?$ary_detail['production_date']:$moren['data']['production_date'];
				$arr1['sku'] = isset($arr['sku'])?$arr['sku']:'';
				if($this->pici_exist($arr1['sku'],$arr1['lof_no'],$arr1['production_date'])){
					
				}else{
					$ret = $this->insert($arr1);
				}
			}
		}
		return $this->format_ret(1);
			
	}
	/*
	 *检查此 批次是否唯一
	*/
	function pici_exist($sku,$lof_no,$production_date){
		$sql = "select id  from {$this->table} where sku = :sku and lof_no = :lof_no  limit 1 ";
		$arr = array(':sku' => $sku,':lof_no' => $lof_no);
		$data = $this->db->get_all($sql, $arr);
		if($data){
			return true;
		} else {
			return false;
		}
	
	}
         /**
	 * 获取系统默认批次号
	 * 
	 */
        function get_sys_lof(){
                $params = array('sku'=>'','type'=>1);
		$ret = $this->get_row($params);   
                return $ret;
        }




        /**
	 * 检查批次号是否存在
	 * @param $lof_no
	 */
	function lof_exist($lof_no){
		$sql = "select lof_no,production_date  from goods_lof where  lof_no = :lof_no limit 1 ";
		$arr = array(':lof_no' => $lof_no);
		$data = $this->db->get_all($sql, $arr);
		if($data){
			$ret['status'] = '1';
			$ret['data'] = $data;
			$ret['message'] = '';
			return $ret;
		} else {
			$ret['status'] = '0';
			$ret['data'] = 'false';
			$ret['message'] = '';
			return $ret;
		}
	
	}
	/*
	 * 添加新纪录
	 */
	function insert($brand) {
		$status = $this->valid($brand);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		if(isset($brand['brand_code'])){
			$ret = $this->is_exists($brand['brand_code']);
			
			if (!empty($ret['data'])) return $this->format_ret(BRAND_ERROR_UNIQUE_CODE);
		}
		return parent::insert($brand);
	}
	
	/*
	 * 修改纪录
	 */
	function update($brand, $brand_id) {
		$status = $this->valid($brand, true);
		if ($status < 1) {
			return $this->format_ret($status);
		}
		$ret = $this->get_row(array('brand_id'=>$brand_id));
		if(isset($brand['brand_code']) &&  $brand['brand_code'] != $ret['data']['brand_code']){
			$ret1 = $this->is_exists($brand['brand_code'], 'brand_code');
			if (!empty($ret1['data'])) return $this->format_ret(BRAND_ERROR_UNIQUE_CODE);
		}
		
		$ret = parent::update($brand, array('brand_id'=>$brand_id));
		return $ret;
	}

	
	function is_exists($value, $field_name='lof_no') {
		$ret = parent::get_row(array($field_name=>$value));

		return $ret;
	}
        
        function is_exists_lof($sku,$lof_no,$production_date){
            	$ret = parent::get_row(array('sku'=>$sku,'lof_no'=>$lof_no));

		return $ret;
        }
            function get_lof_production_date($lof_no,$barcode){
        $sql = "SELECT production_date from goods_lof l
            INNER JOIN goods_sku  s ON l.sku=s.sku
            where l.lof_no=:lof_no AND s.barcode=:barcode ";
       return $this->db->get_value($sql,array(':lof_no'=>$lof_no,':barcode'=>$barcode));
    }
    
    /**
     * @todo 通过商品条形码和批次获取商品的基本信息
     */
    function get_field_by_barcode($lof_no, $barcode, $field) {
        $sql = "SELECT {$field} FROM goods_lof l
                        INNER JOIN goods_sku  s ON l.sku=s.sku
               WHERE l.lof_no=:lof_no AND s.barcode=:barcode ";
        return $this->db->get_row($sql, array(':lof_no' => $lof_no, ':barcode' => $barcode));
    }
}



