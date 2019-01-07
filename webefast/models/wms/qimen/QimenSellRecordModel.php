<?php

require_model("wms/WmsSellRecordModel");

class QimenSellRecordModel extends WmsSellRecordModel {

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
            $payable_money = 0;
        }


        $sql = "select shop_id,shop_name from base_shop where shop_code = '{$order['shop_code']}'";
        $shop_data = ctx()->db->get_row($sql);
        if (empty($shop_data)) {
            return $this->format_ret(-1, '', '找不到订单对应的网店');
        }
        //新单号处理
        $wms_order['deliveryOrderCode'] = $new_record_code;
        $wms_order['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $wms_order['preDeliveryOrderCode'] = 'JYCK';
        if (!empty($order['change_record_from'])) {
            $wms_order['preDeliveryOrderCode'] = $order['change_record_from'];
            $wms_order['preDeliveryOrderCode'] = 'HHCK';
        }
        $orderflag_arr = $this->orderflag($order);
        if (!empty($orderflag_arr)) {
            $wms_order['orderFlag'] = implode('^', $orderflag_arr);
        }

        $sourcePlatform_arr = $this->get_sale_channel($order['sale_channel_code'], $order['shop_code']);

        $wms_order['sourcePlatformCode'] = $sourcePlatform_arr[0];
        $wms_order['sourcePlatformName'] = $sourcePlatform_arr[1];


        $wms_order['orderType'] = 'JYCK';
        $wms_order['createTime'] = empty($order['create_time']) || $order['create_time'] === '0000-00-00 00:00:00' ? $order['record_time'] : $order['create_time'];
        $wms_order['placeOrderTime'] = $order['record_time'];
        $wms_order['payTime'] = $order['pay_type'] == 'cod' ? '' : $order['pay_time']; //货到付款订单，支付时间传空值
        $wms_order['payNo'] = $order['alipay_no'];
        $wms_order['operateTime'] = $order['check_time'];
        $wms_order['shopNick'] = $shop_data['shop_name'];
        $wms_order['buyerNick'] = $order['buyer_name'];
        $wms_order['totalAmount'] = $order['payable_money'];
        $wms_order['itemAmount'] = $order['goods_money'];
        $wms_order['freight'] = $order['express_money'];
        $wms_order['arAmount'] = $payable_money;
        $wms_order['gotAmount'] = $order['paid_money'];

        $express_company = $this->get_express_company($order['express_code']);
        if (!empty($express_company)) {
            $wms_order['logisticsCode'] = $express_company['company_code'];
            $wms_order['logisticsName'] = $express_company['company_name'];
        }
        if (!empty($order['express_no'])) {
            $wms_order['expressCode'] = $order['express_no'];
        }

        $store_info = $this->get_store_info($order['store_code']);
        $sender_info = array();
        $sender_info['name'] = $store_info['contact_person'];
        $sender_info['zipCode'] = $store_info['zipcode'];
        $sender_info['mobile'] = $store_info['contact_phone'];
        $sender_info['tel'] = $store_info['contact_tel'];
        $sender_info['province'] = $store_info['province'];
        $sender_info['city'] = $store_info['city'];
        $sender_info['area'] = $store_info['district'];
        $sender_info['detailAddress'] = $store_info['address'];
        $wms_order['senderInfo'] = $sender_info;

        $receiver_info = array();
        $receiver_info['name'] = $this->html_decode($order['receiver_name']);
        $receiver_info['zipCode'] = $order['receiver_zip_code'];
        $receiver_info['mobile'] = empty($order['receiver_mobile']) ? $order['receiver_phone'] : $order['receiver_mobile'];
        $receiver_info['tel'] = $order['receiver_phone'];
        $receiver_info['zipCode'] = $order['receiver_zip_code'];
            $receiver_info['province'] = $this->get_area_name($order['receiver_province']);
            $receiver_info['city'] = $this->get_area_name($order['receiver_city']);
            $receiver_info['area'] = $this->get_area_name($order['receiver_district']);
            $receiver_info['town'] = $this->get_area_name($order['receiver_street']);
        //订单若为速卖通海外订单，省、市、区参数取api_order表的省市区(拓尚特殊处理)
        $kh_id = CTX()->saas->get_saas_key();
        if ($kh_id == '2368' && $order['sale_channel_code'] === 'aliexpress') {
            $deal_code_arr = explode(',', $order['deal_code_list']);
            $deal_code = empty($deal_code_arr) ? $order['deal_code'] : $deal_code_arr[0];
            $api_info = $this->get_api_order_data($deal_code, 'aliexpress');
            if(!empty($api_info)){
                $receiver_info['countryCode'] = $api_info['receiver_country'];
                $receiver_info['province'] = $api_info['receiver_province'];
                $receiver_info['city'] = $api_info['receiver_city'];
                $receiver_info['area'] =  !empty($api_info['receiver_district'])?$api_info['receiver_district']: '' ;
                $receiver_info['town'] = !empty($api_info['receiver_street'])?$api_info['receiver_street']: '' ;
            }
        } 

        $receiver_info['detailAddress'] = empty($order['receiver_addr']) ? $receiver_info['town'] : $this->html_decode($order['receiver_addr']);
        $receiver_info['email'] = $order['receiver_email'];
        $wms_order['receiverInfo'] = $receiver_info;

        if ($order['is_rush'] == 1) {
            $wms_order['isUrgency'] = 'Y';
        }
        $wms_order['invoiceFlag'] = 'N';
        if (!empty($order['invoice_title'])) {
            $wms_order['invoiceFlag'] = 'Y';
            $wms_order['invoices']['invoice']['type'] = 'INVOICE';
            $wms_order['invoices']['invoice']['header'] = $order['invoice_title'];
            $wms_order['invoices']['invoice']['content'] = $order['invoice_content'];
            $wms_order['invoices']['invoice']['amount'] = $order['invoice_money'];
        }
        $wms_order['buyerMessage'] = html_entity_decode($order['buyer_remark']);
        $wms_order['sellerMessage'] = html_entity_decode($order['seller_remark']);
        $wms_order['remark'] = html_entity_decode($order['order_remark']);

        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        $orderLineNo = 1;
        $goods_data = array();
        $invoices_items = array();
        foreach ($order['goods'] as $row) {
            $goods_info = load_model("goods/GoodsCModel")->get_goods_info($row['goods_code'], array('goods_name'));
            $order_goods = array();
            $order_goods['sourceOrderCode'] = $row['deal_code'];
            $order_goods['subSourceOrderCode'] = $row['sub_deal_code'];
            $order_goods['ownerCode'] = $this->wms_cfg['owner_code'];
            $order_goods['itemCode'] = $row['barcode'];
            $order_goods['itemName'] = $goods_info['goods_name'];
            $order_goods['inventoryType'] = 'ZP';
            $item_id = $this->get_item_id('qimen', $order['store_code'], $row['barcode']);
            if (!empty($item_id)) {
                $order_goods['itemId'] = $item_id;
            }

            if ($is_lof == 1) {
                $batch_count = count($row['batchs']);
                $actual_price = $row['avg_money'] / $batch_count;
                foreach ($row['batchs'] as $val) {
                    $order_line = $order_goods;
                    $order_line['orderLineNo'] = $orderLineNo;
                    $order_line['batchCode'] = $val['lof_no'];
                    $order_line['productDate'] = $val['production_date'];
                    $order_line['planQty'] = $val['num'];
                    $order_line['actualPrice'] = $actual_price;
                    $goods_data[] = array('orderLine' => $order_line);
                    $orderLineNo++;
                }
            } else {
                $order_goods['orderLineNo'] = $orderLineNo;
                $order_goods['planQty'] = $row['num'];
                $order_goods['actualPrice'] = $row['avg_money'];
                $goods_data[] = array('orderLine' => $order_goods);
                $orderLineNo++;
            }

            if ($wms_order['invoiceFlag'] == 'Y') {
                $item = array();
                $item['itemName'] = $goods_info['goods_name'];
                $item['price'] = $row['goods_price'];
                $item['quantity'] = $row['num'];
                $item['amount'] = $row['avg_money'];
                $invoices_items[] = array('item' => $item);
            }
        }
        if ($wms_order['invoiceFlag'] == 'Y') {
            $wms_order['invoices']['invoice']['detail']['items'] = $invoices_items;
        }

        $data = array('deliveryOrder' => $wms_order, 'orderLines' => $goods_data);

        $express_sql = "SELECT company_code FROM base_express WHERE express_code = :express_code";
        $express_sql_values = array(":express_code" => $order['express_code']);
        $company_code = $this->db->get_value($express_sql, $express_sql_values);

        $print_data = array();

        if ($company_code == 'SFC') {
            $pdata = json_decode($order['__print'], true);
            $print_data['extendProps'] = array('print_data' => $pdata['url']);
            $data = array_merge($data, $print_data);
        }


        return $this->format_ret(1, $data);
    }

    function orderflag($record_data) {
        $arr = array();
        if (strtolower($record_data['pay_type']) == 'cod') {
            $arr[] = 'COD';
        }
        if (!empty($record_data['change_record_from'])) {
            $arr[] = 'EXCHANGE';
        }
        if ($record_data['is_split_new'] == 1) {
            $arr[] = 'SPLIT';
        }
        return $arr;
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'taobao.qimen.deliveryorder.create';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            return $this->format_ret(1, $ret['data']['deliveryOrderId']);
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {

        $this->get_wms_cfg($efast_store_code);
        $method = 'taobao.qimen.order.cancel';

        $record_data = $this->get_record_code($record_code);
        $orderCode = empty($record_data['new_record_code']) ? $record_code : $record_data['new_record_code'];
        $wms_record_code = empty($record_data['wms_record_code']) ? '' : $record_data['wms_record_code'];
        $req = array('warehouseCode' => $this->wms_cfg['wms_store_code'], 'ownerCode' => $this->wms_cfg['owner_code'], 'orderCode' => $orderCode, 'orderId' => $wms_record_code, 'orderType' => 'JYCK');
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', $ret['message']);
        }
        if ($ret['data']['flag'] == 'success') {
            $ret = $this->format_ret(1, $ret['data']);
        } else {
            $ret = $this->format_ret(-1, '', $ret['data']['message']);
        }
        return $ret;
    }

    function get_wms_id($record_code) {

        $record_data = $this->db->get_row("select record_code,wms_record_code from wms_oms_trade where record_code='{$record_code}'  and record_type = 'sell_record'");
        if (empty($record_data)) {
            $record_data = $this->db->get_row("select record_code,wms_record_code from wms_oms_trade where new_record_code='{$record_code}'  and record_type = 'sell_record'");
        }
        return isset($record_data['wms_record_code']) ? $record_data['wms_record_code'] : '';
    }

    function get_record_code($record_code) {
        $record_data = $this->db->get_row("select new_record_code,wms_record_code  from wms_oms_trade where record_code='{$record_code}'  and record_type = 'sell_record'");
        if (empty($record_data)) {
            $record_data = $this->db->get_row("select new_record_code,wms_record_code from wms_oms_trade where new_record_code='{$record_code}'  and record_type = 'sell_record'");
        }
        return $record_data;
    }

    function wms_record_info($record_code, $efast_store_code) {

        return $this->format_ret(-1);
    }

    function get_sale_channel($sale_channel_code, $shop_code) {
        static $sale_channel_shop = null;
        if (!isset($sale_channel_shop[$shop_code])) {
            $cainiao_sale_channel = require_conf('wms/qm_source_platform');
            if ($sale_channel_code == 'taobao') {
                $tb_shop_type = $this->db->get_value("select tb_shop_type from base_shop_api where shop_code =:shop_code", array(':shop_code' => $shop_code));
                if ($tb_shop_type == 'B') {//天猫店铺
                    $sale_channel_code = 'tmall';
                }
            }
            if ($this->wms_cfg['product_type'] === 'cainiao' && $sale_channel_code === 'aliexpress') {
                $sale_channel_shop[$shop_code] = ['aliexpress', '速卖通'];
            } else {
                $sale_channel_shop[$shop_code] = isset($cainiao_sale_channel[$sale_channel_code]) ? $cainiao_sale_channel[$sale_channel_code] : array('OTHER', '其他');
            }
        }
        return $sale_channel_shop[$shop_code];
    }

    function get_express_company($express_code) {

        $express_data = $this->db->get_row("select c.company_code,c.company_name FROM base_express_company c INNER JOIN base_express s ON c.company_code=s.company_code where express_code=:express_code ", array(':express_code' => $express_code));
        $qm_express_data = array(
            'JD' => '京东配送',
            'SF' => '顺丰',
            'EMS' => '标准快递',
            'EYB' => '经济快件',
            'ZJS' => '宅急送',
            'ZTO' => '中通',
            'YTO' => '圆通',
            'HTKY' => '百世汇通',
            'UC' => '优速',
            'STO' => '申通',
            'TTKDEX' => '天天快递',
            'QFKD' => '全峰',
            'FAST' => '快捷',
            'POSTB' => '邮政小包',
            'GTO' => '国通',
            'YUNDA' => '韵达',
            'DBL' => '德邦物流',
            'DBKD' => '德邦快递',
            'OTHER' => '其他',
            'YTOXG' => '圆通国际快递', //振颜
            'YUNDAXG' => '韵达国际快递', //振颜
            'TTKDEXXG' => '天天国际快递', //振颜
            'CAINIAO_STANDARD' => '无忧快递', //振颜
        );

        if (isset($qm_express_data[$express_data['company_code']])) {
            $express_data['company_name'] = $qm_express_data[$express_data['company_code']];
        } else if ($express_data['company_code'] == 'SFC') {
            $sfc_express_data = array(
                'WWRAM' => '三态-中国邮政外围小包(挂号）',
                'WWRM' => '三态-中国邮政外围小包(平邮）',
            );
            $express_data['company_code'] = $express_code;
            $express_data['compacompany_nameny_code'] = $sfc_express_data[$express_code];
        } else {
            $express_data['company_code'] = 'OTHERS';
            $express_data['company_name'] = '其他';
        }

        return $express_data;
    }

    function get_store_info($store_code) {


        $row = $this->db->get_row("select * FROM base_store where  store_code=:store_code  ", array(':store_code' => $store_code));
        if (!empty($row)) {
            $row['country'] = $this->get_area_name($row['country']);
            $row['province'] = $this->get_area_name($row['province']);
            $row['city'] = $this->get_area_name($row['city']);
            $row['district'] = $this->get_area_name($row['district']);
        }
        return $row;
    }

    function get_area_name($id) {
        if (!empty($id)) {
            return $this->db->get_value("select name from base_area where id=:id", array(':id' => $id));
        }
        return '';
    }

    //查询单据状态
    function get_record_flow($record_coce, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);

        $method = 'taobao.qimen.orderprocess.query';
        $req = array('orderType' => 'JYCK', 'orderCode' => $record_coce);
        $orderId = $this->get_wms_id($record_coce);


        if (!empty($orderId)) {
            $req['orderId'] = $orderId;
        }

        $req['warehouseCode'] = $this->wms_cfg['wms_store_code'];
