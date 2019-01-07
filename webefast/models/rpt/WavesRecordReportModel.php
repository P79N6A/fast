<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

class WavesRecordReportModel extends TbModel {

    /**
     * @var string 表名
     */
    public $table = 'oms_waves_record';

    /**
     * 根据条件查询数据
     * @param $filter
     * @return array
     */
    function get_list_by_page($filter) {
        $sql_values = array();
        $sub_sql = "SELECT r1.picker,r2.goods_num FROM {$this->table} r1 
                   INNER JOIN oms_deliver_record r2 ON r1.waves_record_id = r2.waves_record_id 
                   WHERE r2.is_deliver=1 AND r1.picker<>'' ";
        //仓库权限
        $filter_store_code=null;
        $sub_sql .= load_model('base/StoreModel')->get_sql_purview_store('r2.store_code', $filter_store_code);
        //店铺权限
        $filter_shop_code = null;
        $sub_sql .= load_model('base/ShopModel')->get_sql_purview_shop('r2.shop_code', $filter_shop_code);
        //发货时间
        if (isset($filter['delivery_time_start']) && $filter['delivery_time_start'] != '') {
            $sub_sql .= " AND r2.delivery_date >= :delivery_time_start ";
            $sql_values[':delivery_time_start'] = $filter['delivery_time_start'];
        }
        if (isset($filter['delivery_time_end']) && $filter['delivery_time_end'] != '') {
            $sub_sql .= " AND r2.delivery_date <= :delivery_time_end ";
            $sql_values[':delivery_time_end'] = $filter['delivery_time_end'];
        }

        $select = ' t.picker AS staff_code,count(1) AS pick_record_num,sum(t.goods_num) AS pick_goods_num';
        $sql_main = " FROM (" . $sub_sql . ") AS t GROUP BY t.picker";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $data_by_key = array();
        foreach ($data['data'] as $key => &$value) {
            //获取拣货员名称
            $staff = $this->get_staff_data($value['staff_code']);
            $value['staff_name'] = $staff['staff_name'];
            $value['staff_id'] = $staff['staff_id'];
        }
        return $this->format_ret(1, $data);
    }

    function get_staff_data($staff_code) {
        $sql = "SELECT * FROM base_store_staff WHERE staff_code=:staff_code";
        $sql_value[':staff_code'] = $staff_code;
        $ret = $this->db->get_row($sql, $sql_value);
        return $ret;
    }

}
