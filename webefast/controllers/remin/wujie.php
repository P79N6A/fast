<?php

/**
 * 无界热敏
 * @author WMH
 */
class wujie {

    function get_jd_provider(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('remin/WujieModel')->get_provider_by_company($request['company_code']);
    }

    function get_jd_provider_sign_api(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('remin/WujieModel')->get_provider_sign_info_api($request['shop_code']);
    }

}
