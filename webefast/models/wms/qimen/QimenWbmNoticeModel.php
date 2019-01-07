<?php

require_model("wms/WmsWbmNoticeModel");

class QimenWbmNoticeModel extends WmsWbmNoticeModel {

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
            'deliveryOrderCode' => $new_record_code,
            'orderType' => 'B2BCK',
            'warehouseCode' => $this->wms_cfg['wms_store_code'],
            'createTime' => $order['order_time'],
            'remark' => (string) $order['remark']
        );

        $sender_info = array();
        $sender_info['name'] = $order['store']['contact_person'];
        $sender_info['mobile'] = $order['store']['mobile'];
        $sender_info['detailAddress'] = $order['store']['address'];
        $sender_info['province'] = $order['store']['province'];
        $sender_info['city'] = $order['store']['city'];
        $wms_order['senderInfo'] = $sender_info;

        $receiver_info = array();
        $receiver_info['name'] = $order['distributor']['contact_person'];
        $receiver_info['mobile'] = $order['distributor']['mobile'];
        $receiver_info['detailAddress'] = $order['distributor']['address'];

        $this->get_area($order['distributor'], $receiver_info);
        $province_arr = $this->check_area($order['distributor']['address']);
        if (!empty($province_arr) && empty($receiver_info['province'])) {
            $receiver_info['province'] = $province_arr['name'];
            $city_arr = $this->check_area($order['distributor']['address'], 3, $province_arr['id']);
            if (!empty($city_arr) && empty($receiver_info['city'])) {
                $receiver_info['city'] = $city_arr['name'];
            }
        }
        if (empty($receiver_info['province'])) {
            return $this->format_ret(-1, '', '地址缺少省');
        }
        if (empty($receiver_info['city'])) {
            return $this->format_ret(-1, '', '地址缺少市');
        }
        $wms_order['receiverInfo'] = $receiver_info;

        $sql = "select * from api_weipinhuijit_wms_info where notice_record_no=:notice_record_no ";
        $sql_value = array(
            ':notice_record_no' => $record_code,
        );
        $weipinhuijit_info = $this->db->get_row($sql, $sql_value);
        //:1-汽运;2-空运',
        $kh_id = CTX()->saas->get_saas_key();
        $kh_arr = array('2295', '2380', '2349', '842', '749'); //娅丽达 iwms切换菜鸟
        $extendProps = array();
        if (!empty($weipinhuijit_info) && in_array($kh_id, $kh_arr)) {
            //storage_no
            $ShippingStyle = array('1' => '汽运', '2' => '空运');
            $extendProps['ShippingStyle'] = $ShippingStyle[$weipinhuijit_info['delivery_method']];
            $extendProps['ShippingCode'] = $weipinhuijit_info['express_code'];
            $extendProps['DealBillid'] = '';
            $extendProps['KdBillId'] = $weipinhuijit_info['express'];
            $extendProps['Brand'] = $weipinhuijit_info['brand_code'];
            $extendProps['VIPReceiveHouse'] = $this->get_jit_warehouse($weipinhuijit_info['pick_ids']);
            $extendProps['VIPRKBillID'] = $weipinhuijit_info['delivery_id'];
            $extendProps['VIPPlanComeDate'] = $weipinhuijit_info['arrival_time'];
            $extendProps['IsVip'] = 1;
            $extendProps_new = '';
            foreach ($extendProps as $key => $val) {
                $extendProps_new[] = $key . "," . $val;
            }

            $wms_order['remark'] = implode(";", $extendProps_new);
        }

        $order_goods = array();
        $orderLineNo = 1;
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        if ($is_lof == 1) {
            foreach ($order['goods'] as $row) {
                $t_row = array();
                $t_row['ownerCode'] = $this->wms_cfg['owner_code'];
                $t_row['itemCode'] = $row['barcode'];
                $t_row['inventoryType'] = 'ZP';
                $item_id = $this->get_item_id('qimen', $order['store_code'], $row['barcode']);
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

                $item_id = $this->get_item_id('qimen', $order['store_code'], $row['barcode']);
                if (!empty($item_id)) {
                    $t_row['itemId'] = $item_id;
                }
                $order_goods[] = array('orderLine' => $t_row);
                $orderLineNo++;
            }
        }

        $data = array('deliveryOrder' => $wms_order, 'orderLines' => $order_goods);
        if (!empty($extendProps)) {
            $data['extendProps'] = $extendProps;
        }

        return $this->format_ret(1, $data);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'taobao.qimen.stockout.create';
        //  var_dump($method, $wms_order);die;
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
        $req = array('warehouseCode' => $this->wms_cfg['wms_store_code'], 'ownerCode' => $this->wms_cfg['owner_code'], 'orderCode' => $record_code, 'orderId' => $wms_record_code, 'orderType' => 'B2BCK');
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

        $ret = array('status' => -1, 'data' => array(), 'message' => '不支持获取');
        return $ret;
    }

    function conv_wms_record_info($result) {
        //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';
        $status_map = array('FULFILLED' => 'flow_end', 'NotAvailableStatus' => 'upload', 'NotExist' => 'wait_upload', 'NotExistOrIsCancel' => 'wait_upload');
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

    function get_wms_id($record_code) {
        return $this->db->get_value("select wms_record_code from wms_b2b_trade where (record_code='{$record_code}'  OR new_record_code='{$record_code}'  )  and record_type = 'wbm_notice'");
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
        if (empty($data['province']) || empty($data['city']) || empty($data['district'])) {
            $sql = "select * from base_custom where custom_code=:custom_code ";
            $data = $this->db->get_row($sql, array(':custom_code' => $data['custom_code']));
        }


//         "province": null, 
//        "city": null, 
//        "district": null,

        array_walk($fld, function($val, $key) use(&$data, &$wms_order) {
            if (!empty($data[$key])) {
                $wms_order[$val] = get_area_name_by_id($data[$key]);
            }
        });
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

}
