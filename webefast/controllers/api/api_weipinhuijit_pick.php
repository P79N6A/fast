<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class api_weipinhuijit_pick {

    //档期列表
    function do_list(array &$request, array &$response, array &$app) {
        $response['shop'] = load_model('base/ShopModel')->get_wepinhuijit_shop();
    }

    //详情
    function view(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitPickModel')->get_row(array('pick_no' => $request['pick_no']));
        //获取仓库名称
        $warehouse = load_model('api/WeipinhuijitWarehouseModel')->get_by_field('warehouse_code', $ret['data']['warehouse'], 'warehouse_name');
        $ret['data']['warehouse_name'] = $warehouse['data']['warehouse_name'];
        //获取店铺名称
        $shop_row = load_model('base/ShopModel')->get_by_code($ret['data']['shop_code']);
        $ret['data']['shop_name'] = $shop_row['data']['shop_name'];
        $response = $ret;
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] =isset($arr_spec1['goods_spec1'])?$arr_spec1['goods_spec1']:'' ;
    }

    //生成批发销货单校验
    function do_check(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitPickModel')->check_pick($request['pick_id']);
        $response = $ret;
    }

    //生成批发销货单校验
    function check_pick_more(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitPickModel')->check_pick_more($request['pick_ids']);
        $response = $ret;
    }

    //生成批发通知单页面(旧逻辑)
    function notice_record_view(array &$request, array &$response, array &$app) {
        $response['brand'] = load_model('prm/BrandModel')->get_purview_brand();
        //收货仓库
        $response['warehouse'] = load_model('api/WeipinhuijitPickModel')->weipinhui_warehouse();
        $pick_row = load_model('api/WeipinhuijitPickModel')->get_by_id($request['pick_id']);
        $response['pick'] = $pick_row['data'];
        //未出库的出库单
        $params = array('is_delivery' => 0, 'warehouse' => $pick_row['data']['warehouse']);
        $response['delivery'] = load_model('api/WeipinhuijitDeliveryModel')->get_all_delivery($params);
    }

    //生成批发销货单（新）
    function create_view(array &$request, array &$response, array &$app) {
        $shop_data = load_model('base/ShopModel')->get_by_code($request['shop_code']);
        $response['shop']['store_code'] = empty($shop_data['data']['send_store_code']) ? '' : $shop_data['data']['send_store_code'];
        $response['shop']['express_code'] = empty($shop_data['data']['express_code']) ? '' : $shop_data['data']['express_code'];

        $response['brand'] = load_model('prm/BrandModel')->get_purview_brand();

        $ret_pick = load_model('api/WeipinhuijitPickModel')->get_by_ids($request['pick_id']);
        $warehouse_name = load_model('api/WeipinhuijitWarehouseModel')->get_by_field('warehouse_code', $ret_pick['data'][0]['warehouse'], 'warehouse_code,warehouse_name,custom_code');
        $response['pick'] = $warehouse_name['data'];
            $custom_data=load_model('base/CustomModel')->get_by_code($response['pick']['custom_code']);
            $response['custom_name']=$custom_data['data']['custom_name'];

        //未出库的出库单
        $po_no_arr = array_unique(array_column($ret_pick['data'], 'po_no'));
        $po_no_str = implode(",", $po_no_arr);
        $params = array('is_delivery' => 0, 'warehouse' => $response['pick']['warehouse_code'], 'po_no' => $po_no_str);
        $response['delivery'] = load_model('api/WeipinhuijitDeliveryModel')->get_all_delivery($params);
        //通知单
        $response['notice'] = load_model('api/WeipinhuijitPickModel')->get_relation_notice($request['pick_id']);

        //系统参数控制供货价 含税/不含税
        $arr = array('supply_price');
        $params = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['supply_price'] = $params['supply_price'];
        $response['fx_custom'] = load_model('base/CustomModel')->get_useful_custom_arr();
    }

    //生成批发销货单
    function do_create(array &$request, array &$response, array &$app) {

        $ret = load_model('api/WeipinhuijitPickModel')->create($request);
        $response = $ret;
    }
    function custom_search(array &$request, array &$response, array &$app){

    }

    //生成批发销货单(未绑定通知单)
    function do_create_notice_record(array &$request, array &$response, array &$app) {
        $pick_id = $request['pick_id'];
        $store_code = $request['store_code'];
        $distributor_code = $request['distributor_code'];
        $ret = load_model('api/WeipinhuijitPickModel')->pick_create_by_unrelation_notice($pick_id, $store_code, $distributor_code);
        $response = $ret;
    }

    //生成批发销货单(已绑定通知单)
    function do_create_out_record(array &$request, array &$response, array &$app) {
        $pick_id = $request['pick_id'];
        $notice_record = $request['notice_record'];
        $ret = load_model('api/WeipinhuijitPickModel')->pick_create_by_notice($pick_id, $notice_record);
        $response = $ret;
    }

    /**
     * 根据拣货单获取生成的批发销货单
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_out_store_record_by_pick(array & $request, array & $response, array & $app) {
        $ret = load_model('api/WeipinhuijitStoreOutRecordModel')->get_out_record_by_pick_no($request['pick_no']);
        $response = array('rows' => $ret);
    }

    function get_notice_info(array &$request, array &$response, array &$app) {
        $ret = load_model('wbm/NoticeRecordModel')->get_by_code($request['notice_code']);
        $custom_data=load_model('base/CustomModel')->get_by_code($ret['data']['distributor_code']);
        $ret['data']['custom_name']=$custom_data['data']['custom_name'];
        $response = $ret['data'];
    }

    //更新供货价系统参数设置
    function update_price_params(array &$request, array &$response, array &$app) {
        $ret = load_model('api/WeipinhuijitPickModel')->update_price_params($request['value']);
        exit_json_response($ret);
    }

}
