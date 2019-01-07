<?php
require_model("wms/WmsShiftInModel");
class ShunfengShiftInModel extends WmsShiftInModel{
    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'shift_in'));
        if (empty($json_data)) {
            return $this->format_ret(-1, '', '移仓单不存在');
        }
        $order = json_decode($json_data, true);
        
        $this->get_wms_cfg($order['shift_in_store_code']);

        $wms_order = array();
        $wms_order['CompanyCode'] = $this->wms_cfg['company'];
        $data = array();
        $data['WarehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['SFOrderType'] = "采购入库";
        $data['ErpOrder'] = $order['record_code'];
        $data['ErpOrderType'] = '采购入库';
        $data['OrderDate'] =  $order['record_time'];
       // $data['ScheduledReceiptDate'] = date('Y-m-d H:i:s', strtotime('+5 day'));
        $data['VendorCode'] = $this->wms_cfg['supplier'];
        $data['ScheduledReceiptDate'] = date('Y-m-d H:i:s', strtotime('+5 day'));
        
        
        $line_num = 1;
        foreach ($order['goods'] as $row) {
         //   $sku = $this->db->get_value("select sku from goods_sku where barcode = :barcode",array(":barcode" => $row['barcode']));
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
        return $this->format_ret(1, $result['data']['PurchaseOrders']['PurchaseOrder']['ReceiptId']);
    }


    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
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
        $this->get_wms_cfg($efast_store_code);
        $method = 'PURCHASE_ORDER_INBOUND_QUERY_SERVICE';

        $data = array();
        $data['CompanyCode'] = $this->wms_cfg['company'];
        $data['PurchaseOrders']['PurchaseOrder']['WarehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['PurchaseOrders']['PurchaseOrder']['ErpOrder'] = $record_code;
        $ret = $this->biz_req($method, $data);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->conv_wms_record_info($ret['data'], $efast_store_code);
        return $ret;
    }

    function conv_wms_record_info($result, $efast_store_code) {
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
            $cp_goods = array();
            $zp_goods = array();
            foreach ($goods as $sub_goods) {
                if($sub_goods['InventoryStatus'] == '10'){
                    $zp_goods[] = array('barcode' => $sub_goods['SkuNo'], 'sl' => $sub_goods['ActualQty']);
                } else {
                    $cp_goods[] = array('barcode' => $sub_goods['SkuNo'], 'sl' => $sub_goods['ActualQty']);
                }
                $ret['goods'][] = array('barcode' => $sub_goods['SkuNo'], 'sl' => $sub_goods['ActualQty']);
            }
            if (!empty($zp_goods)) {
                //收货商品同步
                $ret_zp = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($order['ErpOrder'], 'shift_in', $zp_goods);
                if ($ret_zp['status'] < 0) {
                    return $ret_zp;
                }
            }
            if (!empty($cp_goods)) {
                //收货商品同步
                $ret_cp = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($order['ErpOrder'], 'shift_in', $cp_goods,0);
                if ($ret_cp['status'] < 0) {
                    return $ret_cp;
                }
            }
            
            $data = array('process_flag' => 0, 'wms_order_flow_end_flag' => 1);
            $where = array('record_code' => $order['ErpOrder'], 'record_type' => 'shift_in');
            $this->update_exp('wms_b2b_trade', $data, $where);
        }
        return $this->format_ret(1, $ret);
    }
    
}