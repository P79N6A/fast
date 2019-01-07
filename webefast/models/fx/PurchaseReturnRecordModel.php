<?php

/**
 * 采购入库单相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');

class PurchaseReturnRecordModel extends TbModel {

   

    private $is_store_out = array(
        0 => '未入库',
        1 => '已入库',
        2 => '部分入库',
    );
    function get_table() {
        return 'fx_purchaser_return_record';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
    	if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
    		$filter[$filter['keyword_type']] = trim($filter['keyword']);
    	}
        $sql_join = " LEFT JOIN fx_purchaser_return_record_detail r2 on rl.return_record_code = r2.record_code LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code ";

        $sql_main = "FROM {$this->table} rl {$sql_join} ";

        $sql_main .= ' WHERE 1 ';
        $sql_values = array();
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        //$login_type = CTX()->get_session('login_type');
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if($login_type == 2){
            //$user_code = CTX()->get_session('user_code');
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
        if (isset($filter['return_record_code']) && $filter['return_record_code'] != '') {
            $sql_main .= " AND (rl.return_record_code LIKE :return_record_code )";
            $sql_values[':return_record_code'] = '%'.$filter['return_record_code'] . '%';
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
        //单据状态
        if(isset($filter['is_check_and_accept']) && $filter['is_check_and_accept'] != '') {
            switch ($filter['is_check_and_accept']) {
                case 'is_check_0':
                    $sql_main .= " AND (rl.is_check = 0) ";
                    break;
                case 'is_check_1':
                    $sql_main .= " AND (rl.is_check = 1) ";
                    break;
                case 'is_store_in_0':
                    $sql_main .= " AND (rl.is_store_in = 0) ";
                    break;
                case 'is_store_in_1':
                    $sql_main .= " AND (rl.is_store_in = 1) ";
                    break;
                case 'is_store_in_2':
                    $sql_main .= " AND (rl.is_store_in = 2) ";
                    break;
                case 'is_settlement_0':
                    $sql_main .= " AND (rl.is_settlement = 0) ";
                    break;
                case 'is_settlement_1':
                    $sql_main .= " AND (rl.is_settlement = 1) ";
                    break;
            }
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
        $select = 'rl.*';
        $sql_main .= " group by rl.return_record_code order by record_time desc,return_record_code desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as $key => $value) {
            $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['store_code'], 'store_name');
            $data['data'][$key]['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
            $arr = load_model('base/CustomModel')->get_by_field('custom_code', $value['custom_code'], 'custom_name');
            $data['data'][$key]['custom_name'] = isset($arr['data']['custom_name']) ? $arr['data']['custom_name'] : '';
            $data['data'][$key]['diff_num'] = $data['data'][$key]['num'] - $data['data'][$key]['finish_num'];
            $is_check = $value['is_check'] == 1 ? '已确认' : "未确认";
            $is_settlement = $value['is_settlement'] == 1 ? '已结算' : "未结算";
            $is_store_out = $this->is_store_out[$value['is_store_in']];
            $data['data'][$key]['status'] = $is_check . " " . $is_store_out ." " . $is_settlement;
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //导出明细
    private function sell_record_search_csv($sql_main, $sql_values, $filter) {
        $select = "rl.is_check,rl.is_settlement,rl.is_store_in,rl.return_record_code,rl.store_code,rl.init_code,rl.order_time,rl.record_time,rl.custom_code,r3.goods_code,r3.goods_name,r3.barcode,r2.price,r2.money,r2.num,r2.finish_num,r2.sku,r2.spec1_code,r2.spec2_code";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($ret_data['data'] as $key => &$value) {
            //分销商
            $arr = load_model('base/CustomModel')->get_by_field('custom_code', $value['custom_code'], 'custom_name');
            $value['custom_name'] = isset($arr['data']['custom_name']) ? $arr['data']['custom_name'] : '';
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
            //单据状态
            $is_check = $value['is_check'] == 1 ? '已确认' : "未确认";
            $is_settlement = $value['is_settlement'] == 1 ? '已结算' : "未结算";
            $is_store_out = $this->is_store_out[$value['is_store_in']];
            $value['status'] = $is_check . " " . $is_store_out ." " . $is_settlement;
        }
        return $this->format_ret(1, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('fx_purchaser_return_id' => $id));
        $arr = load_model('base/StoreModel')->table_get_by_field('base_custom', 'custom_code', $data['data']['custom_code']);
        $data['data']['custom_info'] = !empty($arr['data']) ? $arr['data'] : '';
        $arr = load_model('base/StoreModel')->table_get_by_field('base_store', 'store_code', $data['data']['store_code'], 'store_name');
        $data['data']['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
        return $data;
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
        if(!isset($where['fx_purchaser_return_id']) && !isset($where['return_record_code'])){
            return $this->format_ret(false,array(),'修改时缺少主单据ID或code!');
        }
        $result = $this->get_row($where);
        if(1!=$result['status']){
            return $this->format_ret(false,array(),'没找到单据!');
        }
//        if(1==$result['data']['is_check_and_accept']){
//            return $this->format_ret(false,array(),'单据已经验收,不能修改!');
//        }

        if(isset($data['store_code'])){
        	$ret = load_model('stm/GoodsInvLofRecordModel')->modify_store_code($data['store_code'],$result['data']['return_record_code']);
        }
        //更新主表数据
        return parent::update($data, $where);
    }

    /*
     * 删除记录
     * */

    function delete($fx_purchaser_return_id) {
        $sql = "select * from {$this->table} where fx_purchaser_return_id = :fx_purchaser_return_id";
        $data = $this->db->get_row($sql, array(":fx_purchaser_return_id" => $fx_purchaser_return_id));
        if ($data['is_check'] == 1 || $data['is_store_in'] == 1 || $data['is_settlement'] == 1) {
            return $this->format_ret('-1', array(), '单据已经确认，不能删除！');
        }
        $ret = parent::delete(array('fx_purchaser_return_id' => $fx_purchaser_return_id));
        $this->db->create_mapper('fx_purchaser_record_detail')->delete(array('pid' => $fx_purchaser_return_id));
        $this->db->create_mapper('b2b_lof_datail')->delete(array('pid' => $fx_purchaser_return_id, 'order_type' => 'fx_return'));
        
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
        $ret = $this->is_exists($stock_adjus['return_record_code']);
        if (!empty($ret['data']))
            return $this->format_ret('-1', '', 'RECORD_ERROR_UNIQUE_CODE1');
        $stock_adjus['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $stock_adjus['remark']);
        /*if(!empty($stock_adjus['custom_code'])){
            $sql = "select fixed_money from base_custom where custom_code = :custom_code";
            $row = $this->db->get_row($sql,array(':custom_code' => $stock_adjus['custom_code']));
            $stock_adjus['express_money'] = $row['fixed_money'];
        }*/
        return parent::insert($stock_adjus);
//        //有关联单号就添加明细
//        if(!empty($stock_adjus['init_code'])) {
//            $data = load_model('fx/PurchaseRecordDetailModel')->get_by_record_code($stock_adjus['init_code']);
//            if(empty($data['data'])) {
//                return $ret;
//            }
//            $sql = "SELECT fx_purchaser_return_id FROM fx_purchaser_return_record WHERE return_record_code = '{$stock_adjus['return_record_code']}';";
//            $pid = $this->db->getOne($sql);
//            foreach ($data['data'] as $key => $value) {
//                $detail['record_code'] = $stock_adjus['return_record_code'];
//                $detail['pid'] = $pid;                 
//                $detail['goods_code'] = $value['goods_code']; 
//                $detail['spec1_code'] = $value['spec1_code']; 
//                $detail['spec2_code'] = $value['spec2_code']; 
//                $detail['sku'] = $value['sku']; 
//                $detail['price'] = $value['price']; 
//                $detail['money'] = $value['money']; 
//                $detail['finish_num'] = 0; 
//                $detail['num'] = $value['num']; 
//                $detail['goods_property'] = 0; 
//                $detail['cost_price'] = $value['cost_price']; 
//                $detail['remark'] = $stock_adjus['remark']; 
//                $sum_money += $detail['money']; 
//            }
//            $ret2 = M('oms_return_package_detail')->insert_exp('fx_purchaser_return_record_detail',$detail,true);
//            if($ret2['status'] != 1) {
//                return $ret2;
//            }
//            //回写订单总金额
//            $ret3 = parent::update(array('sum_money'=>$sum_money), array('fx_purchaser_return_id' => $pid));
//            if($ret3['status'] != 1) {
//                return $ret3;
//            }
//            //维护单据批次数据
//            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $record['store_code'], 'fx_purchase', $detail);
//            if($ret['status'] != 1) {
//                $this->rollback();
//                return $ret;
//            }
//        }
    }

    public function is_exists($value, $field_name = 'return_record_code') {

        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['return_record_code']) || !valid_input($data['return_record_code'], 'required')))
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
        $record = $this->get_row(array('fx_purchaser_return_id' => $id));
        $detail = $this->db->get_all("select * from fx_purchaser_return_record_detail where pid = :pid",array(":pid" => $id));
        if($active == 1){
            if(empty($detail)){
                return $this->format_ret(-1,'','单据详细信息为空，不能确认');
            }
        }
        $sum_finish_num = 0;
        foreach ($detail as &$value) {
            $sum_finish_num += $value['finish_num'];
            unset($value['return_record_detail_id']);
            unset($value['pid']);
            unset($value['record_code']);
            unset($value['lastchanged']);
            $value['trade_price'] = $value['price'];
        }
