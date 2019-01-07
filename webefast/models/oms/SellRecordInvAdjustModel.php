<?php

require_model('tb/TbModel');

class SellRecordInvAdjustModel extends TbModel {

    private $short_detail = array();

    /*
     * 调剂单据锁定库存
     */

    function inv_adjust($record_code, $sku, $by_record_code) {
        $short_data = $this->get_record_short_data($record_code, $sku);
        $reocrd_data = load_model('oms/SellRecordModel')->get_record_by_code($record_code);
        $by_record_data = load_model('oms/SellRecordModel')->get_record_by_code($by_record_code);
        $ret = $this->format_ret(1);
        if ($short_data['short_num'] < 1) {
            $ret = $this->format_ret(-1, '', '单据' . $record_code . '没有缺货或锁定异常！');
        }
        $detail_data = $this->get_by_adjust_detail($by_record_code, $sku);

        $this->begin_trans();

        $ret_adjust_detail = $this->set_inv_adjust_detail($record_code, $short_data, $detail_data);
        if ($ret_adjust_detail['status'] < 0) {
            $this->rollback();
            return $ret_adjust_detail;
        }

        $ret_adjust = $this->update_adjust_record_info($reocrd_data);
        if ($ret_adjust['status'] < 0) {
            $this->rollback();
            return $ret_adjust;
        }
        $ret_by_adjust = $this->update_by_adjust_record_info($by_record_data, $short_data);
        if ($ret_by_adjust['status'] < 0) {
            $this->rollback();
            return $ret_by_adjust;
        }
        //加日志
        $key_arr = array('barcode');
        $sku_info = load_model("goods/SkuCModel")->get_sku_info($sku, $key_arr);
        $actionNote = $sku_info['barcode'] . ' 调剂单据 ' . $by_record_code . ' 锁定 ' . $short_data['short_num'] . '件';
        load_model('oms/SellRecordActionModel')->add_action($record_code, '库存调剂', $actionNote);
        $actionNote = $sku_info['barcode'] . ' 被单据 ' . $record_code . ' 调剂锁定 ' . $short_data['short_num'] . '件';
        load_model('oms/SellRecordActionModel')->add_action($by_record_code, '库存调剂', $actionNote);

        $this->commit();
        return $ret_by_adjust;
    }

    /*
     * 设置调剂明细
     */

    private function set_inv_adjust_detail($record_code, $short_data, $detail_data) {

        $new_detail = array();
        $edit_detail = array();
        $del_detail = array();
        $short_num = $short_data['short_num'];
        foreach ($detail_data as $val) {
            if ($short_num < 1) {
                break;
            }

            if ($val['num'] > $short_num) {
                $row = $val;
                $row['num'] = $short_num;
                $row['deal_code'] = $short_data['deal_code'];
                $row['record_code'] = $record_code;
                unset($row['id']);
                unset($row['lastchanged']);
                $new_detail[] = $row;

                $val['adjust_num'] = $short_num;
                $short_num = 0;
                $edit_detail[] = $val;
            } else {  //多批次情况
                $row = $val;
                $short_num -=$val['num'];

                $row['deal_code'] = $short_data['deal_code'];
                $row['record_code'] = $record_code;
                unset($row['id']);
                unset($row['lastchanged']);
                $new_detail[] = $row;

                $del_detail[] = $val;
            }
        }

        if ($short_num > 0) {
            return $this->format_ret(-1, '', '调剂单据锁定库存不足');
        }

        return $this->set_inv_detail_lof($new_detail, $edit_detail, $del_detail);
    }

    /*
     * 更新用来调剂成功单据信息
     */

