<?php
/**
 * 库存流水表模型
 */
require_model('tb/TbModel');
require_lang('prm');

class InvRecordModel extends TbModel {
    public $record_type = array(
        8 => '库存调整单',
        14 => '电商订单',
        15 => '电商退单',
    );

    function get_table() {
        return 'goods_inv_record';
    }

    function get_by_page($filter) {
        $sql_values = array();
		$sql_join = "";
                $select ='';
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sku = $this->get_sku_by_barcode($filter['barcode']);
        }
        $sql_main = "FROM {$this->table} r1 LEFT JOIN base_goods r2 on r1.goods_code = r2.goods_code $sql_join WHERE 1";
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $fun = isset($filter['is_entity']) && $filter['is_entity'] == 1 ? 'get_entity_store' : 'get_purview_store';
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code, $fun, 2);
        //计划日期
        if (isset($filter['time_start']) && $filter['time_start'] != '') {
        	$sql_main .= " AND (r1.record_time >= :time_start )";
        	$sql_values[':time_start'] = $filter['time_start'];
        }
        if (isset($filter['time_end']) && $filter['time_end'] != '') {
        	$sql_main .= " AND (r1.record_time <= :time_end )";
//                $date = new DateTime($filter['time_end']);
//                $date->add(new DateInterval('P1D'));
//        	$sql_values[':time_end'] = $date->format('Y-m-d H:i:s');
                $sql_values[':time_end'] = $filter['time_end'];
        }
        if(isset($filter['inv_type']) && $filter['inv_type'] != ''){
                $inv = explode(',',$filter['inv_type']);
                $inv_list = $this->arr_to_in_sql_value($inv, 'remark', $sql_values);
                $sql_main .= " AND r1.remark in ({$inv_list})";
        }
		//商品编号
		if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
			$sql_main .= " AND (r1.goods_code LIKE :goods_code )";
			$sql_values[':goods_code'] = $filter['goods_code'].'%';
		}
		//商品条码
		if (isset($filter['barcode']) && $filter['barcode'] != '') {
			$sql_main .= " AND (r1.sku = :sku";
            $sql_values[':sku'] = $filter['barcode'];
            if($sku != ''){
                $sql_main .= " OR r1.sku = :barcode) ";
                $sql_values[':barcode'] = $sku;
            }else{
                $sql_main .= ") ";
            }
		}
        //仓库代码
		if (isset($filter['store_code']) && $filter['store_code'] != '') {
			$sql_main .= " AND (r1.store_code in (:store_code) )";
			$sql_values[':store_code'] = explode(',',$filter['store_code']);
		}
		//单据类型
		if (isset($filter['relation_code']) && $filter['relation_code'] != '') {
			$sql_main .= " AND (r1.relation_code in (:relation_code) )";
			$sql_values[':relation_code'] = $filter['relation_code'];
		}
        //单据类型
        if (isset($filter['type']) && $filter['type'] != '') {
			$sql_main .= " AND (r1.relation_type in (:type) )";
			$sql_values[':type'] = explode(',',$filter['type']);
		}
// 		$sql_main .= " group by sku,relation_code,relation_type,record_time order by inv_record_id desc " ;
		$sql_main .= " order by inv_record_id desc " ;
//         $select = 'r1.inv_record_id,r1.goods_code,r1.spec1_code,r1.spec2_code,r1.sku,r1.lof_no,r1.production_date,r1.store_code,
//             r1.occupy_type,sum(r1.stock_change_num) as stock_change_num,r1.stock_lof_num_before_change,r1.stock_num_before_change,r1.stock_num_after_change,
//              r1.stock_lof_num_after_change,sum(r1.lock_change_num) as lock_change_num, r1.lock_num_before_change, r1.lock_num_after_change, r1.lock_lof_num_before_change,
//         r1.lock_lof_num_after_change, r1.frozen_change_num, r1.record_time, r1.object_code, r1.relation_code, r1.relation_type, r1.remark,
//         r2.goods_name,r2.weight';
		$select .= "r1.*,r2.goods_name,r2.weight";
