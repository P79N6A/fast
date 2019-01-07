<?php

/**
 * 库存移仓单详情管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
//require_lang('stm');
require_lang('pur');

class StoreShiftRecordDetailModel extends TbModel {

    function get_table() {
        return 'stm_store_shift_record_detail';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        //print_r($filter);
        $sql_join = "";
        $sql_main = "FROM stm_store_shift_record rl
		            INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		            INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
                            INNER JOIN goods_sku r4 on r2.sku = r4.sku
		            WHERE  1=1  ";
        $sql_values = array();
        //$sql_values[':order_type'] = 'adjust';
        // record_code查询
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r2.record_code = :record_code )";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            return;
        }
        //是否有差异款
        if (isset($filter['difference_sku']) && $filter['difference_sku'] != '') {
            if ($filter['difference_sku'] == 1) {
                    $sql_main .= " AND (r2.out_num != r2.in_num )";
            } else {
                    $sql_main .= " AND (r2.out_num = r2.in_num )";
            }
        }
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name or r4.barcode LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        $select = 'r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,rl.is_sure';
        //$sql_main .= "group by r2.sku";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //进货价权限
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['in_num'] = $value['in_num'] == 0 ? '' : $value['in_num'];
            $data['data'][$key]['in_money'] = $value['in_money'] == 0.000 ? '' : $value['in_money'];

            if ($status['status'] != 1) {
                $data['data'][$key]['price'] = '****';
                $data['data'][$key]['out_money'] = '****';
                $data['data'][$key]['in_money'] = '****';
            }
        }

        //print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page_lof($filter) {
        //print_r($filter);
        $sql_join = "";
        $sql_main = "FROM stm_store_shift_record rl
		            INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		            INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		            INNER JOIN b2b_lof_datail r4 on r2.record_code = r4.order_code
                            INNER JOIN goods_sku r5 on r2.sku = r5.sku
		            WHERE  r2.sku = r4.sku and (r4.order_type = :order_type or r4.order_type = :order_type1 ) ";
        $sql_values = array();
        $sql_values[':order_type'] = 'shift_out';
        $sql_values[':order_type1'] = 'shift_in';
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
        $select = 'r4.id,r4.pid,r2.price,r2.rebate,r2.refer_price,r2.out_money,r2.in_money,r2.goods_code,r2.spec1_code,r2.spec2_code,r2.sku,r2.out_num,r2.in_num,r3.goods_name,r5.barcode,rl.shift_in_store_code,rl.shift_out_store_code,rl.is_sure,GROUP_CONCAT(r4.store_code) as store,GROUP_CONCAT(r4.order_type) as order_type1,GROUP_CONCAT(r4.num) as num,r4.lof_no,r4.production_date';

        //$sql_main .= "group by r4.sku";
        $sql_main .= "group by r4.sku,r4.lof_no ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        //进货价权限
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        //var_dump( $sql_main,$sql_values, $select);die;
        foreach ($data['data'] as $key => $value) {
            //$data['data'][$key]['refer_price'] = round($value['refer_price'],2);
            $sore_arr = explode(',', $value['store']);
            $num_arr = explode(',', $value['num']);
            foreach ($sore_arr as $k => $v) {
                if ($v == $value['shift_in_store_code']) {
                    $data['data'][$key]['shift_in_num'] = $num_arr[$k];
                    $data['data'][$key]['shift_in_money'] = $num_arr[$k] * $value['price'] * $value['rebate'];
                }
                if ($v == $value['shift_out_store_code']) {
                    $data['data'][$key]['shift_out_num'] = $num_arr[$k];
                    $data['data'][$key]['shift_out_money'] = $num_arr[$k] * $value['price'] * $value['rebate'];
                }
            }
            $key_arr = array('spec1_name', 'spec2_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            if ($status['status'] != 1) {
                $data['data'][$key]['price'] = '****';
                $data['data'][$key]['shift_in_money'] = '****';
                $data['data'][$key]['shift_out_money'] = '****';
                $data['data'][$key]['in_money'] = '****';
                $data['data'][$key]['out_money'] = '****';
            }
            $data['data'][$key] = array_merge($data['data'][$key], $sku_info);
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
        /*
          //如果规格1 规格2 不存在, 通过sku获取到规格1 规格2的代码和名称
          if(isset($ary_detail['sku'])&&!empty($ary_detail['sku'])){
          $info = load_model('prm/SkuModel')->get_spec_by_sku($ary_detail['sku']);
          if(!isset($info['goods_code'])){
          return $this->format_ret(-1,array(),'SKU信息不存在:'.$ary_detail['sku']);
          }
          $ary_detail['goods_id'] = $info['goods_id'];
          $ary_detail['goods_code'] = $info['goods_code'];
          $ary_detail['spec1_id'] = $info['spec1_id'];
          $ary_detail['spec1_code'] = $info['spec1_code'];
          $ary_detail['spec2_id'] = $info['spec2_id'];
          $ary_detail['spec2_code'] = $info['spec2_code'];
          }else{
          return $this->format_ret(-1,array(),'SKU信息不存在:'.$ary_detail['sku']);
          }
         */
        return parent::insert($ary_detail);
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name1, $value1, $field_name2, $value2, $select = "*") {

        $sql = "select {$select} from {$this->table} where {$field_name1} = :{$field_name1} and {$field_name2} = :{$field_name2} ";
        $data = $this->db->get_row($sql, array(":{$field_name1}" => $value1, ":{$field_name2}" => $value2));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    public function get_by_fields($field_name1, $value1, $select = "*") {

        $sql = "select {$select} from {$this->table} where {$field_name1} = :{$field_name1} ";
        $data = $this->db->get_all($sql, array(":{$field_name1}" => $value1));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
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
        /*
          if(isset($ary_detail['sku'])&&!empty($ary_detail['sku'])){
          $info = load_model('prm/SkuModel')->get_spec_by_sku($ary_detail['sku']);
          $ary_detail['goods_id'] = $info['goods_id'];
          $ary_detail['goods_code'] = $info['goods_code'];
          $ary_detail['spec1_id'] = $info['spec1_id'];
          $ary_detail['spec1_code'] = $info['spec1_code'];
          $ary_detail['spec2_id'] = $info['spec2_id'];
          $ary_detail['spec2_code'] = $info['spec2_code'];
          } */
        return parent::update($ary_detail, $where);
    }

    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id) {
        $detail = $this->get_row(array('shift_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'shift_record_id');
            if (isset($record['data']['is_sure']) && 1 == $record['data']['is_sure']) {
                return $this->format_ret(false, array(), '单据已确认!不能删除明细');
            }
        }

        $this->begin_trans();
        $res = $this->db->create_mapper('b2b_lof_datail')->delete(array('sku' => $detail['data']['sku'], 'pid' => $detail['data']['pid']));
        $result = $this->format_ret(-1, array(), '单据删除异常！');
        if ($res === FALSE) {
            $this->rollback();
            return $result;
        }

        $res = parent::delete(array('shift_record_detail_id' => $id));
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
    function delete_for_scan($id){
        $this->begin_trans();
        $result = $this->format_ret(-1, array(), '单据删除异常！');
        $res = parent::delete(array('shift_record_detail_id' => $id));
        if ($res === FALSE) {
            $this->rollback();
            return $result;
        }

        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete_lof($id) {
        $lof_data = $this->db->get_row("select * from b2b_lof_datail where id=:id", array(':id' => $id));
        if (empty($lof_data)) {
            return $this->format_ret(false, array(), '无批次信息，不能删除');
        }

        $detail = $this->get_row(array('pid' => $lof_data['pid'], 'sku' => $lof_data['sku']));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'shift_record_id');
            if (isset($record['data']['is_sure']) && 1 == $record['data']['is_sure']) {
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

        if ($lof_data['num'] == $detail['data']['out_num']) {
            //$result = parent::delete(array('stock_adjust_record_detail_id'=>$id));
            $result = parent::delete(array('shift_record_detail_id' => $detail['data']['shift_record_detail_id']));
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
     * 根据明细的record_code判断主表单据是否存在
     * @param string $value 值
     * @param string $field_name 字段名
     * @return array
     */
    private function is_exists($value, $field_name = 'record_code') {
        $m = load_model('stm/StoreShiftRecordModel');
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

    public function update_detail_action($pid, $ary_details) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'shift_record_id');
        $is_sure = $record['data']['is_sure'];
        $is_shift_out = $record['data']['is_shift_out'];
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '移仓单明细所关联的主单据不存在!');
        }
        $this->begin_trans();
        try {
            foreach ($ary_details as $ary_detail) {
                $ary_detail['pid'] = $pid;
                if ($is_sure == 0) {
                    $ary_detail['out_num'] = $ary_detail['num'];
                    $ary_detail['in_num'] = $ary_detail['in_num'];
                } else if ($is_sure == 1 && $is_shift_out == 0) {
                    $ary_detail['in_num'] = $ary_detail['num'];
                    $ary_detail['out_num'] = $ary_detail['out_num'];
                }
                $ret = $this->update($ary_detail, array(
                    'pid' => $pid, 'sku' => $ary_detail['sku']
                ));
                if (1 != $ret['status']) {
                    $this->rollback();
                    return $ret;
                }
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    /**
     * 扫描出库新增多条库存调整单明细记录
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function add_detail_action($pid, $ary_details) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'shift_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '移仓单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret(false, array(), '移仓单已验收, 不能修改明细!');
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
                $ary_detail['out_num'] = $ary_detail['num'];
                $ary_detail['out_money'] = $ary_detail['out_num'] * $ary_detail['purchase_price'] * $ary_detail['rebate'];
                //判断SKU是否已经存在
                $check = $this->is_detail_exists($pid, $ary_detail['sku']);
                if ($check) {
                    //批次表里查出该单据数量和价格
                    $pici = load_model('stm/GoodsInvLofRecordModel')->get_detail_cnt('shift_out', $pid, $ary_detail['sku']);
                    $ary_detail['out_num'] = isset($pici[0]['cnt']) ? $pici[0]['cnt'] : '';
                    $ary_detail['out_money'] = $ary_detail['out_num'] * $ary_detail['price'] * $ary_detail['rebate'];
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

    //扫描入库添加明细
    public function add_detail_action_scan($pid, $ary_details) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'shift_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '移仓单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret(false, array(), '移仓单已验收, 不能修改明细!');
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
                $ary_detail['scan_num'] = 1;
                $ary_detail['out_num'] = 0;
                $ary_detail['out_money'] = 0;
                $ary_detail['in_num'] = 0;
                $ary_detail['in_money'] = $ary_detail['in_num'] * $ary_detail['purchase_price'] * $ary_detail['rebate'];
                //判断SKU是否已经存在
                //$check = $this->is_detail_exists($pid,$ary_detail['sku']);
                //if($check){
                /*
                  //批次表里查出该单据数量和价格
                  $pici =load_model('stm/GoodsInvLofRecordModel')->get_detail_cnt('shift',$pid,$ary_detail['sku']);
                  $ary_detail['out_num'] = isset($pici[0]['cnt'])?$pici[0]['cnt']:'';
                  $ary_detail['out_money'] = $ary_detail['num'] * $ary_detail['price'];
                  //更新明细数据
                  $ret = $this->update($ary_detail,array(
                  'pid'=>$pid,'sku'=>$ary_detail['sku']
                  ));
                 */
                //}else{
                //插入明细数据
                $ret = $this->insert($ary_detail);
                //}
                if (1 != $ret['status']) {
                    return $ret;
                }
            }
            //回写数量和金额
            //$this->mainWriteBack($pid);
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    //详细单据列表
    public function get_list($pid) {
        $sql = "select r2.*,r4.barcode,r4.spec1_name,r4.spec2_name,r4.spec1_code,r4.spec2_code,r1.goods_name,r4.gb_code FROM {$this->table} r2
    			INNER JOIN base_goods r1 on r1.goods_code = r2.goods_code
    			INNER JOIN goods_sku r4 on r2.sku = r4.sku
    	        where pid = :pid ";
        $sql_values[':pid'] = $pid;
        $rs = $this->db->get_all($sql, $sql_values);
        $sql = "select shift_in_store_code from stm_store_shift_record where 
shift_record_id = :pid";
        $store_info = ctx()->db->get_row($sql, array(':pid' =>
            $pid));
        foreach ($rs as $key => $val){
//            $shelf_info = $this->db->get_all("select distinct 
//bs.shelf_name from base_shelf bs left join goods_shelf gs on 
//bs.shelf_code = gs.shelf_code where gs.store_code = :store_code and 
//gs.sku = :sku", array(':store_code' => $store_info['shift_in_store_code'], ':sku' => $val['sku']));
            $shelf_code_info = $this->db->get_all("select distinct 
                                bs.shelf_code from base_shelf bs left join goods_shelf gs on 
                                bs.shelf_code = gs.shelf_code where gs.store_code = :store_code and
                                gs.sku = :sku", array(':store_code' => $store_info['shift_in_store_code'], 
                                                      ':sku' => $val['sku']));
           
            $shelf_name = '';
            foreach ($shelf_code_info as $value) {
                $shelf_info = $this->db->get_all('select shelf_name from base_shelf where shelf_code = :shelf_code and store_code = :store_code', array(
                                                  ':shelf_code' =>  $value['shelf_code'], ':store_code' => $store_info['shift_in_store_code']
                ));
                
                foreach ($shelf_info as $val){
                    $shelf_name .= $val['shelf_name'] . ',';
                }
            }
            $shelf_name = rtrim($shelf_name, ',');
            $rs[$key]['shelf_name'] = isset($shelf_name) ? $shelf_name : '';
        }
        return $rs;
    }

    /**
     * 主单据数据回写
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function mainWriteBack($record_id) {
        //回写数量和金额
        $sql = "update stm_store_shift_record set
                  stm_store_shift_record.out_num = (select sum(out_num) from stm_store_shift_record_detail where pid = :id),
                  stm_store_shift_record.out_money = (select sum(out_money) from stm_store_shift_record_detail where pid = :id)
                where stm_store_shift_record.shift_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }

    /**
     * 回写入库数
     * @param $record_id
     * @return array
     */
    public function mainWrite_in_Back($record_id) {
        //回写数量和金额
        $sql = "update stm_store_shift_record set
                  stm_store_shift_record.in_num = (select sum(in_num) from stm_store_shift_record_detail where pid = :id),
                  stm_store_shift_record.in_money = (select sum(in_money) from stm_store_shift_record_detail where pid = :id)
                where stm_store_shift_record.shift_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }


    /**
     * 主详情单据数据回写
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function mainWriteBackDetail($record_id, $sku, $order_type = 'shift_out') {
        $sql = "update {$this->table} set
                  out_num = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type),
                  out_money = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type)*price
                where pid = :pid and sku = :sku";
        $res = $this->query($sql, array(':pid' => $record_id, ':sku' => $sku, ':order_type' => $order_type));

        return $res;
    }

    public function view_scan($record_code,$dj_type='') {
        $sql = "select goods_code,spec1_code,spec2_code,sku,in_num,out_num,scan_num from stm_store_shift_record_detail where record_code = :record_code";
        $db_mx = ctx()->db->get_all($sql, array(':record_code' => $record_code));

        $mx_data = array();
        $must_scan_mx = array();
        $must_scan_mx_zero_num = array(); //要扫描的明细，但完成单的数量为0,要去通知单取值
        $total_sl = 0;
        $total_scan_sl = 0;
        $dj_mx_map = array();
        if(empty($dj_type)){
            foreach ($db_mx as $sub_mx) {
                $total_sl += $sub_mx['out_num'];
                $total_scan_sl += $sub_mx['in_num'];
                if ($sub_mx['in_num'] > 0) {
                    $sub_mx['in_num'] = (int) $sub_mx['in_num'];
                    $sub_mx['out_num'] = (int) $sub_mx['out_num'];
                    $mx_data[] = $sub_mx;
                }
                if ($sub_mx['out_num'] > 0) {
                    $must_scan_mx[$sub_mx['sku']] = array('out_num' => (int) $sub_mx['out_num'], 'in_num' => (int) $sub_mx['in_num']);
                } else {
                    $must_scan_mx_zero_num[$sub_mx['sku']] = array('out_num' => 0, 'in_num' => $sub_mx['in_num']);
                }
                $dj_mx_map[$sub_mx['sku']] = $sub_mx;
            }
        }else{
            foreach ($db_mx as $sub_mx) {
                $total_sl += $sub_mx['out_num'];
                $total_scan_sl += $sub_mx['scan_num'];
                if ($sub_mx['scan_num'] > 0) {
                    $sub_mx['scan_num'] = (int) $sub_mx['scan_num'];
                    $sub_mx['out_num'] = (int) $sub_mx['out_num'];
                    $mx_data[] = $sub_mx;
                }
                if ($sub_mx['out_num'] > 0) {
                    $must_scan_mx[$sub_mx['sku']] = array('out_num' => (int) $sub_mx['out_num'], 'scan_num' => (int) $sub_mx['scan_num']);
                } else {
                    $must_scan_mx_zero_num[$sub_mx['sku']] = array('out_num' => 0, 'scan_num' => $sub_mx['scan_num']);
                }
                $dj_mx_map[$sub_mx['sku']] = $sub_mx;
            }
        }


        $result = array();
        $result['total_sl'] = (int) $total_sl;
        $result['total_scan_sl'] = (int) $total_scan_sl;
        $result['must_scan_mx'] = $must_scan_mx;

        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));

        $result['base_spec1_name'] = $cfg['goods_spec1'];
        $result['base_spec2_name'] = $cfg['goods_spec2'];
        //echo '<hr/>$scan_barcode_map<xmp>'.var_export($scan_barcode_map,true).'</xmp>';
        return $this->format_ret(1, $result);
    }
    /**
     *
     * 清除扫描记录
     */
    public function clean_scan($pid,$record_code,$dj_type){
        if($dj_type=='scan_out'){
            $data=array('scan_num'=>0);
        }else{
            $data=array('in_num'=>0,'in_money'=>0);
            $arr_data=array('pid'=>$pid,'order_code'=>$record_code,'order_type'=>'shift_in');
            $ret = load_model('stm/GoodsInvLofRecordModel')->delete_record_data($arr_data);
        }
        $ret=parent::update($data,array('record_code'=>$record_code));
        return $ret;

    }
    /**
     * @todo 扫描更新移仓单明细入库数
     * @date 2016-08-17
     */
    public function scan_update_detail($param, $is_api = 0) {
        $record = $param['record'];
        $sql = 'SELECT rd.*,gb.sku FROM stm_store_shift_record_detail AS rd INNER JOIN goods_barcode AS gb ON gb.sku=rd.sku WHERE rd.record_code=:record_code AND gb.barcode=:barcode';
        $detail = $this->db->get_row($sql, array(':record_code' => $param['record_code'], ':barcode' => $param['barcode']));
        if (empty($detail)) {
            return $this->format_ret(-1, array('record_code' => $param['record_code'], 'barcode' => $param['barcode']), '该单据不存在此商品条码');
        }
        if ($is_api == 1 && $param['in_num'] > $detail['out_num']) {
            return $this->format_ret(-1, '', '移入数量不能超过出库数');
        }
        $pid = $record['shift_record_id'];
        //转化成批次数据
        $pici_arr = array();
        $p = load_model('stm/GoodsInvLofRecordModel')->detail_all($pid, 'shift_out', $record['shift_out_store_code'], $detail['sku']);

        $num = $is_api == 1 ? intval($param['in_num']) : intval($detail['in_num']) + 1;
        $all_num = $num;
        $in_money = $detail['price'] * $detail['rebate'] * $all_num;
        $value1 = array(
            'pid' => $detail['pid'],
            'order_code' => $detail['record_code'],
            'goods_code' => $detail['goods_code'],
            'spec1_code' => $detail['spec1_code'],
            'spec2_code' => $detail['spec2_code'],
            'sku' => $detail['sku'],
            'store_code' => $record['shift_in_store_code'],
        );

        foreach ($p['data'] as $v) {
            if ($num >= intval($v['num'])) {
                $value1['num'] = $v['num'];
            } else {
                $value1['num'] = $num;
            }
            $value1['lof_no'] = $v['lof_no'];
            $value1['production_date'] = $v['production_date'];

            $pici_arr[] = $value1;
            $num = $num - $v['num'];
            if ($num <= 0) {
                break;
            }
        }

        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($pid, $pici_arr);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $record['shift_in_store_code'], 'shift_in', $pici_arr);
         if ($ret['status']<1) {
               return $ret;
        }
        $ret = $this->update(array('in_num' => $all_num, 'in_money' => $in_money), array('shift_record_detail_id' => $detail['shift_record_detail_id']));
        if ($is_api == 0) {
            $barcode = load_model('goods/SkuCModel')->get_barcode($detail['sku']);
            if ($ret['status'] == 1) {
                $ret['data'] = array('sku' => $detail['sku'], 'barcode' => $barcode, 'num' => $all_num);
            }
        }
        return $ret;
    }

}
