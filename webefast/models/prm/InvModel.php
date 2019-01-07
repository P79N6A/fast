<?php

/**
 * 商品库存帐管理相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('prm');

class InvModel extends TbModel {

    function get_table() {
        return 'goods_inv';
    }

    /*
     * 根据条件查询数据
     */

    var $loop_arr = array();
    var $wms_no_control = array('hwwms', 'ydwms', 'qimen');

    function get_by_page($filter) {
        $sql_values = array();

        $sql_main = "FROM {$this->table} rl
        			LEFT JOIN base_goods r2 on rl.goods_code = r2.goods_code
                                LEFT JOIN goods_sku gs ON rl.sku=gs.sku
        			WHERE 1";

        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        //仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $fun = isset($filter['is_entity']) && $filter['is_entity'] == 1 ? 'get_entity_store' : 'get_purview_store';
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code, $fun, 2);

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
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $arr = explode(',', $filter['goods_code']);
            $str = $this->arr_to_like_sql_value($arr, 'goods_code', $sql_values);
            $str = str_replace('goods_code like', 'rl.goods_code like', $str);
            $sql_main .= " AND " . $str;
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND ( (r2.goods_name LIKE :goods_name ) OR (r2.goods_code LIKE :goods_name ) )";
            $sql_values[':goods_name'] = '%' . trim($filter['goods_name']) . '%';
        }

        if (isset($filter['barcode_remark']) && $filter['barcode_remark'] != '') {
            $sql_main .= " AND gs.remark  LIKE :remark ";
            $sql_values[':remark'] = '%' . trim($filter['barcode_remark']) . '%';
        }
        //启用状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND r2.status = :status ";
            $sql_values[':status'] = $filter['status'];
        }

        //仓库类别
        if (isset($filter['store_type_code']) && $filter['store_type_code'] != '') {
            $store_arr = load_model('base/StoreModel')->get_by_store_code_type($filter['store_type_code']);
            if (!empty($store_arr)) {
                $sql_main .= " AND rl.store_code in (:store_type_code) ";
                $sql_values[':store_type_code'] = $store_arr;
            } else {
                $sql_main .= " AND 1 = 2 ";
            }
        }

        //库存查询
        if (isset($filter['num_start']) && $filter['num_start'] != '' && $filter['barcode_group_val'] != 1) {
            switch ($filter['is_num']) {
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
        if (isset($filter['num_end']) && $filter['num_end'] != '' && $filter['barcode_group_val'] != 1) {
            switch ($filter['is_num']) {
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
        if (isset($filter['less_than_safe_num']) && $filter['less_than_safe_num'] != '' && $filter['barcode_group_val'] != 1) {
            if ($filter['less_than_safe_num'] == 1) {//低于安全库存的商品
                $sql_main .= " AND rl.stock_num < rl.safe_num";
            }
        }

        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            //$sql_main .= " AND (rl.barcode LIKE :barcode )";
            $arr = explode(',', $filter['barcode']);
            $sku_arr = array();
            foreach ($arr as &$val) {
                $barcode_sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($val);
                $sku_arr = array_merge($sku_arr, $barcode_sku_arr);
            }

            $sku_arr = array_filter($sku_arr);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {

                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND rl.sku in({$sku_str}) ";
            }
        }
        //排序
        if (isset($filter['is_sort']) && $filter['is_sort'] != '') {
            switch ($filter['is_sort']) {
                case 'barcode_desc':  //条形码由高到低
                    $order_by = "  ORDER BY rl.goods_code  , rl.sku ASC ";
                    break;
                case 'road_num_desc':  //在途库存由高到低 
                    $order_by = " ORDER BY road_num  DESC ";
                    break;
                case 'road_num_asc':  //在途库存由低到高
                    $order_by = " ORDER BY road_num ASC ";
                    break;
                case 'stock_num_desc':  //实物库存由高到低
                    $order_by = " ORDER BY stock_num DESC ";
                    break;
                case 'stock_num_asc':  //实物库存由低到高
                    $order_by = " ORDER BY stock_num ASC ";
                    break;
                case 'out_num_desc':  //缺货库存由高到低
                    $order_by = " ORDER BY out_num DESC ";
                    break;
                case 'out_num_asc':  //缺货库存由低到高
                    $order_by = " ORDER BY out_num ASC ";
                    break;
            }
        }

        $is_group = false;

        if ($filter['barcode_group_val'] != 1 && empty($filter['is_sort'])) { //$filter['barcode_group_val'] =1 按仓库合并
            $sql_main .= "  ORDER BY rl.goods_code  , rl.sku ASC ";
            $select = 'rl.*,r2.goods_name,r2.category_code,r2.goods_img,r2.goods_thumb_img,r2.category_name,r2.brand_name,r2.year_name,r2.season_name,r2.weight,(CASE when gs.price=\'0.000\' then r2.sell_price ELSE gs.price END) as sell_price,(CASE when gs.cost_price=\'0.000\' then r2.cost_price ELSE gs.cost_price END) as cost_price,gs.remark,gs.spec1_code,gs.spec1_name,gs.spec2_code,gs.spec2_name,gs.barcode';
        } elseif ($filter['barcode_group_val'] != 1 && !empty($filter['is_sort'])) {
            $sql_main .= $order_by;
            $select = 'rl.*,r2.goods_name,r2.category_code,r2.goods_img,r2.goods_thumb_img,r2.category_name,r2.brand_name,r2.year_name,r2.season_name,r2.weight,(CASE when gs.price=\'0.000\' then r2.sell_price ELSE gs.price END) as sell_price,(CASE when gs.cost_price=\'0.000\' then r2.cost_price ELSE gs.cost_price END) as cost_price,gs.remark,gs.spec1_code,gs.spec1_name,gs.spec2_code,gs.spec2_name,gs.barcode';
        } elseif ($filter['barcode_group_val'] = 1 && empty($filter['is_sort'])) {
            $sql_main .= "  group by rl.sku    ORDER BY rl.goods_code  , rl.sku ASC";
            $select = " sum(rl.stock_num) as stock_num,sum(rl.lock_num) as lock_num,sum(rl.out_num) as out_num,sum(rl.road_num) as road_num,sum(rl.safe_num) as safe_num,rl.sku,rl.store_code,";
            $select .= 'r2.goods_name,r2.goods_code,r2.goods_img,r2.goods_thumb_img,r2.category_code,r2.category_name,r2.brand_name,r2.year_name,r2.season_name,r2.weight,(CASE when gs.price=\'0.000\' then r2.sell_price ELSE gs.price END) as sell_price,(CASE when gs.cost_price=\'0.000\' then r2.cost_price ELSE gs.cost_price END) as cost_price,gs.remark,gs.spec1_code,gs.spec1_name,gs.spec2_code,gs.spec2_name,gs.barcode';
            $is_group = TRUE;
        } elseif ($filter['barcode_group_val'] = 1 && !empty($filter['is_sort'])) {
            $sql_main .= "  group by rl.sku " . $order_by;
            $select = " sum(rl.stock_num) as stock_num,sum(rl.lock_num) as lock_num,sum(rl.out_num) as out_num,sum(rl.road_num) as road_num,sum(rl.safe_num) as safe_num,rl.sku,rl.store_code,";
            $select .= 'r2.goods_name,r2.goods_code,r2.goods_img,r2.goods_thumb_img,r2.category_code,r2.category_name,r2.brand_name,r2.year_name,r2.season_name,r2.weight,(CASE when gs.price=\'0.000\' then r2.sell_price ELSE gs.price END) as sell_price,(CASE when gs.cost_price=\'0.000\' then r2.cost_price ELSE gs.cost_price END) as cost_price,gs.remark,gs.spec1_code,gs.spec1_name,gs.spec2_code,gs.spec2_name,gs.barcode';
            $is_group = TRUE;
        }

        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('cost_price', $filter['user_id']);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, $is_group);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['effec_num'] = $data['data'][$key]['stock_num'] - $data['data'][$key]['lock_num'];
            //获取扩展属性
            $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
            $property_power = $ret_cfg['property_power'];
            if ($property_power) {
                $goods_property = load_model('prm/GoodsModel')->get_export_property($value['goods_code']);
                $data['data'][$key] = $goods_property != -1 && is_array($goods_property) ? array_merge($data['data'][$key], $goods_property) : $data['data'][$key];
            }
            if ($status['status'] != 1) {
                $data['data'][$key]['cost_price'] = '***';
            }
        }

        $ret_status = OP_SUCCESS;
        //  filter_fk_name($data['data'], array('spec1_code|spec1_code', 'spec2_code|spec2_code', 'store_code|store', 'sku|barcode','category_code|category_code'));
        if ($filter['barcode_group_val'] != 1) {
            filter_fk_name($data['data'], array('store_code|store'));
        }
        $ret_data = $data;
        $store_sku_arr = array();
        $key_arr = array();
        $sku_arr = array();
        //判断是否开启了子条码
        $goods_sub_barcode = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_sub_barcode'));
        foreach ($ret_data['data'] as $key => &$value) {
            // $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode');
            //$sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            //$value = array_merge($sku_info, $value);
            $store_sku_arr[$value['store_code']][] = $value['sku'];
            $new_key = $this->get_store_sku_key($value['store_code'], $value['sku']);
            $key_arr[$new_key] = $key;
            $sku_arr[] = $value['sku'];
            //  $url = "?app_act=prm/inv/lock_detail&store_code={$value['store_code']}&sku={$value['sku']}&goods_code={$value['goods_code']}";
//            if($goods_sub_barcode['goods_sub_barcode'] == 1){
//                $value['goods_sub_barcode'] ='';
//                $sql = "select barcode from goods_barcode_child where sku ='{$value['sku']}'";
//                $goods_child_barcode = $this->db->getAll($sql);
//                if($goods_child_barcode){
//                    foreach($goods_child_barcode as $k=>$v){
//                        $value['goods_sub_barcode'] .= ($v['barcode'])?$v['barcode'].';':'';
//                    }
//                }
//            }
            $value['spec1_code_name'] = $value['spec1_name'] . " (" . $value['spec1_code'] . ")";
            $value['spec2_code_name'] = $value['spec2_name'] . " (" . $value['spec2_code'] . ")";
            $value['goods_self_name'] = '';
            if (!empty($value['goods_img'])) {
                $value['goods_pic'] = "<img width='50px' height='50px' data-goods-img='{$value['goods_img']}' src='{$value['goods_img']}' />";
            }
            //$value['goods_self_name'] = $this->get_goods_self_by_sku($value['sku'], $value['store_code']);
        }
        if ($goods_sub_barcode['goods_sub_barcode'] == 1) {
//            $this->set_goods_child_barcode($store_sku_arr,$key_arr,$ret_data['data'] );
            $this->set_sku_child_barcode($sku_arr, $ret_data['data']);
        }
        $this->set_goods_self_name($store_sku_arr, $key_arr, $ret_data['data']);
        return $this->format_ret($ret_status, $ret_data);
    }

    private function get_store_sku_key($store_code, $sku) {
        return $store_code . "|" . $sku;
    }

    function set_goods_self_name($store_sku_arr, &$key_arr, &$inv_data) {
        foreach ($store_sku_arr as $store_code => $sku_arr) {
            $this->set_sku_self_name($store_code, $sku_arr, $key_arr, $inv_data);
        }
    }

    function set_sku_self_name($store_code, $sku_arr, &$key_arr, &$inv_data) {
        $sql_values = array(':store_code' => $store_code);
        $str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "select DISTINCT b.shelf_name,b.store_code,s.sku  from base_shelf b INNER JOIN goods_shelf s ON b.shelf_code=s.shelf_code and b.store_code = s.store_code "
                . "where  b.store_code=:store_code AND s.sku in ({$str}) ";
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as $val) {
            $key = $this->get_store_sku_key($val['store_code'], $val['sku']);
            $inv_key = $key_arr[$key];
            $inv_data[$inv_key]['goods_self_name'] .= empty($inv_data[$inv_key]['goods_self_name']) ? $val['shelf_name'] : "," . $val['shelf_name'];
        }
    }

    //通过关联表实现
