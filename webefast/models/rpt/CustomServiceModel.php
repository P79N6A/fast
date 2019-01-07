<?php

require_lib('util/oms_util', true);
require_model('oms/SellRecordModel', true);

class CustomServiceModel extends TbModel {
    function get_by_page($filter,$type) {
        $sql_join = "";
        $sql_main = "FROM oms_sell_record AS sr {$sql_join} WHERE order_status != 3 AND order_status = 1 ";
        $sql_values = array();
        
        
        //订单状态
        if (isset($filter['status']) && $filter['status'] != '' && $filter['status'] != 'all') {
            if($filter['status'] == 0) {
                $sql_main .= " AND sr.shipping_status in (0,1,2,3) ";
            } else {
                $sql_main .= " AND sr.shipping_status = 4 ";
            }
        }
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
            $sql_values[':delivery_time_start'] = $filter['delivery_time_start'] . ' 00:00:00';
        }
        if (isset($filter['delivery_time_end']) && $filter['delivery_time_end'] != '') {
            $sql_main .= " AND (sr.delivery_time <= :delivery_time_end )";
            $sql_values[':delivery_time_end'] = $filter['delivery_time_end'] . ' 23:59:59';
        }
        //确认日期
        if (isset($filter['check_time_start']) && $filter['check_time_start'] != '') {
            $sql_main .= " AND (sr.check_time >= :check_time_start )";
            $sql_values[':check_time_start'] = $filter['check_time_start'] . ' 00:00:00';
        }
        if (isset($filter['check_time_end']) && $filter['check_time_end'] != '') {
            $sql_main .= " AND (sr.check_time <= :check_time_end )";
            $sql_values[':check_time_end'] = $filter['check_time_end'] . ' 23:59:59';
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
//   function get_return_info($get_return_info) {
//       $sql = "SELECT count(sell_return_code) AS count_sell_return,sum(recv_num) AS sum_return_num,sum(refund_total_fee) AS sum_return_money FROM oms_sell_return WHERE sell_record_code IN (SELECT sell_record_code FROM `oms_sell_record` WHERE `confirm_person` = '{$get_return_info}');";
//       $return = $this->db->get_row($sql);
//       return $return;
//   }
   
   function get_by_list_page($filter) {
       $data = $this->get_by_page($filter,'view');
       foreach ($data['data'] as &$val) {
           $val['user_code'] = oms_tb_val('sys_user', 'user_code', array('user_name' => $val['confirm_person']));
       }
       $ret_status = OP_SUCCESS;
       $ret_data = $data;
       return $this->format_ret($ret_status, $ret_data);
   }
//   function custom_service_count($filter) {
//       $data = $this->get_by_page($filter,'count');
//       $ret = array();
//       foreach ($data as $value) {
//           $ret['count_sell_record'] += $value['count_sell_record'];
//           $ret['sum_record_num'] += $value['sum_record_num'];
//       }
//   }
}
