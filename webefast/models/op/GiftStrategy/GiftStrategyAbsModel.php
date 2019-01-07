<?php

require_model('tb/TbModel');

abstract class GiftStrategyAbsModel extends TbModel {

    protected $sell_record;
    protected $sell_record_detail;
    protected $strategy_data;
    protected $strategy_type = "gift";
    protected $sell_gift_arr = array(); //交易获取赠品
    protected $strategy_send_gift = array();  //策略赠送赠品
    protected $rule_send_gift = array();  //策略规则赠送赠品
    protected $strategy_gift_result = array();
    protected $sell_record_log = "";
    protected $sell_record_buy_goods = array(); //购买的商品
    protected $strategy_rule = array(); //策略规则
    protected $is_buy_enough_num = 0; //满足条件的策略倍数
    //日志处理使用
    protected $strategy_name_arr = array();
    protected $trade_detail;
    protected $sku_data = array();
    protected $sku_combo_data = array();
    protected $strategy_rule_log;
    protected $is_combo_detail = false; //是否组合商品
    protected $is_combine_trade = false; //是否合并交易
    protected $buy_activity_goods = array(); //买的活动商品
    protected $strategy_data_all = array();
    protected $usable_gifts_num = array(); //设置可赠送数量

    protected function init(&$sell_record, &$sell_record_detail, &$trade_detail) {
        $this->sell_gift_arr = array();
        $this->strategy_send_gift = array();
        $this->rule_send_gift = array();

        $this->strategy_gift_result = array(); //赠品明细
        $this->strategy_data = array();
        $this->sell_record = &$sell_record;
        $this->sell_record_detail = &$sell_record_detail;
        $this->sell_record_buy_goods = array();
        $this->sell_record_log = '';
        $this->strategy_rule_log = '';
        $this->trade_detail = $trade_detail;
        $this->usable_gifts_num = array();
    }

    function action_strategy(&$sell_record, &$sell_record_detail, $trade_detail) {

        $this->init($sell_record, $sell_record_detail, $trade_detail);
        $ret_strategy = $this->get_strategy_by_trade();
     

        if ($ret_strategy['status'] > 0) {
            foreach ($ret_strategy['data'] as $val) {
                $this->strategy_data = $val;
                $ret_check = $this->check_strategy();

                if ($ret_check['status'] > 0) {
                    $this->strategy_gift_result[$val['strategy_code']] = $ret_check['data'];
                    $this->strategy_name_arr[$val['strategy_code']] = $val['strategy_name'];
                } else {
                    $this->strategy_gift_result[$val['strategy_code']] = array();
                }
                $this->strategy_rule_log[$val['strategy_code']] = $ret_check['message'];
            }
        }

        if (!empty($this->sell_gift_arr)) {
            $this->add_sell_detail_gift();
            $this->set_sell_record_log();
        }

        return $this->format_ret(1, $this->sell_gift_arr, $this->sell_record_log);
    }

    function save_send_gift_num() {

        foreach ($this->rule_send_gift as $strategy_code => $rule_data) {
            if ($this->strategy_data_all[$strategy_code]['set_gifts_num'] == 1) {
                $this->save_rule_send_gift_num($rule_data);
            }
        }
    }

    private function save_rule_send_gift_num($rule_data) { //$range
        foreach ($rule_data as $op_gift_strategy_detail_id => $rang_data) {
            $this->save_rang_send_gift_num($rang_data, $op_gift_strategy_detail_id);
        }
    }

    private function save_rang_send_gift_num($rang_data, $op_gift_strategy_detail_id) { //$range
        foreach ($rang_data as $rang_id => $sku_data) {
            foreach ($sku_data as $sku => $val) {
                $sql = "update op_gift_strategy_goods set  send_gifts_num=send_gifts_num+{$val['num']} where is_gift=1  ";
                $sql .= " AND op_gift_strategy_detail_id=:op_gift_strategy_detail_id AND op_gift_strategy_range_id=:op_gift_strategy_range_id AND sku=:sku ";
                $sql_value = array(':op_gift_strategy_detail_id' => $op_gift_strategy_detail_id, ':op_gift_strategy_range_id' => $rang_id, ':sku' => $sku);
                $this->db->query($sql, $sql_value);
            }
        }
    }

