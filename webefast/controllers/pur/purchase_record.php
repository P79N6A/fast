<?php

require_lib('util/web_util', true);
require_lib('business_util', true);

class purchase_record {

    function do_list(array & $request, array & $response, array & $app) {
        $response['user_id'] = CTX()->get_session('user_id');
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('pur/PurchaseRecordModel')->get_by_id($request['_id']);
        }
        //供应商
        $response['supplier'] = load_model('base/SupplierModel')->get_select(2);
        //调整仓库
        $response['store'] = load_model('base/StoreModel')->get_select(2);
        $ret['data']['record_code'] = load_model('pur/PurchaseRecordModel')->create_fast_bill_sn();
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
        $ret = load_model('pur/PurchaseRecordModel')->get_by_id($request['purchaser_record_id']);
        if($ret['data']['is_check_and_accept'] == 1 && $ret['data']['is_notify_payment'] == 1) { 
            //已验收、采购订单通知付款并且已付款，不允许修改金额
            $order_data = load_model('pur/OrderRecordModel')->is_exists($ret['data']['relation_code']);
            $planned_data = load_model('pur/PlannedRecordModel')->get_by_code($order_data['data']['relation_code']);
            $ret['data']['is_payment'] = $planned_data['data']['is_payment'];
        }
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $response['selection']['supplier'] = load_model('base/SupplierModel')->get_view_select();

        $response['selection']['record_type'] = bui_get_select('record_type', 0, array('record_type_property' => 0));


