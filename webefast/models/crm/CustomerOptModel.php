<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CustomerOptModel
 *
 * @author wq
 */
require_lib('util/web_util', true);
require_lib('comm_util', true);

class CustomerOptModel extends TbModel {

    private $is_new_create = 0;
    private $taobao_encrypt = array(
        'receiver_name' => 'name',
        'buyer_nick' => 'buyer_name',
        'receiver_addr' => 'address',
        'receiver_mobile' => 'tel',
        'receiver_phone' => 'home_tel',
    );

    function get_table() {
        return'crm_customer';
    }

    /**
     * create new sell_record code
     * @return string
     */
    function create_code() {
        $num = $this->db->get_seq_next_value('crm_customer_seq');
        $code = 'c' . $num;
        return $code;
    }

    function add_customer($data) {
        $customer_name_code = md5($data['customer_name']);
        $is_page = isset($data['is_page']) ? $data['is_page'] : 0;

        $sql = "select customer_code,customer_name_encrypt,shop_code from crm_customer where customer_name_code=:customer_name_code AND source=:source ";
        $customer_row = $this->db->get_row($sql, array(':customer_name_code' => $customer_name_code, ':source' => $data['source']));

        if (empty($customer_row)) {

            $this->is_new_create = 1;
            $customer_code = $this->create_code();
            $data['customer_code'] = $customer_code;
            $data['customer_name_code'] = $customer_name_code;

            $key_arr = array(
                'customer_name_code', 'customer_code', 'customer_name', 'customer_level', 'source', 'shop_code', 'customer_tel', 'customer_sex', 'email', 'work_address', 'birthday', 'customer_integral', 'consume_money', 'consume_num', 'password', 'id_card', 'status', ' nickname', 'type', 'level_time', 'is_online', 'is_offline', 'country', 'province', 'city', 'district', 'marriage', 'education', 'weixin_id', 'is_edit', 'remark', 'is_add_person', 'is_add_time', 'is_edit_person', 'is_edit_time', 'home_tel', 'tel', 'address'
            );
            $customer_data = $this->get_key_data($data, $key_arr);
            $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($customer_data['shop_code']);
            if (!empty($ret_encrypt['data'])) {
                $encrypt_str = '*****';
                $customer_data['customer_name_encrypt'] = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($customer_data['customer_name'], 'buyer_nick', $customer_data['shop_code']);
                //失败再来一次
                if ($customer_data['customer_name_encrypt'] == '~~0~') {
                    $customer_data['customer_name_encrypt'] = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($customer_data['customer_name'], 'buyer_nick', $customer_data['shop_code']);
                }
                //失败返回
                if ($customer_data['customer_name_encrypt'] == '~~0~') {
                    $customer_data['customer_name_encrypt'] = false;
                }

                if ($customer_data['customer_name_encrypt'] !== false) {

                    $customer_data['customer_name'] = $this->get_hide_name($customer_data['customer_name']);
                } else {
                    return $this->format_ret(-1, '', '数据加密异常，稍后再试！');
                    //  $customer_data['customer_name_encrypt'] = '';
                }


                if ($is_page == 0) {
                    $customer_data['tel'] = '';
                    $customer_data['home_tel'] = '';
                }


                $customer_data['address'] = isset($customer_data['address']) && !empty($customer_data['home_tel']) ? $encrypt_str : '';
            }

            $customer_data['is_add_time'] = date('Y-m-d H:i:s');
            $this->insert($customer_data);
        } else {
            $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($customer_row['shop_code']);
            if (!empty($ret_encrypt['data']) && (empty($customer_row['customer_name_encrypt']) || $customer_row['shop_code'] != $data['shop_code'])) {
                $customer_data['customer_name_encrypt'] = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($data['customer_name'], 'buyer_nick', $customer_row['shop_code']);
                if ($customer_data['customer_name_encrypt'] !== false) {
                    $customer_data['customer_name'] = $this->get_hide_name($data['customer_name']);
                } else {
                    if ($customer_row['shop_code'] == $data['shop_code']) {//店铺相同
                        return $this->format_ret(-1, '', '数据加密异常，稍后再试！');
                    } else {
                        $customer_data['customer_name_encrypt'] = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($data['customer_name'], 'buyer_nick', $data['shop_code']);
                        if ($customer_data['customer_name_encrypt'] !== false) {
                            $customer_data['customer_name'] = $this->get_hide_name($data['customer_name']);
                            $customer_data['shop_code'] = $data['shop_code'];
                        } else {
                            return $this->format_ret(-1, '', '数据加密异常，稍后再试！');
                        }
                    }
                }
                $this->update($customer_data, array('customer_code' => $customer_row['customer_code']));
            }
            $customer_code = $customer_row['customer_code'];
        }

        return $this->format_ret(1, $customer_code);
    }

