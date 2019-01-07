<?php

require_lib('util/web_util', true);
require_model('sys/PrintTemplatesModel');

class print_templates {

    function do_list(array &$request, array &$response, array &$app) {
        
    }

    function add(array &$request, array &$response, array &$app) {
        $m = new PrintTemplatesModel();
        //$response['record'] = $m->get_row(array(''));
        //$app['page'] = 'NULL';

        $response['variables'] = $m->variables;

        $response['variables_all'] = array_merge($m->variables['delivery'], $m->variables['receiving'], $m->variables['detail'], $m->variables['record']);
    }

    //编辑快递模板
    function edit_express(array &$request, array &$response, array &$app) {
        $m = new PrintTemplatesModel();

        // FIXME: print_templates_id

        $ret  = $m->get_row_by_id(array('print_templates_id' => $request['_id'])); //, 'type'=>$response['record_type'] record_list
       // $response['record_list'] = $ret['data'];
        $response['record'] = $ret['data'];
        if ($response['record']['is_buildin'] == 2) {//云栈
            $app['tpl'] = 'sys/print_templates_edit_express_cainiao';
            $shop_info = $m->get_shop_key_by_temp_id($response['record']['print_templates_id']);
            if(!empty($shop_info['data']['app_key']) && !empty($shop_info['data']['user_id'])) {
                $response['shop_info'] = $shop_info['data'];
            } else {
                //测试
                $response['shop_info'] = array('app_key'=>'98801','user_id'=>'155809');
            }
            $response['variables'] = $m->variables;
             $response['variables_all'] = array_merge($m->variables['delivery'], $m->variables['receiving'], $m->variables['detail'], $m->variables['record']);
             
            $response['template_body'] = json_decode($response['record']['template_body'], true);
           // var_dump($response['template_body'] );die;
             if (!empty($response['record']['template_val'])) {
                $response['template_val'] = json_decode($response['record']['template_val'], true);
            } else {
                $response['template_val'] = $m->tpl_val;
            }
            $express_type_conf = require_conf('cainiao/cainiao');
            $response['express_type']  = isset($express_type_conf[$response['record']['company_code']]['type'])?$express_type_conf[$response['record']['company_code']]['type']:array('无');
            
        }else if($response['record']['is_buildin'] == 4){//批发模板
            $response['variables'] = $m->pf_variables;
            $response['variables_all'] = array_merge($m->pf_variables['delivery'], $m->pf_variables['receiving'], $m->pf_variables['detail'], $m->pf_variables['record']);

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
        }else {
			if ($ret['data']['company_code']=='SF'){
				$m->variables = array_merge_recursive($m->variables,$m->sf_rm_variables);
			}
            $response['variables'] = $m->variables;
            $response['variables_all'] = array_merge($m->variables['delivery'], $m->variables['receiving'], $m->variables['detail'], $m->variables['record']);
            if (in_array($ret['data']['print_templates_code'],array('alpha','alpha_sf_small','alpha_sf_big'))){
                $response['variables_all'] = array_merge($response['variables_all'],$m->jd_express_info);
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
    }
    
    function edit_express_cainiao(array &$request, array &$response, array &$app) {
         $m = new PrintTemplatesModel();


        $data = array();
        $data['print_templates_name'] = $request['print_templates_name'];

        $data['printer'] = $request['printer'];

        if(isset($request['templates_val'])){
            $request['templates_val']['detail_val'] =  $m->set_cainiao_templates_val($request['templates_val']['detail_val']);
            $data['template_val'] =  json_encode($request['templates_val']) ;
        }
        
        
        $data['template_body'] =  json_encode($request['template_body']);
        $response = $m->update($data, array('print_templates_id' => $request['print_templates_id']));       
    }
    
    function get_edit_express(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $m = new PrintTemplatesModel();
        $record = $m->get_row_by_id(array('print_templates_id' => $request['print_remplates_id']));
        if ($record['data']['company_code']=='SF'){
            $m->variables = array_merge_recursive($m->variables,$m->sf_rm_variables);
	}
        $response['variables'] = $m->variables;
        $response['variables_all'] = array_merge($m->variables['delivery'], $m->variables['receiving'], $m->variables['detail'], $m->variables['record']);
        $response['template_body'] = $m->printCastContent($record['data']['template_body'], $response['variables_all']);
    }

    //编辑快递模板
    function edit_express_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new PrintTemplatesModel();
        $request['paper_width'] = str_replace('mm', '', $request['paper_width']);
        $request['paper_height'] = str_replace('mm', '', $request['paper_height']);
        if ($request['is_buildin'] == 4) {
            $response['variables'] = $m->pf_variables;
            $response['variables_all'] = array_merge($m->pf_variables['delivery'], $m->pf_variables['receiving'], $m->pf_variables['detail'], $m->pf_variables['record']);
        } else {
            if($request['templates_type'] == 'print_barcode') {
                $barcode_arr = load_model('prm/GoodsBarcodeModel')->print_fields_default;
                $response['variables_all'] = array_flip($barcode_arr);
            } else {
                $response['variables'] = $m->variables;
                $response['variables_all'] = array_merge($m->variables['delivery'], $m->variables['receiving'], $m->variables['detail'], $m->variables['record']);
            }
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
        if($request['print_type']=='clodop'){//启用Clodop云打印控件后的修改保存
            $d = $m->printCastContent2($data['template_body'], $response['variables_all'], 'printCastContent_ToVar',1);
        }else{
            $d = $m->printCastContent2($data['template_body'], $response['variables_all'], 'printCastContent_ToVar');
        }

        $data['template_body'] = $d['body'];
        $data['template_body_replace'] = json_encode($d['replace']);
        $data['template_body_default']=$data['template_body'];
        $response = $m->update($data, array('print_templates_id' => $request['print_templates_id']));
    }

    //另存为
    function edit_express_saveas(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new PrintTemplatesModel();

        //复制
        $newId = $m->copy_from($request['print_templates_id']);   
        if ($request['is_buildin'] == 4) {
            $response['variables'] = $m->pf_variables;
            $response['variables_all'] = array_merge($m->pf_variables['delivery'], $m->pf_variables['receiving'], $m->pf_variables['detail'], $m->pf_variables['record']);
        } else {
            //更新
            $response['variables'] = $m->variables;
            $response['variables_all'] = array_merge($m->variables['delivery'], $m->variables['receiving'], $m->variables['detail'], $m->variables['record']);
        }
        $data = array();
        $data['print_templates_name'] = $request['print_templates_name'];
        $data['offset_top'] = $request['offset_top'];
        $data['offset_left'] = $request['offset_left'];
        $data['paper_width'] = $request['paper_width'];
        $data['paper_height'] = $request['paper_height'];
        $data['printer'] = $request['printer'];
        $data['company_code'] = $request['company_code'];
        $data['template_body'] = $request['template_body'];
        $data['template_val'] = json_encode($request['templates_val']);
        $m->template_val = $request['templates_val'];
        $d = $m->printCastContent2($data['template_body'], $response['variables_all'], 'printCastContent_ToVar');
        $data['template_body'] = $d['body'];


        $data['template_body_replace'] = json_encode($d['replace']);

        $response = $m->update($data, array('print_templates_id' => $newId));
        $response['data'] = $newId;
    }

    //删除快递模板
    function edit_express_deleting(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new PrintTemplatesModel();

        $response['record'] = $m->get_row(array('print_templates_id' => $request['print_templates_id']));
        $response['record'] = $response['record']['data'];

        if ($response['record']['is_buildin'] == '1') {
            $response = array('status' => '-1', 'message' => '不允许删除系统内置模板');
        } else {
            $response = $m->delete(array('print_templates_id' => $request['print_templates_id']));
        }
    }

    //上传文件
    function edit_express_upload(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
         $ret = check_ext_execl();
        if($ret['status']<0){
            $response = $ret;
            return ;
        }
        $files = array();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/";
        $url = str_replace("/web/", "/uploads/",$url);
        
        
        $fileInput = 'fileData';
        $dir = ROOT_PATH . 'webefast/uploads/';
        $type = $_POST['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        foreach ($files_name_arr as $k => $v) {
            $pic = $_FILES[$v];
            $isExceedSize = $pic['size'] > 500000;
            if (!$isExceedSize) {
                if (file_exists($dir . $pic['name'])) {
                    @unlink($dir . $pic['name']);
                }
                // 解决中文文件名乱码问题
                //$pic['name'] = iconv('UTF-8', 'GBK', $pic['name']);
                $result = move_uploaded_file($pic['tmp_name'], $dir . $pic['name']);
              //  $files[$k] = $url . $dir . $pic['name'];
                $url .=  $pic['name'];
            }
        }
        
        
        if (!$isExceedSize && $result) {
            $response = array(
                'status' => 1,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'url' => $url
            );
        } else if ($isExceedSize) {
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "文件大小超过500kb！"
            );
        } else {
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！" . $result
            );
        }
                 set_uplaod($request, $response, $app);
    }
    
}
