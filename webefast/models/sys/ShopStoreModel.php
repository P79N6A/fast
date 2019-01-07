<?php

/**
 * 短信模板 相关业务
 *
 * @author dfr
 */
require_model('tb/TbModel');

class ShopStoreModel extends TbModel {

    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'sys_api_shop_store';
    }

    public function get_erp_store_code($type = '') {
        $data = load_model("base/StoreModel")->get_purview_store();
        $sql = "select shop_store_code,outside_code from sys_api_shop_store where p_type = 0 and outside_type = 1";
        $erp_store = $this->db->get_all($sql);
        $data_store_arr = array();
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data_store_arr[$val['store_name']] = $val['store_code'];
            }
        }
        $i = 0;
        $erp_store_arr = array();
        if (!empty($erp_store)) {
            foreach ($erp_store as $key => $val) {
                if (!in_array($val['shop_store_code'], $data_store_arr)) {
                    unset($erp_store[$key]);
                } else {
                    $store_name = array_search($val['shop_store_code'], $data_store_arr);
                    $erp_store_arr[$i]['store_code'] = $val['shop_store_code'] . '_' . $val['outside_code'];
                    if(!empty($type) && $type = 'erp') {
                        $erp_store_arr[$i]['store_name'] = $store_name . "[BSERP-" . $val['outside_code'] . "]";
                    } else {
                        $erp_store_arr[$i]['store_name'] = $store_name . "[BS3000J-" . $val['outside_code'] . "]";
                    }
                    $i++;
                }
            }
        }
        return $erp_store_arr;
    }

    function get_type_data($id, $p_type, $shop_store_type = 1) {
        $arr = $this->get_all(array('p_id' => $id, 'p_type' => $p_type, 'shop_store_type' => $shop_store_type));
        return $arr;
    }

    function get_select_store($wms_config_id = 0, $p_type = 1) {
        $data = load_model("base/StoreModel")->get_purview_store();

        $sql = "select shop_store_code from sys_api_shop_store where p_type=$p_type and shop_store_type=1 ";
        if ($wms_config_id != 0) {
            $sql.=" AND p_id<>'{$wms_config_id}'";
        }
        $wms_store = $this->db->get_all($sql);
        $wms_store_arr = array();
        if (!empty($wms_store)) {
            foreach ($wms_store as $val) {
                $wms_store_arr[] = $val['shop_store_code'];
            }
        }
        foreach ($data as $key => $val) {
            if (in_array($val['store_code'], $wms_store_arr)) {
                unset($data[$key]);
            }
        }

        return $this->format_ret(1, $data);
    }

    function get_select_shop($config_id = 0, $p_type = 0) {
        $data = load_model("base/ShopModel")->get_purview_shop();

        $sql = "select shop_store_code from sys_api_shop_store where p_type=$p_type and shop_store_type=0 ";
        if ($config_id != 0) {
            $sql.=" AND p_id<>'{$config_id}'";
        }
        $erp_shop = $this->db->get_all($sql);
        $erp_shop_arr = array();
        if (!empty($erp_shop)) {
            foreach ($erp_shop as $val) {
                $erp_shop_arr[] = $val['shop_store_code'];
            }
        }
        foreach ($data as $key => $val) {
            if (in_array($val['shop_code'], $erp_shop_arr)) {
                unset($data[$key]);
            }
        }

        return $this->format_ret(1, $data);
    }

    function check_store($store_arr, $p_type = 1) {
        if(empty($store_arr)){
                return $this->format_ret(1, array());
        }
        
        $sql_values = array();
        $store_str = $this->arr_to_in_sql_value($store_arr, 'shop_store_code', $sql_values);
        
        $sql = "select s.shop_store_code,b.store_name from sys_api_shop_store s
        INNER JOIN base_store b ON s.shop_store_code = b.store_code
        where s.p_type=$p_type  and shop_store_type=1 and s.shop_store_code  in($store_str)";
        $data = $this->db->get_all($sql,$sql_values);
        return $this->format_ret(1, $data);
    }

    function check_shop($shop_arr, $p_type = 0) {
       if(empty($shop_arr)){
                return $this->format_ret(1, array());
        }
        $sql_values = array();
        $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_store_code', $sql_values);
        $sql = "select s.shop_store_code,b.shop_name from sys_api_shop_store s
    	INNER JOIN base_shop b ON s.shop_store_code = b.shop_code
    	where s.p_type=$p_type  and shop_store_type=0 and s.shop_store_code  in($shop_str)";
        $data = $this->db->get_all($sql,$sql_values);
        return $this->format_ret(1, $data);
    }

    /**
     * 验证绑定的店铺
     * @param $shop_arr
     * @param int $p_type
     * @param string $config_id
     * @return array
     */
    function check_bind_shop($shop_arr, $p_type = 0,$config_id='') {
        if(empty($shop_arr)){
            return $this->format_ret(1, array());
        }
        $sql_values = array();
        $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_store_code', $sql_values);
        $sql = "select s.shop_store_code,b.shop_name from sys_api_shop_store s
    	INNER JOIN base_shop b ON s.shop_store_code = b.shop_code
    	where s.p_type=$p_type  and shop_store_type=0 and s.shop_store_code  in({$shop_str})";
        if (!empty($config_id)) {
            $sql .= ' AND s.p_id<>:p_id';
            $sql_values[':p_id'] = $config_id;
        }
        $data = $this->db->get_all($sql,$sql_values);
        return $this->format_ret(1, $data);
    }

    /**
     * 验证绑定的仓库
     * @param $store_arr
     * @param int $p_type
     * @param string $config_id
     * @return array
     */
    function check_bind_store($store_arr, $p_type = 1,$config_id='') {
        if(empty($store_arr)){
            return $this->format_ret(1, array());
        }

        $sql_values = array();
        $store_str = $this->arr_to_in_sql_value($store_arr, 'shop_store_code', $sql_values);

        $sql = "select s.shop_store_code,b.store_name from sys_api_shop_store s
        INNER JOIN base_store b ON s.shop_store_code = b.store_code
        where s.p_type=$p_type  and shop_store_type=1 and s.shop_store_code  in({$store_str})";
        if (!empty($config_id)) {
            $sql .= ' AND s.p_id<>:p_id';
            $sql_values[':p_id'] = $config_id;
        }
        $data = $this->db->get_all($sql,$sql_values);
        return $this->format_ret(1, $data);
    }

    function get_alls($type, $outside_type) {
        $arr = $this->get_all(array('shop_store_type' => $type, 'outside_type' => $outside_type));
        return $arr;
    }

    /**
     * 修改纪录
     */
    function update($supplier, $id) {
        $status = $this->valid($supplier, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

//        $ret = $this->get_row(array('erp_config_id' => $id));
//        if ($supplier['erp_config_name'] != $ret['data']['erp_config_name']) {
//            $ret = $this->is_exists($supplier['erp_config_name'], 'erp_config_name');
//            if ($ret['status'] > 0 && !empty($ret['data'])) {
//                return $this->format_ret('erp_config_error_unique_name');
//            }
//        }

        $ret = parent::update($supplier, array('shop_store_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'shop_store_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 添加新纪录
     */
    function insert($supplier) {
        $status = $this->valid($supplier);
        if ($status < 1) {
            return $this->format_ret($status);
        }

//        $ret = $this->is_exists($supplier['supplier_code']);
//        if ($ret['status'] > 0 && !empty($ret['data'])) {
//            return $this->format_ret('sms_supplier_error_unique_code');
//        }
//        $ret = $this->is_exists($supplier['erp_config_name'], 'erp_config_name');
//        if ($ret['status'] > 0 && !empty($ret['data'])) {
//            return $this->format_ret('erp_config_error_unique_name');
//        }

        return parent::insert($supplier);
    }

    /**
     * 删除记录
     */
    function delete_store_config($id, $p_type) {
        $ret = parent :: delete(array('p_id' => $id, 'p_type' => $p_type));
        return $ret;
    }

    /**
     * 是否对接外部仓储
     * @return type
     */
    function is_wms_store($store_code) {
        static $store_arr = NULL;
        if (!isset($store_arr[$store_code])) {
            $sql = "select wms_system_code from wms_config w
                INNER JOIN sys_api_shop_store s ON s.p_id=w.wms_config_id
                where  s.p_type=1 AND s.shop_store_code='{$store_code}' AND s.shop_store_type=1";
            $row = $this->db->get_row($sql);
            $data = false;
            if (!empty($row)) {
                $data = $row['wms_system_code'];
            }
            $store_arr[$store_code] = $data;
        }
        return $store_arr[$store_code];
    }

    function get_no_effect_inv($store_code) {
        static $no_effect_inv_arr = NULL;
        if (!isset($no_effect_inv_arr[$store_code])) {
            $sql = "select wms_system_code,wms_params from wms_config w
                INNER JOIN sys_api_shop_store s ON s.p_id=w.wms_config_id
                where  s.p_type=1 AND s.shop_store_code='{$store_code}' AND s.shop_store_type=1";
            $row = $this->db->get_row($sql);
            if(!empty($row)){
                $param_data = empty($row['wms_params']) ? array() : json_decode($row['wms_params'], true);
                 $wms_conf = require_conf('sys/wms');
                 $effect_inv = 0;
                if (isset($param_data['effect_inv_type'])) {
                   $effect_inv = $param_data['effect_inv_type'];
                } else {
                   $effect_inv= isset($wms_conf[$row['wms_system_code']]['effect_inv_type']) && $wms_conf[$row['wms_system_code']]['effect_inv_type'] == 1 ?
                            1 : 0;
                }
                 $no_effect_inv_arr[$store_code] = array();
                if($effect_inv==0){
                    $no_effect_inv_arr[$store_code] = $wms_conf[$row['wms_system_code']]['not_effect_inv'];
                }
            }else{
                 $no_effect_inv_arr[$store_code] = array();
            }
            $affect_inv = $this->erp_no_affect_inv($store_code);
            if ($affect_inv > 0){
                $no_effect_inv_arr[$store_code] = 1;
            }
        }
        return $no_effect_inv_arr[$store_code];
    }
    function erp_no_affect_inv($store_code){
        $sql = "select count(1) from erp_config e
                INNER JOIN sys_api_shop_store s ON s.p_id=e.erp_config_id
                where  s.p_type=0 AND s.shop_store_code='{$store_code}' AND s.shop_store_type=1 and s.update_stock=1";
        $affect_inv = $this->db->get_value($sql);
        return $affect_inv;
    }

    function get_store_for_wms() {
        //权限暂时去掉
//       $purview_store = load_model('sys/ShopStoreModel')->get_purview_store();
//       $store_arr = array();
//       foreach($purview_store as $val){
//           $store_arr[] = $val['store_code'];
//       }
//       if(empty($store_arr)){
//          return array(); 
//       }
        //$store_code_str = "'".implode("','", $store_arr)."'";
        // AND s.shop_store_code in ({$store_code_str}) 

        $sql = "select s.shop_store_code from wms_config w
                INNER JOIN sys_api_shop_store s ON s.p_id=w.wms_config_id
                where  s.p_type=1 AND s.shop_store_type=1";
        $data = $this->db->get_all($sql);
        $store_data = array();
        if (!empty($data)) {
            foreach ($data as $val) {
                $store_data[] = $val['shop_store_code'];
            }
        }
        return $store_data;
    }

}
