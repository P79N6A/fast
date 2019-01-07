<?php

/**
 * 采购报表
 *
 * @author wq
 *
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);

class PurReportModel extends TbModel {
    private $purchase_price_status = null;
    public $record_type_name = array(
        '0'=>'采购进货',
        '1'=>'采购退货',
    );


    function __construct() {
        parent::__construct();
       // $this->record_type_name = $this->get_record_type_name();
    }

    /**获取类型名称
     * @return mixed
     */
//    function get_record_type_name() {
//        $sql = "SELECT record_type_code,record_type_name FROM base_record_type";
//        $ret = $this->db->get_all($sql);
//        $record = load_model('util/ViewUtilModel')->get_map_arr($ret, 'record_type_code');
//        return $record;
//    }


    //base_supplier
    function get_report_by_page($filter) {
         //导出
        if ($filter['ctl_type'] == 'export') {
            return $this->pur_record_report_search_csv($filter);
        }
        $sql_values = array();
        $sql_main = "";
        $this->get_pur_sql_by_filter($filter, $sql_values, $sql_main);

        $select = "rl.supplier_code,rl.supplier_name,sum(t.return_num) as return_num,sum(t.return_money) as return_money,sum(t.record_num) as record_num,sum(t.record_money) as record_money ";
        //供应商权限
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $sql_main .= load_model('base/SupplierModel')->get_sql_purview_supplier('rl.supplier_code', $filter_supplier_code);
        if (isset($filter['supplier_code']) && $filter['supplier_code'] <> '') {
            $arr = explode(',', $filter['supplier_code']);
            $str = $this->arr_to_in_sql_value($arr, 'supplier_code', $sql_values);
            $sql_main .= " AND rl.supplier_code IN ({$str}) ";
        }

        $sql_main .= " GROUP BY rl.supplier_code ";

        //获取操作人的id
        $user_arr['user_code'] = isset($filter['__t_user_code']) ? $filter['__t_user_code'] : '';
        $user_id_arr = load_model('sys/UserModel')->get_by_code($user_arr['user_code'],'user_id');
        $user_id = isset($user_id_arr['user_id']) ? $user_id_arr['user_id'] : '';
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price',$user_id);

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as $key => &$val) {
            $val['record_num'] = empty($val['record_num']) ? 0 : $val['record_num'];
            $val['record_money'] = empty($val['record_money']) ? 0 : $val['record_money'];
            $val['return_num'] = empty($val['return_num']) ? 0 : $val['return_num'];
            $val['return_money'] = empty($val['return_money']) ? 0 : $val['return_money'];
            //金额权限控制
            if($status['status'] != 1){
                $val['return_money'] = "****";
                $val['record_money'] = "****";
            }
        }

        return $this->format_ret(1, $data);
    }

    /**
     * 主单列表导出包含明细
     * @param $filter
     * @return array
     */
    public function pur_record_report_search_csv($filter) {
        //获取操作人的id
        $user_arr['user_code'] = isset($filter['__t_user_code']) ? $filter['__t_user_code'] : '';
        $user_id_arr = load_model('sys/UserModel')->get_by_code($user_arr['user_code'],'user_id');
        $user_id = isset($user_id_arr['user_id']) ? $user_id_arr['user_id'] : '';
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price',$user_id);
        $this->purchase_price_status = $status['status'];

        $sql_values = array();
        $join_sr = " SELECT sr.supplier_code,srd.sku,sum(srd.num) AS record_num,sum(srd.money) AS record_money,0 AS return_num,0 AS return_money "
            . "FROM pur_purchaser_record sr INNER JOIN pur_purchaser_record_detail srd ON sr.record_code = srd.record_code INNER JOIN base_goods bg ON srd.goods_code = bg.goods_code "
            . "WHERE 1 AND  sr.is_check_and_accept=1 ";
        $join_wr = " SELECT wr.supplier_code,wrd.sku,0 AS record_num,0 AS record_money,sum(wrd.num) AS return_num,sum(wrd.money) AS return_money "
            . "FROM pur_return_record wr INNER JOIN pur_return_record_detail wrd ON wr.record_code = wrd.record_code INNER JOIN base_goods bg ON wrd.goods_code = bg.goods_code "
            . "WHERE 1 AND wr.is_store_out=1 ";
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

        //供应商权限
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $join_sr .= load_model('base/SupplierModel')->get_sql_purview_supplier('sr.supplier_code', $filter_supplier_code);
        $join_wr .= load_model('base/SupplierModel')->get_sql_purview_supplier('wr.supplier_code', $filter_supplier_code);
        //品牌权限
        $join_sr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        $join_wr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        $join_sr.=" GROUP BY sr.supplier_code,srd.sku ";
        $join_wr.=" GROUP BY wr.supplier_code,wrd.sku ";

        $sql_main = "FROM base_supplier r1 LEFT JOIN ( " . $join_sr . " UNION ALL " . $join_wr . ") AS t ON r1.supplier_code=t.supplier_code ";
        $sql_main .="  WHERE 1 ";

        $select = "r1.supplier_code,r1.supplier_name,t.sku,sum(t.record_num) AS record_num,sum(t.record_money) AS record_money,sum(t.return_num) AS return_num,sum(t.return_money) AS return_money ";

        //供应商检索
        if (isset($filter['supplier_code']) && $filter['supplier_code'] <> '') {
            $arr = explode(',', $filter['supplier_code']);
            $str = $this->arr_to_in_sql_value($arr, 'supplier_code', $sql_values);
            $sql_main .= " AND r1.supplier_code IN ({$str}) ";
        }

        $sql_main .= load_model('base/SupplierModel')->get_sql_purview_supplier('r1.supplier_code', $filter_supplier_code);
        $sql_main.= " GROUP BY r1.supplier_code,t.sku ";
        //获取总数量
        $sum_all=$this->get_export_sum_num($filter);
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($ret_data['data'] as $key => &$val) {
            $val['record_num'] = empty($val['record_num']) ? 0 : $val['record_num'];
            $val['record_money'] = empty($val['record_money']) ? 0 : $val['record_money'];
            $val['return_num'] = empty($val['return_num']) ? 0 : $val['return_num'];
            $val['return_money'] = empty($val['return_money']) ? 0 : $val['return_money'];
            $key_a = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'goods_code', 'brand_name', 'category_name', 'season_name', 'year_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_a);
            $val = array_merge($val, $sku_info);
            $val['record_num_all'] =$sum_all[$val['supplier_code']]['record_num_all'];
            $val['record_money_all'] = $sum_all[$val['supplier_code']]['record_money_all'];
            $val['return_num_all'] = $sum_all[$val['supplier_code']]['return_num_all'];
            $val['return_money_all'] = $sum_all[$val['supplier_code']]['return_money_all'];
            //价格权限控制
            if($this->purchase_price_status != 1){
                $val['record_money'] = '****';
                $val['return_money'] = '****';
                $val['record_money_all'] = '****';
                $val['return_money_all'] = '****';
            }
        }
        return $this->format_ret(1, $ret_data);
    }


    /**
     * 获取供应商采购，退货总数
     * @param $filter
     */
    function get_export_sum_num($filter) {
        $sql_values = array();
        $sql_main = '';
        $this->get_pur_sql_by_filter($filter, $sql_values, $sql_main);
        $select = "SELECT rl.supplier_code,sum(t.record_num) as record_num_all,sum(t.record_money) as record_money_all,sum(t.return_num) as return_num_all,sum(t.return_money) as return_money_all ";
        if (isset($filter['supplier_code']) && $filter['distributor_code'] <> '') {
            $arr = explode(',', $filter['supplier_code']);
            $str = $this->arr_to_in_sql_value($arr, 'supplier_code', $sql_values);
            $sql_main .= " AND rl.supplier_code IN ({$str}) ";
        }
        $sql_main .= " GROUP BY rl.supplier_code ";
        $sql = $select . $sql_main;
        $data = $this->db->get_all($sql, $sql_values);
        $sum_num = array();
        foreach ($data as $val) {
            $val['record_num_all'] = empty($val['record_num_all']) ? 0 : $val['record_num_all'];
            $val['record_money_all'] = empty($val['record_money_all']) ? 0 : $val['record_money_all'];
            $val['return_num_all'] = empty($val['return_num_all']) ? 0 : $val['return_num_all'];
            $val['return_money_all'] = empty($val['return_money_all']) ? 0 : $val['return_money_all'];
            $sum_num[$val['supplier_code']] = $val;
        }
        return $sum_num;
    }



    /**
     * 主单列表查询组装sql
     * @param $filter
     * @param $sql_values
     * @param $sql_main
     */
    function get_pur_sql_by_filter($filter, &$sql_values, &$sql_main) {
        $join_sr = " SELECT sr.supplier_code,sum(srd.num) AS record_num,sum(srd.money) AS record_money,0 AS return_num,0 AS return_money "
            . "FROM pur_purchaser_record sr INNER JOIN pur_purchaser_record_detail srd ON sr.record_code = srd.record_code INNER JOIN base_goods bg ON srd.goods_code = bg.goods_code "
            . "WHERE  sr.is_check_and_accept=1 ";
        $join_wr = " SELECT wr.supplier_code,0 AS record_num,0 AS record_money,sum(wrd.num) AS return_num,sum(wrd.money) AS return_money "
            . "FROM pur_return_record wr INNER JOIN pur_return_record_detail wrd ON wr.record_code = wrd.record_code INNER JOIN base_goods bg ON wrd.goods_code = bg.goods_code "
            . "WHERE wr.is_store_out=1 ";

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
        //供应商权限
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $join_sr .= load_model('base/SupplierModel')->get_sql_purview_supplier('sr.supplier_code', $filter_supplier_code);
        $join_wr .= load_model('base/SupplierModel')->get_sql_purview_supplier('wr.supplier_code', $filter_supplier_code);
        //品牌权限
        $join_sr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        $join_wr .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code');
        $join_sr.=" GROUP BY sr.supplier_code ";
        $join_wr.=" GROUP BY wr.supplier_code ";
        $sql_main = "FROM base_supplier rl LEFT JOIN (" . $join_sr . " UNION ALL" . $join_wr . " ) AS t ON rl.supplier_code=t.supplier_code WHERE 1 ";
    }


    //导出
    public function pur_report_search_csv($filter) {
        //获取操作人的id
        $user_arr['user_code'] = isset($filter['__t_user_code']) ? $filter['__t_user_code'] : '';
        $user_id_arr = load_model('sys/UserModel')->get_by_code($user_arr['user_code'],'user_id');
        $user_id = isset($user_id_arr['user_id']) ? $user_id_arr['user_id'] : '';
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price',$user_id);
        $this->purchase_price_status = $status['status'];
        $sql_main = "";
        $sql_values = array();

        $this->get_detail_param_by_filter($filter,$sql_main,$sql_values);

        $sql_main .="GROUP BY supplier_code,sku ";
        $select = "rl.supplier_code,rl.supplier_name,r3.sku,r3.goods_code,r3.spec1_code,r3.spec2_code,sum(r3.num) as record_num,sum(r3.money) as record_money ";

        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

        $sku_arr = array();
        $goods_arr = array();
        $key_arr = array();
        $supplier_arr = array();
        foreach ($ret_data['data'] as $key => &$val) {
            $val['supplier_code_sku'] = $this->get_detail_key($val);
            
            $sku_arr[] = $val['sku'];
            $goods_arr[] = $val['goods_code'];
            $supplier_arr[$val['supplier_code']] = $val['supplier_code'];
            $key_arr[$val['supplier_code_sku']] = $key;
            
            $val['record_num'] = empty($val['record_num'])?0:$val['record_num'];
            if($this->purchase_price_status == 1){
                $val['record_money'] = empty($val['record_money'])?0:$val['record_money'];
                $val['return_money'] = 0;
            }else{
                $val['record_money'] = '****';
                $val['return_money'] = '****';
            }
            $val['return_num'] = 0;
            
            $key_a = array('brand_name','category_name','season_name','year_name','goods_name','spec1_name','spec2_name','barcode');
            $sku_info =  load_model('goods/SkuCModel')->get_sku_info($val['sku'],$key_a);
            

            $val = array_merge($val,$sku_info);

        }
        $this->get_return_detail_data($filter,$ret_data['data'],$key_arr);
        $result_main=$this->get_return_info_data($filter);
        foreach($ret_data['data'] as $k=>&$v){
            $v['money'] = $this->purchase_price_status == 1 ? $v['money'] : '****';
            $v['record_money'] = $this->purchase_price_status == 1 ? $v['record_money'] : '****';
           foreach($result_main['data']['data'] as $k1=>$v1){
              if($v['supplier_code']==$v1['supplier_code']){
                  $v['record_num_all']=$v1['record_num'];
                    $v['record_money_all']=$v1['record_money'];
                      $v['return_num_all']=$v1['return_num'];
                        $v['return_money_all']=$v1['return_money'];
              }
           } 
        }
         return $this->format_ret(1, $ret_data);
    
    }
    
    
    function get_return_info_data($filter){
        $sql_values = array();
        $sql_main = "";
        $this->get_param_by_filter($filter, $sql_values, $sql_main);

        $select = "rl.supplier_code,rl.supplier_name,r2.store_code,sum(r3.num) as record_num,sum(r3.money) as record_money ";
        $sql_main .= "  GROUP BY rl.supplier_code ";
        
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $supplier_key_arr = array();

        foreach ($data['data'] as $key => &$val) {
            $supplier_key_arr[$val['supplier_code'] . '_' . $val['store_code']] = $key;
            $val['record_num'] = empty($val['record_num'])?0:$val['record_num'];
            $val['record_money'] = empty($val['record_money'])?0:$val['record_money']; 
        }
        $this->get_return_data($supplier_key_arr, $filter, $data['data']);

        return $this->format_ret(1, $data);
        
    }

    
    function get_return_data($supplier_key_arr, $filter, &$data,$supplier_code_arr=array()) {

        $sql_values = array();
        $sql_main = "";
        $this->get_param_by_filter($filter, $sql_values, $sql_main, 1);
        //分销商
        if (!isset($filter['supplier_code']) || empty($filter['supplier_code'])) {
            if (!empty($supplier_code_arr)) {
                $supplier_code_arr = array_unique($supplier_code_arr);
                $supplier_code_str = $this->arr_to_in_sql_value($supplier_code_arr, 'return_supplier_code', $sql_values);
                $sql_main .= " AND rl.supplier_code in ({$supplier_code_str})";
            }
//            $supplier_arr = array_keys($supplier_key_arr);
//            $supplier_arr_str = "'" . implode("','", $supplier_arr) . "'";
//            $sql_main .= " AND rl.supplier_code in ({$supplier_arr_str})";
        }
        $sql = "select rl.supplier_code,sum(r2.num) as return_num ,sum(r2.money) as return_money,r2.store_code " . $sql_main . "  GROUP BY rl.supplier_code,r2.store_code ";

        $return_data = $this->db->get_all($sql, $sql_values);
        //获取操作人的id
        $user_arr['user_code'] = isset($filter['__t_user_code']) ? $filter['__t_user_code'] : '';
        $user_id_arr = load_model('sys/UserModel')->get_by_code($user_arr['user_code'],'user_id');
        $user_id = isset($user_id_arr['user_id']) ? $user_id_arr['user_id'] : '';
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price',$user_id);
        foreach ($return_data as $val) {
            $val['return_money'] = empty($val['return_money'])?0:$val['return_money'];
            $val['return_num'] = empty($val['return_num'])?0:$val['return_num'];
            $key = $val['supplier_code'] . '_' . $val['store_code'];
            $row = $data[$supplier_key_arr[$key]];
            if(isset($row)){
                if($status['status'] != 1){
                    $val['return_money'] = "****";
                    $row['record_money'] = "****";
                }
                $data[$supplier_key_arr[$key]] = array_merge($row, $val);
            }
        }

        //价格权限控制
        foreach ($data as &$pur_val) {
            $pur_val['return_num'] = empty($pur_val['return_num']) ? 0 : $pur_val['return_num'];
            if ($status['status'] != 1) {
                $pur_val['return_money'] = "****";
                $pur_val['record_money'] = "****";
            } else {
                $pur_val['return_money'] = empty($pur_val['return_money']) ? 0 : $pur_val['return_money'];
                $pur_val['record_money'] = empty($pur_val['record_money']) ? 0 : $pur_val['record_money'];
            }
        }

    }

    function get_param_by_filter($filter, &$sql_values, &$sql_main, $type = 0) {
        $join = "";
        if ($type == 0) {
            $join = " LEFT JOIN pur_purchaser_record r2 on rl.supplier_code = r2.supplier_code  and r2.is_check_and_accept=1 ";
            $join .= " LEFT JOIN pur_purchaser_record_detail r3 on r2.record_code = r3.record_code  ";
        } else {
            $join = " LEFT JOIN pur_return_record r2 on rl.supplier_code = r2.supplier_code  and r2.is_store_out=1 ";
        }


        $sql_main = "FROM base_supplier rl  ";
        $sql_where = " WHERE  1=1  ";

        $sql_values = array();
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $sql_where .= load_model('base/SupplierModel')->get_sql_purview_supplier('rl.supplier_code', $filter_supplier_code);
        // 开始时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $join .= " AND r2.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        // 开始时间
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $join .= " AND r2.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
            $sql_where .= " AND r2.store_code IN ({$store_code_str})";
        }

        $sql_main .=$join . $sql_where;
    }

    private function get_detail_param_by_filter($filter,&$sql_main,&$sql_values,$type=0) {
        
        if ($type == 0) {

            $join = " INNER JOIN pur_purchaser_record r2 on rl.supplier_code = r2.supplier_code  and r2.is_check_and_accept=1 ";
            $join_detail = " INNER JOIN pur_purchaser_record_detail r3 ON r2.record_code = r3.record_code  ";
        } else {
            $join = " INNER JOIN pur_return_record r2 ON rl.supplier_code = r2.supplier_code  and r2.is_store_out=1 ";
            $join_detail = " INNER JOIN pur_return_record_detail r3 ON r2.record_code = r3.record_code ";
        }
        if (isset($filter['keyword_type']) && $filter['keyword_type'] != '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        

        $sql_where = " WHERE  1=1  ";

        $sql_values = array();
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $sql_where .= load_model('base/SupplierModel')->get_sql_purview_supplier('rl.supplier_code', $filter_supplier_code);
        // 开始时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $sql_where .= " AND r2.record_time >= :record_time_start ";
          //  $join_return .= " AND r3.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        // 开始时间
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql_where .= " AND r2.record_time <= :record_time_end ";
           // $join_return .= " AND r3.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }

        if (isset($filter['goods_code']) && $filter['goods_code'] <> '') {
            $sql_where .= " AND r3.goods_code like :goods_code ";
            $sql_values[':goods_code'] = "%" . $filter['goods_code'] . "%";
        }

        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
            $sql_where .= " AND r2.store_code IN ({$store_code_str})";
        }

        if (isset($filter['barcode']) && $filter['barcode'] <> '') {
     	     $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);

                            if(empty($sku_arr)){
                                   $sql_where .= " AND 1=2 ";
                            }else{
                                  $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                                $sql_where .= " AND r3.sku in({$sku_str}) ";
                            }           
            
        }     
        $sql_main = " FROM base_supplier rl  ".$join.$join_detail.$sql_where;
    }

    /**批发统计，按商品维度
     * @param $filter
     * @return array
     */
    function get_report_detail_by_page($filter) {
        $sql_main = "";
        $sql_values = array();
        $this->get_sql_by_filter($filter, $sql_main, $sql_values);
        $sql_main .= "  WHERE 1 GROUP BY t.supplier_code,t.sku,t.record_time ";
        if (isset($filter['is_sort']) && $filter['is_sort'] == 'record_time_desc') {
            $sql_main .= ' ORDER BY t.record_time DESC ';
        }
        $select = "t.record_time,t.supplier_code,t.sku,sum(t.pur_num) AS pur_num,sum(t.pur_money) AS pur_money,sum(t.return_num) AS return_num,sum(t.return_money) AS return_money ";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        foreach ($ret_data['data'] as $key => &$val) {
            $val['supplier_name'] = oms_tb_val('base_supplier', 'supplier_name', array('supplier_code' => $val['supplier_code']));
            $val['pur_num'] = empty($val['pur_num']) ? 0 : $val['pur_num'];
            $val['pur_money'] = empty($val['pur_money']) ? 0 : $val['pur_money'];
            $val['return_num'] = empty($val['return_num']) ? 0 : $val['return_num'];
            $val['return_money'] = empty($val['return_money']) ? 0 : $val['return_money'];
            $params = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'goods_code', 'brand_name', 'category_name', 'season_name', 'year_name', 'cost_price');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $params);
            $val = array_merge($val, $sku_info);
            //获取扩展属性
            if ($property_power) {
                $goods_property = load_model('prm/GoodsModel')->get_export_property($val['goods_code']);
                $val = $goods_property != -1 && is_array($goods_property) ? array_merge($val, $goods_property) : $val;
            }
        }
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $filter['user_id']);
        if ($status['status'] != 1) {
            foreach ($ret_data['data'] as &$val) {
                $val['pur_money'] = '****';
                $val['return_money'] = '****';
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
        $sql_main .= "  WHERE 1 ";//GROUP BY t.record_code,t.supplier_code,t.sku
        //单据类型
        if(isset($filter['record_type'])&&$filter['record_type']!=''){
            $sql_main.='and record_type_code = :record_type_code';
            $sql_values[':record_type_code']=$filter['record_type'];
        }
        if (isset($filter['is_sort']) && $filter['is_sort'] == 'record_time_desc') {
            $sql_main .= ' ORDER BY t.record_time DESC,t.record_code ASC ';
        }
        $select = "t.store_code,t.new_record_type_name,t.record_time,t.record_type_code,t.record_code,t.supplier_code,t.sku,t.num AS num,t.money AS money,t.remark ";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $store_arr = array();
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        foreach ($ret_data['data'] as $key => &$val) {
            $val['supplier_name'] = oms_tb_val('base_supplier', 'supplier_name', array('supplier_code' => $val['supplier_code']));
            $val['num'] = empty($val['num']) ? 0 : $val['num'];
            $val['money'] = empty($val['money']) ? 0 : $val['money'];
            $store_name = isset($store_arr[$val['store_code']]) ? $store_arr[$val['store_code']] : oms_tb_val('base_store', 'store_name', array('store_code' => $val['store_code']));
            $val['store_name'] = $store_name;
            $store_arr[$val['store_code']] = $store_name;
            $val['record_type_name']=$this->record_type_name[$val['record_type_code']];
            $val['new_record_type_name']=$this->get_record_type_name($val['new_record_type_name']);
            $params = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'goods_code', 'brand_name', 'category_name', 'season_name', 'year_name', 'cost_price');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $params);
            $val = array_merge($val, $sku_info);
            //获取扩展属性
            if ($property_power) {
                $goods_property = load_model('prm/GoodsModel')->get_export_property($val['goods_code']);
                $val = $goods_property != -1 && is_array($goods_property) ? array_merge($val, $goods_property) : $val;
            }
        }
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price',$filter['user_id']);
        if ($status['status'] != 1) {
            foreach ($ret_data['data'] as &$val) {
                $val['money'] = '****';
            }
        }
        return $this->format_ret(1, $ret_data);
    }
    public function get_record_type_name($type_code){
        $sql="select record_type_name from base_record_type where record_type_code = :record_type_code ";
        $res=$this->db->get_row($sql,array(':record_type_code'=>$type_code));
        return $res['record_type_name'];
    }

    /**
     * @param $filter
     * @param $sql_main
     * @param $sql_values
     */
    private function get_sql_by_filter($filter, &$sql_main, &$sql_values, $type = 0) {
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
            $join_sr = " SELECT sr.record_time,sr.supplier_code,srd.sku,sum(srd.num) AS pur_num,sum(srd.money) AS pur_money,0 AS return_num,0 AS return_money "
                . "FROM pur_purchaser_record sr INNER JOIN pur_purchaser_record_detail srd ON sr.record_code = srd.record_code {$brand_join_sr} "
                . "WHERE 1 AND  sr.is_check_and_accept=1 ";
            $join_wr = " SELECT wr.record_time,wr.supplier_code,wrd.sku,0 AS pur_num,0 AS pur_money,sum(wrd.num) AS return_num,sum(wrd.money) AS return_money "
                . "FROM pur_return_record wr INNER JOIN pur_return_record_detail wrd ON wr.record_code = wrd.record_code {$brand_join_wr} "
                . "WHERE 1 AND wr.is_store_out=1 ";
        }elseif($type == 2){
            //详情页面数据统计
            $join_sr = " SELECT 0 as record_type_code,sum(srd.num) AS pur_num,sum(srd.money) AS pur_money,0 AS return_num,0 AS return_money "
                . "FROM pur_purchaser_record sr INNER JOIN pur_purchaser_record_detail srd ON sr.record_code = srd.record_code {$brand_join_sr} "
                . "WHERE 1 AND  sr.is_check_and_accept=1 ";
            $join_wr = " SELECT 1 as record_type_code,0 AS pur_num,0 AS pur_money,sum(wrd.num) AS return_num,sum(wrd.money) AS return_money "
                . "FROM pur_return_record wr INNER JOIN pur_return_record_detail wrd ON wr.record_code = wrd.record_code {$brand_join_wr} "
                . "WHERE 1 AND wr.is_store_out=1 ";
        }else{
            $join_sr = " SELECT 0 as record_type_code,sr.record_type_code as new_record_type_name,sr.record_code,sr.supplier_code,srd.sku,srd.num AS num,srd.money AS money,sr.remark,sr.record_time,sr.store_code "
                . "FROM pur_purchaser_record sr INNER JOIN pur_purchaser_record_detail srd ON sr.record_code = srd.record_code {$brand_join_sr} "
                . "WHERE 1 AND  sr.is_check_and_accept=1 ";
            $join_wr = " SELECT 1 as record_type_code,wr.record_type_code as new_record_type_name,wr.record_code,wr.supplier_code,wrd.sku,wrd.num AS num,wrd.money AS money,wr.remark,wr.record_time,wr.store_code "
                . "FROM pur_return_record wr INNER JOIN pur_return_record_detail wrd ON wr.record_code = wrd.record_code {$brand_join_wr} "
                . "WHERE 1 AND wr.is_store_out=1 ";
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
        //备注
        if (isset($filter['remark']) && $filter['remark'] !== '') {
            $join_sr .= " AND sr.remark LIKE :remark ";
            $join_wr .= " AND wr.remark LIKE :remark ";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
        }
        // 仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_values);
            $join_sr .= " AND sr.store_code IN ({$store_code_str}) ";
            $join_wr .= " AND wr.store_code IN ({$store_code_str}) ";
        }else{
            $filter_store_code = null;
            $join_sr .= load_model('base/StoreModel')->get_sql_purview_store('sr.store_code', $filter_store_code);
            $join_wr .= load_model('base/StoreModel')->get_sql_purview_store('wr.store_code', $filter_store_code);
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
        //供应商
        $supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $join_sr .= load_model('base/SupplierModel')->get_sql_purview_supplier('sr.supplier_code', $supplier_code);
        $join_wr .= load_model('base/SupplierModel')->get_sql_purview_supplier('wr.supplier_code', $supplier_code);
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] <> '') {
            $join_sr .= " AND srd.goods_code like :goods_code ";
            $join_wr .= " AND wrd.goods_code like :goods_code ";
            $sql_values[':goods_code'] = "%" . $filter['goods_code'] . "%";
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] <> '') {
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
        if ($type == 0) {
            $join_sr .= " GROUP BY sr.record_time,sr.supplier_code,srd.sku ";
            $join_wr .= " GROUP BY wr.record_time,wr.supplier_code,wrd.sku ";
        }
        $sql_main = " FROM (" . $join_sr . " UNION ALL " . $join_wr . ") AS t ";
    }

    private function  get_detail_key($val){
        return  $val['supplier_code'] . "," . $val['sku'];
    }
            
    
    function get_return_detail_data($filter,&$data,$key_arr){
        $sql_main = "";
        $sql_values = array();
        $this->get_detail_param_by_filter($filter,$sql_main,$sql_values,1);
        
//        $sku_str =  "'" . implode("','", $sku_arr) . "'";
//        $sql_main .=" AND r3.sku in ({$sku_str}) ";
//        
//        $supplier_str =  "'" . implode("','", $supplier_arr) . "'";
//        $sql_main .=" AND rl.supplier_code in ({$supplier_str}) ";

        $sql_main .="  GROUP BY rl.supplier_code,r3.sku ";
        $sql = " select  rl.supplier_code,rl.supplier_name,r3.sku,sum(r3.num) as return_num,sum(r3.money) as return_money ".$sql_main;
        $return_data = $this->db->get_all($sql,$sql_values);

        if(!empty($return_data)){
            foreach($return_data as $val){
                $val['return_num'] = empty($val['return_num'])?0:$val['return_num'];
                if($this->purchase_price_status == 1){
                    $val['return_money'] = empty($val['return_money'])?0:$val['return_money'];
                }else{
                    $val['return_money'] = '****';
                }
                $key = $this->get_detail_key($val);
                if(isset($key_arr[$key])){
                    $row =  &$data[$key_arr[$key]];
                    $row = array_merge($row,$val);
                }else{
                    $val['record_num'] = 0;
                    if($this->purchase_price_status == 1){
                        $val['record_money'] = 0;
                    }else{
                        $val['record_money'] = '****';
                    }



                    $key_a = array('spec1_code','spec1_name','spec2_code','spec2_name','barcode', 'goods_name','goods_code','brand_name','category_name','season_name','year_name','cost_price');
                    $sku_info =  load_model('goods/SkuCModel')->get_sku_info($val['sku'],$key_a);

                    $val = array_merge($val,$sku_info);   
                    $data[] = $val;
                }
            }
        }
     
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
     * 汇总统计
     * @param $filter
     * @return array|bool|mixed
     */
    function report_count($filter) {
        $sql_values = array();
        $sql_main = "";
        $this->get_pur_sql_by_filter($filter, $sql_values, $sql_main);
        $select = "SELECT sum(t.record_num) as record_num,sum(t.record_money) as record_money,sum(t.return_num) as return_num,sum(t.return_money) as return_money ";
        $sql_main .= " GROUP BY rl.supplier_code ";
        $sql = $select . $sql_main;
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as $key => &$val) {
            $val['record_num'] = empty($val['record_num']) ? 0 : $val['record_num'];
            $val['record_money'] = empty($val['record_money']) ? 0 : $val['record_money'];
            $val['return_num'] = empty($val['return_num']) ? 0 : $val['return_num'];;
            $val['return_money'] = empty($val['return_money']) ? 0 : $val['return_money'];;
        }

        //权限控制
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $count = array(
            'record_num_all' => array_sum(array_column($data, 'record_num')),
            'record_money_all' => ($status['status'] != 1) ? '****' : array_sum(array_column($data, 'record_money')),
            'return_num_all' => array_sum(array_column($data, 'return_num')),
            'return_money_all' => ($status['status'] != 1) ? '****' : array_sum(array_column($data, 'return_money')),
        );
        return $count;
    }

    /**
     * 详情页面采购商品数量总计/采购商品金额总计/退回商品数量总计/退货商品金额总计统计
     * @param $filter
     */
    public function detail_count($filter){
        $sql_main_sta = '';
        $sql_values_sta = array();
        $this->get_sql_by_filter($filter, $sql_main_sta, $sql_values_sta,2);
        $sql_main_sta .= "  WHERE 1 ";
        //单据类型
        if(isset($filter['record_type'])&&$filter['record_type']!=''){
            $sql_main_sta.=' and record_type_code = :record_type_code';
            $sql_values_sta[':record_type_code']=$filter['record_type'];
        }
        $select = "select sum(t.pur_num) AS pur_num,sum(t.pur_money) AS pur_money,sum(t.return_num) AS return_num,sum(t.return_money) AS return_money ";
        $sql = $select.$sql_main_sta;
        $detail_sta_arr = $this->db->get_row($sql,$sql_values_sta);
        //详情页面采购商品数量总计/采购商品金额总计/退回商品数量总计/退货商品金额总计统计
        if(!empty($detail_sta_arr)){
            foreach ($detail_sta_arr as &$value){
                $value = $value ? $value : 0;
            }
        }else{
            $detail_sta_arr = array('pur_num'=>0,'pur_money'=>0,'return_num'=>0,'return_money'=>0);
        }
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $filter['user_id']);
        if ($status['status'] != 1) {
            $detail_sta_arr['pur_money'] = '****';
            $detail_sta_arr['return_money'] = '****';
        }
        return $detail_sta_arr;
    }
}
