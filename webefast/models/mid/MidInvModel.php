<?php

require_model('tb/TbModel');

class MidInvModel extends TbModel {

    private $adjust_inv_api_product = null;

    function sync_inv() {


        $flow_type = 'to_sys';
        $record_type = 'inv';
        $data = load_model('mid/MidBaseModel')->check_flow($flow_type, $record_type);

        if (!empty($data)) {
            $data = load_model('mid/MidApiConfigModel')->get_mid_api_config_by_api_product($data['api_product']);
            foreach ($data as $val) {
                $this->download_api_inv($val);
            }
        }
    }

    function download_api_inv($api_data) {

        //, $api_store_data
        $down_type = array(
            'bserp2' => 'time', //通过增量时间下载
        );

        $function = 'download_api_inv_' . $down_type[$api_data['api_product']];
        $api_store_all = load_model('mid/MidApiConfigModel')->get_join_data($api_data['mid_code']);

        foreach ($api_store_all as $val) {
            $ret = $this->$function($api_data, $val);
        }
    }

    function download_api_inv_time($api_data, $api_store_info) {
        $adjust_inv_by_record = array(
            'bserp2',
        );
        $adjust_inv_api_product = in_array($api_data['api_product'], $adjust_inv_by_record) ? $api_data['api_product'] : null;

        $mod = $this->get_api_product_mod($api_data['api_product'], $api_data);
        $param = array(
            'page' => 1,
            'page_size' => 100,
            'api_store_code' => $api_store_info['outside_code'],
            'sys_store_code' => $api_store_info['join_sys_code'],
        );

        while (true) {
            $ret = $mod->sys_inv($param);

            if ($ret['status'] < 1) {
                break;
            }
            if (!empty($ret['data'])) {
                $update_str = " num = VALUES(num),is_sync = VALUES(is_sync),down_time = VALUES(down_time)";
                $this->insert_multi_duplicate('mid_goods_inv', $ret['data'], $update_str);
            } else {
                break;
            }
            if(count($ret['data'])<$param['page_size']){
                break;
            }
            $param['page'] = $param['page'] + 1;
        }
        $inv_num = 0;
        while(true){
             $inv_num =   $this->sync_inv_to_sys($api_store_info['join_sys_code'], $adjust_inv_api_product,1000);
             if($inv_num<>1000){
                 break;
             }
        }
     

        return $ret;
    }

