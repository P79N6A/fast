<?php
require_lib ('util/web_util', true);
require_model('sys/PrintTemplatesModel');
class record_templates {
    function do_list(array &$request, array &$response, array &$app) {
    	$ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('clodop_print');
        $response['new_clodop_print'] = !empty($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
    }
    
    function do_edit_barcode(array &$request, array &$response, array &$app) {
        $m = new PrintTemplatesModel();
        $ret = $m->get_row_by_id(array('print_templates_id' => $request['_id'])); //, 'type'=>$response['record_type'] record_list
       
        // $response['record_list'] = $ret['data'];
        $response['record'] = $ret['data'];
        
        $barcode_arr = load_model('prm/GoodsBarcodeModel')->print_fields_default;
        	foreach($barcode_arr as $k=>&$v){
        		if($k == '条码') {
                            $v = '690123456789';
                        }
        	}
        $response['variables'] = array_flip($barcode_arr);
        $response['variables_all'] = $response['variables'];

        $response['record_type'] =  100; //默认模板
        // 偏移量
        preg_match('/LODOP\.PRINT_INITA\("([^"]+)"\,"([^"]+)"\,\d+\,\d+\,"[^"]*"\);/', $response['record']['template_body'], $matches);
        $response['record']['offset_top'] = isset($matches[1]) ? $matches[1] : '';
        $response['record']['offset_left'] = isset($matches[2]) ? $matches[2] : '';

        // 宽高
        preg_match('/LODOP\.SET_PRINT_PAGESIZE\(\d+\,(\d+)\,(\d+)\,"[^"]*"\);/', $response['record']['template_body'], $matches);
        $response['record']['paper_width'] = isset($matches[1]) ? $matches[1] : '';
        $response['record']['paper_height'] = isset($matches[2]) ? $matches[2] : '';

        $response['record_index'] = empty($request['record_index']) ? $response['record']['print_templates_id'] : $request['record_index'];

        $response['record']['template_body'] = $m->printCastContent($response['record']['template_body'], $response['variables_all']);

        $response['preview_data'] = $m->printCastContent($response['record']['template_body'], $response['variables_all'], 'printCastContent_ToVar');



        if (!empty($response['record']['template_val'])) {
            $response['template_val'] = json_decode($response['record']['template_val'], true);
        } else {
            $response['template_val'] = $m->tpl_val;
        }
        
        $conf = require_conf('sys/upload');
        $response['upload_path'] = $conf['path']['upload_path'];
    }
        
    //打印条码
    function print_barcode(array &$request, array &$response, array &$app) {
        $ret1 = load_model('sys/PrintTemplatesModel')->get_templates_by_code('barcode_lodop');
        if ($ret1['status'] > 0) {
            $response['status'] = '1';
            $response['tpl'] = $ret1['data'];
            $response['tpl']['printer'] = $ret1['data']['printer'];
        } else {
            $response['status'] = '-1';
            $response['message'] = $ret1['message'];
        }
        //$app['tpl'] = 'pur/purchase_record/print_barcode';
        if (!isset($request['printer']) || empty($request['printer'])) {
            $request['printer'] = isset($response['tpl']['printer']) ? $response['tpl']['printer'] : '';
        }
    }
    
    //编辑条码模板clodop
    function do_edit_barcode_clodop(array &$request, array &$response, array &$app) {
       session_cache('set');
        $m = new PrintTemplatesModel();
        $ret = $m->get_row_by_id(array('print_templates_id' => $request['_id'])); //, 'type'=>$response['record_type'] record_list
       
        // $response['record_list'] = $ret['data'];
        $response['record'] = $ret['data'];
        
        $barcode_arr = load_model('prm/GoodsBarcodeModel')->print_fields_default;
        	foreach($barcode_arr as $k=>&$v){
        		if($k == '条码') {
                            $v = '690123456789';
                        }
        	}
        $response['variables'] = array_flip($barcode_arr);
        $response['variables_all'] = $response['variables'];

        $response['record_type'] =  100; //默认模板
        // 偏移量
        preg_match('/LODOP\.PRINT_INITA\("([^"]+)"\,"([^"]+)"\,\d+\,\d+\,"[^"]*"\);/', $response['record']['template_body'], $matches);
        $response['record']['offset_top'] = isset($matches[1]) ? $matches[1] : '';
        $response['record']['offset_left'] = isset($matches[2]) ? $matches[2] : '';

        // 宽高
        preg_match('/LODOP\.SET_PRINT_PAGESIZE\(\d+\,(\d+)\,(\d+)\,"[^"]*"\);/', $response['record']['template_body'], $matches);
        $response['record']['paper_width'] = isset($matches[1]) ? $matches[1] : '';
        $response['record']['paper_height'] = isset($matches[2]) ? $matches[2] : '';

        $response['record_index'] = empty($request['record_index']) ? $response['record']['print_templates_id'] : $request['record_index'];

        $response['record']['template_body'] = $m->printCastContent($response['record']['template_body'], $response['variables_all']);
        $response['record']['template_body_default'] = $m->printCastContent_clodop_img($response['record']['template_body'], $response['variables_all']);

        $response['preview_data'] = $m->printCastContent($response['record']['template_body'], $response['variables_all'], 'printCastContent_ToVar');



        if (!empty($response['record']['template_val'])) {
            $response['template_val'] = json_decode($response['record']['template_val'], true);
        } else {
            $response['template_val'] = $m->tpl_val;
        }
        $conf = require_conf('sys/upload');
        $response['upload_path'] = $conf['path']['upload_path'];
    }
    
}
