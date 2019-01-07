<?php

require_model("wms/WmsSellReturnModel");

class JdwmscloudSellReturnModel extends WmsSellReturnModel {

    function __construct() {
        parent::__construct();
    }
    
    /**
     * 售后服务单上传--数据转换
     */
    function convert_data($record_code) {
        $sql = "select wms_record_code, json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $wms_data = $this->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => 'sell_return'));
        $order = json_decode($wms_data['json_data'], true);
        $this->get_wms_cfg($order['store_code']);
       $check_order = $this->get_record_data($order);
        if ($check_order === false) {
            return $this->format_ret(-1, '', '解密失败，稍后再处理...');
        }
        $data = array();
        $data['receiptNo'] = $order['sell_return_code'];
        $data['sourceNo'] = $order['sell_record_code'];
        $data['ownerNo'] = $this->wms_cfg['deptNo'];//货主编号
        $data['billType'] = 'DDTHRK';//与京东wms协定的值
        $data['warehouseNo'] = $this->wms_cfg['warehouseNo'];
        $i = 0;
        $data['SkuNo'] = array();
        $data['SkuName'] = array();
        $data['expectedQty'] = array();
        $goods_arr = array();
        foreach ($order['goods'] as $row) {
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($row['sku'], array('goods_name', 'spec1_name', 'spec2_name'));
            $goods_arr[$row['barcode']] = $i;
            $data['SkuNo'][$i] = $row['barcode'];
            $data['SkuName'][$i] = $sku_info['goods_name'] . ' ' . $sku_info['spec1_name'] . ' ' . $sku_info['spec2_name'];//客户要求
            $data['expectedQty'][$i] = $row['num'];
            $i++;
        }

        $data['SkuNo'] = implode(",", $data['SkuNo']);
        $data['SkuName'] = implode(",", $data['SkuName']);
        $data['expectedQty'] = implode(",", $data['expectedQty']);
        return $this->format_ret(1, $data);
    }

    /**
     * 售后服务单上传
     */
    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'jingdong.eclp.rtw.acceptReturnOrder';
        $result = $this->biz_req($method, $wms_order);
        if ($result['status'] < 0) {
            return $result;
        }
        if ($result['data']['acceptReturnOrder_result']['resultCode'] != 1) {
            return $this->format_ret(-1, '', $result['data']['acceptReturnOrder_result']['msg']);
        }
        $api_record_code = isset($ret['data']['acceptReturnOrder_result']['eclpRtwNo']) && !empty($ret['data']['acceptReturnOrder_result']['eclpRtwNo']) ? $ret['data']['transportrtw_result']['eclpRtwNo'] : $record_code;

        return $this->format_ret(1, $api_record_code);
    }

    function cancel($record_code, $efast_store_code) {//京东接口不存在
        return $this->format_ret(-1, '', '不支持取消');
    }

    /**
     * 售后服务单收货查询
     */
    function wms_record_info($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'jingdong.eclp.cloud.queryReceivingResult';
        $sql = " select json_data from wms_oms_trade where record_code=:record_code AND record_type=:record_type";
        $sql_values = array(
            ':record_code' => $record_code,
            ':record_type' => 'sell_return',
        );
        $json_data = $this->db->get_value($sql, $sql_values);
        $order = json_decode($json_data, true);
        $data = array();
        $data['receiptNo'] = $order['sell_return_code'];
        $data['billType'] = 'DDTHRK';
        $data['warehouseNo'] = $this->wms_cfg['warehouseNo'];
        $ret = $this->biz_req($method, $data);

        if ($ret['status'] < 0) {
            return $ret;
        }
        $result = $this->conv_wms_record_info($ret['data'], $order);
        return $result;
    }

    function conv_wms_record_info($result, $order) {
        $api_order_data = $result['queryreceivingresult_result']['content'];
        $status_txt_map = array('flow_end' => '已收发货', 'upload' => '已上传', 'wait_upload' => '未上传');
        if ($api_order_data['status'] == 1) {
            $ret['order_status'] = 'flow_end';
            $ret['efast_record_code'] = $api_order_data['receiptNo'];
            $ret['wms_record_code'] = $api_order_data['receiptNo'];
            //发货时间
            $ret['flow_end_time'] = '';

            foreach ($order['detailModelDtos'] as $sub_goods) {
                $ret['goods'][] = array('barcode' => $sub_goods['skuNo'], 'sl' => $sub_goods['receivedQty']);
            }
        } else {
            $ret['efast_record_code'] = $order['sell_return_code'];
            $ret['order_status'] = 'upload';
            $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
            $ret['msg'] = $result['queryreceivingresult_result']['message'];
        }
        return $this->format_ret(1, $ret);
    }

    private function set_api_barcode_detail(&$api_goods_detail, $goods_arr) {
        $barcode_arr = array_keys($goods_arr);
        $sql = "select sys_code,api_code from wms_archive where wms_config_id=:wms_config_id  ";
        $sql_values = array(
            ':wms_config_id' => $this->wms_cfg['wms_config_id'],
        );
        $str = $this->arr_to_in_sql_value($barcode_arr, 'sys_code', $sql_values);
        $sql .= " AND sys_code IN ({$str}) ";
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as $val) {
            $key = $goods_arr[$val['sys_code']];
            $api_goods_detail[$key] = $val['api_code'];
        }
    }

}
