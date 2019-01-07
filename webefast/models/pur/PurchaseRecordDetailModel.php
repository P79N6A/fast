<?php

/**
 * 库单详情管理相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');

class PurchaseRecordDetailModel extends TbModel {

    function get_table() {
        return 'pur_purchaser_record_detail';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";

        $sql_main = "FROM pur_purchaser_record rl
		            INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		            INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		            LEFT JOIN goods_sku r4 on r2.sku = r4.sku
		            WHERE 1=1 ";


        $sql_values = array();
        // record_code查询
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
                $sql_main .= " AND (r2.num != r2.notice_num )";
            } else {
                $sql_main .= " AND (r2.num = r2.notice_num )";
            }
        }
        $select = 'r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,rl.is_check_and_accept,rl.is_check,rl.rebate';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
//		print_r($data);exit;
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['lof_num'] = $data['data'][$key]['num'];
            //$data['data'][$key]['price'] = round($value['price'],2);
            //$data['data'][$key]['rebate'] = round($value['rebate'],2);
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = str_replace(',','', number_format($data['data'][$key]['price1'], 3));//处理千位情况
//            $value['money'] = $data['data'][$key]['price1'] * $value['num'];
//            $data['data'][$key]['money'] = round($value['money'],3);
            $data['data'][$key]['num_differ'] = $value['notice_num'] - $value['num'];
            $name_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $value['spec2_code'], 'spec2_name');
            $data['data'][$key]['spec2_name'] = isset($name_arr['data']['spec2_name']) ? $name_arr['data']['spec2_name'] : '';
            $name_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $value['spec1_code'], 'spec1_name');
            $data['data'][$key]['spec1_name'] = isset($name_arr['data']['spec1_name']) ? $name_arr['data']['spec1_name'] : '';

            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);
            if ($status['status'] != 1) {
                $data['data'][$key]['price1'] = '****';
                $data['data'][$key]['price'] = '****';
                $data['data'][$key]['money'] = '****';
                $data['data'][$key]['refer_price'] = '****';
            }
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //批次查询
    function get_by_page_lof($filter) {
        $sql_join = "";

        $sql_main = "FROM pur_purchaser_record rl
		INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		INNER JOIN b2b_lof_datail r4 on r2.record_code = r4.order_code
		LEFT JOIN goods_sku r5 on r2.sku = r5.sku
		WHERE  r2.sku = r4.sku and r4.order_type = :order_type  ";

        $sql_values = array();
        $sql_values[':order_type'] = 'purchase';

        // record_code查询
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r2.record_code = :record_code )";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            return;
        }
        //是否有差异单
        if (isset($filter['difference_models']) && $filter['difference_models'] != '') {
            if ($filter['difference_models'] == 1) {
                $sql_main .= " AND (r2.num != r2.notice_num )";
            } else {
                $sql_main .= " AND (r2.num = r2.notice_num )";
            }
        }
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name or r5.barcode LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }

        $select = 'r4.id,r4.pid,r2.*,r3.goods_name,r5.barcode,rl.is_check_and_accept,rl.is_check,rl.rebate,r4.num as lof_num,r4.lof_no,r4.production_date';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //		print_r($data);exit;
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($data['data'] as $key => $value) {

            //$data['data'][$key]['price'] = round($value['price'],2);
            //$data['data'][$key]['rebate'] = round($value['rebate'],2);
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = number_format($data['data'][$key]['price1'], 3);
            $value['money'] = $data['data'][$key]['price1'] * $value['num'];

            $data['data'][$key]['money'] = number_format($value['money'], 3);
            $data['data'][$key]['num_differ'] = $value['notice_num'] - $value['lof_num'];
            $name_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $value['spec2_code'], 'spec2_name');
            $data['data'][$key]['spec2_name'] = isset($name_arr['data']['spec2_name']) ? $name_arr['data']['spec2_name'] : '';
            $name_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $value['spec1_code'], 'spec1_name');
            $data['data'][$key]['spec1_name'] = isset($name_arr['data']['spec1_name']) ? $name_arr['data']['spec1_name'] : '';

            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);
            if ($status['status'] != 1) {
                $data['data'][$key]['price1'] = '****';
                $data['data'][$key]['price'] = '****';
                $data['data'][$key]['money'] = '****';
                $data['data'][$key]['refer_price'] = '****';
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

    //通灵唯一码采购入库
   function insert_tl($data) {
        $ret = $this->insert_multi($data,true);
        return $ret;
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
        $detail = $this->get_row(array('purchaser_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'purchaser_record_id');
            if (isset($record['data']['is_check_and_accept']) && 1 == $record['data']['is_check_and_accept']) {
                return $this->format_ret(false, array(), '单据已验收!不能删除明细');
            }
        }
        $result = parent::delete(array('purchaser_record_detail_id' => $id));
//        $this->mainWriteBack($detail['data']['pid']);
        return $result;
    }

    //删除批次
    function delete_lof($id) {
        $sql = "SELECT pid,sku,num FROM b2b_lof_datail WHERE id=:id";
        $lof_data = $this->db->get_row($sql, array(':id' => $id));
        if (empty($lof_data)) {
            return $this->format_ret(false, array(), '批次明细异常，不能删除');
        }

        $detail = $this->get_row(array('pid' => $lof_data['pid'], 'sku' => $lof_data['sku']));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'purchaser_record_id');
            if (isset($record['data']['is_check_and_accept']) && 1 == $record['data']['is_check_and_accept']) {
                return $this->format_ret(false, array(), '单据已验收!不能删除明细');
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
            $result = parent::delete(array('purchaser_record_detail_id' => $detail['data']['purchaser_record_detail_id']));
            $res = ($result['status'] < 1) ? FALSE : TRUE;
        } else {
            $res = $this->mainWriteBackDetail($lof_data['pid'], $lof_data['sku']);
        }
        if ($res === FALSE) {
            $this->rollback();
            return $result;
        }


        //$res = $this->mainWriteBack($detail['data']['pid']);
        if ($res === FALSE) {
            $this->rollback();
            return $result;
        }
        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 主详情单据数据回写
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function mainWriteBackDetail($record_id, $sku, $order_type = 'purchase') {
        $sql = "update {$this->table} set
    	num = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type),
    	money = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type)*price
    	where pid = :pid and sku = :sku";
        $res = $this->query($sql, array(':pid' => $record_id, ':sku' => $sku, ':order_type' => $order_type));

        return $res;
    }

    public function mainWriteBack($record_code) {
        $sql = 'UPDATE pur_purchaser_record pr,(SELECT SUM(notice_num) num,SUM(num) finish_num,SUM(money) money FROM pur_purchaser_record_detail WHERE record_code=:_code) temp SET pr.num=temp.num,pr.finish_num=temp.finish_num,pr.money=temp.money WHERE pr.record_code=:_code';
        
        return $this->query($sql, array(':_code' => $record_code));
    }

    /**
     * 根据明细的record_code判断主表单据是否存在
     * @param string $value 值
     * @param string $field_name 字段名
     * @return array
     */
    private function is_exists($value, $field_name = 'record_code') {
        $ret = load_model('pur/PurchaseRecordModel')->get_row(array($field_name => $value));
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
     * 新增多条明细记录
     * @param string $record_code 单据号
     * @param array $ary_details 单据明细数组
     * @param string $store_code 仓库代码
     * @return array 返回新增结果
     */
    public function add_detail_action($record_code, $ary_details, $store_code = '') {
        //判断主单据是否存在
        $record = load_model('pur/PurchaseRecordModel')->is_exists($record_code);
        $record = $record['data'];
        if (empty($record)) {
            return $this->format_ret(false, array(), '采购入库单明细所关联的主单据不存在!');
        }
        //判断主单据验收状态
        if ($record['is_check_and_accept'] == 1) {
            return $this->format_ret(false, array(), '采购入库单已验收, 不能修改明细!');
        }
        $pid = $record['purchaser_record_id'];

        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        $lof_status = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        if ($lof_status == 1) {
            $sku_arr = array();
            foreach ($ary_details as $key => $detail) {
                if (in_array($detail['sku'], $sku_arr)) {
                    $k = array_search($detail['sku'], $sku_arr);
                    $ary_details[$k]['num'] += $detail['num'];
                    unset($ary_details[$key]);
                } else {
                    $sku_arr[$key] = $detail['sku'];
                }
            }
        }
        $this->begin_trans();
        try {
            foreach ($ary_details as $ary_detail) {
                //$ary_detail['notice_num'] = $ary_detail['num'];
                //$ary_detail['num'] = 0;
                $ary_detail['pid'] = $pid;
                $ary_detail['record_code'] = $record['record_code'];
                $ary_detail['rebate'] = $record['rebate'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
                if (isset($ary_detail['sell_price'])) {
                    $ary_detail['money'] = $ary_detail['num'] * $ary_detail['purchase_price'] * $ary_detail['rebate'];
                    $ary_detail['price'] = $ary_detail['purchase_price'];
                    $ary_detail['refer_price'] = $ary_detail['sell_price'];
                } else {
                    $ary_detail['money'] = $ary_detail['price'] * $ary_detail['num'] * $ary_detail['rebate'];
                    $ary_detail['price'] = $ary_detail['price'];
                    $ary_detail['refer_price'] = $ary_detail['refer_price'];
                }
                //判断SKU是否已经存在
                $detail = $this->get_row(array('record_code' => $record_code, 'sku' => $ary_detail['sku']));
                if ($detail['status'] == 1) {
                    //批次表里查出该单据数量和价格
                    $pici = load_model('stm/GoodsInvLofRecordModel')->get_detail_cnt('purchase', $pid, $ary_detail['sku']);

                    //如果含有两条批次并且未开启批次的情况下
                    //删除两条批次 并重新插入一条数量为导入数量的批次
                    if (count($pici) > 1 && $lof_status == 0) {
                        $sql = "delete from b2b_lof_datail where order_type='purchase' and pid={$pid} and sku='" . $ary_detail['sku'] . "'";
                        $ret = $this->query($sql);
                        //插入一条批次信息
                        $detail_data_lof = array();
                        $val = array();
                        if (intval($ary_detail['num']) > 0) {
                            $val['num'] = $ary_detail['num'];
                            $val['notice_num'] = $ary_detail['num'];
                            $val['init_num'] = $ary_detail['num'];
                            $detail_data_lof[] = $val;
                        }
                        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $store_code, 'purchase', $detail_data_lof);
                       if ($ret['status']<1) {
                            return $ret;
                        }
                    }
                    //开启批次，明细SKU数量=SKU批次数量之和
                    if ($lof_status == 1) {
                        $ary_detail['num'] = $pici[0]['cnt'];
                    }
                    $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'] * $ary_detail['rebate'];
                    //更新明细数据
                    $ret = $this->update($ary_detail, array(
                        'pid' => $pid, 'sku' => $ary_detail['sku']
                    ));
                } else {
                    //插入明细数据
                    $ret = $this->insert($ary_detail);
                }
                if ($ret['status'] != 1) {
                    return $ret;
                }
            }
            $this->mainWriteBack($record_code);
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    function update_scan_num($record_code, $num, $id) {
        $ret = load_model('pur/PurchaseRecordModel')->get_row(array('record_code' => $record_code));
        $relation_code = $ret['data']['relation_code'];
        $sku = substr($id, 8);
       // $sku = $this->db->get_value("select sku from goods_sku where barcode = :barcode", array(":barcode" => $barcode));
        if ($relation_code) {
            $sql = "select num from pur_order_record_detail where record_code = :record_code and sku = :sku";
            $tz_num = $this->db->get_value($sql, array(":record_code" => $relation_code, ":sku" => $sku));
            if ($num > $tz_num) {
                return $this->format_ret(-1, '', '更新数量超出采购通知单数量！');
            }
        }
        $detail = $this->get_row(array('record_code' => $record_code, 'sku' => $sku));

        if (empty($detail['data'])) {
            return $this->format_ret(-1, '', '单据明细信息不存在');
        }
        $detail['data']['num'] = $num;
//     	$sql = "select * from  set num = {$num} where record_code = '{$record_code}' and sku='{$sku}' ";
//     	$ret = $this->query($update_sql);
        $ret = $this->edit_detail_action($ret['data']['purchaser_record_id'], $detail['data']);
        if ($ret) {
            return $this->format_ret(1, '', '更新成功');
        } else {
            return $this->format_ret(-1, '', '扫描入库更新单据明细数量失败');
        }
    }

    function update_scan_num_lof($data) {
        $ret = load_model('pur/PurchaseRecordModel')->get_row(array('record_code' => $data['record_code']));
//    	$relation_code = $ret['data']['relation_code'];

        $id_arr = explode('_', $data['id']);
        $sku = $id_arr[2];
      //  $sku = $this->db->get_value("select sku from goods_sku where barcode = :barcode", array(":barcode" => $barcode));
        $detail = $this->get_row(array('record_code' => $data['record_code'], 'sku' => $sku));
        if (empty($detail['data'])) {
            return $this->format_ret(-1, '', '单据明细信息不存在');
        }


        $ret = $this->edit_detail_action_lof($ret['data']['purchaser_record_id'], $detail['data'], $data);
        if ($ret['status'] == 1) {
            return $this->format_ret(1, '', '更细成功');
        } else {
            return $this->format_ret(-1, '', '扫描入库更新单据明细数量失败');
        }
    }

    public function edit_detail_action_lof($pid, $detail, $lof_info) {
        $sql = "update b2b_lof_datail set num='{$lof_info['num']}' where order_type = 'purchase' and order_code = '{$lof_info['record_code']}' and sku = '{$detail['sku']}' and lof_no = '{$lof_info['lof_no']}' ";
        $this->db->query($sql);
        $total_num = 0;
        $lof_sql = "select sku,lof_no,production_date,num,init_num from b2b_lof_datail where order_type = 'purchase' and order_code = :order_code and sku = :sku";
        $lof_data = $this->db->get_all($lof_sql, array(":order_code" => $lof_info['record_code'], ":sku" => $detail['sku']));
        if (!empty($lof_data)) {
            foreach ($lof_data as $lof) {
                $total_num += $lof['num'];
            }
        }
        $detail['num'] = $total_num;
        $this->begin_trans();
        $ret = parent::update(array('num' => $detail['num'], 'money' => $detail['num'] * $detail['price'] * $detail['rebate']), array('pid' => $pid, 'sku' => $detail['sku']));
        if($ret == false) {
            $this->rollback();
            return $this->format_ret(-1, '', '操作失败');
        }
        //回写主表金额
        $ret2 = load_model('pur/PurchaseRecordModel')->record_finish_num($is_num['record_code']);
        if($ret2 == false) {
            $this->rollback();
            return $this->format_ret(-1, '', '回写主表金额失败');
        }
        $this->commit();
        return $ret;
    }

    /**
     * 编辑指定明细字段
     * @param int $pid 主单据ID
     * @param array $data 更新数据
     * @return array 更细结果
     */
    public function edit_detail_action($pid, $data) {
        $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $sql = "select num,money,price,record_code from  pur_purchaser_record_detail  where pid='{$pid}' and sku = '{$data['sku']}'";
        $is_num = $this->db->get_row($sql);
        $record = load_model('pur/PurchaseRecordModel')->get_by_id($pid);
        if($is_num['money'] != $data['money'] && $data['price'] == $is_num['price'] && $data['num'] == $is_num['num'] ){
            if ($data['num'] == 0 && $record['data']['is_check_and_accept'] == 0) {
                $ret = array('status' => '-1', 'data' => '', 'message' => '实际入库数量为0,不允许修改金额');
                return $ret;
            }
            //已验收时数量不能修改
            if ($record['data']['is_check_and_accept'] == 1) {
                $data['num'] = $is_num['num'];
            }
            $up_data = array( 'money' => $data['money'], 'price' => round($data['money'] / $data['rebate'] / $data['num'],2));
        }elseif($data['price'] != $is_num['price'] && $price_status['status']==1){
            $up_data = array('num' => $data['num'], 'price' => $data['price'], 'money' => $data['num'] * $data['price'] * $data['rebate']);
        } elseif ($data['num'] != $is_num['num'] && $record['data']['is_check_and_accept'] == 0) {
            $up_data = array('num' => $data['num'], 'price' => $is_num['price'], 'money' => $data['num'] * $is_num['price'] *$data['rebate']);
        }else{
            $up_data = array('num' => $is_num['num'], 'price' => $is_num['price']);
        }
        if (isset($data['notice_num'])) {
            $up_data['notice_num'] = $data['notice_num'];
        }
        $this->begin_trans();
        $ret = parent::update($up_data, array('pid' => $pid, 'sku' => $data['sku']));
        if($ret == false) {
            $this->rollback();
            return $this->format_ret(-1, '', '修改失败');
        } 
        //回写主表金额
        $ret2 = load_model('pur/PurchaseRecordModel')->record_finish_num($is_num['record_code']);
        if($ret2 == false) {
            $this->rollback();
            return $this->format_ret(-1, '', '回写主表金额失败');
        }
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');

        if ($ret_arr['lof_status'] == 0) {
            $this->update_lof_detail($data['record_code'], $data['sku'], $data['num']);
        }
        $this->commit();
        return $ret;
    }

    function update_lof_detail($record_code, $sku, $num) {
        if ($num == 0) {
            $sql = "delete from b2b_lof_datail where order_type='purchase' and order_code='{$record_code}' and sku='{$sku}'";
            $this->db->query($sql);
            return $this->format_ret(1);
        } else {
            $sku_arr = array('goods_code', 'spec1_code', 'spec2_code', 'sku');
            $detail_lof = load_model('goods/SkuCModel')->get_sku_info($sku, $sku_arr);
            $ret = load_model('pur/PurchaseRecordModel')->get_row(array('record_code' => $record_code));
            $record_data = $ret['data'];
            $detail_lof['num'] = $num;
            $detail_lof['notice_num'] = $num;
            $detail_lof['init_num'] = $num;

            return load_model('stm/GoodsInvLofRecordModel')->add_detail_action($record_data['purchaser_record_id'], $record_data['store_code'], 'purchase', array($detail_lof));
        }
    }

    /**
     * 获取系统的sku_id
     */
    function  get_sku_id_by_pid($purchaser_record_id){
        $sql="SELECT r1.sku_id,r2.num FROM goods_sku r1 LEFT JOIN pur_purchaser_record_detail r2 ON r1.sku=r2.sku WHERE r2.pid='{$purchaser_record_id}'";
        $value=$this->db->get_all($sql);
        $sku_info=array();
        foreach($value as $k=>$id){
            $sku_info[$k]['sku_id']=$id['sku_id'];
            $sku_info[$k]['num']=$id['num'];
        }
        return $sku_info;
    }

}
