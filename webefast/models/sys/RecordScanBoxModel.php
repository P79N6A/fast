<?php
require_model('tb/TbModel');

class RecordScanBoxModel extends TbModel {
	public $dj_type;
	public $dj_type_map = array();
	public $cur_dj_info = array();

    public function __construct($dj_type = 'wbm_store_out') {
    	parent::__construct();
    	$this->dj_type = $dj_type;
    	$info = array(
    		'dj_type'=>'wbm_store_out',
    		'tbl'=>'wbm_store_out_record',
    		'tbl_notice'=>'wbm_notice_record',
    		'id_fld'=>'store_out_record_id',
    		'add_time_fld'=>'order_time',
    		'enotice_num_fld'=>'enotice_num',
    		'ys_fld'=>'is_store_out as ys_flag',//验收标识字段
			'name'=>'批发',
			'dj_notice_name'=>'批发通知单',
			'dj_name'=>'批发销货单',
			'ys_url'=> '?app_act=wbm/store_out_record/do_shift_out&is_scan_tag=1&id=',
			'dj_price'=>'trade_price',
    	);
    	$this->dj_type_map['wbm_store_out'] = $info;

    	$info = array(
    		'dj_type'=>'pur_purchaser',
    		'tbl'=>'pur_purchaser_record',
    		'tbl_notice'=>'pur_order_record',
    		'id_fld'=>'purchaser_record_id',
    		'add_time_fld'=>'is_add_time as order_time',
    		'enotice_num_fld'=>'notice_num as enotice_num',
    		'ys_fld'=>'is_check_and_accept as ys_flag',//验收标识字段
			'name'=>'采购',
			'dj_notice_name'=>'采购订单',
			'dj_name'=>'采购入库单',
			'ys_url'=> '?app_act=pur/purchase_record/do_checkin&is_scan_tag=1&id=',
			'dj_price'=>'purchase_price',
    	);
    	$this->dj_type_map['pur_purchaser'] = $info;

    	$this->cur_dj_info = $this->dj_type_map[$this->dj_type];
    }

	function create_box_task($record_code,$record_type,$relation_code,$store_code,$record_time,$create_user){
		$task_code  = 'BR'.preg_replace("/^[^0-9]+/","",$record_code);
		$arr = array('task_code'=>$task_code,'record_type'=>$record_type,
					 'relation_code'=>$relation_code,'store_code'=>$store_code,
					 'record_time'=>$record_time,'create_user'=>$create_user,'create_time'=>date('Y-m-d H:i:s')
					);
		$ret = M('b2b_box_task')->insert($arr);
		if ($ret != true){
			return $this->format_ret(-1,'','生成装箱任务单失败');
		}
		return $this->format_ret(1,$task_code);
	}

	function create_box_record($task_code,$store_code,$scan_user){
		$sql = "select MAX(box_order)  from b2b_box_record where task_code =:task_code";
		$count = ctx()->db->getOne($sql,array(':task_code'=>$task_code));
                $count = empty($count)?0:(int)$count;

		$count++;
                $d_code = substr($task_code,4);
		$box_code = 'B'.preg_replace("/^[^0-9]+/","",$d_code).str_pad($count,4,'0',STR_PAD_LEFT);
                $box_order = str_pad($count,4,'0',STR_PAD_LEFT);//箱序号
		$create_time = date('Y-m-d H:i:s');
		$arr = array('task_code'=>$task_code,
                                        'box_order'=>$box_order,
					 'record_code'=>$box_code,
					 'store_code'=>$store_code,
					 'scan_user'=>$scan_user,
					 'create_time'=>$create_time,
					);
		$ret = M('b2b_box_record')->insert($arr);
		if ($ret != true){
			return $this->format_ret(-1,'','生成装箱单失败');
		}
        $sql = "select id from b2b_box_record where record_code = '{$box_code}'";
        $box_id = ctx()->db->getOne($sql);
		return $this->format_ret(1,array('box_code'=>$box_code,'box_id'=>$box_id));
	}

