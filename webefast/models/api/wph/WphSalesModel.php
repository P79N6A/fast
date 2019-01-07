<?php

require_model('tb/TbModel');

/**
 * 唯品会专场业务
 */
class WphSalesModel extends TbModel {

    function __construct() {
        parent::__construct('api_wph_sales');
    }

    function get_by_page($filter) {
        if (isset($filter['keyword_sales_value']) && $filter['keyword_sales_value'] != '') {
            $filter[$filter['keyword_sales']] = trim($filter['keyword_sales_value']);
        }

        $sql_join = '';
        $is_relation_goods = 0;
        if (isset($filter['keyword_goods_value']) && $filter['keyword_goods_value'] != '') {
            $filter[$filter['keyword_goods']] = trim($filter['keyword_goods_value']);
            $sql_join = 'INNER JOIN `api_wph_sales_sku_relation` AS sr ON ws.`sales_no`=sr.`sales_no`
                        INNER JOIN `api_wph_sales_sku` AS ss ON ws.`shop_code`=ss.`shop_code` AND sr.`barcode`=ss.`barcode`';
            $is_relation_goods = 1;
        }
        $sql_main = "FROM `{$this->table}` AS ws {$sql_join} WHERE 1";
        $sql_values = array();
        $select = 'ws.`shop_code`,ws.`warehouse`,ws.`sales_no`,ws.`name`,ws.`sale_st`,ws.`sale_et`,ws.`insert_time`';
        //店铺代码
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_main .= ' AND ws.`shop_code`=:shop_code ';
            $sql_values[':shop_code'] = $filter['shop_code'];
        }
        //唯品会仓库
        if (isset($filter['warehouse']) && $filter['warehouse'] != '') {
            $sql_main .= ' AND ws.`warehouse`=:warehouse ';
            $sql_values[':warehouse'] = $filter['warehouse'];
        }
        //专场ID
        if (isset($filter['sales_no']) && $filter['sales_no'] != '') {
            $sql_main .= ' AND ws.`sales_no` LIKE :sales_no ';
            $sql_values[':sales_no'] = "%{$filter['sales_no']}%";
        }
        //专场名称
        if (isset($filter['name']) && $filter['name'] != '') {
            $sql_main .= ' AND ws.`name` LIKE :name ';
            $sql_values[':name'] = "%{$filter['name']}%";
        }
        //开售时间-起始
        if (isset($filter['sale_st_start']) && !empty($filter['sale_st_start'])) {
            $sql_main .= ' AND ws.`sale_st`>=:sale_st_start ';
            $sql_values[':sale_st_start'] = strtotime($filter['sale_st_start']);
        }
        //开售时间-结束
        if (isset($filter['sale_st_end']) && !empty($filter['sale_st_end'])) {
            $sql_main .= ' AND ws.`sale_st`<=:sale_st_end ';
            $sql_values[':sale_st_end'] = strtotime($filter['sale_st_end']);
        }
        //专场状态
        if (isset($filter['sales_status']) && $filter['sales_status'] !== '') {
            $status = $filter['sales_status'];
            $curr_time = time();
            switch ($status) {
                case 'no_start':
                    $sql_main .= " AND ws.`sale_st`>{$curr_time}";
                    break;
                case 'starting':
                    $sql_main .= " AND ws.`sale_st`<={$curr_time} && ws.`sale_et`>{$curr_time}";
                    break;
                case 'end':
                    $sql_main .= " AND ws.`sale_et`<={$curr_time}";
                    break;
                default:
                    break;
            }
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= ' AND ss.`barcode` LIKE :barcode ';
            $sql_values[':barcode'] = "%{$filter['barcode']}%";
        }
        //商品名称
        if (isset($filter['product_name']) && $filter['product_name'] != '') {
            $sql_main .= ' AND ss.`product_name` LIKE :product_name ';
            $sql_values[':product_name'] = "%{$filter['product_name']}%";
        }
        //商品品牌
        if (isset($filter['brand_name']) && $filter['brand_name'] != '') {
            $sql_main .= ' AND ss.`brand_name` LIKE :brand_name ';
            $sql_values[':brand_name'] = "%{$filter['brand_name']}%";
        }
        if ($is_relation_goods == 1) {
            $sql_main .= ' GROUP BY ws.`shop_code`,ws.`sales_no`';
        }
        $sql_main .= ' ORDER BY ws.`insert_time`,ws.`sale_st` DESC';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, TRUE);
        $warehouse = load_model('api/WeipinhuijitWarehouseModel')->get_warehouse_select();
        $warehouse = array_column($warehouse, 'warehouse_name', 'warehouse_code');
        foreach ($data['data'] as &$row) {
            $curr_time = time();
            $row['sales_status'] = '';
            if ($row['sale_st'] > $curr_time) {
                $row['sales_status'] = 1;
                $row['status_txt'] = '未开始';
            } else if ($row['sale_et'] <= $curr_time) {
                $row['sales_status'] = 3;
                $row['status_txt'] = '已结束';
            } else if ($row['sale_st'] <= $curr_time && $row['sale_et'] > $curr_time) {
                $row['sales_status'] = 2;
                $row['status_txt'] = '进行中';
            }
            $row['sale_st'] = empty($row['sale_st']) ? '' : date('Y-m-d H:i:s', $row['sale_st']);
            $row['sale_et'] = empty($row['sale_et']) ? '' : date('Y-m-d H:i:s', $row['sale_et']);
            $row['insert_time'] = empty($row['insert_time']) ? '' : date('Y-m-d H:i:s', $row['insert_time']);
            $row['warehouse_name'] = $warehouse[$row['warehouse']];
        }
        filter_fk_name($data['data'], array('shop_code|shop',));
        $ret_data = $data;

        return $this->format_ret(1, $ret_data);
    }

    /**
     * 保存专场信息
     * @param string $shop_code 店铺代码
     * @param array $data 保存数据
     * @return array 保存结果
     */
    function save_sales_list($shop_code, $data) {
        if (empty($data)) {
            return $this->format_ret(-1, '', '数据为空');
        }
        foreach ($data as &$val) {
            $val['shop_code'] = $shop_code;
            $val['insert_time'] = time();
        }

        $ret = $this->insert_multi_exp($this->table, $data, TRUE);
        return $ret;
    }

}