//            $sql = "select json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
//        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
//                //orderId

        $ret = $this->biz_req($method, $req);

        $status_arr = array(
            'NEW' => '新增',
            'ACCEPT' => '仓库接单',
            'PRINT' => '打印',
            'PICK' => '捡货',
            'CHECK' => '复核',
            'PACKAGE' => '打包',
            'WEIGH' => '称重',
            'READY' => '待提货',
            'DELIVERED' => '已发货',
            'EXCEPTION' => '异常',
            'CLOSED' => '关闭',
            'CANCELED' => '取消',
            'REJECT' => '仓库拒单',
            'REFUSE' => '客户拒签',
            'CANCELEDFAIL' => '取消失败',
            'SIGN' => '签收',
            'TMSCANCELED' => '快递拦截',
            'PARTFULFILLED' => '部分收货完成',
            'FULFILLED' => '收货完成',
            'PARTDELIVERED' => '部分发货完成',
            'OTHER' => '其他',
        );

        $status_data = array();
        if ($ret['status'] > 0) {
            $process_data = isset($ret['data']['processes']['process']['processStatus']) ?
                    array($ret['data']['processes']['process']) : $ret['data']['processes']['process'];
            foreach ($process_data as $status) {
                $status_name = $status_arr[$status['processStatus']];
                //$status_arr[] = $status_name;
                $key = strtotime($status['operateTime']);
                $status_data[$key] = array('OpDate' => $status['operateTime'], 'Description' => $status_name);
            }
            krsort($status_data);
        }
        return $this->format_ret(1, $status_data);
    }

    /**
     * 获取订单中间表原始数据
     * @param string $deal_code 交易号
     * @param string $source 平台
     * @return array
     */
    private function get_api_order_data($deal_code, $source) {
        $sql = 'SELECT receiver_country,receiver_province,receiver_city,receiver_district,receiver_street FROM api_order WHERE tid=:tid AND source=:source';
        $data = $this->db->get_row($sql, [':tid' => $deal_code, ':source' => $source]);

        return $data;
    }

}