	/**
	* 如果进行过普通扫描,则不能进行装箱扫描
	*/
    function view_scan($record_code){

	    $tbl = $this->cur_dj_info['tbl'];
	    $tbl_mx = $this->cur_dj_info['tbl'].'_detail';
	    $tbl_notice_mx = $this->cur_dj_info['tbl_notice'].'_detail';
	    $id_fld = $this->cur_dj_info['id_fld'];
	    $add_time_fld = $this->cur_dj_info['add_time_fld'];
	    $enotice_num_fld = $this->cur_dj_info['enotice_num_fld'];

        $sysuser = load_model('oms/SellRecordOptModel')->sys_user();
        $create_user = $sysuser['user_name'];

		$sql = "select task_code from b2b_box_task where record_type = :record_type and relation_code = :relation_code";
		$task_code = ctx()->db->getOne($sql,array('record_type'=>$this->dj_type,':relation_code'=>$record_code));
		$sql = "select count(*) from b2b_box_task where task_code = '{$task_code}' and is_change=1";
		$c = ctx()->db->getOne($sql);
		if ($c>0){
	        return $this->format_ret(-1,'','此装箱任务已经验收，无法使用装箱扫描!');
		}
        $sql = "select count(*) from {$tbl_mx} where record_code = :record_code and num>0";
        $scan_mx_count = ctx()->db->getOne($sql,array(':record_code'=>$record_code));

        if ($scan_mx_count>0 && empty($task_code)){
	        return $this->format_ret(-1,'','此单据已经 普通扫描 过，无法使用装箱扫描!');
        }

        $sql = "select {$id_fld} as record_id,record_code,store_code,{$add_time_fld},relation_code,record_time,distributor_code from {$tbl} where record_code = :record_code";
        $dj_info = ctx()->db->get_row($sql,array(':record_code'=>$record_code));

        if (empty($task_code)){
	        $ret = $this->create_box_task($record_code,$this->dj_type,$record_code,$dj_info['store_code'],$dj_info['record_time'],$create_user);
	        if ($ret['status']<1){
		        return $ret;
	        }
	        $task_code = $ret['data'];
        }

        //是否存在未完成扫描的装箱单，如果不存在新生成装箱单
        $sql = "select record_code from b2b_box_record where task_code = :task_code and is_check_and_accept = 0";
        $box_code = ctx()->db->getOne($sql,array(':task_code'=>$task_code));
        //echo '<hr/>$box_code<xmp>'.var_export($box_code,true).'</xmp>';die;
        if (empty($box_code)){
	        $ret = $this->create_box_record($task_code,$dj_info['store_code'],$create_user);
	        if ($ret['status']<1){
		        return $ret;
	        }
	        $box_code = $ret['data']['box_code'];
        }

        $sql = "select id from b2b_box_record where record_code = '{$box_code}'";
        $box_id = ctx()->db->getOne($sql);

        $sql = "select goods_code,spec1_code,spec2_code,sku,{$enotice_num_fld},num from {$tbl_mx} where record_code = :record_code";
		$db_mx = ctx()->db->get_all($sql,array(':record_code'=>$record_code));

		$mx_data = array();
		$must_scan_mx = array();
		$must_scan_mx_zero_num = array();//要扫描的明细，但完成单的数量为0,要去通知单取值
		$total_sl = 0;
		$total_scan_sl = 0;
		$dj_mx_map = array();
		foreach($db_mx as $sub_mx){
			$total_sl += $sub_mx['enotice_num'];
			$total_scan_sl += $sub_mx['num'];
			if ($sub_mx['num']>0){
                $sub_mx['num'] = (int)$sub_mx['num'];
                $sub_mx['enotice_num'] = (int)$sub_mx['enotice_num'];
				$mx_data[] = $sub_mx;
			}
			if ($sub_mx['enotice_num']>0){
				$must_scan_mx[$sub_mx['sku']] = array('enotice_num'=>(int)$sub_mx['enotice_num'],'num'=>(int)$sub_mx['num']);
			}else{
				$must_scan_mx_zero_num[$sub_mx['sku']] = array('enotice_num'=>0,'num'=>$sub_mx['num']);
			}
			$dj_mx_map[$sub_mx['sku']] = $sub_mx;
		}

        //如果存在已扫描数，就要读取已扫描的明细
        $scan_barcode_map = array();
        $scan_data = array();
        $scan_data_js = array();
        $box_scan_data = array();
        if ($total_scan_sl>0){
			$sql = "select goods_code,spec1_code,spec2_code,sku,sum(num) as num from b2b_box_record_detail where task_code = :task_code group by sku";
			$scan_data = ctx()->db->get_all($sql,array(':task_code'=>$task_code));
			$scan_data_js = load_model('util/ViewUtilModel')->get_map_arr($scan_data,'sku',0,'num');
			//box_scan_data

			$sql = "select goods_code,spec1_code,spec2_code,sku,num from b2b_box_record_detail where record_code = :box_code";
			$db_box_mx = ctx()->db->get_all($sql,array(':box_code'=>$box_code));

	        $box_scan_data = load_model('util/ViewUtilModel')->record_detail_append_goods_info($db_box_mx,1);
			$scan_barcode_map = load_model('util/ViewUtilModel')->get_map_arr($box_scan_data,'barcode',0,'sku');
			$sku_list = "'".join("','",$scan_barcode_map)."'";
			$sql = "select sku,barcode from goods_barcode_child where sku in({$sku_list})";
			$db_child_barcode = ctx()->db->get_all($sql);
			$child_barcode_arr = load_model('util/ViewUtilModel')->get_map_arr($db_child_barcode,'barcode',0,'sku');
			$scan_barcode_map = array_merge($scan_barcode_map,$child_barcode_arr);
			//echo '<hr/>$box_scan_data<xmp>'.var_export($box_scan_data,true).'</xmp>';
			//echo '<hr/>$scan_barcode_map<xmp>'.var_export($scan_barcode_map,true).'</xmp>';
        }
        if (empty($must_scan_mx) || !empty($must_scan_mx_zero_num)){
	        $sql = "select sku,num,finish_num from {$tbl_notice_mx} where record_code = :record_code";
	        $db_tzd_mx = ctx()->db->get_all($sql,array(':record_code'=>$dj_info['relation_code']));
	        $tzd_mx = array();
	        foreach($db_tzd_mx as $sub_mx){
		        $_sl = $sub_mx['num'] - $sub_mx['finish_num'];
                if($_sl<=0){
                    continue;
                }
		        $_sku = $sub_mx['sku'];
                $tzd_mx[$_sku] = $_sl;
	        }
            if(empty($must_scan_mx)){
                foreach($tzd_mx as $_tzd_sku=>$_tzd_sl){
                    $_sl = isset($must_scan_mx_zero_num[$_tzd_sku]) ? $must_scan_mx_zero_num[$_tzd_sku] : 0;
                    $must_scan_mx[$_tzd_sku] = array('enotice_num'=>$_tzd_sl,'num'=>$_sl);
                }
            }
            foreach($db_mx as $sub_mx){
                if ($sub_mx['enotice_num'] == 0){
                    $must_scan_mx[$sub_mx['sku']] = array('enotice_num'=>$tzd_mx[$sub_mx['sku']],'num'=>$sub_mx['num']);
                }
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
        $sql = "select id from api_weipinhuijit_store_out_record where store_out_record_no = :store_out_record_no";
        $id = $this->db->getOne($sql,array(':store_out_record_no' => $record_code));
        $connection_jit = false;
        if ($id){
        	$connection_jit = true;
        }
        $result = array();
        $result['total_sl'] = (int)$total_sl;
        $result['total_scan_sl'] = (int)$total_scan_sl;
        $result['dj_info'] = $dj_info;
        $result['dj_info']['task_code'] = $task_code;
        $result['dj_info']['box_code'] = $box_code;
        $result['dj_info']['box_id'] = $box_id;
        $result['dj_info']['connection_jit'] = $connection_jit;

        $result['dj_info']['dj_type'] = $this->cur_dj_info['dj_type'];
        $result['dj_info']['dj_type_name'] = $this->cur_dj_info['dj_name'];
        $result['dj_info']['dj_ys_url'] = $this->cur_dj_info['ys_url'].$dj_info['record_id'];

        $result['scan_data'] = $scan_data;
        $result['scan_data_js'] = $scan_data_js;
        $result['must_scan_mx'] = $must_scan_mx;

        $result['scan_barcode_map'] = $scan_barcode_map;
        $result['box_scan_data'] = $box_scan_data;

        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1','goods_spec2'));

		$result['base_spec1_name'] = $cfg['goods_spec1'];
		$result['base_spec2_name'] = $cfg['goods_spec2'];

		$sql = "select store_name from base_store where store_code = :store_code";
		$result['dj_info']['store_name'] = ctx()->db->getOne($sql,array(':store_code'=>$dj_info['store_code']));

		$sql = "select custom_name from base_custom where custom_code = :custom_code";
		$result['dj_info']['custom_name'] = ctx()->db->getOne($sql,array(':custom_code'=>$dj_info['distributor_code']));

        //echo '<hr/>scan_data<xmp>'.var_export($result['scan_data'],true).'</xmp>';
		return $this->format_ret(1,$result);
    }

    function save_scan($req){
	    $tbl = $this->cur_dj_info['tbl'];
	    $tbl_mx = $this->cur_dj_info['tbl'].'_detail';
	    $tbl_notice_mx = $this->cur_dj_info['tbl_notice'].'_detail';
	    $dj_notice_name = $this->cur_dj_info['dj_notice_name'];
	    $dj_name = $this->cur_dj_info['dj_name'];
	    $enotice_num_fld = $this->cur_dj_info['enotice_num_fld'];
	    $id_fld = $this->cur_dj_info['id_fld'];
	    $ys_fld = $this->cur_dj_info['ys_fld'];

		$record_code = $req['record_code'];//批发采购单号
		$task_code = $req['task_code'];//装箱任务号
		$box_code = $req['box_code'];//箱号

		$tzd_code = $req['tzd_code'];
		$record_type = $req['dj_type'];
		$scan_barcode = $req['scan_barcode'];
		$barcode_is_exist = $req['barcode_is_exist'];

		//echo '<hr/>$req<xmp>'.var_export($req,true).'</xmp>';
		if(empty($record_code) || empty($task_code) || empty($box_code) || empty($record_type) || empty($scan_barcode)){
			return $this->format_ret(-1,'','单号/单据类型/扫描条码 数据缺失');
		}
		$ret = $this->parse_scan_barcode($scan_barcode);
		if ($ret['status']<0){
			return $ret;
		}
		$sku = $ret['data']['sku'];
		$goods_code = $ret['data']['goods_code'];

		//如果没有关联的通知单，直接认完成单
		if (empty($tzd_code)){
			$dj_scan_row = array();
			$dj_scan_row['goods_code'] = $goods_code;
			$dj_scan_row['spec1_code'] = $ret['data']['spec1_code'];
			$dj_scan_row['spec2_code'] = $ret['data']['spec2_code'];
			$dj_scan_row['enotice_num'] = 0;
			$dj_scan_row['num'] = 0;
			$dj_scan_row['goods_code'] = $goods_code;
// 			$sql = "select goods_code,spec1_code,spec2_code,{$enotice_num_fld},num from {$tbl_mx} where record_code = :record_code and sku = :sku";
// 			$dj_scan_row = ctx()->db->get_row($sql,array(':record_code'=>$record_code,':sku'=>$sku));
// 			if (empty($dj_scan_row) && $this->dj_type != 'wbm_store_out'){
// 		       return $this->format_ret(-1,'',$dj_name.'中无此商品');
// 			}
// 			$surplus_num = (int)$dj_scan_row['enotice_num'] - (int)$dj_scan_row['num'];
// 			if ($surplus_num <= 0 && $this->dj_type == 'wbm_store_out'){
// 		       return $this->format_ret(-1,'',$dj_name.'中此商品已出库完成');
// 			}
		}else{
			$sql = "select goods_code,spec1_code,spec2_code,enotice_num,num from wbm_store_out_record_detail where record_code = :record_code and sku = :sku";
			$dj_scan_row = ctx()->db->get_row($sql,array(':record_code'=>$record_code,':sku'=>$sku));
			if (empty($dj_scan_row)){
		       return $this->format_ret(-1,'',$dj_notice_name.'中无此商品');
			}
			$surplus_num = (int)$dj_scan_row['enotice_num'];
			if ($surplus_num <= 0 && $this->dj_type == 'wbm_store_out'){
		       return $this->format_ret(-1,'',$dj_notice_name.'中此商品已出库完成');
			}
		}

		$sql = "select {$id_fld} as record_id,{$ys_fld},rebate,store_code from {$tbl} where record_code = :record_code";
		$dj_row = ctx()->db->get_row($sql,array(':record_code'=>$record_code));
		if ($dj_row['ys_flag']>0){
	       return $this->format_ret(-1,'',$dj_name.'已验收');
		}
		$rebate = empty($dj_row['rebate']) ? 1 : $dj_row['rebate'];

		$sql = "select record_code,is_check_and_accept from b2b_box_record where task_code = :task_code and record_code = :record_code";
		$sql_params = array(':task_code'=>$task_code,':record_code'=>$box_code);
		$box_row = ctx()->db->get_row($sql,$sql_params);
		if (empty($box_row)){
	       return $this->format_ret(-1,'','扫描出错，装箱单'.$box_code.'不存在');
		}
		if ($box_row['is_check_and_accept']>0){
	       return $this->format_ret(-1,'','装箱单'.$box_row['record_code'].'已验收');
		}
		//echo '<hr/>$box_row<xmp>'.var_export($box_row,true).'</xmp>';die;

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

		$this->begin_trans();
		$insert_data = array(
			'pid'=>$dj_row['record_id'],
			'goods_code'=>$dj_scan_row['goods_code'],
			'spec1_code'=>$dj_scan_row['spec1_code'],
			'spec2_code'=>$dj_scan_row['spec2_code'],
			'record_code'=>$record_code,
			'sku'=>$sku,
			'num'=>1,
			'price'=>$price,
			'rebate'=>$rebate,
			'money'=>$price * 1 * $rebate,
		);
		//echo '<hr/>$insert_data<xmp>'.var_export($insert_data,true).'</xmp>';die;
        $update_str = "num = num +VALUES(num),money = num*price*rebate";
        $ret = $this->insert_multi_duplicate($tbl_mx, array($insert_data), $update_str);
        if ($ret['status']<0){
	       $this->rollback();
	       return $this->format_ret(-1,'','保存扫描数据失败');
        }

        //保存装箱商品明细
		$ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
		if ($ret_lof['status']<0){
			$this->rollback();
			return $ret_lof;
		}
		$lof_no = $ret_lof['data']['lof_no'];
		$production_date = $ret_lof['data']['production_date'];

		$insert_data = array(
			'task_code'=>$task_code,
			'record_code'=>$box_code,
			'goods_code'=>$dj_scan_row['goods_code'],
			'spec1_code'=>$dj_scan_row['spec1_code'],
			'spec2_code'=>$dj_scan_row['spec2_code'],
			'sku'=>$sku,
			'lof_no'=>$lof_no,
			'production_date'=>$production_date,
			'num'=>1,
			'create_time'=>date('Y-m-d H:i:s'),
		);
		//echo '<hr/>$insert_data<xmp>'.var_export($insert_data,true).'</xmp>';die;
        $update_str = "num = num +VALUES(num)";
        $ret = $this->insert_multi_duplicate('b2b_box_record_detail', array($insert_data), $update_str);
        if ($ret['status']<0){
	       $this->rollback();
	       return $this->format_ret(-1,'','保存装箱商品明细失败');
        }

		//如果是采购入库单，要维护 b2b_lof_datail 表
		if ($this->dj_type == 'pur_purchaser'||$this->dj_type == 'wbm_store_out'){
                        $dj_type = 'purchase';
                                if($this->dj_type == 'wbm_store_out'){
                                    $dj_type = 'wbm_store_out';
                                }
			$ret = $this->update_lof($record_code,$dj_row,$dj_scan_row,$sku,$lof_no,$production_date,$dj_type);
			if ($ret['status']<0){
	       		$this->rollback();
				return $ret;
			}
		}

        $sql = "select {$enotice_num_fld},num from {$tbl_mx} where record_code = :record_code and sku = :sku";
        $_scan_after_row = ctx()->db->get_row($sql,array(':record_code'=>$record_code,':sku'=>$sku));
        if (empty($_scan_after_row)){
	       $this->rollback();
	       return $this->format_ret(-1,'','单据不存在sku:'.$sku);
        }
        $_scan_after_num = (int)$_scan_after_row['num'];
        $_scan_after_notice_num = (int)$_scan_after_row['enotice_num'];

		//如果没有关联的通知单，直接认完成单
		$is_wh_num = 0;

		if ($this->dj_type == 'wbm_store_out'){
                    $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未出库', 'action_name' => '扫描', 'module' => "store_out_record", 'pid' => $dj_row['record_id'],'action_note' => '扫描条码：'.$scan_barcode);
                    load_model('pur/PurStmLogModel')->insert($log);
			if (empty($tzd_code)){
				if ($_scan_after_num>$_scan_after_notice_num){
					$_up_num = $_scan_after_num;
					//$_scan_after_num = $_scan_after_notice_num;
					$is_wh_num = 1;
				}
//				$lof_data[] = array(
//						'goods_code'=>$dj_scan_row['goods_code'],
//						'spec1_code'=>$dj_scan_row['spec1_code'],
//						'spec2_code'=>$dj_scan_row['spec2_code'],
//						'sku'=>$sku,
//						'num'=>1,
//				);
//				$pid = $dj_row['record_id'];
//				$store_code= $dj_row['store_code'];
//				$ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid,$store_code,'wbm_store_out',$lof_data);

			}else{
		        if ($_scan_after_num>$surplus_num){
					$_up_num = $surplus_num;
					$_scan_after_num = $surplus_num;
					$is_wh_num = 1;
		        }
			}
		}

		if ($is_wh_num == 1 && $this->dj_type == 'wbm_store_out'){
			$sql = "update {$tbl_mx} set num = {$_up_num},money = {$_up_num}*price*rebate where record_code = :record_code and sku = :sku";
			$ret = ctx()->db->query($sql,array(':record_code'=>$record_code,':sku'=>$sku));
			if ($ret !== true){
			   $this->rollback();
		       return $this->format_ret(-1,'','维护扫描数量失败');
			}
//			$sql = "update b2b_lof_datail set num = {$_up_num} where order_code = :record_code and sku = :sku and order_type = 'wbm_store_out'";
//			$ret = ctx()->db->query($sql,array(':record_code'=>$record_code,':sku'=>$sku));
//			if ($ret !== true){
//			   $this->rollback();
//		       return $this->format_ret(-1,'','维护扫描批次数量失败');
//			}

			if (!empty($tzd_code)){
				$this->rollback();
				return $this->format_ret(-1,'','此商品数量已满');
			}
		}
		$this->commit();
        $result = array();
        if ($barcode_is_exist == -1){
	        $goods_info = load_model('util/ViewUtilModel')->record_detail_append_goods_info(array(array('sku'=>$sku)),1,1);
	        $result = $goods_info[0];
        }
        $sql = "select num from b2b_box_record_detail where record_code = :box_code and sku =:sku";
        $box_num = ctx()->db->getOne($sql,array(':box_code'=>$box_code,':sku'=>$sku));

        //回写主表数量金额
        $ret = load_model('wbm/StoreOutRecordDetailModel')->mainWriteBack($dj_row['record_id']);

        $result['num'] = $_scan_after_num;
        $result['box_num'] = $box_num;
        $result['sku'] = $sku;
        $result['scan_barcode'] = $scan_barcode;
        $result['barcode_is_exist'] = $barcode_is_exist;
        //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';
		return $this->format_ret(1,$result);
    }

    function clean_scan($req){
		$tbl = $this->cur_dj_info['tbl'];
    	$tbl_mx = $this->cur_dj_info['tbl'].'_detail';
    	$tbl_notice_mx = $this->cur_dj_info['tbl_notice'].'_detail';
    	$dj_notice_name = $this->cur_dj_info['dj_notice_name'];
    	$dj_name = $this->cur_dj_info['dj_name'];
    	$enotice_num_fld = $this->cur_dj_info['enotice_num_fld'];
    	$id_fld = $this->cur_dj_info['id_fld'];
    	$ys_fld = $this->cur_dj_info['ys_fld'];

    	$record_code = $req['record_code'];//批发采购单号
    	$task_code = $req['task_code'];//装箱任务号
    	$box_code = $req['box_code'];//箱号

    	$tzd_code = $req['tzd_code'];
    	$record_type = $req['dj_type'];
    	//装箱明细
    	$sql = "select goods_code,spec1_code,spec2_code,sku,num from b2b_box_record_detail where record_code = :box_code and task_code = :task_code";
    	$box_ret = ctx()->db->get_all($sql,array(':box_code'=>$box_code,':task_code'=>$task_code));
    	//单据明细
    	$sql = "select {$enotice_num_fld},num,sku from {$tbl_mx} where record_code = :record_code ";
    	$record_mx_ret = ctx()->db->get_all($sql,array(':record_code'=>$record_code));
    	$record_mx_arr = array();
    	foreach ($record_mx_ret as $mx_row) {
    		$record_mx_arr[$mx_row['sku']] = $mx_row;
    	}
    	$up_data = array();
    	foreach ($box_ret as $box_row) {
    		if (!isset($record_mx_arr[$box_row['sku']])){
    			continue;
    		}
    		$record_row = $record_mx_arr[$box_row['sku']];
    		$up_num = $record_row['num']-$box_row['num'];
    		if ($up_num < 0){
    			$up_num = 0;
    		}
    		$price = $record_row['price'];
    		$rebate = $record_row['rebate'];
    		$money = $price * $up_num * $rebate;
    		$up_row = array(
    				'record_code'=>$record_code,
    				'sku'=>$box_row['sku'],
    				'num'=>$up_num,
    				'money'=>$money,
    		);
    		$up_data[] = $up_row;
    	}
    	$this->begin_trans();

    	$update_str = "num = VALUES(num),money = VALUES(money)";
    	$ret = $this->insert_multi_duplicate($tbl_mx, $up_data, $update_str);
    	if ($ret['status']<0){
    		$this->rollback();
    		return $this->format_ret(-1,'','清除扫描数据失败');
    	}


    	//删除装箱明细数据
    	$sql = "delete from b2b_box_record_detail where record_code = :box_code and task_code = :task_code";
    	ctx()->db->query($sql,array(':box_code'=>$box_code,':task_code'=>$task_code));

        //`order_type`,`order_code`,`sku`,`lof_no`
        $sql_up_lof = "update b2b_lof_datail set num=0 where order_code=:order_code AND order_type=:order_type";
        $sql_up_value = array(':order_code'=>$record_code,':order_type'=>$record_type);
        $this->db->query($sql_up_lof,$sql_up_value);


        $sql_lof = "INSERT INTO  b2b_lof_datail(order_type,order_code,sku,lof_no,num )
                select  '{$record_type}' as order_type,'{$record_code}' as order_code,sku,lof_no,sum(num) as num
                from  b2b_box_record_detail where task_code='{$task_code}' GROUP BY sku,lof_no
              ON DUPLICATE KEY UPDATE num = VALUES(num) ";
        $this->query($sql_lof);

		$sql = "SELECT store_out_record_id FROM wbm_store_out_record WHERE record_code = :record_code";
		$id = $this->db->get_row($sql,array(':record_code'=>$record_code));

		if ($this->dj_type == 'wbm_store_out'){
			//日志记录
			$log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '清除装箱扫描记录', 'action_note' => "清除箱号：{$box_code}记录", 'module' => "store_out_record", 'pid' => $id['store_out_record_id']);
			load_model('pur/PurStmLogModel')->insert($log);
		}
    	$this->commit();
    	return $this->format_ret(1,'');
    }

    /**
     * 扫描识别条码信息
     * @param string $i_barcode 商品条形码
     * @return array 条码数据
     */
    function parse_scan_barcode($i_barcode) {
        $sku_data = load_model('prm/SkuModel')->convert_scan_barcode($i_barcode, 1);

        if (empty($sku_data)) {
            return $this->format_ret(-1, '', '条码无效');
        }

        $result = array('sku' => $sku_data['sku'], 'goods_code' => $sku_data['goods_code'], 'spec1_code' => $sku_data['spec1_code'], 'spec2_code' => $sku_data['spec2_code']);
        return $this->format_ret(1, $result);
    }

    function update_lof($record_code,$dj_row,$dj_scan_row,$sku,$lof_no,$production_date,$order_type = 'purchase'){
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
	    return $this->format_ret(1,$insert_data);
    }

	//装箱单验收
	function b2b_box_record_ys($task_code,$box_code){
		$sql = "select count(*) from b2b_box_record_detail where record_code = :box_code";
		//echo '<hr/>box_code<xmp>'.var_export($box_code,true).'</xmp>';
		$count = ctx()->db->getOne($sql,array(':box_code'=>$box_code));
		if ($count == 0){
                    return $this->format_ret(-1,'',$box_code.'装箱单商品明细为空，无法验收');
		}
		$sql = "select is_check_and_accept from b2b_box_record where record_code = :box_code";
		$is_check_and_accept = ctx()->db->getOne($sql,array(':box_code'=>$box_code));
		if ($is_check_and_accept == 1){
	       return $this->format_ret(-1,'',$box_code.'装箱单已经验收');
		}
		$sql = "update b2b_box_record set is_check_and_accept = 1 where record_code = :box_code and is_check_and_accept = 0";
		$ret = ctx()->db->query($sql,array(':box_code'=>$box_code));
		if ($ret != true){
	       return $this->format_ret(-1,'',$box_code.'装箱单验收失败');
		}
		$aff_row = ctx()->db->affected_rows();
		if ($aff_row == 0){
	       return $this->format_ret(-1,'',$box_code.'装箱单验收失败,数据未刷新');
		}
		$sql = "select sum(num) from b2b_box_record_detail where record_code = :box_code";
		$box_total_sl = ctx()->db->getOne($sql,array(':box_code'=>$box_code));

		$sql = "update b2b_box_record set num = :num where record_code = :box_code";
		ctx()->db->query($sql,array(':num'=>$box_total_sl,':box_code'=>$box_code));

		$sql = "select sum(num) from b2b_box_record where task_code = :task_code";
		$task_total_sl = ctx()->db->getOne($sql,array(':task_code'=>$task_code));

		$sql = "update b2b_box_task set num = :num where task_code = :task_code";
		ctx()->db->query($sql,array(':num'=>$task_total_sl,':task_code'=>$task_code));

		return $this->format_ret(1);
	}

	//装箱任务验收并转换
	function b2b_box_task_ys($task_code){
		//自动删除空的装箱单
		$sql = "select t1.record_code from b2b_box_record t1 left join b2b_box_record_detail t2 on t1.record_code = t2.record_code where t1.task_code = :task_code and t2.record_code is null";
		$empty_record_code_arr = ctx()->db->get_all_col($sql,array(':task_code'=>$task_code));
		if (!empty($empty_record_code_arr)){
			$empty_record_code_list = "'".join("','",$empty_record_code_arr)."'";
			$sql = "delete from b2b_box_record where task_code = :task_code and record_code in({$empty_record_code_list})";
			//echo $sql;die;
			ctx()->db->query($sql,array(':task_code'=>$task_code));
		}
// 		$sql = "select count(*) from b2b_box_record where task_code = :task_code and is_check_and_accept = 0";
// 		$c = ctx()->db->getOne($sql,array(':task_code'=>$task_code));
// 		if ($c>0){
// 	       return $this->format_ret(-1,'',$task_code.'存在未验收的装箱单');
// 		}
		//die;

		$sql = "select relation_code,is_check_and_accept,is_change from b2b_box_task where task_code = :task_code";
		$task_info = ctx()->db->get_row($sql,array(':task_code'=>$task_code));
		$record_code = $task_info['relation_code'];
		if ($task_info['is_check_and_accept'] != 0 || $task_info['is_change'] != 0){
	       return $this->format_ret(-1,'',$task_code.'装箱任务已验收');
		}

		$sql = "select record_code,sku,sum(num) as num from b2b_box_record_detail where task_code = :task_code";
		$box_mx = ctx()->db->get_all($sql,array(':task_code'=>$task_code));
		if (empty($box_mx)){
	       return $this->format_ret(-1,'','没有找到对应的装箱明细');
		}

		//根据装箱明细，刷新原单据明细
		$record_mx_tbl = $this->cur_dj_info['tbl']."_detail";
        $update_str = "num = num +VALUES(num)";
        $ret = $this->insert_multi_duplicate($record_mx_tbl, $box_mx, $update_str);
        if ($ret['status']<0){
	       return $this->format_ret(-1,'',$record_code.' 刷新单据明细数据失败');
        }
        $sql = "update {$record_mx_tbl} set money = price * num where record_code = :record_code";
        ctx()->db->query($sql,array(':record_code'=>$record_code));

		$record_tbl = $this->cur_dj_info['tbl'];
		$ys_fld = $this->cur_dj_info['ys_fld'];
		$id_fld = $this->cur_dj_info['id_fld'];
		$sql = "select {$id_fld},{$ys_fld} from {$record_tbl} where record_code = :record_code";
		$record_tbl_row = ctx()->db->get_row($sql,array(':record_code'=>$record_code));
		if (empty($record_tbl_row)){
	       return $this->format_ret(-1,'',$record_code.' 单据不存在');
		}
		if ($record_tbl_row['ys_flag']!=0){
	       return $this->format_ret(-1,'',$record_code.' 单据已验收');
		}
		//$record_tbl_id = $record_tbl_row[$id_fld];

		$this->begin_trans();
		$ret = load_model('wbm/StoreOutRecordModel')->do_sure_and_shift_out($record_code,0);

		if (empty($ret['status']) || $ret['status']<0){
			$this->rollback();
			return $ret;
		}

		$sql = "update b2b_box_task set is_check_and_accept = 1,is_change=1 where task_code = :task_code and is_check_and_accept = 0 and is_change = 0";
		$ret = ctx()->db->query($sql,array(':task_code'=>$task_code));
		if ($ret != true){
		   $this->rollback();
	       return $this->format_ret(-1,'',$task_code.'装箱任务验收失败');
		}
		$aff_row = ctx()->db->affected_rows();
		if ($aff_row == 0){
		   $this->rollback();
	       return $this->format_ret(-1,'',$task_code.'装箱任务验收失败,数据未刷新');
		}
		$this->commit();
		return $this->format_ret(1);
	}

	function get_scan_mode($record_code,$record_type){
		$scan_mode = "select";
		$sql = "select count(*) from b2b_box_task where relation_code = :record_code and record_type = :record_type";
		$c = ctx()->db->getOne($sql,array(':record_code'=>$record_code,':record_type'=>$record_type));
		if ($c>0){
			$scan_mode = 'scan_box';
		}else{
			$tbl_mx = $record_type.'_record_detail';
			$sql = "select count(*) from {$tbl_mx} where num>0 and record_code = :record_code";
			$c = ctx()->db->getOne($sql,array(':record_code'=>$record_code));
			if ($c>0){
				$scan_mode = 'scan';
			}
		}
		return $this->format_ret(1,$scan_mode);
	}

	//取消批发单时,自动取消空的装箱任务，如果装箱任务不为空，不能取消批发单
	function cancel_box_task($wbm_record_code){
		$sql = "select task_code from b2b_box_task where relation_code = :record_code and record_type = :record_type";
		$task_code = ctx()->db->getOne($sql,array(':record_code'=>$wbm_record_code,':record_type'=>$this->dj_type));
		if (empty($task_code)){
			return $this->format_ret(1);
		}else{
			$sql = "select count(*) from b2b_box_record_detail where task_code = :task_code";
			$c = ctx()->db->getOne($sql,array(':task_code'=>$task_code));
			if ($c>0){
				return $this->format_ret(-1,'','装箱任务'.$task_code.'进行中，无法取消单据');
			}else{
				$sql = "delete from b2b_box_task where task_code = :task_code";
				ctx()->db->query($sql,array(':task_code'=>$task_code));
				$sql = "delete from b2b_box_record where task_code = :task_code";
				ctx()->db->query($sql,array(':task_code'=>$task_code));
				return $this->format_ret(1);
			}
		}
	}
        //修改扫描商品数量
        function update_goods_scan_num($filter) {
			$box_code = $filter['box_code'];//装箱任务号
            $task_code = $filter['task_code'];//箱号
            $record_code = $filter['record_code'];//批发单号
            $tbl_mx = $this->cur_dj_info['tbl'].'_detail';//批发明细表

            //查询批发销货明细单通知数量
            $sql = "SELECT enotice_num FROM {$tbl_mx} WHERE record_code = :record_code AND sku = :sku;";
            $enotice_num = $this->db->get_col($sql,array(':record_code'=>$record_code,':sku'=>$filter['sku']));

            //查询装箱单明细单个商品总数量
            $sql = "SELECT sum(num) num FROM b2b_box_record_detail WHERE task_code = :task_code AND sku = :sku AND record_code <> :box_code;";
            $num = $this->db->get_col($sql,array(':task_code'=>$task_code, ':sku'=>$filter['sku'] , ':box_code'=>$box_code));

            //判断有没有关联单
            $sql = "SELECT store_out_record_id AS record_id, relation_code,store_out_record_id FROM wbm_store_out_record WHERE record_code = :record_code";
            $relation_code = $this->db->get_row($sql,array(':record_code'=>$record_code));

            //判断剩余扫描数量是否大于剩余通知数量
            if($relation_code['relation_code']) {
                $surplus_num = $enotice_num[0] - $num[0] - $filter['scan_num'];
                if($surplus_num < 0) {
                    return $this->format_ret(-1,'','扫描数量大于通知数量');
                }
            }
			//获取之前数量记录
			$sql = "SELECT num FROM b2b_box_record_detail WHERE record_code = :box_code AND task_code = :task_code AND sku = :sku;";
			$old_num = $this->db->get_row($sql,array(':box_code'=>$box_code, ':task_code'=>$task_code, 'sku'=>$filter['sku']));
            //修改装箱单明细数量
            $sql = "UPDATE b2b_box_record_detail SET num = :num WHERE record_code = :box_code AND task_code = :task_code AND sku = :sku;";
            $ret = $this->query($sql,array(':num'=>$filter['scan_num'], ':box_code'=>$box_code, ':task_code'=>$task_code, 'sku'=>$filter['sku']));
            if($ret['status'] != 1){
                return $ret;
            }

            //查询装箱单明细单个商品总数量
            $sql = "SELECT sum(num) FROM b2b_box_record_detail WHERE task_code = :task_code AND sku = :sku;";
            $scan_num = $this->db->get_col($sql,array(':task_code'=>$task_code, ':sku'=>$filter['sku']));
            //修改批发销货单明细表完成数量
            $sql = "UPDATE {$tbl_mx} SET num = :scan_num WHERE record_code = :record_code AND sku = :sku;";
            $ret = $this->query($sql,array(':scan_num'=>$scan_num, ':record_code'=>$record_code, ':sku'=>$filter['sku']));
            if($ret['status'] != 1){
                return $ret;
            }

            //修改批次表数量
            $sql = "UPDATE b2b_lof_datail SET num = :scan_num WHERE order_code = :record_code AND sku = :sku";
            $ret = $this->query($sql,array(':scan_num'=>$scan_num, ':record_code'=>$record_code, ':sku'=>$filter['sku']));
            if($ret['status'] != 1){
                return $ret;
            }

			$sql="select barcode from goods_barcode where sku='{$filter['sku']}' ";
			$barcode=$this->db->getOne($sql);
			if ($this->dj_type == 'wbm_store_out'){
                //回写主表数量金额
                $ret = load_model('wbm/StoreOutRecordDetailModel')->mainWriteBack($relation_code['record_id']);
				//日志记录
				$log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '修改装箱扫描数量', 'action_note' => "箱号为{$box_code}的条码：{$barcode}数量由{$old_num['num']}改为{$filter['scan_num']}", 'module' => "store_out_record", 'pid' => $relation_code['store_out_record_id']);
				load_model('pur/PurStmLogModel')->insert($log);
			}

            return $this->format_ret(1,$scan_num,'更新完成');
        }

	//删除无商品明细的装箱单
	function cancel_box_record($record_code){
		$sql = "select count(*) from b2b_box_record_detail where record_code = :record_code";
		$c = ctx()->db->getOne($sql,array(':record_code'=>$record_code));

		if ($c>0){
			return $this->format_ret(-1,'','装箱单'.$record_code.'存在商品明细无法删除');
		}else{
			$sql = "select task_code from b2b_box_record where record_code = :record_code";
			$task_code=ctx()->db->getOne($sql,array(':record_code'=>$record_code));

			$sql = "delete from b2b_box_record where record_code = :record_code";
			ctx()->db->query($sql,array(':record_code'=>$record_code));
			//删除为空的任务号
			$sql = "select count(*) from b2b_box_record where task_code = :task_code";
			$num=ctx()->db->getOne($sql,array(':task_code'=>$task_code));
			if($num==0){
				$sql = "delete from b2b_box_task where task_code = :task_code";
				ctx()->db->query($sql,array(':task_code'=>$task_code));
			}
			return $this->format_ret(1);
		}

	}
}
