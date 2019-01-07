<?php

require_lib('util/oms_util', true);
require_model('sys/security/CustomersSecurityOptModel');

class HistorySecurityOptModel extends CustomersSecurityOptModel {

    private $record_config = array();
    private $encrypt_status = true;

    function exec_security($request) {
        $ret = load_model('sys/security/SysEncrypRecordModel')->get_by_id($request['record_id']);
        $this->record_config = require_conf('oms/security/record_history');
        if (!empty($ret['data'])) {
            $method_name = "exec_" . $ret['data']['tb_name'];
            if (method_exists($this, $method_name)) {
                $this->$method_name($ret['data']);
            }
        }
    }

    function exec_oms_sell_record($record_info) {
        $select_arr = array(
            'shop_code', 'sale_channel_code',
            'sell_record_id', 'sell_record_code', 'customer_code', 'customer_address_id',
            'buyer_name', 'receiver_name', 'receiver_country', 'receiver_province', 'receiver_city',
            'receiver_district', 'receiver_street', 'receiver_address', 'receiver_addr',
            'receiver_mobile', 'receiver_phone'
        );
        $select = implode(',', $select_arr);
      $id = $record_info['sys_id'];
       $record_data = array();
       $record_data['num'] = $record_info['num'] ;
        $record_data['is_over'] = $record_info['is_over'] ;
        while ($record_info['sys_id'] < $record_info['max_id']) {
            $sql = "select {$select} from oms_sell_record   where ( sale_channel_code ='taobao' OR sale_channel_code ='fenxiao' ) ";
            $sql.=" AND sell_record_id >{$record_info['sys_id']}  AND sell_record_id <={$record_info['max_id']}   limit 1000  ";
            $data = $this->db->get_all($sql);
            $loop_num = 0;
            foreach ($data as $val) {
                $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($val['shop_code']);
                //未开启加密的跳过
                if($ret_encrypt['status']<1){
                    continue;
                }
                
                $address_info = $this->create_customer($val, 'sell_record');

                if ($address_info !== false) {
                    $this->update_exp('oms_sell_record', $address_info, " sell_record_code= '{$val['sell_record_code']}'");
                    $this->update_exp('oms_sell_record_cz', $address_info, " sell_record_code= '{$val['sell_record_code']}'");
                    $this->update_exp('oms_deliver_record', $address_info, " sell_record_code= '{$val['sell_record_code']}'");
                }
                if ($this->encrypt_status == false) {
                    break;
                }
                $loop_num++;
                $id = max($id, $val['sell_record_id']);
            }
           $record_info['sys_id'] = count($data) < 1000 ? $record_info['max_id'] : $id;
         
            $record_data['sys_id'] = $record_info['sys_id'];
            $record_data['num'] = $record_data['num'] + $loop_num;
            $record_data['is_over'] = $this->encrypt_status == false ? $record_data['is_over'] + 1 : $record_data['is_over'];
            $this->update_exp('sys_encrypt_record', $record_data, " id= {$record_info['id']}");
						
            if ($this->encrypt_status == false) {
                break;
            }
        }
    }