    /*
     * 检查策略规则的会员是否赠送
     */

    abstract protected function check_rule_customer_is_send();

    /*
     * 获取交易满足的策略
     */

    abstract protected function get_strategy_by_trade();
    /*
     * 检测满送规则
     */

    abstract protected function check_rule_is_full();
    /*
     * 检测买送规则
     */

    abstract protected function check_rule_is_buy();
    /*
     * 获取是否固定会员送
     */

    abstract protected function get_is_fixed_customer();

    /*
     * 检测买送规则
     */

    abstract protected function set_gift_by_rule();

    /*
     * 通过从策略获取规则
     */

    abstract protected function get_strategy_rule_by_strategy_code();

    /*
     * 检查是否同1分组互斥
     */

    abstract protected function check_rule_is_sort($rule_sort, $sort);

    protected function set_sell_record_log() {

        foreach ($this->strategy_send_gift as $strategy_code => $gift_arr) {
            $op_gift_strategy_detail_id_arr = $this->strategy_gift_result[$strategy_code];
            $detail_id = deal_array_with_quote($op_gift_strategy_detail_id_arr);
            $sql = "SELECT name FROM op_gift_strategy_detail WHERE op_gift_strategy_detail_id IN ({$detail_id})";
            $rule = $this->db->get_all($sql);
            $name = array();
            //$this->strategy_data_all
            foreach ($rule as $value) {
                $name[] = $value['name'];
            }
            $name_str = implode(',', $name);
            $strategy_name = $this->strategy_name_arr[$strategy_code] . "($strategy_code)";
            $rule_name = "规则：" . "($name_str)";
            $this->sell_record_log .= "赠品策略：" . $strategy_name . ',' . $rule_name . ",赠送赠品：";
            foreach ($gift_arr as $sku => &$val) {
                $barcode = $this->sku_data[$sku]['barcode'];
                $this->sell_record_log.=$barcode . "({$val['num']}件),";
            }
        }
    }

    /*
     * 检查策略是否满足赠送条件
     */

    protected function check_strategy() {
        $ret_check = $this->check_strategy_customer_is_send();
        if ($ret_check['status'] < 1) {
            return $ret_check;
        }

        //合并升档
        $ret_combine = $this->check_strategy_combine_send();
        if ($ret_combine['status'] < 1) {
            return $this->format_ret(-1, '', '策略不支持合并升档');
        }


        $rule_data = $this->get_strategy_rule_by_strategy_code();

        if (empty($rule_data)) {
            return $this->format_ret(-1, '', '无可用规则');
        }

        $is_mutex = 1; //互溶
        $sort = -1; //分组
        $strategy_rule_arr = array();
        $rule_fail_log = array();
        foreach ($rule_data as &$val) {

            if ($this->check_rule_is_sort($val['sort'], $sort) === FALSE) {
                $rule_fail_log[] = "分组{$val['sort']}与{$sort}互斥";
                break;
            }

            if ($val['is_mutex'] == 0 && $is_mutex == 0) {
                $rule_fail_log[] = "与前一个互斥规则互斥({$val['op_gift_strategy_detail_id']})";
                continue;
            }

            $this->strategy_rule = $val;
            $ret_rule_check = $this->check_strategy_rule();
            $this->send_gift_by_rule($ret_rule_check);
            if ($ret_rule_check['status'] > 0) {
                $strategy_rule_arr[] = $val['op_gift_strategy_detail_id'];
                $is_mutex = ($val['is_mutex'] == 0) ? 0 : 1;
                $sort = ($sort == -1) ? $val['sort'] : $sort;
            } else {
                $rule_fail_log[] = $ret_rule_check['message'] . "({$val['op_gift_strategy_detail_id']})";
            }
        }

        if (empty($strategy_rule_arr)) {
            $ret = $this->format_ret(-1, '', implode(",", $rule_fail_log));
        } else {
            $ret = $this->format_ret(1, $strategy_rule_arr, implode(",", $rule_fail_log));
        }
        return $ret;
    }

    /*
     * 通过策略送赠品
     */

