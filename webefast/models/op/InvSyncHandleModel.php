<?php

require_model('tb/TbModel');
require_lang('inv');

/**
 * 库存同步策略逻辑计算
 */
class InvSyncHandleModel extends TbModel {

    //库存同步参数
    private $sync = array();
    private $sys_param = array();

    /**
     * @todo 获取店铺商品库存同步数量
     * 同步库存数 =（实物库存 - 实物锁定 - 缺货数 - 平台未转单数 + 在途数 - 安全库存）* 同步比例
     */
    function get_shop_sku_inv($shop_code, array $barcode_arr, $is_check_combo_diy = false) {
        $this->sync = array();
        $barcode_arr = array_unique($barcode_arr);

        //判断店铺是否存在库存同步策略并且已启用
        $ret_sync = $this->policy_status($shop_code);
        if ($ret_sync['status'] != 1) {
            return $ret_sync;
        }
        $this->sync = $ret_sync['data'];

        //系统参数
        $this->get_sys_param_cfg();

        //获取供货仓
        $ret_store = $this->get_sync_store($shop_code);
        if ($ret_store['status'] != 1) {
            return $ret_store;
        }
        //条码转小写
        foreach ($barcode_arr as &$b) {
            $b = strtolower($b);
        }

        $this->sync['store_code'] = $ret_store['data'];
        $data = $this->get_sku_inv($ret_store['data'], $barcode_arr);
        if (empty($data)) {
            return $this->format_ret(-1, array(), '商品无库存记录');
        }

        //获取商品同步比例
        $this->get_sync_ratio($data);

        //获取供货仓关联的所有店铺
        $shop_arr = $this->get_store_shop();
        //获取平台未转单商品数量
        $trade_barcode = $this->get_trade_num($shop_arr, $barcode_arr);
        if ($this->sys_param['inv_cal_lock'] == 1) {
            //获取锁定单中条码锁定数量
            $lock_record_num = $this->get_order_lock_num($ret_store['data'], $shop_code, $data);
        }

        $barcode_arr_key = array_flip($barcode_arr); //键值调换

        $barcode_inv = array();
        //条码预警
        $sku_arr = array_column($data, 'sku');
        $warn_info = $this->get_warn_sku_info('', $sku_arr);
        $warn_sku = array();
        if (!empty($warn_info)) {
            foreach ($warn_info as $warn) {
                $warn_sku[$warn['sku']] = $warn;
            }
        }
        if ($this->sync['sync_mode'] == 1) {
            //库存处理
            foreach ($data as $val) {
                $b_code = $val['barcode'];
                $num = 0; //计算同步比例的库存数
                $ori_num = 0; //未计算同步比例的库存数
                if (!is_null($val['stock_num'])) {
                    $num = $val['stock_num'] - $val['lock_num'] - $val['out_num'];
                    $num += $this->sync['is_road'] == 1 ? $val['road_num'] : 0;
                    $num -= $this->sync['is_safe'] == 1 ? $val['safe_num'] : 0;
                }
                $ori_num = $num;

                $trade_num = isset($trade_barcode[$b_code]) ? $trade_barcode[$b_code] : 0;
                $ori_num -= $trade_num;
                //条码预警
                if (isset($warn_sku[$val['sku']])) {
                    //预警数
                    $warn_sku_num = $warn_sku[$val['sku']]['warn_sku_val'];
                    $warn_sku_shop = $warn_sku[$val['sku']]['shop_code'];
                    if ($this->sys_param['anti_oversold'] == 1 && $this->sync['shop_code'] != $warn_sku_shop && $ori_num <= $warn_sku_num) {
                        $num = 0;
                    } else {
                        $num = floor($num * $val['sync_ratio']);
                        $num -= $trade_num;
                        $num = $num > 0 ? $num : 0;
                    }
                } else {
                    if ($this->sys_param['anti_oversold'] == 1 && $this->sync['shop_code'] != $this->sync['warn_goods_sell_shop'] && $ori_num <= $this->sync['warn_goods_val']) {
                        $num = 0;
                    } else {
                        $num = floor($num * $val['sync_ratio']);
                        $num -= $trade_num;
                        $num = $num > 0 ? $num : 0;
                    }
                }
                if ($this->sys_param['inv_cal_lock'] == 1) {
                    //加锁定单锁定数量
                    $num += isset($lock_record_num[$val['sku']]) ? $lock_record_num[$val['sku']] : 0;
                }


                $barcode_inv[$b_code] = array('sku' => $val['sku'], 'barcode' => $b_code, 'num' => $num, 'inv_update_time' => $val['record_time']);

                unset($barcode_arr_key[$b_code]);
            }
        } else {
            //获取barcode在数组中最后出现的位置
            $b_last_pos_arr = array();
            array_walk($data, function($v, $k) use (&$b_last_pos_arr) {
                $b_last_pos_arr[$v['barcode']] = $k;
            });

            //库存处理
            foreach ($data as $k => $val) {
                $b_code = $val['barcode'];
                $num = 0; //当前循环计算的库存数量（计算同步比例）
                $total_num = isset($barcode_inv[$b_code]) ? $barcode_inv[$b_code]['num'] : 0; //条码库存总数（计算同步比例）
                $ori_num = isset($barcode_inv[$b_code]) ? $barcode_inv[$b_code]['ori_num'] : 0; //原始库存数（未计算同步比例）

                if (!is_null($val['stock_num'])) {
                    $num = $val['stock_num'] - $val['lock_num'] - $val['out_num'];
                    $num += $this->sync['is_road'] == 1 ? $val['road_num'] : 0;
                    $num -= $this->sync['is_safe'] == 1 ? $val['safe_num'] : 0;
                }
                $ori_num += $num;
                $num = floor($num * $val['sync_ratio']);
                $total_num += $num;

                $is_last = $k === $b_last_pos_arr[$b_code]; //条码是否在$data中最后一次出现
                //如果条码为最后一次出现，则进行库存预警校验
                if ($is_last == TRUE) {
                    $trade_num = isset($trade_barcode[$b_code]) ? $trade_barcode[$b_code] : 0; //未转单商品数
                    $ori_num -= $trade_num;
                    $total_num -= $trade_num;

                    //条码预警
                    if (isset($warn_sku[$val['sku']])) {
                        $warn_sku_num = $warn_sku[$val['sku']]['warn_sku_val']; //预警数
                        $warn_sku_shop = $warn_sku[$val['sku']]['shop_code']; //预警店铺
                        $check_sku_oversold = $this->sys_param['anti_oversold'] == 1 && $this->sync['shop_code'] != $warn_sku_shop && $ori_num <= $warn_sku_num;
                        if ($check_sku_oversold == TRUE) {
                            $total_num = 0; //超过条码预警库存不销售，总库存数直接赋值0
                        } else {
                            $total_num = $total_num > 0 ? $total_num : 0;
                        }
                    } else {
                        $check_oversold = $this->sys_param['anti_oversold'] == 1 && $this->sync['shop_code'] != $this->sync['warn_goods_sell_shop'] && $ori_num <= $this->sync['warn_goods_val'];
                        if ($check_oversold == TRUE) {
                            $total_num = 0; //超过预警库存不销售，总库存数直接赋值0
                        } else {
                            $total_num = $total_num > 0 ? $total_num : 0;
                        }
                    }
                    if ($this->sys_param['inv_cal_lock'] == 1) {
                        //加锁定单锁定数量
                        $total_num += isset($lock_record_num[$val['sku']]) ? $lock_record_num[$val['sku']] : 0;
                    }

                    unset($barcode_inv[$b_code]['ori_num']);
                }

                if (array_key_exists($b_code, $barcode_inv)) {
                    $old_record_time = strtotime($barcode_inv[$b_code]['inv_update_time']);
                    $record_time = strtotime($val['record_time']);
                    $barcode_inv[$b_code]['inv_update_time'] = $record_time > $old_record_time ? $val['record_time'] : $barcode_inv[$b_code]['inv_update_time'];
                } else {
                    $barcode_inv[$b_code] = array('sku' => $val['sku'], 'barcode' => $b_code, 'inv_update_time' => $val['record_time']);
                }
                $barcode_inv[$b_code]['num'] = $total_num;
                if ($is_last != TRUE) {
                    $barcode_inv[$b_code]['ori_num'] = $ori_num;
                }

                unset($barcode_arr_key[$b_code]);
            }
        }

        if ($is_check_combo_diy === true) {
            $new_barcode = array_keys($barcode_arr_key); //获取其他未计算的条码
            $combo_barcode = $this->get_combo_barcode($new_barcode);
            if (!empty($combo_barcode)) {
                $ret_combo_diy = $this->get_combo_diy_inv($shop_code, $combo_barcode);
                if (!empty($ret_combo_diy['data'])) {
                    $barcode_inv = array_merge($barcode_inv, $ret_combo_diy['data']);
                }
            }
        }

        return $this->format_ret(1, $barcode_inv);
    }