//        if($active == 0){
//            if($record['data']['is_settlement'] == 1 || $record['data']['is_store_in'] == 1){
//                return $this->format_ret(-1,'','单据已经结算，不能取消确认');
//            }
//        }
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        if($active == 1) {
            $remark = isset($record['data']['remark']) && !empty($record['data']['remark']) ? $record['data']['remark'].',经销退货单号：'.$record['data']['return_record_code'] : '经销退货单号：'.$record['data']['return_record_code'];
            //生成批发退货通知单
            $return_notice_record['return_notice_code'] = load_model('wbm/ReturnNoticeRecordModel')->create_fast_bill_sn();
            $return_notice_record['order_time'] = $record['data']['order_time'];
            $return_notice_record['jx_return_code'] = $record['data']['return_record_code'];
            $return_notice_record['custom_code'] = $record['data']['custom_code'];
            $return_notice_record['store_code'] = $record['data']['store_code'];
            $return_notice_record['remark'] = $remark;
            $return_notice_record['return_type_code'] = 'inferior_return';
            
            $this->begin_trans();
            //修改状态
            $ret = parent:: update(array($field => $active), array('fx_purchaser_return_id' => $id));
            if($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
            $ret2 = load_model('wbm/ReturnNoticeRecordModel')->insert($return_notice_record);
            $notice_record_id = $ret2['data'];
            if($ret2['status'] != 1) {
                $this->rollback();
                return $ret2;
            }
            //日志
            $log = array('user_id'=>CTX()->get_session('user_id'),'user_code'=>CTX()->get_session('user_code'),'ip'=>'','add_time'=>date('Y-m-d H:i:s'),'sure_status'=>"未确认",'finish_status'=>'未完成','action_name'=>"创建",'module'=>"wbm_return_notice_record",'pid'=>$notice_record_id,'action_note' => '经销退货单号：'.$record['data']['return_record_code']);
            load_model('pur/PurStmLogModel')->insert($log);
            
            //生成明细
            $ret3 = load_model('wbm/ReturnNoticeDetailRecordModel')->add_detail_action($ret2['data'],$detail);
            if($ret3['status'] != 1) {
                $this->rollback();
                return $ret3;
            }
            
            //自动确认
            $ret = load_model('wbm/ReturnNoticeRecordModel')->update_check(1,'is_check',$return_notice_record['return_notice_code']);
            if($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            $log = array('user_id'=>CTX()->get_session('user_id'),'user_code'=>CTX()->get_session('user_code'),'ip'=>'','add_time'=>date('Y-m-d H:i:s'),'sure_status'=>'未确认','finish_status'=>'未完成','action_name'=>'确认','module'=>"wbm_return_notice_record",'pid'=>$notice_record_id);
            load_model('pur/PurStmLogModel')->insert($log);
            
            
            $this->commit();
            if($ret['status'] == 1) {   
                return $this->format_ret(1,$notice_record_id,'操作成功');
            }
        } else {
            //查询批发退货通知单是否确认
            $sql = "SELECT return_notice_code,return_notice_record_id,is_check FROM wbm_return_notice_record WHERE jx_return_code = '{$record['data']['return_record_code']}';";
            $notice_record = $this->db->get_row($sql);
            if((isset($notice_record['is_check']) && $notice_record['is_check'] == 1) && $sum_finish_num != 0) {
                return $this->format_ret(-1,'','关联的批发退货通知单已确认，不能取消确认');
            }
            
            //修改状态
            $ret = parent:: update(array($field => $active), array('fx_purchaser_return_id' => $id));
            if($ret['status'] != 1) {
                return $ret;
            }
            
            if(!((!empty($notice_record) && $notice_record['is_check'] == 1) && $sum_finish_num != 0)) {
                //是否有关联的退货单
                $return = load_model('wbm/ReturnRecordModel');
                $return_record = $return->is_exists($notice_record['return_notice_code'],'relation_code');
                if(!empty($return_record['data'])) { //关联退单一并删除
                    $ret = $return->delete($return_record['data']['return_record_id']);
                    if($ret['status'] < 0) {
                        return $ret;
                    }
                }
                
                //删除关联的批发退货通知单
                $ret = parent::delete_exp('wbm_return_notice_record',array('jx_return_code' => $record['data']['return_record_code']));
                if($ret != TRUE) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '删除关联批发退货通知单失败。');
                }
                //删除批发退货通知单明细
                $ret = parent::delete_exp('wbm_return_notice_detail_record', array('return_notice_record_id' => $notice_record['return_notice_record_id']));
                if($ret != TRUE) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '删除关联批发退货通知单失败。');
                }
            }
            return $this->format_ret('1','','操作成功');
        }
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn($djh = 0) {
        if ($djh > 0) {
            $sql = "select fx_purchaser_return_id from {$this->table} order by fx_purchaser_return_id desc limit 1 ";
            $data = $this->db->get_all($sql);
            if ($data) {
                $djh = intval($data[0]['fx_purchaser_return_id']) + 1;
            } else {
                $djh = 1;
            }
        } else {
            $djh = $djh + 1;
        }
        require_lib('comm_util', true);
        $date = substr(date("Ymd"),2);
        $new_jdh = "JXTH" . $date . add_zero($djh,4);
        $sql = "select fx_purchaser_return_id  from {$this->table}  where return_record_code=:return_record_code ";
        $sql_value = array(':return_record_code' => $new_jdh);
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
        $record = $this->get_row(array('fx_purchaser_return_id' => $id));
        $store_code = $record['data']['store_code'];
        $barcode_arr = $barcode_num = array();
        $error_msg = '';
        $err_num = 0;

        //未开启批次导入库存方法
        if ($is_lof == '1') {
            $num = $this->read_csv_lof($file, $barcode_arr, $barcode_num);
            $import_count = count($barcode_num);
            $ret = $this->get_barcode_by_sku($barcode_arr, $barcode_num,$error_msg,$err_num);
        } else {
            $num = $this->read_csv_sku($file, $barcode_arr, $barcode_num);
            $import_count = count($barcode_num);
            $ret = $this->get_barcode_by_sku($barcode_arr, $barcode_num,$error_msg,$err_num);
            if ($ret['status'] < 1) {
                return $ret;
            }
            //没有开启批次的 查询默认批次 如果批次表里 没有，在批次表里添加goods_lof
            $sql_values = array();
            $sku_str = $this->arr_to_in_sql_value($barcode_arr,'sku',$sql_values);
            $sql = "select g.barcode,g.sku,l.lof_no,l.production_date,l.type from goods_lof l "
                    . "INNER JOIN goods_sku g ON g.sku=l.sku  where"
                    . " g.sku in ('$sku_str') group by l.sku";
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
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.sku,g.price,g.purchase_price,g.sell_price  from
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
                if(!(isset($custom_arr[$record['data']['custom_code']]) && in_array($val['barcode'],$custom_arr[$record['data']['custom_code']]))) {
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
                        $error_msg[] = array($val['barcode'] => '数量不能为空或零');
                        $err_num ++;
                        unset($barcode_num[$val['sku']]);
                    }
                }
            }
            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $detail_data_lof);
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'fx_return', $detail_data_lof);
            if ($ret['status']<1) {
                return $ret;
            }
            //入库单明细添加
            $ret = load_model('fx/PurchaseReturnRecordDetailModel')->add_detail_action($record['data']['return_record_code'], $detail_data_lof);
            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '增加明细', 'module' => "purchase", 'pid' => $id);
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
            // return $this->format_ret(-1,'',"找不到对应条码".  implode(",", $result));
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
        $record = $this->get_row(array('fx_purchaser_return_id' => $id));
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $data);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'fx_return', $data);
       if ($ret['status']<1) {
            return $ret;
       }
        
        //增加明细        
        $ret = load_model('fx/PurchaseReturnRecordDetailModel')->add_detail_action($record['data']['return_record_code'], $data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'finish_status' => '未入库', 'action_name' => '增加明细', 'module' => "fx_purchase_return", 'pid' => $id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
    }

    function do_settlement($record_code_id,$type = ''){
        $record = $this->get_row(array('fx_purchaser_return_id' => $record_code_id));
        $record = $record['data'];
        if(empty($record)){
            return $this->format_ret(-1,'','单据信息为空不能结算！');
        }
        //$fenxiao_power = load_model('oms/SellRecordModel')->check_fenxiao_power($record);
        $this->begin_trans();
        try {
            //结算
            $arr = array('is_settlement' => 1);
            $ret = $this->db->update('fx_purchaser_return_record', $arr, array('fx_purchaser_return_id' => $record_code_id));
            if ($ret !== true) {
                $this->rollback();
                return $this->format_ret(-1, '', '经销采购退货单结算操作失败');
            }
            //修改资金账户
            if(!empty($type) && $type == 'fx_purchase_return_finish') {
                $type = 'fx_purchase_return_finish';
            } else {
                $type = "fx_purchase_return_settlement";
            }
            $detail = load_model('fx/PurchaseReturnRecordDetailModel')->get_detail_by_code($record['return_record_code']);
            //计算商品总金额
            $money = array_map(function($val) {
                return (float) $val['finish_num'] * $val['price'];
            }, $detail);
            $sum_money = array_sum($money);
            $record['sum_money'] = $sum_money;
            if($sum_money > 0) {
                $ret = $this->js_income_pay($record,$type);
                if($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
            }
            
            $store_status = $record['num'] > $record['finish_num'] && $record['finish_num'] > 0 ? '部分入库' : '已入库';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => $store_status, 'action_name' => '结算', 'module' => "fx_purchase_return", 'pid' => $record_code_id,'action_note' => '单据结算');
            load_model('pur/PurStmLogModel')->insert($log);
            
            $this->commit();
            return $this->format_ret(1,'','结算成功');
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
            $money = $record['sum_money'] + $record['express_money'];
            $account_data = array(
                'money' => $money,
                'record_code' => $record['return_record_code'],
                'custom_code' => $record['custom_code'],
                'type' => $type,
            );
            //修改资金账户
            $ret = load_model('fx/BalanceOfPaymentsModel')->create_fx_income_pay($account_data);
            return $ret;
        }
        return $this->format_ret(1);
    }
    
    /*function do_unsettlement($record_code_id){
        $record = $this->get_row(array('fx_purchaser_return_id' => $record_code_id));
        $record = $record['data'];
        $detail = $this->db->get_all("select goods_code,goods_name,spec1_code,spec2_code,barcode,sku,price AS trade_price,money,finish_num,num,goods_property,cost_price,remark from fx_purchaser_return_record_detail where pid = :pid",array(":pid" => $record_code_id));
        $finish_num = 0;
        foreach($datail as $val) {
            $finish_num += $val['finish_num'];
        }
        if(empty($record)){
            return $this->format_ret(-1,'','单据信息为空不能取消结算！');
        }
        //查询批发退货通知单是否确认
        $sql = "SELECT return_notice_code,return_notice_record_id,is_check FROM wbm_return_notice_record WHERE jx_return_code = '{$record['return_record_code']}'";
        $notice_record = $this->db->get_row($sql);
        if (isset($notice_record['is_check']) && $notice_record['is_check'] == 1 && $finish_num != 0) { //实际入库数大于零不能取消结算
            return $this->format_ret(-1, '', '关联的批发退货通知单已确认，不能取消结算');
        }
        $this->begin_trans();
        try {
            if((!empty($notice_record) && $notice_record['is_check'] == 0) || $finish_num == 0) {
                //删除关联的批发退货通知单
                $ret = parent::delete_exp('wbm_return_notice_record',array('jx_return_code' => $record['return_record_code']));
                if($ret != TRUE) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '删除关联批发退货通知单失败。');
                }
                //删除批发退货通知单明细
                $ret = parent::delete_exp('wbm_return_notice_detail_record', array('return_notice_record_id' => $notice_record['return_notice_record_id']));
                if($ret != TRUE) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '删除批发退货通知单明细失败。');
                }
            }
            //取消结算
            $arr = array('is_settlement' => 0);
            $ret = $this->db->update('fx_purchaser_return_record', $arr, array('fx_purchaser_return_id' => $record_code_id));
            if ($ret != true) {
                $this->rollback();
                return $this->format_ret(-1, '', '经销采购退货单取消结算操作失败');
            }
            //修改资金账户
            $ret = $this->js_income_pay($record,'fx_purchase_return_unsettlement');
            if($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未入库', 'action_name' => '取消结算', 'module' => "fx_purchase_return", 'pid' => $record_code_id);
            $ret = load_model('pur/PurStmLogModel')->insert($log);
            
            $this->commit();
            return $this->format_ret('1','','操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }*/
}
