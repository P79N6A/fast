<?php

require_model("wms/WmsWbmNoticeModel");

class IwmscloudWbmNoticeModel extends WmsWbmNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $data = $this->get_upload_record_data($record_code, 'wbm_notice');

        $order = json_decode($data['json_data'], true);
        if (empty($order)) {
            return $this->format_ret(-1, '', '批发销货通知单不存在');
        }
        $new_record_code = empty($data['new_record_code']) ? $record_code : $data['new_record_code'];

        $this->get_wms_cfg($order['store_code']);
        $wms_order = array(
            'SyncBillId' => $new_record_code,
            'OrderId' => (string) $order['relation_code'],
            'BusinessDate' => $order['record_time'],
            'WarehouseCode' => $this->wms_cfg['wms_store_code'],
            'syncproperty1' => $order['store_code'],
            'OrigBillId' => (string) $order['relation_code'],
            'CustomerCode' => $order['distributor_code'],
            'CustomerName' => $order['distributor']['custom_name'],
            'ChannelCode' => '',
            'ChannelName' => '',
            'PlanDate' => '',
            'CreateEmp' => (string) $order['is_add_person'],
            'CreateDate' => $order['is_add_time'],
            'Memo' => (string) $order['remark'],
            'ReceiverName' => (string) $order['distributor']['contact_person'],
            'ReceiverAddress' => (string) $order['distributor']['address'],
            'ReceiverTel' => (string) $order['distributor']['mobile'],
            'ReceiverMobile' => '',
            'ShippingStyle' => '',
            'CkOutCode' => 0,
        );
        $sql = "select * from api_weipinhuijit_wms_info where notice_record_no=:notice_record_no ";
        $sql_value = array(
            ':notice_record_no' => $record_code,
        );
        $weipinhuijit_info = $this->db->get_row($sql, $sql_value);
        if (!empty($weipinhuijit_info)) {
            $wms_order['ShippingCode'] = $weipinhuijit_info['express_code'];
            $wms_order['DealBillid'] = '';
            $wms_order['KdBillId'] = $weipinhuijit_info['express'];
            $wms_order['Brand'] = $weipinhuijit_info['brand_code'];
            $wms_order['VIPReceiveHouse'] = $this->get_jit_warehouse($weipinhuijit_info['pick_ids']);
            $wms_order['VIPRKBillID'] = $weipinhuijit_info['delivery_id'];
            $wms_order['VIPPlanComeDate'] = $weipinhuijit_info['arrival_time'];
            $wms_order['IsVip'] = 1;
        }
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
        if (!empty($result['ShippingCode'])) {
            $ret['express_code'] = $result['ShippingCode'];
        }
        if (!empty($result['KDBillID'])) {
            $ret['express_no'] = $result['KDBillID'];
        }
        $ret['order_status'] = is_null($status_map[$order_status]) ? $order_status : $status_map[$order_status];
        $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
        $ret['flow_end_time'] = $result['ChargeDate'];

        $goods = $result['BillStockGoods'];
        foreach ($goods as $sub_goods) {
            $ret['goods'][] = array('barcode' => $sub_goods['SkuCode'], 'sl' => $sub_goods['normalQuantity']);
        }
        return $this->format_ret(1, $ret);
    }

    private function get_jit_warehouse($pick_ids) {
        $pick_ids_arr = explode(',', $pick_ids);
        $id = $pick_ids_arr[0];
        $sql = "select h.warehouse_name from api_weipinhuijit_pick p
            INNER JOIN api_weipinhuijit_warehouse h ON p.warehouse=h.warehouse_code
              where id=:id";
        $sql_value = array(
            ':id' => $id,
        );
        return $this->db->get_value($sql, $sql_value);
    }

    private function get_brand_name($brand_code) {
        static $brand_arr = null;
        if (!isset($brand_arr[$brand_code])) {
            $sql = "select brand_name from base_brand where brand_code=:brand_code";
            $sql_value = array(
                ':brand_code' => $brand_code,
            );
            $brand_arr[$brand_code] = $this->db->get_value($sql, $sql_value);
        }
        return $brand_arr[$brand_code];
    }

}
