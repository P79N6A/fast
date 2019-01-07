<?php
/**
 * 采购入库单相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lang('stm');

class PurchaseAnalyseModel extends TbModel{
    function get_table(){
        return 'pur_purchaser_record';
    }
    
    /*
     * 根据条件查询数据
     */
    function get_by_page($filter){
        //$sql_join = "";
      
        $sql_main1 = "FROM {$this->table} rl  LEFT JOIN pur_purchaser_record_detail r2 on rl.record_code = r2.record_code WHERE 1";
        $sql_main2 = " FROM pur_return_record rl WHERE 1";
        $sql_values = array();
        // 供应商
        if (isset($filter['supplier_code']) && $filter['supplier_code'] != '') {
            $sql_main .= " AND (rl.supplier_code = :supplier_code )";
            $sql_values[':supplier_code'] = $filter['supplier_code'];

        }
        //业务日期
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $sql_main .= " AND (rl.record_time >= :record_time_start )";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql_main .= " AND (rl.record_time <= :record_time_end )";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        
        $select = ' sum(r2.money) as record_money,sum(r2.notice_num) as record_num,rl.supplier_code';
        $select2 = " sum(rl.money) as return_money,sum(rl.num) as return_num,rl.supplier_code";
        $sql_main .= " group by rl.supplier_code order by record_time desc";
        $data = $this->get_page_from_sql($filter, $sql_main1.$sql_main, $sql_values, $select, true);
        $data2 = $this->get_page_from_sql($filter, $sql_main2.$sql_main, $sql_values, $select2, true);
		$i = 0;
		$supplier_code_arr = array();
        foreach ($data['data'] as $key => $v1) {
        	$supplier_code_arr[$key] = $v1['supplier_code'];
        	$data['data'][$key]['supplier_code'] = $v1['supplier_code'];
        	$data['data'][$key]['record_money'] = sprintf("%.2f",$v1['record_money']);
        	$data['data'][$key]['record_num'] = $v1['record_num'];
        	$data['data'][$key]['return_money'] = '';
        	$data['data'][$key]['return_num'] = '';
        	$data['data'][$key]['supplier_name'] = oms_tb_val('base_supplier', 'supplier_name', array('supplier_code' => $v1['supplier_code']));
        	$i++;
        }
        foreach ($data2['data'] as $key2 => $v2) {
       		if (in_array($v2['supplier_code'], $supplier_code_arr)) {
       			$k = array_search($v2['supplier_code'],$supplier_code_arr);
        		$data['data'][$k]['return_money'] =$data['data'][$k]['return_money'] + sprintf("%.2f",$v2['return_money']);
        		$data['data'][$k]['return_num'] = $data['data'][$k]['return_num'] + $v2['return_num'];
        		$data2['filter']['record_count'] = $data2['filter']['record_count']-1;
       		} else {
       			$supplier_code_arr[$i] = $v2['supplier_code'];
       			$data['data'][$i]['supplier_code'] = $v2['supplier_code'];
       			$data['data'][$i]['record_money'] = '';
       			$data['data'][$i]['record_num'] = '';
       			$data['data'][$i]['return_money'] = sprintf("%.2f",$v2['return_money']);
       			$data['data'][$i]['return_num'] = $v2['return_num'];
       			$data['data'][$i]['supplier_name'] = oms_tb_val('base_supplier', 'supplier_name', array('supplier_code' => $v2['supplier_code']));
        		$i++;
       		}
        }
        $data['filter']['record_count'] = $data['filter']['record_count']+$data2['filter']['record_count'];
        $data['filter']['page_count'] = ceil($data['filter']['record_count']/$data['filter']['page_size']);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    function get_detail_by_page($filter){
    	if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
    		$filter[$filter['keyword_type']] = $filter['keyword'];
    	}
    	$sql_main1 = "FROM {$this->table} rl 
    				 LEFT JOIN pur_purchaser_record_detail r2 
    					on rl.record_code = r2.record_code 
    				 LEFT JOIN base_goods bg
    				    on bg.goods_code = r2.goods_code 
    				WHERE 1";
    	$sql_main2 = " FROM pur_return_record rl
	    			   LEFT JOIN pur_return_record_detail r2 
	    				on rl.record_code = r2.record_code 
	    		   	   LEFT JOIN base_goods bg
	    			    on bg.goods_code = r2.goods_code 
	    			   WHERE 1";
    	$sql_values = array();
    	// 供应商
    	if (isset($filter['supplier_code']) && $filter['supplier_code'] != '') {
    		$sql_main .= " AND (rl.supplier_code = :supplier_code )";
    		$sql_values[':supplier_code'] = $filter['supplier_code'];
    	}
    	//业务日期
    	if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
    		$sql_main .= " AND (rl.record_time >= :record_time_start )";
    		$sql_values[':record_time_start'] = $filter['record_time_start'];
    	}
    	if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
    		$sql_main .= " AND (rl.record_time <= :record_time_end )";
    		$sql_values[':record_time_end'] = $filter['record_time_end'];
    	}
    	if (isset($filter['goods_code']) && $filter['goods_code'] != ''){
    		$sql_main .= " AND (r2.goods_code = :goods_code )";
    		$sql_values[':goods_code'] = $filter['goods_code'];
    	}
    	
    	if (isset($filter['barcode']) && $filter['barcode'] != ''){
//    		$sql_main .= " AND (r2.sku = :barcode )";
//    		$sql_values[':barcode'] = $filter['barcode'];
     	     $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
                            if(empty($sku_arr)){
                                   $sql_main .= " AND 1=2 ";
                            }else{
                                 $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                                $sql_main .= " AND r2.sku in({$sku_str}) ";
                            }               
                
                
                
    	}
    	
    	$select = ' r2.goods_code,r2.spec1_code,r2.spec2_code,r2.sku,bg.goods_name,bg.brand_code,bg.category_code,bg.season_code,bg.year, sum(r2.money) as record_money,sum(r2.notice_num) as record_num,rl.supplier_code';
    	$select2 = " r2.goods_code,r2.spec1_code,r2.spec2_code,r2.sku,bg.goods_name,bg.brand_code,bg.category_code,bg.season_code,bg.year, sum(rl.money) as return_money,sum(rl.num) as return_num,rl.supplier_code";
    	$sql_main .= " group by rl.supplier_code,r2.sku order by rl.record_time desc";
    	$data = $this->get_page_from_sql($filter, $sql_main1.$sql_main, $sql_values, $select, true);
