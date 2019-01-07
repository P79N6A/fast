<?php

require_model('api/JxcOptApiModel');

/**
 * 采购入库单接口
 * @author WMH
 */
class PurchaseRecordApiModel extends JxcOptApiModel {

    function __construct() {
        parent::__construct('pur_purchaser_record');
        $this->record_type = 'purchase';
    }

    public function api_record_create($param) {
        $arr_required = array();
        $arr_option = array();
        if (!empty($param['relation_code'])) {
            $key_option = array(
                's' => array('init_code', 'relation_code', 'remark', 'produce_mode')
            );
        } else {
            $k_required = array(
                's' => array('store_code', 'supplier_code'),
            );
            $key_option = array(
                's' => array('record_type', 'record_time', 'rebate', 'remark')
            );
            $ret_required = valid_assign_array($param, $k_required, $arr_required, TRUE);
            if ($ret_required['status'] !== TRUE) {
                return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
            }
        }
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        $arr_deal = array_merge($arr_required, $arr_option);
        unset($param, $arr_required, $arr_option);
        if (!empty($arr_deal['record_time']) && !strtotime($arr_deal['record_time'])) {
            return $this->format_ret(-10005, array('record_time' => $arr_deal['record_time']), '日期格式错误');
        }

        $this->begin_trans();
        $arr_deal['remark'] = empty($arr_deal['remark']) ? '' : $arr_deal['remark'];
        if (!empty($arr_deal['relation_code'])) {
            //根据通知单创建入库单
            if (!empty($arr_deal['init_code'])) {
                //如果存在原单号，则校验原单号是否唯一（一般用于wms入库操作，判断重复操作）
                $sql = "SELECT init_code,relation_code,record_code FROM pur_purchaser_record WHERE relation_code=:relation_code AND init_code=:init_code";
                $sql_values = array(':relation_code' => $arr_deal['relation_code'], 'init_code' => $arr_deal['init_code']);
                $ret = $this->db->get_row($sql, $sql_values);
                if (!empty($ret)) {
                    return $this->format_ret(-20001, $ret, "原单号{$arr_deal['init_code']},已创建入库单");
                }
            }
            $ret = $this->create_record_by_notice($arr_deal);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
            $this->commit();
            return $ret;
        }

        //校验档案类数据
        $check_exists = array(
            array('base_store', 'store_code', $arr_deal['store_code']),
            array('base_supplier', 'supplier_code', $arr_deal['supplier_code']),
            array('base_record_type', 'record_type_code', $arr_deal['record_type'])
        );
        foreach ($check_exists as $option) {
            if (empty($option[2])) {
                continue;
            }
            $ret_exists = $this->check_data_exists($option[0], $option[1], $option[2]);
            if ($ret_exists['status'] < 1) {
                return $ret_exists;
            }
        }
        //直接创建入库单
        $record = array();
        $this->record_code = $this->produce_record_code();
        $record['record_code'] = $this->record_code;
        $record['record_time'] = empty($arr_deal['record_time']) ? date('Y-m-d') : $arr_deal['record_time'];
        $record['order_time'] = date('Y-m-d H:i:s');
        $record['store_code'] = $arr_deal['store_code'];
        $record['supplier_code'] = $arr_deal['supplier_code'];
        $record['record_type_code'] = empty($arr_deal['record_type']) ? '000' : $arr_deal['record_type'];
        $record['rebate'] = empty($arr_deal['rebate']) ? 1 : $arr_deal['rebate'];
        $record['remark'] = $arr_deal['remark'];
        $ret = $this->insert($record);
        if ($ret['status'] < 1 || $this->affected_rows() != 1) {
            $this->rollback();
            return $this->format_ret(-1, (object) array(), '创建采购入库单失败');
        }
        $this->get_record_by_code();
        $this->set_opt_log('新增', 'API调用生成');

        $this->commit();

        return $this->format_ret(1, array('record_code' => $this->record_code), '创建成功');
    }

