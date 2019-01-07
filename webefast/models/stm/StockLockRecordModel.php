<?php

/**
 * 库存锁定管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('stm');
require_lib('util/oms_util', true);

class StockLockRecordModel extends TbModel {

    //单据状态
    public $order_status = array(
        0 => '未锁定',
        1 => '已锁定',
        2 => '已释放',
        3 => '已作废',
    );
    public $type_name = array(
        'wbm_notice' => '批发通知单',
        'sell_record' => '网络订单',
    );
    public $lock_obj = array(
        '0' => '无',
        '1' => '网络店铺',
    );

    function get_table() {
        return 'stm_stock_lock_record';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_values = array();
        $sql_join = " LEFT JOIN stm_stock_lock_record_detail r2 on rl.record_code = r2.record_code "
                . "LEFT JOIN goods_sku r3 on r2.sku = r3.sku";
        $sql_main = "FROM {$this->table} rl {$sql_join}  WHERE 1";
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        //下单时间
        if (isset($filter['is_add_time_start']) && $filter['is_add_time_start'] != '') {
            $sql_main .= " AND (rl.is_add_time >= :is_add_time_start )";
            $sql_values[':is_add_time_start'] = $filter['is_add_time_start'] . ' 00:00:00';
        }
        if (isset($filter['is_add_time_end']) && $filter['is_add_time_end'] != '') {
            $sql_main .= " AND (rl.is_add_time <= :is_add_time_end )";
            $sql_values[':is_add_time_end'] = $filter['is_add_time_end'] . ' 23:59:59';
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND r3.barcode LIKE :barcode";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND r3.goods_code LIKE :goods_code";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND rl.record_code=:record_code";
            $sql_values[':record_code'] = $filter['record_code'];
        }
        //单据状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND rl.order_status=:order_status";
            $sql_values[':order_status'] = $filter['status'];
        }
        if (isset($filter['is_relation']) && $filter['is_relation'] != '') {
            $sql_main .= " AND rl.is_relation=:is_relation";
            $sql_values[':is_relation'] = $filter['is_relation'];
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] !== '') {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ( " . $str . " ) ";
        }
        //备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND rl.remark LIKE :remark ";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
        }
        //锁定对象
        if (isset($filter['lock_obj']) && $filter['lock_obj'] != '') {
            $sql_main .= " AND rl.lock_obj=:lock_obj ";
            $sql_values[':lock_obj'] = $filter['lock_obj'];
        }
        //导出
        if ($filter['ctl_type'] == 'export') {
            $select = 'rl.*,r2.sku,r2.lock_num as detail_lock_num,r2.release_num as detail_release_num,r2.available_num as detail_available_num';
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, false);
            foreach ($data['data'] as $key => &$value) {
                $data['data'][$key]['status'] = $this->order_status[$value['order_status']];
                $data['data'][$key]['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
                //查询规格1/规格2
                $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'goods_code', 'goods_name', 'barcode');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
                $value = array_merge($value, $sku_info);
                $data['data'][$key]['lock_obj_name'] = $this->lock_obj[$value['lock_obj']];
                $data['data'][$key]['shop_name'] = '';
                if ($value['lock_obj'] == 1) {
                    $data['data'][$key]['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
                }
            }
            $ret_status = OP_SUCCESS;
            $ret_data = $data;
            return $this->format_ret($ret_status, $ret_data);
        }
        $select = 'rl.*';
        $sql_main .= " GROUP BY rl.record_code ORDER BY is_add_time desc,rl.lastchanged desc";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['status'] = $this->order_status[$value['order_status']];
            $data['data'][$key]['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $data['data'][$key]['lock_obj_name'] = $this->lock_obj[$value['lock_obj']];
            $data['data'][$key]['shop_name'] = '';
            if ($value['lock_obj'] == 1) {
                $data['data'][$key]['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('stock_lock_record_id' => $id));
        filter_fk_name($data['data'], array('store_code|store'));
        if ($data['status'] == 1) {
            $data['data']['status'] = $this->order_status[$data['data']['order_status']];
        }
        return $data;
    }

    function get_by_code($code) {
        $data = $this->get_row(array('record_code' => $code));
        filter_fk_name($data['data'], array('store_code|store'));
        if ($data['status'] == 1) {
            $data['data']['status'] = $this->order_status[$data['data']['order_status']];
        }
        return $data;
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
     * 添加新纪录
     */

    function insert($stock_lock) {
        $status = $this->valid($stock_lock);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->is_exists($stock_lock['record_code']);
        if (!empty($ret['data'])) {
            return $this->format_ret('-1', '', '订单号已存在！');
        }
        $stock_lock['is_add_time'] = date('Y-m-d H:i:s');
        $stock_lock['is_add_person'] = CTX()->get_session('user_code');
        $stock_lock['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $stock_lock['remark']);
        $stock_lock['order_time'] = $stock_lock['record_time'];
        return parent::insert($stock_lock);
    }

    public function is_exists($value, $field_name = 'record_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['record_code']) || !valid_input($data['record_code'], 'required')))
            return RECORD_ERROR_CODE;
        return 1;
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn($num = 0) {
        $sql = "select stock_lock_record_id  from {$this->table}   order by stock_lock_record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['stock_lock_record_id']) + 1;
        } else {
            $djh = 1;
        }
        $djh = $num + $djh;
        require_lib('comm_util', true);
        $jdh = "SD" . date("Ymd") . add_zero($djh);
        return $jdh;
    }

    /*     * 编辑主单
     * @param $data
     * @param $where
     * @return array
     */

    function edit_action($data, $where) {
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        if (!isset($where['stock_lock_record_id']) && !isset($where['record_code'])) {
            return $this->format_ret(false, array(), '修改时缺少主单据ID或code!');
        }
        $result = $this->get_row($where);
        if (1 != $result['status']) {
            return $this->format_ret(false, array(), '没找到单据!');
        }
        //更新主表数据
        return parent::update($data, $where);
    }

    /** 单据锁定
     * @param $params
     * @return array
     */
    function record_lock($params) {
        $id = $params['id'];
        $record = $this->get_row(array('stock_lock_record_id' => $id));
        if (empty($record['data'])) {
            return $this->format_ret(-1, '', '单据不存在');
        }
        $record = $record['data'];
        if ($record['order_status'] != 0) {
            return $this->format_ret('-1', '', '单据状态异常！');
        }
        $record_detail = load_model('stm/StockLockRecordDetailModel')->get_all(array('pid' => $id));
        if ($record_detail['status'] != 1) {
            return $this->format_ret('-1', '', '请添加锁定明细！');
        }

        $sql = "select * from b2b_lof_datail where order_code=:order_code  AND  order_type=:order_type ";
        $ret_lof_details['data'] = $this->db->get_all($sql, array(
            ':order_code' => $record['record_code'],
            ':order_type' => 'stm_stock_lock',
        ));

        $old_lof_details = $ret_lof_details;

        require_model('prm/InvOpModel');
        $this->begin_trans();
        $invobj = new InvOpModel($record['record_code'], 'stm_stock_lock', $record['store_code'], 1, $ret_lof_details['data']);
        $ret = $invobj->adjust();
        //可用库存不足
        $sku_info = array();
        if ($ret['status'] == -10) {
            foreach ($ret['data'] as $value) {
                if ($value['lock_num'] == 0) {
                    $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $value['sku']));
                    $sku_info[] = array($barcode . "\t" => "系统可用库存不足");
                }
            }
            if (!empty($sku_info)) {
                $this->rollback();
                $msg = load_model('api/WeipinhuijitPickModel')->create_fail_file($sku_info);
                return $this->format_ret(-1, '', '商品条码在锁定单' . $record['record_code'] . '中库存不足' . $msg);
            }
            $this->rollback();
            return $ret;
        }
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        foreach ($old_lof_details['data'] as $detail_lof) {
            if ($record['order_status'] == 2) {
                $data = array(
                    'num' => $detail_lof['init_num'],
                    'fill_num' => 0,
                );
                $ret = $this->update_exp('b2b_lof_datail', $data, array('id' => $detail_lof['id']));
            }
            //回写明细
            load_model('stm/StockLockRecordDetailModel')->new_mainWriteBackDetail($record['record_code'], $detail_lof['sku']);
        }
        //更新单据状态为锁定
        $lock_person = CTX()->get_session('user_code');
        $lock_time = date('Y-m-d H:i:s');
        $ret = $this->update(array('lock_person' => $lock_person, 'lock_time' => $lock_time, 'order_status' => 1), array('stock_lock_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret('-1', '', '更新状态失败！');
        }
        //回写主单
        load_model('stm/StockLockRecordDetailModel')->new_mainWriteBack($record['record_code']);

        //更新库存同步策略
        if ($params['lock_sync_mode'] == 1) {
            $record['sync_code'] = $params['sync_code'];
            $ret_sync = load_model('op/InvSyncRatioModel')->lock_record_update_goods_retio($record, $record_detail['data'], 'lock');
            if ($ret_sync['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '更新商品同步比例失败');
            }
            $this->update(array('sync_code' => $params['sync_code']), array('record_code' => $record['record_code']));
            $this->add_opt_log($id, '已锁定', '锁定订单', "修改库存同步策略：{$params['sync_code']}，将锁定商品同步比例设置为0");
        } else {
            $this->add_opt_log($id, '已锁定', '锁定订单');
        }

        $this->commit();
        return $this->format_ret('1', '', '锁定成功！');
    }

    private function add_opt_log($id, $status, $action_name, $action_note = '') {
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $status, 'action_name' => $action_name, 'action_note' => $action_note, 'module' => "stock_lock_record", 'pid' => $id);
        $ret = load_model('pur/PurStmLogModel')->insert($log);
    }

    /** 单据释放
     * @param $id
     * @return array
     */
    function record_unlock($params) {
        $id = $params['id'];
        $record = $this->get_row(array('stock_lock_record_id' => $id));
        $record = $record['data'];
        if ($record['order_status'] != 1) {
            return $this->format_ret('-1', '', '单据状态异常！');
        }
        $record_detail = load_model('stm/StockLockRecordDetailModel')->get_all(array('pid' => $id));
        if ($record_detail['status'] != 1) {
            return $this->format_ret('-1', '', '请添加明细！');
        }
        //$ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'stm_stock_lock');
        $sql = "select * from b2b_lof_datail where order_code=:order_code  AND  order_type=:order_type ";
        $ret_lof_details['data'] = $this->db->get_all($sql, array(
            ':order_code' => $record['record_code'],
            ':order_type' => 'stm_stock_lock',
        ));

        //过滤锁定数量是0的明细
        $new_lof_details = array();
        foreach ($ret_lof_details['data'] as $key => $value) {
            if ($value['num'] > 0 && $value['occupy_type'] == 1) {
                $new_lof_details[] = $value;
            }
        }
        require_model('prm/InvOpModel');
        $this->begin_trans();
        if (!empty($new_lof_details)) {
            $invobj = new InvOpModel($record['record_code'], 'stm_stock_lock', $record['store_code'], 0, $new_lof_details);
            $ret = $invobj->adjust();
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
        }
        foreach ($ret_lof_details['data'] as $detail) {
            $data = array(
                'fill_num' => $detail['init_num'],
                'num' => 0,
            );
            $ret = $this->update_exp('b2b_lof_datail', $data, array('id' => $detail['id']));
            //回写明细
            load_model('stm/StockLockRecordDetailModel')->new_mainWriteBackDetail($record['record_code'], $detail['sku']);
        }
        //更新单据状态为释放
        $release_person = CTX()->get_session('user_code');
        $release_time = date('Y-m-d H:i:s');
        $ret = $this->update(array('release_person' => $release_person, 'release_time' => $release_time, 'order_status' => 2), array('stock_lock_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret('-1', '', '更新状态失败！');
        }
        load_model('stm/StockLockRecordDetailModel')->new_mainWriteBack($record['record_code']);

        //关联了库存同步策略，更新商品同步比例
        if (!empty($record['sync_code'])) {
            $record['sync_ratio'] = $params['sync_ratio'] / 100;
            $ret_sync = load_model('op/InvSyncRatioModel')->lock_record_update_goods_retio($record, $record_detail['data'], 'unlock');
            if ($ret_sync['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '更新商品同步比例失败');
            }
            $this->add_opt_log($id, '已释放', '释放订单', "修改库存同步策略：{$record['sync_code']}，将锁定商品同步比例设置为{$params['sync_ratio']}");
        } else {
            $this->add_opt_log($id, '已释放', '释放订单');
        }

        $this->commit();
        return $this->format_ret('1', '', '释放成功！');
    }

    /**
     * 导入明细
     * @param type $id
     * @param type $file
     * @return type
     */
    function imoprt_detail($id, $file, $import_from = '') {
        $ret = $this->get_row(array('stock_lock_record_id' => $id));
        if ($ret['data']['order_status'] != 0 && $ret['data']['order_status'] != 1) {
            return $this->format_ret('-1', '', '单据状态异常！');
        }
        $store_code = $ret['data']['store_code'];
        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = $lof_manage['lof_status'];
        $barcode_arr = $barcode_num = array();
        $err_num = 0; //导入错误数量
        if ($is_lof == 1) {
            $num = $this->read_csv_lof($file, $barcode_arr, $barcode_num);
        } else {
            //未开启批次导入库存方法
            $num = $this->read_csv_sku($file, $barcode_arr, $barcode_num);
            //条码去重
            $barcode_arr = array_unique($barcode_arr);
            //没有开启批次的 查询默认批次 如果批次表里 没有，在批次表里添加goods_lof
            $barcode_str = implode("','", $barcode_arr);
            $sql = "select g.barcode,g.sku,l.lof_no,l.production_date,l.type from goods_lof l "
                    . "INNER JOIN goods_sku g ON g.sku=l.sku  where"
                    . " g.barcode in ('$barcode_str') group by g.barcode";
            $sku_data = $this->db->get_all($sql);
            $sql_moren = "select lof_no,production_date from goods_lof  where type=1";
            $moren = $this->db->get_row($sql_moren);
            $lof_data_new = array();
            foreach ($sku_data as $lof_data) {
                $lof_data_new[$lof_data['barcode']]['production_date'] = $lof_data['production_date'];
                $lof_data_new[$lof_data['barcode']]['lof_no'] = $lof_data['lof_no'];
                $lof_data_new[$lof_data['barcode']]['sku'] = $lof_data['sku'];
            }
            $barcode_all = $barcode_num;
            //获取错误的行数
            $barcode_err = array();
            foreach ($barcode_all as $key => $val) {
                $k = ltrim(strstr($key, '_'), '_');
                if (empty($k)) {
                    $barcode_err[] = strstr($key, '_', true) + 1;
                }
                $barcode_num1[$k] = $val;
            }
            $new_barcode_num = $barcode_num1;
            $barcode_num = array();
            foreach ($barcode_arr as $barcode) {
                if (array_key_exists($barcode, $lof_data_new)) {
                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['num'] = $new_barcode_num[$barcode];
                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['lof_no'] = $lof_data_new[$barcode]['lof_no'];
                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['production_date'] = $lof_data_new[$barcode]['production_date'];
                } else {
                    $barcode_num[$barcode][$moren['lof_no']]['num'] = $new_barcode_num[$barcode];
                    $barcode_num[$barcode][$moren['lof_no']]['lof_no'] = $moren['lof_no'];
                    $barcode_num[$barcode][$moren['lof_no']]['production_date'] = $moren['production_date'];
                }
            }
        }
        if (!empty($barcode_num) && !empty($barcode_arr)) {
            foreach ($barcode_num as $key => $value) {
                if (empty($key)) {
                    unset($barcode_num[$key]);
                }
            }
            //$all_num = count($barcode_arr);var_dump($barcode_arr);die;
            $sql_values = array();
            $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.sku,g.price,g.purchase_price,g.sell_price  from
                    goods_sku b
                    inner join  base_goods g ON g.goods_code = b.goods_code
                    where b.barcode in({$barcode_str}) GROUP BY b.barcode";
            $detail_data = $this->db->get_all($sql, $sql_values); //sell_price
            $detail_data_lof = array();
            $success_num = 0;
            foreach ($detail_data as $key => $val) {
                foreach ($barcode_num[$val['barcode']] as $k1 => $v1) {
                    if (preg_match("/^[A-Za-z]/", $v1['num'])) {
                        $error_msg[] = array($val['barcode'] . "\t" => '请输入数字');
                        $err_num ++;
                        unset($barcode_num[$val['barcode']]);
                    } else if (empty($v1['num']) || $v1['num'] <= 0) {
                        $error_msg[] = array($val['barcode'] . "\t" => '数量不能为空且必须大于0');
                        $err_num ++;
                        unset($barcode_num[$val['barcode']]);
                    } else {
                        $val['num'] = $v1['num'];
                        $val['lof_no'] = $v1['lof_no'];
                        $val['production_date'] = $v1['production_date'];
                        $detail_data_lof[] = $val;
                        unset($barcode_num[$val['barcode']]);
                        $success_num ++;
                    }
                }
            }
            if ($import_from == '已锁定') {
                //调整单明细添加
                $ret = load_model('stm/StockLockRecordDetailModel')->import_detail_action($id, $detail_data_lof, $store_code);
            } else {
                //批次档案维护
                $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $detail_data_lof);
                //单据批次添加
                $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'stm_stock_lock', $detail_data_lof);
                //调整单明细添加
                $ret = load_model('stm/StockLockRecordDetailModel')->add_detail_action($id, $detail_data_lof);
            }
            if ($ret['status'] == '1') {
                $record = $this->get_by_id($id);
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $record['data']['status'], 'action_name' => '导入增加明细', 'module' => "stock_lock_record", 'pid' => $id);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
            if ($ret['status'] == '-3') {
                if (!empty($ret['data'])) {
                    foreach ($ret['data'] as $val) {
                        $error_msg[] = array($val . "\t" => '条码在订单中已存在');
                        $err_num ++;
                        $success_num--;
                    }
                }
                if (!empty($ret['message'])) {
                    foreach ($ret['message'] as $k => $val) {
                        $error_msg[] = array($k . "\t" => $val);
                        $err_num ++;
                        $success_num--;
                    }
                }
                if ($success_num > 0) {
                    $record = $this->get_by_id($id);
                    //日志
                    $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $record['data']['status'], 'action_name' => '导入增加明细', 'module' => "stock_lock_record", 'pid' => $id);
                    $ret1 = load_model('pur/PurStmLogModel')->insert($log);
                }
            }
        }
        $ret['data'] = '';
        $ret['status'] = '1';
        if (!empty($barcode_num)) {
            $sku_error = array_keys($barcode_num);
            foreach ($sku_error as $err) {
                $error_msg[] = array($err . "\t" => '系统不存在该条码信息');
                $err_num ++;
            }
        }
        if (!empty($barcode_err)) {
            foreach ($barcode_err as $err) {
                $error_msg[] = array('第' . $err . '行' . "\t" => '系统不存在该条码信息');
                $err_num ++;
            }
        }
