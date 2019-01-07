<?php

require_lib('util/web_util', true);

class api_config {

    function do_list(array &$request, array &$response, array &$app) {

             load_model('mid/MidApiConfigModel')->init_mid_service();

            $response['service_data'] = load_model('mid/MidApiConfigModel')->get_service_arr();
    }




    function del(array &$request, array &$response, array &$app) {
        $response = load_model('mid/MidApiConfigModel')->del($request['id'],$request['mid_code']);
        exit_json_response($response);
    }

}
