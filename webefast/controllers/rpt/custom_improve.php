<?php

require_lib('util/web_util', true);

class custom_improve {

    function do_list(array & $request, array & $response, array & $app) {

    }

    /**列表类型
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function statistic_list(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    /**图标类型
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function statistic_picture(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    /**获取图表数据
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_picture_data(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/CustomerModel')->get_picture_data($request);
        exit_json_response($ret);
    }

    /**
     * 导出csv
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $request['ctl_type'] == 'export';
        $ret = load_model('crm/CustomerModel')->get_improve_by_filter($request);
        $main_result = $ret['data']['data'];
        $str = "月份,新增会员数,新会员消费金额,老会员数,老会员消费金额\n";
        $str = iconv('utf-8', 'gbk', $str);

        foreach ($main_result as $value) {
            $value['add_month'] = iconv('utf-8', 'gbk', $value['add_month']);
            $value['new_custom_num'] = iconv('utf-8', 'gbk', $value['new_custom_num']);
            $value['new_consume_money'] = iconv('utf-8', 'gbk', $value['new_consume_money']);
            $value['old_custom_num'] = iconv('utf-8', 'gbk', $value['old_custom_num']);
            $value['old_consume_money'] = iconv('utf-8', 'gbk', $value['old_consume_money']);
            $str .= $value['add_month'] . "\t," . $value['new_custom_num'] . "," . $value['new_consume_money'] . "," . $value['old_custom_num'] . "," . $value['old_consume_money']
                 ."\n"; //用引文逗号分开
        }
        $filename = '会员统计分析' . date('Ymd') . '.csv'; //设置文件名
        header_download_csv($filename, $str); //导出
        echo $str;
        die;
    }

}
