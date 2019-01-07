<?php
class NewFile {
    /**
     * 通用文件上传处理
     * // TODO 文件上传待完善，无效文件通过某种规则来定期清理
     */
    function upload(array & $request,array & $response,array & $app){
        $path = $request['path'];
    	$ret = check_ext_execl();
        if($ret['status']<0){
            exit_json_response($ret);
        }
	    if (defined('CLOUD') && CLOUD) {
		    $kh_code = $GLOBALS['context']->get_session("kh_code");
		    $path.= $kh_code.'/';
	    }

        $result = false;
//        $ym = date('Y/m/');
        $dir = $path;
        if (!file_exists($dir)) {
            if(!mkdir($dir, 0700, true)) {
                CTX()->log_error($dir.' mkdir failed!');
            }
        }
        foreach ($_FILES as $_name => $_file) {
            $filename = $_file['name']; 
            //$filename = iconv('UTF-8', 'GBK', $filename);
            //$result = move_uploaded_file($_file['tmp_name'], $dir.$filename);
            $filepathlist=explode('.',$filename);
            $newfilename=uniqid().'.'.$filepathlist[1]; 
            $result = move_uploaded_file($_file['tmp_name'], $dir.$newfilename);  
        }
        $relative_path = $ym.$newfilename;

	    if (defined('CLOUD') && CLOUD) {
		    $kh_code = $GLOBALS['context']->get_session("kh_code");
		    $relative_path = $kh_code.'/'.$ym.$newfilename;
	    }

        $status = $result ? 1 : 0;
        $ret = array('status'=>$status, 'data'=>array('path'=>json_encode(array($relative_path, $filename))), 'message'=>'');
        exit_json_response($ret);
    }
    
    function img(array & $request,array & $response,array & $app){
        $file = empty($request['f']) ? 'no_pic_62x62.jpg' : $request['f'];
        $path = CTX()->get_app_conf('file_upload_path').$file;
        if (!file_exists($path)) {
        	exit_json_response('no_img');
        }
        ob_clean();
        $res = getimagesize($path);
        
        $type = $res['mime'];
        
        $data = fread(fopen($path,'rb'), filesize($path));
        header("content-type:{$type}");
        echo $data;
        exit;
    }
    
    function download_upload_file(array & $request,array & $response,array & $app) {
        if (empty($request['path']) || empty($request['name'])) {
            die('params [path, name] cannot be empty!');
        }
        $dir = CTX()->get_app_conf('file_upload_path');
        $file = $dir.$request['path'];
        if (!file_exists($file)) {
        	die('not exists!');
        }
        
        require_lib('util/download_util');
        force_download($request['name'], file_get_contents($file));
        exit;
    }
}
