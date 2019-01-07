<?php

/**
 * 短信模板 相关业务
 *
 * @author dfr
 */
require_model('tb/TbModel');

class SapSellRecordModel extends TbModel {

    private $implement_num = 1;
    private $order_type = array(
        0 => '销售订单',
        1 => '销售退单',
        2 => '月度积分'
    );

    function get_table() {
        return 'sap_sell_record';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sap_config = $this->get_sap_config();
        $efast_shop_code_arr = explode(",", $sap_config['efast_shop_code']);
        $shop_code_str = "'" . implode("','", $efast_shop_code_arr) . "'";

        $efast_store_code_arr = explode(",", $sap_config['efast_store_code']);
        $store_code_str = "'" . implode("','", $efast_store_code_arr) . "'";

        $sql_values = array();
        $sql_main = "FROM {$this->table} rl WHERE 1 AND rl.shop_code in ({$shop_code_str}) AND rl.store_code in ({$store_code_str}) ";
        //是否处理
        if ($filter['ex_list_tab'] == 'ok_upload') {
            $sql_main .= " AND rl.order_status = 1 ";
        } else {
            $sql_main .= " AND (rl.order_status = 0 OR rl.order_status = 2) ";
        }
        //订退单号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND rl.record_code LIKE :record_code";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] !== '') {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ( " . $str . " ) ";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
     $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code in ( " .$str. " ) ";
        }
        //单据类型
        if (isset($filter['order_type']) && $filter['order_type'] !== 'all') {
            $sql_main .= " AND rl.order_type = :order_type ";
            $sql_values[':order_type'] = $filter['order_type'];
        }
        //处理时间
        if (isset($filter['upload_date_start']) && $filter['upload_date_start'] != '') {
            $sql_main .= " AND rl.upload_date >= :upload_date_start ";
            $sql_values[':upload_date_start'] = $filter['upload_date_start'];
        }
        if (isset($filter['upload_date_end']) && $filter['upload_date_end'] != '') {
            $sql_main .= " AND rl.upload_date <= :upload_date_end ";
            $sql_values[':upload_date_end'] = $filter['upload_date_end'];
        }