    function sync_inv_to_sys($store_code, $adjust_inv_api_product,$max_num =1000) {

        //todo: ERP需要扣减未上传单据库存数量
        // 需要先完善类型

        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();

        $default_lof_no = $ret_lof['data']['lof_no'];
        $default_lof_production_date = $ret_lof['data']['production_date'];
        $sql = "select * from  mid_goods_inv where (sync_time<down_time) OR is_sync=1 limit {$max_num} ";
        $data = $this->db->get_all($sql);
      
        if (empty($data)) {
            return false;
        }

        $inv_num = count($data);
        $up_inv_arr = array();
        $lof_inv_arr = array();
        $inv_record_log = array();

        $inv_key_arr = array();
        $now_date = date('Y-m-d H:i:s');
        $num_data  = array();
     
        if(!empty($adjust_inv_api_product)){
            $num_data = $this->get_no_upload_num($store_code, $adjust_inv_api_product);
        } 
        

        
        //todo:扣减发货单据未上传的
        foreach ($data as $sub_sku) {
            $inv_key = $this->inv_key($sub_sku);
            $inv_key_arr[$inv_key] = " ( store_code='{$sub_sku['store_code']}' AND sku='{$sub_sku['sku']}' ) ";
            $sub_sku['num'] = isset($num_data[$inv_key])?$num_data[$inv_key]+$sub_sku['num']:$sub_sku['num'];

            $up_inv_arr[$inv_key] = array('store_code' => $sub_sku['store_code'],
                'goods_code' => $sub_sku['goods_code'],
                'spec1_code' => $sub_sku['spec1_code'],
                'spec2_code' => $sub_sku['spec2_code'],
                'sku' => $sub_sku['sku'],
                'stock_num' => $sub_sku['num'],
                'record_time' => $now_date,
            );
            $lof_inv_arr[$inv_key] = array('store_code' => $sub_sku['store_code'],
                'goods_code' => $sub_sku['goods_code'],
                'spec1_code' => $sub_sku['spec1_code'],
                'spec2_code' => $sub_sku['spec2_code'],
                'sku' => $sub_sku['sku'],
                'stock_num' => $sub_sku['num'],
                'lof_no' => $default_lof_no,
                'production_date' => $default_lof_production_date,
            );
            
            
            $remark = '实物增加';
            $inv_record_log[$inv_key] = array(
                'store_code' => $sub_sku['store_code'],
                'goods_code' => $sub_sku['goods_code'],
                'spec1_code' => $sub_sku['spec1_code'],
                'spec2_code' => $sub_sku['spec2_code'],
                'sku' => $sub_sku['sku'],
                'barcode' => isset($sub_sku['barcode'])?$sub_sku['barcode']:'',
                'production_date' => $default_lof_production_date,
                'lof_no' => $default_lof_no,
                'occupy_type' => 3,
                'stock_change_num' => $sub_sku['num'],
                'stock_lof_num_before_change' => 0,
                'stock_lof_num_after_change' => $sub_sku['num'],
                'stock_num_before_change' => 0,
                'stock_num_after_change' => $sub_sku['num'],
                'lock_num_before_change' => 0,
                'lock_num_after_change' => 0,
                'lock_lof_num_before_change' => 0,
                'lock_lof_num_after_change' => 0,
                'record_time' => $now_date,
                'relation_code' => '外部接口调整',
                'relation_type' => 'adjust',
                'remark' => $remark
            );
        }
        //对比系库存
        $this->set_inv_data($up_inv_arr, $lof_inv_arr, $inv_record_log, $inv_key_arr);

        if (!empty($up_inv_arr)) {

            $update_inv_str = ' stock_num = VALUES(stock_num) ';
            $this->insert_multi_duplicate('goods_inv', $up_inv_arr, $update_inv_str);

            $update_lof_str = '  stock_num = VALUES(stock_num)  ';
            $this->insert_multi_duplicate('goods_inv_lof', $lof_inv_arr, $update_lof_str);


            $this->insert_multi_exp('goods_inv_record', $inv_record_log);


        }
        if(!empty($inv_key_arr)){
            $sql = "update  mid_goods_inv set  sync_time = '{$now_date}' ,is_sync =0 where ";
            $sql.=implode(" OR ", $inv_key_arr);
            $this->db->query($sql);
        }
       
        return $inv_num;
    }
    
    function get_sku_arr(&$data){
        $sku_arr = array();
        foreach($data as $val){
            $sku_arr[] = $val['sku'];
        }
        return $sku_arr;
    }

