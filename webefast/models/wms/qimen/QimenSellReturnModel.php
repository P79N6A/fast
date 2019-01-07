<?php

require_model("wms/WmsSellReturnModel");

class QimenSellReturnModel extends WmsSellReturnModel {

    function __construct() {
        parent::__construct();
    }

    /**
     * 转换上传数据
     * @param string $record_code 退单号
     * @return array 数据集
     */
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

        $wms_order['returnOrderCode'] = $new_record_code;
        $wms_order['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $wms_order['orderType'] = 'THRK';

        //如果退单关联的订单为取消重新上传，则取新单号
        $wms_order['preDeliveryOrderCode'] = $this->is_canceled($order['sell_record_code'], 'sell_record');

        if ($this->wms_cfg['product_type'] === 'cainiao') {
            $wms_order['preDeliveryOrderId'] = $this->get_wms_id($order['sell_record_code'], 'sell_record');
        }

        $express_company = $this->get_express_company($order['return_express_code']);


        $wms_order['logisticsCode'] = $express_company['company_code'];
        $wms_order['logisticsName'] = $express_company['company_name'];

        $wms_order['expressCode'] = $order['return_express_no'];
        $sql = "select return_reason_name from base_return_reason where return_reason_code = :return_reason_code";
        $return_reason_name = ctx()->db->getOne($sql, array(':return_reason_code' => $order['return_reason_code']));
        $wms_order['returnReason'] = $return_reason_name;
        $wms_order['buyerNick'] = $order['buyer_name'];
        $wms_order['remark'] = $order['return_remark'];

        $sender_info = array();
        $sender_info['name'] = $order['return_name'];
        $sender_info['zipCode'] = $order['return_zip_code'];
        $sender_info['mobile'] = $order['return_mobile'];
        $sender_info['tel'] = $order['return_phone'];

        $sender_info['province'] = $this->get_area_name($order['return_province']);
        $sender_info['city'] = $this->get_area_name($order['return_city']);
        $sender_info['area'] = $this->get_area_name($order['return_district']);
        $sender_info['detailAddress'] = $this->html_decode($order['return_address']);
        $wms_order['senderInfo'] = $sender_info;


        $orderLineNo = 1;
        $goods_data = array();
        foreach ($order['goods'] as $row) {
            $order_goods = array();
            $order_goods['orderLineNo'] = $orderLineNo;
            $order_goods['sourceOrderCode'] = $row['deal_code'];
            $order_goods['ownerCode'] = $this->wms_cfg['owner_code'];
            $order_goods['itemCode'] = $row['barcode'];
            $order_goods['inventoryType'] = 'ZP';
            $order_goods['planQty'] = $row['num'];

            $item_id = $this->get_item_id('qimen', $order['store_code'], $row['barcode']);
            if (!empty($item_id)) {
                $order_goods['itemId'] = $item_id;
            }

            $goods_data[] = array('orderLine' => $order_goods);
            $orderLineNo++;
        }
        $extendProps = array('shopNick' => $this->get_shop_name($order['shop_code']));
        $data = array('returnOrder' => $wms_order, 'orderLines' => $goods_data, 'extendProps' => $extendProps);
        return $this->format_ret(1, $data);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'taobao.qimen.returnorder.create';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            return $this->format_ret(1, $ret['data']['returnOrderId']);
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'taobao.qimen.order.cancel';
        $wms_record_code = $this->get_wms_id($record_code);
        $req = array('warehouseCode' => $this->wms_cfg['wms_store_code'], 'ownerCode' => $this->wms_cfg['owner_code'], 'orderCode' => $record_code, 'orderId' => $wms_record_code, 'orderType' => 'XTRK');
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

    function get_express_company($express_code) {

        return $this->db->get_row("select c.company_code,c.company_name FROM base_express_company c INNER JOIN base_express s ON c.company_code=s.company_code where express_code=:express_code ", array(':express_code' => $express_code));
    }

    private function is_canceled($record_code, $record_type = 'sell_return') {
        $sql = "select new_record_code from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
        return !empty($new_record_code) ? $new_record_code : $record_code;
    }

    function get_wms_id($record_code, $record_type = 'sell_return') {
        return $this->db->get_value("select wms_record_code from wms_oms_trade where (record_code='{$record_code}'  OR new_record_code='{$record_code}'  )  and record_type = :return_type", array(':return_type' => $record_type));
    }

    function get_area_name($id) {
        if (!empty($id)) {
            return $this->db->get_value("select name from base_area where id=:id", array(':id' => $id));
        }
        return '';
    }

    function get_shop_name($shop_code) {
        $sql = "SELECT shop_name FROM base_shop WHERE shop_code=:shop_code";
        $sql_value = array(":shop_code" => $shop_code);
        return $this->db->get_value($sql, $sql_value);
    }

    function wms_record_info($record_code, $efast_store_code) {

        return $this->format_ret(-1);
    }

}
