<?php

require_model("wms/WmsSellRecordModel");

class ShunfengSellRecordModel extends WmsSellRecordModel {

    public $get_shipping_name = array(
        'SF' => '顺丰速运',
        //'ZT' => '自提承运商',
		'ZT' => '自提',
        'EMS' => '中国邮政',
        'YUNDA' => '韵达快递',
        'STO' => '申通快递',
        'ZTO' => '中通快递',
        'YTO' => '圆通快递',
        'HT' => '汇通快递',
        'SFGR' => '云仓专配隔日',
        'SFCR' => '云仓专配次日',
        'SFCR2' => '顺丰次日',
        'SFGR2' => '顺丰特惠',
        'SFCC2' => '顺丰次晨',
        'SFJR2' => '顺丰即日',
        'JDCOD' => '京东快递'
    );

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
        $order = json_decode($json_data, true);
         $this->get_record_data($order);   
        $this->get_wms_cfg($order['store_code']);
        $wms_order = array();
        $wms_order['CompanyCode'] = $this->wms_cfg['company'];
        $data = array();
        if (strtolower($order['pay_type']) == 'cod') {
            $payable_money = $order['payable_money'] - $order['paid_money'];
            $data['amount'] = number_format($order['payable_money'] - $order['paid_money'], 2, '.', '');
        } else {
            $payable_money = $order['payable_money'];
            $data['PayDateTime'] = $order['pay_time'];
        }

        $sql = "select shop_id,shop_name from base_shop where shop_code = '{$order['shop_code']}'";
        $shop_row = ctx()->db->get_row($sql);
        if (empty($shop_row)) {
            return $this->format_ret(-1, '', '找不到订单对应的网店');
        }

