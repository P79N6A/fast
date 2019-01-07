<?php

require_lib('util/web_util', true);
require_lib('business_util', true);
require_lib('util/oms_util', true);
class purchase_record {

    function do_list(array & $request, array & $response, array & $app) {
        $login_type = CTX()->get_session('login_type');
        $response['login_type'] = $login_type;
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('fx/PurchaseRecordModel')->get_by_id($request['_id']);
        }
        //调整仓库
        //$login_type = CTX()->get_session('login_type');
//        if($login_type == 2) {
            $response['store'] = load_model('base/StoreModel')->get_fx_select(1);
//        } else {
//            $response['store'] = load_model('base/StoreModel')->get_select(2);
//        }
        $ret['data']['record_code'] = load_model('fx/PurchaseRecordModel')->create_fast_bill_sn();
        $response['data'] = $ret['data'];
        $login_type = CTX()->get_session('login_type');
        $response['login_type'] = $login_type;
        if($login_type == 2){
            $user_code = CTX()->get_session('user_code');
            $custom_code = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            $response['custom'] = $custom_code;
        }
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
        $ret = load_model('fx/PurchaseRecordModel')->get_by_id($request['purchaser_record_id']);
        $response['selection']['store'] = load_model('base/StoreModel')->get_view_select();
        $response['selection']['express_code'] = bui_get_select('express', 0, array('status' => 1));
        $custom_info = ds_get_select('custom');
        $response['selection']['custom'] = $this->custom_info($custom_info);
        //状态
        $is_check = !empty($ret['data']['is_check']) && $ret['data']['is_check'] == 1 ? '已确认' : '未确认';
        $is_settlement = isset($ret['data']['is_settlement']) && $ret['data']['is_settlement'] == 1 ? '已结算' : '未结算';
        $deliver = array(0=>'未出库',1=>'已出库',2=>'部分出库');
        $is_deliver = $deliver[$ret['data']['is_deliver']];
        $ret['data']['record_status'] = $is_check .' '. $is_settlement .' '. $is_deliver;
         //取得国家数据
        $response['area']['country'] = load_model('base/TaobaoAreaModel')->get_area('0');
        $response['area']['province'] = load_model('base/TaobaoAreaModel')->get_area($ret['data']['country']);
//        $response['area']['province'] = array();
//        $response['area']['province'] = load_model('base/TaobaoAreaModel')->get_area($ret['data']['country']);
        $response['area']['city'] = load_model('base/TaobaoAreaModel')->get_area($ret['data']['province']);
        $response['area']['district'] = load_model('base/TaobaoAreaModel')->get_area($ret['data']['city']);
        $response['area']['street'] = load_model('base/TaobaoAreaModel')->get_area($ret['data']['district']);
        $country = oms_tb_val('base_area', 'name', array('id' => $ret['data']['country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $ret['data']['province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $ret['data']['city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $ret['data']['district']));
        //$street = oms_tb_val('base_area', 'name', array('id'=>$request['receiver_street']));
        $ret['data']['addr'] = $country . $province . $city . $district . $ret['data']['address'];
        $response['data'] = $ret['data'];
              
        $response['data']['custom_name'] = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $ret['data']['custom_code']));
       
        //spec1别名
        $arr = array('goods_spec1', 'goods_spec2', 'lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';
        $response['lof_status'] = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $response['price_status'] = $price_status['status'];
        $login_type = CTX()->get_session('login_type');
        $response['login_type'] = $login_type;
    }
    
    function custom_info($custom_info){
        $custom_arr = array();
        foreach ($custom_info as $val) {
            $custom_arr[$val['custom_code']] = $val['custom_name'];
        }
        return json_encode(bui_bulid_select($custom_arr));
    }
            
    function do_add(array & $request, array & $response, array & $app) {
        $request['order_time'] = date('Y-m-d H:i:s', time());
        $stock_adjus = get_array_vars($request, array('record_code', 'order_time', 'init_code', 'record_time', 'custom_code', 'store_code', 'rebate', 'remark'));
        $login_type = CTX()->get_session('login_type');
        if($login_type == 2){
            $user_code = CTX()->get_session('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            $stock_adjus['custom_code'] = $custom['custom_code'];
        }
        $ret = load_model('fx/PurchaseRecordModel')->insert($stock_adjus);
        if (isset($ret['data']) && $ret['data'] <> '') {
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未出库', 'action_name' => "创建", 'module' => "fx_purchase_record", 'pid' => $ret['data']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }
    
    function do_edit(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/PurchaseRecordModel')->edit_action($request['parameter'], array('purchaser_record_id' => $request['parameterUrl']['purchaser_record_id']));
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '修改', 'module' => "fx_purchase_record", 'pid' => $request['parameterUrl']['purchaser_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    /**
     * 审核
     */
    function do_check(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('fx/PurchaseRecordModel')->update_check($arr[$request['type']], 'is_check', $request['id']);
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
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未出库', 'action_name' => $action_name, 'module' => "fx_purchase_record", 'pid' => $request['id']);
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
        $ret = load_model('fx/PurchaseRecordModel')->add_detail_goods($request['id'], $request['data'], $request['store_code']);
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
        $detail = array('rebate' => $request['rebate'], 'record_code' => $request['record_code'], 'sku' => $request['sku'], 'num' => $request['num'], 'price' => $request['price']);
        $ret = load_model('fx/PurchaseRecordDetailModel')->edit_detail_action($request['pid'], $detail);
        exit_json_response($ret);
    }

    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/PurchaseRecordModel')->delete($request['purchaser_record_id']);
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

        $ret = load_model('fx/PurchaseRecordDetailModel')->delete($request['purchaser_record_detail_id']);
        //批次删除
        $ret1 = load_model('stm/GoodsInvLofRecordModel')->delete_pid($request['pid'], $request['sku'], 'fx_purchase');
        exit_json_response($ret);
    }

    //删除批次
    function do_delete_detail_lof(array & $request, array & $response, array & $app) {
        $ret = load_model('fx/PurchaseRecordDetailModel')->delete_lof($request['id']);
        exit_json_response($ret);
    }

    function update_check(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('stm/StockAdjustRecordModel')->update_check($arr[$request['type']], $request['id']);
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
        $ret = load_model('fx/PurchaseRecordModel')->imoprt_detail($request['id'], $file, $request['is_lof']);
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
        $ret = load_model('fx/PurchaseRecordModel')->get_by_id($request['id']);
        $main_result = $ret['data'];
//		print_r($main_result);exit;

        $filter['record_code'] = $request['record_code'];
        $filter['code_name'] = $request['code_name'];
        $filter['page'] = 1;
        $filter['page_size'] = 1000;
        $lof_no = '';
        if ($request['is_lof'] != 1) {
            $res = load_model('fx/PurchaseRecordDetailModel')->get_by_page($filter);
        } else {
            $res = load_model('fx/PurchaseRecordDetailModel')->get_by_page_lof($filter);
            $lof_no = "批次号";
        }
        $detail_result = $res['data']['data'];

        $str = "单据编号,原单号,分销商,仓库,下单日期,业务日期,商品名称,商品编码,规格1代码,规格1名称,规格2代码,规格2名称,商品条形码,采购单价,实际入库数,商品总金额,通知数,差异数," . $lof_no . "\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($detail_result as $value) {

            $custom_name = iconv('utf-8', 'gbk', $main_result['custom_info']['custom_name']);
            $store_name = iconv('utf-8', 'gbk', $main_result['store_name']);
            $record_type_name = iconv('utf-8', 'gbk', $main_result['record_type_name']);
            $value['goods_name'] = iconv('utf-8', 'gbk', $value['goods_name']);
            $value['spec1_name'] = iconv('utf-8', 'gbk', $value['spec1_name']);
            $value['spec2_name'] = iconv('utf-8', 'gbk', $value['spec2_name']);
            $lof = '';
            if ($request['is_lof'] == 1) {
                $lof = $value['lof_no'];
            }
            $str .= $value['record_code'] . "\t," . $main_result['init_code'] . "," . $custom_name . "," . $store_name .
                    "," . $main_result['order_time'] . "\t," . $main_result['record_time'] . "\t," . $value['goods_name'] . "," . $value['goods_code'] . "," . $value['spec1_code'] . "," . $value['spec1_name'] .
                    "," . $value['spec2_code'] . "," . $value['spec2_name'] . "\t," . $value['barcode'] . "\t," . $value['price'] .
                    "," . $value['finish_num'] . "," . $value['money'] . "," . $value['lof_num'] . "," . $value['num_differ'] . "," . $lof . "\n"; //用引文逗号分开  
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

    //结算
    function do_settlement(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/PurchaseRecordModel')->do_settlement($request['record_code'],$request['type']);
        exit_json_response($ret);
    }
    
    //取消结算
    function do_unsettlement(array &$request, array &$response, array &$app) {
        $ret = load_model('fx/PurchaseRecordModel')->do_unsettlement($request['record_code'],$request['type']);
        exit_json_response($ret);
    }
    
    function do_delivery(array &$request, array &$response, array &$app){
        $ret = load_model('fx/PurchaseRecordModel')->do_delivery($request['record_code']);
        exit_json_response($ret);
    }
}
