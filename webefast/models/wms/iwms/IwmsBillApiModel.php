<?php

require_model('tb/TbModel');

class IwmsBillApiModel extends TbModel {

    function __construct() {
        parent::__construct('iwms_bill_data');
    }

    /**
     * API-写入创建移仓单数据
     * @author wmh
     * @date 2016-01-09
     * @param array $params
     * <pre> 必选: 'record_code','record_time','record_data','store_code_out','store_code_in','detail','barcode','num'
     * <pre> 可选: 'init_code','rebate','remark'
     * <pre> record_data:[{"store_code_out":"A10001","store_code_in":"A10002","detail":[{"barcode":"C111","num":10},{"barcode":"C112","num":10}]},{"store_code_out":"A10003","store_code_in":"A10004","detail":[{"barcode":"C111","num":10}]}]
     * @return array 操作结果
     */
    function api_shift_record_process($params) {
        $key_require = array(
            's' => array('record_code', 'record_time', 'record_data')
        );
        $arr_require = array();
        //提取可选字段中已赋值数据
        $ret_require = valid_assign_array($params, $key_require, $arr_require, true);
        if ($ret_require['status'] === FALSE) {
            return $this->format_ret(-10001, $ret_require['req_empty'], '缺少必填参数或必填参数为空');
        }
        $ret = $this->is_exists_record($arr_require['record_code']);
        if ($ret > 0) {
            return $this->format_ret(-10003, '', '单据已存在');
        }
        if (strtotime($arr_require['record_time']) === FALSE) {
            return $this->format_ret(-10005, '', '日期格式不正确');
        }
        $record_data = json_decode($arr_require['record_data'], true);
        if (empty($record_data)) {
            return $this->format_ret(-10005, '', '单据明细数据格式错误');
        }
        $data = $arr_require;
        $data['remark'] = isset($params['remark']) ? $params['remark'] : '';
        $data['record_type'] = 'shift';
        $data['create_time'] = time();
        unset($arr_require, $params);

        $key_option = array(
            's' => array('store_code_out', 'store_code_in', 'detail')
        );

        $detail = array();
        foreach ($record_data as $row) {
            $arr_option = array();
            $ret_require = valid_assign_array($row, $key_option, $arr_option, true);
            if ($ret_require['status'] === FALSE) {
                return $this->format_ret(-10001, $ret_require['req_empty'], '缺少必填参数或必填参数为空');
            }
            if ($row['store_code_out'] == $row['store_code_in']) {
                return $this->format_ret(-10003, '', '移出仓和移入仓不能相同');
            }
            $detail = array_merge($detail, $row['detail']);
        }

        //校验仓库
        $api_store = $this->get_api_store();
        $api_store = array_column($api_store, 'outside_code');
        $store_code_out = array_column($record_data, 'store_code_out');
        $store_code_in = array_column($record_data, 'store_code_in');
        $store = array_unique(array_merge($store_code_out, $store_code_in));
        $store_diff = array_diff($store, $api_store);
        if (!empty($store_diff)) {
            return $this->format_ret(-10002, $store_diff, '仓库不存在');
        }

        //校验明细
        $ret_check = $this->check_detail($detail);
        if ($ret_check['status'] != 1) {
            return $ret_check;
        }

        $this->begin_trans();
        $ret = parent::insert($data);
        if ($this->affected_rows() != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '创建失败');
        }
        $this->commit();
        return $this->format_ret(1, '', '创建成功');
    }

    /**
     * 检查明细数据
     * @param array $detail 明细
     * @return array 数据
     */
    private function check_detail($detail) {
        $d_require = array(
            's' => array('barcode'), 'i' => array('num')
        );
        $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = $lof_status['lof_status'];
        if ($is_lof == 1) {
            $d_require['s'] = array_merge($d_require['s'], array('lof_no', 'production_date'));
        }

        foreach ($detail as $d) {
            $d_require_arr = array();
            $ret_d_required = valid_assign_array($d, $d_require, $d_require_arr, TRUE);
            if ($ret_d_required['status'] === FALSE) {
                return $this->format_ret("-10001", $ret_d_required['req_empty'], '缺少明细数据成员');
            }
        }

        $barcode = array_unique(array_column($detail, 'barcode'));
        $sql_values = array();
        $sql_str = $this->arr_to_in_sql_value($barcode, 'barcode', $sql_values);
        $sql = "SELECT barcode FROM goods_sku WHERE barcode IN({$sql_str})";
        $barcode_exists = $this->db->get_all_col($sql, $sql_values);
        $barcode_diff = array_diff($barcode, $barcode_exists);
        if (!empty($barcode_diff)) {
            return $this->format_ret(-10002, $barcode_diff, '明细条码不存在');
        }

        return $this->format_ret(1);
    }

    /**
     * 判断单据是否已存在
     * @param string $record_code WMS单号
     * @return int 记录数
     */
    private function is_exists_record($record_code) {
        $sql = "SELECT COUNT(1) FROM {$this->table} WHERE record_code=:record_code";
        $ret = $this->db->get_value($sql, array(':record_code' => $record_code));
        return $ret;
    }

    /**
     * 获取wms仓库
     * @param type $wms_system
     */
    public function get_api_store() {
        $sql = "SELECT ss.shop_store_code,ss.outside_code FROM wms_config wc
            INNER JOIN sys_api_shop_store ss ON wc.wms_config_id = ss.p_id
            WHERE wc.wms_system_code IN('iwms','iwmscloud') AND ss.p_type = 1 AND ss.shop_store_type = 1 AND ss.outside_type = 1";
        $ret = $this->db->get_all($sql);
        return $ret;
    }

}
