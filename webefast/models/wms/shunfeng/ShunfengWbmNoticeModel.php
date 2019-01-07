<?php

require_model("wms/WmsWbmNoticeModel");

class ShunfengWbmNoticeModel extends WmsWbmNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'wbm_notice'));
        $order = json_decode($json_data, true);
        $this->get_wms_cfg($order['store_code']);
        
        $wms_order = array();
        $wms_order['CompanyCode'] = $this->wms_cfg['company'];
        
        $data = array();
  
        $data['WarehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['SfOrderType'] = "销售订单";
        $data['ErpOrder'] = $order['record_code'];
        $data['TradeOrderDateTime'] = $order['record_time'];

   
        $data['OrderReceiverInfo']['ReceiverCompany'] = $order['distributor']['custom_name'];
        $data['OrderReceiverInfo']['ReceiverName'] = $order['distributor']['custom_name'];
        $data['OrderReceiverInfo']['ReceiverZipCode'] = $order['distributor']['zipcode'];
        $data['OrderReceiverInfo']['ReceiverMobile'] = (string) $order['distributor']['mobile'];
        $data['OrderReceiverInfo']['ReceiverAddress'] = (string) $order['distributor']['address'];
        $data['OrderCarrier']['Carrier'] = '';
        $data['OrderCarrier']['CarrierProduct'] = '';
        $line_num = 1;
        foreach ($order['goods'] as $row) {
            $key_arr = array('goods_name','trade_price');
            $sku = $this->db->get_value("select sku from goods_sku where barcode = :barcode",array(":barcode" => $row['barcode']));
            $sku_info =  load_model('goods/SkuCModel')->get_sku_info($sku,$key_arr);
            $order_goods = array();
            $order_goods['ErpOrderLineNum'] = $line_num;
            $order_goods['SkuNo'] = $row['barcode'];
            $order_goods['ItemName'] = $sku_info['goods_name'];
            $order_goods['ItemPrice'] = $row['price'];
            $order_goods['ItemQuantity'] = $row['num'];
         
            $data['OrderItems'][] = array('OrderItem' => $order_goods);
            $line_num++;
        }
        $wms_order['SaleOrders']['SaleOrder'] = $data;
        return $this->format_ret(1, $wms_order);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $method = 'SALE_ORDER_SERVICE';
        $result = $this->biz_req($method, $ret['data']);
        if ($result['status'] < 0) {
            return $result;
        }
        if ($result['status'] == 2 && $result['data']['SaleOrders']['SaleOrder']['Result'] == 2) {
            return $this->format_ret(-1, '', $result['data']['SaleOrders']['SaleOrder']['Note']);
        }
        return $this->format_ret(1, $ret['data']['SaleOrders']['SaleOrder']['ShipmentId']);
    }

//状态回传
    function wms_record_info($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'SALE_ORDER_OUTBOUND_DETAIL_QUERY_SERVICE';
        $data = array();
        $data['CompanyCode'] = $this->wms_cfg['company'];
        $data['SaleOrders']['SaleOrder']['WarehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['SaleOrders']['SaleOrder']['ErpOrder'] = $record_code;
        $ret = $this->biz_req($method, $data);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->conv_wms_record_info($ret['data'], $efast_store_code);
        return $ret;
    }

    function conv_wms_record_info($result, $efast_store_code) {
        $status_txt_map = array('flow_end' => '已收发货', 'upload' => '已上传', 'wait_upload' => '未上传');
        //根据顺丰返回的操作日志 解析当前订单所处的订单状态
        $order_status = $result['SaleOrders']['SaleOrder']['Header']['DataStatus'];
        //是否已出库
        if(empty($order_status)){
            $ret['order_status'] = 'wait_upload';
        } elseif ($order_status =='2900' || $order_status == '3900') {
            $ret['order_status'] = 'flow_end';
        } else {
            $ret['order_status'] = 'upload';
        }
        $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
        if ($ret['order_status'] == 'flow_end') {
            $order = $result['SaleOrders']['SaleOrder']['Header'];
            $ret['efast_record_code'] = $order['ErpOrder'];
            $ret['wms_record_code'] = $order['ShipmentId'];
            $ret['wms_store_code'] = $efast_store_code;
            $ret['express_code'] = array_search($order['Carrier'], $this->shipping);
            $ret['express_no'] = $order['WayBillNo'];

            //发货时间
            $ret['flow_end_time'] = isset($order['ActualShipDateTime'])?$order['ActualShipDateTime']:'';
            $goods_ret = $result['SaleOrders']['SaleOrder']['Items']['Item'];
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


    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'CANCEL_SALE_ORDER_SERVICE';
        $req = array();
        $req['CompanyCode'] = $this->wms_cfg['company'];
        $req['SaleOrders']['SaleOrder']['ErpOrder'] = $record_code;
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0) {
            return $ret;
        }
        if ($ret['status'] == 2) {
            return $this->format_ret(-1, '', $ret['data']['SaleOrders']['SaleOrder']['Note']);
        }
        return $this->format_ret(1, $ret['data']['SaleOrders']['SaleOrder']['ErpOrder']);
    }
    

}
