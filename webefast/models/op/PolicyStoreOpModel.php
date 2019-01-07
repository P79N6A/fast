<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

/**
 * 仓库适配策略
 */
class PolicyStoreOpModel extends TbModel {

    private $sell_record;
    private $sell_record_detail;
    private $shop_store;
    private $sku_num_data = array();
    private $sku_data = array();
    private $store_sku_data = array();

    function set_plicy_store_code(&$sell_record, &$sell_record_detail) {
        $this->sell_record = &$sell_record;
        $this->sell_record_detail = &$sell_record_detail;
        $ret = $this->get_store_by_shop_code($sell_record['shop_code']);

        if ($ret['status'] < 1) {
            return $ret;
        }


        $ret_auth = load_model('sys/SysAuthModel')->get_cp_code();
        if (strtolower($ret_auth['data']) == 'efast5_standard') {
            $this->set_sell_record_store($this->shop_store['send_store_code']);
            return $this->format_ret(1);
        }


        $ret_param = load_model('sys/SysParamsModel')->get_val_by_code(array('is_policy_store'));
        //不开启默认店铺参数
        if ($ret_param['is_policy_store'] == 0) {
            $this->set_sell_record_store($this->shop_store['send_store_code']);
            return $this->format_ret(1);
        }



        if (empty($this->shop_store['send_store_code']) && empty($this->shop_store['store_all'])) {
            return $this->format_ret(-1, '', '店铺需要设置发货仓和库存来源仓');
        }

        if (empty($this->shop_store['store_all'])) {
            $this->set_sell_record_store($this->shop_store['send_store_code']);
            return $this->format_ret(1);
        }

        $is_find = $this->set_policy_store();

        if ($is_find === FALSE) {//不满足
            $this->set_sell_record_store($this->shop_store['send_store_code']);
        }


        return $this->format_ret(1);
    }

    private function set_sell_record_store($store_code) {
        $this->sell_record['store_code'] = $store_code;
    }

    private function set_policy_store() {
        $this->store_sku_data = array();
        $this->set_detail_sku_num();
        $is_find = FALSE;
        $inv_store = ''; //库存满足的仓库
        foreach ($this->shop_store['store_all'] as $store_code) {
            $check_status = $this->check_store($store_code);
            if ($check_status == 1) {
                $this->set_sell_record_store($store_code);
                $is_find = true;
                break;
            } else if ($check_status == 0 && empty($inv_store)) {
                $inv_store = $store_code;
            }
        }

        //取库存满足仓库
        if ($is_find === FALSE && !empty($inv_store)) {
            $is_find = TRUE;
            $this->set_sell_record_store($inv_store);
        }

        return $is_find;
    }

    private function check_store($store_code) {
        $check_addr = $this->check_address($store_code);
        $check_inv = $this->check_store_inv($store_code);

        if ($check_addr && $check_inv) { //满足
            return 1;
        }
        if ($check_inv) {//库存满足
            return 0;
        }

        return -1;
    }

    private function check_address($store_code) {
        static $all_data = NULL;
        $sql_values = array(':store_code' => $store_code);
        if(!isset($all_data[$store_code])){
            $sql = " select count(1) from op_policy_store_area where store_code=:store_code  ";
            $all_data[$store_code] = $this->db->get_value($sql, $sql_values);
        }
        
        if ($all_data[$store_code] == 0) {
            return true;
        }
        $area_city_arr = array('442000000000', '441900000000');

        $area_id = in_array($this->sell_record['receiver_city'],$area_city_arr)&&empty($this->sell_record['receiver_district'])
                ?$this->sell_record['receiver_city']:$this->sell_record['receiver_district'];
        
        if (!empty($area_id)) {
            $sql = "select area_id from op_policy_store_area where store_code=:store_code AND area_id=:area_id";
            $sql_values[':area_id'] = $area_id;
            $check = $this->db->get_value($sql, $sql_values);
            if (!empty($check)) {
                return true;
            }
        } else { //城市全选
            $sql = "select count(1) from base_area  where parent_id=:parent_id  "
                    . " AND id not in(select area_id from op_policy_store_area where store_code=:store_code) ";
            $sql_values[':parent_id'] = $this->sell_record['receiver_city'];

            $check = $this->db->get_value($sql, $sql_values);
            if ($check == 0) {
                return true;
            }
        }

        return FALSE;
    }

    private function check_store_inv($store_code) {
        $sku_str = "'" . implode("','", $this->sku_data) . "'";
        $this->store_sku_data[$store_code] = array();
        $sql = "select sku,stock_num,lock_num,safe_num from goods_inv where store_code=:store_code AND sku IN({$sku_str})";
        $data = $this->db->get_all($sql, array(':store_code' => $store_code));
        $ret_param = load_model('sys/SysParamsModel')->get_val_by_code(array('is_policy_store_safe_inv'));
        $ret_param['is_policy_store_safe_inv'] = isset($ret_param['is_policy_store_safe_inv'])?$ret_param['is_policy_store_safe_inv']:0;
        
        foreach ($data as $val) {
            $num = $val['stock_num'] - $val['lock_num'];
            //安全库存处理
            if($ret_param['is_policy_store_safe_inv']==1){
                $num = $num-$val['safe_num'];
            }

            if ($num >= $this->sku_num_data[$val['sku']]) {
                $this->store_sku_data[$store_code][] = $val['sku'];
            }
        }
        if (count($this->sku_data) == count($this->store_sku_data[$store_code])) {
            return true;
        }
        return false;
    }

    private function set_detail_sku_num() {
        $this->sku_num_data = array();
        $this->sku_data = array();
        foreach ($this->sell_record_detail as $val) {
            $this->sku_num_data[$val['sku']] = $val['num'];
            $this->sku_data[] = $val['sku'];
        }
    }

    private function get_store_by_shop_code($shop_code) {
        static $store_data = NULL;
        if (!isset($store_data[$shop_code])) {
            $sql = "select send_store_code,stock_source_store_code  from base_shop where shop_code=:shop_code ";
            $row = $this->db->get_row($sql, array(':shop_code' => $shop_code));
            $row['store_all'] = array();
            if (!empty($row['stock_source_store_code'])) {
                $store_arr = explode(",", $row['stock_source_store_code']);
                $row['store_all'] = $this->get_policy_store($store_arr);
            } else {
                return $this->format_ret(-1, '', $shop_code . '未设置库存来源仓');
            }

            $store_data[$shop_code] = $row;
        }
        $this->shop_store = $store_data[$shop_code];

        return $this->format_ret(1);
    }

    private function get_policy_store(&$store_arr) {
        $sql_values = array();
        $store_str = $this->arr_to_in_sql_value($store_arr, 'store_code', $sql_values);
        $sql = "select s.store_code from base_store s LEFT JOIN op_policy_store p ON s.store_code=p.store_code
            where s.store_code in({$store_str}) order by p.sort desc  ";
        $data = $this->db->get_all($sql,$sql_values);
        $op_store_arr = array();
        if (!empty($data)) {
            foreach ($data as $val) {
                $op_store_arr[] = $val['store_code'];
            }
        }
        return $op_store_arr;
    }

}
