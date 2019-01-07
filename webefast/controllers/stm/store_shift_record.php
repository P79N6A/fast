<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);

class store_shift_record {

    function do_list(array & $request, array & $response, array & $app) {
        //出库状态
        $response['shift_out_status'] = load_model('stm/StoreShiftRecordModel')->shift_out_status;
        //入库状态
        $response['shift_in_status'] = load_model('stm/StoreShiftRecordModel')->shift_in_status;
        $response['user_id'] = CTX()->get_session('user_id');
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('stm/StockAdjustRecordModel')->get_by_id($request['_id']);
        }
        $ret['data']['record_code'] = load_model('stm/StoreShiftRecordModel')->create_fast_bill_sn();
        $response['data'] = $ret['data'];
    }

    //添加主单据
    function do_add(array & $request, array & $response, array & $app) {
        $user_id = CTX()->get_session('user_id');
        $user_name = oms_tb_val('sys_user', 'user_name', array('user_id' => $user_id));
        $request['is_add_person'] = $user_name;
        $ret = load_model('stm/StoreShiftRecordModel')->do_add_record($request);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "", 'finish_status' => '未确认', 'action_name' => "创建", 'module' => "store_shift_record", 'pid' => $ret['data']);
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
        $ret = load_model('stm/StoreShiftRecordModel')->edit_action($request['parameter'], array('shift_record_id' => $request['parameterUrl']['shift_record_id']));
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '修改', 'module' => "store_shift_record", 'pid' => $request['parameterUrl']['shift_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
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
        $ret = load_model('stm/StoreShiftRecordModel')->get_by_id($request['shift_record_id']);
//        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $response['is_entity'] = isset($request['is_entity']) && $request['is_entity'] == 1 ? 1 : 0;

        $purview_store = load_model('base/StoreModel')->get_purview_store(); //取出有权限的仓库
        if ($response['is_entity'] == 1) {
            $entity_store = load_model('base/StoreModel')->get_entity_store();
            $purview_store = array_merge($purview_store, $entity_store);
        }
        $response['is_same_outside_code'] = load_model('stm/StoreShiftRecordModel')->is_same_outside_code($ret['data']['shift_in_store_code'], $ret['data']['shift_out_store_code']);
        $store_arr = array_column($purview_store, 'store_name', 'store_code');
        $response['purview_store'] = array_column($purview_store, 'store_code');

        $is_shift_out_time = strtotime($ret['data']['is_shift_out_time']);
        $is_shift_in_time = strtotime($ret['data']['is_shift_in_time']);
        $ret['data']['is_shift_out_time'] = $is_shift_out_time !== false ? date('Y-m-d', $is_shift_out_time) : '';
        $ret['data']['is_shift_in_time'] = $is_shift_in_time !== false ? date('Y-m-d', $is_shift_in_time) : '';
        $response['data'] = $ret['data'];
        //权限控制
        $response['power']['confirm'] = load_model('sys/PrivilegeModel')->check_priv('stm/store_shift_record/confirm');
        $response['power']['output'] = load_model('sys/PrivilegeModel')->check_priv('stm/store_shift_record/output');
        $response['power']['scan_input'] = load_model('sys/PrivilegeModel')->check_priv('stm/store_shift_record/scan_input');
        $response['power']['force_input'] = load_model('sys/PrivilegeModel')->check_priv('stm/store_shift_record/force_input');

        $shift_in_store = load_model('base/StoreModel')->get_by_code($ret['data']['shift_in_store_code']);
        $response['data']['shift_in_store_name'] = $shift_in_store['data']['store_name'];

        $shift_out_store = load_model('base/StoreModel')->get_by_code($ret['data']['shift_out_store_code']);
        $response['data']['shift_out_store_name'] = $shift_out_store['data']['store_name'];

        $response['selection']['store'] = json_encode(bui_bulid_select($store_arr)); //详情页-基本信息仓库下拉列表数据
        //spec1别名
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status', 'clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;

        $is_wms_in = load_model('sys/ShopStoreModel')->is_wms_store($ret['data']['shift_in_store_code']);
        if ($is_wms_in !== FALSE) {
            $response['is_wms_in'] = 1;
        } else {
            $response['is_wms_in'] = 0;
        }
        $is_wms_out = load_model('sys/ShopStoreModel')->is_wms_store($ret['data']['shift_out_store_code']);
        if ($is_wms_out !== FALSE) {
            $response['is_wms_out'] = 1;
        } else {
            $response['is_wms_out'] = 0;
        }

        $store_arr = array('shift_out' => $ret['data']['shift_out_store_code'], 'shift_in' => $ret['data']['shift_in_store_code']);
        $store = load_model('wms/WmsBaseModel')->get_outside_code($store_arr, 'iwms');
        if (isset($store[$store_arr['shift_out']]) && isset($store[$store_arr['shift_in']]) && $store[$store_arr['shift_out']] == $store[$store_arr['shift_in']]) {
            $response['is_wms_out'] = 0;
            $response['is_wms_in'] = 0;
        }
        $store = load_model('wms/WmsBaseModel')->get_outside_code($store_arr, 'iwmscloud');
        if (isset($store[$store_arr['shift_out']]) && isset($store[$store_arr['shift_in']]) && $store[$store_arr['shift_out']] == $store[$store_arr['shift_in']]) {
            $response['is_wms_out'] = 0;
            $response['is_wms_in'] = 0;
        }

        $response['login_type'] = empty(CTX()->get_session('login_type')) ? 0 : CTX()->get_session('login_type');
        //进货价权限
        $user_id = CTX()->get_session('user_id');
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $user_id);
        if ($status['status'] != 1) {
            $response['data']['out_money'] = '****';
            $response['data']['in_money'] = '****';
        }

        $param_auth = load_model('sys/SysParamsModel')->get_val_by_code('size_layer');
        $response['priv_size_layer'] = $param_auth['size_layer'];
    }

    //扫描入库
    function shift_in(array & $request, array & $response, array & $app) {

        $ret = load_model('stm/StoreShiftRecordDetailModel')->view_scan($request['record_code']);
        $response = $ret['data'];
        $response['record_code'] = $request['record_code'];
        $response['pid'] = $request['pid'];
        $response['in_store'] = $request['in_store'];
        $response['dj_type'] = isset($request['dj_type']) ? $request['dj_type'] : '';
        //条形码列表
        $goods = load_model('stm/StoreShiftRecordDetailModel')->get_list($request['pid']);

        //  filter_fk_name($goods, array('goods_code|goods_code', 'spec1_code|spec1_code','spec2_code|spec2_code'));

        $response['scan_data'] = $goods;
        //print_r($response);exit;
    }

    //扫描出库
    function shift_out(array & $request, array & $response, array & $app) {

        $ret = load_model('stm/StoreShiftRecordDetailModel')->view_scan($request['record_code'], $request['dj_type']);
        $response = $ret['data'];
        $response['record_code'] = $request['record_code'];
        $response['pid'] = $request['pid'];
        $response['in_store'] = $request['in_store'];
        $response['dj_type'] = isset($request['dj_type']) ? $request['dj_type'] : '';
        //条形码列表
        $goods = load_model('stm/StoreShiftRecordDetailModel')->get_list($request['pid']);

        //  filter_fk_name($goods, array('goods_code|goods_code', 'spec1_code|spec1_code','spec2_code|spec2_code'));

        $response['scan_data'] = $goods;
    }

    function clean_scan(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StoreShiftRecordModel')->clean_scan($request['id'], $request['record_code'], $request['out_type']);
        exit_json_response($ret);
    }

    function shift_in_list(array & $request, array & $response, array & $app) {

        $goods = load_model('stm/StoreShiftRecordDetailModel')->get_list($request['pid']);

        filter_fk_name($goods, array('goods_code|goods_code', 'spec1_code|spec1_code', 'spec2_code|spec2_code'));

        $response['goods'] = $goods;
    }

    //入库
    function do_shift_in(array & $request, array & $response, array & $app) {
        //print_r($request);exit;
        $ret = load_model('stm/StoreShiftRecordModel')->shift_in($request['id']);

        exit_json_response($ret);
    }

    //强制入库
    function do_qz_shift_in(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StoreShiftRecordModel')->do_qz_shift_in($request['id']);



        exit_json_response($ret);
    }

    //扫描检查
    function scan_check(array & $request, array & $response, array & $app) {

        //print_r($goods);
        $ret = array(
            'status' => '-2',
            'data' => '',
            'message' => ''
        );
        $goods = load_model('prm/GoodsBarcodeModel')->get_by_field_table('goods_barcode', 'barcode', $request['barcord']);

        if ($goods['status'] == '1') {
            $sku = $goods['data']['sku'];
            $ret = load_model('stm/StoreShiftRecordDetailModel')->get_by_field('sku', $sku, 'pid', $request['pid']);
        }
        exit_json_response($ret);
    }

    function scan(array & $request, array & $response, array & $app) {
        //print_r($request);exit;
        //是否存在
        $goods = load_model('prm/GoodsBarcodeModel')->get_by_field_table('goods_barcode', 'barcode', $request['barcord']);
        if ($goods['status'] == '-1') {
            $goods = load_model('prm/GoodsBarcodeModel')->get_by_field_table('goods_barcode', 'gb_code', $request['barcord']);
        }
        //移仓单移入进行条码匹配
        if ($goods['status'] == '-1' && isset($request['dj_type']) && $request['dj_type'] === 'shift_in') {
            $goods = load_model('prm/GoodsBarcodeIdentifyRuleModel')->yanzheng($request['barcord'], 1);
        }
        //print_r($goods);
        if ($goods['status'] == '1') {
            $sku = $goods['data']['sku'];
            $goods_b = load_model('stm/StoreShiftRecordDetailModel')->get_by_field('sku', $sku, 'pid', $request['pid']);
            $record = load_model('stm/StoreShiftRecordModel')->get_by_id($goods_b['data']['pid']);

            if ($goods_b['status'] == '1' && $goods_b['data'] <> '') {
                //转化成批次数据
                $pici_arr = array();
                $p = load_model('stm/GoodsInvLofRecordModel')->detail_all($request['pid'], 'shift_out', $record['data']['shift_out_store_code'], $sku);
                // foreach($request['selections'] as $key1=>$value1){
                //$num = $value1['num'];
                $num = intval($goods_b['data']['in_num']) + 1;
                $all_num = $num;
                $in_money = $goods_b['data']['price'] * $goods_b['data']['rebate'] * $all_num;
                $value1 = array(
                    'pid' => $goods_b['data']['pid'],
                    'order_code' => $goods_b['data']['record_code'],
                    'goods_code' => $goods_b['data']['goods_code'],
                    'spec1_code' => $goods_b['data']['spec1_code'],
                    'spec2_code' => $goods_b['data']['spec2_code'],
                    'sku' => $goods_b['data']['sku'],
                    'store_code' => $record['data']['shift_in_store_code'],
                );


                foreach ($p['data'] as $v) {

                    if ($num >= intval($v['num'])) {
                        $value1['num'] = $v['num'];
                    } else {
                        $value1['num'] = $num;
                    }
                    $value1['lof_no'] = $v['lof_no'];
                    $value1['production_date'] = $v['production_date'];

                    $pici_arr[] = $value1;
                    $num = $num - $v['num'];
                    if ($num <= 0) {
                        break;
                    }
                }
                //print_r($pici_arr);
                //exit;
                //}

                /*
                  //此商品存在，updata
                  $pici = load_model('stm/GoodsInvLofRecordModel')->detail_list($request['pid'],'shift_in',$request['in_store'],$sku);

                  $num = intval($pici['data']['num'])+1;
                  //$goods['data']['num'] = $num;
                  $pici['data']['num'] = $num;
                  unset($pici['data']['store_code'],$pici['data']['pid']);
                  $data[0] = $pici['data'];
                 */
                //print_r($pici_arr);exit;
                //批次档案维护
                $ret = load_model('prm/GoodsLofModel')->add_detail_action($request['pid'], $pici_arr);
                //单据批次添加
                $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($request['pid'], $request['in_store'], 'shift_in', $pici_arr);
                if ($ret['status'] < 1) {
                    return $ret;
                }
                $ret = load_model('stm/StoreShiftRecordDetailModel')->update(array('in_num' => $all_num, 'in_money' => $in_money), array('shift_record_detail_id' => $goods_b['data']['shift_record_detail_id']));
                $barcode = load_model('goods/SkuCModel')->get_barcode($goods_b['data']['sku']);
                $gb_code = load_model('goods/SkuCModel')->get_gb_code($goods_b['data']['sku']);
                if ($ret['status'] == 1) {
                    $ret['data'] = array('sku' => $goods_b['data']['sku'], 'barcode' => $barcode, 'num' => $all_num, 'gb_code' => $gb_code);
                }
            } else {
                $ret['status'] = '-1';
                $ret['data'] = '';
                $ret['message'] = '此单据不存在此商品条形码';
                /*
                  //不存在添加明细
                  $goods['data']['num'] = '1';
                  $data[0] = $goods['data'];
                  //批次档案维护
                  //$ret = load_model('prm/GoodsLofModel')->add_detail_action($request['pid'],$data);
                  //单据批次添加
                  $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($request['pid'],$request['in_store'],'shift_in',$data);
                  //增加明细
                  $ret = load_model('stm/StoreShiftRecordDetailModel')->add_detail_action_scan($request['pid'],$data);
                 */
            }
        }
        exit_json_response($ret);
    }

