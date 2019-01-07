<?php

require_lib('util/web_util', true);
require_lib('business_util', true);

class return_record {

    function do_list(array & $request, array & $response, array & $app) {
        $custom = load_model('base/CustomModel');
        $fenxiao = $custom->get_purview_custom_select('pt_fx');
        $response['fenxiao'] = $custom->array_order($fenxiao, 'custom_name');
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('wbm/ReturnRecordModel')->get_by_id($request['_id']);
        }
        // 分销商
        $custom = load_model('base/CustomModel');
        $fenxiao = $custom->get_purview_custom_select('pt_fx', 2);
        $response['custom'] = $custom->array_order($fenxiao, 'custom_name');

        $ret['data']['record_code'] = load_model('wbm/ReturnRecordModel')->create_fast_bill_sn();
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
        $ret = load_model('wbm/ReturnRecordModel')->get_by_id($request['return_record_id']);

        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
//        $response['selection']['custom'] = bui_get_select('custom', 0, array('custom_type'=>'pt_fx'));

        $response['selection']['custom'] = load_model('base/CustomModel')->get_purview_custom_select('pt_fx', 3);

        $response['selection']['record_type'] = bui_get_select('record_type', 0, array('record_type_property' => 3));
        //查询绑定的通知单是否经销生成
        if (isset($ret['data']['relation_code']) && !empty($ret['data']['relation_code'])) {
            $record_notice = load_model('wbm/ReturnNoticeRecordModel')->is_exists($ret['data']['relation_code'], 'return_notice_code');
            if (isset($record_notice['data']['jx_return_code']) && !empty($record_notice['data']['jx_return_code'])) {
                $response['is_fenxiao'] = 1;
            }
        }

        $ok = get_theme_url('images/ok.png');
        $no = get_theme_url('images/no.gif');
        if ($ret['data']['is_sure'] == '1') {
            $is_sure_src = $ok;
        } else {
            $is_sure_src = $no;
        }
        $ret['data']['is_sure_src'] = "<img src='{$is_sure_src}'>";
        if ($ret['data']['is_store_in'] == '1') {
            $is_store_in_src = $ok;
        } else {
            $is_store_in_src = $no;
        }
        $ret['data']['is_store_in_src'] = "<img src='{$is_store_in_src}'>";
        $response['data'] = $ret['data'];
        $response['data']['record_time'] = date("Y-m-d", strtotime($response['data']['record_time']));
        $response['data']['enotice_num_all'] = load_model('wbm/ReturnRecordDetailModel')->get_enotice_num_all($ret['data']['record_code']);
        //spec1别名
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status', 'clodop_print', 'print_template_choose');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
        $response['print_template_choose'] = isset($ret_arr['print_template_choose']) ? $ret_arr['print_template_choose'] : 0;

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    //导入商品
    function importGoods(array & $request, array & $response, array & $app) {
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
        //$response = load_model('prm/GoodsImportModel')->import_base_spec1($file);

        $ret = load_model('wbm/ReturnRecordModel')->imoprt_detail($request['id'], $file, $request['is_lof']);
        $response = $ret;
        //$response['url'] = $_FILES['fileData']['name'];
        //   set_uplaod($request, $response, $app);
    }

    function do_add(array & $request, array & $response, array & $app) {
        $request['order_time'] = date('Y-m-d H:i:s', time());
        $stock_adjus = get_array_vars($request, array('record_code', 'order_time', 'init_code', 'record_time', 'distributor_code', 'store_code', 'rebate', 'remark', 'record_type_code'));
        $ret = load_model('wbm/ReturnRecordModel')->insert($stock_adjus);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未入库', 'action_name' => "新增", 'module' => "wbm_return_record", 'pid' => $ret['data'], 'action_note' => '手工生成');
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 采购入库单增加明细
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-10
     *
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array json
     */
    function do_add_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/ReturnRecordModel')->add_detail_goods($request['id'], $request['data'], $request['store_code']);
        exit_json_response($ret);
    }

    /**
     * 修改单据明细数量
     */
    function do_edit_detail(array & $request, array & $response, array & $app) {
        //查询订单状态
        $ret = CTX()->db->get_row("select is_sure from wbm_return_record where record_code = :record_code", array(':record_code' => $request["record_code"]));
        if ($ret['is_sure'] == 1) {
            $ret = array('status' => -1, 'message' => '单据已经验收不能修改');
            exit_json_response($ret);
        }
        $detail = get_array_vars($request, array('record_code', 'rebate', 'sku', 'num', 'price'));
        $ret = load_model('wbm/ReturnRecordDetailModel')->edit_detail_action($request['pid'], array($detail));
        $res = load_model('wbm/ReturnRecordModel')->get_by_id($request['pid']);
        $ret['res'] = $res['data'];
        exit_json_response($ret);
    }

    /**
     * 修改批次明细入库数
     */
    function do_edit_detail_lof(array & $request, array & $response, array & $app) {
        $detail = get_array_vars($request, array('sku', 'price', 'rebate'));
        $detail_lof = get_array_vars($request, array('num', 'record_code', 'lof_no', 'production_date'));
        $ret = load_model('wbm/ReturnRecordDetailModel')->edit_detail_action_lof($request['pid'], $detail, $detail_lof);
        $res = load_model('wbm/ReturnRecordModel')->get_by_id($request['pid']);
        $ret['res'] = $res['data'];
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/ReturnRecordModel')->delete($request['return_record_id']);
        exit_json_response($ret);
    }

