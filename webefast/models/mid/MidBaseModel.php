<?php

require_model('tb/TbModel');

class MidBaseModel extends TbModel {

    protected $mid_type;
    protected $mid_config;
    protected $flow_type = array(
        'sell_record' => array(
            'scan',
            'shipping',
            'send'),
        'sell_return' => array('return_shipping', 'receiving'),
        'wbm_store_out' => array(
            'send'),
        'wbm_return' => array('receiving',),
    );

    /**
     * 设置中间表对接记录
     *
     * @staticvar type $check_arr
     * @param type $flow_type  scan,shipping，send
     * @param type $record_code
     * @param type $record_type sell_record sell_return
     * @param type $store_code
     * @param type $shop_code
     * @return boolean
     */
    function set_mid_record($flow_type, $record_code, $record_type, $store_code, $shop_code = '') {

        static $check_arr = null;
        $key = $flow_type . '|' . $record_type . '|' . $store_code . '|' . $shop_code;
        if (!isset($check_arr[$key])) {
            $check_arr[$key] = $this->check_is_mid($flow_type, $record_type, $store_code, $shop_code);
        }
        if ($check_arr[$key] === false) {
            return $this->format_ret(1, '', '无对接配置！');
        }
        $api_product = $check_arr[$key];

        if (!isset($this->mid_config[$api_product][$store_code])) {
            $ret = load_model('mid/MidApiConfigModel')->get_mid_config_by_sys_code($store_code, $api_product);
            if ($ret['status'] < 1) {
                return $this->format_ret(1, '', '无对接配置！');
            }

            $this->mid_config[$api_product][$store_code] = $ret['data'];
        }

        return $this->add_mid_info($record_code, $record_type, $this->mid_config[$api_product][$store_code]);
    }

    /**
     * 设置中间表对接记录
     * 
     * @staticvar type $check_arr
     * @param type $flow_type  scan,shipping，send
     * @param type $record_code
     * @param type $record_type sell_record sell_return
     * @param type $store_code
     * @param type $shop_code
     * @return boolean
     */
    function cancel_mid_record($record_code, $record_type, $store_code, $shop_code = '') {

        static $check_arr = null;
        $key = $record_type . '|' . $store_code . '|' . $shop_code;
        if (!isset($check_arr[$key])) {
            $check_arr[$key] = $this->check_is_mid_all($record_type, $store_code, $shop_code);
        }

        if ($check_arr[$key] === false) {
            return $this->format_ret(1);
        }

        $api_product = &$check_arr[$key];

        if (!isset($this->mid_config[$api_product][$store_code])) {
            $ret = load_model('mid/MidApiConfigModel')->get_mid_config_by_sys_code($store_code, $api_product);
            $this->mid_config[$api_product][$store_code] = $ret['data'];
        }
        $sql = "select id from mid_order where record_code=:record_code AND  record_type=:record_type AND api_product=:api_product ";
        $sql_value = array(
            ':record_code' => $record_code,
            ':record_code' => $record_code,
            ':record_type' => $record_type,
            ':api_product' => $api_product,
        );
        $row = $this->db->get_row($sql, $sql_value);

        if (empty($row)) {
            return $this->format_ret(1);
        }
        return load_model('mid/MidOptModel')->opt_order($row['id'], 1);
    }

    function check_is_mid_all($record_type, $store_code, $shop_code = '') {
        static $flow_arr = NULL;


        $flow_type_arr = array(
            'sell_record' => array(
                'scan',
                'shipping',
            ),
            'sell_return' => array('return_shipping',),
        );

        foreach ($flow_type_arr[$record_type] as $flow_type) {
            if (!isset($flow_arr[$record_type])) {
                $flow_arr[$flow_type][$record_type] = $this->check_flow($flow_type, $record_type);
            }
            if (empty($flow_arr[$flow_type][$record_type])) {
                continue;
            }
            $flow_data = &$flow_arr[$flow_type][$record_type];
            $api_product = false;
            if ($flow_data['check_type'] == 1) {
                $api_product = load_model('mid/MidApiConfigModel')->check_is_mid_by_code($store_code, 1);
            } else if ($flow_data['check_type'] == 0) {
                $api_product = load_model('mid/MidApiConfigModel')->check_is_mid_by_code($shop_code, 0);
            }
            return $api_product;
        }
        return false;
    }

    /*
     * 检查是否需要传入中间表
     */

