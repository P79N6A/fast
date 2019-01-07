<?php

require_lib('util/oms_util', true);

/**
 * 库存同步策略控制器业务
 */
class inv_sync {

    function do_list(array & $request, array & $response, array & $app) {

    }

    function detail(array & $request, array & $response, array & $app) {
        switch ($app['scene']) {
            case 'add':
                $response['title'] = '新增策略';
                break;
            case 'edit':
                $response['title'] = '编辑策略';
                break;
            default:
                $response['title'] = '查看策略';
                break;
        }
    }

    function warn_goods(array & $request, array & $response, array & $app) {
        $response['spec'] = load_model("oms_shop/OmsShopModel")->get_spec_rename();
    }

    /**
     * 读取详情页各部分信息
     */
    function get_tab(array &$request, array &$response, array &$app) {
        $type = $request['type'];
        $response[$type] = load_model('op/InvSyncModel')->get_baseinfo($request['sync_code']);
        if ($type == 'baseinfo') {
            $response['baseinfo'] = json_encode($response['baseinfo'], true);
        }
        if ($type == 'anti_oversold') {
            $anti_oversold = load_model('sys/SysParamsModel')->get_val_by_code('anti_oversold');
            $response['anti_oversold_state'] = $anti_oversold['anti_oversold'];
            $shop_data = load_model('op/InvSyncModel')->get_ss_name_by_code($request['sync_code'], 1);
            $shop_code_arr = explode(',', $shop_data['shop_code']);
            $shop_name_arr = explode(',', $shop_data['shop_name']);
            foreach ($shop_code_arr as $key => $code) {
                $response['shop'][$code] = $shop_name_arr[$key];
            }
            $response['sync_code'] = $request['sync_code'];
        }
        if ($type == 'shop_ratio') {
            $shop_data = load_model('op/InvSyncModel')->get_ss_name_by_code($request['sync_code'], 1);
            $shop_code_arr = explode(',', $shop_data['shop_code']);
            $shop_name_arr = explode(',', $shop_data['shop_name']);
            foreach ($shop_code_arr as $key => $code) {
                $response['shop'][$code] = $shop_name_arr[$key];
            }
        }
        if ($type == 'goods_ratio') {
            $response['brand'] = load_model('prm/BrandModel')->get_code_name();
            $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
            $response['season'] = load_model('base/SeasonModel')->get_season();
            $response['year'] = load_model('base/YearModel')->get_year();
            $response['spec'] = load_model("oms_shop/OmsShopModel")->get_spec_rename();
        }
        ob_start();
        $path = get_tpl_path('op/inv_sync_' . $type);
        include $path;
        $ret = ob_get_contents();
        ob_end_clean();
        die($ret);
    }

    /**
     * 启用停用
     */
    function update_active(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $params = get_array_vars($request, array('id', 'active'));
        $response = load_model('op/InvSyncModel')->update_active($params);
    }

