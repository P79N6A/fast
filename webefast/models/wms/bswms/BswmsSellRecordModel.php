<?php

require_model("wms/WmsSellRecordModel");

class BswmsSellRecordModel extends WmsSellRecordModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
        if (empty($json_data)) {
            return $this->format_ret(-1, '', '订单号不存在');
        }
        $order = json_decode($json_data, true);
        
        $check_order = $this->get_record_data($order);
        if($check_order===false){
             return $this->format_ret(-1, '','解密失败，稍后再处理...');
        }
        
        $this->get_wms_cfg($order['store_code']);
        return $this->format_ret(1, $order);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $order = $ret['data'];
        $wms_order = array();
        $wms_order['customerCode'] = $this->wms_cfg['customerCode'];
        $wms_order['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $wms_order['orderCode'] = $order['sell_record_code']; //?客户交易生成物流订单号，客户系统保证唯一（即外部单号）
        $wms_order['actionType'] = 'ADD'; //?操作类型： ADD-新增 CANCEL-取消
        $wms_order['extTradeId'] = $order['deal_code_list'];

        $wms_order['orderType'] = 'B2C'; //订单类型： NORMAL-普通订单/交易订单（B2C）WDO-出库单/非交易订单（B2B）
        $wms_order['extOrderType'] = $order['pay_code'] == 'cod' ? 'COD' : 'NORMAL';
        $wms_order['orderSource'] = $order['sale_channel_code'];
        $wms_order['orderTime'] = $order['record_time'];
        $wms_order['paymentTime'] = $order['pay_time'];
        $wms_order['shipmentTime'] = $order['plan_send_time'];
        //$wms_order['deliveryTime'] = '';//预计到货时间
        $wms_order['totalAmount'] = $order['order_money'];
        $wms_order['shippingAmount'] = $order['express_money'];
        $wms_order['discountAmount'] = ''; //折扣费
        $wms_order['actualAmount'] = $order['paid_money'];

        $wms_order['mergeOrderFlag'] = $order['is_combine']; //是否合并
        $wms_order['mergeOrderCodes'] = $order['combine_orders']; //合并单号
        $wms_order['invoiceFlag'] = $order['invoice_status'] == 0 ? false : true; //
        $wms_order['invoiceNote'] = $order['invoice_content'];
        $wms_order['invoiceTitle'] = $order['invoice_title']; //唯一码明细
        $wms_order['invoiceAmount'] = $order['invoice_money'];
        $wms_order['note'] = $order['order_remark'];
        $wms_order['logisticsProviderCode'] = $order['express_code'];
        $wms_order['shippingOrderNo'] = $order['express_no'];
        //收件人信息
        $recipient = array();
        $recipient['name'] = $order['receiver_name'];
        $recipient['postalCode'] = $order['receiver_zip_code'];
        $recipient['phoneNumber'] = $order['receiver_phone'];
        $recipient['mobileNumber'] = $order['receiver_mobile'];
        $recipient['province'] = $this->get_area_name($order['receiver_province']);
        $recipient['city'] = $this->get_area_name($order['receiver_city']);
        $recipient['district'] = $this->get_area_name($order['receiver_district']);
        $recipient['shippingAddress'] = $order['receiver_addr'];
        $recipient['email'] = $order['receiver_email'];
        $wms_order['recipient'] = $recipient;
        //商品详细信息
        foreach ($order['goods'] as $row) {
            //$key_arr = array('goods_name','spec1_code','spec2_code','spec1_name','spec2_name');
            //$sku_info =  load_model('goods/SkuCModel')->get_sku_info($row['sku'],$key_arr);
            $items = array();
            $items['itemSkuCode'] = $row['barcode'];
            $items['itemName'] = $row['goods_name'];
            $items['itemQuantity'] = $row['num'];
            $items['sellingPrice'] = $row['goods_price'] * $row['num'];
            $items['itemNote'] = "颜色:" . $row['spec1_name'] . ";尺码:" . $row['spec2_name'];
            $wms_order['items'][]['item'] = $items;
        }

        $method = 'SyncSalesOrderInfo';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            if($ret['data']['SalesOrderInfo']['flag'] == 'SUCCESS'){
                return $this->format_ret(1, '', '订单发货推送成功');
            } else {
                return $this->format_ret(-1, $ret['data']['SalesOrderInfo'], $ret['data']['SalesOrderInfo']['note']);
            }
        }
        return $this->format_ret(-1, $ret['data']['SalesOrderInfo'], '订单发货推送失败'); //   return  xiugai
    }

    function cancel($record_code, $efast_store_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, $ret);
        }
        $order = $ret['data'];
        $wms_order = array();
        $wms_order['customerCode'] = $this->wms_cfg['customerCode'];
        $wms_order['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $wms_order['orderCode'] = $order['sell_record_code']; //?客户交易生成物流订单号，客户系统保证唯一（即外部单号）
        $wms_order['actionType'] = 'CANCEL'; //?操作类型： ADD-新增 CANCEL-取消

        $method = 'SyncSalesOrderInfo';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            if($ret['data']['SalesOrderInfo']['flag'] == 'SUCCESS'){
                return $this->format_ret(1, '', '订单取消发货推送成功');
            } else {
                return $this->format_ret(-1, $ret['data']['SalesOrderInfo'], $ret['data']['SalesOrderInfo']['note']);
            }
        }
        return $this->format_ret(-1, $ret['data']['SalesOrderInfo'], '订单发货推送失败');
    }

    private function get_area_name($id) {
        $sql = "select name from base_area where id=:id";
        return $this->db->get_value($sql, array(':id' => $id));
    }
    
    //状态回传
    function wms_record_info($record_code, $efast_store_code) {
        $this->wms_cfg = array();
        $this->get_wms_cfg($efast_store_code);
        $method = 'GetSalesOrderStatus';
        $data = array();
        $data['customerCode'] = $this->wms_cfg['customerCode'];
        $data['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['orderCode'] = $record_code;
        $ret = $this->biz_req($method, $data);
        if ($ret['status'] < 0) {
            return $ret;
        }
 
        if($ret['data']['SalesOrderStatus']['flag'] == 'SUCCESS'){
            $ret = $this->conv_wms_record_info($ret['data']['SalesOrderStatus'], $efast_store_code);
        } else {
            return $this->format_ret(-1,$ret,$ret['data']['SalesOrderStatus']['note']);
        }
            
   
        return $ret;
    }

    function conv_wms_record_info($result, $efast_store_code) {
            $status_txt_map = array('flow_end' => '已收发货', 'upload' => '已上传', 'wait_upload' => '未上传');
        //根据顺丰返回的操作日志 解析当前订单所处的订单状态
        $order_status = $result['salesOrder']['orderStatus'];
        //是否已出库
        if(empty($order_status)){
            $ret['order_status'] = 'wait_upload';
        } elseif ($order_status =='DELIVERED') {
            $ret['order_status'] = 'flow_end';
        } else {
            $ret['order_status'] = 'upload';
        }
        $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
        if ($ret['order_status'] == 'flow_end') {
            $order = $result['salesOrder'];
            $ret['efast_record_code'] = $order['orderCode'];
            $ret['wms_store_code'] = $efast_store_code;
            $ret['express_code'] = $order['logisticsProviderCode'];
            $ret['express_no'] = $order['shippingOrderNo'];
            $ret['order_weight'] = !empty($order['weight']) ? $order['weight'] : 0;

            //发货时间
           // $ret['flow_end_time'] = isset($order['ActualShipDateTime'])?$order['ActualShipDateTime']:'';//问百世
            $goods_ret = $order['products']['product'];
            if (isset($goods_ret[0])) {
                $goods = $goods_ret;
            } else {
                $goods[0] = $goods_ret;
            }
            foreach ($goods as $sub_goods) {
                $ret['goods'][] = array('barcode' => $sub_goods['skuCode'], 'sl' => $sub_goods['normalQuantity']);
            }
        }
        return $this->format_ret(1, $ret);
    }
    
    

}
