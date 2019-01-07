<?php

require_model('tb/TbModel');

class GiftStrategyOpModel extends TbModel {

    var $sell_record;
    var $sell_record_detail;
    var $strategy_data;
    var $strategy_type = "gift";
    private $sell_gift_arr = array(); //获取赠品
    private $sell_gift_all = array(); //所有赠品
    private $strategy_detail_arr = array(); //满足条件的策略明细ID
    private $sell_record_log = "";
    private $sell_record_buy_goods = array();

    function init(&$sell_record, &$sell_record_detail) {
        $this->sell_gift_all = array();
        $this->strategy_detail_arr = array();
        $this->strategy_data = array();
        $this->sell_record = &$sell_record;
        $this->sell_record_detail = &$sell_record_detail;
        $this->sell_record_buy_goods = array();
        $this->sell_record_log = '';
    }

    function set_trade_gift(&$sell_record, &$sell_record_detail) {

        $this->init($sell_record, $sell_record_detail);
        $pay_time = $this->get_record_pay_time();
        $ret_strategy = $this->get_shop_gift_strategy($sell_record['shop_code'], $pay_time);

        if ($ret_strategy['status'] > 0) {
            foreach ($ret_strategy['data'] as $val) {
                $this->strategy_data = $val;
                if (TRUE === $this->check_strategy_is_send()) {
                    $this->sell_gift_arr = array();
                    $this->set_gift_by_strategy();
                    $this->add_sell_detail_gift();
                }
            }
        }
        return $this->format_ret(1, $this->sell_gift_all, $this->sell_record_log);
    }

    function check_strategy_is_send() {
        $status = TRUE;
        //是否只送1次
        if (1 == $this->strategy_data['is_once_only']) {
            $ret = load_model("op/StrategyLogModel")->get_strategy_log_by_customer_code($this->strategy_type, $this->strategy_data['strategy_code'], $this->sell_record['customer_code']);
            if ($ret['data'] > 0) {
                $status = FALSE;
            }
        }


        return $status;
    }

    function get_strategy_log() {

        $log_data = array();
        if (!empty($this->strategy_detail_arr)) {
            $log_data['type'] = $this->strategy_type;
            $log_data['strategy_code'] = $this->strategy_data['strategy_code'];

            $log_data['strategy_detail_id'] = implode(",", $this->strategy_detail_arr);
            $log_data['is_success'] = 0;
            if (!empty($this->sell_gift_arr)) {
                $log_data['strategy_content'] = json_encode($this->sell_gift_arr);
                $log_data['is_success'] = 1;
            } else {
                $log_data['is_success'] = 0;
            }
            $log_data['customer_code'] = $this->sell_record['customer_code'];
            $log_data['sell_record_code'] = $this->sell_record['sell_record_code'];
            $log_data['deal_code'] = $this->sell_record['deal_code'];
        }
        return $log_data;
    }

    function add_sell_detail_gift() {
        if (!empty($this->sell_gift_arr)) {
            $sku_arr = array_keys($this->sell_gift_arr);
            $sku_data = $this->get_sku_info($sku_arr);
            $plan_send_time = $this->sell_record_detail[0]['plan_send_time'];

            $this->sell_record_log .= "赠品策略({$this->strategy_data['strategy_name']}):";

            $sell_record_sku_key = array();
            foreach ($this->sell_record_detail as $key => $val) {
                $sell_record_sku_key[$val['sku']] = $key;
            }


            foreach ($this->sell_gift_arr as $val) {

                //设置所有送的赠品
                if (isset($this->sell_gift_all[$val['sku']])) {
                    $this->sell_gift_all[$val['sku']]['num'] += $val['num'];
                } else {
                    $this->sell_gift_all[$val['sku']] = $val['num'];
                }


                if (isset($sell_record_sku_key[$val['sku']])) {
                    $_sell_key = $sell_record_sku_key[$val['sku']];
                    $gift_arr = $sku_data[$val['sku']];
                    $this->sell_record_detail[$_sell_key]['num'] +=$val['num'];
                } else {
                    $gift_arr = array(
                        'deal_code' => $this->sell_record['deal_code'],
                        'sell_record_code' => $this->sell_record['sell_record_code'],
                        'sub_deal_code' => '',
                        'num' => $val['num'],
                        'avg_money' => 0,
                        'sku' => $val['sku'],
                        'lock_num' => 0,
                        'is_gift' => 1,
                        'plan_send_time' => $plan_send_time,
                        'platform_spec' => '',
                        'sku_id' => '',
                        'pic_path' => '',
                    );
                    $gift_arr = array_merge($sku_data[$val['sku']], $gift_arr);
                    $this->sell_record_detail[] = $gift_arr;
                }
                $this->sell_record_log.="{$gift_arr['barcode']}（{$val["num"]}件）,";
            }
        }
    }

