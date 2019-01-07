<?php

ini_set('memory_limit', '1000M'); //内存限制 
set_time_limit(0);
require_model('tb/TbModel');

class OrderCombineViewModel extends TbModel {

    //订单状态
    public $order_status = array(
        0 => '未确认',
        1 => '已确认',
        3 => '已作废',
        5 => '已完成',
    );
    //付款状态
    public $pay_status = array(
        0 => '未付款',
        2 => '已付款',
    );
    //发货状态
    public $shipping_status = array(
        0 => '未发货',
        1 => '已通知配货',
        2 => '拣货中',
        3 => '已完成拣货',
        4 => '已发货',
    );
    private $is_view = 0;
            
    function get_list_by_page($filter) {
        $sql_main_arr = array();
        $sql_values = array();
        $sqlone_main_arr = array();
        $sqlone_values = array();
        $is_detail = 0;
        $this->is_view = 1;



        $sql_main = "FROM oms_sell_record rl  ";
        if(isset($filter['goods_code'])&&!empty($filter['goods_code'])||isset($filter['goods_barcode'])&&!empty($filter['goods_barcode'])||isset($filter['deal_code_list'])&&!empty($filter['deal_code_list'])){
         $sql_main .= ", oms_sell_record_detail r2 WHERE rl.sell_record_code = r2.sell_record_code and is_replenish = 0 ";
        }else{
                $sql_main.=" where is_replenish = 0 ";
        }
    

        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);



        if (empty($filter['sell_record_code']) && empty($filter['deal_code_list'])) {
            if (!empty($filter['sale_channel_code'])) {
                $code_arr = explode(',', $filter['sale_channel_code']);
                $in_sql = CTX()->db->get_in_sql('sale_channel_code', $code_arr, $sql_values);
                $sql_main_arr[] = " AND rl.sale_channel_code IN (" . $in_sql . ") ";
            }


            if (!empty($filter['goods_code'])) {
                $sql_main_arr[] = " AND r2.goods_code LIKE :goods_code ";
                $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
            }

            if (!empty($filter['goods_barcode'])) {

//			    $sql_main_arr[] = " AND r2.barcode LIKE :goods_barcode ";
//			    $sql_values[':goods_barcode'] = '%'.$filter['goods_barcode'].'%';
                $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['goods_barcode']);
                if (empty($sku_arr)) {
                    $sql_main .= " AND 1=2 ";
                } else {
                    $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                    $sql_main .= " AND r2.sku in({$sku_str}) ";
                }
            }

            if (!empty($filter['buyer_remark'])) {
                $sql_main_arr[] = " AND rl.buyer_remark LIKE :buyer_remark ";
                $sql_values[':buyer_remark'] = '%' . $filter['buyer_remark'] . '%';
            }

            if (!empty($filter['seller_remark'])) {
                $sql_main_arr[] = " AND rl.seller_remark LIKE :seller_remark ";
                $sql_values[':seller_remark'] = '%' . $filter['seller_remark'] . '%';
            }

            if (!empty($filter['buyer_name'])) {

                $customer_code_arr = load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                if (!empty($customer_code_arr)) {

                    $customer_code_str = "'" . implode("','", $customer_code_arr) . "'";
                    $sql_main_arr[] = " AND ( rl.customer_code in ({$customer_code_str}) ) ";
                } else {
                    $sql_main_arr[] = " AND rl.buyer_name = :buyer_name ";
                    $sql_values[':buyer_name'] = $filter['buyer_name'];
                }
//                $sql_main_arr[] = " AND rl.buyer_name LIKE :buyer_name ";
//                $sql_values[':buyer_name'] = '%' . $filter['buyer_name'] . '%';
            }

