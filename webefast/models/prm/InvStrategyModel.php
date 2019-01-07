<?php

require_model('tb/TbModel');
require_lang('inv');

class InvStrategyModel extends TbModel {

    var $barcode_trade_arr = array();
    var $log_data = array();

    function get_shop_sku_inv($shop_code, array $barcode_arr, $is_check_combo_diy = false) {

        $this->log_data = array();
        //获取商品对应发货仓库
        //查询库存
        // 实物库存 - 锁定库存- 缺货库存  小于0等于0

        $sync_prec = $this->get_shop_sync_prec($shop_code);

        $ret_store = $this->get_sync_store($shop_code);
        if ($ret_store['status'] < 1) {
            return $ret_store;
        }
        $store_arr = &$ret_store['data'];
        $data = $this->get_sku_inv($store_arr, $barcode_arr);

        $barcode_inv = array();
        $shop_arr = $this->get_store_shop($store_arr, $shop_code);
        $trade_barcode = $this->get_trade_num($shop_arr, $barcode_arr);
        //若是淘宝店铺，下单减库存的商品还要减掉未付款数
        //$nopay_barcode = $this->get_taobao_nopay_num($shop_code,$barcode_arr);
        $barcode_arr_key = array_flip($barcode_arr);

        foreach ($data as $val) {
            if (is_null($val['stock_num'])) {
                $num = 0;
            } else {
                $num = $val['stock_num'] - $val['lock_num'] - $val['out_num'];
            }
            $num = ($num < 0) ? 0 : $num;

            /*
              if(!empty($val['stock_num'])){
              $num = $val['stock_num'] - $val['lock_num'] - $val['out_num'];
              }
             */
            if (isset($trade_barcode[$val['barcode']])) {
                $num = $num - $trade_barcode[$val['barcode']];
            }
            /*
              if(isset($nopay_barcode[$val['barcode']])){
              $num = $num-$nopay_barcode[$val['barcode']];
              } */
            $num = floor($num * $sync_prec);

            if ($num < 0) {
                $num = 0;
            }
            $barcode_inv[$val['barcode']] = array('sku' => $val['sku'], 'barcode' => $val['barcode'], 'num' => $num, 'inv_update_time' => $val['record_time']);
            $this->set_log_data($barcode_inv[$val['barcode']], $shop_code, $store_arr);
            unset($barcode_arr_key[$val['barcode']]);
        }

        $this->save_log();

        if ($is_check_combo_diy === true) {
            $new_barcode = array_keys($barcode_arr_key);
            $combo_diy_barcode = $this->get_combo_diy_barcode($new_barcode);
            if (!empty($combo_diy_barcode)) {
                $ret_combo_diy = $this->get_combo_diy_inv($shop_code, $combo_diy_barcode);
                if (!empty($ret_combo_diy['data'])) {
                    $barcode_inv = array_merge($barcode_inv, $ret_combo_diy['data']);
                }
            }
        }

        // 找不到暂时不处理
//        foreach ($barcode_arr as $barcode) {
//            if (!isset($barcode_inv[$barcode])) {
//                $barcode_inv[$barcode] = array('barcode' => $barcode, 'num' => 0,'sku'=>'','inv_update_time'=>'');
//            }
//        }

        return $this->format_ret(1, $barcode_inv);
    }

    /**
     * @todo 获取SKU库存数据
     */
    private function get_sku_inv($store_arr, $barcode_arr) {
        $sku_data = load_model('prm/SkuModel')->convert_barcode($barcode_arr);
        $sku_data = $sku_data['data'];
        $sku_arr = array_unique(array_column($sku_data, 'sku'));
        if (empty($sku_arr)) {
            return array();
        }

        $sql_values = array();
        $store_code_str = $this->arr_to_in_sql_value($store_arr, 'store_code', $sql_values);
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'barcode', $sql_values);
        $sql = "SELECT SUM( if((i.stock_num<i.safe_num),0, i.stock_num-i.safe_num)) AS stock_num ,SUM(i.lock_num) AS lock_num , SUM(i.out_num) AS out_num  ,b.barcode, b.sku,MAX(i.record_time) AS record_time
        FROM goods_sku AS b LEFT JOIN goods_inv AS i ON i.sku=b.sku
        WHERE i.store_code IN({$store_code_str}) AND b.sku IN({$sku_str}) GROUP BY b.sku";

