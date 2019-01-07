<?php

/*
 * 仓库相关业务控制器
 */
require_lib('util/web_util', true);

class store_staff {

    /**列表页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array &$request, array &$response, array &$app) {

    }

    /**新增编辑页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function detail(array &$request, array &$response, array &$app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('base/StoreStaffModel')->get_by_id($request['_id']);
        }
        $response['staff_type'] = array(array('0' => '0', '1' => '拣货员'));
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
        $response['scene']=$app['scene'];
    }

    /**编辑
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $store_staff = get_array_vars($request, array('staff_name', 'staff_type', 'status'));
        $ret = load_model('base/StoreStaffModel')->update($store_staff, $request['staff_id']);
        $response = $ret;
    }

    /**新增
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_add(array &$request, array &$response, array &$app) {
        $store_staff = get_array_vars($request, array('staff_code', 'staff_name', 'staff_type', 'status'));
        $ret = load_model('base/StoreStaffModel')->insert($store_staff);
        $response = $ret;
    }

    /**删除
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/StoreStaffModel')->delete($request['staff_id']);
        exit_json_response($ret);
    }


    /**更新启用状态
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function update_active(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $active = array('enable' => 1, 'disable' => 0);
        $response = load_model('base/StoreStaffModel')->update_active($request['id'], $active[$request['type']]);
    }

}
