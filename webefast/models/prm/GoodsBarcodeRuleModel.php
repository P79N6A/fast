<?php
/**
 * 规格1相关业务
 *
 * @author dfr
 *
 */
require_lib ( 'comm_util', true );
require_model('tb/TbModel');
class GoodsBarcodeRuleModel extends TbModel {
        public $serial_num  = 0;
    
    	function get_table() {
		return 'goods_barcode_rule';
	}
        
        public function create_barcode($filter)
        {
            /** 
             * base_goods（商品基础表）, goods_sku（商品sku表）, goods_barcode（商品条形码表）
             * 联表查询goods_code（商品代码），spec1_code（规格1代码），spec2_code（规格2代码）
             * sku（sku码），barcode（商品条形码），分批查询，一次查1000条 start
             */
            $filter['page_size'] = 1000;//1次
            $status = 1; // 查询状态
            $num    = 0; // 转变条数
            $sql_main = ' FROM base_goods g'
                      . ' INNER JOIN goods_sku s'
                      . ' ON g.goods_code = s.goods_code'
                      . ' WHERE 1 ';
            $select = ' g.goods_code, s.spec1_code, s.spec2_code, s.sku, s.barcode ';
            if( $filter['is_cover'] == 0 ){
                $filter['page'] = 1;
                $sql_main .= " AND (s.barcode is NULL OR  s.barcode = '') ";
            }
            $ret =  $this->get_page_from_sql($filter, $sql_main, array(), $select);
            /** end */
            /** 获取条形码生成规则 start */
            $ret_rule = $this->get_row(array('rule_code' => $filter['rule_code']));
            /** end */
            /** 获取所有订单和退单存在的条形码 start*/
//            $barcodes1 = $this->db
//                ->get_all('SELECT DISTINCT barcode FROM oms_sell_record_detail');
//            $barcodes2 = $this->db
//                ->get_all('SELECT DISTINCT barcode FROM oms_sell_return_detail');
            $exist_barcode_arr = array();
//            foreach ($barcodes1 as $barcode){
//            	$exist_barcode_arr[] = $barcode['barcode'];
//            }
//            foreach ($barcodes2 as $key => $bar2){
//            	if (!in_array($bar2['barcode'], $exist_barcode_arr)){
//            		$exist_barcode_arr[] = $bar2['barcode'];
//            	}
//            }
            /** $exist_barcode_arr即存在的barcode end*/
            if( !empty($ret['data']) ){
                $barcode_arr = array();
                $cnt = 0;
                foreach($ret['data'] as $key => $val){
                    $cnt++;
                    /** 正在进行的订单的条形码不予以覆盖 */
//                    if ($filter['is_cover'] == 1 && in_array($val['barcode'], $exist_barcode_arr)){
                    if ($filter['is_cover'] == 1 && in_array($val['barcode'], $exist_barcode_arr)){
                        unset($ret['data'][$key]);
                        continue;
                    }
                    /** 如果规则是根据商品编码，查询是否是单规格 */
                    if ( $ret_rule['data']['rule_code'] === '001'){
                        $q = $this->db->get_row('SELECT COUNT(*) as cnt FROM goods_sku WHERE goods_code = "'.$val['goods_code'].'"');
                        if ($q['cnt'] > 1) {
                            unset($ret['data'][$key]);
                            continue;
                        }
                    }
                    /** 根据条形码生成规则生成条形码 */
                    $val['barcode'] = $this->get_barocde($ret_rule['data'], $val);
                    /** 商品的sku为空的话，立即生成 */
                    if( empty($val['sku']) ){
                        $val['sku'] = $val['goods_code'].$val['spec1_code'].$val['spec2_code'];
                    }
                    $barcode_arr[] = $val;
                }
                $num = count($ret['data']);
                $this->insert_multi_duplicate('goods_sku', $barcode_arr, 'barcode = VALUES(barcode)');
           //     if($filter['is_cover'] == 0){
              //      $this->insert_multi_exp('goods_barcode', $barcode_arr, TRUE);
             //   }else{
                    $this->insert_multi_duplicate('goods_barcode', $barcode_arr, $barcode_arr);
              //  }
                if($cnt < $filter['page_size']){                    
                    $status = -1;
                }
            }else{
                $status = -1;
            }
            if($this->serial_num>0){//更新流水号
                $this->update(array('serial_num'=>$this->serial_num), " rule_code = '{$filter['rule_code']}'");
            }
            return $this->format_ret($status,$num);
        }
        
        /**
         * 根据规则生成条形码
         * @param type $rule_data
         * @param type $data
         * @return type
         */
        public function get_barocde($rule_data, $data)
        {
            $barcode = $rule_data['barcode_prefix'];
            $rule_type = array(
                1 => 'goods_code',
                2 => 'spec1_code',
                3 => 'spec2_code'
            );
            /** 根据project权重拼接字符串 */
            if( $rule_data['project1'] > 0 ){
                // 商品代码
                $barcode .= $data[$rule_type[$rule_data['project1']]].$rule_data['split1'];
            }
            if( $rule_data['project2'] > 0 ){
                // 规格1
                $barcode .= $data[$rule_type[$rule_data['project2']]].$rule_data['split2'];
            }
            if( $rule_data['project3'] > 0 ){
                // 规格2
                $barcode .= $data[$rule_type[$rule_data['project3']]].$rule_data['split3'];
            }
            // 商品条形码前缀
            $barcode .= $rule_data['barcode_suffix'];
            // 连接序列号
            if( $rule_data['serial_num_length'] > 0 ){
                $this->serial_num = $this->serial_num > 0 ? $this->serial_num : (int)$rule_data['serial_num'];
                $this->serial_num++;
                $barcode .= sprintf("%0{$rule_data['serial_num_length']}d", $this->serial_num);
            }
            return $barcode;
        }
        
        function get_list(){
            
           return $this->get_all(array('status'=>1),'rule_code,rule_name');
          
        }
        
        
    
}
?>
