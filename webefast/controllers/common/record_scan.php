<?php
class record_scan{
	
    function view_scan(array & $request, array & $response, array & $app){    
        $record_code = $request['record_code'];
        require_model('sys/RecordScanModel');
        $obj = new RecordScanModel($request['dj_type']);
        $ret = $obj->view_scan($record_code);
        $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        if ($ret['status'] < 0) {
            $tpl = "web_page_message";
            $app['title'] = "普通扫描出错";
            $app['message'] = $ret['message'];
            $app['url'] = array();
        } elseif ($arr_lof['lof_status']==1) {
            $tpl = "record_scan_lof";
            $response = $ret['data'];
            $response['goods_lof'] = load_model('prm/GoodsLofModel')->get_sys_lof();
        } else {
            $tpl = "record_scan";
            $response = $ret['data'];
        }
        $response['power_check'] = load_model('sys/PrivilegeModel')->check_priv('pur/purchase_record/do_checkin');
        ob_start();
        include get_tpl_path($tpl);
        $html = ob_get_contents();
        ob_end_clean();

        echo $html;
        die;
    }

    function save_scan(array & $request, array & $response, array & $app){
	$record_code = $request['record_code'];
        require_model('sys/RecordScanModel');
        $obj = new RecordScanModel($request['dj_type']);
        $response = $obj->save_scan($request);
        if ($response['status'] < 0) {
            $response['message'] = $request['scan_barcode'] . $response['message'];
        }
        //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';die;
    }
    //清除扫描记录
    function clean_scan(array & $request, array & $response, array & $app) {
        require_model('sys/RecordScanModel');
        $obj = new RecordScanModel($request['dj_type']);
        $response = $obj->clean_scan($request);
        if ($response['status'] < 0) {
            $response['message'] = $request['scan_barcode'] . $response['message'];
        }
    }

    //修改扫描数量
    function update_goods_scan_num(array & $request, array & $response, array & $app){
        require_model('sys/RecordScanModel');
        $obj = new RecordScanModel($request['dj_type']);
        $ret = $obj->update_goods_scan_num($request);
        exit_json_response($ret);
    }
}


