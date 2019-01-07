<?php

require_model('api/JxcOptApiModel');

/**
 * 装箱单相关接口
 * @author WMH
 */
class BoxRecordApiModel extends JxcOptApiModel {

    function __construct() {
        parent::__construct('b2b_box_record');
        $this->record_type = 'box_record';
    }

    /**
     * 装箱单生成
     * @param array $param
     * @return array
     */
    public function api_record_create($param) {
        $arr_required = array();
        $k_required = array(
            's' => array('relation_code', 'opt_user_code'),
        );
        $ret_required = valid_assign_array($param, $k_required, $arr_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
        $relation_code = $arr_required['relation_code'];
        $opt_user_code = $arr_required['opt_user_code'];

        $sql = 'SELECT is_store_out FROM wbm_store_out_record WHERE record_code=:record_code';
        $is_store_out = $this->db->get_value($sql, array(':record_code' => $relation_code));
        if ($is_store_out === FALSE) {
            return $this->format_ret(-10002, array('relation_code' => $relation_code), '批发销货单不存在');
        }
        if ($is_store_out === 1) {
            return $this->format_ret(-10006, array('relation_code' => $relation_code), '批发销货单已验收');
        }
        $user_name = $this->get_user_name_by_code($opt_user_code);
        if (empty($user_name)) {
            return $this->format_ret(-10002, array('opt_user_code' => $opt_user_code), '用户不存在');
        }

        $this->begin_trans();
        $ret = $this->produce_box_record($relation_code, $user_name);
        if ($ret['status'] < 1) {
            $this->rollback();
        } else {
            $this->commit();
        }
        $this->record_code = $ret['data']['record_code'];
        $this->get_record_by_code();
        $this->set_opt_log('创建', "API-由销货单{$relation_code}生成装箱单");

        return $ret;
    }

    public function api_record_get($param) {
        
    }

    public function api_detail_get($param) {
        
    }

    /**
     * API-更新装箱单明细
     * @author wmh
     * @date 2017-06-24
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

        $obj_sku = load_model('prm/SkuModel');
        foreach ($detail as &$val) {
            $d_required = array();
            $ret_required = valid_assign_array($val, $k_d_required, $d_required, TRUE);
            if ($ret_required['status'] !== TRUE) {
                return $this->format_ret(-10001, array(), '明细存在空数据');
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
            $val = array_merge($val, $b_data);
        }

        $ret_detail = $this->deal_detail($detail, $update_mode, $lof_status['lof_status']);
        if ($ret_detail['status'] < 1) {
            return $ret_detail;
        }

        $this->begin_trans();
        $ret = $this->insert_multi_duplicate('b2b_box_record_detail', $ret_detail['data'], 'num=VALUES(num)');
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, (object) array(), '更新装箱单明细失败');
        }

        $sql = "UPDATE {$this->table} SET num=(SELECT SUM(num) FROM b2b_box_record_detail WHERE record_code=:code) WHERE record_code=:code";
        $ret = $this->query($sql, array(':code' => $this->record_code));
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, (object) array(), '更新装箱单数量失败');
        }
        $this->commit();

        $revert_data = $this->get_record_comb_data();

        $action_note = $update_mode == 1 ? '数量累加' : '数量覆盖';
        $this->set_opt_log('更新明细', 'API-更新明细，' . $action_note);

        return $this->format_ret(1, $revert_data, '更新成功');
    }

    public function api_record_accept($param) {
        if (empty($param['record_code'])) {
            return $this->format_ret(-10001, array('record_code'), 'API_RETURN_MESSAGE_10001');
        }

        $sql = "SELECT task_code FROM {$this->table} WHERE record_code=:code";
        $task_code = $this->db->get_value($sql, array(':code' => $param['record_code']));
        if ($task_code === FALSE) {
            return $this->format_ret(-10002, (object) array(), '装箱单关联装箱任务不存在');
        }
        $this->record_code = $param['record_code'];
        $this->begin_trans();
        $ret = load_model('sys/RecordScanBoxModel')->b2b_box_record_ys($task_code, $param['record_code']);
        if ($ret['status'] < 1) {
            $this->rollback();
        } else {
            $ret['message'] = '验收成功';
            $this->commit();
        }
        $ret['data'] = (object) array();

        $this->get_record_by_code();
        $this->set_opt_log('验收', 'API-验收装箱单');

        return $ret;
    }

    /**
     * 装箱单打印数据获取
     * @param array $param
     * @return array
     */
    public function api_box_record_print($param) {
        if (!empty($param['is_template']) && strtolower($param['is_template']) == 'sjboxprinter') {
            $revert_data = array(
                'record_code' => '',
                'box_order' => '',
                'relation_code' => '',
                'store_name' => '',
                'scan_user' => '',
                'total_num' => '',
                'total_money' => '',
                'create_time' => '',
                'detail' => array(
                    array('goods_code' => '', 'goods_name' => '', 'goods_spec' => '', 'barcode' => '', 'num' => ''),
                ),
            );
            return $this->format_ret(1, $revert_data, '装箱单打印数据结构');
        }
        if (empty($param['record_code'])) {
            return $this->format_ret(-10001, array('record_code'), 'API_RETURN_MESSAGE_10001');
        }
        $record_code = $param['record_code'];
        $sql = "SELECT br.record_code,br.box_order AS box_no,bt.relation_code,br.store_code,br.num AS total_num,br.money AS total_amount,
                br.scan_user,br.create_time,br.is_check_and_accept FROM {$this->table} AS br 
                INNER JOIN b2b_box_task AS bt ON br.task_code=bt.task_code
                WHERE br.record_code=:code";
        $box_record = $this->db->get_row($sql, array(':code' => $record_code));
        if (empty($box_record)) {
            return $this->format_ret(-10002, array('record_code' => $record_code), '装箱单不存在');
        }
        if ($box_record['is_check_and_accept'] != 1) {
            return $this->format_ret(-1, array('record_code' => $record_code), '装箱单未完成');
        }
        $box_record['store_name'] = get_store_name_by_code($box_record['store_code']);
        $revert_data = get_array_vars($box_record, array('record_code', 'box_no', 'relation_code', 'store_name', 'total_num', 'total_amount', 'scan_user', 'create_time'));

        $sql = "SELECT rd.goods_code,bg.goods_name,CONCAT_WS('；',gs.spec1_name,gs.spec2_name) AS goods_spec,gs.barcode,rd.num 
                FROM {$this->detail_table_map[$this->record_type]} AS rd 
                INNER JOIN goods_sku AS gs ON rd.sku=gs.sku 
                INNER JOIN base_goods AS bg ON rd.goods_code=bg.goods_code
                WHERE rd.record_code=:code";
        $revert_data['detail'] = $this->db->get_all($sql, array(':code' => $record_code));
        return $this->format_ret(1, $revert_data, '装箱单打印数据');
    }

    /**
     * 箱唛打印数据获取
     * @param array $param
     * @return array
     */
    public function api_box_record_mark_print($param) {
        return $this->format_ret(1, (object) array());
    }

    protected function set_opt_log($action_name, $action_note) {
        $log_data = array(
            'pid' => $this->record['id'],
            'action_name' => $action_name,
            'action_note' => $action_note,
        );
        $this->set_log($log_data);
    }

    /**
     * 生成装箱单
     * @param string $relation_code 批发销货单号
     * @param string $create_user 操作人
     * @return array
     */
    public function produce_box_record($relation_code, $create_user) {
        $sql = "SELECT bt.task_code FROM b2b_box_task AS bt WHERE bt.record_type='wbm_store_out' AND bt.relation_code=:relation_code";
        $task_code = $this->db->get_value($sql, array(':relation_code' => $relation_code));
        if (!empty($task_code)) {
            $sql = "SELECT br.record_code FROM {$this->table} AS br WHERE br.task_code=:task_code AND br.is_check_and_accept=0";
            $box_record_code = $this->db->get_value($sql, array(':task_code' => $task_code));
            if (!empty($box_record_code)) {
                return $this->format_ret(2, array('record_code' => $box_record_code), '存在未完成的装箱单');
            }
        }

        $sql = "SELECT COUNT(1) FROM wbm_store_out_record_detail WHERE record_code=:record_code and num>0";
        $scan_mx_count = $this->db->get_value($sql, array(':record_code' => $relation_code));
        if ($scan_mx_count > 0 && empty($task_code)) {
            return $this->format_ret(-1, array('relation_code' => $relation_code), '此单据已使用普通扫描');
        }

        $sql = "SELECT store_out_record_id AS record_id,record_code,store_code,order_time,relation_code,record_time,distributor_code FROM wbm_store_out_record WHERE record_code=:record_code";
        $store_out_record = ctx()->db->get_row($sql, array(':record_code' => $relation_code));

        if (empty($task_code)) {
            $ret = load_model('sys/RecordScanBoxModel')->create_box_task($relation_code, 'wbm_store_out', $relation_code, $store_out_record['store_code'], $store_out_record['record_time'], $create_user);
            if ($ret['status'] < 1) {
                $ret['data'] = array('relation_code' => $relation_code);
                return $ret;
            }
            $task_code = $ret['data'];
        }

        $ret = load_model('sys/RecordScanBoxModel')->create_box_record($task_code, $store_out_record['store_code'], $create_user);
        if ($ret['status'] < 1) {
            $ret['data'] = array('relation_code' => $relation_code);
            return $ret;
        }
        $box_code = $ret['data']['box_code'];

        return $this->format_ret(1, array('record_code' => $box_code), '生成装箱单成功');
    }

    /**
     * 获取商品明细信息
     */
    private function deal_detail($detail, $update_mode, $lof_status) {
        $obj_util = load_model('util/ViewUtilModel');

        //数量为累加模式时，获取单据已存在的商品信息
        $pre_detail = array();
        if ($update_mode == 1) {
            $sql_values = array();
            $sku_arr = array_column($detail, 'sku');
            $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
            $sql_values[':record_code'] = $this->record['record_code'];
            $select = 'sku,num';
            $select .= $lof_status == 1 ? ',lof_no' : '';
            $sql = "SELECT {$select} FROM b2b_box_record_detail WHERE record_code=:record_code AND sku IN({$sku_str})";
            $pre_detail = $this->db->get_all($sql, $sql_values);
            if (!empty($pre_detail)) {
                $key_fld = $lof_status == 1 ? 'sku,lof_no' : 'sku';
                $pre_detail = $obj_util->get_map_arr($pre_detail, $key_fld);
            }
        }

        if ($lof_status != 1) {
            $sys_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
            $sys_lof = $sys_lof['data'];
        }

        $data = array();
        foreach ($detail as $val) {
            $k = $lof_status == 1 ? $val['sku'] . ',' . $val['lof_no'] : $val['sku'];
            if (isset($data[$k])) {
                $data[$k]['num'] += $val['num'];
                continue;
            }
            if (isset($pre_detail[$k])) {
                $pre_d_temp = $pre_detail[$k];
                $val['num'] += $pre_d_temp['num'];
            }
            $val['record_code'] = $this->record_code;
            $val['task_code'] = $this->record['task_code'];
            $val['create_time'] = date('Y-m-d H:i:s');
            $val['lof_no'] = empty($val['lof_no']) ? $sys_lof['lof_no'] : $val['lof_no'];
            $val['production_date'] = empty($val['production_date']) ? $sys_lof['production_date'] : $val['production_date'];
            $data[$k] = $val;
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
                FROM b2b_box_record_detail AS rd 
                INNER JOIN goods_sku AS gs ON rd.sku=gs.sku 
                INNER JOIN base_goods AS bg ON rd.goods_code=gs.goods_code
                WHERE rd.record_code=:record_code GROUP BY rd.sku";
        $revert_data['detail'] = $this->db->get_all($sql, array(':record_code' => $this->record_code));

        return $revert_data;
    }

    private function get_user_name_by_code($user_code) {
        $sql = 'SELECT user_name FROM sys_user WHERE user_code=:user_code';
        return $this->db->get_value($sql, array(':user_code' => $user_code));
    }

}
