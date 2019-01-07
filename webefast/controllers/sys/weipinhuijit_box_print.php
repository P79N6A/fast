<?php

require_lib('util/web_util', true);
require_model('sys/WeipinhuijitBoxRecordModel');

class weipinhuijit_box_print {

    function do_list(array &$request, array &$response, array &$app) {

    }

    function add(array &$request, array &$response, array &$app) {
        $m = new WeipinhuijitBoxRecordModel();
        //$response['record'] = $m->get_row(array(''));
        //$app['page'] = 'NULL';
        $response['variables'] = $m->variables;
    }

    //编辑快递模板
    function edit_express(array &$request, array &$response, array &$app) {
        $m = new WeipinhuijitBoxRecordModel();

        // FIXME: print_templates_id

        $ret = $m->get_row_by_id($request['_id']); //, 'type'=>$response['record_type'] record_list
        // $response['record_list'] = $ret['data'];
        $response['record'] = $ret['data'];

        if ($request['print_templates_code'] == 'general_box_print') {
            $response['variables'] = $m->variables_general;
            $response['variables_all'] = array_merge($m->variables_general['record'], $m->variables_general['delivery_info']);
        } else {
            $response['variables'] = $m->variables;
            $response['variables_all'] = array_merge($m->variables['record'], $m->variables['barcode_info']);
        }


        $response['record_type'] = empty($request['record_type']) ? 100 : $request['record_type']; //默认模板
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
    }

    //编辑快递模板
    function edit_express_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new WeipinhuijitBoxRecordModel();

        if ($request['print_templates_code'] == 'general_box_print') {
            $response['variables'] = $m->variables_general;
            $response['variables_all'] = array_merge($m->variables_general['record'], $m->variables_general['delivery_info']);
        } else {
            $response['variables'] = $m->variables;
            $response['variables_all'] = array_merge($m->variables['record'], $m->variables['barcode_info']);
        }

        $data = array();
        $data['print_templates_name'] = $request['print_templates_name'];

        $data['offset_top'] = $request['offset_top'];
        $data['offset_left'] = $request['offset_left'];
        $matches = array();
        preg_match('/LODOP\.PRINT_INITA\("([^"]+)"\,"([^"]+)"\,\d+\,\d+\,"[^"]*"\);/', $request['template_body'], $matches);
        $old_offset = $matches[0];
        $arr_offset = explode(',', $old_offset);
        $arr_offset[0] = 'LODOP.PRINT_INITA("' . $data['offset_top'] . '"';
        $arr_offset[1] = '"' . $data['offset_left'] . '"';
        $new_offset = implode(",", $arr_offset);
        $request['template_body'] = str_replace($old_offset, $new_offset, $request['template_body']);
        //
        // 宽高
        $pattern = '/(LODOP\.SET_PRINT_PAGESIZE\(\d+\,)\d+(\,)\d+(\,"[^"]*"\);)/';
        preg_match($pattern, $request['template_body'], $str);
        $replace = $str[1] . $request['paper_width'] . $str[2] . $request['paper_height'] . $str[3];
        $request['template_body'] = str_replace($str[0], $replace, $request['template_body']);
        $data['paper_width'] = $request['paper_width'];
        $data['paper_height'] = $request['paper_height'];

        $data['printer'] = $request['printer'];
        $data['template_body'] = $request['template_body'];

        $data['template_val'] = isset($request['templates_val']) ? json_encode($request['templates_val']) : '';
        $m->template_val = isset($request['templates_val']) ? $request['templates_val'] : '';
        $d = $m->printCastContent2($data['template_body'], $response['variables_all'], 'printCastContent_ToVar');
        $data['template_body'] = $d['body'];
        $data['template_body_replace'] = json_encode($d['replace']);
        $response = $m->update($data, array('print_templates_id' => $request['print_templates_id']));
    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑快递单模板', 'add' => '添加快递单模板');
        $app['title'] = $title_arr[$app['scene']];
        if ($app['scene'] == 'edit') {
            $ret = load_model('sys/ExpressTplModel')->get_by_id($request['_id']);
        }
        $response['data'] = $ret['data'];
    }

}