    function check_is_mid($flow_type, $record_type, $store_code, $shop_code = '') {
        static $flow_arr = NULL;


        if (!isset($flow_arr[$flow_type][$record_type])) {
            $flow_arr[$flow_type][$record_type] = $this->check_flow($flow_type, $record_type);
        }

        if (empty($flow_arr[$flow_type][$record_type])) {
            return false;
        }
        $flow_data = &$flow_arr[$flow_type][$record_type];

        $api_product = false;
        if ($flow_data['check_type'] == 1) {
            $api_product = load_model('mid/MidApiConfigModel')->check_is_mid_by_code($store_code, 1);
        } else if ($flow_data['check_type'] == 0) {
            $api_product = load_model('mid/MidApiConfigModel')->check_is_mid_by_code($shop_code, 0);
        }
        //比较对接是否同一个类型
        if ($api_product !== false && $flow_data['api_product'] !== $api_product) {
            $api_product = false;
        }


        return $api_product;
    }

    /*
     * 检查类此是否存在中间处理
     */

    function check_flow($flow_type, $record_type) {
        $sql = "select * from mid_process_flow where record_type=:record_type AND record_mid_type=:record_mid_type ";
        $sql_values = array(
            ':record_type' => $record_type,
            ':record_mid_type' => $flow_type,
        );
        $row = $this->db->get_row($sql, $sql_values);
        return $row;
    }

    /*
     * 添加到中间表
     *
     */

    private function add_mid_info($record_code, $record_type, $config) {

        if (isset($config['api_config']['connection_mode']) && $config['api_config']['connection_mode'] == 2) {//日报模式
            return $this->format_ret(1);
        }
        $mod = $this->get_mod($record_type);

        $base_info = array(
            'record_type' => $record_type,
            'create_time' => date('Y-m-d H:i:s'),
            'cancel_flag' => 0,
            'cancel_request_flag' => 0,
            'cancel_response_flag' => 0,
            'upload_request_flag' => 0,
            'upload_response_flag' => 0,
            'create_time' => date('Y-m-d H:i:s'),
            'api_product' => $config['join_config']['api_product'],
            'mid_code' => $config['join_config']['mid_code'],
        );

        $ret = $mod->get_mid_data($record_code, $base_info);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $mid_order_data = array(
            $ret['data']
        );
        $update_str = "upload_request_flag=VALUES(upload_request_flag),upload_response_flag=VALUES(upload_response_flag), cancel_request_flag=VALUES(cancel_request_flag),cancel_response_flag=VALUES(cancel_response_flag), cancel_flag = VALUES(cancel_flag),express_code = VALUES(express_code),express_no = VALUES(express_no) ";
        $this->insert_multi_duplicate('mid_order', $mid_order_data, $update_str);

        return $this->format_ret(1);
    }

    function get_mod($record_type) {
        static $mod_name_arr = array();

        if (!isset($mod_name_arr[$record_type])) {
            $name_arr = explode('_', $record_type);
            $mod_name = 'Mid';
            foreach ($name_arr as $name) {
                $mod_name.=ucfirst($name);
            }
            $mod_name .= 'Model';
            $mod_name_arr[$record_type] = $mod_name;
        } else {
            $mod_name = $mod_name_arr[$record_type];
        }

        return load_model('mid/' . $mod_name);
    }

    function set_process_flow_end($record_code, $record_type, $api_product, $api_data = array()) {
        $data = array(
            'order_flow_end_flag' => 1,
            'process_flag' => 0,
            'order_time' => time(),
        );
        if (!empty($api_data)) {
            $data['order_time'] = isset($api_data['order_time']) ? $api_data['order_time'] : $data['order_time'];
            if (isset($api_data['express_code'])) {
                $data['express_code'] = $api_data['express_code'];
            }
            if (isset($api_data['express_no'])) {
                $data['express_no'] = $api_data['express_no'];
            }
        }

        $where = " record_code='{$record_code}' AND  record_type='{$record_type}' AND   api_product='{$api_product}'  ";
        $this->db->update('mid_order', $data, $where);
    }

    /**
     * 获取上次调用记录
     * 
     */
    function get_api_record($mid_code, $api_name) {
        $sql = "select * from mid_api_record where mid_code=:mid_code AND api_name=:api_name";
        $sql_values = array(
            ':mid_code' => $mid_code,
            ':api_name' => $api_name,
        );
        return $this->db->get_row($sql, $sql_values);
    }

    /**
     * 获取上次调用记录
     * 
     */
    function save_api_record($data) {
        $update_str = " api_request_time = VALUES(api_request_time) ";
        if (isset($data['start_time'])) {
            $update_str.="  ,start_time = VALUES(start_time)";
        }
        if (isset($data['end_time'])) {
            $update_str.=" , end_time = VALUES(end_time) ";
        }
        if (isset($data['last_api_time'])) {
            $update_str.=" , last_api_time = VALUES(last_api_time)";
        }
        if (isset($data['request_data'])) {
            $update_str.=" , request_data = VALUES(request_data) ";
        }
        $this->insert_multi_duplicate('mid_api_record', array($data), $update_str);
    }

}