    function exec_oms_sell_return($record_info) {
        $select_arr = array(
            'shop_code', 'sale_channel_code',
            'sell_return_id', 'sell_return_code', 'customer_code', 'customer_address_id',
            'buyer_name', 'return_name', 'return_country', 'return_province', 'return_city', 'return_district', 'return_street', 'return_address', 'return_addr', 'return_mobile', 'return_phone',
            'change_name', 'change_country', 'change_province', 'change_city',
            'change_district', 'change_street', 'change_address', 'change_addr', 'change_mobile', 'change_phone', 'change_customer_address_id'
        );
        $select = implode(',', $select_arr);
        $id = $record_info['sys_id'];
        $record_data = array();
      $record_data['num'] = $record_info['num'] ;
        $record_data['is_over'] = $record_info['is_over'] ;
        while ($record_info['sys_id'] < $record_info['max_id']) {
            $sql = "select {$select} from oms_sell_return   where  ( sale_channel_code ='taobao' OR sale_channel_code ='fenxiao'  ) ";
            $sql.=" AND sell_return_id >{$record_info['sys_id']}  AND sell_return_id <={$record_info['max_id']}   limit 1000  ";
            $data = $this->db->get_all($sql);
            $loop_num = 0;
            foreach ($data as $val) {
                $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($val['shop_code']);
                //未开启加密的跳过
                if($ret_encrypt['status']<1){
                    continue;
                }
                
                $address_info = $this->create_customer($val, 'sell_return');

                if ($address_info !== false) {
                    $address_info2 = $this->create_customer($val, 'sell_return_change');
                    $address_info = array_merge($address_info, $address_info2);
                    $this->update_exp('oms_sell_return', $address_info, " sell_return_code= '{$val['sell_return_code']}'");
                }
                if ($this->encrypt_status == false) {
                    break;
                }
                $loop_num++;
                $id = max($id, $val['sell_return_id']);
            }
            $record_info['sys_id'] = count($data) < 1000 ? $record_info['max_id'] : $id;
         
            $record_data['sys_id'] = $record_info['sys_id'];
            $record_data['num'] = $record_data['num'] + $loop_num;
            $record_data['is_over'] = $this->encrypt_status == false ? $record_data['is_over'] + 1 : $record_data['is_over'];
            $this->update_exp('sys_encrypt_record', $record_data, " id= {$record_info['id']}");
            if ($this->encrypt_status == false) {
                break;
            }
        }
    }

    function exec_oms_return_package($record_info) {
        $select_arr = array(
            'shop_code',
            'return_package_id', 'return_package_code', 'customer_code', 'customer_address_id',
            'buyer_name', 'return_name', 'return_country', 'return_province', 'return_city', 'return_district', 'return_street', 'return_address', 'return_addr', 'return_mobile', 'return_phone',
        );
        $select = implode(',', $select_arr);
        $shop_all = $this->db->get_all("select shop_code from base_shop where sale_channel_code ='taobao'");
        $shop_code_arr = array_column($shop_all, 'shop_code');
        $shop_str = "'" . implode("','", $shop_code_arr) . "'";
        $id = $record_info['sys_id'];
        $record_data = array();
      $record_data['num'] = $record_info['num'] ;
        $record_data['is_over'] = $record_info['is_over'] ;

        while ($record_info['sys_id'] < $record_info['max_id']) {
            $sql = "select {$select} from oms_return_package   where  shop_code in ({$shop_str}) ";
            $sql.=" AND return_package_id >{$record_info['sys_id']}  AND return_package_id <={$record_info['max_id']}   limit 1000  ";
            $data = $this->db->get_all($sql);
            $loop_num = 0;
            foreach ($data as $val) {
                $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($val['shop_code']);
                //未开启加密的跳过
                if($ret_encrypt['status']<1){
                    continue;
                }
                
                $address_info = $this->create_customer($val, 'sell_return');
                if ($address_info !== false) {

                    $this->update_exp('oms_return_package', $address_info, " return_package_code= '{$val['return_package_code']}'");
                }
                if ($this->encrypt_status == false) {
                    break;
                }
                $loop_num++;
                $id = max($id, $val['return_package_id']);
            }
           $record_info['sys_id'] = count($data) < 1000 ? $record_info['max_id'] : $id;
         
            $record_data['sys_id'] = $record_info['sys_id'];
            $record_data['num'] = $record_data['num'] + $loop_num;
            $record_data['is_over'] = $this->encrypt_status == false ? $record_data['is_over'] + 1 : $record_data['is_over'];
            $this->update_exp('sys_encrypt_record', $record_data, " id= {$record_info['id']}");
            if ($this->encrypt_status == false) {
                break;
            }
        }
    }

