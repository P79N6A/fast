<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('api/sys/ApiGoodsModel', true);
require_model("sys/SysTaskModel");

class goods {

    function index(array & $request, array & $response, array & $app) {
        //导入商品增值服务
        $response['service_export_api_goods'] = load_model('common/ServiceModel')->check_is_auth_by_value('export_api_goods');
        $presell_plan = load_model('sys/SysParamsModel')->get_val_by_code('presell_plan');
        $response['presell_plan'] = $presell_plan['presell_plan'];
        //过滤唯品会店铺
        $shop_info = load_model('base/ShopModel')->get_purview_shop();
        $shop_code_arr = array_column($shop_info, 'shop_code');
        $weipinhui_shop = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('weipinhui');
        $weipinhui_shop_code_arr = array_column($weipinhui_shop, 'shop_code');
        $filter_shop_code = array_diff($shop_code_arr, $weipinhui_shop_code_arr);
        $response['shop_code'] = array();
        foreach ($shop_info as $shop) {
            if (in_array($shop['shop_code'], $filter_shop_code)) {
                $response['shop_code'][] = $shop;
            }
        }
        $app['tpl'] = "api/sys/goods/index";
    }

    /**
     * 通过类型获取明细
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_sku_list_by_item_id(array & $request, array & $response, array & $app) {
        $ret = load_model('oms/ApiGoodsSkuModel')->get_list_by_item_id($request['goods_from_id'], array());
        $dataset = $ret['data'];

        exit_table_data_json($dataset['data'], $dataset['filter']['record_count']);
    }

    function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('oms/ApiGoodsModel')->update_active($arr[$request['type']], $request['api_goods_id']);
        exit_json_response($ret);
    }

    function down(array &$request, array &$response, array &$app) {
        $app['tpl'] = "api/sys/goods/down";
        if($request['sale_channel']=='weipinhui'&&$request['type']=='jit'){
            //唯品会jit商品下载（唯品会商品列表）
            $response['shop']=load_model('base/ShopModel')->get_wepinhuijit_shop();
            $response['sale_channel'][0]=array('weipinhui','唯品会');
        }else{
            $sale_channel = load_model('base/SaleChannelModel')->get_select();
            $sale_channel_arr = array();
            foreach ($sale_channel as $key => $value) {
                if ($value[0] != 'houtai' && $value[0] != 'weipinhui' && $value[0] != 'yoho') {
                    $sale_channel_arr[] = $value;
                }
            }
            $response['sale_channel'] = $sale_channel_arr;
            $sale_channel_code = $sale_channel_arr[0][0];
            $response['shop'] = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code($sale_channel_code);
        }
    }

    function down_action(array &$request, array &$response, array &$app) {
        $new_arr = array();
        $start_time = $request['parameter']['start_time'];
        $end_time = date("Y-m-d H:i:s", strtotime($request['parameter']['end_time'] . " +1 day"));

        $time_arr = split_time($start_time, $end_time, 240000);
        $shop_arr = explode(",", $request['parameter']['shop_code']);
        foreach ($shop_arr as $shop) {
            foreach ($time_arr as $time) {
                $time['shop_code'] = $shop;
                $new_arr[] = $time;
            }
        }
        $response = $new_arr;
    }

    function upload(array &$request, array &$response, array &$app) {
        $response['shop_api'] = get_shop_api_list();
        $app['tpl'] = "api/sys/goods/upload";
    }

    function get_goods_page(array &$request, array &$response, array &$app) {
        $mdl_api_goods = new ApiGoodsModel();
        $mdl_sys_task = new SysTaskModel();
        $param = array();
        $message = "";
        $type = 1;
        if (isset($request['platform'])) {
            $param['platform'] = $request['platform'];
        }
        if (isset($request['shop_code'])) {
            $param['shop_code'] = $request['shop_code'];
        }
        if (isset($request['status']) && $request['status'] == "onsale") {
            $param['status'] = 1;
        }
        if (isset($request['status']) && $request['status'] == "inv") {
            $param['status'] = 0;
        }
        if (isset($request['goods_inv_status']) && $request['goods_inv_status'] == "change") {
            $param['goods_inv_status'] = $request['goods_inv_status'];
        }
        if (isset($request['type'])) {
            $type = $request['type'];
        }
        if ($type == 1) {
            $num = $mdl_api_goods->get_sku_num_or_data($param, $type);
            $response['page'] = ceil($num / 100);
        } else {
            $param['page'] = $request['page'];
            $start = ($request['page'] - 1) * 100;
            $end = $request['page'] * 100;
            $message .= "正在同步" . $start . "-" . $end . "个商品<br />";
            $goods_id_str = "";

            $goods_list = $mdl_api_goods->get_sku_num_or_data($param, $type);

            foreach ($goods_list as $goods) {
                $goods_id_str .= $goods['api_goods_id'] . ",";
            }
            $goods_id_str = substr($goods_id_str, 0, strlen($goods_id_str) - 1);

            $ret = $mdl_sys_task->sync_goods_inv($goods_id_str);
            $message .= $ret['message'];
        }
        $response['message'] = $message;
        exit_json_response($response);
    }

    function upload_goods_inv(array &$request, array &$response, array &$app) {
        
    }

    //库存更新
    function sync_goods_inv(array &$request, array &$response, array &$app) {
        $ret = load_model('api/sys/ApiGoodsModel')->sync_goods_inv($request);
        exit_json_response($ret);
    }

    //淘分销库存更新
    function fenxiao_sync_goods_inv(array &$request, array &$response, array &$app) {
        $ret = load_model('api/sys/FxGoodsModel')->fenxiao_sync_goods_inv($request['id']);
        exit_json_response($ret);
    }

    /**
     * 淘分销批量库存同步
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function multi_fenxiao_sync_goods_inv(array &$request, array &$response, array &$app) {
        $ret = load_model('api/sys/FxGoodsModel')->multi_fenxiao_sync_goods_inv($request['pid']);
        exit_json_response($ret);
    }

    //下载
    function down_goods(array &$request, array &$response, array &$app) {
        $ret = load_model('api/sys/ApiGoodsModel')->down_goods($request);
        exit_json_response($ret);
    }

    //下载进度
    function down_goods_check(array &$request, array &$response, array &$app) {
        $ret = load_model('api/sys/ApiGoodsModel')->down_goods_check($request);
        exit_json_response($ret);
    }

    //联动效果
    function get_shop_by_sale_channel(array &$request, array &$response, array &$app) {
        $sale_channel_code = $request['sale_channel_code'];
        $ret = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code($sale_channel_code);
        exit_json_response($ret);
    }

    function show_sku_quantity_update(array &$request, array &$response, array &$app) {
      
        
        if(isset(  $request['type'] ) &&   $request['type']==1 ){
            $response['sku_info'] = load_model('api/FxTaoBaoProductModel')->get_sku_info_by_id($request['id']);
        }else{
            $response['sku_info'] = load_model('api/sys/ApiGoodsModel')->get_sku_info_by_id($request['id']);
        }
        
    }
    function import(array &$request, array &$response, array &$app){
        $app['tpl'] = "api/sys/goods/goods_import";
    }
    function import_upload(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $files = array();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/";
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $fileInput = 'fileData';
        $dir = ROOT_PATH . 'webefast/uploads/';
        $type = $_POST['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        foreach ($files_name_arr as $k => $v) {
            $pic = $_FILES[$v];
            $isExceedSize = $pic['size'] > 500000;
            if (!$isExceedSize) {
                if (file_exists($dir . $pic['name'])) {
                    @unlink($dir . $pic['name']);
                }
                // 解决中文文件名乱码问题
                //$pic['name'] = iconv('UTF-8', 'GBK', $pic['name']);
                $result = move_uploaded_file($pic['tmp_name'], $dir . $pic['name']);
                $files[$k] = $url . $dir . $pic['name'];
            }
        }
        if (!$isExceedSize && $result) {
            $response = array(
                'status' => 1,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'url' => $dir . $_FILES[$fileInput]['name']
            );
        } else if ($isExceedSize) {
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "文件大小超过500kb！"
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
    function import_action(array &$request, array &$response, array &$app) {

        $app['fmt'] = 'json';
        require_once ROOT_PATH . 'lib/PHPExcel.php';
        $excelType = pathinfo($request['url'], PATHINFO_EXTENSION) == 'xlsx' ? 'Excel2007' : 'Excel5';
        $objReader = PHPExcel_IOFactory::createReader($excelType);
        $objPHPExcel = $objReader->load($request['url']);
        $arrExcel = $objPHPExcel->getActiveSheet()->toArray();
        //移除第一行
        array_shift($arrExcel);

        $success = 0;
        $fail_num = 0;
        $faild = array();
        if(count($arrExcel)>1000){
            return $response = array('status' => '-1', 'message' => '导入的数据超过1000条');
        }
        $m = new ApiGoodsModel();
        foreach ($arrExcel as $k => $v) {
            if (empty($v[0]) && empty($v[1]) && empty($v[2]) && empty($v[3])) {
                continue;
            }
            $r = $m->goods_import($v[0], $v[1], $v[2],$v[3]);
            if ($r['status'] == '1') {
                $success++;
            } else {
                //faild .= sprintf("%s,%s,%s,%s\n<br>", $v[0], $v[1], $v[2], $r['message']);
                $faild[$k+1] = $r['message'];
                $fail_num ++;
            }
        }
        $message = '导入成功：' . $success . '条';
        $status = 1;
        if (!empty($faild)) {
            $status = -1;
            $message .=',' . '导入失败:' . $fail_num . '条';
            $fail_top = array('错误的行号', '错误信息');
            $filename = 'sys_goods';
            $file_name = $m->create_import_fail_files($faild, $fail_top, $filename);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $response = array('status' => $status, 'success' => $success, 'message' => $message);
    }


    /**
     * 唯品会批量库存同步
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function weipinhui_multi_sync_goods_inv(array &$request, array &$response, array &$app) {
        $ret = load_model('api/sys/ApiGoodsModel')->weipinhui_multi_sync_goods_inv($request);
        exit_json_response($ret);
    }


    /**
     * 验证自动服务
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function check_schedule_value(array &$request, array &$response, array &$app) {
        $code_arr = array('weipinhuijit_getOccupiedOrders_cmd');
        $code_info = load_model('sys/ScheduleModel')->get_val_by_code($code_arr);
        if ($code_info['weipinhuijit_getOccupiedOrders_cmd']['status'] == 0) {
            $ret = array('status' => -1, 'data' => '', 'message' => '');
        } else {
            $ret = array('status' => 1, 'data' => '', 'message' => '');
        }
        exit_json_response($ret);
    }

}
