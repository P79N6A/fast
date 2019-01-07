<?php

/**
 * 公共模板
 *
 * @author WMH
 */
class select_comm {

    /**
     * 选择快递
     */
    function express(array & $request, array & $response, array & $app) {
        $app['page'] = 'NULL';
    }

    /**
     * 选择快递数据源
     */
    function express_data(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $request['data_type'] = isset($request['data_type']) ? $request['data_type'] : '';

        $result = load_model('base/ShippingModel')->get_select_comm_express_data($request);
        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    /**
     * 商品选择-尺码层
     */
    function goods_layer(array & $request, array & $response, array & $app) {
        $app['page'] = 'NULL';
        $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
    }

}
