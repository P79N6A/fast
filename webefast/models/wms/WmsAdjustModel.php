<?php

require_model('wms/WmsRecordModel');

class WmsAdjustModel extends WmsRecordModel {

    function __construct() {
        parent::__construct();
    }

    /**
     * $order_mx = array('barcode'=>,'num'=>)
     */
    function order_shipping($record_code, $record_time, $order_mx) {

        $record_info = $this->get_record_info($record_code);

        $stock_adjus['record_code'] = $record_code;
        $stock_adjus['record_time'] = empty($record_time || $record_time == '0000-00-00 00:00:00') ? date('Y-m-d H:i:s') : date('Y-m-d', strtotime($record_info['process_time']));
        $stock_adjus['adjust_type'] = 802;
        $stock_adjus['store_code'] = $record_info['efast_store_code'];
        $stock_adjus['init_code'] = $record_info['wms_record_code'];
        $stock_adjus['is_add_person'] = 'WMS';
        $this->begin_trans();
        $ret = load_model('stm/StockAdjustRecordModel')->insert($stock_adjus);
        $id = $ret['data'];
        $ret_goods_data = $this->get_record_goods($record_code);
        if ($ret_goods_data['status']<1) {
            $this->rollback();
            return $ret_goods_data;
        }
        //添加调整单创建日志
        $log = array('user_id' => 'WMS', 'user_code' => 'WMS', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '创建','action_note'=>'WMS创建调整单', 'module' => "stock_adjust_record", 'pid' => $id);
        load_model('pur/PurStmLogModel')->insert($log);

        $goods_data = &$ret_goods_data['data'];

        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $goods_data);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $stock_adjus['store_code'], 'adjust', $goods_data);
       if ($ret['status']<1) {
            $this->rollback();
            return $ret;
        } 
        //调整单明细添加
        $ret = load_model('stm/StmStockAdjustRecordDetailModel')->add_detail_action($id, $goods_data);
        $ret = load_model('stm/StockAdjustRecordModel')->checkin($id);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }

        $log = array('user_id' => 'WMS', 'user_code' => 'WMS', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '验收', 'action_name' => '创建', 'action_note' => 'WMS验收调整单', 'module' => "stock_adjust_record", 'pid' => $id);
        load_model('pur/PurStmLogModel')->insert($log);
        $this->commit();


        return $ret;
    }

    function order_cancel($msg = '') {
        
    }

    function get_record_info($record_code) {
        $sql = "select * from wms_b2b_trade where record_type=:record_type AND record_code=:record_code ";
        return $this->db->get_row($sql, array(':record_type' => 'adjust', 'record_code' => $record_code));
    }

    function get_record_goods($record_code) {
        $sql = "select o.wms_sl as num,s.sku,o.barcode,s.goods_code,s.spec1_code,s.spec2_code from wms_b2b_order o INNER JOIN goods_sku s ON o.barcode=s.barcode where record_type=:record_type AND record_code=:record_code ";
        $data = $this->db->get_all($sql, array(':record_type' => 'adjust', 'record_code' => $record_code));
        $no_sku_barcode = array();
        if(empty($data)){
            return    $this->format_ret(-1,'','调整明细不能为空');
        }
        
        foreach($data as $val){
            if(empty($val['sku'])){
               $no_sku_barcode[] = $val['barcode'];
            }
        }
        
        
        $ret = $this->format_ret(1, $data);
        if(!empty($no_sku_barcode)){
            $msg = "WMS条码在系统中不存在：".  implode(",", $no_sku_barcode);
            $ret = $this->format_ret(-1,$data,$msg );
        }

        return $ret;
    }

}
