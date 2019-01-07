<?php

/**
 * 库存调整详情管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
//require_lang('stm');
require_lang('prm');

class StmStockAdjustRecordDetailModel extends TbModel {

    function get_table() {
        return 'stm_stock_adjust_record_detail';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('cost_price');
        //print_r($filter);
        $sql_join = "";
        $sql_main = "FROM stm_stock_adjust_record rl  
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
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name or r4.barcode LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        $select = 'r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,rl.is_check_and_accept';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => $value) {
            if ($status['status'] != 1) {
                $cost_price = '****';
            }else{
                $cost_price = round($value['cost_price'], 2);
            }
            $data['data'][$key]['refer_price'] = round($value['refer_price'], 2);
            $data['data'][$key]['price'] = round($value['price'], 2);
            $data['data'][$key]['cost_price'] = $cost_price;
            $data['data'][$key]['rebate'] = round($value['rebate'], 2);
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = number_format($data['data'][$key]['price1'], 3,'.','');
            $value['money'] = $data['data'][$key]['price1'] * $value['num'];
            $data['data'][$key]['money'] = number_format($value['money'], 3);
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page_lof($filter) {
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('cost_price');
        //print_r($filter);die;
        $sql_join = "";
        $sql_main = "FROM stm_stock_adjust_record rl  
		            INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code 
		            INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		            INNER JOIN b2b_lof_datail r4 on r2.record_code = r4.order_code 
                            INNER JOIN goods_sku r5 on r2.sku = r5.sku
		            WHERE  r2.sku = r4.sku and r4.order_type = :order_type ";
        $sql_values = array();
        $sql_values[':order_type'] = 'adjust';
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
        $select = 'r4.id,r4.pid,r2.price,r2.rebate,r2.refer_price,r2.money,r2.goods_code,r2.spec1_code,r2.spec2_code,r2.sku,r3.goods_name,r5.barcode,rl.is_check_and_accept,r4.num,r4.lof_no,r4.production_date';

        //$sql_main .= "group by r2.sku";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //var_dump( $sql_main,$sql_values, $select);die;
        foreach ($data['data'] as $key => $value) {
            if ($status['status'] != 1) {
                $cost_price = '****';
            }else{
                $cost_price = round($value['cost_price'], 2);
            }
            $key_arr = array('spec1_name', 'spec2_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $data['data'][$key]['refer_price'] = round($value['refer_price'], 3);
            $data['data'][$key]['price'] = round($value['price'], 3);
            $data['data'][$key]['rebate'] = round($value['rebate'], 3);
            //$data['data'][$key]['money'] = round($value['money'],2);

            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rebate'];
            $data['data'][$key]['price1'] = number_format($data['data'][$key]['price1'], 3);
            $value['money'] = $data['data'][$key]['price1'] * $value['num'];
            $data['data'][$key]['money'] = number_format($value['money'], 3);


            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);
        }
        //print_r($data);
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
        $detail = $this->get_row(array('stock_adjust_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'stock_adjust_record_id');
            if (isset($record['data']['is_check_and_accept']) && 1 == $record['data']['is_check_and_accept']) {
                return $this->format_ret(false, array(), '单据已验收!不能删除明细');
            }
        }
//         $this->begin_trans();
//        $res = $this -> db -> create_mapper('b2b_lof_datail') -> delete(array('sku'=>$detail['data']['sku'],'pid'=>$detail['data']['pid'])); 
//        $result = $this->format_ret(-1,array(),'单据删除异常！');
//       if($res===FALSE){
//            $this->rollback();
//            return $result;
//        }

        $result = parent::delete(array('stock_adjust_record_detail_id' => $id));
//        if($res===FALSE){
//            $this->rollback();
//            return $result;
//        }
        $this->mainWriteBack($detail['data']['pid']);
//       if($res===FALSE){
//            $this->rollback();
//            return $result;
//        }
//        $this->commit();
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
            return $this->format_ret(false, array(), '单据已验收!不能删除明细');
        }

        $detail = $this->get_row(array('pid' => $lof_data['pid'], 'sku' => $lof_data['sku']));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'stock_adjust_record_id');
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
            //$result = parent::delete(array('stock_adjust_record_detail_id'=>$id));
            $result = parent::delete(array('stock_adjust_record_detail_id' => $detail['data']['stock_adjust_record_detail_id']));
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
        $m = load_model('stm/StockAdjustRecordModel');
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
        $record = $this->is_exists($pid, 'stock_adjust_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '调整单明细所关联的主单据不存在!');
        }
        $new_ary_details = array();
        foreach ($ary_details as $ary_detail) {
            if (isset($new_ary_details[$ary_detail['sku']])) {
                $new_ary_details[$ary_detail['sku']]['num'] += $ary_detail['num'];
            } else {
                $new_ary_details[$ary_detail['sku']] = $ary_detail;
            }
        }
        $ary_details = $new_ary_details;
        //判断主单据状态
        if ($record['data']['is_check_and_accept'] == 1) {
            return $this->format_ret(false, array(), '调整单已验收, 不能修改明细!');
        }
        $this->begin_trans();
        try {
            foreach ($ary_details as $ary_detail) {
 
                
                $ary_detail['pid'] = $pid;
                $ary_detail['record_code'] = $record['data']['record_code'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
                
                //成本价的获取
                $key_arr = array('cost_price','price');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($ary_detail['sku'], $key_arr);
                $cost_price = $sku_info['cost_price'];
                $sql = "select a.* from base_goods a where a.goods_code = :code";
                $goods = $this->db->get_row($sql, array('code' => $ary_detail['goods_code']));
                $price = $sku_info['price'];
                if ($cost_price <= 0 || empty($cost_price)) {
                    $cost_price = $goods['cost_price'];
                }
                if ($price <= 0 || empty($price)) {
                    $price = $goods['sell_price'];
                }
                $ary_detail['cost_price'] = $cost_price;
                $ary_detail['price'] = $price;
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'];
                // $ary_detail['spec1_id'] = $ary_detail['spec1_id'];
                //$ary_detail['spec1_code'] = $ary_detail['spec1_code'];
                // $ary_detail['spec2_id'] = $ary_detail['spec2_id'];
                // $ary_detail['spec2_code'] = $ary_detail['spec2_code'];
                //判断SKU是否已经存在

                $check = $this->is_detail_exists($pid, $ary_detail['sku']);
                if ($check) {
                    //批次表里查出该单据数量和价格
                    $pici = load_model('stm/GoodsInvLofRecordModel')->get_detail_cnt('adjust', $pid, $ary_detail['sku']);
                    $ary_detail['num'] = isset($pici[0]['cnt']) ? $pici[0]['cnt'] : '';
                    $ary_detail['rebate'] = empty($ary_detail['rebate']) ? 1 : $ary_detail['rebate'];
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
            return $ret;
            //return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    /**
     * 主详情单据数据回写
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function mainWriteBackDetail($record_id, $sku, $order_type = 'adjust') {
        $sql = "update {$this->table} set
                  num = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type),
                  money = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku and order_type = :order_type)*price
                where pid = :pid and sku = :sku";
        $res = $this->query($sql, array(':pid' => $record_id, ':sku' => $sku, ':order_type' => $order_type));

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
        $sql = "update stm_stock_adjust_record set
                  stm_stock_adjust_record.num = (select sum(num) from stm_stock_adjust_record_detail where pid = :id),
                  stm_stock_adjust_record.money = (select sum(money) from stm_stock_adjust_record_detail where pid = :id)
                where stm_stock_adjust_record.stock_adjust_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }

}
