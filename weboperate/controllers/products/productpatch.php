<?php

/*
 * 产品中心-产品补丁
 */
require_lib ( 'util/web_util', true );
class Productpatch {
    
    //产品补丁列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    //新建、编辑产品补丁显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
		$title_arr = array('edit'=>'编辑补丁信息', 'add'=>'新建补丁');
		$app['title'] = $title_arr[$app['scene']];
                if($app['scene']=="edit"){
                    $app['tpl']="products/productpatch_detail_edit";
                }
                if($app['scene']=="view"){
                    $app['tpl']="products/productpatch_detail_show";
                }
		$ret = load_model('products/ProductpatchModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
                
    }
    
    //编辑补丁信息数据处理。
    function patch_edit(array & $request, array & $response, array & $app) {
		$prouct = get_array_vars($request, 
                            array('cp_id', 
                                'version_no',
                                 'is_sql',
                                'version_patch',
                                'version_file_path',
                                'version_file_name',
                                'upgrade_patch',
                                'is_exec',
                                ));
                
                $prouct['is_sql'] = 1;
                $prouct['update_time'] =date('Y-m-d H:i:s');
		$ret = load_model('products/ProductpatchModel')->update($prouct, $request['id']);
		exit_json_response($ret);
	}
    //添加补丁信息数据处理。    
    function patch_add(array & $request, array & $response, array & $app) {
		$prouct = get_array_vars($request, 
                             array('cp_id', 
                                'version_no',
                                // 'is_sql',
                                'version_patch',
                                'version_file_path',
                                'version_file_name',
                                'upgrade_patch',
                                'is_exec',
                                ));
                $prouct['is_sql'] = 1;
                $prouct['create_time'] =date('Y-m-d H:i:s');
		$ret = load_model('products/ProductpatchModel')->insert($prouct);
		exit_json_response($ret);
	}
        
    //补丁附件上传
    function patch_upload(array & $request, array & $response, array & $app) {
        $upfile="version_patch";  //补丁附件路径
        $file=$_FILES['upfile'];
        if(!is_uploaded_file($file['tmp_name'])){ //判断上传文件是否存在
            //文件不存在
            exit_json_response(array('status' => '-1', 'data' => '', 'message' => '上传失败'));
        }
        if(!file_exists($upfile)){  // 判断存放文件目录是否存在
            mkdir($upfile,0777,true);
        } 
        $fname=$file['name'];
        $ftype=explode('.',$fname);
        $reaName=$upfile."/".uniqid().".".$ftype[1];
        if(file_exists($picName)){
            //>同文件名已存在;
            exit_json_response(array('status' => '-1', 'data' => '', 'message' => '上传失败,文件名重复'));
        }
        if(!move_uploaded_file($file['tmp_name'],$reaName)){ 
            //移动文件出错;
            exit_json_response(array('status' => '-1', 'data' => '', 'message' => '上传失败'));
        }
        else{
            exit_json_response(array('status' => '1', 'data'=>array('path'=>json_encode(array($reaName, $fname))), 'message' => 'success'));
        }
    }
}