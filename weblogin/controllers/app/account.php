<?php

class Account {

    function validate(array & $request, array & $response, array & $app) {
        $app['page'] = 'NULL';
    }

    function account_opt(array & $request, array & $response, array & $app) {
        $app['page'] = 'NULL';
    }

    function get_verify_code(array & $request, array & $response, array & $app) {
        //需验证百胜内部员工手机号码
        $auth = load_model('app/AccountModel')->is_auth_mobile($request['mobile_num']);
        if ($auth['status'] == -1) {
            exit_json_response($auth);
        }
        if ($request['action'] == 'create') {
            //创建账户时判断手机号码是否存在账号
            $is_exists = load_model('app/AccountModel')->is_exists($request['mobile_num']);
            if ($is_exists['status'] == -1) {
                exit_json_response($is_exists);
            }
        }
        $result = load_model('app/MobileVerifyCodeModel')->get_verify_code($request['mobile_num']);
        exit_json_response($result);
    }

    function check_verify_code(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('mobile_num', 'verify_code', 'action'));
        $r = load_model('app/MobileVerifyCodeModel')->check_verify_code($params);
        if ($r['status'] < 1) {
            exit_json_response($r);
        }
        $auth = load_model('app/AccountModel')->is_auth_mobile($params['mobile_num']);
        if ($auth['status'] == -1) {
            exit_json_response($auth);
        }
        $ret = load_model('app/AccountModel')->set_account($params);
        exit_json_response($ret);
    }

    function get_baison_info(array & $request, array & $response, array & $app) {
        $ret = load_model('app/AccountModel')->get_baison_info();
        exit_json_response($ret);
    }

}