    /**
     * API-查询采购入库单列表
     * @author wmh
     * @date 2017-06-14
     * @param array $param
     * @return array 操作结果
     */
    public function api_record_get($param) {
        //可选字段
        $key_option = array(
            's' => array('start_time', 'end_time', 'store_code', 'supplier_code', 'relation_code'),
            'i' => array('page', 'page_size', 'is_check')
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        unset($param);
        //检查单页数据条数是否超限
        if (isset($arr_option['page_size']) && $arr_option['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_option['page_size']), 'API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE');
        }
        if (!isset($arr_option['is_check']) || !in_array($arr_option['is_check'], array(0, 1))) {
            $arr_option['is_check'] = '';
        }

        $select = ' `record_code`, `relation_code`, `order_time`, `store_code`, `supplier_code`, `record_type_code`, `is_check`,`num`,`finish_num`';
        $sql_main = " FROM {$this->table} pr WHERE pr.is_check_and_accept=0";
        $sql_values = array();
        //生成sql条件语句
        $this->get_record_sql_where($arr_option, $sql_main, $sql_values, 'pr.');
        $sql_main .= ' ORDER BY pr.order_time DESC';
        //获取主单据信息
        $ret = $this->get_page_from_sql($arr_option, $sql_main, $sql_values, $select);

        $data = $ret['data'];
        if (empty($data)) {
            return $this->format_ret(-10002, (object) array(), 'API_RETURN_MESSAGE_10002');
        }
        filter_fk_name($data, array('store_code|store', 'supplier_code|supplier', 'record_type_code|record_type'));

        $new_data = array();
        foreach ($data as &$row) {
            $new_data[] = array(
                'record_code' => $row['record_code'],
                'relation_code' => $row['relation_code'],
                'record_time' => $row['order_time'],
                'is_check' => $row['is_check'],
                'store_code' => $row['store_code'],
                'store_name' => $row['store_code_name'],
                'supplier_code' => $row['supplier_code'],
                'supplier_name' => $row['supplier_code_name'],
                'record_type' => $row['record_type_code_name'],
                'notice_num' => $row['num'],
                'finish_num' => $row['finish_num'],
            );
        }

        $filter = get_array_vars($ret['filter'], array('page', 'page_size', 'page_count', 'record_count'));

        $revert_data = array(
            'filter' => $filter,
            'data' => $new_data,
        );
        return $this->format_ret(1, $revert_data);
    }

    /**
     * API-查询采购入库单明细
     * @author wmh
     * @date 2017-07-03
     * @param array $param
     * @return array 操作结果
     */
    public function api_detail_get($param) {
        $k_required = array('s' => array('record_code'),);
        $key_option = array('i' => array('page', 'page_size'));
        $arr_required = array();
        $arr_option = array();

        //提取可选字段中已赋值数据
        $ret_required = valid_assign_array($param, $k_required, $arr_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
        $record_code = $arr_required['record_code'];

        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        $filter = $arr_option;
        unset($param, $arr_required, $arr_option);

        //检查单页数据条数是否超限
        if (isset($filter['page_size']) && $filter['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $filter['page_size']), 'API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE');
        }

        $select = 'rd.`goods_code`, bg.`goods_name`, gs.`spec1_code`, gs.`spec1_name`, gs.`spec2_code`, gs.`spec2_name`, gs.`barcode`,
        rd.`refer_price`, rd.`price`, rd.`rebate`, rd.`money`, rd.`notice_num`, rd.`num` AS finish_num';
        $sql_join = 'INNER JOIN goods_sku AS gs ON rd.sku = gs.sku INNER JOIN base_goods AS bg ON rd.goods_code = bg.goods_code';
        $sql_main = " FROM {$this->detail_table_map[$this->record_type]} AS rd {$sql_join} WHERE rd.record_code=:record_code";
        $sql_values = array(':record_code' => $record_code);
        $ret = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $data = $ret['data'];
        if (empty($data)) {
            return $this->format_ret(-10002, (object) array(), 'API_RETURN_MESSAGE_10002');
        }
        foreach ($data as &$row) {
            $row['price'] = round($row['price'] * $row['rebate'], 3);
            unset($row['rebate']);
        }

        $filter = get_array_vars($ret['filter'], array('page', 'page_size', 'page_count', 'record_count'));

        $revert_data = array(
            'filter' => $filter,
            'data' => $data,
        );
        return $this->format_ret(1, $revert_data);
    }

    /**
     * API-更新采购入库单明细
     * @author wmh
     * @date 2017-06-14
     * @param array $param 接口参数
     * @return array 操作结果
     */
    public function api_detail_update($param) {
        $k_required = array(
            's' => array('record_code', 'detail'),
            'i' => array('update_mode')
        );
        $k_d_required = array(
            's' => array('barcode', 'num'),
        );
        $r_required = array();
        //提取可选字段中已赋值数据
        $ret_required = valid_assign_array($param, $k_required, $r_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }

        $record_code = $r_required['record_code'];
        $update_mode = $r_required['update_mode'];
        if (!in_array($update_mode, array(0, 1))) {
            return $this->format_ret(-10005, array('update_mode' => $update_mode), '参数错误');
        }
        $detail = json_decode($r_required['detail'], TRUE);
        if (empty($detail) || !is_array($detail)) {
            return $this->format_ret(-10005, (object) array(), '明细数据解析失败');
        }

        unset($param, $r_required, $ret_required);

        $this->record_code = $record_code;
        $record = $this->check_record();
        if ($record['status'] < 1) {
            return $record;
        }

        $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        if ($lof_status['lof_status'] == 1) {
            $k_d_required['s'] = array_merge($k_d_required['s'], array('lof_no', 'production_date'));
        }
        $barcode_num = array_column($detail, 'num', 'barcode');

        $old_detail = [];
        if (!empty($this->record['relation_code'])) {
            //获取入库单明细，用来判断扫描数是否超过通知数
            $old_detail = $this->get_detail_by_code('sku,num,notice_num');
            $old_detail = load_model('util/ViewUtilModel')->get_map_arr($old_detail, 'sku');
        }

        $barcode_map = array();
        $obj_sku = load_model('prm/SkuModel');
        foreach ($detail as &$val) {
            $d_required = array();
            $ret_required = valid_assign_array($val, $k_d_required, $d_required, TRUE);
            if ($ret_required['status'] !== TRUE) {
                return $this->format_ret(-10001, (object) array(), '添加明细存在空数据');
            }

            $barcode = $val['barcode'];
            $b_data = $obj_sku->convert_scan_barcode($barcode);
            if (empty($b_data)) {
                return $this->format_ret(-10002, array('barcode' => $barcode), '条码不存在');
            }
            $num = $barcode_num[$barcode];
            if (!is_int((int) $num) || $num < 1) {
                return $this->format_ret(-10005, $val, '数量值无效');
            }
            if (isset($old_detail[$b_data['sku']])) {
                //数量追加模式要加上原来的数量比较
                $new_num = $update_mode == 1 ? $old_detail[$b_data['sku']]['num'] + $num : $num;
                if ($new_num > $old_detail[$b_data['sku']]['notice_num']) {
                    return $this->format_ret(-1, (object) array(), '添加的商品数量不能超过入库单通知数');
                }
            }

            $barcode_map[$b_data['barcode']] = $barcode;
            $val = array_merge($val, $b_data);
        }

        $ret_detail = $this->deal_detail($detail, $update_mode, $lof_status['lof_status']);
        if ($ret_detail['status'] < 1) {
            return $ret_detail;
        }

        $ret = load_model('pur/PurchaseRecordModel')->add_detail_goods($this->record['purchaser_record_id'], $ret_detail['data'], $this->record['store_code'], 0);
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, (object) array(), $ret['message']);
        }

        $revert_data = $this->get_record_comb_data();
        foreach ($revert_data['detail'] as &$row) {
            $barcode = $row['barcode'];
            if (isset($barcode_map[$barcode])) {
                $row['scan_barcode'] = $barcode_map[$barcode];
            }
        }

        $log_arr = array();
        foreach ($ret_detail['data'] as $r) {
            $log_arr[] = "{$r['barcode']}({$r['num']})";
        }
        $log_str = implode('；', $log_arr);

        $action_note = $update_mode == 1 ? '数量累加。' : '数量覆盖。';
        $this->set_opt_log('更新明细', 'API-更新明细，' . $action_note . $log_str);

        return $this->format_ret(1, $revert_data, '更新成功');
    }

    public function api_record_accept($param) {
        
    }

    protected function set_opt_log($action_name, $action_note) {
        $log_data = array(
            'pid' => $this->record['purchaser_record_id'],
            'action_name' => $action_name,
            'action_note' => $action_note,
            'sure_status' => $this->record['is_check'] == 1 ? '已确认' : '未确认',
            'finish_status' => $this->record['is_check_and_accept'] == 1 ? '已验收' : '未验收',
        );
        $this->set_log($log_data);
    }

    /**
     * 获取商品明细信息
     */
    private function deal_detail($detail, $update_mode, $lof_status) {
        $obj_util = load_model('util/ViewUtilModel');
        //获取商品信息
        $sql_values = array();
        $g_code_arr = array_unique(array_column($detail, 'goods_code'));
        $g_code_str = $this->arr_to_in_sql_value($g_code_arr, 'goods_code', $sql_values);
        $sql = "SELECT goods_code,purchase_price,sell_price FROM base_goods WHERE goods_code IN({$g_code_str})";
        $goods_data = $this->db->get_all($sql, $sql_values);
        if (empty($goods_data)) {
            return $this->format_ret(-1, (object) array(), '明细商品不存在');
        }
        $goods_data = $obj_util->get_map_arr($goods_data, 'goods_code');

        $notice_detail = array();
        $sql_values = array(':record_code' => $this->record['relation_code']);
        $sku_arr = array_column($detail, 'sku');
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        if (!empty($this->record['relation_code'])) {
            //获取通知单商品信息
            $sql = "SELECT sku,price,refer_price FROM pur_order_record_detail WHERE record_code=:record_code AND sku IN({$sku_str})";
            $notice_detail = $this->db->get_all($sql, $sql_values);
            $notice_detail = $obj_util->get_map_arr($notice_detail, 'sku');
        }

        //数量为累加模式时，获取单据已存在的商品信息
        $pre_detail = array();
        if ($update_mode == 1) {
            $sql_values[':record_code'] = $this->record['record_code'];
            $select = 'sku,num';
            $select .= $lof_status == 1 ? ',lof_no' : '';
            $sql = "SELECT {$select} FROM b2b_lof_datail WHERE order_type='purchase' AND order_code=:record_code AND sku IN({$sku_str})";
            $pre_detail = $this->db->get_all($sql, $sql_values);
            if (!empty($pre_detail)) {
                $key_fld = $lof_status == 1 ? 'sku,lof_no' : 'sku';
                $pre_detail = $obj_util->get_map_arr($pre_detail, $key_fld);
            }
        }
        $data = array();
        foreach ($detail as $val) {
            $k = $lof_status == 1 ? $val['sku'] . ',' . $val['lof_no'] : $val['sku'];
            if (isset($data[$k])) {
                $data[$k]['num'] += $val['num'];
            }
            $d = array_merge($val, $goods_data[$val['goods_code']]);
            if (isset($notice_detail[$val['sku']])) {
                $d = array_merge($d, $notice_detail[$val['sku']]);
            }
            if (isset($pre_detail[$k])) {
                $pre_d_temp = $pre_detail[$k];
                $d['num'] += $pre_d_temp['num'];
            }
            $data[$k] = $d;
        }
        if (empty($data)) {
            return $this->format_ret(-1, (object) array(), '明细处理结果为空');
        }

        return $this->format_ret(1, $data);
    }

    private function get_record_comb_data() {
        $record = $this->get_record_by_code();
        $revert_data = array(
            'record_code' => $record['record_code'],
            'total_num' => $record['finish_num'],
        );
        $sql = "SELECT rd.goods_code,bg.goods_name,CONCAT_WS('；',spec1_name,spec2_name) AS goods_spec,gs.barcode,rd.num
                FROM pur_purchaser_record_detail AS rd 
                INNER JOIN goods_sku AS gs ON rd.sku=gs.sku 
                INNER JOIN base_goods AS bg ON rd.goods_code=gs.goods_code
                WHERE rd.record_code=:record_code GROUP BY rd.sku";
        $revert_data['detail'] = $this->db->get_all($sql, array(':record_code' => $this->record_code));
        $total_num = array_column($revert_data['detail'], 'num');
        $revert_data['total_num'] = array_sum($total_num);

        return $revert_data;
    }

    /**
     * 生成单据查询sql条件语句
     * @param array $filter 参数条件
     * @param string $sql_main sql主体
     * @param string $sql_values sql映射值
     * @param string $ab 表别名
     */
    private function get_record_sql_where($filter, &$sql_main, &$sql_values, $ab) {
        foreach ($filter as $key => $val) {
            if (in_array($key, array('page', 'page_size')) || $val === '') {
                continue;
            }
            if ($key == 'start_time') {
                $sql_main .= " AND {$ab}lastchanged>=:{$key}";
            } else if ($key == 'end_time') {
                $sql_main .= " AND {$ab}lastchanged<=:{$key}";
            } else {
                $sql_main .= " AND {$ab}{$key}=:{$key}";
            }
            $sql_values[":{$key}"] = $val;
        }

        if (!isset($filter['start_time'])) {
            $start_time = date("Y-m-d H:i:s", strtotime("today"));
            $sql_main .= " AND {$ab}lastchanged >= :start_time";
            $sql_values[':start_time'] = $start_time;
        }
        if (!isset($filter['end_time'])) {
            $end_time = date("Y-m-d H:i:s", strtotime("today +1 days"));
            $sql_main .= " AND {$ab}lastchanged <= :end_time";
            $sql_values[':end_time'] = $end_time;
        }
    }

    /**
     * 根据通知单创建入库单
     * @param array $param 参数
     * @return array
     */
    private function create_record_by_notice($param) {
        $record = load_model('pur/OrderRecordModel')->get_by_field('record_code', $param['relation_code'], 'order_record_id,pur_type_code AS record_type_code,record_time,remark,store_code,supplier_code,rebate,is_check,is_finish');
        if ($record['status'] < 1) {
            return $this->format_ret(-10002, array('relation_code' => $param['relation_code']), '采购通知单不存在');
        }
        $record = $record['data'];
        if ($record['is_finish'] == 1) {
            return $this->format_ret(-1, array('relation_code' => $param['relation_code']), '采购通知单已完成');
        }
        if ($record['is_check'] != 1) {
            return $this->format_ret(-1, array('relation_code' => $param['relation_code']), '采购通知单未确认');
        }

        $this->record_code = $this->produce_record_code();
        //添加采购入库单主单据
        $record['relation_code'] = $param['relation_code'];
        $record['record_code'] = $this->record_code;
        $record['init_code'] = empty($param['init_code']) ? '' : $param['init_code'];
        $record['order_time'] = date('Y-m-d H:i:s');
        $record['record_time'] = empty($param['record_time']) ? $record['record_time'] : $param['record_time'];
        $record['remark'] = empty($param['remark']) ? $record['remark'] : trim($param['remark']);
        unset($record['is_check'], $record['is_finish']);
        $ret = $this->insert($record);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $record['pid'] = $ret['data'];

        //生成明细
        $msg = '';
        if (!isset($param['produce_mode']) || $param['produce_mode'] == 1) {
            $ret = $this->add_detail_by_notice($record);
            if ($ret['status'] < 1) {
                return $ret;
            }
            $msg = '按未完成数';
        }

        //回写执行状态
        $ret = load_model('pur/OrderRecordModel')->update_check('1', 'is_execute', $record['order_record_id']);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $this->get_record_by_code();
        $this->set_opt_log('新增', "API调用生成");

        return $this->format_ret(1, array('record_code' => $this->record_code), '创建采购入库单成功');
    }

    /**
     * 根据通知单剩余未完成数添加入库单明细
     * @param array $record
     * @return array
     */
    private function add_detail_by_notice($record) {
        $sql = "SELECT goods_code,spec1_code,spec2_code,sku,refer_price,price,rebate,rebate,num,finish_num FROM pur_order_record_detail WHERE record_code = :record_code";
        $data = $this->db->get_all($sql, [':record_code' => $record['relation_code']]);

        foreach ($data as $key => &$row) {
            if ($row['num'] <= $row['finish_num']) {
                unset($data[$key]);
                continue;
            }
            $row['notice_num'] = $row['num'] - $row['finish_num'];
            unset($row['finsih_num'], $row['num']);
        }
        if (empty($data)) {
            return $this->format_ret(-1, '', '采购通知单已经全部生成完毕，不可再生成入库单');
        }
        $ret = load_model('pur/PurchaseRecordDetailModel')->add_detail_action($record['record_code'], $data);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '采购通知单生成采购入库单明细保存失败');
        }

        load_model('pur/PurchaseRecordModel')->update_num($record['record_code']);

        return $this->format_ret(1);
    }

}
