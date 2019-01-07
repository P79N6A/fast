<?php

class execl_csv {

    function read_csv($file, $start_line = 0, $key_arr = array(), $encode_key = array()) {

        $is_utf8 = $this->detect_encoding($file) == 'UTF-8' ? true : false;
        $file = fopen($file, "r");

        if ($is_utf8) {
            $encode_key = array();
        }
        $i = 0;

        $csv_data = array();
        $fail_num = 0;
        try {
            if ($file) {
                while (!feof($file)) {
                    $row = fgetcsv($file);
                    if ($i >= $start_line) {
                        if (!empty($row)) {
                            $csv_data[] = $this->change_key_row($row, $key_arr, $encode_key);
                            $fail_num = 0;
                        } else {
                            $fail_num++;
                        }
                    }
                    if ($fail_num > 100) {
                        break;
                    }
                    $i++;
                }
            }
        } catch (Exception $ex) {

        }
        fclose($file);
        return $csv_data;
    }

    private function change_key_row($row, $key_arr, $encode_key) {
        $new_row = array();
        if (!empty($key_arr) || !empty($encode_key)) {
            foreach ($row as $key => $val) {
                if (isset($key_arr[$key])) {
                    $r_k = $key_arr[$key];
                    $new_row[$r_k] = in_array($r_k, $encode_key) ?
                            trim(iconv('GBK', 'UTF-8', $val)) : trim($val);
                }
            }
        } else {
            $new_row = & $row;
        }

        return $new_row;
    }

    function detect_encoding($file) {
        $list = array('GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1');
        $str = file_get_contents($file);
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($str, $item, $item);
            if (md5($tmp) == md5($str)) {
                return $item;
            }
        }
        return null;
    }

    function import_upload($upload_files, $type = '') {


        $fileInput = 'fileData';
        $dir = ROOT_PATH . 'webefast/uploads/';
        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        $is_max = 0;
        $is_file_type = 0;
        $file_type = array('csv', 'xlsx', 'xls');
        $upload_max_filesize = 2097152;
        foreach ($files_name_arr as $k => $v) {
            $pic = $upload_files[$v];
            if (!isset($pic['tmp_name']) || empty($pic['tmp_name'])) {
                $is_max = 1;
                continue;
            }
            $file_ext = get_file_extension($pic['name']);
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
                if (true == $result && ('xlsx' == $file_ext || 'xls' == $file_ext)) {
                    $result = $this->excel2csv($dir . $new_file_name, $file_ext);
                    $new_file_name = str_replace('.' . $file_ext, '.csv', $new_file_name);
                }
            }
        }
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
                'url' => $dir . $new_file_name
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
     *
     * 方法名       excel2csv
     *
     * 功能描述     excel转换csv文件
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-24
     * @param       string $file
     * @param       string $extends
     *
     * @return      string $data
     */
    function excel2csv($file, $extends) {
        require_lib('PHPExcel', true);
        try {

            $PHPExcel = PHPExcel_IOFactory::load($file);

            $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'CSV');
            $objWriter->setUseBOM(true);
            $objWriter->setPreCalculateFormulas(false);
            $objWriter->save(str_replace('.' . $extends, '.csv', $file));
        } catch (Exception $e) {

            return false;
        }

        return true;
    }

    /**
     * 导出信息
     * @param array $fail_top 表头
     * @param array $error_msg 信息数组
     * @return string 导出文件地址
     */
    function create_fail_csv_files($fail_top, $error_msg, $filename) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $val) {
            $file_str .= implode("\t,", $val) . "\r\n";
        }
        $filename = md5($filename . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

}
