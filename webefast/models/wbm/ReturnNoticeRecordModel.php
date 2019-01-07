<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lang('stm');

class ReturnNoticeRecordModel extends TbModel {

    public $return_type = array(
        'inferior_return' => '次品退货',
    );
    public $order_status = array(
// 			'is_check' => '未确认',
        'checked' => '已确认',
        'returned' => '已生成退货单',
        'finished' => '已完成',
    );

    function get_table() {
        return 'wbm_return_notice_record';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl
			LEFT JOIN wbm_return_notice_detail_record r2 on rl.return_notice_code = r2.return_notice_code
			WHERE 1";
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        $sql_values = array();
        // 调整仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ( " . $str . " ) ";
        }
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('custom_power'));
        if ($ret_cfg['custom_power'] == 1) { //开启分销商业务权限
            $custom_code = isset($filter['custom_code']) ? $filter['custom_code'] : null;
            $sql_main .= load_model('base/CustomModel')->get_sql_purview_custom('rl.custom_code', $custom_code);
        } else {
            if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
                $arr = explode(',', $filter['custom_code']);
                $str = $this->arr_to_in_sql_value($arr, 'custom_code', $sql_values);
                $sql_main .= " AND rl.custom_code in ({$str}) ";
            }
        }
        //单据状态
        if (isset($filter['check_status']) && $filter['check_status'] != '') {
            $sql_main .= " AND rl.is_check = :is_check";
            $sql_values[':is_check'] = $filter['check_status'];
        }
        if (isset($filter['return_status']) && $filter['return_status'] != '') {
            $sql_main .= " AND rl.is_return = :is_return";
            $sql_values[':is_return'] = $filter['return_status'];
        }
        if (isset($filter['finish_status']) && $filter['finish_status'] != '') {
            $sql_main .= " AND rl.is_finish = :is_finish";
            $sql_values[':is_finish'] = $filter['finish_status'];
        }

        if (isset($filter['return_type_code']) && $filter['return_type_code'] != '') {
            $arr = explode(',', $filter['return_type_code']);
            $str = $this->arr_to_in_sql_value($arr, 'return_type_code', $sql_values);
            $sql_main .= " AND rl.return_type_code in ( " . $str . " ) ";
        }
        //经销退货单编号
        if (isset($filter['jx_return_code']) && $filter['jx_return_code'] != '') {
            $sql_main .= " AND (rl.jx_return_code LIKE :jx_return_code )";
            $sql_values[':jx_return_code'] = '%' . $filter['jx_return_code'] . '%';
        }

        //下单日期
        if (isset($filter['order_time_start']) && $filter['order_time_start'] != '') {
            $sql_main .= " AND (rl.order_time >= :order_time_start )";
            $sql_values[':order_time_start'] = $filter['order_time_start'];
        }
        if (isset($filter['order_time_end']) && $filter['order_time_end'] != '') {
            $sql_main .= " AND (rl.order_time <= :order_time_end )";
            $sql_values[':order_time_end'] = $filter['order_time_end'];
        }
        //单据编号
        if (isset($filter['return_notice_code']) && $filter['return_notice_code'] != '') {
            $sql_main .= " AND (rl.return_notice_code LIKE :return_notice_code )";
            $sql_values[':return_notice_code'] = $filter['return_notice_code'] . '%';
        }

        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }

        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND (r2.barcode LIKE :barcode )";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        //备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND (rl.remark LIKE :remark )";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
        }
        //导出
        if (isset($filter['ctl_type']) && $filter['ctl_type'] == 'export') {
            return $this->wbm_return_notice_record_export($filter, $sql_main, $sql_values);
        }

        //$select = 'rl.*,r2.goods_name,r2.goods_name,r2.weight';
        $select = 'rl.*';
        $sql_main .= " GROUP BY rl.return_notice_code order by order_time desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['money'] = round($value['money'], 2);
            $data['data'][$key]['different_num'] = $value['num'] - $value['finish_num'];
            $data_name = load_model('base/RecordTypeModel')->get_by_field('record_type_code', $value['return_type_code'], 'record_type_name');
            $data['data'][$key]['type_name'] = $data_name['data']['record_type_name'];
            $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($value['store_code']);
            if ($wms_system_code !== FALSE) {
                $data['data'][$key]['is_wms'] = 1;
            } else {
                $data['data'][$key]['is_wms'] = 0;
            }
        }
        filter_fk_name($data['data'], array('store_code|store', 'custom_code|custom'));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 导出明细
     * @param $filter
     * @param $sql_main
     * @param $sql_values
     * @return array
     */
    function wbm_return_notice_record_export($filter, $sql_main, $sql_values) {
        $select = 'rl.*,r2.sku,r2.num as detail_num,r2.money AS detail_money,r2.finish_num AS detail_finish_num,r2.trade_price AS detail_trade_price,r2.price AS detail_price';
        $sql_main .= " ORDER BY rl.order_time DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        foreach ($data['data'] as $key => &$value) {
            $value['money'] = round($value['money'], 2);
            $value['is_check_name'] = ($value['is_check'] == 0) ? '未确认' : '已确认';
            $value['is_return_name'] = ($value['is_return'] == 0) ? '未生成退货单' : '已生成退货单';
            $value['is_finish_name'] = ($value['is_finish'] == 0) ? '未完成' : '已完成';

            $key_arr = array('goods_name', 'goods_code', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'barcode',);
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($value, $sku_info);
            //获取扩展属性
            if ($property_power) {
                $goods_property = load_model('prm/GoodsModel')->get_export_property($value['goods_code']);
                $value = $goods_property != -1 && is_array($goods_property) ? array_merge($value, $goods_property) : $value;
            }
        }
        filter_fk_name($data['data'], array('store_code|store', 'custom_code|custom'));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 编辑一条记录
     */
    public function edit_action($data, $where) {
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        if (!isset($where['return_notice_record_id']) && !isset($where['return_notice_code'])) {
            return $this->format_ret('-1', array(), 'RECORD_RELATION_ERROR_CODE');
        }
        $result = $this->get_row($where);
        if (1 != $result['status']) {
            return $this->format_ret('-1', array(), 'RECORD_NO_ERROR_DATA');
        }
        if (1 == $result['data']['is_check']) {
            return $this->format_ret('-1', array(), 'RECORD_DELETE_ERROR_CHECK!');
        }

        //if(isset($data['store_code'])){
        //$ret = load_model('stm/GoodsInvLofRecordModel')->modify_store_code($data['store_code'],$result['data']['record_code']);
        //}
        //更新主表数据
        return parent::update($data, $where);
    }

    /*
     * 删除记录
     * */

    function do_delete($return_notice_code) {
//		if (!load_model('sys/PrivilegeModel')->check_priv('wbm/return_notice_record/do_delete')) {
//			return $this->format_ret(-1, array(), '无权访问');
//		}
        $record = $this->is_exists($return_notice_code, 'return_notice_code');
        if (isset($record['data']['is_check']) && 1 == $record['data']['is_check']) {
            return $this->format_ret(false, array(), 'PLAN_DELETE_ERROR_CHECK');
        }
        $this->begin_trans();
        try {
            $ret = load_model('wbm/ReturnNoticeDetailRecordModel')->do_delete($return_notice_code);
            if ($ret['status'] != 1) {
                $this->rollback(); //事务回滚
                return $ret;
            }
            $ret = parent::delete(array('return_notice_code' => $return_notice_code));
            $this->commit();
            //日志
// 			$log = array('user_id'=>CTX()->get_session('user_id'),'user_code'=>CTX()->get_session('user_code'),'ip'=>'','add_time'=>date('Y-m-d H:i:s'),'sure_status'=>"确认",'finish_status'=>'未完成','action_name'=>"删除",'module'=>"order_record",'pid'=>$order_record_id);
// 			$ret1 = load_model('pur/PurStmLogModel')->insert($log);
            return $ret;
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), 'DATABASE_ERROR' . $e->getMessage());
        }
    }

    /*
     * 添加新纪录
     */

    function insert($stock_adjus) {
        $status = $this->valid($stock_adjus);
        if ($status < 1) {
            return $this->format_ret('-1', '', $status);
        }

        $ret = $this->is_exists($stock_adjus['return_notice_code']);

        if (!empty($ret['data']))
            return $this->format_ret('-1', '', 'RECORD_ERROR_UNIQUE_CODE1');
        $stock_adjus['create_time'] = date('Y-m-d H:i:s');
        $stock_adjus['create_person'] = CTX()->get_session('user_code');
        $stock_adjus['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $stock_adjus['remark']);
        return parent::insert($stock_adjus);
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['return_notice_code']) || !valid_input($data['return_notice_code'], 'required')))
            return 'RECORD_ERROR_CODE';
        return 1;
    }

    public function is_exists($value, $field_name = 'return_notice_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    function get_by_id($id) {
        $data = $this->get_row(array('return_notice_record_id' => $id));
        filter_fk_name($data['data'], array('store_code|store', 'custom_code|custom'));
        $sql = "select is_store_in from wbm_return_record where relation_code = '{$data['data']['return_notice_code']}'";
        $is_store_in = $this->db->get_row($sql);
        $data['is_store_in'] = $is_store_in;
        return $data;
    }

    function check_status($return_notice_record) {
        $record_detail = load_model('wbm/ReturnNoticeDetailRecordModel')->is_exists($return_notice_record['data']['return_notice_code'], 'return_notice_code');
        $sql = "SELECT * FROM wbm_return_record WHERE relation_code = '{$return_notice_record['data']['return_notice_code']}';";
        $return_record = $this->db->get_row($sql);
        if (empty($return_notice_record) || empty($record_detail)) {
            return $this->format_ret(-1, '', '退货通知单信息不存在！');
        }
        if ($return_notice_record['data']['is_check'] == 0) {
            return $this->format_ret(-1, '', '未确认退货通知单不能生成退单！');
        }
        if ($return_notice_record['data']['is_finish'] == 1) {
            return $this->format_ret(-1, '', '已完成退货通知单不能生成退单！');
        }
//                if(!empty($return_notice_record['data']['jx_return_code']) && !empty($return_record)) {
//                        return $this->format_ret(-1,'','该单号是经销退货单，只能生成一张退单');
//                }
        return $this->format_ret(1, $record_detail);
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn() {
        $sql = "select return_notice_record_id  from {$this->table}   order by return_notice_record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['return_notice_record_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "PTTZ" . date("Ymd") . add_zero($djh);
        return $jdh;
    }

    //获取退单类型
    function get_return_type() {
        $sql = 'select record_type_code,record_type_name from base_record_type where record_type_property=3';
        $ret = $this->db->get_all($sql);
        $i = 0;
        foreach ($ret as $value) {
            $return_type[$i]['return_type_code'] = $value['record_type_code'];
            $return_type[$i]['return_type_name'] = $value['record_type_name'];
            $i++;
        }
        return $return_type;
    }

    //获取单据状态
    function get_order_status() {
        $order_status = array();
        $ret = $this->order_status;
        $i = 0;
        foreach ($ret as $key => $status) {
            $order_status[$i]['order_status_code'] = $key;
            $order_status[$i]['order_status_name'] = $status;
            $i++;
        }
        return $order_status;
    }

    //
    function update_check_record_code($active, $field, $record_code) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $details = load_model('pur/OrderRecordDetailModel')->get_all(array('record_code' => $record_code));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret = parent:: update(array($field => $active), array('record_code' => $record_code));
        return $ret;
    }

    function update_check($active, $field, $return_notice_code) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $ret = $this->get_row(array('return_notice_code' => $return_notice_code));
        $record_date = $ret['data'];
        if ($active == 0) { // 取消确认
            if (isset($record_date['jx_return_code']) && !empty($record_date['jx_return_code'])) {
                return $this->format_ret('-1', '', '经销退单生成的通知单不允许取消确认');
            }
        }
        $details = load_model('wbm/ReturnNoticeDetailRecordModel')->get_all(array('return_notice_code' => $return_notice_code));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $this->begin_trans();
        $ret = parent:: update(array($field => $active), array('return_notice_code' => $return_notice_code));

        if ($ret['status'] > 0 && $field == 'is_check') {

            $ret = load_model('wms/WmsEntryModel')->check_wms_store($record_date['store_code'], 'wbm_return_notice');

            if ($ret['status'] > 0) {//判定是否是WMS第三方仓储
                $record = $this->get_row(array('return_notice_code' => $return_notice_code));
                if ($active == 1) {

                    $ret = load_model('wms/WmsEntryModel')->add($return_notice_code, 'wbm_return_notice', $record['data']['store_code']);
                    if ($ret['status'] < 0) {
                        $this->rollback();
                        return $ret;
                    } else {
                        $ret['status'] = 1;
                    }
                } else {
                    $ret = load_model('wms/WmsEntryModel')->cancel($return_notice_code, 'wbm_return_notice', $record['data']['store_code']);
                    if ($ret['status'] < 0) {
                        $this->rollback();
                        return $ret;
                    } else {
                        $ret['status'] = 1;
                    }
                }
            } else {
                $this->commit();
                return $this->format_ret(1, '', '操作成功');
            }
        }

        $this->commit();

        return $ret;
    }

    function do_finish($return_notice_code, $id) {
        $record = $this->is_exists($return_notice_code, 'return_notice_code');
        if (!isset($record['data']['is_check']) && 0 == $record['data']['is_check']) {
            return $this->format_ret(false, array(), 'PLAN_DELETE_ERROR_CHECK');
        }
        $action = '完成';
        if ($record['data']['is_return'] != 1) {
            $action = '强制完成';
        }
        $this->begin_trans();
        $ret = parent:: update(array('is_finish' => 1), array('return_notice_code' => $return_notice_code));
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '完成', 'action_name' => '完成', 'module' => "wbm_return_notice_record", 'pid' => $record['data']['return_notice_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        $jx_data = load_model('fx/PurchaseReturnRecordModel')->is_exists($record['data']['jx_return_code']);
        if (isset($record['data']['jx_return_code']) && !empty($record['data']['jx_return_code']) && $jx_data['data']['is_settlement'] == 0) { //经销退货单生成的,回写主表信息
            //回写主表信息
            $ret1 = load_model('fx/PurchaseReturnRecordDetailModel')->mainWriteBackfinish($return_notice_code, 'fx_purchase_return_finish');
            if ($ret1['status'] != 1) {
                $this->rollback(); //事务回滚
                return $ret1;
            }
        }
        $this->commit();
        return $ret;
    }

    function imoprt_detail($id, $file) {
        $ret = $this->get_row(array('return_notice_record_id' => $id));
        $store_code = $ret['data']['store_code'];
        $sku_arr = $sku_num = $price_arr = array();
        $error_msg = array();
        $err_num = 0;

        $num = $this->read_csv_sku($file, $sku_arr, $sku_num, $price_arr);
        $sku_count = count($sku_arr);
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'barcode', $sql_values);
        $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.price,g.goods_name,g.trade_price from goods_sku b
		inner join  base_goods g ON g.goods_code = b.goods_code

		where b.barcode in({$sku_str}) group by b.barcode ";
        $detail_data = $this->db->get_all($sql, $sql_values);
        foreach ($detail_data as $key => $val) {
            if (intval($sku_num[$val['barcode']]['num']) > 0) {
                $detail_data[$key]['num'] = $sku_num[$val['barcode']];
                $detail_data[$key]['trade_price'] = is_numeric($price_arr[$val['barcode']]) ? $price_arr[$val['barcode']] : 0;

                if ($detail_data[$key]['trade_price'] == 0 && $val['trade_price'] > 0) {
                    $detail_data[$key]['trade_price'] = $val['trade_price'];
                }

                unset($sku_num[$val['barcode']]);
            } else {
                $error_msg[] = array($val['barcode'] => '数量不能为空');
                $err_num ++;
                unset($sku_num[$val['barcode']]);
            }
        }
        //通知单明细添加
        $ret = load_model('wbm/ReturnNoticeDetailRecordModel')->add_detail_action($id, $detail_data);

        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '导入明细', 'module' => "wbm_return_notice_record", 'pid' => $id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        $ret['data'] = '';
        if (!empty($sku_num)) {
            $sku_error = array_keys($sku_num);
            foreach ($sku_error as $err) {
                $error_msg[] = array($err => '系统不存在该条码信息');
                $err_num ++;
            }
        }
        $success_num = $sku_count - $err_num;
        $message = '导入成功' . $success_num . '条';
        if ($err_num > 0 || !empty($error_msg)) {
            $message .= ',' . '失败数量:' . $err_num . '条';
            $fail_top = array('商品条码', '错误信息');
            $file_name = load_model('wbm/ReturnRecordModel')->create_import_fail_files($fail_top, $error_msg);
//                $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    function read_csv_sku($file, &$sku_arr, &$sku_num, &$price_arr) {
        //    $key_arr = array('0'=>'sku','1'=>'num');
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $barcode = trim($row[0]);
                    $sku_arr[] = $barcode;
                    $sku_num[$barcode] = trim($row[1]);
                    $price_arr[$barcode] = trim($row[2]);
                }
            }
            $i++;
        }
        fclose($file);
        //var_dump($sku_arr,$sku_num,$price_arr);
        return $i;
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

    function get_by_code($notice_code) {
        $data = $this->get_row(array('return_notice_code' => $notice_code));
        filter_fk_name($data['data'], array('store_code|store', 'custom_code|custom'));
        $data['data']['return_type_name'] = $this->return_type[$data['data']['return_type_code']];
        return $data;
    }

    function api_return_notice_get($param) {
        //可选字段
        $key_option = array(
            's' => array('start_time', 'end_time', 'store_code', 'is_check', 'custom_code', 'is_finish'),
            'i' => array('page', 'page_size')
        );
        $arr_option = array();
        valid_assign_array($param, $key_option, $arr_option);
        $arr_deal = $arr_option;
        if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }
        //清空无用数据
        unset($arr_option);
        unset($param);
        $select = "r1.return_notice_code as record_code,r1.custom_code,r1.store_code,r1.rebate,r1.order_time,r1.num,r1.return_type_code as type_code,r1.remark ";
        $sql_values = array();
        $sql_join = "";
        $sql_main = " from wbm_return_notice_record r1 {$sql_join} where 1";
        $sql_values[":is_check"] = 1;
        $sql_values[":is_finish"] = 0;
        $sql_values[":start_time"] = date("Y-m-d") . " 00:00:00";
        foreach ($arr_deal as $key => $val) {
            if ($key != 'page' && $key != 'page_size') {
                if ($key == 'start_time') {
                    $sql_values[":{$key}"] = $val;
                }
                if ($key == 'end_time') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND r1.lastchanged <=:{$key}";
                }
                if ($key == 'store_code') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND r1.store_code =:{$key}";
                }
                if ($key == 'is_check') {
                    $sql_values[":{$key}"] = $val;
                }
                if ($key == 'custom_code') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND r1.custom_code =:{$key}";
                }
                if ($key == 'is_finish') {
                    $sql_values[":{$key}"] = $val;
                }
            }
        }
        $sql_main .= " AND r1.is_check =:is_check";
        $sql_main .= " AND r1.is_finish =:is_finish";
        $sql_main .= " AND r1.lastchanged >=:start_time";
        $sql_main .= ' group by record_code ';
        $ret = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select, true);
        if (empty($ret['data'])) {
            return $this->format_ret(-1002, '', API_RETURN_MESSAGE_10002);
        }
        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status, $ret);
    }

    //查询是否有未出库订单
    function out_relation($id) {
        $record = $this->get_row(array('return_notice_record_id' => $id));
        $record_code = $record['data']['return_notice_code'];
        $sql = " select count(*) as cnt from wbm_return_record where relation_code = :record_code AND is_store_in = '0' ";
        $arr = array(':record_code' => $record_code);
        $data = $this->db->get_all($sql, $arr);
        if (isset($data[0]['cnt']) && $data[0]['cnt'] > 0) {
            return $this->format_ret('-1', '', '存在未验收的批发退货单，是否继续');
        }
        return $this->format_ret('1');
    }

    public function add_detail($param) {
        $ret = load_model('wbm/ReturnNoticeDetailRecordModel')->add_detail_action($param['record_id'], $param['detail']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '增加明细', 'module' => "wbm_return_notice_record", 'pid' => $param['record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }

        return $ret;
    }

}