    function get_no_upload_num($store_code, $api_product) {

        $mid_sql = "SELECT c.online_time,c.api_param_json,c.mid_code
           FROM mid_api_config c INNER JOIN mid_api_join j ON c.mid_code =j.mid_code
            WHERE c.api_product=:api_product AND j.join_sys_code=:store_code
           ";
        $config_data = $this->db->get_row($mid_sql, array(":api_product" => $api_product, ":store_code" => $store_code));
        $online_time = $config_data['online_time'];
        $mid_config = json_decode($config_data['api_param_json'], true);

        $this->sys_to_mid();
        $connection_mode = isset($mid_config['connection_mode']) ? $mid_config['connection_mode'] : 1;
        //获取上线日期
        $mid_code = $config_data['mid_code'];



        $sql = "select record_code,record_type from mid_order where efast_store_code=:efast_store_code AND api_product=:api_product  AND upload_response_flag<>10";
        $sql_values = array(
            ':efast_store_code' => $store_code,
            ':api_product' => $api_product,
        );
        $record_data = $this->db->get_all($sql, $sql_values);
        $record_arr = array();
        foreach ($record_data as $val) {
            $record_arr[$val['record_type']][] = $val['record_code'];
        }
        $num_data = array();

        if (isset($record_arr['sell_record'])) {
            $record_code_str = "'" . implode("','", $record_arr['sell_record']) . "'";
            $sql = "select sum(num) as num,sku,store_code  from oms_sell_record_lof where record_type=1 AND record_code in($record_code_str) GROUP BY store_code,sku ";
            $sell_record_data = $this->db->get_all($sql);
            $this->set_data_num($num_data, $sell_record_data, -1);
            unset($record_arr['sell_record']);
        }

        if (isset($record_arr['sell_return'])) {
            $record_code_str = "'" . implode("','", $record_arr['sell_return']) . "'";
            $sql = "select sum(recv_num) as num,sku,'{$store_code}' as store_code  from oms_sell_return_detail where sell_return_code in($record_code_str) GROUP BY sku ";
            $sell_return_data = $this->db->get_all($sql);
            $this->set_data_num($num_data, $sell_return_data, 1);
            unset($record_arr['sell_return']);
        }

        if ($connection_mode == 2) {

            if (isset($record_arr['sell_record_rb'])) {
                $sell_record_rb_sql_value = array();
                $sell_record_rb_str = $this->arr_to_in_sql_value($record_arr['sell_record_rb'], 'record_code', $sell_record_rb_sql_value);
                $sql = "SELECT sum(d.num) as num, d.sku, t.store_code FROM bsapi_trade AS t, bsapi_trade_detail AS d where t.record_code=d.record_code AND t.record_code IN({$sell_record_rb_str}) GROUP BY d.sku";
                $sell_record_data = $this->db->get_all($sql, $sell_record_rb_sql_value);
                $this->set_data_num($num_data, $sell_record_data, -1);
                unset($record_arr['sell_record_rb']);
            }

            if (isset($record_arr['sell_return_rb'])) {
                $sell_return_rb_sql_value = array();
                $sell_return_rb_str = $this->arr_to_in_sql_value($record_arr['sell_return_rb'], 'record_code', $sell_return_rb_sql_value);
                $sql = "SELECT sum(d.num) as num, d.sku, t.store_code FROM bsapi_trade AS t, bsapi_trade_detail AS d where t.record_code=d.record_code AND t.record_code IN({$sell_return_rb_str}) GROUP BY d.sku";
                $sell_return_data = $this->db->get_all($sql, $sell_return_rb_sql_value);
                $this->set_data_num($num_data, $sell_return_data, 1);
                unset($record_arr['sell_return_rb']);
            }

            //获取配置的店铺数据
            $record_date_arr = array();

            $record_date_sell_record_sql = "select max(record_date) from bsapi_trade  where record_type='sell_record' AND store_code=:store_code";
            $record_date_arr['sell_record'] = $this->db->get_value($record_date_sell_record_sql, array(":store_code" => $store_code));

            $record_date_sell_return_sq1 = "select max(record_date) from bsapi_trade  where record_type='sell_return' AND store_code=:store_code";
            $record_date_arr['sell_return'] = $this->db->get_value($record_date_sell_return_sq1, array(":store_code" => $store_code));


            //未生成日报的
            $uncreate_rb_arr = array('sell_return' => 1, 'sell_record' => -1);

            $join_sys_data = $this->db->get_all("select join_sys_code from mid_api_join  where mid_code=:mid_code AND join_sys_type=0 ", array(':mid_code' => $mid_code));
            $shop_code_arr = array_column($join_sys_data, 'join_sys_code');

            foreach ($uncreate_rb_arr as $record_type => $val) {
                $record_date = !empty($record_date_arr[$record_type]) ? $record_date_arr[$record_type] : date('Y-m-d', strtotime($online_time));
                $order_data = $this->get_record_data($record_type, $shop_code_arr, $store_code, $record_date);
                $this->set_data_num($num_data, $order_data, $val);
            }
        }


        foreach ($record_arr as $order_type => $record_code_arr) {

            $record_code_str = "'" . implode("','", $record_code_arr) . "'";
            $order_type = $val['order_type'];
            $sql = "select  sum( if(occupy_type=2,-1*num,num) )as num,sku, store_code  from b2b_lof_datail where order_type='{$order_type}'  AND order_code in($record_code_str)   GROUP BY sku";
            $order_data = $this->db->get_all($sql);
            $this->set_data_num($num_data, $order_data);
        }

        return $num_data;
    }

