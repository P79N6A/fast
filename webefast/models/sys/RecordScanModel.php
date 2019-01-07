<?php
require_model('tb/TbModel');

class RecordScanModel extends TbModel {
	public $dj_type;
	public $dj_type_map = array();
	public $cur_dj_info = array();

    public function __construct($dj_type = 'wbm_store_out') {
    	parent::__construct();
    	$this->dj_type = $dj_type;
    	$info = array(
            'dj_type' => 'wbm_store_out',
            'tbl' => 'wbm_store_out_record',
            'tbl_notice' => 'wbm_notice_record',
            'id_fld' => 'store_out_record_id',
            'add_time_fld' => 'order_time',
            'enotice_num_fld' => 'enotice_num',
            'ys_fld' => 'is_store_out as ys_flag', //验收标识字段
            'name' => '批发',
            'dj_notice_name' => '批发通知单',
            'dj_name' => '批发销货单',
            //'update_scan_num_url' => '?app_act=pur/store_out_record/update_scan_num&is_scan_tag=1&record_code=',
            'update_scan_num_url' => '?app_act=common/record_scan/update_goods_scan_num&record_code=',
            'ys_url' => '?app_act=wbm/store_out_record/do_shift_out&is_scan_tag=1&record_code=',
            'dj_price' => 'trade_price',
        );
        $this->dj_type_map['wbm_store_out'] = $info;
        //采购入库单
    	$info = array(
    		'dj_type'=>'purchase',
    		'tbl'=>'pur_purchaser_record',
    		'tbl_notice'=>'pur_order_record',
    		'id_fld'=>'purchaser_record_id',
    		'add_time_fld'=>'order_time',
    		'enotice_num_fld'=>'notice_num as enotice_num',
    		'ys_fld'=>'is_check_and_accept as ys_flag',//验收标识字段
			'name'=>'采购',
			'dj_notice_name'=>'采购订单',
			'dj_name'=>'采购入库单',
			'ys_url'=> '?app_act=pur/purchase_record/do_checkin&is_scan_tag=1&record_code=',
    		'update_scan_num_url' => '?app_act=pur/purchase_record/update_scan_num&is_scan_tag=1&record_code=',
			'dj_price'=>'purchase_price',
    	);
    	$this->dj_type_map['purchase'] = $info;

        //采购通知单
        $info = array(
            'dj_type'=>'pur_notice',
            'tbl'=>'pur_order_record',
            'tbl_notice'=>'pur_planned_record',
            'id_fld'=>'order_record_id',
            'add_time_fld'=>'order_time',
            'enotice_num_fld'=>'0 as enotice_num',
            'ys_fld'=>'is_check as ys_flag',//验收标识字段
            'name'=>'采购通知单',
            'dj_notice_name'=>'采购订单',
            'dj_name'=>'采购通知单',
            'ys_url'=> '?app_act=pur/order_record/scan_do_check&record_code=',//验收
            'update_scan_num_url' => '?app_act=pur/order_record/update_scan_num&record_code=',//修改扫面数量
            'dj_price'=>'purchase_price',
        );
        $this->dj_type_map['pur_notice'] = $info;

        //采购退货通知单
        $info = array(
            'dj_type'=>'pur_return_notice',
            'tbl'=>'pur_return_notice_record',
            'tbl_notice'=>'pur_planned_record',
            'id_fld'=>'return_notice_record_id',
            'add_time_fld'=>'order_time',
            'enotice_num_fld'=>'0 as enotice_num',
            'ys_fld'=>'is_sure as ys_flag',//验收标识字段
            'name'=>'采购退货通知单',
            'dj_notice_name'=>'采购退货通知单',
            'dj_name'=>'采购退货通知单',
            'ys_url'=> '?app_act=pur/return_notice_record/scan_do_sure&record_code=',//验收
            'update_scan_num_url' => '?app_act=pur/return_notice_record/update_scan_num&record_code=',//修改扫面数量
            'dj_price'=>'purchase_price',
        );
        $this->dj_type_map['pur_return_notice'] = $info;

        //批发销货通知单
        $info = array(
            'dj_type'=>'wbm_notice',
            'tbl'=>'wbm_notice_record',
            'tbl_notice'=>'wbm_notice_record',
            'id_fld'=>'notice_record_id',
            'add_time_fld'=>'order_time',
            'enotice_num_fld'=>'0 as enotice_num',
            'ys_fld'=>'is_sure as ys_flag',//验收标识字段
            'name'=>'批发销货通知单',
            'dj_notice_name'=>'批发销货通知单',
            'dj_name'=>'批发销货通知单',
            'ys_url'=> '?app_act=wbm/notice_record/scan_do_check&record_code=',//验收
            'update_scan_num_url' => '?app_act=wbm/notice_record/update_scan_num&record_code=',//修改扫面数量
            'dj_price'=>'trade_price',//批发价
        );
        $this->dj_type_map['wbm_notice'] = $info;

    	//采购退
    	$info = array(
    			'dj_type'=>'pur_return',
    			'tbl'=>'pur_return_record',
    			'tbl_notice'=>'pur_return_notice_record',
    			'id_fld'=>'return_record_id',
    			'add_time_fld'=>'order_time',
    			'enotice_num_fld'=>'enotice_num',
    			'ys_fld'=>'is_store_out as ys_flag',//验收标识字段
    			'name'=>'采购',
    			'dj_notice_name'=>'采购退货通知单',
    			'dj_name'=>'采购退货单',
    			'update_scan_num_url' => '?app_act=pur/return_record/update_scan_num&is_scan_tag=1&record_code=',
    			'ys_url'=> '?app_act=pur/return_record/do_checkout&is_scan_tag=1&record_code=',
    			'dj_price'=>'purchase_price',
    	);
    	$this->dj_type_map['pur_return'] = $info;
    	//批发退
    	$info = array(
    			'dj_type'=>'wbm_return',
    			'tbl'=>'wbm_return_record',
    			'tbl_notice'=>'wbm_return_notice_record',
    			'id_fld'=>'return_record_id',
    			'add_time_fld'=>'order_time',
    			'enotice_num_fld'=>'enotice_num',
    			'ys_fld'=>'is_store_in as ys_flag',//验收标识字段
    			'name'=>'批发退货单',
    			'dj_notice_name'=>'批发退货通知单',
    			'dj_name'=>'批发退货单',
    			'ys_url'=> '?app_act=wbm/return_record/do_shift_in&is_scan_tag=1&record_code=',
    			'update_scan_num_url' => '?app_act=wbm/return_record/update_scan_num&is_scan_tag=1&record_code=',
                        'dj_price'=>'trade_price',
    	);
    	$this->dj_type_map['wbm_return'] = $info;
    	$this->cur_dj_info = $this->dj_type_map[$this->dj_type];
    }

