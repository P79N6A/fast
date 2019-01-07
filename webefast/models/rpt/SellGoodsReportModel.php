<?php
require_lib('util/oms_util', true);
require_model('tb/TbModel');

class SellGoodsReportModel extends TbModel
{
    //商品编码
    function trends_goods_code($filter)
    {
        $filter['group_by'] = 'rd.goods_code';
        if (isset($filter['keyword']) && $filter['keyword'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }

        $arr = $this->get_by_page_sql($filter);
        $arr['select'] = 'rd.goods_code, sum(rd.num) num, sum(rd.avg_money) avg_money';
        $sql = $arr['from'] . $arr['join'] . $arr['where'] . $arr['group_by'] . $arr['order_by'];
             // var_dump( $sql, $arr['params'], $arr['select']);die;
        return $this->get_by_page_1($filter, $sql, $arr['params'], $arr['select']);
    }

    //商品编码
    function trends_goods_code_count($filter){
        return array();
    }

    //商品条形码
    function trends_goods_barcode($filter)
    {
        $filter['group_by'] = 'rd.sku';
        if (isset($filter['keyword']) && $filter['keyword'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        
        $arr = $this->get_by_page_sql($filter);
        $arr['select'] = 'rd.goods_code, rd.sku, sum(rd.num) num, sum(rd.avg_money) avg_money';
        $sql = $arr['from'] . $arr['join'] . $arr['where'] . $arr['group_by'] . $arr['order_by'];
  
        return $this->get_by_page_1($filter, $sql, $arr['params'], $arr['select']);
    }

    //商品条形码
    function trends_goods_barcode_count($filter){
        return array();
    }

    /**
     * @param $filter
     * @param $sql
     * @param $params
     * @param $select
     * @return array
     */
    public function get_by_page_1($filter, $sql, $params, $select){
        $data =  $this->get_page_from_sql($filter, $sql, $params, $select, true);
        $this->get_by_page_parse($data['data']);
        return $this->format_ret(1, $data);
    }

    /**
     * @param $filter
     * @return array
     * @throws Exception
     */
    public function get_by_page_sql($filter){
        $select = 'rd.*';
        $from = "FROM oms_sell_record_detail rd ";
        $join = 'INNER JOIN oms_sell_record r ON r.sell_record_code = rd.sell_record_code';
        $where = ' WHERE 1 ';
        $params = array();

        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $where .= load_model('base/StoreModel')->get_sql_purview_store('r.store_code',$filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $where .= load_model('base/ShopModel')->get_sql_purview_shop('r.shop_code',$filter_shop_code);

        $where .= " AND r.order_status <> 3 ";
        
        if(isset($filter['shipping_flag'])){
            if($filter['shipping_flag'] == '1'){
                $where .= " AND r.shipping_status = 4 ";
            }
            if($filter['shipping_flag'] == '0'){
                $where .= " AND r.shipping_status < 4 ";
            }
        } else {
            $where .= " AND r.shipping_status = 4 ";
        }
        //发货状态
        if (isset($filter['shipping_status']) && $filter['shipping_status'] !== '') {
            $where .= " AND r.shipping_status = :shipping_status ";
            $params[':shipping_status'] = $filter['shipping_status'];
        }
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
            $arr = explode(',', $filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $params);
            $where .= " AND r.sale_channel_code in ( ".$str." ) ";
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $filter['goods_code'] = trim($filter['goods_code']);
            $where .= " AND rd.goods_code like :goods_code ";
            $params[':goods_code'] = '%'.$filter['goods_code'].'%';
        }
        //套餐条形码
        if (isset($filter['combo_sku']) && $filter['combo_sku'] !== '') {
            $filter['combo_sku'] = trim($filter['combo_sku']);   
            $combo_sku_arr = load_model('prm/GoodsComboModel')->get_combo_sku_by_barcode($filter['combo_sku']);
            if (empty($combo_sku_arr)) {
                $where .= " AND 1=2";
            } else {
                $key='combo_sku';
                $combo_sku_str=$this->arr_to_in_sql_value($combo_sku_arr, $key, $params);
                $where .= " AND rd.combo_sku in({$combo_sku_str}) ";
            }
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
//            $where .= " AND rd.barcode LIKE :barcode ";
//            $params[':barcode'] = "%" . $filter['barcode'] . "%";
                $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
                            if(empty($sku_arr)){
                                   $where .= " AND 1=2";
                            }else{
                                $sku_str = "'".implode("','", $sku_arr)."'";
                                $where .= " AND rd.sku in({$sku_str}) ";
                            }    
			     
            
        }
        //下单时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] !== '') {
            $where .= " AND r.record_time >= :record_time_start ";
            $record_time_start = strtotime(date("Y-m-d", strtotime($filter['record_time_start'])));
            if ($record_time_start == strtotime($filter['record_time_start'])) {
                $params[':record_time_start'] = $filter['record_time_start'] . ' 00:00:00';
            } else {
                $params[':record_time_start'] = $filter['record_time_start'];
            }
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] !== '') {
            $where .= " AND r.record_time <= :record_time_end ";
            $record_time_end = strtotime(date("Y-m-d", strtotime($filter['record_time_end'])));
            if ($record_time_end == strtotime($filter['record_time_end'])) {
                $params[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
            } else {
                $params[':record_time_end'] = $filter['record_time_end'];
            }
        }
        //付款时间
        if (isset($filter['pay_time_start']) && $filter['pay_time_start'] !== '') {
            $where .= " AND r.pay_time >= :pay_time_start ";
            $pay_time_start = strtotime(date("Y-m-d", strtotime($filter['pay_time_start'])));
            if ($pay_time_start == strtotime($filter['record_time_start'])) {
                $params[':pay_time_start'] = $filter['pay_time_start'] . ' 00:00:00';
            } else {
                $params[':pay_time_start'] = $filter['pay_time_start'];
            }
        }
        if (isset($filter['pay_time_end']) && $filter['pay_time_end'] !== '') {
            $where .= " AND r.pay_time <= :pay_time_end ";
            $pay_time_end = strtotime(date("Y-m-d", strtotime($filter['pay_time_end'])));
            if ($pay_time_end == strtotime($filter['pay_time_end'])) {
                $params[':pay_time_end'] = $filter['pay_time_end'] . ' 23:59:59';
            } else {
                $params[':pay_time_end'] = $filter['pay_time_end'];
            }
        }
        //发货时间
        if (!empty($filter['send_time_start'])) {
            $where .= " AND r.delivery_time >= :send_time_start ";
            $send_time_start = strtotime(date("Y-m-d", strtotime($filter['send_time_start'])));
            if ($send_time_start == strtotime($filter['send_time_start'])) {
                $params[':send_time_start'] = $filter['send_time_start'] . ' 00:00:00';
            } else {
                $params[':send_time_start'] = $filter['send_time_start'];
            }
        }
        if (!empty($filter['send_time_end'])) {
            $where .= " AND r.delivery_time <= :send_time_end ";
            $send_time_end = strtotime(date("Y-m-d", strtotime($filter['send_time_end'])));
            if ($send_time_end == strtotime($filter['send_time_end'])) {
                $params[':send_time_end'] = $filter['send_time_end'] . ' 23:59:59';
            } else {
                $params[':send_time_end'] = $filter['send_time_end'];
            }
        }
        $group_by = '';
        if(!empty($filter['group_by'])){
            $group_by = " group by ".$filter['group_by']." ";
        }

        $order_by = " ORDER BY sum(rd.num) DESC ";
        //var_dump($filter,$select,$where,$params);

        return array(
            'select'=>$select,
            'from'=>$from,
            'join'=>$join,
            'where'=>$where,
            'group_by'=>$group_by,
            'order_by'=>$order_by,
            'params'=>$params
        );
    }

    /**
     * @param $data
     */
    public function get_by_page_parse(&$data){
        foreach($data as $key => &$value){
//            $value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code'=>$value['goods_code']));
//            if(isset($value['spec1_code']))
//                $value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code'=>$value['spec1_code']));
//            if(isset($value['spec2_code']))
//                $value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code'=>$value['spec2_code']));
            
            if(isset($value['sku'])){
                        $key_arr = array('barcode','spec1_code','spec2_code','spec1_name','spec2_name','goods_name');
             $sku_info =  load_model('goods/SkuCModel')->get_sku_info($value['sku'],$key_arr);
             $value = array_merge($value,$sku_info);
 
            }else{
              $key_arr = array('goods_name');
              $goods_info =  load_model('goods/GoodsCModel')->get_goods_info($value['goods_code'],$key_arr);  
              $value = array_merge($value,$goods_info);
            }
            
            
        }
    }
    
    function create_inventory_compare(){
        set_time_limit(0);
        $sql = "select store_code from goods_unique_code_tl where status = 0 group by store_code";
        $data = $this->db->get_all($sql);
        if(!empty($data)){
             foreach ($data as $value) {
                $this->insert_compare($value['store_code']);
            }
        }  
    }
    
    function insert_compare($store_code){
        $compare_code = $store_code . '_' . date('Ymd');
//        $sql = "select * from rpt_inv_compare where compare_code=:compare_code AND  store_code=:store_code";
//        $row = $this->db->get_row($sql, array(':compare_code' => $compare_code,':store_code'=>$store_code));
//        if (!empty($row)) {
//            return $this->format_ret(-1, '', '已经生成！');
//        }
        $compare_time = date('Y-m-d H:i:s', time());
        $sql = "select count(1) as unique_num from goods_unique_code_tl where status = 0 and store_code = :store_code";
        $num_list = $this->db->getRow($sql, array(':store_code'=>$store_code));
        $unique_num = $num_list['unique_num'];
        $compare_data = array(
            'compare_code' => $compare_code,
            'store_code' => $store_code,
            'compare_time' => $compare_time,
            'unique_num' => $unique_num, //唯一码可用总数
        );
        //$day_time = date('Y-m-d', strtotime("-1 day"))." 00:00:00";
        //$sql_insert = "insert ignore into rpt_inv_compare_detail (compare_code,compare_time,unique_num,barcode,store_code,sku,sys_num)";   
        $sql_insert = "select s.barcode,s.sku,cast(i.stock_num as signed)-cast(i.lock_num as signed) as sys_num from goods_inv i 
            inner join goods_sku s on s.sku = i.sku 
            where i.store_code = :store_code group by i.sku ";
        
        $result = $this->db->getAll($sql_insert, array(':store_code' =>$store_code));
        $sql_un = "select count(1) as unique_num,sku,barcode from goods_unique_code_tl where store_code = :store_code and status = 0 group by sku";
        $unique_list = $this->db->getAll($sql_un, array(':store_code' =>$store_code));
        $uniuqe_nu= array_column($unique_list, 'unique_num', 'sku');//sku=>unique_num
        $uniuqe_bar = array_column($unique_list, 'barcode', 'sku');//sku=>barcode
        $new_arr = array();
        foreach ($result as $k => $v) {
            $new_arr[$k]['compare_code'] = $compare_code;
            $new_arr[$k]['store_code'] = $store_code;
            $new_arr[$k]['compare_time'] = $compare_time;
            $new_arr[$k]['barcode'] = $v['barcode'];
            $new_arr[$k]['sku'] = $v['sku'];
            $new_arr[$k]['sys_num'] = $v['sys_num'];
            if(array_key_exists($v['sku'],$uniuqe_nu)){
                $new_arr[$k]['unique_num'] = $uniuqe_nu[$v['sku']];
                unset($uniuqe_nu[$v['sku']]);
                unset($uniuqe_bar[$v['sku']]);
            } else {
                $new_arr[$k]['unique_num'] = 0;
            }
        }
        $list = [
            'compare_code' => $compare_code,
            'store_code' => $store_code,
            'compare_time' => $compare_time,
        ];
        foreach ($uniuqe_nu as $k => $v){
             $list['sku'] = $k;
             $list['unique_num'] = $v;
             $list['barcode'] = $uniuqe_bar[$k];
             $list['sys_num'] = 0;
             $new_arr[] = $list;
        }
        $up_str = 'unique_num=VALUES(unique_num),sys_num=VALUES(sys_num),compare_time=VALUES(compare_time)';
        $this->insert_multi_duplicate('rpt_inv_compare_detail',$new_arr,$up_str);
        
        $sql_num = "select sum(sys_num) as inventory_sku_num,sum(unique_num) as unique_num,(sum(unique_num)-sum(sys_num)) as compare_num from rpt_inv_compare_detail where compare_code = :compare_code";
        $data = $this->db->getRow($sql_num, array(':compare_code' => $compare_code));
        if(!empty($data)){
            $compare_data = array_merge($compare_data, $data);
            $compare_data['compare_num'] = is_null($compare_data['compare_num'])?0:$compare_data['compare_num'];
        }
        $up = 'inventory_sku_num=VALUES(inventory_sku_num),unique_num=VALUES(unique_num),compare_num=VALUES(compare_num),compare_time=VALUES(compare_time)';
        $this->insert_multi_duplicate('rpt_inv_compare', array($compare_data),$up);
        return $this->format_ret(1);
    }




    //库存差异对比报表
    function get_compare_list($filter){
        $sql_main = "FROM rpt_inv_compare  WHERE 1 ";
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('store_code', $filter_store_code);
        $sql_values = array();
        $select = '*';
        $sql_main.=" ORDER BY compare_time DESC";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $sql = "SELECT store_name,store_code from base_store";
        $store_data = $this->db->getAll($sql);
        $arr = array();
        foreach ($store_data as $value) {
            $arr[$value['store_code']] = $value['store_name'];
        }
        foreach ($data['data'] as &$val) {
            $val['store_name'] = $arr[$val['store_code']]; 
        }
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    //下载对照报表
    function down_compare_data($compare_code,$store_code){
        $diff = array();
        $sql = "select DISTINCT r1.* from rpt_inv_compare_detail r1
                inner join goods_unique_code_tl r2 on r1.sku = r2.sku
                where r1.compare_code = :compare_code AND  r1.store_code=:store_code";
        $data = $this->db->get_all($sql, array(':compare_code' => $compare_code,':store_code'=>$store_code));
        foreach ($data as &$value) {
           $value['diff_num'] = $value['unique_num'] - $value['sys_num'];
        }
        $hear_data = array('compare_time' => '对照时间', 'barcode' => '商品条形码', 'store_name' => '系统仓库','unique_num' => '可用唯一码可用数', 'sys_num' => '在库库存数','diff_num' => '差异库存总数');
        $content_csv = array();
        $hear_str = implode(',', $hear_data);
        $content_csv[] = iconv('utf-8', 'gbk', $hear_str);
        if (!empty($data)) {


            $defaut_row['store_name'] = $this->db->get_value("SELECT store_name from base_store  WHERE store_code=:store_code ", array(':store_code' => $store_code));
            $key_arr = array_keys($hear_data);
            foreach ($data as $val) {
                $row = array();
                foreach ($key_arr as $k) {
                    $row[$k] = isset($val[$k]) ? $val[$k] : $defaut_row[$k];
                    $row[$k] = iconv('utf-8', 'gbk', $row[$k]);
                    if($k =='barcode'){
                         $row[$k] = $row[$k]."\t";
                    }
                }
                $content_csv[] = implode(',', $row);
            }
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Disposition:inline;filename="' . iconv('utf-8', 'gbk', $compare_code) . '.csv"');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            echo join("\r\n", $content_csv) . "\r\n";
            die;
        }
    }
}