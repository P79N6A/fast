<?php
set_time_limit(0);
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_model('oms/WavesRecordModel');

class SellRecordNoticeModel extends TbModel {

    protected $table_detail = "oms_sell_record_notice_detail";

    function __construct() {
        parent::__construct('oms_sell_record_notice');
    }

    function create_record_notice($sell_record_code) {
        $filed_record = array('sell_record_id', 'sell_record_code', 'deal_code', 'deal_code_list', 'sale_channel_code', 'alipay_no', 'is_handwork', 'store_code', 'shop_code', 'user_code', 'pay_type', 'pay_code', 'pay_status', 'pay_time', 'customer_code','customer_address_id', 'buyer_name', 'receiver_name', 'receiver_country', 'receiver_province', 'receiver_city', 'receiver_district', 'receiver_street', 'receiver_address', 'receiver_addr', 'receiver_zip_code', 'receiver_mobile', 'receiver_phone', 'receiver_email', 'express_code', 'express_no', 'express_data', 'plan_send_time', 'goods_num', 'sku_num', 'goods_weigh', 'real_weigh', 'weigh_express_money', 'weigh_time', 'buyer_remark', 'seller_remark', 'seller_flag', 'order_remark', 'store_remark', 'order_money', 'goods_money', 'express_money', 'delivery_money', 'payable_money', 'paid_money', 'invoice_type', 'invoice_title', 'invoice_content', 'invoice_money', 'invoice_status', 'create_time', 'record_time', 'record_date', 'is_lock', 'is_change_record', 'change_record_from', 'is_split', 'is_split_new', 'split_order', 'split_new_orders', 'is_combine', 'is_combine_new', 'combine_orders', 'combine_new_order', 'is_copy', 'is_copy_from', 'is_wap', 'is_jhs', 'point_fee', 'alipay_point_fee', 'coupon_fee', 'yfx_fee', 'change_sell_record', 'is_print_sellrecord', 'is_print_express', 'is_fenxiao', 'is_notice_time', 'check_time', 'lastchanged', 'is_buyer_remark', 'is_seller_remark', 'is_rush', 'confirm_person', 'notice_person', 'fenxiao_id', 'fenxiao_name','sale_mode');
        $filed_record_detail = array('sell_record_detail_id', 'sell_record_code', 'deal_code', 'sub_deal_code', 'goods_code', 'spec1_code', 'spec2_code', 'sku', 'goods_price', 'num', 'goods_weigh', 'avg_money', 'platform_spec', 'is_gift', 'sale_mode', 'delivery_mode', 'delivery_days_or_time', 'plan_send_time', 'lastchanged', 'pic_path','combo_sku');

        $filed_record_str = implode(',', $filed_record);
        $sql_record = "insert into {$this->table} ({$filed_record_str}) ";
        $sql_record .="select {$filed_record_str} from oms_sell_record  ";
        $sql_record .=" WHERE sell_record_code='{$sell_record_code}' ";
        $this->db->query($sql_record);

        $filed_record_detail_str = implode(',', $filed_record_detail);
        $sql_detail = "insert into {$this->table_detail} ({$filed_record_detail_str}) ";
        $sql_detail .="select {$filed_record_detail_str} from oms_sell_record_detail  ";
        $sql_detail .=" WHERE sell_record_code='{$sell_record_code}' ";
        $this->db->query($sql_detail);
        $sql = "select  sku,sum(num) as num  from oms_sell_record_notice_detail  WHERE sell_record_code='{$sell_record_code}' GROUP BY sku  ";

        $data = $this->db->get_all($sql);
        $sku_arr = array();
        $sku_num_arr =  array();
        $goods_num = 0;
        foreach($data as $val){
            $sku_arr[] = $val['sku'];
            $sku_num_arr[$val['sku']] = $val['num'];
            $goods_num += $val['num'];
        }
         sort($sku_arr);
         ksort($sku_num_arr);
         $sku_str = implode(",", $sku_arr);
         $sku_num_str = implode(",", $sku_num_arr);
         $this->db->update('oms_sell_record_notice', array('sku_all'=>$sku_str,'sku_all_num'=>$sku_num_str,'goods_num'=>$goods_num), "sell_record_code='{$sell_record_code}'");

        return $this->format_ret(1);
    }

    function delete_record_notice($sell_record_code_arr) {
        $sell_record_code_str = "'" . implode("','", $sell_record_code_arr) . "'";
        $sql_record = "delete from {$this->table}  WHERE sell_record_code in({$sell_record_code_str}) ";
        $this->db->query($sql_record);
        $sql_detail = "delete from {$this->table_detail}  WHERE sell_record_code in({$sell_record_code_str}) ";
        $this->db->query($sql_detail);
        return $this->format_ret(1);
    }

    private $record_list = array();
    private $sku_key_arr = array();

    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "";

        $this->get_filter_data($filter, $sql_main, $sql_values);

        $sql_main_end = $sql_main . " GROUP BY r2.sku order  by goods_num desc  ";
        $select = "  r2.sku,SUM(r2.sku_num_gift) as goods_num ,count(rl.sell_record_code) as order_num,r2.pic_path ";

        if((isset($filter['goods_num_start']) && $filter['goods_num_start'] !== '') || (isset($filter['goods_num_end']) && $filter['goods_num_end'] !== '')){
            $sql_main_end = " FROM (SELECT " . $select . $sql_main_end .") AS tmp WHERE 1 ";
            if(isset($filter['goods_num_start']) && $filter['goods_num_start'] !== ''){
                $sql_main_end .= " AND goods_num >= :goods_num_start ";
                $sql_values[':goods_num_start'] = $filter['goods_num_start'];
            }
            if(isset($filter['goods_num_end']) && $filter['goods_num_end'] !== ''){
                $sql_main_end .= " AND goods_num <= :goods_num_end ";
                $sql_values[':goods_num_end'] = $filter['goods_num_end'];
            }
            $select = " * ";
        }

        $ret_data = $this->get_page_from_sql($filter, $sql_main_end, $sql_values, $select, true);
        $this->sku_key_arr = array();

        if (!empty($ret_data['data'])) {



            foreach ($ret_data['data'] as $key => &$val) {
                $this->sku_key_arr[$val['sku']] = $key;
            }

            $this->record_list = &$ret_data['data'];
            //  var_dump( $this->record_list);die;
            unset($sql_values[':goods_num_start']);
            unset($sql_values[':goods_num_end']);
            $this->get_express_info_by_sku($sql_main, $sql_values);
            $this->get_goods_info_by_sku();

            $this->get_goods_shelf_by_sku($filter['store_code']);
        }