            if (!empty($filter['receiver_name'])) {

                $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
                if (!empty($customer_address_id)) {
                    $customer_address_id_str = implode(",", $customer_address_id);
                    $sql_main_arr[] = " AND ( rl.receiver_name LIKE :receiver_name  OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                    $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
                } else {
                    $sql_main_arr[] = " AND rl.receiver_name LIKE :receiver_name ";
                    $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
                }
//
//                $sql_main_arr[] = " AND rl.receiver_name LIKE :receiver_name ";
//                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            }

            if (!empty($filter['receiver_mobile'])) {
                $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_mobile'], 'tel');
                if (!empty($customer_address_id)) {
                    $customer_address_id_str = implode(",", $customer_address_id);
                    $sql_main_arr[] = " AND ( rl.receiver_mobile = :receiver_mobile OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                    $sql_values[':receiver_mobile'] = $filter['receiver_mobile'];
                } else {
                    $sql_main_arr[] = " AND rl.receiver_mobile = :receiver_mobile ";
                    $sql_values[':receiver_mobile'] = $filter['receiver_mobile'];
                }


//                $sql_main_arr[] = " AND rl.receiver_mobile LIKE :receiver_mobile ";
//                $sql_values[':receiver_mobile'] = '%' . $filter['receiver_mobile'] . '%';
            }

            //发票
            if (isset($filter['invoice_status']) && $filter['invoice_status'] !== '') {
                if ($filter['invoice_status'] == '1') {
                    $sql_main_arr[] = " AND rl.invoice_status <> 0";
                } elseif ($filter['invoice_status'] == '-1') {
                    $sql_main_arr[] = " AND rl.invoice_status = 0";
                }
            }
            //国家
            if (isset($filter['country']) && $filter['country'] !== '') {
                $sql_main .= " AND rl.receiver_country = :country ";
                $sql_values[':country'] = $filter['country'];
            }
            //省
            if (isset($filter['province']) && $filter['province'] !== '') {
                $sql_main .= " AND rl.receiver_province = :province ";
                $sql_values[':province'] = $filter['province'];
            }
            //城市
            if (isset($filter['city']) && $filter['city'] !== '') {
                $sql_main .= " AND rl.receiver_city = :city ";
                $sql_values[':city'] = $filter['city'];
            }
            //订单标签
            if (isset($filter['order_tag']) && $filter['order_tag'] !== '') {
                if($filter['sell_record_code'] == ''){
                    $tag_arr = explode(',', $filter['order_tag']);
                    $_tag_str = "'" . implode("','", $tag_arr) . "'";
                    if (in_array('none', $tag_arr)) {
                        if (count($tag_arr) > 1) {
                            $sql_tag = "SELECT rn.sell_record_code,rt.tag_v FROM    oms_sell_record rn LEFT JOIN oms_sell_record_tag rt ON rn.sell_record_code = rt.sell_record_code AND rt.tag_type = 'order_tag' having tag_v  in({$_tag_str}) or tag_v is null";
                        } else {
                            $sql_tag = "SELECT rn.sell_record_code,rt.tag_v FROM    oms_sell_record rn LEFT JOIN oms_sell_record_tag rt ON rn.sell_record_code = rt.sell_record_code AND rt.tag_type = 'order_tag' having  tag_v is null";
                        }
                        $tag_record_data = $this->db->get_all($sql_tag);
                        if (!empty($tag_record_data)) {
                            $tag_record = array_column($tag_record_data, 'sell_record_code');
                            $tag_record_str = $this->arr_to_in_sql_value($tag_record, 'sell_record_code', $sql_values);
                            $sql_main .= " AND rl.sell_record_code  in ({$tag_record_str}) ";
                        } else {
                            $sql_main .= "AND 1=2";
                        }
                    } else {
                        $tag_record = load_model('oms/SellRecordTagModel')->get_sell_record_by_tag($tag_arr);
                        if (!empty($tag_record)) {
                            $tag_record_str = $this->arr_to_in_sql_value($tag_record, 'sell_record_code', $sql_values);
                            $sql_main .= " AND rl.sell_record_code in ({$tag_record_str}) ";
                        } else {
                            $sql_main .= " AND 1=2 ";
                        }
                    }
                }
            }

            //地区
            if (isset($filter['district']) && $filter['district'] !== '') {
                $sql_main .= " AND rl.receiver_district = :district ";
                $sql_values[':district'] = $filter['district'];
            }
            if (!empty($filter['express_code'])) {
                $code_arr = explode(',', $filter['express_code']);
                $in_sql = CTX()->db->get_in_sql('express_code', $code_arr, $sql_values);
                $sql_main_arr[] = " AND rl.express_code IN (" . $in_sql . ") ";
            }

            if (!empty($filter['store_code'])) {
                $code_arr = explode(',', $filter['store_code']);
                $in_sql = CTX()->db->get_in_sql('store_code', $code_arr, $sql_values);
                $sql_main_arr[] = " AND rl.store_code IN (" . $in_sql . ") ";
            }

            if (!empty($filter['receiver_address'])) {
                $sql_main_arr[] = " AND rl.receiver_address LIKE :receiver_address ";
                $sql_values[':receiver_address'] = '%' . $filter['receiver_address'] . '%';
            }

            if (!empty($filter['record_time_start'])) {
                $t_start = $filter['record_time_start'] . ' 00:00:00';
                $sql_main_arr[] = " AND rl.record_time >= :record_time_start ";
                $sql_values[':record_time_start'] = $t_start;
            }
            if (!empty($filter['record_time_end'])) {
                $t_end = $filter['record_time_end'] . ' 23:59:59';
                $sql_main_arr[] = " AND rl.record_time <= :record_time_end ";
                $sql_values[':record_time_end'] = $t_end;
            }

            if (!empty($filter['pay_time_start'])) {
                $t_start = $filter['pay_time_start'] . ' 00:00:00';
                $sql_main_arr[] = " AND rl.pay_time >= :pay_time_start ";
                $sql_values[':pay_time_start'] = $t_start;
            }
            if (!empty($filter['pay_time_end'])) {
                $t_end = $filter['pay_time_end'] . ' 23:59:59';
                $sql_main_arr[] = " AND rl.pay_time <= :pay_time_end ";
                $sql_values[':pay_time_end'] = $t_end;
            }
        } else {
            if (!empty($filter['sell_record_code'])) {
                $sell_record_code_arr = explode(',', $filter['sell_record_code']);
                $sell_str = $this->arr_to_in_sql_value($sell_record_code_arr, 'sell_record_code', $sql_values);
                $sql_main_arr[] = " AND rl.sell_record_code in ({$sell_str}) ";
            } else if (!empty($filter['deal_code_list'])) {
                $deal_code_arr = explode(',', $filter['deal_code_list']);
                $sell_str = $this->arr_to_in_sql_value($deal_code_arr, 'deal_code_list', $sql_values);
                $sql_main_arr[] = " AND r2.deal_code in ({$sell_str}) ";
            }
        }
//       $cfg_data = load_model('oms/OrderCombineStrategyModel')->get_val_by_code('order_combine_is_houtai');
//       if($cfg_data['order_combine_is_houtai']['rule_status_value'] == 1){
//           $sql_main_arr[] = " AND rl.sale_channel_code ='houtai' ";
//       }else{
//           $sql_main_arr[] = " AND rl.sale_channel_code <>'houtai' ";
//       }
        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');




