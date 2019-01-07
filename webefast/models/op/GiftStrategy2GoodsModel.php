<?php
require_model('tb/TbModel');
require_lib('util/oms_util', true);
class GiftStrategy2GoodsModel extends TbModel{
    

    function  get_table(){
        return 'op_gift_strategy_goods';
    }

     function insert( $data){
     	return parent::insert($data);
    }
    function get_by_page($filter) {
    	$sql_main = "FROM {$this->table} rl WHERE 1";
    	 
    	$sql_values = array();
    	//策略代码
    	if (isset($filter['strategy_code']) && $filter['strategy_code'] != '') {
    		$sql_main .= " AND rl.strategy_code = :strategy_code ";
    		$sql_values[':strategy_code'] = $filter['strategy_code'];
    	}
    	//规则id
    	if (isset($filter['op_gift_strategy_detail_id']) && $filter['op_gift_strategy_detail_id'] != '') {
    		$sql_main .= " AND rl.op_gift_strategy_detail_id = :op_gift_strategy_detail_id ";
    		$sql_values[':op_gift_strategy_detail_id'] = $filter['op_gift_strategy_detail_id'];
    	}
    	//指定赠品范围
    	if (isset($filter['op_gift_strategy_range_id']) && $filter['op_gift_strategy_range_id'] != '') {
    		$sql_main .= " AND rl.op_gift_strategy_range_id = :op_gift_strategy_range_id ";
    		$sql_values[':op_gift_strategy_range_id'] = $filter['op_gift_strategy_range_id'];
    	}
    	if (isset($filter['is_gift']) && $filter['is_gift'] != '') {
    		$sql_main .= " AND rl.is_gift = :is_gift ";
    		$sql_values[':is_gift'] = $filter['is_gift'];
    	}
    	$select = 'rl.*';
    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
    	foreach ($data['data'] as $key => &$value) {
    		//套餐
    		if ($value['is_combo'] == 1){
    			$sku_info =  load_model('prm/GoodsComboBarcodeModel')->get_barcode_info_by_sku($value['sku']);
    		} else {
    			$key_arr = array('spec1_code','spec1_name','spec2_code','spec2_name','barcode','goods_name','goods_code');
    			$sku_info =  load_model('goods/SkuCModel')->get_sku_info($value['sku'],$key_arr);
    		}
    		if ($value['is_combo'] == 1) {
                    $data['data'][$key]['is_combo_html'] = '<img src=' . get_theme_url("images/ok.png") . '>';
                } else {
                    $data['data'][$key]['is_combo_html'] = '<img src=' . get_theme_url("images/no.gif") . '>';
                }
                if ($value['diy'] == 1) {
                    $data['data'][$key]['diy_html'] = '<img src=' . get_theme_url("images/ok.png") . '>';
                } else {
                    $data['data'][$key]['diy_html'] = '<img src=' . get_theme_url("images/no.gif") . '>';
                }
    		$value = array_merge($value,$sku_info);
    		$data['data'][$key]['spec'] = "规格1：".$value['spec1_name'].";"."规格2：".$value['spec2_name'];
    	}    
        //导出
        if ($filter['ctl_type'] == 'export' && $filter['ctl_export_conf'] == 'gift_strategy_rule_goods') {
            return $this->export_detail($data);
        }
    	$ret_status = OP_SUCCESS;
    	$ret_data = $data;
    	return $this->format_ret($ret_status, $ret_data);
    }
    
