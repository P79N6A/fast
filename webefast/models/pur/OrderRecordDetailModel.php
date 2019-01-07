<?php

/**
 * 采购计划订单相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('pur');

class OrderRecordDetailModel extends TbModel {

    function get_table() {
        return 'pur_order_record_detail';
    }

    /**
     * 判断主单据是否存在
     */
    public function is_exists($value, $field_name = 'record_code') {
        $m = load_model('pur/OrderRecordModel');
        $ret = $m->get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 判断单据是否存在明细
     */
    public function is_exists_detail($value, $field_name = 'record_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    
    //根据record_code获取商品的个数
    public function get_num($filter){
        $res = count($this->db->get_all("SELECT order_record_detail_id FROM `pur_order_record_detail`  WHERE record_code = '{$filter['record_code']}'")); 
        return $res; 
    }
    
    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        //print_r($filter);
        $sql_join = "";
        $sql_main = "FROM pur_order_record rl
		INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
		INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		LEFT JOIN goods_sku r4 on r2.sku = r4.sku
		WHERE  1 ";


        $sql_values = array();

        // record_code查询
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r2.record_code = :record_code )";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            return;
        }
        //是否有差异款
        if (isset($filter['difference_models']) && $filter['difference_models'] != '') {
            if ($filter['difference_models'] == 1) {
                $sql_main .= " AND (r2.num != r2.finish_num )";
            } else {
                $sql_main .= " AND (r2.num = r2.finish_num )";
            }
        }
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name or r4.barcode LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        $select = 'r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,rl.is_check,rl.rebate as rl_rebate';
        //$sql_main .= "group by r2.sku";
        
        if (isset($filter['is_fenye']) && $filter['is_fenye'] == '1') {
            $filter['page_size'] = 1000;           
        }
        
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($data['data'] as $key => $value) {
            //$data['data'][$key]['refer_price'] = round($value['refer_price'],3);
            //$data['data'][$key]['price'] = round($value['price'],3);
            //$data['data'][$key]['rebate'] = round($value['rebate'],3);
            //$data['data'][$key]['money'] = round($value['money'],3);
            $data['data'][$key]['no_finish_num'] = $data['data'][$key]['num'] - $data['data'][$key]['finish_num'];
            $data['data'][$key]['difference_num'] = $data['data'][$key]['num'] - $data['data'][$key]['finish_num'];
            $data['data'][$key]['price1'] = $data['data'][$key]['price'] * $data['data'][$key]['rl_rebate'];
            if ($status['status'] != 1) {
                $data['data'][$key]['price1'] = '****';
                $data['data'][$key]['price'] = '****';
                $data['data'][$key]['money'] = '****';
            }
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
        $data = $this->db->get_all($sql,$sql_values);


        return $this->format_ret(1, $data);
    }

    function get_select_id($id_arr) {
        $id_str = implode(',', $id_arr);
        $sql = " select goods_code,spec1_code,spec2_code,sku,refer_price,price,rebate,money,num,finish_num,(num-finish_num) as no_finish_num,record_code
            from  pur_order_record_detail  where order_record_detail_id in($id_str)";
        $data = $this->db->get_all($sql);
        return $this->format_ret(1, $data);
    }

    /**
     * 根据主单据号明细信息
     * @param string $record_code 主单据编码
     * @return array 明细
     */
    function get_detail_by_code($record_code) {
        $sql = " SELECT goods_code,spec1_code,spec2_code,sku,refer_price,price,rebate,money,num,finish_num,(num-finish_num) as no_finish_num,record_code FROM  {$this->table} WHERE record_code=:record_code";
        $sql_valuse = array(':record_code' => $record_code);
        $data = $this->db->get_all($sql, $sql_valuse);
        return $this->format_ret(1, $data);
    }
    
    //根据pid获取明细数据
    function get_detail_by_pid ($pid) {
        $sql = "SELECT goods_code,sku,price,rebate,money,num FROM {$this->table} WHERE pid = :pid ";
        $data = $this->db->get_all($sql, array(':pid'=>$pid));
        return $data;
    }

    /**
     * 新增多条明细记录
     */
    public function add_detail_action($pid, $ary_details) {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'order_record_id');
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
                $ary_detail['pid'] = $pid;
                $ary_detail['record_code'] = $record['data']['record_code'];
                $ary_detail['rebate'] = $record['data']['rebate'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
                if (isset($ary_detail['sell_price']) || empty($ary_detail['price']) || $ary_detail['price'] <= 0 || !is_numeric($ary_detail['price'])) {
                    $ary_detail['money'] = $ary_detail['num'] * $ary_detail['purchase_price'] * $ary_detail['rebate'];
                    $ary_detail['price'] = $ary_detail['purchase_price'];
                } else {
                    $ary_detail['money'] = $ary_detail['price'] * $ary_detail['rebate'] * $ary_detail['num'];
                    $ary_detail['price'] = $ary_detail['price'];
                }
                //判断SKU是否已经存在
                $check = $this->is_detail_exists($pid, $ary_detail['sku']);
                if ($check) {
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
            return $this->format_ret(-1, array(), 'DATABASE_ERROR:' . $e->getMessage());
        }
    }

    /**
     * 根据pID删除行数据
     */
    function delete_pid($pid) {
        $result = parent::delete(array('pid' => $pid));
        return $result;
    }

    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id) {
        $detail = $this->get_row(array('order_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'order_record_id');
            if (isset($record['data']['is_check']) && 1 == $record['data']['is_check']) {
                return $this->format_ret(false, array(), 'ORDER_DELETE_ERROR_CHECK');
            }
        }
        $result = parent::delete(array('order_record_detail_id' => $id));
        $this->mainWriteBack($detail['data']['pid']);
        return $result;
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
        if ($ret['status'] == 1 && !empty($ret['data'])) {
            return true;
        } else {
            return false;
        }
    }

    public function edit_detail_action($pid, $data) {
        $record = $this->db->get_row("select * from pur_order_record where order_record_id =:pid", array(":pid" => $pid));
        //print_r(array('num'=>$data['num'],'money'=>$data['num']*$data['sell_price']*$data['rebate']));
        $price_status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $sql = "select num,money,price from  pur_order_record_detail  where pid='{$pid}' and sku = '{$data['sku']}'";
        $detail_data = $this->db->get_row($sql);
        if($price_status['status'] != -1){
//            $data['sell_price'] = sprintf('%.3f',$data['sell_price']);
            $data['sell_price'] = number_format($data['sell_price'], 3);
            $ret = parent::update(array('num' => $data['num'], 'price' => $data['sell_price'], 'money' => $data['num'] * $data['sell_price'] * $record['rebate']), array('pid' => $pid, 'sku' => $data['sku']));
        }else{
            $ret = parent::update(array('num' => $data['num'], 'price' => $detail_data['price'], 'money' => $data['num'] * $detail_data['price'] * $record['rebate']), array('pid' => $pid, 'sku' => $data['sku']));
        }
        $this->mainWriteBack($pid);
        return $ret;
    }

    /**
     * 回写完成数
     * @param   string  $record_code   主单据号
     * @param   string  $data
     */
    public function update_finish_num($record_code, $data) {
        $ret = load_model('pur/OrderRecordModel')->get_by_field('record_code', $record_code);
        foreach ($data as $ary_detail) {
            //$ret= parent:: update(array('finish_num' => $ary_detail['num']), array('record_code' => $record_code,'sku' => $ary_detail['sku']));
            $sql = "update pur_order_record_detail set finish_num = finish_num + {$ary_detail['num']} where record_code = :record_code and sku = :sku ";
            $res = $this->query($sql, array(':record_code' => $record_code, ':sku' => $ary_detail['sku']));
            if (isset($ret['data']['relation_code']) && $ret['data']['relation_code'] <> '') {
                //回写采购计划完成数
                $result = load_model('pur/PlannedRecordDetailModel')->update_finish_num($ret['data']['relation_code'], $ary_detail['sku'], $ary_detail['num']);
            }
        }
        //回写采购订单完成数金额
        if (isset($ret['data']['relation_code']) && $ret['data']['relation_code'] <> '') {
            load_model('pur/PlannedRecordDetailModel')->update_finish_money($ret['data']['relation_code']);
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
     * 主单据数据回写
     */
    public function mainWriteBack($record_id) {
        //回写数量和金额
        $sql = "update pur_order_record set
		pur_order_record.num = (select sum(num) from pur_order_record_detail where pid = :id),
		pur_order_record.money = (select sum(money) from pur_order_record_detail where pid = :id)
		where pur_order_record.order_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }

    /**
     * 修改扫描数量
     * @param $record_code
     * @param $num
     * @param $id
     * @return array
     */
    function update_scan_num($record_code, $num, $id) {
        //获取主单信息
        $ret = load_model('pur/OrderRecordModel')->get_row(array('record_code' => $record_code));
        $record = $ret['data'];
        if ($record['is_check'] != 0) {
            return $this->format_ret('-1', '', '单据状态异常！');
        }
        $sku = substr($id, 8);
        //获取明细信息
        $detail = $this->get_row(array('record_code' => $record_code, 'sku' => $sku));
        if (empty($detail['data'])) {
            return $this->format_ret(-1, '', '单据明细信息不存在');
        }
        $detail['data']['num'] = $num;
        $ret = parent::update(array('num' => $num, 'money' => $num * $detail['data']['price'] * $detail['data']['rebate']), array('record_code' => $record_code, 'sku' => $detail['data']['sku']));
        $this->mainWriteBack($record['order_record_id']);
        if ($ret['status'] == 1) {
            return $this->format_ret(1, '', '更新成功');
        } else {
            return $this->format_ret(-1, '', '扫描入库更新单据明细数量失败');
        }
    }

}
