<?php

require_model("wms/WmsGoodsDiyRecordModel");

class IwmscloudGoodsDiyRecordModel extends WmsGoodsDiyRecordModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "SELECT record_type FROM stm_goods_diy_record WHERE record_code=:record_code";
        $record_type = $this->db->get_value($sql, array(':record_code' => $record_code));
        $record_type = $record_type == 1 ? 'stm_split' : 'stm_diy';
        $sql = "SELECT json_data FROM wms_b2b_trade WHERE record_code = :record_code and record_type = :record_type";
        $json_data = $this->db->get_value($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
        $order = json_decode($json_data, true);
        $this->get_wms_cfg($order['store_code']);
        $wms_order = array(
            'WarehouseCode' => $this->wms_cfg['wms_store_code'],
            'SyncBillId' => $order['record_code'],
            'BillType' => $order['record_type'],
        );

        $order_goods = array();
        foreach ($order['goods'] as $row) {
            if ($row['is_child'] == 1) {
                continue;
            }
            $t_row = array();
            $t_row['SkuCode'] = $row['barcode'];
            $t_row['normalQuantity'] = abs($row['num']);
            $order_goods[] = $t_row;
        }
        $wms_order['BillProductGoods'] = $order_goods;
        return $this->format_ret(1, $wms_order);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'ewms.ProductProcessbill.set';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            return $this->format_ret(1, $ret['data']['wmsid']);
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'ewms.billcancel.set';
        $req = array('bill_type' => 'productprocess.normal', 'bill_code' => $record_code);
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0 && $ret['data']['error'] == 'BIZID_NOT_FOUND') {
            return $this->format_ret(1, $ret['data']);
        }
        return $ret;
    }

    function wms_record_info($record_code, $efast_store_code) {
        return $this->format_ret(-1, '', '不支持获取');
    }

}