    function get_customer_address_id_with_search($val, $type) {
        $type_arr = array('tel', 'home_tel', 'name');
        if (!in_array($type, $type_arr)) {
            return array();
        }

        $val_code = $this->get_str_only_code($val);
        $sql = "select customer_address_id from crm_customer_address  where  {$type}_code =:type_code   ";
        $data = $this->db->get_all($sql, array(':type_code' => $val_code));
        if (empty($data)) {
            $sql = "select customer_address_id from crm_customer_address  where  {$type}=:type   ";
            $data = $this->db->get_all($sql, array(':type' => $val));
        }
        $customer_address_id_arr = array();
        if (!empty($data)) {
            $customer_address_id_arr = array_column($data, 'customer_address_id');
        }

        return $customer_address_id_arr;
    }

    function get_customer_code_with_search($buyer_name) {
        $buyer_name_code = $this->get_str_only_code($buyer_name);
        $customer_code_arr = array();
        $customer_code_arr2 = array();
        $sql_t = "select customer_code from crm_customer  where  customer_name_code=:customer_name_code   ";
        $data_t = $this->db->get_all($sql_t, array(':customer_name_code' => $buyer_name_code));
        if (!empty($data_t)) {
            $customer_code_arr = array_column($data_t, 'customer_code');
        }

        $sql_o = "select customer_code from crm_customer  where  customer_name=:customer_name   ";
        $data_o = $this->db->get_all($sql_o, array(':customer_name' => $buyer_name));

        if (!empty($data_o)) {
            $customer_code_arr2 = array_column($data_o, 'customer_code');
        }
        if (!empty($customer_code_arr2)) {
            $customer_code_arr = array_merge($customer_code_arr, $customer_code_arr2);
        }

        return $customer_code_arr;
    }

    function get_customer_code_by_buyer_name($buyer_name, $source = 'taobao') {
        $buyer_name_code = $this->get_str_only_code($buyer_name);
        $sql_t = "select customer_code from crm_customer  where  customer_name_code=:customer_name_code    ";
        $sql_values = array(':customer_name_code' => $buyer_name_code);
        if (!empty($source)) {
            $sql_t.= " AND source=:source ";
            $sql_values[':source'] = $source;
        }
        $data_t = $this->db->get_all($sql_t, $sql_values);
        $customer_code_arr = array();
        if (!empty($data_t)) {
            $customer_code_arr = array_column($data_t, 'customer_code');
        }
        return $customer_code_arr;
    }

    function get_customer_address_id_by_tel($tel, $source = 'taobao') {
        $tel_code = $this->get_str_only_code($tel);
        $sql_t = "select customer_address_id from crm_customer_address  where  tel_code=:tel_code    ";
        $sql_values = array(':tel_code' => $tel_code);
        if (!empty($source)) {
            $sql_t.= " AND source=:source ";
            $sql_values[':source'] = $source;
        }
        $data_t = $this->db->get_all($sql_t, $sql_values);
        $customer_address_id_arr = array();
        if (!empty($data_t)) {
            $customer_address_id_arr = array_column($data_t, 'customer_address_id');
        }
        return $customer_address_id_arr;
    }

    function handle_customer($customer) {
        $this->is_new_create = 0;
        $ret_add = $this->add_customer($customer);
        if ($ret_add['status'] < 1) {
            return $ret_add;
        }
        $customer_code = &$ret_add['data'];
        $customer['customer_code'] = $customer_code;
        $ret = $this->create_customer_address($customer);
        return $ret;
    }

    /*
     * 明文地址唯一编码
     */