//出库扫描
    function store_out_scan(array & $request, array & $response, array & $app) {
        $goods = load_model('prm/GoodsBarcodeModel')->get_by_field_table('goods_barcode', 'barcode', $request['barcord']);
        if ($goods['status'] == '-1') {
            $goods = load_model('prm/GoodsBarcodeModel')->get_by_field_table('goods_barcode', 'gb_code', $request['barcord']);
        }
        if ($goods['status'] == "1") {
            $sku = $goods['data']['sku'];
            $goods_b = load_model('stm/StoreShiftRecordDetailModel')->get_by_field('sku', $sku, 'pid', $request['pid']);
            $record = load_model('stm/StoreShiftRecordModel')->get_by_id($goods_b['data']['pid']);
            if ($goods_b['status'] == '1' && $goods_b['data'] <> '') {
                $num = intval($goods_b['data']['scan_num']) + 1;
                $all_num = $num;
                $ret = load_model('stm/StoreShiftRecordDetailModel')->update(array('scan_num' => $all_num), array('shift_record_detail_id' => $goods_b['data']['shift_record_detail_id']));
                $barcode = load_model('goods/SkuCModel')->get_barcode($goods_b['data']['sku']);
                $gb_code = load_model('goods/SkuCModel')->get_gb_code($goods_b['data']['sku']);
                if ($ret['status'] == 1) {
                    $ret['data'] = array('sku' => $goods_b['data']['sku'], 'barcode' => $barcode, 'num' => $all_num, 'gb_code' => $gb_code);
                }
            } else {
                $ret['status'] = '-1';
                $ret['data'] = '';
                $ret['message'] = '此单据不存在此商品条形码';
            }
            exit_json_response($ret);
        }
    }

    /**
     * 确认/取消确认
     */
    function do_sure(array &$request, array &$response, array &$app) {

        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('stm/StoreShiftRecordModel')->update_sure($arr[$request['type']], 'is_sure', $request['id']);
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
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未出库', 'action_name' => $action_name, 'module' => "store_shift_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 扫描确认
     */
    function do_scan_sure(array &$request, array &$response, array &$app) {
        $power = load_model('sys/PrivilegeModel')->check_priv('stm/store_shift_record/confirm');
        if ($power) {
            $ret = load_model('stm/StoreShiftRecordModel')->update_sure(1, 'is_sure', $request['shift_record_id']);
            if ($ret['status'] == '1') {
                //日志
                $action_name = '扫描确认';
                $sure_status = '扫描确认';
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未出库', 'action_name' => $action_name, 'module' => "store_shift_record", 'pid' => $request['shift_record_id']);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
        } else {
            $ret = array(
                'status' => -1,
                'data' => '',
                'message' => '没有确认权限'
            );
        }
        exit_json_response($ret);
    }

    //出库
    function do_shift_out(array & $request, array & $response, array & $app) {
        if($request['out_type']=='scan_out'){
            $ret = load_model('stm/StoreShiftRecordModel')->scan_shift_out($request['id']);
            if($ret['status']=='1'){
                $scan_data = load_model('stm/StoreShiftRecordDetailModel')->get_list($request['id']);
                $log_detail = '';
                foreach ($scan_data as $k => $val) {
                    if (!empty($val['scan_num'])) {
                        $log_detail .= '扫描条码:' . $val['barcode'] . ",扫描出库数量:" . $val['scan_num'] . ';';
                    }
                }
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '出库', 'action_name' => '扫描出库', 'module' => "store_shift_record", 'pid' => $request['id'], 'action_note' => $log_detail);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
        }else{
            $ret = load_model('stm/StoreShiftRecordModel')->shift_out($request['id']);
            if($ret['status']=='1'){
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '出库', 'action_name' => '强制出库', 'module' => "store_shift_record", 'pid' => $request['id']);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
        }

        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StoreShiftRecordModel')->delete($request['shift_record_id']);
        exit_json_response($ret);
    }

    function do_delete_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StoreShiftRecordDetailModel')->delete($request['detail_id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未确认', 'action_name' => '删除移仓单明细', 'module' => "store_shift_record", 'pid' => $request['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    function do_delete_detail_lof(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StoreShiftRecordDetailModel')->delete_lof($request['id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未确认', 'action_name' => '删除移仓单批次明细', 'module' => "store_shift_record", 'pid' => $request['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 移仓单增加明细
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
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($request['id'], $request['store_code'], 'shift_out', $data);
        if ($ret['status'] < 1) {
            return $ret;
        }
//增加明细
        $ret = load_model('stm/StoreShiftRecordDetailModel')->add_detail_action($request['id'], $data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '增加明细', 'module' => "store_shift_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 导入商品
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function importGoods(array & $request, array & $response, array & $app) {
        $response['id'] = $request['id'];
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        $response['lof_status'] = $ret_arr['lof_status'];
    }

    function import_goods(array & $request, array & $response, array & $app) {
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
        set_uplaod($request, $response, $app);
    }

    function do_import_goods(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $ret = load_model('stm/StoreShiftRecordModel')->import_detail($request['id'], $file, $request['is_lof']);
        $response = $ret;
    }

    /**
     * 移仓单详情导出csv
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $ret = load_model('stm/StoreShiftRecordModel')->get_by_id($request['id']);
        $main_result = $ret['data'];
        //获取移入和移出仓库名
        $shift_in_data = load_model('base/StoreModel')->get_by_code($main_result['shift_in_store_code']);
        $shift_out_data = load_model('base/StoreModel')->get_by_code($main_result['shift_out_store_code']);
        $main_result['shift_in_store_name'] = $shift_in_data['data']['store_name'];
        $main_result['shift_out_store_name'] = $shift_out_data['data']['store_name'];
        $filter['record_code'] = $main_result['record_code'];
        $filter['page'] = 1;
        $filter['page_size'] = 5000;
        $res = load_model('stm/StoreShiftRecordDetailModel')->get_by_page_lof($filter);
        $detail_result = $res['data']['data'];
        $str = "单据编号,原单号,业务日期(出库),业务日期(入库),移出仓库,移入仓库,出库时间,入库时间,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,系统sku码,移出数量,移入数量,移出金额,移入金额,备注,采购价\n";
        $str = iconv('utf-8', 'gbk', $str);
        $main_result['shift_in_store_name'] = iconv('utf-8', 'gbk', $main_result['shift_in_store_name']);
        $main_result['shift_out_store_name'] = iconv('utf-8', 'gbk', $main_result['shift_out_store_name']);
        $main_result['remark'] = iconv('utf-8', 'gbk', $main_result['remark']);
        foreach ($detail_result as $value) {
            $value['goods_name'] = mb_convert_encoding(str_replace("\xC2\xA0", ' ', $value['goods_name']), 'GBK', 'UTF-8'); //中英文混合并且带空格的
            $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
            $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
            $pos = strpos($value['num'], ',');
            $in_num = ($pos) ? substr($value['num'], $pos - 1, 1) : 0;
            $str .= $main_result['record_code'] . "\t," . $main_result['init_code'] . "\t," . $main_result['record_time'] . "\t," . $main_result['shift_in_time'] . "\t," . $main_result['shift_out_store_name'] . "\t," . $main_result['shift_in_store_name'] .
                    "\t," . $main_result['is_shift_out_time'] . "\t," . $main_result['is_shift_in_time'] . "\t," . $value['goods_name'] . "\t," . $value['goods_code'] . "\t," . $value['spec1_code'] . "\t," . $value['spec1_name'] .
                    "\t," . $value['spec2_code'] . "\t," . $value['spec2_name'] . "\t," . $value['barcode'] . "\t," . $value['sku'] . "\t," . $value['out_num'] . "\t," . $value['in_num'] . "\t," . $value['out_money'] . "\t," . $value['in_money'] . "\t," . $main_result['remark'] . "\t," . $value['price'] . "\n"; //用引文逗号分开
        }
        $filename = date('Ymd') . '.csv'; //设置文件名
        header_download_csv($filename, $str); //导出
        echo $str;
        die;
    }

    function entity_shop(array &$request, array &$response, array &$app) {
        //出库状态
        $response['shift_out_status'] = load_model('stm/StoreShiftRecordModel')->shift_out_status;
        //入库状态
        $response['shift_in_status'] = load_model('stm/StoreShiftRecordModel')->shift_in_status;
    }

    function entity_shop_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StoreShiftRecordModel')->get_shift_type();
        $response['shift_type'] = !empty($ret['shift_type']) ? $ret['shift_type'] : '';
        $response['user_type'] = !empty($ret['user_type']) ? $ret['user_type'] : '';

//        $response['shift_type'] = array_merge(array(array('','请选择')),$response['shift_type']);
    }

    function entity_shop_view(array & $request, array & $response, array & $app) {
        $app['act'] = 'view';
        $this->view($request, $response, $app);
    }

    function get_store_info(array & $request, array & $response, array & $app) {
        //$app['fmt'] = 'json';
        $ret = load_model('stm/StoreShiftRecordModel')->get_store_info($request['shift_type']);
        exit_json_response($ret);
    }

    //扫描单据添加数量
    function update_scan_num(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StoreShiftRecordModel')->update_scan_num($request['record_code'], $request['num'], $request['id']);
        exit_json_response($ret);
    }

    function check_is_print(array & $request, array & $response, array & $app) {
        $response = load_model('stm/StoreShiftRecordModel')->check_is_print($request['shift_record_id']);
    }

}
