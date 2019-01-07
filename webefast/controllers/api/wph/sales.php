<?php

class sales {

    function do_list(array &$request, array &$response, array &$app) {
        $response['shop'] = load_model('base/ShopModel')->get_wepinhuijit_shop();
    }

    function get_sales_list(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('api/wph/WphSalesApiModel')->get_sales_data();
    }

}