    function view_scan($record_code){
        $sql = "select count(*) from b2b_box_task where relation_code = :record_code and record_type =:record_type";
        $c = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => $this->dj_type));
        if ($c > 0) {
            return $this->format_ret(-1, '', '此单据已经用过装箱扫描功能，无法再用普通扫描');
        }
        $tbl = $this->cur_dj_info['tbl'];
        $tbl_mx = $this->cur_dj_info['tbl'] . '_detail';
        if ($this->dj_type == 'wbm_return') {
            $tbl_notice_mx = 'wbm_return_notice_detail_record';
        } else {
            $tbl_notice_mx = $this->cur_dj_info['tbl_notice'] . '_detail';
        }

        $id_fld = $this->cur_dj_info['id_fld'];
        $add_time_fld = $this->cur_dj_info['add_time_fld'];
        $enotice_num_fld = $this->cur_dj_info['enotice_num_fld'];
        $sql = "select {$id_fld} as record_id,record_code,store_code,{$add_time_fld},relation_code from {$tbl} where record_code = :record_code";
        $dj_info = ctx()->db->get_row($sql, array(':record_code' => $record_code));

        $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        if($arr_lof['lof_status'] == 1 && $this->dj_type != 'purchase'){
            $sql = "select goods_code,spec1_code,spec2_code,sku,init_num as enotice_num,num,lof_no,production_date from b2b_lof_datail where order_code = :record_code and order_type = :order_type";
            $db_mx = ctx()->db->get_all($sql, array(':record_code' => $record_code,':order_type' => $this->dj_type));
        } else {
            $sql = "select goods_code,spec1_code,spec2_code,sku,{$enotice_num_fld},num from {$tbl_mx} where record_code = :record_code";
            $db_mx = ctx()->db->get_all($sql, array(':record_code' => $record_code));
        }

        $mx_data = array();
        $must_scan_mx = array();
        $must_scan_mx_zero_num = array(); //要扫描的明细，但完成单的数量为0,要去通知单取值
        $total_sl = 0;
        $total_scan_sl = 0;
        $dj_mx_map = array();
        foreach ($db_mx as $sub_mx) {
            $total_sl += $sub_mx['enotice_num'];
            $total_scan_sl += $sub_mx['num'];
            if ($sub_mx['num'] > 0) {
                $sub_mx['num'] = (int) $sub_mx['num'];
                $sub_mx['enotice_num'] = (int) $sub_mx['enotice_num'];
                $mx_data[] = $sub_mx;
            }
            if ($sub_mx['enotice_num'] > 0) {
                $must_scan_mx[$sub_mx['sku']] = array('enotice_num' => (int) $sub_mx['enotice_num'], 'num' => (int) $sub_mx['num']);
            } else {
                $must_scan_mx_zero_num[$sub_mx['sku']] = array('enotice_num' => 0, 'num' => $sub_mx['num']);
            }
            $dj_mx_map[$sub_mx['sku']] = $sub_mx;
        }

        //如果存在已扫描数，就要读取已扫描的明细
        $scan_barcode_map = array();
        $scan_data = array();
        $scan_data_js = array();

        if ($total_scan_sl > 0) {
            if($this->dj_type != 'purchase'){
                $scan_data = load_model('util/ViewUtilModel')->record_detail_append_goods_info($mx_data, 1);
            }
            $scan_barcode_map = load_model('util/ViewUtilModel')->get_map_arr($scan_data, 'barcode', 0, 'sku');
            $sku_list = "'" . join("','", $scan_barcode_map) . "'";
            $sql = "select sku,barcode from goods_barcode_child where sku in({$sku_list})";
            $db_child_barcode = ctx()->db->get_all($sql);
            $child_barcode_arr = load_model('util/ViewUtilModel')->get_map_arr($db_child_barcode, 'barcode', 0, 'sku');
            if (!empty($child_barcode_arr)) {
                $scan_barcode_map = array_merge($scan_barcode_map, $child_barcode_arr);
            }
            $key_str = 'sku';
            if($arr_lof['lof_status'] == 1){
                $key_str = 'sku,lof_no,production_date';
            }

            $scan_data_js = load_model('util/ViewUtilModel')->get_map_arr($scan_data, $key_str, 0, 'num');
        }
        if (empty($must_scan_mx) || !empty($must_scan_mx_zero_num)) {
            if ($this->dj_type == 'wbm_return') {
                $sql = "select sku,num,finish_num from {$tbl_notice_mx} where return_notice_code = :record_code";
            } else {
                $sql = "select sku,num,finish_num from {$tbl_notice_mx} where record_code = :record_code";
            }
            $db_tzd_mx = ctx()->db->get_all($sql, array(':record_code' => $dj_info['relation_code']));
            $tzd_mx = array();
            foreach ($db_tzd_mx as $sub_mx) {
                $_sl = $sub_mx['num'] - $sub_mx['finish_num'];
                if ($_sl <= 0) {
                    continue;
                }
                $_sku = $sub_mx['sku'];
                $tzd_mx[$_sku] = $_sl;
            }
            if (empty($must_scan_mx)) {
                foreach ($tzd_mx as $_tzd_sku => $_tzd_sl) {
                    $_sl = isset($must_scan_mx_zero_num[$_tzd_sku]) ? $must_scan_mx_zero_num[$_tzd_sku] : 0;
                    $_tzd_sl = empty($_tzd_sl) ? 0 : $_tzd_sl;
                    $must_scan_mx[$_tzd_sku] = array('enotice_num' => $_tzd_sl, 'num' => $_sl);
                }
            }
            foreach ($db_mx as $sub_mx) {
                if ($sub_mx['enotice_num'] == 0) {
                    $must_scan_mx[$sub_mx['sku']] = array('enotice_num' => $tzd_mx[$sub_mx['sku']], 'num' => $sub_mx['num']);
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
        if ($this->dj_type == 'wbm_notice') {
            $total_sl = 0;
        }
        $result = array();
        $result['total_sl'] = (int) $total_sl;
        $result['total_scan_sl'] = (int) $total_scan_sl;
        $result['dj_info'] = $dj_info;

        $result['dj_info']['dj_type'] = $this->cur_dj_info['dj_type'];
        $result['dj_info']['dj_type_name'] = $this->cur_dj_info['dj_name'];
        $result['dj_info']['dj_ys_url'] = $this->cur_dj_info['ys_url'] . $dj_info['record_code'];
        $result['dj_info']['dj_update_scan_num_url'] = $this->cur_dj_info['update_scan_num_url'] . $dj_info['record_code'];

        $result['scan_data'] = $scan_data;
        $result['scan_data_js'] = $scan_data_js;
        $result['scan_barcode_map'] = $scan_barcode_map;
        $result['must_scan_mx'] = $must_scan_mx;

        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));

        $result['base_spec1_name'] = $cfg['goods_spec1'];
        $result['base_spec2_name'] = $cfg['goods_spec2'];
        //echo '<hr/>$scan_barcode_map<xmp>'.var_export($scan_barcode_map,true).'</xmp>';
        if ($this->dj_type == 'wbm_return') {
            $sql = "select store_code from {$tbl} where record_code = :record_code";
            $store_info = ctx()->db->get_row($sql, array(':record_code' => $record_code));
            foreach ($result['scan_data'] as &$vv){
                $shelf_info = $this->db->get_all("select distinct bs.shelf_name from base_shelf bs left join goods_shelf gs on bs.shelf_code = gs.shelf_code where bs.store_code = :store_code and gs.store_code = :store_code and gs.sku = :sku", array(':store_code' => $store_info['store_code'], ':sku' => $vv['sku']));               
                $shelf_name = '';
                foreach ($shelf_info as $val){
                    $shelf_name .= $val['shelf_name'] . ',';
                }
                $shelf_name = rtrim($shelf_name, ',');
                $vv['shelf_name'] = isset($shelf_name) ? $shelf_name : '';
            }
        }

        return $this->format_ret(1, $result);
    }

    function save_scan($req){
//                var_dump($req);exit;
        $tbl = $this->cur_dj_info['tbl'];
        $tbl_mx = $this->cur_dj_info['tbl'].'_detail';
        if ($this->dj_type == 'wbm_return'){
            $tbl_notice_mx = 'wbm_return_notice_detail_record';
        } else {
            $tbl_notice_mx = $this->cur_dj_info['tbl_notice'].'_detail';
        }
        $dj_name = $this->cur_dj_info['dj_name'];
        $enotice_num_fld = $this->cur_dj_info['enotice_num_fld'];
        $id_fld = $this->cur_dj_info['id_fld'];
        $ys_fld = $this->cur_dj_info['ys_fld'];
        $record_code = $req['record_code'];
        $tzd_code = $req['tzd_code'];
        $record_type = $req['dj_type'];
        $scan_barcode = $req['scan_barcode'];
        $producte_date = !empty($req['producte_date'])?$req['producte_date']:'';
        $lof_no = !empty($req['lof_no'])?$req['lof_no']:'';
        $barcode_is_exist = $req['barcode_is_exist'];
        //echo '<hr/>$req<xmp>'.var_export($req,true).'</xmp>';
        if (empty($record_code) || empty($record_type) || empty($scan_barcode)) {
            return $this->format_ret(-1, '', '单号/单据类型/扫描条码 数据缺失');
        }

        //条件检查转换为SKU 数据
        $ret = $this->parse_scan_barcode($scan_barcode);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $sku = $ret['data']['sku'];

        $this->begin_trans();
        //通知单据 商品完成信息  $tzd_tcode 通知单号
        $dj_scan_data = $this->get_dj_scan_row($tzd_code,$tbl_notice_mx,$record_code,$ret);

        if ($dj_scan_data['status'] < 0){
            return $dj_scan_data;
        } else {
            $dj_scan_row = $dj_scan_data['data'];
        }

        $sql = "select {$id_fld} as record_id,{$ys_fld},rebate,store_code from {$tbl} where record_code = :record_code";
        $dj_row = ctx()->db->get_row($sql, array(':record_code' => $record_code));
        if ($dj_row['ys_flag'] > 0) {
            $this->rollback();
            return $this->format_ret(-1, '', $dj_name . '已验收');
        }
        $rebate = empty($dj_row['rebate']) ? 1 : $dj_row['rebate'];
        $price = 0;
        if ($barcode_is_exist == -1) {
            $_dj_price = $this->cur_dj_info['dj_price'];
            $sku_map = array($sku => $dj_scan_row['goods_code']);
            $ret = load_model('prm/GoodsModel')->get_goods_price($_dj_price, $sku_map);
            if ($ret['status'] < 0) {
                     $this->rollback();
                return $ret;
            }
            $price = (float) $ret['data'][$sku];
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
        );

        $update_str = "num = num +VALUES(num),money = (num +VALUES(num))*price*rebate";
        //$tbl_mx 插入明细表
        $ret = $this->insert_multi_duplicate($tbl_mx, array($insert_data), $update_str);
        $sql_money = "select price,rebate,num from {$tbl_mx} where sku=:sku and pid=:pid";
        $mes = $this->db->get_all($sql_money,array(':sku'=>$sku,':pid'=>$dj_row['record_id']));
        parent::update_exp($tbl_mx, array('money'=>$mes[0]['price']*$mes[0]['rebate']*$mes[0]['num']),array('sku'=>$sku,'pid'=>$dj_row['record_id']));
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '保存扫描数据失败');
        }

          if($req['is_lof'] == 1&& $this->dj_type=='wbm_return'){
             //整理批次数据
              $lof_record_data = array('record_code' => $record_code,
                  'sku' => $sku,
                  'order_type' => $this->dj_type,
                  'lof_no' => $lof_no,
                  'production_date' => $producte_date,
                  'goods_code' => $dj_scan_row['goods_code'],
                  'spec1_code' => $dj_scan_row['spec1_code'],
                  'spec2_code' => $dj_scan_row['spec2_code'],
                  'num' => 1,

              );
          }

        $type_arr = array('purchase', 'pur_return', 'wbm_return', 'wbm_notice','pur_return_notice');
        //如果是采购入库单，要维护 b2b_lof_datail 表
        if (in_array($this->dj_type, $type_arr)) {
            //$ret = $this->update_lof($record_code, $dj_row, $dj_scan_row, $sku, $this->dj_type);
            if($req['is_lof'] == 1){
                $barcode_is_exist  = $this->lof_barcode_exist($sku,$producte_date,$lof_no,$record_code);
            }
            $ret = $this->update_lof(array('lof_no'=>$lof_no,'producte_date'=>$producte_date,'dj_type'=>$this->dj_type,'sku' => $sku,'record_code' => $record_code,'dj_row' => $dj_row,'dj_scan_row' => $dj_scan_row));
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            $producte_date = $ret['data']['production_date'];
        }


        if($req['is_lof'] == 1&&$this->dj_type<> 'wbm_store_out'){
            $sql = "select init_num as enotice_num,num from b2b_lof_datail where order_code = :record_code and lof_no=:lof_no and production_date=:production_date and order_type = :order_type and sku = :sku";
            $_scan_after_row = ctx()->db->get_row($sql,array(':record_code'=>$record_code,':sku'=>$sku,':order_type' => $this->dj_type,':lof_no'=>$lof_no,':production_date'=>$producte_date));
        } else {
            $sql = "select {$enotice_num_fld},num from {$tbl_mx} where record_code = :record_code and sku = :sku";
            $_scan_after_row = ctx()->db->get_row($sql,array(':record_code'=>$record_code,':sku'=>$sku));

        }

        if (empty($_scan_after_row)){
            return $this->format_ret(-1,'','单据不存在sku:'.$sku);
         }
         $_scan_after_num = (int)$_scan_after_row['num'];
         $_scan_after_notice_num = (int)$_scan_after_row['enotice_num'];

        //如果没有关联的通知单，直接认完成单
        $is_wh_num = 0;
        if ($this->dj_type == 'wbm_store_out') {

            if (empty($tzd_code)) {
                if ($_scan_after_num > $_scan_after_notice_num) {
                    $_up_num = $_scan_after_num;
                    //$_scan_after_num = $_scan_after_num;
                    $is_wh_num = 1;
                }
               $ret = $this->update_lof(array('lof_no'=>$lof_no,'producte_date'=>$producte_date,'dj_type'=>$this->dj_type,'sku' => $sku,'record_code' => $record_code,'dj_row' => $dj_row,'dj_scan_row' => $dj_scan_row));

               if ($ret['status'] < 0) {
                       $this->rollback();
                   return $ret;
               }
                 $producte_date = $ret['data']['production_date'];
            } else {
                //$_scan_after_row 已经扫描数量
                //$surplus_num 通知单据未完成数量
                $surplus_num = $dj_scan_row['num'] - $dj_scan_row['finish_num'];
                if ($_scan_after_num > $surplus_num) {
                    $_up_num = $surplus_num;
                    $_scan_after_num = $surplus_num;
                    $is_wh_num = 1;
                } else {
                    $ret = $this->update_lof(array('lof_no' => $lof_no, 'producte_date' => $producte_date, 'dj_type' => $this->dj_type, 'sku' => $sku, 'record_code' => $record_code, 'dj_row' => $dj_row, 'dj_scan_row' => $dj_scan_row));
                    if ($ret['status'] < 0) {
                        $this->rollback();
                        return $ret;
                    }
                    $producte_date = $ret['data']['production_date'];
                }
            }
        }

        if ($is_wh_num == 1 && $this->dj_type == 'wbm_store_out') {
            $sql = "update {$tbl_mx} set num = {$_up_num} where record_code = :record_code and sku = :sku";
            $ret = ctx()->db->query($sql, array(':record_code' => $record_code, ':sku' => $sku));
            if ($ret !== true) {
                     $this->rollback();
                return $this->format_ret(-1, '', '维护扫描数量失败');
            }

            if (!empty($tzd_code)) {
              //  $this->rollback();
                  $this->commit();
                return $this->format_ret(-1, '', '此商品数量已满');
            }
        }

        $result = array();
        if ($barcode_is_exist == -1){
            $goods_info = load_model('util/ViewUtilModel')->record_detail_append_goods_info(array(array('sku'=>$sku)),1,1);
            $result = $goods_info[0];
        }
       $this->commit();
        //$sku_info = load_model('goods/SkuCModel')->get_sku_info($sku,array('goods_name','barcode','spec1_code','goods_code','spec2_code','spec1_name','spec2_name'));

        $result['num'] = $_scan_after_num;
        $result['sku'] = $sku;
        $result['scan_barcode'] = $scan_barcode;
        $result['barcode_is_exist'] = $barcode_is_exist;
        $result['producte_date'] = $producte_date;
        $result['lof_no'] = $lof_no;
        if ($this->dj_type == 'purchase' || $this->dj_type == 'wbm_return') {
            $sql = "select store_code from {$tbl} where record_code = :record_code";
            $store_info = ctx()->db->get_row($sql, array(':record_code' => $record_code));
            $shelf_info = $this->db->get_all("select distinct bs.shelf_name from base_shelf bs left join goods_shelf gs on bs.shelf_code = gs.shelf_code where bs.store_code = :store_code and gs.store_code = :store_code and gs.sku = :sku", array(':store_code' => $store_info['store_code'], ':sku' => $sku));
            $shelf_name = '';
            foreach ($shelf_info as $val){
                $shelf_name .= $val['shelf_name'] . ',';
            }
            $shelf_name = rtrim($shelf_name, ',');
            $result['shelf_name'] = isset($shelf_name) ? $shelf_name : '';
        }
       // $result = array_merge($sku_info,$result);
       //添加日志（目前只做了批发销货单）
          if ($this->dj_type == 'wbm_store_out') {
             //回写主表数量金额
             $ret = load_model('wbm/StoreOutRecordDetailModel')->mainWriteBack($dj_row['record_id']);
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '扫描添加商品', 'action_note' => "添加商品条码：{$scan_barcode}", 'module' => "store_out_record", 'pid' => $dj_row['record_id']);
            load_model('pur/PurStmLogModel')->insert($log);
        }
        //采购通主单回写
        if ($this->dj_type == 'pur_notice') {
            //回写主表数量金额
            $ret = load_model('pur/OrderRecordDetailModel')->mainWriteBack($dj_row['record_id']);
        }
        //批发销货通知单主单回写
        if ($this->dj_type == 'wbm_notice') {
            //回写主表数量金额
            $ret = load_model('wbm/NoticeRecordDetailModel')->mainWriteBack($dj_row['record_id']);
        }
        //采购退货通知单主单回写
        if ($this->dj_type == 'pur_return_notice') {
            //回写主表数量金额
            $ret = load_model('pur/ReturnNoticeRecordDetailModel')->mainWriteBack($dj_row['record_id']);
        }

        return $this->format_ret(1,$result);
    }
    //清除扫描记录
    function clean_scan($req){
	$tbl = $this->cur_dj_info['tbl'];//wbm_store_out_record    wbm_return_record
    	$tbl_mx = $this->cur_dj_info['tbl'].'_detail';//wbm_store_out_record_detail  wbm_return_record_detail
    	$dj_name = $this->cur_dj_info['dj_name'];//批发销货单   批发退货单
    	$enotice_num_fld = $this->cur_dj_info['enotice_num_fld'];//enotice_num
    	$id_fld = $this->cur_dj_info['id_fld'];//store_out_record_id   return_record_id
    	$ys_fld = $this->cur_dj_info['ys_fld'];//is_store_out as ys_flag   is_store_in as ys_flag
    	$record_code = $req['record_code'];//单号
    	$record_type = $req['dj_type'];//wbm_store_out    wbm_return
        //var_dump($tbl,$tbl_mx,$dj_name,$enotice_num_fld,$id_fld,$ys_fld,$record_code);die;
        //批发销货单，批发退货单 支持清除
        $sql = "select {$id_fld} as record_id,{$ys_fld} from {$tbl} where record_code = :record_code";
        $dj_row = $this->db->get_row($sql, array(':record_code' => $record_code));
        if ($dj_row['ys_flag'] > 0) {
            return $this->format_ret(-1, '', $dj_name . '已验收');
        }
        $this->begin_trans();
        //单据明细
        $sql = "select {$enotice_num_fld},num,sku from {$tbl_mx} where record_code = :record_code ";
        $record_mx_ret = $this->db->get_all($sql,array(':record_code'=>$record_code));
        foreach ($record_mx_ret as $value) {
            //扫描数
            $num += $value['num'];
        }
        if ($num != 0) {
            //维护b2b_lof_datail、明细表
            $ret = $this->delete_exp('b2b_lof_datail',array('order_code'=>$record_code,'order_type'=>$record_type));
            foreach ($record_mx_ret as $value) {
                $ret2 = $this->update_exp($tbl_mx, array('num'=>0), array('record_code'=>$record_code,'sku'=>$value['sku']));             
            }
        }else{
            //无扫描记录
            $this->rollback();
            return $this->format_ret(-1, '', '清除扫描记录失败');
        }
        if (!$ret || $ret2['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '清除扫描记录失败');
        }
        $this->commit();
        if ($record_type == 'wbm_store_out') {
            $module = 'store_out_record';
            $status = '未出库';
        }else{
            $module = 'wbm_return_record';
            $status = '未验收';
        }
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => $status, 'action_name' => '清除普通扫描记录', 'action_note' => "", 'module' => $module, 'pid' => $dj_row['record_id']);
        load_model('pur/PurStmLogModel')->insert($log);
        return $this->format_ret(1);
    }        

    function lof_barcode_exist($sku,$producte_date,$lof_no,$record_code){
        $type = $this->dj_type;
        $sql = "select * from b2b_lof_datail where order_code = :order_code and order_type = :order_type";
        $lof_details  = CTX()->db->get_all($sql,array(':order_code' => $record_code,':order_type' =>$type) );
        $key_arr = array();
        $barcode_is_exist = 1;
        $key = $sku.','.$lof_no;
        if(!empty($lof_details)){
            foreach ($lof_details as $detail){
                $key_arr[] = $detail['sku'].','.$detail['lof_no'];
            }

        }
        if(!in_array($key, $key_arr)){
            $barcode_is_exist = -1;
        }
        return $barcode_is_exist;
    }

    /**
     * 扫描识别条码信息
     * @param string $i_barcode 商品条形码
     * @return array 条码数据
     */
    function parse_scan_barcode($i_barcode) {
       // $is_convert_rule = in_array($this->dj_type, array('purchase', 'pur_return')) ? 1 : 0;
        $sku_data = load_model('prm/SkuModel')->convert_scan_barcode($i_barcode);
        if (empty($sku_data)) {
            return $this->format_ret(-1, '', '条码无效');
        }
        $result = array('sku' => $sku_data['sku'], 'goods_code' => $sku_data['goods_code'], 'spec1_code' => $sku_data['spec1_code'], 'spec2_code' => $sku_data['spec2_code']);
        return $this->format_ret(1, $result);
    }

    function update_lof($data){
        if (!empty($data['lof_no'])) {
            $ret_lof = load_model('prm/GoodsLofModel')->is_exists_lof($data['sku'], $data['lof_no'], $data['producte_date']);
            if ($ret_lof['status'] != 1) {
                if ($data['dj_type'] == 'wbm_store_out' || $data['dj_type'] == 'pur_return' || $data['dj_type'] == 'wbm_notice') {
                    return $this->format_ret(-1, '', '条码对应的批次不存在！');
                }
                $sku_lof = array(
                    'sku' => $data['sku'],
                    'lof_no' => $data['lof_no'],
                    'production_date' => $data['producte_date'],
                );
                $lof_no = $data['lof_no'];
                $production_date = $data['producte_date'];
                load_model('prm/GoodsLofModel')->insert($sku_lof);
            } else {
                $lof_no = $ret_lof['data']['lof_no'];
                $production_date = $ret_lof['data']['production_date'];
            }
        } else {
            $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
            if ($ret_lof['status'] < 0) {
                return $ret_lof;
            } else {
                $lof_no = $ret_lof['data']['lof_no'];
                $production_date = $ret_lof['data']['production_date'];
            }
        }


        $insert_data = array(
            'pid' => $data['dj_row']['record_id'],
            'order_code' => $data['record_code'],
            'order_type' => $data['dj_type'],
            'store_code' => $data['dj_row']['store_code'],
            'goods_code' => $data['dj_scan_row']['goods_code'],
            'spec1_code' => $data['dj_scan_row']['spec1_code'],
            'spec2_code' => $data['dj_scan_row']['spec2_code'],
            'sku' => $data['sku'],
            'lof_no' => $lof_no,
            'production_date' => $production_date,
            'num' => 1,
            'occupy_type' => 0,
        );
        //echo '<hr/>$insert_data<xmp>'.var_export($insert_data,true).'</xmp>';die;
        $update_str = "num = num +VALUES(num)";
        if ($this->dj_type == 'wbm_notice' || $this->dj_type == 'pur_return_notice') {
            $insert_data['init_num'] = 1;
            $update_str .= ",init_num = init_num +VALUES(init_num)";
        }
        $ret = $this->insert_multi_duplicate('b2b_lof_datail', array($insert_data), $update_str);
        if ($ret['status']<0){
	       return $this->format_ret(-1,'','保存扫描数据失败');
        }
        return $this->format_ret(1,$insert_data);
    }

    private function get_dj_scan_row($tzd_code,$tbl_notice_mx,$record_code,$ret){
        $sku = $ret['data']['sku'];
        $goods_code = $ret['data']['goods_code'];
        $dj_notice_name = $this->cur_dj_info['dj_notice_name'];
        $tbl_mx = $this->cur_dj_info['tbl'].'_detail';
        //如果没有关联的通知单，直接认完成单
        if (empty($tzd_code)) {
            $dj_scan_row = array();
            $dj_scan_row['goods_code'] = $goods_code;
            $dj_scan_row['spec1_code'] = $ret['data']['spec1_code'];
            $dj_scan_row['spec2_code'] = $ret['data']['spec2_code'];
            $dj_scan_row['enotice_num'] = 0;
            $dj_scan_row['num'] = 0;
        } else {
            $record_code_name = 'record_code';
            if($tbl_notice_mx=='wbm_return_notice_detail_record'){
                 $record_code_name = 'return_notice_code';

            }
            $sql = "select goods_code,spec1_code,spec2_code,num,finish_num from {$tbl_notice_mx} where {$record_code_name} = :record_code and sku = :sku";
            $dj_scan_row = ctx()->db->get_row($sql, array(':record_code' => $tzd_code, ':sku' => $sku));
            if (empty($dj_scan_row)) {
                return $this->format_ret(-1, '', $dj_notice_name . '中无此商品');
            }
            $surplus_num = (int) $dj_scan_row['num'] - (int) $dj_scan_row['finish_num'];
            if ($surplus_num <= 0 && $this->dj_type == 'wbm_store_out') {
                return $this->format_ret(-1, '', $dj_notice_name . '中此商品已出库完成');
            }
            if ($this->dj_type == 'pur_return') {// || $this->dj_type == 'wbm_return'
                $sql = "select goods_code,spec1_code,spec2_code,num,enotice_num from {$tbl_mx} where record_code = :record_code and sku = :sku";
                $dj_record = ctx()->db->get_row($sql, array(':record_code' => $record_code, ':sku' => $sku));
                if (isset($dj_record['num']) && isset($surplus_num) && $dj_record['num'] >= $surplus_num) {
                    return $this->format_ret(-1, '', '此商品数量已满');
                }
            }
        }
        return $this->format_ret(1,$dj_scan_row);
    }

    //修改扫描商品数量
    function update_goods_scan_num($filter) {
        $id_arr = explode('_', $filter['id']);
        $sku = $id_arr[2];
        $filter['sku']=$sku;
        $filter['scan_num']=$filter['num'];
        //查询批发销货明细单通知数量,完成数
        $sql = "SELECT enotice_num,num FROM wbm_store_out_record_detail WHERE record_code = :record_code AND sku = :sku;";
        $data = $this->db->get_row($sql,array(':record_code'=>$filter['record_code'],':sku'=>$filter['sku']));

        //判断有没有关联单号
        $sql = "SELECT relation_code,store_out_record_id FROM wbm_store_out_record WHERE record_code = :record_code";
        $relation_code = $this->db->get_row($sql,array(':record_code'=>$filter['record_code']));

        if(!empty($relation_code['relation_code'])) {
            $surplus_num = $data['enotice_num'] - $filter['scan_num'];
            if($surplus_num < 0) {
                return $this->format_ret(-1,'','扫描数量大于通知数量');
            }
        }

        //修改明细表完成数量
        $sql = "UPDATE wbm_store_out_record_detail SET num = :num WHERE record_code = :record_code AND sku = :sku";
        $ret = $this->query($sql,array(':num' => $filter['scan_num'],':record_code' => $filter['record_code'],':sku' => $filter['sku']));
        if($ret['status'] != 1){
            return $ret;
        }

        //判断批次是否开启
        if($filter['is_lof'] != 1) {
            //修改批次表数量
            $sql = "UPDATE b2b_lof_datail SET num = :scan_num WHERE order_code = :record_code AND sku = :sku";
            $ret = $this->query($sql,array(':scan_num' => $filter['scan_num'], ':record_code' => $filter['record_code'], ':sku' => $filter['sku']));
        } else {
            //修改批次表数量
            $sql = "UPDATE b2b_lof_datail SET num = :scan_num WHERE order_code = :record_code AND sku = :sku AND lof_no = :lof_no;";
            $ret = $this->query($sql,array(':scan_num' => $filter['scan_num'], ':record_code' => $filter['record_code'], ':sku' => $filter['sku'], ':lof_no' => $filter['lof_no']));
        }
        if($ret['status'] != 1){
             return $ret;
        }
        $sql="select barcode from goods_barcode where sku='{$filter['sku']}' ";
        $barcode=$this->db->getOne($sql);
        //日志记录
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '修改普通扫描数量', 'action_note' => "条码：{$barcode}数量由{$data['num']}改为{$filter['scan_num']}", 'module' => "store_out_record", 'pid' => $relation_code['store_out_record_id']);
            load_model('pur/PurStmLogModel')->insert($log);

        return $this->format_ret(1,'','更新完成');
    }
}