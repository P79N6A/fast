<?php
require_model('tb/TbModel');

class RecordScanCommonModel extends TbModel {
	public $dj_type;
	public $dj_type_map = array();
	public $cur_dj_info = array();

    public function __construct($dj_type = 'wbm_store_out') {
    	parent::__construct();
    	$this->dj_type = $dj_type;
    	
    	//盘点单
    	$info = array(
    			'dj_type'=>'take_stock',
    			'tbl'=>'stm_take_stock_record',
    			'tbl_notice'=>'stm_take_stock_record_detail',
    			'id_fld'=>'take_stock_record_id',
    			'add_time_fld'=>'is_add_time as order_time',
                        'ys_fld'=>'status as ys_flag',//验收标识字段
    			'name'=>'盘点单',
    			'dj_name'=>'盘点单',
    			'update_scan_num_url' => '?app_act=stm/take_stock_record/update_scan_num&is_scan_tag=1&record_code=',
    			'ys_url'=> '?app_act=stm/take_stock_record/do_sure&is_scan_tag=1&take_stock_record_id=',
    			'dj_price'=>'purchase_price',
    	);
    	$this->dj_type_map['take_stock'] = $info;
        
        //调整单
    	$info = array(
    			'dj_type'=>'adjust',
    			'tbl'=>'stm_stock_adjust_record',
    			'tbl_notice'=>'stm_stock_adjust_record_detail',
    			'id_fld'=>'stock_adjust_record_id',
    			'add_time_fld'=>'is_add_time as order_time',
                        'ys_fld'=>'is_check_and_accept as ys_flag',//验收标识字段
    			'name'=>'调整单',
    			'dj_name'=>'调整单',
    			'update_scan_num_url' => '?app_act=stm/stock_adjust_record/update_scan_num&is_scan_tag=1&record_code=',
    			'ys_url'=> '?app_act=stm/stock_adjust_record/do_scan_checkin&is_scan_tag=1&stock_adjust_record_id=',
    			'dj_price'=>'purchase_price',
    	);
    	$this->dj_type_map['adjust'] = $info;
        
        //移仓单
    	$info = array(
    			'dj_type'=>'shift_out',
    			'tbl'=>'stm_store_shift_record',
    			'tbl_notice'=>'stm_store_shift_record_detail',
    			'id_fld'=>'shift_record_id',
    			'add_time_fld'=>'is_add_time as order_time',
                        'ys_fld'=>'is_sure as ys_flag',//验收标识字段
    			'name'=>'移仓单',
    			'dj_name'=>'移仓单',
    			'update_scan_num_url' => '?app_act=stm/store_shift_record/update_scan_num&is_scan_tag=1&record_code=',
    			'ys_url'=> '?app_act=stm/store_shift_record/do_scan_sure&shift_record_id=',
    			'dj_price'=>'purchase_price',
    	);
    	$this->dj_type_map['shift_out'] = $info;
    	$this->cur_dj_info = $this->dj_type_map[$this->dj_type];
    }

