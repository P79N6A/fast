<?php

require_model('sys/security/CustomersSecurityModel');

class OmsSecurityOptModel extends CustomersSecurityModel {

    /**
     * 获取订单的加密信息
     * @param type $sell_record_code
     * @param string $type
     * @return type array
     */
    function get_sell_record_decrypt_info($sell_record_code, $type = '') {
        $record_conf = $this->get_record_conf('sell_record');


        $type_arr = ($type == '') ? array_keys($record_conf['key_arr']) : explode(',', $type);

        $select = $this->set_type_select($type_arr, $record_conf);

        $sql = "select {$select} from oms_sell_record where sell_record_code=:sell_record_code";
        $data = $this->db->get_row($sql, array(':sell_record_code' => $sell_record_code));

        $ret_data = $this->get_decrypt_data($data, $type_arr, $record_conf);

        return $ret_data;
    }

    /**
     * 
     * @param type $sell_return_code
     * @param type $type
     * @param type $is_change 是否包含换货单 0 退单，1换货单
     * @return type
     */
    function get_sell_return_decrypt_info($sell_return_code, $type = '', $is_change = 0) {

        $record_conf = ($is_change == 0) ?
                $this->get_record_conf('sell_return') :
                $this->get_record_conf('sell_return_change');


        $type_arr = ($type == '') ? array_keys($record_conf['key_arr']) : explode(',', $type);

        $select = $this->set_type_select($type_arr, $record_conf);
        $sql = "select {$select} from oms_sell_return where sell_return_code=:sell_return_code";
        $data = $this->db->get_row($sql, array(':sell_return_code' => $sell_return_code));
        $ret_data = $this->get_decrypt_data($data, $type_arr, $record_conf);


        return $ret_data;
    }

    /**
     * 获取包裹单的加密信息
     * @param type $return_package_code
     * @param string $type
     * @return type array
     */
    function get_sell_return_package_decrypt_info($return_package_code, $type = '') {
        $record_conf = $this->get_record_conf('sell_return_package');


        $type_arr = ($type == '') ? array_keys($record_conf['key_arr']) : explode(',', $type);

        $select = $this->set_type_select($type_arr, $record_conf);

        $sql = "select {$select} from oms_return_package where return_package_code=:return_package_code";
        $data = $this->db->get_row($sql, array(':return_package_code' => $return_package_code));
        $ret_data = $this->get_decrypt_data($data, $type_arr, $record_conf);

        return $ret_data;
    }

    private function set_type_select(&$type_arr, $record_conf) {

        $customer_address_id_key = $record_conf['customer_address_id'];
        $address_long_key = $record_conf['address_key_long'];
        $address_key_short = $record_conf['address_key_short'];
        $address_arr = $record_conf['address_arr'];
        $select = " {$customer_address_id_key} ";
        $key_arr = $record_conf['key_arr'];
        $key_data = array_keys($key_arr);
        $select .="," . implode(",", $key_data);


        if (in_array($address_long_key, $type_arr)) {
            $select.="," . implode(",", $address_arr);
            if (!in_array($address_key_short, $type_arr)) {
                $type_arr[] = $address_key_short;
                $select.=",{$address_key_short}";
            }
        }
        $select = "shop_code," . $select;
        return $select;
    }

    /**
     * 批量获取订单解密数据
     * @param type $sell_record_data
     * @param string $type
     * @return type array
     */
    function get_sell_record_decrypt_list($sell_record_data, $type = '') {
        $record_conf = $this->get_record_conf('sell_record');


        $type_arr = ($type == '') ? array_keys($record_conf['key_arr']) : explode(',', $type);

        $this->set_type_select($type_arr, $record_conf);
        $customer_address_id_key = $record_conf['customer_address_id'];
        $customer_address_id_arr = array();
        foreach ($sell_record_data as $_val) {
            if ($_val[$customer_address_id_key] > 0) {
                $customer_address_id_arr[] = $_val[$customer_address_id_key];
            }
        }


        //特殊批量处理加密数据加载
        $this->set_customer_address_data($customer_address_id_arr);


        foreach ($sell_record_data as &$val) {
            if ($val[$customer_address_id_key] > 0) {
                $ret_data = $this->get_decrypt_data($val, $type_arr, $record_conf);
                $val = array_merge($val, $ret_data);
            }
        }
        return $sell_record_data;
    }

