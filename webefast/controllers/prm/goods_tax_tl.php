<?php

require_lib('util/web_util', true);
//商品税收编码
class goods_tax_tl{
    public function do_list(array & $request, array & $response, array & $app) {
       
    }
    
    
    public function import(array &$request, array &$response, array &$app) {
        $response['action_type'] = $request['action'];
    }
    
    function import_upload(array & $request, array & $response, array & $app) {
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
        set_uplaod($request, $response, $app);
    }
    
     //商品税收分类导入
    public function import_action(array &$request, array &$response, array &$app) {
                $app['fmt'] = 'json';
		$file = $request['url'];
                    if (empty($file)) {
                        $response = array(
                            'status' => 0,
                            'type' => '',
                            'msg' => "请先上传文件"
                        );
                    }
                    if($request['action_type'] == 'do_barcode'){ //条码导入
                        $res = load_model('prm/GoodsTaxModel')->import_tax_by_barcode($file,$request['action_type']);
                    }else{
                        $res = load_model('prm/GoodsTaxModel')->import_tax_by_goods_code($file,$request['action_type']);
                    }
		 
		
        $response = array('message'=>$res['message'], 'status'=>$res['status']);
    }
    
    function detail(array &$request, array &$response, array &$app) {
        $ret = array();
        if (!empty($request['_id'])) {
            $ret = load_model('prm/GoodsTaxModel')->get_by_id($request['_id']);
        }
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
        $response['app_scene'] = $_GET['app_scene'];
    }
    
    //编辑
    function do_edit(array &$request, array &$response, array &$app) {
        $up_arr = array(
            'tax_code' =>$request['tax_code'],
            'unit' => isset($request['unit'])?$request['unit']:'',
            'goods_code_short' =>$request['goods_code_short'],
        );
        $ret = load_model('prm/GoodsTaxModel')->do_edit($request['tax_id'],$up_arr);
        exit_json_response($ret);
    }
    
    //删除
    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('prm/GoodsTaxModel')->do_delete($request['tax_id']);
        exit_json_response($ret);
    }
    
}