    //导出活动商品
    function export_detail($ary_detail){
        //有权限的店铺
        $shop_code_arr = array();
        $shop_code_p = load_model('base/ShopModel')->get_purview_shop('shop_code');
        foreach ($shop_code_p as $value) {
            $shop_code_arr[] = $value['shop_code'];
        }
        //策略代码
        $strategy_code = oms_tb_val('op_gift_strategy_detail', 'strategy_code', array('op_gift_strategy_detail_id'=>$ary_detail['filter']['op_gift_strategy_detail_id']));
        $sql = " SELECT strategy_name,start_time,end_time,shop_code FROM op_gift_strategy WHERE strategy_code = :strategy_code ";
        //策略详情
        $strategy_detail = $this->db->get_row($sql, array(':strategy_code'=>$strategy_code));
        $shop_code = explode(',', $strategy_detail['shop_code']);
        $shop_name_arr = array();
        foreach ($shop_code as $value) {
            if (in_array($value, $shop_code_arr)) {
                $shop_name_arr[] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value));
            }                      
        }
        foreach ($ary_detail['data'] as &$value) {
            $value['strategy_name'] = $strategy_detail['strategy_name'];
            $value['shop_name'] = implode(',', $shop_name_arr);
            $value['start_time'] = date('Y-m-d H:i:s', $strategy_detail['start_time']);
            $value['end_time'] = date('Y-m-d H:i:s', $strategy_detail['end_time']);
            //组合
            $value['is_diy_c'] = $value['diy']== 1 ? '是' : '否';
            //套餐
            $value['is_combo_c'] = $value['is_combo'] == '0' ? '否' : '是';
        }
        return $this->format_ret('1', $ary_detail);
    }
            
    function get_by_detail_id($op_gift_strategy_goods_id,$range_id = "",$is_gift=""){
    	$wh = array('op_gift_strategy_detail_id'=>$op_gift_strategy_goods_id);
    	if ($is_gift!== ""){
    		$wh['is_gift'] = $is_gift;
    	}
    	if ($range_id!= ""){
    		$wh['op_gift_strategy_range_id'] = $range_id;
    	}
    	$ret = $this->get_all($wh);
    	$range_gift = array();
    	
    	foreach ($ret['data'] as $gift_row) {
    		//套餐
    		if ($gift_row['is_combo'] == 1){
    			$sku_info =  load_model('prm/GoodsComboBarcodeModel')->get_barcode_info_by_sku($gift_row['sku']);
    		} else {
    			$key_arr = array('spec1_code','spec1_name','spec2_code','spec2_name','barcode','goods_name','goods_code');
    			$sku_info =  load_model('goods/SkuCModel')->get_sku_info($gift_row['sku'],$key_arr);
    		}
    		$gift_row = array_merge($gift_row,$sku_info);
    		$gift_row['spec'] = "规格1：".$gift_row['spec1_name'].";"."规格2：".$gift_row['spec2_name'];
    		if ($is_gift == 1){
    			$range_gift[$gift_row['op_gift_strategy_range_id']][] = $gift_row;
    		} else {
    			$range_gift[] = $gift_row;
    		}
    		
    	}
    	return $range_gift;
    	
    }
    function check_exist_gift($op_gift_strategy_detail_id){
    	$sql = "select count(op_gift_strategy_goods_id) from $this->table where op_gift_strategy_detail_id = $op_gift_strategy_detail_id and is_gift=1";
    	$count = $this->db->getOne($sql);
    	if ($count > 0){
    		return $this->format_ret(-1,'');
    	} 
    	return $this->format_ret(1,'');
    }
    function update($data, $op_gift_strategy_goods_id) {
    	
    	$ret = parent::update($data, array('op_gift_strategy_goods_id' => $op_gift_strategy_goods_id));
    	return $ret;
    }
    function add_goods($param){
        $sta = $this->gift_is_check($param['strategy_code']);
        if ($sta) {
            return $this->format_ret(-1, '', '该策略已审核，添加失败');
        }
        $goods_arr = array();
        $detail_id = &$param['detail_id'];
        $strategy_code = &$param['strategy_code'];
        $range_id = &$param['range_id'];
        $is_gift= &$param['is_gift'];
        foreach ($param['data'] as $val){          
            $goods = array(
                'strategy_code'=>$strategy_code,
                'op_gift_strategy_detail_id'=>$detail_id,
                'is_gift'=>$is_gift,
                'sku'=>$val['sku'],
            	'op_gift_strategy_range_id'=>$range_id,
                );
            $goods['num'] = isset($val['num'])?$val['num']:1;
            $goods['is_combo'] = isset($val['is_combo'])?$val['is_combo']:0;
            $goods['diy'] = isset($val['diy'])?$val['diy']:0;
            $sql_diy = "select count(1) from goods_diy where p_sku='".$val['sku']."'";
            $count_diy = $this->db->getOne($sql_diy);
            if($count_diy > 0){
                    $goods['diy'] = 1;
                }
            $goods_arr[] = $goods ;
        }
        $update_str = " num = VALUES(num) ";
        return  $this->insert_multi_duplicate($this->table, $goods_arr, $update_str);
 
    }
    
    public function is_exists($value, $field_name = 'record_code') {
    
    	$m = load_model('op/GiftStrategyDetailModel');
    	$ret = $m->get_row(array($field_name => $value));
    	return $ret;
    }
    
    public function  del_goods($fileld,$value){
    	$result = parent::delete(array($fileld=>$value));
    	return $result;
    }
    public function del_batch(array $data){
    $sta = $this->gift_is_check($data['strategy_code']);
        if ($sta) {
            return $this->format_ret(-1, '', '该策略已审核，一键清空失败');
        }
    	if (isset($data['op_gift_strategy_detail_id'])){
    		$del['op_gift_strategy_detail_id'] = $data['op_gift_strategy_detail_id'];
    	}
    	if (isset($data['is_gift'])){
    		$del['is_gift'] = $data['is_gift'];
    	}
    	if (isset($data['op_gift_strategy_range_id'])){
    		$del['op_gift_strategy_range_id'] = $data['op_gift_strategy_range_id'];
    	}
    	$result = parent::delete($del);
    	return $result;
    }
    //导入商品混合数据导入
    function import_rule_goods($file,$strategy_code,$detail_id) {
        $sta = $this->gift_is_check($strategy_code);
        if ($sta) {
            return $this->format_ret(-1, '', '该策略已审核，导入失败');
        }
    	set_time_limit(0);      
    	try {
    		$csv_data = $this->read_rule_goods_csv($file);
    	}catch (Exception $ex) {
    		$this->rollback();
    		return $this->format_ret(-1, '', $ex->getMessage());
    	}
    	$bar = array();
    	foreach ($csv_data as $csv_row) {
    		$bar[] = $csv_row['barcode'];
    	}
    	//查询sku信息
        $sql_values = array();
    	$bar_str = $this->arr_to_in_sql_value($bar, 'barcode', $sql_values);
    	$sql = "select sku,barcode from goods_sku where barcode in($bar_str)";
    	$bar_ret = $this->db->get_all($sql,$sql_values);
    	$sku_info = array();
    	foreach ($bar_ret as $bar_row) {
    		$sku_info[$bar_row['barcode']] = $bar_row;
        }
    	//组装插入数据
    	$msg_arr = array();
    	$goods_data = array();
    	foreach ($csv_data as $key=>$csv_row) {
    		$line = $key+1;
    		//条码不存在
                //屏蔽套餐商品(暂时)
                $sql_barcode = "select count(1) from goods_combo_barcode where barcode='{$csv_row['barcode']}'";
                $barcode_flg = $this->db->getOne($sql_barcode);
    		if (!isset($sku_info[$csv_row['barcode']]) && $barcode_flg == 0){
    			$msg_arr[] = "第".$line."行 条码：".$csv_row['barcode']."在系统不存在";
    			continue;
    		}
                $sql_diy = "select count(1) from goods_diy where p_sku='".$sku_info[$csv_row['barcode']]['sku']."'";
                $count_diy = $this->db->getOne($sql_diy);
                if($count_diy>0){
                    $goods_data[] = array(
    				'sku' => $sku_info[$csv_row['barcode']]['sku'],
    				'num' => empty($csv_row['sl'])?1:$csv_row['sl'],
    				'is_gift' =>0,
    				'op_gift_strategy_detail_id' =>$detail_id,
    				'strategy_code' =>$strategy_code,
    				'op_gift_strategy_range_id' =>0,
                                'is_combo' =>0,
                                'diy' =>1
    				);
                }
                if($barcode_flg>0){
                    $sql_combo_sku = "select sku from goods_combo_barcode where barcode='{$csv_row['barcode']}'";
                    $goods_data[] = array(                    
    				'sku' => $this->db->get_value($sql_combo_sku),
    				'num' => empty($csv_row['sl'])?1:$csv_row['sl'],
    				'is_gift' =>0,
    				'op_gift_strategy_detail_id' =>$detail_id,
    				'strategy_code' =>$strategy_code,
    				'op_gift_strategy_range_id' =>0,
                                'is_combo' =>1,
                                'diy' =>0
    				);            
                }
                if($count_diy == 0 && $barcode_flg == 0){
                    $goods_data[] = array(
    				'sku' => $sku_info[$csv_row['barcode']]['sku'],
    				'num' => empty($csv_row['sl'])?1:$csv_row['sl'],
    				'is_gift' =>0,
    				'op_gift_strategy_detail_id' =>$detail_id,
    				'strategy_code' =>$strategy_code,
    				'op_gift_strategy_range_id' =>0,
                                'is_combo' =>0,
                                'diy' =>0
    				);
                }
    	}
        $update_str = " num = VALUES(num) ";
        $this->insert_multi_duplicate($this->table, $goods_data, $update_str);
    	if (!empty($msg_arr)) {

    		$file_name = $this->create_import_fail_files($msg_arr, 'gift_strategy_rule_goods_import_fail');    
//    		$msg = "部分导入失败，失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
                $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                $msg = "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";

    		return $this->format_ret(-1,'',$msg);
    	}
    	return $this->format_ret(1,'');
    	
    }
    //读取活动商品数据
    function read_rule_goods_csv($file) {
    
    	require_lib('csv_util');
    	$exec = new execl_csv();
    	$this->set_property_set_arr();
    	$key_arr = array(
    			'barcode','sl'
    	);        
    	$csv_data = $exec->read_csv($file, 1, $key_arr);
    	return $csv_data;
    }
    function create_import_fail_files($msg_arr, $name) {
    	$fail_top = array('错误信息');
    	$file_str = implode(",", $fail_top) . "\n";
    	foreach ($msg_arr as $key => $val) {
    		$file_str .= $val . "\r\n";
    	}
    	$filename = md5($name . time());
    	$file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
    	file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
    	//var_dump($file_str);die;
    	return $filename;
    }
    
    //校验赠品策略是否审核
    function gift_is_check ($strategy_code) {
        $is_check = oms_tb_val('op_gift_strategy', 'is_check', array('strategy_code'=>$strategy_code));
        if ($is_check) {
            return true;
        }else{
            return false;
        }
    }
    
    //删除活动商品
    function delete_gift_goods ($gift_goods_id,$gift_detail_id,$strategy_code,$barcode) {
        $sta = $this->gift_is_check($strategy_code);
        if ($sta) {
            return $this->format_ret(-1, '', '该策略已审核，删除失败');
        }
        $ret = $this->delete_exp('op_gift_strategy_goods', array('op_gift_strategy_goods_id'=>$gift_goods_id));
        //规则名称
        $strategy_name = oms_tb_val('op_gift_strategy_detail', 'name', array('op_gift_strategy_detail_id'=>$gift_detail_id));
        if ($ret) {
            $data = array(
            'strategy_code' => $strategy_code,
            'action_name' => '商品删除',
            'action_desc' => "从赠送规则:{$strategy_name}中,删除条码为{$barcode}的商品"
            );
            load_model('op/GiftStrategyLogModel')->insert($data);
            return $this->format_ret(1, '', '删除成功');
        }else{
            return $this->format_ret(-1, '', '删除失败');
        }
    }
}
