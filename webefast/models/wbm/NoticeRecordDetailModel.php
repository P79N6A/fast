<?php

/**
 * 库单详情管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('stm');

class NoticeRecordDetailModel extends TbModel {

    function get_table() {
        return 'wbm_notice_record_detail';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";

        $sql_main = "FROM wbm_notice_record rl
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
        //是否有差异单
        if (isset($filter['difference_models']) && $filter['difference_models'] != '') {
            if ($filter['difference_models'] == 1) {
                $sql_main .= " AND (r2.num != r2.finish_num )";
            } else {
                $sql_main .= " AND (r2.num = r2.finish_num )";
            }
        }

        $select = 'r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,rl.is_finish,rl.is_sure,r4.price AS sku_sell_price,r3.sell_price AS goods_sell_price,r3.brand_name';
        if (isset($filter['is_fenye']) && $filter['is_fenye'] == '1') {
            $filter['page_size'] = 1000;
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        //print_r($data);exit;

        foreach ($data['data'] as $key => $value) {
            //$data['data'][$key]['rebate'] = round($value['rebate'],2);
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = round($data['data'][$key]['price1'], 3);
            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);
            $data['data'][$key]['diff_num'] = $value['num'] - $value['finish_num'];
            $data['data'][$key]['sell_price'] = empty($value['sku_sell_price']) || $value['sku_sell_price'] == 0 ? $value['goods_sell_price'] : $value['sku_sell_price'];
        }

        //print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_page_lof($filter) {
        $sql_join = "";

        $sql_main = "FROM wbm_notice_record rl
		INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		INNER JOIN b2b_lof_datail r4 on r2.record_code = r4.order_code
		INNER JOIN goods_sku r5 on r2.sku = r5.sku
		WHERE  r2.sku = r4.sku and r4.order_type = :order_type  ";

        $sql_values = array();
        $sql_values[':order_type'] = 'wbm_notice';

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
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }

        //$select = 'r2.*,r3.goods_name,r4.barcode,rl.is_store_out';
        $select = 'r4.id,r4.pid,r2.*,rl.is_finish,rl.is_sure,r3.goods_name,r5.spec1_name,r5.spec2_name,r5.barcode,r4.fill_num,r4.init_num,r4.num,r4.lof_no,r4.production_date,r3.brand_name';
        //echo $sql_main;
        if (isset($filter['is_fenye']) && $filter['is_fenye'] == '1') {
            $filter['page_size'] = 1000;
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        //		print_r($data);exit;

        foreach ($data['data'] as $key => $value) {

            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = round($data['data'][$key]['price1'], 3);
            $data['data'][$key]['money'] = $data['data'][$key]['price1'] * $value['init_num'];
            $data['data'][$key]['money'] = round($data['data'][$key]['money'], 3);
            ;
            $data['data'][$key]['diff_num'] = $value['init_num'] - $value['fill_num'];
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);
        }

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
            $ary_detail['goods_code'] = $info['goods_code'];
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
            $ary_detail['goods_code'] = $info['goods_code'];
        }
        return parent::update($ary_detail, $where);
    }

    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id) {
        $detail = $this->get_row(array('notice_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'notice_record_id');
            if (isset($record['data']['is_sure']) && 1 == $record['data']['is_sure']) {
                return $this->format_ret(false, array(), '单据已确认!不能删除明细');
            }
        }
        $result = parent::delete(array('notice_record_detail_id' => $id));
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
            $record = $this->is_exists($detail['data']['pid'], 'notice_record_id');
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
            $result = parent::delete(array('notice_record_detail_id' => $detail['data']['notice_record_detail_id']));
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
    public function finishWriteBackDetail($record_code, $sku, $order_type = 'wbm_notice') {
        $sql = "update wbm_notice_record_detail set
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

        $m = load_model('wbm/NoticeRecordModel');
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
     * 新增多条通知单明细记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-10-28
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function add_detail_action($pid, $ary_details) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'notice_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '采购出库单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret(false, array(), '采购出库单已验收, 不能修改明细!');
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
                $ary_detail['price'] = $ary_detail['trade_price'];
                $ary_detail['price'] = round($ary_detail['price'], 3);
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['trade_price'] * $ary_detail['rebate'];
                $ary_detail['money'] = round($ary_detail['money'], 3);
                $detail_data[] = $ary_detail;
            }
            $data_all = array();
            if (count($detail_data) > 300) {
                $data_all = array_chunk($detail_data, 300);
            } else {
                $data_all[] = $detail_data;
            }

            foreach ($data_all as $detai_arr) {
                $update_str = " money=VALUES(money), price=VALUES(price), rebate=VALUES(rebate), num=VALUES(num) ";
                $this->insert_multi_duplicate($this->table, $detai_arr, $update_str);
            }

            $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
            $lof_status = $lof_manage['lof_status'];
            if ($lof_status == 1) {
                $sql = "insert into {$this->table} (record_code,sku,num) select order_code,sku,sum(num) as num from b2b_lof_datail where
                order_code=:order_code AND order_type=:order_type GROUP BY sku  ON DUPLICATE KEY UPDATE num=VALUES(num)
                        ";
                $sql_values = array(':order_code' => $record['data']['record_code'], ':order_type' => 'wbm_notice');
                $this->db->query($sql, $sql_values);
                $sql_up = "update {$this->table} set money=price*num*rebate where record_code='{$record['data']['record_code']}'";
                $this->db->query($sql_up);
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
        $sql = "select * from wbm_notice_record where  notice_record_id=:notice_record_id ";
        $data = $this->db->get_row($sql, array(':notice_record_id' => $pid));


        try {
            foreach ($ary_details as $ary_detail) {

//                 $ary_detail['notice_record_detail_id'] = $pid;
                //更新明细数据
//                 $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'];
                $ret = $this->update($ary_detail, array(
                    'pid' => $pid, 'sku' => $ary_detail['sku']
                ));
                $this->update_lof_detail($data['record_code'], $ary_detail['sku'], $ary_detail['num'], $ary_detail['lof_no']);
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

    function update_lof_detail($record_code, $sku, $num, $lof_no='') {
        $sql = "select * from b2b_lof_datail where order_code=:record_code AND sku=:sku AND order_type='wbm_notice' ";
        if (isset($lof_no) && $lof_no != '') {
            $sql .= "AND lof_no = '{$lof_no}'";
        }
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
     * 回写完成数
     * @param   string  $record_code   主单据号
     * @param   string  $data
     */
    public function update_finish_num($record_code, $data, $type = '') {
        $sku_arr = array();
        foreach ($data as $ary_detail) {
            //$sql = "update pur_order_record_detail set finish_num = finish_num + {$ary_detail['num']} where record_code = :record_code and sku = :sku ";
            $sql = "update b2b_lof_datail set fill_num = fill_num + {$ary_detail['num']} where order_type = 'wbm_notice'  and order_code = :record_code and sku = :sku and lof_no = :lof_no  ";
            $res = $this->query($sql, array(':record_code' => $record_code, ':sku' => $ary_detail['sku'], ':lof_no' => $ary_detail['lof_no']));
            $sku_arr[$ary_detail['sku']] = $ary_detail['sku'];
            /*
              $ret = load_model('pur/OrderRecordModel')->get_by_field('record_code', $record_code);
              if(isset($ret['data']['relation_code']) && $ret['data']['relation_code'] <> ''  ){
              //回写采购计划完成数
              $ret	= load_model('pur/PlannedRecordDetailModel')->update_finish_num($ret['data']['relation_code'],$ary_detail['sku'],$ary_detail['num']);
              } */
        }

        foreach ($sku_arr as $v) {
            $this->finishWriteBackDetail($record_code, $v, 'wbm_notice');
        }
        $ret = $this->mainFinishWriteBack($record_code);
        //回写采购订单完成状态
        $ret1 = load_model('wbm/NoticeRecordModel')->update_finish($record_code);
        if ($ret1['status'] == '1') {
            $ret = load_model('wbm/NoticeRecordModel')->update_check_record_code('1', 'is_finish', $record_code, $type);
            $sql = "SELECT notice_record_id FROM wbm_notice_record WHERE record_code=:record_code";
            $sql_values = array(':record_code'=>$record_code);
            $notice_record_id = $this->db->get_value($sql, $sql_values);
            if ('1' == $ret['status'] && 'store_out_record' == $type && !empty($notice_record_id)){
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '完成', 'action_name' => '完成', 'module' => "wbm_notice_record", 'pid' => $notice_record_id);
                load_model('pur/PurStmLogModel')->insert($log);
            }
        }

        //回写计划单完成状态
        /*
          $data1 = load_model('pur/OrderRecordModel')->get_by_field('record_code', $record_code, 'relation_code');
          if(isset($data1['data']['relation_code']) && $data1['data']['relation_code'] <> '' ){
          $ret1 = load_model('pur/PlannedRecordModel')->update_finish($data1['data']['relation_code']);
          if($ret1['status'] == '1'){
          $ret = load_model('pur/PlannedRecordModel')->update_check_record_code('1','is_finish', $data1['data']['relation_code']);
          }
          } */
        return $ret;
    }

    //回写主表完成数
    public function mainFinishWriteBack($record_code) {

        //回写完成数量
        $sql = "update wbm_notice_record set
	   	wbm_notice_record.finish_num = (select sum(finish_num) from wbm_notice_record_detail where record_code = :id)
	   	where wbm_notice_record.record_code = :id ";
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
        $sql = "update wbm_notice_record set
                  wbm_notice_record.num = (select sum(num) from wbm_notice_record_detail where pid = :id),
                  wbm_notice_record.finish_num = (select sum(finish_num) from wbm_notice_record_detail where pid = :id),
                  wbm_notice_record.money = (select sum(money) from wbm_notice_record_detail where pid = :id)
                where wbm_notice_record.notice_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }

    function imoprt_detail($id, $sku_arr, $import_data, $is_lof = 0) {
        //获取导入的总条数
        $err_num = count($import_data);
        $error_msg = array();
        $sku_lof = array();

        //判断主单据的pid是否存在
        $record = $this->is_exists($id, 'notice_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '采购出库单明细所关联的主单据不存在!');
        }

        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
        //将条形码转换成字符串
        $sku_str = "'" . implode("','", $sku_arr) . "'";
        //将批次号转换成字符串
        foreach ($import_data as $val) {
            $lof_no .= "'" . $val['lof_no'] . "',";
        }
        $lof_no = rtrim($lof_no, ',');
        if ($is_lof == 0) {
            $sql = "select DISTINCT b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.price,g.sell_price,g.trade_price from
                    goods_sku b inner join  base_goods g ON g.goods_code = b.goods_code where b.barcode in({$sku_str}) ";
        } else {
            $sql = "select DISTINCT b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.price,g.sell_price,g.trade_price,r1.lof_no from
                    goods_sku b inner join  base_goods g ON g.goods_code = b.goods_code INNER JOIN goods_lof r1 ON r1.sku = b.sku where b.barcode in({$sku_str}) AND r1.lof_no IN ({$lof_no})";
        }
        $detail_data_ret = $this->db->get_all($sql);
        foreach ($detail_data_ret as $k => $v) {
            $detail_data[$v['barcode']] = $v;
        }
        foreach ($import_data as $key => $val) {
            $sql = "SELECT * FROM goods_sku WHERE barcode = '{$val['sku']}';";
            $barcode = $this->db->get_all($sql);
            if (empty($barcode)) {
                $error_msg[] = array($val['sku'] => '商品条形码不存在');
                $sku_lof[] = $key;
//                unset($detail_data[$val['sku']]);
            }
        }
        if ($is_lof == 1) {
            //判断批次号是否存在
            $err_msg = $this->is_lof_no($import_data, $sku_lof);
            if (!empty($err_msg)) {
                foreach ($err_msg as $key => $val) {
                    $error_msg[] = array($key => $val);
                }
            }
        }
        foreach ($sku_lof as $val) {
            unset($import_data[$val]);
        }
        $detail_data_lof = array();
        if ($is_lof == 0) {
            foreach ($detail_data as &$val) {

                $num = $import_data[$val['barcode']]['num'];
                if (is_numeric($num) && $num > 0) {
                    $val['num'] = round($num);
                    $val['trade_price'] = isset($import_data[$val['barcode']]['price']) && !empty($import_data[$val['barcode']]['price']) ? $import_data[$val['barcode']]['price'] : $val['trade_price'];
                    $val['trade_price'] = sprintf('%.3f', $val['trade_price']);
                    $key = array_search($val['barcode'], $sku_arr);
                    $lof_val = $val;
                    $lof_val['lof_no'] = $ret_lof['data']['lof_no'];
                    $lof_val['production_date'] = $ret_lof['data']['production_date'];
                    $detail_data_lof[] = $val;
                    unset($sku_arr[$key]);
                }else{
                    $error_msg[] = array($val['barcode'] => '数量不能为空');
                    unset($sku_arr[$key]);
                }
            }
        } else {
            $sql = "SELECT sku.barcode,lof.lof_no,lof.production_date FROM goods_sku AS sku INNER JOIN goods_lof AS lof ON sku.sku = lof.sku WHERE sku.barcode in ({$sku_str}) AND lof.lof_no in ({$lof_no});";
            $date_arr = $this->db->get_all($sql);
            foreach ($date_arr as $val) {
                $code_lof = $val['barcode'] . '_' . $val['lof_no'];
                if (isset($import_data[$code_lof]) && !empty($import_data[$code_lof])) {
                    $import_data[$code_lof]['production_date'] = $val['production_date'];
                }
            }
            foreach ($import_data as $val) {
                $num = $val['num'];
                if (is_numeric($num) && $num > 0) {
                    $detail_data[$val['sku']]['num'] = round($num);
                    $detail_data[$val['sku']]['trade_price'] = isset($val['price']) && !empty($val['price']) ? $val['price'] : $detail_data[$val['sku']]['trade_price'];
                    $detail_data[$val['sku']]['trade_price'] = sprintf('%.3f', $detail_data[$val['sku']]['trade_price']);
                    $detail_data[$val['sku']]['lof_no'] = !empty($val['lof_no']) ? $val['lof_no'] : $ret_lof['data']['production_date'];
                    $detail_data[$val['sku']]['production_date'] = $val['production_date'];
                    $detail_data_lof[] = $detail_data[$val['sku']];
                }else{
                    $error_msg[] = array($val['sku'] => '数量不能为空');
                }
            }
        }
        $result['success'] = count($detail_data_lof);
        //"行<br>导入失败列表:<br>"+result.data.fail
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $detail_data_lof);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $record['data']['store_code'], 'wbm_notice', $detail_data_lof);

        if ($ret['status'] != '1') {
            return $ret;
        }

        $ret = $this->add_detail_action($id, $detail_data);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '导入明细', 'module' => "wbm_notice_record", 'pid' => $id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
