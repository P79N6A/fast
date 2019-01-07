<?php

/**
 * 库单详情管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('stm');

class ReturnRecordDetailModel extends TbModel {

    function get_table() {
        return 'wbm_return_record_detail';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";

        $sql_main = "FROM wbm_return_record rl
		            INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		            INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		            INNER JOIN goods_sku r4 on r2.sku = r4.sku
		            WHERE  1=1  ";

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
        if (isset($filter['differ']) && $filter['differ'] == 'return_differ') {
            $sql_main .= " AND r2.num <> r2.enotice_num";
        }
        $select = 'r2.*,r3.goods_name,r3.trade_price,r4.barcode,r4.spec1_name,r4.spec2_name,rl.is_store_in,rl.is_sure,rl.store_code,r3.brand_name ';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //添加库位（开始）
        $shelf_arr = array();
        $sku_arr = array_unique(array_column($data['data'],'sku'));
        $store_code_arr = array_unique(array_column($data['data'],'store_code'));
        if(!empty($data['data']) && !empty($sku_arr) && !empty($store_code_arr)){
            $shelf_arr_ret = load_model('prm/GoodsShelfModel')->get_shelf_name($store_code_arr,$sku_arr,'store_code,sku');
            if($shelf_arr_ret['status'] == 1){
                $shelf_arr = $shelf_arr_ret['data'];
            }
        }
        //添加库位（结束）
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];  
//            $data['data'][$key]['price1'] = $data['data'][$key]['trade_price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = round($data['data'][$key]['price1'], 3);
            $data['data'][$key]['different_num'] = $value['enotice_num'] - $value['num'];
            $shelf_key = $value['store_code'].','.$value['sku'];
            $data['data'][$key]['shelf_name'] = isset($shelf_arr[$shelf_key])? $shelf_arr[$shelf_key]['shelf_name']:'';
        }

        //print_r($data);exit;
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_page_lof($filter) {
        $sql_join = "";
        $sql_main = "FROM wbm_return_record rl
		INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		INNER JOIN b2b_lof_datail r4 on r2.record_code = r4.order_code
		INNER JOIN goods_sku r5 on r2.sku = r5.sku
		WHERE  r2.sku = r4.sku and r4.order_type = :order_type  ";

        $sql_values = array();
        $sql_values[':order_type'] = 'wbm_return';

        //$sql_values = array();
        // record_code查询
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r2.record_code = :record_code )";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            return;
        }
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name or r5.barcode LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }

        //$select = 'r2.*,r3.goods_name,r4.barcode,rl.is_store_out';
        $select = 'r4.id,r4.pid,r2.*,rl.is_store_in,rl.is_sure,r3.goods_name,r3.trade_price,r5.barcode,r4.num,r4.lof_no,r4.production_date,rl.store_code,r3.brand_name';
        //echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //添加库位（开始）
        $shelf_arr = array();
        if(!empty($data['data'])){
            $sku_arr = array_unique(array_column($data['data'],'sku'));
            $store_code_arr = array_unique(array_column($data['data'],'store_code'));
            if(!empty($sku_arr) && !empty($store_code_arr)){
                $shelf_arr_ret = load_model('prm/GoodsShelfModel')->get_shelf_name($store_code_arr,$sku_arr,'store_code,sku');
                if($shelf_arr_ret['status'] == 1){
                    $shelf_arr = $shelf_arr_ret['data'];
                }
            }
        }
        //添加库位（结束）
        foreach ($data['data'] as $key => $value) {
            $key_arr = array('spec1_name', 'spec2_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];     
//            $data['data'][$key]['price1'] = $data['data'][$key]['trade_price'] * $data['data'][$key]['rebate'];         
            $data['data'][$key]['price1'] = round($data['data'][$key]['price1'], 3);
            $data['data'][$key]['money'] = $data['data'][$key]['price1'] * $value['num'];
            $data['data'][$key]['money'] = round($data['data'][$key]['money'], 3);
            $shelf_key = $value['store_code'].','.$value['sku'];
            $data['data'][$key]['shelf_name'] = isset($shelf_arr[$shelf_key])? $shelf_arr[$shelf_key]['shelf_name']:'';
        }

        //		print_r($data);exit;
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

    function get_enotice_num_all($record_code) {
        $enotice_num = 0;
        $details = $this->get_by_record_code($record_code);
        foreach ($details['data'] as $detail) {
            $enotice_num += $detail['enotice_num'];
        }
        return $enotice_num;
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
            if (isset($record['data']['is_store_in']) && 1 == $record['data']['is_store_in']) {
                return $this->format_ret(false, array(), '单据已入库!不能删除明细');
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
            if (isset($record['data']['is_store_in']) && 1 == $record['data']['is_store_in']) {
                return $this->format_ret(false, array(), '单据已入库!不能删除明细');
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
    public function mainWriteBackDetail($record_id, $sku, $order_type = 'wbm_return') {
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
    public function is_exists($value, $field_name = 'record_code') {

        $m = load_model('wbm/ReturnRecordModel');
        $ret = $m->get_row(array($field_name => $value));
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
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function add_detail_action($pid, $ary_details) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'return_record_id');

        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '批发退货单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_store_in'] == 1) {
            return $this->format_ret(false, array(), '批发退货单已入库, 不能添加明细!');
        }
        $this->begin_trans();
        try {
            foreach ($ary_details as $ary_detail) {
                if (isset($ary_detail['num_flag']) && $ary_detail['num_flag'] == '1') {
                    //兼容入库为0
                } else {
                    if ((!isset($ary_detail['num']) || $ary_detail['num'] == 0) && (!isset($ary_detail['enotice_num']) || $ary_detail['enotice_num'] == 0)) {
                        continue;
                    }
                }

                $ary_detail['pid'] = $pid;
                $ary_detail['record_code'] = $record['data']['record_code'];
                $ary_detail['rebate'] = $record['data']['rebate'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
                if (isset($ary_detail['sell_price'])) {
                    $ary_detail['money'] = $ary_detail['num'] * $ary_detail['trade_price'] * $ary_detail['rebate'];
                    $ary_detail['money'] = round($ary_detail['money'], 3);
                    $ary_detail['price'] = $ary_detail['trade_price'];
                    $ary_detail['refer_price'] = $ary_detail['sell_price'];
                } else {
                    $ary_detail['money'] = $ary_detail['price'] * $ary_detail['num'] * $ary_detail['rebate'];
                    $ary_detail['money'] = round($ary_detail['money'], 3);
                    $ary_detail['price'] = $ary_detail['price'];
                    $ary_detail['refer_price'] = $ary_detail['refer_price'];
                }

                //判断SKU是否已经存在
                $check = $this->is_detail_exists($pid, $ary_detail['sku']);
                if ($check) {

                    //批次表里查出该单据数量和价格
                    $pici = load_model('stm/GoodsInvLofRecordModel')->get_detail_cnt('wbm_return', $pid, $ary_detail['sku']);
                    $ary_detail['num'] = isset($pici[0]['cnt']) ? $pici[0]['cnt'] : '';
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
     * 编辑多条明细记录
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function edit_detail_action($pid, $ary_details) {       
        $this->begin_trans();
        try {
            $is_lof = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
            foreach ($ary_details as $ary_detail) {                
                //获取修改商品的原数据明细
                $ret_detail = $this ->get_row(array('record_code'=>$ary_detail['record_code'],'sku'=>$ary_detail['sku']));
                $ret_detail = $ret_detail['data'];
                //获取商品条形码
                $ret = load_model('goods/SkuCModel')->get_sku_info($ary_detail['sku']);
                $barcode = $ret['barcode'];
                $ary_detail['pid'] = $pid;
                //更新明细数据
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'] * $ary_detail['rebate'];
                $ret = $this->update($ary_detail, array('pid' => $pid, 'sku' => $ary_detail['sku']));
                if ($is_lof['lof_status'] == 0) {
                    $this->update_lof_detail($ary_detail['record_code'], $ary_detail['sku'], $ary_detail['num']);
                    if (1 != $ret['status']) {
                        $this->rollback();
                        return $ret;
                    }
                }
                if (1 != $ret['status']) {
                    return $ret;
                }
                //添加操作日志
                 if ($ary_detail['num'] != $ret_detail['num']) {
                    $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '修改数量', 'action_note' => "商品条码：{$barcode}，数量由{$ret_detail['num']}改为{$ary_detail['num']}", 'module' => "wbm_return_record", 'pid' => $pid);                   
                    $ret = load_model('pur/PurStmLogModel')->insert($log);
                }
                if ($ary_detail['price']!=$ret_detail['price']) {
                    $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '修改批发价', 'action_note' => "商品条码：{$barcode}，批发价由{$ret_detail['price']}改为{$ary_detail['price']}", 'module' => "wbm_return_record", 'pid' => $pid);
                    load_model('pur/PurStmLogModel')->insert($log);
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
        $sql = "UPDATE b2b_lof_datail SET num='{$lof_info['num']}' WHERE order_type = 'wbm_return' and order_code = '{$lof_info['record_code']}' AND sku = '{$detail['sku']}' AND lof_no = '{$lof_info['lof_no']}' ";
        $this->db->query($sql);
        $total_num = 0;
        $lof_sql = "SELECT sku,lof_no,production_date,num,init_num FROM b2b_lof_datail WHERE order_type = 'wbm_return' AND order_code = :order_code AND sku = :sku";
        $lof_data = $this->db->get_all($lof_sql, array(":order_code" => $lof_info['record_code'], ":sku" => $detail['sku']));
        if (!empty($lof_data)) {
            foreach ($lof_data as $lof) {
                $total_num += $lof['num'];
            }
        }
        $detail['num'] = $total_num;
        $ret = parent::update(array('num' => $detail['num'], 'money' => $detail['num'] * $detail['price'] * $detail['rebate']), array('pid' => $pid, 'sku' => $detail['sku']));
        $this->mainWriteBack($pid);
        return $ret;
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
        $sql = "update wbm_return_record set
                  wbm_return_record.num = (select sum(num) from wbm_return_record_detail where pid = :id),
                  wbm_return_record.money = (select sum(money) from wbm_return_record_detail where pid = :id)
                where wbm_return_record.return_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }

    
    /**
     * 修改扫描数量
     */
      function update_scan_num($record_code, $num, $id) {
        $ret = load_model('wbm/ReturnRecordModel')->get_row(array('record_code' => $record_code));
        $relation_code = $ret['data']['relation_code'];
        $sku = substr($id, 8);
        if ($relation_code) {
            $sql = "select num from wbm_return_notice_detail_record where return_notice_code = :relation_code and sku = :sku";
            $tz_num = $this->db->get_value($sql, array(":relation_code" => $relation_code, ":sku" => $sku));
            if ($num > $tz_num) {
                return $this->format_ret(-1, '', '更新数量超出退货通知单数量！');
            }
        }
        $detail = $this->get_row(array('record_code' => $record_code, 'sku' => $sku));

        if (empty($detail['data'])) {
            return $this->format_ret(-1, '', '单据明细信息不存在');
        }
        $detail['data']['num'] = $num;
        $ret = $this->edit_detail_action($ret['data']['return_record_id'], array($detail['data']));
        if ($ret['status']==1) {
            //更新批次
            $this->update_lof_detail($record_code, $sku, $num);
            return $this->format_ret(1, '', '更新成功');
        } else {
            return $this->format_ret(-1, '', '扫描批发退货更新单据明细数量失败');
        }
    }

    
     function update_lof_detail($record_code, $sku, $num) {
        if ($num == 0) {
            $sql = "delete from b2b_lof_datail where order_type='wbm_return' and order_code='{$record_code}' and sku='{$sku}'";
            $this->db->query($sql);
            return $this->format_ret(1);
        } else {
            $sku_arr = array('goods_code', 'spec1_code', 'spec2_code', 'sku');
            $detail_lof = load_model('goods/SkuCModel')->get_sku_info($sku, $sku_arr);
            $ret = load_model('wbm/ReturnRecordModel')->get_row(array('record_code' => $record_code));
            $record_data = $ret['data'];
            $detail_lof['num'] = $num;
            $detail_lof['notice_num'] = $num;
            $detail_lof['init_num'] = $num;
            return load_model('stm/GoodsInvLofRecordModel')->add_detail_action($record_data['return_record_id'], $record_data['store_code'], 'wbm_return', array($detail_lof));
        }
    }
    
    
   function update_scan_num_lof($data) {
        $ret = load_model('wbm/ReturnRecordModel')->get_row(array('record_code' => $data['record_code']));
        $relation_code = $ret['data']['relation_code'];
        $id_arr = explode('_', $data['id']);
        $sku = $id_arr[2];
        if ($relation_code) {
            $sql = "select num from wbm_return_notice_detail_record where return_notice_code = :relation_code and sku = :sku";
            $tz_num = $this->db->get_value($sql, array(":relation_code" => $relation_code, ":sku" => $sku));
            if ($data['num'] > $tz_num) {
                return $this->format_ret(-1, '', '更新数量超出退货通知单数量！');
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
            return $this->format_ret(-1, '', '批发退货单更新单据明细数量失败');
        }
    }

    /**更新明细表和批次表
     * @param $pid
     * @param $detail
     * @param $lof_info
     * @return array
     */
    public function update_detail_action_lof($pid, $detail, $lof_info) {
        $sql = "UPDATE b2b_lof_datail SET num='{$lof_info['num']}' WHERE order_type = 'wbm_return' and order_code = '{$lof_info['record_code']}' AND sku = '{$detail['sku']}' AND lof_no = '{$lof_info['lof_no']}' ";
        $this->db->query($sql);
        $ret = parent::update(array('num' => $lof_info['num'], 'money' => $lof_info['num'] * $detail['price'] * $detail['rebate']), array('pid' => $pid, 'sku' => $detail['sku']));
        $this->mainWriteBack($pid);
        return $ret;
    }

}