    function get_only_code($address_info) {
        $key_arr = array(
            'city', 'district', 'address', 'name', 'tel',
            'home_tel', 'buyer_name',
        );
        if (!isset($address_info['buyer_name'])||empty($address_info['buyer_name'])) {
            $address_info['buyer_name'] = $address_info['customer_name'];
        }

        $only_arr = array();
        foreach ($key_arr as $key) {
            if (isset($address_info[$key])) {
                $only_arr[$key] = !empty($address_info[$key]) ? $address_info[$key] : '';
            } else {
                $only_arr[$key] = '';
            }
        }
        ksort($only_arr);
        $md5_str = json_encode($only_arr);
        return md5($md5_str);
    }

    function get_buyer_name_by_code($customer_code, $customer_address_id = 0) {


        $data = $this->get_customer_by_code($customer_code, true);
        $customer_name = $data['customer_name'];
        if ($customer_address_id > 0 && !empty($data['customer_name_encrypt'])) {
            $customer_name = load_model('sys/security/CustomersSecurityModel')->get_customer_decrypt($customer_address_id, 'buyer_name');
        } else {
            $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($data['shop_code']);
            if (!empty($ret_encrypt['data']) && !empty($data['customer_name_encrypt'])) {
                $customer_name = load_model('sys/security/CustomersSecurityModel')->decrypt_shop_text($data['customer_name_encrypt'], 'name', $data['shop_code']);
            }
        }


        return $customer_name;
    }

//添加地址信息
    function insert_customer_address($info, $is_encrypt = 0) {
        $encrypt_str = '*****';
        if ($is_encrypt == 1) {
            $address_info = $this->check_is_create_address($info);
            if (!empty($address_info)) {
                //如果街道不为空处理下
                if (!empty($info['street'])) {
                    $up_data['street'] = $info['street'];
                }
                $this->db->update('crm_customer_address', $up_data, "customer_address_id ='{$address_info['customer_address_id']}' ");

                return $this->format_ret(2, $address_info);
            }
        }

        $customer_address_id = $this->check_is_address($info);
        if (!empty($customer_address_id)) {
            $info['customer_address_id'] = $customer_address_id;
            $sql = "select customer_address_id, name from crm_customer_address where customer_code = :customer_code";
            $address_arr = $this->db->getAll($sql,['customer_code' => $info['customer_code']]);
            if(count($address_arr)==1){ //如果添加的地址信息和表中的一样，并且表中只有一条信息，修改添加的地址信息is_default默认1
                $info['is_default'] = 1;
            }
        }

        $only_code_arr = array('tel', 'home_tel', 'name');
        foreach ($only_code_arr as $key) {
            $c_key = $key . "_code";
            if (!empty($info[$key])) {
                $info[$c_key] = $this->get_str_only_code($info[$key]);
            } else {
                $info[$c_key] = '';
            }
        }

        if ($is_encrypt == 1) {
            $info['tel'] = substr($info['tel'], 0, 3) . $encrypt_str;
            $info ['home_tel'] = substr($info['home_tel'], 0, 3) . $encrypt_str;
            $info['name'] = $this->get_hide_name($info['name']);
            $info['address'] = $encrypt_str;
        }
        $key_arr = array('customer_code', 'address', 'country', 'province', 'city', 'district', 'zipcode', 'tel', 'home_tel', 'is_add_time', 'name', 'street', 'is_default', 'only_code', 'tel_code', 'home_tel_code', 'name_code');

        $new_data = $this->get_key_data($info, $key_arr);

        $new_data['address_detail'] = $this->get_address_detail($new_data);
        //更新
        if (isset($info['customer_address_id']) && !empty($info['customer_address_id'])) {
            $this->update_exp('crm_customer_address', $new_data, "customer_address_id='{$info['customer_address_id']}'");
            return $this->format_ret(1, $info);
        }


        if ($this->is_new_create == 1) {
            $new_data['is_add_time'] = date('Y-m-d H:i:s');
            $new_data['is_default'] = 1;
            $result = $this->insert_exp('crm_customer_address', $new_data);
            $info['customer_address_id'] = $result['data'];
        } else {
            if ($customer_address_id == 0) {
                $result = $this->insert_exp('crm_customer_address', $new_data);
                $info['customer_address_id'] = $result['data'];
            } else {
                $info['customer_address_id'] = $customer_address_id;
            }
        }
        return $this->format_ret(1, $info);
    }

