<?php

require_model('tb/TbModel');

class ExpressPloyCensusModel extends TbModel {

    /**
     * 查询快递分布数据
     * @param array $filter 过滤条件
     * @return array 数据集
     */
    function get_by_page($filter) {
        $sql_main = '';
        $sql_values = array();
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_main .= " AND (o.shop_code=:shop_code )";
            $sql_values[':shop_code'] = $filter['shop_code'];
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $sql_main .= " AND (o.store_code = :store_code )";
            $sql_values[':store_code'] = $filter['store_code'];
        }
        //付款日期
        if (!empty($filter['pay_first_time'])) {
            $sql_main .= " AND o.pay_time >= :pay_first_time ";
            $sql_values[':pay_first_time'] = $filter['pay_first_time'];
        }
        if (!empty($filter['pay_last_time'])) {
            $sql_main .= " AND o.pay_time <= :pay_last_time ";
            $sql_values[':pay_last_time'] = $filter['pay_last_time'];
        }
        $select = "express_code,express_name, count(*) as order_num ";

        $sql = " from (select o.sell_record_code,o.store_code,o.shop_code,b.express_code,b.express_name from oms_sell_record o INNER JOIN base_express b on o.express_code=b.express_code where o.order_status<>3 {$sql_main}) as tmp group by express_code ";
        $data = $this->get_page_from_sql($filter, $sql, $sql_values, $select, true);

        $sql_sum = "select count(*) from oms_sell_record o INNER JOIN base_express b on o.express_code=b.express_code where o.order_status<>3 {$sql_main}";
        $sum = $this->db->getOne($sql_sum, $sql_values);
        foreach ($data['data'] as &$value) {
            $value['proportion'] = round($value['order_num'] / $sum * 100, 2) . "％";
        }

        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status, $data);
    }

}
