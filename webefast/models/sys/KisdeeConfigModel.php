<?php

require_model('tb/TbModel');

/**
 * Kisdee 配置模块
 */
class KisdeeConfigModel extends TbModel {

    private $config_param = array(
        'kis_ver' => '2.0',
    );
    private $custdata_param = array(
        'ProductID' => 'S1S013S001'
    );

    function __construct() {
        parent :: __construct('kisdee_config');
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";

        if (isset($filter['config_name']) && $filter['config_name'] != '') {
            $sql_main .= " AND config_name LIKE :config_name";
            $sql_values[':config_name'] = "%{$filter['config_name']}%";
        }

        $select = 'config_id,config_name,config_status,online_time,lastchanged';

        $sql_values .= ' ORDER BY lastchanged DESC ';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 根据id获取配置信息
     * @param int $id config_id
     * @return array 数据集
     */
    function get_by_id($id) {
        $arr = $this->get_row(array('config_id' => $id));
        return $arr;
    }

    /**
     * 获取配置编辑信息
     * @param int $id config_id
     * @return array 数据集
     */
    function get_config_edit_info($id) {
        $ret = $this->get_by_id($id);
        $data = $ret['data'];
        $kis_custdata = json_decode($data['kis_custdata'], TRUE);
        unset($data['kis_custdata']);
        $data = array_merge($data, $kis_custdata);

        return $data;
    }

    /**
     * 查询记录是否存在
     * @param string $value 字段值
     * @param sting $field_name 字段名
     * @return array 数据集
     */
    private function is_exists($value, $field_name = 'config_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 删除记录
     */
    function delete($id) {
        $this->begin_trans();
        $ret = parent :: delete(array('config_id' => $id));
        $affect_row = $this->affected_rows();
        if ($ret['status'] != 1 || $affect_row != 1) {
            $this->rollback();
        } else {
            $ret = load_model('sys/ShopStoreModel')->delete_store_config($id, 2);
            $affect_row = $this->affected_rows();
            if ($ret['status'] != 1) {
                $this->rollback();
            } else {
                $this->commit();
            }
        }

        return $ret;
    }

    /**
     * 更新启用状态
     * @param int $id config_id
     * @param string $active 状态
     * @return array 启用结果
     */
    function update_active($id, $active) {
        $active_arr = array('enable' => 1, 'disable' => 0);
        if (!array_key_exists($active, $active_arr)) {
            return $this->format_ret('error_params');
        }
        $active = $active_arr[$active];
        if ($active == 1) {
            $config = $this->is_exists(1, 'config_status');
            if (!empty($config['data'])) {
                return $this->format_ret(-1, '', '启用失败，存在已启用的配置！');
            }
            $ret_check = $this->check_config_valid($id);
            if ($ret_check['status'] != 1) {
                return $ret_check;
            }
        }

        $this->begin_trans();
        $ret = parent::update(array('config_status' => $active), array('config_id' => $id));
        $affect_row = $this->affected_rows();
        if ($ret['status'] != 1 || $affect_row != 1) {
            $this->rollback();
        } else {
            $this->commit();
        }

//        $this->update_kis_menu();

        return $ret;
    }

    /**
     * 创建新配置
     * @param array $data 配置信息
     * @return array 操作结果
     */
    function opt_config($data, $type) {
        $data = $this->trim_array($data);

        if ($type == 'add') {
            $check_res = $this->check_params($data);
        } else {
            $check_res = $this->check_params($data, array('config_id' => '配置ID'), 1);
        }
        if ($check_res['status'] != 1) {
            return $check_res;
        }

        $ret_count = $this->is_exists_config_name($data, $type);
        if ($ret_count > 0) {
            return $this->format_ret(-1, '', '已存在此配置名：' . $data['config_name']);
        }
        $config_data = array();
        $msg = $this->deal_data($data, $config_data);
        $store_data = $data['store'];

        $this->begin_trans();
        if ($type == 'edit') {
            $ret = parent::update($config_data, array('config_id' => $data['config_id']));
        } else {
            $ret = parent::insert($config_data);
        }

        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $id = $type == 'add' ? $ret['data'] : $data['config_id'];
        $api_shop_store = array(
            'p_id' => $id,
            'p_type' => 2,
            'shop_store_type' => 1,
            'outside_type' => 1,
        );
        foreach ($store_data as $key => &$val) {
            if (empty($val['shop_store_code'])) {
                unset($store_data[$key]);
                continue;
            }
            $val = array_merge($val, $api_shop_store);
        }
        if ($type == 'edit') {
            $ret = load_model('sys/ShopStoreModel')->delete_store_config($id, 2);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
        }
        $ret = $this->insert_multi_exp('sys_api_shop_store', $store_data, TRUE);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }

        $this->commit();
        return $this->format_ret(1, $id, $msg);
    }

    /**
     * 处理数据，用于添加/更新配置
     * @param array $data 数据
     * @return array 处理结果数据
     */
    function deal_data(&$data, &$config_data) {
        $msg = '保存成功';
        $kis_params = get_array_vars($data, array('kis_eid', 'kis_auth_token'));
        $kis_params = $this->remove_child_str($kis_params, 'kis_');
        if (empty($data['kis_server_url']) || empty($data['netid'])) {
            $result = load_model('api/kis/KisApiModel')->request_api_test($kis_params);
            if ($result['status'] == 1 && !empty($result['data'])) {
                $data['kis_server_url'] = $result['data'][0]['server_url'];
                $data['kis_netid'] = $result['data'][0]['netid'];
            } else {
                $msg = '保存成功，提示：接口连接失败';
            }
        }
        $data['kis_server_url'] = $this->deal_server_url($data['kis_server_url']);

        //组装数据
        $config_data = get_array_vars($data, array('config_name', 'kis_eid', 'online_time', 'kis_server_url', 'kis_netid','kis_auth_token'));
        $config_data = array_merge($config_data, $this->config_param);

        $kis_custdata['ProductID'] = $this->custdata_param['ProductID'];
        $kis_custdata['AccountDB'] = $data['AccountDB'];
        $config_data['kis_custdata'] = json_encode($kis_custdata);

        return $msg;
    }

    /**
     * 新增/编辑配置时检查配置名称是否存在
     * @param array $data 配置数据
     * @param type $type
     * @return type
     */
    function is_exists_config_name(&$data, $type = 'add') {
        $wh = '';
        $sql_values = array(':config_name' => $data['config_name']);
        if ($type == 'edit') {
            $wh = ' AND config_id <>:config_id';
            $sql_values[':config_id'] = $data['config_id'];
        }
        $sql = "SELECT count(1) FROM kisdee_config WHERE config_name=:config_name {$wh}";
        $ret_count = $this->db->get_value($sql, $sql_values);
        return $ret_count;
    }

    /**
     * 启用成功后显示
     */
    function update_kis_menu() {
        $sql = "SELECT config_name FROM kisdee_config WHERE config_status=1";
        $data = $this->db->get_row($sql);
        $status = 1;
        if (empty($data)) {
            $status = 0;
        }
        $action_arr = array(
            array(
                'status' => $status,
                'action_id' => '13020000',
            ),
            array(
                'status' => $status,
                'action_id' => '13020100',
            ),
        );
        foreach ($action_arr as $action) {
            load_model('sys/PrivilegeModel')->update_status($action['action_id'], $action['status']);
        }
        return $this->format_ret(1);
    }

    /**
     * 金蝶kis接口连通测试
     * @param array $params 接口参数
     */
    function api_test($params) {
        $field_arr = array('kis_eid' => 'eid', 'kis_auth_token' => 'auth_token');
        $check_res = $this->check_params($params, $field_arr, 0);
        if ($check_res['status'] != 1) {
            return $check_res;
        }
        $params = $this->remove_child_str($params, 'kis_');

        $result = load_model('api/kis/KisApiModel')->request_api_test($params);
        if ($result['status'] != 1 || empty($result['data'])) {
            return $this->format_ret(-1, '', '接口连接失败，请检查金蝶服务状态');
        }

        $result['data'] = get_array_vars($result['data'][0], array('server_url', 'netid'));

        return $result;
    }

    /**
     * 检查参数是否存在
     * @param array $params 参数
     * @param array $field_arr 要检查的字段 array('字段'=>'字段名')
     * @return array 检查结果
     */
    private function check_params($params, $field_arr = array(), $type = 0) {
        if ($type === 1 || empty($field_arr)) {
            $field_arr = array_merge($field_arr, array('config_name' => '配置名称', 'online_time' => '上线日期', 'kis_eid' => '企业号', 'AccountDB' => '账套号', 'kis_auth_token' => '访问口令', 'store' => '仓库'));
        }
        $status = '1';
        $msg = '';
        foreach ($field_arr as $k => $v) {
            if (!isset($params[$k]) || empty($params[$k])) {
                $status = '-1';
                $msg = $v . ' 不能为空';
                break;
            }
        }
        return $this->format_ret($status, array(), $msg);
    }

    /**
     * 去掉数组或字符串键的某个子串
     * @param array $params 参数
     * @param string $str 子串
     * @return array 新数组
     */
    function remove_child_str($params, $str) {
        if (!is_array($params)) {
            return str_replace($str, '', $params);
        }
        $data = array();
        foreach ($params as $k => $v) {
            $key = str_replace($str, '', $k);
            $data[$key] = trim($v);
        }
        return $data;
    }

    /**
     * 为字符串或数组键添加前缀
     * @param array/string $params 参数
     * @param string $str 前缀
     * @return array/string 处理结果
     */
    function append_prefix($params, $str) {
        if (!is_array($params)) {
            return $str . $params;
        }
        $data = array();
        foreach ($params as $k => $v) {
            $k = $str . $k;
            $data[$k] = $v;
        }
        return $data;
    }

    /**
     * 处理业务路由url
     * @param string $url ip/域名
     * @return string 处理后的url
     */
    function deal_server_url($url) {
        $url = trim($url);
        if (empty($url)) {
            return '';
        }
        $str = '/Kisopenapi/router/';
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = 'http://' . $url . $str;
        } else {
            $url = $url . $str;
        }
        return $url;
    }

