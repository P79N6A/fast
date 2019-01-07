<?php

require_lib('util/web_util', true);
require_lib('business_util', true);

class return_record {

    function do_list(array & $request, array & $response, array & $app) {
        $response['user_id'] = CTX()->get_session('user_id');
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('pur/ReturnRecordModel')->get_by_id($request['_id']);
        }
        //供应商
        $response['supplier'] = load_model('base/SupplierModel')->get_select(2);
        //调整仓库
        $response['store'] = load_model('base/StoreModel')->get_select(2);
        //require_lib ( 'comm_util', true );
        //$ret['data']['record_code'] = create_fast_bill_sn('return');
        $ret['data']['record_code'] = load_model('pur/ReturnRecordModel')->create_fast_bill_sn();
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
        $ret = load_model('pur/ReturnRecordModel')->get_by_id($request['return_record_id']);
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $response['selection']['supplier'] = load_model('base/SupplierModel')->get_view_select();

        $response['selection']['record_type'] = bui_get_select('record_type', 0, array('record_type_property' => 1));
        ;
        $ok = get_theme_url('images/ok.png');
        $no = get_theme_url('images/no.gif');
        if ($ret['data']['is_check'] == '1') {
            $is_check_src = $ok;
        } else {
            $is_check_src = $no;
        }
        $ret['data']['is_check_src'] = "<img src='{$is_check_src}'>";
        if ($ret['data']['is_store_out'] == '1') {
            $is_store_out_src = $ok;
        } else {
            $is_store_out_src = $no;
        }
        $ret['data']['is_store_out_src'] = "<img src='{$is_store_out_src}'>";
        $response['data'] = $ret['data'];
        //spec1别名
        //spec1别名
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status', 'clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $response['price_status'] = $price_status['status'];
        $ret_store = load_model('base/StoreModel')->get_by_code($ret['data']['store_code']);
        $response['allow_negative_inv'] = $ret_store['data']['allow_negative_inv'];
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    function do_add(array & $request, array & $response, array & $app) {
        $request['order_time'] = date('Y-m-d H:i:s', time());
        $stock_adjus = get_array_vars($request, array('record_code', 'order_time', 'relation_code', 'record_time', 'record_type_code', 'supplier_code', 'store_code', 'rebate', 'remark'));
        $ret = load_model('pur/ReturnRecordModel')->insert($stock_adjus);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未出库', 'action_name' => "新增", 'module' => "return_record", 'pid' => $ret['data'], 'action_note' => '手工生成');
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
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($request['id'], $request['store_code'], 'pur_return', $data);
        if ($ret['status'] < 1) {
            return $ret;
        }
        //增加明细
        $ret = load_model('pur/ReturnRecordDetailModel')->add_detail_action($request['id'], $data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '增加明细', 'module' => "return_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 修改单据明细
     */
    function do_edit_detail(array & $request, array & $response, array & $app) {
        $detail = get_array_vars($request, array('record_code', 'rebate', 'sku', 'num', 'price'));
        $ret = load_model('pur/ReturnRecordDetailModel')->edit_detail_action($request['pid'], array($detail));
        $res = load_model('pur/ReturnRecordModel')->get_by_id($request['pid']);
        $ret['res'] = $res['data'];
        exit_json_response($ret);
    }

    /**
     * 修改批次单据明细数量
     */
    function edit_detail_action_lof(array & $request, array & $response, array & $app) {
        $detail = get_array_vars($request, array('sku', 'price', 'rebate'));
        $detail_lof = get_array_vars($request, array('num', 'record_code', 'lof_no', 'production_date'));
        $ret = load_model('pur/ReturnRecordDetailModel')->edit_detail_action_lof($request['pid'], $detail, $detail_lof);
        $res = load_model('pur/ReturnRecordModel')->get_by_id($request['pid']);
        $ret['res'] = $res['data'];
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/ReturnRecordModel')->delete($request['return_record_id']);
        exit_json_response($ret);
    }

    /*     * 扫描单据添加数量
     * @param array $request
     * @param array $response
     * @param array $app
     */

    function update_scan_num(array & $request, array & $response, array & $app) {
        if ($request['is_lof'] == 1) {
            $ret = load_model('pur/ReturnRecordDetailModel')->update_scan_num_lof($request);
        } else {
            $ret = load_model('pur/ReturnRecordDetailModel')->update_scan_num($request);
        }
        exit_json_response($ret);
    }

    /**
     * 采购入库单出库
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-11
     * @param array $request
     * @param array $response
     * @param array $app
     * @return string json
     */
    function do_checkout(array & $request, array & $response, array & $app) {
        $power = load_model('sys/PrivilegeModel')->check_priv('pur/return_record/do_checkin');
        if ($power) {
            $check = isset($request['is_scan_tag']) ? 0 : 1;
            $ret = load_model('pur/ReturnRecordModel')->checkout($request['record_code'], '', '', '', $check);
            if ($ret['status'] == 1) {
                //日志
                $record = load_model('pur/ReturnRecordModel')->get_row(array('record_code' => $request['record_code']));
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '验收', 'action_name' => '验收', 'module' => "return_record", 'pid' => $record['data']['return_record_id']);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
        } else {
            $ret = array(
                'status' => -401,
                'data' => '',
                'message' => '没有验收权限'
            );
        }
        exit_json_response($ret);
    }

    //检查差异数
    function check_diff_num(array & $request, array & $response, array & $app) {
        $response = load_model('pur/ReturnRecordModel')->check_diff_num($request['record_code']);
        exit_json_response($response);
    }

    /**
     * 按业务日期验收
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_checkout_by_record_date(array & $request, array & $response, array & $app) {
        $check = isset($request['is_scan_tag']) ? 0 : 1;
        $ret = load_model('pur/ReturnRecordModel')->checkout($request['record_code'], '', '', '', $check, 0, 1);
        if ($ret['status'] == 1) {
            //日志
            $record = load_model('pur/ReturnRecordModel')->get_row(array('record_code' => $request['record_code']));
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '验收', 'action_name' => '按业务日期验收', 'module' => "return_record", 'pid' => $record['data']['return_record_id']);
            load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 审核
     */
    function do_check(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('pur/ReturnRecordModel')->update_check_new($arr[$request['type']], 'is_check', $request['id']);
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
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未出库', 'action_name' => $action_name, 'module' => "return_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
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
//    	print_r($request);exit;
        $ret = load_model('pur/ReturnRecordDetailModel')->delete($request['return_record_detail_id']);
        //批次删除
        $ret1 = load_model('stm/GoodsInvLofRecordModel')->delete_pid($request['pid'], $request['sku'], 'pur_return');
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '删除明细', 'module' => "return_record", 'pid' => $request['pid']);
            $ret2 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //删除批次
    function do_delete_detail_lof(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/ReturnRecordDetailModel')->delete_lof($request['id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '删除明细', 'module' => "return_record", 'pid' => $request['pid']);
            $ret2 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    function update_check(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('stm/StockAdjustRecordModel')->update_check($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }

    /**
     * 修改调整单主单据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-12
     *
     * @param array $request
     * @param array $response
     * @param array $app
     * @return string json
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $record = load_model('pur/ReturnRecordModel')->get_row(array('return_record_id' => $request['parameterUrl']['return_record_id']));
        $record_data = $record['data'];
        $ret = load_model('pur/ReturnRecordModel')->edit_action($request['parameter'], array('return_record_id' => $request['parameterUrl']['return_record_id']));
        if ($ret['status'] == '1') {
            //日志
            $finish_status = ($record_data['is_store_out'] == 0) ? '未验收' : '已验收';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => $finish_status, 'action_name' => '修改', 'module' => "return_record", 'pid' => $request['parameterUrl']['return_record_id']);
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
    function import(array & $request, array & $response, array & $app) {

    }

    //导入商品
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
        $ret = load_model('pur/ReturnRecordModel')->imoprt_detail($request['id'], $file, $request['is_lof']);
        $response = $ret;
    }

    function export_csv_list(array & $request, array & $response, array & $app) {
        $detail_result = load_model('pur/ReturnRecordModel')->get_detail_by_record_code($request['record_code'], $request['lof_status'], $request['goods_code']);
        $plus_str = ($request['lof_status'] == 1) ? "批次号,生产日期,进货价" : "进货价";
        $str = "验收,单据编号,退货通知单号,下单时间,供应商,仓库,折扣,退货类型,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,{$plus_str},进货单价,实际退货数,金额,通知退货数,差异数\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($detail_result as $value) {
            $value['is_store_out'] = iconv('utf-8', 'gbk', $value['is_store_out']);
            $value['supplier_name'] = iconv('utf-8', 'gbk', $value['supplier_name']);
            $value['store_name'] = iconv('utf-8', 'gbk', $value['store_name']);
            $value['goods_name'] = mb_convert_encoding(str_replace("\xC2\xA0", ' ', $value['goods_name']), 'GBK', 'UTF-8'); //中英文混合并且带空格的
            $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
            $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
            $value['barcode'] = iconv('utf-8', 'gbk', $value['barcode']);
            $value['goods_code'] = iconv('utf-8', 'gbk', $value['goods_code']);
            $value['record_type_name'] = iconv('utf-8', 'gbk', $value['record_type_name']);
            $str_plus = ($request['lof_status'] == 1) ? ($value['lof_no'] . "," . $value['production_date'] . "," . $value['price']) : $value['price'];
            $str .= $value['is_store_out'] . "," . $value['record_code'] . "," . $value['relation_code'] . "," . $value['order_time'] . "," . $value['supplier_name'] .
                    "," . $value['store_name'] . "," . $value['rebate'] . "," . $value['record_type_name'] . "," . $value['goods_name'] . "," . $value['goods_code'] . "," . $value['spec1_code'] . "," . $value['spec1_name'] .
                    "," . $value['spec2_code'] . "," . $value['spec2_name'] . "," . $value['barcode'] . "," . $str_plus . "," . $value['per_price'] . "," . $value['num'] .
                    "," . $value['money'] . "," . $value['enotice_num'] . "," . $value['num_differ'] . "\n"; //用引文逗号分开
        }
        $filename = date('Ymd') . '.csv'; //设置文件名
        $this->export_csv($filename, $str); //导出
    }

    function export_csv($filename, $data) {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
        die;
    }

    function check_is_print(array & $request, array & $response, array & $app) {
        $response = load_model('pur/ReturnRecordModel')->check_is_print($request['return_record_id']);
    }

}
