<?php
require_lib('util/web_util', true);
class inventory_mgr{
    function inventory_compare(array &$request, array &$response, array &$app){
        $app['fmt'] = "json";
        //双十一当天不运行
        $date = date("m-d H:i:s", time());
        if (($date > "11-10 21:00:00") && ($date < "11-11 21:00:00")) {
            $msg = "双十一期间库存差异不允许对比";
            $response = array('status' => 1, 'msg' => $msg);
            return;
        }
        //618活动禁止同步
        $pause_start_time = date('Y') . "-06-17 22:59:00";
        $pause_end_time = date('Y') . "-06-18 12:01:00";
        $current_time = time();
        if ($current_time >= strtotime($pause_start_time) && $current_time <= strtotime($pause_end_time)) {
            $msg = "618期间库存差异不允许对比";
            $response = array('code' => -1, 'msg' => $msg);
            return;
        }
        $obj = load_model('rpt/SellGoodsReportModel');
        $obj->create_inventory_compare();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }
}
