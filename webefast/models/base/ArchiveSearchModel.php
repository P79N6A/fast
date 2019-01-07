<?php

require_model('tb/TbModel');

/**
 * 公共基础档案检索类
 *
 * @author WMH
 */
class ArchiveSearchModel extends TbModel {

    /**
     * 档案数据表设置,供档案数据查询使用
     * @param string $_type 数据分类
     * @return array
     */
    private function get_table_map($_type) {
        static $table_map = [
            'express' => ['base_express', 'express_code', 'express_name'],
            'store' => ['base_store', 'store_code', 'store_name'],
            'shop' => ['base_shop', 'shop_code', 'shop_name'],
            'supplier' => ['base_supplier', 'supplier_code', 'supplier_name'],
            'area' => ['base_area', 'id', 'name'],
            'express_company' => ['base_express_company', 'company_code', 'company_name'],
            'sale_channel' => ['base_sale_channel', 'sale_channel_code', 'sale_channel_name'],
            'print_templates' => ['sys_print_templates', 'print_templates_code', 'print_templates_name'],
            'spec1' => ['base_spec1', 'spec1_code', 'spec1_name'],
            'spec2' => ['base_spec2', 'spec2_code', 'spec2_name'],
            'goods' => ['base_goods', 'goods_code', 'goods_name'],
            'sku' => ['goods_sku', 'sku', 'barcode'],
        ];

        $arr = isset($table_map[$_type]) ? $table_map[$_type] : [];
        return $arr;
    }

    /**
     * 根据代码获取档案数据
     * @staticvar type $data 数据集合
     * @param string $_type 数据分类
     * @param array|string $_code 代码条件,为空则直接查询档案所有数据
     * @param string $_struct 返回数据结构类型 1-单层键值对 2-多维数组
     * @param string $_select 配置字段外需要查询的字段 $_struct=2时有效
     * @param string $_where 其他条件
     * @return array 档案数据
     */
    public function get_archives_map($_type, $_code = [], $_struct = 1, $_select = '', $_where = '') {
        static $data = NULL;

        $code_arr = $_code;
        if (!is_array($_code) && !empty($_code)) {
            if (isset($data[$_type][$_code])) {
                return $data[$_type];
            }
            $code_arr = [$_code];
        }

        $table_data = $this->get_table_map($_type);
        if (empty($table_data)) {
            return [];
        }
        $table = $table_data[0];
        $field = $table_data[1];
        $select = $field . ' AS _code,' . $table_data[2] . ' AS _name';
        if ($_struct === 2 && !empty($_select)) {
            $select .= ',' . $_select;
        }

        $sql_values = [];
        $sql = "SELECT {$select} FROM {$table} WHERE 1";

        $sql .= empty($_where) ? '' : ' AND ' . $_where;

        if (!empty($_code)) {
            $code_arr = array_unique($code_arr);
            $code_str = $this->arr_to_in_sql_value($code_arr, $field, $sql_values);
            $sql .= " AND {$field} IN({$code_str})";
        }

        $temp_data = $this->db->get_all($sql, $sql_values);

        if ($_struct === 2) {
            $data[$_type] = load_model('util/ViewUtilModel')->get_map_arr($temp_data, '_code');
        } else {
            $data[$_type] = array_column($temp_data, '_name', '_code');
        }

        return $data[$_type];
    }

    /**
     * 获取所有已定义的档案
     * @param array $_type_arr 档案类型
     * @return array
     */
    public function get_all_archives_map($_type_arr) {
        if (!is_array($_type_arr) || empty($_type_arr)) {
            return [];
        }

        $data = [];
        array_walk($_type_arr, function($_type) use (&$data) {
            $data[$_type] = $this->get_archives_map($_type);
        });

        return $data;
    }

    /**
     * 根据原始数据查询系统中的代码、名称数据,一般用于做数据校验取数据
     * @param string $_type 数据分类
     * @param array $_data 原始数据
     * @param string $_field 条件数据
     * @return array
     */
    public function get_single_data($_type, &$_data, $_field) {
        if (isset($_data[0][$_field])) {
            $d = $this->filter_single_data($_data, $_field);
            if (empty($d)) {
                return [];
            }
        }else{
            $d = $_data;
        }

        $table_data = $this->get_table_map($_type);
        if (empty($table_data)) {
            return [];
        }

        $sql_values = [];
        $field_str = $this->arr_to_in_sql_value($d, $_field, $sql_values);
        $select = $table_data[1] . ' AS _code,' . $table_data[2] . ' AS _name';
        $sql = "SELECT {$select} FROM {$table_data[0]} WHERE $_field IN({$field_str})";
        $data = $this->db->get_all($sql, $sql_values);
        return array_column($data, '_name', '_code');
    }

    public function filter_single_data(&$data, $column_key, $index_key = NULL) {
        return array_filter(array_unique(array_column($data, $column_key, $index_key)));
    }

}
