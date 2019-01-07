<?php

require_model('tb/TbModel');
require_lang('pur');
require_lib('comm_util', true);

/**
 * 采购通知单相关业务
 */
class OrderRecordModel extends TbModel {

    function get_table() {
        return 'pur_order_record';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = " LEFT JOIN  pur_order_record_detail r2 ON rl.record_code=r2.record_code ";
        $sql_main = "FROM {$this->table} rl {$sql_join}  WHERE 1 ";
        $sql_values = array();
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $sql_main .= load_model('base/SupplierModel')->get_sql_purview_supplier('rl.supplier_code', $filter_supplier_code);
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }

        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND r2.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {

//                        $sql_main .= " AND r2.sku  in(select sku from goods_barcode where barcode LIKE :barcode ) ";
//			$sql_values[':barcode'] =  '%'.$filter['barcode'] . '%';
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND r2.sku in({$sku_str}) ";
            }
        }
        //单据状态
        if (isset($filter['check_status']) && $filter['check_status'] !== '') {
            $sql_main .= " AND rl.is_check = :check_status ";
            $sql_values[':check_status'] = $filter['check_status'];
        }
        if (isset($filter['finish_status']) && $filter['finish_status'] !== '') {
            $sql_main .= " AND rl.is_finish = :finish_status ";
            $sql_values[':finish_status'] = $filter['finish_status'];
        }
        if (isset($filter['execute_status']) && $filter['execute_status'] !== '') {
            $sql_main .= " AND rl.is_execute = :execute_status ";
            $sql_values[':execute_status'] = $filter['execute_status'];
        }

        //计划日期
        if (isset($filter['planned_time_start']) && $filter['planned_time_start'] != '') {
            $sql_main .= " AND (rl.planned_time >= :planned_time_start )";
            $sql_values[':planned_time_start'] = $filter['planned_time_start'];
        }
        if (isset($filter['planned_time_end']) && $filter['planned_time_end'] != '') {
            $sql_main .= " AND (rl.planned_time <= :planned_time_end )";
            $sql_values[':planned_time_end'] = $filter['planned_time_end'];
        }

        //入库期限
        if (isset($filter['in_time_start']) && $filter['in_time_start'] != '') {
            $sql_main .= " AND (rl.in_time >= :in_time_start )";
            $sql_values[':in_time_start'] = $filter['in_time_start'] . ' 00:00:00';
        }
        if (isset($filter['in_time_end']) && $filter['in_time_end'] != '') {
            $sql_main .= " AND (rl.in_time <= :in_time_end )";
            $sql_values[':in_time_end'] = $filter['in_time_end'] . ' 23:59:59';
        }
        //下单时间
        if (isset($filter['order_time_start']) && $filter['order_time_start'] != '') {
            $sql_main .= " AND (rl.order_time >= :order_time_start )";
            $sql_values[':order_time_start'] = $filter['order_time_start'];
        }
        if (isset($filter['order_time_end']) && $filter['order_time_end'] != '') {
            $sql_main .= " AND (rl.order_time <= :order_time_end )";
            $sql_values[':order_time_end'] = $filter['order_time_end'];
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        //计划编号
        if (isset($filter['relation_code']) && $filter['relation_code'] != '') {
            $sql_main .= " AND (rl.relation_code LIKE :relation_code )";
            $sql_values[':relation_code'] = $filter['relation_code'] . '%';
        }
        //原单号
        if (isset($filter['init_code']) && $filter['init_code'] != '') {
            $sql_main .= " AND (rl.init_code = :init_code )";
            $sql_values[':init_code'] = $filter['init_code'];
        }
        //备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND (rl.remark LIKE :remark )";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
        }
        //是否有差异订单
        if (isset($filter['difference_models']) && $filter['difference_models'] != '') {
            if ($filter['difference_models'] == 1) {
                $sql_main .= " AND (rl.num != rl.finish_num )";
            } else {
                $sql_main .= " AND (rl.num = rl.finish_num )";
            }
        }
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) /*&& $filter['ctl_export_conf'] != 'planned_record_list'*/ ) {
            $select = 'rl.*,r2.spec1_code,r2.goods_code,r2.spec2_code,r2.sku,r2.price,r2.num as num_detail,r2.finish_num as finish_detail,r2.money as price_detail,r2.rebate as rebate_detail';
        }else{
            $select = ' DISTINCT rl.*';
        }       
            $sql_main .= " order by rl.order_time desc, rl.record_code desc";//var_dump($sql_main);die;

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select,true);

        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $filter['user_id']);
        foreach ($data['data'] as $key => $value) {
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $type_data = load_model('base/RecordTypeModel')->get_by_code($value['pur_type_code']);
            $data['data'][$key]['record_type'] = $type_data['data']['record_type_name'];
            $data['data'][$key]['barcode'] = $sku_info['barcode'];
            $data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $data['data'][$key]['goods_name'] = $sku_info['goods_name'];
            $data['data'][$key]['money'] = round($value['money'], 2);
            $data['data'][$key]['price1'] = $value['price'] * $value['rebate_detail'];
            if ($status['status'] != 1) {
                $data['data'][$key]['money'] = '****';
                $data['data'][$key]['price_detail'] = '****';
                $data['data'][$key]['price'] = '****';
                $data['data'][$key]['price1'] = '****';
            }
            $data['data'][$key]['different_num'] = $value['num'] - $value['finish_num'];
            $data['data'][$key]['different_num_detail'] = $value['num_detail'] - $value['finish_detail'];
            $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($value['store_code']);
            if ($wms_system_code !== FALSE) {
                $data['data'][$key]['is_wms'] = 1;
            } else {
                $data['data'][$key]['is_wms'] = 0;
            }
            $data['data'][$key]['is_check_name'] = ($value['is_check'] == 0) ? '未确认' : '确认';
            $data['data'][$key]['is_execute_name'] = ($value['is_execute'] == 0) ? '未生成入库单' : '已生成入库单';
            $data['data'][$key]['is_finish_name'] = ($value['is_finish'] == 0) ? '未完成' : '已完成';

        }
        //获取扩展属性
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        if ($property_power) {
            foreach ($data['data'] as &$val) {
                $goods_property = load_model('prm/GoodsModel')->get_export_property($val['goods_code']);
                $val = $goods_property != -1 && is_array($goods_property) ? array_merge($val, $goods_property) : $val;
            }
        }
        filter_fk_name($data['data'], array('store_code|store', 'supplier_code|supplier'));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    /**
     * 编辑一条记录
     */
    public function edit_action($data, $where) {
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        if (!isset($where['order_record_id']) && !isset($where['record_code'])) {
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
        //更新明细表
        $this->db->query("UPDATE pur_order_record_detail SET money = price*{$data['rebate']}*num WHERE pid = {$where['order_record_id']}");
        $total_money = $this->db->get_row("SELECT sum(money) as total_money FROM pur_order_record_detail WHERE pid = {$where['order_record_id']} ");
        $data['money'] = $total_money['total_money'];
        //更新主表数据
        return parent::update($data, $where);
    }

    /*
     * 删除记录
     * */

    function delete($order_record_id) {
        $record = $this->is_exists($order_record_id, 'order_record_id');
        if (isset($record['data']['is_check']) && 1 == $record['data']['is_check']) {
            return $this->format_ret(false, array(), 'PLAN_DELETE_ERROR_CHECK');
        }
        $this->begin_trans();
        try {
            $ret = load_model('pur/OrderRecordDetailModel')->delete_pid($order_record_id);
            if ($ret['status'] != 1) {
                $this->rollback(); //事务回滚
                return $ret;
            }
            $ret = parent::delete(array('order_record_id' => $order_record_id));
            $this->commit();
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "确认", 'finish_status' => '未完成', 'action_name' => "删除", 'module' => "order_record", 'pid' => $order_record_id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
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

        $ret = $this->is_exists($stock_adjus['record_code']);

        if (!empty($ret['data']))
            return $this->format_ret('-1', '', 'RECORD_ERROR_UNIQUE_CODE1');
        $stock_adjus['is_add_time'] = date('Y-m-d H:i:s');
        $stock_adjus['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $stock_adjus['remark']);
        return parent::insert($stock_adjus);
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['record_code']) || !valid_input($data['record_code'], 'required')))
            return 'RECORD_ERROR_CODE';
        return 1;
    }

    public function is_exists($value, $field_name = 'record_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    function get_by_id($id) {
        $data = $this->get_row(array('order_record_id' => $id));
        filter_fk_name($data['data'], array('store_code|store', 'supplier_code|supplier'));
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        if ($status['status'] != 1 && !empty($data['data'])) {
            $data['data']['money'] = '****';
        }
        return $data;
    }

    /**
     * 采购类型列表
     */
    function get_pur_type_list() {
        $sql = "select record_type_code,record_type_name  from base_record_type where record_type_property = '0'  ";
        $data = $this->db->get_all($sql);
        return $data;
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn() {
        $sql = "select order_record_id  from {$this->table}   order by order_record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['order_record_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "JR" . date("Ymd") . add_zero($djh);
        return $jdh;
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

    /**
     * 检查是否有未完成的入库单
     * @param string $record_code 通知单号
     * @return array 结果
     */
    function check_exists_no_accept_pur($record_code) {
        $sql = 'SELECT COUNT(1) FROM pur_purchaser_record WHERE relation_code=:relation_code AND is_check_and_accept=0';
        $data = $this->db->get_value($sql, array(':relation_code' => $record_code));
        if ($data > 0) {
            return $this->format_ret(-1, '', '存在未完成的入库单');
        }
        return $this->format_ret(1);
    }

    //判断完成
    function update_finish($record_code) {

        $sql = " select count(*) as cnt  from pur_order_record_detail where  record_code = :record_code AND   finish_num >= num  ";
        $arr = array(':record_code' => $record_code);
        $data = $this->db->get_all($sql, $arr);
        $sql = "select count(*) as cnt  from pur_order_record_detail where  record_code = :record_code  ";
        $data1 = $this->db->get_all($sql, $arr);
        if (isset($data[0]['cnt']) && isset($data1[0]['cnt']) && ( $data[0]['cnt'] == $data1[0]['cnt'] )) {
            $ret['status'] = '1';
            $ret['data'] = '';
            $ret['message'] = '';

            return $ret;
        } else {
            $ret['status'] = '0';
            $ret['data'] = '';
            $ret['message'] = '有未完成明细';

            return $ret;
        }
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

    function update_check($active, $field, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }

        $details = load_model('pur/OrderRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $sql = "select record_code,store_code,is_check,is_finish from pur_order_record where order_record_id = :id";
        $record = $this->db->get_row($sql, array(':id' => $id));
        if ($record['is_check'] == 1 && $active == 1 && $field == 'is_check') {
            return $this->format_ret(-1, array('data'=>$record['record_code']), ' 单据已确认');
        }
        if ($record['is_finish'] == 1 && $active == 1 && $field == 'is_finish') {
            return $this->format_ret(-1, array('data'=>$record['record_code']), ' 单据已完成');
        }
        $this->begin_trans();
        $ret = parent:: update(array($field => $active), array('order_record_id' => $id));
        if ($ret['status'] > 0 && $field == 'is_check') {
            if ($active == 1) {
                $ret = load_model('wms/WmsEntryModel')->add($record['record_code'], 'pur_notice', $record['store_code']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
            } else {
                $ret = load_model('wms/WmsEntryModel')->cancel($record['record_code'], 'pur_notice', $record['store_code']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
            }
        }
        $this->commit();
        $ret['status'] = $ret['status'] > 0 ? 1 : 0;
        if ($ret['status'] == 1) {
            $ret['message'] = '操作成功';
        }
        return $ret;
    }

    function imoprt_detail($id, $file) {
        $ret = $this->get_row(array('order_record_id' => $id));
        $store_code = $ret['data']['store_code'];
        $relation_code = $ret['data']['relation_code'];
        $sku_arr = $sku_num = $price_arr = array();
        $error_msg = '';

        $num = $this->read_csv_sku($file, $sku_arr, $sku_num, $price_arr);
        $sku_str = "'" . implode("','", $sku_arr) . "'";
        $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.price,g.purchase_price from goods_sku b
    			inner join  base_goods g ON g.goods_code = b.goods_code
    			where b.barcode in({$sku_str}) group by b.barcode ";

        $detail_data = $this->db->get_all($sql);
        $sucess_num = 0;
        foreach ($detail_data as $key => $val) {
            if (intval($sku_num[$val['barcode']]['num']) > 0) {

                $detail_data[$key]['num_flag'] = '1';

                $detail_data[$key]['num'] = $sku_num[$val['barcode']];
                $detail_data[$key]['price'] = $price_arr[$val['barcode']];
                //$detail_data[$key]['num'] = 0;
                //$val['num'] = $sku_num[$val['barcode']];
                unset($sku_num[$val['barcode']]);
                $sucess_num++;
            } else {
                unset($detail_data[$key]);
            }
        }
        //print_r($detail_data);
        //通知单明细添加
        $ret = load_model('pur/OrderRecordDetailModel')->add_detail_action($id, $detail_data);

        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未入库', 'action_name' => '增加明细', 'module' => "purchase", 'pid' => $id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        $ret['data'] = '';
        $message = '导入成功SKU数' . $sucess_num;
        if (!empty($sku_num)) {
            $message .=',' . '失败sku:' . count($sku_num);
            $file_name = $this->create_import_fail_files($sku_num, '找不到对应条码');
            //        load_model("sys/ExportModel")->downlaod_csv($request['file_key'],$request['export_name']);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        //$sucess_num
        //导入成功SKU数X，导入失败的SKU数Y，错误信息下载
        $ret['message'] = $message;

        return $ret;
    }

    function create_import_fail_files($fail_data, $msg) {
        $fail_top = array('商品条码', '错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($fail_data as $barcode => $val) {
            $val_data = array($barcode, $msg);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("order_record_fail" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        //var_dump($file_str);die;
        return $filename;
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
                    $sku_arr[] = $row[0];
                    $sku_num[$row[0]] = $row[1];
                    $price_arr[$row[0]] = $row[2];
                }
            }
            $i++;
        }
        fclose($file);
        //var_dump($sku_arr,$sku_num,$price_arr);
        return $i;
    }

    /*
     * 根据条件导出数据
     */

    function get_export_data($request) {
        $where = "WHERE 1=1";
        //单据编号
        if ($request['keyword_type'] == 'record_code' && $request['keyword'] != '') {
            $where .= " AND (r1.record_code LIKE '{$request['keyword']}%')";
        }
//		if (isset($request['record_code']) && $request['record_code'] != '') {
//			$where .= " AND (r1.record_code LIKE '{$request['record_code']}%')";
//		}
        //商品编码
        if ($request['keyword_type'] == 'goods_code' && $request['keyword'] != '') {
            $where .= " AND (r4.goods_code LIKE '{$request['keyword']}%')";
        }
        //商品条码
        if ($request['keyword_type'] == 'barcode' && $request['keyword'] != '') {
            $where .= " AND (r3.sku LIKE '{$request['keyword']}%')";
        }
        //计划编号
        if ($request['keyword_type'] == 'relation_code' && $request['keyword'] != '') {
            $where .= " AND (r1.relation_code LIKE '{$request['keyword']}%')";
        }
        //备注
        if (isset($request['remark']) && $request['remark'] != '') {
            $where .= " AND (r1.remark LIKE '{$request['remark']}%')";
        }
        //是否有差异订单
        if (isset($request['difference_models']) && $request['difference_models'] != '') {
            if ($request['difference_models'] == 1) {
                $where .= " AND (r1.num != r1.finish_num )";
            } else {
                $where .= " AND (r1.num = r1.finish_num )";
            }
        }
        // 调整仓库
        if (isset($request['store_code']) && $request['store_code'] != '') {
            $store_code_arr = explode(",", $request['store_code']);
            if (!empty($store_code_arr)) {
                $where .= " AND (";
                foreach ($store_code_arr as $key => $value) {
                    if ($key == 0) {
                        $where .= " r1.store_code = ('{$value}') ";
                    } else {
                        $where .= " OR r1.store_code = ('{$value}') ";
                    }
                }
                $where .= ")";
            }
        }
        // 供应商
        if (isset($request['supplier_code']) && $request['supplier_code'] != '') {
            $supplier_code_arr = explode(",", $request['supplier_code']);
            if (!empty($supplier_code_arr)) {
                $where .= " AND (";
                foreach ($supplier_code_arr as $key => $value) {
                    if ($key == 0) {
                        $where .= " r1.supplier_code = ('{$value}') ";
                    } else {
                        $where .= " OR r1.supplier_code = ('{$value}') ";
                    }
                }
                $where .= ")";
            }
        }
        //采购订单
        if (isset($request['relation_code']) && $request['relation_code'] != '') {
            $where .= " AND (r1.relation_code LIKE '{$request['relation_code']}')";
        }
        //下单时间
        if (isset($request['order_time_start']) && $request['order_time_start'] != '') {
            $where .= " AND (r1.order_time >= '{$request['order_time_start']} 00:00:00' )";
        }
        if (isset($request['order_time_end']) && $request['order_time_start'] != '') {
            $where .= " AND (r1.order_time <= '{$request['order_time_end']} 24:00:00' )";
        }
        //入库期限
        if (isset($request['in_time_start']) && $request['in_time_start'] != '') {
            $where .= " AND (r1.in_time >= '{$request['in_time_start']} 00:00:00' )";
        }
        if (isset($request['in_time_end']) && $request['in_time_end'] != '') {
            $where .= " AND (r1.in_time <= '{$request['in_time_end']} 00:00:00' )";
        }

        $sql = "SELECT r1.*,r2.goods_code,r2.spec1_code,r2.spec2_code,r2.sku,r2.money,r2.price,r2.num,r2.finish_num,r3.barcode,r4.goods_name FROM pur_order_record r1 LEFT JOIN pur_order_record_detail r2 ON r1.record_code = r2.record_code LEFT JOIN goods_sku r3 ON r2.sku = r3.sku LEFT JOIN base_goods r4 ON r2.goods_code = r4.goods_code " . $where . " ORDER BY order_time DESC, r1.record_code DESC";

        $detail_data = $this->db->get_all($sql);
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($detail_data as $key => $value) {
            $detail_data[$key]['money'] = round($value['money'], 2);
            $detail_data[$key]['different_num'] = $value['num'] - $value['finish_num'];
            if ($status['status'] != 1) {
                $detail_data[$key]['money'] = '****';
            }
        }

        filter_fk_name($detail_data, array('store_code|store', 'supplier_code|supplier'));
        //print_r($detail_data);exit;
        return $detail_data;
    }

//上传文件
    function import_upload($request, $upload_files) {
        $app['fmt'] = 'json';
        $files = array();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/";
        $fileInput = 'fileData';
        $dir = ROOT_PATH . 'webefast/uploads/';
        $type = $_POST['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        $is_max = 0;
        $is_file_type = 0;
        $file_type = array('csv', 'xlsx', 'xls');
        $upload_max_filesize = 5242880;
        foreach ($files_name_arr as $k => $v) {
            $pic = $upload_files[$v];
            if (!isset($pic['tmp_name']) || empty($pic['tmp_name'])) {
                $is_max = 1;
                continue;
            }
            $file_ext = get_file_extension($pic['name']);
            if (!in_array($file_ext, $file_type)) {
                $is_file_type = 1;
                continue;
            }
            $isExceedSize = $pic['size'] > $upload_max_filesize;
            if (!$isExceedSize) {
                if (file_exists($dir . $pic['name'])) {
                    @unlink($dir . $pic['name']);
                }
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
                $result = move_uploaded_file($pic['tmp_name'], $dir . $new_file_name);
                if (true == $result && ('xlsx' == $file_ext || 'xls' == $file_ext)) {
                    $result = $this->excel2csv($dir . $new_file_name, $file_ext);
                    $new_file_name = str_replace('.' . $file_ext, '.csv', $new_file_name);
                }
            }
        }
        if ($is_max) {
            return array(
                'status' => 0,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'msg' => str_replace('{0}', substr(ini_get('upload_max_filesize'), 0, -1) * 1024, lang('upload_msg_maxSize'))
            );
        } else if ($is_file_type) {
            return array(
                'status' => 0,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'msg' => str_replace('{0}', implode(',', $file_type), lang('upload_msg_ext'))
            );
        } else if (!$isExceedSize && $result) {
            return array(
                'status' => 1,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'url' => $dir . $new_file_name
            );
        } else if ($isExceedSize) {
            return array(
                'status' => 0,
                'type' => $type,
                'msg' => str_replace('{0}', $upload_max_filesize / 1024, lang('upload_msg_maxSize'))
            );
        } else {
            return array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！" . $result
            );
        }
    }

    /**
     *
     * 方法名       excel2csv
     *
     * 功能描述     excel转换csv文件
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-24
     * @param       string $file
     * @param       string $extends
     *
     * @return      string $data
     */
    function excel2csv($file, $extends) {
        require_lib('PHPExcel', true);
        try {
            $time3 = time();
            $PHPExcel = PHPExcel_IOFactory::load($file);
            $time4 = time();
            $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'CSV');
            $objWriter->setUseBOM(true);
            $objWriter->setPreCalculateFormulas(false);
            $objWriter->save(str_replace('.' . $extends, '.csv', $file));
            $time5 = time();
        } catch (Exception $e) {
            /* return array(
              'status' => -1,
              'data' => array($e->getMessage()),
              'msg' => lang('op_error')
              ); */
            return false;
        }
        /* return array(
          'status' => 1,
          'data' => array('load_excel' => $time4 - $time3, 'write_csv' => $time5 - $time4, 'excel_to_csv' => $time5 - $time3),
          'msg' => lang('op_success')
          ); */
        return true;
    }

    /**
     * @todo        采购通知单列表查询接口
     * @author      BaiSon PHP R&D
     * @date        2016-03-10
     * @param       array $param
     *               array(
     *                  可选: 'page', 'page_size', 'start_time', 'end_time','store_code','supplier_code','is_check'
     *               )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_order_notice_list_get($param) {
        //可选字段
        $key_option = array(
            's' => array(
                'page', 'page_size', 'start_time', 'end_time', 'store_code', 'supplier_code', 'is_check','is_finish'
            )
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        unset($param);
        //检查单页数据条数是否超限
        if (isset($arr_option['page_size']) && $arr_option['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_option['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }
        if (!isset($arr_option['is_check']) || $arr_option['is_check'] === '') {
            $arr_option['is_check'] = '1';
        }
        if (!isset($arr_option['is_finish']) || $arr_option['is_finish'] === '') {
            $arr_option['is_finish'] = '0';
        }elseif($arr_option['is_finish'] == 2){
            unset($arr_option['is_finish']);
        }
        //主单据开放字段
        $select = '
            `record_code`, `supplier_code`,`record_time`, `store_code`, `rebate`, `order_time`, `in_time`,`num`,`finish_num`,`remark`,`pur_type_code`
        ';
        $sql_main = " FROM {$this->table} ro WHERE 1=1";
        $sql_values = array();
        //生成sql条件语句
        $this->create_api_notice_sql_where($arr_option, $sql_main, $sql_values);
        //获取主单据信息
        $ret = $this->get_page_from_sql($arr_option, $sql_main, $sql_values, $select);
        if (count($ret['data']) <= 0) {
            return $this->format_ret(-10002, '', API_RETURN_MESSAGE_10002);
        }
        filter_fk_name($ret['data'], array('supplier_code|supplier', 'pur_type_code|record_type'));
        foreach($ret['data'] as &$row){
            $row['supplier_name'] = $row['supplier_code_name'];
            $row['record_type'] = $row['pur_type_code_name'];
            unset($row['supplier_code_code'],$row['supplier_code_name'],$row['pur_type_code_code'],$row['pur_type_code_name'],$row['pur_type_code']);
        }
        return $this->format_ret(1, $ret);
    }

    /**
     * @todo  API-创建主单据sql条件语句
     * @param array $arr_deal
     * @param string $sql_main
     * @param string $sql_values
     */
    private function create_api_notice_sql_where($arr_deal, &$sql_main, &$sql_values) {
        foreach ($arr_deal as $key => $val) {
            if ($key != 'page' && $key != 'page_size') {
                if ($key == 'start_time') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND ro.lastchanged>=:{$key}";
                } else if ($key == 'end_time') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND ro.lastchanged<=:{$key}";
                }else {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND ro.{$key}=:{$key}";
                }
            }
        }
        $start_time = date("Y-m-d H:i:s", strtotime("today"));
        $end_time = date("Y-m-d H:i:s", strtotime("today +1 days"));
        if (!isset($arr_deal['start_time'])) {
            $sql_main.=" AND ro.lastchanged >= :start_time";
            $sql_values[':start_time'] = $start_time;
        }
        if (!isset($arr_deal['end_time'])) {
            $sql_main.=" AND ro.lastchanged <= :end_time";
            $sql_values[':end_time'] = $end_time;
        }
    }

    /**
     * @tode  API-匹配开放字段信息
     * @param array $arr_detail 详情信息
     * @param array $open_key 开放字段
     * @return array 结果数组
     */
    private function match_detail_open_key($arr_detail, $open_key) {
        $notice_detail = array();
        foreach ($arr_detail as $k => $value) {
            foreach ($open_key as $v) {
                if (array_key_exists($v, $value)) {
                    $notice_detail[$k][$v] = $value[$v];
                }
            }
        }
        return $notice_detail;
    }

    /**
     * @todo        采购通知单明细查询接口
     * @author      BaiSon PHP R&D
     * @date        2016-03-10
     * @param       array $param
     *               array(
     *                  可选: 'page', 'page_size', 'record_code'
     *               )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_order_notice_detail_get($param) {
        if (!isset($param['record_code']) || empty($param['record_code'])) {
            return $this->format_ret('-10001', array('record_code'), '采购通知单号为必填项');
        }
        //检查单页数据条数是否超限
        if (isset($param['page_size']) && $param['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $param['page_size']), 'API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE');
        }
        //可选字段
        $key_option = array(
            's' => array(
                'page', 'page_size', 'record_code'
            )
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        unset($param);
        //详情开放字段
        $open_key = array(
            'goods_code', 'goods_name', 'spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode',
            'refer_price', 'price', 'money', 'num', 'finish_num', 'remark'
        );

        //提取通知单明细
        $arr_detail = load_model('pur/OrderRecordDetailModel')->get_by_page($arr_option);
        $data = $arr_detail['data']['data'];
        $filter = $arr_detail['data']['filter'];
        //检测是否为空
        if (empty($data)) {
            return $this->format_ret(-10002, '', API_RETURN_MESSAGE_10002);
        } else {
            $data = $this->match_detail_open_key($data, $open_key);
        }
        //返回数据给请求方
        return $this->format_ret(1, array('filter' => $filter, 'data' => $data));
    }

    /**
     * 检查采购通知单状态
     */
    function check_status($order_record) {
        $record_detail = load_model('pur/OrderRecordDetailModel')->is_exists_detail($order_record['record_code'], 'record_code');
        if (empty($order_record) || empty($record_detail['data'])) {
            return $this->format_ret(-1, '', '采购通知单明细信息不存在！');
        }
        if ($order_record['is_check'] == 0) {
            return $this->format_ret(-1, '', '未确认采购通知单不能生成采购入库单！');
        }
        if ($order_record['is_finish'] == 1) {
            return $this->format_ret(-1, '', '已完成采购通知单不能生成采购入库单！');
        }
        return $this->format_ret(1);
    }

    /**
     * 采购订单生成采购通知单
     */
    public function create_order_record($planned_record, $type = "create_return_unfinish") {
        $record_code = $this->create_fast_bill_sn();
        $order_record = array();

        $order_record['record_code'] = $record_code;
        $order_record['relation_code'] = $planned_record['record_code'];
        $order_record['supplier_code'] = $planned_record['supplier_code'];
        $order_record['store_code'] = $planned_record['store_code'];
        $order_record['rebate'] = $planned_record['rebate'];
        $order_record['pur_type_code'] = $planned_record['pur_type_code'];
        $order_record['order_time'] = date('Y-m-d H:i:s');
        $order_record['is_add_time'] = $planned_record['is_add_time'];
        $order_record['in_time'] = $planned_record['in_time'];
        $order_record['is_notify_payment'] = $planned_record['is_notify_payment'];
        $order_record['remark'] = $planned_record['remark'];
        $this->begin_trans();
        try {
            $ret = $this->insert($order_record);
            $pid = $ret['data'];
            //按未完成数生成
            if ($type == "create_return_unfinish") {
                $sql = "select goods_code,spec1_code,spec2_code,sku,refer_price,price,rebate,rebate,num,finish_num from pur_planned_record_detail where record_code = '{$planned_record['record_code']}'";
                $data = $this->db->get_all($sql);

                //通知单的数量生成退单时 为通知数
                foreach ($data as $key => $return_info) {
                    if ($return_info['num'] <= $return_info['finish_num']) {
                        unset($data[$key]);
                        continue;
                    }
                    $data[$key]['num'] = $return_info['num'] - $return_info['finish_num'];
                    unset($data[$key]['finsih_num']);
                }
                if (empty($data)) {
                    throw new Exception('采购订单已经全部生成 ，不可再采购通知单');
                }
                $ret = load_model('pur/OrderRecordDetailModel')->add_detail_action($pid, $data);
                if ($ret['status'] != 1) {
                    throw new Exception('退单明细保存失败');
                }
            }

            $this->commit();
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '创建', 'module' => "order_record", 'pid' => $pid, 'action_note' => "由采购订单{$planned_record['record_code']}生成");
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            return array('status' => 1, 'message' => '更新成功', 'data' => $pid);
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => $e->getMessage());
        }
    }

    /**
     * 采购订单生成采购通知单
     */
    public function create_purchaser_record($order) {
        $order_record = $this->get_by_id($order['order_record_id']);
        $ret = $this->check_status($order_record['data']);
        if ($ret['status'] == 1) {
            $ret = load_model('pur/PurchaseRecordModel')->create_purchase_record($order_record['data'], $order['create_type']);
            if ($ret['status'] == 1) {
                $ret1 = $this->update_check('1', 'is_execute', $order['order_record_id']);
            }
        }
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未完成', 'action_name' => '生成采购入库单', 'module' => "order_record", 'pid' => $order['order_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
    }

    //判断是否有未入库的采购入库单
    public function out_relation($id) {
        $record = $this->get_row(array('order_record_id' => $id));
        $record_code = $record['data']['record_code'];
        $sql = " select count(*) as cnt  from pur_purchaser_record where  relation_code = :record_code AND   is_check_and_accept = '0'  ";
        $arr = array(':record_code' => $record_code);
        $data = $this->db->get_row($sql, $arr);
        if (isset($data['cnt']) && $data['cnt'] > 0) {
            return $this->format_ret('-1', '', '存在未入库的入库单，是否继续');
        }
        return $this->format_ret('1');
    }

    /**
     * API-创建采购通知单
     * @author wmh
     * @date 2016-11-29
     * @param array $param
     * <pre> 必选: 'store_code','supplier_code','pur_type_code','in_time','rebate'
     * <pre> 可选: 'remark'
     * @return array 操作结果
     */
    public function api_pur_notice_create($param) {
        $error_data = array();
        try {
            $key_required = array(
                's' => array('pur_type_code', 'in_time', 'store_code', 'supplier_code', 'rebate'),
            );
            $data = array();
            $ret_require = valid_assign_array($param, $key_required, $data, TRUE);
            if ($ret_require['status'] === FALSE) {
                $error_data = $ret_require['req_empty'];
                throw new Exception('缺少必填参数或必填参数为空', '-10001');
            }
            $data['remark'] = isset($param['remark']) ? str_replace(array("\r\n", "\r", "\n"), '', trim($param['remark'])) : '';
            unset($param);

            if (strtotime($data['in_time']) === FALSE) {
                $error_data = array('in_time' => $data['in_time']);
                throw new Exception('日期格式不正确', '-10005');
            }
            $data['in_time'] = format_day_time($data['in_time']);
            if (!is_numeric($data['rebate']) || $data['rebate'] < 0 || $data['rebate'] > 1) {
                $error_data = array('rebate' => $data['rebate']);
                throw new Exception('折扣必须为0-1的数字(例0.5)', '-10005');
            }
            $fld = array('pur_type_code' => '采购类型', 'supplier_code' => '供应商', 'store_code' => '仓库');
            $check_arr = array(
                array('base_record_type', 'record_type_code', $data['pur_type_code'], 'pur_type_code'),
                array('base_supplier', 'supplier_code', $data['supplier_code'], 'supplier_code'),
                array('base_store', 'store_code', $data['store_code'], 'store_code')
            );
            $ret = $this->is_exists_data($check_arr);
            if ($ret !== TRUE) {
                $error_data = array($ret => $data[$ret]);
                throw new Exception($fld[$ret] . '不存在', '-10002');
            }

            $data['record_code'] = $this->create_fast_bill_sn();
            $data['is_add_time'] = date('Y-m-d H:i:s');
            $data['order_time'] = date('Y-m-d H:i:s');

            $ret = parent::insert($data);
            $affect_row = $this->affected_rows();
            if ($ret['status'] != 1 || $affect_row != 1) {
                throw new Exception('创建失败', '-1');
            }
            //日志
            $log = array('user_id' => 1, 'user_code' => 'admin', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "创建", 'module' => "order_record", 'pid' => $ret['data'], 'action_note' => 'API-创建单据');
            load_model('pur/PurStmLogModel')->insert($log);

            return $this->format_ret(1, $data['record_code'], '创建成功');
        } catch (Exception $e) {
            $msg = $e->getCode() == 0 ? '创建失败' : $e->getMessage();
            return $this->format_ret($e->getCode(), $error_data, $msg);
        }
    }

    /**
     * API-更新采购通知单明细
     * @author wmh
     * @date 2016-11-29
     * @param array $param
     * <pre> 必选: 'record_code','detail'
     * @return array 操作结果
     */
    public function api_pur_notice_update($param) {
        $error_data = array();
        try {
            if (!isset($param['record_code']) || empty($param['record_code'])) {
                $error_data = array('record_code');
                throw new Exception('缺少必填参数或必填参数为空', '-10001');
            }
            if (!isset($param['detail']) || empty($param['detail'])) {
                $error_data = array('detail');
                throw new Exception('缺少必填参数或必填参数为空', '-10001');
            }
            $record_code = $param['record_code'];
            $detail = json_decode($param['detail'], true);
            if (empty($detail)) {
                throw new Exception('明细数据异常', '-10001');
            }
            unset($param);
            //检查明细是否为空
            $error_data = $this->api_check_detail($detail, array('barcode', 'num'));
            if (!empty($error_data)) {
                throw new Exception('明细数据不能为空', '-10001');
            }

            $record = $this->is_exists($record_code);
            if ($record['status'] != 1 || empty($record['data'])) {
                $error_data = array('record_code' => $record_code);
                throw new Exception('采购通知单不存在', '-10002');
            }
            $record = $record['data'];

            $barcode_num = array_column($detail, 'num', 'barcode');
            $barcode_arr = array_column($detail, 'barcode');
            $barcode_str = "'" . implode("','", $barcode_arr) . "'";
            $sql = "SELECT bg.goods_code,bg.purchase_price,gs.sku,gs.barcode,gs.spec1_code,gs.spec2_code,gs.cost_price FROM base_goods AS bg INNER JOIN goods_sku AS gs ON bg.goods_code=gs.goods_code WHERE gs.barcode IN({$barcode_str})";
            $detail = $this->db->get_all($sql);

            $barcode_exists = array_column($detail, 'barcode');
            $error_data = array_diff($barcode_arr, $barcode_exists);
            if (!empty($error_data)) {
                throw new Exception('商品条形码不存在', '-10002');
            }
            foreach ($detail as &$val) {
                $num = $barcode_num[$val['barcode']];
                if (!is_int((int) $num) || $num < 1) {
                    $error_data = array($val['barcode'] => $num);
                    throw new Exception('数量必须为正整数', '-10005');
                }
                $val['rebate'] = $record['rebate'];
                $val['num'] = $num;
                unset($val['barcode']);
            }
            $ret = load_model('pur/OrderRecordDetailModel')->add_detail_action($record['order_record_id'], $detail);
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }

            //日志
            $log = array('user_id' => 1, 'user_code' => 'admin', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "更新明细", 'module' => "order_record", 'pid' => $record['order_record_id'], 'action_note' => 'API-更新明细');
            load_model('pur/PurStmLogModel')->insert($log);
            return $this->format_ret(1, '', '更新成功');
        } catch (Exception $e) {
            $msg = $e->getCode() == 0 ? '更新失败' : $e->getMessage();
            return $this->format_ret($e->getCode(), $error_data, $msg);
        }
    }

    /**
     * 确认采购通知单
     * @author wmh
     * @date 2016-11-29
     * @param array $param
     * <pre> 必选: 'record_code'
     * @return array 操作结果
     */
    public function api_pur_notice_confirm($param) {
        $error_data = array();
        try {
            if (!isset($param['record_code']) || empty($param['record_code'])) {
                $error_data = array('record_code');
                throw new Exception('缺少必填参数或必填参数为空', '-10001');
            }
            $code_arr = json_decode($param['record_code'], true);
            if (!is_array($code_arr)) {
                $code_arr = array($param['record_code']);
            }
            if (empty($code_arr)) {
                throw new Exception('单据编号格式有误', '-10005');
            }
            $code_str = "'" . implode("','", $code_arr) . "'";
            $sql = "SELECT order_record_id AS id,record_code FROM pur_order_record WHERE record_code IN($code_str)";
            $record = $this->db->get_all($sql);
            $code_exists = array_column($record, 'record_code');
            $code_diff = array_diff($code_arr, $code_exists);
            foreach ($code_diff as $val) {
                $error = array();
                $error['status'] = '-10002';
                $error['data'] = $val;
                $error['message'] = '采购通知单不存在';
                $error_data[] = $error;
            }
            array_walk($record, function($val) use(&$error_data) {
                $ret = $this->update_check(1, 'is_check', $val['id']);
                if ($ret['status'] == 1) {
                    //日志
                    $log = array('user_id' => '1', 'user_code' => 'admin', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未完成', 'action_name' => '确认', 'module' => "order_record", 'pid' => $val['id'], 'action_note' => 'API-确认单据');
                    load_model('pur/PurStmLogModel')->insert($log);
                    $ret['message'] = '确认成功';
                }
                $ret['data'] = $val['record_code'];
                $error_data[] = $ret;
            });

            return $this->format_ret(1, $error_data);
        } catch (Exception $e) {
            $msg = $e->getCode() == 0 ? '确认失败' : $e->getMessage();
            return $this->format_ret($e->getCode(), $error_data, $msg);
        }
    }

    /**
     * 完成采购通知单
     * @author wmh
     * @date 2016-12-14
     * @param array $param
     * <pre> 必选: 'record_code'
     * @return array 操作结果
     */
    public function api_pur_notice_finish($param) {
        $error_data = array();
        try {
            if (!isset($param['record_code']) || empty($param['record_code'])) {
                $error_data = array('record_code');
                throw new Exception('缺少必填参数或必填参数为空', '-10001');
            }
            $code_arr = json_decode($param['record_code'], true);
            if (!is_array($code_arr)) {
                $code_arr = array($param['record_code']);
            }
            if (empty($code_arr)) {
                throw new Exception('单据编号格式有误', '-10005');
            }
            $code_str = "'" . implode("','", $code_arr) . "'";
            $sql = "SELECT order_record_id AS id,record_code FROM pur_order_record WHERE record_code IN($code_str)";
            $record = $this->db->get_all($sql);
            $code_exists = array_column($record, 'record_code');
            $code_diff = array_diff($code_arr, $code_exists);
            foreach ($code_diff as $val) {
                $error = array();
                $error['status'] = '-10002';
                $error['data'] = $val;
                $error['message'] = '采购通知单不存在';
                $error_data[] = $error;
            }
            array_walk($record, function($val) use(&$error_data) {
                $ret = $this->check_exists_no_accept_pur($val['record_code']);
                if ($ret['status'] == 1) {
                    $ret = $this->update_check(1, 'is_finish', $val['id']);
                    if ($ret['status'] == 1) {
                        $log = array('user_id' => '1', 'user_code' => 'admin', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '完成', 'action_name' => '确认', 'module' => "order_record", 'pid' => $val['id'], 'action_note' => 'API-完成单据');
                        load_model('pur/PurStmLogModel')->insert($log);
                    }
                }

                $ret['data'] = $val['record_code'];
                $error_data[] = $ret;
            });

            return $this->format_ret(1, $error_data);
        } catch (Exception $e) {
            $msg = $e->getCode() == 0 ? '完成失败' : $e->getMessage();
            return $this->format_ret($e->getCode(), $error_data, $msg);
        }
    }

    /**
     * 检查数据是否存在
     * @param array $arr 数据：array(array('表名','字段名','字段值','映射字段名'))
     * @return boolean 存在返回true，不存在返回映射字段名
     */
    private function is_exists_data($arr) {
        foreach ($arr as $val) {
            $sql = "SELECT count(1) FROM {$val[0]} WHERE {$val[1]}=:{$val[1]}";
            $ret = $this->db->get_value($sql, array(":{$val[1]}" => $val[2]));
            if ($ret < 1) {
                return $val[3];
            }
        }
        return TRUE;
    }

    /**
     * 检查接口传入明细信息是否为空
     */
    private function api_check_detail($detail, $check_key) {
        $err_data = array();
        foreach ($detail as $key => $val) {
            foreach ($check_key as $v) {
                if (empty($val[$v])) {
                    $err_data[$key][] = $v;
                }
            }
        }
        return $err_data;
    }

    /**
     * 扫描确认
     * @param $request
     */
    function scan_do_check($request) {
        $record = load_model('pur/OrderRecordModel')->get_row(array('record_code' => $request['record_code']));
        $record_data = $record['data'];
        if ($record_data['is_check'] != 0) {
            return $this->format_ret('-1', '', '单据状态异常！');
        }
        $ret = $this->update_check(1, 'is_check', $record_data['order_record_id']);
        //日志
        if ($ret['status'] == '1') {
            $action_name = '扫描确认';
            $sure_status = '扫描确认';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未完成', 'action_name' => $action_name, 'module' => "order_record", 'pid' => $record_data['order_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
    }

    public function add_detail($param) {
        $ret = load_model('pur/OrderRecordDetailModel')->add_detail_action($param['record_id'], $param['detail']);
        return $ret;
    }
}
