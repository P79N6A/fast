<?php
require_model('op/GiftStrategy/GiftStrategyEnterpriseModel');
class TestGiftStrategyModel extends GiftStrategyEnterpriseModel{
    public $strategy_gift_result;
    //测试礼品
    public function set_strategy_data(&$sell_record_data,&$sell_record_mx_data,$trade_detail,$strategy_data){
        $this->init($sell_record_data, $sell_record_mx_data, $trade_detail);
        $this->strategy_data = $strategy_data;
        $ret_check = $this->check_strategy();

        if ($ret_check['status'] > 0) {
            $this->strategy_gift_result[$this->strategy_data['strategy_code']] = $ret_check['data'];
            $this->strategy_name_arr[$this->strategy_data['strategy_code']] = $this->strategy_data['strategy_name'];
            if (!empty($this->sell_gift_arr)) {
                $test_gift_arr = $this->deal_sell_gift();
                return $this->format_ret(1, $test_gift_arr);
            }
        } else {
            $this->strategy_gift_result[$this->strategy_data['strategy_code']] = array();
            return $ret_check;
        }
    }

    /**
     * 处理礼品
     */
    protected function deal_sell_gift() {
        $test_gift_arr = array();
        if (!empty($this->sell_gift_arr)) {
            //规格别名
            $arr = array('goods_spec1', 'goods_spec2');
            $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
            $goods_spec1_rename = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
            $goods_spec2_rename = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
            $sku_arr = array_keys($this->sell_gift_arr);
            $this->sku_data = $this->get_sku_info($sku_arr);
            $sku_data = &$this->sku_data;
            $plan_send_time = $this->sell_record_detail[0]['plan_send_time'];
            $deal_code = $this->sell_record_detail[0]['deal_code'];
            //获取礼品的信息
            $sku_arr = array_column($this->sell_gift_arr,'sku');
            $sku_arr_value = array();
            $sku_arr_str = $this->arr_to_in_sql_value($sku_arr,'sku',$sku_arr_value);
            $sku_ret = $this->db->get_all('select s.sku, s.spec1_name,s.spec2_name,s.barcode,g.goods_name from goods_sku s inner join base_goods g on s.goods_code = g.goods_code where s.sku in ('.$sku_arr_str.')',$sku_arr_value);
            $sku_ret = load_model('util/ViewUtilModel')->get_map_arr($sku_ret, 'sku');
            foreach ($this->sell_gift_arr as $val) {
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], array('cost_price'));
                $sku_info = isset($sku_ret[$val['sku']]) ? $sku_ret[$val['sku']] : array('spec1_name'=>'','barcode'=>'','goods_name'=>'','spec2_name'=>'');
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
                    'barcode'=>$sku_info['barcode'],
                    'spec_name'=>$goods_spec1_rename.':'.$sku_info['spec1_name'].';'.$goods_spec2_rename.':'.$sku_info['spec2_name'],
                    'goods_name'=>$sku_info['goods_name'],
                );
                $gift_arr = array_merge($sku_data[$val['sku']], $gift_arr);
                $test_gift_arr[] = $gift_arr;
            }
        }
        return $test_gift_arr;
    }
}
