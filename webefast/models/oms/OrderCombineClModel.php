<?php

class OrderCombineClModel extends TbModel {

    function __construct() {
        parent::__construct();
    }

    //获取条件串
    function get_where_str($pre = '', $type = '') {
        return self::get_system_str($pre, $type) . $this->get_custom_str($pre);
    }

    //获取系统级参数条件串 分销商的暂时不支持合单
    static function get_system_str($pre, $type) {
        $wms_store = load_model('sys/WmsConfigModel')->get_wms_store_all();
        $str = '';
        if(empty($wms_store)){
            $str = "    AND {$pre}order_status <>3
                                    AND {$pre}shipping_status < 4 ";
        }else{
            $wms_store_str ="'".implode("','", $wms_store)."'";
            $store_where1 =  "   {$pre}order_status <>3
                                    AND {$pre}shipping_status < 1 AND  {$pre}store_code in({$wms_store_str}) ";
            
            
            $store_arr = self::get_no_wms_store_all($wms_store);
            $store_where2 = '';
            if(!empty($store_arr)){
                $store_str = "'".implode("','", $store_arr)."'";
                $store_where2 = "  {$pre}order_status <>3
                                        AND {$pre}shipping_status < 4   AND  {$pre}store_code in({$store_str}) ";
            }
            $str.=!empty($store_where2)? " AND (({$store_where1}) OR ({$store_where2}) ) ":" AND ".$store_where1;
        }
        
        $str .= " AND {$pre}pay_status = 2
        
                AND {$pre}is_pending = 0
                AND {$pre}pay_type != 'cod'
                               
                ";
        //  已打印（已打印快递单或发货都算已打印）的订单，控制不能合并订单，仅适用于自动合并
       $cfg_data = load_model('oms/OrderCombineStrategyModel')->get_val_by_code(array('order_outo_combine','order_combine_is_change', 'order_combine_is_split','order_combine_is_houtai','order_combine_is_taofx','order_combine_is_problem_reimburse','order_combine_is_problem','order_combine_is_short','order_combine_is_presell'));
           $condction_arr = array(
               
           'order_outo_combine' => array(
                1 => " AND {$pre}is_print_express !=1 AND {$pre}is_print_sellrecord !=1",
            ),
            'order_combine_is_change' => array(
                1 => " AND {$pre}is_change_record=0",
            ),
            'order_combine_is_split' => array(
                1 => " AND {$pre}is_split_new=0",
            ),
             'order_combine_is_houtai' => array(
                1 => " AND {$pre}sale_channel_code <>'houtai'",
            ),
            'order_combine_is_taofx' => array(
                1 => " AND ({$pre}is_fenxiao=0 OR({$pre}is_fenxiao = 2 AND {$pre}order_status = 0))",
            ),
//             'order_combine_is_problem' => array(
//                1 => " AND {$pre}is_problem=0",
//            ),
              'order_combine_is_short' => array(
                1 => " AND {$pre}lock_inv_status=1",
            ),
              'order_combine_is_presell' => array(
                1 => " AND {$pre}sale_mode<>'presale'",
            ),
//            'order_combine_is_problem_reimburse' => array(
//                1 => " AND {$pre}is_problem=0",
//            ),
        );
        $order_combine_is_problem_arr = array();
        foreach ($cfg_data as $key_code => $cfg) {
            if (($type == 'byhand' && $cfg['rule_status_value'] == 1) || ( $cfg['rule_scene_value'] == 1 && $type <> 'byhand')) {
                if($key_code=='order_combine_is_taofx'){
                   $str.=" AND ({$pre}is_fenxiao=0 OR ({$pre}is_fenxiao = 1 AND {$pre}is_fx_settlement=1) OR ({$pre}is_fenxiao = 2 AND {$pre}order_status = 0))";
                } else if ($key_code == 'order_combine_is_short') {
                    $str.=" AND {$pre}must_occupy_inv=1 AND {$pre}lock_inv_status<>0";
                } else if ($key_code == 'order_combine_is_problem') {
                    $order_combine_is_problem_arr[] = 'order_combine_is_problem';
                } else if ($key_code == 'order_combine_is_problem_reimburse') {
                     $order_combine_is_problem_arr[] = 'order_combine_is_problem_reimburse';
                }
            } else {
                $str .= isset($condction_arr[$key_code][1]) ? $condction_arr[$key_code][1] : '';
            }
        }
        if (in_array('order_combine_is_problem_reimburse', $order_combine_is_problem_arr)) {

            if (in_array('order_combine_is_problem', $order_combine_is_problem_arr)) {
                $refund_arr = load_model('oms/SellRecordTagModel')->get_problem_full_refund(); //不包含全退
                $refund_str = "'" . implode("','", $refund_arr) . "'";
                $str .= " AND ({$pre}sell_record_code NOT IN({$refund_str}) )";
            
            }else{
                  $refund_arr = load_model('oms/SellRecordTagModel')->get_problem_part_refund(); //部分退
                  $refund_str = "'" . implode("','", $refund_arr) . "'";
                  $str .= " AND ({$pre}sell_record_code  IN({$refund_str}) OR   {$pre}is_problem=0)";
            }

        } else if (in_array('order_combine_is_problem', $order_combine_is_problem_arr)) {
            $refund_arr = load_model('oms/SellRecordTagModel')->get_problem_refund(); //包含退款，全退和部分退
            $refund_str = "'" . implode("','", $refund_arr) . "'";
            $str .= " AND {$pre}sell_record_code NOT IN({$refund_str})";
        }else{
             $str .=" AND {$pre}is_problem=0";
        }


        return $str;
    }
    static function get_no_wms_store_all($wms_store){
        $where = " 1 ";
        if(!empty($wms_store)){
            $store_str = "'".implode("','", $wms_store)."'";
            $where.=" AND store_code not in({$store_str})";
        }

        $sql = "select store_code from base_store where {$where}";

        $data = CTX()->db->get_all($sql);
        $store_arr = array();
        foreach($data as $val){
            $store_arr[] = $val['store_code'];
        }
        return $store_arr;

    }

    //获取自定义级参数条件串
    function get_custom_str($pre) {
        $sql = "select sale_channel_code from base_sale_channel where is_active = 1";
        $sale_channel_code_arr = ctx()->db->get_all_col($sql);
        $unset_code = array( 'ncm','jingdong', 'openshop', 'yougou', 'vja');
        $sale_channel_code_arr = array_diff($sale_channel_code_arr, $unset_code);
        $sale_channel_code_list = "'" . join("','", $sale_channel_code_arr) . "'";
        
        $jd_shop = $this->get_jd_sop_shop();
        if(empty($jd_shop)){
            $str = " and {$pre}sale_channel_code in({$sale_channel_code_list})";
        }else{
            $shop_str =  "'" . join("','", $jd_shop) . "'";
            $str = " and  ( {$pre}sale_channel_code in({$sale_channel_code_list}) OR {$pre}shop_code in({$shop_str}) )";
        }

        return $str;
    }
    
    function get_jd_sop_shop(){
        $sql = "select s.shop_code,a.api from base_shop s 
            INNER JOIN base_shop_api a ON s.shop_code = s.shop_code 
            where s.is_active=1 AND s.sale_channel_code='jingdong'
            ";
        $data = $this->db->get_all($sql);
        $shop_arr = array();
        foreach($data as $val){//{"type":"sop",
            if(!empty($val['api'])){
                $api = json_decode($val['api'],TRUE);
                if(isset($api['type'])&&$api['type']=='sop'){
                    $shop_arr[] = $val['shop_code'];
                }
            }
        }
        return $shop_arr;
    }

}
