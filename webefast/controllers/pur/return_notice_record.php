<?php

require_lib('util/web_util', true);
require_lib('business_util', true);

class return_notice_record {

    function do_list(array & $request, array & $response, array & $app) {
        $response['user_id'] = CTX()->get_session('user_id');
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('pur/ReturnNoticeRecordModel')->get_by_id($request['_id']);
        }
        //供应商
        $response['supplier'] = load_model('base/SupplierModel')->get_select(2);
        //调整仓库
        $response['store'] = load_model('base/StoreModel')->get_select(2);
        $ret['data']['record_code'] = load_model('pur/ReturnNoticeRecordModel')->create_fast_bill_sn();
        $response['data'] = $ret['data'];
    }

    /**
     * 查看库存调整单详情页, 包含基本信息和调整明细信息
     * @param array $request
     * @param array $response
     * @param array $app
     * @throws Exception
     */
    function view(array & $request, array & $response, array & $app) {
        //主单据信息
        $ret = load_model('pur/ReturnNoticeRecordModel')->get_by_id($request['return_notice_record_id']);
        //$ret = load_model('wbm/NoticeRecordModel')->get_by_id($request['return_notice_record_id']);


        $ret_store = load_model('base/StoreModel')->get_by_code($ret['data']['store_code']);
        $ret['data']['allow_negative_inv'] = $ret_store['data']['allow_negative_inv'];
        $response['selection']['custom'] = bui_get_select('custom');
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $response['selection']['supplier'] = load_model('base/SupplierModel')->get_view_select();
        $response['selection']['record_type'] = bui_get_select('record_type', 0, array('record_type_property' => 1));
        $ok = get_theme_url('images/ok.png');
        $no = get_theme_url('images/no.gif');
        if ($ret['data']['is_sure'] == '1') {
            $is_check_src = $ok;
        } else {
            $is_check_src = $no;
        }
        $ret['data']['is_check_src'] = "<img src='{$is_check_src}'>";
        if ($ret['data']['is_finish'] == '1') {
            $is_finish_src = $ok;
        } else {
            $is_finish_src = $no;
        }
        $ret['data']['is_finish_src'] = "<img src='{$is_finish_src}'>";
        $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($ret['data']['store_code']);
        if ($wms_system_code !== FALSE) {
            $ret['data']['is_wms'] = '1';
        } else {
            $ret['data']['is_wms'] = '0';
        }
        $response['data'] = $ret['data'];
        //spec1别名
        //spec1别名
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $response['price_status'] = $price_status['status'];

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    function do_add(array & $request, array & $response, array & $app) {
        $request['order_time'] = date('Y-m-d H:i:s', time());
        $stock_adjus = get_array_vars($request, array('record_code', 'order_time', 'record_type_code', 'supplier_code', 'init_code', 'record_time', 'store_code', 'rebate', 'remark'));
        $ret = load_model('pur/ReturnNoticeRecordModel')->insert($stock_adjus);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "创建", 'module' => "pur_return_notice_record", 'pid' => $ret['data']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 采购退货单增加明细
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-10
     *
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array json
     */
    function do_add_detail(array & $request, array & $response, array & $app) {
        //print_r($request);exit;
        $data = $request['data'];
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($request['id'], $data);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($request['id'], $request['store_code'], 'pur_return_notice', $data);
        if ($ret['status'] < 1) {
            return $ret;
        }
        //增加明细
        $ret = load_model('pur/ReturnNoticeRecordDetailModel')->add_detail_action($request['id'], $data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '增加明细', 'module' => "pur_return_notice_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 修改单据明细数量
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-11
     *
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array json
     */
    function do_edit_detail(array & $request, array & $response, array & $app) {
        $detail = get_array_vars($request, array('record_code', 'rebate', 'sku', 'num', 'price'));
        $ret = load_model('pur/ReturnNoticeRecordDetailModel')->edit_detail_action($request['return_notice_record_id'], array($detail));
        $res = load_model('pur/ReturnNoticeRecordModel')->get_by_id($request['return_notice_record_id']);
        $ret['res'] = $res['data'];
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/ReturnNoticeRecordModel')->delete($request['return_notice_record_id']);
        exit_json_response($ret);
    }

    //终止
    function do_stop(array &$request, array &$response, array &$app) {
        $ret = load_model('pur/ReturnNoticeRecordModel')->update_stop('1', 'is_stop', $request['id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '终止', 'action_name' => '终止', 'module' => "pur_return_notice_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //是否有未出库
    function out_relation(array &$request, array &$response, array &$app) {
        $ret = load_model('pur/ReturnNoticeRecordModel')->out_relation($request['id']);
        exit_json_response($ret);
    }

    /**
     * 确认/取消确认
     */
    function do_sure(array &$request, array &$response, array &$app) {

        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('pur/ReturnNoticeRecordModel')->update_sure($arr[$request['type']], 'is_sure', $request['id']);
        if ($ret['status'] == '1') {
            //日志
            if ($request['type'] == 'disable') {
                $action_name = '取消确认';
                $sure_status = '未确认';
            }
            if ($request['type'] == 'enable') {
                $action_name = '确认';
                $sure_status = '确认';
            }
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未出库', 'action_name' => $action_name, 'module' => "pur_return_notice_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        if ($ret['status'] == '-2') {
            $file_name = $this->create_import_fail_files($ret['message'], 'pur_notice_enable_err');
            $url = set_download_csv_url($file_name, array('export_name' => '采购退货通知单异常信息'));
            $msg = ",失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            $ret = array('status' => -1, 'message' => "可用库存不足，暂时无法锁定{$msg}");
        }
        exit_json_response($ret);
    }

    function create_import_fail_files($msg_arr, $name) {
        $fail_top = array('错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg_arr as $key => $val) {
            $file_str .= "库存不足的商品条形码：{$key}; 缺货数量:{$val}\r\n";
        }
        $filename = md5($name . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    /**
     * 扫描页面确认
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function scan_do_sure(array &$request, array &$response, array &$app) {
        $record = load_model('pur/ReturnNoticeRecordModel')->get_row(array('record_code' => $request['record_code']));
        $return_notice_record_id = $record['data']['return_notice_record_id'];
        $ret = load_model('pur/ReturnNoticeRecordModel')->update_sure(1, 'is_sure', $return_notice_record_id);
        if ($ret['status'] == '1') {
            //日志
            $action_name = '确认';
            $sure_status = '确认';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未出库', 'action_name' => $action_name, 'module' => "pur_return_notice_record", 'pid' => $return_notice_record_id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        if ($ret['status'] == '-2') {
            $file_name = $this->create_import_fail_files($ret['message'], 'pur_notice_enable_err');
            $url = set_download_csv_url($file_name, array('export_name' => '采购退货通知单异常信息'));
            $msg = ",失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            $ret = array('status' => -1, 'message' => "可用库存不足，暂时无法锁定{$msg}");
        }
        exit_json_response($ret);
    }

    /**
     * 删除单据明细
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-10
     * @param array $request
     * @param array $response
     * @param array $app
     * @throws Exception
     */
    function do_delete_detail(array & $request, array & $response, array & $app) {

        //print_r($request);exit;
        $ret = load_model('pur/ReturnNoticeRecordDetailModel')->delete($request['return_notice_record_detail_id']);
        //批次删除
        $ret1 = load_model('stm/GoodsInvLofRecordModel')->delete_pid($request['pid'], $request['sku'], 'pur_return_notice');
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "删除明细", 'module' => "pur_return_notice_record", 'pid' => $request['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //删除批次
    function do_delete_detail_lof(array & $request, array & $response, array & $app) {
        //print_r($request);exit;
        $ret = load_model('pur/ReturnNoticeRecordDetailModel')->delete_lof($request['id']);

        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "删除明细", 'module' => "pur_return_notice_record", 'pid' => $request['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 采购退货通知单生成采购退货单
     */
    function execute(array & $request, array & $response, array & $app) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('pur/return_notice_record/do_execute')) {
            return $this->format_ret(-1, array(), '无权访问');
        }
        //主单据信息
        $ret = load_model('pur/ReturnNoticeRecordModel')->get_by_id($request['return_notice_record_id']);
        $response['data'] = $ret['data'];
    }

    /**
     * 采购退货通知单生成采购退货单
     */
    function create_return_record(array &$request, array &$response, array &$app) {
        $return_notice_record = get_array_vars($request, array('return_notice_record_id', 'create_type', 'record_code'));
        $ret = load_model('pur/ReturnNoticeRecordModel')->create_return_record($return_notice_record);
        exit_json_response($ret);
    }

    /**
     * 修改调整单主单据
     * @param array $request
     * @param array $response
     * @param array $app
     * @return string json
     */
    function do_edit(array &$request, array &$response, array &$app) {
        //print_r($request);exit;
        $ret = load_model('pur/ReturnNoticeRecordModel')->edit_action($request['parameter'], array('return_notice_record_id' => $request['parameterUrl']['return_notice_record_id']));
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '修改', 'module' => "pur_return_notice_record", 'pid' => $request['parameterUrl']['return_notice_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 库存调整单导入
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function import_goods(array & $request, array & $response, array & $app) {
        $response['id'] = $request['id'];
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
    }

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
        $ret = load_model('pur/ReturnNoticeRecordModel')->imoprt_detail($request['id'], $file, $request['is_lof']);
        $response = $ret;
    }

    /**
     * 导出明细csv
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $ret = load_model('pur/ReturnNoticeRecordModel')->get_by_id($request['id']);
        $main_result = $ret['data'];
        $main_result['record_time'] = date('Y-m-d', strtotime($main_result['record_time']));
        $filter['record_code'] = $request['return_notice_code'];
        $filter['page'] = 1;
        $filter['page_size'] = 1000;
        //print_r($detail_result);exit;
        $str = "单据编号,原单号,供应商,仓库,业务日期,下单日期,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,系统SKU码,标准进价,数量,金额,完成数,差异数\n";
        $str = iconv('utf-8', 'gbk', $str);
        $supplier_name = oms_tb_val('base_supplier', 'supplier_name', array('supplier_code' => $main_result['supplier_code']));
        $store_code_name = oms_tb_val('base_store', 'store_name', array('store_code' => $main_result['store_code']));
        $supplier_name = iconv('utf-8', 'gbk', $supplier_name);
        $store_code_name = iconv('utf-8', 'gbk', $store_code_name);

        while (true) {
            $res = load_model('pur/ReturnNoticeRecordDetailModel')->get_by_page($filter);
            $detail_result = $res['data']['data'];
            foreach ($detail_result as $value) {
                $value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));
                $value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));
                $value['goods_name'] = mb_convert_encoding(str_replace("\xC2\xA0", ' ', $value['goods_name']), 'GBK', 'UTF-8'); //中英文混合并且带空格的
                $value['goods_code'] = iconv('utf-8', 'gbk', $value['goods_code']);
                $value['barcode'] = iconv('utf-8', 'gbk', $value['barcode']);
                $value['sku'] = iconv('utf-8', 'gbk', $value['sku']);
                $value['spec1_code_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
                $value['spec2_code_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
                $str .= $main_result['record_code'] . "," . $main_result['init_code'] . "," . $supplier_name . "," . $store_code_name . "," . $main_result['record_time'] .
                        "," . $main_result['order_time'] . "," . $value['goods_name'] . "," . $value['goods_code'] . "\t" . "," . $value['spec1_code'] . "\t" . "," . $value['spec1_code_name'] .
                        "," . $value['spec2_code'] . "\t" . "," . $value['spec2_code_name'] . "," . $value['barcode'] . "\t" . "," . $value['sku'] . "\t" . "," . $value['price'] . "," . $value['num'] . "," . $value['money'] . "," . $value['finish_num'] . "," . $value['diff_num'] . "\n"; //用引文逗号分开
            }
            if (count($detail_result) < $filter['page_size']) {
                break;
            }
            $filter['page'] += 1;
        }

        $filename = date('Ymd') . '.csv'; //设置文件名
        $this->export_csv($filename, $str); //导出
    }

    // 导出方法
    function export_csv($filename, $data) {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
        die;
    }

    /**
     * 修改扫描数量
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function update_scan_num(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/ReturnNoticeRecordDetailModel')->update_scan_num($request['record_code'], $request['num'], $request['id']);
        exit_json_response($ret);
    }

}
