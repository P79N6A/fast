<?php

/**
 * 库单详情管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('stm');

class ReturnNoticeRecordDetailModel extends TbModel {

    function get_table() {
        return 'pur_return_notice_record_detail';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";

        $sql_main = "FROM pur_return_notice_record rl
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
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name  or r4.barcode LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }

        $select = 'r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,rl.is_finish,rl.is_sure';
        if (isset($filter['is_fenye']) && $filter['is_fenye'] == '1') {
            $filter['page_size'] = 1000;
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        //print_r($data);exit;
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($data['data'] as $key => $value) {
            //$data['data'][$key]['rebate'] = round($value['rebate'],2);
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = round($data['data'][$key]['price1'], 3);
            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);
            $data['data'][$key]['diff_num'] = $value['num'] - $value['finish_num'];
            if ($status['status'] != 1) {
                $data['data'][$key]['price1'] = '****';
                $data['data'][$key]['price'] = '****';
                $data['data'][$key]['money'] = '****';
            }
        }
        filter_fk_name($data['data'], array('spec1_code|spec1_code', 'spec2_code|spec2_code'));
        //print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_page_lof($filter) {
        $sql_join = "";

        $sql_main = "FROM pur_return_notice_record rl
		INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		INNER JOIN b2b_lof_datail r4 on r2.record_code = r4.order_code
		INNER JOIN goods_sku r5 on r2.sku = r5.sku
		WHERE  r2.sku = r4.sku and r4.order_type = :order_type  ";

        $sql_values = array();
        $sql_values[':order_type'] = 'pur_return_notice';

        //$sql_values = array();
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

        //$select = 'r2.*,r3.goods_name,r4.barcode,rl.is_store_out';
        $select = 'r4.id,r4.pid,r2.*,rl.is_finish,rl.is_sure,r3.goods_name,r5.barcode,r4.fill_num,r4.init_num,r4.num,r4.lof_no,r4.production_date';
        //echo $sql_main;
        if (isset($filter['is_fenye']) && $filter['is_fenye'] == '1') {
            $filter['page_size'] = 1000;
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        //		print_r($data);exit;
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($data['data'] as $key => $value) {

            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = round($data['data'][$key]['price1'], 3);
            $data['data'][$key]['money'] = $data['data'][$key]['price1'] * $value['init_num'];
            $data['data'][$key]['money'] = round($data['data'][$key]['money'], 3);
            ;
            $data['data'][$key]['diff_num'] = $value['init_num'] - $value['fill_num'];
            $key_arr = array('spec1_name', 'spec2_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);
            if ($status['status'] != 1) {
                $data['data'][$key]['price1'] = '****';
                $data['data'][$key]['price'] = '****';
                $data['data'][$key]['money'] = '****';
            }
        }
        filter_fk_name($data['data'], array('spec1_code|spec1_code', 'spec2_code|spec2_code'));
        //print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_all(array('pid' => $id));

        filter_fk_name($data['data'], array('adjust_type|record_type', 'store_code|store'));
        return $data;
    }

    function get_select_data($id, $select_sku_arr) {
        $sku_str = "'" . implode("','", $select_sku_arr) . "'";
        $sql = " select goods_code,spec1_code,spec2_code,sku,refer_price,price,rebate,money,num,finish_num,(num-finish_num) as diff_num
    	from  pur_return_notice_record_detail  where pid='{$id}' and sku in($sku_str)";
        $data = $this->db->get_all($sql);

        return $this->format_ret(1, $data);
    }

    function get_select_ids($id_arr) {
        $id_str = "'" . implode("','", $id_arr) . "'";
        $sql = " select goods_code,spec1_code,spec2_code,sku,refer_price,price,rebate,money,num,finish_num,(num-finish_num) as diff_num,record_code
    	from  pur_return_notice_record_detail  where return_notice_record_detail_id in($id_str)";
        $data = $this->db->get_all($sql);

        return $this->format_ret(1, $data);
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
            //if(!isset($info['goods_code'])){
            if ($info['goods_code'] == '') {
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

        $detail = $this->get_row(array('return_notice_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'return_notice_record_id');
            if (isset($record['data']['is_sure']) && 1 == $record['data']['is_sure']) {
                return $this->format_ret(false, array(), '单据已确认!不能删除明细');
            }
        }
        $result = parent::delete(array('return_notice_record_detail_id' => $id));

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
            $record = $this->is_exists($detail['data']['pid'], 'return_notice_record_id');
            if (isset($record['data']['is_sure']) && 1 == $record['data']['is_sure']) {
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
            $result = parent::delete(array('return_notice_record_detail_id' => $detail['data']['return_notice_record_detail_id']));
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
     * @since 2014-11-11
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function mainWriteBackDetail($record_id, $sku, $order_type = 'wbm_notice') {
        $sql = "update {$this->table} set
    	num = (select sum(init_num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type),
    	finish_num = (select sum(fill_num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type),
    	money = (select sum(init_num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type )*price*rebate
    	where pid = :pid and sku = :sku";
        $res = $this->query($sql, array(':pid' => $record_id, ':sku' => $sku, ':order_type' => $order_type));

        return $res;
    }

    /**
     * 详细表完成数回写
     * @since 2014-11-11
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function finishWriteBackDetail($record_code, $sku, $order_type = 'pur_return_notice') {
        $sql = "update pur_return_notice_record_detail set
    	finish_num = (select sum(fill_num) from b2b_lof_datail where order_code = :record_code and sku = :sku and order_type = :order_type)
    	where record_code = :record_code and sku = :sku";
        $res = $this->query($sql, array(':record_code' => $record_code, ':sku' => $sku, ':order_type' => $order_type));

        return $res;
    }

    /**
     * 根据明细的record_code判断主表单据是否存在
     * @param string $value 值
     * @param string $field_name 字段名
     * @return array
     */
    public function is_exists($value, $field_name = 'record_code') {

        $m = load_model('pur/ReturnNoticeRecordModel');
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
     * 根据明细的sku和主单据id,判断明细是否已经存在
     * @param   int     $pid    主单据ID
     * @param   string  $sku    SKU编号
     * @return  boolean 存在返回true
     */
    public function is_detail_exists($pid, $sku) {
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
    public function valid($data, $is_edit = false) {
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
        $record = $this->is_exists($pid, 'return_notice_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '采购退货单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret(false, array(), '采购退货单已验收, 不能修改明细!');
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

                $ary_detail['rebate'] = $record['data']['rebate'];
                // $ary_detail['refer_price'] = $ary_detail['price'];
                $ary_detail['price'] = $ary_detail['purchase_price'];
                $ary_detail['price'] = round($ary_detail['price'], 3);
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['purchase_price'] * $ary_detail['rebate'];
                $ary_detail['money'] = round($ary_detail['money'], 3);
                //判断SKU是否已经存在
                $check = $this->is_detail_exists($pid, $ary_detail['sku']);
                if ($check) {
                    //批次表里查出该单据数量和价格
                    $pici = load_model('stm/GoodsInvLofRecordModel')->get_detail_notice_cnt('pur_return_notice', $pid, $ary_detail['sku']);
                    $ary_detail['num'] = isset($pici[0]['init_num']) ? $pici[0]['init_num'] : '';
                    $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'] * $ary_detail['rebate'];
                    $ary_detail['money'] = round($ary_detail['money'], 3);

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
     * 编辑多条库存调整单明细记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-10-28
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function edit_detail_action($pid, $ary_details) {
        $this->begin_trans();
        try {
            $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
            foreach ($ary_details as $ary_detail) {
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
                $this->update_lof_detail($ary_detail['record_code'], $ary_detail['sku'], $ary_detail['num']);
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

    function update_lof_detail($record_code, $sku, $num) {
        $sql = "select * from b2b_lof_datail where order_code=:record_code AND sku=:sku AND order_type='pur_return_notice' ";

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
     * 回写完成数
     * @param   string  $record_code   主单据号
     * @param   string  $data
     */
    public function update_finish_num($record_code, $data) {
        $sku_arr = array();
        foreach ($data as $ary_detail) {
            //$sql = "update pur_order_record_detail set finish_num = finish_num + {$ary_detail['num']} where record_code = :record_code and sku = :sku ";
            $sql = "update b2b_lof_datail set fill_num = fill_num + {$ary_detail['num']} where order_type = 'pur_return_notice'  and order_code = :record_code and sku = :sku and lof_no = :lof_no  ";
            $res = $this->query($sql, array(':record_code' => $record_code, ':sku' => $ary_detail['sku'], ':lof_no' => $ary_detail['lof_no'], ));
            $sku_arr[$ary_detail['sku']] = $ary_detail['sku'];
        }

        foreach ($sku_arr as $v) {
            $this->finishWriteBackDetail($record_code, $v, 'pur_return_notice');
        }
        $ret = $this->mainFinishWriteBack($record_code);
        //回写采购订单完成状态

        $ret1 = load_model('pur/ReturnNoticeRecordModel')->update_finish($record_code);
        if ($ret1['status'] == '1') {
            $ret = load_model('pur/ReturnNoticeRecordModel')->update_check_record_code('1', 'is_finish', $record_code);
        }


        return $ret;
    }

    //回写主表完成数
    public function mainFinishWriteBack($record_code) {

        //回写完成数量
        $sql = "update pur_return_notice_record set
	   	pur_return_notice_record.finish_num = (select sum(finish_num) from pur_return_notice_record_detail where record_code = :id)
	   	where pur_return_notice_record.record_code = :id ";
        $res = $this->query($sql, array(':id' => $record_code));
        return $res;
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
        $sql = "update pur_return_notice_record set
                  pur_return_notice_record.num = (select sum(num) from pur_return_notice_record_detail where pid = :id),
                  pur_return_notice_record.finish_num = (select sum(finish_num) from pur_return_notice_record_detail where pid = :id),
                  pur_return_notice_record.money = (select sum(money) from pur_return_notice_record_detail where pid = :id)
                where pur_return_notice_record.return_notice_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }

    function imoprt_detail($id, $sku_arr, $import_data, $is_lof = 0) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($id, 'return_notice_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '采购出库单明细所关联的主单据不存在!');
        }
        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
        $ret['data'] = array();
        $sku_str = "'" . implode("','", $sku_arr) . "'";
        $detail_data = $this->db->get_all("select DISTINCT b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.price,g.sell_price,g.trade_price,g.purchase_price  from
                goods_sku b
                inner join  base_goods g ON g.goods_code = b.goods_code

            where g.status = '0' and b.barcode in({$sku_str}) "); //sell_price

        $detail_data_lof = array();
        foreach ($detail_data as &$val) {
            $num = $import_data[$val['barcode']]['num'];
            if (is_numeric($num) && $num > 0) {
                $val['num'] = round($num);
                $key = array_search($val['barcode'], $sku_arr);
                $lof_val = $val;
                $lof_val['lof_no'] = $ret_lof['data']['lof_no'];
                $lof_val['production_date'] = $ret_lof['data']['production_date'];
                $detail_data_lof[] = $val;
                unset($sku_arr[$key]);
            }
        }

        $result['success'] = count($detail_data_lof);
        //"行<br>导入失败列表:<br>"+result.data.fail
        //print_r($detail_data);exit;
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $detail_data_lof);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $record['data']['store_code'], 'pur_return_notice', $detail_data_lof);

        if ($ret['status'] != '1') {
            return $ret;
        }

        //调整单明细添加
        $ret = $this->add_detail_action($id, $detail_data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '导入明细', 'module' => "pur_return_notice_record", 'pid' => $id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        $ret['data'] = '';
        if (!empty($sku_arr)) {
            $result['fail'] = implode(',', $sku_arr);
        }
        $ret['data'] = $result;
        return $ret;
    }

    /**
     * 未开启批次修改扫描数量
     * @param $record_code
     * @param $num
     * @param $id
     * @return array
     */
    function update_scan_num($record_code, $num, $id) {
        $record = load_model('pur/ReturnNoticeRecordModel')->get_row(array('record_code' => $record_code));
        $sku = substr($id, 8);
        $detail = $this->get_row(array('record_code' => $record_code, 'sku' => $sku));
        if (empty($detail['data'])) {
            return $this->format_ret(-1, '', '单据明细信息不存在');
        }
        $detail_info=$detail['data'];
        $detail_info['num'] = $num;
        $ret = $this->edit_detail_action($record['data']['return_notice_record_id'], array($detail_info));
        if ($ret['status'] != 1) {
            return $ret;
        }
        return $this->format_ret(1, '', '更新扫描数量成功！');
    }

}
