<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms/TaobaoRecordModel', true);
require_model('oms/SellRecordFixModel', true);
require_model('oms/SellRecordOptModel', true);

class sell_record {

    //平台订单列表
    function td_list(array &$request, array &$response, array &$app) {
        $response['source'] = load_model('base/SaleChannelModel')->get_data_code_map();
        $response['change_fail_num'] = load_model('oms/OrderMenuTipModel')->get_fail_order_num();
        //平台标签
        $response['trade_tags'] = load_model('api/OrderTagModel')->get_all_source_tag();
        //订单类型（万人团）配置文件
        $response['type'] = require_conf('sys/trade_type');

        $response['order_down_priv'] = load_model('sys/PrivilegeModel')->check_priv('oms/api_order/down');
    }

    //平台订单保存
    function td_save(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $order_info = load_model('oms/ApiOrderModel')->get_by_id($request['api_order_id']);
        
        $arr_country = load_model('base/TaobaoAreaModel')->get_by_field('id', $request['country']);
        $request['receiver_country'] = isset($arr_country['data']['name']) ? $arr_country['data']['name'] : '';
        $arr_province = load_model('base/TaobaoAreaModel')->get_by_field('id', $request['province']);
        $request['receiver_province'] = isset($arr_province['data']['name']) ? $arr_province['data']['name'] : '';
        $arr_city = load_model('base/TaobaoAreaModel')->get_by_field('id', $request['city']);
        $request['receiver_city'] = isset($arr_city['data']['name']) ? $arr_city['data']['name'] : '';
        $arr_district = load_model('base/TaobaoAreaModel')->get_by_field('id', $request['district']);
        $request['receiver_district'] = isset($arr_district['data']['name']) ? $arr_district['data']['name'] : '';
        $arr_street = load_model('base/TaobaoAreaModel')->get_by_field('id', $request['street']);
        $request['receiver_street'] = isset($arr_street['data']['name']) ? $arr_street['data']['name'] : '';
        $request['receiver_address'] = $request['receiver_province'] . $request['receiver_city'] . $request['receiver_district'] . $request['receiver_street'] . $request['receiver_addr'];
        $ret['status'] = 1;
      //   'receiver_name', 'receiver_mobile','receiver_phone',
        $key_arr = array('receiver_country', 'receiver_province', 'receiver_city', 'receiver_district', 'receiver_street', 'receiver_address');
        if (isset($request['receiver_addr']) && !empty($request['receiver_addr'])) {
            $key_arr[] = 'receiver_addr';
        }
        if (isset($request['receiver_address']) && !empty($request['receiver_address'])) {
            $key_arr[] = 'receiver_address';
        }
        if (isset($request['receiver_name']) && !empty($request['receiver_name'])) {
            $key_arr[] = 'receiver_name';
        }
        if (isset($request['receiver_mobile']) && !empty($request['receiver_mobile'])) {
            $key_arr[] = 'receiver_mobile';
        }
        if (isset($request['receiver_phone']) && !empty($request['receiver_phone'])) {
            $key_arr[] = 'receiver_phone';
        }


        //修改api_order表
      if (!empty($request['receiver_addr']) ) {
            $data = get_array_vars($request, $key_arr);
            $data['customer_address_id'] = 0;
            $data['customer_code'] = '';
            $ret = load_model('oms/ApiOrderModel')->update($data, $request['api_order_id']);
            if ($ret['status'] == 1) {
                //添加日志
                $log = load_model('oms/ApiOrderModel')->add_order_operate_log($request, $order_info['data']);
            }
      }

        $detail_list = load_model('oms/ApiOrderDetailModel')->get_by_field_all('tid', $request['tid']);
        $update_barcode_arr = load_model('oms/ApiOrderModel')->get_update_barcode_log($request);
        //修改api_order_detail表
        if (!empty($request['barcode'])) {
            $ret = load_model('oms/ApiOrderDetailModel')->save($request['barcode']);
            if ($ret['status'] == 1) {
                //添加操作日志
                $log = load_model('oms/ApiOrderModel')->add_operate_log($detail_list, $request['barcode']);
            }
        }
        $response = $ret;
        //更新条码
        if ($request['update_status'] == 1) {
            $result = load_model('oms/ApiOrderModel')->update_barcode($request);
            //添加更新操作日志
            $log = load_model('oms/ApiOrderModel')->add_update_operate_log($detail_list, $update_barcode_arr);
        }

        $is_strong_safe = CTX()->get_app_conf('is_strong_safe');
        if (!$is_strong_safe) {
            $ret = load_model('oms/ApiOrderModel')->get_by_id($request['api_order_id']);
            //御城河日志
            $trade_data = array($ret['data']);
            load_model('common/TBlLogModel')->set_log_multi($trade_data, 'edit');
        }
    }

    //检测是否需要更新条码
    function td_save_goods_check(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/ApiOrderModel')->td_save_goods_check($request);
        exit_json_response($ret);
    }

    //平台订单详情
    function td_view(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/ApiOrderModel')->get_by_id($request['id']);
        $response['record'] = $ret['data'];
        if (isset($ret['data']) && !empty($ret['data'])) {
            $detail_list = load_model('oms/ApiOrderDetailModel')->get_by_field_all('tid', $ret['data']['tid'], $select = "*");
            //print_r($detail_list);
            $mingxi = '';
            foreach ($detail_list as $value) {
                $mingxi .= $value['detail_id'] . "_";
            }
            $mingxi = substr($mingxi, 0, strlen($mingxi) - 1);
            $response['record']['mingxi'] = $mingxi;
            $response['record']['detail_list'] = $detail_list;
        }

        //取得国家数据
        $response['area']['country'] = load_model('base/TaobaoAreaModel')->get_area('0');
        $response['area']['province'] = array();
        $area_ids = load_model('base/TaobaoAreaModel')->get_by_field_all($response['record']['receiver_country'], $response['record']['receiver_province'], $response['record']['receiver_city'], $response['record']['receiver_district'], $response['record']['receiver_street']);
        $response['record']['ids'] = $area_ids;

        $response['area']['province'] = load_model('base/TaobaoAreaModel')->get_area($area_ids['country_id']);

        $province_id = load_model('base/TaobaoAreaModel')->get_area_id_by_name($area_ids['country_id'], $response['record']['receiver_province']);
        $response['area']['city'] = load_model('base/TaobaoAreaModel')->get_area($province_id);
        $city_id = load_model('base/TaobaoAreaModel')->get_area_id_by_name($province_id, $response['record']['receiver_city'], $area_ids['city']);

        $response['area']['district'] = load_model('base/TaobaoAreaModel')->get_area($city_id);
        $district_id = load_model('base/TaobaoAreaModel')->get_area_id_by_name($city_id, $response['record']['receiver_district'], $area_ids['district']);
        $response['area']['street'] = load_model('base/TaobaoAreaModel')->get_area($district_id);
        //御城河日志
        $trade_data = array($response['record']);

        load_model('common/TBlLogModel')->set_log_multi($trade_data, 'view');
    }

    //平台订单转单
    function td_tran(array &$request, array &$response, array &$app) {
//        $app['fmt'] = 'json';
//        $m = new SellRecordFixModel();
//        $response = $m->fix_record($request['sell_record_code']);
        $sql = "select tid from api_order where id in(" . $request['api_order_id'] . ")";
        $tid_arr = ctx()->db->getAll($sql);

        $tids = array();
        foreach ($tid_arr as $tid) {
            $tids[] = $tid['tid'];
        }
        $response = load_model("oms/TranslateOrderModel")->translate_order($tids);
        
        if (empty($response['err'])) {
            $response['status'] = 1;
            $response['message'] = '转单成功';
        } else {
            $response['status'] = -1;
            $response['message'] = '转单失败,' . $response['err'][0]['message'];
        }
        if ($response['status'] == -1) {
            $response['change_fail_num'] = load_model('oms/OrderMenuTipModel')->get_fail_order_num();
        } else {
            $response['change_fail_num'] = 0;
        }
    }

    //平台订单标记已转单
    function td_traned(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        //td_traned($ids,$is_change=1)
        $is_change = isset($request['is_change']) ? $request['is_change'] : 1;
        $response = load_model('oms/ApiOrderModel')->td_traned($request['id'], $is_change);
    }

    //平台订单标记已转单
    function td_no_traned(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        //td_traned($ids,$is_change=1)
        $is_change = isset($request['is_change']) ? $request['is_change'] : 0;
        $response = load_model('oms/ApiOrderModel')->td_traned($request['id'], $is_change);
    }

    //订单查询
    function do_list(array &$request, array &$response, array &$app) {
//    	$response['source'] = load_model('base/SaleChannelModel')->get_data_code_map();
        $this->get_spec_rename($response);
        //页面操作权限获取
        $priv_map = array(
            'ex_update_detail' => 'oms/sell_record/ex_update_detail'
        );
        $obj_priv = load_model('sys/PrivilegeModel');
        $priv_arr = array();
        foreach ($priv_map as $opt => $val) {
            $priv_arr[$opt] = $obj_priv->check_priv($val);
        }
        $response['priv'] = $priv_arr;
        //打印发票增值
        $auth_print_invoice = load_model('common/ServiceModel')->check_is_auth_by_value('print_invoice');
        $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status'));
        $response['unique_status'] = $unique_arr['unique_status'];
        $response['auth_print_invoice'] = $auth_print_invoice;

        $response['record_time_start'] = date('Y-m-d H:i:s', strtotime('-3 month', strtotime(date('Y-m-d'))));
    }

