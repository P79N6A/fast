<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
set_time_limit(0);

class goods {

    function do_list(array &$request, array &$response, array &$app) {

    }

    //商品铺货
    function ph_list(array &$request, array &$response, array &$app) {

    }

    function get_sku_list_by_num_iid(array &$request, array &$response, array &$app) {
        $data = load_model("api/taobao/GoodsModel")->get_sku_list_by_num_iid($request['num_iid']);
        $response = array('rows' => $data);
    }

    //修改商家编码
    function update_code(array &$request, array &$response, array &$app) {
        if (!empty($request['sku_id'])) {
            $response['data'] = load_model("api/taobao/GoodsModel")->get_sku_by_skuid($request['sku_id']);
        } else {
            $response = load_model("api/taobao/GoodsModel")->get_by_id($request['num_iid']);
        }
        $response['data']['ES_frmId'] = $request['ES_frmId'];
    }

    //保存修改的商家编码
    function do_update_code(array &$request, array &$response, array &$app) {
        $response = load_model("api/taobao/GoodsModel")->update_outer_id($request['num_iid'], $request['outer_id'], $request['sku_id']);
    }

    /**
     * 商家编码匹配
     */
    function do_relation(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model("api/taobao/GoodsModel")->match_code();
    }

    /**
     * 商品规格匹配
     */
    function do_relation_gg(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model("api/taobao/GoodsModel")->match_spec();
    }

    function do_goods_init(array &$request, array &$response, array &$app) {
        $shop_code = $request['shop_code'];
        $response = load_model("api/taobao/GoodsModel")->do_goods_init($shop_code);
    }

}
