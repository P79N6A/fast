<?php
require_model('tb/TbModel'); 
require_lib('util/oms_util', true);
require_lib('comm_util', true);

class ActivityGoodsModel extends TbModel {
    
    protected $table = 'op_api_activity_goods';
    
    
    function get_by_page($filter) {
        $sql_join = "";
        
        $sql_main = " FROM {$this->table} rl LEFT JOIN base_shop r2 ON rl.shop_code = r2.shop_code LEFT JOIN goods_sku r3 ON rl.sku = r3.sku where 1";
        $sql_values = array();
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
                     $arr = explode(',', $filter['shop_code']);
        $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code in (".$str.")";
            
        }
        
         $select = ' rl.*,r2.shop_name,r3.goods_code,r3.spec1_name,r3.spec2_name ';
         $sql_main .= " ORDER BY id DESC ";
         $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
         foreach ($data['data'] as $key => &$value) {
             $value['goods_barcode'] = $value['barcode'];
             $value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $value['goods_code']));
             $value['spec'] = "{$value['spec1_name']},{$value['spec2_name']}";
         }
         return $this->format_ret(1, $data);
    }
    
    function do_delete($id){
        $sql_check = "select count(1) from {$this->table} where id = {$id}";
        if($this->db->getOne($sql_check) > 0){
            $sql_del = "delete from {$this->table} where id = {$id}";
            $this->db->query($sql_del);
            return $this->format_ret(1,'','删除成功');
        }
        else{
            return $this->format_ret(-1,'','活动商品不存在');
        }
    }
    public function edit_detail_action($id, $ary_details) {
        $this->begin_trans();
            foreach ($ary_details as $ary_detail) {
                $ary_detail['id'] = $id;
                //更新明细数据
                $ret = $this->update($ary_detail, array('id' => $id, 'sku' => $ary_detail['sku']));
            }
            //回写数量和金额
            $this->mainWriteBack($id);
            $this->commit();
            return $this->format_ret(1);
        } 
        
    function import_rule_goods($file) {
    	set_time_limit(0);      
    		$csv_data = $this->read_rule_goods_csv($file);
    	$bar = array();
        //print_r($csv_data);
    	foreach ($csv_data as $csv_row) {
    		$bar[] = $csv_row['barcode'];
    	}
    	//查询sku信息
    	$bar_str = "'".implode("','", $bar)."'";
    	$sql = "select sku,barcode from goods_sku where barcode in($bar_str)";
    	$bar_ret = $this->db->get_all($sql);
        
        $sql_api = "select goods_barcode from api_goods_sku where goods_barcode in($bar_str)";
    	$bar_ret_api = $this->db->get_all($sql_api);
    	$sku_info = array();
    	foreach ($bar_ret as $bar_row) {
    		$sku_info[$bar_row['barcode']] = $bar_row;
        }
        $sku_info_api = array();
    	foreach ($bar_ret_api as $bar_row) {
    		$sku_info_api[$bar_row['goods_barcode']] = $bar_row;
        }
//    	//组装插入数据
    	$msg_arr = array();
    	$goods_data = array();
    	foreach ($csv_data as $key=>$csv_row) {
    		$line = $key+2;
    		//条码不存在
    		if (!isset($sku_info[$csv_row['barcode']])){
    			$msg_arr[] = "第".$line."行 条码：".$csv_row['barcode']."在系统不存在";
    			continue;
    		}
                if (!isset($sku_info_api[$csv_row['barcode']])){
    			$msg_arr[] = "第".$line."行 条码：".$csv_row['barcode']."在店铺不存在";
    			continue;
    		}
                $sql_shop = "select count(1) from base_shop where shop_code = '{$csv_row['shop_code']}'";
                if($this->db->getOne($sql_shop) == 0){
                        $msg_arr[] = "第".$line."行 条码：店铺不在系统中";
    			continue;
                }
                $sql_shop = "select count(1) FROM base_shop t LEFT JOIN base_shop_api r ON t.shop_code=r.shop_code where r.shop_code = '{$csv_row['shop_code']}' and is_active=1 AND shop_type = 0 AND r.tb_shop_type = 'B'";
                if($this->db->getOne($sql_shop) == 0){
                        $msg_arr[] = "第".$line."行 条码：店铺不是天猫店铺";
    			continue;
                }
                if($csv_row['inv_num'] < 0 || $csv_row['sale_price'] < 0 ){
                        $msg_arr[] = "第".$line."行 条码：数量和金额不能为负";
    			continue;
                }
                    $goods_data[] = array(
    				'shop_code' => $csv_row['shop_code'],
    				'barcode' => $sku_info[$csv_row['barcode']]['barcode'],
    				'sku' => $sku_info[$csv_row['barcode']]['sku'],
    				'inv_num' => $csv_row['inv_num'],
    				'sale_price' => $csv_row['sale_price']
    				);
    	}
        $update_str = " inv_num = VALUES(inv_num),sale_price = VALUES(sale_price) ";
        $this->insert_multi_duplicate($this->table, $goods_data, $update_str);
    	if (!empty($msg_arr)) {

    		$file_name = $this->create_import_fail_files($msg_arr, 'op_activity_goods_rule_goods_import_fail');    
//    		$msg = "部分导入失败，失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
                $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                $msg = "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";

    		return $this->format_ret(-1,'',$msg);
    	}
    	return $this->format_ret(1,'');
    	
    }
    function read_rule_goods_csv($file) {
    
    	require_lib('csv_util');
    	$exec = new execl_csv();
    	$this->set_property_set_arr();
    	$key_arr = array(
    			'shop_code','barcode','inv_num','sale_price'
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
    function shop_select(){
        $sql="select rr.shop_name,rl.shop_code from op_api_activity_goods rl inner join base_shop rr on rl.shop_code=rr.shop_code group by shop_code";
        $data = $this->db->get_all($sql);
        foreach($data as $val){
           $ret['name'][] = $val['shop_name'];
           $ret['code'][] = $val['shop_code'];
        }
        return $ret;
    }
    function start_efficacy($shop_code){

 
            $task = array(
                'shop_code_list'=>  json_encode($shop_code),
                'create_time'=>time(),
                'status'=> 0,
                );

        $this->insert_exp('op_api_activity_check_task', $task);
        
        
     //   $ret = load_model('op/activity/OpApiActivityCheckTaskModel')->check_task($shop_code);
        return $this->format_ret(1);
    }
    function get_inv($shop_code){
        $sql = "select sku,inv_num from {$this->table} where shop_code = :shop_code";
        $data = $this->db->get_All($sql,array(':shop_code'=>$shop_code));
        return $data;
    }
    function import_rule_goods_inv($file,$shop) {
    	set_time_limit(0);      
    		$csv_data = $this->read_rule_goods_csv_inv($file);
//                print_r($csv_data);
//                return;
    	$bar = array();
        //print_r($csv_data);
    	foreach ($csv_data as $csv_row) {
    		$bar[] = $csv_row['barcode'];
    	}
    	//查询sku信息
    	$bar_str = "'".implode("','", $bar)."'";
    /*	$sql = "select sku,barcode from goods_sku where barcode in($bar_str)";
    	$bar_ret = $this->db->get_all($sql);*/
        
        $sql_api = "select goods_barcode from api_goods_sku where goods_barcode in($bar_str) and shop_code=:shop_code";
    	$bar_ret_api = $this->db->get_all($sql_api,array(':shop_code'=>$shop));
    	/*$sku_info = array();
    	foreach ($bar_ret as $bar_row) {
    		$sku_info[$bar_row['barcode']] = $bar_row;
        }*/
        $sku_info_api = array();
    	foreach ($bar_ret_api as $bar_row) {
    		$sku_info_api[$bar_row['goods_barcode']] = $bar_row;
        }
//    	//组装插入数据
    	$msg_arr = array();
    	$goods_data = array();
    	foreach ($csv_data as $key=>$csv_row) {
    		$line = $key+1;
    		//条码不存在
    		/*if (!isset($sku_info[$csv_row['barcode']])){
    			$msg_arr[] = "第".$line."行 条码：".$csv_row['barcode']."在系统不存在";
    			continue;
    		}*/
                if (!isset($sku_info_api[$csv_row['barcode']])){
    			$msg_arr[] = "第".$line."行 条码：".$csv_row['barcode']."在店铺不存在";
    			continue;
    		}
                if($csv_row['inv_num'] < 0 || $csv_row['sale_price'] < 0 ){
                        $msg_arr[] = "第".$line."行 条码：数量和金额不能为负";
    			continue;
                }
//                $sql_inv = "select goods_from_id,sku_id from api_goods_sku where goods_barcode = '{$sku_info[$csv_row['barcode']]['barcode']}' and shop_code='{$shop}'";
//                $data_inv = $this->db->get_All($sql_inv);
//                print_r($data_inv);
                    $goods_data[] = array(
    				'barcode' => $csv_row['barcode'],
    				'num' => $csv_row['inv_num'],
//                                'goods_from_id' => $data_inv['goods_from_id'],
//                                'sku_id' => $data_inv['sku_id']
    				);
    	}
    	if (!empty($msg_arr)) {

    		$file_name = $this->create_import_fail_files($msg_arr, 'op_activity_goods_rule_goods_inv_update_fail');    
//    		$msg = "部分导入失败，失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
                $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                $msg = "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";

    		return $this->format_ret(-1,'',$msg);
    	}
        
    	return $this->format_ret(1,'',$goods_data);
    	
    }
    function read_rule_goods_csv_inv($file) {
    
    	require_lib('csv_util');
    	$exec = new execl_csv();
    	$this->set_property_set_arr();
    	$key_arr = array(
    			'barcode','inv_num'
    	);        
    	$csv_data = $exec->read_csv($file, 1, $key_arr);
    	return $csv_data;
    }
    function get_data_inv($data,$shop){
        foreach($data as &$val){
                $sql_inv = "select goods_from_id,sku_id,source from api_goods_sku where goods_barcode = '{$val['barcode']}' and shop_code='{$shop}'";
                $data_inv = $this->db->get_all($sql_inv);
                $val['goods_from_id'] = $data_inv[0]['goods_from_id'];
                $val['sku_id'] = $data_inv[0]['sku_id'];
                $val['source'] = $data_inv[0]['source'];
                $val['shop_code'] = $shop;
        }
        return $data;
    }
}

?>