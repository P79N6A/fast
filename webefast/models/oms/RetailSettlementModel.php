<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

class RetailSettlementModel extends TbModel {

    protected $table = "oms_sell_settlement_record";

    function get_list_by_page($filter) {
        $sql_values = array();
        $sql_main = '';

        //过滤店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('shop_code', $filter_shop_code);

        //销售平台
        if (isset($filter['sale_channel']) && !empty($filter['sale_channel'])) {
            $arr = explode(',',$filter['sale_channel']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND sale_channel_code in ({$str})";
        }
        //账期
        if (isset($filter['zq_month']) && !empty($filter['zq_month'])) {
            $sql_main .= " AND create_time > :create_time_start AND create_time < :create_time_end ";
            $create_time_start = date('Y-m-01', strtotime($filter['zq_month'])) . " 00:00:00";
            $create_time_end = date('Y-m-d', strtotime("$create_time_start +1 month -1 day")) . " 23:59:59";
            $sql_values[':create_time_start'] = $create_time_start;
            $sql_values[':create_time_end'] = $create_time_end;
        }
        $select = ' sale_channel_code,shop_code,SUM(sell_je) as order_money,SUM(return_je) as return_money,(SUM(sell_je) - SUM(return_je)) AS total_money ';
        $sql = " FROM (
                        (SELECT sale_channel_code,shop_code,je as sell_je,0 as return_je
                            FROM oms_sell_settlement_record where order_attr=1 {$sql_main})  UNION ALL
                        (SELECT sale_channel_code,shop_code,0 as sell_je ,je as return_je
                            FROM oms_sell_settlement_record where order_attr=2 {$sql_main}) 
                     ) as tmp
                 GROUP BY shop_code ";



        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select,TRUE);
        //得到邮费汇总和补差汇总
        $sql2="SELECT sale_channel_code,shop_code,SUM(sell_je) as order_money,SUM(return_je) as return_money,(SUM(sell_je) - SUM(return_je)) AS total_money  FROM (
                        (SELECT sale_channel_code,shop_code,je as sell_je,0 as return_je
                            FROM oms_sell_settlement_record where order_attr=1 and settle_type=2 {$sql_main})  UNION ALL
                        (SELECT sale_channel_code,shop_code,0 as sell_je ,je as return_je
                            FROM oms_sell_settlement_record where order_attr=2 and settle_type=3 {$sql_main})
                     ) as tmp
                 GROUP BY shop_code ";
        // $data2 = $this->get_page_from_sql($filter, $sql2, $sql_values, $select,TRUE);
        $data2 = $this->db->get_all($sql2, $sql_values);

        foreach($data2 as $k=>&$sub_data2 ){
            $new_arr[$sub_data2['sale_channel_code'] . $sub_data2['shop_code']] = $sub_data2;
        }

        foreach ($data['data'] as &$sub_data) {
            $sub_data['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $sub_data['shop_code']));
            $sub_data['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $sub_data['sale_channel_code']));

            $sub_data['postage_total'] = $new_arr[$sub_data['sale_channel_code'] . $sub_data['shop_code']]['order_money'];
            $sub_data['other_total'] = $new_arr[$sub_data['sale_channel_code'] . $sub_data['shop_code']]['return_money'];
            $sub_data['postage_total']=empty($sub_data['postage_total']) ? sprintf("%.3f",0) : $sub_data['postage_total'];
            $sub_data['other_total']=empty($sub_data['other_total']) ? sprintf("%.3f",0) : $sub_data['other_total'];
            $sub_data['order_money']=$sub_data['order_money'].'('.$sub_data['postage_total'].')';
            $sub_data['return_money']=$sub_data['return_money'].'('.$sub_data['other_total'].')';
        }

        return $this->format_ret(1, $data);
    }

}