    function get_sku_info($sku_arr) {
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,p.sell_price as price  from  goods_sku b
            LEFT JOIN base_goods p ON b.goods_code = p.goods_code
            where  b.sku in({$sku_str})";
        $data = $this->db->get_all($sql,$sql_values);
        $sku_data = array();
        foreach ($data as $val) {
            $sku_data[$val['sku']] = $val;
        }
        return $sku_data;
    }

    /*
     * 获取店铺可用的赠品策略
     */

    function get_shop_gift_strategy($shop_code, $pay_time) {
        $ret_strategy = $this->get_shop_available_strategy($shop_code, $pay_time);
        $ret_data = array();
        $ret_status = -1;
        if ($ret_strategy['status'] > 0) {
            foreach ($ret_strategy['data'] as $val) {
                $ret_strategy_detail = $this->get_strategy_detail($val['strategy_code']);
                if (!empty($ret_strategy_detail['data'])) {
                    $val['strategy_detail'] = $ret_strategy_detail['data'];
                    $ret_data[] = $val;
                    $ret_status = 1;
                }
            }
        }
        return $this->format_ret($ret_status, $ret_data);
    }

    private function get_record_pay_time() {
        $pay_time = $this->sell_record['pay_time'];
        //货到付款取单据创建时间
        if ($this->sell_record['pay_code'] == 'cod') {
            $pay_time = $this->sell_record['record_time'];
        }
        return $pay_time;
    }

    function get_shop_available_strategy($shop_code, $pay_time) {


        $sql_main = "select * from op_gift_strategy where 1 ";

        $sql_main .= "  AND  shop_code=:shop_code";
        $sql_values[':shop_code'] = $shop_code;

        $sql_main .= " AND status=:status";
        $sql_values[':status'] = 1;

        $sql_main .= " AND start_time<=:pay_time";
        $sql_main .= " AND end_time>=:pay_time";
        $sql_values[':pay_time'] = strtotime($pay_time);
        $data = $this->db->get_all($sql_main, $sql_values);
        $ret_status = 1;
        if (empty($data)) {
            $ret_status = -1;
        }
        return $this->format_ret($ret_status, $data);
    }

    function get_strategy_detail($strategy_code) {
        static $strategy_detail_data = null;
        if (!isset($strategy_detail_data[$strategy_code])) {
            $sql = "select * from op_gift_strategy_detail where strategy_code=:strategy_code order by op_gift_strategy_detail_id";
            $sql_values[':strategy_code'] = $strategy_code;
            $strategy_detail_data[$strategy_code] = $this->db->get_all($sql, $sql_values);
        }
        return $this->format_ret(1, $strategy_detail_data[$strategy_code]);
    }

    function set_gift_by_strategy() {
        $send_type = array('0' => 'is_full', '1' => 'is_buy');
        $is_mutex = 1; //默认互溶

        foreach ($this->strategy_data['strategy_detail'] as &$strategy_detail) {

            if ($strategy_detail['is_fixed_customer'] == 1) {
                $check_is_send = $this->is_send_by_buyer_name($this->strategy_data['strategy_code'], $this->sell_record['buyer_name']);
                //固定会员送
                if (FALSE === $check_is_send) {
                    continue;
                }
            }


            //存在互斥跳过互斥
            if ($is_mutex == 0 && $strategy_detail['is_mutex'] == 0) {
                continue;
            }
            if (isset($send_type[$strategy_detail['type']])) {
                $func = "send_gift_by_" . $send_type[$strategy_detail['type']];

                $gift_sku_arr = $this->$func($strategy_detail);
                if (!empty($gift_sku_arr)) {
                    if ($strategy_detail['give_way'] == 1) {    //随机送
                        //满送叠加送
                        if ($strategy_detail['is_repeat'] == 1 && $strategy_detail['type'] == 0) {
                            $strategy_detail['gift_num'] = $this->is_buy_enough_num * $strategy_detail['gift_num'];
                        }
                        $this->send_random_gift($strategy_detail['gift_num'], $gift_sku_arr);
                    } else {  //固定送
                        //满送叠加送
                        if ($strategy_detail['is_repeat'] == 1 && $strategy_detail['type'] == 0) {
                            $this->set_repeat_gift($gift_sku_arr);
                        }
                        $this->send_fixed_gift($gift_sku_arr);
                    }

                    //出现互斥设置互斥
                    if ($strategy_detail['is_mutex'] == 0) {
                        $is_mutex = 0;
                    }
                }
            }
        }
    }

