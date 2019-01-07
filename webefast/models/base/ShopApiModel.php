<?php

require_model('tb/TbModel');

class ShopApiModel extends TbModel {

    /**
     * 读取店铺API列表, 根据来源
     * @param $source
     * @return array
     */
    function get_table() {
        return 'base_shop_api';
    }

    function get_shop_api($source) {
        $sql = "select a.api, a.shop_code,a.tb_shop_type, b.store_code, b.shop_name as shop_name
        from base_shop_api a
        inner join base_shop b on b.shop_code = a.shop_code
        where a.source =:source and b.status = 1";
        $data = $this->db->get_all($sql, array('source' => $source));

        foreach ($data as &$value) {
            $value['api'] = json_decode($value['api'], true);

            $data['api']['app'] = CTX()->get_app_conf('app_key');
            $data['api']['secret'] = CTX()->get_app_conf('app_secret');

            //多种key切换 2014-09-04
            /* if (isset($api['order_type']) && $api['order_type']) {
              $_top_app_keys = CTX()->get_app_conf('top_app_keys');
              if ($_top_app_keys && isset($_top_app_keys[$api['order_type']])) {
              $data['api']['app'] = $_top_app_keys[$api['order_type']]['top_app_key'];
              $data['api']['secret'] = $_top_app_keys[$api['order_type']]['top_app_secret'];
              }
              } */
        }

        return $data;
    }

    function get_shop_api_by_shop_code($shop_code) {
        $sql = "select a.api, a.shop_code,a.tb_shop_type, b.shop_name as shop_name
        from base_shop_api a
        inner join base_shop b on b.shop_code = a.shop_code
        where a.shop_code =:shop_code ";
        $data = $this->db->get_row($sql, array('shop_code' => $shop_code));

        $data['api'] = json_decode($data['api'], true);

        $data['api']['app'] = CTX()->get_app_conf('app_key');
        $data['api']['secret'] = CTX()->get_app_conf('app_secret');

        //多种key切换 2014-09-04
        /* if (isset($api['order_type']) && $api['order_type']) {
          $_top_app_keys = CTX()->get_app_conf('top_app_keys');
          if ($_top_app_keys && isset($_top_app_keys[$api['order_type']])) {
          $data['api']['app'] = $_top_app_keys[$api['order_type']]['top_app_key'];
          $data['api']['secret'] = $_top_app_keys[$api['order_type']]['top_app_secret'];
          }
          } */

        return $data;
    }

    function get_shop($shop_code) {
        $sql = "select a.api, a.shop_code,a.tb_shop_type,a.source, b.shop_name as shop_name
        from base_shop_api a
        inner join base_shop b on b.shop_code = a.shop_code
        where 1 and b.is_active = 1 and source='taobao'";
        $sql_values = array();
        if (!empty($shop_code)) {
            $sql .= " and a.shop_code =:shop_code";
            $sql_values = array('shop_code' => $shop_code);
        }
        $data = $this->db->get_all($sql, $sql_values);
        if (empty($data)) {
            return $this->format_ret(-1);
        }
        if (!empty($shop_code)) {
            $row = $data[0];
            $row['api'] = json_decode($row['api'], true);
            $ret_data = $row;
        } else {
            $ret_data = $data;
        }


        return $this->format_ret(1, $ret_data);
    }

    function get_shop_extra_params($shop_code) {
        //$shop_code
        $api_params = $this->db->getOne("select api from base_shop_api where shop_code=:shop_code",[':shop_code' => $shop_code]);
        $params_arr = array();
        if (!empty($api_params)) {
            $params_arr = json_decode($api_params, true);
        }
        return $this->format_ret(1, $params_arr);
    }

