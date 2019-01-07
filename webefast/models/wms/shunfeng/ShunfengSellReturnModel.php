<?php

require_model("wms/WmsSellReturnModel");

class ShunfengSellReturnModel extends WmsSellReturnModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_return'));
        $order = json_decode($json_data, true);
         $this->get_record_data($order);   
        $this->get_wms_cfg($order['store_code'],'sell_return');
        $wms_order = array();
        $wms_order['CompanyCode'] = $this->wms_cfg['company'];
        $data = array();
        $data['WarehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['SFOrderType'] = "退货入库";
//        $data['ErpOrder'] = $order['sell_return_code'];
        $data['ErpOrder'] = $this->is_canceled($record_code);
        $data['ErpOrderType'] = '退货入库';
        $data['OrderDate'] = $order['create_time'];
        $data['ScheduledReceiptDate'] = date('Y-m-d H:i:s', strtotime('+5 day'));
        $data['VendorCode'] = $this->wms_cfg['supplier'];
        $sql = "select wms_record_code from wms_oms_trade where record_code='{$order['sell_record_code']}' and record_type='sell_record'";
        $wms_record_code = $this->db->getOne($sql);
        $data['OriginalOrderNo'] = !empty($wms_record_code)?$wms_record_code:'';

        $line_num = 1;
        foreach ($order['goods'] as $row) {
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
        if ($result['data']['PurchaseOrders']['PurchaseOrder']['Result'] == 2) {
            return $this->format_ret(-1, '', $result['data']['PurchaseOrders']['PurchaseOrder']['Note']);
        }

        return $this->format_ret(1, $ret['data']['PurchaseOrders']['PurchaseOrder']['ReceiptId']);

    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code,'sell_return');
        $data = array();
        $data['CompanyCode'] = $this->wms_cfg['company'];
//        $data['PurchaseOrders']['PurchaseOrder']['ErpOrder'] = $record_code;
        $data['PurchaseOrders']['PurchaseOrder']['ErpOrder'] = $this->is_canceled($record_code);
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
        $this->get_wms_cfg($efast_store_code,'sell_return');
        $method = 'PURCHASE_ORDER_INBOUND_QUERY_SERVICE';

        $data = array();
        $data['CompanyCode'] = $this->wms_cfg['company'];
        $data['PurchaseOrders']['PurchaseOrder']['WarehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['PurchaseOrders']['PurchaseOrder']['ErpOrder'] = $record_code;
        $ret = $this->biz_req($method, $data);

        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->conv_wms_record_info($ret['data']);
        return $ret;
      
    }

    function conv_wms_record_info($result) {
        $ret['order_status_txt'] = $result['PurchaseOrders']['PurchaseOrder']['Header']['Status'];
        if ($ret['order_status_txt'] == '收货完成') {
            $ret['order_status'] = 'flow_end';
            $order = $result['PurchaseOrders']['PurchaseOrder']['Header'];
            $ret['efast_record_code'] = $order['ErpOrder'];
            $ret['wms_record_code'] = $order['ReceiptId'];
//            $ret['wms_store_code'] = $efast_store_code;

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

    private function is_canceled($record_code){
        $sql = "select new_record_code from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_return'));
        return !empty($new_record_code)?$new_record_code:$record_code;
    }
}