    private function set_repeat_gift(&$gift_sku_arr) {
        if ($this->is_buy_enough_num > 1) {
            foreach ($gift_sku_arr as $sku => $num) {
                $gift_sku_arr[$sku] = $num * $this->is_buy_enough_num;
            }
        }
    }

    //获取满赠SKU
    function send_gift_by_is_full($strategy_detail) {
        $buy_money = $this->get_sell_record_buy_money($strategy_detail['is_contain_delivery_money']);
        $gift_sku_arr = array();
        if ($buy_money > $strategy_detail['money_min'] && $buy_money <= $strategy_detail['money_max']) {
            $this->set_strategy_detail_id($strategy_detail['op_gift_strategy_detail_id']);
            $ret_gift_sku = $this->get_strategy_detail_goods($strategy_detail['op_gift_strategy_detail_id'], 1);
            if (!empty($ret_gift_sku['data'])) {
                $gift_sku_arr = $ret_gift_sku['data'];
            }
        }
        return $gift_sku_arr;
    }

    function set_strategy_detail_id($op_gift_strategy_detail_id) {
        $this->strategy_detail_arr[] = $op_gift_strategy_detail_id;
    }

    private $is_buy_enough_num = 0;

    function send_gift_by_is_buy($strategy_detail) {
        $this->is_buy_enough_num = 0;

        $ret = $this->get_strategy_detail_goods($strategy_detail['op_gift_strategy_detail_id'], 0);


        $strategy_buy_goods = $ret['data'];
        $gift_sku_arr = array();
        if (empty($strategy_buy_goods) && $strategy_detail['goods_condition'] != 2) {
            return $gift_sku_arr;
        }

        $buy_goods = $this->get_sell_record_buy_goods();
        $is_enough_num = 0;
        //重复送
        //$is_repeat = ($strategy_detail['is_repeat']==0)?FALSE:TRUE;
        if ($strategy_detail['goods_condition'] == 1) {
            //随机购买
            $is_enough_num = $this->check_goods_is_contain_by_num($buy_goods, $strategy_buy_goods, $strategy_detail['buy_num']);
        } else if ($strategy_detail['goods_condition'] == 0) {
            //固定购买
            $is_enough_num = $this->check_goods_is_contain($strategy_buy_goods, $buy_goods);
        } else if ($strategy_detail['goods_condition'] == 2) {
            $is_enough_num = 1;
        }

        $this->set_strategy_detail_id($strategy_detail['op_gift_strategy_detail_id']);

        if ($is_enough_num > 0) {
            $this->is_buy_enough_num = $is_enough_num;
            $ret_gift_sku = $this->get_strategy_detail_goods($strategy_detail['op_gift_strategy_detail_id'], 1);
            if (!empty($ret_gift_sku['data'])) {
                $gift_sku_arr = $ret_gift_sku['data'];
            }
        }

        return $gift_sku_arr;
    }

    function get_sell_record_buy_goods() {
        if (empty($this->sell_record_buy_goods)) {
            foreach ($this->sell_record_detail as $val) {
                $num = isset($val['combo_num']) ? $val['num'] - $val['combo_num'] : $val['num'];
                if ($num > 0) {
                    $this->sell_record_buy_goods[$val['sku']] = $num;
                }
            }
        }
        return $this->sell_record_buy_goods;
    }

    private function check_goods_is_contain($goods_sku_arr, $by_goods_sku_arr) {
        $is_enough_num = 1;
        foreach ($goods_sku_arr as $_sku => $_num) {
            if (!isset($by_goods_sku_arr[$_sku])) {
                $is_enough_num = 0;
                break;
            } else {
                $check_num = (int) floor($by_goods_sku_arr[$_sku] / $_num);
                if ($check_num == 0) {
                    $is_enough_num = 0;
                    break;
                } else {
                    $is_enough_num = min($is_enough_num, $check_num);
                }
            }
        }

        return $is_enough_num;
    }