        $select = 'rl.invoice_status,rl.is_problem,rl.is_copy,rl.is_combine_new,rl.must_occupy_inv,rl.lock_inv_status,rl.is_change_record,rl.is_split_new,rl.is_rush,rl.is_pending,rl.is_handwork,rl.sale_mode,rl.is_fenxiao,rl.pay_type,rl.is_lock, rl.sell_record_id,rl.sale_channel_code,rl.sell_record_code,rl.deal_code_list,rl.store_code,rl.shop_code,rl.pay_code,rl.buyer_name,rl.receiver_name,rl.receiver_province,rl.receiver_city,rl.receiver_district,rl.receiver_address,rl.receiver_addr,rl.seller_remark,rl.buyer_remark,rl.paid_money,rl.receiver_mobile,rl.deal_code,rl.pay_time,rl.record_time,rl.express_code,rl.express_no,rl.express_money,rl.goods_num,rl.order_remark,rl.confirm_person,rl.order_status,rl.shipping_status,rl.pay_status,rl.payable_money,rl.fenxiao_code,rl.customer_address_id';

        $sql_main .= join(' ', $sql_main_arr);
        $p_sql_values = $sql_values;
        $wh = load_model('oms/OrderCombineClModel')->get_where_str('rl.', 'byhand');
        $sql_main .= $wh;
        //echo '<hr/>$sql_main<xmp>'.$sql_main.'</xmp>';
        //echo '<hr/>$p_sql_values<xmp>'.var_export($p_sql_values,true).'</xmp>';