    function view_scan($record_code){
	    $sql = "select count(*) from b2b_box_task where relation_code = :record_code and record_type =:record_type";
	    $c = ctx()->db->getOne($sql,array(':record_code'=>$record_code,':record_type'=>$this->dj_type));
	    if ($c>0){
		    return $this->format_ret(-1,'','此单据已经用过装箱扫描功能，无法再用普通扫描');
	    }
	    $tbl = $this->cur_dj_info['tbl'];
	    $tbl_mx = $this->cur_dj_info['tbl'].'_detail';
	    $id_fld = $this->cur_dj_info['id_fld'];
	    $add_time_fld = $this->cur_dj_info['add_time_fld'];
            if($this->dj_type=='shift_out'){
              $sql = "select {$id_fld} as record_id,record_code,shift_out_store_code,{$add_time_fld} from {$tbl} where record_code = :record_code";  
            }else{
              $sql = "select {$id_fld} as record_id,record_code,store_code,{$add_time_fld},relation_code from {$tbl} where record_code = :record_code";
            } 
            $dj_info = ctx()->db->get_row($sql,array(':record_code'=>$record_code));
             if($this->dj_type=='shift_out'){
                  $sql = "select goods_code,spec1_code,spec2_code,sku,out_num as num from {$tbl_mx} where record_code = :record_code";
             }else{
                  $sql = "select goods_code,spec1_code,spec2_code,sku,num from {$tbl_mx} where record_code = :record_code";
             }
		$db_mx = ctx()->db->get_all($sql,array(':record_code'=>$record_code));

		$mx_data = array();
		$must_scan_mx = array();
		$must_scan_mx_zero_num = array();//要扫描的明细，但完成单的数量为0,要去通知单取值
		$total_sl = 0;
		$total_scan_sl = 0;
		$dj_mx_map = array();
                if ($this->dj_type != 'adjust') {
            foreach ($db_mx as $sub_mx) {
                $total_sl += $sub_mx['enotice_num'];
                $total_scan_sl += $sub_mx['num'];
                if ($sub_mx['num'] > 0) {
                    $sub_mx['num'] = (int) $sub_mx['num'];
                    $mx_data[] = $sub_mx;
                }
                $must_scan_mx_zero_num[$sub_mx['sku']] = array('num' => $sub_mx['num']);
                $dj_mx_map[$sub_mx['sku']] = $sub_mx;
            }
        } else {
                if(!empty($db_mx)){
                $adjust_num=1;
                }
                foreach ($db_mx as $sub_mx) {
                $total_sl += $sub_mx['enotice_num'];
                if ($sub_mx['num'] > 0) {
                    $total_scan_sl += $sub_mx['num'];
                    $sub_mx['num'] = (int) $sub_mx['num'];           
                }
                $mx_data[] = $sub_mx;
                $must_scan_mx_zero_num[$sub_mx['sku']] = array('num' => $sub_mx['num']);
                $dj_mx_map[$sub_mx['sku']] = $sub_mx;
            }
        }
        //如果存在已扫描数，就要读取已扫描的明细
        $scan_barcode_map = array();
        $scan_data = array();
        $scan_data_js = array();
        if ($total_scan_sl>0||$adjust_num==1){
	        $scan_data = load_model('util/ViewUtilModel')->record_detail_append_goods_info($mx_data,1);
			$scan_barcode_map = load_model('util/ViewUtilModel')->get_map_arr($scan_data,'barcode',0,'sku');
			$sku_list = "'".join("','",$scan_barcode_map)."'";
			$sql = "select sku,barcode from goods_barcode_child where sku in({$sku_list})";
			$db_child_barcode = ctx()->db->get_all($sql);
			$child_barcode_arr = load_model('util/ViewUtilModel')->get_map_arr($db_child_barcode,'barcode',0,'sku');
			if (!empty($child_barcode_arr)){
				$scan_barcode_map = $scan_barcode_map + $child_barcode_arr;
            }
			$scan_data_js = load_model('util/ViewUtilModel')->get_map_arr($scan_data,'sku',0,'num');
        }
        if (empty($must_scan_mx) || !empty($must_scan_mx_zero_num)){
	
            foreach($db_mx as $sub_mx){
                $must_scan_mx[$sub_mx['sku']] = array('num'=>$sub_mx['num']);
            }
        }
        //echo '<hr/>$must_scan_mx<xmp>'.var_export($must_scan_mx,true).'</xmp>';die;

		/*
        $sql = "select spec1_code,spec1_name from base_spec1";
        $db_spec1 = ctx()->db->get_all($sql);
        $response['base_spec1'] = load_model('util/ViewUtilModel')->get_map_arr($db_spec1,'spec1_code');

        $sql = "select spec2_code,spec2_name from base_spec2";
        $db_spec2 = ctx()->db->get_all($sql);
        $response['base_spec2'] = load_model('util/ViewUtilModel')->get_map_arr($db_spec2,'spec2_code');
        */
        $result = array();
        $result['total_sl'] = (int)$total_sl;
        $result['total_scan_sl'] = (int)$total_scan_sl;
        $result['dj_info'] = $dj_info;

        $result['dj_info']['dj_type'] = $this->cur_dj_info['dj_type'];
        $result['dj_info']['dj_type_name'] = $this->cur_dj_info['dj_name'];
        $result['dj_info']['dj_ys_url'] = $this->cur_dj_info['ys_url'].$dj_info['record_id'];
        $result['dj_info']['dj_update_scan_num_url'] = $this->cur_dj_info['update_scan_num_url'].$dj_info['record_code'];

        $result['scan_data'] = $scan_data;
        $result['scan_data_js'] = $scan_data_js;
        $result['scan_barcode_map'] = $scan_barcode_map;
        $result['must_scan_mx'] = $must_scan_mx;

        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1','goods_spec2'));

		$result['base_spec1_name'] = $cfg['goods_spec1'];
		$result['base_spec2_name'] = $cfg['goods_spec2'];
        if($this->dj_type != 'shift_out'){
            $sql = "select store_code from {$tbl} where record_code = :record_code";
            $store_info = ctx()->db->get_row($sql, array(':record_code' => $record_code));
            foreach ($result['scan_data'] as &$vv){
                $shelf_info = $this->db->get_all(
                    "select 
                                  distinct bs.shelf_name 
                          from base_shelf bs 
                          left join goods_shelf gs on bs.shelf_code = gs.shelf_code and bs.store_code = gs.store_code
                          where gs.store_code = :store_code and gs.sku = :sku",
                    array(':store_code' => $store_info['store_code'], ':sku' => $vv['sku']));
                $shelf_name = '';
                foreach ($shelf_info as $val){
                    $shelf_name .= $val['shelf_name'] . ',';
                }
                $shelf_name = rtrim($shelf_name, ',');
                $vv['shelf_name'] = isset($shelf_name) ? $shelf_name : '';
            }
        }

        //echo '<hr/>$scan_barcode_map<xmp>'.var_export($scan_barcode_map,true).'</xmp>';
		return $this->format_ret(1,$result);
    }