    /**
     * 批量获取退单解密数据
     * @param type $sell_return_data
     * @param string $type
     * @return type array
     */
    function get_sell_return_decrypt_list($sell_return_data, $type = '') {
        $record_conf = $this->get_record_conf('sell_return');


        $type_arr = ($type == '') ? array_keys($record_conf['key_arr']) : explode(',', $type);

        $this->set_type_select($type_arr, $record_conf);
        $customer_address_id_key = $record_conf['customer_address_id'];
        $customer_address_id_arr = array();
        foreach ($sell_return_data as $_val) {
            if ($_val[$customer_address_id_key] > 0) {
                $customer_address_id_arr[] = $_val[$customer_address_id_key];
            }
        }
        //特殊批量处理加密数据加载
        $this->set_customer_address_data($customer_address_id_arr);
        foreach ($sell_return_data as &$val) {
            if ($val[$customer_address_id_key] > 0) {
                $ret_data = $this->get_decrypt_data($val, $type_arr, $record_conf);
                $val = array_merge($val, $ret_data);
            }
        }
        return $sell_return_data;
    }

    private function get_record_conf($record_type) {
        return require_conf('oms/security/' . $record_type);
    }

    /**
     * 单据加密转换
     * @param type $data
     * @param type $type_arr
     * @return type array
     */
    private function get_decrypt_data($data, $type_arr, $record_conf) {
        $customer_address_id_key = $record_conf['customer_address_id'];
        $customer_address_id = $data[$customer_address_id_key];
        $address_long_key = $record_conf['address_key_long'];
        $address_key_short = $record_conf['address_key_short'];

        $address_arr = $record_conf['address_arr'];
        $key_arr = $record_conf['key_arr'];
        $ret_data = array();

        //判断店铺是否支持加密
        //    $encrypt_info = load_model('sys/security/SysEncrypModel')->get_encrypt_info_by_shop($data['shop_code']);


        if ($customer_address_id == 0 || (isset($data[$address_key_short]) && $data[$address_key_short] != "*****")) {
            foreach ($type_arr as $type) {
                $ret_data[$type] = isset($data[$type]) ? $data[$type] : '';
            }
        } else {
            foreach ($type_arr as $type) {
                if ($type == $address_long_key) {
                    continue;
                }
                $type_key = $key_arr[$type];

                //判断店铺是否加密开启
                $ret_data[$type] = $this->get_customer_decrypt($customer_address_id, $type_key);
                //解密失败
                if ($ret_data[$type] === false) {
                    return array();
                }
            }

            if (in_array($address_long_key, $type_arr)) {
                $ret_data[$address_long_key] = $this->get_address($data, $address_arr, $ret_data[$address_key_short]);
            }
        }
        return $ret_data;
    }

    public function get_address($data, $receiver_address_arr, $receiver_addr) {
        $area_id_arr = array();

        foreach ($receiver_address_arr as $key) {
            if (!empty($data[$key])) {
                $area_id_arr[] = $data[$key];
            }
        }
        $area_id_str = "'" . implode("','", $area_id_arr) . "'";
        $sql = "select id,name from base_area where id in ({$area_id_str})  order by type ";
        $area_data = $this->db->get_all($sql);

        $receiver_address = '';
        foreach ($area_data as $val) {
            $receiver_address.=$val['name'] . " ";
        }
        $receiver_address .=$receiver_addr;
        return $receiver_address;
    }