    private function set_data_num(&$num_data, $record_data, $type = -1) {


        foreach ($record_data as $val) {
            $key = $this->inv_key($val);
            if (isset($num_data[$key])) {
                $num_data[$key] += $type * $val['num'];
            } else {
                $num_data[$key] = $type * $val['num'];
            }
        }
    }

    /**
     * 获取未生成日报的单据的sku和库存数
     */
//    public function get_uncreate_data($type, $store_code, $config_data, $record_date) {
//        //获取最后一次生成的日报的业务日期，如果没有，取上线日期
//        $sql = "SELECT record_date FROM bsapi_trade WHERE record_type = :record_type ORDER BY record_date DESC";
//        $record_date  = $this->db->get_value($sql, array(":record_type" => $type));
//        $record_date_arr = array();
//        $record_date_arr['record_date_end'] = date('Y-m-d H:i:s');
//        if(!empty($record_date) && $record_date != '0000-00-00' && $record_date > $online_time) {
//            //业务日期后一天
//            $record_date_arr['record_date_start'] = date('Y-m-d 00:00:00', strtotime($record_date) + 24 *3600);
//            return $this->build_uncreate_data($type, $config_data, $store_code, $record_date_arr);
//        } else {
//            //上线日期当天
//            $record_date_arr['record_date_start'] = $online_time;
//            return $this->build_uncreate_data($type, $config_data, $store_code, $record_date_arr);
//        }
//        
//    }
    
//    /**
//     * 创建数据
//     */
//    private function build_uncreate_data($type, $config_data, $store_code, $record_date_arr) {
//        $shop_arr = array_column($config_data, 'join_sys_code');
//        foreach ($shop_arr as $shop_code) {
//            $total_data[] = $this->get_record_data($type, $shop_code, $store_code, $record_date_arr);
//        }
//        return $total_data;
//    }
    
