<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
class wms_mgr {

    function upload(array &$request, array &$response, array &$app) {
        $response = load_model('wms/WmsMgrModel')->upload($request['task_id'], $request['type']);
    }

    function cancel(array &$request, array &$response, array &$app) {
        $response = load_model('wms/WmsMgrModel')->cancel($request['task_id'], $request['type'], $request['is_cancel_tag']);
    }

    function force_cancel(array &$request, array &$response, array &$app) {
        $response = load_model('wms/WmsMgrModel')->force_cancel($request['task_id'], $request['type'], $request['is_cancel_tag']);
    }

    function wms_record_info(array &$request, array &$response, array &$app) {
        $response = load_model('wms/WmsMgrModel')->wms_record_info($request['task_id'], $request['type']);
    }

    function order_shipping(array &$request, array &$response, array &$app) {
        $response = load_model('wms/WmsMgrModel')->order_shipping($request['task_id'], $request['type']);
    }
    
    //批量处理
    function opt_order_shipping(array &$request, array &$response, array &$app) {
        $response = load_model('wms/WmsMgrModel')->order_shipping($request['id'], $request['type']);
    }

    function opt_upload(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('wms/WmsMgrModel')->upload($request['id'], $request['type']);
    }

    //上传百胜iwms档案
    function cli_sync_iwms_archive(array &$request, array &$response, array &$app) {
        $app['fmt'] = "json";
        //双十一当天不运行
        $date = date("m-d H:i:s", time());
        if (($date > "11-10 21:00:00") && ($date < "11-12 00:00:00")) {
            $msg = "双十一期间档案不允许同步";
            $response = array('status' => 1, 'msg' => $msg);
            return;
        }
        //618活动禁止同步
        $pause_start_time = date('Y') . "-06-17 22:59:00";
        $pause_end_time = date('Y') . "-06-18 12:01:00";
        $current_time = time();
        if ($current_time >= strtotime($pause_start_time) && $current_time <= strtotime($pause_end_time)) {
            $msg = "618期间档案不允许同步";
            $response = array('code' => -1, 'msg' => $msg);
            return;
        }

        $obj = load_model('wms/WmsArchiveModel');
        $request['api_product'] = isset($request['api_product']) ? $request['api_product'] : '';
        $ret = $obj->sync($request['api_product']);
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    //上传wms单据
    function cli_upload_record(array &$request, array &$response, array &$app) {
        $obj = load_model('wms/WmsMgrModel');

        $ret = $obj->upload_record_cli();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    function cli_upload_batch_by_record_type(array &$request, array &$response, array &$app) {
        $obj = load_model('wms/WmsMgrModel');

        $ret = $obj->cli_upload_batch_by_record_type($request['record_type'], $request['update_index']);
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    function cli_wms_record_info(array &$request, array &$response, array &$app) {
        $obj = load_model('wms/WmsMgrModel');
        $ret = $obj->cli_wms_record_info();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    function cli_order_shipping(array &$request, array &$response, array &$app) {
        $obj = load_model('wms/WmsMgrModel');
        $ret = $obj->cli_order_shipping();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    //下载wms库存 到中间表
    function cli_down_wms_stock(array &$request, array &$response, array &$app) {
        $app['fmt'] = "json";
        //双十一当天不运行
        $date = date("m-d H:i:s", time());
        if (($date > "11-10 21:00:00") && ($date < "11-12 00:00:00")) {
            $msg = "双十一期间库存不允许同步";
            $response = array('status' => 1, 'msg' => $msg);
            return;
        }
        //618活动禁止同步
        $pause_start_time = date('Y') . "-06-17 22:59:00";
        $pause_end_time = date('Y') . "-06-18 12:01:00";
        $current_time = time();
        if ($current_time >= strtotime($pause_start_time) && $current_time <= strtotime($pause_end_time)) {
            $msg = "618期间库存不允许同步";
            $response = array('code' => -1, 'msg' => $msg);
            return;
        }
        $obj = load_model('wms/WmsInvModel');
        $request['api_product'] = isset($request['api_product']) ? $request['api_product'] : '';

        $obj->down_wms_stock($request['api_product']);
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    //下载wms库存 到中间表
    function down_wms_stock_compare(array &$request, array &$response, array &$app) {
        $app['fmt'] = "json";
        //双十一当天不运行
        $date = date("m-d H:i:s", time());
        if (($date > "11-10 21:00:00") && ($date < "11-11 21:00:00")) {
            $msg = "双十一期间库存不允许同步";
            $response = array('status' => 1, 'msg' => $msg);
            return;
        }
        //618活动禁止同步
        $pause_start_time = date('Y') . "-06-17 22:59:00";
        $pause_end_time = date('Y') . "-06-18 12:01:00";
        $current_time = time();
        if ($current_time >= strtotime($pause_start_time) && $current_time <= strtotime($pause_end_time)) {
            $msg = "618期间库存不允许同步";
            $response = array('code' => -1, 'msg' => $msg);
            return;
        }
        $obj = load_model('wms/WmsInvModel');
        $obj->down_wms_stock_compare();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    //wms库存更新到efast
    function cli_update_efast_stock_from_wms(array &$request, array &$response, array &$app) {
        $app['fmt'] = "json";
        $obj = load_model('wms/WmsInvModel');
        $obj->update_efast_stock_from_wms();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    //获取缺货库存
    function cli_sync_quehuo(array &$request, array &$response, array &$app) {
        $obj = load_model('wms/WmsMgrModel');
        $obj->sync_quehuo();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    //下载wms缺货订单 到中间表
    //页面调用 下载wms库存
    function down_wms_stock_by_barcode(array &$request, array &$response, array &$app) {
        $obj = load_model('wms/WmsInvModel');
        $barcode_arr = explode(',', $request['barcode']);
        $response = $obj->down_wms_stock_by_barcode($request['efast_store_code'], $barcode_arr);
    }

    //页面调用 wms库存更新到efast
    function update_efast_stock_by_barcode(array &$request, array &$response, array &$app) {
        $obj = load_model('wms/WmsInvModel');
        $barcode_arr = explode(',', $request['barcode']);
        $response = $obj->down_wms_stock_by_barcode($request['efast_store_code'], $barcode_arr);
    }

    function yd_inv_add(array &$request, array &$response, array &$app) {
        load_model('wms/ydwms/YdwmsInvModel')->inv_add_test();
    }

    function update_efast_stock(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('wms/WmsInvModel')->update_efast_stock_by_barcode($request['efast_store_code'], array($request['barcode']));
    }

    function iwms_shift_bill_deal(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('wms/iwms/IwmsBillHandleModel')->deal_bill('shift');
    }
    
    function sync_jdwms_return(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        load_model('wms/WmsMgrModel')->sync_jdwms_return();
        $response['status'] = 1;
    }
    
}
