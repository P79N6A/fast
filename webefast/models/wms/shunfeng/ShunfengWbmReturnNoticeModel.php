<?php

require_model("wms/WmsPurNoticeModel");

class ShunfengWbmReturnNoticeModel extends WmsPurNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'wbm_return_notice'));
        $order = json_decode($json_data, true);
        $this->get_wms_cfg($order['store_code'],'wbm_return_notice');
        $wms_order = array();
        $wms_order['CompanyCode'] = $this->wms_cfg['company'];
        $data = array();
        $data['WarehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['SFOrderType'] = "退货入库";
        $data['ErpOrder'] = $order['return_notice_code'];
        $ErpOrderType = load_model('wbm/ReturnNoticeRecordModel')->return_type[$order['return_type_code']];
        $data['ErpOrderType'] = !empty($ErpOrderType) ? $ErpOrderType : '退货入库';
        $data['OrderDate'] = $order['order_time'];
        $data['ScheduledReceiptDate'] = date('Y-m-d H:i:s', strtotime('+5 day'));
        $data['VendorCode'] = $this->wms_cfg['supplier'];

        $line_num = 1;
        foreach ($order['goods'] as $row) {
            $sku = $this->db->get_value("select sku from goods_sku where barcode = :barcode",array(":barcode" => $row['barcode']));
//            $key_arr = array('goods_name');
//            $sku_info =  load_model('goods/SkuCModel')->get_sku_info($sku,$key_arr);
            $t_row = array();
            $t_row['ErpOrderLineNum'] = $line_num;
            $t_row['SkuNo'] = $row['barcode'];
            $t_row['Qty'] = $row['num'];
            $data['Items'][]['Item'] = $t_row;
            $line_num++;
        }
        $wms_order['PurchaseOrders']['PurchaseOrder'] = $data;
        return $this->format_ret(1, $wms_order);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'PURCHASE_ORDER_SERVICE';
       
        $result = $this->biz_req($method, $wms_order);
        if ($result['status'] < 0) {
            return $result;
        }
        if ($result['status'] == 2 && $result['data']['PurchaseOrders']['PurchaseOrder']['Result'] == 2) {
            return $this->format_ret(-1, '', $result['data']['PurchaseOrders']['PurchaseOrder']['Note']);
        }
        return $this->format_ret(1, $ret['data']['PurchaseOrders']['PurchaseOrder']['ReceiptId']);
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code,'wbm_return_notice');
        $data = array();
        $data['CompanyCode'] = $this->wms_cfg['company'];
        $data['PurchaseOrders']['PurchaseOrder']['ErpOrder'] = $record_code;
        $method = 'CANCEL_PURCHASE_ORDER_SERVICE';
        $result = $this->biz_req($method, $data);
        if($result['status'] < 0){
             return $result;
        }
        if ($result['data']['PurchaseOrders']['PurchaseOrder']['Result'] == 2) {
            return $this->format_ret(-1, '', $result['data']['PurchaseOrders']['PurchaseOrder']['Note']);
        }
        return $this->format_ret(1, $result['data']['PurchaseOrders']['PurchaseOrder']['ErpOrder']);
    }

    function wms_record_info($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code,'wbm_return_notice');
        $method = 'PURCHASE_ORDER_INBOUND_QUERY_SERVICE';

        $data = array();
        $data['CompanyCode'] = $this->wms_cfg['company'];
        $data['PurchaseOrders']['PurchaseOrder']['WarehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['PurchaseOrders']['PurchaseOrder']['ErpOrder'] = $record_code;
        $ret = $this->biz_req($method, $data);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->conv_wms_record_info($ret['data'],$efast_store_code);
        return $ret;
    }

    function conv_wms_record_info($result,$efast_store_code) {
        $ret['order_status_txt'] = $result['PurchaseOrders']['PurchaseOrder']['Header']['Status'];
        if ($ret['order_status_txt'] == '收货完成') {
            $ret['order_status'] = 'flow_end';
            $order = $result['PurchaseOrders']['PurchaseOrder']['Header'];
            $ret['efast_record_code'] = $order['ErpOrder'];
            $ret['wms_record_code'] = $order['ReceiptId'];
            $ret['wms_store_code'] = $efast_store_code;

            //发货时间
            $ret['flow_end_time'] = isset($order['CloseDate'])?$order['CloseDate']:'';
            $goods_ret = $result['PurchaseOrders']['PurchaseOrder']['Items']['Item'];
            if (isset($goods_ret[0])) {
                $goods = $goods_ret;
            } else {
                $goods[0] = $goods_ret;
            }
            foreach ($goods as $sub_goods) {
                $ret['goods'][] = array('barcode' => $sub_goods['SkuNo'], 'sl' => $sub_goods['ActualQty']);
            }
        }
        return $this->format_ret(1, $ret);
    }

}
