<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class running_account {

    function do_list(array &$request, array &$response, array &$app) {
        $login_type = CTX()->get_session('login_type');
        $response['login_type'] = $login_type;
    }
}
