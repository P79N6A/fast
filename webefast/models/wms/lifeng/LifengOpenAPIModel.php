<?php

require_model('wms/lifeng/LifengAPIModel');

class LifengOpenAPIModel extends LifengAPIModel {

    protected $db;

    function __construct($token = array()) {
        parent::__construct($token);
        $this->db = CTX()->db;
    }

    function exec_api($request) {

        $method = $request['PacketMarked'];
        $action_method = str_replace('_TEST', '', $method);


        if (method_exists($this, $action_method)) {
            //  $data = $this->xml2array($this->req_data_xml);
            $data = $this->xml2array(html_entity_decode($request['Packet']));
            return $this->$action_method($data);
        } else {
            $return = array();
            $return['msgType'] = 'WMSSHP';
            $return['msgStatus'] = 'E';
            $return['msgDetail'] = '请求异常！';

            return $this->get_return($return);
        }
    }

    function WMSSHP($request) {
        $data = $request['WMSSHP'];

        $filepath = '/www/webroot/efast365_test/webefast/logs/sss_api_' . date('Ymd') . '.log';
        file_put_contents($filepath, "tdata:" . var_export($request, true), FILE_APPEND);
        //     <Response><BatchID>5016715940</BatchID>
        //     <Result><ExternDocKey>1708010000100</ExternDocKey><Success>true</Success></Result></Response>
        //Facility
        $ret_data = array();
        $ExternDocKey = '';

        if (isset($data['SHPHD']['ExternOrderKey'])) {
            $data['SHPHD'] = array($data['SHPHD']);
        }


        foreach ($data['SHPHD'] as $sub_data) {

            if (strtoupper($sub_data['SOStatus']) == 9) {
                $ret_data['data']['order_status'] = 'flow_end';
                $ret_data['data']['order_status_txt'] = '已收发货';
            } else {
                //不准确，属于部分发货
                $ret_data['data']['order_status'] = 'upload';
                $ret_data['data']['order_status_txt'] = '已上传';
            }

            $record_code = $sub_data['ExternOrderKey'];
            $record_type = 'sell_record';
            $ExternDocKey = $sub_data['ExternOrderKey'];
            $ret_data['data']['efast_record_code'] = $record_code;
            $ret_data['data']['wms_record_code'] = $record_code;
            $ret_data['data']['wms_store_code'] = $sub_data['Facility'];
            $ret_data['data']['flow_end_time'] = $sub_data['EffectiveDate'];


            $ret_data['data']['order_weight'] = 0;

            $ret_data['data']['express_code'] = $sub_data['ShipperKey'];
            $ret_data['data']['express_no'] = $sub_data['LoadKey'];
            $ret_data['data']['order_weight'] = $sub_data['Weight'];

            $goods_info = array();
            if (isset($sub_data['SHPDT']['SKU'])) {
                $sub_data['SHPDT'] = array($sub_data['SHPDT']);
            }

            foreach ($sub_data['SHPDT'] as $val) {
                $goods_info[] = array('sl' => $val['ShippedQty'], 'barcode' => $val['SKU']);
            }


            if ($ret_data['data']['order_status'] == 'flow_end') {
                if (!empty($goods_info)) {
                    $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info);
                }


                $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data, 1);
            }
        }

        $return = array();
        $return['msgType'] = 'WMSSHP';
        $return['ExternOrderKey'] = $ExternDocKey;
        $return['msgStatus'] = 'S';
        $return['msgDetail'] = '';

        return $this->get_return($return, 'WMSSHP');
    }

    function WMSREC($request) {
        $data = $request['WMSREC'];

        $filepath = '/www/webroot/efast365_test/webefast/logs/sss_api_' . date('Ymd') . '.log';
        file_put_contents($filepath, "tdata:" . var_export($request, true), FILE_APPEND);
        //     <Response><BatchID>5016715940</BatchID>
        //     <Result><ExternDocKey>1708010000100</ExternDocKey><Success>true</Success></Result></Response>
        //Facility
        $ret_data = array();
        $ExternReceiptKey = '';

        if (isset($data['RECHD']['ExternReceiptKey'])) {
            $data['RECHD'] = array($data['RECHD']);
        }


        foreach ($data['RECHD'] as $sub_data) {

            if (strtoupper($sub_data['ASNStatus']) == 9) {
                $ret_data['data']['order_status'] = 'flow_end';
                $ret_data['data']['order_status_txt'] = '已收发货';
            } else {
                //不准确，属于部分发货
                $ret_data['data']['order_status'] = 'upload';
                $ret_data['data']['order_status_txt'] = '已上传';
            }

            $record_code = $sub_data['ExternReceiptKey'];
            $record_type = 'sell_return';
            $ExternReceiptKey = $sub_data['ExternReceiptKey'];
            $ret_data['data']['efast_record_code'] = $record_code;
            $ret_data['data']['wms_record_code'] = $record_code;
            $ret_data['data']['wms_store_code'] = $sub_data['Facility'];
            $ret_data['data']['flow_end_time'] = $sub_data['EffectiveDate'];

            $ret_data['data']['express_code'] = $sub_data['ShipperKey'];

            if (isset($sub_data['UserDefines']['UserDefine'])) {
                if (isset($sub_data['UserDefines']['UserDefine']['UserDefine_No'])) {
                    $sub_data['UserDefines']['UserDefine'] = array($sub_data['UserDefines']['UserDefine']);
                }

                foreach ($sub_data['UserDefines']['UserDefine'] as $val) {
                    if ($val['UserDefine_No'] == 1) {
                        $ret_data['data']['express_no'] = $val['UserDefine_Value'];
                    }
                }
            }

            $goods_info = array();
            if (isset($sub_data['RECDT']['SKU'])) {
                $sub_data['RECDT'] = array($sub_data['RECDT']);
            }

            foreach ($sub_data['RECDT'] as $val) {
                $goods_info[] = array('sl' => $val['QtyReceived'], 'barcode' => $val['SKU']);
            }


            if ($ret_data['data']['order_status'] == 'flow_end') {
                if (!empty($goods_info)) {
                    $ret = load_model('wms/WmsRecordModel')->uploadtask_order_goods_update($record_code, $record_type, $goods_info);
                }
                $ret = load_model('wms/WmsRecordModel')->uploadtask_order_end($record_code, $record_type, $ret_data, 1);
            }
        }

        $return = array();
        $return['msgType'] = 'WMSREC';
        $return['ExternReceiptKey'] = $ExternReceiptKey;
        $return['msgStatus'] = 'S';
        $return['msgDetail'] = '';

        return $this->get_return($return, 'WMSREC');
    }

    function WMSITR($request) {
        $data = $request['WMSITR'];

        $filepath = '/www/webroot/efast365_test/webefast/logs/sss_api_' . date('Ymd') . '.log';
        file_put_contents($filepath, "tdata:" . var_export($request, true), FILE_APPEND);
        //     <Response><BatchID>5016715940</BatchID>
        //     <Result><ExternDocKey>1708010000100</ExternDocKey><Success>true</Success></Result></Response>
        //Facility



        if (isset($data['ITRHD']['Facility'])) {
            $data['ITRHD'] = array($data['ITRHD']);
        }
        $WMSDocKey = '';
        foreach ($data['ITRHD'] as $sub_data) {
            //$ret_data['data']['efast_record_code'] = $record_code;
            $WMSDocKey = $sub_data['WMSDocKey'];
            $record_data['wms_record_code'] = $WMSDocKey;
            $record_data['wms_store_code'] = $sub_data['Facility'];
            $record_data['process_time'] = $sub_data['EffectiveDate'];
            //adjust
            $record_data['order_status'] = 'upload';
            if ($sub_data['FinalFlag'] == 1) {//最后一单
                $record_data['order_status'] = 'flow_end';
            }
            $goods_info = array();
            if (isset($sub_data['ITRDT']['SKU'])) {
                $sub_data['ITRDT'] = array($sub_data['ITRDT']);
            }

            foreach ($sub_data['ITRDT'] as $val) {
                $goods_info[] = array('sl' => $val['Qty'], 'barcode' => $val['SKU']);
            }

            load_model('wms/WmsInvModel')->create_inv_order($record_data, $goods_info, 'adjust');
        }
        $return = array();
        $return['msgType'] = 'WMSITR';
        $return['WMSDocKey'] = $WMSDocKey;
        $return['msgStatus'] = 'S';
        $return['msgDetail'] = '';


        return $this->get_return($return, 'WMSITR');
    }

    function get_return($resp, $type) {
        //    $data = array('Response' => $resp);

        $return_xml = $this->array2xml($resp, 'Header');
        $return_xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $return_xml);
        if ($type == 'WMSSHP') {
            $head = '<?xml version="1.0" encoding="UTF-8"?><WMSSHPOUTPUT xmlns="http://www.baison.com/service/WMSSHPServiceOut_1_00" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
            $return_xml = $head . $return_xml . "</WMSSHPOUTPUT>";
        } else if ($type == 'WMSREC') {
            $head = '<?xml version="1.0" encoding="UTF-8"?><WMSRECOUTPUT xmlns="http://www.baison.com/service/WMSRECServiceOut_1_00" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
            $return_xml = $head . $return_xml . "</WMSRECOUTPUT>";
        } else if ($type == 'WMSITR') {
            $head = '<?xml version="1.0" encoding="UTF-8"?><WMSITROUTPUT xmlns="http://www.baison.com/service/GoodsMdataServiceOut_1_00" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
            $return_xml = $head . $return_xml . "</WMSITROUTPUT>";
        }

        return $return_xml; // 不转换任何引号;

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

}
