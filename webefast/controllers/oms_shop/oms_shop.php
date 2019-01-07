<?php

/**
 * 门店订单业务
 */
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms_shop/OmsShopModel');
require_model('oms_shop/OmsShopOptModel');

class oms_shop {

    function do_list(array &$request, array &$response, array &$app) {
        $response = load_model("oms_shop/OmsShopModel")->get_spec_rename();
    }

    function view(array &$request, array &$response, array &$app) {
        $response['record'] = load_model('oms_shop/OmsShopOptModel')->get_record_data($request['record_code']);
    }

    function add(array &$request, array &$response, array &$app) {
        
    }

    /**
     * 读取详情页各部分信息
     */
    function component(array &$request, array &$response, array &$app) {
        $types = $request['components'];
        if ($request['type'] != 'all') {
            $types = explode(',', $request['type']);
        }

        //读取订单
        $response = load_model('oms_shop/OmsShopModel')->component($request['record_code'], $types);
        $response['add_his'] = isset($request['add_his']) ? $request['add_his'] : '';
        if (empty($response['record'])) {
            die(json_encode(array()));
        }
        $arr = array();
        $arr['record'] = $response['record'];
        foreach ($types as $type) {
            ob_start();
            $app['scene'] = $request['opt'];
            $path = get_tpl_path('oms_shop/shop_sell_record/get_' . $type);
            include $path;
            $ret = ob_get_contents();
            ob_end_clean();
            $arr[$type] = $ret;
        }
        if (empty($response['detail_list'])) {
            $response['detail_list']['record_code'] = $request['record_code'];
        }
        die(json_encode($arr));
    }

    /**
     * 根据订单号获取明细
     */
    function get_detail_by_code(array &$request, array &$response, array &$app) {
        $data = load_model("oms_shop/OmsShopModel")->get_detail_by_code($request['record_code'], 1);
        $response = array('rows' => $data);
    }

    /**
     * 付款页面
     */
    function pay(array &$request, array &$response, array &$app) {
        $response['record'] = load_model('oms_shop/OmsShopOptModel')->get_record_data($request['record_code']);
        $pay_way = load_model('base/PaymentModel')->get_by_page();
        $response['pay_way'] = $pay_way['data']['data'];
    }

    /**
     * 发货页面
     */
    function send(array &$request, array &$response, array &$app) {
        $response['record'] = load_model('oms_shop/OmsShopOptModel')->get_record_data($request['record_code']);
    }

    /**
     * 详情操作
     */
    function opt(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = !empty($request['data']) ? $request['data'] : array();
        $response = load_model('oms_shop/OmsShopOptModel')->opt_record($request['record_code'], $request['type'], $data);
    }

    /**
     * 新增明细
     */
    function opt_new_multi_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms_shop/OmsShopOptModel')->opt_new_multi_detail($request);
        $response['record'] = load_model('oms_shop/OmsShopModel')->get_record_by_code($request['record_code']);
    }

    /**
     * 保存详情各部分
     */
    function save_component(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms_shop/OmsShopModel')->save_component($request['record_code'], $request['type'], $request['data']);
    }

    /**
     * 保存明细
     */
    function opt_save_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $obj = new OmsShopOptModel();
        $response = $obj->opt_save_detail($request);
        $response['record'] = $obj->get_record_data($request['record_code']);
    }

    /**
     * 删除明细
     */
    function opt_delete_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $obj = new OmsShopOptModel();
        $response = $obj->opt_delete_detail($request['record_code'], $request['sell_goods_id']);
    }

    /**
     * 订单详情商品改款页
     */
    function change_goods_view(array &$request, array &$response, array &$app) {
        $response['cur_goods']['record_code'] = isset($request['record_code']) ? $request['record_code'] : '';
        $response['cur_goods']['goods_code'] = isset($request['goods_code']) ? $request['goods_code'] : '';
        $response['cur_goods']['sku'] = isset($request['sku']) ? $request['sku'] : '';
        $response['cur_goods']['num'] = isset($request['num']) ? $request['num'] : '';
        $response['cur_goods']['barcode'] = isset($request['barcode']) ? $request['barcode'] : '';
        $response['cur_goods']['avg_money'] = isset($request['avg_money']) ? $request['avg_money'] : '';
        $response['cur_goods']['sell_goods_id'] = isset($request['sell_goods_id']) ? $request['sell_goods_id'] : '';
        $response['cur_goods']['spec1_name'] = isset($request['spec1_name']) ? $request['spec1_name'] : '';
        $response['cur_goods']['spec2_name'] = isset($request['spec2_name']) ? $request['spec2_name'] : '';
    }

    /**
     * 订单详情商品改款页检索
     */
    function search_change_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response['rows'] = load_model('oms_shop/OmsShopModel')->get_change_goods($request);
    }

    /**
     * 改款商品添加
     */
    function opt_change_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $obj = new OmsShopOptModel();
        //删除当前明细
        $response1 = $obj->opt_delete_detail($request['record_code'], $request['sell_goods_id']);
        //添加明细
        $detail = array();
        $detail['record_code'] = $request['record_code'];
        $detail['data'][0] = load_model('prm/GoodsModel')->get_sku_list($request['sku']);
        $detail['data'][0]['num'] = $request['num'];
        $detail['data']['barcode'] = $request['barcode'];
        $detail['data'][0]['sum_money'] = $request['avg_money'];
        if (isset($request['is_gift']) && $request['is_gift'] == '1') {
            $detail['data'][0]['is_gift'] = $request['is_gift'];
        }
        $response = $obj->opt_new_multi_detail($detail);
    }

    //读取详情按钮权限
    function btn_check(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $obj = new OmsShopOptModel();
        $ret = $obj->check_opt_priv($request['record_code'], $request['opt_priv']);
        $response = $ret['data'];
    }

    /**
     * 沟通日志页面
     */
    function communicate(array &$request, array &$response, array &$app) {
        
    }

    /**
     * 沟通日志
     */
    function opt_communicate(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms_shop/OmsShopModel')->opt_communicate($request);
    }

    /**
     * 批量操作
     */
    function opt_batch(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $opt_type = $request['type'];
        $msgFaild = '';
        $obj = new OmsShopOptModel();
        foreach ($request['record_code_list'] as $code) {
            if (empty($code)) {
                continue;
            }

            if ($opt_type == 'pay') {
                $response = array('status' => 1, 'message' => '');exit;
            } else {
                $ret_opt = $obj->opt_record($code, $opt_type);
            }

            $msg = '';
            if ($ret_opt['status'] == '1') {
                $msg = '执行成功';
            } else {
                $msgFaild .= $code . '  ' . $ret_opt['message'] . ',<br/>';
            }
        }
        if (!empty($msgFaild)) {
            $msg .= sprintf("订单:<br/> %s", rtrim($msgFaild, ','));
        }

        $response = array('status' => 1, 'message' => $msg);
    }

}
