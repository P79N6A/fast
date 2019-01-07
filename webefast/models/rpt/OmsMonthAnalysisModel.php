<?php

class OmsMonthAnalysisModel extends TbModel {

    protected $date_data;

    function __construct() {
        parent::__construct();
    }

    function get_sale_all($shop_code, $year_month) {
        $this->create_data($shop_code, $year_month);
        $sql = "select sum(sale_num) as sale_num ,sum(sale_money) as sale_money ,sum(sale_goods_num) as sale_goods_num,
                 sum(refund_num) as refund_num,sum(refund_goods_num) as refund_goods_num,sum(refund_money) as refund_money
                 from oms_sale_day where shop_code=:shop_code AND sale_date>=:start_date AND sale_date<=:end_date  ";
        $sql_value = array(':shop_code' => $shop_code, ':start_date' => $this->date_data['start_date'], ':end_date' => $this->date_data['end_date']);
        $data = $this->db->get_row($sql, $sql_value);
        return $this->format_ret(1, $data);
    }

    function get_sale_data($shop_code, $year_month) {
        $this->create_data($shop_code, $year_month);
        $sql = "select sale_num ,sale_money, refund_num, refund_money,sale_date
                 from oms_sale_day where shop_code=:shop_code AND sale_date>=:start_date AND sale_date<=:end_date  ";
        $sql_value = array(':shop_code' => $shop_code, ':start_date' => $this->date_data['start_date'], ':end_date' => $this->date_data['end_date']);
        $data = $this->db->get_all($sql, $sql_value);
        $month_data = $this->create_month_day($year_month);
        $sale_data = $sale_money_data = $refund_data = $refund_money_data = $month_data;

        foreach ($data as $val) {
            $day_arr = explode('-', $val['sale_date']);
            $day = (int) end($day_arr);

            $sale_data[$day] = (int) $val['sale_num'];
            $sale_money_data[$day] = (float) $val['sale_money'];
            $refund_data[$day] = (int) $val['refund_num'];
            $refund_money_data[$day] = (float) $val['refund_money'];
        }
        $month_arr = array_keys($month_data);
        $ret_data = array(
            'month_data' => $month_arr,
            'sale_data' => array_values($sale_data),
            'sale_money_data' => array_values($sale_money_data),
            'refund_data' => array_values($refund_data),
            'refund_money_data' => array_values($refund_money_data),
        );


        return $this->format_ret(1, $ret_data);
    }

    private function create_month_day($year_month) {

        list($year, $month) = explode("-", $year_month);
        $d = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $day = 1;
        $data = array();
        while ($day <= $d) {
            $data[$day] = 0;
            $day++;
        }
        return $data;
    }

    function get_sale_cat_data($shop_code, $year_month, $type = 0) {
        $this->create_data($shop_code, $year_month);

        if ($type == 0) {
            $sql = "select s.type_code,s.sale_num,s.sale_money,c.category_name as  type_name
                 from oms_sale_month_cat s
                 INNER JOIN base_category c ON s.type_code=c.category_code";
        } else {

            $sql = "select s.type_code,s.sale_num,s.sale_money,c.brand_name as  type_name
                 from oms_sale_month_cat s
                 INNER JOIN base_brand  c ON s.type_code=c.brand_code";
        }


        $sql .=" where s.shop_code=:shop_code AND  s.date_year_month=:year_month  AND s.type=:type  ";
        $sql_value = array(':shop_code' => $shop_code, ':year_month' => $year_month, ':type' => $type);
        $data = $this->db->get_all($sql, $sql_value);
        $all_money = 0;
        $all_num = 0;

        foreach ($data as &$val) {
            $all_money +=$val['sale_money'];
            $all_num +=$val['sale_num'];
        }

        $all_money_pr = 0;
        $all_num_pr = 0;
        $count = count($data) - 1;
        $ret_money = array();
        $ret_num = array();
        foreach ($data as $k => &$row) {
            if ($k < $count) {
                $num_p = bcdiv($row['sale_num'], $all_num, 4) * 100;
                $all_num_pr +=$num_p;
                $ret_num[] = array('name' => $row['type_name'], 'num' => $num_p);

                $money_p = bcdiv($row['sale_money'], $all_money, 4) * 100;
                $all_money_pr +=$money_p;
                $ret_money[] = array('name' => $row['type_name'], 'num' => $money_p);
            } else {
                $num_p = 100 - $all_num_pr;

                $ret_num[] = array('name' => $row['type_name'], 'num' => $num_p);
                $money_p = 100 - $all_money_pr;
                $ret_money[] = array('name' => $row['type_name'], 'num' => $money_p);
            }
        }

        $ret_data = array(
            'num' => $ret_num,
            'money' => $ret_money,
        );
        return $this->format_ret(1, $ret_data);
    }