    /**
     * 获取条码预警信息
     * @param $shop_code
     * @param $sku_arr
     * @return array|bool
     */
    function get_warn_sku_info($shop_code = '', $sku_arr) {
        $sql_value = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_value);
        $sql = "SELECT * FROM op_inv_sync_warn_sku WHERE 1 AND sync_code=:sync_code AND warn_sku_val>0 AND sku IN({$sku_str})";
        if (!empty($shop_code)) {
            $sql .= " AND shop_code=:shop_code";
            $sql_value[':shop_code'] = $shop_code;
        }
        $sql_value[':sync_code'] = $this->sync['sync_code'];
        $ret = $this->db->get_all($sql, $sql_value);
        return $ret;
    }

    /**
     * @todo 获取商品同步比例
     */
    private function get_sync_ratio(&$data) {
        $sku_arr = array_unique(array_column($data, 'sku'));
        $sku_str = deal_array_with_quote($sku_arr);
        $sql_values = array(':sync_code' => $this->sync['sync_code'], ':shop_code' => $this->sync['shop_code']);
        if ($this->sync['sync_mode'] == 1) {
            //取商品比例配置
            $sql = "SELECT sku,sync_ratio FROM op_inv_sync_goods_ratio
                    WHERE sync_code=:sync_code AND shop_code=:shop_code AND sku in({$sku_str})";
            $sku_ratio = $this->db->get_all($sql, $sql_values);

            $match_sku_ratio = array_column($sku_ratio, 'sync_ratio', 'sku');

            //取店铺比例配置
            $sql = "SELECT sync_ratio FROM op_inv_sync_shop_ratio
                    WHERE sync_code=:sync_code AND shop_code=:shop_code";
            $shop_ratio = $this->db->get_value($sql, $sql_values);

            foreach ($data as $key => &$val) {
                if (array_key_exists($val['sku'], $match_sku_ratio)) {
                    $val['sync_ratio'] = $match_sku_ratio[$val['sku']];
                } else {
                    $val['sync_ratio'] = $shop_ratio;
                }
            }
        } else if ($this->sync['sync_mode'] == 2) {
            $store_str = deal_array_with_quote($this->sync['store_code']);
            //取商品比例配置
            $sql = "SELECT CONCAT(store_code,sku) AS _key,sync_ratio FROM op_inv_sync_goods_ratio
                    WHERE sync_code=:sync_code AND shop_code=:shop_code AND store_code in({$store_str}) AND sku in({$sku_str})";
            $sku_ratio = $this->db->get_all($sql, $sql_values);

            $match_sku_ratio = array_column($sku_ratio, 'sync_ratio', '_key');

            //取仓库比例配置
            $sql = "SELECT store_code,sync_ratio FROM op_inv_sync_shop_ratio
                    WHERE sync_code=:sync_code AND shop_code=:shop_code AND store_code in({$store_str})";
            $store_ratio = $this->db->get_all($sql, $sql_values);
            $store_ratio = array_column($store_ratio, 'sync_ratio', 'store_code');
            foreach ($data as $key => &$val) {
                $_key = $val['store_code'] . $val['sku'];
                if (array_key_exists($_key, $match_sku_ratio)) {
                    $val['sync_ratio'] = $match_sku_ratio[$_key];
                } else if (array_key_exists($val['store_code'], $store_ratio)) {
                    $val['sync_ratio'] = $store_ratio[$val['store_code']];
                }
            }
        }
    }

    /**
     * @todo 获取SKU库存数据
     */
    private function get_sku_inv($store_arr, $barcode_arr) {
        $sku_data = load_model('prm/SkuModel')->convert_barcode($barcode_arr);
        $sku_data = $sku_data['data'];
        $sku_arr = array_unique(array_column($sku_data, 'sku'));
        if (empty($sku_arr)) {
            return array();
        }

        $sql_values = array();
        $store_code_str = $this->arr_to_in_sql_value($store_arr, 'store_code', $sql_values);
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $select = 'SUM(i.stock_num) AS stock_num,
                   SUM(i.lock_num) AS lock_num,
                   SUM(i.out_num) AS out_num,
                   b.sku,b.barcode,
                   MAX(i.record_time) AS record_time';
        //在途数和安全库存通过状态判断是否启用
        $select .= $this->sync['is_road'] == 1 ? ',SUM(i.road_num) AS road_num' : '';
        $select .= $this->sync['is_safe'] == 1 ? ',SUM(i.safe_num) AS safe_num' : '';
        if ($this->sync['sync_mode'] == 2) {
            $select .= ',i.store_code';
            $group_by = ' GROUP BY i.store_code,b.sku';
        } else {
            $group_by = ' GROUP BY b.sku';
        }
        $sql = "SELECT {$select} FROM goods_sku AS b LEFT JOIN goods_inv AS i ON i.sku=b.sku
                WHERE i.store_code IN({$store_code_str}) AND b.sku IN({$sku_str}) {$group_by}";
        $data = $this->db->get_all($sql, $sql_values);
        if (empty($data)) {
            return array();
        }

        $inv_data = array();
        if ($this->sync['sync_mode'] == 2) {
            foreach ($sku_data as $key => $val) {
                foreach ($data as $d) {
                    if ($val['sku'] == $d['sku']) {
                        $d['barcode'] = $key;
                        $inv_data[] = $d;
                    }
                }
            }
        } else {
            $data = load_model('util/ViewUtilModel')->get_map_arr($data, 'sku');
            foreach ($sku_data as $key => $val) {
                if (isset($data[$val['sku']])) {
                    $temp = $data[$val['sku']];
                    $temp['barcode'] = $key;
                    $inv_data[] = $temp;
                }
            }
        }

        return $inv_data;
    }

    /**
     * @todo 获取SKU库存数据(停用)
     */
    private function get_sku_data($table, $is_gb = 0) {
        $sql_values = array();
        $store_code_str = $this->arr_to_in_sql_value($this->sync['store_code'], 'store_code', $sql_values);
        $barcode_str = $this->arr_to_in_sql_value($this->sync['barcode'], 'barcode', $sql_values);

        $select = 'SUM(i.stock_num) AS stock_num,
                   SUM(i.lock_num) AS lock_num,
                   SUM(i.out_num) AS out_num,
                   b.sku,
                   MAX(i.record_time) AS record_time,';
        $select .= $is_gb == 1 ? "b.gb_code AS barcode" : 'b.barcode';
        //在途数和安全库存通过状态判断是否启用
        $select .= $this->sync['is_road'] == 1 ? ',SUM(i.road_num) AS road_num' : '';
        $select .= $this->sync['is_safe'] == 1 ? ',SUM(i.safe_num) AS safe_num' : '';
        if ($this->sync['sync_mode'] == 2) {
            $select .= ',i.store_code';
            $group_by = ' GROUP BY i.store_code,b.sku';
        } else {
            $group_by = ' GROUP BY b.sku';
        }

        $sql = "SELECT {$select} FROM {$table} b
                LEFT JOIN goods_inv i ON i.sku=b.sku
                WHERE i.store_code IN({$store_code_str}) AND ";
        $sql .= $is_gb == 1 ? " b.gb_code IN({$barcode_str}) " : " b.barcode IN({$barcode_str}) ";
        $sql .= $group_by;

        $data = $this->db->get_all($sql, $sql_values);
        if (!empty($data)) {
            $barcode_exists = array_column($data, 'barcode');
            $this->sync['barcode'] = array_diff($this->sync['barcode'], $barcode_exists);
        }
        return $data;
    }

    /**
     * @todo 平台未转单商品数量
     */
    function get_trade_num($shop_arr, $barcode_arr) {
        $sql_values = array();
        $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
        $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
        $sql = "SELECT sum(d.num) AS num, d.goods_barcode FROM api_order o
                INNER JOIN api_order_detail d ON o.tid = d.tid
                WHERE o.status =1 AND  (o.is_change=0 OR o.is_change=-1)  AND d.goods_barcode IN({$barcode_str}) 
                AND d.goods_barcode IS NOT NULL AND d.goods_barcode<>''
                AND o.shop_code IN($shop_str) GROUP BY d.goods_barcode";
        $data = $this->db->get_all($sql, $sql_values);
        $barcode_num = array();
        foreach ($data as $val) {
            $barcode = strtolower($val['goods_barcode']);
            $barcode_num[$barcode] = $val['num'];
        }
        return $barcode_num;
    }

    /**
     * @todo 获取锁定单数量
     */
    function get_order_lock_num($store_arr, $shop_code, $barcode_arr) {
        //小于预警 同步锁定值
        $sql_values = array(':shop_code' => $shop_code);
        $sku_arr = array_column($barcode_arr, 'sku');
        $store_str = $this->arr_to_in_sql_value($store_arr, 'store_code', $sql_values);
        $sql = "SELECT record_code FROM stm_stock_lock_record 
                WHERE order_status =1  AND lock_obj =1  
                AND shop_code=:shop_code AND store_code IN({$store_str})";
        $record_data = $this->db->get_all($sql, $sql_values);
        if (empty($record_data)) {
            return array();
        }

        $record_arr = array_column($record_data, 'record_code');

        $record_str = "'" . implode("','", $record_arr) . "'";
        $sql_d_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_d_values);
        $sql = "SELECT sum(d.available_num) AS num,s.sku FROM stm_stock_lock_record_detail d 
                INNER JOIN goods_sku s ON s.sku=d.sku
                WHERE d.record_code IN({$record_str}) AND s.sku IN({$sku_str}) GROUP BY d.sku";
        $data = $this->db->get_all($sql, $sql_d_values);

        $sku_lock_num = array_column($data, 'num', 'sku');
        return $sku_lock_num;
    }

    /**
     * @todo 获取供货仓关联的所有店铺
     */
    private function get_store_shop() {
        $store_code_str = deal_array_with_quote($this->sync['store_code']);
        $sql = "SELECT `code` FROM op_inv_sync_ss_relation WHERE type=1 AND sync_code IN(SELECT sync_code FROM op_inv_sync_ss_relation WHERE type=2 AND `code` in({$store_code_str}))";
        $ret = $this->db->get_all($sql);
        $shop_arr = array_column($ret, 'code');
        return $shop_arr;
    }

    /**
     * @todo 根据店铺获取供货仓
     */
    function get_sync_store($shop_code) {
        $sql = 'SELECT `code` FROM op_inv_sync_ss_relation WHERE `type`=2 AND sync_code in(SELECT sync_code FROM op_inv_sync_ss_relation WHERE `type`=1 AND `code`=:shop_code)';
        $store = $this->db->get_all($sql, array(':shop_code' => $shop_code));
        if (empty($store)) {
            return $this->format_ret(-1, array(), '供货仓不存在');
        }
        $store = array_column($store, 'code');
        return $this->format_ret(1, $store);
    }

    /**
     * @todo 获取套餐条码
     */
    private function get_combo_barcode($barcode_arr) {
        if (empty($barcode_arr)) {
            return array();
        }
        $sql_values = array();
        $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
        $sql = " SELECT barcode FROM  goods_combo_barcode WHERE barcode IN($barcode_str); ";
        $data = $this->db->get_all($sql, $sql_values);
        $combo_barcode = array_column($data, 'barcode');
        return $combo_barcode;
    }

    /**
     * @todo 获取套餐商品库存
     */
    function get_combo_diy_inv($shop_code, array $barcode_arr) {
        if (empty($this->sync) || $this->sync['shop_code'] != $shop_code) {
            $this->sync = array();
            //判断店铺是否存在库存同步策略并且已启用
            $ret_sync = $this->policy_status($shop_code);
            if ($ret_sync['status'] != 1) {
                return $ret_sync;
            }
            $this->sync = $ret_sync['data'];
            //防超卖预警开启状态
            $this->get_sys_param_cfg();
        }

        $ret_store = $this->get_sync_store($shop_code);
        if ($ret_store['status'] != 1) {
            return $ret_store;
        }

        $store_arr = &$ret_store['data'];
        $barcode_inv_data = array();
        $this->sync['store_code'] = $store_arr;
        $shop_arr = $this->get_store_shop();
        $trade_barcode = $this->get_trade_num($shop_arr, $barcode_arr);

        foreach ($barcode_arr as $barcode) {
            $barcode_inv = array();
            foreach ($store_arr as $store_code) {
                $new_barcode_inv = $this->get_combo_diy_num($store_code, $barcode);
                if (!empty($new_barcode_inv)) {
                    $this->merge_combo_diy_inv($barcode_inv, $new_barcode_inv);
                }
            }

            if (empty($barcode_inv)) { //找不到不同步
                continue;
            }
            if (isset($trade_barcode[$barcode])) {
                $barcode_inv['num'] = $barcode_inv['num'] - $trade_barcode[$barcode];
            }

            if ($barcode_inv['num'] < 0) {
                $barcode_inv['num'] = 0;
            }
            $barcode_inv_data[$barcode] = $barcode_inv;
        }
        return $this->format_ret(1, $barcode_inv_data);
    }

    /**
     * @todo 合并套餐商品同步数量，得到套餐同步数
     */
    private function merge_combo_diy_inv(&$barcode_inv, $new_barcode_inv) {
        if (!empty($barcode_inv)) {
            $barcode_inv['num'] += $new_barcode_inv['num'];
            $old_record_time = strtotime($barcode_inv['inv_update_time']);
            $record_time = strtotime($new_barcode_inv['inv_update_time']);
            if ($old_record_time < $record_time) {
                $barcode_inv['record_time'] = $record_time;
            }
        } else {
            $barcode_inv = $new_barcode_inv;
        }
    }

    /**
     * @todo 获取单个仓库套餐同步数量
     */
    function get_combo_diy_num($store_code, $barcode) {
        static $combo_diy_inv_data = null;

        if (isset($combo_diy_inv_data[$store_code][$barcode])) {
            return $combo_diy_inv_data[$store_code][$barcode];
        }

        $sql = "SELECT d.num,d.sku FROM goods_combo_diy d
                INNER JOIN  goods_combo_barcode b ON b.sku=d.p_sku
                WHERE b.barcode = :barcode ";
        $data = $this->db->get_all($sql, array(':barcode' => $barcode));
        $sku_arr = array_column($data, 'sku');
        $combo_diy_arr = array_column($data, 'num', 'sku');

        $sku_str = deal_array_with_quote($sku_arr);
        $sql = "SELECT stock_num,lock_num,out_num,road_num,safe_num,record_time,sku,store_code FROM goods_inv WHERE store_code='{$store_code}' AND sku IN({$sku_str})";
        $data = $this->db->get_all($sql);
        $this->get_sync_ratio($data);

        $barcode_data = array();
        foreach ($data as $val) {
            $num = $val['stock_num'] - $val['lock_num'] - $val['out_num'];
            $num += $this->sync['is_road'] == 1 ? $val['road_num'] : 0;
            $num -= $this->sync['is_safe'] == 1 ? $val['safe_num'] : 0;
            $i = (string) $val['sku'];
            if ($num > 0) {
                $num = floor($num / $combo_diy_arr[$i]);
            }
            if ($this->sys_param['anti_oversold'] == 1 && $this->sync['shop_code'] != $this->sync['warn_goods_sell_shop'] && $num <= $this->sync['warn_goods_val']) {
                $num = 0;
            } else {
                $num *= $val['sync_ratio'];
                $num = floor($num);
            }

            unset($combo_diy_arr[$i]);
            if (!empty($barcode_data)) {
                if ($barcode_data['num'] > $num) {
                    $barcode_data['num'] = $num;
                }
                $old_record_time = strtotime($barcode_data['inv_update_time']);
                $record_time = strtotime($val['record_time']);
                if ($old_record_time < $record_time) {
                    $barcode_data['inv_update_time'] = $val['record_time'];
                }
            } else {
                $barcode_data = array('sku' => $val['sku'], 'barcode' => $barcode, 'num' => $num, 'inv_update_time' => $val['record_time']);
            }
        }

        //存在没有的SKU
        if (!empty($combo_diy_arr)) {
            return array();
        }
        $combo_diy_inv_data[$store_code][$barcode] = $barcode_data;
        return $barcode_data;
    }

    /**
     * @todo 判断店铺是否存在策略|是否启用
     */
    function policy_status($shop_code) {
        static $sync_policy = NULL;
        $status = 1;
        $msg = '';
        if ($sync_policy == NULL || $sync_policy['shop_code'] != $shop_code) {
            $sql = "SELECT os.sync_code,os.sync_name,os.sync_mode,os.is_road,os.is_safe,os.status,os.warn_goods_val,os.warn_goods_sell_shop,ss.code AS shop_code FROM op_inv_sync os LEFT JOIN op_inv_sync_ss_relation ss ON os.sync_code=ss.sync_code WHERE type=1 AND code=:shop_code";
            $sync_policy = $this->db->get_row($sql, array(':shop_code' => $shop_code));
            if (empty($sync_policy)) {
                $status = -1;
                $sync_policy = array();
                $msg = '库存策略不存在';
            } else if ($sync_policy['status'] == 0) {
                $status = -1;
                $sync_policy = array();
                $msg = '库存策略未启用';
            }
        }
        return $this->format_ret($status, $sync_policy, $msg);
    }

    function get_sys_param_cfg() {
        $param_code = array(
            'anti_oversold',
            'inv_cal_lock',
        );
        $this->sys_param = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
    }

}
