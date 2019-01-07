<?php

require_model("wms/WmsWbmNoticeModel");

class IwmsPurReturnNoticeModel extends WmsWbmNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $data = $this->get_upload_record_data($record_code, 'pur_return_notice');

        $order = json_decode($data['json_data'], true);
        if (empty($order)) {
            return $this->format_ret(-1, '', '采购退货通知单不存在');
        }
        $new_record_code = empty($data['new_record_code']) ? $record_code : $data['new_record_code'];

        $this->get_wms_cfg($order['store_code']);
        $wms_order = array(
            'SyncBillId' => $new_record_code,
            'BusinessDate' => $order['record_time'],
            'OrigBillId' => (string) $order['relation_code'],
            'WarehouseCode' => $this->wms_cfg['wms_store_code'],
            'syncproperty1' => $order['store_code'],
            'VendorCode' => (string) $order['supplier_code'],
            'VendorName' => (string) $order['supplier']['supplier_name'],
            'PlanDate' => '',
            'CreateEmp' => (string) $order['is_add_person'],
            'CreateDate' => $order['record_time'],
            'Status' => 0,
            'CkOutCode' => '0',
            'Category' => '',
            'Memo' => (string) $order['remark'], //备注
        );

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
        $wms_order['PurchaseGoods'] = $order_goods;
        //echo '<hr/>$wms_order<xmp>'.var_export($wms_order,true).'</xmp>';die;
        return $this->format_ret(1, $wms_order);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'ewms.purchasereturn.set';
        $ret = $this->biz_req($method, $wms_order);
        //$ret = array ( 'status' => 1, 'data' => array ( 'bizid' => 'JR20150420000003', 'wmsid' => 'ASCK1150521000001', 'state' => 'Repeated', 'msg' => '单号已经存在', ), 'message' => '操作成功');
        if ($ret['status'] > 0) {
            return $this->format_ret(1, $ret['data']['wmsid']);
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'ewms.billcancel.set';
        $req = array('bill_type' => 'purchasereturn.normal', 'bill_code' => $record_code);
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0 && $ret['data']['error'] == 'BIZID_NOT_FOUND') {
            return $this->format_ret(1, $ret['data']);
        }
        return $ret;
    }

    function wms_record_info($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'ewms.purchasereturnstatus.get';
        $ret = $this->biz_req($method, array('BillId' => $record_code));
        /* $ret = array (
          'WarehouseCode' => 'ck1',
          'AsnCode' => 'PFTZ20150410000014',
          'AsnStatus' => 'FULFILLED',
          'OperateorTime' => '2015-05-22 14:20:28',
          'ExceptionCode' => NULL,
          'Memo' => '',
          'ChargeDate' => '2015-05-22 14:20:28',
          'BillStockGoods' =>
          array (
          0 =>
          array (
          'SkuCode' => 'SP001001002',
          'normalQuantity' => '4',
          ),
          1 =>
          array (
          'SkuCode' => 'SP001002000',
          'normalQuantity' => '8',
          ),
          ),
          ); */
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->conv_wms_record_info($ret['data']);
        return $ret;
    }

    function conv_wms_record_info($result) {
        //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';
        $status_map = array('DELIVERED' => 'flow_end', 'NotAvailableStatus' => 'upload', 'NotExist' => 'wait_upload', 'NotExistOrIsCancel' => 'wait_upload');
        $status_txt_map = array('flow_end' => '已收发货', 'upload' => '已上传', 'wait_upload' => '未上传');

        $ret = array();
        if (isset($result['bizid'])) {
            $ret['efast_record_code'] = $result['bizid'];
            $order_status = $result['state'];
            $ret['order_status'] = isset($status_map[$order_status]) ? $status_map[$order_status] : $ret['order_status'];
            $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
            $ret['msg'] = $result['msg'];
            //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
            return $this->format_ret(1, $ret);
        }

        $ret['efast_record_code'] = $result['AsnCode'];
        $ret['wms_record_code'] = $result['WMSBillCode'];
        $ret['wms_store_code'] = $result['WarehouseCode'];
        $order_status = $result['AsnStatus'];

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
        //echo '<hr/>$order_info<xmp>'.var_export($ret,true).'</xmp>';die;
        return $this->format_ret(1, $ret);
    }

}
