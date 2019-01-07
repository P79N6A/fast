<?php

require_model('wms/WmsRecordModel');

class WmsShiftInModel extends WmsRecordModel {

    function __construct() {
        parent::__construct();
    }

    /**
     * 移仓入库处理
     * @param string $record_code 通知单号
     * @param date $record_time 业务时间
     * @param array $order_mx 回传单据明细
     * @return array 处理结果
     */
    function order_shipping($record_code, $record_time, $order_mx) {
        $record_type = 'shift_in';
        //检查并获取通知单数据
        $ret = $this->check_shift_record($record_code, $record_type);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $notice_info = $ret['data'];
        $this->get_wms_cfg($notice_info['shift_in_store_code']);

        $this->begin_trans();
        $ret = $this->create_b2bdetail_record($notice_info);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '明细处理失败');
        }
        $ret = load_model('stm/StoreShiftRecordModel')->do_qz_shift_in($notice_info['shift_record_id'], $record_code, 1);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }

        $this->commit();
        return $ret;
    }

    function create_b2bdetail_record($notice_info) {
        $record_code = $notice_info['record_code'];
        $time = time();
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        if ($is_lof == 1 && in_array($this->api_product, array('qimen'))) {
            $lof_data = $this->get_lof_data($record_code, 'shift_in', $record_code, $notice_info['shift_in_store_code']);
            $append_info = array('pid' => $notice_info['shift_record_id'], 'create_time' => time());
            $lof_data = load_model('util/ViewUtilModel')->set_arr_el_val($lof_data, $append_info);
            $ret = $this->insert_multi_duplicate('b2b_lof_datail', $lof_data, 'num= VALUES(num)');
            if ($ret['status'] < 1) {
                return $ret;
            }
            $ret = load_model('prm/GoodsLofModel')->add_detail_action(0, $lof_data);
            if ($ret['status'] < 1) {
                return $ret;
            }
        } else {
            $sql = "INSERT INTO b2b_lof_datail (pid,order_code,order_type,goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,num,init_num,create_time)
    	SELECT b.pid, b.order_code,'shift_in', b.goods_code, b.spec1_code, b.spec2_code, b.sku,'{$notice_info['shift_in_store_code']}', b.lof_no, b.production_date,o.wms_sl as num, b.init_num,{$time} as create_time  
        FROM b2b_lof_datail  b
	INNER JOIN goods_sku s  ON b.sku=s.sku
	INNER JOIN wms_b2b_order o ON s.barcode=o.barcode AND b.order_code=o.record_code
    	WHERE  b.order_code  ='{$record_code}' AND o.wms_sl>0 AND b.order_type = 'shift_out' AND b.store_code = '{$notice_info['shift_out_store_code']}' ON DUPLICATE KEY UPDATE num=VALUES(num)";
            $ret = $this->query($sql);
        }


        $sql = "insert into stm_store_shift_record_detail (record_code,sku,in_num) 
        SELECT order_code as record_code,sku,sum(num) as in_num  from b2b_lof_datail where 
        order_code ='{$record_code}' and order_type = 'shift_in' GROUP BY sku
        ON DUPLICATE KEY UPDATE in_num = VALUES(in_num)";

        $ret = $this->query($sql);
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
        $info['goods'] = ctx()->db->get_all($sql, array(':record_code' => $record_code));
        if (empty($info)) {
            return $this->format_ret(-1, '', '找不到移仓单明细');
        }
        $ret = $this->append_mx_barcode_by_sku($info['goods'], 1, 'price,rebate,money');
        if ($ret['status'] < 0) {
            return $ret;
        }
        $info['goods'] = $ret['data'];
        return $this->format_ret(1, $info);
    }

}