    function save_shop_extra_params($shop_code, $api_params, $kh_id = 0, $conf) {
        $ret = $this->get_shop_extra_params($shop_code);

        $row = $this->db->get_row("select shop_api_id,api from base_shop_api where shop_code=:shop_code", [':shop_code' => $shop_code]);
        $params = array();
        if (!empty($row)) {
            if (!empty($row['api'])) {
                $params = json_decode($row['api'], true);
            }
        } else {//创建参数
            $this->create_shop_api($shop_code);
        }


        $api_params = array_merge($params, $api_params);
        $ret = $this->update(array('api' => json_encode($api_params), 'kh_id' => $kh_id), array('shop_code' => $shop_code));
        if ($ret['status'] == '1') {
            $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $shop_code));
            $log_str = '网络店铺:' . $shop_name;
            foreach ($api_params as $key => $val) {
                if ($params[$key] != $val) {
                    if (isset($conf[$key]['show']) && $conf[$key]['show'] == '1') {
                        $log_str .= $key . '进行了修改;';
                    } else {
                        if (empty($params[$key])) {
                            $params[$key] = '空';
                        }
                        $log_str .= $key . '由' . $params[$key] . "修改为" . $val . ";";
                    }
                }
            }
            if ($log_str != '网络店铺:' . $shop_name) {
                $module = '网络店铺'; //模块名称
                $operate_type = '编辑'; //操作类型
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'operate_type' => $operate_type, 'operate_xq' => $log_str);
                load_model('sys/OperateLogModel')->insert($log);
            }
        }
        return $ret;
    }

    function create_shop_api($shop_code) {
        $row = $this->db->get_row("select shop_code, sale_channel_code  as source from base_shop where shop_code=:shop_code", [':shop_code' => $shop_code]);

        return $this->insert($row);
    }

    /**
     * 功能描述     验收批发退货单（影响库存）
     * @author     FBB
     * @date       2016-08-27
     * @param      必选:  string '$source'
     *             可选:  string json $shop_id
     *             可选:  string json $shop_code
     * @return      json {"shop_nick":"XXX","app_key":"XXX","app_secret":"XXX","access_token":"bXXX","refresh_token":"XXX"}
     */
    function api_get_apiinfo($params) {
        if (!isset($params['source']) || empty($params['source'])) {
            return $this->format_ret(-10001, '', '销售平台为必填项');
        }
        if ($params['source'] != 'aliexpress') {
            return $this->format_ret(-10002, '', '请求数据不存在');
        }
        $sql = "SELECT shop_api_id, shop_code, api FROM {$this->table} WHERE source = :source ";
        $sql_values = array(":source" => $params['source']);
        if (!empty($params['shop_id'])) {
            $shop_id_str = deal_array_with_quote(json_decode($params['shop_id']));
            $sql .= " AND shop_api_id IN ({$shop_id_str}) ";
        }
        if (!empty($params['shop_code'])) {
            $shop_code_str = deal_array_with_quote(json_decode($params['shop_code']));
            $sql .= " AND shop_code IN ({$shop_code_str}) ";
        }
        $api_info_data = $this->db->get_all($sql, $sql_values);
        if (empty($api_info_data)) {
            return $this->format_ret(-10002, '', '请求数据不存在');
        }
        $api_info = array();
        foreach ($api_info_data as $key => $value) {
            $api_info[$key]['shop_id'] = $value['shop_api_id'];
            $api_info[$key]['shop_code'] = $value['shop_code'];
            $api_info[$key]['api'] = $value['api'];
        }
        return $api_info;
    }

    /**
     * 批量获取店铺参数
     * @param $shop_code_arr
     * @return array
     */
    function get_shop_api_info($shop_code_arr) {
        $sql_value = array();
        $shop_code_str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_value);
        $sql = "SELECT * FROM base_shop_api WHERE shop_code IN({$shop_code_str})";
        $ret = $this->db->get_all($sql, $sql_value);
        $result = array();
        if (!empty($ret)) {
            foreach ($ret as &$value) {
                $value['api_arr'] = json_decode($value['api'], true);
                $result[$value['shop_code']] = $value;
            }
            return $this->format_ret('1', $result, '');
        }
        return $this->format_ret('-1', '', '');
    }

    //修改base_shop_api的nick值,仅限淘宝和京东
    function update_nick($shop_code, $nick) {
        return $this->update(array('nick' => $nick), array('shop_code' => $shop_code));
    }

}