    /**
     * 确认/取消确认
     */
    function do_sure(array &$request, array &$response, array &$app) {

        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('wbm/ReturnRecordModel')->update_sure($arr[$request['type']], 'is_sure', $request['id']);
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
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未出库', 'action_name' => $action_name, 'module' => "wbm_return_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //检查差异数
    function check_diff_num(array & $request, array & $response, array & $app) {
        $response = load_model('wbm/ReturnRecordModel')->check_diff_num($request['record_code']);
        exit_json_response($response);
    }

    /**
     * 采购入库单入库
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-11
     * @param array $request
     * @param array $response
     * @param array $app
     * @return string json
     */
    function do_shift_in(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/ReturnRecordModel')->shift_in($request['record_code']);
        if ($ret['status'] == '1') {
            //日志
            $record = load_model('wbm/ReturnRecordModel')->get_row(array('record_code' => $request['record_code']));
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '验收', 'action_name' => '验收', 'module' => "wbm_return_record", 'pid' => $record['data']['return_record_id']);
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

        $ret = load_model('wbm/ReturnRecordDetailModel')->delete($request['return_record_detail_id']);
        //批次删除
        $ret1 = load_model('stm/GoodsInvLofRecordModel')->delete_pid($request['pid'], $request['sku'], 'wbm_return');
        exit_json_response($ret);
    }

    //删除批次
    function do_delete_detail_lof(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/ReturnRecordDetailModel')->delete_lof($request['id']);
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
        $ret = load_model('wbm/ReturnRecordModel')->edit_action($request['parameter'], array('return_record_id' => $request['parameterUrl']['return_record_id']));
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

    /**
     * 导出csv
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $ret = load_model('wbm/ReturnRecordModel')->get_by_id($request['id']);
        $main_result = $ret['data'];
        $main_result['store_name'] = load_model('base/StoreModel')->get_by_code($main_result[store_code]);
        $main_result['distributor_code'] = load_model('base/CustomModel')->get_by_code($main_result['distributor_code']);

        $filter['record_code'] = $main_result['record_code'];
        $filter['page'] = 1;
        $filter['page_size'] = 5000;
        $filter['code_name'] = $request['goods_code'];

        $res = load_model('wbm/ReturnRecordDetailModel')->get_by_page($filter);
        $detail_result = $res['data']['data'];

        $str = "单据编号,原单号,通知单号,分销商,仓库,下单日期,业务日期,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,批发价,单价,实际退货数,通知数,差异数,商品总金额,备注\n";
        $str = iconv('utf-8', 'gbk', $str);
        $main_result['distributor_code']['data']['custom_name'] = iconv('utf-8', 'gbk', $main_result['distributor_code']['data']['custom_name']);
        $main_result['store_name']['data']['store_name'] = iconv('utf-8', 'gbk', $main_result['store_name']['data']['store_name']);
        $main_result['remark'] = iconv('utf-8', 'gbk', $main_result['remark']);
        foreach ($detail_result as $value) {
            $value['goods_name'] = mb_convert_encoding(str_replace("\xC2\xA0", ' ', $value['goods_name']), 'GBK', 'UTF-8'); //中英文混合并且带空格的
            $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
            $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
            $value['barcode'] = iconv('utf-8', 'gbk', $value['barcode']);
            $value['goods_code'] = iconv('utf-8', 'gbk', $value['goods_code']);
            if ($request['is_lof'] == 1) {
                $value['enotice_num'] = $value['init_num'];
            }
            $str .= $main_result['record_code'] . "\t," . $main_result['init_code'] . "," . $main_result['relation_code'] . "," . $main_result['distributor_code']['data']['custom_name'] . "," . $main_result['store_name']['data']['store_name'] .
                    "," . $main_result['order_time'] . "\t," . $main_result['record_time'] . "\t," . $value['goods_name'] . "," . $value['goods_code'] . "\t," . $value['spec1_code'] . "\t," . $value['spec1_name'] .
                    "," . $value['spec2_code'] . "\t," . $value['spec2_name'] . "\t," . $value['barcode'] . "\t," . $value['price'] . "," . $value['price1'] .
                    "," . $value['num'] . "," . $value['enotice_num'] . "," . $value['different_num'] . "," . $value['money'] . "," . $main_result['remark'] . "\n"; //用引文逗号分开
        }
        $filename = date('Ymd') . '.csv'; //设置文件名
        header_download_csv($filename, $str); //导出
        echo $str;
        die;
    }

    /**
     * 差异数页面
     */
    function diff_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/ReturnRecordModel')->get_by_field('record_code', $request['record_code']);
        $response['order_time'] = $ret['data']['order_time'];
    }

    //扫描单据添加数量
    function update_scan_num(array & $request, array & $response, array & $app) {
        if ($request['is_lof'] == 1) {
            $ret = load_model('wbm/ReturnRecordDetailModel')->update_scan_num_lof($request);
        } else {
            $ret = load_model('wbm/ReturnRecordDetailModel')->update_scan_num($request['record_code'], $request['num'], $request['id']);
        }
        exit_json_response($ret);
    }

    public function check_is_print(array & $request, array & $response, array & $app) {
        $response = load_model('wbm/ReturnRecordModel')->check_is_print($request['return_record_id']);
    }

}
