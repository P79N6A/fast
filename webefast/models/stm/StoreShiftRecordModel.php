<?php

/**
 * 库存调整管理相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');

class StoreShiftRecordModel extends TbModel {

    //移出状态
    public $shift_out_status = array(array('0' => '0,1', '1' => '全部'), array('0' => '0', '1' => '未出库'), array('0' => '1', '1' => '已出库'));
    //移入状态
    public $shift_in_status = array(array('0' => '0,1', '1' => '全部'), array('0' => '0', '1' => '未入库'), array('0' => '1', '1' => '已入库'));
    //店员调拨类型
    public $shop_shift_type = array(
        "general_to_shop_shop" => "总部调货到本店",
        "shop_to_general_shop" => "本店退货到总部",
        "next_to_shop_shop" => "邻店调货到本店",
        "shop_to_next_shop" => "本店调货到邻店",
    );
    //系统用户调拨类型
    public $sys_user_shift_type = array(
        "general_to_shop_user" => "总部调货到门店",
        "shop_to_general_user" => "门店退货到总部",
        "shop_to_shop_user" => "门店间调货",
    );
    private $is_api = 0; //是否是API调用

    function get_table() {
        return 'stm_store_shift_record';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_main = "FROM {$this->table} rl
    	            LEFT JOIN stm_store_shift_record_detail r2 on rl.record_code = r2.record_code
    	            LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code
                    LEFT JOIN goods_sku r4 on r4.sku = r2.sku  WHERE rl.shift_property=0";
        $sql_values = array();
        //商店仓库权限
        $shift_out_store_code = isset($filter['shift_out_store_code']) ? $filter['shift_out_store_code'] : null;
        $shift_in_store_code = isset($filter['shift_in_store_code']) ? $filter['shift_in_store_code'] : null;
        if (empty($shift_out_store_code) && empty($shift_in_store_code)) {
            $sql_shift_out_store_code = load_model('base/StoreModel')->get_sql_purview_store('rl.shift_out_store_code', $shift_out_store_code);
            $sql_shift_in_store_code = load_model('base/StoreModel')->get_sql_purview_store('rl.shift_in_store_code', $shift_in_store_code);
            if (!empty($sql_shift_out_store_code) && !empty($sql_shift_in_store_code)) {
                $sql_main .= substr_replace($sql_shift_out_store_code, ' (', 4, 1);
                $sql_main .= str_replace('and', 'or', $sql_shift_in_store_code) . ')';
            } else {
                $sql_main .= $sql_shift_out_store_code;
                $sql_main .= str_replace('and', 'or', $sql_shift_in_store_code);
            }
        } else if (!empty($shift_out_store_code) && empty($shift_in_store_code)) {
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.shift_out_store_code', $shift_out_store_code);
            $sql_main .= load_model('base/StoreModel')->get_sql_all_store('rl.shift_in_store_code');
        } else if (!empty($shift_in_store_code) && empty($shift_out_store_code)) {
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.shift_in_store_code', $shift_in_store_code);
            $sql_main .= load_model('base/StoreModel')->get_sql_all_store('rl.shift_out_store_code');
        } else {
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.shift_out_store_code', $shift_out_store_code);
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.shift_in_store_code', $shift_in_store_code);
        }
        //下单时间
        if (isset($filter['order_time_start']) && $filter['order_time_start'] != '') {
            $sql_main .= " AND (rl.record_time >= :order_time_start )";
            $sql_values[':order_time_start'] = $filter['order_time_start'];
        }
        if (isset($filter['order_time_end']) && $filter['order_time_end'] != '') {
            $sql_main .= " AND (rl.record_time <= :order_time_end )";
            $sql_values[':order_time_end'] = $filter['order_time_end'];
        }
        //入库时间
        if (isset($filter['shift_in_time_start']) && $filter['shift_in_time_start'] != '') {
            $sql_main .= " AND (rl.is_shift_in_time >= :shift_in_time_start )";
            $sql_values[':shift_in_time_start'] = $filter['shift_in_time_start'];
        }
        if (isset($filter['shift_in_time_end']) && $filter['shift_in_time_end'] != '') {
            $sql_main .= " AND (rl.is_shift_in_time <= :shift_in_time_end )";
            $sql_values[':shift_in_time_end'] = $filter['shift_in_time_end'];
        }
        //出库时间
        if (isset($filter['shift_out_time_start']) && $filter['shift_out_time_start'] != '') {
            $sql_main .= " AND (rl.is_shift_out_time >= :shift_out_time_start )";
            $sql_values[':shift_out_time_start'] = $filter['shift_out_time_start'];
        }
        if (isset($filter['shift_out_time_end']) && $filter['shift_out_time_end'] != '') {
            $sql_main .= " AND (rl.is_shift_out_time <= :shift_out_time_end )";
            $sql_values[':shift_out_time_end'] = $filter['shift_out_time_end'];
        }
        //出库状态
        if (isset($filter['is_shift_out']) && $filter['is_shift_out'] != '') {
            $state_arr = explode(',', $filter['is_shift_out']);
            if (!empty($state_arr)) {
                $sql_main .= " AND (";
                foreach ($state_arr as $key => $value) {
                    $param_state = 'is_shift_out' . $key;
                    if ($key == 0) {
                        $sql_main .= " is_shift_out = :{$param_state} ";
                    } else {
                        $sql_main .= " or is_shift_out = :{$param_state} ";
                    }

                    $sql_values[':' . $param_state] = $value;
                }
                $sql_main .= ")";
            }
        }
        //单据状态
        if (isset($filter['shift_status']) && $filter['shift_status'] != '') {
            if ($filter['shift_status'] == 0) {
                $sql_main .= " AND rl.is_sure = 0";
            } elseif ($filter['shift_status'] == 1) {
                $sql_main .= " AND rl.is_sure = 1 AND rl.is_shift_out = 0";
            } elseif ($filter['shift_status'] == 2) {
                $sql_main .= " AND rl.is_shift_out = 1 AND rl.is_shift_in = 0";
            } elseif ($filter['shift_status'] == 3) {
                $sql_main .= " AND rl.is_shift_out = 1 AND rl.is_shift_in = 1";
            }
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code or rl.init_code like :init_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
            $sql_values[':init_code'] = $filter['record_code'] . '%';
        }
        // 原单号
        if (isset($filter['init_code']) && $filter['init_code'] != '') {
            $sql_main .= " AND (rl.init_code LIKE :init_code )";
            $sql_values[':init_code'] = $filter['init_code'] . '%';
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND (r4.barcode LIKE :barcode )";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        //备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND rl.remark LIKE :remark";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
        }
        //明细
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf'])) {
            return $this->sell_record_search_csv($sql_main, $sql_values, $filter);
        }
        if (isset($filter['add_person']) && $filter['add_person'] != '') {
            $sql_main .= " AND (rl.is_add_person = :add_person )";
            $sql_values[':add_person'] = trim($filter['add_person']);
        }
        $select = 'rl.*';
        $sql_main .= " group by rl.record_code order by record_time desc,lastchanged desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        //进货价权限
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $filter['user_id']);
        foreach ($data['data'] as $key => &$value) {
            $is_wms_in = load_model('sys/ShopStoreModel')->is_wms_store($value['shift_in_store_code']);
            if ($is_wms_in !== FALSE) {
                $data['data'][$key]['is_wms_in'] = 1;
            } else {
                $data['data'][$key]['is_wms_in'] = 0;
            }
            $is_wms_out = load_model('sys/ShopStoreModel')->is_wms_store($value['shift_out_store_code']);
            if ($is_wms_out !== FALSE) {
                $data['data'][$key]['is_wms_out'] = 1;
            } else {
                $data['data'][$key]['is_wms_out'] = 0;
            }
            $value['difference_num'] = abs($value['out_num'] - $value['in_num']);
            if ($status['status'] != 1) {
                $data['data'][$key]['out_money'] = '****';
            }
            $value['is_same_outside_code'] = $this->is_same_outside_code($value['shift_in_store_code'], $value['shift_out_store_code']);
        }
        filter_fk_name($data['data'], array('shift_in_store_code|store', 'shift_out_store_code|store'));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //导出明细
    private function sell_record_search_csv($sql_main, $sql_values, $filter) {
        $select = "r2.shift_record_detail_id,rl.record_code,rl.init_code,rl.record_time,rl.shift_in_store_code,rl.shift_out_store_code,rl.is_shift_in_time,rl.is_shift_in,rl.is_shift_out,rl.is_sure,rl.is_shift_out_time,r3.goods_name,r2.goods_code,r2.spec1_code,r2.spec2_code,r4.barcode,r4.sku,r2.out_num,r2.in_num,r2.out_money,r2.in_money,rl.remark,rl.shift_in_time";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //进货价权限
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $filter['user_id']);
        foreach ($ret_data['data'] as $key => $value) {
            //查询仓库名称
            $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['shift_out_store_code'], 'store_name');
            $ret_data['data'][$key]['shift_out_store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
            $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['shift_in_store_code'], 'store_name');
            $ret_data['data'][$key]['shift_in_store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
            //查询规格1/规格2
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $ret_data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $ret_data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $ret_data['data'][$key]['goods_name'] = $sku_info['goods_name'];
            $ret_data['data'][$key]['barcode'] = $sku_info['barcode'];
            $ret_data['data'][$key]['ruku'] = $value['is_shift_in'] == 1 ? '是' : '否';
            $ret_data['data'][$key]['chuku'] = $value['is_shift_out'] == 1 ? '是' : '否';
            $ret_data['data'][$key]['queren'] = $value['is_sure'] == 1 ? '是' : '否';
            $ret_data['data'][$key]['detail_difference_num'] = abs($value['out_num'] - $value['in_num']);
            if ($status['status'] != 1) {
                $ret_data['data'][$key]['out_money'] = '****';
                $ret_data['data'][$key]['in_money'] = '****';
            }
        }
        return $this->format_ret(1, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('shift_record_id' => $id));
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

    function insert($stock_adjus) {
        $status = $this->valid($stock_adjus);

        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($stock_adjus['record_code']);

        if (!empty($ret['data']))
            return $this->format_ret('-1', '', 'RECORD_ERROR_UNIQUE_CODE1');
        $stock_adjus['is_add_time'] = date('Y-m-d H:i:s');
        $stock_adjus['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $stock_adjus['remark']);
        return parent::insert($stock_adjus);
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
     * 新增一条库存调整单记录
     * @param array $ary_main 主单据数组
     * @return array 返回新增结果
     */
    public function add_action($ary_main) {
        //校验参数
        if (!isset($ary_main['store_code']) || !valid_input($ary_main['store_code'], 'required')) {
            return RECORD_ERROR_STORE_CODE;
        }
        //插入主单据
        //生成调整单号
        if (!isset($ary_main['record_code']) && empty($ary_main['record_code'])) {
            require_lib('comm_util', true);
            $ary_main['record_code'] = $this->create_fast_bill_sn();
        }
        $ary_main['is_add_time'] = date('Y-m-d H:i:s');
        $ret = $this->insert($ary_main);
        //返回结果
        return $ret;
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn() {

        $sql = "select shift_record_id  from {$this->table}   order by shift_record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['shift_record_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "YC" . date("Ymd") . add_zero($djh);
        return $jdh;
    }

    /**
     * 入库
     * @param int $id
     * @return array
     */
    function shift_in($id) {
        //检查调整单状态是否为已验收
        $record = $this->get_row(array('shift_record_id' => $id));
        $details = load_model('stm/StoreShiftRecordDetailModel')->get_all(array('pid' => $id));

        if (isset($record['data']['is_shift_in']) && 1 == $record['data']['is_shift_in']) {
            return $this->format_ret(false, array(), '该单据已经入库!');
        }
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret(false, array(), '单据明细不能为空!');
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid_store($id, 'shift_in', $record['data']['shift_in_store_code']);
        //$ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id,'shift');
        if (empty($ret_lof_details['data'])) {
            return $this->format_ret(false, array(), '单据明细异常!');
        }
        $in_num = 0;
        $in_money = 0;
        foreach ($details['data'] as $val) {
            $in_num += $val['in_num'];
            $in_money += $val['in_money'];
        }

        $this->begin_trans();
        $ret = parent:: update(array('is_shift_in' => 1, 'in_num' => $in_num, 'in_money' => $in_money, 'is_shift_in_time' => date('Y-m-d'), 'shift_in_time' => date('Y-m-d')), array('shift_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }

        require_model('prm/InvOpModel');

        $invobj = new InvOpModel($record['data']['record_code'], 'shift_in', $record['data']['shift_in_store_code'], 3, $ret_lof_details['data']);
        $ret2 = $invobj->adjust();
        if ($ret2['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret2;
        }

//        if ($record['data']['out_num'] <> $record['data']['in_num']) {
        //$this->create_adjust_record($id);
//        }
        $scan_data = load_model('stm/StoreShiftRecordDetailModel')->get_list($id);
        $log_detail = '';
        foreach ($scan_data as $k => $val) {
            if (!empty($val['in_num'])) {
                $log_detail .= '扫描条码:' . $val['barcode'] . ",扫描入库数量:" . $val['in_num'] . ';';
            }
        }
        $user_id = $this->is_api == 1 ? '1' : CTX()->get_session('user_id');
        $user_code = $this->is_api == 1 ? 'admin' : CTX()->get_session('user_code');
        $log = array('user_id' => $user_id, 'user_code' => $user_code, 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '入库', 'action_name' => '扫描入库', 'module' => "store_shift_record", 'pid' => $id, 'action_note' => $log_detail);
        load_model('pur/PurStmLogModel')->insert($log);



        $this->commit(); //事务提交


        return $this->format_ret(1, array(), '入库成功!');
    }

    /**
     * 强制入库
     * @param int $id
     * @return array
     */
    function do_qz_shift_in($id, $record_code = '', $is_wms = 0, $is_wms_create = 0) {
        //检查调整单状态是否为已验收
        if (empty($record_code)) {
            $record = $this->get_row(array('shift_record_id' => $id));
        } else {
            $record = $this->get_row(array('record_code' => $record_code));
        }

        $details = load_model('stm/StoreShiftRecordDetailModel')->get_all(array('pid' => $id));
        if (isset($record['data']['is_shift_in']) && 1 == $record['data']['is_shift_in']) {
            return $this->format_ret(false, array(), '该单据已经入库!');
        }
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret(false, array(), '单据明细不能为空!');
        }
        //通知数，完成数
        $in_num = 0;
        foreach ($details['data'] as $k => $v) {
            $in_num += intval($v['in_num']);
        }
        if ($in_num > 0 && $is_wms == 0) {
            return $this->format_ret(false, array(), '不能强制入库,请扫描入库!');
        }
        $this->begin_trans();
        if ($is_wms == 0) {
            $ret = $this->create_b2bdetail_record($id, $record);
            if (!$ret) {
                return $this->format_ret("-1", '', 'insert_error');
            }
        }

        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid_store($id, 'shift_in', $record['data']['shift_in_store_code']);
        if (empty($ret_lof_details['data'])) {
            $this->rollback(); //事务回滚
            return $this->format_ret(false, array(), '单据明细异常!');
        }
        //更新主单信息
        $ret = parent:: update(array('is_shift_in' => 1, 'in_num' => $in_num, 'in_money' => $record['data']['out_money'], 'is_shift_in_time' => date('Y-m-d'), 'shift_in_time' => date('Y-m-d')), array('shift_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }

        require_model('prm/InvOpModel');
        $invobj = new InvOpModel($record['data']['record_code'], 'shift_in', $record['data']['shift_in_store_code'], 3, $ret_lof_details['data']);
        $ret2 = $invobj->adjust();
        if ($ret2['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret2;
        }
        //回写主单
        load_model('stm/StoreShiftRecordDetailModel')->mainWrite_in_Back($id);
        if ($is_wms_create === 0) {
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '入库', 'action_name' => '强制入库', 'module' => "store_shift_record", 'pid' => $id);
            load_model('pur/PurStmLogModel')->insert($log);
        }

        $this->commit(); //事务提交
        return $this->format_ret(1, array(), '强制入库成功!');
    }

    function create_b2bdetail_record($id, $record) {
        //sprint_r($record);
        $time = time();
        $sql = "insert into b2b_lof_datail (pid,order_code,order_type,goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,num,init_num,create_time)
    	select pid,order_code,'shift_in',goods_code,spec1_code,spec2_code,sku,'{$record['data']['shift_in_store_code']}',lof_no,production_date,num,init_num,{$time} as create_time  from b2b_lof_datail
    	where  pid ='{$id}' and order_type = 'shift_out' and store_code = '{$record['data']['shift_out_store_code']}'";
        $ret = $this->db->query($sql);
        $sql = "update stm_store_shift_record_detail set in_num = out_num, in_money=out_money where  pid ='{$id}' ";
        $ret = $this->db->query($sql);
        return $ret;
    }

    //确认/取消确认，锁定库存/释放锁定 针对移出仓
    function update_sure($active, $field, $id, $is_wms_create = 0) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }

        $record = $this->get_row(array('shift_record_id' => $id));
        $details = load_model('stm/StoreShiftRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        //对接wms，是否相同外部编码,现在改成不考虑正次品仓
        $same_outside_code = $this->is_same_outside_code($record['data']['shift_in_store_code'], $record['data']['shift_out_store_code']);

        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid_store($id, 'shift_out', $record['data']['shift_out_store_code']);
        if (empty($ret_lof_details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
        }

        //库存操作锁库存
        require_model('prm/InvOpModel');
        if ($active == 1) {
            $invobj = new InvOpModel($record['data']['record_code'], 'shift_out', $record['data']['shift_out_store_code'], 1, $ret_lof_details['data']);
            $this->begin_trans();
            $ret = $invobj->adjust();
            if ($ret['status'] == -10) {//锁定批次库存不足
                $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
                if (!empty($invobj->check_data['adjust_record_info']) && $lof_manage['lof_status'] == 0) {//关闭掉批次情况
                    $this->rollback();
                    //调整库存不足的sku
                    $ret = $invobj->adjust_lock_record($invobj->check_data['adjust_record_info']); //调整锁定批次
                    if ($ret['status'] != 1) {
                        //获取可用库存
                        $sku_arr = array_column($invobj->check_data['adjust_record_info'], 'sku');
                        $sku_inv = load_model('prm/InvModel')->get_inv_by_sku($record['data']['shift_out_store_code'], $sku_arr, 0);
                        $sku_inv = load_model('api/WeipinhuijitPickModel')->trans_arr_key($sku_inv['data'], 'sku');
                        foreach ($invobj->check_data['adjust_record_info'] as $value) {
                            $sku = $value['sku'];
                            $barcode = load_model('goods/SkuCModel')->get_barcode($sku);
                            $short_num = $value['num'] - $sku_inv[$sku]['available_num'];
                            $error_msg[] = array($barcode => $ret['message'] . "，缺货数:{$short_num}");
                        }
                        if (!empty($error_msg)) {
                            $fail_top = array('商品条码', '错误信息');
                            $message = $ret['message'];
                            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
//                            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
                            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
                            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
                        }
                        $ret['message'] = $is_wms_create === 0 ? $message : '锁定批次库存不足';
                        return $ret;
                    }
                    $this->begin_trans();
                    $ret = $invobj->adjust(); //重新提交
                }
            }
        } else {
            $invobj = new InvOpModel($record['data']['record_code'], 'shift_out', $record['data']['shift_out_store_code'], 0, $ret_lof_details['data']);
            $this->begin_trans();
            $ret = $invobj->adjust();
        }
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }


        $ret = parent:: update(array($field => $active), array('shift_record_id' => $id));

        if ($is_wms_create === 0 && $same_outside_code == 0) {
            if ($active == 1) {
                $ret = load_model('wms/WmsEntryModel')->add($record['data']['record_code'], 'shift_out', $record['data']['shift_out_store_code']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                } else {
                    $ret['status'] = 1;
                    $ret['message'] = '操作成功';
                }
            } else {
                $ret = load_model('wms/WmsEntryModel')->cancel($record['data']['record_code'], 'shift_out', $record['data']['shift_out_store_code']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                } else {
                    $ret['status'] = 1;
                    $ret['message'] = '操作成功';
                }
            }
        }

        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        $this->commit(); //事务提交
        return $ret;
    }

    //出库
    function shift_out($id, $record_code = '', $is_wms_create = 0, $force_negative_inv = 0) {
        //检查调整单状态是否为已出库
        if (empty($record_code)) {
            $record = $this->get_row(array('shift_record_id' => $id));
        } else {
            $record = $this->get_row(array('record_code' => $record_code));
        }


        $details = load_model('stm/StoreShiftRecordDetailModel')->get_all(array('pid' => $id));
        if (isset($record['data']['is_shift_out']) && 1 == $record['data']['is_shift_out']) {
            return $this->format_ret(false, array(), '单据已出库');
            //		return $this->format_ret('RETURN_RECORD_ERROR_STORE_OUT');
        }
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid_store($id, 'shift_out', $record['data']['shift_out_store_code']);
        if (empty($ret_lof_details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
        }
        //对接wms，是否相同外部编码
        $same_outside_code = $this->is_same_outside_code($record['data']['shift_in_store_code'], $record['data']['shift_out_store_code']);

        if ($is_wms_create === 0 && $same_outside_code == 0) {
            $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($record['data']['shift_out_store_code']);
            //判断wms 是否收发货
            $sql = "select wms_order_flow_end_flag from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
            $wms_order_flow_end_flag = ctx()->db->getOne($sql, array(':record_code' => $record['data']['record_code'], ':record_type' => 'shift_out'));
            if ($wms_system_code !== FALSE && $wms_order_flow_end_flag != 1) {
                $is_wms = 1;
                $store_arr = array('shift_out' => $record['data']['shift_out_store_code'], 'shift_in' => $record['data']['shift_in_store_code']);
                $store = load_model('wms/WmsBaseModel')->get_outside_code($store_arr, 'iwms');
                if (isset($store[$store_arr['shift_out']]) && isset($store[$store_arr['shift_in']]) && $store[$store_arr['shift_out']] == $store[$store_arr['shift_in']]) {
                    $is_wms = 0;
                }
                $store = load_model('wms/WmsBaseModel')->get_outside_code($store_arr, 'iwmscloud');
                if (isset($store[$store_arr['shift_out']]) && isset($store[$store_arr['shift_in']]) && $store[$store_arr['shift_out']] == $store[$store_arr['shift_in']]) {
                    $is_wms = 0;
                }
                if ($is_wms == 1) {
                    return $this->format_ret('-1', '', '对接外部仓储不能执行移出，自动处理移出');
                }
            }
        }
        $this->begin_trans();
        require_model('prm/InvOpModel');

        //扣减库存
        $invobj = new InvOpModel($record['data']['record_code'], 'shift_out', $record['data']['shift_out_store_code'], 2, $ret_lof_details['data']);
        if ($force_negative_inv == 1) {
            $invobj->force_negative_inv(); //强制允许负库存
        }
        $ret = $invobj->adjust();
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }

        $ret = parent:: update(array('is_shift_out' => 1, 'is_shift_out_time' => date('Y-m-d'), 'record_time' => date('Y-m-d')), array('shift_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        if ($is_wms_create === 0 && $same_outside_code == 0) {
            $ret = load_model('wms/WmsEntryModel')->add($record['data']['record_code'], 'shift_in', $record['data']['shift_in_store_code']);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            } else {
                $ret['status'] = 1;
            }
        }

        $this->commit();
        return $this->format_ret(1, array(), 'SUCCESS_STORE_OUT');
    }

    function scan_shift_out($id, $record_code = '', $is_wms_create = 0, $force_negative_inv = 0){
        //检查调整单状态是否为已出库
        if (empty($record_code)) {
            $record = $this->get_row(array('shift_record_id' => $id));
        } else {
            $record = $this->get_row(array('record_code' => $record_code));
        }


        $details = load_model('stm/StoreShiftRecordDetailModel')->get_all(array('pid' => $id));
        if (isset($record['data']['is_shift_out']) && 1 == $record['data']['is_shift_out']) {
            return $this->format_ret(false, array(), '单据已出库');
            //		return $this->format_ret('RETURN_RECORD_ERROR_STORE_OUT');
        }
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid_store($id, 'shift_out', $record['data']['shift_out_store_code']);
        if (empty($ret_lof_details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
        }
        //未扫描就出库，直接返回。
        $num=0;
        $out_money=0;
        foreach($details['data'] as $k=>$val){
            $num+=$val['scan_num'];
            $out_money+=$val['price'] * $val['rebate'] * $val['scan_num'];
            $new_detail[$val['sku']]=$val;
        }
        if($num==0){
            return $this->format_ret('-1', '', '单据未扫描，请扫描后出库');
        }
        foreach($ret_lof_details['data'] as $key=>&$vals){
            $vals['num']=$new_detail[$vals['sku']]['scan_num'];
            $vals['scan_lock_num']=$new_detail[$vals['sku']]['out_num'];
        }
        //对接wms，是否相同外部编码
        $same_outside_code = $this->is_same_outside_code($record['data']['shift_in_store_code'], $record['data']['shift_out_store_code']);

        if ($is_wms_create === 0 && $same_outside_code == 0) {
            $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($record['data']['shift_out_store_code']);
            //判断wms 是否收发货
            $sql = "select wms_order_flow_end_flag from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
            $wms_order_flow_end_flag = ctx()->db->getOne($sql, array(':record_code' => $record['data']['record_code'], ':record_type' => 'shift_out'));
            if ($wms_system_code !== FALSE && $wms_order_flow_end_flag != 1) {
                $is_wms = 1;
                $store_arr = array('shift_out' => $record['data']['shift_out_store_code'], 'shift_in' => $record['data']['shift_in_store_code']);
                $store = load_model('wms/WmsBaseModel')->get_outside_code($store_arr, 'iwms');
                if (isset($store[$store_arr['shift_out']]) && isset($store[$store_arr['shift_in']]) && $store[$store_arr['shift_out']] == $store[$store_arr['shift_in']]) {
                    $is_wms = 0;
                }
                $store = load_model('wms/WmsBaseModel')->get_outside_code($store_arr, 'iwmscloud');
                if (isset($store[$store_arr['shift_out']]) && isset($store[$store_arr['shift_in']]) && $store[$store_arr['shift_out']] == $store[$store_arr['shift_in']]) {
                    $is_wms = 0;
                }
                if ($is_wms == 1) {
                    return $this->format_ret('-1', '', '对接外部仓储不能执行移出，自动处理移出');
                }
            }
        }
        $this->begin_trans();


        require_model('prm/InvOpModel');

        //扣减库存
        $invobj = new InvOpModel($record['data']['record_code'], 'shift_out', $record['data']['shift_out_store_code'], 2, $ret_lof_details['data']);
        if ($force_negative_inv == 1) {
            $invobj->force_negative_inv(); //强制允许负库存
        }
        $ret = $invobj->adjust();
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }

        foreach($details['data'] as $ks=>$v){
            if($v['scan_num']!='0'){
                $ret = load_model('stm/GoodsInvLofRecordModel')->update(array('num'=>$v['scan_num'],'init_num'=>$v['scan_num']),array('pid'=>$v['pid'],'sku'=>$v['sku'],'order_type'=>'shift_out'));
                if ($ret['status'] < 1) {
                    $this->rollback();
                    return $ret;
                }
            }else{
                $ret = load_model('stm/GoodsInvLofRecordModel')->delete_pid($v['pid'],$v['sku'],'shift_out');
                if ($ret['status'] < 1) {
                    $this->rollback();
                    return $ret;
                }
            }

        }

        $ret = parent:: update(array('is_shift_out' => 1, 'out_num'=>$num,'out_money'=>$out_money,'is_shift_out_time' => date('Y-m-d'), 'record_time' => date('Y-m-d')), array('shift_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        foreach($details['data'] as $ks=>$v){
            if($v['scan_num']!='0'){
                $out_money=$v['price'] * $v['rebate'] * $v['scan_num'];
                $ret = load_model('stm/StoreShiftRecordDetailModel')->update(array('out_num'=>$v['scan_num'],'out_money'=>$out_money),array('pid'=>$v['pid'],'sku'=>$v['sku'],'record_code'=>$v['record_code']));
                if ($ret['status'] < 1) {
                    $this->rollback();
                    return $ret;
                }
            }else{
                $out_money=$v['price'] * $v['rebate'] * $v['scan_num'];
                $ret = load_model('stm/StoreShiftRecordDetailModel')->delete_for_scan($v['shift_record_detail_id']);
                if ($ret['status'] < 1) {
                    $this->rollback();
                    return $ret;
                }
            }

        }
        if ($is_wms_create === 0 && $same_outside_code == 0) {
            $ret = load_model('wms/WmsEntryModel')->add($record['data']['record_code'], 'shift_in', $record['data']['shift_in_store_code']);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            } else {
                $ret['status'] = 1;
            }
        }

        $this->commit();
        return $this->format_ret(1, array(), 'SUCCESS_STORE_OUT');
    }

    /**
     * 清除扫描记录
     */
    function clean_scan($pid, $record_code, $dj_type) {
        $sql = "select is_shift_in,is_shift_out from {$this->table} where record_code = :record_code";
        $ret = $this->db->get_row($sql, array(':record_code' => $record_code));
        if ($dj_type == 'scan_out' && $ret['is_shift_out'] == 1) {
            return $this->format_ret('-1', '', '单据已经出库，不能清除！');
        } elseif ($dj_type == 'scan_in' && $ret['is_shift_in'] == 1) {
            return $this->format_ret('-1', '', '单据已经入库，不能清除！');
        }
        $res = load_model('stm/StoreShiftRecordDetailModel')->clean_scan($pid, $record_code, $dj_type);
        return $res;
    }

    /**
     * 删除记录
     */
    function delete($return_record_id) {
        $sql = "select * from {$this->table} where shift_record_id = :return_record_id";
        $data = $this->db->get_row($sql, array(":return_record_id" => $return_record_id));
        if ($data['is_shift_out'] == 1) {
            return $this->format_ret('-1', array(), '单据已经出库，不能删除！');
        }
        if ($data['is_sure'] == 1) {
            return $this->format_ret('-1', array(), '单据已经确认，不能删除！');
        }
        $ret = parent::delete(array('shift_record_id' => $return_record_id));
        $this->db->create_mapper('stm_store_shift_record_detail')->delete(array('pid' => $return_record_id));
        //$this->db->create_mapper('b2b_lof_datail')->delete(array('pid'=>$return_record_id,'order_type'=>'shift'));
        $this->db->create_mapper('b2b_lof_datail')->delete(array('order_code' => $data['record_code']));
        //添加系统日志
        $module = '进销存'; //模块名称          
        $yw_code = $data['record_code'];  //业务编码
        $operate_type = '删除'; //操作类型                     
        $xq = '删除移仓单'; //操作日志                                          
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $xq);
        load_model('sys/OperateLogModel')->insert($log);
        return $ret;
    }

    /**
     * 编辑一条库存调整记录
     * @param array $data
     * @param array $where
     * @return array
     */
    public function edit_action($data, $where) {
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        $data['remark'] = str_replace(array(","), '，', $data['remark']);
        if (!isset($where['shift_record_id']) && !isset($where['record_code'])) {
            return $this->format_ret(false, array(), '修改时缺少主单据ID或code!');
        }
        $result = $this->get_row($where);
        if (1 != $result['status']) {
            return $this->format_ret(false, array(), '没找到单据!');
        }
        if (1 == $result['data']['is_sure']) {
            return $this->format_ret(false, array(), '单据已经确认,不能修改!');
        }
        //task#1403 移仓单，若仓库对接外部WMS，且外部WMS编码一致且类型不一致，允许添加移仓单，但不上传至中间表（调整良品/次品库存） FBB 2017.06.16
        $sql = "update b2b_lof_datail set store_code='{$data['shift_out_store_code']}' where pid='{$where['shift_record_id']}' and order_type='shift_out';";
        $this->db->query($sql);
        //更新主表数据
        return parent::update($data, $where);
    }

    /**
     *
     * 生成调整单
     * @param unknown_type $id
     */
    function create_adjust_record($id) {
        $ret = $this->get_row(array('shift_record_id' => $id));
        if ($ret['status'] < 1 || empty($ret['data'])) {
            return $ret;
        }
        $record_code = $ret['data']['record_code'];
        $stock_adjust['record_code'] = load_model('stm/StockAdjustRecordModel')->create_fast_bill_sn();
        $stock_adjust['relation_code'] = $record_code;
        $stock_adjust['store_code'] = $ret['data']['shift_out_store_code'];
        $stock_adjust['order_time'] = $ret['data']['is_shift_in_time']; //下单日期
        $stock_adjust['record_time'] = $ret['data']['is_shift_in_time']; //业务日期
        $stock_adjust['adjust_type'] = 802;
        $shift_out_store_code = $ret['data']['shift_out_store_code'];
        $shift_in_store_code = $ret['data']['shift_in_store_code'];
        $ret = load_model('stm/StockAdjustRecordModel')->insert($stock_adjust);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $stock_adjust_id = $ret['data'];
        //明细
        $sql = "insert into  stm_stock_adjust_record_detail(pid,record_code,goods_code,spec1_code,spec2_code,sku,num,refer_price,price,rebate,money)";
        $sql .= " select {$stock_adjust_id},'{$stock_adjust['record_code']}',p.goods_code,p.spec1_code,p.spec2_code,p.sku,(p.out_num-p.in_num) as c,p.refer_price,p.price,p.rebate,p.price*(p.out_num-p.in_num) from stm_store_shift_record_detail p
    	where  p.pid='{$id}' and p.out_num <> p.in_num ";

        $this->db->query($sql);

        //b2b
        $sql = "select *  from stm_stock_adjust_record_detail where pid = '{$stock_adjust_id}' ";
        $mingxi = $this->db->get_all($sql);

        foreach ($mingxi as $k => $v) {
            if (intval($v['num']) > 0) {

                $sql = "select *  from b2b_lof_datail where order_type = 'shift' and pid = '{$id}' and store_code = '{$shift_out_store_code}' and sku = '{$v['sku']}'  ";
                $pici = $this->db->get_row($sql);
            } else {

                $sql = "select *  from b2b_lof_datail where order_type = 'shift' and pid = '{$id}' and store_code = '{$shift_in_store_code}' and sku = '{$v['sku']}' ";
                $pici = $this->db->get_row($sql);
            }


            $ary_detail['pid'] = $stock_adjust_id;
            $ary_detail['order_code'] = $v['record_code'];
            $ary_detail['order_type'] = 'adjust';
            $ary_detail['goods_code'] = $v['goods_code'];
            $ary_detail['spec1_code'] = $v['spec1_code'];
            $ary_detail['spec2_code'] = $v['spec2_code'];
            $ary_detail['sku'] = $v['sku'];
            $ary_detail['store_code'] = $pici['store_code'];
            $ary_detail['lof_no'] = $pici['lof_no'];
            $ary_detail['production_date'] = $pici['production_date'];
            $ary_detail['num'] = abs($v['num']);
            $ret = load_model('stm/GoodsInvLofRecordModel')->insert($ary_detail);
        }


        return $this->format_ret(1, $stock_adjust_id); //调整
    }

    /**
     * 默认打印数据 new
     * @param $id
     * @return array
     * 发货单新版打印（更改打印配件）
     */
    public function print_data_default($request) {
        $id = $request['record_ids'];
        $r = array();
        $record = $this->get_by_id($id);
        $r['record'] = $record['data'];
        //todo:new_sku_cache
        $sku_array = array();
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        if ($ret_arr['lof_status'] == 1) {
            $r['detail'] = $this->get_print_info_with_lof_no($r['record']['record_code']);
        } else {
            $sql = "select d.record_code,d.goods_code,d.out_num,d.in_num,d.sku,d.price,d.out_money,d.in_money
                from stm_store_shift_record_detail d 
                where d.pid=:pid";
            $r['detail'] = $this->db->get_all($sql, array(':pid' => $id));
            foreach ($r['detail'] as $key => $detail) {//合并同一sku
                $detail['shelf_code'] = $this->get_shelf_code($detail['sku'], $r['record']['shift_out_store_code']);
                $key_arr = array('goods_name', 'goods_short_name', 'spec1_name', 'spec2_name', 'barcode', 'category_name');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
                $r['detail'][$key] = array_merge($detail, $sku_info);
                if (in_array($detail['sku'], $sku_array)) {
                    $exist_key = array_keys($sku_array, $detail['sku']);
                    $r['detail'][$exist_key[0]]['out_num'] += $detail['out_num'];
                    $r['detail'][$exist_key[0]]['in_num'] += $detail['in_num'];
                    // $r['detail'][$exist_key[0]]['avg_money'] += $detail['avg_money'];
                    unset($r['detail'][$key]);
                } else {
                    $sku_array[$key] = $detail['sku'];
                }
            }
        }

        $this->print_data_escape($r['record'], $r['detail']);
        //更新状态
        $this->update_print_status($id);
        return $r;
    }

    function print_data_escape(&$record, &$detail) {
        $record['shift_out_store_code'] = $this->db->get_value("select store_name from base_store where store_code = '{$record['shift_out_store_code']}'");
        $record['shift_in_store_code'] = $this->db->get_value("select store_name from base_store where store_code = '{$record['shift_in_store_code']}'");
        $record['print_time'] = date('Y-m-d H:i:s');
        $record['print_user'] = CTx()->get_session('user_name');
    }

    //更新打印状态
    public function update_print_status($shift_record_id) {
        $sql = "SELECT is_print_record FROM stm_store_shift_record WHERE shift_record_id = :shift_record_id ";
        $data = $this->db->get_row($sql, array(':shift_record_id' => $shift_record_id));
        if (empty($data)) {
            return array('status' => '-1', 'message' => '移仓单不存在');
        }
        //添加日志
        $this->add_print_log($shift_record_id);
        //更新状态
        $this->update_exp('stm_store_shift_record', array('is_print_record' => 1), array('shift_record_id' => $shift_record_id));
    }

    //打印日志记录
    public function add_print_log($shift_record_id) {
        $sql = "SELECT is_shift_in,is_shift_out FROM stm_store_shift_record WHERE shift_record_id = :shift_record_id  ";
        $d = $this->db->get_row($sql, array(':shift_record_id' => $shift_record_id));
        if (!$d['is_shift_out']) {
            $finish_status = '未出库';
        } else if ($d['is_shift_out'] && !$d['is_shift_in']) {
            $finish_status = '出库';
        } else if ($d['is_shift_in']) {
            $finish_status = '入库';
        }
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $finish_status, 'action_name' => '打印', 'module' => "store_shift_record", 'pid' => $shift_record_id);
        load_model('pur/PurStmLogModel')->insert($log);
    }

    //检查是否打印
    public function check_is_print($shift_record_id) {
        $sql = "SELECT is_print_record FROM stm_store_shift_record WHERE shift_record_id = :shift_record_id ";
        $data = $this->db->get_row($sql, array(':shift_record_id' => $shift_record_id));
        $ret = $data['is_print_record'] == 1 ? $this->format_ret(-1, '', '重复打印移仓单，是否继续打印？') : $this->format_ret(1, '', '');
        return $ret;
    }

    private function get_print_info_with_lof_no($record_code) {
        $sql = "select  rl.*, rr.purchase_price as price from b2b_lof_datail rl "
                . "left join base_goods rr on rl.goods_code = rr.goods_code "
                . " where rl.order_code = :order_code and rl.order_type = 'shift_out'";
        $lof_details = $this->db->get_all($sql, array(":order_code" => $record_code));
        if (empty($lof_details)) {
            return array();
        }

        foreach ($lof_details as $key => $detail) {
            $detail['shelf_code'] = $this->get_shelf_code_lof($detail['sku'], $detail['lof_no'], $detail['store_code']);
            $detail['out_num'] = $detail['num'];
            $detail['out_money'] = $detail['num'] * $detail['price'];
            $detail['in_money'] = $detail['num'] * $detail['price'];
            $key_arr = array('goods_name', 'goods_short_name', 'spec1_name', 'spec2_name', 'barcode', 'category_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);

            $lof_details[$key] = array_merge($detail, $sku_info);
        }
        return $lof_details;
    }

    /**
     * 读取库位代码
     * @param $record_code
     * @param $sku
     * @return string
     */
    public function get_shelf_code($sku, $store_code) {
        $sql = "select a.shelf_code from goods_shelf a
        where a.store_code = :store_code and a.sku = :sku";
        $l = $this->db->get_all($sql, array(':store_code' => $store_code, ':sku' => $sku));
        $arr = array();
        foreach ($l as $_v) {
            $arr[] = $_v['shelf_code'];
        }
        return implode(',', $arr);
    }

    private function get_shelf_code_lof($sku, $lof_no, $store_code) {
        $sql = "select shelf_code from goods_shelf where store_code = :store_code and sku = :sku and batch_number =:batch_number";
        $l = $this->db->get_all($sql, array(':store_code' => $store_code, ':sku' => $sku, ':batch_number' => $lof_no));
        $arr = array();
        foreach ($l as $_v) {
            $arr[] = $_v['shelf_code'];
        }
        return implode(',', $arr);
    }

    /**
     * 导入商品
     * @param type $id
     * @param type $file
     * @param type $is_lof
     * @return type
     */
    function import_detail($id, $file, $is_lof = 0) {
        $ret = $this->get_row(array('shift_record_id' => $id));
        $store_code = $ret['data']['shift_out_store_code'];
        $is_sure = $ret['data']['is_sure'];
        $is_shift_out = $ret['data']['is_shift_out'];

        $barcode_arr = $barcode_num = array();
        $error_msg = '';
        $err_num = 0;

        if ($is_lof == '1') {
            //开启批次
            $num = $this->read_csv_lof($file, $barcode_arr, $barcode_num);
        } else {
            //未开批次
            $this->read_csv_no_lof($file, $barcode_arr, $barcode_num);
        }

        $sku_count = count($barcode_arr);

        if (!empty($barcode_num) && !empty($barcode_arr)) {
            $lof_no_arr = array();
            foreach ($barcode_num as $key => $val) {
                $lof_no = array_column($val, 'lof_no');
                $lof_no_arr = array_merge($lof_no_arr, $lof_no);
            }

            $lof_no_arr = array_unique($lof_no_arr);
            $lof_no_str = deal_array_with_quote($lof_no_arr);
            $barcode_str = deal_array_with_quote($barcode_arr);
            $sql = "SELECT b.goods_code,b.sku,b.barcode,l.lof_no,l.production_date,g.purchase_price FROM goods_sku b
                    INNER JOIN base_goods g ON g.goods_code = b.goods_code INNER JOIN goods_lof l ON b.sku=l.sku
                    WHERE b.barcode IN({$barcode_str}) AND l.lof_no in({$lof_no_str})  GROUP BY b.barcode";
            $detail_data = $this->db->get_all($sql);

            $detail_data_lof = array();
            foreach ($detail_data as $key => $val) {
                foreach ($barcode_num[$val['barcode']] as $k1 => $v1) {
                    if (intval($v1['num']) > 0 && is_int($v1['num'] + 0)) {
                        if ($is_sure == 0)
                            $val['num'] = $v1['num'];
                        if ($is_sure == 1 && $is_shift_out == 0)
                            $val['num'] = $v1['num'] == 0 ? $val['in_num'] : $v1['num'];
                        $val['lof_no'] = $v1['lof_no'];
                        $val['production_date'] = $v1['production_date'];
                        //$val['pici_lof'] = '1';
                        $detail_data_lof[] = $val;
                        //unset($barcode_num[$val['barcode']]);
                    } else {
                        $error_msg[] = array($val['barcode'] => '商品数量不能为空或小于1或小数');
                        $err_num ++;
                        //unset($barcode_num[$val['barcode']]);
                    }
                    foreach ($barcode_arr as $keykey => $value) {
                        if ($value == $val['barcode']) {
                            unset($barcode_arr[$keykey]);
                        }
                    }
                }
            }

            $type = '';
            if ($is_sure == 0) {
                $type = 'shift_out';
            }
            if ($is_sure == 1 && $is_shift_out == 0) {
                $type = 'shift_in';
            }
            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $detail_data_lof, $type);
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, $type, $detail_data_lof);
            if ($ret['status'] < 1) {
                return $ret;
            }
            //入库单明细添加
            $ret = load_model('stm/StoreShiftRecordDetailModel')->add_detail_action($id, $detail_data_lof);

            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '导入明细', 'module' => "store_shift_record", 'pid' => $id);
                load_model('pur/PurStmLogModel')->insert($log);
            }
            $ret['data'] = '';
        }

        if (!empty($barcode_arr)) {
            foreach ($barcode_arr as $err) {
                $error_msg[] = array($err => '系统不存在该条码信息');
                $err_num ++;
            }
        }
        $success_num = $sku_count - $err_num;
        $message = '导入成功' . $success_num . '条信息';
        if ($err_num > 0 || !empty($error_msg)) {
            $ret['status'] = -1;
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

    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $key = array_keys($val);
            $val_data = array("\t" . $key[0], "\t" . $val[$key[0]]);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("wbm_store_shift_record_import" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function read_csv_no_lof($file, &$barcode_arr, &$barcode_num) {
        $num = $this->read_csv_sku($file, $barcode_arr, $barcode_num);
        //没有开启批次去批次表查询批次，如果没有则增加默认批次
        $barcode_str = deal_array_with_quote($barcode_arr);
        $sql = "SELECT b.barcode,b.sku,l.lof_no,l.production_date,l.type FROM goods_sku b
                    LEFT JOIN goods_lof l ON b.sku=l.sku  WHERE
                    b.barcode IN({$barcode_str}) GROUP BY b.sku";
        $sku_data = $this->db->get_all($sql);
        $moren = load_model('prm/GoodsLofModel')->get_sys_lof();
        $moren = $moren['data'];

        $lof_data_new = array();
        $sys_lof_data = array();
        foreach ($sku_data as $lof_data) {
            if (empty($lof_data['sku'])) {
                //条码不存在
                continue;
            }
            $lof_data_new[$lof_data['barcode']]['sku'] = $lof_data['sku'];
            if (empty($lof_data['lof_no']) || empty($lof_data['production_date'])) {
                $lof_data_new[$lof_data['barcode']]['production_date'] = $moren['production_date'];
                $lof_data_new[$lof_data['barcode']]['lof_no'] = $moren['lof_no'];

                $sys_lof_data[] = array(
                    'sku' => $lof_data['sku'],
                    'lof_no' => $moren['lof_no'],
                    'production_date' => $moren['production_date'],
                    'type' => 0,
                );
                continue;
            }
            $lof_data_new[$lof_data['barcode']]['production_date'] = $lof_data['production_date'];
            $lof_data_new[$lof_data['barcode']]['lof_no'] = $lof_data['lof_no'];
        }

        if (!empty($sys_lof_data)) {
            //不存在批次数据的sku，增加默认批次数据
            $this->insert_multi_exp('goods_lof', $sys_lof_data, true);
        }

        $new_barcode_num = $barcode_num;
        $barcode_num = array();
        foreach ($barcode_arr as $barcode) {
            if (array_key_exists($barcode, $lof_data_new)) {
                $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['num'] = $new_barcode_num[$barcode]['num'];
                $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['lof_no'] = $lof_data_new[$barcode]['lof_no'];
                $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['production_date'] = $lof_data_new[$barcode]['production_date'];
            }
        }
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
                    $sku_arr[] = trim($row[0]);
                    $sku_num[trim($row[0])]['num'] = $row[1];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
        // var_dump($sku_arr,$sku_num);die;
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
                    $sku_arr[] = trim($row[0]);
                    $sku_num[trim($row[0])][$row[1]]['lof_no'] = $row[1];
                    $sku_num[trim($row[0])][$row[1]]['production_date'] = $row[2];
                    $sku_num[trim($row[0])][$row[1]]['num'] = $row[3];
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

    function get_entity_shop_list($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = " LEFT JOIN stm_store_shift_record_detail r2 on rl.record_code = r2.record_code"
                . " LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code  ";
        $sql_main = "FROM {$this->table} rl {$sql_join} WHERE rl.shift_property = 1";
        $sql_values = array();
        //下单时间
        if (isset($filter['is_add_time_start']) && $filter['is_add_time_start'] != '') {
            $sql_main .= " AND (rl.is_add_time >= :is_add_time_start )";
            $sql_values[':is_add_time_start'] = $filter['is_add_time_start'];
        }
        if (isset($filter['is_add_time_end']) && $filter['is_add_time_end'] != '') {
            $sql_main .= " AND (rl.is_add_time <= :is_add_time_end )";
            $sql_values[':is_add_time_end'] = $filter['is_add_time_end'];
        }
        //移入时间
        if (isset($filter['is_shift_in_time_start']) && $filter['is_shift_in_time_start'] != '') {
            $sql_main .= " AND (rl.is_shift_in_time >= :is_shift_in_time_start )";
            $sql_values[':is_shift_in_time_start'] = $filter['is_shift_in_time_start'];
        }
        if (isset($filter['is_shift_in_time_end']) && $filter['is_shift_in_time_end'] != '') {
            $sql_main .= " AND (rl.is_shift_in_time <= :is_shift_in_time_end )";
            $sql_values[':is_shift_in_time_end'] = $filter['is_shift_in_time_end'];
        }
        //移出时间
        if (isset($filter['is_shift_out_time_start']) && $filter['is_shift_out_time_start'] != '') {
            $sql_main .= " AND (rl.is_shift_out_time >= :is_shift_out_time_start )";
            $sql_values[':is_shift_out_time_start'] = $filter['is_shift_out_time_start'];
        }
        if (isset($filter['is_shift_out_time_end']) && $filter['is_shift_out_time_end'] != '') {
            $sql_main .= " AND (rl.is_shift_out_time <= :is_shift_out_time_end )";
            $sql_values[':is_shift_out_time_end'] = $filter['is_shift_out_time_end'];
        }
        //单据状态
        if (isset($filter['is_shift_in']) && $filter['is_shift_in'] != '') {
            $sql_main .= " AND rl.is_shift_in in ({$filter['is_shift_in']}) ";
        }
        if (isset($filter['is_shift_out']) && $filter['is_shift_out'] != '') {
            $sql_main .= " AND rl.is_shift_out in ({$filter['is_shift_out']}) ";
        }
        //移入仓
        if (isset($filter['shift_in_store_code']) && $filter['shift_in_store_code'] != '') {
            $arr = explode(',', $filter['shift_in_store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shift_in_store_code', $sql_values);
            $sql_main .= " AND rl.shift_in_store_code in ({$str}) ";
        }
        //移出仓
        if (isset($filter['shift_out_store_code']) && $filter['shift_out_store_code'] != '') {
            $arr = explode(',', $filter['shift_out_store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shift_out_store_code', $sql_values);
            $sql_main .= " AND rl.shift_out_store_code in ( {$str}) ";
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = '%' . $filter['record_code'] . '%';
        }
        // 商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }

        if (isset($filter['goods_barcode']) && $filter['goods_barcode'] != '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['goods_barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND  1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND  r2.sku in({$sku_str})   ";
            }
        }

        $select = 'rl.*';
        $sql_main .= " group by rl.record_code order by record_time desc,lastchanged desc";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as &$val) {
            $is_shift_out_time = strtotime($val['is_shift_out_time']);
            $is_shift_in_time = strtotime($val['is_shift_in_time']);
            $val['is_shift_out_time'] = $is_shift_out_time !== false ? date('Y-m-d', $is_shift_out_time) : '';
            $val['is_shift_in_time'] = $is_shift_in_time !== false ? date('Y-m-d', $is_shift_in_time) : '';
        }
        filter_fk_name($data['data'], array('shift_in_store_code|store', 'shift_out_store_code|store'));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_shift_type() {
        $user_code = CTX()->get_session('user_code');
        $sql = "select type from sys_user where user_code = :user_code";
        $type = $this->db->get_value($sql, array(":user_code" => $user_code));
        $user_type = 1; //系统用户
        $shop_type = $this->sys_user_shift_type;
        if ($type != 0) {
            $shop_type = $this->shop_shift_type;
            $user_type = 2; //门店用户
        }

        $i = 0;
        $shift_type = array();
        foreach ($shop_type as $key => $type) {
            $shift_type[$i]['shop_tyoe_code'] = $key;
            $shift_type[$i]['shop_tyoe_name'] = $type;
            $i ++;
        }
        return array('shift_type' => $shift_type, 'user_type' => $user_type);
    }

    function get_store_info($shift_type) {
        $purview_store = $this->db->get_all("select store_code,store_name FROM base_store where status=1 AND store_property=0"); //总部仓
        $entity_store = load_model('base/StoreModel')->get_entity_store(); //门店仓
        $user_code = CTX()->get_session('user_code');
        $relation_store = $this->db->get_value("select relation_shop from sys_user where user_code = :user_code", array(':user_code' => $user_code));
        $next_store = $this->db->get_all("select store_code,store_name from base_store where store_code <> :store_code and store_property <> 0", array(":store_code" => $relation_store));
        $store_info = array();
        switch ($shift_type) {
            case "general_to_shop_user":
                $store_info['store_out_code'] = $purview_store;
                $store_info['store_in_code'] = $entity_store;
                break;
            case "shop_to_general_user":
                $store_info['store_out_code'] = $entity_store;
                $store_info['store_in_code'] = $purview_store;
                break;
            case "shop_to_shop_user":
                $store_info['store_out_code'] = $entity_store;
                $store_info['store_in_code'] = $entity_store;
                break;

            case "general_to_shop_shop":
                $store_info['store_out_code'] = $purview_store;
                $store_info['store_in_code'] = !empty($relation_store) ? $relation_store : "";
                break;

            case "next_to_shop_shop":
                $store_info['store_out_code'] = $next_store;
                $store_info['store_in_code'] = !empty($relation_store) ? $relation_store : "";
                break;

            case "shop_to_general_shop":
                $store_info['store_out_code'] = !empty($relation_store) ? $relation_store : "";
                $store_info['store_in_code'] = $purview_store;
                break;

            case "shop_to_next_shop":
                $store_info['store_out_code'] = !empty($relation_store) ? $relation_store : "";
                $store_info['store_in_code'] = $next_store;
                break;
            default:
                break;
        }

        return $store_info;
    }

    function do_add_record($data) {
        $data['remark'] = str_replace(array(","), '，', $data['remark']);
        $data['record_time'] = date('Y-m-d');
        $data['shift_property'] = 0;
        $shop_user_type = array('general_to_shop_shop', 'next_to_shop_shop', 'shop_to_general_shop', 'shop_to_general_shop');
        $sys_user_type = array('general_to_shop_user', 'shop_to_general_user', 'shop_to_shop_user');

        if (isset($data['type']) && $data['type'] == 'entity_shop') {
            $data['shift_property'] = 1;
            $data['record_code'] = load_model('stm/StoreShiftRecordModel')->create_fast_bill_sn();
            if (in_array($data['shift_type'], $sys_user_type)) {
                $data['shift_out_store_code'] = $data['store_out_code'];
                $data['shift_in_store_code'] = $data['store_in_code'];
            }
            if (in_array($data['shift_type'], $shop_user_type)) {
                $user_code = CTX()->get_session('user_code');
                //$user_code = '123456789';//测试
                $relation_store = $this->db->get_value("select relation_shop from sys_user where user_code = :user_code", array(':user_code' => $user_code));
                switch ($data['shift_type']) {
                    case 'general_to_shop_shop':
                        $data['shift_out_store_code'] = $data['store_code'];
                        $data['shift_in_store_code'] = $relation_store;
                        break;
                    case 'next_to_shop_shop':
                        $data['shift_out_store_code'] = $data['store_code'];
                        $data['shift_in_store_code'] = $relation_store;
                        break;
                    case 'shop_to_general_shop':
                        $data['shift_out_store_code'] = $relation_store;
                        $data['shift_in_store_code'] = $data['store_code'];
                        break;
                    case 'shop_to_next_shop':
                        $data['shift_out_store_code'] = $relation_store;
                        $data['shift_in_store_code'] = $data['store_code'];
                        break;
                    default:
                        break;
                }
            }
        }
        //task#1403 移仓单，若仓库对接外部WMS，且外部WMS编码一致且类型不一致，允许添加移仓单，但不上传至中间表（调整良品/次品库存） FBB 2017.06.16
        $store_shift = get_array_vars($data, array('record_code', 'init_code', 'record_time', 'is_shift_out_time', 'is_shift_in_time', 'shift_out_store_code', 'shift_in_store_code', 'rebate', 'remark', 'shift_property', 'is_add_person'));
        return $this->insert($store_shift);
    }

    /**
     * @todo API-移仓单查询
     * @desc shift_type=shift_out,移出；shift_type=shift_in，移入
     * @date       2016-08-17
     * @param
     *         主单据:     array(
     *                  必填： 'shift_type', 'store_code'
     *                  选填:  'page','page_size', 'start_time', 'end_time'
     *         )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    function api_shift_record_list_get($param) {
        //判断移仓单类型是否传入并正确
        if (!isset($param['shift_type']) || !in_array($param['shift_type'], array('shift_in', 'shift_out'))) {
            return $this->format_ret('-10001', array('shift_type'), 'API_STM_MESSAGE_10001');
        }
        $shift_type = $param['shift_type'];
        if (!isset($param['store_code']) || empty($param['store_code'])) {
            return $this->format_ret('-10001', array('store_code'), 'API_STM_MESSAGE_10001');
        }
        $store_code = $param['store_code'];
        //可选字段
        $key_option = array(
            's' => array(
                'page', 'page_size', 'start_time', 'end_time'
            )
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        //合并数据
        $arr_deal = $arr_option;
        //清空无用数据
        unset($arr_option, $param);

        $status = '0';
        $data_msg = array();
        try {
            //检查单页数据条数是否超限
            if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
                $data_msg['page_size'] = $arr_deal['page_size'];
                throw new Exception('API_STM_MESSAGE_PAGE_SIZE_TOO_LARGE');
            }

            //查询SQL
            $sql_main = " FROM {$this->table} sr WHERE 1=1";
            //开放字段
            $select = 'sr.`record_code`, sr.`record_time`, sr.`shift_in_store_code` AS store_code_in, sr.`shift_out_store_code` AS store_code_out,sr.`is_shift_out`,sr.`is_shift_in`, sr.`rebate`, sr.`is_add_time` AS create_time, sr.`out_num`,sr.`remark`';
            if ($shift_type == 'shift_out') {
                $sql_main .= ' AND sr.is_sure=1 AND sr.is_shift_out=0 ';
                $arr_deal['shift_out_store_code'] = $store_code;
            } else if ($shift_type == 'shift_in') {
                $select .= ' ,sr.`is_shift_out_time` AS out_time';
                $sql_main .= ' AND sr.is_shift_out=1 AND sr.is_shift_in=0 ';
                $arr_deal['shift_in_store_code'] = $store_code;
            }

            if (isset($arr_deal['start_time']) && !empty($arr_deal['start_time'])) {
                $ret = strtotime($arr_deal['start_time']);
                if ($ret == FALSE || $ret == '-1') {
                    $data_msg['start_time'] = $arr_deal['start_time'];
                    throw new Exception('时间格式错误');
                }
            } else {
                $arr_deal['start_time'] = date("Y-m-d H:i:s", strtotime("today"));
            }
            if (isset($arr_deal['end_time']) && !empty($arr_deal['end_time'])) {
                $ret = strtotime($arr_deal['end_time']);
                if ($ret == FALSE || $ret == '-1') {
                    $data_msg['end_time'] = $arr_deal['end_time'];
                    throw new Exception('时间格式错误');
                }
            } else {
                $arr_deal['end_time'] = date("Y-m-d H:i:s", strtotime("today +1 days"));
            }

            //绑定数据
            $sql_values = array();
            foreach ($arr_deal as $key => $val) {
                if ($key == 'page' || $key == 'page_size') {
                    continue;
                }
                if ($key == 'start_time') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND sr.lastchanged>=:{$key}";
                } else if ($key == 'end_time') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND sr.lastchanged<=:{$key}";
                } else {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND sr.{$key}=:{$key}";
                }
            }

            $data = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select);
            if (empty($data['data'])) {
                $status = '-10002';
                throw new Exception('API_STM_MESSAGE_10002');
            }
            return $this->format_ret(1, $data['data']);
        } catch (Exception $e) {
            return $this->format_ret($status, $data_msg, $e->getMessage());
        }
    }

    /**
     * @todo API-移仓单明细查询
     * @desc shift_type=shift_out,移出；shift_type=shift_in，移入
     * @date       2016-08-17
     * @param
     *         主单据:     array(
     *                  必填： 'record_code'
     *                  选填:  'page','page_size'
     *         )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    function api_shift_record_detail_get($param) {
        if (!isset($param['record_code']) || empty($param['record_code'])) {
            return $this->format_ret('-10001', array('record_code'), 'API_STM_MESSAGE_10001');
        }

        //可选字段
        $key_option = array(
            's' => array(
                'page', 'page_size'
            )
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        //合并数据
        $arr_deal = $arr_option;
        $arr_deal['record_code'] = $param['record_code'];
        //清空无用数据

        unset($arr_option);
        unset($param);

        $status = '0';
        $data_msg = array();
        try {
            //检查单页数据条数是否超限
            if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
                $data_msg['page_size'] = $arr_deal['page_size'];
                throw new Exception('API_STM_MESSAGE_PAGE_SIZE_TOO_LARGE');
            }

            //查询SQL
            $sql_main = " FROM stm_store_shift_record_detail rd
                        LEFT JOIN base_goods bg ON rd.goods_code = bg.goods_code
                        LEFT JOIN goods_sku gk ON rd.sku = gk.sku WHERE rd.record_code = :record_code";
            $sql_values[':record_code'] = $arr_deal['record_code'];
            //开放字段
            $select = 'rd.`record_code`, rd.`goods_code`, bg.`goods_name`, gk.`spec1_code`, gk.`spec1_name`, gk.`spec2_code`, gk.`spec2_name`,gk.`barcode`,rd.`price`,rd.`in_num`,rd.`in_money`,rd.`out_num`,rd.`out_money`,rd.`remark`';

            $data = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select);
            if (empty($data['data'])) {
                $status = '-10002';
                throw new Exception('API_STM_MESSAGE_10002');
            }
            return $this->format_ret(1, $data['data']);
        } catch (Exception $e) {
            return $this->format_ret($status, $data_msg, $e->getMessage());
        }
    }

    /**
     * @todo       API-移仓单出库验收
     * @date       2016-08-17
     * @param      array $param
     *              array(
     *                  必选: 'record_code'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_shift_record_out_accept($param) {
        return $this->api_shift_record_accept($param, 'shift_out');
    }

    /**
     * @todo       API-移仓单入库验收（扫描入库）
     * @date       2016-08-17
     * @param      array $param
     *              array(
     *                  必选: 'record_code'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_shift_record_in_accept($param) {
        return $this->api_shift_record_accept($param, 'shift_in');
    }

    /**
     * @todo API-移仓单移出、移入验收公共方法
     */
    private function api_shift_record_accept($param, $shift_type) {
        $status = '-10001';
        $data_msg = array();
        try {
            if (isset($param['record_code'])) {
                $record_code_arr = json_decode($param['record_code'], true);
                if (!is_array($record_code_arr)) {
                    $record_code_arr = array($param['record_code']);
                }
                if (empty($record_code_arr)) {
                    $data_msg[] = 'record_code';
                    throw new Exception('API_STM_MESSAGE_10001');
                }
            } else {
                $data_msg[] = 'record_code';
                throw new Exception('API_STM_MESSAGE_10001');
            }
            $this->is_api = 1;
            $msg = array();
            foreach ($record_code_arr as $record_code) {
                $record = $this->is_exists($record_code);
                $record = $record['data'];
                if (empty($record)) {
                    $msg[] = array('status' => '-10002', 'data' => $record_code, 'message' => '单据不存在');
                    continue;
                }
                if ($shift_type == 'shift_out' && $record['is_sure'] != 1) {
                    $msg[] = array('status' => '-1', 'data' => $record_code, 'message' => '单据未确认，不能出库');
                    continue;
                }
                if ($shift_type == 'shift_in' && $record['is_shift_out'] != 1) {
                    $msg[] = array('status' => '-1', 'data' => $record_code, 'message' => '单据未出库，不能入库');
                    continue;
                }
                $ret = $this->$shift_type($record['shift_record_id'], $record_code);
                $status = '1';
                if ($ret['status'] == '1') {
                    $status = '1';
                    if ($shift_type == 'shift_out') {
                        $log_data['id'] = $record['shift_record_id'];
                        $log_data['sure_status'] = '确认';
                        $log_data['finish_status'] = '出库';
                        $log_data['action_name'] = '出库';
                        $this->add_log($log_data);
                    }
                } else {
                    $status = '-1';
                }
                $message = $ret['status'] == '-2' ? '单据未确认' : $ret['message'];
                $msg[] = array('status' => $status, 'data' => $record_code, 'message' => $message);
            }
            return $this->format_ret(1, $msg);
        } catch (Exception $e) {
            return $this->format_ret($status, $data_msg, $e->getMessage());
        }
    }

    /**
     * @todo       API-移仓单明细更新，更新入库数
     * @date       2016-08-17
     * @param      array $param
     *              array(
     *                  必选: 'record_code'
     *                  必选: 'barcode_list'=>array(
     *                        'barcode','in_num','lof_no','production_date'
     *                         )
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    function api_shift_record_detail_update($param) {
        $status = '-10001';
        $data_msg = array();
        try {
            if (!isset($param['record_code']) || empty($param['record_code'])) {
                $data_msg = array('record_code');
                throw new Exception('API_STM_MESSAGE_10001');
            }
            $record_code = $param['record_code'];
            if (isset($param['barcode_list']) && !empty($param['barcode_list'])) {
                $detail = json_decode($param['barcode_list'], true);
            } else {
                $data_msg = array('barcode_list');
                throw new Exception('API_STM_MESSAGE_10001');
            }
            unset($param);
            $check_key = array('商品条码' => 'barcode', '实际入库数' => 'in_num');

            //检查明细是否为空
            $find_data = $this->api_check_detail($detail, $check_key);
            if ($find_data['status'] != 1) {
                $data_msg = $find_data['data'];
                throw new Exception($find_data['message']);
            }
            $record = $this->get_row(array('record_code' => $record_code));
            $record = $record['data'];
            if (empty($record)) {
                $status = '-10002';
                $data_msg['record_code'] = $record_code;
                throw new Exception('该移仓单号不存在');
            }
            if ($record['is_shift_out'] != 1) {
                $status = '-1';
                $data_msg['record_code'] = $record_code;
                throw new Exception('该移仓单未出库，不能更新入库数');
            }
            if ($record['is_shift_in'] != 0) {
                $status = '-1';
                $data_msg['record_code'] = $record_code;
                throw new Exception('该移仓单已入库，不能更新入库数');
            }
            $this->begin_trans();
            foreach ($detail as $val) {
                $val['record_code'] = $record_code;
                $val['record'] = $record;
                $ret = load_model('stm/StoreShiftRecordDetailModel')->scan_update_detail($val, 1);
                if ($ret['status'] != 1) {
                    $data_msg[] = $ret;
                }
            }
            if (!empty($data_msg)) {
                $this->rollback();
                $status = '-1';
                throw new Exception('更新失败');
            } else {
                $this->commit();
            }
            return $this->format_ret(1);
        } catch (Exception $e) {
            return $this->format_ret($status, $data_msg, $e->getMessage());
        }
    }

    /**
     * 检查接口传入明细信息是否为空
     */
    private function api_check_detail($detail, $check_key) {
        $err_data = array();
        foreach ($detail as $key => $val) {
            foreach ($check_key as $k => $v) {
                if (empty($val[$v])) {
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
     * @todo 新增日志
     */
    function add_log($param) {
        $opt_user_id = $this->is_api == 1 ? 1 : CTX()->get_session('user_id');
        $opt_user_code = $this->is_api == 1 ? 'admin' : CTX()->get_session('user_code');
        $suer_status = isset($param['sure_status']) ? $param['sure_status'] : '确认';
        $finish_status = isset($param['finish_status']) ? $param['finish_status'] : '未完成';
        $log = array('user_id' => $opt_user_id, 'user_code' => $opt_user_code, 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $suer_status, 'finish_status' => $finish_status, 'action_name' => $param['action_name'], 'module' => 'store_shift_record', 'pid' => $param['id']);
        $ret = load_model('pur/PurStmLogModel')->insert($log);
    }

    /**
     * 修改明细表扫描数量
     */
    function update_scan_num($record_code, $num, $id) {
        $ret = $this->get_row(array('record_code' => $record_code));
        $sku = substr($id, 8);
        //  $sku = $this->db->get_value("select sku from goods_sku where barcode = :barcode", array(":barcode" => $barcode));

        $detail = $this->db->get_row("select * from stm_store_shift_record_detail where record_code = '{$record_code}' and sku = '{$sku}'");
        if (empty($detail)) {
            return $this->format_ret(-1, '', '单据明细信息不存在');
        }
        if ($num <= 0) {
            return $this->format_ret(-1, '', '修改扫描数量必须大于0');
        }
        $detail['out_num'] = $num;
        $ret = $this->edit_detail_action($ret['data']['shift_record_id'], $detail);
        if ($ret) {
            return $this->format_ret(1, '', '更新成功');
        } else {
            return $this->format_ret(-1, '', '扫描更新单据明细数量失败');
        }
    }

    public function edit_detail_action($pid, $data) {
        $ret = $this->db->update('stm_store_shift_record_detail', array('out_num' => $data['out_num'], 'out_money' => $data['out_num'] * $data['price'] * $data['rebate']), array('pid' => $pid, 'sku' => $data['sku']));
        //回写金额和数量
        $res = load_model('stm/StoreShiftRecordDetailModel')->mainWriteBack($pid);
        $this->update_lof_detail($data['record_code'], $data['sku'], $data['out_num']);
        return $ret;
    }

    function update_lof_detail($record_code, $sku, $num) {
        $sql = "select * from b2b_lof_datail where order_code=:record_code AND sku=:sku AND order_type='shift_out' ";
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

    function is_same_outside_code($shift_in_code, $shift_out_code, $id = '') {
        if (!empty($id)) {
            $record = $this->get_row(array('shift_record_id' => $id));
            $shift_in_code = $record['data']['shift_in_store_code'];
            $shift_out_code = $record['data']['shift_out_store_code'];
        }
        $is_same_outside_code = 0;
        $sql = "SELECT r2.outside_code,r2.store_type FROM wms_config r1 LEFT JOIN sys_api_shop_store r2 ON r1.wms_config_id=r2.p_id WHERE r2.p_type=1 AND r2.shop_store_type=1 AND outside_type=1 AND shop_store_code=:shop_store_code";
        $value1 = $this->db->get_row($sql, array('shop_store_code' => $shift_in_code));
        $value2 = $this->db->get_row($sql, array('shop_store_code' => $shift_out_code));
        //if ($value1['outside_code'] == $value2['outside_code'] && $value1['store_type'] != $value2['store_type']) {
        //    $is_same_outside_code = 1;
        //}
        if ($value1['outside_code'] == $value2['outside_code']) {
            $is_same_outside_code = 1;
        }
        return $is_same_outside_code;
    }

    public function add_detail($param) {
        $this->begin_trans();
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($param['record_id'], $param['detail'], 'shift_out');
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($param['record_id'], $param['store_code'], 'shift_out', $param['detail']);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }
        $ret = load_model('stm/StoreShiftRecordDetailModel')->add_detail_action($param['record_id'], $param['detail']);

        if ($ret['status'] == '1') {
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '增加明细', 'module' => "store_shift_record", 'pid' => $param['record_id']);
            load_model('pur/PurStmLogModel')->insert($log);

            $this->commit();
        }else{
            $this->rollback();
        }

        return $ret;
    }

}
