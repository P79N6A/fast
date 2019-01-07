<?php

require_lib('util/oms_util', true);
require_model('oms/SellRecordModel', true);

class SellRecordReportModel extends SellRecordModel {

    //销售订单
    function shipped_sell_record($filter) {
        $filter['shipping_status'] = 4;
        $filter['group_by'] = 'r.sell_record_code';

        $arr = $this->get_by_page_sql($filter);
        $arr['select'] = 'r.sell_record_code,r.goods_weigh,r.alipay_no,r.delivery_time,r.record_time,r.pay_time,r.pay_code,r.express_code,r.express_no,r.deal_code_list,r.buyer_name,r.is_fenxiao,r.fenxiao_name';
        $arr['select'] .= ',r.receiver_name,r.receiver_mobile,r.receiver_address,r.receiver_zip_code,r.sale_channel_code,r.shop_code,r.payable_money,r.delivery_money,r.express_money,r.paid_money,r.fx_express_money,r.fx_payable_money';

        //增值服务
        
        if(empty($arr['join'])) {
            $arr['select'] .= ',r.goods_num as goods_count ';
        }else{
            $arr['select'] .= ',sum(rd.num) as goods_count ';
        }    

        $arr['where'] .= load_model('base/SaleChannelModel')->get_values_where('r.sale_channel_code');
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            if ($filter['sell_record_attr'] == 0) {
                $arr['where'] .= ' AND r.is_fenxiao = 0 ';
            }elseif($filter['sell_record_attr'] == 1){//淘分销
                $arr['where'] .= ' AND r.is_fenxiao = 1 ';
            }elseif($filter['sell_record_attr'] == 2){//普通分销
                $arr['where'] .= ' AND r.is_fenxiao = 2 ';
            }
        }
        $sql = $arr['from'] . $arr['join'] . $arr['where'] . $arr['group_by'] . $arr['order_by'];
      
        
        return $this->get_by_page_1($filter, $sql, $arr['params'], $arr['select']);     
    }
    

    //销售订单
    function shipped_sell_record_count($filter) {
        $filter['ref'] = 'do';
        $filter['shipping_status'] = 4;

        // 汇总
        $arr = $this->get_by_page_sql($filter);

        $sql = " select sum(a.goods_money) pay_money
        , sum(a.express_money) express_money
        , sum(a.goods_count) goods_count
        , sum(a.record_count) record_count
        ";

        $sql .= "
        FROM (
            select IF(is_fenxiao<>0,r.fx_payable_money,(r.payable_money - r.express_money - r.delivery_money)) AS goods_money
            , IF(is_fenxiao<>0,r.fx_express_money,r.express_money) as express_money
            , 1 record_count
        ";
        $group = '';
        if (empty($arr['join'])) {
            $sql .= ',r.goods_num as goods_count ';
        } else {
            $sql .= ',sum(rd.num) as goods_count ';
            $group = '  group by r.sell_record_code ';
        }
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            if ($filter['sell_record_attr'] == 0) {
                $arr['where'] .= ' AND r.is_fenxiao = 0 ';
            }elseif($filter['sell_record_attr'] == 1){//淘分销
                $arr['where'] .= ' AND r.is_fenxiao = 1 ';
            }elseif($filter['sell_record_attr'] == 2){//普通分销
                $arr['where'] .= ' AND r.is_fenxiao = 2 ';
            }
        }

        $sql .= $arr['from'] . $arr['join'] . $arr['where'];
        //增值服务
        $sql .= load_model('base/SaleChannelModel')->get_values_where('r.sale_channel_code');
        $sql .= $group;
        $sql .= ") a ";

        $data = $this->db->get_row($sql, $arr['params']);
        $data['paid_money'] = sprintf("%.2f", $data['paid_money']);
        $data['express_money'] = sprintf("%.2f", $data['express_money']);
        return $data;
    }

    //销售渠道
    function shipped_sale_channel($filter) {
        $filter['ref'] = 'do';
        $filter['shipping_status'] = 4;

        $arr = $this->get_by_page_sql($filter);

        $arr['select'] = " a.sale_channel_code
        , sum(a.goods_money) goods_money
        , sum(a.express_money) express_money
        , sum(a.goods_count) goods_count
        , sum(a.record_count) record_count
        ";

        $sql = "
        FROM (
            select r.sale_channel_code
            ,IF(is_fenxiao<>0,r.fx_payable_money,(r.payable_money - r.express_money - r.delivery_money)) AS goods_money
            , IF(is_fenxiao<>0,r.fx_express_money,r.express_money) as express_money
            , 1 record_count
        ";
        $group = '';
         if(empty($arr['join'])) {
            $sql  .= ',r.goods_num as goods_count ';
        }else{
           $sql .= ',sum(rd.num) as goods_count ';
            $group ='  group by r.sell_record_code ';
         } 
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            if ($filter['sell_record_attr'] == 0) {
                $arr['where'] .= ' AND r.is_fenxiao = 0 ';
            }elseif($filter['sell_record_attr'] == 1){//淘分销
                $arr['where'] .= ' AND r.is_fenxiao = 1 ';
            }elseif($filter['sell_record_attr'] == 2){//普通分销
                $arr['where'] .= ' AND r.is_fenxiao = 2 ';
            }
        }
        
        $sql .= $arr['from'] . $arr['join'] . $arr['where'];
        //增值服务
        $sql .= load_model('base/SaleChannelModel')->get_values_where('r.sale_channel_code');
        $sql .= $group;

        
        $sql .= ") a group by a.sale_channel_code";
        
     //echo  $arr['select'];die;

        return $this->get_by_page_2($filter, $sql, $arr['params'], $arr['select']);
    }

    //销售渠道
    function shipped_sale_channel_count($filter) {
        return $this->shipped_sell_record_count($filter);
    }

    //商店
    function shipped_shop($filter) {
        $filter['ref'] = 'do';
        $filter['shipping_status'] = 4;

        $arr = $this->get_by_page_sql($filter);
        //增值服务
        $arr['where'] .= load_model('base/SaleChannelModel')->get_values_where('r.sale_channel_code');

        $arr['select'] = " a.shop_code
        , sum(a.goods_money) goods_money
        , sum(a.express_money) express_money
        , sum(a.goods_count) goods_count
        , sum(a.record_count) record_count
        ";

        $sql = "
        FROM (
            select r.shop_code
            ,IF(is_fenxiao<>0,r.fx_payable_money,(r.payable_money - r.express_money - r.delivery_money)) AS goods_money
            ,IF(is_fenxiao<>0,r.fx_express_money,r.express_money) as express_money
            , 1 record_count
        ";
            $group = '';
         if(empty($arr['join'])) {
          $sql .= ',r.goods_num as goods_count ';
        }else{
            $sql  .= ',sum(rd.num) as goods_count ';
            $group ='  group by r.sell_record_code ';
         }  
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            if ($filter['sell_record_attr'] == 0) {
                $arr['where'] .= ' AND r.is_fenxiao = 0 ';
            }elseif($filter['sell_record_attr'] == 1){//淘分销
                $arr['where'] .= ' AND r.is_fenxiao = 1 ';
            }elseif($filter['sell_record_attr'] == 2){//普通分销
                $arr['where'] .= ' AND r.is_fenxiao = 2 ';
            }
        }
        
        $sql .= $arr['from'] . $arr['join'] . $arr['where'];
        $sql .= $group;

        $sql .= ") a group by a.shop_code";

        return $this->get_by_page_2($filter, $sql, $arr['params'], $arr['select']);
    }

    //商店
    function shipped_shop_count($filter) {
        return $this->shipped_sell_record_count($filter);
    }

    //仓库
    function shipped_store($filter) {
        $filter['ref'] = 'do';
        $filter['shipping_status'] = 4;

        $arr = $this->get_by_page_sql($filter);
        //增值服务
        $arr['where'] .= load_model('base/SaleChannelModel')->get_values_where('r.sale_channel_code');

        $arr['select'] = " a.store_code
        , sum(a.goods_money) goods_money
        , sum(a.express_money) express_money
        , sum(a.goods_count) goods_count
        , sum(a.record_count) record_count
        ";
           $group = '';
                $sql = "
        FROM (
            select r.store_code
            ,IF(is_fenxiao<>0,r.fx_payable_money,(r.payable_money - r.express_money - r.delivery_money)) AS goods_money
            , IF(is_fenxiao<>0,r.fx_express_money,r.express_money) as express_money
            , 1 record_count
        ";
         if(empty($arr['join'])) {
            $sql .= ',r.goods_num as goods_count ';
        }else{
            $sql.= ',sum(rd.num) as goods_count ';
            $group ='  group by r.sell_record_code ';
         }  
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            if ($filter['sell_record_attr'] == 0) {
                $arr['where'] .= ' AND r.is_fenxiao = 0 ';
            }elseif($filter['sell_record_attr'] == 1){//淘分销
                $arr['where'] .= ' AND r.is_fenxiao = 1 ';
            }elseif($filter['sell_record_attr'] == 2){//普通分销
                $arr['where'] .= ' AND r.is_fenxiao = 2 ';
            }
        }
   
        $sql .= $arr['from'] . $arr['join'] . $arr['where'];
         $sql .= $group;

        $sql .= ") a group by a.store_code";

        return $this->get_by_page_2($filter, $sql, $arr['params'], $arr['select']);
    }

    //仓库
    function shipped_store_count($filter) {
        return $this->shipped_sell_record_count($filter);
    }

    //配送方式
    function shipped_express($filter) {
        $filter['ref'] = 'do';
        $filter['shipping_status'] = 4;

        $arr = $this->get_by_page_sql($filter);
        //增值服务
        $arr['where'] .= load_model('base/SaleChannelModel')->get_values_where('r.sale_channel_code');

        $arr['select'] = " a.express_code
        , sum(a.goods_money) goods_money
        , sum(a.express_money) express_money
        , sum(a.goods_count) goods_count
        , sum(a.record_count) record_count
        ";

        $sql = "
        FROM (
            select r.express_code
            ,IF(is_fenxiao<>0,r.fx_payable_money,(r.payable_money - r.express_money - r.delivery_money)) AS goods_money
            ,IF(is_fenxiao<>0,r.fx_express_money,r.express_money) as express_money
            , 1 record_count
        ";
                   $group = '';
         if(empty($arr['join'])) {
            $sql .= ',r.goods_num as goods_count ';
        }else{
            $sql.= ',sum(rd.num) as goods_count ';
            $group ='  group by r.sell_record_code ';
         }
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            if ($filter['sell_record_attr'] == 0) {
                $arr['where'] .= ' AND r.is_fenxiao = 0 ';
            }elseif($filter['sell_record_attr'] == 1){//淘分销
                $arr['where'] .= ' AND r.is_fenxiao = 1 ';
            }elseif($filter['sell_record_attr'] == 2){//普通分销
                $arr['where'] .= ' AND r.is_fenxiao = 2 ';
            }
        }
        $sql .= $arr['from'] . $arr['join'] . $arr['where'];
         $sql .= $group;
        $sql .= ") a group by a.express_code";

        return $this->get_by_page_2($filter, $sql, $arr['params'], $arr['select']);
 
    }

    //配送方式
    function shipped_express_count($filter) {
        return $this->shipped_sell_record_count($filter);
    }

    /**
     * @param $filter
     * @param $sql
     * @param $params
     * @param $select
     * @return array
     */
    public function get_by_page_1($filter, $sql, $params, $select) {
        $data = $this->get_page_from_sql($filter, $sql, $params, $select, true);
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        foreach($data['data'] as &$value){
             if($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view'){
                $value['receiver_name'] = $this->name_hidden($value['receiver_name']);
                $value['buyer_name'] = $this->name_hidden($value['buyer_name']);
                $value['receiver_mobile'] = $this->phone_hidden($value['receiver_mobile']);
                $value['receiver_address'] = $this->address_hidden($value['receiver_address']);
            }
            if ($value['is_fenxiao'] == 1 || $value['is_fenxiao'] == 2) { //分销单取分销商的信息
                $value['buyer_name'] = isset($value['fenxiao_name']) && !empty($value['fenxiao_name']) ? $value['fenxiao_name'] : $value['buyer_name'];
                $value['express_money'] =  isset($value['fx_express_money']) && !empty($value['fx_express_money']) ? $value['fx_express_money'] : 0;
                $value['avg_money_all'] =  isset($value['fx_payable_money']) && !empty($value['fx_payable_money']) ? $value['fx_payable_money'] : 0;
                $value['paid_money'] =  $value['express_money'] + $value['avg_money_all'];
            }
        }
        $this->get_by_page_parse($data['data']);
        return $this->format_ret(1, $data);
    }

    /**
     * @param $filter
     * @param $sql
     * @param $params
     * @param $select
     * @return array
     */
    public function get_by_page_2($filter, $sql, $params, $select) {
        $data = $this->get_page_from_sql($filter, $sql, $params, $select, true);
        foreach ($data['data'] as $key => &$value) {
            $value['goods_money'] = sprintf("%.2f", $value['goods_money']);
            $value['express_money'] = sprintf("%.2f", $value['express_money']);
            if (isset($value['sale_channel_code'])) {
                $value['sale_channel_name'] = $this->get_sale_channel_name_by_code($value['sale_channel_code']);
            }
            if (isset($value['shop_code'])) {
                $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            }
            if (isset($value['store_code'])) {
                $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            }
            if (isset($value['express_code'])) {
                $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            }
        }
        return $this->format_ret(1, $data);
    }

    /**
     * @param $filter
     * @return array
     * @throws Exception
     */
    public function get_by_page_sql($filter) {
        $select = 'r.*';
        $from = "FROM oms_sell_record r ";
        $join = '';
        $where = ' WHERE 1 ';
        $params = array();
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        //时间查询
        if (isset($filter['start_time']) && $filter['start_time'] != '') {
            switch ($filter['time_type']) {
                //下单时间
                case 'record_time':
                    $where .= " AND r.record_time >= :start_time ";
                    $params[':start_time'] = $filter['start_time'];
                    break;
                //付款时间
                case 'pay_time':
                    $where .= " AND r.pay_time >= :start_time ";
                    $params[':start_time'] = $filter['start_time'];
                    break;
                //发货时间
                case 'plan_time':
                    $where .= " AND r.delivery_time >= :start_time ";
                    $params[':start_time'] = $filter['start_time'];
                    break;
            }
        }
        if (isset($filter['end_time']) && $filter['end_time'] != '') {
            switch ($filter['time_type']) {
                //下单时间
                case 'record_time':
                    $where .= " AND r.record_time <= :end_time ";
                    $params[':end_time'] = $filter['end_time'];
                    break;
                //付款时间
                case 'pay_time':
                    $where .= " AND r.pay_time <= :end_time ";
                    $params[':end_time'] = $filter['end_time'];
                    break;
                //发货时间
                case 'plan_time':
                    $where .= " AND r.delivery_time <= :end_time ";
                    $params[':end_time'] = $filter['end_time'];
                    break;
            }
        }
        // 明细条件
        //  $sub_sql = "SELECT rd.sell_record_code FROM oms_sell_record_detail rd WHERE 1";
        $sub_values = array();
        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $where .= load_model('base/StoreModel')->get_sql_purview_store('r.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $where .= load_model('base/ShopModel')->get_sql_purview_shop('r.shop_code', $filter_shop_code);

        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
              $arr = explode(',', $filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $params);
            $where .= " AND r.sale_channel_code in ( " .$str . " ) ";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
           $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $params);
            $where .= " AND r.shop_code in ( " . $str . " ) ";
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] !== '') {
          $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $params);
            $where .= " AND r.store_code in ( " . $str . " ) ";
        }
        //发货状态
        if (isset($filter['shipping_status']) && $filter['shipping_status'] !== '') {
            $where .= " AND r.shipping_status = :shipping_status ";
            $params[':shipping_status'] = $filter['shipping_status'];
        }
        //支付方式
        if (isset($filter['pay_code']) && $filter['pay_code'] !== '') {
                     $arr = explode(',', $filter['pay_code']);
            $str = $this->arr_to_in_sql_value($arr, 'pay_code', $params);
            $where .= " AND r.pay_code in ( " . $str . " ) ";
        }
        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] !== '') {
        $arr = explode(',', $filter['express_code']);
            $str = $this->arr_to_in_sql_value($arr, 'express_code', $params);
            $where .= " AND r.express_code in ( " . $str . " ) ";
        }
        //快递单号
        if (isset($filter['express_no']) && $filter['express_no'] !== '') {
            $where .= " AND r.express_no LIKE :express_no ";
            $params[':express_no'] = '%' . $filter['express_no'] . '%';
        }
        //订单编号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
            $where .= " AND r.sell_record_code LIKE :sell_record_code ";
            $params[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }
        //交易号
        if (isset($filter['deal_code']) && $filter['deal_code'] !== '') {
            $where .= " AND r.deal_code LIKE :deal_code ";
            $params[':deal_code'] = '%' . $filter['deal_code'] . '%';
        }
        //国家
        if (isset($filter['country']) && $filter['country'] !== '') {
            $where .= " AND r.receiver_country = :country ";
            $params[':country'] = $filter['country'];
        }
        //省
        if (isset($filter['province']) && $filter['province'] !== '') {
            $where .= " AND r.receiver_province = :province ";
            $params[':province'] = $filter['province'];
        }
        // 城市
        if (isset($filter['city']) && $filter['city'] !== '') {
            $where .= " AND r.receiver_city = :city ";
            $params[':city'] = $filter['city'];
        }
        // 区域
        if (isset($filter['district']) && $filter['district'] !== '') {
            $where .= " AND r.receiver_district = :district ";
            $params[':district'] = $filter['district'];
        }
        
        // 套餐条形码
        if (isset($filter['combo_barcode']) && $filter['combo_barcode'] !== '') {
            $sku_arr = load_model('prm/GoodsComboOpModel')->get_sku_by_combo_barcode($filter['combo_barcode']);
            if(empty($sku_arr)) {
                $where .= " AND 1 != 1";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr,'combo_sku',$params);
                $join = " INNER JOIN oms_sell_record_detail rd ON r.sell_record_code=rd.sell_record_code ";
                $where .= " AND rd.combo_sku in ({$sku_str}) ";
            }
        }

        //商品编码

        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $join = " INNER JOIN oms_sell_record_detail rd ON r.sell_record_code=rd.sell_record_code ";
            $where .= " AND rd.goods_code = :goods_code ";
            $params[':goods_code'] = $filter['goods_code'];
        }
        //商品条形码

        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $join = " INNER JOIN oms_sell_record_detail rd ON r.sell_record_code=rd.sell_record_code ";
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $where .= " AND 1=2 ";
            } else {
                $sku_str = "'" . implode("','", $sku_arr) . "'";
                $where .= " AND rd.sku in({$sku_str}) ";
            }
        }
        //会员昵称
        if(isset($filter['buyer_name']) && $filter['buyer_name'] != '') {
      
             $customer_code_arr= load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                if(!empty($customer_code_arr)){

                        $customer_code_str = "'".implode("','", $customer_code_arr)."'";
                        $where .= " AND ( r.customer_code in ({$customer_code_str}) ) ";  
                }else{
                       $where .= " AND r.buyer_name LIKE :buyer_name ";
                       $params[':buyer_name'] = '%' . $filter['buyer_name'] . '%';
                }            
            
        }
        //分销商名称
        if(isset($filter['fenxiao_name']) && $filter['fenxiao_name'] != '') {
            $where .= " AND r.fenxiao_name LIKE :fenxiao_name ";
            $params[':fenxiao_name'] = '%' . $filter['fenxiao_name'] . '%';
        }

        $group_by = '';
        if (!empty($filter['group_by'])) {
            $group_by = " group by " . $filter['group_by'] . " ";
        }

        $order_by = " ORDER BY sell_record_id DESC ";
        //var_dump($filter,$select,$where,$params);

        return array(
            'select' => $select,
            'from' => $from,
            'join' => $join,
            'where' => $where,
            'group_by' => $group_by,
            'order_by' => $order_by,
            'params' => $params
        );
    }

    /**
     * @param $data
     */
    public function get_by_page_parse(&$data) {
        foreach ($data as $key => &$value) {
            $value['status_text'] = $this->get_status_text($value);
            $value['paid_money'] = sprintf("%.2f", $value['paid_money']);
            $value['express_money'] = sprintf("%.2f", $value['express_money']);
            $value['sale_channel_name'] = $this->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['pay_name'] = oms_tb_val('base_pay_type', 'pay_type_name', array('pay_type_code' => $value['pay_code']));
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            $value['avg_money_all'] = !empty($value['avg_money_all']) ? sprintf("%.2f", $value['avg_money_all']) : sprintf("%.2f", ($value['payable_money'] - $value['express_money'] - $value['delivery_money']));
            // 'html'=>sprintf("%.2f", $response['record']['payable_money']-$response['record']['express_money']-$response['record']['delivery_money']),
            //整理订单状态
            //$value['status'] = $this->order_status[$value['order_status']];
            //$value['status'] .= '<br/>'.$this->shipping_status[$value['shipping_status']];
            //$value['status'] .= '<br/>'.$this->pay_status[$value['pay_status']];
        }
    }

}
