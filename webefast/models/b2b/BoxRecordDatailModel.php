<?php

/**
 * 库单详情管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('stm');

class BoxRecordDatailModel extends TbModel {

    function get_table() {
        return 'b2b_box_record_detail';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_main = "FROM b2b_box_record rl
		INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		INNER JOIN goods_sku r4 on r2.sku = r4.sku
		WHERE  1=1  ";

        $sql_values = array();
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r2.record_code = :record_code )";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            return;
        }
        //商品名称/编码/条形码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $filter['code_name'] = trim($filter['code_name']);
            $sql_main .= " AND (r2.goods_code LIKE :code_name OR r3.goods_name LIKE :code_name OR r4.barcode LIKE :code_name )";
            $sql_values[':code_name'] = "%{$filter['code_name']}%";
        }

        $select = 'r2.*,r3.goods_name,r4.barcode,spec1_name,r4.spec2_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 获取开批次装箱明细数据
     * @param array $filter 条件
     * @return array 数据集
     */
    function get_by_page_lof($filter) {
        $sql_main = "FROM b2b_box_task bt
                INNER JOIN {$this->table} rd ON bt.task_code=rd.task_code
		INNER JOIN b2b_lof_datail ld ON bt.relation_code = ld.order_code
		INNER JOIN goods_sku gs ON rd.sku = gs.sku
		WHERE ld.order_type = 'wbm_store_out' ";

        $sql_values = array();
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND rd.record_code = :record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            return;
        }
        //商品名称/编码/条形码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $filter['code_name'] = trim($filter['code_name']);
            $sql_main .= " AND (bg.goods_code LIKE :code_name or bg.goods_name LIKE :code_name or gs.barcode LIKE :code_name)";
            $sql_values[':code_name'] = "%{$filter['code_name']}%";
        }

        $select = 'rd.id,rd.record_code,rd.goods_code,gs.sku,gs.barcode,rd.num,ld.lof_no,ld.production_date,gs.spec1_name,gs.spec2_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        filter_fk_name($data['data'], array('goods_code|goods_code'));

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function delete_box_record($pid, $sku, $record_type) {
        $data = load_model('wbm/StoreOutRecordModel')->get_row(array('store_out_record_id' => $pid));
        $box_task = load_model('b2b/BoxTaskModel')->get_row(array('relation_code' => $data['data']['record_code'], 'record_type' => 'wbm_store_out'));
        if ($box_task['status'] == 1) {
            $ret = $this->delete(array('task_code' => $box_task['data']['task_code'], 'sku' => $sku));
            return $ret;
        }
        return $this->format_ret(-1, '', '装箱信息不存在');
    }

    /**
     * 更新装箱单明细数量
     * @param array $params 参数
     * @return array 更新结果
     * @throws Exception
     */
    function update_detail($params) {
        $this->begin_trans();
        try {
            //检查参数是否为空
            $ret_check = $this->check_params($params);
            if ($ret_check['status'] != 1) {
                throw new Exception($ret_check['message'], $ret_check['status']);
            }
            $is_lof = $params['is_lof'];
            $record_code = $params['record_code'];
            $sku = $params['sku'];
            //校验明细是否存在
            $wh = array('record_code' => $record_code, 'sku' => $sku);
            $ret_detail = $this->get_row($wh);
            if ($ret_detail['status'] != 1 || empty($ret_detail['data'])) {
                throw new Exception('明细不存在', -1);
            }
            $task_code = $ret_detail['data']['task_code'];
            $old_num = (int) $ret_detail['data']['num'];
            $new_num = (int) $params['num'];
            if ($old_num == $new_num) {
                throw new Exception('数量未修改', 2);
            }
            unset($ret_detail);
            //关联单据查询
            $sql = 'SELECT br.id,bt.is_check_and_accept,sr.store_out_record_id AS pid,sr.record_code,rd.rebate,rd.price,rd.num FROM b2b_box_task AS bt INNER JOIN b2b_box_record AS br ON bt.task_code=br.task_code INNER JOIN wbm_store_out_record AS sr ON bt.relation_code=sr.record_code INNER JOIN wbm_store_out_record_detail AS rd ON sr.record_code=rd.record_code WHERE bt.task_code=:task_code AND rd.sku=:sku AND br.record_code=:record_code';
            $ret_record = $this->db->get_row($sql, array(':task_code' => $task_code, ':sku' => $sku, ':record_code' => $record_code));
            if (empty($ret_record)) {
                throw new Exception('关联单据不存在', -1);
            }
            if ($ret_record['is_check_and_accept'] == 1) {
                throw new Exception('关联单据已验收，不能修改', -1);
            }

            if ($old_num > $new_num) {
                $num = $ret_record['num'] - ($old_num - $new_num);
            } else {
                $num = $ret_record['num'] + ($new_num - $old_num);
            }

            //销货单数量更新
            if ($is_lof == 1) {
                $p_detail = array('sku' => $sku, 'rebate' => $ret_record['rebate'], 'price' => $ret_record['price'], 'barcode' => $params['barcode']);
                $p_detail_lof = array('num' => $num, 'record_code' => $ret_record['record_code'], 'lof_no' => $params['lof_no'], 'production_date' => $params['lof_no']);
                $ret = load_model('wbm/StoreOutRecordDetailModel')->edit_detail_action_lof($ret_record['pid'], $p_detail, $p_detail_lof);
            } else {
                $p_detail = array('record_code' => $ret_record['record_code'], 'rebate' => $ret_record['rebate'], 'sku' => $sku, 'num' => $num, 'price' => $ret_record['price'], 'barcode' => $params['barcode']);
                $ret = load_model('wbm/StoreOutRecordDetailModel')->edit_detail_action($ret_record['pid'], array($p_detail));
            }
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }
            //回写装箱单据
            $ret = $this->update(array('num' => $new_num), $wh);
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }
            $ret = load_model('b2b/BoxRecordModel')->mainWriteBack(array('record_code' => $record_code));
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }
            $ret = load_model('b2b/BoxTaskModel')->mainWriteBack(array('task_code' => $task_code));
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }

            $this->opt_log($ret_record['id'], '修改数量', "条码：{$params['barcode']}，数量由{$old_num}改为{$new_num}");
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret($e->getCode(), '', $e->getMessage());
        }
    }

    function delete_detail($params) {
        $this->begin_trans();
        try {
            //检查参数是否为空
            $ret_check = $this->check_params($params);
            if ($ret_check['status'] != 1) {
                throw new Exception($ret_check['message'], $ret_check['status']);
            }
            $is_lof = $params['is_lof'];
            $record_code = $params['record_code'];
            $sku = $params['sku'];
            //校验明细是否存在
            $wh = array('record_code' => $record_code, 'sku' => $sku);
            $ret_detail = $this->get_row($wh);
            if ($ret_detail['status'] != 1 || empty($ret_detail['data'])) {
                throw new Exception('明细不存在,删除失败', -1);
            }
            $task_code = $ret_detail['data']['task_code'];
            $old_num = (int) $ret_detail['data']['num'];
            unset($ret_detail);
            //关联单据查询
            $sql = 'SELECT br.id,bt.is_check_and_accept,sr.store_out_record_id AS pid,sr.record_code,rd.rebate,rd.price,rd.num FROM b2b_box_task AS bt INNER JOIN b2b_box_record AS br ON bt.task_code=br.task_code INNER JOIN wbm_store_out_record AS sr ON bt.relation_code=sr.record_code INNER JOIN wbm_store_out_record_detail AS rd ON sr.record_code=rd.record_code WHERE bt.task_code=:task_code AND rd.sku=:sku AND br.record_code=:record_code';
            $ret_record = $this->db->get_row($sql, array(':task_code' => $task_code, ':sku' => $sku, ':record_code' => $record_code));
            if (empty($ret_record)) {
                throw new Exception('关联单据不存在，删除失败', -1);
            }
            if ($ret_record['is_check_and_accept'] == 1) {
                throw new Exception('关联单据已验收，不能删除', -1);
            }
            $num = $ret_record['num'] - $old_num;

            //销货单数量更新
            if ($is_lof == 1) {
                $p_detail = array('sku' => $sku, 'rebate' => $ret_record['rebate'], 'price' => $ret_record['price'], 'barcode' => $params['barcode']);
                $p_detail_lof = array('num' => $num, 'record_code' => $ret_record['record_code'], 'lof_no' => $params['lof_no'], 'production_date' => $params['lof_no']);
                $ret = load_model('wbm/StoreOutRecordDetailModel')->edit_detail_action_lof($ret_record['pid'], $p_detail, $p_detail_lof);
            } else {
                $p_detail = array('record_code' => $ret_record['record_code'], 'rebate' => $ret_record['rebate'], 'sku' => $sku, 'num' => $num, 'price' => $ret_record['price'], 'barcode' => $params['barcode']);
                $ret = load_model('wbm/StoreOutRecordDetailModel')->edit_detail_action($ret_record['pid'], array($p_detail));
            }
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }
            //回写装箱单据
            $ret = $this->delete($wh);
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }
            $ret = load_model('b2b/BoxRecordModel')->mainWriteBack(array('record_code' => $record_code));
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }
            $ret = load_model('b2b/BoxTaskModel')->mainWriteBack(array('task_code' => $task_code));
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }

            $this->opt_log($ret_record['id'], '删除明细', "删除商品，条码为：{$params['barcode']}");
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret($e->getCode(), '', $e->getMessage());
        }
    }

    function opt_log($pid, $action_name, $action_note) {
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '', 'action_name' => $action_name, 'action_note' => $action_note, 'module' => "box_record", 'pid' => $pid);
        load_model('pur/PurStmLogModel')->insert($log);
    }

    /**
     * 检查参数是否存在
     * @param array $params 参数
     * @param array $field_arr 要检查的字段
     * @return array 检查结果
     */
    public function check_params($params) {
        if (empty($params)) {
            return $this->format_ret(-1, '', '内部参数错误，请刷新页面重试');
        }
        $status = '1';
        $msg = '';
        foreach ($params as $v) {
            if ($v == '') {
                $status = '-1';
                $msg = '内部参数错误，请刷新页面重试';
                break;
            }
        }
        return $this->format_ret($status, array(), $msg);
    }

}
