<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TicketHandelModel
 *
 * @author wq
 */
require_model('qianniu/TicketModel');

class TicketHanlderModel extends TicketModel {

    protected $type_arr = array(
        'official_refund_with_goods_return',
        'official_refund',
        'official_trade_item_exchange',
        'official_trade_modify_address',
    );

    function exec_ticket_all() {
        $sql = "select ticket_id,action,deal_code from sys_tickets where status = 1 AND is_stop=0 AND is_sys_action=0";
        $data = $this->db->get_all($sql);

        foreach ($data as $val) {
            if (in_array($val['action'], $this->type_arr)) {
                $this->begin_trans();
                $ticket_form = $this->get_ticket_data($val['ticket_id']);
                $ret = $this->$val['action']($val['deal_code'], $ticket_form);
                if ($ret['status'] < 1) {
                    $this->rollback(); //处理失败暂时不记录日志
                    continue;
                }

                $status = $this->handler_ticket($val['ticket_id']);
                if ($status === false) {
                    $this->rollback();
                } else {
                    $this->commit();
                }
            }
        }
    }

    function exec_ticket($ticket_id) {
        $sql = "select ticket_id,action,deal_code,status,is_stop,is_sys_action from sys_tickets where ticket_id=:ticket_id";
        $data = $this->db->get_row($sql, array(':ticket_id' => $ticket_id));
        if ($data['status'] != 1 || $data['is_stop'] != 0 || $data['is_sys_action'] != 0) {
            return $this->format_ret(1, '', '状态异常');
        }
        
        if (in_array($data['action'], $this->type_arr)) {
            $this->begin_trans();
            $ticket_form = $this->get_ticket_data($data['ticket_id']);
            $ret = $this->$data['action']($data['deal_code'], $ticket_form);
            if ($ret['status'] < 1) {
                $this->rollback(); //处理失败暂时不记录日志
                return $ret;
            }

            $status = $this->handler_ticket($data['ticket_id'],$ret['data']);
            if ($status === false) {
                $this->rollback();
            } else {
                $this->commit();
            }
        }
        
        return $this->format_ret(1);
    }

    function get_ticket_data($ticket_id) {
        $sql = "select ticket_form from api_qn_tickets where ticket_id=:ticket_id";
        $ticket_form = $this->db->get_value($sql, array(':ticket_id' => $ticket_id));
        return json_decode($ticket_form, true);
    }

    /* 退货退款任务 */

    function official_refund_with_goods_return($deal_code, $ticket_form) {
        $params_info = array();
        /* $params_info['tid'] = $deal_code;
          $params_info['refund_id'] = $ticket_form['refundNo']; //检查是否转退单 */

        if(!empty($ticket_form['refundNo'])){
            $check_status = $this->check_refund_id($ticket_form['refundNo']);
            if ($check_status == 0) {
                return load_model('oms/TranslateRefundModel')->translate_refund_api($ticket_form['refundNo']);
            } else if ($check_status == 1) {
                return $this->format_ret(1, '', '已经转单!');
            }
        }

//        $refundFee = $ticket_form['refund']['refundFee'];
//        $reason = $ticket_form['refund']['reason'];
//        $sku_arr = array_column($params_info['mx'], 'sku');
//        $sell_record_code = $this->get_record_code_by_deal_code($deal_code, $sku_arr);
//        if (empty($sell_record_code)) {
//            return $this->format_ret(-1, '', '未找到单据!');
//        } //检查是否转退单
        //关联订单信息
        
        
        
        $record_data = $this->get_api_order($deal_code);
        if(empty($record_data)){
            //直接生成退单
                 $refundFee = $ticket_form['refund']['refundFee'];
                $reason = $ticket_form['refund']['reason'];
                $sku_arr = array();
             if (!empty($ticket_form['refund']['oids'])) {
                 $params_info_mx = $this->get_refund_detail($deal_code, $ticket_form['refund']['oids']);
                 $sku_arr = array_column($params_info_mx, 'sku');
             }

             $sell_record_code = $this->get_record_code_by_deal_code($deal_code, $sku_arr);
             if (empty($sell_record_code)) {
                 return $this->format_ret(-1, '', '未找到单据!');
             }

             $params_info['return_pay_code'] = $ticket_form['refund']['buyerAlipay'];
             $params_info['refund_id'] = $ticket_form['refundNo']; //检查是否转退单
             if (isset($ticket_form['refundLogistics'])) {
                 $params_info['return_express_no'] = $ticket_form['refundLogistics'];
             }

             $params_info['return_type_money'] = 3;
             //不支持订单拆分情况
             $ret = load_model('oms/SellReturnOptModel')->create_return($params_info, $sell_record_code, 3, $reason, $refundFee);
             //加日志；
             if($ret['status']>0){
                  return $this->format_ret(1, array('record_code'=>$ret['data']));
             }
        }
            //通过接口退单生成退单
        $params_info = array(
            'tid' => $deal_code,
            'refund_id' => $ticket_form['refundNo'],
            'source' => $record_data['source'],
            'shop_code' => $record_data['shop_code'],
            'seller_nick' => $record_data['seller_nick'],
            'buyer_nick' => $record_data['buyer_nick'],
            'has_good_return' => true,
            'status' => 1,
            'is_change' => 0,
            'refund_fee' => $ticket_form['refund']['refundFee'], // 金额有可能不准确（可能跟明细汇总金额不相等）
            'refund_desc' => $ticket_form['refundDesc'],
            'refund_reason' => $ticket_form['refund']['reason'],
            'refund_express_no' => isset($ticket_form['refundLogistics']) ? $ticket_form['refundLogistics'] : '',
            'return_pay_code' => $ticket_form['refund']['buyerAlipay']
        );
        //明细
        $params_info['mx'] = $this->get_refund_detail($deal_code, $ticket_form['refund']['oids'], 'refunds_goods_return');
        /* if (isset($ticket_form['refundLogistics'])) {
          $params_info['return_express_no'] = $ticket_form['refundLogistics'];
          } */
        //不支持订单拆分情况
        $ret = load_model('oms/TranslateRefundModel')->translate_refund_qn($params_info);
        //$ret = load_model('oms/SellReturnOptModel')->create_return($params_info, $sell_record_code, 3, $reason, $refundFee);
        //加日志；
        return $ret;
    }

