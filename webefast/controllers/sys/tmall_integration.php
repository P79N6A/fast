<?php

require_lib('util/web_util', true);

class tmall_integration {

    function do_list(array & $request, array & $response, array & $app) {
        
    }


    function report_count(array & $request, array & $response, array & $app){
        $app['fmt'] = 'json';
        $response = load_model('rpt/TmallIntegrationModel')->report_count($request);
    }
}
