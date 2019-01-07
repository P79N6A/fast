<?php

require_model('tb/TbModel');

/**
 * 批次切换
 */
class WmsSwitchLofModel extends TbModel {

    private $record_code;
    private $record_type;
    private $store_code;
    private $wms_type;
    private $lof_table;
    private $relation_type;
    private $oms_type = array(
        '1' => 'sell_record',
    );
    private $b2b_type = array(
        'shift_out',
        'pur_return_notice',
        'wbm_notice'
    );

    private function _init_data($record_code, $record_type) {
        $this->record_code = $record_code;
        $this->record_type = $record_type;
        if (in_array($record_type, $this->oms_type)) {
            $this->wms_type = 'oms';
            $this->lof_table = 'oms_sell_record_lof';
        } else {
            $this->wms_type = 'b2b';
            $this->lof_table = 'b2b_lof_datail';
        }
        if ($record_type == 'sell_record') {
            $this->relation_type = 'oms';
        } else {
            $this->relation_type = $this->record_type;
        }
    }

    /**
     * 批次/锁定数据切换
     * @return array 处理结果
     */
    function switch_lof_lock($record_code, $record_type) {
        $this->_init_data($record_code, $record_type);

        $wms_lof_tbl = "wms_{$this->wms_type}_order_lof";
        $sql_values = array(':code' => $this->record_code, ':type' => $this->record_type);
        if ($this->record_type == 'sell_record') {
            $sql = "SELECT barcode,SUM(efast_sl) AS efast_sl_total,SUM(wms_sl) AS wms_sl_total FROM {$wms_lof_tbl} 
                    WHERE record_code=:code AND record_type=:type GROUP BY barcode HAVING efast_sl_total<>wms_sl_total";
            $return_total = $this->db->get_all($sql, $sql_values);
            if (!empty($return_total)) {
                $barcode_str = implode('；', array_column($return_total, 'barcode'));
                return $this->format_ret(-1, '', "条码：{$barcode_str}回传商品数量与上传数量不一致");
            }
        }

        $sql = "SELECT barcode,lof_no,production_date,efast_sl,wms_sl FROM {$wms_lof_tbl} 
                WHERE record_code=:code AND record_type=:type AND efast_sl<>wms_sl";
        $wms_lof_data = $this->db->get_all($sql, $sql_values);
        if (empty($wms_lof_data)) {
            return $this->format_ret(1, '', '没有需要调整的批次数据');
        }
        //barcode转换为sku
        $barcode_arr = array_unique(array_column($wms_lof_data, 'barcode'));
        $sku_data = load_model('prm/SkuModel')->convert_barcode($barcode_arr);
        $sku_data = array_column($sku_data['data'], 'sku', 'barcode');


        $wms_lof_incr_lock = array(); //需要新增锁定的批次
        $wms_lof_decr_lock = array(); //需要取消锁定的批次
        if (in_array($this->record_type, array('wbm_notice', 'pur_return_notice'))) {
            $this->deal_delivery_data($wms_lof_data);

            foreach ($wms_lof_data as $val) {
                $barcode = strtolower($val['barcode']);
                $val['sku'] = $sku_data[$barcode];
                unset($val['barcode']);
                $k = $val['sku'] . '_' . $val['lof_no'];
                if ($val['efast_sl'] == 0) {
                    $val['num'] = $val['efast_sl_temp'];
                    unset($val['efast_sl'], $val['wms_sl']);
                    if (isset($wms_lof_incr_lock[$k])) {
                        $wms_lof_incr_lock[$k]['num'] += $val['num'];
                    } else {
                        $wms_lof_incr_lock[$k] = $val;
                    }
                } else if ($val['wms_sl'] == 0) {
                    $val['num'] = $val['efast_sl'];
                    unset($val['efast_sl'], $val['wms_sl']);
                    if (isset($wms_lof_decr_lock[$k])) {
                        $wms_lof_decr_lock[$k]['num'] += $val['num'];
                    } else {
                        $wms_lof_decr_lock[$k] = $val;
                    }
                } else if ($val['wms_sl'] <> $val['efast_sl'] && isset($val['efast_sl_temp'])) {
                    $temp = $val;
                    $val['num'] = $val['efast_sl_temp'];
                    unset($val['efast_sl'], $val['wms_sl']);
                    if (isset($wms_lof_incr_lock[$k])) {
                        $wms_lof_incr_lock[$k]['num'] += $val['num'];
                    } else {
                        $wms_lof_incr_lock[$k] = $val;
                    }

                    $temp['num'] = $temp['efast_sl'];
                    unset($temp['efast_sl'], $temp['wms_sl']);
                    if (isset($wms_lof_decr_lock[$k])) {
                        $wms_lof_decr_lock[$k]['num'] += $temp['num'];
                    } else {
                        $wms_lof_decr_lock[$k] = $temp;
                    }
                }
            }
        } else {
            foreach ($wms_lof_data as $val) {
                $barcode = strtolower($val['barcode']);
                $val['sku'] = $sku_data[$barcode];
                unset($val['barcode']);
                $k = $val['sku'] . '_' . $val['lof_no'];
                if ($val['efast_sl'] == 0) {
                    $val['num'] = $val['wms_sl'];
                    unset($val['efast_sl'], $val['wms_sl']);
                    if (isset($wms_lof_incr_lock[$k])) {
                        $wms_lof_incr_lock[$k]['num'] += $val['num'];
                    } else {
                        $wms_lof_incr_lock[$k] = $val;
                    }
                } else if ($val['wms_sl'] == 0) {
                    $val['num'] = $val['efast_sl'];
                    unset($val['efast_sl'], $val['wms_sl']);
                    if (isset($wms_lof_decr_lock[$k])) {
                        $wms_lof_decr_lock[$k]['num'] += $val['num'];
                    } else {
                        $wms_lof_decr_lock[$k] = $val;
                    }
                } else if ($val['wms_sl'] <> $val['efast_sl']) {
                    if ($val['wms_sl'] < $val['efast_sl'] && !in_array($this->record_type, array('shift_out', 'sell_record'))) {
                        continue;
                    }
                    $temp = $val;
                    unset($temp['efast_sl'], $temp['wms_sl']);
                    $temp['num'] = $val['wms_sl'];
                    if (isset($wms_lof_incr_lock[$k])) {
                        $wms_lof_incr_lock[$k]['num'] += $temp['num'];
                    } else {
                        $wms_lof_incr_lock[$k] = $temp;
                    }
                    $temp['num'] = $val['efast_sl'];
                    if (isset($wms_lof_decr_lock[$k])) {
                        $wms_lof_decr_lock[$k]['num'] += $temp['num'];
                    } else {
                        $wms_lof_decr_lock[$k] = $temp;
                    }
                }
            }
        }

        unset($wms_lof_data, $sku_data);

        if (in_array($this->record_type, array('sell_record'))) {
            $incr_num = array_sum(array_column($wms_lof_incr_lock, 'num'));
            $decr_num = array_sum(array_column($wms_lof_decr_lock, 'num'));
            if ($incr_num != $decr_num) {
                return $this->format_ret(-1, '', '单据批次调整数量不匹配');
            }
        }

        //切换批次数据
        $ret = $this->switch_lof($wms_lof_incr_lock, $wms_lof_decr_lock);
        if ($ret['status'] < 1) {
            return $ret;
        }

        //取消锁定
        $ret = $this->switch_lock($wms_lof_decr_lock, 0);
        if ($ret['status'] < 1) {
            return $ret;
        }

        //增加锁定
        $ret = $this->switch_lock($wms_lof_incr_lock, 1);
        if ($ret['status'] < 1) {
            return $ret;
        }

        return $this->format_ret(1, '', '处理成功');
    }

