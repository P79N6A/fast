<?php

require_model("wms/WmsWbmNoticeModel");

class IwmsWbmNoticeModel extends WmsWbmNoticeModel {

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
        $address_arr = array();
        $this->get_area($order, $address_arr);
        $address_str = "中国{$address_arr['province']}{$address_arr['city']}{$address_arr['district']}{$order['address']}";
        $wms_order = array(
            'SyncBillId' => $new_record_code,
            'OrderId' => (string) $order['relation_code'],
            'BusinessDate' => $order['record_time'],
            'OrigBillId' => (string) $order['relation_code'],
            'WarehouseCode' => $this->wms_cfg['wms_store_code'],
            'syncproperty1' => $order['store_code'],
            'CustomerCode' => $order['distributor_code'],
            'CustomerName' => $order['distributor']['custom_name'],
            'ChannelCode' => '',
            'ChannelName' => '',
            'PlanDate' => '',
            'CreateEmp' => (string) $order['is_add_person'],
            'CreateDate' => $order['is_add_time'],
            'Memo' => (string) $order['remark'],
            'ReceiverName' => (string) $order['receiver_name'],
            'ReceiverAddress' => $address_str,
            'ReceiverTel' => (string) $order['receiver_tel'],
            'ReceiverMobile' => '',
            'ShippingStyle' => '',
            'CkOutCode' => 0,
        );

        $sql = "select * from api_weipinhuijit_wms_info where notice_record_no=:notice_record_no ";
        $sql_value = array(
            ':notice_record_no' => $record_code,
        );
        $weipinhuijit_info = $this->db->get_row($sql, $sql_value);
        //:1-汽运;2-空运',
        if (!empty($weipinhuijit_info)) {
            //storage_no
            $ShippingStyle = array('1' => '汽运', '2' => '空运');
            $wms_order['ShippingStyle'] = $ShippingStyle[$weipinhuijit_info['delivery_method']];
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
            $t_row['Sku'] = ($this->wms_cfg['goods_upload_type'] == 1) ? $row['sku'] : $row['barcode'];
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

    /**
     * 处理地址
     * @param array 源地址数据
     * @param array 接口数据
     */
    function get_area($data, &$wms_order) {
        $fld = array('province' => 'province', 'city' => 'city', 'district' => 'district');
        array_walk($fld, function($val, $key) use(&$data, &$wms_order) {
            if (!empty($data[$key])) {
                $wms_order[$val] = get_area_name_by_id($data[$key]);
            } else {
                $wms_order[$val] = '';
            }
        });
    }

}
