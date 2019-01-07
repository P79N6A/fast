<?php

require_model('op/GiftStrategy/GiftStrategyAbsModel');

class GiftStrategyStandardModel extends GiftStrategyAbsModel {
    /*
     * 检查策略规则的会员是否是否赠送
     */

    protected function check_rule_customer_is_send() {
        $strategy_code = &$this->strategy_data['strategy_code'];
        $buyer_name = &$this->sell_record['buyer_name'];
        $sql = "select * from op_gift_strategy_customer where strategy_code=:strategy_code AND buyer_name=:buyer_name ";
        $data = $this->db->get_row($sql, array(':strategy_code' => $strategy_code, ':buyer_name' => $buyer_name));
        if (!empty($data)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*
     * 满送
     */

    protected function check_rule_is_full() {
        $strategy_rule = &$this->strategy_rule;
         $this->is_buy_enough_num = 0; //满足赠品倍数
        $buy_money = $this->get_sell_record_buy_money($strategy_rule['is_contain_delivery_money']);
        if ($buy_money > $strategy_rule['money_min'] && $buy_money <= $strategy_rule['money_max']) {
            $this->is_buy_enough_num = 1;
        }
        $ret = ($this->is_buy_enough_num > 0) ? $this->format_ret(1) : $this->format_ret(-1, '', "金额不满足({$strategy_rule['op_gift_strategy_detail_id']})");
        return $ret;
    }

    /*
     * 买送
     */

    protected function check_rule_is_buy() {

        $strategy_rule = &$this->strategy_rule;
        $rule_buy_goods = $this->get_strategy_rule_buy_goods($strategy_rule['op_gift_strategy_detail_id']);

        //goods_condition 0固定商品条件，1随机商品条件 2买全场
        if (empty($rule_buy_goods) && $strategy_rule['goods_condition'] != 2) {
              return $this->format_ret(-1,'',"不是指定活动商品({$strategy_rule['op_gift_strategy_detail_id']})");
        }

        $buy_goods = $this->get_sell_record_buy_goods();
        $this->is_buy_enough_num = 0; //满足条件倍数

        if ($strategy_rule['goods_condition'] == 0) {
            //固定购买
            $this->is_buy_enough_num = $this->check_buy_goods_is_contain($rule_buy_goods, $buy_goods);
        } else if ($strategy_rule['goods_condition'] == 1) {
            //随机购买
            $this->is_buy_enough_num = $this->check_buy_goods_is_contain_by_num($buy_goods, $rule_buy_goods, $strategy_rule['buy_num']);
        } else if ($strategy_rule['goods_condition'] == 2) {
            $this->is_buy_enough_num = 1;
        }
        $ret = ($this->is_buy_enough_num > 0) ? $this->format_ret(1) : $this->format_ret(-1, '', "购买指定商品不足({$strategy_rule['op_gift_strategy_detail_id']})");
        return $ret;
    }

    /*
     * 获取规则的赠品
     */

    protected function set_gift_by_rule() {

        $strategy_rule = &$this->strategy_rule;

        $gift_data = $this->get_rule_gift($strategy_rule['op_gift_strategy_detail_id']);


        if (empty($gift_data)) {
            //没设置赠品
           return $this->format_ret(-1,'','没设置赠品');
        }

        $status = FALSE;
        if ($strategy_rule['give_way'] == 1) {    //随机送
            //满送叠加送
            $gift_num = $strategy_rule['gift_num'];
            if ($strategy_rule['is_repeat'] == 1 && $strategy_rule['type'] == 0) {
                $gift_num = $this->is_buy_enough_num * $strategy_rule['gift_num'];
            }
            $status = $this->send_random_gift($gift_num, $gift_data);
        } else {  //固定送
            //满送叠加送
            if ($strategy_rule['is_repeat'] == 1 && $strategy_rule['type'] == 0) {
                $this->set_repeat_gift($gift_data);
            }
            $status = $this->send_fixed_gift($gift_data);
        }

        return ($status===false)?$this->format_ret(-1,'','赠品库存不足'):$this->format_ret(1);
    }

    /*
     * 获取是否固定会员送
     */

    protected function get_is_fixed_customer() {
        return ($this->strategy_rule['is_fixed_customer'] == 1) ? true : false;
    }

    /*
     * 获取店铺可用的赠品策略
     */

    protected function get_strategy_by_trade() {
        $sql_main = "select * from op_gift_strategy where 1 ";
        $sql_main .= " AND status=:status";
        $sql_main .= "  AND  shop_code=:shop_code";
        $sql_main .= " AND start_time<=:pay_time";
        $sql_main .= " AND end_time>=:pay_time";

        $sql_values[':status'] = 1;
        $sql_values[':shop_code'] = $this->sell_record['shop_code'];
        

        $sql_values[':pay_time'] = ($this->sell_record['pay_type'] == 'cod')?
                      strtotime($this->sell_record['record_time']):strtotime($this->sell_record['pay_time']);
    

        $data = $this->db->get_all($sql_main, $sql_values);

        $ret_status = 1;
        if (empty($data)) {
            $ret_status = -1;
        }
        return $this->format_ret($ret_status, $data);
    }
   /*
     * 通过从策略获取规则
     */

    protected function get_strategy_rule_by_strategy_code() {
        static $strategy_rule_data = NULL;
        $strategy_code = &$this->strategy_data['strategy_code'];
        if (!isset($strategy_rule_data[$strategy_code])) {
            $sql = "SELECT * from op_gift_strategy_detail where strategy_code=:strategy_code AND status=1 order by level desc ";
            $strategy_rule_data[$strategy_code] = $this->db->get_all($sql, array(':strategy_code' => $this->strategy_data['strategy_code']));
        }
        return $strategy_rule_data[$strategy_code];
    }
    
     /*
     * 检查是否同1分组互斥
     */
     protected function check_rule_is_sort($rule_sort,$sort){
         return TRUE;
     }
    
}
