<?php

require_model('op/GiftStrategy/GiftStrategyAbsModel');

class GiftStrategyEnterpriseModel extends GiftStrategyAbsModel {
    /*
     * 检查策略规则的会员是否是否赠送
     */

    protected $success_rule_range = array(); //满足规则的范围
    
    protected function check_rule_customer_is_send() {
        $strategy_code = &$this->strategy_data['strategy_code'];
        $buyer_name = $this->sell_record['buyer_name'];
        
        //淘宝需要解密
        if($this->sell_record['sale_channel_code']=='taobao'){ 
           $buyer_name = load_model('crm/CustomerOptModel')->get_buyer_name_by_code($this->sell_record['customer_code']);
        }

        $detail_id = &$this->strategy_rule['op_gift_strategy_detail_id'];
        $sql = "select * from op_gift_strategy_customer where strategy_code=:strategy_code AND buyer_name=:buyer_name AND op_gift_strategy_detail_id=:op_gift_strategy_detail_id ";
        $sql_values = array(':strategy_code' => $strategy_code, ':buyer_name' => $buyer_name, ':op_gift_strategy_detail_id' => $detail_id);
        $data = $this->db->get_row($sql, $sql_values);
        if (!empty($data)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*
     * 获取是否固定会员送
     */

    protected function get_is_fixed_customer() {
        $sql = "select count(1) from op_gift_strategy_customer where strategy_code=:strategy_code  AND op_gift_strategy_detail_id=:op_gift_strategy_detail_id ";
        $sql_values = array();
        $sql_values[':strategy_code'] = $this->strategy_data['strategy_code'];
        $sql_values[':op_gift_strategy_detail_id'] = $this->strategy_rule['op_gift_strategy_detail_id'];
        $num = $this->db->get_value($sql, $sql_values);
        return ($num > 0) ? TRUE : FALSE;
    }

    protected function check_rule_is_full() {
         $strategy_rule = &$this->strategy_rule;
        $is_buy = $this->check_trade_is_buy_goods();
        if ($is_buy === FALSE) {
              return $this->format_ret(-1,'',"不是指定活动商品({$strategy_rule['op_gift_strategy_detail_id']})");
        }
        $this->is_buy_enough_num = 0; //满足赠品倍数

        $buy_money = $this->get_sell_record_buy_money();
        if ($strategy_rule['range_type'] == 1) {//倍增加
            $this->is_buy_enough_num = (int) floor($buy_money/$strategy_rule['doubled']);
        } else {
            $is_success = $this->check_rule_rang($buy_money);
            $this->is_buy_enough_num = ($is_success > 0) ? 1 : 0;
        }
       //  return $this->format_ret(-1,'',"不是指定活动商品({$strategy_rule['op_gift_strategy_detail_id']})");
        $ret = ($this->is_buy_enough_num > 0) ? $this->format_ret(1) : $this->format_ret(-1, '', "金额不满足({$strategy_rule['op_gift_strategy_detail_id']})");
        return $ret;
    }

    /*
     * 购买金额所在范围
     */

    private function check_rule_rang($rang_num) {
        $strategy_rule = &$this->strategy_rule;

        $rule_range_data = $this->get_strategy_rule_range($strategy_rule['op_gift_strategy_detail_id']);
        //$rule_range
        $this->success_rule_range = array();
        foreach ($rule_range_data as &$val) {
            if ($this->strategy_data['strategy_new_type'] == 1) {
                //调整成大于等于
                if ($rang_num >= $val['range_start'] && $rang_num <= $val['range_end']) {
                    $this->success_rule_range = $val;
                    break;
                }
            } else {
                //原来大于
                if ($rang_num > $val['range_start'] && $rang_num <= $val['range_end']) {
                    $this->success_rule_range = $val;
                    break;
                }
            }
        }
        
        
        return empty($this->success_rule_range) ? FALSE : TRUE;
    }

    /*
     * 获取规则金额范围
     */

    private function get_strategy_rule_range($op_gift_strategy_detail_id) {
        static $strategy_rule_range = null;
        if (!isset($strategy_rule_range[$op_gift_strategy_detail_id])) {
            $sql = "select * from op_gift_strategy_range where op_gift_strategy_detail_id=:op_gift_strategy_detail_id";
            $data = $this->db->get_all($sql, array(':op_gift_strategy_detail_id' => $op_gift_strategy_detail_id));
            $strategy_rule_range[$op_gift_strategy_detail_id] = $data;
        }
        return $strategy_rule_range[$op_gift_strategy_detail_id];
    }

    /*
     * 满赠是否买指定商品
     */

    private function check_trade_is_buy_goods() {
        $strategy_rule = &$this->strategy_rule;
        $this->buy_activity_goods = array();
        $rule_buy_goods = $this->get_strategy_rule_buy_goods($strategy_rule['op_gift_strategy_detail_id']);
        if (empty($rule_buy_goods)) {
            return TRUE;
        }

        $buy_goods = $this->get_sell_record_buy_goods();
 
        foreach ($buy_goods as $sku => $num) {
            if (isset($rule_buy_goods[$sku])) {
                $this->buy_activity_goods[] = $sku;
            }
        }
        $is_buy =  empty($this->buy_activity_goods)?false:true;
        return $is_buy;
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
            $success_times = $this->check_buy_goods_is_contain($rule_buy_goods, $buy_goods);
            if ($success_times > 0) {
                $this->is_buy_enough_num = $this->set_is_buy_success_rule($success_times);
            }
        } else if ($strategy_rule['goods_condition'] == 1) {
            //随机购买
            $success_times = $this->check_buy_goods_is_contain_by_num($buy_goods, $rule_buy_goods, $strategy_rule['buy_num']);
            if ($success_times > 0) {
                $this->is_buy_enough_num = $this->set_is_buy_success_rule($success_times);
            }
        } else if ($strategy_rule['goods_condition'] == 2) {
            //全场买送
            $this->is_buy_enough_num = 1;
        }
       $ret = ($this->is_buy_enough_num > 0) ? $this->format_ret(1) : $this->format_ret(-1, '', "购买指定商品不足({$strategy_rule['op_gift_strategy_detail_id']})");
        return $ret;
      //  return ($this->is_buy_enough_num > 0) ? TRUE : FALSE;
    }

    /*
     * 设置买成功的规则
     */

    private function set_is_buy_success_rule($success_times) {
        $strategy_rule = &$this->strategy_rule;
        $enough_num = 0;

        if ($strategy_rule['range_type'] == 0) {
            $is_success = $this->check_rule_rang($success_times);
            $enough_num = ($is_success == TRUE) ? 1 : 0;
        } else {
            $enough_num = $success_times * $strategy_rule['doubled'];
        }


        return $enough_num;
    }

    /*
     * 获取规则的赠品
     */

    protected function set_gift_by_rule() {

        $strategy_rule = &$this->strategy_rule;
        $op_gift_strategy_range_id = 0;
        $send_gift_num = $strategy_rule['gift_num']; //送赠品数据
        
        if ($strategy_rule['range_type'] == 0 && $strategy_rule['goods_condition'] !=2 ) {
            if (empty($this->success_rule_range)) {
                //无规则范围
              //  return FALSE;
                return $this->format_ret(-1,'','不在规则范围');
            }
            $op_gift_strategy_range_id = $this->success_rule_range['id'];

        }
        $gift_data = $this->get_rule_gift($strategy_rule['op_gift_strategy_detail_id'], $op_gift_strategy_range_id);

        if (empty($gift_data)) {
            //没设置赠品
            return $this->format_ret(-1,'','赠品赠品已经送完('.$op_gift_strategy_range_id.')');
        
        }
        $give_way = -1; //1随机 0固定送
        if ($strategy_rule['range_type'] == 0 && $strategy_rule['goods_condition'] !=2 ) {
            $give_way = $this->success_rule_range['give_way'];
            $send_gift_num = $this->success_rule_range['gift_num'];
        } else {
            $give_way = $strategy_rule['give_way'];
        }

        $status = FALSE;
        if ($give_way == 1) {    //随机送
            //满送叠加送
            $gift_num = $this->is_buy_enough_num * $send_gift_num;
            $status = $this->send_random_gift($gift_num, $gift_data);
        } else {  //固定送
            $this->set_repeat_gift($gift_data); //设置叠加赠品

            $status = $this->send_fixed_gift($gift_data);
        }
        return ($status===false)?$this->format_ret(-1,'','赠品库存不足'):$this->format_ret(1);
  
        

    }
    /*
     * 通过从策略获取规则
     */

   protected function check_rule_is_sort($rule_sort,$sort){
       
        if($sort>0&&$rule_sort!=$sort){
            return FALSE;
        }
        return TRUE;
    }
    
    
    protected function get_strategy_by_trade() {

        $sql_main = "select y.* from op_gift_strategy y
            INNER JOIN op_gift_strategy_shop s ON y.strategy_code=s.strategy_code
            where 1 ";
        $sql_main .= " AND y.status=:status";
        $sql_main .= "  AND  s.shop_code=:shop_code";

        $sql_values[':status'] = 1;
        $sql_values[':shop_code'] = $this->sell_record['shop_code'];

        $sql_pay_time = "   start_time<=:pay_time AND end_time>=:pay_time AND time_type=0  ";
        $sql_record_time = " start_time<=:record_time AND end_time>=:record_time AND time_type=1  ";
        $sql_main.= " AND (  ({$sql_pay_time}) OR ({$sql_record_time}) ) ";

        $sql_values[':pay_time'] = ($this->sell_record['pay_type'] == 'cod')?
                      strtotime($this->sell_record['record_time']):strtotime($this->sell_record['pay_time']);
        $sql_values[':record_time'] = strtotime($this->sell_record['record_time']);

        $data = $this->db->get_all($sql_main, $sql_values);
        $ret_status = 1;
        if (empty($data)) {
            $ret_status = -1;
        }
        
        //主策略信息汇总
        foreach($data as $val){
            if(!isset($this->strategy_data_all[$val['strategy_code']])){
                  $this->strategy_data_all[$val['strategy_code']] = $val;
            }
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
}
