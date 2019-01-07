<?php

require_lib('util/web_util', true);

class sfc {

    function config_add(array &$request, array &$response, array &$app) {
        if ($request['_id']) {
            $ret = load_model('remin/SfcModel')->get_by_id($request['_id']);
            $response['data'] = $ret['data'];
        }
    }

    function do_add(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('remin/SfcModel')->add_config($request);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('remin/SfcModel')->update_config($request);
    }
    
    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('remin/SfcModel')->delete($request['id']);
        exit_json_response($ret);
    }
    
}

?>