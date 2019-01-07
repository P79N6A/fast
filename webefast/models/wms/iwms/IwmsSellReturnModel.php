<?php

require_model("wms/WmsSellReturnModel");

class IwmsSellReturnModel extends WmsSellReturnModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $data = $this->get_upload_record_data($record_code, 'sell_return');

        $order = json_decode($data['json_data'], true);
        if (empty($order)) {
            return $this->format_ret(-1, '', '交易退单不存在');
        }
        $new_record_code = empty($data['new_record_code']) ? $record_code : $data['new_record_code'];
        
        $check_order = $this->get_record_data($order);
        if ($check_order === false) {
            return $this->format_ret(-1, '', '解密失败，稍后再处理...');
        }

        $this->get_wms_cfg($order['store_code']);

        $wms_order['ShippingName'] = $order['return_express_code'];
        $wms_order['SyncBillId'] = $new_record_code;
        $wms_order['Memo'] = $order['return_remark'];

        $wms_order['OrderSn'] = $order['sell_record_code'];
        $sql = "select return_reason_name from base_return_reason where return_reason_code = :return_reason_code";
        $return_reason_name = ctx()->db->getOne($sql, array(':return_reason_code' => $order['return_reason_code']));
        $wms_order['ReturnReason'] = $return_reason_name;

        $shop_id = $this->get_wms_shop_id($this->wms_cfg['wms_config_id'], $order['shop_code']);
        if ($shop_id === FALSE) {
            return $this->format_ret(-1, '', '找不到订单对应的网店');
        }
        $wms_order['CKID'] = $order['store_code'];
        $wms_order['SdId'] = $shop_id; //'001';//$order['shop_code']
        $wms_order['BillDate'] = $order['create_time'];
        $wms_order['WarehouseCode'] = $this->wms_cfg['wms_store_code'];
        $wms_order['ReturnReasonCode'] = $order['return_reason_code'];
        $wms_order['ReturnReasonDesc'] = $return_reason_name;
        $wms_order['DealCode'] = (string) $order['deal_code'];
        $wms_order['UserName'] = (string) $order['buyer_name'];
        $wms_order['ReceiverTel'] = (string) $order['return_phone'];
        $wms_order['ReceiverMobile'] = (string) $order['return_mobile'];
        $wms_order['ReceiverAddress'] = (string) $order['return_address'];
        $wms_order['ShippingCode'] = $order['return_express_code'];
        $wms_order['ShippingSn'] = (string) $order['return_express_no'];
        $wms_order['ReceiverName'] = trim((string) $order['return_name']);

        $order_goods = array();
        foreach ($order['goods'] as $row) {
            $t_row = array();
            $t_row['Sku'] = ($this->wms_cfg['goods_upload_type'] == 1) ? $row['sku'] : $row['barcode'];
            $t_row['MarketPrice'] = $row['goods_price'];
            $t_row['GoodsPrice'] = $row['goods_price'];
            $t_row['ReturnNum'] = $row['num'];
            $order_goods[] = $t_row;
        }
        $wms_order['OrderReturnGoods'] = $order_goods;
        return $this->format_ret(1, $wms_order);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'ewms.orderreturn.set';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            return $this->format_ret(1, $ret['data']['wmsid']);
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'ewms.billcancel.set';
        $req = array('bill_type' => 'orderreturn.normal', 'bill_code' => $record_code);
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0 && $ret['data']['error'] == 'BIZID_NOT_FOUND') {
            return $this->format_ret(1, $ret['data']);
        }
        return $ret;
    }

    function wms_record_info($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'ewms.orderreturnstatus.get';
        $ret = $this->biz_req($method, array('BillId' => $record_code));
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->conv_wms_record_info($ret['data']);
        return $ret;
    }

    function conv_wms_record_info($result) {
        $status_map = array('FULFILLED' => 'flow_end', 'NotAvailableStatus' => 'upload', 'NotExist' => 'wait_upload', 'NotExistOrIsCancel' => 'wait_upload');
        $status_txt_map = array('flow_end' => '已收发货', 'upload' => '已上传', 'wait_upload' => '未上传');

        $ret = array();
        if (isset($result['bizid'])) {
            $ret['efast_record_code'] = $result['bizid'];
            $order_status = $result['state'];
            $ret['order_status'] = isset($status_map[$order_status]) ? $status_map[$order_status] : $ret['order_status'];
            $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
            $ret['msg'] = $result['msg'];
            return $this->format_ret(2, $ret);
        }

        $ret['efast_record_code'] = $result['OrderCode'];
        $ret['wms_record_code'] = $result['WMSBillCode'];
        $ret['wms_store_code'] = $result['WarehouseCode'];
        $order_status = $result['OrderStatus'];

        $ret['order_status'] = is_null($status_map[$order_status]) ? $order_status : $status_map[$order_status];
        $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
        $ret['flow_end_time'] = $result['ChargeDate'];
        $ret['express_code'] = isset($result['logisticsProviderCode']) ? $result['logisticsProviderCode'] : '';
        $ret['express_no'] = isset($result['shippingOrderNo']) ? $result['shippingOrderNo'] : '';
        $goods = $result['BillStockGoods'];
        foreach ($goods as $sub_goods) {
            if ($this->wms_cfg['goods_upload_type'] == 1) {
                $key_arr = array('barcode');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_goods['SkuCode'], $key_arr);
                $barcode = $sku_info['barcode'];
            } else {
                $barcode = $sub_goods['SkuCode'];
            }
            $ret['goods'][] = array('barcode' => $barcode, 'sl' => $sub_goods['normalQuantity']);
        }
        return $this->format_ret(1, $ret);
    }

}
