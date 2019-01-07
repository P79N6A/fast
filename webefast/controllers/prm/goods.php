<?php

/**
 * 商品控制器相关业务
 * @author dfr
 *
 */
require_lib('util/web_util', true);

class Goods {

    function do_list(array & $request, array & $response, array & $app) {
        $response['user_id'] = CTX()->get_session('user_id');
        //类别 start
        $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
        //品牌  start
        $response['brand'] = $this->get_purview_brand();
        //年份 start
        $response['year'] = $this->get_year();
        //季节 start
        $response['season'] = $this->get_season();
        //商品状态
        $response['state'] = load_model('prm/GoodsModel')->state;
        //商品属性
        $response['prop'] = load_model('prm/GoodsModel')->prop;
        //操作状态
        $response['status'] = load_model('prm/GoodsModel')->status;
        //商品发布增值
        $response['goods_issue'] = load_model('common/ServiceModel')->check_is_auth_by_value('goods_issue');
        //扩展属性
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        if ($property_power) {
            $response['proprety'] = load_model('prm/GoodsPropertyModel')->get_property_val('property_code,property_val_title,property_val');
        }
    }

    function do_list_diy(array & $request, array & $response, array & $app){
        $response['user_id'] = CTX()->get_session('user_id');
        //类别 start
        $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
        //$response['category'] = array();
        //品牌  start
        $response['brand'] = $this->get_purview_brand();
        //年份 start
        $response['year'] = $this->get_year();
        //季节 start
        $response['season'] = $this->get_season();
        //商品状态
        $response['state'] = load_model('prm/GoodsModel')->state;
        //商品属性
        $response['prop'] = load_model('prm/GoodsModel')->prop;
        //操作状态
        $response['status'] = load_model('prm/GoodsModel')->status;
        //商品发布增值
        $response['goods_issue'] = load_model('common/ServiceModel')->check_is_auth_by_value('goods_issue');
        //扩展属性
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        if($property_power) {
            $response['proprety'] = load_model('prm/GoodsPropertyModel')->get_property_val('property_code,property_val_title,property_val');
        }
    }




    function category(array & $request, array & $response, array & $app) {
        
    }

    function brand(array & $request, array & $response, array & $app) {
        
    }

    //商品规格1
    function get_goods_spec1($goods_code) {
        $arr_goods_spec1 = load_model('prm/GoodsModel')->get_goods_spec1($goods_code);
        $goods_spec1_name = '';
        $goods_spec1_code = '';
        foreach ($arr_goods_spec1 as $v) {
            $goods_spec1_code .= $v['spec1_code'] . ',';
            $name_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $v['spec1_code'], 'spec1_name');
            $goods_spec1_name .= isset($name_arr['data']['spec1_name']) ? $name_arr['data']['spec1_name'] . ',' : '';
        }
        $goods_spec1_code = $goods_spec1_code ? substr($goods_spec1_code, 0, strlen($goods_spec1_code) - 1) : $goods_spec1_code;

