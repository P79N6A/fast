<?php

require_model("wms/WmsWbmNoticeModel");

class BswmsPurReturnNoticeModel extends WmsWbmNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'pur_return_notice'));
        if (empty($json_data)) {
            return $this->format_ret(-1, '', '通知单号不存在');
        }
        $order = json_decode($json_data, true);
        $this->get_wms_cfg($order['store_code']);
        return $this->format_ret(1, $order);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, $ret);
        }
        $order = $ret['data'];
        $wms_order = array();
        $wms_order['customerCode'] = $this->wms_cfg['customerCode'];
        $wms_order['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $wms_order['orderCode'] = $order['record_code']; //?客户交易生成物流订单号，客户系统保证唯一（即外部单号）
        $wms_order['actionType'] = 'ADD'; //?操作类型： ADD-新增 CANCEL-取消
        $wms_order['orderType'] = 'B2B'; //订单类型： NORMAL-普通订单/交易订单（B2C）WDO-出库单/非交易订单（B2B）
        $wms_order['orderTime'] = $order['order_time'];
        $wms_order['note'] = $order['remark'];
        //收件人信息
        $recipient = array();
        $recipient['name'] = $order['supplier']['contact_person'];
        $recipient['postalCode'] = $order['supplier']['zipcode'];
        $recipient['phoneNumber'] = $order['supplier']['tel'];
        $recipient['mobileNumber'] = $order['supplier']['mobile'];
        $recipient['shippingAddress'] = $order['supplier']['address'];
        $recipient['email'] = $order['email'];
        $wms_order['recipient'] = $recipient;
        //商品详细信息
        foreach ($order['goods'] as $row) {
            $sku = $this->db->get_value("select sku from goods_sku where barcode = '{$row['barcode']}'");
            $key_arr = array('goods_name', 'spec1_name', 'spec2_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sku, $key_arr);
            $items = array();
            $items['itemSkuCode'] = $row['barcode'];
            $items['itemName'] = $sku_info['goods_name'];
            $items['itemQuantity'] = $row['num'];
            $items['sellingPrice'] = $row['price'] * $row['num'];
            $items['itemNote'] = "颜色:" . $row['spec1_name'] . ";尺码:" . $row['spec2_name'];
            $wms_order['items'][]['item'] = $items;
        }

        $method = 'SyncSalesOrderInfo';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            if($ret['data']['SalesOrderInfo']['flag'] == 'SUCCESS'){
                return $this->format_ret(1, '', '采购退货通知单推送成功');
            }
        }
        return $this->format_ret(-1, $ret['data']['SalesOrderInfo'], '采购退货通知单推送失败'); //   return  xiugai
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
        $wms_order['orderCode'] = $order['record_code']; //?客户交易生成物流订单号，客户系统保证唯一（即外部单号）
        $wms_order['actionType'] = 'CANCEL'; //?操作类型： ADD-新增 CANCEL-取消

        $method = 'SyncSalesOrderInfo';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            if($ret['data']['SalesOrderInfo']['flag'] == 'SUCCESS'){
                return $this->format_ret(1, '', '退货通知单取消成功');
            }
        }
        return $this->format_ret(-1, $ret['data']['SalesOrderInfo'], '退货通知单取消失败');
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
