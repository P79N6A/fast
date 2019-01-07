<?php

require_model('tb/TbModel');

/**
 * 商品选择类
 *
 * @author WMH
 */
class GoodsSelectModel extends TbModel {

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $select = 'bg.goods_id,bg.goods_code,bg.goods_name,bg.goods_short_name,bg.category_code,bg.category_name,bg.sell_price';
        $sql_values = array();
        $sql_main = "FROM base_goods bg WHERE bg.diy=0";

        //分类
        if (!empty($filter['category_code'])) {
            $category_arr = explode(',', $filter['category_code']);
            $category_str = $this->arr_to_in_sql_value($category_arr, 'category_code', $sql_values);
            $sql_main .= " AND bg.category_code IN({$category_str}) ";
        }
        //商品编码
        if (!empty($filter['goods_code'])) {
            $sql_main .= " AND bg.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = "%{$filter['goods_code']}%";
        }
        //商品名称
        if (!empty($filter['goods_name'])) {
            $sql_main .= " AND bg.goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = "%{$filter['goods_name']}%";
        }
        //商品简称
        if (!empty($filter['goods_short_name'])) {
            $sql_main .= " AND bg.goods_short_name LIKE :goods_short_name ";
            $sql_values[':goods_short_name'] = "%{$filter['goods_short_name']}%";
        }

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        if (empty($data['data'])) {
            return $this->format_ret(1, $data);
        }

        foreach ($data['data'] as &$row) {
            $row['sell_price'] = isset($row['sell_price']) ? round($row['sell_price'], 2) : '';
        }

        return $this->format_ret(1, $data);
    }

}
