<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InvOpLockModel
 *
 * @author wq
 */
require_model('tb/TbModel');

class InvOpLockModel extends TbModel {

    private $detail_data = array();
    private $sell_record = array();
    private $sell_record_lof_data = array(); //锁定批次数据
    private $available_data = array(); //是否数据记录
    private $lock_sku_data = array();

    function check_detail_lock($sell_record, &$detail_data) {

        $this->sell_record = $sell_record;
        $this->detail_data = &$detail_data;



        $this->sell_record_lof_data = array(); //锁定批次数据
        $this->available_data = array(); //是否数据记录
        $sku_num_data = $this->get_sku_num_data($detail_data);
        $sku_data = array_keys($sku_num_data);



        $order_data = $this->check_is_lock_order($sell_record['shop_code'], $sell_record['store_code']);

        if (empty($order_data)) {
            return $this->format_ret(1);
        }
        //暂时缺少并发异常，重复处理
        $this->get_order_lock_num_data($sku_data, $order_data);


        if (!empty($this->lock_sku_data)) {

            $this->db->begin_trans();
            $status = $this->set_lock_detail();

            if ($status !== false) {
                $this->save_lof_detail();
                $this->save_available_data();
                $this->db->commit();
            } else {
                $this->db->rollback();
            }

            return $status === true ? $this->format_ret(2) : $this->format_ret(-1, '', '通过锁定单锁定仓库存出现并发异常！');
        } else {
            return $this->format_ret(1);
        }
    }

    /*
     * 检查店铺是否包含锁定单
     */

    private function check_is_lock_order($shop_code, $store_code) {
        static $shop_order_data = null;
        if (!isset($shop_order_data[$shop_code])) {
            $sql = "select record_code from stm_stock_lock_record where order_status=1 AND lock_obj=1 and  shop_code=:shop_code AND store_code=:store_code ";
            $data = $this->db->get_all($sql, array(':shop_code' => $shop_code, ':store_code' => $store_code));
            foreach ($data as $val) {
                $shop_order_data[$shop_code][] = $val['record_code'];
            }
        }
        return isset($shop_order_data[$shop_code]) ? $shop_order_data[$shop_code] : array();
    }

    private function get_sku_num_data($detail_data) {
        $sku_num_data = array();
        foreach ($detail_data as $val) {
            if (isset($sku_num_data[$val['sku']])) {
                $sku_num_data[$val['sku']] += $val['num'];
            } else {
                $sku_num_data[$val['sku']] = $val['num'];
            }
        }
        return $sku_num_data;
    }

    private function get_order_lock_num_data($sku_data, $order_data) {
        $record_code_str = "'" . implode("','", $order_data) . "'";
        $sku_str = "'" . implode("','", $sku_data) . "'";
        $sql = "select available_num as num,sku,record_code from stm_stock_lock_record_detail where record_code in ({$record_code_str}) AND sku in({$sku_str})";
        $data = $this->db->get_all($sql);
        $lock_sku_data = array();

        foreach ($data as $val) {

            if ($val['num'] > 0) {
                if (isset($lock_sku_data[$val['sku']]['num'])) {
                    $lock_sku_data[$val['sku']]['num'] += $val['num'];
                } else {
                    $lock_sku_data[$val['sku']]['num'] = $val['num'];
                }
                $lock_sku_data[$val['sku']]['record_data'][] = $val;
            }
        }
        $this->lock_sku_data = $lock_sku_data;
    }

    function set_lock_detail() {

        foreach ($this->detail_data as $key => $val) {
            $sku = $val['sku'];
            if (isset($this->lock_sku_data[$sku])) {
                $no_lock_num = $this->set_sku_lock_data($this->lock_sku_data[$sku], $val['num']);

                if ($no_lock_num < 0) {//异常
                    return false;
                }
                if ($no_lock_num == 0) {
                    unset($this->detail_data[$key]);
                    //更新订单明细
                } else {
                    $this->detail_data[$key]['num'] = $no_lock_num;
                }
                $lock_num = $val['num'] - $no_lock_num;
                $sql_detail = "update oms_sell_record_detail
                       set  lock_num=lock_num+{$lock_num} 
                        where sku=:sku AND sell_record_code=:sell_record_code and is_gift=:is_gift  AND lock_num+{$lock_num}<=num ";
                $s_values = array(
                    ':sku' => $sku,
                    ':sell_record_code' => $val['sell_record_code'],
                    ':is_gift' => $val['is_gift'],
                );

                $this->db->query($sql_detail, $s_values);
                $run_num = $this->affected_rows();
                if ($run_num < 1) {//出现并发异常
                    return false;
                }
            }
        }
        return true;
    }

    private function set_sku_lock_data($lock_sku_data, $num) {

        foreach ($lock_sku_data['record_data'] as $val) {
            $lock_num = ($val['num'] >= $num) ? $num : $val['num'];
            $status = $this->update_lock_order($val['record_code'], $val['sku'], $lock_num);
            if ($status < 0) { //异常
                return $status;
            }
            $num = $num - $lock_num;
            if ($num == 0) {
                break;
            }
        }

        return $num;
    }

