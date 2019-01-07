<?php

/**
 * Description of CustomersEncryptOptModel
 *
 * @author wq
 */
require_model('sys/security/CustomersSecurityModel');

class CustomersSecurityOptModel extends CustomersSecurityModel {

    protected $taobao_encrypt = array();
    private $online_date;

    function __construct() {
        parent::__construct();
        $this->online_date = date('Y-m-d H:i:s', strtotime($this->sys_param['online_date']));
        $this->taobao_encrypt = array(
            'receiver_name' => 'name',
            'buyer_nick' => 'buyer_name',
            'receiver_addr' => 'address',
            'receiver_mobile' => 'tel',
            'receiver_phone' => 'home_tel',
        );
    }

    function cli_decrypt_api_order() {
        while (1) {
            $is_continue = $this->decrypt_api_order();
            if ($is_continue === false) {
                break;
            }
        }
        while (1) {
            $is_continue = $this->decrypt_fx_order();
            if ($is_continue === false) {
                break;
            }
        }
    }

    function decrypt_api_order($limit = 1000) {
        $online_time = strtotime($this->online_date);

        //需要调整 查询加密店铺
        //转单自动识别是否加密，并创建店铺，支持手动店铺开启加密 并调用接口测试是否正常

        $sql = "select tid,source,shop_code,buyer_nick,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,
         receiver_street,receiver_address,receiver_addr,receiver_mobile,receiver_phone,receiver_email,order_money
         from api_order where status = 1 and is_change <>1 AND customer_address_id=0  AND source='taobao' and order_first_insert_time_int>={$online_time}
        limit {$limit} ";

        //$this->online_date
        $data = $this->db->get_all($sql);

        foreach ($data as $val) {

            $this->decrypt_order($val);
        }
        if (count($data) < $limit) {
            return false;
        }
        return true;
    }

    function decrypt_fx_order($limit = 1000) {
        $sql = "SELECT
                    fenxiao_id AS tid,
                    supplier_from AS source,
                    shop_code,
             supplier_username AS seller_nick,
             receiver_name AS buyer_nick,
             receiver_name,
             '中国' AS receiver_country,
             receiver_state AS receiver_province,
             receiver_city,
             receiver_district,
             receiver_address AS receiver_addr,
             receiver_zip AS receiver_zip_code,
             receiver_mobile_phone AS receiver_mobile,
             IFNULL(receiver_phone, '') AS receiver_phone
            FROM
	api_taobao_fx_trade where is_invo = 1 AND  is_change <>1  AND customer_address_id=0   AND  created>='{$this->online_date}' limit {$limit} ";
        $data = $this->db->get_all($sql);

        foreach ($data as $val) {

            $this->decrypt_order($val, 1);
        }
        if (count($data) < $limit) {
            return false;
        }
        return true;
    }

