<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sendorder
 *
 * @author wq
 */
class otherapi {

    function send_order(array & $request, array & $response, array & $app) {
        $request['source'] = isset($request['source']) ? $request['source'] : 'houtai';
        $request['type'] = isset($request['hlhdj']) ? $request['type'] : 'hlhdj';
        load_model('api/pushapi/OrderSendModel')->exec_send_api();
        $response['status'] = 1;
    }

}
