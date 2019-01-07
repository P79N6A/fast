<?php

class record_scan_box {

    function view_scan(array & $request, array & $response, array & $app) {
        $record_code = $request['record_code'];
        require_model('sys/RecordScanBoxModel');
        $obj = new RecordScanBoxModel($request['dj_type']);
        $ret = $obj->view_scan($record_code);
        
        if ($ret['status'] < 0) {
            $tpl = "web_page_message";
            $app['title'] = "装箱扫描出错";
            $app['message'] = $ret['message'];
            $app['url'] = array();
        } else {
            $tpl = "record_scan_box";
            $response = $ret['data'];
        }
        $wph_jit_service_status = load_model('common/ServiceModel')->check_is_auth_by_value('wph_jit');
        $response['wph_jit_service_status'] = $wph_jit_service_status;
        $arr = array('clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
        $response['record_code'] = $record_code;
        $b2b_box = load_model('sys/PrintTemplatesModel')->get_printer('b2b_box');
        $weipinhuijit_box_print = load_model('sys/PrintTemplatesModel')->get_printer('weipinhuijit_box_print');
        $general_box_print = load_model('sys/PrintTemplatesModel')->get_printer('general_box_print');
        $print_aggr_box = load_model('sys/PrintTemplatesModel')->get_printer('aggr_box');
        if ($request['dj_type'] == 'wbm_store_out') {
            $ret = load_model('api/WeipinhuijitStoreOutRecordModel')->get_by_out_record_no($record_code);
            if (!empty($ret['data'])) {
                $response['is_jit_execute'] = 1;
            } else {
                $response['is_jit_execute'] = 0;
            }
        }
        if($response['wph_jit_service_status'] == true && $response['dj_info']['connection_jit'] == true){
            if($b2b_box['status'] == -1 || $weipinhuijit_box_print['status'] == -1 || $print_aggr_box['status'] == -1){
                $response['printer'] = 0;
            }else{
                $response['printer'] = 1;
            }            
        }else{
            if($b2b_box['status'] == -1 || $general_box_print['status'] == -1 || $print_aggr_box['status'] == -1){
                $response['printer'] = 0;
            }else{
                $response['printer'] = 1;
            }              
        }
        ob_start();
        include get_tpl_path($tpl);
        $html = ob_get_contents();
        ob_end_clean();

        echo $html;
        die;
    }

    function save_scan(array & $request, array & $response, array & $app) {
        $record_code = $request['record_code'];
        require_model('sys/RecordScanBoxModel');
        $obj = new RecordScanBoxModel($request['dj_type']);
        $response = $obj->save_scan($request);
        if ($response['status'] < 0) {
            $response['message'] = $request['scan_barcode'] . $response['message'];
        }
        //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';die;
    }

    function ys_box_record(array & $request, array & $response, array & $app) {
        $task_code = $request['task_code'];
        $box_code = $request['box_code'];
        require_model('sys/RecordScanBoxModel');
        $obj = new RecordScanBoxModel($request['dj_type']);
        $response = $obj->b2b_box_record_ys($task_code, $box_code);
        //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';die;
    }

    function ys_box_task(array & $request, array & $response, array & $app) {
        $power = load_model('sys/PrivilegeModel')->check_priv('wbm/store_out_record/do_checkin');
        if ($request['dj_type'] == 'wbm_store_out' && !$power ) {                   
            $ret = array(
                'status'=>-1,
                'data' => '',
                'message' => '无验收权限'
            );
            exit_json_response($ret) ;
        }
        $task_code = $request['task_code'];
        require_model('sys/RecordScanBoxModel');
        $obj = new RecordScanBoxModel($request['dj_type']);
        $response = $obj->b2b_box_task_ys($task_code);
        //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';die;
    }

    function create_box_record(array & $request, array & $response, array & $app) {
        $task_code = $request['task_code'];
        $store_code = $request['store_code'];
        $dj_type = $request['dj_type'];

        $sysuser = load_model('oms/SellRecordOptModel')->sys_user();
        $create_user = $sysuser['user_name'];

        require_model('sys/RecordScanBoxModel');
        $obj = new RecordScanBoxModel($dj_type);
        $response = $obj->create_box_record($task_code, $store_code, $create_user);
        //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';die;
    }

    function get_scan_mode(array & $request, array & $response, array & $app) {
        $record_code = $request['record_code'];
        $record_type = $request['record_type'];
        require_model('sys/RecordScanBoxModel');
        $obj = new RecordScanBoxModel($record_type);
        $response = $obj->get_scan_mode($record_code, $record_type);
        //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';die;
    }

    //清除扫描记录
    function clean_scan(array & $request, array & $response, array & $app) {
        $record_code = $request['record_code'];
        require_model('sys/RecordScanBoxModel');
        $obj = new RecordScanBoxModel($request['dj_type']);
        $response = $obj->clean_scan($request);
        if ($response['status'] < 0) {
            $response['message'] = $request['scan_barcode'] . $response['message'];
        }
    }
    //修改商品扫描数量
    function update_goods_scan_num(array & $request, array & $response, array & $app) {
        require_model('sys/RecordScanBoxModel');
        $obj = new RecordScanBoxModel($request['dj_type']);
        $ret = $obj->update_goods_scan_num($request);
        exit_json_response($ret);
    }

    //删除无商品明细的装箱单
    function cancel_box_record(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $box_code=isset($request['box_code'])?$request['box_code']:$request['record_code'];
        //$box_code = $request['box_code'];
        require_model('sys/RecordScanBoxModel');
        $obj = new RecordScanBoxModel($request['dj_type']);
        $response = $obj->cancel_box_record($box_code);
        if ($response['status'] < 0) {
            $response['message'] = $response['message'];
        }
    }
    function choose_clodop_printer(array & $request, array & $response, array & $app){
        $response['printer'] = explode(',',$request['print_templates_code']);
    }
}
