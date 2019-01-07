<?php

require_lib('util/web_util', true);

/**
 * 门店收银业务
 */
class cashier {

    function do_list(array &$request, array &$response, array &$app) {
        $user_code = CTX()->get_session('user_code');
        $user = load_model('sys/UserModel')->get_shop_user($user_code);
        if (!empty($user)) {
            $ret = load_model('base/ShopModel')->check_shop_active($user['shop_code']);
            if ($ret === TRUE) {
                $response = $user;
                $ret_param = load_model('sys/SysParamsModel')->get_val_by_code(array('ticket_print'));
                $response['ticket_print_power'] = $ret_param['ticket_print'];
            } else {
                $response['tips'] = "门店[<b>{$user['shop_name']}</b>]尚未营业，请先";
                $response['tips_name'] = '开始营业';
                $response['tips_url'] = '?app_act=base/shop_entity/do_list';
                $response['tab_name'] = '实体店铺';
                $app['tpl'] = 'common/page_power';
            }
        } else {
            $response['tips'] = "当前登录用户不是门店用户，请使用门店用户登录！";
            $response['tips_url'] = '';
            $app['tpl'] = 'common/page_power';
        }
        //是否开启clodop
        $arr = array( 'clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
    }

    /**
     * 扫描商品条码
     */
    function scan_barcode(array &$request, array &$response, array &$app) {
        $ret = load_model('oms_shop/CashierModel')->scan_barcode($request['barcode'], $request['shop_code']);
        if ($ret['status'] < 0) {
            $ret['message'] = $ret['scan_barcode'] . $ret['message'];
        }
        exit_json_response($ret);
    }

    /**
     * 修改商品
     */
    function get_change_goods(array &$request, array &$response, array &$app) {
        $ret = load_model('oms_shop/CashierModel')->get_change_goods($request);
        exit_json_response($ret);
    }

    function change_goods(array &$request, array &$response, array &$app) {
        $response['spec'] = load_model("oms_shop/OmsShopModel")->get_spec_rename();
    }

    /**
     * 获取页面显示时间，服务器时间
     */
    function get_time() {
        $ret[] = date('Y-m-d', time());
        $ret[] = date('h:i', time());
        $ret[] = date('N', time());
        exit_json_response($ret);
    }

    /**
     * 检查会员是否存在
     */
    function check_member(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/ClientModel')->get_by_field('client_tel', $request['tel']);
        exit_json_response($ret);
    }

    /**
     * 现金收银
     */
    function cash(array &$request, array &$response, array &$app) {
        $ret = load_model('oms_shop/OmsShopModel')->cashier_add($request);
        exit_json_response($ret);
    }

}
