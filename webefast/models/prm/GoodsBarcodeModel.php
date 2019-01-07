<?php

/**
 * 商品条码管理相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('prm');

class GoodsBarcodeModel extends TbModel {

    public $is_create_barcode = array(
        '0' => '否',
        '1' => '是'
    );
    public $print_fields_default = array(
        '品名' => 'goods_name',
        '货号' => 'goods_code',
        '商品简称' => 'goods_short_name',
        '出厂名称' => 'goods_produce_name',
        '品牌' => 'brand_name',
        '品牌代码' => 'brand_code',
        '分类' => 'category_name',
        '分类代码' => 'category_code',
        '季节' => 'season_name',
        '季节代码' => 'season_code',
        '年份' => 'year_name',
        '年份代码' => 'year_code',
        //'规格1'=>'spec1_name',
        //'规格1代码'=>'spec1_code',
        //'规格2'=>'spec2_name',
        //'规格2代码'=>'spec2_code',
        '条码' => 'barcode',
        '条码描述' => 'barcode_remark',
        '商品重量' => 'weight',
        '吊牌价' => 'price',
        '成本价' => 'cost_price',
        '批发价' => 'trade_price',
        '进货价' => 'purchase_price',
        '国标码' => 'gb_code',
        '商品子条码' => 'goods_barcode_child'

            /* '执行标准'=>'',
              '安全技术类别'=>'',
              '等级'=>'',
              '面料'=>'',
              '配料'=>'',
              '电话'=>'',
              '检验员'=>'', */
    );
    public $property = array();

    public function print_data_default($id, $print_type = 'flash') {
        $r = array();
        $sql = "select k.sku,k.gb_code
                ,k.goods_code,g.goods_name,g.goods_short_name,g.goods_produce_name
                ,g.brand_code,g.brand_name
                ,g.category_code,g.category_name
                ,g.season_code,g.season_name
                ,g.year_code,g.year_name
                ,k.spec1_code,k.spec1_name,k.spec2_code,k.spec2_name,s1.remark as spec1_remark,s2.remark as spec2_remark
                ,k.weight,g.weight goods_weight
                ,k.price,k.cost_price,g.sell_price
                ,g.cost_price  as base_cost_price,g.trade_price,g.purchase_price,k.remark as barcode_remark,GROUP_CONCAT(gc.barcode) goods_barcode_child
                from goods_sku k
                left join base_goods g  ON g.goods_code = k.goods_code
                 left join base_spec1 s1 ON s1.spec1_code = k.spec1_code
                left join base_spec2 s2 ON s2.spec2_code = k.spec2_code
                left join goods_barcode_child gc ON k.sku = gc.sku
                where k.sku_id=:sku_id";


        $r = $this->db->get_row($sql, array('sku_id' => $id));
        $sql = "select barcode from goods_barcode where sku = :sku";
        $r['barcode'] = $this->db->get_value($sql, array('sku' => $r['sku']));
        if (empty($r['weight']) && isset($r['goods_weight']))
            $r['weight'] = $r['goods_weight'];
        if ($r['price'] == 0 && isset($r['sell_price']))
            $r['price'] = $r['sell_price'];
        if ($r['cost_price'] == 0 && isset($r['base_cost_price']))
            $r['cost_price'] = $r['base_cost_price'];

        // extra property
        $sql = "select * from base_property where property_val_code = :goods_code and property_type ='goods'";
        $s = $this->db->get_row($sql, array('goods_code' => $r['goods_code']));
        if (!empty($s)) {
            foreach ($this->property as $k => $v) {
                if (isset($s[$v]))
                    $r[$v] = $s[$v];
            }
        }

        $d = array();
        foreach ($r as $k => $v) {
            // 键值对调
            if ($print_type == 'flash') {
                $nk = array_search($k, $this->print_fields_default);
                $nk = $nk === false ? $k : $nk;
                $d[$nk] = is_null($v) ? '' : $v;
            } else {
                if ($k == 'barcode') {
                    $d['690123456789'] = is_null($v) ? '' : $v;
                } else {
                    $d[$k] = is_null($v) ? '' : $v;
                }
            }
        }

        return $d;
    }

    public function __construct($table = '', $pk = '', $db = '') {
        parent::__construct($table, $pk, $db);

        // init custom property
        $sql = "SELECT property_val,property_val_title from base_property_set where property_type ='goods'";
        $r = $this->db->get_all($sql);
        foreach ($r as $k => $v) {
            $this->property[$v['property_val_title']] = $v['property_val'];
            if (!isset($this->print_fields_default[$v['property_val_title']])) {
                $this->print_fields_default[$v['property_val_title']] = $v['property_val'];
            }
        }

        // spec alias
//        $sql = "select `value` from sys_params where param_code = :param_code";
//        $r = $this->db->get_value($sql, array('param_code'=>'goods_spec1'));
//        $r = $this->db->get_value($sql, array('param_code'=>'goods_spec2'));
        $spec_arr = load_model("sys/SysParamsModel", true, false, 'webefast')->get_val_by_code(array('goods_spec1', 'goods_spec2'));
        if (!isset($spec_arr['goods_spec1']) || empty($spec_arr['goods_spec1'])) {
            $r = "规格1";
        } else {
            $r = $spec_arr['goods_spec1'];
        }
        $this->print_fields_default[$r] = 'spec1_name';
        $this->print_fields_default[$r . '代码'] = 'spec1_code';


        $this->print_fields_default[$r . '描述'] = 'spec1_remark';

        if (!isset($spec_arr['goods_spec2']) || empty($spec_arr['goods_spec2'])) {
            $r = "规格2";
        } else {
            $r = $spec_arr['goods_spec2'];
        }
        $this->print_fields_default[$r] = 'spec2_name';
        $this->print_fields_default[$r . '代码'] = 'spec2_code';
        $this->print_fields_default[$r . '描述'] = 'spec2_remark';

        $this->print_fields_default['条码描述'] = 'barcode_remark';
    }

    function get_table() {
        return 'goods_sku';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {

        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_join = "LEFT JOIN goods_barcode gb on rl.goods_code = gb.goods_code and rl.spec1_code = gb.spec1_code and rl.spec2_code = gb.spec2_code";
        $sql_main = "FROM {$this->table} rl LEFT JOIN base_goods r2 on rl.goods_code = r2.goods_code {$sql_join}  WHERE 1";
        //品牌权限
        $filter_brand_code = isset($filter['brand_code']) ? $filter['brand_code'] : null;
        $sql_main .= load_model('prm/BrandModel')->get_sql_purview_brand('r2.brand_code', $filter_brand_code);
        //分类
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $category_code_arr = explode(',', $filter['category_code']);
            if (!empty($category_code_arr)) {
                $sql_main .= " AND (";
                foreach ($category_code_arr as $key => $value) {
                    $param_category = 'param_category' . $key;
                    if ($key == 0) {
                        $sql_main .= " r2.category_code = :{$param_category} ";
                    } else {
                        $sql_main .= " or r2.category_code = :{$param_category} ";
                    }

                    $sql_values[':' . $param_category] = $value;
                }
                $sql_main .= ")";
            }
        }
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $brand_code_arr = explode(',', $filter['brand_code']);
            if (!empty($brand_code_arr)) {
                $sql_main .= " AND (";
                foreach ($brand_code_arr as $key => $value) {
                    $param_brand = 'param_brand' . $key;
                    if ($key == 0) {
                        $sql_main .= " r2.brand_code = :{$param_brand} ";
                    } else {
                        $sql_main .= " or r2.brand_code = :{$param_brand} ";
                    }

                    $sql_values[':' . $param_brand] = $value;
                }
                $sql_main .= ")";
            }
        }
        //年份
        if (isset($filter['year_code']) && $filter['year_code'] != '') {
            $year_code_arr = explode(',', $filter['year_code']);
            if (!empty($year_code_arr)) {
                $sql_main .= " AND (";
                foreach ($year_code_arr as $key => $value) {
                    $param_year = 'param_year' . $key;
                    if ($key == 0) {
                        $sql_main .= " r2.year_code = :{$param_year} ";
                    } else {
                        $sql_main .= " or r2.year_code = :{$param_year} ";
                    }

                    $sql_values[':' . $param_year] = $value;
                }
                $sql_main .= ")";
            }
        }
        //季节
        if (isset($filter['season_code']) && $filter['season_code'] != '') {
            $season_code_arr = explode(',', $filter['season_code']);
            if (!empty($season_code_arr)) {
                $sql_main .= " AND (";
                foreach ($season_code_arr as $key => $value) {
                    $param_season = 'param_season' . $key;
                    if ($key == 0) {
                        $sql_main .= " r2.season_code = :{$param_season} ";
                    } else {
                        $sql_main .= " or r2.season_code = :{$param_season} ";
                    }

                    $sql_values[':' . $param_season] = $value;
                }
                $sql_main .= ")";
            }
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND (r2.goods_name LIKE :goods_name )";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品编号/商品条形码
        if (isset($filter['goods_code_barcode']) && $filter['goods_code_barcode'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :goods_code_barcode  or (rl.sku in (select sku from goods_barcode where barcode LIKE :goods_code_barcode)))";
            $sql_values[':goods_code_barcode'] = '%' . $filter['goods_code_barcode'] . '%';
        }
        //商品备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND rl.remark LIKE :remark";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
        }
        //商品简称
        if (isset($filter['goods_short_name']) && $filter['goods_short_name'] != '') {
            $sql_main .= " AND (r2.goods_short_name LIKE :goods_short_name )";
            $sql_values[':goods_short_name'] = '%' . $filter['goods_short_name'] . '%';
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND rl.sku in (select sku from goods_barcode where barcode LIKE :barcode)";
            $sql_values[':barcode'] = '%' . $filter['barcode'] . '%';
        }
        //国标码
        if (isset($filter['gb_code']) && $filter['gb_code'] != '') {
            $sql_main .= " AND (rl.gb_code like :gb_code)";
            $sql_values[':gb_code'] = $filter['gb_code'] . '%';
        }
        ///对外接口 条件使用
        if (isset($filter['lastchanged']) && $filter['lastchanged'] != '') {
            $sql_main .= " AND rl.lastchanged > :lastchanged ";
            $sql_values[':lastchanged'] = $filter['lastchanged'];
        }
        //最后更新时间
        if (isset($filter['lastchanged_start']) && $filter['lastchanged_start'] !== '') {
            $sql_main .= " AND rl.lastchanged >= :lastchanged_start ";
            $sql_values[':lastchanged_start'] = $filter['lastchanged_start'] . ' 00:00:00';
        }
        if (isset($filter['lastchanged_end']) && $filter['lastchanged_end'] !== '') {
            $sql_main .= " AND rl.lastchanged <= :lastchanged_end ";
            $sql_values[':lastchanged_end'] = $filter['lastchanged_end'] . ' 23:59:59';
        }
        //判断未生成条码的商品
        if (isset($filter['is_create_barcode']) && $filter['is_create_barcode'] != '') {
            if ($filter['is_create_barcode'] === '1') {
                $sql_main .= " AND (gb.barcode != '' AND gb.barcode is not null)";
            } elseif ($filter['is_create_barcode'] === '0') {
                $sql_main .= " AND ( gb.barcode = '' or gb.barcode is null)";
            }
        }

        if (isset($filter['is_api']) && $filter['is_api'] !== '') {
            $sql_main .= " order by rl.lastchanged desc ";
        }
        ///对外接口

        $select = 'rl.*,r2.goods_short_name,r2.goods_name,r2.goods_name,r2.weight as zhongliang';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => &$row) {
            $row[0] = isset($row[0]) ? $row[0] : '';
            $row['sell_price'] = isset($row['sell_price']) ? $row['sell_price'] : '';
            $row['sell_price'] = round($row[0], 2);

            //默认取条码吊牌价
            $row['price'] = empty($row['price']) ? 0.000 : round($row['price'], 2);
            if ($row['weight'] == '0.000') {
                $row['weight'] = $row['zhongliang'];
            }
            //商品条码
            $sql = "select barcode FROM goods_barcode where goods_code = '{$row['goods_code']}' and spec1_code = '{$row['spec1_code']}' and spec2_code = '{$row['spec2_code']}' ";
            $rs = $this->db->get_all_col($sql);
            //对外接口 去掉html标签
            if (isset($filter['is_api']) && $filter['is_api'] !== '') {
                $row['barcode'] = $rs;
            } else {
                $row['barcode'] = $rs[0];
            }

            $row['barcode_html'] = "<label class=\"bar-style\" onclick=\"changeElement(this)\" sku=\"{$row['sku']}\" gb_code=\"{$row['gb_code']}\">{$row['barcode']}</label>";
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 获取条码列表(暂时用于对外接口)
     * @param $filter
     * @return array
     * Author: yb.ding<ybd312@163.com>
     */
    function get_barcode($filter) {
        $sql_main = "FROM goods_barcode  as rl WHERE 1";
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        //商品编号
        if (isset($filter['sku']) && $filter['sku'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :sku )";
            $sql_values[':goods_code'] = $filter['sku'] . '%';
        }
        if (isset($filter['lastchanged']) && $filter['lastchanged'] != '') {
            $sql_main .= " AND rl.lastchanged > :lastchanged ";
            $sql_values[':lastchanged'] = $filter['lastchanged'];
        }
        if (isset($filter['is_api']) && $filter['is_api'] !== '') {
            $sql_main .= " order by rl.lastchanged desc ";
        }
        $select = 'rl.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {

        return $this->get_row(array('barcode_id' => $id));
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*") {

        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    public function get_by_field_table($table, $field_name, $value, $select = "*") {

        $sql = "select r1.*,r2.cost_price,r2.sell_price,r2.buy_price,r2.purchase_price from {$table} r1
				   INNER JOIN base_goods r2 on r1.goods_code = r2.goods_code
		          where r1.{$field_name} = :{$field_name}";

        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    public function get_by_field_muti($field_name1, $value1, $field_name2, $value2, $select = "*", $table) {
        $sql = "select {$select} from {$table} where {$field_name1} = :{$field_name1} and {$field_name2} = :{$field_name2} ";

        $data = $this->db->get_row($sql, array(":{$field_name1}" => $value1, ":{$field_name2}" => $value2));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    /**
     * 保存系统sku
     */
    function save_sku($request) {
        $goods_code = $request['goods_code'];
        $spec1_code_str = $request['spec1_code'];
        $spec2_code_str = $request['spec2_code'];
        $status = $this->valid($goods_code, $spec1_code_str, $spec2_code_str, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $log_data = $this->sorting_log_data($request);
        if (!empty($log_data)) {
            $log = array(
                'goods_id' => $request['goods_id'],
                'operation_note' => $log_data,
                'operation_name' => '修改条码'
            );
            //添加日志
            $ret = load_model('prm/GoodsModel')->insert_goods_log($log);
            if ($ret['status'] < 0) {
                return $this->format_ret(-1, '', '保存日志出错');
            }
        }

        $sql = "select sku_id,goods_code,spec1_code,spec2_code,sku FROM {$this->table} where goods_code ='{$goods_code}' ";
        $data = $this->db->get_all($sql);
        if (count($data) != 0) {
            $sql = "delete from {$this->table} where goods_code ='{$goods_code}'";
            $ret = $this->db->query($sql);
        }

        $spec1_code_arr = explode(',', $spec1_code_str);
        $spec2_code_arr = explode(',', $spec2_code_str);
        $exists_sku_arr = array();
        foreach ($spec1_code_arr as $spec1_code) {
            foreach ($spec2_code_arr as $spec2_code) {
                $str = $spec1_code . '_' . $spec2_code . '_sku';
                $sku = trim($request[$str]);

                if (empty($sku) || trim($sku) == '') {
                    continue;
                }
                if (in_array($sku, $exists_sku_arr)) {
                    continue;
                }
                $exists_sku_arr[] = $sku;

                $str = $spec1_code . '_' . $spec2_code . '_sku_remark';
                $sku_remark = $request[$str];
                //价格
                $str = $spec1_code . '_' . $spec2_code . '_sell_price';
                $sell_price = $request[$str];
                //sku 成本价
                $str = $spec1_code . '_' . $spec2_code . '_cost_price';
                $cost_price = $request[$str];
                $str = $spec1_code . '_' . $spec2_code . '_weight';
                $weight = $request[$str];
                $sql = "INSERT  INTO " . $this->table . "(goods_code,spec1_code,spec2_code,sku,remark,price,cost_price,weight) VALUES('{$goods_code}','{$spec1_code}','{$spec2_code}','{$sku}','{$sku_remark}','{$sell_price}','{$cost_price}','{$weight}')"
                        . " ON DUPLICATE KEY UPDATE barcode=VALUES(barcode),remark=VALUES(remark),price=VALUES(price),weight=VALUES(weight)";
                $ret = $this->db->query($sql);
            }
        }


        if ($ret) {
            $id = $this->db->insert_id();
            return $this->format_ret("1", $id, 'insert_success');
        } else {
            return $this->format_ret("-1", '', 'insert_error');
        }
        exit;
    }

    /**
     * 整理日志数据
     * @param type $log_info
     */
    function sorting_log_data($request) {
        $spec1_code_str = $request['spec1_code'];
        $spec2_code_str = $request['spec2_code'];
        $spec1_code_arr = explode(',', $spec1_code_str);
        $spec2_code_arr = explode(',', $spec2_code_str);
        $list_data = array();
        $sku_arr = array();
        foreach ($spec1_code_arr as $spec1_code) {
            foreach ($spec2_code_arr as $spec2_code) {
                //sku
                $str = $spec1_code . '_' . $spec2_code . '_sku';
                $sku = trim($request[$str]);
                //价格
                $str = $spec1_code . '_' . $spec2_code . '_sell_price';
                $sell_price = $request[$str];
                //sku 成本价
                $str = $spec1_code . '_' . $spec2_code . '_cost_price';
                $cost_price = $request[$str];
                //条码
                $str = $spec1_code . '_' . $spec2_code . '_barcode';
                $barcode = trim($request[$str]);
                //国标码
                $str = $spec1_code . '_' . $spec2_code . '_gb_code';
                $gb_code = trim($request[$str]);
                $list_data[$sku] = array(
                    'sku' => $sku,
                    'sell_price' => (float) $sell_price,
                    'cost_price' => (float) $cost_price,
                    'barcode' => $barcode,
                    'gb_code' => $gb_code
                );
                $sku_arr[] = $sku;
            }
        }
        //查询数据库的sku信息    
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT sku,price,cost_price,barcode,gb_code FROM goods_sku WHERE sku in ({$sku_str}) ";
        $tab_data = $this->db->get_all($sql, $sql_values);
        $log_info = array();
        foreach ($tab_data as $val) {
            $key = $val['sku'];
            $log = '';
            if (!isset($list_data[$key]) && empty($list_data[$key])) {
                $log_info[] = '删除条码:' . $val['barcode'] . '；';
                unset($list_data[$key]);
                continue;
            }
            $log .= load_model('prm/GoodsModel')->get_log_data($val['barcode'], $list_data[$key]['barcode'], '商品条形码');
            $log .= load_model('prm/GoodsModel')->get_log_data($val['gb_code'], $list_data[$key]['gb_code'], '国标码');
            $log .= load_model('prm/GoodsModel')->get_log_data((float) $val['price'], (float) $list_data[$key]['sell_price'], '吊牌价');
            $log .= load_model('prm/GoodsModel')->get_log_data((float) $val['cost_price'], (float) $list_data[$key]['cost_price'], '成本价');
            if (!empty($log)) {
                $log_info[] = 'SKU:' . $key . '：' . $log;
            }
            unset($list_data[$key]);
        }
        if (!empty($list_data)) {
            foreach ($list_data as $kuy => $val) {
                if (!empty($val['barcode'])) {
                    $log_info[] = "新增条码：" . $val['barcode'] . "；";
                }
                if (!empty($val['gb_code'])) {
                    $log_info[] = "新增国标码：" . $val['gb_code'] . "；";
                }
                if (!empty($val['price'])) {
                    $log_info[] = "新增吊牌价：" . $val['price'] . "；";
                }
                if (!empty($val['cost_price'])) {
                    $log_info[] = "新增成本价：" . $val['cost_price'] . "；";
                }
            }
        }
        $log_str = implode(' ', $log_info);
        return $log_str;
    }

    /*
     * 保存商品条码
     * */

    public $check_data = array();

    function save_barcode($request) {
        $goods_code = $request['goods_code'];
        $spec1_code_str = $request['spec1_code'];
        $spec2_code_str = $request['spec2_code'];
        $this->check_data = array();
        $status = $this->valid($goods_code, $spec1_code_str, $spec2_code_str, true);
        if ($status <> 1) {
            return $this->format_ret($status);
        }

        $sql = "select barcode_id,goods_code,spec1_code,spec2_code,barcode,sku FROM goods_barcode where goods_code ='{$goods_code}' ";

        $data = $this->db->get_all($sql);

        $where = " goods_code ='{$goods_code}' ";

        $barcode_arr = $this->get_use_sku_by_goods_code($goods_code);
        if (!empty($barcode_arr)) {
            $sku_arr = array_keys($barcode_arr);
            $sku_str = "'" . implode("','", $sku_arr) . "'";
            $where .= "  and sku not in({$sku_str})";
        }

        if (count($data) != 0) {
            $sql = "delete from goods_barcode where {$where}";
            $ret = $this->db->query($sql);
        }

        $spec1_code_arr = explode(',', $spec1_code_str);
        $spec2_code_arr = explode(',', $spec2_code_str);
        $exists_barcode_arr = $exists_gb_code_arr = array();
        $ret = true;
        $is_status = 1;
        $message = '';
        foreach ($spec1_code_arr as $spec1_code) {
            foreach ($spec2_code_arr as $spec2_code) {
                $str_barcode = $spec1_code . '_' . $spec2_code . '_barcode';
                $barcode = trim($request[$str_barcode]);
                $str_gb_code = $spec1_code . '_' . $spec2_code . '_gb_code';
                $gb_code = trim($request[$str_gb_code]);
                if (!empty($barcode) && !empty($gb_code) && $barcode === $gb_code) {
                    $is_status = 0;
                    $message = '商品条码' . $barcode . '与商品国标码' . $gb_code . '重复，请修改或重新输入';
                    continue;
                }
                $sql = "select barcode from goods_barcode where (goods_code <> :goods_code or spec1_code <> :spec1_code or spec2_code <> :spec2_code) and  barcode = :barcode";
                $arr = array(':barcode' => $barcode, ':goods_code' => $goods_code, ':spec1_code' => $spec1_code, ':spec2_code' => $spec2_code);
                $exists_barcode_arr = $this->db->get_row($sql, $arr);

                if (!empty($barcode) || trim($barcode) != '') {
                    if (in_array($barcode, $exists_barcode_arr)) {
                        $is_status = 0;
                        $message .= '商品条码' . $barcode . '已存在，请修改或重新输入';
                        continue;
                    }
                }
                $exists_barcode_arr[] = $barcode;

                /*                $sql = "select gb_code from goods_barcode where (goods_code <> :goods_code or spec1_code <> :spec1_code or spec2_code <> :spec2_code) and  gb_code = :gb_code";
                  $arr = array(':gb_code' => $gb_code, ':goods_code' => $goods_code, ':spec1_code' => $spec1_code, ':spec2_code' => $spec2_code);
                  $exists_gb_code_arr = $this->db->get_row($sql, $arr);
                  if (!empty($gb_code) || trim($gb_code) != '') {
                  if (in_array($gb_code, $exists_gb_code_arr)) {
                  $message .= '国标码{$barcode}已存在，请修改或重新输入';
                  continue;
                  }
                  }
                  $exists_gb_code_arr[] = $gb_code; */
                $spec = $spec1_code . '_' . $spec2_code;
                if (!empty($gb_code) || trim($gb_code) != '') {
                    $ret = load_model('prm/GoodsModel')->gb_code_exist($gb_code, $goods_code, $spec);
                    if ($ret['status'] == 1) {
                        $is_status = 0;
                        $message .= $ret['message'];
                        continue;
                    }
                }

                $str_sku = $spec1_code . '_' . $spec2_code . '_sku';
                $sku = $request[$str_sku];

                /*                if (isset($barcode_arr[$sku])) {
                  if ($barcode_arr[$sku]['barcode'] == $barcode && $barcode_arr[$sku]['goods_code'] == $goods_code && $barcode_arr[$sku]['spec1_code'] == $spec1_code && $barcode_arr[$sku]['spec2_code'] == $spec2_code) {
                  $message .= '商品条码'.$barcode.'已存在，请修改或重新输入';
                  continue;
                  }
                  } */

                if (empty($sku) || trim($sku) == '') {
                    $message .= '商品sku' . $sku . '不存在，请核查';
                    continue;
                }
                $check_use = $this->check_is_use($goods_code, $spec1_code, $spec2_code, $sku);
                if ($check_use === true) {
                    if ((empty($barcode) || trim($barcode) == '') && (empty($gb_code) || trim($gb_code) == '')) {
                        $sql = "INSERT  INTO goods_barcode (goods_code,spec1_code,spec2_code,sku,barcode,gb_code)"
                                . " VALUES('{$goods_code}','{$spec1_code}','{$spec2_code}','{$sku}',NULL,NULL) "
                                . " ON DUPLICATE KEY UPDATE barcode=VALUES(barcode),sku=VALUES(sku),gb_code=VALUES(gb_code)";
                    } else if ((empty($barcode) || trim($barcode) == '') && (!empty($gb_code) || trim($gb_code) != '')) {
                        $sql = "INSERT  INTO goods_barcode (goods_code,spec1_code,spec2_code,sku,barcode,gb_code)"
                                . " VALUES('{$goods_code}','{$spec1_code}','{$spec2_code}','{$sku}',NULL,'{$gb_code}') "
                                . " ON DUPLICATE KEY UPDATE barcode=VALUES(barcode),sku=VALUES(sku),gb_code=VALUES(gb_code)";
                    } else if ((!empty($barcode) || trim($barcode) != '') && (empty($gb_code) || trim($gb_code) == '')) {
                        $sql = "INSERT  INTO goods_barcode (goods_code,spec1_code,spec2_code,sku,barcode,gb_code)"
                                . " VALUES('{$goods_code}','{$spec1_code}','{$spec2_code}','{$sku}','{$barcode}',NULL) "
                                . " ON DUPLICATE KEY UPDATE barcode=VALUES(barcode),sku=VALUES(sku),gb_code=VALUES(gb_code)";
                    } else {
                        $sql = "INSERT  INTO goods_barcode (goods_code,spec1_code,spec2_code,sku,barcode,gb_code)"
                                . " VALUES('{$goods_code}','{$spec1_code}','{$spec2_code}','{$sku}','{$barcode}','{$gb_code}') "
                                . " ON DUPLICATE KEY UPDATE barcode=VALUES(barcode),sku=VALUES(sku),gb_code=VALUES(gb_code)";
                    }
                    $ret = $this->db->query($sql);
                } else {
                    $this->check_data[] = $sku;
                }
            }
        }

        $up_sql = "update  goods_sku,goods_barcode set
                            goods_sku.barcode=goods_barcode.barcode,goods_sku.gb_code=goods_barcode.gb_code
                           where  goods_sku.sku=goods_barcode.sku AND goods_sku.goods_code='{$goods_code}'";
        $this->db->query($up_sql);

        $goods_lastchanged_sql = "update base_goods set
                            base_goods.lastchanged=now()
                           where base_goods.goods_code='{$goods_code}'";
        $this->db->query($goods_lastchanged_sql);

        $spec1_sql = "update  goods_sku,base_spec1 set
                            goods_sku.spec1_name=base_spec1.spec1_name
                           where  goods_sku.spec1_code=base_spec1.spec1_code AND goods_sku.goods_code='{$goods_code}'";
        $this->db->query($spec1_sql);

        $spec2_sql = "update  goods_sku,base_spec2 set
                            goods_sku.spec2_name=base_spec2.spec2_name
                           where  goods_sku.spec2_code=base_spec2.spec2_code AND goods_sku.goods_code='{$goods_code}'";
        $this->db->query($spec2_sql);

        if ($ret) {
            $id = $this->db->insert_id();
            return $this->format_ret($is_status, $id, $message);
        } else {
            return $this->format_ret("-1", '', $message);
        }
    }

    public function check_is_use($goods_code, $spec1_code, $spec2_code, $sku) {
        $sql = "select goods_code,spec1_code,spec2_code from goods_sku where sku='{$sku}' ";
        $row = $this->db->get_row($sql);

        if (!empty($row)) {
            if ($goods_code != $row['goods_code'] || $spec1_code != $row['spec1_code'] || $spec2_code != $row['spec2_code']) {
                return FALSE;
            }
        }
        return true;
    }

    public function check_is_use_barcode($sku = '') {
        $sql_values = array(':sku' => $sku);
        $sql1 = "select  1 from goods_inv where sku=:sku ";
        $row1 = $this->db->get_row($sql1, $sql_values);

        $sql2 = "select 1  from oms_sell_record_detail where sku=:sku ";
        $row2 = $this->db->get_row($sql2, $sql_values);
        $ret_status = FALSE;
        if (!empty($row1) || !empty($row2)) {
            $ret_status = TRUE;
        }
        return $ret_status;
    }

    public function check_is_use_gb_code($sku = '') {

        $sql_values = array(':sku' => $sku);
        $sql1 = "select  1 from goods_inv where sku=:sku ";
        $row1 = $this->db->get_row($sql1, $sql_values);

        $sql2 = "select 1  from oms_sell_record_detail where sku=:sku ";
        $row2 = $this->db->get_row($sql2, $sql_values);
        $ret_status = FALSE;
        if (!empty($row1) || !empty($row2)) {
            $ret_status = TRUE;
        }
        return $ret_status;
    }

    public function get_sku_by_bacorde($barcode) {
        return $this->db->get_row("select sku from goods_barcode where barcode=:barcode", array(':barcode' => $barcode));
    }

    function save_barcode_gb_code_by_sku($barcode, $sku, $gb_code) {
        $row = $this->get_data_by_barcode($barcode);
        $is_gb_code = $this->get_data_by_barcode_gb_code($barcode);
        $old_barcode = load_model('goods/SkuCModel')->get_barcode($sku);

        $ret = array();
        if ($old_barcode == $barcode) {
            $ret = $this->save_gb_code_by_sku($gb_code, $sku);
        } else {
            if (!empty($is_gb_code)) {
                return $this->format_ret(-1, '', '此条码与国标码冲突，请重新输入或先修改已存在的国标码');
            }
            if (!empty($row) && $sku != $row['sku'] && !empty($barcode)) {
                $ret = $this->format_ret(-1, '', '此条码系统已存在，请重新输入或者先修改已存在的条码');
            } else {
                $barcode = $barcode == '' ? NULL : $barcode;
                $data = array('barcode' => $barcode);
                $where = array('sku' => $sku);
                $this->update_exp('goods_barcode', $data, $where);
                $ret = $this->update_exp('goods_sku', $data, $where);
                //同步更新base_goods表时间
                $now_row = $this->get_data_by_barcode($barcode);
                $lastchanged = array('lastchanged' => $now_row['lastchanged']);
                $where_lanchanged = array('goods_code' => $now_row['goods_code']);
                $this->update_exp('base_goods', $lastchanged, $where_lanchanged);
            }
            $module = '商品'; //模块名称
            $yw_code = $sku; //业务编码
            $operate_type = '编辑';

            if ($old_barcode != $barcode) {
                $log_xq = "条码：{$old_barcode}变更为{$barcode}";
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);

                load_model('sys/OperateLogModel')->insert($log);
            }
        }
        return $ret;
    }

    function save_gb_code_by_sku($gb_code, $sku) {
        $old_gb_code = load_model('goods/SkuCModel')->get_gb_code($sku);

        $ret = array();
        if ($old_gb_code == $gb_code) {
            return $this->format_ret(0, '', '相同码不弹提示');
        }
        $ret = load_model('prm/GoodsModel')->gb_code_exist($gb_code);

        if ($ret['status'] != 0) {
            return $ret;
        } else {
            $gb_code = $gb_code == '' ? NULL : $gb_code;
            $data = array('gb_code' => $gb_code);
            $where = array('sku' => $sku);
            $this->update_exp('goods_barcode', $data, $where);
            $ret = $this->update_exp('goods_sku', $data, $where);
            //同步更新base_goods表时间
            $now_row = $this->get_data_by_gb_code($gb_code);
            $lastchanged = array('lastchanged' => $now_row['lastchanged']);
            $where_lanchanged = array('goods_code' => $now_row['goods_code']);
            $this->update_exp('base_goods', $lastchanged, $where_lanchanged);
        }
        $module = '商品'; //模块名称
        $yw_code = $sku; //业务编码
        $operate_type = '编辑';

        if ($old_gb_code != $gb_code) {
            $log_gb = "国标码：{$old_gb_code}变更为{$gb_code}";
            $_log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_gb);

            load_model('sys/OperateLogModel')->insert($_log);
        }

        return $ret;
    }

    function get_data_by_barcode($barcode) {
        return $this->db->get_row("select * from goods_barcode where barcode=:barcode", array(':barcode' => $barcode));
    }

    function get_data_by_barcode_gb_code($barcode) {
        return $this->db->get_row("select * from goods_barcode where gb_code=:barcode", array(':barcode' => $barcode));
    }

    function get_data_by_gb_code($gb_code) {
        return $this->db->get_row("select * from goods_barcode where gb_code=:gb_code", array(':gb_code' => $gb_code));
    }

    public function get_use_sku_by_goods_code($goods_code) {
        $sql = "select sku from oms_sell_record_detail where goods_code='{$goods_code}' ";
        $row1 = $this->db->get_row($sql);

        $sql = "
             select DISTINCT a.sku ,b.goods_code,b.spec1_code,b.spec2_code,b.barcode,b.gb_code  from
            (
            select DISTINCT  sku from goods_inv where goods_code='{$goods_code}'
             UNION ALL
                select DISTINCT sku from oms_sell_record_detail where  goods_code='{$goods_code}'
             )  a left join goods_barcode b on a.sku = b.sku  ";

        $data = $this->db->get_all($sql);
        $sku_arr = array();
        foreach ($data as $val) {
            $sku_arr[$val['sku']] = $val;
        }
        return $sku_arr;
    }

    public function get_user_sku() {
        return $this->check_data;
    }

    /*
     * 多表查询
     * */

    public function get_by_search($goods_code) {
        $tbl = $this->table;
        $sql = "SELECT
                    r1.spec1_code AS spec1_code,
                    r1.spec2_code AS spec2_code,
                    r1.sku AS sku,
                    r1.cost_price AS cost_price,
                    r1.price AS price,
                    r1.weight AS weight,
                    r1.remark as remark,
                    r2.barcode AS barcode,
                    r2.gb_code AS gb_code,
                    r1.spec2_name,
                    r1.spec1_name
                FROM
                    {$tbl} r1
                LEFT JOIN goods_barcode r2 ON r1.goods_code = r2.goods_code
                AND r1.spec2_code = r2.spec2_code
                AND r1.spec1_code = r2.spec1_code
                WHERE
                    r1.goods_code = '{$goods_code}'";
        //echo $sql;die;
        $rs = $this->db->get_all($sql);
        return $rs;
    }

    function get_barcode_comb_by_goods_code($goods_code) {
        $sql = "SELECT
                    g.goods_code,
                    s.spec1_code,
                    s.spec2_code,
                    s.sku AS sku,
                    s.price AS price,
                    s.weight AS weight,
                    s.remark as remark,
                    s.barcode AS barcode,
                    s.gb_code AS gb_code,
                    s.spec1_name,
                    s.spec2_name,
                    s.cost_price
               FROM base_goods g
               INNER JOIN goods_sku s ON s.goods_code=g.goods_code
               WHERE g.goods_code = '{$goods_code}'";
        $rs = $this->db->get_all($sql);

        $spec1_arr = array_column($rs, 'spec1_code', 'spec1_name');
        $spec2_arr = array_column($rs, 'spec2_code', 'spec2_name');

        $spec = array();
        foreach ($spec1_arr as $name1 => $spec1) {
            foreach ($spec2_arr as $name2 => $spec2) {
                $k = $spec1 . $spec2;
                $spec[$k]['spec1_code'] = $spec1;
                $spec[$k]['spec1_name'] = $name1;
                $spec[$k]['spec2_code'] = $spec2;
                $spec[$k]['spec2_name'] = $name2;
            }
        }

        $exists_spec = array();
        foreach ($rs as &$v) {
            $exists_spec[] = $v['spec1_code'] . $v['spec2_code'];
            if (empty($v['sku'])) {
                $val['sku'] = $v['goods_code'] . $v['spec1_code'] . $v['spec2_code'];
            }
        }

        foreach ($spec as $key => $val) {
            if (in_array($key, $exists_spec)) {
                unset($spec[$key]);
                continue;
            }
            $spec[$key]['goods_code'] = $goods_code;
            $spec[$key]['sku'] = $goods_code . $val['spec1_code'] . $val['spec2_code'];
            $spec[$key]['price'] = '0.000';
            $spec[$key]['weight'] = '0.000';
            $spec[$key]['remark'] = '';
            $spec[$key]['barcode'] = '';
            $spec[$key]['gb_code'] = '';
            $spec[$key]['cost_price'] = '0.000';
        }

        $rs = array_merge($rs, $spec);
        return $rs;
    }

    /* 判断是否自定义规格参数关闭，如果关闭，并且是组装商品，自动添加信息至sku和barcode表 */

    function set_spec($goods_code) {
        $sql_get = "select count(1) FROM base_goods g INNER JOIN goods_sku s ON s.goods_code=g.goods_code WHERE g.goods_code = '{$goods_code}' and s.spec1_code='000' and s.spec2_code='000'";
        $t = $this->db->get_value($sql_get);
        $spec1 = load_model('prm/Spec1Model')->get_by_code('000');
        $spec2 = load_model('prm/Spec2Model')->get_by_code('000');
        if ($t == 0) {
            parent::insert_exp('goods_barcode', array('goods_code' => $goods_code, 'spec1_code' => '000', 'spec2_code' => '000', 'barcode' => $goods_code, 'sku' => $goods_code . '000000'));
            parent::insert_exp('goods_sku', array('goods_code' => $goods_code, 'spec1_code' => '000', 'spec1_name' => $spec1['data']['spec1_name'], 'spec2_code' => '000', 'spec2_name' => $spec2['data']['spec2_name'], 'barcode' => $goods_code, 'sku' => $goods_code . '000000'));
        }
    }

    /*
     * 删除记录
     * */

    function delete($sku_id) {
        $sql = "select goods_code,spec1_code,spec2_code FROM goods_sku where sku_id = '{$sku_id}' limit 1 ";
        $rs = $this->db->get_all($sql);
        $sql = "delete from goods_barcode where goods_code ='{$rs[0][goods_code]}' and spec1_code = '{$rs[0][spec1_code]}' and spec2_code = '{$rs[0][spec2_code]}'";
        $ret = $this->db->query($sql);
        $ret = parent::delete(array('sku_id' => $sku_id));
        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($goods_code, $spec1_code, $spec2_code, $is_edit = false) {
        if (!$is_edit && (!isset($goods_code) || !valid_input($goods_code, 'required')))
            return GOODS_ERROR_CODE1;
        if (!isset($spec1_code) || !valid_input($spec1_code, 'required'))
            return GOODS_ERROR_NAME2;
        if (!isset($spec2_code) || !valid_input($spec2_code, 'required'))
            return GOODS_ERROR_NAME3;
        return 1;
    }

    /**
     * 判断是否存在
     * @param $value
     * @param string $field_name
     * @return array
     */
    function is_exists($value, $field_name = 'barcode_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    /*
     * 添加新纪录
     */

    function insert($data) {

        $ret = parent::insert($data);

        return $ret;
    }

    //修改条形码
    function update_save($b) {
        $this->begin_trans();
        try {
            foreach ($b as $id => $barcode) {

                $r = $this->db->update('goods_barcode', array('barcode' => $barcode), array('barcode_id' => $id));
                if ($r !== true) {
                    throw new Exception('保存失败');
                }
            }

            $this->commit();
            return array('status' => 1, 'message' => '更新成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => $e->getMessage());
        }
    }

    /**
     * 生成barcode 目前自动建档使用
     * @param $data array(0=>'goods_code', 'spec1_code', 'spec2_code','sku','barcode')
     */
    function _save_barcode($data) {

        $this->init_table('goods_barcode');
        if (!$data) {
            return false;
        }

        foreach ($data as $_val) {
            if (!isset($_val['barcode']) || '' == $_val['barcode']) {
                $_val['barcode'] = $_val['goods_code'] . $_val['spec1_code'] . $_val['spec2_code'];
            }

            if (!isset($_val['sku']) || '' == $_val['sku']) {
                $_val['sku'] = $_val['goods_code'] . $_val['spec1_code'] . $_val['spec2_code'];
            }

            $check_exist = $this->is_exists($_val['barcode'], 'barcode');
            if ($check_exist['data']) {
                continue;
            }

            $insert_data = array(
                'goods_code' => $_val['goods_code'],
                'sku' => $_val['sku'],
                'barcode' => $_val['barcode'],
                'spec1_code' => $_val['spec1_code'],
                'spec2_code' => $_val['spec2_code'],
                'price' => $_val['price']
            );

            $this->insert($insert_data);
        }
    }

    //商品编码
    function get_barcode_list($arr) {
        $sql = "select * FROM goods_barcode  where 1 ";
        foreach ($arr as $key1 => $value1) {
            $key = substr($key1, 1);
            $sql .= " and {$key} = {$key1} ";
        }
        $rs = $this->db->get_all($sql, $arr);
        return $rs;
    }

    //组合商品
    function get_diy_list($arr) {
        $sql = "select * FROM goods_diy where 1 ";
        foreach ($arr as $key1 => $value1) {
            $key = substr($key1, 1);
            $sql .= " and {$key} = {$key1} ";
        }

        $rs = $this->db->get_all($sql, $arr);

        return $rs;
    }

    /**
     *
     * 方法名                               api_goods_barcode_update
     *
     * 功能描述                           更新商品条型码
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-11
     * @param       array $param
     *              array(
     *                  必填: 'goods_code', 'spec1_code', 'spec2_code', 'barcode'
     *                  可选: 'weight', 'price', 'gb_code'
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_goods_barcode_update($param) {
        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
            's' => array('goods_code', 'spec1_code', 'spec2_code', 'barcode')
        );
        $barcode_info = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $barcode_info, TRUE);
        if (TRUE == $ret_required['status']) {
            //可选字段
            $key_option = array(
                's' => array('gb_code'),
                'i' => array('weight', 'price')
            );
            $arr_option = array();
            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($param, $key_option, $arr_option);

            //合并数据
            $barcode_info = array_merge($barcode_info, $arr_option);

            unset($param);

            $goods_code = $barcode_info['goods_code'];
            $barcode = $barcode_info['barcode'];
            $spec1_code = $barcode_info['spec1_code'];
            $spec2_code = $barcode_info['spec2_code'];

            //检查客户传入的产品是否存在
            $goods = load_model('prm/GoodsModel')->get_by_goods_code($goods_code);
            if (1 != $goods['status']) {
                return $this->format_ret("-10002", array('goods_code' => $goods_code), "API_RETURN_MESSAGE_10002");
            }

            //检查客户传入的规格1是否存在
            $spec1 = load_model('prm/Spec1Model')->get_by_code($spec1_code);
            if (1 != $spec1['status']) {
                return $this->format_ret("-10002", array('spec1_code' => $spec1_code), "API_RETURN_MESSAGE_10002");
            }

            //检查客户传入的规格2是否存在
            $spec2 = load_model('prm/Spec2Model')->get_by_code($spec2_code);
            if (1 != $spec2['status']) {
                return $this->format_ret("-10002", array('spec2_code' => $spec2_code), "API_RETURN_MESSAGE_10002");
            }

            //因为有三表操作作故开启事务
            $this->begin_trans();
            try {
                //检测规格1，不存在则添加
//	            $good_spec1_mod = load_model('prm/GoodsSpec1Model');
//	            $ret = $good_spec1_mod->is_exists(array('goods_code' => $goods_code, 'spec1_code' => $spec1_code));
//	            if (1 != $ret['status']) {
//	                $ret = $good_spec1_mod->save($goods_code, $spec1_code);
//	                if (1 != $ret['status']) {
//	                    throw new Exception(lang('API_SPEC1_CODE_UPDATE_FAILD'), -1);
//	                }
//	            }
//	            //检测规格2，不存在则添加
//	            $good_spec2_mod = load_model('prm/GoodsSpec2Model');
//	            $ret = $good_spec2_mod->is_exists(array('goods_code' => $goods_code, 'spec2_code' => $spec2_code));
//	            if (1 != $ret['status']) {
//	                $ret = $good_spec2_mod->save($goods_code, $spec2_code);
//	                if (1 != $ret['status']) {
//	                    throw new Exception(lang('API_SPEC2_CODE_UPDATE_FAILD'), -1);
//	                }
//	            }
                //检查商品sku是否存在
                $sku_code = $goods_code . $spec1_code . $spec2_code;
                $sku = load_model('prm/SkuModel')->is_exists($sku_code);
                $r = true;
                if (1 == $sku['status']) {
                    if (!empty($arr_option)) {
                        $arr_option['barcode'] = $barcode;
                        $r = $this->db->update('goods_sku', $arr_option, array('sku_id' => $sku['data']['sku_id']));
                    }
                } else {
                    //检查“商品sku”表中goods_code|spec1_code|spec2_code组合唯一键是否存在
                    $filter = array('goods_code' => $goods_code, 'spec1_code' => $spec1_code, 'spec2_code' => $spec2_code);
                    $ret = load_model('prm/SkuModel')->check_exists_by_condition($filter, 'goods_sku');
                    if (1 == $ret['status']) {
                        throw new Exception(lang('API_CODE_SPEC1_SPEC2_UNIQUE'), -10004);
                    } else {
                        $sku_array = array(
                            'goods_code' => $barcode_info['goods_code'], 'spec1_code' => $barcode_info['spec1_code'],
                            'spec2_code' => $barcode_info['spec2_code'], 'sku' => $sku_code,
                            'gb_code' => $barcode_info['gb_code'], 'weight' => $barcode_info['weight'],
                            'price' => $barcode_info['price'],
                            'barcode' => $barcode,
                        );
                        $r = $this->db->insert('goods_sku', $sku_array);
                    }
                }
                if (true !== $r) {
                    throw new Exception(lang('update_error'), -1);
                }

                //检查商品条码库中是否存在客户上传的barcode
                $ret = $this->check_exists_by_condition(array('barcode' => $barcode), 'goods_barcode');
                if (1 == $ret['status']) {
                    //$r = $this->db->update('goods_barcode', array('barcode' => $barcode), array('barcode_id' => $ret['data']['barcode_id']));
                    $this->commit();
                    return $this->format_ret("1", '', "update_success");
                    //throw new Exception('该产品条形码已经存在');
                } else {
                    //检查“商品条码”表中goods_code|spec1_code|spec2_code组合唯一键是否存
                    /* $filter = array('goods_code' => $goods_code, 'spec1_code' => $spec1_code, 'spec2_code' => $spec2_code);
                      $ret = $this->check_exists_by_condition($filter, 'goods_barcode');
                      if (1 == $ret['status']) {
                      throw new Exception('商品条码数据goods_code|spec1_code|spec2_code组合唯一键出现重复');
                      } else {
                      $r = $this->db->insert('goods_barcode', array('goods_code' => $goods_code, 'spec1_code' => $spec1_code, 'spec2_code' => $spec2_code, 'barcode' => $barcode, 'sku' => $sku_code));
                      if (true !== $r) {
                      throw new Exception('barcode更新失败');
                      }
                      }
                      //以前操作均成功，提交事务
                      $this->commit();
                      return $this -> format_ret("1", '', "insert_success"); */
                    throw new Exception(lang('API_GOODS_BARCODE_NO_EXISTS'), -10002);
                }
            } catch (Exception $e) {
                //异常处理，回滚事务
                $this->rollback();
                return array('status' => $e->getCode(), 'message' => $e->getMessage());
            }
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
    }

    /**
     *
     * 方法名                               api_goods_barcode_add
     *
     * 功能描述                           添加商品条型码
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-13
     * @param       array $param
     *              array(
     *                  必填: 'goods_code', 'spec1_code', 'spec2_code', 'barcode'
     *                  可选: 'weight', 'price', 'gb_code'
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_goods_barcode_add($param) {
        //过滤特殊字符
        $param['goods_code'] = htmlentities($param['goods_code']);
        $param['barcode'] = htmlentities($param['barcode']);
        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
            's' => array('goods_code', 'spec1_code', 'spec2_code', 'barcode')
        );
        $barcode_info = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $barcode_info, TRUE);
        if (TRUE == $ret_required['status']) {
            //可选字段
            $key_option = array(
                's' => array('gb_code'),
                'i' => array('weight', 'price')
            );
            $arr_option = array();
            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($param, $key_option, $arr_option);

            //合并数据
            $barcode_info = array_merge($barcode_info, $arr_option);

            $spec1_name = $param['spec1_name'];
            $spec2_name = $param['spec2_name'];

            unset($param);

            $goods_code = $barcode_info['goods_code'];
            $barcode = $barcode_info['barcode'];
            $spec1_code = $barcode_info['spec1_code'];
            $spec2_code = $barcode_info['spec2_code'];

            //检查客户传入的产品是否存在
            $goods = load_model('prm/GoodsModel')->get_by_goods_code($goods_code);
            if (1 != $goods['status']) {
                return $this->format_ret("-10002", array('goods_code' => $goods_code), "API_RETURN_MESSAGE_10002");
            }

            //检查客户传入的规格1是否存在
            $spec1 = load_model('prm/Spec1Model')->get_by_code($spec1_code);
            if (1 != $spec1['status']) {
                return $this->format_ret("-10002", array('spec1_code' => $spec1_code), "API_RETURN_MESSAGE_10002");
            }

            //检查客户传入的规格2是否存在
            $spec2 = load_model('prm/Spec2Model')->get_by_code($spec2_code);
            if (1 != $spec2['status']) {
                return $this->format_ret("-10002", array('spec2_code' => $spec2_code), "API_RETURN_MESSAGE_10002");
            }

            //因为有三表操作作故开启事务
            $this->begin_trans();
            try {
                //检测规格1，不存在则添加
//	            $good_spec1_mod = load_model('prm/GoodsSpec1Model');
//	            $ret = $good_spec1_mod->is_exists(array('goods_code' => $goods_code, 'spec1_code' => $spec1_code));
//	            if (1 != $ret['status']) {
//	                $ret = $good_spec1_mod->save($goods_code, $spec1_code);
//	                if (1 != $ret['status']) {
//	                    throw new Exception(lang('API_SPEC1_CODE_UPDATE_FAILD'), -1);
//	                }
//	            }
//	            //检测规格2，不存在则添加
//	            $good_spec2_mod = load_model('prm/GoodsSpec2Model');
//	            $ret = $good_spec2_mod->is_exists(array('goods_code' => $goods_code, 'spec2_code' => $spec2_code));
//	            if (1 != $ret['status']) {
//	                $ret = $good_spec2_mod->save($goods_code, $spec2_code);
//	                if (1 != $ret['status']) {
//	                    throw new Exception(lang('API_SPEC2_CODE_UPDATE_FAILD'), -1);
//	                }
//	            }
                //检查商品sku是否存在
                $sku_code = $goods_code . $spec1_code . $spec2_code;
                $sku = load_model('prm/SkuModel')->is_exists($sku_code);
                $r = true;
                if (1 == $sku['status']) {
                    if (!empty($arr_option)) {
                        $arr_option['barcode'] = $barcode;
                        $r = $this->db->update('goods_sku', $arr_option, array('sku_id' => $sku['data']['sku_id']));
                    }
                } else {
                    //检查“商品sku”表中goods_code|spec1_code|spec2_code组合唯一键是否存在
                    $filter = array('goods_code' => $goods_code, 'spec1_code' => $spec1_code, 'spec2_code' => $spec2_code);
                    $ret = load_model('prm/SkuModel')->check_exists_by_condition($filter, 'goods_sku');
                    if (1 == $ret['status']) {
                        throw new Exception(lang('API_CODE_SPEC1_SPEC2_UNIQUE'), -10004);
                    } else {
                        $sku_array = array(
                            'goods_code' => $barcode_info['goods_code'],
                            'spec1_code' => $barcode_info['spec1_code'],
                            'spec2_code' => $barcode_info['spec2_code'],
                            'spec1_name' => $spec1['data']['spec1_name'],
                            'spec2_name' => $spec2['data']['spec2_name'],
                            'sku' => $sku_code,
                            'gb_code' => $barcode_info['gb_code'],
                            'weight' => $barcode_info['weight'],
                            'price' => $barcode_info['price'],
                            'barcode' => $barcode,
                        );
                        $r = $this->db->insert('goods_sku', $sku_array);
                    }
                }
                if (true !== $r) {
                    $ret = $this->format_ret("-1", '', "update_error");
                    throw new Exception(lang('update_error'), -1);
                }

                //检查商品条码库中是否存在客户上传的barcode
                $ret = $this->check_exists_by_condition(array('barcode' => $barcode), 'goods_barcode');
                if (1 == $ret['status']) {
                    //$r = $this->db->update('goods_barcode', array('barcode' => $barcode), array('barcode_id' => $ret['data']['barcode_id']));
                    //$this->commit();
                    //return $this -> format_ret("1", '', "update_success");
                    throw new Exception(lang('API_GOODS_BARCODE_EXISTS'), -10004);
                } else {
                    //检查“商品条码”表中goods_code|spec1_code|spec2_code组合唯一键是否存
                    $filter = array('goods_code' => $goods_code, 'spec1_code' => $spec1_code, 'spec2_code' => $spec2_code);
                    $ret = $this->check_exists_by_condition($filter, 'goods_barcode');
                    if (1 == $ret['status']) {
                        $ret = $this->format_ret("-1", array('goods_code' => $goods_code, 'spec1_code' => $spec1_code, 'spec2_code' => $spec2_code), "API_CODE_SPEC1_SPEC2_IN_BARCODE_UNIQUE");
                        throw new Exception(lang('API_CODE_SPEC1_SPEC2_IN_BARCODE_UNIQUE'), -10004);
                    } else {
                        $r = $this->db->insert('goods_barcode', array('goods_code' => $goods_code, 'spec1_code' => $spec1_code, 'spec2_code' => $spec2_code, 'barcode' => $barcode, 'sku' => $sku_code));
                        if (true !== $r) {
                            throw new Exception(lang('insert_error'), -1);
                        }
                    }
                    //以前操作均成功，提交事务
                    $this->commit();
                    return $this->format_ret("1", '', "insert_success");
                }
            } catch (Exception $e) {
                //异常处理，回滚事务
                $this->rollback();
                return array('status' => $e->getCode(), 'message' => $e->getMessage());
            }
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
    }

    public function get_create_barcode_status() {
        $barcode_status = $this->is_create_barcode;
        $barcode_status_arr = array();
        $i = 0;
        foreach ($barcode_status as $key => $value) {
            $barcode_status_arr[$i]['is_create_barcode_code'] = $key;
            $barcode_status_arr[$i]['is_create_barcode_name'] = $value;
            $i++;
        }
        return $barcode_status_arr;
    }

    function get_sku_by_barcode($barocde) {
        $barocde = trim($barocde);
        $sql = "select * from (
            (select sku from goods_sku where barcode like :barcode)
                  UNION
                (select sku from goods_barcode_child where barcode like :barcode))  a";
        $data = $this->db->get_all($sql, array(':barcode' => '%' . $barocde . '%'));
        $sku_data = array();
        foreach ($data as $val) {
            $sku_data[] = $val['sku'];
        }
        return $sku_data;
    }

    //解析条码,返回sku
    function check_barcode($barcode) {
        $sku = oms_tb_val('goods_sku', 'sku', array('barcode' => $barcode));
        if (empty($sku)) {
            //子条码查询
            $sql = "select sku from goods_barcode_child where barcode = :barcode";
            $sku = $this->db->get_value($sql, array('barcode' => $barcode));
            if (empty($sku)) {
                //根据条码规则识别
                $barcode_rule_ret = load_model('prm/GoodsBarcodeIdentifyRuleModel')->yanzheng($barcode, 1);
                if ($barcode_rule_ret['status'] > 0) {
                    $sku = $barcode_rule_ret['data']['sku'];
                } else {
                    //查询是否开启唯一码
                    $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status'));
                    if ($unique_arr['unique_status'] == 1) {
                        //开启唯一码查询:扫描条码是否 是唯一码
                        $sql = "select sku,status from goods_unique_code where unique_code = :unique_code";
                        $unique_ret = $this->db->get_row($sql, array('unique_code' => $barcode));
                        if (empty($unique_ret)) {
                            return $this->format_ret(-1, '', '系统中不存在该条码');
                        } else if ($unique_ret['status'] == 1) {
                            return $this->format_ret(-1, '', '系统已开启唯一码，且唯一码为不可用状态！');
                        } else {
                            $sku = $unique_ret['sku'];
                        }
                    } else {
                        return $this->format_ret(-1, '', '系统中不存在该条码');
                    }
                }
            }
        }
        return $this->format_ret(1, $sku);
    }

    //获取条码打印数据
    function get_print_barcode_data($filter) {
        $ret = array();
        $ret['filter']['page'] = 1;
        if ($filter['list_type'] == 1) {
            //获取系统sku_id
            $ids = load_model('pur/PurchaseRecordDetailModel')->get_sku_id_by_pid($filter['record_ids']);
            // 循环打印
            foreach ($ids as $id) {
                if ($id['num'] == 0) {
                    $data = $this->print_data_default($id['sku_id'], 'lodop');
                    $ret['data'][] = $data;
                } else {
                    for ($i = 1; $i <= $id['num']; $i++) {
                        $data = $this->print_data_default($id['sku_id'], 'lodop');
                        $ret['data'][] = $data;
                    }
                }
            }
        } else if ($filter['list_type'] == 3) {
            //获取系统sku_id
            $ids = load_model('wbm/StoreOutRecordDetailModel')->get_sku_id_by_pid($filter['record_ids']);
            // 循环打印
            foreach ($ids as $id) {
                if ($id['num'] == 0) {
                    $data = $this->print_data_default($id['sku_id'], 'lodop');
                    $ret['data'][] = $data;
                } else {
                    for ($i = 1; $i <= $id['num']; $i++) {
                        $data = $this->print_data_default($id['sku_id'], 'lodop');
                        $ret['data'][] = $data;
                    }
                }
            }
        } else {
            // 循环打印
            $sku_id_arr = explode(",", $filter['record_ids']);
            $sku_params = array();
            $print_num = array();
            foreach ($sku_id_arr as $id) {
                $sku_arr = explode("_", $id);
                $sku_params[] = $sku_arr[0];
                $print_num[$sku_arr[0]] = $sku_arr[1];
            }
            foreach ($sku_params as $val) {
                $data = $this->print_data_default($val, 'lodop');
                $print_num[$val] = !empty($print_num[$val]) ? $print_num[$val] : 1;
                for ($i = 1; $i <= $print_num[$val]; $i++) {
                    $ret['data'][] = $data;
                }
            }
        }
        $ret['filter']['page_count'] = count($ret['data']);

        return $this->format_ret(1, $ret, '');
    }

}
