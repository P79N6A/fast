<?php

/**
 * 多包裹验货（后置打单）
 *
 * @author WMH
 */
class deliver_package {

    /**
     * 多包裹验货页面
     */
    function multi_examine(array &$request, array &$response, array &$app) {
        $presell_priv = load_model('sys/SysParamsModel')->get_val_by_code('is_more_deliver_package');
        if ($presell_priv['is_more_deliver_package'] != 1) {
            $response['tips_type'] = "more_package";
            $response['tips'] = "未开启多包裹发货，请先开启";
            $response['tips_name'] = '系统参数设置 > 配发货 > 支持多包裹发货';
            $response['tips_url'] = '?app_act=sys/params/do_list&page_no=waves';
            $response['tab_name'] = '系统参数设置';
            $app['tpl'] = 'common/page_power';
        } else {
            $response['sound'] = load_model('oms/DeliverRecordModel')->get_sound();
        }
    }

    /**
     * 检查扫描订单
     */
    function check_scan_record(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/DeliverPackageModel')->check_scan_record($request['sell_record_code']);
    }

    /**
     * 获取电子面单号
     */
    function get_waybill_multi(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/DeliverPackageOptModel')->get_waybill_multi($request['sell_record_code'], $request['package_num']);
    }

    /**
     * 获取包裹明细数据
     */
    function get_package_detail_by_page(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/DeliverPackageModel')->get_package_detail_by_page($request);
    }

    /**
     * 扫描条码
     */
    function scan_barcode(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/DeliverPackageOptModel')->scan_barcode($request['params']);
    }

    /**
     * 封包
     */
    function packet_package(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/DeliverPackageOptModel')->packet_package($request['params']);
    }

    /**
     * 更新打印状态
     */
    function print_update(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/DeliverPackageModel')->update(array('print_status' => 1), array('sell_record_code' => $request['params']['sell_record_code'], 'package_no' => $request['params']['package_no']));
    }

    /**
     * 更新发货单当前包裹号
     */
    function update_record_package_no(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/DeliverPackageOptModel')->update_exp('oms_deliver_record', array('package_no' => $request['package_no']), array('sell_record_code' => $request['sell_record_code'], 'is_cancel' => 0));
    }

    /**
     * 删除空包裹
     */
    function delete_package(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/DeliverPackageOptModel')->delete_package($request['params']);
    }

    /**
     * 重置当前包裹扫描数据
     */
    function clear_curr_package(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/DeliverPackageOptModel')->clear_curr_package($request['params']);
    }

    /**
     * 发货
     */
    function delivery(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/DeliverPackageOptModel')->delivery($request['params']);
    }

}