	function save_scan($req){
	    $tbl = $this->cur_dj_info['tbl'];
	    $tbl_mx = $this->cur_dj_info['tbl'].'_detail';

	    $dj_name = $this->cur_dj_info['dj_name'];

	    $id_fld = $this->cur_dj_info['id_fld'];
	    $ys_fld = $this->cur_dj_info['ys_fld'];
		$record_code = $req['record_code'];
	
		$record_type = $req['dj_type'];
		$scan_barcode = $req['scan_barcode'];
		$barcode_is_exist = $req['barcode_is_exist'];
		//echo '<hr/>$req<xmp>'.var_export($req,true).'</xmp>';
		if(empty($record_code) || empty($record_type) || empty($scan_barcode)){
			return $this->format_ret(-1,'','单号/单据类型/扫描条码 数据缺失');
		}
		$ret = $this->parse_scan_barcode($scan_barcode);
		if ($ret['status']<0){
			return $ret;
		}
		$sku = $ret['data']['sku'];
		$goods_code = $ret['data']['goods_code'];
	
		$dj_scan_row = array();
		$dj_scan_row['goods_code'] = $goods_code;
		$dj_scan_row['spec1_code'] = $ret['data']['spec1_code'];
		$dj_scan_row['spec2_code'] = $ret['data']['spec2_code'];
		$dj_scan_row['enotice_num'] = 0;
		$dj_scan_row['num'] = 0;
                if ($this->dj_type == 'shift_out') {
		$sql = "select {$id_fld} as record_id,{$ys_fld},rebate,shift_out_store_code as store_code from {$tbl} where record_code = :record_code";
                }else{
                $sql = "select {$id_fld} as record_id,{$ys_fld},rebate,store_code from {$tbl} where record_code = :record_code";
                }
                $dj_row = ctx()->db->get_row($sql,array(':record_code'=>$record_code));	
		if ($dj_row['ys_flag']>0){
	       return $this->format_ret(-1,'',$dj_name.'已验收');
		}
		$rebate = empty($dj_row['rebate']) ? 1 : $dj_row['rebate'];
		$price = 0;
		if ($barcode_is_exist == -1){
			$_dj_price = $this->cur_dj_info['dj_price'];
                $sku_map = array($sku=>$dj_scan_row['goods_code']);
			$ret = load_model('prm/GoodsModel')->get_goods_price($_dj_price,$sku_map);
			if ($ret['status']<0){
				return $ret;
			}
			$price = (float)@$ret['data'][$sku];
		}
                if ($this->dj_type == 'shift_out') {
            $insert_data = array(
                'pid' => $dj_row['record_id'],
                'goods_code' => $dj_scan_row['goods_code'],
                'spec1_code' => $dj_scan_row['spec1_code'],
                'spec2_code' => $dj_scan_row['spec2_code'],
                'record_code' => $record_code,
                'sku' => $sku,
                'out_num' => 1,
                'price' => $price,
                'rebate' => $rebate,
                'out_money' => $price * 1 * $rebate,
            );
              $update_str = "out_num = out_num +VALUES(out_num),out_money = (out_num+VALUES(out_num)-1)*price*rebate";
        } else {
					//调整单 获取商品成本价
					if($tbl_mx == 'stm_stock_adjust_record_detail'){
						$key_arr = array('cost_price','price');
						$sku_info = load_model('goods/SkuCModel')->get_sku_info($sku, $key_arr);
						$cost_price = $sku_info['cost_price'];
                                                $price = $sku_info['price'];
						$sql = "select a.* from base_goods a where a.goods_code = :code";
						$goods = $this->db->get_row($sql, array('code' => $dj_scan_row['goods_code']));
						if ($cost_price <= 0 || empty($cost_price)) {
							$cost_price = $goods['cost_price'];
						}
                                                if ($price <= 0 || empty($price)) {
							$price = $goods['sell_price'];
						}
						$insert_data = array(
								'pid' => $dj_row['record_id'],
								'goods_code' => $dj_scan_row['goods_code'],
								'spec1_code' => $dj_scan_row['spec1_code'],
								'spec2_code' => $dj_scan_row['spec2_code'],
								'record_code' => $record_code,
								'sku' => $sku,
								'num' => 1,
								'price' => $price,
								'rebate' => $rebate,
								'money' => $price * 1 * $rebate,
								'cost_price'=>$cost_price,
						);
					}else{
						$insert_data = array(
								'pid' => $dj_row['record_id'],
								'goods_code' => $dj_scan_row['goods_code'],
								'spec1_code' => $dj_scan_row['spec1_code'],
								'spec2_code' => $dj_scan_row['spec2_code'],
								'record_code' => $record_code,
								'sku' => $sku,
								'num' => 1,
								'price' => $price,
								'rebate' => $rebate,
								'money' => $price * 1 * $rebate,
						);
					}

            //echo '<hr/>$insert_data<xmp>'.var_export($insert_data,true).'</xmp>';die;
            $update_str = "num = num +VALUES(num),money = (num +VALUES(num))*price*rebate";
        }
        $ret = $this->insert_multi_duplicate($tbl_mx, array($insert_data), $update_str);
        if($tbl_mx == 'stm_stock_adjust_record_detail'){
        $sql_money = "select price,rebate,num from {$tbl_mx} where sku=:sku and pid=:pid";
        $mes = $this->db->get_all($sql_money,array(':sku'=>$sku,':pid'=>$dj_row['record_id']));
        parent::update_exp($tbl_mx, array('money'=>$mes[0]['price']*$mes[0]['rebate']*$mes[0]['num']),array('sku'=>$sku,'pid'=>$dj_row['record_id']));
        }
        if ($ret['status']<0){
	       return $this->format_ret(-1,'','保存扫描数据失败');
        }

                if ($this->dj_type == 'shift_out') {
                    $sql = "select out_num as num from {$tbl_mx} where record_code = :record_code and sku = :sku";
                }else{
                    $sql = "select num from {$tbl_mx} where record_code = :record_code and sku = :sku";
                }
        $_scan_after_row = ctx()->db->get_row($sql,array(':record_code'=>$record_code,':sku'=>$sku));
        if (empty($_scan_after_row)){
	       return $this->format_ret(-1,'','单据不存在sku:'.$sku);
        }
           //维护 b2b_lof_datail 表
            $ret = $this->update_lof($record_code,$dj_row,$dj_scan_row,$sku,$this->dj_type);
            if ($ret['status']<0){
                    return $ret;
            }
        if ($this->dj_type == 'take_stock'){
            //盘点单回写金额和数量
            load_model('stm/TakeStockRecordModel')->mainWriteBack($dj_row['record_id']);
        }elseif($this->dj_type == 'adjust'){
            //调整单回写金额和数量
            load_model('stm/StmStockAdjustRecordDetailModel')->mainWriteBack($dj_row['record_id']);
        }elseif($this->dj_type == 'shift_out'){
            //移仓单回写金额和数量
            load_model('stm/StoreShiftRecordDetailModel')->mainWriteBack($dj_row['record_id']);
        }
        
        $_scan_after_num = (int)$_scan_after_row['num'];
        $result = array();
        if ($barcode_is_exist == -1){
	        $goods_info = load_model('util/ViewUtilModel')->record_detail_append_goods_info(array(array('sku'=>$sku)),1,1);
	        $result = $goods_info[0];
        }
        $result['num'] = $_scan_after_num;
        $result['sku'] = $sku;
        $result['scan_barcode'] = $scan_barcode;
        $result['barcode_is_exist'] = $barcode_is_exist;
        if($this->dj_type != 'shift_out') {
            $sql = "select store_code from {$tbl} where record_code = :record_code";
            $store_info = ctx()->db->get_row($sql, array(':record_code' => $record_code));
            $shelf_info = $this->db->get_all("select distinct bs.shelf_name from base_shelf bs left join goods_shelf gs on bs.shelf_code = gs.shelf_code and bs.store_code = gs.store_code where gs.store_code = :store_code and gs.sku = :sku", array(':store_code' => $store_info['store_code'], ':sku' => $sku));
            $shelf_name = '';
            foreach ($shelf_info as $val) {
                $shelf_name .= $val['shelf_name'] . ',';
            }
            $shelf_name = rtrim($shelf_name, ',');
            $result['shelf_name'] = isset($shelf_name) ? $shelf_name : '';
        }
        //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';
		return $this->format_ret(1,$result);
    }

