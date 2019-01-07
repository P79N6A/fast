<?php

require_lib('util/web_util', true);

class file {

    function get_file(array &$request, array &$response, array &$app) {
        if (!isset($request['name'])) {
            $app['fmt'] = "json";
            $response = array('status' => -1);
            return ;
        }
        $type = !isset($request['type']) ? 0 : $request['type'];
        $path_conf = require_conf('filepath');
        $path = isset($path_conf[$type]) ? $path_conf[$type] . '/' : '';
        $name = $request['name'];
        $file_path = ROOT_PATH . CTX()->app_name . "/data/" . $path . $name;
        
 
        //token
         $user_token = create_user_token($request['name']);
         if(!isset($request['token'])||$user_token!=$request['token'])  {
            $app['fmt'] = "json";
            $response = array('status' => -1,'message'=>'验证失败！');
            return ;
         }
            
            
        if(isset($request['down_name'])){
            list($f_name,$type) = explode(".", $name);
            $down_name = $request['down_name'].".".$type; 
        }else{
            $down_name = $name ;
        }
        
        if (is_file($file_path)) {
            header('Content-Type: application/octet-stream');
            $filesize = filesize($file_path);
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            header('Content-Transfer-Encoding: binary');
            header('Content-Encoding: none');
            header('Content-type: application/force-download');
            header('Content-length: ' . $filesize);
            header('Content-Disposition: attachment; filename="' . $down_name . '"');
            readfile($file_path);
            die;
        } else {
            $app['fmt'] = "json";
            $response = array('status' => -1);
        }
    }

}

?>
