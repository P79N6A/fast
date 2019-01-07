<?php

/**
 * 门店商品控制器相关业务
 */
require_lib('util/web_util', true);

class shop_goods {

    function do_list(array & $request, array & $response, array & $app) {
        //登录类型
        $response['login_type'] = CTX()->get_session('login_type');
        //当前门店
        $response['oms_shop_code'] = empty(CTX()->get_session('oms_shop_code')) ? CTX()->get_session('oms_shop_code') : '';
        //规格名称
        $response['spec'] = load_model("oms_shop/OmsShopModel")->get_spec_rename();

        //按钮权限
        $response['priv']['add_goods'] = load_model('sys/PrivilegeModel')->check_priv('prm/shop_goods/add_shop_goods');
        $response['priv']['update_status'] = load_model('sys/PrivilegeModel')->check_priv('prm/shop_goods/update_active');
        $response['priv']['batch_update_status'] = load_model('sys/PrivilegeModel')->check_priv('prm/shop_goods/batch_update_active');
        $response['priv']['update_price'] = load_model('sys/PrivilegeModel')->check_priv('prm/shop_goods/update_price');
        $response['priv']['import_goods'] = load_model('sys/PrivilegeModel')->check_priv('prm/shop_goods/import_data');
    }

    /**
     * @todo 获取列表点击展开的商品详细信息
     */
    function get_detail_by_code(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response['rows'] = load_model("prm/ShopGoodsModel")->get_detail_by_code($request['goods_code'], $request['shop_code']);
    }

    /**
     * @todo 添加门店商品
     */
    function add_shop_goods(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('prm/ShopGoodsModel')->add_goods($request);
    }

    /**
     * 启用停用
     */
    function update_active(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('id', 'goods_code', 'active'));
        $response = load_model('prm/ShopGoodsModel')->update_active($params);
    }

    /**
     * @todo 批量启用停用
     */
    function batch_update_active(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('prm/ShopGoodsModel')->batch_update_active($request);
    }

    /**
     * @todo 更新商品售价
     */
    function update_price(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('prm/ShopGoodsModel')->update_price($request);
    }

    /**
     * @todo 商品删除
     */
    function do_delete(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('prm/ShopGoodsModel')->goods_delete($request['id']);
    }

    /**
     * @todo 导入商品-页面
     */
    function import_goods(array & $request, array & $response, array & $app) {

    }

    /**
     * @todo 导入商品-上传文件
     */
    function import_upload(array & $request, array & $response, array & $app) {
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('pur/PlannedRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
        set_uplaod($request, $response, $app);
    }

    /**
     * @todo 导入商品-数据
     */
    function import_data(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $ret = load_model('prm/ShopGoodsModel')->import_goods_data($file);
        $response = $ret;
    }

}