    private function check_goods_is_contain_by_num($goods_sku_arr, $by_goods_sku_arr, $num) {
        $buy_num = 0;
        foreach ($goods_sku_arr as $_sku => $_num) {
            if (isset($by_goods_sku_arr[$_sku])) {
                $buy_num += $_num;
            }
        }
        $is_enough_num = (int) floor($buy_num / $num);


        return $is_enough_num;
    }

    function get_sell_record_buy_money($is_contain_delivery_money) {
        $buy_money = $this->sell_record['payable_money'];
        if ($is_contain_delivery_money == 0) {
            $buy_money = $buy_money - $this->sell_record['express_money'];
        }
        return $buy_money;
    }

    /*
     * 获取赠品策略明细级别商品
     */

    function get_strategy_detail_goods($op_gift_strategy_detail_id, $is_gift) {
        static $strategy_detail_goods = null;

        if (!isset($strategy_detail_goods[$op_gift_strategy_detail_id][$is_gift])) {
            $sql = "select sku,num from op_gift_strategy_goods where op_gift_strategy_detail_id=:op_gift_strategy_detail_id and is_gift=:is_gift ";
            $sql_values = array(':op_gift_strategy_detail_id' => $op_gift_strategy_detail_id, ':is_gift' => $is_gift);
            $data = $this->db->get_all($sql, $sql_values);
            $sku_data = array();
            if (!empty($data)) {
                foreach ($data as $val) {
                    $sku_data[$val['sku']] = $val['num'];
                }
            }
            $strategy_detail_goods[$op_gift_strategy_detail_id][$is_gift] = $sku_data;
        }

        return $this->format_ret(1, $strategy_detail_goods[$op_gift_strategy_detail_id][$is_gift]);
    }

    function send_fixed_gift($gift_sku_arr) {
        $sku_arr = array_keys($gift_sku_arr);
        $inv_sku_data = $this->get_enough_inv_sku($sku_arr);


        //检测库存是否是否满足
        $is_enough = $this->check_goods_is_contain($gift_sku_arr, $inv_sku_data);


        if ($is_enough === true) {
            foreach ($gift_sku_arr as $sku => $gift_num) {
                $this->add_gift($sku, $gift_num);
            }
        }
    }

    /*
     * 随机送送赠品
     */

    function send_random_gift($num, $gift_sku_arr) {

        $sku_arr = array_keys($gift_sku_arr);
        $inv_sku_data = $this->get_enough_inv_sku($sku_arr);

        if (!empty($inv_sku_data)) {
            $gift_sku_num = count($inv_sku_data);

            if ($num >= $gift_sku_num) {  //当赠品数量大于 SKU数量，每个SKU都送
                $this->get_all_gift($num, $inv_sku_data);
            } else { //同款先送
                $this->get_gift_by_spec($num, $inv_sku_data); //优先选择 同规格
            }
            if ($num > 0 && !empty($inv_sku_data)) {
                $this->get_random_gift($num, $inv_sku_data);
            }
        }
    }

    //当赠品数量大于 SKU数量，每个SKU都送
    private function get_all_gift(&$num, &$inv_sku_data) {
        foreach ($inv_sku_data as $sku => &$sku_num) {
            $this->add_gift($sku);
            $num--;
            $sku_num--;
            if ($sku_num == 0) {
                unset($inv_sku_data[$sku]);
            }
        }
        if ($num > count($inv_sku_data)) {
            $this->get_all_gift($num, $inv_sku_data);
        }
    }

    private function get_random_gift(&$num, &$inv_sku_data) {//$inv_sku_data $new_sku_data
        $gift_sku_arr = array_keys($inv_sku_data);
        $gift_num = count($gift_sku_arr);
        if ($gift_num > 1) {
            $random_key = rand(0, $gift_num - 1);
            $sku = $gift_sku_arr[$random_key];
            $this->add_gift($sku);
            $num = $num - 1;
            unset($inv_sku_data[$sku]);
        } else {
            $this->add_gift($gift_sku_arr[0]);
            $num = $num - 1;
        }
        if ($num > 0 && !empty($inv_sku_data)) {
            return $this->get_random_gift($num, $inv_sku_data);
        } else {
            return TRUE;
        }
    }

