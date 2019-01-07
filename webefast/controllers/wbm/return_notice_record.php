<?php

require_lib('util/web_util', true);
require_lib('business_util', true);

class return_notice_record {

    function do_list(array & $request, array & $response, array & $app) {
        $custom = load_model('base/CustomModel');
        $fenxiao = $custom->get_purview_custom_select('pt_fx');
        $response['fenxiao'] = $custom->array_order($fenxiao, 'custom_name');
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        //退单类型
        $response['return_type'] = load_model('wbm/ReturnNoticeRecordModel')->get_return_type();
        //分销商
//		$response['custom'] = ds_get_select('custom', 2, array('custom_type'=>'pt_fx'));
        $custom = load_model('base/CustomModel');
        $fenxiao = $custom->get_purview_custom_select('pt_fx', 2);
        $response['custom'] = $custom->array_order($fenxiao, 'custom_name');

// 		//调整仓库
        $response['store'] = load_model('base/StoreModel')->get_purview_store();

        $ret['data']['return_notice_code'] = load_model('wbm/ReturnNoticeRecordModel')->create_fast_bill_sn();
        $response['data'] = $ret['data'];
    }

    function do_add(array & $request, array & $response, array & $app) {
        $request['order_time'] = date('Y-m-d H:i:s', time());
        $return_notice_record = get_array_vars($request, array('return_notice_code', 'init_code', 'order_time', 'custom_code', 'store_code', 'return_type_code', 'rebate', 'remark'));
        $ret = load_model('wbm/ReturnNoticeRecordModel')->insert($return_notice_record);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "创建", 'module' => "wbm_return_notice_record", 'pid' => $ret['data']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $ret = load_model('wbm/ReturnNoticeRecordModel')->edit_action($request['parameter'], array('return_notice_record_id' => $request['parameterUrl']['return_notice_record_id']));
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未入库', 'action_name' => '修改', 'module' => "wbm_return_notice_record", 'pid' => $request['parameterUrl']['return_notice_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //查看详情
    function view(array & $request, array & $response, array & $app) {
        //主单据信息
        $ret = load_model('wbm/ReturnNoticeRecordModel')->get_by_id($request['return_notice_record_id']);


        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $response['selection']['custom'] = bui_get_select('custom');
        //$return_type = load_model('wbm/ReturnNoticeRecordModel')->get_return_type();
        $response['selection']['record_type'] = bui_get_select('record_type', 0, array('record_type_property' => 3));

        $ok = get_theme_url('images/ok.png');
        $no = get_theme_url('images/no.gif');
        if ($ret['data']['is_check'] == '1') {
            $is_check_src = $ok;
        } else {
            $is_check_src = $no;
        }
        $ret['data']['is_check_src'] = "<img src='{$is_check_src}'>";

        if ($ret['data']['is_return'] == '1') {
            $is_return_src = $ok;
        } else {
            $is_return_src = $no;
        }
        $ret['data']['is_return_src'] = "<img src='{$is_return_src}'>";

        if ($ret['data']['is_finish'] == '1') {
            $is_finish_src = $ok;
        } else {
            $is_finish_src = $no;
        }
        $ret['data']['is_finish_src'] = "<img src='{$is_finish_src}'>";

        $response['data'] = $ret['data'];
        $response['is_store_in'] = $ret['is_store_in'];
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';


        $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($ret['data']['store_code']);
        if ($wms_system_code !== FALSE) {
            $response['is_wms'] = 1;
        } else {
            $response['is_wms'] = 0;
        }

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    //增加明细
    function do_add_detail(array & $request, array & $response, array & $app) {
        $data = $request['data'];
        //调整单明细添加
        $ret = load_model('wbm/ReturnNoticeDetailRecordModel')->add_detail_action($request['id'], $data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '增加明细', 'module' => "wbm_return_notice_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //删除明细
    function do_delete_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/ReturnNoticeDetailRecordModel')->delete($request['return_notice_record_detail_id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '删除明细', 'module' => "wbm_return_notice_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //删除主单信息
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/ReturnNoticeRecordModel')->do_delete($request['return_notice_code']);
        exit_json_response($ret);
    }

    /**
     * 审核
     */
    function do_check(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('wbm/ReturnNoticeRecordModel')->update_check($arr[$request['type']], 'is_check', $request['return_notice_code']);
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
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未完成', 'action_name' => $action_name, 'module' => "wbm_return_notice_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //生产退货单
    function create_return_record(array &$request, array &$response, array &$app) {
        $return_notice_record = load_model('wbm/ReturnNoticeRecordModel')->get_by_id($request['return_notice_record_id']);
        $ret = load_model('wbm/ReturnNoticeRecordModel')->check_status($return_notice_record);
        if ($ret['status'] == 1) {
            $ret = load_model('wbm/ReturnRecordModel')->create_return_record($return_notice_record['data'], $request['create_type']);
            if ($ret['status'] == 1) {
                $sql = "update wbm_return_notice_record set is_return = 1 where return_notice_code='" . $return_notice_record['data']['return_notice_code'] . "'";
                CTX()->db->query($sql);
            }
        }
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未完成', 'action_name' => '生成退单', 'module' => "wbm_return_notice_record", 'pid' => $request['return_notice_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    function do_return(array &$request, array &$response, array &$app) {
        $return_notice_record = load_model('wbm/ReturnNoticeRecordModel')->get_by_id($request['return_notice_record_id']);
        $response['data'] = $return_notice_record['data'];
    }

    /**
     * 完成
     */
    function do_finish(array &$request, array &$response, array &$app) {
        $ret = load_model('wbm/ReturnNoticeRecordModel')->do_finish($request['return_notice_code'], $request['id']);

        exit_json_response($ret);
    }

    function bui_wbm_type($pur_type) {
        $result = $pur_type;
        $return = array();
        if (empty($result) || !is_array($result)) {
            return json_encode($return);
        }
        $array_keys = array_keys($result[0]);
        foreach ($result as $data) {
            $return[] = array(
                'value' => $data[$array_keys[0]],
                'text' => $data[$array_keys[1]],
            );
        }
        return json_encode($return);
    }

    /**
     * 导出明细csv
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $ret = load_model('wbm/ReturnNoticeRecordModel')->get_by_id($request['id']);
        $main_result = $ret['data'];
        $filter['return_notice_code'] = $request['return_notice_code'];
        $filter['page'] = 1;
        $filter['page_size'] = 1000;


        //print_r($detail_result);exit;
        $str = "单据编号,原单号,分销商,仓库,退单类型,下单日期,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,系统SKU码,批发价,数量,金额,完成数,差异数\n";
        $str = iconv('utf-8', 'gbk', $str);

        $custom_name = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $main_result['custom_code']));
        $store_code_name = oms_tb_val('base_store', 'store_name', array('store_code' => $main_result['store_code']));
        $return_type = load_model('wbm/ReturnNoticeRecordModel')->return_type[$main_result['return_type_code']];
        $return_type = iconv('utf-8', 'gbk', $return_type);
        $custom_name = iconv('utf-8', 'gbk', $custom_name);
        $store_code_name = iconv('utf-8', 'gbk', $store_code_name);

        while (true) {
            $res = load_model('wbm/ReturnNoticeDetailRecordModel')->get_by_page($filter);
            $detail_result = $res['data']['data'];
            foreach ($detail_result as $value) {
                $value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));
                $value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));
                $value['barcode'] = iconv('utf-8', 'gbk', $value['barcode']);
                $value['goods_code'] = iconv('utf-8', 'gbk', $value['goods_code']);
                $value['sku'] = iconv('utf-8', 'gbk', $value['sku']);
                $value['goods_name'] = mb_convert_encoding(str_replace("\xC2\xA0", ' ', $value['goods_name']), 'GBK', 'UTF-8'); //中英文混合并且带空格的
                $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
                $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
                $str .= $main_result['return_notice_code'] . "," . $main_result['init_code'] . "," . $custom_name . "," . $store_code_name . "," . $return_type .
                        "," . $main_result['order_time'] . "," . $value['goods_name'] . "," . $value['goods_code'] . "\t" . "," . $value['spec1_code'] . "\t" . "," . $value['spec1_name'] .
                        "," . $value['spec2_code'] . "\t" . "," . $value['spec2_name'] . "," . $value['barcode'] . "\t" . "," . $value['sku'] . "\t" . "," . $value['trade_price'] . "," . $value['num'] . "," . $value['money'] . "," . $value['finish_num'] . "," . $value['difference_num'] . "\n"; //用引文逗号分开
            }
            if (count($detail_result) < $filter['page_size']) {
                break;
            }
            $filter['page'] += 1;
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

    //导入商品
    function import_goods(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }

        $ret = load_model('wbm/ReturnNoticeRecordModel')->imoprt_detail($request['id'], $file);
        $response = $ret;
    }

    function importGoods(array & $request, array & $response, array & $app) {
        $response['id'] = $request['id'];
        // $arr = array('lof_status');
        // $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        // $response['lof_status'] =isset($ret_arr['lof_status'])?$ret_arr['lof_status']:'' ;
    }

    /**
     * @todo 编辑批发退货通知单详细信息
     */
    function do_edit_detail(array & $request, array & $response, array & $app) {
        $detail = array(
            array('trade_price' => $request['trade_price'], 'num' => $request['num'], 'price' => $request['trade_price'] * $request['rebate'], 'sku' => $request['sku'], 'money' => $request['trade_price'] * $request['rebate'] * $request['num']),
        );
        $ret = load_model('wbm/ReturnNoticeDetailRecordModel')->edit_detail_action($request['pid'], $detail);
        $res = load_model('wbm/ReturnNoticeRecordModel')->get_by_id($request['pid']);
        $ret['res'] = $res['data'];
        exit_json_response($ret);
    }

    //是否有未出库
    function out_relation(array &$request, array &$response, array &$app) {
        $ret = load_model('wbm/ReturnNoticeRecordModel')->out_relation($request['id']);
        exit_json_response($ret);
    }

}
