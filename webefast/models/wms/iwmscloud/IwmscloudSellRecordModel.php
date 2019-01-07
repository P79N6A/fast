<?php

require_model("wms/WmsSellRecordModel");

class IwmscloudSellRecordModel extends WmsSellRecordModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $data = $this->get_upload_record_data($record_code, 'sell_record');

        $order = json_decode($data['json_data'], true);
        if (empty($order)) {
            return $this->format_ret(-1, '', '交易订单不存在');
        }
        $new_record_code = empty($data['new_record_code']) ? $record_code : $data['new_record_code'];

        $check_order = $this->get_record_data($order);
        if ($check_order === false) {
            return $this->format_ret(-1, '', '解密失败，稍后再处理...');
        }

        $this->get_wms_cfg($order['store_code']);

        if (strtolower($order['pay_type']) == 'cod') {
            $payable_money = $order['payable_money'] - $order['paid_money'];
        } else {
            $payable_money = $order['payable_money'];
        }
        $sql = "select pay_type_name from base_pay_type where pay_type_code = '{$order['pay_code']}'";
        $pay_name = (string) $this->db->getOne($sql);

        $shop_id = $this->get_wms_shop_id($this->wms_cfg['wms_config_id'], $order['shop_code']);
        if ($shop_id === FALSE) {
            return $this->format_ret(-1, '', '找不到订单对应的网店');
        }

        $wms_order['OrderSn'] = $new_record_code;
        $wms_order['DealCode'] = $order['deal_code_list'];
        $wms_order['SdId'] = $shop_id;
        $wms_order['SoType'] = $order['sale_channel_code'];
        $wms_order['UserName'] = $order['buyer_name'];
        $wms_order['OrderStatus'] = $order['order_status'];
        $wms_order['PayCode'] = $order['pay_code'];
        $wms_order['PayName'] = $pay_name;
        $wms_order['ReceiverName'] = $order['receiver_name'];
        $wms_order['WarehouseCode'] = $this->wms_cfg['wms_store_code'];



        $wms_order['ReceiverCountry'] = $order['receiver_country'];
        $wms_order['ReceiverProvince'] = $order['receiver_province'];
        $wms_order['ReceiverCity'] = $order['receiver_city'];
        $wms_order['ReceiverDistrict'] = $order['receiver_district'];
        $wms_order['ReceiverAddress'] = $order['receiver_address'];
        $wms_order['ReceiverZip'] = $order['receiver_zip_code'];
        $wms_order['ReceiverTel'] = $order['receiver_phone'];

        $wms_order['ReceiverMobile'] = $order['receiver_mobile'];
        $wms_order['ReceiverEmail'] = $order['receiver_email'];
        $wms_order['ShippingCode'] = $order['express_code'];

        $wms_order['ShippingTimeTzph'] = $order['is_notice_time']; //通知配货时间
        $wms_order['ShippingTimeJh'] = '';
        $wms_order['TotalAmount'] = $order['order_money'];
        $wms_order['MarketGoodsAmount'] = $order['goods_money'];
        $wms_order['ShopGoodsAmount'] = '';
        $wms_order['ShippingFee'] = $order['express_money'];
        $wms_order['CodFee'] = '';
        $wms_order['paid_money'] = $order['paid_money'];
        $wms_order['OrderAmount'] = $payable_money;

        $wms_order['AddTime'] = $order['record_time'];
        $wms_order['ConfirmTime'] = $order['check_time'];
        if (strtolower($order['pay_type']) == 'cod') {
            $wms_order['PayTime'] = '';
        } else {
            $wms_order['PayTime'] = $order['pay_time'];
        }
        $wms_order['BuyMsg'] = $order['buyer_remark'];
        $wms_order['SellerMsg'] = $order['seller_remark'];
        $wms_order['OrderMsg'] = $order['order_remark'];
        $wms_order['PayMsg'] = '';
        $wms_order['InvoiceType'] = $order['invoice_type'];
        $wms_order['InvoiceContent'] = $order['invoice_content'];
        $wms_order['InvoicePay'] = '';
        $wms_order['InvoiceTitle'] = $order['invoice_title'];
        $wms_order['InvoiceAmount'] = '';
        $wms_order['GoodsCount'] = '';
        $wms_order['SkuCount'] = $order['goods_num']; //商品数量
        $wms_order['Weigh'] = $order['goods_weigh'];
        $wms_order['TimeoutTime'] = $order['plan_send_time']; //计划发货时间
        $wms_order['IsCod'] = strtolower($order['pay_type']) == 'cod' ? 1 : 0;
        $wms_order['StorageMessage'] = (string) $order['store_remark'];
        $wms_order['CoopModel'] = '';
        $wms_order['BillInfo'] = '';
        $wms_order['Priority'] = ''; //是否急单
        $wms_order['orderCode'] = $this->get_order_code($order);
        $wms_order['orderType'] = $this->order_type[$wms_order['orderCode']];
        foreach ($order['goods'] as $row) {
            $order_goods = array();
            $order_goods['DealCode'] = $row['deal_code'];
            $order_goods['GoodsSn'] = $row['goods_code'];
            $order_goods['GoodsNumber'] = $row['num'];
            $order_goods['MarketPrice'] = $row['goods_price'];
            $order_goods['ShopPrice'] = $row['goods_price'];
            $order_goods['GoodsPrice'] = $row['goods_price'];
            $order_goods['DiscountFee'] = 1;
            $order_goods['SharePrice'] = '';
            $order_goods['ShareDiscountFee'] = '';
            $order_goods['GoodsAttr'] = '';
            $order_goods['OuterGoodsName'] = '';
            $order_goods['OuterGoodsSn'] = '';
            $order_goods['Sku'] = $row['barcode'];
            $order_goods['Barcode'] = $row['barcode'];
            $order_goods['ColorCode'] = $row['spec1_code'];
            $order_goods['ColorName'] = $row['spec1_name'];
            $order_goods['SizeCode'] = $row['spec2_code'];
            $order_goods['SizeName'] = $row['spec2_name'];
            $order_goods['IsGift'] = $row['is_gift'];
            $order_goods['IsQh'] = $row['num'] > $row['lock_num'] ? 1 : 0;
            $order_goods['Weigh'] = $row['goods_weigh'];
            $order_goods['IsGift'] = $row['is_gift'];

            $wms_order['OrderGoods'][] = $order_goods;
        }
        $map = array('dangdang' => '当当', 'vjia' => 'v+', 'jingdong' => '京东'); //
        if (!empty($order['__print']) && isset($map[$order['sale_channel_code']])) {
            $wms_order['CoopModel'] = $map[$order['sale_channel_code']];
            if (!empty($order['__print']['jd_type'])) {
                $wms_order['CoopModel'] = $order['__print']['jd_type'];
            }

            if ($order['sale_channel_code'] != 'jingdong') {
                $wms_order['BillInfo'] = $order['__print']['print_data'];
            }
        }
        return $this->format_ret(1, $wms_order);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'ewms.order.set';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            return $this->format_ret(1, $ret['data']['wmsid']);
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'ewms.billcancel.set';
        $req = array('bill_type' => 'order.normal', 'bill_code' => $record_code);
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0 && $ret['data']['error'] == 'BIZID_NOT_FOUND') {
            return $this->format_ret(1, $ret['data']);
        }
        return $ret;
    }

    function wms_record_info($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'ewms.orderstatus.get';
        $ret = $this->biz_req($method, array('BillId' => $record_code));
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->conv_wms_record_info($ret['data']);
        return $ret;
    }

    function conv_wms_record_info($result) {
        $status_map = array('DELIVERED' => 'flow_end', 'NotAvailableStatus' => 'upload', 'NotExist' => 'wait_upload', 'NotExistOrIsCancel' => 'wait_upload');
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
        $ret['express_code'] = $result['logisticsProviderCode'];
        $ret['express_no'] = $result['ShippingOrderNo'];
        $order_status = $result['OrderStatus'];

        $ret['order_status'] = is_null($status_map[$order_status]) ? $order_status : $status_map[$order_status];
        $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
        $ret['flow_end_time'] = $result['ChargeDate'];

        $goods = $result['BillStockGoods'];
        foreach ($goods as $sub_goods) {
            $ret['goods'][] = array('barcode' => $sub_goods['SkuCode'], 'sl' => $sub_goods['normalQuantity']);
        }
        return $this->format_ret(1, $ret);
    }

    function get_wms_quehuo($efast_store_code, $start_time, $end_time) {
        $this->get_wms_cfg($efast_store_code);
        if ($this->wms_cfg['api_product'] !== 'iwmscloud') {
            return $this->format_ret(-1, '', "仓库{$efast_store_code}未对接wms365");
        }
        $method = 'ewms.orderoos.get';
        $req = array('WarehouseCode' => $this->wms_store_code, 'BeginDate' => $start_time, 'EndDate' => $end_time);
        $ret = $this->biz_req($method, $req);

        $result = array();
        if ($ret['status'] > 0 && is_array($ret['data'])) {
            $state = @$ret['data']['state'];
            if ($state == 'NotRecord') {
                return $this->format_ret(1);
            }
            $down_time = date('Y-m-d H:i:s');
            foreach ($ret['data'] as $sub_ret) {
                if (empty($sub_ret['OrderCode'])) {
                    continue;
                }
                $su_result = array('sell_record_code' => $sub_ret['OrderCode'],
                    'efast_store_code' => $efast_store_code,
                    'down_time' => $down_time,
                    'wms_store_code' => $sub_ret['WarehouseCode']);
                if (empty($sub_ret['Items'])) {
                    continue;
                }
                $items = is_array($sub_ret['Items'][0]) ? $sub_ret['Items'] : array($sub_ret['Items']);
                foreach ($items as $sub_item) {
                    $su_result['goods'][] = array('sell_record_code' => $sub_ret['OrderCode'],
                        'efast_store_code' => $efast_store_code,
                        'wms_store_code' => $sub_ret['WarehouseCode'],
                        'barcode' => $sub_item['SkuCode'],
                        'num' => $sub_item['QtyPlan'],
                        'qh_num' => $sub_item['NormalQuantity']);
                }
                $result[] = $su_result;
            }
            //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';//die;
            return $this->format_ret(1, $result);
        }
        return $ret;
    }

    //查询单据状态
    function get_record_flow($record_coce, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);

        $method = 'ewms.orderbizflow.get';
        $req = array('OrderSn' => $record_coce, 'status' => '0:9');
        $status_ret = $this->biz_req($method, $req);

        $status_arr = array();
        $opdate_arr = array();
        if ($status_ret['status'] == 1) {
            foreach ($status_ret['data'] as $status) {
                $status_arr[] = $status['Status'];
                $opdate_arr[] = $status['OpDate'];
            }
            array_multisort($status_arr, SORT_DESC, $opdate_arr, $status_ret['data']);
        }
        return $status_ret;
    }

    private function get_order_code($order) {
        if ($order['is_change_record'] == 1) {
            return "HH";
        } elseif ($order['sale_mode'] == 'presale') {
            return "YS";
        } else {
            return "JY";
        }
    }

    private $order_type = array(
        "JY" => '正常单',
        "HH" => '换货单',
        "YS" => '预售单',
        "SG" => '手工单',
    );

}
