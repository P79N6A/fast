<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

/**
 * 有货采购单
 * Class YohoPurchaseModel
 */
class YohoPurchaseModel extends TbModel {

    protected $table = "api_youhuo_purchase_record";
    protected $detail_table = "api_youhuo_purchase_record_detail";
    public $is_wms = false;

    /**
     * 列表数据
     * @param $filter
     * @return array
     */
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = '';
        $sql_values = array();
        //导出
        if ($filter['ctl_type'] == 'export') {
            $sql_join = " LEFT JOIN {$this->detail_table} AS r2 ON r1.purchase_no=r2.purchase_no ";
        }
        $sql_main = "FROM {$this->table} AS r1 {$sql_join} WHERE 1";
        //商店权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('r1.shop_code', $filter_shop_code);

        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND r1.shop_code in ({$str}) ";
        }
        //采购单号
        if (isset($filter['purchase_no']) && $filter['purchase_no'] != '') {
            $sql_main .= " AND r1.purchase_no = :purchase_no ";
            $sql_values[':purchase_no'] = $filter['purchase_no'];
        }
        //批发通知单
        if ($filter['notice_record_code'] && $filter['notice_record_code'] != '') {
            $sql = "select purchase_no from api_youhuo_store_out_record where notice_record_code=:notice_record_code";
            $sql_value[':notice_record_code'] = $filter['notice_record_code'];
            $purchase_data = $this->db->get_all($sql, $sql_value);
            if (!empty($purchase_data)) {
                $purchase_no_arr = array_column($purchase_data, 'purchase_no');
                $purchase_no_str = $this->arr_to_in_sql_value($purchase_no_arr, 'purchase_no', $sql_values);
                $sql_main .= " AND r1.purchase_no in ({$purchase_no_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }

        //是否生成通知单
        if (isset($filter['is_execute']) && $filter['is_execute'] != '') {
            $sql_main .= " AND r1.is_execute = :is_execute ";
            $sql_values[':is_execute'] = $filter['is_execute'];
        }
        //创建时间，时间戳格式
        if (isset($filter['create_time_start']) && $filter['create_time_start'] !== '') {
            $sql_main .= " AND r1.create_time >=:create_time_start ";
            $sql_values[':create_time_start'] = strtotime($filter['create_time_start']);
        }
        //业务日期-结束
        if (isset($filter['create_time_end']) && $filter['create_time_end'] !== '') {
            $sql_main .= " AND r1.create_time <=:create_time_end ";
            $sql_values[':create_time_end'] = strtotime($filter['create_time_end']);
        }
        if ($filter['ctl_type'] == 'export') {
            $select = 'r1.*,r2.factory_code,r2.numbers AS sku_numbers,r2.deliver_num AS sku_deliver_num,r2.purchase_price AS sku_purchase_price';
        } else {
            $select = 'r1.*';
        }
        $sql_main .= " ORDER BY r1.create_time desc ";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            $value['no_deliver_num'] = $value['numbers'] - $value['deliver_num'];
            $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
        }
        filter_fk_name($data['data'], array('shop_code|shop',));
        $ret_data = $data;

        return $this->format_ret(1, $ret_data);
    }


    /**
     * 通过field_name查询
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
            return $this->format_ret('-1', '', '获取数据失败！');
        }
    }


    /**
     * 明细数据
     * @param $filter
     * @return array
     */
    function get_goods_by_page($filter) {
        $sql_main = "FROM {$this->detail_table}  WHERE 1";
        $sql_values = array();
        //拣货单号
        if (isset($filter['purchase_no']) && $filter['purchase_no'] != '') {
            $sql_main .= " AND purchase_no = :purchase_no ";
            $sql_values[':purchase_no'] = $filter['purchase_no'];
        }
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            $value['no_deliver_num'] = $value['numbers'] - $value['deliver_num'];
        }
        return $this->format_ret(1, $data);
    }

    /**
     *
     * @param $pick_id
     * @return array
     */
    function check_purchase($purchase_id) {
        //校验待发货数是否大于发货数
        $purchase_ret = $this->get_by_id($purchase_id);
        if ($purchase_ret['data']['numbers'] <= $purchase_ret['data']['deliver_num']) {
            return $this->format_ret(-1, '', '采购单的全部商品已发货');
        }
        $purchase_no = $purchase_ret['data']['purchase_no'];
        //通知数大于0标识该采购单已生成过销货单
        if ($purchase_ret['data']['notice_num'] > 0) {
            //是否存在未验收单的批发销货单
            $sql = "select a.store_out_record_code from api_youhuo_store_out_record a,wbm_store_out_record b where a.store_out_record_code=b.record_code and a.purchase_no='{$purchase_no}' and is_store_out=0";
            $recode_codes = $this->db->get_col($sql);
            if (!empty($recode_codes)) {
                $code_str = implode(',', $recode_codes);
                return $this->format_ret(-1, '', '还有未完成的销货单' . $code_str);
            }
        }
        return $this->format_ret(1, '');
    }

    //根据id查询
    function get_by_id($id) {
        return $this->get_row(array('id' => $id));
    }


    /**
     * 创建批发通知单、批发销货单
     * @param array $out_params 页面参数
     * @return array 创建结果
     */
    function create($out_params) {
        $this->begin_trans();
        //条码识别
        $ret = $this->convert_barcode_to_sku($out_params['purchase_id']);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $this->is_wms = load_model('api/WeipinhuijitPickModel')->check_is_wms_store_code($out_params['store_code']);
        $ret = $this->purchase_create_by_unrelation_notice($out_params['purchase_id'], $out_params);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //回写生成状态
        $ids = explode(',', $out_params['purchase_id']);
        $sql_values = array();
        $ids_str = $this->arr_to_in_sql_value($ids, 'id', $sql_values);
        $sql = "UPDATE {$this->table} SET is_execute=1 WHERE id IN ({$ids_str})";
        $ret_purchase = $this->db->query($sql, $sql_values);
        if ($ret_purchase != true) {
            $this->rollback();
            return $this->format_ret(-1, '', '生成状态更新失败');
        }
        //创建出库单
        $notice_record_code = $ret['data']['notice_record_code'];
        $ret = $this->set_jit_delivery($notice_record_code, $out_params);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        return $this->format_ret('1', '', '创建成功！');
    }


    /**
     * 转换条码/子条码为SKU
     * @param string 采购单ID，多个逗号隔开
     * @return array 处理结果
     */
    function convert_barcode_to_sku($purchase_id) {
        $purchase_arr = $this->get_by_ids($purchase_id);
        $purchase_no_arr = array_column($purchase_arr['data'], 'purchase_no');
        $sql_values = array();
        $purchase_no_str = $this->arr_to_in_sql_value($purchase_no_arr, 'purchase_no', $sql_values);
        $sql = "SELECT purchase_no,factory_code FROM {$this->detail_table} WHERE purchase_no IN({$purchase_no_str})";
        $purchase_detail = $this->db->get_all($sql, $sql_values);
        if (empty($purchase_detail)) {
            return $this->format_ret(-1, '', '采购单明细为空');
        }
        $barcode_arr = array_column($purchase_detail, 'factory_code');
        $ret = load_model('prm/SkuModel')->convert_barcode($barcode_arr);
        if (empty($ret['data'])) {
            return $this->format_ret(-1, '', '条码在系统中不存在!');
        }
        $convert_barcode = $ret['data'];
        //更新sku
        foreach ($purchase_detail as &$value) {
            $barcode = strtolower($value['factory_code']);
            $value['sku'] = isset($convert_barcode[$barcode]['sku']) ? $convert_barcode[$barcode]['sku'] : '';
        }
        $update_str = "sku = VALUES(sku)";
        $ret = $this->insert_multi_duplicate('api_youhuo_purchase_record_detail', $purchase_detail, $update_str);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '识别条码失败');
        }
        return $this->format_ret(1);
    }


    /**
     * 根据采购单id，获取信息
     * @param $pick_ids
     * @return array
     */
    function get_by_ids($purchase_ids) {
        $purchase_ids_arr = explode(",", $purchase_ids);
        $sql_values = array();
        $purchase_ids_str = $this->arr_to_in_sql_value($purchase_ids_arr, 'id', $sql_values);
        $sql = " SELECT * FROM {$this->table} WHERE id IN({$purchase_ids_str}); ";
        $data = $this->db->get_all($sql, $sql_values);
        return $this->format_ret(1, $data);
    }

    function purchase_create_by_unrelation_notice($purchase_id, $out_params) {
        //创建通知单
        $ret = $this->create_notice_record($purchase_id, $out_params['store_code'], $out_params);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $notice_record = $ret['data']['notice_record'];
        //仓库对接了wms,只生成批发销货单
        if ($this->is_wms === TRUE) {
            //更新绑定关系
            $result = $this->update_yoho_store_out($purchase_id, $notice_record);
            if ($result['status'] != 1) {
                return $this->format_ret('-1', '', '更新关联表失败！');
            }
            return $this->format_ret('1', array('notice_record_code' => $notice_record), '仓库对接了wms，只能生成批发通知单！');
        } else {
            //创建批发销货单
            $ret = $this->create_out_record($purchase_id, $notice_record, $out_params);
            //更新绑定关系
            $result = $this->update_yoho_store_out($purchase_id, $notice_record, $ret['data']);
            if ($result['status'] != 1) {
                return $this->format_ret('-1', '', '更新关联表失败！');
            }
            return $this->format_ret('1', array('notice_record_code' => $notice_record, 'store_out_record_code' => $ret['data']), '更新关联表成功！');
        }
    }


    /**
     *更新有货的绑定关系表
     * @param $purchase_id
     * @param $notice_record
     * @param string $store_out_code
     */
    function update_yoho_store_out($purchase_id, $notice_record, $store_out_record = '') {
        $purchase = $this->get_by_ids($purchase_id);
        $out_record_data = array();
        foreach ($purchase['data'] as $purchase_row) {
            //删除垃圾数据
            $sql = "delete from api_youhuo_store_out_record where notice_record_code=:notice_record_code AND purchase_no=:purchase_no ";
            $sql_values = array(':notice_record_code' => $notice_record, ':purchase_no' => $purchase_row['purchase_no']);
            $this->db->query($sql, $sql_values);
            //更新拣货单销货单表关联关系is_execute
            $out_record_data[] = array(
                'shop_code' => $purchase_row['shop_code'],
                'purchase_no' => $purchase_row['purchase_no'],
                'notice_record_code' => $notice_record,
                'store_out_record_code' => $store_out_record,
            );
        }
        $ret = $this->insert_multi_exp('api_youhuo_store_out_record', $out_record_data);
        return $ret;
    }


    /**
     * 创建通知单
     * @param $pick_ids
     * @param $store_code
     * @param $out_params
     * @return array
     */
    function create_notice_record($purchase_ids, $store_code, $out_params) {
        $ret = $this->add_notice_check($purchase_ids, $store_code);
        if ($ret['status'] == -1) {
            return $ret;
        }
        $notice_goods = $ret['data']['notice_goods'];
        $purchase_no_str = $ret['data']['purchase_no_str'];
        //通知单主单信息
        $record_code = load_model('wbm/NoticeRecordModel')->create_fast_bill_sn();
        $notice_row['record_code'] = $record_code;
        $notice_row['order_time'] = date('Y-m-d H:i:s');
        $notice_row['record_time'] = date('Y-m-d H:i:s');
        $notice_row['distributor_code'] = $out_params['distributor_code'];
        $ret_distributor = load_model('base/CustomModel')->get_by_code($out_params['distributor_code']);
        $ret_distributor = $ret_distributor['data'];
        $notice_row['address'] = empty($ret_distributor['address']) ? '' : $ret_distributor['address'];
        $notice_row['province'] = empty($ret_distributor['province']) ? '' : $ret_distributor['province'];
        $notice_row['city'] = empty($ret_distributor['city']) ? '' : $ret_distributor['city'];
        $notice_row['district'] = empty($ret_distributor['district']) ? '' : $ret_distributor['district'];
        $notice_row['name'] = $ret_distributor['contact_person'];
        $notice_row['tel'] = empty($ret_distributor['mobile']) ? '' : $ret_distributor['mobile'];
        $notice_row['store_code'] = $store_code;
        $notice_row['rebate'] = 1;
        $notice_row['remark'] = "由有货采购单:[{$purchase_no_str}]生成";
        $this->begin_trans();
        $ret = load_model('wbm/NoticeRecordModel')->insert($notice_row);
        if ($ret['status'] == -1) {
            return $this->format_ret(-1, '', '批发通知单生成失败' . $ret['message']);
        }
        $notice_id = $ret['data'];
        foreach ($notice_goods as &$row) {
            $row['pid'] = $notice_id;
            $row['record_code'] = $record_code;
        }
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($notice_id, $notice_goods);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($notice_id, $store_code, 'wbm_notice', $notice_goods);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }
        //添加商品明细
        $ret = load_model('wbm/NoticeRecordDetailModel')->add_detail_action($notice_id, $notice_goods);
        if ($ret['status'] == -1) {
            $this->rollback();
            return $this->format_ret(-1, '', '批发通知单生成失败' . $ret['message']);
        }
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未出库', 'action_name' => "创建", 'action_note' => '有货JIT，' . $notice_row['remark'], 'module' => "wbm_notice_record", 'pid' => $notice_id);
        load_model('pur/PurStmLogModel')->insert($log);

        //审核批发通知单
        $ret = load_model('wbm/NoticeRecordModel')->update_sure(1, 'is_sure', $notice_id);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '批发通知单审核失败，' . $ret['message']);
        }
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未出库', 'action_name' => '确认', 'module' => "wbm_notice_record", 'pid' => $notice_id);
        load_model('pur/PurStmLogModel')->insert($log);
        $this->commit();
        return $this->format_ret(1, array('notice_record' => $record_code));
    }

    /**
     *校验
     * @param $purchase_ids
     * @param null $notice_record_no
     * @param null $store_code
     * @return array
     */
    function add_notice_check($purchase_ids, $store_code = null) {
        $purchase_ret = $this->get_by_ids($purchase_ids);
        $purchase_no_all = array_column($purchase_ret['data'], 'purchase_no');
        $purchase_no_str = implode(',', $purchase_no_all);
        //校验采购单明细
        $purchase_goods = $this->get_detail_by_purchase_no($purchase_no_all);
        //采购单明细为空
        if (empty($purchase_goods)) {
            return $this->format_ret('-1', '', '采购单无明细！');
        }

        //可用库存
        $sku_arr = array_filter(array_column($purchase_goods, 'sku'));
        $sku_inv = load_model('prm/InvModel')->get_inv_by_sku($store_code, $sku_arr, 0);
        $sku_inv = $this->trans_arr_key($sku_inv['data'], 'sku');
        $no_exists_code = array();
        $low_stock_code = array();
        foreach ($purchase_goods as $row) {
            //校验条码在系统是否存在
            if (empty($row['sku'])) {
                $no_exists_code[] = array($row['barcode'] => "采购单({$row['purchase_no']})商品条码在系统中不存在");
                continue;
            }
            //校验商品是否缺货
            if ($sku_inv[$row['sku']]['available_num'] < $row['stock']) {
                $low_stock_code[] = array($row['barcode'] => "采购单({$row['purchase_no']})商品条码库存不足");
                continue;
            }
        }
        if (!empty($no_exists_code)) {
            $msg = load_model('api/WeipinhuijitPickModel')->create_fail_file($no_exists_code);
            return $this->format_ret(-1, '', '采购单商品条码在系统中不存在' . $msg);
        }
        //判断是否开启缺货商品允许发货
        $ret_store = load_model('base/StoreModel')->get_by_code($store_code);
        $allow_negative_inv = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;
        if (!empty($low_stock_code) && $allow_negative_inv != 1) {
            $msg = load_model('api/WeipinhuijitPickModel')->create_fail_file($low_stock_code);
            return $this->format_ret(-1, '', '采购单商品条码库存不足' . $msg);
        }

        //获取SKU信息
        $sql_values = array();
        $sku = array_column($purchase_goods, 'sku');
        $sku_str = $this->arr_to_in_sql_value($sku, 'sku', $sql_values);
        $sql = "SELECT sku,barcode,goods_code,spec1_code,spec2_code FROM goods_sku WHERE sku IN({$sku_str}) ";
        $sku_arr = $this->db->get_all($sql, $sql_values);
        $sku_arr = $this->trans_arr_key($sku_arr, 'sku');

        //组装通知单详情信息
        $notice_goods = array();
        foreach ($purchase_goods as $row) {
            $sl = $row['stock'] - $row['delivery_stock'];
            if ($sl <= 0) {
                continue;
            }
            $sku_info = $sku_arr[$row['sku']];
            $goods_row = array();
            $goods_row['goods_code'] = $sku_info['goods_code'];
            $goods_row['spec1_code'] = $sku_info['spec1_code'];
            $goods_row['spec2_code'] = $sku_info['spec2_code'];
            $goods_row['sku'] = $sku_info['sku'];
            $price = $row['price'];
            $price = str_replace(',', '', $price);//处理千位 ，情况
            $goods_row['trade_price'] = $price;
            $goods_row['price'] = $price;
            $goods_row['num'] = $sl;
            if (isset($notice_goods[$goods_row['sku']])) {
                $goods_row['num'] += $sl;
                $goods_row['money'] += $price * $sl;
                continue;
            }
            $goods_row['rebate'] = 1;
            $goods_row['money'] = $price * $sl;
            $notice_goods[$goods_row['sku']] = $goods_row;
        }
        return $this->format_ret(1, array('notice_goods' => $notice_goods, 'purchase_no_str' => $purchase_no_str,));
    }


    /**
     * 获取明细
     * @param $purchase_no
     * @return array|bool
     */
    public function get_detail_by_purchase_no($purchase_no) {
        $purchase_no_arr = is_array($purchase_no) ? $purchase_no : array($purchase_no);
        $sql_value = array();
        $purchase_no_str = $this->arr_to_in_sql_value($purchase_no_arr, 'purchase_no', $sql_value);
        $sql = "select purchase_no,SUM(numbers) as stock,SUM(notice_num) as notice_stock,SUM(deliver_num) as delivery_stock,factory_code AS barcode,sku,purchase_price AS price from {$this->detail_table} WHERE purchase_no IN({$purchase_no_str}) AND numbers<>deliver_num group by factory_code";
        $data = $this->db->get_all($sql, $sql_value);
        return $data;
    }

    /**
     * 转变数据格式
     * @param $data
     * @param $key_fld
     * @return array
     */
    function trans_arr_key($data, $key_fld) {
        $arr = array();
        foreach ($data as $val) {
            $arr[$val[$key_fld]] = $val;
        }
        return $arr;
    }


    /**
     * 创建批发销货单
     * @param $purchase_ids
     * @param $notice_record
     * @param $out_params
     * @return array
     */
    function create_out_record($purchase_id, $notice_record, $out_params) {
        $ret = $this->get_by_ids($purchase_id);
        $purchase_ret = $ret['data'];
        $purchase_no_all = array_column($purchase_ret, 'purchase_no');
        $purchase_no_str = implode(',', $purchase_no_all);
        //校验采购单明细
        $purchase_goods_ret = $this->get_detail_by_purchase_no($purchase_no_all);

        //从通知单获取相关信息
        $notice_ret = load_model('wbm/NoticeRecordModel')->get_by_field('record_code', $notice_record);
        $notice_ret = $notice_ret['data'];
        $bill_sn = load_model('wbm/StoreOutRecordModel')->create_fast_bill_sn();

        //批发销货单主单信息
        $out_record['relation_code'] = $notice_ret['record_code'];
        $out_record['record_code'] = $bill_sn;
        $out_record['tel'] = $notice_ret['tel'];
        $out_record['express'] = $out_params['express'];
        $out_record['express_code'] = $out_params['express_code'];
        $out_record['record_time'] = date('Y-m-d H:i:s');
        $out_record['remark'] = "由有货采购单:[{$purchase_no_str}]生成";
        $out_record['order_time'] = date('Y-m-d H:i:s');
        $out_record['store_code'] = $notice_ret['store_code'];
        $out_record['distributor_code'] = $notice_ret['distributor_code'];
        $out_record['name'] = $notice_ret['name'];
        $out_record['address'] = empty($notice_ret['address']) ? '' : $notice_ret['address'];
        $out_record['province'] = empty($notice_ret['province']) ? '' : $notice_ret['province'];
        $out_record['city'] = empty($notice_ret['city']) ? '' : $notice_ret['city'];
        $out_record['district'] = empty($notice_ret['district']) ? '' : $notice_ret['district'];
        $out_record['record_type_code'] = '200';
        $ret = load_model('wbm/StoreOutRecordModel')->insert($out_record);
        if ($ret['status'] == -1) {
            return $this->format_ret(-1, '', '销货单生成失败');
        }
        $store_out_record_id = $ret['data'];
        //通知单详细信息
        $sql = "select * from wbm_notice_record_detail where record_code='{$notice_record}'";
        $notice_goods = $this->db->get_all($sql);
        $notice_goods = $this->trans_arr_key($notice_goods, 'sku');
        $sku_arr = array_column($notice_goods, 'sku');
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT * FROM goods_sku WHERE sku IN($sku_str) ";
        $sku_arr = $this->db->get_all($sql, $sql_values);
        $sku_arr = $this->trans_arr_key($sku_arr, 'sku');

        //销货单详细信息
        $goods = array();
        foreach ($purchase_goods_ret as $row) {
            $sku = $sku_arr[$row['sku']]['sku'];
            $notice_goods_row = $notice_goods[$sku];
            $num = $row['stock'] - $row['delivery_stock'];
            if ($num <= 0) {
                continue;
            }
            if (isset($goods[$sku])) {
                $goods[$sku]['enotice_num'] += $num;
                continue;
            }
            $goods_row['pid'] = $store_out_record_id;
            $goods_row['relation_code'] = $notice_record;
            $goods_row['record_code'] = $bill_sn;
            $goods_row['goods_id'] = $notice_goods_row['goods_id'];
            $goods_row['goods_code'] = $notice_goods_row['goods_code'];
            $goods_row['spec1_id'] = $notice_goods_row['spec1_id'];
            $goods_row['spec1_code'] = $notice_goods_row['spec1_code'];
            $goods_row['spec2_id'] = $notice_goods_row['spec2_id'];
            $goods_row['spec2_code'] = $notice_goods_row['spec2_code'];
            $goods_row['sku'] = $notice_goods_row['sku'];
            $goods_row['refer_price'] = $notice_goods_row['refer_price'];
            $goods_row['price'] = $notice_goods_row['price'];
            $goods_row['trade_price'] = $notice_goods_row['price'];
            $goods_row['rebate'] = $notice_goods_row['rebate'];
            $goods_row['enotice_num'] = $num;
            $goods_row['num_flag'] = 1;
            $goods[$sku] = $goods_row;
        }

        //单据批次信息添加
        $sql = "select  goods_code,spec1_code,spec2_code,sku,init_num-fill_num as init_num,lof_no,production_date  from b2b_lof_datail where order_code = '{$notice_record}' AND order_type='wbm_notice' ";
        $lof_data = $this->db->get_all($sql);
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($store_out_record_id, $out_record['store_code'], 'wbm_store_out', $lof_data);
        if ($ret['status'] < 0) {
            return $ret;
        }
        //销货单明细添加
        $ret = load_model('wbm/StoreOutRecordDetailModel')->add_detail_action($store_out_record_id, $goods);
        if ($ret['status'] == -1) {
            return $this->format_ret(-1, '', '销货单生成失败');
        }
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '创建', 'action_note' => '有货JIT，' . $out_record['remark'], 'module' => "store_out_record", 'pid' => $store_out_record_id);
        $ret = load_model('pur/PurStmLogModel')->insert($log);

        //更新回写状态
        //流程调整
        $ret = load_model('wbm/NoticeRecordModel')->update_check('1', 'is_execute', $notice_ret['notice_record_id']);
        if ($ret['status'] == -1) {
            return $this->format_ret(-1, '', '更新通知单执行状态失败');
        }

        //回写通知数
        $sql_values = array();
        $purchase_no_str = $this->arr_to_in_sql_value($purchase_no_all, 'purchase_no', $sql_values);
        $sql = "update api_youhuo_purchase_record set notice_num=numbers where purchase_no in ({$purchase_no_str}) ";
        $this->db->query($sql, $sql_values);
        $sql = "update api_youhuo_purchase_record_detail set notice_num=numbers where  purchase_no in ({$purchase_no_str}) ";
        $this->db->query($sql, $sql_values);
        return $this->format_ret(1, $bill_sn, '销货单创建成功');
    }


    /**
     * 创建有货出库单
     * @param $out_record_code
     * @param $out_params
     * @return mixed
     */
    function set_jit_delivery($notice_record_code, $out_params) {
        $purchase_arr = $this->get_by_ids($out_params['purchase_id']);
        $purchase_no_arr = array_column($purchase_arr['data'], 'purchase_no');
        $purchase_no = $purchase_no_arr[0];
        //生成出库单
        $params = array(
            'notice_record_code' => $notice_record_code,
            'purchase_no' => $purchase_no,
            'express_no' => $out_params['express'],
            'express_code' => $out_params['express_code'],
        );
        $ret = load_model('api/YohoDeliveryModel')->create_delivery($params);
        return $ret;
    }


    function get_yoho_by_api($out_params) {
        $params = array();
        $params['shop_code'] = $out_params['shop_code'];
        $params['start_time'] = $out_params['start_time'];
        $params['end_time'] = $out_params['end_time'];
        if ($params['start_time'] > $params['end_time']) {
            return $this->format_ret('-1', '', '开始时间不能大于结束时间！');
        }
        $result = load_model('sys/EfastApiModel')->request_api('yoho_api/purchase_export', $params);
        if ($result['resp_data']['code'] == '0') {
            return $this->format_ret('1', '', '获取成功！');
        } else {
            return $this->format_ret('-1', '', '获取失败！' . $result['resp_data']['msg']);
        }
    }

}
