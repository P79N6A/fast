<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms/SellReturnModel');
require_model('oms/SellReturnOptModel');

class sell_return {

    //售后服务单
    function after_service_list(array &$request, array &$response, array &$app) {
        $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status', 'fast_return'));
        $response['unique_status'] = $unique_arr['unique_status'];
        $response['fast_return'] = $unique_arr['fast_return'];
    }

    //售后服务单详情
    function after_service_detail(array &$request, array &$response, array &$app) {
        $sell_return_code = $request['sell_return_code'];
        $response = load_model('oms/SellReturnModel')->get_return_by_return_code($sell_return_code);
        $response['mx'] = load_model('oms/SellReturnModel')->get_detail_list_by_return_code($sell_return_code);

        $sell_return_scanning = load_model('sys/SysParamsModel')->get_val_by_code(array('sell_return_scanning', 'fast_return'));
        $response['sell_return_scanning'] = $sell_return_scanning['sell_return_scanning'];
        $response['fast_return'] = $sell_return_scanning['fast_return'];
        $sql = "select deal_code_list,store_code,shipping_status from oms_sell_record where sell_record_code = :sell_record_code";
        $sell_row = ctx()->db->get_row($sql, array(':sell_record_code' => $response['sell_record_code']));
        if (empty($response['change_store_code'])) {
            $sell_store_code = $sell_row['store_code'];
        } else {
            $sell_store_code = $response['change_store_code'];
        }
        $response['deal_code'] = $sell_row['deal_code_list'];
        $response['forjs_data'] = array('return_store_code' => $response['store_code'], 'sell_store_code' => $sell_store_code, 'sell_record_shipping_status' => $sell_row['shipping_status']);
        $is_WMS = load_model('sys/ShopStoreModel')->is_wms_store($response['store_code']);
        $response['is_wms'] = !empty($is_WMS) ? 1 : -1;
    }

    function sell_return_scanning_view(array &$request, array &$response, array &$app) {
        $sell_return_code = $request['sell_return_code'];
        $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status'));
        $response['unique_status'] = $unique_arr['unique_status'];
        $ret = load_model('oms/ReturnPackageModel')->is_exists($sell_return_code, 'sell_return_code');
        if ($request['sell_return_code'] != '') {
            $response['mx'] = load_model('oms/ReturnPackageModel')->get_detail_list_by_return_code($sell_return_code);
            $response['return_package_code'] = $response['mx'][0]['return_package_code'];
        } else {
            //不关联退单的
            $response['mx'] = load_model('oms/ReturnPackageModel')->get_detail_list_by_return_package_code($request['return_package_code']);
            $response['return_package_code'] = $request['return_package_code'];
        }
        $detail_sku = array();

        $total_sl = 0;
        $recv_num = 0;
        foreach ($response['mx'] as &$value) {
            $total_sl += $value['note_num'];
            $recv_num += $value['recv_num'];
            $detail_sku[$value['barcode']] = array('num' => $value['recv_num'], 'note_num' => $value['note_num']);
        }
        $response['detail_key'] = $detail_sku;
        $response['total_sl'] = $total_sl;
        $response['total_scan_sl'] = $recv_num;
        $response['total_no_scan_sl'] = $total_sl - $recv_num;
    }

    function scan_barcode(array &$request, array &$response, array &$app) {
        $record_code = $request['record_code'];
        $response = load_model('oms/SellReturnModel')->scan_barcode($record_code, $request['scan_barcode'], $request['type'], $request['return_package_code']);
        return $response;
    }

