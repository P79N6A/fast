<?php

require_model('wms/WmsRecordModel');
require_lib('apiclient/TaobaoClient');

class WmsSellRecordModel extends WmsRecordModel {
    
    public $express_type =array(
         'SFCR',//顺丰次日
    );
    
    public $sfc_express_type = array(
        'WWAM',//邮政小包（平邮）
        'WWRAM'//邮政小包（挂号）
    );
            
    function __construct() {
        parent::__construct();
    }

    /**
     * 订单发货处理
     * @param string $sell_record_code 订单号
     * @param date $record_time 业务时间
     * @param string $express_code 快递代码
     * @param string $express_no 快递单号
     * @param float $order_weight 订单重量
     * @return array 处理结果
     */
    function order_shipping($sell_record_code, $record_time, $express_code, $express_no, $order_weight) {
        $record_type = 'sell_record';
        //检查订单发货状态，已发货不处理
        $sql = "SELECT shipping_status FROM oms_sell_record WHERE sell_record_code = :sell_record_code";
        $shipping_status = ctx()->db->getOne($sql, array(':sell_record_code' => $sell_record_code));
        if ($shipping_status >= 4) {
            return $this->format_ret(1);
        }
        //校验配送方式是否存在
        $ret_exists = $this->check_express_exists($express_code);
        if ($ret_exists['status'] != 1) {
            return $ret_exists;
        }
        $express_code = $ret_exists['data'];

        if (empty($express_no)) {
            return $this->format_ret(-1, '', '快递单号不能为空');
        }
        //取快递单号
        $express_no_arr = array();
        if (strpos($express_no, ';') !== false) {
            $express_no_arr = explode(";", $express_no);
            $express_no = $express_no_arr[0];
        }

        if (strpos($express_no, ',') !== false) {
            $express_no_arr = explode(",", $express_no);
            $express_no = $express_no_arr[0];
        }
        $this->begin_trans();
        //更新订单发货信息
        $sql = "UPDATE oms_sell_record SET express_code = :express_code, express_no = :express_no, real_weigh=:real_weigh WHERE sell_record_code = :sell_record_code";
        $sql_values = array(':express_code' => $express_code, ':express_no' => $express_no, ':real_weigh' => $order_weight, ':sell_record_code' => $sell_record_code);
        ctx()->db->query($sql, $sql_values);
        //主单信息
        $record = load_model("oms/SellRecordOptModel")->get_record_by_code($sell_record_code);
        //明细数据
        $detail = load_model("oms/SellRecordOptModel")->get_detail_list_by_code($sell_record_code);
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        $force_negative_inv = 0;
        //批次处理
        $this->get_wms_cfg($record['store_code']);
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        if ($is_lof == 1 && in_array($this->api_product, array('qimen'))) {
            $ret = load_model('wms/WmsSwitchLofModel')->switch_lof_lock($sell_record_code, $record_type);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1, '', '批次处理失败');
            }
            $force_negative_inv = 1;
        }
        
        //订单发货
        $ret = load_model("oms/SellRecordOptModel")->sell_record_send($record, $detail, $sys_user, 'wms_send', 0, 1, $force_negative_inv);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }
        if (!empty($express_no_arr)) {
            load_model("oms/SellRecordOptModel")->add_action($sell_record_code, "仓储发货", '多快递单：' . implode(",", $express_no_arr));
        }

        $this->commit();
        return $ret;
    }

    function order_cancel($is_refund, $msg) {
        $record = load_model("oms/SellRecordOptModel")->get_record_by_code($sell_record_code);
        $ret = $this->biz_intercept($record, $is_refund, $msg);
        return $ret;
    }

    function order_print($record) {
        $sale_channel_code_limit = explode(',', 'jingdong,dangdang,vjia');
        if (!in_array($record['sale_channel_code'], $sale_channel_code_limit)) {
            return $this->format_ret(1);
        }
        $func = "get_print_data_" . $record['sale_channel_code'];
        $ret = $this->$func($record);
        return $ret;
    }

    function get_print_data_jingdong($record) {
        
    //   if( $record['is_handwork']==1 ){
     //       return $this->format_ret(1,array());
    //   }
        
        $sql = "select api from base_shop_api where shop_code = :shop_code";
        $api_data = ctx()->db->get_row($sql, array(':shop_code' => $record['shop_code']));
        $api_data_arr = json_decode($api_data['api'], true);

        if (empty($api_data_arr['type'])) {
            return $this->format_ret(-1,'',  '找不到商店配置的京东合作模式 sop/sopl/lbp');
        }
        $jd_type = strtolower($api_data_arr['type']);
        $jd_type_limit = explode(',', 'sop,sopl,lbp');
        if (!in_array($jd_type, $jd_type_limit)) {
            return $this->format_ret(1);
        }
        $sql = "select * from api_jingdong_trade_printdata where order_id = :deal_code";
        $print_data = ctx()->db->get_row($sql, array(':deal_code' => $record['deal_code_list']));
        if (empty($print_data)) {
            return $this->format_ret(-1,'', '缺少京东打印数据');
        }

        $sql = "select * from api_jingdong_trade_printdata_detail where id = :deal_code";
        $print_data['print_items'] = ctx()->db->get_all($sql, array(':deal_code' => $record['deal_code_list']));
        if (empty($print_data['print_items'])) {
            return $this->format_ret(-1, '', '缺少京东打印明细数据');
        }

        $result = array('print_data' => $print_data, 'jd_type' => $jd_type);

        return $this->format_ret(1, $result);
    }

    function get_print_data_dangdang($record) {
        if ($record['pay_type'] == 'cod') {
            return $this->format_ret(-1, '', '货到付款需要下载面单');
        }
        return $this->format_ret(1);
    }

    /**
     * 获取上传中间表的单据数据
     * @param string $sell_record_code 订单号
     * @return array 数据集
     */
    function get_record_info($sell_record_code) {
        $sql = "select sell_record_code,deal_code_list,sale_channel_code,order_status,shipping_status,store_code,shop_code,pay_type,pay_code,pay_time,buyer_name,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,receiver_street,receiver_address,receiver_addr,receiver_zip_code,receiver_mobile,receiver_phone,receiver_email,express_code,express_no,plan_send_time,goods_num,sku_num,goods_weigh,lock_inv_status,buyer_remark,seller_remark,seller_flag,order_remark,store_remark,order_money,goods_money,express_money,payable_money,paid_money,invoice_type,invoice_title,invoice_content,invoice_money,invoice_status,record_time,is_change_record,is_wap,is_jhs,is_fenxiao,check_time,is_notice_time,plan_send_time,is_handwork,sale_mode,create_time from oms_sell_record where sell_record_code = :sell_record_code";
        $info = ctx()->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
        if (empty($info)) {
            return $this->format_ret(-1, '', '找不到订单');
        }
        $this->get_wms_cfg($info['store_code']);
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        if (in_array($this->api_product, array('qimen')) && $is_lof == 1) {
            $sql = "SELECT deal_code,goods_code,spec1_code,spec2_code,sku,barcode,SUM(num) AS num,SUM(lock_num) AS lock_num,goods_price,SUM(goods_weigh) AS goods_weigh,SUM(avg_money) AS avg_money from oms_sell_record_detail WHERE sell_record_code = :sell_record_code GROUP BY sku";
        } else {
            $sql = "select deal_code,goods_code,spec1_code,spec2_code,sku,barcode,num,lock_num,is_gift,goods_price,goods_weigh,avg_money from oms_sell_record_detail where sell_record_code = :sell_record_code";
        }
        $goods = ctx()->db->get_all($sql, array(':sell_record_code' => $sell_record_code));
        if (empty($goods)) {
            return $this->format_ret(-1, '', '找不到订单明细');
        }
        //开启批次，则增加批次数据
        if (in_array($this->api_product, array('qimen')) && $is_lof == 1) {
            $goods = load_model('util/ViewUtilModel')->get_map_arr($goods, 'sku');
            $lof_data = load_model('oms/SellRecordLofModel')->get_by_order_code($sell_record_code, 1);
            foreach ($lof_data as $val) {
                $sku = $val['sku'];
                if (isset($goods[$sku])) {
                    $goods[$sku]['batchs'][] = get_array_vars($val, array('lof_no', 'production_date', 'num'));
                }
            }
        }

        $goods = array_values($goods);
        $info['goods'] = load_model('util/ViewUtilModel')->record_detail_append_goods_info($goods);
        $ret = load_model('wms/WmsRecordModel')->check_mx($info['goods']);

        if ($ret['status'] < 0) {
            return $ret;
        }
        
        $ret_jd = $this->wms_get_express_info($info);
        
        if ($ret_jd['status'] < 0) {
            return $ret_jd;
        }

        if (!empty($ret_jd['data'])) {
            $info['payable_money'] = $ret_jd['data']['payable_money'];
            $info['express_no'] = $ret_jd['data']['express_no'];
        }
        if (isset($info['pay_type']) && $info['pay_type'] == 'cod' && $this->api_product != 'jdwms') {
            $ret = $this->order_print($info);
            if ($ret['status'] < 0) {
                return $ret;
            }
            if (!empty($ret['data'])) {
                $info['__print'] = $ret['data'];
            }
        } else {
            $info['__print'] = $ret_jd['data']['express_data'];
        }


        return $this->format_ret(1, $info);
    }

    //获取京东热敏单号
    function wms_get_express_info($info) {
        $wms_cfg = $this->get_wms_cfg($info['store_code']);
        $api_product = $wms_cfg['api_product'];
        $express_no = '';
        if ($api_product != 'qimen' && $api_product != 'jdwms') {
            return $this->format_ret(1, $express_no);
        }
        $sys_param = load_model('sys/SysParamsModel')->get_val_by_code(array('wms_is_get_jd_cod_express'));
        //参数开启才获取
        if ($sys_param['wms_is_get_jd_cod_express'] == 0) {
            return $this->format_ret(1);
        }
        $express_sql = "SELECT company_code FROM base_express WHERE express_code = :express_code";
        $express_sql_values = array(":express_code" => $info['express_code']);
        $company_code = $this->db->get_value($express_sql, $express_sql_values);
        $data = array();
        if ($info['pay_type'] == 'cod' && $info['sale_channel_code'] == 'jingdong') {

            $params = array();

            $apiName = 'jingdong_api/jingdong_etms_waybillcode_get';
            $shop_code = $info['shop_code'];
            $payable_money = $this->db->get_value('SELECT order_payment FROM api_jingdong_trade WHERE order_id=:tid', array(':tid' => $info['deal_code_list']));
            if ($payable_money === FALSE) {
                return $this->format_ret(-1, '', "店铺{$shop_code}的京东订单买家应付金额获取失败");
            }
            //$shop_id = $this->db->get_value("select shop_id from base_shop where  shop_code='{$shop_code}'");

            $params['pre_num'] = 1;
            $params['shop_code'] = $info['shop_code']; //店铺ID

            $ret_api = load_model('sys/EfastApiModel')->request_api($apiName, $params);
            
            //FIX ME 京东获取单号的接口处理数据有问题，返回结果如果有错误不方便识别 FBB 2017.06.08
            $ret_info = isset($ret_api['resp_data']['jingdong_etms_waybillcode_get_responce']['resultInfo']) ? $ret_api['resp_data']['jingdong_etms_waybillcode_get_responce']['resultInfo'] : '';
            
            if (($ret_api['resp_data']['code'] != 100 || $ret_api['resp_data']['code'] != 0) && empty($ret_info)) {
                return $this->format_ret(-1, '', '店铺' . $shop_code . '的京东面单号获取失败:' . $ret_api['resp_data']['msg']);
            }

            $up_data['express_no'] = $ret_info['deliveryIdList'][0];
            $this->db->update('oms_sell_record', $up_data, " sell_record_code = '{$info['sell_record_code']}'");
            $log_msg = "单号:" . $up_data['express_no'];
            load_model('oms/SellRecordOptModel')->add_action($info['sell_record_code'], "京东热敏获取物流单号", $log_msg);
            $express_no = $up_data['express_no'];
           $data = array(
            'payable_money' => $payable_money,
            'express_no' => $express_no
            );
        } else if ($api_product == 'jdwms') {
            if (in_array($info['express_code'], $this->express_type)) {
                $waybill = load_model('remin/ShunfengModel')->get_sf_express_no_by_sell_record_code($info['sell_record_code']);
            } else {
                $waybill = load_model('oms/SellRecordOptModel')->get_waybill($info['sell_record_code']);
            }
            if ($waybill['status'] == 1) {
                $data['payable_money'] = $info['payable_money'];
                $data['express_no'] = $waybill['data']['express_no'];
                $data['express_data'] = $waybill['data']['express_data'];
            } else if ($waybill['status'] == -1) {
                return $waybill;
            }
        } else if($company_code == 'SFC') {
           $sfc_data = $this->wms_get_sfc_waybill($info);
           if ($sfc_data['status'] == 1) {
                $data['payable_money'] = $info['payable_money'];
                $data['express_no'] = $sfc_data['data']['express_no'];
                $data['express_data'] = $sfc_data['data']['express_data'];
            } else if ($sfc_data['status'] == -1) {
                return $sfc_data;
            }
        }

        return $this->format_ret(1, $data);
    }

    function sync_wms_quehuo($efast_store_code, $start_time, $end_time) {
        //$start_time = '2015-05-01 00:00:00';
        //$end_time = '2015-06-01 00:00:00';
        $ret = load_model('wms/iwms/IwmsSellRecordModel')->get_wms_quehuo($efast_store_code, $start_time, $end_time);
        if ($ret['status'] < 0) {
            return $ret;
        }

        if (is_array($ret['data'])) {
            $save_ret = $this->save_wms_quehuo($ret['data']);
            if ($save_ret['status'] < 0) {
                return $save_ret;
            }
        }

        return $ret;
    }

    function save_wms_quehuo($data) {
        foreach ($data as $val) {
            $d = $val;
            ctx()->db->begin_trans();
            M('wms_trade_quehuo_mx')->insert_dup($val['goods']);
            unset($val['goods']);
            M('wms_trade_quehuo')->insert_dup($val);
            $this->set_problem_wms_short_order($d);
            ctx()->db->commit();
        }
        return $this->format_ret(1);
    }

    function set_problem_wms_short_order($data) {
        $is_active = load_model('base/QuestionLabelModel')->get_is_active_value('WMS_SHORT_ORDER');
        if ($is_active == 1) {
            $sql = "SELECT t2.* FROM wms_oms_trade t1
                INNER JOIN oms_sell_record t2 ON t1.record_code = t2.sell_record_code
                where t1.record_type = 'sell_record' and t1.upload_response_flag = 10 and t1.cancel_response_flag<>10 and t1.wms_order_flow_end_flag = 0 and t2.order_status=1 AND t2.shipping_status<>4 and t2.sell_record_code = '{$data['sell_record_code']}'";
            $db_wms = ctx()->db->get_row($sql);
            if (empty($db_wms)) {
                return $this->format_ret(1);
            }
            $ret = load_model('oms/SellRecordOptModel')->biz_intercept($db_wms, 3, '');

            if ($ret['status'] < 0) {
                return $this->format_ret(-1, $ret, 'wms订单拦截失败,设问失败');
            }

            $ret = load_model('oms/SellProblemModel')->save_problem($data['sell_record_code'], 'WMS_SHORT_ORDER');
            $action = 'WMS缺货';
            $log_msg = 'WMS缺货，订单返回未确认状态，并设为问题单。';
            foreach ($data['goods'] as $val) {
                $log_msg .= '条码缺货信息：' . $val['barcode'] . '缺货:' . $val['qh_num'] . '；';
            }
            load_model('oms/SellRecordActionModel')->add_action($data['sell_record_code'], $action, $log_msg, true);

            return $ret;
        }
        return $this->format_ret(1);
    }

    function process_quehuo() {
        $sql = "select sell_record_code,efast_store_code from wms_trade_quehuo where process_flag = 0";
        $db_wms = ctx()->db->get_all($sql);
        if (empty($db_wms)) {
            return $this->format_ret(1, '', '没有可处理的数据');
        }
        $sell_record_list = load_model('util/ViewUtilModel')->get_arr_val_by_key($db_wms, 'sell_record_code', 'string', 'string');
        $sql = "select sell_record_code,store_code from oms_sell_record where sell_record_code in({$sell_record_list})";
        $db_sell = ctx()->db->get_all($sql);
        $sell_arr = load_model('util/ViewUtilModel')->get_map_arr($db_sell, 'sell_record_code');

        $result = array();
        foreach ($db_wms as $sub_wms) {
            $sell_record_code = $sub_wms['sell_record_code'];

            $find_sell = @$sell_arr[$sell_record_code];
            if (empty($find_sell)) {
                $_msg = '找不到对应的订单';
                $this->set_process_quehuo_result($sell_record_code, 20, $_msg);
                $result['fail'][$sell_record_code] = $_msg;
                continue;
            }
            if ($find_sell['store_code'] <> $sub_wms['efast_store_code']) {
                $_msg = '仓库代码对应出错';
                $this->set_process_quehuo_result($sell_record_code, 20, $_msg);
                $result['fail'][$sell_record_code] = $_msg;
                continue;
            }
            $ret = $this->save_quehuo_problem($sell_record_code);
            if ($ret['status'] < 0) {
                $result['fail'][$sell_record_code] = $_msg;
                $this->set_process_quehuo_result($sell_record_code, 20, $ret['message']);
            } else {
                $result['success'][$sell_record_code] = $ret;
                $this->set_process_quehuo_result($sell_record_code, 30);
            }
        }

        return $this->format_ret(1, $result);
    }

    function save_quehuo_problem($sell_record_code, $q_code = 'WMS_SHORT_ORDER', $action = 'WMS缺货', $log_msg = 'WMS缺货，订单返回未确认状态，并设为问题单') {
        ctx()->db->begin_trans();
        $ret = load_model('oms/SellProblemModel')->save_problem($sell_record_code, $q_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $_tv = '0000-00-00 00:00:00';
        $sql = "update oms_sell_record set is_problem = 1,order_status = 0,shipping_status = 0,is_notice_time = '{$_tv}',check_time = '{$_tv}' where sell_record_code = '{$sell_record_code}' and order_status<>3 and shipping_status<4";
        ctx()->db->query($sql);
        ctx()->db->commit();
        load_model('oms/SellRecordActionModel')->add_action($sell_record_code, $action, $log_msg, true);
        return $ret;
    }

    function set_process_quehuo_result($sell_record_code, $process_flag, $process_err_msg = '') {
        $sql = "update wms_trade_quehuo set process_flag = {$process_flag},process_err_msg = '{$process_err_msg}' where sell_record_code = '{$sell_record_code}' and process_flag<30";
        ctx()->db->query($sql);
        return $this->format_ret(1);
    }  
    
    /**
     * @todo 获取三态物流信息
     */
    function wms_get_sfc_waybill($sell_record_data) {
        if(!in_array($sell_record_data['express_code'], $this->sfc_express_type)) {
            return $this->format_ret(-1, '', '不支持该配送方式获取三态物流信息，请选择三态速递公司的“邮政小包（平邮）”或“邮政小包（挂号）”。');
        }
        $express_sql = "SELECT sfc.*, b.print_type FROM sfc_rm_config  AS sfc INNER JOIN base_express AS b ON sfc.express_code = b.express_code  WHERE b.express_code = :express_code ORDER BY sfc.pid DESC";
        $express_data = $this->db->get_row($express_sql, array('express_code' => $sell_record_data['express_code']));
        if($express_data['print_type'] != 1) {
            return $this->format_ret(-1, '', '仅支持通过“直连热敏”获取三态物流信息');
        }
        $header = array();
        $header['appKey'] = $express_data['sfckey'];
        $header['token'] = $express_data['token'];
        $header['userId'] = $express_data['sfcid'];
        $client = new SoapClient('http://www.sendfromchina.com/ishipsvc/web-service?wsdl');
        $params = array();
        //获取发货人信息（店铺信息不为空取店铺，否则取仓库）
        $store_sql = "SELECT * FROM base_store WHERE store_code = :store_code";
        $store = $this->db->get_row($store_sql, array('store_code' => $sell_record_data['store_code']));
        $store_sender_info = $store;
        $store_sender_info['tel'] = $store['contact_phone'];

        $shop_sql = "SELECT * FROM base_shop WHERE shop_code = :shop_code";
        $shop = $this->db->get_row($shop_sql, array('shop_code' => $sell_record_data['shop_code']));
        if (!empty($shop['province']) && !empty($shop['city']) && !empty($shop['contact_person']) && !empty($shop['tel']) && !empty($shop['address'])) {
            $store_sender_info = $shop;
        }
        
        $country_sql = "SELECT api_country FROM api_country_data WHERE tid = :tid AND sale_channel_code = :sale_channel_code";
        $api_country = $this->db->get_value($country_sql, array('tid' => $sell_record_data['deal_code_list'], 'sale_channel_code' => $sell_record_data['sale_channel_code']));
        if(empty($api_country)) {
            $api_country = substr($sell_record_data['receiver_addr'], 0, 2);
        }
        $country_conf = require_conf('sys/api_country');
        $country = $country_conf[$api_country];
        if(empty($country)) {
            $country = 'China';
        }
        //收货地址
        $_receiver_ids[] = $store_sender_info['province'];
        $_receiver_ids[] = $store_sender_info['city'];
        $_receiver_ids[] = $store_sender_info['district'];
        $_receiver_ids[] = $store_sender_info['street'];
        $_receiver_ids[] = $sell_record_data['receiver_province'];
        $_receiver_ids[] = $sell_record_data['receiver_city'];
        $_receiver_ids[] = $sell_record_data['receiver_district'];
        $_receiver_ids[] = $sell_record_data['receiver_street'];
        //收货地址
        $_new_receiver_ids = implode("','", array_unique($_receiver_ids));
        $_region_data = $this->db->get_all("SELECT id region_id, name region_name FROM base_area WHERE id IN('{$_new_receiver_ids}')");
        foreach ($_region_data as $_region) {
            $_receiver_data[$_region['region_id']] = $_region['region_name'];
        }

        $sell_record_data['sender_province_name'] = isset($_receiver_data[$store_sender_info['province']]) ? $_receiver_data[$store_sender_info['province']] : '';
        $sell_record_data['sender_district_name'] = isset($_receiver_data[$store_sender_info['city']]) ? $_receiver_data[$store_sender_info['city']] : '';
        $sell_record_data['sender_city_name'] = isset($_receiver_data[$store_sender_info['district']]) ? $_receiver_data[$store_sender_info['district']] : '';
        $sell_record_data['sender_street_name'] = isset($_receiver_data[$store_sender_info['street']]) ? $_receiver_data[$store_sender_info['street']] : '';
        $sell_record_data['sender_addr'] = $store_sender_info['address'];



        $sell_record_data['receiver_province_name'] = isset($_receiver_data[$sell_record_data['receiver_province']]) ? $_receiver_data[$sell_record_data['receiver_province']] : '';
        $sell_record_data['receiver_city_name'] = isset($_receiver_data[$sell_record_data['receiver_city']]) ? $_receiver_data[$sell_record_data['receiver_city']] : '';
        $sell_record_data['receiver_district_name'] = isset($_receiver_data[$sell_record_data['receiver_district']]) ? $_receiver_data[$sell_record_data['receiver_district']] : '';
        $sell_record_data['receiver_street_name'] = isset($_receiver_data[$sell_record_data['receiver_street']]) ? $_receiver_data[$sell_record_data['receiver_street']] : '';


        $requestInfo = array();
        $goods_details = array();
        $sql_value = array();
        $_d = array();
        $goods_code_arr = array_column($sell_record_data['goods'], 'goods_code');
        $sql_str = $this->arr_to_in_sql_value($goods_code_arr, 'goods_code', $sql_value);
        $api_goods_sql = "SELECT property_val_code, property_val8 FROM base_property WHERE property_val_code IN ({$sql_str}) AND property_type='goods'";
        $api_goods_data = $this->db->get_all($api_goods_sql, $sql_value);
        if(!empty($api_goods_data)) {
            foreach ($api_goods_data as $api_goods) {
                $_d[$api_goods['property_val_code']] = $api_goods['property_val8'];
            }
        }
        $defalult_detailWorth = 3;//商品默认申报商品价值（拓尚）
        $goods_declare_price = 0;
        $goods_species = array();
        foreach ($sell_record_data['goods'] as $key => $goods) {
            $key_arr = array('category_name', 'weight');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($goods['sku'], $key_arr);
            $goods_details[$key]['detailDescription'] = isset($_d[$goods['goods_code']]) && !empty($_d[$goods['goods_code']]) ? $_d[$goods['goods_code']] : $goods['goods_code']; //详细物品描述
            $goods_details[$key]['detailQuantity'] = $goods['num']; //详细物品数量
            $goods_details[$key]['detailDescriptionCN'] = isset($sku_info['category_name']) && !empty($sku_info['category_name']) ? $sku_info['category_name'] : $goods['goods_name']; //订单产品中文描述
            $goods_details[$key]['hsCode'] = $goods['goods_code']; //商品编码
            //$goods_details[$key]['detailWorth'] = $goods['avg_money']/$goods['num']; //详细物品价值 美元
			$goods_details[$key]['detailWorth'] = $defalult_detailWorth;
            $goods_details[$key]['detailWeight'] = isset($sku_info['weight']) && !empty($sku_info['weight']) ? $sku_info['weight'] : $goods['goods_weigh'];//产品重量
//            $goods_details[$key]['detailCustomLabel'] = '';//详细物品客户自定义标签            
//            $goods_details[]['detailEbayTxnId'] = '';//Ebay交易事务ID
//            $goods_details[]['detailEbayItemId'] = '';//EbayItem ID
//            $goods_details[]['detailEbayUserId'] = '';//Ebay买家ID
//            $goods_details[]['detailEbayPayDate'] = '';//Ebay支付时间
//            $goods_details[]['detailEbaySoldDate'] = '';//Ebay卖出时间
//            $goods_details[]['origin'] = '';//原产地
//            $goods_details[$key]['enMaterial'] = '木材';//物品英文材质
//            $goods_details[$key]['cnMaterial'] = '木材';//物品中文材质
//            $goods_details[]['boxId'] = '';//箱号
            $goods_desc[] = $goods_details[$key]['detailDescription'];
           // $goods_declare_price += $goods['avg_money'];//商品总价值 作为物品申报价值
			$goods_declare_price +=  $defalult_detailWorth*$goods['num'];
            $total_goods_weight += $goods['goods_weigh'];
            $goods_species[$key] = $goods['num']; //商品种类
        }
        if ($goods_declare_price > 25) { //商品总金额不能大于25
            $goods_declare_price = 25;
            $goods_sum_num = array_sum($goods_species);
            $species_num = count($goods_species);
            $avg_money = bcdiv($goods_declare_price, $goods_sum_num, 2); //保留小数点后两位
            $diff_money = $goods_declare_price - ($avg_money * $goods_sum_num); //差额
            $goods_declare_price = 0; //重置商品总金额
            foreach($sell_record_data['goods'] as $key => $goods) {
                if($species_num == 1) { //最后一个种类的金额
                    $end_goods_money = $diff_money + ($avg_money * $goods['num']);
                    $goods_details[$key]['detailWorth'] = bcdiv($end_goods_money, $goods['num'], 2); //可能除不尽
                } else {
                    $goods_details[$key]['detailWorth'] = $avg_money;
                }
                $goods_declare_price +=  $goods_details[$key]['detailWorth'] * $goods['num'];
                $species_num--;
            }
        }
        $requestInfo['customerOrderNo'] = $sell_record_data['sell_record_code']; //订单号
        $requestInfo['shipperAddressType'] = 2; //发货地址类型， 1 为用户系统默认地址， 2为用户传送的地址信息
        $requestInfo['shipperName'] = $store_sender_info['contact_person']; //发件人姓名

        $requestInfo['shipperPhone'] = $store_sender_info['tel']; //发件人电话
        $requestInfo['shipperAddress'] = $sell_record_data['sender_province_name'] . $sell_record_data['sender_city_name'] . $sell_record_data['sender_district_name'] . $sell_record_data['sender_city_name'] . $sell_record_data['sender_street_name'] . $sell_record_data['send_addr']; //发件人地址
        $address = explode(',', $sell_record_data['receiver_addr']);
        $requestInfo['shipperCompanyName'] = $shop['shop_name'];//发件人公司名称
        $requestInfo['shippingMethod'] = $sell_record_data['express_code'];//货运方式
        
       
        if($country!='China'){
            $deal_code_list = explode(',', $sell_record_data['deal_code_list']);
            $sql_addr = "select receiver_country,receiver_province,receiver_city from api_order where tid=:tid ";
            $addr_info = $this->db->get_row($sql_addr,array(':tid'=>$deal_code_list[0]));
            $country = isset( $country_conf[$addr_info['receiver_country']] )? $country_conf[$addr_info['receiver_country']]:'China';
        }else{
            $addr_info = array(
                'receiver_province'=>'',
                'receiver_city'=>'',
            );
        }
        $requestInfo['recipientCountry'] = $country; //收件人国家
        
        
        $requestInfo['recipientName'] = $sell_record_data['receiver_name']; //收件人姓名



		$requestInfo['shipperName'] = 'Sharon';
		//$requestInfo['shipperPhone'] = '（86 20）8734 2228';
                $requestInfo['shipperPhone'] = '(+86)13533426237';
		$requestInfo['shipperAddress'] = 'NO. 203, BUILDING 4TH, PHOENIX CREATIVE PARK INDUSTRIAL NORTH AVENUE 67TH, HAIZHU DISTRICT, GUANGZHOU';
		$requestInfo['shipperCompanyName'] = 'TOPD3 NETWORK TECHNOLOGY CO., LTD.';//发件人公司名称
		$requestInfo['shipperEmail'] = 'sharonmai@topd3.com';//发件人邮箱 
                //$requestInfo['shipperEmail'] = 'ebay.viennois@gmail.com';//发件人邮箱 

        $requestInfo['recipientState'] =!empty($addr_info['receiver_province'])&&$addr_info['receiver_province']!='海外'? $addr_info['receiver_province']:$address['1']; //收件人州或省份
        $requestInfo['recipientCity'] = !empty($addr_info['receiver_city'])&&$addr_info['receiver_city']!='海外'? $addr_info['receiver_city']:$address['2']; //收件人城市
        /*if ($country == '海外'){
            $requestInfo['recipientState'] = ''; //收件人州或省份
            $requestInfo['recipientCity'] = ''; //收件人城市
        }*/
        $requestInfo['recipientAddress'] = $sell_record_data['receiver_addr']; //收件人国家
        $requestInfo['recipientZipCode'] = $sell_record_data['receiver_zip_code'];//收件人邮编
        $requestInfo['recipientPhone'] = !empty($sell_record_data['receiver_mobile']) ? $sell_record_data['receiver_mobile'] : $sell_record_data['receiver_phone']; //收件人电话号码
        $requestInfo['recipientEmail'] = $sell_record_data['receiver_email'];//收件人邮箱
        $requestInfo['goodsDescription'] = implode(',', $goods_desc); //物品描述
        $requestInfo['goodsQuantity'] = $sell_record_data['goods_num']; //物品数量
        $requestInfo['goodsDeclareWorth'] = number_format($goods_declare_price, 3, '.', ''); //物品申报价值
        
        $requestInfo['goodsDetails'] = $goods_details; //物品明细
        $requestInfo['orderStatus'] = 'confirmed'; //提交订单 confirmed，订单预提交状态preprocess，提交且交寄订单 sumbmitted，默认为交寄状态
        if($goods_declare_price >= 25){
            $requestInfo['evaluate'] = number_format($goods_declare_price, 3, '.', '') + 0.1; //投保价值
        }
        if($goods_declare_price >= 50) {
            $requestInfo['evaluate'] = number_format(50, 3, '.', ''); //投保价值
        }
        $requestInfo['goodsWeight'] = $total_goods_weight;//物品重量
        $requestInfo['isRemoteConfirm'] = '0'; //是否同意收偏远费 0 不同意， 1 同意
//        $requestInfo['shipperEmail'] = '';//发件人邮箱        
//        $requestInfo['shipperZipCode'] = '';//发件人邮编   
//        $requestInfo['goodsLength'] = '';//物品长
//        $requestInfo['goodsWidth'] = '';//物品宽
//        $requestInfo['goodsHeight'] = '';//物品高
//        $requestInfo['taxesNumber'] = '';//税号
//        $requestInfo['isReturn'] = '';//是否退件 0 否， 1 是，默认 0 只有这些方式 EUEXP1,EUEXP2,SFCQM1,SFCQM1R,RM1,RM1R,SFCQM2,SFCQM2R支持退件
//        $requestInfo['withBattery'] = '';//是否带电池， 1 是，默认 0
//        $requestInfo['dutyforward'] = '';//是否关税预付， 1 是，默认 0
//        $requestInfo['is_Customs'] = '';//是否单独报关， 1 是，默认 0
//        $requestInfo['isSignature'] = '';//是否电子签名， 1 是，默认 0
//        $requestInfo['shippingWorth'] = '';//销售运费（适用 DEAM1,DERAM1）
//        $requestInfo['taxType'] = '';//税号类型 1 自身税号， 2 SFC 税号，只有这些方式 EUEXP1、 EUEXP2、 RM1、 RM1R、SFCQM1、 SFCQM1R、 SFCQM2、 SFCQM2R支持税号类型
//        $requestInfo['isFba'] = '';//是否 FBA 订单, 1 是,0 否
//        $requestInfo['warehouseName'] = '';//当 isFba 为 1 时有效, 可选参考 FBA,或other 其它海外仓储

        $params['HeaderRequest'] = $header;
        $params['addOrderRequestInfo'] = $requestInfo;
        //添加新订单
        try{
			$data = $client->addOrder($params);
        } catch (Exception $e) {
			$error =  $e->getMessage();
			return $this->format_ret(-1, '', $error);
		}
        $d_arr = json_decode(json_encode($data), true);
        //记录日志
        $this->add_log($params, $d_arr);

        $express_no = !empty($d_arr['trackingNumber']) ? $d_arr['trackingNumber'] : $d_arr['orderCode'];
        $flag = 'add';
        //订单已存在
        if($d_arr['orderActionStatus'] != 'Y' && strpos($d_arr['note'], 'has existed in the system')) {
            $sql = "select json_data,new_record_code from wms_oms_trade where record_code = :record_code and record_type = :record_type";
            $row = $this->db->get_row($sql, array(':record_code' => $sell_record_data['sell_record_code'], ':record_type' => 'sell_record'));
            $order = json_decode($row['json_data'], true);
            $print_data = json_decode($order['__print'], true);
            unset($params['addOrderRequestInfo']);
            $params['updateOrderInfo'] = $requestInfo;
            $params['orderCode'] = $print_data['orderCode'];
            try{
                $data = $client->updateOrder($params);
            } catch (Exception $e) {
                $error =  $e->getMessage();
                return $this->format_ret(-1, '', $error);
            }
            $d_arr = json_decode(json_encode($data), true);
            $d_arr['orderCode'] = $print_data['orderCode'];
            $this->add_log($params, $d_arr);
            $flag = 'update';
        }
        //错误提醒
        if($d_arr['orderActionStatus'] != 'Y' && empty($d_arr['trackingNumber']) && empty($d_arr['orderCode']) && !isset($d_arr['ask'])) {
            $msg = !empty($d_arr['note']) && isset($d_arr['note']) ? $d_arr['note'] : '获取三态速递物流信息失败';
            return $this->format_ret(-1, '', $msg);
        }
        if(isset($d_arr['ask']) && $d_arr['ask'] != 'Success') {
            $msg = !empty($d_arr['message']) && isset($d_arr['message']) ? $d_arr['message'] : '更新三态速递物流信息失败';
            return $this->format_ret(-1, '', $msg);
        }
        
        $print_data = array('url' => 'http://www.sendfromchina.com/api/label?orderCodeList=' . $d_arr['orderCode'] . '&printType=1&print_type=pdf&printSize=3',
                            'orderCode' => $d_arr['orderCode']);
        $express_data = json_encode($print_data);
        //更新信息
        $this->db->update('oms_sell_record', array("express_no" => $express_no), " sell_record_code = '{$sell_record_data['sell_record_code']}'");
        //记录操作日志
        $log_msg = "单号:" . $express_no;
        $action = $flag == 'update' ? "更新三态速递物流信息": "获取三态速递物流单号";
        load_model('oms/SellRecordOptModel')->add_action($sell_record_data['sell_record_code'], $action, $log_msg);
        $return_data = array('express_no' => $express_no, 'express_data' => $express_data);
        return $this->format_ret(1, $return_data);
        
    }
    
    function add_log($params, $result) {
        $req = var_export($params, true);
        $res = var_export($result, true);
        $logPath = $this->get_log_path();
        error_log(date("Y-m-d H:i:s") . ":(" . $req . "\n" . $res . "\n\n", 3, $logPath);
    }

    function get_log_path() {
        static $logPath = NULL;
        if ($logPath === NULL) {
            $date = date("Y-m-d");
            $logPath = ROOT_PATH . "logs" . DIRECTORY_SEPARATOR;

            if (defined('RUN_SAAS') && RUN_SAAS) {
                $logPath .= "SanTai_express_log" . DIRECTORY_SEPARATOR;
                if (!file_exists($logPath)) {
                    mkdir($logPath);
                }
                $logPath .= $date . DIRECTORY_SEPARATOR;
                if (!file_exists($logPath)) {
                    mkdir($logPath);
                }
                $logPath .= "SanTai_express_log_";
                $saas_key = CTX()->saas->get_saas_key();
                if (!empty($saas_key)) {
                    $logPath .= $saas_key . "_";
                }

                $logPath .= $date . ".log";
            } else {
                $logPath .= "SanTai_express_log" . $date . ".log";
            }
        }
        return $logPath;
    }
    
    function get_record_data(&$record_data) {

        $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($record_data['sell_record_code']);

        if (empty($record_decrypt_info)) {
            return false;
        }
        if($record_decrypt_info['receiver_addr']=='*****'|| strpos($record_decrypt_info['receiver_mobile'],"***")!==false){
            return false;
        }
        
        $record_data = array_merge($record_data, $record_decrypt_info);
        return true;
    }

}
