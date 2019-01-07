<?php
/**
* sync_mode = incr 增量 | all 全量 | fix 上传出错的再次上传
*/
require_model('wms/WmsBaseModel');
class HwwmsArchiveModel extends WmsBaseModel {
	var $sync_mode = '';
	function __construct( $efast_store_code = '' )
	{
		parent::__construct();
		$this->sync_mode = 'incr';
            if(!empty($efast_store_code)){
                $this->get_wms_cfg($efast_store_code);
            }
	}
	
	function set_sync_mode($v){
		$this->sync_mode = $v;
	}

	/*
	* params = array(code_fld => 'spec1_code',code_fld2 => 'clr_code',method=>'ewms.color.set',sql_tpl=>'','wh'=>,sync_data_fn=>fn_name);
	*/
	function comm_sync($type = 'base_spec1',$task_name = '',$params = array(),$batch_num = 200)
	{
		$code_fld = $params['code_fld'];
		$code_fld2 = $params['code_fld2'];
		$method = $params['method'];
		$sql_tpl = isset($params['sql_tpl']) ? $params['sql_tpl'] : '';
		$wh = isset($params['wh']) ? $params['wh'] : '';
		$sync_data_fn = isset($params['sync_data_fn']) ? $params['sync_data_fn'] : '';
		if ($this->sync_mode == 'incr'){
			$tbl_changed = $this->get_tbl_changed($type);
		}else{
			$tbl_changed = '0000-00-00 00:00:00';
		}
		$page_no = 1;
		while(1){
			$prev_tbl_changed = $tbl_changed;
			if ($this->sync_mode == 'fix'){
				$code_fld = 'code';
				$sql = "SELECT lastchanged,{$code_fld} FROM wms_archive where is_success = 0 and efast_store_code = :efast_store_code and type = :type and lastchanged>= :lastchanged order by lastchanged";
			}else{
//                              //  查找指定单据
				$sql = "SELECT lastchanged,{$code_fld} FROM {$type} where lastchanged>= :lastchanged  {$wh} order by lastchanged";
			}
			if ($page_no>1){
				$start_limit  = $batch_num * ($page_no -1);
				$sql .= " limit {$start_limit},{$batch_num}";
			}else{
				$sql .= " limit {$batch_num}";
			}

			if ($this->sync_mode == 'fix'){
				$db_data = ctx()->db->get_all($sql,array(':lastchanged'=>$tbl_changed,':efast_store_code'=>$this->wms_cfg['efast_store_code'],':type'=>$type));
			}else{
				$db_data = ctx()->db->get_all($sql,array(':lastchanged'=>$tbl_changed));
			}

			if (empty($db_data)){
				if ($page_no == 1){
					echo $task_name."同步已完成 \n";
					return;
				}else{
					$tbl_changed = date('Y-m-d H:i:s',strtotime($tbl_changed) + 1);
					$page_no = 1;
					continue;
				}
			}

			echo $task_name."同步 lastchanged={$tbl_changed} page_no={$page_no} \n";

			$code_arr = load_model('util/ViewUtilModel')->get_map_arr($db_data,$code_fld,0,'lastchanged');
			if (empty($sync_data_fn)){
				$sync_data = $this->get_sync_data($code_arr,$sql_tpl);
			}else{
				$sync_data = $this->$sync_data_fn($code_arr);
			}
                        
                        //$params = array('code_fld' => 'sku','code_fld2' => 'Item','method'=>'ItemRequest','sql_tpl'=>$sql_tpl);
                     
			//echo '<hr/>$sync_data<xmp>'.var_export($sync_data,true).'</xmp>';die;
			$this->biz_sync($code_arr,$type,$code_fld2,$method,$sync_data);
		
			$last_row = end($db_data);
			$tbl_changed = $last_row['lastchanged'];
			if ($tbl_changed == $prev_tbl_changed){
				$page_no++;
			}else{
				$page_no = 1;
			}
  
		}
	}
	
