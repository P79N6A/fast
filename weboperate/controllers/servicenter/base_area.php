<?php

/*
 * 地址库
 */

class base_area {

    /**列表
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_list(array & $request, array & $response, array & $app) {

    }

    /**新增页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function detail(array & $request, array & $response, array & $app) {
        //国家
        $key = 1;
        $arr_country[$key][0] = '1';
        $arr_country[$key][1] = '中国';
        $response['area']['country'] = $arr_country;
        //取得省数据
        $arr_province[0][0] = '';
        $arr_province[0][1] = '请选择省';
        $arr_area_province = load_model('servicenter/BaseAreaModel')->get_area(1);
        $key = 1;
        foreach ($arr_area_province as $value) {
            $arr_province[$key][0] = $value['id'];
            $arr_province[$key][1] = $value['name'];
            $key++;
        }
        $response['area']['province'] = $arr_province;
        //取得市数据
        $arr_city[0][0] = '';
        $arr_city[0][1] = '请选择城市';
        $response['area']['city'] = $arr_city;

    }

    /**新增
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_add(array & $request, array & $response, array & $app) {
        $ret = load_model('servicenter/BaseAreaModel')->add_action($request);
        exit_json_response($ret);
    }

    function get_area(array &$request, array &$response, array &$app) {
        $parent_id = isset($request['parent_id']) ? $request['parent_id'] : 0;
        $ret = load_model('servicenter/BaseAreaModel')->get_area($parent_id);
        exit_json_response($ret);
    }

}