<?php

/**
 * 采购计划单相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('pur');
require_lib('comm_util', true);

class PlannedRecordModel extends TbModel {

    function get_table() {
        return 'pur_planned_record';
    }

    public $print_fields_default = array(
        'record' => array(
            '单据编号' => 'record_code',
            '原单号' => 'init_code',
            '计划日期' => 'planned_time',
            '数量' => 'num',
            '供应商' => 'supplier_name',
            '仓库' => 'store_name',
            '折扣' => 'rebate',
            '业务日期' => 'record_time',
            '金额' => 'money',
            '备注' => 'remark',
            '完成数量' => 'finish_num',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
            '采购类型' => 'pur_type_code',
        ),
        'detail' => array(
            array(
                '图片地址' => 'goods_img',
                '客户货号' => 'sku',
                '数量' => 'num_detail',
                '货品描述' => 'goods_desc',
                '货品编号' => 'goods_code',
            ),
        ),
    );

    /**
     * 默认打印数据 new
     * @param $id
     * @return array
     * 发货单新版打印（更改打印配件）
     */
    public function print_data_default_new($request) {
        $id = $request['record_ids'];
        $r = array();
        $r['record'] = $this->db->get_row("select * from pur_planned_record where planned_record_id = :id", array(':id' => $id));
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $sql = "select *
                    from pur_planned_record_detail d
                    where d.pid=:pid";
        $r['detail'] = $this->db->get_all($sql, array(':pid' => $id));
        $sku_array = array();
        $shelf_code_arr = array();
        $i = 0;
        foreach ($r['detail'] as $key => $detail) {//合并同一sku
            $key_arr = array('goods_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
            $goods_mes = load_model('prm/GoodsModel')->get_by_goods_code($detail['goods_code']);
            $r['detail'][$key] = array_merge($detail, $sku_info);
            $r['detail'][$key]['goods_desc'] = $goods_mes['data']['goods_desc'];
            $dat = $this->db->get_row("select goods_img from base_goods where goods_code=:goods_code", array(":goods_code" => $detail['goods_code']));
//                        oms_tb_val('base_goods','goods_img',array('goods_code'=>$detail['goods_code']));
            //$r['detail'][$key]['goods_img'] = "<img src='{$dat['goods_img']}'  style='width:150px;height:150px;' />";
            $r['detail'][$key]['id'] = $i;
            $i++;
        }
        array_multisort($shelf_code_arr, SORT_ASC, $r['detail']);
        $this->print_data_escape($r['record'], $r['detail']);
        $trade_data = array($r['record']);
        //更新状态
        $this->update_print_status($id);
        return $r;
    }

    function print_data_escape(&$record, &$detail) {
        $data = $this->db->get_row("select store_name from base_store where store_code=:store_code", array(":store_code" => $record['store_code']));
        $record['store_name'] = $data['store_name'];
    }

    //更新打印状态
    public function update_print_status($planned_record_id) {
        $sql = "SELECT is_print_record FROM pur_planned_record WHERE planned_record_id = :planned_record_id ";
        $data = $this->db->get_row($sql, array(':planned_record_id' => $planned_record_id));
        if (empty($data)) {
            return array('status' => '-1', 'message' => '采购订单不存在');
        }
        //添加日志
        $this->add_print_log($planned_record_id);
        //更新状态
        $this->update_exp('pur_planned_record', array('is_print_record' => 1), array('planned_record_id' => $planned_record_id));
    }

    //打印日志记录
    public function add_print_log($planned_record_id) {
        // 增加打印日志
        $sql = "SELECT is_check,is_finish FROM pur_planned_record WHERE planned_record_id = :planned_record_id  ";
        $d = $this->db->get_row($sql, array(':planned_record_id' => $planned_record_id));
        $sure_status = $d['is_check'] == 1 ? "确认" : "未确认";
        $finish_status = $d['is_finish'] == 0 ? '未完成' : '已完成';
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => $finish_status, 'action_name' => '打印', 'module' => "planned_record", 'pid' => $planned_record_id);
        load_model('pur/PurStmLogModel')->insert($log);
    }

    //检查是否打印
    public function check_is_print($planned_record_id) {
        $sql = "SELECT is_print_record FROM pur_planned_record WHERE planned_record_id = :planned_record_id ";
        $data = $this->db->get_row($sql, array(':planned_record_id' => $planned_record_id));
        $ret = $data['is_print_record'] == 1 ? $this->format_ret(-1, '', '重复打印采购订单，是否继续打印？') : $this->format_ret(1, '', '');
        return $ret;
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = "left join pur_planned_record_detail r2 on rl.record_code = r2.record_code
        			left join goods_sku gb on gb.goods_code = r2.goods_code and
        										gb.spec1_code = r2.spec1_code and
        										gb.spec2_code = r2.spec2_code
                                                                                        LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code ";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
        $sql_values = array();
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $sql_main .= load_model('base/SupplierModel')->get_sql_purview_supplier('rl.supplier_code', $filter_supplier_code);
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);

        //计划日期
        if (isset($filter['planned_time_start']) && $filter['planned_time_start'] != '') {
            $sql_main .= " AND (rl.planned_time >= :planned_time_start )";
            $sql_values[':planned_time_start'] = $filter['planned_time_start'];
        }
        if (isset($filter['planned_time_end']) && $filter['planned_time_end'] != '') {
            $sql_main .= " AND (rl.planned_time <= :planned_time_end )";
            $sql_values[':planned_time_end'] = $filter['planned_time_end'];
        }

        //新增商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = "%" . $filter['goods_code'] . "%";
        }

        //新增条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND (gb.barcode LIKE :barcode )";
            $sql_values[':barcode'] = "%" . $filter['barcode'] . "%";
        }

        //入库期限
        if (isset($filter['in_time_start']) && $filter['in_time_start'] != '') {
            $sql_main .= " AND (rl.in_time >= :in_time_start )";
            $sql_values[':in_time_start'] = $filter['in_time_start'];
        }
        if (isset($filter['in_time_end']) && $filter['in_time_end'] != '') {
            $sql_main .= " AND (rl.in_time <= :in_time_end )";
            $sql_values[':in_time_end'] = $filter['in_time_end'];
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        //原单号
        if (isset($filter['init_code']) && $filter['init_code'] != '') {
            $sql_main .= " AND (rl.init_code = :init_code )";
            $sql_values[':init_code'] = $filter['init_code'];
        }
        // 备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND (rl.remark LIKE :remark )";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
        }
        // 下单日期
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $sql_main .= " AND (rl.record_time >= :record_time_start )";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql_main .= " AND (rl.record_time <= :record_time_end )";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        //是否有差异订单
        if (isset($filter['difference_models']) && $filter['difference_models'] != '') {
            if ($filter['difference_models'] == 1) {
                $sql_main .= " AND (rl.num != rl.finish_num )";
            } else {
                $sql_main .= " AND (rl.num = rl.finish_num )";
            }
        }
        //单据状态
        if (isset($filter['record_status']) && $filter['record_status'] != '') {
            switch ($filter['record_status']) {
                case 'is_check_0':
                    $sql_main .= " AND (rl.is_check = 0) ";
                    break;
                case 'is_check_1':
                    $sql_main .= " AND (rl.is_check = 1) ";
                    break;
                case 'is_execute_0':
                    $sql_main .= " AND (rl.is_execute = 0) ";
                    break;
                case 'is_execute_1':
                    $sql_main .= " AND (rl.is_execute = 1) ";
                    break;
                case 'is_finish_0':
                    $sql_main .= " AND (rl.is_finish = 0) ";
                    break;
                case 'is_finish_1':
                    $sql_main .= " AND (rl.is_finish = 1) ";
                    break;
            }
        }
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] != 'planned_record_list') {
            return $this->sell_record_search_csv($sql_main, $sql_values, $filter);
        }
        //$select = 'rl.*,r2.goods_name,r2.goods_name,r2.weight';
        $select = 'rl.*';
        $sql_main .= " GROUP BY rl.record_code order by planned_time desc, rl.record_code desc";
        //echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, TRUE);
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $filter['user_id']);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['money'] = round($value['money'], 2);
            //获取完成数金额
            if ($status['status'] != 1) {
                $data['data'][$key]['money'] = '****';
                $data['data'][$key]['finish_money'] = '****';
            }
