<?php

require_model('oms/SellProblemModel');

class ComplementaryGoodsModel extends SellProblemModel {

    //有补邮商品
    function handler($sell_record_data) {
        static $goods_prop = null;
        if (is_null($goods_prop)) {
            $goods_prop = array();
            $sql = "select goods_code from base_goods where  goods_prop=1";
            $data = $this->db->get_all($sql);
            foreach ($data as $v) {
                $goods_prop[] = $v['goods_code'];
            }
        }

        if (empty($goods_prop)) {
            return $this->format_ret(-1);
        }

        $goods_arr = array();
        foreach ($sell_record_data['mx'] as $val) {
            $goods_arr[$val['goods_code']] = 1;
            if (in_array($val['goods_code'], $goods_prop)) {
                return $this->format_ret(1);
            }
        }


        return $this->format_ret(-1);
    }

}
