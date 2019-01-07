<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

class WeipinhuijitPickModel extends TbModel {

    protected $table = "api_weipinhuijit_pick";
    protected $detail_table = "api_weipinhuijit_pick_goods";
    private $create_params = array();
    private $jit_version = 1;

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = '';
        //导出
        if ($filter['ctl_type'] == 'export') {
            $sql_join = " LEFT JOIN {$this->detail_table} AS r2 ON r1.pick_no=r2.pick_no LEFT JOIN goods_sku AS r3 ON r3.sku = r2.sku LEFT JOIN base_spec1 AS r4 ON r4.spec1_code = r3.spec1_code";
        }
        $sql_main = "FROM {$this->table} AS r1 {$sql_join} WHERE 1";

        $sql_values = array();
        //商店权限1
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('r1.shop_code', $filter_shop_code);

        //JIT接口版本
        if (isset($filter['jit_version']) && in_array($filter['jit_version'], array(1, 2))) {
            $sql_main .= " AND r1.jit_version = :jit_version ";
            $sql_values[':jit_version'] = $filter['jit_version'];
        }

        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND r1.shop_code in ({$str}) ";
        }

        //拣货单号
        if (isset($filter['pick_no']) && $filter['pick_no'] != '') {
            $sql_main .= " AND r1.pick_no = :pick_no ";
            $sql_values[':pick_no'] = $filter['pick_no'];
        }

        $where = '';
        //批发销货单号
        if (isset($filter['store_out_record_no']) && $filter['store_out_record_no'] != '') {
            $where = "store_out_record_no=:store_out_record_no ";
            $sql_value[':store_out_record_no'] = $filter['store_out_record_no'];
        }
        //出库单ID
        if (isset($filter['delivery_id']) && $filter['delivery_id'] != '') {
            $where = "delivery_id=:delivery_id ";
            $sql_value[':delivery_id'] = $filter['delivery_id'];
        }
        if ($where != '') {
            $sql = "select pick_no from api_weipinhuijit_store_out_record where " . $where;
            $pick_data = $this->db->get_all($sql, $sql_value);
            if (!empty($pick_data)) {
                $pick_arr = array();
                foreach ($pick_data as $val) {
                    $pick_arr[] = $val['pick_no'];
                }
                $pick_no_str = $this->arr_to_in_sql_value($pick_arr, 'pick_no', $sql_values);

                $sql_main .= " AND r1.pick_no in ({$pick_no_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }

        //档期号
        if (isset($filter['po_no']) && $filter['po_no'] != '') {
            $sql_main .= " AND r1.po_no LIKE :po_no ";
            $sql_values[':po_no'] = "%{$filter['po_no']}%";
        }
        //送货仓库
        if (isset($filter['warehouse']) && $filter['warehouse'] != '') {
            $warehouse = deal_strs_with_quote($filter['warehouse']);
            $sql_main .= " AND r1.warehouse in({$warehouse}) ";
        }
        //是否生成销货单
        if (isset($filter['is_execute']) && $filter['is_execute'] != '') {
            $sql_main .= " AND r1.is_execute = :is_execute ";
            $sql_values[':is_execute'] = $filter['is_execute'];
        }
        //业务日期-开始
        if (isset($filter['insert_time_start']) && $filter['insert_time_start'] !== '') {
            $sql_main .= " AND r1.insert_time >=:insert_time_start ";
            $sql_values[':insert_time_start'] = $filter['insert_time_start'];
        }
        //业务日期-结束
        if (isset($filter['insert_time_end']) && $filter['insert_time_end'] !== '') {
            $sql_main .= " AND r1.insert_time <=:insert_time_end ";
            $sql_values[':insert_time_end'] = $filter['insert_time_end'];
        }

        if ($filter['ctl_type'] == 'export') {
            $select = 'r1.*,r2.art_no,r2.product_name,r2.barcode,r2.size,r2.actual_unit_price,r2.actual_market_price,r2.stock,r2.notice_stock,r2.delivery_stock,r2.sku,r4.spec1_name';
        } else {
            $select = 'r1.*';
        }
        $sql_main .= " order by insert_time desc ";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $warehouse_arr = $this->weipinhui_warehouse(0);
        foreach ($data['data'] as &$row) {
            $row['delete_status'] = $row['is_execute'];
            $row['warehouse_name'] = $warehouse_arr[$row['warehouse']]['name'];
            $row['no_delivery_num'] = $row['pick_num'] - $row['delivery_num'];
            if ($filter['ctl_type'] == 'export') {
                $sql = "SELECT r1.shelf_code,r1.shelf_name FROM base_shelf AS r1 INNER JOIN goods_shelf AS r2 ON r1.shelf_code = r2.shelf_code AND r1.store_code = r2.store_code WHERE r2.sku = :sku ";
                $shelf_arr = $this->db->get_all($sql, array(':sku' => $row['sku']));
                $shelf_name_arr = array_column($shelf_arr, 'shelf_name');
                $row['shelf_name_str'] = implode(",", $shelf_name_arr);
            }
        }
        filter_fk_name($data['data'], array('shop_code|shop',));
        $ret_data = $data;

        return $this->format_ret(1, $ret_data);
    }
    public function get_by_custom($filter){
        $sql_values = array();
        $sql = "SELECT user_code FROM sys_user WHERE status = 2 AND login_type = 2";
        $user_code = $this->get_all_col($sql);
        $sql_main = " FROM base_custom rl WHERE rl.is_effective = 1 ";
        if(!empty($user_code['data'])) {
            $user_str = $this->arr_to_in_sql_value($user_code['data'],'user_code',$sql_values);
            $sql_main .= " AND (rl.user_code NOT IN ({$user_str}) OR rl.user_code is null) ";
        }
        if (isset($filter['custom_name']) && $filter['custom_name'] !== '') {
            $sql_main .= " AND rl.custom_name LIKE :custom_name ";
            $sql_values[':custom_name'] ="%{$filter['custom_name']}%";
        }
        if (isset($filter['custom_code']) && $filter['custom_code'] !== '') {
            $sql_main .= " AND rl.custom_code LIKE :custom_code ";
            $sql_values[':custom_code'] = "%{$filter['custom_code']}%";
        }
        $select="rl.custom_code,rl.custom_name,rl.mobile";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        return $this->format_ret(1,$data);

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

    public function weipinhui_warehouse($check = 1) {
        $warehouse = load_model('api/WeipinhuijitWarehouseModel')->get_warehouse_select($check);
        $warehouse_arr = array();
        foreach ($warehouse as $val) {
            $warehouse_arr[$val['warehouse_code']] = array('name' => $val['warehouse_name'], 'val' => $val['warehouse_no']);
        }
        return $warehouse_arr;
    }

    function get_goods_by_page($filter) {

        $sql_join = " LEFT JOIN goods_sku AS r2 ON rl.sku = r2.sku LEFT JOIN base_spec1 AS r3 ON r2.spec1_code = r3.spec1_code ";
        $sql_main = "FROM {$this->detail_table} AS rl {$sql_join} WHERE 1";

        $sql_values = array();

        //拣货单号
        if (isset($filter['pick_no']) && $filter['pick_no'] != '') {
            $sql_main .= " AND rl.pick_no = :pick_no ";
            $sql_values[':pick_no'] = $filter['pick_no'];
        }


        $select = 'rl.*,r3.spec1_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        return $this->format_ret(1, $data);
    }

    //根据id查询
    function get_by_id($id) {
        return $this->get_row(array('id' => $id));
    }

    public function get_by_field_order_all($field_name, $value, $select = "*") {
        $sql = "select {$select} from {$this->detail_table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_all($sql, array(":{$field_name}" => $value));
        return $data;
    }

    public function get_detail_by_pick_no($pick_no) {
        $pick_no_arr = explode(",", $pick_no);
        $pick_no_str = "'" . implode("','", $pick_no_arr) . "'";
        $sql = "select  SUM(stock) as stock,SUM(notice_stock) as notice_stock,SUM(delivery_stock) as delivery_stock,barcode,sku,art_no,product_name,size,actual_unit_price,actual_market_price,pick_no from {$this->detail_table} where pick_no in({$pick_no_str}) AND stock<>delivery_stock group by barcode";
        $data = $this->db->get_all($sql);
        return $data;
    }

    //校验是否可以生成批发销货单
    function check_pick($pick_id, $type = 1) {
        //校验待发货数是否大于发货数
        $pick_ret = $this->get_by_id($pick_id);
        $pick_data = $pick_ret['data'];
        if ($pick_data['pick_num'] <= $pick_data['delivery_num']) {
            return $this->format_ret(-1, '', '拣货单的全部商品已拣货完成');
        }
        //校验送货仓
        $ret_warehouse = load_model('api/WeipinhuijitWarehouseModel')->check_warehouse($pick_data['warehouse']);
        if ($ret_warehouse['status'] < 1) {
            return $ret_warehouse;
        }

        $pick_no = $pick_data['pick_no'];
        //通知数大于0标识该拣货单已生成过销货单
        if ($pick_data['notice_num'] > 0) {
            //是否存在未验收单的批发销货单
            $sql = "select a.store_out_record_no from api_weipinhuijit_store_out_record a,wbm_store_out_record b where a.store_out_record_no=b.record_code and a.pick_no='{$pick_no}' and is_store_out=0";
            $recode_codes = $this->db->get_col($sql);
            if (!empty($recode_codes)) {
                $code_str = implode(',', $recode_codes);
                return $this->format_ret(-1, '', '还有未完成的销货单' . $code_str);
            }
        }
        if ($type == 1) {
            $is_wms = $this->check_is_wms_notice($pick_id);
            if ($is_wms === true) {
                return $this->format_ret(-1, '', '单据已经生成通知单，通知外部仓储系统');
            }
        }

        return $this->format_ret(1, '');
    }

    function check_is_wms_notice($pick_id) {
        $sql = "select pick_ids from api_weipinhuijit_wms_info where pick_ids like :pick_id and status=0 ";
        $data = $this->db->get_all($sql, array(':pick_id' => '%' . $pick_id . '%'));
        $is_wms = false;
        foreach ($data as $val) {
            $pick_id_arr = explode(',', $val['pick_ids']);
            $find = array_search($pick_id, $pick_id_arr);
            if ($find !== NULL) {
                $is_wms = TRUE;
                break;
            }
        }
        return $is_wms;
    }

    function check_pick_more($pick_ids) {
        $pick_ids_arr = explode(",", $pick_ids);
        $pick_ids_str = "'" . implode("','", $pick_ids_arr) . "'";

        $sql = " SELECT id,pick_no,po_no,warehouse FROM {$this->table}  WHERE 1 AND  id in({$pick_ids_str}); ";
        $data = $this->db->get_all($sql);
        $warehouse = $data[0]['warehouse'];
        $po_no = $data[0]['po_no'];
        //校验送货仓
        $ret_warehouse = load_model('api/WeipinhuijitWarehouseModel')->check_warehouse($warehouse);
        if ($ret_warehouse['status'] < 1) {
            return $ret_warehouse;
        }

        $ret = $this->format_ret(1, '');
        foreach ($data as $val) {
            if ($po_no != $val['po_no']) {
                $ret = $this->format_ret(-1, '', '拣货单PO必须相同！');
                break;
            }
            if ($warehouse != $val['warehouse']) {
                $ret = $this->format_ret(-1, '', '拣货单必须是同一个送货仓库！');
                break;
            }

            $ret_check = $this->check_pick($val['id']);
            if ($ret_check['status'] < 1) {
                $ret = $ret_check;
                $ret['message'] = $val['pick_no'] . ":" . $ret['message'];
                break;
            }
        }
        return $ret;
    }

    //拣货单关联的通知单
    function get_relation_notice($pick_id) {
        $notice = array();
        $pick_ret = $this->get_by_ids($pick_id);
        foreach ($pick_ret['data'] as $pick_row) {
            if (!empty($pick_row['notice_record_no'])) {
                //通知单是否终止
                $notice_ret = load_model('wbm/NoticeRecordModel')->get_by_field('record_code', $pick_row['notice_record_no']);
                if (!empty($notice_ret['data']) && $notice_ret['data']['is_stop'] == 0) {
                    $notice[] = $pick_row['notice_record_no'];
                }
            }

            //档期是否绑定通知单
            $po_ret = load_model('api/WeipinhuijitPoModel')->get_by_field('po_no', $pick_row['po_no']);
            if (!empty($po_ret['data']['notice_record_no']) && $po_ret['data']['relation_type'] != 1) {
                $notice_ret = load_model('wbm/NoticeRecordModel')->get_by_field('record_code', $po_ret['data']['notice_record_no']);
                if (!empty($notice_ret['data']) && $notice_ret['data']['is_stop'] == 0 && $notice_ret['data']['is_finish'] == 0) {
                    $notice[] = $po_ret['data']['notice_record_no'];
                }
            }
        }
        $notice = array_unique($notice);
        return $notice;
    }

    /**
     * 根据拣货单ID获取拣货单信息
     * @param string $pick_ids 拣货单ID,多个逗号隔开
     * @return array 数据集
     */
    function get_by_ids($pick_ids,$column = '*') {
        $pick_ids_arr = explode(",", $pick_ids);
        $sql_values = array();
        $pick_ids_str = $this->arr_to_in_sql_value($pick_ids_arr, 'id', $sql_values);
        $sql = " SELECT {$column} FROM {$this->table} WHERE id IN({$pick_ids_str}); ";
        $data = $this->db->get_all($sql, $sql_values);
        return $this->format_ret(1, $data);
    }

    /**
     * 创建批发通知单、批发销货单、JIT出库单
     * @param array $out_params 页面参数
     * @return array 创建结果
     */
    function create($out_params) {
        if (!empty($out_params['delivery_id'])) {
            $ret = load_model('api/WeipinhuijitDeliveryModel')->check_is_delivery($out_params['delivery_id']);
            if ($ret['status'] != 1) {
                return $ret;
            }
        }
        $this->jit_version = isset($out_params['jit_version']) ? $out_params['jit_version'] : 1;
        unset($out_params['jit_version']);

        $this->begin_trans();
        $this->create_params = $out_params;
        $ret = $this->convert_barcode_to_sku($out_params['pick_id']);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //选择已有通知单
        if (!empty($out_params['notice_code'])) {
            $ret = $this->pick_create_by_notice($out_params['pick_id'], $out_params['notice_code'], $out_params);
        } else {
            //未生成通知单
            $ret = $this->pick_create_by_unrelation_notice($out_params['pick_id'], $out_params);
        }
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //回写拣货单生成状态
        $ids = explode(',', $out_params['pick_id']);
        $sql_values = array();
        $ids_str = $this->arr_to_in_sql_value($ids, 'id', $sql_values);
        $sql = "UPDATE {$this->table} SET is_execute=1 WHERE id IN ({$ids_str})";
        $ret_pick = $this->db->query($sql, $sql_values);
        if ($ret_pick != TRUE) {
            $this->rollback();
            return $this->format_ret(-1, '', '生成状态更新失败');
        }

        $out_record_code = $ret['data'];
        $ret = $this->set_jit_delivery($out_record_code, $out_params);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }

        $this->commit();
        return $ret;
    }

    function set_jit_delivery($out_record_code, $out_params) {
        //生成出库单
        if (empty($out_params['delivery_id'])) {
            $params = array('store_out_record_no' => $out_record_code, 'tel' => $out_params['tel'], 'brand_code' => $out_params['brand_code'], 'delivery_method' => $out_params['delivery_method'], 'arrival_time' => $out_params['arrival_time'], 'express_code' => $out_params['express_code'], 'express' => $out_params['express']);
            $act = $this->jit_version == 2 ? 'create_delivery2' : 'create_delivery';
            $ret = load_model('api/WeipinhuijitDeliveryModel')->$act($params);
        } else {
            //更新关联关系
            $ret = load_model('api/WeipinhuijitDeliveryModel')->update_relation($out_params['delivery_id'], $out_record_code);
        }

        return $ret;
    }

    public function check_is_wms_store_code($store_code) {
        static $wms_store = NULL;
        if (!isset($wms_store[$store_code])) {
            $ret = load_model('wms/WmsEntryModel')->check_wms_store($store_code);
            $wms_store[$store_code] = ($ret['status'] > 0) ? TRUE : FALSE;
            //是否仓储，
        }
        return $wms_store[$store_code];
    }

    /**
     * 转换条码/子条码为SKU
     * @param string $pick_id 拣货单ID，多个逗号隔开
     * @return array 处理结果
     */
    function convert_barcode_to_sku($pick_id) {
        $pick_arr = $this->get_by_ids($pick_id);
        $pick_no_arr = array_column($pick_arr['data'], 'pick_no');
        $sql_values = array();
        $pick_no_str = $this->arr_to_in_sql_value($pick_no_arr, 'pick_no', $sql_values);
        $sql = "SELECT po_no,pick_no,barcode FROM {$this->detail_table} WHERE pick_no IN({$pick_no_str})";
        $pick_detail = $this->db->get_all($sql, $sql_values);
        if (empty($pick_detail)) {
            return $this->format_ret(-1, '', '拣货单明细为空，请至档期管理中操作[更新拣货单]');
        }
        $barcode_arr = array_column($pick_detail, 'barcode');
        $ret = load_model('prm/SkuModel')->convert_barcode($barcode_arr);
        if (empty($ret['data'])) {
            return $this->format_ret(-1, '', '识别条码失败');
        }
        $convert_barcode = $ret['data'];
        //更新sku
        foreach ($pick_detail as &$value) {
            $barcode = strtolower($value['barcode']);
            $value['sku'] = isset($convert_barcode[$barcode]['sku']) ? $convert_barcode[$barcode]['sku'] : '';
        }
        $update_str = "sku = VALUES(sku)";
        $ret = $this->insert_multi_duplicate('api_weipinhuijit_pick_goods', $pick_detail, $update_str);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '识别条码失败');
        }
        return $this->format_ret(1);
    }

    /**
     * 将数组中某个值用作键
     * @param array $data 数据
     * @param string $key_fld 用作键的字段
     * @return array 处理后的数据
     */
    function trans_arr_key($data, $key_fld) {
        $arr = array();
        foreach ($data as $val) {
            $arr[$val[$key_fld]] = $val;
        }
        return $arr;
    }

    function add_notice_check($pick_ids, $notice_record_no = null, $store_code = null) {
        $pick_ret = $this->get_by_ids($pick_ids);
        $pick_no_all = array();
        $po_no_arr = array();
        foreach ($pick_ret['data'] as $val) {
            $po_no = explode(",", $val['po_no']);
            $po_no_arr = array_merge($po_no_arr, $po_no);
            $pick_no_all[] = $val['pick_no'];
        }
        $pick_no_str = implode(",", $pick_no_all);
        $po_no_str = implode(",", array_unique($po_no_arr));
        $warehouse_code = $pick_ret['data'][0]['warehouse'];
        $warehouse_name = load_model('api/WeipinhuijitWarehouseModel')->get_by_field('warehouse_code', $warehouse_code, 'warehouse_name');
        $warehouse_name = $warehouse_name['data']['warehouse_name'];
        unset($pick_ret);

        //校验拣货单明细
        $pick_goods = $this->get_detail_by_pick_no($pick_no_str);
        //拣货单明细为空，重新获取
        if (empty($pick_goods)) {
            return $this->format_ret('-1', '', '拣货单无明细信息，请至档期管理中操作[更新拣货单]');
        }

        //绑定通知单
        if (!empty($notice_record_no)) {
            //通知单明细信息
            $notice_record_goods = load_model('wbm/NoticeRecordDetailModel')->get_by_record_code($notice_record_no);
            $notice_record_goods = $this->trans_arr_key($notice_record_goods['data'], 'sku');

            //校验拣货单明细
            $no_exists_notice = array(); //通知单中不存在的条码
            $out_num_notice = array(); //拣货数超过通知单未完成数的条码

            foreach ($pick_goods as $row) {
                $sku = $row['sku'];
                if (!isset($notice_record_goods[$sku])) {
                    $no_exists_notice[] = array($row['barcode'] => "拣货单({$row['pick_no']})条码在通知单({$notice_record_no})中不存在");
                    continue;
                }
                $sl = $row['stock'] - $row['delivery_stock'];
                $no_finish_num = $notice_record_goods[$sku]['num'] - $notice_record_goods[$sku]['finish_num'];
                if ($sl > $no_finish_num) {
                    $out_num_notice[] = array($row['barcode'] => "拣货单({$row['pick_no']})条码拣货数已超过通知单{$notice_record_no}的未完成数,待发货数为:{$sl}");
                    continue;
                }
            }
            if (!empty($no_exists_notice)) {
                $msg = $this->create_fail_file($no_exists_notice);
                return $this->format_ret(-1, '', '拣货单商品条码在通知单' . $notice_record_no . '中不存在' . $msg);
            }
            if (!empty($out_num_notice)) {
                $msg = $this->create_fail_file($out_num_notice);
                return $this->format_ret(-1, '', '商品条码拣货数超过通知单' . $notice_record_no . '的未完成数' . $msg);
            }
            return $this->format_ret(1);
        }

        //校验锁定单
        $ret = $this->check_blind_check($po_no_str, $store_code);
        if ($ret['status'] == -1) {
            return $ret;
        }
        //是否绑定锁定单，若绑定则校验锁定单库存 不判断系统库存
        $is_bind_lock = ($ret['status'] == 1) ? 1 : 0;
        $blind_record_info = ($ret['status'] == 1) ? $ret['data'] : '';
        //未绑定通知单
        //可用库存
        $sku_arr = array_column($pick_goods, 'sku');
        $sku_inv = load_model('prm/InvModel')->get_inv_by_sku($store_code, $sku_arr, 0);
        $sku_inv = $this->trans_arr_key($sku_inv['data'], 'sku');

        $no_exists_code = array();
        $low_stock_code = array();
        foreach ($pick_goods as $row) {
            //校验条码在系统是否存在
            if (empty($row['sku'])) {
                $pick_str = $this->get_barcode_relation_pick($pick_no_all, $row['barcode']);
                $no_exists_code[] = array($row['barcode'] => "拣货单({$pick_str})商品条码在系统中不存在");
                continue;
            }
            //校验商品是否缺货
            if ($sku_inv[$row['sku']]['available_num'] < $row['stock'] && $is_bind_lock == 0) {
                $sku_no_deliver = $row['stock'] - $row['delivery_stock'];
                $sku_lack_num = $sku_no_deliver - $sku_inv[$row['sku']]['available_num'];
                $pick_str = $this->get_barcode_relation_pick($pick_no_all, $row['barcode']);
                $low_stock_code[] = array($row['barcode'] => "拣货单({$pick_str})商品条码库存不足,待发货数:{$sku_no_deliver},缺货数为:{$sku_lack_num}");
                continue;
            }
        }
        if (!empty($no_exists_code)) {
            $msg = $this->create_fail_file($no_exists_code);
            return $this->format_ret(-1, '', '拣货单商品条码在系统中不存在' . $msg);
        }
        //判断是否开启缺货商品允许发货
        $ret_store = load_model('base/StoreModel')->get_by_code($store_code);
        $allow_negative_inv = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;
        if (!empty($low_stock_code) && $allow_negative_inv != 1) {
            $msg = $this->create_fail_file_new($low_stock_code);
            return $this->format_ret(-1, '', '拣货单商品条码库存不足' . $msg);
        }

        //获取SKU信息
        $sql_values = array();
        $sku = array_column($pick_goods, 'sku');
        $sku_str = $this->arr_to_in_sql_value($sku, 'sku', $sql_values);
        $sql = "SELECT sku,barcode,goods_code,spec1_code,spec2_code FROM goods_sku WHERE sku IN({$sku_str}) ";
        $sku_arr = $this->db->get_all($sql, $sql_values);
        $sku_arr = $this->trans_arr_key($sku_arr, 'sku');

        //组装通知单详情信息
        $notice_goods = array();
        foreach ($pick_goods as $row) {
            $sl = $row['stock'] - $row['delivery_stock'];
            if ($sl <= 0) {
                continue;
            }
            $sku_info = $sku_arr[$row['sku']];
            $sku = $sku_info['sku'];
            $goods_row = array();
            $goods_row['goods_code'] = $sku_info['goods_code'];
            $goods_row['spec1_code'] = $sku_info['spec1_code'];
            $goods_row['spec2_code'] = $sku_info['spec2_code'];
            $goods_row['sku'] = $sku;
            $price = $row[$this->create_params['price_type']];
            $price = str_replace(',', '', $price); //处理千位 ，情况
            $goods_row['trade_price'] = $price;
            $goods_row['price'] = $price;
            $goods_row['rebate'] = 1;
            if (isset($notice_goods[$sku])) {
                $notice_goods[$sku]['num'] += $sl;
                $notice_goods[$sku]['money'] += $price * $sl;
                continue;
            }
            $goods_row['num'] = $sl;
            $goods_row['money'] = $price * $sl;
            $notice_goods[$sku] = $goods_row;
        }
        return $this->format_ret(1, array('notice_goods' => $notice_goods,'pick_no_str'=>$pick_no_str, 'po_no_str' => $po_no_str, 'warehouse_name' => $warehouse_name, 'blind_record_info' => $blind_record_info));
    }

    /**
     * 获取包含改条码的拣货单
     * @param $pick_no_arr
     * @param $barcode
     * @return string
     */
    function get_barcode_relation_pick($pick_no_arr, $barcode) {
        $sql_value = array();
        $pick_no_str = $this->arr_to_in_sql_value($pick_no_arr, 'pick_no', $sql_value);
        $sql = "SELECT pick_no FROM api_weipinhuijit_pick_goods WHERE pick_no IN ({$pick_no_str}) AND barcode=:barcode";
        $sql_value[':barcode'] = $barcode;
        $pick = $this->db->get_all_col($sql, $sql_value);
        $ret = implode(';', $pick);
        return $ret;
    }


    //已绑定通知单生成销货单
    function pick_create_by_notice($pick_id, $notice_record, $out_params) {
        $ret = $this->add_notice_check($pick_id, $notice_record);
        if ($ret['status'] != 1) {
            return $ret;
        }

        $check = $this->check_is_wms_store_code($out_params['store_code']);
        if ($check === TRUE) {
            //加入临时数据
            $ret = $this->create_jit_wms_info($pick_id, $notice_record, $out_params);
        } else {
            $ret = $this->create_out_record($pick_id, $notice_record, $out_params);
        }

        return $ret;
    }

    function create_jit_wms_info($pick_id, $notice_record, $out_params) {
        $data = array();
        $data['notice_record_no'] = $notice_record;
        $data['pick_ids'] = $pick_id;
        if(!empty($out_params['delivery_id'])){
              $data['delivery_id'] = load_model('api/WeipinhuijitDeliveryModel')->get_storage_no_by_delivery_id($out_params['delivery_id']);
        }else{
             $data['delivery_id'] = $out_params['delivery_id'];//需要完善
        }
        $data['tel'] = $out_params['tel'];
        $data['brand_code'] = $out_params['brand_code'];
        $data['express_code'] = $out_params['express_code'];
        $data['express'] = $out_params['express'];
        //新增
        $data['delivery_method'] = $out_params['delivery_method'];
        $data['arrival_time'] = $out_params['arrival_time'];
        if (isset($out_params['jit_version'])) {
            $data['jit_version'] = $out_params['jit_version'];
        }
        $data['price_type'] = $out_params['price_type'];
        $data['distributor_code'] = $out_params['distributor_code'];


        return $this->insert_exp('api_weipinhuijit_wms_info', $data);
    }

    function update_jit_wms_info($notice_record, $store_out_record_no) {
        $data['store_out_record_no'] = $store_out_record_no;
        $where = " notice_record_no = '{$notice_record}' ";
        $this->db->update('api_weipinhuijit_wms_info', $data, $where);
    }

    function create_jit_out_record_by_wms($notice_record) {
        $sql = "select * from api_weipinhuijit_wms_info where notice_record_no=:notice_record_no";
        $sql_value = array(':notice_record_no' => $notice_record);
        $row = $this->db->get_row($sql, $sql_value);

        if (!empty($row)) {
            if ($row['status'] == 1) {
                return $this->format_ret(-1, '', 'jit出库单已经处理，不能重复处理');
            }
            //  $express_no = !empty($wms_record_data['express_no']) ? $wms_record_data['express_no'] : $row['express'];
            //   $row['express_code'];
            //    需要更新快递更新和快递单号
            //  $ret_c = $this->create_out_record($pick_id, $notice_record, $out_params);
            //$ret = $this->set_jit_delivery($out_record_code, $out_params);
            //api_weipinhuijit_store_out_record
            //清楚记录防止部分出库
            $sql = "update  api_weipinhuijit_wms_info set status=1 where id='{$row['id']}' ";
            $this->db->query($sql);
            $ret = $this->format_ret(1);
            $ret['data'] = $row['store_out_record_no'];
            return $ret;
        } else {
            //不是jit
            return $this->format_ret(2);
        }
    }

    function is_jit_notice_record($record_code) {
        $sql = "select id from api_weipinhuijit_wms_info where notice_record_no=:notice_record_no";
        $sql_values = array(
            ':notice_record_no' => $record_code
        );
        $id = $this->db->get_value($sql, $sql_values);
        return $id > 0 ? true : false;
    }

    /**
     * 未绑定通知单生成销货单
     * @param string $pick_id 拣货单ID，多个逗号隔开
     * @param array $out_params 页面参数
     * @return array 创建结果
     */
    function pick_create_by_unrelation_notice($pick_id, $out_params) {
        $ret = $this->create_notice_record($pick_id, $out_params['store_code'], $out_params);
        if ($ret['status'] != 1) {
            return $ret;
        }

        $notice_record = $ret['data']['notice_record'];
        $check = $this->check_is_wms_store_code($out_params['store_code']);
        if ($check === TRUE) {
            //加入临时数据  对接wms确认可以取消，不用这个表
            $this->create_jit_wms_info($pick_id, $notice_record, $out_params);
        }
        $ret = $this->create_out_record($pick_id, $notice_record, $out_params);

        return $ret;
    }

    //拣货单生成批发销货单通知单
    function create_notice_record($pick_ids, $store_code, $out_params) {
        $ret = $this->add_notice_check($pick_ids, '', $store_code);
        if ($ret['status'] == -1) {
            return $ret;
        }
        $notice_goods = $ret['data']['notice_goods'];
        $blind_record_info = $ret['data']['blind_record_info'];
        $po_no_str = $ret['data']['po_no_str'];
        $pick_no_str = $ret['data']['pick_no_str'];
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
        $notice_row['tel'] = $out_params['tel'];
        $notice_row['store_code'] = $store_code;
        $notice_row['rebate'] = 1;
        $notice_row['remark'] = "由PO:[{$ret['data']['po_no_str']}]，拣货单号:[{$pick_no_str}]生成，送往唯品会仓库：[{$ret['data']['warehouse_name']}]";
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
        if($ret['status']<1){
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
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未出库', 'action_name' => "创建", 'action_note' => '唯品会JIT，' . $notice_row['remark'], 'module' => "wbm_notice_record", 'pid' => $notice_id);
        load_model('pur/PurStmLogModel')->insert($log);

        //若绑定锁定单释放部分库存
        if (!empty($blind_record_info)) {
            $lof_notice_goods = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($notice_id, 'wbm_notice');
            //根据通知单释放锁定单数量
            $ret = $this->record_lock_release($lof_notice_goods['data'], $blind_record_info['lock_record'], $blind_record_info['po_blind']);
            if ($ret['status'] !=1) {
                 $this->rollback();
                return $ret;
            }
            $notice_main = load_model('wbm/NoticeRecordModel')->get_row(array('notice_record_id' => $notice_id));
            //更新实际占用锁定单的通知单号
            $relation_record = array(
                'record_code' => $blind_record_info['lock_record'],
                'relation_code' => $record_code,
                'relation_type' => 'wbm_notice',
                'inv_status' => 2,
                'describe' => "由PO:[{$po_no_str}]进行发货产生批发通知单，锁定库存",
                'num' => $notice_main['data']['num'],
            );
            $result = $this->add_lock_relation_record($relation_record);
            if ($result['status'] != 1) {
                $this->rollback();   
                return $result;
            }
        }

        //审核批发通知单
        $ret = load_model('wbm/NoticeRecordModel')->update_sure(1, 'is_sure', $notice_id);
        if ($ret['status'] <0) {
            $this->rollback();   
            return $this->format_ret(-1, '', '批发通知单审核失败，' . $ret['message']);
        }
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未出库', 'action_name' => '确认', 'module' => "wbm_notice_record", 'pid' => $notice_id);
        load_model('pur/PurStmLogModel')->insert($log);
        $this->commit();
        return $this->format_ret(1, array('notice_record' => $record_code));
    }

    //生成批发销货单
    function create_out_record($pick_ids, $notice_record, $out_params) {
        $pick_ret = $this->get_by_ids($pick_ids);
        $pick_ret = $pick_ret['data'];
        $warehouse = $pick_ret[0]['warehouse'];

        $pick_no_all = array();
        $po_no_arr = array();
        foreach ($pick_ret as $val) {
            $po_no = explode(",", $val['po_no']);
            $po_no_arr = array_merge($po_no_arr, $po_no);
            $pick_no_all[] = $val['pick_no'];
        }
        $pick_no_str = implode(",", $pick_no_all);
        $po_no_str = implode(",", array_unique($po_no_arr));

        //校验拣货单明细
        $pick_goods_ret = $this->get_detail_by_pick_no($pick_no_str);

        //通知单
        $notice_ret = load_model('wbm/NoticeRecordModel')->get_by_field('record_code', $notice_record);
        $notice_ret = $notice_ret['data'];
        $bill_sn = load_model('wbm/StoreOutRecordModel')->create_fast_bill_sn();

        //批发销货单主单信息

        $out_record['relation_code'] = $notice_ret['record_code'];
        $out_record['record_code'] = $bill_sn;
        $out_record['tel'] = $out_params['tel'];
        $out_record['express'] = $out_params['express'];
        $out_record['express_code'] = $out_params['express_code'];
        $out_record['record_time'] = date('Y-m-d H:i:s');
        $warehouse_name = load_model('api/WeipinhuijitWarehouseModel')->get_by_field('warehouse_code', $warehouse, 'warehouse_name');
        $warehouse_name = empty($warehouse_name['data']['warehouse_name']) ? '' : $warehouse_name['data']['warehouse_name'];
        $out_record['remark'] = "由PO:[{$po_no_str}]，拣货单号:[{$pick_no_str}]生成，送往唯品会仓库：[{$warehouse_name}]";
        $out_record['order_time'] = date('Y-m-d H:i:s');
        $out_record['store_code'] = $notice_ret['store_code'];
        $out_record['distributor_code'] = $notice_ret['distributor_code'];
        $out_record['name'] = $notice_ret['name'];
        $out_record['address'] = empty($notice_ret['address']) ? '' : $notice_ret['address'];
        $out_record['province'] = empty($notice_ret['province']) ? '' : $notice_ret['province'];
        $out_record['city'] = empty($notice_ret['city']) ? '' : $notice_ret['city'];
        $out_record['district'] = empty($notice_ret['district']) ? '' : $notice_ret['district'];
        $out_record['record_type_code'] = '200';

        $check_wms = $this->check_is_wms_store_code($out_record['store_code']);
        if ($check_wms === true) {//对接仓储 设置隐藏 作废状态
            $out_record['is_cancel'] = 1;
            $this->update_jit_wms_info($notice_record, $bill_sn);
        }

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
        foreach ($pick_goods_ret as $row) {
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
        $ret = load_model('wbm/StoreOutRecordDetailModel')->add_detail_action($store_out_record_id, $goods);

        if ($ret['status'] == -1) {
            return $this->format_ret(-1, '', '销货单生成失败');
        }
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '创建', 'action_note' => '唯品会JIT，' . $out_record['remark'], 'module' => "store_out_record", 'pid' => $store_out_record_id);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);

        //更新回写状态
        //流程调整
        $ret1 = load_model('wbm/NoticeRecordModel')->update_check('1', 'is_execute', $notice_ret['notice_record_id']);
        if ($ret1['status'] == -1) {
            return $this->format_ret(-1, '', '更新通知单执行状态失败');
        }


        $out_record_data = array();
        foreach ($pick_ret as $pick_row) {
            //批发销货单删除后再生成的销货单单号有可能与删掉的批发单据编号相同
            $sql = "delete from api_weipinhuijit_store_out_record where store_out_record_no=:store_out_record_no AND pick_no=:pick_no ";
            $sql_values = array(':store_out_record_no' => $bill_sn, ':pick_no' => $pick_row['pick_no']);
            $this->db->query($sql, $sql_values);
            //更新拣货单销货单表关联关系is_execute
            $out_record_data[] = array('shop_code' => $pick_row['shop_code'],
                'pick_no' => $pick_row['pick_no'],
                'notice_record_no' => $notice_record,
                'store_out_record_no' => $bill_sn,
                'po_no' => $pick_row['po_no'],
                'warehouse' => $pick_row['warehouse'],
                'brand_code' => $out_params['brand_code'],
                'insert_time' => date('Y-m-d H:i:s')
            );

            $ret = $this->check_pick($pick_row['id'], 0);
            if ($ret['status'] < 1) {
                return $ret;
            }
        }
        //print_r($record);die;
        load_model('api/WeipinhuijitStoreOutRecordModel')->insert_multi($out_record_data);
        //回写通知数
        $sql_values = array();
        $pick_no_str = $this->arr_to_in_sql_value($pick_no_all, 'pick_no', $sql_values);
        $sql = "update api_weipinhuijit_pick_goods set notice_stock=stock where pick_no in ({$pick_no_str}) ";
        $this->db->query($sql, $sql_values);
        //拣货单明细维护系统sku
        $sql = "update api_weipinhuijit_pick_goods pg,goods_sku gb set pg.sku=gb.sku where pg.barcode=gb.barcode and pg.pick_no  in ({$pick_no_str}) ";
        $this->db->query($sql, $sql_values);
        $sql = "update api_weipinhuijit_pick set notice_num=pick_num,notice_record_no='{$notice_record}' where  pick_no in ({$pick_no_str}) ";
        $this->db->query($sql, $sql_values);

        return $this->format_ret(1, $bill_sn, '销货单创建成功');
    }

    function create_fail_file($error_msg) {
        $fail_top = array('商品条码', '错误信息');
        $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
        $message = '';
//        $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }
    
    //库存不足导出信息，加商品编码
    function create_fail_file_new($error_msg) {
        $fail_top = array('商品条码','商品编码', '错误信息');
        $file_name = $this->create_import_fail_files_new($fail_top, $error_msg);
        $message = '';
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }   
    function create_import_fail_files_new($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $key = array_keys($val);
            $goods_code = $this->db->get_row("SELECT goods_code FROM goods_sku WHERE barcode = '{$key[0]}' ");
            if (empty($goods_code['goods_code'])) {
                $goods_code = $this->db->get_row("SELECT goods_code FROM goods_barcode_child WHERE barcode = '{$key[0]}' ");
            }
            $val_data = array($key[0], $goods_code['goods_code'], $val[$key[0]]);
            $file_str .= implode("\t,", $val_data) . "\r\n";
        }
        $filename = md5("store_out_record_import" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }
   
    /**
     * 条码转换为SKU（备用，启用去掉_backup）
     * @param string $pick_id 拣货单ID，多个逗号隔开
     * @return array 转换结果
     */
    function convert_barcode_to_sku_backup($pick_id) {
        $pick_ids_arr = explode(',', $pick_id);
        $sql_values = array();
        $pick_ids_str = $this->arr_to_in_sql_value($pick_ids_arr, 'pick_id', $sql_values);
        $sql = "SELECT DISTINCT pg.pick_no,pg.barcode FROM {$this->table} AS wp INNER JOIN {$this->detail_table} AS pg ON wp.pick_no=pg.pick_no WHERE wp.id IN({$pick_ids_str})";
        $pick_arr = $this->db->get_all($sql, $sql_values);
        $barcode = array_column($pick_arr, 'barcode');
        $sku_arr = $this->convert_barcode_sku($barcode);
        $sql_values = array();
        $sql = "UPDATE {$this->detail_table} SET sku = CASE barcode";
        $i = 1;
        foreach ($sku_arr as $barcode => $sku) {
            $b_str = ":barcode_{$i}";
            $s_str = ":sku_{$i}";
            $sql .= " WHEN {$b_str} THEN {$s_str} ";
            $sql_values[$b_str] = $barcode;
            $sql_values[$s_str] = $sku;
            $i++;
        }
        $pick_no_arr = array_column($pick_arr, 'pick_no');
        $pick_no_str = $this->arr_to_in_sql_value($pick_no_arr, 'pick_no', $sql_values);
        $pick_no_str = $sql .= " END WHERE pick_no IN({$pick_no_str})";
        $ret = $this->query($sql, $sql_values);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '识别条码失败');
        }
    }

    //根据通知单释放锁定单库存
    function record_lock_release($notice_goods, $lock_record, $po_blind) {
        //锁定单信息
        $lock_main = load_model('stm/StockLockRecordModel')->get_row(array('record_code' => $lock_record));
        if ($lock_main['status'] != 1) {
            return $this->format_ret('-1', '', '锁定单不存在！');
        }
        if ($lock_main['data']['order_status'] != 1) {
            return $this->format_ret('-1', '', "锁定单{$lock_record}未锁定库存！");
        }
        //获取批次明细
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($lock_main['data']['stock_lock_record_id'], 'stm_stock_lock');
        $sku_lof_arr = array();
        foreach ($ret_lof_details['data'] as $detail) {
            $sku_lof_arr[$detail['sku'] . '_' . $detail['lof_no']] = $detail;
        }
        $old_sku_arr = $sku_lof_arr;
        //判断条码在库存锁定单是否存在
        $no_exists_lock = array();
        //通知单的数量不得超过锁定单的数量
        $diff_num = array();
        //组装释放的条码
        $release_sku = array();
        foreach ($notice_goods as $key => $goods) {
            $sku_lof = $goods ['sku'] . '_' . $goods['lof_no'];
            if (!array_key_exists($sku_lof, $sku_lof_arr)) {
                $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $goods['sku']));
                $no_exists_lock[] = array($barcode => "通知单条码在锁定单({$lock_record})中不存在!");
                continue;
            }
            if ($goods['num'] > $sku_lof_arr[$sku_lof]['num']) {
                $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $goods['sku']));
                $diff_num[] = array($barcode => "通知单条码数量超过锁定单({$lock_record})中已锁定数量!");
                continue;
            } else {
                //部分释放库存
                $sku_lof_arr[$sku_lof]['num'] = $goods['num'];
            }
            //组装所需释放的sku
            $release_sku_lof[] = $sku_lof_arr[$sku_lof];
        }
        //返回错误信息
        if (!empty($no_exists_lock)) {
            $msg = $this->create_fail_file($no_exists_lock);
            return $this->format_ret(-1, '', '拣货单商品条码在锁定单' . $lock_record . '中不存在' . $msg);
        }
        if (!empty($diff_num)) {
            $msg = $this->create_fail_file($diff_num);
            return $this->format_ret(-1, '', '拣货单中条码数量超过锁定单' . $lock_record . '中已锁定数量' . $msg);
        }
        //扣减库存
        require_model('prm/InvOpModel');
        $this->begin_trans();
        $invobj = new InvOpModel($lock_record, 'stm_stock_lock', $lock_main['data']['store_code'], 0, $release_sku_lof);
        $ret = $invobj->adjust();
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //更新锁定单的状态
        foreach ($release_sku_lof as $b2b_detail) {
            $sku_lof = $b2b_detail['sku'] . '_' . $b2b_detail['lof_no'];
            $updata = array(
                'occupy_type' => 1,
                'num' => $old_sku_arr[$sku_lof]['num'] - $b2b_detail['num'],
                'fill_num' => $old_sku_arr[$sku_lof]['fill_num'] + $b2b_detail['num'],
            );
            $ret = $this->db->update('b2b_lof_datail', $updata, array('id' => $b2b_detail['id']));
            if (!$ret) {
                $this->rollback();
                return $this->format_ret(-1, '', '更新锁定单库存失败！');
            }
            //回写明细表
            $ret = load_model('stm/StockLockRecordDetailModel')->mainWriteBackDetail($lock_main['data']['stock_lock_record_id'], $b2b_detail['sku']);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '回写失败！');
            }
        }
        //回写主单
        load_model('stm/StockLockRecordDetailModel')->mainWriteBack($lock_main['data']['stock_lock_record_id']);
        //更新锁定单绑定关系表
        $ret = $this->update_lock_blind($lock_record, $po_blind);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '更新关联表失败！');
        }
        $this->commit();
        return $this->format_ret(1, '', '库存锁定单释放成功！');
    }

    
    //验证绑定
    function check_blind_check($po_no_str, $store_code) {
        $sql_value = array();
        $po = explode(',', $po_no_str);
        $po_str = $this->arr_to_in_sql_value($po, 'po_no', $sql_value);
        $sql = "SELECT * FROM api_weipinhuijit_po WHERE notice_id<>0 AND relation_type=1 AND po_no IN ({$po_str})";
        $po_data = $this->db->get_all($sql, $sql_value);
        if (!empty($po_data)) {
            $lock_record = $po_data[0]['notice_record_no'];
            //多po时锁定单必须相同
            if ($this->jit_version == 2) {
                foreach ($po_data as $key => $value) {
                    if ($lock_record != $value['notice_record_no']) {
                        return $this->format_ret('-1', '', '档期绑定的锁定单必须相同！');
                    }
                    $blind_po_arr[] = $value['po_no'];
                }
            }
            $lock_main = load_model('stm/StockLockRecordModel')->get_row(array('stock_lock_record_id' => $po_data[0]['notice_id']));
            if ($lock_main['data']['store_code'] != $store_code) {
                return $this->format_ret(-1, '', '出库仓库与锁定单仓库不同！');
            }
            if ($this->jit_version == 2) {
                $po_blind = $blind_po_arr;
            } else {
                $po_blind = $po_data[0]['po_no'];
            }
            return $this->format_ret(1, array('lock_record' => $lock_record, 'po_blind' => $po_blind) , '');
        } else {
            return $this->format_ret(-2, '', '');
        }
    }

    //更新锁定单关联关系
    function update_lock_blind($record_code, $relation_code, $relation_type = 'po_no') {
        $sql_value = array();
        $relation_code = is_array($relation_code) ? $relation_code : array($relation_code);
        $relation_code_str = $this->arr_to_in_sql_value($relation_code, 'po_no', $sql_value);
        $sql = "UPDATE stm_stock_lock_relation_record SET inv_status=1 WHERE record_code=:record_code AND relation_code IN ({$relation_code_str}) AND relation_type=:relation_type ";
        $sql_value[':record_code'] = $record_code;
        $sql_value[':relation_type'] = $relation_type;
        $ret = $this->query($sql, $sql_value);
        return $ret;
    }
    
    //实际占用锁定单的单据
    function add_lock_relation_record($params) {
        $params['add_time'] = date('Y-m-d H:i:s');
        $ret = $this->insert_exp('stm_stock_lock_relation_record', $params);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '插入关联表失败！');
        }
        return $ret;
    }


    function update_price_params($value) {
        $data = array('value' => $value);
        $ret = $this->update_exp('sys_params', $data, array('param_code' => 'supply_price'));
        return $ret;
    }


    /**
     * 删除拣货单
     * @param $pick_no
     * @return array
     */
    function delete_by_pick_no($pick_no) {
        //判断是否满足删除条件
        $pick = $this->get_row(array('pick_no' => $pick_no));
        if ($pick['status'] != 1) {
            return $this->format_ret(-1, '', '拣货单不存在！');
        }
        $pick_data = $pick['data'];
        if ($pick_data['is_execute'] != 0) {
            return $this->format_ret(-1, '', '已生成批发销货单，不能删除！');
        }
        $this->begin_trans();
        //主单
        $ret = $this->delete_exp('api_weipinhuijit_pick', array('pick_no' => $pick_no));
        if (!$ret) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除主单失败！');
        }
        //明细
        $ret = $this->delete_exp('api_weipinhuijit_pick_goods', array('pick_no' => $pick_no));
        if (!$ret) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除主单失败！');
        }
        //日志
        $log_xq = "删除拣货单:{$pick_no}";
        $log = array(
            'user_id' => CTX()->get_session('user_id'),
            'user_code' => CTX()->get_session('user_code'),
            'ip' => '', 'add_time' => date('Y-m-d H:i:s'),
            'module' => '进销存',
            'yw_code' => $pick_no,
            'operate_type' => '删除',
            'operate_xq' => $log_xq
        );
        $ret = load_model('sys/OperateLogModel')->insert($log);
        $this->commit();
        return $this->format_ret(1, '', '删除成功！');
    }

}