    /**
     * 启用配置时，检查配置参数是否有效
     * @param int $id config_id
     * @return array 检查结果
     */
    private function check_config_valid($id) {
        $config = $this->get_by_id($id);
        $config = $config['data'];
        $kis_custdata = json_decode($config['kis_custdata'], TRUE);
        $config = array_merge($config, $kis_custdata);

        $field_arr = array('online_time', 'kis_method', 'kis_ver', 'kis_eid', 'kis_custdata', 'ProductID', 'AccountDB', 'kis_auth_token');
        //配置参数信息检查
        foreach ($config as $key => $val) {
            if (in_array($key, $field_arr) && empty($val)) {
                return $this->format_ret(-1, '', '参数有误');
            }
        }
        //检查是否存在仓库对应关系
        $store_arr = load_model('sys/ShopStoreModel')->get_type_data($id, 2);
        if ($store_arr['status'] != 1 || empty($store_arr['data'])) {
            return $this->format_ret(-1, '', '未配置系统仓库');
        }

        $result = load_model('api/kis/KisApiModel')->request_api_test(array('eid' => $config['kis_eid'], 'auth_token' => $config['kis_auth_token']));
        if ($result['status'] != 1 || empty($result['data'])) {
            return $this->format_ret(-1, '', '接口连接失败，不能启用，请检查金蝶服务状态');
        }

        return $this->format_ret(1);
    }

    /**
     * 去除数组或字符串空格
     * @param array/string $data 数组或字符串
     * @return array/string 处理结果
     */
    function trim_array($data) {
        if (!is_array($data)) {
            return trim($data);
        }
        while (list($key, $value) = each($data)) {
            if (is_array($value)) {
                $data[$key] = $this->trim_array($value);
            } else {
                $data[$key] = trim($value);
            }
        }
        return $data;
    }

    /**
     * 获取业务API参数
     */
    function get_api_params() {
        $sql = 'SELECT config_id,kis_server_url,kis_netid,kis_ver,kis_eid,kis_auth_token,kis_custdata,online_time FROM kisdee_config WHERE config_status = 1';
        $ret = $this->db->get_row($sql);
        if (empty($ret)) {
            return '';
        }
        $api_params = $this->remove_child_str($ret, 'kis_');
        $api_params['custdata'] = json_decode($api_params['custdata'], TRUE);

        return $api_params;
    }

    /**
     * 检查是否有已启用的配置
     */
    function is_enable_config() {
        $sql = 'SELECT COUNT(1) FROM kisdee_config WHERE config_status=1';
        return $this->db->get_value($sql);
    }

}
