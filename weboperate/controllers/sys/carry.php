<?php

/*
 * 结转
 */

class Carry {

    function add_db(array & $request, array & $response, array & $app) {
        $response['select_rds'] = load_model('clients/AlirdsModel')->get_select();
    }

    function do_add_db(array & $request, array & $response, array & $app) {
        $response = load_model('sys/SysCarryModel')->add_carry_db($request);
    }

    function add_db_kh(array & $request, array & $response, array & $app) {
        $response['select_kh'] = load_model('clients/ClientModel')->get_select_carry();
        $response['select_carry_db'] = load_model('sys/SysCarryModel')->select_carry_db();
    }

    function do_add_db_kh(array & $request, array & $response, array & $app) {
        $response = load_model('sys/SysCarryModel')->add_kh_carry_db($request);
    }

    function do_list(array & $request, array & $response, array & $app) {
        
    }

}
