<?php
require_lib('util/web_util', true);
require_model('rpt/SellRecordReportModel', true);

class sell_record
{
    // 订单发货数据分析
    function shipped(array &$request, array &$response, array &$app) {

    }

    // 销售订单
    function shipped_sell_record(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    // 销售订单
    function shipped_sell_record_count(array &$request, array &$response, array &$app) {
        //$app['fmt'] = 'json';
        $app['page'] = 'NULL';
        $m = new SellRecordReportModel();
        $response = $m->shipped_sell_record_count($request);
    }

    // 销售渠道
    function shipped_sale_channel(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    // 销售渠道
    function shipped_sale_channel_count(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
        $app['tpl'] = 'rpt/sell_record_shipped_sell_record_count';
        $m = new SellRecordReportModel();
        $response = $m->shipped_sale_channel_count($request);
    }

    // 商店
    function shipped_shop(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    // 商店
    function shipped_shop_count(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
        $app['tpl'] = 'rpt/sell_record_shipped_sell_record_count';
        $m = new SellRecordReportModel();
        $response = $m->shipped_shop_count($request);
    }

    // 仓库
    function shipped_store(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    // 仓库
    function shipped_store_count(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
        $app['tpl'] = 'rpt/sell_record_shipped_sell_record_count';
        $m = new SellRecordReportModel();
        $response = $m->shipped_shop_count($request);
    }

    // 配送方式
    function shipped_express(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    // 配送方式
    function shipped_express_count(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
        $app['tpl'] = 'rpt/sell_record_shipped_sell_record_count';
        $m = new SellRecordReportModel();
        $response = $m->shipped_shop_count($request);
    }

}
