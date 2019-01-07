<?php

require_model("wms/WmsShiftOutModel");

class IwmscloudShiftOutModel extends WmsShiftOutModel {

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
        $shift_out_store_info = $this->get_store_info($order['shift_out_store_code']);
        $address = '';
        if (!empty($shift_out_store_info['province'])) {
            $address = $shift_out_store_info['province'];
        } else {
            $msg .= '收货仓省未设置';
        }
        if (!empty($shift_out_store_info['city'])) {
            $address .= $shift_out_store_info['city'];
        } else {
            $msg .= '收货仓失未设置';
        }

        if (!empty($shift_out_store_info['district'])) {
            $address .= $shift_out_store_info['district'];
        }
        if (!empty($order['store_info']['street'])) {
            $address .= $shift_out_store_info['street'];
        }

        if (!empty($order['store_info']['address'])) {
            $address .= $order['store_info']['address'];
        }
        $wms_order = array(
            'SyncBillId' => $new_record_code,
            'OrderId' => (string) $new_record_code,
            'BusinessDate' => $order['record_time'],
            'OrigBillId' => (string) $new_record_code,
            'CustomerCode' => $order['shift_out_store_code'],
            'CustomerName' => $shift_out_store_info['store_name'],
            'WarehouseCode' => $this->wms_cfg['wms_store_code'],
            'ChannelCode' => '',
            'ChannelName' => '',
            'PlanDate' => '',
            'CreateEmp' => (string) $order['is_add_person'],
            'CreateDate' => $order['is_add_time'],
            'Memo' => (string) $order['remark'],
            'ReceiverName' => (string) $order['store_info']['contact_person'],
            'ReceiverAddress' => (string) $address,
            'ReceiverTel' => (string) $order['store_info']['contact_phone'],
            'ReceiverMobile' => '',
            'ShippingStyle' => '',
            'CkOutCode' => 0,
        );

        $order_goods = array();
        foreach ($order['goods'] as $row) {
            $t_row = array();
            $t_row['Sku'] = $row['barcode'];
            $t_row['QtyPlan'] = $row['num'];
            $t_row['StdPrice'] = $row['price'];
            $t_row['Discount'] = $row['rebate'];
            $t_row['RealPrice'] = $row['price'];
            $t_row['Amount'] = $row['money'];
            $order_goods[] = $t_row;
        }
        $wms_order['BatchOutGoods'] = $order_goods;
        return $this->format_ret(1, $wms_order);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'ewms.batchout.set';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            return $this->format_ret(1, $ret['data']['wmsid']);
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'ewms.billcancel.set';
        $req = array('bill_type' => 'batchout.normal', 'bill_code' => $record_code);
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0 && $ret['data']['error'] == 'BIZID_NOT_FOUND') {
            return $this->format_ret(1, $ret['data']);
        }
        return $ret;
    }

    function wms_record_info($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'ewms.batchoutstatus.get';
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
            return $this->format_ret(1, $ret);
        }

        $ret['efast_record_code'] = $result['AsnCode'];
        $ret['wms_record_code'] = '';
        $ret['wms_store_code'] = $result['WarehouseCode'];
        $order_status = $result['AsnStatus'];

        $ret['order_status'] = is_null($status_map[$order_status]) ? $order_status : $status_map[$order_status];
        $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
        $ret['flow_end_time'] = $result['ChargeDate'];

        $goods = $result['BillStockGoods'];
        foreach ($goods as $sub_goods) {
            $ret['goods'][] = array('barcode' => $sub_goods['SkuCode'], 'sl' => $sub_goods['normalQuantity']);
        }
        return $this->format_ret(1, $ret);
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

    private function get_area_name($id) {
        $sql = "select name from base_area where id=:id";
        return $this->db->get_value($sql, array(':id' => $id));
    }

}
