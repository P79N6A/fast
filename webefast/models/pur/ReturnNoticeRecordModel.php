<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lib('comm_util', true);
require_lang('stm');

/**
 * 采购退货通知单业务
 */
class ReturnNoticeRecordModel extends TbModel {

    private $is_api = 0;

    function get_table() {
        return 'pur_return_notice_record';
    }

    /**
     * 替换待打印数据字段
     * @param $record
     * @param $detail
     */
    public function print_data_escape(&$record, &$detail) {
        $record['distributor_name'] = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $record['distributor_code']));
        $record['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $record['store_code']));
        $record['print_time'] = date('Y-m-d H:i:s');

        //寄送人信息
        $sql = "select * from base_store where store_code = :store_code";
        $store = $this->db->get_row($sql, array('store_code' => $record['store_code']));
        $record['shop_contact_person'] = $store['shop_contact_person'];
        $record['contact_person'] = $store['contact_person'];
        $record['sender_phone'] = $store['contact_phone'];
        $record['message'] = $store['message'];
        $record['message2'] = $store['message2'];

        //$record['finish_num'] = 0;
        foreach ($detail as $k => &$v) {
            $v['pf_price'] = $v['price'] * $v['rebate'];
            $v['shelf_code'] = $this->get_shelf_code($record['record_code'], $v['sku']);
            //$record['finish_num'] += $v['finish_num'];

            $v['category_name'] = oms_tb_val('base_category', 'category_name', array('category_code' => $v['category_code']));
            $v['brand_name'] = oms_tb_val('base_brand', 'brand_name', array('brand_code' => $v['brand_code']));
            $v['season_name'] = oms_tb_val('base_season', 'season_name', array('season_code' => $v['season_code']));
            $v['year_name'] = oms_tb_val('base_year', 'year_name', array('year_code' => $v['year']));
        }

        //差异数
        $record['diff_num'] = $record['num'] - $record['finish_num'];
    }

    /**
     * 读取库位代码
     * @param $recordCode
     * @param $sku
     * @return string
     */
    public function get_shelf_code($recordCode, $sku) {
        // and b.lof_no = a.batch_number
        $sql = "select a.shelf_code from goods_shelf a
    	inner join b2b_lof_datail b on b.store_code = a.store_code and b.sku = a.sku
    	where b.order_code = :record_code and b.order_type = 'wbm_notice' and b.sku = :sku";
        $l = $this->db->get_all($sql, array('record_code' => $recordCode, 'sku' => $sku));

        $arr = array();
        foreach ($l as $_k => $_v) {
            $arr[] = $_v['shelf_code'];
        }

        return implode(',', $arr);
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        //$sql_join = "";
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_main = "FROM {$this->table} rl
                    LEFT JOIN pur_return_notice_record_detail r2 on rl.record_code = r2.record_code
                    LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code
                    LEFT JOIN goods_sku r4 on r4.sku = r2.sku
                    WHERE 1";
        $sql_values = array();
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $sql_main .= load_model('base/SupplierModel')->get_sql_purview_supplier('rl.supplier_code', $filter_supplier_code);
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        // 单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        // 单据状态
        if (isset($filter['is_stop']) && $filter['is_stop'] != '') {
            $arr = explode(',', $filter['is_stop']);
            $str = "'" . join("','", $arr) . "'";
            $sql_main .= " AND rl.is_stop in ({$str}) ";
        }
        //商品
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        //业务日期
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $sql_main .= " AND (rl.record_time >= :record_time_start )";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql_main .= " AND (rl.record_time <= :record_time_end )";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        //下单日期
        if (isset($filter['order_time_start']) && $filter['order_time_start'] != '') {
            $sql_main .= " AND (rl.order_time >= :order_time_start )";
            $sql_values[':order_time_start'] = $filter['order_time_start'];
        }
        if (isset($filter['order_time_end']) && $filter['order_time_end'] != '') {
            $sql_main .= " AND (rl.order_time <= :order_time_end )";
            $sql_values[':order_time_end'] = $filter['order_time_end'];
        }

        // 商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND (r4.barcode LIKE :barcode )";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        //备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND (rl.remark LIKE :remark )";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
        }

        //导出
        if (isset($filter['ctl_type']) && $filter['ctl_type'] == 'export') {
            return $this->pur_return_notice_record_export($filter, $sql_main, $sql_values);
        }
        //$select = 'rl.*,r2.goods_name,r2.goods_name,r2.weight';
        $select = 'rl.*';
        $sql_main .= " group by rl.record_code order by record_time desc,record_code desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

        foreach ($data['data'] as $key => $value) {
            //$data['data'][$key]['money'] = round($value['money'], 3);
            $data['data'][$key]['diff_num'] = $value['num'] - $value['finish_num'];
            $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($value['store_code']);
            if ($wms_system_code !== FALSE) {
                $data['data'][$key]['is_wms'] = '1';
            } else {
                $data['data'][$key]['is_wms'] = '0';
            }
        }
        filter_fk_name($data['data'], array('store_code|store', 'supplier_code|supplier'));
        //dump($data,1);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 导出明细
     * @param $filter
     * @param $sql_main
     * @param $sql_values
     * @return array
     */
    function pur_return_notice_record_export($filter, $sql_main, $sql_values) {
        $select = 'rl.*,r2.sku,r2.num as detail_num,r2.money AS detail_money,r2.finish_num AS detail_finish_num,r2.price,r2.rebate as rebate_detail';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $filter['user_id']);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['price1'] = $value['price'] * $value['rebate_detail'];
            $data['data'][$key]['price1'] = round($data['data'][$key]['price1'], 3);
            $data['data'][$key]['money'] = round($value['money'], 2);
            $data['data'][$key]['is_sure_name'] = ($value['is_sure'] == 0) ? '未确认' : '已确认';
            $data['data'][$key]['is_execute_name'] = ($value['is_execute'] == 0) ? '未生成退货单' : '已生成退货单';
            $data['data'][$key]['is_stop_name'] = ($value['is_stop'] == 0) ? '未终止' : '已终止';
            $data['data'][$key]['record_time'] = date('Y-m-d', strtotime($value['record_time']));
            $key_arr = array('goods_name', 'goods_code', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'barcode',);
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $data['data'][$key] = array_merge($data['data'][$key], $sku_info);
            if ($status['status'] != 1) {
                $data['data'][$key]['money'] = '****';
                $data['data'][$key]['price1'] = '****';
                $data['data'][$key]['detail_money'] = '****';
                $data['data'][$key]['price'] = '****';
            }
        }
        //获取扩展属性
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        if ($property_power) {
            foreach ($data['data'] as &$val) {
                $goods_property = load_model('prm/GoodsModel')->get_export_property($val['goods_code']);
                $val = $goods_property != -1 && is_array($goods_property) ? array_merge($val, $goods_property) : $val;
            }
        }
        filter_fk_name($data['data'], array('store_code|store', 'supplier_code|supplier'));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('return_notice_record_id' => $id));
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        if ($status['status'] != 1 && !empty($data['data'])) {
            $data['data']['money'] = '****';
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
     * 删除记录
     * */

    function delete($return_notice_record_id) {
        $sql = "select * from {$this->table} where return_notice_record_id = :return_notice_record_id";
        $data = $this->db->get_row($sql, array(":return_notice_record_id" => $return_notice_record_id));
        if ($data['is_sure'] == 1) {
            return $this->format_ret('-1', array(), '单据已经确认，不能删除！');
        }
        $ret = parent::delete(array('return_notice_record_id' => $return_notice_record_id));
        $this->db->create_mapper('pur_return_notice_record_detail')->delete(array('pid' => $return_notice_record_id));
        $this->db->create_mapper('b2b_lof_datail')->delete(array('pid' => $return_notice_record_id, 'order_type' => 'pur_return_notice'));
        return $ret;
    }

    /*
     * 添加新纪录
     */

    function insert($stock_adjus) {
        $status = $this->valid($stock_adjus);
        if ($status < 1) {
            return $this->format_ret('-1', '', $status);
        }

        $ret = $this->is_exists($stock_adjus['record_code']);

        if (!empty($ret['data']))
            return $this->format_ret('-1', '', 'RECORD_ERROR_UNIQUE_CODE1');
//        $stock_adjus['is_add_time'] = date('Y-m-d H:i:s');
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
     *
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-10-23
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
     * 生成单据号
     */
    function create_fast_bill_sn() {
        $sql = "select  return_notice_record_id from {$this->table}   order by return_notice_record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['return_notice_record_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "CGTH" . date("Ymd") . add_zero($djh, 3);
        return $jdh;
    }

    /**
     * 编辑一条采购入库单记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-11-12
     * @param array $data
     * @param array $where
     * @return array
     */
    public function edit_action($data, $where) {
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        if (!isset($where['return_notice_record_id']) && !isset($where['record_code'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_ID_CODE');
        }
        $result = $this->get_row($where);
        if (1 != $result['status']) {
            return $this->format_ret('-1', '', 'RECORD_ERROR');
        }
//        if(1==$result['data']['is_check_and_accept']){
//            return $this->format_ret(false,array(),'单据已经验收,不能修改!');
//        }
        //修改批次相应仓库
        if (isset($data['store_code'])) {
            $ret = load_model('stm/GoodsInvLofRecordModel')->modify_store_code($data['store_code'], $result['data']['record_code']);
        }
        //更新主表数据
        return parent::update($data, $where);
    }

    function update_check_record_code($active, $field, $record_code) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $record = $this->get_row(array('record_code' => $record_code));
        $details = load_model('pur/ReturnNoticeRecordDetailModel')->get_all(array('record_code' => $record_code));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($record['data']['return_notice_record_id'], 'pur_return_notice');
        $lof_detail = &$ret_lof_details['data'];
        foreach ($lof_detail as $key => $val) {
            if ($val['num'] == 0) {
                unset($ret_lof_details['data'][$key]);
            }
        }

        $this->begin_trans();
        if (!empty($lof_detail)) {
            //释放库存
            require_model('prm/InvOpModel');
            $invobj = new InvOpModel($record['data']['record_code'], 'pur_return_notice', $record['data']['store_code'], 0, $lof_detail);

            $ret = $invobj->adjust();
            if ($ret['status'] != 1) {
                $this->rollback(); //事务回滚
                return $ret;
            }
        }

        $ret = parent:: update(array($field => $active), array('record_code' => $record_code));
        $this->commit(); //事务提交
        return $ret;
    }

    //更新字段
    function update_check($active, $field, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $details = load_model('pur/ReturnNoticeRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }

        $ret = parent:: update(array($field => $active), array('return_notice_record_id' => $id));
        return $ret;
    }

    //
    function out_relation($id) {
        $record = $this->get_row(array('return_notice_record_id' => $id));
        $record_code = $record['data']['record_code'];
        $sql = " select count(*) as cnt  from pur_return_record where  relation_code = :record_code AND   is_store_out = '0'  ";
        $arr = array(':record_code' => $record_code);
        $data = $this->db->get_all($sql, $arr);

        if (isset($data[0]['cnt']) && $data[0]['cnt'] > 0) {
            return $this->format_ret('-1', '', '存在未出库的采购退货单，是否继续');
        }
        return $this->format_ret('1');
    }

    //终止
    function update_stop($active, $field, $id, $skip_priv = 0, $skip_check = 0, $force_negative_inv = 0) {
        //#############权限
        if ($skip_priv == 0) {
            if (!load_model('sys/PrivilegeModel')->check_priv('pur/return_notice_record/do_stop')) {
                return array(
                    'status' => '-1',
                    'data' => '',
                    'message' => '无权访问'
                );
            }
        }
        //###########
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $record = $this->get_row(array('return_notice_record_id' => $id));

        if ($record['data']['is_sure'] == '0' || $record['data']['is_finish'] == '1' || $record['data']['is_stop'] == '1') {
            return $this->format_ret('-1', '', '未确认或者已终止或已完成不允许终止');
        }
        $record_code = $record['data']['record_code'];
        $details = load_model('pur/ReturnNoticeRecordDetailModel')->get_all(array('pid' => $id));

        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }

        if ($skip_check == 0) {
            $sql = " select count(*) as cnt  from pur_return_record where  relation_code = :record_code AND   is_store_out = '0'  ";
            $arr = array(':record_code' => $record_code);
            $data = $this->db->get_all($sql, $arr);
            //print_r($data);
            if (isset($data[0]['cnt']) && $data[0]['cnt'] > 0) {
                return $this->format_ret('-1', '', '存在未出库的采购退货单，不允许终止');
            }
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'pur_return_notice');

        //释放锁定库存
        require_model('prm/InvOpModel');
        $invobj = new InvOpModel($record['data']['record_code'], 'pur_return_notice', $record['data']['store_code'], 0, $ret_lof_details['data']);
        $this->begin_trans();
        if ($force_negative_inv == 1) {
            $invobj->force_negative_inv(); //强制允许负库存
        }
        $ret = $invobj->adjust();
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        $ret = parent:: update(array($field => $active), array('return_notice_record_id' => $id));
        $this->commit(); //事务提交
        return $ret;
    }

    //确认/取消确认，锁定库存/释放锁定 针对移出仓
    function update_sure($active, $field, $id) {
        //#############权限

        if (!load_model('sys/PrivilegeModel')->check_priv('pur/return_record/do_sure') && $this->is_api == 0) {
            return $this->format_ret(-1, array(), '无权访问');
        }
        //###########
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }


        $record = $this->get_row(array('return_notice_record_id' => $id));
        if ($record['data']['is_sure'] == 1 && $active == 1 && $field == 'is_sure') {
            return $this->format_ret(-1, '', '单据已确认');
        }
        if ($active == 0) {
            if ($record['data']['is_execute'] == '1' || $record['data']['is_finish'] == '1' || $record['data']['is_stop'] == '1') {
                return $this->format_ret('-1', '', '已经生成退货单或者已终止或已完成不允许取消确认');
            }
        }
        $details = load_model('pur/ReturnNoticeRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'pur_return_notice');
        //$ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id,'shift');
        if (empty($ret_lof_details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
        }

        $ret_store = load_model('base/StoreModel')->get_by_code($record['data']['store_code']);
        $allow_negative_inv = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;
        if ($allow_negative_inv == 0 && $active == 1) {
            //批次参数
            $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
            //计算缺货数 返回缺货barcode => 数量
            $info = $this->count_short_stock($ret_lof_details['data'], $lof_manage['lof_status']);
            if (!empty($info)) {
                $message = empty($info) ? '' : $info;
                return $this->format_ret('-2', '', $message);
            }
        }
        //库存操作锁库存
        require_model('prm/InvOpModel');
        $this->begin_trans();

        if ($active == 1) {
            $invobj = new InvOpModel($record['data']['record_code'], 'pur_return_notice', $record['data']['store_code'], 1, $ret_lof_details['data']);
            $ret = $invobj->adjust();
            if ($ret['status'] == -10) {//锁定批次库存不足
                if (!empty($invobj->check_data['adjust_record_info']) && $lof_manage['lof_status'] == 0) {//关闭掉批次情况
                    $this->rollback();
                    $ret = $invobj->adjust_lock_record($invobj->check_data['adjust_record_info']); //调整锁定批次
                    if ($ret['status'] != 1) {
                        return $ret;
                    }
                    $this->begin_trans();
                    $ret = $invobj->adjust(); //重新提交
                } else {
                    $this->rollback();
                    return $ret;
                }
            }

            if ($ret['status'] > 0 && $field == 'is_sure') {
                $ret = load_model('wms/WmsEntryModel')->add($record['data']['record_code'], 'pur_return_notice', $record['data']['store_code']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                } else {
                    $ret['status'] = 1;
                }
            }
        }

        if ($active == 0) {
            $invobj = new InvOpModel($record['data']['record_code'], 'pur_return_notice', $record['data']['store_code'], 0, $ret_lof_details['data']);
            //$this->begin_trans();
            $ret = $invobj->adjust();
            if ($ret['status'] > 0 && $field == 'is_sure') {
                $ret = load_model('wms/WmsEntryModel')->cancel($record['data']['record_code'], 'pur_return_notice', $record['data']['store_code']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                } else {
                    $ret['status'] = 1;
                }
            }
        }




        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }

        $ret = parent:: update(array($field => $active), array('return_notice_record_id' => $id));
        //$ret= parent:: update(array('is_store_out' => 1), array('return_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        $this->commit(); //事务提交
        return $ret;
    }

    //计算缺库存数
    function count_short_stock($ret, $loft_status) {
        $err_arr = array();
        foreach ($ret as $value) {
            $store_code = $value['store_code'];
            $num = $value['init_num'];
            $sku = $value['sku'];
            $data2 = $this->db->get_row("select barcode from goods_sku where sku = :sku ", array(':sku' => $sku));
            $barcode = $data2['barcode'];
            if ($loft_status == 1) {
                $lof_no = $value['lof_no'];
                $sql4 = "select stock_num,lock_num from goods_inv_lof where sku = :sku and store_code = :store_code and lof_no = :lof_no ";
                $data4 = $this->db->get_row($sql4, array(':sku' => $sku, ':store_code' => $store_code, ':lof_no' => $lof_no));
                $can_use_inv = $data4['stock_num'] - $data4['lock_num'];
                $short_inv = $num >= $can_use_inv ? $num - $can_use_inv : 0;
                if ($short_inv > 0) {
                    $err_arr[$barcode] = $short_inv;
                }
            } else {
                $sql3 = "select stock_num,lock_num from goods_inv where sku = :sku and store_code = :store_code ";
                $data3 = $this->db->get_row($sql3, array(':sku' => $sku, ':store_code' => $store_code));
                $can_use_inv = $data3['stock_num'] - $data3['lock_num'];
                $short_inv = $num >= $can_use_inv ? $num - $can_use_inv : 0;
                if ($short_inv > 0) {
                    $err_arr[$barcode] = $short_inv;
                }
            }
        }
        return $err_arr;
    }

    //判断完成
    function update_finish($record_code) {
        $sql = " select count(*) as cnt  from pur_return_notice_record_detail where  record_code = :record_code AND   finish_num >= num  ";
        $arr = array(':record_code' => $record_code);
        $data = $this->db->get_all($sql, $arr);
        $sql = "select count(*) as cnt  from pur_return_notice_record_detail where  record_code = :record_code  ";
        $data1 = $this->db->get_all($sql, $arr);
        if (isset($data[0]['cnt']) && isset($data1[0]['cnt']) && ( $data[0]['cnt'] == $data1[0]['cnt'] )) {
            $ret['status'] = '1';
            $ret['data'] = '';
            $ret['message'] = '';

            return $ret;
        } else {
            $ret['status'] = '0';
            $ret['data'] = '';
            $ret['message'] = '有未完成明细';

            return $ret;
        }
    }

    function imoprt_detail($id, $file, $is_lof = 0) {
        //     	var_dump($id);var_dump($file);var_dump($is_lof);exit;
        $ret = $this->get_row(array('return_notice_record_id' => $id));
        if (empty($ret)) {
            return array(-1, '', '采购退货单通知单不存在！');
        }
        $store_code = $ret['data']['store_code'];
        $sku_arr = $sku_num = array();
        $error_msg = array();
        $err_num = 0;
        //未开启批次导入库存方法
        if ($is_lof == '1') {
            $num = $this->read_csv_lof($file, $sku_arr, $sku_num);
        } else {
            $num = $this->read_csv_sku($file, $sku_arr, $sku_num);

            //没有开启批次的 查询默认批次 如果批次表里 没有，在批次表里添加goods_lof
            $sku_str = implode("','", $sku_arr);
            $sql = "select sku,lof_no,production_date,type from goods_lof where sku in ('$sku_str') group by sku";
            $sku_data = $this->db->get_all($sql);

            $sql_moren = "select lof_no,production_date from goods_lof order by id ASC";
            $moren = $this->db->get_row($sql_moren);
            $sku_data_new = array();
            foreach ($sku_data as $key2 => $data) {
                $sku_data_new[$data['sku']]['production_date'] = $data['production_date'];
                $sku_data_new[$data['sku']]['lof_no'] = $data['lof_no'];
            }
            $new_sku_num = $sku_num;
            $sku_num = array();
            foreach ($sku_arr as $key1 => $sku) {
                if (array_key_exists($sku, $sku_data_new)) {
                    $sku_num[$sku][$sku_data_new[$sku]['lof_no']]['num'] = $new_sku_num[$sku]['num'];
                    $sku_num[$sku][$sku_data_new[$sku]['lof_no']]['purchase_price'] = $new_sku_num[$sku]['purchase_price'];
                    //$sku_num[$sku][$sku_data_new[$sku]['lof_no']]['lof_no'] = $sku_data_new[$sku]['lof_no'];
                    //$sku_num[$sku][$sku_data_new[$sku]['lof_no']]['production_date'] = $sku_data_new[$sku]['production_date'];
                } else {
                    $sku_num[$sku][$moren['lof_no']]['num'] = $new_sku_num[$sku]['num'];
                    $sku_num[$sku][$moren['lof_no']]['purchase_price'] = $new_sku_num[$sku]['purchase_price'];
                    //$sku_num[$sku][$moren['lof_no']]['lof_no'] = $moren['lof_no'];
                    //$sku_num[$sku][$moren['lof_no']]['production_date'] = $moren['production_date'];
                }
            }
        }
        $sku_count = count($sku_arr);

        if (!empty($sku_num) && !empty($sku_arr)) {
            $sql_values = array();
            $sku_str = $this->arr_to_in_sql_value($sku_arr, 'barcode', $sql_values);
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.sku,g.price,g.purchase_price,g.sell_price from
    		goods_sku b
    		inner join base_goods g ON g.goods_code = b.goods_code
    		where b.barcode in({$sku_str}) ";
            $detail_data = $this->db->get_all($sql, $sql_values); //sell_price
            $detail_data_lof = array();
            $temp = array();
            //     	if($is_lof == '1'){
            foreach ($detail_data as $key => $val) {
                foreach ($sku_num[$val['barcode']] as $k1 => $v1) {
                    if (intval($v1['num']) > 0) {
                        $val['num'] = $v1['num'];
                        $val['lof_no'] = $v1['lof_no'];
                        $val['production_date'] = $v1['production_date'];
                        $val['pici_lof'] = '1';
                        if ($v1['purchase_price']) {
                            $val['purchase_price'] = $v1['purchase_price'];
                        }
                        $detail_data_lof[] = $val;
                        unset($sku_num[$val['barcode']]);
                    } else {
                        $error_msg[] = array($val['barcode'] => '数量不能为空');
                        $err_num ++;
                        unset($sku_num[$val['barcode']]);
                    }
                }
            }
            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $detail_data_lof);
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'pur_return_notice', $detail_data_lof);
            if ($ret['status'] == 1) {
                //入库单明细添加
                $ret = load_model('pur/ReturnNoticeRecordDetailModel')->add_detail_action($id, $detail_data_lof);
            } else if ($ret['status'] == -3) {
                $data = $ret['data'];
                foreach ($detail_data_lof as $val) {
                    $k = $store_code . ',' . $val['sku'];
                    //有库存就添加明细
                    if (!isset($data[$k])) {
                        //入库单明细添加
                        $ret = load_model('pur/ReturnNoticeRecordDetailModel')->add_detail_action($id, array($val));
                        //单据批次添加
                        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'pur_return_notice', array($val));
                        if ($ret['status'] < 1) {
                            return $ret;
                        }
                    }
                }
                foreach ($data as $val) {
                    $error_msg[] = array($val['barcode'] => '商品库存不足');
                    $err_num ++;
                }
            } else {
                return $ret;
            }

            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '导入明细', 'module' => "return_record", 'pid' => $id);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
            $ret['data'] = '';
        }

        if (!empty($sku_num)) {
            $sku_error = array_keys($sku_num);
            foreach ($sku_error as $err) {
                $error_msg[] = array($err => '系统不存在该条码信息');
                $err_num ++;
            }
        }

        $success_num = $sku_count - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
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
            $val_data = array($key[0], $val[$key[0]]);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("return_record_notice_import" . time());
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
                    $sku_num[$row[0]]['purchase_price'] = $row[2];
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
                    $sku_arr[] = $row[0];
                    //     				$keypi = $row[0].'_'.$row[2];
                    $sku_num[$row[0]][$row[3]]['num'] = $row[1];
                    $sku_num[$row[0]][$row[3]]['purchase_price'] = $row[2];
                    $sku_num[$row[0]][$row[3]]['lof_no'] = $row[3];
                    $sku_num[$row[0]][$row[3]]['production_date'] = $row[4];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