        $ok = get_theme_url('images/ok.png');
        $no = get_theme_url('images/no.gif');
        if ($ret['data']['is_check'] == '1') {
            $is_check_src = $ok;
        } else {
            $is_check_src = $no;
        }
        $ret['data']['is_check_src'] = "<img src='{$is_check_src}'>";
        if ($ret['data']['is_check_and_accept'] == '1') {
            $is_check_and_accept_src = $ok;
        } else {
            $is_check_and_accept_src = $no;
        }
        $ret['data']['is_check_and_accept_src'] = "<img src='{$is_check_and_accept_src}'>";
        $response['data'] = $ret['data'];
        $is_price = load_model('sys/PrivilegeModel')->check_priv('pur/purchase_record/do_edit_detail_price');              
        $response['is_price'] = $is_price == '' ? 0 : 1;
        $is_money = load_model('sys/PrivilegeModel')->check_priv('pur/purchase_record/do_edit_detail_money');
        $response['is_money'] = $is_money == '' ? 0 : 1;
        //spec1别名
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status','pur_barcode_print','clodop_print','barcode_template');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $response['pur_barcode_print'] = isset($ret_arr['pur_barcode_print']) ? $ret_arr['pur_barcode_print'] : '';
        $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $response['price_status'] = $price_status['status'];
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
	$response['barcode_template'] =isset($ret_arr['barcode_template'])?$ret_arr['barcode_template']:'' ;

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    function do_add(array & $request, array & $response, array & $app) {
        $request['order_time'] = date('Y-m-d H:i:s', time());
        $stock_adjus = get_array_vars($request, array('record_code', 'order_time', 'relation_code', 'record_time', 'record_type_code', 'supplier_code', 'store_code', 'rebate', 'remark'));
        $ret = load_model('pur/PurchaseRecordModel')->insert($stock_adjus);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未验收', 'action_name' => "新增", 'module' => "purchase_record", 'pid' => $ret['data'],'action_note' => '手工生成');
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 审核
     */
    function do_check(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('pur/PurchaseRecordModel')->update_check_new($arr[$request['type']], 'is_check', $request['id']);
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
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未验收', 'action_name' => $action_name, 'module' => "purchase_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 采购入库单增加明细
     */
    function do_add_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/PurchaseRecordModel')->add_detail_goods($request['id'], $request['data'], $request['store_code']);
        exit_json_response($ret);
    }

    /**
     * 修改明细入库数、进货价 - 未开批次
     */
    function do_edit_detail(array & $request, array & $response, array & $app) {
        $sql = "select money,price from  pur_purchaser_record_detail  where pid='{$request['pid']}' and sku = '{$request['sku']}'";
        $detail_arr = CTX()->db->get_row($sql);
        $detail = get_array_vars($request, array('record_code', 'sku', 'num', 'price', 'rebate','money','old_num','old_price'));
        $res = load_model('pur/PurchaseRecordModel')->get_by_id($request['pid']);
        if($res['data']['is_check']==1 && $request['num'] != $request['old_num']){
            $ret = array('status'=>-1,'message'=>'单据已经验收不能修改');
            exit_json_response($ret);
        }
        if($res['data']['is_payment'] != 0) {
            $ret = array('status'=>-1,'message'=>'单据已经付款不能修改');
            exit_json_response($ret);
        }

        $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $ret = load_model('pur/PurchaseRecordDetailModel')->edit_detail_action($request['pid'], $detail);
        $ret['res'] = $res['data'];
        if($ret['status'] == 1){
            if($res['data']['is_check'] == 1){
                $finish_status = '验收';
            }else{
                $finish_status = '未验收';
            }
            if($request['num'] != $request['old_num']){
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'),'sure_status' => '', 'finish_status' => $finish_status, 'action_name' => '修改实际入库数', 'module' => "purchase_record", 'pid' => $request['pid'],'action_note' => '商品条形码'.$request['barcode'].'数量由'.$request['old_num'].'修改为'.$request['num']);
            }
            elseif($request['price'] != $detail_arr['price'] &&  $price_status['status']==1){
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'),'sure_status' => '', 'finish_status' => $finish_status, 'action_name' => '修改进货价', 'module' => "purchase_record", 'pid' => $request['pid'],'action_note' =>  '商品条形码'.$request['barcode'].'价格由'.$detail_arr['price'].'修改为'.sprintf("%01.2f",$request['price']));
            }elseif($detail_arr['money'] != $request['money'] && $price_status['status']==1){
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'),'sure_status' => '', 'finish_status' => $finish_status, 'action_name' => '修改金额', 'module' => "purchase_record", 'pid' => $request['pid'],'action_note' => '商品条形码'.$request['barcode'].'金额由'.$detail_arr['money'].'修改为'.$request['money']);
            }
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        
        exit_json_response($ret);
    }
    function do_edit_detail_price(array & $request, array & $response, array & $app){
        
    }
    /**
     * 修改明细入库数 - 开启批次
     */
    function do_edit_detail_lof(array & $request, array & $response, array & $app) {
        $detail = get_array_vars($request, array('sku', 'price', 'rebate'));
        $detail_lof = get_array_vars($request, array('num', 'record_code', 'lof_no', 'production_date'));
        $ret = load_model('pur/PurchaseRecordDetailModel')->edit_detail_action_lof($request['pid'], $detail, $detail_lof);
        exit_json_response($ret);
    }

    //删除入库单
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/PurchaseRecordModel')->delete($request['purchaser_record_id']);
        exit_json_response($ret);
    }

    /**
     * 采购入库单入库
     */
    function do_checkin(array & $request, array & $response, array & $app) {
        $power = load_model('sys/PrivilegeModel')->check_priv('pur/purchase_record/do_checkin');
        if ($power) {
            $check = isset($request['is_scan_tag']) ? 0 : 1;
            //兼容导入批次
            $action_name = '验收';
    // 	    if($check == 1){
    // 	    	$ret_lof = load_model('stm/GoodsInvLofRecordModel')->get_all(array('order_code' => $request['record_code'], 'order_type' => 'purchase'));
    // 	    	if($ret_lof['status'] <> 'op_no_data'){
    // 	    		$check = 0;
    //                 }else{
    //                     $action_name = '扫描验收';
    //                 }
    // 	    }
            $ret = load_model('pur/PurchaseRecordModel')->checkin($request['record_code']);
            $res = load_model('pur/PurchaseRecordModel')->get_by_id($request['pid']);
            $ret['res'] = $res['data'];
            if ($ret['status'] == '1') {
                //采购单入库，减在途
                load_model('prm/InvOpRoadModel')->update_road_inv($request['record_code']);

                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '验收', 'action_name' => $action_name, 'module' => "purchase_record", 'pid' => $ret['data']);
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
    function check_diff_num (array & $request, array & $response, array & $app) {
        $response = load_model('pur/PurchaseRecordModel')->check_diff_num($request['record_code']);
        exit_json_response($response);
    }

    /**
     * 按业务日期验收
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_checkin_by_record_date(array & $request, array & $response, array & $app) {
        //兼容导入批次
        $ret = load_model('pur/PurchaseRecordModel')->checkin($request['record_code'], 1, '', '', 1);
        if ($ret['status'] == '1') {
            //$record = load_model('pur/PurchaseRecordModel')->get_by_id($request['pid']);
            //采购单入库，减在途
            load_model('prm/InvOpRoadModel')->update_road_inv($request['record_code']);
            //日志
            $action_name = '按业务日期验收';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '验收', 'action_name' => $action_name, 'module' => "purchase_record", 'pid' => $ret['data']);
            load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }


    /**
     * 删除单据明细
     */
    function do_delete_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/PurchaseRecordDetailModel')->delete($request['purchaser_record_detail_id']);
        //批次删除
        $ret1 = load_model('stm/GoodsInvLofRecordModel')->delete_pid($request['pid'], $request['sku'], 'purchase');
        exit_json_response($ret);
    }

    //批次明细删除
    function do_delete_detail_lof(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/PurchaseRecordDetailModel')->delete_lof($request['id']);
        exit_json_response($ret);
    }

    function update_check(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('stm/StockAdjustRecordModel')->update_check($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }

    //扫描单据添加数量
    function update_scan_num(array & $request, array & $response, array & $app) {
        if ($request['is_lof'] == 1) {
            $ret = load_model('pur/PurchaseRecordDetailModel')->update_scan_num_lof($request);
        } else {
            $ret = load_model('pur/PurchaseRecordDetailModel')->update_scan_num($request['record_code'], $request['num'], $request['id']);
        }

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
        $ret = load_model('pur/PurchaseRecordModel')->edit_action($request['parameter'], array('purchaser_record_id' => $request['parameterUrl']['purchaser_record_id']));
        if ($ret['status'] == '1') {
            $record=load_model('pur/PurchaseRecordModel')->get_row(array('purchaser_record_id'=>$request['parameterUrl']['purchaser_record_id']));
            $finish_status = ($record['data']['is_check_and_accept'] == 0) ? '未验收' : '已验收';
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => $finish_status, 'action_name' => '修改', 'module' => "purchase_record", 'pid' => $request['parameterUrl']['purchaser_record_id']);
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
        $ret = load_model('pur/PurchaseRecordModel')->imoprt_detail($request['id'], $file, $request['is_lof']);
        $response = $ret;
        $response['url'] = $_FILES['fileData']['name'];
    }

    //导入商品
    function importGoods(array & $request, array & $response, array & $app) {
        $response['id'] = $request['id'];
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
    }

    /**
     * 导出csv
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $ret = load_model('pur/PurchaseRecordModel')->get_by_id($request['id']);
        $main_result = $ret['data'];
//		print_r($main_result);exit;

        $filter['record_code'] = $request['record_code'];
        $filter['code_name'] = $request['code_name'];
        $filter['page'] = 1;
        $filter['page_size'] = 10000;
        $lof_no = '';
        if ($request['is_lof'] != 1) {
            $res = load_model('pur/PurchaseRecordDetailModel')->get_by_page($filter);
        } else {
            $res = load_model('pur/PurchaseRecordDetailModel')->get_by_page_lof($filter);
            $lof_no = "批次号";
        }
        $detail_result = $res['data']['data'];

        $str = "单据编号,原单号,采购通知单号,供货商,仓库,采购类型,下单日期,业务日期,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,进货价,进货单价,实际入库数,商品总金额,通知数,差异数," . $lof_no . "\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($detail_result as $value) {

            $supplier_name = iconv('utf-8', 'gbk', $main_result['supplier_name']);
            $store_name = iconv('utf-8', 'gbk', $main_result['store_name']);
            $record_type_name = iconv('utf-8', 'gbk', $main_result['record_type_name']);
            $value['goods_name'] = mb_convert_encoding(str_replace("\xC2\xA0", ' ', $value['goods_name']),'GBK','UTF-8');//中英文混合并且带空格的
            $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
            $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
            $value['barcode'] = iconv('utf-8', 'gbk', $value['barcode']);
            $value['goods_code'] = iconv('utf-8', 'gbk', $value['goods_code']);
            $lof = '';
            if ($request['is_lof'] == 1) {
                $lof = $value['lof_no'];
            }
            $str .= $value['record_code'] . "\t," . $main_result['init_code'] . "," . $main_result['relation_code'] . "," . $supplier_name . "," . $store_name . "," . $record_type_name .
                    "," . $main_result['order_time'] . "\t," . $main_result['record_time'] . "\t," . $value['goods_name'] . "," . $value['goods_code'] . "," . $value['spec1_code'] . "," . $value['spec1_name'] .
                    "," . $value['spec2_code'] . "," . $value['spec2_name'] . "\t," . $value['barcode'] . "\t," . $value['price'] . "," . $value['price1'] .
                    "," . $value['lof_num'] . "," . $value['money'] . "," . $value['notice_num'] . "," . $value['num_differ'] . "," . $lof . "\n"; //用引文逗号分开
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
    
    function do_add_print_log(array &$request, array &$response, array &$app) {
        //主单据信息
        $ret = load_model('pur/PurchaseRecordModel')->get_by_id($request['id']);
        $sure_status = $ret['data']['is_check'] == 1 ? '已确认' : '未确认';
        $finish_status = $ret['data']['is_check_and_accept'] == 1 ? '已验收' : '未验收';
        
        $action_name = $request['type'] == 'barcode' ? '打印条码' : '单据打印';
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => $finish_status, 'action_name' => $action_name, 'module' => "purchase_record", 'pid' => $request['id']);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        exit_json_response($ret1);
    }
    
    function check_is_print (array & $request, array & $response, array & $app) {
        $response = load_model('pur/PurchaseRecordModel')->check_is_print($request['purchaser_record_id']);
    }

}