//        $success_num = $all_num - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $ret['status'] = '-1';
            $message .= ',' . '失败数量:' . $err_num;
            $fail_top = array('商品条码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    /**
     * 开启批次读取
     * @param type $file
     * @param type $sku_arr
     * @param type $sku_num
     * @return int
     */
    function read_csv_lof($file, &$sku_arr, &$sku_num) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $row[0] = trim($row[0]);
                    $row[1] = trim($row[1]);
                    $row[2] = trim($row[2]);
                    $row[3] = trim($row[3]);
                    $sku_arr[] = $row[0];
                    $sku_num[$row[0]][$row[3]]['lof_no'] = $row[1];
                    $production_date = load_model('prm/GoodsLofModel')->get_lof_production_date($row[1], $row[0]);
                    $sku_num[$row[0]][$row[3]]['production_date'] = !empty($production_date) ? $production_date : $row[2];
                    $sku_num[$row[0]][$row[3]]['num'] = $row[3];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /**
     * 未开启批次读取
     * @param type $file
     * @param type $sku_arr
     * @param type $sku_num
     */
    function read_csv_sku($file, &$sku_arr, &$sku_num) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if ($row !== false) {
                    $k = $i . '_' . trim($row[0]);
                    $sku_arr[] = trim($row[0]);
                    $sku_num[$k] = trim($row[1]);
                }
            }
            $i++;
        }
        fclose($file);
    }

    /**
     * 下载错误信息
     * @param type $fail_top
     * @param type $error_msg
     * @return type
     */
    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $key = array_keys($val);
            $val_data = array($key[0], $val[$key[0]]);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("stock_adjust_record" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    /**
     * 追加锁定库存
     * @param type $params
     */
    function lock_add_inv($params) {
        $ret = $this->check_inv_num($params['add_inv_num']);
        if ($ret['status'] != 1) {
            return $ret;
        }
        //判断系统库存是否满足锁定要求
        if ($params['add_inv_num'] > $params['inv_available_mum']) {
            return $this->format_ret('-1', '', '输入追加数量超过系统可用追加数！');
        }
        $log_num = $params['add_inv_num'];
        $detail = load_model('stm/StockLockRecordDetailModel')->get_row(array('stock_lock_record_detail_id' => $params['stock_lock_record_detail_id']));
        $detail_data = $detail['data'];
        $record = load_model('stm/StockLockRecordModel')->get_row(array('record_code' => $detail_data['record_code']));
        if ($record['data']['order_status'] != 1) {
            return $this->format_ret('-1', '', '单据状态异常！');
        }
        $this->begin_trans();
        //   $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($detail_data['pid'], 'stm_stock_lock');

        $sql = "select * from b2b_lof_datail where order_code=:order_code  AND  order_type=:order_type AND sku=:sku";
        $ret_lof_details['data'] = $this->db->get_all($sql, array(
            ':order_code' => $record['data']['record_code'],
            ':order_type' => 'stm_stock_lock',
            ':sku' => $detail_data['sku'],
        ));

        $is_lock = ($params['add_inv_num'] > 0) ? 7 : 0; //判断锁定还是释放
        $new_lof_detial = array();
        foreach ($ret_lof_details['data'] as $key => $lof_detail) {
            if (($lof_detail['sku'] == $detail_data['sku'])) {
                $old_lof_details = $ret_lof_details['data'][$key];
                //释放数不能超过锁定数
                if ($is_lock == 0) {
                    $params['add_inv_num'] = abs($params['add_inv_num']);
                    if ($params['add_inv_num'] > $ret_lof_details['data'][$key]['num']) {
                        return $this->format_ret('-1', '', '释放数量超过已锁定数量！');
                    }
                }
                if ($is_lock == 7) {
                    $ret_lof_details['data'][$key]['occupy_type'] = 0;
                }
                $ret_lof_details['data'][$key]['num'] = $params['add_inv_num'];
                $new_lof_detial[] = $ret_lof_details['data'][$key];
            } else {
                continue;
            }
        }
        require_model('prm/InvOpModel');
        $invobj = new InvOpModel($detail_data['record_code'], 'stm_stock_lock', $record['data']['store_code'], $is_lock, $new_lof_detial);
        $ret = $invobj->adjust();
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        if ($is_lock == 7) {
            //锁定
            $update = array(
                'num' => $old_lof_details['num'] + $params['add_inv_num'],
                'init_num' => $old_lof_details['init_num'] + $params['add_inv_num'],
            );
        } else {
            //释放
            $update = array(
                'num' => $old_lof_details['num'] - $params['add_inv_num'],
                'fill_num' => $old_lof_details['fill_num'] + $params['add_inv_num'],
                'occupy_type' => 1
            );
        }
        $ret = $this->update_exp('b2b_lof_datail', $update, array('id' => $old_lof_details['id']));
        //回写明细
        load_model('stm/StockLockRecordDetailModel')->new_mainWriteBackDetail($record['data']['record_code'], $old_lof_details['sku']);
        //回写主单
        load_model('stm/StockLockRecordDetailModel')->new_mainWriteBack($record['data']['record_code']);
        //日志
        $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $old_lof_details['sku']));
        $log_meg = $barcode . '追加数量为:' . $log_num;
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '已锁定', 'action_note' => $log_meg, 'action_name' => '追加锁定', 'module' => "stock_lock_record", 'pid' => $record['data']['stock_lock_record_id']);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        $this->commit();
        return $this->format_ret(1, '', '追加成功！');
    }

    /**
     * 开启批次追加
     * @param type $params
     * @return type
     */
    function lock_add_inv_lof($params) {
        $ret = $this->check_inv_num($params['add_inv_num']);
        if ($ret['status'] != 1) {
            return $ret;
        }
        //判断系统库存是否满足锁定要求
        if ($params['add_inv_num'] > $params['inv_available_mum']) {
            return $this->format_ret('-1', '', '输入追加数量超过系统可用追加数！');
        }
        $log_num = $params['add_inv_num'];
        $b2b_detail = load_model('stm/GoodsInvLofRecordModel')->get_row(array('id' => $params['id']));
        $detail_data = $b2b_detail['data'];
        $old_lof_detail = $detail_data;
        $record = $this->get_by_code($detail_data['order_code']);
        if ($record['data']['order_status'] != 1) {
            return $this->format_ret('-1', '', '单据状态异常！');
        }
        $is_lock = ($params['add_inv_num'] > 0) ? 7 : 0; //判断锁定还是释放
        $new_lof_detial = array();
        //释放数不能超过锁定数
        if ($is_lock == 0) {
            $params['add_inv_num'] = abs($params['add_inv_num']);
            if ($params['add_inv_num'] > $detail_data['num']) {
                return $this->format_ret('-1', '', '释放数量超过已锁定数量！');
            }
        }
        if ($is_lock == 7) {
            $detail_data['occupy_type'] = 0;
        }
        $detail_data['num'] = $params['add_inv_num'];
        $new_lof_detial[] = $detail_data;
        $this->begin_trans();
        require_model('prm/InvOpModel');
        $invobj = new InvOpModel($record['data']['record_code'], 'stm_stock_lock', $record['data']['store_code'], $is_lock, $new_lof_detial);
        $ret = $invobj->adjust();
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        if ($is_lock == 7) {
            //锁定
            $update = array(
                'num' => $old_lof_detail['num'] + $params['add_inv_num'],
                'init_num' => $old_lof_detail['init_num'] + $params['add_inv_num'],
            );
            $ret = $this->update_exp('b2b_lof_datail', $update, array('id' => $old_lof_detail['id']));
        } else {
            //释放
            $update = array(
                'num' => $old_lof_detail['num'] - $params['add_inv_num'],
                'fill_num' => $old_lof_detail['fill_num'] + $params['add_inv_num'],
                'occupy_type' => 1
            );
            $ret = $this->update_exp('b2b_lof_datail', $update, array('id' => $old_lof_detail['id']));
        }
        //回写明细
        load_model('stm/StockLockRecordDetailModel')->new_mainWriteBackDetail($record['data']['record_code'], $old_lof_detail['sku']);
        //回写主单
        load_model('stm/StockLockRecordDetailModel')->new_mainWriteBack($record['data']['record_code']);
        $this->commit();
        //日志
        $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $old_lof_detail['sku']));
        $log_meg = $barcode . '追加数量为:' . $log_num;
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $record['data']['status'], 'action_note' => $log_meg, 'action_name' => '追加锁定', 'module' => "stock_lock_record", 'pid' => $record['data']['stock_lock_record_id']);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        return $this->format_ret(1, '', '追加成功！');
    }

    function check_inv_num($add_num) {
        if (empty($add_num)) {
            return $this->format_ret('-1', '', '请输入整数!');
        }
        if (!preg_match("/^\-?[1-9]{1}[0-9]*$|^[0]{1}$/", $add_num)) {
            return $this->format_ret('-1', '', '请输入整数!');
        }
        return $this->format_ret('1', '', '');
    }

    /**
     * 主单删除
     * @param type $id
     */
    function delete_main_action($id) {
        $record = $this->get_by_id($id);
        if ($record['status'] != 1) {
            return $this->format_ret('-1', '', '单据不存在！');
        }
        if ($record['data']['order_status'] != 0) {
            return $this->format_ret('-1', '', '单据状态不允许删除！');
        }
        $this->begin_trans();
        try {
            //删除主单 
            $ret = $this->delete(array('stock_lock_record_id' => $id));
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除主单失败!');
            }
            $ret = $this->delete_exp('stm_stock_lock_record_detail', array('pid' => $id));
            if (!$ret) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除明细失败!');
            }
            $ret = $this->delete_exp('b2b_lof_datail', array('order_code' => $record['data']['record_code']));
            if (!$ret) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除批次明细失败!');
            }
            $this->commit();
            return $this->format_ret(1, '', '删除成功!');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除失败:' . $e->getMessage());
        }
    }

    /**
     * 获取占用锁定单库存的单据
     * @param type $filter
     */
    function get_relation_by_page($filter) {
        $sql_values = array();
        $sql_main = " FROM stm_stock_lock_relation_record r1 WHERE 1 ";
        //锁定单号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND r1.record_code= :record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            $sql_main .= " AND 1=2 ";
        }
        //关联单据状态
        if (isset($filter['inv_status']) && $filter['inv_status'] != '') {
            $sql_main .= " AND r1.inv_status= :inv_status ";
            $sql_values[':inv_status'] = $filter['inv_status'];
        }
        $select = "r1.*";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['type_name'] = $this->type_name[$value['relation_type']];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
