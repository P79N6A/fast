<?php

require_model('tb/TbModel');

/**
 * 京东沧海wms分类档案
 *
 * @author WMH
 */
class JdwmsCategoryModel extends TbModel {

    protected $table = 'jdwms_category';

    public function get_by_page($filter) {
        $select = 'category_code,category_name';
        $sql_main = "FROM {$this->table} WHERE 1";
        $sql_values = array();
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $sql_main .= " AND category_code=:category_code";
            $sql_values[':category_code'] = $filter['category_code'];
        }
        if (isset($filter['category_name']) && $filter['category_name'] != '') {
            $sql_main .= " AND category_name LIKE :category_name";
            $sql_values[':category_name'] = "%{$filter['category_name']}%";
        }

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        
        return $this->format_ret(1, $data);
    }

}
