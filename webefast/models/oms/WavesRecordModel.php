<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

class WavesRecordModel extends TbModel {

    //波次单打印模板显示字段
    public $print_fields_default = array(
        'record' => array(
            '波次号' => 'record_code',
            '仓库' => 'store_name',
            '商品总数量' => 'goods_num',
            '商品总金额' => 'total_amount',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
            '拣货员' => 'picker_name',
        ),
        'detail' => array(
            array(
                '交易号' => 'deal_code',
                '订单号' => 'sell_record_code',
                '序号' => 'sort_num',
                '蓝位号' => 'sort_no',
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '商品简称' => 'goods_short_name',
                '商品分类' => 'category_name',
                '商品品牌' => 'brand_name',
                '商品季节' => 'season_name',
                '商品年份' => 'year_name',
                '商品属性' => 'goods_prop',
                '生产周期' => 'goods_days',
                '商品描述' => 'goods_desc',
                //'商品套餐条形码'=>'combo_barcode',
                '规格1' => 'spec1_name',
                '规格1代码' => 'spec1_code',
                '规格2' => 'spec2_name',
                '规格2代码' => 'spec2_code',
                '条形码' => 'barcode',
                '批次号' => 'lof_no',
                '均摊金额' => 'avg_money',
                '数量' => 'num',
                '库位代码' => 'shelf_code',
                '库位' => 'shelf_name',
                '扩展属性1' => 'property_val1',
                '扩展属性2' => 'property_val2',
                '扩展属性3' => 'property_val3',
                '扩展属性4' => 'property_val4',
                '扩展属性5' => 'property_val5',
                '扩展属性6' => 'property_val6',
                '扩展属性7' => 'property_val7',
                '扩展属性8' => 'property_val8',
                '扩展属性9' => 'property_val9',
                '扩展属性10' => 'property_val10',
            ),
        ),
    );

    /**
     * @var string 表名
     */
    protected $table = 'oms_waves_record';

    /**
     * 根据条件查询数据
     * @param $filter
     * @return array
     */
    function get_by_page($filter) {
        //去掉空格
        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }

        $sql_values = array();
        $sql_tb = "FROM {$this->table} a
          INNER JOIN oms_deliver_record b ON b.waves_record_id = a.waves_record_id
          LEFT JOIN oms_sell_record r1 ON r1.sell_record_code = b.sell_record_code
          ";
        $sql_detail = '';
        $sql_goods = '';

        $sql_main = " WHERE 1 ";

