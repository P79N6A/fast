<?php

require_model('wms/WmsRecordModel');

class WmsShiftOutModel extends WmsRecordModel {

    function __construct() {
        parent::__construct();
    }

    /**
     * 移仓出库处理
     * @param string $record_code 通知单号
     * @param date $record_time 业务时间
     * @param array $order_mx 回传单据明细
     * @return array 处理结果
     */
    function order_shipping($record_code, $record_time, $order_mx) {
        $record_type = 'shift_out';
        //检查并获取通知单数据
        $ret = $this->check_shift_record($record_code, $record_type);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $notice_info = $ret['data'];
        $force_negative_inv = 0;
        $this->begin_trans();
        //批次处理
        $this->get_wms_cfg($notice_info['shift_out_store_code']);
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        if ($is_lof == 1 && in_array($this->api_product, array('qimen'))) {
            $ret = load_model('wms/WmsSwitchLofModel')->switch_lof_lock($record_code, $record_type);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1, '', '批次处理失败');
            }
            $force_negative_inv = 1;

            //更新非批次通知数量
            $sql = "UPDATE stm_store_shift_record_detail AS rd,(SELECT sku,SUM(init_num) AS out_num FROM b2b_lof_datail WHERE order_code=:code AND order_type='shift_out' GROUP BY sku) AS ld SET rd.out_num=ld.out_num WHERE rd.record_code=:code AND rd.sku=ld.sku";
            $this->db->query($sql, array(':code' => $record_code));

            $sql = "UPDATE stm_store_shift_record AS sr,(SELECT SUM(out_num) AS out_num FROM stm_store_shift_record_detail WHERE record_code=:code) AS rd SET sr.out_num=rd.out_num WHERE sr.record_code=:code";
            $this->db->query($sql, array(':code' => $record_code));
        }

        $ret = load_model('stm/StoreShiftRecordModel')->shift_out($notice_info['shift_record_id'], $record_code, 0, $force_negative_inv);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }

        $this->commit();
        return $ret;
    }

    function order_cancel($msg = '') {
        
    }

    /**
     * 获取上传中间表的单据数据
     * @param string $record_code 移仓单号
     * @return array 数据集
     */
    function get_record_info($record_code) {
        $sql = "SELECT * FROM stm_store_shift_record WHERE record_code  = :record_code";
        $info = ctx()->db->get_row($sql, array(':record_code' => $record_code));
        if (empty($info)) {
            return $this->format_ret(-1, '', '找不到与移仓单');
        }

        $sql = "select sku,out_num as num,price,rebate,out_money as money from stm_store_shift_record_detail where record_code  = :record_code";
        $goods = ctx()->db->get_all($sql, array(':record_code' => $record_code));
        if (empty($goods)) {
            return $this->format_ret(-1, '', '找不到移仓单明细');
        }
        //获取移入仓信息
        $ret_store = load_model('base/StoreModel')->get_by_code($info['shift_in_store_code']);
        $info['store_info'] = $ret_store['data'];

        $ret = $this->incr_lof_info($record_code, 'shift_out', $info['shift_out_store_code'], $goods);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $info['goods'] = array_values($ret['data']);
        return $this->format_ret(1, $info);
    }

}
