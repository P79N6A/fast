<?php

/**
 * 预售计划
 * @author WMH
 */
class presell {

    /**
     * 预售计划列表
     */
    function plan_do_list(array &$request, array &$response, array &$app) {
        $presell_priv = load_model('sys/SysParamsModel')->get_val_by_code('presell_plan');
        if ($presell_priv['presell_plan'] != 1) {
            $response['tips_type'] = "presell";
            $response['tips'] = "预售功能未开启，请先开启";
            $response['tips_name'] = '系统参数设置 > 运营 > 预售计划';
            $response['tips_url'] = '?app_act=sys/params/do_list&page_no=op';
            $response['tab_name'] = '系统参数设置';
            $app['tpl'] = 'common/page_power';
        } else {
            $response['shop'] = load_model('base/ShopModel')->get_purview_shop();
        }
    }

    /**
     * 预售计划添加页面
     */
    function plan_add(array &$request, array &$response, array &$app) {
        $response['plan_code'] = load_model('op/presell/PresellModel')->create_fast_bill_sn();
    }

    /**
     * 预售计划添加
     */
    function do_add(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = get_array_vars($request, array('plan_code', 'plan_name', 'start_time', 'end_time', 'shop_code'));
        $response = load_model('op/presell/PresellModel')->create_presell_plan($data);
    }

    /**
     * 预售计划编辑
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = array('plan_code' => $request['parameterUrl']['plan_code'], 'start_time' => $request['parameter']['plan_start_time'], 'end_time' => $request['parameter']['plan_end_time']);
        $response = load_model('op/presell/PresellModel')->edit_presell_plan($params);
    }

    /**
     * 预售计划删除
     */
    function do_delete(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('op/presell/PresellModel')->delete_presell_plan($request['plan_code']);
    }

    /**
     * 预售终止
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function exit_now(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('op/presell/PresellModel')->exit_presell_plan($request['plan_code']);
    }

    /**
     * 预售计划详情
     */
    function plan_detail(array &$request, array &$response, array &$app) {
        $data = load_model('op/presell/PresellModel')->get_presell_plan_by_page(array('plan_code' => $request['plan_code']));
        $response['data'] = $data['data']['data'][0];
        $response['sync_priv'] = load_model('sys/PrivilegeModel')->check_priv('op/presell/plan_sync_check');
        $response['delete_priv'] = load_model('sys/PrivilegeModel')->check_priv('op/presell/do_delete');
        $response['scene'] = $app['scene'];
        $ok = get_theme_url('images/ok.png');
        $no = get_theme_url('images/no.gif');
        $is_check_src = ($response['data']['exit_status'] == 1) ? $ok : $no;
        $response['data']['exit_status_src'] = "<img src='{$is_check_src}'>";
    }

    /**
     * 获取预售计划信息
     */
    function get_presell_plan_info(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = load_model('op/presell/PresellModel')->get_presell_plan_by_page(array('plan_code' => $request['plan_code']));
        $response['data'] = $data['data']['data'][0];
    }

    /**
     * 预售计划查看
     */
    function plan_view(array &$request, array &$response, array &$app) {
        $this->plan_detail($request, $response, $app);
        $app['tpl'] = 'op/presell_plan_detail';
    }

    /**
     * 预售计划编辑
     */
    function plan_edit(array &$request, array &$response, array &$app) {
        $this->plan_detail($request, $response, $app);
        $app['tpl'] = 'op/presell_plan_detail';
    }

    /**
     * 同步检查
     */
    function plan_sync_check(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('plan_code'));
        $response = load_model('op/presell/PresellModel')->sync_presell_inv_check($params);
    }

    /**
     * 同步预售库存
     */
    function plan_sync(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('plan_code'));
        $response = load_model('op/presell/PresellModel')->sync_presell_inv($params);
    }

    /**
     * 获取barcode关联的平台商品
     */
    function get_pt_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('plan_code', 'barcode'));
        $data = load_model('op/presell/PresellDealPtGoodsModel')->get_presell_pt_goods($params);
        $response = array('rows' => $data);
    }

    /**
     * 添加预售商品明细
     */
    function do_add_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('plan_code', 'data'));
        $response = load_model('op/presell/PresellDetailModel')->add_presell_detail($params['plan_code'], $params['data']);
    }

    /**
     * 删除预售商品明细
     */
    function do_delete_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('id', 'barcode'));
        $response = load_model('op/presell/PresellDetailModel')->delete_presell_detail($params);
    }

    /**
     * 编辑预售商品明细
     */
    function do_edit_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('id', 'presell_num', 'plan_send_time', 'barcode'));
        $response = load_model('op/presell/PresellDetailModel')->edit_presell_detail($params);
    }

    /**
     * 一键编辑预售商品明细
     */
    function one_key_edit(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('value', 'field', 'plan_code'));
        $response = load_model('op/presell/PresellDetailModel')->one_key_edit($params);
    }

    /**
     * 更新平台商品预售状态
     */
    function up_goods_presell_status(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('params'));
        $response = load_model('op/presell/PresellDealPtGoodsModel')->up_goods_presell_status($params['params']);
    }

    /**
     * 导入预售明细-页面
     */
    function plan_import_detail(array &$request, array &$response, array &$app) {
        
    }

    /**
     * 导入预售明细-数据处理
     */
    function import_data(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $ret = load_model('op/presell/PresellDetailModel')->imoprt_detail($request['plan_code'], $file);
        $response = $ret;
        $response['url'] = $_FILES['fileData']['name'];
    }

    /**
     * 预售库存同步日志查看
     */
    function plan_sync_log_view(array &$request, array &$response, array &$app) {
        
    }

    /**
     * 获取预售同步日志
     */
    function get_sync_log(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('op/presell/PresellLogModel')->get_sync_log_by_page($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    /**
     * 预售结束，自动还原平台商品预售信息
     */
    function cli_res_pt_presell_goods(array &$request, array &$response, array &$app) {
        load_model('op/presell/PresellDealPtGoodsModel')->auto_res_pt_presell_goods();
        $response['status'] = 1;
    }

    /**
     * 预售跟踪
     */
    function plan_track(array &$request, array &$response, array &$app) {
        $response['shop'] = load_model('base/ShopModel')->get_purview_shop();
    }

}
