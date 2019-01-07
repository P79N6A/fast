<?php

require_lib('util/web_util', true);
require_lib('business_util', true);

class stm_goods_diy_record {

    function do_list(array & $request, array & $response, array & $app) {
        //models/oms/TranslateOrderModel.php  match_addr($api_data) 9153626203
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('stm/StmGoodsDiyRecordModel')->get_by_id($request['_id']);
        }
        $data[1]['record_type'] = '0';
        $data[1]['record_type_name'] = '组装';
        $data[2]['record_type'] = '1';
        $data[2]['record_type_name'] = '拆分';
        $data = array_merge(array(array('', '请选择')), $data);
        $response['record_type']=$data;
        $ret['data']['record_code'] = load_model('stm/StmGoodsDiyRecordModel')->create_fast_bill_sn();
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
        $ret = load_model('stm/StmGoodsDiyRecordModel')->get_by_id($request['goods_diy_record_id']);

        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();

        //$response['selection']['record_type'] = bui_get_select('record_type',0,array('record_type_property'=>9));;
        $ok = get_theme_url('images/ok.png');
        $no = get_theme_url('images/no.gif');
        if ($ret['data']['is_sure'] == '1') {
            $is_check_src = $ok;
        } else {
            $is_check_src = $no;
        }
        $ret['data']['is_check_src'] = "<img src='{$is_check_src}'>";
        $response['data'] = $ret['data'];
        $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($ret['data']['store_code']);
        if ($wms_system_code == 'iwms' || $wms_system_code == 'iwmscloud') {
            $response['data']['is_wms'] = '1';
        } else {
            $response['data']['is_wms'] = '0';
        }
        //spec1别名
        //spec1别名
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $response['data'] ['record_type_name'] = $response['data'] ['record_type'] == '0' ? '组装' : '拆分';
        //print_r($response);
    }

    function do_add(array & $request, array & $response, array & $app) {
        $request['order_time'] = date('Y-m-d H:i:s', time());
        $stock_adjus = get_array_vars($request, array('record_code', 'order_time', 'record_time', 'store_code', 'remark','record_type'));
        $ret = load_model('stm/StmGoodsDiyRecordModel')->insert($stock_adjus);
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未出库', 'action_name' => "创建", 'module' => "stm_goods_diy_record", 'pid' => $ret['data']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 商品组装单增加明细
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-10
     *
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array json
     */
    function do_add_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StmGoodsDiyRecordDetailModel')->add_detail_action($request);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '增加明细', 'module' => "stm_goods_diy_record", 'pid' => $request['pid']);
            load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 修改单据明细数量
     * @param array $request
     * @param array $response
     * @param array $app
     * @return array json
     */
    function do_edit_detail(array & $request, array & $response, array & $app) {
        $detail = array(
            array('sku' => $request['sku'], 'num' => $request['num'], 'price' => $request['price']),
        );
        $ret = load_model('stm/StmGoodsDiyRecordDetailModel')->edit_detail_action($request['pid'], $detail);
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StmGoodsDiyRecordModel')->delete($request['goods_diy_record_id']);
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

    /**
     * 确认/取消确认
     */
    function do_sure(array &$request, array &$response, array &$app) {

        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('stm/StmGoodsDiyRecordModel')->update_sure($arr[$request['type']], 'is_sure', $request['id']);
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
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未完成', 'action_name' => $action_name, 'module' => "stm_goods_diy_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        if (isset($ret)) {
            $bill_sn = isset($ret['data']['record_code']) ? $ret['data']['record_code'] : '';
            $ret['stock_adjust_record_id'] = isset($ret['data']['stock_adjust_id']) ? $ret['data']['stock_adjust_id'] : '';
            $ret['msg'] = "已生成调整单" . $bill_sn . "，是否打开已生成调整单详情？";
        }
        exit_json_response($ret);
    }
    
    function do_check(array &$request, array &$response, array &$app) {
        $ret = load_model('stm/StmGoodsDiyRecordModel')->update_check_by_id($request['id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '审核', 'module' => "stm_goods_diy_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }
    
    /**
     * @todo 取消审核
     */
    function do_uncheck(array &$request, array &$response, array &$app) {
        $ret = load_model('stm/StmGoodsDiyRecordModel')->uncheck_by_id($request['id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '未审核', 'module' => "stm_goods_diy_record", 'pid' => $request['id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }
    
    /**
     * 删除单据明细
     * @param array $request
     * @param array $response
     * @param array $app
     * @throws Exception
     */
    function do_delete_detail(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StmGoodsDiyRecordDetailModel')->delete($request['id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '删除明细', 'module' => "stm_goods_diy_record", 'pid' => $request['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //删除批次
    function do_delete_detail_lof(array & $request, array & $response, array & $app) {
        $ret = load_model('stm/StmGoodsDiyRecordDetailModel')->delete_detail_lof($request['id']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '删除批次', 'module' => "stm_goods_diy_record", 'pid' => $request['pid']);
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
        $ret = load_model('stm/StmGoodsDiyRecordModel')->edit_action($request['parameter'], array('goods_diy_record_id' => $request['parameterUrl']['goods_diy_record_id']));
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '修改', 'module' => "stm_goods_diy_record", 'pid' => $request['parameterUrl']['goods_diy_record_id']);
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
        
    }
    
    function import_goods_upload(array & $request, array & $response, array & $app){
        $app['fmt'] = 'json';

        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        if($request['lof_status'] == 1) {
            $ret = load_model('stm/StmGoodsDiyRecordDetailModel')->imoprt_detail($request['id'], $file);
        } else {
            $ret = load_model('stm/StmGoodsDiyRecordDetailModel')->imoprt_detail_no_lof($request['id'], $file);
        }
        $response = $ret;
  
    }
    
    function add_detail_lof(array & $request, array & $response, array & $app){
        
    }
    
    public function get_lof_no_select_inv_panel(array & $request, array & $response, array & $app) {
        $diy_goods = load_model('stm/StmGoodsDiyRecordDetailModel')->get_diy_goods($request);
        $response['diy_goods'] = $diy_goods;
        
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
        //批次是否开启
        $response['lof'] = '';
        if (isset($request['lof_status']) && $request['lof_status'] == 1) {
            $arr = array('lof_status');
            $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
            $response['lof'] = isset($arr_lof['lof_status']) ? $arr_lof['lof_status'] : '';
        }
        $app['page'] = 'NULL';
    }
    
    public function goods_diy_select_action_inv(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        //批次是否开启
        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        if (isset($request['diy_good']) && $request['diy_good'] != '') {
            $diy_lof_goods = load_model('stm/StmGoodsDiyRecordDetailModel')->get_combo_sku($request['record_code'],$request['diy_good']);
            $request['diy_goods_lof'] = $diy_lof_goods['data'];
        }
        
        $result = load_model('prm/InvModel')->get_goods_diy_sku_inv($request);
        $info = $this->check_data($diy_lof_goods['data'], $result['data']['data']);
        $response['rows'] = $info;
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';

    }

    private function check_data($sku, $info) {
        $sku_arr = array();
        foreach ($info as $i) {
            $sku_arr[] = $i['sku'];
        }
        $sub_info = array();
        foreach ($sku as $sub_sku) {
            //商品是否含有库存记录，没有库存记录组装商品
            if (!in_array($sub_sku, $sku_arr)) {
                $goods_Info = load_model('prm/GoodsModel')->get_sku_list($sub_sku);
                $sub_info[] = array(
                    "stock_num" => 0,
                    "lock_num" => 0,
                    "sku" => $goods_Info['sku'],
                    "lof_no" => "",
                    "production_date" => "",
                    "spec1_code" => $goods_Info['spec1_code'],
                    "spec2_code" => $goods_Info['spec2_code'],
                    "goods_code" => $goods_Info['goods_code'],
                    "goods_name" => $goods_Info['goods_name'],
                    "barcode" => $goods_Info['barcode'],
                    "spec1_name" => $goods_Info['spec1_name'],
                    "spec2_name" => $goods_Info['spec2_name'],
                    "purchase_price" => $goods_Info['purchase_price']
                        );
            }
        }
        $new_info = array_merge($info, $sub_info);
        return $new_info;
    }

}