    function show_address($record_code, $record_type, &$customer_address_data, $action_note) {

        $customer_address_id = $customer_address_data['customer_address_id'];
        $is_encrypt = $this->is_encrypt_address($customer_address_id);
        $status = -1;
        if ($is_encrypt === true) {
            $log_data['customer_address_id'] = $customer_address_id;
            $log_data['customer_code'] = isset($customer_address_data['customer_code']) ? $customer_address_data['customer_code'] : '';
            $log_data['record_code'] = $record_code;
            $log_data['record_type'] = $record_type;
            $log_data['action_note'] = $action_note;
            $this->add_show_log($log_data);
            $type_arr = array(
                'name', 'address', 'tel', 'home_tel'
            );
            foreach ($type_arr as $type_key) {
                $customer_address_data[$type_key] = $this->get_customer_decrypt($customer_address_id, $type_key);
            }
            $status = 1;
        } else {
            $data = load_model('crm/CustomerOptModel')->get_customer_address($customer_address_id);
            $type_arr = array(
                'name', 'address', 'tel', 'home_tel'
            );
            foreach ($type_arr as $type_key) {
                $customer_address_data[$type_key] = $data[$type_key];
            }
        }
        return $this->format_ret($status, $customer_address_data);
    }

    function add_show_log($data) {
        load_model('sys/security/CustomersSecurityLogModel')->add_log($data);
    }

    function is_encrypt_record($record_data, $record_type) {
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        $shop_code = '';
        if (isset($record_data['shop_code'])) {
            $shop_code = $record_data['shop_code'];
        } else {

            if ($record_type == 'sell_record') {
                $sql = "select shop_code from oms_sell_record where sell_record_code=:sell_record_code";
                $data = $this->db->get_row($sql, array(':sell_record_code' => $record_data['sell_record_code']));
                $shop_code = $data['shop_code'];
            } else if ($record_type == 'sell_return') {
                $sql = "select shop_code from oms_sell_return where sell_record_code=:sell_record_code";
                $data = $this->db->get_row($sql, array(':sell_record_code' => $record_data['sell_record_code']));
                $shop_code = $data['shop_code'];
            } else if ($record_type == 'sell_return_package') {
                $sql = "select shop_code from oms_return_package where return_package_code=:return_package_code";
                $data = $this->db->get_row($sql, array(':return_package_code' => $record_data['return_package_code']));
                $shop_code = $data['shop_code'];
            }
        }
        if ($shop_code == '') {
            return 0;
        }

        if ($record_type == 'sell_record') {
            if (isset($record_data['receiver_addr']) && $record_data['receiver_addr'] == '*****') {
                return 2;
            }
        } else {
            if (isset($record_data['return_addr']) && $record_data['return_addr'] == '*****') {
                return 2;
            }
        }

        //    $is_encrypt = load_model('sys/security/CustomersSecurityModel')->is_encrypt_sale_channel($sale_channel_code);
        $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($shop_code);
        $is_encrypt = empty($ret_encrypt['data']) ? FALSE : TRUE;

        if ($cfg['safety_control'] == 0 && $is_encrypt === FALSE) {
            return 0; //返回不做任何处理
        }
        if ($cfg['safety_control'] == 1 && $is_encrypt === FALSE) {
            return 1; //检查加密字段
        }

        if ($is_encrypt === TRUE) {
            return 2; //不检查加密字段
        }
    }

