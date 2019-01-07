<?php
require_lib('util/web_util', true);
require_model('rpt/SellGoodsReportModel', true);

class goods_report {
    // 商品销售排行分析
    function trends(array &$request, array &$response, array &$app) {

    }

    // 商品编码
    function trends_goods_code(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    // 商品编码
    function trends_goods_code_count(array &$request, array &$response, array &$app) {
        //$app['fmt'] = 'json';
        $app['page'] = 'NULL';
        $m = new SellGoodsReportModel();
        $response = $m->trends_goods_code_count($request);
    }

    // 商品条形码
    function trends_goods_barcode(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    // 商品条形码
    function trends_goods_barcode_count(array &$request, array &$response, array &$app) {
        //$app['fmt'] = 'json';
        $app['page'] = 'NULL';
        $m = new SellGoodsReportModel();
        $response = $m->trends_goods_barcode_count($request);
    }
}
