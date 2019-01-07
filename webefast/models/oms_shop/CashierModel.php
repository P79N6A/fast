<?php

require_model('tb/TbModel');

/**
 * 门店收银业务
 */
class CashierModel extends TbModel {

    /**
     * 扫描商品条码
     */
    public function scan_barcode($barcode, $shop_code) {
        try {
            if (empty($shop_code)) {
                throw new Exception('未绑定门店');
            }
            $sql = "SELECT sk.goods_code,sk.sku,gb.spec1_code,gb.spec2_code,sk.sku_price FROM base_shop_sku AS sk LEFT JOIN goods_barcode AS gb ON sk.sku=gb.sku WHERE gb.barcode = :barcode AND sk.shop_code=:shop_code AND sk.`status`=1";
            $goods = $this->db->get_row($sql, array(':barcode' => $barcode, ':shop_code' => $shop_code));
            $child_arr = array();
            if (empty($goods)) {
                $sql = "SELECT sk.goods_code,sk.sku,bc.spec1_code,bc.spec2_code,sk.sku_price FROM goods_barcode_child AS bc INNER JOIN base_shop_sku AS sk ON sk.sku=bc.sku WHERE bc.barcode = :barcode AND sk.shop_code=:shop_code AND sk.`status`=1";
                $child_arr = $this->db->get_all($sql, array(':barcode' => $barcode, ':shop_code' => $shop_code));
                if (!empty($child_arr)) {
                    if (count($child_arr) > 1) {
                        throw new Exception('存在重复的子条码,无法识别');
                    } else {
                        $goods = $child_arr[0];
                    }
                } else {
                    //识别条码识别规则
                    $barcode_rule_ret = load_model('prm/GoodsBarcodeIdentifyRuleModel')->yanzheng($barcode, 1);
                    if ($barcode_rule_ret['status'] > 0) {
                        $sql = "SELECT sku_price FROM base_shop_sku WHERE sku='{$barcode_rule_ret['data']['sku']}' AND shop_code=:shop_code AND `status`=1";
                        $ret_sku = $this->db->get_all($sql,array(':shop_code' => $shop_code));
                        if (!empty($ret_sku)) {
                            $goods = $barcode_rule_ret['data'];
                            $goods['sku_price'] = $ret_sku['sku_price'];
                        }
                    }
                    if ($barcode_rule_ret['status'] < 0 || empty($ret_sku)) {
                        //查询是否开启唯一码
                        $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status'));
                        if ($unique_arr['unique_status'] == 1) {
                            //开启唯一码查询:扫描条码是否 是唯一码
                            $sql = "SELECT go.sku,go.status,sk.sku_price FROM goods_unique_code AS go INNER JOIN base_shop_sku AS sk ON go.sku=sk.sku WHERE go.unique_code = :unique_code AND sk.shop_code=:shop_code AND sk.`status`=1";
                            $ret_unique = $this->db->get_row($sql, array(':unique_code' => $barcode, ':shop_code' => $shop_code));
                            if (empty($ret_unique)) {
                                throw new Exception('门店商品中不存在该条码或未启用');
                            } else if ($ret_unique['status'] == 1) {
                                throw new Exception('系统已开启唯一码，且唯一码为不可用状态！');
                            } else {
                                $goods = $ret_unique;
                            }
                        } else {
                            throw new Exception('门店商品中不存在该条码或未启用！');
                        }
                    }
                }
            }
            if (empty($goods)) {
                throw new Exception('条码无效');
            }
            //查询库存
            /* 强制出库，不校验库存
            $sql = 'SELECT gi.stock_num FROM goods_inv gi INNER JOIN base_shop bs ON gi.store_code=bs.send_store_code WHERE bs.shop_code=:shop_code AND gi.goods_code=:goods_code AND gi.sku=:sku';
            $sql_values = array(':shop_code' => $shop_code, ':goods_code' => $goods['goods_code'], ':sku' => $goods['sku']);
            $stock_num = $this->db->get_value($sql, $sql_values);
            if ($stock_num < 1) {
                throw new Exception('商品库存不足');
            }*/
            $sql = "SELECT goods_name,sell_price FROM base_goods WHERE goods_code='{$goods['goods_code']}'";
            $ret_goods = $this->db->get_row($sql);
            filter_fk_name($goods, array('spec1_code|spec1_code', 'spec2_code|spec2_code'));
            $goods = array_merge_recursive($goods, $ret_goods);
            $goods = get_array_vars($goods, array('goods_name', 'goods_code', 'spec1_code', 'spec2_code', 'spec1_code_name', 'spec2_code_name', 'sell_price', 'sku_price', 'sku'));
            $goods['spec1_name'] = $goods['spec1_code_name'];
            $goods['spec2_name'] = $goods['spec2_code_name'];
            unset($goods['spec1_code_name'], $goods['spec2_code_name']);
            $goods['rebate'] = 1;
            $goods['price'] = $goods['sku_price'];

            return $this->format_ret(1, $goods);
        } catch (Exception $e) {
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    //获取改款商品明细
    function get_change_goods($filter) {
        $sql_join .= "
                    INNER JOIN base_goods r2 ON r1.goods_code = r2.goods_code
                    INNER JOIN goods_sku r3 ON r1.sku = r3.sku
                    INNER JOIN goods_inv r4 ON r1.sku = r4.sku";
        $select = "SELECT r1.sku,r1.goods_code,r1.sku_price AS price,r2.sell_price,r4.stock_num,r4.lock_num,r2.goods_name,r3.barcode,r3.spec1_name,r3.spec2_name,r3.spec1_code,r4.spec2_code";
        $sql_main = "{$select} FROM base_shop_sku r1 {$sql_join} WHERE 1=1 ";
        $sql_values = array();

        if (CTX()->get_session('login_type') == 1 && !empty(CTX()->get_session('oms_shop_code'))) {
            $shop_code = CTX()->get_session('oms_shop_code');
            $sql_main .=" AND r1.shop_code = '{$shop_code}' AND r4.store_code='{$shop_code}'";
        }

        if (isset($filter['goods_multi']) && !empty($filter['goods_multi'])) {
            $sql_main .=' AND (r2.goods_code = :goods_code OR r2.goods_name = :goods_code OR r3.barcode = :goods_code)';
            $sql_values[':goods_code'] = $filter['goods_multi'];
        }
        $sql_main .= " GROUP BY r1.sku";
        $data = $this->db->get_all($sql_main, $sql_values);
        foreach ($data as &$value) {
            $value['available_num'] = (int) $value['stock_num'] - (int) $value['lock_num'];
            $value['available_num'] = ($value['available_num'] < 0) ? 0 : $value['available_num'];
            $value['rebate'] = 1;
            unset($value['lock_num'], $value['stock_num']);
        }
        return $data;
    }

    /**
     * 默认打印数据
     */
    public function print_data_default($request) {
        $record_code = $request['record_ids'];
        $r = array();
        $sql = "SELECT sr.record_code,sr.create_time,sr.payable_amount,sr.offline_shop_code AS shop_code,sr.cashier_code,rp.pay_code FROM oms_shop_sell_record sr LEFT JOIN oms_shop_sell_record_pay rp ON sr.record_code=rp.record_code WHERE sr.record_code = :record_code";
        $r['record'] = $this->db->get_row($sql, array('record_code' => $record_code));
        filter_fk_name($r['record'], array('shop_code|shop', 'pay_code|pay'));
        $r['record']['cashier_name'] = CTX()->get_session('user_name');
        $r['record']['print_time'] = date('Y-m-d H:i:s');
        $r['record']['create_date'] = substr($r['record']['create_time'], 0, 10);
        $spec = load_model('oms_shop/OmsShopModel')->get_spec_rename();
        $r['record']['goods_spec1'] = $spec['goods_spec1'];
        $r['record']['goods_spec2'] = $spec['goods_spec2'];
        //获取线下店铺的地址和联系方式
        $shop_sql = "SELECT * FROM base_shop WHERE shop_code = :shop_code";
        $shop_data = $this->db->get_row($shop_sql, array('shop_code' => $r['record']['shop_code']));
        $shop_address_ids[] = $shop_data['province'];
        $shop_address_ids[] = $shop_data['city'];
        $shop_address_ids[] = $shop_data['district'];
        $shop_address_ids[] = $shop_data['street'];
        //收货地址
        $shop_data_arr = array();
        $sql_values = array();
        $_new_receiver_ids =array_unique($shop_address_ids);
        $sql_str = $this->arr_to_in_sql_value($_new_receiver_ids, 'id', $sql_values);
        $_region_data_sql = "SELECT id AS region_id, name AS region_name FROM base_area WHERE id IN({$sql_str})";
        $_region_data = $this->db->get_all($_region_data_sql, $sql_values);
        foreach ($_region_data as $_region) {
            $shop_data_arr[$_region['region_id']] = $_region['region_name'];
        }
        $r['record']['shop_address'] = implode('', $shop_data_arr) . $shop_data['address'];
        $r['record']['shop_phone'] = !empty($shop_data['tel']) ? $shop_data['tel'] : $shop_data['phone'];
        $sql = "SELECT rd.goods_code,rd.num,rd.goods_amount,gk.spec1_name,gk.spec2_name
                FROM oms_shop_sell_record_detail rd LEFT JOIN goods_sku gk ON rd.sku=gk.sku
                WHERE rd.record_code=:record_code";
        $r['detail'] = $this->db->get_all($sql, array(':record_code' => $record_code));
        return $r;
    }

}
