<?php

class pubdata {

    function express_list(array & $request, array & $response, array & $app) {
        
    }


    /**
     * 新增快递
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function express_detail(array & $request, array & $response, array & $app) {

    }

    /**
     * 新增
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_add(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('company_code', 'company_name', 'rule'));
        $ret = load_model('pubdata/BaseExpressCompanyModel')->add_action($params);
        exit_json_response($ret);
    }


}