    private function update_by_adjust_record_info($reocrd_data, $short_data) {
        $sql = "select * from oms_sell_record_detail where sell_record_code =:sell_record_code AND sku=:sku AND lock_num>0";
        $sql_value = array(
            ':sell_record_code' => $reocrd_data['sell_record_code'],
            ':sku' => $short_data['sku'],
        );
        $detail_data = $this->db->get_all($sql, $sql_value);
        if (empty($detail_data)) {
            return $this->format_ret(-1, '', $reocrd_data['sell_record_code'] . '单据信息变化，不能调剂');
        }
        $shor_num = $short_data['short_num'];
        foreach ($detail_data as $val) {
            if ($shor_num == 0) {
                break;
            }
            $adjust_num = 0;
            if ($val['lock_num'] > $shor_num) {
                $adjust_num = $shor_num;
                $shor_num = 0;
            } else {
                $shor_num -=$val['lock_num'];
                $adjust_num = $val['lock_num'];
            }
            $sql_update = "update oms_sell_record_detail set lock_num = lock_num-{$adjust_num}  where sell_record_detail_id=:sell_record_detail_id"
                    . " AND lock_num=:lock_num AND num=:num";
            $update_value = array(
                ':sell_record_detail_id' => $val['sell_record_detail_id'],
                ':lock_num' => $val['lock_num'],
                ':num' => $val['num'],
            );
            $this->db->query($sql_update, $update_value);
            $run_num = $this->affected_rows();
            if ($run_num != 1) {
                return $this->format_ret(-1, '', $val['sell_record_code'] . '单据信息变化，不能调剂');
            }
        }

        $sql = "select count(1) from oms_sell_record_detail where sell_record_code =:sell_record_code AND num=lock_num";

        $num = $this->db->get_value($sql, array(':sell_record_code' => $reocrd_data['sell_record_code']));

        $lock_inv_status = ($num > 0) ? 2 : 3;
        $pay_status = $reocrd_data['pay_type'] == 'cod' ? 0 : 2;
        $lastchanged = date('Y-d-m H:i:s');
        $sql = "update oms_sell_record set lock_inv_status='{$lock_inv_status}',lastchanged='{$lastchanged}' where sell_record_code =:sell_record_code "
                . " AND order_status = 0 AND shipping_status = 0 AND pay_status={$pay_status} ";
        $this->db->query($sql, array(':sell_record_code' => $reocrd_data['sell_record_code']));
        $run_num = $this->affected_rows();
        if ($run_num != 1) {
            return $this->format_ret(-1, '', $reocrd_data['sell_record_code'] . '单据信息变化，不能调剂');
        }

        return $this->format_ret(1);
    }

    /*
     * 更新调剂成功单据信息
     */

    private function update_adjust_record_info($reocrd_data) {

        $record_code = $reocrd_data['sell_record_code'];
        foreach ($this->short_detail as $val) {
            $sql_update = "update oms_sell_record_detail set lock_num = num where "
                    . " sell_record_code =:sell_record_code AND deal_code=:deal_code AND sku=:sku AND is_gift=:is_gift"
                    . " AND  num=:num AND lock_num=:lock_num ";
            $update_value = array(
                ':sell_record_code' => $record_code,
                ':deal_code' => $val['deal_code'],
                ':sku' => $val['sku'],
                ':is_gift' => $val['is_gift'],
                ':num' => $val['num'],
                ':lock_num' => $val['lock_num'],
            );
            $this->db->query($sql_update, $update_value);
            $run_num = $this->affected_rows();
            if ($run_num != 1) {
                return $this->format_ret(-1, '', $record_code . '单据信息变化，不能调剂');
            }
        }
        $sql = "select count(1) from oms_sell_record_detail where sell_record_code =:sell_record_code AND num>lock_num";
        $num = $this->db->get_value($sql, array(':sell_record_code' => $record_code));

        $lock_inv_status = ($num > 0) ? 2 : 1;
        $pay_status = $reocrd_data['pay_type'] == 'cod' ? 0 : 2;
        $lastchanged = date('Y-d-m H:i:s');
        $sql = "update oms_sell_record set lock_inv_status='{$lock_inv_status}',lastchanged='{$lastchanged}' where sell_record_code =:sell_record_code  "
                . " AND order_status = 0 AND shipping_status = 0 AND pay_status={$pay_status} ";
        $this->db->query($sql, array(':sell_record_code' => $record_code));
        $run_num = $this->affected_rows();

        if ($run_num != 1) {
            return $this->format_ret(-1, '', $record_code . '单据信息变化，不能调剂');
        }

        return $this->format_ret(1);
    }

    /*
     * 保存条件批次明细
     */