    function official_refund($deal_code, $ticket_form) {
        $params_info = array();
        $params_info['refund_id'] = $ticket_form['refundNo']; //检查是否转退单

        $check_status = $this->check_refund_id($params_info['refund_id']);
        if ($check_status == 0) {
            return load_model('oms/TranslateRefundModel')->translate_refund_api($params_info['refund_id']);
        } else if ($check_status == 1) {
            return $this->format_ret(1, '', '已经转单!');
        }

        $refundFee = $ticket_form['refund']['refundFee'];
        $reason = $ticket_form['refund']['reason'];

        $sku_arr = array();
        if (!empty($ticket_form['refund']['oids'])) {
            $params_info_mx = $this->get_refund_detail($deal_code, $ticket_form['refund']['oids']);
            $sku_arr = array_column($params_info_mx, 'sku');
        }

        $sell_record_code = $this->get_record_code_by_deal_code($deal_code, $sku_arr);
        if (empty($sell_record_code)) {
            return $this->format_ret(-1, '', '未找到单据!');
        }

        $params_info['return_pay_code'] = $ticket_form['refund']['buyerAlipay'];
        $params_info['refund_id'] = $ticket_form['refundNo']; //检查是否转退单
        if (isset($ticket_form['refundLogistics'])) {
            $params_info['return_express_no'] = $ticket_form['refundLogistics'];
        }

        $params_info['return_type_money'] = 1;
        //不支持订单拆分情况
        $ret = load_model('oms/SellReturnOptModel')->create_return($params_info, $sell_record_code, 1, $reason, $refundFee);
        //加日志；
        if($ret['status']>0){
             return $this->format_ret(1, array('record_code'=>$ret['data']));
        }
        
        return $ret;
    }

    function official_trade_item_exchange($deal_code, $ticket_form) {
        $params_info = array();
        //   $params_info['refund_id'] = $ticket_form['refundNo']; //检查是否转退单
//        $check_status = $this->check_refund_id($params_info['refund_id']);
//        if ($check_status == 0) {
//            return load_model('oms/TranslateRefundModel')->translate_refund_api($params_info['refund_id']);
//        } else if ($check_status == 1) {
//            return $this->format_ret(1, '', '已经转单!');
//        }
        // $refundFee = $ticket_form['exchange']['refundFee'];
        $refundFee = 0;
        foreach ($ticket_form['exchange']['exchangeItem'] as $val) {
            $refundFee +=$val['price'];
        }

        $reason = '换货';
        $params_info['mx'] = $this->get_refund_detail($deal_code, $ticket_form['refund']['oids']);
        $sku_arr = array_column($params_info['mx'], 'sku');
        $sell_record_code = $this->get_record_code_by_deal_code($deal_code, $sku_arr);
        if (empty($sell_record_code)) {
            return $this->format_ret(-1, '', '未找到单据!');
        }

        $params_info['return_pay_code'] = $ticket_form['exchange']['buyerAlipay'];
        //$params_info['refund_id'] = $ticket_form['refundNo']; //检查是否转退单
        if (isset($ticket_form['refundLogistics'])) {
            $params_info['return_express_no'] = $ticket_form['refundLogistics'];
        }

        $params_info['adjust_money'] = isset($ticket_form['exchange']['compensation']) ? $ticket_form['exchange']['compensation'] : 0;


        $ret = load_model('oms/SellReturnOptModel')->create_return($params_info, $sell_record_code, 3, $reason, $refundFee);
        //加日志；
        //设置成退货单      $params_info['is_exchange_goods'] = 1;
       if($ret['status']>0){
             return $this->format_ret(1, array('record_code'=>$ret['data']));
        }
        
        return $ret;
    }

