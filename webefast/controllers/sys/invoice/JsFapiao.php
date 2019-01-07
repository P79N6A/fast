<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//开票系统配置
class JsFapiao{
    //开票主体信息列表
    function do_list(array &$request, array &$response, array &$app){
        
    }
    
    //新增开票主体
    function do_add(array &$request, array &$response, array &$app){
        $ret = load_model('oms/invoice/JsFapiaoModel')->add_info($request);
        exit_json_response($ret);
    }
    
        //开票系统设置详情
  function detail(array &$request, array &$response, array &$app) {
      //var_dump($request['_id']);die;
        $title_arr = array('edit' => '编辑开票配置', 'add' => '添加开票主体');
        $app['title'] = $title_arr[$app['scene']];
 
        if ($app['scene'] == 'edit') {
            $ret = load_model('oms/invoice/JsFapiaoModel')->get_by_id($request['_id']);
            $response = $ret['data'];
            $response['shop_pz'] =load_model('oms/invoice/JsFapiaoModel')->get_shop_code_by_id($request['_id']);
            //var_dump($response);die;
        }
        $response['shop'] = load_model("base/ShopModel")->get_purview_shop();
       //已经开启的淘宝店铺
        $response['shop_tb'] = load_model("base/ShopModel")->get_purview_tb_shop();
        $response['invoice_id'] = isset($request['_id']) ? $request['_id'] : 0;

        $response['app_scene'] = $app['scene'];
    }
    function get_shop_id(array &$request, array &$response, array &$app){
            $shop_id = load_model('oms/invoice/JsFapiaoModel')->get_shop_id($request['shop_code']);
            $response = $shop_id;
    }
    //开票系统参数编辑
    function do_edit (array &$request, array &$response, array &$app){
        $ret = load_model('oms/invoice/JsFapiaoModel')->edit_info($request);
        exit_json_response($ret);
    }
   
    //开票列表删除配置
     function do_delete(array &$request, array &$response, array &$app) {
         //删除基本配置
        $ret = load_model('oms/invoice/JsFapiaoModel')->delete($request['id']);
        //删除店铺配置
        $ret = load_model('sys/invoice/JsShopModel')->delete_shop_config($request['id']);
        exit_json_response($ret);
    }
    
     /**
     * 接口连通测试
     */
    function api_test(array &$request, array &$response, array &$app) {
        //var_dump($request);die;
        $api_url = $request['api_url'];
        if (stripos($api_url, 'http://') !== 0 && stripos($api_url, 'https://') !== 0) {
            $response = array('status' => -1, 'message' => 'URL地址格式错误，请重新填写！');
            exit_json_response($response);
        }

        $headers = array(
            "Content-Type:application/x-www-form-urlencoded",
        );
        require_lib('net/HttpClient');
        $h = new HttpClient();
        $h->newHandle('0', 'post',$api_url , $headers);
        $h->exec();

        $result = $h->responses();
        
        if (!isset($result['0'])) {
            $response = array('status' => -1, 'message' => '请求出错, 返回结果错误');
            exit_json_response($response);
        }
       
        $status = 1;
        $msg = '接口测试成功';
        $ret = $this->xml_parser($result['0']);
        if (!isset($ret['@attributes'])) {
            $status = -1;
            $msg = '接口测试失败';
        }
        $response = array('status' => $status, 'message' => $msg);
        exit_json_response($response);
    }
     function xml_parser($str){
        $xml_parser = xml_parser_create();
        if(!xml_parse($xml_parser,$str,true)){
          xml_parser_free($xml_parser);
          return false;
        }else {
          return (json_decode(json_encode(simplexml_load_string($str)),true));
        }
    }
    /**
     * 检测主题店铺是否以选择
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function check_shop_code(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ret = load_model('oms/invoice/JsFapiaoModel')->check_shop_code($request['shop_code']);
        exit_json_response($ret);
    }
}