<?php

/**
 * 商品控制器相关业务
 * @author dfr
 *
 */
require_lib('util/web_util', true);
require_lib('comm_util');

class Goods {

    function do_list(array & $request, array & $response, array & $app) {
        //类别 start
        $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
        //$response['category'] = array();
        //品牌  start
        $response['brand'] = $this->get_purview_brand();
        //年份 start
        $response['year'] = $this->get_year();
        //季节 start
        $response['season'] = $this->get_season();
        //仓库
        $response['store'] = load_model('base/StoreModel')->get_fx_store();
        //分销商登录
        $response['login_type'] = CTX()->get_session('login_type');
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
    
    //单个添加购物车
    function add_one_shopping_cart(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('sku', 'lof_no', 'purchase_num', 'store_code','spec_info','effec_num'));
        $ret = load_model('prm/GoodsModel')->add_one_shopping_cart($params);
        exit_json_response($ret);
    }
    
    function shopping_cart_view (array &$request, array &$response, array &$app) {
        $response = load_model('prm/GoodsModel')->fx_goods_count();
    }
    //清空商品
    function click_clear (array &$request, array &$response, array &$app) {
        $ret = load_model('prm/GoodsModel')->click_clear();
        exit_json_response($ret);
    }
    //提交采购
    function submit_purchase(array &$request, array &$response, array &$app) {
        $ret = load_model('prm/GoodsModel')->submit_purchase($request['shopping_ids']);
        exit_json_response($ret);
    }
    //查看库存是否不足
    function is_store_num(array &$request, array &$response, array &$app) {
        $ret = load_model('prm/GoodsModel')->is_store_num($request);
        exit_json_response($ret);
    }
    //修改采购数量
    function edit_purchase_num(array &$request, array &$response, array &$app) {
        $ret = load_model('prm/GoodsModel')->edit_purchase_num($request['shopping_id'],$request['purchase_num']);
        exit_json_response($ret);
    }
    //获取商品指定分销商
    function get_goods_custom_list(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/GoodsModel')->get_goods_custom_list($request['goods_code']);
        exit_json_response($ret);
    }
    //设置分销款
    function set_custom_money(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/GoodsModel')->set_custom_money($request['goods_list'],$request['is_goods_custom']);
        exit_json_response($ret);
    }
    //添加指定分销商
    function set_goods_custom(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/GoodsModel')->set_goods_custom($request['goods_list'],$request['custom_code']);
        exit_json_response($ret);
    }
    //保存分销商价格
    function save_fx_price(array &$request, array &$response, array &$app) {
        $param = get_array_vars($request, array('fx_price', 'goods_code', 'custom_code'));	
        $ret = load_model('fx/GoodsModel')->save_fx_price($param);
        exit_json_response($ret);
    }
    //删除指定分销商
    function delete_custom(array &$request, array &$response, array &$app) {
        $param = get_array_vars($request, array( 'goods_code', 'custom_code'));	
        $ret = load_model('fx/GoodsModel')->delete_custom($param);
        exit_json_response($ret);
    }
    //保存分销商折扣
    function save_fx_rebate(array &$request, array &$response, array &$app) {
        $param = get_array_vars($request, array('fx_rebate', 'goods_code', 'custom_code'));	
        $ret = load_model('fx/GoodsModel')->save_fx_rebate($param);
        exit_json_response($ret);
    }

   /**
    * 导入
    * @param array $request
    * @param array $response
    * @param array $app
    */
    function import(array & $request, array & $response, array & $app) {
        
    }

    function import_goods(array & $request, array & $response, array & $app) {
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
        set_uplaod($request, $response, $app);
    }

    /**
     * 导入操作
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_import_action(array & $request, array & $response, array & $app) {
        //分销商登录
        $response['login_type'] = CTX()->get_session('login_type');
        $prive = load_model('sys/PrivilegeModel')->check_priv('fx/goods/import_goods_fx');
        if ($prive && $response['login_type'] != 2) {
           $app['fmt'] = 'json';
            $file = $request['url'];
            if (empty($file)) {
                $response = array(
                    'status' => 0,
                    'type' => '',
                    'msg' => "请先上传文件"
                );
            }
            $ret = load_model('fx/GoodsModel')->imoprt_detail($file);
        } else {
            $ret = array(
                'status' => '-1',
                'data' => '',
                'message' => '请先获取权限！'
            );
        }
        exit_json_response($ret);
    }

    function select_fx_goods(array &$request, array &$response, array &$app) {
        $brand = load_model('prm/BrandModel')->get_code_name();
        $response['selection']['brand'] = json_encode($brand);
        $year = load_model('base/YearModel')->get_code_name();
        $response['selection']['year'] = json_encode($year);
        $season = load_model('base/SeasonModel')->get_code_name();
        $response['selection']['season'] = json_encode($season);
        $category = load_model('prm/CategoryModel')->get_category_trees();
        $new_category=array();
        foreach ($category as $value){
            $new_category[$value['category_code']]=$value['category_name'];
        }
        $response['selection']['category'] = json_encode($new_category);
        $app['page'] = 'NULL';
    }

    function select_action(array &$request, array &$response, array &$app) {
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $request['is_custom_money'] = '0';
        $request['status'] = '0';
        $result = load_model('prm/GoodsModel')->get_by_page($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }
    
    
    function add_fx_goods(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/GoodsModel')->add_fx_goods($request['data']);
        exit_json_response($ret);
    }
    
    //获取商品指定barcode
    function get_goods_barcode_list(array &$request, array &$response, array &$app) {
        $param = get_array_vars($request, array('goods_code', 'is_custom', 'fx_price'));	
        $ret = load_model('fx/GoodsModel')->get_goods_barcode_list($param);
        exit_json_response($ret);
    }
    
    //一键清除分销款商品
    function remove_all_goods(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/GoodsModel')->remove_all_goods($request['obj']);
        exit_json_response($ret);
    }
    //一键添加所有商品
    function set_all_goods_fx(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/GoodsModel')->set_all_goods_fx($request['filter']);
        exit_json_response($ret);
    }
}