        //订单性质查询
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] !== '') {
            $sell_record_attr_arr = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = array();
            foreach ($sell_record_attr_arr as $attr) {
                if ($attr == 'attr_lock') {
                    $sql_attr_arr[] = " r1.is_lock = 1";
                }
//                if ($attr == 'attr_pending') {
//                    $sql_attr_arr[] = " r1.is_pending = 1";
//                }
//                if ($attr == 'attr_problem') {
//                    $sql_attr_arr[] = " r1.is_problem = 1";
//                }
//                if ($attr == 'attr_bf_quehuo') {
//                    $sql_attr_arr[] = " (r1.must_occupy_inv = 1 and r1.lock_inv_status = 2)";
//                }
//                if ($attr == 'attr_all_quehuo') {
//                    $sql_attr_arr[] = " (r1.must_occupy_inv = 1 and r1.lock_inv_status = 3)";
//                }
                if ($attr == 'attr_combine') {
                    $sql_attr_arr[] = " r1.is_combine_new = 1";
                }
                if ($attr == 'attr_split') {
                    $sql_attr_arr[] = " r1.is_split_new = 1";
                }
                if ($attr == 'attr_change') {
                    $sql_attr_arr[] = " r1.is_change_record = 1";
                }
                if ($attr == 'attr_handwork') {
                    $sql_attr_arr[] = " r1.is_handwork = 1";
                }
                if ($attr == 'attr_copy') {
                    $sql_attr_arr[] = " r1.is_copy = 1";
                }
                if ($attr == 'attr_presale') {
                    $sql_attr_arr[] = " r1.sale_mode = 'presale'";
                }
                if ($attr == 'attr_fenxiao') {
                    $sql_attr_arr[] = " (r1.is_fenxiao = 1 OR r1.is_fenxiao = 2) ";
                }
                if ($attr == 'is_rush') {
                    $sql_attr_arr[] = " r1.is_rush = 1";
                }
                if ($attr == 'is_problem') {
                    $sql_attr_arr[] = " (r1.must_occupy_inv = '1' AND r1.lock_inv_status = '1'  AND r1.is_pending = '0' AND r1.is_problem = '0') ";
                }
                if ($attr == 'is_replenish') {
                    $sql_attr_arr[] = " r1.is_replenish = 1";
                }
            }
            $sql_main .= ' AND ' . join(' or ', $sql_attr_arr);
        }

        //仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('a.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('b.shop_code', $filter_shop_code);
        //波次号
        if (!empty($filter['record_code'])) {
            $sql_main .= " AND a.record_code LIKE :record_code ";
            $sql_values[':record_code'] = '%' . $filter['record_code'] . '%';
        }
        //订单号
        if (isset($filter['sell_record_code']) && !empty($filter['sell_record_code'])) {
            $sql_main .= " AND b.sell_record_code LIKE :sell_record_code ";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }
        //快递单号
        if (isset($filter['express_no']) && !empty($filter['express_no'])) {
            $sql_main .= " AND b.express_no LIKE :express_no ";
            $sql_values[':express_no'] = '%' . $filter['express_no'] . '%';
        }
        //条码
        if (isset($filter['barcode']) && !empty($filter['barcode'])) {
            $sql_detail = ' INNER JOIN oms_deliver_record_detail c ON c.deliver_record_id = b.deliver_record_id';
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND c.sku in({$sku_str}) ";
            }
        }

        //销售平台
        if (isset($filter['source']) && !empty($filter['source'])) {
            $sql_main .= " AND b.sale_channel_code in (:source) ";
            $sql_values[':source'] = explode(',', $filter['source']);
        }
        //是否验收
        if (isset($filter['check_accept']) && !empty($filter['check_accept'])) {
            if ($filter['check_accept'] == 'no_accept') {
                $sql_main .= " AND a.is_accept = 0 ";
            } else {
                $sql_main .= " AND a.is_accept = 1 ";
            }
        }
        //是否发货
        if (isset($filter['check_deliver']) && !empty($filter['check_deliver'])) {
            if ($filter['check_deliver'] == 'no_deliver') {
                $sql_main .= " AND a.is_deliver = 0 ";
            } elseif ($filter['check_deliver'] == 'bf_deliver') {
                $sql_main .= " AND a.is_deliver = 2 ";
            } elseif ($filter['check_deliver'] == 'deliver') {
                $sql_main .= " AND a.is_deliver = 1 ";
            } else {
                $sql_main .= " AND a.is_cancel = 1 ";
            }
        }
        //店铺
        if (isset($filter['shop_code']) && !empty($filter['shop_code'])) {
            $sql_main .= " AND b.shop_code in (:shop_code) ";
            $sql_values[':shop_code'] = explode(',', $filter['shop_code']);
        }

        //商品名称
        if (isset($filter['goods_name']) && !empty($filter['goods_name'])) {
            $sql_detail = ' INNER JOIN oms_deliver_record_detail c ON c.deliver_record_id = b.deliver_record_id';
            $sql_goods = '   INNER  JOIN base_goods gd ON gd.goods_code = c.goods_code    ';
            $sql_main .= " AND gd.goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }

        //买家昵称
        if (isset($filter['buyer_name']) && !empty($filter['buyer_name'])) {

            $customer_code_arr = load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
            if (!empty($customer_code_arr)) {
                $customer_code_str = "'" . implode("','", $customer_code_arr) . "'";
                $sql_main .= " AND ( b.customer_code in ({$customer_code_str}) ) ";
            } else {
                $sql_main .= " AND b.buyer_name = :buyer_name ";
                $sql_values[':buyer_name'] = $filter['buyer_name'];
            }
//            $sql_main .= " AND b.buyer_name LIKE :buyer_name ";
//            $sql_values[':buyer_name'] = '%' . $filter['buyer_name'] . '%';
        }

        //收货人
        if (isset($filter['receiver_name']) && !empty($filter['receiver_name'])) {
//            $sql_main .= " AND b.receiver_name LIKE :receiver_name ";
//            $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND ( b.receiver_name LIKE :receiver_name  OR b.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            } else {
                $sql_main .= " AND b.receiver_name LIKE :receiver_name ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            }
        }
        //订单商品数量
        if (isset($filter['sell_num_type']) && !empty($filter['sell_num_type'])) {
            $sql_main .= " AND a.sell_num_type=:sell_num_type ";
            $sql_values[':sell_num_type'] = $filter['sell_num_type'];
        }
        //交易号
        if (isset($filter['deal_code']) && !empty($filter['deal_code'])) {
            $sql_main .= " AND b.deal_code LIKE :deal_code ";
            $sql_values[':deal_code'] = '%' . $filter['deal_code'] . '%';
        }
        //商品
        if (isset($filter['goods_code']) && !empty($filter['goods_code'])) {
            $sql_detail = ' INNER JOIN oms_deliver_record_detail c ON c.deliver_record_id = b.deliver_record_id';
            $sql_main .= " AND c.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //验收
        if (isset($filter['is_accept']) && !empty($filter['is_accept'])) {
            $sql_main .= " AND a.is_accept = :is_accept ";
            $sql_values[':is_accept'] = $filter['is_accept'];
        }
        //仓库
        if (isset($filter['store_code']) && !empty($filter['store_code'])) {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND a.store_code in ( " . $str . " ) ";
        }
        //配送方式
        if (isset($filter['express_code']) && !empty($filter['express_code'])) {
            $arr = explode(',', $filter['express_code']);
            $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
            $sql_main .= " AND a.express_code in ( " . $str . " ) ";
        }
        //是否打印商品
        if (isset($filter['is_print_goods']) && !empty($filter['is_print_goods'])) {
            $sql_main .= " AND a.is_print_goods = :is_print_goods ";
            $sql_values[':is_print_goods'] = $filter['is_print_goods'];
        }
        //是否打印快递单
        if (isset($filter['is_print_express']) && !empty($filter['is_print_express'])) {
            $sql_main .= " AND a.is_print_express = :is_print_express ";
            $sql_values[':is_print_express'] = $filter['is_print_express'];
        }
        //是否打印订单
        if (isset($filter['is_print_sellrecord']) && !empty($filter['is_print_sellrecord'])) {
            $sql_main .= " AND a.is_print_sellrecord = :is_print_sellrecord ";
            $sql_values[':is_print_sellrecord'] = $filter['is_print_sellrecord'];
        }
        //制单时间
        if (!empty($filter['record_time_start'])) {
            $sql_main .= " AND a.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['record_time_end'])) {
            $sql_main .= " AND a.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
        }
        //拣货员
        if (isset($filter['staff_code']) && $filter['staff_code'] != '') {
            $staff_code_arr = explode(',', $filter['staff_code']);
            $staff_code_str = $this->arr_to_in_sql_value($staff_code_arr, 'picker', $sql_values);
            $sql_main .= " AND a.picker IN({$staff_code_str})";
        }
        if (isset($filter['is_deliver']) && $filter['is_deliver'] != '') {
            $sql_main .= " AND a.is_deliver = :is_deliver ";
            $sql_values[':is_deliver'] = $filter['is_deliver'];
        }

        //订单波次打印tab页面
        if (isset($filter['do_list_tab']) && $filter['do_list_tab'] !== '') {
            //待打印快递单
            if ($filter['do_list_tab'] == 'tabs_print_express') {
                $sql_main .= " and a.is_print_express <> 2  and a.is_cancel=0";
            }
            //待打印发货单
            if ($filter['do_list_tab'] == 'tabs_print_sellrecord') {
                $sql_main .= " and a.is_print_sellrecord <> 2 and a.is_cancel=0";
            }
            //待验收
            if ($filter['do_list_tab'] == 'tabs_accept') {
                $sql_main .= " and a.is_accept = 0 and a.is_cancel= 0";
            }
            //待发货is_cancel
            if ($filter['do_list_tab'] == 'tabs_sending') {
                $sql_main .= " and (a.is_deliver = 0 || a.is_deliver = 2) and a.is_cancel = 0 and a.is_accept = 1";
            }
            //已发货
            if ($filter['do_list_tab'] == 'tabs_sended') {
                $sql_main .= " and a.is_deliver = 1 and a.is_cancel = 0";
            }
            //已取消
            if ($filter['do_list_tab'] == 'tabs_cancel') {
                $sql_main .= " and a.is_cancel = 1";
            }
        }


        $select = ' a.*';
        $sql_main = $sql_tb . $sql_detail . $sql_goods . $sql_main;
        $sql_main .= " GROUP BY a.waves_record_id ORDER BY a.waves_record_id DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $do_cancel_privilege = load_model('sys/PrivilegeModel')->check_priv('oms/waves_record/do_cancel');
        $data_by_key = array();
        //如果查询数据不存在，直接返回，避免数据为空时，in('')查询出waves_record_id=0的数据，导致bug.
        if(empty($data['data'])){
            return $this->format_ret(1, $data);
        }
        foreach ($data['data'] as $key => &$value) {
            $value['do_cancel_privilege'] = (string) $do_cancel_privilege;
            $value['checkbox_html'] = "<input type='checkbox' name='ckb_record_id' value='{$value['waves_record_id']}'>";
            $data_by_key[$value['waves_record_id']] = $key;
            $url = "?app_act=oms/waves_record/view&waves_record_id={$value['waves_record_id']}";
            $value['record_code_href'] = "<a href=\"{$url}\">" . $value['record_code'] . "</a>";
            $value['record_time'] = date('Y-m-d', strtotime($value['record_time']));
            $total_sell_record= load_model('oms/DeliverRecordModel')->get_order_num($value['waves_record_id']);
            $value['total_sell_record']=$total_sell_record['cnt'];
            if ($value['is_print_express'] == 0) {
                $value['html_print_express'] = "未打印";
            }
            if ($value['is_print_express'] == 1) {
                $value['html_print_express'] = "部分打印";
            }
            if ($value['is_print_express'] == 2) {
                $value['html_print_express'] = "全部打印";
            }
            if ($value['is_print_sellrecord'] == 0) {
                $value['html_print_sellrecord'] = "未打印";
            }
            if ($value['is_print_sellrecord'] == 1) {
                $value['html_print_sellrecord'] = "部分打印";
            }
            if ($value['is_print_sellrecord'] == 2) {
                $value['html_print_sellrecord'] = "全部打印";
            }
            $data['data'][$key]['is_deliver_count'] = 0;
        }
        $this->set_waves_record_data($data_by_key, $data['data']);
        foreach($data["data"] as &$val){
            $val['is_deliver_count']=$val['sell_record_count']-$val['is_deliver_count'];
        }
        return $this->format_ret(1, $data);
    }

    function set_waves_record_data($data_by_key, &$data) {
        $waves_record_id_arr = array_keys($data_by_key);
        $waves_record_id_str = "'" . implode("','", $waves_record_id_arr) . "'";
        $sql1 = "select count(1) as cnt,waves_record_id from oms_deliver_record where is_deliver = '1' and waves_record_id  in({$waves_record_id_str}) GROUP BY waves_record_id";
        $data1 = $this->db->get_all($sql1);
        //  var_dump($data1);die;
        foreach ($data1 as $val1) {
            $data[$data_by_key[$val1['waves_record_id']]]['is_deliver_count'] =$val1['cnt'];
        }

//        $sql2 = "select count(1) as cnt,waves_record_id from oms_deliver_record where  waves_record_id  in({$waves_record_id_str}) GROUP BY waves_record_id";
//        $data2 = $this->db->get_all($sql2);
//           foreach($data2 as $val2){
//               $data[$data_by_key[$val2['waves_record_id']]]['sell_record_count']  = $val2['cnt'];$data[$data_by_key[$val1['waves_record_id']]]['effective_record']-
//           }
    }

    /**
     * 供波次单打印使用的方法, 请勿随意修改
     * @param $id
     * @return array|bool
     */
    public function get_record_by_id($id) {
        $data = $this->db->get_row("select * from oms_waves_record where waves_record_id = :id", array('id' => $id));
        $data['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $data['store_code']));
        $picker = load_model('base/StoreStaffModel')->get_by_code($data['picker']);
        $data['picker_name'] = $picker['data']['staff_name'];
        return $data;
    }

    /**
     * 供波次单打印使用的方法, 请勿随意修改
     * @param $id
     * @return array|bool
     */
    public function get_deliver_detail_list_by_pid($id) {
        $record = $this->db->get_row("select * from oms_waves_record where waves_record_id = :id", array('id' => $id));
        if (empty($record)) {
            return array();
        }

        $data = $this->db->get_all("select * from oms_deliver_record_detail where waves_record_id = :id", array('id' => $id));

        foreach ($data as $key => &$value) {

            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'goods_name', 'goods_short_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($value, $sku_info);
            $value['shelf_code'] = oms_tb_val('goods_shelf', 'shelf_code', array('store_code' => $record['store_code'], 'sku' => $value['sku']));
            $value['lof_no'] = load_model('oms/DeliverRecordModel')->get_lof_no($value['sell_record_code'], $value['sku']);
        }

        //合并重复的sku
        $count = count($data);
        foreach ($data as $key => &$value) {
            for ($i = $key + 1; $i < $count; $i++) {
                if (!isset($data[$i]))
                    continue;
                if ($data[$i]['sku'] == $value['sku'] && $data[$i]['shelf_code'] == $value['shelf_code']) {
                    $value['num'] += $data[$i]['num'];
                    unset($data[$i]);
                }
            }
        }
        $data = array_values($data);

        return $data;
    }

    public function get_deliver_detail_with_is_not_cancel($id) {
        $record = $this->db->get_row("select * from oms_waves_record where waves_record_id = :id", array('id' => $id));
        if (empty($record)) {
            return array();
        }
        $sql = "select detail.*,record.sort_no from oms_deliver_record_detail detail
    			inner join oms_deliver_record record on record.deliver_record_id = detail.deliver_record_id
    			where detail.waves_record_id = :id and record.is_cancel = 0";
        $data = $this->db->get_all($sql, array('id' => $id));
        foreach ($data as $key => &$value) {
//    		$value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code'=>$value['goods_code']));
//    		$value['goods_short_name'] = oms_tb_val('base_goods', 'goods_short_name', array('goods_code'=>$value['goods_code']));
//    		$value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code'=>$value['spec1_code']));
//    		$value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code'=>$value['spec2_code']));
            $key_arr = array('barcode', 'spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'goods_name', 'goods_short_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($value, $sku_info);

            $value['shelf_code'] = oms_tb_val('goods_shelf', 'shelf_code', array('store_code' => $record['store_code'], 'sku' => $value['sku']));
            $value['lof_no'] = load_model('oms/DeliverRecordModel')->get_lof_no($value['sell_record_code'], $value['sku']);
        }

        $count = count($data);
        foreach ($data as $key => &$value) {
            for ($i = $key + 1; $i < $count; $i++) {
                if (!isset($data[$i]))
                    continue;
                if ($data[$i]['sku'] == $value['sku'] && $data[$i]['shelf_code'] == $value['shelf_code'] && $value['sort_no'] == $data[$i]['sort_no']) {
                    if ($value['deal_code'] != $data[$i]['deal_code']) { //合并订单,交易号不同，拼接在一起
                        $value['deal_code'] .= "," . $data[$i]['deal_code'];
                    }
                    $value['num'] += $data[$i]['num'];
                    unset($data[$i]);
                }
            }
        }
        //合并重复的sku
        $count = count($data);
        $data = array_values($data);
        foreach ($data as $key => &$value) {
            //$value['sort_no'] = $value['sort_no'] . "(" . $value['num'] . ")";
            $value['sort_no'] = $value['num'] . "(" . $value['sort_no'] . ")";
            for ($i = $key + 1; $i < $count; $i++) {
                if (!isset($data[$i]))
                    continue;
                if ($data[$i]['sku'] == $value['sku'] && $data[$i]['shelf_code'] == $value['shelf_code']) {
                    $value['sort_no'] .= ',' . $data[$i]['num'] . "(" . $data[$i]['sort_no'] . ")";
                    $value['num'] += $data[$i]['num'];
                    $value['deal_code'] .= ',' . $data[$i]['deal_code'];
                    $value['sell_record_code'] .= ',' . $data[$i]['sell_record_code'];
                    unset($data[$i]);
                }
            }
        }
        $data = array_values($data);

        return $data;
    }

    /**
     * Get oms_deliver_record list, according to waves_record_id.
     * @param $ids
     * @return array|bool
     */
    public function get_deliver_record_ids($ids) {
        $str = implode(',', $ids);
        if (empty($str)) {
            return array('status' => '-1', 'data' => '', 'message' => '传入参数不正确');
        }
        //打印快递单不打印取消状态的 is_cancel=0
        $recordList = $this->db->get_all("select * from oms_deliver_record where is_cancel=0 and waves_record_id in ($str)");

        $idList = array();
        foreach ($recordList as $row) {
            $idList[] = $row['deliver_record_id'];
        }
        return array('status' => '1', 'data' => $idList, 'message' => '验收成功');
    }

    /**
     * @param $ids
     * @return array|bool
     */
    public function get_deliver_record_list_by_ids($ids) {
        $str = implode(',', $ids);
        if (empty($str)) {
            return array();
        }

        return $this->db->get_all("select * from oms_deliver_record where waves_record_id in ($str)");
    }

    /**
     * 验收波次单
     * @param $id 波次单ID
     * @param $opt_user 操作员
     * @return array
     */
    public function accept($id, $opt_user = '') {
        $row = $this->get_record_by_id($id);
        if (empty($row)) {
            return $this->format_ret(-1, '', '单据不存在');
        }
        if ($row['is_accept'] == 1) {
            return $this->format_ret(-1, '', '单据已验收');
        }

        if ($row['is_cancel'] == 1) {
            return $this->format_ret(-1, '', '单据已取消');
        }
        $this->begin_trans();
        try {
            $d = array(
                'is_accept' => '1',
                'accept_time' => date('Y-m-d H:i:s'),
                'accept_user' => empty($opt_user) ? CTX()->get_session('user_name') : $opt_user,
            );

            $r = $this->update($d, array('waves_record_id' => $id));

            //更新前先校验波次中是否存在未拣货的订单
            $sql_count = "SELECT COUNT(1) FROM oms_sell_record WHERE waves_record_id=:_id AND order_status=1 AND shipping_status=2";
            $count = $this->db->get_value($sql_count, [':_id' => $id]);
            if ($count > 0) {
                //修改订单验收状态
                $this->db->update('oms_sell_record', array('shipping_status' => 3), array('waves_record_id' => $id, 'shipping_status' => 2, 'order_status' => 1));
                $ret = $this->affected_rows();
                if ($ret <= 0) {
                    $this->rollback();
                    return $this->format_ret('-1', '', '修改出错');
                }
            }

            //验收后重置扫描数量
            $sql = "update oms_deliver_record_detail set scan_num = 0 where waves_record_id = :id";
            $this->db->query($sql, array('id' => $id));

            // 全链路
            $sql = "select sale_channel_code, shop_code, deal_code FROM oms_deliver_record where waves_record_id in ($id) and is_cancel = 0";
            $l = $this->db->get_all($sql);
            foreach ($l as $k => $v) {
                load_model('oms/SellRecordActionModel')->add_action_to_api($v['sale_channel_code'], $v['shop_code'], $v['deal_code'], 'check_wave');
                load_model('oms/SellRecordActionModel')->add_action_to_api($v['sale_channel_code'], $v['shop_code'], $v['deal_code'], 'check_wave');
            }

            $this->commit();
            $sql = "select sell_record_code,store_code,shop_code from oms_deliver_record where waves_record_id = :waves_record_id  AND is_cancel=0 ";
            $data_order = $this->db->get_all($sql, array('waves_record_id' => $id));

            foreach ($data_order as $v) {
                //中间对接扫描单据 scan
                $ret = load_model('mid/MidBaseModel')->set_mid_record('scan', $v['sell_record_code'], 'sell_record', $v['store_code'], $v['shop_code']);

                $action_note = empty($opt_user) ? '' : '操作人：' . $opt_user;
                load_model('oms/SellRecordModel')->add_action($v['sell_record_code'], '波次单验收', $action_note);
            }
            return array('status' => '1', 'data' => '', 'message' => '验收成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    public function print_data_default_new($request) {
        $id = $request['record_ids'];
        $r = array();
//        $r['record'] = $this->db->get_row("select * from oms_waves_record where waves_record_id = :id", array(':id' => $id));
        $r['record'] = $this->get_record_by_id($id);
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        if ($ret_arr['lof_status'] == 1) {
            $r['detail'] = $this->get_print_info_with_lof_no($r['record']['record_code']);
        } else {
//            $sql = "select f.*
//                    from oms_deliver_record d inner join oms_deliver_record_detail f on d.deliver_record_id=f.deliver_record_id
//                    where d.waves_record_id=:id";
//            $r['detail'] = $this->db->get_all($sql, array(':id' => $id));
            $r['detail'] = $this->get_deliver_detail_with_is_not_cancel($id);
            $sku_array = array();
            $shelf_code_arr = array();
            $money = 0;
            $sku_list = array();
            $list = array();
            $list_sell = array();
            foreach ($r['detail'] as $key => &$detail) {//合并同一sku
                if (in_array($detail['sku'], $sku_list)) {
                    if (strpos($list[$detail['sku']], $detail['deal_code']) === false) {
                        $list[$detail['sku']] = $list[$detail['sku']] . ',' . $detail['deal_code'];
                        $list_sell[$detail['sku']] = $list_sell[$detail['sku']] . ',' . $detail['sell_record_code'];
                    }
                    $list_num[$detail['sku']] = $list_num[$detail['sku']] + $detail['num'];
                    unset($r['detail'][$key]);
                    continue;
                } else {
                    $list_num[$detail['sku']] = $detail['num'];
                    $list_sell[$detail['sku']] = $detail['sell_record_code'];
                    $list[$detail['sku']] = $detail['deal_code'];
                    $sku_list[] = $detail['sku'];
                }
                $shelf_arr = $this->get_shelf_code_new($detail['sku'], $r['record']['store_code']);
                if (empty($shelf_arr)) {
                    $detail['shelf_code'] = '';
                    $detail['shelf_name'] = '';
                } else {
                    list($detail['shelf_code'], $detail['shelf_name']) = $shelf_arr;
                }
                $key_arr = array('goods_name', 'goods_short_name', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'barcode', 'category_name', 'remark');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
                $r['detail'][$key] = array_merge($detail, $sku_info);

                $money = $money + $detail['money'];
            }
            foreach ($r['detail'] as $key => &$detail) {
                $detail['deal_code_total'] = $list[$detail['sku']];
                $detail['sell_record_code_total'] = $list_sell[$detail['sku']];
                $detail['num_total'] = $list_num[$detail['sku']];
            }
            $r['record']['sum_money'] = $money;
        }
        array_multisort($shelf_code_arr, SORT_ASC, $r['detail']);
        $this->print_data_escape($r['record'], $r['detail']);
        $trade_data = array($r['record']);
        //更新波次单打印标识
        $this->update_exp('oms_waves_record', array('is_print_waves' => 1), array('waves_record_id' => $id));
        return $r;
    }

    private function get_shelf_code_new($sku, $store_code) {

        $sql = "select a.shelf_code from goods_shelf a
        where a.store_code = :store_code and a.sku = :sku order by a.sku,a.shelf_code asc";
        $l = $this->db->get_all($sql, array(':store_code' => $store_code, ':sku' => $sku));
        $arr = array();
        $arr1 = array();
        foreach ($l as $_v) {
            $arr[] = $_v['shelf_code'];
//            $arr1[] = oms_tb_val('base_shelf', 'shelf_name', array('shelf_code' => $_v['shelf_code']));
            $arr1[] = oms_tb_val('base_shelf', 'shelf_name', array('shelf_code' => $_v['shelf_code'], 'store_code' => $store_code));
        }
        return array(implode(',', $arr), implode(',', $arr1));
    }

    public function update_express($express_code, $record_code) {

        $d = array(
            'express_code' => $express_code,
        );
        $r = $this->update($d, array('record_code' => $record_code));
    }

    /**
     * Deleting row.
     * @param $id
     * @return array
     */
    public function do_delete($id) {
        $record = $this->get_row(array('waves_record_id' => $id));
        if (empty($record['data'])) {
            return array('status' => '-1', 'data' => '', 'message' => '单据不存在');
        }
        $record = $record['data'];
        if ($record['is_accept'] != '0') {
            return array('status' => '-1', 'data' => '', 'message' => '单据已验收, 不能删除');
        }

        $this->begin_trans();
        try {
            $r = $this->delete(array('waves_record_id' => $id));

            $r = $this->db->query('update oms_deliver_record_detail set waves_record_id = 0 where waves_record_id = :id', array('id' => $id));

            $this->commit();
            return array('status' => '1', 'data' => '', 'message' => '删除成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    /**
     * Cancel row.
     * @param $id
     * @return array
     * $is_accept_check = 0， 如果检货单已验收,在订单拦截时，也要允许取消检货单中的订单
     */
    public function cancel($id, $is_accept_check = 1, $remark = '') {
        $record = $this->get_row(array('waves_record_id' => $id));
        if (empty($record['data'])) {
            return array('status' => '-1', 'data' => '', 'message' => '单据不存在');
        }
        $record = $record['data'];
        if ($record['is_accept'] != '0' && $is_accept_check == 1) {
            return array('status' => '-2', 'data' => '', 'message' => '单据已验收, 不能取消');
        }
        if ($record['is_cancel'] != '0') {
            return array('status' => '-10', 'data' => '', 'message' => '单据已取消');
        }

        require_model('oms/DeliverRecordModel');
        $sql = "select * from oms_deliver_record where is_cancel= 0 and is_deliver=0 and waves_record_id = {$id}";
        $oms_deliver_record_arr = $this->db->get_all($sql);
        $this->begin_trans();
        try {
            //$r = $this->delete(array('waves_record_id'=>$id));
            if (!empty($oms_deliver_record_arr)) {
                foreach ($oms_deliver_record_arr as $record) {
                    $record['remark'] = $remark;
                    $ret = load_model('oms/DeliverRecordModel')->_cancel($record);
                    if ($ret['status'] < 0) {
                        $this->rollback();
                        return $ret;
                    }
                }
                $this->commit();
            } else {
                $this->commit();
                return array('status' => '-1', 'data' => '', 'message' => '波次单明细不存在或波次单已发货已取消');
            }
            return array('status' => '1', 'data' => '', 'message' => '取消成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    function edit_express_code($wavesRecordId, $expressCode) {
        $this->begin_trans();
        try {
            $ids = implode(',', $wavesRecordId);

            $sql = "update oms_waves_record set express_code = :express_code, is_print_express = 0
            where waves_record_id IN ($ids)";
            $this->query($sql, array('express_code' => $expressCode));

            $sql = "update oms_deliver_record set express_code = :express_code, express_no = '', is_print_express = 0
            where waves_record_id IN ($ids)";
            $this->query($sql, array('express_code' => $expressCode));

            $sql = "update oms_sell_record set express_code = :express_code, express_no = '', is_print_express = 0
            where waves_record_id IN ($ids)";
            $this->query($sql, array('express_code' => $expressCode));

            $this->commit();
            return array('status' => '1', 'data' => '', 'message' => '生成成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    private $recordFields = array(
        'sell_record_code',
        'deal_code',
        'deal_code_list',
        'sale_channel_code',
        'store_code',
        'shop_code',
        'pay_type',
        'pay_code',
        'pay_time',
        'goods_num',
        'sku_num',
        'record_time',
        'goods_weigh',
        //'real_weigh',
        //'weigh_express_money',
        'express_code',
        'express_no',
        //'send_order',
        //'pack_no',
        //'waves_record_id',
        'order_money',
        'goods_money',
        'express_money',
        'delivery_money',
        'paid_money',
        'payable_money',
        'buyer_remark',
        'seller_remark',
        'seller_flag',
        'order_remark',
        'store_remark',
        'buyer_name',
        'receiver_name',
        'receiver_country',
        'receiver_province',
        'receiver_city',
        'receiver_district',
        'receiver_street',
        'receiver_address',
        'receiver_addr',
        'receiver_zip_code',
        'receiver_mobile',
        'receiver_phone',
        'receiver_email',
        'invoice_type',
        'invoice_title',
        'invoice_content',
        'invoice_money',
        'invoice_status',
        'customer_code',
        'customer_address_id',
            //'is_weigh',
            //'is_edit_shipping_time',
            //'is_plan_send_time',
            //'is_hope_send_time',
            //'is_last_send_time',
            //'is_print_sellrecord',
            //'is_print_express',
            //'is_cancel',
            //'is_deliver',
            //'is_notice_time',
            //'is_stock_out',
    );
    private $detailFields = array(
        'sell_record_code',
        'deal_code',
        'goods_code',
        'spec1_code',
        'spec2_code',
        'sku',
        'barcode',
        'goods_price',
        'num',
        'goods_weigh',
        //'weigh_express_money',
        'avg_money',
        //'scan_num',
        'is_gift',
        'platform_spec',
            //'is_real_stock_out',
            //'is_stock_out_num',
            //'remark',
    );

    /**
     * Create new oms_waves_record.
     * @param $arr
     * @param int $isCheck
     * @return array
     */
    function create_waves($arr, $isCheck = 1) {
        if (empty($arr)) {
            return array('status' => '-1', 'message' => '没有选择订单');
        }
        //开启系统参数调用云栈四期接口获取物流单号
        $sys_params = load_model('sys/SysParamsModel')->get_val_by_code(array('opt_confirm_get_cainiao', 'is_more_deliver_package'));
        $this->begin_trans();
        try {
            $ids = implode("','", $arr);
            $sql = "select * from oms_sell_record where sell_record_code IN ('{$ids}') and waves_record_id=0 ";
            $recordList = $this->db->get_all($sql);

            $order_num = count($recordList);
            if (empty($recordList)) {
                throw new Exception('订单已生成波次单', '-1');
            }

            $sql = "select * from oms_sell_record_detail where sell_record_code IN ('{$ids}')";
            $detailList = $this->db->get_all($sql);
            if (empty($detailList)) {
                throw new Exception('订单明细不存在', '-1');
            }

            //生成波次：判断所选订单的仓库、配送方式、付款类型（货到付款和非货到付款）是否一致，不一致时，给出提示
            $storeCode = $recordList[0]['store_code'];
            $expressCode = $recordList[0]['express_code'];
            $payType = $recordList[0]['pay_type'];
            foreach ($recordList as $key => $value) {
                if ($value['order_status'] != '1') {
                    throw new Exception('订单非确认单据: ' . $value['sell_record_code'], '-1');
                }

                if ($value['shipping_status'] != '1') {
                    throw new Exception('订单非已通知配货状态: ' . $value['sell_record_code'], '-1');
                }
                if ($value['waves_record_id'] > 0) {
                    throw new Exception('订单已分配波次单: ' . $value['sell_record_code'], '-1');
                }
                if ($isCheck > 0) {
                    if ($storeCode != $value['store_code'] || $expressCode != $value['express_code']) {
                        throw new Exception('仓库或配送方式不统一，是否继续?', '-2');
                    }
                    if ($isCheck == 1) {
                        if ($payType != $value['pay_type']) {
                            throw new Exception('付款类型不一致, 是否继续?', '-2');
                        }
                    }
                }
            }
            $waves_sort = load_model('sys/SysParamsModel')->get_val_by_code('waves_create_sort_shelf');
            if ($waves_sort['waves_create_sort_shelf'] == 1) {
                $sql_sort = "SELECT DISTINCT rd.sell_record_code,sr.express_code,bs.shelf_code,rd.sku FROM oms_sell_record_detail rd
                            LEFT JOIN oms_sell_record sr ON rd.sell_record_code=sr.sell_record_code
                            LEFT JOIN goods_shelf bs ON rd.sku=bs.sku AND sr.store_code=bs.store_code
                            WHERE rd.sell_record_code IN ('{$ids}') ORDER BY sr.express_code,bs.shelf_code,rd.sku";
                $record_sort = $this->db->get_all($sql_sort);
                $sort_arr_str = array();
                $sort_sku_arr = array();
                foreach ($record_sort as $key => $val) {
                    $sort_express[$val['sell_record_code']] = $val['express_code'];
                    $sort_arr_str[$val['sell_record_code']][] = empty($val['shelf_code']) ? '' : $val['shelf_code'];
                    if (isset($sort_sku_arr[$val['sell_record_code']])) {
                        $new_arr_sku = array($sort_sku_arr[$val['sell_record_code']], $val['sku']);
                        sort($new_arr_sku);
                        $sort_sku_arr[$val['sell_record_code']] = $new_arr_sku[0];
                    } else {
                        $sort_sku_arr[$val['sell_record_code']] = $val['sku'];
                    }
                }
                foreach ($sort_arr_str as $key => &$value) {
                    $sort_arr[$key] = $value[0];
                }
                unset($record_sort);
                $total_amount = 0;
                $sort_sku = array();
                $sort_empty_num = 0;
                foreach ($recordList as $key => $value) {
                    $recordList[$key]['shelf_code_str'] = isset($sort_arr[$value['sell_record_code']]) ? $sort_arr[$value['sell_record_code']] : '';
                    $sort_empty_num += empty($recordList[$key]['shelf_code_str']) ? 1 : 0;
                    $sort[$key] = $recordList[$key]['shelf_code_str'];
                    $recordList[$key]['sku'] = isset($sort_sku_arr[$value['sell_record_code']]) ? $sort_sku_arr[$value['sell_record_code']] : '';
                    $recordList[$key]['express_sort'] = $sort_express[$value['sell_record_code']];
                    $sort_express_str[$key] = $recordList[$key]['express_sort'];
                    $sort_sku[$key] = $recordList[$key]['sku'];
                    $total_amount += $value['paid_money'];
                }

                if (count($sort) > $sort_empty_num) {
                    array_multisort($sort_express_str, SORT_STRING, SORT_ASC, $sort, SORT_STRING, SORT_ASC, $sort_sku, SORT_STRING, SORT_ASC, $recordList);
                } else {
                    array_multisort($sort_express_str, SORT_STRING, SORT_ASC, $sort_sku, SORT_STRING, SORT_ASC, $recordList);
                }
                unset($sort);
            } else {
                $total_amount = 0;
                foreach ($recordList as $key => $value) {
                    $total_amount += $value['paid_money'];
                }
            }


            $goods_count = 0;
            foreach ($detailList as $key => $value) {
                $goods_count += $value['num'];
            }
            if (count($recordList) == $goods_count) {
                $sell_num_type = 1;
            } else {
                $sell_num_type = 2;
            }
            //生成波次单
            //获取操作人
            $user_code = CTX()->get_session('user_code');
            $user_name = CTX()->get_session('user_name');
            $record_code = $this->new_code(); //缺少生成单号规则            
            $d = array(
                'record_code' => $record_code,
                'record_time' => date('Y-m-d H:i:s'),
                'store_code' => $recordList[0]['store_code'],
                'express_code' => $recordList[0]['express_code'],
                'sell_record_count' => count($recordList),
                'goods_count' => $goods_count,
                'cancelled_goods_count' => 0,
                'valide_goods_count' => $goods_count,
                'total_amount' => $total_amount,
                'sell_num_type' => $sell_num_type,
                'user_code' => $user_code,
                'user_name' => $user_name,
            );
            $r = $this->db->insert('oms_waves_record', $d);
            if (!$r) {
                throw new Exception('保存波次单失败', '-1');
            }
            $wavesRecordID = $this->db->insert_id();

            //保存存波次单的订单明细
            $sort_no = 0;
            foreach ($recordList as $sellRecord) {
                $d = array();
                $d['waves_record_id'] = $wavesRecordID;
                foreach ($this->recordFields as $field) {
                    $d[$field] = $sellRecord[$field];
                }
                $d['express_code'] = $sellRecord['express_code'];
                $d['express_no'] = $sellRecord['express_no'];
                if (!empty($sellRecord['express_no']) && !empty($sellRecord['express_data'])) {
                    $d['express_data'] = $sellRecord['express_data'];
                }
                //计划发货时间
                $d['is_plan_send_time'] = $sellRecord['plan_send_time'];
                $sort_no ++;
                $d['sort_no'] = $sort_no;
                //保存发货订单
                $r = $this->db->insert('oms_deliver_record', $d);
                if (!$r) {
                    throw new Exception('保存发货订单失败', '-1');
                }
                //新的发货订单ID
                $deliverRecordID = $this->db->insert_id();

                //保存发货订单明细
                $deliver_detail = array();
                foreach ($detailList as $detail) {
                    if ($detail['sell_record_code'] != $sellRecord['sell_record_code']) {
                        continue; //FIXME: 暂时方案
                    }

                    $dd = array();
                    $dd['deliver_record_id'] = $deliverRecordID;
                    $dd['waves_record_id'] = $wavesRecordID;
                    foreach ($this->detailFields as $field) {
                        $dd[$field] = $detail[$field];
                    }
                    $deliver_detail[] = $dd;
                    //保存明细
                    $r = $this->db->insert('oms_deliver_record_detail', $dd);
                    if (!$r) {
                        throw new Exception('保存发货订单明细失败', '-1');
                    }
                }

                //存在云栈热敏数据，并且开启多包裹，则生成一张包裹单
                if ($sys_params['opt_confirm_get_cainiao'] == 1 && !empty($sellRecord['express_no']) && !empty($sellRecord['express_data']) && $sys_params['is_more_deliver_package'] == 1) {
                    $package_no = 1;
                    $package = array(
                        'sell_record_code' => $sellRecord['sell_record_code'],
                        'express_code' => $sellRecord['express_code'],
                        'express_no' => $sellRecord['express_no'],
                        'express_data' => $sellRecord['express_data'],
                        'package_no' => $package_no,
                        'waves_record_id' => $wavesRecordID,
                        'goods_num' => $sellRecord['goods_num'],
                    );
                    $ret = $this->insert_exp('oms_deliver_record_package', $package);
                    if ($ret['status'] != 1) {
                        throw new Exception('保存包裹单失败', '-1');
                    }
                    $this->update_exp('oms_deliver_record', array('package_no' => $package_no), array('deliver_record_id' => $deliverRecordID));
                    $package_record_id = $ret['data'];
                    $package_detail = array();
                    foreach ($deliver_detail as $val) {
                        $package_detail[] = array(
                            'package_record_id' => $package_record_id,
                            'sell_record_code' => $sellRecord['sell_record_code'],
                            'package_no' => $package_no,
                            'sku' => $val['sku'],
                            'goods_num' => $val['num'],
                        );
                    }
                    $result = $this->insert_multi_exp('oms_deliver_package_detail', $package_detail, true);
                }
            }
            //更新订单状态: 拣货中
            $sql = "update oms_sell_record set shipping_status = 2, waves_record_id = $wavesRecordID where sell_record_code IN ('{$ids}') and waves_record_id=0";
            $r = $this->db->query($sql);
            if (!$r) {
                throw new Exception('更新订单状态失败', '-1');
            }
            $up_num = $this->affected_rows();
            if ($order_num != $up_num) {
                throw new Exception('生成波次单异常，存在已经生成波次单订单', '-1');
            }
            // $arr = explode(',', $ids);
            //删除通知配货数据
            load_model('oms/SellRecordNoticeModel')->delete_record_notice($arr);

            $this->commit();
            foreach ($arr as $sell_record_code) {
                load_model('oms/SellRecordActionModel')->add_action($sell_record_code, '生成波次单', '已生成波次单' . $record_code);
            }
            return array('status' => '1', 'data' => $wavesRecordID, 'message' => '已生成波次单' . $record_code . '，是否显示波次单详情', '');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => $e->getCode(), 'data' => '', 'message' => $e->getMessage());
        }
    }

    /**
     * （批量）取消波次单
     * pl 批量  one 单个
     */
    function opt_cancel_waves($filter) {
        if ($filter['do_type'] == 'pl') { //批量取消
            $waves_record_str = $this->arr_to_in_sql_value($filter['waves_record_id'], 'waves_record_id', $sql_values);
        } else { //单个
            $waves_record_id = $filter['waves_record_id'];
            $waves_record_str = ':waves_record_id';
            $sql_values[':waves_record_id'] = $waves_record_id;
            $sell_record = $filter['sell_record_code'];
            $sql2 = " select is_cancel,is_deliver from oms_deliver_record where sell_record_code = :sell_record_code and waves_record_id = :waves_record_id ";
            $deliver_record = $this->db->get_row($sql2, array(':sell_record_code' => $sell_record, ':waves_record_id' => $waves_record_id));
            if ($deliver_record['is_cancel'] != 0) {
                return array('status' => '-1', 'data' => '', 'message' => '订单已取消');
            }
            if ($deliver_record['is_deliver'] == 1) {
                return array('status' => '-1', 'data' => '', 'message' => '订单已发货');
            }
        }
        $sql = " select * from oms_waves_record where waves_record_id in ({$waves_record_str}) ";
        $waves_record = $this->db->get_all($sql, $sql_values);
        foreach ($waves_record as $value) {
            if ($value['is_accept'] == 1 || $value['is_cancel'] == 1) {
                return $this->format_ret(-1, '', $value['record_code'] . '已验收, 不能取消');
            }
        }
        $this->begin_trans();
        try {
            if ($filter['do_type'] == 'one') {//单个
                $ret2 = $this->update_exp('oms_deliver_record', array('is_cancel' => $waves_record_id), array('sell_record_code' => $sell_record, 'waves_record_id' => $waves_record_id));
                if ($ret2['status'] != 1) {
                    throw new Exception('更新订单状态失败', '-1');
                }
                //生成通知配发货数据
                $ret4 = load_model('oms/SellRecordNoticeModel')->create_record_notice($sell_record);
                //更新订单状态: 通知配发货
                $r = load_model('oms/SellRecordModel')->update(array('shipping_status' => '1'), array('sell_record_code' => $sell_record, 'shipping_status' => '2'));
                //日志记录
                load_model('oms/SellRecordModel')->add_action($sell_record, "取消", '取消波次单');
                $sql = "select is_cancel from oms_deliver_record where waves_record_id = :waves_record_id";
                $deliver_r = $this->db->get_all($sql, array(':waves_record_id' => $waves_record_id));
                $arr = array('is_cancel' => '0');
                if (!in_array($arr, $deliver_r)) {
                    $ret5 = $this->update(array('is_cancel' => 1, 'is_accept' => 0), array('waves_record_id' => $waves_record_id));
                    if ($ret5['status'] != 1) {
                        throw new Exception('更新订单状态失败', '-1');
                    }
                }
                if ($ret4['status'] != 1 || $r['status'] != 1) {
                    throw new Exception('更新订单状态失败', '-1');
                }
                $sql = "select express_no from oms_deliver_record where waves_record_id = :waves_record_id AND sell_record_code = :sell_record_code ";
                $express_no = $this->db->get_row($sql, array(':sell_record_code' => $sell_record, ':waves_record_id' => $waves_record_id));
                if (!empty($express_no['express_no'])) {
                    //取消云栈单号
                    $ret6 = load_model('oms/DeliverRecordModel')->cancel_express_no_all($sell_record, $waves_record_id);
                    $ret7 = M('oms_deliver_record')->update(array('express_no' => ''), array('waves_record_id' => $waves_record_id, 'sell_record_code' => $sell_record));
                    $ret8 = M('oms_sell_record_notice')->update(array('express_no' => ''), array('sell_record_code' => $sell_record));
                    $ret9 = M('oms_sell_record')->update(array('express_no' => ''), array('sell_record_code' => $sell_record));
                    if ($ret6['status'] != 1 || $ret7['status'] != 1 || $ret8['status'] != 1 || $ret9['status'] != 1) {
                        throw new Exception('取消快递单号失败', '-1');
                    }
                }
                $d = $this->count_waves_info($waves_record_id,$filter['sell_record_code']);
                $ret7 = M('oms_waves_record')->update($d, array('waves_record_id' => $waves_record_id));
                //更新波次单号
                $ret1 = load_model('oms/SellRecordModel')->update(array('waves_record_id' => '0'), array('sell_record_code' => $sell_record));
                if ($ret1['status'] == 1 && $ret7['status'] == 1) {
                    $this->commit();
                    return $this->format_ret(1, '', '取消波次单成功');
                } else {
                    throw new Exception('取消波次单失败', '-1');
                }
            } else { //批量取消波次单
                $sql2 = "select sell_record_code,order_status,waves_record_id from oms_sell_record where waves_record_id IN ({$waves_record_str})";
                //订单号
                $ret1 = $this->db->get_all($sql2, $sql_values);
                foreach ($ret1 as $key => $value) {
                    $sql_d = " select is_deliver from oms_deliver_record where sell_record_code = :sell_record_code and waves_record_id = :waves_record_id ";
                    $deliver_records = $this->db->get_row($sql_d, array(':sell_record_code' => $value['sell_record_code'], ':waves_record_id' => $value['waves_record_id']));
                    //订单已取消或者已完成 则不进行取消
                    if ($value['order_status'] == 3 || $value['order_status'] == 5 || $deliver_records['is_deliver'] == 1) {
                        continue;
                    }
                    //生成通知配发货数据
                    $ret4 = load_model('oms/SellRecordNoticeModel')->create_record_notice($value['sell_record_code']);
                    //更新订单状态: 通知配发货
                    $r = load_model('oms/SellRecordModel')->update(array('shipping_status' => '1'), array('sell_record_code' => $value['sell_record_code'], 'shipping_status' => '2'));
                    if ($ret4['status'] != 1 || $r['status'] != 1) {
                        throw new Exception('更新订单状态失败', '-1');
                    }
                    //取消波次
                    $waves_record_id = $this->db->get_row("select distinct waves_record_id from oms_sell_record where sell_record_code = :sell_record_code", array(':sell_record_code' => $value['sell_record_code']));
                    $ret2 = $this->db->update('oms_deliver_record', array('is_cancel' => $waves_record_id['waves_record_id']), array('sell_record_code' => $value['sell_record_code'], 'waves_record_id' => $waves_record_id['waves_record_id']));
                    if ($ret2) {
                        //日志记录
                        load_model('oms/SellRecordModel')->add_action($value['sell_record_code'], "取消", '取消波次单');
                    }
                    $sql = "select express_no from oms_deliver_record where waves_record_id = :waves_record_id AND sell_record_code = :sell_record_code ";
                    $express_no = $this->db->get_row($sql, array(':waves_record_id' => $waves_record_id['waves_record_id'], ':sell_record_code' => $value['sell_record_code']));
                    if (!empty($express_no['express_no'])) {
                        //取消云栈单号                        
                        $ret6 = load_model('oms/DeliverRecordModel')->cancel_express_no_all($value['sell_record_code'], $value['waves_record_id']);
                        $ret7 = M('oms_deliver_record')->update(array('express_no' => ''), array('waves_record_id' => $value['waves_record_id'], 'sell_record_code' => $value['sell_record_code']));
                        $ret8 = M('oms_sell_record_notice')->update(array('express_no' => ''), array('sell_record_code' => $value['sell_record_code']));
                        $ret9 = M('oms_sell_record')->update(array('express_no' => ''), array('sell_record_code' => $value['sell_record_code']));
                        if ($ret6['status'] != 1 || $ret7['status'] != 1 || $ret8['status'] != 1 || $ret9['status'] != 1) {
                            throw new Exception('取消快递单号失败', '-1');
                        }
                    }
                }
                foreach ($filter['waves_record_id'] as $waves_record_id) {
                    $d = $this->count_waves_info($waves_record_id);
                    //更新波次单信息                   
                    $ret6 = $this->update($d, array('waves_record_id' => $waves_record_id));
                }
                //更新波次单号
                $sql3 = "update oms_sell_record set waves_record_id = 0  where waves_record_id IN ({$waves_record_str})";
                //更新波次单状态
                $sql4 = "update oms_waves_record set is_cancel = 1  where waves_record_id IN ({$waves_record_str})";
                $ret4 = $this->db->query($sql3, $sql_values);
                $ret5 = $this->db->query($sql4, $sql_values);
                if (!$ret4 || !$ret2 || !$ret5) {
                    throw new Exception('更新订单状态失败', '-1');
                }
                if ($ret6['status'] == 1) {
                    $this->commit();
                    return array('status' => '1', 'message' => '取消波次单成功');
                } else {
                    throw new Exception('取消波次单失败', '-1');
                }
            }
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => $e->getCode(), 'data' => '', 'message' => $e->getMessage());
        }
    }

    //计算波次单中基本信息
    function count_waves_info($waves_record_id,$sell_record_code = '') {
        $data = $this->db->get_row("SELECT sell_record_count,goods_count,cancelled_sell_record_count,cancelled_goods_count,total_amount,valide_goods_count FROM oms_waves_record WHERE waves_record_id = :waves_record_id ", array(':waves_record_id' => $waves_record_id));
        $is_deliver_arr=$this->db->get_all("select * from oms_deliver_record where  waves_record_id =:waves_record_id and is_deliver=:is_deliver",array(':waves_record_id'=>$waves_record_id,":is_deliver"=>'1'));
        $sell_num_sql="select count(*) as record_num from oms_deliver_record where  waves_record_id =:waves_record_id";
        $sell_num_arr=$this->db->get_row($sell_num_sql,array('waves_record_id'=>$waves_record_id));
        if ($sell_record_code != '') {
                $sell_record_count=$data['sell_record_count'] - 1;
                $cancelled_sell_record_count = $data['cancelled_sell_record_count'] + 1;
                $sql3 = "SELECT goods_num,paid_money FROM oms_sell_record WHERE sell_record_code = :sell_record_code ";
                $ret = $this->db->get_row($sql3, array(':sell_record_code' => $sell_record_code));
                $cancelled_goods_count =  $ret['goods_num'];
                $valide_goods_count = $data['valide_goods_count'] - $cancelled_goods_count;
                $paid_money_total = $data['total_amount'] - $ret['paid_money'];
                $cancelled_goods_count_total = $data['cancelled_goods_count'] + $cancelled_goods_count ;
            }elseif (empty($is_deliver_arr)){
                $sell_record_total=$sell_num_arr['record_num'];
                $sell_record_count=0;
                $cancelled_sell_record_count =$sell_record_total;
                $valide_goods_count = 0;
                $paid_money_total = 0 ;
                $cancelled_goods_count_total = $data['goods_count'];
            }else{
                $valide_goods_count=0;
                $paid_money_total = 0 ;
                $goods_num=0;
                foreach($is_deliver_arr as $val){
                    if($val['is_deliver']=="1"){
                        $valide_goods_count+=$val['goods_num'];
                        $paid_money_total+=$val['paid_money'];
                        $goods_num+=$val['goods_num'];
                    }
                };
                $sell_record_count=count($is_deliver_arr);
                $cancelled_sell_record_count =$sell_num_arr['record_num']-$sell_record_count;
                $cancelled_goods_count_total = $data['goods_count']-$goods_num;

            }
            $d = array(
                'sell_record_count'=>$sell_record_count,
                'cancelled_sell_record_count' => $cancelled_sell_record_count,
                'valide_goods_count' => $valide_goods_count,
                'total_amount' => $paid_money_total,
                'cancelled_goods_count' => $cancelled_goods_count_total
            );
        return $d;
    }

    /**
     * create new sell_record code
     * @return string
     */
    function new_code() {
        $num = $this->db->get_seq_next_value('oms_waves_record_seq');
        $time = date('ymd', time());

        $num = sprintf('%06s', $num);
        $length = strlen($num);
        $num = substr($num, $length - 6, 6);
        $str = $time . $num;
        return $str;
    }

    /**
     * 波次打印验收扫描条码
     * @param int $id 波次单ID
     * @param string $barcode 条码
     * @return array 扫描结果
     */
    public function get_sku_by_sub_barcode($id, $barcode) {
        if (empty($barcode)) {
            return $this->format_ret(-1, '', '未扫描商品');
        }
        $sku_data = load_model('prm/SkuModel')->convert_scan_barcode($barcode, 0);
        if (empty($sku_data)) {
            return $this->format_ret(-1, '', '条码不存在');
        }

        $sql = "SELECT sku FROM oms_deliver_record_detail WHERE waves_record_id=:waves_record_id AND sku=:sku";
        $data = $this->db->get_all_col($sql, array(':waves_record_id' => $id, ':sku' => $sku_data['sku']));
        if (empty($data)) {
            return $this->format_ret(-1, '', $barcode . '：条码在明细中不存在');
        }
        return $this->format_ret(1, $data);
    }

    //取消波次单 $is_accept_check = 1 已验收的不能取消 =0允许已验收的取消
    function cancel_waves($waves_id, $sell_record_code, $is_accept_check, $sysuser) {
        $cancel_time = date('Y-m-d H:i:s', time());
        $cancel_user = $sysuser['user_name'];
        $sql = "update oms_waves_record set is_cancel = 1,cancel_time = :cancel_time,cancel_user = :cancel_user where waves_record_id = :id and is_cancel=0";
        if ($is_accept_check == 1) {
            $sql .= " and is_accept = 0";
        }
        $ret = $this->db->query($sql, array(':cancel_time' => $cancel_time, ':cancel_user' => $cancel_user, ':id' => $waves_id));
        if ($ret != true) {
            return $this->format_ret(-1, '', '取消波次单状态失败');
        }
        $aff_row = $this->db->affected_rows();
        if ($aff_row == 0) {
            return $this->format_ret(-100, '', '取消波次单状态失败,未刷新数据');
        }
        return $this->format_ret(1);
    }

    function cancel_waves_check($waves_record, $is_accept_check) {
        if ($waves_record['is_accept'] != '0' && $is_accept_check == 1) {
            return $this->format_ret(-1, '', '单据已验收, 不能取消');
        }
        if ($waves_record['is_cancel'] != '0') {
            return $this->format_ret(-10, '', '单据已取消');
        }
        return $this->format_ret(1);
    }

    //取消时，重新计算波次单的主单相关信息
    function js_waves_sell_record_info($waves_id, $cancel_sell_record_code) {
        $sql = "select sell_record_code,is_cancel,goods_num,paid_money,is_deliver from oms_deliver_record where waves_record_id = :waves_record_id";
        $db_record = ctx()->db->get_all($sql, array(':waves_record_id' => $waves_id));

        $sell_record_count = 0; //有效订单数
        $goods_count = 0; //总商品数
        $cancelled_goods_count = 0; //无效商品数
        $valide_goods_count = 0; //有效商品数
        $total_amount = 0;
        $wave_is_deliver = 0;
        $wave_deliver_num = 0;
        $sell_record_data = array();
        foreach ($db_record as $sub_record) {
            $goods_count += $sub_record['goods_num'];
            if ($sub_record['is_cancel'] == 0 && $sub_record['sell_record_code'] != $cancel_sell_record_code) {
                $valide_goods_count += $sub_record['goods_num'];
                $sell_record_count++;
                $total_amount += $sub_record['paid_money'];

                if ($sub_record['is_deliver'] == 1) {
                    $wave_deliver_num ++;
                }
            } else {
                $cancelled_goods_count += $sub_record['goods_num'];
            }
            $sell_record_data[$sub_record['sell_record_code']] = $sub_record;
        }

        if ($wave_deliver_num == $sell_record_count && $sell_record_count <> 0) {
            $wave_is_deliver = 1;
        } else if ($wave_deliver_num > 0 && $wave_deliver_num <> $sell_record_count) {
            $wave_is_deliver = 2;
        }
        $data = array(
            'sell_record_count' => $sell_record_count,
            'total_amount' => $total_amount,
            'valide_goods_count' => $valide_goods_count,
            'goods_count' => $goods_count,
            'cancelled_goods_count' => $cancelled_goods_count,
            'wave_is_deliver' => $wave_is_deliver,
            'sell_record_data' => $sell_record_data,
        );
        return $data;
    }

    //取消波次单中的订单 $is_accept_check = 1 已验收的波次单不能取消 =0 允许已验收波次单的取消 $is_log 是否记录订单的日志
    function cancel_waves_sell_record($waves_id, $cancel_sell_record_code, $cancel_reason = '', $is_accept_check = 1) {
        $ret = $this->get_row(array('waves_record_id' => $waves_id));
        if (empty($ret['data'])) {
            return $this->format_ret(-1, '', '找不到波次单,waves_record_id=' . $waves_id);
        }
        $waves_record = $ret['data'];
        $ret = $this->cancel_waves_check($waves_record, $is_accept_check);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $js_ret = $this->js_waves_sell_record_info($waves_id, $cancel_sell_record_code);
        $sell_record_data = $js_ret['sell_record_data'];

        if (!isset($sell_record_data[$cancel_sell_record_code])) {
            return $this->format_ret(-1, '', '波次单' . $waves_record['record_code'] . '找不到订单' . $cancel_sell_record_code);
        }
        if ($sell_record_data[$cancel_sell_record_code]['is_cancel'] == 1) {
            return $this->format_ret(-1, '', '订单' . $cancel_sell_record_code . '已取消');
        }
        //取消云栈单号
        load_model('oms/DeliverRecordModel')->cancel_express_no_all($cancel_sell_record_code, $waves_id);

        $sysuser = load_model("oms/SellRecordOptModel")->sys_user();
        $this->begin_trans();
        $cancel_time = date('Y-m-d H:i:s', time());
        $cancel_user = $sysuser['user_name'];
        $sql = "update oms_deliver_record set is_cancel = {$waves_id},express_no='' where is_cancel=0 and is_deliver = 0 and sell_record_code = :sell_record_code and waves_record_id = :id";
        $ret = $this->db->query($sql, array(':id' => $waves_id, ':sell_record_code' => $cancel_sell_record_code));
        if ($ret != true) {
            $this->rollback();
            return $this->format_ret(-1, '', '取消波次单中的订单状态失败');
        }
        $aff_row = $this->db->affected_rows();
        if ($aff_row == 0) {
            $this->rollback();
            return $this->format_ret(-100, '', '取消波次单中的订单状态失败,未刷新数据');
        }

        //更新订单为未确认状态,并写订单日志
//        $sql = "update oms_sell_record set order_status = 0,shipping_status = 0,waves_record_id = 0 where order_status<>3 and shipping_status<4 and sell_record_code = :sell_record_code";
//        $ret = ctx()->db->query($sql,array(':sell_record_code'=>$cancel_sell_record_code));
//        if ($ret!=true){
//	        return $this->format_ret(-1,'','取消波次单,更新订单为未确认状态失败');
//        }
//        $aff_row = ctx()->db->affected_rows();
//        if ($aff_row == 0){
//            ctx()->db->rollback();
//            return $this->format_ret(-1,'','取消波次单,更新订单为未确认状态失败,未刷新数据');
//        }

        load_model('oms/SellRecordActionModel')->add_action($cancel_sell_record_code, '取消波次单', $cancel_reason);
        $wave_status = $this->flush_waves_status($waves_id, $cancel_sell_record_code);
        $d = array(
            'sell_record_count' => $js_ret['sell_record_count'],
            'goods_count' => $js_ret['goods_count'],
            'cancelled_goods_count' => $js_ret['cancelled_goods_count'],
            'valide_goods_count' => $js_ret['valide_goods_count'],
            'total_amount' => $js_ret['total_amount'],
            'is_deliver' => $js_ret['wave_is_deliver'],
            'is_print_sellrecord' => $wave_status['is_print_sellrecord'],
            'is_print_express' => $wave_status['is_print_express']
        );

        $ret = M('oms_waves_record')->update($d, array('waves_record_id' => $waves_id));
        if ($ret != true) {
            $this->rollback();
            return $this->format_ret(-1, '', '更新波次单主单信息失败');
        }
        //如果订单全部取消掉了，那么波次单主单就要打上取消标识
        if ($js_ret['sell_record_count'] == 0) {
            $ret = $this->cancel_waves($waves_id, $cancel_sell_record_code, 0, $sysuser);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
        }
        $this->commit();
        return $this->format_ret(1);
    }

    function get_waves_send_sell_record($waves_record_code, $record_ids = '', $is_priv = true) {
        if ($is_priv) {
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/waves_record/waves_batch_send')) {
                exit_json_response(-401, '', '无权访问');
            }
        }
        if (!empty($record_ids) && $waves_record_code == '') {
            $sql = "select sell_record_code,deal_code,store_code from oms_deliver_record where deliver_record_id in({$record_ids}) and is_cancel=0 and is_deliver=0";
            $db_deliver = ctx()->db->get_all($sql);
        } else {
            $sql = "select waves_record_id from oms_waves_record where record_code = :record_code";
            $waves_record_id = ctx()->db->getOne($sql, array(':record_code' => $waves_record_code));
            if (empty($waves_record_id)) {
                return $this->format_ret(-1, '', '找不到波次单号:' . $waves_record_code);
            }
            $sql = "select sell_record_code,deal_code,store_code from oms_deliver_record where waves_record_id = :waves_record_id and is_cancel=0 and is_deliver=0";
            $db_deliver = ctx()->db->get_all($sql, array(':waves_record_id' => $waves_record_id));
        }

        if (empty($db_deliver)) {
            return $this->format_ret(-1, '', '波次单号:' . $waves_record_code . ' 有效单全部已发货');
        }
        $store_code = $db_deliver[0]['store_code'];
        $status = load_model('mid/MidBaseModel')->check_is_mid('scan', 'sell_record', $store_code);
        if ($status !== false) {
            return $this->format_ret(-1, '', '仓库对接' . $status . '，不允许手工发货');
        }

        $db_deliver_record = array();
        $db_deliver_tid = array();
        $new_db_deliver = array();
        foreach ($db_deliver as $key => $deliver) {
            $db_deliver_record[] = $deliver['sell_record_code'];
            $deliver_tid_list = explode(";", $deliver['deal_code']);
            foreach ($deliver_tid_list as $tid) {
                $db_deliver_tid[] = $tid;
            }
            $new_db_deliver[$key]['sell_record_code'] = $deliver['sell_record_code'];
            $new_db_deliver[$key]['deal_code'] = $deliver_tid_list;
        }
        $tid_str = implode("','", $db_deliver_tid);
        $sql = "select tid from api_refund where tid in ('{$tid_str}') and is_change = 0";
        $tids = ctx()->db->get_all($sql);
        if (!empty($tids)) {
            $tid_arr = array();
            foreach ($tids as $tid) {
                foreach ($new_db_deliver as $deliver) {
                    if (in_array($tid['tid'], $deliver['deal_code'])) {
                        $tid_arr[] = $deliver['sell_record_code'];
                    }
                }
            }

            return $this->format_ret(-1, '', '以下订单，买家已申请退款，建议不发货：' . implode(",", $tid_arr));
        }
        return $this->format_ret(1, join(',', $db_deliver_record));
    }

    function get_waves_back_sell_record($waves_record_code) {
        $sql = "select waves_record_id from oms_waves_record where record_code = :record_code";
        $waves_record_id = ctx()->db->getOne($sql, array(':record_code' => $waves_record_code));
        if (empty($waves_record_id)) {
            return $this->format_ret(-1, '', '找不到波次单号:' . $waves_record_code);
        }
        $sql = "select sell_record_code from oms_deliver_record where waves_record_id = :waves_record_id and  is_deliver=1";
        $db_deliver_record = ctx()->db->get_all_col($sql, array(':waves_record_id' => $waves_record_id));
        if (empty($db_deliver_record)) {
            return $this->format_ret(-1, '', '波次单号:' . $waves_record_code . ' 有效单全部未发货');
        }
        return $this->format_ret(1, join(',', $db_deliver_record));
    }

    function waves_send_sell_record($sell_record_code, $opt_user = '') {
        $sell_obj = load_model('oms/SellRecordOptModel');
        $record = $sell_obj->get_record_by_code($sell_record_code);
        $detail = $sell_obj->get_detail_list_by_code($sell_record_code);
        $sys_user = empty($opt_user) ? $sell_obj->sys_user() : $opt_user;
        $ret = $sell_obj->sell_record_send($record, $detail, $sys_user, 'waves_send', 1);
        if ($ret['status'] >= 1) {
            $ret['message'] = "发货成功";
        }
        return $ret;
    }

    function waves_batch_send_sell_record($sell_record_code) {
        $sell_obj = load_model('oms/SellRecordOptModel');
        $record = $sell_obj->get_record_by_code($sell_record_code);
        $detail = $sell_obj->get_detail_list_by_code($sell_record_code);
        $sys_user = $sell_obj->sys_user();
        $ret = $sell_obj->sell_record_send($record, $detail, $sys_user, 'waves_send', 1);
        if ($ret['status'] >= 1) {
            $ret['message'] = "发货成功";
        }
        return $ret;
    }

    //整单回写
    function waves_back_sell_record($sell_record_code) {
        $params1 = require_conf('sys/api_channel_params');
        $sell_obj = load_model('oms/SellRecordOptModel');
        $record = $sell_obj->get_record_by_code($sell_record_code);

        $sql = "select * from base_shop where shop_code = :shop_code";
        $data_shop = $this->db->get_row($sql, array('shop_code' => $record['shop_code']));
        $sd_id = $data_shop["shop_id"];
        //print_r($params1);
        if (isset($params1[$record['sale_channel_code']]['trade_shipping_sync'])) {
            //$apiName = 'chuchujie_api/trade_shipping_sync';
            $apiName = $params1[$record['sale_channel_code']]['trade_shipping_sync'];
            $params['sd_id'] = $sd_id;
            $params['tid'] = $record['deal_code_list'];
            $ret1 = load_model('sys/EfastApiModel')->request_api($apiName, $params);

            if ($ret1['resp_data']['code'] == '0') {
                $ret['status'] = '1';
                $ret['message'] = '回写成功';
            } else {
                $ret['status'] = '-1';
                $ret['message'] = '失败,' . $ret1['resp_data']['msg'];
            }

            return $ret;
        }
        $ret['status'] = '-1';
        $ret['message'] = "没回写";
        return $ret;
    }

    function do_print_log($id) {
        $sql = "select * from oms_deliver_record where waves_record_id = :waves_record_id";
        $data_order = $this->db->get_all($sql, array('waves_record_id' => $id));

        foreach ($data_order as $v) {
            load_model('oms/SellRecordModel')->add_action($v['sell_record_code'], '波次单打印');
        }
        $ret['status'] = '1';
        return $ret;
    }

    /**
     *
     * 方法名       print_data_default
     *
     * 功能描述     波次单模板打印
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-25
     */
    public function print_data_default($id) {
        $r = array();
        //获取波次单信息
        $r['record'] = $this->get_record_by_id($id);
        //获取波次单商品信息
        $r['detail'] = $this->get_deliver_detail_with_is_not_cancel($id);
        //数据处理
        $this->print_data_escape($r['record'], $r['detail']);

        $d = array('record' => array(), 'detail' => array());
        foreach ($r['record'] as $k => $v) {
            // 键值对调
            $nk = array_search($k, $this->print_fields_default['record']);
            $nk = $nk === false ? $k : $nk;
            $d['record'][$nk] = is_null($v) ? '' : $v; //$v; //
        }
        foreach ($r['detail'] as $k1 => $v1) {
            //键值对调
            foreach ($v1 as $k => $v) {
                $nk = array_search($k, $this->print_fields_default['detail'][0]);
                $nk = $nk === false ? $k : $nk;
                $d['detail'][$k1][$nk] = is_null($v) ? '' : $v; //$v; //
                $d['detail'][$k1]["蓝位号"] = $v1['sort_no'];
            }
        }
        //更新波次单打印标识
        $this->update_exp('oms_waves_record', array('is_print_waves' => 1), array('waves_record_id' => $id));
        //echo '<pre>';print_r($d);exit;
        return $d;
    }

    /**
     *
     * 方法名       print_data_escape
     *
     * 功能描述     波次单模板打印中数据格式化处理
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-25
     */
    public function print_data_escape(&$record, &$detail) {
        $record['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $record['store_code']));
        $record['print_time'] = date('Y-m-d H:i:s');
        $record['print_user_name'] = CTX()->get_session('user_name');
        $record['goods_num'] = 0;
        $picker = load_model('base/StoreStaffModel')->get_by_code($record['picker']);
        $record['picker_name'] = $picker['data']['staff_name'];
        //   $i = 1;

        foreach ($detail as $k => &$v) {
            //  $v['sort_num'] = $i;
            //$v1['lof_no'] = load_model('oms/DeliverRecordModel')->get_lof_no($v['sell_record_code'], $v['sku']);
            $sku_arr = load_model('prm/SkuModel')->get_spec_by_sku($v['sku']);
            //获取产品属性
            $goods_arr = oms_tb_all('base_goods', array('goods_code' => $sku_arr['goods_code']));

            //获取商品属性对应的属性名
            $v['category_name'] = oms_tb_val('base_category', 'category_name', array('category_code' => $goods_arr[0]['category_code']));
            $v['brand_name'] = oms_tb_val('base_brand', 'brand_name', array('brand_code' => $goods_arr[0]['brand_code']));
            $v['season_name'] = oms_tb_val('base_season', 'season_name', array('season_code' => $goods_arr[0]['season_code']));
            $v['year_name'] = oms_tb_val('base_year', 'year_name', array('year_code' => $goods_arr[0]['year_code']));
            $v['goods_prop'] = load_model('prm/GoodsModel')->prop[$goods_arr[0]['goods_prop']][1];
            $v['goods_days'] = $goods_arr[0]['goods_days'];
            $v['goods_desc'] = $goods_arr[0]['goods_desc'];
            $v['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $v['spec1_code']));
            $v['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $v['spec2_code']));
            $shelf_arr = $this->get_shelf_code_new($v['sku'], $record['store_code']);
            if (empty($shelf_arr)) {
                $v['shelf_code'] = '';
                $v['shelf_name'] = '';
            } else {
                list($v['shelf_code'], $v['shelf_name']) = $shelf_arr;
            }
            $record['goods_num'] += $v['num'];

            //获取扩展属性
            $prop_arr = load_model('prm/GoodsPropertyModel')->get_goods_prop_data($v['goods_code']);
            if (!empty($prop_arr)) {
                foreach ($prop_arr as $key => $value) {
                    $v[$key] = $value;
                }
            }
        }
        //排序
        $detail = array_orderby($detail, 'shelf_code', SORT_ASC, SORT_STRING, 'goods_code', SORT_ASC, SORT_STRING, 'spec1_code', SORT_ASC, SORT_STRING, 'spec2_code', SORT_ASC, SORT_STRING);
        $i = 1;
        foreach ($detail as $k => &$v) {
            $v['sort_num'] = $i;
            $i++;
        }
    }

    /**
     * 服装特性模板打印数据格式化
     */
    public function print_data_escape_clothing(&$record, &$detail){
        $record['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $record['store_code']));
        $record['print_time'] = date('Y-m-d H:i:s');
        $record['print_user_name'] = CTX()->get_session('user_name');
        $record['goods_num'] = 0;
        $picker = load_model('base/StoreStaffModel')->get_by_code($record['picker']);
        $record['picker_name'] = $picker['data']['staff_name'];
        //   $i = 1;
        foreach ($detail as $k => &$v) {
            //获取商品属性对应的属性名
            $v['category_name'] = oms_tb_val('base_category', 'category_name', array('category_code' => $v['category_code']));
            $shelf_arr = array();
            foreach (explode(',',$v['shelf_code']) as $val){
                $arr = $this->db->get_row('select shelf_name,shelf_code from base_shelf where shelf_code = :shelf_code and store_code = :store_code ',array(':shelf_code'=>$val,':store_code'=>$record['store_code']));
                if(!empty($arr)) $shelf_arr[] = $arr['shelf_name'].'('.$arr['shelf_code'].')';
            }
            if (empty($shelf_arr)) {
                $v['shelf_name'] = '';
            } else {
                $v['shelf_name'] = implode(',',array_unique($shelf_arr));
            }
        }
        //排序
        $detail = array_orderby($detail, 'shelf_code', SORT_ASC, SORT_STRING, 'goods_code', SORT_ASC, SORT_STRING, 'spec1_code', SORT_ASC, SORT_STRING, 'spec2_code', SORT_ASC, SORT_STRING);
    }


    /**
     *
     * 方法名       get_shelf_code
     *
     * 功能描述     读取库位代码和库位名称
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-25
     */
    public function get_shelf_code($sellRecordCode, $sku) {
        $sql = "select a.shelf_code from goods_shelf a
        inner join oms_sell_record_lof b on b.store_code = a.store_code and b.sku = a.sku
        where b.record_code = :record_code and b.record_type = '1' and b.sku = :sku";
        $l = $this->db->get_all($sql, array('record_code' => $sellRecordCode, 'sku' => $sku));

        $arr = array();
        $arr1 = array();
        foreach ($l as $_k => $_v) {
            $arr[] = $_v['shelf_code'];
            $arr1[] = oms_tb_val('base_shelf', 'shelf_name', array('shelf_code' => $_v['shelf_code']));
            ;
        }
        return array(implode(',', $arr), implode(',', $arr1));
    }

    /**
     *
     * 方法名                               api_wave_order_get
     *
     * 功能描述                           获取波次订单数据
     *
     * @author      BaiSon PHP R&D
     * @date        2016-02-26
     * @param       array $param
     *              array(
     *                  可选: 'page', 'page_size','start_time','end_time','record_code','is_accept','store_code'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_wave_order_get($param) {
        //可选字段
        $key_option = array(
            's' => array('start_time', 'end_time', 'record_code', 'store_code'),
            'i' => array('page', 'page_size', 'is_accept')
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        //合并数据
        $arr_deal = $arr_option;

        //检查单页数据条数是否超限
        if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }

        //清空无用数据
        unset($arr_option);
        unset($param);

        //开放字段
        $select = '`waves_record_id`,`record_code`';
        //查询SQL
        $sql_main = " FROM {$this->table} wr WHERE 1=1";
        //绑定数据
        $sql_values = array();
        foreach ($arr_deal as $key => $val) {
            if ($key != 'page' && $key != 'page_size') {
                if ($key == 'start_time') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND wr.record_time>=:{$key}";
                } else if ($key == 'end_time') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND wr.record_time<=:{$key}";
                } else {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND wr.{$key}=:{$key}";
                }
            }
        }
        if (!isset($arr_deal['start_time'])) {
            $start_time = date("Y-m-d H:i:s", strtotime("today"));
            $sql_main .= " AND wr.record_time >= :start_time";
            $sql_values[':start_time'] = $start_time;
        }
        if (!isset($arr_deal['end_time'])) {
            $end_time = date("Y-m-d H:i:s", strtotime("today +1 days"));
            $sql_main .= " AND wr.record_time < :end_time";
            $sql_values[':end_time'] = $end_time;
        }

        $waves_record_id = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select);
        $wave_list['filter'] = $waves_record_id['filter'];
        if (count($waves_record_id['data']) > 0) {
            foreach ($waves_record_id['data'] as $key => $wave) {
                $sql = "select sell_record_code,express_no,sort_no from oms_deliver_record where is_cancel=0 and is_deliver=0 and waves_record_id = :waves_record_id";
                $order = $this->db->get_all($sql, array("waves_record_id" => $wave['waves_record_id']));
                //检测是否为空
                if (empty($order)) {
                    $wave_list['data'][$wave['record_code']] = array();
                } else {
                    //将订单信息压入波次单数组中
                    $wave_list['data'][$wave['record_code']] = $order;
                    unset($order);
                }
            }
            //御城河日志
            load_model('common/TBlLogModel')->set_log_multi($wave_list, '开放接口获取波次订单数据', 'sendOrder');
            //返回数据给请求方
            return $this->format_ret(1, $wave_list);
        } else {
            //返回数据给请求方
            return $this->format_ret(-10002, '', API_RETURN_MESSAGE_10002);
        }
    }

    /**
     * 取消发货时刷新波次单状态
     */
    function flush_waves_status($waves_record_id, $record_code) {
        $sql = "SELECT is_print_sellrecord,is_print_express FROM oms_deliver_record WHERE waves_record_id=:waves_record_id AND sell_record_code <> :sell_record_code AND is_cancel=0";
        $sql_value = array(":waves_record_id" => $waves_record_id, ":sell_record_code" => $record_code);
        $deliver_data = $this->db->get_all($sql, $sql_value);
        foreach ($deliver_data as $value) {
            $sellrecord_sum += $value['is_print_sellrecord'];
            $express_sum += $value['is_print_express'];
        }
        if ($sellrecord_sum == 0) {
            $is_print_sellrecord = 0; //订单未打印
        } elseif ($sellrecord_sum < count($deliver_data)) {
            $is_print_sellrecord = 1; //部分打印
        } else {
            $is_print_sellrecord = 2; //已打印
        }
        if ($express_sum == 0) {
            $is_print_express = 0; //快递单未打印
        } elseif ($express_sum < count($deliver_data)) {
            $is_print_express = 1; //部分打印
        } else {
            $is_print_express = 2; //已打印
        }
        return array('is_print_express' => $is_print_express, 'is_print_sellrecord' => $is_print_sellrecord);
    }

    function print_oms_waves_goods($request) {
        $id = $request['record_ids'];

        $r = array();
        //获取波次单信息
        $r['record'] = $this->get_record_by_id($id);
        //获取波次单商品信息
        $sql = "select detail.sku,sum(detail.num) as num from oms_deliver_record_detail detail
    			inner join oms_deliver_record record on record.deliver_record_id = detail.deliver_record_id
    			where detail.waves_record_id = :id and record.is_cancel = 0 GROUP BY  detail.sku order by detail.sku";
        $data = $this->db->get_all($sql, array('id' => $id));
        $spec1_data = array();
        $spec2_data = array();

        $new_data = array();
        $goods_data = array();
        foreach ($data as $key => &$value) {

            $key_arr = array('barcode', 'goods_code', 'spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'goods_name', 'goods_short_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($value, $sku_info);

            // $value['shelf_code'] = oms_tb_val('goods_shelf', 'shelf_code', array('store_code' => $value['store_code'], 'sku' => $value['sku']));
            //   $value['lof_no'] = load_model('oms/DeliverRecordModel')->get_lof_no($value['sell_record_code'], $value['sku']);
            $spec1_data[$sku_info['spec1_code']] = $sku_info['spec1_name'];
            $spec2_data[$sku_info['spec2_code']] = $sku_info['spec2_name'];
            $goods_data[$sku_info['goods_code']] = $sku_info['goods_name'];

            // if(isset($new_data[$sku_info['goods_code']][$sku_info['spec1_code']][$sku_info['spec2_code']])){
            $new_data[$sku_info['goods_code']][$sku_info['spec1_code']][$sku_info['spec2_code']] = (int) $value['num'];
//            }else{
//                $new_data[$sku_info['goods_code']][$sku_info['spec1_code']][$sku_info['spec2_code']] +=(int)$value['num'];
//            }
        }
        array_flip($spec2_data);
        ksort($spec2_data);
        array_flip($spec2_data);
        $r['record']['print_name'] = CTX()->get_session('user_name', true);
        $r['record']['print_time'] = date('Y-m-d H:i:s');
        $r['goods_data'] = $goods_data;
        $r['spec1_data'] = $spec1_data;
        $r['spec2_data'] = $spec2_data;
        $r['print_data'] = $new_data;
        return $r;
    }

    /*     * 分配拣货员
     * @param $request
     * @return array
     */

    public function update_pick_member($request) {
        $sql_values = array();
        $sql_values[':picker'] = $request['pick_member_code'];
        $key = 'waves_record_id';
        $waves_record_id_arr = $request['waves_record_id_list'];
        //处理占位符
        $waves_record_id_str = $this->arr_to_in_sql_value($waves_record_id_arr, $key, $sql_values);
        $sql = "UPDATE oms_waves_record SET picker=:picker WHERE waves_record_id IN ({$waves_record_id_str})";
        $ret = $this->db->query($sql, $sql_values);
        if ($ret != true) {
            return $this->format_ret('-1', '', '分配失败');
        }
        return $this->format_ret('1', '', '分配成功');
    }

    /**
     * 校验批次打印服装特性尺码是否存在
     * @param $wave_record_ids
     * @param $type 1-打印错误数据，2-不打印错误数据
     * @return mixed
     */
    public function check_is_print_record($wave_record_ids){
        $wave_record_id_arr = explode(',',$wave_record_ids);
        $sql_values = array();
        $sql_str = $this->arr_to_in_sql_value($wave_record_id_arr,'wave_record_id',$sql_values);
        $sql = " select group_concat(r3.spec2_name) spec2_name,a.record_code,a.waves_record_id,odd.goods_code
          FROM {$this->table} a
          INNER JOIN oms_deliver_record b ON b.waves_record_id = a.waves_record_id
          INNER JOIN oms_deliver_record_detail  odd on b.deliver_record_id = odd.deliver_record_id
          left join base_goods g  ON odd.goods_code = g.goods_code
          left JOIN goods_sku r3 on r3.sku = odd.sku
          where a.waves_record_id in({$sql_str}) and b.is_cancel = 0 GROUP BY a.waves_record_id,odd.goods_code";
        $ret = $this->db->get_all($sql,$sql_values);
        $error_message = array();
        $error_id = array();
        foreach ($ret as &$val){
            $spec2_arr = array_unique(explode(',',$val['spec2_name']));
            $size_ret = load_model('wbm/NoticeRecordModel')->is_in_size($spec2_arr);
            if($size_ret['status'] < 1){
                $error_id[] = $val['waves_record_id'];
                $error_message[] = array('record_code'=>$val['record_code'],'message'=>'商品编码'.$val['goods_code'].':'.$size_ret['message']);
            }
        }
        $success_arr = array_diff($wave_record_id_arr,$error_id);
        if(!empty($error_message)){
            $arr = array('success'=>implode(',',$success_arr),'error'=>$error_message);
            return $this->format_ret(-2,$arr,implode('，',array_column($error_message,'message')));
        }else{
            return $this->format_ret(1);
        }

    }


    public function print_data_default_clothing($request) {
        $id = $request['record_ids'];
        $r = array();
        $r['record'] = $this->get_record_by_id($id);
        $r['detail'] = $this->get_deliver_detail_clothing($id);
        array_multisort($shelf_code_arr, SORT_ASC, $r['detail']);
        $this->print_data_escape_clothing($r['record'], $r['detail']);
        //更新波次单打印标识
        $this->update_exp('oms_waves_record', array('is_print_waves' => 1), array('waves_record_id' => $id));
        return $r;
    }
    public function get_deliver_detail_clothing($id){
        $record = $this->db->get_row("select * from oms_waves_record where waves_record_id = :id", array('id' => $id));
        if (empty($record)) {
            return array();
        }
        $sql = "select  odd.goods_code,sum(odd.num) num,sum(odd.scan_num) scan_num,sum(odd.avg_money) avg_money,g.category_code,g.goods_name,r3.spec1_name,r3.spec1_code,group_concat(r3.spec2_name,'<|>',odd.num) spec2_name,group_concat(r3.spec2_code) spec2_code,group_concat(r3.sku) sku
                from oms_deliver_record_detail odd
                INNER join oms_deliver_record record on record.deliver_record_id = odd.deliver_record_id
                left join base_goods g  ON odd.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = odd.sku
    			where odd.waves_record_id = :id and record.is_cancel = 0
    			GROUP By odd.goods_code,r3.spec1_name";
        $data = $this->db->get_all($sql, array('id' => $id));
        foreach ($data as $key => &$value) {
            $shelf_code_arr = array();
            foreach (explode(',',$value['sku']) as $val){
                $shelf_code = oms_tb_all('goods_shelf', array('store_code' => $record['store_code'], 'sku' => $val));
                $shelf_code_arr = array_merge($shelf_code_arr,array_unique(array_column($shelf_code,'shelf_code')));
            }
            $value['shelf_code'] = implode(',',array_unique($shelf_code_arr));
        }
        return $data;
    }
    public function get_error_message($data){
        $err_num = count($data);
        $message ='有'.$err_num.'张波次单无法按服装行业特性打印:';
        $file_name = $this->create_import_fail_files($data);
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "<a target=\"_blank\" href=\"{$url}\" >错误信息下载</a>";
        return $message;
    }
    function create_import_fail_files($msg) {
        $fail_top = array('波次单号', '错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg as $val_data) {
            $file_str .= "\t".implode("\t,\t", $val_data) . "\t\r\n";
        }
        $filename = md5("waves_clothing" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

}
