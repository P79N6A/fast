<?php

require_lib('util/web_util', true);

class custom_consume {

    function do_list(array & $request, array & $response, array & $app) {
        
    }

    /*     * 获取图表数据
     * @param array $request
     * @param array $response
     * @param array $app
     */

    function get_consume_data(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/CustomerModel')->get_consume_data($request);
        exit_json_response($ret);
    }

    /**
     * 导出csv
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $data = load_model("crm/CustomerModel")->get_consume_data($request);
        $str = "排名,省份,消费金额\n";
        $str = iconv('utf-8', 'gbk', $str);
        $list_data = $data['list_data'];
        foreach ($list_data as $value) {
            $value['order'] = iconv('utf-8', 'gbk', $value['order']);
            $value['name'] = iconv('utf-8', 'gbk', $value['name']);
            $value['value'] = iconv('utf-8', 'gbk', $value['value']);
            $str .= $value['order'] . "," . $value['name'] . "\t," . $value['value']
                    . "\n"; //用引文逗号分开
        }
        $filename = '会员消费金额分析' . date('Ymd') . '.csv'; //设置文件名
        header_download_csv($filename, $str); //导出
        echo $str;
        die;
    }

}