    private function add_gift($sku, $num = 1) {
        if (isset($this->sell_gift_arr[$sku])) {
            $this->sell_gift_arr[$sku]['num'] +=$num;
        } else {
            $this->sell_gift_arr[$sku] = array('sku' => $sku, 'num' => $num);
        }
    }

    private function get_gift_by_spec(&$num, &$inv_sku_data) {//$inv_sku_data
        $sell_sku = array();


        foreach ($this->sell_record_detail as $val) {
            $sell_sku[$val['sku']] = $val['sku'];
        }
        $new_sku_arr = array_keys($inv_sku_data);
        $gift_str = "'" . implode("','", $new_sku_arr) . "'";
        $sell_sku_str = "'" . implode("','", $sell_sku) . "'";
        //优先同规格
        $sql = "select s1.sku from goods_sku s1
                        inner join goods_sku s2 ON s1.spec1_code=s2.spec1_code AND  s1.spec2_code=s2.spec2_code 
                    where s1.sku in({$gift_str}) and s2.sku in({$sell_sku_str}) ";

        $data = $this->db->get_all($sql);

        if (!empty($data)) {
            $this->get_gift_by_sku_data($data, $num, $inv_sku_data);
        }
        if ($num > 0) {
            $new_sku_arr = array_keys($inv_sku_data);
            $gift_str = "'" . implode("','", $new_sku_arr) . "'";
            $sql = "select s1.sku from goods_sku s1
                            inner join goods_sku s2 ON s1.spec1_code=s2.spec1_code 
                        where s1.sku in({$gift_str}) and s2.sku in({$sell_sku_str}) ";

            $data = $this->db->get_all($sql);
            if (!empty($data)) {
                $this->get_gift_by_sku_data($data, $num, $inv_sku_data);
            }
        }
        if ($num > 0) {
            $new_sku_arr = array_keys($inv_sku_data);
            $gift_str = "'" . implode("','", $new_sku_arr) . "'";
            $sql = "select s1.sku from goods_sku s1
                        inner join goods_sku s2 ON s1.spec2_code=s2.spec2_code 
                    where s1.sku in({$gift_str}) and s2.sku in({$sell_sku_str}) ";
            $data = $this->db->get_all($sql);
            if (!empty($data)) {
                $this->get_gift_by_sku_data($data, $num, $inv_sku_data);
            }
        }
    }

    private function get_gift_by_sku_data($sku_data, &$num, &$inv_sku_data) {
        foreach ($sku_data as $val) {
            $this->add_gift($val['sku']);
            unset($inv_sku_data[$val['sku']]);
            $num = $num - 1;
            if ($num == 0) {
                break;
            }
        }
    }

    private function get_enough_inv_sku($sku_arr) {
        $sku_str = "'" . implode("','", $sku_arr) . "'";
        $store_code = $this->sell_record['store_code'];
        //echo "select sku,stock_num,lock_num  from goods_inv where sku in({$sku_str}) and store_code = '{$store_code}' and stock_num>lock_num";die;
        $data = $this->db->get_all("select sku,stock_num,lock_num  from goods_inv where sku in({$sku_str}) and store_code = '{$store_code}' and stock_num>lock_num");
        $new_sku = array();
        foreach ($data as $val) {
            $num = $val['stock_num'] - $val['lock_num'];
            $sku = $val['sku'];
            if (isset($this->sell_gift_arr[$sku])) {
                $num = $num - $this->sell_gift_arr[$sku]['num'];
            }
            if ($num > 0) {
                $new_sku[$val['sku']] = $num;
            }
        }
        return $new_sku;
    }

    function get_gift_strategy_customer($strategy_code, $buyer_name) {
        $sql = "select * from op_gift_strategy_customer where strategy_code=:strategy_code AND buyer_name=:buyer_name ";
        $data = $this->db->get_row($sql, array(':strategy_code' => $strategy_code, ':buyer_name' => $buyer_name));
        if (!empty($data)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    //废弃
    function strategy_is_send_by_buyer_name($strategy_code) {
        $sql = "select 1 from op_gift_strategy_customer where strategy_code=:strategy_code ";
        $data = $this->db->get_row($sql, array(':strategy_code' => $strategy_code));
        if (!empty($data)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function is_send_by_buyer_name($strategy_code, $buyer_name) {

        return $this->get_gift_strategy_customer($strategy_code, $buyer_name);
    }

}
