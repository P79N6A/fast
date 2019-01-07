<?php

/**
 * 采购入库单相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');

class PurchaseRecordModel extends TbModel {

    private $is_check_notice_num = 1;
    private $is_deliver = array(
        0 => '未出库',
        1 => '已出库',
        2 => '部分出库',
    );
    
                function get_table() {
        return 'fx_purchaser_record';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        //$sql_join = "";

        $sql_main = "FROM {$this->table} rl  LEFT JOIN fx_purchaser_record_detail r2 on rl.record_code = r2.record_code LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code ";

        $sql_main .= ' WHERE 1 ';
        $sql_values = array();
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
//        $login_type = CTX()->get_session('login_type');
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if($login_type == 2){
//            $user_code = CTX()->get_session('user_code');
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            if(!empty($custom['custom_code'])){
                $sql_main .= " AND rl.custom_code = :custom_code";
                $sql_values[':custom_code'] = $custom['custom_code'];
            } else {
                $sql_main .= " AND 1 != 1";
            }
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code,'get_fx_store');
        } else {
            if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
                $arr_custom = explode(",", $filter['custom_code']);
                $str_custom = $this->arr_to_in_sql_value($arr_custom, 'custom_code', $sql_values);
                $sql_main .= " AND rl.custom_code in ({$str_custom}) ";
            }
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        }
        
        // 单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = '%'.$filter['record_code'] . '%';
        }
        // 通知单号
        if (isset($filter['goods_code']) && $filter['relation_code'] != '') {
            $sql_main .= " AND (rl.relation_code LIKE :relation_code )";
            $sql_values[':relation_code'] = '%'.$filter['relation_code'] . '%';
        }

        // 商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = "'" . implode("','", $sku_arr) . "'";
                $sql_main .= " AND r2.sku in({$sku_str}) ";
            }
        }

        // 入库类型
        if (isset($filter['record_type_code']) && $filter['record_type_code'] != '') {
            $sql_main .= " AND (rl.record_type_code = :record_type_code )";
            $sql_values[':record_type_code'] = $filter['record_type_code'];
        }
        // 单据状态
        if (isset($filter['is_deliver']) && $filter['is_deliver'] != '') {
            $sql_main .= " AND (rl.is_deliver = :is_deliver )";
            $sql_values[':is_deliver'] = $filter['is_deliver'];
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :goods_code)";
            $sql_values[':goods_code'] = '%'.$filter['goods_code'] . '%';
        }
        
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND (r3.goods_name LIKE :goods_name)";
            $sql_values[':goods_name'] = '%'.$filter['goods_name'] . '%';
        }
       
        //下单时间order_time_start
        if (isset($filter['order_time_start']) && $filter['order_time_start'] != '') {
            $sql_main .= " AND (rl.order_time >= :order_time_start )";
            $sql_values[':order_time_start'] = $filter['order_time_start'].' 00:00:00';
        }
        if (isset($filter['order_time_end']) && $filter['order_time_end'] != '') {
            $sql_main .= " AND (rl.order_time <= :order_time_end )";
            $sql_values[':order_time_end'] = $filter['order_time_end'].' 23:59:59';
        }
        //业务时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $sql_main .= " AND (rl.record_time >= :record_time_start )";
            $sql_values[':record_time_start'] = $filter['record_time_start'].' 00:00:00';
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql_main .= " AND (rl.record_time <= :record_time_end )";
            $sql_values[':record_time_end'] = $filter['record_time_end'].' 23:59:59';
        }
        
        //导出明细
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] != 'purchase_record_list') {
            $sql_main .= " order by record_time desc";
            return $this->sell_record_search_csv($sql_main, $sql_values, $filter);
        }
        //$select = 'rl.*,r2.goods_name,r2.goods_name,r2.weight';
        $select = 'rl.*';
        $sql_main .= " group by rl.record_code order by record_time desc,record_code desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as $key => $value) {
            $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['store_code'], 'store_name');
            $data['data'][$key]['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
            $arr = load_model('base/CustomModel')->get_by_field('custom_code', $value['custom_code'], 'custom_name');
            $data['data'][$key]['custom_name'] = isset($arr['data']['custom_name']) ? $arr['data']['custom_name'] : '';
            $data['data'][$key]['diff_num'] = $data['data'][$key]['num'] - $data['data'][$key]['finish_num'];
            $data['data'][$key]['is_deliver_name'] = $this->is_deliver[$value['is_deliver']];
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //导出明细
    private function sell_record_search_csv($sql_main, $sql_values, $filter) {
        $select = "rl.record_code,rl.rebate,rl.store_code,rl.order_time,rl.record_time,r3.goods_code,r3.barcode,r3.price as dj,r2.price,r2.money,r2.num,r2.finish_num,r2.sku,r2.spec1_code,r2.spec2_code,r2.rebate,rl.is_deliver,rl.custom_code";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($ret_data['data'] as $key => &$value) {
            //查询仓库名称
            $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['store_code'], 'store_name');
            $value['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
            //查询规格1/规格2
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value['spec1_name'] = $sku_info['spec1_name'];
            $value['spec2_name'] = $sku_info['spec2_name'];
            $value['goods_name'] = $sku_info['goods_name'];
            $value['barcode'] = $sku_info['barcode'];
            //计算差异
            $value['diff_num'] = $value['num'] - $value['finish_num'];
            //计算进货单价
            $value['price1'] = $value['price'] * $value['rebate'];
            //单据状态
            $value['is_deliver_name'] = $this->is_deliver[$value['is_deliver']];
            //分销商
            $arr = load_model('base/CustomModel')->get_by_field('custom_code', $value['custom_code'], 'custom_name');
            $value['custom_name'] = isset($arr['data']['custom_name']) ? $arr['data']['custom_name'] : '';
        }
        return $this->format_ret(1, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('purchaser_record_id' => $id));
        $arr = load_model('base/StoreModel')->table_get_by_field('base_custom', 'custom_code', $data['data']['custom_code']);
        $data['data']['custom_info'] = !empty($arr['data']) ? $arr['data'] : '';
        $arr = load_model('base/StoreModel')->table_get_by_field('base_store', 'store_code', $data['data']['store_code'], 'store_name');
        $data['data']['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
        return $data;
    }
    
    function get_by_code($code,$select = '*') {
        $sql = "SELECT {$select} FROM fx_purchaser_record WHERE record_code = :code ";
        $ret = $this->db->get_row($sql,array(':code' => $code));
        return $ret;
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*") {

        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }
    
    public function edit_action($data,$where){
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        if(!isset($where['purchaser_record_id']) && !isset($where['record_code'])){
            return $this->format_ret(false,array(),'修改时缺少主单据ID或code!');
        }
        $result = $this->get_row($where);
        if(1!=$result['status']){
            return $this->format_ret(false,array(),'没找到单据!');
        }
//        if(1==$result['data']['is_check_and_accept']){
//            return $this->format_ret(false,array(),'单据已经出库,不能修改!');
//        }

        if(isset($data['store_code'])){
        	$ret = load_model('stm/GoodsInvLofRecordModel')->modify_store_code($data['store_code'],$result['data']['record_code']);
        }
        //更新主表数据
        return parent::update($data, $where);
    }

    /*
     * 删除记录
     * */

    function delete($purchaser_record_id) {
        $sql = "select * from {$this->table} where purchaser_record_id = :purchaser_record_id";
        $data = $this->db->get_row($sql, array(":purchaser_record_id" => $purchaser_record_id));
        if ($data['is_check'] == 1 || $data['is_deliver'] || $data['is_settlement'] == 1) {
            return $this->format_ret('-1', array(), '单据已经确认，不能删除！');
        }
        $ret = parent::delete(array('purchaser_record_id' => $purchaser_record_id));
        $this->db->create_mapper('fx_purchaser_record_detail')->delete(array('pid' => $purchaser_record_id));
        $this->db->create_mapper('b2b_lof_datail')->delete(array('pid' => $purchaser_record_id, 'order_type' => 'fx_purchase'));
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未出库', 'action_name' => "删除", 'module' => "fx_purchase_record", 'pid' => $purchaser_record_id);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        return $ret;
    }

    /*
     * 添加新纪录
     */

    function insert($stock_adjus) {
        $status = $this->valid($stock_adjus);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->is_exists($stock_adjus['record_code']);
        if (!empty($ret['data']))
            return $this->format_ret('-1', '', 'RECORD_ERROR_UNIQUE_CODE1');
        $stock_adjus['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $stock_adjus['remark']);
        if(!empty($stock_adjus['custom_code'])){
            $sql = "select * from base_custom where custom_code = :custom_code";
            $row = $this->db->get_row($sql,array(':custom_code' => $stock_adjus['custom_code']));
            $stock_adjus['express_money'] = $row['fixed_money'];
            $stock_adjus['country'] = empty($row['country']) ? '' : $row['country'];
            $stock_adjus['province'] = empty($row['province']) ? '' : $row['province'];
            $stock_adjus['city'] = empty($row['city']) ? '' : $row['city'];
            $stock_adjus['district'] = empty($row['district']) ? '' : $row['district'];
            $stock_adjus['address'] = empty($row['address']) ? '' : $row['address'];
            $stock_adjus['mobile'] = empty($row['mobile']) ? '' : $row['mobile'];
            $stock_adjus['contact_person'] = empty($row['contact_person']) ? '' : $row['contact_person'];
        }
        return parent::insert($stock_adjus);
    }

    public function is_exists($value, $field_name = 'record_code') {

        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['record_code']) || !valid_input($data['record_code'], 'required')))
            return RECORD_ERROR_CODE;
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
    public function add_action($ary_main) {
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
    
    function update_check($active, $field, $id) {
        $ret = $this->get_row(array('purchaser_record_id' => $id));
        $record = $ret['data'];
        $check_str = $active == 1 ? '不能确认!':'不能取消确认!';
        if(empty($record)){
            return $this->format_ret(-1,'','单据信息为空'.$check_str);
        }elseif($record['is_check'] == 1 && $active == 1){
            return $this->format_ret(-1,'','单据已确认不能重复确认！');
        }elseif($record['is_check'] == 0 && $active == 0){
            return $this->format_ret(-1,'','单据已取消不能重复取消！');
        }
        $details = load_model('fx/PurchaseRecordDetailModel')->get_by_record_code($record['record_code']);
        $sku_arr = array();
        foreach($details['data'] as $val) {
            $sku_arr[$val['sku']] = $val['num'];
        }
        //查询分销商信息
        $custom_data = load_model('base/CustomModel')->get_by_code($record['custom_code']);
        //是否是按重量结算运费，根据商品理论重量计算运费
        $record['express_money'] = (float)$record['express_money'];
        if($custom_data['data']['settlement_method'] == 1 && $active == 1 && empty($record['express_money'])) {
            $ret = $this->get_express_money($record, $details, $sku_arr);
            if($ret['status'] < 0) {
                return $ret;
            }
        }
        if($active == 1){
            $detail = $this->db->get_row("select * from fx_purchaser_record_detail where pid = :pid",array(":pid" => $id));
            if(empty($detail)){
                return $this->format_ret(-1,'','单据详细信息为空，不能确认');
            }
        }
        if($active == 0){
            if($record['data']['is_settlement'] == 1 || $record['data']['is_deliver'] == 1){
                return $this->format_ret(-1,'','单据已经结算，不能取消确认');
            }
        }
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        } 
        $ret = parent:: update(array($field => $active), array('purchaser_record_id' => $id));
        return $ret;
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn($djh = 0) {
        if ($djh > 0) {
            $sql = "select purchaser_record_id from {$this->table} order by purchaser_record_id desc limit 1 ";
            $data = $this->db->get_all($sql);
            if ($data) {
                $djh = intval($data[0]['purchaser_record_id']) + 1;
            } else {
                $djh = 1;
            }
        } else {
            $djh = $djh + 1;
        }
        require_lib('comm_util', true);
        $new_jdh = "JX" . date("Ymd") . add_zero($djh);
        $sql = "select purchaser_record_id  from {$this->table}  where record_code=:record_code ";
        $sql_value = array(':record_code' => $new_jdh);
        $id = $this->db->get_value($sql, $sql_value);
        if (empty($id) || $id === false) {
            return $new_jdh;
        } else {
            return $this->create_fast_bill_sn($djh);
        }
    }
    //计算金额
    function set_money($record_id) {
        $sql = " update pur_purchaser_record_detail set money = price * rebate * num  where pid ='{$record_id}'  ";
        //echo $sql;
        return $this->db->query($sql);
    }

   

    function imoprt_detail($id, $file, $is_lof = 0) {
        $record = $this->get_row(array('purchaser_record_id' => $id));
        $store_code = $record['data']['store_code'];
        $barcode_arr = $barcode_num = array();
        $error_msg = '';
        $err_num = 0;
        /*$lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = $lof_manage['lof_status'];*/

        //未开启批次导入库存方法
        if ($is_lof == '1') {
            $num = $this->read_csv_lof($file, $barcode_arr, $barcode_num);
            $import_count = count($barcode_arr);
            $ret = $this->get_barcode_by_sku($barcode_arr, $barcode_num,$error_msg,$err_num);
        } else {
            $num = $this->read_csv_sku($file, $barcode_arr, $barcode_num);
            $import_count = count($barcode_num);
            $ret = $this->get_barcode_by_sku($barcode_arr, $barcode_num,$error_msg,$err_num);
            //没有开启批次的 查询默认批次 如果批次表里 没有，在批次表里添加goods_lof
            $sql_values = array();
            $sku_str = $this->arr_to_in_sql_value($barcode_arr,'sku',$sql_values);
            $sql = "select g.barcode,g.sku,l.lof_no,l.production_date,l.type from goods_lof l "
                    . "INNER JOIN goods_sku g ON g.sku=l.sku  where"
                    . " g.sku in ({$sku_str}) group by l.sku";
            $sku_data = $this->db->get_all($sql,$sql_values);
            $sql_moren = "select lof_no,production_date from goods_lof  where type=1";
            $moren = $this->db->get_row($sql_moren);
            $lof_data_new = array();
            foreach ($sku_data as $lof_data) {
                $lof_data_new[$lof_data['sku']]['production_date'] = $lof_data['production_date'];
                $lof_data_new[$lof_data['sku']]['lof_no'] = $lof_data['lof_no'];
            }
            $new_barcode_num = $barcode_num;
            $barcode_num = array();
            foreach ($barcode_arr as $sku) {
                if (array_key_exists($sku, $lof_data_new)) {
                    $barcode_num[$sku][$lof_data_new[$sku]['lof_no']]['num'] = $new_barcode_num[$sku]['num'];
                    $barcode_num[$sku][$lof_data_new[$sku]['lof_no']]['lof_no'] = $lof_data_new[$sku]['lof_no'];
                    $barcode_num[$sku][$lof_data_new[$sku]['lof_no']]['production_date'] = $lof_data_new[$sku]['production_date'];
                } else {
                    $barcode_num[$sku][$moren['lof_no']]['num'] = $new_barcode_num[$sku]['num'];
                    $barcode_num[$sku][$moren['lof_no']]['lof_no'] = $moren['lof_no'];
                    $barcode_num[$sku][$moren['lof_no']]['production_date'] = $moren['production_date'];
                }
            }
        }
        if (!empty($barcode_num) && !empty($barcode_arr)) {
            //判断商品是否分销款,是否与分销商匹配
            $goods_model = load_model('fx/GoodsModel');
            $sku_arr = $goods_model->get_by_fx_goods_sku('barcode', $barcode_arr);
            $fx_barcode_arr = array_column($sku_arr,'barcode');
            $custom_arr = $goods_model->get_custom_goods_sku('rl.custom_code,r2.barcode',array($record['data']['custom_code']));

            $sql_values = array();
            $sku_str = $this->arr_to_in_sql_value($barcode_arr,'sku',$sql_values);
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.price,g.purchase_price,g.sell_price  from
	    		goods_sku b
	    		inner join  base_goods g ON g.goods_code = b.goods_code
	    		where b.sku in({$sku_str}) GROUP BY b.barcode";
            $detail_data = $this->db->get_all($sql,$sql_values); //sell_price
            $detail_data_lof = array();
            $temp = array();
            foreach ($detail_data as $key => $val) {
                if(!in_array($val['barcode'], $fx_barcode_arr)) {
                    $error_msg[] = array($val['barcode'] => '商品不是分销款商品');
                    $err_num ++;
                    unset($barcode_num[$val['sku']]);
                    continue;
                }
                if(!(isset($custom_arr[$record['data']['custom_code']]) && in_array($val['barcode'],$fx_barcode_arr))) {
                    $error_msg[] = array($val['barcode'] => '商品与分销商不匹配');
                    $err_num ++;
                    unset($barcode_num[$val['sku']]);
                    continue;
                }
                foreach ($barcode_num[$val['sku']] as $k1 => $v1) {
                    if (intval($v1['num']) > 0) {
                        $val['num'] = $v1['num'];
                        $val['lof_no'] = $v1['lof_no'];
                        $val['production_date'] = $v1['production_date'];
                        //取出分销结算单价
                        $val['price'] = load_model('fx/GoodsManageModel')->compute_fx_price($record['data']['custom_code'],$val, $record['data']['order_time']);
                        $val['price'] = sprintf("%.2f",$val['price']);
                        $val['money'] = (float)$val['price'] * $val['num'];
                        $detail_data_lof[] = $val;
                        unset($barcode_num[$val['sku']]);
                    } else {
                        $error_msg[] = array($val['barcode'] => '数量不能为空或为零');
                        $err_num ++;
                        unset($barcode_num[$val['sku']]);
                    }
                }
            }
            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $detail_data_lof);
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'fx_purchase', $detail_data_lof);
               if ($ret['status']<1) {
                return $ret;
                }  
            //入库单明细添加
            $ret = load_model('fx/PurchaseRecordDetailModel')->add_detail_action($record['data']['record_code'], $detail_data_lof);
            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未出库', 'action_name' => '增加明细', 'module' => "purchase", 'pid' => $id);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
            $ret['data'] = '';
        }


        if (!empty($barcode_num)) {
            $sku_error = array_keys($barcode_num);
            foreach ($sku_error as $err) {
                $error_msg[] = array($err => '系统不存在该条码信息');
                $err_num ++;
            }
        }
        $success_num = $import_count - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $message .=',' . '失败数量:' . $err_num;
            $fail_top = array('商品条码', '错误信息');
            $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }
    
    function get_barcode_by_sku(&$barcode_arr, &$barcode_num,&$error_msg,&$err_num){
        $sql_values = array();
        $barcode_str = $this->arr_to_in_sql_value($barcode_arr,'barcode',$sql_values);
        $sql = "select sku,barcode from goods_sku where barcode in({$barcode_str}) ";
        $data = $this->db->get_all($sql,$sql_values);
        $sku_num = array();
        $sku_arr = array();
        $new_barcode = array();
        foreach($data as $val){
            $sku_arr[]=$val['sku'];
            $sku_num[$val['sku']] = $barcode_num[$val['barcode']] ;
            $new_barcode[] = $val['barcode'];
        }
        if(count($barcode_arr)!=count($new_barcode)){
            $result = array_diff($barcode_arr,$new_barcode);
            $err_num = count($result);
            foreach($result as $val) {
                $error_msg[] = array($val=>'找不到对应条码');
            }
            //return $this->format_ret(-1,'',"找不到对应条码".  implode(",", $result));
        }
        $barcode_arr = $sku_arr;
        $barcode_num = $sku_num;
        return $this->format_ret(1);
        
    }

   
    function read_csv_sku($file, &$sku_arr, &$sku_num) {
        //    $key_arr = array('0'=>'sku','1'=>'num');
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sku_arr[] = $row[0];
                    $sku_num[$row[0]]['num'] = $row[1];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
        // var_dump($sku_arr,$sku_num);die;
    }

    function read_csv_lof($file, &$sku_arr, &$sku_num) {
        //    $key_arr = array('0'=>'sku','1'=>'num');
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sku_arr[$row[0]] = $row[0];
                    $sku_num[$row[0]][$row[3]]['num'] = $row[1];
                    $sku_num[$row[0]][$row[3]]['lof_no'] = $row[3];
                    $production_date = load_model('prm/GoodsLofModel')->get_lof_production_date($row[3], $row[0]);
                    $sku_num[$row[0]][$row[3]]['production_date'] = !empty($production_date) ? $production_date : $row[4];
                }
            }
            $i++;
        }
        fclose($file);
        if (!empty($sku_arr)) {
            $sku_arr = array_values($sku_arr);
        }

        return $i;
        // var_dump($sku_arr,$sku_num);die;
    }

    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
                $val = iconv('gbk', 'utf-8', $val);
                $val = trim(str_replace('"', '', $val));
                //   $row[$key] = $val;
            }
        }
    }

    function add_detail_goods($id, $data, $store_code) {
        $record = $this->get_row(array('purchaser_record_id' => $id));
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $data);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'fx_purchase', $data);
        if ($ret['status']<1) {
                return $ret;
        }
        //增加明细        
        $ret = load_model('fx/PurchaseRecordDetailModel')->add_detail_action($record['data']['record_code'], $data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'finish_status' => '未出库', 'action_name' => '增加明细', 'module' => "fx_purchase_record", 'pid' => $id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
    }

    function do_settlement($record_code,$type){
        $record = $this->get_row(array('record_code' => $record_code));
        $record = $record['data'];
        if(empty($record)){
            return $this->format_ret(-1,'','单据信息为空不能结算！');
        }elseif($record['is_check'] != 1){
            return $this->format_ret(-1,'','单据未确认不能结算！');
        }elseif($record['is_settlement'] == 1){
            return $this->format_ret(-1,'','单据已结算不能重复结算！');
        }

//        $fenxiao_power = load_model('oms/SellRecordModel')->check_fenxiao_power($record);
        $this->begin_trans();
        try {
            $arr = array('is_settlement' => 1);
            $ret = $this->db->update('fx_purchaser_record', $arr, array('record_code' => $record_code));
            if ($ret !== true) {
                return $this->format_ret(-1, '', '经销采购订单结算操作失败');
            }
            //修改资金账户
            $ret = $this->js_income_pay($record,'fx_purchase_settlement');
            if($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            $record['is_settlement'] = 1;
            $create_pft = load_model('wbm/NoticeRecordModel')->create_pftz($record);
            if($create_pft['status'] < 0){
                $this->rollback();
                return $create_pft;
            }
                    
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未出库', 'action_name' => '结算', 'module' => "fx_purchase_record", 'pid' => $record['purchaser_record_id'],'action_note' => '单据结算');
            load_model('pur/PurStmLogModel')->insert($log);
            $this->commit();
            return $this->format_ret(1,$create_pft['data']['id']);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }
    function js_income_pay($record,$type) {
        //是否开启资金账户
        $fx_finance_account_manage = load_model('sys/SysParamsModel')->get_val_by_code('fx_finance_account_manage');
        //判断分销商是否是淘宝分销商
        $data = load_model('base/CustomModel')->get_by_code($record['custom_code']);
        $custom_data = empty($data['data']) ? '' : $data['data'];
        //开启资金账户生成资金流水(不是淘宝分销商)
        if($fx_finance_account_manage['fx_finance_account_manage'] == 1 && !empty($custom_data['custom_type']) && $custom_data['custom_type'] == 'pt_fx') {
            //金额
            $money = isset($record['express_money']) && !empty($record['express_money']) ? $record['sum_money'] + $record['express_money'] : $record['sum_money'];
            $account_data = array(
                'money' => $money,
                'record_code' => $record['record_code'],
                'custom_code' => $record['custom_code'],
                'type' => $type,
            );
            //修改资金账户
            $ret = load_model('fx/BalanceOfPaymentsModel')->create_fx_income_pay($account_data);
            return $ret;
        }
        return $this->format_ret(1);
    }
    
    function do_unsettlement($record_code,$type){
        $record = $this->get_row(array('record_code' => $record_code));
        $record = $record['data'];
        if(empty($record)){
            return $this->format_ret(-1,'','单据信息为空不能结算！');
        }elseif($record['is_check'] != 1){
            return $this->format_ret(-1,'','单据未确认不能取消结算！');
        }elseif($record['is_settlement'] == 0){
            return $this->format_ret(-1,'','单据已取消结算不能取消结算！');
        }
//        $fenxiao_power = load_model('oms/SellRecordModel')->check_fenxiao_power($record);
        $this->begin_trans();
        try {
            $arr = array('is_settlement' => 0);
            $ret = $this->db->update('fx_purchaser_record', $arr, array('record_code' => $record_code));
            if ($ret !== true) {
                $this->rollback();
                return $this->format_ret(-1, '', '经销采购订单取消结算操作失败');
            }
            //修改资金账户
            $ret = $this->js_income_pay($record,'fx_purchase_unsettlement');
            if($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未出库', 'action_name' => '取消结算', 'module' => "fx_purchase_record", 'pid' => $record['purchaser_record_id']);
            load_model('pur/PurStmLogModel')->insert($log);
            $delete_pft = load_model('wbm/NoticeRecordModel')->delete_pftz($record);
            if($delete_pft['status'] < 0){
                $this->rollback();
                return $delete_pft;
            }
            
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }
    function get_express_money($record, $new_details, $sku_arr) {
        $ret = load_model('op/PolicyExpressModel')->fx_parse($record, $new_details);
        if ((isset($ret['data']) && !empty($ret['data'])) || empty($record['express_code'])) {
            $record['express_code'] = $ret['data'];
            //计算商品理论重量
            $weight = 0;
            foreach($sku_arr as $key => $val) {
                $ret = load_model('goods/SkuCModel')->get_sku_info($key);
                $weight += $ret['weight'] * $val;
            }
            $weight = (float)sprintf("%01.3f",(float)$weight/1000);
            //计算运费
            $express_money = load_model('oms/SellRecordCzModel')->get_trade_weigh_express_money($record,$weight,'jx');
            $ret = $this->update_exp('fx_purchaser_record', array('express_code' => $record['express_code'],'express_money' => $express_money['data']), array('record_code' => $record['record_code']));
            //$ret = $this->affected_rows();
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '计算运费失败');
            }
        } else {
            return $this->format_ret(-1, '', '匹配快递失败');
        }
    }
    
}