//                $val = iconv('gbk', 'utf-8', $val); 中文转码后变false
//                $val = mb_convert_encoding($val,'utf-8','gbk'); 中文转码后变乱码
                $val = str_replace('"', '', $val);
            }
        }
    }

    /**
     * 批量追加，是释放
     * @param $id
     * @param $file
     * @return array
     */
    function imoprt_add_inv($id, $file) {
        $record = $this->get_row(array('stock_lock_record_id' => $id));
        if ($record['data']['order_status'] != 1) {
            return $this->format_ret('-1', '', '单据状态异常！');
        }
        $error_msg = array();
        $barcode_arr = $barcode_num = array();
        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = $lof_manage['lof_status'];
        if ($is_lof == 1) {
            $this->add_inv_read_csv_sku_lof($file, $barcode_arr, $barcode_num);
        } else {
            $this->add_inv_read_csv_sku($file, $barcode_arr, $barcode_num);
            foreach ($barcode_num as &$info) {
                $info['lof_no'] = 'default_lof';
            }
        }
        //总数
        $all_num = count($barcode_num);
        //将条码转成sku
        $sql_values = array();
        $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
        $sql = "SELECT barcode,sku FROM goods_sku WHERE barcode IN({$barcode_str})";
        $barcode_info = $this->db->get_all($sql, $sql_values);
        $conver_barcode = load_model('util/ViewUtilModel')->get_map_arr($barcode_info, 'barcode');
        //查询批次数据
        $sql_values = array();
        $sql = "SELECT * FROM b2b_lof_datail WHERE order_code=:order_code AND order_type=:order_type";
        $sql_values[':order_code'] = $record['data']['record_code'];
        $sql_values[':order_type'] = 'stm_stock_lock';
        $b2b_lof_detail = $this->db->get_all($sql, $sql_values);
        //判断系统库存
        $sku_arr = array_column($b2b_lof_detail, 'sku');
        $sku_inv = load_model('prm/InvModel')->get_inv_by_sku($record['data']['store_code'], $sku_arr, 0);
        $sku_inv = load_model('api/WeipinhuijitPickModel')->trans_arr_key($sku_inv['data'], 'sku');
        $lof_detail_arr = load_model('util/ViewUtilModel')->get_map_arr($b2b_lof_detail, 'sku,lof_no');
        //追加锁定数据
        $lock_record = array();
        //释放数据
        $release_record = array();
        $old_detail = array();
        foreach ($barcode_num as $key => $value) {
            $barcode = $value['barcode'];
            $sku = isset($conver_barcode[$barcode]) ? $conver_barcode[$barcode]['sku'] : '';
            $sku_lof = $sku . ',' . $value['lof_no'];
            if (empty($sku)) {
                $error_msg[] = array($barcode . "\t" => '条码在系统中不存在！');
                continue;
            }
            if (!array_key_exists($sku_lof, $lof_detail_arr)) {
                $error_msg[] = array($barcode . "\t" => '条码在锁定单中不存在！');
                continue;
            }
            if (empty($value['num']) || $value['num'] == 0) {
                $error_msg[] = array($barcode . "\t" => '请输入正整数或负整数！');
                continue;
            }
            if ($value['num'] > 0) {//锁定
                //判断库存
                if ($sku_inv[$sku]['available_num'] < $value['num']) {
                    $error_msg[] = array($barcode . "\t" => '商品条码库存不足！');
                    continue;
                }
                $old_detail[$sku_lof]['num'] = $lof_detail_arr[$sku_lof]['num'];
                $lof_detail_arr[$sku_lof]['num'] = $value['num'];
                $lof_detail_arr[$sku_lof]['occupy_type'] = 0;
                $lock_record[$sku_lof] = $lof_detail_arr[$sku_lof];
            } else {//释放
                $num = abs($value['num']);
                if ($num > $lof_detail_arr[$sku_lof]['num']) {
                    $error_msg[] = array($barcode . "\t" => '释放数量超过已锁定数量！');
                    continue;
                }
                $old_detail[$sku_lof]['num'] = $lof_detail_arr[$sku_lof]['num'];
                $lof_detail_arr[$sku_lof]['num'] = $num;
                $release_record[$sku_lof] = $lof_detail_arr[$sku_lof];
            }
        }
        //锁定
        if (!empty($lock_record)) {
            $lock_sku_arr = array_column($lock_record, 'sku');
            $lock_sku_log = array();
            $this->begin_trans();
            require_model('prm/InvOpModel');
            $invobj = new InvOpModel($record['data']['record_code'], 'stm_stock_lock', $record['data']['store_code'], 7, $lock_record);
            $ret = $invobj->adjust();
            if ($ret['status'] != 1) {
                $this->rollback();
                foreach ($lock_sku_arr as $lock_sku) {
                    $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $lock_sku));
                    $error_msg[] = array($barcode . "\t" => '条码追加锁定失败！');
                }
            } else {
                //回写明细
                foreach ($lock_record as $new_sku_lof => $lock_deatil) {
                    $update = array(
                        'num' => $lock_deatil['num'] + $old_detail[$new_sku_lof]['num'],
                        'init_num' => $lock_deatil['init_num'] + $lock_deatil['num'],
                        'occupy_type' => 1
                    );
                    $this->update_exp('b2b_lof_datail', $update, array('id' => $lock_deatil['id']));
                    //回写批次明细
                    load_model('stm/StockLockRecordDetailModel')->new_mainWriteBackDetail($record['data']['record_code'], $lock_deatil['sku']);
                    //日志
                    $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $lock_deatil['sku']));
                    $lock_sku_log[] = "条码{$barcode}追加数量:{$lock_deatil['num']}";
                }
                //回写主单
                load_model('stm/StockLockRecordDetailModel')->new_mainWriteBack($record['data']['record_code']);
                //添加日志
                $log_meg = '批量追加库存：' . implode(',', $lock_sku_log);
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $this->order_status[$record['data']['order_status']], 'action_note' => $log_meg, 'action_name' => '批量追加库存', 'module' => "stock_lock_record", 'pid' => $record['data']['stock_lock_record_id']);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
                $this->commit();
            }
        }

        //释放
        if (!empty($release_record)) {
            $release_sku_arr = array_column($release_record, 'sku');
            $release_sku_log = array();
            $this->begin_trans();
            require_model('prm/InvOpModel');
            $invobj = new InvOpModel($record['data']['record_code'], 'stm_stock_lock', $record['data']['store_code'], 0, $release_record);
            $ret = $invobj->adjust();
            if ($ret['status'] != 1) {
                $this->rollback();
                foreach ($release_sku_arr as $release_sku) {
                    $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $release_sku));
                    $error_msg[] = array($barcode . "\t" => '条码释放库存失败！');
                }
            } else {
                //回写明细
                foreach ($release_record as $new_sku_lof => $release_deatil) {
                    $update = array(
                        'num' => $old_detail[$new_sku_lof]['num'] - $release_deatil['num'],
                        'fill_num' => $release_deatil['fill_num'] + $release_deatil['num'],
                        'occupy_type' => 1
                    );
                    $this->update_exp('b2b_lof_datail', $update, array('id' => $release_deatil['id']));
                    //回写批次明细
                    load_model('stm/StockLockRecordDetailModel')->new_mainWriteBackDetail($record['data']['record_code'], $release_deatil['sku']);
                    //日志
                    $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $release_deatil['sku']));
                    $release_sku_log[] = "条码{$barcode}释放数量:{$release_deatil['num']}";
                }
                //回写主单
                load_model('stm/StockLockRecordDetailModel')->new_mainWriteBack($record['data']['record_code']);
                //添加日志
                $log_meg = '批量释放库存：' . implode(',', $release_sku_log);
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $this->order_status[$record['data']['order_status']], 'action_note' => $log_meg, 'action_name' => '批量释放库存', 'module' => "stock_lock_record", 'pid' => $record['data']['stock_lock_record_id']);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
                $this->commit();
            }
        }
        $err_num = count($error_msg);
        $success_num = $all_num - $err_num;
        $message = '导入成功' . $success_num;
        $ret['status'] = '1';
        if ($err_num > 0 || !empty($error_msg)) {
            $ret['status'] = '-1';
            $message .= ',' . '失败数量:' . $err_num;
            $fail_top = array('商品条码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    function add_inv_read_csv_sku($file, &$sku_arr, &$sku_num) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sku_arr[] = trim($row[0]);
                    $sku_num[trim($row[0])]['num'] = trim($row[1]);
                    $sku_num[trim($row[0])]['barcode'] = trim($row[0]);
                }
            }
            $i++;
        }
        fclose($file);
    }

    function add_inv_read_csv_sku_lof($file, &$sku_arr, &$sku_num) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sku_arr[] = trim($row[0]);
                    $sku_num[$i]['barcode'] = trim($row[0]);
                    $sku_num[$i]['lof_no'] = trim($row[1]);
                    $sku_num[$i]['num'] = trim($row[2]);
                }
            }
            $i++;
        }
        fclose($file);
    }

    public function add_detail($param) {
        $this->begin_trans();
        $record = $this->get_by_id($param['record_id']);
        if ($record['data']['lock_obj'] != 0 && $record['data']['order_status'] != 0) {
            $ret = $this->format_ret(-1, '', '单据状态异常，无法添加明细');
        } else {
            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($param['record_id'], $param['detail']);
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($param['record_id'], $record['data']['store_code'], 'stm_stock_lock', $param['detail']);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
            //锁定单明细添加
            $ret = load_model('stm/StockLockRecordDetailModel')->add_detail_action($param['record_id'], $param['detail']);
            if ($ret['status'] == '1' && $record['data']['order_status'] == 0) {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $record['data']['status'], 'action_name' => '增加明细', 'module' => "stock_lock_record", 'pid' => $param['record_id']);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
                $this->commit();
            }else{
                $this->rollback();
            }
        }

        return $ret;
    }

}
