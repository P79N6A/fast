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
        return 'fx_purchaser_record_detail';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";

        $sql_main = "FROM fx_purchaser_record rl  
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
        $select = 'r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,rl.is_check';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['lof_num'] = $data['data'][$key]['num'];
//            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
//            $data['data'][$key]['price1'] = number_format($data['data'][$key]['price1'], 3);
            $data['data'][$key]['num_differ'] = $value['num'] - $value['finish_num'];
            $name_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $value['spec2_code'], 'spec2_name');
            $data['data'][$key]['spec2_name'] = isset($name_arr['data']['spec2_name']) ? $name_arr['data']['spec2_name'] : '';
            $name_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $value['spec1_code'], 'spec1_name');
            $data['data'][$key]['spec1_name'] = isset($name_arr['data']['spec1_name']) ? $name_arr['data']['spec1_name'] : '';
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //批次查询
    function get_by_page_lof($filter) {
        $sql_join = "";

        $sql_main = "FROM fx_purchaser_record rl
		INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		INNER JOIN b2b_lof_datail r4 on r2.record_code = r4.order_code
		LEFT JOIN goods_sku r5 on r2.sku = r5.sku
		WHERE  r2.sku = r4.sku and r4.order_type = :order_type  ";

        $sql_values = array();
        $sql_values[':order_type'] = 'fx_purchase';

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

        $select = 'r4.id,r4.pid,r2.*,r3.goods_name,r5.barcode,rl.is_check,r4.num as lof_num,r4.fill_num,r4.lof_no,r4.production_date';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //		print_r($data);exit;
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($data['data'] as $key => $value) {

            //$data['data'][$key]['price'] = round($value['price'],2);
            //$data['data'][$key]['rebate'] = round($value['rebate'],2);
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = number_format($data['data'][$key]['price1'], 3);
            $value['money'] = $data['data'][$key]['price1'] * $value['lof_num'];

            $data['data'][$key]['money'] = number_format($value['money'], 3);
            $data['data'][$key]['num_differ'] = $value['lof_num'] - $value['fill_num'];
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
            if (isset($record['data']['is_check']) && 1 == $record['data']['is_check']) {
                return $this->format_ret(false, array(), '单据已确认!不能删除明细');
            }
        }
        $result = parent::delete(array('purchaser_record_detail_id' => $id));
        $this->mainWriteBack($detail['data']['pid']);
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '删除明细', 'module' => "fx_purchase_record", 'pid' => $detail['data']['pid']);
        load_model('pur/PurStmLogModel')->insert($log);
        return $result;
    }

    //删除批次
    function delete_lof($id) {
        $lof_data = $this->db->get_row("select * from b2b_lof_datail where id=:id", array(':id' => $id));
        if (empty($lof_data)) {
            return $this->format_ret(false, array(), '批次明细信息不存在!不能删除明细');
        }
        $detail = $this->get_row(array('pid' => $lof_data['pid'], 'sku' => $lof_data['sku']));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'purchaser_record_id');
            if (isset($record['data']['is_check']) && 1 == $record['data']['is_check']) {
                return $this->format_ret(false, array(), '单据已确认!不能删除明细');
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
        $res = $this->mainWriteBack($detail['data']['pid']);
        if ($res === FALSE) {
            $this->rollback();
            return $result;
        }
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '删除批次明细', 'module' => "fx_purchase_record", 'pid' => $lof_data['pid']);
        load_model('pur/PurStmLogModel')->insert($log);
        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 主详情单据数据回写
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function mainWriteBackDetail($record_id, $sku, $order_type = 'fx_purchase') {
        $sql = "update {$this->table} set
    	num = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type),
    	money = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type)*price
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

        $m = load_model('fx/PurchaseRecordModel');
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
     * 根据明细的sku和主单据号,判断明细是否已经存在
     * @param   int     $record_code    主单据号
     * @param   string  $sku    SKU编号
     * @return  boolean 存在返回true
     */
    private function is_detail_exists($record_code, $sku) {
        $ret = $this->get_row(array(
            'record_code' => $record_code,
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
     * 导入经销采购订单明细记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-10-28
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function add_detail_action($record_code, $ary_details, $store_code = '') {
        //判断主单据是否存在
        $record = $this->is_exists($record_code, 'record_code');
        $pid = $record['data']['purchaser_record_id'];
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '经销采购单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_check'] == 1) {
            return $this->format_ret(false, array(), '经销采购单已确认, 不能修改明细!');
        }
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        $lof_status = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $new_details = array();
        $sql = "select * from b2b_lof_datail where order_code = :order_code and order_type = 'fx_purchase'";
        $details_lof = $this->db->get_all($sql, array(":order_code" => $record_code));
        if (!empty($details_lof)) {
            foreach ($details_lof as $k => $d) {
                $key = $d['sku'];
                if (array_key_exists($key, $new_details)) {
                    if ($lof_status == 1) {
                        $new_details[$key]['num'] += $d['num'];
                    }
                } else {
                    $new_details[$key] = $d;
                }
                $arr = array();
                $arr['pid'] = $pid;
                $arr['record_code'] = $record['data']['record_code'];
                $arr['rebate'] = $record['data']['rebate'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
                // 传入下单时间校验是否有调价单
                $arr['price'] = load_model('fx/GoodsManageModel')->compute_fx_price($record['data']['custom_code'], $d, $record['data']['order_time']);
                $arr['price'] = sprintf("%.3f", $arr['price']);
                $arr['money'] = (float) $arr['price'] * $new_details[$key]['num'];
                $arr['cost_price'] = $arr['price'];
                $new_details[$key] = array_merge($new_details[$key],$arr);
            }
        }
        $this->begin_trans();
        try {
            $result = parent::delete(array('pid' => $pid));
            if ($result['status'] < 0) {
                $this->rollback();
                return $result;
            }
            $ret = $this->insert_multi($new_details);
            if ($ret['status'] < 0) {
                $this->rollback(); 
                return $ret;
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
     * 编辑多条库存调整单明细记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-10-28
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function edit_detail_action($pid, $data) {
        $ret = parent::update(array('num' => $data['num'], 'money' => $data['num'] * $data['price']), array('pid' => $pid, 'sku' => $data['sku']));
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        if ($ret_arr['lof_status'] == 0) {
            $this->update_lof_detail($data['record_code'], $data['sku'], $data['num'],$pid);
        }
        //维护主单金额
        $this->mainWriteBack($pid);
        return $ret;
    }

    function update_lof_detail($record_code, $sku, $num,$pid) {
        if ($num == 0) {
            $sql = "delete from b2b_lof_datail where order_type='fx_purchase' and order_code='{$record_code}' and sku='{$sku}'";
            $this->db->query($sql);
            return $this->format_ret(1);
        } else {
            $sku_arr = array('goods_code', 'spec1_code', 'spec2_code', 'sku');
            $detail_lof = load_model('goods/SkuCModel')->get_sku_info($sku, $sku_arr);
            $ret = load_model('fx/PurchaseRecordModel')->get_by_id($pid);
            $record_data = $ret['data'];
            $detail_lof['num'] = $num;
            $detail_lof['init_num'] = $num;

            return load_model('stm/GoodsInvLofRecordModel')->add_detail_action($record_data['purchaser_record_id'], $record_data['store_code'], 'fx_purchase', array($detail_lof));
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
        $sql = "update fx_purchaser_record set
                  fx_purchaser_record.num = (select sum(num) from fx_purchaser_record_detail where pid = :id),
                  fx_purchaser_record.finish_num = (select sum(finish_num) from fx_purchaser_record_detail where pid = :id),
                  fx_purchaser_record.sum_money = (select sum(money) from fx_purchaser_record_detail where pid = :id)
                where fx_purchaser_record.purchaser_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }

    /**
     * 回写完成数
     * @param   string  $record_code   主单据号
     * @param   string  $data
     */
    public function update_finish_num($record_code, $data, $type = 'fx_purchase') {
        if (empty($record_code)) {
            return $this->format_ret(-1, '', '关联经销采购单为空');
        }
        $this->begin_trans();
        try {
            $sku_arr = array();
            foreach ($data as $ary_detail) {
                $sql = "update b2b_lof_datail set fill_num = fill_num + {$ary_detail['num']} where order_type = 'fx_purchase' and order_code = :record_code and sku = :sku and lof_no = :lof_no ";
                $res = $this->query($sql, array(':record_code' => $record_code, ':sku' => $ary_detail['sku'], ':lof_no' => $ary_detail['lof_no']));
                if($res['status'] < 0){
                    $this->rollback();
                    return $this->format_ret(-1,'','回写经销采购订单批次信息失败');
                }
                $sku_arr[$ary_detail['sku']] = $ary_detail['sku'];
            }
            foreach ($sku_arr as $v) {
                $ret = $this->finishWriteBackDetail($record_code, $v, 'fx_purchase');
                if($ret['status'] < 0){
                    $this->rollback();
                    return $this->format_ret(-1,'','回写经销采购订单详情表失败');
                }
            }
            $ret = $this->mainFinishWriteBack($record_code);
            if($ret['status'] < 0){
                $this->rollback();
                return $this->format_ret(-1,'','回写经销采购订单表失败');
            }
            $record_detail = $this->get_by_record_code($record_code);
            $data = array();
            $pid = '';
            foreach($record_detail['data'] as $val) {
                $pid = $val['pid'];
                if($val['finish_num'] != $val['num']) {
                    $data['is_deliver'] = 2;
                    break;
                }
                $data['is_deliver'] = 1;
            }
            //回写采购订单完成状态
            $ret = $this->update_exp('fx_purchaser_record', $data, array("record_code" => $record_code));
            if($ret['status'] < 0){
                $this->rollback();
                return $this->format_ret(-1,'','更改经销采购订单表状态失败');
            }
            //日志
            $finish_status = $data['is_deliver'] == 1 ? '出库' : '部分出库';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "确认", 'finish_status' => $finish_status, 'action_name' => "出库", 'module' => "fx_purchase_record", 'pid' => $pid);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            $this->commit();
            return $ret;
            
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    //回写主表完成数
    public function mainFinishWriteBack($record_code) {
        //回写完成数量
        $sql = "update fx_purchaser_record set
	   	fx_purchaser_record.finish_num = (select sum(finish_num) from fx_purchaser_record_detail where record_code = :record_code)
	   	where fx_purchaser_record.record_code = :record_code ";
        $res = $this->query($sql, array(':record_code' => $record_code));
        return $res;
    }

    /**
     * 详细表完成数回写
     * @since 2014-11-11
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function finishWriteBackDetail($record_code, $sku, $order_type = 'fx_purchase') {
        $sql = "update fx_purchaser_record_detail set
    	finish_num = (select sum(fill_num) from b2b_lof_datail where order_code = :record_code and sku = :sku and order_type = :order_type)
    	where record_code = :record_code and sku = :sku";
        $res = $this->query($sql, array(':record_code' => $record_code, ':sku' => $sku, ':order_type' => $order_type));
        return $res;
    }
}