//    function set_goods_child_barcode($store_sku_arr,&$key_arr,&$inv_data){
//        foreach($store_sku_arr as $store_code=>$sku_arr){
//            $this->set_sku_child_barcode_old($store_code,$sku_arr,$key_arr,$inv_data);
//        }
//    }
//    function set_sku_child_barcode_old($store_code,$sku_arr,&$key_arr,&$inv_data){
//        $sql_values = array(':store_code'=>$store_code);
//        $str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
//        $sql = "select DISTINCT b.barcode,s.sku,s.store_code  from goods_barcode_child b INNER JOIN goods_inv s ON b.sku=s.sku
//           where  s.store_code=:store_code AND s.sku in ({$str}) ";
//        $data =  $this->db->get_all($sql,$sql_values);
//        foreach($data as $val){
//            $key = $this->get_store_sku_key($val['store_code'],$val['sku']);
//            $inv_key = $key_arr[$key];
//            $inv_data[$inv_key]['goods_sub_barcode'] .= empty($inv_data[$inv_key]['barcode'])? $val['barcode']:",". $val['barcode'];
//        }
//    }
    function set_sku_child_barcode($sku_arr, &$inv_data) {
        $sku_arr = array_unique($sku_arr);
        $str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        if (empty($str)) {
            foreach ($inv_data as $k => $val) {
                $inv_data[$k]['goods_sub_barcode'] = '';
            }
        } else {
            $sql = "select DISTINCT b.barcode,b.sku from goods_barcode_child b where  b.sku in ({$str}) ";
            $data = $this->db->get_all($sql, $sql_values);
            $child_sku_arr = array();
            foreach ($data as $child_row) {
                $child_sku_arr[$child_row['sku']][] = $child_row['barcode'];
            }
            foreach ($inv_data as $k => $val) {
                if (isset($child_sku_arr[$val['sku']])) {
                    $inv_data[$k]['goods_sub_barcode'] = implode("，", $child_sku_arr[$val['sku']]);
                } else {
                    $inv_data[$k]['goods_sub_barcode'] = '';
                }
            }
        }
    }

    function get_by_id($id) {

        return $this->get_row(array('brand_id' => $id));
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

    /*
     * 多表查询
     * */

    public function get_by_search($goods_code) {
        $sql = "select r1.spec1_code as spec1_code,r1.spec2_code as spec2_code,r2.barcode as sku,"
                . "r2.barcode as barcode,r2.spec2_name,r2.spec1_name "
                . "FROM {$this->table} r1"
                . " left join goods_sku r2 on r1.goods_code = r2.goods_code AND r1.spec2_code = r2.spec2_code AND  r1.spec1_code = r2.spec1_code "
                . " where r1.goods_code = '{$goods_code}' ";
        $rs = $this->db->get_all($sql);
        return $rs;
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
     * 获取商品SKU级别的商品信息
     * @sice 2014-11-06
     * @param array $filter 检索条件
     * @return array
     * @todo 强制不分页
     */
    public function get_sku($filter = array()) {
        //查询套餐商品
        if (isset($filter['is_combo']) && $filter['is_combo'] == 1) {
            return $this->get_combo_barcode($filter);
        }
        //todo 强制不分页
        $filter['page_size'] = isset($filter['page_size']) ? $filter['page_size'] : 10000;
        /*
          if (!isset($filter['store_code']) || $filter['store_code'] == '') {
          return $this->format_ret('-1', '', '仓库代码必填');
          } */
        $sql_values = array();
        $sql_join = "";
        /*
          $sql_main = " FROM goods_sku r1
          LEFT JOIN base_goods r2 on r1.goods_code = r2.goods_code
          LEFT JOIN goods_inv r3 on r1.sku = r3.sku AND r3.store_code = :store_code
          LEFT JOIN goods_barcode r4 on r1.sku = r4.sku
          LEFT JOIN goods_price r5 on r1.goods_code = r5.goods_code
          WHERE 1 ";
          $sql_values[':store_code'] = $filter['store_code'];
         */
        $sql_main = " FROM goods_sku r1
    	INNER JOIN base_goods r2 on r1.goods_code = r2.goods_code

    	WHERE 1 ";
        if (isset($filter['custom_code']) && !empty($filter['custom_code'])) {
            $custom_code = $filter['custom_code'] != 'all_fx_goods' ? $filter['custom_code'] : '';
            $ret = load_model('fx/GoodsModel')->get_fx_money_goods('rl.goods_code', 'sql', 'r2.', $custom_code);
            if (!empty($ret)) {
                $sql_main .= $ret['sql'];
                $sql_values = $ret['value'];
            } else {
                $sql_main .= ' AND 1 != 1 ';
            }
        }
        $sql_main .= " AND r2.status = :status ";
        $sql_values[':status'] = 0;

        $is_allowed_exceed = load_model('sys/SysParamsModel')->get_val_by_code('is_allowed_exceed');
        //开启退货商品数，不允许超过订单商品数
        if ($is_allowed_exceed['is_allowed_exceed'] == 1 && isset($filter['return_package_code']) && $filter['return_package_code'] != '') {
            //退单号
            $package_data = load_model('oms/ReturnPackageModel')->get_return_package_by_code($filter['return_package_code']);
            if (!empty($package_data['sell_return_code'])) {
                //查询退单商品
                $sql = "SELECT sku FROM oms_sell_return_detail WHERE sell_return_code = :sell_return_code";
                $sku_arr = $this->db->get_all_col($sql, array(':sell_return_code' => $package_data['sell_return_code']));
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND r1.sku in ({$sku_str})";
            }
        }
        //商品匹配快递页面
        if (!empty($filter['list_type']) && $filter['list_type'] == 'goods_matching_express') {
            //过滤以选择的sku
            $express_goods_data = load_model('crm/OpExpressByGoodsModel')->do_sku_all();
            if (!empty($express_goods_data)) {
                $sku_str = $this->arr_to_in_sql_value($express_goods_data, 'sku', $sql_values);
                $sql_main .= " AND r1.sku NOT IN ({$sku_str})";
            }
        }

        //品牌权限
        $filter_brand_code = isset($filter['brand_code']) ? $filter['brand_code'] : null;
        $sql_main .= load_model('prm/BrandModel')->get_sql_purview_brand('r2.brand_code', $filter_brand_code);
        //分类
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $category_code_arr = explode(',', $filter['category_code']);
            $category_code_str = $this->arr_to_in_sql_value($category_code_arr, 'category_code', $sql_values);
            if (!empty($category_code_arr)) {
                $sql_main .= " AND r2.category_code in($category_code_str) ";
                //$sql_values[':category_code'] = $category_code_str;
            }
        }
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $brand_code_arr = explode(',', $filter['brand_code']);
            $brand_code_str = $this->arr_to_in_sql_value($brand_code_arr, 'brand_code', $sql_values);
            if (!empty($brand_code_arr)) {
                $sql_main .= " AND r2.brand_code in($brand_code_str) ";
                //$sql_values[':brand_code'] = $brand_code_arr;
            }
        }
        //年份
        if (isset($filter['year_code']) && $filter['year_code'] != '') {
            $year_code_arr = explode(',', $filter['year_code']);
            $year_code_str = $this->arr_to_in_sql_value($year_code_arr, 'year_code', $sql_values);
            if (!empty($year_code_arr)) {
                $sql_main .= " AND r2.year_code in($year_code_str) ";
                //  $sql_values[':year_code'] = $year_code_str;
            }
        }
        //季节
        if (isset($filter['season_code']) && $filter['season_code'] != '') {
            $season_code_arr = explode(',', $filter['season_code']);
            $season_code_str = $this->arr_to_in_sql_value($season_code_arr, 'season_code', $sql_values);
            if (!empty($season_code_arr)) {
                $sql_main .= " AND r2.season_code in($season_code_str) ";
            }
        }
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r1.goods_code LIKE :goods_code OR r2.goods_name LIKE :goods_code OR r2.goods_short_name LIKE :goods_code )";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND r1.barcode LIKE :barcode ";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        //商品编号
        if (isset($filter['sku']) && $filter['sku'] != '') {
            $sql_main .= " AND (r1.sku LIKE :sku )";
            $sql_values[':sku'] = $filter['sku'] . '%';
        }
        //是否组装商品
        if (isset($filter['diy']) && $filter['diy'] != '') {
            $sql_main .= " AND r2.diy = :diy ";
            $sql_values[':diy'] = $filter['diy'];
            //启用 //有字条码
        }

        //$select ="*";
        $sql_main = $sql_main . " ORDER BY r1.goods_code,r1.spec1_code,r1.spec2_code ";
        //$select = 'r1.*,r2.goods_name,r2.goods_img,IFNULL(r3.stock_num,0) stock_num,r4.barcode,r5.sell_price';
        $select = 'r1.*,r2.goods_name,r2.goods_img,r2.sell_price,r2.purchase_price,r2.trade_price,r2.diy';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, false);

        //  filter_fk_name($data['data'], array('spec1_code|spec1_code', 'spec2_code|spec2_code'));
        foreach ($data['data'] as $key => &$value) {

            //  $url = "?app_act=prm/inv/lock_detail&store_code={$value['store_code']}&sku={$value['sku']}&goods_code={$value['goods_code']}";
            $value['spec1_code_name'] = $value['spec1_name'] . "[" . $value['spec1_code'] . "]";
            $value['spec2_code_name'] = $value['spec2_name'] . "[" . $value['spec2_code'] . "]";
            //   $value['lock_num'] = "<a onclick=javascript:openPage('lock_detail','{$url}','实物锁定明细')>" . $value['lock_num'] . "</a>";
        }
        // var_dump($data);die;

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 获取商品SKU级别的商品套餐信息
     * @sice 2014-11-06
     * @param array $filter 检索条件
     * @return array
     * @todo 强制不分页
     */
    public function get_combo_barcode($filter = array()) {
        //todo 强制不分页
        $filter['page_size'] = isset($filter['page_size']) ? $filter['page_size'] : 10000;
        $sql_values = array();
        $sql_join = "";

        $sql_main = " FROM goods_combo_barcode b
	              	INNER JOIN  goods_combo g ON b.goods_code=g.goods_code
	                WHERE 1 AND g.status=1 ";

        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (g.goods_code LIKE :goods_code OR g.goods_name LIKE :goods_code)";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }

        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND b.barcode LIKE :barcode ";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        $select = 'b.goods_combo_barcode_id as sku_id,b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode, if(b.price>0,b.price,g.price) as price,g.goods_name,1 as is_combo  ';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

        filter_fk_name($data['data'], array('spec1_code|spec1_code', 'spec2_code|spec2_code'));
        foreach ($data['data'] as $key => &$value) {

            $value['spec1_name'] = $value['spec1_code_name'];
            $value['spec2_name'] = $value['spec2_code_name'];
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_goods_self_by_sku($sku, $store_code) {
        $data = $this->db->get_all("select b.shelf_name from base_shelf b INNER JOIN goods_shelf s ON b.shelf_code=s.shelf_code and b.store_code = s.store_code where s.sku=:sku AND b.store_code=:store_code ", array(':sku' => $sku, ':store_code' => $store_code));
        foreach ($data as $val) {
            $goods_shelf[] = $val['shelf_name'];
        }
        if (!empty($goods_shelf)) {
            return implode(',', $goods_shelf);
        }
        return '';
    }

    /**
     * 获取商品SKU级别的库存信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @sice 2014-11-06
     * @param array $filter 检索条件
     * @return array
     * @todo 强制不分页
     */
    public function get_sku_inv($filter = array()) {
        //todo 强制不分页
        $filter['page_size'] = isset($filter['page_size']) ? $filter['page_size'] : 10000;

        if (!isset($filter['store_code']) || $filter['store_code'] == '') {
            return $this->format_ret('-1', '', '仓库代码必填');
        }
        $sql_values = array();
        $sql_join = "";
        $sql_tb_r1 = 'goods_inv';
        if (isset($filter['lof_status']) && $filter['lof_status'] == 1) {
            $sql_tb_r1 = "goods_inv_lof";
        }
        if (isset($filter['is_entity']) && $filter['is_entity'] == 1) {
            $sql_join = "INNER JOIN base_shop_sku ss ON r1.sku=ss.sku";
        }
        //判断是否开启导出子条码参数
        $goods_sub_barcode = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_sub_barcode'));
        $sql_main = " FROM {$sql_tb_r1} r1
                          LEFT JOIN base_goods r2 on r1.goods_code = r2.goods_code
                          LEFT JOIN goods_sku r4 on r1.sku = r4.sku {$sql_join}
                          WHERE 1 ";
        if (isset($filter['custom_code']) && !empty($filter['custom_code'])) {
            $ret = load_model('fx/GoodsModel')->get_fx_money_goods('rl.goods_code', 'sql', 'r2.', $filter['custom_code']);
            if (!empty($ret)) {
                $sql_main .= $ret['sql'];
                $sql_values = $ret['value'];
            } else {
                $sql_main .= " AND 1 != 1 ";
            }
        }
        $sql_main .= " AND r2.status = :status ";
        //                          LEFT JOIN goods_price r5 on r1.goods_code = r5.goods_code
        $sql_values[':status'] = 0;

        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        //商店仓库权限
        $login_type = CTX()->get_session('login_type');
        if ($login_type == 2) {
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code, 'get_fx_store');
        } else {
            $act = 'get_purview_store';
            if ($filter['is_entity'] == 1) {
                $store_data = load_model('base/StoreModel')->get_by_code($filter_store_code);
                $act = $store_data['data']['store_property'] == 1 ? 'get_entity_store' : 'get_purview_store';
            }
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code, $act);
            //品牌权限
            $filter_brand_code = isset($filter['brand_code']) ? $filter['brand_code'] : null;
            $sql_main .= load_model('prm/BrandModel')->get_sql_purview_brand('r2.brand_code', $filter_brand_code);
        }
        $is_allowed_exceed = load_model('sys/SysParamsModel')->get_val_by_code('is_allowed_exceed');
        //开启退货商品数，不允许超过订单商品数
        /* if($is_allowed_exceed['is_allowed_exceed'] == 1 && isset($filter['sell_return_code']) && $filter['sell_return_code'] != '') {
          //查询订单
          $result = load_model('oms/SellReturnModel')->get_return_by_return_code($filter['sell_return_code']);
          if(!empty($result['sell_record_code'])) {
          //查询订单商品
          $sql = "SELECT sku FROM oms_sell_record_detail WHERE sell_record_code = :sell_record_code";
          $sku_arr = $this->db->get_all_col($sql,array(':sell_record_code' => $result['sell_record_code']));
          $sku_str = $this->arr_to_in_sql_value($sku_arr,'sku',$sql_values);
          $sql_main .= " AND r1.sku in ({$sku_str})";
          }
          } */
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_arr = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
            if (!empty($store_code_arr)) {
                $sql_main .= " AND r1.store_code in($store_code_arr) ";
            }
        }
        //分类
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $category_code_arr = explode(',', $filter['category_code']);
            $category_code_str = $this->arr_to_in_sql_value($category_code_arr, 'category_code', $sql_values);
            if (!empty($category_code_arr)) {
                $sql_main .= " AND r2.category_code in($category_code_str) ";
                //$sql_values[':category_code'] = $category_code_str;
            }
        }
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $brand_code_arr = explode(',', $filter['brand_code']);
            $brand_code_str = $this->arr_to_in_sql_value($brand_code_arr, 'brand_code', $sql_values);
            if (!empty($brand_code_arr)) {
                $sql_main .= " AND r2.brand_code in($brand_code_str) ";
                //$sql_values[':brand_code'] = $brand_code_arr;
            }
        }
        //年份
        if (isset($filter['year_code']) && $filter['year_code'] != '') {
            $year_code_arr = explode(',', $filter['year_code']);
            $year_code_str = $this->arr_to_in_sql_value($year_code_arr, 'year_code', $sql_values);
            if (!empty($year_code_arr)) {
                $sql_main .= " AND r2.year_code in($year_code_str) ";
            }
        }
        //季节
        if (isset($filter['season_code']) && $filter['season_code'] != '') {
            $season_code_arr = explode(',', $filter['season_code']);
            $season_code_str = $this->arr_to_in_sql_value($season_code_arr, 'season_code', $sql_values);
            if (!empty($season_code_arr)) {
                $sql_main .= " AND r2.season_code in($season_code_str) ";
            }
        }
        //商品编号、名称、规格商家编码  加判断$goods_sub_barcode是否有子条码
        if (isset($filter['goods_multi']) && $filter['goods_multi'] != '') {
            if ($goods_sub_barcode['goods_sub_barcode'] == 1) {
                $sql = "SELECT sku FROM goods_barcode_child WHERE barcode='{$filter['goods_multi']}'";
                $gbc_sku = $this->db->getOne($sql);
                if ($gbc_sku) {
                    $sql_main .= " AND (r1.goods_code LIKE :goods_multi OR r2.goods_name LIKE :goods_multi  OR r2.goods_short_name LIKE :goods_multi OR  r4.barcode LIKE :goods_multi OR r4.sku = :gbc_sku )";
                } else {
                    $sql_main .= " AND (r1.goods_code LIKE :goods_multi OR r2.goods_name LIKE :goods_multi  OR r2.goods_short_name LIKE :goods_multi OR  r4.barcode LIKE :goods_multi)";
                }
            } else {
                $sql_main .= " AND (r1.goods_code LIKE :goods_multi OR r2.goods_name LIKE :goods_multi  OR r2.goods_short_name LIKE :goods_multi OR  r4.barcode LIKE :goods_multi)";
            }
            $sql_values[':goods_multi'] = '%' . $filter['goods_multi'] . '%';
            $sql_values[':gbc_sku'] = $gbc_sku;
        }
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r1.goods_code LIKE :goods_code OR r2.goods_name LIKE :goods_code  OR r2.goods_short_name LIKE :goods_code )";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND r4.barcode LIKE :barcode ";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        //商品编号
        if (isset($filter['sku']) && $filter['sku'] != '') {
            $sql_main .= " AND (r1.sku LIKE :sku )";
            $sql_values[':sku'] = $filter['sku'] . '%';
        }
        //是否组装商品
        if (isset($filter['diy']) && $filter['diy'] != '') {
            $sql_main .= " AND r2.diy = :diy ";
            $sql_values[':diy'] = $filter['diy'];
        }
        //批次号
        if (isset($filter['lof_no']) && $filter['lof_no'] != '') {
            $sql_main .= " AND r1.lof_no = :lof_no ";
            $sql_values[':lof_no'] = $filter['lof_no'];
        }
        //失效日期
        if (isset($filter['invalid_time']) && $filter['invalid_time'] != '') {
            $sql_main .= " AND DATE_ADD(r1.production_date,INTERVAL r2.period_validity MONTH) = :invalid_time ";
            $sql_values[':invalid_time'] = $filter['invalid_time'];
        }
        //剩余可卖月
        if (isset($filter['available_month']) && $filter['available_month'] != '') {
            $sql_main .= " AND TIMESTAMPDIFF(MONTH,CURDATE(),DATE_ADD(r1.production_date,INTERVAL r2.period_validity MONTH)) = :available_month ";
            $sql_values[':available_month'] = $filter['available_month'];
        }
        //有效期
        if (isset($filter['period_validity']) && $filter['period_validity'] != '') {
            $sql_main .= " AND r2.period_validity = :period_validity ";
            $sql_values[':period_validity'] = $filter['period_validity'];
        }
        //锁定单已锁定的情况
        if (isset($filter['type']) && $filter['type'] == 'lock') {
            $record = load_model('stm/StockLockRecordModel')->get_row(array('record_code' => $filter['record_code']));
            if ($record['data']['order_status'] == 1) {
                $record_detail = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($record['data']['stock_lock_record_id'], 'stm_stock_lock');
                $detail = array();
                foreach ($record_detail['data'] as $key => $value) {
                    $detail[$key]['sku'] = $value['sku'];
                    $detail[$key]['lof_no'] = $value['lof_no'];
                }
                if (isset($filter['lof_status']) && $filter['lof_status'] == 1) {
                    $goods_inv_id = array();
                    foreach ($detail as $sku_lof) {
                        $sql_value_sku = array();
                        $sql_sku = "SELECT goods_inv_id FROM goods_inv_lof WHERE sku=:sku AND lof_no=:lof_no AND store_code=:store_code ";
                        $sql_value_sku[':sku'] = $sku_lof['sku'];
                        $sql_value_sku[':lof_no'] = $sku_lof['lof_no'];
                        $sql_value_sku[':store_code'] = $filter['store_code'];
                        $ret = $this->db->get_row($sql_sku, $sql_value_sku);
                        $goods_inv_id[] = $ret['goods_inv_id'];
                    }
                    if (!empty($goods_inv_id)) {
                        $goods_inv_id_str = $this->arr_to_in_sql_value($goods_inv_id, 'goods_inv_id', $sql_values);
                        $sql_main .= " AND r1.goods_inv_id NOT IN ({$goods_inv_id_str}) ";
                    }
                } else {
                    $sku_arr = array_column($detail, 'sku');
                    $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                    $sql_main .= " AND r1.sku NOT IN ({$sku_str}) ";
                }
            }
        }

        if (isset($filter['lof_status']) && $filter['lof_status'] == 1) {
            $sql_main = $sql_main . " GROUP BY r1.sku,r1.lof_no,r1.lastchanged ";
            $select = 'r1.goods_inv_id ,r1.stock_num,r1.lock_num,r1.sku,r1.lof_no,r1.production_date,r1.spec1_code,r1.spec2_code,r1.goods_code,r2.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,r2.sell_price,r2.price,r2.purchase_price,r2.trade_price';
        } else {
            $sql_main = $sql_main . " GROUP BY r1.sku ";
            $select = 'r1.goods_inv_id ,r1.stock_num,r1.lock_num,r1.sku,r1.spec1_code,r1.spec2_code,r1.goods_code,r2.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,r2.sell_price,r2.price,r2.purchase_price,r2.trade_price';
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        //  filter_fk_name($data['data'], array('spec1_code|spec1_code', 'spec2_code|spec2_code'));
        foreach ($data['data'] as &$val) {
            $val['is_combo'] = 0;
            $val['available_mum'] = (int) $val['stock_num'] - (int) $val['lock_num'];
            //$val['available_mum'] = ($val['available_mum'] < 0) ? 0 : $val['available_mum'];
            $val['spec1_code_name'] = $val['spec1_name'] . "[" . $val['spec1_code'] . "]";
            $val['spec2_code_name'] = $val['spec2_name'] . "[" . $val['spec2_code'] . "]";
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        if (isset($filter['is_combo']) && $filter['is_combo'] == 1) {
            //获取套餐商品
            $this->get_sku_combo_inv($filter, $ret_data);
        }


        return $this->format_ret($ret_status, $ret_data);
    }


    /**
     * 调整单新增
     * @param array $filter
     * @return array
     */
    public function get_sku_inv_adjust($filter = array()) {
        //todo 强制不分页
        $filter['page_size'] = isset($filter['page_size']) ? $filter['page_size'] : 10000;

        if (!isset($filter['store_code']) || $filter['store_code'] == '') {
            return $this->format_ret('-1', '', '仓库代码必填');
        }
        $sql_values = array();
        $sql_join = "";
        $sql_tb_r1 = 'goods_inv';
        if (isset($filter['lof_status']) && $filter['lof_status'] == 1) {
            $sql_tb_r1 = "goods_inv_lof";
        }
        if (isset($filter['is_entity']) && $filter['is_entity'] == 1) {
            $sql_join = "INNER JOIN base_shop_sku ss ON r4.sku=ss.sku";
        }
        //判断是否开启导出子条码参数
        $goods_sub_barcode = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_sub_barcode'));
        $sql_main = " FROM goods_sku AS r4 LEFT JOIN {$sql_tb_r1} r1 ON r4.sku=r1.sku AND r1.store_code='{$filter['store_code']}'
                          LEFT JOIN base_goods r2 on r4.goods_code = r2.goods_code
                          {$sql_join}
                          WHERE 1 ";
        if (isset($filter['custom_code']) && !empty($filter['custom_code'])) {
            $ret = load_model('fx/GoodsModel')->get_fx_money_goods('rl.goods_code', 'sql', 'r2.', $filter['custom_code']);
            if (!empty($ret)) {
                $sql_main .= $ret['sql'];
                $sql_values = $ret['value'];
            } else {
                $sql_main .= " AND 1 != 1 ";
            }
        }
        $sql_main .= " AND r2.status = :status ";
        $sql_values[':status'] = 0;

        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        //商店仓库权限
        $login_type = CTX()->get_session('login_type');
        if ($login_type == 2) {
            //$sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code, 'get_fx_store');
        } else {
            $act = 'get_purview_store';
            if ($filter['is_entity'] == 1) {
                $store_data = load_model('base/StoreModel')->get_by_code($filter_store_code);
                $act = $store_data['data']['store_property'] == 1 ? 'get_entity_store' : 'get_purview_store';
            }
            //$sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code, $act);
            //品牌权限
            $filter_brand_code = isset($filter['brand_code']) ? $filter['brand_code'] : null;
            $sql_main .= load_model('prm/BrandModel')->get_sql_purview_brand('r2.brand_code', $filter_brand_code);
        }

        //分类
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $category_code_arr = explode(',', $filter['category_code']);
            $category_code_str = $this->arr_to_in_sql_value($category_code_arr, 'category_code', $sql_values);
            if (!empty($category_code_arr)) {
                $sql_main .= " AND r2.category_code in($category_code_str) ";
                //$sql_values[':category_code'] = $category_code_str;
            }
        }
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $brand_code_arr = explode(',', $filter['brand_code']);
            $brand_code_str = $this->arr_to_in_sql_value($brand_code_arr, 'brand_code', $sql_values);
            if (!empty($brand_code_arr)) {
                $sql_main .= " AND r2.brand_code in($brand_code_str) ";
                //$sql_values[':brand_code'] = $brand_code_arr;
            }
        }
        //年份
        if (isset($filter['year_code']) && $filter['year_code'] != '') {
            $year_code_arr = explode(',', $filter['year_code']);
            $year_code_str = $this->arr_to_in_sql_value($year_code_arr, 'year_code', $sql_values);
            if (!empty($year_code_arr)) {
                $sql_main .= " AND r2.year_code in($year_code_str) ";
            }
        }
        //季节
        if (isset($filter['season_code']) && $filter['season_code'] != '') {
            $season_code_arr = explode(',', $filter['season_code']);
            $season_code_str = $this->arr_to_in_sql_value($season_code_arr, 'season_code', $sql_values);
            if (!empty($season_code_arr)) {
                $sql_main .= " AND r2.season_code in($season_code_str) ";
            }
        }
        //商品编号、名称、规格商家编码  加判断$goods_sub_barcode是否有子条码
        if (isset($filter['goods_multi']) && $filter['goods_multi'] != '') {
            if ($goods_sub_barcode['goods_sub_barcode'] == 1) {
                $sql = "SELECT sku FROM goods_barcode_child WHERE barcode='{$filter['goods_multi']}'";
                $gbc_sku = $this->db->getOne($sql);
                if ($gbc_sku) {
                    $sql_main .= " AND (r1.goods_code LIKE :goods_multi OR r2.goods_name LIKE :goods_multi  OR r2.goods_short_name LIKE :goods_multi OR  r4.barcode LIKE :goods_multi OR r4.sku = :gbc_sku )";
                } else {
                    $sql_main .= " AND (r1.goods_code LIKE :goods_multi OR r2.goods_name LIKE :goods_multi  OR r2.goods_short_name LIKE :goods_multi OR  r4.barcode LIKE :goods_multi)";
                }
            } else {
                $sql_main .= " AND (r1.goods_code LIKE :goods_multi OR r2.goods_name LIKE :goods_multi  OR r2.goods_short_name LIKE :goods_multi OR  r4.barcode LIKE :goods_multi)";
            }
            $sql_values[':goods_multi'] = '%' . $filter['goods_multi'] . '%';
            $sql_values[':gbc_sku'] = $gbc_sku;
        }
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r4.goods_code LIKE :goods_code OR r2.goods_name LIKE :goods_code  OR r2.goods_short_name LIKE :goods_code )";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND r4.barcode LIKE :barcode ";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        //商品编号
        if (isset($filter['sku']) && $filter['sku'] != '') {
            $sql_main .= " AND (r4.sku LIKE :sku )";
            $sql_values[':sku'] = $filter['sku'] . '%';
        }
        //是否组装商品
        if (isset($filter['diy']) && $filter['diy'] != '') {
            $sql_main .= " AND r2.diy = :diy ";
            $sql_values[':diy'] = $filter['diy'];
        }
        //批次号
        if (isset($filter['lof_no']) && $filter['lof_no'] != '') {
            $sql_main .= " AND r1.lof_no = :lof_no ";
            $sql_values[':lof_no'] = $filter['lof_no'];
        }
        //失效日期
        if (isset($filter['invalid_time']) && $filter['invalid_time'] != '') {
            $sql_main .= " AND DATE_ADD(r1.production_date,INTERVAL r2.period_validity MONTH) = :invalid_time ";
            $sql_values[':invalid_time'] = $filter['invalid_time'];
        }
        //剩余可卖月
        if (isset($filter['available_month']) && $filter['available_month'] != '') {
            $sql_main .= " AND TIMESTAMPDIFF(MONTH,CURDATE(),DATE_ADD(r1.production_date,INTERVAL r2.period_validity MONTH)) = :available_month ";
            $sql_values[':available_month'] = $filter['available_month'];
        }
        //有效期
        if (isset($filter['period_validity']) && $filter['period_validity'] != '') {
            $sql_main .= " AND r2.period_validity = :period_validity ";
            $sql_values[':period_validity'] = $filter['period_validity'];
        }

        if (isset($filter['lof_status']) && $filter['lof_status'] == 1) {
            $sql_main = $sql_main . " GROUP BY r4.sku,r1.lof_no,r1.lastchanged ";
            $select = 'CONCAT(r4.sku_id,\'\',r1.goods_inv_id) AS goods_inv_id,r1.stock_num,r1.lock_num,r4.sku,r1.lof_no,r1.production_date,r1.spec1_code,r1.spec2_code,r4.goods_code,r2.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,r2.sell_price,r2.price,r2.purchase_price,r2.trade_price';
        } else {
            $sql_main = $sql_main . " GROUP BY r4.sku ";
            $select = 'r4.sku_id as goods_inv_id,r1.stock_num,r1.lock_num,r4.sku,r1.spec1_code,r1.spec2_code,r4.goods_code,r2.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,r2.sell_price,r2.price,r2.purchase_price,r2.trade_price';
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

        $g = 1; //无库存的,goods_inv_id后增加标识,防止唯一键重复,页面取值无法识别
        foreach ($data['data'] as &$val) {
            $val['is_combo'] = 0;
            if ($val['stock_num'] == null) {
                $val['available_mum'] = '';
            } else {
                $val['available_mum'] = (int) $val['stock_num'] - (int) $val['lock_num'];
            }
            $val['spec1_code_name'] = $val['spec1_name'] . "[" . $val['spec1_code'] . "]";
            $val['spec2_code_name'] = $val['spec2_name'] . "[" . $val['spec2_code'] . "]";

            if (empty($val['spec1_code'])) {
                $val['goods_inv_id'] = $val['goods_inv_id'] . '_' . $g;
            }
            $g ++;
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        if (isset($filter['is_combo']) && $filter['is_combo'] == 1) {
            //获取套餐商品
            $this->get_sku_combo_inv($filter, $ret_data);
        }

        return $this->format_ret($ret_status, $ret_data);
    }



    //获取组装商品库存
    function get_sku_combo_inv($filter, &$ret_data) {
        //商品编号
        if (isset($filter['lof_status']) && $filter['lof_status'] == 0 && isset($filter['goods_code']) && $filter['goods_code'] != '') {

            $sql_main = " FROM goods_combo r1
                          LEFT JOIN goods_combo_barcode r4 on r1.goods_code = r4.goods_code
                          WHERE 1 ";
            $sql_main .= " AND (r1.goods_code LIKE :goods_code OR r1.goods_name LIKE :goods_code )";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';

            $sql_main = $sql_main . " GROUP BY r4.sku ";
            $select = 'r4.goods_combo_barcode_id as goods_inv_id ,r4.sku,r4.spec1_code,r1.goods_name,r1.goods_code,r4.spec2_code,r4.barcode ';

            $sql = "select count(*) as total_sl from (select $select $sql_main) t";

            $count = $this->db->get_value($sql, $sql_values);
            $filter['page'] = !isset($filter['page']) ? 1 : $filter['page'];
            if ($count > 0) {
                $count += $ret_data['filter']['record_count'];
                $data_num = count($ret_data['data']);

                $new_page = $filter['page'] - $ret_data['filter']['page_count'];

                $limit = ($new_page == 0) ? $ret_data['filter']['page_size'] - $data_num : $ret_data['filter']['page_size'];

                if ($new_page > 0) {
                    $start = ($new_page - 1) * $ret_data['filter']['page_size'];
                    $start += $data_num;
                } else {
                    $start = 0;
                }
                $start = ($start < 0) ? 0 : $start;

                $ret_data['filter']['page_count'] = ceil($count / $ret_data['filter']['page_size']);
                $ret_data['filter']['record_count'] = $count;

                if ($filter['page'] > $ret_data['filter']['page_count']) {
                    $ret_data['data'] = array();
                }
                if ($limit > 0) {
                    $data = $this->db->get_all("select $select $sql_main limit {$start},{$limit}", $sql_values);
                    filter_fk_name($data, array('spec1_code|spec1_code', 'spec2_code|spec2_code'));
                    foreach ($data as $val) {
                        $inv = load_model('prm/GoodsComboBarcodeModel')->get_inv_by_sku($val['sku'], $filter['store_code']);
                        $val['is_combo'] = 1;
                        $val['goods_inv_id'] = "c_" . $val['goods_inv_id'];
                        $val['spec1_code_name'] = $val['spec1_code_name'] . "[" . $val['spec1_code'] . "]";
                        $val['spec2_code_name'] = $val['spec2_code_name'] . "[" . $val['spec2_code'] . "]";
                        $ret_data['data'][] = array_merge($val, $inv);
                    }
                }
            }
        }
    }

    /**
     * 获取商品SKU批次级别商品
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @sice 2014-11-06
     * @param array $filter 检索条件
     * @return array
     * @todo 强制不分页
     */
    public function get_sku_inv_all($filter = array()) {
        //todo 强制不分页
        $filter['page_size'] = isset($filter['page_size']) ? $filter['page_size'] : 10000;
        //如果不是从活动详情添加商品就添加仓库代码
        if ($filter['list_type'] != 'crm_goods' && isset($filter['list_type'])) {
            if (!isset($filter['store_code']) || $filter['store_code'] == '') {
                return $this->format_ret('-1', '', '仓库代码必填');
            }
        }

        $sql_values = array();
        $sql_join = "";
        $join_lof = '';
        if (isset($filter['lof_status']) && $filter['lof_status'] == 1) {
            $join_lof = "LEFT JOIN goods_lof r3 on r1.sku = r3.sku ";

            $data = load_model("prm/GoodsLofModel")->get_sys_lof();
            $lof_no = $data['data']['lof_no'];
            $production_date = $data['data']['production_date'];
        }
        if (isset($filter['is_entity']) && $filter['is_entity'] == 1) {
            $sql_join = "INNER JOIN base_shop_sku ss ON r1.sku=ss.sku";
        }
//       if (isset($filter['diy']) && $filter['diy'] != '') {
//              $join_lof.= " INNER JOIN  goods_diy gd ON  gd.p_sku= r1.sku"; //sku
//        }

        $select = "";
        $sql_main = " FROM goods_sku r1
                      LEFT JOIN base_goods r2 on r1.goods_code = r2.goods_code
                      {$join_lof} {$sql_join}
                      WHERE 1 ";
        if (isset($filter['custom_code']) && !empty($filter['custom_code'])) {
            $ret = load_model('fx/GoodsModel')->get_fx_money_goods('rl.goods_code', 'sql', 'r2.', $filter['custom_code']);
            if (!empty($ret)) {
                $sql_main .= $ret['sql'];
                $sql_values = $ret['value'];
            } else {
                $sql_main .= " AND 1 != 1 ";
            }
        }
        $sql_main .= " AND r2.status = :status ";
        $sql_values[':status'] = 0;
        //品牌权限
        $filter_brand_code = isset($filter['brand_code']) ? $filter['brand_code'] : null;
        $sql_main .= load_model('prm/BrandModel')->get_sql_purview_brand('r2.brand_code', $filter_brand_code);

        $is_allowed_exceed = load_model('sys/SysParamsModel')->get_val_by_code('is_allowed_exceed');
        //开启退货商品数，不允许超过订单商品数
        /* if($is_allowed_exceed['is_allowed_exceed'] == 1 && isset($filter['sell_return_code']) && $filter['sell_return_code'] != '') {
          //查询订单
          $result = load_model('oms/SellReturnModel')->get_return_by_return_code($filter['sell_return_code']);
          if(!empty($result['sell_record_code'])) {
          //查询订单商品
          $sql = "SELECT sku FROM oms_sell_record_detail WHERE sell_record_code = :sell_record_code";
          $sku_arr = $this->db->get_all_col($sql,array(':sell_record_code' => $result['sell_record_code']));
          $sku_str = $this->arr_to_in_sql_value($sku_arr,'sku',$sql_values);
          $sql_main .= " AND r1.sku in ({$sku_str})";
          }
          } */

        if (isset($filter['is_entity']) && $filter['is_entity'] == 1 && $filter['list_type'] == 'adjust') {
            $shop_code = $filter['store_code'];
            $sql_main .=" AND ss.shop_code=:shop_code";
            $sql_values[':shop_code'] = $shop_code;
        }

        //分类
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $category_code_arr = explode(',', $filter['category_code']);
            $category_code_str = $this->arr_to_in_sql_value($category_code_arr, 'category_code', $sql_values);
            if (!empty($category_code_arr)) {
                $sql_main .= " AND r2.category_code in($category_code_str) ";
                //$sql_values[':category_code'] = $category_code_str;
            }
        }
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $brand_code_arr = explode(',', $filter['brand_code']);
            $brand_code_str = $this->arr_to_in_sql_value($brand_code_arr, 'brand_code', $sql_values);
            if (!empty($brand_code_arr)) {
                $sql_main .= " AND r2.brand_code in($brand_code_str) ";
                //$sql_values[':brand_code'] = $brand_code_arr;
            }
        }
        //年份
        if (isset($filter['year_code']) && $filter['year_code'] != '') {
            $year_code_arr = explode(',', $filter['year_code']);
            $year_code_str = $this->arr_to_in_sql_value($year_code_arr, 'year_code', $sql_values);
            if (!empty($year_code_arr)) {
                $sql_main .= " AND r2.year_code in($year_code_str) ";
            }
        }
        //季节
        if (isset($filter['season_code']) && $filter['season_code'] != '') {
            $season_code_arr = explode(',', $filter['season_code']);
            $season_code_str = $this->arr_to_in_sql_value($season_code_arr, 'season_code', $sql_values);
            if (!empty($season_code_arr)) {
                $sql_main .= " AND r2.season_code in($season_code_str) ";
            }
        }
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r1.goods_code LIKE :goods_code OR r2.goods_name LIKE :goods_code  OR r2.goods_short_name LIKE :goods_code )";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND r1.barcode LIKE :barcode ";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }

        //商品编号
        if (isset($filter['sku']) && $filter['sku'] != '') {
            $sql_main .= " AND (r1.sku LIKE :sku )";
            $sql_values[':sku'] = $filter['sku'] . '%';
        }
        //是否组装商品
        if (isset($filter['diy']) && $filter['diy'] != '') {
            $sql_main .= " AND r2.diy = :diy ";
            $sql_values[':diy'] = $filter['diy'];
        }

        //goods_diy


        $group_by = FALSE;
        if (isset($filter['lof_status']) && $filter['lof_status'] == 1) {
            $select = "  ifnull(r3.id ,r1.barcode*100000) as goods_inv_id ,r1.sku,
                ifnull(r3.lof_no,'" . $lof_no . "') as lof_no,ifnull(r3.production_date,'" . $production_date . "') as production_date,
                r1.spec1_code,r1.spec2_code,r1.goods_code,r2.goods_name,r1.spec1_name,r1.spec2_name,r1.barcode,r2.sell_price,r2.price,r2.purchase_price,r2.trade_price";
        } else {
            $group_by = true;
            $sql_main = $sql_main . " GROUP BY r1.sku ";
            $select .= ' r1.sku_id as goods_inv_id ,r1.sku,r1.spec1_code,r1.spec2_code,r1.goods_code,r2.goods_name,r1.barcode,r1.spec1_name,r1.spec2_name,r2.sell_price,r2.price,r2.purchase_price,r2.trade_price';
        }
        //$select = 'r1.*,r2.goods_name,r2.goods_img,r4.barcode,r5.sell_price';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, $group_by);

        foreach ($data['data'] as &$val) {
            $val['spec1_code_name'] = $val['spec1_name'] . "[" . $val['spec1_code'] . "]";
            $val['spec2_code_name'] = $val['spec2_name'] . "[" . $val['spec2_code'] . "]";
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    public function set_road_num() {
        
    }

    /**
     * 统计缺货库存
     *
     * @param type $sku_arr
     */
    public function update_stock_out_inv($sell_record_detail, $store_code) {
//        if($force>0){
//            $sku_arr = array();
//            foreach($sell_record_detail as $val){
//                $sku_arr[] = $val['sku'];
//            }
//            return  $this->stock_out_inv($store_code,$sku_arr,$force);
//        }
        $update_str = "out_num = out_num +VALUES(out_num)";
        $add_inv_arr = array();
        foreach ($sell_record_detail as $deatil) {
            if ($deatil['out_num'] == 0) {
                continue;
            }
            $new_deatil['store_code'] = $store_code;
            $new_deatil['goods_code'] = $deatil['goods_code'];
//                $new_deatil['spec1_code'] = $deatil['spec1_code'];
//                $new_deatil['spec2_code'] = $deatil['spec2_code'];
            $new_deatil['sku'] = $deatil['sku'];
            $new_deatil['stock_num'] = 0;
            $new_deatil['lock_num'] = 0;
            $new_deatil['out_num'] = $deatil['out_num'];
            if ($deatil['out_num'] < 0) {
                $out_num = abs($deatil['out_num']);
                $sql = "update goods_inv set  out_num=if(out_num>$out_num,out_num-$out_num,0) where
                    store_code = '{$new_deatil['store_code']}' and
                    sku = '{$new_deatil['sku']}'";
                $this->db->query($sql);
            } else {
                $add_inv_arr[] = $new_deatil;
            }
        }
        $ret = $this->format_ret(1);
        if (!empty($add_inv_arr)) {
            $ret = $this->insert_multi_duplicate($this->table, $add_inv_arr, $update_str);
        }
    }

    /**
     * 取消缺货库存
     *
     * @param type $sku_arr
     */
    public function cancel_stock_out_inv($sell_record_detail, $store_code) {
        //  var_dump($sell_record_detail);
        foreach ($sell_record_detail as $key => $sub_record) {
            $out_num = $sub_record['num'] - $sub_record['lock_num'];
            if ($out_num > 0) {
                $sql = "update {$this->table} set  out_num=out_num-{$out_num} where
                sku = '{$sub_record['sku']}'
               and store_code = '{$store_code}'
               and      out_num>=" . $out_num;
                $status = $this->db->query($sql);
                $run_num = $this->affected_rows();
                if ($run_num != 1 || $status === FALSE) {
                    $this->stock_out_inv($store_code, $sub_record['sku']);
                    //return $this->format_ret(-1,array(),'取消缺货库存异常');
                } else {
                    unset($sell_record_detail[$key]);
                }
            }
        }
        if (!empty($sell_record_detail)) {
            if (empty($this->loop_arr)) {
                $this->loop_arr['num'] = count($sell_record_detail);
                $ret = $this->cancel_stock_out_inv($sell_record_detail, $store_code);

                return $ret;
            } else {
                //写日志，数据异常
            }
        }

        return $this->format_ret(1);
    }

    //维护缺货库存
    public function stock_out_inv($store_code, $sku_arr = array()) {
        $this->begin_trans();
        $where_detail = " r.store_code = '{$store_code}'  ";
        $where = " store_code = '{$store_code}'  ";
        if (!empty($sku_arr)) {
            $str_sku = "'" . implode("'", $sku_arr) . "'";
            $where .=" and  sku IN ({$str_sku})";
            $where_detail .=" and  d.sku  IN ({$str_sku})";
        }
        $sql = "update goods_inv set out_num =0 where {$where} ";
        $this->db->query($sql);
        $sql = "insert into goods_inv(goods_code,spec1_code,spec2_code,sku,store_code,out_num)";
        $sql .= " select d.goods_code,d.spec1_code,d.spec2_code,d.sku,r.store_code,d.num-d.lock_num as out_num from oms_sell_record r
                    INNER JOIN oms_sell_record_detail d
                    ON r.sell_record_code = d.sell_record_code
                    where {$where_detail} AND   r.order_status<>3 and r.shipping_status<4  and r.must_occupy_inv=1 and r.lock_inv_status<>1 and  d.is_delete=0  ";
        $sql .=" ON DUPLICATE KEY UPDATE out_num=VALUES(out_num)+out_num";
        $this->db->query($sql);
        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 库存变动公共方法, 需要仓库代码和SKU, 此方法需要包含在try...catch...中使用
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-11
     * @param string $store_code 仓库代码
     * @param string $sku   商品SKU
     * @param array $num_array  变动数量, 可以为正负. 包含库存/锁定库存/冻结库存/在途库存
     * @param string $record_time 库存变动业务时间
     * @param array $record_info 库存变动相关单据信息, type:变动类型;relation_code:关联单号;object_code:关联对象;remark备注
     * @throws exception
     * @return array
     */
    public function adjust($store_code, $sku, $num_array, $record_time, $record_info) {
        //根据SKU获取商品代码/规格代码等信息 +++++++++++++++++++++++++++++++++++++++++++++++
        $info = load_model('prm/SkuModel')->get_spec_by_sku($sku);
        if (false == $info || empty($info)) {
            //throw new Exception('商品规格信息有误!');
            $this->format_ret(false, array(), '商品规格信息有误!');
        }
        //当前库存信息 +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $inv = $this->get_row(array('sku' => $sku, 'store_code' => $store_code));
        if (empty($inv['data'])) {
            //库存不存在, 新增模式
            $data = array(
                'stock_num' => isset($num_array['stock_num']) ? $num_array['stock_num'] : 0,
                'lock_num' => isset($num_array['lock_num']) ? $num_array['lock_num'] : 0,
                'frozen_num' => isset($num_array['frozen_num']) ? $num_array['frozen_num'] : 0,
                'road_num' => isset($num_array['road_num']) ? $num_array['road_num'] : 0,
                'goods_id' => $info['goods_id'],
                'goods_code' => $info['goods_code'],
                'spec1_id' => $info['spec1_id'],
                'spec1_code' => $info['spec1_code'],
                'spec2_id' => $info['spec2_id'],
                'spec2_code' => $info['spec2_code'],
                'sku' => $sku,
                'store_id' => '',
                'store_code' => $store_code,
                'batch' => '',
            );
        } else {
            //库存编辑模式
            $data = $inv['data']; //变动后的数据
            foreach ($data as $field => $value) {
                if ($field == 'stock_num' && isset($num_array['stock_num'])) {
                    $data['stock_num'] = $value + $num_array['stock_num'];
                }
                if ($field == 'lock_num' && isset($num_array['lock_num'])) {
                    $data['lock_num'] = $value + $num_array['lock_num'];
                }
                if ($field == 'frozen_num' && isset($num_array['frozen_num'])) {
                    $data['frozen_num'] = $value + $num_array['frozen_num'];
                }
                if ($field == 'road_num' && isset($num_array['road_num'])) {
                    $data['road_num'] = $value + $num_array['road_num'];
                }
            }
        }
        //商品库存修改 +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $this->begin_trans();
        try {
            if (empty($inv['data'])) {
                $this->insert($data);
            } else {
                $this->update($data, array('goods_inv_id' => $data['goods_inv_id']));
            }
            //生成库存流水
            $record = array(
                'goods_id' => $info['goods_id'],
                'goods_code' => $info['goods_code'],
                'color_id' => $info['spec1_id'],
                'color_code' => $info['spec1_code'],
                'size_id' => $info['spec2_id'],
                'size_code' => $info['spec2_code'],
                'sku' => $sku,
                'price_type' => $record_info['price_type'],
                'money' => $record_info['money'],
                'store_code' => $store_code,
                'stock_change_num' => isset($num_array['stock_num']) ? $num_array['stock_num'] : 0,
                'stock_num_before_change' => isset($inv['data']['stock_num']) ? $inv['data']['stock_num'] : 0,
                'stock_num_after_change' => $data['stock_num'],
                'lock_change_num' => isset($num_array['lock_num']) ? $num_array['lock_num'] : 0,
                'lock_num_before_change' => isset($inv['data']['lock_num']) ? $inv['data']['lock_num'] : 0,
                'lock_num_after_change' => $data['lock_num'],
                'frozen_change_num' => isset($num_array['frozen_num']) ? $num_array['frozen_num'] : 0,
                'frozen_num_before_change' => isset($inv['data']['frozen_num']) ? $inv['data']['frozen_num'] : 0,
                'frozen_num_after_change' => $data['frozen_num'],
                'road_change_num' => isset($num_array['road_num']) ? $num_array['road_num'] : 0,
                'road_num_before_change' => isset($inv['data']['road_num']) ? $inv['data']['road_num'] : 0,
                'road_num_after_change' => $data['road_num'],
                'record_time' => $record_time,
                'is_add_time' => date('Y-m-d H:i:s'),
                'is_add_person' => '',
                'object_code' => $record_info['object_code'],
                'relation_code' => $record_info['relation_code'],
                'type' => $record_info['type'],
                'remark' => $record_info['remark'],
            );
            $insertResult = load_model('prm/InvRecordModel')->insert($record);
            if ($insertResult['status'] != 1) {
                $this->rollback();
                return $this->format_ret(false, array(), '库存流水账生成出错:' . $insertResult['message']);
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(false, array(), '数据库操作出错:' . $e->getMessage());
        }
    }

    //获取缺货记录
    function get_short_record() {
        return $record = $this->get_all("out_num>0");
    }

    function inv_maintain($store_code) {

        //$wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($store_code);

        $wms_effect_inv = load_model('sys/ShopStoreModel')->get_no_effect_inv($store_code);
        if (!empty($wms_effect_inv)) {
            return $this->format_ret(-1, array(), '对接外部仓储或ERP，不能库存维护!');
        }
        $this->begin_trans();
        $sql = "delete from goods_inv_lof where store_code ='{$store_code}' ";
        $this->db->query($sql);
        //1实物锁定，2实物扣减，3实物增加
        $sql = "insert into goods_inv_lof(goods_code,spec1_code,spec2_code,sku,store_code,stock_num,lock_num,lof_no,production_date)
select goods_code,spec1_code,spec2_code,sku,store_code,sum(num),sum(lock_num),lof_no,production_date  from (
	select goods_code,spec1_code,spec2_code,sku,store_code,sum(num) as num,0 as lock_num,lof_no,production_date  from b2b_lof_datail where  store_code='{$store_code}' and occupy_type=3   group by sku,store_code,lof_no,production_date
	union ALL
        select goods_code,spec1_code,spec2_code,sku,store_code,sum(-num) as num,0 as lock_num,lof_no,production_date  from b2b_lof_datail where store_code='{$store_code}'  and occupy_type=2  group by sku,store_code,lof_no,production_date
	union ALL
        select goods_code,spec1_code,spec2_code,sku,store_code,0 as num,sum(num) as lock_num,lof_no,production_date  from b2b_lof_datail where store_code='{$store_code}'  and occupy_type=1  group by sku,store_code,lof_no,production_date
	union ALL
	select goods_code,spec1_code,spec2_code,sku,store_code,sum(num) as num,0 as lock_num,lof_no,production_date  from oms_sell_record_lof where  store_code='{$store_code}'  and occupy_type=3  group by sku,store_code,lof_no,production_date
        union ALL
	select goods_code,spec1_code,spec2_code,sku,store_code,sum(-num) as num,0 as lock_num,lof_no,production_date  from oms_sell_record_lof where  store_code='{$store_code}'  and occupy_type=2  group by sku,store_code,lof_no,production_date
        union ALL
	select goods_code,spec1_code,spec2_code,sku,store_code,0 as num,sum(num) as lock_num,lof_no,production_date from oms_sell_record_lof where store_code='{$store_code}'  and occupy_type=1  group by sku,store_code,lof_no,production_date

) t10 group by t10.sku,t10.store_code,t10.lof_no,t10.production_date";
        $sql .=" ON DUPLICATE KEY UPDATE stock_num=VALUES(stock_num)+stock_num,lock_num=VALUES(lock_num)+lock_num";

        $this->db->query($sql);

        //$sql = "truncate table goods_inv";
        $sql = "update  goods_inv set lock_num=0,stock_num=0,out_num=0,road_num=0  where store_code ='{$store_code}' ";
        $this->db->query($sql);

        $sql = "insert into goods_inv(goods_code,spec1_code,spec2_code,sku,store_code,stock_num,lock_num)
            select goods_code,spec1_code,spec2_code,sku,store_code,sum(stock_num) as stock_num,sum(lock_num) as lock_num from goods_inv_lof gif where gif.store_code ='{$store_code}' group by gif.sku,gif.store_code ";
        $sql .=" ON DUPLICATE KEY UPDATE stock_num=VALUES(stock_num)+stock_num,lock_num=VALUES(lock_num)+lock_num";
        $this->db->query($sql);

        $this->stock_out_inv($store_code); //维护缺货库存
        //维护在途库存
        load_model('prm/InvOpRoadModel')->inv_maintain_road($store_code);

        $this->commit();

        return $this->format_ret(1, array());
    }

    function inv_maintain_lock($store_code) {
        $this->begin_trans();
        $sql = "update   goods_inv_lof set lock_num=0   where store_code ='{$store_code}' ";
        $this->db->query($sql);

        //1实物锁定
        $sql = "insert into goods_inv_lof(goods_code,spec1_code,spec2_code,sku,store_code,lock_num,lof_no,production_date)
select goods_code,spec1_code,spec2_code,sku,store_code,sum(lock_num) as lock_num,lof_no,production_date  from (
        select goods_code,spec1_code,spec2_code,sku,store_code,sum(num) as lock_num,lof_no,production_date  from b2b_lof_datail where store_code='{$store_code}'  and occupy_type=1 group by sku,store_code,lof_no,production_date
        union ALL
	select goods_code,spec1_code,spec2_code,sku,store_code,sum(num) as lock_num,lof_no,production_date from oms_sell_record_lof where store_code='{$store_code}'  and occupy_type=1 group by sku,store_code,lof_no,production_date
) t10 group by t10.sku,t10.store_code,t10.lof_no,t10.production_date";
        $sql .=" ON DUPLICATE KEY UPDATE lock_num=VALUES(lock_num)+lock_num";

        $this->db->query($sql);
        $sql = "update   goods_inv set lock_num=0   where store_code ='{$store_code}' ";
        $this->db->query($sql);
        $sql = "insert into goods_inv(goods_code,spec1_code,spec2_code,sku,store_code,lock_num)
            select goods_code,spec1_code,spec2_code,sku,store_code,sum(lock_num) as lock_num from goods_inv_lof gif where gif.store_code ='{$store_code}' group by gif.sku,gif.store_code ";
        $sql .=" ON DUPLICATE KEY UPDATE  lock_num=VALUES(lock_num)+lock_num";
        $this->db->query($sql);
        $this->commit();
        return $this->format_ret(1, array());
    }

    var $code_arr = array('0' => 'inv_maintain', '1' => 'inv_maintain_lock', '2' => 'stock_out_inv', '3' => 'inv_maintain_road','4'=>'inv_adjust_close_lof');

    function set_inv_maintain_task($store_code, $type = 0) {
        if ($type == 0 || $type == 3) {
            $wms_effect_inv = load_model('sys/ShopStoreModel')->get_no_effect_inv($store_code);
            if (!empty($wms_effect_inv)) {
                return $this->format_ret(-1, array(), '对接外部仓储或ERP，不能库存维护!');
            }
        }
        require_model('common/TaskModel');
        $task = new TaskModel();
        $task_data = array();
        //inv_maintain_road($store_code)
        $code = $this->code_arr[$type];
        $task_data['code'] = $code . $store_code;
        $request['app_act'] = 'prm/inv/' . $code;


        $request['store_code'] = $store_code;
        $request['app_fmt'] = 'json';
        $task_data['start_time'] = time();
        $task_data['request'] = $request;
        $ret = $task->save_task($task_data);
        if ($ret['status'] < 0) {
            if ($ret['status'] == -5) {
                $ret['message'] = "库存维护中，尚未结束...";
            }
        }
        return $ret;
    }

    function get_inv_maintain_task($store_code, $type) {

        require_model('common/TaskModel');
        $task = new TaskModel();

        $code = $this->code_arr[$type] . $store_code;
        $data = $task->get_status_by_code($code);

        return $this->format_ret(1, $data);
    }

    function get_summary($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $sql_main = "";
        $sql_values = array();

        //仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $fun = isset($filter['is_entity']) && $filter['is_entity'] == 1 ? 'get_entity_store' : 'get_purview_store';
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code, $fun, 2);

        $r2 = false;
        $ret_cfg = load_model('sys/SysParamsModel', true, false, 'webefast')->get_val_by_code(array('brand_power'));
        $brand_power = $ret_cfg['brand_power'];
        $login_type = CTX()->get_session('login_type');
        if ($login_type != 2) {
            if ($brand_power == 1) {
                $r2 = true;
                $filter_brand_code = isset($filter['brand_code']) ? $filter['brand_code'] : null;
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
//            $sql_main .= " AND ( (rl.goods_code LIKE :goods_code ) OR (r2.goods_name LIKE :goods_code ) ) ";
//            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
            $arr = explode(',', $filter['goods_code']);
            $str = $this->arr_to_like_sql_value($arr, 'goods_code', $sql_values);
            $str = str_replace('goods_code like', 'rl.goods_code like', $str);
            $sql_main .= " AND " . $str;
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
            if (!empty($store_arr)) {
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
//            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            $arr = explode(',', $filter['barcode']);
            $sku_arr = array();
            foreach ($arr as &$val) {
                $barcode_sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($val);
                $sku_arr = array_merge($sku_arr, $barcode_sku_arr);
            }
            $sku_arr = array_filter($sku_arr);
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

    function imoprt_detail($request, $file) {

        $cangku_arr = $sku_num = $safe_arr = array();
        $error_msg = '';

        $num = $this->read_csv_sku($file, $sku_num, $safe_arr);
        $sku_count = count($sku_num);

        $sku_str = implode("','", $sku_num);
        $sql = "SELECT g2.barcode,g2.sku FROM `goods_inv` g1 LEFT JOIN `base_store` b ON g1.store_code = b.store_code LEFT JOIN `goods_sku` g2 ON g1.sku = g2.sku WHERE b.store_code in('{$request['store_code']}') AND g2.barcode in ('{$sku_str}') GROUP BY g2.barcode ";
        $detail_data = $this->db->get_all($sql);
        if (!empty($detail_data)) {
            foreach ($detail_data as $key => $val) {
                $detail_data[$key]['safe_num'] = $safe_arr[$val['barcode']];
                if (empty($detail_data[$key]['safe_num']) || $detail_data[$key]['safe_num'] < 0) {
                    $detail_data[$key]['safe_num'] = 0;
                }
                $sql = "UPDATE `goods_inv` SET safe_num = '{$detail_data[$key]['safe_num']}',record_time=now() WHERE store_code in('{$request['store_code']}') AND sku = '{$val['sku']}' ";
                $res = $this->db->query($sql);
                unset($safe_arr[$val['barcode']]);
            }
        }
        //执行全量库存同步
        $this->db->query("update sys_schedule_record set all_exec_time=0 where type_code='update_inv'");
        $err_num = count($safe_arr);
        $success_num = $sku_count - $err_num;

        $message = '导入成功:' . $success_num . '条';
        if ($err_num > 0 || !empty($safe_arr)) {
            $message .=',' . '失败数量:' . $err_num;
            $fail_top = array('商品条码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $safe_arr);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    private function create_import_fail_files($fail_top, $safe_arr) {
        $file_str = implode(",", $fail_top) . "\n";
        $keys = array_keys($safe_arr);
        foreach ($keys as $val) {
            $val_data = array($val, '条码不存在');
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("inv_safe_import" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function read_csv_sku($file, &$sku_num, &$safe_arr) {
        //    $key_arr = array('0'=>'sku','1'=>'num');
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $row = $this->my_iconv($row);
                if (!empty($row[0])) {
                    $cangku_arr[] = $row[0];
                    $sku_num[] = $row[0];
                    $safe_arr[$row[0]] = $row[1];
                }
            }
            $i++;
        }
        fclose($file);
        //var_dump($sku_num,$safe_arr);
        return $i;
    }

    function my_iconv($arr) {
        $result = array();
        foreach ($arr as $k => $v) {
            $result[$k] = iconv('gbk', 'utf-8', $v);
        }
        return $result;
    }

    /**
     * 获取SKU库存
     * @param string $store_code 仓库代码
     * @param array $sku_arr SKU集合
     * @param int $type 获取库存类型
     * @return array 数据集
     */
    function get_inv_by_sku($store_code, $sku_arr, $type = 0) {
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT i.stock_num,i.lock_num,i.out_num,i.safe_num,b.barcode,b.sku,MAX(i.record_time) as record_time
                FROM goods_sku b LEFT JOIN goods_inv i ON i.sku=b.sku
                WHERE i.store_code=:store_code AND b.sku IN({$sku_str}) GROUP BY b.sku";
        $sql_values[':store_code'] = $store_code;
        $data = $this->db->get_all($sql, $sql_values);

        foreach ($data as &$val) {
            switch ($type) {
                case 1:
                    $val['available_num'] = (int) $val['stock_num'] - (int) $val['lock_num'] - (int) $val['out_num'] - (int) $val['safe_num'];
                    break;
                case 2:
                    $val['available_num'] = (int) $val['stock_num'] - (int) $val['lock_num'] - (int) $val['out_num'];
                    break;
                case 3:
                    $val['available_num'] = (int) $val['stock_num'] - (int) $val['lock_num'] - (int) $val['safe_num'];
                    break;
                default:
                    $val['available_num'] = (int) $val['stock_num'] - (int) $val['lock_num'];
                    break;
            }

            $val['available_num'] = ($val['available_num'] < 0) ? 0 : $val['available_num'];
        }
        return $this->format_ret(1, $data);
    }

    /**
     *
     * 方法名                               api_goods_inv_update
     *
     * 功能描述                           更新年份数据
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-17
     * @param       array $param
     *              array(
     *                  必选: 'barcode', 'store_code', 'stock_num',
     *                  可选: 'lof_no', 'production_date'【如果系统启用批次，则此两项为必填】
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_goods_inv_update($param) {
        //获取系弘是否开启批次功能
        $ret = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));

        //是否开启批次状态
        $lof_status = (int) $ret['lof_status'];
        unset($ret);
        //分类操作
        if (1 == $lof_status) {
            //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
            $key_required = array(
                's' => array('barcode', 'store_code', 'lof_no', 'production_date'),
                'i' => array('stock_num')
            );
            //可选字段
            $key_option = array(
                's' => array('lof_price'),
            );
        } else {
            //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
            $key_required = array(
                's' => array('barcode', 'store_code'),
                'i' => array('stock_num')
            );
            //可选字段
            $key_option = array(
                's' => array('lof_no', 'production_date', 'lof_price')
            );
        }

        $arr_required = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);

        //必填项检测通过
        if (TRUE == $ret_required['status']) {
            $arr_option = array();
            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($param, $key_option, $arr_option);

            //合并数据
            $arr_deal = array_merge($arr_required, $arr_option);

            //清空无用数据
            unset($arr_required);
            unset($arr_option);

            //检测产品是否存在
            $filter = array('barcode' => $arr_deal['barcode']);
            $ret = load_model('prm/GoodsBarcodeModel')->check_exists_by_condition($filter, 'goods_barcode');
            if (1 != (int) $ret['status']) {
                return $this->format_ret("-10002", array('barcode' => $arr_deal['barcode']), "API_RETURN_MESSAGE_10002");
            }

            //提取产品信息
            $goods = $ret['data'];
            unset($ret);

            //检测仓库是否存在
            $ret = load_model('base/StoreModel')->get_by_code($arr_deal['store_code']);
            if (1 != (int) $ret['status']) {
                return $this->format_ret("-10002", array('store_code' => $arr_deal['store_code']), "API_RETURN_MESSAGE_10002");
            }

            //提取仓库信息
            $store = $ret['data'];
            unset($ret);

            $is_add = 0;
            //提取批次信息
            if (1 == $lof_status) {
                $ret = load_model('prm/GoodsLofModel')->is_exists_lof($goods['sku'], $arr_deal['lof_no'], $arr_deal['production_date']);
                if (1 != (int) $ret['status']) {
                    $is_add = 1;
                    $goods_lof = $arr_deal;
                    $goods_lof['sku'] = $goods['sku'];
                } else {
                    $goods_lof = $ret['data'];
                }
            } else {
                $ret = load_model('prm/GoodsLofModel')->get_sys_lof();
                if (1 != (int) $ret['status']) {
                    return $this->format_ret("-10002", array('default_lof' => 'default_lof'), "API_RETURN_MESSAGE_10002");
                } else {
                    $goods_lof = $ret['data'];
                }
            }

            //查询库存批次库是否存在客户提交数据
            $filter = array(
                'sku' => $goods['sku'],
                'store_code' => $store['store_code'],
                'lof_no' => $goods_lof['lof_no'],
                'production_date' => $goods_lof['production_date']
            );
            $ret = load_model('prm/InvLofModel')->check_exists_by_condition($filter);

            //因为有三表操作作故开启事务
            $this->begin_trans();
            try {
                $lof_inv_num = 0;
                if (1 == (int) $ret['status']) {
                    //获取批次库存信息
                    $goods_inv_lof = $ret['data'];
                    $lof_inv_num = $goods_inv_lof['stock_num'];
                    unset($ret);
                }
                if (!empty($arr_deal['lof_price']) && $is_add == 0) {
                    $ret = $this->update_exp('goods_lof', array('lof_price' => $arr_deal['lof_price']), array("sku" => $goods['sku'], "lof_no" => $arr_deal['lof_no'], "production_date" => $arr_deal['production_date']));
                    if ($ret['status'] < 0) {
                        throw new Exception('批次库存更新批次价格失败');
                    }
                } else if ($is_add == 1) {
                    $ret = $this->insert_exp('goods_lof', $goods_lof);
                    if ($ret['status'] < 0) {
                        throw new Exception('批次添加失败');
                    }
                }

                //批次库存插入数据
                $data = array(
                    0 => array(
                        'goods_id' => $goods['goods_id'], 'goods_code' => $goods['goods_code'],
                        'spec1_id' => $goods['spec1_id'], 'spec1_code' => $goods['spec1_code'],
                        'spec2_id' => $goods['spec2_id'], 'spec2_code' => $goods['spec2_code'], 'sku' => $goods['sku'],
                        'store_id' => $store['store_id'], 'store_code' => $store['store_code'],
                        'stock_num' => $arr_deal['stock_num'],
                        'lock_num' => 0,
                        'lof_no' => $goods_lof['lof_no'],
                        'production_date' => $goods_lof['production_date']
                    )
                );

                //插入兼更新批次库存表
                $update_str = "stock_num = VALUES(stock_num)";
                $r = $this->insert_multi_duplicate('goods_inv_lof', $data, $update_str);
                if (1 != (int) $r['status']) {
                    throw new Exception('批次库存更新失败');
                }
                unset($update_str);
                unset($data);
                unset($r);

                //查询主库存库是否存在客户提交数据
                $filter = array(
                    'sku' => $goods['sku'],
                    'store_code' => $store['store_code']
                );
                $ret = $this->check_exists_by_condition($filter);
                $inv_num = 0;
                if (1 == (int) $ret['status']) {
                    //获取批次库存信息
                    $goods_inv = $ret['data'];
                    $inv_num = $goods_inv['stock_num'];
                    unset($ret);
                }
                $sql_inv = "select sum(stock_num) from goods_inv_lof where store_code=:store_code AND sku=:sku ";
                $inv_stock_num = $this->db->get_value($sql_inv, array(':store_code' => $store['store_code'], ':sku' => $goods['sku']));

                //主库存插入数据
                $data = array(
                    0 => array(
                        'goods_id' => $goods['goods_id'], 'goods_code' => $goods['goods_code'],
                        'org_code' => '000',
                        'spec1_id' => $goods['spec1_id'], 'spec1_code' => $goods['spec1_code'],
                        'spec2_id' => $goods['goods_code'], 'spec2_code' => $goods['spec2_code'],
                        'sku' => $goods['sku'],
                        'store_id' => $store['store_id'], 'store_code' => $store['store_code'],
                        'stock_num' => $inv_stock_num, 'lock_num' => 0,
                        'frozen_num' => 0, 'pre_sale_num' => 0,
                        'pre_sale_lock_num' => 0, 'out_num' => 0,
                        'road_num' => 0, 'safe_num' => 0,
                        'record_time' => date('Y-m-d H:i:s'),
                    )
                );

                //插入兼更新主库存表
                $update_str = "stock_num = VALUES(stock_num),record_time = VALUES(record_time)";
                $r = $this->insert_multi_duplicate('goods_inv', $data, $update_str);
                if (1 != (int) $r['status']) {
                    throw new Exception('主库存更新失败');
                }


                unset($data);
                unset($r);
                unset($update_str);

                //批次库存更新前库存数
                $stock_lof_num_before_change = isset($goods_inv_lof) ? $goods_inv_lof['stock_num'] : 0;
                //批次库存更新后的库存量
                $stock_lof_num_after_change = $arr_deal['stock_num'];
                //在库变动数量
                $stock_change_num = $stock_lof_num_after_change - $stock_lof_num_before_change;
                //主库存变动前库存量
                $stock_num_before_change = isset($goods_inv) ? $goods_inv['stock_num'] : 0;
                //主库存变动后的库存量
                $stock_num_after_change = $stock_num_before_change + $stock_change_num;
                //主库锁定库存变化前
                $lock_num_before_change = isset($goods_inv) ? $goods_inv['lock_num'] : 0;
                //批次库锁定库存变化前
                $lock_lof_num_before_change = isset($goods_inv_lof) ? $goods_inv_lof['lock_num'] : 0;

                //日志基础数据
                $data = array(
                    'goods_id' => $goods['goods_id'], 'goods_code' => $goods['goods_code'], 'spec1_id' => $goods['spec1_id'],
                    'spec1_code' => $goods['spec1_code'], 'spec2_id' => $goods['spec2_id'], 'spec2_code' => $goods['spec2_code'],
                    'sku' => $goods['sku'], 'lof_no' => $goods_lof['lof_no'], 'production_date' => $goods_lof['production_date'],
                    'store_code' => $store['store_code'], 'occupy_type' => 3,
                    'stock_change_num' => $stock_change_num,
                    'stock_lof_num_before' => $stock_lof_num_before_change, 'stock_lof_num_after_change' => $stock_lof_num_after_change,
                    'stock_num_before_change' => $stock_num_before_change, 'stock_num_after_change' => $stock_num_after_change,
                    'lock_change_num' => 0, 'lock_num_before_change' => $lock_num_before_change,
                    'lock_num_after_change' => $lock_num_before_change, 'lock_lof_num_before_change' => $lock_lof_num_before_change, 'lock_lof_num_after_change' => $lock_lof_num_before_change,
                    'frozen_change_num' => 0, 'frozen_num_before_change' => 0, 'frozen_num_after_change' => 0,
                    'road_change_num' => 0, 'road_num_before_change' => 0, 'road_num_after_change' => 0,
                    'record_time' => date('Y-m-d H:i:s'), 'object_code' => '', 'relation_code' => '',
                    'relation_type' => 'api', 'remark' => ''
                );

                //插入库存日志
                $r = $this->insert_exp('goods_inv_record', $data);
                if (1 != (int) $r['status']) {
                    throw new Exception('主库存更新失败');
                }
                unset($data);
                unset($r);
                //以上操作均成功，提交事务
                $this->commit();
                return $this->format_ret("1", '', "insert_success");
            } catch (Exception $e) {
                //异常处理，回滚事务
                $this->rollback();
                return array('status' => -1, 'message' => $e->getMessage());
            }
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
    }

    /**
     * @todo 获取调整单验收失败的sku
     * @description 当调负时,若调整数量的绝对值大于商品实际库存数量,则会使商品库存为负,此时导出这样的sku
     */
    function get_fail_sku($goods_details) {
        foreach ($goods_details as $detail) {
            //当调负时
            if ($detail['num'] < 0) {
                //$goods_inv_ret = load_model('prm/InvModel')->get_by_field('sku', $detail['sku'], 'stock_num');
                $sql ="select stock_num from goods_inv where sku=:sku AND store_code=:store_code ";
                $goods_inv_stock_num = $this->db->get_value($sql,array(':sku'=>$detail['sku'],':store_code'=>$detail['store_code']));
                //$goods_inv_stock_num = $goods_inv_ret['data']['stock_num'];
                $goods_inv_lof_stock_num = load_model('prm/InvLofModel')->get_lof_inv_num(array('sku' => $detail['sku'], 'store_code' => $detail['store_code'], 'lof_no' => $detail['lof_no']));
                if ($goods_inv_lof_stock_num < abs($detail['num']) || $goods_inv_stock_num < abs($detail['num'])) {
                    $fail_sku[] = $detail['sku'];
                }
            }
        }
        return $fail_sku;
    }

    public function get_goods_diy_sku_inv($filter = array()) {
        //todo 强制不分页
        $filter['page_size'] = isset($filter['page_size']) ? $filter['page_size'] : 10000;
        $sql_values = array();
        $sql_join = "";
        if (!isset($filter['store_code']) || $filter['store_code'] == '') {
            return $this->format_ret('-1', '', '仓库代码必填');
        }
        $sql_main = " FROM goods_inv_lof r1
                          LEFT JOIN base_goods r2 on r1.goods_code = r2.goods_code
                          LEFT JOIN goods_sku r4 on r1.sku = r4.sku
                          WHERE 1 ";
        $sql_main .= " AND r2.status = :status ";
        //                          LEFT JOIN goods_price r5 on r1.goods_code = r5.goods_code
        $sql_values[':status'] = 0;
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code);

        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_arr = implode("','", $store_code_arr);
            $store_code_arr = "'" . $store_code_arr . "'";
            if (!empty($store_code_arr)) {
                $sql_main .= " AND r1.store_code in($store_code_arr) ";
            }
        }

        //是否组装商品
        if (isset($filter['diy']) && $filter['diy'] != '') {
            $sql_main .= " AND r2.diy = :diy ";
            $sql_values[':diy'] = $filter['diy'];
        }

        //是否组装商品
        if (isset($filter['diy_goods_lof']) && $filter['diy_goods_lof'] != '') {
            $diy_goods_lof = implode("','", $filter['diy_goods_lof']);
            $diy_goods_lof = "'" . $diy_goods_lof . "'";
            if (!empty($diy_goods_lof)) {
                $sql_main .= " AND r4.sku in($diy_goods_lof) ";
            }
        }


        $sql_main = $sql_main . " GROUP BY r1.sku,r1.lof_no,r1.lastchanged ";
        $select = 'r1.goods_inv_id ,r1.stock_num,r1.lock_num,r1.sku,r1.lof_no,r1.production_date,r1.spec1_code,r1.spec2_code,r1.goods_code,r2.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,r2.sell_price,r2.price,r2.purchase_price,r2.trade_price';


        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        //  filter_fk_name($data['data'], array('spec1_code|spec1_code', 'spec2_code|spec2_code'));
        foreach ($data['data'] as &$val) {
            $val['is_combo'] = 0;
            $val['available_mum'] = (int) $val['stock_num'] - (int) $val['lock_num'];
            $val['available_mum'] = ($val['available_mum'] < 0) ? 0 : $val['available_mum'];
            $val['spec1_code_name'] = $val['spec1_name'] . "[" . $val['spec1_code'] . "]";
            $val['spec2_code_name'] = $val['spec2_name'] . "[" . $val['spec2_code'] . "]";
            $val['diy_sku'] = (isset($filter['diy_good']) && $filter['diy_good'] != '') ? $filter['diy_good'] : '';
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        //获取套餐商品
        $this->get_sku_combo_inv($filter, $ret_data);

        return $this->format_ret($ret_status, $ret_data);
    }

    function edit_safe_num($request) {
        $sql = "SELECT * FROM goods_inv WHERE goods_inv_id='{$request['goods_inv_id']}'";
        $value = $this->db->get_row($sql);
        if ($value['safe_num'] != $request['safe_num']) {
            $ret = CTX()->db->update("goods_inv", array('safe_num' => $request['safe_num'], 'record_time' => date("Y-m-d H:i:s")), array('goods_inv_id' => $request['goods_inv_id']));
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $inv_record = array();
            $inv_record['relation_type'] = 'safe_inv';
            $inv_record['store_code'] = $value['store_code'];
            $inv_record['goods_code'] = $value['goods_code'];
            $inv_record['spec1_code'] = $sku_info['spec1_code'];
            $inv_record['spec2_code'] = $sku_info['spec2_code'];
            $inv_record['sku'] = $value['sku'];
            $inv_record['lock_num_before_change'] = $value['lock_num'];
            $inv_record['lock_num_after_change'] = $value['lock_num'];
            $inv_record['lock_change_num'] = 0;
            $inv_record['stock_num_before_change'] = $value['stock_num'];
            $inv_record['stock_num_after_change'] = $value['stock_num'];
            $inv_record['stock_change_num'] = 0;
            $inv_record['record_time'] = date("Y-m-d H:i:s");
            $inv_record['remark'] = '安全库存变动';
            if ($ret != true) {
                return $this->format_ret(-1, '', '更新失败');
            } else {
                load_model('prm/InvRecordModel')->insert($inv_record);
                return $this->format_ret(1);
            }
        }

        return $this->format_ret(1);
    }

    /**
     * 库存查询（商品）
     */
    function get_by_page_goods($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl
        			LEFT JOIN base_goods r2 on rl.goods_code = r2.goods_code
        			WHERE 1 ";

        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        if (isset($filter['list_type']) && $filter['list_type'] == 'fx_goods' && !(isset($filter['store_code']) && $filter['store_code'] != '')) {
            $store_str = load_model('base/StoreModel')->get_fx_store_sql();
            $filter_store_code = empty($store_str) ? NULL : $store_str;
        } else {
            //仓库权限
            $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        }
        $fun = isset($filter['is_entity']) && $filter['is_entity'] == 1 ? 'get_entity_store' : 'get_purview_store';
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code, $fun, 2);

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
        //启用状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND r2.status = :status ";
            $sql_values[':status'] = $filter['status'];
        }

        //仓库类别
        if (isset($filter['store_type_code']) && $filter['store_type_code'] != '') {
            $store_arr = load_model('base/StoreModel')->get_by_store_code_type($filter['store_type_code']);
            if (!empty($store_arr)) {
                $sql_main .= " AND rl.store_code in (:store_type_code) ";
                $sql_values[':store_type_code'] = $store_arr;
            } else {
                $sql_main .= " AND 1 = 2 ";
            }
        }
        if (isset($filter['goods_group_val']) && $filter['goods_group_val'] != 1) {
            $sql_main .= " group by rl.goods_code,rl.store_code  ORDER BY rl.goods_code ASC ";
            $select = 'SELECT sum(rl.stock_num)-sum(rl.lock_num) AS effec_num,sum(rl.stock_num) as stock_num,sum(rl.lock_num) as lock_num,sum(rl.out_num) as out_num,sum(rl.road_num) as road_num,sum(rl.safe_num) as safe_num,r2.goods_name,r2.category_name,r2.goods_img,rl.store_code,rl.goods_code ';
            $sql = $select . $sql_main;
        } else {
            $sql_main .= "  group by rl.goods_code   ORDER BY rl.goods_code ASC ";
            $select = "SELECT sum(rl.stock_num)-sum(rl.lock_num) AS effec_num,sum(rl.stock_num) as stock_num,sum(rl.lock_num) as lock_num,sum(rl.out_num) as out_num,sum(rl.road_num) as road_num,sum(rl.safe_num) as safe_num,";
            $select .= 'r2.goods_name,r2.goods_code,r2.category_code,r2.category_name,r2.goods_img ';
            $sql = $select . $sql_main;
        }
        $ret = array(
            'sql_values' => $sql_values,
            'sql' => $sql
        );
        return $ret;
    }

    /**
     * 列表查询
     * @param type $filter
     * @return type
     */
    function get_goods_list_by_page($filter) {
        $ret = $this->get_by_page_goods($filter);
        $sql_main = "FROM (" . $ret['sql'] . " ) AS t WHERE 1";
        $sql_values = $ret['sql_values'];
        $select = "t.*";
        if (isset($filter['num_start']) && $filter['num_start'] != '' && $filter['goods_group_val'] != 1) {
            switch ($filter['is_num']) {
                case 'effec_num':  //可用库存
                    $sql_main .= " AND t.effec_num >= :num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
                case 'road_num':  //在途库存
                    $sql_main .= " AND t.road_num >= :num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
                case 'safe_num':  //安全库存
                    $sql_main .= " AND t.safe_num >= :num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
                case 'out_num':  //缺货库存
                    $sql_main .= " AND t.out_num >= :num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
                case 'stock_num':  //实物库存
                    $sql_main .= " AND t.stock_num >= :num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
            }
        }
        if (isset($filter['num_end']) && $filter['num_end'] != '' && $filter['goods_group_val'] != 1) {
            switch ($filter['is_num']) {
                case 'effec_num':  //可用库存
                    $sql_main .= " AND t.effec_num <= :num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
                case 'road_num':  //在途库存
                    $sql_main .= " AND t.road_num <= :num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
                case 'safe_num':  //安全库存
                    $sql_main .= " AND t.safe_num <= :num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
                case 'out_num':  //缺货库存
                    $sql_main .= " AND t.out_num <= :num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
                case 'stock_num':  //实物库存
                    $sql_main .= " AND t.stock_num<= :num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
            }
        }
        //安全库存查询
        if (isset($filter['less_than_safe_num']) && $filter['less_than_safe_num'] != '' && $filter['goods_group_val'] != 1) {
            if ($filter['less_than_safe_num'] == 1) {//低于安全库存的商品
                $sql_main .= " AND t.stock_num < t.safe_num";
            }
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, TRUE);
        foreach ($data['data'] as $key => &$value) {
            if (!empty($value['goods_img'])) {
                $value['goods_pic'] = "<img width='50px' height='50px' src='{$value['goods_img']}' />";
            }
            //获取扩展属性
            $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
            $property_power = $ret_cfg['property_power'];
            if ($property_power) {
                $goods_property = load_model('prm/GoodsModel')->get_export_property($value['goods_code']);
                $data['data'][$key] = $goods_property != -1 && is_array($goods_property) ? array_merge($data['data'][$key], $goods_property) : $data['data'][$key];
            }
            if (!empty($value['goods_img'])) {
                $value['goods_pic'] = "<img width='50px' height='50px' data-goods-img='{$value['goods_img']}' src='{$value['goods_img']}' />";
            }

        }
        $ret_status = OP_SUCCESS;
        if ($filter['goods_group_val'] != 1) {
            filter_fk_name($data['data'], array('store_code|store'));
        }
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 汇总
     * @param type $filter
     * @return type
     */
    function get_goods_count($filter) {
        $ret = $this->get_by_page_goods($filter);
        $sql_main = "FROM (" . $ret['sql'] . " ) AS t WHERE 1";
        $sql_values = $ret['sql_values'];
        if (isset($filter['num_start']) && $filter['num_start'] != '' && $filter['goods_group_val'] != 1) {
            switch ($filter['is_num']) {
                case 'effec_num':  //可用库存
                    $sql_main .= " AND t.effec_num >= :num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
                case 'road_num':  //在途库存
                    $sql_main .= " AND t.road_num >= :num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
                case 'safe_num':  //安全库存
                    $sql_main .= " AND t.safe_num >= :num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
                case 'out_num':  //缺货库存
                    $sql_main .= " AND t.out_num >= :num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
                case 'stock_num':  //实物库存
                    $sql_main .= " AND t.stock_num >= :num_start ";
                    $sql_values[':num_start'] = $filter['num_start'];
                    break;
            }
        }
        if (isset($filter['num_end']) && $filter['num_end'] != '' && $filter['goods_group_val'] != 1) {
            switch ($filter['is_num']) {
                case 'effec_num':  //可用库存
                    $sql_main .= " AND t.effec_num <= :num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
                case 'road_num':  //在途库存
                    $sql_main .= " AND t.road_num <= :num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
                case 'safe_num':  //安全库存
                    $sql_main .= " AND t.safe_num <= :num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
                case 'out_num':  //缺货库存
                    $sql_main .= " AND t.out_num <= :num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
                case 'stock_num':  //实物库存
                    $sql_main .= " AND t.stock_num<= :num_end ";
                    $sql_values[':num_end'] = $filter['num_end'];
                    break;
            }
        }
        if (isset($filter['less_than_safe_num']) && $filter['less_than_safe_num'] != '' && $filter['goods_group_val'] != 1) {
            if ($filter['less_than_safe_num'] == 1) {//低于安全库存的商品
                $sql_main .= " AND t.stock_num < t.safe_num";
            }
        }
        $sql = "SELECT t.* " . $sql_main;
        $data = $this->db->get_all($sql, $sql_values);
        $sum_num = array();
        //总在途库存
        $sum_num['road_num'] = 0;
        //总缺货库存
        $sum_num['out_num'] = 0;
        //总可用库存
        $sum_num['available_mum'] = 0;
        //总锁定库存
        $sum_num['lock_num'] = 0;
        //总实物库存
        $sum_num['stock_num'] = 0;
        //总安全库存
        $sum_num['safe_num'] = 0;
        foreach ($data as $key => $value) {
            $sum_num['road_num']+=$value['road_num'];
            $sum_num['out_num']+=$value['out_num'];
            $sum_num['lock_num']+=$value['lock_num'];
            $sum_num['stock_num']+=$value['stock_num'];
            $sum_num['safe_num']+=$value['safe_num'];
        }
        $sum_num['available_mum'] = $sum_num['stock_num'] - $sum_num['lock_num'];
        return $sum_num;
    }
 

    function inv_adjust_close_lof($store_code) {
        //库存维护
//        // $this->set_inv_maintain_task($store_code);
//        $task_code = 'inv_maintain' . $store_code;
//        $task_row = load_model('TaskModel/common')->get_status_by_code($task_code);
//        if ($task_row['is_over'] != 2) {
//            return $this->format_ret(-1, '', '库存维护暂未结束！');
//        }
//        $time = time();
//        if ($task_row['over_time'] - $time > 3600) {
//            return $this->format_ret(-1, '', '1小时内无库存维护，不允许！');
//        }
        $ret_lof = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $lof_status = (int) $ret_lof['lof_status'];
        if ($lof_status == 1) {
            return $this->format_ret(-1, '', '必须关闭批次，才可以进行批准数据修正！');
        }

        $this->inv_maintain($store_code);
        //批次清0调整单
        $stock_adjust['record_code'] = load_model('stm/StockAdjustRecordModel')->create_fast_bill_sn();

        $stock_adjust['init_code'] = '';
        $stock_adjust['store_code'] = $store_code;
        $stock_adjust['record_time'] = date('Y-m-d H:i:s');
        $stock_adjust['adjust_type'] = 800; //批次期初调整
        $stock_adjust['remark'] = '修复开启批次的数据'; //批次期初调整


        $ret = load_model('stm/StockAdjustRecordModel')->insert($stock_adjust);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $stock_adjust_id = $ret['data'];
        $sql = "insert into b2b_lof_datail (pid,order_code,order_type,goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,num)
    select {$stock_adjust_id} as stock_adjust_id, '{$stock_adjust['record_code']}' as record_code,'adjust',goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,-stock_num as num from goods_inv_lof where  store_code='{$store_code}' ";
        $this->db->query($sql);
        
        
        $sql = "insert into  stm_stock_adjust_record_detail(pid,record_code,goods_code,spec1_code,spec2_code,sku,num,refer_price,price,rebate,money)";

        $sql .= " select b.pid,b.order_code,s.goods_code,s.spec1_code,s.spec2_code,s.sku,sum(b.num),g.sell_price,g.sell_price,1 as rebate,g.sell_price*sum(b.num) as money from b2b_lof_datail b
        inner join  goods_sku s ON  s.sku=b.sku
        inner join  base_goods g ON  g.goods_code=s.goods_code
        where  b.order_code='{$stock_adjust['record_code']}'
        group by b.sku";
        $this->db->query($sql);

        //批次调整正常调整单

        $n_stock_adjust['record_code'] = load_model('stm/StockAdjustRecordModel')->create_fast_bill_sn();

        $n_stock_adjust['init_code'] = '';
        $n_stock_adjust['store_code'] = $store_code;
        $n_stock_adjust['record_time'] = date('Y-m-d H:i:s');
        $n_stock_adjust['adjust_type'] = 800; //批次期初调整
        $n_stock_adjust['remark'] = '修复开启批次的数据'; //批次期初调整
        $ret_n = load_model('stm/StockAdjustRecordModel')->insert($n_stock_adjust);
        if ($ret_n['status'] < 1) {
            return $ret;
        }
        $n_stock_adjust_id = $ret_n['data'];
        $lof_info = $this->db->get_row("select lof_no,production_date from goods_lof where type=1");
        $lof_no = $lof_info['lof_no'];
        $production_date = $lof_info['production_date'];
        $sql_insert = "insert into b2b_lof_datail (pid,order_code,order_type,goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,num)";

        $sql_select = "  select {$n_stock_adjust_id} as pid,'{$n_stock_adjust['record_code']}' as order_code, 'adjust' as order_type,goods_code,spec1_code,spec2_code,sku,store_code,'{$lof_no}' as lof_no,'{$production_date}' as production_date,sum(num)*-1 as num from b2b_lof_datail where  order_code='{$stock_adjust['record_code']}' AND order_type='adjust' GROUP BY sku ";
        $this->db->query($sql_insert . $sql_select);
        
         $sql_n = "insert into  stm_stock_adjust_record_detail(pid,record_code,goods_code,spec1_code,spec2_code,sku,num,refer_price,price,rebate,money)";
    
        $sql_n .= " select b.pid,b.order_code,s.goods_code,s.spec1_code,s.spec2_code,s.sku,sum(b.num),g.sell_price,g.sell_price,1 as rebate,g.sell_price*sum(b.num) as money from b2b_lof_datail b
        inner join  goods_sku s ON  s.sku=b.sku
        inner join  base_goods g ON  g.goods_code=s.goods_code
        where  b.order_code='{$n_stock_adjust['record_code']}'
        group by b.sku";
        $this->db->query($sql_n);
  
        
        $sql_check ="select sum(num) as num from b2b_lof_datail where order_type='adjust'  AND order_code in ('{$n_stock_adjust['record_code']}','{$stock_adjust['record_code']}') GROUP BY  sku ";
        $num =  $this->db->get_value($sql_check);
        if($num!=0){
            return $this->format_ret(-1, '', '修复数据出现异常！');
        }
        
        load_model('stm/StmStockAdjustRecordDetailModel')->mainWriteBack($stock_adjust_id);
        load_model('stm/StmStockAdjustRecordDetailModel')->mainWriteBack($n_stock_adjust_id);
        $this->begin_trans();
        $ret1 = load_model('stm/StockAdjustRecordModel')->checkin($stock_adjust_id);
        if ($ret1['status'] < 1) {
            $this->rollback();
            return $ret1;
        }

        $ret2 = load_model('stm/StockAdjustRecordModel')->checkin($n_stock_adjust_id);
        if ($ret2['status'] < 1) {
            $this->rollback();
            return $ret2;
        }
        $this->commit();
        //修复锁定
        $where = "store_code='{$store_code}'  AND occupy_type = 1 ";
        $this->update_exp('b2b_lof_datail', $lof_info, $where);
        $this->update_exp('oms_sell_record_lof', $lof_info, $where);
        $this->update_exp('goods_inv_lof', array('lock_num'=>0), " store_code='{$store_code}'  AND lock_num>0");
        //锁定维护
        $this->inv_maintain_lock($store_code);
        return $this->format_ret(1);
    }

}
