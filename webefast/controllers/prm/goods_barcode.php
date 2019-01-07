<?php

require_lib('util/web_util', true);

class goods_barcode {

    function do_list(array & $request, array & $response, array & $app) {
        //类别 start
        $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
        /*
          //品牌  start
          $response['brand'] = $this->get_brand();
          //年份 start
          $response['year'] = $this->get_year();

          //季节 start
          $response['season'] = $this->get_season();
         */
        //spec1别名
        $arr = array('goods_spec1', 'clodop_print', 'barcode_template');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        $response['new_clodop_print'] = isset($arr_spec1['clodop_print']) ? $arr_spec1['clodop_print'] : '';
        $response['barcode_template'] = isset($arr_spec1['barcode_template']) ? $arr_spec1['barcode_template'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        $response['is_edit'] = load_model('sys/PrivilegeModel')->check_priv('prm/goods_barcode/edit_barcode');
    }

    function serach(array & $request, array & $response, array & $app) {
        
    }

    function edit_barcode_gb_code(array & $request, array & $response, array & $app) {
        if (!load_model('sys/PrivilegeModel')->check_priv('prm/goods_barcode/edit_barcode')) {
            $response = load_model('prm/GoodsBarcodeModel')->format_ret(-1, '', '无权限');
            return;
        }

        $request['barcode'] = trim($request['barcode']);
        $request['sku'] = trim($request['sku']);
        $request['gb_code'] = trim($request['gb_code']);

        $check = false;
        if ($request['barcode'] == '' && $request['is_check'] == 1) {
            $check = load_model('prm/GoodsBarcodeModel')->check_is_use_barcode($request['sku']);
        }
        if (!$check) {
            $response = load_model('prm/GoodsBarcodeModel')->save_barcode_gb_code_by_sku($request['barcode'], $request['sku'], $request['gb_code']);
        } else {
            $response = load_model('prm/GoodsBarcodeModel')->format_ret(-2, '', '此条码已在业务单据中使用，确认清空吗？清空后历史数据将无法查询！');
        }
    }

    //品牌数据
    function get_brand() {
        //品牌  start
        $arr_brand = load_model('prm/BrandModel')->get_brand();
        $key = 0;
        foreach ($arr_brand as $value) {
            $arr_brand[$key][0] = $value['brand_code'];
            $arr_brand[$key][1] = $value['brand_name'];
            $key++;
        }
        return $arr_brand;
    }

    //季节
    function get_season() {
        $arr_season = load_model('base/SeasonModel')->get_season();
        $key = 0;
        foreach ($arr_season as $value) {
            $arr_season[$key][0] = $value['season_code'];
            $arr_season[$key][1] = $value['season_name'];
            $key++;
        }
        return $arr_season;
    }

    //年份
    function get_year() {
        $arr_year = load_model('base/YearModel')->get_year();
        $key = 0;
        foreach ($arr_year as $value) {
            $arr_year[$key][0] = $value['year_code'];
            $arr_year[$key][1] = $value['year_name'];
            $key++;
        }
        return $arr_year;
    }

    //删除
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('prm/GoodsBarcodeModel')->delete($request['sku_id']);
        exit_json_response($ret);
    }

    //删除
    function get_print_barcode_data(array & $request, array & $response, array & $app) {
        $response = load_model('prm/GoodsBarcodeModel')->get_print_barcode_data($request);
    }

    //打印条码
    function print_barcode_clodop(array & $request, array & $response, array & $app) {
        
    }

}