    /**
     * 导出csv
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $m = new SellRecordModel();
        $data = $m->do_list_by_page($request);
        $result = $data['data'];
//		print_r($result);exit;
        $str = "店铺,交易号,订单号,订单状态,买家昵称,收货人,手机,收货地址,仓库,配送方式,快递单号,快递费,已付款,发票抬头,商家留言,买家留言,仓库留言,下单时间,计划发货时间,通知配货时间,发货时间,有无退单,订单标识,挂起原因及备注,问题原因及备注,商品名称,商品编码,商品条形码,规格1,规格2,数量,吊牌价,均摊金额,重量\n";
//	    $str = iconv('utf-8','gb2312',$str);
        foreach ($result['data'] as $value) {
            $shop_name = $value['shop_name'];
            $sql = " select *  from  oms_sell_return  where  sell_record_code = '" . $value['sell_record_code'] . "'";
            $result = ctx()->db->get_all($sql);
            if (empty($result))
                $back = "无";
            else
                $back = "有";
            $order_mark = "";
            if ($value['is_handwork'] == 1)
                $order_mark .= " 手工单  ";

            if ($value['is_pending'] == 1)
                $order_mark .= " 挂起单  ";

            if ($value['is_problem'] == 1)
                $order_mark .= " 问题单  ";

            if ($value['is_change_record'] == 1)
                $order_mark .= " 换货单  ";

            if ($value['is_split'] == 1)
                $order_mark .= " 拆分单  ";

            if ($value['is_combine'] == 1)
                $order_mark .= " 合并单  ";

            if ($value['is_copy'] == 1)
                $order_mark .= " 复制单  ";

            if ($value['is_wap'] == 1)
                $order_mark .= " 手机单  ";

            $pend_name = oms_tb_val('base_sell_pending_label', 'sell_psending_name', array('sell_psending_code' => $value['is_pending_code']));

            $pend = $pend_name . $value['is_pending_memo'];

            $sql = " select *  from oms_sell_record_tag where sell_record_code = '" . $value['sell_record_code'] . "'";
            $result = ctx()->db->get_all($sql);

            $problem = "";

            foreach ($result as $v) {
                $problem_reason = oms_tb_val('base_question_label', 'question_label_name', array('question_label_code' => $v['tag_v']));
                $problem .= $problem_reason . "  " . $v['tag_desc'] . "|";
            }

            $spec1_name = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));

            $spec2_name = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));

            $goods_name = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $value['goods_code']));
            $price = oms_tb_val('base_goods', 'price', array('goods_code' => $value['goods_code']));

            $str .= $value['shop_name'] . "," . $value['deal_code'] . "," . $value['sell_record_code'] . "," . $value['status'] . "," . $value['buyer_name'] . "," . $value['receiver_name'] .
                    "," . $value['receiver_mobile'] . "," . $value['receiver_address'] . "," . $value['store_name'] . "," . $value['express_name'] . "," . $value['express_no'] . "," . $value['express_money'] .
                    "," . $value['paid_money'] . "," . $value['invoice_title'] . "," . $value['seller_remark'] . "," . $value['buyer_remark'] . "," . $value['store_remark'] . "," . $value['record_time'] .
                    "," . $value['plan_send_time'] . "," . $value['is_notice_time'] . "," . $value['delivery_time'] . "," . $back . "," . $order_mark . "," . $pend . "," . $problem . "," . $goods_name . "," . $value['goods_code'] . "," . $value['sku'] .
                    "," . $spec1_name . "," . $spec2_name . "," . $value['num'] . "," . $price . "," . $value['avg_money'] . "," . $value['goods_weigh'] . "\n"; //用引文逗号分开
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
    }

    //详情
    function view(array &$request, array &$response, array &$app) {
        $m = new SellRecordModel();
        $response['record'] = $m->get_record_by_code($request['sell_record_code']);
        //御城河日志
        $trade_data = array($response['record']);
        //根据订单号判断是否含有非分销商品
        $response['is_out_goods'] = $response['record']['is_fenxiao'] == 1 ? $m->is_out_goods($request['sell_record_code']) : 3;
        load_model('common/TBlLogModel')->set_log_multi($trade_data, 'view');


        $response['tabs_name'] = isset($request['tabs_name']) ? $request['tabs_name'] : 'order_info';
    }

    //订单处理
    function ex_list(array &$request, array &$response, array &$app) {
        $this->get_spec_rename($response);
        //页面操作权限获取
        $priv_map = array(
            'inspect_priv' => 'oms/sell_record/inspect_record',
            'export_ext_list' => 'oms/sell_record/export_ext_list',
            'opt_edit_store_code' => 'oms/order_opt/opt_edit_store_code',
            'opt_edit_store_remark' => 'oms/order_opt/opt_edit_store_remark',
            'opt_edit_express_code' => 'oms/order_opt/opt_edit_express_code',
            'opt_pending' => 'oms/order_opt/opt_pending',
            'opt_unpending' => 'oms/order_opt/opt_unpending',
            'opt_confirm' => 'oms/order_opt/opt_confirm',
            'pl_opt_confirm' => 'oms/order_opt/pl_opt_confirm',
            'opt_alter_detail' => 'oms/order_opt/opt_alter_detail',
            'opt_change_detail' => 'oms/order_opt/opt_change_detail',
            'opt_cancel' => 'oms/order_opt/opt_cancel',
            'set_rush' => 'oms/order_opt/set_rush',
            'opt_unpay' => 'oms/order_opt/opt_unpay',
            'ex_update_detail' => 'oms/sell_record/ex_update_detail'
        );
        $obj_priv = load_model('sys/PrivilegeModel');
        $priv_arr = array();
        foreach ($priv_map as $opt => $val) {
            $priv_arr[$opt] = $obj_priv->check_priv($val);
        }
        $response['priv'] = $priv_arr;
    }

    function ex_update_detail(array &$request, array &$response, array &$app) {
        $response['record'] = load_model('oms/SellRecordModel')->get_record_by_code($request['sell_record_code']);
        $response['tabs_name'] = isset($request['tabs_name']) ? $request['tabs_name'] : 'edit_goods_info';
    }

    //发货订单列表
    function fh_list(array &$request, array &$response, array &$app) {
        $param_code = array('cainiao_intelligent_delivery',);
        $response['sys_params'] = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
        $response['source'] = load_model('base/SaleChannelModel')->get_data_code_map();
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] =isset($arr_spec1['goods_spec1'])?$arr_spec1['goods_spec1']:'' ;
        //spec2别名
        $arr2 = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr2);
        $response['goods_spec2_rename'] =isset($arr_spec2['goods_spec2'])?$arr_spec2['goods_spec2']:'' ;
    }

    //新增
    function add(array &$request, array &$response, array &$app) {
        $mdlSellRecord = new SellRecordModel();
        $response['shop_express_list'] = array();
        $response['shop_store_list'] = array();
        $response['channel_shop_list'] = array();
        //$response['arr_source'] = load_model('base/SaleChannelModel')->get_data_map();
        $arr_source = load_model('base/SaleChannelModel')->get_my_select();
        $response['arr_source'] = array2dict($arr_source);
        $response['arr_shop'] = load_model('base/ShopModel')->get_purview_shop('*', 'filter_fx');
        foreach ($response['arr_shop'] as $k => $v) {
            $response['shop_express_list'][$v['shop_code']] = $v['express_code'];
            $response['shop_store_list'][$v['shop_code']] = $v['send_store_code'];
            $response['channel_shop_list'][$v['sale_channel_code']][$v['shop_code']] = $v['shop_name'];
        }
        //取得省数据
        $response['area']['country'] = load_model('base/TaobaoAreaModel')->get_area('0');
        $ret = array();
        $ret['data'] = get_array_vars($ret, array('source', 'shop_code'));
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
        $response['login_type'] = CTX()->get_session('login_type');
        //权限
        $response['power']['next_step'] = load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/next_step');
        $response['power']['deliver_record_import'] = load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/deliver_record_import');
        $response['power']['sell_record_import'] = load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/sell_record_import');
    }

    //新增
    function add_action(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $request['receiver_country'] = isset($request['country']) ? $request['country'] : '';
        $request['receiver_province'] = isset($request['province']) ? $request['province'] : '';
        $request['receiver_city'] = isset($request['city']) ? $request['city'] : '';
        $request['receiver_district'] = isset($request['district']) ? $request['district'] : '';
        $request['receiver_street'] = isset($request['street']) ? $request['street'] : '';
        $request['deal_code'] = trim($request['deal_code']);
        $response = load_model('oms/SellRecordModel')->add($request);
        //echo '<hr/>response<xmp>'.var_export($response,true).'</xmp>';die;
    }

    //读取取详情各部分
    function component(array &$request, array &$response, array &$app) {
        $types = $request['components'];
        if ($request['type'] != 'all') {
            $types = explode(',', $request['type']);
        }
        $mdlSellRecord = new SellRecordModel();

        //读取订单
        $response = $mdlSellRecord->component($request['sell_record_code'], $types);
        //发票信息

        if(substr($response['record']['receiver_address'], -3) == '***'){ //加密
            $data =  load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($request['sell_record_code'],'receiver_address');
            $response['record']['decrypt_address'] = $data['receiver_address'];
        }
        
        $response['record_type'] = $request['record_type'];
        //var_dump($response);die;
        $response['add_his'] = isset($request['add_his']) ? $request['add_his'] : '';
        if (empty($response['record'])) {
            die(json_encode(array()));
        }
        $response['login_type'] = CTX()->get_session('login_type');
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        $response['safety_control'] = $cfg['safety_control'];
        $arr = array();
        $arr['record'] = $response['record'];
         if ($request['type'] == 'shipping' && $request['opt'] == 'edit') {
             $mdlSellRecord->get_sell_record_decrypt_info($response['record']);
         }
         //其他优惠金额
        if(empty($response['record']['other_amount'])){
            $response['record']['other_amount'] = '0.00';
        }
        foreach ($types as $type) {
            if($type == 'invoice_info'){
                if(empty($response['record']['decrypt_address'])){
                    $response['record']['decrypt_address'] = $response['record']['receiver_address'];
                }
            }
            ob_start();
            $app['scene'] = $request['opt'];
            $path = get_tpl_path('oms/sell_record/get_' . $type);
            include $path;
            $ret = ob_get_contents();
            ob_end_clean();
            $arr[$type] = $ret;
            //var_dump($arr[$type]);die;
        }
        //var_dump($arr['detail']);die;
        if (empty($response['detail_list'])) {
            $response['detail_list']['sell_record_code'] = $request['sell_record_code'];
        }
        if ($request['type'] == 'shipping' && $request['opt'] == 'edit' ) {
            $key_arr = array(
                'receiver_address' => '收货地址',
                'receiver_name' => '收货人',
                'receiver_mobile' => '手机',
                'receiver_phone' => '固定电话',
            );
            load_model('oms/SellRecordActionModel')->add_action($request['sell_record_code'], '发货信息编辑', implode(",", $key_arr));
        }
        //var_dump($arr);die;
        die(json_encode($arr));
    }

    //保存收货地址
    function save_component_ship(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $mdlSellRecord = new SellRecordModel();
        $response = $mdlSellRecord->save_component_ship($request['sell_record_code'], $request['type'], $request['data']);


        load_model('oms/SellRecordOptModel')->set_tb_log($request['sell_record_code']);
    }

    //保存详情各部分
    function save_component(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $mdlSellRecord = new SellRecordModel();
        $response = $mdlSellRecord->save_component($request['sell_record_code'], $request['type'], $request['data']);

        load_model('oms/SellRecordOptModel')->set_tb_log($request['sell_record_code']);
    }

    function edit_express_no(array &$request, array &$response, array &$app) {
        $m = new SellRecordModel();
        $response['sell_record_list'] = $m->get_record_list_by_ids(explode(',', $request['sell_record_code_list']));
        $response['express_arr'] = array();
        foreach ($response['sell_record_list'] as $record) {
            $response['express_arr'][$record['express_code']] = $record['express_code'];
        }
    }

    function edit_express_no_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $check_the_no = empty($request['check_the_no']) ? false : true;
        $m = new SellRecordOptModel();
        $err = '';
        foreach ($request['express_no'] as $id => $no) {
            $s = $m->edit_express_no($id, $no, $check_the_no);
            if ($s['status'] != 1) {
                $err .= " " . $s['message'];
            }
        }
        if (empty($err)) {
            $response = array('status' => 1, 'message' => '更新成功', 'data' => array());
        } else {
            $response = array('status' => -1, 'message' => $err, 'data' => array());
        }
    }

    function next_express_no(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $m = new SellRecordModel();

        if (!empty($request['check_the_no'])) {
            $s = $m->check_express_no($request['express_code'], $request['express_no']);
            if ($s == false) {
                return $response = array('status' => -1, 'message' => '快递单号不合法', 'data' => array());
            }
        }

        $data = array();
        $data[0] = $m->get_next_express_no($request['express_no'], $request['express_code']);
        for ($i = 1; $i < $request['rows']; $i++) {
            $data[] = $m->get_next_express_no($data[$i - 1], $request['express_code']);
        }

        $response = array('status' => 1, 'message' => '', 'data' => $data);
    }

    function edit_express(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->edit_express($request['sell_record_code'], array('express_code' => $request['express_code'], 'express_no' => $request['express_no']));
    }

    function edit_express_code(array &$request, array &$response, array &$app) {
        
    }
    //检验分销是否结算
    function fx_account (array $request) {
        $i = 1;
        foreach ($request['sell_record_code'] as $val) {
            $record = load_model('oms/SellRecordOptModel')->get_record_by_code($val);
            if($record['is_fx_settlement'] == '1' && ($record['is_fenxiao'] == '2'||$record['is_fenxiao'] == '1')) {
                $i++;
            }
        }       
        if($i>1) {
            $ret = array(
                'status' => -1,
                'data' => '',
                'message' => '已结算分销订单修改配送方式不会重新结算！',
            );          
        }else{
            $ret = array(
                'status' => 1,
                'data' => '',
                'message' => '',
            );
        }
        exit_json_response($ret);
    }

    function edit_store_code(array &$request, array &$response, array &$app) {
        
    }

    function cancel_all(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $err = "";
        foreach ($request['sell_record_id_list'] as $code) {
            $r = $mdl->opt_cancel($code, 1, 'direct');
            if ($r['status'] != '1')
                $err .= '作废失败(' . $code . '): ' . $r['message'] . "\n";
        }
        if (!empty($err)) {
            $response = array('status' => -1, 'message' => $err);
        } else {
            $response = array('status' => 1, 'message' => '作废成功');
        }
    }

    function cancel_all_one(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $err = "";
        $r = $mdl->opt_cancel($request['sell_record_code'], 1, 'direct');
        if ($r['status'] != '1') {
            $err .= '作废失败(' . $request['sell_record_code'] . '): ' . $r['message'] . "\n";
        }

        if (!empty($err)) {
            $response = array('status' => -1, 'message' => $err);
        } else {
            $response = array('status' => 1, 'message' => '作废成功');
        }
    }

    function edit_store_remark(array &$request, array &$response, array &$app) {
        
    }

    function edit_order_remark(array &$request, array &$response, array &$app) {
        
    }

    //批量替换商品
    function change_detail(array &$request, array &$response, array &$app) {
        
    }

    //批量替换商品--核心功能方法
    function sure_change_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/SellRecordModel')->sure_change_goods($request);
    }

    //批量改商品/删除及增加的方法
    function alter_detail(array &$request, array &$response, array &$app) {
        
    }

    function alter_detete_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/SellRecordModel')->alter_detete_detail($request);
    }

    function alter_add_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/SellRecordModel')->alter_add_detail($request);
    }

    function change_detail_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $err = '';
        $mdl = new SellRecordOptModel();
        $sql_store = "select count(1) from base_store where store_code='" . $request['store_code'] . "'";
        $check_store = CTX()->db->getOne($sql_store);
        if ($check_store == 0) {
            $err .='仓库不能空' . "<br>";
        } else {
            foreach ($request['sell_record_id_list'] as $id) {
                $sql = "select store_code,sell_record_code from oms_sell_record where sell_record_id = :sell_record_id";
                $record = ctx()->db->get_row($sql, array(':sell_record_id' => $id));
                $ret = $mdl->save_component($record['sell_record_code'], 'store_code', array('store_code' => $request['store_code']));
                if ($ret['status'] < 1) {
                    $err .= $id . ': ' . $ret['message'] . "<br>";
                    continue;
                }
                $old_store = get_store_name_by_code($record['store_code']);
                $new_store = get_store_name_by_code($request['store_code']);
                $mdl->add_action($record['sell_record_code'], "批量修改发货仓库", $old_store . "修改成" . $new_store);
                //$msg .= $id.": 成功\n";
            }
        }

        if (!empty($err)) {
            $response = array('status' => -1, 'message' => $err);
        } else {
            $response = array('status' => 1, 'message' => '更新成功');
        }
    }

    /**
     * 批量备注
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function edit_order_remark_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $error_msg = array();
        $mdl = new SellRecordOptModel();
        foreach ($request['sell_record_code_list'] as $id) {
            $sql = "select order_remark from oms_sell_record where sell_record_code = :sell_record_code";
            $record = ctx()->db->get_row($sql, array(':sell_record_code' => $id));
            $order_remark = '';
            $order_remark = $request['order_remark'];
            $ret = $mdl->save_component($id, 'order_remark', array('order_remark' => $order_remark));

            //$msg .= $id . ': ' . ($ret['status'] == 1 ? '成功' : $ret['message']) . "\n";
            if ($ret['status'] != 1) {
                $error_msg[] = array($id => $ret['message']);
            }
            if ($ret['status'] == 1) {
                $mdl->add_action($id, "批量备注", '订单备注："' . $record['order_remark'] . '" 修改成  "' . $order_remark . '"');
            }
        }
        if (!empty($error_msg)) {
            $sum_num = count($request['sell_record_code_list']);
            $error_num = count($error_msg);
            $success_num = $sum_num - $error_num;
            $message = "成功{$success_num}条，失败{$error_num}";
            $msg = $mdl->create_fail_file($error_msg);
            $ret = array(
                'status' => -1,
                'data' => '',
                'message' => $message . $msg,
            );
        } else {
            $ret = array(
                'status' => 1,
                'data' => '',
                'message' => '添加成功！',
            );
        }
        //$response = array('status' => 1, 'message' => $msg);
        exit_json_response($ret);
    }

    function edit_express_code_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $msg = '';
        $mdl = new SellRecordOptModel();
        //$okk = '';
        $err = '';
        foreach ($request['sell_record_code_list'] as $id) {
            $record = $mdl->get_record_by_code($id);
            $ret = $mdl->edit_express_code($id, $request['express_code'], 1);
            //$msg .= $id.': '.($ret['status'] == 1 ? '更新成功' : $ret['message'])."\n";
            if ($ret['status'] == 1) {
                $old_store = get_express_name_by_code($record['express_code']);
                $new_store = get_express_name_by_code($request['express_code']);
                $mdl->add_action($id, "批量修改配送方式", $old_store . "修改成" . $new_store);
                //$okk .= "订单: ".$id.': 更新成功<br>';
            } else {
                $err .= "订单: " . $id . ': 更新失败(' . $ret['message'] . ")<br>";
            }
            $m = new SellRecordModel();
            $m->update_express($id);
        }

        //$response = array('status'=>1, 'message'=>$msg);
        if (!empty($err)) {
            $response = array('status' => -1, 'message' => $err);
        } else {
            $response = array('status' => 1, 'message' => '更新成功');
        }
    }

    function edit_store_code_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $err = '';
        $mdl = new SellRecordOptModel();
        $sql_store = "select count(1) from base_store where store_code='" . $request['store_code'] . "'";
        $check_store = CTX()->db->getOne($sql_store);
        if ($check_store == 0) {
            $err .='仓库不能空' . "<br>";
        } else {
            foreach ($request['sell_record_id_list'] as $id) {
                $sql = "select store_code,sell_record_code,order_status from oms_sell_record where sell_record_id = :sell_record_id";
                $record = ctx()->db->get_row($sql, array(':sell_record_id' => $id));
                if ($record['order_status'] > 0) {
                    $err .= $record['sell_record_code'] . ': ' . '订单状态不是未确认状态！' . "<br>";
                    continue;
                }
                $ret = $mdl->save_component($record['sell_record_code'], 'store_code', array('store_code' => $request['store_code']));
                if ($ret['status'] < 1) {
                    $err .= $record['sell_record_code'] . ': ' . $ret['message'] . "<br>";
                    continue;
                }
                $old_store = get_store_name_by_code($record['store_code']);
                $new_store = get_store_name_by_code($request['store_code']);
                $mdl->add_action($record['sell_record_code'], "批量修改发货仓库", $old_store . "修改成" . $new_store);
                //$msg .= $id.": 成功\n";
            }
        }

        if (!empty($err)) {
            $response = array('status' => -1, 'message' => $err);
        } else {
            $response = array('status' => 1, 'message' => '更新成功');
        }
    }

    function edit_store_remark_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $msg = '';
        $mdl = new SellRecordOptModel();
        foreach ($request['sell_record_code_list'] as $id) {
            $sql = "select store_remark from oms_sell_record where sell_record_code = :sell_record_code";
            $record = ctx()->db->get_row($sql, array(':sell_record_code' => $id));
            $ret = $mdl->save_component($id, 'store_remark', array('store_remark' => $request['store_remark']));

            $msg .= $id . ': ' . ($ret['status'] == 1 ? '成功' : $ret['message']) . "\n";
            if ($ret['status'] == 1) {
                $mdl->add_action($id, "批量修改仓库留言", '给仓库留言："' . $record['store_remark'] . '" 修改成  "' . $request['store_remark'] . '"');
            }
        }

        $response = array('status' => 1, 'message' => $msg);
    }

    function pay(array &$request, array &$response, array &$app) {
        $mdl = new SellRecordModel();
        $response['record'] = $mdl->get_record_by_code($request['sell_record_code']);
    }

    function opt_pay(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_pay($request['sell_record_code'], $request['paid_money']);
    }

    function send(array &$request, array &$response, array &$app) {
        $mdl = new SellRecordModel();
        $response['record'] = $mdl->get_record_by_code($request['sell_record_code']);
    }

    function opt_send(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_send($request['sell_record_code'], $request);
    }

    //详情操作
    function opt(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdlSellRecord = new SellRecordOptModel();
        $func = $request['type'];
        $is_fx = isset($request['fx'])?1:0;
        
        if ($func == 'opt_pay') {
            $response = $mdlSellRecord->$func($request['sell_record_code'], $request['paid_money']);
        } elseif ($func == 'opt_cancel') {
            $response = $mdlSellRecord->$func($request['sell_record_code'], '', 'direct');
        } elseif ($func == 'opt_unproblem') {
            $response = $mdlSellRecord->$func($request['sell_record_code'],$request = array(),$is_fx);
        }elseif($func == 'opt_settlement'){
            $func = 'opt_settlement_new';
            $response = $mdlSellRecord->$func($request['sell_record_code'],$request);
        }elseif($func == 'opt_unsettlement'){
            $response = $mdlSellRecord->$func($request['sell_record_code'],$request);
        }else{
             $response = $mdlSellRecord->$func($request['sell_record_code']);
        }
        //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';die;
    }

    function opt_batch_task(array &$request, array &$response, array &$app) {
        $response = load_model("oms/SellRecordModel")->opt_batch_task($request);
        $response['status'] = 1;
    }

    /**
     * 分销批量结算
     */
    function opt_fx_settlement(array &$request, array &$response, array &$app) {
        $this->opt_batch($request, $response, $app);
    }