    function create_customer_address($customer_address_data) {
        $customer_data = $this->get_customer_by_code($customer_address_data['customer_code'], true);

        $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($customer_data['shop_code']);
        $is_encrypt = empty($ret_encrypt['data']) ? 0 : 1;


        //判断店铺是否开启加密
        $is_update = isset($customer_address_data['customer_address_id']) && !empty($customer_address_data['customer_address_id']) ? 1 : 0;
        $this->begin_trans();
        $result = $this->insert_customer_address($customer_address_data, $is_encrypt);
        $customer_address_data['customer_address_id'] = $result['data']['customer_address_id'];
        if ($is_encrypt == 1) {
            $check_row = $this->check_is_create_address($customer_address_data);
            if(isset($check_row['customer_address_id'])){
                $customer_address_data['customer_address_id'] = $check_row['customer_address_id'];
            }
            $check_row['shop_code'] = !isset($check_row['shop_code']) ? '' : $check_row['shop_code'];
            if ($check_row['shop_code'] != $customer_address_data['shop_code']) {
                $ret = $this->create_address_encrypt($customer_address_data, $is_update);
                if ($ret['status'] < 1) {
                    $this->rollback();
                    return $ret;
                }
                $customer_address_data['customer_address_id'] = !empty($ret['data']) ? $ret['data'] : $customer_address_data['customer_address_id'];
            }
        }
        $this->commit();
        return $this->format_ret(1, $customer_address_data);
    }

    function get_customer_encryp_id($customer_code) {
        $data = $this->get_customer_by_code($customer_code, true);
        $encryp_data = load_model('sys/security/SysEncrypModel')->get_encrypt_info_by_shop($data['shop_code']);
        if (empty($encryp_data)) {
            return 0;
        }
        return $encryp_data['id'];
    }

    function create_address_encrypt($customer_address_data, $is_update = 0) {

        $data = $this->get_customer_by_code($customer_address_data['customer_code']);
//        $shop_code = $data['shop_code'];
//        if (isset($customer_address_data['shop_code']) && $customer_address_data['shop_code'] != $shop_code) {
        $shop_code = $customer_address_data['shop_code']; //直接取明细加密
        //   }
        $encryp_data = load_model('sys/security/SysEncrypModel')->get_encrypt_info_by_shop($shop_code);
        if (empty($encryp_data)) {
            return $this->format_ret(-1, '', '店铺未开启加密！');
        }
        $encryp_id = $encryp_data['id'];
        if (!isset($customer_address_data['buyer_name']) && isset($customer_address_data['customer_name'])) {
            $customer_address_data['buyer_name'] = $customer_address_data['customer_name'];
        }



        foreach ($this->taobao_encrypt as $type => $type_key) {
            if (empty($customer_address_data[$type_key])) {
                continue;
            }
            $encryp_text = $customer_address_data[$type_key];
            //  $type = $type == 'receiver_mobile' ? 'phone' : $type; //手机号是特殊处理类型
            //取消手机浩加密
            $type = 'string';
            $customer_address_data[$type_key] = load_model('sys/security/CustomersSecurityModel')->encrypt_value($encryp_text, $type, $encryp_id);
            if (empty($customer_address_data[$type_key]) || $customer_address_data[$type_key] === FALSE || $customer_address_data[$type_key] == $encryp_text) {
                return $this->format_ret(-1, '', '数据加密异常:加密不成功');
            }
        }
        if (!isset($customer_address_data['buyer_name'])) {
            $customer_address_data['buyer_name'] = $data['customer_name_encrypt'];
        }

        $customer_address_data['shop_code'] = $shop_code;
        $customer_address_data['encrypt_id'] = $encryp_id;

        $update_str = "shop_code = VALUES(shop_code),address = VALUES(address),tel = VALUES(tel),home_tel = VALUES(home_tel),name = VALUES(name),buyer_name = VALUES(buyer_name),encrypt_id = VALUES(encrypt_id)";

        $ret = $this->insert_multi_duplicate('crm_customer_address_encrypt', array($customer_address_data), $update_str);
        $sql_customer_address_id = "select customer_address_id from crm_customer_address_encrypt where customer_code=:customer_code AND only_code=:only_code ";
        $customer_address_id = $this->db->get_value($sql_customer_address_id, array(':customer_code' => $customer_address_data['customer_code'], ':only_code' => $customer_address_data['only_code']));
        return $this->format_ret(1, $customer_address_id);
    }

