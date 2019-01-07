<?php

require_lib('util/oms_util', true);
require_model('oms/SellRecordModel', true);

class GoodsPerformanceModel extends TbModel {
    function get_by_page($filter,$type) {
        $sql_join = "";
        $sql_main = "FROM oms_sell_record AS sr {$sql_join} WHERE shipping_status = 4 ";
        $sql_values = array();
        
        //店铺
        if($filter['shop_code'] != '' && isset($filter['shop_code'])){
            $sql_main .= "AND sr.shop_code = :shop_code ";
            $sql_values['shop_code'] = $filter['shop_code'];
        }
        //平台
        if($filter['sale_channel_code'] != '' && isset($filter['sale_channel_code'])){
            $sql_main .= "AND sr.sale_channel_code = :sale_channel_code ";
            $sql_values['sale_channel_code'] = $filter['sale_channel_code'];
        }
        //发货日期
        if (isset($filter['delivery_time_start']) && $filter['delivery_time_start'] != '') {
            $sql_main .= " AND (sr.delivery_time >= :delivery_time_start )";
            $sql_values[':delivery_time_start'] = $filter['delivery_time_start'];
        }
        if (isset($filter['delivery_time_end']) && $filter['delivery_time_end'] != '') {
            $sql_main .= " AND (sr.delivery_time <= :delivery_time_end )";
            $sql_values[':delivery_time_end'] = $filter['delivery_time_end'];
        }
        
        $select = "sr.confirm_person,count(sr.sell_record_code) AS count_sell_record,sum(sr.goods_num) AS sum_record_num,sum(sr.payable_money) AS sum_record_money";
        $group = "GROUP BY sr.confirm_person ";
        $sql_main .= $group;
        $sql_main .= "ORDER BY count_sell_record DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as $key => &$val) {
            //客单价
            $unit_price = $val['sum_record_money'] / $val['count_sell_record'];
            $val['unit_price'] = sprintf("%.2f",$unit_price);
            //连带率
            $related = $val['sum_record_num'] / $val['count_sell_record'];
            $val['related'] = sprintf("%.2f",$related);
            $val['sum_record_money'] = (float)$val['sum_record_money'];
            $val['count_sell_record'] = (int)$val['count_sell_record'];
            
        }
        if($type == 'view') {
            return $data;
        }
    }
   
    function get_by_list_page($filter) {
        // 已发货订单数据
        $sql_main = "FROM `oms_sell_record` WHERE 1 AND `shipping_status` = '4'";
        $sql_values = array();
        /** 条件筛选 */
        // 仓库
        if (isset($filter['store_code'])) {
            if (empty($filter['store_code'])) {
                return $this->format_ret('-1', array());
            }
            $sql_main .= " AND `store_code` = :store_code ";
            $sql_values[':store_code'] = $filter['store_code'];
        }
        // 月份
        if (isset($filter['delivery_time_start']) && !empty($filter['delivery_time_start'])) {
            $sql_main .= "AND `delivery_time` >= :delivery_time_start ";
            $sql_values[':delivery_time_start'] = $filter['delivery_time_start'];
        }
        if (isset($filter['delivery_time_end']) && !empty($filter['delivery_time_end'])) {
            $sql_main .= "AND `delivery_time` <= :delivery_time_end ";
            $sql_values[':delivery_time_end'] = $filter['delivery_time_end'];
        } else {
            $sql_main .= "AND `delivery_time` <= :delivery_time_end ";
            $sql_values[':delivery_time_end'] = date('Y-m-d H:i:s');
        }
        
        $select = '`delivery_person`, COUNT(*) AS `order_num`, SUM(`goods_num`) AS `product_num`,sum(if(goods_num=1,1,0)) sin_record_num,sum(if(goods_num>1,1,0)) mul_record_num';
        $sql_main .= ' AND `delivery_person` <> "" GROUP BY `delivery_person` ';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as &$row) {
            $user = load_model('sys/UserModel')->get_row(array('user_code' => $row['delivery_person']));
            $row['user_id']         = $row['delivery_person'];
            $row['delivery_person'] = $user['data']['user_name'];
        }
        unset($row);
        usort($data['data'], function ($a, $b){
            if ($a['order_num'] != $b['order_num']) {
                return $a['order_num'] < $b['order_num'];
            } else {
                return $a['product_num'] < $b['product_num'];
            }
        });
        return $this->format_ret(OP_SUCCESS, $data);
    }
}