    function get_sale_goods_data($shop_code, $year_month, $type = 0) {
        $this->create_data($shop_code, $year_month);
        $sql = "select s.barcode,b.goods_name,g.sku_value
                 from oms_sale_month_goods g
                 INNER JOIN goods_sku s ON g.sku=s.sku
                 INNER JOIN base_goods b ON b.goods_code=s.goods_code
                 where shop_code=:shop_code AND date_year_month=:year_month AND type=:type order by  CAST(g.sku_value as DECIMAL ) desc   ";
        $sql_value = array(':shop_code' => $shop_code, ':year_month' => $year_month, ':type' => $type);
        $data = $this->db->get_all($sql, $sql_value);
        return $this->format_ret(1, $data);
    }

    function create_data($shop_code, $year_month, $is_force = 0) {
        static $is_create = NULL;

        if (empty($is_create)) {
            $is_create = 1;
            $ret_check = $this->get_is_create_date($shop_code, $year_month, $is_force);
            if ($ret_check['status'] < 0) {
                return $ret_check;
            }
            $date_data = &$ret_check['data'];

            $this->create_oms_sale_day($shop_code, $date_data);
            $this->create_category($shop_code, $year_month, $date_data);
            $this->create_brand($shop_code, $year_month, $date_data);
            $this->create_goods($shop_code, $year_month, $date_data);
            $this->create_unsalable_goods($shop_code, $year_month, $date_data);
        }
        return $this->format_ret(1);
    }

    /*
     * 创建分类销售数据
     * 
     */

    function create_category($shop_code, $year_month, $date_data) {
        $date_data['start_date'] = $year_month . "-01";
        $sql = "select  sum(d.num) as sale_num,sum(d.avg_money) as sale_money,'{$year_month}' as date_year_month ,r.shop_code,0 as type,g.category_code as type_code  from oms_sell_record r
            INNER JOIN oms_sell_record_detail d ON r.sell_record_code=d.sell_record_code
            INNER JOIN base_goods g ON d.goods_code = g.goods_code
            where r.shop_code=:shop_code  AND   r.shipping_status=4 
             AND r.delivery_date>=:start_date
             AND r.delivery_date<=:end_date   GROUP BY g.category_code";
        $sql_value = array(':shop_code' => $shop_code, ':start_date' => $date_data['start_date'], ':end_date' => $date_data['end_date']);
        $data = $this->db->get_all($sql, $sql_value);
        if (!empty($data)) {
            $update_str = " sale_money = VALUES(sale_money),sale_num = VALUES(sale_num) ";
            $this->insert_multi_duplicate('oms_sale_month_cat', $data, $update_str);
        }
    }

    /*
     * 创建品牌销售数据
     * 
     */

    function create_brand($shop_code, $year_month, $date_data) {
        $date_data['start_date'] = $year_month . "-01";
        $sql = "select  sum(d.num) as sale_num,sum(d.avg_money) as sale_money,'{$year_month}' as date_year_month ,r.shop_code,1 as type,g.brand_code as type_code  from oms_sell_record r
            INNER JOIN oms_sell_record_detail d ON r.sell_record_code=d.sell_record_code
            INNER JOIN base_goods g ON d.goods_code = g.goods_code
            where r.shop_code=:shop_code  AND   r.shipping_status=4 
             AND r.delivery_date>=:start_date
             AND r.delivery_date<=:end_date   GROUP BY g.brand_code";
        $sql_value = array(':shop_code' => $shop_code, ':start_date' => $date_data['start_date'], ':end_date' => $date_data['end_date']);
        $data = $this->db->get_all($sql, $sql_value);
        if (!empty($data)) {
            $update_str = " sale_money = VALUES(sale_money),sale_num = VALUES(sale_num) ";
            $this->insert_multi_duplicate('oms_sale_month_cat', $data, $update_str);
        }
    }