    function get_address_detail($new_data) {
        $area_id_arr = array();
        $key_arr = array('province', 'city', 'district', 'street');
        foreach ($key_arr as $key) {
            if (isset($key)) {
                $area_id_arr[] = $new_data[$key];
            }
        }
        $str = "";
        $area_id_str = "'" . implode("','", $area_id_arr) . "'";
        $sql = "select id,name from base_area where id in ({$area_id_str})  order by type ";
        $data = $this->db->get_all($sql);

        foreach ($data as $val) {
            $str.=$val['name'];
        }
        $str .=$new_data['address'];
        return $str;
    }

    private function check_is_address($info) {
        $sql = "select customer_address_id, name,province,city,district,street,address,tel from crm_customer_address where customer_code = :customer_code";
        $old_info = CTX()->db->get_all($sql, array('customer_code' => $info['customer_code']));
        $info['district'] = empty($info['district']) ? '' : $info['district'];
        if (!empty($old_info)) {
            foreach ($old_info as $val) {
                $val['district'] = empty($val['district']) ? '' : $val['district'];

                if (
                        $val['name'] == $info['name'] &&
                        $val['province'] == $info['province'] && $val['city'] == $info['city'] &&
                        $val['district'] == $info['district'] &&
                        $val['address'] == $info['address'] && $val['tel'] == $info['tel']
                ) {
                    if ($info['street'] != $val['street']) {
                        $this->update_exp('crm_customer_address', array('street' => $info['street']), "customer_address_id={$val['customer_address_id']}");
                    }
                    return $val['customer_address_id'];
                }
            }
        }
        return 0;
    }

    function get_key_data($data, $key_arr) {
        $new_data = array();

        foreach ($key_arr as $key) {
            if (isset($data[$key])) {
                $new_data[$key] = $data[$key];
            }
        }
        return $new_data;
    }

    function check_is_create_customer($buyer_name, $source = 'taobao') {
        $sql = "select customer_code from crm_customer  where customer_name_code=:customer_name_code AND source=:source";
        $customer_name_code = md5($buyer_name);
        $customer_code = $this->db->get_value($sql, array(':customer_name_code' => $customer_name_code, ':source' => $source));
        return $customer_code;
    }

    function check_is_create_address(&$address_info) {
        $only_code = $this->get_only_code($address_info);
        $row = $this->check_is_only_address($only_code, $address_info['customer_code']);
        $address_info['only_code'] = $only_code;

        return $row;
    }

    private function check_is_only_address($only_code, $customer_code) {
        $sql = "select  customer_address_id,shop_code,customer_code from crm_customer_address_encrypt where only_code = :only_code AND customer_code=:customer_code";
        $row = $this->db->get_row($sql, array(':only_code' => $only_code, ':customer_code' => $customer_code));
        return $row;
    }

    function get_customer_address_encrypt($customer_address_id, $msg = '') {
        $customer_address = $this->get_customer_address($customer_address_id);
        $type_arr = array('address', 'tel', 'home_tel', 'name');
        foreach ($type_arr as $type) {
            $customer_address[$type] = load_model('sys/security/CustomersSecurityModel')->get_customer_decrypt($customer_address_id, $type);
        }
        $log_data['customer_address_id'] = $customer_address_id;
        $log_data['customer_code'] = $customer_address['customer_code'];
        $log_data['record_code'] = '无';
        $log_data['record_type'] = '无';
        $log_data['action_note'] = empty($msg) ? '会员编辑地址查看' : '';
        load_model('sys/security/CustomersSecurityLogModel')->add_log($log_data);


        return $customer_address;
    }

    function get_hide_name($name) {
        return substr_utf8($name, 0, 1) . '***';
    }

    function get_customer_by_code($customer_code, $is_cache = false) {
        static $customer_data = null;
        $customer_row = array();
        if ($is_cache === FALSE || !isset($customer_data[$customer_code])) {
            $customer_row = $this->db->get_row("select * from {$this->table} where customer_code=:customer_code ", array(':customer_code' => $customer_code));
        } else if (isset($customer_data[$customer_code])) {
            $customer_row = $customer_data[$customer_code];
        }
        $is_cache === true ?
                        $customer_data[$customer_code] = $customer_row : false;

        return $customer_row;
    }

