<?php

/**
 * 商品控制器相关业务
 * @author dfr
 *
 */
require_lib('util/web_util', true);

class goods_inv {

    function do_list(array & $request, array & $response, array & $app) {
        $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
        //品牌  start
        $response['brand'] = $this->get_purview_brand();
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        if (isset($request['mode'])) {
            $response['mode'] = $request['mode'];
        }
        //获取系统参数 是否启用批次
        $lof_status = load_model("sys/SysParamsModel")->get_val_by_code(array("lof_status"));
        $response['lof_status'] = $lof_status['lof_status'];

        $user_id = CTX()->get_session('user_id');
        $user = load_model('sys/UserModel')->query_by_id($user_id);
        $response['store_code'] = $user['data']['type'] > 0 ? $user['data']['relation_shop'] : '';
        //分销商登录
        $response['login_type'] = CTX()->get_session('login_type');
    }

    function get_purview_brand() {
        //品牌  start
        $arr_brand = load_model('prm/BrandModel')->get_purview_brand();
        $key = 0;
        foreach ($arr_brand as $value) {
            $arr_brand[$key][0] = $value['brand_code'];
            $arr_brand[$key][1] = $value['brand_name'];
            $key++;
        }
        return $arr_brand;
    }
    function get_inv_summary(array & $request, array & $response, array & $app){
        $response = load_model('fx/GoodsInvModel')->get_summary($request);
    }

}
