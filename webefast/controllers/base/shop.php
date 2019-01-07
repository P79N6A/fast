<?php

require_lib('util/web_util', true);

class shop {

    function do_list(array &$request, array &$response, array &$app) {
        //商品状态
        $response['sale_channel'] = load_model('base/SaleChannelModel')->get_data_code_map();
        $response['add_service_authority'] = load_model('common/ServiceModel')->check_is_auth_by_value('alipay');
        //增值服务
        $response['service_custom'] = load_model('common/ServiceModel')->check_is_auth_by_value('CDKZ');
        //是否分销商登录
        $response['login_type'] = CTX()->get_session('login_type');
    }

    function detail(array &$request, array &$response, array &$app) {
        //是否分销商登录
        $response['login_type'] = CTX()->get_session('login_type');
        //增值服务
        $response['service_custom'] = load_model('common/ServiceModel')->check_is_auth_by_value('CDKZ');
        $title_arr = array('edit' => '编辑商店', 'add' => '添加商店', 'view' => '查看商店');
        $app['title'] = $title_arr[$app['scene']];
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('base/ShopModel')->get_by_id($request['_id']);
            //查找该店铺下的分销商
            $custom_arr = load_model('base/CustomModel')->get_by_code($ret['data']['custom_code']);
            //查看该店铺是否绑定单据
            $response['is_update_entity'] = load_model('base/ShopModel')->get_all_record_code_num($ret['data']['shop_code']);
            //唯品会店铺绑定的仓库
            if ($ret['data']['sale_channel_code'] == 'weipinhui') {
                $response['shop_warehouse']=load_model('base/ShopModel')->get_warehouse_by_shop($ret['data']['shop_code']);
            }
        }
        if (isset($ret['data']['express_data']) && $ret['data']['express_data'] != '') {
            $ret['data']['express_data'] = json_decode($ret['data']['express_data'], true);
        } else {
            $ret['data']['express_data'] = array();
        }
        $response['custom_name'] = $custom_arr['data']['custom_name'];
        //商品状态
        $sale_channel2 = load_model('base/SaleChannelModel')->get_data_code_map();
        //print_r($sale_channel);
        $sale_channel = array();
        $sale_channel['notai'][0] = '';
        $sale_channel['notai'][1] = '请选择';
        foreach ($sale_channel2 as $k => $v) {
            $sale_channel[$k] = $v;
        }
        $response['active'] = isset($ret['data']['is_active']) ? $ret['data']['is_active'] : '';
        $response['sale_channel'] = $sale_channel;
        if($response['login_type'] != 2) { 
            //仓库
            $store_arr = $this->get_store();
            $store_arr1[] = array('', '请选择');
            $response['store'] = array_merge($store_arr1, $store_arr);
            $response['fx_store'] = load_model('base/StoreModel')->get_fx_store(1);
        } else { //分销商登录
            $response['store'] = load_model('base/StoreModel')->get_fx_store(1);
            //分销商
            $user_code = CTX()->get_session('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            $response['custom'] = $custom;
        }
        //快递公司
        $express_data = $this->get_express(1);
        $response['express_data'] = $express_data;
        $express_data1[] = array('', '请选择');
        $express_data = array_merge($express_data1, $express_data);
        $response['express'] = $express_data;
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
        $shop_data = $this->get_taobao_shop();
        $response['shop_data_1'] = $shop_data;
        $shop_data1[] = array('', '请选择');
        $shop_data = array_merge($shop_data1, $shop_data);
        $response['shop_data'] = $shop_data;
        if ($app['scene'] == 'add') {
            $response['data']['shop_code'] = $this->serial_code('taobao');
        }
        $response['product_version_no'] = load_model('sys/SysAuthModel')->product_version_no();
        $response['authorize_state'] = isset($response['data']['authorize_state']) ? $response['data']['authorize_state'] : '';
        $response['shop_user_nick'] = isset($response['data']['shop_user_nick']) ? $response['data']['shop_user_nick'] : '';
        $response['auth_url'] = '';
        $ret = load_model('base/ShopAuthModel')->auth_shop($request['_id']);

        if ($ret['status'] > 0) {
            $response['auth_url'] = $ret['data'];
        }
        //唯品会店铺
        $warehouse = oms_tb_all('api_weipinhuijit_warehouse', array('status' => 1));
        foreach ($warehouse as $key=>$value){
            $response['warehouse'][$key]['warehouse_code']=$value['warehouse_code'];
            $response['warehouse'][$key]['warehouse_name']=$value['warehouse_name'];
        }
    }

