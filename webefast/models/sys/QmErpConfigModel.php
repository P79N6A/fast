<?php
/**
 * 奇门erp
 */
require_model('tb/TbModel');
require_lib('apiclient/QmErpClient');


class QmErpConfigModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'qm_erp_config';
    }



    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";

        if (isset($filter['erp_config_name']) && $filter['erp_config_name'] != '') {
            $sql_main .= " AND erp_config_name LIKE :erp_config_name";
            $sql_values[':erp_config_name'] = $filter['erp_config_name'] . '%';
        }

        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 新增，编辑配置
     * @param $data
     * @param $type
     * @return array
     */
    function opt_config($data, $type) {
        //去除空格
        $data = load_model('sys/KisdeeConfigModel')->trim_array($data);
        $check_res = $this->check_params($data);
        if ($check_res['status'] != 1) {
            return $check_res;
        }

        $ret_count = $this->is_exists_config_name($data, $type);
        if ($ret_count > 0) {
            return $this->format_ret(-1, '', '已存在此配置名：' . $data['qm_erp_config_name']);
        }

        //校验绑定的店铺是否已存在
        $shop_arr = array_column($data['shop'], 'shop_store_code');
        if (count($shop_arr) != count(array_unique($shop_arr))) {
            return $this->format_ret(-1, '', '存在相同的系统店铺！');
        }
        $config_id = ($type == 'add') ? '' : $data['qm_erp_config_id'];
        $ret_check = load_model('sys/ShopStoreModel')->check_bind_shop($shop_arr, 4, $config_id);
        if (!empty($ret_check['data'])) {
            $msg = implode(',', array_column($ret_check['data'], 'shop_name'));
            return $this->format_ret(-1, '', $msg . " 店铺已经被使用");
        }

        //校验绑定的仓库是否已存在
        $store_arr = array_column($data['store'], 'shop_store_code');
        if (count($store_arr) != count(array_unique($store_arr))) {
            return $this->format_ret(-1, '', '存在相同的系统仓库！');
        }
        $ret_check = load_model('sys/ShopStoreModel')->check_bind_store($store_arr, 4, $config_id);
        if (!empty($ret_check['data'])) {
            $msg = implode(',', array_column($ret_check['data'], 'store_name'));
            return $this->format_ret(-1, '', $msg . "仓库已经被使用");
        }
        //分销商
        $custom_arr = array_column($data['fx'], 'sys_fx');
        if (count($custom_arr) != count(array_unique($custom_arr))) {
            return $this->format_ret(-1, '', '存在相同的系统分销商！');
        }

        $config_data = array();
        $config_data['qm_erp_config_name'] = $data['qm_erp_config_name'];
        $config_data['qm_erp_system'] = $data['qm_erp_system'];
        $config_data['target_key'] = $data['target_key'];
        $config_data['customer_id'] = $data['customer_id'];
        $config_data['manage_stock'] = $data['manage_stock'];
        $config_data['item_infos_download'] = $data['item_infos_download'];
        $config_data['online_time'] = $data['online_time'];
        $config_data['trade_sync'] = $data['trade_sync'];
        $this->begin_trans();
        if ($type == 'edit') {
            $ret = parent::update($config_data, array('qm_erp_config_id' => $data['qm_erp_config_id']));
        } else {
            $ret = parent::insert($config_data);
        }

        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $id = $type == 'add' ? $ret['data'] : $data['qm_erp_config_id'];

        //插入关联表
        if ($type == 'edit') {
            $this->delete_exp('sys_api_shop_store', array('p_id' => $id, 'p_type' => 4));
            $this->delete_exp('qm_sys_api_fx', array('p_id' => $id));
        }

        //店铺
        $api_shop_store_arr = array();
        foreach ($data['shop'] as $val) {
            $api_shop_store_arr[] = array(
                'p_id' => $id,
                'p_type' => 4,
                'shop_store_code' => $val['shop_store_code'],
                'shop_store_type' => 0,
                'outside_type' => 0,
                'outside_code' => $val['outside_code'],
            );
        }

        //仓库
        foreach ($data['store'] as $val) {
            $api_shop_store_arr[] = array(
                'p_id' => $id,
                'p_type' => 4,
                'shop_store_code' => $val['shop_store_code'],
                'shop_store_type' => 1,
                'outside_type' => 1,
                'outside_code' => $val['outside_store'],
            );
        }
        $ret = $this->insert_multi_exp('sys_api_shop_store', $api_shop_store_arr, TRUE);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }

        //分销商
        $fx_arr = array();
        foreach ($data['fx'] as $val) {
            $fx_arr[] = array(
                'p_id' => $id,
                'custom_code' => $val['sys_fx'],
                'outside_code' => $val['outside_fx'],
            );
        }
        $ret = $this->insert_multi_exp('qm_sys_api_fx', $fx_arr, TRUE);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }

        $this->commit();
        return $this->format_ret(1, $id, '');
    }


    /**
     * 校验参数信息
     * @param $params
     * @param array $field_arr
     * @param int $type
     * @return array
     */
    private function check_params($params, $field_arr = array(), $type = 0) {
        if ($type == 0) {
            $field_arr = array_merge($field_arr, array('qm_erp_config_name' => '配置名称', 'online_time' => '上线日期', 'customer_id' => 'Customer ID', 'store' => '仓库', 'shop' => '店铺', 'fx' => '分销商','target_key'=>'目标app_key'));
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
     * 检查配置名称是否已存在
     * @param $data
     * @param string $type
     * @return bool|mixed
     */
    function is_exists_config_name(&$data, $type = 'add') {
        $wh = '';
        $sql_values = array(':config_name' => $data['config_name']);
        if ($type == 'edit') {
            $wh = ' AND qm_erp_config_id <>:config_id';
            $sql_values[':config_id'] = $data['config_id'];
        }
        $sql = "SELECT count(1) FROM qm_erp_config WHERE qm_erp_config_name=:config_name {$wh}";
        $ret_count = $this->db->get_value($sql, $sql_values);
        return $ret_count;
    }

    /**
     * 删除
     * @param $qm_erp_config_id
     * @return bool|mixed
     */
    function do_delete($qm_erp_config_id) {
        $config = $this->get_by_id($qm_erp_config_id);
        if ($config['status'] != 1) {
            return $this->format_ret(-1, '', '该配置不存在！');
        }
        $config_data = $config['data'];
        $this->begin_trans();
        $ret = $this->delete_exp('qm_erp_config', array('qm_erp_config_id' => $qm_erp_config_id));
        if (!$ret) {
            $this->rollback();
            return $this->format_ret('-1', '', '删除配置失败！');
        }
        $ret = $this->delete_exp('qm_sys_api_fx', array('p_id' => $qm_erp_config_id));
        if (!$ret) {
            $this->rollback();
            return $this->format_ret('-1', '', '删除分销商失败！');
        }
        $ret = load_model('sys/ShopStoreModel')->delete_store_config($qm_erp_config_id, 4);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $ret = $this->delete_exp('qm_erp_outside_store', array('customer_id' => $config_data['customer_id']));
        if (!$ret) {
            $this->rollback();
            return $this->format_ret('-1', '', '删除外部仓库档案失败！');
        }
        $ret = $this->delete_exp('qm_erp_outside_custom', array('customer_id' => $config_data['customer_id']));
        if (!$ret) {
            $this->rollback();
            return $this->format_ret('-1', '', '删除外部分销商档案失败！');
        }

        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 获取配置信息
     * @param $id
     * @return array
     */
    function get_by_id($id) {
        $arr = $this->get_row(array('qm_erp_config_id' => $id));
        return $arr;
    }

    /**
     * 获取外部分销商档案
     * @param $customer_id
     * @return array|bool
     */
    function get_custom_by_id($customer_id) {
        $sql = "SELECT * FROM qm_erp_outside_custom WHERE customer_id=:customer_id";
        $sql_values[':customer_id'] = $customer_id;
        $ret = $this->db->get_all($sql, $sql_values);
        return $ret;
    }

    /**
     * 获取外部仓库档案
     * @param $customer_id
     * @return array|bool
     */
    function get_store_by_id($customer_id) {
        $sql = "SELECT * FROM qm_erp_outside_store WHERE customer_id=:customer_id";
        $sql_values[':customer_id'] = $customer_id;
        $ret = $this->db->get_all($sql, $sql_values);
        return $ret;
    }

    /**
     * 获取已绑定的分销商信息
     * @param $config_id
     * @return array|bool
     */
    function get_custom_by_config_id($config_id) {
        $sql = "SELECT * FROM qm_sys_api_fx WHERE p_id=:p_id";
        $sql_values[':p_id'] = $config_id;
        $ret = $this->db->get_all($sql, $sql_values);
        return $ret;
    }


    /**
     * 接口测试
     * @param $params
     * @return array
     */
    function api_test($params) {
        $field_arr = array('target_key' => '目标AppKey', 'customer_id' => 'customer id');
        $check_res = $this->check_params($params, $field_arr, 1);
        if ($check_res['status'] != 1) {
            return $check_res;
        }
        $api_obj = $this->get_api_obj($params['target_key'], $params['customer_id']);
        $api_params['pageSize'] = 1;
        $ret = $api_obj->get_customer($api_params);
        return $ret;
    }

    /**
     * 获取外部分销商
     * @param $params
     * @return array
     */
    function get_outside_customer($params) {
        $field_arr = array('target_key' => '目标AppKey', 'customer_id'=>'customer id');
        $check_res = $this->check_params($params, $field_arr, 1);
        if ($check_res['status'] != 1) {
            return $check_res;
        }

        $api_obj = $this->get_api_obj($params['target_key'], $params['customer_id']);
        $ret = $api_obj->get_customer(array());
        if ($ret['status'] != 1) {
            return $ret;
        }
        //分页下载
        $page_size = 100;
        $total_page = ceil($ret['total'] / $page_size); //总页数
        for ($page_no = 1; $page_no <= $total_page; $page_no++) {//使用倒序
            $api_params = array();
            $api_params['page'] = $page_no;
            $api_params['pageSize'] = $page_size;
            $ret = $api_obj->get_customer($api_params);
            if($ret['status']!=1){
                return $ret;
            }
            $ret = $this->save_outside_customer($params['customer_id'], $ret['item']['item']);
            if ($ret['status'] != 1) {
                return $ret;
            }
        }

//        $result = '{
//    "code":"0",
//    "flag":"success|failure",
//    "message":"获取成功!",
//    "total":36,
//    "item":{
//        "item":[
//            {
//                "CustomerCode":"DLJMD1",
//                "CustomerName":"外部分销2",
//                "SJCustomerCode":"YJDL01",
//                "ReveiveName":"张三",
//                "ReceiveAddress":"浙江省 杭州市 余杭区 文一西路969号",
//                "Tel":"13111111111",
//                "ZipCode":"310000",
//                "EMAIL":"xxx@xxx.com",
//                "CustomerType":"客户类型",
//                "extendProps":"扩展信息"
//            },
//              {
//                "CustomerCode":"DLJMD2",
//                "CustomerName":"外部分销2",
//                "SJCustomerCode":"YJDL01",
//                "ReveiveName":"张三",
//                "ReceiveAddress":"浙江省 杭州市 余杭区 文一西路969号",
//                "Tel":"13111111111",
//                "ZipCode":"310000",
//                "EMAIL":"xxx@xxx.com",
//                "CustomerType":"客户类型",
//                "extendProps":"扩展信息"
//            }
//        ]
//    }
//}';
//        $ret = json_decode($result, true);//var_dump($ret);exit;
//        $ret = $this->save_outside_customer($params['customer_id'], $ret['item']['item']);
//        if ($ret['status'] != 1) {
//            return $ret;
//        }

        $sql = "SELECT customer_code,customer_name FROM qm_erp_outside_custom WHERE customer_id=:customer_id";
        $sql_values[':customer_id'] = $params['customer_id'];
        $outside_customer_data = $this->db->get_all($sql, $sql_values);
        return $this->format_ret(1,$outside_customer_data,'获取成功！');
    }

    /**
     * 获取外部仓库
     * @param $params
     * @return array
     */
    function get_outside_store($params) {
        $field_arr = array('target_key' => '目标AppKey', 'customer_id'=>'customer id');
        $check_res = $this->check_params($params, $field_arr, 1);
        if ($check_res['status'] != 1) {
            return $check_res;
        }
        $api_obj = $this->get_api_obj($params['target_key'], $params['customer_id']);
        $api_params = array();
        $api_params['basedataType'] = 1;//仓库类型
        $ret = $api_obj->sync_base_data($api_params);
        if ($ret['status'] != 1) {
            return $ret;
        }
        //分页下载
        $page_size = 100;
        $total_page = ceil($ret['total'] / $page_size); //总页数
        for ($page_no = 1; $page_no <= $total_page; $page_no++) {//使用倒序
            $api_params['page_no'] = $page_no;
            $api_params['pageSize'] = $page_size;
            $ret = $api_obj->sync_base_data($api_params);
            if ($ret['status'] != 1) {
                return $ret;
            }
            $ret = $this->save_outside_store($params['customer_id'], $ret['items']['items']);
            if ($ret['status'] != 1) {
                return $ret;
            }
        }

//        $result = '{
//    "code":"0",
//    "flag":"success|failure",
//    "message":"获取成功!",
//    "total":"68",
//    "items":{
//        "items":[
//            {
//                "id":"123456789",
//                "billNumber":"123456",
//                "name":"外仓1",
//                "isEnable":"完成",
//                "extendProps":"扩展信息"
//            },
//             {
//                "id":"123456789",
//                "billNumber":"446456456",
//                "name":"外仓2",
//                "isEnable":"完成",
//                "extendProps":"扩展信息"
//            }
//        ]
//    }
//}';
//        $ret = json_decode($result, true);
//        $ret = $this->save_outside_store($params['customer_id'], $ret['items']['items']);       // var_dump($ret);exit;
//        if ($ret['status'] != 1) {
//            return $ret;
//        }
//
        $sql = "SELECT bill_number,store_name FROM qm_erp_outside_store WHERE customer_id=:customer_id";
        $sql_values[':customer_id'] = $params['customer_id'];
        $outside_store_data = $this->db->get_all($sql, $sql_values);
        return $this->format_ret(1, $outside_store_data, '获取成功！');
    }

    /**
     * 保存外部分销商
     * @param $customer_id
     * @param $customer
     * @return array
     */
    function save_outside_customer($customer_id, $customer) {
        $insert_params = array();
        foreach ($customer as $val) {
            $insert_params[] = array(
                'customer_id' => $customer_id,
                'customer_code' => $val['CustomerCode'],
                'customer_name' => $val['CustomerName'],
                'reveive_name' => $val['ReveiveName'],
                'receive_address' => $val['ReceiveAddress'],
                'tel' => $val['Tel'],
                'zip_code' => $val['ZipCode'],
                'email' => $val['EMAIL'],
                'customer_type' => $val['CustomerType'],
                'extend_props' => $val['extendProps'],
            );
        }
        $ret = $this->insert_multi_exp('qm_erp_outside_custom', $insert_params, true);
        if ($ret['status'] != 1) {
            return $ret;
        }
        return $this->format_ret(1);
    }

    /**
     * 保存外部仓库
     * @param $customer_id
     * @param $store
     * @return array
     */
    function save_outside_store($customer_id, $store) {
        $insert_params = array();
        foreach ($store as $val) {
            $insert_params[] = array(
                'customer_id' => $customer_id,
                'store_id' => $val['id'],
                'bill_number' => $val['billNumber'],
                'store_name' => $val['name'],
                'is_enable' => $val['isEnable'],
                'extend_props' => $val['extendProps'],
            );
        }
        $ret = $this->insert_multi_exp('qm_erp_outside_store', $insert_params, true);
        if ($ret['status'] != 1) {
            return $ret;
        }
        return $this->format_ret(1);
    }

    /**
     * 获取接口对象
     * @param $target_key
     * @param $customer_id
     * @return QmErpClient
     */
    function get_api_obj($target_key, $customer_id) {
        static $mod_arr = null;
        $key = $target_key . "_" . $customer_id;
        if (!isset($mod_arr[$key])) {
            $api_config['target_key'] = $target_key;
            $api_config['customer_id'] = $customer_id;
            $_client = new QmErpClient($api_config);
            return $_client;
//            $record_arr = explode("_", $record_type);
//            $mod_str = ucfirst($api_product);
//            foreach ($record_arr as $val) {
//                $mod_str.=ucfirst($val);
//            }
//
//            $mod_path = 'o2o/' . $api_product . '/' . ucfirst($mod_str) . 'Model';
//            $mod_arr[$api_product][$record_type] = load_model($mod_path);
        }
        return $mod_arr[$key];
    }


}
    