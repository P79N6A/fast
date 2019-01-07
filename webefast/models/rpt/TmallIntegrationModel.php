<?php
require_lib('util/oms_util', true);
require_model('base/ShopModel', true);

class TmallIntegrationModel extends TbModel{

    function get_table(){
        return 'api_taobao_trade';
    }


    /**
     * 组装条件查询数据
     * @param $filter
     * @param $onlySql
     * @return array
     */
    function get_by_page($filter, $onlySql = false){
        $sql_values = array();
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl WHERE 1 AND status='TRADE_FINISHED' AND real_point_fee>0 ";
        //天猫店铺权限
        $tianmao_shop_code = load_model('base/ShopModel')->get_purview_shop_tianmao('t.shop_code');
        $shop_arr = array();
        foreach ($tianmao_shop_code as $shop) {
            $shop_arr[] = $shop['shop_code'];
        }
        $shop_str = deal_array_with_quote($shop_arr);
        if ($filter['shop_code'] == '') {
            $sql_main .= " AND rl.shop_code in ({$shop_str}) ";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
                  $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code in ( " . $str. " ) ";
        }
        //买家昵称
        if (isset($filter['buyer_nick']) && $filter['buyer_nick'] !== '') {
            $sql_main .= " AND rl.buyer_nick LIKE :buyer_nick ";
            $sql_values[':buyer_nick'] = "%" . $filter['buyer_nick'] . "%";
        }
        //成交时间
        if (isset($filter['end_time_start']) && $filter['end_time_start'] !== '') {
            $sql_main .= " AND rl.end_time >= :end_time_start ";
            $sql_values[':end_time_start'] = $filter['end_time_start'] . ' 00:00:00';
        }
        if (isset($filter['end_time_end']) && $filter['end_time_end'] !== '') {
            $sql_main .= " AND rl.end_time <= :end_time_end ";
            $sql_values[':end_time_end'] = $filter['end_time_end'] . ' 23:59:59';
        }

        if ($onlySql) {
            $sql = array('from' => $sql_main, 'params' => $sql_values);
            return array('status' => '1', 'data' => $sql, 'message' => '仅返回SQL');
        }

        return array(
            'filter' => $filter,
            'sql_main' => $sql_main,
            'sql_values' => $sql_values,
        );
    }


    /*
     *列表查询
     *  **/
    function get_by_list_page($filter){
        $ret = $this->get_by_page($filter);
        $sql_main = $ret['sql_main'];
        $sql_values = $ret['sql_values'];
        $select = "rl.*";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, false);
        foreach ($data['data'] as &$value) {
            $value['real_point_fee_money'] = $value['real_point_fee'] * 0.01;
            $value['shop_code_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }


    /*
     * 汇总统计
     * */
    function report_count($filter){
        // 汇总
        $sqlArr = $this->get_by_page($filter, true);
        $sqlArr = $sqlArr['data'];
        $sql_main = $sqlArr['from'];
        $sql = " SELECT sum(payment) as payment_all ,sum(post_fee) as post_fee_all,sum(real_point_fee)*0.01 as real_point_fee_money_all " . $sql_main;
        $row = $this->db->get_row($sql, $sqlArr['params']);

        return $row;
    }

}