    /*
     * 创建商品销售数据
     * 
     */

    function create_goods($shop_code, $year_month, $date_data) {
        //销售数量
        $date_data['start_date'] = $year_month . "-01";
        $sql = "select  sum(d.num) as sku_value,'{$year_month}' as date_year_month ,r.shop_code,0 as type,d.goods_code,d.sku  from oms_sell_record r
            INNER JOIN oms_sell_record_detail d ON r.sell_record_code=d.sell_record_code
             where r.shop_code=:shop_code   AND   r.shipping_status=4 
            AND r.delivery_date>=:start_date
            AND r.delivery_date<=:end_date  GROUP BY d.sku   order by sku_value desc limit 5";
        $sql_value = array(':shop_code' => $shop_code, ':start_date' => $date_data['start_date'], ':end_date' => $date_data['end_date']);
        $data = $this->db->get_all($sql, $sql_value);

        if (!empty($data)) {
            $update_str = " sku_value = VALUES(sku_value) ";
            $this->insert_multi_duplicate('oms_sale_month_goods', $data, $update_str);
        }
        //销售金额
        $sql_m = "select  sum(d.avg_money) as sku_value,'{$year_month}' as date_year_month ,r.shop_code,1 as type,d.goods_code,d.sku  from oms_sell_record r
            INNER JOIN oms_sell_record_detail d ON r.sell_record_code=d.sell_record_code
             where r.shop_code=:shop_code   AND   r.shipping_status=4 
            AND r.delivery_date>=:start_date
            AND r.delivery_date<=:end_date  GROUP BY d.sku   order by sku_value desc limit 5";
        // $sql_value = array(':shop_code' => $shop_code, ':start_date' => $date_data['start_date'], ':end_date' => $date_data['end_date']);
        $data_m = $this->db->get_all($sql_m, $sql_value);

        if (!empty($data_m)) {
            $update_str = " sku_value = VALUES(sku_value) ";
            $this->insert_multi_duplicate('oms_sale_month_goods', $data_m, $update_str);
        }
    }
    /*
    * 创建滞销商品数据(新sjs)
    *
    */

    function create_unsalable_goods($shop_code, $year_month, $date_data){
        $filter = array('shop_code'=>$shop_code,'year_month'=>$year_month);
        $sql_values = array();
        $sql_main = '';
        load_model('oms/SellRecordModel')->get_unsalable_sql($filter,1,$sql_values,$sql_main);
        //清除数据
        $sql_del = "delete from oms_sale_month_goods where shop_code=:shop_code AND  date_year_month=:date_year_month and type=:type";
        $this->db->query($sql_del,array(':shop_code'=>$shop_code,':type'=>2,':date_year_month'=>$year_month));
        $sql = 'select
                    sum(gi.stock_num) as sku_value,
                    gs.sku,
                    gs.goods_code,
                    "'.$year_month.'" as date_year_month ,
                    g.shop_code,
                    2 as type,
                    gs.barcode
              '.$sql_main.' limit 5 ';
        $data = $this->db->get_all($sql,$sql_values);
        if (!empty($data)) {
            $update_str = " sku_value = VALUES(sku_value) ";
            $this->insert_multi_duplicate('oms_sale_month_goods', $data, $update_str);
        }

    }

    /*
     * 创建滞销商品数据
     * 
     */