    function select_shop(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    function select_store(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    function select_action(array & $request, array & $response, array & $app) {
        $action = $request['type'] == 'shop' ? 'get_shop_select' : 'get_store_select';
        $model = $request['type'] == 'shop' ? 'ShopModel' : 'StoreModel';
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('base/' . $model)->$action($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    function do_add(array & $request, array & $response, array & $app) {
        $request['act'] = $app['act'];
        $response = load_model('op/InvSyncModel')->set_baseinfo($request);
    }

    function do_edit(array & $request, array & $response, array & $app) {
        $request['act'] = $app['act'];
        $request['is_road'] = isset($request['is_road']) && $request['is_road'] == 0 ? 1 : 0;
        $request['is_safe'] = isset($request['is_safe']) && ($request['is_safe'] == 'on' || $request['is_safe'] == 0) ? 1 : 0;
        $response = load_model('op/InvSyncModel')->set_baseinfo($request);
    }

    function update_anti_status(array & $request, array & $response, array & $app) {
        $value = ($request['value'] == 0) ? 1 : 0;
        $data = array('value' => $value);
        $where = array('param_code' => 'anti_oversold');
        $ret = load_model('sys/SysParamsModel')->update($data, $where);
        $log_info = ($request['value'] == 0) ? '防超卖预警配置启用' : '防超卖预警配置停用';
        $log = array('sync_code'=>$request['sync_code'],'user_code' => CTX()->get_session('user_code'),'user_ip'=>gethostbyname($_SERVER["SERVER_NAME"]),'tab_type'=>'anti_oversold','log_info'=>$log_info,'log_time'=>date('Y-m-d H:i:s'));
        $res = load_model('op/InvSyncLogModel')->insert($log);
        exit_json_response($ret);
    }

    function save_oversold(array & $request, array & $response, array & $app) {
        $request['type'] = 'anti_oversold';
        $ret = load_model('op/InvSyncModel')->set_baseinfo($request);
        exit_json_response($ret);
    }

    /**
     * 编辑店铺同步比例
     */
    function do_edit_ratio(array & $request, array & $response, array & $app) {
        $ret = load_model('op/InvSyncRatioModel')->set_sync_ratio($request);
        exit_json_response($ret);
    }

    /**
     * @todo 设置商品同步比例
     */
    function set_goods_ratio(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('sync_code', 'shop_code', 'store_code', 'sync_ratio', 'sku', 'set_type', 'select_wh'));
        $ret = load_model('op/InvSyncRatioModel')->set_goods_ratio($params);
        exit_json_response($ret);
    }

    /**
     * @todo 删除商品同步比例
     */
    function delete_goods_ratio(array & $request, array & $response, array & $app) {
        $ret = load_model('op/InvSyncRatioModel')->delete_goods_ratio($request);
        exit_json_response($ret);
    }

    function shop_ratio(array & $request, array & $response, array & $app) {
        $response['shop_ratio'] = load_model('op/InvSyncModel')->get_baseinfo($request['sync_code']);
        $shop_data = load_model('op/InvSyncModel')->get_ss_name_by_code($request['sync_code'], 1);
        $shop_code_arr = explode(',', $shop_data['shop_code']);
        $shop_name_arr = explode(',', $shop_data['shop_name']);
        foreach ($shop_code_arr as $key => $code) {
            $response['shop'][$code] = $shop_name_arr[$key];
        }
        if ($request['set_type'] == 'set') {
            $response['goods_info'] = load_model('prm/GoodsModel')->get_sku_list($request['sku']);
        }
        if ($request['set_type'] == 'one_set') {
            $response['select_wh'] = json_encode($request['select_wh']);
        }
    }

    /**
     * 条码预警设置
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function sku_ratio(array & $request, array & $response, array & $app) {
        $response['shop_ratio'] = load_model('op/InvSyncModel')->get_baseinfo($request['sync_code']);
        $response['sync_code'] = $request['sync_code'];
        $shop_data = load_model('op/InvSyncModel')->get_ss_name_by_code($request['sync_code'], 1);
        $shop_code_arr = explode(',', $shop_data['shop_code']);
        $shop_name_arr = explode(',', $shop_data['shop_name']);
        foreach ($shop_code_arr as $key => $code) {
            $response['shop'][$code] = $shop_name_arr[$key];
        }
        $response['sku'] = $request['sku'];
        $response['goods_info'] = load_model('prm/GoodsModel')->get_sku_list($request['sku']);
        $response['warn_sku'] = load_model('op/InvSyncAntiOversoldModel')->get_warn_info_all($request['sync_code'], $request['sku']);
    }

    /**
     * 获取条码预警信息
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function get_sku_warn(array & $request, array & $response, array & $app) {
        $ret = load_model('op/InvSyncAntiOversoldModel')->get_warn_sku_info($request['sync_code'], $request['sku'],$request['shop_code']);
        exit_json_response($ret);
    }

    /**
     * @todo 导入-页面
     */
    function import_goods_ratio(array & $request, array & $response, array & $app) {

    }

    /**
     * @todo 导入-上传文件
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
     * @todo 导入-入库
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
        $param['sync_code'] = $request['sync_code'];
        $param['sync_mode'] = $request['sync_mode'];
        $ret = load_model('op/InvSyncRatioModel')->import_ratio_data($param, $file);
        $response = $ret;
    }
    function delete(array & $request, array & $response, array & $app){
        $ret = load_model('op/InvSyncModel')->delete($request['sync_code']);
        exit_json_response($ret);
    }


    /**
     * 保存条码预警
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function save_warn_sku(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('sync_code', 'warn_sku_val', 'sku', 'shop_code'));
        $ret = load_model('op/InvSyncAntiOversoldModel')->save_warn_sku($params);
        exit_json_response($ret);
    }

}
