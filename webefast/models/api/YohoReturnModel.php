<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

/**
 * 有货采购单
 * Class YohoPurchaseModel
 */
class YohoReturnModel extends TbModel {

    protected $table = "api_youhuo_return";
    protected $detail_table = "api_youhuo_return_detail";
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
        //退货通知单
        if ($filter['return_notice_code'] && $filter['return_notice_code'] != '') {
            $sql = "select purchase_no from api_youhuo_return_relation_record where return_notice_code=:return_notice_code";
            $sql_value[':return_notice_code'] = $filter['return_notice_code'];
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
        //创建时间，
        if (isset($filter['create_time_start']) && $filter['create_time_start'] !== '') {
            $sql_main .= " AND r1.create_time >=:create_time_start ";
            $sql_values[':create_time_start'] = $filter['create_time_start'];
        }
        //业务日期-结束
        if (isset($filter['create_time_end']) && $filter['create_time_end'] !== '') {
            $sql_main .= " AND r1.create_time <=:create_time_end ";
            $sql_values[':create_time_end'] = $filter['create_time_end'];
        }
        if ($filter['ctl_type'] == 'export') {
            $select = 'r1.*,r2.factory_code,r2.numbers AS sku_numbers,r2.purchase_price AS sku_purchase_price,r2.sku';
            return $this->get_export_data($filter, $sql_main, $sql_values, $select);
        } else {
            $select = 'r1.*';
        }
        $sql_main .= " ORDER BY r1.create_time desc ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        filter_fk_name($data['data'], array('shop_code|shop',));
        $ret_data = $data;

        return $this->format_ret(1, $ret_data);
    }