    function create_unsalable_goods_old($shop_code, $year_month, $date_data) {
        $sql = "select   d.sku from oms_sell_record r
            INNER JOIN oms_sell_record_detail d ON r.sell_record_code=d.sell_record_code
            where r.shop_code=:shop_code  AND   r.shipping_status=4 
             AND r.delivery_date>=:start_date
             AND r.delivery_date<=:end_date  GROUP BY sku";
        $sql_value = array(':shop_code' => $shop_code, ':start_date' => $date_data['start_date'], ':end_date' => $date_data['end_date']);
        $data = $this->db->get_all($sql, $sql_value);
        $sku_arr = array();
        if (!empty($data)) {
            foreach ($data as $val) {
                $sku_arr[] = $val['sku'];
            }
        }
        if (empty($sku_arr)) {
            return $this->format_ret(1);
        }

        $now_year_month = date('Y-m');
        list($now_year, $now_month) = explode("-", $now_year_month);
        list($year, $month) = explode("-", $year_month);
        $sku_str = "'" . implode("','", $sku_arr) . "'";

        $sql_shop = "select send_store_code,stock_source_store_code from base_shop where shop_code=:shop_code";

        $shop_row = $this->db->get_row($sql_shop, array(':shop_code' => $shop_code));
        $store_arr = array();
        if (!empty($shop_row['stock_source_store_code'])) {
            $store_arr = explode(",", $shop_row['stock_source_store_code']);
        } else {
            $store_arr[] = $shop_row['send_store_code'];
        }
        $store_str = "'" . implode("','", $store_arr) . "'";

        //清除数据
        $sql_del = "delete from oms_sale_month_goods where shop_code=:shop_code AND  date_year_month=:date_year_month and type=:type";
        $sql_del_value = array(':shop_code' => $shop_code, ':date_year_month' => $year_month, ':type' => 2);
        $this->db->query($sql_del, $sql_del_value);


        $sql = "SELECT  sum(gi.stock_num) as sku_value,gs.sku,gs.goods_code,'{$year_month}' as date_year_month ,g.shop_code,2 as type
                    from api_goods  g
                    INNER JOIN api_goods_sku s ON g.goods_from_id=s.goods_from_id
                    INNER JOIN goods_sku gs ON gs.barcode=s.goods_barcode
                    INNER JOIN goods_inv gi ON gs.sku=gi.sku
                    where g.status=1 AND gs.sku  not in({$sku_str})
                    AND gi.store_code in ({$store_str}) GROUP BY gi.sku order by sku_value desc limit 5";
        $data_goods = $this->db->get_all($sql);
        if (!empty($data_goods)) {
            $update_str = " sku_value = VALUES(sku_value) ";
            $this->insert_multi_duplicate('oms_sale_month_goods', $data_goods, $update_str);
        }
    }

    /*
     * 创建销售数据
     * 
     */

