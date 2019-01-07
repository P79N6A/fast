<?php

/**
 * 采购入库单相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');

class PurchaseReturnRecordDetailModel extends TbModel {

   

    function get_table() {
        return 'fx_purchaser_return_record_detail';
    }
    
   /**
     * 新增多条经销退货明细记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-10-28
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function add_detail_action($return_record_code, $ary_details, $store_code = '') {
        //判断主单据的pid是否存在
        $record = $this->is_exists($return_record_code, 'return_record_code');
        $pid = $record['data']['fx_purchaser_return_id'];
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '经销采购退货单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_check'] == 1) {
            return $this->format_ret(false, array(), '经销采购退货单已确认, 不能修改明细!');
        }
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        $lof_status = isset($ret_arr['lof_status']) ? $ret_arr['lof_status'] : '';
        $new_details = array();
        $sql = "select * from b2b_lof_datail where order_code = :order_code and order_type = 'fx_return'";
        $details_lof = $this->db->get_all($sql, array(":order_code" => $return_record_code));
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
                //取出分销结算单价
                $arr['price'] = load_model('fx/GoodsManageModel')->compute_fx_price($record['data']['custom_code'],$d, $record['data']['order_time']);
                
                $arr['pid'] = $pid;
                $arr['record_code'] = $return_record_code;
                $arr['money'] = $arr['price'] * $new_details[$key]['num'];
                $arr['cost_price'] = $arr['price'];
                $arr['barcode'] = oms_tb_val('goods_sku', 'barcode', array('sku'=>$d['sku']));
                $arr['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code'=>$d['goods_code']));
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
     * 根据明细的record_code判断主表单据是否存在
     * @param string $value 值
     * @param string $field_name 字段名
     * @return array 
     */
    private function is_exists($value, $field_name = 'return_record_code') {

        $m = load_model('fx/PurchaseReturnRecordModel');
        $ret = $m->get_row(array($field_name => $value));
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
        $sql = "update fx_purchaser_return_record set
                  fx_purchaser_return_record.num = (select sum(num) from fx_purchaser_return_record_detail where pid = :id),
                  fx_purchaser_return_record.sum_money = (select sum(money) from fx_purchaser_return_record_detail where pid = :id)
                where fx_purchaser_return_record.fx_purchaser_return_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }
    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";

        $sql_main = "FROM fx_purchaser_return_record rl  
		            INNER JOIN {$this->table} r2 on rl.return_record_code = r2.record_code 
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
        $sql_main = "FROM fx_purchaser_return_record rl
		INNER JOIN {$this->table} r2 on rl.return_record_code = r2.record_code
		INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		INNER JOIN b2b_lof_datail r4 on r2.record_code = r4.order_code
		LEFT JOIN goods_sku r5 on r2.sku = r5.sku
		WHERE  r2.sku = r4.sku and r4.order_type = :order_type  ";

        $sql_values = array();
        $sql_values[':order_type'] = 'fx_return';

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
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['money'] = number_format($value['money'], 3);
            $data['data'][$key]['num_differ'] = $value['lof_num'] - $value['fill_num'];
            $name_arr = load_model('prm/Spec2Model')->get_by_field('spec2_code', $value['spec2_code'], 'spec2_name');
            $data['data'][$key]['spec2_name'] = isset($name_arr['data']['spec2_name']) ? $name_arr['data']['spec2_name'] : '';
            $name_arr = load_model('prm/Spec1Model')->get_by_field('spec1_code', $value['spec1_code'], 'spec1_name');
            $data['data'][$key]['spec1_name'] = isset($name_arr['data']['spec1_name']) ? $name_arr['data']['spec1_name'] : '';
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
        /**
     * 主单据数据回写
     */
    public function mainWriteBackfinish($return_notice_code,$type = '') {
        $date = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM wbm_return_notice_record WHERE return_notice_code = '{$return_notice_code}';";
        $notice_record = $this->db->get_row($sql);
        //查询主表信息
        $sql = "SELECT fx_purchaser_return_id,num,finish_num FROM fx_purchaser_return_record WHERE return_record_code = '{$notice_record['jx_return_code']}';";
        $record = $this->db->get_row($sql);
        $is_store_status = $record['num'] > $notice_record['finish_num'] && $notice_record['finish_num'] > 0 ? 2 : 1;
        $this->begin_trans();
        //回写主单数量、状态、入库时间
        $sql = "UPDATE fx_purchaser_return_record fx SET "
                . " fx.finish_num = ( "
                . " SELECT sum(fxd.finish_num) FROM fx_purchaser_return_record_detail fxd WHERE fxd.record_code = '{$notice_record['jx_return_code']}'),fx.is_settlement = 1 , fx.is_store_in = {$is_store_status} , fx.is_store_in_time = '{$date}' , fx.sum_money = (SELECT sum(ff.money) FROM(SELECT finish_num*price AS money FROM fx_purchaser_return_record_detail WHERE record_code = '{$notice_record['jx_return_code']}') ff ) "
                . " WHERE return_record_code = '{$notice_record['jx_return_code']}' ";
        $ret = $this->query($sql);
        if($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        //回写明细表的金额
        $sql = "UPDATE fx_purchaser_return_record_detail SET money = price * finish_num WHERE record_code = '{$notice_record['jx_return_code']}'";
        $ret = $this->query($sql);
        if($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        
        //回写单据批次表
        $sql = "UPDATE b2b_lof_datail lof,(SELECT fx.sku,fx.record_code,fx.finish_num FROM fx_purchaser_return_record_detail fx WHERE fx.record_code = '{$notice_record['jx_return_code']}') fxd SET lof.fill_num = fxd.finish_num WHERE lof.sku = fxd.sku AND lof.order_code = fxd.record_code AND lof.order_type = 'fx_return';";
        $ret1 = $this->query($sql);
        if($ret1['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret1;
        }
        
        //添加日志
        $store_status = $is_store_status == 2 ? '部分入库' : '已入库';
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '已确认', 'finish_status' => $store_status, 'action_name' => '验收入库', 'module' => "fx_purchase_return", 'pid' => $record['fx_purchaser_return_id'],'action_note'=>'入库');        
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        
        //单据结算
        $ret1 = load_model('fx/PurchaseReturnRecordModel')->do_settlement($record['fx_purchaser_return_id'],$type);
        if($ret1['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret1;
        }
        
        $this->commit();
        return $ret;
    }
    //删除批次
    function delete_lof($id) {
        $lof_data = $this->db->get_row("select * from b2b_lof_datail where id=:id", array(':id' => $id));
        if (empty($lof_data)) {
            return $this->format_ret(false, array(), '批次明细信息不存在!不能删除明细');
        }
        $detail = $this->get_row(array('pid' => $lof_data['pid'], 'sku' => $lof_data['sku']));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'fx_purchaser_return_id');
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
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未入库', 'action_name' => '删除批次明细', 'module' => "fx_purchase_return", 'pid' => $lof_data['pid']);
        load_model('pur/PurStmLogModel')->insert($log);
        $this->commit();
        return $this->format_ret(1);
    }
    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id) {
        $detail = $this->get_row(array('return_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'fx_purchaser_return_id');
            if (isset($record['data']['is_check']) && 1 == $record['data']['is_check']) {
                return $this->format_ret(false, array(), '单据已确认!不能删除明细');
            }
        }
        $result = parent::delete(array('return_record_detail_id' => $id));
        $this->mainWriteBack($detail['data']['pid']);
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未入库', 'action_name' => '删除明细', 'module' => "fx_purchase_return", 'pid' => $detail['data']['pid']);
        load_model('pur/PurStmLogModel')->insert($log);
        return $result;
    }

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
            $sql = "delete from b2b_lof_datail where order_type='fx_return' and order_code='{$record_code}' and sku='{$sku}'";
            $this->db->query($sql);
            return $this->format_ret(1);
        } else {
            $sku_arr = array('goods_code', 'spec1_code', 'spec2_code', 'sku');
            $detail_lof = load_model('goods/SkuCModel')->get_sku_info($sku, $sku_arr);
            $ret = load_model('fx/PurchaseReturnRecordModel')->get_by_id($pid);
            $record_data = $ret['data'];
            $detail_lof['num'] = $num;
            $detail_lof['init_num'] = $num;

            return load_model('stm/GoodsInvLofRecordModel')->add_detail_action($record_data['fx_purchaser_return_id'], $record_data['store_code'], 'fx_return', array($detail_lof));
        }
    }
    
    function get_detail_by_code($code,$select = "*") {
        $sql = "SELECT {$select} FROM fx_purchaser_return_record_detail WHERE record_code = :record_code";
        $ret = $this->db->get_all($sql,array(':record_code' => $code));
        return $ret;
    }

}