    function get_export_data($filter, $sql_main, $sql_values, $select) {
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            $value['is_execute_name'] = ($value['is_execute'] == 0) ? '未生成' : '已生成';
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
     * 获取明细数据
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
            $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            $value['no_store_in_num'] = $value['numbers'] - $value['store_in_num'];
        }
        return $this->format_ret(1, $data);
    }

    /**
     * 生成批发退货通知单，批发退货单
     * @param $out_params
     * @return array
     */
    function create($out_params) {
        $this->begin_trans();
        //条码识别
        $ret = $this->convert_barcode_to_sku($out_params['return_id']);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $ret = $this->return_create_by_unrelation_notice($out_params['return_id'], $out_params);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //更新关联表
        $result = $this->update_return_store_in($out_params['return_id'], $ret['data']);
        if ($result['status'] != 1) {
            return $this->format_ret('-1', '', '更新关联表失败！');
        }
        //回写退供单生成状态
        $id_arr = explode(',', $out_params['return_id']);
        $sql_value = array();
        $id_str = $this->arr_to_in_sql_value($id_arr, 'id', $sql_value);
        $sql = "UPDATE {$this->table} SET is_execute=1 WHERE id IN ({$id_str})";
        $ret_return = $this->query($sql,$sql_value);
        if ($ret_return['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '生成状态更新失败');
        }
        $this->commit();
        return $ret;
    }


    function update_return_store_in($return_id, $params) {
        $purchase = $this->get_by_ids($return_id);
        $insert_data = array();
        foreach ($purchase['data'] as $row) {
            $insert_data[] = array(
                'purchase_no' => $row['purchase_no'],
                'return_notice_code' => $params['return_notice_code'],
                'return_code' => $params['return_code'],
                'insert_time' => date('Y-m-d H:i:s', time()),
            );
        }
        $update_str = "return_code =VALUES(return_code)";
        $ret = $this->insert_multi_duplicate('api_youhuo_return_relation_record', $insert_data, $update_str);
        return $ret;
    }


    function return_create_by_unrelation_notice($return_id, $out_params) {
        $ret = $this->create_return_notice_record($return_id, $out_params);
        if ($ret['status'] != '1') {
            return $ret;
        }
        $return_notice_code = $ret['data']['return_notice_code'];
        $check = load_model('api/WeipinhuijitReturnModel')->check_is_wms_store_code($out_params['store_code']);
        if ($check === TRUE) {
            $ret = $this->format_ret(1, array('return_notice_code' => $return_notice_code, 'return_code' => ''), '外接wms仓库仅能生成退货通知单');
        } else {
            $ret = $this->create_return_record($return_id, $return_notice_code);
            if ($ret['status'] == 1) {
                $ret = $this->format_ret(1, array('return_notice_code' => $return_notice_code, 'return_code' => $ret['data']), '创建成功！');
            }
        }
        return $ret;
    }

    /**
     * 生成退货通知单
     * @param $return_ids
     * @param $out_params
     * @return array
     */
    function create_return_notice_record($return_ids, $out_params) {
        $return_ret = $this->get_by_ids($return_ids);
        $return_no_all = array_column($return_ret['data'],'purchase_no');
        $return_no_str = implode(",", $return_no_all);
        $ret = $this->add_return_notice_check($return_ids);
        if ($ret['status'] == -1) {
            return $ret;
        }
        $return_notice_goods = $ret['data']['return_notice_goods'];

        //通知单主单信息
        $return_notice_record = array();
        $return_notice_record['custom_code'] = $out_params['distributor_code'];
        $return_notice_record['store_code'] = $out_params['store_code'];
        $return_notice_record['return_type_code'] = $out_params['return_type_code'];
        $return_notice_record['order_time'] = date('Y-m-d H:i:s', time());
        $return_notice_code = load_model('wbm/ReturnNoticeRecordModel')->create_fast_bill_sn();
        $return_notice_record['return_notice_code'] = $return_notice_code;
        $return_notice_record['remark'] = "由采购退单{$return_no_str}自动生成";
        $return_notice_record['rebate'] = 1;
        $ret = load_model('wbm/ReturnNoticeRecordModel')->insert($return_notice_record);
        if ($ret['status'] == -1) {
            return $this->format_ret(-1, '', '批发退货通知单生成失败' . $ret['message']);
        }
        $return_notice_id = $ret['data'];
        foreach ($return_notice_goods as &$row) {
            $row['return_notice_record_id'] = $return_notice_id;
            $row['return_notice_code'] = $return_notice_code;
        }
        //添加商品明细
        $ret = load_model('wbm/ReturnNoticeDetailRecordModel')->add_detail_action($return_notice_id, $return_notice_goods);
        if ($ret['status'] == -1) {
            return $this->format_ret(-1, '', '批发退货通知单生成失败' . $ret['message']);
        }
        if (isset($return_notice_id) && $return_notice_id <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "创建", 'module' => "wbm_return_notice_record", 'pid' => $return_notice_id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        //审核批发退货通知单
        $ret = load_model('wbm/ReturnNoticeRecordModel')->update_check(1, 'is_check', $return_notice_code);
        if ($ret['status'] == -1) {
            return $this->format_ret(-1, '', '批发退货通知单审核失败' . $ret['message']);
        }
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "已确认", 'finish_status' => '未完成', 'action_name' => "确认", 'module' => "wbm_return_notice_record", 'pid' => $return_notice_id);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);

        return $this->format_ret(1, array('return_notice_code' => $return_notice_code));
    }

    /**
     * 获取主单数据
     * @param $return_ids
     * @return array
     */
    function get_by_ids($return_ids) {
        $return_ids_arr = explode(",", $return_ids);
        $sql_value = array();
        $return_ids_str = $this->arr_to_in_sql_value($return_ids_arr, 'id', $sql_value);
        $sql = " SELECT  * FROM {$this->table}  WHERE 1  AND id in({$return_ids_str}); ";
        $data = $this->db->get_all($sql, $sql_value);
        return $this->format_ret(1, $data);
    }

    /**
     * 审核单据
     * @param $return_ids
     * @param null $return_notice_record_no
     * @return array
     */
    function add_return_notice_check($return_ids) {
        $return_ret = $this->get_by_ids($return_ids);
        $return_no_all = array_column($return_ret['data'], 'purchase_no');
        $return_no_str = implode(",", $return_no_all);
        //校验退供单明细
        $return_goods = $this->get_detail_by_purchase_no($return_no_str);
        if (empty($return_goods)) {
            return $this->format_ret('-1', '', '采购退单无明细');
        }

        $no_exists_gb = array();
        foreach ($return_goods as $row) {
            //校验条码在系统是否存在
            if (empty($row['sku'])) {
                $no_exists_gb[] = array($row['barcode'] => "商品条码在系统中不存在");
                continue;
            }
        }
        if (!empty($no_exists_gb)) {
            $msg = load_model('api/WeipinhuijitPickModel')->create_fail_file($no_exists_gb);
            return $this->format_ret(-1, '', '条码在系统中不存在' . $msg);
        }

        //获取SKU信息
        $sql_values = array();
        $sku = array_column($return_goods, 'sku');
        $sku_str = $this->arr_to_in_sql_value($sku, 'sku', $sql_values);
        $sql = "SELECT sku,barcode,goods_code,spec1_code,spec2_code FROM goods_sku WHERE sku IN({$sku_str}) ";
        $sku_arr = $this->db->get_all($sql, $sql_values);
        $sku_arr = load_model('api/WeipinhuijitPickModel')->trans_arr_key($sku_arr, 'sku');

        //组装通知单详情信息
        $return_notice_goods = array();
        foreach ($return_goods as $row) {
            $sl = $row['num'];
            $sku_info = $sku_arr[$row['sku']];
            $goods_row['goods_code'] = $sku_info['goods_code'];
            $goods_row['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $sku_info['goods_code']));
            $price = $row['price'];
            $price = str_replace(',', '', $price);//处理千位 ，情况
            $goods_row['trade_price'] = $price;
            $goods_row['sell_price'] = $price;
            $goods_row['spec1_code'] = $sku_info['spec1_code'];
            $goods_row['spec2_code'] = $sku_info['spec2_code'];
            $goods_row['sku'] = $sku_info['sku'];
            if (isset($return_notice_goods[$goods_row['sku']])) {
                $return_notice_goods[$goods_row['sku']]['num'] += $sl;
                $return_notice_goods[$goods_row['sku']]['money'] += $price * $sl;
                continue;
            }
            $goods_row['barcode'] = $sku_info['barcode'];
            $goods_row['num'] = $sl;
            $goods_row['rebate'] = 1;
            $goods_row['money'] = $price * $sl;
            $return_notice_goods[$goods_row['sku']] = $goods_row;
        }
        return $this->format_ret(1, array('return_notice_goods' => $return_notice_goods));
    }


    function get_detail_by_purchase_no($purchase_no) {
        $purchase_no_arr = explode(",", $purchase_no);
        $sql_value = array();
        $purchase_no_str = $this->arr_to_in_sql_value($purchase_no_arr, 'purchase_no', $sql_value);
        $sql = "select factory_code AS barcode,sku,sum(numbers) AS num,purchase_price as price from api_youhuo_return_detail where purchase_no in({$purchase_no_str}) group by factory_code;";
        $data = $this->db->get_all($sql, $sql_value);
        return $data;
    }

    /**
     * 创建退货单
     * @param $return_ids
     * @param $return_notice_code
     * @param $out_params
     * @return mixed
     */
    function create_return_record($return_ids, $return_notice_code) {
        //$return_ret = $this->get_by_ids($return_ids);
        //$return_no_all = array_column($return_ret['data'],'purchase_no');
        //$return_no_str = implode(",", $return_no_all);
        //通知单
        $return_notice = load_model('wbm/ReturnNoticeRecordModel')->get_by_field('return_notice_code', $return_notice_code);
        $ret = load_model('wbm/ReturnNoticeRecordModel')->check_status($return_notice);
        if ($ret['status'] != 1) {
            return $ret;
        }
        //生成退货单
        $ret = load_model('wbm/ReturnRecordModel')->create_return_record($return_notice['data']);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $sql = "select record_code from wbm_return_record where return_record_id='{$ret['data']}';";
        $return_record_code = $this->db->get_value($sql);
        //回写通知单状态
        $this->update_exp('wbm_return_notice_record', array('is_return' => 1), array('return_notice_code' => $return_notice_code));
        return $this->format_ret('1', $return_record_code, '创建成功！');
    }

    /**
     * 条码识别
     * @param $return_id
     * @return array
     */
    function convert_barcode_to_sku($return_id) {
        $purchase_arr = $this->get_by_ids($return_id);
        $purchase_no_arr = array_column($purchase_arr['data'], 'purchase_no');
        $sql_values = array();
        $purchase_no_str = $this->arr_to_in_sql_value($purchase_no_arr, 'purchase_no', $sql_values);
        $sql = "SELECT purchase_no,factory_code FROM {$this->detail_table} WHERE purchase_no IN({$purchase_no_str})";
        $purchase_detail = $this->db->get_all($sql, $sql_values);
        if (empty($purchase_detail)) {
            return $this->format_ret(-1, '', '采购退单明细为空');
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
        $ret = $this->insert_multi_duplicate('api_youhuo_return_detail', $purchase_detail, $update_str);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '识别条码失败');
        }
        return $this->format_ret(1);
    }


    function get_notice_record_by_purchase($purchase_no) {
        $sql = "select b.* from api_youhuo_return_relation_record as a,wbm_return_notice_record as b where a.return_notice_code=b.return_notice_code and a.purchase_no='{$purchase_no}'";
        $ret = $this->db->get_all($sql);
        foreach ($ret as &$value) {
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
        }
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
        $result = load_model('sys/EfastApiModel')->request_api('yoho_api/refund_supplier', $params);
        if ($result['resp_data']['code'] == '0') {
            return $this->format_ret('1', '', '获取成功！');
        } else {
            return $this->format_ret('-1', '', '获取失败！' . $result['resp_data']['msg']);
        }
    }

    /**
     * 批量验证
     * @param $return_ids
     * @return array
     */
    function check_return_more($return_ids) {
        $return_ids_arr = explode(",", $return_ids);
        $sql_value = array();
        $return_ids_str = $this->arr_to_in_sql_value($return_ids_arr, 'id', $sql_value);
        $sql = " SELECT is_execute,purchase_no FROM {$this->table}  WHERE 1 AND  id in({$return_ids_str}) AND is_execute=1";
        $data = $this->db->get_all($sql,$sql_value);
        if (!empty($data)) {
            $purchase_no_arr = array_column($data, 'purchase_no');
            $purchase_no_str = implode(',', $purchase_no_arr);
            $ret = $this->format_ret(-1, '', '采购单:' . $purchase_no_str . '已生成退货单不可再生成退货单!');
        } else {
            $ret = $this->format_ret(1, '', '');
        }
        return $ret;
    }


    function get_data_by_relation($return_code) {
        $sql_value = array();
        $sql = "SELECT * FROM api_youhuo_return_relation_record WHERE return_notice_code=:return_code";
        $sql_value[':return_code'] = $return_code;
        $data = $this->db->get_all($sql, $sql_value);
        return $data;
    }
}
