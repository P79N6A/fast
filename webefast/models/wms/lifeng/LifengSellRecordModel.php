<?php

require_model("wms/WmsSellRecordModel");

class LifengSellRecordModel extends WmsSellRecordModel {

    function __construct() {
        parent::__construct();
        //StorerKey
    }

    function convert_data($record_code) {
        $sql = "select json_data,new_record_code from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $row = ctx()->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));

        $order = json_decode($row['json_data'], true);
        $check_order = $this->get_record_data($order);
        if ($check_order === false) {
            return $this->format_ret(-1, '', '解密失败，稍后再处理...');
        }
        $this->get_wms_cfg($order['store_code']);

        $wms_store_code = $this->wms_cfg['wms_store_code'];

        $wms_order['BatchID'] = $this->get_unid();
        $wms_order['Facility'] = $wms_store_code;



        $wms_order['ORDHD'] = array();
        $wms_order['ORDHD']['PrintFlag'] = 0; //--是否需要发票
        // $express_company = $this->get_express_company($order['express_code']);
        $wms_order['ORDHD']['ShipperKey'] = 'SF'; //  //$express_company['company_code'];
        $wms_order['ORDHD']['DeliveryNote'] = '';
        $wms_order['ORDHD']['Notes'] = ''; //备注
        $wms_order['ORDHD']['FinalFlag'] = 1;
        $wms_order['ORDHD']['TotalLines'] = count($order['goods']); //订单行总数 todo 计算
        $wms_order['ORDHD']['CurrReq'] = 1; //条数
        $wms_order['ORDHD']['TotalLeft'] = 0;


        $wms_order['ORDHD']['DeliveryPlace'] = '';
        $wms_order['ORDHD']['IntermodalVehicle'] = 'EXPRESS';
        $wms_order['ORDHD']['Salesman'] = '';
        $wms_order['ORDHD']['Type'] = 'NORMAL';
        $wms_order['ORDHD']['InvoiceAmount'] = $order['payable_money'];
        $wms_order['ORDHD']['InterfaceActionFlag'] = 'A';
        $wms_order['ORDHD']['Priority'] = '1';
        $wms_order['ORDHD']['Facility'] = $wms_store_code;

        $wms_order['ORDHD']['StorerKey'] = $this->wms_cfg['StorerKey'];
        $wms_order['ORDHD']['OrderDate'] = date('Y-m-d H:i:s');
        $wms_order['ORDHD']['M_Company'] = !empty($order['express_no']) ? $order['express_no'] : '';
        $wms_order['ORDHD']['ExternOrderKey'] = $record_code;


        $wms_order['ORDHD']['Consignee'] = array(
            'C_Zip' => empty($order['receiver_zip_code']) ? '000000' : $order['receiver_zip_code'],
            'C_Contact1' => $this->html_decode($order['receiver_name']),
            'C_Address1' => $this->get_area_name($order['receiver_district']),
            'Facility' => $wms_store_code,
            'C_Address2' => $order['receiver_address'], // $order['receiver_address']
            'C_State' => $this->get_area_name($order['receiver_province']),
            'C_City' => $this->get_area_name($order['receiver_city']),
            'C_Phone1' => $order['receiver_mobile'],
            'C_Phone2' => $order['receiver_phone'],
        );

        $shop_name = $this->db->get_value("select shop_name from base_shop where shop_code=:shop_code", array(':shop_code' => $order['shop_code']));
//         $wms_order['ORDHD']['UserDefines'][]  = array(
//             'UserDefine'=>array(
//                 'UserDefine_No'=>3,
//                  'UserDefine_Value'=>$shop_name,
//                  'Facility'=>$order['store_code'],
//             ),
//         );
        $orderLineNo = 1;
        foreach ($order['goods'] as $row) {
            $order_goods = array();
            $order_mes = array();
            $order_mes['Facility'] = $wms_store_code;
            $order_mes['OrderInfo_Title'] = '';
            $order_mes['OrderInfo_No'] = $orderLineNo;
            $order_mes['OrderInfo_Amount'] = $row['avg_money'];
            $order_mes['OrderInfo_Content'] = '';

            $order_goods['SKU'] = $row['barcode'];
            $order_goods['Facility'] = $wms_store_code;
            $order_goods['OpenQty'] = $row['num'];

            $order_goods['ExternLineNo'] = $orderLineNo;
            $order_goods['UnitPrice'] = $row['avg_money'];
            // $wms_order['ORDHD']['OrderInformation'][]=array('OrderInfo'=>$order_mes);
            $wms_order['ORDHD'][] = array('ORDDT' => $order_goods);
            //  $wms_order['ORDHD']['ORDDT']=$order_goods;
            // break;
            $orderLineNo++;
        }


//        $wms_order['ORDHD'] = $OrderInformation;
//        $wms_order['ORDHD']['ORDDT'] = $ORDDT;
        $data = array('WMSORD' => $wms_order);

        return $this->format_ret(1, $data);
    }

