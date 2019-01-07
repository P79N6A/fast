<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
set_time_limit(0);

class api_weipinhuijit_po {

    //档期列表
    function do_list(array &$request, array &$response, array &$app) {
        $response['shop'] = load_model('base/ShopModel')->get_wepinhuijit_shop();
    }

    //绑定批发通知单
    function relation_notice(array &$request, array &$response, array &$app) {
        $response['data']['po_id'] = $request['po_id'];
        $response['data']['ES_frmId'] = $request['ES_frmId'];
    }

    //绑定库存锁定单
    function relation_lock(array &$request, array &$response, array &$app) {
        $response['data']['po_id'] = $request['po_id'];
        $response['data']['ES_frmId'] = $request['ES_frmId'];
    }

    /**
     * 生成拣货单页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function create_pick_by_warehouse(array &$request, array &$response, array &$app) {
        $response['po_no'] = $request['po_no'];
        load_model('api/WeipinhuijitPoModel')->update_po($request['id']);
        $po_info = load_model('api/WeipinhuijitPoModel')->get_by_field('po_no', $request['po_no']);
        $response['data'] = $po_info['data'];
    }

    //绑定批发通知单绑定逻辑
    function do_relation(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitPoModel')->relatiion_notice($request['po_id'], $request['notice_record_id']);
        $response = $ret;
    }

    /**绑定库存锁定单
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_relation_lock(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitPoModel')->relation_lock($request['po_id'], $request['lock_record_id']);
        exit_json_response($ret);
    }

    //解绑
    function unrelation(array &$request, array &$response, array &$app) {
        if($request['relation_type']==0){
            //解绑通知单
            $ret = load_model('api/WeipinhuijitPoModel')->unrelation_notice($request['po_id']);
        }else{
            //解绑锁定单
            $ret = load_model('api/WeipinhuijitPoModel')->unrelation_lock($request['po_id']);
        }
        $response = $ret;
    }

    //获取档期
    function get_po(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitPoModel')->get_po();
        $response = $ret;
    }
    
    /**
     * 更新档期未拣货数
     */
    function update_po(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ret = load_model('api/WeipinhuijitPoModel')->update_po($request['id']);
        $response = $ret;
    }

    //创建拣货单
    function create_pick(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitPoModel')->create_pick($request['po_id'], $request['warehouse_code']);
        $response = $ret;
    }

    //获取拣货单
    function get_pick(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitPoModel')->get_pick($request['po_id']);
        $response = $ret;
    }

    /**
     * 多PO创建拣货单(同时获取拣货单信息) 2.0
     */
    function multi_po_create_pick(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $param = get_array_vars($request, array('po_no'));
        $ret = load_model('api/WeipinhuijitPoModel')->multi_po_create_pick($param);
        $response = $ret;
    }

    /**
     * 批量获取拣货单信息 2.0
     */
    function batch_get_pick(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $param = get_array_vars($request, array('po_no'));
        $ret = load_model('api/WeipinhuijitPoModel')->batch_sync_pick($param);
        $response = $ret;
    }


    function select_po(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    function select_action(array &$request, array &$response, array &$app) {
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('api/WeipinhuijitPoModel')->get_by_page($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

}