    /**
     * 订单详情操作按钮
     * 不能对此方法进行权限控制，另写方法调用此方法
     */
    function opt_batch(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $msgSuccess = '';
        $msgFaild = '';
        $msg_faild_arr = array();
        $sell_record_code_list = (isset($request['sell_record_code_list']) && is_array($request['sell_record_code_list'])) ? $request['sell_record_code_list'] : explode(',', $request['sell_record_code_list']);
        $sell_record_code_list = array_unique($sell_record_code_list);
        $total_c = count($sell_record_code_list);
        $mdlSellRecord = new SellRecordOptModel();
        foreach ($sell_record_code_list as $code) {
            if (empty($code))
                continue;
            if ($request['type'] != 'opt_lock' && $request['type'] != 'opt_unlock') {
                //执行前先锁定
                $unlock = false;
                $record = $mdlSellRecord->get_record_by_code($code, 'is_lock,payable_money');
                if ($record['is_lock'] == '0') {
                    $lock_ret = $mdlSellRecord->opt_lock($code);
                    if ($lock_ret['status'] != '1' && $request['type'] !='opt_settlement') {
                        $msgFaild .= $code . $lock_ret['message'] . ',';
                        continue;
                    } elseif(($lock_ret['status'] != '1' && $request['type'] =='opt_fx_settlement')){
                        $msg_faild_arr['code'] =$lock_ret['message'];
                    }else {
                        $unlock = true;
                    }
                }
            }

            //执行时
            $func = $request['type'];
            $ret = array();
            if ($func == 'opt_pay') {
                $ret = $mdlSellRecord->$func($code, $record['payable_money'], $request);
            } elseif ($func =='opt_unproblem'){
                $is_fx = 0;
                $ret = $mdlSellRecord->$func($code, $request,$is_fx);
            }else{
                $ret = $mdlSellRecord->$func($code, $request);
            }
//            $record = $mdlSellRecord->get_record_by_id($id);
//            $code = isset($record['record_code']) ? $record['record_code'] : '';
            $msg = '';
            if ($ret['status'] == '1') {
                $msg = '执行成功';
            } elseif(($ret['status'] != '1' && $request['type'] =='opt_settlement')){
                $msg_faild_arr[] =array($code=>$ret['message']);
            }else {
                $msgFaild .= $code . '  ' . $ret['message'] . ',<br/>';
            }
            //执行后要解锁（执行前是锁定的执行后不需解锁）
            if ($request['type'] != 'opt_lock' && $request['type'] != 'opt_unlock') {
                if ($unlock) {
                    $lock_ret = $mdlSellRecord->opt_unlock($code);
                }
            }
        }
        if(!empty($msg_faild_arr)){
            $error_c = count($msg_faild_arr);
            $suc_c = $total_c-$error_c;
            $file_str = load_model('oms/SellRecordOptModel')->create_fail_file($msg_faild_arr);
            $msg .= '成功'.$suc_c.'条，失败'.$error_c.'条;'.$file_str;
        }elseif (!empty($msgFaild)) {
            $msg .= sprintf("订单:<br/> %s", rtrim($msgFaild, ','));
        }

        $response = array('status' => 1, 'message' => $msg);
    }

