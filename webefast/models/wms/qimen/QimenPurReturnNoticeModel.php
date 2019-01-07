<?php

require_model("wms/WmsPurReturnNoticeModel");

class QimenPurReturnNoticeModel extends WmsPurReturnNoticeModel {

    private $order_type = NULL;

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

        $this->get_wms_cfg($order['store_code'], 'pur_return_notice');

        $wms_order = array(
            'deliveryOrderCode' => $new_record_code,
            'orderType' => 'PTCK',
            'warehouseCode' => $this->wms_cfg['wms_store_code'],
            'createTime' => $order['order_time'],
            'remark' => (string) $order['remark'],
        );
        //iwms切换临时处理
        //佛山市顺德区宏一贸易有限公司
        //湖南铭三贸易有限公司
        $kh_id = CTX()->saas->get_saas_key();
        if (in_array($kh_id, ['2295', '2635','2349','2380'])) {
            $wms_order['supplierCode'] = (string) $order['supplier_code'];
            $wms_order['supplierName'] = (string) $order['supplier']['supplier_name'];
        }

        //获取上传单据类型
        $this->get_order_type();
        $wms_order['orderType'] = $this->order_type;

        $sender_info = array();
        $sender_info['name'] = $order['store']['contact_person'];
        $sender_info['mobile'] = $order['store']['mobile'];
        $sender_info['detailAddress'] = $order['store']['address'];
        $sender_info['province'] = $order['store']['province'];
        $sender_info['city'] = $order['store']['city'];
        $wms_order['senderInfo'] = $sender_info;

        $receiver_info = array();
        $receiver_info['name'] = $order['supplier']['contact_person'];
        $receiver_info['mobile'] = $order['supplier']['mobile'];
        $receiver_info['detailAddress'] = $order['supplier']['address'];

        $this->get_area($order['supplier'], $receiver_info);
        $province_arr = $this->check_area($order['supplier']['address']);
        if (!empty($province_arr) && empty($receiver_info['province'])) {
            $receiver_info['province'] = $province_arr['name'];
            $city_arr = $this->check_area($order['supplier']['address'], 3, $province_arr['id']);
            if (!empty($city_arr) && empty($receiver_info['city'])) {
                $receiver_info['city'] = $city_arr['name'];
            }
        }

        if (empty($receiver_info['province'])) {
            return $this->format_ret(-1, '', '供应商地址缺少省');
        }
        if (empty($receiver_info['city'])) {
            return $this->format_ret(-1, '', '供应商地址缺少市');
        }
        $wms_order['receiverInfo'] = $receiver_info;

        $order_goods = array();
        $orderLineNo = 1;
        $inventoryType = 'ZP';
        if (isset($this->wms_cfg['store_type']) && $this->wms_cfg['store_type'] == 0) {
            $inventoryType = 'CC';
        }
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        if ($is_lof == 1) {
            //wms暂不支持批次出库
            /*
              foreach ($order['goods'] as $row) {
              $t_row = array();
              $t_row['ownerCode'] = $this->wms_cfg['owner_code'];
              $t_row['itemCode'] = $row['barcode'];
              $t_row['inventoryType'] = $inventoryType;
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
             */
            foreach ($order['goods'] as $row) {
                $t_row = array();
                $t_row['orderLineNo'] = $orderLineNo;
                $t_row['ownerCode'] = $this->wms_cfg['owner_code'];
                $t_row['itemCode'] = $row['barcode'];
                $t_row['planQty'] = 0;
                $t_row['inventoryType'] = $inventoryType;
                $item_id = $this->get_item_id('qimen', $order['store_code'], $row['barcode']);
                if (!empty($item_id)) {
                    $t_row['itemId'] = $item_id;
                }
                foreach ($row['batchs'] as $val) {
                    $t_row['planQty'] += $val['num'];
                }
                $order_goods[] = array('orderLine' => $t_row);
                $orderLineNo++;
            }
        } else {
            foreach ($order['goods'] as $row) {
                $t_row = array();
                $t_row['orderLineNo'] = $orderLineNo;
                $t_row['ownerCode'] = $this->wms_cfg['owner_code'];
                $t_row['itemCode'] = $row['barcode'];
                $t_row['planQty'] = $row['num'];
                $t_row['inventoryType'] = $inventoryType;
                $item_id = $this->get_item_id('qimen', $order['store_code'], $row['barcode']);
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
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'taobao.qimen.stockout.create';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            $wms_record_code = isset($ret['data']['entryOrderId']) ? $ret['data']['entryOrderId'] : $ret['data']['deliveryOrderId'];
            return $this->format_ret(1, $wms_record_code);
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code, 'pur_return_notice');
        $method = 'taobao.qimen.order.cancel';
        $wms_record_code = $this->get_wms_id($record_code);
        $req = array('warehouseCode' => $this->wms_cfg['wms_store_code'], 'ownerCode' => $this->wms_cfg['owner_code'], 'orderCode' => $record_code, 'orderId' => $wms_record_code, 'orderType' => 'PTCK');
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
        return $this->db->get_value("SELECT wms_record_code FROM wms_b2b_trade WHERE (record_code=:_code OR new_record_code=:_code) AND record_type='pur_return_notice'", [':_code' => $record_code]);
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

    private function get_order_type() {
        if ($this->order_type == NULL) {
            $order_type_map = ['PTCK', 'CGTH'];

            $sys_params = load_model('sys/SysParamsModel')->get_val_by_code('qimen_pur_return_type');
            $this->order_type = $order_type_map[$sys_params['qimen_pur_return_type']];
            if (empty($this->order_type)) {
                $this->order_type = 'PTCK';
            }
        }
    }

}