    function exec_api_order($record_info) {
        $select_arr = array(
            'shop_code', 'source',
            'id', 'customer_code', 'customer_address_id', 'buyer_nick',
            'receiver_name', 'receiver_country', 'receiver_province',
            'receiver_city', 'receiver_district', 'receiver_street',
            'receiver_address', 'receiver_addr',
            'receiver_mobile', 'receiver_phone', 'receiver_email',
        );
        $select = implode(',', $select_arr);
             $id = $record_info['sys_id'];
        $record_data = array();
      $record_data['num'] = $record_info['num'] ;
        $record_data['is_over'] = $record_info['is_over'] ;
   
        while ($record_info['sys_id'] < $record_info['max_id']) {
            $sql = "select {$select} from api_order   where  ( source ='taobao' OR source ='fenxiao' )  ";
            $sql.=" AND id >{$record_info['sys_id']}  AND id <={$record_info['max_id']}   limit 1000  ";
            $data = $this->db->get_all($sql);
            $loop_num = 0;
            foreach ($data as $val) {
                $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($val['shop_code']);
                //未开启加密的跳过
                if($ret_encrypt['status']<1){
                    continue;
                }
                
                $new_info = $this->encryp_api_data($val, 'api_order');
                if (!empty($new_info) && $new_info !== false) {
                    $this->update_exp('api_order', $new_info, " id= '{$val['id']}'");
                }
                if ($this->encrypt_status == false) {
                    break;
                }
                $loop_num++;
                $id = max($id, $val['id']);
            }
           $record_info['sys_id'] = count($data) < 1000 ? $record_info['max_id'] : $id;
         
            $record_data['sys_id'] = $record_info['sys_id'];
            $record_data['num'] = $record_data['num'] + $loop_num;
            $record_data['is_over'] = $this->encrypt_status == false ? $record_data['is_over'] + 1 : $record_data['is_over'];
            $this->update_exp('sys_encrypt_record', $record_data, " id= {$record_info['id']}");
            if ($this->encrypt_status == false) {
                break;
            }
        }
    }

    function exec_api_taobao_fx_trade($record_info) {

        $select = "*";
        $id = $record_info['sys_id'];
    $record_data['num'] = $record_info['num'] ;
        $record_data['is_over'] = $record_info['is_over'] ;

        while ($record_info['sys_id'] < $record_info['max_id']) {
            $sql = "select {$select} from api_taobao_fx_trade   where   ";
            $sql.="  ttid >{$record_info['sys_id']}  AND ttid <={$record_info['max_id']}   limit 1000  ";
            $data = $this->db->get_all($sql);
            $loop_num = 0;
            foreach ($data as $val) {
                $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($val['shop_code']);
                //未开启加密的跳过
                if($ret_encrypt['status']<1){
                    continue;
                }
                
                $new_info = $this->encryp_api_data($val, 'api_taobao_fx_trade');
                if (!empty($new_info) && $new_info !== false) {
                    $this->update_exp('api_taobao_fx_trade', $new_info, " ttid= '{$val['ttid']}'");
                }

                if ($this->encrypt_status == false) {
                    break;
                }
                $loop_num++;
                $id = max($id, $val['ttid']);
            }
           $record_info['sys_id'] = count($data) < 1000 ? $record_info['max_id'] : $id;
         
            $record_data['sys_id'] = $record_info['sys_id'];
            $record_data['num'] = $record_data['num'] + $loop_num;
            $record_data['is_over'] = $this->encrypt_status == false ? $record_data['is_over'] + 1 : $record_data['is_over'];
            $this->update_exp('sys_encrypt_record', $record_data, " id= {$record_info['id']}");
            if ($this->encrypt_status == false) {
                break;
            }
        }
    }

