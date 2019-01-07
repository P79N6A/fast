<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class report_day {

    /**
     * 获取罗盘数据
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_data(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = load_model('oms/OmsEchartDataModel')->getData(0);
        $ret[0]['type'] = 'sell_num';
        $ret[0]['report_data'] = empty($data[0]) ? 0 : (int)$data[0];
        $ret[1]['type'] = 'sell_money';
        $ret[1]['report_data'] = $data[6];
        $ret[2]['type'] = 'wait_confirm';
        $ret[2]['report_data'] = empty($data[8]) ? 0 : (int)$data[8];
        $ret[3]['type'] = 'wait_create_waves';
        $ret[3]['report_data'] = empty($data[9]) ? 0 : (int)$data[9];
        $ret[4]['type'] = 'wait_scan';
        $ret[4]['report_data'] = empty($data[10]) ? 0 : (int)$data[10];
        $ret[5]['type'] = 'oms_send';
        $ret[5]['report_data'] = empty($data[4]) ? 0 : (int)$data[4];
        $response['data'] = $ret;
        $response['update_time'] = date('Y-m-d H:i:s');
    }


        function create_data(array &$request, array &$response, array &$app) {
            $app['fmt'] = 'json';
            load_model("oms/OmsReportDayModel")->create_data();
            $response['status'] = 1;
        }
}