    /**
     * @todo 不关联订单或退单的条码扫描方法
     */
    function scan_barcode_no_return_code(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('scan_barcode', 'return_package_code'));
        $response = load_model('oms/SellReturnModel')->scan_barcode_no_return_code($params);
        return $response;
    }

    //读取取详情各部分
    function component(array &$request, array &$response, array &$app) {
        $types = $request['components'];
        if ($request['type'] != 'all') {
            $types = array($request['type']);
        }
        if (in_array("return_money", $types) && !in_array("baseinfo", $types)) {
            $types[] = 'baseinfo';
        }

        $mdlSellReturn = new SellReturnModel();

        //读取订单
        $response = $mdlSellReturn->component($request['sell_return_code'], $types);
        if ($response['status'] == -1) {
            return $response;
        }

        $result = array();
        $arr = array();
        foreach ($types as $type) {
            ob_start();
            $t_opt = $request['opt'];
            $type_tpl = $type;
            if ($response['data']['sell_after_is_compensate'] == 1) {
                if ($type == 'baseinfo' || $type == 'return_money') {
                    $type_tpl = $type . '_refund';
                }
            }
            //echo 'oms/sell_return/'.$t_opt.'_'.$type;die;
            $path = get_tpl_path('oms/sell_return/' . $t_opt . '_' . $type_tpl);
            include $path;
            $ret = ob_get_contents();
            ob_end_clean();
            $arr[$type] = $ret;
        }
        return $response = $arr;
    }

    //读取详情按钮权限
    function btn_check(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $req = $request['fields'];
        $mdl_return = new SellReturnModel();
        $mdl_return_opt = new SellReturnOptModel();

        $record = $mdl_return->get_return_by_return_code($request['sell_return_code']);
        $detail = $mdl_return->get_detail_list_by_return_code($request['sell_return_code']);
        $sys_user = $mdl_return_opt->sys_user();
        /*
          echo '<hr/>record<xmp>'.var_export($record,true).'</xmp>';
          echo '<hr/>detail<xmp>'.var_export($detail,true).'</xmp>';
          die;
         */
        $response = array();
        foreach ($req as $key => $status) {
            $func = $key . '_check';
            //echo '<hr/>$func<xmp>'.var_export($func,true).'</xmp>';
            if ($key == 'opt_create_change_order') {
                $change_detail = $mdl_return->get_detail_list_by_return_code($request['sell_return_code']);

                $s = $mdl_return_opt->$func($record, $detail, $change_detail);
            } else {
                $s = $mdl_return_opt->$func($record, $detail, $sys_user);
            }
            //echo '<hr/>$s<xmp>'.var_export($s,true).'</xmp>';
            $response[$key]['status'] = $s['status'] == 1 ? 1 : 0;
            $response[$key]['message'] = $s['message'];
        }
    }

    //保存编辑
    function do_edit(array &$request, array &$response, array &$app) {
        $result = array();
        if (isset($request['sell_return_code'])) {
            $sell_return_code = $request['sell_return_code'];
            if (isset($request['express_code']) && isset($request['express_code'])) {
                $data = get_array_vars($request, array('express_code', 'express_no'));
                unset($request['express_code']);
                unset($request['express_no']);
            }
            unset($request['sell_return_code']);
            $result = load_model("oms/SellReturnModel")->update($request, array('sell_return_code' => $sell_return_code));
            if (!empty($data)) {
                $sell_return_info = load_model("oms/SellReturnModel")->get_by_pk($sell_return_code);
                if ($sell_return_info['status'] != '1') {
                    return $response = $sell_return_info;
                }
                $result = load_model("oms/SellRecordModel")->update($data, array('sell_record_code' => $sell_return_info['data']['sell_record_code']));
                if ($result['status'] != '1') {
                    return $response = $result;
                }
            }
        } else {
            foreach ($request as $key => $value) {
                if (is_array($value)) {
                    $result = load_model("oms/SellReturnModel")->update_detail($value, array('sell_return_detail_id' => $key));
                }
            }
        }

        $response = $result;
    }

    //删除退单明细
    function delete_detail_by_id(array &$request, array &$response, array &$app) {
        $param = get_array_vars($request, ['sell_return_code','sell_return_detail_id']);
        $response = load_model("oms/SellReturnModel")->delete_detail($param);
    }

    //删除换货明细
    function delete_change_detail_by_id(array &$request, array &$response, array &$app) {
        $response = load_model("oms/SellReturnModel")->delete_change_detail(array('sell_change_detail_id' => $request['sell_change_detail_id']));
    }

    //详情操作
    function opt(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdlSellRecord = new SellReturnOptModel();
        $func = $request['type'];
        $response = $mdlSellRecord->$func($request['sell_return_code']);
        //echo '<hr/>func<xmp>'.var_export($func,true).'</xmp>';
        //echo '<hr/>response<xmp>'.var_export($response,true).'</xmp>';
    }

    //订单完成操作
    function opt_finish(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdlSellRecord = new SellReturnOptModel();
        $response = $mdlSellRecord->opt_finish($request['sell_return_code']);
    }

    function opt_return_shipping(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdlSellRecord = new SellReturnOptModel();
//    	$type = '';
//    	if ($request['type']){
//    		$type = $request['type'];
//    	}
        if (empty($request['sell_return_code']) && isset($request['return_package_code']) && !empty($request['return_package_code'])) {
            $response = $mdlSellRecord->opt_return_shipping_package($request['return_package_code']);
        } else {
            $response = $mdlSellRecord->opt_return_shipping($request['sell_return_code'], $request);
        }
    }

    function package_list(array &$request, array &$response, array &$app) {

    }

    //退货包裹单明细
    function package_detail(array &$request, array &$response, array &$app) {
        //主单据信息
        $ret = load_model('oms/ReturnPackageModel')->get_return_package_by_code($request['return_package_code']);
        $response['selection']['express_code'] = bui_get_select('express', 0, array('status' => 1));
        $response['selection']['store'] = bui_get_select('store');
        $response['selection']['shop'] = bui_get_select('shop');
        $sell_return_scanning = load_model('sys/SysParamsModel')->get_val_by_code(array('sell_return_scanning'));
        $response['sell_return_scanning'] = $sell_return_scanning['sell_return_scanning'];
        //取得国家数据
        $response['area']['country'] = load_model('base/TaobaoAreaModel')->get_area('0');
        $response['area']['province'] = array();
        $response['area']['province'] = load_model('base/TaobaoAreaModel')->get_area($ret['return_country']);
        $response['area']['city'] = load_model('base/TaobaoAreaModel')->get_area($ret['return_province']);
        $response['area']['district'] = load_model('base/TaobaoAreaModel')->get_area($ret['return_city']);
        $response['area']['street'] = load_model('base/TaobaoAreaModel')->get_area($ret['return_district']);
        $country = oms_tb_val('base_area', 'name', array('id' => $ret['return_country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $ret['return_province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $ret['return_city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $ret['return_district']));
//        $street = oms_tb_val('base_area', 'name', array('id'=>$request['receiver_street']));
        $ret['addr'] = $country . $province . $city . $district;

        $response['data'] = $ret;
    }

    function package_edit(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/ReturnPackageModel')->edit_action($request['parameter'], array('return_package_code' => $request['parameterUrl']['return_package_code']));

        exit_json_response($ret);
    }

    function package_add(array &$request, array &$response, array &$app) {
        $response['return_package_code'] = 'BG' . load_model('util/CreateCode')->get_code('oms_return_package');
        //取得省数据
        $response['area']['country'] = load_model('base/TaobaoAreaModel')->get_area('0');
    }

    function add_package_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $request['receiver_country'] = isset($request['country']) ? $request['country'] : '';
        $request['receiver_province'] = isset($request['province']) ? $request['province'] : '';
        $request['receiver_city'] = isset($request['city']) ? $request['city'] : '';
        $request['receiver_district'] = isset($request['district']) ? $request['district'] : '';
        $request['receiver_street'] = isset($request['street']) ? $request['street'] : '';
        $response = load_model('oms/ReturnPackageModel')->add($request);
    }

    function do_add_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('oms/ReturnPackageModel')->add_detail_goods($request['id'], $request['data'], $request['store_code']);
        exit_json_response($ret);
    }

    function do_delete_package_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('oms/ReturnPackageModel')->do_package_detail_delete($request);
        exit_json_response($ret);
    }

    function save_component(array &$request, array &$response, array &$app) {
        $fn = 'save_component_' . $request['type'];
        $response = load_model('oms/SellReturnOptModel')->$fn($request['sell_return_code'], $request);
    }

    function add_return_goods(array &$request, array &$response, array &$app) {
        $response = load_model('oms/SellReturnModel')->add_return_goods($request);
    }

    function add_change_goods_view(array &$request, array &$response, array &$app) {
        $return_detail_ret = load_model('oms/SellReturnModel')->get_detail_list_by_return_code($request['sell_return_code'], 'sell_return_detail_id');
        $detail = array();
        foreach ($return_detail_ret as $key => $detail_row) {
            //商品编码：A，规格：B，数量：C，价格：D
            $de_val = "商品编码：" . $detail_row['goods_code'] . "；规格：" . $detail_row['spec1_name'] . "，" . $detail_row['spec2_name'] . "；数量：" . $detail_row['note_num'] . "；价格：" . $detail_row['avg_money'];
            //$detail[] = array($key,$de_val);
            //$detail[$key] = $de_val;
            $detail[] = array('text' => $de_val, 'value' => $key);
        }
        $response['return_goods'] = $detail;
    }

    function select_change_goods(array &$request, array &$response, array &$app) {
        $result = load_model('oms/SellReturnModel')->get_return_by_return_code($request['sell_return_code']);
        if (!empty($result['change_store_code'])) {
            $request['store_code'] = $result['change_store_code'];
        } else {
            $result2 = load_model("oms/SellRecordModel")->get_record_by_code($result['sell_record_code']);
            $request['store_code'] = $result2['store_code'];
        }
        $app['fmt'] = 'json';
        $request['page_size'] = 200;
        $request['page'] = 1;
        $result = load_model('prm/InvModel')->get_sku_inv($request);
//print_r($result);die;
        $response['rows'] = $result['data']['data'];
        //$response = $result['data']['data'];
    }

    function do_add_change_goods(array &$request, array &$response, array &$app) {
        $return_detail_row = load_model('oms/SellReturnModel')->get_detail_by_detail_id($request['return_goods']);
        $change_avg_money = $return_detail_row['avg_money'];
        $fx_amount = $return_detail_row['fx_amount'];
        if (!empty($request['bc_je'])) {
            $change_avg_money = $return_detail_row['avg_money'] + $request['bc_je'];
        }
        $request['data'][] = array(
            'avg_money' => $change_avg_money,
            'sku' => $request['sku'],
            'num' => $request['num'],
            'fx_amount' => $fx_amount,
        );
        $response = load_model('oms/SellReturnModel')->add_change_goods($request);
    }

    function add_change_goods(array &$request, array &$response, array &$app) {
        $response = load_model('oms/SellReturnModel')->add_change_goods($request);
    }

    function add_change_goods_by_return_goods(array &$request, array &$response, array &$app) {
        $response = load_model('oms/SellReturnOptModel')->add_change_goods_by_return_goods($request['sell_return_code']);
    }

    function save_component_baseinfo(array &$request, array &$response, array &$app) {
        $response = load_model('oms/SellReturnOptModel')->add_change_goods_by_return_goods($request['sell_return_code']);
    }

    function td_tran(array &$request, array &$response, array &$app) {

        $response = load_model('oms/TranslateRefundModel')->translate_refund_api($request['api_order_id']);
    }

    //换货单商品改款
    function opt_change_detail(array &$request, array &$response, array &$app) {
        //print_r($request);exit;
        $app['fmt'] = 'json';
        //删除当前明细
        $response1 = load_model("oms/SellReturnModel")->delete_change_detail(array('sell_change_detail_id' => $request['sell_change_detail_id']));
        //添加明细
        $detail = array();

        $detail['sell_return_code'] = $request['sell_return_code'];
        $detail['data'][0] = load_model('prm/GoodsModel')->get_sku_list($request['sku']);
        $detail['data'][0]['num'] = $request['num'];
        $detail['deal_code'] = $request['deal_code'];
        $detail['data'][0]['avg_money'] = $request['avg_money'];
        if (isset($request['is_gift']) && $request['is_gift'] == '1') {
            $detail['data'][0]['is_gift'] = $request['is_gift'];
        }
        //print_r($request);
        //print_r($detail);exit;
        $response = load_model('oms/SellReturnModel')->add_change_goods($detail);
    }

    //退单打标
    function label(array &$request, array &$response, array &$app) {
        if (isset($request['sell_return_code_list'])) {
            $request['sell_return_code'] = json_encode(explode(',', $request['sell_return_code_list']));
        }
    }

    function opt_label(array &$request, array &$response, array &$app) {
        $ret = array();
        $sell_return_arr = is_array($request['sell_return_code']) ? $request['sell_return_code'] : array($request['sell_return_code']);
        $msg = '';
        foreach ($sell_return_arr as $code) {
            $ret_sub = load_model("oms/SellReturnOptModel")->opt_label($code, $request['label_code'], $request);
            $msg .= $code . ': ' . ($ret_sub['status'] == 1 ? '成功' : $ret_sub['message']) . "<br />";
        }
        $ret = array('status' => $ret_sub['status'], 'message' => $msg);
        $response = $ret;
    }

    /**
     *
     * 方法名       opt_return_detail
     *
     * 功能描述     更改退单商品
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-09
     * @param       mixed &$request
     * @param       mixed &$response
     * @param      mixed $app
     */
    public function opt_return_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
            's' => array('sku', 'sell_return_detail_id', 'sell_return_code', 'deal_code'),
            'i' => array('num', 'avg_money', 'is_gift')
        );
        $req_arr = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($request, $key_required, $req_arr, TRUE);
        if (TRUE == $ret_required['status']) {
            //删除当前退单明细
            $srm = new SellReturnModel();
            $filter = array('sell_return_detail_id' => $req_arr['sell_return_detail_id']);
            $srmd = $srm->delete_detail($filter);
            //添加客户选择的退单明细

            $detail = array();
            $detail['sell_return_code'] = $req_arr['sell_return_code'];
            $detail['deal_code'] = $req_arr['deal_code'];
            $detail['data'] = array(
                array('sku' => $req_arr['sku'], 'num' => $req_arr['num'])
            );
            $response = $srm->add_return_goods($detail);
            //$response = array('status'=> '1', 'message' => lang('error_params'), 'data' => $req_arr);
        } else {
            $response = array('status' => '-1', 'message' => lang('error_params'));
        }
    }

    /**
     *
     * 方法名       auto_confirm_return_money
     *
     * 功能描述     Job执行：已'确认收货'的退款退货单，3天后，系统将自动确认退款
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-24
     * @param       mixed &$request
     * @param       mixed &$response
     * @param       mixed $app
     */
    public function auto_confirm_return_money(array &$request, array &$response, array &$app) {
        $job_obj = new SellReturnOptModel();

        $job_obj->auto_confirm_return_money();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    /**
     *
     * 方法名       auto_checked_and_return_money
     * 功能描述     Job执行：天猫退单交易审核通过，将系统售后服务单自动确认；天猫退单交易退款成功，将系统售后服务单自动财务退款
     * @param       mixed &$request
     * @param       mixed &$response
     * @param       mixed $app
     */
    public function auto_checked_and_return_money(array &$request, array &$response, array &$app) {
        $job_obj = new SellReturnOptModel();
        ;
        $job_obj->auto_checked_and_return_money();
        $app['fmt'] = "json";
        $response['status'] = 1;
    }

    //校验退单商品是否在订单中存在
    public function check_return_goods(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/SellReturnModel')->check_return_goods($request['sell_return_code']);
        exit_json_response($ret);
    }

    //校验退单是否生成换货单
    public function check_change_record(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/SellReturnModel')->check_change_record($request['sell_return_code']);
        exit_json_response($ret);
    }

    //
    function communicate_log(array &$request, array &$response, array &$app) {

    }

    function opt_communicate_log(array &$request, array &$response, array &$app) {
        $response = load_model('oms/SellReturnModel')->communicate_log($request);
    }

//    function insert_unique_barcode(array & $request, array & $response, array & $app) {
//        $data = array();
//        $data['sell_record_code'] = $request['record_code'];
//        $data['unique_code'] = $request['unique_code'];
//        $data['barcode_type'] = 'unique_code';
//        $response = load_model('oms/UniqueCodeScanTemporaryLogModel')->insert($data);
//    }
    //查看关联退单号是否存在
    function is_return_code(array & $request, array & $response, array & $app) {
        $param = get_array_vars($request, array('sell_return_code'));
        $data = load_model('oms/ReturnPackageModel')->is_return_code($param);
        exit_json_response($data);
    }

    function edit_store_code(array &$request, array &$response, array &$app) {

    }

    //批量修改退货仓库
    function edit_store_code_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $err = '';
        foreach ($request['sell_return_code_list'] as $key => $id) {
            $sell_return_code = $id;
            $sql = "select * from oms_sell_return where sell_return_code = :sell_return_code";
            $record = ctx()->db->get_row($sql, array(':sell_return_code' => $id));
            $ret = load_model('oms/SellReturnOptModel')->save_component_baseinfo($sell_return_code, $request);
            if ($ret['status'] < 1) {
                $err .= $id . ': ' . $ret['message'] . "<br>";
                continue;
            }
            $old_store = get_store_name_by_code($record['store_code']);
            $new_store = get_store_name_by_code($request['store_code']);
            load_model('oms/SellReturnModel')->add_action($record, "批量修改退货仓库", $old_store . "修改成" . $new_store);
        }
        if (!empty($err)) {
            $response = array('status' => -1, 'message' => $err);
        } else {
            $response = array('status' => 1, 'message' => '更新成功');
        }
        exit_json_response($response);
    }

    //批量快速入库
    function opt_confirm_return_shipping(array &$request, array &$response, array &$app) {
        $sell_return_code_list = explode(',', $request["sell_return_code_list"]);
        $success = 0;
        $faile = 0;
        foreach ($sell_return_code_list as $key => $sell_return_code) {
            $ret = load_model('oms/SellReturnOptModel')->opt_confirm_return_shipping($sell_return_code);
            if ($ret['status'] != 1) {
                $faile++;
                $error_msg[] = array($sell_return_code => $ret['message']);
            } else {
                $success++;
            }
        }
        if ($faile != 0) {
            //$fail_top = array('退货单号', '错误信息');
            // $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
            //  $err="'成功'.$success.'条，失败'.$faile.'条，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $err = '成功' . $success . '条，失败' . $faile . '条';
            $response = array('status' => -1, 'message' => $err);
        } else {
            $response = array('status' => 1, 'message' => '更新成功');
        }
        exit_json_response($response);
    }

    //修改退货包裹单实际退货数
    function do_edit_detail(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('return_package_code', 'num', 'sku', 'barcode', 'sell_return_code'));
        $res = load_model('oms/ReturnPackageModel')->do_edit_package_detail($params);
        exit_json_response($res);
    }

    //通过package_code获取子订单详情
    function get_detail_list_by_sell_record_code(array & $request, array & $response, array & $app) {
        $data = load_model("oms/ReturnPackageModel")->get_detail_list_by_return_package_code($request['return_package_code']);
        $response = array('rows' => $data);
    }
    //修改收获服务单调整金额
    function update_abjust_money(array & $request, array & $response, array & $app) {
        $res = load_model('oms/SellReturnModel')->update_abjust_money($request);
        exit_json_response($res);
    }
    //验证实际入库数
     function check_num(array & $request, array & $response, array & $app) {
        $ret = load_model('oms/ReturnPackageModel')->get_by_sku_id($request['return_package_code'],$request['sku'],'num');
        if($ret['num'] > 0) {
            $ret = array('status' => -1,'data' => $ret['num'],'message' => '这条明细已有实际退货数，确认删除吗？');
        } else {
            $ret = array('status' => 1,'data' => $ret['num'],'message' => '');
        }
        exit_json_response($ret);
    }
}
