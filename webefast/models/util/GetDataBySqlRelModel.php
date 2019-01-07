<?php
/*
$tbl_cfg = array('order_goods'=>array('fld'=>'rec_id,order_id,goods_id,color_id,size_id,payment_ft ,goods_number'),
'order_info'=>array('fld'=>'order_sn,add_time,sd_id,ck_id,shipping_id','relation_fld'=>'order_id+order_id'),
'goods_barcode'=>array('fld'=>'barcode','relation_fld'=>'goods_id+goods_id,color_id+color_id,size_id+size_id'),
'goods'=>array('fld'=>'goods_sn,goods_name,cost_price,cat_id','relation_fld'=>'goods_id+goods_id'),
'shangdian'=>array('fld'=>'sdmc','relation_fld'=>'id+sd_id'),
'cangku'=>array('fld'=>'ckmc','relation_fld'=>'id+ck_id'),
'category'=>array('fld'=>'cat_name','relation_fld'=>'cat_id+cat_id'),
'shipping'=>array('fld'=>'shipping_name','relation_fld'=>'shipping_id+shipping_id'),
'color'=>array('fld'=>'color_code,color_name','relation_fld'=>'color_id+color_id'),
'size'=>array('fld'=>'size_code,size_name','relation_fld'=>'size_id+size_id'),
'lsxhdlk'=>array('fld'=>'order_id,p_id as lsxhd_id','relation_fld'=>'order_id+order_id'),
'lsxhd'=>array('fld'=>'id as lsxhd_id,djbh as lsxhd_djbh','relation_fld'=>'id+lsxhd_id'),
);
*/
class GetDataBySqlRelModel extends TbModel
{
	public $tbl_cfg;
	public $p_idx_fld;
    /**
     *
     * 构造方法，初始化
     */
    public function __construct()
    {
			parent::__construct();
			date_default_timezone_set('PRC');
    }

	function get_data_by_cfg($key_val_list,$result = array()){
		if (empty($result)){
			return $result;
		}
		$tbl_cfg = $this->tbl_cfg;
		$key_fld = $this->p_idx_fld;
		foreach($tbl_cfg as $table_name=>$sub_cfg){
			//echo '<hr/>$result_xx<xmp>'.var_export($result,true).'</xmp>';
			$cur_table_name = $table_name;
			$cur_fld_list = $sub_cfg['fld'];
                        $cur_fld_type_int_arr = array();
                        if(isset($sub_cfg['fld_type_int'])){
                            $cur_fld_type_int_arr = explode(',',(string)$sub_cfg['fld_type_int']);
                        }
			$cur_fld_arr = explode(',',$cur_fld_list);

			if (!isset($sub_cfg['relation_fld']) || (string)$sub_cfg['relation_fld'] == ''){
				$sql = "select {$cur_fld_list} from {$cur_table_name} where {$key_fld} in ($key_val_list)";
				$db_arr = $this->db->getAll($sql);
			}else{
				$where_fld_val_arr = array();
				$cur_relation_fld_arr = explode(',',$sub_cfg['relation_fld']);
				foreach($result as $sub_result){
					foreach($cur_relation_fld_arr as $s_fld){
						$s_fld_arr = explode('+',$s_fld);
						$tv = isset($sub_result[$s_fld_arr[1]])?trim((string)$sub_result[$s_fld_arr[1]]):'';
						if($tv == ''){
							continue;
						}
						if(!in_array($s_fld_arr[1],$cur_fld_type_int_arr)){
							$tv = strtoupper($tv);
						}
						$where_fld_val_arr[$s_fld_arr[1]][] = $tv;
					}
				}

				$wh_arr = array();
				$add_relation_fld_arr = array();
				foreach($cur_relation_fld_arr as $s_fld){
					$s_fld_arr = explode('+',$s_fld);
					if(in_array($s_fld_arr[1],$cur_fld_type_int_arr)){
						$cur_where_fld_val_list = join(',',array_unique((array)$where_fld_val_arr[$s_fld_arr[1]]));
					}else{
                                                $where_fld_val_arr[$s_fld_arr[1]] = isset($where_fld_val_arr[$s_fld_arr[1]])?$where_fld_val_arr[$s_fld_arr[1]]:array();
						$cur_where_fld_val_list = "'".join("','",array_unique((array)$where_fld_val_arr[$s_fld_arr[1]]))."'";
					}
					if($cur_where_fld_val_list != ''){
						$wh_arr[] = " {$s_fld_arr[0]} in ($cur_where_fld_val_list)";
					}
					$add_relation_fld_arr[] = $s_fld_arr[0];
				}
				$add_relation_fld_list  = join(',',$add_relation_fld_arr);
				$sql = "select {$add_relation_fld_list},{$cur_fld_list} from {$cur_table_name} where";
				$sql .= join(' and ',$wh_arr);
				if(isset($sub_cfg['wh'])){
					$sql .= $sub_cfg['wh'];
				}
				//echo $sql;
				$db_arr = array();
				if(count($wh_arr)>0){
					$db_arr = $this->db->getAll($sql);
				}
				//echo '<hr/>$sql<xmp>'.$sql.'</xmp>';
				//echo '<hr/>$db_arr11<xmp>'.var_export($db_arr,true).'</xmp>';
				if(count($db_arr) == 0 || empty($db_arr)){
					continue;
				}
			}

			if (empty($result)){
				$result = $db_arr;
			}else{
				$t_db_arr = array();
				foreach($db_arr as $sub_arr){
					$ks_val_arr = array();
					foreach($cur_relation_fld_arr as $s_fld){
						$ss_fld = explode('+',$s_fld);
						$ks_val_arr[] = strtolower($sub_arr[$ss_fld[0]]);
					}
					$ks = join(',',$ks_val_arr);
					$t_db_arr[$ks] = $sub_arr;
				}
				foreach($result as $k=>$sub_result){
					$ks_val_arr = array();
					foreach($cur_relation_fld_arr as $s_fld){
						$ss_fld = explode('+',$s_fld);
                                                $sub_result[$ss_fld[1]] = isset($sub_result[$ss_fld[1]])?$sub_result[$ss_fld[1]]:'';
						$ks_val_arr[] = strtolower($sub_result[$ss_fld[1]]);
					}
					$ks = join(',',$ks_val_arr);
					$find_row = isset($t_db_arr[$ks])?$t_db_arr[$ks]:'';
					if ($find_row){
						foreach($cur_fld_arr as $t_fld){
							$tt_arr = explode(' as ',$t_fld);
							$t_fld = array_pop($tt_arr);
							$result[$k][$t_fld] = $find_row[$t_fld];
						}					
					}
				}
			}
		}

		return $result;
	}

}