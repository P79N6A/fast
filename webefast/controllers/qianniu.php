<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of qianniu
 *
 * @author wq
 */
class qianniu {

    //put your code here
    function login_by_qn(array & $request, array & $response, array & $app) {
        //error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
        $ret = load_model('common/OspSaasModel')->osp_login_change($request);

        if ($ret['status'] < 0) {
            $response = $ret;
        } else {
            $response = load_model('sys/EfastUserModel')->login_qn($ret['data']);
        }

        if ($response['status'] > 0) {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
            if (isset($ret['data']['act'])) {
                $url .="?app_act=" . $ret['data']['act'];
            }

            echo "<script>location.href='{$url}';</script>";
        } else {
            echo $response['message'];
        }
        die;
    }

    function request_api(array & $request, array & $response, array & $app) {
        $response = load_model('qianniu/QianNiuApiModel')->exec_api($request);
        echo json_encode($response);
        die;
    }

    function upload_data(array & $request, array & $response, array & $app) {
        load_model('qianniu/TicketModel')->hanlder_tickets(1);
    }

    function dwonload(array & $request, array & $response, array & $app) {

        load_model('qianniu/TicketModel')->download_tickets();
    }

    function multi_account(array & $request, array & $response, array & $app) {
        load_model('qianniu/TicketModel')->multi_account();
    }

    function exec_ticket_all(array & $request, array & $response, array & $app) {

        load_model('qianniu/TicketModel')->exec_ticket_all();
    }
    function hanlder_tickets(array & $request, array & $response, array & $app) {
        $request['id'] = '528112804';
        load_model('qianniu/TicketModel')->hanlder_tickets($request['id']);
    }
    function exec_ticket(array & $request, array & $response, array & $app) {
        $ticket_id = $request['ticket_id'];
        $response = load_model('qianniu/TicketHanlderModel')->exec_ticket($ticket_id);
    }

}
