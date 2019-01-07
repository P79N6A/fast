<?php

require_lib('util/web_util', true);

/**
 * 售后退货数据分析业务 
 */
class sell_return {

    function after_analysis(array &$request, array &$response, array &$app) {
        
    }

    /**
     * @todo 获取售后退货数据-明细
     */
    function after_sell_return(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    /**
     * @todo 售后退货数据统计-明细
     */
    function after_sell_return_count(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
        $response = load_model('rpt/SellReturnReportModel')->get_sell_return_count($request);
    }

    /**
     * @todo 获取售后退货数据-平台
     */
    function after_sale_channel(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    /**
     * @todo 售后退货数据统计-平台
     */
    function after_sale_channel_count(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
        $app['tpl'] = 'rpt/sell_return_after_sell_return_count';
        $response = load_model('rpt/SellReturnReportModel')->get_sale_channel_count($request);
    }

    /**
     * @todo 获取售后退货数据-店铺
     */
    function after_shop(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    /**
     * @todo 售后退货数据统计-店铺
     */
    function after_shop_count(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
        $app['tpl'] = 'rpt/sell_return_after_sell_return_count';
        $response = load_model('rpt/SellReturnReportModel')->get_shop_count($request);
    }

    /**
     * @todo 获取售后退货数据-退货原因
     */
    function after_return_reasons(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    /**
     * @todo 售后退货数据统计-退货原因
     */
    function after_return_reasons_count(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
        $app['tpl'] = 'rpt/sell_return_after_sell_return_count';
        $response = load_model('rpt/SellReturnReportModel')->get_return_reasons_count($request);
    }

}
