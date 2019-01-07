<?php

/**
 * 商品批次级库存帐管理相关业务
 *
 * @author jia.ceng
 *
 */
require_model('tb/TbModel');
require_lang('prm');

class InvLofModel extends TbModel {

    //put your code here
    function get_table() {
        return 'goods_inv_lof';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_join = "";

        $sql_main = "FROM {$this->table} r1 
        			INNER JOIN goods_inv r3 on r1.sku = r3.sku and r1.store_code = r3.store_code  
        			INNER JOIN base_goods r2 on r1.goods_code = r2.goods_code
        
        			WHERE 1";
        
                $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
                $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code);
                //品牌权限        
        $filter_brand_code= isset($filter['brand_code']) ? $filter['brand_code'] : null;
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
        //保质期
        if (isset($filter['period_validity_start']) && $filter['period_validity_start'] !== '') {
            $sql_main .= " AND r2.period_validity >= :period_validity_start ";
            $sql_values[':period_validity_start'] = $filter['period_validity_start'];
        }
        if (isset($filter['period_validity_end']) && $filter['period_validity_end'] !== '') {
            $sql_main .= " AND r2.period_validity <= :period_validity_end ";
            $sql_values[':period_validity_end'] = $filter['period_validity_end'];
        }
        //剩余可卖月
        if (isset($filter['sellmonth_start']) && $filter['sellmonth_start'] !== '') {
            $sql_main .= " AND DATEDIFF(DATE_ADD(r1.production_date,INTERVAL r2.period_validity MONTH),curdate())/30 >= :sellmonth_start ";
            $sql_values[':sellmonth_start'] = $filter['sellmonth_start'];
        }
        if (isset($filter['sellmonth_end']) && $filter['sellmonth_end'] !== '') {
            $sql_main .= " AND DATEDIFF(DATE_ADD(r1.production_date,INTERVAL r2.period_validity MONTH),curdate())/30 <= :sellmonth_end ";
            $sql_values[':sellmonth_end'] = $filter['sellmonth_end'];
        }
        //失效日期
        if (isset($filter['lost_validity_start']) && $filter['lost_validity_start'] !== '') {
            $sql_main .= " AND DATE_ADD(r1.production_date,INTERVAL r2.period_validity MONTH) >= :lost_validity_start ";
            $sql_values[':lost_validity_start'] = $filter['lost_validity_start'];
        }
        if (isset($filter['lost_validity_end']) && $filter['lost_validity_end'] !== '') {
            $sql_main .= " AND DATE_ADD(r1.production_date,INTERVAL r2.period_validity MONTH) <= :lost_validity_end ";
            $sql_values[':lost_validity_end'] = $filter['lost_validity_end'];
        }
        //可用库存
        if (isset($filter['effec_num_start']) && $filter['effec_num_start'] !== '') {
            $sql_main .= " AND (r1.stock_num-CAST(r1.lock_num AS SIGNED)) >= :effec_num_start ";
            $sql_values[':effec_num_start'] = $filter['effec_num_start'];
        }
        if (isset($filter['effec_num_end']) && $filter['effec_num_end'] !== '') {
            $sql_main .= " AND (r1.stock_num-CAST(r1.lock_num AS SIGNED)) <= :effec_num_end ";
            $sql_values[':effec_num_end'] = $filter['effec_num_end'];
        }
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r1.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND (r2.goods_name LIKE :goods_name )";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        //启用状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND r2.status = :status ";
            $sql_values[':status'] = $filter['status'];
        }
        //批次号
        if (isset($filter['lof_no']) && $filter['lof_no'] != '') {
            $sql_main .= " AND (r1.lof_no LIKE :lof_no )";
            $sql_values[':lof_no'] = '%' . $filter['lof_no'] . '%';
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $sql_main .= " AND r1.store_code in (:store_code) ";
            $sql_values[':store_code'] = explode(',', $filter['store_code']);
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {

            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND r3.sku in({$sku_str}) ";
            }
        }
        $select = 'r3.*,r2.goods_name,r2.weight,r1.lof_no,r1.production_date,r1.stock_num,r1.lock_num,r2.sell_price,r2.cost_price,r2.period_validity';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        //   filter_fk_name($data['data'], array('spec1_code|spec1_code','spec2_code|spec2_code','store_code|store','sku|barcode'));
        filter_fk_name($data['data'], array('store_code|store'));
        $ret_data = $data;
        foreach ($ret_data['data'] as $key => &$value) {
            //  $url = "?app_act=prm/inv/lock_detail&store_code={$value['store_code']}&sku={$value['sku']}&first_mode=lof_mode&lof_no={$value['lof_no']}";
            $value['effec_num'] = $value['stock_num'] - $value['lock_num'];
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($sku_info, $value);

            $value['spec1_code_name'] = $value['spec1_name'] . "(" . $value['spec1_code'] . ")";
            $value['spec2_code_name'] = $value['spec2_name'] . "(" . $value['spec2_code'] . ")";
            $production_date = $value['production_date'];
            $period_validity = $value['period_validity'];
            if (!isset($period_validity) || $period_validity == '') {
                $period_validity = 0;
            }
            if($production_date == '0000-00-00') {
                $value['lost_validity'] = $production_date;
            } else {
                $value['lost_validity'] = date('Y-m-d', strtotime("$production_date +$period_validity month")); //失效日期   
            }
            // $value['lock_num'] = "<a onclick=javascript:openPage('lock_detail','{$url}','实物锁定明细')>".$value['lock_num']."</a>";
        }

        //print_r($data);
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 获取批次的实物库存
     */
    function get_lof_inv_num($params) {
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        if ($data) {
            return $data['stock_num'];
        } else {
            return '';
        }
    }


    /**获取sku批次库存
     * @param $store_code
     * @param $sku_arr
     * @param string $lof_no
     * @return array
     */
    function get_inv_by_sku($store_code, $sku_arr, $lof_no = 'default_lof') {
        $sku_arr = is_array($sku_arr) ? $sku_arr : array($sku_arr);
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT i.stock_num,i.lock_num,i.sku,i.lof_no
                FROM goods_inv_lof i WHERE i.store_code=:store_code AND i.lof_no=:lof_no AND i.sku IN({$sku_str})";
        $sql_values[':store_code'] = $store_code;
        $sql_values[':lof_no'] = $lof_no;
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as &$val) {
            $val['available_num'] = (int)$val['stock_num'] - (int)$val['lock_num'];
            $val['available_num'] = ($val['available_num'] < 0) ? 0 : $val['available_num'];
        }
        return $this->format_ret(1, $data);
    }



}
