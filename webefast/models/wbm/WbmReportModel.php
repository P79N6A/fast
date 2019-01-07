<?php

/**
 * 批发报表
 *
 * @author wq
 *
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);

class WbmReportModel extends TbModel {
    public $record_type_name = array(
        '0'=>'批发销货',
        '1'=>'批发退货',
    );
    //base_custom
    function get_report_by_page($filter) {
        //导出
        if ($filter['ctl_type'] == 'export') {
            return $this->wbm_report_search_csv($filter);
        }
        $sql_values = array();
        $sql_main = "";
        $this->get_param_by_filter($filter, $sql_values, $sql_main);
        
        $select = "rl.custom_code,rl.custom_name,sum(t.out_num) as out_num,sum(t.out_money) as out_money,sum(t.in_num) as in_num,sum(t.in_money) as in_money ";
        $sql = "SELECT user_code FROM sys_user WHERE (status = 2 OR status = 0) AND login_type = 2;";
        $user_code = $this->db->get_all_col($sql);
        if (!empty($user_code)) {
            $user_str = $this->arr_to_in_sql_value($user_code, 'user_code', $sql_values);
            $sql_main .= " AND (rl.user_code NOT IN ({$user_str}) OR rl.user_code is null) ";
        }
        //分销商权限
        $custom_code = isset($filter['custom_code']) ? $filter['custom_code'] : null;
        $sql_main .= load_model('base/CustomModel')->get_sql_purview_custom('rl.custom_code', $custom_code);
        if (isset($filter['distributor_code']) && $filter['distributor_code'] <> '') {
            $arr = explode(',', $filter['distributor_code']);
            $str = $this->arr_to_in_sql_value($arr, 'custom_code', $sql_values);
            $sql_main .= " AND rl.custom_code in ({$str}) ";
        }
        //仓库
//        if (isset($filter['store_code']) && $filter['store_code'] != '') {
//            $store_code_arr=explode(',',$filter['store_code']);
//            $store_code_str=$this->arr_to_in_sql_value($store_code_arr,'store_code',$sql_values);
//            $sql_main .= " AND t.store_code IN ({$store_code_str}) " ;
//        }
        $sql_main .= " GROUP BY rl.custom_code ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as $key => &$val) {
            $val['custom_name']=  oms_tb_val('base_custom', 'custom_name', array('custom_code'=>$val['custom_code']));
            $val['out_num'] = empty($val['out_num']) ? 0 : $val['out_num'];
            $val['out_money'] = empty($val['out_money']) ? 0 : $val['out_money'];
            $val['in_num'] = empty($val['in_num']) ? 0 : $val['in_num'];
            $val['in_money'] = empty($val['in_money']) ? 0 : $val['in_money'];
            //$val['store_name'] = empty($val['store_code']) ? '' : oms_tb_val('base_store', 'store_name', array('store_code' => $val['store_code']));
        }
        return $this->format_ret(1, $data);
    }

    
        //导出
    public function wbm_report_search_csv($filter) {
        $sql_main = "";
        $sql_values = array();
        $join_sr = " SELECT sr.distributor_code AS custom_code,srd.sku,sum(srd.num) AS out_num,sum(srd.money) AS out_money,0 AS in_num,0 AS in_money "
                . "FROM wbm_store_out_record sr INNER JOIN wbm_store_out_record_detail srd ON sr.record_code = srd.record_code INNER JOIN base_goods bg ON srd.goods_code = bg.goods_code "
                . "WHERE 1 AND  sr.is_sure=1 and sr.is_store_out=1 ";
        $join_wr = " SELECT wr.distributor_code AS custom_code,wrd.sku,0 AS out_num,0 AS out_money,sum(wrd.num) AS in_num,sum(wrd.money) AS in_money "
                . "FROM wbm_return_record wr INNER JOIN wbm_return_record_detail wrd ON wr.record_code = wrd.record_code INNER JOIN base_goods bg ON wrd.goods_code = bg.goods_code "
                . "WHERE 1 AND wr.is_sure=1 and wr.is_store_in=1 ";
        // 开始时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $join_sr .= " AND sr.record_time >= :record_time_start ";
            $join_wr .= " AND wr.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        // 开始时间
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $join_sr .= " AND sr.record_time <= :record_time_end ";
            $join_wr .= " AND wr.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }

        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
            $join_sr .= " AND sr.store_code IN ({$store_code_str})";
            $join_wr .= " AND wr.store_code IN ({$store_code_str})";
        }else{//仓库权限
            $filter_store_code=null;
            $join_sr .= load_model('base/StoreModel')->get_sql_purview_store('sr.store_code', $filter_store_code);
            $join_wr .= load_model('base/StoreModel')->get_sql_purview_store('wr.store_code', $filter_store_code);
        }
        //分销商权限
        $custom_code = isset($filter['distributor_code']) ? $filter['distributor_code'] : null;
        $join_sr .= load_model('base/CustomModel')->get_sql_purview_custom('sr.distributor_code', $custom_code);
        $join_wr .= load_model('base/CustomModel')->get_sql_purview_custom('wr.distributor_code', $custom_code);
        //品牌权限
        $join_sr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        $join_wr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        $join_sr.=" GROUP BY sr.distributor_code,srd.sku ";
        $join_wr.=" GROUP BY wr.distributor_code,wrd.sku ";

        $sql_main = "FROM base_custom r1 LEFT JOIN ( " . $join_sr . " UNION ALL " . $join_wr . ") AS t ON r1.custom_code=t.custom_code ";
        $sql_main .=" WHERE r1.custom_type = 'pt_fx' and r1.is_effective = 1";

        //仓库
//        if (isset($filter['store_code']) && $filter['store_code'] != '') {
//            $store_code_arr = explode(',', $filter['store_code']);
//            $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
//            $sql_main .= " AND t.store_code IN ({$store_code_str})";
//        }

        $select = "r1.custom_code,t.sku,sum(t.out_num) AS out_num,sum(t.out_money) AS out_money,sum(t.in_num) AS in_num,sum(t.in_money) AS in_money ";
        $sql = "SELECT user_code FROM sys_user WHERE (status = 2 OR status = 0) AND login_type = 2;";
        $user_code = $this->db->get_all_col($sql);
        if (!empty($user_code)) {
            $user_str = $this->arr_to_in_sql_value($user_code, 'user_code', $sql_values);
            $sql_main .= " AND (r1.user_code NOT IN ({$user_str}) OR r1.user_code is null) ";
        }
        if (isset($filter['distributor_code']) && $filter['distributor_code'] <> '') {
            $arr = explode(',', $filter['distributor_code']);
            $str = $this->arr_to_in_sql_value($arr, 'custom_code', $sql_values);
            $sql_main .= " AND r1.custom_code in ({$str}) ";
        }
        $sql_main .= load_model('base/CustomModel')->get_sql_purview_custom('r1.custom_code', $custom_code);
        $sql_main.= " GROUP BY r1.custom_code,t.sku ";
        //获取总数量
        $sum_all=$this->get_export_sum_num($filter);
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($ret_data['data'] as $key => &$val) {
            $val['custom_name'] = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $val['custom_code']));
            $val['out_num'] = empty($val['out_num']) ? 0 : $val['out_num'];
            $val['out_money'] = empty($val['out_money']) ? 0 : $val['out_money'];
            $val['in_num'] = empty($val['in_num']) ? 0 : $val['in_num'];
            $val['in_money'] = empty($val['in_money']) ? 0 : $val['in_money'];
            $key_a = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'goods_code', 'brand_name', 'category_name', 'season_name', 'year_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_a);
            $val = array_merge($val, $sku_info);
            $val['out_num_all'] =$sum_all[$val['custom_code']]['out_num_all'];
            $val['out_money_all'] = $sum_all[$val['custom_code']]['out_money_all'];
            $val['in_num_all'] = $sum_all[$val['custom_code']]['in_num_all'];
            $val['in_money_all'] = $sum_all[$val['custom_code']]['in_money_all'];
        }
        return $this->format_ret(1, $ret_data);
    }

    /**
     * 导出查询总数量
     * @param type $filter
     * @return type
     */
    function get_export_sum_num($filter) {
        $join_sr = " SELECT sr.distributor_code AS custom_code,sum(srd.num) AS out_num,sum(srd.money) AS out_money,0 AS in_num,0 AS in_money "
                . "FROM wbm_store_out_record sr INNER JOIN wbm_store_out_record_detail srd ON sr.record_code = srd.record_code INNER JOIN base_goods bg ON srd.goods_code = bg.goods_code "
                . "WHERE  sr.is_sure=1 AND sr.is_store_out=1 ";
        $join_wr = " SELECT wr.distributor_code AS custom_code,0 AS out_num,0 AS out_money,sum(wrd.num) AS in_num,sum(wrd.money) AS in_money "
                . "FROM wbm_return_record wr INNER JOIN wbm_return_record_detail wrd ON wr.record_code = wrd.record_code INNER JOIN base_goods bg ON wrd.goods_code = bg.goods_code "
                . "WHERE wr.is_sure=1 and wr.is_store_in=1 ";
        $sql_values = array();
        // 业务时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $join_sr .= " AND sr.record_time >= :record_time_start " ;
            $join_wr .= " AND wr.record_time >= :record_time_start " ;
            $sql_values[':record_time_start'] = $filter['record_time_start']. ' 00:00:00';
        }
        // 业务时间
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $join_sr .= " AND sr.record_time <= :record_time_end " ;
            $join_wr .= " AND wr.record_time <= :record_time_end " ;
            $sql_values[':record_time_end'] = $filter['record_time_end']. ' 23:59:59';
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
            $join_sr .= " AND sr.store_code IN ({$store_code_str})";
            $join_wr .= " AND wr.store_code IN ({$store_code_str})";
        }else{
            $filter_store_code=null;
            $join_sr .= load_model('base/StoreModel')->get_sql_purview_store('sr.store_code', $filter_store_code);
            $join_wr .= load_model('base/StoreModel')->get_sql_purview_store('wr.store_code', $filter_store_code);
        }
        //分销商
        if (isset($filter['distributor_code']) && $filter['distributor_code'] <> '') {
            $arr = explode(',', $filter['distributor_code']);
            $str = $this->arr_to_in_sql_value($arr, 'distributor_code', $sql_values);
            $join_sr .= " AND sr.distributor_code in ({$str}) ";
            $join_wr .= " AND wr.distributor_code in ({$str}) ";
        }
        //品牌权限
        $join_sr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        $join_wr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        $join_sr.=" GROUP BY sr.distributor_code ";
        $join_wr.=" GROUP BY wr.distributor_code ";
        $sql_main = "FROM base_custom rl LEFT JOIN (" . $join_sr . " UNION ALL" . $join_wr . " ) AS t ON rl.custom_code=t.custom_code WHERE 1 ";
        $select = "SELECT rl.custom_code,sum(t.out_num) as out_num_all,sum(t.out_money) as out_money_all,sum(t.in_num) as in_num_all,sum(t.in_money) as in_money_all ";
        if (isset($filter['distributor_code']) && $filter['distributor_code'] <> '') {
            $arr = explode(',', $filter['distributor_code']);
            $str = $this->arr_to_in_sql_value($arr, 'custom_code', $sql_values);
            $sql_main .= " AND rl.custom_code in ({$str}) ";
        }

        //仓库