    protected function send_gift_by_rule(&$ret_rule_check) {
        //送赠品

        if ($ret_rule_check['status'] > 0) {

            $ret = $this->set_gift_by_rule();
            //是赠送成功
            if ($ret['status'] < 1) {
                $ret_rule_check = $ret;
            }
        }
    }

    /*
     * 检查策略规则是否满足赠送条件
     */

    protected function check_strategy_rule() {
        $strategy_rule = &$this->strategy_rule;
        $this->success_rule_range = array();
        //固定会员送
        $is_fixed_customer = $this->get_is_fixed_customer();
        if ($is_fixed_customer === TRUE) {
            if (!$this->check_rule_customer_is_send()) {
                //return FALSE;
                return $this->format_ret(-1, '', "不是指定会员({$strategy_rule['op_gift_strategy_detail_id']})");
            }
        }

        $send_type = array('0' => 'is_full', '1' => 'is_buy');

        if (isset($send_type[$strategy_rule['type']])) {
            $func = "check_rule_" . $send_type[$strategy_rule['type']];
            return $this->$func();
        }

        //无效的策略规则
        return $this->format_ret(-1, '', "无效的策略规则({$strategy_rule['op_gift_strategy_detail_id']})");
        // return FALSE;
    }

    /*
     * 检查策略的会员是否是否赠送
     */

    protected function check_strategy_customer_is_send() {
        $ret = $this->format_ret(1);
        //是否只送1次
        if (1 == $this->strategy_data['is_once_only']) {
            $ret = load_model("op/StrategyLogModel")->get_strategy_log_by_customer_code($this->strategy_type, $this->strategy_data['strategy_code'], $this->sell_record['customer_code']);
            if ($ret['data'] > 0) {
                $ret = $this->format_ret(-1, '', '会员此策略只赠送1次');
            }
        }
        return $ret;
    }

    /*
     * 合并升档次赠送
     */

    protected function check_strategy_combine_send() {
        $ret_status = 1;
        if (isset($this->sell_record['is_combine_new']) && $this->sell_record['is_combine_new'] == 1) {
            $ret_status = -1;
            if (1 == $this->strategy_data['combine_upshift']) {
                $ret_status = 1;
            }
        }
        return $this->format_ret($ret_status);
    }

    function get_strategy_log() {

        $log_data = array();
        // $this->strategy_gift_result[$val['strategy_code']] 
        if (!empty($this->strategy_gift_result)) {
            foreach ($this->strategy_gift_result as $strategy_code => $rule_data) {
                $log_row = array();
                $log_row['type'] = $this->strategy_type;
                $log_row['strategy_code'] = $strategy_code;

                $log_row['strategy_detail_id'] = empty($rule_data) ? '' : implode(",", $rule_data);
                $log_row['is_success'] = 0;
                if (isset($this->rule_send_gift[$strategy_code])) {
                    $log_row['strategy_content'] = json_encode($this->rule_send_gift);
                    $log_row['desc'] = $this->strategy_rule_log[$strategy_code];
                    $log_row['is_success'] = 1;
                } else {
                    $log_row['strategy_content'] = $this->strategy_rule_log[$strategy_code];
                    $log_row['is_success'] = 0;
                }
                $log_row['customer_code'] = $this->sell_record['customer_code'];
                $log_row['sell_record_code'] = $this->sell_record['sell_record_code'];
                $log_row['deal_code'] = $this->sell_record['deal_code'];

                $log_data[] = $log_row;
            }
        }

        $this->save_send_gift_num();

        return $log_data;
    }

    protected function add_sell_detail_gift() {
        if (!empty($this->sell_gift_arr)) {
            $sku_arr = array_keys($this->sell_gift_arr);
            $this->sku_data = $this->get_sku_info($sku_arr);
            $sku_data = &$this->sku_data;
            $plan_send_time = $this->sell_record_detail[0]['plan_send_time'];
            $deal_code = $this->sell_record_detail[0]['deal_code'];

            foreach ($this->sell_gift_arr as $val) {
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], array('cost_price'));
                $gift_arr = array(
                    'deal_code' => $deal_code,
                    'sell_record_code' => $this->sell_record['sell_record_code'],
                    'sub_deal_code' => '',
                    'num' => $val['num'],
                    'avg_money' => 0,
                    'sku' => $val['sku'],
                    //  'cost_price' => $this->get_cost_price($val['sku']),
                    'cost_price' => $sku_info['cost_price'],
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
        }
    }

