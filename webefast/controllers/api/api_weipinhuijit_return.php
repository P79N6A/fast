<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class api_weipinhuijit_return {

    //退供单列表
    function do_list(array &$request, array &$response, array &$app) {
        $response['shop'] = load_model('base/ShopModel')->get_wepinhuijit_shop();
    }

    /**
     * 根据退供单单获取生成的批发退货单
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_return_record_by_sn(array & $request, array & $response, array & $app) {
        $ret = load_model('api/WeipinhuijitReturnModel')->get_return_record_by_sn($request['return_sn']);
        $response = array('rows' => $ret);
    }

    //详情
    function view(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitReturnModel')->get_row(array('return_sn' => $request['return_sn']));
        //获取关联批发退货单
        $r = load_model('api/WeipinhuijitReturnModel')->get_record($request['return_sn']);
        foreach ($r as $k => $v) {
            $p[] = $v['return_record_no'];
        }
        $record = implode(',', $p);
        $ret['data']['record_code'] = $record;
        //获取仓库名称
        $warehouse = load_model('api/WeipinhuijitWarehouseModel')->get_by_field('warehouse_code', $ret['data']['warehouse'], 'warehouse_name');
        $ret['data']['warehouse_name'] = $warehouse['data']['warehouse_name'];
        //获取店铺名称
        $shop_row = load_model('base/ShopModel')->get_by_code($ret['data']['shop_code']);
        $ret['data']['shop_name'] = $shop_row['data']['shop_name'];
        $response = $ret;
    }

    //生成批发退货单
    function create_view(array &$request, array &$response, array &$app) {
        // $ret_return = load_model('api/WeipinhuijitReturnModel')->get_by_ids($request['return_id']);
        //退单类型
        $response['return_type'] = load_model('wbm/ReturnNoticeRecordModel')->get_return_type();
        //通知单
        $response['notice'] = load_model('api/WeipinhuijitReturnModel')->get_relation_notice($request['return_id']);
    }

    //生成批发退货单
    function do_create(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitReturnModel')->create($request);
        $response = $ret;
    }

    function get_notice_info(array &$request, array &$response, array &$app) {
        $ret = load_model('wbm/ReturnNoticeRecordModel')->get_by_code($request['notice_code']);
        $response = $ret['data'];
    }

    //生成批发退货单校验
    function do_check(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitReturnModel')->check_return($request['return_id']);
        $response = $ret;
    }

    //批量生成批发退货单校验
    function check_return_more(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitReturnModel')->check_return_more($request['return_ids']);
        $response = $ret;
    }

    //获取退货单
    function get_return(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitReturnModel')->get_return();
        $response = $ret;
    }

    /**
     * 退供单下载页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function down(array &$request, array &$response, array &$app) {
        $response['shop'] = $shop_arr = load_model('base/ShopModel')->get_wepinhuijit_shop();
    }

    /**
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function down_refund(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('shop_code', 'start_time', 'end_time'));
        $params['sale_channel_code'] = 'weipinhui';
        $ret = load_model('api/WeipinhuijitReturnModel')->down_refund($params);
        exit_json_response($ret);
    }

}