    /**
     * 获取数据
     */
    private function get_record_data ($type, $shop_code_arr, $store_code, $record_date){
        
        $shop_code_str = "'".implode("','", $shop_code_arr)."'";
        if ($type == 'sell_record') {
            $sql = "SELECT
                        rd.sku,
                        sum(rd.num) AS num,
                        sr.store_code
                    FROM
                        oms_sell_record AS sr
                    INNER JOIN oms_sell_record_detail AS rd ON sr.sell_record_code = rd.sell_record_code
                    WHERE
                        sr.shipping_status = 4
                    AND sr.delivery_date >:record_date_start

                     AND sr.shop_code in ({$shop_code_str})
                    AND sr.store_code =:store_code
                    GROUP BY
                        rd.sku
                    ORDER BY
                        sku";
        } else {
            $sql = "SELECT
                        rd.sku,
                        sum(rd.recv_num) AS num,
                        sr.store_code
                    FROM
                        oms_sell_return AS sr
                    INNER JOIN oms_sell_return_detail AS rd ON sr.sell_return_code = rd.sell_return_code
                    WHERE
                        sr.return_shipping_status = 1
                    AND sr.stock_date >:record_date_start
          
                    AND sr.shop_code in ({$shop_code_str})
                    AND sr.store_code =:store_code
                    GROUP BY
                        rd.sku
                    ORDER BY
                        sku";
        }
        $sql_values = array('record_date_start' => $record_date, 'store_code' => $store_code);

        $data = $this->db->get_all($sql, $sql_values);
        return $data;
    }
   
    
    private function set_inv_data(&$up_inv_arr, &$lof_inv_arr, &$inv_record_log, &$inv_key_arr) {
        if (empty($inv_key_arr)) {
            return false;
        }
        $sql = "select * from goods_inv where ";
        $sql.=implode(" OR ", $inv_key_arr);
        $data = $this->db->getAll($sql);
        foreach ($data as $val) {
            $inv_key = $this->inv_key($val);
            if ($val['stock_num'] == $up_inv_arr[$inv_key]['stock_num']) {
                unset($up_inv_arr[$inv_key]);
                unset($lof_inv_arr[$inv_key]);
                unset($inv_record_log[$inv_key]);
                unset($inv_record_log[$inv_key]);
            } else {

                $stock_change_num = $up_inv_arr[$inv_key]['stock_num'] - $val['stock_num'];
                $remark = $stock_change_num > 0 ? '实物增加' : '实物扣减';
                $inv_record_log[$inv_key]['remark'] = $remark;


                $inv_record_log[$inv_key]['stock_change_num'] = abs($stock_change_num);
                $inv_record_log[$inv_key]['stock_lof_num_before_change'] = $val['stock_num'];
                $inv_record_log[$inv_key]['stock_num_before_change'] = $val['stock_num'];
                $inv_record_log[$inv_key]['lock_num_before_change'] = $val['lock_num'];
                $inv_record_log[$inv_key]['lock_num_after_change'] = $val['lock_num'];
                $inv_record_log[$inv_key]['lock_lof_num_before_change'] = $val['lock_num'];
                $inv_record_log[$inv_key]['lock_lof_num_after_change'] = $val['lock_num'];
            }
        }
    }

    private function inv_key($row) {
        return $row['store_code'] . "|" . $row['sku'];
    }

    function get_api_product_mod($api_product, $api_data) {

        static $api_mod_arr = array();

        if (!isset($api_mod_arr[$api_data['mid_code']])) {
            $api_mod_name = ucfirst($api_product) . "InvModel";
            $mod_path = 'mid/' . $api_product . '/' . $api_mod_name;

            require_model($mod_path);

            $api_mod_arr[$api_data['mid_code']] = new $api_mod_name($this, $api_data);
        }
        return $api_mod_arr[$api_data['mid_code']];
    }
    
    /**
     * @todo 获取erp配置
     */
    function cli_upload_lock_inv() {
        $data = load_model('mid/MidApiConfigModel')->get_mid_api_config_by_api_product('bserp2');
        foreach ($data as $val) {
            $this->upload_lock_inv($val);
        }
    }

    function upload_lock_inv($api_data) {
        $down_type = array(
            'bserp2' => 'time',
        );
        $function = 'upload_lock_inv_' . $down_type[$api_data['api_product']];
        $api_store_all = load_model('mid/MidApiConfigModel')->get_join_data($api_data['mid_code']);
        foreach ($api_store_all as $val) {
            $ret = $this->$function($api_data, $val);
        }
    }
    
    /**
     * @todo 销售订单锁定库存上传到ERP
     */
    function upload_lock_inv_time($api_data, $api_store_info) {
        $inv_data =   $this->get_sync_lock_inv($api_store_info['join_sys_code']);
        $mod = $this->get_api_product_mod($api_data['api_product'], $api_data);
        $ret = $mod->upload_lock_inv($inv_data, $api_store_info['outside_code']);
        return $ret;
    }

    /**
     * @todo 获取销售订单锁定库存
     */
    function get_sync_lock_inv($store_code) {
        $inv_sql = "SELECT sku,lock_num FROM goods_inv WHERE store_code=:store_code AND lock_num!=0";
        $inv_sql_values = array(":store_code" => $store_code);
        $data = $this->db->get_all($inv_sql, $inv_sql_values);
        foreach ($data as &$value) {
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'price', 'cost_price', 'goods_code');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($value, $sku_info);
        }
        return $data;
    }
    
}