       if (empty($filter['sell_record_code']) && empty($filter['deal_code_list'])) {
            $customer_arr = $this->get_customer_code_all($sql_main, $p_sql_values);
            if (empty($customer_arr)) {
                $filter['record_count'] = 0;
                $result = array('filter' => $filter, 'data' => array());
                return $this->format_ret(1, $result);
            } else {
                $customer_str = "'" . implode("','", $customer_arr) . "'";
                $sql_main .= " AND rl.customer_code in ({$customer_str}) ";
            }
       }
        
        
        $sql = "select rl.sell_record_id " . $sql_main;

        $ids_arr = ctx()->db->get_all_col($sql, $p_sql_values);
        //echo '<hr/>$ids_arr<xmp>'.var_export($ids_arr,true).'</xmp>';die;
        if (empty($ids_arr)) {
            $filter['record_count'] = 0;
            $result = array('filter' => $filter, 'data' => array());
            return $this->format_ret(1, $result);
        }
       $ids_arr = array_unique($ids_arr);
        //获取存在已上传WMS的订单的id
//        $wms_store = load_model('sys/WmsConfigModel')->get_wms_store_all();
//        $wms_store_str = deal_array_with_quote($wms_store);
//        $ids_str = deal_array_with_quote($ids_arr);
//        $fileter_sql = "SELECT sell_record_id FROM oms_sell_record WHERE store_code IN({$wms_store_str}) AND sell_record_id IN ({$ids_str}) AND shipping_status IN (1,2,3)";
//        $filter_ids_arr = ctx()->db->get_all_col($fileter_sql);
//        foreach ($ids_arr as $key => $id){
//            if(in_array($id, $filter_ids_arr)){
//                unset($ids_arr[$key]);
//            }
//        }
//        if (empty($ids_arr)) {
//            $filter['record_count'] = 0;
//            $result = array('filter' => $filter, 'data' => array());
//            return $this->format_ret(1, $result, '订单已全部上传至WMS');
//        }


        $ids = join(',', $ids_arr);
        $sql = "select {$select} from oms_sell_record rl where rl.sell_record_id in($ids)";
        $data = ctx()->db->get_all($sql);
        filter_fk_name($data, array('express_code|express'));
//        $find_kv = array();
//
//        if (!empty($filter['sell_record_code'])) {
//            $find_kv = array('sell_record_code', $filter['sell_record_code']);
//        }
//        if (!empty($filter['deal_code_list']) && empty($find_kv)) {
//            $find_kv = array('deal_code_list', $filter['deal_code_list']);
//        }
        //echo '<hr/>$filter<xmp>'.var_export($filter,true).'</xmp>';
        $ret = $this->get_combine_show_data($data, (int) $filter['page'], $filter['page_size']);

        //echo '<hr/>$data<xmp>'.var_export($data,true).'</xmp>';
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';die;

