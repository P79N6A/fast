<?php

require_model('tb/TbModel');

class IwmsBillHandleModel extends TbModel {

    function __construct() {
        parent::__construct('iwms_bill_data');
    }

    public function deal_bill($record_type) {
        $sql = "SELECT record_code,record_time,record_type,remark,record_data,is_deal,fail_num FROM {$this->table} WHERE is_deal IN(0,2) AND record_type=:record_type";
        $record = $this->db->get_all($sql, array(':record_type' => $record_type));
        if (empty($record)) {
            return $this->format_ret(-1, '', '无待处理单据');
        }

        array_walk($record, function($row) {
            $this->begin_trans();
            $act = "deal_{$row['record_type']}_bill";
            $ret = $this->$act($row);

            if ($ret['status'] != 1) {
                $this->rollback();
                $fail_num = $row['fail_num'] + 1;
                $this->update(array('is_deal' => 2, 'fail_num' => $fail_num, 'fail_reason' => $ret['message']), array('record_code' => $row['record_code'], 'record_type' => $row['record_type']));
            } else {
                $this->commit();
                $this->update(array('is_deal' => 1, 'fail_reason' => ''), array('record_code' => $row['record_code'], 'record_type' => $row['record_type']));
            }
        });

        return $this->format_ret(1);
    }

    /**
     * 处理移仓单
     * @param array $data 数据
     * @return array 处理结果
     */
    private function deal_shift_bill($data) {
        $record_data = json_decode($data['record_data'], TRUE);
        if (empty($record_data)) {
            return $this->format_ret(-1, '', '单据明细异常');
        }
        $obj_shift = load_model('stm/StoreShiftRecordModel');

        $record = array();
        $record['init_code'] = $data['record_code'];
        $record['is_shift_in_time'] = $data['record_time'];
        $record['is_shift_out_time'] = $data['record_time'];
        $record['remark'] = $data['remark'];
        $record['rebate'] = 1;

        $api_store = load_model('wms/iwms/IwmsBillApiModel')->get_api_store();
        $outside_code = array_column($api_store, 'outside_code');
        $store_arr = array_merge(array_column($record_data, 'store_code_out'), array_column($record_data, 'store_code_in'));
        $store_arr = array_unique($store_arr);
        $store_diff = array_diff($store_arr, $outside_code);
        if (!empty($store_diff)) {
            $store_diff_str = implode(',', $store_diff);
            return $this->format_ret(-1, '', $store_diff_str . '仓库未配置');
        }
        $api_store = array_column($api_store, 'shop_store_code', 'outside_code');

        $detail_key = array('barcode', 'num');
        $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = $lof_status['lof_status'];
        if ($is_lof == 1) {
            $detail_key = array_merge($detail_key, array('lof_no', 'production_date'));
        }
        foreach ($record_data as $row) {
            //创建移仓主单据
            $record['record_code'] = $obj_shift->create_fast_bill_sn();
            $record['shift_out_store_code'] = $api_store[$row['store_code_out']];
            $record['shift_in_store_code'] = $api_store[$row['store_code_in']];
            $ret = $obj_shift->do_add_record($record);
            if ($ret['status'] != 1) {
                return $ret;
            }
            $id = $ret['data'];

            //添加移仓明细
            $barcode_arr = array_column($row['detail'], 'barcode');
            $sql_values = array();
            $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
            $sql = "SELECT bg.goods_code,bg.purchase_price,gs.sku,gs.spec1_code,gs.spec2_code,gs.barcode FROM goods_sku gs,base_goods bg WHERE gs.goods_code=bg.goods_code AND gs.barcode IN({$barcode_str})";
            $sku_arr = $this->db->get_all($sql, $sql_values);
            $sku_arr = $this->trans_arr_key($sku_arr, 'barcode');
            $detail = array();
            foreach ($row['detail'] as $d) {
                $d = get_array_vars($d, $detail_key);
                $d = array_merge($d, $sku_arr[$d['barcode']]);
                $detail[] = $d;
            }

            $ret = $this->add_detail($id, $record['shift_out_store_code'], $detail);
            if ($ret['status'] != 1) {
                return $ret;
            }

            //确认单据
            $ret = $obj_shift->update_sure(1, 'is_sure', $id, 1);
            if ($ret['status'] != 1) {
                return $ret;
            }

            //出库
            $ret = $obj_shift->shift_out($id, '', 1);
            if ($ret['status'] != 1) {
                return $ret;
            }
            //入库
            $ret = $obj_shift->do_qz_shift_in($id, '', 0, 1);
            if ($ret['status'] != 1) {
                return $ret;
            }

            //添加日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '入库', 'action_name' => 'IWMS创建移仓单', 'action_note' => 'IWMS移仓单创建并处理', 'module' => "store_shift_record", 'pid' => $id);
            load_model('pur/PurStmLogModel')->insert($log);
        }

        return $this->format_ret(1);
    }

    /**
     * 添加明细
     * @param int $pid 主单据ID
     * @param string $store_code 仓库代码
     * @param array $detail 明细数据
     * @return array 添加结果
     */
    private function add_detail($pid, $store_code, $detail) {
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($pid, $detail);
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $store_code, 'shift_out', $detail);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $ret = load_model('stm/StoreShiftRecordDetailModel')->add_detail_action($pid, $detail);
        return $ret;
    }

    /**
     * 将数组中某个值用作键
     * @param array $data 数据
     * @param string $key_fld 用作键的字段
     * @return array 处理后的数据
     */
    function trans_arr_key($data, $key_fld) {
        $arr = array();
        foreach ($data as $val) {
            $arr[$val[$key_fld]] = $val;
        }
        return $arr;
    }

}
