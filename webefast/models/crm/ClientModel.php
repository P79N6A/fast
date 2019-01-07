<?php

/**
 * 顾客相关业务
 */
require_model('tb/TbModel');
require_lang('crm');
require_lib('util/oms_util', true);

class ClientModel extends TbModel {

    function __construct() {
        parent :: __construct('crm_client');
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_main = " FROM {$this->table} rl WHERE 1 ";
        $sql_values = array();
        //顾客代码
        if (isset($filter['client_code']) && $filter['client_code'] != '') {
            $sql_main .= " AND rl.client_code LIKE :client_code";
            $sql_values[':client_code'] = '%' . $filter['client_code'] . '%';
        }
        //顾客名称
        if (isset($filter['client_name']) && $filter['client_name'] != '') {
            $sql_main .= " AND rl.client_name LIKE :client_name";
            $sql_values[':client_name'] = '%' . $filter['client_name'] . '%';
        }
        //手机号码
        if (isset($filter['client_tel']) && $filter['client_tel'] != '') {
            $sql_main .= " AND rl.client_tel LIKE :client_tel";
            $sql_values[':client_tel'] = '%' . $filter['client_tel'] . '%';
        }

        $select = 'rl.client_id,rl.client_code, rl.client_name, rl.client_sex, rl.birthday, rl.client_tel, rl.email, rl.client_integral';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        foreach ($data['data'] as &$v) {
            $v['client_sex'] = ds_get_field_name('usersex', $v['client_sex']);
            if ($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
                $v['client_tel'] = $this->phone_hidden($v['client_tel']);
                $v['client_name'] = $this->name_hidden($v['client_name']);
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_detail_by_id($client_id) {
        $sql = "SELECT * FROM {$this->table} WHERE client_id=:client_id";
        $sql_values = array(":client_id" => $client_id);
        $ret = $this->db->get_row($sql, $sql_values);
        return $ret;
    }

    /**
     * 修改纪录
     */
    function update($client) {
        $ret = $this->valid($client);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $ret = $this->get_row(array('client_code' => $client['client_code']));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '顾客代码不存在');
        }

        if (isset($client['client_tel']) && isset($ret['data']['client_tel']) && ($client['client_tel'] != $ret['data']['client_tel'])) {
            $ret = $this->is_exists($client['client_tel'], 'client_tel');
            if ($ret['status'] > 0 && !empty($ret['data'])) {
                return $this->format_ret(-1, '', 'CLIENT_ERROR_UNIQUE_TEL');
            }
        }
        $ret = parent :: update($client, array('client_code' => $client['client_code']));
        return $ret;
    }

    /**
     * 添加顾客
     */
    function add_client($param) {
        $ret = $this->get_row(array('client_tel' => $param['client_tel']));
        if (!empty($ret['data'])) {
            return $this->format_ret(-1, '', '手机号已存在');
        }
        $this->begin_trans();
        $ret = $this->insert($param);
        $affect = $this->affected_rows();
        if ($ret['status'] != 1 || $affect != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '添加失败');
        }
        $customer = array(
            'customer_name' => $param['client_name'],
            'customer_tel' => $param['client_tel'],
            'customer_sex' => $param['client_sex'],
            'email' => $param['email'],
            'address' => $param['address'],
            'birthday' => $param['birthday'],
            'country' => 1,
            'province' => $param['province'],
            'city' => $param['city'],
            'district' => $param['district'],
            'remark' => $param['remark']
        );
        $ret = load_model('crm/CustomerModel')->add_customer($customer);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 服务器端验证
     */
    private function valid($data) {
        if (!isset($data['client_code']) || !isset($data['client_name']) || !valid_input($data['client_name'], 'required')) {
            return $this->format_ret(-1, '', 'CLIENT_ERROR_NAME');
        } else {
            return $this->format_ret(1);
        }
    }

    function is_exists($value, $field_name) {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 通过field_name查询
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

    /**
     * 顾客代码生成
     */
    function serial_code() {
        $id = $this->get_last_id();
        $id = empty($id) ? 1 : intval($id) + 1;
        $len = strlen($id);
        $pinyin = 'GK';

        switch ($len) {
            case 1:
                $code = $pinyin . '000' . $id;
                break;
            case 2:
                $code = $pinyin . '00' . $id;
                break;
            case 3:
                $code = $pinyin . '0' . $id;
                break;
            default:
                $code = $pinyin . $id;
        }

        return $code;
    }

    /**
     * 取出末条记录的id
     */
    function get_last_id() {
        $sql = "SELECT client_id FROM {$this->table} ORDER BY client_id DESC";
        $rs = $this->db->get_value($sql);
        return $rs;
    }

}