    private function set_inv_detail_lof(&$new_detail, &$edit_detail, &$del_detail) {

        //批次明细处理
        $update_str = " num =  VALUES(num)+num ";
        $this->insert_multi_duplicate("oms_sell_record_lof", $new_detail, $update_str);

        if (!empty($del_detail)) {
            foreach ($del_detail as $val) {
                $sql_del = "delete from  oms_sell_record_lof "
                        . "where record_code=:record_code AND record_type=:record_type AND  sku=:sku AND lof_no=:lof_no AND num=:num";
                $del_value = array(
                    ':record_code' => $val['record_code'],
                    ':record_type' => $val['record_type'],
                    ':sku' => $val['sku'],
                    ':lof_no' => $val['lof_no'],
                    ':num' => $val['num']
                );
                $this->db->query($sql_del, $del_value);
                $run_num = $this->affected_rows();
                if ($run_num != 1) {
                    return $this->format_ret(-1, '', $val['record_code'] . '单据锁定商品已经变化');
                }
            }
        }

        if (!empty($edit_detail)) {
            foreach ($edit_detail as $val) {
                $sql_update = "update oms_sell_record_lof set num=num-{$val['adjust_num']} "
                        . " where record_code=:record_code AND record_type=:record_type AND  sku=:sku AND lof_no=:lof_no AND num=:num AND occupy_type=:occupy_type ";
                $update_value = array(
                    ':record_code' => $val['record_code'],
                    ':record_type' => $val['record_type'],
                    ':sku' => $val['sku'],
                    ':lof_no' => $val['lof_no'],
                    ':num' => $val['num'],
                    ':occupy_type' => $val['occupy_type'],
                );
                $this->db->query($sql_update, $update_value);
                $run_num = $this->affected_rows();
                if ($run_num != 1) {
                    return $this->format_ret(-1, '', $val['record_code'] . '单据锁定商品已经变化');
                }
            }
        }
        return $this->format_ret(1);
    }

    /*
     * 获取缺货信息
     */

    function get_record_short_data($record_code, $sku) {
        $sql = "select num,lock_num,deal_code,sku,is_gift from oms_sell_record_detail where  sell_record_code=:sell_record_code AND sku=:sku AND num>lock_num ";
        $data = $this->db->get_all($sql, array(':sell_record_code' => $record_code, ':sku' => $sku));

        $short_num = 0;
        $deal_code_arr = array();
        $this->short_detail = array();
        foreach ($data as $val) {
            $short_num +=$val['num'] - $val['lock_num'];
            $deal_code_arr[] = $val['deal_code'];
            $this->short_detail[] = $val;
        }
        $deal_code_arr = array_unique($deal_code_arr);
        return array('short_num' => $short_num, 'deal_code' => implode(",", $deal_code_arr), 'sku' => $sku);
    }

    function get_record_short_detail($record_code) {
        $sql = "select sum(lock_num) as lock_num,sum(num) as num,sku,goods_code from oms_sell_record_detail where  sell_record_code=:sell_record_code AND num>lock_num  group by sku";
        $data = $this->db->get_all($sql, array(':sell_record_code' => $record_code));

        foreach ($data as &$val) {
            $key_arr = array('barcode', 'goods_code', 'spec1_name', 'spec2_name');
            $sku_info = load_model("goods/SkuCModel")->get_sku_info($val['sku'], $key_arr);
            $val = array_merge($val, $sku_info);
        }
        return $this->format_ret(1, $data);
    }

    /*
     * 获取调剂明细信息
     */

    function get_by_adjust_detail($record_code, $sku) {
        $sql = "select * from oms_sell_record_lof where  record_code=:record_code AND sku=:sku AND occupy_type=1 order by num desc ";
        return $this->db->get_all($sql, array(':record_code' => $record_code, ':sku' => $sku));
    }

    function get_adjust_record_list($filter) {

        $sql_main = "FROM oms_sell_record r "
                . "  INNER JOIN oms_sell_record_detail d ON r.sell_record_code=d.sell_record_code"
                . " WHERE 1  AND r.order_status = 0 AND r.shipping_status = 0 AND d.lock_num>0 ";
        $record_data = $reocrd_data = load_model('oms/SellRecordModel')->get_record_by_code($filter['sell_record_code']);

        $sql_main .= " AND  r.sell_record_code<>:sell_record_code  ";
        $sql_values[':sell_record_code'] = isset($filter['sell_record_code']) ? $filter['sell_record_code'] : '';

        $sql_main .= " AND d.sku = :sku  ";
        $sql_values[':sku'] = isset($filter['sku']) ? $filter['sku'] : '';
        
        $sql_main .= " AND r.store_code = :store_code  ";
        $sql_values[':store_code'] = $record_data['store_code'];


        $short_num = $filter['short_num'];



        $select = 'r.sell_record_code,r.deal_code_list,r.pay_time,r.plan_send_time,r.record_time,r.buyer_name,d.goods_code,d.sku,sum(d.lock_num) as num ';

        $sql_main.=" group by sell_record_code,sku having  num>={$short_num} order by plan_send_time,pay_time,record_time  desc ";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

        $ret_status = OP_SUCCESS;

        return $this->format_ret($ret_status, $data);
    }

}
