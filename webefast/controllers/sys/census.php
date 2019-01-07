<?php

/**
 * 数据看板
 *
 * @author WMH
 */
class census {

    public function board(array &$request, array &$response, array &$app) {
        $sys_params = load_model('sys/SysParamsModel')->get_val_by_code('census_board_target');
        $response['sales_target'] = empty($sys_params['census_board_target']) ? 0 : $sys_params['census_board_target'];
    }

    public function get_board_data(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/OmsCensusBoardModel')->get_board_data();
    }

}
