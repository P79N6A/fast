<?php
require_model('oms/TradeTransferModel', true);

class TaobaoTransferModel extends TradeTransferModel {

    protected $source = 9;

    protected $tables = array('trade' => 'oms_taobao_record', 'items' => 'oms_taobao_record_detail', 'promotion' => 'oms_taobao_promo');

    public function __construct() {
        parent::__construct();
        $this->_pay_code = 'alipay';
        $this->_table_key = 'tid';
    }

    protected function get_trade($recordId){
        $this->trade = $this->db->get_row("SELECT * FROM oms_taobao_record WHERE `record_id`=:record_id  AND status IN ('WAIT_SELLER_SEND_GOODS')",array('record_id'=>$recordId));
    }

    protected function get_items($recordId){
        $this->tradeItems = $this->db->get_all("SELECT * FROM oms_taobao_record_detail WHERE `pid`=:pid",array('pid'=>$recordId));
    }

    protected function remove_refund_items() {
        foreach ($this->tradeItems as $key => $_items) {
            if ('SUCCESS' == $_items['refund_status']) {
                unset($this->tradeItems[$key]);//已经退款成功,废掉此明细
            }
        }
    }

    protected function transfer_trade_custom() {
        $this->order['deal_code'] = $this->trade['tid'];
        $this->order['buyer_name'] = $this->trade['buyer_nick'];
        $this->order['paid_money'] = $this->trade['payment'];
        $this->order['receiver_name'] = $this->trade['receiver_name'];
        $this->order['receiver_addr'] = $this->trade['receiver_address'];
        $this->order['receiver_address'] = $this->trade['receiver_state'].' '.$this->trade['receiver_city'].' '.$this->trade['receiver_district'].' '.$this->trade['receiver_address'];
        $this->order['receiver_zip_code'] = $this->trade['receiver_zip'];
        $this->order['receiver_mobile'] = $this->trade['receiver_mobile'];
        $this->order['receiver_phone'] = $this->trade['receiver_phone'];
        $this->order['receiver_email'] = $this->trade['buyer_email'];
        $this->order['cod_fee'] = round($this->trade['buyer_cod_fee'], 2);
        $this->order['pay_type'] = ('cod' == $this->trade['type'] ? 1 : 0);
        $this->order['record_time'] = $this->trade['created'];
        $this->order['seller_remark'] = $this->trade['seller_memo'];
        $this->order['buyer_remark'] = $this->trade['buyer_message'];
        $this->order['vip_code'] = $this->trade['buyer_nick'];

        //更改支付状态
        if ($this->trade['status'] == 'WAIT_SELLER_SEND_GOODS' && 'cod' != $this->trade['type']) {
            //$this->order['pay_id'] = get_pay_id_by_code('alipay');
            $this->order['pay_code'] = 'alipay';
            $this->order['pay_status'] = '2';
            $this->order['is_pay_time'] = empty($this->trade['pay_time']) ? '' : $this->trade['pay_time'];
        }

        if (!empty($this->trade['send_time'])) {
            $this->order['is_plan_send_time'] = $this->trade['send_time'];
        }

        //解析省市区地址
        $region_arr['province'] = $this->trade['receiver_state'];
        $region_arr['city'] = $this->trade['receiver_city'];
        $region_arr['district'] = $this->trade['receiver_district'];
        $region = load_model('base/RegionModel')->get_region_id_by_name($region_arr);

        $this->order['receiver_country'] = 1;
        $this->order['receiver_province'] = $region['receiver_province'];
        $this->order['receiver_city'] = $region['receiver_city'];
        $this->order['receiver_district'] = $region['receiver_district'];

        /* /*TODO货到付款
          if ('cod' == $trade['type']) {
          //获得所有的支付方式
          $payment = loadModel('payment_model', 'package/payment')->get_payment();
          $order_info['pay_code'] = 'cod';
          $order_info['pay_id'] = isset($payment[$order_info['pay_code']]['pay_id']) ? $payment[$order_info['pay_code']]['pay_id'] : 0;
          $order_info['pay_name'] = isset($payment[$order_info['pay_code']]['pay_name']) ? $payment[$order_info['pay_code']]['pay_name'] : '货到付款';
          $order_info['pay_status'] = 0;
          $order_info['payment'] = 0;
          $order_info['is_cod'] = 1;
          } else if ('WAIT_SELLER_SEND_GOODS' == $trade['status']) {
          $order_info['pay_status'] = 2;
          } else {
          $order_info['pay_status'] = 0;
          $order_info['payment'] = 0;
          }
         *
         */
    }

