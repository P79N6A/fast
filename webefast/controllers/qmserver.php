<?php

/**
 * 奇门官方接口业务对接
 *
 * @author WMH
 */
class qmserver {

    function router(array &$request, array &$response, array &$app) {
        header('Content-Type: text/json;charset=UTF-8');

        //获取客户ID
        $shop_data = load_model('api/ApiKehuModel')->get_kh_id_by_shop($request['sellerNick']);
        if (empty($shop_data)) {
            echo '{"success":false,"errorCode":"modify-address-forbid","errorMsg":"sellerNick无效"}';
            die;
        }
        //切换数据库
        $status = load_model('api/ApiKehuModel')->change_db_conn($shop_data['kh_id']);
        if ($status === false) {
            echo '{"success":false,"errorCode":"modify-address-forbid","errorMsg":"sellerNick无效"}';
            die;
        }

        $this->filterRequest($request);
        $request['app_key'] = $app['key'];

        $json_resp = load_model('api/qimen/QimenAuthApiModel')->exec_api($request);
        echo $json_resp;
        die;
    }

    function filterRequest(&$request) {
        if (isset($request['s_c_c_k'])) {
            unset($request['s_c_c_k']);
        }
        if (isset($request['fastappsid'])) {
            unset($request['fastappsid']);
        }

        foreach ($request as &$val) {
            $val = htmlspecialchars_decode($val);
        }

        return TRUE;
    }

}
