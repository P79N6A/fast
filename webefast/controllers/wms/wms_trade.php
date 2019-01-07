<?php

// require_lib('util/web_util', true);
require_lib('util/oms_util', true);
// require_model('oms/TaobaoRecordModel', true);
require_model('wms/WmsTradeModel', true);
require_model('wms/WmsOrderModel', true);
require_model('wms/WmsMgrModel', true);

class wms_trade {

    //平台订单列表
    function do_list(array &$request, array &$response, array &$app) {
        $response['wmsId'] = &$request['wmsId'];
        if ($response['wmsId'] == 'oms') {
            $response['title'] = '外包仓零售单';
        } else {
            $response['title'] = '外包仓进销存单';
        }
    }

    function view(array &$request, array &$response, array &$app) {
        $response = load_model('wms/WmsMgrModel')->wms_record_info($request['task_id'], $request['type'], 1);
        $m = new WmsTradeModel();
        if ($request['type'] == 'oms') {
            $wms_info = $m->getInfoById($request['task_id'], 'wms_oms_trade');
        } else {
            $wms_info = $m->getInfoById($request['task_id'], 'wms_b2b_trade');
        }
        $record_order_type = isset($m->order_type[$wms_info['record_type']]) ? $m->order_type[$wms_info['record_type']] : '';
        $response['data']['record_order_type'] = $record_order_type;
        $response['data']['wms_info'] = $wms_info;

        if ($response['status'] > 0 && $wms_info['wms_order_flow_end_flag'] == 1) {
            $obj = load_model('util/ViewUtilModel');
            $mx = $obj->append_mx_info_by_barcode($response['data']['goods']);
            $goods_info = $obj->record_detail_append_goods_info($mx['data']);
            $total_efast_sl = '';
            $total_wms_sl = '';
            foreach ($response['data']['goods'] as $key1 => $good) {
                foreach ($goods_info as $key2 => $good_info) {
                    if ($good_info['barcode'] == $good['barcode']) {
                        $response['data']['goods'][$key1]['goods_code'] = $good_info['goods_code'];
                        $response['data']['goods'][$key1]['spec1_code'] = $good_info['spec1_code'];
                        $response['data']['goods'][$key1]['spec2_code'] = $good_info['spec2_code'];
                        $response['data']['goods'][$key1]['sku'] = $good_info['sku'];
                        $response['data']['goods'][$key1]['goods_name'] = $good_info['goods_name'];
                        $response['data']['goods'][$key1]['spec1_name'] = $good_info['spec1_name'];
                        $response['data']['goods'][$key1]['spec2_name'] = $good_info['spec2_name'];
                        $total_efast_sl += $good['efast_sl'];
                        $total_wms_sl += $good['wms_sl'];
                    }
                }
            }
//        if ($response['data']['express_code']){
//        	$express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $response['data']['express_code']));
//        	$response['data']['express_name'] = !empty($express_name)?$express_name:'';
//        }
            $response['data']['total_efast_sl'] = ($total_efast_sl >= 0) ? $total_efast_sl : '';
            $response['data']['total_wms_sl'] = ($total_wms_sl >= 0) ? $total_wms_sl : '';
            $response['data']['wms_order_time'] = (strtotime($response['data']['wms_order_time']) > 0) ? $response['data']['wms_order_time'] : '';
        }
        if ($response['data']['wms_info']['express_code']) {
            $express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $response['data']['wms_info']['express_code']));
            $response['data']['wms_info']['express_name'] = !empty($express_name) ? $express_name : '';
        }

        if ($request['type'] == 'oms') {
            $wms_oms_log = $m->get_wms_oms_log($wms_info['record_code'], 'wms_oms_log');
        } else {
            $wms_oms_log = $m->get_wms_oms_log($wms_info['record_code'], 'wms_b2b_log');
        }
        $response['data']['log_info'] = $wms_oms_log;
        $response['type'] = $request['type'];
        $response['data']['express_company'] = load_model('base/ExpressCompanyModel')->get_view_select();
    }
    
    //修改物流公司 物流单号
    function do_edit(array &$request, array &$response, array &$app) {
        $record_code = oms_tb_val('wms_oms_trade', 'record_code', array('id'=>$request['parameterUrl']['task_id']));
        $filter['record_code'] = $record_code;
        $filter['express_no'] = $request['parameter']['express_no'];
        $filter['express_code'] = $request['parameter']['express_code'];
        $ret1 = load_model('wms/WmsTradeModel')->update_express($filter);
        $ret2 = load_model('wms/WmsTradeModel')->update_express_company($filter);
        if ($ret1['status']==1 && $ret2['status']==1) {
            $ret = $ret1;
        }else {
            $ret = array('status'=>-1,'','更新失败');
        }
        exit_json_response($ret);
    }

    //ajax显示请求报文和返回报文
    function show_detail(array &$request, array &$response, array &$app) {
        $msg = '';
        $result = load_model('wms/WmsTradeModel')->get_api_log_by_id($request, 'post_data, return_data');
        if ($request['param'] === 'post') {
            $msg = json_decode($result['post_data'], true);
        }
        if ($request['param'] === 'return') {
            if($this->xml_parser($result['return_data'])){
                $xml_str = simplexml_load_string($result['return_data']);
                $msg = json_decode(json_encode($xml_str), true);
            } else {
                $msg = json_decode($result['return_data'], true);
            }
        }
        $response = $msg;
        exit_json_response($response);
    }
    
     function xml_parser($str){ 
        $xml_parser = xml_parser_create(); 
        if(!xml_parse($xml_parser,$str,true)){ 
            xml_parser_free($xml_parser); 
            return false; 
        }else { 
            return true;
        } 
    } 
    
    //外包仓库存
    function inv_list(array &$request, array &$response, array &$app) {

    }

    function update_sku(array &$request, array &$response, array &$app) {

        $data = load_model('wms/WmsTradeModel')->get_wms_store();

        $html_tpl = '<option value="" >--请选择--</option>';
        foreach ($data as $sub_data) {
            $html_tpl.="<option value='{$sub_data['store_code']}'>{$sub_data['store_name']}</option>";
        }

        $response['tpl_html'] = $html_tpl;
    }

    function inv_compare_list(array &$request, array &$response, array &$app) {

    }

    function down_compare_data(array &$request, array &$response, array &$app) {
        load_model('wms/WmsInvModel')->down_compare_data($request['compare_code'], $request['store_code']);
    }

    function get_api_log(array &$request, array &$response, array &$app) {
        $api_log = load_model('wms/WmsTradeModel')->get_api_log($request['record_code'], 'id, type, method, add_time');
        $method_arr = array('ShipmentRequest', 'ShipmentCancelRequest', 'GetShipments');
        foreach ($api_log as $key => &$value) {
            if (!empty($value['method']) && $value['method'] == 'ShipmentRequest') {
                $value['method_name'] = '上传日志';
            }
            if (!empty($value['method']) && $value['method'] == 'ShipmentCancelRequest') {
                $value['method_name'] = '取消日志';
            }
            if (!empty($value['method']) && $value['method'] == 'GetShipments') {
                $value['method_name'] = '收发货日志';
            }
            //判断是否为汉维接口的日志,仅返回出错的日志,且只返回上传日志、取消日志、收发货日志
            if (!empty($value['type']) && strtolower($value['type']) === 'hwms' && $value['is_err'] == 0 && in_array($value['method'], $method_arr)) {
                $value['result'] = '失败';
            }
        }
        if (!empty($api_log)) {
            $ret = array('status' => 1, 'data' => $api_log, 'message' => '');
        } else {
            $ret = array('status' => -1, 'data' => '', 'message' => '');
        }
        exit_json_response($ret);
    }

    //更新快递
//    function update_express(array & $request, array & $response, array & $app) {
//        $ret = load_model('wms/WmsTradeModel')->update_express($request);
//        exit_json_response($ret);
//    }
//
//    //更新快递
//    function update_express_company(array & $request, array & $response, array & $app) {
//        $ret = load_model('wms/WmsTradeModel')->update_express_company($request);
//        exit_json_response($ret);
//    }

}