    protected function transfer_item_custom(&$orderItem, $tradeItem){
        //打算取代_abstract_get_items_params(&$items)
        //$orderItem['outer_iid'] = $tradeItem['outer_iid'];
        $orderItem['outer_sku_id'] = isset($tradeItem['outer_sku_id']) && !empty($tradeItem['outer_sku_id']) ? $tradeItem['outer_sku_id'] : $tradeItem['outer_iid'];
        //$orderItem['sku_name'] = $tradeItem['sku_properties_name'];
        //$orderItem['goods_name'] = $tradeItem['title'];
        $orderItem['price'] = $tradeItem['price'];
        $orderItem['adjust_fee'] = $tradeItem['adjust_fee'];
        $orderItem['discount_fee'] = $tradeItem['discount_fee'];
        $orderItem['part_mjz_discount'] = $tradeItem['part_mjz_discount'];
        $orderItem['num'] = $tradeItem['num'];
        $orderItem['sub_deal_code'] = $tradeItem['oid'];
        $orderItem['is_gift'] = isset($tradeItem['is_gift']) ? $tradeItem['is_gift'] : 0;
    }

    protected function transfer_trade_price(){
        $sell_money = $goods_money = $discount_amount = $pay_money = $adjust_fee = $goods_rebate_money = $other_rebate_money = 0;

        foreach ($this->orderItems as $key => &$orderItem) {
            $orderItem['sell_price'] = $orderItem['price'];

            //商品单价=淘宝商品单价-淘宝系统折让【如打折，VIP，满就送等】-卖家手工调整金额
            //$adjust_fee += $orderItem['adjust_fee'];
            $orderItem['adjust_money'] = $orderItem['discount_fee'] - $orderItem['adjust_fee'] + $orderItem['part_mjz_discount'];
            $orderItem['count_money'] = $orderItem['num'] * $orderItem['price'] - $orderItem['adjust_money'];
            $orderItem['goods_price'] = $orderItem['count_money'] / $orderItem['num'];
            $orderItem['rebate'] = $orderItem['goods_price'] / $orderItem['sell_price'];

            //淘宝订单明细只有一个的时候明细中的实付款包含运费
            //if(1 == count($this->orderItems)) {
            //    $orderItem['payment'] = $orderItem['payment'] - $this->order['post_fee'];
            //}

            $pay_money += $orderItem['count_money'];
            $sell_money += $orderItem['num'] * $orderItem['sell_price'];
            //$goods_money += $orderItem['num'] * $orderItem['goods_price'];
            $goods_money += $orderItem['count_money'];
            $goods_rebate_money += $orderItem['adjust_money'];

        }

        $this->order['sell_money'] = $sell_money; //市场价之和
        $this->order['goods_money'] = $goods_money; //实际支付的商品金额之和
        $this->order['express_money'] = $this->trade['post_fee'];
        $this->order['delivery_money'] = $this->trade['cod_fee']; //货到付款算在买家身上
        //$this->order['pay_money'] = $pay_money + $this->trade['post_fee'] + $this->trade['cod_fee'];

        //计算订单总金额
        $this->order['order_money'] = $sell_money + $this->trade['post_fee'] + $this->trade['cod_fee'];

        //订单其他折让总额
        $this->order['other_rebate_money'] = $other_rebate_money;

        $this->order['goods_rebate_money'] = $goods_rebate_money;

        //计算订单应付金额
        $this->order['payable_money'] = $goods_money + $this->trade['post_fee'] + $this->trade['cod_fee'] - $this->order['other_rebate_money'];

    }

