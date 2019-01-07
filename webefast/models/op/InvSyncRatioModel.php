<?php

#author FBB

require_model('tb/TbModel');
require_lib('util/oms_util', true);

class InvSyncRatioModel extends TbModel {

    private $shop_table = 'op_inv_sync_shop_ratio';
    private $goods_table = 'op_inv_sync_goods_ratio';

    function get_by_global_page($filter) {
        $sql_values = array();
        $sql_join = '';
        $select = 'ssr.*';
        if (isset($filter['type']) && $filter['type'] == 'goods_ratio') {
            $sql_join .= " LEFT JOIN op_inv_sync_goods_ratio sgr ON ssr.sync_code = sgr.sync_code
                           AND ssr.shop_code = sgr.shop_code
                           AND ssr.store_code = sgr.store_code AND sgr.sku=:sku ";
            $select .= ",sgr.sku,sgr.sync_ratio AS goods_sync_ratio";
            $sql_values[':sku'] = $filter['sku'];
        }
        $sql_main = "FROM {$this->shop_table} ssr {$sql_join} WHERE 1";
        //策略代码
        if (isset($filter['sync_code']) && $filter['sync_code'] != '') {
            $sql_main .= " AND ssr.sync_code = :sync_code ";
            $sql_values[':sync_code'] = $filter['sync_code'];
        }

        $sql_main .= " ORDER BY ssr.shop_code DESC";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            $shop = load_model('base/ShopModel')->get_by_code($val['shop_code']);
            $val['shop_name'] = $shop['data']['shop_name'];
            $val['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($shop['data']['sale_channel_code']);
            if (isset($filter['type']) && $filter['type'] == 'goods_ratio') {
                $val['shop_sync_ratio'] = (!empty($val['goods_sync_ratio']) && $val['sku'] == $filter['sku']) ? $val['goods_sync_ratio'] * 100 : $val['sync_ratio'] * 100;
            } else {
                $val['shop_sync_ratio'] = $val['sync_ratio'] * 100;
            }
            $val['shop_sync_ratio_tmp'] = $val['shop_sync_ratio'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_store_page($filter) {
        $sql_values = array();
        $sql_join = '';
        $sql_join .= ' LEFT JOIN base_store bs ON ssr.store_code=bs.store_code ';
        $select = ' ssr.*,bs.store_property,bs.store_name';
        //商品配置比例页面
        if (isset($filter['type']) && $filter['type'] == 'goods_ratio') {
            $sql_join .= " LEFT JOIN op_inv_sync_goods_ratio sgr ON ssr.sync_code = sgr.sync_code
                           AND ssr.shop_code = sgr.shop_code
                           AND ssr.store_code = sgr.store_code AND sgr.sku=:sku ";
            $select .= ",sgr.sku,sgr.sync_ratio AS goods_sync_ratio";
            $sql_values[':sku'] = $filter['sku'];
        }
        $sql_main = "FROM {$this->shop_table} ssr {$sql_join} WHERE 1";
        //策略代码
        if (isset($filter['sync_code']) && $filter['sync_code'] != '') {
            $sql_main .= " AND ssr.sync_code = :sync_code ";
            $sql_values[':sync_code'] = $filter['sync_code'];
        }
        //店铺代码
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_main .= " AND ssr.shop_code = :shop_code ";
            $sql_values[':shop_code'] = $filter['shop_code'];
        }
        $sql_main .= " ORDER BY bs.store_property DESC";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            $val['store_property'] = ($val['store_property'] == 1) ? '门店仓' : '普通仓';
            if (isset($filter['type']) && $filter['type'] == 'goods_ratio') {
                $val['store_sync_ratio'] = (!empty($val['goods_sync_ratio']) && $val['sku'] == $filter['sku']) ? $val['goods_sync_ratio'] * 100 : $val['sync_ratio'] * 100;
            } else {
                $val['store_sync_ratio'] = $val['sync_ratio'] * 100;
            }
            $val['store_sync_ratio_tmp'] = $val['store_sync_ratio'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_goods_by_page($filter) {
        if (empty($filter['sync_code'])) {
            $ret_status = OP_SUCCESS;
            $ret_data = array();
            return $this->format_ret($ret_status, $ret_data);
        }
        $sql_values = array();
        $sql_main = " FROM goods_sku gs
                     LEFT JOIN base_goods bg ON bg.goods_code = gs.goods_code
                     LEFT JOIN op_inv_sync_goods_ratio ss ON ss.sku = gs.sku AND ss.sync_code=:sync_code LEFT JOIN op_inv_sync AS ois ON ois.sync_code = ss.sync_code WHERE 1 ";
        $filter_brand_code = isset($filter['brand_code']) ? $filter['brand_code'] : null;
        $sql_main .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code', $filter_brand_code);
        $sql_values[':sync_code'] = $filter['sync_code'];
        //分类
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $category_code_arr = explode(',', $filter['category_code']);
            if (!empty($category_code_arr)) {
                $sql_main .= " AND (";
                foreach ($category_code_arr as $key => $value) {
                    $param_category = 'param_category' . $key;
                    if ($key == 0) {
                        $sql_main .= " category_code = :{$param_category} ";
                    } else {
                        $sql_main .= " or category_code = :{$param_category} ";
                    }

                    $sql_values[':' . $param_category] = $value;
                }
                $sql_main .= ")";
            }
        }
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $brand_code_arr = explode(',', $filter['brand_code']);
            if (!empty($brand_code_arr)) {
                $sql_main .= " AND (";
                foreach ($brand_code_arr as $key => $value) {
                    $param_brand = 'param_brand' . $key;
                    if ($key == 0) {
                        $sql_main .= " brand_code = :{$param_brand} ";
                    } else {
                        $sql_main .= " or brand_code = :{$param_brand} ";
                    }

                    $sql_values[':' . $param_brand] = $value;
                }
                $sql_main .= ")";
            }
        }
        //年份
        if (isset($filter['year_code']) && $filter['year_code'] != '') {
            $year_code_arr = explode(',', $filter['year_code']);
            if (!empty($year_code_arr)) {
                $sql_main .= " AND (";
                foreach ($year_code_arr as $key => $value) {
                    $param_year = 'param_year' . $key;
                    if ($key == 0) {
                        $sql_main .= " year_code = :{$param_year} ";
                    } else {
                        $sql_main .= " or year_code = :{$param_year} ";
                    }

                    $sql_values[':' . $param_year] = $value;
                }
                $sql_main .= ")";
            }
        }
        //季节
        if (isset($filter['season_code']) && $filter['season_code'] != '') {
            $season_code_arr = explode(',', $filter['season_code']);
            if (!empty($season_code_arr)) {
                $sql_main .= " AND (";
                foreach ($season_code_arr as $key => $value) {
                    $param_season = 'param_season' . $key;
                    if ($key == 0) {
                        $sql_main .= " season_code = :{$param_season} ";
                    } else {
                        $sql_main .= " or season_code = :{$param_season} ";
                    }

                    $sql_values[':' . $param_season] = $value;
                }
                $sql_main .= ")";
            }
        }
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (bg.goods_code LIKE :code_name OR bg.goods_name LIKE :code_name OR gs.barcode LIKE :code_name)";
            $sql_values[':code_name'] = "%" . trim($filter['code_name']) . "%";
        }
        //预警设置
        if (isset($filter['warn_sku_status']) && $filter['warn_sku_status'] != '') {
            $warn_sku = $this->get_warn_sku_info($filter['sync_code']);
            $sku_arr = array_column($warn_sku, 'sku');
            $sku_arr = array_unique($sku_arr);
            if ($filter['warn_sku_status'] == 1) {
                if (!empty($sku_arr)) {
                    $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                    $sql_main .= " AND gs.sku IN({$sku_str})";
                } else {
                    $sql_main .= " AND 1=2";
                }
            } else {
                if (!empty($sku_arr)) {
                    $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                    $sql_main .= " AND gs.sku NOT IN({$sku_str})";
                }
            }
        }
        //同步设置
        if (isset($filter['setted_sync_ratio']) && $filter['setted_sync_ratio'] != '') {
            if ($filter['setted_sync_ratio'] == 1) {
                $sql_main .= " AND ss.sync_ratio IS NOT NULL ";
            } else {
                $sql_main .= " AND ss.sync_ratio IS NULL ";
            }
        }
        if (isset($filter['ctl_type']) && $filter['ctl_type'] == 'set_ratio') {
            $filter['page_size'] = 100000;
            $select = 'gs.sku'; //一键设置使用
        } else {
            //商品比例列表检索使用
            $select = 'gs.sku_id,gs.sku,gs.barcode,bg.goods_code,bg.goods_name,bg.category_name,bg.brand_name,bg.year_name,bg.season_name,gs.spec1_code,gs.spec1_name,gs.spec2_code,gs.spec2_name,ois.status';
            if ($filter['ctl_type'] == 'export') {
                $select .=',ss.shop_code,ss.store_code,ss.sync_ratio*100 AS sync_ratio'; //导出
            } else {
                $select .=',ss.sync_ratio';
            }
        }
        //商品比例配置 把所有店铺的都导出
        if ($filter['ctl_type'] == 'export') {
            $sql_main .= ' ORDER BY sku_id ';
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        } else {
            $sql_main .= ' GROUP BY sku_id ';
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        }

        if (isset($filter['ctl_type']) && $filter['ctl_type'] == 'set_ratio') {
            if (!empty($data['data'])) {
                $sku_arr = array_column($data['data'], 'sku');
                return $this->format_ret(1, $sku_arr);
            } else {
                return $this->format_ret(-1, '', '没有发现要进行操作的商品');
            }
        }

        $warn_sku_data = array();
        if (!empty($data['data'])) {
            $sku_arr = array_column($data['data'], 'sku');
            $warn_sku_info = $this->get_warn_sku_info($filter['sync_code'], $sku_arr);
            foreach ($warn_sku_info as $info) {
                $warn_sku_data[$info['sku']] = $info['sku'];
            }
        }
        //预警参数
        $anti_oversold = load_model('sys/SysParamsModel')->get_val_by_code('anti_oversold');
        foreach ($data['data'] as &$value) {
            $value['setted_ratio'] = (empty($value['sync_ratio'])) ? '否' : '是';
            $value['warn_sku_name'] = (isset($warn_sku_data[$value['sku']])) ? '是' : '否';
            $value['anti_oversold_status'] = (isset($warn_sku_data[$value['sku']])) ? '1' : '0';
            $value['anti_oversold'] = $anti_oversold['anti_oversold'];
        }
        if ($filter['ctl_type'] == 'export' && isset($filter['sync_mode'])) {
            if ($filter['sync_mode'] == 1) {
                filter_fk_name($data['data'], array('shop_code|shop'));
            } else {
                filter_fk_name($data['data'], array('shop_code|shop', 'store_code|store'));
            }
            $log_info = '商品比例配置导出;';
            $user_code = empty(CTX()->get_session('user_code')) ? 'admin' : CTX()->get_session('user_code');
            $log = array('sync_code' => $filter['sync_code'], 'user_code' => $user_code, 'user_ip' => gethostbyname($_SERVER["SERVER_NAME"]), 'tab_type' => 'goods_ratio', 'log_info' => $log_info, 'log_time' => date('Y-m-d H:i:s'));
            $res = load_model('op/InvSyncLogModel')->insert($log);
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 获取条码预警库存
     * @param $sync_code
     * @return array|bool
     */
    function get_warn_sku_info($sync_code, $sku_arr = array()) {
        $sql = "SELECT * FROM op_inv_sync_warn_sku WHERE sync_code=:sync_code";
        $sql_value = array();
        $sql_value[':sync_code'] = $sync_code;
        if (!empty($sku_arr)) {
            $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_value);
            $sql.= " AND sku IN({$sku_str})";
        }
        $ret = $this->db->get_all($sql, $sql_value);
        return $ret;
    }

    /**
     * @todo 创建策略时向店铺比例配置表和商品比例配置表写入数据
     * @param $shop_data array 店铺数据
     * @param $store_data array 仓库数据
     * @param $sync_mode 1 全局模式 2 仓库模式
     */
    function insert_by_multi($shop_data, $store_data, $sync_mode) {
        $data = array();
        if ($sync_mode == 2) {
            $i = 0;
            foreach ($shop_data as $value) {
                foreach ($store_data as $v) {
                    $data[$i]['sync_code'] = $value['sync_code'];
                    $data[$i]['shop_code'] = $value['code'];
                    $data[$i]['store_code'] = $v['code'];
                    $data[$i]['sync_ratio'] = 1;
                    $i++;
                }
            }
        } else {
            foreach ($shop_data as &$value) {
                $value['sync_ratio'] = 1;
                $value['shop_code'] = $value['code'];
                unset($value['type']);
            }
            $data = $shop_data;
        }
        $this->begin_trans();
        $update_str = ' sync_code = VALUES(sync_code), shop_code = VALUES(shop_code) ';
        $update_str .= ($sync_mode == 2) ? ', store_code = VALUES(store_code)' : '';
        $ret = $this->insert_multi_duplicate($this->shop_table, $data, $update_str);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        return $ret;
    }

    /**
     * @todo 店铺比例设置
     */
    function set_sync_ratio($params) {
        $isAllow = preg_match('/^\+?[1-9]\d*$/', $params['sync_ratio']);
        if ($isAllow == 0) {
            return $this->format_ret(-1, '', '同步比例必须为大于0的整数！');
        }

        $sql = "SELECT shop_code, store_code, sync_ratio FROM {$this->shop_table} WHERE ratio_id=:ratio_id";
        $old_data = $this->db->get_row($sql, array(':ratio_id' => $params['ratio_id']));

        $data = array('sync_ratio' => ($params['sync_ratio'] / 100));
        $where = array('ratio_id' => $params['ratio_id'], 'sync_code' => $params['sync_code']);
        $ret = parent::update_exp($this->shop_table, $data, $where);

        $sql = "select shop_name from base_shop where shop_code = :shop_code ";
        $sql_val = array(':shop_code' => $old_data['shop_code']);
        $shop_name = $this->db->get_value($sql, $sql_val);
        $sql = "select store_name from base_store where store_code = :store_code ";
        $sql_val = array(':store_code' => $old_data['store_code']);
        $store_name = $this->db->get_value($sql, $sql_val);

        $log_info = '店铺[' . $shop_name . ']同步比例由[' . $old_data['sync_ratio'] . ']修改为[' . $data['sync_ratio'] . '];';
        if ($params['sync_mode'] == 2) {
            $log_info = '店铺[' . $shop_name . ']仓库[' . $store_name . ']同步比例由[' . $old_data['sync_ratio'] . ']修改为[' . $data['sync_ratio'] . '];';
        }
        $log = array('sync_code' => $params['sync_code'], 'user_code' => CTX()->get_session('user_code'), 'user_ip' => gethostbyname($_SERVER["SERVER_NAME"]), 'tab_type' => 'shop_ratio', 'log_info' => $log_info, 'log_time' => date('Y-m-d H:i:s'));
        $res = load_model('op/InvSyncLogModel')->insert($log);

        return $ret;
    }

    /**
     * @todo 批量设置同步比例
     */
    function set_goods_ratio($params) {
        $isAllow = preg_match('/^\+?[0-9]\d*$/', $params['sync_ratio']);
        if ($isAllow == 0) {
            return $this->format_ret(-1, '', '同步比例必须为整数！');
        }
        $params['sync_ratio'] = !empty($params['sync_ratio']) ? ($params['sync_ratio'] / 100) : '';
        $ratio_arr = array();
        if ($params['set_type'] == 'set') {
            $ratio_arr[] = $params;
            $log_type = $params['set_type'];
        } else {
            if ($params['set_type'] == 'one_set') {
                $sku_arr = $this->get_goods_by_page($params['select_wh']);
                if (empty($sku_arr['data'])) {
                    return $sku_arr;
                }
                $sku_arr = $sku_arr['data'];
            } else if ($params['set_type'] == 'batch_set' || $params['set_type'] == 'set') {
                $sku_arr = explode(',', $params['sku']);
            }
            $log_type = $params['set_type'];
            $log_sku = $params['sku'];
            unset($params['sku'], $params['set_type']);
            foreach ($sku_arr as $key => $val) {
                $ratio_arr[$key] = $params;
                $ratio_arr[$key]['sku'] = $val;
            }
        }
        $ratio = array_chunk($ratio_arr, 300, true);
        $this->begin_trans();

        $sql = "SELECT shop_name FROM base_shop WHERE shop_code=:shop_code";
        $shop_name = $this->db->get_row($sql, array(':shop_code' => $params['shop_code']));
        if ($log_type == 'set') {
            if ($params['store_code'] != '') {
                $sql = "SELECT store_name FROM base_store WHERE store_code=:store_code";
                $store_name = $this->db->get_row($sql, array(':store_code' => $params['store_code']));
                $sql = "SELECT sync_ratio FROM {$this->goods_table} WHERE sync_code=:sync_code AND shop_code=:shop_code AND store_code=:store_code AND sku=:sku";
                $old_data = $this->db->get_row($sql, array(':sync_code' => $params['sync_code'], ':shop_code' => $params['shop_code'], ':store_code' => $params['store_code'], ':sku' => $params['sku']));
                $sql = "SELECT sync_ratio FROM {$this->shop_table} WHERE sync_code=:sync_code AND shop_code=:shop_code AND store_code=:store_code";
                $sync_ratio = $this->db->get_value($sql, array(':sync_code' => $params['sync_code'], ':shop_code' => $params['shop_code'], ':store_code' => $params['store_code']));
                $old_data['sync_ratio'] = $old_data['sync_ratio'] ? $old_data['sync_ratio'] : $sync_ratio;
                $params['sync_ratio'] = $params['sync_ratio'] ? $params['sync_ratio'] : 0;
                if ($old_data['sync_ratio'] != $params['sync_ratio']) {
                    $log_info = '店铺[' . $shop_name['shop_name'] . ']仓库[' . $store_name['store_name'] . ']下的商品sku:' . $params['sku'] . '同步比例由[' . $old_data['sync_ratio'] . ']修改为[' . $params['sync_ratio'] . '];';
                }
            } else {
                $sql = "SELECT sync_ratio FROM {$this->goods_table} WHERE sync_code=:sync_code AND shop_code=:shop_code AND sku=:sku";
                $old_data = $this->db->get_row($sql, array(':sync_code' => $params['sync_code'], ':shop_code' => $params['shop_code'], ':sku' => $params['sku']));
                $sql = "SELECT sync_ratio FROM {$this->shop_table} WHERE sync_code=:sync_code AND shop_code=:shop_code ";
                $sync_ratio = $this->db->get_value($sql, array(':sync_code' => $params['sync_code'], ':shop_code' => $params['shop_code']));
                $old_data['sync_ratio'] = $old_data['sync_ratio'] ? $old_data['sync_ratio'] : $sync_ratio;
                $params['sync_ratio'] = $params['sync_ratio'] ? $params['sync_ratio'] : 0;
                if ($old_data['sync_ratio'] != $params['sync_ratio']) {
                    $log_info = '店铺[' . $shop_name['shop_name'] . ']下的商品sku:' . $params['sku'] . '同步比例由[' . $old_data['sync_ratio'] . ']修改为[' . $params['sync_ratio'] . '];';
                }
            }
        } elseif ($log_type == 'batch_set') {
            if ($params['store_code'] != '') {
                $sql = "SELECT store_name FROM base_store WHERE store_code=:store_code";
                $store_name = $this->db->get_row($sql, array(':store_code' => $params['store_code']));
                $batch_sku = deal_array_with_quote($sku_arr);
                $sql = "SELECT sku,sync_ratio FROM {$this->goods_table} WHERE sync_code=:sync_code AND shop_code=:shop_code AND store_code=:store_code AND sku IN ({$batch_sku})";
                $old_data = $this->db->get_all($sql, array(':sync_code' => $params['sync_code'], ':shop_code' => $params['shop_code'], ':store_code' => $params['store_code']));
                $str_sku = '';
                $str_ratio = '';
                if ($old_data) {
                    foreach ($old_data as &$val) {
                        if ($val['sync_ratio'] == $params['sync_ratio']) {
                            continue;
                        }
                        $str_sku .= $val['sku'] . ',';
                        $str_ratio .= $val['sync_ratio'] . ',';
                    }
                    $str_sku = rtrim($str_sku, ',');
                    $str_ratio = rtrim($str_ratio, ',');
                } else {
                    $sql = "SELECT sync_ratio FROM {$this->shop_table} WHERE sync_code=:sync_code AND shop_code=:shop_code ";
                    $sync_ratio = $this->db->get_value($sql, array(':sync_code' => $params['sync_code'], ':shop_code' => $params['shop_code']));
                    $str_sku = $log_sku;
                    if ($sync_ratio != $params['sync_ratio']) {
                        $str_sku_arr = explode(',', $str_sku);
                        foreach ($str_sku_arr as $val) {
                            $str_ratio .= $sync_ratio . ',';
                        }
                        $str_ratio = rtrim($str_ratio, ',');
                    }
                }

                if ($str_sku && $str_ratio) {
                    $params['sync_ratio'] = $params['sync_ratio'] ? $params['sync_ratio'] : 0;
                    $log_info = '店铺[' . $shop_name['shop_name'] . ']仓库[' . $store_name['store_name'] . ']下的商品sku:' . $str_sku . '同步比例由[' . $str_ratio . ']批量修改为[' . $params['sync_ratio'] . '];';
                }
            } else {
                $batch_sku = deal_array_with_quote($sku_arr);
                $sql = "SELECT sku,sync_ratio FROM {$this->goods_table} WHERE sync_code=:sync_code AND shop_code=:shop_code AND sku IN ({$batch_sku})";
                $old_data = $this->db->get_all($sql, array(':sync_code' => $params['sync_code'], ':shop_code' => $params['shop_code']));
                $str_sku = '';
                $str_ratio = '';
                if ($old_data) {
                    foreach ($old_data as &$val) {
                        if ($val['sync_ratio'] == $params['sync_ratio']) {
                            continue;
                        }
                        $str_sku .= $val['sku'] . ',';
                        $str_ratio .= $val['sync_ratio'] . ',';
                    }
                    $str_sku = rtrim($str_sku, ',');
                    $str_ratio = rtrim($str_ratio, ',');
                } else {
                    $sql = "SELECT sync_ratio FROM {$this->shop_table} WHERE sync_code=:sync_code AND shop_code=:shop_code ";
                    $sync_ratio = $this->db->get_value($sql, array(':sync_code' => $params['sync_code'], ':shop_code' => $params['shop_code']));
                    $str_sku = $log_sku;
                    if ($sync_ratio != $params['sync_ratio']) {
                        $str_sku_arr = explode(',', $str_sku);
                        foreach ($str_sku_arr as $val) {
                            $str_ratio .= $sync_ratio . ',';
                        }
                        $str_ratio = rtrim($str_ratio, ',');
                    }
                }

                if ($str_sku && $str_ratio) {
                    $log_info = '店铺[' . $shop_name['shop_name'] . ']下的商品sku:' . $str_sku . '同步比例由[' . $str_ratio . ']批量修改为[' . $params['sync_ratio'] . '];';
                }
            }
        } else {
            $log_info = '店铺[' . $shop_name['shop_name'] . ']一键设置商品同步比例:' . $params['sync_ratio'] . ';';
            if ($params['store_code'] != '') {
                $sql = "SELECT store_name FROM base_store WHERE store_code=:store_code";
                $store_name = $this->db->get_row($sql, array(':store_code' => $params['store_code']));
                $log_info = '店铺[' . $shop_name['shop_name'] . ']仓库[' . $store_name['store_name'] . ']一键设置商品同步比例:' . $params['sync_ratio'] . ';';
            }
        }

        foreach ($ratio as $val) {
            $update_str = "sync_ratio = VALUES(sync_ratio) ";
            $ret = $this->insert_multi_duplicate($this->goods_table, $val, $update_str);
            if ($ret['status'] != 1) {
                $this->rollback();
                break;
            }
        }
        if ($ret['status'] == 1) {
            $this->commit();
        }

        if ($log_info) {
            $log = array('sync_code' => $params['sync_code'], 'user_code' => CTX()->get_session('user_code'), 'user_ip' => gethostbyname($_SERVER["SERVER_NAME"]), 'tab_type' => 'goods_ratio', 'log_info' => $log_info, 'log_time' => date('Y-m-d H:i:s'));
            $res = load_model('op/InvSyncLogModel')->insert($log);
        }

        return $ret;
    }

    /**
     * @todo 批量删除同步比例
     */
    function delete_goods_ratio($params) {
        if ($params['set_type'] == 'one_del') {
            $sku_arr = $this->get_goods_by_page($params);
            if (empty($sku_arr['data'])) {
                return $sku_arr;
            }
            $str_arr = $sku_arr['data'];
            //获取预警条码
            //$warn = $this->get_warn_sku_info($params['sync_code']);
            //$warn_sku = array_column($warn, 'sku');
            //$str_arr = array_merge($str_arr, $warn_sku);
        } else if ($params['set_type'] == 'batch_del') {
            $str_arr = $params['sku'];
        } else {
            $str_arr = explode(',', $params['sku']);
        }
        $sku_arr = array_chunk($str_arr, 1000, true);
        $this->begin_trans();

        foreach ($sku_arr as $val) {
            $sku_str = deal_array_with_quote($val);
            $sql = "DELETE FROM {$this->goods_table} WHERE sync_code=:sync_code AND sku in({$sku_str})";
            $ret = $this->query($sql, array(':sync_code' => $params['sync_code']));
            if ($ret['status'] != 1) {
                $this->rollback();
                break;
            }
            //删除条码预警
            $sql_value = array();
            $warn_sku_str = $this->arr_to_in_sql_value($val, 'sku', $sql_value);
            $sql = "DELETE FROM op_inv_sync_warn_sku WHERE sync_code=:sync_code AND sku in({$warn_sku_str})";
            $sql_value[':sync_code'] = $params['sync_code'];
            $ret = $this->query($sql, $sql_value);
            if ($ret['status'] != 1) {
                $this->rollback();
                break;
            }
        }
        if ($ret['status'] == 1) {
            $this->commit();
            if ($params['set_type'] == 'one_del') {
                $log_info = '一键清除商品同步比例及条码预警;';
            } else if ($params['set_type'] == 'batch_del') {
                $str_str = implode(",", $params['sku']);
                $log_info = '清除商品sku:' . $str_str . '同步比例及条码预警;';
            } else {
                $log_info = '清除商品sku:' . $params['sku'] . '同步比例及条码预警;';
            }
            $log = array('sync_code' => $params['sync_code'], 'user_code' => CTX()->get_session('user_code'), 'user_ip' => gethostbyname($_SERVER["SERVER_NAME"]), 'tab_type' => 'goods_ratio', 'log_info' => $log_info, 'log_time' => date('Y-m-d H:i:s'));
            $res = load_model('op/InvSyncLogModel')->insert($log);
        }

        return $ret;
    }

    function delete_diff_data($sync_code, $diff_shop, $diff_store) {
        $shop_code = join("','", $diff_shop['less_code']);
        $store_code = join("','", $diff_store['less_code']);
        $this->delete_exp($this->shop_table, "sync_code = '{$sync_code}' AND shop_code IN ('{$shop_code}')");
        $this->delete_exp($this->shop_table, "sync_code = '{$sync_code}' AND store_code IN ('{$store_code}')");
        $this->delete_exp($this->goods_table, "sync_code = '{$sync_code}' AND shop_code IN ('{$shop_code}')");
        $this->delete_exp($this->goods_table, "sync_code = '{$sync_code}' AND store_code IN ('{$store_code}')");
    }

    /**
     * @todo 导入比例数据
     */
    function import_ratio_data($param, $file) {
        $sync_code = $param['sync_code'];
        $sync_mode = $param['sync_mode'];
        //判断主单据的pid是否存在
        $sync_record = load_model('op/InvSyncModel')->get_row(array('sync_code' => $sync_code));
        if ($sync_record['status'] != 1) {
            return $this->format_ret(-1, '', '策略不存在，请重试');
        }
        //读取excel
        $action = ($sync_mode == 1) ? 'read_csv_shop' : 'read_csv_store';
        $sync_perc = array();
        $num = $this->$action($file, $sync_perc);

        //获取策略中的仓库
        if ($sync_mode == 2) {
            $store_arr = load_model('op/InvSyncModel')->get_relation(array('sync_code' => $sync_code, 'type' => 2));
            $store_arr = array_column($store_arr, 'code'); //策略中存在的店铺
        }
        //获取策略中的店铺
        $shop_arr = load_model('op/InvSyncModel')->get_relation(array('sync_code' => $sync_code, 'type' => 1));
        $shop_arr = array_column($shop_arr, 'code'); //策略中存在的店铺
        //获取excel中存在于系统的sku
        $barcode_str = deal_array_with_quote(array_column($sync_perc, 'barcode'));
        $sql = "SELECT sku,barcode FROM goods_sku WHERE barcode in({$barcode_str})";
        $sku_info = $this->db->get_all($sql);
        $sku_arr = array();
        foreach ($sku_info as $val) {
            $sku_arr[$val['barcode']] = $val['sku'];
        }

        $import_count = count($sync_perc);
        $error_msg = array();
        $err_num = 0;
        $log_info = '';
        //处理导入信息，正确和错误信息分离
        foreach ($sync_perc as $key => $val) {
            $check = 1;
            if (!in_array($val['shop_code'], $shop_arr)) {
                $msg = '策略中不存在此店铺';
                $check = 0;
            } else if (!in_array($val['store_code'], $store_arr) && $sync_mode == 2) {
                $msg = '策略中不存在此仓库';
                $check = 0;
            } else if (!array_key_exists($val['barcode'], $sku_arr)) {
                $msg = '系统中不存在此条码';
                $check = 0;
            } else if (strpos($val['sync_ratio'], '.') || $val['sync_ratio'] < 0) {
                $msg = '同步比例必须为大于0的整数';
                $check = 0;
            }
            if ($check == 0) {
                $err_num++;
                if ($sync_mode == 1) {
                    $error_msg[] = array($val['barcode'] . ',' . $val['shop_code'] => $msg);
                } else {
                    $error_msg[] = array($val['barcode'] . ',' . $val['shop_code'] . ',' . $val['store_code'] => $msg);
                }

                unset($sync_perc[$key]);
                continue;
            }
            $sync_perc[$key]['sku'] = $sku_arr[$val['barcode']];
            $sync_perc[$key]['sync_code'] = $sync_code;
            $sync_perc[$key]['sync_ratio'] = $val['sync_ratio'] / 100;
            unset($sync_perc[$key]['barcode']);
            if ($sync_mode == 2) {
                $sql = "SELECT store_name FROM base_store WHERE store_code=:store_code";
                $store_name = $this->db->get_row($sql, array(':store_code' => $val['store_code']));
                $sql = "SELECT shop_name FROM base_shop WHERE shop_code=:shop_code";
                $shop_name = $this->db->get_row($sql, array(':shop_code' => $val['shop_code']));
                $log_info .= '店铺[' . $shop_name['shop_name'] . ']仓库[' . $store_name['store_name'] . ']条码:' . $val['barcode'] . '同步比例:' . $sync_perc[$key]['sync_ratio'] . ';';
            } else {
                $sql = "SELECT shop_name FROM base_shop WHERE shop_code=:shop_code";
                $shop_name = $this->db->get_row($sql, array(':shop_code' => $val['shop_code']));
                $log_info .= '店铺[' . $shop_name['shop_name'] . ']条码:' . $val['barcode'] . '同步比例:' . $sync_perc[$key]['sync_ratio'] . ';';
            }
        }

        if (!empty($sync_perc)) {
            $update_str = 'sync_ratio = VALUES(sync_ratio)';
            $ret = $this->insert_multi_duplicate($this->goods_table, $sync_perc, $update_str);
        }

        $success_num = $import_count - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $message .=',' . '失败数量:' . $err_num;
            if ($sync_mode == 1) {
                $fail_top = array('条码', '店铺代码', '错误信息');
            } else {
                $fail_top = array('条码', '店铺代码', '仓库代码', '错误信息');
            }
            $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }

        $ret['message'] = $message;
        if ($log_info != '') {
            $log = array('sync_code' => $sync_code, 'user_code' => CTX()->get_session('user_code'), 'user_ip' => gethostbyname($_SERVER["SERVER_NAME"]), 'tab_type' => 'goods_ratio', 'log_info' => $log_info, 'log_time' => date('Y-m-d H:i:s'));
            $res = load_model('op/InvSyncLogModel')->insert($log);
        }

        return $ret;
    }

    /**
     * 读取文件，保存到数组中
     */
    function read_csv_shop($file, &$sync_perc) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 2) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sync_perc[$i]['barcode'] = $row[0];
                    $sync_perc[$i]['shop_code'] = $row[1];
                    $sync_perc[$i]['sync_ratio'] = $row[2];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /**
     * 读取文件，保存到数组中
     */
    function read_csv_store($file, &$sync_perc) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 2) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sync_perc[$i]['barcode'] = $row[0];
                    $sync_perc[$i]['shop_code'] = $row[1];
                    $sync_perc[$i]['store_code'] = $row[2];
                    $sync_perc[$i]['sync_ratio'] = $row[3];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /**
     * @todo 编码转换
     */
    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