    function get_shop_list(array &$request, array &$response, array &$app) {
        $request['page_size'] = empty($request['page_size']) ? 100 : $request['page_size'];
        $ret = load_model('base/ShopModel')->get_by_page($request);
        exit_json_response($ret);
    }

    function get_sale_channel_params(array &$request, array &$response, array &$app) {
        $params = require_conf('sys/sale_channel_params');
        if (!isset($params[$request['sale_channel_code']])) {
            $response['status'] = -1;
        } else {
            $conf = $params[$request['sale_channel_code']];
            //var_dump($conf);die;
            $ret = load_model('base/ShopApiModel')->get_shop_extra_params($request['shop_code']);
            //print_r($ret);
            if (!empty($ret['data'])) {
                foreach ($conf as $key => &$val) {
                    if (isset($val['show']) && $val['show'] == '1') {
                        //$val['val'] = isset($ret['data'][$key])?substr($ret['data'][$key],0,4).'****':$val['val'];
                        $val['val'] = '机密参数，暂不显示';
                    } else {
                        $val['val'] = isset($ret['data'][$key]) ? $ret['data'][$key] : $val['val'];
                    }
                }
            }

            //print_r($conf);
            $response['status'] = 1;
            $response['data'] = $conf;
        }
    }

    function get_channel() {
        $arr_channel = array('9' => array('0' => '9', '1' => '淘宝'), '13' => array('0' => '13', '1' => '京东'),
            '16' => array('0' => '16', '1' => '一号店'), '10' => array('0' => '10', '1' => '拍拍'),
            '14' => array('0' => '14', '1' => '亚马逊'),);
        return $arr_channel;
    }

    //仓库
    function get_store() {
        $arr_store = load_model('base/StoreModel')->get_list();
        $key = 0;
        foreach ($arr_store as $value) {
            $arr_store[$key]['0'] = $value['store_code'];
            $arr_store[$key]['1'] = $value['store_name'];
            unset($arr_store[$key]['store_id'], $arr_store[$key]['store_code'], $arr_store[$key]['store_name']);
            $key++;
        }
        return $arr_store;
    }

