<?php

/**
 * 订单运费核销明细
 * @author FBB
 */
class order_express_detail {

    function do_list(array & $request, array & $response, array & $app) {
        
    }

    /**
     * @todo 新增快递对账单
     */
    function add(array & $request, array & $response, array & $app) {
        $response['store'] = load_model('base/StoreModel')->get_select(0);
        $response['data']['dz_code'] = load_model('acc/OrderExpressDzModel')->create_dz_num();
    }

    function do_add(array & $request, array & $response, array & $app) {
        $dz_data = get_array_vars($request, array('dz_code', 'dz_month', 'store_code'));
        $ret = load_model('acc/OrderExpressDzModel')->insert_dz_data($dz_data);
        exit_json_response($ret);
    }

    /**
     * @todo 查看运费核销明细
     */
    function view(array & $request, array & $response, array & $app) {
        $response['data'] = load_model('acc/OrderExpressDzModel')->view($request['dz_code']);
        $response['detail'] = load_model('acc/OrderExpressDzDetailModel')->get_detail_data_by_code($request['dz_code']);
    }

    /**
     * @todo 刷新称重数据
     */
    function refesh_record(array & $request, array & $response, array & $app) {
        $data = get_array_vars($request, array('dz_code', 'dz_month', 'store_code'));
        $ret = load_model('acc/OrderExpressDzDetailModel')->refresh_record($data);
        exit_json_response($ret);
    }

    /**
     * @todo 导入快递运费
     */
    function import(array & $request, array & $response, array & $app) {
        $response['express'] = load_model('acc/OrderExpressDzDetailModel')->get_express_name_by_dz_code($request['dz_code']);
        $response['dz_code'] = $request['dz_code'];
    }

    function do_import(array & $request, array & $response, array & $app) {
        $ret = check_ext_execl();
         set_uplaod($request, $response, $app);
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
    }

    function do_import_detail(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array('status' => -1, 'type' => '', 'msg' => "请先上传文件");
        }
        $ret = load_model('acc/OrderExpressDzDetailModel')->import_detail($request);
        $response = $ret;
    }

    /**
     * @todo 手动核销
     */
    function do_hx(array & $request, array & $response, array & $app) {
        $response = load_model('acc/OrderExpressDzDetailModel')->get_detail_by_id($request['detail_dz_id']);
    }

    function opt_hx(array & $request, array & $response, array & $app) {
        $ret = load_model('acc/OrderExpressDzDetailModel')->opt_hx($request);
        exit_json_response($ret);
    }

    /**
     * @todo 导出数据
     */
    function export_csv_list(array & $request, array & $response, array & $app) {
        $detail_result = load_model('acc/OrderExpressDzDetailModel')->get_detail_by_page($request);
        $detail_result = $detail_result['data'];
        $strs = "核销状态,配送方式,快递单号,称重运费,重量,快递运费,仓库,订单编号,省份,城市,地区,街道,详细地址,发货时间\n";
        $str = iconv('utf-8', 'gbk', $strs);
        foreach ($detail_result as $value) {
            $value['hx_status'] = iconv('utf-8', 'gbk', $value['hx_status_type']);
            $value['express_name'] = iconv('utf-8', 'gbk', $value['express_name']);
            $value['store_name'] = iconv('utf-8', 'gbk', $value['store_name']);
            $value['province_name'] = iconv('utf-8', 'gbk', $value['province_name']);
            $value['city_name'] = iconv('utf-8', 'gbk', $value['city_name']);
            $value['district_name'] = iconv('utf-8', 'gbk', $value['district_name']);
            $value['street_name'] = iconv('utf-8', 'gbk', $value['street_name']);
            $value['receiver_addr'] = iconv('utf-8', 'gbk', $value['receiver_addr']);
            $value['express_money'] = sprintf("%.2f", $value['express_money']);
            $value['weigh_express_money'] = sprintf("%.2f", $value['weigh_express_money']);
            $str .= $value['hx_status'] . "," . $value['express_name'] . "," . "\t" . $value['express_no'] . "\t," . $value['weigh_express_money'] . "," . $value['real_weigh'] .
                    "," . $value['express_money'] . "," . $value['store_name'] . ",\t" . $value['sell_record_code'] ."\t,\t". $value['province_name'] .",\t".$value['city_name'] . ",\t".
                    $value['district_name'] . ",\t".$value['street_name'] . ",\t". $value['receiver_addr'] . "," . $value['delivery_time'] . "\n"; //用引文逗号分开  
        }
        $filename = 'YFHXMX' . date('Ymd') . '.csv'; //设置文件名  
        $this->export_csv($filename, $str); //导出  
    }

    function export_csv($filename, $data) {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
        die;
    }


    function get_express_by_dz_code(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('dz_code', 'express_code'));
        $ret = load_model('acc/OrderExpressDzDetailModel')->get_express_by_dz_code($params);
        $response = array('rows' => $ret);
    }

}
