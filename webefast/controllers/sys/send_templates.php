<?php
require_lib('util/web_util', true);
require_model('sys/SendTemplatesModel');

class send_templates
{
    function do_list(array &$request, array &$response, array &$app) {

    }

    function add(array &$request, array &$response, array &$app) {
        $m = new SendTemplatesModel();
        //$response['record'] = $m->get_row(array(''));
        //$app['page'] = 'NULL';

        $response['variables'] = $m->variables;

        $response['variables_all'] = array_merge($m->variables['delivery'], $m->variables['receiving'], $m->variables['detail'], $m->variables['record']);
    }

    //编辑快递模板
    function edit(array &$request, array &$response, array &$app) {
        $m = new SendTemplatesModel();

        // FIXME: print_templates_id


        $response['variables'] = $m->variables;
        $response['variables_all'] = array_merge($m->variables['delivery'], $m->variables['receiving'], $m->variables['detail'], $m->variables['record']);

       $response['record_type'] = empty($request['record_type']) ? 100 : $request['record_type']; //默认模板

        $response['record_list'] = $m->get_all(array('print_templates_id'=>$request['_id']));//, 'type'=>$response['record_type']
        $response['record_list'] = $response['record_list']['data'];

        $response['record'] = $response['record_list'][0];
        if(!empty($response['record']['template_val'])){
            $response['template_val'] = json_decode($response['record']['template_val'],true);
        }

        $response['record_index'] = empty($request['record_index']) ? $response['record']['print_templates_id'] : $request['record_index'];

        $response['record']['template_body'] = $m->printCastContent($response['record']['template_body'], $response['variables_all']);
        $detail_str = '';

        if(isset($response['template_val']['detail'])){

             $detail_str .= ' LODOP.ADD_PRINT_HTML(';
             $detail_str .= $response['template_val']['detail']['top'].',';
             $detail_str .= $response['template_val']['detail']['left'].',';
             $detail_str .= $response['template_val']['detail']['width'].',';
             $detail_str .= $response['template_val']['detail']['height'].',';
             $detail_str .= '"'. str_replace('"', '\"', $response['template_val']['detail']['html']).'");';
             $detail_str .= "\r\n";
             $detail_str .='LODOP.SET_PRINT_STYLEA(0,"ItemName","detail");'."\r\n";
        }
        $response['record']['template_body'] .=$detail_str;     
        $response['preview_data'] =  array();
     
       
        
    
    }

    //编辑快递模板
    function edit_send_action(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $m = new SendTemplatesModel();

        $response['variables'] = $m->variables;
        $response['variables_all'] = array_merge($m->variables['delivery'], $m->variables['receiving'], $m->variables['detail'], $m->variables['record']);

        $data = array();
        $data['print_templates_name'] = $request['print_templates_name'];
        $data['offset_top'] = $request['offset_top'];
        $data['offset_left'] = $request['offset_left'];
        $data['paper_width'] = $request['paper_width'];
        $data['paper_height'] = $request['paper_height'];
        $data['printer'] = $request['printer'];
        $data['template_body'] = $request['template_body'];

        $m->template_val = $request['templates_val'];

        $data['template_body'] = $m->printCastContent($data['template_body'], $response['variables_all'], 'printCastContent_ToVar');
        
        $data['template_body_replace'] = json_encode($m->tpl_replace);

        if(isset($m->template_val['detail_val'])&&!empty ($m->template_val['detail_val'])){
            $m->template_val['detail_val'] = explode('|',$m->template_val['detail_val']);
        }
        $data['template_val'] = json_encode($m->template_val);
        //var_dump($data);
        $response = $m->update($data, array('print_templates_id'=>$request['print_templates_id']));
    }

    //删除快递模板
    function edit_express_deleting(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $m = new SendTemplatesModel();

        $response['record'] = $m->get_row(array('print_templates_id'=>$request['print_templates_id']));
        $response['record'] = $response['record']['data'];

        if($response['record']['is_buildin'] == '1') {
            $response = array('status'=>'-1', 'message'=>'不允许删除系统内置模板');
        } else {
            $response = $m->delete(array('print_templates_id'=>$request['print_templates_id']));
        }
    }

    //上传文件
    function edit_express_upload(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
         $ret = check_ext_execl();
        if($ret['status']<0){
            $response = $ret;
            return ;
        }
        $files = array();
        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/";

        $fileInput = 'fileData';
        $dir = ROOT_PATH.'webefast/uploads/';
        $type = $_POST['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        foreach($files_name_arr as $k=>$v){
            $pic = $_FILES[$v];
            $isExceedSize = $pic['size'] > 500000;
            if(!$isExceedSize){
                if(file_exists($dir.$pic['name'])){
                    @unlink($dir.$pic['name']);
                }
                // 解决中文文件名乱码问题
                //$pic['name'] = iconv('UTF-8', 'GBK', $pic['name']);
                $result = move_uploaded_file($pic['tmp_name'], $dir.$pic['name']);
                $files[$k] = $url.$dir.$pic['name'];
            }
        }
        if(!$isExceedSize && $result){
            $response = array(
                'status' => 1,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'url' => $dir.$_FILES[$fileInput]['name']
            );
        }else if($isExceedSize){
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "文件大小超过500kb！"
            );
        }else{
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！".$result
            );
        }
                 set_uplaod($request, $response, $app);
    }
}