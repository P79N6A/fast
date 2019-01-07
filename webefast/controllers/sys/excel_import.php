<?php

require_lib('util/web_util', true);

/**
 * Excel导入控制器
 * @author jhua.zuo <jhua.zuo@baisonmail.com>
 * @since 2014-10-21
 */
class excel_import {

    public function index(array & $request, array & $response, array & $app) {

    }

    public function config(array & $request, array & $response, array & $app) {
        $M = load_model('sys/ExcelImportModel');
        $ret = $M->get_config_by_id($request['_id']);
        //echo '<pre>';print_r($ret);exit;
        $response['data'] = $ret['data'];
    }

    public function save_config(array & $request, array & $response, array & $app) {
        $M = load_model('sys/ExcelImportModel');
        $data = $request;
        $app['fmt'] = 'json';

        unset($data['_id']);
        unset($data['my_wikiUserID']);
        unset($data['my_wikiUserName']);
        unset($data['style']);
        unset($data['PHPSESSID']);
        unset($data['fastappsid']);

        $response = $M->save_config_by_id($request['_id'], $data);
    }

    /**
     * 导入excel
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function import(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $M = load_model('sys/ExcelImportModel');
        $code = $request['code'];
        $excel_src = $request['excel_src'];
        $excel_src_ary = json_decode($excel_src,TRUE);
        $import_type = $request['import_type'];
        $response = $M->import($excel_src_ary[0][0], $code, $import_type);
    }

    /**
     * excel文档下载
     * 根据传递的code, 取到相应的excel模版文件, 读取并输出
     */
    public function tplDownload(array & $request, array & $response, array & $app){
        //$app['fmt'] = 'json';
        $M = load_model('sys/ExcelImportModel');
        $code = $request['code'];
        $ext = isset($request['ext']) ? $request['ext'] : 'csv';
        $excel = $M->get_row(array('danju_code'=>$code));
        if($excel['status']==true && !empty($excel['data']['danju_path'])){
            //获取url路径
            $path = $excel['data']['danju_path'];
            $path = APP_PATH.'web'.DIRECTORY_SEPARATOR.$path;
            //echo '<hr/>$path<xmp>'.var_export($path,true).'</xmp>';//die;
            header("Content-type:application/vnd.ms-excel;charset=utf8");
            header("Content-Disposition:attachment; filename={$code}.{$ext}");
            echo file_get_contents($path);
            die();
        }else{
            exit_error_page('出错啦!', '模版文件不存在');
        }

    }

    public function tplDownload_by_code(array & $request, array & $response, array & $app){
        $path = APP_PATH.'web'.DIRECTORY_SEPARATOR.'/excelDefault/';
        $tpl = $request['tpl'];
        $name = $request['name'];
        $ext = isset($request['ext']) ? $request['ext'] : 'csv';

        $path = $path.$tpl.'.'.$ext;
        header("Content-type:application/vnd.ms-excel;charset=utf8");
        header("Content-Disposition:attachment; filename={$name}.{$ext}");
        echo file_get_contents($path);
        die();
    }

}
