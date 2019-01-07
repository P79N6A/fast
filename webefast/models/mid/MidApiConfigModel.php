<?php

require_model('tb/TbModel');

class MidApiConfigModel extends TbModel {

    function __construct() {
        parent::__construct('mid_api_config');
    }

    public $erp_type=array(
        '0'=>'直连',
        '1'=>'奇门',
    );

    function get_service_arr() {
        $service_code_arr = array(
            'mes' => 'MES系统',
            'bserp2'=> 'BSERP2'
        );
        $code_arr = array();
        foreach ($service_code_arr as $code => $v) {
            $check = load_model('common/ServiceModel')->check_is_auth_by_value($code);
            if ($check === true) {
                $code_arr[$code] = $v;
            }
        }
        return $code_arr;
    }

    //临时增加后续完善
    function init_mid_service() {
        $code_arr = $this->get_service_arr();
        if (empty($code_arr)) {
            return false;
        }

        $api_process_flow = require_conf('mid/api_process_flow');
        $process_flow_data = array();
        foreach ($code_arr as $api_code => $v) {
            foreach ($api_process_flow[$api_code] as $record_type => $val) {
                $val['api_product'] = $api_code;
                $val['record_type'] = $record_type;
                $process_flow_data[] = $val;
            }
        }

        if (!empty($process_flow_data)) {
            $update_str = " api_product = VALUES(api_product) ";
            $this->insert_multi_duplicate('mid_process_flow', $process_flow_data, $update_str);
        }
        return true;
    }

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} rl

                    WHERE 1";
        $sql_values = array();

        // 单据编号
        if (isset($filter['api_name']) && $filter['api_name'] != '') {
            $sql_main .= " AND (rl.api_name LIKE :api_name )";
            $sql_values[':api_name'] = '%' . $filter['api_name'] . '%';
        }
        if (isset($filter['_id']) && $filter['_id'] != '') {
            $sql_main .= " AND (rl.id = :id )";
            $sql_values[':id'] = $filter['_id'];
        }
        $select = 'rl.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            if ($val['api_product'] == 'bserp2') {
                $val['erp_type_name'] = $this->erp_type[$val['erp_type']];
            } else {
                $val['erp_type_name'] = '';
            }
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     *
     * @staticvar type $mid_arr
     * @param type $mid_code 配置代码
     * @param type $type 获取参数方式 0 配置文件方式，1 带名称方式
     * @return type
     */
    function get_config($mid_code, $type = 0) {

        static $mid_arr = null;
        if (!isset($mid_arr[$mid_code][$type])) {
            $sql = "select * from mid_api_config where 1 AND mid_code = :mid_code ";
            $sql_value = array(':mid_code' => $mid_code);
            $row = $this->db->get_row($sql, $sql_value);
            $mid_arr[$mid_code][$type] = array();
            if (!empty($row)) {
                $mid_arr[$mid_code][$type] = $this->match_config($row, $type);
            }
        }
        return $this->format_ret(1, $mid_arr[$mid_code][$type]);
    }

    /**
     *
     * @param type $sys_code 仓库代码或店铺代码
     * @param type $type 0为店铺，1为仓库
     * @return type
     */
    function check_is_mid_by_code($sys_code, $type = 0) {


        $sql = "select * from mid_api_join where join_sys_code=:sys_code AND join_sys_type=:join_sys_type ";
        $sql_value = array(
            ':sys_code' => $sys_code,
            ':join_sys_type' => $type,
        );
        $data = $this->db->get_row($sql, $sql_value);
        $api_product = false;
        if (!empty($data)) {
            $ret = $this->get_row(array('mid_code' => $data['mid_code']));
            $api_product = $ret['data']['api_product'];
        }

        return $api_product;
    }

    /**
     *  通过系统代码获取中间配置代码
     * @param type $sys_code 仓库代码或店铺代码
     * @param type $type 0为店铺，1为仓库
     * @return type
     */
    function get_mid_config_by_sys_code($sys_code, $api_product, $type = 1) {

        $sql = "select * from mid_api_join where join_sys_code=:sys_code AND join_sys_type=:join_sys_type ";
        $sql_value = array(
            ':sys_code' => $sys_code,
            ':join_sys_type' => $type,
        );
        $data = $this->db->get_all($sql, $sql_value);

        if (!empty($data)) {
            foreach ($data as $val) {
                $ret = $this->get_row(array('mid_code' => $val['mid_code']));
                if ($ret['data']['api_product'] != $api_product) {
                    continue;
                }
                $val['api_product'] = $ret['data']['api_product'];
                $ret_data = array(
                    'join_config' => $val,
                );
                $ret_data['api_config'] = $this->match_config($ret['data']);

                return $this->format_ret(1, $ret_data, '');
            }
        }
        return $this->format_ret(-1, '', '未找到数据');
    }

    /**
     *
     * 获取系统链接配置
     * @param type $mid_code
     * @param type $sys_code
     * @param type $type
     * @return type
     */
    function get_mid_api_join_config($mid_code, $sys_code, $type = 0) {
        $sql = "select * from mid_api_join where mid_code=:mid_code AND join_sys_code=:join_sys_code and join_sys_type=:join_sys_type";
        $sql_values = array(
            ':mid_code' => $mid_code,
            ':join_sys_code' => $sys_code,
            ':join_sys_type' => $type,
        );
        $data = $this->db->get_row($sql, $sql_values);

        return $this->format_ret(1, $data);
    }

    /**
     *  匹配转换配置文件
     * @param type $mid_api_config
     * @param type $type 0 标准配置模式 ，1 全数据模式
     * @return type
     */
    private function match_config($mid_api_config, $type = 0) {
        $api_param = require_conf('mid/api_param');

        $api_config = isset($api_param[$mid_api_config['api_product']]['config']) ?
                $api_param[$mid_api_config['api_product']]['config'] : array();

        $config = json_decode($mid_api_config['api_param_json'], true);

        foreach ($api_config as $key => $val) {
            if ($type == 0) {
                $config[$val['key']] = !empty($mid_api_config[$key]) ? $mid_api_config[$key] : $config[$val['key']];
            } else {
                $config[$val['key']] = $val;
                $config[$val['key']]['value'] = !empty($mid_api_config[$key]) ? $mid_api_config[$key] : $config[$val['key']];
            }
        }


        return $config;
    }

    function del($id, $mid_code) {
        $params = array('id' => $id);
        $ret = $this->get_row($params);
        if (!empty($ret['data'])) {
            $sql = "select id from mid_order where mid_code=:mid_code";
            $sql_value = array(':mid_code' => $ret['data']['mid_code']);
            $check = $this->db->get_value($sql, $sql_value);
            if (!empty($check)) {
                return $this->format_ret(-1, '', '配置已经在使用，不能删除！');
            }
        }

        $where_join = " mid_code = '{$mid_code}'";
        $where = " id = '{$id}'";
        $this->delete_exp('mid_api_join', $where_join);
        return $this->delete($where);
    }

    /* 添加 */

    function add_info($request) {
        $params = require_conf('mid/api_param');
        $extra_params = array();
        if (isset($params[$request['api_product']])) {
            $conf = $params[$request['api_product']]['config'];
            foreach ($conf as $val) {
                $extra_params[$val['key']] = $request[$val['key']];
            }
        }
        $wms_key = json_encode($extra_params);
        $data['mid_code'] = $request['api_name'] . date('i') . date('s');
        $data['api_param_json'] = $wms_key;
        $data['api_product'] = $request['api_product'];
        $data['online_time'] = $request['online_time'];
        $data['api_name'] = $request['api_name'];
        $data['erp_type'] = $request['erp_type'];
        if ($data['api_product'] == 'bserp2' && $data['erp_type'] == 1) {
            $data['target_key'] = $request['target_key'];
            $data['customer_id'] = $request['customer_id'];
        }
        $this->begin_trans();
        $ret = $this->insert($data);
        $store_data = array();
        $store_arr = array();
        foreach ($request['store'] as $key => $value) {
            $data1['outside_code'] = $value['outside_code'];
            $data1['join_sys_code'] = $value['shop_store_code'];
            $data1['join_sys_type'] = isset($value['join_sys_type']) ? $value['join_sys_type'] : 1;
            $data1['outside_type'] = isset($value['join_sys_type']) ? $value['join_sys_type'] : 1;
            $data1['shop_store_code'] = $value['shop_store_code'];
            $data1['param_val1'] = isset($request['connection_mode']) ? $request['connection_mode'] : 0; //erp单据对接模式 1 单据模式 2 日报模式)
            if (empty($data1['outside_code']) || empty($data1['shop_store_code'])) {
                $this->rollback();
                return $this->format_ret(-1, '', "仓库不能为空");
            }
            $data1['mid_code'] = $data['mid_code'];

            $store_data[] = $data1;
            $store_arr[] = $value['shop_store_code'];
        }
        foreach ($request['shop'] as $k => $val) {
            $data2['outside_code'] = $val['outside_code'];
            $data2['join_sys_code'] = $val['shop_shop_code'];
            $data2['join_sys_type'] = isset($val['join_sys_type']) ? $val['join_sys_type'] : 0;
            $data2['outside_type'] = isset($val['join_sys_type']) ? $val['join_sys_type'] : 0;
            $data2['shop_store_code'] = $val['shop_shop_code'];
            $data2['param_val1'] = isset($request['connection_mode']) ? $request['connection_mode'] : 0; //erp单据对接模式 1 单据模式 2 日报模式)
            if ((empty($data2['outside_code']) || empty($data2['shop_store_code'])) && $request['api_product'] != 'mes') {
                continue;
//                $this->rollback();
//                return $this->format_ret(-1, '', "店铺不能为空");
            }
            $data2['mid_code'] = $data['mid_code'];

            $shop_data[] = $data2;
            $shop_arr[] = $val['shop_shop_code'];
        }
        foreach ($request['custom'] as $k => $val) {
            $data3['outside_code'] = $val['outside_code'];
            $data3['join_sys_code'] = $val['custom_custom_code'];
            $data3['join_sys_type'] = isset($val['join_sys_type']) ? $val['join_sys_type'] : 2;
            $data3['outside_type'] = isset($val['join_sys_type']) ? $val['join_sys_type'] : 2;
            $data3['shop_store_code'] = $val['custom_custom_code'];
            $data3['param_val1'] = isset($request['connection_mode']) ? $request['connection_mode'] : 1; //erp单据对接模式 1 单据模式 2 日报模式)
            if (empty($data3['outside_code']) || empty($data3['shop_store_code'])) {
                continue;
//                $this->rollback();
//                $msg = "";
//                return $this->format_ret(-1, '', $msg . "不能为空");
            }
            $data3['mid_code'] = $data['mid_code'];

            $custom_data[] = $data3;
            $custom_arr[] = $val['custom_custom_code'];
        }
        $ret_check = $this->check_store_shop($store_arr);
        $ret_check_1 = $this->check_store_shop($shop_arr,0);
        $ret_check_2 = $this->check_store_shop($custom_arr,2);
        if (!empty($ret_check['data'])) {
            $this->rollback();
            $msg = "";
            foreach ($ret_check['data'] as $val) {
                $msg .= $val['store_name'] . ",";
            }
            $msg = substr($msg, 0, -1);
            return $this->format_ret(-1, '', $msg . " 仓库已经被使用");
        }
        if (!empty($ret_check_1['data'])) {
            $this->rollback();
            $msg = "";
            foreach ($ret_check_1['data'] as $v) {
                $msg .= $v['shop_name'] . ",";
            }
            $msg = substr($msg, 0, -1);
            return $this->format_ret(-1, '', $msg . " 店铺已经被使用");
        }
        if (!empty($ret_check_2['data'])) {
            $this->rollback();
            $msg = "";
            foreach ($ret_check_2['data'] as $v) {
                $msg .= $v['custom_name'] . ",";
            }
            $msg = substr($msg, 0, -1);
            return $this->format_ret(-1, '', $msg . " 分销商已经被使用");
        }
        $ret = parent::insert_exp('mid_api_join', $store_data);
        if(isset($shop_data) && !empty($shop_data)) {
            $ret = parent::insert_exp('mid_api_join', $shop_data);
        }
        if(isset($custom_data) && !empty($custom_data)) {
            $ret = parent::insert_exp('mid_api_join', $custom_data);
        }
        $this->commit();

        return $ret;
    }

    public function get_mid_system($config_id) {
        $sql = "select api_param_json from {$this->table} where id='{$config_id}'";
        $params = $this->db->getOne($sql);
        $params = json_decode($params, true);
        return $this->format_ret(1, $params);
    }

    function edit_info($request) {
        $params = require_conf('mid/api_param');
        $extra_params = array();
        if (isset($params[$request['api_product_flg']])) {
            $conf = $params[$request['api_product_flg']]['config'];
            foreach ($conf as $val) {
                $extra_params[$val['key']] = $request[$val['key']];
            }
        }
        $pre_config = $this->db->get_row("SELECT online_time, api_param_json FROM mid_api_config WHERE id = :id", array(":id" => $request['id']));
        $pre_config_arr = json_decode($pre_config['api_param_json'], 512, JSON_BIGINT_AS_STRING);
        $extra_params['connection_mode'] = isset($pre_config_arr['connection_mode']) ? $pre_config_arr['connection_mode'] : 0;
        $wms_key = json_encode($extra_params);
        $data['mid_code'] = $request['mid_code'];
        $data['api_param_json'] = $wms_key;
        $data['api_product'] = $request['api_product_flg'];
        $data['online_time'] = $pre_config['online_time'];
        $data['api_name'] = $request['api_name'];
        $data['erp_type']= $request['erp_type'];
        if ($data['api_product'] == 'bserp2' && $data['erp_type'] == 1) {
            $data['target_key'] = $request['target_key'];
            $data['customer_id'] = $request['customer_id'];
        }else{
            $data['target_key'] = '';
            $data['customer_id'] = '';
        }
        $this->begin_trans();
        $ret = $this->update($data, array('mid_code' => $request['mid_code']));
        $store_data = array();
        $store_arr = array();
        foreach ($request['store'] as $key => $value) {
            $data1['outside_code'] = $value['outside_code'];
            $data1['join_sys_code'] = $value['shop_store_code'];
            $data1['join_sys_type'] = isset($value['join_sys_type']) ? $value['join_sys_type'] : 1;
            $data1['outside_type'] = isset($value['join_sys_type']) ? $value['join_sys_type'] : 1;
            $data1['param_val1'] = isset($pre_config_arr['connection_mode']) ? $pre_config_arr['connection_mode'] : 0; //erp单据对接模式 1 单据模式 2 日报模式)
            if (empty($data1['outside_code'])) {
                $this->rollback();
                return $this->format_ret(-1, '', "仓库不能为空");
            }
            $data1['mid_code'] = $request['mid_code'];
            $store_data[] = $data1;
            $store_arr[] = $value['shop_store_code'];
        }
        foreach ($request['shop'] as $k => $val) {
            $data2['outside_code'] = $val['outside_code'];
            $data2['join_sys_code'] = $val['shop_shop_code'];
            $data2['join_sys_type'] = isset($val['join_sys_type']) ? $val['join_sys_type'] : 0;
            $data2['outside_type'] = isset($val['join_sys_type']) ? $val['join_sys_type'] : 0;
            $data2['shop_store_code'] = $val['shop_shop_code'];
            $data2['param_val1'] = isset($pre_config_arr['connection_mode']) ? $pre_config_arr['connection_mode'] : 0;
            if ((empty($data2['outside_code']) || empty($data2['shop_store_code'])) && $request['api_product'] != 'mes') {
                continue;
//                $this->rollback();
//                return $this->format_ret(-1, '', "店铺不能为空");
            }
            $data2['mid_code'] = $data['mid_code'];

            $shop_data[] = $data2;
            $shop_arr[] = $val['shop_shop_code'];
        }
        foreach ($request['custom'] as $k => $val) {
            $data3['outside_code'] = $val['outside_code'];
            $data3['join_sys_code'] = $val['custom_custom_code'];
            $data3['join_sys_type'] = isset($val['join_sys_type']) ? $val['join_sys_type'] : 2;
            $data3['outside_type'] = isset($val['join_sys_type']) ? $val['join_sys_type'] : 2;
            $data3['shop_store_code'] = $val['custom_custom_code'];
            $data3['param_val1'] = isset($pre_config_arr['connection_mode']) ? $pre_config_arr['connection_mode'] : 0;
            if (empty($data3['outside_code']) || empty($data3['shop_store_code'])) {
                continue;
//                $this->rollback();
//                $msg = "";
//                return $this->format_ret(-1, '', $msg . "不能为空");
            }
            $data3['mid_code'] = $data['mid_code'];

            $custom_data[] = $data3;
            $custom_arr[] = $val['custom_custom_code'];
        }
        $ret = parent::delete_exp('mid_api_join', array('mid_code' => $request['mid_code']));
        $ret_check = $this->check_store_shop($store_arr);
        $ret_check_1 = $this->check_store_shop($shop_arr,0);
        $ret_check_2 = $this->check_store_shop($custom_arr,2);
        if (!empty($ret_check['data'])) {
            $this->rollback();
            $msg = "";
            foreach ($ret_check['data'] as $val) {
                $msg .= $val['store_name'] . ",";
            }
            $msg = substr($msg, 0, -1);
            return $this->format_ret(-1, '', $msg . " 仓库已经被使用");
        }
        if (!empty($ret_check_1['data'])) {
            $this->rollback();
            $msg = "";
            foreach ($ret_check_1['data'] as $v) {
                $msg .= $v['shop_name'] . ",";
            }
            $msg = substr($msg, 0, -1);
            return $this->format_ret(-1, '', $msg . " 店铺已经被使用");
        }
        if (!empty($ret_check_2['data'])) {
            $this->rollback();
            $msg = "";
            foreach ($ret_check_2['data'] as $v) {
                $msg .= $v['custom_name'] . ",";
            }
            $msg = substr($msg, 0, -1);
            return $this->format_ret(-1, '', $msg . " 分销商已经被使用");
        }
        $ret = $this->insert_multi_exp('mid_api_join', $store_data);
        if(!empty($shop_data)){
             $ret = $this->insert_multi_exp('mid_api_join', $shop_data);
        }
       
        if(!empty($custom_data)){
            $ret = $this->insert_multi_exp('mid_api_join', $custom_data);
        }
        $this->commit();

        return $ret;
    }

    function get_type_data($id, $join_sys_type = 1) {
        $this->table = 'mid_api_join';
        $arr = $this->get_all(array('mid_code' => $id, 'join_sys_type' => $join_sys_type));
        return $arr;
    }

    function check_store_shop($store_arr, $join_sys_type = 1) {
        $store_str = "'" . implode("','", $store_arr) . "'";
        $sql = "select s.join_sys_code,b.store_name from mid_api_join s
        INNER JOIN base_store b ON s.join_sys_code = b.store_code
        where s.join_sys_type=:join_sys_type   and s.join_sys_code  in(:store_str)";
        $data = $this->db->get_all($sql, array(':join_sys_type' => $join_sys_type, ':store_str' => $store_str));
        return $this->format_ret(1, $data);
    }

    function get_select($config_id = 0, $type = 1) {
        $name = "";
        if($type == 1){
        $data = load_model("base/StoreModel")->get_purview_store();
            $name = 'store_code';
        }else if($type == 0){
            $data = load_model("base/ShopModel")->get_purview_shop();
            $name = 'shop_code';
        }else if($type == 2){
            $data = load_model("base/CustomModel")->get_custom_info();
            $name = 'custom_code';
        }
        $sql_mid = "select mid_code from mid_api_config where id=:config_id";
        $mid_code = $this->db->get_value($sql_mid, array(':config_id' => $config_id));
        $sql = "select join_sys_code from mid_api_join where join_sys_type=:type ";
        if ($config_id != 0) {
            $sql.=" AND mid_code<>".'"'.$mid_code.'"';
        }
        $mid = $this->db->get_all($sql, array(':type' => $type));
        $mid_arr = array();
        if (!empty($mid)) {
            foreach ($mid as $val) {
                $mid_arr[] = $val['join_sys_code'];
            }
        }
        foreach ($data as $key => $val) {
            if (in_array($val[$name], $mid_arr)) {
                unset($data[$key]);
            }
        }

        return $this->format_ret(1, $data);
    }
    function get_select_shop($config_id = 0, $type = 0) {
        $data = load_model("base/ShopModel")->get_purview_shop();
        $sql_mid = "select mid_code from mid_api_config where id=:config_id";
        $mid_code = $this->db->get_value($sql_mid, array(':config_id' => $config_id));
        $sql = "select join_sys_code from mid_api_join where join_sys_type=:type ";
        if ($config_id != 0) {
            $sql.=" AND mid_code<>'{$mid_code}'";
        }
        $mid = $this->db->get_all($sql, array(':type' => $type));
        $mid_arr = array();
        if (!empty($mid)) {
            foreach ($mid as $val) {
                $mid_arr[] = $val['join_sys_code'];
            }
        }
        foreach ($data as $key => $val) {
            if (in_array($val[$name.'code'], $mid_arr)) {
                unset($data[$key]);
            }
        }

        return $this->format_ret(1, $data);
    }

    function get_mid_api_config_by_api_product($api_product) {
        
        
        $sql = "select * from mid_api_config where api_product=:api_product";
        $sql_values = array(
            ':api_product' => $api_product,
        );
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as $key => &$val) {
            $val['api_config'] = $this->match_config($val);
        }
        
        
        return $data;
    }
    /**
     * 获取对接数据
     * @param type $mid_code 对接配置代码
     * @param type $join_sys_type 对接类型1为仓库，0为店铺
     * @return type
     */
    function get_join_data($mid_code,$join_sys_type = 1) {//对接仓库
        $sql = "select * from mid_api_join where mid_code=:mid_code AND join_sys_type=:join_sys_type ";
        $sql_values = array(
            ':mid_code' => $mid_code,
            ':join_sys_type' => $join_sys_type,
        );
        $data = $this->db->get_all($sql, $sql_values);
        return $data;
    }
    
    function test_api($request){
//        $params = array('id'=>$confg_id);
//        $ret = $this->get_row($params);
        $api_conf = $request['api_param_json'];
        $test_fun = strtolower($request['api_product'])."_test";
        if(empty($api_conf)){
            return $this->format_ret(-1,'','请先完成参数配置！');
        }
        if(!method_exists($this, $test_fun)) {
            return $this->format_ret(-1,'','暂未实现接口测试！');
        }
        return $this->$test_fun($api_conf) ;
    }

    function bserp2_test($api_conf) {
        require_lib('apiclient/BserpClient');
        $api_mod = new BserpClient($api_conf);
        $api_param = array(
            'page' => 1,
            'pageSize' => 10);
        $api_data = $api_mod->get_goods($api_param);

        if (isset($api_data['response']) && $api_data['response']['flag'] == 'success') {
            return $this->format_ret(1);
        } else {
            $msg = isset($api_data['response'][' message']) ? $api_data['response'][' message'] : '接口数据异常';
            return $this->format_ret(-1, '', $msg);
        }
    }
    
    function is_edit_onlinetime($mid_code){
        $is_edit_onlinetime = 1;
        $sql = "SELECT COUNT(1) FROM mid_order WHERE mid_code=:mid_code";
        $count = $this->db->get_value($sql, array(":mid_code" => $mid_code));
        if($count > 0) {
            $is_edit_onlinetime = 0;
        }
        return $is_edit_onlinetime;
    }
}
