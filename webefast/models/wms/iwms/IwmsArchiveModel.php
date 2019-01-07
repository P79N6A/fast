<?php
/**
* sync_mode = incr 增量 | all 全量 | fix 上传出错的再次上传
*/
set_time_limit(0);
require_model('wms/WmsBaseModel');
class IwmsArchiveModel extends WmsBaseModel {
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

	function update_result($type,$code,$tbl_changed,$ret){
		$ins = array(
			'efast_store_code'=>$this->wms_cfg['efast_store_code'],
			'type'=>$type,
			'code'=>$code,
			'tbl_changed'=>$tbl_changed,
                        'wms_config_id'=>$this->wms_cfg['wms_config_id'],
		);
		if ($ret['status']<0){
			$ins['is_success'] = 0;
			$ins['msg'] = $ret['message'];
		}else{
            $ins['api_code'] = isset($ret['data']['data']['wmsid']) && !empty($ret['data']['data']['wmsid']) ? $ret['data']['data']['wmsid'] : '';
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
		if(CTX()->saas->get_saas_key()=='2380'&&$type=='goods_barcode'){
			$page_no = 13;
		}
		while(1){
			$prev_tbl_changed = $tbl_changed;
			if ($this->sync_mode == 'fix'){
				$code_fld = 'code';
				$sql = "SELECT lastchanged,{$code_fld} FROM wms_archive where is_success = 0 and efast_store_code = :efast_store_code and type = :type and lastchanged>= :lastchanged order by lastchanged";
			}else{
				$sql = "SELECT lastchanged,{$code_fld} FROM {$type} where lastchanged>= :lastchanged {$wh} order by lastchanged";
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

			$this->biz_sync($code_arr,$type,$code_fld2,$method,$sync_data);

			$last_row = end($db_data);
			$tbl_changed = $last_row['lastchanged'];
			if ($tbl_changed == $prev_tbl_changed){
				$page_no++;
			}else{
				$page_no = 1;
			}
            /*
			echo '<hr/>$tbl_changed<xmp>'.var_export($tbl_changed,true).'</xmp>';
			echo '<hr/>$page_no<xmp>'.var_export($page_no,true).'</xmp>';
			die;*/
		}
	}

        /**
         * 获取同步条码数据
         * @param array $code_arr 条码集合
         * @return array 条码数据
         */
	function get_sync_data_barcode($code_arr){
		$code_list = "'".join("','",array_keys($code_arr))."'";
		$sql = "SELECT gs.goods_code AS style,
                    gs.spec1_code AS color,
                    gs.spec2_code AS size,
                    gs.barcode AS sku,
                    gs.sku AS sys_sku,
                    gs.gb_code AS nation_sku,
                    gs.remark AS note,
                    bg.diy AS IsComb
                    FROM goods_sku AS gs INNER JOIN base_goods AS bg ON gs.goods_code=bg.goods_code WHERE gs.barcode IN({$code_list}) AND gs.barcode<>'' AND gs.barcode IS NOT NULL ORDER BY gs.lastchanged";
		$sync_data = ctx()->db->get_all($sql);
                    //2、商品上传，支持国标码（nation_sku）和sku备注（note）上传
                //增加子条码
                $key_arr = array();
                $sku_arr = array();
                foreach($sync_data as $k=>$val){
                    $key_arr[$val['sys_sku']] = $k;
                    $sku_arr[] = $val['sys_sku'];
                    unset($sync_data[$k]['sys_sku']);
                }
                if(!empty($sku_arr)){
                    $sql_values = array();
                     $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                     $sql_chlid = "select barcode,sku from goods_barcode_child where sku in({$sku_str})";
                     $chlid_data = $this->db->get_all($sql_chlid,$sql_values);
                     $child_i_arr = array();

                     foreach($chlid_data as $v){
                        $child_i_arr[$v['sku']] = !isset($child_i_arr[$v['sku']])?1: $child_i_arr[$v['sku']]+1;
                        $scan_sku_key = 'scan_sku'.$child_i_arr[$v['sku']];
                        $sync_data[$key_arr[$v['sku']]][$scan_sku_key] = $v['barcode'];
                     }
                 }
                 
		return $sync_data;
	}
    
    /**
     * 获取同步sku数据
     * @param array $code_arr 条码集合
     * @return array sku数据
     */
	function get_sync_sku_data($code_arr){
		$code_list = "'".join("','",array_keys($code_arr))."'";
		$sql = "SELECT
                    gs.goods_code AS style,
                    gs.spec1_code AS color,
                    gs.spec2_code AS size,
                    gs.sku AS sku,
                    gs.gb_code AS nation_sku,
                    gs.barcode AS provide_sku,
                    gs.remark AS note,
                    bg.diy AS IsComb
                FROM
                    goods_sku AS gs
                INNER JOIN 
                    base_goods AS bg ON gs.goods_code = bg.goods_code
                WHERE
                    gs.sku IN ({$code_list})
                ORDER BY
                    gs.lastchanged";
		$sync_data = $this->db->get_all($sql);
        $key_arr = array();
        $sku_arr = array();
        foreach($sync_data as $k => $val){
            $key_arr[$val['sku']] = $k;
            $sku_arr[] = $val['sku'];
        }
        if(!empty($sku_arr)){
            $sql_values = array();
             $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
             $sql_chlid = "select barcode,sku from goods_barcode_child where sku in({$sku_str})";
             $chlid_data = $this->db->get_all($sql_chlid,$sql_values);
             $child_i_arr = array();
             foreach($chlid_data as $v){
                $child_i_arr[$v['sku']] = !isset($child_i_arr[$v['sku']])? 1: $child_i_arr[$v['sku']] + 1;
                $scan_sku_key = 'scan_sku' . $child_i_arr[$v['sku']];
                $sync_data[$key_arr[$v['sku']]][$scan_sku_key] = $v['barcode'];
             }
         }
		return $sync_data;
	}

    function get_sync_data_diy($code_arr) {
        $code_list = "'" . join("','", array_keys($code_arr)) . "'";
        $sql = "SELECT gd.p_sku, gd.sku, gd.num, bg.`status`,gd.lastchanged
                FROM goods_diy AS gd INNER JOIN base_goods AS bg ON bg.goods_code=gd.p_goods_code
                WHERE bg.diy=1 AND gd.p_sku IN ({$code_list}) ORDER BY gd.lastchanged";
        $data = $this->db->get_all($sql);

        $p_sku = array_column($data, 'p_sku');
        $sku = array_column($data, 'sku');
        $sku = array_merge($sku, $p_sku);
        $sku_str = deal_array_with_quote($sku);
        $barcode = $this->db->get_all("SELECT sku,barcode FROM goods_sku WHERE sku IN({$sku_str})");
        $barcode = array_column($barcode, 'barcode', 'sku');
        $sync_data = array();

        foreach ($data as $val) {
            $d = array();
            $d['CombSku'] = $barcode[$val['p_sku']];
            $d['Sku'] = $barcode[$val['sku']];
            $d['Qty'] = $val['num'];
            $d['Status'] = $val['Status'] == 0 ? 1 : 0;
            $d['lastchanged'] = $val['lastchanged'];
            $sync_data[] = $d;
        }

        return $sync_data;
    }

    function get_sync_data($code_arr,$sql_tpl){
		$code_list = "'".join("','",array_keys($code_arr))."'";
		$sql = str_replace('[#code_list]',$code_list,$sql_tpl);
		$sync_data = ctx()->db->get_all($sql);
		return $sync_data;
	}

	function biz_sync($code_arr,$type,$code_fld,$method,$sync_data){
		//echo '<hr/>$code_arr<xmp>'.var_export($code_arr,true).'</xmp>';
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
                        $sub_data['lastchanged'] = isset($sub_data['lastchanged'])?$sub_data['lastchanged']:'';
			$_v = "{$sub_data[$code_fld]},{$sub_data['lastchanged']}";
			if (in_array($_v,$exists_code_arr) && $this->sync_mode <> 'all'){
				continue;
			}
			unset($sub_data['lastchanged']);
			$params = $sub_data;
			$ret = $this->biz_req($method,$sub_data);
			$_code = $sub_data[$code_fld];
			/*
			echo '<hr/>$method<xmp>'.var_export($method,true).'</xmp>';
			echo '<hr/>$sub_data<xmp>'.var_export($sub_data,true).'</xmp>';
			echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
			*/
			$this->update_result($type,$_code,$code_arr[$_code],$ret);
			//die;
		}
	}

	function spec1_sync($batch_num = 200){
		$sql_tpl = "SELECT spec1_code AS clr_code,spec1_name AS clr_name,lastchanged FROM base_spec1 where spec1_code in([#code_list]) order by lastchanged";
		$task_name = $this->goods_spec1_name;
		$params = array('code_fld' => 'spec1_code','code_fld2' => 'clr_code','method'=>'ewms.color.set','sql_tpl'=>$sql_tpl);
		$this->comm_sync('base_spec1',$task_name,$params,$batch_num);
	}

	function spec2_sync($batch_num = 200){
		$sql_tpl = "SELECT spec2_code AS size_code,spec2_name AS size_name,lastchanged FROM base_spec2 where spec2_code in([#code_list]) order by lastchanged";
		$task_name = $this->goods_spec2_name;
		$params = array('code_fld' => 'spec2_code','code_fld2' => 'size_code','method'=>'ewms.size.set','sql_tpl'=>$sql_tpl);
		$this->comm_sync('base_spec2',$task_name,$params,$batch_num);
	}

	function brand_sync($batch_num = 200){
		$sql_tpl = "SELECT brand_code AS brand_code,brand_name AS brand_name,lastchanged FROM base_brand where brand_code in([#code_list]) order by lastchanged";
		$task_name = "品牌";
		$params = array('code_fld' => 'brand_code','code_fld2' => 'brand_code','method'=>'ewms.brand.set','sql_tpl'=>$sql_tpl);
		$this->comm_sync('base_brand',$task_name,$params,$batch_num);
	}

	function category_sync($batch_num = 200){
		$sql_tpl = "SELECT category_code AS cat_code,category_name AS cat_name,lastchanged FROM base_category where category_code in([#code_list]) order by lastchanged";
		$task_name = "分类";
		$params = array('code_fld' => 'category_code','code_fld2' => 'cat_code','method'=>'ewms.category.set','sql_tpl'=>$sql_tpl);
		$this->comm_sync('base_category',$task_name,$params,$batch_num);
	}

	function season_sync($batch_num = 200){
		$sql_tpl = "SELECT season_code AS season_code,season_name AS season_name,lastchanged FROM base_season where season_code in([#code_list]) order by lastchanged";
		$task_name = "季节";
		$params = array('code_fld' => 'season_code','code_fld2' => 'season_code','method'=>'ewms.season.set','sql_tpl'=>$sql_tpl);
		$this->comm_sync('base_season',$task_name,$params,$batch_num);
	}

	function year_sync($batch_num = 200){
		$sql_tpl = "SELECT year_code AS year_code,year_name AS year_name,lastchanged FROM base_year where year_code in([#code_list]) order by lastchanged";
		$task_name = "年份";
		$params = array('code_fld' => 'year_code','code_fld2' => 'year_code','method'=>'ewms.year.set','sql_tpl'=>$sql_tpl);
		$this->comm_sync('base_year',$task_name,$params,$batch_num);
	}

	function shop_sync($batch_num = 200){
            $wms_config_id = $this->db->get_value("SELECT wms_config_id FROM wms_config WHERE wms_system_code='iwms'");
            $sql_tpl = "SELECT
                            if(ss.outside_code IS NULL,bs.shop_id,ss.outside_code) AS shop_code,
                            bs.shop_name AS shop_name,
                            bs.is_active AS isvalid,
                            bs.lastchanged
                        FROM base_shop AS bs
                        LEFT JOIN sys_api_shop_store AS ss ON bs.shop_code = ss.shop_store_code AND ss.shop_store_type = 0 
                        AND ss.outside_type = 0 AND ss.p_type = 1 AND p_id = {$wms_config_id}
                        WHERE bs.shop_code IN ([#code_list]) ORDER BY bs.lastchanged";
		$task_name = "商店";
		$params = array('code_fld' => 'shop_code','code_fld2' => 'shop_code','method'=>'ewms.shop.set','sql_tpl'=>$sql_tpl);
		$this->comm_sync('base_shop',$task_name,$params,$batch_num);
	}

	function area_sync($batch_num = 200){
		$sql_tpl = "SELECT id AS region_code,parent_id as parent_code,(type-1) as region_type,name AS region_name,lastchanged FROM base_area where id in([#code_list]) and type in(2,3,4) and id>=110000 order by lastchanged";
		$task_name = "地址";
		$params = array('code_fld' => 'id','code_fld2' => 'region_code','method'=>'ewms.region.set','sql_tpl'=>$sql_tpl,'wh'=>' and type in(1,2,3,4)');
		$this->comm_sync('base_area',$task_name,$params,$batch_num);
	}


	function goods_sync($batch_num = 200){
		$sql_tpl = "SELECT
					t1.goods_code AS style,
					t1.goods_name AS style_name,
					IFNULL(t1.sell_price,'0.00') AS dp_price,
					t1.brand_code AS brand,
					t1.category_code AS category,
					t1.season_code AS season,
					t1.year_code AS year,
					t1.diy AS IsComb,
					t1.lastchanged
				FROM
					base_goods t1

				WHERE
					t1.goods_code IN ([#code_list])
				ORDER BY
					t1.lastchanged";
		$task_name = "商品";
		$params = array('code_fld' => 'goods_code','code_fld2' => 'style','method'=>'ewms.style.set','sql_tpl'=>$sql_tpl);
		$this->comm_sync('base_goods',$task_name,$params,$batch_num);
	}

    /**
     * 条码同步
     * @param int $batch_num 批次同步数量
     */
    function barcode_sync($batch_num = 200) {
        if($this->wms_cfg['goods_upload_type'] == 1) {//sku上传模式
            $this->sku_sync($batch_num);
            return;
        }
        $task_name = "条码"; //
        $params = array(
            'code_fld' => 'barcode',
            'code_fld2' => 'sku',
            'method' => 'ewms.sku.set',
            'sync_data_fn' => 'get_sync_data_barcode',
            'wh' => " AND barcode<>'' AND barcode IS NOT NULL "
        );
        $this->comm_sync('goods_barcode', $task_name, $params, $batch_num);
    }
    
    /**
     * 条码同步 （sku上传模式）
     * @param int $batch_num 批次同步数量
     */
    function sku_sync($batch_num){
        $task_name = "条码(sku上传模式)"; //
        $params = array(
            'code_fld' => 'sku',
            'code_fld2' => 'sku',
            'method' => 'ewms.sku.set',
            'sync_data_fn' => 'get_sync_sku_data',
            'wh' => " AND sku<>'' AND sku IS NOT NULL AND barcode<>'' AND barcode IS NOT NULL "
        );
        $this->comm_sync('goods_sku', $task_name, $params, $batch_num);
    }

    /**
         * 组装商品同步
         * @param int $batch_num 批量执行数量
         */
	function diy_detail_sync($batch_num = 200){
            $task_name = "组装商品";
            $params = array('code_fld' => 'p_sku','code_fld2' => 'CombSku,Sku','method'=>'ewms.skucombdet.set','sync_data_fn'=>'get_sync_data_diy');
            $this->comm_sync('goods_diy',$task_name,$params,$batch_num);
	}

	function sync()
	{
		error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
        set_time_limit(1800);
		@ini_set('memory_limit','2048M');

		$this->sync_mode = 'incr';
		$fn_arr = explode(',','spec1_sync,spec2_sync,brand_sync,category_sync,season_sync,year_sync,shop_sync,goods_sync,barcode_sync,area_sync');
		foreach($fn_arr as $_fn){
			$this->$_fn();
		}
                $this->diy_detail_sync();

		$this->sync_mode = 'fix';
		foreach($fn_arr as $_fn){
			$this->$_fn();
		}

                $this->diy_detail_sync();
	}

}