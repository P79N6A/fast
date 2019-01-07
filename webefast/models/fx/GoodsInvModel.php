<?php

/**
 * 供应商类型相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('base');
require_lib('util/oms_util', true);

class GoodsInvModel extends TbModel {

    function get_table() {
        return 'goods_inv';
    }
    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl
        			LEFT JOIN base_goods r2 on rl.goods_code = r2.goods_code
                                LEFT JOIN goods_sku gs ON rl.sku=gs.sku
        			WHERE 1";

        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $ret = load_model('fx/GoodsModel')->get_fx_money_goods('rl.goods_code','sql', 'r2.');
        if(!empty($ret)) {
          $sql_main .= $ret['sql'];
          $sql_values = $ret['value'];
        } else {
          $sql_main .= ' AND 1 != 1 ';
        }
        //仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        if (!(isset($filter['store_code']) && $filter['store_code'] != '')) {
            $store_str = load_model('base/StoreModel')->get_fx_store_sql();
            $filter_store_code = empty($store_str) ? NULL : $store_str;
            if (!empty($filter_store_code)) {
                $store_code_arr = explode(',', $filter_store_code);
                $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
                $sql_main .= " AND store_code in ({$store_code_str})";
            } else {
                $sql_main .= " AND 1 != 1";
            }
        } else {
            $store_code_arr = explode(',', $filter['store_code']);
            if (!empty($store_code_arr)) {
                $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
                $sql_main .= " AND store_code in ({$store_code_str})";
            } else {
                $sql_main .= " AND 1 != 1";
            }
        }

        if($filter['login_type'] != 2) {
            //品牌权限
            $filter_brand_code= isset($filter['brand_code']) ? $filter['brand_code'] : null;
            $sql_main .= load_model('prm/BrandModel')->get_sql_purview_brand('r2.brand_code', $filter_brand_code);
        }
        
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
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND ( (rl.goods_code LIKE :goods_code ) OR (r2.goods_name LIKE :goods_code ) )";
            $sql_values[':goods_code'] = '%' . trim($filter['goods_code']) . '%';
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND ( (r2.goods_name LIKE :goods_name ) OR (r2.goods_code LIKE :goods_name ) )";
            $sql_values[':goods_name'] = '%' . trim($filter['goods_name']) . '%';
        }

        if (isset($filter['barcode_remark']) && $filter['barcode_remark'] != '') {
            $sql_main .= " AND gs.remark  LIKE :remark ";
            $sql_values[':remark']  = '%' . trim($filter['barcode_remark']) . '%';
        }
        //启用状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND r2.status = :status ";
            $sql_values[':status'] = $filter['status'];
        }

        //仓库类别
        if (isset($filter['store_type_code']) && $filter['store_type_code'] != '') {
            $store_arr = load_model('base/StoreModel')->get_by_store_code_type($filter['store_type_code']);
            if(!empty($store_arr)){
                $sql_main .= " AND rl.store_code in (:store_type_code) ";
                $sql_values[':store_type_code'] = $store_arr;
            } else {
                $sql_main .= " AND 1 = 2 ";
            }
        }

        //库存查询
        if (isset($filter['num_start']) && $filter['num_start'] != ''&&$filter['barcode_group_val'] !=1) {
            switch ($filter['is_num']){
                case 'effec_num':  //可用库存
                $sql_main .= " AND (cast(rl.stock_num as signed)-cast(rl.lock_num as signed) >= :num_start) ";
                $sql_values[':num_start'] = $filter['num_start'];
                break;
                case 'road_num':  //在途库存
                $sql_main .= " AND (cast(rl.road_num as signed) >= :num_start) ";
                $sql_values[':num_start'] = $filter['num_start'];
                break;
                case 'safe_num':  //安全库存
                $sql_main .= " AND (cast(rl.safe_num as signed) >= :num_start) ";
                $sql_values[':num_start'] = $filter['num_start'];
                break;
                case 'out_num':  //缺货库存
                $sql_main .= " AND (cast(rl.out_num as signed) >= :num_start) ";
                $sql_values[':num_start'] = $filter['num_start'];
                break;
                case 'stock_num':  //实物库存
                $sql_main .= " AND (cast(rl.stock_num as signed)>= :num_start) ";
                $sql_values[':num_start'] = $filter['num_start'];
                break;
            }
;
        }
       if (isset($filter['num_end']) && $filter['num_end'] != ''&&$filter['barcode_group_val'] !=1) {
               switch ($filter['is_num']){
                case 'effec_num':  //可用库存
                $sql_main .= " AND (cast(rl.stock_num as signed)-cast(rl.lock_num as signed) <= :num_end) ";
                $sql_values[':num_end'] = $filter['num_end'];
                break;
                case 'road_num':  //在途库存
                $sql_main .= " AND (cast(rl.road_num as signed) <= :num_end) ";
                $sql_values[':num_end'] = $filter['num_end'];
                break;
                case 'safe_num':  //安全库存
                $sql_main .= " AND (cast(rl.safe_num as signed) <= :num_end) ";
                $sql_values[':num_end'] = $filter['num_end'];
                break;
                case 'out_num':  //缺货库存
                $sql_main .= " AND (cast(rl.out_num as signed) <= :num_end) ";
                $sql_values[':num_end'] = $filter['num_end'];
                break;
                case 'stock_num':  //实物库存
                $sql_main .= " AND (cast(rl.stock_num as signed)<= :num_end) ";
                $sql_values[':num_end'] = $filter['num_end'];
                break;
            }
        }

        //安全库存查询
        if (isset($filter['less_than_safe_num']) && $filter['less_than_safe_num'] != ''&&$filter['barcode_group_val'] !=1) {
            if ($filter['less_than_safe_num'] == 1) {//低于安全库存的商品
                $sql_main .= " AND rl.stock_num < rl.safe_num";
            }
        }

        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            //$sql_main .= " AND (rl.barcode LIKE :barcode )";
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND rl.sku in({$sku_str}) ";
            }
        }


        $is_group = false;
        if($filter['barcode_group_val'] !=1){
            $sql_main .= "  ORDER BY rl.goods_code  , rl.sku ASC ";
            $select = 'rl.*,r2.goods_name,r2.category_code,r2.category_name,r2.brand_name,r2.year_name,r2.season_name,r2.weight,r2.sell_price,r2.cost_price,gs.remark';
            $is_group = TRUE;
        }else{
            $sql_main .= "  group by rl.sku    ORDER BY rl.goods_code  , rl.sku ASC";
            $select = " sum(rl.stock_num) as stock_num,sum(rl.lock_num) as lock_num,sum(rl.out_num) as out_num,sum(rl.road_num) as road_num,sum(rl.safe_num) as safe_num,rl.sku,rl.store_code,";
            $select .= 'r2.goods_name,r2.goods_code,r2.category_code,r2.category_name,r2.brand_name,r2.year_name,r2.season_name,r2.weight,r2.sell_price,r2.cost_price,gs.remark';
            $is_group = TRUE;
        }

        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('cost_price', $filter['user_id']);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, $is_group);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['effec_num'] = $data['data'][$key]['stock_num'] - $data['data'][$key]['lock_num'];
            if ($status['status'] != 1) {
                $data['data'][$key]['cost_price'] = '***';
            }
        }
        $ret_status = OP_SUCCESS;
        //  filter_fk_name($data['data'], array('spec1_code|spec1_code', 'spec2_code|spec2_code', 'store_code|store', 'sku|barcode','category_code|category_code'));
        if($filter['barcode_group_val'] !=1){
            filter_fk_name($data['data'], array('store_code|store'));
        }
        $ret_data = $data;
        foreach ($ret_data['data'] as $key => &$value) {
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($sku_info, $value);

            //  $url = "?app_act=prm/inv/lock_detail&store_code={$value['store_code']}&sku={$value['sku']}&goods_code={$value['goods_code']}";
            $value['spec1_code_name'] = $value['spec1_name'] . "[" . $value['spec1_code'] . "]";
            $value['spec2_code_name'] = $value['spec1_name'] . "[" . $value['spec2_code'] . "]";
            $value['goods_self_name'] = $this->get_goods_self_by_sku($value['sku'], $value['store_code']);
        }
        return $this->format_ret($ret_status, $ret_data);
    }
    //获取商品库位
    function get_goods_self_by_sku($sku, $store_code) {
        $sql = "SELECT b.shelf_name FROM base_shelf b INNER JOIN goods_shelf s ON b.shelf_code = s.shelf_code AND b.store_code = s.store_code WHERE s.sku = :sku AND b.store_code = :store_code";
        $data = $this->db->get_all($sql, array(':sku' => $sku, ':store_code' => $store_code));
        $goods_shelf = array();
        foreach ($data as $val) {
            $goods_shelf[] = $val['shelf_name'];
        }
        if (!empty($goods_shelf)) {
            return implode(',', $goods_shelf);
        }
        return '';
    }
    
    function get_summary($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
    		$filter[$filter['keyword_type']] = $filter['keyword'];
    	}
        $sql_main = "";
        $sql_values = array();
        
        $ret = load_model('fx/GoodsModel')->get_fx_money_goods('rl.goods_code','sql', 'rl.');
        if(!empty($ret)) {
          $sql_main .= $ret['sql'];
          $sql_values = $ret['value'];
        }
        //仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        if(!(isset($filter['store_code']) && $filter['store_code'] != '')) {
            $store_str = load_model('base/StoreModel')->get_fx_store_sql();
            $filter_store_code = empty($store_str) ? NULL : $store_str;
        }
        $fun = isset($filter['is_entity']) && $filter['is_entity'] == 1 ? 'get_entity_store' : 'get_purview_store';
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code,$fun,2);

        $r2 = false;
        $ret_cfg = load_model('sys/SysParamsModel',true,false,'webefast')->get_val_by_code(array('brand_power'));
        $brand_power = $ret_cfg['brand_power'];
        //分销商登录
        $login_type = CTX()->get_session('login_type');
        if($login_type != 2) {
            if($brand_power==1){
                $r2 = true;
                $filter_brand_code= isset($filter['brand_code']) ? $filter['brand_code'] : null;
                $sql_main .= load_model('prm/BrandModel')->get_sql_purview_brand('r2.brand_code', $filter_brand_code);
            }
        }

        //分类

        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $r2 = true;
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
            $r2 = true;
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
            $r2 = true;
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
            $r2 = true;
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
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $r2 = true;
            $sql_main .= " AND ( (rl.goods_code LIKE :goods_code ) OR (r2.goods_name LIKE :goods_code ) ) ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $r2 = true;
            $sql_main .= " AND r2.goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $sql_main .= " AND rl.store_code in (:store_code) ";
            $sql_values[':store_code'] = explode(',', $filter['store_code']);
        }
        //仓库类别
        if (isset($filter['store_type_code']) && $filter['store_type_code'] != '') {
            $store_arr = load_model('base/StoreModel')->get_by_store_code_type($filter['store_type_code']);
            if(!empty($store_arr)){
                $sql_main .= " AND rl.store_code in (:store_type_code) ";
                $sql_values[':store_type_code'] = $store_arr;
            } else {
                $sql_main .= " AND 1 = 2 ";
            }
        }
         //可用库存查询
        if (isset($filter['effec_num_start']) && $filter['effec_num_start'] != '') {
            $sql_main .= " AND (cast(rl.stock_num as signed)-cast(rl.lock_num as signed) >= :effec_num_start) ";
            $sql_values[':effec_num_start'] = $filter['effec_num_start'];
        }

        if (isset($filter['effec_num_end']) && $filter['effec_num_end'] != '') {

            $sql_main .= " AND (cast(rl.stock_num as signed)-cast(rl.lock_num as signed) <= :effec_num_end) ";
            $sql_values[':effec_num_end'] = $filter['effec_num_end'];
        }
        //启用状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $r2 = true;
            $sql_main .= " AND r2.status = :status ";
            $sql_values[':status'] = $filter['status'];
        }
        //安全库存查询
        if (isset($filter['less_than_safe_num']) && $filter['less_than_safe_num'] != '') {
            if ($filter['less_than_safe_num'] == 1) {//低于安全库存的商品
                $sql_main .= " AND rl.stock_num < rl.safe_num";
            }
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            //$sql_main .= " AND (rl.barcode LIKE :barcode )";
//            $sql_main .= " AND (rl.sku in (select sku from goods_barcode where barcode LIKE :barcode) or rl.sku LIKE :sku )";
//            $sql_values[':barcode'] = $filter['barcode'] . '%';
//            $sql_values[':sku'] = $filter['barcode'] . '%';

            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND rl.sku in({$sku_str}) ";
            }
        }
        $sql_main_tb = " FROM {$this->table} rl   ";
        if ($r2 === true) {
            $sql_main_tb.="LEFT JOIN base_goods r2 on rl.goods_code = r2.goods_code";
        }
        $sql_main_tb.=" WHERE 1 ";
        $sql_main = $sql_main_tb . $sql_main;
        
        //总在途库存：100000  |  总缺货库存：300000  |  总可用库存：50000000 | 总锁定库存：1000000  |  总实物库存：1000000
        $data = array();
        $row = $this->db->get_row("select sum(road_num) as road_num  " . $sql_main, $sql_values);
        $data['road_num'] = empty($row['road_num']) ? 0 : $row['road_num'];

        $row = $this->db->get_row("select sum(out_num) as out_num " . $sql_main, $sql_values);
        $data['out_num'] = empty($row['out_num']) ? 0 : $row['out_num'];

        $row = $this->db->get_row("select sum(safe_num) as safe_num  " . $sql_main, $sql_values);
        $data['safe_num'] = empty($row['safe_num']) ? 0 : $row['safe_num'];

        $row = $this->db->get_row("select sum(stock_num) as stock_num " . $sql_main, $sql_values);
        $data['stock_num'] = empty($row['stock_num']) ? 0 : $row['stock_num'];

        $row = $this->db->get_row("select sum(stock_num) as stock_num " . $sql_main, $sql_values);
        $row = $this->db->get_row("select sum(lock_num) as lock_num " . $sql_main, $sql_values);
        $data['lock_num'] = empty($row['lock_num']) ? 0 : $row['lock_num'];
        $sql_main.=" AND stock_num>lock_num";

//        $row = $this->db->get_row("select sum(stock_num-lock_num) as available_mum " . $sql_main, $sql_values);
//        $data['available_mum'] = empty($row['available_mum']) ? 0 : $row['available_mum'];
        $data['available_mum'] = $data['stock_num'] - $data['lock_num'];

        return $this->format_ret(1, $data);
    }

    /**
     * 根据商品编码和仓库编码获取库存
     * @param $goods_code_arr
     * @param $store_code_arr
     * @return array|bool
     */
    public function get_inv_by_goods($goods_code_arr,$store_code_arr){
        if(!is_array($goods_code_arr)){
            $goods_code_arr = array_unique(explode(',',$goods_code_arr));
        }
        if(!is_array($store_code_arr)){
            $store_code_arr = array_unique(explode(',',$store_code_arr));
        }
        $sql = 'select goods_code,store_code,sum(stock_num) stock_num from '.$this->table.' where 1=1 ';
        $sql_values = array();
        if(!empty($goods_code_arr)){
            $goods_code_str = $this->arr_to_in_sql_value($goods_code_arr,'goods_code',$sql_values);
            $sql .= ' AND goods_code in ('.$goods_code_str.') ';
        }
        if(!empty($store_code_arr)){
            $store_code_str = $this->arr_to_in_sql_value($store_code_arr,'store_code',$sql_values);
            $sql .= ' AND store_code in ('.$store_code_str.') ';
        }
        $sql .= ' group by goods_code,store_code ';
        return $this->db->get_all($sql,$sql_values);
    }
    /**
     * 根据商品sku和仓库编码获取库存
     * @param $sku_code_arr
     * @param $store_code_arr
     * @return array|bool
     */
    public function get_inv_by_sku($sku_arr,$store_code_arr){
        if(!is_array($sku_arr)){
            $sku_arr = array_unique(explode(',',$sku_arr));
        }
        if(!is_array($store_code_arr)){
            $store_code_arr = array_unique(explode(',',$store_code_arr));
        }
        $sql = 'select sku,store_code,sum(stock_num) stock_num from '.$this->table.' where 1=1 ';
        $sql_values = array();
        if(!empty($sku_arr)){
            $sku_str = $this->arr_to_in_sql_value($sku_arr,'sku',$sql_values);
            $sql .= ' AND sku in ('.$sku_str.') ';
        }
        if(!empty($goods_code_arr)){
            $store_code_str = $this->arr_to_in_sql_value($store_code_arr,'store_code',$sql_values);
            $sql .= ' AND store_code in ('.$store_code_str.') ';
        }
        $sql .= ' group by sku,store_code ';
        return $this->db->get_all($sql,$sql_values);
    }
}