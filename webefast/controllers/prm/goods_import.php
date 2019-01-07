<?php

/**
 * 商品控制器相关业务
 * @author dfr
 *
 */
require_lib('util/web_util', true);
require_lib('comm_util', true);

class goods_import {

    function do_list(array & $request, array & $response, array & $app) {
        //是否企业版大于0为企业版 旗舰版
        $response['product_version'] = load_model('sys/SysAuthModel')->product_version_no();
        $response['spec_power'] = load_model('sys/SysParamsModel')->get_val_by_code('spec_power');
    }

    function base_spec1(array &$request, array &$response, array &$app) {
        //print_r($request);
        //exit;
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $response = load_model('prm/GoodsImportModel')->import_base_spec1($file);
    }

    function base_barcode(array &$request, array &$response, array &$app) {
        //print_r($request);
        //exit;
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        //$response = load_model('prm/GoodsImportModel')->import_base_barcode($file);
        $response = load_model('prm/GoodsImportModel')->good_spec($file);
    }

    //商品导入
    function base_goods(array &$request, array &$response, array &$app) {
        //print_r($request);
        //exit;
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $response = load_model('prm/GoodsImportModel')->import_base_goods($file);
    }

    function base_goods_barcode_property(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        $type = $request['type'] == 'code' ? 'code' : 'name';
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $response = load_model('prm/GoodsImportModel')->import_base_goods_barcode_property($file, $type);
    }

    //商品导入
    function base_goods_property(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $is_cover = isset($request['is_cover']) ? $request['is_cover'] : 1;
        $response = load_model('prm/GoodsImportModel')->import_goods_property($file, $is_cover);
    }

    function base_spec(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $is_cover = isset($request['is_cover']) ? $request['is_cover'] : 1;
        $type = isset($request['type']) ? $request['type'] : 1;

        $response = load_model('prm/GoodsImportModel')->import_spec($file, $is_cover, $type);
    }

    //商品条形码信息价格导入
    function base_goods_barcode_price(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }

