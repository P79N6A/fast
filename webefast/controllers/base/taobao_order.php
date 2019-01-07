<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms/TaobaoRecordModel', true);
require_model('oms/SellRecordFixModel', true);
require_model('oms/SellRecordOptModel', true);

class taobao_order {
    //淘宝分销列表
    function td_list(array &$request, array &$response, array &$app) {
    	$response['source'] = load_model('base/SaleChannelModel')->get_data_code_map();
    }
    

    //淘宝分销商品列表
    function goods_list(array &$request, array &$response, array &$app) {
        $response['source'] = load_model('base/SaleChannelModel')->get_data_code_map();
    }


}