        return $this->format_ret(1, $ret_data);
    }

    function get_by_page_more($filter) {

      //  $this->create_sku_by_reocrd();
        $sql_values = array();
        $sql_main = "";

        $this->get_filter_data($filter, $sql_main, $sql_values);
        $sku_all_num = (isset($filter['sku_all_num']) && $filter['sku_all_num'] == 1) ? 1 : 0;

        if($sku_all_num==1){
            $sql_main_end = $sql_main . " GROUP BY rl.sku_all,rl.sku_all_num  order  by goods_num desc  ";
            $select = "  rl.sku_all,rl.sku_all_num,SUM(rl.goods_num) as goods_num ,count(rl.sell_record_code) as order_num ";
        }else{
            $sql_main_end = $sql_main . " GROUP BY rl.sku_all  order  by goods_num desc  ";
            $select = "  rl.sku_all,SUM(rl.goods_num) as goods_num ,count(rl.sell_record_code) as order_num ";
        }

        if((isset($filter['goods_num_start']) && $filter['goods_num_start'] !== '') || (isset($filter['goods_num_end']) && $filter['goods_num_end'] !== '')){
            $sql_main_end = " FROM (SELECT " . $select . $sql_main_end .") AS tmp WHERE 1 ";
            if(isset($filter['goods_num_start']) && $filter['goods_num_start'] !== ''){
                $sql_main_end .= " AND goods_num >= :goods_num_start ";
                $sql_values[':goods_num_start'] = $filter['goods_num_start'];
            }
            if(isset($filter['goods_num_end']) && $filter['goods_num_end'] !== ''){
                $sql_main_end .= " AND goods_num <= :goods_num_end ";
                $sql_values[':goods_num_end'] = $filter['goods_num_end'];
            }
            $select = " * ";
        }

        $ret_data = $this->get_page_from_sql($filter, $sql_main_end, $sql_values, $select, true);
        $this->sku_key_arr = array();

        if (!empty($ret_data['data'])) {


            foreach ($ret_data['data'] as $key => &$val) {
                
                //设置索引数组
               $search_key = $this->set_search_data_key($val,$sku_all_num);
               $this->sku_key_arr[$search_key] = $key;
               
                $sku_arr = explode(",", $val['sku_all']);
                $sku_num_arr = array();
                if($sku_all_num==1){
                    $sku_num_arr = explode(",", $val['sku_all_num']);
                }

                
                $val['barcode_info'] = $this->set_barcode_by_sku($sku_arr,$sku_num_arr);
                $goods_shelf = $this->get_goods_shelf_by_sku_more($filter['store_code'], $sku_arr);
                $val['goods_shelf'] = implode(',', $goods_shelf);
            }

            $this->record_list = &$ret_data['data'];
    
            unset($sql_values[':goods_num_start']);
            unset($sql_values[':goods_num_end']);
            $this->get_express_info_by_sku_more($sql_main, $sql_values,$sku_all_num);
        }

        return $this->format_ret(1, $ret_data);
    }

    private function set_search_data_key(&$row,$sku_all_num){
          $key = $row['sku_all'];
          if($sku_all_num==1){
             $key =  $row['sku_all']."|".$row['sku_all_num'];
          }
          return $key;
    }


    private function set_barcode_by_sku($sku_arr,$sku_num_arr) {

        $barcode = array();

        foreach ($sku_arr as $key=>$sku) {
            $sku_info = load_model("goods/SkuCModel")->get_sku_info($sku, array('barcode','spec1_name','spec2_name','goods_code'));
            $barcode_str = $sku_info['barcode'];
            if(isset($sku_num_arr[$key])){
               $barcode_str.="({$sku_num_arr[$key]}/{$sku_info['goods_code']}/{$sku_info['spec1_name']}/{$sku_info['spec2_name']})";
            }else{
               $barcode_str.="({$sku_info['goods_code']}/{$sku_info['spec1_name']}/{$sku_info['spec2_name']})"; 
            }
            $barcode[] = $barcode_str;
        }
        return implode('<br />', $barcode);
    }
    public function get_sell_record_by_spec($spec,$type){
        $spec_str = $this->arr_to_in_sql_value($spec,'spec_code',$sql_values);
        if($type=="spec1"){
            $sql = "select r1.sell_record_code from {$this->table} r1 inner join oms_sell_record_notice_detail r2   on r1.sell_record_code=r2.sell_record_code inner join goods_sku gs on r2.sku=gs.sku  where  gs.spec1_code IN({$spec_str})  GROUP BY r1.sell_record_code";
        }else{
            $sql = "select r1.sell_record_code from {$this->table} r1 inner join oms_sell_record_notice_detail r2  on r1.sell_record_code=r2.sell_record_code inner join goods_sku gs on r2.sku=gs.sku  where  gs.spec2_code IN({$spec_str})  GROUP BY r1.sell_record_code";
        }
        $data = $this->db->get_all($sql,$sql_values);
        $ret_data = array();
        foreach($data as $val){
            $ret_data[] = $val['sell_record_code'];
        }
        return $ret_data;
    }

    private function get_filter_data($filter, &$sql_main, &$sql_values) {
        if(isset($filter['keyword_type'])&&$filter['keyword_type']!==''){
            $filter[$filter['keyword_type']]=trim($filter['keyword']);
        }
        
     
        $goods_sql = '';
        $goods_join = '';
//        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
//            $keyword_type_goods = array('goods_code', 'goods_short_name');
//            if (in_array($filter['keyword_type'], $keyword_type_goods)) {
//                $goods_join = " INNER  JOIN  base_goods g ON r2.goods_code=g.goods_code";
//            } else {
//                $goods_join = " INNER  JOIN  goods_sku g ON r2.sku=g.sku";
//            }
//            $keyword_arr = explode(",", $filter['keyword']);
//            $keyword_str = "'" . implode("','", $keyword_arr) . "'";
//            $goods_sql = " AND g.{$filter['keyword_type']} in ({$keyword_str})";
//        }            $sql_join = " INNER JOIN (select sum(t1.num) as sku_num_gift,t1.*,t2.goods_name from {$this->table_detail} t1 inner join base_goods t2 on t1.goods_code=t2.goods_code inner join goods_sku t3 on t3.sku=t1.sku  group by t1.sell_record_code,t1.sku) as r2 ON rl.sell_record_code=r2.sell_record_code LEFT JOIN goods_sku r3 ON r2.sku=r3.sku ";
         $sql_join = '';
        if ((isset($filter['barcode']) && $filter['barcode'] !== '')|| (isset($filter['goods_code']) && $filter['goods_code'] !== '')||(isset($filter['goods_name']) && $filter['goods_name'] !== '')||$filter['sku_num'] == 1) {
            $sql_join = " INNER JOIN (select sum(t1.num) as sku_num_gift,t1.*,t2.goods_name from {$this->table_detail} t1 inner join base_goods t2 on t1.goods_code=t2.goods_code   group by t1.sell_record_code,t1.sku) as r2 ON rl.sell_record_code=r2.sell_record_code LEFT JOIN goods_sku r3 ON r2.sku=r3.sku ";
        }
        if ((isset($filter['shelf_code']) && $filter['shelf_code'] !== '')) {
            $sql_val = array();
            $shelf_code = explode(',', $filter['shelf_code']);
            $store_code = explode(',', $filter['store_code']);
            $shelf_str = $this->arr_to_in_sql_value($shelf_code,'shelf_code',$sql_val);
            $store_str = $this->arr_to_in_sql_value($store_code, 'store_code', $sql_val);
            $sql ="SELECT DISTINCT r1.sell_record_code FROM oms_sell_record_notice_detail r1  INNER JOIN goods_shelf gs ON r1.sku = gs.sku WHERE gs.store_code IN ({$store_str}) AND gs.shelf_code IN ({$shelf_str}) ";
            $data = $this->db->get_all($sql, $sql_val);
            if (!empty($data)) {
                $sell_record_code_arr = array_column($data, 'sell_record_code');
                $sell_record_code_str = $this->arr_to_in_sql_value($sell_record_code_arr, 'sell_record_code', $sql_values);
                $goods_sql = " AND rl.sell_record_code IN  ({$sell_record_code_str}) ";
            }else{
                $goods_sql = " AND 1=2 ";
            }
        }
        $sql_main = "FROM {$this->table} rl $sql_join  $goods_join WHERE 1 " . $goods_sql;
        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);


        $wms_store = load_model('sys/WmsConfigModel')->get_wms_store_all();
        
        $o2o_store = load_model('o2o/O2oEntryModel')->get_o2o_store_all();
        $no_select_store = array_merge($wms_store,$o2o_store);

        if(!empty($no_select_store)){
            $no_select_store = array_unique($no_select_store);
            $no_store_str = $this->arr_to_in_sql_value($no_select_store, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code not in($no_store_str) ";
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $filter['barcode'] = str_replace('，', ',', $filter['barcode']);
            $barcode_arr = explode(',', $filter['barcode']);
            $barcode_where = $this->arr_to_like_sql_value($barcode_arr, 'barcode', $sql_values, 'r3.');
            $sql_main .= ' AND ' . $barcode_where;
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $filter['goods_code'] = str_replace('，', ',', $filter['goods_code']);
            $goods_code_arr = explode(',', $filter['goods_code']);
            $goods_code_where = $this->arr_to_like_sql_value($goods_code_arr, 'goods_code', $sql_values, 'r3.');
            $sql_main .= ' AND ' . $goods_code_where;
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $filter['goods_name'] = str_replace('，', ',', $filter['goods_name']);
            $goods_name_arr = explode(',', $filter['goods_name']);
            $goods_name_where = $this->arr_to_like_sql_value($goods_name_arr, 'goods_name', $sql_values, 'r2.');
            $sql_main .= ' AND ' . $goods_name_where;
        }

        //规格1
        if($filter['spec1']!=''){
            $spec1_arr = explode(',', $filter['spec1']);
            $spec1_record = $this->get_sell_record_by_spec($spec1_arr,$type="spec1");
            if (!empty($spec1_record)) {
                $spec1_record_str = $this->arr_to_in_sql_value($spec1_record, 'spec1_code', $sql_values);
                $sql_main .= " AND rl.sell_record_code in ({$spec1_record_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }
        //规格2
        if($filter['spec2']!=''){
            $spec2_arr = explode(',', $filter['spec2']);
            $spec2_record = $this->get_sell_record_by_spec($spec2_arr,$type="spec2");
            if (!empty($spec2_record)) {
                $spec2_record_str = $this->arr_to_in_sql_value($spec2_record, 'spec2_code', $sql_values);
                $sql_main .= " AND rl.sell_record_code in ({$spec2_record_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }
        //是否打印发票
        if (isset($filter['is_print_invoice'])) {
            if ($filter['is_print_invoice'] == '0') {
                $sql_main .= " AND rl.is_print_invoice = 0 ";
            } elseif ($filter['is_print_invoice'] == '1') {
                $sql_main .= " AND rl.is_print_invoice = 1 ";
            }
        }
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
        $arr = explode(',', $filter['sale_channel_code']);
        $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND rl.sale_channel_code in ( " . $str . " ) ";
        }
        //销售平台
        if (isset($filter['is_cod']) && $filter['is_cod'] !== '') {
            if ($filter['is_cod'] == 1) {
                $sql_main .= " AND rl.pay_type ='cod' ";
            } else {
                $sql_main .= " AND rl.pay_type <>'cod' ";
            }
        }
        //开票订单 invoice_title
        if (isset($filter['is_nvoice']) && $filter['is_nvoice'] !== '') {
            if ($filter['is_nvoice'] == 1) {
                $sql_main .= " AND rl.invoice_title <>'' ";
            } else {
                $sql_main .= " AND rl.invoice_title ='' ";
            }
        }
        //订单标签
        if (isset($filter['order_tag']) && $filter['order_tag'] !== '') {
            $tag_arr = explode(',', $filter['order_tag']);
            $_tag_str = "'" . implode("','", $tag_arr) . "'";
            if (in_array('none', $tag_arr)) {
                if (count($tag_arr) > 1) {
                    $sql_tag = "SELECT rn.sell_record_code,rt.tag_v FROM    oms_sell_record_notice rn LEFT JOIN oms_sell_record_tag rt ON rn.sell_record_code = rt.sell_record_code AND rt.tag_type = 'order_tag' having tag_v  in({$_tag_str}) or tag_v is null";
                } else {
                    $sql_tag = "SELECT rn.sell_record_code,rt.tag_v FROM    oms_sell_record_notice rn LEFT JOIN oms_sell_record_tag rt ON rn.sell_record_code = rt.sell_record_code AND rt.tag_type = 'order_tag' having  tag_v is null";
                }
                $tag_record_data = $this->db->get_all($sql_tag);
                if (!empty($tag_record_data)) {
                    $tag_record = array_column($tag_record_data, 'sell_record_code');
                    $tag_record_str = $this->arr_to_in_sql_value($tag_record, 'sell_record_code', $sql_values);
                    $sql_main .= " AND rl.sell_record_code  in ({$tag_record_str}) ";
                } else {
                    $sql_main .= "AND 1=2";
                }
            } else {
                $sql_tag = "select os.sell_record_code from oms_sell_record_notice os inner JOIN oms_sell_record_tag tag on os.sell_record_code=tag.sell_record_code where tag.tag_type='order_tag' and tag.tag_v in ({$_tag_str})";
                $tag_record_data = $this->db->get_all($sql_tag);
                if (!empty($tag_record_data)) {
                    $tag_record = array_column($tag_record_data, 'sell_record_code');
                    $tag_record_str = $this->arr_to_in_sql_value($tag_record, 'sell_record_code', $sql_values);
                    $sql_main .= " AND rl.sell_record_code  in ({$tag_record_str}) ";
                } else {
                    $sql_main .= " AND 1=2 ";
                }
            }
        }

        //分销订单
        if (isset($filter['is_fenxiao']) && $filter['is_fenxiao'] !== '') {
            $sql_main .= " AND (rl.is_fenxiao = 1 OR rl.is_fenxiao = 2) ";
        }

        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] !== '') {
                $arr = explode(',', $filter['express_code']);
        $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
            $sql_main .= " AND rl.express_code in ( " . $str . " ) ";
        }
        //聚划算订单
        if (isset($filter['is_jhs']) && $filter['is_jhs'] !== '') {
            $sql_main .= " AND rl.is_jhs =:is_jhs ";
            $sql_values[':is_jhs'] = $filter['is_jhs'];
        }

        //加急订单
        if (isset($filter['is_rush']) && $filter['is_rush'] !== '') {
            $sql_main .= " AND rl.is_rush =:is_rush ";
            $sql_values[':is_rush'] = $filter['is_rush'];
        }

        //通知配货时间
        if (!empty($filter['is_notice_time_start'])) {
            $sql_main .= " AND rl.is_notice_time >= :is_notice_time_start ";
            $is_notice_time_start = strtotime(date("Y-m-d", strtotime($filter['is_notice_time_start'])));
            if ($is_notice_time_start == strtotime($filter['is_notice_time_start'])) {
                $sql_values[':is_notice_time_start'] = $filter['is_notice_time_start'] . ' 00:00:00';
            } else {
                $sql_values[':is_notice_time_start'] = $filter['is_notice_time_start'];
            }
        }
        if (!empty($filter['is_notice_time_end'])) {
            $sql_main .= " AND rl.is_notice_time <= :is_notice_time_end ";
            $is_notice_time_end = strtotime(date("Y-m-d", strtotime($filter['is_notice_time_end'])));
            if ($is_notice_time_end == strtotime($filter['is_notice_time_end'])) {
                $sql_values[':is_notice_time_end'] = $filter['is_notice_time_end'] . ' 23:59:59';
            } else {
                $sql_values[':is_notice_time_end'] = $filter['is_notice_time_end'];
            }
        }

        //明细表sku的数量
            if(isset($filter['detail_goods_num_start']) && $filter['detail_goods_num_start'] !== ''){
                $sql_main .= " AND r2.sku_num_gift >= :detail_goods_num_start ";
                $sql_values[':detail_goods_num_start'] = $filter['detail_goods_num_start'];
            }
            if(isset($filter['detail_goods_num_end']) && $filter['detail_goods_num_end'] !== ''){
                $sql_main .= " AND r2.sku_num_gift <= :detail_goods_num_end ";
                $sql_values[':detail_goods_num_end'] = $filter['detail_goods_num_end'];
            }
        //计划发货时间
        if (!empty($filter['plan_time_start'])) {
            $sql_main .= " AND rl.plan_send_time >= :plan_time_start ";
            $sql_values[':plan_time_start'] = $filter['plan_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['plan_time_end'])) {
            $sql_main .= " AND rl.plan_send_time <= :plan_time_end ";
            $sql_values[':plan_time_end'] = $filter['plan_time_end'] . ' 23:59:59';
        }
        //下单时间
        if (!empty($filter['record_time_start'])) {
            $sql_main .= " AND rl.record_time >= :record_time_start ";
            $record_time_start = strtotime(date("Y-m-d", strtotime($filter['record_time_start'])));
            if ($record_time_start == strtotime($filter['record_time_start'])) {
                $sql_values[':record_time_start'] = $filter['record_time_start'] . ' 00:00:00';
            } else {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            }
        }
        if (!empty($filter['record_time_end'])) {
            $sql_main .= " AND rl.record_time <= :record_time_end ";
            $record_time_end = strtotime(date("Y-m-d", strtotime($filter['record_time_end'])));
            if ($record_time_end == strtotime($filter['record_time_end'])) {
                $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
            } else {
                $sql_values[':record_time_end'] = $filter['record_time_end'];
            };
        }

        //付款时间
        if (!empty($filter['pay_time_start'])) {
            $sql_main .= " AND rl.pay_time >= :pay_time_start ";
            $pay_time_start = strtotime(date("Y-m-d", strtotime($filter['pay_time_start'])));
            if ($pay_time_start == strtotime($filter['pay_time_start'])) {
                $sql_values[':pay_time_start'] = $filter['pay_time_start'] . ' 00:00:00';
            } else {
                $sql_values[':pay_time_start'] = $filter['pay_time_start'];
            }
        }
        if (!empty($filter['pay_time_end'])) {
            $sql_main .= " AND rl.pay_time <= :pay_time_end ";
            $pay_time_end = strtotime(date("Y-m-d", strtotime($filter['pay_time_end'])));
            if ($pay_time_end == strtotime($filter['pay_time_end'])) {
                $sql_values[':pay_time_end'] = $filter['pay_time_end'] . ' 23:59:59';
            } else {
                $sql_values[':pay_time_end'] = $filter['pay_time_end'];
            }
        }
        // $filter['sku_num'] = 1;
        if (!empty($filter['sku_num']) && $filter['sku_num'] != '10' && is_numeric($filter['sku_num']) == true ) {
            $sql_main .= " AND rl.sku_num = :sku_num ";
            $sql_values[':sku_num'] = $filter['sku_num'];
        }
        
        //是否含运费
        $contain_express_money = 0;
        if (isset($filter['contain_express_money']) && $filter['contain_express_money'] == '1') {
            $contain_express_money = 1;
        }        
                //订单价格
        if (isset($filter['money_start']) && $filter['money_start'] !== '') {
            if ($contain_express_money == 1) {
                $sql_main .= " AND rl.payable_money>= :money_start";
            } else {
                $sql_main .= " AND rl.payable_money - rl.express_money >= :money_start";
            }
            $sql_values[':money_start'] = $filter['money_start'];
        }
        
        if (isset($filter['money_end']) && $filter['money_end'] !== '') {
            if ($contain_express_money == 1) {
                $sql_main .= " AND rl.payable_money<= :money_end";
            } else {
                $sql_main .= " AND rl.payable_money - rl.express_money <= :money_end";
            }
            $sql_values[':money_end'] = $filter['money_end'];
        }
           //省
        if (isset($filter['province_multi']) && $filter['province_multi'] !== '') {
            $sql_main .= " AND rl.receiver_province in({$filter['province_multi']})";
        }
        //省
        if(isset($filter['province']) && $filter['province'] !== ''){
            $sql_main .= " AND rl.receiver_province =:receiver_province";
            $sql_values[':receiver_province'] = $filter['province'];
        }
        //市
        if(isset($filter['city']) && $filter['city'] !== ''){
            $sql_main .= " AND rl.receiver_city =:receiver_city";
            $sql_values[':receiver_city'] = $filter['city'];
        }
        //区
        if(isset($filter['district']) && $filter['district'] !== ''){
            $sql_main .= " AND rl.receiver_district =:receiver_district";
            $sql_values[':receiver_district'] = $filter['district'];
        }

           //订单性质
            if (isset($filter['record_nature']) && $filter['record_nature'] !== '') {
            $sell_record_attr_arr = explode(',', $filter['record_nature']);
            $sql_attr_arr = array();
            foreach ($sell_record_attr_arr as $attr) {
                if ($attr == 'is_presale') {
                    $sql_attr_arr[] = " rl.sale_mode = 'presale' ";
                }
                if ($attr == 'is_fenxiao') {
                    $sql_attr_arr[] = " (rl.is_fenxiao = 1 OR rl.is_fenxiao = 2) ";
                }
                if ($attr == 'is_rush') {
                    $sql_attr_arr[] = " rl.is_rush = 1 ";
                }
                if ($attr == 'is_combine') {
                    $sql_attr_arr[] = " rl.is_combine = 1 ";
                }
                if ($attr == 'is_split') {
                    $sql_attr_arr[] = " rl.is_split = 1 ";
                }
                if ($attr == 'is_handwork') {
                    $sql_attr_arr[] = " rl.is_handwork = 1 ";
                }
                if ($attr == 'is_copy') {
                    $sql_attr_arr[] = " rl.is_copy = 1 ";
                }
                if ($attr == 'is_change_record') {
                    $sql_attr_arr[] = " rl.is_change_record = 1 ";
                }
                if ($attr == 'is_replenish') {
                    $order_type_sql=" select tl.sell_record_code from {$this->table} tl inner join oms_sell_record t2 on tl.sell_record_code=t2.sell_record_code and t2.is_replenish='1'";
                    $record_type_data = $this->db->get_all($order_type_sql);
                    if (!empty($record_type_data)) {
                        $record_type_arr = array_column($record_type_data, 'sell_record_code');
                        $record_type_str = $this->arr_to_in_sql_value($record_type_arr, 'sell_record_code', $sql_values);
                        $sql_attr_arr[] = " rl.sell_record_code  in ({$record_type_str})";

                    }
                }
            }
            if(!empty($sql_attr_arr)){
                $sql_main .= ' AND (' . join(' OR ', $sql_attr_arr) . ')';
            }
        }

         //时间查询
        if (isset($filter['start_time']) && $filter['start_time'] != '') {
            switch ($filter['time_type']) {
                //下单时间
                case 'record_time':
                    $sql_main .= " AND rl.record_time >= :start_time ";
                    $record_time_start = strtotime(date("Y-m-d", strtotime($filter['start_time'])));
                    if ($record_time_start == strtotime($filter['start_time'])) {
                        $sql_values[':start_time'] = $filter['start_time'] . ' 00:00:00';
                    } else {
                        $sql_values[':start_time'] = $filter['start_time'];
                    }
                    break;
                //付款时间
                case 'pay_time':
                    $sql_main .= " AND rl.pay_time >= :start_time ";
                    $pay_time_start = strtotime(date("Y-m-d", strtotime($filter['start_time'])));
                    if ($pay_time_start == strtotime($filter['start_time'])) {
                        $sql_values[':start_time'] = $filter['start_time'] . ' 00:00:00';
                    } else {
                        $sql_values[':start_time'] = $filter['start_time'];
                    }
                    break;
                //通知配货时间
                case 'notice_time':
                    $sql_main .= " AND rl.is_notice_time >= :start_time ";
                    $is_notice_time_start = strtotime(date("Y-m-d", strtotime($filter['start_time'])));
                    if ($is_notice_time_start == strtotime($filter['start_time'])) {
                        $sql_values[':start_time'] = $filter['start_time'] . ' 00:00:00';
                    } else {
                        $sql_values[':start_time'] = $filter['start_time'];
                    }
                    break;
                //计划发货时间
                case 'plan_time':
                    $sql_main .= " AND rl.plan_send_time >= :start_time ";
                    $is_notice_time_start = strtotime(date("Y-m-d", strtotime($filter['start_time'])));
                    if ($is_notice_time_start == strtotime($filter['start_time'])) {
                        $sql_values[':start_time'] = $filter['start_time'] . ' 00:00:00';
                    } else {
                        $sql_values[':start_time'] = $filter['start_time'];
                    }
                    break;
            }
        }

        if (isset($filter['end_time']) && $filter['end_time'] != '') {
            switch ($filter['time_type']) {
                //下单时间
                case 'record_time':
                    $sql_main .= " AND rl.record_time <= :end_time ";
                    $record_time_end = strtotime(date("Y-m-d", strtotime($filter['end_time'])));
                    if ($record_time_end == strtotime($filter['end_time'])) {
                        $sql_values[':end_time'] = $filter['end_time'] . ' 00:00:00';
                    } else {
                        $sql_values[':end_time'] = $filter['end_time'];
                    }
                    break;
                //付款时间
                case 'pay_time':
                    $sql_main .= " AND rl.pay_time <= :end_time ";
                    $pay_time_end = strtotime(date("Y-m-d", strtotime($filter['end_time'])));
                    if ($pay_time_end == strtotime($filter['end_time'])) {
                        $sql_values[':end_time'] = $filter['end_time'] . ' 00:00:00';
                    } else {
                        $sql_values[':end_time'] = $filter['end_time'];
                    }
                    break;
                //通知配货时间
                case 'notice_time':
                    $sql_main .= " AND rl.is_notice_time <= :end_time ";
                    $is_notice_time_end = strtotime(date("Y-m-d", strtotime($filter['end_time'])));
                    if ($is_notice_time_end == strtotime($filter['end_time'])) {
                        $sql_values[':end_time'] = $filter['end_time'] . ' 00:00:00';
                    } else {
                        $sql_values[':end_time'] = $filter['end_time'];
                    }
                    break;
                //计划发货时间
                case 'plan_time':
                    $sql_main .= " AND rl.plan_send_time <= :end_time ";
                    $is_plan_time_end = strtotime(date("Y-m-d", strtotime($filter['end_time'])));
                    if ($is_plan_time_end == strtotime($filter['end_time'])) {
                        $sql_values[':end_time'] = $filter['end_time'] . ' 00:00:00';
                    } else {
                        $sql_values[':end_time'] = $filter['end_time'];
                    }
                    break;
            }
        }
        //订单理论重量
        if (isset($filter['weight_start']) && $filter['weight_start'] != '') {
            $sql_main .= " AND rl.goods_weigh >= :weight_start ";
            $sql_values[':weight_start'] = $filter['weight_start'];
        }
        if (isset($filter['weight_end']) && $filter['weight_end'] != '') {
            $sql_main .= " AND rl.goods_weigh <= :weight_end ";
            $sql_values[':weight_end'] = $filter['weight_end'];
        }

        if (isset($filter['buyer_remark']) && $filter['buyer_remark'] != '') {
            if ($filter['buyer_remark'] == '1') {
                $sql_main .= " AND rl.is_buyer_remark = 1 ";
            } else {
                $sql_main .= " AND rl.is_buyer_remark = 0 ";
            }
        }
        //商家留言
        if (isset($filter['seller_remark']) && $filter['seller_remark'] != '') {
            if ($filter['seller_remark'] == '1') {
                $sql_main .= " AND rl.is_seller_remark =1 ";
            } else {
                $sql_main .= " AND rl.is_seller_remark =0 ";
            }
        }
        //仓库留言
        if (isset($filter['store_remark']) && $filter['store_remark'] != '') {
            if ($filter['store_remark'] == '1') {
                $sql_main .= " AND rl.store_remark <> '' ";
            } else {
                $sql_main .= " AND rl.store_remark = '' ";
            }
        }
        //多sku商品数量检索
        if (isset($filter['record_goods_num_start']) && $filter['record_goods_num_start'] !== '') {
            $sql_main .= " AND rl.goods_num >= :record_goods_num_start ";
            $sql_values[':record_goods_num_start'] = $filter['record_goods_num_start'];
        }
        if (isset($filter['record_goods_num_end']) && $filter['record_goods_num_end'] !== '') {
            $sql_main .= " AND rl.goods_num <= :record_goods_num_end ";
            $sql_values[':record_goods_num_end'] = $filter['record_goods_num_end'];
        }
    }

    private function get_sku_str() {
        static $sku_str = '';
        if (empty($sku_str) && !empty($this->sku_key_arr)) {
            $sku_arr = array_keys($this->sku_key_arr);
            $sku_str = "'" . implode("','", $sku_arr) . "'";
        }
        return $sku_str;
    }

    private function get_express_info_by_sku($sql_main, $sql_values) {
        $sku_str = $this->get_sku_str();
        $sql_main .= " AND r2.sku in({$sku_str}) GROUP BY r2.sku,rl.express_code order by  order_num desc ";
        $select = "select r2.sku,rl.express_code,count(rl.sell_record_code) as order_num  ";
        $data = $this->db->get_all($select . $sql_main, $sql_values);
        $info_key = 'express_info';
        $info_key2 = 'express_code_all';
        foreach ($data as $val) {
            $info = $this->get_express_name($val['express_code']) . "({$val['order_num']})";
            $this->set_record_list_info($val['sku'], $info_key, $info);
            $this->set_record_list_info($val['sku'], $info_key2, $val['express_code']);
        }
    }

    private function get_express_info_by_sku_more($sql_main, $sql_values,$sku_all_num) {
        

        $sku_all_arr = array_keys($this->sku_key_arr);

        if($sku_all_num==1){
            $where_sku_arr = array();
            foreach($sku_all_arr as $sku_num_str ){
                list($sku_all, $sku_num) = explode("|", $sku_num_str);
                $where_sku_arr[] = " (rl.sku_all = '{$sku_all}' AND rl.sku_all_num = '{$sku_num}') ";
            }
    
            $where_sku = implode(" OR ", $where_sku_arr);
            $sql_main .=" AND  ({$where_sku})";
            
            $sql_main .= " GROUP BY rl.sku_all,rl.sku_all_num,rl.express_code order by  order_num desc ";
            $select = "select  rl.sku_all,rl.sku_all_num,rl.express_code,count(rl.sell_record_code) as order_num  ";       
        }else{
            $sku_all_str = "'".implode("','", $sku_all_arr)."'";
            $sql_main .=" AND rl.sku_all in({$sku_all_str}) ";
            $sql_main .= " GROUP BY rl.sku_all,rl.express_code order by  order_num desc ";
            $select = "select  rl.sku_all,rl.express_code,count(rl.sell_record_code) as order_num  ";         
        }

        $data = $this->db->get_all($select . $sql_main, $sql_values);

        foreach ($data as $val) {
            $info = $this->get_express_name($val['express_code']) . "({$val['order_num']})";
            $serach_key = $this->set_search_data_key($val,$sku_all_num);
            
            if(!isset($this->sku_key_arr[$serach_key])){
                continue;
            }
            
            $key = $this->sku_key_arr[$serach_key];
            if (!isset($this->record_list[$key]['express_info'])) {
                $this->record_list[$key]['express_info'] = $info;

                $this->record_list[$key]['express_code_all'] = $val['express_code'];
            } else {
                $this->record_list[$key]['express_info'] .= "<br>" . $info;
                $this->record_list[$key]['express_code_all'] .= "," . $val['express_code'];
            }
        }
    }

    private function get_express_name($express_code) {
        static $express_data = null;
        if (empty($express_data)) {
            $data = $this->db->get_all("select express_code,express_name from base_express");
            foreach ($data as $val) {
                $express_data[$val['express_code']] = $val['express_name'];
            }
        }
        return $express_data[$express_code];
    }

    private function set_record_list_info($sku, $info_key, $info) {
        $key = $this->sku_key_arr[$sku];
        $tag_arr = array('express_info' => '<br />');
        if (isset($this->record_list[$key][$info_key])) {
            $tag = isset($tag_arr[$info_key]) ? $tag_arr[$info_key] : ',';
            $this->record_list[$key][$info_key] .= $tag . $info;
        } else {
            $this->record_list[$key][$info_key] = $info;
        }
    }

    private function get_goods_info_by_sku() {
        $sku_str = $this->get_sku_str();
        $sql = "select b.goods_code,b.goods_name,s.sku,b.goods_short_name,s.spec1_name,s.spec2_name,s.barcode from base_goods b "
                . "INNER JOIN  goods_sku s ON b.goods_code=s.goods_code "
//                . "INNER JOIN  base_spec1 c1 ON c1.spec1_code=s.spec1_code "
//                . "INNER JOIN  base_spec2 c2 ON c2.spec2_code=s.spec2_code "
                . "where s.sku   in({$sku_str}) ";
        $data = $this->db->get_all($sql);
        // var_dump($sql);die;
        $info_key = "goods_info";
        $code_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));
        foreach ($data as $val) {
            $goods_name = empty($val['goods_short_name']) ? $val['goods_name'] : $val['goods_short_name'];
            $info = "商品简称：" . $goods_name . "<br / >";
            $info .= "商品编码：" . $val['goods_code'] . "<br / >";
            $info .= "商品规格：" . $code_arr['goods_spec1'] . ":" . $val['spec1_name'] . ',' . $code_arr['goods_spec2'] . ":" . $val['spec2_name'];
            $this->set_record_list_info($val['sku'], $info_key, $info);
            $this->set_record_list_info($val['sku'], 'barcode', $val['barcode']);
        }
    }

    private function get_goods_shelf_by_sku($store_code, $sku_str = '') {
        $sku_str = $this->get_sku_str();
        $sql = "select sku,shelf_code from goods_shelf where store_code='{$store_code}'  AND  sku in({$sku_str})  ";
        $data = $this->db->get_all($sql);
        $info_key = "goods_shelf";
        foreach ($data as $val) {
            $this->set_record_list_info($val['sku'], $info_key, $val['shelf_code']);
        }
    }

    private function get_goods_shelf_by_sku_more($store_code, $sku_arr) {
        $sku_str = "'" . implode("','", $sku_arr) . "'";
        $sql = "select sku,shelf_code from goods_shelf where store_code='{$store_code}'  AND  sku in({$sku_str})  ";
        $data = $this->db->get_all($sql);
        $goods_shelf_arr = array();
        foreach ($data as $val) {
            $goods_shelf_arr[] = $val['shelf_code'];
        }
        return $goods_shelf_arr;
    }

    private $waves_num = 0;

    function create_waves_record($filter) {
        $isCheck = isset($filter['is_check']) ? $filter['is_check'] : 2;
        $sql_values = array();
        $sql_main = "";
        $this->get_filter_data($filter, $sql_main, $sql_values);
        //rl
        $sku_data = &$filter['sku_data'];



        //  $express_code_arr = implode(",", $express_code_all);
        $order_num = $filter['order_num'];
        $select = " select  rl.sell_record_code ";
        $i = 0;
        $sell = array();
        $is_loop = false;
        foreach ($sku_data as $sku => $express_code_all) {
            $sql_main_sku = $sql_main . "  AND r2.sku=:sku ";
            $sql_values[':sku'] = $sku;

            $express_code_arr = explode(',', $express_code_all);

            foreach ($express_code_arr as $express_code) {
                $sql_main_end = $sql_main_sku . " AND  rl.express_code=:express_code ";
                $sql_values[':express_code'] = $express_code;
                $limit = $order_num*10;
                $sql_main_end.=" order by rl.plan_send_time limit {$limit} "; //limit ".$order_num

                $data = $this->db->get_all($select . $sql_main_end, $sql_values);
                if($is_loop===false){
                    $is_loop = count($data)==$limit?true:false;
                }
                if (!empty($data)) {
                    if(!isset($filter['create_type']) || $filter['create_type'] != 'combine'){
                        $ret = $this->create_waves_by_num($data, $order_num, $isCheck);
                        if ($ret['status'] < 0) {
                            $ret['data'] = $this->waves_num;
                            return $ret;
                        } 
                    }
                }
                //整理数组用于合并生成波次
                if(isset($filter['create_type']) && $filter['create_type'] == 'combine' && !empty($data)){
                    foreach ($data as $value){
                        $sell[$express_code][$i]['sell_record_code'] = $value['sell_record_code'];
                        $i++;
                    }
                }
            }  
        }
        if(isset($filter['create_type']) && $filter['create_type'] == 'combine' && !empty($data)){
            foreach ($sell as $sell_record_code_arr){
                $ret = $this->create_waves_by_num(array_unique($sell_record_code_arr, SORT_REGULAR), $order_num, $isCheck);
                if ($ret['status'] < 0) {
                    $msg .= $ret['message'];
                }
            }
            if(!empty($msg)){
                return $this->format_ret(-1,$this->waves_num, $msg); 
            } 
        }
        $status = $is_loop===true?2:1;
        return $this->format_ret($status, $this->waves_num); 
       // return $this->format_ret(1, $this->waves_num);
    }
    
    
    function create_waves_task($filter){
        //$filter
         require_model('common/TaskModel');
        $task = new TaskModel();
        $task_data = array();
        $md5  = md5(json_encode($filter) );
        $task_data['code'] = 'create_waves_record_more_'.$md5;
        $request['app_act'] = 'prm/inv/' . $code;
//create_wave_more_combine create_wave_more

    
        $request['app_fmt'] = 'json';
        $task_data['start_time'] = time();
        $task_data['request'] = require_model;
        $ret = $task->save_task($task_data);
        if ($ret['status'] < 0) {
            if ($ret['status'] == -5) {
                $ret['message'] = "库存维护中，尚未结束...";
            }
        }
        return $ret;  
    }
    
    
    
    function create_waves_record_more($filter){
        $isCheck = isset($filter['is_check']) ? $filter['is_check'] : 2;
            $sql_values = array();
        $sql_main = "";
        $this->get_filter_data($filter, $sql_main, $sql_values);
        //rl
        $sku_data = &$filter['sku_data'];
        $sku_all_num = &$filter['sku_all_num'];


        //  $express_code_arr = implode(",", $express_code_all);
        $order_num = $filter['order_num'];
        $select = " select  rl.sell_record_code ";
        $i = 0;
        $sell = array();
        $is_loop = false; 
        foreach ($sku_data as $sku_all => $express_code_all) {
            if($sku_all_num==0){
                $sql_main_sku = $sql_main . "  AND rl.sku_all=:sku_all ";
                $sql_values[':sku_all'] = $sku_all;
            }else{
                   $sql_main_sku = $sql_main . "  AND rl.sku_all=:sku_all AND rl.sku_all_num=:sku_all_num   ";
                   list($sku_all_str,$sku_num) = explode("|", $sku_all);
                   $sql_values[':sku_all'] = $sku_all_str; 
                   $sql_values[':sku_all_num'] = $sku_num; 
            }
            
            $express_code_arr = explode(',', $express_code_all);

            foreach ($express_code_arr as $express_code) {
                $sql_main_end = $sql_main_sku . " AND  rl.express_code=:express_code ";
                $sql_values[':express_code'] = $express_code;
                 $limit = $order_num*10;
                $sql_main_end.=" order by rl.plan_send_time limit {$limit}"; //limit ".$order_num

                $data = $this->db->get_all($select . $sql_main_end, $sql_values);     
                if($is_loop===false){
                    $is_loop = count($data)==$limit?true:false;
                }   
                
                if (!empty($data)) {
                    if(!isset($filter['create_type']) || $filter['create_type'] != 'combine'){
                        $ret = $this->create_waves_by_num($data, $order_num, $isCheck);
                        if ($ret['status'] < 0) {
                            $ret['data'] = $this->waves_num;
                            return $ret;
                        } 
                    }
                }
                //整理数组用于合并生成波次
                if(isset($filter['create_type']) && $filter['create_type'] == 'combine' && !empty($data)){
                    foreach ($data as $value){
                        $sell[$express_code][$i]['sell_record_code'] = $value['sell_record_code'];
                        $i++;
                    }
                }
            }          
        }
        if(isset($filter['create_type']) && $filter['create_type'] == 'combine' && !empty($data)){
            $msg = '';
            foreach ($sell as $sell_record_code_arr){
                $ret = $this->create_waves_by_num($sell_record_code_arr, $order_num, $isCheck);
                if ($ret['status'] < 0) {
                    $msg .= $ret['message'];
                }
            }
            if(!empty($msg)){
                return $this->format_ret(-1,$this->waves_num, $msg); 
            } 
        }
        $status = $is_loop===true?2:1;
        return $this->format_ret($status, $this->waves_num); 
    }
            
    
    
    function create_waves_by_num($data, $order_num, $isCheck) {

        $m = new WavesRecordModel();
        $sell_record_code_arr = array();
        $record_page = ceil(count($data) / $order_num);
        foreach ($data as $val) {
            $sell_record_code_arr[] = $val['sell_record_code'];
            if (count($sell_record_code_arr) == $order_num && $record_page > 1) {
                $ret = $m->create_waves($sell_record_code_arr, $isCheck);
                if ($ret['status'] < 0) {
                    return $ret;
                }
                $this->waves_num++;
                $record_page--;
                $sell_record_code_arr = array();
            }
        }

        if (!empty($sell_record_code_arr)) {
            $ret = $m->create_waves($sell_record_code_arr, $isCheck);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $this->waves_num++;
        }
        return $this->format_ret(1);
    }

    //保存波次策略名称
    function save_wave_strategy_name($params) {
        $wave_strategy_name = isset($params['wave_strategy_name']) ? $params['wave_strategy_name'] : '';
        $data = $this->filter_elements($params);
        $data['name'] = trim($wave_strategy_name);
        $ret = $this->db->insert('oms_waves_strategy', $data);
        if ($ret !== true) {
            return $this->format_ret(-1, '', '保存出错');
        } else {
            return $this->format_ret(1, '', '保存成功');
        }
    }
    
    //删除波次策略
    function delete_wave_strategy_name($params) {
        $ret = $this->delete_exp('oms_waves_strategy', array('name'=>$params['wave_strategy_name'],'type'=>$params['type']));
        if ($ret !== true) {
            return $this->format_ret(-1, '', '删除出错');
        } else {
            return $this->format_ret(1, '', '删除成功');
        }
    }

    //更新波次策略
    function update_wave_strategy_name($params) {
        $wave_strategy_name = isset($params['wave_strategy_name']) ? trim($params['wave_strategy_name']) : '';
        $data = $this->filter_elements($params);
        $ret = $this->db->update('oms_waves_strategy', $data, array('name' => $wave_strategy_name));
        if ($ret !== true) {
            return $this->format_ret(-1, '', '更新失败');
        } else {
            return $this->format_ret(1, '', '更新成功');
        }
    }

    function is_exist_name($name, $type = 0) {
        $sql = "select * from oms_waves_strategy where name = :name AND type=:type";
        $ret = $this->db->get_row($sql, array('name' => $name, ':type' => $type));
        return $ret;
    }

    function get_waves_strategy($type) {
        $sql = "select * from oms_waves_strategy where type=:type";
        return $this->db->get_all($sql, array(':type' => $type));
    }

    function filter_elements($params) {
        $store_code = isset($params['store_code']) ? $params['store_code'] : '';
        $shop_code = isset($params['shop_code']) ? $params['shop_code'] : '';
        $sale_channel_code = isset($params['sale_channel_code']) ? $params['sale_channel_code'] : '';
        $is_cod = isset($params['is_cod']) ? $params['is_cod'] : '';
        $is_nvoice = isset($params['is_nvoice']) ? $params['is_nvoice'] : '';
        $is_fenxiao = isset($params['is_fenxiao']) ? $params['is_fenxiao'] : '';
        $express_code = isset($params['express_code']) ? $params['express_code'] : '';
        $is_rush = isset($params['is_rush']) ? $params['is_rush'] : '';
        $is_notice_time_start = isset($params['is_notice_time_start']) ? $params['is_notice_time_start'] : '';
        $is_notice_time_end = isset($params['is_notice_time_end']) ? $params['is_notice_time_end'] : '';
        $plan_time_start = isset($params['plan_time_start']) ? $params['plan_time_start'] : '';
        $plan_time_end = isset($params['plan_time_end']) ? $params['plan_time_end'] : '';
        $record_time_start = isset($params['record_time_start']) ? $params['record_time_start'] : '';
        $record_time_end = isset($params['record_time_end']) ? $params['record_time_end'] : '';
        $pay_time_start = isset($params['pay_time_start']) ? $params['pay_time_start'] : '';
        $pay_time_end = isset($params['pay_time_end']) ? $params['pay_time_end'] : '';
        $str = $store_code . $shop_code . $sale_channel_code . $is_cod . $is_nvoice . $is_fenxiao . $express_code . $is_rush . $is_notice_time_start . $is_notice_time_end . $plan_time_start . $plan_time_end . $record_time_start . $record_time_end . $pay_time_start . $pay_time_end;
        $code = md5($str);
        $type = isset($params['type']) ? $params['type'] : 0;
        unset($params['_ati']);
        unset($params['PHPSESSID']);
        unset($params['fastappsid']);
        unset($params['wave_strategy_name']);
        return array('code' => $code, 'condition' => json_encode($params), 'user_code' => CTx()->get_session('user_code'), 'is_sys' => 0, 'type' => $type);
    }

    function create_sku_by_reocrd() {
        $sql = "select sell_record_code,sku,sum(num) as num from oms_sell_record_notice_detail  GROUP BY sell_record_code,sku   ";
        $data = $this->db->get_all($sql);
        $reocrd_data = array();
        foreach ($data as $val) {
            $reocrd_data[$val['sell_record_code']]['sku_all'][] = $val['sku'];
            $reocrd_data[$val['sell_record_code']]['sku_all_num'][$val['sku']] = $val['num'];
        }

        foreach ($reocrd_data as $sell_record_code => $sku_arr) {
            sort($sku_arr['sku_all']);
            ksort($sku_arr['sku_all_num']);
            $sku_str = implode(",", $sku_arr['sku_all']);
            $goods_num = 0;
            foreach($sku_arr['sku_all_num'] as $num){
                $goods_num+=$num;
            }
            $sku_num_str  = implode(",", $sku_arr['sku_all_num']);
            $this->update_exp('oms_sell_record_notice', array('sku_all' => $sku_str,'sku_all_num'=>$sku_num_str,'goods_num'=>$goods_num), " sell_record_code={$sell_record_code}");
        }
    }
    function edit_express_code($filter){
        $sku_list = $filter['sku_list'];
        $store_code = $filter['store_code'];
        $express_code = $filter['express_code_new'];
        $this->begin_trans();
        $sql_value = array();
        $sql_main = "";
        $this->get_filter_data($filter, $sql_main, $sql_value);
        //存在热敏单号,取消云栈
        $param_code = array('opt_confirm_get_cainiao');
        $sys_params = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
        try {
            $sku = $this->arr_to_in_sql_value($sku_list, 'sku', $sql_value);
            $select  = "select rl.sell_record_code ";
            $sql_express = $sql_main . "  AND r2.sku in ({$sku}) ";
//            $sql = "select r1.sell_record_code from oms_sell_record_notice r1 inner join oms_sell_record_detail r2 on r1.sell_record_code=r2.sell_record_code where r2.sku in ({$sku}) and r1.store_code=:store_code group by r1.sell_record_code";
            $idss = $this->db->get_all_col($select.$sql_express,$sql_value);

            //获取单据修改前的快递信息
            $record_sql_value = array();
            $old_sell_record_code_str = $this->arr_to_in_sql_value($idss, 'sell_record_code', $record_sql_value);
            $record_sql = "SELECT * FROM oms_sell_record WHERE sell_record_code IN ({$old_sell_record_code_str})";
            $record_info = $this->db->get_all($record_sql, $record_sql_value);

            $sql_values[':express_code'] = $express_code;
            $code = $this->arr_to_in_sql_value($idss, 'sell_record_code', $sql_values);
            $sql = "update oms_sell_record set express_code = :express_code, express_no = '', is_print_express = 0
            where sell_record_code IN ($code) ";
            $this->query($sql,$sql_values);          
            $sql = "update oms_sell_record_notice set express_code = :express_code, express_no = '', is_print_express = 0
            where sell_record_code IN ($code) ";
            $this->query($sql,$sql_values);
            $this->commit();
            foreach($idss as $v){
                $express_name = oms_tb_val("base_express", 'express_name', array('express_code' => $express_code));
                $remark = "修改配送方式为".$express_name;
                load_model("oms/SellRecordOptModel")->add_action($v, '修改配送方式', $remark);
            }

            //取消云栈
            if ($sys_params['opt_confirm_get_cainiao'] == 1) {
                foreach ($record_info as $record) {
                    if (!empty($record['express_no']) && !empty($record['express_data']) && $record['express_code'] != $express_code) {
                        load_model("oms/SellRecordOptModel")->cancle_cainiao_wlb_waybil_action($record);
                    }
                }
            }
            return array('status' => '1', 'data' => '', 'message' => '修改成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }
    function edit_express_multi_code($filter){
        $sku_list = $filter['sku_list'];
        $store_code = $filter['store_code'];
        $express_code = $filter['express_code_new'];
        $sku_num = $filter['sku_num'];
        $this->begin_trans();
        $sql_values = array();
        $sql_value = array();
        $arr_list = array();
        $result = array();
        $sql_main = "";
        $this->get_filter_data($filter, $sql_main, $sql_value);
        //存在热敏单号,取消云栈
        $param_code = array('opt_confirm_get_cainiao');
        $sys_params = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
        try {
            $sku_list = str_replace("'",'"',$sku_list);
            $sku = json_decode(json_encode(json_decode($sku_list)),true);
            unset($sku['length']);
            $sku_num = str_replace("'",'"',$sku_num);
            $sku_n = json_decode(json_encode(json_decode($sku_num)),true);
            unset($sku['length']);
            foreach($sku as $key=>$v){
                $sku_multi = implode(',',$v);
                $num_multi = implode(',',$sku_n[$key]);
                $select = "select rl.sell_record_code ";
                $sql_express = $sql_main." AND rl.sku_all='{$sku_multi}' and rl.sku_all_num='{$num_multi}' group by sell_record_code";
                $list = $this->db->get_all_col($select.$sql_express,$sql_value);
                $result = array_merge($result,$list);
            }
            //获取单据修改前的快递信息
            $record_sql_value = array();
            $old_sell_record_code_str = $this->arr_to_in_sql_value($result, 'sell_record_code', $record_sql_value);
            $record_sql = "SELECT * FROM oms_sell_record WHERE sell_record_code IN ({$old_sell_record_code_str})";
            $record_info = $this->db->get_all($record_sql, $record_sql_value);

            $sql_values[':express_code'] = $express_code;
            $code = $this->arr_to_in_sql_value($result, 'sell_record_code', $sql_values);
            $sql = "update oms_sell_record set express_code = :express_code, express_no = '', is_print_express = 0
            where sell_record_code IN ($code) ";
            $this->query($sql,$sql_values);          
            $sql = "update oms_sell_record_notice set express_code = :express_code, express_no = '', is_print_express = 0
            where sell_record_code IN ($code) ";
            $this->query($sql,$sql_values);
            $this->commit();
            foreach($result as $v){
                $express_name = oms_tb_val("base_express", 'express_name', array('express_code' => $express_code));
                $remark = "修改配送方式为".$express_name;
                load_model("oms/SellRecordOptModel")->add_action($v, '修改配送方式', $remark);
            }

            //取消云栈
            if ($sys_params['opt_confirm_get_cainiao'] == 1) {
                foreach ($record_info as $record) {
                    if (!empty($record['express_no']) && !empty($record['express_data']) && $record['express_code'] != $express_code) {
                        load_model("oms/SellRecordOptModel")->cancle_cainiao_wlb_waybil_action($record);
                    }
                }
            }
            return array('status' => '1', 'data' => '', 'message' => '修改成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }       
    }


    /**
     * 菜鸟智能
     * @param $sell_record_code_list
     */
    function cainiao_intelligent_delivery($sell_record_code_list) {
        //获取智能店铺
        $param_code = array('cainiao_intelligent_shop','is_more_deliver_package');
        $sys_params = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
        if ($sys_params['is_more_deliver_package'] == 1) {
            return $this->format_ret('-1', '', '开启多包裹发货时，不能使用菜鸟智能发货！');
        }
        if(empty($sys_params['cainiao_intelligent_shop'])){
            return $this->format_ret('-1','','请配置智选物流店铺！');
        }
        $sell_record_code_arr = explode(',', $sell_record_code_list);
        $sql_value = array();
        $sell_record_code_str = $this->arr_to_in_sql_value($sell_record_code_arr, 'sell_record_code', $sql_value);
        $sql = "SELECT * FROM oms_sell_record_notice WHERE sell_record_code IN({$sell_record_code_str})";
        $sell_record_code_info = $this->db->get_all($sql, $sql_value);
        //处理数据
        $receiver_ids = array();
        $params = array();
        //从店铺获取发货信息
       // $shop_code_arr = array_column($sell_record_code_info, 'shop_code');
        $sql_value = array();
   //     $shop_code_str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_value);
        $shop_sql = "SELECT * FROM base_shop WHERE shop_code=:shop_code";
        $sql_value[':shop_code'] = $sys_params['cainiao_intelligent_shop'];
        $shop_info = $this->db->get_row($shop_sql, $sql_value);
     //   $shop_info = load_model('util/ViewUtilModel')->get_map_arr($shop, 'shop_code');

        //从仓库获取发货信息
        //$store_code_arr = array_column($sell_record_code_info, 'store_code');
        $sql_value = array();
      //  $store_code_str = $this->arr_to_in_sql_value($store_code_arr, 'store_code', $sql_value);
        $sql_store = "SELECT * FROM base_store WHERE store_code=:store_code";
        $sql_value[':store_code'] = $shop_info['send_store_code'];
        $store_info = $this->db->get_row($sql_store, $sql_value);
       // $store_info = load_model('util/ViewUtilModel')->get_map_arr($store, 'store_code');
        //优先取店铺的发货信息，如果店铺的联系人，联系电话，发货地址，详细地址任何一个为空则从仓库取值
        $send_info_type = $this->check_arr_params($shop_info, array('contact_person', 'tel', 'province', 'city', 'address'));

        foreach ($sell_record_code_info as &$sellRecord) {
            //商品明细
            $sellRecord['goods_list'] = $this->get_tb_record_detail($sellRecord['sell_record_code']);
            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($sellRecord['sell_record_code']);
            if(empty($record_decrypt_info)){
                       return $this->format_ret(-1, '', '数据解密失败订单：'.$sellRecord['sell_record_code'].'，稍后尝试！');
            }   
            
            $sellRecord = array_merge($sellRecord,$record_decrypt_info);

            // 发货地址
            $sellRecord['sender_province'] = ($send_info_type == 1) ? $store_info['province'] : $shop_info['province'];
            $sellRecord['sender_city'] = ($send_info_type == 1) ? $store_info['city'] : $shop_info['city'];
            $sellRecord['sender_district'] = ($send_info_type == 1) ? $store_info['district'] : $shop_info['district'];
            $sellRecord['sender_addr'] = ($send_info_type == 1) ? $store_info['address'] : $shop_info['address'];
            $sellRecord['sender_street'] = ($send_info_type == 1) ? $store_info['street'] : $shop_info['street'];
            $sellRecord['sender_name'] = ($send_info_type == 1) ? $store_info['contact_person'] : $shop_info['contact_person'];
            $sellRecord['sender_mobile'] = ($send_info_type == 1) ? $store_info['contact_phone'] : $shop_info['tel'];
            $receiver_ids[] = ($send_info_type == 1) ? $store_info['province'] : $shop_info['province'];
            $receiver_ids[] = ($send_info_type == 1) ? $store_info['city'] : $shop_info['city'];
            $receiver_ids[] = ($send_info_type == 1) ? $store_info['district'] : $shop_info['district'];
            $receiver_ids[] = ($send_info_type == 1) ? $store_info['street'] : $shop_info['street'];

            //收货地址
            $sellRecord['receiver_addr'] = addslashes($sellRecord['receiver_addr']);
            $sellRecord['receiver_addr'] = mb_substr($sellRecord['receiver_addr'], 0, 100, 'UTF-8');
            $sellRecord['order_channels_type'] = load_model('oms/DeliverRecordModel')->get_cainiao_sale_channel($sellRecord['sale_channel_code'], $sellRecord['shop_code']);
            $receiver_ids[] = $sellRecord['receiver_province'];
            $receiver_ids[] = $sellRecord['receiver_city'];
            $receiver_ids[] = $sellRecord['receiver_district'];
            $receiver_ids[] = $sellRecord['receiver_street'];
        }

        //将地址编码转换成地址名称
        $receiver_ids = array_unique($receiver_ids);
        $sql_value = array();
        $receiver_ids_str = $this->arr_to_in_sql_value($receiver_ids, 'id', $sql_value);
        $sql = "SELECT id as region_id,name as region_name FROM base_area WHERE id IN({$receiver_ids_str})";
        $data = $this->db->get_all($sql, $sql_value);
        $_receiver_data = array();
        foreach ($data as $_region) {
            $_receiver_data[$_region['region_id']] = $_region['region_name'];
        }
        foreach ($sell_record_code_info as $key => $Record) {
            $sell_record_code_info[$key]['sender_province_name'] = isset($_receiver_data[$Record['sender_province']]) ? $_receiver_data[$Record['sender_province']] : '';
            $sell_record_code_info[$key]['receiver_province_name'] = isset($_receiver_data[$Record['receiver_province']]) ? $_receiver_data[$Record['receiver_province']] : '';
            $sell_record_code_info[$key]['sender_city_name'] = isset($_receiver_data[$Record['sender_city']]) ? $_receiver_data[$Record['sender_city']] : '';
            $sell_record_code_info[$key]['receiver_city_name'] = isset($_receiver_data[$Record['receiver_city']]) ? $_receiver_data[$Record['receiver_city']] : '';
            $sell_record_code_info[$key]['sender_district_name'] = isset($_receiver_data[$Record['sender_district']]) ? $_receiver_data[$Record['sender_district']] : '';
            $sell_record_code_info[$key]['receiver_district_name'] = isset($_receiver_data[$Record['receiver_district']]) ? $_receiver_data[$Record['receiver_district']] : '';
            $sell_record_code_info[$key]['sender_street_name'] = isset($_receiver_data[$Record['sender_street']]) ? $_receiver_data[$Record['sender_street']] : '';
            $sell_record_code_info[$key]['receiver_street_name'] = isset($_receiver_data[$Record['receiver_street']]) ? $_receiver_data[$Record['receiver_street']] : '';
            $params[$Record['sell_record_code']] = $sell_record_code_info[$key];
        }

        //$conf['app_key'] = '1023300032';
        //$conf['app_secret'] = 'sandbox345cf996ba9257bc7bd877770';
        //$conf['session'] = '620070910df1bf235ZZc3194ad5f13910acfd2a97cbba132054718218';
        require_lib('apiclient/TaobaoClient');
        $client = new TaobaoClient($sys_params['cainiao_intelligent_shop']);
        //$client = new TaobaoClient();
        //$client->set_api_config($conf);
        $code = $client->cainiaoIntelligentDelivery($params);
        $success = 0;
        foreach ($code as $sell_record_code => $response) {
            if ($response['status'] != 1) {
                $errs[] = array($sell_record_code => "订单" . $sell_record_code . ": " . $response['message']);
                continue;
            }
            $new_data = $response['data']['smart_delivery_response'];
            $print_data = $new_data['waybill_cloud_print_info']['print_data'];//云打印信息
            $cp_code = $new_data['smart_delivery_cp_info']['cp_code'];//快递公司
            $waybill_code = $new_data['waybill_cloud_print_info']['waybill_code'];//快递单号
            $express_data_arr = array(
                'object_id' => $new_data['object_id'],
                'print_data' => $print_data,
                'waybill_code' => $waybill_code,
                'get_type'=>'cainiao_intelligent_delivery'//获取方式（菜鸟智选物流）
            );
            $express_data = json_encode($express_data_arr);
            $this->begin_trans();
            try {
                $express_info = array('express_no' => $waybill_code, 'express_data' => $express_data, 'express_code' => $cp_code);
                $ret = $this->save_sell_record_express_info($sell_record_code, $express_info);
                if ($ret['status'] != 1) {
                    $this->rollback();
                    $errs[] = array($sell_record_code => "订单" . $sell_record_code . ": " . $ret['message']);
                    continue;
                }
                //添加订单日志
                $express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $cp_code));
                $remark = "菜鸟智选快递单号：{$waybill_code},快递公司：{$express_name}";
                load_model("oms/SellRecordOptModel")->add_action($sell_record_code, '获取菜鸟智选物流', $remark);
                $this->commit();
                $success++;
            } catch (Exception $e) {
                $this->rollback();
                $errs[] = "订单" . $sell_record_code . ": " . $e->getMessage();
            }
        }
        if (count($errs) > 0) {
            $msg = $this->create_fail_file($errs);
            return $this->format_ret(-1, '', '获取成功:' . $success . ', 失败:' . count($errs) . $msg);
        }
        return $this->format_ret(1, '', '获取成功！');
    }

    /**
     * 判断数组中是否有空值
     * @param $arr
     * @param $params
     */
    function check_arr_params($arr, $params) {
        $type = 0;
        foreach ($params as $value) {
            if (empty($arr[$value])) {
                $type = 1;
                break;
            }
        }
        return $type;
    }

    /**
     * 获取明细
     * @param $sell_record_code
     * @return array
     */
    function get_tb_record_detail($sell_record_code) {
        $sql = "select d.sku,sum(num) as num from oms_sell_record_notice_detail d WHERE d.sell_record_code=:sell_record_code GROUP BY sku";
        $sql_value = array();
        $sql_value['sell_record_code'] = $sell_record_code;
        $data = $this->db->get_all($sql, $sql_value);
        $_goods_data = array();
        if (!empty($data)) {
            foreach ($data as $val) {
                $key_arr = array('spec1_name', 'spec2_name', 'goods_name');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
                $goods_name = $sku_info['goods_name'] . "[{$sku_info['spec1_name']},{$sku_info['spec2_name']}]";
                $_goods_data[] = array('goods_name' => $goods_name, 'num' => $val['num']);
            }
        }
        return $_goods_data;
    }

    /**
     * 保存智能发货信息
     * @param $sell_record_code
     * @param $express_info
     */
    function save_sell_record_express_info($sell_record_code, $express_info) {
        //更新通知配货表
        $data = array(
            'express_code' => $express_info['express_code'],
            'express_no' => $express_info['express_no'],
            'express_data' => $express_info['express_data'],
        );
        $ret = $this->update_exp('oms_sell_record_notice', $data, array('sell_record_code' => $sell_record_code));
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '更新智能物流失败！');
        }
        //更新订单表
        $ret = $this->update_exp('oms_sell_record', $data, array('sell_record_code' => $sell_record_code));
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '更新智能物流失败！');
        }
        return $this->format_ret('1', '', '更新智能物流成功！');
    }

    function create_fail_file($error_msg) {
        $fail_top = array('订单号', '错误信息');
        $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
        $message = '';
//        $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }


}