    /**
     * 数据调整
     * @param array $wms_lof_data
     */
    private function deal_delivery_data(&$wms_lof_data) {
        $wms_lof_data = load_model('util/ViewUtilModel')->get_map_arr($wms_lof_data, 'barcode,lof_no');
        $temp_group = array();
        foreach ($wms_lof_data as $key => $row) {
            $barcode = $row['barcode'];
            if ($row['efast_sl'] == 0 || $row['efast_sl'] < $row['wms_sl']) {
                $temp_group[$barcode]['A'][] = $key;
            } else {
                $temp_group[$barcode]['B'][] = $key;
            }
        }

        foreach ($wms_lof_data as $key => &$d) {
            $barcode = $d['barcode'];
            $group_child = $temp_group[$barcode];
            if (!in_array($key, $group_child['A'])) {
                continue;
            }
            $temp_efast_sl = $d['wms_sl'];
            $d['efast_sl_temp'] = $temp_efast_sl;
            $is_deal = 0;
            foreach ($group_child['B'] as $val) {
                $total_num = isset($wms_lof_data[$val]['efast_sl_temp']) ? $wms_lof_data[$val]['efast_sl_temp'] : $wms_lof_data[$val]['efast_sl'];
                if (($total_num - $wms_lof_data[$val]['wms_sl'] - $temp_efast_sl) >= 0) {
                    $wms_lof_data[$val]['efast_sl_temp'] = $total_num - $temp_efast_sl;
                    $is_deal = 1;
                    break;
                }
            }
            if ($is_deal == 1) {
                continue;
            }

            foreach ($group_child['B'] as $val) {
                if ($temp_efast_sl <= 0) {
                    break;
                }
                $diff = $wms_lof_data[$val]['efast_sl'] - $wms_lof_data[$val]['wms_sl'];
                if ($diff > 0 && !isset($wms_lof_data[$val]['efast_sl_temp'])) {
                    $wms_lof_data[$val]['efast_sl_temp'] = $wms_lof_data[$val]['efast_sl'] - $diff;
                    $temp_efast_sl -= $diff;
                }
            }
        }
    }

