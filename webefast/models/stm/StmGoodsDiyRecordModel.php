<?php
/**
 * 组装单据相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lang('stm');

class StmGoodsDiyRecordModel extends TbModel
{
    function get_table(){
        return 'stm_goods_diy_record';
    }


    /*
     * 根据条件查询数据
     */
    function get_by_page($filter){
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        //$sql_join = "";
        $sql_main = "FROM {$this->table} rl
                    LEFT JOIN stm_goods_diy_record_detail r2 on rl.record_code = r2.record_code
                    LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code
                    LEFT JOIN goods_sku r4 on r4.sku = r2.sku
                    WHERE 1";
        $sql_values = array();

                $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
                $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        // 单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }

        //店铺
        if (isset($filter['store_code']) && $filter['store_code'] <> '') {
        	$arr = explode(',',$filter['store_code']);
        	$str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
        	$sql_main .= " AND rl.store_code in ({$str}) ";
        }
   		 // 单据状态
        if (isset($filter['is_sure']) && $filter['is_sure'] != '') {
        	$arr = explode(',',$filter['is_sure']);
        	$str = $this->arr_to_in_sql_value($arr, 'is_sure', $sql_values);
        	$sql_main .= " AND rl.is_sure in ({$str}) ";
        }
    	//商品
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        //关联调整单
        if (isset($filter['relation_code']) && $filter['relation_code'] != '') {
        	$sql_main .= " AND (rl.relation_code LIKE :relation_code )";
        	$sql_values[':relation_code'] = $filter['relation_code'] . '%';
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
        //下单日期
        if (isset($filter['order_time_start']) && $filter['order_time_start'] != '') {
        	$sql_main .= " AND (rl.order_time >= :order_time_start )";
        	$sql_values[':order_time_start'] = $filter['order_time_start']." 00:00:00";
        }
        if (isset($filter['order_time_end']) && $filter['order_time_end'] != '') {
        	$sql_main .= " AND (rl.order_time <= :order_time_end )";
        	$sql_values[':order_time_end'] = $filter['order_time_end']." 23:59:59";
        }
        //类型
         if (isset($filter['record_type']) && $filter['record_type'] != '') {
        	$sql_main .= " AND rl.record_type = :record_type";
        	$sql_values[':record_type'] = $filter['record_type'];
        }
        // 商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
        	$sql_main .= " AND (r4.barcode LIKE :barcode )";
        	$sql_values[':barcode'] = $filter['barcode'] . '%';
        }
         //审核状态
         if (isset($filter['is_check']) && $filter['is_check'] != '') {
        	$sql_main .= " AND rl.is_check = :is_check";
        	$sql_values[':is_check'] = $filter['is_check'];
        }
        //$select = 'rl.*,r2.goods_name,r2.goods_name,r2.weight';
        $select = 'rl.*';
        $sql_main .= " group by rl.record_code order by record_time desc,record_code desc";
       // echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

        foreach ($data['data'] as $key => $value) {
        	$adjust = load_model('stm/StockAdjustRecordModel')->get_row(array('record_code' => $value['relation_code']));
        	$data['data'][$key]['stock_adjust_record_id'] = isset($adjust['data']['stock_adjust_record_id'])?$adjust['data']['stock_adjust_record_id']:'';
                $data['data'][$key]['record_type_name'] = $value['record_type'] == '0' ? '组装' : '拆分';
        }
        filter_fk_name($data['data'], array('store_code|store','supplier_code|supplier'));
       // print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }


    function get_by_id($id){
        $data = $this->get_row(array('goods_diy_record_id' => $id));

        return $data;
    }


    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*"){

        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    /*
     * 删除记录
     * */
    function delete($goods_diy_record_id){
    	$sql = "select * from {$this->table} where goods_diy_record_id = :goods_diy_record_id";
    	$data = $this->db->get_row($sql, array(":goods_diy_record_id" => $goods_diy_record_id));
    	if($data['is_sure']==1){
    		return  $this->format_ret('-1',array(),'单据已经确认，不能删除！');
    	}
        $ret = parent::delete(array('goods_diy_record_id' => $goods_diy_record_id));
        $this->db->create_mapper('stm_goods_diy_record_detail')->delete(array('pid'=>$goods_diy_record_id));

        return $ret;
    }

    /*
     * 添加新纪录
    */
    function insert($stock_adjus){
        $status = $this->valid($stock_adjus);
        if ($status < 1) {
            return $this->format_ret('-1', '',$status);
        }

        $ret = $this->is_exists($stock_adjus['record_code']);

        if (!empty($ret['data'])) return $this->format_ret('-1', '','RECORD_ERROR_UNIQUE_CODE1');
//        $stock_adjus['is_add_time'] = date('Y-m-d H:i:s');
        $stock_adjus['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $stock_adjus['remark']);
        return parent::insert($stock_adjus);
    }

    public function is_exists($value, $field_name = 'record_code'){

        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /*
     * 服务器端验证
    */
    private function valid($data, $is_edit = false){
        if (!$is_edit && (!isset($data['record_code']) || !valid_input($data['record_code'], 'required'))) return RECORD_ERROR_CODE;
        return 1;
    }

    /**
     * 新增一条库存调整单记录
     *
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-10-23
     * @param array $ary_main 主单据数组
     * @return array 返回新增结果
     */
    public function add_action($ary_main){
        //校验参数
        if (!isset($ary_main['store_code']) || !valid_input($ary_main['store_code'], 'required')) {
            return RECORD_ERROR_STORE_CODE;
        }
        //插入主单据
        //生成调整单号
        if (!isset($ary_main['record_code']) && empty($ary_main['record_code'])) {
            $ary_main['record_code'] = $this->create_fast_bill_sn();
        }
        $ary_main['is_add_time'] = date('Y-m-d H:i:s');
        $ret = $this->insert($ary_main);
        //返回结果
        return $ret;
    }
    /**
     * 生成单据号
     */
    function create_fast_bill_sn()
    {
    	$sql = "select  goods_diy_record_id from {$this->table}   order by goods_diy_record_id desc limit 1 ";
    	$data = $this->db->get_all($sql);
    	if ($data) {
    		$djh = intval($data[0]['goods_diy_record_id'])+1;
    	} else {
    		$djh = 1;
    	}
    	require_lib ( 'comm_util', true );
    	$jdh = "AS" . date("Ymd") . add_zero($djh,3);
    	return $jdh;
    }
    /**
     * 编辑一条采购入库单记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-11-12
     * @param array $data
     * @param array $where
     * @return array
     */
    public function edit_action($data,$where){
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        if(!isset($where['goods_diy_record_id']) && !isset($where['record_code'])){
            return $this->format_ret('-1', '','RECORD_ERROR_ID_CODE');
        }
        $result = $this->get_row($where);
        if(1!=$result['status']){
            return $this->format_ret('-1', '','RECORD_ERROR');
        }

        //更新主表数据
        return parent::update($data, $where);
    }
    function update_check_record_code($active,$field, $record_code) {
    	if (!in_array($active, array(0, 1))) {
    		return $this->format_ret('-1','','ERROR_PARAMS');
    	}
    	$record = $this->get_row(array('record_code' => $record_code));
    	$details = load_model('pur/ReturnNoticeRecordDetailModel')->get_all(array('record_code' => $record_code));
    	//检查明细是否为空
    	if (empty($details['data'])) {
    		return $this->format_ret('-1','','RECORD_ERROR_DETAIL_EMPTY');
    	}
    	$ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($record['data']['return_notice_record_id'], 'pur_return_notice');
    	//释放库存
    	require_model('prm/InvOpModel');
    	$invobj = new InvOpModel( $record['data']['record_code'],'pur_return_notice', $record['data']['store_code'],0,$ret_lof_details['data']);
    	$this->begin_trans();
    	$ret = $invobj->adjust();
    	if($ret['status']!=1){
    		$this->rollback(); //事务回滚
    		return $ret;
    	}

    	$ret = parent:: update(array($field => $active), array('record_code' => $record_code));
    	$this->commit(); //事务提交
    	return $ret;
    }
    //更新字段
    function update_check($active,$field, $id) {
    	if (!in_array($active, array(0, 1))) {
    		return $this->format_ret('-1','','ERROR_PARAMS');
    	}
    	$details = load_model('pur/ReturnNoticeRecordDetailModel')->get_all(array('pid' => $id));
    	//检查明细是否为空
    	if (empty($details['data'])) {
    		return $this->format_ret('-1','','RECORD_ERROR_DETAIL_EMPTY');
    	}

    	$ret = parent:: update(array($field => $active), array('return_notice_record_id' => $id));
       $record = $this->get_row(array('goods_diy_record_id' => $id));
        if ($active == 1) {

            $ret = load_model('wms/WmsEntryModel')->add($record['data']['record_code'], 'goods_diy', $record['data']['store_code']);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            } else {
                $ret['status'] = 1;
                $ret['message'] = '操作成功';
            }
        } else {
            $ret = load_model('wms/WmsEntryModel')->cancel($record['data']['record_code'], 'goods_diy', $record['data']['store_code']);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            } else {
                $ret['status'] = 1;
                $ret['message'] = '操作成功';
            }
        }




    	return $ret;
    }
    //
    function  out_relation($id){
    	$record = $this->get_row(array('return_notice_record_id' => $id));
    	$record_code = $record['data']['record_code'];
    	$sql = " select count(*) as cnt  from pur_return_record where  relation_code = :record_code AND   is_store_out = '0'  ";
    	$arr = array(':record_code' => $record_code);
    	$data = $this->db->get_all($sql, $arr);

    	if(isset($data[0]['cnt']) && $data[0]['cnt'] > 0){
    		return $this->format_ret('-1','','存在未出库的采购退货单，是否继续');
    	}
    	return $this->format_ret('1');
    }

    //当关闭批次的时候 查询组装商品 对应详细信息（关闭批次情况下 详情表不包含对应商品信息）
    function get_diy_details_without_lof($details){
        $new_details = array();
        $i = 0;
        if(!empty($details['data'])){
            foreach ($details['data'] as $detail) {
                $new_details[$i] = $detail;
                $i++;
                $sql = "select sku,num from goods_diy d WHERE p_sku = :p_sku ";
                $diy_details = $this->db->get_all($sql,array(":p_sku" => $detail['sku']));
                if(!empty($diy_details)){
                    foreach($diy_details as $d){
                        $key_arr = array('sell_price','trade_price','purchase_price','spec1_code','spec2_code','cost_price','goods_code');
                        $sku_info = load_model('goods/SkuCModel')->get_sku_info($d['sku'], $key_arr);
                        $d_info = array_merge($d,$sku_info);
                        $d_info['pid'] = $detail['pid'];
                        $d_info['record_code'] = $detail['record_code'];
                        $d_info['type'] = 'lof';
                        $d_info['num'] = 0 - ($detail['num']*$d['num']);
                        $d_info['money'] = $d_info['num']*$sku_info['sell_price'];
                        $new_details[$i] = $d_info;
                        $i++;
                    }
                }
            }
        }
        return $new_details;
    }


    //确认/取消确认，锁定库存/释放锁定 针对移出仓
    function update_sure($active, $field, $id,$is_check_wms = 1) {
//        if (!load_model('sys/PrivilegeModel')->check_priv('stm/stm_goods_diy_record/do_sure')) {
//            return $this->format_ret(-1, array(), '无权访问');
//        }




        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $record = $this->get_row(array('goods_diy_record_id' => $id));
        if ($record['data']['is_sure'] == '1') {
            return $this->format_ret('-1', '', '已经确认过');
        }
        if($is_check_wms==1){
            $ret =   load_model('wms/WmsEntryModel')->check_is_wms_record($record['data']['record_code'],'goods_diy' ,$record['data']['store_code']);
            if($ret['status']<1){
                return $ret;
            }

        }




        $details = load_model('stm/StmGoodsDiyRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }

        if(!$this->check_sub_items($record['data']['record_code']))
        {
        	return $this->format_ret('-1', '', '指定批次明细不符合组装商品要求');
        }

        $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        if($arr_lof['lof_status'] == 0){
            $details['data'] = $this->get_diy_details_without_lof($details);
        }
        //库存判断
        $ret = $this->is_inv($details, $record['data']['store_code']);
        if ($ret['status'] == '-1') {
            return $ret;
        }
        //生成调整单
        $ret = $this->create_adjust_record($id, $details, $record['data']['store_code'], $record['data']['record_code']);
        $ret1 = parent:: update(array($field => $active, 'relation_code' => $ret['data']['record_code']), array('goods_diy_record_id' => $id));
        //调整单验收
        $ret2 = load_model('stm/StockAdjustRecordModel')->checkin($ret['data']['stock_adjust_id']);
        return $ret;
    }

    /**
     * @todo 审核单据
     */
    function update_check_by_id($id){
        $record = $this->get_row(array('goods_diy_record_id' => $id));
        $record = $record['data'];
        if ($record['is_check'] == '1') {
            return $this->format_ret('-1', '', '已经审核过');
        }
        $details = load_model('stm/StmGoodsDiyRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        //库存判断
        $inv_ret = $this->is_inv($details, $record['store_code']);
        $msg = '';
        if ($inv_ret['status'] == '-1') {
            $fail_top = array('以下条形码库存不足：');
            $file_name = $this->create_check_fail_files($fail_top, $inv_ret['data']);
//            $msg .= "审核失败，存在库存不足的商品<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $msg .= "审核失败，存在库存不足的商品<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            return $this->format_ret(-1, '', $msg);
        }

        $this->begin_trans();
        $ret = parent::update(array('is_check' => 1), array('record_code' => $record['record_code']));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        if ($lof_status['lof_status'] == 0) {
            $record_type = $record['record_type'] == 1 ? 'stm_split' : 'stm_diy';
            $ret = load_model('wms/WmsEntryModel')->add($record['record_code'], $record_type, $record['store_code']);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
        }

        $this->commit();
        return $this->format_ret(1, '', '审核成功');
    }

    /**
     * @todo 创建审核失败的商品条形码数据
     */
    function create_check_fail_files($fail_top, $barcode) {
        $file_str = implode(",", $fail_top) . "\n";
        ;
        $file_str .= implode("\r\n", $barcode);
        $filename = md5("stm_goods_diy_detail" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }


    /**
     * @todo 取消审核
     */
    function uncheck_by_id($id) {
        $res = $this->get_row(array('goods_diy_record_id' => $id));
        if ($res['data']['is_sure'] == '1') {
            return $this->format_ret('-1', '', '已确认调整，不能取消审核！');
        }

        $record = $this->get_row(array('goods_diy_record_id' => $id));
        $record = $record['data'];
        if ($record['is_check'] == '0') {
            return $this->format_ret('-1', '', '单据未审核');
        }
        $details = load_model('stm/StmGoodsDiyRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $this->begin_trans();
        $ret = parent::update(array('is_check' => 0), array('record_code' => $record['record_code']));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        if ($lof_status['lof_status'] == 0) {
            $record_type = $record['record_type'] == 1 ? 'stm_split' : 'stm_diy';
            $ret = load_model('wms/WmsEntryModel')->cancel($record['record_code'], $record_type, $record['store_code']);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
        }
        $this->commit();
        return $this->format_ret(1, '', '取消审核成功');
    }

    function  check_sub_items($record_code)
    {
    	$ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        $lof_status = $ret_arr['lof_status'] == 1 ? 1 : 0;

        if($lof_status)
        {
        	 $sql = "select count(1) as sku_count from
(
select goods_diy.sku from stm_goods_diy_record_detail,goods_diy
where stm_goods_diy_record_detail.record_code=:record_code and goods_diy.p_sku = stm_goods_diy_record_detail.sku
and stm_goods_diy_record_detail.type = 'diy' group by goods_diy.sku
) as tmp";
             $result = $this->db->get_row($sql,array(':record_code'=>$record_code));

             $sku_count = $result['sku_count'];

			 if($sku_count == 0)
			{
				return false;
			}

              $sql = "select count(1) actual_sku_count from
(
select sku from stm_goods_diy_record_detail
where record_code=:record_code and stm_goods_diy_record_detail.type = 'lof' group by sku
) as tmp";
             $result = $this->db->get_row($sql,array(':record_code'=>$record_code));

             $actual_sku_count = $result['actual_sku_count'];

             if($actual_sku_count == $sku_count)
             	return true;
             else
             	return false;

        }
        else
        {
        	return TRUE;
        }
    }

    function check_lof_inv($detail,$store_code,$lof_status){
//        $type = $detail['num'] > 0 ? 'lof' :'diy';
        $key_arr = array('barcode');
        $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
        $inv_info = array();
        if($lof_status == 1){
//            if($detail['type'] == $type){
              if($detail['num']<0){
                $sql = "select stock_num,lock_num from goods_inv_lof where lof_no = :lof_no and sku = :sku and store_code = :store_code";
                $inv_info = $this->db->get_row($sql,array(':lof_no'=>$detail['lof_no'],':sku'=>$detail['sku'], ':store_code' => $store_code));
                if(empty($inv_info)){
                    return $this->format_ret(-1,$sku_info['barcode'],$sku_info['barcode'].'商品库存不存在');
                }
              }
//            }
        } else {
//
                if($detail['num']<0){
                    $sql = "select stock_num,lock_num from goods_inv where sku = :sku and store_code = :store_code";
                    $inv_info = $this->db->get_row($sql,array(':sku'=>$detail['sku'], ':store_code' => $store_code));
                    if(empty($inv_info)){
                        return $this->format_ret(-1,$sku_info['barcode'],$sku_info['barcode'].'商品库存不存在');
                    }
                }

        }
        if(!empty($inv_info)){
            if($detail['num'] < 0){
                $num = $inv_info['stock_num'] - $inv_info['lock_num'] + ($detail['num']);
                if($num < 0){
                    return $this->format_ret(-1, $sku_info['barcode'], $sku_info['barcode'].'可用库存不足');
                }
            }
        }
        return $this->format_ret(1);
    }

    //库存判断
    function is_inv($details, $store_code) {
        if (!empty($details['data'])) {
            $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
            $lof_status = $ret_arr['lof_status'] == 1 ? 1 : 0;
            $barcode = array();
            $msg=array();
            foreach ($details['data'] as $key => $ary_detail) {
                $inv_info = $this->check_lof_inv($ary_detail,$store_code,$lof_status);
                if($inv_info['status'] != 1){
                    $barcode[] = $inv_info['data'];
                    $msg[]=$inv_info['message'];
                }
            }
            if(!empty($barcode)){
                $inv_info['message']=  implode(',', $msg);
                return $this->format_ret(-1, $barcode, $inv_info['message']);
            }
        }
        return $this->format_ret('1');
    }

    /**
     * 生成调整单
     * @param unknown_type $type 1:全盘 2：商品 3：sku
     */
    function create_adjust_record($id, $details, $store_code, $record_code) {
        //生成调整单主单据
        $ret = $this->get_row(array('goods_diy_record_id' => $id));
        if ($ret['status'] < 1 || empty($ret['data'])) {
            return $ret;
        }
        $record_code = $ret['data']['record_code'];
        $stock_adjust['record_code'] = load_model('stm/StockAdjustRecordModel')->create_fast_bill_sn();
        $stock_adjust['relation_code'] = $record_code;
        $stock_adjust['store_code'] = $ret['data']['store_code'];
        $stock_adjust['order_time'] = date("Y-m-d H:i:s");
        $stock_adjust['rebate'] = 1;
        $stock_adjust['record_time'] = $ret['data']['record_time']; //业务日期
        $stock_adjust['remark'] = '由组装单' . $record_code . '确认生成'; //备注
        $stock_adjust['adjust_type'] = 803;
        $this->begin_trans();
        try {
            $ret = load_model('stm/StockAdjustRecordModel')->insert($stock_adjust);
            if ($ret['status'] < 1) {
                return $ret;
            }
            $stock_adjust_id = $ret['data'];
            $detail_ret = $this->create_detail_adjust($stock_adjust_id, $details, $store_code, $stock_adjust['record_code']);
            if($detail_ret['status']< 0){
                return $detail_ret;
            }
            $this->commit();
            return $this->format_ret(1, array('stock_adjust_id' => $stock_adjust_id, 'record_code' => $stock_adjust['record_code'])); //调整
        } catch (Exception $ex) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $ex->getMessage());
        }
    }

    function create_detail_adjust($stock_adjust_id, $details, $store_code,$record_code){
        //生成批次，明细
        $pici_arr = array();
        if (!empty($details['data'])) {
            foreach ($details['data'] as $key => $ary_detail) {
                $ary_detail['price'] = isset($ary_detail['price']) ? $ary_detail['price'] : 0;
                $key_arr = array('trade_price','purchase_price','spec1_code','cost_price','spec2_code','goods_code');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($ary_detail['sku'], $key_arr);
                $ary_detail['pid'] = $stock_adjust_id;
                $ary_detail['record_code'] = $record_code;
                $ary_detail['sell_price'] = $ary_detail['price'];
                $pici_arr[] = array_merge($ary_detail,$sku_info);
            }
            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($stock_adjust_id, $pici_arr);
            //单据批次添加
            $diy_lof = $pici_arr;
            $new_pici_arr = array();
            foreach($pici_arr as $pici){
                $key = $pici['sku'].'_'.$pici['lof_no'].'_'.$pici['record_code'];
                if(array_key_exists($key, $new_pici_arr)){
                    $new_pici_arr[$key]['num'] = $new_pici_arr[$key]['num'] + $pici['num'];
                } else {
                    $new_pici_arr[$key] = $pici;
                }
            }
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($stock_adjust_id, $store_code, 'adjust', $new_pici_arr);
            if  ($ret['status'] < 0) {
                return $ret;
            }
            //调整单明细添加
            $ret = load_model('stm/StmStockAdjustRecordDetailModel')->add_detail_action($stock_adjust_id, $new_pici_arr);
            if ($ret['status'] < 0) {
                return $ret;
            }
            return $this->format_ret(1, $ret);
        }
        return $this->format_ret(-1, '', '组装单明细为空');
    }

    /**
     * API-组装单确认调整
     * @author wmh
     * @date 2016-12-24
     * @param array $params
     * <pre> 必选: 'record_code','detail''
     * @return array 操作结果
     */
    public function api_goods_diy_accept($params) {
        $error_data = array();
        try {
            $require_option = array(
                's' => array('record_code', 'detail')
            );
            $arr_require = array();
            //提取可选字段中已赋值数据
            $ret_require = valid_assign_array($params, $require_option, $arr_require, true);
            if ($ret_require['status'] === FALSE) {
                $error_data = $ret_require['req_empty'];
                throw new Exception('缺少必填参数或必填参数为空','-10001');
            }
            unset($params);

            $diy_record = $this->get_row(array('record_code' => $arr_require['record_code']));
            $diy_record = $diy_record['data'];
            if (empty($diy_record)) {
                $error_data = $diy_record['record_code'];
                throw new Exception('组装单不存在','-10002');
            }
            if ($diy_record['is_check'] != 1) {
                $error_data = $diy_record['record_code'];
                throw new Exception('组装单未审核','-1');
            }
            if ($diy_record['is_sure'] == 1) {
                $error_data = $diy_record['record_code'];
                throw new Exception('组装单已确认', '-1');
            }
            $ret = load_model('wms/WmsEntryModel')->check_is_wms_record($diy_record['record_code'], 'goods_diy', $diy_record['store_code']);
            if ($ret['status'] < 1) {
                throw new Exception($ret['message'], -1);
            }
            if (!$this->check_sub_items($diy_record['record_code'])) {
                throw new Exception('指定批次明细不符合组装商品要求', '-1');
            }

            $details = $this->api_check_params($arr_require['detail'], $diy_record);
            if ($details['status'] != 1) {
                $error_data = $details['data'];
                throw new Exception($details['message'], $details['status']);
            }

            //库存判断
            $ret = $this->is_inv($details, $diy_record['store_code']);
            if ($ret['status'] == '-1') {
                throw new Exception($ret['message'], -1);
            }
            $this->begin_trans();
            //生成调整单
            $ret = $this->create_adjust_record($diy_record['goods_diy_record_id'], $details, $diy_record['store_code'], $diy_record['record_code']);
            if ($ret['status'] != 1) {
                $this->rollback();
                throw new Exception($ret['message'], -1);
            }
            $ret1 = parent:: update(array('is_sure' => 1, 'relation_code' => $ret['data']['record_code']), array('record_code' => $diy_record['record_code']));
            if ($ret1['status'] != 1) {
                $this->rollback();
                throw new Exception($ret1['message'], -1);
            }
            //调整单验收
            $ret2 = load_model('stm/StockAdjustRecordModel')->checkin($ret['data']['stock_adjust_id']);
            if ($ret['status'] != 1) {
                $this->rollback();
                throw new Exception($ret2['message'], -1);
            }

            $log = array('user_id' => 1, 'user_code' => 'admin', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '完成', 'action_name' => '确认', 'module' => "stm_goods_diy_record", 'action_note' => 'API-确认调整', 'pid' => $diy_record['goods_diy_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $msg = $e->getCode() == 0 ? '更新失败' : $e->getMessage();
            return $this->format_ret($e->getCode(), $error_data, $msg);
        }
    }

    private function api_check_params($detail,$diy_record) {
        $detail = json_decode($detail, true);
        if (empty($detail)) {
            return $this->format_ret(-10005, '', '明细数据处理异常');
        }
        $record_code = $diy_record['record_code'];

        $diy_detail = $this->db->get_all('SELECT * FROM stm_goods_diy_record_detail WHERE record_code=:record_code', array(':record_code' => $record_code));
        if (empty($diy_detail)) {
            return $this->format_ret(-10002, '', '组装单明细为空');
        }

        $d_require = array(
            's' => array('barcode'), 'i' => array('num')
        );
        $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = $lof_status['lof_status'];
        if ($is_lof == 1) {
            $d_require['s'] = array_merge($d_require['s'], array('lof_no', 'production_date'));
            return $this->format_ret(-1, '', '暂未支持批次');
        }
        $d_require_arr = array();
        $diy_key = array();
        $child = array();
        foreach ($detail as $d) {
            $ret_d_required = valid_assign_array($d, $d_require, $d_require_arr, TRUE);
            if ($ret_d_required === FALSE) {
                return $this->format_ret("-10001", $ret_d_required['req_empty'], '缺少必填参数或必填参数为空');
            }
            $child = array_merge($child, $d['child']);
            unset($d['child']);
            $diy[] = $d;

            $k = implode('_', get_array_vars($d, $d_require['s']));
            $num = $d['num'];
            if (isset($diy_key[$k])) {
                $diy_key[$k] += $num;
            } else {
                $diy_key[$k] = $num;
            }
        }
        unset($detail);
        $child_key = array();
        foreach ($child as $c) {
            $num = $c['num'];
            $k = implode('_', get_array_vars($c, $d_require['s']));
            if (isset($child_key[$k])) {
                $child_key[$k] += $num;
            } else {
                $child_key[$k] = $num;
            }
        }

        $sql = "SELECT rd.num,gs.barcode FROM stm_goods_diy_record_detail AS rd INNER JOIN goods_sku AS gs ON rd.sku=gs.sku WHERE record_code=:record_code";
        $diy_detail = $this->db->get_all($sql, array(':record_code' => $record_code));
        if ($lof_status['lof_status'] == 0) {
            $diy_key = array_merge($diy_key, $child_key);
        }

        $details = array();
        foreach ($diy_detail as $d) {
            $arr = get_array_vars($d, $d_require['s']);
            $_key = implode('_', $arr);
            if (!array_key_exists($_key, $diy_key)) {
                return $this->format_ret(-10002, $arr, '商品在组装单中不存在');
            }
            $detail = array();
            $detail['sl'] = $diy_key[$_key];
            $detail['barcode'] = $d['barcode'];
            $details[] = $detail;
        }

        if ($lof_status['lof_status'] == 0) {
            $barcode = deal_array_with_quote(array_keys($diy_key));
            $sku = $this->db->get_col("SELECT sku FROM goods_sku WHERE barcode IN({$barcode})");
            $sku = deal_array_with_quote($sku);
            $sql = "SELECT DISTINCT gs.barcode,gs.sku FROM goods_diy as gd INNER JOIN goods_sku AS gs ON gd.sku=gs.sku WHERE gd.p_sku IN({$sku})";
            $diy = $this->db->get_all($sql);
            $child_barcode_old = array_column($diy, 'barcode');
            $child_barcode_new = array_keys($child_key);
            $barcode_diff = array_diff($child_barcode_old, $child_barcode_new);
            if (!empty($barcode_diff)) {
                return $this->format_ret(-10002, $barcode_diff, '组装单的组装商品中不含以上子商品');
            }

            foreach ($diy as $d) {
                $detail = array();
                $detail['sl'] = $child_key[$d['barcode']];
                $detail['barcode'] = $d['barcode'];
                $details[] = $detail;
            }
        }

        if (empty($details)) {
            return $this->format_ret(-1, '', '组装单明细处理失败');
        }
        return $this->format_ret(1, $details);
    }

    /**
     * API-WMS回传-组装单
     * @author wmh
     * @date 2016-12-24
     * @param array $params
     * <pre> 必选: 'record_code','detail''
     * @return array 操作结果
     */
    public function api_wms_diy_accept($params) {
        $error_data = array();
        try {
            $require_option = array(
                's' => array('record_code', 'detail')
            );
            $arr_require = array();
            //提取可选字段中已赋值数据
            $ret_require = valid_assign_array($params, $require_option, $arr_require, true);
            if ($ret_require['status'] === FALSE) {
                $error_data = $ret_require['req_empty'];
                throw new Exception('缺少必填参数或必填参数为空','-10001');
            }
            unset($params);

            $diy_record = $this->get_row(array('record_code' => $arr_require['record_code']));
            $diy_record = $diy_record['data'];
            if (empty($diy_record)) {
                $error_data = $diy_record['record_code'];
                throw new Exception('组装单不存在', '-10002');
            }
            $record_code = $diy_record['record_code'];
            $record_type = $diy_record['record_type'] == 1 ? 'stm_split' : 'stm_diy';
            if ($diy_record['is_check'] != 1) {
                $error_data = $record_code;
                throw new Exception('组装单未审核', '-1');
            }
            if ($diy_record['is_sure'] == 1) {
                $error_data = $record_code;
                throw new Exception('组装单已确认', '-1');
            }

            $ret = load_model('wms/WmsEntryModel')->check_is_wms_record($diy_record['record_code'], 'goods_diy', $diy_record['store_code']);
            if ($ret['status'] < 1) {
                throw new Exception($ret['message'], -1);
            }
            $sql = 'SELECT wms_order_flow_end_flag FROM wms_b2b_trade WHERE record_code=:record_code AND record_type=:record_type';
            $wms_order_flow_end_flag = $this->db->get_value($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
            if ($wms_order_flow_end_flag == 1) {
                $error_data = $record_code;
                throw new Exception('组装单已回传', '-1');
            }

            if (!$this->check_sub_items($diy_record['record_code'])) {
                throw new Exception('指定批次明细不符合组装商品要求', '-1');
            }

            $details = $this->api_check_params($arr_require['detail'], $diy_record);
            if ($details['status'] != 1) {
                $error_data = $details['data'];
                throw new Exception($details['message'], $details['status']);
            }

            $sql = "SELECT t1.outside_code FROM sys_api_shop_store t1,wms_config t2 WHERE t1.p_id = t2.wms_config_id AND t1.p_type=1 AND t1.shop_store_code = :efast_store_code AND t1.shop_store_type = 1";
            $wms_store_code = $this->db->get_value($sql, array(':efast_store_code' => $diy_record['store_code']));

            $ret_data = array();
            $ret_data['data']['wms_store_code'] = $wms_store_code;
            $ret_data['data']['order_status'] = 'flow_end';
            $ret_data['data']['order_status_txt'] = '收货完成';
            $ret_data['data']['flow_end_time'] = date('Y-m-d H:i:s');

            if (!empty($details['data'])) {
                $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $details['data']);
                if ($ret['status'] < 0) {
                    return $ret;
                }
            }

            if ($ret_data['data']['order_status'] == 'flow_end') {
                $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data, 1);
            }
            return $ret;
        } catch (Exception $e) {
            $msg = $e->getCode() == 0 ? '更新失败' : $e->getMessage();
            return $this->format_ret($e->getCode(), $error_data, $msg);
        }
    }

    /**
     * wms根据组装单创建调整单
     * @param array $record 组装单主单据信息
     * @param array $detail 实际调整数据
     * @return array 结果
     */
    function wms_create_adjust_by_diy($record, $detail) {
        $barcode = array_column($detail, 'barcode');
        $barcode = deal_array_with_quote($barcode);
        $sku = $this->db->get_all("SELECT sku,barcode FROM goods_sku WHERE barcode IN({$barcode})");
        $sku = array_column($sku, 'sku','barcode');
        foreach($detail as &$val){
            $val['sku'] = $sku[$val['barcode']];
        }
        $details = array('data' => $detail);
        $ret = $this->is_inv($details, $record['store_code']);
        if ($ret['status'] == '-1') {
            return $ret;
        }
        $this->begin_trans();
        //生成调整单
        $ret = $this->create_adjust_record($record['goods_diy_record_id'], $details, $record['store_code'], $record['record_code']);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $ret1 = parent:: update(array('is_sure' => 1, 'relation_code' => $ret['data']['record_code']), array('record_code' => $record['record_code']));
        if ($ret1['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //调整单验收
        $ret1 = load_model('stm/StockAdjustRecordModel')->checkin($ret['data']['stock_adjust_id']);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret1;
        }

        $log = array('user_id' => 1, 'user_code' => 'admin', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '完成', 'action_name' => '确认', 'module' => "stm_goods_diy_record", 'action_note' => 'API-确认调整', 'pid' => $record['goods_diy_record_id']);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);

        $this->commit();

        return $this->format_ret(1);
    }

}

