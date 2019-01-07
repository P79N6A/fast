<?php

require_model("wms/WmsShiftOutModel");

class QimenShiftOutModel extends WmsShiftOutModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $data = $this->get_upload_record_data($record_code, 'shift_out');

        $order = json_decode($data['json_data'], true);
        if (empty($order)) {
            return $this->format_ret(-1, '', '移仓单不存在');
        }

        $new_record_code = empty($data['new_record_code']) ? $record_code : $data['new_record_code'];

        $this->get_wms_cfg($order['shift_out_store_code']);

        $wms_order = array();
        $wms_order['deliveryOrderCode'] = $new_record_code;
        $wms_order['orderType'] = 'DBCK';
        $wms_order['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $wms_order['createTime'] = $order['is_add_time'];
        $wms_order['remark'] = $order['remark'];

        $msg = '';
        $address = '';
        $receiver_info = array();
        if (!empty($order['store_info']['province'])) {
            $receiver_info['province'] = $this->get_area_name($order['store_info']['province']);
            $address = $receiver_info['province'];
        } else {
            $msg .= '收货仓省未设置';
        }
        if (!empty($order['store_info']['city'])) {
            $receiver_info['city'] = $this->get_area_name($order['store_info']['city']);
            $address .= $receiver_info['city'];
        } else {
            $msg .= '收货仓失未设置';
        }

        if (!empty($order['store_info']['district'])) {
            $address .= $this->get_area_name($order['store_info']['district']);
        }
        if (!empty($order['store_info']['street'])) {
            $address .= $this->get_area_name($order['store_info']['street']);
        }

        if (!empty($order['store_info']['address'])) {
            $address .= $order['store_info']['address'];
        }
        $receiver_info['name'] = $order['store_info']['contact_person'];
        $receiver_info['mobile'] = $order['store_info']['contact_phone'];
        $receiver_info['detailAddress'] = $address;
        $wms_order['receiverInfo'] = $receiver_info;


        $order_goods = array();
        $orderLineNo = 1;
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        if ($is_lof == 1) {
            foreach ($order['goods'] as $row) {
                $t_row = array();
                $t_row['ownerCode'] = $this->wms_cfg['owner_code'];
                $t_row['itemCode'] = $row['barcode'];
                $t_row['inventoryType'] = 'ZP';
                $item_id = $this->get_item_id('qimen', $order['shift_out_store_code'], $row['barcode']);
                if (!empty($item_id)) {
                    $t_row['itemId'] = $item_id;
                }
                foreach ($row['batchs'] as $val) {
                    $order_line = $t_row;
                    $order_line['orderLineNo'] = $orderLineNo;
                    $order_line['batchCode'] = $val['lof_no'];
                    $order_line['productDate'] = $val['production_date'];
                    $order_line['planQty'] = $val['num'];
                    $order_goods[] = array('orderLine' => $order_line);
                    $orderLineNo++;
                }
            }
        } else {
            foreach ($order['goods'] as $row) {
                $t_row = array();
                $t_row['orderLineNo'] = $orderLineNo;
                $t_row['ownerCode'] = $this->wms_cfg['owner_code'];
                $t_row['itemCode'] = $row['barcode'];
                $t_row['planQty'] = $row['num'];
                $t_row['inventoryType'] = 'ZP';
                $item_id = $this->get_item_id('qimen', $order['shift_out_store_code'], $row['barcode']);
                if (!empty($item_id)) {
                    $t_row['itemId'] = $item_id;
                }
                $order_goods[] = array('orderLine' => $t_row);
                $orderLineNo++;
            }
        }


        $data = array('deliveryOrder' => $wms_order, 'orderLines' => $order_goods);
        return $this->format_ret(1, $data);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, $ret);
        }

        $wms_order = $ret['data'];
        $method = 'taobao.qimen.stockout.create';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            return $this->format_ret(1, $ret['data']['deliveryOrderId']);
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'taobao.qimen.order.cancel';
        $wms_record_code = $this->get_wms_id($record_code);
        $req = array('warehouseCode' => $this->wms_cfg['wms_store_code'], 'ownerCode' => $this->wms_cfg['owner_code'], 'orderCode' => $record_code, 'orderId' => $wms_record_code, 'orderType' => 'DBCK');
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

    private function get_area_name($id) {
        $sql = "select name from base_area where id=:id";
        return $this->db->get_value($sql, array(':id' => $id));
    }

    function wms_record_info($record_code, $efast_store_code) {
        return $this->format_ret(-1, '');
    }

    function conv_wms_record_info($result) {
        $ret = array();
        $data = $result['SHIPMENT'];
        if (!isset($data[0])) {
            $data = array($data);
        }
        $data = $data[0];
        $ret['efast_record_code'] = $data['ShipmentId'];
        $ret['wms_record_code'] = '';
        $ret['wms_store_code'] = $data['WareHouse'];
        $ret['express_code'] = $data['Carrier'];
        $ret['express_no'] = $data['TrackingNumber'];

        $ret['order_status'] = 'flow_end';
        $ret['order_status_txt'] = '已收发货';
        $flow_end_time = explode("T", $data['ShipmentDate']);
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
        return $this->db->get_value("select wms_record_code from wms_b2b_trade where (record_code='{$record_code}'  OR new_record_code='{$record_code}'  )  and record_type = 'shift_out'");
    }

}
