<?php

/**
 * 会员相关业务
 *
 * @author huanghy
 *
 */
require_model('tb/TbModel');
require_lang('crm');
require_lib('util/oms_util', true);


class CustomerModel extends TbModel {
    function get_table() {
        return 'crm_customer';
    }

    function get_by_page_address($filter) {
        $sql_join = "";
        $sql_main = "FROM crm_customer_address r1 inner join   crm_customer  r2 on r2.customer_code = r1.customer_code   WHERE 1";
        $sql_values = array();
        //顾客代码
        if (isset($filter['buyer_name']) && $filter['buyer_name'] != '') {
            $sql_main .= " AND r2.customer_name = :customer_name";
            $sql_values[':customer_name'] = $filter['buyer_name'];
        }
        //if (isset($filter['customer_code']) && $filter['customer_code'] != '') {
                $sql_main .= " AND r2.customer_code= :customer_code";
                $sql_values[':customer_code'] = $filter['customer_code'];
        //}

        //按照指定地址排序
//        if (isset($filter['sort'])) {
//            $sort_filter = $filter['sort'];
//            $sql_value = array();
//            $select_ad = "ad.customer_address_id";
//            $sql = "from crm_customer_address ad,crm_customer crm where ad.customer_code = crm.customer_code ";
//            if (isset($sort_filter['address']) && $sort_filter['address'] != '') {
//                $sort_filter['address'] = addslashes($sort_filter['address']);
//                $sql .= " AND ad.address= :address";
//                $sql_value[':address'] = $sort_filter['address'];
//            }
//            if (isset($sort_filter['city']) && $sort_filter['city'] != '') {
//                $sql .= " AND ad.city= :address";
//                $sql_value[':city'] = $sort_filter['city'];
//            }
//            if (isset($sort_filter['district']) && $sort_filter['district'] != '') {
//                $sql .= " AND ad.district= :district";
//                $sql_value[':district'] = $sort_filter['district'];
//            }
//            if (isset($sort_filter['street']) && $sort_filter['street'] != '') {
//                $sql .= " AND ad.street= :street";
//                $sql_value[':street'] = $sort_filter['street'];
//            }
//            if (isset($sort_filter['name']) && $sort_filter['name'] != '') {
//                $sql .= " AND ad.name= :name";
//                $sql_value[':name'] = $sort_filter['name'];
//            }
//            if (isset($sort_filter['tel']) && $sort_filter['tel'] != '') {
//                $sql .= " AND ad.tel= :tel";
//                $sql_value[':tel'] = $sort_filter['tel'];
//            }
//            if (isset($sort_filter['home_tel']) && $sort_filter['home_tel'] != '') {
//                $sql .= " AND ad.home_tel= :home_tel";
//                $sql_value[':home_tel'] = $sort_filter['home_tel'];
//            }
//            if (isset($filter['buyer_name']) && $filter['buyer_name'] != '') {
//                $sql .= " AND crm.customer_name= :buyer_name";
//                $sql_value[':buyer_name'] = $filter['buyer_name'];
//            }
//     
//
//            
//            $address_id_arr = $this->get_page_from_sql($filter, $sql, $sql_value, $select_ad);
//
//            if (!empty($address_id_arr['data'])) {
//                foreach ($address_id_arr['data'] as $val) {
//                    $address_id = implode(',', $val['customer_address_id']);
//                }
//
//                $sql_main .= " order by find_in_set(customer_address_id,'{$address_id}') desc ";
//            }
//        }
        $sort_filter = $filter['sort'];
        $select = 'r1.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $top_addr = array();
        foreach ($data['data'] as $k => $find_addr) {
            $country1 = oms_tb_val('base_area', 'name', array('id' => $find_addr['country']));
            $province1 = oms_tb_val('base_area', 'name', array('id' => $find_addr['province']));
            $city1 = oms_tb_val('base_area', 'name', array('id' => $find_addr['city']));
            $district1 = oms_tb_val('base_area', 'name', array('id' => $find_addr['district']));
            $street1 = oms_tb_val('base_area', 'name', array('id' => $find_addr['street']));
            $data['data'][$k]['address1'] = $country1 . $province1 . $city1 . $district1 . $find_addr['address'];
            $data['data'][$k]['address2'] = $country1 . $province1 . $city1 . $district1 . $street1 . $find_addr['address'];
            $data['data'][$k]['country_name'] = $country1;
            $data['data'][$k]['province_name'] = $province1;
            $data['data'][$k]['city_name'] = $city1;
            $data['data'][$k]['district_name'] = $district1;
            $data['data'][$k]['street_name'] = $street1;
            if($sort_filter['address']==$find_addr['address']&&$sort_filter['city']==$find_addr['city']&&$sort_filter['district']==$find_addr['district']&&$sort_filter['name']==$find_addr['name']&&$sort_filter['tel']==$find_addr['tel']&&$sort_filter['home_tel']==$find_addr['home_tel']){
                $top_addr[] =  $data['data'][$k];
                unset($data['data'][$k]);
            }
        }
        foreach($top_addr as $val){
             array_unshift($data['data'],$val);
        }
       //
        
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //print_r($data);
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 此方法针对bug490的sql性能优化
     */
    public function get_page_from_sql_opt($filter, $sql_main, $sql_value = array(), $select = '*') {
        $page = !isset($filter['page']) ? 1 : intval($filter['page']);
        $where = "where 1=1 ";
        //$sql_value = $this->get_where($parameter, $where);
        if (!is_array($sql_value) || empty($sql_value)) {
            $sql_value = array();
        }
        if ($page < 1) {
            $page = 1;
        }
        $page_size = !isset($filter['page_size']) ? 10 : intval($filter['page_size']);
        // 总数计算
        $where_str = array_pop(explode('WHERE 1', $sql_main));
        $ext_shop_code_sql = '';
        $ext_row_sql = '';
        foreach (explode('AND', trim($where_str)) as $condition) {
            if (strpos($condition, ':') === false && !empty($condition)) {
                $ext_shop_code_sql .= ' AND `bd`.' . array_pop(explode('.', $condition));
            } else {
                $ext_row_sql .= empty($condition) ? '' : ' AND ' . $condition;
            }
        }
        $shop_code_sql = 'SELECT `shop_code` '
            . 'FROM `base_shop` `bd`, `base_sale_channel` `bsc` '
            . 'WHERE `bd`.`sale_channel_code` = `bsc`.`sale_channel_code`'
            . $ext_shop_code_sql;
        $shop_code_str = 'AND `shop_code` IN (';
        $shop_code_str .= implode(',', array_map(function ($row) {
                return '"' . $row['shop_code'] . '"';
            }, $this->db->get_all($shop_code_sql))) . ') ';
        $row_sql = 'SELECT count(*) AS `total_sl` '
            . 'FROM `crm_customer` `rl` '
            . 'WHERE 1 ' . $shop_code_str . $ext_row_sql;

        $row = $this->db->get_all($row_sql, $sql_value);
        $row = $row[0];
        //$count = is_array($row) ? $row['total_sl'] : 0;
        $count = empty($row) ? 0 : $row['total_sl'];

        $filter['page_size'] = $page_size;
        $filter['page_count'] = ceil($count / $page_size);
        $filter['record_count'] = $count;

        $filter['page'] = $page;

        $start = ($page - 1) * $page_size;
        $sql = "select $select $sql_main";

        $rs = $this->db->get_limit($sql, $sql_value, $page_size, $start);

        return array(
            'filter' => $filter,
            'data' => $rs
        );
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl inner join base_shop bd inner join base_sale_channel bsc on rl.shop_code = bd.shop_code and bd.sale_channel_code = bsc.sale_channel_code WHERE 1";
        //店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        $sql_values = array();
        $sql_value = array();
        //收货人 收货地址 手机
        $sql_w = '';
        if (isset($filter['name']) && $filter['name'] != '') {
            $name_code = load_model('crm/CustomerOptModel')->get_str_only_code($filter['name']);
            $sql_w .= " AND (name LIKE :name OR name_code = :name_code   )";
            $sql_value[':name'] = '%'.$filter['name'].'%';
            $sql_value[':name_code'] =  $name_code;
            
        }
        if (isset($filter['address']) && $filter['address'] != '') {
            $sql_w .= " AND address LIKE :address ";
            $sql_value[':address'] = '%'.$filter['address'].'%';
        }
        if (isset($filter['tel']) && $filter['tel'] != '') {
            $tel_code = load_model('crm/CustomerOptModel')->get_str_only_code($filter['tel']);
            $sql_w .= " AND (tel LIKE :tel OR tel_code = :tel_code  )";
            $sql_value[':tel'] = '%'.$filter['tel'].'%';
            $sql_value[':tel_code'] =  $tel_code;
        }
        if (!empty($sql_w)) {
            $sql_se = 'SELECT customer_code FROM crm_customer_address WHERE 1' . $sql_w;
            $code = $this->db->get_all_col($sql_se,$sql_value);
            if (!empty($code)) {
                $code_str = $this->arr_to_in_sql_value($code, 'customer_code', $sql_values);
                $sql_main .= " AND rl.customer_code in ({$code_str})";
            } else {
                $sql_main .= ' AND 1=2 ';
            }
        }

        //名称
        if (isset($filter['customer_name']) && $filter['customer_name'] != '') {
            $customer_name_code = load_model('crm/CustomerOptModel')->get_str_only_code($filter['customer_name']);

            $sql_main .= " AND ( rl.customer_name LIKE :customer_name OR rl.customer_name_code=:customer_name_code ) ";
            $sql_values[':customer_name'] = "%" . $filter['customer_name'] . '%';
            $sql_values[':customer_name_code'] = $customer_name_code;
        }
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] != '') {
            $sale_channel_code = explode(",", $filter['sale_channel_code']);
            $sale_channel_code_list = "'" . join("','", $sale_channel_code) . "'";
            $sql_main .= " AND bd.sale_channel_code in($sale_channel_code_list)";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $shop_code = explode(",", $filter['shop_code']);
            $shop_code_list = $this->arr_to_in_sql_value($shop_code, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code in($shop_code_list)";
        }
        //国家
        if (isset($filter['country']) && $filter['country'] != '') {
            $sql_main .= " AND rl.country = :country";
            $sql_values[':country'] = $filter['country'];
        }

        //省份
        if (isset($filter['province']) && $filter['province'] != '') {
            $sql_main .= " AND rl.province = :province";
            $sql_values[':province'] = $filter['province'];
        }


        //城市
        if (isset($filter['city']) && $filter['city'] != '') {
            $sql_main .= " AND rl.city = :city";
            $sql_values[':city'] = $filter['city'];
        }

        //区/县
        if (isset($filter['district']) && $filter['district'] != '') {
            $sql_main .= " AND rl.district = :district";
            $sql_values[':district'] = $filter['district'];
        }


        //黑名单
        if (isset($filter['type']) && $filter['type'] != '') {
            $sql_main .= " AND rl.type = :type";
            $sql_values[':type'] = $filter['type'];
        }

        //消费数
        if (isset($filter['consume_num_start']) && $filter['consume_num_start'] != '') {
            $sql_main .= " AND (rl.consume_num >= :consume_num_start )";
            $sql_values[':consume_num_start'] = $filter['consume_num_start'];
        }
        if (isset($filter['consume_num_end']) && $filter['consume_num_end'] != '') {
            $sql_main .= " AND (rl.consume_num <= :consume_num_end )";
            $sql_values[':consume_num_end'] = $filter['consume_num_end'];
        }

        //消费额
        if (isset($filter['consume_money_start']) && $filter['consume_money_start'] != '') {
            $sql_main .= " AND (rl.consume_money >= :consume_money_start )";
            $sql_values[':consume_money_start'] = $filter['consume_money_start'];
        }
        if (isset($filter['consume_money_end']) && $filter['consume_money_end'] != '') {
            $sql_main .= " AND (rl.consume_money <= :consume_money_end )";
            $sql_values[':consume_money_end'] = $filter['consume_money_end'];
        }

        if (!empty($filter['is_add_time_start'])) {
            $sql_main .= " AND rl.is_add_time >= :is_add_time_start ";
            $sql_values[':is_add_time_start'] = $filter['is_add_time_start'];
        }
        if (!empty($filter['is_add_time_end'])) {
            $sql_main .= " AND rl.is_add_time <= :is_add_time_end ";
            $sql_values[':is_add_time_end'] = $filter['is_add_time_end'];
        }

        $select = 'bsc.sale_channel_name,rl.customer_id,rl.shop_code,rl.customer_code,rl.customer_name,rl.type,rl.is_add_time,rl.consume_num,rl.consume_money,bd.shop_name as shop_name';
//        $data = $this->get_page_from_sql_opt($filter, $sql_main, $sql_values, $select);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select); // 原先语句
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));

        if (!empty($data['data'])) {
            $customer_code_arr = array();
            foreach ($data['data'] as $sub_arr) {
                $customer_code_arr[] = $sub_arr['customer_code'];
            }
            $customer_code_list = "'" . join("','", $customer_code_arr) . "'";
            $sql = "select customer_code,name,province,city,district,address,tel from crm_customer_address where is_default = 1 and customer_code in($customer_code_list)";
            $addr_arr = CTX()->db->getAll($sql);
            foreach ($addr_arr as $sub_addr) {
                $addr_arr[$sub_addr['customer_code']] = $sub_addr;
            }
            $en_data = array();
            $CustomerM = load_model('sys/security/CustomersSecurityModel');
            $CustomerOptM = load_model('crm/CustomerOptModel');
            foreach ($data['data'] as $k => &$sub_data) {
                if (isset($addr_arr[$sub_data['customer_code']])) {
                    $find_addr = $addr_arr[$sub_data['customer_code']];
                    $data['data'][$k]['customer_code'] = $find_addr['customer_code'];
                    $data['data'][$k]['name'] = $find_addr['name'];
                    $country = oms_tb_val('base_area', 'name', array('id' => $filter['country']));
                    $province = oms_tb_val('base_area', 'name', array('id' => $find_addr['province']));
                    $city = oms_tb_val('base_area', 'name', array('id' => $find_addr['city']));
                    $district = oms_tb_val('base_area', 'name', array('id' => $find_addr['district']));
                    $data['data'][$k]['address'] = $country . " " . $province . " " . $city . " " . $district . " " . $find_addr['address'];
                    $data['data'][$k]['tel'] = $find_addr['tel'];
                }
                if ($sub_data['type'] == 1) {
                    $data['data'][$k]['type_html'] = '<img src=' . get_theme_url("images/no.gif") . '>';
                } else {
                    $data['data'][$k]['type_html'] = '<img src=' . get_theme_url("images/ok.png") . '>';
                }
                $shop = load_model('base/ShopModel')->get_by_code($sub_data['shop_code']);
                $data['data'][$k]['shop_name'] = $shop['data']['shop_name'];
                //解密信息               
                $customer_address_id = oms_tb_val('crm_customer_address', 'customer_address_id', array('customer_code'=>$sub_data['customer_code']));
                $en_data[$k]['name'] = $CustomerM ->get_customer_decrypt($customer_address_id, 'name');
//                $en_data[$k]['customer_name'] = $CustomerM ->get_customer_decrypt($customer_address_id, 'buyer_name');
                $en_data[$k]['customer_name'] = $CustomerOptM -> get_buyer_name_by_code($sub_data['customer_code']);
                $address = $CustomerM ->get_customer_decrypt($customer_address_id, 'address');
                $en_data[$k]['address'] =  $country . " " . $province . " " . $city . " " . $district . " " .$address;
                $en_data[$k]['tel'] = $CustomerM ->get_customer_decrypt($customer_address_id, 'tel');
                $en_data[$k]['consume_num'] = $data['data'][$k]['consume_num'];
                $en_data[$k]['consume_money'] = $data['data'][$k]['consume_money'];
                $en_data[$k]['tconsume_moneyype'] = $data['data'][$k]['type'];
                $en_data[$k]['shop_name'] = $data['data'][$k]['shop_name'];
                $en_data[$k]['is_add_time'] = $data['data'][$k]['is_add_time'];
                $en_data[$k]['type'] = $data['data'][$k]['type'];
                
                if ($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
                    $sub_data['customer_name'] = $this->name_hidden($sub_data['customer_name']);
                    $sub_data['name'] = $this->name_hidden($sub_data['name']);
                    $sub_data['tel'] = $this->phone_hidden($sub_data['tel']);
                    $sub_data['address'] = $this->address_hidden($sub_data['address']);
                }
            }
        }
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] == 'customer_do_list' &&!empty($filter['__t_user_code'])) {
            $is_security_role = load_model('sys/UserModel')->is_security_role($filter['__t_user_code']);
            if ($is_security_role === true) {
                $data['data'] = $en_data;
                $log = array('user_id' =>0, 'user_code' => $filter['__t_user_code'], 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => '运营', 'yw_code' => '', 'operate_type' => '导出', 'operate_xq' => '会员列表导出解密数据');
                load_model('sys/OperateLogModel')->insert($log);
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_page_list($filter) {
        $sql_join = "LEFT JOIN base_shop as bs ON rl.shop_code=bs.shop_code 
					inner join crm_customer_address r2 on r2.customer_code = rl.customer_code
				";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1 AND r2.is_default=1  ";
        $sql_values = array();
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        //名称或代码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
//            $sql_main .= " AND (rl.customer_code LIKE :code_name or rl.customer_name LIKE :code_name)";
//            $sql_values[':code_name'] = '%' . $filter['code_name'] . '%';
//            
         $customer_name_code = load_model('crm/CustomerOptModel')->get_str_only_code($filter['code_name']);

            $sql_main .= " AND ( rl.customer_name LIKE :customer_name OR rl.customer_code LIKE :customer_name OR rl.customer_name_code=:customer_name_code ) ";
            $sql_values[':customer_name'] = "%" . $filter['code_name'] . '%';
            $sql_values[':customer_name_code'] = $customer_name_code;

        }
        if (isset($filter['customer_tel']) && $filter['customer_tel'] != '') {
//            $sql_main .= " AND (rl.tel LIKE :tel)";
//            $sql_values[':tel'] = '%' . $filter['customer_tel'] . '%';
//            
            $tel_code = load_model('crm/CustomerOptModel')->get_str_only_code($filter['customer_tel']);
            $sql_main .= " AND (r2.tel LIKE :customer_tel  OR  r2.tel_code= :tel_code   )";
            $sql_values[':customer_tel'] = '%'.$filter['customer_tel'].'%';
            $sql_values[':tel_code'] =  $tel_code;   
            
        }
        if (isset($filter['name_list']) && $filter['name_list'] != '') {
//            $sql_main .= " AND (r2.name like :name)";
//            $sql_values[':name'] = '%' . $filter['name_list'] . "%";
            
            $name_code = load_model('crm/CustomerOptModel')->get_str_only_code($filter['name_list']);
            $sql_main .= " AND (r2.name LIKE :name OR r2.name_code = :name_code   )";
            $sql_values[':name'] = '%'.$filter['name_list'].'%';
            $sql_values[':name_code'] =  $name_code;
            
        }
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_main .= " AND (rl.shop_code = :shop_code)";
            $sql_values[':shop_code'] = $filter['shop_code'];
        }
        $select = 'r2.*,bs.shop_name,rl.customer_name,rl.type ';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
         $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        foreach ($data['data'] as $k => $sub_data) {
            $province = oms_tb_val('base_area', 'name', array('id' => $sub_data['province']));
            $city = oms_tb_val('base_area', 'name', array('id' => $sub_data['city']));
            $district = oms_tb_val('base_area', 'name', array('id' => $sub_data['district']));
            $data['data'][$k]['address'] = "中国" . $province . $city . $district . $sub_data['address'];
            $data['data'][$k]['black_name'] = $sub_data['type'] == 2 ? 1 : 0;
            if ($cfg['safety_control'] == 1 ) {
                     $data['data'][$k]['customer_name'] = $this->name_hidden($sub_data['customer_name']);
                     $data['data'][$k]['name'] = $this->name_hidden($sub_data['name']);
                     $data['data'][$k]['tel'] = $this->phone_hidden($sub_data['tel']);
                     $data['data'][$k]['address'] = $this->address_hidden($sub_data['address']);
             }   
        }
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * @param $id
     * @return array
     */
    function get_by_id($id) {

        return $this->get_row(array('customer_id' => $id));
    }

    /**
     * @param $code
     * @return array
     */
    function get_by_code($code) {
        return $this->get_row(array('customer_code' => $code));
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*") {
        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));
        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    /*
     * 添加新纪录
     */

    function insert($customer) {
        $status = $this->valid($customer);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($customer['customer_name']);

        if (!empty($ret['data'])) {
            return $this->format_ret(-2, $ret['data']);
        }
        return parent::insert($customer);
    }

    //转单时添加会员
    function add_customer($info) {
        $status = $this->valid($info);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $sql = "select customer_code from crm_customer where customer_name = :customer_name";
        $row = CTX()->db->getRow($sql, array('customer_name' => $info['customer_name']));
        if (!empty($row)) {
            return $this->format_ret(1, $row);
        }

//        $sql = "select max(customer_id) from crm_customer";
//        $max_customer_id = CTX()->db->getOne($sql);
//        $info['customer_code'] = (int) $max_customer_id + 1;
        $info['customer_code'] = NULL;
        $ret = $this->insert($info);
        if ($ret['status'] < 1) {
            return $ret;
        }


        $info['customer_id'] = $ret['data'];
        $info['customer_code'] = $info['customer_id'];
        $sql_up = "update  crm_customer set customer_code='{$info['customer_id']}' where customer_id='{$info['customer_id']}' ";
        $this->db->query($sql_up);

        return $this->format_ret(2, $info);
    }

    /*
     * 修改纪录
     */

    function update($customer, $customer_id) {
//		echo $customer_id;
//		print_r($customer);exit;
        $status = $this->valid($customer, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->get_row(array('customer_id' => $customer_id));
        if (isset($customer['customer_code']) && $customer['customer_code'] != $ret['data']['customer_code']) {
            $ret1 = $this->is_exists($customer['customer_code'], 'customer_code');
            if (!empty($ret1['data'])) {
                return $this->format_ret(CUSTOMER_ERROR_UNIQUE_CODE);
            }
        }
        $ret = parent::update($customer, array('customer_id' => $customer_id));
        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!isset($data['customer_name']) || !valid_input($data['customer_name'], 'required'))
            return CUSTOMER_ERROR_NAME;
        return 1;
    }

    function is_exists($value, $field_name = 'customer_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    function get_addr_list($customer_code) {
        $sql = "select customer_address_id,customer_code,address,tel,name,zipcode,is_default,home_tel,province,city,district,street from crm_customer_address where customer_code = '{$customer_code}' ";
        $data = CTX()->db->getAll($sql);
        return $data;
    }

    function get_default_addr($customer_code) {
        $sql = "select * from crm_customer_address where is_default = 1 and customer_code = :customer_code";
        $data = CTX()->db->getRow($sql, array('customer_code' => $customer_code));
        $msg = '新增订单选择会员';
        $customer_address = load_model('crm/CustomerOptModel')->get_customer_address_encrypt($data['customer_address_id'],$msg);
        $data = array_merge($data,$customer_address);
        
        $data['customer_name'] = load_model('crm/CustomerOptModel')->get_buyer_name_by_code($data['customer_code']); 
        
        if ($data) {
            return $this->format_ret(1, $data);
        } else {
            return $this->format_ret(-1);
        }
    }

    function get_addr($customer_address_id) {
        $is_use = $this->check_is_use_address($customer_address_id);
        if($is_use){
            return $this->format_ret(-1,'','地址信息被使用，不可以编辑，请添加新的地址信息！');
        }
        
        $sql = "select * from crm_customer_address where customer_address_id = :customer_address_id";
        $data = CTX()->db->getRow($sql, array('customer_address_id' => $customer_address_id));
        return $this->format_ret(1,$data);
    }
    function check_is_use_address($customer_address_id){
        
        $sql = "select 1 from oms_sell_record where customer_address_id=:customer_address_id";
        $num = $this->db->get_value($sql,array(':customer_address_id'=>$customer_address_id));
        if($num==1){
               return TRUE;
        } 
        $sql = "select 1 from oms_sell_return where change_customer_address_id=:customer_address_id OR customer_address_id=:customer_address_id ";
        $num = $this->db->get_value($sql,array(':customer_address_id'=>$customer_address_id));
        if($num==1){
                return TRUE;
        } 
        
        $sql = "select 1 from oms_return_package where customer_address_id=:customer_address_id";
        $num = $this->db->get_value($sql,array(':customer_address_id'=>$customer_address_id));
        if($num==1){
                return TRUE;
        } 
        return FALSE;
    }
    
    
    function set_default($customer_address_id) {
        $sql = "select customer_code from crm_customer_address where customer_address_id = :customer_address_id";
        $customer_code = CTX()->db->getOne($sql, array('customer_address_id' => $customer_address_id));
        $sql = "update crm_customer_address set is_default = 0 where customer_code = :customer_code";
        CTX()->db->query($sql, array('customer_code' => $customer_code));
        $sql = "update crm_customer_address set is_default = 1 where customer_address_id = :customer_address_id";
        CTX()->db->query($sql, array('customer_address_id' => $customer_address_id));
        return true;
    }

    function clear_default($customer_code) {
        $sql = "update crm_customer_address set is_default = 0 where customer_code = :customer_code";
        CTX()->db->query($sql, array('customer_code' => $customer_code));
        return true;
    }

    //添加地址信息
    function insert_customer_address($info, $is_new = 0) {
        if ($is_new == 0) {
            $sql = "select  name,country,province,city,district,street,address,tel from crm_customer_address where customer_code = :customer_code";
            $old_info = CTX()->db->get_all($sql, array('customer_code' => $info['customer_code']));
            if (!empty($old_info)) {
                foreach ($old_info as $val) {
                    if (
                        $val['name'] == $info['name'] && $val['country'] == $info['country'] &&
                        $val['province'] == $info['province'] && $val['city'] == $info['city'] &&
                        $val['district'] == $info['district'] && $val['street'] == $info['street'] &&
                        $val['address'] == $info['address'] && $val['tel'] == $info['tel']
                    ) {
                        return true;
                    }
                }
            }
            $sql_add = "update crm_customer_address set is_default = 0 where customer_code = :customer_code  ";
            CTX()->db->query($sql_add, array('customer_code' => $info['customer_code']));
        }
        $info['is_add_time'] = date('Y-m-d H:i:s');
//		$info['is_default'] = empty($old_info) ? 1 : 0;
        $result = M('crm_customer_address')->insert($info);
        return $result;
    }

    //修改地址信息
    function update_customer_address($info, $wh) {
        //echo '<hr/>info<xmp>'.var_export($info,true).'</xmp>';
        //echo '<hr/>wh<xmp>'.var_export($wh,true).'</xmp>';
        $ret = M('crm_customer_address')->update($info, $wh);
        return $ret;
    }

    //删除地址信息
    function delete_customer_address($customer_address_id) {
        $sql = "select customer_code from crm_customer_address where customer_address_id = :customer_address_id";
        $customer_code = CTX()->db->getOne($sql, array('customer_address_id' => $customer_address_id));
        
       $is_use =  $this->check_is_use_address($customer_address_id);
       if($is_use===TRUE){
           return $this->format_ret(-1,'','地址信息被使用，暂时不能编辑!');
       }
        
        $sql = "select count(*) from crm_customer_address where customer_code = :customer_code";
        $c = CTX()->db->getOne($sql, array('customer_code' => $customer_code));
        if ($c <= 1) {
            $ret = array('status' => -1, null, 'message' => '地址信息必须保留一条');
        } else {
            $sql = "delete from crm_customer_address where customer_address_id = :customer_address_id";
            CTX()->db->query($sql, array('customer_address_id' => $customer_address_id));
            $ret = array('status' => 1);
        }
        return $ret;
    }

    /**
     * 新增订单是处理会员信息
     * @param type $customer
     */
    function handle_customer($customer) {

        $ret = $this->add_customer($customer);

        if ($ret['status'] < 1) {
            return $ret;
        }


        $customer_code = $ret['data']['customer_code'];
        if ($ret['status'] > 0) {
            $is_new = $ret['status'] == 2 ? 1 : 0;
            $customer['customer_code'] = $customer_code;
            $customer['is_default'] = 1;


            $ret = $this->insert_customer_address($customer, $is_new);

            if (!$ret) {
                return $this->format_ret(-1, '', 'CUSTOMER_ADDR_INSERT_ERROR');
            }
        } else {
            return $this->format_ret(-1, '', 'CUSTOMER_INSERT_ERROR');
        }

        return $this->format_ret(1, $customer_code);
    }

    /**
     * 删除纪录
     */
    function delete($id) {
        $ret_row =  $this->get_by_id($id);
        if($ret_row['data']['customer_code']){
            $sql = "select 1 from oms_sell_record where customer_code=:customer_code";
            $num = $this->db->get_value($sql,array(':customer_code'=>$ret_row['data']['customer_code']));
            if($num==1){
                return $this->format_ret(-1,'','会员信息已经被使用，不能删除');
            }
        }
        
        $ret = parent:: delete(array('customer_id' => $id));
        
        return $ret;
    }

    function update_active($active, $id) {
        if (!in_array($active, array(1, 2))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('type' => $active), array('customer_id' => $id));
        return $ret;
    }

    function get_address_by_buyer_name($buger_name) {
        $sql = "select r1.address,r1.customer_address_id,r1.country,r1.province,r1.city,r1.district,r1.zipcode,r1.tel,r1.home_tel,r1.name,r1.street
    			FROM crm_customer_address r1 inner join crm_customer r2 on r2.customer_code = r1.customer_code WHERE r2.customer_name = :customer_name";
        $data = $this->db->get_all($sql, array('customer_name' => $buger_name));
        foreach ($data as $k => $find_addr) {
            $country1 = oms_tb_val('base_area', 'name', array('id' => $find_addr['country']));
            $province1 = oms_tb_val('base_area', 'name', array('id' => $find_addr['province']));
            $city1 = oms_tb_val('base_area', 'name', array('id' => $find_addr['city']));
            $district1 = oms_tb_val('base_area', 'name', array('id' => $find_addr['district']));
            $street1 = oms_tb_val('base_area', 'name', array('id' => $find_addr['street']));
            $data[$k]['address1'] = $country1 . ' ' . $province1 . ' ' . $city1 . ' ' . $district1 . ' ' . $street1 . ' ' . $find_addr['address'];
            $data[$k]['address2'] = $country1 . $province1 . $city1 . $district1 . $street1 . $find_addr['address'];
        }
        return $data;
    }


    /**获取图表数据
     * @param $buger_name
     * @return array|bool
     */
    function get_picture_data($filter) {
        $ret = $this->get_sql_by_filter($filter);
        $sql_main = $ret['sql_main'];
        $sql_values = $ret['sql_value'];
        $select_join = " SELECT consume_money,DATE_FORMAT(rl.is_add_time,'%Y-%m') AS add_month " . $sql_main;
        $sql = "SELECT count(1) AS custom_num,t.add_month FROM (" . $select_join . ") AS t WHERE 1 ";
        //开始月份
        if (isset($filter['month_start']) && $filter['month_start'] != '') {
            $sql .= " AND t.add_month >= :month_start ";
            $sql_values[':month_start'] = $filter['month_start'];
        }
        //结束月份
        if (isset($filter['month_end']) && $filter['month_end'] != '') {
            $sql .= " AND t.add_month <= :month_end ";
            $sql_values[':month_end'] = $filter['month_end'];
        }
        $sql .= " GROUP BY t.add_month ORDER BY t.add_month";
        $data = $this->db->get_all($sql, $sql_values);
        $month_data = array();
        foreach ($data as $value) {
            $month_data[$value['add_month']] = $value['custom_num'];
        }
        $month_start = isset($filter['month_start']) ? $filter['month_start'] : date(Y) . "-01";
        $month_end = isset($filter['month_end']) ? $filter['month_end'] : date(Y) . "-12";
        //获取时间差的月份
        $monarr = $this->get_diff_month($month_start, $month_end);
        $ret_data = array();
        foreach ($monarr as $v) {
            if (array_key_exists($v, $month_data)) {
                $ret_data[$v] = $month_data[$v];
            } else {
                $ret_data[$v] = 0;
            }
        }
        $result = array();
        foreach ($ret_data as $key => $val) {
            $result['add_month'][] = $key;
            $result['num'][] = $val;
        }
        return $result;
    }


    /**报表组装sql
     * @param $filter
     * @return array|bool
     */
    function get_sql_by_filter($filter) {
        $sql_main = "FROM {$this->table} rl LEFT JOIN base_shop bd ON rl.shop_code=bd.shop_code WHERE 1";
        $sql_values = array();
        //店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] != '') {
            $sale_channel_code = explode(",", $filter['sale_channel_code']);
            $key = 'sale_channel_code';
            $sale_channel_code_list=$this->arr_to_in_sql_value($sale_channel_code, $key, $sql_values);
            $sql_main .= " AND bd.sale_channel_code in($sale_channel_code_list)";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $shop_code = explode(",", $filter['shop_code']);
            $key = 'shop_code';
            $shop_code_list=$this->arr_to_in_sql_value($shop_code, $key, $sql_values);
            $sql_main .= " AND rl.shop_code in($shop_code_list)";
        }
        $data = array(
            "sql_main" => $sql_main,
            "sql_value" => $sql_values,
        );
        return $data;
    }

    /**会员新增报表列表
     * @param $filter
     * @return array
     */
    function get_improve_by_filter($filter) {
        $ret = $this->get_sql_by_filter($filter);
        $sql_main = $ret['sql_main'];
        $sql_values = $ret['sql_value'];
        $select_join = " SELECT consume_money,DATE_FORMAT(rl.is_add_time,'%Y-%m') AS add_month " . $sql_main;
        $sql = " FROM (" . $select_join . ") AS t WHERE 1";
        //开始月份
        if (isset($filter['month_start']) && $filter['month_start'] != '') {
            $sql .= " AND t.add_month >= :month_start ";
            $sql_values[':month_start'] = $filter['month_start'];
        }
        //结束月份
        if (isset($filter['month_end']) && $filter['month_end'] != '') {
            //若检索月份大于当前月，只显示当前月份为止的数据
            $current_month=date('Y-m');
            $filter['month_end'] = ($filter['month_end'] > $current_month) ? $current_month : $filter['month_end'];
            $sql .= " AND t.add_month <= :month_end ";
            $sql_values[':month_end'] = $filter['month_end'];
        }
        $select = 'count(1) AS new_custom_num,t.add_month,sum(t.consume_money) AS new_consume_money';
        $sql .= " GROUP BY t.add_month ORDER BY t.add_month";
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);
        $ret_status = OP_SUCCESS;
        $exist_month = array();
        foreach ($data['data'] as $k => &$sub_data) {
            $old_data = $this->get_old_improve_data($filter, $sub_data['add_month']);
            $sub_data['old_custom_num'] = $old_data['old_custom_num'];
            $sub_data['old_consume_money'] = $old_data['old_consume_money'];
            $exist_month[] = $sub_data['add_month'];
        }
        $month_start = isset($filter['month_start']) ? $filter['month_start'] : date(Y) . "-01";
        $month_end = isset($filter['month_end']) ? $filter['month_end'] : date(Y) . "-12";
        //获取时间差的月份
        $monarr = $this->get_diff_month($month_start, $month_end);
        //组装表中不存在月份的数据
        $not_exist_month = array();
        foreach ($monarr as $v) {
            if (!in_array($v, $exist_month)) {
                $k = isset($k) ? $k : -1;
                $k_old = $k++;
                $not_exist_month[$k_old]['add_month'] = $v;
                $not_exist_month[$k_old]['new_custom_num'] = 0;
                $not_exist_month[$k_old]['new_consume_money'] = 0;
                $not_data = $this->get_old_improve_data($filter, $v);
                $not_exist_month[$k_old]['old_custom_num'] = $not_data['old_custom_num'];
                $not_exist_month[$k_old]['old_consume_money'] = $not_data['old_consume_money'];
            }
        }
        $data['data'] = array_merge($data['data'], $not_exist_month);
        //排序
        $sort_month = array();
        foreach ($data['data'] as $sort) {
            $sort_month[] = $sort['add_month'];
        }
        array_multisort($sort_month, SORT_ASC, $data['data']);
        //导出
        if ($filter['ctl_type'] == 'export') {
            return $this->format_ret($ret_status, $data);
        }
        //处理分页
        //总条数
        $record_count = count($monarr);
        $data['filter']['record_count'] = $record_count;
        //页码
        $page = (int)$filter['page'];
        //每页的显示条数
        $pageSize = (int)$filter['page_size'];
        //总共要显示几页
        $data['filter']['page_count'] = ceil($record_count / $pageSize);
        //分页显示
        $new_data = array_chunk($data['data'], $pageSize);
        $data['data'] = $new_data[$page - 1];
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 获取老会员数据
     */
    function get_old_improve_data($filter, $add_month) {
        $ret = $this->get_sql_by_filter($filter);
        $sql_main = $ret['sql_main'];
        $sql_values = $ret['sql_value'];
        $select_join = " SELECT consume_money,DATE_FORMAT(rl.is_add_time,'%Y-%m') AS add_month " . $sql_main;
        $sql = "SELECT count(1) AS old_custom_num,sum(consume_money) AS old_consume_money FROM (" . $select_join . ") AS t WHERE 1 AND t.add_month< :add_month";
        $sql_values[':add_month'] = $add_month;
        $old_data = $this->db->get_row($sql, $sql_values);
        $old_data['old_consume_money'] = empty($old_data['old_consume_money']) ? 0 : $old_data['old_consume_money'];
        return $old_data;
    }


    /**获取时间差的月份
     * @param $month_start
     * @param $month_end
     * @return array
     */
    function get_diff_month($month_start, $month_end) {
        $time_start = strtotime($month_start);
        $time_end = strtotime($month_end);
        $monarr = array();
        $monarr[] = $month_start; // 当前月;
        while (($time_start = strtotime('+1 month', $time_start)) <= $time_end) {
            $monarr[] = date('Y-m', $time_start); // 取得递增月;
        }
        return $monarr;
    }


    /**会员消费统计
     * @param $filter
     * @return array
     */
    function get_consume_data($filter) {
        $ret = $this->get_consume_sql_by_filter($filter);
        $sql_main = $ret['sql_main'];
        $sql_values = $ret['sql_value'];
        $sql_main .= " GROUP BY rl.id ORDER BY payable_money_all DESC";
        $sql = "SELECT rl.name,ifnull(sum(r2.payable_money),0) AS payable_money_all " . $sql_main;
        $data = $this->db->get_all($sql, $sql_values);
        $i = 0;
        $map_data = array();
        foreach ($data as $key => &$val) {
            //序号
            $val['order'] = ++$i;
            //金额
            $val['value'] = $val['payable_money_all'];
            $vowels_1 = array("省", "回族自治区", "壮族自治区", "维吾尔自治区", "特别行政区");
            $name = str_replace($vowels_1, "", $val['name']);
            $vowels_2 = array("自治区");
            $val['name']=str_replace($vowels_2, "",$name);
            $map_data[$key]['name'] = $val['name'];
            $map_data[$key]['value'] = $val['value'];
        }
        $ret_data = array(
            "list_data" => $data,
            "map_data" => $map_data,
        );
        return $ret_data;
    }

    /**消费金额报表组装sql
     * @param $filter
     * @return array
     */
    function get_consume_sql_by_filter($filter) {
        $sql_main = "FROM base_area rl INNER JOIN 
        (SELECT receiver_province,payable_money FROM oms_sell_record WHERE 1 AND shipping_status=4 AND order_status=1 ";
        $sql_values = array();
        //店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('shop_code', $filter_shop_code);
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] != '') {
            $sale_channel_code = explode(",", $filter['sale_channel_code']);
            $key = 'sale_channel_code';
            $sale_channel_code_list=$this->arr_to_in_sql_value($sale_channel_code, $key, $sql_values);
            $sql_main .= " AND sale_channel_code in($sale_channel_code_list)";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $shop_code = explode(",", $filter['shop_code']);
            $key = 'shop_code';
            $shop_code_list=$this->arr_to_in_sql_value($shop_code, $key, $sql_values);
            $sql_main .= " AND shop_code in($shop_code_list)";
        }
        //开始月份
        if (isset($filter['month_start']) && $filter['month_start'] != '') {
            $sql_main .= " AND DATE_FORMAT(delivery_date,'%Y-%m') >= :month_start ";
            $sql_values[':month_start'] = $filter['month_start'];
        }
        //结束月份
        if (isset($filter['month_end']) && $filter['month_end'] != '') {
            $sql_main .= " AND DATE_FORMAT(delivery_date,'%Y-%m') <= :month_end ";
            $sql_values[':month_end'] = $filter['month_end'];
        }
        $sql_main.=" ) as r2 ON rl.id=r2.receiver_province WHERE 1 AND rl.parent_id=1 ";
        $data = array(
            "sql_main" => $sql_main,
            "sql_value" => $sql_values,
        );
        return $data;
    }
    /*
     * * 方法名       api_get_customer                        
     *
     * 功能描述     通过平台获取店铺信息
     *
     * @author      F.ling
     * @date        2017.02.17
     * @param       $param
     *              array(
     *                  可选: 'buyer_name', 'receiver_name','recevier_mobile','page','page_size'
     *                  必选: 'start_lastchanged','end_lastchanged'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     *
     * */
    function api_get_customer($param){
        //可选字段
        $key_option = array(
            's' => array('buyer_name','receiver_name'),
            'i' => array('recevier_mobile','page','page_size')
        );
        $key_require = array(
            's' => array('start_lastchanged','end_lastchanged'),
        );
        $arr_option = array();
        $arr_required = array();
        //提取可选字段中已赋值数据
        $ret_arr_required = valid_assign_array($param, $key_require, $arr_required,TRUE);
        if($ret_arr_required['status'] == TRUE){
            $ret_arr_option = valid_assign_array($param, $key_option, $arr_option);
            $arr_deal = array_merge($arr_required, $arr_option);
        
            if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
                    return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
            }
            //清空无用数据
            unset($arr_option);
            unset($param);
            $select = 'r1.customer_code,r1.customer_name,r1.shop_code,r2.shop_name,r1.type,r1.customer_level as level,r1.birthday,r1.is_add_time';
            $sql_values = array();
            $sql_main = "FROM {$this->table} r1 inner join base_shop r2 on r1.shop_code=r2.shop_code inner join crm_customer_address r3 on r1.customer_code=r3.customer_code WHERE 1 and r1.lastchanged>='{$arr_deal['start_lastchanged']}' and r1.lastchanged<='{$arr_deal['end_lastchanged']}'";
            foreach ($arr_deal as $key => $val) {
                if ($key != 'page' && $key != 'page_size') {
                    if($key == 'buyer_name'){
                        $sql_values[":{$key}"] = $val;
                        $sql_main .= " AND r1.customer_name=:{$key}";
                    }
                    if($key == 'receiver_name'){
                        $sql_values[":{$key}"] = $val;
                        $sql_main .= " AND r3.name=:{$key}";
                    }
                    if($key == 'recevier_mobile'){
                        $sql_values[":{$key}"] = $val;
                        $sql_main .= " AND r3.tel=:{$key}";
                    }
                }
            }
            $sql_main .= ' group by customer_code ';
            $ret = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select,true);
            $del_key = array('customer_code');
            if(count($ret['data'])==0){
                return $this->format_ret(-1002, '', API_RETURN_MESSAGE_10002);
            }else{
                foreach($ret['data'] as $key=>&$v){
                    $sql_address = "select r3.name,r3.tel as mobile,r3.home_tel as tel,r3.country,r3.province,r3.city,r3.district,r3.street,r3.zipcode,r3.is_default,r3.is_add_time as add_address_time from crm_customer_address r3 where r3.customer_code=:customer_code";
                    $data_address = $this->db->get_all($sql_address,array(':customer_code'=>$v['customer_code']));
                    foreach($data_address as $k=>$val){
                        $country = load_model('base/TaobaoAreaModel')->get_by_field('id',$val['country'],'name');
                        $data_address[$k]['country'] = $country['data']['name'];
                        $province = load_model('base/TaobaoAreaModel')->get_by_field('id',$val['province'],'name');
                        $data_address[$k]['province'] = $province['data']['name'];
                        $city =  load_model('base/TaobaoAreaModel')->get_by_field('id',$val['city'],'name');
                        $data_address[$k]['city'] = $city['data']['name'];
                        $district = load_model('base/TaobaoAreaModel')->get_by_field('id',$val['district'],'name');
                        $data_address[$k]['district'] = $district['data']['name'];
                        $street = load_model('base/TaobaoAreaModel')->get_by_field('id',$val['street'],'name');
                        $data_address[$k]['street'] = $street['data']['name'];
                    }
                        $v['address']=$data_address;
                        foreach ($del_key as $value) {
                            if (array_key_exists($value, $v)) {
                                unset($v[$value]);
                            }
                        }
                }
            }
            $ret_status = OP_SUCCESS;
            return $this -> format_ret($ret_status, $ret);
        }else{
            return $this->format_ret(-10001, $ret_arr_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
        
    }
    
}