        $goods_spec1_name = $goods_spec1_name ? substr($goods_spec1_name, 0, strlen($goods_spec1_name) - 1) : $goods_spec1_name;
        return array('goods_spec1_code' => $goods_spec1_code, 'goods_spec1_name' => $goods_spec1_name);
    }

    //商品规格2
    function get_goods_spec2($goods_code) {
        $arr_goods_spec2 = load_model('prm/GoodsModel')->get_goods_spec2($goods_code);
        $goods_spec2_name = '';
        $goods_spec2_code = '';
        foreach ($arr_goods_spec2 as $v) {
            $goods_spec2_code .= $v['spec2_code'] . ',';
            $name_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $v['spec2_code'], 'spec2_name');
            $goods_spec2_name .= isset($name_arr['data']['spec2_name']) ? $name_arr['data']['spec2_name'] . ',' : '';
        }
        $goods_spec2_code = $goods_spec2_code ? substr($goods_spec2_code, 0, strlen($goods_spec2_code) - 1) : $goods_spec2_code;
        $goods_spec2_name = $goods_spec2_name ? substr($goods_spec2_name, 0, strlen($goods_spec2_name) - 1) : $goods_spec2_name;
        //$arr = explode(",",$goods_spec2_code);
        return array('goods_spec2_code' => $goods_spec2_code, 'goods_spec2_name' => $goods_spec2_name);
    }

    //规格1列表
    function get_spec1() {
        //规格1  start
        $arr_spec1 = load_model('prm/Spec1Model')->get_spec1();
        $key = 0;
        foreach ($arr_spec1 as $value) {
            $arr_spec1[$key][0] = $value['spec1_code'];
            $arr_spec1[$key][1] = $value['spec1_name'];
            $key++;
        }
        return $arr_spec1;
        //支持多选
        $field['data'] = $arr_spec1;
        $str = $this->_fieldtype_select_js($field, $_value, "spec1_code");
        return $str;
        //规格1  end
    }

    //规格2列表
    function get_spec2() {
        $arr_spec2 = load_model('prm/Spec2Model')->get_spec2();
        $key = 0;
        foreach ($arr_spec2 as $value) {
            $arr_spec2[$key][0] = $value['spec2_code'];
            $arr_spec2[$key][1] = $value['spec2_name'];
            $key++;
        }
        return $arr_spec2;
        //支持多选
        $field['data'] = $arr_spec2;
        $str = $this->_fieldtype_select_js($field, $_value, "spec2_code");
        return $str;
    }

    function detail(array & $request, array & $response, array & $app) {
        session_cache('set');
        $ret = array();
        if (isset($request['goods_id']) && $request['goods_id'] != '') {
            $ret = load_model('prm/GoodsModel')->get_by_id($request['goods_id']);
        } else {
            $ret['data'] = get_array_vars($ret, array('goods_id', 'goods_code', 'goods_name', 'goods_short_name', 'goods_produce_name', 'category_code', 'brand_code', 'season_code', 'year_code', 'goods_prop', 'state', 'weight', 'sell_price', 'cost_price', 'trade_price', 'purchase_price', 'goods_days', 'goods_desc', 'diy'));
        }

        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code('spec_power');
        $response['spec_power'] = isset($arr_spec) ? $arr_spec : '';
        if ($ret['data']['diy'] == 0) {
            $response['spec_power']['spec_power'] = 1;
        }
        //类别
        $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
        //品牌
        $response['brand'] = $this->get_purview_brand();
        //供应商
        $response['supplier'] = load_model('base/SupplierModel')->get_purview_supplier();
        //年份
        $response['year'] = $this->get_year();
        //季节
        $response['season'] = $this->get_season();
        //商品状态
        $response['state'] = load_model('prm/GoodsModel')->state;
        //规格1
        if (isset($ret['data']['goods_code'])) {
            $goods_spec1_arr = $this->get_goods_spec1($ret['data']['goods_code']);
            if ($response['spec_power']['spec_power'] == 0 && $goods_spec1_arr['goods_spec1_code'] == null) {
                /* 获取code为000的规格1信息 */
                $spec1_data = load_model('prm/Spec1Model')->get_by_code('000');
                $ret['data']['goods_spec1_str_code'] = $spec1_data['data']['spec1_code'];
                $ret['data']['goods_spec1_str_name'] = $spec1_data['data']['spec1_name'];
            } else {
                $ret['data']['goods_spec1_str_code'] = $goods_spec1_arr['goods_spec1_code'];
                $ret['data']['goods_spec1_str_name'] = $goods_spec1_arr['goods_spec1_name'];
            }
        }
        if (isset($ret['data']['goods_spec1_str_code'])) {
            $ret['data']['goods_spec1_code'] = explode(",", $ret['data']['goods_spec1_str_code']);
            $ret['data']['goods_spec1_name'] = explode(",", $ret['data']['goods_spec1_str_name']);
        }
        //规格2
        if (isset($ret['data']['goods_code'])) {
            $goods_spec2_arr = $this->get_goods_spec2($ret['data']['goods_code']);
            if ($response['spec_power']['spec_power'] == 0 && $goods_spec2_arr['goods_spec2_code'] == null) {
                /* 获取code为000的规格2信息 */
                $spec2_data = load_model('prm/Spec2Model')->get_by_code('000');
                $ret['data']['goods_spec2_str_code'] = $spec2_data['data']['spec2_code'];
                $ret['data']['goods_spec2_str_name'] = $spec2_data['data']['spec2_name'];
            } else {
                $ret['data']['goods_spec2_str_code'] = $goods_spec2_arr['goods_spec2_code'];
                $ret['data']['goods_spec2_str_name'] = $goods_spec2_arr['goods_spec2_name'];
            }
        }
        if (isset($ret['data']['goods_spec2_str_code'])) {
            $ret['data']['goods_spec2_code'] = explode(",", $ret['data']['goods_spec2_str_code']);
            $ret['data']['goods_spec2_name'] = explode(",", $ret['data']['goods_spec2_str_name']);
        }
        //商品条形码
        if (isset($ret['data']['goods_code'])) {

            $barcode_arr = load_model('prm/GoodsBarcodeModel')->get_barcode_comb_by_goods_code($ret['data']['goods_code']);

            $ret['data']['barcode'] = $barcode_arr;
        }
        //商品属性
        $response['prop'] = load_model('prm/GoodsModel')->prop;
        if (!empty($ret['data']['brand_code'])) {
            $brand_arr = load_model('prm/BrandModel')->get_by_field('brand_code', $ret['data']['brand_code']);
            $ret['data']['brand_id'] = isset($brand_arr['data']['brand_id']) ? $brand_arr['data']['brand_id'] : '';
        }
        if (!empty($ret['data']['category_code'])) {
            $brand_arr = load_model('prm/CategoryModel')->get_by_field('category_code', $ret['data']['category_code']);
            $ret['data']['category_id'] = isset($brand_arr['data']['category_id']) ? $brand_arr['data']['category_id'] : '';
        }
        if (!empty($ret['data']['season_code'])) {
            $brand_arr = load_model('base/SeasonModel')->get_by_field('season_code', $ret['data']['season_code']);
            $ret['data']['season_id'] = isset($brand_arr['data']['season_id']) ? $brand_arr['data']['season_id'] : '';
        }
        if (!empty($ret['data']['year_code'])) {
            $brand_arr = load_model('base/YearModel')->get_by_field('year_code', $ret['data']['year_code']);
            $ret['data']['year_id'] = isset($brand_arr['data']['year_id']) ? $brand_arr['data']['year_id'] : '';
        }
        //规格1
//        $response['spec1'] = $this->get_spec1();
//        $response['spec2'] = $this->get_spec2();
        if (isset($ret['data']['goods_code']) && $ret['data']['goods_code'] != '') {
            //$arr_price = load_model('prm/GoodsModel')->get_by_field('goods_code',$ret['data']['goods_code'],'cost_price,sell_price,trade_price,purchase_price','goods_price');
            $arr_price = $ret;
            if (isset($arr_price['data']['sell_price'])) {
                $ret['data']['sell_price'] = round($arr_price['data']['sell_price'], 2);
                if ($ret['data']['sell_price'] == 0) {
                    $ret['data']['sell_price'] = '';
                }
            }
            if (isset($arr_price['data']['cost_price'])) {
                $ret['data']['cost_price'] = round($arr_price['data']['cost_price'], 2);
                if ($ret['data']['cost_price'] == 0) {
                    $ret['data']['cost_price'] = '';
                }
            }
            if (isset($arr_price['data']['trade_price'])) {
                $ret['data']['trade_price'] = round($arr_price['data']['trade_price'], 2);
                if ($ret['data']['trade_price'] == 0) {
                    $ret['data']['trade_price'] = '';
                }
            }
            if (isset($arr_price['data']['purchase_price'])) {
                $ret['data']['purchase_price'] = round($arr_price['data']['purchase_price'], 2);
                if ($ret['data']['purchase_price'] == 0) {
                    $ret['data']['purchase_price'] = '';
                }
            }
            if (isset($arr_price['data']['min_price'])) {
                $ret['data']['min_price'] = round($arr_price['data']['min_price'], 2);
                if ($ret['data']['min_price'] == 0) {
                    $ret['data']['min_price'] = '';
                }
            }
        }
        if (isset($ret['data']['goods_days']) && $ret['data']['goods_days'] == 0) {
            $ret['data']['goods_days'] = '';
        }
        if (!isset($ret['data']['goods_id'])) {
            $ret['data']['goods_id'] = '';
        }
        $ret['data']['goods_img'] = isset($ret['data']['goods_img']) ? $ret['data']['goods_img'] : '';
        $ret['data']['goods_thumb_img'] = isset($ret['data']['goods_thumb_img']) ? $ret['data']['goods_thumb_img'] : '';

        if (isset($ret['data'])) {
            $response['data'] = $ret['data'];
        }
        //单据中是否被用过start
        $arr_spec1_limit = array();
        foreach ($response['data']['goods_spec1_code'] as $value) {
            $flag1 = $this->check_spec1_exist($response['data']['goods_code'], $value);
            if ($flag1) {
                $arr_spec1_limit[$value] = '1';
            }
        }
        $arr_spec2_limit = array();
        foreach ($response['data']['goods_spec2_code'] as $value) {
            $flag2 = $this->check_spec2_exist($response['data']['goods_code'], $value);
            if ($flag2) {
                $arr_spec2_limit[$value] = '1';
            }
        }
        //单据中是否被用过end
        //print_r($response['data']['goods_spec1_code']);
        $response['spec1_limit'] = $arr_spec1_limit;
        $response['spec2_limit'] = $arr_spec2_limit;
        $response['action'] = isset($request['action']) ? $request['action'] : '';
        $response['next'] = isset($request['next']) ? $request['next'] : '';
        $response['action_spec'] = 'do_save_spec';
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';

        $conf = require_conf('sys/upload');
        $response['upload_path'] = $conf['path']['upload_path'];

        $user_id = CTX()->get_session('user_id');
        //成本价权限
        $status_cost_price = load_model('sys/RoleManagePriceModel')->get_user_permission_price('cost_price', $user_id);
        $response['cost_price_status'] = $status_cost_price['status'];
        if ($status_cost_price['status'] != 1 && $response['action'] == 'do_edit') {
            $response['data']['cost_price'] = '*****';
            foreach ($response['data']['barcode'] as $key => $value) {
                $response['data']['barcode'][$key]['cost_price'] = '*****';
            }
        }
        //进货价权限
        $status_pur_price = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $user_id);
        $response['purchase_price_status'] = $status_pur_price['status'];
        if ($status_pur_price['status'] != 1 && $response['action'] == 'do_edit') {
            $response['data']['purchase_price'] = '*****';
        }
    }
    function diy_goods_detail(array & $request, array & $response, array & $app) {
        session_cache('set');
        $ret = array();
        if (isset($request['goods_id']) && $request['goods_id'] != '') {
            $ret = load_model('prm/GoodsModel')->get_by_id($request['goods_id']);
        } else {
            $ret['data'] = get_array_vars($ret, array('goods_id', 'goods_code', 'goods_name', 'goods_short_name', 'goods_produce_name', 'category_code', 'brand_code', 'season_code', 'year_code', 'goods_prop', 'state', 'weight', 'sell_price', 'cost_price', 'trade_price', 'purchase_price', 'goods_days', 'goods_desc','diy'));
        }

        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code('spec_power');
        $response['spec_power'] = isset($arr_spec) ? $arr_spec : '';
        if($ret['data']['diy'] == 0){
            $response['spec_power']['spec_power'] = 1;
        }
        //类别
        $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
        //品牌
        //$response['brand'] = $this->get_brand();
        $response['brand'] = $this->get_purview_brand();
        //年份
        $response['year'] = $this->get_year();
        //季节
        $response['season'] = $this->get_season();
        //商品状态
        $response['state'] = load_model('prm/GoodsModel')->state;
        //规格1
        if (isset($ret['data']['goods_code'])) {
            $goods_spec1_arr = $this->get_goods_spec1($ret['data']['goods_code']);
            if($response['spec_power']['spec_power'] == 0 && $goods_spec1_arr['goods_spec1_code'] == null){
                /*获取code为000的规格1信息*/
                $spec1_data = load_model('prm/Spec1Model')->get_by_code('000');
                $ret['data']['goods_spec1_str_code'] = $spec1_data['data']['spec1_code'];
                $ret['data']['goods_spec1_str_name'] = $spec1_data['data']['spec1_name'];
            }else{
                $ret['data']['goods_spec1_str_code'] = $goods_spec1_arr['goods_spec1_code'];
                $ret['data']['goods_spec1_str_name'] = $goods_spec1_arr['goods_spec1_name'];
            }

        }
        if (isset($ret['data']['goods_spec1_str_code'])) {
            $ret['data']['goods_spec1_code'] = explode(",", $ret['data']['goods_spec1_str_code']);
            $ret['data']['goods_spec1_name'] = explode(",", $ret['data']['goods_spec1_str_name']);
        }
        //规格2
        if (isset($ret['data']['goods_code'])) {
            $goods_spec2_arr = $this->get_goods_spec2($ret['data']['goods_code']);
            if($response['spec_power']['spec_power'] == 0 && $goods_spec2_arr['goods_spec2_code'] == null){
                /*获取code为000的规格2信息*/
                $spec2_data = load_model('prm/Spec2Model')->get_by_code('000');
                $ret['data']['goods_spec2_str_code'] = $spec2_data['data']['spec2_code'];
                $ret['data']['goods_spec2_str_name'] = $spec2_data['data']['spec2_name'];
            }else{
                $ret['data']['goods_spec2_str_code'] = $goods_spec2_arr['goods_spec2_code'];
                $ret['data']['goods_spec2_str_name'] = $goods_spec2_arr['goods_spec2_name'];
            }
        }
        if (isset($ret['data']['goods_spec2_str_code'])) {
            $ret['data']['goods_spec2_code'] = explode(",", $ret['data']['goods_spec2_str_code']);
            $ret['data']['goods_spec2_name'] = explode(",", $ret['data']['goods_spec2_str_name']);
        }
        //商品条形码
        if (isset($ret['data']['goods_code'])) {

            $barcode_arr = load_model('prm/GoodsBarcodeModel')->get_barcode_comb_by_goods_code($ret['data']['goods_code']);

            $ret['data']['barcode'] = $barcode_arr;
        }
        //商品属性
        $response['prop'] = load_model('prm/GoodsModel')->prop;
        if (!empty($ret['data']['brand_code'])) {
            $brand_arr = load_model('prm/BrandModel')->get_by_field('brand_code', $ret['data']['brand_code']);
            $ret['data']['brand_id'] = isset($brand_arr['data']['brand_id']) ? $brand_arr['data']['brand_id'] : '';
        }
        if (!empty($ret['data']['category_code'])) {
            $brand_arr = load_model('prm/CategoryModel')->get_by_field('category_code', $ret['data']['category_code']);
            $ret['data']['category_id'] = isset($brand_arr['data']['category_id']) ? $brand_arr['data']['category_id'] : '';
        }
        if (!empty($ret['data']['season_code'])) {
            $brand_arr = load_model('base/SeasonModel')->get_by_field('season_code', $ret['data']['season_code']);
            $ret['data']['season_id'] = isset($brand_arr['data']['season_id']) ? $brand_arr['data']['season_id'] : '';
        }
        if (!empty($ret['data']['year_code'])) {
            $brand_arr = load_model('base/YearModel')->get_by_field('year_code', $ret['data']['year_code']);
            $ret['data']['year_id'] = isset($brand_arr['data']['year_id']) ? $brand_arr['data']['year_id'] : '';
        }
        //规格1

//        $response['spec1'] = $this->get_spec1();
//        $response['spec2'] = $this->get_spec2();
        if (isset($ret['data']['goods_code']) && $ret['data']['goods_code'] != '') {
            //$arr_price = load_model('prm/GoodsModel')->get_by_field('goods_code',$ret['data']['goods_code'],'cost_price,sell_price,trade_price,purchase_price','goods_price');
            $arr_price = $ret;
            if (isset($arr_price['data']['sell_price'])) {
                $ret['data']['sell_price'] = round($arr_price['data']['sell_price'], 2);
                if ($ret['data']['sell_price'] == 0) {
                    $ret['data']['sell_price'] = '';
                }
            }
            if (isset($arr_price['data']['cost_price'])) {
                $ret['data']['cost_price'] = round($arr_price['data']['cost_price'], 2);
                if ($ret['data']['cost_price'] == 0) {
                    $ret['data']['cost_price'] = '';
                }
            }
            if (isset($arr_price['data']['trade_price'])) {
                $ret['data']['trade_price'] = round($arr_price['data']['trade_price'], 2);
                if ($ret['data']['trade_price'] == 0) {
                    $ret['data']['trade_price'] = '';
                }
            }
            if (isset($arr_price['data']['purchase_price'])) {
                $ret['data']['purchase_price'] = round($arr_price['data']['purchase_price'], 2);
                if ($ret['data']['purchase_price'] == 0) {
                    $ret['data']['purchase_price'] = '';
                }
            }
            if (isset($arr_price['data']['min_price'])) {
                $ret['data']['min_price'] = round($arr_price['data']['min_price'], 2);
                if ($ret['data']['min_price'] == 0) {
                    $ret['data']['min_price'] = '';
                }
            }
        }
        if (isset($ret['data']['goods_days']) && $ret['data']['goods_days'] == 0) {
            $ret['data']['goods_days'] = '';
        }
        if (!isset($ret['data']['goods_id'])) {
            $ret['data']['goods_id'] = '';
        }
        $ret['data']['goods_img'] = isset($ret['data']['goods_img']) ? $ret['data']['goods_img'] : '';
        $ret['data']['goods_thumb_img'] = isset($ret['data']['goods_thumb_img']) ? $ret['data']['goods_thumb_img'] : '';

        if (isset($ret['data'])) {
            $response['data'] = $ret['data'];
        }
        //单据中是否被用过start
        $arr_spec1_limit = array();
        foreach ($response['data']['goods_spec1_code'] as $value) {
            $flag1 = $this->check_spec1_exist($response['data']['goods_code'], $value);
            if ($flag1) {
                $arr_spec1_limit[$value] = '1';
            }
        }
        $arr_spec2_limit = array();
        foreach ($response['data']['goods_spec2_code'] as $value) {
            $flag2 = $this->check_spec2_exist($response['data']['goods_code'], $value);
            if ($flag2) {
                $arr_spec2_limit[$value] = '1';
            }
        }
        //单据中是否被用过end
        //print_r($response['data']['goods_spec1_code']);
        $response['spec1_limit'] = $arr_spec1_limit;
        $response['spec2_limit'] = $arr_spec2_limit;
        $response['action'] = isset($request['action']) ? $request['action'] : '';
        $response['next'] = isset($request['next']) ? $request['next'] : '';
        $response['action_spec'] = 'do_save_spec';
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';

        $conf = require_conf('sys/upload');
        $response['upload_path'] = $conf['path']['upload_path'];

        $user_id = CTX()->get_session('user_id');
        //成本价权限
        $status_cost_price = load_model('sys/RoleManagePriceModel')->get_user_permission_price('cost_price', $user_id);
        $response['cost_price_status'] = $status_cost_price['status'];
        if ($status_cost_price['status'] != 1 && $response['action'] == 'do_edit') {
            $response['data']['cost_price'] = '*****';
            foreach ($response['data']['barcode'] as $key => $value) {
                $response['data']['barcode'][$key]['cost_price'] = '*****';
            }
        }
        //进货价权限
        $status_pur_price = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $user_id);
        $response['purchase_price_status'] = $status_pur_price['status'];
        if ($status_pur_price['status'] != 1 && $response['action'] == 'do_edit') {
            $response['data']['purchase_price'] = '*****';
        }

    }

    /**
     * 检查规格1是否被用过
     * @param $goods_code
     * @param $spec1_code
     */
    function check_spec1_exist($goods_code, $spec1_code) {
        $flag = load_model('prm/GoodsModel')->check_spec1_exist($goods_code, $spec1_code);
        return $flag;
    }

    /**
     * 检查规格2是否被用过
     * @param $goods_code
     * @param $spec2_code
     */
    function check_spec2_exist($goods_code, $spec2_code) {
        $flag = load_model('prm/GoodsModel')->check_spec2_exist($goods_code, $spec2_code);
        return $flag;
    }

    //季节js
    function get_season_js() {
        $ret = $this->get_season();
        exit_json_response($ret);
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

    function get_year_js() {
        $ret = $this->get_year();
        exit_json_response($ret);
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

    function get_category_js() {
        $ret = load_model('prm/CategoryModel')->get_category_trees();
        $arr = array();
        foreach ($ret as $k => $v) {
            $arr[] = $v;
        }
        unset($ret);
        exit_json_response($arr);
    }

    //品牌数据js
    function get_brand_js() {
        $ret = $this->get_brand();
        exit_json_response($ret);
    }

    //品牌数据
    function get_brand() {
        $arr_brand = load_model('prm/BrandModel')->get_brand();
        $key = 0;
        foreach ($arr_brand as $value) {
            $arr_brand[$key][0] = $value['brand_code'];
            $arr_brand[$key][1] = $value['brand_name'];
            $key++;
        }
        return $arr_brand;
    }

    function get_purview_brand() {
        $arr_brand = load_model('prm/BrandModel')->get_purview_brand();

        $key = 0;
        foreach ($arr_brand as $value) {
            $arr_brand[$key][0] = $value['brand_code'];
            $arr_brand[$key][1] = $value['brand_name'];
            $key++;
        }
        return $arr_brand;
    }

    function get_spec2_js() {
        $spec2_code = $_REQUEST['spec2_code'];
        $ret = $this->get_spec2($spec2_code);

        $arr = explode(",", $spec2_code);
        $html = '';
        foreach ($ret as $k => $v) {
            if ($k % 4 == 0) {
                $html .= "<br>";
            }
            if (in_array($v['spec2_code'], $arr)) {
                $html .= "<input name='spec2[]' type='checkbox' checked value='{$v['spec2_code']}' />{$v['spec2_name']}&nbsp;&nbsp;&nbsp;&nbsp;";
            } else {
                $html .= "<input name='spec2[]' type='checkbox'  value='{$v['spec2_code']}' />{$v['spec2_name']}&nbsp;&nbsp;&nbsp;&nbsp;";
            }
        }
        echo $html;
        exit;
    }

    function get_spec1_js() {
        $spec1_code = $_REQUEST['spec1_code'];
        $ret = $this->get_spec1($spec1_code);
        $arr = explode(",", $spec1_code);
        $html = '';
        foreach ($ret as $k => $v) {
            if ($k % 4 == 0) {
                $html .= "<br>";
            }
            if (in_array($v['spec1_code'], $arr)) {
                $html .= "<input name='spec1[]' type='checkbox' checked value='{$v['spec1_code']}' />{$v['spec1_name']}&nbsp;&nbsp;&nbsp;&nbsp;";
            } else {
                $html .= "<input name='spec1[]' type='checkbox'  value='{$v['spec1_code']}' />{$v['spec1_name']}&nbsp;&nbsp;&nbsp;&nbsp;";
            }
        }
        echo $html;
        exit;
    }

    function _fieldtype_select_js($field, $value, $_id, $_editable) {
        $items = array();
        foreach ($field['data'] as $row) {
            $items[] = array('text' => $row[1], 'value' => $row[0]);
        }

        $items_str = json_encode($items);
        $jsstr = "
		var {$_id}_items = eval('({$items_str})'),
		 {$_id}_select = new BUI.Select.Select({
		render:'#{$_id}_select_multi',
		valueField:'#{$_id}',
		multipleSelect:true,
		items:{$_id}_items
	});
	{$_id}_select.render()
	{$_id}_select.setSelectedValue('{$value}');
	";
        return $jsstr;
    }

    function do_edit(array & $request, array & $response, array & $app) {
        $ret = load_model('prm/GoodsModel')->get_by_id($request['goods_id']); //取旧数据
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        if ($ret_arr['lof_status'] == 1) {
            $goods = get_array_vars($request, array('goods_code', 'goods_name', 'diy', 'goods_short_name', 'goods_produce_name', 'category_code', 'brand_code', 'season_code', 'year_code', 'goods_prop', 'state', 'weight', 'sell_price', 'cost_price', 'trade_price', 'purchase_price', 'goods_days', 'goods_img', 'goods_thumb_img', 'period_validity', 'operating_cycles', 'goods_desc', 'min_price', 'supplier_code'));
        } else {
            $goods = get_array_vars($request, array('goods_code', 'goods_name', 'diy', 'goods_short_name', 'goods_produce_name', 'category_code', 'brand_code', 'season_code', 'year_code', 'goods_prop', 'state', 'weight', 'sell_price', 'cost_price', 'trade_price', 'purchase_price', 'goods_days', 'goods_img', 'goods_thumb_img', 'goods_desc', 'min_price', 'supplier_code'));
        }

        $user_id = CTX()->get_session('user_id');
        //成本价权限
        $status_cost_price = load_model('sys/RoleManagePriceModel')->get_user_permission_price('cost_price', $user_id);
        if ($status_cost_price['status'] != 1) {
            $goods['cost_price'] = $ret['data']['cost_price'];
        }
        //进货价权限
        $status_pur_price = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $user_id);
        if ($status_pur_price['status'] != 1) {
            $goods['purchase_price'] = $ret['data']['purchase_price'];
        }
        foreach ($goods as $key3 => $value3) {
            if ($key3 == 'goods_desc') {
                $goods[$key3] = trim($value3);
            }
        }
        $ret1 = load_model('prm/GoodsModel')->update($goods, $request['goods_id']);
        
        $log_xq = '';

        $goods_old = array();

        $goods_old['goods_code'] = $ret['data']['goods_code'];
        $goods_old['goods_name'] = $ret['data']['goods_name'];
        $goods_old['goods_short_name'] = $ret['data']['goods_short_name'];
        $goods_old['goods_produce_name'] = $ret['data']['goods_produce_name'];
        $goods_old['category_code'] = $ret['data']['category_code'];
        $goods_old['brand_code'] = $ret['data']['brand_code'];
        $goods_old['season_code'] = $ret['data']['season_code'];
        $goods_old['year_code'] = $ret['data']['year_code'];
        $goods_old['goods_prop'] = $ret['data']['goods_prop'];
        $goods_old['state'] = $ret['data']['state'];
        $goods_old['weight'] = $ret['data']['weight'];
        $goods_old['goods_days'] = $ret['data']['goods_days'];
        $goods_old['goods_desc'] = trim($ret['data']['goods_desc']);
//        $arr_price = load_model('prm/GoodsModel')->get_by_field('goods_code', $request['goods_code'], 'cost_price,sell_price,trade_price,purchase_price', 'goods_price');

        $goods_old['sell_price'] = floatval($ret['data']['sell_price']);
        $goods_old['cost_price'] = floatval($ret['data']['cost_price']);
        $goods_old['trade_price'] = floatval($ret['data']['trade_price']);
        $goods_old['purchase_price'] = floatval($ret['data']['purchase_price']);
        $goods_new = $goods;

        $goods_edit = array();
        foreach ($goods_old as $key2 => $value2) {
            if (isset($goods_new[$key2]) && ($value2 <> $goods_new[$key2])) {
                $goods_edit[$key2] = $value2;
            }
        }
        if (empty($goods_edit)) {
            $ret1['data'] = $request['goods_id'];
            exit_json_response($ret1);
        }
        //映射名称
        $goods_name['goods_code'] = '商品编码';
        $goods_name['goods_name'] = '商品名称';
        $goods_name['goods_short_name'] = '商品简称';
        $goods_name['goods_produce_name'] = '出厂名称';
        $goods_name['category_code'] = '商品分类';
        $goods_name['brand_code'] = '商品品牌';
        $goods_name['season_code'] = '商品季节';
        $goods_name['year_code'] = '商品年份';
        $goods_name['goods_prop'] = '商品属性';
        $goods_name['state'] = '商品状态';
        $goods_name['weight'] = '商品重量';
        $goods_name['goods_days'] = '生产周期';
        $goods_name['goods_desc'] = '商品描述';
        $goods_name['sell_price'] = "吊牌价格";
        $goods_name['cost_price'] = '成本价格';
        $goods_name['trade_price'] = '批发价格';
        $goods_name['purchase_price'] = '进货价格';

        foreach ($goods_edit as $key => $value) {
            $old_value = $value;
            $new_value = $goods_new[$key];
            switch ($key) {
                case 'category_code':
                    $category_arr = load_model('prm/CategoryModel')->get_by_field('category_code', $value, 'category_name');
                    $old_value = $category_arr['data']['category_name'];
                    $category_arr = load_model('prm/CategoryModel')->get_by_field('category_code', $goods_new[$key], 'category_name');
                    $new_value = $category_arr['data']['category_name'];
                    break;
                case 'brand_code':
                    $brand_arr = load_model('prm/BrandModel')->get_by_field('brand_code', $value, 'brand_name');
                    $old_value = $brand_arr['data']['brand_name'];
                    $brand_arr = load_model('prm/BrandModel')->get_by_field('brand_code', $goods_new[$key], 'brand_name');
                    $new_value = $brand_arr['data']['brand_name'];
                    break;
                case 'season_code':
                    $brand_arr = load_model('base/SeasonModel')->get_by_field('season_code', $value, 'season_name');
                    $old_value = $brand_arr['data']['season_name'];
                    $brand_arr = load_model('base/SeasonModel')->get_by_field('season_code', $goods_new[$key], 'season_name');
                    $new_value = $brand_arr['data']['season_name'];
                    break;
                case 'year_code':
                    $brand_arr = load_model('base/YearModel')->get_by_field('year_code', $value, 'year_name');
                    $old_value = $brand_arr['data']['year_name'];
                    $brand_arr = load_model('base/YearModel')->get_by_field('year_code', $goods_new[$key], 'year_name');
                    $new_value = $brand_arr['data']['year_name'];
                    break;
                case 'goods_prop':
                    $prop = load_model('prm/GoodsModel')->prop;

                    $old_value = $prop[$value]['1'];
                    $new_value = $prop[$goods_new[$key]]['1'];
                    break;
                case 'state':
                    $state = load_model('prm/GoodsModel')->state;
                    $old_value = $state[$value]['1'];
                    $new_value = $state[$goods_new[$key]]['1'];
                    break;
            }
            $log_xq .= $goods_name[$key] . '由' . $old_value . '改为' . $new_value . ',';
        }

        $module = '商品'; //模块名称
        $yw_code = $request['goods_code']; //业务编码
        $operate_xq = '编辑商品'; //操作详情
        $operate_type = '编辑';

        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
        $ret2 = load_model('sys/OperateLogModel')->insert($log);
        ###########添加操作日志end
        //CTX()->redirect('prm/goods/do_list');
        //return;

        $ret2['data'] = $request['goods_id'];

        exit_json_response($ret2);
    }

    function do_save_spec(array & $request, array & $response, array & $app) {
        $goods_spec1_arr = $this->get_goods_spec1($request['goods_code']); //规格1旧数据
        $goods_spec2_arr = $this->get_goods_spec2($request['goods_code']);
        $barcode_arr = load_model('prm/GoodsBarcodeModel')->get_by_search($request['goods_code']);

        $user_id = CTX()->get_session('user_id');
        //成本价权限关闭，不更新成本价
        $status_cost_price = load_model('sys/RoleManagePriceModel')->get_user_permission_price('cost_price', $user_id);
        if ($status_cost_price['status'] != 1 && !empty($barcode_arr)) {
            $sku_info = array();
            foreach ($barcode_arr as $val) {
                $sku_info[$val['sku']] = $val;
            }
            $spec1_code_str = $request['spec1_code'];
            $spec2_code_str = $request['spec2_code'];
            $spec1_code_arr = explode(',', $spec1_code_str);
            $spec2_code_arr = explode(',', $spec2_code_str);
            foreach ($spec1_code_arr as $spec1_code) {
                foreach ($spec2_code_arr as $spec2_code) {
                    //sku
                    $str_sku = $spec1_code . '_' . $spec2_code . '_sku';
                    $sku = trim($request[$str_sku]);
                    //sku 成本价
                    $str_cost_price = $spec1_code . '_' . $spec2_code . '_cost_price';
                    $request[$str_cost_price] = isset($sku_info[$sku]['cost_price']) ? $sku_info[$sku]['cost_price'] : '0.000';
                }
            }
        }

        //商品规格1保存
        //系统条码sku保存
        CTX()->db->begin_trans();
        $ret2 = load_model('prm/GoodsBarcodeModel')->save_sku($request);
        if ($ret2['status'] < 1) {
            CTX()->db->rollback();
            exit_json_response($ret2);
        }
        //商品条码(国标码)保存
        $ret2 = load_model('prm/GoodsBarcodeModel')->save_barcode($request);

        ###########操作日志start
        $log_xq = '';
        if ($ret2['status'] < 1) {
            CTX()->db->rollback();
            exit_json_response($ret2);
        }
        
        //规格1
        $spec1_old = explode(',', $goods_spec1_arr['goods_spec1_name']);
        $spec1_code_new = explode(',', $request['spec1_code']);
        $spec1M = load_model('prm/Spec1Model');
        $spec1_new = array();
        foreach ($spec1_code_new as $v) {
            $spec1_new[] = $spec1M->get_spec1_name($v);
        }
        $spec1_del = array_diff($spec1_old, $spec1_new);
        if ($spec1_del) {
            $log_xq .= '删除' . implode(',', $spec1_del) . ' ';
        }
        $spec1_add = array_diff($spec1_new, $spec1_old);
        if ($spec1_add) {
            $log_xq .= '增加' . implode(',', $spec1_add) . ' ';
        }
        
        //规格2
        $spec2_old = explode(',', $goods_spec2_arr['goods_spec2_name']);
        $spec2_code_new = explode(',', $request['spec2_code']);
        $spec2M = load_model('prm/Spec2Model');
        $spec2_new = array();
        foreach ($spec2_code_new as $v) {
            $spec2_new[] = $spec2M->get_spec2_name($v);
        }
        $spec2_del = array_diff($spec2_old, $spec2_new);
        if ($spec2_del) {
            $log_xq .= '删除' . implode(',', $spec2_del) . ' ';
        }
        $spec2_add = array_diff($spec2_new, $spec2_old);
        if ($spec2_add) {
            $log_xq .= '增加' . implode(',', $spec2_add) . ' ';
        }

        //商品条形码
        $barcode_old = array();
        foreach ($barcode_arr as $key => $value) {
            if ($value['barcode'] <> '') {
                $barcode_old[$value['spec1_code'] . '_' . $value['spec2_code'] . '_barcode'] = $value['barcode'];
            }
        }
        $barcode_new = array();
        foreach (explode(',', $request['spec1_code']) as $spec1_code) {
            foreach (explode(',', $request['spec2_code']) as $spec2_code) {
                $str_barcode = $spec1_code . '_' . $spec2_code . '_barcode';
                if ($request[$str_barcode] <> '') {
                    $barcode_new[$str_barcode] = $request[$str_barcode];
                }
            }
        }
        //删除条形码
        $barcode_del = array_diff_assoc($barcode_old, $barcode_new);
        foreach ($barcode_del as $key1 => $value1) {
            if (isset($barcode_new[$key1])) {
                unset($barcode_del[$key1]);
            }
        }
        //增加条形码
        $barcode_add = array_diff_assoc($barcode_new, $barcode_old);
        foreach ($barcode_add as $key3 => $value3) {
            if (isset($barcode_old[$key3])) {
                unset($barcode_add[$key3]);
            }
        }
        //修改条形码
        $barcode_edit = array();
        foreach ($barcode_old as $key2 => $value2) {
            if (isset($barcode_new[$key2]) && $value2 <> $barcode_new[$key2]) {
                $barcode_edit[$key2] = $value2;
            }
        }
        $spec2_arr = array();
        foreach ($barcode_del as $key => $value) {
            $arr = explode("_", $key);
            $spec1_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $arr[0], 'spec1_name');
            $spec2_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $arr[1], 'spec2_name');
            $log_xq .= "删除" . $spec1_arr['data']['spec1_name'] . '/' . $spec2_arr['data']['spec2_name'] . '的商品条码' . $value . ',';
        }
        foreach ($barcode_add as $key => $value) {
            $arr = explode("_", $key);
            $spec1_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $arr[0], 'spec1_name');
            $spec2_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $arr[1], 'spec2_name');
            $log_xq .= "增加" . $spec1_arr['data']['spec1_name'] . '/' . $spec2_arr['data']['spec2_name'] . "的商品条码:" . $value . ',';
        }
        foreach ($barcode_edit as $key => $value) {
            $arr = explode("_", $key);
            $spec1_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $arr[0], 'spec1_name');
            $spec2_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $arr[1], 'spec2_name');
            $log_xq .= "修改" . $spec1_arr['data']['spec1_name'] . '/' . $spec2_arr['data']['spec2_name'] . "的商品条码由" . $value . '改为' . $barcode_new[$key] . ',';
        }

        //商品国标码
        $gb_code_old = array();
        foreach ($barcode_arr as $key => $value) {
            if ($value['gb_code'] <> '') {
                $gb_code_old[$value['spec1_code'] . '_' . $value['spec2_code'] . '_gb_code'] = $value['gb_code'];
            }
        }
        $gb_code_new = array();
        foreach (explode(',', $request['spec1_code']) as $spec1_code) {
            foreach (explode(',', $request['spec2_code']) as $spec2_code) {
                $str_gb_code = $spec1_code . '_' . $spec2_code . '_gb_code';
                if ($request[$str_gb_code] <> '') {
                    $gb_code_new[$str_gb_code] = $request[$str_gb_code];
                }
            }
        }
        //删除国标码
        $gb_code_del = array_diff_assoc($gb_code_old, $gb_code_new);
        foreach ($gb_code_del as $key1 => $value1) {
            if (isset($gb_ode_new[$key1])) {
                unset($gb_code_del[$key1]);
            }
        }
        //修改国标码
        $gb_code_edit = array();
        foreach ($gb_code_old as $key2 => $value2) {
            if (isset($gb_code_new[$key2]) && $value2 <> $gb_code_new[$key2]) {
                $gb_code_edit[$key2] = $value2;
            }
        }
        //增加国标码
        $gb_code_add = array_diff_assoc($gb_code_new, $gb_code_old);
        foreach ($gb_code_add as $key3 => $value3) {
            if (isset($gb_code_old[$key3])) {
                unset($gb_code_add[$key3]);
            }
        }
        foreach ($gb_code_del as $key => $value) {
            $arr = explode("_", $key);
            $spec1_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $arr[0], 'spec1_name');
            $spec2_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $arr[1], 'spec2_name');
            $log_xq .= "删除" . $spec1_arr['data']['spec1_name'] . '/' . $spec2_arr['data']['spec2_name'] . '的国标码' . $value . ',';
        }
        foreach ($gb_code_add as $key => $value) {
            $arr = explode("_", $key);
            $spec1_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $arr[0], 'spec1_name');
            $spec2_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $arr[1], 'spec2_name');
            $log_xq .= "增加" . $spec1_arr['data']['spec1_name'] . '/' . $spec2_arr['data']['spec2_name'] . "的国标码:" . $value . ',';
        }
        foreach ($gb_code_edit as $key => $value) {
            $arr = explode("_", $key);
            $spec1_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $arr[0], 'spec1_name');
            $spec2_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $arr[1], 'spec2_name');
            $log_xq .= "修改" . $spec1_arr['data']['spec1_name'] . '/' . $spec2_arr['data']['spec2_name'] . "的国标码由" . $value . '改为' . $barcode_new[$key] . ',';
        }

        $module = '商品'; //模块名称
        $yw_code = $request['goods_code']; //业务编码
        $operate_xq = '编辑商品'; //操作详情
        $operate_type = '编辑';

        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
        $ret1 = load_model('sys/OperateLogModel')->insert($log);

        $check_data = load_model('prm/GoodsBarcodeModel')->get_user_sku();
        if (!empty($check_data)) {
            $msg = "系统SKU" . implode(",", $check_data) . " 已经被使用，不能进行修改";
            $ret1['message'] = $msg;
        }

        CTX()->db->commit();
        exit_json_response($ret1);
    }

    function do_add(array & $request, array & $response, array & $app) {
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        if ($ret_arr['lof_status'] == 1) {
            $data = get_array_vars($request, array('goods_code', 'goods_name', 'diy', 'goods_short_name', 'goods_produce_name', 'category_code', 'brand_code', 'season_code', 'year_code', 'goods_prop', 'state', 'weight', 'sell_price', 'cost_price', 'trade_price', 'purchase_price', 'goods_days', 'goods_img', 'goods_thumb_img', 'period_validity', 'operating_cycles', 'goods_desc', 'min_price', 'supplier_code'));
        } else {
            $data = get_array_vars($request, array('goods_code', 'goods_name', 'diy', 'goods_short_name', 'goods_produce_name', 'category_code', 'brand_code', 'season_code', 'year_code', 'goods_prop', 'state', 'weight', 'sell_price', 'cost_price', 'trade_price', 'purchase_price', 'goods_days', 'goods_img', 'goods_thumb_img', 'goods_desc', 'min_price', 'supplier_code'));
        }

        $data['goods_code'] = trim($data['goods_code']);
        $ret = load_model('prm/GoodsModel')->insert($data);

        ###########添加操作日志start
        $module = '商品'; //模块名称
        $yw_code = $request['goods_code']; //业务编码
        $operate_type = '新增';
        $operate_xq = '新增商品' . $yw_code; //操作详情

        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $operate_xq);

        $ret1 = load_model('sys/OperateLogModel')->insert($log);

        exit_json_response($ret);
    }
    function do_diy_add(array & $request, array & $response, array & $app) {
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        if ($ret_arr['lof_status'] == 1) {
            $data = get_array_vars($request, array('goods_code', 'goods_name', 'diy', 'goods_short_name', 'goods_produce_name', 'category_code', 'brand_code', 'season_code', 'year_code', 'goods_prop', 'state', 'weight', 'sell_price', 'cost_price', 'trade_price', 'purchase_price', 'goods_days', 'goods_img', 'goods_thumb_img', 'period_validity', 'operating_cycles', 'goods_desc', 'min_price'));
        } else {
            $data = get_array_vars($request, array('goods_code', 'goods_name', 'diy', 'goods_short_name', 'goods_produce_name', 'category_code', 'brand_code', 'season_code', 'year_code', 'goods_prop', 'state', 'weight', 'sell_price', 'cost_price', 'trade_price', 'purchase_price', 'goods_days', 'goods_img', 'goods_thumb_img', 'goods_desc', 'min_price'));
        }

        $data['goods_code'] = trim($data['goods_code']);
        $ret = load_model('prm/GoodsModel')->insert($data);

        ###########添加操作日志start
        $module = '商品'; //模块名称
        $yw_code = $request['goods_code']; //业务编码
        $operate_type = '新增';
        $operate_xq = '新增组装商品' . $yw_code; //操作详情
        //$log = array('user_id'=>CTX()->get_session('user_id'),'user_code'=>CTX()->get_session('user_code'),'ip'=>get_client_ip(),'add_time'=>date('Y-m-d H:i:s'),'module'=>$module,'yw_code'=>$yw_code,'operate_type'=>$operate_type,'operate_xq'=>$operate_xq);
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $operate_xq);

        $ret1 = load_model('sys/OperateLogModel')->insert($log);
        ###########添加操作日志end
        //CTX()->redirect('prm/goods/detail&action=do_edit&goods_id='.$ret['data'].'&next=1');
        exit_json_response($ret);
    }

    public function goods_select_tpl_short(array & $request, array & $response, array & $app) {
        #####################################################################
        //获取仓库/品牌/年份/季节的下拉选框数据

        $store = load_model('base/StoreModel')->get_code_name();
        $request['diy'] = isset($request['diy']) ? $request['diy'] : '';
        $response['store_name'] = isset($request['store_code']) ? $store[$request['store_code']] : '';
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $brand = load_model('prm/BrandModel')->get_code_name();
        $response['selection']['brand'] = json_encode($brand);
        $year = load_model('base/YearModel')->get_code_name();
        $response['selection']['year'] = json_encode($year);
        $season = load_model('base/SeasonModel')->get_code_name();
        $response['selection']['season'] = json_encode($season);

        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        //批次是否开启
        $response['lof'] = '';
        if (isset($request['lof_status']) && $request['lof_status'] == 1) {
            $arr = array('lof_status');
            $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
            $response['lof'] = isset($arr_lof['lof_status']) ? $arr_lof['lof_status'] : '';
        }

        $app['page'] = 'NULL';
    }

    //商品选择控件 不带数量的 选择框--未使用
    public function goods_select_tpl_multi(array & $request, array & $response, array & $app) {
        #####################################################################
        //获取仓库/品牌/年份/季节的下拉选框数据

        $store = load_model('base/StoreModel')->get_code_name();
        $request['diy'] = isset($request['diy']) ? $request['diy'] : '';
        $response['store_name'] = isset($request['store_code']) ? $store[$request['store_code']] : '';
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $brand = load_model('prm/BrandModel')->get_code_name();
        $response['selection']['brand'] = json_encode($brand);
        $year = load_model('base/YearModel')->get_code_name();
        $response['selection']['year'] = json_encode($year);
        $season = load_model('base/SeasonModel')->get_code_name();
        $response['selection']['season'] = json_encode($season);

        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        //批次是否开启
        $response['lof'] = '';
        if (isset($request['lof_status']) && $request['lof_status'] == 1) {
            $arr = array('lof_status');
            $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
            $response['lof'] = isset($arr_lof['lof_status']) ? $arr_lof['lof_status'] : '';
        }

        $app['page'] = 'NULL';
    }

    /**
     * 商品选择控件, 可以在弹出框显示也可单独页面显示   基础档案商品用的
     * @since 2014-11-06
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function goods_select_tpl(array & $request, array & $response, array & $app) {
        #####################################################################
        //获取仓库/品牌/年份/季节的下拉选框数据

        $request['is_select'] = isset($request['is_select']) ? $request['is_select'] : 0;

        $store = load_model('base/StoreModel')->get_code_name();
        $request['diy'] = isset($request['diy']) ? $request['diy'] : '';
        $response['store_name'] = isset($request['store_code']) && !empty($request['store_code']) ? $store[$request['store_code']] : '';
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $brand = load_model('prm/BrandModel')->get_code_name();
        $response['selection']['brand'] = json_encode($brand);
        $year = load_model('base/YearModel')->get_code_name();
        $response['selection']['year'] = json_encode($year);
        $season = load_model('base/SeasonModel')->get_code_name();
        $response['selection']['season'] = json_encode($season);

        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        //批次是否开启
        $response['lof'] = '';
        if (isset($request['lof_status']) && $request['lof_status'] == 1) {
            $arr = array('lof_status');
            $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
            $response['lof'] = isset($arr_lof['lof_status']) ? $arr_lof['lof_status'] : '';
        }
        $response['list_type'] = isset($request['list_type']) && !empty($request['list_type']) ? $request['list_type'] : '';
        $response['custom_code'] = isset($request['custom_code']) && !empty($request['custom_code']) ? $request['custom_code'] : ''; //是否查询分销款商品

        $app['page'] = 'NULL';
    }

    /**
     * 商品选择控件, 可以在弹出框显示也可单独页面显示   与库存相关商品用的
     * @since 2014-11-06
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function goods_select_tpl_inv(array & $request, array & $response, array & $app) {
        #####################################################################
        //获取仓库/品牌/年份/季节的下拉选框数据
        $store = load_model('base/StoreModel')->get_code_name();
        $response['store_name'] = isset($request['store_code']) ? $store[$request['store_code']] : '';
        $request['diy'] = isset($request['diy']) ? $request['diy'] : '';
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $brand = load_model('prm/BrandModel')->get_code_name();
        $response['selection']['brand'] = json_encode($brand);
        $year = load_model('base/YearModel')->get_code_name();
        $response['selection']['year'] = json_encode($year);
        $season = load_model('base/SeasonModel')->get_code_name();
        $response['selection']['season'] = json_encode($season);

        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        //批次是否开启
        $response['lof'] = '';
        if (isset($request['lof_status']) && $request['lof_status'] == 1) {
            $arr = array('lof_status');
            $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
            $response['lof'] = isset($arr_lof['lof_status']) ? $arr_lof['lof_status'] : '';
        }
        $request['model'] = isset($request['model']) ? $request['model'] : '';
        $ret_store = load_model('base/StoreModel')->get_by_code($request['store_code']);
        $response['allow_negative_inv'] = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;
        if (isset($request['dingd']) && $request['dingd'] == '1') {
            $response['allow_negative_inv'] = $request['dingd'];
        }
        $response['type'] = isset($request['type']) ? $request['type'] : '';
        $response['record_code'] = isset($request['record_code']) ? $request['record_code'] : '';
        $response['list_type'] = isset($request['list_type']) && !empty($request['list_type']) ? $request['list_type'] : '';
        $response['custom_code'] = isset($request['custom_code']) && !empty($request['custom_code']) ? $request['custom_code'] : ''; //是否查询分销款商品
        $app['page'] = 'NULL';
    }

    /**
     * 调整单新增商品专门页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function goods_select_tpl_inv_adjust(array & $request, array & $response, array & $app) {
        #####################################################################
        //获取仓库/品牌/年份/季节的下拉选框数据
        $store = load_model('base/StoreModel')->get_code_name();
        $response['store_name'] = isset($request['store_code']) ? $store[$request['store_code']] : '';
        $request['diy'] = isset($request['diy']) ? $request['diy'] : '';
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $brand = load_model('prm/BrandModel')->get_code_name();
        $response['selection']['brand'] = json_encode($brand);
        $year = load_model('base/YearModel')->get_code_name();
        $response['selection']['year'] = json_encode($year);
        $season = load_model('base/SeasonModel')->get_code_name();
        $response['selection']['season'] = json_encode($season);

        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        //批次是否开启
        $arr = array('lof_status');
        $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['lof'] = isset($arr_lof['lof_status']) ? $arr_lof['lof_status'] : '';

        $request['model'] = isset($request['model']) ? $request['model'] : '';
        $ret_store = load_model('base/StoreModel')->get_by_code($request['store_code']);
        $response['allow_negative_inv'] = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;
        if (isset($request['dingd']) && $request['dingd'] == '1') {
            $response['allow_negative_inv'] = $request['dingd'];
        }
        $response['type'] = isset($request['type']) ? $request['type'] : '';
        $response['record_code'] = isset($request['record_code']) ? $request['record_code'] : '';
        $response['list_type'] = isset($request['list_type']) && !empty($request['list_type']) ? $request['list_type'] : '';
        $response['custom_code'] = isset($request['custom_code']) && !empty($request['custom_code']) ? $request['custom_code'] : ''; //是否查询分销款商品
        $app['page'] = 'NULL';
    }

    /**
     * 商品选择控件, 可以在弹出框显示也可单独页面显示   与库存相关商品用的
     * @since 2014-11-06
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function goods_select_tpl_sku(array & $request, array & $response, array & $app) {
        #####################################################################
        //获取仓库/品牌/年份/季节的下拉选框数据
        $response['type'] = $request['type'];
        $store = load_model('base/StoreModel')->get_code_name();
        $response['store_name'] = isset($request['store_code']) ? $store[$request['store_code']] : '';
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $brand = load_model('prm/BrandModel')->get_code_name();
        $response['selection']['brand'] = json_encode($brand);
        $year = load_model('base/YearModel')->get_code_name();
        $response['selection']['year'] = json_encode($year);
        $season = load_model('base/SeasonModel')->get_code_name();
        $response['selection']['season'] = json_encode($season);

        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        //批次是否开启
        $response['lof'] = '';
        if (isset($request['lof_status']) && $request['lof_status'] == 1) {
            $arr = array('lof_status');
            $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
            $response['lof'] = isset($arr_lof['lof_status']) ? $arr_lof['lof_status'] : '';
        }
        $request['diy'] = isset($request['diy']) ? $request['diy'] : '';


        $ret_store = load_model('base/StoreModel')->get_by_code($request['store_code']);
        $response['allow_negative_inv'] = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;
        $response['list_type'] = isset($request['list_type']) && !empty($request['list_type']) ? $request['list_type'] : '';
        $response['custom_code'] = isset($request['custom_code']) && !empty($request['custom_code']) ? $request['custom_code'] : ''; //是否查询分销款商品
        $app['page'] = 'NULL';
    }

    public function goods_select_tpl_presell(array & $request, array & $response, array & $app) {
        $this->goods_select_tpl_shop($request, $response, $app);
    }

    /**
     * 商品选择控件，供添加门店商品使用
     */
    public function goods_select_tpl_shop(array & $request, array & $response, array & $app) {
        $brand = load_model('prm/BrandModel')->get_code_name();
        $response['selection']['brand'] = json_encode($brand);
        $year = load_model('base/YearModel')->get_code_name();
        $response['selection']['year'] = json_encode($year);
        $season = load_model('base/SeasonModel')->get_code_name();
        $response['selection']['season'] = json_encode($season);

        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';

        $app['page'] = 'NULL';
    }

    public function goods_select_action(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';

        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('prm/InvModel')->get_sku($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    public function goods_select_action_inv(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        //批次是否开启
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        if (isset($request['lof_status']) && $request['lof_status'] == 1) {
            $arr = array('lof_status');
            $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
            $request['lof_status'] = isset($arr_lof['lof_status']) ? $arr_lof['lof_status'] : '';
        }
        //status
        $result = load_model('prm/InvModel')->get_sku_inv($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    public function goods_select_action_inv_adjust(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        //批次是否开启
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        if (isset($request['lof_status']) && $request['lof_status'] == 1) {
            $arr = array('lof_status');
            $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
            $request['lof_status'] = isset($arr_lof['lof_status']) ? $arr_lof['lof_status'] : '';
        }
        //status
        $result = load_model('prm/InvModel')->get_sku_inv_adjust($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    public function goods_select_action_sku(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        //批次是否开启
        $request['page_size'] = $request['limit'];
        ;
        $request['page'] = $request['pageIndex'] + 1;
        if (isset($request['lof_status']) && $request['lof_status'] == 1) {
            $arr = array('lof_status');
            $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
            $request['lof_status'] = isset($arr_lof['lof_status']) ? $arr_lof['lof_status'] : '';
        }
        //status
        $result = load_model('prm/InvModel')->get_sku_inv_all($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    /**
     * 启用停用
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('prm/GoodsModel')->update_active($arr[$request['type']], $request['id']);

        exit_json_response($ret);
    }
    //停用启用组装
    function update_active_diy(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('prm/GoodsModel')->update_active($arr[$request['type']], $request['id']);

        exit_json_response($ret);
    }

    // 批量启用停用
    function opt_update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 0, 'disable' => 1);
        foreach ($request['id'] as $value) {
            $ret = load_model('prm/GoodsModel')->update_active($arr[$request['type']], $value);
        }
        exit_json_response($ret);
    }
    // 批量启用停用组装
    function opt_update_active_diy(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 0, 'disable' => 1);
        foreach ($request['id'] as $value) {
            $ret = load_model('prm/GoodsModel')->update_active($arr[$request['type']], $value);
        }
        exit_json_response($ret);
    }

    /**
     * barcode是否重复
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function barcode_exist(array &$request, array &$response, array &$app) {
        if (isset($request['barcode']) && $request['barcode'] != '') {
            $ret = load_model('prm/GoodsModel')->barcode_exist($request['barcode'], $request['goods_code'], $request['spec']);

            if ($ret['status'] != 1) {
                $ret['status'] = '0';
                $ret['data'] = 'false';
                $ret['message'] = '此条码可以使用';
            }
            exit_json_response($ret);
        }
    }

    /**
     * gb_code是否重复
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function gb_code_exist(array &$request, array &$response, array &$app) {
        if (isset($request['gb_code']) && $request['gb_code'] != '') {
            $ret = load_model('prm/GoodsModel')->gb_code_exist($request['gb_code'], $request['goods_code'], $request['spec']);
            if ($ret['status'] != 1) {
                $ret['status'] = '0';
                $ret['data'] = 'false';
                $ret['message'] = '此国标码可以使用';
            }
            exit_json_response($ret);
        }
    }

    /**
     * 是否存在批次
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function lof_exist(array &$request, array &$response, array &$app) {
        if (isset($request['lof_no']) && $request['lof_no'] != '') {
            $flag = load_model('prm/GoodsModel')->lof_exist($request['lof_no']);
            $ret = array();
            if (isset($flag['status']) && $flag['status'] == '1') {
                if ($request['production_date'] == '') {
                    $ret['status'] = '1';
                    $ret['data'] = $flag['data'][0]['production_date'];
                    $ret['message'] = '重复,生产日期为空';
                } else {
                    if (isset($flag['data'][0]['production_date']) && $flag['data'][0]['production_date'] == $request['production_date']) {
                        $ret['status'] = '2';
                        $ret['data'] = 'true';
                        $ret['message'] = '重复,生产日期一值';
                    } else {
                        $ret['status'] = '3';
                        $ret['data'] = 'true';
                        $ret['message'] = '已存在批次号' . $request['lof_no'] . '，生产日期' . $flag['data'][0]['production_date'] . ' 的商品，请重新修改批次号';
                    }
                }
            } else {
                $ret['status'] = '0';
                $ret['data'] = 'false';
                $ret['message'] = '此批次可以使用';
            }
            exit_json_response($ret);
        }
    }

    //组合商品
    function detail_diy(array & $request, array & $response, array & $app) {
        $arr = array(':goods_code' => $request['goods_code']);
        $barcord = load_model('prm/GoodsBarcodeModel')->get_barcode_list($arr);
        $response['goods_code'] = $request['goods_code'];
        $response['sku'] = $request['sku'];
        filter_fk_name($barcord, array('goods_code|goods_code', 'spec1_code|spec1_code', 'spec2_code|spec2_code'));
        //价格
        $arr_price = load_model('prm/GoodsModel')->get_by_field('goods_code', $request['goods_code'], 'sell_price', 'goods_price');
        ;
        if (isset($arr_price['data']['sell_price'])) {
            $sell_price = round($arr_price['data']['sell_price'], 2);
            if ($sell_price == 0) {
                $sell_price = '';
            }
        }

        foreach ($barcord as $key => $v) {
            $barcord[$key]['sell_price'] = $sell_price;
            if ($v['sku'] == $request['sku']) {
                $arr1 = array(':p_sku' => $v['sku'], ':p_goods_code' => $request['goods_code']);
                $diy = load_model('prm/GoodsBarcodeModel')->get_diy_list($arr1);
                filter_fk_name($diy, array('goods_code|goods_code', 'spec1_code|spec1_code', 'spec2_code|spec2_code'));
                $barcord[$key]['diy'] = $diy;
            }
        }

        $response['barcord'] = $barcord;
    }

    //ajax 组合
    function diy_show_detail(array &$request, array &$response, array &$app) {
        $arr1 = array(':p_sku' => $request['p_sku'], ':p_goods_code' => $request['p_goods_code']);
        $diy = load_model('prm/GoodsBarcodeModel')->get_diy_list($arr1);

        filter_fk_name($diy, array('goods_code|goods_code', 'spec1_code|spec1_code', 'spec2_code|spec2_code'));
        $response['diy'] = $diy;
    }

    function record_import(array &$request, array &$response, array &$app) {
        if ($request['type'] == 0) {
            $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        }
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        if(isset($request['excel_tpl'])&&$request['excel_tpl']!='undefined'){
            $response['excel_tpl']=$request['excel_tpl'];
        }else{
            $response['excel_tpl']='goods_record';
        }
    }

    function do_record_import(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $sku_arr = array();
        $read_data = array();
        $conf = require_conf('sys/goods_recode_import');
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }

        $is_lof = isset($request['is_lof']) ? $request['is_lof'] : 0;
        $sort = isset($request['sort']) ? $request['sort'] : 0;
        load_model('prm/GoodsModel')->read_csv_recode_sku($file, $sku_arr, $read_data, $is_lof);
        $check_arr = $conf[$request['act_type']]['import'];
        $ret = load_model($check_arr[0])->$check_arr[1]($request['id'], $sku_arr, $read_data, $is_lof, $sort);
        $response = $ret;
    }

    //导入商品
    function import_goods(array & $request, array & $response, array & $app) {
        set_uplaod($request, $response, $app);
        $ret = check_ext_execl();

        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
    }

    /**
     * 商品删除
     * 仅针对未启用的且未产生销售记录和库存记录的商品进行删除操作
     */
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('prm/GoodsModel')->goods_delete($request['goods_code']);
        exit_json_response($ret);
    }

    function select_spec1(array & $request, array & $response, array & $app) {
        
    }

    //查询规格1
    function search_change_spec1(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $data = load_model('prm/Spec1Model')->get_spec1_page($request);
        $response['rows'] = $data['data']['data'];
        $count = load_model('prm/Spec1Model')->spec1_count($request);
        $response['results'] = $count['count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    function select_spec2(array & $request, array & $response, array & $app) {
        
    }

    //查询规格2
    function search_change_spec2(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $data = load_model('prm/Spec2Model')->get_spec2_page($request);
        $response['rows'] = $data['data']['data'];
        $count = load_model('prm/Spec2Model')->spec2_count($request);
        $response['results'] = $count['count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    function goods_issue(array &$request, array &$response, array &$app) {

    }

    function goods_combo_select_inv(array &$request, array &$response, array &$app) {

        $app['page'] = 'NULL';
    }

    //查询套餐商品
    function goods_combo_select_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $request['page_size'] = $request['limit'];
        ;
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('prm/GoodsComboModel')->get_combo_goods($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    //查询商品
    function serach_barcode(array &$request, array &$response, array &$app) {

        $app['page'] = 'NULL';
    }

    function serach_barcode_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('prm/GoodsModel')->get_goods_barcode($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

}
