<?php

/**
 * 商品相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('prm');
require_lib('util/oms_util', true);

class GoodsModel extends TbModel {

    //商品属性
    public $prop = array(array('0' => '0', '1' => '普通商品'), array('0' => '1', '1' => '补邮商品'), array('0' => '2', '1' => '赠品'), array('0' => '3', '1' => '进口商品'));
    //商品状态
    public $state = array(array('0' => '0', '1' => '在售'), array('0' => '1', '1' => '在库'));
    //操作状态
    public $status = array(array('0' => '0,1', '1' => '全部'), array('0' => '0', '1' => '启用'), array('0' => '1', '1' => '停用'));

    function get_table() {
        return 'base_goods';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_join = "";
        if(isset($filter['ctl_export_conf'])&&$filter['ctl_export_conf']==='goods_record_list_detail'){
            $sql_join=" LEFT JOIN goods_barcode r2 on rl.goods_code=r2.goods_code LEFT JOIN goods_diy r3 on r3.p_sku=r2.sku and r3.p_goods_code=r2.goods_code";
        }
        $sql_main = "FROM {$this->table} rl {$sql_join} WHERE 1 ";
        $filter_brand_code = isset($filter['brand_code']) ? $filter['brand_code'] : null;
        if (!empty($filter['supplier_code'])) {
            $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
            $sql_main .= load_model('base/SupplierModel')->get_sql_purview_supplier('rl.supplier_code', $filter_supplier_code);
        }
        //分类
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $category_code_arr = explode(',', $filter['category_code']);
            if (!empty($category_code_arr)) {
                $sql_main .= " AND (";
                foreach ($category_code_arr as $key => $value) {
                    $param_category = 'param_category' . $key;
                    if ($key == 0) {
                        $sql_main .= " rl.category_code = :{$param_category} ";
                    } else {
                        $sql_main .= " or rl.category_code = :{$param_category} ";
                    }

                    $sql_values[':' . $param_category] = $value;
                }
                $sql_main .= ")";
            }
        }

        //品牌
        $sql_main .= load_model('prm/BrandModel', true, false, 'webefast')->get_sql_purview_brand('rl.brand_code', $filter_brand_code);

        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $brand_code_arr = explode(',', $filter['brand_code']);
            if (!empty($brand_code_arr)) {
                $sql_main .= " AND (";
                foreach ($brand_code_arr as $key => $value) {
                    $param_brand = 'param_brand' . $key;
                    if ($key == 0) {
                        $sql_main .= " rl.brand_code = :{$param_brand} ";
                    } else {
                        $sql_main .= " or rl.brand_code = :{$param_brand} ";
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
                        $sql_main .= " rl.year_code = :{$param_year} ";
                    } else {
                        $sql_main .= " or rl.year_code = :{$param_year} ";
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
                        $sql_main .= " rl.season_code = :{$param_season} ";
                    } else {
                        $sql_main .= " or rl.season_code = :{$param_season} ";
                    }

                    $sql_values[':' . $param_season] = $value;
                }
                $sql_main .= ")";
            }
        }
        //状态
        if (isset($filter['state']) && $filter['state'] != '') {
            $state_arr = explode(',', $filter['state']);
            if (!empty($state_arr)) {
                $sql_main .= " AND (";
                foreach ($state_arr as $key => $value) {
                    $param_state = 'param_state' . $key;
                    if ($key == 0) {
                        $sql_main .= " rl.state = :{$param_state} ";
                    } else {
                        $sql_main .= " or rl.state = :{$param_state} ";
                    }

                    $sql_values[':' . $param_state] = $value;
                }
                $sql_main .= ")";
            }
        }
        //操作状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $status_arr = explode(',', $filter['status']);
            if (!empty($status_arr)) {
                $sql_main .= " AND (";
                foreach ($status_arr as $key => $value) {
                    $param_status = 'param_status' . $key;
                    if ($key == 0) {
                        $sql_main .= " rl.status = :{$param_status} ";
                    } else {
                        $sql_main .= " or rl.status = :{$param_status} ";
                    }

                    $sql_values[':' . $param_status] = $value;
                }
                $sql_main .= ")";
            }
        }
        //属性
        if (isset($filter['goods_prop']) && $filter['goods_prop'] != '') {
            $goods_prop_arr = explode(',', $filter['goods_prop']);
            if (!empty($goods_prop_arr)) {
                $sql_main .= " AND (";
                foreach ($goods_prop_arr as $key => $value) {
                    $param_goods_prop = 'param_goods_prop' . $key;
                    if ($key == 0) {
                        $sql_main .= " rl.goods_prop = :{$param_goods_prop} ";
                    } else {
                        $sql_main .= " or rl.goods_prop = :{$param_goods_prop} ";
                    }

                    $sql_values[':' . $param_goods_prop] = $value;
                }
                $sql_main .= ")";
            }
        }
        //是否设置分销款
        if (isset($filter['is_custom_money']) && $filter['is_custom_money'] !== '') {
            $sql_main .= " AND rl.is_custom_money = :is_custom_money ";
            $sql_values[':is_custom_money'] = $filter['is_custom_money'];
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $filter['goods_code']=str_replace('，',',',$filter['goods_code']);
            $arr = explode(',', $filter['goods_code']);
            $str = $this->arr_to_like_sql_value($arr, 'goods_code', $sql_values,'rl.');
            $sql_main .= " AND " . $str;
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql_main .= " AND rl.goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        //商品名称
        if (isset($filter['goods_short_name']) && $filter['goods_short_name'] !== '') {
            $sql_main .= " AND rl.goods_short_name LIKE :goods_short_name ";
            $sql_values[':goods_short_name'] = '%' . $filter['goods_short_name'] . '%';
        }
        //商品出厂名称
        if (isset($filter['goods_produce_name']) && $filter['goods_produce_name'] !== '') {
            $sql_main .= " AND rl.goods_produce_name LIKE :goods_produce_name ";
            $sql_values[':goods_produce_name'] = '%' . $filter['goods_produce_name'] . '%';
        }
        //是否组装商品
        if (isset($filter['diy']) && $filter['diy'] !== '') {
            $sql_main .= " AND rl.diy = :diy ";
            $sql_values[':diy'] = $filter['diy'];
        }

        //api模式下按数据库更新时间戳逆序排序@2015-5-15 +++++++++++++++++++++++
        if (isset($filter['lastchanged']) && $filter['lastchanged'] !== '') {
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
        $select = 'rl.*';
        if(isset($filter['ctl_export_conf'])&&$filter['ctl_export_conf']==='goods_record_list_detail'){
            $select ='rl.goods_name,rl.goods_short_name,rl.goods_code,r2.spec1_code,r2.spec2_code,r2.sku,r2.barcode,r3.num,r3.sku as sku_diy,r3.goods_code as goods_code_diy,r3.spec1_code as spec1_code_diy,r3.spec2_code as spec2_code_diy';
            $sql_main .=' order by rl.goods_code desc,r2.barcode desc';
        }else{
            if (isset($filter['is_api']) && $filter['is_api'] !== '') {
                $sql_main .= " order by rl.lastchanged desc ";
            } else {
                $sql_main .= " order by rl.is_add_time desc ";
            }
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        if (empty($data['data'])) {
            return $this->format_ret(1, $data);
        }
        if(isset($filter['ctl_export_conf'])&&$filter['ctl_export_conf']==='goods_record_list_detail') {
            foreach ($data['data'] as $k => $value) {
                $data['data'][$k]['spec1_name'] = load_model('prm/Spec1Model')->get_spec1_name($value['spec1_code']);
                $data['data'][$k]['spec2_name'] = load_model('prm/Spec2Model')->get_spec2_name($value['spec2_code']);
                $data['data'][$k]['spec1_name_diy'] = load_model('prm/Spec1Model')->get_spec1_name($value['spec1_code_diy']);
                $data['data'][$k]['spec2_name_diy'] = load_model('prm/Spec2Model')->get_spec2_name($value['spec2_code_diy']);
                $data['data'][$k]['goods_name_diy'] = $this->get_goods_name($value['goods_code_diy']);
                $arr_price1 = load_model('goods/SkuCModel')->get_sku_info($value['sku_diy']);
                $data['data'][$k]['price_diy'] =  empty($arr_price1['price']) ? $arr_price1['sell_price'] : $arr_price1['price'];
                $data['data'][$k]['barcode_diy'] = $arr_price1['barcode'];
                $data['data'][$k]['goods_code_diy']=$arr_price1['goods_code'];
            }
                $ret_status = OP_SUCCESS;
                $ret_data = $data;
                return $this->format_ret($ret_status, $ret_data);

        }
        $supplier = load_model('base/ArchiveSearchModel')->get_archives_map('supplier');

        //价格管控判断
        $status_cost_price = load_model('sys/RoleManagePriceModel')->get_user_permission_price('cost_price', $filter['user_id']);
        $status_pur_price = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $filter['user_id']);

        foreach ($data['data'] as $key => &$value) {
            if ($filter['ctl_type'] == 'export') {
                //规格1
                $sql = "select distinct spec1_name FROM goods_sku where goods_code='{$value['goods_code']}'";
                $spec1_arr = $this->db->get_all_col($sql);
                $value['spec1_name'] = implode(' ', $spec1_arr);

                //规格2
                $sql = "select distinct spec2_name FROM goods_sku where goods_code='{$value['goods_code']}' ";
                $spec2_arr = $this->db->get_all_col($sql);
                $value['spec2_name'] = implode(' ', $spec2_arr);
            }

            $value['sell_price'] = isset($value['sell_price']) ? round($value['sell_price'], 2) : '';
            $value['cost_price'] = isset($value['cost_price']) ? round($value['cost_price'], 2) : '';
            $value['trade_price'] = isset($value['trade_price']) ? round($value['trade_price'], 2) : '';
            $value['purchase_price'] = isset($value['purchase_price']) ? round($value['purchase_price'], 2) : '';
            if (!empty($value['goods_thumb_img'])) {
                $value['goods_thumb_img'] = "<img width='48px' height='48px' data-goods-img='{$value['goods_img']}' src='{$value['goods_thumb_img']}' />";
            } else {
                $value['goods_thumb_img'] = "";
            }

            //商品状态
            $arr_state = $this->state;
            $value['state'] = isset($arr_state[$value['state']]['1']) ? $arr_state[$value['state']]['1'] : '';
            //商品属性
            $arr_prop = $this->prop;
            $value['goods_prop'] = isset($arr_prop[$value['goods_prop']]['1']) ? $arr_prop[$value['goods_prop']]['1'] : '';
            //成本价管控
            if ($status_cost_price['status'] != 1) {
                $value['cost_price'] = '****';
            }
            //进货价管控
            if ($status_pur_price['status'] != 1) {
                $value['purchase_price'] = '****';
            }
            //获取扩展属性
            $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
            $property_power = $ret_cfg['property_power'];
            if ($property_power) {
                $goods_property = $this->get_export_property($value['goods_code']);
                $value = $goods_property != -1 && is_array($goods_property) ? array_merge($value, $goods_property) : $value;
            }
            //供应商
            $value['supplier_name'] = isset($supplier[$value['supplier_code']]) ? $supplier[$value['supplier_code']] : '';
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_goods_name($goods_code){
        if(!empty($goods_code)){
            $sql='select goods_name from base_goods where goods_code=:goods_code';
            $ret=$this->db->get_row($sql,array(':goods_code'=>$goods_code));
            return $ret['goods_name'];
        }
    }

    /*     * *
     * 导出获取扩展属性
     */

    function get_export_property($goods_code) {
        $proprety = load_model('prm/GoodsPropertyModel')->get_property_val('property_val');
        $select = '';
        if (empty($proprety)) {
            return -1;
        }
        foreach ($proprety as $val) {
            $select .= $val['property_val'] . ",";
        }
        $select = substr($select, 0, -1);
        $sql = "SELECT {$select} FROM base_property WHERE property_val_code=:property_val_code AND property_type='goods'";
        $result1 = $this->db->get_row($sql, array(':property_val_code' => $goods_code));
        $result = array();
        foreach ($result1 as $k => $v) {
            $result[$k] = "\t" . $v;
        }
        return $result;
    }

    function get_by_page_select($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl
		LEFT JOIN goods_sku r2 on rl.goods_code = r2.goods_code

		WHERE 1";

        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }


        $select = 'rl.goods_code,rl.goods_id,rl.goods_name,r2.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;

        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['op_gift_strategy_detail_id'] = $filter['op_gift_strategy_detail_id'];
            $data['data'][$key]['op_gift_strategy_goods_id'] = $filter['op_gift_strategy_goods_id'];
        }
        $ret_data = $data;
        //print_r($data);
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * @todo 商品发布新增宝贝
     */
    function get_issue_goods($filter) {
        $sql_main = "FROM {$this->table} bg WHERE 1";
        $sql_values = array();
        //名称或代码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (bg.goods_code LIKE :code_name or bg.goods_name LIKE :code_name)";
            $sql_values[':code_name'] = '%' . $filter['code_name'] . '%';
        }
        if (isset($filter['update_time_start']) && $filter['update_time_start'] != '') {
            $sql_main .= " AND bg.lastchanged>=:start_time";
            $sql_values[':start_time'] = $filter['update_time_start'];
        }
        if (isset($filter['update_time_end']) && $filter['update_time_end'] != '') {
            $sql_main .= " AND bg.lastchanged<=:start_time";
            $sql_values[':start_time'] = $filter['update_time_end'];
        }
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_main .= ' AND bg.goods_code NOT IN(SELECT goods_code FROM api_tb_goods_issue WHERE shop_code=:shop_code)';
            $sql_values[':shop_code'] = $filter['shop_code'];
        }

        $select = 'bg.goods_id,bg.goods_code,bg.goods_name,bg.sell_price AS price';
        $sql_main .= " ORDER BY bg.lastchanged DESC";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    //订单改款选择商品
    function order_select($goods_code, $sku) {
        $sql_main = "select rl.goods_code,rl.goods_name,r2.sku,r2.barcode,r2.barcode,r2.spec1_code,r2.spec2_code,r2.spec1_name,r2.spec2_name FROM {$this->table} rl
			LEFT JOIN goods_sku r2 on rl.goods_code = r2.goods_code
			WHERE rl.goods_code = :goods_code ";
        $sql_values[':goods_code'] = $goods_code;
        $rs = $this->db->get_all($sql_main, $sql_values);
        //print_r($rs);
        foreach ($rs as $key => $value) {
            if ($rs[$key]['sku'] == $sku) {
                unset($rs[$key]);
            }
        }
        return $rs;
    }

    function get_sku_list($sku) {
        $sql_main = "select rl.goods_code,rl.goods_name,rl.sell_price,rl.purchase_price,rl.trade_price,r2.sku,r2.barcode,r2.barcode,r2.spec1_code,r2.spec1_name,r2.spec2_code,r2.spec2_name FROM {$this->table} rl
		LEFT JOIN goods_sku r2 on rl.goods_code = r2.goods_code
		WHERE r2.sku = :sku ";
        $sql_values[':sku'] = $sku;
        $rs = $this->db->get_row($sql_main, $sql_values);

        return $rs;
    }

    function get_by_id($id) {
        return $this->get_row(array('goods_id' => $id));
    }

    /**
     * 通过外部编码获取
     * @param $code
     * @return array
     */
    function get_by_outer_code($code) {

        return $this->get_row(array('goods_outer_code' => $code));
    }

    /**
     * 通过编码获取
     * @param $code
     */
    function get_by_goods_code($code) {
        return $this->get_row(array('goods_code' => $code));
    }

    //商品规格2
    function get_goods_spec2($goods_code) {
        $sql = "select distinct goods_code,spec2_code FROM goods_sku where goods_code ='{$goods_code}'";
        $rs = $this->db->get_all($sql);
        if (empty($rs)) {
            $sql = "select  goods_code,spec2_code FROM goods_spec2 where goods_code ='{$goods_code}'";
            $rs = $this->db->get_all($sql);
        }


        return $rs;
    }

    //商品规格1
    function get_goods_spec1($goods_code) {
        $sql = "select distinct goods_code,spec1_code FROM goods_sku where goods_code ='{$goods_code}'";
        $rs = $this->db->get_all($sql);
        if (empty($rs)) {
            $sql = "select  goods_code,spec1_code FROM goods_spec1 where goods_code ='{$goods_code}'";
            $rs = $this->db->get_all($sql);
        }


        return $rs;
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*", $table) {

        $sql = "select {$select} from {$table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    /**
     * 添加新纪录
     */
    function insert($brand) {
        $status = $this->valid($brand);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($brand['goods_code']);

        if (!empty($ret['data']))
            return $this->format_ret(GOODS_ERROR_UNIQUE_CODE);

        $ret = parent::insert($brand);
        $this->update_pro($brand['goods_code']);
        return $ret;
    }

    /**
     * 删除记录
     */
    function delete($goods_id) {
        $ret = parent::delete(array('goods_id' => $goods_id));
        return $ret;
    }

    /**
     * 修改纪录
     */
    function update($goods, $goods_id) {
        $status = $this->valid($goods, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('goods_id' => $goods_id));
        if ($goods['goods_code'] != $ret['data']['goods_code']) {
            $ret1 = $this->is_exists($goods['goods_code'], 'goods_code');
            if (!empty($ret1['data']))
                return $this->format_ret(GOODS_ERROR_UNIQUE_CODE);
        }
        $log_info = '';
        $log_info .= $this->get_log_data((float) $ret['data']['trade_price'], (float) $goods['trade_price'], '商品批发价');
        $log_info .= $this->get_log_data((float) $ret['data']['purchase_price'], (float) $goods['purchase_price'], '商品进货价');
        $log_info .= $this->get_log_data((float) $ret['data']['cost_price'], (float) $goods['cost_price'], '商品成本价');
        $log_info .= $this->get_log_data((float) $ret['data']['sell_price'], (float) $goods['sell_price'], '商品吊牌价');
        if (!empty($log_info)) {
            $data = array(
                'goods_id' => $goods_id,
                'operation_note' => $log_info,
                'operation_name' => '修改商品'
            );
            //添加日志
            $ret = $this->insert_goods_log($data);
            if ($ret['status'] < 0) {
                return $this->format_ret(-1, '', '保存日志出错');
            }
        }

        $ret = parent::update($goods, array('goods_id' => $goods_id));
        $this->update_pro($goods['goods_code']);
        return $ret;
    }

    function update_pro($goods_code) {
        $sql1 = " update base_goods,base_brand
set base_goods.brand_name = base_brand.brand_name
where base_goods.brand_code = base_brand.brand_code AND base_goods.goods_code =:goods_code ";
        $sql2 = "
update base_goods,base_category
set base_goods.category_name = base_category.category_name
where base_goods.category_code = base_category.category_code AND base_goods.goods_code =:goods_code";
        $sql3 = "
update base_goods,base_brand
set base_goods.brand_name = base_brand.brand_name
where base_goods.brand_code = base_brand.brand_code AND base_goods.goods_code =:goods_code";
        $sql4 = "update base_goods ,base_season set base_goods.season_name= base_season.season_name
where  base_goods.season_code= base_season.season_code AND base_goods.goods_code =:goods_code";

        $sql5 = "  update base_goods,base_year
set base_goods.year_name = base_year.year_name
where base_goods.year_code = base_year.year_code AND base_goods.goods_code =:goods_code";
        $sql_value[':goods_code'] = $goods_code;

        $this->query($sql1, $sql_value);
        $this->query($sql2, $sql_value);
        $this->query($sql3, $sql_value);
        $this->query($sql4, $sql_value);
        $this->query($sql5, $sql_value);
    }

    /**
     * 服务器端验证
     */
    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['goods_code']) || !valid_input($data['goods_code'], 'required')))
            return GOODS_ERROR_CODE;
        if (!isset($data['goods_name']) || !valid_input($data['goods_name'], 'required'))
            return GOODS_ERROR_NAME;

        return 1;
    }

    function is_exists($value, $field_name = 'goods_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent :: update(array('status' => $active), array('goods_id' => $id));
        return $ret;
    }

    /**
     * 检查规格1是否被用过
     * @param $goods_code
     * @param $spec1_code
     */
    function check_spec1_exist($goods_code, $spec1_code) {
        $sql = "select sku from  goods_sku where goods_code=:goods_code AND spec1_code=:spec1_code ";
        $sql_values = array(':goods_code' => $goods_code, ':spec1_code' => $spec1_code);
        $data = $this->db->get_all($sql, $sql_values);
        $sku_arr = array();
        foreach ($data as $val) {
            $sku_arr[] = $val['sku'];
        }
        return $this->check_sku_is_user($sku_arr);
    }

    private function check_sku_is_user($sku_arr) {
        $sku_str = "'" . implode("','", $sku_arr) . "'";
        $order_type_arr = array(
            'b2b_lof_datail',
            'oms_sell_record_lof',
            'pur_order_record_detail',
            'pur_planned_record_detail',
            'pur_purchaser_record_detail',
            'stm_stock_adjust_record_detail',
            'oms_sell_record_detail',
            'goods_inv',
        );
        $check = false;
        foreach ($order_type_arr as $order_tb) {
            $sql = "select count(1) from {$order_tb} where sku in($sku_str)  ";
            $num = $this->db->get_value($sql);
            if ($num > 0) {
                $check = TRUE;
                break;
            }
        }

        return $check;
    }

    /**
     * 检查规格2是否被用过
     * @param $goods_code
     * @param $spec1_code
     */
    function check_spec2_exist($goods_code, $spec2_code) {
        $sql = "select sku from  goods_sku where goods_code=:goods_code AND spec2_code=:spec2_code ";
        $sql_values = array(':goods_code' => $goods_code, ':spec2_code' => $spec2_code);
        $data = $this->db->get_all($sql, $sql_values);
        $sku_arr = array();
        foreach ($data as $val) {
            $sku_arr[] = $val['sku'];
        }
        return $this->check_sku_is_user($sku_arr);
    }

    /**
     * 检查商品条码是否被用过
     * @param $barcode
     * $goods_code
     * $spec
     */
    function barcode_exist($barcode, $goods_code, $spec) {
        $arr = explode('_', $spec);
        $spec1_code = $arr[0];
        $spec2_code = $arr[1];
        $sql = "select barcode_id  from goods_barcode where (goods_code <> :goods_code or spec1_code <> :spec1_code or spec2_code <> :spec2_code) and  barcode = :barcode ";
        $arr = array(':barcode' => $barcode, ':goods_code' => $goods_code, ':spec1_code' => $spec1_code, ':spec2_code' => $spec2_code);
        $data = $this->db->get_all($sql, $arr);
        if ($data) {
            return $this->format_ret(1, '', '输入商品条形码重复，请修改或重新输入');
        }
        $sql = "select barcode_id  from goods_barcode where gb_code = :barcode limit 1 ";
        $arr = array(':barcode' => $barcode);
        $data = $this->db->get_all($sql, $arr);
        if ($data) {
            return $this->format_ret(1, '', '输入商品条码与国标码冲突，请修改或重新输入');
        }
    }

    function gb_code_exist($gb_code, $goods_code, $spec) {
        $arr = explode('_', $spec);
        $spec1_code = $arr[0];
        $spec2_code = $arr[1];
        $sql = "select barcode_id  from goods_barcode where (goods_code <> :goods_code or spec1_code <> :spec1_code or spec2_code <> :spec2_code) and  gb_code = :gb_code limit 1 ";
        $arr = array(':gb_code' => $gb_code, ':goods_code' => $goods_code, ':spec1_code' => $spec1_code, ':spec2_code' => $spec2_code);
        $data = $this->db->get_all($sql, $arr);
        if ($data) {
            return $this->format_ret(1, '', '输入国标码已存在，请修改或重新输入');
        }
        $sql = "select barcode_id  from goods_barcode where barcode = :gb_code limit 1 ";
        $arr = array(':gb_code' => $gb_code);
        $data = $this->db->get_all($sql, $arr);
        if ($data) {
            return $this->format_ret(1, '', '输入国标码跟商品条码冲突，请修改或重新输入');
        }
        $sql = "select goods_combo_barcode_id  from goods_combo_barcode where barcode = :gb_code limit 1 ";
        $arr = array(':gb_code' => $gb_code);
        $data = $this->db->get_all($sql, $arr);
        if ($data) {
            return $this->format_ret(1, '', '输入国标码跟套餐条码冲突，请修改或重新输入');
        }
    }

    /**
     * 检查批次号是否存在
     * @param $sku
     */
    function lof_exist($lof_no) {
        $sql = "select lof_no,production_date  from goods_lof where  lof_no = :lof_no limit 1 ";
        $arr = array(':lof_no' => $lof_no);
        $data = $this->db->get_all($sql, $arr);
        if ($data) {
            $ret['status'] = '1';
            $ret['data'] = $data;
            $ret['message'] = '';
            return $ret;
        } else {
            $ret['status'] = '0';
            $ret['data'] = 'false';
            $ret['message'] = '';
            return $ret;
        }
    }

    function read_csv_recode_sku($file, &$sku_arr, &$data, $is_lof) {
        $file = fopen($file, "r");
        if ($is_lof == 0) {
            $row_type = array('0' => 'sku', '1' => 'num', '2' => 'price');
        } else {
            $row_type = array('0' => 'sku', '1' => 'num', '2' => 'price', '3' => 'lof_no');
        }
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $row = $this->tran_csv($row, $row_type);
                if (!empty($row['sku'])) {
                    $sku_arr[] = $row['sku'];
                    if ($is_lof != 1) {
                        $data[$row['sku']] = preg_replace(' # # ', '', $row);
                    } else {
                        $sku_lof = preg_replace(' # # ', '', $row['sku']) . '_' . preg_replace(' # # ', '', $row['lof_no']);
                        $data[$sku_lof] = preg_replace(' # # ', '', $row);
                    }
                }
            }
            $i++;
        }
        fclose($file);
    }

    private function tran_csv(&$row, $row_type) {
        $new_row = array();
        if (!empty($row)) {
            foreach ($row as $key => $val) {
//                $val = iconv('gbk', 'utf-8', $val); 中文转码后变false
//                $val = mb_convert_encoding($val,'utf-8','gbk'); 中文转码后变乱码
                $val = str_replace('"', '', $val);
                $new_key = isset($row_type[$key]) ? $row_type[$key] : $key;
                $new_row[$new_key] = $val;
            }
        }
        return $new_row;
    }

    /**
     *
     * 方法名                               api_goods_add
     *
     * 功能描述                           客户添加产品数据接口
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-11
     * @param       array $param
     *              array(
     *                  必填: 'goods_code', 'goods_name', 'diy', 'category_code', 'brand_code', 'goods_prop', 'state',
     *                  选填: 'goods_short_name', 'goods_produce_name', 'season_code', 'year_code', 'weight', 'goods_days', 'goods_desc', 'status', 'is_add_person', 'is_add_time'
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_goods_add($param) {
        //特殊字符转义
        $param['goods_code'] = htmlentities($param['goods_code']);
        $param['goods_name'] = htmlentities($param['goods_name']);
        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
            's' => array('goods_code', 'goods_name', 'category_code', 'brand_code'),
            'i' => array('diy', 'goods_prop', 'state')
        );
        //可选字段
        $key_option = array(
            's' => array('goods_short_name', 'goods_produce_name', 'season_code', 'year_code', 'weight', 'goods_desc', 'is_add_person', 'is_add_time'),
            'i' => array('validity_date', 'goods_days', 'status')
        );


        $brand_required = array();
        $brand_option = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $brand_required, TRUE);
        if (TRUE == $ret_required['status']) {
            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($param, $key_option, $brand_option);
            $brand = array_merge($brand_required, $brand_option);

            $cat = $brand_new = $season = $year = array();
            $cat['category_code'] = $brand['category_code'];
            $cat['category_name'] = $param['category_name'];
            $brand_new['brand_code'] = $brand['brand_code'];
            $brand_new['brand_name'] = $param['brand_name'];
            $season['season_code'] = $param['season_code'];
            $season['season_name'] = $param['season_name'];
            $year['year_code'] = $param['year_code'];
            $year['year_name'] = $param['year_name'];

            unset($brand_required);
            unset($brand_option);
            unset($param);

            //检查客户传入的分类是否存在，若不存在则新增
            $cat_info = $this->get_by_cat_code($cat['category_code']);
            if (!$cat_info) {
                if ($cat['category_code']) {
                    $ret = $this->insert_multi_duplicate('base_category', array($cat), 'category_name = VALUES(category_name)');
                }
            }
            //检查客户传入的季节是否存在，若不存在则新增
            $season_info = $this->get_by_season_code($season['season_code']);
            if (!$season_info) {
                if ($season['season_code']) {
                    $ret = $this->insert_multi_duplicate('base_season', array($season), 'season_name = VALUES(season_name)');
                }
            }
            //检查客户传入的年份是否存在，若不存在则新增
            $year_info = $this->get_by_year_code($year['year_code']);
            if (!$year_info) {
                $ret = $this->insert_multi_duplicate('base_year', array($year), 'year_name = VALUES(year_name)');
            }
            //检查客户传入的品牌是否存在，若不存在则新增
            $brand_info = $this->get_by_brand_code($brand_new['brand_code']);
            if (!$brand_info) {
                $ret = $this->insert_multi_duplicate('base_brand', array($brand_new), 'brand_name = VALUES(brand_name)');
            }

            $brand['period_validity'] = isset($brand['validity_date']) ? $brand['validity_date'] : 0;

            //调用本module新增方法
            $ret = $this->insert($brand);
            if ('GOODS_ERROR_UNIQUE_CODE' == $ret['status']) {
                $ret['status'] = '-10002';
            }

            //扩展属性
            if ($ret['status'] > 0) {
                $this->expand_property_save($brand['goods_code'], $param);
            }

            return $ret;
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
    }

    /**
     *
     * 方法名                               api_goods_update
     *
     * 功能描述                           客户更新产品数据接口
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-11
     * @param       array $param
     *              array(
     *                  必填: 'goods_code',
     *                  选填: 'goods_name', 'goods_short_name', 'goods_produce_name', 'diy', 'category_code', 'brand_code', 'season_code', 'year_code', 'goods_prop', 'state', 'weight', 'goods_days', 'goods_desc', 'status'
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_goods_update($param) {

        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
            's' => array('goods_code')
        );
        //可选字段
        $key_option = array(
            's' => array('goods_name', 'goods_short_name', 'goods_produce_name', 'category_code', 'brand_code', 'season_code', 'year_code', 'weight', 'goods_desc'),
            'i' => array('diy', 'goods_prop', 'state', 'validity_date', 'goods_days', 'status')
        );
        $match_field = array(
            array('code' => 'category_code', 'name' => 'category_name', 'explain' => '分类代码', 'table' => 'base_category'),
            array('code' => 'brand_code', 'name' => 'brand_name', 'explain' => '品牌代码', 'table' => 'base_brand'),
            array('code' => 'season_code', 'name' => 'season_name', 'explain' => '季节代码', 'table' => 'base_season'),
            array('code' => 'year_code', 'name' => 'year_name', 'explain' => '年份代码', 'table' => 'base_year'),
        );
        $msg_data = array();
        try {
            $brand_required = array();
            $brand_option = array();
            //验证必选字段是否为空并提取必选字段数据
            $ret_required = valid_assign_array($param, $key_required, $brand_required, TRUE);
            if (TRUE !== $ret_required['status']) {
                throw new Exception("API_RETURN_MESSAGE_10001", '', '-10001');
            }
            $goods_code = $param['goods_code'];
            unset($brand_required['goods_code']);
            //根据产品code查询数据是否存在该数据
            $good = $this->get_by_goods_code($goods_code);
            if ('op_no_data' == $good['status']) {
                $msg_data = array('goods_code' => $param['goods_code']);
                throw new Exception('API_RETURN_MESSAGE_10002', '-10002');
            }
            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($param, $key_option, $brand_option);
            $brand = array_merge($brand_required, $brand_option);
            unset($brand_required);
            unset($brand_option);
            unset($param);
            if (isset($brand['validity_date'])) {
                $brand['period_validity'] = $brand['validity_date'];
            }
            foreach ($match_field as $val) {
                $code = $val['code'];
                if (!isset($brand[$code])) {
                    continue;
                }
                $wh = array($code => $brand[$code]);
                $name = oms_tb_val($val['table'], $val['name'], $wh);
                if ($name == '') {
                    $msg_data = $wh;
                    throw new Exception($val['explain'] . '不存在', '-10002');
                } else {
                    $brand[$val['name']] = $name;
                }
            }
            //根据产品code来更新产品数据
            $ret = $this->update_by_goods_code($brand, $goods_code);

            //扩展属性
            $this->expand_property_save($goods_code, $param);
            return $ret;
        } catch (Exception $e) {
            return $this->format_ret($e->getCode(), $msg_data, $e->getMessage());
        }
    }

    private function expand_property_save($goods_code, $param) {

        $expand_property_option = array(
            's' => array('property_val1', 'property_val2', 'property_val3', 'property_val4', 'property_val5', 'property_val6', 'property_val7', 'property_val8', 'property_val9', 'property_val10'),
        );
        $property_arr = array();
        $ret = array();
        $ret_option = valid_assign_array($param, $expand_property_option, $property_arr);
        if (!empty($property_arr)) {
            $property_arr['property_type'] = 'goods';
            $property_arr['property_val_code'] = $goods_code;
            $ret = $this->insert_multi_duplicate('base_property', array($property_arr));
        }
        return $this->format_ret(1);
    }

    /**
     *
     * 方法名                               update_by_goods_code
     *
     * 功能描述                          根据产品编码更新产品数据
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-11
     * @param       array $goods
     *              string $goods_code
     * @return      array
     */
    public function update_by_goods_code($goods, $goods_code) {
        $ret = parent::update($goods, array('goods_code' => $goods_code));
        return $ret;
    }

    /**
     * API-获取商品列表
     * @author BaiSon PHP R&D
     * @date 2015-08-08
     * @modify 2017-07-03 wmh 增加国标码出参
     * @param array $filter 接口参数
     * @return array 结果集
     */
    public function api_goods_list($filter) {
        $sql_values = array();
        $sql_join .= " LEFT JOIN base_property r3 ON rl.goods_code=r3.property_val_code AND r3.property_type='goods' ";
        $sql_main = "FROM {$this->table} rl {$sql_join} WHERE 1  ";
        //分类
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $category_code_arr = explode(',', $filter['category_code']);
            if (!empty($category_code_arr)) {
                $sql_main .= " AND (";
                foreach ($category_code_arr as $key => $value) {
                    $param_category = 'param_category' . $key;
                    if ($key == 0) {
                        $sql_main .= " category_code = :{$param_category} ";
                    } else {
                        $sql_main .= " or category_code = :{$param_category} ";
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
                        $sql_main .= " brand_code = :{$param_brand} ";
                    } else {
                        $sql_main .= " or brand_code = :{$param_brand} ";
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
                        $sql_main .= " year_code = :{$param_year} ";
                    } else {
                        $sql_main .= " or year_code = :{$param_year} ";
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
                        $sql_main .= " season_code = :{$param_season} ";
                    } else {
                        $sql_main .= " or season_code = :{$param_season} ";
                    }

                    $sql_values[':' . $param_season] = $value;
                }
                $sql_main .= ")";
            }
        }
        //状态
        if (isset($filter['state']) && $filter['state'] != '') {
            $state_arr = explode(',', $filter['state']);
            if (!empty($state_arr)) {
                $sql_main .= " AND (";
                foreach ($state_arr as $key => $value) {
                    $param_state = 'param_state' . $key;
                    if ($key == 0) {
                        $sql_main .= " state = :{$param_state} ";
                    } else {
                        $sql_main .= " or state = :{$param_state} ";
                    }

                    $sql_values[':' . $param_state] = $value;
                }
                $sql_main .= ")";
            }
        }
        //商品代码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND rl.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql_main .= " AND rl.goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }

        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $sql_main .= " AND rl.goods_code  in (select goods_code from goods_sku where barcode=:barcode)";
            $sql_values[':barcode'] = $filter['barcode'];
        }

        //是否组装商品
        if (isset($filter['diy']) && $filter['diy'] !== '') {
            $sql_main .= " AND rl.diy = :diy ";
            $sql_values[':diy'] = $filter['diy'];
        }

        //api模式下按数据库更新时间戳逆序排序@2015-5-15 +++++++++++++++++++++++

        if (isset($filter['lastchanged_start']) && $filter['lastchanged_start'] !== '') {
            $sql_main .= " AND rl.lastchanged >= :lastchanged_start ";
            $sql_values[':lastchanged_start'] = $filter['lastchanged_start'];
        }
        if (isset($filter['lastchanged_end']) && $filter['lastchanged_end'] !== '') {
            $sql_main .= " AND rl.lastchanged < :lastchanged_end ";
            $sql_values[':lastchanged_end'] = $filter['lastchanged_end'];
        }



        $sql_main .= " order by rl.lastchanged desc ";

        $select = 'rl.goods_code,rl.goods_name,rl.goods_short_name,rl.category_code,rl.category_name'
                . ',rl.brand_code,rl.brand_name,rl.season_code,rl.season_name,rl.goods_img,rl.goods_produce_name'
                . ',rl.weight,rl.diy,rl.status,rl.goods_desc,rl.year_code,rl.year_name'
                . ',rl.lastchanged,rl.goods_prop,rl.goods_days,rl.state';

        $select .= ',rl.sell_price,rl.cost_price,rl.trade_price,rl.purchase_price';
        //扩展属性
        for ($i = 1; $i < 11; $i++) {
            $select .= ',r3.property_val' . $i;
        }
        if (isset($filter['page_size'])) {
            if ($filter['page_size'] > 500) {
                $filter['page_size'] = 500;
            }
        } else {
            $filter['page_size'] = 20;
        }

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            $val['sell_price'] = empty($val['sell_price']) ? 0 : $val['sell_price'];
            $val['cost_price'] = empty($val['cost_price']) ? 0 : $val['cost_price'];
            $val['trade_price'] = empty($val['trade_price']) ? 0 : $val['trade_price'];
            $val['purchase_price'] = empty($val['purchase_price']) ? 0 : $val['purchase_price'];
            $val['barcode_list'] = $this->get_barcode_by_goods_code($val['goods_code']);
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 方法名      get_barcode_by_goods_code
     * 功能描述    通过商品获取条码信息(包含子条码)
     * @author      BaiSon PHP R&D
     * @date        2015-08-08
     * @param       array $param
     * @return      array
     */
    private function get_barcode_by_goods_code($goods_code) {
        $sql = "SELECT goods_code,spec1_code,spec1_name,spec2_code,spec2_name,sku,barcode,gb_code FROM goods_sku WHERE goods_code=:goods_code";
        $sql_vaules = array(':goods_code' => $goods_code);
        $goods_sku = $this->db->get_all($sql, $sql_vaules);
        $sql_child = "SELECT barcode barcode_child FROM goods_barcode_child WHERE goods_code=:goods_code and spec1_code=:spec1_code and spec2_code=:spec2_code and sku=:sku";
        foreach ($goods_sku as $key => $val) {
            $sql_values = array(':goods_code' => $val['goods_code'], ':spec1_code' => $val['spec1_code'], ':spec2_code' => $val['spec2_code'], ':sku' => $val['sku']);
            $barcode_child = $this->db->get_all($sql_child, $sql_values);
            $goods_sku[$key]['barcode_child_list'] = array();
            foreach ($barcode_child as $k => $v) {
                $goods_sku[$key]['barcode_child_list'][$k] = $v['barcode_child'];
            }
        }
        return $goods_sku;
    }

    function update_property($data, $where) {
        return parent::update($data, $where);
    }

    /**
     * @param $get_type cost_price | sell_price | buy_price | trade_price | purchase_price,多个price以逗号分开
     * @param $sku_map = array('sku'=>'goods_code') 指定当前SKU关联的货号，这样如果SKU级没价格，可以取商品级的
     * @param $pm_goods_code 如果不取sku的价格那么指定 goods_code 就行
     */
    function get_goods_price($get_type, $sku_map, $pm_goods_code = null) {
        $fld = empty($get_type) ? $get_type : ',' . $get_type;
        $get_type_arr = explode(',', $get_type);

        if (!empty($pm_goods_code)) {
            $goods_code_arr = explode(',', $pm_goods_code);
        } else {
            $goods_code_arr = array_unique($sku_map);
        }
        if (empty($goods_code_arr)) {
            return array();
        }
        $goods_code_list = "'" . join("','", $goods_code_arr) . "'";
        $wh = "goods_code in ({$goods_code_list})";

        $sql = "select goods_code{$fld} from base_goods where {$wh}";
//        echo $sql;die;
        $db_arr = ctx()->db->get_all($sql);
        $goods_price_arr = array();
        $sku_price_arr = array();
        foreach ($db_arr as $sub_arr) {
            if (empty($sub_arr['sku'])) {
                $goods_price_arr[$sub_arr['goods_code']] = $sub_arr;
            } else {
                $sku_price_arr[$sub_arr['sku']] = $sub_arr;
            }
        }
        $result = array();
        $c = count($get_type_arr);
        if (!empty($sku_map)) {
            foreach ($sku_map as $sku => $goods_code) {
                $find_sku_price_row = !empty($sku_price_arr[$sku]) ? $sku_price_arr[$sku] : array();
                $find_goods_price_row = !empty($goods_price_arr[$goods_code]) ? $goods_price_arr[$goods_code] : array();
                foreach ($get_type_arr as $_type) {
                    if ($c == 1) {
                        $result[$sku] = isset($find_sku_price_row[$_type]) ? (float) $find_sku_price_row[$_type] : 0;
                        $find_goods_price_row[$_type] = isset($find_goods_price_row[$_type]) ? $find_goods_price_row[$_type] : 0;
                        $result[$sku] = empty($result[$sku]) ? $find_goods_price_row[$_type] : $result[$sku];
                    } else {
                        $result[$sku][$_type] = isset($find_sku_price_row[$_type]) ? (float) $find_sku_price_row[$_type] : 0;

                        $result[$sku][$_type] = empty($result[$sku][$_type]) ? $find_goods_price_row[$_type] : $result[$sku][$_type];
                    }
                }
            }
        } else {
            foreach ($goods_code_arr as $goods_code) {
                $find_goods_price_row = !empty($goods_price_arr[$goods_code]) ? $goods_price_arr[$goods_code] : array();
                foreach ($get_type_arr as $_type) {
                    if ($c == 1) {
                        $result[$goods_code] = isset($find_goods_price_row[$_type]) ? (float) $find_goods_price_row[$_type] : 0;
                    } else {
                        $result[$goods_code][$_type] = isset($find_goods_price_row[$_type]) ? (float) $find_goods_price_row[$_type] : 0;
                    }
                }
            }
        }

        return $this->format_ret(1, $result);
    }

    /**
     * 商品删除
     * 仅针对未启用的且未产生销售记录和库存记录的商品进行删除操作
     * @param string $goods_code 商品编码
     * @return array
     */
    function goods_delete($goods_code) {
        //特殊字符转义
        $goods_code = html_entity_decode($goods_code);
        $msg = '';
        $sql_inv = "SELECT goods_code FROM goods_inv WHERE goods_code=:goods_code";
        $sql_value = array(':goods_code' => $goods_code);
        $ret_inv = $this->db->get_row($sql_inv, $sql_value);
        if (!empty($ret_inv)) {
            $msg = '商品存在库存记录,';
        }
        $sql_record = "SELECT goods_code FROM oms_sell_record_detail WHERE goods_code=:goods_code";
        $ret_record = $this->db->get_row($sql_record, $sql_value);
        if (!empty($ret_record)) {
            $msg .= '存在销售记录,';
        }
        if (!empty($msg)) {
            $msg .= '不能删除';
            return $this->format_ret(-1, '', $msg);
        }
        $sql = "select diy from {$this->table} where goods_code=:goods_code";
        $diy = $this->db->get_row($sql, array(":goods_code" => $goods_code));
        $this->begin_trans();
        if ($diy['diy'] == 1) {
            parent::delete_exp('goods_diy', array('p_goods_code' => $goods_code));
        }
        $ret = parent::delete(array('goods_code' => $goods_code));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $ret2 = parent::delete_exp('goods_sku', array('goods_code' => $goods_code));
        if ($ret2 != TRUE) {
            $this->rollback();
            return $ret;
        }
        $ret2 = parent::delete_exp('goods_barcode', array('goods_code' => $goods_code));
        if ($ret2 != TRUE) {
            $this->rollback();
            return $ret;
        }
        //删除绑定的库位
        $ret2 = parent::delete_exp('goods_shelf', array('goods_code' => $goods_code));
        if ($ret2 != TRUE) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        return $ret;
    }

    //获取当前登录的分销商信息,return name,code
    function get_custom() {
        $user_code = CTX()->get_session('user_code');
        $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
        return $custom;
    }

    //添加
    function add_one_shopping_cart($params) {
        //获取当前登录的分销商code
        $custom = $this->get_custom();
        $custom_code = $custom['custom_code'];
        $params['custom_code'] = $custom_code;

        //判断是否有已存在的数据
        $num = $this->fx_is_exists($custom_code, $params['sku'], $params['purchase_num'], $params['lof_no']);
        if ($num != '') {
            $params['purchase_num'] = $num;
        }

        //根据sku查询barcode和商品编码
        $sql = "SELECT goods_code,barcode FROM goods_barcode WHERE sku = '{$params['sku']}';";
        $goods_info = $this->db->get_row($sql);
        $params['barcode'] = $goods_info['barcode'];
        $params['goods_code'] = $goods_info['goods_code'];

        //获取商品名称和缩略图
        $sql = "SELECT goods_thumb_img,goods_name FROM base_goods WHERE goods_code = '{$goods_info['goods_code']}';";
        $goods_info = $this->db->get_row($sql);
        $params['goods_thumb_img'] = $goods_info['goods_thumb_img'];
        $params['goods_name'] = $goods_info['goods_name'];

        //获取库存
//        $sql = "SELECT stock_num,lock_num FROM goods_inv_lof WHERE sku = '{$params['sku']}';";
//        $goods_info = $this->db->get_row($sql);
//        $params['effec_num'] = $goods_info['stock_num'] - $goods_info['lock_num'];
        //获取仓库
        $params['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $params['store_code']));

        //批次价格
        $sql = "SELECT lof_price FROM goods_lof WHERE sku = :sku AND lof_no = :lof_no;";
        $goods_info = $this->db->get_row($sql, array(':sku' => $params['sku'], ':lof_no' => $params['lof_no']));
        $params['lof_price'] = $goods_info['lof_price'];

        //添加待采购商品表
        $ret = M('oms_return_package_detail')->insert_exp('fx_shopping_cart', $params, true);

        return $ret;
    }

    //判断是否以选择此商品，是就叠加商品数量,然后删除前一条信息
    function fx_is_exists($custom_code, $sku, $purchase_num, $lof_no) {
        $sql = "SELECT * FROM fx_shopping_cart WHERE custom_code = '{$custom_code}' AND sku = '{$sku}' AND lof_no = '{$lof_no}';";
        $data = $this->db->get_row($sql);
        if (!empty($data)) {
            $purchase_num += $data['purchase_num'];
            parent::delete_exp('fx_shopping_cart', array('sku' => $sku, 'custom_code' => $custom_code, 'lof_no' => $lof_no));
            return $purchase_num;
        }
        return;
    }

    //待采购商品列表
    function get_by_shopping_page($filter) {
        //获取当前登录的分销商code
        $custom = $this->get_custom();
        $custom_code = $custom['custom_code'];

        $sql_values = array();
        $sql_join = "";
        $sql_main = " FROM fx_shopping_cart rl {$sql_join} WHERE custom_code = '{$custom_code}' ";

        $select = " rl.* ";
        $sql_main .= "ORDER BY shopping_id ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['img_html'] = !empty($value['goods_thumb_img']) ? "<img style = 'width:48px;heigth:48px' src = '{$value['goods_thumb_img']}'>" : '';
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //删除商品
    function do_delete_shopping($ids_str) {
        //获取当前登录的分销商code=
        $custom = $this->get_custom();
        $custom_code = $custom['custom_code'];

        //删除商品
        $sql = "DELETE from fx_shopping_cart WHERE custom_code = '{$custom_code}' AND shopping_id in ($ids_str);";
        $ret = $this->query($sql);
        return $ret;
    }

    //清空商品
    function click_clear() {
        //获取当前登录的分销商code
        $custom = $this->get_custom();
        $custom_code = $custom['custom_code'];

        //删除商品
        $ret = $this->delete_exp('fx_shopping_cart', array('custom_code' => $custom_code));
        return $ret;
    }

    //提交采购
    function submit_purchase($ids) {
        $ids_str = "'" . implode("','", $ids) . "'";

        //获取当前时间
        $record['order_time'] = date('Y-m-d H:i:s', time());
        //生成单据编号
        $record['record_code'] = load_model('fx/PurchaseRecordModel')->create_fast_bill_sn();
        //获取业务时间
        $record['record_time'] = date('Y-m-d H:i:s', time());

        //获取当前登录的分销商code
        $custom = $this->get_custom();
        $custom_code = $custom['custom_code'];
        //分销商
        $record['custom_code'] = $custom_code;
        //根据分销商查询仓库
        $sql = "SELECT store_code FROM fx_shopping_cart WHERE custom_code = '{$custom_code}'";
        $data = $this->db->get_row($sql);
        $record['store_code'] = $data['store_code'];
        //折扣
        $record['rebate'] = 1;
        $record['remark'] = '';

        $this->begin_trans();
        try {
            //添加一条经销采购主单据
            $ret = load_model('fx/PurchaseRecordModel')->insert($record);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
            //获取已选中的代采购商品信息
            $shopping_data = $this->get_by_shopping($custom_code, $ids_str);

            //根据订单编号查询id
            $sql = "select purchaser_record_id from fx_purchaser_record where record_code = '{$record['record_code']}'";
            $pid = $this->db->getOne($sql);

            //批量添加经销采购明细单据
            $detail = array();
            foreach ($shopping_data as $key => $val) {
                $detail[$key]['record_code'] = $record['record_code'];
                $detail[$key]['pid'] = $pid;
                $detail[$key]['goods_code'] = $val['goods_code'];
                $detail[$key]['sku'] = $val['sku'];
                $detail[$key]['price'] = empty($val['lof_price']) ? 0 : $val['lof_price'];
                $money = $val['lof_price'] * $val['purchase_num'];
                $detail[$key]['money'] = $money;
                $detail[$key]['finish_num'] = 0;
                $detail[$key]['num'] = $val['purchase_num'];
                $detail[$key]['goods_property'] = 1;
                $detail[$key]['cost_price'] = $val['lof_price'];
                //根据barcode查询规格1规格2
                $spec = $this->get_by_spec($val['sku']);
                $detail[$key]['spec1_code'] = $spec['spec1_code'];
                $detail[$key]['spec2_code'] = $spec['spec2_code'];
                $detail[$key]['production_date'] = oms_tb_val('goods_lof', 'production_date', array('sku' => $val['sku'], 'lof_no' => $val['lof_no']));
                $detail[$key]['lof_no'] = $val['lof_no'];
            }

            //添加经销采购单明细
            $ret = M('oms_return_package_detail')->insert_exp('fx_purchaser_record_detail', $detail, true);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }


            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $record['store_code'], 'fx_purchase', $detail);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }

            //添加完成后清空待采购表
            $ret = $this->do_delete_shopping($ids_str);
            if ($ret != TRUE) {
                $this->rollback();
                return $ret;
            }

            $this->commit();
            return $this->format_ret(1, $pid, '保存成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    //根据分销商code查询代采购商品信息
    function get_by_shopping($custom_code, $ids_str) {
        $sql = "SELECT * FROM fx_shopping_cart WHERE custom_code = '{$custom_code}' AND shopping_id in({$ids_str})";
        $data = $this->db->get_all($sql);
        return $data;
    }

    function get_by_spec($sku) {
        $sql = "SELECT spec1_code,spec2_code FROM goods_sku WHERE sku = '{$sku}'";
        $spec = $this->db->get_row($sql);
        return $spec;
    }

    //判断库存数量是否足够
    function is_store_num($filter) {
        $sql = "SELECT purchase_num FROM fx_shopping_cart WHERE sku = :sku AND custom_code = :custom_code AND lof_no = :lof_no";
        $purchase_num = $this->db->get_row($sql, array(':sku' => $filter['sku'], ':custom_code' => $filter['custom_code'], ':lof_no' => $filter['lof_no']));
        $purchase_num = $filter['purchase_num'] + $purchase_num['purchase_num'];
        if ($purchase_num > $filter['effec_num']) {
            return $this->format_ret('-1', '', '商品库存不足');
        }
        return $this->format_ret('1');
    }

    //分销商品汇总
    function fx_goods_count() {
        //获取当前登录的分销商code
        $custom = $this->get_custom();
        $custom_code = $custom['custom_code'];
        $sql = "SELECT SUM(purchase_num) AS sum_num,SUM(lof_price) sum_price FROM fx_shopping_cart WHERE custom_code = '{$custom_code}';";
        $data = $this->db->get_row($sql);
        $data['sum_num'] = empty($data['sum_num']) ? 0 : $data['sum_num'];
        $data['sum_price'] = empty($data['sum_price']) ? 0 : $data['sum_price'];
        return $data;
    }

    //修改采购数量
    function edit_purchase_num($shopping_id, $purchase_num) {
        $sql = "UPDATE fx_shopping_cart SET purchase_num = :purchase_num WHERE shopping_id = :shopping_id;";
        $ret = $this->query($sql, array(':purchase_num' => $purchase_num, ':shopping_id' => $shopping_id));
        return $ret;
    }

    function get_goods_barcode($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        //todo 强制不分页
        $filter['page_size'] = isset($filter['page_size']) ? $filter['page_size'] : 10000;
        $sql_value = '';
        $sql_join = " INNER JOIN goods_barcode r2 ON rl.goods_code = r2.goods_code INNER JOIN base_spec1 r3 ON r3.spec1_code=r2.spec1_code  INNER JOIN base_spec2 r4 ON r4.spec2_code=r2.spec2_code";
        $sql_main = " FROM {$this->table} rl {$sql_join}";


        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND (rl.goods_name LIKE :goods_name )";
            $sql_values[':goods_name'] = $filter['goods_name'] . '%';
        }
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND (r2.barcode LIKE :barcode )";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }

        $select = "rl.goods_code,rl.goods_name,r2.barcode,r2.sku,r3.spec1_name,r4.spec2_name";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_cat_code($cat_code) {
        $sql = "SELECT category_id FROM base_category WHERE category_code = :cat_code";
        return $this->db->get_value($sql, array(':cat_code' => $cat_code));
    }

    function get_by_brand_code($brand_code) {
        $sql = "SELECT brand_id FROM base_brand WHERE brand_code = :brand_code";
        return $this->db->get_value($sql, array(':brand_code' => $brand_code));
    }

    function get_by_season_code($season_code) {
        $sql = "SELECT season_id FROM base_season WHERE season_code = :season_code";
        return $this->db->get_value($sql, array(':season_code' => $season_code));
    }

    function get_by_year_code($year_code) {
        $sql = "SELECT year_id FROM base_year WHERE year_code = :year_code";
        return $this->db->get_value($sql, array(':year_code' => $year_code));
    }

    function get_by_page_log($filter) {
        $sql_value = array();
        $sql_main = " FROM base_goods_log AS rl WHERE rl.goods_id = :goods_id GROUP BY add_time DESC";
        $sql_value[':goods_id'] = $filter['goods_id'];
        $select = "rl.*";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, $select, true);
        foreach ($data['data'] as &$value) {
            /* $user_data = load_model('sys/UserModel')->get_by_code($value['user_code'],'user_name');
              $value['user_name'] = $user_data['user_name']; */
            $value['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_log_data($tab_info, $list_info, $type = '') {
        $log_info = '';
        $tab_info = empty($tab_info) ? 0 : $tab_info;
        $list_info = empty($list_info) ? 0 : $list_info;
        if (empty($tab_info) && !empty($list_info)) {
            $log_info .= '新增' . $type . '为：' . $list_info . '；';
        }
        if (!empty($tab_info) && !empty($list_info) && $tab_info != $list_info) {
            $log_info .= '修改' . $type . '：由' . $tab_info . '修改为：' . $list_info . '；';
        }
        if (!empty($tab_info) && empty($list_info)) {
            $log_info .= '删除' . $type . '；';
        }
        return $log_info;
    }

    function insert_goods_log($data) {
        //获取当前用户code和id
        $data['user_code'] = CTX()->get_session('user_code');
        $data['user_id'] = CTX()->get_session('user_id');
        $data['add_time'] = time();
        $ret = $this->insert_exp('base_goods_log', $data);
        return $ret;
    }

}