    protected function transfer_trade_express() {
        $params['deal_code'] = $this->trade['tid'];
        $params['vip_code'] = $this->trade['buyer_nick'];//会员名称，注意和收货人区别
        $params['deal_note'] = $this->trade['seller_memo'];
        $params['trade_express_code'] = $this->trade['shipping_type'];
        $params['shop_id'] = $this->order['shop_id'];
        $params['is_cash_on_delivery'] = ('cod' == $this->trade['type'] ? 1 : 0);
        return $params;
    }

    protected function transfer_trade_settlement($sell_record_id) {
        //处理销售订单结算表
        /*$d = array();
        $d['sell_record_id'] = $sell_record_id;
        $d['record_code'] = $this->order['record_code'];
        $d['pay_code'] = 'alipay';
        $d['pay_id'] = get_pay_id_by_code('alipay');
        $d['pay_name'] = get_pay_name_by_id($d['pay_id']);
        $d['money'] = $this->order['paid_money'];
        $d['remark'] = '';

        $this->db->insert('order_sell_record_settlement', $d);*/
    }


    /**
     * 订单转入的时候处理额外信息
     * @param $sell_record_id
     * @return array
     * @throws Exception
     */
    protected function transfer_other($sell_record_id) {

        //品牌特卖直接作废订单
        /*if ((isset($this->trade['is_force_wlb']) && $this->trade['is_force_wlb'] == 1) ||
            (isset($this->trade['is_lgtype']) && $this->trade['is_lgtype'] == 1)) {

            $ret = load_model('oms/SellRecordModel')->opt_cancel($sell_record_id, true);
            if ($ret['status'] != 1) {
                throw new Exception($ret["message"]);
            }
        }*/
        
        //退款判断
        /*$params = array('sell_record_id'=>$sell_record_id);
        load_model('oms/SellRecordModel')->do_refund_mode($params, true);*/

        return $this->return_value(1);
    }

    /**
     * 订单转入的时候处理退单信息, 如果有退单的话,转入的订单直接挂起
     * @param $order_id
     * @param $deal_code
     * @return mixed
     */
    protected function remove_refund_trade($order_id, $deal_code) {
        $sql = "SELECT oid FROM sys_info.jdp_tb_refund WHERE tid=:tid and status <> 'CLOSED'";
        $result_refund = $this->db->get_all($sql, array('tid'=>$deal_code));

        if (empty($result_refund)) {
            return $this->return_value(1);
        }

        $oids = array();
        foreach ((array) $result_refund as $_refund) {
            $oids[] = $_refund['oid'];
        }
        $oids = "'" . implode("','", $oids) . "'";

        //取trade中的items
        $sql = "SELECT outer_sku_id FROM order_taobao_record_detail WHERE tid=:tid AND oid IN ($oids)";
        $result_items = $this->db->get_all($sql, array(':tid'=>$deal_code));

        if (empty($result_items)) {
            return $this->return_value(1);
        }

        $skus = array();
        foreach ((array) $result_items as $_items) {
            if(empty($_items['outer_sku_id'])) {
                return $this->return_value(-1, '商家编码为空');
            }

            $skus[] = $_items['outer_sku_id'];
        }
        $skus = "'" . implode("','", $skus) . "'";

        //获得商品信息goods
        $sql = "SELECT sell_record_detail_id FROM order_sell_record_detail WHERE pid= :pid AND sku IN ($skus)";
        $result_goods = $this->db->get_all($sql, array(':pid'=>$order_id));

        if (empty($result_goods) || !is_array($result_goods)) {
            return $this->return_value(1);
        }

        //更新订单有退款申请标志
        //$this->db->query('UPDATE order_info SET refund_status=1 WHERE order_id=:id', array(':id' => $order_id));

        //$params['type'] = 'wtzph';
        //$params['order_id'] = $order_id;
        //$params['action_note'] = '此订单有退款申请，取消发货';
        //$result = loadModel('order_process_model', 'order')->do_wtzph_process($params, true, true);

        //挂起订单
        $params['type'] = 'gq';
        $params['order_id'] = $order_id;
        $params['gq_note'] = '此订单有退款申请：' . $deal_code;
        $params['action_note'] = '此订单有退款申请：' . $deal_code;
        $result = load_model('order/sell_record')->do_pending_mode($params, true);

        return $this->return_value(1);
    }

}
