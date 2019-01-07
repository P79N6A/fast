<?php

require_lib('util/web_util', true);

class kisdee {

    /**
     * 销售日报列表
     */
    function sell_daily_list(array &$request, array &$response, array &$app) {
        $response['store'] = load_model('kis/KisdeeModel')->get_sys_api_store();
        $response['shop'] = load_model('base/ShopModel')->get_purview_shop();
        $response['record_type'] = load_model('kis/KisdeeModel')->get_select_record_type();
        $response['is_on_config'] = load_model('sys/KisdeeConfigModel')->is_enable_config();
    }

    /**
     * 销售日报详情
     */
    function sell_daily_detail(array &$request, array &$response, array &$app) {
        $response['data'] = load_model('kis/KisdeeModel')->get_sell_daily_by_id($request['id']);
    }

    /**
     * 生成销售日报页面
     */
    function sell_daily_build(array &$request, array &$response, array &$app) {

    }

    /**
     * 生成销售日报
     */
    function create_sell_daily(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('record_type', 'record_date', 'shop_code', 'remark'));
        $response = load_model('kis/KisdeeModel')->create_sell_daily($data);
    }

    /**
     * 上传到金蝶
     */
    function opt_upload(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('record_code', 'record_type'));
        $response = load_model('kis/KisdeeUploadModel')->upload_sell_daily($params);
    }

    /**
     * 批量上传到金蝶
     */
    function opt_upload_batch(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('params'));
        $response = load_model('kis/KisdeeUploadModel')->batch_upload_sell_daily($params['params']);
    }

    /**
     * 获取选择仓库值--暂作废
     */
    function select_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('kis/KisdeeModel')->get_store_by_page($request);
        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

}