    function decrypt_order($order_data, $is_fx = 0) {
        $customer_address_data = array();
        $shop_code = $order_data['shop_code'];
        $encryp_data = load_model('sys/security/SysEncrypModel')->get_encrypt_info_by_shop($shop_code);

        $is_encryp_value = load_model('sys/security/CustomersSecurityModel')->is_encrypt_value($order_data['buyer_nick'], $order_data['source'], 'buyer_nick');

        if (empty($encryp_data) && $is_encryp_value == true) {

            $ret = load_model('sys/security/SysEncrypModel')->create_shop_encrypt($shop_code);
            if ($ret['status'] < 1) {
                return $ret;
            }
        }


        foreach ($this->taobao_encrypt as $key => $adder_key) {
            if (!empty($order_data[$key]) && $is_encryp_value === true) {
                $encryp_text = $order_data[$key];
                $type = ($key == 'receiver_mobile') ? 'phone' : $key;
                $decrypt_text = $this->decrypt_shop_text($encryp_text, $type, $shop_code);
                if ($decrypt_text === false || empty($decrypt_text)) {
                    return $this->format_ret(-1, '', "{$shop_code}店铺解密失败！");
                }
                if ($key == 'buyer_nick' && $encryp_text == $decrypt_text && strlen($encryp_text) > 5) {
                    return $this->format_ret(-1, '', "{$shop_code}店铺解密失败！");
                }
                $order_data[$key] = $decrypt_text;
            } else {
                $decrypt_text = $order_data[$key];
            }
            $customer_address_data[$adder_key] = $decrypt_text;
        }



        $ret_addr = $this->get_address($order_data);
        if ($ret_addr['status'] < 1) {
            $this->update_order_fail($order_data['tid'], $ret_addr['message'], $is_fx);
            return $ret_addr;
        }

        $addr_info = $ret_addr['data'];
        $customer_address_data['city'] = $addr_info['receiver_city'];
        $customer_address_data['district'] = $addr_info['receiver_district'];

        $customer_code = load_model('crm/CustomerOptModel')->check_is_create_customer($customer_address_data['buyer_name']);
//        $customer_data = array();
        if (!empty($customer_code)) {
            $customer_address_data['customer_code'] = $customer_code;
//            $customer_data = load_model('crm/CustomerOptModel')->check_is_create_address($customer_address_data);
        }

//        if (empty($customer_data)) {
            $customer_address_data['customer_name'] = $customer_address_data['buyer_name'];
            $customer_address_data['shop_code'] = $shop_code;
            $customer_address_data['source'] = $order_data['source'];
            $customer_address_data['country'] = $addr_info['receiver_country'];
            $customer_address_data['province'] = $addr_info['receiver_province'];
            $customer_address_data['zipcode'] = $order_data['zipcode'];
            $customer_address_data['is_add_time'] = date('Y-m-d H:i:s');


            $ret_create = load_model('crm/CustomerOptModel')->handle_customer($customer_address_data);
            if ($ret_create['status'] < 1) {
                return $ret_create;
            }

            $customer_data = $ret_create['data'];
            $customer_address_data = array_merge($customer_data, $customer_address_data);
//        } else {
//            $customer_address_data = array_merge($customer_data, $customer_address_data);
//        }


        $this->update_order_address($order_data['tid'], $customer_address_data, $is_fx);

        return $this->format_ret(1, $customer_address_data);
    }

    function update_order_address($tid, $customer_data, $is_fx) {
        if (empty($customer_data['customer_code']) || empty($customer_data['customer_address_id'])) {
            //处理并发异常
            return false;
        }


        if ($is_fx == 0) {
            $update_data = array(
                'customer_address_id' => $customer_data['customer_address_id'],
                'customer_code' => $customer_data['customer_code'],
            );
            //测试注释掉
            $this->update_exp('api_order', $update_data, "tid = '{$tid}'");
        } else {
            $update_data = array(
                'customer_address_id' => $customer_data['customer_address_id'],
                'customer_code' => $customer_data['customer_code'],
            );
            //测试注释掉

            $this->update_exp('api_taobao_fx_trade', $update_data, "fenxiao_id = '{$tid}'");
        }
    }

    function update_order_fail($tid, $fail_message, $is_fx = 0) {
        if ($is_fx == 0) {
            $update_data['change_remark'] = $fail_message;
            $update_data['is_change'] = '-1';
            $where = "tid = '{$tid}' AND is_change<>1  ";
            $this->update_exp('api_order', $update_data, $where);
        } else {
            $update_data['change_remark'] = $fail_message;
            $update_data['is_change'] = '-1';
            $where = "fenxiao_id = '{$tid}' AND is_change<>1  ";
            $this->update_exp('api_taobao_fx_trade', $update_data, $where);
        }
    }

    function get_address($order_data) {

        if ($order_data['receiver_country'] == '海外') {
            $ret_data['receiver_country'] = '250';
            $ret_data['receiver_province'] = '250000';
            $ret_data['receiver_city'] = '25000000';
            $ret_data['receiver_district'] = 0;
            $ret_data['receiver_street'] = '';
            $ret_data['receiver_address'] = $order_data['receiver_address'];
            $ret_data['receiver_addr'] = $order_data['receiver_address'];
            return $this->format_ret(1, $ret_data);
        }


        $ret = load_model('oms/trans_order/AddrCommModel')->match_addr($order_data);


        return $ret;
    }

    private function set_encrypt_str($str) {
        $encrypt_str = '*****';
        return substr($str, 0, 3) . $encrypt_str;
    }

    private function get_hide_name($name) {
        return substr_utf8($name, 0, 1) . '***';
    }

    //todo  需要完善自动服务加密 api_order 表和api_taobao_fx_trade 表等相关表，包含未转单的
    //提供下载报错判断是否加密
}