    //获取快递公司
    function get_express($is_active = null) {
        $arr_store = load_model('base/ShippingModel')->get_list($is_active);
        $key = 0;
        foreach ($arr_store as $value) {
            $arr_store[$key]['0'] = $value['express_code'];
            $arr_store[$key]['1'] = $value['express_name'];
            unset($arr_store[$key]['express_id'], $arr_store[$key]['express_code'], $arr_store[$key]['express_name']);
            $key++;
        }
        return $arr_store;
    }
    //获取授权的淘宝店铺
    function get_taobao_shop() {
        $arr_shop = load_model('base/ShopModel')->get_taobao_list();
        $key = 0;
        foreach ($arr_shop as $value) {
            $arr_shop[$key]['0'] = $value['shop_code'];
            $arr_shop[$key]['1'] = $value['shop_name'];
            unset($arr_shop[$key]['shop_id'], $arr_shop[$key]['shop_code'], $arr_shop[$key]['shop_name']);
            $key++;
        }
        return $arr_shop;
    }
    function parse_req_data(array &$request, $shop) {
        $shop = get_array_vars($request, array('shop_code', 'shop_name', 'shop_xz', 'sale_channel_code', 'is_active', 'admin_user', 'contact', 'wangang', 'is_logistics'));
        $params_name_arr = explode(',', 'app_key,app_secret,app_session,app_nick,app_type,refresh_token,expires_in,tmall_refund_start,taobao_trade_source,ekey');
        $api_params = array();
        foreach ($params_name_arr as $s_pm) {
            $api_params[$s_pm] = (string) $request[$s_pm];
        }
        $shop['api_params'] = json_encode($api_params);
        return $shop;
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $shop = get_array_vars($request, array('shop_name', 'sale_channel_code', 'fenxiao_status', 'send_store_code', 'refund_store_code', 'days', 'express_code', 'express_data', 'contact_person', 'tel', 'address', 'province', 'city', 'district', 'street', 'entity_type','taobao_shop_code'));
        //是否分销商登录
        $login_type = CTX()->get_session('login_type');
        if($login_type == 2) {
            $user_code = CTX()->get_session('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            $shop['custom_code'] = $custom['custom_code'];
        } else {
            $shop['custom_code'] = $request['custom_code'];
        }
        //过滤店铺名称前后空格
        $shop['shop_name'] = trim($shop['shop_name']);
        if (!empty($shop['express_data'])) {
            $shop['express_data'] = json_encode($shop['express_data']);
        }
        if (isset($request['inv_syn']) && $request['inv_syn'] != '') {
            $shop['inv_syn'] = 1;
        } else {
            $shop['inv_syn'] = 0;
        }
        if ($shop['entity_type'] == 0) {
            $shop['custom_code'] = '';
        }
        $ret = load_model('base/ShopModel')->update($shop, $request['shop_id']);
//        if ($request['sale_channel_code'] <> 'taobao') {
            $ret = $this->save_shop_extra($request, $response, $app);
//        }
        exit_json_response($ret);
    }

    function do_add(array &$request, array &$response, array &$app) {
        $shop = get_array_vars($request, array('shop_code', 'shop_name', 'sale_channel_code', 'fenxiao_status', 'send_store_code', 'stock_source_store_code', 'refund_store_code', 'days', 'express_code', 'contact_person', 'tel', 'address', 'province', 'city', 'district', 'street', 'create_time', 'entity_type','taobao_shop_code'));
        //是否分销商登录
        $login_type = CTX()->get_session('login_type');
        if($login_type == 2) {
            $user_code = CTX()->get_session('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            $shop['custom_code'] = $custom['custom_code'];
        } else {
            $shop['custom_code'] = $request['custom_code'];
        }
        $shop['create_time'] = date('Y-m-d', mktime());
        //过滤店铺名称前后空格
        $shop['shop_name'] = trim($shop['shop_name']);
        $shop['stock_source_store_code'] = empty($shop['stock_source_store_code']) ? $shop['send_store_code'] : $shop['stock_source_store_code'];
        if (isset($request['inv_syn']) && $request['inv_syn'] != '') {
            $shop['inv_syn'] = 1;
        } else {
            $shop['inv_syn'] = 0;
        }
        if ($shop['entity_type'] == 0) {
            $shop['custom_code'] = '';
        }
        if($shop['sale_channel_code'] == 'fenxiao'){
            $shop['fenxiao_status'] = 1;
        }
        $ret = load_model('base/ShopModel')->insert($shop);

//        if ($request['sale_channel_code'] <> 'taobao') {
            $ret = $this->save_shop_extra($request, $response, $app);
//        }
        exit_json_response($ret);
    }

    function save_shop_extra(array &$request, array &$response, array &$app) {
        $params = require_conf('sys/sale_channel_params');
        $extra_params = array();
        if (isset($params[$request['sale_channel_code']])) {
            $conf = $params[$request['sale_channel_code']];
            for ($i = 1; $i < 10; $i++) {
                if (isset($request['param' . $i])) {
                    $key = $request['param' . $i];
                    if (isset($conf[$key])) {
                        if ($request['param' . $i . "_val"] <> '机密参数，暂不显示' && $request['param' . $i . "_val"] <> '') {
                            $extra_params[$key] = isset($request['param' . $i . "_val"]) ? $request['param' . $i . "_val"] : '';
                        }
                    } else {
                        break;
                    }
                }
            }
        }
        if(!empty($request['taobao_shop_code'])){
            $extra_data = load_model('base/ShopApiModel')->get_shop_extra_params($request['taobao_shop_code']);
        
            $extra_params = $extra_data['data'];
        }
        //CTX()->saas->get_saas_key();
        $test_channel = array('jingdong', 'chuanyi', 'chuchujie', 'meilishuo', 'yamaxun', 'beibei', 'baidumall', 'mogujie', 'zhe800', 'youzan', 'aliexpress', 'suning', 'miya', 'weimob', 'vdian', 'yintai', 'gonghang', 'kaola', 'huayang', 'shangpin', 'weipinhui', 'juanpi', 'okbuy', 'renrendian', 'fenxiao', 'alibaba', 'mxyc', 'feiniu', 'xiaohongshu', 'pinduoduo', 'yihaodian', 'jumei', 'biyao', 'xiaomizhijia', 'ofashion','xiaomi','yoho','siku','yougou','weifenxiao', 'dangdang','chuizhicai','shangpai','zouxiu','sappho','akucun','davdian');

        $ret = array('status' => 1, 'data' => '', 'message' => '');
        if (!empty($extra_params)) {
            $kh_id = CTX()->saas->get_saas_key();
            load_model('base/ShopApiModel')->save_shop_extra_params($request['shop_code'], $extra_params, $kh_id,$conf);
            //唯品会店铺保存相关信息
            if ($request['sale_channel_code'] == 'weipinhui') {
                load_model('base/ShopModel')->save_weipinhui_shop_relation_warehouse($request['shop_code'], $request['shop_warehouse_params'],$extra_params['co_mode']);
            }

            //识别店铺昵称
            $nick_channel = array('weipinhui');
            if (in_array($request['sale_channel_code'], $nick_channel)) {
                $shop_nick = $extra_params['shop_nick'];
                if (!empty($shop_nick)) {
                    $ret = load_model('base/ShopModel')->shop_nick_check($request['shop_code'], $shop_nick, $request['sale_channel_code']);
                    if ($ret['status'] != 1) {
                        return $ret;
                    }
                }
            }

            if($request['sale_channel_code'] == 'taobao' || $request['sale_channel_code']=='jingdong'){
                $ret = load_model('base/ShopApiModel')->update_nick($request['shop_code'], $extra_params['nick']);
            }

            //新增京东店铺未填店铺参数，不需要调接口
            if ($request['sale_channel_code'] == 'jingdong') {
                if (count($extra_params) == 1 && isset($extra_params['type'])) {
                    return $ret;
                }
            }
            //授权测试接口
            if (in_array($request['sale_channel_code'], $test_channel)) {
                $fun_name = $request['sale_channel_code'] . '_' . 'api';
                if ($request['sale_channel_code'] == 'weipinhui' && $extra_params['type'] == 'JIT') {
                    $fun_name = $request['sale_channel_code'] . 'jit_' . 'api';
                }
                if ($request['sale_channel_code'] == 'meilishuo' || $request['sale_channel_code'] == 'mogujie') {
                    $fun_name = 'xiaodian_api';
                }
                if ($request['sale_channel_code'] == 'fenxiao') {
                    $fun_name = 'taobao_api';
                }
                $api_params['shop_code'] = $request['shop_code'];
                //$result = load_model('sys/EfastApiModel')->request_api($fun_name . '/test', $api_params);
                $result['resp_data']['code'] = 0;
                /* if($request['sale_channel_code']<>'jingdong'){
                  $api_params['shop_code'] = $request['shop_code'];
                  $result = load_model('sys/EfastApiModel')->request_api($fun_name . '/test', $api_params);
                  }else{
                  $result['resp_data']['code'] = 0;
                  } */
                if(!empty($result)){
                    if ($result['resp_data']['code'] == '0') {
                        $auth_params['is_active'] = 1;
                        $auth_params['authorize_state'] = 1;
                        $auth_params['authorize_date'] = empty($extra_params['authorize_date']) ? '2030-07-01 00:00:00' : $extra_params['authorize_date'];
                    } else {
                        $auth_params['is_active'] = 0;
                        $auth_params['authorize_state'] = 0;
                        $ret = array('status' => -1, 'data' => '', 'message' => $result['resp_data']['msg']);
                    }
                    //更新授权状态
                    load_model('base/ShopModel')->update_auth_shop($request['shop_code'], $auth_params);
                }else{
                    $ret = array('status' => -1, 'data' => '', 'message' => '授权激活失败');
                }
            }
        }
        return $ret;
    }

    function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('base/ShopModel')->update_active($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }

    /**
     * get店铺代码
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function serial_num(array &$request, array &$response, array &$app) {
        if (isset($request['sale_channel_code']) && $request['sale_channel_code'] != '') {
            $shop_code = $this->serial_code($request['sale_channel_code']);
            if ($shop_code) {
                exit_json_response('success', $shop_code, 'xulie');
            } else {
                exit_json_response('error', $shop_code, 'xulie');
            }
        }
    }

    /**
     * 店铺代码生成
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function serial_code($sale_channel_code) {

        $data = load_model('base/ShopModel')->get_last();
        $id = isset($data[0]['shop_id']) ? intval($data[0]['shop_id']) + 1 : '';
        $pinyin = load_model('base/SaleChannelModel')->get_short_code($sale_channel_code);
        $pinyin = empty($pinyin) ? $sale_channel_code : $pinyin;
        $len = strlen($id);
        if ($len == 0) {
            $shop_code = $pinyin . '000';
        } else {
            switch ($len) {
                case 1:
                    $shop_code = $pinyin . '00' . $id;
                    break;
                case 2:
                    $shop_code = $pinyin . '0' . $id;
                    break;
                default:
                    $shop_code = $pinyin . $id;
            }
        }

        return $shop_code;
    }

//	/**
//	 * 授权页面
//	 * @param array $request
//	 * @param array $response
//	 * @param array $app
//	 */
//	function shop_authorize(array &$request, array &$response, array &$app) {
//
//		$app['tpl'] = "base/shop_authorize";
//	}

    /**
     * 授权成功
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function shop_authorize_success(array &$request, array &$response, array &$app) {
        $ret = load_model('base/ShopModel')->update_authorize_state($request['shop_code']);
        if($ret['status'] == 1){
            $ret_1 = load_model('base/ShopModel')->update_fenxiao_shop($request['shop_code']);
        }
        exit_json_response($ret);
    }

    function pre_authorize(array &$request, array &$response, array &$app) {
        $sql = "select shop_id from base_shop where shop_code = :shop_code";
        $shop_id = ctx()->db->getOne($sql, array(':shop_code' => $request['shop_code']));

        $ret = load_model('base/ShopAuthModel')->auth_shop($shop_id);

        if ($ret['status'] > 0) {
            echo "<script>location.href = '" . $ret['data'] . "';</script>";
        } else {
            echo $ret['message'];
        }
        die;
    }

    function stock_source(array &$request, array &$response, array &$app) {
        $response['store'] = load_model('base/StoreModel')->get_purview_store();
        $sql = "select stock_source_store_code from base_shop where shop_id =" . (int) $request['_id'];
        $stock_source_store_code = ctx()->db->getOne($sql);
        $response['stock_source_store_code'] = explode(',', $stock_source_store_code);
    }

    function save_stock_source(array &$request, array &$response, array &$app) {
        $response = load_model('base/ShopModel')->save_stock_source($request['_id'], $request['store_code_list']);
    }

    /**
     * 订购成功
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function alipay_order_succ(array &$request, array &$response, array &$app) {
        $ret = load_model('base/ShopModel')->update_alipay_order_status($request['shop_code']);
        exit_json_response($ret);
    }

    /**
     * 获取有权限的店铺，供BUI框架下拉选择使用
     */
    function get_purview_shop(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $first_val = isset($request['first_val']) ? $request['first_val'] : 'all';
        $first_type = isset($request['first_type']) ? $request['first_type'] : 1;
        $response = load_model('base/ShopModel')->get_bui_select_shop($first_val, $first_type);
    }
    
    function auth_platform(array &$request, array &$response, array &$app) {
        $sale_channel_code = $request['sale_channel_code'];
        $method = "auth_" . $sale_channel_code;
        $ret = load_model('base/ShopAuthModel')->$method($request['api_params']);
        exit_json_response($ret);
    }
    
    /**
     * 店铺选择弹框基本方法
     */
    function base_selection(array &$request, array &$response, array &$app) {
        switch ($request['select_type']) {
            case 'erp' :
                $request['node_url'] = 'base/sale_channel/get_erp_api_nodes&app_fmt=json';
                $request['dataStore_url'] = 'base/shop/erp_shop_select_action&app_fmt=json';
        }
    }
    
    /**
     * erp店铺选择数据获取
     */
    function erp_shop_select_action(array & $request, array & $response, array & $app) {
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('base/ShopModel')->erp_shop_select_action($request);
        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }
    
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('base/ShopModel')->do_delete($request['shop_id']);
        exit_json_response($ret);
    }

}