    function exec_crm_customer($record_info) {

        $select = "*";
        $id = $record_info['sys_id'];

    $record_data['num'] = $record_info['num'] ;
        $record_data['is_over'] = $record_info['is_over'] ;
        while ($id < $record_info['max_id']) {
            $sql = "select {$select} from crm_customer   where  ( source ='taobao' OR source ='fenxiao' )    ";
            $sql.="  AND customer_id >{$record_info['sys_id']}  AND customer_id <={$record_info['max_id']}   limit 1000  ";
            $data = $this->db->get_all($sql);
            $loop_num = 0;
            foreach ($data as $val) {
                $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($val['shop_code']);
                //未开启加密的跳过
                if($ret_encrypt['status']<1){
                    continue;
                }   
                
                if (empty($val['customer_name_encrypt'])) {
                    $encrypt_val = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($val['customer_name'], 'buyer_nick', $val['shop_code']);
                    if (empty($encrypt_val) || $encrypt_val == $val['customer_name']) {
                        $this->encrypt_status = false;
                    } else {
                        $customer_info = array('customer_name_encrypt' => $encrypt_val);
                        $customer_info['customer_name'] = load_model('crm/CustomerOptModel')->get_hide_name($val['customer_name']);
                        $this->update_exp('crm_customer', array('customer_name_encrypt' => $encrypt_val), " customer_code = '{$val['customer_code']}'");
                    }
                }

                $sql = "select a.* from crm_customer_address a LEFT JOIN
                    crm_customer_address_encrypt e ON a.customer_address_id=e.customer_address_id
                    where a.customer_code='{$val['customer_code']}' AND e.customer_address_id is null";
                $addr_data = $this->db->get_all($sql);
                if (!empty($addr_data) && $this->encrypt_status == true) {
                    $this->encryp_customer($addr_data);
                }

                if ($this->encrypt_status == false) {
                    break;
                }
                $loop_num++;
                $id = max($id, $val['customer_id']);
            }
           $record_info['sys_id'] = count($data) < 1000 ? $record_info['max_id'] : $id;
         
            $record_data['sys_id'] = $record_info['sys_id'];
            $record_data['num'] = $record_data['num'] + $loop_num;
            $record_data['is_over'] = $this->encrypt_status == false ? $record_data['is_over'] + 1 : $record_data['is_over'];
            $this->update_exp('sys_encrypt_record', $record_data, " id= {$record_info['id']}");
            if ($this->encrypt_status == false) {
                break;
            }
        }
    }

