<?php

/**
 * 尺码层
 * @author WMH
 */
class size_layer {

    function set(array &$request, array &$response, array &$app) {
        $response['data'] = load_model('sys/ParamsModel')->get_param_set('size_layer');
    }

    function update_layer(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $param = get_array_vars($request, ['data', 'line', 'column']);
        $response = load_model('prm/SizeLayerModel')->update_layer($param);
    }

    function select(array &$request, array &$response, array &$app) {
        $response = load_model('prm/SizeLayerModel')->get_goods_info($request['goods_code'], $request['store_code'], $request['layer_line']);
    }

    function check_goods_layer(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('prm/SizeLayerModel')->check_goods_layer($request['goods_code']);
    }

    function add_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $param = get_array_vars($request, ['data', 'store_code', 'record_id', 'model']);
        $param['data'] = array_values($param['data']);
        $response = load_model('prm/SizeLayerOptModel')->add_detail($param);
    }

}
