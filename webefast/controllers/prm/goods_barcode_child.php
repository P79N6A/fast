<?php
/**
 * 商品子条码相关业务
 * @author dfr
 *
 */
require_lib ( 'util/web_util', true );
class Goods_barcode_child {
    
    	function do_list(array & $request, array & $response, array & $app) {
            

        }
	    function do_delete(array &$request, array &$response, array &$app) {
	    	if(!empty($request['ids'])){
	    		$id_array = explode(",", $request['ids']);
	    		foreach($id_array as $val){
	    			$ret = load_model('prm/GoodsBarcodeChildModel')->delete($val);	
	    		
	    		}
	    	}else{
	        	$ret = load_model('prm/GoodsBarcodeChildModel')->delete($request['barcode_id']);
	    	}
	        exit_json_response($ret);
	    }
    
    function import(array &$request, array &$response, array &$app) {

    }
    
	function import_action(array &$request, array &$response, array &$app) {

		$app['fmt'] = 'json';
		$file = $request['url'];
		if(empty($file)){
			$response = array(
					'status' => 0,
					'type' => '',
					'msg' => "请先上传文件"
			);
		}
		$res = load_model('prm/GoodsBarcodeChildModel')->add($file);
		

        $response = array('message'=>$res['message'], 'status'=>$res['status']);
    }

    function import_upload(array &$request, array &$response, array &$app) {
//        $app['fmt'] = 'json';
//        $files = array();
//        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/";
//         $ret = check_ext_execl();
//        if($ret['status']<0){
//            $response = $ret;
//            return ;
//        }
//        $fileInput = 'fileData';
//        $dir = ROOT_PATH.'webefast/uploads/';
//        $type = $_POST['type'];
//
//        $isExceedSize = false;
//        $files_name_arr = array($fileInput);
//        foreach($files_name_arr as $k=>$v){
//            $pic = $_FILES[$v];
//            $isExceedSize = $pic['size'] > 500000;
//            if(!$isExceedSize){
//                if(file_exists($dir.$pic['name'])){
//                    @unlink($dir.$pic['name']);
//                }
//                // 解决中文文件名乱码问题
//                //$pic['name'] = iconv('UTF-8', 'GBK', $pic['name']);
//                $result = move_uploaded_file($pic['tmp_name'], $dir.$pic['name']);
//                $files[$k] = $url.$dir.$pic['name'];
//            }
//        }
//        if(!$isExceedSize && $result){
//            $response = array(
//                'status' => 1,
//                'type' => $type,
//                'name' => $_FILES[$fileInput]['name'],
//                'url' => $dir.$_FILES[$fileInput]['name']
//            );
//        }else if($isExceedSize){
//            $response = array(
//                'status' => 0,
//                'type' => $type,
//                'msg' => "文件大小超过500kb！"
//            );
//        }else{
//            $response = array(
//                'status' => 0,
//                'type' => $type,
//                'msg' => "未知错误！".$result
//            );
//        }
//                 set_uplaod($request, $response, $app);
//                 
                 
                 
        set_uplaod($request, $response, $app);
            	$ret = check_ext_execl();
        if($ret['status']<0){
            exit_json_response($ret);
        }
        
    	$ret = load_model('pur/OrderRecordModel')->import_upload($request,$_FILES);
    	$response = $ret;
                 
                 
                 
    }

}

?>
