<?php
require_lib ( 'util/web_util', true );

class patch{
    
    function do_list(array & $request, array & $response, array & $app) {

    }
    
    //获取产品版本
    function get_product_version(array & $request, array & $response, array & $app) {
        $cpid=$request['cpid'];
        $ret =  load_model('patch/PatchModel')->get_product_version($cpid);  
        exit_json_response($ret);
    }
    
    //获取产品版本补丁
    function get_version_patch(array & $request, array & $response, array & $app) {
        $cpid=$request['cpid'];
        $ver_no=$request['ver_no'];
        $ret =  load_model('patch/PatchModel')->get_version_patch($cpid,$ver_no);  
        exit_json_response($ret);
    }
    
    //批量升级数据库补丁
    function do_upgrade_all(array & $request, array & $response, array & $app){
        $ret = load_model('patch/PatchModel')->set_upgrade_dbs($request['dbs'],$request['version_no'],$request['version_patch']);
        exit_json_response($ret);
    }
}
?>
