<?php

/*
 * 基础数据-RDS列表
 */

class Rdsdata {

    //基础数据-rds列表
    function update_data(array & $request, array & $response, array & $app) {
        $response = load_model('basedata/RdsDataModel')->update_rds_all();
    }

}
