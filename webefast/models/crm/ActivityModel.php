<?php

require_model('tb/TbModel');

class ActivityModel extends TbModel {

    function get_table() {
        return 'crm_activity';
    }

    //按条件查询
    function get_by_page($filter) {
        $group = "";
        $sql_main = "FROM {$this->table} rl LEFT JOIN base_shop rr ON rl.shop_code = rr.shop_code ";
        if ($filter['ctl_type'] == 'export' || (isset($filter['goods_code']) && $filter['goods_code'] != '')) {
            $sql_main .=" INNER JOIN crm_goods cg on rl.activity_code = cg.activity_code WHERE 1";
        } else {
            $sql_main .=" WHERE 1";
        }
        $sql_values = array();
        //活动/代码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (rl.activity_code LIKE :code_name or rl.activity_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . "%";
        }
        //活动时间
        if (isset($filter['start_time']) && $filter['start_time'] != '') {
            $sql_main .= " AND rl.start_time >= :start_time ";
            $sql_values[':start_time'] = $filter['start_time'];
        }
        if (isset($filter['end_time']) && $filter['end_time'] != '') {
            $sql_main .= " AND rl.end_time <= :end_time ";
            $sql_values[':end_time'] = $filter['end_time'];
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_main .= " AND rl.shop_code = :shop_code ";
            $sql_values[':shop_code'] = $filter['shop_code'];
        }
        //商品编码条码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_sku = "select sku from goods_sku where barcode = :goods_code or goods_code = :goods_code";
            $sql_sku_values[':goods_code'] = $filter['goods_code'];
            $goods_sku = $this->db->get_all($sql_sku, $sql_sku_values);
            if (!empty($goods_sku)) {
                $sku_val = $this->arr_to_in_sql_value($goods_sku, 'sku', $sql_values);
                if (!empty($sku_val)) {
                    $sql_main .= " AND cg.sku in ({$sku_val})";
                } else {
                    $sql_main .= " AND cg.sku = :sku ";
                    $sql_values[':sku'] = '';
                }
            } else {
                $sql_comob_sku = "select sku from goods_combo_barcode where barcode = :goods_code or goods_code = :goods_code";
                $sql_comob_sku_values[':goods_code'] = $filter['goods_code'];
                $goods_comob_sku = $this->db->get_all($sql_comob_sku, $sql_comob_sku_values);
                $comob_sku_val = $this->arr_to_in_sql_value($goods_comob_sku, 'sku', $sql_values);
                if (!empty($comob_sku_val)) {
                    $sql_main .= " AND cg.sku in ({$comob_sku_val})";
                } else {
                    $sql_main .= " AND cg.sku = :sku ";
                    $sql_values[':sku'] = '';
                }
            }
        }

        $select = 'rl.*';

        if ($filter['ctl_type'] == 'export') {
            $select = 'rl.activity_code,rl.activity_name,rl.start_time,rl.end_time,rl.shop_code,
               rr.shop_name,
               cg.sku,
               cg.update_num,
               cg.inv_num,
               cg.sync_type,
               cg.goods_from_id,
               cg.sell_num';
        } else {
            $group .=" group by rl.activity_code";
        }
        $sql_main .=" order by rl.start_time desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, $group);
        
        if (!empty($data['data'])) {
            $del = load_model('sys/PrivilegeModel')->check_priv('crm/activity/delete');
            $copy = load_model('sys/PrivilegeModel')->check_priv('crm/activity/copy_activity');
        }
        foreach ($data['data'] as &$val) {
            $val['is_del'] = $del == 0 ? 0 : 1;
            $val['is_copy'] = $copy == 0 ? 0 : 1;
            
            $shop_code = explode(',', $val['shop_code']);
            $shop_code_list = "'" . implode("','", $shop_code) . "'";
            $sql = "SELECT shop_name FROM base_shop WHERE shop_code in({$shop_code_list});";
            $shop_name_arr = $this->db->get_col($sql);
            $val['shop_name'] = !empty($shop_name_arr) ? implode(',', $shop_name_arr) : '';

            $sku_info = $this->get_sku_info($val['sku']);
            $val['goods_code'] = $sku_info['goods_code'];
            $val['goods_name'] = $sku_info['goods_name'];
            $val['sku_code'] = $sku_info['barcode'];
            $val['goods_spec'] = "规格1：" . $sku_info['spec1_name'] . "," . "规格2：" . $sku_info['spec2_name'];
            if (isset($val['stock_lock_record']) && $val['stock_lock_record'] != '') {
                $stock = load_model('stm/StockLockRecordModel')->get_by_code($val['stock_lock_record']);
                $val['stock_lock_record_id'] = $stock['data']['stock_lock_record_id'];
            }
            if ($filter['ctl_type'] == 'export') {
                $val['goods_type'] = $val['sync_type'] == 1 ? '套餐' : '普通';
                if ($val['sync_type'] == 1) {
                    $inv_num = $this->get_inv_combo($val['sku'], $val['shop_code']);
                    $val['result_inv'] = $inv_num - ($val['update_num'] - $val['sell_num']);
                } else {
                    $inv_num = $this->get_effec_num_common($val['shop_code'], $val['sku']);
                    $val['result_inv'] = $inv_num - ($val['update_num'] - $val['sell_num']);
                }
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_inv_combo($sku, $shop_code) {
        $store_sql = "select send_store_code from base_shop where shop_code= :shop";
        $ret = $this->db->get_row($store_sql, array(':shop' => $shop_code));
        $data = $ret['send_store_code'];
        $sql_values = array();
        $sql = "select sku,num from goods_combo_diy where p_sku='{$sku}'";
        $sku_data = $this->db->get_all($sql);
        $sku_c_arr = array();
        foreach ($sku_data as $val) {
            $sku_c_arr[$val['sku']] = $val['num'];
        }
        $sku_list = array_keys($sku_c_arr);

        $list = $this->arr_to_in_sql_value($sku_list, 'sku', $sql_values);
        $sql = "select stock_num,lock_num ,sku from goods_inv where sku in ({$list}) and store_code='{$data}'";
        $data_sum = $this->db->get_all($sql, $sql_values);
        if (!empty($data_sum)) {
            $result = -1;
            foreach ($data_sum as $s_val) {
                $sku_num = $sku_c_arr[$s_val['sku']];
                $num = ceil(($s_val['stock_num'] - $s_val['lock_num']) / $sku_num);
                $num = $num < 0 ? 0 : $num;
                $result = $result == -1 ? $num : min($result, $num);
            }
        } else {
            $result = 0;
        }

        return $result;
    }

    function get_effec_num_common($shop, $goods, $mul = 1) {
        $num_effec = 0;
        $store_sql = "select send_store_code from base_shop where shop_code= :shop";
        $ret = $this->db->get_row($store_sql, array(':shop' => $shop));
        $data = $ret['send_store_code'];
        //print_r($data);
        /* 通过获取的仓库名去计算库存 */
        $sql_num = "select stock_num,lock_num,out_num from goods_inv where store_code= :val and sku= :goods ";
        $num = $this->db->get_row($sql_num, array(':val' => $data, ':goods' => $goods));
        $num_new = $num['stock_num'] - $num['lock_num'] - $num['out_num'];
        //print_r($num_new);
        $num_effec += $num_new;
        $num_effec = intval($num_effec / $mul);
        return $num_effec;
    }

    //停用启用
    function update_active($active, $id) {
        if (!in_array($active, array(0, 1, 5, 4))) {
            return $this->format_ret('error_params');
        }
        $sql = "select activity_code,shop_code from crm_activity where activity_id= {$id}";
        $row = $this->db->get_row($sql);
        if ($active == 5) {
            load_model('crm/ActivityGoodsModel')->update_children_is_sync($row['activity_code']);
            $ret_1 = $this->check_stock_lock($id);
            if ($ret_1['status'] != 1) {
                return $ret_1;
            }
            $ret = parent :: update(array('status' => 1), array('activity_id' => $id));
            parent :: update(array('is_first' => 1), array('activity_id' => $id));
            $log = array('activity_code' => $row['activity_code'], 'user_code' => CTX()->get_session('user_id'), 'user_name' => CTX()->get_session('user_code'), 'action_name' => '启用', 'action_time' => date('Y-m-d H:i:s', time()), 'action_desc' => "启用活动" . $row['activity_code']);
            $r = load_model('crm/ActivityLogModel')->insert($log);
            return $ret;
        }
        if ($active == 4) {
            load_model('crm/ActivityGoodsModel')->update_children_is_sync($row['activity_code']);
            $ret = parent :: update(array('status' => 1), array('activity_id' => $id));
            parent :: update(array('is_first' => 1), array('activity_id' => $id));
            $log = array('activity_code' => $row['activity_code'], 'user_code' => CTX()->get_session('user_id'), 'user_name' => CTX()->get_session('user_code'), 'action_name' => '启用', 'action_time' => date('Y-m-d H:i:s', time()), 'action_desc' => "启用活动" . $row['activity_code']);
            $r = load_model('crm/ActivityLogModel')->insert($log);
            return $ret;
        }
        if ($active == 1) {
            load_model('crm/ActivityGoodsModel')->inv_fresh($row);
            //取消库存检查
//            $ret_check = load_model('crm/ActivityGoodsModel')->check_inv($row['activity_code']);
//       
//            if($ret_check['status']<0){
//                return $ret_check;
//            }
            $ret_check_null = load_model('crm/ActivityGoodsModel')->check_inv_is_null($row['activity_code']);
            if ($ret_check_null['status'] < 0) {
                return $ret_check_null;
            }
            $ret_1 = $this->check_stock_lock($id);
            if ($ret_1['status'] != 1) {
                return $ret_1;
            }
        }
        load_model('crm/ActivityGoodsModel')->update_children_is_sync($row['activity_code']);

        $ret = parent :: update(array('status' => $active), array('activity_id' => $id));
        $action_name = $active == 1 ? "启用" : "停用";
        $log = array('activity_code' => $row['activity_code'], 'user_code' => CTX()->get_session('user_id'), 'user_name' => CTX()->get_session('user_code'), 'action_name' => $action_name, 'action_time' => date('Y-m-d H:i:s', time()), 'action_desc' => $action_name . "活动" . $row['activity_code']);
        $r = load_model('crm/ActivityLogModel')->insert($log);
        if ($active == 1) {
            parent :: update(array('is_first' => 1), array('activity_id' => $id));
            $ret = $ret_1;
        }
        return $ret;
    }

    function check_stock_lock($id) {

        $sql = "select stock_lock_record from crm_activity where activity_id={$id}";
        $record = $this->db->get_row($sql);
        if (empty($record['stock_lock_record'])) {
            $ret = $this->create_stock_lock($id);
            return $ret;
        } else {
            $ret = $this->update_stock_lock($record['stock_lock_record'], $id);
            return $ret;
        }
    }

    private function add_opt_log($id, $status, $action_name, $action_note = '') {
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $status, 'action_name' => $action_name, 'action_note' => $action_note, 'module' => "stock_lock_record", 'pid' => $id);
        $ret = load_model('pur/PurStmLogModel')->insert($log);
    }

    function create_stock_lock($id) {
        $this->begin_trans();
        $data['record_code'] = load_model('stm/StockLockRecordModel')->create_fast_bill_sn();
        $sql = "select r1.shop_code,r2.send_store_code,r1.activity_code from crm_activity r1 inner join base_shop r2 on r1.shop_code=r2.shop_code where r1.activity_id=:id";
        $result = $this->db->get_row($sql, array(':id' => $id));
        $data['store_code'] = $result['send_store_code'];
        $data['shop_code'] = $result['shop_code'];
        $data['record_time'] = date('Y-m-d');
        $data['lock_obj'] = 1;
        $check_set_inv = load_model('op/InvSyncModel')->check_inv_sync_policy(array('shop_code' => $result['shop_code'], 'store_code' => $result['send_store_code']));
        if ($check_set_inv['status'] != 1) {
            $this->rollback();
            return $check_set_inv;
        }
        $sync_code = $check_set_inv['data'];
        $ret = load_model('stm/StockLockRecordModel')->insert($data);
        $pid = $this->db->get_value("select stock_lock_record_id from stm_stock_lock_record where record_code=:code", array(':code' => $data['record_code']));
        $this->add_opt_log($pid, '未锁定', '添加锁定单', "由活动{$result['activity_code']}创建锁定单");
        $log = array('activity_code' => $result['activity_code'], 'user_code' => CTX()->get_session('user_id'), 'user_name' => CTX()->get_session('user_code'), 'action_name' => '锁定单', 'action_time' => date('Y-m-d H:i:s', time()), 'action_desc' => "创建锁定单");
        $r = load_model('crm/ActivityLogModel')->insert($log);
        if ($ret['status'] == 1) {
            $ret = parent::update(array('stock_lock_record' => $data['record_code']), array('activity_id' => $id));
            $ret = $this->insert_stock_lock_detail($id, $data['record_code'], $sync_code);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
            $log = array('activity_code' => $result['activity_code'], 'user_code' => CTX()->get_session('user_id'), 'user_name' => CTX()->get_session('user_code'), 'action_name' => '锁定单', 'action_time' => date('Y-m-d H:i:s', time()), 'action_desc' => "锁定锁定单");
            $r = load_model('crm/ActivityLogModel')->insert($log);
            $this->commit();
            return $ret;
        } else {
            $this->rollback();
            return $ret;
        }
    }

    function insert_stock_lock_detail($id, $record, $sync_code = '') {
        $sql = "select r3.activity_code,sum(r3.p_sync_num) as update_num,r3.sku,r3.p_sku from crm_activity r1 inner join crm_goods r2 on r1.activity_code=r2.activity_code inner join crm_goods_children r3 on r2.sku=r3.p_sku and r1.activity_code=r3.activity_code where r3.inv_num>r3.lock_num and r1.activity_id={$id} group by r1.activity_code,r3.sku";
        $data = $this->db->get_all($sql);
        //$sql = "select r2.update_num,r2.sku from crm_activity r1 inner join crm_goods r2 on r1.activity_code=r2.activity_code where r1.activity_id={$id} and sync_type=0";
        //$data_1 = $this->db->get_all($sql);
        // $data = array_merge($data,$data_1);
        $sku_list = array();
        foreach ($data as $key => $val) {
            $key_arr = array('barcode', 'goods_name', 'goods_code', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'price', 'sell_price');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
            $data[$key] = array_merge($data[$key], $sku_info);
            if (in_array($val['sku'], $sku_list) == true) {
                $num_list[$val['sku']] += $val['update_num'];
                unset($data[$key]);
                continue;
            } else {
                $sku_list[] = $val['sku'];
                $num_list[$val['sku']] = $val['update_num'];
            }
        }
        foreach ($data as $key => $val) {
            $data[$key]['num'] = $num_list[$val['sku']];
        }
        $sql = "select stock_lock_record_id,store_code from stm_stock_lock_record where record_code='{$record}'";
        $pid = $this->db->get_row($sql);
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid['stock_lock_record_id'], $pid['store_code'], 'stm_stock_lock', $data);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $ret = load_model('stm/StockLockRecordDetailModel')->add_detail_action($pid['stock_lock_record_id'], $data);
        $params['id'] = $pid['stock_lock_record_id'];
        $params['lock_sync_mode'] = 1;
        $params['sync_code'] = $sync_code;
        foreach ($data as $key => $val) {
            $this->add_opt_log($pid['stock_lock_record_id'], '未锁定', '添加锁定商品', "由活动{$val['activity_code']}同步商品{$val['barcode']}锁定数量{$val['num']}");
        }
        $ret = load_model('stm/StockLockRecordModel')->record_lock($params);
        return $ret;
    }

    function update_stock_lock($record, $id) {
        $sql = "select order_status from stm_stock_lock_record where record_code=:record";
        $data = $this->db->get_row($sql, array(':record' => $record));
        if ($data['order_status'] == 1) {
            return $this->format_ret(1, '', '已存在相应的库存锁定单');
        } else {
            return $this->format_ret(-1, '', '请先锁定相应的库存锁定单');
        }
    }

    //生成单据号
    function create_fast_bill_sn() {
        $sql = "select activity_id from crm_activity order by activity_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['activity_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "HD" . date("Ymd") . add_zero($djh, 3);
        return $jdh;
    }

    //添加基础数据
    function insert($data) {
        $ret = $this->is_exists($data['activity_code']);

        if (!empty($ret['data'])) {
            return $this->format_ret('-1', '', '此code存在');
        }
        if (empty($data['shop_code'])) {
            return $this->format_ret('-1', '', '店铺不能为空');
        }
        $sql = "select shop_code,activity_code from crm_activity where start_time<= :start_time and end_time>= :start_time";
        $ret = $this->db->get_all($sql, array(':start_time' => $data['start_time']));
        foreach ($ret as $val) {
            if ($data['shop_code'] == $val['shop_code']) {
                return $this->format_ret('-1', '', '相同时间段内不能设置相同店铺');
            }
        }
        $sql = "select shop_code from crm_activity where start_time<= :end_time and end_time>= :end_time";
        $ret1 = $this->db->get_all($sql, array(':start_time' => $data['start_time'], ':end_time' => $data['end_time']));
        foreach ($ret1 as $value) {
            if ($data['shop_code'] == $value['shop_code']) {
                return $this->format_ret('-1', '', '相同时间段内不能设置相同店铺');
            }
        }
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'activity_code' => $data['activity_code'], 'action_name' => '创建活动', 'action_time' => date('Y-m-d H:i:s', time()));
        $ret = load_model('crm/ActivityLogModel')->insert($log);
        return parent::insert($data);
    }

    //修改基础数据
    function update($data) {
        return parent::update($data, array('activity_code' => $data['activity_code']));
    }

    //查询对应code的数据
    public function is_exists($value, $field_name = 'activity_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    //根据id查询一条数据
    function get_by_id($id) {
        $data = $this->get_row(array('activity_id' => $id));
        //获取店铺名称
        $shop_code = explode(',', $data['data']['shop_code']);
        $shop_code = "'" . implode("','", $shop_code) . "'";
        $sql = "SELECT shop_name FROM base_shop WHERE shop_code in({$shop_code});";
        $shop_name_arr = $this->db->get_col($sql);
        $data['data']['shop_code_name'] = !empty($shop_name_arr) ? implode(',', $shop_name_arr) : '';
        return $data;
    }

    function delete($code) {
        $this->delete_exp('crm_activity', array('activity_code' => $code));
        $this->delete_exp('crm_goods', array('activity_code' => $code));
        $this->delete_exp('crm_goods_children', array('activity_code' => $code));
        $this->delete_exp('crm_goods_log', array('activity_code' => $code));
        return $this->format_ret(1, '', '删除成功');
    }

    private function get_sku_info($sku) {
        $sql = "select bg.goods_name,gs.goods_code,gs.barcode,gs.spec1_name,gs.spec2_name from base_goods bg left join goods_sku gs on bg.goods_code = gs.goods_code where gs.sku=:sku ";
        $row = $this->db->get_row($sql, array(':sku' => $sku));

        if (empty($row)) {
            $sql = "select gc.goods_name,gcb.goods_code,gcb.barcode,bs1.spec1_name,bs2.spec2_name from goods_combo gc left join goods_combo_barcode gcb on gc.goods_code = gcb.goods_code left join base_spec1 bs1 ON gcb.spec1_code = bs1.spec1_code left join base_spec2 bs2 ON gcb.spec2_code = bs2.spec2_code where gcb.sku=:sku ";
            $row = $this->db->get_row($sql, array(':sku' => $sku));
        }
        return $row;
    }

    public function copy($code) {
        $new_code = $this->create_fast_bill_sn();
        $sql = "insert into crm_activity(`activity_code`,`activity_name`, `start_time`, `end_time`, `shop_code`, `status`, `event_desc`, `last_update`, `update_inv_time`,`is_first`) select '{$new_code}',`activity_name`,`start_time`,`end_time`,`shop_code`,`status`,`event_desc`,`last_update`,`update_inv_time`,0 from crm_activity where activity_code='{$code}'";
        $this->db->query($sql);
        $sql = "INSERT INTO `crm_goods`( `activity_code`, `shop_code`, `sku`, `sync_type`, `lastchanged`, `inv_num`, `update_num`, `lock_num`, `sell_num`, `goods_code`, `goods_from_id`) select '{$new_code}',`shop_code`, `sku`, `sync_type`, `lastchanged`, `inv_num`, `update_num`, `lock_num`, `sell_num`, `goods_code`, `goods_from_id` from crm_goods where activity_code='{$code}'";
        $this->db->query($sql);
        $sql = "INSERT INTO `crm_goods_children`(`activity_code`, `shop_code`, `sku`, `p_sku`, `lastchanged`, `inv_num`, `lock_num`, `p_sync_num`, `is_sync`, `activity_list`) select '{$new_code}',`shop_code`, `sku`, `p_sku`, `lastchanged`, `inv_num`, `lock_num`, `p_sync_num`, `is_sync`, `activity_list` from crm_goods_children where activity_code='{$code}'";
        $this->db->query($sql);
        $log = array('activity_code' => $new_code, 'user_code' => CTX()->get_session('user_id'), 'user_name' => CTX()->get_session('user_code'), 'action_name' => '复制', 'action_time' => date('Y-m-d H:i:s', time()), 'action_desc' => "有活动{$code}复制生成");
        $r = load_model('crm/ActivityLogModel')->insert($log);
        return $this->format_ret(1, '', '复制成功');
    }

}
