<?php

class pubdata_sync {

    //
    function do_index(array & $request, array & $response, array & $app) {
        
    }

    function download_express(array & $request, array & $response, array & $app) {
        //获取
        $response =   load_model('pubdata/TaobaoPubModel')->get_express();
    }

    function sync_express(array & $request, array & $response, array & $app) {
        //获取
        $response = load_model('pubdata/SyncPubModel')->sync_to_kh_db('base_express_company');
    }

    /**同步地址
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function sync_base_area(array & $request, array & $response, array & $app) {
        //获取
        $ret = load_model('pubdata/SyncPubModel')->sync_to_kh_db('base_area_new');
        exit_json_response($ret);
    }


    /**
     * 同步唯品会仓库
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function sync_weipinhuijit_warehouse(array & $request, array & $response, array & $app) {
        //获取
        $ret = load_model('pubdata/SyncPubModel')->sync_to_kh_db('api_weipinhuijit_warehouse');
        exit_json_response($ret);
    }

    /**
     * 产品信息回溯
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function product_information_tracebacke(array & $request, array & $response, array & $app) {
        $ret = load_model('pubdata/SyncPubModel')->product_inform_back();
        exit_json_response($ret);
    }

    /**
     * 登录信息回溯
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function login_information_tracebacke(array & $request, array & $response, array & $app) {
        $ret = load_model('pubdata/SyncPubModel')->login_inform_back();
        exit_json_response($ret);
    }

}