    function get_customer_address($customer_address_id) {
        $customer_address = $this->db->get_row("select * from crm_customer_address where customer_address_id=:customer_address_id ", array(':customer_address_id' => $customer_address_id));
        return $customer_address;
    }

    function get_customer_name($customer_code) {
        $customer_data = $this->get_customer_by_code($customer_code, true);
        return $customer_data['customer_name'];
    }

    function get_str_only_code($str) {
        return md5($str);
    }

    //读取数据
    function imoprt_detail($file) {
        set_time_limit(0);
        require_lib('csv_util');
        $exec = new execl_csv();
        $key_arr = array(
            'customer_name',
            'nickname',
            'sale_channel', //平台
            'shop_name',
            'customer_sex',
            'type', //黑名单
            'country',
            'province',
            'city',
            'district',
            'street',
            'address',
            'zipcode', //邮编
            'name', //收货人
            'tel',
            'home_tel',
            'is_default', //默认收货地址
        );
        $csv_data = $exec->read_csv($file, 2, $key_arr, array('customer_name', 'nickname', 'sale_channel', 'shop_name', 'customer_sex', 'type', 'country', 'province', 'city', 'district', 'street', 'address', 'zipcode', 'name', 'tel', 'home_tel', 'is_default'));
        if (!(is_array($csv_data) && count($csv_data) > 0)) {
            return $this->format_ret('-1', '', '没有找到需要导入的会员！');
        }
        $sale_channel_detail = load_model('base/SaleChannelModel')->get_data_code_map(); //获取所有平台
        $sale_channel = array_column($sale_channel_detail, 0, 1);
        $shop_list = $this->get_shop_detail();//把店铺名称和平台代码拼接
        $all_num = count($csv_data);
        $err = array();
        $data_arr = array();
        $add_arr = array();
        $i = 3;
        foreach ($csv_data as $key => $value) {
            $ret = $this->is_valid_excel_data($value, $key);
            if ($ret['status'] == 1) {
                // $i += $key;
                if (!array_key_exists($value['sale_channel'], $sale_channel)) {
                    $err[] = '第' . $i . '行平台在系统中不存在';
                    $i++;
                    continue;
                }
                $data_arr['source'] = $sale_channel[$value['sale_channel']];
                $shop_check = $value['shop_name'] .'_line_'.$data_arr['source'];//店铺名称和平台代码拼接
                if (!in_array($shop_check, $shop_list)) {
                    $err[] = '第' . $i . '行店铺不在对应的平台中';
                    $i++;
                    continue;
                }
                $data['shop_code'] = array_search($shop_check, $shop_list);
                //地址判断
                //过滤海外地址
                if($value['country'] != '中国'){
                    $err[] = '第' . $i . '行地址为海外，暂不支持海外地址导入';
                    $i++;
                    continue;
                }
                
                $add_arr['country'] = 1;
                if (!empty($value['province']) && !empty($value['city']) && !empty($value['district'])) {
                    $province = $this->get_area($value['province'], '1');
                    if (empty($province)) {
                        $err[] = '第' . $i . '行省份无法识别';
                        $i++;
                        continue;
                    }

                    $city = $this->get_area($value['city'], $province[0]['id']);
                    if (!empty($city)) {
                        $district = $this->get_area($value['district'], $city[0]['id']);
                    } else {
                        $err[] = '第' . $i . '行市无法识别';
                        $i++;
                        continue;
                    }
                    if (!empty($district)) {
                        if(!empty($value['street'])){
                           $street = $this->get_area($value['street'], $district[0]['id']); 
                        }
                    } else {
                        $err[] = '第' . $i . '行区无法识别';
                        $i++;
                        continue;
                    }
                    if(empty($street) && !empty($value['street'])){
                        $err[] = '第' . $i . '行街道无法识别';
                        $i++;
                        continue;
                    }
                }
                $add_arr['province'] = empty($city) ? '' : $province[0]['id'];
                $add_arr['city'] = empty($city) ? '' : $city[0]['id'];
                $add_arr['district'] = empty($district) ? '' : $district[0]['id'];
                $add_arr['street'] = empty($street) ? '' : $street[0]['id'];
                $add_arr['address'] = $value['address']; //不含省市区地址
                $add_arr['is_default'] = $value['is_default'] == '是' ? 1 : 0;

                $add_arr['name'] = $value['name'];
                $add_arr['tel'] = isset($value['tel']) ? $value['tel'] : '';
                $add_arr['home_tel'] = isset($value['home_tel']) ? $value['home_tel'] : '';
                $add_arr['shop_code'] = $data['shop_code'];
                $add_arr['zipcode'] = isset($value['zipcode']) ? $value['zipcode'] : '';

                $customer_name_code = md5($value['customer_name']);
                $sql = "select customer_code,customer_name_encrypt,shop_code from crm_customer where customer_name_code=:customer_name_code AND source=:source ";
                $customer_row = $this->db->get_row($sql, array(':customer_name_code' => $customer_name_code, ':source' => $data_arr['source']));

                //会员信息
                $data['nickname'] = isset($value['nickname']) ? $value['nickname'] : ''; //会员昵称
                if (empty($value['customer_sex']) || $value['customer_sex'] == '保密') {
                    $data['customer_sex'] = 3;
                }
                if ($value['customer_sex'] == '男') {
                    $data['customer_sex'] = 1;
                } elseif ($value['customer_sex'] == '女') {
                    $data['customer_sex'] = 2;
                }
                $data['type'] = $value['type'] == '是' ? 2 : 1; //黑名单

                if (empty($customer_row)) {
                    $this->is_new_create = 1;
                    $customer_code = $this->create_code(); //C76
                    $data['customer_code'] = $customer_code; //会员代码

                    $data['customer_name_code'] = $customer_name_code;
                    $data['source'] = $data_arr['source']; //平台
                    $data['customer_name'] = $value['customer_name']; //名称

                    $add_arr['customer_code'] = $customer_code;
                    $key_arr2 = array(
                        'customer_name_code', 'customer_code', 'customer_name', 'source', 'shop_code', 'customer_sex', 'type', 'nickname'
                    );

                    $customer_data[$key] = $this->get_key_data($data, $key_arr2);
                    $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($customer_data[$key]['shop_code']); //店铺加密

                    if (!empty($ret_encrypt['data'])) {
                        $encrypt_str = '*****';
                        $customer_data[$key]['customer_name_encrypt'] = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($customer_data[$key]['customer_name'], 'buyer_nick', $customer_data[$key]['shop_code']);
                        //失败再来一次
                        if ($customer_data[$key]['customer_name_encrypt'] == '~~0~') {
                            $customer_data[$key]['customer_name_encrypt'] = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($customer_data[$key]['customer_name'], 'buyer_nick', $customer_data[$key]['shop_code']);
                        }
                        //失败返回
                        if ($customer_data[$key]['customer_name_encrypt'] == '~~0~') {
                            $customer_data[$key]['customer_name_encrypt'] = false;
                        }

                        if ($customer_data[$key]['customer_name_encrypt'] !== false) {

                            $customer_data[$key]['customer_name'] = $this->get_hide_name($customer_data[$key]['customer_name']);
                        } else {
                            $err[] = '数据加密异常，稍后再试！';
                            continue;
                        }
                    }
                    $customer_data[$key]['is_add_time'] = date('Y-m-d H:i:s');
                    $this->insert($customer_data[$key]);
                    $rus = $this->create_customer_address($add_arr);
                } else {
                    $this->is_new_create = 0;
                    if ($add_arr['is_default'] == 1) {
                        load_model('crm/CustomerModel')->clear_default($customer_row['customer_code']);
                    }
                    $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($customer_row['shop_code']);
                    if (!empty($ret_encrypt['data']) && (empty($customer_row['customer_name_encrypt']) || $customer_row['shop_code'] != $value['shop_code'])) {
                        $customer_data['customer_name_encrypt'] = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($value['customer_name'], 'buyer_nick', $customer_row['shop_code']);
                        if ($customer_data['customer_name_encrypt'] !== false) {
                            $customer_data['customer_name'] = $this->get_hide_name($value['customer_name']);
                        } else {
                            if ($customer_row['shop_code'] == $value['shop_code']) {//店铺相同
                                $err[] = '数据加密异常，稍后再试！';
                                continue;
                            } else {
                                $customer_data['customer_name_encrypt'] = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($value['customer_name'], 'buyer_nick', $value['shop_code']);
                                if ($customer_data['customer_name_encrypt'] !== false) {
                                    $customer_data['customer_name'] = $this->get_hide_name($value['customer_name']);
                                    $customer_data['shop_code'] = $value['shop_code'];
                                } else {
                                    $err[] = '数据加密异常，稍后再试！';
                                    continue;
                                }
                            }
                        }
                    }
                    $customer_data = $data;

                    $this->update($customer_data, array('customer_code' => $customer_row['customer_code']));
                    $add_arr['customer_code'] = $customer_row['customer_code'];
                    $rus = $this->create_customer_address($add_arr);
                }
            } else {
                $err[] = $ret;
            }

            $i++;
        }
        $err_num = count($err);
        $rs['data'] = '';
        $rs['status'] = '1';
        $success_num = $all_num - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($err)) {
            $rs['status'] = '-1';
            $message .=',' . '失败数量:' . $err_num;
            $file_name = $this->create_import_fail_files($err, 'customer_import');
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $rs['message'] = $message;
        return $rs;
    }
    /**
     * 把店铺名称和平台代码进行拼接 cicishop旗舰店_line_taobao
     * @return type
     */
    function get_shop_detail() {
        $sql = "select CONCAT(shop_name,'_line_',sale_channel_code) AS shop_score,shop_code FROM base_shop where is_active=1";
        $shop_detail = $this->db->getAll($sql);
        $shop_list = array_column($shop_detail,'shop_score','shop_code');
        return $shop_list;
    }
    /**
     * 判定导入数据是否有效
     * @param type $row_data 行数据
     * @return true 有效 false 无效
     */
    function is_valid_excel_data($row_data, $key) {
        $key += 3;
        if ($row_data['customer_name'] == '') {
            $err = '第' . $key . '行会员名称不能为空;';
            return $err;
        }
        if ($row_data['sale_channel'] == '') {
            $err = '第' . $key . '行平台名称不能为空;';
            return $err;
        }
        if ($row_data['shop_name'] == '') {
            $err = '第' . $key . '行店铺不能为空;';
            return $err;
        }
        if ($row_data['country'] == '') {
            $err = '第' . $key . '行国家不能为空;';
            return $err;
        }
        if ($row_data['province'] == '') {
            $err = '第' . $key . '行省不能为空;';
            return $err;
        }
        if ($row_data['city'] == '') {
            $err = '第' . $key . '行市不能为空;';
            return $err;
        }
        if ($row_data['district'] == '') {
            $err = '第' . $key . '行区不能为空;';
            return $err;
        }
        if ($row_data['address'] == '') {
            $err = '第' . $key . '行详细地址不能为空;';
            return $err;
        }
        if ($row_data['name'] == '') {
            $err = '第' . $key . '行收货人不能为空;';
            return $err;
        }
        if ($row_data['tel'] == '' && $row_data['home_tel'] == '') {
            $err = '第' . $key . '行手机或固话必填一个';
            return $err;
        }
        return $this->format_ret(1);
    }