    function get_sku_info($sku_arr) {
        $sku_str = "'" . implode("','", $sku_arr) . "'";
        $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,p.sell_price as price  from  goods_sku b
            LEFT JOIN base_goods p ON b.goods_code = p.goods_code
            where  b.sku in({$sku_str})";
        $data = $this->db->get_all($sql);
        $sku_data = array();
        foreach ($data as $val) {
            if (is_null($val['barcode'])) {
                $val['barcode'] = '';
            }
            $sku_data[$val['sku']] = $val;
        }
        return $sku_data;
    }

    protected function set_repeat_gift(&$gift_sku_arr) {
        if ($this->is_buy_enough_num > 1) {
            foreach ($gift_sku_arr as $sku => $num) {
                $gift_sku_arr[$sku] = $num * $this->is_buy_enough_num;
            }
        }
    }

    protected function get_sell_record_buy_goods() {
        if (empty($this->sell_record_buy_goods)) {
            $this->set_is_combo_detail();
            $sell_detail = $this->sell_record_detail;

            //是否判断组合商品 或 合并单据
            if (($this->is_combo_detail === TRUE && !empty($this->trade_detail)) || (isset($this->sell_record['is_combine_new']) && $this->sell_record['is_combine_new'] == 1 && !empty($this->trade_detail))) {
                $sell_detail = $this->trade_detail;
            } else {
                //设置交易数据 后续活动商品金额计算使用
                $this->trade_detail = $sell_detail;
            }
            
            foreach ($sell_detail as $val) {
                $num = isset($val['combo_num']) ? $val['num'] - $val['combo_num'] : $val['num'];
                if ($num > 0) {
                    $this->sell_record_buy_goods[$val['sku']] =isset($this->sell_record_buy_goods[$val['sku']])?$this->sell_record_buy_goods[$val['sku']]+$num:$num;
                }
            }
            
        }
        return $this->sell_record_buy_goods;
    }

    protected function set_is_combo_detail() {
        foreach ($this->sell_record_detail as $val) {
            if (isset($val['combo_sku']) && !empty($val['combo_sku'])) {
                $this->is_combo_detail = TRUE;
            }
        }
    }

    protected function check_buy_goods_is_contain($rule_buy_goods, $buy_goods) {

        $is_enough_num = 0;
        foreach ($rule_buy_goods as $_sku => $_num) {
            if (!isset($buy_goods[$_sku])) {
                $is_enough_num = 0;
                break;
            } else {
                $check_num = (int) floor($buy_goods[$_sku] / $_num);
                if ($check_num == 0) {
                    $is_enough_num = 0;
                    break;
                } else {
                    $is_enough_num = ($is_enough_num > 0) ? min($is_enough_num, $check_num) : $check_num;
                }
            }
        }

        return $is_enough_num;
    }

    protected function check_buy_goods_is_contain_by_num($buy_goods, $rule_buy_goods, $num) {//$goods_sku_arr, $by_goods_sku_arr
        $buy_enough_num = 0;
        foreach ($buy_goods as $_sku => $_num) {
            if (isset($rule_buy_goods[$_sku])) {
                $buy_enough_num += $_num;
            }
        }
        $is_enough_num = (int) floor($buy_enough_num / $num);
        return $is_enough_num;
    }

    protected function get_sell_record_buy_money() {

        $is_contain_delivery_money = &$this->strategy_rule['is_contain_delivery_money'];
        $buy_money = $this->sell_record['payable_money'];
        if ($is_contain_delivery_money == 0) {
            $buy_money = $buy_money - $this->sell_record['express_money'];
        }

        $is_goods_money = &$this->strategy_rule['is_goods_money'];
        if ($is_goods_money == 1) { //活动商品金额满足
            $buy_money = 0;
            foreach ($this->trade_detail as &$val) {
                if (in_array($val['sku'], $this->buy_activity_goods)) {
                    $buy_money +=$val['avg_money'];
                }
            }
        }


        return $buy_money;
    }