    /* 修改订单收货地址 */

    function official_trade_modify_address($deal_code, $ticket_form) {
        //查询订单号
        $record_code_arr = $this->get_record_code_by_deal_code($deal_code, array(), 'record');
        if (empty($record_code_arr)) {
            return $this->format_ret(-1, '', '未找到单据(可能已作废、已确认、已完成)!');
        }
        $record_model = load_model('oms/SellRecordModel');
        $this->begin_trans();
        foreach ($record_code_arr as $val) {
            $record_data = $record_model->get_record_by_code($val['sell_record_code']);
            //地址匹配
            $adds_data = $this->match_addr($ticket_form['changeAddress']);
            $adds_data['receiver_name'] = empty($ticket_form['name']) ? $record_data['receiver_name'] : $ticket_form['name'];
            $adds_data['receiver_phone'] = empty($ticket_form['phone']) ? $record_data['receiver_phone'] : $ticket_form['phone'];
            $ret = $record_model->update_record_data($val['sell_record_code'], $adds_data);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1, '', '修改失败');
            }
            //记录订单修改地址日志
            $str = '地址：' . $adds_data['receiver_address'] . " 修改为 " . $adds_data['receiver_address'] . '；';
            if (!empty($ticket_form['name'])) {
                $str .= "收货人：" . $record_data['receiver_name'] . '修改为' . $ticket_form['name'] . '；';
            } else if (!empty($ticket_form['phone'])) {
                $str .= "电话：" . $record_data['receiver_phone'] . '修改为' . $ticket_form['phone'] . '；';
            }
            $record_model->add_action($val['sell_record_code'], "修改发货信息", $str);
        }
        $this->commit();
        return $this->format_ret(1, '', '修改成功');
    }

    function handler_ticket($ticket_id,$record_data) {
        $where = " ticket_id='{$ticket_id}' AND status = 1 AND is_stop = 0 AND is_sys_action=0 ";
        if(isset($record_data['record_code'])){
              $data['record_code'] = $record_data['record_code'];
        }
        
        $data['is_sys_action'] = 1;
        $status = $this->update_exp('sys_tickets', $data, $where);
        $num = $this->affected_rows();
        if ($num != 1 || !$status) {
            return false;
        }
        return true;
    }

    function get_record_code_by_deal_code($deal_code, $sku_arr = array(), $type = '') {
        $sql = "select DISTINCT d.sell_record_code from 
                oms_sell_record r INNER JOIN 
                oms_sell_record_detail d ON r.sell_record_code=d.sell_record_code where
                d.deal_code='{$deal_code}' ";
        if ($type == 'record') {
            $sql .= " AND r.order_status = 0 ";
        } else {
//            $sql .= " AND r.order_status=1 AND r.shipping_status=3 ";
        }
        $sql_values = array();
        if (!empty($sku_arr)) {
            //$sku_str = "'" . implode("','", $sku_arr) . "'";
            //,d.num,d.return_num,d.sku
            $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
            $sql .=" AND d.sku IN ({$sku_str}) ";
        }
        $data = $this->db->get_all($sql, $sql_values);
        $sell_record_code = '';
        if (!empty($data)) {
            if ($type == 'record') {
                $sell_record_code = $data;
            } else {
                $sell_record_code = $data[0]['sell_record_code'];
            }
        }
        return $sell_record_code;
    }

    function get_sku_by_barcode($barcode_arr) {
        //暂时不支持套餐
        $data = load_model('prm/SkuModel')->convert_barcode($barcode_arr);
        $sku_arr = array();
        foreach ($data as $val) {
            $sku_arr[] = $val['sku'];
        }
        return $sku_arr;
    }

    function get_sku_by_skuId($skuId) {
        
    }

    function get_refund_detail($tid, $oid_arr = array(), $type = '') {
        $sql = "select * from api_order_detail where tid='{$tid}' ";
        if (count($oid_arr) == 1) {
            if ($oid_arr[0] == $tid) {
                $oid_arr = array();
            }
        }
        if (!empty($oid_arr)) {
            $oid_str = "'" . implode("','", $oid_arr) . "'";
            $sql.=" AND oid in({$oid_str})";
        }
        $api_data_mx = $this->db->get_all($sql);
        load_model('oms/TranslateRefundModel')->check_barcode($api_data_mx);
        $detail_data = array();
        foreach ($api_data_mx as $val) {
            if ($type == 'refunds_goods_return') { //退款退货
                $_row = array('tid' => $tid);
                $_row['goods_barcode'] = $val['goods_barcode'];
                $_row['num'] = $val['num'];
                $_row['refund_price'] = $val['payment']; //可能不准确
            } else { //退款
                $sql = "select sku,barcode from goods_barcode where barcode = :barcoder ";
                $db_barcode = ctx()->db->get_all($sql, array(':barcoder' => $val['goods_barcode']));
                $barcode_arr = load_model('util/ViewUtilModel')->get_map_arr($db_barcode, 'barcode', 0, 'sku', 1);
                $s_goods_barcode = strtolower($val['goods_barcode']);
                $b_goods_barcode = strtoupper($val['goods_barcode']);
                $_sku = isset($barcode_arr[$s_goods_barcode]) ? $barcode_arr[$s_goods_barcode] : null;
                $_sku = isset($barcode_arr[$b_goods_barcode]) ? $barcode_arr[$b_goods_barcode] : $_sku;

                $_row = array('deal_code' => $tid);
                $_row['sku'] = $_sku;
                $_row['return_num'] = $val['num'];
                $_row['return_price'] = $val['payment']; //可能不准确
            }
            $detail_data[] = $_row;
        }

        return $detail_data;
    }

    function check_refund_id($refund_id) {
        $sql = "select status,is_change from api_refund where  refund_id='{$refund_id}'";
        $row = $this->db->get_row($sql);
        if (empty($row)) {
            return 100;
        }
        if ($row['status'] == 1 && $row['is_change'] == 1) {
            return 1; //已经转单
        }
        if ($row['status'] == 1 && $row['is_change'] != 1) {
            return 0; //未转单
        }
    }

    //地址匹配
    function match_addr($address_arr) {
        $data = array();
        $receiver_address = $address_arr['country'] . $address_arr['state'] . $address_arr['city'] . $address_arr['district'] . $address_arr['town'];
        if ((!empty($address_arr['receiver_address']) && !preg_match('/[\x{4e00}-\x{9fa5}]/u', $address_arr['receiver_address'])) || $address_arr['receiver_country'] == '海外') {
            $data['receiver_country'] = '250';
            $data['receiver_province'] = '250000';
            $data['receiver_city'] = '25000000';
            $data['receiver_district'] = 0;
            $data['receiver_street'] = '';
            $data['receiver_address'] = $receiver_address;
            $data['receiver_addr'] = $receiver_address;
        } else {
            $obj_name = "oms/trans_order/AddrCommModel";
            $adds = array(
                'receiver_country' => $address_arr['country'],
                'receiver_province' => $address_arr['state'],
                'receiver_city' => $address_arr['city'],
                'receiver_district' => $address_arr['district'],
                'receiver_street' => $address_arr['town'],
                'receiver_address' => $receiver_address,
                'receiver_addr' => $address_arr['town']
            );
            //地址匹配
            $addr_ret = load_model($obj_name)->match_addr($adds);

            if ($addr_ret['status'] < 0) {
                return $addr_ret;
            }
            $addr_ret = $addr_ret['data'];
            if (empty($addr_ret['receiver_province']) || empty($addr_ret['receiver_city'])) {
                return $this->format_ret(-30, '', '地址匹配找不到省市信息');
            }
            $data['receiver_country'] = $addr_ret['receiver_country'];
            $data['receiver_province'] = $addr_ret['receiver_province'];
            $data['receiver_city'] = $addr_ret['receiver_city'];
            $data['receiver_district'] = !empty($addr_ret['receiver_district']) ? $addr_ret['receiver_district'] : 0;
            $data['receiver_street'] = isset($addr_ret['receiver_street']) ? $addr_ret['receiver_street'] : '';
            $data['receiver_addr'] = $addr_ret['receiver_addr'];
            $data['receiver_address'] = $addr_ret['receiver_address'];
        }
        return $data;
    }

    function get_api_order($tid) {
        $sql = "SELECT * FROM api_order WHERE tid = :tid";
        $row = $this->db->get_row($sql, array(':tid' => $tid));
        return $row;
    }

}