        if ($filter['ex_list_tab'] == 'ok_upload') {
            $sql_main .= " ORDER BY rl.upload_date DESC";
        } else {
            $sql_main .= " ORDER BY rl.pay_refund_time DESC";
        }

        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        filter_fk_name($data['data'], array('store_code|store', 'shop_code|shop', 'sale_channel_code|sale_channel'));
        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['order_type'] = $this->order_type[$val['order_type']];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }
    //获取sap配置信息
    function get_sap_config() {
        $sql = "SELECT * FROM sap_config";
        $sap_config = $this->db->get_row($sql);
        return $sap_config;
    }

    function insert_record() {
        $add_update_time = time();
        $sql = "SELECT * FROM sap_config";
        $sap_config = $this->db->get_row($sql);
        $efast_shop_code_arr = explode(",", $sap_config['efast_shop_code']);
        $shop_code_str = "'" . implode("','", $efast_shop_code_arr) . "'";

        $efast_store_code_arr = explode(",", $sap_config['efast_store_code']);
        $store_code_str = "'" . implode("','", $efast_store_code_arr) . "'";
        //获取当前时间
        $end_date = date('Y-m-d H:i:s');
        //查询sap更新时间表最后时间
        $sql = "SELECT update_time FROM sap_update_time ORDER BY id DESC ";
        $update_time = $this->db->get_value($sql);
        if ($update_time == 0 || empty($update_time)) {
            $begin_date = date('Y-m-d H:i:s', strtotime($sap_config['online_time']));
        } else {
            $begin_date = date("Y-m-d H:i:s", $update_time - 300);
        }

        //查询订单
        $record_sql = "SELECT sell_record_code AS record_code,sale_channel_code,shop_code,store_code,goods_num,order_money,goods_money,express_money,delivery_money,0 AS order_type,pay_time AS pay_refund_time,concat(sell_record_code , '_0') AS record_code_type,payable_money,delivery_time AS record_time,invoice_number,deal_code_list AS deal_code FROM oms_sell_record ";
        $record_where = " WHERE shipping_status = 4 AND order_status !=3 AND delivery_time >= '{$begin_date}' AND delivery_time <= '{$end_date}' AND shop_code IN ({$shop_code_str}) AND store_code in ({$store_code_str}) ";

        $sql = "SELECT COUNT(sell_record_code) FROM oms_sell_record" . $record_where;
        $record_num = $this->db->get_value($sql);

        //查询退单
        $return_sql = "SELECT sell_return_code AS record_code,sale_channel_code,shop_code,store_code,recv_num AS goods_num,refund_total_fee AS order_money,return_avg_money AS goods_money,(buyer_express_money+seller_express_money) AS express_money,0 AS delivery_money,1 AS order_type,agreed_refund_time AS pay_refund_time,concat(sell_return_code , '_1') AS record_code_type,receive_time AS record_time,deal_code FROM oms_sell_return ";
        $return_where = " WHERE return_shipping_status = 1 AND return_order_status != 3 AND receive_time >= '{$begin_date}' AND receive_time <= '{$end_date}' AND shop_code in ({$shop_code_str}) AND store_code in ({$store_code_str})";

        $sql = "SELECT COUNT(sell_return_code) FROM oms_sell_return" . $return_where;
        $return_num = $this->db->get_value($sql);

        if ($record_num == 0 && $return_num == 0) {
            return $this->format_ret(-1, '', '没有数据可以更新');
        }

        $this->begin_trans();
        try {

            if ($record_num != 0) {
                //订单添加中间表数据
                $ret = $this->insert_sap_record($record_num, $record_sql, $record_where, 'oms_sell_record');
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '订单更新失败');
                }
            }
            if ($return_num != 0) {
                //退单添加中间表数据
                $ret = $this->insert_sap_record($return_num, $return_sql, $return_where, 'oms_sell_return');
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '退单更新失败');
                }
            }
            //添加sap更新时间表的更新时间
            $this->insert_exp('sap_update_time', array('update_time' => $add_update_time));
            $this->commit();
            return $ret;
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '更新失败' . $e->getMessage());
        }
    }

    function insert_sap_record($num, $sql_main, $where, $table) {
        $page_size = ceil((float) $num / 500);
        $page = 0;
        $size = 500;
        for ($i = 1; $i <= $page_size; $i++) {
            //查询单据编号
            if ($table == 'oms_sell_record') {
                $sql_detail = "SELECT sell_record_code FROM " . $table . $where;
            } else if ($table == 'oms_sell_return') {
                $sql_detail = "SELECT sell_return_code FROM " . $table . $where;
            }
            $record_code_arr = $this->db->get_all_col($sql_detail);
            
            //添加主表数据
            $sql = $sql_main . $where . "limit {$page},{$size}";
            $record_data = $this->db->getAll($sql);
            if($table == 'oms_sell_return') {
                foreach($record_data as $key => $val){
                    $return_data = load_model('oms/SellReturnModel')->get_return_by_return_code($val['record_code']);
                    $ytk1 = bcadd($return_data['return_avg_money'], $return_data['seller_express_money'], 3);
                    $ytk2 = bcadd($return_data['compensate_money'], $return_data['adjust_money'], 3);
                    $record_data[$key]['payable_money'] = bcadd($ytk1, $ytk2, 3);
                }
            }
            $ret = $this->insert_multi($record_data, TRUE);
            if ($ret['status'] < 0) {
                break;
            }
            
            //添加明细数据
            $ret = $this->insert_sap_detail($record_code_arr, $table);
            if ($ret['status'] < 0) {
                break;
            }
            $page += 500;
        }
        return $ret;
    }

    //添加sap明细
    function insert_sap_detail($record_code_arr, $table) {
        $record_code_str = "'" . implode("','", $record_code_arr) . "'";
        //判断订单还是退单
        if ($table == 'oms_sell_record') {
            //查询订单退单明细表的数据
            $detail_sql = "SELECT sell_record_code AS record_code,goods_code,spec1_code,spec2_code,sku,barcode,goods_price,cost_price,num,avg_money,deal_code,0 AS order_type,is_gift FROM oms_sell_record_detail WHERE sell_record_code IN ({$record_code_str})";
        } else if ($table == 'oms_sell_return') {
            $detail_sql = "SELECT sell_return_code AS record_code,goods_code,spec1_code,spec2_code,sku,barcode,goods_price,0 AS cost_price,recv_num AS num,avg_money,deal_code,1 AS order_type,0 AS is_gift FROM oms_sell_return_detail WHERE sell_return_code IN ({$record_code_str})";
        }
        $detail_data = $this->db->getAll($detail_sql);
        $ret = $this->insert_detail($detail_data, TRUE);
        return $ret;
    }

    function insert_detail($data) {
        $check_is_gift = array();
        foreach ($data as &$val) {
            $val['record_code_type'] = $val['record_code'] . '_' . $val['order_type'];
            if (array_key_exists(($val['sku'] . $val['record_code'] . $val['deal_code'] . $val['order_type']), $check_is_gift)) {
                $check_is_gift[$val['sku'] . $val['record_code'] . $val['deal_code'] . $val['order_type']]['num'] += $val['num'];
                continue;
            }
            $check_is_gift[$val['sku'] . $val['record_code'] . $val['deal_code'] . $val['order_type']] = $val;
        }
        $check_is_gift = array_chunk($check_is_gift, 2000);
        foreach ($check_is_gift as $v) {
            $ret = $this->insert_multi_exp('sap_sell_record_detail', $v,true);
        }
        return $ret;
    }

    function get_by_id($id_str) {
        $sql = "SELECT r2.*,r1.shop_code,r1.pay_refund_time,r1.sap_record_id,r1.order_status AS record_status,r1.invoice_number FROM sap_sell_record AS r1 INNER JOIN sap_sell_record_detail AS r2 ON r1.record_code_type = r2.record_code_type WHERE r1.sap_record_id IN ({$id_str}) ORDER BY r2.sap_record_detail_id";
        return $this->db->get_all($sql);
    }

    function upload_data($data,$sap_record_id) {
        $sap_data = array();
        $record_code_type_arr = array();
        $error_info_num = array();
        $error = '';
        $posex = 0;
        $execute_date = '2017-03-01 00:00:00';
        $current_date = date('Y-m-d H:i:s');
        if($current_date >= $execute_date) {
            //查询定单交易号
            $sql = "SELECT deal_code,order_type FROM sap_sell_record WHERE sap_record_id = :id";
            $record = $this->db->get_row($sql,array(':id' => $sap_record_id));
            $point_fee = '';
            $deal_code = !empty($record['deal_code']) ? $record['deal_code'] : '';
            if(!empty($deal_code) && $record['order_type'] == 0) { //订单类型，有交易号，查询订单积分
                $deal_code_arr = explode(",", $deal_code);
                //查询关联的交易号是否已扣减
                $where = array();
                $deal_code_str = $this->arr_to_in_sql_value($deal_code_arr,'tid',$where);
                $sql = "SELECT r2.deal_code FROM sap_sell_record AS r1 LEFT JOIN sap_sell_record_detail AS r2 ON r1.record_code = r2.record_code WHERE r1.order_type = 0 AND r2.deal_code IN ({$deal_code_str}) AND r1.order_status = 1";
                $record_status = $this->db->get_all_col($sql,$where);
                //去掉重复的数据
                $record_status = array_unique($record_status);
                $deal_code_arr = array_diff($deal_code_arr,$record_status);
                //查询订单积分
                if(!empty($deal_code_arr)) {
                    $sql_values = array();
                    $deal_code_str = $this->arr_to_in_sql_value($deal_code_arr,'tid',$sql_values);
                    $sql = "SELECT sum(point_fee) FROM api_taobao_trade WHERE tid in ({$deal_code_str})";
                    $point_fee = $this->db->get_value($sql,$sql_values);
                    $point_fee_money = (float)$point_fee / 100;
                }
            }
            if(!empty($point_fee_money)) { //有积分金额，扣减积分金额，重新计算均摊金额
                $data = $this->compute_avg_money($point_fee_money,$data);
            }
        }
        //组合sap数据
        foreach ($data as $val) {
            $posex++;
            if($val['order_type'] == 0) { //订单
                $order_type = 'ZG01'; 
            } else if($val['order_type'] == 1) { // 退单
                $order_type = 'ZG02'; 
            }
            $fkdat = $val['pay_refund_time'] == '0000-00-00 00:00:00' ? '' : date('Ymd', strtotime($val['pay_refund_time']));
            $sql = "SELECT goods_name FROM base_goods WHERE goods_code = '{$val['goods_code']}'";
            $goods_name = $this->db->get_value($sql);
            $sql = "SELECT barcode FROM goods_sku WHERE sku = '{$val['sku']}'";
            $barcode = $this->db->get_value($sql);
            $sap_barcode = str_pad($barcode, '18', '0', STR_PAD_LEFT);
            $sql = "SELECT * FROM sys_api_shop_store WHERE shop_store_code = '{$val['shop_code']}' AND p_type = 3;";
            $sap_shop = $this->db->get_row($sql);
            $sql = "SELECT property_val1 FROM base_property WHERE property_val_code = '{$val['goods_code']}'";
            $property = $this->db->get_value($sql);
            if($val['avg_money'] == 0) {
                $error = '第'.$posex.'行条码'.$barcode.'商品金额为零。';
                break;
            }
            if ($val['record_status'] == 1) {
                $error = '单据已上传。';
                break;
            }
            if ($val['num'] == 0) {
                $error[] = '第'.$posex.'行条码'.$barcode.'商品数量为零。';
                break;
            }
            //如果是积分单据写死商品信息
            if($val['order_type'] == 2) {
                $sap_shop['outside_code'] = 102265;
                $order_type = 'ZGE1'; 
                $goods_name = '电商积分虚拟物料';
                $barcode = '1000000002';
                $sap_barcode = str_pad($barcode, '18', '0', STR_PAD_LEFT);
                $property = 'PC';
            }
            //商品实际销售单价
            $goods_price = sprintf("%01.2f", (float)$val['avg_money'] / $val['num']);
            $sap_data['IT_DATA'][] = array(
                'BSTKD' => $val['record_code'],
                'POSEX' => $posex,
                'AUART' => $order_type,
                'KUNNR' => $sap_shop['outside_code'],
                'FKDAT' => $fkdat,
                'MATNR' => $sap_barcode,
                'ARKTX' => $goods_name,
                'KWMENG' => sprintf("%01.3f", $val['num']),
                'VRKME' => $property,
                'ACTPR' => $goods_price,
                'WRBTR' => sprintf("%01.2f", $val['avg_money']),
                'WAERK' => 'CNY',
                'VFLNR' => $val['invoice_number'],
            );
        }
        if (!empty($error)) {
            $sql = "UPDATE sap_sell_record SET upload_info = '{$error}' WHERE sap_record_id = {$sap_record_id}";
            $this->query($sql);
            return $this->format_ret(-1, '', $error);
        }
        /*if (!empty($error_info_num)) {
            $error_info_num = "'" . implode("','", $error_info_num) . "'";
            $sql = "UPDATE sap_sell_record SET upload_info = concat(upload_info,'单据明细数量为空' WHERE sap_record_id in ({$error_info_num})";
            return $this->format_ret(-1, '', '单据明细数量为空');
        }*/
        $ret = load_model('api/sap/SapApiClientModel')->zr_send_order_to_sap($sap_data);
        $date = date('Y-m-d H:i:s');
        if ($ret['data']["ET_RETURN"][0]['TYPE'] == 'E') {
            $message = '';
            foreach($ret['data']["ET_RETURN"] as $val) {
                $message .= $val['MESSAGE'];
            }
            $message = substr($message, 0, -1);
            $i = strcmp($message, '客户采购订单编号 ' . $val['record_code'] . ' 已经存在。');
            if ($i == 0) {
                $sql = "UPDATE sap_sell_record SET order_status = 1,upload_info = '',upload_date = '{$date}' WHERE sap_record_id = '{$sap_record_id}'";
                $this->query($sql);
            } else {
                $sql = "UPDATE sap_sell_record SET order_status = 2,upload_info = '{$message}' WHERE sap_record_id = '{$sap_record_id}'";
                $this->query($sql);
            }
            //return $this->format_ret(-1, '', $message);
        } else {
            $sql = "UPDATE sap_sell_record SET order_status = 1,upload_date = '{$date}',upload_info = '' WHERE sap_record_id = '{$sap_record_id}'";
            $this->query($sql);
        }
        return $this->format_ret(1, '', '上传成功');
    }
    
    function compute_avg_money($point_fee,$data) {
        $avg_money = array();
        $id = array();
        //排序数组
        foreach($data as $key => $val) {
            $avg_money[$key] = $val['avg_money'];
            $id[$key] = $val['sap_record_detail_id'];
        }
        array_multisort($avg_money, SORT_DESC, $id, SORT_ASC, $data);
        //扣减积分
        foreach($data as &$val) {
            if ($val['avg_money'] >= $point_fee) {
                $val['avg_money'] -= (float) $point_fee;
                $point_fee = 0;
            } else {
                $point_fee -= (float) $val['avg_money'];
                $val['avg_money'] = 0;
            }
        }
        return $data;
    }

    function update_error($sap_record_id) {
        $sql = "UPDATE sap_sell_record SET upload_info = '单据明细不存在' WHERE sap_record_id = ({$sap_record_id})";
        $this->query($sql);
        return $this->format_ret(-1, '', '单据明细不存在');
    }

    //每月1号生成上月每个店铺的积分
    function insert_integral() {
        $this->begin_trans();
        try {
            if ($this->implement_num > 3) {
                throw new Exception('执行完毕', -2);
            }
            
            //查询sap配置的仓库
            $sql = "SELECT * FROM sys_api_shop_store WHERE outside_code = '1024' AND p_type = 3";
            $store_data = $this->db->get_row($sql);
            $store_code = $store_data['shop_store_code'];
            //获取上月时间
            $timestamp = strtotime(date('Y-m-d'));
            $firstday = date('Y-m-01', strtotime(date('Y', $timestamp) . '-' . (date('m', $timestamp) - 1) . '-01')) . ' 00:00:00';
            $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day")) . ' 23:59:59';
            //统计淘宝平台店铺积分
          /*  $sql = "SELECT shop_code,sum(real_point_fee) AS real_point_fee FROM api_taobao_trade WHERE (end_time >= '{$firstday}' AND end_time <= '{$lastday}') AND status = 'TRADE_FINISHED' GROUP BY shop_code";*/
		  $sql = "SELECT shop_code,sum(point_fee) AS real_point_fee FROM api_taobao_trade WHERE (created >= '{$firstday}' AND created <= '{$lastday}') GROUP BY shop_code";
            $shop_arr = $this->db->get_all($sql);
            //组合数据
            $params = array();
            $detail = array();
            foreach ($shop_arr as $key => $val) {
                if($val['real_point_fee'] == 0) {
                    continue;
                }
                //查询sap配置的店铺
                $sql = "SELECT count(*) AS count FROM sys_api_shop_store WHERE shop_store_code = '{$val['shop_code']}' AND p_type = 3";
                $id_shop = $this->db->get_value($sql);
                if($id_shop == 0) {
                    continue;
                }
                //判断有没有重复
                $sql = "SELECT count(*) FROM sap_sell_record WHERE order_type = 2 AND shop_code = :shop_code AND record_time = :record_time";
                $shop_num = $this->db->get_value($sql,array(':shop_code' => $val['shop_code'],':record_time' => $lastday));
                if($shop_num != 0) {
                    continue;
                }
                
                $record_code = 'JF' . date('Ymd') . str_pad($key + 1, 3, 0, STR_PAD_LEFT);
                $money = sprintf("%01.3f", $val['real_point_fee'] / 100);
                $params[] = array(
                    'record_code' => $record_code,
                    'order_type' => 2, 
                    'sale_channel_code' => 'taobao',
                    'store_code' => $store_code,
                    'shop_code' => $val['shop_code'],
                    'goods_num' => 1,
                    'order_money' => $money,
                    'goods_money' => $money,
                    'record_code_type' => $record_code . '_2',
                    'payable_money' => $money,
                    'record_time' => $lastday,
                    'pay_refund_time' => $lastday,
                );
                $detail[] = array(
                    'record_code' => $record_code,
                    'order_type' => 2, 
                    'goods_code' => 'JF',
                    'sku' => '12',
                    'barcode' => '12',
                    'goods_price' => $money,
                    'cost_price' => $money,
                    'num' => 1,
                    'avg_money' => $money,
                    'record_code_type' => $record_code . '_2',
                    'is_gift' => 0,
                );
            }

            //添加主表
            $ret = $this->insert_multi($params, TRUE);
            if ($ret['status'] < 0) {
                throw new Exception('更新失败', -1);
            }
            //添加明细表
            $ret = $this->insert_multi_exp('sap_sell_record_detail', $detail);
            if ($ret['status'] < 0) {
                throw new Exception('更新失败', -1);
            }
            $this->commit();
            $this->implement_num = 1;
            return $ret;
        } catch (Exception $e) {
            $this->rollback();
            $msg = $e->getCode() == 0 ? '更新异常' : $e->getMessage();
            if ($e->getCode() == -2) {
                $this->implement_num = 1;
                return $this->format_ret(-1, '', $msg);
            } else if ($e->getCode() == -1) {
                $this->implement_num ++;
                $this->insert_integral();
            }
        }
    }
    //自动服务上传单据
    function uploade_automatism() {
        //查询出未上传的总数
        $sql = "SELECT sap_record_id FROM sap_sell_record WHERE order_status != 1";
        $sap_id_arr = $this->db->get_all($sql);
        foreach ($sap_id_arr as $key => $val) {
            $data = $this->get_by_id($val['sap_record_id']);
            // 没有明细就跳过
            if(empty($data)) {
                $this->update_error($val['sap_record_id']);
                continue;
            }   
            $this->upload_data($data,$val['sap_record_id']);
        }
        return $this->format_ret(1);
    }
}