    /*
     * 获取赠品策略明细级别商品
     */

    protected function get_strategy_rule_buy_goods($op_gift_strategy_detail_id) {
        static $strategy_rule_buy_goods = null;

        if (!isset($strategy_rule_buy_goods[$op_gift_strategy_detail_id])) {
            $param = array('op_gift_strategy_detail_id' => $op_gift_strategy_detail_id, 'is_gift' => 0);
            $data = $this->get_strategy_rule_goods($param);
            $sku_data = array();

            foreach ($data as $val) {
                $sku_data[$val['sku']] = $val['num'];
            }

            $strategy_rule_buy_goods[$op_gift_strategy_detail_id] = $sku_data;
        }

        return $strategy_rule_buy_goods[$op_gift_strategy_detail_id];
    }

    /*
     * 获取规则的赠品
     */

    protected function get_rule_gift($op_gift_strategy_detail_id, $op_gift_strategy_range_id = 0) {
        static $strategy_rule_gift = null;
        $is_set_gifts_num = empty($this->strategy_data['set_gifts_num']) ? 0 : 1;

        if (!isset($strategy_rule_gift[$op_gift_strategy_detail_id][$op_gift_strategy_range_id]) || $is_set_gifts_num == 1) {
            $param['op_gift_strategy_detail_id'] = $op_gift_strategy_detail_id;
            $param['is_gift'] = 1;
            $param['set_gifts_num'] = $is_set_gifts_num;

            if ($op_gift_strategy_range_id > 0) {
                $param['op_gift_strategy_range_id'] = $op_gift_strategy_range_id;
            }
            $data = $this->get_strategy_rule_goods($param);

            foreach ($data as $val) {
                $sku_data[$val['sku']] = $val['num'];
            }

            $strategy_rule_gift[$op_gift_strategy_detail_id][$op_gift_strategy_range_id] = $sku_data;
        }

        return $strategy_rule_gift[$op_gift_strategy_detail_id][$op_gift_strategy_range_id];
    }

