<?php

require_model('oms/SellProblemModel');

class CashOnDeliveryModel extends SellProblemModel {

    //货到付款
    function handler($sell_record_data) {
        if ($sell_record_data['pay_type'] == 'cod') {
            return $this->format_ret(1);
        }
        return $this->format_ret(-1);
    }

}