    function get_unid() {
        static $time = null;
        static $num = 0;

        if (empty($num)) {
            $num = 0;
        } else {
            $num++;
        }

        $un_id = 0;
        $now_time = time();
        if (empty($time)) {
            $time = time();
            $un_id = substr($time, 1) . $num;
        } else if ($now_time == $time) {
            $un_id = substr($time, 1) . $num;
        } else {
            $time = $now_time;
            $num = 0;
            $un_id = substr($time, 1) . $num;
        }

        return $un_id;
    }

    function orderflag($record_data) {
        $arr = array();
        if (strtolower($record_data['pay_type']) == 'cod') {
            $arr[] = 'COD';
        }
        if (!empty($record_data['change_record_from'])) {
            $arr[] = 'EXCHANGE';
        }
        if ($record_data['is_split_new'] == 1) {
            $arr[] = 'SPLIT';
        }
        return $arr;
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'logistic_order_notify';
        $ret = $this->biz_req($method, $wms_order);

        if ($ret['status'] > 0 && $ret['data']['Response']['Result']['Success'] == 'true') {
            return $this->format_ret(1, $record_code);
        }
        return $ret;
    }

    function cancel($record_code, $efast_store_code) {

        $this->get_wms_cfg($efast_store_code);
        $method = 'logistic_order_cancel';


        $wms_store_code = $this->wms_cfg['wms_store_code'];
        $order['ORDHD'] = array(
            'Facility' => $wms_store_code,
            'StorerKey' => $this->wms_cfg['StorerKey'],
            'ExternOrderKey' => $record_code,
            'InterfaceActionFlag' => 'D'
        );
        $order['BatchID'] = $this->get_unid();
        $req = array(
            'WMSORD' => $order,
        );

        $ret = $this->biz_req($method, $req);
        if ($ret['status'] > 0 && $ret['data']['Response']['Result']['Success'] == 'true') {
            return $this->format_ret(1, $record_code);
        } else {
            return $this->format_ret(-1, $ret['data'], $ret['data']['Response']['Result']['Description']);
        }
    }

    function get_wms_id($record_code) {

        $record_data = $this->db->get_row("select record_code,wms_record_code from wms_oms_trade where record_code='{$record_code}'  and record_type = 'sell_record'");
        if (empty($record_data)) {
            $record_data = $this->db->get_row("select record_code,wms_record_code from wms_oms_trade where new_record_code='{$record_code}'  and record_type = 'sell_record'");
        }
        return isset($record_data['wms_record_code']) ? $record_data['wms_record_code'] : '';
    }

    function get_record_code($record_code) {
        $record_data = $this->db->get_row("select new_record_code,wms_record_code  from wms_oms_trade where record_code='{$record_code}'  and record_type = 'sell_record'");
        if (empty($record_data)) {
            $record_data = $this->db->get_row("select new_record_code,wms_record_code from wms_oms_trade where new_record_code='{$record_code}'  and record_type = 'sell_record'");
        }
        return $record_data;
    }

    function wms_record_info($record_code, $efast_store_code) {

        return $this->format_ret(-1);
    }

    function get_sale_channel($sale_channel_code, $shop_code) {
        static $sale_channel_shop = null;
        //tb_shop_type
        if (!isset($sale_channel_shop[$shop_code])) {
            $cainiao_sale_channel = require_conf('wms/qm_source_platform');
            if ($sale_channel_code == 'taobao') {
                $tb_shop_type = $this->db->get_value("select tb_shop_type from base_shop_api where shop_code =:shop_code", array(':shop_code' => $shop_code));
                if ($tb_shop_type == 'B') {//天猫店铺
                    $sale_channel_code = 'tmall';
                }
            }
            $sale_channel_shop[$shop_code] = isset($cainiao_sale_channel[$sale_channel_code]) ? $cainiao_sale_channel[$sale_channel_code] : array('OTHER', '其他');
        }
        return $sale_channel_shop[$shop_code];
    }

