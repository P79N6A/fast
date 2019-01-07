<?php

/**
 * 采购计划订单相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('pur');

class ReturnNoticeDetailRecordModel extends TbModel {

    function get_table() {
        return 'wbm_return_notice_detail_record';
    }

    public function is_exists($value, $field_name = 'return_notice_code') {
        $m = load_model('wbm/ReturnNoticeRecordModel');
        $ret = $m->get_row(array($field_name => $value));
        return $ret;
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        //print_r($filter);
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl
                INNER JOIN base_goods r3 on rl.goods_code = r3.goods_code
		INNER JOIN wbm_return_notice_record r2 on rl.return_notice_code = r2.return_notice_code
		WHERE 1 ";


        $sql_values = array();
        if (isset($filter['return_notice_code']) && $filter['return_notice_code'] != '') {
            $sql_main .= " AND (r2.return_notice_code = :return_notice_code )";
            $sql_values[':return_notice_code'] = $filter['return_notice_code'];
        } else {
            return;
        }

        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :code_name or rl.barcode LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        $select = 'rl.*,r2.is_check,r3.brand_name';
        //$sql_main .= "group by r2.sku";

        if (isset($filter['is_fenye']) && $filter['is_fenye'] == '1') {
            $filter['page_size'] = 1000;
        }

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['trade_price'] = sprintf('%.2f', $value['trade_price']);
            $data['data'][$key]['price'] = round($value['price'], 2);
            $data['data'][$key]['rebate'] = round($value['rebate'], 2);
            $data['data'][$key]['money'] = round($value['money'], 2);

            $data['data'][$key]['difference_num'] = $data['data'][$key]['num'] - $data['data'][$key]['finish_num'];
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $data['data'][$key] = array_merge($data['data'][$key], $sku_info);
            //$data['data'][$key]['store_name'] = $this->get_store_code($value[store_code]);
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_select_data($id, $select_sku_arr) {
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($select_sku_arr, 'sku', $sql_values);
        $sql = " select goods_code,spec1_code,spec2_code,sku,refer_price,price,rebate,money,num,finish_num,(num-finish_num) as no_finish_num
            from  pur_order_record_detail  where pid='{$id}' and sku in($sku_str)";
        $data = $this->db->get_all($sql,sql_values);


        return $this->format_ret(1, $data);
    }

    /**
     * 新增多条明细记录
     */
    public function add_detail_action($pid, $ary_details) {

        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'return_notice_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), 'ORDER_RELATION_ERROR_CODE!');
        }
        //判断主单据状态
        if ($record['data']['is_check'] == 1) {
            return $this->format_ret(false, array(), 'ORDER_RELATION_ERROR_CHECK!');
        }
        $this->begin_trans();
        try {
            foreach ($ary_details as $ary_detail) {
                if (!isset($ary_detail['num']) || $ary_detail['num'] == 0) {
                    continue;
                }
                $ary_detail['finish_num'] = 0;
                $ary_detail['return_notice_record_id'] = $pid;
                $ary_detail['return_notice_code'] = $record['data']['return_notice_code'];
                $ary_detail['rebate'] = $record['data']['rebate'];
                //todo 此处参考价格取goods_price表中的trade_price字段
                //if(isset($ary_detail['trade_price']) || empty($ary_detail['purchase_price']) || $ary_detail['purchase_price'] <= 0 || !is_numeric($ary_detail['purchase_price'])){
                $ary_detail['trade_price'] = (isset($ary_detail['trade_price']) && $ary_detail['trade_price'] != 0) ? $ary_detail['trade_price'] : 0;

                $ary_detail['price'] = $ary_detail['trade_price'] * $ary_detail['rebate']; //单价
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'];

                //判断barcode是否已经存在
                $check = $this->is_detail_exists($record['data']['return_notice_code'], $ary_detail['sku']);
                if ($check) {

                    //更新明细数据
                    $ret = $this->update($ary_detail, array(
                        'return_notice_record_id' => $pid, 'sku' => $ary_detail['sku']
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
            $this->mainWriteBack($record['data']['return_notice_code']);
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), 'DATABASE_ERROR:' . $e->getMessage());
        }
    }

    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id) {
        if (!load_model('sys/PrivilegeModel')->check_priv('wbm/return_notice_record/do_finish')) {
            return $this->format_ret(-1, array(), '无权访问');
        }
        $detail = $this->get_row(array('return_notice_record_detail_id' => $id));
        if (isset($detail['data']['return_notice_code'])) {
            $record = $this->is_exists($detail['data']['return_notice_code'], 'return_notice_code');
            if (isset($record['data']['is_check']) && 1 == $record['data']['is_check']) {
                return $this->format_ret(false, array(), 'ORDER_DELETE_ERROR_CHECK');
            }
        }
        $result = parent::delete(array('return_notice_record_detail_id' => $id));
        $this->mainWriteBack($detail['data']['return_notice_code']);
        return $result;
    }

    function do_delete($return_notice_code) {
        if (!load_model('sys/PrivilegeModel')->check_priv('wbm/return_notice_record/do_delete')) {
            return $this->format_ret(-1, array(), '无权访问');
        }
        $detail = $this->get_row(array('return_notice_code' => $return_notice_code));
        if (isset($detail['data']['return_notice_code'])) {
            $record = $this->is_exists($detail['data']['return_notice_code'], 'return_notice_code');
            if (isset($record['data']['is_check']) && 1 == $record['data']['is_check']) {
                return $this->format_ret(false, array(), 'ORDER_DELETE_ERROR_CHECK');
            }
        }
        $result = parent::delete(array('return_notice_code' => $return_notice_code));
        return $result;
    }

    public function get_all_details($return_notice_code) {
        $result = parent::get_all(array('return_notice_code' => $return_notice_code));
        return $result;
    }

    /**
     * 根据明细的sku和主单据id,判断明细是否已经存在
     * @param   int     $pid    主单据ID
     * @param   string  $sku    SKU编号
     * @return  boolean 存在返回true
     */
    private function is_detail_exists($return_notice_code, $sku) {
        $ret = $this->get_row(array(
            'return_notice_code' => $return_notice_code,
            'sku' => $sku
        ));

        if ($ret['status'] == 1 && !empty($ret['data'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 回写完成数
     * @param   string  $record_code   主单据号
     * @param   string  $data
     */
    public function update_finish_num($record_code, $data) {
        foreach ($data as $ary_detail) {
            //$ret= parent:: update(array('finish_num' => $ary_detail['num']), array('record_code' => $record_code,'sku' => $ary_detail['sku']));
            $sql = "update pur_order_record_detail set finish_num = finish_num + {$ary_detail['num']} where record_code = :record_code and sku = :sku ";
            $res = $this->query($sql, array(':record_code' => $record_code, ':sku' => $ary_detail['sku']));
            $ret = load_model('pur/OrderRecordModel')->get_by_field('record_code', $record_code);
            if (isset($ret['data']['relation_code']) && $ret['data']['relation_code'] <> '') {
                //回写采购计划完成数
                $ret = load_model('pur/PlannedRecordDetailModel')->update_finish_num($ret['data']['relation_code'], $ary_detail['sku'], $ary_detail['num']);
            }
        }
        $this->mainFinishWriteBack($record_code);
        //回写采购订单完成状态
        $ret1 = load_model('pur/OrderRecordModel')->update_finish($record_code);
        if ($ret1['status'] == '1') {
            $ret2 = load_model('pur/OrderRecordModel')->update_check_record_code('1', 'is_finish', $record_code);
            if ($ret2['status'] == '1') {
                //日志
                $data = load_model('pur/OrderRecordModel')->get_by_field('record_code', $record_code, 'order_record_id');
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '完成', 'action_name' => '完成', 'module' => "order_record", 'pid' => $data['data']['order_record_id']);
                load_model('pur/PurStmLogModel')->insert($log);
            }
        }
        //回写计划单完成状态
        $data1 = load_model('pur/OrderRecordModel')->get_by_field('record_code', $record_code, 'relation_code');
        if (isset($data1['data']['relation_code']) && $data1['data']['relation_code'] <> '') {
            $ret1 = load_model('pur/PlannedRecordModel')->update_finish($data1['data']['relation_code']);
            if ($ret1['status'] == '1') {
                $ret = load_model('pur/PlannedRecordModel')->update_check_record_code('1', 'is_finish', $data1['data']['relation_code']);
            }
        }
        return $ret;
    }

    //回写主表完成数
    public function mainFinishWriteBack($record_code) {
        //回写完成数量
        $sql = "update pur_order_record set
		pur_order_record.finish_num = (select sum(finish_num) from pur_order_record_detail where record_code = :id)
		where pur_order_record.record_code = :id ";
        $res = $this->query($sql, array(':id' => $record_code));
        return $res;
    }

    /**
     * @todo 编辑批发退货通知单详情
     * @param int $pid return_notice_record_id
     * @param array $ary_details 详情数据
     * @return  array
     */
    public function edit_detail_action($pid, $ary_details) {
        $this->begin_trans();
        $sql = "SELECT * FROM wbm_return_notice_record WHERE return_notice_record_id=:return_notice_record_id ";
        $data = $this->db->get_row($sql, array(':return_notice_record_id' => $pid));
        try {
            foreach ($ary_details as $ary_detail) {
                $ret = $this->update($ary_detail, array(
                    'return_notice_record_id' => $pid, 'sku' => $ary_detail['sku']
                ));
                if ($ret['status'] != 1) {
                    return $ret;
                }
            }
            //回写数量和金额
            $this->mainWriteBack($data['return_notice_code']);
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    /**
     * 主单据数据回写
     */
    public function mainWriteBack($return_notice_code) {
        //回写数量和金额
        $sql = "update wbm_return_notice_record set
		wbm_return_notice_record.num = (select sum(num) from wbm_return_notice_detail_record where return_notice_code = :return_notice_code),
		wbm_return_notice_record.money = (select sum(money) from wbm_return_notice_detail_record where return_notice_code = :return_notice_code)
		where wbm_return_notice_record.return_notice_code = :return_notice_code ";
        $res = $this->query($sql, array(':return_notice_code' => $return_notice_code));
        return $res;
    }

    /**
     * 主单据数据回写
     */
    public function mainWriteBackfinish($return_notice_code) {
        //回写数量和金额
        $sql = "update wbm_return_notice_record set
		wbm_return_notice_record.finish_num = (select sum(finish_num) from wbm_return_notice_detail_record where return_notice_code = :return_notice_code)
		where wbm_return_notice_record.return_notice_code = :return_notice_code ";
        $res = $this->query($sql, array(':return_notice_code' => $return_notice_code));
        return $res;
    }
    function api_return_notice_detail_get($param){
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
        $select = "r1.goods_code,r1.spec1_code,r1.spec2_code,r1.sku,r1.sell_price,r1.price,r1.money,r1.num,r1.finish_num ";
        $sql_values = array();
        $sql_join = "";
        $sql_main = " from wbm_return_notice_detail_record r1 {$sql_join} where 1";
        foreach ($arr_deal as $key => $val) {
                if ($key != 'page' && $key != 'page_size') {
                    if($key == 'record_code'){
                        $sql_values[":{$key}"] = $val;
                        $sql_main .= " AND r1.return_notice_code =:{$key}";
                    }                    
                }
            }
        $sql_main .= ' group by return_notice_record_detail_id ';
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
}
