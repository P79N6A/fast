<?php

require_lib('util/web_util', true);
require_lib('comm_util');

class stock_lock_record {

    function do_list(array & $request, array & $response, array & $app) {
        
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret['record_code'] = load_model('stm/StockLockRecordModel')->create_fast_bill_sn();
        $response['data'] = $ret;
        //系统参数
        $arr = array('stm_record_lock_obj');
        $response['params'] = load_model('sys/SysParamsModel')->get_val_by_code($arr);
    }

    /**
     * 详情页
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function view(array & $request, array & $response, array & $app) {
        $lock_obj = array(
            '0' => '无',
            '1' => '网络店铺',
        );
        //主单据信息
        $ret = load_model('stm/StockLockRecordModel')->get_by_id($request['stock_lock_record_id']);
        $response['data'] = $ret['data'];
        $response['data']['lock_obj_type'] = $lock_obj[$response['data']['lock_obj']];
        if ($response['data']['lock_obj'] == 1) {
            $response['data']['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $response['data']['shop_code']));
        }
        //系统参数
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    /**
     * 添加单据
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_add(array & $request, array & $response, array & $app) {
        $stock_lock = get_array_vars($request, array('record_code', 'record_time', 'store_code', 'remark', 'lock_obj', 'shop_code'));
        $ret = load_model('stm/StockLockRecordModel')->insert($stock_lock);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "", 'finish_status' => '未锁定', 'action_name' => "创建", 'module' => "stock_lock_record", 'pid' => $ret['data']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 库存锁定单增加明细
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array json
     */
    function do_add_detail(array & $request, array & $response, array & $app) {
        $data = $request['data'];
        $record = load_model('stm/StockLockRecordModel')->get_by_id($request['id']);
        if ($record['data']['lock_obj'] != 0 && $record['data']['order_status'] != 0) {
            $ret = array('status' => '-1', 'data' => '', 'message' => '单据状态异常，无法添加明细！');
        } else {
            $store_code = $record['data']['store_code'];
            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($request['id'], $data);
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($request['id'], $store_code, 'stm_stock_lock', $data);
            if ($ret['status'] < 1) {
                return $ret;
            }
            //锁定单明细添加
            $ret = load_model('stm/StockLockRecordDetailModel')->add_detail_action($request['id'], $data);
            if ($ret['status'] == '1' && $record['data']['order_status'] == 0) {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $record['data']['status'], 'action_name' => '增加明细', 'module' => "stock_lock_record", 'pid' => $request['id']);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
        }
        exit_json_response($ret);
    }

    /*     * 编辑主单
     * @param array $request
     * @param array $response
     * @param array $app
     */

    function do_edit(array &$request, array &$response, array &$app) {
        $record = load_model('stm/StockLockRecordModel')->get_by_id($request['parameterUrl']['stock_lock_record_id']);
        $ret = load_model('stm/StockLockRecordModel')->edit_action($request['parameter'], array('stock_lock_record_id' => $request['parameterUrl']['stock_lock_record_id']));
        if ($ret['status'] == '1') {
            //日志
            if ($record['data']['remark'] != $request['parameter']['remark']) {
                $action_note = '备注由:' . $record['data']['remark'] . '修改为:' . $request['parameter']['remark'];
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $record['data']['status'], 'action_note' => $action_note, 'action_name' => '修改', 'module' => "stock_lock_record", 'pid' => $request['parameterUrl']['stock_lock_record_id']);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
        }
        exit_json_response($ret);
    }

    /*     * 用于页面无刷新
     * @param array $request
     * @param array $response
     * @param array $app
     */

    function get_stock_info(array & $request, array & $response, array & $app) {
        $lock_obj = array(
            '0' => '无',
            '1' => '网络店铺',
        );
        $ret = load_model('stm/StockLockRecordModel')->get_by_id($request['id']);
        $ret['data']['lock_obj_type'] = $lock_obj[$ret['data']['lock_obj']];
        $response['data'] = $ret['data'];
    }

    /*     * 删除明细
     * @param array $request
     * @param array $response
     * @param array $app
     */

