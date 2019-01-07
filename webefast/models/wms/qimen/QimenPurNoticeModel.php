<?php

require_model("wms/WmsPurNoticeModel");

class QimenPurNoticeModel extends WmsPurNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $data = $this->get_upload_record_data($record_code, 'pur_notice');

        $order = json_decode($data['json_data'], true);
        if (empty($order)) {
            return $this->format_ret(-1, '', '采购通知单不存在');
        }

        $new_record_code = empty($data['new_record_code']) ? $record_code : $data['new_record_code'];

        $this->get_wms_cfg($order['store_code']);

        $wms_order['entryOrder'] = array(
            'entryOrderCode' => $new_record_code,
            'purchaseOrderCode' => $new_record_code,
            'ownerCode' => $this->wms_cfg['owner_code'],
            'warehouseCode' => $this->wms_cfg['wms_store_code'],
            'orderType' => 'CGRK',
            'supplierCode' => (string) $order['supplier_code'],
            'supplierName' => (string) $order['supplier']['supplier_name'],
            'senderInfo' => array('name' => $order['supplier']['supplier_name']),
            'orderCreateTime' => $order['is_add_time'],
            'remark' => (string) $order['remark'], //备注
        );

        if (in_array($this->wms_cfg['product_type'], ['cainiao'])) {
            $sender_info = array();
            $sender_info['name'] = $order['supplier']['contact_person'];
            $sender_info['mobile'] = $order['supplier']['mobile'];
            $sender_info['detailAddress'] = $order['supplier']['address'];
            $sender_info['zipCode'] = $order['supplier']['zipcode'];

            $this->get_area($order['supplier'], $sender_info);
            $province_arr = $this->check_area($order['supplier']['address']);
            if (!empty($province_arr) && empty($sender_info['province'])) {
                $sender_info['province'] = $province_arr['name'];
                $city_arr = $this->check_area($order['supplier']['address'], 3, $province_arr['id']);
                if (!empty($city_arr) && empty($sender_info['city'])) {
                    $sender_info['city'] = $city_arr['name'];
                }
            }
            if (empty($sender_info['province'])) {
                return $this->format_ret(-1, '', '供应商地址缺少省');
            }
            if (empty($sender_info['city'])) {
                return $this->format_ret(-1, '', '供应商地址缺少市');
            }
            $wms_order['entryOrder']['senderInfo'] = $sender_info;
        }

        $order_goods = array();
        $orderLineNo = 1;
        foreach ($order['goods'] as $row) {
            $t_row = array();
            $t_row['orderLineNo'] = $orderLineNo;
            $t_row['ownerCode'] = $this->wms_cfg['owner_code'];
            $t_row['itemCode'] = $row['barcode'];

            $item_id = $this->get_item_id('qimen', $order['store_code'], $row['barcode']);
            if (!empty($item_id)) {
                $t_row['itemId'] = $item_id;
            }

            $t_row['planQty'] = $row['num'];
            $t_row['inventoryType'] = 'ZP';

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
        $req = array('warehouseCode' => $this->wms_cfg['wms_store_code'], 'ownerCode' => $this->wms_cfg['owner_code'], 'orderCode' => $record_code, 'orderId' => $wms_record_code, 'orderType' => 'CGRK');
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
        return $this->db->get_value("select wms_record_code from wms_b2b_trade where (record_code='{$record_code}'  OR new_record_code='{$record_code}'  )  and record_type = 'pur_notice'");
    }

    function wms_record_info($record_code, $efast_store_code) {

        return $this->format_ret(-1);
    }

    private function check_area($address, $type = 2, $parent_id = 1) {
        $sql = "select id,name from base_area where  type=:type AND parent_id=:parent_id ";
        $sql_value = array(':type' => $type, ':parent_id' => $parent_id);
        $data = $this->db->get_all($sql, $sql_value);
        $area_arr = array();
        foreach ($data as $val) {
            if (strpos($address, $val['name']) !== false) {
                $area_arr = $val;
                break;
            }
        }
        return $area_arr;
    }

    /**
     * 处理地址
     * @param array 源地址数据
     * @param array 接口数据
     */
    function get_area($data, &$wms_order) {
        $fld = array('province' => 'province', 'city' => 'city', 'district' => 'area');
        array_walk($fld, function($val, $key) use(&$data, &$wms_order) {
            if (!empty($data[$key])) {
                $wms_order[$val] = get_area_name_by_id($data[$key]);
            }
        });
    }

}