    function create_customer($record_data, $record_type) {
        $conf = $this->record_config[$record_type];
        $c_address_id_key = $record_type == 'sell_return_change' ? 'change_customer_address_id' : 'customer_address_id';

        $addr_key = array_search('address', $conf);
        //已经加密不用处理
        if (!empty($record_data[$c_address_id_key]) && $record_data[$addr_key] == '*****') {
            return false;
        }

        if (!isset($record_data['sale_channel_code']) || empty($record_data['sale_channel_code'])) {
            $sql = "select sale_channel_code from base_shop  where shop_code=:shop_code ";
            $record_data['sale_channel_code'] = $this->db->get_value($sql, array(':shop_code' => $record_data['shop_code']));
        }



        $customer_address_array = array();
        $address_key = '';
        foreach ($conf as $key => $c_key) {
            if (empty($c_key)) {
                $address_key = $key;
                continue;
            }
            $customer_address_array[$c_key] = $record_data[$key];
        }
        $customer_address_array['shop_code'] = $this->get_shop($record_data['shop_code'], $record_data['sale_channel_code'], $customer_address_array['customer_name']);



        $customer_address_array['source'] = $record_data['sale_channel_code'];
        $customer_address_array['is_add_time'] = date('Y-m-d H:i:s');

        $customer_address_array['customer_name'] = $customer_address_array['buyer_name'];
	
        $ret_create = load_model('crm/CustomerOptModel')->handle_customer($customer_address_array);

        if ($ret_create['status'] < 1) {
            $this->encrypt_status = false;
            return array();
        }
        $customer_address_id = $ret_create['data']['customer_address_id'];
        $customer_code = $ret_create['data']['customer_code'];
        $customer_address = load_model('crm/CustomerOptModel')->get_customer_address($customer_address_id);

        $no_key_arr = array('buyer_name');
        $data = array(
            'customer_code' => $customer_code,
        );
        foreach ($conf as $key => $c_key) {
            if (empty($c_key) || in_array($c_key, $no_key_arr)) {
                continue;
            }
            $data[$key] = $customer_address[$c_key];
        }


        $country = oms_tb_val('base_area', 'name', array('id' => $customer_address['country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $customer_address['province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $customer_address['city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $customer_address['district']));
        $street = oms_tb_val('base_area', 'name', array('id' => $customer_address['street']));

        $data[$address_key] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $customer_address['address'];
        if ($record_type == 'sell_return_change') {
            $data['change_customer_address_id'] = $customer_address_id;
        } else {
            $data['customer_address_id'] = $customer_address_id;
        }
        $data['buyer_name'] = load_model('crm/CustomerOptModel')->get_hide_name($customer_address_array['customer_name']);
        return $data;
    }

    function encryp_api_data($data, $type) {

        $new_data = array();
        $conf_all = array(
            'api_order' => array(
                'receiver_mobile', 'receiver_phone', 'receiver_email', 'buyer_nick', 'receiver_name'
            ),
            'api_taobao_fx_trade' => array(
                'receiver_phone', 'receiver_mobile_phone', 'receiver_name'
            ),
        );
        $is_encryp_value = load_model('sys/security/CustomersSecurityModel')->is_encrypt_value($data['receiver_name'], 'taobao', 'receiver_name');
        if ($is_encryp_value === true) {
            return false;
        }

        $config = $conf_all[$type];
        $shop_code = $data['shop_code'];
        foreach ($config as $key) {
            $encrypt_text = $data[$key];
            if (!empty($encrypt_text)) {
                $encrypt_val = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($encrypt_text, $key, $shop_code);
                if ($encrypt_val != $encrypt_text) {
                    $new_data[$key] = $encrypt_val;
                } else {
                    $this->encrypt_status = false;
                    return false; //加密失败
                }
            }
        }
        return $new_data;
    }

    function encryp_customer($data) {

        foreach ($data as $customer_address_data) {
            $check_row = load_model('crm/CustomerOptModel')->check_is_create_address($customer_address_data);
            if (empty($check_row)) {
                $ret = load_model('crm/CustomerOptModel')->create_address_encrypt($customer_address_data);
                if ($ret['status'] < 1) {
                    $this->encrypt_status = false;
                    return $ret;
                }
            }
        }
    }

    function get_shop($shop_code, $source, $customer_name) {
        //开启过加密
        $sql_check = "select shop_code from sys_encrypt where shop_code=:shop_code";
        $check = $this->db->get_row($sql_check, array(':shop_code' => $shop_code));
        if (!empty($check)) {
            return $shop_code;
        }

        $sql = "select sale_channel_code,authorize_state from base_shop where shop_code=:shop_code  ";
        $row = $this->db->get_row($sql, array(':shop_code' => $shop_code));

        if (empty($row) || $row['authorize_state'] != 1) { //如果店铺未授权，换成已经授权的店铺
            //已有档案切换店铺
            $customer_name_code = md5($customer_name);
            $sql = "select customer_code,customer_name_encrypt,shop_code from crm_customer where customer_name_code=:customer_name_code AND source=:source ";
            $customer_row = $this->db->get_row($sql, array(':customer_name_code' => $customer_name_code, ':source' => $source));
            if (!empty($customer_row) && empty($customer_row['customer_name_encrypt'])) {
                $sql = "select shop_code from sys_encrypt where type='taobao' AND status=1";
                $e_shop_code = $this->db->get_value($sql);
                $where = " customer_code ='{$customer_row['customer_code']}' ";
                $this->update_exp("crm_customer", array('shop_code' => $e_shop_code), $where);
                return $e_shop_code;
            }
        }
        return $shop_code;
    }

}
