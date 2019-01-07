<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
set_time_limit(0);

class activity {

    function do_list(array & $request, array & $response, array & $app) {
        $response['start_time'] = date("Y-m") . '-01 00:00:00';
    }

    /**
     * 启用停用
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0, 'check' => 5, 'is_null' => 4);
        $ret = load_model('crm/ActivityModel')->update_active($arr[$request['type']], $request['id']);

        exit_json_response($ret);
    }

    //活动详情
    function view(array &$request, array &$response, array &$app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('crm/ActivityModel')->get_by_id($request['_id']);
        } else {
            $ret['data']['activity_code'] = load_model('crm/ActivityModel')->create_fast_bill_sn();
        }
        $response['data'] = $ret['data'];
        $response['app_scene'] = $_GET['app_scene'];
        $response['_id'] = $request['_id'];

        $response['shop'] = load_model('base/ShopModel')->get_purview_shop();
    }

    //添加基础数据
    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('activity_code', 'activity_name', 'shop_code', 'start_time', 'end_time', 'event_desc'));
        $ret = load_model('crm/ActivityModel')->insert($data);
        exit_json_response($ret);
    }

    //商品库存同步设置
    function goods_stock_do_list(array & $request, array & $response, array & $app) {
        $ret = load_model('crm/ActivityModel')->get_by_id($request['_id']);
        $response['activity'] = $ret['data'];
        $response['_id'] = $request['_id'];
    }

    function goods_child_barcode(array & $request, array & $response, array & $app) {
        if (isset($request['activity_code']) && $request['activity_code'] != '') {
            $response['activity_code'] = $request['activity_code'];
        }
        if (isset($request['shop']) && $request['shop'] != '') {
            $response['shop'] = $request['shop'];
        }
    }

    //修改基础数据
    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('activity_code', 'activity_name', 'shop_code', 'start_time', 'end_time', 'event_desc'));
        $ret = load_model('crm/ActivityModel')->update($data);
        $ret['data'] = $request['activity_id'];
        exit_json_response($ret);
    }

    //添加活动商品
    function add_activity_goods(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/ActivityGoodsModel')->insert($request);
        exit_json_response($ret);
    }

    //一键删除
    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/ActivityGoodsModel')->delete($request['activity_code'], $request['tab']);
        exit_json_response($ret);
    }

    //删除
    function delete(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/ActivityGoodsModel')->delete_goods($request['activity_code'], $request['barcode']);
        exit_json_response($ret);
    }

    //商品导入
    function importGoods(array &$request, array &$response, array &$app) {
        $response['activity_code'] = $request['activity_code'];
        $response['type'] = $request['type'];
    }

    /**
     * 导入商品
     */
    function import_goods_upload(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $param = get_array_vars($request, array('activity_code', 'type'));
        $ret = load_model('crm/ActivityGoodsModel')->imoprt_activity_goods($param, $file);
        $response = $ret;
    }

    function view_table(array &$request, array &$response, array &$app) {
        if (isset($request['type']) && $request['type'] != '') {
            $response['type'] = $request['type'];
        }
        if (isset($request['code']) && $request['code'] != '') {
            $response['code'] = $request['code'];
        }
        if (isset($request['shop']) && $request['shop'] != '') {
            $response['shop_code'] = $request['shop'];
        }
        if (isset($request['status']) && $request['status'] != '') {
            $response['status'] = $request['status'];
        }
        if (isset($request['start_time']) && $request['start_time'] != '') {
            $response['start_time'] = $request['start_time'];
        }
        if (isset($request['end_time']) && $request['end_time'] != '') {
            $response['end_time'] = $request['end_time'];
        }
        if (isset($request['is_first']) && $request['is_first'] != '') {
            $response['is_first'] = $request['is_first'];
        }
    }

    /* 修改上报库存 */

    function do_edit_num(array &$request, array &$response, array &$app) {

        $ret = load_model('crm/ActivityGoodsModel')->edit_num_action($request);

        exit_json_response($ret);
    }

    /* 刷新 */

    function inv_fresh(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/ActivityGoodsModel')->inv_fresh($request);
        exit_json_response($ret);
    }

    //活动库存同步改成异步库存同步
    function create_sync_inv_task(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/ActivityGoodsModel')->create_sync_inv_task($request['code'], $request['shop']);
        exit_json_response($ret);
    }

    function sync_inv(array &$request, array &$response, array &$app) {
        //获取库存同步数据
        $type_arr = array(1, 0, 2);
        foreach ($type_arr as $type) {
            $_run = true;
            $i = 0;
            while ($_run) {
                //1次1000条
                $ret = load_model('crm/ActivityGoodsModel')->sync_inv($request['code'], $request['shop'], $type, $i);
                //如果错误便返回
                if (!empty($ret['data'])) {
                    $data = load_model("api/sys/ApiGoodsModel")->activity_goods_sync_goods_inv_action($ret['data']);
                } else {
                    $_run = false;
                    break;
                }
                if (count($ret['data']) < 1000) {
                    $_run = false;
                    break;
                }
                $i++;
            }
        }

        $ret = array('status' => 1, 'message' => '同步成功！');

        exit_json_response($ret);
    }

    function goods_log(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/ActivityModel')->get_by_id($request['_id']);
        $response['activity'] = $ret['data'];
        $response['_id'] = $request['_id'];
    }

    function lock_detail(array &$request, array &$response, array &$app) {
        if (isset($request['start_time']) && $request['start_time'] != '') {
            $response['start_time'] = $request['start_time'];
        }
        if (isset($request['end_time']) && $request['end_time'] != '') {
            $response['end_time'] = $request['end_time'];
        }
        if (isset($request['sku']) && $request['sku'] != '') {
            $response['sku'] = $request['sku'];
        }
        if (isset($request['activity_code']) && $request['activity_code'] != '') {
            $response['activity_code'] = $request['activity_code'];
        }
        if (isset($request['shop_code']) && $request['shop_code'] != '') {
            $response['shop_code'] = $request['shop_code'];
        }
    }

    function delete_activity(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/ActivityModel')->delete($request['code']);
        exit_json_response($ret);
    }

    function update_sell_data(array &$request, array &$response, array &$app) {
        $response = load_model('crm/ActivityGoodsModel')->get_activery_sell_data($request['activity_code'], 'goods_combo_barcode');
        $response = load_model('crm/ActivityGoodsModel')->get_activery_sell_data($request['activity_code'], 'goods_sku');
    }

    function get_pt_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('barcode'));
        $data = load_model('crm/ActivityGoodsModel')->get_pt_goods($request['shop_code'], $params);
        $response = array('rows' => $data);
    }

    function copy_activity(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/ActivityModel')->copy($request['code']);
        exit_json_response($ret);
    }

}
