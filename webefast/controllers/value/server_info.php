<?php

/*
 * 增值服务订单业务控制器
 */
require_lib('util/web_util', true);
require_lib('apiclient/AlipaymClient', true);
require_lib('util/oms_util', true);
require_model('value/ValueServerModel', true);

class server_info {

    /**服务订购列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array &$request, array &$response, array &$app) {
        $response['kh_id'] = CTX()->saas->get_saas_key();
    }

  //续费
  function renew_ali_pay(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('kh_id', 'vra_server_id'));
        $params['user_code'] = CTX()->get_session('user_code');
        $ret = load_model('value/ValueServerModel')->renew_ali_pay($params);
        exit_json_response($ret);
    }

}