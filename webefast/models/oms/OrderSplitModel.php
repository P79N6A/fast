<?php

/**
 * 订单拆分模型类
 *
 * @author user
 */
require_model("oms/OrderSplitBaseModel");

class OrderSplitModel extends OrderSplitBaseModel {

    //一键拆分
    function split_a_key() {
        $sell_record_info = load_model("oms/SellRecordModel")->get_all(array("is_real_stock_out" => '2'));
        if ($sell_record_info['status'] == '1') {
            foreach ($sell_record_info['data'] as $info) {
                $ret = $this->auto_base_split($info['sell_record_id'], "is_real_stock_out");
            }
        }
        return $this->format_ret(1);
    }

    //批量拆分
    function split_group($sell_record_ids) {
        foreach ($sell_record_ids as $id) {
            $this->auto_base_split($id, "is_real_stock_out");
        }
        return $this->format_ret(1);
    }

    //缺货拆分
    function split_short($sell_record_code,$split_short = 0) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/split_short')) {
            return $this->format_ret(-1, '', '无权访问');
        }
        //###########
        $record = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);
        $detail = load_model('oms/SellRecordModel')->get_detail_list_by_code($sell_record_code);
        $store_code = $record['store_code'];
        $split_arr = array();
        //如果系统参数开启，则赠品也作为订单商品参与缺货拆单
        $gift_split = load_model('sys/SysParamsModel')->get_val_by_code(array('gift_split'));
        if($gift_split['gift_split'] == 1){
            foreach ($detail as $sub_og) {
                if ($sub_og['is_delete'] > 0) {
                    continue;
                }
                if ($sub_og['num'] <= $sub_og['lock_num']) {
                    $split_arr[0]['data'][] = array('sell_record_detail_id' => $sub_og['sell_record_detail_id'], 'num' => $sub_og['num']);
                    $split_arr[0]['store_code'] = $store_code;
                } else {
                    $split_arr[1]['data'][] = array('sell_record_detail_id' => $sub_og['sell_record_detail_id'], 'num' => $sub_og['num']);
                    $split_arr[1]['store_code'] = $store_code;
                }
            }
            if (count($split_arr) <= 1) {
                return $this->format_ret(-1, '', '缺货单内不存在无库存的明细商品(含赠品),不能进行缺货拆单操作');
            }
        }else{
            foreach ($detail as $sub_og) {
                if ($sub_og['is_gift'] > 0 || $sub_og['is_delete'] > 0) {
                    continue;
                }
                if ($sub_og['num'] <= $sub_og['lock_num']) {
                    $split_arr[0]['data'][] = array('sell_record_detail_id' => $sub_og['sell_record_detail_id'], 'num' => $sub_og['num']);
                    $split_arr[0]['store_code'] = $store_code;
                } else {
                    $split_arr[1]['data'][] = array('sell_record_detail_id' => $sub_og['sell_record_detail_id'], 'num' => $sub_og['num']);
                    $split_arr[1]['store_code'] = $store_code;
                }
            }
            if (count($split_arr) <= 1) {
                return $this->format_ret(-1, '', '缺货单内不存在无库存的明细商品(不含赠品),不能进行缺货拆单操作');
            }
        }
        $split_arr = array();
        foreach ($detail as $sub_og) {
            if ($sub_og['is_delete'] > 0) {
                continue;
            }
            if ($sub_og['num'] <= $sub_og['lock_num']) {
                $split_arr[0]['data'][] = array('sell_record_detail_id' => $sub_og['sell_record_detail_id'], 'num' => $sub_og['num']);
                $split_arr[0]['store_code'] = $store_code;
            } else {
                $split_arr[1]['data'][] = array('sell_record_detail_id' => $sub_og['sell_record_detail_id'], 'num' => $sub_og['num']);
                $split_arr[1]['store_code'] = $store_code;
            }
        }

        $ret = load_model('oms/OrderSplitBaseModel')->order_split_logic($split_arr, $record, $detail, $skip_lock_check = 10);
        //echo '<hr/>$arr<xmp>'.var_export($ret,true).'</xmp>';die;
        return $ret;
    }

    //分仓发货拆分
    function divide_store_send($record, $detail) {
        $sku_arr = array();
        foreach ($detail as $row) {
            $sku_arr[] = $row['sku'];
        }

        $sku_list = "'" . join("','", $sku_arr) . "'";

        //增加库存来源仓库
        $sql = "select stock_source_store_code from base_shop where shop_code = :shop_code ";
        $stock_source_store_code = $this->db->get_value($sql, array(':shop_code' => $record['shop_code']));
        $store_arr = array();
        if (!empty($stock_source_store_code)) {
            $store_arr = explode(",", trim($stock_source_store_code));
        }
        $store_str = "'" . implode("','", $store_arr) . "'";
        $where = "  AND store_code in({$store_str}) ";

        $sql = "SELECT
					store_code,
					sku,
					sum(stock_num) AS stock_num,
					sum(lock_num) AS lock_num
				FROM
					goods_inv_lof
				WHERE
					sku IN ({$sku_list}) {$where}
				GROUP BY
					store_code,sku";


        $db_inv = ctx()->db->getAll($sql);
        $inv_arr = array();
        foreach ($db_inv as $sub_inv) {
            $inv_arr[$sub_inv['sku']][$sub_inv['store_code']] = $sub_inv;
        }
        $split_detail = array();
        foreach ($detail as $row) {
            $_sku = $row['sku'];
            $_barcode = load_model('goods/SkuCModel')->get_barcode($_sku);
            $_need_num = $row['num'];
            $_lock_num = $row['lock_num'];
            if ($row['num'] == $row['lock_num']) {
                $row['store_code'] = $record['store_code'];
                $split_detail[$row['store_code']][] = $row;
            } else {
                if (!isset($inv_arr[$_sku])) {
                    return $this->format_ret(100, '', $_barcode . '条码找不到库存');
                }
                $find_inv = $inv_arr[$_sku];
                $_find_inv_row = array();
                foreach ($find_inv as $sub_inv) {
                    $_inv_lock_num = $sub_inv['lock_num'] > 0 ? $sub_inv['lock_num'] : 0;
                    $_inv_num = $sub_inv['stock_num'] - $_inv_lock_num;
                    if ($sub_inv['store_code'] == $record['store_code']) {
                        $_inv_num += $_lock_num;
                    }
                    if ($_inv_num >= $_need_num) {
                        $_find_inv_row = $sub_inv;
                    }
                }
                //echo '<hr/>$_find_inv_row<xmp>'.var_export($_find_inv_row,true).'</xmp>';
                if (empty($_find_inv_row)) {
                    return $this->format_ret(100, '', $_barcode . '条码找不到充足库存');
                } else {
                    $row['store_code'] = $_find_inv_row['store_code'];
                    $split_detail[$row['store_code']][] = $row;
                }
            }
        }
        if (empty($split_detail) || count($split_detail) <= 1) {
            return $this->format_ret(200, $split_detail, '没有需要拆单的明细');
        }

        $split_arr = array();
        $idx = 0;
        foreach ($split_detail as $store_code => $_detail) {
            $split_arr[$idx]['store_code'] = $store_code;
            foreach ($_detail as $_row) {
                $split_arr[$idx]['data'][] = array('sell_record_detail_id' => $_row['sell_record_detail_id'], 'num' => $_row['num']);
            }
            $idx++;
        }

        $skip_lock_check = 10;
        $ret = load_model('oms/OrderSplitBaseModel')->order_split_logic($split_arr, $record, $detail, $skip_lock_check, '分仓发货，自动拆单。', 1);
        //echo '<hr/>$arr<xmp>'.var_export($ret,true).'</xmp>';die;
        return $ret;
    }

    /**
     * 预售拆单
     * @param array $record 主单数据
     * @param array $detail 明细数据
     * @return array 结果
     */
    function presell_order_split($record, $detail) {
        $split_arr = array();
        $store_code = $record['store_code'];
        $presell_arr = array();
        $stock_arr = array();
        foreach ($detail as $val) {
            if ($val['sale_mode'] == 'presale') {
                $presell_arr[] = $val;
            } else {
                $stock_arr[] = $val;
            }
        }
        $split_arr[0]['store_code'] = $store_code;
        foreach ($presell_arr as $p) {
            $split_arr[0]['data'][] = array('sell_record_detail_id' => $p['sell_record_detail_id'], 'num' => $p['num']);
        }

        $split_arr[1]['store_code'] = $store_code;
        foreach ($stock_arr as $s) {
            $split_arr[1]['data'][] = array('sell_record_detail_id' => $s['sell_record_detail_id'], 'num' => $s['num']);
        }
        if (empty($split_arr[1]['data'])) {
            return $this->format_ret(1);
        }

        $ret = load_model('oms/OrderSplitBaseModel')->order_split_logic($split_arr, $record, $detail, 10, '预售订单，自动拆单。', 1);

        return $ret;
    }

    //预售拆分
    function split_presale($sell_record_id) {
        return $this->auto_base_split($sell_record_id, "is_presale");
    }

    //公用自动拆分方法
    function auto_base_split($sell_record_id, $type) {
        $not_stock_out = array(); //未缺货数组
        $stock_out = array(); //缺货数组
        $gift = array(); //礼品
        $not_gift = array(); //缺货礼品
        $sell_record_info = load_model("oms/SellRecordModel")->get_row(array("sell_record_id" => $sell_record_id));
        if ($sell_record_info['status'] != '1') {
            return $this->format_ret(-1, '', 'SELL_RECORD_NOT_EXIST');
        }
        if ($sell_record_info['data'][$type] != '2') {
            return $this->format_ret(-1, '', 'SELL_RECORD_CANT_SPLIT');
        }
        $store_id = $sell_record_info['data']['store_id'];
        $sell_record_detail_list = load_model("oms/SellRecordModel")->get_detail_by_pid($sell_record_id);
        foreach ($sell_record_detail_list as $sell_record_detail) {
            $info['sell_record_detail_id'] = $sell_record_detail['sell_record_detail_id'];
            $info['num'] = $sell_record_detail['num'];
            if ($sell_record_detail['is_gift'] == '0') {
                if ($sell_record_detail[$type] == '0') {
                    array_push($not_stock_out, $info);
                } else {
                    array_push($stock_out, $info);
                }
            } else {
                if ($sell_record_detail[$type] == '0') {
                    array_push($gift, $info);
                } else {
                    array_push($not_gift, $info);
                }
            }
        }
        if (empty($not_stock_out) || empty($stock_out)) {
            return $this->format_ret(-1, '', 'SELL_RECORD_SPLIT_ERROR');
        } else {
            $not_stock_out = array_merge($not_stock_out, $gift);
            $stock_out = array_merge($stock_out, $not_gift);
        }
        $arr[] = array("data" => $not_stock_out, "store_id" => $store_id);
        $arr[] = array("data" => $stock_out, "store_id" => $store_id);
        return $this->order_split_logic($arr, $sell_record_id);
    }

    function order_split($data) {
        $sell_record_code = $data['sell_record_code'];
        if (!empty($data['data']) && !empty($sell_record_code)) {
            //获取单个订单的订单数据
            $record = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);
            //获取单个订单的订单详情
            $detail = load_model('oms/SellRecordModel')->get_detail_list_by_code($sell_record_code);
            $store_code = $record['store_code'];
            $opt = isset($data['opt']) ? $data['opt'] : '';
            //$split_arr = array();
            foreach ($data['data'] as $data) {
                foreach ($detail as $sub_og) {
                    if ($sub_og['is_delete'] > 0) {
                        continue;
                    }
                    if ($data['sell_record_detail_id'] == $sub_og['sell_record_detail_id']) {
                        $sell_record_detail_id = $data['sell_record_detail_id'];
                        //普通拆分
                        if ($opt === 'tabs_split') {
                            if ($data['split_num'] != 0) {
                                $split_arr[0]['data'][] = array('sell_record_detail_id' => $sell_record_detail_id, 'num' => $data['split_num']);
                                $split_arr[0]['store_code'] = $store_code;
                            }
                            if ($sub_og['num'] - $data['split_num'] > 0) {
                                $split_arr[1]['data'][] = array('sell_record_detail_id' => $sell_record_detail_id, 'num' => ($sub_og['num'] - $data['split_num']));
                                $split_arr[1]['store_code'] = $store_code;
                            }
                            //批量拆分
                        } else if ($opt === 'tabs_split_all') {
                            if ($data['split_num'] != 0) {
                                //每单数能整除
                                if ($sub_og['num'] % $data['split_num'] === 0) {
                                    $num = $sub_og['num'] / $data['split_num'];
                                    for ($key = 0; $key < $num; $key++) {
                                        $split_arr[$key]['data'][] = array('sell_record_detail_id' => $sell_record_detail_id, 'num' => $data['split_num']);
                                        $split_arr[$key]['store_code'] = $store_code;
                                    }
                                } else {
                                    //每单数不能整除
                                    $num = floor($sub_og['num'] / $data['split_num']);
                                    $sub_num = $sub_og['num'] % $data['split_num'];
                                    for ($key = 0; $key < $num; $key++) {
                                        $split_arr[$key]['data'][] = array('sell_record_detail_id' => $sell_record_detail_id, 'num' => $data['split_num']);
                                        $split_arr[$key]['store_code'] = $store_code;
                                    }
                                    $split_arr[$num]['data'][] = array('sell_record_detail_id' => $sell_record_detail_id, 'num' => $sub_num);
                                    $split_arr[$num]['store_code'] = $store_code;
                                }
                            } else {
                                if ($sub_og['num'] > 1) {
                                    return $this->format_ret(-1, '', '拆分数量应该为大于1并且小于商品数量的整数');
                                }
                                //批量拆分下,除商品数量为1外,其他情况都不允许拆分数量为0,则当拆分数量为0时,商品数量必为0
                                $split_arr[0]['data'][] = array('sell_record_detail_id' => $sell_record_detail_id, 'num' => 1);
                                $split_arr[0]['store_code'] = $store_code;
                            }
                        }
                    }
                }
            }
            $ret = load_model('oms/OrderSplitBaseModel')->order_split_logic($split_arr, $record, $detail, $skip_lock_check = 10);
            return $ret;
        } else {
            return $this->format_ret(-1, '', '拆分数量不能为空！');
        }
    }

    /**
     * 按重量拆单
     * @param string $sell_code 订单号
     * @param int $max_weight 订单最大重量
     * @return array 结果
     */
    private function split_order_by_weight($record, $detail, $max_weight) {
        $sku_arr = array_column($detail, 'sku');
        $weight_data = load_model('prm/SkuModel')->get_goods_weight($sku_arr);
        $sort_arr = array();
        foreach ($detail as $key => $d) {
            $detail[$key]['weight'] = $weight_data[$d['sku']];
            $sort_arr[$key] = $weight_data[$d['sku']];
        }

        $sort_res = array_multisort($sort_arr, SORT_DESC, $detail);
        if ($sort_res == FALSE) {
            return $this->format_ret(-1, '', '处理失败');
        }
        unset($sort_arr, $weight_data);

        $k = 0;
        $split_data = array();
        $no_fill_data = array();
        foreach ($detail as $row) {
            $detail_id = $row['sell_record_detail_id'];
            $sku = $row['sku'];
            $num = (int) $row['num'];
            $sku_weight = $row['weight'];

            //未设置重量的不参与拆单
            if (bccomp($sku_weight, 0, 6) == 0) {
                $split_data[0][$detail_id] = array('sell_record_detail_id' => $detail_id, 'num' => $num, 'weight' => $sku_weight, 'sku' => $sku);
                continue;
            }

            //商品重量>=订单最大重量，则一单一件
            $single_multi = bcdiv($sku_weight, $max_weight, 2);
            if (bccomp($single_multi, 0.5, 2) > 0) {
                for ($i = 0; $i < $num; $i++) {
                    $k ++;
                    $split_data[$k][$detail_id] = array('sell_record_detail_id' => $detail_id, 'num' => 1, 'weight' => $sku_weight, 'sku' => $sku);

                    if (bccomp($single_multi, 0.5, 2) > 0 && bccomp($single_multi, 1, 2) < 0) {
                        $no_fill_data[$k] = bcsub($max_weight, $sku_weight, 6);
                    }
                }
                continue;
            }

//            $avg_num = floor($max_weight / $sku_weight);
//            if ($avg_num <= $num) {
//                $k ++;
//                $split_data[$k][$detail_id] = array('sell_record_detail_id' => $detail_id, 'num' => $avg_num, 'weight' => $sku_weight, 'sku' => $sku);
//                $num -= $avg_num;
//                if ($avg_num < $num) {
//                    $no_fill_data[$k] = $max_weight - $sku_weight * $avg_num;
//                }
//            }
            $total_weight = bcmul($sku_weight, $num, 6);

            foreach ($no_fill_data as $kk => $surplus_weight) {
                if ($num == 0) {
                    break;
                }
                if (bccomp($surplus_weight, $sku_weight, 6) < 0) {
                    continue;
                }
                $bccomp_val = bccomp($total_weight, $surplus_weight, 6);
                if ($bccomp_val <= 0) {
                    $split_data[$kk][$detail_id] = array('sell_record_detail_id' => $detail_id, 'num' => $num, 'weight' => $sku_weight, 'sku' => $sku);
                    if ($bccomp_val < 0) {
                        $no_fill_data[$kk] = bcsub($no_fill_data[$kk], $total_weight, 6);
                    } else {
                        unset($no_fill_data[$kk]);
                    }
                    $num = 0;
                    break;
                }
                if ($bccomp_val > 0) {
                    $curr_num = floor(bcdiv($surplus_weight, $sku_weight, 6));
                    if ($curr_num == 0) {
                        continue;
                    }
                    $split_data[$kk][$detail_id] = array('sell_record_detail_id' => $detail_id, 'num' => $curr_num, 'weight' => $sku_weight, 'sku' => $sku);
                    $sku_total_weight = bcmul($curr_num, $sku_weight, 6);
                    if (bccomp($sku_total_weight, $surplus_weight, 6) == 0) {
                        unset($no_fill_data[$kk]);
                    } else {
                        $no_fill_data[$kk] = bcsub($no_fill_data[$kk], $sku_total_weight, 6);
                    }
                    $total_weight = bcsub($total_weight, $sku_total_weight, 6);
                    $num -= $curr_num;
                }
            }

            if ($num == 0) {
                continue;
            }

            if (bccomp($total_weight, $max_weight, 6) <= 0) {
                $k ++;
                $split_data[$k][$detail_id] = array('sell_record_detail_id' => $detail_id, 'num' => $num, 'weight' => $sku_weight, 'sku' => $sku);
                if (bccomp($total_weight, $max_weight, 6) < 0) {
                    $no_fill_data[$k] = bcsub($max_weight, $total_weight, 6);
                }
                continue;
            }
            $multi_int = bcdiv($total_weight, $max_weight, 6);
            $multi_int = bccomp(bcdiv($sku_weight, $max_weight, 6), 0.5, 6) > 0 ? ceil($multi_int) : floor($multi_int);
            $curr_num = floor(bcdiv($max_weight, $sku_weight, 6));
            for ($y = 0; $y < $multi_int; $y++) {
                $k ++;
                $split_data[$k][$detail_id] = array('sell_record_detail_id' => $detail_id, 'num' => $curr_num, 'weight' => $sku_weight, 'sku' => $sku);
                $curr_weight = bcmul($sku_weight, $curr_num, 6);
                if (bccomp($max_weight, $curr_weight, 6) > 0) {
                    $no_fill_data[$k] = bcsub($max_weight, $curr_weight, 6);
                }
                $total_weight = bcsub($total_weight, $curr_weight, 6);
                $num -= $curr_num;
            }

            if (bccomp($total_weight, 0, 6) <> 0) {
                $curr_num = floor(bcdiv($total_weight, $sku_weight, 6));
                $k ++;
                $split_data[$k][$detail_id] = array('sell_record_detail_id' => $detail_id, 'num' => $num, 'weight' => $sku_weight, 'sku' => $sku);
                $no_fill_data[$k] = bcsub($max_weight, $total_weight, 6);
            }
        }

        if (!empty($split_data[0])) {
            $split_data[1] = array_merge($split_data[0], $split_data[1]);
            unset($split_data[0]);
        }
        if (count($split_data) == 1) {
            return $this->format_ret(-1, '', '无需拆分');
        }

        $split_arr = array();
        foreach ($split_data as $split_detail) {
            $split_arr[] = array(
                'data' => array_values($split_detail),
                'store_code' => $record['store_code'],
            );
        }

        $ret = $this->order_split_logic($split_arr, $record, $detail, 10);
        return $ret;
    }

    /**
     * 拆单入口方法(按数量/按重量)
     * @param array $param 参数 split_mode,split_value,sell_record_code
     * @return array 结果
     */
    public function split_order_process($param) {
        $record = load_model('oms/SellRecordModel')->get_record_by_code($param['sell_record_code']);
        if ($record['is_split'] == 1) {
            return $this->format_ret(-1, '', '订单已拆单');
        }
        if ($record['order_status'] > 1) {
            return $this->format_ret(-1, '', '订单已确认');
        }
        if ($record['shipping_status'] > 0) {
            return $this->format_ret(-1, '', '订单已通知配货');
        }
        $detail = load_model('oms/SellRecordModel')->get_detail_list_by_code($param['sell_record_code']);
        if (empty($detail)) {
            return $this->format_ret(-1, '', '订单明细为空');
        }

        $split_mode = $param['split_mode'];
        $ret = array();
        switch ($split_mode) {
            case 'weight':
                $ret = $this->split_order_by_weight($record, $detail, $param['split_value']);
                break;
            default:
                $ret = $this->format_ret(-1, '', '参数有误');
                break;
        }

        return $ret;
    }

}