    function reset_record_info($sell_record_code) {
        $sql = "select shop_code,sale_channel_code,deal_code_list,customer_code,customer_address_id from oms_sell_record where sell_record_code=:sell_record_code ";
        $record_info = $this->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
        //select * from api_taobao_fx_trade where fenxiao_id='23441267151028'
        $deal_arr = explode(",", $record_info['deal_code_list']);

        $deal_code = $deal_arr[0];
        $sql = "select shop_code,buyer_nick,receiver_country,receiver_province,receiver_city,
                receiver_district,receiver_street,receiver_name,receiver_addr,receiver_mobile,receiver_phone
                from  api_order where tid=:tid";
        $order_info = $this->db->get_row($sql, array(':tid' => $deal_code));
        if (empty($order_info)) {
            $sql = "SELECT
                receiver_name AS buyer_nick,
                receiver_name,
                '中国' AS receiver_country,
                receiver_state AS receiver_province,
                receiver_city,
                receiver_district,
                receiver_address AS receiver_addr,
                receiver_mobile_phone AS receiver_mobile,
                IFNULL(receiver_phone, '') AS receiver_phone
               FROM
           api_taobao_fx_trade  where fenxiao_id=:fenxiao_id";
            $order_info = $this->db->get_row($sql, array(':fenxiao_id' => $deal_code));
        }
    
        if (!empty($order_info)) {
            $shop_code = $record_info['shop_code'];
            $order_info['customer_code'] = $record_info['customer_code'];
            $is_encrpty = $this->is_encrypt_value($order_info['buyer_nick'], 'taobao', 'buyer_nick');
            if ($is_encrpty === true) {
                $buyer_nick = $this->decrypt_shop_text($order_info['buyer_nick'], 'buyer_nick', $shop_code);
                if ($buyer_nick === false || $buyer_nick === $order_info['buyer_nick']) {
                    return $this->format_ret(-1, '', '解密失败！');
                }
                $order_info['buyer_nick'] = $buyer_nick;

                $buyer_name_code = load_model('crm/CustomerOptModel')->get_str_only_code($buyer_nick);
                $sql = "select customer_code,shop_code,customer_name_encrypt from crm_customer  where  customer_name_code=:customer_name_code  AND source=:source  ";
                $customer_info = $this->db->get_row($sql, array(':customer_name_code' => $buyer_name_code, ':source' => $record_info['sale_channel_code']));
                if (!empty($customer_info)) {
                    $buyer_nick_encrypt = $this->encrypt_shop_value($buyer_nick, 'buyer_nick', $customer_info['shop_code']);
                    if ($buyer_nick_encrypt !== false && $buyer_nick_encrypt != $buyer_nick) {
                   
               
                        if ($buyer_nick_encrypt != $customer_info['customer_name_encrypt']) {
                            $this->db->update('crm_customer', array('customer_name_encrypt' => $buyer_nick_encrypt), " customer_code='{$customer_info['customer_code']}' AND shop_code='{$customer_info['shop_code']}'  ");
                            $this->db->update('crm_customer_address_encrypt', array('buyer_name' => $buyer_nick_encrypt), " customer_code='{$customer_info['customer_code']}' AND shop_code='{$customer_info['shop_code']}' ");
                        }

                        if ($record_info['customer_code'] != $customer_info['customer_code']) {
                            $this->db->update('oms_sell_record', array('customer_code' => $customer_info['customer_code']), "sell_record_code = '{$sell_record_code}'");
                        }
                    }  
                    
                    $ret = $this->check_addres_info($order_info, $shop_code);
                    if ($ret['status'] < 1) {
                        return $ret;
                    }
                    if ($ret['data']['customer_address_id'] != $order_info['customer_address_id']) {
                        $this->db->update('oms_sell_record', array('customer_address_id' => $ret['data']['customer_address_id']), "sell_record_code = '{$sell_record_code}'");
                        return $this->format_ret(1, '', '成功修复数据，此次修复可能存在异常(如:修改地址信息)，请核查加密字段信息!');
                    }


                    return $this->format_ret(1, '', '成功修复数据，请核查加密字段信息!');
                } else {
                    $ret = $this->check_addres_info($order_info, $shop_code);
                    if ($ret['status'] < 1) {
                        return $ret;
                    }
                    if ($ret['data']['customer_address_id'] != $order_info['customer_address_id'] || $ret['data']['customer_code'] != $order_info['customer_code']) {
                        $new_customer = array(
                            'customer_address_id' => $ret['data']['customer_address_id'],
                            'customer_code' => $ret['data']['customer_code'],
                        );
                        $this->db->update('oms_sell_record', $new_customer, "sell_record_code = '{$sell_record_code}'");
                        return $this->format_ret(1, '', '成功修复数据，此次修复可能存在异常(如:修改地址信息)，请核查加密字段信息!');
                    }
                    return $this->format_ret(1, '', '成功修复数据，请核查加密字段信息!');
                }
            } else {
                return $this->format_ret(-1, '', '请联系客服处理！');
            }
        } else {
            return $this->format_ret(-1, '', '未找到原始单据数据');
        }
    }