    function opt_confirm(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/SellRecordOptModel')->new_opt_confirm($request['sell_record_code_list']);
    }

    //解挂定时器
    function cli_unpending(array &$request, array &$response, array &$app) {
        $mdlSellRecord = new SellRecordOptModel();
        $mdlSellRecord->cli_unpending();
        $response['status'] = 1;
    }

    //读取详情按钮权限
    function btn_check(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $is_fx = isset($request['is_fx'])?$request['is_fx']:0;
        $response = array();
        $mdlSellRecord = new SellRecordOptModel();
        $next_opt = $mdlSellRecord->btn_nav($request['sell_record_code']);
        $response['next_opt'] = $next_opt['data'];

        $record = $mdlSellRecord->get_record_by_code($request['sell_record_code']);
        $detail = $mdlSellRecord->get_detail_by_sell_record_code($request['sell_record_code']);
        $sys_user = $mdlSellRecord->sys_user();
        foreach ($request['fields'] as $key => &$status) {
            $func = $key . '_check';
            if ($func == 'opt_send_check') {
                $s = $mdlSellRecord->$func($record, $detail, $sys_user, 'handwork_send');
            } elseif ($func == 'opt_unproblem_check') {//返回正常单
                $s = $mdlSellRecord->$func($record, $detail, $sys_user,$is_fx);
            }else {
                
                $s = $mdlSellRecord->$func($record, $detail, $sys_user);
            }
            $response['comp'][$key]['status'] = $s['status'] == 1 ? 1 : 0;
            $response['comp'][$key]['message'] = (string) $s['message'];
        }
        $ret = $mdlSellRecord->opt_problem_check($record, $detail, $sys_user);

//      echo '<hr/>$retxx<xmp>'.var_export($ret,true).'</xmp>';die;
    }