//                $val = iconv('gbk', 'utf-8', $val); 中文转码后变false
//                $val = mb_convert_encoding($val,'utf-8','gbk'); 中文转码后变乱码
                $val = trim(str_replace('"', '', $val));
                //   $row[$key] = $val;
            }
        }
    }

    /**
     * 检查采购退货通知单状态
     */
    function check_status($return_notice_record) {
        $record_detail = load_model('pur/ReturnNoticeRecordDetailModel')->is_exists_detail($return_notice_record['record_code'], 'record_code');
        if (empty($return_notice_record) || empty($record_detail['data'])) {
            return $this->format_ret(-1, '', '采购退货通知单信息不存在！');
        }
        if ($return_notice_record['is_sure'] == 0) {
            return $this->format_ret(-1, '', '未确认采购退货通知单不能生成采购退货单！');
        }
        if ($return_notice_record['is_finish'] == 1) {
            return $this->format_ret(-1, '', '已完成采购退货通知单不能生成采购退货单！');
        }
        return $this->format_ret(1);
    }

    /**
     * 采购退货通知单生成采购退货单
     */
    public function create_return_record($return_notice) {
        $return_notice_record = $this->get_by_id($return_notice['return_notice_record_id']);
        $ret = $this->check_status($return_notice_record['data']);
        if ($ret['status'] == 1) {
            $ret = load_model('pur/ReturnRecordModel')->create_return_record_new($return_notice_record['data'], $return_notice['create_type']);
            if ($ret['status'] == 1) {
                $ret1 = $this->update_check('1', 'is_execute', $return_notice['return_notice_record_id']);
            }
        }
        if ($ret['status'] == '1') {
            //日志
            $record = load_model('pur/ReturnRecordModel')->get_row(array('return_record_id' => $ret['data']));
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未完成', 'action_name' => '生成采购退货单', 'module' => "pur_return_notice_record", 'pid' => $return_notice['return_notice_record_id'], 'action_note' => "退货单号({$record['data']['record_code']})");
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
    }

    /**
     * API-创建采购退货通知单
     * @author wmh
     * @date 2016-11-29
     * @param array $param
     * <pre> 必选: 'store_code','supplier_code','record_time','rebate'
     * <pre> 可选: 'remark'
     * @return array 操作结果
     */
    public function api_return_notice_create($param) {
        $error_data = array();
        try {
            $key_required = array(
                's' => array('record_time', 'store_code', 'supplier_code', 'rebate'),
            );
            $data = array();
            $ret_require = valid_assign_array($param, $key_required, $data, TRUE);
            if ($ret_require['status'] === FALSE) {
                $error_data = $ret_require['req_empty'];
                throw new Exception('缺少必填参数或必填参数为空', '-10001');
            }
            $data['remark'] = isset($param['remark']) ? str_replace(array("\r\n", "\r", "\n"), '', trim($param['remark'])) : '';
            unset($param);

            if (strtotime($data['record_time']) === FALSE) {
                $error_data = array('record_time' => $data['record_time']);
                throw new Exception('日期格式不正确', '-10005');
            }
            $data['record_time'] = format_day_time($data['record_time']);
            if (!is_numeric($data['rebate']) || $data['rebate'] < 0 || $data['rebate'] > 1) {
                $error_data = array('rebate' => $data['rebate']);
                throw new Exception('折扣必须为0-1的数字(例0.5)', '-10005');
            }
            $fld = array('supplier_code' => '供应商', 'store_code' => '仓库');
            $check_arr = array(
                array('base_supplier', 'supplier_code', $data['supplier_code'], 'supplier_code'),
                array('base_store', 'store_code', $data['store_code'], 'store_code')
            );
            $ret = $this->is_exists_data($check_arr);
            if ($ret !== TRUE) {
                $error_data = array($ret => $data[$ret]);
                throw new Exception($fld[$ret] . '不存在', '-10002');
            }

            $data['record_code'] = $this->create_fast_bill_sn();
            $data['order_time'] = date('Y-m-d H:i:s');

            $ret = parent::insert($data);
            $affect_row = $this->affected_rows();
            if ($ret['status'] != 1 || $affect_row != 1) {
                throw new Exception('创建失败', '-1');
            }
            //日志
            $log = array('user_id' => 1, 'user_code' => 'admin', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "创建", 'module' => "pur_return_notice_record", 'pid' => $ret['data'], 'action_note' => 'API-创建单据');
            load_model('pur/PurStmLogModel')->insert($log);

            return $this->format_ret(1, $data['record_code'], '创建成功');
        } catch (Exception $e) {
            $msg = $e->getCode() == 0 ? '创建失败' : $e->getMessage();
            return $this->format_ret($e->getCode(), $error_data, $msg);
        }
    }

    /**
     * API-更新采购退货通知单明细
     * @author wmh
     * @date 2016-11-29
     * @param array $param
     * <pre> 必选: 'record_code','detail'
     * @return array 操作结果
     */
    public function api_return_notice_update($param) {
        $error_data = array();
        try {
            if (!isset($param['record_code']) || empty($param['record_code'])) {
                $error_data = array('record_code');
                throw new Exception('缺少必填参数或必填参数为空', '-10001');
            }
            if (!isset($param['detail']) || empty($param['detail'])) {
                $error_data = array('detail');
                throw new Exception('缺少必填参数或必填参数为空', '-10001');
            }
            $record_code = $param['record_code'];
            $detail = json_decode($param['detail'], true);
            if (empty($detail)) {
                throw new Exception('明细数据异常', '-10001');
            }
            unset($param);
            //检查明细是否为空
            $error_data = $this->api_check_detail($detail, array('barcode', 'num'));
            if (!empty($error_data)) {
                throw new Exception('明细数据不能为空', '-10001');
            }

            $record = $this->is_exists($record_code);
            if ($record['status'] != 1 || empty($record['data'])) {
                $error_data = array('record_code' => $record_code);
                throw new Exception('采购退货通知单不存在', '-10002');
            }
            $record = $record['data'];
            $pid = $record['return_notice_record_id'];

            $barcode_num = array_column($detail, 'num', 'barcode');
            $barcode_arr = array_column($detail, 'barcode');
            $barcode_str = "'" . implode("','", $barcode_arr) . "'";
            $sql = "SELECT bg.goods_code,bg.purchase_price,gs.sku,gs.barcode,gs.spec1_code,gs.spec2_code,gs.cost_price FROM base_goods AS bg INNER JOIN goods_sku AS gs ON bg.goods_code=gs.goods_code WHERE gs.barcode IN({$barcode_str})";
            $detail = $this->db->get_all($sql);

            $barcode_exists = array_column($detail, 'barcode');
            $error_data = array_diff($barcode_arr, $barcode_exists);
            if (!empty($error_data)) {
                throw new Exception('商品条形码不存在', '-10002');
            }
            foreach ($detail as &$val) {
                $num = $barcode_num[$val['barcode']];
                if (!is_int((int) $num) || $num < 1) {
                    $error_data = array($val['barcode'] => $num);
                    throw new Exception('数量必须为正整数', '-10005');
                }
                $val['rebate'] = $record['rebate'];
                $val['num'] = $num;
                unset($val['barcode']);
            }
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($pid, $detail);
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $record['store_code'], 'pur_return_notice', $detail);
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }
            $ret = load_model('pur/ReturnNoticeRecordDetailModel')->add_detail_action($pid, $detail);
            if ($ret['status'] != 1) {
                throw new Exception($ret['message'], $ret['status']);
            }

            //日志
            $log = array('user_id' => 1, 'user_code' => 'admin', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "更新明细", 'module' => "pur_return_notice_record", 'pid' => $record['return_notice_record_id'], 'action_note' => 'API-更新明细');
            load_model('pur/PurStmLogModel')->insert($log);
            $ret['message'] = '更新成功';
            return $ret;
        } catch (Exception $e) {
            $msg = $e->getCode() == 0 ? '更新失败' : $e->getMessage();
            return $this->format_ret($e->getCode(), $error_data, $msg);
        }
    }

    /**
     * 确认采购退货通知单
     * @author wmh
     * @date 2016-11-29
     * @param array $param
     * <pre> 必选: 'record_code'
     * @return array 操作结果
     */
    public function api_return_notice_confirm($param) {
        $error_data = array();
        try {
            if (!isset($param['record_code']) || empty($param['record_code'])) {
                $error_data = array('record_code');
                throw new Exception('缺少必填参数或必填参数为空', '-10001');
            }
            $code_arr = json_decode($param['record_code'], true);
            if (!is_array($code_arr)) {
                $code_arr = array($param['record_code']);
            }
            if (empty($code_arr)) {
                throw new Exception('单据编号格式有误', '-10005');
            }
            $code_str = "'" . implode("','", $code_arr) . "'";
            $sql = "SELECT return_notice_record_id AS id,record_code FROM pur_return_notice_record WHERE record_code IN($code_str)";
            $record = $this->db->get_all($sql);
            $code_exists = array_column($record, 'record_code');
            $code_diff = array_diff($code_arr, $code_exists);
            foreach ($code_diff as $val) {
                $error = array();
                $error['status'] = '-10002';
                $error['data'] = $val;
                $error['message'] = '采购退货通知单不存在';
                $error_data[] = $error;
            }
            $this->is_api = 1;
            array_walk($record, function($val) use(&$error_data) {
                $ret = $this->update_sure(1, 'is_sure', $val['id']);
                if ($ret['status'] == 1) {
                    //日志
                    $log = array('user_id' => '1', 'user_code' => 'admin', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未完成', 'action_name' => '确认', 'module' => "pur_return_notice_record", 'pid' => $val['id'], 'action_note' => 'API-确认单据');
                    load_model('pur/PurStmLogModel')->insert($log);
                    $ret['message'] = '确认成功';
                }
                $ret['data'] = $val['record_code'];
                $error_data[] = $ret;
            });
            $this->is_api = 0;
            return $this->format_ret(1, $error_data);
        } catch (Exception $e) {
            $msg = $e->getCode() == 0 ? '确认失败' : $e->getMessage();
            return $this->format_ret($e->getCode(), $error_data, $msg);
        }
    }

    /**
     * 检查数据是否存在
     * @param array $arr 数据：array(array('表名','字段名','字段值','映射字段名'))
     * @return boolean 存在返回true，不存在返回映射字段名
     */
    private function is_exists_data($arr) {
        foreach ($arr as $val) {
            $sql = "SELECT count(1) FROM {$val[0]} WHERE {$val[1]}=:{$val[1]}";
            $ret = $this->db->get_value($sql, array(":{$val[1]}" => $val[2]));
            if ($ret < 1) {
                return $val[3];
            }
        }
        return TRUE;
    }

    /**
     * 检查接口传入明细信息是否为空
     */
    private function api_check_detail($detail, $check_key) {
        $err_data = array();
        foreach ($detail as $key => $val) {
            foreach ($check_key as $v) {
                if (empty($val[$v])) {
                    $err_data[$key][] = $v;
                }
            }
        }
        return $err_data;
    }

    public function add_detail($param) {
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($param['record_id'], $param['detail']);
        if ($ret['status'] != '1') {
            return $ret;
        }
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($param['record_id'], $param['store_code'], 'pur_return_notice', $param['detail']);
        if ($ret['status'] != '1') {
            return $ret;
        }
        $ret = load_model('pur/ReturnNoticeRecordDetailModel')->add_detail_action($param['record_id'], $param['detail']);
        if ($ret['status'] == '1') {
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '添加明细', 'module' => "pur_return_notice_record", 'pid' => $param['record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        $ret = load_model('pur/ReturnNoticeRecordDetailModel')->add_detail_action($param['record_id'], $param['detail']);
        return $ret;
    }

}