//     	var_dump($sql_main2.$sql_main);var_dump($sql_values);var_dump($select2);
    	$data2 = $this->get_page_from_sql($filter, $sql_main2.$sql_main, $sql_values, $select2, true);
    	
    	$i = 0;
    	$supplier_code_arr = array();
    	$sku_arr = array();

    	foreach ($data['data'] as $key => $v1) {
    		$supplier_code_arr[$key] = $v1['supplier_code'];
    		$sku_arr[$key] = $v1['sku'];
    		//$data['data'][$key]['supplier_code'] = $v1['supplier_code'];
    		$data['data'][$key]['record_money'] = sprintf("%.2f",$v1['record_money']);
    		$data['data'][$key]['record_num'] = $v1['record_num'];
    		$data['data'][$key]['return_money'] = '';
    		$data['data'][$key]['return_num'] = '';
    		$data['data'][$key]['supplier_name'] = oms_tb_val('base_supplier', 'supplier_name', array('supplier_code' => $v1['supplier_code']));
    		$i++;
    	}
    	
    	foreach ($data2['data'] as $key2 => $v2) {
    		if (in_array($v2['supplier_code'], $supplier_code_arr)) {
    			$k = array_search($v2['supplier_code'],$supplier_code_arr);
    			if (in_array($v2['sku'], $sku_arr) && array_search($v2['sku'], $sku_arr) == $k){
    				$data['data'][$k]['return_money'] = $data['data'][$k]['return_money'] + sprintf("%.2f",$v2['return_money']);
    				$data['data'][$k]['return_num'] = $data['data'][$k]['return_num'] + $v2['return_num'];
    				$data2['filter']['record_count'] = $data2['filter']['record_count']-1;
    			} else {
    				$this->get_return_elements($data['data'],$supplier_code_arr,$v2,$i);
    			}
    		} else {
    			$this->get_return_elements($data['data'],$supplier_code_arr,$v2,$i);
    		}
    	}
    	foreach ($data['data'] as $key => &$value){
    		$value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code'=> $value['spec1_code']));
    		$value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code'=> $value['spec2_code']));
    		$value['season_name'] = oms_tb_val('base_season', 'season_name', array('season_code'=> $value['season_code']));
    		$value['brand_name'] = oms_tb_val('base_brand', 'brand_name', array('brand_code'=> $value['brand_code']));
    		$value['category_name'] = oms_tb_val('base_category', 'category_name', array('category_code'=> $value['category_code']));
    		$value['year_name'] = oms_tb_val('base_year', 'year_name', array('year_code'=> $value['year']));
    	}
    	$data['filter']['record_count'] = $data['filter']['record_count']+$data2['filter']['record_count'];
    	$data['filter']['page_count'] = ceil($data['filter']['record_count']/$data['filter']['page_size']);
    	$ret_status = OP_SUCCESS;
    	$ret_data = $data;
    	return $this->format_ret($ret_status, $ret_data);
    }

    function get_return_elements(&$data,&$supplier_code_arr,&$v2,&$i){
    	$supplier_code_arr[$i] = $v2['supplier_code'];
    	$sku_arr[$i] = $v2['sku'];
    	$data[$i]['supplier_code'] = $v2['supplier_code'];
    	$data[$i]['goods_code'] = $v2['goods_code'];
    	$data[$i]['spec1_code'] = $v2['spec1_code'];
    	$data[$i]['spec2_code'] = $v2['spec2_code'];
    	$data[$i]['sku'] = $v2['sku'];
    	$data[$i]['goods_name'] = $v2['goods_name'];
    	$data[$i]['brand_code'] = $v2['brand_code'];
    	$data[$i]['category_code'] = $v2['category_code'];
    	$data[$i]['season_code'] = $v2['season_code'];
    	$data[$i]['year'] = $v2['year'];
    	$data[$i]['record_money'] = '';
    	$data[$i]['record_num'] = '';
    	$data[$i]['return_money'] = sprintf("%.2f",$v2['return_money']);
    	$data[$i]['return_num'] = $v2['return_num'];
    	$data[$i]['supplier_name'] = oms_tb_val('base_supplier', 'supplier_name', array('supplier_code' => $v2['supplier_code']));
    	$i++;
    }
}