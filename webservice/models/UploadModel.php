<?php

/**
 * 上传文件
 */
require_lib('comm_util', true);
require_lib('img/ImageUtil', true);

class UploadModel {

    function upload_images($request, $upload_files) {

        $conf = require_conf('upload');

//        $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/";
        $fileInput = 'fileData';

        $dir = ROOT_PATH . 'webservice/' . $conf['path']['upload_path'] . '/';

        if ($conf['path']['path_type'] == 1) {
            $dir = ROOT_PATH . $conf['path']['upload_path'] . '/';
        } else if ($conf['path']['path_type'] == 2) {
            $dir = $conf['path']['upload_path'] . '/';
        }
        $img_url = $conf['path']['img_url'];

        if (!file_exists($dir)) {
            mkdir($dir);
        }

        $this->create_dir($dir);

        $type = $request['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        $is_max = 0;
        $is_file_type = 0;
        $file_type = array('jpg', 'png', 'gif');
        $upload_max_filesize = 2097152;
        foreach ($files_name_arr as $k => $v) {
            $pic = $upload_files[$v];
            if (!isset($pic['tmp_name']) || empty($pic['tmp_name'])) {
                $is_max = 1;
                continue;
            }
            $file_ext = $this->get_file_extension($pic['name']);
            if (!in_array($file_ext, $file_type)) {
                $is_file_type = 1;
                continue;
            }
            $isExceedSize = $pic['size'] > $upload_max_filesize;
            if (!$isExceedSize) {
                if (file_exists($dir . $pic['name'])) {
                    @unlink($dir . $pic['name']);
                }
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
                $result = move_uploaded_file($pic['tmp_name'], $dir . $new_file_name);

                //生成缩略图
                $thumb_file_name = 'thumb_' . $new_file_name;
                $thumb_dir = $dir . 'thumb/';
                if (!file_exists($thumb_dir)) {
                    mkdir($thumb_dir);
                }
                $thumb_obj = new ImageUtil();
                $thumb_obj->thumb($dir . $new_file_name, $thumb_dir . $thumb_file_name, 50, 50);
            }
        }
        //img_url
        $img_path = $dir . $new_file_name;
        $img_path = str_replace(ROOT_PATH, $img_url, $img_path);

        $thumb_path = $thumb_dir . $thumb_file_name;
        $thumb_path = str_replace(ROOT_PATH, $img_url, $thumb_path);

        if ($is_max) {
            return array(
                'status' => 0,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'msg' => str_replace('{0}', substr(ini_get('upload_max_filesize'), 0, -1) * 1024, lang('upload_msg_maxSize'))
            );
        } else if ($is_file_type) {
            return array(
                'status' => 0,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'msg' => str_replace('{0}', implode(',', $file_type), lang('upload_msg_ext'))
            );
        } else if (!$isExceedSize && $result) {
            return array(
                'status' => 1,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'url' => $img_path,
                'thumb_url' => $thumb_path,
            );
        } else if ($isExceedSize) {
            return array(
                'status' => 0,
                'type' => $type,
                'msg' => str_replace('{0}', $upload_max_filesize / 1024, lang('upload_msg_maxSize'))
            );
        } else {
            return array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！" . $result
            );
        }
    }

    /**
     * 方法名       get_file_extension
     * 功能描述     获取文件扩展名
     * @author      BaiSon PHP R&D
     * @param       string $file
     * @return      string $file_ext_name [扩展名]
     */
    function get_file_extension($file) {
        $temp_arr = explode('.', $file);
        $file_ext = array_pop($temp_arr);
        $file_ext = trim($file_ext);
        $file_ext = strtolower($file_ext);
        return $file_ext;
    }

    function update_path($path) {
        $config_file = ROOT_PATH . 'webservice/conf/upload.conf.php';
        $con = "<?php\r\nreturn array(\r\n";
        $con .= "\t'path'=>array(\r\n";
        $con .= "\t'upload_path' => '{$path}',\r\n";
        $con .= "\t),\r\n);\r\n?>";
        if (file_put_contents($config_file, $con)) {
            return array(
                'status' => 1,
                'data' => '',
                'message' => '修改成功'
            );
        } else {
            return array(
                'status' => -1,
                'data' => '',
                'message' => '修改失败'
            );
        }
    }

    private function create_dir(&$dir) {
        if (CTX()->get_session('kh_id') !== NULL) {
            $dir .=CTX()->get_session('kh_id') . "/";
            if (!file_exists($dir)) {
                mkdir($dir);
            }
        }
        //增加按照日期生成目录
        $dir .=date('Ymd') . "/";
        if (!file_exists($dir)) {
            mkdir($dir);
        }
    }

}