    /**
     * 切换批次
     * @param array $wms_lof_incr_lock 需增加的批次数据
     * @param array $wms_lof_decr_lock 需删除的批次数据
     * @return array 处理结果
     */
    private function switch_lof($wms_lof_incr_lock, $wms_lof_decr_lock) {
        //获取单据原批次数据
        $model = $this->wms_type == 'oms' ? 'oms/SellRecordLofModel' : 'stm/GoodsInvLofRecordModel';
        $record_type = $this->wms_type == 'oms' ? array_search($this->record_type, $this->oms_type) : $this->record_type;
        $lof_detial = load_model($model)->get_by_order_code($this->record_code, $record_type);
        //批次数据异常
        if (empty($lof_detial) && $this->record_type == 'sell_record') {
            return $this->format_ret(-1, '', '原单据批次数据异常！');
        }
        $this->store_code = $lof_detial[0]['store_code'];

        $sku_data = array();
        $del_lof_id = array(); //需要删除的批次数据id
        foreach ($lof_detial as $val) {
            $id = $val['id'];
            unset($val['id']);
            $k = $val['sku'] . '_' . $val['lof_no'];
            if (isset($wms_lof_decr_lock[$k])) {
                $del_lof_id[] = $id;
            }

            $sku = $val['sku'];
            if (!isset($sku_data[$sku])) {
                $sku_data[$sku] = $val;
            }
        }
        unset($lof_detial);
        if (!empty($del_lof_id)) {
            //删除未回传的批次数据
            $sql_values = array();
            $id_str = $this->arr_to_in_sql_value($del_lof_id, 'id', $sql_values);
            $sql = "DELETE FROM {$this->lof_table} WHERE id IN({$id_str})";
            $ret = $this->query($sql, $sql_values);
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '清除未回传的批次数据失败');
            }
        }

        if (!empty($wms_lof_incr_lock)) {
            //处理要新增的批次数据
            foreach ($wms_lof_incr_lock as $key => $val) {
                $temp = $sku_data[$val['sku']];
                if ((int) $val['num'] === 0) {
                    unset($wms_lof_incr_lock[$key]);
                    continue;
                }
                $temp['num'] = $val['num'];
                $temp['sku'] = $val['sku'];
                if ($this->wms_type == 'b2b') {
                    $temp['init_num'] = $val['num'];
                }
                $temp['lof_no'] = $val['lof_no'];
                $temp['production_date'] = $val['production_date'];
                $temp['occupy_type'] = 1;
                $temp['create_time'] = time();
                $wms_lof_incr_lock[$key] = $temp;
            }

            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action('', $wms_lof_incr_lock, $this->record_type);
            //单据批次添加
            $ret = $this->insert_multi_exp($this->lof_table, $wms_lof_incr_lock);
            if ($ret['status'] < 0) {
                return $this->format_ret(-1, '', '批次数据新增失败');
            }
        }
        return $this->format_ret(1);
    }

    /**
     * 切换锁定-取消/增加批次锁定库存
     * 0-取消锁定；1-增加锁定
     * @param type $data
     * @param type $type
     */
    private function switch_lock($data, $type) {
        if (empty($data)) {
            return $this->format_ret(1);
        }
        //获取批次锁定数据
        $sql_wh = array();
        $sql_values = array(':store_code' => $this->store_code);
        $i = 0;
        foreach ($data as $val) {
            $sql_wh[] = "(sku=:sku_{$i} AND lof_no=:lof_no_{$i})";
            $sql_values[":sku_{$i}"] = $val['sku'];
            $sql_values[":lof_no_{$i}"] = $val['lof_no'];
            $i++;
        }
        $sql_wh = implode(" OR ", $sql_wh);
        $sql = "SELECT goods_code,spec1_code,spec2_code,sku,lof_no,store_code,stock_num,lock_num,lof_no,production_date FROM goods_inv_lof 
                WHERE store_code=:store_code AND ({$sql_wh})";
        $inv_lof = $this->db->get_all($sql, $sql_values);
        foreach ($inv_lof as $key => $val) {
            $inv_lof[$val['sku'] . '_' . $val['lof_no']] = $val;
            unset($inv_lof[$key]);
        }
        //获取sku数据
        $sku_arr = array_column($data, 'sku');
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT goods_code,sku,spec1_code,spec2_code FROM goods_sku WHERE sku IN({$sku_str})";
        $sku_data = $this->db->get_all($sql, $sql_values);
        $sku_data = load_model('util/ViewUtilModel')->get_map_arr($sku_data, 'sku');

        //增加库存锁定流水
        $inv_lof_new = array();
        $inv_record = array();

        foreach ($data as $k => $val) {
            $num = (int) $val['num'];
            unset($val['num']);
            $val['store_code'] = $this->store_code;
            $comm_temp = array_merge($val, $sku_data[$val['sku']]);
            $temp = $comm_temp;
            if (isset($inv_lof[$k])) {
                $inv_lof_temp = $inv_lof[$k];
                //$inv_lof[$k]['lock_num'] += $type == 0 ? 0 - $num : $num;
                $change_lock_num = $type == 0 ? 0 - $num : $num;
                if ($change_lock_num === 0) {
                    continue;
                }
                $sql = "update goods_inv_lof set lock_num = lock_num+{$change_lock_num} where
                store_code =:store_code AND sku=:sku AND lof_no = :lof_no
                  ";
                $s_values = array(':store_code' => $inv_lof[$k]['store_code'], ':sku' => $inv_lof[$k]['sku'], ':lof_no' => $inv_lof[$k]['lof_no']);
                $this->db->query($sql, $s_values);
                $run_num = $this->affected_rows();
                if ($run_num != 1 || $status === FALSE) {
                    return $this->format_ret(-1, '', '更新库存失败');
                }


                //存在的直接更新
                // $inv_lof_new[] = $inv_lof[$k];
                $temp['stock_lof_num_before_change'] = $inv_lof_temp['stock_num'];
                $temp['stock_num_before_change'] = $inv_lof_temp['stock_num'];
                $temp['stock_num_after_change'] = $inv_lof_temp['stock_num'];
                $temp['stock_lof_num_after_change'] = $inv_lof_temp['stock_num'];

                $temp['lock_num_before_change'] = $inv_lof_temp['lock_num'];
                $after_change = $type == 0 ? $inv_lof_temp['lock_num'] - $num : $inv_lof_temp['lock_num'] + $num;
                $temp['lock_num_after_change'] = $after_change;
                $temp['lock_lof_num_before_change'] = $inv_lof_temp['lock_num'];
                $temp['lock_lof_num_after_change'] = $after_change;
            } else {
                $comm_temp['stock_num'] = 0;
                $comm_temp['lock_num'] = $num;
                $inv_lof_new[] = $comm_temp;

                $temp['stock_lof_num_before_change'] = 0;
                $temp['stock_num_before_change'] = 0;
                $temp['stock_num_after_change'] = 0;
                $temp['stock_lof_num_after_change'] = 0;

                $temp['lock_num_before_change'] = 0;
                $temp['lock_num_after_change'] = $num;
                $temp['lock_lof_num_before_change'] = 0;
                $temp['lock_lof_num_after_change'] = $num;
            }
            $temp['stock_change_num'] = 0;
            $temp['lock_change_num'] = $num;

            $temp['record_time'] = date('Y-m-d H:i:s');
            $temp['relation_code'] = $this->record_code;
            $temp['relation_type'] = $this->relation_type;
            $temp['remark'] = $type == 0 ? '锁定取消' : '锁定增加';
            $inv_record[] = $temp;
        }

        if (!empty($inv_lof_new)) {
            $update_str = "lock_num=VALUES(lock_num)+lock_num";
            $ret = $this->insert_multi_duplicate('goods_inv_lof', $inv_lof_new, $update_str);
            if ($ret['status'] < 1) {
                return $this->format_ret(-1, '', '锁定库存变动失败');
            }
        }


        $ret = $this->insert_exp('goods_inv_record', $inv_record);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', '库存锁定流水新增失败');
        }
        return $ret;
    }

}
