<?php

require_lib('util/web_util', true);
require_lib('business_util', true);
require_lib('util/common_util', true);

class store_out_record {

    function do_list(array & $request, array & $response, array & $app) {
        $custom = load_model('base/CustomModel');
        $fenxiao = $custom->get_purview_custom_select('pt_fx');
        $response['fenxiao'] = $custom->array_order($fenxiao, 'custom_name');

    }

    function diff_detail(array & $request, array & $response, array & $app) {

    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('pur/ReturnRecordModel')->get_by_id($request['_id']);
        }

        $ret['data']['record_code'] = load_model('wbm/StoreOutRecordModel')->create_fast_bill_sn();
        $response['data'] = $ret['data'];
        
        $custom = load_model('base/CustomModel');
        $fenxiao = $custom->get_purview_custom_select('pt_fx',2);
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
        $ret = load_model('wbm/StoreOutRecordModel')->get_by_id($request['store_out_record_id']);
        $ret = $ret['data'];
        $response['selection']['express_code'] = bui_get_select('express', 0, array('status' => 1));
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
//        $response['selection']['custom'] = bui_get_select('custom', 0, array('custom_type'=>'pt_fx'));
        $response['selection']['custom'] = load_model('base/CustomModel')->get_purview_custom_select('pt_fx',3);
        $response['add_service_status'] = load_model('common/ServiceModel')->check_is_auth_by_value('pfx_goods_print');
        if($response['add_service_status'] == 0){
            $response['is_JIT'] = 0;
        }else{
            $JIT = load_model('api/WeipinhuijitStoreOutRecordModel')->get_by_out_record_no($ret['record_code']);
            $response['is_JIT'] = empty($JIT['data'])?0:1;            
        }
        $response['selection']['record_type'] = bui_get_select('record_type', 0, array('record_type_property' => 2));

        $ok = get_theme_url('images/ok.png');
        $no = get_theme_url('images/no.gif');
        if ($ret['is_sure'] == '1') {
            $is_check_src = $ok;
        } else {
            $is_check_src = $no;
        }
        $ret['is_check_src'] = "<img src='{$is_check_src}'>";
        if ($ret['is_store_out'] == '1') {
            $is_store_out_src = $ok;
        } else {
            $is_store_out_src = $no;
        }
        $ret['is_store_out_src'] = "<img src='{$is_store_out_src}'>";

        //spec1别名
        //spec1别名
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status', 'pur_express_print','clodop_print','barcode_template','wbm_barcode_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $response['pur_express_print'] = isset($ret_arr['pur_express_print']) ? $ret_arr['pur_express_print'] : '';
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
        $response['barcode_template'] =isset($ret_arr['barcode_template'])?$ret_arr['barcode_template']:0 ;
        $response['wbm_barcode_print'] =isset($ret_arr['wbm_barcode_print'])?$ret_arr['wbm_barcode_print']:0 ;
        //取得国家数据
        $response['area']['country'] = load_model('base/TaobaoAreaModel')->get_area('0');
        if($ret['country'] == '0' && $ret['province'] != '0'){
            $ret['country'] = '1';
        }
        //$response['area']['province'] = array();
        if($ret['country'] != '0'){
            $response['area']['province'] = load_model('base/TaobaoAreaModel')->get_area($ret['country']);
        }
        if($ret['province'] != '0'){
            $response['area']['city'] = load_model('base/TaobaoAreaModel')->get_area($ret['province']);
        }
        if($ret['city'] != '0'){
            $response['area']['district'] = load_model('base/TaobaoAreaModel')->get_area($ret['city']);
        }
        if($ret['district'] != '0'){
            $response['area']['street'] = load_model('base/TaobaoAreaModel')->get_area($ret['district']);
        }
                