        $data = $this->db->get_all($sql, $sql_values);
        $data = load_model('util/ViewUtilModel')->get_map_arr($data, 'sku');
        $inv_data = array();
        foreach ($sku_data as $key => $val) {
            if (isset($data[$val['sku']])) {
                $temp = $data[$val['sku']];
                $temp['barcode'] = $key;
                $inv_data[] = $temp;
            }
        }
        return $inv_data;
    }

    function get_combo_diy_barcode($barcode_arr) {
        $barcodes = "'" . implode("','", $barcode_arr) . "'";
        $sql = " select barcode from  goods_combo_barcode where barcode in($barcodes); ";
        $data = $this->db->get_all($sql);
        $combo_diy_barcode = array();
        foreach ($data as $val) {
            $combo_diy_barcode[] = $val['barcode'];
        }
        return $combo_diy_barcode;
    }

    function get_trade_num($shop_arr, $barcode_arr) {

        $shop_str = "'" . implode("','", $shop_arr) . "'";
        $barcodes = "'" . implode("','", $barcode_arr) . "'";

        $data = $this->db->get_all("select sum(d.num) as num, d.goods_barcode from api_order o
            inner join api_order_detail d ON o.tid = d.tid
            where o.status =1 and (o.is_change=0 OR o.is_change=-1)  and  d.goods_barcode in ({$barcodes}) and o.shop_code in($shop_str) group by d.goods_barcode ");
        $barcode_num = array();
        foreach ($data as $val) {
            $barcode_num[$val['goods_barcode']] = $val['num'];
        }
        return $barcode_num;
    }

    function get_taobao_nopay_num($shop_code, $barcode_arr) {
        $barcode_num = array();
        $ret_shop = load_model("base/ShopModel")->get_by_code($shop_code);
        if ($ret_shop['data']['sale_channel_code'] != 'taobao') {
            return $barcode_num;
        }
        //获取下单付款的商品
        $barcodes = "'" . implode("','", $barcode_arr) . "'";
        $sql = "select goods_barcode from api_goods g,api_goods_sku s where g.goods_from_id=s.goods_from_id and g.stock_type=1 and s.goods_barcode in({$barcodes})";
        $stock_barcodes_arr = $this->db->get_all_col($sql);
        if (empty($stock_barcodes_arr)) {
            return $barcode_num;
        }
        $stock_barcodes = "'" . implode("','", $stock_barcodes_arr) . "'";
        //获取未付款的订单号
        $sql = "select tid from api_taobao_trade where status = 'WAIT_BUYER_PAY'";
        $tid_arr = $this->db->get_all_col($sql);
        if (empty($tid_arr)) {
            return $barcode_num;
        }
        //计算未付款的订单数
        $tids = "'" . implode("','", $tid_arr) . "'";
        $sql = "select sum(d.num) as num, d.goods_barcode from api_order o
            inner join api_order_detail d ON o.tid = d.tid
            where o.is_change=0 and  d.goods_barcode in ({$stock_barcodes}) and o.tid in({$tids}) and o.shop_code='{$shop_code}' group by d.goods_barcode ";
        $data = $this->db->get_all($sql);
        foreach ($data as $val) {
            $barcode_num[$val['goods_barcode']] = $val['num'];
        }
        return $barcode_num;
    }

    function get_store_shop($store_code_arr, $shop_code) {
        $shop_arr = array();
        $shop_arr[$shop_code] = isset($shop_code) ? $shop_code : '';
        foreach ($store_code_arr as $store_code) {
            $shop_data = $this->db->get_all("select shop_code,stock_source_store_code from base_shop where shop_code<>:shop_code and stock_source_store_code like :store_code", array(':shop_code' => $shop_code, ':store_code' => '%' . $store_code . '%'));
            foreach ($shop_data as $val) {
                $store_data = explode(',', $val['stock_source_store_code']);
                if (in_array($store_code, $store_data)) {
                    $shop_arr[$val['shop_code']] = $val['shop_code'];
                }
            }
        }
        return $shop_arr;
    }

    //获取百分比
    function get_shop_sync_prec($shop_code) {
        $sql = "select value from sys_kc_sync_cfg where shop_code = :shop_code";
        $sync_prec = ctx()->db->getOne($sql, array(':shop_code' => $shop_code));
        if (empty($sync_prec)) {
            $sync_prec = 1;
        } else {
            $sync_prec = (int) $sync_prec < 0 ? 1 : $sync_prec / 100;
        }
        return $sync_prec;
    }

    function get_sync_store($shop_code) {
        $ret_shop = load_model("base/ShopModel")->get_by_code($shop_code);
        if ($ret_shop['status'] < 1) {
            return $this->format_ret(-1, array(), '仓库信息不存在');
        }
        //$store_code = $ret_shop['data']['send_store_code'];
        $stock_source_store_code = $ret_shop['data']['stock_source_store_code'];
        $send_store_code = $ret_shop['data']['send_store_code'];


        if (empty($stock_source_store_code)) {
            $stock_source_store_code = $send_store_code;
        }

        $store_code_arr = explode(',', $stock_source_store_code);

        if (!in_array($send_store_code, $store_code_arr)) {
            $store_code_arr[] = $send_store_code;
        }

        $store_code_str = "'" . implode("','", $store_code_arr) . "'";

        $num = $this->db->getOne("select count(1) from base_store where store_code in({$store_code_str})");
        if ($num < 1) {
            return $this->format_ret(-1, array(), '没有设置库存同步的仓库');
        }

        return $this->format_ret(1, $store_code_arr);
    }

    //获取组合商品库存
    function get_combo_diy_inv($shop_code, array $barcode_arr) {
        $this->log_data = array();
        $sync_prec = $this->get_shop_sync_prec($shop_code);
        $ret_store = $this->get_sync_store($shop_code);
        if ($ret_store['status'] < 1) {
            return $ret_store;
        }

        $store_arr = &$ret_store['data'];
        $barcode_inv_data = array();

        $shop_arr = $this->get_store_shop($store_arr, $shop_code);
        $trade_barcode = $this->get_trade_num($shop_arr, $barcode_arr);
        //若是淘宝店铺，下单减库存的商品还要减掉未付款数
        //$nopay_barcode = $this->get_taobao_nopay_num($shop_code,$barcode_arr);
        foreach ($barcode_arr as $barcode) {
            $barcode_inv = array();
            foreach ($store_arr as $store_code) {
                $new_barcode_inv = $this->get_combo_diy_num($store_code, $barcode);
                if (!empty($new_barcode_inv)) {
                    $this->merge_combo_diy_inv($barcode_inv, $new_barcode_inv);
                }
            }

            if (empty($barcode_inv)) { //找不到不同步
                continue;
            }
            if (isset($trade_barcode[$barcode])) {
                $barcode_inv['num'] = $barcode_inv['num'] - $trade_barcode[$barcode];
            }
            /*
              if(isset($nopay_barcode[$barcode])){
              $barcode_inv['num'] = $barcode_inv['num']-$nopay_barcode[$barcode];
              } */

            $barcode_inv['num'] = floor($barcode_inv['num'] * $sync_prec);

            if ($barcode_inv['num'] < 0) {
                $barcode_inv['num'] = 0;
            }
            $this->set_log_data($barcode_inv, $shop_code, $store_arr);
            $barcode_inv_data[$barcode] = $barcode_inv;
        }
        $this->save_log();
        return $this->format_ret(1, $barcode_inv_data);
    }

    private function merge_combo_diy_inv(&$barcode_inv, $new_barcode_inv) {

        if (!empty($barcode_inv)) {
            $barcode_inv['num'] += $new_barcode_inv['num'];
            $old_record_time = strtotime($barcode_inv['inv_update_time']);
            $record_time = strtotime($new_barcode_inv['inv_update_time']);
            if ($old_record_time < $record_time) {
                $barcode_inv['record_time'] = $record_time;
            }
        } else {
            $barcode_inv = $new_barcode_inv;
        }
    }

    function get_combo_diy_num($store_code, $barcode) {
        static $combo_diy_inv_data = null;

        if (isset($combo_diy_inv_data[$store_code][$barcode])) {
            return $combo_diy_inv_data[$store_code][$barcode];
        }

        $sql = "select d.num,d.sku from goods_combo_diy d
                INNER JOIN   goods_combo_barcode b ON b.sku=d.p_sku
                WHERE b.barcode = :barcode ";
        $sql_value = array(':barcode' => $barcode);
        $data = $this->db->get_all($sql, $sql_value);
        $sku_arr = array();
        $combo_diy_arr = array();

        foreach ($data as $val) {
            $combo_diy_arr[$val['sku']] = $val['num'];
            $sku_arr[] = $val['sku'];
        }
        $sku_str = "( sku='" . implode("' OR sku='", $sku_arr) . "' )";
        $sql = "select stock_num,lock_num,out_num,safe_num,record_time,sku from goods_inv where store_code='{$store_code}' AND {$sku_str}  ";
        $data = $this->db->get_all($sql);
        //$inv_sku_num = count($data);
        // $diy_num = 0;
        $barcode_data = array();
        foreach ($data as $val) {
            $num = $val['stock_num'] - $val['lock_num'] - $val['out_num'] - $val['safe_num'];
            if ($num > 0) {
                $num = floor($num / $combo_diy_arr[$val['sku']]);
            }
            unset($combo_diy_arr[$val['sku']]);
            if (!empty($barcode_data)) {
                if ($barcode_data['num'] > $num) {
                    $barcode_data['num'] = $num;
                }
                $old_record_time = strtotime($barcode_data['inv_update_time']);
                $record_time = strtotime($val['record_time']);
                if ($old_record_time < $record_time) {
                    $barcode_data['inv_update_time'] = $val['record_time'];
                }
            } else {
                $barcode_data = array('sku' => $val['sku'], 'barcode' => $barcode, 'num' => $num, 'inv_update_time' => $val['record_time']);
            }
        }

        //存在没有的SKU
        if (!empty($combo_diy_arr)) {
            return array();
        }
        $combo_diy_inv_data[$store_code][$barcode] = $barcode_data;
        return $barcode_data;
    }

    function save_log() {
        if (!empty($this->log_data)) {
            load_model('prm/GoodsInvApiSyncLogModel')->save_log($this->log_data);
        }
    }

    function set_log_data($barcode_data, $shop_code, $store_code = '') {
        if ($barcode_data['num'] < 1) {
            $barcode_data['shop_code'] = $shop_code;

            if (is_array($store_code)) {
                $store_code = implode(",", $store_code);
            }

            $barcode_data['store_code'] = $store_code;
            $this->log_data[] = $barcode_data;
        }
    }

}
