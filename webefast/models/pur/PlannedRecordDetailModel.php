<?php

/**
 * 采购计划订单相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('pur');

class PlannedRecordDetailModel extends TbModel {

    function get_table() {
        return 'pur_planned_record_detail';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_join = "";
        $sql_main = "FROM pur_planned_record r1
		INNER JOIN {$this->table} r2 on r1.record_code = r2.record_code
		INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		INNER JOIN goods_sku r4 on r2.sku = r4.sku
		WHERE  1 ";

        $sql_values = array();
        $select .= 'r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,r1.is_check,r1.rebate as r1_rebate,r1.is_finish';
        if ($filter['ctl_type'] == 'export_detail') {
            if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
                $filter[$filter['keyword_type']] = trim($filter['keyword']);
            }
            $select .=',r1.supplier_code,r1.store_code,r1.init_code,r1.planned_time,r1.in_time,r1.pur_type_code record_type_code';
            $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
            $sql_main .= load_model('base/SupplierModel')->get_sql_purview_supplier('r1.supplier_code', $filter_supplier_code);
            $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code);

            //单据编号
            if (isset($filter['record_code']) && $filter['record_code'] != '') {
                $sql_main .= " AND (r1.record_code = :record_code )";
                $sql_values[':record_code'] = $filter['record_code'];
            }

            //计划日期
            if (isset($filter['planned_time_start']) && $filter['planned_time_start'] != '') {
                $sql_main .= " AND (r1.planned_time >= :planned_time_start )";
                $sql_values[':planned_time_start'] = $filter['planned_time_start'];
            }
            if (isset($filter['planned_time_end']) && $filter['planned_time_end'] != '') {
                $sql_main .= " AND (r1.planned_time <= :planned_time_end )";
                $sql_values[':planned_time_end'] = $filter['planned_time_end'];
            }

            //商品编码
            if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
                $sql_main .= " AND (r2.goods_code = :goods_code )";
                $sql_values[':goods_code'] = $filter['goods_code'];
            }

            //条码
            if (isset($filter['barcode']) && $filter['barcode'] != '') {
                $sql_main .= " AND (r4.barcode = :barcode )";
                $sql_values[':barcode'] = $filter['barcode'];
            }

            //入库期限
            if (isset($filter['in_time_start']) && $filter['in_time_start'] != '') {
                $sql_main .= " AND (r1.in_time >= :in_time_start )";
                $sql_values[':in_time_start'] = $filter['in_time_start'];
            }
            if (isset($filter['in_time_end']) && $filter['in_time_end'] != '') {
                $sql_main .= " AND (r1.in_time <= :in_time_end )";
                $sql_values[':in_time_end'] = $filter['in_time_end'];
            }
            //是否有差异单
            if (isset($filter['difference_models']) && $filter['difference_models'] != '') {
                if ($filter['difference_models'] == 1) {
                    $sql_main .= " AND (r2.num != r2.finish_num )";
                } else {
                    $sql_main .= " AND (r2.num = r2.finish_num )";
                }
            }
            //单据状态
            if (isset($filter['record_status']) && $filter['record_status'] != '') {
                switch ($filter['record_status']) {
                    case 'is_check_0':
                        $sql_main .= " AND (r1.is_check = 0) ";
                        break;
                    case 'is_check_1':
                        $sql_main .= " AND (r1.is_check = 1) ";
                        break;
                    case 'is_execute_0':
                        $sql_main .= " AND (r1.is_execute = 0) ";
                        break;
                    case 'is_execute_1':
                        $sql_main .= " AND (r1.is_execute = 1) ";
                        break;
                    case 'is_finish_0':
                        $sql_main .= " AND (r1.is_finish = 0) ";
                        break;
                    case 'is_finish_1':
                        $sql_main .= " AND (r1.is_finish = 1) ";
                        break;
                }
            }
            // 下单日期
            if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
                $sql_main .= " AND (r1.record_time >= :record_time_start )";
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            }
            if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
                $sql_main .= " AND (r1.record_time <= :record_time_end )";
                $sql_values[':record_time_end'] = $filter['record_time_end'];
            }
        } else {
            //单据编号
            if (isset($filter['record_code']) && $filter['record_code'] != '') {
                $sql_main .= " AND (r2.record_code = :record_code )";
                $sql_values[':record_code'] = $filter['record_code'];
            } else {
                return;
            }
            //商品货号或原因
            if (isset($filter['code_name']) && $filter['code_name'] != '') {
                $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name or r4.barcode LIKE :code_name)";
                $sql_values[':code_name'] = $filter['code_name'] . '%';
            }
            //是否有差异单
            if (isset($filter['difference_models']) && $filter['difference_models'] != '') {
                if ($filter['difference_models'] == 1) {
                    $sql_main .= " AND (r2.num != r2.finish_num )";
                } else {
                    $sql_main .= " AND (r2.num = r2.finish_num )";
                }
            }
        }

        //$sql_main .= "group by r2.sku";
        if (isset($filter['is_fenye']) && $filter['is_fenye'] == '1') {
            $filter['page_size'] = 1000;
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['refer_price'] = round($value['refer_price'], 2);
            $data['data'][$key]['price'] = round($value['price'], 2);
            $data['data'][$key]['rebate'] = round($value['rebate'], 2);           
            $data['data'][$key]['money'] = round($value['money'], 2);
            $data['data'][$key]['difference_num'] = $value['num'] - $data['data'][$key]['finish_num'];
            $data['data'][$key]['num_caigou'] = $data['data'][$key]['difference_num'];
            //进货单价
            $price1 = $data['data'][$key]['price'] * $data['data'][$key]['r1_rebate'];
            $data['data'][$key]['price1'] = $price1;
            if ($status['status'] != 1) {
                $data['data'][$key]['price1'] = '****';
                $data['data'][$key]['price'] = '****';
                $data['data'][$key]['money'] = '****';
                $data['data'][$key]['finish_money'] = '****';
            }
            if ($filter['ctl_type'] == 'export_detail') {
                if ($value['is_finish'] == 1) {
                    $data['data'][$key]['is_finish'] = '是';
                } else {
                    $data['data'][$key]['is_finish'] = '否';
                }
            }
            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);
        }
        if ($filter['ctl_type'] == 'export_detail') {
            filter_fk_name($data['data'], array('store_code|store', 'supplier_code|supplier', 'record_type_code|record_type'));
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 判断主单据是否存在
     */
    public function is_exists($value, $field_name = 'record_code') {
        $m = load_model('pur/PlannedRecordModel');
        $ret = $m->get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 判断单据是否存在明细
     */
    public function is_exists_detail($value, $field_name = 'record_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 新增多条明细记录
     */
    public function add_detail_action($pid, $ary_details) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'planned_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), 'PLAN_RELATION_ERROR_CODE!');
        }
        //判断主单据状态
        if ($record['data']['is_check'] == 1) {
            return $this->format_ret(false, array(), 'PLAN_RELATION_ERROR_CHECK!');
        }
        $this->begin_trans();
        try {
            foreach ($ary_details as $ary_detail) {
                if (!isset($ary_detail['num']) || $ary_detail['num'] == 0) {
                    continue;
                }
                $ary_detail['pid'] = $pid;
                $ary_detail['record_code'] = $record['data']['record_code'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['purchase_price'] * $record['data']['rebate'];
                $ary_detail['price'] = $ary_detail['purchase_price'];
                $ary_detail['rebate'] = $rebate;
                //判断SKU是否已经存在
                $check = $this->is_detail_exists($pid, $ary_detail['sku']);
                if ($check) {
                    //更新明细数据
                    $ret = $this->update($ary_detail, array(
                        'pid' => $pid, 'sku' => $ary_detail['sku']
                    ));
                } else {
                    //插入明细数据
                    $ret = $this->insert($ary_detail);
                }
                if (1 != $ret['status']) {
                    return $ret;
                }
            }
            //回写数量和金额
            $this->mainWriteBack($pid);
            $this->commit();
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "添加明细", 'module' => "planned_record", 'pid' => $pid);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), 'DATABASE_ERROR:' . $e->getMessage());
        }
    }

    //修改采购订单数量
    function do_edit_detail($pid, $data) {
        $sku = $data['sku'];
        $sql = "select num,finish_num,price from  pur_planned_record_detail  where pid='{$pid}' and sku = '{$sku}'";
        $record = $this->db->get_row("select * from pur_planned_record where planned_record_id =:pid", array(":pid" => $pid));
        $is_num = $this->db->get_all($sql);
        if ($is_num['0']['num'] != $data['num'] || $is_num['0']['price'] != $data['sell_price'] && $is_num['0']['finish_num'] == $data['finish_num']) {
            //var_dump($pid);var_dump($data);exit;
            //$record = $this->db->get_row("select * from pur_planned_record where planned_record_id =:pid", array(":pid" => $pid));
            //print_r(array('num'=>$data['num'],'money'=>$data['num']*$data['sell_price']*$data['rebate']));
            $ret = parent::update(array('num' => $data['num'], 'price' => $data['sell_price'], 'money' => $data['num'] * $data['sell_price'] * $record['rebate']), array('pid' => $pid, 'sku' => $data['sku']));
            $this->mainWriteBack($pid);
            return $ret;
        }
        //已确认的通知单只能修改完成数，因此不需要加价格的判断
        if ($is_num['0']['finish_num'] != $data['finish_num'] && $is_num['0']['num'] == $data['num']) {
            $ret = parent::update(array('finish_num' => $data['finish_num'],'finish_money'=> $data['finish_num'] * $is_num['0']['price'] * $record['rebate']), array('pid' => $pid, 'sku' => $data['sku']));
            $this->mainWriteBack($pid);
            //日志
            $is_finish = $record['is_finish'] == 1 ? '已完成' : '未完成';
            $is_check = $record['is_check'] == 1 ? '已确认' : '未确认';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "" . $is_check . "", 'finish_status' => "" . $is_finish . "", 'action_name' => "修改明细完成数", 'action_note' => "条形码:" . $data['barcode'] . " 完成数" . $is_num['0']['finish_num'] . "修改为" . $data['finish_num'] . "", 'module' => "planned_record", 'pid' => $pid);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            return $ret;
        }
    }

    /**
     * 主单据数据回写
     */
    public function mainWriteBack($record_id) {
        //回写数量和金额、完成数
        $sql = "update pur_planned_record set
		pur_planned_record.num = (select sum(num) from pur_planned_record_detail where pid = :id),
		pur_planned_record.finish_num = (select sum(finish_num) from pur_planned_record_detail where pid = :id),
		pur_planned_record.finish_money = (select sum(finish_money) from pur_planned_record_detail where pid = :id),
		pur_planned_record.money = (select sum(money) from pur_planned_record_detail where pid = :id)
		where pur_planned_record.planned_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }

    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id) {
        $detail = $this->get_row(array('planned_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'planned_record_id');
            if (isset($record['data']['is_check']) && 1 == $record['data']['is_check']) {
                return $this->format_ret(false, array(), 'PLAN_DELETE_ERROR_CHECK');
            }
        }
        $result = parent::delete(array('planned_record_detail_id' => $id));
        $this->mainWriteBack($detail['data']['pid']);
        return $result;
    }

    /**
     * 根据pID删除行数据
     */
    function delete_pid($pid) {
        $result = parent::delete(array('pid' => $pid));
        return $result;
    }

    /**
     * 回写完成数
     * @param   string  $record_code   主单据号
     * @param   string  $sku
     * @param   string  $finish_num
     */
    public function update_finish_num($record_code, $sku, $finish_num) {
        //$ret= parent:: update(array('finish_num' => $finish_num), array('record_code' => $record_code,'sku' => $sku));
        $sql = "update pur_planned_record_detail set finish_num = finish_num + {$finish_num} where record_code = :record_code and sku = :sku ";
        $res = $this->query($sql, array(':record_code' => $record_code, ':sku' => $sku));
        //回写完成金额
        //$sql = "SELECT * FROM pur_planned_record WHERE record_code=:record_code";
        //$sql_value[':record_code'] = $record_code;
        //$record = $this->db->get_row($sql, $sql_value);
        //$record_detail=$this->get_row(array('record_code' => $record_code, 'sku' => $sku));
        //$this->update_exp('pur_planned_record_detail', array('finish_money' => $record_detail['data']['finish_num'] * $record['rebate'] * $record_detail['data']['price']),array('record_code' => $record_code, 'sku' => $sku));
        $this->mainFinishWriteBack($record_code);
        return $res;
    }

    /**
     * 回写明细和主单的完成金额
     * @param $record_code
     */
    function update_finish_money($record_code) {
        $record = $this->get_row(array('record_code' => $record_code));
        $plan_record = $record['data'];
        $rebate = empty($plan_record['rebate']) ? 1 : $plan_record['rebate'];
        $sql = "UPDATE pur_planned_record_detail SET finish_money=finish_num*price*{$rebate} WHERE record_code=:record_code";
        $sql_value = array();
        $sql_value[':record_code'] = $record_code;
        $this->query($sql, $sql_value);
        //回写主单
        $this->mainFinishWriteBack($record_code);
    }


    //回写主表完成数
    public function mainFinishWriteBack($record_code) {
        //回写完成数量
        $sql = "update pur_planned_record set
		pur_planned_record.finish_num = (select sum(finish_num) from pur_planned_record_detail where record_code = :id),
		pur_planned_record.finish_money = (select sum(finish_money) from pur_planned_record_detail where record_code = :id)
		where pur_planned_record.record_code = :id";
        $res = $this->query($sql, array(':id' => $record_code));
        return $res;
    }

    /**
     * 根据明细的sku和主单据id,判断明细是否已经存在
     * @param   int     $pid    主单据ID
     * @param   string  $sku    SKU编号
     * @return  boolean 存在返回true
     */
    private function is_detail_exists($pid, $sku) {
        $ret = $this->get_row(array(
            'pid' => $pid,
            'sku' => $sku
        ));
        if ($ret['status'] == 1 && !empty($ret['data'])) {
            return true;
        } else {
            return false;
        }
    }

    function get_select_data($id, $select_sku_arr) {

        $sku_arr = array_keys($select_sku_arr);
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = " select goods_code,spec1_code,spec2_code,sku,refer_price,price,rebate,money,num,finish_num,(num-finish_num) as difference_num
            from  pur_planned_record_detail  where pid='{$id}' and sku in($sku_str)";
        $data = $this->db->get_all($sql,$sql_values);
        foreach ($data as &$val) {
            $val['num_caigou'] = $select_sku_arr[$val['sku']];
        }
        return $this->format_ret(1, $data);
    }

}