//        $ret['data'] = '';
//        if (!empty($sku_arr)) {
//            $result['fail'] = implode(',', $sku_arr);
//        }
//        $ret['data'] = $result;
//        $ret['data']['success_num'] = $result['success'];
        $success_num = $result['success'];
        $message = '导入成功' . $success_num;
        //失败数量
        $err_num = $err_num - $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $message .=',' . '失败数量:' . $err_num;
            $fail_top = array('商品条码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $key = array_keys($val);
            $val_data = array($key[0], $val[$key[0]]);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("notice_record_detail_import" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function get_select_data($id, $select_sku_arr) {
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($select_sku_arr, 'sku', $sql_values);
        $sql = " select goods_code,spec1_code,spec2_code,sku,refer_price,price,rebate,money,num,finish_num,(num-finish_num) as diff_num,record_code
            from  wbm_notice_record_detail  where pid='{$id}' and sku in($sku_str)";
        $data = $this->db->get_all($sql,$sql_values);
        return $this->format_ret(1, $data);
    }

    function add_detail_data($id, &$detail_data) {

    }

    function is_lof_no(&$import_data, &$sku_lof) {
        $err_data = '';
        foreach ($import_data as $key => $val) {
            $sql = "SELECT lof_no FROM goods_lof WHERE lof_no = '{$val['lof_no']}';";
            $barcode = $this->db->get_row($sql);
            if (empty($barcode)) {
                $err_data[$val['sku']] = '批次号' . $val['lof_no'] . '不存在';
                $sku_lof[] = $key;
            }
        }
        return $err_data;
    }
    function api_notice_detail_get($param){
        //可选字段
        $key_option = array(
            's' => array('record_code'),
            'i' => array('page','page_size')
        );
        $arr_option = array();
        valid_assign_array($param, $key_option, $arr_option);
        $arr_deal = $arr_option;
        if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
                    return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
            }
            //清空无用数据
            unset($arr_option);
            unset($param);
        $select = "r1.goods_code,r1.spec1_code,r1.spec2_code,r1.sku,r1.refer_price,r1.price,r1.money,r1.num,r1.finish_num,r1.remark ";
        $sql_values = array();
        $sql_join = "";
        $sql_main = " from wbm_notice_record_detail r1 {$sql_join} where 1";
        foreach ($arr_deal as $key => $val) {
                if ($key != 'page' && $key != 'page_size') {
                    if($key == 'record_code'){
                        $sql_values[":{$key}"] = $val;
                        $sql_main .= " AND r1.record_code =:{$key}";
                    }                    
                }
            }
        $sql_main .= ' group by notice_record_detail_id ';
        $ret = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select,true);
        foreach($ret['data'] as $key =>$v){
                $key_arr = array('goods_name', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'barcode');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($v['sku'], $key_arr);
                $ret['data'][$key] = array_merge($v, $sku_info);
        }
        if(empty($ret['data'])){
            return $this->format_ret(-1002, '', API_RETURN_MESSAGE_10002);
        }
        $ret_status = OP_SUCCESS;
        return $this -> format_ret($ret_status, $ret);
    }


    /**
     * 修改扫描数量
     * @param $record_code
     * @param $num
     * @param $id
     * @return array
     */
    function update_scan_num($record_code, $num, $id) {
        $ret = load_model('wbm/NoticeRecordModel')->get_row(array('record_code' => $record_code));
        $sku = substr($id, 8);
        $record = $ret['data'];
        if ($record['is_sure'] != 0) {
            return $this->format_ret('-1', '', '单据状态异常！');
        }
        $detail = $this->get_row(array('record_code' => $record_code, 'sku' => $sku));
        if (empty($detail['data'])) {
            return $this->format_ret(-1, '', '单据明细信息不存在');
        }
        $detail_data = $detail['data'];
        $detail_data['num'] = $num;
        $detail_data['money'] = $num * $detail_data['price'] * $detail_data['rebate'];
        $ret = $this->edit_detail_action($ret['data']['notice_record_id'], array($detail_data));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '扫描批发销货通知单更新单据明细数量失败！');
        }
        return $this->format_ret(1, '', '更新成功！');
    }

    /**
     * 开启批次，修扫描数量
     * @param $data
     * @return array
     */
    function update_scan_num_lof($data) {
        $ret = load_model('wbm/NoticeRecordModel')->get_row(array('record_code' => $data['record_code']));
        $record = $ret['data'];
        if ($record['is_sure'] != 0) {
            return $this->format_ret('-1', '', '单据状态异常！');
        }
        $id_arr = explode('_', $data['id']);
        $sku = $id_arr[2];
        $detail = $this->get_row(array('record_code' => $data['record_code'], 'sku' => $sku));
        if (empty($detail['data'])) {
            return $this->format_ret(-1, '', '单据明细信息不存在！');
        }

        $ret = $this->update_detail_action_lof($record['notice_record_id'], $detail['data'], $data);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '批发销货通知单更新单据明细数量失败');
        }
        return $this->format_ret(1, '', '更新成功');
    }

    public function update_detail_action_lof($pid, $detail, $lof_info) {
        $this->begin_trans();
        $sql = "UPDATE b2b_lof_datail SET num='{$lof_info['num']}',init_num='{$lof_info['num']}' WHERE order_type = 'wbm_notice' and order_code = '{$lof_info['record_code']}' AND sku = '{$detail['sku']}' AND lof_no = '{$lof_info['lof_no']}' ";
        $ret = $this->query($sql);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret('-1', '', '更新失败！');
        }
        $sql = "SELECT sum(num) from b2b_lof_datail WHERE order_type = 'wbm_notice' and order_code = '{$lof_info['record_code']}' AND sku = '{$detail['sku']}'";
        $num = $this->db->get_value($sql);
        $money = $num * $detail['price'] * $detail['rebate'];
        $update = array('num' => $num, 'money' => $money);
        $ret = $this->update($update, array(
            'pid' => $pid, 'sku' => $detail['sku']
        ));
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '回写明细失败！');
        }
        $this->commit();
        $this->mainWriteBack($pid);
        return $ret;
    }

}