        $data['WarehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['SfOrderType'] = "销售订单";
//        $data['ErpOrder'] = $order['sell_record_code'];
        $data['ErpOrder'] = $this->is_canceled($record_code);
        $data['TradeOrderDateTime'] = $order['record_time'];

        $data['ShopName'] = $shop_row['shop_name'];
        $data['DeliveryDate'] = $order['plan_send_time']; //发货时间
        if ($data['DeliveryDate'] == '0000-00-00 00:00:00') {
            $data['DeliveryDate'] = date('Y-m-d H:i:s', strtotime($order['TradeOrderDateTime']) + 3600 * 24);
        }

        $sql_pay = "select pay_type_name from base_pay_type where pay_type_code = '{$order['pay_code']}'";
        $pay_name = (string) $this->db->getOne($sql_pay);

        $data['PaymentMethod'] = $pay_name;
        $data['OrderTotalAmount'] = $order['payable_money'];
        $data['ActualAmount'] = $order['paid_money'];
        $data['Freight'] = $order['express_money'];
        $data['OrderNote'] = $order['order_remark'];
        $data['CompanyNote'] = $order['seller_remark'];
        $data['IsInvoice'] = 'N';
        //顺丰不传发票信息 不能确定 InvoiceType 发票类型，接口报错
//        if (!empty($order['invoice_title'])) {
//            $data['IsInvoice'] = 'Y';
//            $data['OrderInvoice']['InvoiceType'] = $order['invoice_type'];
//            $data['OrderInvoice']['InvoiceTitle'] = $order['invoice_title'];
//            $data['OrderInvoice']['InvoiceContent'] = $order['invoice_content'];
//        }
        $express_code_arr = array('SFGR', 'SFCR', 'SFGR2', 'SFCR2', 'SFCC2', 'SFJR2');
        if (in_array($order['express_code'], $express_code_arr)) {
            $data['OrderCarrier']['Carrier'] = '顺丰速运';
            $data['OrderCarrier']['CarrierProduct'] = $this->get_shipping_name[$order['express_code']];
        } else {
            $data['OrderCarrier']['Carrier'] = $this->get_shipping_name[$order['express_code']];
            $data['OrderCarrier']['CarrierProduct'] = '标准快递';
        }

        $data['OrderCarrier']['WaybillNo'] = $order['express_no'];
        if (isset($this->wms_cfg['monthly_account'])) {
            $data['OrderCarrier']['MonthlyAccount'] = $this->wms_cfg['monthly_account'];
        }
        $data['OrderReceiverInfo']['ReceiverCompany'] = '个人';
        $data['OrderReceiverInfo']['ReceiverName'] = $this->html_decode($order['receiver_name']);
        $data['OrderReceiverInfo']['ReceiverZipCode'] = $order['receiver_zip_code'];
        $data['OrderReceiverInfo']['ReceiverMobile'] = $order['receiver_mobile'];
        $data['OrderReceiverInfo']['ReceiverPhone'] = $order['receiver_phone'];
        $data['OrderReceiverInfo']['ReceiverCountry'] = $this->get_area_name($order['receiver_country']);
        $data['OrderReceiverInfo']['ReceiverProvince'] = $this->get_area_name($order['receiver_province']);
        $data['OrderReceiverInfo']['ReceiverCity'] = $this->get_area_name($order['receiver_city']);
        $data['OrderReceiverInfo']['ReceiverArea'] = $this->get_area_name($order['receiver_district']);
        $data['OrderReceiverInfo']['ReceiverAddress'] = $this->html_decode($order['receiver_address']);
        $send_info = load_model('base/ShopModel')->get_send_info($order['shop_code'],$order['store_code']);
        $data['OrderSenderInfo']['SenderName'] = $send_info['data']['contact_person'];
        $data['OrderSenderInfo']['SenderMobile'] = $send_info['data']['contact_tel'];
        $data['OrderSenderInfo']['ReceiverProvince'] = $this->get_area_name($send_info['data']['province']);
        $data['OrderSenderInfo']['ReceiverCity'] = $this->get_area_name($send_info['data']['city']);
        $data['OrderSenderInfo']['ReceiverArea'] = $this->get_area_name($send_info['data']['district']);
        $data['OrderSenderInfo']['ReceiverAddress'] = $this->html_decode($send_info['data']['address']);
        $line_num = 1;
        foreach ($order['goods'] as $row) {
            $order_goods = array();
            $order_goods['ErpOrderLineNum'] = $line_num;
            $order_goods['SkuNo'] = $row['barcode'];
            $order_goods['ItemName'] = $row['goods_name'];
            $order_goods['ItemQuantity'] = $row['num'];
            $item_price = $row['goods_price'];
            $allow_ft = true; //3.0有参数
            if ($allow_ft) {
                $item_price = number_format($row['avg_money'] / $row['num'], '2', '.', '');
            }
            $order_goods['ItemPrice'] = $item_price;
            $order_goods['ItemDiscount'] = number_format($row['goods_price'] - $row['avg_money'] / $row['num'], '2', '.', ''); //优惠价
            $data['OrderItems'][] = array('OrderItem' => $order_goods);
            $line_num++;
        }
//        $order_arr = array();
//        $order_arr['SaleOrder'][] = $data;
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
        $this->wms_cfg = array();
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
        if(!empty($ret['data'])){
            $ret = $this->conv_wms_record_info($ret['data'], $efast_store_code);
        }
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
            if ($order['CarrierProduct'] == '标准快递') {
                $express_code = array_search($order['Carrier'], $this->get_shipping_name);
            } else {
                $express_code = array_search($order['CarrierProduct'], $this->get_shipping_name);
            }
            $ret['express_code'] = $express_code;
            $ret['express_no'] = $order['WayBillNo'];
            $ret['order_weight'] = !empty($order['Weight']) ? $order['Weight'] : 0;

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
//        $req['SaleOrders']['SaleOrder']['ErpOrder'] = $record_code;
        $req['SaleOrders']['SaleOrder']['ErpOrder'] = $this->is_canceled($record_code);
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0) {
            return $ret;
        }
        if ($ret['status'] == 2) {
            return $this->format_ret(-1, '', $ret['data']['SaleOrders']['SaleOrder']['Note']);
        }
        return $this->format_ret(1, $ret['data']['SaleOrders']['SaleOrder']['ErpOrder']);
    }

    private function get_area_name($id) {
        $sql = "select name from base_area where id=:id";
        return $this->db->get_value($sql, array(':id' => $id));
    }

    private function is_canceled($record_code){
        $sql = "select new_record_code from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
        return !empty($new_record_code)?$new_record_code:$record_code;
    }
}
