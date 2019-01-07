<?php

class erpapi {
    function qimen_api(array &$request, array &$response, array &$app) {

        $ret = load_model('erp/qimen/QimenOpenAPIModel')->exec_api($_REQUEST);

        echo $ret;
        die;
    }
}