    function create_import_fail_files($msg_arr, $name) {
        $fail_top = array('错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg_arr as $key => $val) {
            $file_str .= $val . "\r\n";
        }
        $filename = md5($name . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    /**
     *
     * 获取区域
     * @param unknown_type $area_name
     * @param unknown_type $parent_id
     * @param unknown_type $parent_parent_id
     */
    function get_area($area_name, $parent_id, $parent_parent_id = NULL) {
        if (empty($area_name))
            return '';

        $area_info = '';

        if (!empty($parent_parent_id)) {
            $sql = "select id from base_area where parent_id = :parent_parent_id";
            $parent_data = $this->db->get_all($sql,array('parent_parent_id' => $parent_parent_id));

            $parent_list = array();

            foreach ($parent_data as $sub_parent_data) {
                $parent_list[] = $sub_parent_data['id'];
            }

            $parent_str = "'" . implode("','", $parent_list);
        }
        while (mb_strlen($area_name) > 1) {
            if (!empty($parent_id)) {
                $sql = "select id,parent_id,`name` from base_area where parent_id = '{$parent_id}' AND `name` LIKE '%" . $area_name . "%' limit 1";
            } else {
                $sql = "select id,parent_id,`name` from base_area where parent_id IN ($parent_str) AND `name` LIKE '%" . $area_name . "%' limit 1";
            }

            $area_info = $this->db->get_all($sql);

            if (empty($area_info)) {
                $area_name = mb_substr($area_name, 0, -1);
            } else {
                break;
            }
        }

        return $area_info;
    }

}
