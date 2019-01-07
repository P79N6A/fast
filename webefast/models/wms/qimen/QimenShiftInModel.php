<?php

require_model("wms/WmsShiftInModel");

class QimenShiftInModel extends WmsShiftInModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $data = $this->get_upload_record_data($record_code, 'shift_in');

        $order = json_decode($data['json_data'], true);
        if (empty($order)) {
            return $this->format_ret(-1, '', '移仓单不存在');
        }

        $new_record_code = empty($data['new_record_code']) ? $record_code : $data['new_record_code'];

        $this->get_wms_cfg($order['shift_in_store_code']);


        $kh_id = CTX()->saas->get_saas_key();
        $kh_id_arr = array('2322', '2259');
        //类型兼容 2322 测试 2259 振颜
        //移仓库不支持两个单据相同单号，和出库单号重复了
        if (in_array($kh_id, $kh_id_arr)) {
            $new_record_code = $this->create_new_record_code($new_record_code);
        }
        $wms_order['entryOrder'] = array(
            'entryOrderCode' => $new_record_code,
            'warehouseCode' => $this->wms_cfg['wms_store_code'],
            'orderType' => 'DBRK',
            'ownerCode' => $this->wms_cfg['owner_code'],
            'orderCreateTime' => $order['is_shift_in_time'],
            'remark' => (string) $order['remark'], //备注
        );
        $shift_in_store_info = $this->get_store_info($order['shift_in_store_code']);

        $shift_out_store_info = $this->get_store_info($order['shift_out_store_code']);

        $wms_order['entryOrder']['receiverInfo'] = array(
            'name' => $shift_in_store_info['contact_person'],
            'tel' => $shift_in_store_info['contact_phone'],
            'mobile' => $shift_in_store_info['contact_tel'],
            'province' => $shift_in_store_info['province'],
            'city' => $shift_in_store_info['city'],
            'area' => $shift_in_store_info['area'],
            'town' => $shift_in_store_info['town'],
            'detailAddress' => $shift_in_store_info['province'] . $shift_in_store_info['city'] . $shift_in_store_info['district'] . $shift_in_store_info['street'] . $shift_in_store_info['address'],
        );

        $wms_order['entryOrder']['senderInfo'] = array(
            'name' => $shift_out_store_info['contact_person'],
            'tel' => $shift_out_store_info['contact_phone'],
            'mobile' => $shift_out_store_info['contact_tel'],
            'province' => $shift_out_store_info['province'],
            'city' => $shift_out_store_info['city'],
            'area' => $shift_out_store_info['district'],
            'town' => $shift_out_store_info['street'],
            'detailAddress' => $shift_out_store_info['province'] . $shift_out_store_info['city'] . $shift_out_store_info['district'] . $shift_out_store_info['street'] . $shift_out_store_info['address'],
        );


        $order_goods = array();
        $orderLineNo = 1;
        foreach ($order['goods'] as $row) {
            $t_row = array();
            $t_row['orderLineNo'] = $orderLineNo;
            $t_row['ownerCode'] = $this->wms_cfg['owner_code'];
            $t_row['itemCode'] = $row['barcode'];
            $t_row['planQty'] = $row['num'];
            $t_row['inventoryType'] = 'ZP';

            $item_id = $this->get_item_id('qimen', $order['shift_in_store_code'], $row['barcode']);
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
            return $this->format_ret(-1, $ret);
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
        $req = array('warehouseCode' => $this->wms_cfg['wms_store_code'], 'ownerCode' => $this->wms_cfg['owner_code'], 'orderCode' => $record_code, 'orderId' => $wms_record_code, 'orderType' => 'DBRK');
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

        return $this->format_ret(-1, '');
//        $this->get_wms_cfg($efast_store_code);
//        $wms_order = array();
//        $wms_order['CustomerID'] = $this->wms_cfg['Company'];
//        $wms_order['WareHouse'] = $this->wms_cfg['wms_store_code'];
//        $wms_order['ID'] = $record_code;
//        $method = 'GetReceipts';
//        $ret = $this->biz_req($method, $wms_order);
//        if (!empty($ret['data']['RECEIPTS'])) {
//            $ret = $this->conv_wms_record_info($ret['data']['RECEIPTS']);
//        } else {
//            return $this->format_ret(-1, '', 'wms业务处理未完成');
//        }
        //      return $ret;
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
        return $this->db->get_value("select wms_record_code from wms_b2b_trade where (record_code='{$record_code}'  OR new_record_code='{$record_code}'  )  and record_type = 'shift_in'");
    }

    function get_store_info($store_code) {
        static $store_data = null;
        if (!isset($store_data[$store_code])) {
            $sql = "select * from base_store where store_code=:store_code";
            $data = $this->db->get_row($sql, array(':store_code' => $store_code));
            $data['province'] = $this->get_area_name($data['province']);
            $data['city'] = $this->get_area_name($data['city']);
            $data['district'] = $this->get_area_name($data['district']);
            $data['street'] = $this->get_area_name($data['street']);
            $store_data[$store_code] = $data;
        }
        return $store_data[$store_code];
    }

    function get_area_name($id) {
        if (!empty($id)) {
            return $this->db->get_value("select name from base_area where id=:id", array(':id' => $id));
        }
        return '';
    }

}
