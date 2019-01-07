<?php

/**
 * 库单详情管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('stm');

class StoreOutRecordDetailModel extends TbModel {

    function get_table() {
        return 'wbm_store_out_record_detail';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_main = "FROM wbm_store_out_record rl
                    INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
                    INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
                    INNER JOIN goods_sku r4 on r2.sku = r4.sku
                    WHERE  1=1  ";

        $sql_values = array();
        $select  ='';
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
                $sql_main .= " AND (r2.num != r2.enotice_num )";
            } else {
                $sql_main .= " AND (r2.num = r2.enotice_num )";
            }
        }
        //如果是装箱扫描 点击差异时：不现实 差异数 = 0；
        $from_type = '';
        if (isset($filter['from_type']) && $filter['from_type'] == 'print_box_diff') {
            $sql_main .= " AND r2.num != r2.enotice_num";
            $from_type = 'box_diff';
        }
       
        $select .= 'r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,rl.is_store_out,rl.is_sure,rl.store_code,r3.sell_price AS goods_sell_price,r4.price AS sku_sell_price,r3.brand_name ';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        //print_r($data);exit;

        foreach ($data['data'] as $key => $value) {
            //$data['data'][$key]['rebate'] = round($value['rebate'],2);
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = round($data['data'][$key]['price1'], 3);
            //差异数
            $data['data'][$key]['num_differ'] = $data['data'][$key]['enotice_num'] - $data['data'][$key]['num'];
            $data['data'][$key]['sell_price'] = empty($value['sku_sell_price']) || $value['sku_sell_price'] == 0 ? $value['goods_sell_price'] : $value['sku_sell_price'];


            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);

            if ($from_type == 'box_diff') {
                $sql = "SELECT GROUP_CONCAT(bs.shelf_name) AS shelf_name FROM goods_shelf gs INNER JOIN base_shelf bs ON gs.shelf_code=bs.shelf_code AND gs.store_code=bs.store_code WHERE gs.sku='{$value['sku']}' AND gs.store_code='{$value['store_code']}'";
                $shelf_name = $this->db->get_value($sql);
                $data['data'][$key]['shelf_name'] = $shelf_name ? $shelf_name : '';
            }
        }

        //print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_page_lof($filter) {
        $sql_join = "";

        $sql_main = "FROM wbm_store_out_record rl
		INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		INNER JOIN b2b_lof_datail r4 on r2.record_code = r4.order_code
		INNER JOIN goods_sku r5 on r2.sku = r5.sku
		WHERE  r2.sku = r4.sku and r4.order_type = :order_type  ";

        $sql_values = array();
        $sql_values[':order_type'] = 'wbm_store_out';

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
        $select = 'r4.id,r4.pid,r4.init_num,r2.*,rl.is_store_out,rl.is_sure,r3.goods_name,r5.barcode,r4.num,r4.lof_no,r4.production_date,r5.spec1_name,r5.spec2_name,r3.brand_name';
        //echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        //		print_r($data);exit;

        foreach ($data['data'] as $key => $value) {

            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = round($data['data'][$key]['price1'], 3);
            $data['data'][$key]['money'] = $data['data'][$key]['price1'] * $value['num'];
            $data['data'][$key]['money'] = round($data['data'][$key]['money'], 3);
            //差异数
            $data['data'][$key]['num_differ'] = $data['data'][$key]['init_num'] - $data['data'][$key]['num'];
            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);
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
        $detail = $this->get_row(array('store_out_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'store_out_record_id');
            if (isset($record['data']['is_store_out']) && 1 == $record['data']['is_store_out']) {
                return $this->format_ret(false, array(), '单据已出库!不能删除明细');
            }
        }
        $result = parent::delete(array('store_out_record_detail_id' => $id));
        $this->mainWriteBack($detail['data']['pid']);
        return $result;
    }

    /**
     * 根据ID删除批次行数据
     * @param $id
     * @return array|void
     */
    function delete_lof($id, $is_box_task) {
        $lof_data = $this->db->get_row("select * from b2b_lof_datail where id=:id", array(':id' => $id));
        if (empty($lof_data)) {
            return $this->format_ret(false, array(), '该批次不存在!不能删除');
        }

        $detail = $this->get_row(array('pid' => $lof_data['pid'], 'sku' => $lof_data['sku']));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'store_out_record_id');
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

        if ($lof_data['init_num'] == $detail['data']['enotice_num']) {
            //$result = parent::delete(array('stock_adjust_record_detail_id'=>$id));
            $result = parent::delete(array('store_out_record_detail_id' => $detail['data']['store_out_record_detail_id']));
            $res = ($result['status'] < 1) ? FALSE : TRUE;
        } else {
            $res = $this->mainWriteBackDetail($lof_data['pid'], $lof_data['sku']);
        }
        if ($res === FALSE) {
            $this->rollback();
            return $result;
        }
        //is_box_task==1 开启批次时存在装箱扫描数据 删除时 需把装箱信息一起删除
        if ($is_box_task == 1) {
            $ret2 = load_model('b2b/BoxRecordDatailModel')->delete_box_record($lof_data['pid'], $lof_data['sku'], 'wbm_store_out');
            if ($ret2['status'] != 1) {
                $this->rollback();
                return $ret2;
            }
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
    public function mainWriteBackDetail($record_id, $sku, $order_type = 'wbm_store_out') {
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

        $m = load_model('wbm/StoreOutRecordModel');
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
     * 根据明细的sku和主单据id,判断明细是否已经存在
     * @param   int     $pid    主单据ID
     * @param   string  $sku    SKU编号
     * @return  boolean 存在返回true
     */
    public function is_detail_sku($pid, $sku_arr) {

        $sku_str = "'" . implode("','", $sku_arr) . "'";
        $sql = "select sku from {$this->table} where pid=:pid AND  sku in ({$sku_str})  ";
        $sql_values = array(':pid' => $pid);
        $this->db->get_all($sql, $sql_values);
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
    public function add_detail_action($pid, $ary_details, $api_update_status = NULL) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'store_out_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '采购出库单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret(false, array(), '采购出库单已验收, 不能修改明细!');
        }

        $new_ary_details = array();
        foreach ($ary_details as $val) {
            if (!isset($new_ary_details[$val['sku']])) {
                $new_ary_details[$val['sku']] = $val;
            } else {
                $new_ary_details[$val['sku']]['num'] += $val['num'];
            }
        }
        //   $sku_arr = array_keys($new_ary_details);



        $ary_details = &$new_ary_details;
        $this->begin_trans();
        try {
            $detail_data = array();
            foreach ($ary_details as $ary_detail) {
                if (isset($ary_detail['num_flag']) && $ary_detail['num_flag'] == '1') {
                    //兼容入库为0
                } else {
                    if (!isset($ary_detail['num']) || $ary_detail['num'] == 0) {
                        continue;
                    }
                }
                //if(!isset($ary_detail['num'])||$ary_detail['num']==0){
                // continue;
                // }
                $ary_detail['pid'] = $pid;
                $ary_detail['record_code'] = $record['data']['record_code'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断

                $ary_detail['rebate'] = $record['data']['rebate'];
                // $ary_detail['refer_price'] = $ary_detail['price'];
                if(isset($record['data']['jx_code']) && !empty($record['data']['jx_code'])) {
                    $ary_detail['price'] = load_model('fx/GoodsManageModel')->compute_fx_price($record['data']['distributor_code'],$ary_detail, $record['data']['order_time']);
                } else {
                    $ary_detail['price'] = $ary_detail['trade_price'];
                }
                $ary_detail['price'] = round($ary_detail['price'], 3);
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'] * $ary_detail['rebate'];
                $ary_detail['money'] = round($ary_detail['money'], 3);
                //判断SKU是否已经存在
//                $check = $this->is_detail_exists($pid,$ary_detail['sku']);
//                if($check){
//                	//批次表里查出该单据数量和价格
//                	$pici =load_model('stm/GoodsInvLofRecordModel')->get_detail_cnt('wbm_store_out',$pid,$ary_detail['sku']);
//                    $ary_detail['num'] = isset($pici[0]['cnt'])?$pici[0]['cnt']:'';
//
//                    $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'] * $ary_detail['rebate'];
//                    $ary_detail['money'] = round($ary_detail['money'],3);
//                	//更新明细数据
////                    $ret = $this->update($ary_detail,array(
////                        'pid'=>$pid,'sku'=>$ary_detail['sku']
////                    ));
//                }
//                else{
//                    //插入明细数据
//                    $ret = $this->insert($ary_detail);
//                }
                $detail_data[] = $ary_detail;

//                if(1 != $ret['status']){
//                    return $ret;
//                }
            }



            $data_all = array();
            if (count($detail_data) > 300) {
                $data_all = array_chunk($detail_data, 300);
            } else {
                $data_all[] = $detail_data;
            }

            foreach ($data_all as $detai_arr) {
                $update_str = " rebate = VALUES(rebate),price = VALUES(price),money = VALUES(money),num = VALUES(num) ";
                $this->insert_multi_duplicate($this->table, $detai_arr, $update_str);
            }
            $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
            $lof_status = $lof_manage['lof_status'];
            if ($lof_status == 1) {
                $sql = "insert into {$this->table} (record_code,sku,num,enotice_num) select order_code,sku,sum(num) as num,sum(init_num) as init_num from b2b_lof_datail where
                order_code=:order_code AND order_type=:order_type GROUP BY sku  ON DUPLICATE KEY UPDATE num=VALUES(num),enotice_num=VALUES(enotice_num)
                        ";
                $sql_values = array(':order_code' => $record['data']['record_code'], ':order_type' => 'wbm_store_out');
                $this->db->query($sql, $sql_values);
                $sql_up = "update {$this->table} set money=price*num*rebate where record_code='{$record['data']['record_code']}'";
                $this->db->query($sql_up);
            }


            //更新批发销货单（明细提交）不更新主单
            if ($api_update_status != 'no_update_record') {
                //回写数量和金额
                $this->mainWriteBack($pid);
            }
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

        $sql = "select is_sure  from wbm_store_out_record  where store_out_record_id=:id";
        $is_sure = $this->db->get_value($sql, array(':id' => $pid));
        if ($is_sure == 1) {
            return $this->format_ret(-1, '', '单据已经验收，不能修改！');
        }
        $this->begin_trans();
        try {
            $is_lof = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
            foreach ($ary_details as $ary_detail) {
                $ret_detail = $this->get_row(array('record_code' => $ary_detail['record_code'], 'sku' => $ary_detail['sku']));
                $ret_detail = $ret_detail['data'];
                if ($ret_detail['enotice_num'] != 0 && $ary_detail['num'] > $ret_detail['enotice_num']) {
                    return $this->format_ret(-1, '', '出库数不能大于通知数');
                }

                //$ary_detail['pid'] = $pid;
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
                if ($ary_detail['num'] != $ret_detail['num']) {
                    $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未出库', 'action_name' => '修改数量', 'action_note' => "条码：{$ary_detail['barcode']}，数量由{$ret_detail['num']}改为{$ary_detail['num']}", 'module' => "store_out_record", 'pid' => $pid);
                    load_model('pur/PurStmLogModel')->insert($log);
                }
                if ($ary_detail['price']!=$ret_detail['price']) {
                    $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未出库', 'action_name' => '修改批发价', 'action_note' => "条码：{$ary_detail['barcode']}，批发价由{$ret_detail['price']}改为{$ary_detail['price']}", 'module' => "store_out_record", 'pid' => $pid);
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

    function update_lof_detail($record_code, $sku, $num) {
        $sql = "select * from b2b_lof_datail where order_code=:record_code AND sku=:sku AND order_type='wbm_store_out' ";

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

    /**
     * 批次明细编辑
     * @param int $pid 主单据ID
     * @param array $detail 更新明细
     * @param $lof_info 批次数据
     */
    public function edit_detail_action_lof($pid, $detail, $lof_info) {
        $ret_detail = $this->get_row(array('record_code' => $lof_info['record_code'], 'sku' => $detail['sku']));
        $ret_detail = $ret_detail['data'];
        if ($ret_detail['enotice_num'] != 0 && $lof_info['num'] > $ret_detail['enotice_num']) {
            return $this->format_ret(-1, '', '出库数不能大于通知数');
        }

        unset($lof_info['is_notice']);
        $sql = "UPDATE b2b_lof_datail SET num='{$lof_info['num']}' WHERE order_type = 'wbm_store_out' and order_code = '{$lof_info['record_code']}' AND sku = '{$detail['sku']}' AND lof_no = '{$lof_info['lof_no']}' ";
        $this->db->query($sql);
        $total_num = 0;
        $lof_sql = "SELECT sku,lof_no,production_date,num,init_num FROM b2b_lof_datail WHERE order_type = 'wbm_store_out' AND order_code = :order_code AND sku = :sku";
        $lof_data = $this->db->get_all($lof_sql, array(":order_code" => $lof_info['record_code'], ":sku" => $detail['sku']));
        if (!empty($lof_data)) {
            foreach ($lof_data as $lof) {
                $total_num += $lof['num'];
            }
        }
        $detail['num'] = $total_num;
        $ret = parent::update(array('num' => $detail['num'], 'money' => $detail['num'] * $detail['price'] * $detail['rebate']), array('pid' => $pid, 'sku' => $detail['sku']));
        $this->mainWriteBack($pid);

        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未出库', 'action_name' => '修改数量', 'action_note' => "条码：{$detail['barcode']}，数量由{$ret_detail['num']}改为{$detail['num']}", 'module' => "store_out_record", 'pid' => $pid);
        load_model('pur/PurStmLogModel')->insert($log);
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
//        $sql = "update wbm_store_out_record set
//                  wbm_store_out_record.num = (select sum(num) from wbm_store_out_record_detail where pid = :id),
//                  wbm_store_out_record.money = (select sum(money) from wbm_store_out_record_detail where pid = :id),
//                  wbm_store_out_record.enotice_num = (select sum(enotice_num) from wbm_store_out_record_detail where pid = :id)
//                where wbm_store_out_record.store_out_record_id = :id ";

        $sql = "select record_code from wbm_store_out_record where store_out_record_id=:id ";
        $record_code = $this->db->get_value($sql,array(':id'=>$record_id));
        $sql_detail = "select sum(num)  num,sum(money)  money,sum(enotice_num) enotice_num from wbm_store_out_record_detail where record_code = :record_code ";
        $detail_data = $this->db->get_row($sql_detail,array(':record_code'=>$record_code));
  
        if (empty($detail_data)) {
            $detail_data = array(
                'num' => 0,
                'money' => 0,
                'enotice_num' => 0,
            );
        }
        $res = $this->update_exp('wbm_store_out_record',$detail_data, array('store_out_record_id' => $record_id));
        return $res;
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*") {

        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    /*
     * 获取实际出库数
     */

    function get_num_by_record($record_code) {
        $sql = "SELECT 1 FROM wbm_store_out_record_detail WHERE num>0 AND record_code='{$record_code}' ";
        $result = $this->db->get_row($sql);
        if (empty($result)) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-1);
        }
    }
    function  get_sku_id_by_pid($store_out_record_id){
        $sql="SELECT r1.sku_id,r2.enotice_num FROM goods_sku r1 LEFT JOIN {$this->table} r2 ON r1.sku=r2.sku WHERE r2.pid='{$store_out_record_id}'";
        $value=$this->db->get_all($sql);
        $sku_info=array();
        foreach($value as $k=>$id){
            $sku_info[$k]['sku_id']=$id['sku_id'];
            $sku_info[$k]['num']=$id['enotice_num'];
        }
        return $sku_info;
    }
}