    function parse_scan_barcode($i_barcode){
		$sql = "select goods_code,sku,spec1_code,spec2_code from goods_sku where barcode = :barcode or gb_code = :gb_code";
		$sku_row = ctx()->db->get_row($sql,array(':barcode'=>$i_barcode, ':gb_code'=>$i_barcode));
		$child_sku_arr = array();
		if (empty($sku_row)){
		$sql = "select goods_code,sku,spec1_code,spec2_code from goods_barcode_child where barcode = :barcode";
			$child_sku_arr = ctx()->db->get_all($sql,array(':barcode'=>$i_barcode));
			if (!empty($child_sku_arr)){
				if (count($child_sku_arr)>1){
					return $this->format_ret(-1,'','存在重复的子条码,无法识别');
				}else{
					$_first_row = $child_sku_arr[0];
					$sku = isset($_first_row['sku']) ? $_first_row['sku'] : null;
					$goods_code = isset($_first_row['goods_code']) ? $_first_row['goods_code'] : null;
					$spec1_code = isset($_first_row['spec1_code']) ? $_first_row['spec1_code'] : null;
					$spec2_code = isset($_first_row['spec2_code']) ? $_first_row['spec2_code'] : null;
				}
			}else {
				//盘点单，移仓单继续识别条码识别规则
                $ad_order_type_arr = array('take_stock','shift_out');
				if (in_array($this->dj_type,$ad_order_type_arr)){
					$barcode_rule_ret = load_model('prm/GoodsBarcodeIdentifyRuleModel')->yanzheng($i_barcode,1);
					if ($barcode_rule_ret['status']>0){
						$sku = $barcode_rule_ret['data']['sku'];
						$goods_code = isset($barcode_rule_ret['data']['goods_code']) ? $barcode_rule_ret['data']['goods_code'] : null;
						$spec1_code = isset($barcode_rule_ret['data']['spec1_code']) ? $barcode_rule_ret['data']['spec1_code'] : null;
						$spec2_code = isset($barcode_rule_ret['data']['spec2_code']) ? $barcode_rule_ret['data']['spec2_code'] : null;
					}
					//var_dump($sku);var_dump($goods_code);var_dump($spec1_code);var_dump($spec2_code);exit;
				}
			}
		}else{
			$sku = $sku_row['sku'];
			$goods_code = $sku_row['goods_code'];
			$spec1_code = $sku_row['spec1_code'];
			$spec2_code = $sku_row['spec2_code'];
		}
                
                if((empty($barcode_rule_ret)||$barcode_rule_ret['status'] < 0)&&empty($sku_row) && empty($child_sku_arr)){
                         return $this->format_ret(-1, '', '条码无效');
                }
                $result = array('sku'=>$sku,'goods_code'=>$goods_code,'spec1_code'=>$spec1_code,'spec2_code'=>$spec2_code);
		return $this->format_ret(1,$result);
    }

    function update_lof($record_code,$dj_row,$dj_scan_row,$sku,$order_type = 'purchase'){
		$ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
		if ($ret_lof['status']<0){
			return $ret_lof;
		}
		$lof_no = $ret_lof['data']['lof_no'];
		$production_date = $ret_lof['data']['production_date'];
		$insert_data = array(
			'pid'=>$dj_row['record_id'],
			'order_code'=>$record_code,
			'order_type'=>$order_type,
			'store_code'=>$dj_row['store_code'],
			'goods_code'=>$dj_scan_row['goods_code'],
			'spec1_code'=>$dj_scan_row['spec1_code'],
			'spec2_code'=>$dj_scan_row['spec2_code'],
			'sku'=>$sku,
			'lof_no'=>$lof_no,
			'production_date'=>$production_date,
			'num'=>1,
			'occupy_type'=>0,
		);
		//echo '<hr/>$insert_data<xmp>'.var_export($insert_data,true).'</xmp>';die;
        $update_str = "num = num +VALUES(num)";
        $ret = $this->insert_multi_duplicate('b2b_lof_datail', array($insert_data), $update_str);
        if ($ret['status']<0){
	       return $this->format_ret(-1,'','保存扫描数据失败');
        }
	    return $this->format_ret(1);
    }

}