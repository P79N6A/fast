<?php

require_model('tb/TbModel');
require_lib('privilege_util', true);
require_lang('stm');
require_model('stm/StockAdjustRecordModel');

class TakeStockRecordModel extends TbModel {

    var $wms_no_control = array('hwwms', 'ydwms', 'qimen');

    function get_table() {
        return 'stm_take_stock_record';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl
                    LEFT JOIN stm_take_stock_record_detail r2 on rl.record_code = r2.record_code
                    LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code
                     LEFT JOIN goods_sku r4 on r4.sku = r2.sku
                    WHERE 1";


        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        // $sql_main = "FROM {$this->table} tl WHERE 1";
        //商品编号
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND (r3.goods_name LIKE :goods_name or r3.goods_code like :goods_code )";
            $sql_values[':goods_name'] = $filter['goods_name'] . '%';
            $sql_values[':goods_code'] = $filter['goods_name'] . '%';
        }
        // 商品条形码
        if (isset($filter['barcord']) && $filter['barcord'] != '') {
            $sql_main .= " AND (r4.barcode LIKE :barcode )";
            $sql_values[':barcode'] = $filter['barcord'] . '%';
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        //盘点仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $sql_main .= " AND (rl.store_code = :store_code )";
            $sql_values[':store_code'] = $filter['store_code'];
        }
        //验收
        if (isset($filter['is_sure']) && $filter['is_sure'] != '') {
            $sql_main .= " AND (rl.is_sure = :is_sure )";
            $sql_values[':is_sure'] = $filter['is_sure'];
        }
        //盘点
        if (isset($filter['is_pre_profit_and_loss']) && $filter['is_pre_profit_and_loss'] != '') {
            $sql_main .= " AND (rl.is_pre_profit_and_loss = :is_pre_profit_and_loss )";
            $sql_values[':is_pre_profit_and_loss'] = $filter['is_pre_profit_and_loss'];
        }
        //盘点日期
        if (isset($filter['bill_time_start']) && $filter['bill_time_start'] != '') {
            $sql_main .= " AND (rl.take_stock_time >= :bill_time_start )";
            $sql_values[':bill_time_start'] = $filter['bill_time_start'];
        }
        if (isset($filter['bill_time_end']) && $filter['bill_time_end'] != '') {
            $sql_main .= " AND (rl.take_stock_time <= :bill_time_end )";
            $sql_values[':bill_time_end'] = $filter['bill_time_end'];
        }
        if (isset($filter['add_person']) && $filter['add_person'] != '') {
            $sql_main .= " AND (rl.is_add_person = :add_person )";
            $sql_values[':add_person'] = trim($filter['add_person']);
        }

        $select = 'rl.*';
        //$sql_main .= " order by take_stock_time desc" ;
        $sql_main .= " GROUP BY rl.record_code ORDER BY is_add_time DESC, take_stock_time DESC";
        // echo $sql_main;
        //$sql_main = get_privilege_sql($sql_main,array("shop"=>"t1"));
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $store_data = load_model('base/StoreModel')->get_by_code($value['store_code']);
            $value['store_name'] = $store_data['data']['store_name'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_detail_by_page($filter) {
        $sql_values = array();
        /*
          $sql_main = "FROM stm_take_stock_record_detail tl
          WHERE pid = :pid";
         */
        $sql_main = "FROM stm_take_stock_record rl
        INNER JOIN stm_take_stock_record_detail r2 on rl.record_code = r2.record_code
        INNER JOIN goods_sku r4 on r4.sku = r2.sku
        WHERE  1=1  ";
        $select = 'r2.*,r4.barcode,rl.is_sure';
        if ($filter['ctl_type'] == 'export') {
            $select .= ",rl.is_pre_profit_and_loss,rl.is_stop,rl.take_stock_time,rl.store_code,r4.goods_code AS gs_goods_code,r4.spec1_code AS gs_spec1_code,r4.spec1_name AS gs_spec1_name,r4.spec2_code AS gs_spec2_code,r4.spec2_name AS gs_spec2_name";
        }
        $sql_values[':pid'] = $filter['pid'];
        if (isset($filter['pid']) && $filter['pid'] != '') {
            $sql_main .= " AND (r2.pid = :pid )";
            $sql_values[':pid'] = $filter['pid'];
        }
        //商品货号
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r4.barcode LIKE :code_name)";

            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }

