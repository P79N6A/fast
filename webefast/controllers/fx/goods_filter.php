<?php

/**
 * 商品控制器相关业务
 * @author dfr
 *
 */
require_lib('util/web_util', true);
require_lib('comm_util');

class Goods_filter {

    function do_list(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/GoodsFilterModel')->get_filter_code();
        if ($ret['status'] != 1) {
            $response['data']['filter_code'] = '';
        } else {
            $response['data']['filter_code'] = $ret['data']['filter_code'];
        }

    }

    function save_filter_code(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/GoodsFilterModel')->save_filter_code($request['filter_code']);
        exit_json_response($ret);
    }

}
