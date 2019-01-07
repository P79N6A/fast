<?php

require_model("wms/WmsPurNoticeModel");

class BswmsPurNoticeModel extends WmsPurNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'pur_notice'));
        if (empty($json_data)) {
            return $this->format_ret(-1, '', '采购通知单号不存在');
        }
        $order = json_decode($json_data, true);
        $this->get_wms_cfg($order['store_code']);
        return $this->format_ret(1, $order);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, $ret);
        }
        $order = $ret['data'];
        $wms_order = array();
        $wms_order['customerCode'] = $this->wms_cfg['customerCode'];
        $wms_order['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $wms_order['asnCode'] = $order['record_code'];
        $wms_order['actionType'] = 'ADD'; //操作类型： ADD-新增 CANCEL-取消
        //$wms_order['orderTime'] = $order['order_time'];
        $sender = array();
        $sender['name'] = $order['supplier']['contact_person']; //取 供应商 还是去联系人？
        $sender['postalCode'] = $order['supplier']['zipcode'];
        $sender['phoneNumber'] = $order['supplier']['phone'];
        $sender['mobileNumber'] = $order['supplier']['mobile'];
        $sender['shippingAddress'] = $order['supplier']['address'];
        $sender['email'] = $order['supplier']['email'];
        $wms_order['sender'] = $sender;
        $lineNo = 1;
        foreach ($order['goods'] as $row) {
            $sku = $this->db->get_value("select sku from goods_sku where barcode = '{$row['barcode']}'");
            $key_arr = array('goods_name', 'spec1_name', 'spec2_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sku, $key_arr);
            $items = array();
            $items['itemSkuCode'] = $row['barcode'];
            $items['itemName'] = $sku_info['goods_name'];
            $items['itemQuantity'] = $row['num'];
            $items['lineNo'] = $lineNo;
            $items['itemNote'] = "颜色:" . $sku_info['spec1_name'] . ";尺码:" . $sku_info['spec2_name'];
            $wms_order['items'][]['item'] = $items;
            $lineNo ++;
        }

        $method = 'SyncAsnInfo';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            if($ret['data']['AsnInfo']['flag'] == 'SUCCESS'){
                return $this->format_ret(1, '', '采购通知推送成功');
            }
        }
        return $this->format_ret(-1, $ret['data']['AsnInfo'], '采购通知推送失败');
    }

    function cancel($record_code, $efast_store_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, $ret);
        }
        $order = $ret['data'];
        $wms_order = array();
        $wms_order['customerCode'] = $this->wms_cfg['customerCode'];
        $wms_order['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $wms_order['asnCode'] = $order['record_code'];
        $wms_order['actionType'] = 'CANCEL'; //操作类型： ADD-新增 CANCEL-取消
        $method = 'SyncAsnInfo';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            if($ret['data']['AsnInfo']['flag'] == 'SUCCESS'){
                return $this->format_ret(1, '', '通知单取消成功');
            }
        }
        return $this->format_ret(-1, $ret['data']['AsnInfo'], '通知单取消失败');
    }
    
    
    function wms_record_info($record_code, $efast_store_code) {
        $this->wms_cfg = array();
        $this->get_wms_cfg($efast_store_code);
        $method = 'GetAsnStatus';
        $data = array();
        $data['customerCode'] = $this->wms_cfg['customerCode'];
        $data['warehouseCode'] = $this->wms_cfg['wms_store_code'];
        $data['asnCode'] = $record_code;
        $ret = $this->biz_req($method, $data);
        if ($ret['status'] < 0) {
            return $ret;
        }
 
        if($ret['data']['AsnStatus']['flag'] == 'SUCCESS'){
            $ret = $this->conv_wms_record_info($ret['data']['AsnStatus']['asn'], $efast_store_code);
        } else {
            return $this->format_ret(-1,$ret,$ret['data']['AsnStatus']['note']);
        }
            
   
        return $ret;
    }

    function conv_wms_record_info($result, $efast_store_code) {
        $order_status = $result['asnStatus'];
        if ($order_status == 'FULFILLED') {
            $ret['order_status'] = 'flow_end';
            $ret['order_status_txt'] = '收货完成';
            $ret['efast_record_code'] = $result['asnCode'];
            //$ret['wms_record_code'] = '';
            $ret['wms_store_code'] = $efast_store_code;

            //发货时间
            $ret['flow_end_time'] = isset($result['receiveTime'])?$result['receiveTime']:'';
            $goods_ret = $result['products']['product'];
            if (isset($goods_ret[0])) {
                $goods = $goods_ret;
            } else {
                $goods[] = $goods_ret;
            }
          
            $cp_goods = array();
            foreach ($goods as $sub_goods) {
                if(!empty($sub_goods['normalQuantity'])){
                    $ret['goods'][] = array('barcode' => $sub_goods['skuCode'], 'sl' => $sub_goods['normalQuantity']);
                }
                if(!empty($sub_goods['defectiveQuantity'])){
                    $cp_goods[] = array('barcode' => $sub_goods['skuCode'], 'sl' => $sub_goods['defectiveQuantity']);
                }
               
            }
            if (!empty($cp_goods)) {
                //收货商品同步
                $ret_cp = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($result['asnCode'], 'pur_notice', $cp_goods,0);
                if ($ret_cp['status'] < 0) {
                    return $ret_cp;
                }
            }
            
        }
        return $this->format_ret(1, $ret);
    }

}
