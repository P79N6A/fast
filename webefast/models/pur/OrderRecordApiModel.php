<?php

require_model('api/JxcOptApiModel');

/**
 * 采购通知单接口
 * @author WMH
 */
class OrderRecordApiModel extends JxcOptApiModel {

    function __construct() {
        parent::__construct('pur_order_record');
        $this->record_type = 'pur_notice';
    }

    public function api_record_create($param) {
        
    }

    public function api_record_get($param) {
        
    }

    public function api_detail_get($param) {
        
    }

    /**
     * API-更新采购通知单明细
     * @author wmh
     * @date 2017-06-12
     * @param array $param
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
            $b_data = $obj_sku->convert_scan_barcode($barcode);
            if (empty($b_data)) {
                return $this->format_ret(-10002, array('barcode' => $barcode), '条码不存在');
            }
            $num = $barcode_num[$barcode];
            if (!is_int((int) $num) || $num < 1) {
                return $this->format_ret(-10005, $val, '数量值无效');
            }

            $barcode_map[$b_data['barcode']] = $barcode;
            $val = array_merge($val, $b_data);
        }

        $ret_detail = $this->deal_detail($detail, $update_mode);
        if ($ret_detail['status'] < 1) {
            return $ret_detail;
        }

        $ret = load_model('pur/OrderRecordDetailModel')->add_detail_action($this->record['order_record_id'], $ret_detail['data']);
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

        $action_note = $update_mode == 1 ? '数量累加' : '数量覆盖';
        $this->set_opt_log('更新明细', 'API-更新明细，' . $action_note . $log_str);

        return $this->format_ret(1, $revert_data, '更新成功');
    }

    public function api_record_accept($param) {
        
    }

    protected function set_opt_log($action_name, $action_note) {
        $log_data = array(
            'pid' => $this->record['order_record_id'],
            'action_name' => $action_name,
            'action_note' => $action_note,
            'sure_status' => $this->record['is_check'] == 1 ? '已确认' : '未确认',
            'finish_status' => $this->record['is_finish'] == 1 ? '已完成' : '未完成',
        );
        $this->set_log($log_data);
    }

    /**
     * 获取商品明细信息
     */
    private function deal_detail(&$detail, $update_mode) {
        $obj_util = load_model('util/ViewUtilModel');
        //获取商品信息
        $sql_values = array();
        $g_code_arr = array_unique(array_column($detail, 'goods_code'));
        $g_code_str = $this->arr_to_in_sql_value($g_code_arr, 'goods_code', $sql_values);
        $sql = "SELECT goods_code,purchase_price AS price,cost_price FROM base_goods WHERE goods_code IN({$g_code_str})";
        $goods_data = $this->db->get_all($sql, $sql_values);
        if (empty($goods_data)) {
            return $this->format_ret(-1, (object) array(), '明细商品不存在');
        }
        $goods_data = $obj_util->get_map_arr($goods_data, 'goods_code');

        //数量为累加模式时，获取单据已存在的商品信息
        $pre_detail = array();
        if ($update_mode == 1) {
            $sql_values = array(':record_code' => $this->record_code);
            $sku_arr = array_unique(array_column($detail, 'sku'));
            $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
            $sql = "SELECT sku,num,price FROM pur_order_record_detail WHERE record_code=:record_code AND sku IN({$sku_str})";
            $pre_detail = $this->db->get_all($sql, $sql_values);
            if (!empty($pre_detail)) {
                $pre_detail = $obj_util->get_map_arr($pre_detail, 'sku');
            }
        }
        $data = array();
        foreach ($detail as $val) {
            $sku = $val['sku'];
            if (isset($data[$sku])) {
                $data[$sku]['num'] += $val['num'];
            }

            $d = array_merge($val, $goods_data[$val['goods_code']]);
            if (isset($pre_detail[$sku])) {
                $pre_d_temp = $pre_detail[$sku];
                $d['price'] = $pre_d_temp['price'];
                $d['num'] += $pre_d_temp['num'];
            }

            $data[$sku] = $d;
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
                FROM pur_order_record_detail AS rd 
                INNER JOIN goods_sku AS gs ON rd.sku=gs.sku 
                INNER JOIN base_goods AS bg ON rd.goods_code=gs.goods_code
                WHERE rd.record_code=:record_code GROUP BY rd.sku";
        $revert_data['detail'] = $this->db->get_all($sql, array(':record_code' => $this->record_code));

        return $revert_data;
    }

}
