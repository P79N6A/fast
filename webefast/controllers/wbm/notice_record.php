<?php

require_lib('util/web_util', true);
require_lib('business_util', true);

class notice_record {

    function do_list(array & $request, array & $response, array & $app) {
        $custom = load_model('base/CustomModel');
        $fenxiao = $custom->get_purview_custom_select('pt_fx');
        $response['fenxiao'] = $custom->array_order($fenxiao, 'custom_name');
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('wbm/NoticeRecordModel')->get_by_id($request['_id']);
        }

        $ret['data']['record_code'] = load_model('wbm/NoticeRecordModel')->create_fast_bill_sn();
        $response['data'] = $ret['data'];
        $custom = load_model('base/CustomModel');
        $fenxiao = $custom->get_purview_custom_select('pt_fx', 2);
        $response['fenxiao'] = $custom->array_order($fenxiao, 'custom_name');
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
        $ret = load_model('wbm/NoticeRecordModel')->get_by_id($request['notice_record_id']);

        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $response['selection']['custom'] = load_model('base/CustomModel')->get_purview_custom_select('pt_fx', 3);

        $response['selection']['record_type'] = bui_get_select('record_type', 0, array('record_type_property' => 2));
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
        //spec1别名
        //spec1别名
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status', 'clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
        $response['area']['country'] = load_model('base/TaobaoAreaModel')->get_area('0');
        if ($ret['data']['country'] == '0' && $ret['data']['province'] == '0' && $ret['data']['city'] == '0' && $ret['data']['district'] == '0') {
            $area = load_model('base/CustomModel')->get_by_code($ret['data']['distributor_code']);
            $ret['data']['province'] = $area['data']['province'];
            $ret['data']['city'] = $area['data']['city'];
            $ret['data']['district'] = $area['data']['district'];
        }
        if ($ret['data']['country'] == '0' && $ret['data']['province'] != '0') {
            $ret['data']['country'] = '1';
        }
        //$response['area']['province'] = array();
        if ($ret['data']['country'] != '0') {
            $response['area']['province'] = load_model('base/TaobaoAreaModel')->get_area($ret['data']['country']);
        }
        if ($ret['data']['province'] != '0') {
            $response['area']['city'] = load_model('base/TaobaoAreaModel')->get_area($ret['data']['province']);
        }
        if ($ret['data']['city'] != '0') {
            $response['area']['district'] = load_model('base/TaobaoAreaModel')->get_area($ret['data']['city']);
        }
        if ($ret['data']['district'] != '0') {
            $response['area']['street'] = load_model('base/TaobaoAreaModel')->get_area($ret['data']['district']);
        }
        $country = oms_tb_val('base_area', 'name', array('id' => $ret['data']['country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $ret['data']['province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $ret['data']['city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $ret['data']['district']));
        $ret['data']['addr'] = $country . $province . $city . $district;
        $ret['data']['diff_num'] = $ret['data']['num'] - $ret['data']['finish_num']; //差异总数量 = 总数量-完成总数

        $response['data'] = $ret['data'];
        $response['data']['record_time'] = date('Y-m-d', strtotime($response['data']['record_time']));
        $response['notice_record_id'] = $request['notice_record_id'];

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    function do_add(array & $request, array & $response, array & $app) {
        $ret_distributor = load_model('base/CustomModel')->get_by_code($request['distributor_code']);
        $request['tel'] = !empty($ret_distributor['data']['mobile']) ? $ret_distributor['data']['mobile'] : $ret_distributor['data']['tel'];
        $request['address'] = $ret_distributor['data']['address'];
        $request['province'] = $ret_distributor['data']['province'];
        $request['city'] = $ret_distributor['data']['city'];
        $request['district'] = $ret_distributor['data']['district'];
        $request['name'] = $ret_distributor['data']['contact_person'];
        $request['order_time'] = date('Y-m-d H:i:s', time());
        $stock_adjus = get_array_vars($request, array('record_code', 'order_time', 'distributor_code', 'init_code', 'tel', 'address', 'province', 'city', 'district', 'name', 'record_time', 'store_code', 'rebate', 'remark', 'record_type_code'));
        $ret = load_model('wbm/NoticeRecordModel')->insert($stock_adjus);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未出库', 'action_name' => "创建", 'module' => "wbm_notice_record", 'pid' => $ret['data']);
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
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($request['id'], $request['store_code'], 'wbm_notice', $data);
        if ($ret['status'] < 1) {
            return $ret;
        }
        //增加明细
        $ret = load_model('wbm/NoticeRecordDetailModel')->add_detail_action($request['id'], $data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '增加明细', 'module' => "wbm_notice_record", 'pid' => $request['id']);
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
        if (isset($request['lof_no']) && $request['lof_no']!='') {
            $detail = array(
                array('price' => $request['price'], 'sku' => $request['sku'], 'num' => $request['num'], 'money' => $request['price'] * $request['rebate'] * $request['num'], 'lof_no'=>$request['lof_no']),
            );
        }else{
            $detail = array(
                array('price' => $request['price'], 'sku' => $request['sku'], 'num' => $request['num'], 'money' => $request['price'] * $request['rebate'] * $request['num']),
            );
        }
        $ret = load_model('wbm/NoticeRecordDetailModel')->edit_detail_action($request['pid'], $detail);
        $res = load_model('wbm/NoticeRecordModel')->get_by_id($request['pid']);
        $ret['res'] = $res['data'];
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/NoticeRecordModel')->delete($request['notice_record_id']);
        exit_json_response($ret);
    }

    //终止
    function do_stop(array &$request, array &$response, array &$app) {
        $ret = load_model('wbm/NoticeRecordModel')->update_stop('1', 'is_stop', $request['id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '终止', 'action_name' => '终止', 'module' => "wbm_notice_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //是否有未出库
    function out_relation(array &$request, array &$response, array &$app) {
        $ret = load_model('wbm/NoticeRecordModel')->out_relation($request['id']);
        exit_json_response($ret);
    }

    /**
     * 确认/取消确认
     */
    function do_sure(array &$request, array &$response, array &$app) {

        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('wbm/NoticeRecordModel')->update_sure($arr[$request['type']], 'is_sure', $request['id']);
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
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未出库', 'action_name' => $action_name, 'module' => "wbm_notice_record", 'pid' => $request['id']);
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
        $ret = load_model('wbm/NoticeRecordDetailModel')->delete($request['notice_record_detail_id']);
        //批次删除
        $ret1 = load_model('stm/GoodsInvLofRecordModel')->delete_pid($request['pid'], $request['sku'], 'wbm_notice');
        exit_json_response($ret);
    }

    //删除批次
    function do_delete_detail_lof(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/NoticeRecordDetailModel')->delete_lof($request['id']);
        exit_json_response($ret);
    }

    //批发通知单生成销货单
    function execute(array & $request, array & $response, array & $app) {
        //主单据信息
        $ret = load_model('wbm/NoticeRecordModel')->get_by_id($request['notice_record_id']);
        $response['data'] = $ret['data'];
    }

    //生产销货单
    function create_out_record(array &$request, array &$response, array &$app) {
        $out_notice_record = load_model('wbm/NoticeRecordModel')->get_by_id($request['notice_record_id']);
        $ret = load_model('wbm/NoticeRecordModel')->check_status($out_notice_record['data']);
        if ($ret['status'] == 1) {
            $ret = load_model('wbm/StoreOutRecordModel')->create_out_record($out_notice_record['data'], $request['create_type']);
            if ($ret['status'] == 1) {
                $sql = "update wbm_notice_record set is_execute = 1 where record_code='" . $out_notice_record['data']['record_code'] . "'";
                CTX()->db->query($sql);
                $log2 = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未出库', 'action_name' => '新增', 'module' => "store_out_record", 'pid' => $ret['data'], 'action_note' => "批发销货通知单{$out_notice_record['data']['record_code']}生成");
                $ret2 = load_model('pur/PurStmLogModel')->insert($log2);
            }
        }
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未完成', 'action_name' => '生成销货单', 'module' => "wbm_notice_record", 'pid' => $request['notice_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
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
        $ret = load_model('wbm/NoticeRecordModel')->edit_action($request['parameter'], array('notice_record_id' => $request['parameterUrl']['notice_record_id']));
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '修改', 'module' => "wbm_notice_record", 'pid' => $request['parameterUrl']['notice_record_id']);
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

    function custom(array & $request, array & $response, array & $app) {

    }

    function select_custom(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }

    function select_action(array &$request, array &$response, array &$app) {
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $request['custom_type'] = 'pt_fx';
        $result = load_model('base/CustomModel')->get_custom_select($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }

    /**
     * 修改扫描数量
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function update_scan_num(array & $request, array & $response, array & $app) {
        if ($request['is_lof'] == 1) {
            $ret = load_model('wbm/NoticeRecordDetailModel')->update_scan_num_lof($request);
        } else {
            $ret = load_model('wbm/NoticeRecordDetailModel')->update_scan_num($request['record_code'], $request['num'], $request['id']);
        }
        exit_json_response($ret);
    }

    /**
     * 扫描确认
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function scan_do_check(array &$request, array &$response, array &$app) {
        $ret = load_model('wbm/NoticeRecordModel')->scan_do_check($request);
        exit_json_response($ret);
    }

    /**
     * 是否唯品会通知单, 是则返回销货单号
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function weipinhui_notice_record(array &$request, array &$response, array &$app) {
        $ret = array('status' => -1);
        if (!empty($request['notice_record_no'])) {
            $where = array('notice_record_no' => $request['notice_record_no']);
            $ret = load_model('api/WeipinhuijitStoreOutRecordModel')->get_row($where);
            if ($ret['status'] == 1) {
                $ret['data'] = array('store_out_record_no' => $ret['data']['store_out_record_no']);
            }
        }
        exit_json_response($ret);
    }

    /**
     * 导出明细
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function export_csv_list(array &$request, array &$response, array &$app) {
        $record_code = isset($request['record_code']) ? trim($request['record_code']) : '';
        $notice_record_id = isset($request['notice_record_id']) ? trim($request['notice_record_id']) : '';
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status', 'clodop_print');
        $sys_ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $lof_status = isset($request['lof_status']) ? $request['lof_status'] : $sys_ret_arr['lof_status'];
        $notice_record_model = load_model('wbm/NoticeRecordModel');
        if ($notice_record_id == '') {
            $response = $notice_record_model->format_ret(-1, '', '单据编号不能为空');
        } else {
            //主单据信息
            $ret = $notice_record_model->get_by_id($notice_record_id);
            //仓库
            $store_arr = load_model('util/ViewUtilModel')->get_map_arr(json_decode(load_model('base/StoreModel')->get_view_select(), true), 'value');
            $store = isset($store_arr[$ret['data']['store_code']]) ? $store_arr[$ret['data']['store_code']]['text'] : '';
            //分销商
            $custom_arr = load_model('util/ViewUtilModel')->get_map_arr(json_decode(load_model('base/CustomModel')->get_purview_custom_select('pt_fx', 3), true), 'value');
            $custom = isset($custom_arr[$ret['data']['distributor_code']]) ? $custom_arr[$ret['data']['distributor_code']]['text'] : '';
            //业务类型
            $record_type_arr = load_model('util/ViewUtilModel')->get_map_arr(json_decode(bui_get_select('record_type', 0, array('record_type_property' => 2)), true), 'value');
            $record_type = isset($record_type_arr[$ret['data']['record_type_code']]) ? $record_type_arr[$ret['data']['record_type_code']]['text'] : '';
            $goods_spec1_rename = isset($sys_ret_arr['goods_spec1']) ? $sys_ret_arr['goods_spec1'] : '';
            $goods_spec2_rename = isset($sys_ret_arr['goods_spec2']) ? $sys_ret_arr['goods_spec2'] : '';
            if ($ret['data']['country'] == '0' && $ret['data']['province'] == '0' && $ret['data']['city'] == '0' && $ret['data']['district'] == '0') {
                $area = load_model('base/CustomModel')->get_by_code($ret['data']['distributor_code']);
                $ret['data']['province'] = $area['data']['province'];
                $ret['data']['city'] = $area['data']['city'];
                $ret['data']['district'] = $area['data']['district'];
            }
            if ($ret['data']['country'] == '0' && $ret['data']['province'] != '0') {
                $ret['data']['country'] = '1';
            }
            $country = oms_tb_val('base_area', 'name', array('id' => $ret['data']['country']));
            $province = oms_tb_val('base_area', 'name', array('id' => $ret['data']['province']));
            $city = oms_tb_val('base_area', 'name', array('id' => $ret['data']['city']));
            $district = oms_tb_val('base_area', 'name', array('id' => $ret['data']['district']));
            $addr = $country . $province . $city . $district;
            $record_time = date('Y-m-d', strtotime($ret['data']['record_time']));

            //获取明细的商品信息
            $detail_model = load_model('wbm/NoticeRecordDetailModel');
            $str = "单据编号,原单号,下单时间,分销商,仓库,折扣,业务日期,业务类型,总数量,总金额,完成数量,备注,联系人,地址,详细地址,联系电话,商品名称,商品编码,{$goods_spec1_rename},{$goods_spec2_rename},商品条形码,";
            if ($lof_status == 1) {
                $str .= "批次号,生产日期,";
                $detail_ret = $detail_model->get_by_page_lof($request);
            }
            $str .= "批发价,商品折扣,批发单价,数量,";
            if ($lof_status != 1) {
                $str .= '总金额,';
                $detail_ret = $detail_model->get_by_page($request);
            }
            $str .= "商品完成数量,商品差异数量\n";
            $remark = str_replace(',', '，', $ret['data']['remark']);
            foreach ($detail_ret['data']['data'] as $value) {
                $str .= "\t" . $record_code . "\t,\t" . $ret['data']['init_code'] . "\t," . $ret['data']['order_time'] . "," . $custom . "," . $store . "," . $ret['data']['rebate'] . "," . $record_time . "," . $record_type . "," . $ret['data']['num'] . "," . $ret['data']['money'] . "," . $ret['data']['finish_num'] . "," . $remark . "," . $ret['data']['name'] . "," . $addr . "," . $ret['data']['address'] . ",\t" . $ret['data']['tel'] . "\t," . $value['goods_name'] . ",\t" . $value['goods_code'] . "\t,\t" . $value['spec1_name'] . "\t,\t" . $value['spec2_name'] . "\t,\t" . $value['barcode'] . "\t,";
                if ($lof_status == 1) {
                    $str .= "\t" . $value['lof_no'] . "\t," . $value['production_date'] . ",";
                }
                $str .= "\t" . $value['price'] . "\t," . $value['rebate'] . ",\t" . $value['price1'] . "\t," . $value['num'] . ",";
                if ($lof_status != 1) {
                    $str .= $value['money'] . ",";
                }
                $str .= $value['finish_num'] . "," . $value['diff_num'] . "\n";
            }
            $str = iconv('utf-8', 'gbk', $str);
            $filename = date('Ymd') . '.csv'; //设置文件名
            $this->export_csv($filename, $str); //导出
        }
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

    function check_is_print_record(array &$request, array &$response, array &$app) {
        $type = isset($request['type']) ? $request['type'] : 1;
        if($type == 2){
            //服装特性判断
            $ret = load_model('wbm/NoticeRecordModel')->check_is_print_record_clothing($request['notice_record_id']);
            if($ret['status'] == -2){
                $response = $ret; return;
            }
        }
        $response = load_model('wbm/NoticeRecordModel')->check_is_print_record($request['notice_record_id']);
        if($type == 2){
            $response['data']['out_record_code'] = $ret['data']['out_record_code'];
        }

    }

}