//            $sql = "select sum(finish_num) as finish_num from pur_planned_record_detail where pid = :pid";
//            $finish = $this->db->get_row($sql, array(":pid" => $value['planned_record_id']));
//            $data['data'][$key]['finish_num'] = isset($finish['finish_num'])?$finish['finish_num']:'';
            $data['data'][$key]['difference_num'] = $value['num'] - $data['data'][$key]['finish_num'];
            //$arr = load_model('base/StoreModel')->get_by_field('store_code', $value['store_code'], 'store_name');
            //$data['data'][$key]['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
        }
        filter_fk_name($data['data'], array('store_code|store', 'supplier_code|supplier', 'adjust_type|record_type', 'pur_type_code|record_type'));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 获取完成数金额
     * @param $record_code
     */
    function get_finish_money($record_code) {
        $sql = "SELECT sum(finish_num*price) AS finish_money FROM pur_planned_record_detail WHERE record_code=:record_code GROUP BY record_code";
        $sql_value[':record_code'] = $record_code;
        $ret = $this->db->get_row($sql, $sql_value);
        return $ret;
    }

    /**
     * 明细导出
     * @param $sql_main
     * @param $sql_values
     * @param $filter
     * @return array
     */
    private function sell_record_search_csv($sql_main, $sql_values, $filter) {
        $select = "rl.rebate,rl.record_code,rl.init_code,rl.remark,rl.supplier_code,rl.store_code,rl.pur_type_code record_type_code,rl.planned_time,rl.in_time,r3.goods_name,r2.goods_code,r2.spec1_code,r2.spec2_code,gb.barcode,gb.sku,r2.refer_price,r2.price,r2.num,r2.money,r2.finish_num,rl.is_finish,r2.finish_money AS finish_money_detail";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $filter['user_id']);
        foreach ($ret_data['data'] as $key => $value) {
            //查询仓库名称

            $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['store_code'], 'store_name');
            $ret_data['data'][$key]['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
            //查询规格1/规格2
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $ret_data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $ret_data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $ret_data['data'][$key]['goods_name'] = $sku_info['goods_name'];
            $ret_data['data'][$key]['barcode'] = $sku_info['barcode'];
            $ret_data['data'][$key]['difference_num'] = $value['num'] - $value['finish_num'];
            $ret_data['data'][$key]['stock_price'] = $value['price'] * $value['rebate'];
            if ($value['is_finish'] == 1) {
                $ret_data['data'][$key]['is_finish'] = '是';
            } else {
                $ret_data['data'][$key]['is_finish'] = '否';
            }
            //金额权限
            if ($status['status'] != 1) {
                $ret_data['data'][$key]['price'] = '****';
                $ret_data['data'][$key]['stock_price'] = '****';
                $ret_data['data'][$key]['money'] = '****';
                $ret_data['data'][$key]['finish_money_detail'] = '****';
            }
        }
        filter_fk_name($ret_data['data'], array('supplier_code|supplier', 'record_type_code|record_type'));
        return $this->format_ret(1, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('planned_record_id' => $id));
        filter_fk_name($data['data'], array('store_code|store'));
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        if ($status['status'] != 1 && !empty($data['data'])) {
            $data['data']['money'] = '****';
            $data['data']['finish_money'] = '****';
        }
        //权限状态赋值
        $data['data']['status'] = $status['status'];
        return $data;
    }

    function get_by_code($record_code) {
        $data = $this->get_row(array('record_code' => $record_code));
        return $data;
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn() {

        $sql = "select planned_record_id  from {$this->table}   order by planned_record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['planned_record_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "CG" . date("Ymd") . add_zero($djh);
        return $jdh;
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
     * 删除记录
     * */

    function delete($planned_record_id) {
        $record = $this->is_exists($planned_record_id, 'planned_record_id');
        if (isset($record['data']['is_check']) && 1 == $record['data']['is_check']) {
            return $this->format_ret(false, array(), 'PLAN_DELETE_ERROR_CHECK');
        }
        $this->begin_trans();
        try {
            $ret = load_model('pur/PlannedRecordDetailModel')->delete_pid($planned_record_id);
            if ($ret['status'] != 1) {
                $this->rollback(); //事务回滚
                return $ret;
            }
            $ret = parent::delete(array('planned_record_id' => $planned_record_id));
            $this->commit();
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "确认", 'finish_status' => '未完成', 'action_name' => "删除计划单", 'module' => "planned_record", 'pid' => $planned_record_id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            return $ret;
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), 'DATABASE_ERROR' . $e->getMessage());
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
            return $this->format_ret(RECORD_ERROR_UNIQUE_CODE1);
        $stock_adjus['is_add_time'] = date('Y-m-d H:i:s');
        $stock_adjus['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $stock_adjus['remark']);
        return parent::insert($stock_adjus);
    }

    function insert_data($stock_adjus, $data) {
        $this->begin_trans();
        $ret = $this->insert($stock_adjus);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "创建", 'module' => "planned_record", 'pid' => $ret['data']);
            load_model('pur/PurStmLogModel')->insert($log);
        }

        $detail_data = array();
        foreach ($data as $val) {
            $detail_data[] = $this->get_pur_detail_info($val);
        }

        $ret = load_model('pur/PlannedRecordDetailModel')->add_detail_action($ret['data'], $detail_data);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        return $ret;
    }

    function get_pur_detail_info($val) {
        $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku, g.purchase_price from base_goods g  "
                . " INNER JOIN  goods_sku b ON g.goods_code=b.goods_code "
                . " WHERE b.sku =:sku ";
        $row = $this->db->get_row($sql, array(':sku' => $val['sku']));
        $row['num'] = $val['num'];
        return $row;
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
            return 'RECORD_ERROR_CODE';
        return 1;
    }

    /**
     * 新增一条记录
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
            $ary_main['record_code'] = $this->create_fast_bill_sn();
        }
        $ary_main['is_add_time'] = date('Y-m-d H:i:s');
        $ret = $this->insert($ary_main);
        //返回结果
        return $ret;
    }

    /**
     * 编辑一条库存调整记录
     */
    public function edit_action($record_code, $data, $where) {
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        if (!isset($where['planned_record_id']) && !isset($where['record_code'])) {
            return $this->format_ret('-1', '', 'PLAN_RELATION_ERROR_CODE');
        }
        $result = $this->get_row($where);
        if (1 != $result['status']) {
            return $this->format_ret('-1', '', 'PLAN_NO_ERROR_DATA');
        }
//        if (1 == $result['data']['is_check']) {
//            return $this->format_ret('-1','', 'PLAN_DELETE_ERROR_CHECK!');
//        }
        //更新主表数据
        parent::update($data, $where);
        $sql = "select * from pur_planned_record_detail where record_code='{$record_code}'";
        $record = $this->db->get_all($sql);
        $ret = $this->format_ret("1", "", "update_success");
        $rebate = isset($data['rebate']) ? $data['rebate'] : $result['data']['rebate'];
        foreach ($record as $val) {
            $ret = parent::update_exp('pur_planned_record_detail', array('rebate' => $rebate, 'money' => $val['price'] * $rebate * $val['num']), array('record_code' => $record_code, 'sku' => $val['sku']));
            $this->mainWriteBack($where['planned_record_id']);
        }
        return $ret;
    }

    public function mainWriteBack($record_id) {
        //回写数量和金额、完成数
        $sql = "update pur_planned_record set
		pur_planned_record.num = (select sum(num) from pur_planned_record_detail where pid = :id),
		pur_planned_record.finish_num = (select sum(finish_num) from pur_planned_record_detail where pid = :id),
		pur_planned_record.money = (select sum(money) from pur_planned_record_detail where pid = :id)
		where pur_planned_record.planned_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }

    //判断完成
    function update_finish($record_code, $is_force = 0) {
        $arr = array(':record_code' => $record_code);
        $result = $this->get_row(array('record_code' => $record_code));

        if ($result['status'] == '1' && $result['data']['is_finish'] == '1') {
            return $this->format_ret('-1', '', '该采购订单已完成');
        }
        if ($result['data']['is_check'] == '0') {
            return $this->format_ret('-1', '', '该采购订单未确认');
        }


        //是否有通知单
        $sql = " select count(*) as cnt  from pur_order_record where  relation_code = :record_code ";

        $data = $this->db->get_all($sql, $arr);
        if ($data[0]['cnt'] == 0) {
            $ret['status'] = '1';
            $ret['data'] = '';
            $ret['message'] = '';

            return $ret;
        }
        $is_check = false;
        $ret = $this->format_ret(1);
        if ($is_force == 1) {
            $arr1 = array(':relation_code' => $record_code);
            $sql = " select count(*) as cnt  from pur_order_record where  relation_code = :relation_code AND   is_finish = '0'  ";
            $no_finish = $this->db->get_value($sql, $arr1);

//            $sql = "select count(*) as cnt  from pur_order_record where  relation_code = :relation_code  ";
//            $data1 = $this->db->get_value($sql, $arr1);

            if ($no_finish > 0) {
                $ret = $this->format_ret(0, '', '存在未完成的通知单');
            }
        } else {
            $sql = " select count(*) as cnt  from pur_planned_record_detail where  record_code = :record_code  AND   finish_num<num  ";
            $no_finish = $this->db->get_value($sql, $arr);
            if ($no_finish > 0) {
                $ret = $this->format_ret(0, '', '采购订单采购数量未完成');
            }
        }
        return $ret;
    }

    function update_check_record_code($active, $field, $record_code) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $details = load_model('pur/PlannedRecordDetailModel')->get_all(array('record_code' => $record_code));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret = parent:: update(array($field => $active), array('record_code' => $record_code));
        return $ret;
    }

    function update_check($active, $field, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $details = load_model('pur/PlannedRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            //return $this->format_ret('RECORD_ERROR_DETAIL_EMPTY');
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret = parent:: update(array($field => $active), array('planned_record_id' => $id));

        return $ret;
    }

    /**
     * @todo 判断值在多维数组中是否存在
     */
    function deep_in_array($value, $array) {
        foreach ($array as $item) {
            if (!is_array($item)) {
                if ($item == $value) {
                    return true;
                } else {
                    continue;
                }
            }
            if (in_array($value, $item)) {
                return $item;
            } else if ($this->deep_in_array($value, $item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 导入完成数
     */
    function import_complete_num($param, $file) {
        $id = $param['id'];
        //判断主单据的pid是否存在
        $record = $this->is_exists($id, 'planned_record_id');
        $record = $record['data'];
        if (empty($record)) {
            return $this->format_ret(false, array(), '采购订单不存在!');
        }
        //判断主单据状态
        if ($record['is_check'] == 0) {
            return $this->format_ret(false, array(), '采购订单未确认，不能导入完成数!');
        }
        $sql = "SELECT rd.sku,gk.barcode FROM pur_planned_record_detail rd
                LEFT JOIN goods_sku gk ON rd.sku=gk.sku
                WHERE rd.record_code=:record_code";
        $sql_value = array(':record_code' => $record['record_code']);
        $detail_arr = $this->db->get_all($sql, $sql_value);

        $barcode = array();
        $num = $this->read_csv($file, $barcode);

        $import_count = count($barcode);
        $error_msg = array();
        $err_num = 0;

        //处理导入信息，正确和错误信息分离
        $update_arr = array();
        foreach ($barcode as $key => $val) {
            $check = 0;
            foreach ($detail_arr as $item) {
                if ($item['barcode'] == $key) {
                    $update_arr[$item['sku']] = $val;
                    $check = 1;
                    break;
                }
            }
            if ($check == 0) {
                $error_msg[] = array($key => '明细中不存在此条码商品');
                $err_num++;
            }
        }
        unset($barcode, $detail_arr);
        if (!empty($update_arr)) {
            //拼接批量更新sql语句
            $sku_str = deal_strs_with_quote(implode(',', array_keys($update_arr)));
            $sql = "UPDATE pur_planned_record_detail SET finish_num = CASE sku ";
            if ($param['type'] == 'overlying') {
                foreach ($update_arr as $k => $v) {
                    $sql .= sprintf("WHEN '%s' THEN finish_num + '%s' ", $k, $v);
                }
            } else {
                foreach ($update_arr as $k => $v) {
                    $sql .= sprintf("WHEN '%s' THEN '%s' ", $k, $v);
                }
            }
            $sql .= "END WHERE sku IN ({$sku_str}) AND record_code='{$record['record_code']}'";
            $ret = $this->query($sql);
        }


        $success_num = $import_count - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $message .= ',' . '失败数量:' . $err_num;
            $log_note = $message;
            $fail_top = array('商品条码', '错误信息');
            $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        } else {
            $log_note = $message;
        }

        if ($ret['status'] == 1) {
            load_model('pur/PlannedRecordDetailModel')->mainWriteBack($id); //回写主单据数量
            //日志
            $finish_status = $record['is_finish'] == 1 ? '已完成' : '未完成';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "已确认", 'finish_status' => $finish_status, 'action_name' => "导入完成数", 'module' => "planned_record", 'pid' => $id, 'action_note' => $log_note);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        $ret['message'] = $message;
        return $ret;
    }

    /**
     * 读取文件，保存到数组中
     */
    function read_csv($file, &$barcode) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $barcode[$row[0]] = $row[1];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /**
     * 导入商品
     */
    function imoprt_detail($param, $file) {
        $id = $param['id'];
        $ret = $this->get_row(array('planned_record_id' => $id));
        $store_code = $ret['data']['store_code'];
        $relation_code = $ret['data']['relation_code'];
        $sku_arr = $sku_num = $price_arr = array();
        $error_msg = '';

        $num = $this->read_csv_sku($file, $sku_arr, $sku_num, $price_arr);
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'barcode', $sql_values);
        $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.price,g.purchase_price from goods_sku b
	    	inner join  base_goods g ON g.goods_code = b.goods_code
	    	where b.barcode in({$sku_str}) group by b.barcode ";

        $detail_data = $this->db->get_all($sql, $sql_values);
        $sucess_num = 0;
        foreach ($detail_data as $key => $val) {
            if (intval($sku_num[$val['barcode']]['num']) > 0) {
                $detail_data[$key]['num'] = $sku_num[$val['barcode']];
                $detail_data[$key]['purchase_price'] = empty($price_arr[$val['barcode']]) || $price_arr[$val['barcode']] == 0 ? $val['purchase_price'] : $price_arr[$val['barcode']];
                unset($sku_num[$val['barcode']]);
                $sucess_num++;
            } else {
                unset($detail_data[$key]);
            }
        }
        //采购单明细添加
        $ret = load_model('pur/PlannedRecordDetailModel')->add_detail_action($id, $detail_data);
        if ($ret['status'] != '1') {
            $sucess_num = 0;
            $err_message = $ret['message'];
        } else {
            $err_message = '找不到对应条码';
        }
        $ret['data'] = '';
        $message = '导入成功SKU数' . $sucess_num;
        if (!empty($sku_num)) {
            $message .= ',' . '失败sku:' . count($sku_num);
            $file_name = $this->create_import_fail_files($sku_num, $err_message);
            //        load_model("sys/ExportModel")->downlaod_csv($request['file_key'],$request['export_name']);
//    		$message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        //$sucess_num
        //导入成功SKU数X，导入失败的SKU数Y，错误信息下载
        $ret['message'] = $message;

        return $ret;
    }

    function create_import_fail_files($fail_data, $msg) {
        $fail_top = array('商品条码', '错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($fail_data as $barcode => $val) {
            $val_data = array($barcode, $msg);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("planned_record_fail" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        //var_dump($file_str);die;
        return $filename;
    }

    function read_csv_sku($file, &$sku_arr, &$sku_num, &$price_arr) {
        //    $key_arr = array('0'=>'sku','1'=>'num');
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sku_arr[] = $row[0];
                    $sku_num[$row[0]] = $row[1];
                    $price_arr[$row[0]] = $row[2];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /**
     * @todo 编码转换
     */
    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
//                $val = iconv('gbk', 'utf-8', $val); 中文转码后变false
//                $val = mb_convert_encoding($val,'utf-8','gbk'); 中文转码后变乱码
                $val = trim(str_replace('"', '', $val));
            }
        }
    }

    //上传文件
    function import_upload($request, $upload_files) {
        $app['fmt'] = 'json';
        $files = array();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/";
        $fileInput = 'fileData';
        $dir = ROOT_PATH . 'webefast/uploads/';
        $type = $_POST['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        $is_max = 0;
        $is_file_type = 0;
        $file_type = array('csv', 'xlsx', 'xls');
        $upload_max_filesize = 2097152;
        foreach ($files_name_arr as $k => $v) {
            $pic = $upload_files[$v];
            if (!isset($pic['tmp_name']) || empty($pic['tmp_name'])) {
                $is_max = 1;
                continue;
            }
            $file_ext = get_file_extension($pic['name']);
            if (!in_array($file_ext, $file_type)) {
                $is_file_type = 1;
                continue;
            }
            $isExceedSize = $pic['size'] > $upload_max_filesize;
            if (!$isExceedSize) {
                if (file_exists($dir . $pic['name'])) {
                    @unlink($dir . $pic['name']);
                }
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
                $result = move_uploaded_file($pic['tmp_name'], $dir . $new_file_name);
                if (true == $result && ('xlsx' == $file_ext || 'xls' == $file_ext)) {
                    $result = $this->excel2csv($dir . $new_file_name, $file_ext);
                    $new_file_name = str_replace('.' . $file_ext, '.csv', $new_file_name);
                }
            }
        }
        if ($is_max) {
            return array(
                'status' => 0,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'msg' => str_replace('{0}', substr(ini_get('upload_max_filesize'), 0, -1) * 1024, lang('upload_msg_maxSize'))
            );
        } else if ($is_file_type) {
            return array(
                'status' => 0,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'msg' => str_replace('{0}', implode(',', $file_type), lang('upload_msg_ext'))
            );
        } else if (!$isExceedSize && $result) {
            return array(
                'status' => 1,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'url' => $dir . $new_file_name
            );
        } else if ($isExceedSize) {
            return array(
                'status' => 0,
                'type' => $type,
                'msg' => str_replace('{0}', $upload_max_filesize / 1024, lang('upload_msg_maxSize'))
            );
        } else {
            return array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！" . $result
            );
        }
    }

    /**
     *
     * 方法名       excel2csv
     *
     * 功能描述     excel转换csv文件
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-24
     * @param       string $file
     * @param       string $extends
     *
     * @return      string $data
     */
    function excel2csv($file, $extends) {
        require_lib('PHPExcel', true);
        try {
            $time3 = time();
            $PHPExcel = PHPExcel_IOFactory::load($file);
            $time4 = time();
            $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'CSV');
            $objWriter->setUseBOM(true);
            $objWriter->setPreCalculateFormulas(false);
            $objWriter->save(str_replace('.' . $extends, '.csv', $file));
            $time5 = time();
        } catch (Exception $e) {
            /* return array(
              'status' => -1,
              'data' => array($e->getMessage()),
              'msg' => lang('op_error')
              ); */
            return false;
        }
        /* return array(
          'status' => 1,
          'data' => array('load_excel' => $time4 - $time3, 'write_csv' => $time5 - $time4, 'excel_to_csv' => $time5 - $time3),
          'msg' => lang('op_success')
          ); */
        return true;
    }

    /**
     * 检查采购订单状态
     */
    function check_status($planned_record) {
        $record_detail = load_model('pur/PlannedRecordDetailModel')->is_exists_detail($planned_record['record_code'], 'record_code');
        if (empty($planned_record) || empty($record_detail['data'])) {
            return $this->format_ret(-1, '', '采购订单信息不存在！');
        }
        if ($planned_record['is_check'] == 0) {
            return $this->format_ret(-1, '', '未确认采购订单不能生成采购通知单！');
        }
        if ($planned_record['is_finish'] == 1) {
            return $this->format_ret(-1, '', '已完成采购订单不能生成采购通知单！');
        }
        return $this->format_ret(1);
    }

    /**
     * 采购订单生成采购通知单
     */
    public function create_order_record($planned) {
        $planned_record = $this->get_by_id($planned['planned_record_id']);
        $ret = $this->check_status($planned_record['data']);
        if ($ret['status'] == 1) {
            $ret = load_model('pur/OrderRecordModel')->create_order_record($planned_record['data'], $planned['create_type']);
            if ($ret['status'] == 1) {
                $ret1 = $this->update_check('1', 'is_execute', $planned['planned_record_id']);
            }
        }
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未完成', 'action_name' => '生成采购通知单', 'module' => "planned_record", 'pid' => $planned['planned_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
    }

    /**
     * 创建二维表导入采购订单模版
     * @return array
     */
    public function create_layer_import_tpl() {
        $ret = load_model('prm/SizeLayerModel')->get_layer();
        if ($ret['status'] < 1) {
            return $ret;
        }
        $data = $ret['data'];
        $rcount = count($data) + 1;
        $ccount = count($data[0]);

        require_lib('PHPExcel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()
                ->setCreator('BAOTA')
                ->setLastModifiedBy('BAOTA')
                ->setTitle('BAOTA eFAST365')
                ->setSubject('BAOTA eFAST365')
                ->setDescription('BAOTA eFAST365 EXCEL DOCUMENT')
                ->setKeywords('BAOTA eFAST365')
                ->setCategory('ERP');
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $char = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $layer_col = array_slice($char, 4, $ccount);
        $last_col = $char[4 + $ccount];
        $column = [
            'A' => '仓库代码*', 'B' => '计划日期*', 'C' => '商品编码*', 'D' => '颜色名称*', $last_col => '进货单价'
        ];

        foreach ($column as $k => $c) {
            $objActSheet->getStyle($k)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objActSheet->mergeCells("{$k}1:{$k}{$rcount}");
            $objActSheet->setCellValue("{$k}1", $c);
            //设置对齐方式
            $objActSheet->getStyle("{$k}1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //垂直居中
            $objActSheet->getStyle("{$k}1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }

        $objActSheet->mergeCells("E1:" . end($layer_col) . '1');
        $objActSheet->setCellValue("E1", '尺码数量*');
        //设置对齐方式
        $objActSheet->getStyle("E1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //垂直居中
        $objActSheet->getStyle("E1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ($layer_col as $col) {
            $objActSheet->getStyle($col)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        }

        foreach ($data as $k => $v) {
            $num = $k + 2;
            foreach ($layer_col as $l => $col) {
                $objActSheet->setCellValue("{$col}{$num}", $v[$l]);
            }
        }
        //示例值
        $example = ['CK001', '2018-01-01', 'G0001', '红色', '100.99'];
        $last_row = $num + 1;
        $column = array_keys($column);
        foreach ($column as $k => $c) {
            $objActSheet->setCellValue("{$c}{$last_row}", $example[$k]);
        }
        foreach ($layer_col as $col) {
            $objActSheet->setCellValue("{$col}{$last_row}", '100');
        }


        $objActSheet->setTitle('二维表导入采购订单');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="planned_record_layer_import.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        $objWriter->save(ROOT_PATH . CTX()->app_name . '/data/excelDefault/planned_record_layer_import.xlsx');

        return $this->format_ret(1);
    }

    /**
     * 编码转换
     */
    private function tran_layer_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
                //$val = mb_convert_encoding($val, 'utf-8', 'utf-8');
                $val = trim($val);
                $val = trim(str_replace('"', '', $val),chr(0xc2).chr(0xa0));
            }
        }
    }

    private function read_layer_csv($file, &$data, &$size_ori) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            $c = count($row);
            if ($i > 0 && (!empty($row[0]) || !empty($row[1]) || !empty($row[2]) || !empty($row[3]))) {
                $this->tran_layer_csv($row);
                $data[] = [
                    'no' => $i + 1, //表格行号
                    'store_code' => $row[0],
                    'planned_time' => $row[1],
                    'goods_code' => $row[2],
                    'spec1_name' => $row[3],
                    'price' => $row[$c - 1],
                    'num_arr' => array_slice($row, 4, $c - 5)
                ];
            } else if ($i == 1) {
                $size_ori = array_slice($row, 4, $c - 5);
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    private function get_layer_err_import($err_arr) {
        $fail_top = array('行号', '错误数据', '错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($err_arr as $val) {
            $file_str .= implode("\t,", $val) . "\r\n";
        }
        $filename = md5("planned_record_import_fail" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);

        $url = set_download_csv_url($filename, array('export_name' => 'error'));
        $msg = "数据导入出错，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $msg;
    }

    /**
     *  二维表导入采购单
     */
    function layer_import_action($file) {
        $err_arr = [];
        $data = [];
        $size_ori = [];
        $this->read_layer_csv($file, $data, $size_ori);
        $total = count($data);

        $objData = load_model('base/ArchiveSearchModel');
        $size_arr = $objData->get_single_data('spec2', $size_ori, 'spec2_name');
        $size_diff = array_diff($size_ori, array_values($size_arr));
        if (!empty($size_diff)) {
            $size_str = implode(',', $size_diff);
            return $this->format_ret(-1, '', "以下尺码在系统中不存在【{$size_str}】");
        }
        unset($size_arr, $size_diff);

        //仓库数据 store
        $store = $objData->get_single_data('store', $data, 'store_code');
        //商品编码 goods
        $goods = $objData->filter_single_data($data, 'goods_code');
        $goods = $objData->get_archives_map('goods', $goods, 2, 'supplier_code,purchase_price');
        //颜色数据 color
        $color = $objData->get_single_data('spec1', $data, 'spec1_name');

        //数据校验
        $where = []; //查询条件
        $sql_values = []; //查询条件占位符值
        $s = 1; //执行序号,用来组装查询条件
        foreach ($data as $k => &$row) {
            $err = [];
            $no = $row['no']; //excel表行号
            $planned_time = strtotime($row['planned_time']);
            if (!isset($store[$row['store_code']])) {
                $err = [$no, $row['store_code'], '系统档案中不存在此仓库代码'];
            } else if (empty($planned_time)) {
                $err = [$no, $row['planned_time'], '计划日期格式不正确'];
            } else if (!isset($goods[$row['goods_code']])) {
                $err = [$no, $row['goods_code'], '系统档案中不存在此商品编码'];
            } else if (array_search($row['spec1_name'], $color) === FALSE) {
                $err = [$no, $row['spec1_name'], '系统档案中不存在此颜色'];
            } else if (empty($goods[$row['goods_code']]['supplier_code'])) {
                $err = [$no, $row['goods_code'], '商品未绑定供应商'];
            } else if (!empty($row['price']) && !is_numeric($row['price']) || $row['price'] < 0) {
                $err = [$no, $row['price'], '价格可不填或填写大于等于0的数字'];
            }
            if (!empty($err)) {
                $err_arr[] = $err;
                unset($data[$k]);
                continue;
            }
            $row['planned_time'] = date('Y-m-d', $planned_time);
            $row['supplier_code'] = $goods[$row['goods_code']]['supplier_code'];

            $is_have_num = 0; //判断是否存在有数量的尺码
            foreach ($row['num_arr'] as $i => $num) {
                if (empty($num)) {
                    continue;
                }
                if (!is_int((int) $num) || $num < 1) {
                    $err_arr[] = [$no, $num, '数量必须为大于0的整数'];
                    continue;
                }
                $row['goods'][] = [
                    'goods_code' => $row['goods_code'],
                    'spec1_name' => $row['spec1_name'],
                    'spec2_name' => $size_ori[$i],
                    'num' => $num,
                    'price' => empty($row['price']) ? $goods[$row['goods_code']]['purchase_price'] : $row['price']
                ];
                //组装查询条件,后续用来匹配条码
                $where[] = "(goods_code=:code_{$s} AND spec1_name=:name1_{$s} AND spec2_name=:name2_{$s})";
                $values = [":code_{$s}" => $row['goods_code'], ":name1_{$s}" => $row['spec1_name'], ":name2_{$s}" => $size_ori[$i]];
                $sql_values = array_merge($sql_values, $values);

                $is_have_num ++;
                $s ++;
            }
            if ($is_have_num === 0) {
                $err_arr[] = [$no, '', '必须填写至少一个尺码数量'];
                unset($data[$k]);
                continue;
            }

            unset($row['num_arr'], $row['price'], $row['spec1_name'], $row['goods_code']);
        }
        unset($store, $goods, $color, $row);

        $goods_data = [];
        if (!empty($where)) {
            $where = implode(' OR ', $where);
            $sql = "SELECT MD5(concat(goods_code,'_',spec1_name,'_',spec2_name)) _key,goods_code,spec1_code,spec1_name,spec2_code,spec2_name,sku,barcode,COUNT(1) gcount FROM goods_sku WHERE {$where} GROUP BY goods_code,spec1_name,spec2_name";
            $goods_data = $this->db->get_all($sql, $sql_values);
            $goods_data = load_model('util/ViewUtilModel')->get_map_arr($goods_data, '_key');
        }

        $success = 0;
        $record = [];
        $detail = [];
        foreach ($data as $row) {
            $rk = "{$row['store_code']}_{$row['supplier_code']}_{$row['planned_time']}";
            $rk = md5($rk);
            foreach ($row['goods'] as $g) {
                $dk = "{$g['goods_code']}_{$g['spec1_name']}_{$g['spec2_name']}";
                $dk = md5($dk);
                if (!isset($goods_data[$dk])) {
                    $err_arr[] = [$row['no'], $g['goods_code'], "商品下不存在该颜色[{$g['spec1_name']}]和尺码[{$g['spec2_name']}]的条码"];
                    continue;
                }
                if ($goods_data[$dk]['gcount'] > 1) {
                    $err_arr[] = [$row['no'], $g['goods_code'], "商品下存在相同颜色[{$g['spec1_name']}]和尺码[{$g['spec2_name']}]名称的条码"];
                    continue;
                }
                if (isset($detail[$rk][$dk])) {
                    $detail[$rk][$dk]['num'] += $g['num'];
                } else {
                    $detail[$rk][$dk] = [
                        'goods_code' => $g['goods_code'],
                        'spec1_code' => $goods_data[$dk]['spec1_code'],
                        'spec2_code' => $goods_data[$dk]['spec1_code'],
                        'sku' => $goods_data[$dk]['sku'],
                        'num' => $g['num'],
                        'price' => sprintf("%.2f", $g['price'])
                    ];
                }
            }

            $record[$rk] = [
                'store_code' => $row['store_code'],
                'supplier_code' => $row['supplier_code'],
                'planned_time' => $row['planned_time'],
            ];

            $success ++;
        }

        if (!empty($err_arr)) {
            //错误信息按表格行号排序,导出错误
            $no_arr = array_column($err_arr, 0);
            array_multisort($no_arr, SORT_ASC, SORT_NUMERIC, $err_arr);
            $msg = $this->get_layer_err_import($err_arr);
            return $this->format_ret(-1, '', $msg);
        }
        unset($data, $row);

        //生成采购订单
        $this->begin_trans();
        $pid_arr = [];
        foreach ($record as $key => $row) {
            $record_code = $this->create_fast_bill_sn();
            $row['record_code'] = $record_code;
            $row['record_time'] = date('Y-m-d H:i:s');
            $row['is_add_time'] = date('Y-m-d H:i:s');
            $row['is_add_person'] = CTX()->get_session('user_name');
            $row['in_time'] = $row['planned_time'];
            $row['pur_type_code'] = '000';
            $row['num'] = 0;
            $row['money'] = 0;

            $ret = parent::insert($row);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '创建采购订单出错');
            }
            $pid_arr[] = $ret['data'];

            $ins_detail = $detail[$key];
            foreach ($ins_detail as &$_d) {
                $_d['record_code'] = $record_code;
                $_d['pid'] = $ret['data'];
                $_d['money'] = sprintf("%.2f", $_d['price'] * $_d['num']);
            }
            $ret = $this->insert_multi_exp('pur_planned_record_detail', $ins_detail);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '创建采购订单明细出错');
            }
        }

        //回写数量
        $pid_str = implode(',', $pid_arr);
        $sql = "UPDATE {$this->table} pr,(SELECT pid,SUM(num) num,SUM(money) money FROM pur_planned_record_detail WHERE pid IN({$pid_str}) GROUP BY pid) rd SET pr.money=rd.money,pr.num=rd.num WHERE pr.planned_record_id=rd.pid AND pr.planned_record_id IN({$pid_str})";
        $ret = $this->query($sql);
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, '', '单据数据维护出错');
        }

        $this->commit();
        //添加日志
        $log_arr = [];
        foreach ($pid_arr as $pid) {
            $log_arr[] = ['user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "创建", 'action_note' => '二维表导入创建采购订单', 'module' => "planned_record", 'pid' => $pid];
        }
        load_model('pur/PurStmLogModel')->insert_multi($log_arr);

        $record_count = count($pid_arr);
        $message = "本次操作共{$total}条数据，导入成功{$success}条，生成{$record_count}张单据";
        return $this->format_ret(1, '', $message);
    }

    /**
     * @todo 多采购订单导入
     */
    function multi_import_record($file) {
        $i = 0;
        $params = array();
        $main_info = array();
        $goods_barcode_arr = array();
        $msg = array();
        //读取excel内容
        $record_count = $this->read_multi_csv($file, $main_info, $goods_barcode_arr, $msg);
        $err_msg = $this->check_import_info($main_info, $goods_barcode_arr);
        if (empty($main_info)) {
            $msg = '导入成功0条';
            $file_name = $this->create_multi_import_fail_files($err_msg);
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $msg .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            return $this->format_ret(-1, '', $msg);
        }
        $goods_barcode = array_unique($goods_barcode_arr, SORT_REGULAR);
        $record_code = $this->create_fast_bill_sn();
        $goods_info = $this->get_goods_info_by_barcode($goods_barcode);
        //组装主表数据并写入
        foreach ($main_info as $key => $value) {
            $arr = explode('G', $record_code);
            $code = (float) $arr[1] + $i;
            $params[$key]['record_code'] = 'CG' . $code;
            ;
            $params[$key]['store_code'] = $value['store_code'];
            $params[$key]['supplier_code'] = $value['supplier_code'];
            $params[$key]['planned_time'] = $value['planned_time'];
            $params[$key]['record_time'] = date('Y-m-d');
            $params[$key]['is_add_time'] = date('Y-m-d');
            $params[$key]['is_add_person'] = CTX()->get_session('user_name');
            $params[$key]['in_time'] = $value['planned_time'];
            $params[$key]['pur_type_code'] = '000';
            foreach ($value['mx'] as $detail) {
                $params[$key]['num'] += $detail['num'];
                $price = (!empty($detail['price']) && $detail['price'] != '0.00') ? $detail['price'] : $goods_info[$detail['barcode']]['purchase_price'];
                $params[$key]['money'] += sprintf("%.2f", $price * $detail['num']);
            }
            $i++;
        }
        $this->begin_trans();
        $ret = $this->insert_multi($params);
        if ($ret['status'] < 1) {
            $this->rollback();
        }
        //组装明细表数据,并写入
        $detail = array();
        $new_pid = array();
        $j = 0;
        foreach ($main_info as $key_lof => $info) {
            foreach ($info['mx'] as $mx) {
                $data = $this->get_by_field('record_code', $params[$key_lof]['record_code'], 'planned_record_id');
                $detail[$j]['pid'] = $data['data']['planned_record_id'];
                $record_id = $data['data']['planned_record_id'];
                $new_pid[$record_id] = $info['planned_lof'];
                $detail[$j]['record_code'] = $params[$key_lof]['record_code'];
                $detail[$j]['goods_code'] = $goods_info[$mx['barcode']]['goods_code'];
                $detail[$j]['spec1_code'] = $goods_info[$mx['barcode']]['spec1_code'];
                $detail[$j]['spec2_code'] = $goods_info[$mx['barcode']]['spec2_code'];
                $detail[$j]['sku'] = $goods_info[$mx['barcode']]['sku'];
                $detail[$j]['price'] = (!empty($mx['price']) && $mx['price'] != '0.00') ? $mx['price'] : $goods_info[$mx['barcode']]['purchase_price'];
                $detail[$j]['num'] = $mx['num'];
                $detail[$j]['money'] = sprintf("%.2f", $detail[$j]['price'] * $mx['num']);
                $j++;
            }
        }
        //导入的明细条数
        $count = count($detail);
        $detail_ret = $this->insert_multi_exp('pur_planned_record_detail', $detail);
        if ($detail_ret['status'] < 1) {
            $this->rollback();
        }
        $this->commit();
        $message = '导入成功' . $count . '条';
        $err_msg = array_merge($err_msg, $msg);
        if (!empty($err_msg)) {
            $message .= '，存在导入失败的信息';
            $file_name = $this->create_multi_import_fail_files($err_msg);
            //        load_model("sys/ExportModel")->downlaod_csv($request['file_key'],$request['export_name']);
//    		$message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        foreach ($new_pid as $k => $planned_lof) {
            $log[$k] = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "多采购单导入", 'module' => "planned_record", 'action_note'=>'采购批次:'.$planned_lof, 'pid' => $k);
        }
        load_model('pur/PurStmLogModel')->insert_multi($log);
        return $this->format_ret(1, '', $message);
    }

    /**
     * @todo 校验需要导入的信息
     */
    function check_import_info(&$main_info, $goods_barcode_arr) {
        $i = 0;
        $err_msg = array();
        //获取系统中所有供应商代码
        $all_supplier = load_model('base/SupplierModel')->get_all_supplier();
        //获取系统中所有的仓库
        $all_store = load_model('base/StoreModel')->get_all_store();
        $not_exists_code = load_model('stm/StmGoodsDiyRecordDetailModel')->find_diff_barcode($goods_barcode_arr);
        //excel中的条形码全部不存在，不处理其他数据，直接返回
        if ($not_exists_code === FALSE) {
            $goods_barcode = array_unique($goods_barcode_arr, SORT_REGULAR);
            foreach ($goods_barcode as $barcode) {
                $err_msg[$i][$barcode] = '系统中不存在此商品条形码';
                $i++;
            }
            $main_info = array();
            return $err_msg;
        }
        foreach ($main_info as $key => &$info) {
            if (!in_array($info['store_code'], $all_store)) {
                $err_msg[$i][$info['store_code']] = '系统中不存在此仓库代码';
                $i++;
                unset($main_info[$key]);
                continue;
            }
            if (!in_array($info['supplier_code'], $all_supplier)) {
                $err_msg[$i][$info['supplier_code']] = '系统中不存在此供应商代码';
                $i++;
                unset($main_info[$key]);
                continue;
            }
            foreach ($info['mx'] as $k => $mx) {
                if (in_array($mx['barcode'], $not_exists_code)) {
                    $err_msg[$i][$mx['barcode']] = '系统中不存在此商品条形码';
                    $i++;
                    unset($info['mx'][$k]);
                    continue;
                }
                if (empty($mx['num'])) {
                    $err_msg[$i][$mx['barcode']] = '商品数量为空';
                    $i++;
                    unset($info['mx'][$k]);
                    continue;
                }
            }
            if (empty($info['mx'])) {
                $err_msg[$i][] = '明细信息为空';
                $i++;
                unset($main_info[$key]);
                continue;
            }
        }
        return $err_msg;
    }

    function read_multi_csv($file, &$main_info, &$goods_barcode_arr, &$msg) {
        $file = fopen($file, "r");
        $i = 0;
        $goods_barcode_arr = array();
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (empty($row[1]) && !empty($row[0])) {
                    $msg[$i][$row[4]] = '供应商代码为空';
                    $i++;
                    continue;
                }
                if (empty($row[3]) && !empty($row[0])) {
                    $msg[$i][$row[4]] = '仓库代码为空';
                    $i++;
                    continue;
                }
                if (empty($row[4]) && !empty($row[0])) {
                    $msg[$i][$row[1]] = '商品条形码为空';
                    $i++;
                    continue;
                }
                if (!empty($row[0]) && !empty($row[1]) && !empty($row[3])) {
                    $unique_flag = $row[3] . '_' . $row[1] . '_' . $row[0];
                    $main_info[$unique_flag]['planned_lof'] = $row[0];
                    $main_info[$unique_flag]['supplier_code'] = $row[1];
                    $main_info[$unique_flag]['planned_time'] = $row[2];
                    $main_info[$unique_flag]['store_code'] = $row[3];
                    $main_info[$unique_flag]['mx'][$row[4]]['barcode'] = $row[4];
                    $main_info[$unique_flag]['mx'][$row[4]]['num'] = $row[5];
                    $main_info[$unique_flag]['mx'][$row[4]]['price'] = sprintf("%.2f", $row[6]);
                    $goods_barcode_arr[] = $row[4];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    function create_multi_import_fail_files($msg) {
        $fail_top = array('错误数据', '错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg as $key => $val) {
            $key = array_keys($val);
            $val_data = array((string) $key[0], $val[$key[0]]);
            $file_str .= implode("\t,", $val_data) . "\r\n";
        }
        $filename = md5("multi_planned_record_fail" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function get_goods_info_by_barcode($barcode_arr) {
        $barcode_str = deal_array_with_quote($barcode_arr);
        $sql = "SELECT r1.*, r2.purchase_price FROM goods_sku r1
				   INNER JOIN base_goods r2 on r1.goods_code = r2.goods_code
		          where r1.barcode IN ({$barcode_str})";
        $data = $this->db->get_all($sql);
        foreach ($data as $value) {
            $new_data[$value['barcode']] = $value;
        }
        return $new_data;
    }

    /**
     * 通知财务付款
     * @param type $planned_record_id 采购订单id
     */
    function do_notify_payment($planned_record_id, $type) {
        if ($type == "payment") {
            //是否生成通知单
            $notify = load_model('pur/OrderRecordModel')->is_exists($ret['data']['record_code'], 'relation_code');
            if (!empty($notify['data'])) {
                return $this->format_ret(-1, '', '已生成通知单，不能通知付款');
            }
            $ret = $this->update(array('is_notify_payment' => 1), array('planned_record_id' => $planned_record_id));
            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未完成', 'action_name' => '通知财务付款', 'module' => "planned_record", 'pid' => $planned_record_id);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
        } else if ($type == "cancel_payment") {
            //是否已付款
            $data = $this->get_by_id($planned_record_id);
            $payment_data = load_model('pur/PaymentModel')->get_planned_or_purchaser_code($data['data']['record_code'], 2);
            if (!empty($payment_data)) {
                return $this->format_ret(-1, '', '该单据已付款或部分付款，不能取消财务付款');
            }

            $ret = $this->update(array('is_notify_payment' => 0), array('planned_record_id' => $planned_record_id));
            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未完成', 'action_name' => '取消通知财务付款', 'module' => "planned_record", 'pid' => $planned_record_id);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
        }
        return $ret;
    }

    public function add_detail($param) {
        $ret = load_model('pur/PlannedRecordDetailModel')->add_detail_action($param['record_id'], $param['detail']);
        return $ret;
    }

}
