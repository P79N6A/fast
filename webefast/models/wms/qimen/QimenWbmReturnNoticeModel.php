<?php

require_model("wms/WmsWbmReturnNoticeModel");

class QimenWbmReturnNoticeModel extends WmsWbmReturnNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $data = $this->get_upload_record_data($record_code, 'wbm_return_notice');

        $order = json_decode($data['json_data'], true);
        if (empty($order)) {
            return $this->format_ret(-1, '', '批发退货通知单不存在');
        }

        $new_record_code = empty($data['new_record_code']) ? $record_code : $data['new_record_code'];

        $this->get_wms_cfg($order['store_code'], 'wbm_return_notice');

        $wms_order['entryOrder'] = array(
            'entryOrderCode' => $new_record_code,
            'warehouseCode' => $this->wms_cfg['wms_store_code'],
            'ownerCode' => $this->wms_cfg['owner_code'],
            'orderType' => 'B2BRK', //B2B入库
            'orderCreateTime' => $order['order_time'],
            'remark' => (string) $order['remark'], //备注
            'senderInfo' => array('name' => $order['distributor']['custom_name']),
        );

        $kh_id = CTX()->saas->get_saas_key();
        $kh_id_arr = array('2322', '2259');
        //类型兼容 2322 测试 2259 振颜
        if (in_array($kh_id, $kh_id_arr)) {
            $wms_order['entryOrder']['purchaseOrderCode'] = $order['return_notice_code'];
        }

        if (!empty($order['distributor']['province'])) {
            $province = $this->get_area_name($order['distributor']['province']);
            if (!empty($province)) {
                $wms_order['entryOrder']['senderInfo']['province'] = $province;
            }
        }
        if (!empty($order['distributor']['city'])) {
            $city = $this->get_area_name($order['distributor']['city']);
            if (!empty($city)) {
                $wms_order['entryOrder']['senderInfo']['city'] = $city;
            }
        }

        $order_goods = array();
        $orderLineNo = 1;
        foreach ($order['goods'] as $row) {
            $t_row = array();
            $t_row['orderLineNo'] = $orderLineNo;
            $t_row['ownerCode'] = $this->wms_cfg['owner_code'];
            $t_row['itemCode'] = $row['barcode'];
            $t_row['planQty'] = $row['num'];
            $t_row['inventoryType'] = 'ZP';

            $item_id = $this->get_item_id('qimen', $order['store_code'], $row['barcode']);
            if (!empty($item_id)) {
                $t_row['itemId'] = $item_id;
            }
            $order_goods[] = array('orderLine' => $t_row);
            $orderLineNo++;
        }
        $wms_order['orderLines'] = $order_goods;

        return $this->format_ret(1, $wms_order);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'taobao.qimen.entryorder.create';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            return $this->format_ret(1, $ret['data']['entryOrderId']);
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'taobao.qimen.order.cancel';
        $wms_record_code = $this->get_wms_id($record_code);
        $req = array('warehouseCode' => $this->wms_cfg['wms_store_code'], 'ownerCode' => $this->wms_cfg['owner_code'], 'orderCode' => $record_code, 'orderId' => $wms_record_code, 'orderType' => 'B2BRK');
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

    function wms_record_info($record_code, $efast_store_code) {

        return $this->format_ret(-1);
    }

    function conv_wms_record_info($result) {
        $data = $result['RECEIPT'];
        if (!isset($data[0])) {
            $data = array($data);
        }
        $data = $data[0];
        $ret['efast_record_code'] = $data['ReceiptId'];
        $ret['wms_record_code'] = '';
        $ret['wms_store_code'] = $data['WareHouse'];

        $ret['order_status'] = 'flow_end';
        $ret['order_status_txt'] = '已收发货';
        $flow_end_time = explode("T", $data['ReceiptDate']);
        $time = explode(".", $flow_end_time[1]);

        $ret['flow_end_time'] = $flow_end_time[0] . " " . $time[0];
        $goods = $data['Items']['Item'];
        if (!isset($goods[0])) {
            $goods = array($goods);
        }
        foreach ($goods as $sub_goods) {
            $ret['goods'][] = array(
                'barcode' => $sub_goods['Item'],
                'sl' => $sub_goods['ItemCount'],
                'lof_no' => $sub_goods['Lot']
            );
        }
        return $this->format_ret(1, $ret);
    }

    function get_wms_id($record_code) {
        return $this->db->get_value("select wms_record_code from wms_b2b_trade where (record_code='{$record_code}'  OR new_record_code='{$record_code}'  )  and record_type = 'wbm_return_notice'");
    }

    function get_area_name($id) {
        if (!empty($id)) {
            return $this->db->get_value("select name from base_area where id=:id", array(':id' => $id));
        }
        return '';
    }

}