        $response = load_model('prm/GoodsImportModel')->import_goods_barcode_price($file);
        return $response;
    }

    /**
     * 套餐商品导入
     */
    function goods_combo(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }

        $response = load_model('prm/GoodsImportModel')->import_goods_combo($file);
        return $response;
    }

    /**
     * 组装商品导入
     */
    function goods_diy(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }

        $response = load_model('prm/GoodsImportModel')->import_goods_diy($file);
        return $response;
    }

    //混合模板
    public function hunhemoban(array & $request, array & $response, array & $app) {
        $str = load_model('prm/GoodsImportModel')->hunhe_bie();
        $str = empty($str) || $str == ',' ? '' : ',' . $str;
        $spec = $request['type'] == 'code' ? '代码' : '名称';
        $column = "商品编码*,商品名称*,商品简称,商品分类*#,商品品牌*#,商品季节#,商品年份#,商品属性,商品状态,商品重量(克),吊牌价,成本价,批发价,进货价,有效期,使用周期,商品描述,规格1{$spec}*,规格2{$spec}*,商品条形码*,商品条形码重量,国标码,商品出厂名称" . $str;

        $column_arr = explode(',', $column);
        $char = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        $num = count($column_arr);
        $not_txt = array('K', 'L', 'M', 'N', 'O', 'P'); //不是文本的列

        $map = array();
        $firstRow = array();
        $firstRow['A1'] = '导入商品列表（*号为必填项，#号表示若匹配不成功会重新建立，开启批次“有效期”、“使用周期” 有效）';
        for ($i = 0; $i < $num; $i++) {
            $k = $char[$i];
            if (in_array($k, $not_txt)) {
                $map[$k] = 0;
            } else {
                $map[$k] = 1;
            }

            $firstRow[$k . '2'] = $column_arr[$i];
        }
        load_model('sys/ExcelExportModel')->export($map, $firstRow, 'hunhedaoru', true);
    }

    /**
     * csv文档下载
     * 根据传递的code, 取到相应的模版文件, 读取并输出
     */
    public function tplDownload(array & $request, array & $response, array & $app) {
        //$app['fmt'] = 'json';
        $M = load_model('sys/ExcelImportModel');
        $code = $request['code'];
        $excel = $M->get_row(array('danju_code' => $code));
        if ($excel['status'] == true && !empty($excel['data']['danju_path'])) {
            //获取url路径
            $path = $excel['data']['danju_path'];
            $path = APP_PATH . 'data' . DIRECTORY_SEPARATOR . $path;
            header("Content-type:application/vnd.ms-excel;charset=utf8");
            header("Content-Disposition:attachment; filename=$code.csv");
            echo file_get_contents($path);
            die();
        } else {
            exit_error_page('出错啦!', '模版文件不存在');
        }
    }

    //上传文件
    function import_upload(array &$request, array &$response, array &$app) {
        set_time_limit(0);
        $app['fmt'] = 'json';
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $files = array();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/";

        $fileInput = 'fileData';
        $dir = ROOT_PATH . 'webefast/uploads/';
        $type = $_POST['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        $is_max = 0;
        $is_file_type = 0;
        $file_type = array('csv', 'xlsx', 'xls');
        $upload_max_filesize = 2097152;
        foreach ($files_name_arr as $k => $v) {
            $pic = $_FILES[$v];
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
                // 解决中文文件名乱码问题
                //$pic['name'] = iconv('UTF-8', 'GBK', $pic['name']);
                //$result = move_uploaded_file($pic['tmp_name'], $dir.$pic['name']);
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
                $result = move_uploaded_file($pic['tmp_name'], $dir . $new_file_name);
                //$files[$k] = $url . $dir . $new_file_name;
                if (true == $result && ('xlsx' == $file_ext || 'xls' == $file_ext)) {
                    $result = $this->excel2csv($dir . $new_file_name, $file_ext);
                    $new_file_name = str_replace('.' . $file_ext, '.csv', $new_file_name);
                }
            }
        }
        if ($is_max) {
            $response = array(
                'status' => 0,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'msg' => str_replace('{0}', substr(ini_get('upload_max_filesize'), 0, -1) * 1024, lang('upload_msg_maxSize'))
            );
        } else if ($is_file_type) {
            $response = array(
                'status' => 0,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'msg' => str_replace('{0}', implode(',', $file_type), lang('upload_msg_ext'))
            );
        } else if (!$isExceedSize && $result) {
            $response = array(
                'status' => 1,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'url' => $dir . $new_file_name
            );
        } else if ($isExceedSize) {
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => str_replace('{0}', $upload_max_filesize / 1024, lang('upload_msg_maxSize'))
            );
        } else {
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！" . $result
            );
        }
        set_uplaod($request, $response, $app);
    }

    /**
     * 方法名       excel2csv
     * 功能描述     excel转换csv文件
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
            $time3 = time();
            $PHPExcel = PHPExcel_IOFactory::load($file);
            $time4 = time();
            $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'CSV');
            $objWriter->setUseBOM(true);
            $objWriter->setPreCalculateFormulas(false);
            $objWriter->save(str_replace('.' . $extends, '.csv', $file));
            $time5 = time();
        } catch (Exception $e) {
            /* return array(
              'status' => -1,
              'data' => array($e->getMessage()),
              'msg' => lang('op_error')
              ); */
            return false;
        }
        /* return array(
          'status' => 1,
          'data' => array('load_excel' => $time4 - $time3, 'write_csv' => $time5 - $time4, 'excel_to_csv' => $time5 - $time3),
          'msg' => lang('op_success')
          ); */
        return true;
    }

    /**
     * 商品价格导入
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array
     */
    function goods_price(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $response = load_model('prm/GoodsImportModel')->import_goods_price($file);
        return $response;
    }

    /**
     * 商品信息更新导入
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function goods_update_ex(array &$request,array &$response,array &$app){
        $str = load_model('prm/GoodsImportModel')->hunhe_bie();
        $str = empty($str) || $str == ',' ? '' : ',' . $str;
        $column = "商品编码*,商品名称,商品简称,商品分类,商品品牌,商品季节,商品年份,商品属性,商品状态,商品重量(克),吊牌价,成本价,批发价,进货价,有效期,使用周期,商品描述,规格1代码*,规格2代码*,商品条形码*,商品条形码重量,国标码,商品出厂名称" . $str;

        $column_arr = explode(',', $column);
        $char = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        $num = count($column_arr);
        $not_txt = array('K', 'L', 'M', 'N', 'O', 'P'); //不是文本的列

        $map = array();
        $firstRow = array();
        $firstRow['A1'] = '导入商品信息更新列表（*号为必填项，开启批次“有效期”、“使用周期” 有效）';
        for ($i = 0; $i < $num; $i++) {
            $k = $char[$i];
            if (in_array($k, $not_txt)) {
                $map[$k] = 0;
            } else {
                $map[$k] = 1;
            }

            $firstRow[$k . '2'] = $column_arr[$i];
        }
        load_model('sys/ExcelExportModel')->export($map, $firstRow, 'goods_update_ex', true);
    }
    /**
     *商品信息导入，混合信息导入（暂时未合并）
     */
    public function goods_import_exec(array &$request,array &$response,array &$app){
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        switch ($request['type']){
            case 'code':
                //混合信息导入按规格导入
                $type = 'code';
                $response = load_model('prm/GoodsImportModel')->import_base_goods_barcode_property($file, $type);
                break;
            case 'name':
                //混合信息导入按名称导入
                $type = 'name';
                $response = load_model('prm/GoodsImportModel')->import_base_goods_barcode_property($file, $type);
                break;
            case 'update':
                $type = 'update';
                //商品信息更新导入
                $response = load_model('prm/GoodsImportModel')->goods_import_exec($file,$type);
                break;
            default :
                $type = 'name';
                $response = load_model('prm/GoodsImportModel')->import_base_goods_barcode_property($file, $type);
        }
    }



}