        $country = oms_tb_val('base_area', 'name', array('id' => $ret['country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $ret['province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $ret['city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $ret['district']));
        //$street = oms_tb_val('base_area', 'name', array('id'=>$request['receiver_street']));
        $ret['addr'] = $country . $province . $city . $district;
        $ret['money'] = $ret['money'] + $ret['express_money'];
        $ret_store = load_model('base/StoreModel')->get_by_code($ret['store_code']);
        $ret['allow_negative_inv'] = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;

        $scan = load_model('sys/RecordScanBoxModel')->get_scan_mode($ret['record_code'], 'wbm_store_out');
        $response['scan_type'] = $scan['data'];
//                var_dump($response['scan_type']);exit;
        $response['data'] = $ret;
        //是否是经销采购单生成
        $response['custom_code'] = isset($ret['jx_code']) && !empty($ret['jx_code']) ? $ret['relation_code'] : '';

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    function do_add(array & $request, array & $response, array & $app) {
        $request['order_time'] = date('Y-m-d H:i:s', time());

        //添加分销商信息到批发销货单表
        $ret_distributor = load_model('base/CustomModel')->get_by_code($request['distributor_code']);
        $request['tel'] = !empty($ret_distributor['data']['mobile']) ? $ret_distributor['data']['mobile'] : $ret_distributor['data']['tel'];
        $request['address'] = $ret_distributor['data']['address'];
        $request['province'] = $ret_distributor['data']['province'];
        $request['city'] = $ret_distributor['data']['city'];
        $request['district'] = $ret_distributor['data']['district'];
        $request['name'] = $ret_distributor['data']['contact_person'];
        $stock_adjus = get_array_vars($request, array('record_code', 'order_time', 'distributor_code', 'tel', 'address','province','city','district', 'name', 'relation_code', 'record_time', 'store_code', 'rebate', 'remark','record_type_code'));

        $ret = load_model('wbm/StoreOutRecordModel')->insert($stock_adjus);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未出库', 'action_name' => "新增", 'module' => "store_out_record", 'pid' => $ret['data'],'action_note' => '手工生成');
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
        $ret = load_model('wbm/StoreOutRecordModel')->add_detail_goods($request['id'], $request['data'], $request['store_code']);
        exit_json_response($ret);
//     	$ret =load_model('wbm/StoreOutRecordModel')->get_row(array('store_out_record_id' => $request['id']));
//     	$pftzd_code = $ret['data']['relation_code'];
//     	$sku_arr = $sku_num = array();
//     	$err_num = 0;
//         $data = $request['data'];
//         foreach ($data as $d){
//         	if ($d['num'] > 0){
//         		$sku_num[$d['barcode']] = $d['num'];
//         		$sku_arr[] = $d['barcode'];
//         	}
//         }
//         $check_ret = load_model('wbm/StoreOutRecordModel')->check_pftzd($pftzd_code,$sku_arr,$sku_num,$err_num);
//         if (!empty($check_ret)){
//         	$str = '';
//         	foreach ($check_ret as $err){
//         		foreach ($err as $k => $v){
//         			$str .= "条码".$k.$v."<br>";
//         		}
//         	}
//         	exit_json_response(array('status' => -1,'data'=> '','message' => $str));
//         }
//         //批次档案维护
//         $ret = load_model('prm/GoodsLofModel')->add_detail_action($request['id'],$data);
//         //单据批次添加
//        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($request['id'],$request['store_code'],'wbm_store_out',$data);
//        //增加明细
//        $ret = load_model('wbm/StoreOutRecordDetailModel')->add_detail_action($request['id'],$data);
//        if($ret['status'] == '1'){
//        	//日志
//        	$log = array('user_id'=>CTX()->get_session('user_id'),'user_code'=>CTX()->get_session('user_code'),'ip'=>'','add_time'=>date('Y-m-d H:i:s'),'sure_status'=>'未确认','finish_status'=>'未出库','action_name'=>'增加明细','module'=>"store_out_record",'pid'=>$request['id']);
//        	$ret1 = load_model('pur/PurStmLogModel')->insert($log);
//        }
    }

    /**
     * 通知单生成销货单
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array json
     */
    function do_add_main_detail(array & $request, array & $response, array & $app) {
        //print_r($request);exit;
        //获取采购计划订单主数据
        $ret = load_model('wbm/NoticeRecordModel')->get_by_id($request['notice_record_id']);
        //print_r($request);exit;
        $store_code = isset($ret['data']['store_code']) ? $ret['data']['store_code'] : '';
        $pici_arr = array();
        if (isset($ret['status']) && $ret['status'] == 1) {

            $bill_sn = load_model('wbm/StoreOutRecordModel')->create_fast_bill_sn();

            //添加采购订单主表
            $ret['data']['relation_code'] = $ret['data']['record_code'];
            $ret['data']['record_code'] = $bill_sn;
            $ret['data']['record_time'] = date('Y-m-d H:i:s');
            //$ret['data']['record_type_code'] = '000';
            unset($ret['data']['is_sure'], $ret['data']['num'], $ret['data']['money']);
            $ret = load_model('wbm/StoreOutRecordModel')->insert($ret['data']);
            if (isset($ret['status']) && $ret['status'] == 1) {
                $store_out_record_id = $ret['data'];
            }
            if (isset($request['selections'])) {
                $ret_detail = load_model('wbm/NoticeRecordDetailModel')->get_select_data($request['notice_record_id'], $request['selections']);
                $request['selections'] = $ret_detail['data'];
                foreach ($request['selections'] as $key => $value) {
                    if ($value['diff_num'] == 0) {
                        unset($request['selections'][$key]);
                        continue;
                    }

                    $request['selections'][$key]['pid'] = $store_out_record_id;
                    $request['selections'][$key]['relation_code'] = $request['selections'][$key]['record_code'];
                    $request['selections'][$key]['record_code'] = $bill_sn;
                    $request['selections'][$key]['trade_price'] = $request['selections'][$key]['price'];
                    //unset($request['selections'][$key]['lastchanged'],$request['selections'][$key]['id'],$request['selections'][$key]['notice_record_detail_id'],$request['selections'][$key]['is_finish'],$request['selections'][$key]['is_sure']);
                    switch ($request['exe_type']) {
                        case "1":
                            //$request['selections'][$key]['num'] = $request['selections'][$key]['diff_num'];//入库数
                            $request['selections'][$key]['num'] = 0; //入库数
                            /*
                              $pici = load_model('stm/GoodsInvLofRecordModel')->get_all(array(
                              'pid' => $request['notice_record_id'],
                              'order_type' => 'wbm_notice',
                              'sku' => $request['selections'][$key]['sku']
                              ));

                              $request['selections'][$key]['pice'] = $pici['data'];
                             */
                            $request['selections'][$key]['num_flag'] = '1'; //入库为0标志
                            $request['selections'][$key]['enotice_num'] = $request['selections'][$key]['diff_num']; //通知数
                            $request['selections'][$key]['money'] = $request['selections'][$key]['num'] * $request['selections'][$key]['price'] * $request['selections'][$key]['rebate']; //金额
                            break;
                        case "2":
                            $request['selections'][$key]['num'] = 0; //入库数
                            $pici = load_model('stm/GoodsInvLofRecordModel')->get_row(array(
                                'pid' => $request['notice_record_id'],
                                'order_type' => 'wbm_notice',
                                'sku' => $request['selections'][$key]['sku']
                            ));
                            $request['selections'][$key]['lof_no'] = $pici['data']['lof_no'];
                            $request['selections'][$key]['production_date'] = $pici['data']['production_date'];
                            //$request['selections'][$key]['money'] = 0 ;
                            $request['selections'][$key]['num_flag'] = '1'; //入库为0标志
                            $request['selections'][$key]['enotice_num'] = $request['selections'][$key]['diff_num']; //通知数
                            break;
                    }
                }
                if ($request['exe_type'] == '1') {
                    $pici_arr = $request['selections'];
                    //转化成批次数据
                    /*
                      foreach($request['selections'] as $key1=>$value1){
                      $num = $value1['num'];
                      $p = $value1['pice'];
                      unset($value1['pice']);
                      foreach($p as $v){

                      if($num >= intval($v['init_num'])){
                      $value1['num'] = $v['init_num'];
                      $value1['diff_num'] = $v['init_num'];
                      }else{
                      $value1['num'] = $num;
                      $value1['diff_num'] = $num;
                      }
                      $value1['lof_no'] = $v['lof_no'];
                      $value1['production_date'] = $v['production_date'];

                      $pici_arr[] = $value1;
                      $num = $num - $v['init_num'];
                      if($num <= 0){
                      break;
                      }
                      }

                      }
                     */
                }
                if ($request['exe_type'] == '2') {
                    $pici_arr = $request['selections'];
                }
            }

            //print_r($request);
            //print_r($pici_arr);
            //exit;
            if ($store_out_record_id && isset($request['exe_type']) && ($request['exe_type'] == '1' || $request['exe_type'] == '2')) {
                //对应批次数据
                //$pici = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($request['notice_record_id'], 'wbm_notice');
                //批次档案维护
                //$ret = load_model('prm/GoodsLofModel')->add_detail_action($store_out_record_id,$request['selections']);
                //单据批次添加
                //$ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($store_out_record_id,$store_code,'wbm_store_out',$pici_arr);
                //print_r($request['selections']);exit;
                //明细添加
                $ret = load_model('wbm/StoreOutRecordDetailModel')->add_detail_action($store_out_record_id, $pici_arr);
            }
            //回写执行状态
            $ret1 = load_model('wbm/NoticeRecordModel')->update_check('1', 'is_execute', $request['notice_record_id']);
        }
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '通知单生成销货单', 'module' => "store_out_record", 'pid' => $store_out_record_id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        if (isset($ret)) {
            $bill_sn = isset($bill_sn) ? $bill_sn : '';
            $ret['store_out_record_id'] = isset($store_out_record_id) ? $store_out_record_id : '';
            $ret['msg'] = "已生成销货单" . $bill_sn . "，是否打开销货单详情？";
        }
        exit_json_response($ret);
    }

    /**
     * 修改单据明细
     */
    function do_edit_detail(array & $request, array & $response, array & $app) {
        $detail = get_array_vars($request, array('record_code', 'rebate', 'sku', 'num', 'price','barcode'));
        $ret = load_model('wbm/StoreOutRecordDetailModel')->edit_detail_action($request['pid'], array($detail));
        if($ret['status']==1){
            $res = load_model('wbm/StoreOutRecordModel')->get_by_id($request['pid']);
            $ret['res'] = $res['data'];
        }
        exit_json_response($ret);
    }

    /**
     * 修改批次明细入库数
     */
    function do_edit_detail_lof(array & $request, array & $response, array & $app) {
        $detail = get_array_vars($request, array('sku', 'price', 'rebate', 'barcode'));
        $detail_lof = get_array_vars($request, array('num', 'record_code', 'lof_no', 'production_date'));
        $ret = load_model('wbm/StoreOutRecordDetailModel')->edit_detail_action_lof($request['pid'], $detail, $detail_lof);
        $res = load_model('wbm/StoreOutRecordModel')->get_by_id($request['pid']);
        $ret['res'] = $res['data'];
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/StoreOutRecordModel')->delete($request['store_out_record_id']);
        exit_json_response($ret);
    }
    
    //检查差异数
    function check_diff_num (array & $request, array & $response, array & $app) {
        $response = load_model('wbm/StoreOutRecordModel')->check_diff_num($request['record_code']);
        exit_json_response($response);
    }

    //出库
    function do_shift_out(array & $request, array & $response, array & $app) {
        $power = load_model('sys/PrivilegeModel')->check_priv('wbm/store_out_record/do_checkin');
        if ($power) {
            $data = load_model('wbm/StoreOutRecordModel')->get_row_by_id($request['record_code']);
            if(!$data['status']){
                exit_json_response($data);
            }
            $is_scan_tag = isset($request['is_scan_tag']) ? 0 : 1;
            if ($request['record_code']) {
                $record = load_model('wbm/StoreOutRecordModel')->get_row(array('record_code' => $request['record_code']));
                $id = $record['data']['store_out_record_id'];
            }
            $ret = load_model('wbm/StoreOutRecordModel')->do_sure_and_shift_out($request['record_code'], $is_scan_tag);
        } else {
            $ret = array(
                'status' => -401,
                'data' => '',
                'message' => '没有验收权限'
            );
        }
        exit_json_response($ret);
    }

    /**
     * 按业务日期验收
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_shift_out_by_record_date(array & $request, array & $response, array & $app) {
        $data = load_model('wbm/StoreOutRecordModel')->get_row_by_id($request['record_code']);
        if(!$data['status']){
            exit_json_response($data);
        }
        $ret = load_model('wbm/StoreOutRecordModel')->do_sure_and_shift_out($request['record_code'], 1, 0, '', '', 0, 1);
        exit_json_response($ret);
    }


    /**
     * 唯品会生成的销货单验证
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_shift_out_weipinhui_check(array & $request, array & $response, array & $app) {
        if(!isset($request['type']) or $request['type'] != 1){
            $data = load_model('wbm/StoreOutRecordModel')->get_row_by_id($request['record_code']);
            if(!$data['status']){
                exit_json_response($data);
            }
        }
        $ret = load_model('wbm/StoreOutRecordModel')->do_shift_out_weipinhui_check($request['record_code']);
        exit_json_response($ret);
    }


    function err_handle_type(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/StoreOutRecordModel')->err_handle_type($request['handle_type'], $request['record_code']);
        exit_json_response($ret);
    }

    /**
     * 确认/取消确认
     */
    function do_sure(array &$request, array &$response, array &$app) {

        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('wbm/StoreOutRecordModel')->update_sure($arr[$request['type']], 'is_sure', $request['id']);
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
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未出库', 'action_name' => $action_name, 'module' => "store_out_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //删除时检查是否有装箱单明细（未开启批次）如果有一起删除
    function check_box_record(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/StoreOutRecordModel')->check_box_record($request['pid'], $request['sku']);
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
        $ret = load_model('wbm/StoreOutRecordDetailModel')->delete($request['store_out_record_detail_id']);
        //批次删除
        $ret1 = load_model('stm/GoodsInvLofRecordModel')->delete_pid($request['pid'], $request['sku'], 'wbm_store_out');
        //如果有装箱单明细 装箱单明细一起删除
        if ($request['is_box_task'] == 1) {
            $ret2 = load_model('b2b/BoxRecordDatailModel')->delete_box_record($request['pid'], $request['sku'], 'wbm_store_out');
        }
        //添加日志
        if ($ret['status'] == '1') {
            //$record_detail=load_model('wbm/StoreOutRecordDetailModel')->get_row(array('store_out_record_detail_id' => $request['store_out_record_detail_id']));
            $barcode=  oms_tb_val('goods_sku', 'barcode', array('sku'=>$request['sku']));
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '删除商品', 'action_note' => "删除商品条码：{$barcode}", 'module' => "store_out_record", 'pid' => $request['pid']);
            load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //开启批次时检查是否有装箱单明细
    function check_box_record_lof(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/StoreOutRecordModel')->check_box_record_lof($request['id']);
        exit_json_response($ret);
    }

    //删除批次
    function do_delete_detail_lof(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/StoreOutRecordDetailModel')->delete_lof($request['id'], $request['is_box_task']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '删除商品', 'module' => "store_out_record", 'pid' => $request['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
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
     * @param array $request
     * @param array $response
     * @param array $app
     * @return string json
     */
    function do_edit(array &$request, array &$response, array &$app) {
        require_model('wbm/StoreOutRecordModel');
        $obj = new StoreOutRecordModel();
        $ret = $obj->edit_action($request['parameter'], array('store_out_record_id' => $request['parameterUrl']['store_out_record_id']));
        $data = $obj->get_by_field('store_out_record_id', $request['parameterUrl']['store_out_record_id'], 'is_store_out');
        if ($ret['status'] == '1') {
            if ($data['data']["is_store_out"] == 1) {
                $finish_status = '已出库';
            } else {
                $finish_status = '未出库';
            }
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => $finish_status, 'action_name' => '修改', 'module' => "store_out_record", 'pid' => $request['parameterUrl']['store_out_record_id']);
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

    /**
     * 导出csv
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $ret = load_model('wbm/StoreOutRecordModel')->get_by_id($request['id']);
        $main_result = $ret['data'];

        $filter['record_code'] = $main_result['record_code'];
        $filter['page'] = 1;
        $filter['page_size'] = 5000;
        $filter['code_name'] = $request['goods_code'];
        $lof_no = '';
        if ($request['is_lof'] == 1) {
            $res = load_model('wbm/StoreOutRecordDetailModel')->get_by_page_lof($filter);
            $lof_no = ',批次号';
        } else {
            $res = load_model('wbm/StoreOutRecordDetailModel')->get_by_page($filter);
        }


        $detail_result = $res['data']['data'];
        $str = "单据编号,原单号,通知单号,分销商,仓库,下单日期,业务日期,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,批发价,单价,实际出库数,通知数,差异数,商品总金额,备注{$lof_no}\n";
        $str = iconv('utf-8', 'gbk', $str);
        $main_result['distributor_name'] = iconv('utf-8', 'gbk', $main_result['distributor_name']);
        $main_result['store_name'] = iconv('utf-8', 'gbk', $main_result['store_name']);
        $main_result['remark'] = str_replace(',','，',$main_result['remark']);
        $main_result['remark'] = iconv('utf-8', 'gbk', $main_result['remark']);
        foreach ($detail_result as $value) {
            $lof = '';
            //判断是否开启批次，开启就显示批次号
            if ($request['is_lof'] == 1) {
                $lof = "," . $value['lof_no'];
            }
            $value['goods_name'] = mb_convert_encoding(str_replace("\xC2\xA0", ' ', $value['goods_name']),'GBK','UTF-8');//中英文混合并且带空格的
            $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
            $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
            $value['barcode'] = iconv('utf-8', 'gbk', $value['barcode']);
            $value['goods_code'] = iconv('utf-8', 'gbk', $value['goods_code']);
            if ($request['is_lof'] == 1) {
                $value['enotice_num'] = $value['init_num'];
            }
            $str .= $main_result['record_code'] . "\t," . $main_result['init_code'] . "," . $main_result['relation_code'] . "," . $main_result['distributor_name'] . "," . $main_result['store_name'] .
                    "," . $main_result['order_time'] . "\t," . $main_result['record_time'] . "\t," . $value['goods_name'] . "," . $value['goods_code'] . "\t," . $value['spec1_code'] . "\t," . $value['spec1_name'] .
                    "," . $value['spec2_code'] . "\t," . $value['spec2_name'] . "\t," . $value['barcode'] . "\t," . $value['price'] . "," . $value['price1'] .
                    "," . $value['num'] . "," . $value['enotice_num'] . "," . $value['num_differ'] . "," . $value['money'] . "," . $main_result['remark'] . $lof . "\n"; //用引文逗号分开
        }
        $filename = date('Ymd') . '.csv'; //设置文件名
        header_download_csv($filename, $str); //导出
        echo $str;
        die;
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
        //$response = load_model('prm/GoodsImportModel')->import_base_spec1($file);

        $ret = load_model('wbm/StoreOutRecordModel')->imoprt_detail($request['id'], $file, $request['is_lof']);
        $response = $ret;
    }

    public function print_express(array & $request, array & $response, array & $app) {
        /** 抽取模板 */
        // 通过配送方式获取快递信息，其中包括模板id的信息
        $express_info = CTX()->db->get_row(
                'SELECT * FROM `base_express` WHERE `express_code` = :express_code ', array('express_code' => $request['express_code'])
        );
        // 查看模板配置，如果不是普通打印提醒用户
        if ($express_info['print_type'] != 0) {

        }
        // 获取快递模板
        $tpl = load_model('sys/PrintTemplatesModel')
                ->get_templates_by_id($express_info['pt_id']);
        if ($tpl['status'] > 0) {
            $response['status'] = '1';
            $response['data'] = $express_info;
            $response['tpl']['pt'] = $tpl['data'];
            $response['tpl']['pt']['printer'] = $tpl['data']['printer'];
        } else {
            $response['status'] = '-1';
            $response['message'] = $tpl['message'];
        }

        if (!isset($request['printer']) || empty($request['printer'])) {
            $request['printer'] = isset($response['tpl']['printer']) ? $response['tpl']['printer'] : '';
        }

        $response['print_one'] = 0;
    }

    public function get_print_data(array & $request, array & $response, array & $app) {
        $response = load_model("oms/StoreOutRecordModel")->get_print_data($request);
    }

    /*     * *
     * 获取销货单实际出库数
     */

    public function get_num_by_record_code(array & $request, array & $response, array & $app) {
        $ret = load_model("wbm/StoreOutRecordDetailModel")->get_num_by_record($request['record_code']);
        exit_json_response($ret);
    }

    /*     * *
     * 获取销货单明细实际出库数
     */

    public function get_num_by_detail_id(array & $request, array & $response, array & $app) {
        $ret = load_model("wbm/StoreOutRecordDetailModel")->get_by_field('store_out_record_detail_id', $request['store_out_record_detail_id'], 'num');
        exit_json_response($ret);
    }

    /**
     * 获取快递单模板
     * */
    function print_express_view(array & $request, array & $response, array & $app) {

        $request['page'] = isset($request['page']) ? $request['page'] : 1;
        $express_code = $request['express_code'];
        $template_type_key = 'pt';
        $ret_tpl = load_model("base/ShippingModel")->get_shipping_tpl($express_code);
        if ($ret_tpl['status'] > 0) {
            $response['status'] = '1';
            $response['data'] = $express_code;
            $response['tpl'] = $ret_tpl['data'];
            $response['tpl']['printer'] = $ret_tpl['data'][$template_type_key]['printer'];
        } else {
            $response['status'] = '-1';
            $response['message'] = $ret_tpl['message'];
        }
        if (!isset($request['printer']) || empty($request['printer'])) {
            $request['printer'] = isset($response['tpl']['printer']) ? $response['tpl']['printer'] : '';
        }
        $response['print_one'] = 0;
    }

    /**
     * 获取快递单数据
     */
    function get_print_express_data(array & $request, array & $response, array & $app) {
        $response = load_model("wbm/StoreOutRecordModel")->get_print_express_data($request);
    }

    /**
     * 检查是否开启快递普通模板
     */
    public function check_template(array & $request, array & $response, array & $app) {
        $ret = load_model('wbm/StoreOutRecordModel')->check_template($request);
        exit_json_response($ret);
    }
    
    public function choose_clodop_printer(array & $request, array & $response, array & $app) {
          
    }
    function get_record_goods_ids(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $response = load_model('wbm/StoreOutRecordModel')->get_record_goods_ids($request['record_code']);
    }
    
    public function check_is_print (array & $request, array & $response, array & $app) {
        $type = isset($request['print_type']) ? $request['print_type'] : 1;
        if($type == 2){
            //服装特性判断
            $ret = load_model('wbm/StoreOutRecordModel')->check_is_print_record_clothing($request['store_out_record_id']);
            if($ret['status'] == -2){
                $response = $ret; return;
            }
        }
        $response = load_model('wbm/StoreOutRecordModel')->check_is_print($request['store_out_record_id'],$request['type']);
    }
}
