<?php

require_model('oms/SellProblemModel');

class SellRecordOverweightModel extends SellProblemModel {
    //订单超重
    function handler($sell_record_data) {
        //判断是否超重
        $over_weight = oms_tb_val('base_question_label', 'content', array('question_label_code' => 'SELL_RECORD_OVERWEIGHT'));
        if (!empty($over_weight) && $sell_record_data['goods_weigh'] >= $over_weight) {
            return $this->format_ret(1, $over_weight, '');
        }
        return $this->format_ret(-1);
    }
}