    function create_oms_sale_day($shop_code, $date_data) {

        //  $date_data = &$ret_check['data'];

        $sql = "SELECT sum(r.payable_money) as sale_money,sum(r.goods_num) as sale_goods_num,count(1) as sale_num ,delivery_date as sale_date
            from oms_sell_record r
            where r.shop_code=:shop_code AND  r.shipping_status=4  AND r.delivery_date>=:start_date  AND r.delivery_date<=:end_date   GROUP BY r.delivery_date  ";
        $sql_value = array(':shop_code' => $shop_code, ':start_date' => $date_data['start_date'], ':end_date' => $date_data['end_date']);
        $data = $this->db->get_all($sql, $sql_value);
        $sale_data = array();
        $refund_row = array('refund_money' => 0, 'refund_goods_num' => 0, 'refund_num' => 0, 'shop_code' => $shop_code);
        foreach ($data as $val) {
            $sale_data[$val['sale_date']] = array_merge($val, $refund_row);
        }

        $sql_r = "SELECT sum(d.recv_num) as refund_goods_num,r.stock_date as sale_date
                    from oms_sell_return r  LEFT JOIN oms_sell_return_detail d ON r.sell_return_code=d.sell_return_code
                    where r.shop_code=:shop_code AND  ( r.relation_shipping_status=4  OR r.relation_shipping_status=2 )  AND  r.return_order_status=1        AND r.stock_date>=:start_date  AND r.stock_date<=:end_date  GROUP BY r.stock_date  ";
        $sql_r_value = array(':shop_code' => $shop_code, ':start_date' => $date_data['start_date'], ':end_date' => $date_data['end_date']);
        $refund_data_num = $this->db->get_all($sql_r, $sql_r_value);

        $sale_row = array('refund_money' => 0, 'refund_goods_num' => 0, 'refund_num' => 0, 'sale_money' => 0, 'sale_goods_num' => 0, 'sale_num' => 0, 'shop_code' => $shop_code);
        foreach ($refund_data_num as $val) {
            if (isset($sale_data[$val['sale_date']])) {
                $sale_data[$val['sale_date']] = array_merge($sale_data[$val['sale_date']], $val);
            } else {
                $sale_data[$val['sale_date']] = array_merge($sale_row, $val);
            }
        }
        $sql_r2 = "SELECT sum(r.refund_total_fee) as refund_money,count(1) as refund_num ,r.stock_date as sale_date
                    from oms_sell_return r  
                    where r.shop_code=:shop_code AND  ( r.relation_shipping_status=4  OR r.relation_shipping_status=2 )  AND  r.return_order_status=1        AND r.stock_date>=:start_date  AND r.stock_date<=:end_date  GROUP BY r.stock_date  ";
        //$sql_r_value = array(':shop_code' => $shop_code, ':start_date' => $date_data['start_date'], ':end_date' => $date_data['end_date']);
        $refund_data = $this->db->get_all($sql_r2, $sql_r_value);
        foreach ($refund_data as $val) {
            if (isset($sale_data[$val['sale_date']])) {
                $sale_data[$val['sale_date']] = array_merge($sale_data[$val['sale_date']], $val);
            } else {
                $sale_data[$val['sale_date']] = array_merge($sale_row, $val);
            }
        }



        $update_str = " sale_money = VALUES(sale_money),sale_goods_num = VALUES(sale_goods_num),sale_num = VALUES(sale_num),";
        $update_str .= " refund_money = VALUES(refund_money),refund_goods_num = VALUES(refund_goods_num),refund_num = VALUES(refund_num)";
        $this->insert_multi_duplicate('oms_sale_day', $sale_data, $update_str);
    }

    private function get_is_create_date($shop_code, $year_month, $is_force = 0) {
        list($year, $month) = explode("-", $year_month);
        $d = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $now_year_month = (int) date('Y-m');
        list($now_year, $now_month) = explode("-", $now_year_month);
        $this->date_data = array(
            'start_date' => $year_month . "-01",
            'end_date' => $year_month . "-{$d}",
        );
        $date_data = $this->date_data;
        $ret_status = 1;
        if ($is_force == 1) {//强制重新生成
            return $this->format_ret($ret_status, $date_data);
        }



        if ($year == $now_year && (int) $month == (int) $now_month) {//当月
            $sale_date = $year_month . "-01";
            $sql = "select max(sale_date) from oms_sale_day "
                    . "where shop_code=:shop_code  AND sale_date>=:sale_date";
            $sql_value = array(':shop_code' => $shop_code, ':sale_date' => $sale_date);
            $max_day = $this->db->get_value($sql, $sql_value);
            if (!empty($max_day)) {
                $date_data['start_date'] = $max_day;
            }
            $date_data['end_date'] = date('Y-m-d');
        } else {
            $sql = "select sale_date,lastchanged from oms_sale_day "
                    . "where shop_code=:shop_code  AND sale_date>=:start_date AND sale_date<=:end_date order by sale_date desc";
            $sql_value = array(':shop_code' => $shop_code, ':start_date' => $date_data['start_date'], ':end_date' => $date_data['end_date']);
            $data = $this->db->get_row($sql, $sql_value);
            if (!empty($data)) {
                $create_time = strtotime($data['lastchanged']);
                $end_date_time = strtotime($date_data['end_date'] . " 23:59:59") + 1;
                //创建时间大于业务时间
                if ($data['sale_date'] == $date_data['end_date'] && $create_time > $end_date_time) {
                    $ret_status = -1;
                } else {
                    $date_data['start_date'] = $data['sale_date']; //当天日期
                }
            }
            return $this->format_ret($ret_status, $date_data);
        }
    }

}
