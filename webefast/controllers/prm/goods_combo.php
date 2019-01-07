<?php

/**
 * 套餐商品控制器相关业务
 * @author dfr
 */
require_lib('util/web_util', true);
require_lib('comm_util', true);

class goods_combo {

    function do_list(array & $request, array & $response, array & $app) {
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code('goods_spec1');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code('goods_spec2');
        $response['spec1_rename'] = $arr_spec1['goods_spec1'];
        $response['spec2_rename'] = $arr_spec2['goods_spec2'];
    }

    function detail(array & $request, array & $response, array & $app) {
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code('spec_power');
        $response['spec_power'] = isset($arr_spec) ? $arr_spec : '';
        $response['action'] = isset($_GET['app_scene']) ? $_GET['app_scene'] : '';
//        $response['spec1'] = $this->get_spec1();
//        $response['spec2'] = $this->get_spec2();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('prm/GoodsComboModel')->get_by_id($request['_id']);
            $combo_barcode = load_model('prm/GoodsComboBarcodeModel');
            $ret['data']['barcode'] = $combo_barcode->get_barcode($ret['data']['goods_code']);
            $combo_sku_arr = array_column($ret['data']['barcode'], 'sku');
            $response['is_use'] = $combo_barcode->check_combo_sku_use($combo_sku_arr);
        } else {
            $ret['data']['goods_code'] = load_model('prm/GoodsComboModel')->create_fast_bill_sn();
        }
        