        //$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        if ($filter['ctl_type'] == 'export') {
            foreach ($data['data'] as &$value) {
                $value['is_sure_name'] = ($data['is_sure'] == 1) ? "已确认" : "未确认";
                $value['is_stop_name'] = ($data['is_stop'] == 1) ? "已终止" : "未终止";
                $value['is_pre_profit_and_loss_name'] = ($data['is_pre_profit_and_loss'] == 1) ? "已盘点" : "未盘点";
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], array('goods_name',));
                $value['goods_name'] = $sku_info['goods_name'];
            }
            filter_fk_name($data['data'], array('store_code|store'));
        }

        //print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_detail_page_lof($filter) {
        $sql_values = array();
        $sql_main = "FROM stm_take_stock_record rl
        			INNER JOIN b2b_lof_datail r2 on rl.take_stock_record_id = r2.pid
        			INNER JOIN goods_sku r4 on r4.sku = r2.sku
        			WHERE order_type = 'take_stock' and pid = :pid";
        $select = 'r2.*,r4.barcode,rl.is_sure';

        if ($filter['ctl_type'] == 'export') {
            $select .= ",rl.is_pre_profit_and_loss,rl.is_stop,rl.take_stock_time,r4.goods_code AS gs_goods_code,r4.spec1_code AS gs_spec1_code,r4.spec1_name AS gs_spec1_name,r4.spec2_code AS gs_spec2_code,r4.spec2_name AS gs_spec2_name,rl.record_code";
        }

        $sql_values[':pid'] = $filter['pid'];
        //$sql_values[':order_type'] = 'pur_return_notice';
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r4.barcode LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        // echo $sql_main;
        //  print_r($sql_values);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        if ($filter['ctl_type'] == 'export') {
            foreach ($data['data'] as &$value) {
                $value['is_sure_name'] = ($data['is_sure'] == 1) ? "已确认" : "未确认";
                $value['is_stop_name'] = ($data['is_stop'] == 1) ? "已终止" : "未终止";
                $value['is_pre_profit_and_loss_name'] = ($data['is_pre_profit_and_loss'] == 1) ? "已盘点" : "未盘点";
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], array('goods_name',));
                $value['goods_name'] = $sku_info['goods_name'];
            }

            filter_fk_name($data['data'], array('store_code|store'));
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        // print_r($ret_data);
        return $this->format_ret($ret_status, $ret_data);
    }

    function insert($data) {
        if (!isset($data['record_code']) || !valid_input($data['record_code'], 'required')) {
            return $this->format_ret(-1, "", "RECORD_ERROR_CODE");
        }

        $ret = $this->is_exists($data['record_code']);

        if (!empty($ret['data']))
            return $this->format_ret('-1', '', 'code已存在');
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        return parent::insert($data);
    }

    function is_exists($value, $field_name = 'record_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    function get_take_stock_by_id($id) {
        $db = $GLOBALS['context']->db;
        $sql = "select * from stm_take_stock_record where take_stock_record_id = :id";
        return $db->get_row($sql, array(':id' => $id));
    }

    function update_by_id($id, $condition, $data) {
        $sql = "select * from stm_take_stock_record_detail where  pid = :pid  ";
        $arr = array(':pid' => $id);
        $details = $this->db->get_all($sql, $arr);
        //print_r($details);exit;
        //检查明细是否为空
        if (empty($details)) {
            return $this->format_ret('-1', '', '明细为空，不能确认');
        }
        // return parent::update($data, array("take_stock_record_id"=>$id));
        return parent::update($data, $condition);
    }

    //修改单据
    function edit_action($id, $data) {
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        if (!isset($id)) {
            return $this->format_ret(false, array(), '修改时缺少主单据ID!');
        }
        return parent::update($data, array("take_stock_record_id" => $id));
    }

    public function add_detail_action($pid, $ary_details, $type = '') {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'take_stock_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '盘点单明细所关联的主单据不存在!');
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
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret(false, array(), '盘点单已验收, 不能修改明细!');
        }
        $this->begin_trans();
        try {
            foreach ($ary_details as &$ary_detail) {
                //数量为0 可以导入
                if ((!isset($ary_detail['num']) || $ary_detail['num'] == 0) && $type != 'take_stock') {
                    unset($ary_detail);
                    continue;
                }
                $ary_detail['pid'] = $pid;
                $ary_detail['record_code'] = $record['data']['record_code'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
//                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['sell_price'] * $ary_detail['rebate'];
//                $ary_detail['price'] = $ary_detail['sell_price'];
                // $ary_detail['spec1_id'] = $ary_detail['spec1_id'];
                //$ary_detail['spec1_code'] = $ary_detail['spec1_code'];
                // $ary_detail['spec2_id'] = $ary_detail['spec2_id'];
                // $ary_detail['spec2_code'] = $ary_detail['spec2_code'];
                //判断SKU是否已经存在
//                $check = $this->is_detail_exists($pid, $ary_detail['sku']);
//                if ($check) {
//                    //批次表里查出该单据数量和价格
//                    $pici = load_model('stm/GoodsInvLofRecordModel')->get_detail_cnt('take_stock', $pid, $ary_detail['sku']);
//                    $ary_detail['num'] = isset($pici[0]['cnt']) ? $pici[0]['cnt'] : '';
////                    $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'] * $ary_detail['rebate'];
//                    //更新明细数据
//                    $ret = $this->update_exp("stm_take_stock_record_detail", $ary_detail, array(
//                        'pid' => $pid, 'sku' => $ary_detail['sku']
//                    ));
//                    if (1 != $ret['status']) {
//                        return $ret;
//                    }
//                    unset($ary_detail);
//                }
                /* else {
                  //插入明细数据
                  $ret = $this->insert_exp("stm_take_stock_record_detail", $ary_detail);
                  } */
            }
            $result_data_arr = array_chunk($ary_details, 2000);
            $str_updata = 'num=VALUES(num),money=VALUES(money)';
            foreach ($result_data_arr as $v) {
                $ret = $this->insert_multi_duplicate('stm_take_stock_record_detail', $v, $str_updata);
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

    function is_detail_exists($pid, $sku) {
        $db = $GLOBALS['context']->db;
        $sql = "select * from stm_take_stock_record_detail where pid = :pid and sku = :sku";
        return $db->get_row($sql, array(":pid" => $pid, ":sku" => $sku));
    }

    function get_detail_by_detail_id($id) {
        $db = $GLOBALS['context']->db;
        $sql = "select * from stm_take_stock_record_detail where take_stock_record_detail_id = :id";
        return $db->get_row($sql, array(":id" => $id));
    }

    function delete_record($id) {
        $record = $this->get_take_stock_by_id($id);
        if (!$record) {
            return $this->format_ret('-1', array(), '单据不存在！');
        }
        if ($record['is_sure'] == 1) {
            return $this->format_ret('-1', array(), '单据已经验收，不能删除！');
        }

        $ret = $this->delete_exp("stm_take_stock_record", array("take_stock_record_id" => $id));
        $ret = $this->delete_exp("stm_take_stock_record_detail", array("pid" => $id));
        $ret = $this->delete_exp("b2b_lof_datail", array("pid" => $id, "order_type" => "take_stock"));

        return $this->format_ret(1, array(), '删除成功！');
    }

    //删除明细
    function delete_detail($id, $pid) {

        $detail = $this->get_detail_by_detail_id($id);

        $this->begin_trans();
        $res = $this->db->create_mapper('b2b_lof_datail')->delete(array('sku' => $detail['sku'], 'order_type' => 'take_stock', 'pid' => $detail['pid']));
        $result = $this->format_ret(-1, array(), '单据删除异常！');
        if ($res === FALSE) {
            $this->rollback();
            return $result;
        }
        $result = $this->delete_exp("stm_take_stock_record_detail", array("take_stock_record_detail_id" => $id));
        if ($res === FALSE) {
            $this->rollback();
            return $result;
        }

        $this->mainWriteBack($detail['pid']);
        if ($res === FALSE) {
            $this->rollback();
            return $result;
        }
        $this->commit();
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未确认', 'action_name' => '删除明细', 'module' => "take_stock_record", 'pid' => $pid);
        load_model('pur/PurStmLogModel')->insert($log);
        return $this->format_ret(1);
    }

    function delete_detail_lof($id, $pid) {
        $lof_data = $this->db->get_row("select * from b2b_lof_datail where id=:id", array(':id' => $id));
        if (empty($lof_data)) {
            return $this->format_ret(false, array(), '单据已验收!不能删除明细');
        }

        $detail = $this->is_detail_exists($lof_data['pid'], $lof_data['sku']);

        $result = load_model('stm/GoodsInvLofRecordModel')->delete_lof($id);
        if ($result['status'] < 1) {
            return $result;
        }

        // load_model('stm/GoodsInvLofRecordModel')->delete_lof($id);
        if ($lof_data['num'] == $detail['num']) {
            $result = $this->delete_exp("stm_take_stock_record_detail", array("take_stock_record_detail_id" => $detail['take_stock_record_detail_id']));
            $res = ($result['status'] < 1) ? FALSE : TRUE;
        } else {
            $res = $this->mainWriteBackDetail($lof_data['pid'], $lof_data['sku']);
        }

        $res = $this->mainWriteBack($detail['pid']);

        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未确认', 'action_name' => '删除明细', 'module' => "take_stock_record", 'pid' => $pid);
        load_model('pur/PurStmLogModel')->insert($log);
        return $this->format_ret(1, array(), '删除成功');
    }

    public function mainWriteBack($record_id) {
        //回写数量和金额
        $sql = "update stm_take_stock_record set
                  stm_take_stock_record.num = (select sum(num) from stm_take_stock_record_detail where pid = :id),
                  stm_take_stock_record.money = (select sum(money) from stm_take_stock_record_detail where pid = :id)
                where stm_take_stock_record.take_stock_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }

    public function mainWriteBackDetail($record_id, $sku) {
        $sql = "update stm_take_stock_record_detail set
                  num = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku),
                  money = (select sum(num) from b2b_lof_datail where pid = :pid and sku = :sku)*price
                where pid = :pid and sku = :sku";
        $res = $this->query($sql, array(':pid' => $record_id, ':sku' => $sku));

        return $res;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $param
     */
    function get_unconfirmed_record($id) {
        $ret = array();
        $db = $GLOBALS['context']->db;
        $sql = "select * from stm_take_stock_record where take_stock_record_id = :take_stock_record_id";
        $take_stock_record = $db->get_row($sql, array(":take_stock_record_id" => $id));
        $time = $take_stock_record['take_stock_time'];
        $store_code = $take_stock_record['store_code'];
        //采购入库单
        $sql = "select * from pur_purchaser_record where is_check_and_accept = 0 and store_code = :store_code and record_time <= :time";
        $ret['pur_purchaser'] = $db->get_all($sql, array(":time" => $time, ":store_code" => $store_code));

        //采购退货单
        $sql = "select * from pur_return_record where is_store_out = 0 and store_code = :store_code and record_time <= :time";
        $ret['pur_return'] = $db->get_all($sql, array(":time" => $time, ":store_code" => $store_code));

        //调整单
        $sql = "select * from stm_stock_adjust_record where is_check_and_accept = 0 and store_code = :store_code  and record_time <= :time";
        $ret['stm_adjust'] = $db->get_all($sql, array(":time" => $time, ":store_code" => $store_code));

        //批发销货单
        $sql = "select * from wbm_store_out_record where is_store_out = 0 and store_code = :store_code  and record_time <= :time";
        $ret['store_out'] = $db->get_all($sql, array(":time" => $time, ":store_code" => $store_code));
        //批发退货单
        $sql = "select * from wbm_return_record where is_store_in = 0 and store_code = :store_code  and record_time <= :time";
        $ret['wbm_return'] = $db->get_all($sql, array(":time" => $time, ":store_code" => $store_code));
        //移仓单
        $sql = "select * from stm_store_shift_record where is_shift_out = 0 and shift_out_store_code = :store_code  and record_time <= :time";
        $ret['stm_store_shift'] = $db->get_all($sql, array(":time" => $time, ":store_code" => $store_code));
        return $ret;
    }

    function deal_pre_profit($id) {
        $db = $GLOBALS['context']->db;
        $mdl_inv = new InvModel();
        $sql = "select * from stm_take_stock_record where take_stock_record_id = :take_stock_record_id";
        $take_stock_record = $db->get_row($sql, array(":take_stock_record_id" => $id));

        $sql = "select * from stm_take_stock_record_detail where pid = :pid";
        $detail_list = $db->get_all($sql, array(":pid" => $take_stock_record['take_stock_record_id']));

        foreach ($detail_list as &$detail) {
            $sql = "select barcode from goods_sku where sku = :sku";
            $barcode = $db->get_value($sql, array(":sku" => $detail['sku']));
            $detail['barcode'] = $barcode;
            $inv = $mdl_inv->get_sku_inv(array("store_code" => $take_stock_record['store_code'], "sku" => $detail['sku']));
            $detail['stock_num'] = isset($inv['data']['data'][0]['stock_num']) ? $inv['data']['data'][0]['stock_num'] : 0;
        }
        return $detail_list;
    }

    function get_take_stock_info($store_date) {
        list($take_stock_time, $store_code) = explode(",", $store_date);
        $sql = "select * from  stm_take_stock_record where is_sure=1 and is_stop=0 AND is_pre_profit_and_loss=0 AND (take_stock_status=0 OR take_stock_status=9 )"
                . " AND store_code=:store_code AND take_stock_time=:take_stock_time ";
        $sql_value = array(':store_code' => $store_code, ':take_stock_time' => $take_stock_time);

        $data = $this->db->get_all($sql, $sql_value);
        $ret_data['goods_num'] = 0;
        if (!empty($data)) {
            foreach ($data as $val) {

                $ret_data['record_list'][] = $val['record_code'];
                $ret_data['num_list'][$val['record_code']] = $val['num'];
                $ret_data['goods_num'] += $val['num'];
            }
            $ret_store = load_model('base/StoreModel')->get_by_code($store_code);
            $ret_data['store_name'] = $ret_store['data']['store_name'];
            $ret_data['store_code'] = $store_code;
            $ret_data['take_stock_time'] = $take_stock_time;
            $ret = $this->format_ret(1, $ret_data);
        } else {
            $ret = $this->format_ret(-1, array(), '找不到可用盘点单据');
        }
        return $ret;
    }

    function get_take_stock($param) {
        $db = $GLOBALS['context']->db;
        $sql = "select DISTINCT store_code,take_stock_time  from stm_take_stock_record where 1=1";
        foreach ($param as $k => $v) {
            $sql .= " and " . $k . " = " . $v;
        }
        return $db->get_all($sql);
    }

    function take_stock_inv($param) {

        //$recode_code_list,$type=1

        $type = $param['type'];
        list($take_stock_time, $store_code) = explode(",", $param['store_date']);

        $recode_code_arr = explode(",", $param['recode_code_list']);
        $where = " record_code in ('" . implode("','", $recode_code_arr) . "' )";
        $sql = "select * from  stm_take_stock_record where  " . $where;

        $recode_data = $this->db->get_all($sql);
        $msg = '';
        $id_arr = array();
        foreach ($recode_data as $val) {
            $val_msg_arr = array();
            if ($val['is_sure'] == 0) {
                $val_msg_arr[] = '未确认';
            }
            if ($val['is_stop'] == 1) {
                $val_msg_arr[] = '已经终止';
            }
            if ($val['take_stock_status'] > 0 && $val['take_stock_status'] < 9) {
                $val_msg_arr[] = '正在盘点';
            }
            if ($val['is_pre_profit_and_loss'] == 1) {
                $val_msg_arr[] = '已盘点';
            }
            if (!empty($val_msg_arr)) {
                $msg .= "单据： " . $val['record_code'];
            }
            $id_arr[] = $val['take_stock_record_id'];
        }

        if ($msg != '') {
            return $this->format_ret(-1, array(), $msg);
        }
        $ids = implode(',', $id_arr);

//        $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($store_code);
//        if ($wms_system_code !== FALSE && !in_array(strtolower($wms_system_code), $this->wms_no_control)) {
//            return $this->format_ret(-1, array(), '对接外部仓储，不能库存维护!');
//        }
        $wms_effect_inv = load_model('sys/ShopStoreModel')->get_no_effect_inv($store_code);
        if (!empty($wms_effect_inv)) {
            return $this->format_ret(-1, array(), '对接外部仓储，不能库存维护!');
        }

        require_model('common/TaskModel');
        $task = new TaskModel();

        $task_data['code'] = 'inv_maintain' . $store_code;
        $task_data['is_auto'] = 0;
        $request['store_code'] = $store_code;
        $request['app_act'] = 'prm/inv/inv_maintain';
        $request['app_fmt'] = 'json';
        $task_data['start_time'] = time();
        $task_data['request'] = $request;
        $ret = $task->save_task($task_data);
        if ($ret['status'] < 0) {
            if ($ret['status'] == -5) {
                $ret['message'] = "库存维护正在进行，暂时不能进行盘点";
            }
            return $ret;
        }
        $task_id = $ret['data'];
        $task_data = array();
        $task_data['code'] = 'set_take_stock_inv';
        $task_data['is_auto'] = 0;
        $task_data['start_time'] = time();
        $s_request['status'] = 2;
        $s_request['type'] = $type;
        $s_request['ids'] = $ids;
        $s_request['app_act'] = 'stm/take_stock_record/set_take_stock_inv';
        $s_request['app_fmt'] = 'json';
        $s_request['user_id'] = CTX()->get_session('user_id');
        $s_request['user_code'] = CTX()->get_session('user_code');
        $s_request['user_name'] = CTX()->get_session('user_name');
        $task_data['request'] = $s_request;
        $ret_t = $task->save_task($task_data);
        $chlid_id = $ret_t['data'];

        $task->set_task_child($task_id, $chlid_id); //开启并设置子任务


        $this->update(array('take_stock_task_id' => $task_id, 'take_stock_status' => 1), "take_stock_record_id in({$ids})");

        $task->exec_task_one($task_id);

        if ($type == 1) {
            $action_name = '全盘';
        } else if ($type == 2) {
            $action_name = '部分盘点（SKU级）';
        }
        //记录盘点日志
        $log_data = array(
            'user_id' => CTX()->get_session('user_id'),
            'user_code' => CTX()->get_session('user_code'),
            'add_time' => date('Y-m-d H:i:s'),
            'finish_status' => '已确认'
            , 'module' => 'take_stock_record',
            'action_name' => $action_name,
                //   'pid'=>$id,
        );
        foreach ($id_arr as $pid) {
            $log_data['pid'] = $pid;
            load_model("pur/PurStmLogModel")->insert($log_data);
        }

        return $this->format_ret(1);
    }

    function get_take_stock_inv($recode_code_list) {
        $recode_code_arr = explode(",", $recode_code_list);
        $where = " record_code in ('" . implode("','", $recode_code_arr) . "' )";
        $sql = "select * from  stm_take_stock_record where  " . $where;
        $recode_data = $this->db->get_all($sql);
        $ret_data = '';
        $message = '';
        foreach ($recode_data as $val) {
            $ret_data = $val['take_stock_status'];
            $message = $val['message'];
            break;
        }
        return $this->format_ret(1, $ret_data, $message);
    }

    function set_take_stock_inv($request) {

        if (!isset($request['ids']) || !isset($request['status'])) {
            return $this->format_ret(-1);
        }
        $ids = $request['ids'];
        $type = $request['type'];
        $status = $request['status'];
        $id_arr = explode(",", $ids);
        $id_num = count($id_arr);
        $take_stock_record_where = " take_stock_record_id in({$ids}) ";
        $sql = "select * from  stm_take_stock_record where  " . $take_stock_record_where;
        $record_data = $this->db->get_all($sql);
        if (empty($record_data) || count($record_data) != $id_num) {
            return $this->format_ret(-1, '可盘点单据与要盘点单据数量不匹配');
        }
        $this->update(array('take_stock_status' => $status), $take_stock_record_where);

        if ($type == 1) {
            $action_name = '全盘';
        } else if ($type == 2) {
            $action_name = '部分盘点（SKU级）';
        }

        $task_data = array();
        $message = '';
        $n_request = array();
        $n_request['user_name'] = $request['user_name'];
        if ($status == 2) {
            $ret = $this->count_take_stock_inv($record_data, $type);
            if ($ret['status'] > 0) {
                $task_data['code'] = 'create_adjust_record';
                $n_request['status'] = 3;
                $n_request['record_code'] = $ret['data'];
            } else {
                $message = "计算账面数异常";
            }
        } else if ($status == 3) {
            $record_code = $request['record_code'];
            $record_data['is_add_person'] = $request['user_name'];
            //生成调整
            $ret = $this->create_adjust_record($record_data, $record_code, $type);
            if ($ret['status'] < 0) {
                $message = "生成调整但异常";
            } else {
                $this->update(array('take_stock_status' => 4), $take_stock_record_where);
                $stock_adjust_id = $ret['data'];
                if ($stock_adjust_id > 0) {
                    $ret = load_model('stm/StockAdjustRecordModel')->checkin($stock_adjust_id);
                }
                if ($ret['status'] < 1) {
                    $message = isset($ret['meesage']) ? "调整单验收异常:" . $ret['meesage'] : "调整单验收异常";
                    $ret['meesage'] = $message;
                } else {
                    $this->update(array('is_pre_profit_and_loss' => 1, 'take_stock_status' => 5), $take_stock_record_where);
                }
            }
        }

        if ($ret['status'] < 1) {
            $this->update(array('take_stock_status' => 9, 'message' => $message), $take_stock_record_where);
            //记录盘点日志
        } else if (!empty($task_data)) {
            require_model('common/TaskModel');
            $task = new TaskModel();
            $task_data['start_time'] = time();
            $n_request['app_act'] = 'stm/take_stock_record/set_take_stock_inv';
            $n_request['app_fmt'] = 'json';
            $n_request['ids'] = $ids;
            $n_request['type'] = $type;
            $n_request['user_id'] = $request['user_id'];
            $n_request['user_code'] = $request['user_code'];

            $task_data['request'] = $n_request;
            $ret_t = $task->save_task($task_data);
        }
        if ($message != '' || $status == 3) {
            if ($message == '') {
                $message = '成功完成';
            }
            $record_code = $this->db->get_all_col("select record_code from stm_take_stock_record where take_stock_record_id in ($ids)");
            $record_code_str = implode(',', $record_code);
            //记录盘点日志
            $log_data = array('user_id' => $request['user_id'],
                'user_code' => $request['user_code'],
                'add_time' => date('Y-m-d H:i:s'),
                'finish_status' => '已验收',
                'module' => 'stock_adjust_record',
                'action_name' => "验收",
                'action_note' => '盘点单号为：' . $record_code_str,
                'pid' => $stock_adjust_id
            );
            load_model("pur/PurStmLogModel")->insert($log_data);
            //在盘点单中增加备注 生成调整单号
            if ($status == 3 && $stock_adjust_id > 0) {
                $record_code = $this->db->get_value("select record_code from stm_stock_adjust_record where stock_adjust_record_id = {$stock_adjust_id}");
                $log_data_pd = array(
                    'user_id' => $request['user_id'],
                    'user_code' => $request['user_code'],
                    'add_time' => date('Y-m-d H:i:s'),
                    'finish_status' => '已确认',
                    'module' => 'take_stock_record',
                    'action_name' => $action_name,
                    'action_note' => '调整单号为：' . $record_code,
                );
                $id_arr = explode(',', $ids);
                foreach ($id_arr as $pid) {
                    $log_data_pd['pid'] = $pid;
                    load_model("pur/PurStmLogModel")->insert($log_data_pd);
                }
            }
        }

        return $ret;
    }

    /**
     *
     * 计算账面数
     * @param unknown_type $id
     */
    function count_take_stock_inv($record_data, $type = 1) {

        foreach ($record_data as $val) {
            $take_stock_time = $val['take_stock_time'];
            $store_code = $val['store_code'];
            $record_code_list[] = $val['record_code'];
        }
        sort($record_code_list);
        $record_code_list_str = implode(",", $record_code_list);
        $record_code = $record_code_list_str;



        //采购入库
        $sql = "update pur_purchaser_record,b2b_lof_datail set b2b_lof_datail.order_date=pur_purchaser_record.record_time
        where pur_purchaser_record.record_time>='{$take_stock_time}' and pur_purchaser_record.is_check_and_accept=1
        and pur_purchaser_record.record_code = b2b_lof_datail.order_code and b2b_lof_datail.order_type = 'purchase'
        ";
        $this->db->query($sql);
        //采购退
        $sql = "update pur_return_record,b2b_lof_datail set b2b_lof_datail.order_date=pur_return_record.record_time
        where pur_return_record.record_time>='{$take_stock_time}' and pur_return_record.is_store_out=1
        and pur_return_record.record_code = b2b_lof_datail.order_code and b2b_lof_datail.order_type = 'pur_return'
        ";
        $this->db->query($sql);
        //调整
        $sql = "update stm_stock_adjust_record,b2b_lof_datail set b2b_lof_datail.order_date=stm_stock_adjust_record.record_time
        where stm_stock_adjust_record.record_time>='{$take_stock_time}' and stm_stock_adjust_record.is_check_and_accept=1
        and stm_stock_adjust_record.record_code = b2b_lof_datail.order_code and b2b_lof_datail.order_type = 'adjust'
        ";
        $this->db->query($sql);
        //订单
        $sql = "update oms_sell_record,oms_sell_record_lof set oms_sell_record_lof.order_date=oms_sell_record.delivery_date
        where oms_sell_record.delivery_date>='{$take_stock_time}'
         and oms_sell_record.sell_record_code = oms_sell_record_lof.record_code and (oms_sell_record_lof.record_type = 1 or oms_sell_record_lof.record_type = 3)
        ";
        $this->db->query($sql);
        //退单
        $sql = "update oms_sell_return,oms_sell_record_lof set oms_sell_record_lof.order_date=oms_sell_return.stock_date
        where oms_sell_return.stock_date>='{$take_stock_time}'
         and oms_sell_return.sell_return_code = oms_sell_record_lof.record_code and oms_sell_record_lof.record_type = 2
        ";
        $this->db->query($sql); //receive_time
        //万一残留 删除
        $sql = " delete from stm_profit_loss_lof where take_stock_record_code='{$record_code}'";
        $this->db->query($sql);

        // 批发销货单 + 批发退货单 商品移仓单移入数 - 商品移仓单移除数  // 暂时没有

        $record_conditions = array();
        $_conditions_where = "";
        if ($type == 2) {//部分盘点
            $record_code_list_where = "(order_code = '" . implode("' OR order_code = '", $record_code_list) . "')";

            $sql = "select sku,lof_no,production_date
                    from b2b_lof_datail  where order_type = :order_type  AND " . $record_code_list_where . " GROUP BY sku,lof_no,production_date";


            $take_stock_data = $this->db->get_all($sql, array(':order_type' => 'take_stock'));

            foreach ($take_stock_data as $val) {
                $record_conditions[] = " ( sku='{$val['sku']}' AND  lof_no='{$val['lof_no']}') ";
            }
            $_conditions_where = "  AND  (" . implode(" OR ", $record_conditions) . ") ";
        }




        //计算账面数
        $sql = "insert into stm_profit_loss_lof(take_stock_record_code,record_code_list,goods_code,spec1_code,spec2_code,sku,store_code,num,lof_no,production_date)
    select '{$record_code}'  as record_code,'{$record_code_list_str}' as record_code_list, b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.store_code,sum(b.num),b.lof_no,b.production_date from (
            select goods_code,spec1_code,spec2_code,sku,store_code,stock_num as num,lof_no,production_date from goods_inv_lof
            where  store_code='{$store_code}' {$_conditions_where}
            Union  ALL
            select goods_code,spec1_code,spec2_code,sku,store_code,-num as num,lof_no,production_date from b2b_lof_datail
            where  store_code='{$store_code}' AND occupy_type=3 AND order_date>'{$take_stock_time}'  {$_conditions_where}
            Union  ALL
            select goods_code,spec1_code,spec2_code,sku,store_code,num,lof_no,production_date from b2b_lof_datail where
            store_code='{$store_code}'  and occupy_type=2 and   order_date>'{$take_stock_time}'  {$_conditions_where}
            Union  ALL
            select goods_code,spec1_code,spec2_code,sku,store_code,-num as num,lof_no,production_date from oms_sell_record_lof
            where  store_code='{$store_code}'  and occupy_type=3 and   order_date>'{$take_stock_time}'  {$_conditions_where}
            Union  ALL
            select goods_code,spec1_code,spec2_code,sku,store_code,num,lof_no,production_date from oms_sell_record_lof
            where  store_code='{$store_code}'  and occupy_type=2 and  order_date>'{$take_stock_time}'   {$_conditions_where}
    ) b   group by b.sku,b.store_code,b.lof_no";
        $this->db->query($sql);
        //计算盈亏数
        //1 盘点存在，商品处理
        $record_code_list_where = "(b.order_code = '" . implode("' OR b.order_code = '", $record_code_list) . "')";
        $sql = "insert into stm_profit_loss_lof(take_stock_record_code,record_code_list,goods_code,spec1_code,spec2_code,sku,store_code,num,lof_no,production_date,diff_num,status) ";
        $sql .= " select '{$record_code}'  as record_code,'{$record_code_list_str}' as record_code_list, b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.store_code,0,b.lof_no,b.production_date,sum(b.num) as num,1 as status
                    from b2b_lof_datail b where b.order_type = 'take_stock' AND {$record_code_list_where} group by  b.store_code,b.sku,b.lof_no,b.production_date
                    on duplicate key update  diff_num= VALUES(diff_num)-stm_profit_loss_lof.num,status=1   ";
        $this->db->query($sql);
        //1 盘点存在，需要调整为0
        $sql = "update stm_profit_loss_lof set diff_num=-num,status=1 where take_stock_record_code='{$record_code}' and status=0";
        $this->db->query($sql);
        $status = $this->check_take_inv($record_code_list, $record_code, $store_code, $take_stock_time, $_conditions_where);
        if ($status === FALSE) {
            return $this->format_ret(-1, array(), '盘点异常');
        }


        return $this->format_ret(1, $record_code);
    }

    function check_take_inv($record_code_list, $take_stock_record_code, $store_code, $take_stock_time, $_conditions_where) {

        $record_code_list_where = "(order_code = '" . implode("' OR order_code = '", $record_code_list) . "')";
        $sql = "select sku,store_code,lof_no,production_date,num from (
        select b.sku,b.store_code,b.lof_no,b.production_date,sum(b.num) as num from (
        select sku,store_code,lof_no,production_date,stock_num as num from goods_inv_lof
            where  store_code='{$store_code}'   {$_conditions_where}
            Union  ALL
            select sku,store_code,lof_no,production_date, -num as num  from b2b_lof_datail
            where  store_code='{$store_code}' AND occupy_type=3 AND order_date>'{$take_stock_time}'  {$_conditions_where}
            Union  ALL
            select sku,store_code,lof_no,production_date, num from b2b_lof_datail where
            store_code='{$store_code}'  and occupy_type=2 and   order_date>'{$take_stock_time}'  {$_conditions_where}
            Union  ALL
            select sku,store_code,lof_no,production_date,-num as num from oms_sell_record_lof
            where  store_code='{$store_code}'  and occupy_type=3 and   order_date>'{$take_stock_time}'  {$_conditions_where}
            Union  ALL
            select sku,store_code,lof_no,production_date,num from oms_sell_record_lof
            where  store_code='{$store_code}'  and occupy_type=2 and  order_date>'{$take_stock_time}'   {$_conditions_where}
             Union  ALL
        select sku,store_code,lof_no,production_date,diff_num as num
        from stm_profit_loss_lof where   take_stock_record_code='{$take_stock_record_code}' and diff_num<>0
                Union  ALL
        select sku,store_code,lof_no,production_date,-num as num from b2b_lof_datail where {$record_code_list_where}
        )  b  where b.num<>0 group by b.sku,b.store_code,b.lof_no
        )  c where num>0
        ";
        $data = $this->db->getAll($sql);
        if (empty($data)) {
            return true;
        }
        return FALSE;
    }

    /**
     *
     * 生成调整单
     * @param unknown_type $record_data
     * @param unknown_type $type 1:全盘 2：商品 3：sku
     */
    function create_adjust_record($record_data, $record_code, $type) {
        foreach ($record_data as $val) {
            $take_stock_time = $val['take_stock_time'];
            $store_code = $val['store_code'];
            // $record_code_list[] = $val['record_code'];
            break;
        }


        $sql = "select count(1) from stm_profit_loss_lof where   take_stock_record_code='{$record_code}' and diff_num<>0 ";
        $num = $this->db->getOne($sql);

        //不需要调整库存
        if ($num == 0) {
            return $this->format_ret(1, 0);
        }

        $stock_adjust['record_code'] = load_model('stm/StockAdjustRecordModel')->create_fast_bill_sn();
        $stock_adjust['relation_code'] = $record_code;
        $stock_adjust['init_code'] = '';
        $stock_adjust['store_code'] = $store_code;
        $stock_adjust['record_time'] = $take_stock_time; //业务日期
        $stock_adjust['adjust_type'] = 801;
        $stock_adjust['is_add_person'] = $record_data['is_add_person']; //CTX()->get_session('user_name');
        $ret = load_model('stm/StockAdjustRecordModel')->insert($stock_adjust);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $stock_adjust_id = $ret['data'];
        $sql = "insert into b2b_lof_datail (pid,order_code,order_type,goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,num)
select {$stock_adjust_id} as stock_adjust_id, '{$stock_adjust['record_code']}' as record_code,'adjust',goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,diff_num from stm_profit_loss_lof where   take_stock_record_code='{$record_code}' and diff_num!=0 ";
        $this->db->query($sql);

        $sql = "insert into  stm_stock_adjust_record_detail(pid,record_code,goods_code,spec1_code,spec2_code,sku,num,refer_price,price,rebate,money)";
        /*
          $sql .= " select b.pid,b.order_code,s.goods_code,s.spec1_code,s.spec2_code,s.sku,sum(b.num),g.price,g.price,1 as rebate,g.price*sum(b.num) as money from b2b_lof_datail b
          inner join  goods_sku s ON  s.sku=b.sku
          inner join  base_goods g ON  g.goods_code=s.goods_code
          where  b.order_code='{$stock_adjust['record_code']}'
          group by b.sku";
         */
        $sql .= " select b.pid,b.order_code,s.goods_code,s.spec1_code,s.spec2_code,s.sku,sum(b.num),g.sell_price,g.sell_price,1 as rebate,g.sell_price*sum(b.num) as money from b2b_lof_datail b
        inner join  goods_sku s ON  s.sku=b.sku
        inner join  base_goods g ON  g.goods_code=s.goods_code
        where  b.order_code='{$stock_adjust['record_code']}'
        group by b.sku";
        $this->db->query($sql);
        load_model('stm/StmStockAdjustRecordDetailModel')->mainWriteBack($stock_adjust_id);
        return $this->format_ret(1, $stock_adjust_id); //调整
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn() {

        $sql = "select take_stock_record_id  from stm_take_stock_record   order by take_stock_record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['take_stock_record_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "PD" . date("Ymd") . add_zero($djh);
        return $jdh;
    }

    function imoprt_detail($id, $file, $is_lof = 0) {
        $ret = $this->get_row(array('take_stock_record_id' => $id));
        $store_code = $ret['data']['store_code'];
        $barcode_arr = $barcode_num = array();
        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = $lof_manage['lof_status'];
        if ($is_lof == 1) {
            $num = $this->read_csv_lof($file, $barcode_arr, $barcode_num);
        } else {
            //未开启批次导入库存方法
            $num = $this->read_csv_sku($file, $barcode_arr, $barcode_num);
            //没有开启批次的 查询默认批次 如果批次表里 没有，在批次表里添加goods_lof
            $barcode_str = implode("','", $barcode_arr);


            $sql_moren = "select lof_no,production_date from goods_lof  where type=1";
            $moren = $this->db->get_row($sql_moren);
            $lof_data_new = array();

            //取消掉自适用批次，支持振颜关批次功能
//            $sql = "select g.barcode,g.sku,l.lof_no,l.production_date,l.type from goods_lof l "
//                    . "INNER JOIN goods_sku g ON g.sku=l.sku  where"
//                    . " g.barcode in ('$barcode_str') group by g.barcode";
//            $sku_data = $this->db->get_all($sql);
//            foreach ($sku_data as $lof_data) {
//                $lof_data_new[$lof_data['barcode']]['production_date'] = $lof_data['production_date'];
//                $lof_data_new[$lof_data['barcode']]['lof_no'] = $lof_data['lof_no'];
//                $lof_data_new[$lof_data['barcode']]['sku'] = $lof_data['sku'];
//            }


            $new_barcode_num = $barcode_num;
            $barcode_num = array();

            foreach ($barcode_arr as $barcode) {
                if (array_key_exists($barcode, $lof_data_new)) {
                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['num'] = $new_barcode_num[$barcode]['num'];
                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['lof_no'] = $lof_data_new[$barcode]['lof_no'];
                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['production_date'] = $lof_data_new[$barcode]['production_date'];
                } else {
                    $barcode_num[$barcode][$moren['lof_no']]['num'] = $new_barcode_num[$barcode]['num'];
                    $barcode_num[$barcode][$moren['lof_no']]['lof_no'] = $moren['lof_no'];
                    $barcode_num[$barcode][$moren['lof_no']]['production_date'] = $moren['production_date'];
                }
            }
        }

        if (!empty($barcode_num) && !empty($barcode_arr)) {
            $all_num = count($barcode_arr);
            $sql_values = array();
            $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.sku  from
                    goods_sku b
                    where b.barcode in({$barcode_str}) GROUP BY b.barcode";

            $detail_data = $this->db->get_all($sql, $sql_values); //sell_price
            $detail_data_lof = array();
            $err_num = 0;
            foreach ($detail_data as $key => $val) {
                foreach ($barcode_num[$val['barcode']] as $k1 => $v1) {
                    if (intval($v1['num']) > 0 && is_int($v1['num'] + 0)) {
                        $val['num'] = $v1['num'];
                        $val['lof_no'] = $v1['lof_no'];
                        $val['production_date'] = $v1['production_date'];
                        $detail_data_lof[] = $val;
                        unset($barcode_num[$val['barcode']]);
                    } else {
                        $error_msg[] = array($val['barcode'] => '数量不能为空或小于1或小数');
                        $err_num ++;
                        unset($barcode_num[$val['barcode']]);
                    }
                }
            }

            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $detail_data_lof, 'take_stock');
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'take_stock', $detail_data_lof);
            if ($ret['status'] < 1) {
                return $ret;
            }
            //入库单明细添加
            $ret = $this->add_detail_action($id, $detail_data_lof, 'take_stock');
        }
        $ret['data'] = '';


        if (!empty($barcode_num)) {
            $sku_error = array_keys($barcode_num);
            foreach ($sku_error as $err) {
                $error_msg[] = array($err => '系统不存在该条码信息');
                $err_num ++;
            }
        }

        $success_num = $all_num - $err_num;
        $message = '导入成功数量: ' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $message .= ',' . '失败数量: ' . $err_num;
            $fail_top = array('商品条码', '错误信息');
            $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        if ($success_num > 0) {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未确认', 'action_name' => '导入明细', 'module' => "take_stock_record", 'pid' => $id);
            load_model('pur/PurStmLogModel')->insert($log);
        }
        $ret['message'] = $message;
        return $ret;
    }

    function create_import_fail_files($fail_top, $sku_error) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($sku_error as $val) {
            $val_data = array($val, '条码不存在');
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("take_stock_record_import" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function read_csv_sku($file, &$sku_arr, &$sku_num) {
        //    $key_arr = array('0'=>'sku','1'=>'num');
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sku_arr[] = $row[0];
                    $sku_num[$row[0]]['num'] = $row[1];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
        // var_dump($sku_arr,$sku_num);die;
    }

    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
                $val = iconv('gbk', 'utf-8', $val);
                $val = str_replace('"', '', $val);
                //   $row[$key] = $val;
            }
        }
    }

    //修改扫描数量
    function update_scan_num($record_code, $num, $id) {
        $ret = $this->get_row(array('record_code' => $record_code));
        $relation_code = $ret['data']['relation_code'];
        $sku = substr($id, 8);
        //$sku = $this->db->get_value("select sku from goods_sku where barcode = :barcode", array(":barcode" => $barcode));

        $detail = $this->db->get_row("select * from stm_take_stock_record_detail where record_code = '{$record_code}' and sku = '{$sku}'");
        if (empty($detail)) {
            return $this->format_ret(-1, '', '单据明细信息不存在');
        }
        $detail['num'] = $num;
        $ret = $this->edit_detail_action($ret['data']['take_stock_record_id'], $detail);
        if ($ret) {
            return $this->format_ret(1, '', '更细成功');
        } else {
            return $this->format_ret(-1, '', '扫描更新单据明细数量失败');
        }
    }

    public function edit_detail_action($pid, $data) {
        $ret = $this->db->update('stm_take_stock_record_detail', array('num' => $data['num'], 'money' => $data['num'] * $data['price'] * $data['rebate']), array('pid' => $pid, 'sku' => $data['sku']));
        $res = $this->mainWriteBack($pid);
        $this->update_lof_detail($data['record_code'], $data['sku'], $data['num']);
        return $ret;
    }

    function update_lof_detail($record_code, $sku, $num) {
        $sql = "select * from b2b_lof_datail where order_code=:record_code AND sku=:sku AND order_type='take_stock' ";
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

    function read_csv_lof($file, &$sku_arr, &$sku_num) {
        //    $key_arr = array('0'=>'sku','1'=>'num');
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);

            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $row[0] = trim($row[0]);
                    $sku_arr[] = $row[0];
                    $sku_num[$row[0]][$row[2]]['num'] = $row[1];
                    $sku_num[$row[0]][$row[2]]['lof_no'] = $row[2];
                    $production_date = load_model('prm/GoodsLofModel')->get_lof_production_date($row[2], $row[0]);
                    // $production_date = $this->db->get_row("select production_date from goods_lof where sku = '{$row[0]}' and lof_no = '{$row[3]}'");
                    $sku_num[$row[0]][$row[2]]['production_date'] = !empty($production_date) ? $production_date : $row[3];
                }
            }
            $i++;
        }
        fclose($file);

        return $i;
    }

    /**
     * 检查接口传入明细信息是否为空
     */
    private function api_check_detail($take_detail, $check_key) {
        $err_data = array();
        foreach ($take_detail as $key => $val) {
            foreach ($check_key as $k => $v) {
                if (empty($val[$k])) {
                    $err_data[$key][$k] = $v;
                }
            }
        }
        if (!empty($err_data)) {
            return $this->format_ret(-10001, $err_data, "明细数据不能为空");
        }
        return $this->format_ret(1);
    }

    /**
     * 获取商品明细信息
     */
    private function api_get_goods_detail($take_detail) {
        $data = array();
        foreach ($take_detail as $key => $val) {
            $sql = "SELECT b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.sell_price FROM goods_sku b
                    INNER JOIN  base_goods g ON g.goods_code = b.goods_code
                    WHERE b.barcode ='{$val['barcode']}' GROUP BY b.barcode ";
            $goods_data = $this->db->get_row($sql);
            $data[] = array_merge($goods_data, $take_detail[$key]);
        }
        return $data;
    }

    /**
     * @todo        更新盘点单明细接口
     * @author      BaiSon PHP
     * @date        2016-03-01
     * @param       array $param
     *              array(
     *                  必选: 'record_code'
     *                  可选: 'is_sure'
     *                  必选: 'stock_detail'=>{
     *                      必选: 'barcode','num'
     *                      可选: 'lof_no','production_date'
     *                  }
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_stock_update($param) {
        if (!isset($param['record_code']) || empty($param['record_code'])) {
            return $this->format_ret(-1, '', '请设置盘点单号');
        }

        //获取主单据信息
        $stock = $this->get_row(array('record_code' => $param['record_code']));
        if ($stock['status'] != 1) {
            return $this->format_ret(-1, array('reocrd_code' => $param['record_code']), '盘点单不存在');
        }
        if ($stock['data']['is_sure'] == 1) {
            return $this->format_ret(-1, '', '盘点单已确认，不能更新明细');
        }
        $record_code = $param['record_code'];
        $store_code = $stock['data']['store_code'];
        $id = $stock['data']['take_stock_record_id'];

        //判断提交的明细数是否超限
        $arr_detail = json_decode($param['stock_detail'], true);
        if (count($arr_detail) > 200) {
            return $this->format_ret(-1, '', '盘点单明细数超限');
        }

        $sure = 0;
        if ((int) $param['is_sure'] === 1) {
            $sure = (int) $param['is_sure'];
        }
        unset($param);

        if (isset($arr_detail) && !empty($arr_detail)) {
            $check_key = array('barcode' => '条码', 'num' => '调整数');
            $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
            if ($lof_status['lof_status'] == 1) {
                $check_key = array('barcode' => '条码', 'num' => '调整数', 'lof_no' => '批次', 'production_date' => '生产日期');
            }
            //检查明细是否为空
            $find_data = $this->api_check_detail($arr_detail, $check_key);
            if ($find_data['status'] != 1) {
                return $find_data;
            }

            $data = $this->api_get_goods_detail($arr_detail);
            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $data, 'take_stock');
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'take_stock', $data);
            if ($ret['status'] != 1) {
                return $ret;
            }
//调整单明细添加
            $ret = $this->add_detail_action($id, $data);
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', $ret['message']);
            } else {
                //日志
                $log = array('user_id' => '1', 'user_code' => '系统管理员', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'finish_status' => '未确认', 'action_name' => '更新明细', 'module' => "take_stock_record", 'pid' => $id);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }

            $msg = '盘点单明细更新成功';
            if ($sure == 1) {
                $ret_sure = $this->update_by_id($id, array("take_stock_record_id" => $id), array("status" => 1, "is_sure" => 1));
                $msg .= $ret['status'] == 1 ? '，盘点单确认成功' : ',盘点单确认失败';
                if ($ret_sure['status'] != 1) {
                    return $ret_sure;
                } else {
                    //日志
                    $log = array('user_id' => '1', 'user_code' => '系统管理员', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'finish_status' => '已确认', 'action_name' => '盘点单确认', 'module' => "take_stock_record", 'pid' => $id);
                    $ret1 = load_model('pur/PurStmLogModel')->insert($log);
                }
            }

            return $this->format_ret(1, array('record_code' => $record_code), $msg);
        } else {
            if ($sure == 0) {
                return $this->format_ret(-10001, '', '参数为空');
            }
            $ret = $this->update_by_id($id, array("take_stock_record_id" => $id), array("status" => 1, "is_sure" => 1));
            if ($ret['status'] != 1) {
                return $ret;
            } else {
                //日志
                $log = array('user_id' => '1', 'user_code' => '系统管理员', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'finish_status' => '已确认', 'action_name' => '盘点单确认', 'module' => "take_stock_record", 'pid' => $id);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
            return $this->format_ret(1, array('record_code' => $record_code), '盘点单确认成功');
        }
    }

    /**
     * @todo        创建盘点单接口
     * @author      BaiSon PHP
     * @date        2016-03-01
     * @param       array $param
     *              array(
     *                  必选: 'store_code'
     *                  可选: 'record_time','remark'
     *                  可选: 'stock_detail'=>{
     *                      必选: 'barcode','num'
     *                      可选: 'lof_no'
     *                  }
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_stock_create($param) {
        if (!isset($param['store_code']) || empty($param['store_code'])) {
            return $this->format_ret(-10001, '', '请设置盘点仓库');
        }
        $store_code = $param['store_code'];
        //检测客户提交的仓库是否存在
        $store = $this->is_exists($store_code, 'store_code');
        if ($store['status'] != 1) {
            return $this->format_ret(-1, '', '盘点仓库不存在');
        }
        $stock_record = array();
        $stock_record['store_code'] = $store_code;
        $stock_record['record_code'] = $this->create_fast_bill_sn();
        $stock_record['is_add_time'] = date('Y-m-d H:i:s', time());
        $stock_record['is_add_person'] = 'OPENAPI';
        if (empty($param['record_time'])) {
            $stock_record['record_time'] = date('Y-m-d');
        } else if (strtotime($param['record_time']) == false) {
            return $this->format_ret(-1, '', '日期格式错误');
        } else {
            $stock_record['record_time'] = $param['record_time'];
        }
        $stock_record['take_stock_time'] = date('Y-m-d');
        if (isset($param['remark'])) {
            $stock_record['remark'] = $param['remark'];
        }
        /* 添加盘点单明细 */
        $arr_detail = json_decode($param['stock_detail'], true);
        if (count($arr_detail) > 200) {
            return $this->format_ret(-1, '', '盘点单明细数超限');
        }

        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = ($lof_manage['lof_status'] == 1) ? TRUE : FALSE;

        $check_key = array('barcode' => '条码', 'num' => '数量');
        if ($is_lof) {
            $check_key['lof_no'] = '批次';
        } else {
            $this->set_default_lof();
        }
        $detail_data = array();
        foreach ($arr_detail as $val) {
            $find_data = $this->check_data($val, $check_key);

            if (!empty($find_data)) {
                return $this->format_ret(-1, "明细数据不能为空" . implode(",", $find_data));
            }

            $row = $this->set_detail($val, $is_lof);
            if (empty($row)) {
                $msg = ($is_lof === TRUE) ? "商品条码({$val['barcode']})或批次({$val['lof_no']})不存在" : '商品条码不存在:' . $val['barcode'];
                return $this->format_ret(-10001, '', $msg);
            }
            $detail_data[] = $row;
        }
        $this->begin_trans();

        $ret_record = $this->insert($stock_record);

        $this->commit();
        $pid = $ret_record['data'];
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($pid, $detail_data, 'take_stock');
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $store_code, 'take_stock', $detail_data);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $ret = $this->add_detail_action($pid, $detail_data, 'take_stock');

        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //日志
        $log = array('user_id' => '1', 'user_code' => 'OPENAPI', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'finish_status' => '未确认', 'action_name' => '创建盘点单', 'module' => "take_stock_record", 'action_note' => 'API调用生成', 'pid' => $pid);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        return $this->format_ret(1, array('record_code' => $stock_record['record_code']), '创建盘点单成功');
    }

    private function set_default_lof() {

        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
        $this->lof_no = $ret_lof['data']['lof_no'];
        $this->production_date = $ret_lof['data']['production_date'];
    }

    private function check_data($check_data, $key_arr) {
        $key_arr = array();

        $no_find = array();
        foreach ($key_arr as $key => $name) {
            if (!isset($check_data[$key])) {
                $no_find[] = $name . "({$key})";
            }
        }
        return $no_find;
    }

    private function set_detail($row, $is_lof) {
        $new_row = array();
        if ($is_lof) {
            $sql = "select s.sku,s.goods_code,s.spec1_code,s.spec2_code,l.lof_no,l.production_date from goods_sku s INNER JOIN goods_lof l ON s.sku=l.sku where s.barcode=:barcode AND l.lof_no=:lof_no ";
            $data = $this->db->get_row($sql, array(':barcode' => $row['barcode'], ':lof_no' => $row['lof_no']));
            if (!empty($data)) {
                $new_row = $data;
                $new_row['num'] = $row['num'];
            }
        } else {
            $sql = "select sku,goods_code,spec1_code,spec2_code from goods_sku s where s.barcode=:barcode  ";
            $data = $this->db->get_row($sql, array(':barcode' => $row['barcode']));
            if (!empty($data)) {
                $new_row = $data;
                $new_row['num'] = $row['num'];
                $new_row['lof_no'] = $this->lof_no;
                $new_row['production_date'] = $this->production_date;
            }
        }
        return $new_row;
    }

    function get_detail_by_record_code($record_code, $lof_status, $goods_code) {
        $sql = "SELECT
                    st.take_stock_record_id,
                    st.is_sure,
                    st.is_pre_profit_and_loss,
                    st.is_stop,
                    st.take_stock_time,
                    bs.store_name
                FROM
                    stm_take_stock_record st
                LEFT JOIN base_store bs on st.store_code=bs.store_code
                WHERE record_code=:record_code";
        $sql_value = array(":record_code" => $record_code);
        $data = $this->db->get_row($sql, $sql_value);
        $data['is_sure'] = ($data['is_sure'] == 1) ? "已确认" : "未确认";
        $data['is_stop'] = ($data['is_stop'] == 1) ? "已终止" : "未终止";
        $data['is_pre_profit_and_loss'] = ($data['is_pre_profit_and_loss'] == 1) ? "已盘点" : "未盘点";
        $pid = $data['take_stock_record_id'];
        if ($lof_status == 1) {
            $select = "SELECT sd.*,gs.spec1_name,gs.spec2_name,bg.goods_name,gs.barcode,bd.lof_no,bd.production_date";
            $sql_join = "LEFT JOIN goods_sku gs ON sd.sku = gs.sku
                         LEFT JOIN base_goods bg ON sd.goods_code = bg.goods_code
                         LEFT JOIN b2b_lof_datail bd ON sd.pid=bd.pid";
        } else {
            $select = "SELECT sd.*,gs.spec1_name,gs.spec2_name,bg.goods_name,gs.barcode";
            $sql_join = "LEFT JOIN goods_sku gs ON sd.sku = gs.sku
                         LEFT JOIN base_goods bg ON sd.goods_code = bg.goods_code";
        }
        $where = (!empty($goods_code)) ? " WHERE sd.pid=:pid AND sd.goods_code LIKE :goods_code" : "WHERE sd.pid=:pid";
        $detail_sql = "{$select} FROM stm_take_stock_record_detail sd {$sql_join} {$where}
                        GROUP BY sd.sku";
        $detail_value = (!empty($goods_code)) ? array(":pid" => $pid, ":goods_code" => "%" . $goods_code . "%") : array(":pid" => $pid);
        $detail_data = $this->db->get_all($detail_sql, $detail_value);
        foreach ($detail_data as &$value) {
            $value['is_sure'] = $data['is_sure'];
            $value['is_pre_profit_and_loss'] = $data['is_pre_profit_and_loss'];
            $value['is_stop'] = $data['is_stop'];
            $value['record_code'] = $record_code;
            $value['take_stock_time'] = $data['take_stock_time'];
            $value['store_name'] = $data['store_name'];
        }
        return $detail_data;
    }

    public function add_detail($param) {
        $this->begin_trans();
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($param['record_id'], $param['detail'], 'take_stock');
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($param['record_id'], $param['store_code'], 'take_stock', $param['detail']);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }
        $ret = $this->add_detail_action($param['record_id'], $param['detail'], 'take_stock');
        if ($ret['status'] == 1) {
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未确认', 'action_name' => '新增明细', 'module' => "take_stock_record", 'pid' => $param['record_id']);
            load_model('pur/PurStmLogModel')->insert($log);
            $this->commit();
        } else {
            $this->rollback();
        }

        return $ret;
    }

}
