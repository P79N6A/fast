<?php

require_model('oms/SellProblemModel');

class ExceptionAddressModel extends SellProblemModel {

    //如果省一级 没数据，识别为国外地址
    function handler($sell_record_data) {

        $content = $this->get_problem_content('EXCEPTION_ADDRESS');
        $content = str_replace("，", ",", trim($content));
        $address_arr = empty($content) ? array() : explode(',', $content);
        $ret = $this->format_ret(-1);
        if ($sell_record_data['receiver_addr'] == '*****') {
            $address = load_model("sys/security/CustomersSecurityModel")->get_customer_decrypt($sell_record_data['customer_address_id'], 'address');
            $sell_record_data['receiver_address'] = str_replace($sell_record_data['receiver_addr'], $address, $sell_record_data['receiver_address']);
        }

        foreach ($address_arr as $address) {
            if (stripos($sell_record_data['receiver_address'], $address) !== FALSE) {
                $ret = $this->format_ret(1);
                break;
            }
        }
        return $ret;
    }

}