//        if (isset($filter['store_code']) && $filter['store_code'] != '') {
//            $store_code_arr = explode(',', $filter['store_code']);
//            $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
//            $sql_main .= " AND t.store_code IN ({$store_code_str})";
//        }
        $sql_main .= " GROUP BY rl.custom_code ";
        $sql = $select . $sql_main;
        $data = $this->db->get_all($sql, $sql_values);
        $sum_num = array();
        foreach ($data as $val) {
            $val['out_num_all'] = empty($val['out_num_all']) ? 0 : $val['out_num_all'];
            $val['out_money_all'] = empty($val['out_money_all']) ? 0 : $val['out_money_all'];
            $val['in_num_all'] = empty($val['in_num_all']) ? 0 : $val['in_num_all'];
            $val['in_money_all'] = empty($val['in_money_all']) ? 0 : $val['in_money_all'];
            $sum_num[$val['custom_code']] = $val;
        }
        return $sum_num;
    }

    function get_wbm_report_info_data($filter) {
        $sql_values = array();
        $sql_main = "";
        $this->get_param_by_filter($filter, $sql_values, $sql_main);

        $select = "rl.custom_code,rl.custom_name,sum(r2.num) as out_num,sum(r2.money) as out_money ";
        $sql_main .= "  GROUP BY rl.custom_code ";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $distributor_key_arr = array();

        foreach ($data['data'] as $key => &$val) {
            $distributor_key_arr[$val['custom_code']] = $key;
            $val['out_num'] = empty($val['out_num'])?0:$val['out_num'];
            $val['out_money'] = empty($val['out_money'])?0:$val['out_money']; 
        }
        $this->get_return_data($distributor_key_arr, $filter, $data['data']);

        return $this->format_ret(1, $data);
 }
 
    function get_return_data($distributor_key_arr, $filter, &$data) {

        $sql_values = array();
        $sql_main = "";
        $this->get_param_by_filter($filter, $sql_values, $sql_main, 1);
        //分销商
        if (!isset($filter['distributor_code']) || empty($filter['distributor_code'])) {
                      $arr = explode(',', $filter['distributor_code']);
        $str = $this->arr_to_in_sql_value($arr, 'distributor_code', $sql_values);
            $sql_main.=" AND rl.custom_code in ({$str})";
        }
        $sql = "select rl.custom_code,sum(r2.num) as in_num ,sum(r2.money) as in_money " . $sql_main . "  GROUP BY rl.custom_code ";

        $return_data = $this->db->get_all($sql, $sql_values);
        foreach ($return_data as $val) {
            $val['in_money'] = empty($val['in_money'])?0:$val['in_money'];
            $val['in_num'] = empty($val['in_num'])?0:$val['in_num']; 
            $row = $data[$distributor_key_arr[$val['custom_code']]];
            $data[$distributor_key_arr[$val['custom_code']]] = array_merge($row, $val);

        }
   
    }

    /**
     * 列表查询组装SQL
     * @param type $filter
     * @param type $sql_values
     * @param string $sql_main
     */
    function get_param_by_filter($filter, &$sql_values, &$sql_main) {
       $join_sr = " SELECT sr.distributor_code AS custom_code,sum(srd.num) AS out_num,sum(srd.money) AS out_money,0 AS in_num,0 AS in_money "
                . "FROM wbm_store_out_record sr INNER JOIN wbm_store_out_record_detail srd ON sr.record_code = srd.record_code INNER JOIN base_goods bg ON srd.goods_code = bg.goods_code  "
                . "WHERE  sr.is_sure=1 AND sr.is_store_out=1 ";
        $join_wr = " SELECT wr.distributor_code AS custom_code,0 AS out_num,0 AS out_money,sum(wrd.num) AS in_num,sum(wrd.money) AS in_money "
                . "FROM wbm_return_record wr INNER JOIN wbm_return_record_detail wrd ON wr.record_code = wrd.record_code INNER JOIN base_goods bg ON wrd.goods_code = bg.goods_code "
                . "WHERE wr.is_sure=1 and wr.is_store_in=1 ";

        $sql_values = array();
        // 开始时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $join_sr .= " AND sr.record_time >= :record_time_start ";
            $join_wr .= " AND wr.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start']. ' 00:00:00';
        }
        // 结束时间
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $join_sr .= " AND sr.record_time <= :record_time_end " ;
            $join_wr .= " AND wr.record_time <= :record_time_end " ;
            $sql_values[':record_time_end'] = $filter['record_time_end']. ' 23:59:59';
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
            $join_sr .= " AND sr.store_code IN ({$store_code_str}) ";
            $join_wr .= " AND wr.store_code IN ({$store_code_str}) ";
        } else {//仓库权限
            $filter_store_code = null;
            $join_sr .= load_model('base/StoreModel')->get_sql_purview_store('sr.store_code', $filter_store_code);
            $join_wr .= load_model('base/StoreModel')->get_sql_purview_store('wr.store_code', $filter_store_code);
        }
        //分销商
        $custom_code = isset($filter['distributor_code']) ? $filter['distributor_code'] : null;
        $join_sr .= load_model('base/CustomModel')->get_sql_purview_custom('sr.distributor_code', $custom_code);
        $join_wr .= load_model('base/CustomModel')->get_sql_purview_custom('wr.distributor_code', $custom_code);
        //品牌权限
        $join_sr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        $join_wr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        $join_sr.=" GROUP BY sr.distributor_code ";
        $join_wr.=" GROUP BY wr.distributor_code ";
        $sql_main = "FROM base_custom rl LEFT JOIN (" . $join_sr . " UNION ALL" . $join_wr . " ) AS t ON rl.custom_code=t.custom_code WHERE rl.custom_type = 'pt_fx' and rl.is_effective = 1";
    }

    
    /**
     * 批发明细报表组装sql 
     * @param type $filter
     * @param string $sql_main
     * @param type $sql_values
     */
    private function get_detail_param_by_filter($filter, &$sql_main, &$sql_values) {
        $join_sr = " SELECT sr.distributor_code AS custom_code,srd.sku,sum(srd.num) AS out_num,sum(srd.money) AS out_money,0 AS in_num,0 AS in_money "
                . "FROM wbm_store_out_record sr INNER JOIN wbm_store_out_record_detail srd ON sr.record_code = srd.record_code "
                . "WHERE 1 AND  sr.is_sure=1 and sr.is_store_out=1 ";
        $join_wr = " SELECT wr.distributor_code AS custom_code,wrd.sku,0 AS out_num,0 AS out_money,sum(wrd.num) AS in_num,sum(wrd.money) AS in_money "
                . "FROM wbm_return_record wr INNER JOIN wbm_return_record_detail wrd ON wr.record_code = wrd.record_code "
                . "WHERE 1 AND wr.is_sure=1 and wr.is_store_in=1 ";

        if (isset($filter['keyword_type']) && $filter['keyword_type'] != '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $sql_values = array();
        // 开始时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $join_sr .= " AND sr.record_time >= :record_time_start ";
            $join_wr .= " AND wr.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        // 开始时间
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $join_sr .= " AND sr.record_time <= :record_time_end ";
            $join_wr .= " AND wr.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        //分销商
        $custom_code = isset($filter['distributor_code']) ? $filter['distributor_code'] : null;
        $join_sr .= load_model('base/CustomModel')->get_sql_purview_custom('sr.distributor_code', $custom_code);
        $join_wr .= load_model('base/CustomModel')->get_sql_purview_custom('wr.distributor_code', $custom_code);
        if (isset($filter['goods_code']) && $filter['goods_code'] <> '') {
            $join_sr .= " AND srd.goods_code like :goods_code ";
            $join_wr .= " AND wrd.goods_code like :goods_code ";
            $sql_values[':goods_code'] = "%" . $filter['goods_code'] . "%";
        }
        //商品编码
        if (isset($filter['barcode']) && $filter['barcode'] <> '') {
//            $sql_where .= " AND r2.sku in (SELECT sku FROM goods_barcode WHERE  barcode like :barcode ) ";
//            $sql_values[':barcode'] ="%" . $filter['barcode'] . "%";
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $join_sr .= " AND 1=2 ";
                $join_wr .= " AND 1=2 ";
            } else {
                $sku_str = "'" . implode("','", $sku_arr) . "'";
                $join_sr .= " AND srd.sku in({$sku_str}) ";
                $join_wr .= " AND wrd.sku in({$sku_str}) ";
            }
        }
        $join_sr.=" GROUP BY sr.distributor_code,srd.sku ";
        $join_wr.=" GROUP BY wr.distributor_code,wrd.sku ";
        $sql_main = " FROM (" . $join_sr . " UNION ALL " . $join_wr . ") AS t ";
    }

         function get_return_detail_data($filter,&$data,$key_arr,$sku_arr,$custom_arr){
        $sql_main = "";
        $sql_values = array();
        $this->get_export_detail_param_by_filter($filter,$sql_main,$sql_values,1);
        
        $sku_str =  "'" . implode("','", $sku_arr) . "'";
        $sql_main .=" AND r3.sku in ({$sku_str}) ";
        
        $custom_str =  "'" . implode("','", $custom_arr) . "'";
        $sql_main .=" AND rl.custom_code in ({$custom_str}) ";

        $sql_main .="  GROUP BY custom_code,sku ";
        $sql = " select  rl.custom_code,r3.sku,sum(r3.num) as in_num,sum(r3.money) as in_money ".$sql_main;
        $return_data = $this->db->get_all($sql,$sql_values);
        foreach($return_data as $val){
            $val['in_num'] = empty($val['in_num'])?0:$val['in_num'];
            $val['in_money'] = empty($val['in_money'])?0:$val['in_money']; 
            $key = $this->get_detail_key($val);
            $row =  &$data[$key_arr[$key]];
            $row = array_merge($row,$val);
        }
        
    }

    
    /**
     * 
     * @param type $filter
     * @return type
     * 批发统计，按商品维度
     */
    function get_report_detail_by_page($filter) {
        $sql_main = "";
        $sql_values = array();
        $this->get_sql_by_filter($filter, $sql_main, $sql_values);
//        $this->get_detail_param_by_filter($filter, $sql_main, $sql_values);

        $sql_main .="  WHERE 1 GROUP BY t.custom_code,t.sku ";
        $select = "t.custom_code,t.sku,sum(t.out_num) AS out_num,sum(t.out_money) AS out_money,sum(t.in_num) AS in_num,sum(t.in_money) AS in_money ";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        foreach ($ret_data['data'] as $key => &$val) {
            $val['custom_name']=  oms_tb_val('base_custom', 'custom_name', array('custom_code'=>$val['custom_code']));
            $val['out_num'] = empty($val['out_num']) ? 0 : $val['out_num'];
            $val['out_money'] = empty($val['out_money']) ? 0 : $val['out_money'];
            $val['in_num'] = empty($val['in_num']) ? 0 : $val['in_num'];
            $val['in_money'] = empty($val['in_money']) ? 0 : $val['in_money'];
            $key_a = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'goods_code', 'brand_name', 'category_name', 'season_name', 'year_name','sell_price');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_a);
            $val = array_merge($val, $sku_info);
            //获取扩展属性
            if ($property_power) {
                $goods_property = load_model('prm/GoodsModel')->get_export_property($val['goods_code']);
                $val = $goods_property != -1 && is_array($goods_property) ? array_merge($val, $goods_property) : $val;
            }
        }
        return $this->format_ret(1, $ret_data);
    }

    /**批发统计，按单据维度
     * @param $filter
     * @return array
     */
    function get_record_report_detail_by_page($filter) {
        $sql_main = "";
        $sql_values = array();
        $this->get_sql_by_filter($filter, $sql_main, $sql_values, 1);
        $sql_main .= "  WHERE 1 ";
        $select = "t.record_type_name,t.record_code,t.custom_code,t.sku,t.num AS num,t.money AS money, t.remark,t.store_code";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        foreach ($ret_data['data'] as $key => &$val) {
            $val['custom_name']=  oms_tb_val('base_custom', 'custom_name', array('custom_code'=>$val['custom_code']));
            $val['store_name']=  oms_tb_val('base_store', 'store_name', array('store_code'=>$val['store_code']));
            $val['num'] = empty($val['num']) ? 0 : $val['num'];
            $val['money'] = empty($val['money']) ? 0 : $val['money'];
            $val['record_type_name']=$this->record_type_name[$val['record_type_name']];;
            $params = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'goods_code', 'brand_name', 'category_name', 'season_name', 'year_name', 'cost_price');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $params);
            $val = array_merge($val, $sku_info);
            //获取扩展属性
            if ($property_power) {
                $goods_property = load_model('prm/GoodsModel')->get_export_property($val['goods_code']);
                $val = $goods_property != -1 && is_array($goods_property) ? array_merge($val, $goods_property) : $val;
            }
        }
        return $this->format_ret(1, $ret_data);
    }

    /**
     * @param $filter
     * @param $sql_main
     * @param $sql_values
     */
    private function get_sql_by_filter($filter, &$sql_main, &$sql_values,$type=0) {
        $brand_join_sr = '';
        $brand_join_wr = '';
        if (isset($filter['goods_brand']) && $filter['goods_brand'] != '') {
            $brand_search = true;
            $brand_code_arr = explode(',', $filter['goods_brand']);
            $brand_join_sr = " INNER JOIN base_goods bg ON srd.goods_code = bg.goods_code " ;
            $brand_join_wr = " INNER JOIN base_goods bg ON wrd.goods_code = bg.goods_code ";
        }else{
            $brand_join_sr = " INNER JOIN base_goods bg ON srd.goods_code = bg.goods_code " ;
            $brand_join_wr = " INNER JOIN base_goods bg ON wrd.goods_code = bg.goods_code ";
        }
        if ($type == 0) {
            $join_sr = " SELECT sr.distributor_code AS custom_code,srd.sku,sum(srd.num) AS out_num,sum(srd.money) AS out_money,0 AS in_num,0 AS in_money "
                . "FROM wbm_store_out_record sr INNER JOIN wbm_store_out_record_detail srd ON sr.record_code = srd.record_code {$brand_join_sr} "
                . "WHERE 1 AND  sr.is_sure=1 and sr.is_store_out=1 ";
            $join_wr = " SELECT wr.distributor_code AS custom_code,wrd.sku,0 AS out_num,0 AS out_money,sum(wrd.num) AS in_num,sum(wrd.money) AS in_money "
                . "FROM wbm_return_record wr INNER JOIN wbm_return_record_detail wrd ON wr.record_code = wrd.record_code {$brand_join_wr} "
                . "WHERE 1 AND wr.is_sure=1 and wr.is_store_in=1 ";
        }elseif($type == 2){
            $join_sr = " SELECT sum(srd.num) AS out_num,sum(srd.money) AS out_money,0 AS in_num,0 AS in_money "
                . "FROM wbm_store_out_record sr INNER JOIN wbm_store_out_record_detail srd ON sr.record_code = srd.record_code {$brand_join_sr}"
                . "WHERE 1 AND  sr.is_sure=1 and sr.is_store_out=1 ";
            $join_wr = " SELECT 0 AS out_num,0 AS out_money,sum(wrd.num) AS in_num,sum(wrd.money) AS in_money "
                . "FROM wbm_return_record wr INNER JOIN wbm_return_record_detail wrd ON wr.record_code = wrd.record_code {$brand_join_wr} "
                . "WHERE 1 AND wr.is_sure=1 and wr.is_store_in=1 ";
        }else{
            $join_sr = " SELECT sr.is_store_out,0 AS record_type_name,sr.record_code,sr.distributor_code AS custom_code,srd.sku,srd.num AS num,srd.money AS money,sr.remark,sr.store_code "
                . "FROM wbm_store_out_record sr INNER JOIN wbm_store_out_record_detail srd ON sr.record_code = srd.record_code {$brand_join_sr}"
                . "WHERE 1 AND  sr.is_store_out=1 ";
            $join_wr = " SELECT wr.is_store_in,1 AS record_type_name,wr.record_code,wr.distributor_code AS custom_code,wrd.sku,wrd.num AS num,wrd.money AS money,wr.remark,wr.store_code "
                . "FROM wbm_return_record wr INNER JOIN wbm_return_record_detail wrd ON wr.record_code = wrd.record_code {$brand_join_wr}"
                . "WHERE 1 AND wr.is_store_in=1 ";
        }

        if (isset($filter['keyword_type']) && $filter['keyword_type'] != '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        // 开始时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $join_sr .= " AND sr.record_time >= :record_time_start ";
            $join_wr .= " AND wr.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        // 开始时间
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $join_sr .= " AND sr.record_time <= :record_time_end ";
            $join_wr .= " AND wr.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            if ($type == 1 || $type == 2) {
                $join_sr .= " AND sr.record_code = :record_code ";
                $join_wr .= " AND wr.record_code = :record_code ";
                $sql_values[':record_code'] = $filter['record_code'];
            } else {
                $join_sr .= " AND 1 = 2 ";
                $join_wr .= " AND 1 = 2 ";
            }
        }

        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] <> '') {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
            $join_sr .= " AND sr.store_code IN ({$store_code_str}) ";
            $join_wr .= " AND wr.store_code IN ({$store_code_str})";
        }else{
            $filter_store_code = null;
            $join_sr .= load_model('base/StoreModel')->get_sql_purview_store('sr.store_code', $filter_store_code);
            $join_wr .= load_model('base/StoreModel')->get_sql_purview_store('wr.store_code', $filter_store_code);
        }

        //分销商
        $custom_code = isset($filter['distributor_code']) ? $filter['distributor_code'] : null;
        $join_sr .= load_model('base/CustomModel')->get_sql_purview_custom('sr.distributor_code', $custom_code);
        $join_wr .= load_model('base/CustomModel')->get_sql_purview_custom('wr.distributor_code', $custom_code);
        if (isset($filter['goods_code']) && $filter['goods_code'] <> '') {
            $join_sr .= " AND srd.goods_code like :goods_code ";
            $join_wr .= " AND wrd.goods_code like :goods_code ";
            $sql_values[':goods_code'] = "%" . $filter['goods_code'] . "%";
        }
        //商品编码
        if (isset($filter['barcode']) && $filter['barcode'] <> '') {
//            $sql_where .= " AND r2.sku in (SELECT sku FROM goods_barcode WHERE  barcode like :barcode ) ";
//            $sql_values[':barcode'] ="%" . $filter['barcode'] . "%";
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $join_sr .= " AND 1=2 ";
                $join_wr .= " AND 1=2 ";
            } else {
                $sku_str = "'" . implode("','", $sku_arr) . "'";
                $join_sr .= " AND srd.sku in({$sku_str}) ";
                $join_wr .= " AND wrd.sku in({$sku_str}) ";
            }
        }
        //品牌
        if ($brand_search === true) {
            $brand_code_str = $this->arr_to_in_sql_value($brand_code_arr, 'brand_code', $sql_values);
            $join_sr .= " AND bg.brand_code in({$brand_code_str}) ";
            $join_wr .= " AND bg.brand_code in({$brand_code_str}) ";
        }else{
            $join_sr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
            $join_wr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        }
        if($type==0){
            $join_sr.=" GROUP BY sr.distributor_code,srd.sku ";
            $join_wr.=" GROUP BY wr.distributor_code,wrd.sku ";
        }
        $sql_main = " FROM (" . $join_sr . " UNION ALL " . $join_wr . ") AS t ";
    }

    private function  get_detail_key($val){
        return  $val['custom_code'] . "," . $val['sku'];
    }


    
    function get_barocde_info($sky_arr, $goods_arr, &$data) {
        $sql_values = array();
        $sky_str = $this->arr_to_in_sql_value($sky_arr, 'sku', $sql_values);
        $sql = "select goods_code,barcode,sku from goods_sku where sku in({$sky_str})";
        //echo $sql;die;
        $sku_data = $this->db->get_all($sql,$sql_values);
        $goods_data = $this->get_gooods_info($goods_arr);
        $item_data = array();
        foreach ($sku_data as $val) {
            $item_data[$val['sku']] = array_merge($val, $goods_data[$val['goods_code']]);
        }
        foreach($data as &$row){
            if(isset($item_data[$row['sku']])){
                $row = array_merge($row,$item_data[$row['sku']]);
            }
        }
    }

    function get_gooods_info($goods_arr) {
        $sql_values = array();
        $goods_str = $this->arr_to_in_sql_value($goods_arr, 'goods_code', $sql_values);
        $sql = "select bg.goods_name,bg.goods_code,bb.brand_name,bc.category_name,bs.season_name,y.year_name from base_goods bg "
                . " LEFT JOIN base_brand bb ON bg.brand_code=bb.brand_code "
                . " LEFT JOIN base_category bc ON bg.category_code=bc.category_code "
                . " LEFT JOIN base_season bs ON bg.season_code=bs.season_code "
                . " LEFT JOIN base_year y ON bg.year_code=y.year_code "
                . " where bg.goods_code in({$goods_str})";

         $data = $this->db->get_all($sql,$sql_values);
        $goods_data = array();
        foreach ($data as $val) {
            $this->is_null_set_empty($val);
            $goods_data[$val['goods_code']] = $val;
        }
        return $goods_data;
    }
    private function is_null_set_empty(&$data){
        foreach($data as $key=>&$val){
            if(is_null($val)){
                $val = '';
            }
        }
        
    }

    /**
     * 单据数据统计
     * @param $filter
     */
    public function record_count($filter){
        //统计数据
        $count_arr = array();
        $out_sql = '';
        $out_sql_value = array();
        //统计批发数据
        $out_ret_data = $this->get_count_by_filter($filter,$out_sql,$out_sql_value,1);
        if($out_ret_data['status'] == -1){
            return $out_ret_data;
        }
        $out_data = $this->db->get_row($out_sql,$out_sql_value);
        $count_arr['out_num'] = isset($out_data['num']) ? $out_data['num'] : 0;
        $count_arr['out_money'] = isset($out_data['money']) ? $out_data['money'] : 0;
        $in_sql = '';
        $in_sql_value = array();
        //统计退货数据
        $in_ret_data = $this->get_count_by_filter($filter,$in_sql,$in_sql_value,2);
        if($in_ret_data['status'] == -1){
            return $in_ret_data;
        }
        $in_data = $this->db->get_row($in_sql,$in_sql_value);
        $count_arr['in_num'] = isset($in_data['num']) ? $in_data['num'] : 0;
        $count_arr['in_money'] = isset($in_data['money']) ? $in_data['money'] : 0;
        return $count_arr;
    }

    /**
     * 获取批发销货/退货的
     * @param $filter
     * @param $type
     */
    public function get_count_by_filter(&$filter,&$sql,&$sql_values,$type){
        switch($type){
            case 1:
                $table = 'wbm_store_out_record';
                $join = ' INNER JOIN wbm_store_out_record_detail wbmd ON wbm.record_code = wbmd.record_code INNER JOIN base_goods bg ON wbmd.goods_code = bg.goods_code  ';
                $column1='is_store_out';
                break;
            case 2:
                $table = 'wbm_return_record';
                $join = ' INNER JOIN wbm_return_record_detail wbmd ON wbm.record_code = wbmd.record_code INNER JOIN base_goods bg ON wbmd.goods_code = bg.goods_code ';
                $column1='is_store_in';
                break;
            default:
                $table = -1;
        }
        if($table == -1){
            return $this->format_ret(-1,'','表不存在');
        }

        $sql = 'select 
                      sum(wbmd.num) as num,
                      sum(wbmd.money) as money
                from base_custom bc 
                inner join '.$table.' wbm on bc.custom_code=wbm.distributor_code '.$join.'
                WHERE bc.custom_type = "pt_fx" and bc.is_effective = 1 and wbm.is_sure = 1 and wbm.'.$column1.' = 1 ';
        $sql_user = "SELECT user_code FROM sys_user WHERE (status = 2 OR status = 0) AND login_type = 2;";
        $user_code = $this->db->get_all_col($sql_user);
        if (!empty($user_code)) {
            $user_str = $this->arr_to_in_sql_value($user_code, 'user_code', $sql_values);
            $sql .= " AND (bc.user_code NOT IN ({$user_str}) OR bc.user_code is null) ";
        }
        // 开始时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $sql .= " AND wbm.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start']. ' 00:00:00';
        }
        // 结束时间
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql .= " AND wbm.record_time <= :record_time_end " ;
            $sql_values[':record_time_end'] = $filter['record_time_end']. ' 23:59:59';
        }
        //分销商
        $custom_code = isset($filter['distributor_code']) ? $filter['distributor_code'] : null;
        $sql .= load_model('base/CustomModel')->get_sql_purview_custom('wbm.distributor_code', $custom_code);
        //品牌
        $sql .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_code_arr=explode(',',$filter['store_code']);
            $store_code_str=$this->arr_to_in_sql_value($store_code_arr,'store_code',$sql_values);
            $sql .= " AND wbm.store_code IN ({$store_code_str}) " ;
        }else{//默认权限
            $filter_store_code = null;
            $sql .= load_model('base/StoreModel')->get_sql_purview_store('wbm.store_code', $filter_store_code);
        }
        return $this->format_ret(1,'','拼接成功');
    }

    /**
     * 批发统计明细统计
     * @param $filter
     */
    public function detail_count($filter){
        $sql_main = '';
        $sql_values = array();
        $this->get_sql_by_filter($filter, $sql_main, $sql_values,2);
        $select = "select sum(t.out_num) AS out_num,sum(t.out_money) AS out_money,sum(t.in_num) AS in_num,sum(t.in_money) AS in_money ";
        $ret_data = $this->db->get_row($select.$sql_main,$sql_values);
        foreach ($ret_data as &$value){
            $value = $value ? $value : 0;
        }
        $detail_data = !empty($ret_data) ?$ret_data:array('out_num'=>0,'out_money'=>0,'in_num'=>0,'in_money'=>0);
        return $detail_data;
    }

}
