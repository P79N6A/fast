<?php

require_model('tb/TbModel');

class WeipinhuijitReturnModel extends TbModel {

    function get_table() {
        return 'api_weipinhuijit_return';
    }

    private $create_params = array();

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_main = "FROM {$this->table} r1 LEFT JOIN api_weipinhuijit_return_record r3 on r1.return_sn = r3.return_sn LEFT JOIN api_weipinhuijit_return_detail r2 on r1.return_sn = r2.return_sn WHERE 1";

        $sql_values = array();
        //商店权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('shop_code', $filter_shop_code);

        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND r1.shop_code in ({$str}) ";
        }
        //仓库
        if (isset($filter['warehouse']) && $filter['warehouse'] != '') {
            $warehouse = deal_strs_with_quote($filter['warehouse']);
            $sql_main .= " AND r1.warehouse in({$warehouse}) ";
        }
        //是否生成退货单
        if (isset($filter['is_execute']) && $filter['is_execute'] != '') {
            $sql_main .= " AND r1.is_execute = :is_execute ";
            $sql_values[':is_execute'] = $filter['is_execute'];
        }

        //退供单号
        if (isset($filter['return_sn']) && $filter['return_sn'] != '') {
            $sql_main .= " AND r1.return_sn = :return_sn ";
            $sql_values[':return_sn'] = $filter['return_sn'];
        }
        //批发退货单号
        if (isset($filter['return_record_no']) && $filter['return_record_no'] != '') {
            $sql_main .= " AND r3.return_record_no = :return_record_no ";
            $sql_values[':return_record_no'] = $filter['return_record_no'];
        }

        // 商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND (r2.barcode LIKE :barcode )";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        //商品名称
        if (isset($filter['product_name']) && $filter['product_name'] != '') {
            $sql_main .= " AND (r2.product_name LIKE :product_name )";
            $sql_values[':product_name'] = '%' . $filter['product_name'] . '%';
        }
        //2017-12-12 task#1936 查询条件下拉菜单中增加查询条件“箱号”
        if(isset($filter['box_no']) && $filter['box_no'] != ''){
            $return_sn = $this->get_return_sn_search($filter['box_no']);
            if (empty($return_sn)){
                $sql_main .= " and 1=2 ";
            } else {
                $sql_main .= " AND r1.return_sn = :return_sn ";
                $sql_values[':return_sn'] = $return_sn['return_sn'];
            }
        }
        $select = 'r1.*';
        if(!isset($filter['is_detail']) || $filter['is_detail'] == 0){
            $sql_main .= " group by return_sn ";
        }else{
            $select = 'r1.return_sn,r1.shop_code,r1.warehouse,r1.insert_time,r3.return_record_no,r2.po_no,r2.product_name,r2.barcode,r2.qty,r2.box_no ';
       }
        $sql_main .= ' order by r1.insert_time desc,r1.id desc';
        if($filter['is_detail'] == 1){
            $sql_main .= ',r2.id asc ';
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $warehouse_arr = $this->weipinhui_warehouse(0);
        foreach ($data['data'] as &$row) {
            $row['warehouse_name'] = $warehouse_arr[$row['warehouse']]['name'];
            //   $row['no_return_num'] = $row['num'] - $row['return_num'];
        }
        filter_fk_name($data['data'], array('shop_code|shop',));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }
    //根据箱号查询出退供单号
    public function get_return_sn_search($box_no) {
        $sql = "select return_sn from api_weipinhuijit_return_detail WHERE box_no = :box_no";
        $row = $this->db->get_row($sql, array(':box_no' => $box_no));
        return $row;
    }

    public function weipinhui_warehouse($check = 1) {
        $warehouse = load_model('api/WeipinhuijitWarehouseModel')->get_warehouse_select($check);
        $warehouse_arr = array();
        foreach ($warehouse as $val) {
            $warehouse_arr[$val['warehouse_code']] = array('name' => $val['warehouse_name'], 'val' => $val['warehouse_no']);
        }
        return $warehouse_arr;
    }

    //创建批发退货通知单、退货单
    function create($out_params) {
        $this->begin_trans();
        $this->create_params = $out_params;
        $ret = $this->convert_barcode_to_sku($out_params['return_id']);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //选择已有通知单
        if (!empty($out_params['return_notice_code'])) {
            $ret = $this->return_create_by_notice($out_params['return_id'], $out_params['return_notice_code'], $out_params);
        } else {
            //未生成通知单
            $ret = $this->return_create_by_unrelation_notice($out_params['return_id'], $out_params);
        }
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //回写退供单生成状态
        $ids = deal_strs_with_quote($out_params['return_id']);
        $sql = "UPDATE {$this->table} SET is_execute=1 WHERE id IN ({$ids})";
        $ret_return = $this->db->query($sql);
        if ($ret_return != TRUE) {
            $this->rollback();
            return $this->format_ret(-1, '', '生成状态更新失败');
        }
        $this->commit();
        return $ret;
    }

    //已绑定通知单生成退货单
    function return_create_by_notice($return_id, $return_notice_code, $out_params) {
        $ret = $this->add_return_notice_check($return_id, $return_notice_code);
        if ($ret['status'] != 1) {
            return $ret;
        }

        $check = $this->check_is_wms_store_code($out_params['store_code']);
        if ($check === TRUE) {
            $ret = $this->format_ret(1, '', '外接wms仓库仅能生成退货通知单');
        } else {
            $ret = $this->create_return_record($return_id, $return_notice_code, $out_params);
        }
        return $ret;
    }

    //未绑定通知单生成退货单
    function return_create_by_unrelation_notice($return_id, $out_params) {
        $ret = $this->create_return_notice_record($return_id, $out_params);
        if ($ret['status'] != '1') {
            return $ret;
        }
        $return_notice_code = $ret['data']['return_notice_code'];
        $check = $this->check_is_wms_store_code($out_params['store_code']);
        if ($check === TRUE) {
            $ret = $this->format_ret(1, '', '外接wms仓库仅能生成退货通知单');
        } else {
            $ret = $this->create_return_record($return_id, $return_notice_code, $out_params);
        }
        return $ret;
    }

    //生成批发退货单通知单
    function create_return_notice_record($return_ids, $out_params) {
        $return_ret = $this->get_by_ids($return_ids);
        $return_no_all = array();
        foreach ($return_ret['data'] as $val) {
            $return_no_all[] = $val['return_sn'];
        }
        $return_no_str = implode(",", $return_no_all);

        $ret = $this->add_return_notice_check($return_ids, '');
        if ($ret['status'] == -1) {
            return $ret;
        }
        //var_dump($ret);die;
        $return_notice_goods = $ret['data']['return_notice_goods'];

        //通知单主单信息
        $return_notice_record = array();
        $return_notice_record['custom_code'] = $out_params['distributor_code'];
        $return_notice_record['store_code'] = $out_params['store_code'];
        $return_notice_record['return_type_code'] = $out_params['return_type_code'];
        $return_notice_record['order_time'] = date('Y-m-d H:i:s', time());
        $return_notice_code = load_model('wbm/ReturnNoticeRecordModel')->create_fast_bill_sn();
        $return_notice_record['return_notice_code'] = $return_notice_code;
        $return_notice_record['remark'] = "由退供单{$return_no_str}自动生成";
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
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, '', '批发退货通知单生成失败' . $ret['message']);
        }
        //审核批发退货通知单
        $ret = load_model('wbm/ReturnNoticeRecordModel')->update_check(1, 'is_check', $return_notice_code);
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, '', '批发退货通知单审核失败' . $ret['message']);
        }
        if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "创建", 'module' => "wbm_return_notice_record", 'pid' => $ret['data']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $this->format_ret(1, array('return_notice_code' => $return_notice_code));
    }

    //通知单生成退货单
    function create_return_record($return_ids, $return_notice_code, $out_params) {
        $return_ret = $this->get_by_ids($return_ids);
        $return_no_all = array();
        foreach ($return_ret['data'] as $val) {
            $return_no_all[] = $val['return_sn'];
        }
        $return_no_str = implode(",", $return_no_all);

        //通知单
        $return_notice = load_model('wbm/ReturnNoticeRecordModel')->get_by_field('return_notice_code', $return_notice_code);
        $ret = load_model('wbm/ReturnNoticeRecordModel')->check_status($return_notice);
        if ($ret['status'] != 1) {
            //   load_model('wbm/ReturnNoticeRecordModel')->do_delete($return_notice_code);
            // load_model('wbm/ReturnNoticeDetailRecordModel')->do_delete($return_notice_code);
            return $ret;
        }
        $ret = load_model('wbm/ReturnRecordModel')->create_return_record($return_notice['data']);
        if ($ret['status'] != 1) {
            // load_model('wbm/ReturnNoticeRecordModel')->do_delete($return_notice_code);
            // load_model('wbm/ReturnNoticeDetailRecordModel')->do_delete($return_notice_code);
            return $ret;
        }
        $sql1 = "update wbm_return_notice_record set is_return = 1 where return_notice_code='{$return_notice_code}';";
        CTX()->db->query($sql1);
        $sql = "select record_code from wbm_return_record where return_record_id='{$ret['data']}';";
        $return_record_code = $this->db->get_value($sql);
        $return_record_data = array();
        foreach ($return_ret['data'] as $return_row) {
            //批发退单删除后再生成的退货单单号有可能与删掉的单据编号相同
            $sql = "delete from api_weipinhuijit_return_record where return_record_no=:return_record_no AND return_sn=:return_sn ";
            $sql_values = array(':return_record_no' => $return_record_code, ':return_sn' => $return_row['return_sn']);
            $this->db->query($sql, $sql_values);
            //更新退供单退货单表关联关系is_execute
            $return_record_data[] = array(
                'return_sn' => $return_row['return_sn'],
                'notice_record_no' => $return_notice_code,
                'return_record_no' => $return_record_code,
                'insert_time' => date('Y-m-d H:i:s')
            );
        }
        $this->insert_multi_exp('api_weipinhuijit_return_record', $return_record_data);
//         //退供单明细维护系统sku
//        $sql = "update api_weipinhuijit_return_detail pg,goods_sku gb set pg.sku=gb.sku where pg.barcode=gb.barcode and pg.return_sn  in ({$return_no_str}) ";
//        $this->db->query($sql);
//        $sql = "update api_weipinhuijit_return set return_notice_num=num,return_notice_code='{$return_notice_code}' where  return_sn in ({$return_no_str}) ";
//        $this->db->query($sql);
        return $ret;
    }

    function get_return_record_by_sn($return_sn) {
        $sql = "select b.*,sum(c.enotice_num) as enotice_num from api_weipinhuijit_return_record as a left join wbm_return_record as b on a.return_record_no=b.record_code left join wbm_return_record_detail as c on a.return_record_no=c.record_code  where a.return_sn='{$return_sn}'group by a.return_record_no;";
        $ret = $this->db->get_all($sql);
        foreach ($ret as &$value) {
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
        }
        return $ret;
    }

    function get_goods_by_page($filter) {

        $sql_main = "FROM api_weipinhuijit_return_detail  WHERE 1";

        $sql_values = array();

        //退供单号
        if (isset($filter['return_sn']) && $filter['return_sn'] != '') {
            $sql_main .= " AND return_sn = :return_sn ";
            $sql_values[':return_sn'] = $filter['return_sn'];
        }
        $sql_main .= ' order by id ';


        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        return $this->format_ret(1, $data);
    }

    //根据id查询
    function get_by_id($id) {
        return $this->get_row(array('id' => $id));
    }

    function get_record($return_sn) {
        $sql = "select return_record_no from api_weipinhuijit_return_record  "
                . " where return_sn='{$return_sn}'";
        $ret = $this->db->get_all($sql);
        return $ret;
    }

    //退供单关联的通知单
    function get_relation_notice($return_id) {
        $notice = array();
        $return_ret = $this->get_by_ids($return_id);
        foreach ($return_ret['data'] as $return_row) {
            if (!empty($return_row['return_notice_code'])) {
                //通知单是否终止
                $notice_ret = load_model('wbm/ReturnNoticeRecordModel')->get_by_field('return_notice_code', $return_row['return_notice_code']);
                if (!empty($notice_ret['data']) && $notice_ret['data']['is_return'] == 0) {
                    $notice[] = $return_row['return_notice_code'];
                }
            }
        }
        $notice = array_unique($notice);
        return $notice;
    }

    function get_by_ids($return_ids) {
        $return_ids_arr = explode(",", $return_ids);
        $return_ids_str = "'" . implode("','", $return_ids_arr) . "'";

        $sql = " SELECT  * FROM {$this->table}  WHERE 1  AND id in({$return_ids_str}); ";
        $data = $this->db->get_all($sql);
        return $this->format_ret(1, $data);
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

    function add_return_notice_check($return_ids, $return_notice_record_no = null) {
        $return_ret = $this->get_by_ids($return_ids);
        $return_no_all = array_column($return_ret['data'], 'return_sn');
        $return_no_str = implode(",", $return_no_all);

        //校验退供单明细
        $return_goods = $this->get_detail_by_return_sn($return_no_str);
        if (empty($return_goods)) {
            return $this->format_ret('-1', '', '退供单无明细');
        }
        $return_goods = $this->trans_arr_key($return_goods, 'barcode');
        //绑定了通知单
        if (!empty($return_notice_record_no)) {
            //通知单明细信息
            $notice_record_goods = load_model('wbm/ReturnNoticeDetailRecordModel')->get_all_details($return_notice_record_no);
            $notice_record_goods = $this->trans_arr_key($notice_record_goods['data'], 'sku');

            //校验退货单明细
            $no_exists_notice = array();
            foreach ($return_goods as $row) {
                //校验条码在通知单是否存在
                if (!isset($notice_record_goods[$row['sku']])) {
                    $no_exists_notice[] = array($row['barcode'] => '退供单商品条码' . $row['barcode'] . '在通知单' . $return_notice_record_no . '不存在');
                    continue;
                }
            }
            if (!empty($no_exists_notice)) {
                $msg = load_model('api/WeipinhuijitPickModel')->create_fail_file($no_exists_notice);
                return $this->format_ret(-1, '', '退供单商品条码在通知单' . $return_notice_record_no . '中不存在' . $msg);
            }
            return $this->format_ret(1, '');
        } else {
            $no_exists_gb = array();
            foreach ($return_goods as $barcode => $row) {
                //校验条码在系统是否存在
                if (empty($row['sku'])) {
                    $no_exists_gb[] = array($barcode => '退供单商品条码' . $barcode . '系统不存在');
                    continue;
                }
            }
            if (!empty($no_exists_gb)) {
                $msg = load_model('api/WeipinhuijitPickModel')->create_fail_file($no_exists_gb);
                return $this->format_ret(-1, '', '条码在系统中不存在' . $msg);
            }
            //获取价格
            $sql_values = array();
            $barcode_arr = array_column($return_goods, 'barcode');
            $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'sku', $sql_values);
            $po_no_arr = array_column($return_goods, 'po_no');
            $po_no_str = $this->arr_to_in_sql_value($po_no_arr, 'po_no', $sql_values);
            $sql = "SELECT barcode,actual_unit_price,actual_market_price FROM api_weipinhuijit_pick_goods WHERE po_no IN({$po_no_str}) AND barcode IN({$barcode_str}) GROUP BY barcode";
            $price_arr = $this->db->get_all($sql, $sql_values);
            $price_arr = $this->trans_arr_key($price_arr, 'barcode');
            $sql_values = array();
            $sku_arr = array_column($return_goods, 'sku');
            $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
            $sql = "SELECT sku,barcode,spec1_code,spec2_code,goods_code FROM goods_sku WHERE sku IN($sku_str) ";
            $sku_arr = $this->db->get_all($sql, $sql_values);
            $sku_arr = $this->trans_arr_key($sku_arr, 'sku');
            //组装通知单详情信息
            $return_notice_goods = array();
            foreach ($return_goods as $barcode => $row) {
                $sku = $row['sku'];
                $goods_row['goods_code'] = $sku_arr[$sku]['goods_code'];
                $goods_row['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $goods_row['goods_code']));

                $price = $price_arr[$barcode][$this->create_params['price_type']];
                $price = str_replace(',', '', $price); //处理千位 ，情况
                $goods_row['trade_price'] = $price;
                $goods_row['spec1_code'] = $sku_arr[$sku]['spec1_code'];
                $goods_row['spec2_code'] = $sku_arr[$sku]['spec2_code'];
                $goods_row['sku'] = $sku_arr[$sku]['sku'];
                $goods_row['barcode'] = $sku_arr[$sku]['barcode'];            
                //$goods_row['num'] = $row['qty'];
                $goods_row['rebate'] = 1;
                //$return_notice_goods[$sku] = $goods_row;
                if($row['sku']==$return_notice_goods[$sku]['sku']){
                    $return_notice_goods[$sku]['num'] +=$row['qty'];
                }else{
                    $goods_row['num'] = $row['qty'];
                    $return_notice_goods[$sku] = $goods_row;
                }
            }
            //var_dump($return_notice_goods);die;
            return $this->format_ret(1, array('return_notice_goods' => $return_notice_goods));
        }
    }

    function get_detail_by_return_sn($return_sn) {
        $return_no_arr = explode(",", $return_sn);
        $return_no_str = "'" . implode("','", $return_no_arr) . "'";
        $sql = "select SUM(qty) as qty,barcode,product_name,size,po_no,sku from api_weipinhuijit_return_detail where return_sn in({$return_no_str}) group by barcode;";
        $data = $this->db->get_all($sql);
        return $data;
    }

    function check_return_more($return_ids) {
        $return_ids_arr = explode(",", $return_ids);
        $return_ids_str = "'" . implode("','", $return_ids_arr) . "'";

        $sql = " SELECT is_execute,id,return_sn,warehouse FROM {$this->table}  WHERE 1 AND  id in({$return_ids_str}); ";
        $data = $this->db->get_all($sql);
        $warehouse = $data[0]['warehouse'];
        $ret = $this->format_ret(1, '');
        $sn = '';
        foreach ($data as $val) {
            if (1 == $val['is_execute']) {
                $sn.= $val['return_sn'] . ',';
            }
        }
        if (!empty($sn)) {
            $ret = $this->format_ret(-1, '', $sn . '已生成退货单不可再生成退货单!');
        } else {
            $ret = $this->format_ret(1, '', '');
        }
        return $ret;
    }

    //获取退货单
    function get_return() {
        $ret['status'] = '1';
        $ret['message'] = '成功';
        $shop_arr = load_model('base/ShopModel')->get_wepinhuijit_shop();
        foreach ($shop_arr as $shop_row) {
            $params = array('shop_code' => $shop_row['shop_code'], 'start_time' => date('Y-m-d H:i:s', strtotime('-3 days')), 'end_time' => date('Y-m-d H:i:s'));
            $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/sync_return', $params);
            if ($result['resp_data']['code'] == '0') {
                continue;
            } else {
                $ret['status'] = '-1';
                $ret['message'] = '店铺' . $shop_row['shop_name'] . '获取退货单失败,' . $result['resp_data']['msg'];
                return $ret;
            }
        }
        return $ret;
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

    /**
     * 转换条码/子条码为SKU
     * @param string $return_id 退供单ID，多个逗号隔开
     * @return array 处理结果
     */
    function convert_barcode_to_sku($return_id) {
        $return_arr = $this->get_by_ids($return_id);
        $return_sn_arr = array_column($return_arr['data'], 'return_sn');
        //退供单号
        $sql_values = array();
        $return_sn_str = $this->arr_to_in_sql_value($return_sn_arr, 'return_sn', $sql_values);
        $sql = "SELECT return_sn,po_no,box_no,barcode FROM api_weipinhuijit_return_detail WHERE return_sn IN({$return_sn_str})";
        $return_detail = $this->db->get_all($sql, $sql_values);
        //每一件商品的退供单详情
        if (empty($return_detail)) {
            return $this->format_ret(-1, '', '退货单明细空');
        }
        $barcode_arr = array_column($return_detail, 'barcode');//组成条形码数组
        $ret = load_model('prm/SkuModel')->convert_barcode($barcode_arr);
        if (empty($ret['data'])) {
            return $this->format_ret(-1, '', '识别条码失败');
        }
        $convert_barcode = $ret['data'];
        //更新sku
        foreach ($return_detail as &$value) {
            $barcode = strtolower($value['barcode']);//原始条码
            $value['sku'] = isset($convert_barcode[$barcode]['sku']) ? $convert_barcode[$barcode]['sku'] : '';
        }
        $update_str = "sku = VALUES(sku)";
        $ret = $this->insert_multi_duplicate('api_weipinhuijit_return_detail', $return_detail, $update_str);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '识别条码失败');
        }
        return $this->format_ret(1);
    }

    /**
     * 退供单下载
     * @param $request
     * @return mixed
     */
    function down_refund($request) {
        $params = array();
        $params['sale_channel_code'] = $request['sale_channel_code'];
        $params['shop_code'] = $request['shop_code'];
        $params['start_time'] = $request['start_time'];
        $params['end_time'] = $request['end_time'];
        $params['method'] = 'sync_return';
        $result = load_model('sys/EfastApiTaskModel')->request_api('sync', $params);
        return $result;
    }

}