        $data = $ret['data'];
        $page_num = $ret['page_num'];

        $tbl_cfg = array(
            'base_sale_channel' => array('fld' => 'sale_channel_code,sale_channel_name', 'relation_fld' => 'sale_channel_code+sale_channel_code'),
            'base_shop' => array('fld' => 'shop_name', 'relation_fld' => 'shop_code+shop_code'),
            'base_store' => array('fld' => 'store_name', 'relation_fld' => 'store_code+store_code')
        );
        require_model('util/GetDataBySqlRelModel');
        $obj = new GetDataBySqlRelModel();
        $obj->tbl_cfg = $tbl_cfg;

        $data = $obj->get_data_by_cfg(null, $data);
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        //开启安全控制才遍历数组
        if ($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
            foreach ($data as &$value) {
                $value['receiver_name'] = $this->name_hidden($value['receiver_name']);
                $value['receiver_mobile'] = $this->phone_hidden($value['receiver_mobile']);
                $value['receiver_address'] = $this->address_hidden($value['receiver_address']);
            }
        }

        if ($filter['ctl_type'] == 'export') {
            foreach ($data as &$value) {
                $status = $this->order_status[$value['order_status']];
                $status .= ' ' . $this->shipping_status[$value['shipping_status']];
                $status .= ' ' . $this->pay_status[$value['pay_status']];
                $value['status'] = $status;
            }
        }
        // var_dump($data['sell_record_code']);die;
        // $a = oms_tb_val("oms_sell_record_tag", 'tag_desc', array('sell_record_code' => $data['sell_record_code']));
        // var_dump($a);die;
        //echo '<hr/>$data<xmp>'.var_export($data,true).'</xmp>';die;
        $filter['record_count'] = $page_num;
        //订单图标
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        foreach ($data as &$value) {
            $value['status_text'] = load_model('oms/SellRecordModel')->get_sell_record_tag_img($value, $sys_user);
            $value['tag_desc'] = load_model('oms/SellRecordModel')->get_sell_record_tag_desc($value, $sys_user);
        }
        $result = array('filter' => $filter, 'data' => $data);
        load_model('common/TBlLogModel')->set_log_multi($data, 'search');
        //print_r($result);
        return $this->format_ret(1, $result);
    }

    function get_combine_show_data($data, $page_no, $page_size, $find_kv = array()) {
        $ret = $this->get_combine_data($data);
        $t_result = $ret['data'];
        $page_num = $ret['page_num'];
        $result = array();
        if (!empty($find_kv)) {
            foreach ($t_result as $sub_result) {
                foreach ($sub_result as $sr) {
                    if ($sr[$find_kv[0]] == $find_kv[1]) {
                        $result[] = $sr;
                    }
                }
            }
            $page_num = count($result);
            return array('data' => $result, 'page_num' => $page_num);
        }

        $start_pos = ($page_no - 1) * $page_size;
        $end_pos = $page_no * $page_size;
        /*
          echo '<hr/>$result<xmp>'.var_export($t_result,true).'</xmp>';
          echo '<hr/>$start_pos<xmp>'.var_export($start_pos,true).'</xmp>';
          echo '<hr/>$end_pos<xmp>'.var_export($end_pos,true).'</xmp>';
          echo '<hr/>$page_size<xmp>'.var_export($page_size,true).'</xmp>';
         */
        $idx = 0;
        $result = array();
        foreach ($t_result as $sub_t) {
            foreach ($sub_t as $st) {
                if ($idx < $start_pos) {
                    $idx++;
                    continue;
                }
                if ($idx >= $end_pos) {
                    break;
                }
                $result[] = $st;
                $idx++;
            }
        }
        return array('data' => $result, 'page_num' => $page_num);
    }

    function get_combine_data($data, $only_sell_record_code = 0) {
        //echo '<hr/>$data<xmp>'.var_export($data,true).'</xmp>';die;
        $match_key = explode(',', 'shop_code,store_code,pay_code,buyer_name,receiver_name,receiver_city,receiver_district,receiver_addr,customer_address_id,is_fenxiao,fenxiao_code');
        $match_key_new = explode(',', 'shop_code,store_code,pay_code,customer_address_id,is_fenxiao,fenxiao_code');

        $t_result = array();
        foreach ($data as $sub_data) {
            $_row = array();
            if (empty($sub_data)) {
                foreach ($match_key as $k) {
                    $_row[] = trim($sub_data[$k]);
                }
            } else {
                foreach ($match_key_new as $k) {
                    $_row[] = trim($sub_data[$k]);
                }
            }
            
            $_v = md5(join(' ', $_row));
            if ($only_sell_record_code == 1) {
                if (isset($t_result[$_v]) && $sub_data['invoice_type'] == 'vat_invoice') { //合并订单规则加上发票类型(增值税发票信息是否相同)
                    $sell_arr = array(0 => $t_result[$_v][0], 1 => $sub_data['sell_record_code']);
                    $sql_values = array();
                    $sell_str = $this->arr_to_in_sql_value($sell_arr, 'sell_record_code', $sql_values);
                    $sql = "SELECT company_name,taxpayers_code,registered_country,registered_province,registered_city,registered_district,registered_street,registered_addr,phone,bank,bank_account FROM oms_sell_invoice WHERE sell_record_code IN ({$sell_str}) ";
                    $invoice_data = $this->db->get_all($sql, $sql_values);
                    if (strcasecmp(implode(' ', $invoice_data[0]), implode(' ', $invoice_data[1])) == 0) {
                        $t_result[$_v][] = $sub_data['sell_record_code'];
                    }
                } else {
                    $t_result[$_v][] = $sub_data['sell_record_code'];
                }
            } else {
                $t_result[$_v][] = $sub_data;
            }
        }
        $result = array();
        $page_num = 0;
        foreach ($t_result as $k => $sub_t) {
            $t_count = count($sub_t);

            if ($t_count < 2 && $this->is_view == 0) {
                continue;
            }

            $result[$k] = $sub_t;
            $page_num += $t_count;
        }
        //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';
        return array('data' => $result, 'page_num' => $page_num);
    }

    function batch_combine_by_record_code($sell_record_code_list, $type) {
        $sell_record_code_arr = explode(',', $sell_record_code_list);
        $sell_record_code_list = "'" . join("','", $sell_record_code_arr) . "'";
        $sql = "select sell_record_code,shop_code,store_code,pay_code,buyer_name,receiver_name,receiver_address,is_fenxiao,fenxiao_code,customer_address_id from oms_sell_record where sell_record_code in($sell_record_code_list)";
        $db_arr = ctx()->db->get_all($sql);
        $data = $this->get_combine_data($db_arr, 1);
        if (empty($data['data'])) {
            return $this->format_ret(-1, '', '没有可合并的订单');
        }
        require_model('oms/OrderCombineModel');
        $mdl = new OrderCombineModel();
        $log = array();
        foreach ($data['data'] as $sub_data) {
            $ret = $mdl->combine_order($sub_data, '', $type);
            $sell_record_code_list = join(',', $sub_data);
            if ($ret['status'] < 1) {
                $log[] = $sell_record_code_list . $ret['message'];
            } else {
                $log[] = $sell_record_code_list . '合并成功,新生成的单号为 ' . $ret['data'];
            }
        }
        $log = join('<br/>', $log);
        return $this->format_ret(1, $log, $log);
    }

    private function get_customer_code_all($sql_main, $sql_values) {

        $sql = " SELECT DISTINCT rl.customer_code,count(1)  as num {$sql_main}
            GROUP BY customer_code HAVING num>1";
        $data = $this->db->get_all($sql, $sql_values);
        $customer_code_arr = array_column($data, 'customer_code');
        return $customer_code_arr;
    }

}
