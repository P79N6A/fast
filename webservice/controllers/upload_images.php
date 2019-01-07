<?php

class upload_images {

    function do_list(array & $request, array & $response, array & $app) {
        $app['page'] = NULL;
    }


    function upload_img(array & $request, array & $response, array & $app) {
        $ret = $this->check_ext_image();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('/UploadModel')->upload_images($request, $_FILES);
        $response = $ret;
    }

    function upload(array & $request, array & $response, array & $app) {
        $app['page'] = 'NULL';
        $ret = $this->check_ext_image();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('/UploadModel')->upload_images($request, $_FILES);
        $response = $ret;
    }


    function update_path(array & $request, array & $response, array & $app) {
        $ret = load_model('/UploadModel')->update_path($request['upload_path']);
        $response = $ret;
    }

    function check_ext_image() {
        $ext_arr = array('jpg', 'png', 'gif');
        if (!isset($_FILES['fileData']['name']) || empty($_FILES)) {
            return array('status' => 1);
        }

        $file_name = $_FILES['fileData']['name'];

        $arr = explode('.', $file_name);
        $count = count($arr);
        $is_check = true;
        if ($count < 2) {
            $is_check = false;
        } else {
            $ext = $arr[$count - 1];
            $ext = strtolower($ext);
            if (!in_array($ext, $ext_arr)) {
                $is_check = false;
            }
        }

        if ($is_check === false) {
            $ret = array('status' => -1, 'data' => '', 'message' => '文件类型只能为.jpg,.png,.gif');
            return $ret;
        }
        $ret = array('status' => 1);
        return $ret;
    }

}
