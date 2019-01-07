<?php

/*
 * 营销中心-增值订购
 */
require_lib('util/web_util', true);
require_lib('comm_util');

class valueorder {

    //产品订购列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }

    //产品订购列表(新)
    function do_list_new(array & $request, array & $response, array & $app) {
        
    }

    //新建、编辑产品订购显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑增值服务订购', 'add' => '新建增值服务订购');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('market/ValueorderModel')->get_by_id($request['_id']);
        /** 判断api是否生成 start */
        // 判断增值服务是否是OpenAPI
        if (stripos($ret['data']['val_serverid_code'], 'api') !== false) {

            $model = load_model('market/OspValueauthKeyModel');
            $get = $model->isApiExists($ret['data']['val_kh_id']);
            if ($get['status']) {
                $ret['data']['api'] = $get['data'];
            } else {
                $ret['data']['api'] = '';
            }
        }
        /** 判断api是否生成 end */
        $response['data'] = $ret['data'];
    }

    //编辑订购信息数据处理。
    function valorders_edit(array & $request, array & $response, array & $app) {
        $vorders = get_array_vars($request, array(
            'val_channel_id',
            'val_kh_id',
            'val_cp_id',
            'val_pt_version',
            'val_serverid',
            'val_standard_price',
            'val_cheap_price',
            'val_actual_price',
            'val_hire_limit',
            'val_seller',
            'val_desc'
        ));
        $ret = load_model('market/ValueorderModel')->update($vorders, $request['val_num']);
        if ($ret['status']) {
            load_model('basedata/RdsDataModel')->update_kh_data($request['val_kh_id'], '0', 'osp_valueorder');
        }
        exit_json_response($ret);
    }

    //添加产品订购信息数据处理。    
    function valorders_add(array & $request, array & $response, array & $app) {
        $vorders = get_array_vars($request, array(
            'val_channel_id',
            'val_kh_id',
            'val_cp_id',
            'val_pt_version',
            'val_serverid',
            'val_standard_price',
            'val_cheap_price',
            'val_actual_price',
            'val_hire_limit',
            'val_seller',
            'val_desc'
        ));
        $vorders['val_orderdate'] = date('Y-m-d H:i:s');
        $ret = load_model('market/ValueorderModel')->insert($vorders);
        if ($ret['status']) {
            load_model('basedata/RdsDataModel')->update_kh_data($request['val_kh_id'], '0', 'osp_valueorder');
        }
        exit_json_response($ret);
    }

    //付款操作
    function do_pay_value_orders(array & $request, array & $response, array & $app) {
        if (isset($request['val_num'])) {
            $pay_update['val_pay_status'] = 1;
            $pay_update['val_paydate'] = date('Y-m-d H:i:s');
            $pay_stat = load_model('market/ValueorderModel')->update($pay_update, $request['val_num']);
            exit_json_response($pay_stat);
        }
    }

    //审核操作
    function do_check_value_orders(array & $request, array & $response, array & $app) {
        if (!empty($request['val_num'])) {
            $ret = load_model('market/ValueorderModel')->get_by_id($request['val_num']);
            $check_update['val_check_status'] = 1;
            $check_update['val_checkdate'] = date('Y-m-d H:i:s');
            $check_stat = load_model('market/ValueorderModel')->update($check_update, $request['val_num']);
            $valueauth = array();
            if (isset($ret['data'])) {
                //是否存在授权记录
                $valauthinfo = array('vra_kh_id' => $ret['data']['val_kh_id'],
                    'vra_cp_id' => $ret['data']['val_cp_id'],
                    'vra_pt_version' => $ret['data']['val_pt_version'],
                    'vra_server_id' => $ret['data']['val_serverid']);
                $ret_auth = load_model('products/ValueorderauthModel')->get_values_info($valauthinfo);
                if (!empty($ret_auth['data'])) {
                    if (!empty($ret['data']['val_hire_limit'])) {
                        $valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+" . $ret['data']['val_hire_limit'] . month, strtotime($ret_auth['data']['vra_enddate'])));
                    } else {
                        $valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+20 year"));
                    }
                    //$valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+" . $ret['data']['val_hire_limit'] . month));
                    $value_data = load_model('products/ValueorderauthModel')->update_value_auth($valueauth, $ret_auth['data']['vra_id']);
                } else {
                    if (!empty($ret['data']['val_hire_limit'])) {
                        $valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+" . $ret['data']['val_hire_limit'] . month));
                    } else {
                        $valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+20 year"));
                    }
                    //$valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+20 year"));
                    $valueauth['vra_kh_id'] = $ret['data']['val_kh_id'];
                    $valueauth['vra_cp_id'] = $ret['data']['val_cp_id'];
                    $valueauth['vra_pt_version'] = $ret['data']['val_pt_version'];
                    $valueauth['vra_startdate'] = date('Y-m-d H:i:s');
                    $valueauth['vra_server_id'] = $ret['data']['val_serverid'];
                    $valueauth['vra_state'] = '1';
                    $valueauth['vra_bz'] = $ret['data']['val_desc'];
                    $value_data = load_model('products/ValueorderauthModel')->insert_value_auth($valueauth);
                }
                if ($value_data['status']) {
                    load_model('basedata/RdsDataModel')->update_kh_data($valueauth['vra_kh_id'], '0', 'osp_valueorder_auth');
                }
                exit_json_response($value_data);
            }
        } else {
            exit_json_response(load_model('market/ValueorderModel')->format_ret("-1", '', '订购编号错误'));
        }
    }

    public function openapi(array &$request, array &$response, array &$app) {
        $model = load_model('market/OspValueauthKeyModel');
        $insert = $model->generateApi($request['val_kh_id']);
        /** 判断api是否已经生成 */
        if ($insert['status'] == 2) {
            $insert['message'] = 'API已经生成';
            exit_json_response($insert);
        } elseif ($insert['status'] == 3) {
            $insert['message'] = '尚未付款成功';
            exit_json_response($insert);
        } else {
            /** 输出插入返回的值 */
            exit_json_response($insert);
        }
    }

    //付款操作
    function do_pay_orders_main(array & $request, array & $response, array & $app) {
        $pay_stat = load_model('market/ValueorderModel')->update_order_pay($request['id']);
        exit_json_response($pay_stat);
    }

    /**
     * 主单删除
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete_order(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('id'));
        $ret = load_model('market/ValueorderModel')->do_order_delete($params);
        exit_json_response($ret);
    }

    /**
     * 新增页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function detail_new(array & $request, array & $response, array & $app) {
        $ret['order_code'] = load_model('market/ValueorderMainModel')->create_order_code();
        $response['data'] = $ret;
    }

    /**
     * 新增订单
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function valorders_add_new(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('order_code', 'val_channel_id', 'kh_id', 'val_cp_id', 'val_desc'));
        $ret = load_model('market/ValueorderMainModel')->insert($params);
        exit_json_response($ret);
    }

    /**
     * 详情页
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function view_new(array & $request, array & $response, array & $app) {
        $ret = load_model('market/ValueorderMainModel')->get_by_id($request['id']);
        $response['data'] = $ret['data'];
    }

    /**
     * 删除明细
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete_detail(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('val_num'));
        $ret = load_model('market/ValueorderModel')->delete_detail($params['val_num']);
        exit_json_response($ret);
    }

    function do_add_service(array & $request, array & $response, array & $app) {
        $ret = load_model('market/ValueorderModel')->add_detail_action($request['data'], $request['id']);
        exit_json_response($ret);
    }


    function valueorder_edit(array & $request, array & $response, array & $app) {
        $ret = load_model('market/ValueorderMainModel')->edit_order_action($request['parameter'], $request['parameterUrl']['id']);
        exit_json_response($ret);
    }

    function get_order_info(array & $request, array & $response, array & $app) {
        $ret = load_model('market/ValueorderMainModel')->get_by_id($request['id']);
        $response['data'] = $ret['data'];
    }


    function import(array & $request, array & $response, array & $app) {

    }

    function import_goods(array & $request, array & $response, array & $app) {
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('market/ValueorderMainModel')->import_upload($request, $_FILES);
        $response = $ret;
        set_uplaod($request, $response, $app);
    }



    function do_import_action(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $ret = load_model('market/ValueorderMainModel')->imoprt_detail($file);
        exit_json_response($ret);
    }


}