    //规格
    function spec_list_by_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordModel();
        $response = $mdl->spec_list_by_goods($request['sell_record_code'], $request['goods_code']);
    }

    //新增明细
    function opt_new_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_new_detail($request['sell_record_code'], $request['sku'], $request['num'], $request['sum_money']);

        load_model('oms/SellRecordOptModel')->set_tb_log($request['sell_record_code']);
    }

    //新增明细
    function opt_new_multi_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_new_multi_detail($request);
        $m = new SellRecordModel();
        $response['record'] = $m->get_record_by_code($request['sell_record_code']);

        load_model('oms/SellRecordOptModel')->set_tb_log($request['sell_record_code']);
    }

    //保存明细
    function opt_save_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $res = load_model('oms/SellRecordModel')->check_sellrecord_status($request['sell_record_code']);
        if ($res) {
            $response['status'] = -1;
            $response['message'] = '订单状态发生变化！';
            exit_json_response($response);
        }

        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_save_detail($request['sell_record_code'], $request['sell_record_detail_id'], $request['num'], $request['avg_money'], $request['deal_code'], $request['fx_amount']);
        $m = new SellRecordModel();
        $response['record'] = $m->get_record_by_code($request['sell_record_code']);

        load_model('oms/SellRecordOptModel')->set_tb_log($request['sell_record_code']);
    }

    //删除明细
    function opt_delete_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_delete_detail($request['sell_record_code'], $request['sell_record_detail_id']);

        load_model('oms/SellRecordOptModel')->set_tb_log($request['sell_record_code']);
    }

    function download(array &$request, array &$response, array &$app) {
        $response['arr_shop'] = load_model('base/ShopModel')->get_list();
    }

    function download_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';

        $request['created_min'] = $request['created_min'] . ' 00:00:00';
        $request['created_max'] = $request['created_max'] . ' 23:59:59';

        $m = new TaobaoRecordModel();
        $response['down'] = $m->download_cloud($request);

        //TODO: 转单
        $response['tran'] = $m->transfer($request);
    }

    /**
     * 标记订单已打印
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function mark_sell_record_print(array & $request, array & $response, array & $app) {
        $sell_record_codes = $request['record_ids'];
        if (is_array($sell_record_codes)) {
            $sell_record_code_arr = $sell_record_codes;
        } else {
            $sell_record_code_arr = explode(',', $sell_record_codes);
        }
        foreach ($sell_record_code_arr as $record_id) {
            CTX()->db->update("oms_sell_record", array("is_print_sellrecord" => 1), array('sell_record_code' => $record_id));
        }

        $app['fmt'] = 'json';
    }

    //发货回写
    function delivery_send(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $params = array();
        $params['record_code'] = $request['record_code'];
        $params['user'] = CTX()->get_session('user_id');
        $response = load_model('oms/order_shipping/OrderShippingMgrModel')->send('OrderShippingTaobaoModel', $params);
    }

    //刷新商家备注
    function seller_remark_flush(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $params = array();
        $record_code = $request['record_code'];
        $response = load_model('oms/SellRecordModel')->seller_remark_flush('1506030000786');
        //echo '<hr/>response<xmp>'.var_export($response,true).'</xmp>';
    }

    //上传商家备注
    function seller_remark_upload(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $params = array();
        $record_code = $request['record_code'];
        $seller_remark = $request['seller_remark'];
        $response = load_model('oms/SellRecordModel')->seller_remark_upload($record_code, $seller_remark);
        //echo '<hr/>response<xmp>'.var_export($response,true).'</xmp>';
    }

    //刷新客户留言
    function buyer_remark_flush(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $params = array();
        $record_code = $request['record_code'];
        $response = load_model('oms/SellRecordModel')->seller_remark_flush($record_code);
        //echo '<hr/>response<xmp>'.var_export($response,true).'</xmp>';
    }

    //实际批次锁定情况
    function lock_detail(array & $request, array & $response, array & $app) {
        $params['p_detail_id'] = $request['sell_record_detail_id'];
        $params['sku'] = $request['sku'];
        $params['occupy_type'] = '1';
        $response = load_model('oms/SellRecordLofModel')->get_list_by_params($params, true);
        $this->get_spec_rename($response);
    }

    //问题订单列表
    function question_list(array & $request, array & $response, array & $app) {
        $response['problem_type'] = ds_get_select('problem_type');
        foreach ($response['problem_type'] as $key => &$value) {
            $value['num'] = load_model("oms/SellRecordModel")->get_count_by_problem_type($value['question_label_code']);
        }
        $response['operate']['return_normal'] = "?app_act=oms/sell_record/return_normal&app_fmt=json";
        $this->get_spec_rename($response);
    }

    //通过record_code获取子订单详情
    function get_detail_list_by_sell_record_code(array & $request, array & $response, array & $app) {
        $data = load_model("oms/SellRecordModel")->get_detail_by_sell_record_code($request['sell_record_code'], 1);
        $response = array('rows' => $data);
    }
    //通过record_code获取子订单详情(退单)
    function get_detail_list_by_sell_return_code(array & $request, array & $response, array & $app) {
        $data = load_model("oms/SellRecordModel")->get_return_detail_by_sell_return_code($request['sell_return_code'],$request['sell_record_code'], 0);
        $response = array('rows' => $data);
    }

    function get_detail_list_by_tid(array & $request, array & $response, array & $app) {
        $data = load_model('oms/ApiOrderDetailModel')->get_by_field_all('tid', $request['tid']);
        $response = array('rows' => $data);
    }
    //通过refund_id获取子订单详情(平台退单)
    function get_detail_list_by_tid_refund(array & $request, array & $response, array & $app) {
        $data = load_model('oms/ApiOrderDetailModel')->get_by_field_all_refund('refund_id', $request['refund_id']);
        $response = array('rows' => $data);
    }

    //缺货订单列表
    function short_list(array & $request, array & $response, array & $app) {
        $response['operate']['remove_short'] = "?app_act=oms/sell_record/remove_short&app_fmt=json";
        $response['operate']['splite'] = "?app_act=oms/sell_record/split&app_fmt=json";
        $this->get_spec_rename($response);
    }

    //合并订单列表
    function merge_list(array & $request, array & $response, array & $app) {
        
    }

    //已发货订单列表
    function shipped_list(array & $request, array & $response, array & $app) {
        $arr = array('print_delivery_record_template', 'clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $kdniao = load_model('sys/SysParamsModel')->get_val_by_code('kdniao_enable');
        $response['edit_express_status'] = load_model('sys/PrivilegeModel')->check_priv('oms/shipped_list/edit_express') ? 1 : 0;
        $response['edit_express_status_new'] = load_model('sys/PrivilegeModel')->check_priv('oms/shipped_list/edit_express_new') ? 1 : 0;
        $response['kdniao_enable'] = $kdniao['kdniao_enable'];
        $response['print_delivery_record_template'] = isset($ret_arr['print_delivery_record_template']) ? $ret_arr['print_delivery_record_template'] : 0;
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
    }

    //已发货订单详情
    function get_detail_by_sell_record_code(array &$request, array &$response, array &$app) {
        $sell_record_code = $request['sell_record_code'];
        $result = load_model("oms/SellRecordModel")->get_row(array("sell_record_code" => $sell_record_code));
        $response['data'] = $result['data'];
        $response['detail'] = load_model("oms/SellRecordModel")->get_detail_by_sell_record_code($sell_record_code);
        $this->get_spec_rename($response);
    }

    //缺货单解除缺货
    function remove_short(array &$request, array &$response, array &$app) {
        $response = load_model("oms/SellRecordModel")->remove_short($request['sell_record_code'], 0, @$request['force']);
    }

    //订单拆分
    function split(array &$request, array &$response, array &$app) {
        $return = array();
        switch ($request['mode']) {
            case '0':$return = load_model("oms/OrderSplitModel")->split_a_key();
                break;
            case '1':$return = load_model("oms/OrderSplitModel")->split_group($request['sell_record_code']);
                break;
            case '2':$return = load_model("oms/OrderSplitModel")->split_short($request['sell_record_code']);
                break;
            case '3':$return = load_model("oms/OrderSplitModel")->split_presale($request['sell_record_code']);
                break;
            default :$return = array("status" => 1, "data" => '', "message" => '操作失败');
        }
        $response = $return;
    }

    //拆单弹出页面
    function split_order(array &$request, array &$response, array &$app) {
        $record = load_model('oms/SellRecordModel')->get_record_by_code($request['sell_record_code']);
        $detail = load_model('oms/SellRecordModel')->get_detail_list_by_code($request['sell_record_code']);
        foreach ($detail as $key => &$val) {



            $key_arr = array('goods_code', 'barcode', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);

            $val = array_merge($val, $sku_info);
        }
        $response['record'] = $record;
        $response['detail_list'] = $detail;
        $response['count'] = count($detail);
    }

    //拆单操作
    function opt_split_order(array &$request, array &$response, array &$app) {
        $response = load_model("oms/OrderSplitModel")->order_split($request);
    }

    //订单复制
    function opt_copy(array &$request, array &$response, array &$app) {
        $ret = load_model("oms/SellRecordOptModel")->opt_copy($request['sell_record_code']);
        $response = $ret;
    }
    //订单补单
    function opt_replenish(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';
        $ret = load_model("oms/SellRecordOptModel")->opt_replenish($request['sell_record_code']);
        $response = $ret;
    }

    //生成退单界面
    function create_return_form(array &$request, array &$response, array &$app) {
        $sell_record_code = $request['sell_record_code'];
        $response['detail_list'] = array();
        $data = oms_tb_val('base_return_reason', 'return_reason_code', array('return_reason_name'=>'7天无理由退换货'));
        $response['record'] = load_model("oms/SellRecordModel")->get_record_by_code($sell_record_code);
        $response['reason_code'] = $data;
        if (!empty($response['record'])) {
            if (($response['record']['shipping_status'] == '4')) {
                if ($response['record']['pay_status'] == '0') {
                    $response['record']['return_type'] = '1';
                } else {
                    $response['record']['return_type'] = '2';
                }
            }
        }
        $ret = load_model('oms/SellReturnOptModel')->get_return_store_code($response['record']['shop_code'], $request['sell_record_code']);
        if ($ret['status'] > 0) {
            $response['record']['return_store_code'] = $ret['data'];
        } else {
            $response['record']['return_store_code'] = $response['record']['store_code'];
        }



        $response['detail_list'] = load_model("oms/SellRecordModel")->get_return_detail_by_sell_record_code($sell_record_code, 1, 1);
        $is_allowed_exceed = load_model('sys/SysParamsModel')->get_val_by_code(array('is_allowed_exceed'));
        $response['is_allowed_exceed'] = isset($is_allowed_exceed['is_allowed_exceed']) ? $is_allowed_exceed['is_allowed_exceed'] : 0;

        $ret = load_model('oms/SellReturnOptModel')->get_mx_return_info($sell_record_code);
        $return_mx = $ret['data'];
        foreach ($response['detail_list'] as $ks => $row) {
            $_find_row = isset($return_mx[$ks]) ? $return_mx[$ks] : null;
            $return_num = $_find_row['return_num'];
            if (empty($_find_row)) {
                //已退数量
                $response['detail_list'][$ks]['return_num'] = 0;
                //可退数量
                $response['detail_list'][$ks]['returnable_num'] = 0;
            } else {
                $returnable_num = $row['num'] - $return_num;
                $returnable_num = $returnable_num > 0 ? $returnable_num : 0;
                $response['detail_list'][$ks]['return_num'] = $return_num;
                $response['detail_list'][$ks]['returnable_num'] = $returnable_num;
            }
        }



        $this->get_spec_rename($response);
    }

    //生成退货单
    function create_return(array &$request, array &$response, array &$app) {
        $sell_record_code = $request['sell_record_code'];
        $params_info_mx = array();

        if (isset($request['mx'])) {
            foreach ($request['mx'] as $value) {
                $temp['deal_code'] = $value['deal_code'];
                $temp['sku'] = $value['sku'];
                $temp['return_num'] = $value['return_num'];
                $temp['avg_money'] = $value['avg_money'];
                $key = $value['deal_code'] . '_' . $value['sku'];
                if (!isset($params_info_mx[$key])) {
                    $params_info_mx[$key] = $temp;
                } else {
                    $params_info_mx[$key]['avg_money'] += $value['avg_money'];
                    $params_info_mx[$key]['return_num'] += $value['return_num'];
                }
            }
        }

        $params_info = array();
        $params_info['mx'] = $params_info_mx;
//        $params_info['store_code'] = $request['return_store_code'];
        $params_info['return_type'] = $request['return_type'];
        $params_info['adjust_money'] = isset($request['adjust_money']) ? $request['adjust_money'] : 0;
        $params_info['seller_express_money'] = isset($request['seller_express_money']) ? $request['seller_express_money'] : 0;
        $params_info['compensate_money'] = isset($request['compensate_money']) ? $request['compensate_money'] : 0;
        $params_info['return_reason_code'] = isset($request['return_reason_code']) ? $request['return_reason_code'] : '';
        $params_info['return_remark'] = isset($request['return_remark']) ? $request['return_remark'] : '';
        $params_info['return_buyer_memo'] = isset($request['return_buyer_memo']) ? $request['return_buyer_memo'] : '';
        $params_info['return_pay_code'] = isset($request['return_pay_code']) ? $request['return_pay_code'] : '';
        $params_info['return_express_code'] = isset($request['return_express_code']) ? $request['return_express_code'] : '';
        $params_info['return_express_no'] = isset($request['return_express_no']) ? $request['return_express_no'] : '';
        $params_info['is_compensate'] = isset($request['is_compensate']) ? $request['is_compensate'] : 0;
        $params_info['is_package_out_stock'] = isset($request['is_package_out_stock']) ? $request['is_package_out_stock'] : 0;
        $params_info['sell_record_checkpay_status'] = isset($request['sell_record_checkpay_status']) ? $request['sell_record_checkpay_status'] : 'unpay';

        require_model('oms/SellReturnOptModel');
        $obj = new SellReturnOptModel();
        $response = $obj->create_return($params_info, $sell_record_code, $request['return_type'], $request['return_store_code']);
    }

    function pending_list(array &$request, array &$response, array &$app) {
        $this->get_spec_rename($response);
    }

    function pending(array &$request, array &$response, array &$app) {
        if (isset($request['sell_record_code_list'])) {
            $request['sell_record_code'] = json_encode(explode(',', $request['sell_record_code_list']));
        }
    }

    function opt_pending(array &$request, array &$response, array &$app) {
        $ret = array();
        if (is_array($request['sell_record_code'])) {
            $msg = '';
            foreach ($request['sell_record_code'] as $code) {
                //执行前先锁定
                $unlock = false;
                $mdlSellRecord = new SellRecordOptModel();
                $record = $mdlSellRecord->get_record_by_code($code);
                if ($record['is_lock'] == '0') {
                    $lock_ret = $mdlSellRecord->opt_lock($code);
                    if ($lock_ret['status'] != '1') {
                        $msgFaild = $code . ',';
                        $msg.=$code . ': ' . $lock_ret['message'] . "<br />";
                        continue;
                    } else {
                        $unlock = true;
                    }
                }
                $ret_sub = load_model("oms/SellRecordOptModel")->opt_pending($code, $request['is_pending_code'], $request['is_pending_memo'], $request['is_pending_time'], $request);
                //执行后要解锁（执行前是锁定的执行后不需解锁）
                if ($unlock) {
                    $lock_ret = $mdlSellRecord->opt_unlock($code);
                }

                //只要有$ret_sub['status'] !=1的，返回status就不为1，防止被覆盖
                if ($ret_sub['status'] != 1) {
                    $status = $ret_sub['status'];
                }
                if (isset($msgFaild)) {
                    $status = -1;
                }

                $msg .= $code . ': ' . ($ret_sub['status'] == 1 ? '成功' : $ret_sub['message']) . "<br />";
            }

            if (isset($status)) {
                $ret = array('status' => $status, 'message' => $msg);
            } else {
                $ret = array('status' => $ret_sub['status'], 'message' => $msg);
            }
        } else {
            $ret = load_model("oms/SellRecordOptModel")->opt_pending($request['sell_record_code'], $request['is_pending_code'], $request['is_pending_memo'], $request['is_pending_time'], $request);
        }

        $response = $ret;
    }

    //订单打标
    function label(array &$request, array &$response, array &$app) {
        if (isset($request['sell_record_code_list'])) {
            $request['sell_record_code'] = json_encode(explode(',', $request['sell_record_code_list']));
        }
    }

    function opt_label(array &$request, array &$response, array &$app) {
        $ret = array();
        $sell_record_arr = is_array($request['sell_record_code']) ? $request['sell_record_code'] : array($request['sell_record_code']);
        $msg = '';
        foreach ($sell_record_arr as $code) {
            $ret_sub = load_model("oms/SellRecordOptModel")->opt_label($code, $request['label_code'], $request);
            $msg .= $code . ': ' . ($ret_sub['status'] == 1 ? '成功' : $ret_sub['message']) . "<br />";
        }
        $ret = array('status' => $ret_sub['status'], 'message' => $msg);
        $response = $ret;
    }

    //批量打标
    function opt_batch_label(array &$request, array &$response, array &$app) {
        $ret = array();
        $sell_record_arr = is_array($request['sell_record_code']) ? $request['sell_record_code'] : array($request['sell_record_code']);
        $msg = '';
        foreach ($sell_record_arr as $code) {
            $ret_sub = load_model("oms/SellRecordOptModel")->opt_batch_label($code, $request['label_code'], $request);
            $msg .= $code . ': ' . ($ret_sub['status'] == 1 ? '成功' : $ret_sub['message']) . "<br />";
        }
        $ret = array('status' => $ret_sub['status'], 'message' => $msg);
        $response = $ret;
    }

    function opt_unpending(array &$request, array &$response, array &$app) {
        $ret = load_model("oms/SellRecordOptModel")->opt_unpending($request['sell_record_code']);
        $response = $ret;
    }

    function problem(array &$request, array &$response, array &$app) {
        
    }

    function opt_problem(array &$request, array &$response, array &$app) {
        $ret = load_model("oms/SellRecordOptModel")->opt_problem($request['sell_record_code'], $request['problem_code'], $request);
        $response = $ret;
    }

    function opt_unproblem(array &$request, array &$response, array &$app) {
        $ret = load_model("oms/SellRecordOptModel")->opt_unproblem($request['sell_record_code']);
        $response = $ret;
    }

    private function get_spec_rename(array &$response) {
        //spec别名
        $arr = array('goods_spec1', 'goods_spec2');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
    }

    function problem_list(array &$request, array &$response, array &$app) {
        $response = load_model("oms/SellRecordModel")->get_record_by_code($request['sell_record_code']);
    }

    function a_key_confirm(array &$request, array &$response, array &$app) {
        //标识此任务类型的唯一CODE
        /*
          $task_data['code'] = 'oms_a_key_confirm';
          $task_data['start_time'] = time();

          $request['app_fmt'] = 'json';
          $request['app_act'] = 'oms/sell_record/start_confirm';
          $request['id'] = 100;

          $task_data['request'] = $request;

          $ret = load_model('common/TaskModel')->save_task($task_data);

          $task_id = load_model('common/TaskModel')-> get_task_id ($request);

          $response = load_model('common/TaskModel')->save_log($task_id, "开始一键确认"); */

        $response = load_model('oms/SellRecordModel')->a_key_confirm_create_task();
    }

    function start_confirm(array &$request, array &$response, array &$app) {
        $response = load_model("oms/SellRecordModel")->a_key_confirm($request);
    }

    function get_deliver_record_ids(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordModel();
        $response = $mdl->get_deliver_record_ids($request['record_ids']);
    }

    function import(array &$request, array &$response, array &$app) {
        
    }

    function import_action(array &$request, array &$response, array &$app) {

        $app['fmt'] = 'json';
        //var_dump($request);
        require_once ROOT_PATH . 'lib/PHPExcel.php';
        $excelType = pathinfo($request['url'], PATHINFO_EXTENSION) == 'xlsx' ? 'Excel2007' : 'Excel5';
        $objReader = PHPExcel_IOFactory::createReader($excelType);
        $objPHPExcel = $objReader->load($request['url']);
        $arrExcel = $objPHPExcel->getActiveSheet()->toArray();
        //var_dump($arrExcel);
        //移除第一行
        array_shift($arrExcel);

        $success = 0;
        $fail_num = 0;
        //faild = '';
        $faild = array();
        $m = new SellRecordModel();
        foreach ($arrExcel as $k => $v) {
            if (empty($v[0]) && empty($v[1]) && empty($v[2])) {
                continue;
            }
            $r = $m->shipped_import($v[0], $v[1], $v[2]);
            if ($r['status'] == '1') {
                $success++;
            } else {
                //faild .= sprintf("%s,%s,%s,%s\n<br>", $v[0], $v[1], $v[2], $r['message']);
                $faild[$v[0]] = $r['message'];
                $fail_num ++;
            }
        }
//        if ($success > 0 && $faild == '') {
//            $status = '1';
//        } else {
//            $status = '-1';
        //       }
        $message = '导入成功：' . $success . '条';
        $status = 1;
        if (!empty($faild)) {
            $status = -1;
            $message .=',' . '导入失败:' . $fail_num . '条';
            $fail_top = array('变更快递', '错误信息');
            $filename = 'sell_record_shipped';
            $file_name = $this->create_import_fail_files($faild, $fail_top, $filename);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $response = array('status' => $status, 'success' => $success, 'message' => $message);
    }

    function create_import_fail_files($msg, $fail_top, $filename) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg as $key => $val) {
            $val_data = array($key, $val);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5($filename . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function import_upload(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $files = array();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/";
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $fileInput = 'fileData';
        $dir = ROOT_PATH . 'webefast/uploads/';
        $type = $_POST['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        foreach ($files_name_arr as $k => $v) {
            $pic = $_FILES[$v];
            $isExceedSize = $pic['size'] > 500000;
            if (!$isExceedSize) {
                if (file_exists($dir . $pic['name'])) {
                    @unlink($dir . $pic['name']);
                }
                // 解决中文文件名乱码问题
                //$pic['name'] = iconv('UTF-8', 'GBK', $pic['name']);
                $result = move_uploaded_file($pic['tmp_name'], $dir . $pic['name']);
                $files[$k] = $url . $dir . $pic['name'];
            }
        }
        if (!$isExceedSize && $result) {
            $response = array(
                'status' => 1,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'url' => $dir . $_FILES[$fileInput]['name']
            );
        } else if ($isExceedSize) {
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "文件大小超过500kb！"
            );
        } else {
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！" . $result
            );
        }
        set_uplaod($request, $response, $app);
    }

    function import_trade(array &$request, array &$response, array &$app) {
        $sql = "select * from base_shop where is_active = 1 and sale_channel_code = 'xiachufang'";
        $ret = ctx()->db->get_row($sql);
        $response['xiachufang'] = !empty($ret) ? 1 : 0;
        $response['power']['fx_sell_record_import'] = load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/fx_sell_record_import');
    }

    function import_trade_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        if ($ret['status'] > 0) {
            $file = $ret['url'];
        } else {
            echo $ret['msg'];
            die;
        }

        if ($request['radio_record'] == 1) {
            $ret_msg = load_model('oms/SellRecordModel')->import_trade_action($file);
        } else {
            $ret_msg = load_model('oms/SellRecordModel')->fx_import_trade_action($file);
        }
        header("Content-Type: text/html; charset=UTF-8");
        echo $ret_msg;
        die;
    }

    function history_import_trade(array &$request, array &$response, array &$app) {
        $response['power']['fx_deliver_record_import'] = load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/fx_deliver_record_import');
    }

    function history_import_trade_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        if ($ret['status'] > 0) {
            $file = $ret['url'];
        } else {
            echo $ret['msg'];
            die;
        }
        if ($request['radio_record'] == 1) {
            $ret_msg = load_model('oms/SellRecordHistoryImport')->import_trade_action($file);
        } else {
            $ret_msg = load_model('oms/SellRecordHistoryImport')->fx_import_trade_action($file);
        }
        header("Content-Type: text/html; charset=UTF-8");
        echo $ret_msg;
        die;
    }

    function import_xcf_trade_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        if ($ret['status'] > 0) {
            $file = $ret['url'];
        } else {
            echo $ret['msg'];
            die;
        }

        $ret_msg = load_model('oms/SellRecordModel')->import_xcf_trade_action($file);
        header("Content-Type: text/html; charset=UTF-8");
        echo $ret_msg;
        die;
    }

    public function import_tpl(array & $request, array & $response, array & $app) {
        //获取url路径
        $path = APP_PATH . 'data/excelDefault/sell_record_shipped.xlsx';
        header("Content-type:application/vnd.ms-excel;charset=utf8");
        header("Content-Disposition:attachment; filename=sell_record_shipped.xlsx");
        echo file_get_contents($path);
        die();
    }

    public function auto_confirm(array &$request, array &$response, array &$app) {
        $response = load_model('oms/SellRecordOptModel')->auto_confirm();
    }

    public function auto_notice(array &$request, array &$response, array &$app) {
        if ($request['type'] == 'batch') {
            $response = load_model('oms/SellRecordOptModel')->confirmed_auto_notice($request['code']);
        } else {
            $response = load_model('oms/SellRecordOptModel')->auto_notice();
        }
    }

    //自动合并定时器
    function cli_combine(array &$request, array &$response, array &$app) {
        load_model('oms/OrderCombineModel')->cli_combine();
        $response['status'] = 1;
        die;
    }

    //订单加急，只针对未确认的订单
    function set_rush(array &$request, array &$response, array &$app) {
        $response = load_model('oms/SellRecordOptModel')->set_rush($request['sell_record_code']);
        //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';die;
    }

    //自动解除缺货定时器
    function cli_batch_remove_short(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        load_model('oms/SellRecordModel')->cli_batch_remove_short();
        $response['status'] = 1;
    }

    function import_fenxiao_trade(array &$request, array &$response, array &$app) {
        
    }

    function import_fenxiao_trade_action(array &$request, array &$response, array &$app) {
        set_uplaod($request, $response, $app);
        $response = load_model('oms/SellRecordModel')->import_fenxiao_trade_action($_FILES);
    }

    //订单详情商品改款
    function add_change_goods_view(array &$request, array &$response, array &$app) {
        $response['cur_goods']['goods_code'] = isset($request['goods_code']) ? $request['goods_code'] : '';
        $response['cur_goods']['sell_record_code'] = isset($request['sell_record_code']) ? $request['sell_record_code'] : '';
        $response['cur_goods']['deal_code'] = isset($request['deal_code']) ? $request['deal_code'] : '';
        $response['cur_goods']['sku'] = isset($request['sku']) ? $request['sku'] : '';
        $response['cur_goods']['num'] = isset($request['num']) ? $request['num'] : '';
        $response['cur_goods']['barcode'] = isset($request['barcode']) ? $request['barcode'] : '';
        $response['cur_goods']['avg_money'] = isset($request['avg_money']) ? $request['avg_money'] : '';
        $response['cur_goods']['lof_status'] = isset($request['lof_status']) ? $request['lof_status'] : '';
        $response['cur_goods']['sell_record_detail_id'] = isset($request['sell_record_detail_id']) ? $request['sell_record_detail_id'] : '';
        $response['cur_goods']['spec1_name'] = isset($request['spec1_name']) ? $request['spec1_name'] : '';
        $response['cur_goods']['spec2_name'] = isset($request['spec2_name']) ? $request['spec2_name'] : '';
        $response['cur_goods']['trade_price'] = isset($request['trade_price']) && !empty($request['trade_price']) ? $request['trade_price'] : '';
        $response['cur_goods']['fx_amount'] = isset($request['fx_amount']) && !empty($request['fx_amount']) ? $request['fx_amount'] : '';
    }

    //订单详情商品改款页检索
    function search_change_goods(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $data = load_model('oms/SellRecordModel')->get_change_goods($request);
        $response['rows'] = $data;
    }

    //添加商品明细
    function opt_change_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $res = load_model('oms/SellRecordModel')->check_sellrecord_status($request['sell_record_code']);
        if ($res) {
            $response['status'] = -1;
            $response['message'] = '订单状态发生变化！';
            exit_json_response($response);
        }
        $mdl = new SellRecordOptModel();
        //删除当前明细
        $response1 = $mdl->opt_delete_detail($request['sell_record_code'], $request['sell_record_detail_id']);
        //添加明细
        $detail = array();
        $detail['sell_record_code'] = $request['sell_record_code'];
        $detail['deal_code'] = $request['deal_code'];
        $detail['data'][0] = load_model('prm/GoodsModel')->get_sku_list($request['sku']);
        $detail['data'][0]['num'] = $request['num'];
        $detail['data']['barcode'] = $request['barcode'];
        $detail['data'][0]['sum_money'] = $request['avg_money'];
        $detail['data'][0]['trade_price'] = !empty($request['trade_price']) ? $request['trade_price'] : 0;
        $detail['data'][0]['fx_amount'] = !empty($request['fx_amount']) ? $request['fx_amount'] : 0;
        $detail['deal_code'] = $request['deal_code'];
        if (isset($request['is_gift']) && $request['is_gift'] == '1') {
            $detail['data'][0]['is_gift'] = $request['is_gift'];
        }
        $response = $mdl->opt_new_multi_detail($detail);
        load_model('oms/SellRecordOptModel')->set_tb_log($detail['sell_record_code']);
    }

    //订单详情页面 修改商品信息
    function update_goods_info(array &$request, array &$response, array &$app) {
        $new_params = array();
        $sell_record_code = '';
        foreach ($request['data'] as $key => $record_info) {
            $record_arr = explode(';', $record_info);
            foreach ($record_arr as $new_record) {
                $record = explode("=", $new_record);
                $new_params[$key][$record[0]] = $record[1];
                if ($record[0] == 'sell_record_code') {
                    $sell_record_code = $record[1];
                }
            }
        }
        $res = load_model('oms/SellRecordModel')->check_sellrecord_status($sell_record_code);
        if ($res) {
            $response['status'] = -1;
            $response['message'] = '订单状态发生变化！';
            exit_json_response($response);
        }
        $response = load_model('oms/SellRecordModel')->update_goods_info($new_params);
        $response['sell_record_code'] = $new_params[1]['sell_record_code'];

        load_model('oms/SellRecordOptModel')->set_tb_log($response['sell_record_code']);
    }

    //订单详情页面 修改送货信息
    function update_shipping_info(array &$request, array &$response, array &$app) {
        $mdlSellRecord = new SellRecordModel();
        $response = $mdlSellRecord->update_shipping_info($request['data']['sell_record_code'], $request['data']);
        load_model('oms/SellRecordOptModel')->set_tb_log($request['data']['sell_record_code']);
    }

    function update_inv_info(array &$request, array &$response, array &$app) {
        $mdlSellRecord = new SellRecordModel();
        $response = $mdlSellRecord->update_inv_info($request['data']['sell_record_code'], $request['data']);
    }

    //更新允许转单但未转单订单的商品条码操作
    function barcode_update(array &$request, array &$response, array &$app) {
        $response = load_model('oms/ApiOrderModel')->barcode_update($request);
    }

    //获取WMS配发货查询功能
    function get_wms_status(array &$request, array &$response, array &$app) {
        if ($request['wms_system_code']) {
//     		$mod = load_model('wms/WmsBaseModel')->get_wms_api_mod('iwms',$request['store_code']);
//     		$params = array('OrderSn' => $request['sell_record_code'],'status' => '0:9');
////      		$params = array('OrderSn' => '1508190069177','status' => '0:9');
//     		$status_ret = $mod->request_send('ewms.orderbizflow.get', $params);
//     		$status_arr = array();
//     		$opdate_arr = array();
//     		if ($status_ret['status'] == 1){
//     			foreach ($status_ret['data'] as $key => $status){
//     				$status_arr[] = $status['Status'];
//     				$opdate_arr[] = $status['OpDate'];
//     			}
//     			array_multisort($status_arr,SORT_DESC,$opdate_arr,$status_ret['data']);
//     		}
//     		$response = $status_ret;
            //新的查询方式
            if ($request['order_process'] == 1 && !in_array($request['wms_system_code'], array('ydwms', 'iwms', 'qimen', 'jdwms'))) {
                $response = load_model('oms/SellRecordProcessModel')->get_order_process($request['sell_record_code']);
            } else {
                $response = load_model('wms/WmsMgrModel')->get_wms_record_status($request['sell_record_code']);
            }
        } else {
            $response['status'] = -1;
            $response['message'] = '查询信息为空！';
        }
    }

    function wms_force_cancel(array &$request, array &$response, array &$app) {

        $response = load_model("oms/SellRecordOptModel")->opt_intercept($request['sell_record_code'], 0, '', 1);
    }

    function logistic_trace(array &$request, array &$response, array &$app) {
        $kdniao = load_model('sys/SysParamsModel')->get_val_by_code('kdniao_enable');
        if ($kdniao['kdniao_enable'] == 1) {
            $response = load_model('oms/SellRecordModel')->logistic_trace($request['order_code']);
        } else {
            $response = load_model("oms/SellRecordModel")->taobao_logistics_trace($request['order_code']);
        }
        $response['kdniao_enable'] = $kdniao['kdniao_enable'];
    }

    /**
     * @todo 检测问题单中每单的问题数，大于一的提示用户并返回单号
     */
    function check_question(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $codes = array();
        foreach ($request['sell_record_code_list'] as $sell_record_code) {
            //获取每单的问题数
            $ret = load_model('oms/SellRecordTagModel')->get_sum_by_sell_record_code($sell_record_code);
            if ($ret == 1) {
                continue;
            } else {
                $codes[] = $sell_record_code;
            }
        }
        if (empty($codes)) {
            $response = array('status' => 1, '', '');
        } else {
            foreach ($codes as $key => &$value) {
                if ($key % 3 == 0 && $key != 0) {
                    $value = '<br>' . $value;
                }
                $codes_str = implode(',', $codes);
                $response = array('status' => -1, 'data' => $codes_str, '');
            }
        }
    }

    function td_delete(array &$request, array &$response, array &$app) {
        $detail = get_array_vars($request, array('detail_id', 'tid'));
        $ret = load_model('oms/ApiOrderDetailModel')->td_delete($detail);
        exit_json_response($ret);
    }

    function get_record_key_data(array &$request, array &$response, array &$app) {

     //   $data = load_model('oms/SellRecordModel')->get_record_by_code($request['sell_record_code']);

        $data =  load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($request['sell_record_code'], $request['key']);
        $key_arr = explode(',', $request['key']);
        $ret_data = array();
        $key_info = array(
            'receiver_address' => '收货地址',
            'receiver_name' => '收货人',
            'receiver_mobile' => '手机',
            'receiver_phone' => '固定电话',
            'buyer_name'=>'买家昵称',
        );

        $action_arr = array();
        foreach ($key_arr as $key) {
            $ret_data[$key] = $data[$key];
            $action_arr[] = $key_info[$key];
        }
        load_model('oms/SellRecordActionModel')->add_action($request['sell_record_code'], '信息查看', implode(",", $action_arr));

        $response = $ret_data;
    }

    //查看优惠信息
    function event_details(array &$request, array &$response, array &$app) {
//        $response = load_model('SellRecordModel')->get_event_details($request);
    }

    //待发货订单列表
    function wait_shipped_list(array &$request, array &$response, array &$app) {
        $arr = array('print_delivery_record_template', 'clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['print_delivery_record_template'] = isset($ret_arr['print_delivery_record_template']) ? $ret_arr['print_delivery_record_template'] : 0;
        $response['new_clodop_print'] = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
    }

    //签收超时订单
    function overtime_list(array &$request, array &$response, array &$app) {
        
    }

    //签收超时订单
    function record_overtime_list(array &$request, array &$response, array &$app) {
        $app['act'] = 'overtime_list';
    }

    //删除退款商品
    function do_delete(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $success = 0;
        $fail = 0;

        foreach ($request['sell_record_code_list'] as $sell_record_code) {
            $detail = load_model('oms/SellRecordModel')->get_detail_list_by_code($sell_record_code);
            if (empty($detail)) {
                continue;
            }
            foreach ($detail as $value) {
                if ($value['api_refund_num'] > 0) {
                    $ret = $mdl->opt_delete_return_detail($sell_record_code, $value['sell_record_detail_id']);
                    if ($ret['status'] != 1) {
                        $fail++;
                    } else {
                        $success++;
                    }
                    load_model('oms/SellRecordOptModel')->set_tb_log($sell_record_code);
                }
            }
            $detail_new = load_model('oms/SellRecordModel')->get_detail_list_by_code($sell_record_code);
            if (empty($detail_new)) {
                //删除后明细为空作废订单
                $ret = load_model('oms/SellRecordOptModel')->opt_cancel($sell_record_code, 1, 'direct');
            }
        }
        if ($fail != 0) {
            $mes = '删除退款商品成功' . $success . '条,失败' . $fail . '条！';
            $response = array('status' => -1, 'message' => $mes);
        } else {
            $response = array('status' => 1, 'message' => '删除成功！');
        }
    }

    //沟通日志页面
    function communicate_log(array &$request, array &$response, array &$app) {
        
    }

    //沟通日志
    function opt_communicate_log(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/SellRecordModel')->communicate_log($request);
        exit_json_response($ret);
    }

    //打标
    function do_label(array &$request, array &$response, array &$app) {
        
    }

    //发货超时订单
    function deliver_overtime_list(array &$request, array &$response, array &$app) {
        //发货超时订单统计总数
        $response['deliver_overtime_num'] = load_model('oms/OrderMenuTipModel')->get_deliver_overtime_num();
    }

    function opt_record_by_sell_remark(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/SellRecordOptModel')->opt_record_by_seller_remark();
    }

    //订单发货超时订单
    function record_deliver_overtime_list(array &$request, array &$response, array &$app) {
        $app['act'] = 'deliver_overtime_list';
    }

    //发货超时
    function deliver_overtime_count(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/SellRecordModel')->deliver_overtime_count($request);
    }

    /**
     * 快速审单
     */
    function inspect_record(array &$request, array &$response, array &$app) {
        
    }

    /**
     * 获取快速审单筛选数据
     */
    function get_filter_data(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $param = get_array_vars($request, array('shop_code', 'filter_type', 'is_refresh'));
        $response = load_model('oms/SellRecordInspectModel')->get_filter_data($param);
    }

    function onekey_inspect_record(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $param = get_array_vars($request, array('shop_code', 'filter_type', 'filter_data'));
        $response = load_model('oms/SellRecordInspectModel')->mutil_inspect_record($param);
    }

    //新增套餐明细
    function opt_new_combo_detail(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $mdl = new SellRecordOptModel();
        $response = $mdl->opt_new_combo_detail($request);
        $m = new SellRecordModel();
        $response['record'] = $m->get_record_by_code($request['sell_record_code']);

        load_model('oms/SellRecordOptModel')->set_tb_log($request['sell_record_code']);
    }

    /**
     * @todo 获取平台交易列表展开明细数据
     */
    function get_td_list_cascade_data(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/ApiOrderDetailModel')->get_detail_by_tid_arr($request['expand_param']);
    }
    /**
     * @todo 获取平台退单列表展开明细数据
     */
    function get_td_list_cascade_data_refund(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/ApiOrderDetailModel')->get_detail_by_tid_arr_refund($request['expand_param']);
    }

    /**
     * @todo 获取订单列表展开明细数据
     */
    function get_ex_list_cascade_data(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/SellRecordModel')->get_ex_list_cascade_data($request['expand_param']);
    }
    /**
     * @todo 获取退单列表展开明细数据
     */
    function get_return_list_cascade_data(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/SellRecordModel')->get_return_list_cascade_data($request['expand_param']);
    }

    function express_recycling(array &$request, array &$response, array &$app) {
        
    }

    function show_express(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/SellRecordModel')->show_express($request['express_no']);
        exit_json_response($ret);
    }

    function express_back(array &$request, array &$response, array &$app) {
        $ret = load_model('oms/SellRecordModel')->express_back($request['id'], $request['code'], $request['shop']);
        exit_json_response($ret);
    }

    function tfx_history_import_trade(array &$request, array &$response, array &$app) {
        
    }

    function fx_history_import_trade_action(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        if ($ret['status'] > 0) {
            $file = $ret['url'];
        } else {
            echo $ret['msg'];
            die;
        }

        $ret_msg = load_model('oms/SellRecordHistoryImport')->fx_import_trade_action($file);
        header("Content-Type: text/html; charset=UTF-8");
        echo $ret_msg;
        die;
    }


    
    function get_edit_address(array &$request, array &$response, array &$app) {
         $action_note = '发货信息编辑';
         $customer_address_data['customer_address_id'] = $request['customer_address_id'];
         $record_type = 'sell_record';
         $record_code = $request['record_code'];
         $response  = load_model('sys/security/OmsSecurityOptModel')->show_address($record_code, $record_type, $customer_address_data, $action_note); 

            
    }

    /**
     * 批量拆单页面
     */
    function split_order_batch(array &$request, array &$response, array &$app) {
        
    }

    /**
     * 批量拆单操作
     */
    function split_order_batch_act(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        $response = load_model('oms/OrderSplitModel')->split_order_process($request['params']);
    }
    function reset_encrypt_sell_record(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
       $response = load_model('sys/security/OmsSecurityOptModel')->reset_record_info($request['sell_record_code']); 
    }
    
    function update_invoice_info(array &$request, array &$response, array &$app) {
        $mdlSellRecord = new SellRecordModel();
        $response = $mdlSellRecord->update_invoice_info($request['data']['sell_record_code'], $request['data']);
    }

    
    function link_wangwang(array &$request, array &$response, array &$app) {
        $request['type'] = !isset($request['type']) ? 0 : $request['type'];
        if ($request['type'] == 0) {
            $data = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($request['record_code'], 'buyer_name');
            load_model('oms/SellRecordActionModel')->add_action($request['record_code'], '与买家沟通', '通过旺旺与买家沟通');
        } else {
            $data = load_model('sys/security/OmsSecurityOptModel')->get_sell_return_decrypt_info($request['record_code'], 'buyer_name');
         
            $return_record = load_model('oms/SellReturnModel')->get_return_by_return_code($request['record_code']);
            load_model('oms/SellReturnModel')->add_action($return_record, '与买家沟通','通过旺旺与买家沟通');
        }
        if(empty($data['buyer_name'])){
                echo '数据异常！';die;
         }
        $name = urlencode($data['buyer_name']);
        $url = 'http://amos.im.alisoft.com/msg.aw?v=2&uid=' . $name . '&site=cntaobao&s=1&charset=utf-8';
        CTX()->redirect($url);
    }


    //获取相应发货单ID所对应的订单号
    function getRecord(array &$request, array &$response, array &$app){
        $ret = load_model('oms/DeliverRecordWtyModel')->get_sell_record_code($request['record_ids']);
        if(empty($ret)){
            exit_json_response('-1',$ret,'发货单为空');
        }
        exit_json_response('1',$ret);
    }
    //买家已付款是否大于应付款
    function is_payment_gtr_payable(array &$request, array &$response, array &$app){
        $record = load_model('oms/SellRecordModel')->get_record_by_code($request['sell_record_code']);
        if (empty($record)){
            exit_json_response(-1, '', '订单不存在');
        }
        if ($record['payable_money'] < $record['paid_money']){
            exit_json_response(1, true);
        }
        exit_json_response(1, false);
    }

    /**
     * 生成采购订单
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function add_plan_record(array &$request, array &$response, array &$app) {
        //组装缺货订单明星
        $params = get_array_vars($request, array('sell_record_code_list'));
        $response['sell_record_detail'] = load_model('oms/SellRecordModel')->get_record_short_detail($params['sell_record_code_list']);
        //仓库
        $response['store_code'] = load_model('base/StoreModel')->get_purview_store();
        //供应商
        $response['supplier_code'] = load_model('base/SupplierModel')->get_purview_supplier();
    }

    /**
     * 生成采购订单操作
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function add_plan_record_action(array &$request, array &$response, array &$app) {
        $out_params = get_array_vars($request, array('data'));
        $ret = load_model('oms/SellRecordModel')->add_plan_record_action($out_params['data']);
        exit_json_response($ret);
    }
    //选择库位
    function select_shelf (array &$request, array &$response, array &$app){
        if(isset($request['store_code']) && $request['store_code'] != ''){
            $response['store_code'] = $request['store_code'];
        }
        $app['page'] = 'NULL';
    }
    function select_spec1 (array &$request, array &$response, array &$app){
        //spec1别名
        $arr = array('goods_spec1');
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] =isset($arr_spec1['goods_spec1'])?$arr_spec1['goods_spec1']:'' ;

        $app['page'] = 'NULL';
    }
    function select_spec2 (array &$request, array &$response, array &$app){
        //spec2别名
        $arr2 = array('goods_spec2');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr2);
        $response['goods_spec2_rename'] =isset($arr_spec2['goods_spec2'])?$arr_spec2['goods_spec2']:'' ;
        $app['page'] = 'NULL';
    }
    /**
     * 检验发货单是否存在
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function check_deliver(array &$request, array &$response, array &$app){
        $ret = load_model('oms/DeliverRecordWtyModel')->check_deliver($request['sell_record']);
        exit_json_response($ret);
    }
}