    /**
     * 更新锁定单
     * @param type $record_code
     * @param type $sku
     * @param type $num
     * @return int
     */
    private function update_lock_order($record_code, $sku, $num) {
        $sql_record = "update stm_stock_lock_record_detail
                       set  release_num=release_num+{$num} ,available_num=available_num-{$num}
                        where sku=:sku AND record_code=:record_code and available_num>={$num} ";
        $this->db->query($sql_record, array(':record_code' => $record_code, ':sku' => $sku));
        $run_num = $this->affected_rows();
        if ($run_num < 1) {//出现并发异常
            return -1;
        }
        $sql = "select order_code,goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,occupy_type,num from b2b_lof_datail where order_code=:order_code AND  order_type=:order_type  AND sku=:sku AND occupy_type=1";
        $sql_values = array(
            ':order_code' => $record_code,
            ':order_type' => 'stm_stock_lock',
            ':sku' => $sku,
        );
        $lock_lof_data = $this->db->get_all($sql, $sql_values);

        if (empty($lock_lof_data)) {
            return -2;
        }
        if (count($lock_lof_data) > 1) {
            foreach ($lock_lof_data as $val) {

                $lock_num = $val['num'] > $num ? $num : $val['num'];
                $sql_lof = "update b2b_lof_datail
                            set  num=num-{$lock_num} ,fill_num=fill_num+{$lock_num}
                             where  order_code=:order_code AND  order_type=:order_type and num>={$lock_num} AND sku=:sku  AND lof_no=:lof_no  AND  occupy_type=1 ";
                $sql_values['lof_no'] = $val['lof_no'];
                $this->db->query($sql_lof, $sql_values);
                $run_num = $this->affected_rows();
                if ($run_num < 1) {//出现并发异常
                    return -3;
                }
                $num = $num - $lock_num;
                $this->set_lock_lof_data($val, $lock_num);
                if ($num == 0) {
                    break;
                }
            }
        } else {
            $sql_lof = "update b2b_lof_datail
                        set  num=num-{$num},fill_num=fill_num+{$num}
                         where  order_code=:order_code AND  order_type=:order_type and num>={$num} and sku=:sku  AND lof_no=:lof_no  AND  occupy_type=1 ";
            $sql_values['lof_no'] = $lock_lof_data[0]['lof_no'];
            $this->db->query($sql_lof, $sql_values);
            $run_num = $this->affected_rows();
            if ($run_num < 1) {//出现并发异常
                return -4;
            }

            $this->set_lock_lof_data($lock_lof_data[0], $num);
        }


        return 1;
    }

    private function set_lock_lof_data($lof_data, $num) {

        $lof_key = $this->get_lof_key($lof_data);

        if (!isset($this->sell_record_lof_data[$lof_key])) {
            $key_arr = array(
                'goods_code', 'spec1_code', 'spec2_code', 'sku', 'store_code', 'lof_no', 'production_date', 'occupy_type', 'record_code'
            );
            $new_lof = array();
            foreach ($lof_data as $key => $val) {
                if (in_array($key, $key_arr)) {
                    $new_lof[$key] = $val;
                }
            }
            $new_lof['record_type'] = 1;
            $new_lof['record_code'] = $this->sell_record['sell_record_code'];
            $new_lof['num'] = $num;

            $this->sell_record_lof_data[$lof_key] = $new_lof;
        } else {
            $this->sell_record_lof_data[$lof_key]['num'] += $num;
        }

        $order_key = $this->get_order_key($lof_data);

        if (!isset($this->available_data[$order_key])) {
            $this->available_data[$order_key] = array('record_code' => $lof_data['order_code'], 'sku' => $lof_data['sku'], 'num' => $num);
        } else {
            $this->available_data[$order_key]['num'] +=$num;
        }
    }

    private function save_lof_detail() {
        $update_str = " num = VALUES(num) ";
        $this->insert_multi_duplicate('oms_sell_record_lof', $this->sell_record_lof_data, $update_str);
    }

    private function save_available_data() {
        $data = array();
        $release_data = array();
        foreach ($this->available_data as $val) {
            if (!isset($data[$val['record_code']])) {
                $data[$val['record_code']] = array(
                    'record_code' => $val['record_code'],
                    'relation_code' => $this->sell_record['sell_record_code'],
                    'relation_type' => 'sell_record',
                    'inv_status' => 2,
                    'num' => $val['num'],
                    'describe' => '',
                    'add_time' => date('Y-m-d H:i:s'),
                );
                $release_data[$val['record_code']] = $val['num'];
            } else {
                $data[$val['record_code']]['num']+=$val['num'];
                $release_data[$val['record_code']] += $val['num'];
            }
            $key_arr = array('barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
            $data[$val['record_code']]['describe'] .= $sku_info['barcode'] . "({$val['num']}),";
        }
        $this->insert_multi_exp('stm_stock_lock_relation_record', $data);

        foreach ($release_data as $record_code => $release_num) {
            $sql = "update stm_stock_lock_record set  release_num=release_num+{$release_num}, available_num=available_num-{$release_num} where record_code='{$record_code}'   ";
            $this->db->query($sql);
        }
    }

    private function get_lof_key($new_lof) {
        return $new_lof['sku'] . "|" . $new_lof['lof_no'];
    }

    private function get_order_key($lof_data) {
        return $lof_data['sku'] . "|" . $lof_data['order_code'];
    }

}