	function goods_sync($batch_num = 200){
		//测试 设置 batch_num = 3
		$task_name = "商品条码";
		$sql_tpl = "SELECT
						t1.goods_code,
						t1.goods_name AS Name,
						IFNULL(t1.sell_price,'0.00') AS Sprice,
						t1.weight as weight,
						t1.unit_code as Unit,
						t1.brand_name as Brand,
						t2.lastchanged,
						t2.barcode as Item,
						t2.barcode as BarCode,
						t2.spec2_name as GoodSpec,
						t2.spec1_name as Color,
						t2.weight as Weight,
						t2.sku
					FROM goods_sku t2
					LEFT JOIN base_goods t1 ON t1.goods_code = t2.goods_code
					WHERE t2.sku IN ([#code_list])
					";
		$params = array('code_fld' => 'barcode','code_fld2' => 'Item','method'=>'ItemRequest','sql_tpl'=>$sql_tpl);
		$this->comm_sync('goods_sku',$task_name,$params,$batch_num);
	}

	function update_result($type,$code,$tbl_changed,$ret){

		$ins = array(
				'efast_store_code'=>$this->efast_store_code,
                              'api_product'=>$this->api_product,
				'type'=>$type,
				'code'=>$code,
				'tbl_changed'=>$tbl_changed,
                                'wms_config_id'=>$this->wms_cfg['wms_config_id'],
		);
		if ($ret['status']<0){
			$ins['is_success'] = 0;
			$ins['msg'] = $ret['message'];
		}else{
			$ins['is_success'] = 1;
			$ins['msg'] = '';
		}

		$update_str = "is_success = VALUES(is_success),msg = VALUES(msg),tbl_changed = VALUES(tbl_changed)";
		$ret = $this->insert_multi_duplicate('wms_archive', array($ins), $update_str);
		return $ret;
	}
	
	function get_tbl_changed($type){
		$sql = "select tbl_changed from wms_archive where efast_store_code = :efast_store_code and type = :type order by tbl_changed desc";
		$tbl_changed = ctx()->db->getOne($sql,array(':efast_store_code'=>$this->wms_cfg['efast_store_code'],':type'=>$type));
		$tbl_changed = empty($tbl_changed) ? '0000-00-00 00:00:00' : $tbl_changed;
		return $tbl_changed;
	}
	
	


	function get_sync_data($code_arr,$sql_tpl){
		$code_list = "'".join("','",array_keys($code_arr))."'";
		$sql = str_replace('[#code_list]',$code_list,$sql_tpl);
		$sync_data = ctx()->db->get_all($sql);
		return $sync_data;
	}

	function biz_sync($code_arr,$type,$code_fld,$method,$sync_data){

		if ($this->sync_mode <> 'all'){
			$sql = "select code,tbl_changed from wms_archive where efast_store_code = :efast_store_code and type = :type and is_success = 1";
			$db_wms = ctx()->db->get_all($sql,array(':efast_store_code'=>$this->wms_cfg['efast_store_code'],':type'=>$type));
			$exists_code_arr = array();
			foreach($db_wms as $sub_wms){
				$exists_code_arr[] = "{$sub_wms['code']},{$sub_wms['tbl_changed']}";
			}
		}else{
			$exists_code_arr = array();
		}
                
                
		foreach($sync_data as $sub_data){
                    
                    
			$_v = "{$sub_data[$code_fld]},{$sub_data['lastchanged']}";
			if (in_array($_v,$exists_code_arr) && $this->sync_mode <> 'all'){
				continue;
			}
                        $lastchanged = $sub_data['lastchanged'];
                        
			unset($sub_data['lastchanged']);
			$params = array();
			$params['Items'] = $sub_data;
			$params['WareHouse'] = $this->wms_cfg['wms_store_code'];
			$params['Company'] = $this->wms_cfg['Company'];
			$ret = $this->biz_req($method,$params);
                        //$code_fld: itme
			$_code = $sub_data[$code_fld];
                        $lastchanged = isset($code_arr[$_code])?$code_arr[$_code]:$lastchanged;
                        
                        
			$this->update_result($type,$_code,$lastchanged,$ret);
			//die;
		}
	}
            






	function sync()
	{
		error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
        set_time_limit(1800);
		@ini_set('memory_limit','2048M');
		//area_sync

		$this->sync_mode = 'incr';
		//$fn_arr = explode(',','spec1_sync,spec2_sync,brand_sync,category_sync,season_sync,year_sync,shop_sync,goods_sync,barcode_sync,area_sync');
			$fn_arr = explode(',','goods_sync');
		

		foreach($fn_arr as $_fn){
			$this->$_fn();
		}
		$this->sync_mode = 'fix';
		foreach($fn_arr as $_fn){
			$this->$_fn();
		}
	}

}