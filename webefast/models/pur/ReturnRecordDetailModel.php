<?php

/**
 * 库单详情管理相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');

class ReturnRecordDetailModel extends TbModel {

    function get_table() {
        return 'pur_return_record_detail';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        //退货单详情导出
        if ($filter['ctl_type'] === 'export' && $filter['ctl_export_conf'] === 'return_record_view' ) {
            $detail_result = load_model('pur/ReturnRecordModel')->get_detail_by_record_code($filter['record_code'], $filter['lof_status']);
            $ret_data['data'] = $detail_result;
            return $this->format_ret(1, $ret_data);
        }
        $sql_join = "";

        $sql_main = "FROM pur_return_record rl
		            INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		            INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		            INNER JOIN goods_sku r4 on r2.sku = r4.sku
		            WHERE  1=1  ";

        $sql_values = array();
        // record_code查询
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r2.record_code = :record_code )";
            $sql_values[':record_code'] = $filter['record_code'];
        } 
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name or r4.barcode LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }

        $select = 'r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,rl.is_store_out,rl.is_check,rl.rebate';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
//		print_r($data);exit;
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['refer_price'] = round($value['refer_price'], 3);
            $data['data'][$key]['price'] = round($value['price'], 3);
            $data['data'][$key]['rebate'] = round($value['rebate'], 3);
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['money'] = round($value['money'], 3);
            $data['data'][$key]['num_differ'] = $value['enotice_num'] - $value['num'];
            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);
            if ($status['status'] != 1) {
                $data['data'][$key]['price1'] = '****';
                $data['data'][$key]['price'] = '****';
                $data['data'][$key]['money'] = '****';
            }
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_page_lof($filter) {
        //退货单详情导出
        if ($filter['ctl_type'] === 'export' && $filter['ctl_export_conf'] === 'return_record_view' ) {
            $detail_result = load_model('pur/ReturnRecordModel')->get_detail_by_record_code($filter['record_code'], $filter['lof_status']);
            $ret_data['data'] = $detail_result;
            return $this->format_ret(1, $ret_data);
        }
        $sql_join = "";

        $sql_main = "FROM pur_return_record rl
		INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		INNER JOIN b2b_lof_datail r4 on r2.record_code = r4.order_code
		INNER JOIN goods_sku r5 on r2.sku = r5.sku
		WHERE  r2.sku = r4.sku and r4.order_type = :order_type  ";

        $sql_values = array();
        $sql_values[':order_type'] = 'pur_return';

        // record_code查询
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r2.record_code = :record_code )";
            $sql_values[':record_code'] = $filter['record_code'];
        }
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }

        $select = 'r2.sku,r4.id,r4.pid,r2.spec1_code,r2.spec2_code,r2.goods_code,r2.price,r2.record_code,r5.spec1_name,r5.spec2_name,rl.is_store_out,rl.is_check,r3.goods_name,r5.barcode,r4.num,r4.lof_no,r4.production_date,r4.init_num enotice_num,rl.rebate';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['refer_price'] = round($value['refer_price'], 3);
            $data['data'][$key]['price'] = round($value['price'], 3);
            $data['data'][$key]['rebate'] = round($value['rebate'], 3);
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['num_differ'] = $value['enotice_num'] - $value['num'];
            $value['money'] = $data['data'][$key]['price1'] * $value['num'];

            $data['data'][$key]['money'] = round($value['money'], 2);

            $name_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $value['spec2_code'], 'spec2_name');
            $data['data'][$key]['spec2_name'] = isset($name_arr['data']['spec2_name']) ? $name_arr['data']['spec2_name'] : '';
            $name_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $value['spec1_code'], 'spec1_name');
            $data['data'][$key]['spec1_name'] = isset($name_arr['data']['spec1_name']) ? $name_arr['data']['spec1_name'] : '';
            if ($status['status'] != 1) {
                $data['data'][$key]['price1'] = '****';
                $data['data'][$key]['price'] = '****';
                $data['data'][$key]['money'] = '****';
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_all(array('pid' => $id));

        filter_fk_name($data['data'], array('adjust_type|record_type', 'store_code|store'));
        return $data;
    }

    //根据单据编号查询
    function get_by_record_code($record_code) {
        $data = $this->get_all(array('record_code' => $record_code));
        return $data;
    }

    /**
     * 插入库存调整明细
     * @param array $ary_detail
     * @return array
     */
    function insert($ary_detail) {
        $status = $this->valid($ary_detail);
        if ($status != 1) {
            return $this->format_ret($status);
        }

        //如果规格1 规格2 不存在, 通过sku获取到规格1 规格2的代码和名称
        if (isset($ary_detail['sku']) && !empty($ary_detail['sku'])) {
            $info = load_model('prm/SkuModel')->get_spec_by_sku($ary_detail['sku']);
            if (!isset($info['goods_code'])) {
                return $this->format_ret(-1, array(), 'SKU信息不存在:' . $ary_detail['sku']);
            }
            $ary_detail['goods_id'] = $info['goods_id'];
            $ary_detail['goods_code'] = $info['goods_code'];
            $ary_detail['spec1_id'] = $info['spec1_id'];
            $ary_detail['spec1_code'] = $info['spec1_code'];
            $ary_detail['spec2_id'] = $info['spec2_id'];
            $ary_detail['spec2_code'] = $info['spec2_code'];
        } else {
            return $this->format_ret(-1, array(), 'SKU信息不存在:' . $ary_detail['sku']);
        }

        return parent::insert($ary_detail);
    }

    /**
     * 更新库存调整明细
     * @param array $ary_detail
     * @param array $where
     * @return array
     * @throws Exception
     */
    function update($ary_detail, $where) {
        //如果规格1 规格2 不存在, 通过sku获取到规格1 规格2的代码和名称
        if (isset($ary_detail['sku']) && !empty($ary_detail['sku'])) {
            $info = load_model('prm/SkuModel')->get_spec_by_sku($ary_detail['sku']);
            $ary_detail['goods_id'] = $info['goods_id'];
            $ary_detail['goods_code'] = $info['goods_code'];
            $ary_detail['spec1_id'] = $info['spec1_id'];
            $ary_detail['spec1_code'] = $info['spec1_code'];
            $ary_detail['spec2_id'] = $info['spec2_id'];
            $ary_detail['spec2_code'] = $info['spec2_code'];
        }
        return parent::update($ary_detail, $where);
    }

    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id) {
        $detail = $this->get_row(array('return_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'return_record_id');
            if (isset($record['data']['is_store_out']) && 1 == $record['data']['is_store_out']) {
                return $this->format_ret(false, array(), '单据已验收!不能删除明细');
            }
        }
        $result = parent::delete(array('return_record_detail_id' => $id));
        $this->mainWriteBack($detail['data']['pid']);
        return $result;
    }

    /**
     * 根据ID删除批次行数据
     * @param $id
     * @return array|void
     */
    function delete_lof($id) {
        $lof_data = $this->db->get_row("select * from b2b_lof_datail where id=:id", array(':id' => $id));
        if (empty($lof_data)) {
            return $this->format_ret(false, array(), '该批次不存在!不能删除');
        }

        $detail = $this->get_row(array('pid' => $lof_data['pid'], 'sku' => $lof_data['sku']));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'return_record_id');
            if (isset($record['data']['is_store_out']) && 1 == $record['data']['is_store_out']) {
                return $this->format_ret(false, array(), '单据已出库!不能删除明细');
            }
        }
        $this->begin_trans();
        $result = $this->format_ret(-1, array(), '单据删除异常！');
        $result = load_model('stm/GoodsInvLofRecordModel')->delete_lof($id); // 批次删除
        if ($result['status'] < 1) {
            $this->rollback();
            return $result;
        }

        if ($lof_data['num'] == $detail['data']['num']) {
            //$result = parent::delete(array('stock_adjust_record_detail_id'=>$id));
            $result = parent::delete(array('return_record_detail_id' => $detail['data']['return_record_detail_id']));
            $res = ($result['status'] < 1) ? FALSE : TRUE;
        } else {
            $res = $this->mainWriteBackDetail($lof_data['pid'], $lof_data['sku']);
        }
        if ($res === FALSE) {
            $this->rollback();
            return $result;
        }


        $res = $this->mainWriteBack($detail['data']['pid']);
        if ($res === FALSE) {
            $this->rollback();
            return $result;
        }
        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 主单据数据回写
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-11
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function mainWriteBackDetail($record_id, $sku, $order_type = 'pur_return') {
        $sql = "update {$this->table} set
    	num = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type),
    	money = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type )*price
    	where pid = :pid and sku = :sku";
        $res = $this->query($sql, array(':pid' => $record_id, ':sku' => $sku, ':order_type' => $order_type));

        return $res;
    }

    /**
     * 根据明细的record_code判断主表单据是否存在
     * @param string $value 值
     * @param string $field_name 字段名
     * @return array
     */
    private function is_exists($value, $field_name = 'record_code') {

        $m = load_model('pur/ReturnRecordModel');
        $ret = $m->get_row(array($field_name => $value));
        return $ret;
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
        if ($ret['status'] && !empty($ret['data'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证明细参数是否正确
     * @param array $data 明细单据
     * @param boolean $is_edit 是否为编辑
     * @return int
     */
    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['pid']) || !valid_input($data['pid'], 'required')))
            return RECORD_ERROR_CODE;
        return 1;
    }

    /**
     * 新增多条库存调整单明细记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-10-28
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function add_detail_action($pid, $ary_details) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'return_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '采购退货单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_store_out'] == 1) {
            return $this->format_ret(false, array(), '采购退货单已验收, 不能修改明细!');
        }
        $this->begin_trans();
        try {
            foreach ($ary_details as $ary_detail) {
                if ((!isset($ary_detail['num']) || $ary_detail['num'] == 0) && (!isset($ary_detail['enotice_num']) || $ary_detail['enotice_num'] == 0)) {
                    continue;
                }
                $ary_detail['pid'] = $pid;
                $ary_detail['record_code'] = $record['data']['record_code'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断

                $ary_detail['rebate'] = $record['data']['rebate'];
                // $ary_detail['refer_price'] = $ary_detail['price'];
                $ary_detail['price'] = $ary_detail['purchase_price'];
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['purchase_price'] * $ary_detail['rebate'];
                //判断SKU是否已经存在
                $check = $this->is_detail_exists($pid, $ary_detail['sku']);
                if ($check) {
                    //批次表里查出该单据数量和价格
                    $pici = load_model('stm/GoodsInvLofRecordModel')->get_detail_cnt('pur_return', $pid, $ary_detail['sku']);
                    $ary_detail['num'] = isset($pici[0]['cnt']) ? $pici[0]['cnt'] : '';
                    $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'] * $ary_detail['rebate'];
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
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    /**
     * 批次明细编辑
     * @param int $pid 主单据ID
     * @param array $detail 更新明细
     * @param $lof_info 批次数据
     */
    public function edit_detail_action_lof($pid, $detail, $lof_info) {
        $sql = "UPDATE b2b_lof_datail SET num='{$lof_info['num']}' WHERE order_type = 'pur_return' and order_code = '{$lof_info['record_code']}' AND sku = '{$detail['sku']}' AND lof_no = '{$lof_info['lof_no']}' ";
        $this->db->query($sql);
        $total_num = 0;
        $lof_sql = "SELECT sku,lof_no,production_date,num,init_num FROM b2b_lof_datail WHERE order_type = 'pur_return' AND order_code = :order_code AND sku = :sku";
        $lof_data = $this->db->get_all($lof_sql, array(":order_code" => $lof_info['record_code'], ":sku" => $detail['sku']));
        if (!empty($lof_data)) {
            foreach ($lof_data as $lof) {
                $total_num += $lof['num'];
            }
        }
        //敏感价格,重新赋值
        $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        if ($price_status['status'] != 1) {
            $sql = "SELECT price FROM {$this->table} WHERE pid=:pid AND sku=:sku";
            $where = array(':pid' => $pid, ':sku' => $detail['sku']);
            $old_price = $this->db->get_value($sql, $where);
            if (false !== $old_price){
                $detail['price'] = $old_price;
            }
        }
        $detail['num'] = $total_num;
        $ret = parent::update(array('num' => $detail['num'], 'money' => $detail['num'] * $detail['price'] * $detail['rebate']), array('pid' => $pid, 'sku' => $detail['sku']));
        $this->mainWriteBack($pid);
        return $ret;
    }

    /**
     * 编辑多条明细记录
     * @param int $pid 主单据ID
     * @param array $ary_details 单据明细数组
     * @return array 返回修改结果
     */
    public function edit_detail_action($pid, $ary_details) {
        $this->begin_trans();
        try {
            $is_lof = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
            $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
            foreach ($ary_details as $ary_detail) {
                $ary_detail['pid'] = $pid;
                //敏感价格,重新赋值
                if ($price_status['status'] != 1) {
                    $sql = "SELECT price FROM {$this->table} WHERE pid=:pid AND sku=:sku";
                    $where = array(':pid' => $pid, ':sku' => $ary_detail['sku']);
                    $old_price = $this->db->get_value($sql, $where);
                    if (false !== $old_price){
                        $ary_detail['price'] = $old_price;
                    }
                }
                //更新明细数据
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'] * $ary_detail['rebate'];
                $ret = $this->update($ary_detail, array('pid' => $pid, 'sku' => $ary_detail['sku']));
                if ($is_lof['lof_status'] == 0) {
                    $this->update_lof_detail($ary_detail['record_code'], $ary_detail['sku'], $ary_detail['num']);
                    if (1 != $ret['status']) {
                        return $ret;
                    }
                }
            }
            //回写数量和金额
            $this->mainWriteBack($pid);
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    function update_lof_detail($record_code, $sku, $num) {
        $sql = "select id from b2b_lof_datail where order_code=:record_code AND sku=:sku AND order_type='pur_return' ";
        $data = $this->db->get_all($sql, array(':record_code' => $record_code, 'sku' => $sku));
        $is_only = 0;
        foreach ($data as $val) {
            if ($is_only == 0) {
                $sql = "update b2b_lof_datail set num='{$num}',init_num='{$num}' where id='{$val['id']}' ";
                $is_only = 1;
            } else {
                $sql = "delete from b2b_lof_datail where id='{$val['id']}' ";
            }
            $this->db->query($sql);
        }
    }

    /**
     * 主单据数据回写
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-11
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function mainWriteBack($record_id) {
        //回写数量和金额
        $sql = "update pur_return_record set
                  pur_return_record.num = (select sum(num) from pur_return_record_detail where pid = :id),
                  pur_return_record.enotice_num = (select sum(enotice_num) from pur_return_record_detail where pid = :id),
                  pur_return_record.money = (select sum(money) from pur_return_record_detail where pid = :id)
                where pur_return_record.return_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }


    /**更新扫描数量
     * @param $request
     * @return array
     */
    function update_scan_num($request) {
        $ret = load_model('pur/ReturnRecordModel')->get_row(array('record_code' => $request['record_code']));
        $relation_code = $ret['data']['relation_code'];
        $sku = substr($request['id'], 8);
        $num = $request['num'];
        if ($relation_code) {
            $sql = "select num from pur_return_notice_record_detail where record_code = :relation_code and sku = :sku";
            $tz_num = $this->db->get_value($sql, array(":relation_code" => $relation_code, ":sku" => $sku));
            if ($num > $tz_num) {
                return $this->format_ret(-1, '', '更新数量超出采购退货通知单数量！');
            }
        }
        $detail = $this->get_row(array('record_code' => $request['record_code'], 'sku' => $sku));

        if (empty($detail['data'])) {
            return $this->format_ret(-1, '', '单据明细信息不存在');
        }
        $detail['data']['num'] = $num;
        $ret = $this->edit_detail_action($ret['data']['return_record_id'], array($detail['data']));
        if ($ret['status'] == 1) {
            return $this->format_ret(1, '', '更新成功');
        } else {
            return $this->format_ret(-1, '', '扫描采购退货更新单据明细数量失败');
        }
    }

    /**开启批次修改数量
     * @param $data
     * @return array
     */
    function update_scan_num_lof($data) {
        $ret = load_model('pur/ReturnRecordModel')->get_row(array('record_code' => $data['record_code']));
        $relation_code = $ret['data']['relation_code'];
        $id_arr = explode('_', $data['id']);
        $sku = $id_arr[2];
        if ($relation_code) {
            $sql = "select num from pur_return_notice_record_detail where record_code = :relation_code and sku = :sku";
            $tz_num = $this->db->get_value($sql, array(":relation_code" => $relation_code, ":sku" => $sku));
            if ($data['num'] > $tz_num) {
                return $this->format_ret(-1, '', '更新数量超出采购退货通知单数量！');
            }
        }
        $detail = $this->get_row(array('record_code' => $data['record_code'], 'sku' => $sku));
        if (empty($detail['data'])) {
            return $this->format_ret(-1, '', '单据明细信息不存在');
        }
        $ret = $this->update_detail_action_lof($ret['data']['return_record_id'], $detail['data'], $data);
        if ($ret['status'] == 1) {
            return $this->format_ret(1, '', '更新成功');
        } else {
            return $this->format_ret(-1, '', '采购退货单更新单据明细数量失败');
        }
    }

    /**
     * @param $pid
     * @param $detail
     * @param $lof_info
     * @return array
     */
    public function update_detail_action_lof($pid, $detail, $lof_info) {
        $sql = "UPDATE b2b_lof_datail SET num='{$lof_info['num']}' WHERE order_type = 'pur_return' and order_code = '{$lof_info['record_code']}' AND sku = '{$detail['sku']}' AND lof_no = '{$lof_info['lof_no']}' ";
        $this->db->query($sql);
        $ret = parent::update(array('num' => $lof_info['num'], 'money' => $lof_info['num'] * $detail['price'] * $detail['rebate']), array('pid' => $pid, 'sku' => $detail['sku']));
        $this->mainWriteBack($pid);
        return $ret;
    }

}