    protected function get_strategy_rule_goods($param) {

        $param['is_gift'] = isset($param['is_gift']) ? $param['is_gift'] : 0;
        $sql = "select * from op_gift_strategy_goods where  1 ";
        $sql .=" AND op_gift_strategy_detail_id=:op_gift_strategy_detail_id AND is_gift=:is_gift ";
        $sql_values = array(':op_gift_strategy_detail_id' => $param['op_gift_strategy_detail_id'], ':is_gift' => $param['is_gift']);

        if (isset($param['op_gift_strategy_range_id'])) {
            $sql .=" AND op_gift_strategy_range_id=:op_gift_strategy_range_id ";
            $sql_values[':op_gift_strategy_range_id'] = $param['op_gift_strategy_range_id'];
        }
        $data = $this->db->get_all($sql, $sql_values);

        if (isset($param['set_gifts_num']) && $param['set_gifts_num'] == 1) {
            foreach ($data as $key => &$val) {
                $usable_gifts_num = (int) $val['gifts_num'] - (int) $val['send_gifts_num'];
                if ($usable_gifts_num > 0) {
                    //设置策略可赠送数量
                    $this->usable_gifts_num[$val['op_gift_strategy_detail_id']][$val['op_gift_strategy_range_id']][$val['sku']] = $usable_gifts_num;
                } else {
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }

    /*
     * 获取赠品策略明细级别商品
     */

    protected function get_strategy_detail_goods($op_gift_strategy_detail_id, $is_gift) {
        static $strategy_detail_goods = null;

        if (!isset($strategy_detail_goods[$op_gift_strategy_detail_id][$is_gift])) {
            $sql = "select sku,num from op_gift_strategy_goods where op_gift_strategy_detail_id=:op_gift_strategy_detail_id and is_gift=:is_gift ";
            $sql_values = array(':op_gift_strategy_detail_id' => $op_gift_strategy_detail_id, ':is_gift' => $is_gift);
            $data = $this->db->get_all($sql, $sql_values);
            $sku_data = array();

            foreach ($data as $val) {
                $sku_data[$val['sku']] = $val['num'];
            }

            $strategy_detail_goods[$op_gift_strategy_detail_id][$is_gift] = $sku_data;
        }

        return $this->format_ret(1, $strategy_detail_goods[$op_gift_strategy_detail_id][$is_gift]);
    }

    protected function send_fixed_gift($gift_sku_arr) {
        $is_send = false;
        
        
        if ($this->strategy_data['is_continue_no_inv'] == 0) {
            $is_send = $this->send_fixed_gift_check_inv($gift_sku_arr);
        } else {
            $is_send = $this->send_fixed_gift_no_check_inv($gift_sku_arr);
        }
        return $is_send;
    }

    //检查库存，无库存不赠送
    protected function send_fixed_gift_check_inv($gift_sku_arr) {
        $sku_arr = array_keys($gift_sku_arr);
        $inv_sku_data = $this->get_enough_inv_sku($sku_arr);
        $is_send = FALSE;
        
        $rule_id = &$this->strategy_rule['op_gift_strategy_detail_id'];
        $range_id =  isset($this->success_rule_range['id'])?$this->success_rule_range['id']:0;
        
        if (!empty($inv_sku_data)) {
            foreach ($gift_sku_arr as $sku => $gift_num) {
                //赠送数量足够
                if (isset($inv_sku_data[$sku])) {
                    $gift_num = ($inv_sku_data[$sku] > $gift_num) ? $gift_num : $inv_sku_data[$sku];
                    //增加判断赠品库存
                    if($this->strategy_data['set_gifts_num'] == 1){
                        $gift_num = ($this->usable_gifts_num[$rule_id][$range_id][$sku]>$gift_num)?$gift_num:$this->usable_gifts_num[$rule_id][$range_id][$sku];
                    }
                    if($gift_num>0){
                           $this->add_gift($sku, $gift_num);
                           $is_send = TRUE;
                    }
                }
            }
          
        }
        return $is_send;
    }

    //无库存继续送赠品
    protected function send_fixed_gift_no_check_inv($gift_sku_arr) {
        
        
        $rule_id = &$this->strategy_rule['op_gift_strategy_detail_id'];
        $range_id =  isset($this->success_rule_range['id'])?$this->success_rule_range['id']:0;
        $is_send = FALSE;
        
        foreach ($gift_sku_arr as $sku => $gift_num) {
            if($this->strategy_data['set_gifts_num'] == 1){
                $gift_num = ($this->usable_gifts_num[$rule_id][$range_id][$sku]>$gift_num)?$gift_num:$this->usable_gifts_num[$rule_id][$range_id][$sku];
            }      
            if($gift_num>0){
                $this->add_gift($sku, $gift_num);
                $is_send = TRUE;
            }
            
            
        }
        return $is_send;
    }

    /*
     * 随机送送赠品
     */

    protected function send_random_gift($num, $gift_sku_arr) {

        $sku_arr = array_keys($gift_sku_arr);
        $inv_sku_data = $this->get_enough_inv_sku($sku_arr);

        //设置赠品库存
        $this->set_sku_inv_with_send_random_gift($gift_sku_arr, $inv_sku_data, $num);

        if (!empty($inv_sku_data)) {
            $gift_sku_num = count($inv_sku_data);

            if ($num >= $gift_sku_num) {  //当赠品数量大于 SKU数量，每个SKU都送
                $this->get_all_gift($num, $inv_sku_data);
            } else { //同款先送
                $this->get_gift_by_spec($num, $inv_sku_data); //优先选择 同规格
            }

            //重新设置赠品库存
            $this->set_sku_inv_with_send_random_gift($gift_sku_arr, $inv_sku_data, $num);


            if ($num > 0 && !empty($inv_sku_data)) {
                $this->get_random_gift($num, $inv_sku_data);
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    protected function set_sku_inv_with_send_random_gift(&$gift_sku_arr, &$inv_sku_data, $gift_num) {
        //无库存继续你停止赠送

        $rule_id = &$this->strategy_rule['op_gift_strategy_detail_id'];
        $range_id =  isset($this->success_rule_range['id'])?$this->success_rule_range['id']:0;
        if ($this->strategy_data['is_continue_no_inv'] == 1 && empty($inv_sku_data)) {
            foreach ($gift_sku_arr as $sku => $num) {
                  //有赠送数量限制
                  if ($this->strategy_data['set_gifts_num'] == 1 ) { 
                       if( $this->usable_gifts_num[$rule_id][$range_id][$sku]>0 ){
                            $inv_sku_data[$sku] =  $this->usable_gifts_num[$rule_id][$range_id][$sku];
                       }
                  }else{//无赠送数量限制
                        $inv_sku_data[$sku] = (int) $gift_num + 10000; //设置足够大
                  }
                
            }
        }
    }

    //当赠品数量大于 SKU数量，每个SKU都送
    protected function get_all_gift(&$num, &$inv_sku_data) {
        foreach ($inv_sku_data as $sku => &$sku_num) {
            $this->add_gift($sku);
            $num--;
            $sku_num--;
            if ($sku_num == 0) {
                unset($inv_sku_data[$sku]);
            }
        }
        $inv_sku_num = count($inv_sku_data) ;
        if ($num > $inv_sku_num && $inv_sku_num >0 ) {
            $this->get_all_gift($num, $inv_sku_data);
        }
    }

    protected function get_random_gift(&$num, &$inv_sku_data) {//$inv_sku_data $new_sku_data
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

    protected function add_gift($sku, $num = 1) {

        //添加全部赠品
        if (isset($this->sell_gift_arr[$sku])) {
            $this->sell_gift_arr[$sku]['num'] +=$num;
        } else {
            $this->sell_gift_arr[$sku] = array('sku' => $sku, 'num' => $num);
        }

        //设置相关策略赠品
        $strategy_code = &$this->strategy_data['strategy_code'];
        if (isset($this->strategy_send_gift[$strategy_code][$sku])) {
            $this->strategy_send_gift[$strategy_code][$sku]['num'] +=$num;
        } else {
            $this->strategy_send_gift[$strategy_code][$sku] = array('sku' => $sku, 'num' => $num);
        }

        //设置策略赠品
        $rule_id = &$this->strategy_rule['op_gift_strategy_detail_id'];
        $range_id = isset($this->success_rule_range['id'])?$this->success_rule_range['id']:0;
        //$this->success_rule_range['id']
        if (isset($this->rule_send_gift[$strategy_code][$rule_id][$range_id][$sku])) {
            $this->rule_send_gift[$strategy_code][$rule_id][$range_id][$sku]['num'] +=$num;
        } else {
            $this->rule_send_gift[$strategy_code][$rule_id][$range_id][$sku] = array('sku' => $sku, 'num' => $num);
        }
        
        if ($this->strategy_data['set_gifts_num'] == 1) {
            //扣除可赠送数量
            $this->usable_gifts_num[$rule_id][$range_id][$sku] -=$num;
        }
    }

    protected function get_gift_by_spec(&$num, &$inv_sku_data) {//$inv_sku_data
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

    protected function get_gift_by_sku_data($sku_data, &$num, &$inv_sku_data) {
        foreach ($sku_data as $val) {
            $this->add_gift($val['sku']);
            unset($inv_sku_data[$val['sku']]);
            $num = $num - 1;
            if ($num == 0) {
                break;
            }
        }
    }

    protected function get_enough_inv_sku($sku_arr) {
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
         $range_id = isset($this->success_rule_range['id'])?$this->success_rule_range['id']:0;
        //设置可赠送数量
        if ($this->strategy_data['set_gifts_num'] == 1) {
            foreach ($new_sku as $sku => $num) {
                $usable_gifts_num = $this->usable_gifts_num[$this->strategy_rule['op_gift_strategy_detail_id']][$range_id][$sku];
                if ($usable_gifts_num < $num) {
                    $new_sku[$sku] = $usable_gifts_num;
                }
            }
        }

        return $new_sku;
    }

    private function get_cost_price($sku) {
        $sql = "select gp.cost_price from base_goods gp,goods_sku gb where gb.sku = '{$sku}' and gb.goods_code = gp.goods_code";
        $cost_price = $this->db->getOne($sql);
        return $cost_price;
    }

}