//
//         $sql_main .= "order by record_time desc " ;
//        $select = 'r1.*,r2.goods_name,r2.weight';
//        var_dump($filter, $sql_main,$sql_values, $select);
        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select,TRUE);
        filter_fk_name($data['data'], array('store_code|store'));
        // filter_fk_name($data['data'], array('spec1_code','spec2_code','store_code|store','sku|barcode'));
        $ret_status = OP_SUCCESS;
        $conf = isset($filter['is_entity']) && $filter['is_entity'] == 1 ? 'sys/lof_order_type_entity' : 'sys/lof_order_type';
        $lof_order_type = require_conf($conf);
        if (isset($data['data'])) {
            //print_r($data['data']);
            foreach ($data['data'] as $key => $value) {
                $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
                $data['data'][$key] = array_merge($value, $sku_info);

                if (isset($lof_order_type[$value['relation_type']])) {
                    $data['data'][$key]['relation_type'] = $lof_order_type[$value['relation_type']]['name'];
                } else {
                    $data['data'][$key]['relation_type'] = '';
                }
            }
        }

        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_lof_by_page($filter) {
        $sql_values = array();
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sku = $this->get_sku_by_barcode($filter['barcode']);
        }
        $sql_main = "FROM {$this->table} r1  LEFT JOIN base_goods r2 on r1.goods_code = r2.goods_code  WHERE 1";
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $fun = isset($filter['is_entity']) && $filter['is_entity'] == 1 ? 'get_entity_store' : 'get_purview_store';
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code, $fun, 2);

        //计划日期
        if (isset($filter['time_start']) && $filter['time_start'] != '') {
                $sql_main .= " AND (r1.record_time >= :time_start )";
        	$sql_values[':time_start'] = $filter['time_start'];
        }
        if (isset($filter['time_end']) && $filter['time_end'] != '') {
        	$sql_main .= " AND (r1.record_time <= :time_end )";
        	$sql_values[':time_end'] = $filter['time_end'];
        }
        //库存类型
        if(isset($filter['inv_type']) && $filter['inv_type'] != ''){
            $sql_main .= " AND r1.remark = :inv_type";
            $sql_values[':inv_type'] = $filter['inv_type'];
        }
		//商品编号
		if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
			$sql_main .= " AND (r1.goods_code LIKE :goods_code )";
			$sql_values[':goods_code'] = $filter['goods_code'].'%';
		}
		//商品条码
		if (isset($filter['barcode']) && $filter['barcode'] != '') {
			$sql_main .= " AND (r1.sku = :sku";
            $sql_values[':sku'] = $filter['barcode'];
            if($sku != ''){
                $sql_main .= " OR r1.sku = :barcode) ";
                $sql_values[':barcode'] = $sku;
            }else{
                $sql_main .= ") ";
            }
		}
        //仓库代码
		if (isset($filter['store_code']) && $filter['store_code'] != '') {
			$sql_main .= " AND (r1.store_code in (:store_code) )";
			$sql_values[':store_code'] = explode(',',$filter['store_code']);
		}
		//单据类型
		if (isset($filter['relation_code']) && $filter['relation_code'] != '') {
			$sql_main .= " AND (r1.relation_code in (:relation_code) )";
			$sql_values[':relation_code'] = $filter['relation_code'];
		}
        //单据类型
        if (isset($filter['type']) && $filter['type'] != '') {
			$sql_main .= " AND (r1.relation_type in (:type) )";
			$sql_values[':type'] = explode(',',$filter['type']);
		}
		$sql_main .= " order by inv_record_id desc " ;
        $select = 'r1.*,r2.goods_name,r2.weight';

        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        filter_fk_name($data['data'], array('spec1_code','spec2_code','store_code|store','sku|barcode'));
        $ret_status = OP_SUCCESS;
        $conf = isset($filter['is_entity']) && $filter['is_entity'] == 1 ? 'sys/lof_order_type_entity' : 'sys/lof_order_type';
        $lof_order_type = require_conf($conf);
        if(isset($data['data'])){
        	//print_r($data['data']);
	        foreach ($data['data'] as $key => $value){
                        $key_arr = array('spec1_code','spec1_name','spec2_code','spec2_name','barcode');
                         $sku_info =  load_model('goods/SkuCModel')->get_sku_info($value['sku'],$key_arr);
                        $data['data'][$key] = array_merge($value,$sku_info);
	        	if(isset($lof_order_type[$value['relation_type']]) ){
	        		$data['data'][$key]['relation_type'] = $lof_order_type[$value['relation_type']]['name'];
	        	}else{
	        		$data['data'][$key]['relation_type'] = '';
	        	}
	        }
        }

		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
    }

    //锁定库存明细查询
    function inv_lock_detail($filter){
        $sql_values = array();
        $group_by = false;
        $sql = '';
        $words1 = "rl.order_type,rl.id,rl.pid,rl.p_detail_id,rl.order_code record_code,rl.goods_code,rl.sku,rl.store_code,rl.lof_no,rl.num,rl.production_date,rl.create_time,rl.lastchanged,'' AS sale_channel_code,'' AS shop_code, '' AS return_sale_channel,'' AS return_shop_code ";
        $words2 = "rl.record_type as order_type,rl.id,rl.pid,rl.p_detail_id,rl.record_code,rl.goods_code,rl.sku,rl.store_code,rl.lof_no,rl.num,rl.production_date,rl.create_time,rl.lastchanged,r2.sale_channel_code,r2.shop_code,r3.sale_channel_code AS return_sale_channel,r3.shop_code AS return_shop_code";
        
        //仓库编码
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_code_arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
            $sql .= " AND ( rl.store_code IN ({$str}) )";
        } else {
            $sql .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code');
        }
        
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql .= " AND (rl.goods_code = :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }
        
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if(empty($sku_arr)){
                $sql .= " AND 1=2 ";
            }else{
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql .= " AND rl.sku in({$sku_str}) ";
            }
        }
        //批次
        if (isset($filter['lof_no']) && $filter['lof_no'] != '') {
            $sql .= " AND (rl.lof_no in (:lof_no) )";
            $sql_values[':lof_no'] = explode(',', $filter['lof_no']);
        }
        if (isset($filter['mode']) && $filter['mode']=='lof_mode'){
            $select = 'r1.*';
        }else{
            $sql_group .= " group by order_type,record_code,r1.store_code,sku";
            $group_by = true;
            $select = 'r1.order_type,r1.pid,r1.record_code,r1.goods_code,sku,r1.store_code,lof_no,sum(r1.num) num,r1.create_time,r1.lastchanged,r1.sale_channel_code,r1.shop_code,r1.return_sale_channel,r1.return_shop_code';
        }
        //单据类型
        $where1 = "";
        $where2 = "";
        $where_main = "";
        if (isset($filter['order_type']) && $filter['order_type']!=''){
            $arr = explode(',', $filter['order_type']);
            $str = $this->arr_to_in_sql_value($arr, 'order_type', $sql_values);
            $where_main .= "AND r1.order_type IN ({$str})";
        }
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
            $arr = explode(',',$filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $where1 .= " AND 1=2 ";
            $where2 .= " AND ( r2.sale_channel_code in ( {$str} ) OR r3.sale_channel_code in ( {$str} ) ) ";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
            $arr = explode(',',$filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $where1 .= " AND 1 = 2 ";
            $where2 .= " AND (r2.shop_code in ( {$str} ) OR r3.shop_code in ( {$str} ) ) ";
        }
        $sql_main = "FROM (
                SELECT {$words1} FROM b2b_lof_datail rl where occupy_type=1 {$sql}{$where1}
                union all 
                SELECT {$words2} FROM oms_sell_record_lof rl
                LEFT JOIN oms_sell_record r2 ON rl.record_code = r2.sell_record_code AND rl.record_type = 1 
                LEFT JOIN oms_sell_return r3 ON rl.record_code = r3.sell_return_code AND rl.record_type = 3  WHERE rl.occupy_type = 1 {$sql}{$where2} ) r1  WHERE 1 {$where_main} AND num <> 0 {$sql_group}";    

        $data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select, $group_by);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        $order_type_conf =require_conf("sys/lof_order_type");
        $oms_type = array('1'=>'oms','2'=>'oms_return','3'=>'oms_change');

        //获取销售平台数组 code=>name
        $sale_channel = load_model('base/ArchiveSearchModel')->get_archives_map('sale_channel');
        //获取店铺数组
        $shop_arr = load_model('base/ArchiveSearchModel')->get_archives_map('shop');
        //获取供应商数组
        $supplier_arr = load_model('base/SupplierModel')->get_supplier_arr();
        //获取分销商数组
        $distributor_arr = load_model('base/CustomModel')->get_custom_arr();
        //获取sys_user数组
        $user_arr = load_model('sys/UserModel')->sys_user_arr();
        //获取有权限的店铺
        $shop_data = load_model('base/ShopModel')->get_purview_shop_new();
        foreach ($shop_data as $value) {
            $purview_shop[$value['shop_code']] = $value['shop_name'];
        }
        //进销存单据信息处理
        $jxc_data_arr = array();
        //移仓单-is_add_person
        $sql_1 = "SELECT rl.order_code,r2.is_add_person FROM b2b_lof_datail rl LEFT JOIN stm_store_shift_record r2 ON rl.order_code = r2.record_code WHERE rl.occupy_type = 1 AND rl.order_type = 'shift_out' GROUP BY rl.order_code" ;
        $shif_out_data = $this->db->get_all($sql_1);
        foreach ($shif_out_data as $v) {
            $jxc_data_arr['shift_out'][$v['order_code']] = $v['is_add_person'];
        }
        //采购退货通知单-supplier_code
        $sql_2 = "SELECT rl.order_code,r2.supplier_code FROM b2b_lof_datail rl LEFT JOIN pur_return_notice_record r2 ON rl.order_code = r2.record_code WHERE rl.occupy_type = 1 AND rl.order_type = 'pur_return_notice' GROUP BY rl.order_code" ;
        $supplier_code_data = $this->db->get_all($sql_2);
        foreach ($supplier_code_data as $v) {
            $jxc_data_arr['pur_return_notice'][$v['order_code']] = $supplier_arr[$v['supplier_code']];
        }
        //批发销货通知单-distributor_code
        $sql_3 = "SELECT rl.order_code,r2.distributor_code FROM b2b_lof_datail rl LEFT JOIN wbm_notice_record r2 ON rl.order_code = r2.record_code WHERE rl.occupy_type = 1 AND rl.order_type = 'wbm_notice' GROUP BY rl.order_code" ;
        $distributor_code_data = $this->db->get_all($sql_3);
        foreach ($distributor_code_data as $v) {
            $jxc_data_arr['wbm_notice'][$v['order_code']] = $distributor_arr[$v['distributor_code']];
        }
        //锁定单-lock_person
        $sql_4 = "SELECT rl.order_code,r2.lock_person FROM b2b_lof_datail rl LEFT JOIN stm_stock_lock_record r2 ON rl.order_code = r2.record_code WHERE rl.occupy_type = 1 AND rl.order_type = 'stm_stock_lock' GROUP BY rl.order_code" ;
        $lock_person_data = $this->db->get_all($sql_4);
        foreach ($lock_person_data as $v) {
            $jxc_data_arr['stm_stock_lock'][$v['order_code']] = $user_arr[$v['lock_person']];
        }
        
        foreach($ret_data['data'] as $key => &$val){
            //订单信息组装
            if ($val['order_type'] == 1 || $val['order_type'] == 3) {
                $val['record_code_info'] = '平台：'.$sale_channel[$val['sale_channel_code']].$sale_channel[$val['return_sale_channel']].'<br>店铺：'.$shop_arr[$val['shop_code']].$shop_arr[$val['return_shop_code']];
            }
            if ($val['order_type'] == 'pur_return_notice') {
                $val['record_code_info'] = '供应商：'.$jxc_data_arr['pur_return_notice'][$val['record_code']];
            }
            if ($val['order_type'] == 'shift_out') {
               $val['record_code_info'] = '创建人：'.$jxc_data_arr['shift_out'][$val['record_code']];
            }
            if ($val['order_type'] == 'wbm_notice') {
                $val['record_code_info'] = '分销商：'.$jxc_data_arr['wbm_notice'][$val['record_code']];
            }
            if ($val['order_type'] == 'stm_stock_lock') {
                $val['record_code_info'] = '锁定操作者：'.$jxc_data_arr['stm_stock_lock'][$val['record_code']];
            }
            
            $val['barcode'] =  load_model('goods/SkuCModel')->get_barcode($val['sku']);
            if(isset($oms_type[$val['order_type']])){
                $val['order_type'] = $oms_type[$val['order_type']];
            }
            if(isset($order_type_conf[$val['order_type']])){
                $type = $order_type_conf[$val['order_type']];
                $order_type = $val['order_type'];
                $val['order_type'] = $type['name'];
                if(!empty($type['url'])){
                    if (($order_type == 'oms' || $order_type == 'oms_return' || $order_type == 'oms_change') && ($purview_shop[$val['shop_code']] != '' || $purview_shop[$val['return_shop_code']] != '' ) ){
                        $val['record_code'] ="<a onclick=javascript:openPage('order_detail','{$type['url']}{$val['record_code']}','单据明细')>{$val['record_code']}</a>";
                    } else if (($order_type == 'oms' || $order_type == 'oms_return' || $order_type == 'oms_change') && ($purview_shop[$val['shop_code']] == '' || $purview_shop[$val['return_shop_code']] == '' )) {
                        $val['record_code'] = "<span title='没有该店铺权限，不能查看详情！ ' >{$val['record_code']}</span>";
                    } else {
                        $val['record_code'] ="<a onclick=javascript:openPage('order_detail','{$type['url']}{$val['pid']}','单据明细')>{$val['record_code']}</a>";
                    }
                }
            }
            $val['create_time'] = ($val['create_time']>0)?date('Y-m-d H:i:s',$val['create_time']):$val['lastchanged'];
        }
	return $this->format_ret($ret_status, $ret_data);
    }
    
    //在途库存明细查询
    function inv_road_detail($filter){
        $group_by = false;
        $select = "r1.planned_record_id, r1.record_code, r1.planned_time, r1.in_time, r2.num, r2.finish_num, r3.supplier_name"
                . ", if((r2.num-r2.finish_num)<0,0,r2.num-r2.finish_num) as road_num, r2.sku, r4.barcode";
        $join = "INNER JOIN pur_planned_record_detail r2 ON r1.record_code=r2.record_code"
                . " LEFT JOIN base_supplier r3 ON r1.supplier_code=r3.supplier_code"
                . " INNER JOIN goods_sku r4 on r2.sku = r4.sku";
        $where = "r1.is_check=1 AND r1.is_finish<>1";
        $sql_values = array();
        //仓库编码
        if (isset($filter['store_code']) && $filter['store_code'] != '' && is_string($filter['store_code'])) {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
            $where .= " AND (r1.store_code IN({$store_code_str}))";
        }
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $where .= " AND (r2.goods_code = :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if(empty($sku_arr)){
                $where .= " AND 1=2 ";
            }else{
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $where .= " AND r2.sku in({$sku_str}) ";
            }
        }
        $sql_sub = "SELECT {$select} FROM pur_planned_record r1 {$join} WHERE {$where}";
        $select = '*';
        $sql_main = "FROM ({$sql_sub}) a  WHERE road_num>0";
        $data =  $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, $group_by);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        foreach ($ret_data['data'] as &$val){
            $val['order_type'] = '采购订单';
        }
        unset($val);
        reset($ret_data);
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_sku_by_barcode($barcode){
        $sql = "SELECT sku FROM goods_sku WHERE barcode=:barcode";
        $sql_value = array(':barcode' => $barcode);
        $goods_r = $this->db->get_row($sql, $sql_value);
        if(!empty($goods_r)){
            return $goods_r['sku'];
        }
        $child_sql = "SELECT sku FROM goods_barcode_child WHERE barcode=:barcode";
        $goods_child_r = $this->db->get_row($child_sql, $sql_value);
        if(!empty($goods_child_r)){
            return $goods_child_r['sku'];
        }else{
            return '';
        }
    }

}