    function get_express_company($express_code) {

        $express_data = $this->db->get_row("select c.company_code,c.company_name FROM base_express_company c INNER JOIN base_express s ON c.company_code=s.company_code where express_code=:express_code ", array(':express_code' => $express_code));
        $qm_express_data = array(
            'JD' => '京东配送',
            'SF' => '顺丰',
            'EMS' => '标准快递',
            'EYB' => '经济快件',
            'ZJS' => '宅急送',
            'ZTO' => '中通',
            'YTO' => '圆通',
            'HTKY' => '百世汇通',
            'UC' => '优速',
            'STO' => '申通',
            'TTKDEX' => '天天快递',
            'QFKD' => '全峰',
            'FAST' => '快捷',
            'POSTB' => '邮政小包',
            'GTO' => '国通',
            'YUNDA' => '韵达',
            'DBKD' => '德邦快递',
            'OTHER' => '其他',
        );

        if (isset($qm_express_data[$express_data['company_code']])) {
            $express_data['company_name'] = $qm_express_data[$express_data['company_code']];
        } else if ($express_data['company_code'] == 'SFC') {
            $sfc_express_data = array(
                'WWRAM' => '三态-中国邮政外围小包(挂号）',
                'WWRM' => '三态-中国邮政外围小包(平邮）',
            );
            $express_data['company_code'] = $express_code;
            $express_data['compacompany_nameny_code'] = $sfc_express_data[$express_code];
        } else {
            $express_data['company_code'] = 'OTHERS';
            $express_data['company_name'] = '其他';
        }

        return $express_data;
    }

    function get_store_info($store_code) {


        $row = $this->db->get_row("select * FROM base_store where  store_code=:store_code  ", array(':store_code' => $store_code));
        if (!empty($row)) {
            $row['country'] = $this->get_area_name($row['country']);
            $row['province'] = $this->get_area_name($row['province']);
            $row['city'] = $this->get_area_name($row['city']);
            $row['district'] = $this->get_area_name($row['district']);
        }
        return $row;
    }

    function get_area_name($id) {
        if (!empty($id)) {
            return $this->db->get_value("select name from base_area where id=:id", array(':id' => $id));
        }
        return '';
    }

    //查询单据状态
    function get_record_flow($record_coce, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);

        $method = 'taobao.qimen.orderprocess.query';
        $req = array('orderType' => 'JYCK', 'orderCode' => $record_coce);
        $orderId = $this->get_wms_id($record_coce);


        if (!empty($orderId)) {
            $req['orderId'] = $orderId;
        }

        $req['warehouseCode'] = $this->wms_cfg['wms_store_code'];
//            $sql = "select json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
//        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
//                //orderId

        $ret = $this->biz_req($method, $req);

        $status_arr = array(
            'NEW' => '新增',
            'ACCEPT' => '仓库接单',
            'PRINT' => '打印',
            'PICK' => '捡货',
            'CHECK' => '复核',
            'PACKAGE' => '打包',
            'WEIGH' => '称重',
            'READY' => '待提货',
            'DELIVERED' => '已发货',
            'EXCEPTION' => '异常',
            'CLOSED' => '关闭',
            'CANCELED' => '取消',
            'REJECT' => '仓库拒单',
            'REFUSE' => '客户拒签',
            'CANCELEDFAIL' => '取消失败',
            'SIGN' => '签收',
            'TMSCANCELED' => '快递拦截',
            'PARTFULFILLED' => '部分收货完成',
            'FULFILLED' => '收货完成',
            'PARTDELIVERED' => '部分发货完成',
            'OTHER' => '其他',
        );

        $status_data = array();
        if ($ret['status'] > 0) {
            $process_data = isset($ret['data']['processes']['process']['processStatus']) ?
                    array($ret['data']['processes']['process']) : $ret['data']['processes']['process'];
            foreach ($process_data as $status) {
                $status_name = $status_arr[$status['processStatus']];
                //$status_arr[] = $status_name;
                $key = strtotime($status['operateTime']);
                $status_data[$key] = array('OpDate' => $status['operateTime'], 'Description' => $status_name);
            }
            krsort($status_data);
        }
        return $this->format_ret(1, $status_data);
    }

}
