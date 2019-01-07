<?php

require_model("wms/WmsSellRecordModel");

class YdwmsSellRecordModel extends WmsSellRecordModel {

    function __construct() {
        parent::__construct();
    }

    function upload($record_code) {
        
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];


        $method = 'putSOData';
        //  var_dump($method, $wms_order);die;
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['data']['return']['returnCode'] == '0000') {
            $ret = $this->format_ret(1); //$this->format_ret(1, $ret['data']['wmsid'])
        } else {
            //  $ret['data']['return']['resultInfo']['errordescr']
            $message = isset($ret['data']['return']['returnDesc']) ? $ret['data']['return']['returnDesc'] : '接口返回为空';
            $message .= isset($ret['data']['return']['resultInfo']['errordescr']) ? ':' . $ret['data']['return']['resultInfo']['errordescr'] : '';

            $ret = $this->format_ret(-1, $ret['data'], $message); //$this->format_ret(1, $ret['data']['wmsid'])
            $check = strpos($ret['data']['return']['resultInfo']['errordescr'], '已经存在符合条件');
            if ($check !== false) {
                $ret = $this->format_ret(1);
            }
        }
        return $ret;
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
        $order = json_decode($json_data, true);
        $check_order = $this->get_record_data($order);
        if ($check_order === false) {
            return $this->format_ret(-1, '', '解密失败，稍后再处理...');
        }  
        $this->get_wms_cfg($order['store_code']);
        $wms_order = array();
        //$wms_order['OrderNo'] = $order['sell_record_code'];
        $wms_order['OrderNo'] = $this->is_canceled($record_code);
        $wms_order['OrderType'] = 'SO';
        $wms_order['WarehouseID'] = $this->wms_cfg['wms_store_code'];
        $wms_order['OrderTime'] = $order['record_time'];
        $wms_order['ExpectedShipmentTime1'] = $order['plan_send_time'];

        $wms_order['CustomerID'] = $this->wms_cfg['customerid'];
        $wms_order['SOReference2'] = $order['deal_code_list'];
        // $wms_order['SOReference3'] = $order['shop_code']; //店铺代码

        $wms_order['SOReference3'] = $this->db->get_value("select shop_name from base_shop where  shop_code = '{$order['shop_code']}' ");

        $wms_order['ConsigneeID'] = $this->db->get_value("select sale_channel_name from base_sale_channel where  sale_channel_code = '{$order['sale_channel_code']}' ");
        
        $wms_order['UserDefine6'] = $order['sale_channel_code'];
        
        $wms_order['ConsigneeName'] = $order['receiver_name'];
        $wms_order['C_Country'] = $order['receiver_country'];
        $wms_order['C_Province'] = $this->get_area_name($order['receiver_province']);
        $wms_order['C_City'] = $this->get_area_name($order['receiver_city']);
        $wms_order['C_ZIP'] = $order['receiver_zip_code'];

        $wms_order['C_Tel1'] = $order['receiver_mobile'];
        $wms_order['C_Tel2'] = $order['receiver_phone'];
        $wms_order['C_Address1'] = $order['receiver_address'];
        $wms_order['C_Address2'] = $this->get_area_name($order['receiver_district']);
        $wms_order['C_Mail'] = $order['buyer_name'];
        $wms_order['UserDefine4'] = 'ERP';
        $wms_order['UserDefine5'] = $order['seller_remark'];
        $wms_order['Notes'] = $order['buyer_remark'];
        $wms_order['H_EDI_01'] = '支付宝';
        $wms_order['H_EDI_02'] = $order['order_money'];
        // $wms_order['H_EDI_03'] =  $order['buyer_remark']; //优惠金额
        $wms_order['H_EDI_04'] = $order['paid_money'];
        $wms_order['H_EDI_06'] = $order['payable_money'];
        $wms_order['H_EDI_07'] = $order['alipay_no'];
        $wms_order['H_EDI_10'] = $order['express_money'];
        $express_company = $this->get_express_company($order['express_code']);
        $wms_order['CarrierId'] = $express_company['company_code'];
        $wms_order['CarrierName'] = $express_company['company_name'];

        $sku_arr = array();
        $item_i = 0;
        foreach ($order['goods'] as $row) {
            if (isset($sku_arr[$row['barcode']])) {
                $find_i = $sku_arr[$row['barcode']];
                $wms_order[$find_i]['detailsItem']['QtyOrdered'] += $row['num'];
            } else {
                $sku_arr[$row['barcode']] = $item_i;
                $detailsItem = array();
                $detailsItem['CustomerID'] = $this->wms_cfg['customerid'];
                $detailsItem['SKU'] = $row['barcode'];
                $detailsItem['QtyOrdered'] = $row['num'];
                $detailsItem['Price'] = $row['goods_price'];
                $wms_order[$item_i]['detailsItem'] = $detailsItem;
                $item_i++;
            }
        }
        $wms_order['invoiceItem'] = array();
        if (!empty($order['invoice_type'])) {
            $LineNumber = 1;
            foreach ($order['goods'] as $row) {
                $invoiceItem = array();
                $invoiceItem['OrderNo'] = $order['sell_record_code'];
                $invoiceItem['LineNumber'] = $LineNumber;
                $invoiceItem['Title'] = $order['invoice_title'];
                $invoiceItem['SKU'] = $row['barcode'];
                $invoiceItem['UOM'] = '件';
                $invoiceItem['QTY'] = $row['num'];
                $invoiceItem['UnitPrice'] = $row['goods_price'];
                
                
                $wms_order[]['invoiceItem'] = $invoiceItem;
                $LineNumber++;
            }
        }

        $ret_data =  array('header' => $wms_order);
        return $this->format_ret(1, $ret_data);
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'cancelSOData';
        $req['header'] = array(
            'OrderNo' => $this->is_canceled($record_code),
            'OrderType' => 'SO',
            'CustomerID' => $this->wms_cfg['customerid'],
            'WarehouseID' => $this->wms_cfg['wms_store_code'],
        );
        $ret = $this->biz_req($method, $req);
        if ($ret['data']['return']['returnCode'] == '0000') {
            $ret = $this->format_ret(1); //$this->format_ret(1, $ret['data']['wmsid'])
        } else {

            $message = isset($ret['data']['return']['returnDesc']) ? $ret['data']['return']['returnDesc'] : '接口返回为空';
            $message .= isset($ret['data']['return']['resultInfo']['errordescr']) ? ':' . $ret['data']['return']['resultInfo']['errordescr'] : '';

            $ret = $this->format_ret(-1, $ret['data'], $message); //$this->format_ret(1, $ret['data']['wmsid'])
        }
        return $ret;
    }

    private function get_area_name($id) {
        $sql = "select name from base_area where id=:id";
        return $this->db->get_value($sql, array(':id' => $id));
    }

    function get_express_company($express_code) {

        return $this->db->get_row("select c.company_code,c.company_name FROM base_express_company c INNER JOIN base_express s ON c.company_code=s.company_code where express_code=:express_code ", array(':express_code' => $express_code));
    }

    //查询单据状态
    function get_record_flow($record_coce, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'queryOrderProcess';
        $wms_order = array();
        $wms_order['orderCode'] = $this->is_canceled($record_coce);
        $wms_order['OrderType'] = 'JYCK';
        $sql = "select wms_record_code from  wms_oms_trade where record_code='{$record_coce}' AND record_type='sell_record'  ";
        $wms_order['orderId'] = $this->db->get_value($sql);
        $wms_order['CustomerID'] = $this->wms_cfg['customerid'];
        $wms_order['WarehouseID'] = $this->wms_cfg['wms_store_code'];
        $req['data'] = array('header' => $wms_order);
        $flow_arr = array(
            'ACCEPT' => '仓库接单',
            'ALLOCATED' => '已分配',
            'PICK' => '捡货',
            'PACKAGE' => '打包',
            'WEIGH' => '称重',
            'FULFILLED' => '发货完成',
            'EXCEPTION' => '异常',
            'CANCELED' => '取消',
        );


        $ret = $this->biz_req($method, $req);
        if ($ret['data']['return']['returnCode'] == '0000') {
            $process_data = &$ret['data']['orderProcess']['processes']['process'];
            $process_data = isset($process_data['processStatus']) ? array($process_data) : $process_data;

            $ret_data = array();
            foreach ($process_data as $val) {
                $Description = isset($flow_arr[$val['processStatus']]) ? $flow_arr[$val['processStatus']] : '异常状态';
                $ret_data[] = array('OpDate' => $val['operateTime'], 'Description' => $Description);
            }


            $ret = $this->format_ret(1, $ret_data);
        } else {
            $message = isset($ret['data']['return']['returnDesc']) ? $ret['data']['return']['returnDesc'] : '接口返回为空';
            $message .= isset($ret['data']['return']['resultInfo']['errordescr']) ? ':' . $ret['data']['return']['resultInfo']['errordescr'] : '';

            $ret = $this->format_ret(-1, $ret['data'], $message); //$this->format_ret(1, $ret['data']['wmsid'])
        }
        return $ret;
    }

    private function is_canceled($record_code){
    	$sql = "select new_record_code from wms_oms_trade where record_code = :record_code and record_type = :record_type";
    	$new_record_code = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
    	return !empty($new_record_code)?$new_record_code:$record_code;
    }
}