//                $val = iconv('utf-8', 'utf-8', $val);
                $val = trim(str_replace('"', '', $val));
            }
        }
    }

    /**
     * 锁定单更新商品同步比例
     * @param array $lock_record 锁定单据信息
     * @param array $goods_detail 锁定商品明细
     * @param string $opt_type lock-锁定 unlock-释放
     * @return array 更新结果
     */
    public function lock_record_update_goods_retio($lock_record, $goods_detail, $opt_type) {
        $sync_mode = $this->db->get_value('SELECT sync_mode FROM op_inv_sync WHERE sync_code=:sync_code', array(':sync_code' => $lock_record['sync_code']));
        $sync_ratio = $opt_type == 'lock' ? 0 : $lock_record['sync_ratio'];
        $sync_goods_ratio = array();
        foreach ($goods_detail as $val) {
            $sync_goods_ratio[] = array(
                'sync_code' => $lock_record['sync_code'],
                'shop_code' => $lock_record['shop_code'],
                'store_code' => $sync_mode == 1 ? '' : $lock_record['store_code'],
                'sku' => $val['sku'],
                'sync_ratio' => $sync_ratio,
            );
        }
        $ret = $this->insert_multi_duplicate('op_inv_sync_goods_ratio', $sync_goods_ratio, 'sync_ratio=VALUES(sync_ratio)');
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '更新商品同步比例失败');
        }
        //添加日志
        $sku_arr = array_column($sync_goods_ratio, 'sku');
        $sku_arr = array_chunk($sku_arr, 10);
        foreach ($sku_arr as $val) {
            $sku_str = implode(',', $val);
            if ($sync_mode == 1) {
                $log_info = "锁定单[{$lock_record['record_code']}]，设置店铺[{$lock_record['shop_code']}]下的商品sku[{$sku_str}]同步比例为[{$sync_ratio}]";
            } else {
                $log_info = "锁定单[{$lock_record['record_code']}]，设置店铺[{$lock_record['shop_code']}]仓库[{$lock_record['store_code']}]下的商品sku[{$sku_str}]同步比例为[{$sync_ratio}]";
            }
            //标签类型: goods_ratio -> 商品比例配置  shop_ratio -> 店铺比例配置
            $log = array('sync_code' => $lock_record['sync_code'], 'user_code' => CTX()->get_session('user_code'), 'user_ip' => gethostbyname($_SERVER["SERVER_NAME"]), 'tab_type' => 'goods_ratio', 'log_info' => $log_info, 'log_time' => date('Y-m-d H:i:s'));
            $res = load_model('op/InvSyncLogModel')->insert($log);
        }
        return $this->format_ret(1);
    }

    /**
     * 获取店铺、仓库同步比例
     * @param array $params
     * @return array
     */
    public function get_shop_store_ratio($params) {
        $sync_mode = $this->db->get_value('SELECT sync_mode FROM op_inv_sync WHERE sync_code=:sync_code', array(':sync_code' => $params['sync_code']));
        $sql_values = array('sync_code' => $params['sync_code'], ':shop_code' => $params['shop_code']);
        $sql = 'SELECT sync_ratio FROM op_inv_sync_shop_ratio WHERE sync_code=:sync_code AND shop_code=:shop_code';
        if ($sync_mode == 2) {
            $sql .= ' AND store_code=:store_code';
            $sql_values[':store_code'] = $params['store_code'];
        }
        $ratio = $this->db->get_value($sql, $sql_values);
        return $this->format_ret(1, $ratio * 100);
    }

}
