<?php

require_model('api/JxcOptApiModel');

/**
 * 盘点单接口
 * @author WMH
 */
class TakeStockRecordApiModel extends JxcOptApiModel {

    function __construct() {
        parent::__construct('stm_take_stock_record');
        $this->record_type = 'take_stock';
    }

    public function api_record_create($param) {
        
    }

    /**
     * API-查询盘点单列表
     * @author wmh
     * @date 2017-06-14
     * @param array $param
     * @return array 操作结果
     */
    public function api_record_get($param) {
        //可选字段
        $key_option = array(
            's' => array('start_time', 'end_time', 'store_code'),
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
        if (isset($arr_option['is_check']) && in_array($arr_option['is_check'], array(0, 1))) {
            $arr_option['is_sure'] = $arr_option['is_check'];
        } else {
            $arr_option['is_check'] = '';
        }

        unset($arr_option['is_check']);

        $select = ' `record_code`,`take_stock_time`,`store_code`,`num`,`is_sure`';
        $sql_main = " FROM {$this->table} pr WHERE pr.is_stop=0";
        $sql_values = array();
        //生成sql条件语句
        $this->get_record_sql_where($arr_option, $sql_main, $sql_values, 'pr.');
        //获取主单据信息
        $ret = $this->get_page_from_sql($arr_option, $sql_main, $sql_values, $select);

        $data = $ret['data'];
        if (empty($data)) {
            return $this->format_ret(-10002, (object) array(), 'API_RETURN_MESSAGE_10002');
        }
        filter_fk_name($data, array('store_code|store'));

        $new_data = array();
        foreach ($data as &$row) {
            $new_data[] = array(
                'record_code' => $row['record_code'],
                'record_time' => $row['take_stock_time'],
                'is_check' => $row['is_sure'],
                'store_code' => $row['store_code'],
                'store_name' => $row['store_code_name'],
                'num' => $row['num'],
            );
        }

        $filter = get_array_vars($ret['filter'], array('page', 'page_size', 'page_count', 'record_count'));

        $revert_data = array(
            'filter' => $filter,
            'data' => $new_data,
        );
        return $this->format_ret(1, $revert_data);
    }

    public function api_detail_get($param) {
        
    }

    /**
     * API-盘点单明细更新
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

        $barcode_map = array();
        $obj_sku = load_model('prm/SkuModel');
        foreach ($detail as &$val) {
            $d_required = array();
            $ret_required = valid_assign_array($val, $k_d_required, $d_required, TRUE);
            if ($ret_required['status'] !== TRUE) {
                return $this->format_ret(-10001, (object) array(), '明细存在空数据');
            }

            $barcode = $val['barcode'];
            $num = $barcode_num[$barcode];
            if (!is_int((int) $num) || $num < 1) {
                return $this->format_ret(-10005, $val, '数量值无效');
            }

            $b_data = $obj_sku->convert_scan_barcode($barcode);
            if (empty($b_data)) {
                return $this->format_ret(-10002, array('barcode' => $barcode), '条码不存在');
            }
            $barcode_map[$b_data['barcode']] = $barcode;

            $val = array_merge($val, $b_data);
        }

        $ret_detail = $this->deal_detail($detail, $update_mode, $lof_status['lof_status']);
        if ($ret_detail['status'] < 1) {
            return $ret_detail;
        }
        $this->begin_trans();
        $pid = $this->record['take_stock_record_id'];
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($pid, $ret_detail['data'], $this->record_type);
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $this->record['store_code'], $this->record_type, $ret_detail['data']);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }
        $ret = load_model('stm/TakeStockRecordModel')->add_detail_action($pid, $ret_detail['data'], $this->record_type);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }
        $this->commit();

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
        $finish_status = '未确认';
        if ($this->record['is_sure'] == 1) {
            $finish_status = '已确认';
        } else if ($this->record['is_stop'] == 1) {
            $finish_status = '已验收';
        }
        $log_data = array(
            'pid' => $this->record['take_stock_record_id'],
            'action_name' => $action_name,
            'action_note' => $action_note,
            'finish_status' => $finish_status,
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
        $sql = "SELECT goods_code,sell_price AS price FROM base_goods WHERE goods_code IN({$g_code_str})";
        $goods_data = $this->db->get_all($sql, $sql_values);
        if (empty($goods_data)) {
            return $this->format_ret(-1, (object) array(), '明细商品不存在');
        }
        $goods_data = $obj_util->get_map_arr($goods_data, 'goods_code');

        $pre_detail = array();
        if ($update_mode == 1) {
            $sql_values = array(':record_code' => $this->record['record_code']);
            $sku_arr = array_column($detail, 'sku');
            $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
            $select = 'sku,num';
            $select .= $lof_status == 1 ? ',lof_no' : '';
            $sql = "SELECT {$select} FROM b2b_lof_datail WHERE order_type='take_stock' AND order_code=:record_code AND sku IN({$sku_str})";
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

            if (isset($pre_detail[$k])) {
                $pre_d_temp = $pre_detail[$k];
                $d['price'] = $pre_d_temp['price'];
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
            'total_num' => $record['num'],
        );
        $sql = "SELECT rd.goods_code,bg.goods_name,CONCAT_WS('；',spec1_name,spec2_name) AS goods_spec,gs.barcode,rd.num
                FROM stm_take_stock_record_detail AS rd 
                INNER JOIN goods_sku AS gs ON rd.sku=gs.sku 
                INNER JOIN base_goods AS bg ON rd.goods_code=gs.goods_code
                WHERE rd.record_code=:record_code GROUP BY rd.sku";
        $revert_data['detail'] = $this->db->get_all($sql, array(':record_code' => $this->record_code));

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

}