        foreach($ret['data']['barcode'] as $value){
            $spec1_arr[] .= $value['spec1_code'];
            $spec2_arr[] .= $value['spec2_code'];
        }
        $spec1_arr = array_unique($spec1_arr); 
        $spec2_arr = array_unique($spec2_arr); 
        $goods_spec1_code_str = "'" . implode("','", $spec1_arr) . "'";
        $goods_spec2_code_str = "'" . implode("','", $spec2_arr) . "'";
        /*如果参数没开，判断是否是添加，添加默认规格*/
        if($arr_spec['spec_power'] == 0 && $response['action'] == 'do_add'){
            $spec1_data = load_model('prm/Spec1Model')->get_by_field('spec1_code','000','spec1_code,spec1_name');
            $response['spec1'][0] = $spec1_data['data'];
            $spec2_data = load_model('prm/Spec2Model')->get_by_field('spec2_code','000','spec2_code,spec2_name');
            $response['spec2'][0] = $spec2_data['data'];
            $ret['data']['goods_spec1_str_code'] = '000';
            $ret['data']['goods_spec2_str_code'] = '000';            
        }else{
            $response['spec1'] = load_model('prm/Spec1Model')->get_by_code_spec1($goods_spec1_code_str);
//        var_dump( $response['spec1'],$ret['data']['barcode']);die;
            $response['spec2'] = load_model('prm/Spec2Model')->get_by_code_spec2($goods_spec2_code_str);
            $ret['data']['goods_spec1_str_code'] = implode(",", $spec1_arr);
            $ret['data']['goods_spec2_str_code'] = implode(",", $spec2_arr);
        }
//        var_dump( $response['spec1'],$ret['data']['barcode']);die;
        

        
        $response['data'] = $ret['data'];
        $response['_id'] = $request['_id'];
        foreach($response['data']['barcode'] as $value){
            if($value['goods_exist'] == 1){
                $spec1_disabled[] .= $value['spec1_code'];
                $spec2_disabled[] .= $value['spec2_code'];
            }
        }
        if($arr_spec['spec_power'] == 1){
        $response['goods_spec1_disabled'] = $spec1_disabled;
        $response['goods_spec2_disabled'] = $spec2_disabled;
        }else{
        $response['goods_spec1_disabled'] = $spec1_data['data'];
        $response['goods_spec2_disabled'] = $spec2_data['data'];    
        }
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
    }

    function view(array & $request, array & $response, array & $app) {
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code('spec_power');
        $response['spec_power'] = isset($arr_spec) ? $arr_spec : '';
        $goods_arr = load_model('prm/GoodsComboModel')->get_by_id($request['goods_combo_id']);
        $goods_code = $goods_arr['data']['goods_code'];
            $barcord = load_model('prm/GoodsComboBarcodeModel')->get_barcode($goods_code);
        
        filter_fk_name($barcord, array('goods_code|goods_combo', 'spec1_code|spec1_code', 'spec2_code|spec2_code'));
        foreach ($barcord as $key => $v) {

            $arr1 = array(':p_sku' => $v['sku'], ':p_goods_code' => $goods_code);
            $diy = load_model('prm/GoodsComboDiyModel')->get_diy_list($arr1);



            foreach ($diy as $k => $v1) {
                $arr_goods_spec1 = load_model('prm/SkuModel')->get_spec_by_goods_code($v1['goods_code'], 1);
                $diy[$k]['spec1_data'] = $arr_goods_spec1['data'];
                $arr_goods_spec2 = load_model('prm/SkuModel')->get_spec_by_goods_code($v1['goods_code'], 2);
                $diy[$k]['spec2_data'] = $arr_goods_spec2['data'];
            }
            $barcord[$key]['diy'] = $diy;

            //}
        }
        $response['goods_combo_id'] = $request['goods_combo_id'];
        $response['goods_code'] = $goods_code;
        $response['goods_combo_barcode_id'] = !empty($request['goods_combo_barcode_id'])?$request['goods_combo_barcode_id']:null;
        //$response['sku'] = $request['sku'];

        $response['barcord'] = $barcord;
        $response['goods_code'] = $goods_code;
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
    }

    //新增套餐明细
    function do_add_detail(array &$request, array &$response, array &$app) {
        //print_r($request);exit;
        $data = $request['data'];
        //明细添加
        $ret = load_model('prm/GoodsComboDiyModel')->add_detail_action($request['p_sku'], $request['p_goods_code'], $data);
        exit_json_response($ret);
    }

    function do_add(array & $request, array & $response, array & $app) {
        //print_r($request);exit;$ret = array();
        //$request['create_time'] = date("Y-m-d H:I:S", time());
        $request['create_time'] = date('Y-m-d H:i:s');
        //可能修改编码,组合新sku
        foreach($request['sku'] as $key => &$val) {
            $spec_arr = explode('_', $key);
            $val = $request['goods_code'] . $spec_arr[0] . $spec_arr[1] . '_sku';
        }
        $goods = get_array_vars($request, array('goods_code', 'goods_name', 'price', 'goods_desc', 'create_time','barcode'));
        $ret = load_model('prm/GoodsComboModel')->insert($goods);
        //修改api_order_detail表
        if ($ret['status'] == '1') {
            if (!empty($request['spec1'])) {
                $ret1 = load_model('prm/GoodsComboBarcodeModel')->save($request);
            }
        } else if($ret['status'] == '-1'){
            $ret['status'] = -1;
            $ret['message'] = '套餐基本信息保存失败或套餐编码重复';
        }
        exit_json_response($ret);
    }

    function do_edit(array & $request, array & $response, array & $app) {
        $ret = load_model('prm/GoodsComboModel')->get_row(array('goods_combo_id' => $request['goods_combo_id']));
        $combo_sku_arr = $request['sku'];
        $is_use = load_model('prm/GoodsComboBarcodeModel')->check_combo_sku_use($combo_sku_arr);
        if($is_use == true && $request['goods_code'] != $ret['data']['goods_code']) {
            $ret['status'] = '-1';
            $ret['data'] = 'true';
            $ret['message'] = '已使用的套餐不能修改套餐编码';
            exit_json_response($ret);
        }
        $goods_combo_old = load_model('prm/GoodsComboModel')->get_by_id($request['goods_combo_id']);
        if ($request['goods_code'] != $ret['data']['goods_code']) {
            foreach ($request['sku'] as $key => &$val) {
                $spec_arr = explode('_', $key);
                $request['new_sku'][$key] = $request['goods_code'] . $spec_arr[0] . $spec_arr[1] . '_sku';
            }
        }
        foreach ($request["barcode"] as $key => $value) {
            if ($value != '') {
                $flag = load_model('prm/GoodsComboModel')->barcode_goods_exist($value);
                $ret = array();
                if ($flag) {
                    $ret['status'] = '-1';
                    $ret['data'] = 'true';
                    $ret['message'] = '输入商品条形码重复';
                    exit_json_response($ret);
                } else {

                    $flag1 = load_model('prm/GoodsComboModel')->barcode_exist($value, $request['goods_code'], $key, $request['goods_combo_id']);
                    //var_dump($flag);
                    if ($flag1) {
                        $ret['status'] = '-1';
                        $ret['data'] = 'true';
                        $ret['message'] = '输入商品条形码重复';
                        exit_json_response($ret);
                    } else {
                        $ret['status'] = '0';
                        $ret['data'] = 'false';
                        $ret['message'] = '此条码可以使用';
                    }
                }
            }
        }
        $log_xq = '';
        //print_r($request);die;
        $request['goods_code_old'] = $goods_combo_old['data']['goods_code'];
        $goods = get_array_vars($request, array('goods_code', 'goods_name', 'price', 'goods_desc'));
        $old_data = load_model('prm/GoodsComboModel')->get_row(array('goods_combo_id' => $request['goods_combo_id']));
        $old_data = $old_data['data'];
        if ($old_data['goods_name'] != $goods['goods_name']) {
            $log_xq.='套餐名称由' . $old_data['goods_name'] . '改为' . $goods['goods_name'] . '，';
        }
        if ($old_data['price'] != $goods['price']) {
            $log_xq.='套餐价格由' . $old_data['price'] . '改为' . $goods['price'] . '，';
        }
        if ($old_data['goods_desc'] != $goods['goods_desc']) {
            $log_xq.='套餐描述由' . $old_data['goods_desc'] . '改为' . $goods['goods_desc'] . '，';
        }
        $ret = load_model('prm/GoodsComboModel')->update($goods, $request['goods_combo_id']);
        //修改api_order_detail表

        if ($ret['status'] == '1') {
            $ret = load_model('prm/GoodsComboBarcodeModel')->save($request);
            $log_xq.='增加或修改套餐基本信息中商品条码信息，';
        } else {
            $ret['status'] = -1;
            $ret['message']  = '套餐基本信息保存失败或套餐编码重复';
        }
        $ret['data'] = $request['goods_combo_id'];
        if (!empty($log_xq)) {
            $module = '商品'; //模块名称
            $yw_code = $request['goods_code']; //业务编码
            $operate_xq = '编辑套餐基本信息'; //操作详情
            $operate_type = '编辑';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
            $ret1 = load_model('sys/OperateLogModel')->insert($log);
        }
        exit_json_response($ret);
    }

    //规格1列表
    function get_spec1() {
        //规格1  start
        $arr_spec1 = load_model('prm/Spec1Model')->get_spec1();
        $key = 0;
        foreach ($arr_spec1 as $value) {
            $arr_spec1[$key][0] = $value['spec1_code'];
            $arr_spec1[$key][1] = $value['spec1_name'];
            $key++;
        }
        return $arr_spec1;
    }

    //规格2列表
    function get_spec2() {
        $arr_spec2 = load_model('prm/Spec2Model')->get_spec2();
        $key = 0;
        foreach ($arr_spec2 as $value) {
            $arr_spec2[$key][0] = $value['spec2_code'];
            $arr_spec2[$key][1] = $value['spec2_name'];
            $key++;
        }
        return $arr_spec2;
    }

    function show_detail(array & $request, array & $response, array & $app) {
        //print_r($request);exit;
        $arr1 = array(':p_sku' => $request['p_sku'], ':p_goods_code' => $request['p_goods_code']);
        $diy = load_model('prm/GoodsComboDiyModel')->get_diy_list($arr1);
        foreach ($diy as $k => $v1) {
//			$arr_price1 = load_model('prm/GoodsModel')->get_by_field('goods_code',$v1['goods_code'],'sell_price','goods_price');
//			$diy[$k]['sell_price'] =isset($arr_price1['data']['sell_price'])?$arr_price1['data']['sell_price']:'' ;
//			$arr_barcode1 = load_model('prm/GoodsBarcodeModel')->get_by_field_muti('goods_code',$v1['goods_code'],'sku',$v1['sku'],'barcode','goods_barcode');
//			$diy[$k]['barcode'] =isset($arr_barcode1['data']['barcode'])?$arr_barcode1['data']['barcode']:'' ;

            $key_arr = array('goods_code', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'goods_name', 'barcode', 'sell_price');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($v1['sku'], $key_arr);
            $diy[$k] = array_merge($v1, $sku_info);

            $arr_goods_spec1 = load_model('prm/GoodsSpec1Model')->get_by_code($v1['goods_code']);
            $diy[$k]['spec1_data'] = $arr_goods_spec1['data'];
            $arr_goods_spec2 = load_model('prm/GoodsSpec2Model')->get_by_code($v1['goods_code']);
            $diy[$k]['spec2_data'] = $arr_goods_spec2['data'];
        }
        //print_r($diy);
        $response['diy'] = $diy;
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
    }

    //明细保存
    function mx_save(array &$request, array &$response, array &$app) {
        $log_xq = '';
        $app['fmt'] = 'json';
        if (!empty($request['barcord_price'])) {
            //套餐价格修改
            $ret = load_model('prm/GoodsComboBarcodeModel')->update_save($request);
            $log_xq.=$ret['log_xq'];
        }
        if (!empty($request['diy_price'])) {
            //组装商品数量修改
            $ret = load_model('prm/GoodsComboDiyModel')->update_save($request['diy_price'], $request['spec1_code'], $request['spec2_code'],$request['diy_combo_diy_price']);
            $log_xq.=$ret['log_xq'];
        }
        if (!empty($log_xq)) {
            $module = '商品'; //模块名称
            $yw_code = ''; //业务编码
            $operate_xq = '编辑套餐商品信息'; //操作详情
            $operate_type = '编辑';
            $log_xq_ = rtrim($log_xq,'，');
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_xq_);
            $ret1 = load_model('sys/OperateLogModel')->insert($log);
        }
        $response = $ret;
    }

    //del_diy删除组合
    function del_diy(array &$request, array &$response, array &$app) {
        //print_r($request);exit;
        $app['fmt'] = 'json';
        $ret = load_model('prm/GoodsComboDiyModel')->del_diy($request['goods_combo_diy_id'], $request['p_goods_code'], $request['p_sku']);
        $response = $ret;
    }

    /**
     * barcode是否重复
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function barcode_exist(array &$request, array &$response, array &$app) {

        if (isset($request['barcode']) && $request['barcode'] != '') {

            $flag = load_model('prm/GoodsComboModel')->barcode_goods_exist($request['barcode']);
            $ret = array();
            if ($flag) {
                $ret['status'] = '-1';
                $ret['data'] = 'true';
                $ret['message'] = '输入条形码与商品条形码重复';
            } else {

                $flag1 = load_model('prm/GoodsComboModel')->barcode_exist($request['barcode'], $request['goods_code'], $request['spec']);
                //var_dump($flag);
                if ($flag1) {
                    $ret['status'] = '-1';
                    $ret['data'] = 'true';
                    $ret['message'] = '输入条形码与套餐条形码重复';
                } else {
                    $ret['status'] = '1';
                    $ret['data'] = 'false';
                    $ret['message'] = '此条码可以使用';
                }
            }
            exit_json_response($ret);
        }
    }

    function goods_inv(array &$request, array &$response, array &$app) {
        //print_r($request);
        $store_data = load_model('base/StoreModel')->get_list();
        $response['store_data'] = $store_data;
        $store_code = isset($request['store_code']) ? $request['store_code'] : '001';
        $response['store_code'] = $store_code;
        $goods_arr = load_model('prm/GoodsComboModel')->get_by_id($request['_id']);
        $goods_code = $goods_arr['data']['goods_code'];
        $barcord = load_model('prm/GoodsComboBarcodeModel')->get_barcode($goods_code);
        filter_fk_name($barcord, array('goods_code|goods_combo', 'spec1_code|spec1_code', 'spec2_code|spec2_code'));
        foreach ($barcord as $key => $v) {

            $arr1 = array(':p_sku' => $v['sku'], ':p_goods_code' => $goods_code);
            $diy = load_model('prm/GoodsComboDiyModel')->get_diy_list($arr1);
            $min_arr = array();
            foreach ($diy as $k => $v1) {
                $inv_arr = load_model('prm/GoodsComboModel')->goods_inv($v1['sku'], $store_code);
                $ke = $inv_arr['stock_num'] - $inv_arr['lock_num'];
                $min_arr[] = intval($ke / $v1['num']);
            }
            $barcord[$key]['diy_min'] = min($min_arr);
            if ($barcord[$key]['diy_min'] < 0) {
                $barcord[$key]['diy_min'] = 0;
            }
            $barcord[$key]['diy'] = $diy;

            //}
        }
        $response['barcord'] = $barcord;
        $response['_id'] = $request['_id'];
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec1['goods_spec1']) ? $arr_spec1['goods_spec1'] : '';
        //spec2别名
        $arr = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec2_rename'] = isset($arr_spec2['goods_spec2']) ? $arr_spec2['goods_spec2'] : '';
    }

    /**
     * 启用停用
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function update_active(array &$request, array &$response, array &$app) {
        //print_r($request);exit;
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('prm/GoodsComboModel')->update_active($arr[$request['type']], $request['id']);

        exit_json_response($ret);
    }

    /**
     * 批量启用、停用
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function multi_update_active(array &$request, array &$response, array &$app) {
        $ret = load_model('prm/GoodsComboModel')->multi_update_active($request['active'], $request['ids']);
        exit_json_response($ret);
    }
    
    /**
     * 检查状态
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function check_status(array &$request, array &$response, array &$app) {
        $ret = load_model('prm/GoodsComboModel')->check_status($request['goods_combo_id'], $request['barcode']);
        exit_json_response($ret);
    }
    
    /**
     * 删除
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('prm/GoodsComboModel')->do_delete($request['barcode'],$request['goods_code']);
        exit_json_response($ret);
    }
    
    function diy_rep(array &$request, array &$response, array &$app) {
        print_r($request);
        exit;
    }

    function diy_import(array &$request, array &$response, array &$app) {

    }

    function import_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $response = load_model('prm/GoodsComboDiyModel')->import($request['goods_code'], $file);
    }

    function import_upload(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('prm/GoodsComboDiyModel')->import_upload();
          set_uplaod($request, $response, $app);
    }
    
    /**套餐子商品
     */
    function detail_list(array &$request, array &$response, array &$app){
        
    }

}
