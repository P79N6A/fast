<?php

/**
 *
 * 奇门配置
 */

class wms_qimen_config {

    /**列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array & $request, array & $response, array & $app) {

    }

    /**
     * 新增页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function detail(array & $request, array & $response, array & $app) {

    }

    /**
     * 新增
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_add(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('kh_id', 'qimen_id',));
        $ret = load_model('servicenter/WmsQimenModel')->add_action($params);
        exit_json_response($ret);
    }

    /**
     * 删除
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('wms_config_id',));
        $ret = load_model('servicenter/WmsQimenModel')->delete_action($params);
        exit_json_response($ret);
    }

}