    function check_addres_info($order_data, $shop_code) {
        $key_arr = array('name' => 'receiver_name', 'address' => 'receiver_addr', 'tel' => 'receiver_mobile', 'home_tel' => 'receiver_phone');
        $customer_address_data = array();
        foreach ($key_arr as $c_k => $key) {
            $text = $order_data[$key];
            $is_encrpty = $this->is_encrypt_value($text, 'taobao', $key);
            if (!empty($order_data[$key]) && $is_encrpty === true) {
                $decrypt_text = $this->decrypt_shop_text($text, 'string', $shop_code);
                if ($decrypt_text === false || $decrypt_text === $text) {
                    return $this->format_ret(-1, '', '解密失败！');
                }
                $order_data[$key] = $decrypt_text;
            } else {
                $order_data[$key] = $text;
            }
            $customer_address_data[$c_k] = $order_data[$key];
        }
        $customer_address_data['buyer_name'] = $order_data['buyer_nick'];
        $customer_address_data['customer_code'] = $order_data['customer_code'];
        $ret_addr = load_model('oms/trans_order/AddrCommModel')->match_addr($order_data);
        if (!empty($ret_addr['status'] < 1)) {
            return $ret_addr;
        }

        $addr_info = $ret_addr['data'];
        $customer_address_data['city'] = $addr_info['receiver_city'];
        $customer_address_data['district'] = $addr_info['receiver_district'];
        $customer_data = load_model('crm/CustomerOptModel')->check_is_create_address($customer_address_data);
        if (!empty($customer_data)) {

            $sql = "select shop_code from crm_customer_address_encrypt where customer_address_id='{$customer_data['customer_address_id']}'";
            $e_shop_code = $this->db->get_value($sql);
            $e_address_data = array();
            foreach ($key_arr as $n_key => $_ekey) {
                if (!empty($customer_address_data[$n_key])) {
                    $e_address_data[$n_key] = $this->encrypt_shop_value($customer_address_data[$n_key], 'string', $shop_code);
                    if ($e_address_data[$n_key] === false || $e_address_data[$n_key] === $customer_address_data[$n_key]) {
                        return $this->format_ret(-1, '', '加密失败！');
                    }
                }
            }
            
            $e_address_data['buyer_name'] = $this->encrypt_shop_value($order_data['buyer_nick'], 'string', $shop_code);
            $e_address_data['shop_code'] = $shop_code ;
            $e_address_data['encrypt_id'] = $this->db->get_value("SELECT id from sys_encrypt where shop_code=:shop_code AND status=1",array(':shop_code'=>$shop_code));
           
            $this->db->update('crm_customer_address_encrypt', $e_address_data, " customer_address_id='{$customer_data['customer_address_id']}'");
    
            return $this->format_ret(1, $customer_data);
        }
        $customer_address_data['customer_name'] = $order_data['buyer_nick'];
        $customer_address_data['shop_code'] = $shop_code;
        $customer_address_data['source'] = $order_data['source'];
        $customer_address_data['country'] = $order_data['receiver_country'];
        $customer_address_data['province'] = $order_data['receiver_province'];
        $customer_address_data['is_add_time'] = date('Y-m-d H:i:s');
        return load_model('crm/CustomerOptModel')->handle_customer($customer_address_data);
    }

}