    function do_delete_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StockLockRecordDetailModel')->delete($request['stock_lock_record_detail_id']);
        //批次删除
        $ret1 = load_model('stm/GoodsInvLofRecordModel')->delete_pid($request['pid'], $request['sku'], 'stm_stock_lock');
        if ($ret['status'] == '1') {
            $record = load_model('stm/StockLockRecordModel')->get_by_id($request['pid']);
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $record['data']['status'], 'action_name' => '删除商品', 'module' => "stock_lock_record", 'pid' => $request['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 单据锁定
     */
    function record_lock_action(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StockLockRecordModel')->record_lock($request['params']);
        exit_json_response($ret);
    }

    /**
     * 单据释放
     */
    function record_unlock_action(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StockLockRecordModel')->record_unlock($request['params']);
        exit_json_response($ret);
    }

    /*     * 修改锁定数量
     * @param array $request
     * @param array $response
     * @param array $app
     */

    function do_edit_detail(array & $request, array & $response, array & $app) {
        $detail = get_array_vars($request, array('pid', 'sku', 'lock_num'));
        $ret = load_model('stm/StockLockRecordDetailModel')->edit_detail_action($detail);
        if ($ret['status'] == 1) {
            $res = load_model('stm/StockLockRecordModel')->get_by_id($request['pid']);
            $ret['res'] = $res['data'];
        }
        exit_json_response($ret);
    }

    /*     * 批次删除商品
     * @param array $request
     * @param array $response
     * @param array $app
     */

    function do_delete_detail_lof(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StockLockRecordDetailModel')->delete_lof($request['id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未锁定', 'action_name' => '删除商品批次', 'module' => "stock_lock_record", 'pid' => $request['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    function importGoods(array & $request, array & $response, array & $app) {
        $response['id'] = $request['id'];
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $response['import_from'] = !empty($request['import_from']) ? $request['import_from'] : '';
    }

    function import_goods(array & $request, array & $response, array & $app) {
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
        set_uplaod($request, $response, $app);
    }

    function do_import_goods(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $ret = load_model('stm/StockLockRecordModel')->imoprt_detail($request['id'], $file, $request['import_from']);
        $response = $ret;
    }

    function lock_add_inv(array & $request, array & $response, array & $app) {
        if ($request['lof_status'] == 0) {
            $ret = load_model('stm/StockLockRecordDetailModel')->get_row(array('stock_lock_record_detail_id' => $request['id']));
            //获取可用总库存
            $record = load_model('stm/StockLockRecordModel')->get_row(array('stock_lock_record_id' => $ret['data']['pid']));
            $inv_num = load_model('prm/InvModel')->get_inv_by_sku($record['data']['store_code'], array($ret['data']['sku']));
            $ret['data']['inv_available_mum'] = $inv_num['data'][0]['available_num'];
        } else {
            $ret = load_model('stm/GoodsInvLofRecordModel')->get_row(array('id' => $request['id']));
            $inv_num = load_model('prm/InvLofModel')->get_inv_by_sku($ret['data']['store_code'], array($ret['data']['sku']), $ret['data']['lof_no']);
            $ret['data']['inv_available_mum'] = $inv_num['data'][0]['available_num'];
        }
        $response['data'] = $ret['data'];
    }

    function add_inv_action(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('id', 'add_inv_num', 'lof_status', 'inv_available_mum'));
        if ($params['lof_status'] == 0) {
            $params['stock_lock_record_detail_id'] = $params['id'];
            $ret = load_model('stm/StockLockRecordModel')->lock_add_inv($params);
        } else {
            $ret = load_model('stm/StockLockRecordModel')->lock_add_inv_lof($params);
        }
        exit_json_response($ret);
    }

    /**
     * 删除主单
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete_action(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StockLockRecordModel')->delete_main_action($request['id']);
        exit_json_response($ret);
    }

    /**
     * 检查库存同步策略开启状态
     */
    function check_inv_sync(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('op/InvSyncModel')->check_inv_sync_policy($request['params']);
    }

    /**
     * 检查库存同步策略开启状态
     */
    function get_sync_ratio(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('op/InvSyncRatioModel')->get_shop_store_ratio($request['params']);
    }

    function import_add_inv(array & $request, array & $response, array & $app) {
        $response['id'] = $request['id'];
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
    }

    /**
     * 导出明细
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $ret = load_model('stm/StockLockRecordModel')->get_by_id($request['stock_lock_record_id']);
        $main_result = $ret['data'];

        $filter['record_code'] = $main_result['record_code'];
        $filter['page'] = 1;
        $filter['page_size'] = 5000;
        $filter['code_name'] = $request['goods_code'];
        $lof_no = '';
        if ($request['is_lof'] == 1) {
            $res = load_model('stm/StockLockRecordDetailModel')->get_by_page_lof($filter);
            $lof_no = ',批次号';
        } else {
            $res = load_model('stm/StockLockRecordDetailModel')->get_by_page($filter);
        }
        $spec1_arr = load_model('sys/ParamsModel')->get_by_field('param_code', 'goods_spec1', 'value');
        $spec2_arr = load_model('sys/ParamsModel')->get_by_field('param_code', 'goods_spec2', 'value');
        $detail_result = $res['data']['data'];
        $str = "单据编号,仓库,商品名称,商品编码,{$spec1_arr['data']['value']},{$spec2_arr['data']['value']},商品条形码,计划锁定数量,已释放数量,实际锁定数量{$lof_no}\n";
        $str = iconv('utf-8', 'gbk', $str);
        $main_result['store_code_name'] = iconv('utf-8', 'gbk', $main_result['store_code_name']);
        foreach ($detail_result as $value) {
            $lof = '';
            //判断是否开启批次，开启就显示批次号
            if ($request['is_lof'] == 1) {
                $lof = "\t," . $value['init_num'] . "," . $value['fill_num'] . "," . $value['num'] . "," . $value['lof_no'];
            } else {
                $lof = "\t," . $value['lock_num'] . "," . $value['release_num'] . "," . $value['available_num'];
            }
            $value['goods_name'] = iconv('utf-8', 'gbk', $value['goods_name']);
            $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
            $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
            if ($request['is_lof'] == 1) {
                $value['enotice_num'] = $value['init_num'];
            }
            $str .= $main_result['record_code'] . "\t," . $main_result['store_code_name']
                    . "\t," . $value['goods_name'] . "\t," . $value['goods_code'] . "\t," . $value['spec1_name'] .
                    "\t," . $value['spec2_name'] . "\t," . $value['barcode'] . $lof . "\n"; //用引文逗号分开
        }
        $filename = date('Ymd') . '.csv'; //设置文件名
        header_download_csv($filename, $str); //导出
        echo $str;
        die;
    }

    function do_import_add_inv(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $ret = load_model('stm/StockLockRecordModel')->imoprt_add_inv($request['id'], $file);
        $response = $ret;
    }

    function lock_mode_help(array & $request, array & $response, array & $app) {

    }

}
