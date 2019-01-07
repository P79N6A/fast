<?php

/**
 * 库存锁定单详情管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
//require_lang('stm');
//require_lang('prm');

class StockLockRecordDetailModel extends TbModel {

    //单据状态
    public $order_status = array(
        0 => '未锁定',
        1 => '已锁定',
        2 => '已释放',
        3 => '已作废',
    );

    function get_table() {
        return 'stm_stock_lock_record_detail';
    }

    /**条件查询
     * @param $filter
     * @return array
     */
    function get_by_page($filter) {
        $sql_main = "FROM stm_stock_lock_record rl  
		            INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code 
		            INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
                    INNER JOIN goods_sku r4 on r2.sku = r4.sku
		            WHERE 1 ";
        $sql_values = array();
        // record_code查询
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND r2.record_code = :record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            $sql_main .= " AND 1=2 ";
        }
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name or r4.barcode LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        $select = 'rl.order_status,r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }


    /**批次条件查询
     * @param $filter
     * @return array
     */
    function get_by_page_lof($filter) {
        $sql_main = "FROM stm_stock_lock_record rl  
		            INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code 
		            INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		            INNER JOIN b2b_lof_datail r4 on r2.record_code = r4.order_code AND  r2.sku = r4.sku 
                    INNER JOIN goods_sku r5 on r2.sku = r5.sku
		            WHERE 1 AND r4.order_type = :order_type ";
        $sql_values = array();
        $sql_values[':order_type'] = 'stm_stock_lock';
        // record_code查询
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r2.record_code = :record_code )";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            $sql_main .= " AND 1=2 ";
        }
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name or r5.barcode LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        $select = 'rl.order_status,r5.spec2_name,r5.spec1_name,r4.id,r4.pid,r2.goods_code,r2.spec1_code,r2.spec2_code,r2.sku,r3.goods_name,r5.barcode,r4.num,r4.init_num,r4.fill_num,r4.lof_no,r4.production_date';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            if ($value['order_status'] == 0) {
                $value['num'] = 0;
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
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
            $ary_detail['goods_code'] = $info['goods_code'];
            $ary_detail['spec1_code'] = $info['spec1_code'];
            $ary_detail['spec2_code'] = $info['spec2_code'];
        } else {
            return $this->format_ret(-1, array(), 'SKU信息不存在:' . $ary_detail['sku']);
        }

        return parent::insert($ary_detail);
    }



    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id) {
        $detail = $this->get_row(array('stock_lock_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'stock_lock_record_id');
            if ($record['data']['order_status'] != 0) {
                return $this->format_ret('-1','','单据状态异常，无法删除！');
            }
        }
        $ret = parent::delete(array('stock_lock_record_detail_id' => $id));
        if($ret['status']!=1){
            return $this->format_ret('-1','','删除失败！');
        }
        $this->new_mainWriteBack($detail['data']['record_code']);
        return $this->format_ret(1,'','');
    }

    /**批次状态
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete_lof($id) {
        $lof_data = $this->db->get_row("select * from b2b_lof_datail where id=:id", array(':id' => $id));
        $detail = $this->get_row(array('pid' => $lof_data['pid'], 'sku' => $lof_data['sku']));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'stock_lock_record_id');
            if (isset($record['data']['order_status']) && 0 != $record['data']['order_status']) {
                return $this->format_ret('-1', array(), '不能删除明细!');
            }
        }
        $this->begin_trans();
        $result = load_model('stm/GoodsInvLofRecordModel')->delete_lof($id); // 批次删除
        if ($result['status'] < 1) {
            $this->rollback();
            return $this->format_ret('-1', array(), '删除单据异常!');
        }
        if ($lof_data['num'] == $detail['data']['lock_num']) {
            $result = parent::delete(array('stock_lock_record_detail_id' => $detail['data']['stock_lock_record_detail_id']));
            if ($result['status'] != 1) {
                $this->rollback();
                return $this->format_ret('-1', array(), '删除单据异常!');
            }
        } else {
         //回写批次明细
            $ret = $this->new_mainWriteBackDetail($lof_data['order_code'], $lof_data['sku']);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret('-1', array(), '删除单据异常!');
            }
        }
        //回写主单
        $ret= $this->new_mainWriteBack($detail['data']['record_code']);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret('-1', array(), '删除单据异常!');
        }
        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 根据明细的record_code判断主表单据是否存在
     * @param string $value 值
     * @param string $field_name 字段名
     * @return array
     */
    private function is_exists($value, $field_name = 'record_code') {
        $ret = load_model('stm/StockLockRecordModel')->get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 根据明细的sku和主单据id,判断明细是否已经存在
     * @param   int $pid 主单据ID
     * @param   string $sku SKU编号
     * @return  boolean 存在返回true
     */
    private function is_detail_exists($pid, $sku) {
        $ret = $this->get_row(array('pid' => $pid, 'sku' => $sku));
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


    /**新增锁定单明细
     * @param $pid
     * @param $ary_details
     * @return array
     */
    public function add_detail_action($pid, $ary_details) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'stock_lock_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', '', '锁定单明细所关联的主单据不存在!');
        }
        if ($record['data']['order_status'] >= 2) {
            return $this->format_ret('-1', '', '单据状态异常!');
        }
        $this->begin_trans();
        $add_sku_lof=array();
        try {
            foreach ($ary_details as $ary_detail) {
                $lof_no = (isset($ary_detail['lof_no'])) ? $ary_detail['lof_no'] : 'default_lof';
                $add_sku_lof[] = $ary_detail['sku'] . '_' . $lof_no;
                $ary_detail['pid'] = $pid;
                $ary_detail['record_code'] = $record['data']['record_code'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['sell_price'];
                $ary_detail['price'] = $ary_detail['sell_price'];
                $ary_detail['lock_num'] = $ary_detail['num'];
                //判断SKU是否已经存在
                $check = $this->is_detail_exists($pid, $ary_detail['sku']);
                if ($check) {
                    //批次表里查出该单据数量和价格
                    $pici = load_model('stm/GoodsInvLofRecordModel')->get_detail_cnt('stm_stock_lock', $pid, $ary_detail['sku']);
                    $ary_detail['lock_num'] = isset($pici[0]['cnt']) ? $pici[0]['cnt'] : '';
                    $ary_detail['rebate'] = empty($ary_detail['rebate']) ? $ary_detail['rebate'] : 1;
                    $ary_detail['money'] = $ary_detail['lock_num'] * $ary_detail['price'] * $ary_detail['rebate'];
                    //更新明细数据
                    $ret = $this->update($ary_detail, array('pid' => $pid, 'sku' => $ary_detail['sku']));
                } else {
                    //插入明细数据
                    $ret = $this->insert($ary_detail);
                }
                if (1 != $ret['status']) {
                    $this->rollback();
                    return $ret;
                }
            }
            //单据已锁定时增加明细，锁定库存(导入时会调用此方法，暂时先不做，以后可能要做)
            if ($record['data']['order_status'] == 1) {
              //  $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($pid, 'stm_stock_lock');
                $sql = "select * from b2b_lof_datail where order_code=:order_code  AND  order_type=:order_type ";
                $ret_lof_details['data'] = $this->db->get_all($sql, array(
                    ':order_code' => $record['data']['record_code'],
                    ':order_type' => 'stm_stock_lock',
                ));
                $new_lof_details = array();
                foreach ($ret_lof_details['data'] as $details) {
                    $ret_sku_lof = $details['sku'] . '_' . $details['lof_no'];
                    if (in_array($ret_sku_lof, $add_sku_lof)) {
                        $new_lof_details[] = $details;
                    }
                }
                //锁定
                require_model('prm/InvOpModel');
                $invobj = new InvOpModel($record['data']['record_code'], 'stm_stock_lock', $record['data']['store_code'], 1, $new_lof_details);
                $ret = $invobj->adjust();
                if ($ret['status'] != 1) {
                    $sku_info = array();
                    //库存不足，提示错误信息
                    if ($ret['status'] == -10) {
                        foreach ($ret['data'] as $value) {
                            if ($value['lock_num'] == 0) {
                                $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $value['sku']));
                                $sku_info[] = array($barcode => "系统可用库存不足");
                            }
                        }
                    }
                    $this->rollback();
                    //删除表b2b_lof_detail表
                    $id = array_column($new_lof_details, 'id');
                    $sql_value = array();
                    $id_str = $this->arr_to_in_sql_value($id, 'id', $sql_value);
                    $sql = "DELETE FROM b2b_lof_datail WHERE id IN ({$id_str})";
                    $ret = $this->query($sql, $sql_value);
                    if (!empty($sku_info)) {
                        $msg = load_model('api/WeipinhuijitPickModel')->create_fail_file($sku_info);
                        return $this->format_ret(-1, '', '商品条码在锁定单' . $record['data']['record_code'] . '中库存不足' . $msg);
                    }
                    return $this->format_ret('-1','','库存锁定失败！');
                }else{
                    $record_log = array();
                    //回写批次明细
                    foreach ($new_lof_details as $lock_detail) {
                        $ret = $this->new_mainWriteBackDetail($record['data']['record_code'], $lock_detail['sku']);
                        if ($ret['status'] != 1) {
                            return $this->format_ret('-1', '', '回写明细失败！');
                        }
                        $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $lock_detail['sku']));
                        $record_log[] = "条码{$barcode}添加数量:{$lock_detail['num']}";
                    }
                    //添加日志
                    $log_meg = '单据锁定后添加库存：' . implode(',', $record_log);
                    $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $this->order_status[$record['data']['order_status']], 'action_note' => $log_meg, 'action_name' => '增加明细', 'module' => "stock_lock_record", 'pid' => $record['data']['stock_lock_record_id']);
                    $ret1 = load_model('pur/PurStmLogModel')->insert($log);
                }
            }
            //回写数量和金额
            $this->new_mainWriteBack($record['data']['record_code']);
            $this->commit();
            return $this->format_ret('1','','新增成功!');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }
    
    // 锁定单 锁定对象无 导入方法
    public function import_detail_action($pid, $ary_details,$store_code) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'stock_lock_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', '', '锁定单明细所关联的主单据不存在!');
        }
        if ($record['data']['order_status'] >= 2) {
            return $this->format_ret('-1', '', '单据状态异常!');
        }
        //导入不允许导入原来就存在的条码
        $sql = "SELECT sku FROM stm_stock_lock_record_detail WHERE pid = :pid ";
        $data = $this->db->get_all($sql, array(':pid'=>$pid));
        foreach ($data as $value) {
            $barcode = oms_tb_val('goods_barcode', 'barcode', array('sku' => $value['sku']));
            $before_barcode[] = $barcode;
        }
        foreach ($ary_details as $value) {
            $now_barcode[] = $value['barcode'];
        }
        //重复的barcode
        $err_barcode = array_intersect($before_barcode,$now_barcode);
        //去重可导入的barcode
        $use_barcode = array_diff($now_barcode,$err_barcode);
        if (isset($use_barcode) && $use_barcode=='') {
            return $this->format_ret('-3', $err_barcode, '条码在订单中已存在');
        }
        $this->begin_trans();
        $add_sku_lof=array();
        //去重详情
        $arr_details = array();
        foreach ($ary_details as $ary_detail) {
            $lof_no = (isset($ary_detail['lof_no'])) ? $ary_detail['lof_no'] : 'default_lof';
            if (in_array($ary_detail['barcode'], $use_barcode)) {
                $add_sku_lof[] = $ary_detail['sku'] . '_' . $lof_no;
                $arr_details[] = $ary_detail;
            }
        }
        //单据已锁定时增加明细，锁定库存
        if ($record['data']['order_status'] == 1) {
            //查看批次状态
            $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
            //缺货barcode
            $short_barcode = $this->short_stock($arr_details,$lof_manage['lof_status'],$store_code);
            //去除缺货barcode
            $use_barcode_1 = array_diff($use_barcode,$short_barcode);
            $detail_data_lof = array();
            foreach ($ary_details as $ary_detail) {               
                $ary_detail['pid'] = $pid;
                $ary_detail['record_code'] = $record['data']['record_code'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['sell_price'];
                $ary_detail['price'] = $ary_detail['sell_price'];
                $ary_detail['lock_num'] = $ary_detail['num'];
                $ary_detail['available_num'] = $ary_detail['num'];
                if (in_array($ary_detail['barcode'], $use_barcode_1)) {
                     //插入明细数据
                    $use_sku_1[] = $ary_detail['sku'];
                    //有效数据
                    $detail_data_lof[] = $ary_detail;
                    $ret = $this->insert($ary_detail);
                    if (1 != $ret['status']) {
                        $this->rollback();
                        return $ret;
                    }
                }               
            }
            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($pid, $detail_data_lof);
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $store_code, 'stm_stock_lock', $detail_data_lof);
            $sql = "select * from b2b_lof_datail where order_code=:order_code  AND  order_type=:order_type ";
            $ret_lof_details['data'] = $this->db->get_all($sql, array(
                ':order_code' => $record['data']['record_code'],
                ':order_type' => 'stm_stock_lock',
            ));
            $new_lof_details = array();
            foreach ($ret_lof_details['data'] as $details) {
                $ret_sku_lof = $details['sku'] . '_' . $details['lof_no'];
                if (in_array($ret_sku_lof, $add_sku_lof)) {
                    $new_lof_details[] = $details;
                    $now_import_sku[$details['sku']] = $details['id'];
                }
            }
            $use_datail_lof = array();
            foreach ($new_lof_details as $value) {
                if (in_array($value['sku'], $use_sku_1)) {
                    $use_datail_lof[] = $value;
                }
            }
            //锁定
            require_model('prm/InvOpModel');
            $invobj = new InvOpModel($record['data']['record_code'], 'stm_stock_lock', $record['data']['store_code'], 1, $use_datail_lof);
            $ret = $invobj->adjust();
            //返回信息处理
            if (!empty($short_barcode)) {
                $sku_info = array();
                //库存不足，提示错误信息                
                foreach ($short_barcode as $barcode) {                        
                    $barcode_info[$barcode] = "系统可用库存不足";
                    $sku_info[] = oms_tb_val('goods_barcode', 'sku', array('barcode' => $barcode));
                }
                //删除表b2b_lof_detail表
                foreach ($sku_info as $val) {
                    $id[] =$now_import_sku[$val];
                }
                $sql_value = array();
                $id_str = $this->arr_to_in_sql_value($id, 'id', $sql_value);
                $sql = "DELETE FROM b2b_lof_datail WHERE id IN ({$id_str})";
                $ret = $this->query($sql, $sql_value);               
            }else{
                $record_log = array();
                //回写批次明细
                foreach ($new_lof_details as $lock_detail) {
                    $ret = $this->new_mainWriteBackDetail($record['data']['record_code'], $lock_detail['sku']);                    
                }               
            }
        }
        //回写数量和金额
        $this->new_mainWriteBack($record['data']['record_code']);
        $this->commit();
        if (!empty($err_barcode) || !empty($barcode_info)) {
            return $this->format_ret('-3',$err_barcode,$barcode_info);
        } else {
            return $this->format_ret('1','','新增成功!');
        }
    }
    
    //缺货商品条码
    function short_stock($ary_details,$loft_status,$store_code){
        $err_arr = array();
        foreach ($ary_details as $value) {
            $num = $value['num'];
            $sku = $value['sku'];            
            $barcode = $value['barcode'];
            if ($loft_status == 1) {
                $lof_no = $value['lof_no'];
                $sql4 = "select stock_num,lock_num from goods_inv_lof where sku = '{$sku}' and store_code = '{$store_code}' and lof_no = '{$lof_no}';";
                $data4 = $this->db->get_row($sql4);
                $can_use_inv = $data4['stock_num'] - $data4['lock_num'];
                $short_inv = $num - $can_use_inv;
                if ($short_inv > 0) {
                    $err_arr[] = $barcode;
                }
            }else{
                $sql3 = "select stock_num,lock_num from goods_inv where sku = '{$sku}' and store_code = '{$store_code}';";
                $data3 = $this->db->get_row($sql3);
                $can_use_inv = $data3['stock_num'] - $data3['lock_num'];
                $short_inv = $num - $can_use_inv;
                if ($short_inv > 0) {
                    $err_arr[] = $barcode;
                }
            }            
        }
        return $err_arr;
    }

    /**主单回写
     * @param $record_id
     * @return array
     */
    public function mainWriteBack($record_id) {
        //回写数量和金额
        $sql = "update stm_stock_lock_record set
                  stm_stock_lock_record.lock_num = (select sum(lock_num) from stm_stock_lock_record_detail where pid = :id),
                  stm_stock_lock_record.release_num = (select sum(release_num) from stm_stock_lock_record_detail where pid = :id),
                  stm_stock_lock_record.available_num = (select sum(available_num) from stm_stock_lock_record_detail where pid = :id),
                  stm_stock_lock_record.money = (select sum(money) from stm_stock_lock_record_detail where pid = :id)
                where stm_stock_lock_record.stock_lock_record_id = :id ";
        $ret = $this->query($sql, array(':id' => $record_id));
        return $ret;
    }

    /**编辑
     * @param $detail
     * @return array
     */
    public function edit_detail_action($detail) {
        $detial_info = $this->get_row(array('pid' => $detail['pid'], 'sku' => $detail['sku']));
        if ($detial_info['status'] != 1) {
            return $this->format_ret(-1, '', '明细不存在!');
        }
        $detail_data = $detial_info['data'];
        $record = load_model('stm/StockLockRecordModel')->get_row(array('stock_lock_record_id' => $detail['pid']));
        if ($record['data']['order_status'] != 0) {
            return $this->format_ret(-1, '', '单据只有在未锁定状态才能修改!');
        }
        $this->begin_trans();
        $ret = $this->update(array('lock_num' => $detail['lock_num']), array('pid' => $detail['pid'], 'sku' => $detail['sku']));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '更新失败!');
        }
        $this->update_lof_detail($detail_data['record_code'], $detail_data['sku'], $detail['lock_num']);
        //回写数量和金额
        $this->new_mainWriteBack($detail_data['record_code']);
        $this->commit();
        //添加日志
        if ($detail_data['lock_num'] != $detail['lock_num']) {
            $barcode=oms_tb_val('goods_sku','barcode',array('sku'=>$detail['sku']));
            $log_message=$barcode.': 锁定数量由'.$detail_data['lock_num'].'修改为'.$detail['lock_num'];
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未锁定', 'action_note' => $log_message,'action_name' => '修改锁定数量', 'module' => "stock_lock_record", 'pid' => $detail['pid']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $this->format_ret('1','','更新成功!');
    }


    /**更新批次表
     * @param $record_code
     * @param $sku
     * @param $num
     */
    function update_lof_detail($record_code, $sku, $num) {
        $sql = "select * from b2b_lof_datail where order_code=:record_code AND sku=:sku AND order_type='stm_stock_lock' ";
        $data = $this->db->get_all($sql, array(':record_code' => $record_code, 'sku' => $sku));
        $is_only = 0;
        foreach ($data as $val) {
            if ($is_only == 0) {
                $sql = "update b2b_lof_datail set num='{$num}',init_num='{$num}' where id='{$val['id']}' ";
                $is_only = 1;
            } else {
                $sql = "delete from b2b_lof_datail   where id='{$val['id']}' ";
            }
            $this->db->query($sql);
        }
    }

    /**批次状态，回写明细
     * @param $record_id
     * @param $sku
     * @param string $order_type
     * @return array
     */
    public function mainWriteBackDetail($record_id, $sku, $order_type = 'stm_stock_lock') {
        $sql = "update {$this->table} set
                available_num = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type),
                lock_num = (select sum(init_num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type),
                release_num = (select sum(fill_num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type)
                where pid = :pid and sku = :sku";
        $ret = $this->query($sql, array(':pid' => $record_id, ':sku' => $sku, ':order_type' => $order_type));
        return $ret;
    }


    public function new_mainWriteBackDetail($record_code, $sku, $order_type = 'stm_stock_lock') {
        $sql = "update {$this->table} set
                available_num = (select sum(num) from b2b_lof_datail where order_code = :record_code and sku = :sku and order_type = :order_type),
                lock_num = (select sum(init_num) from b2b_lof_datail where order_code = :record_code and sku = :sku and order_type = :order_type),
                release_num = (select sum(fill_num) from b2b_lof_datail where order_code = :record_code and sku = :sku and order_type = :order_type)
                where record_code = :record_code and sku = :sku";
        $ret = $this->query($sql, array(':record_code' => $record_code, ':sku' => $sku, ':order_type' => $order_type));
        return $ret;
    }



    public function new_mainWriteBack($record_code) {
        //回写数量和金额
        $sql = "update stm_stock_lock_record set
                  stm_stock_lock_record.lock_num = (select sum(lock_num) from stm_stock_lock_record_detail where record_code = :record_code),
                  stm_stock_lock_record.release_num = (select sum(release_num) from stm_stock_lock_record_detail where record_code = :record_code),
                  stm_stock_lock_record.available_num = (select sum(available_num) from stm_stock_lock_record_detail where record_code = :record_code),
                  stm_stock_lock_record.money = (select sum(money) from stm_stock_lock_record_detail where record_code = :record_code)
                where stm_stock_lock_record.record_code = :record_code ";
        $ret = $this->query($sql, array(':record_code' => $record_code));
        return $ret;
    }

}
