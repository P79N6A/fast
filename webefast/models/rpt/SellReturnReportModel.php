<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');

/**
 * @todo 售后退货数据分析
 */
class SellReturnReportModel extends TbModel {

    private $return_type = array(
        '1' => '仅退款',
        '2' => '退货',
        '3' => '退款退货'
    );

    /**
     * @todo 获取售后退货数据-明细
     */
    function get_sell_return_analysis($filter) {
        $arr = $this->get_by_page_sql($filter);
        $arr['select'] = ' sr.change_fx_amount,sr.sale_channel_code,sr.shop_code,sr.sell_return_code,sr.return_type,sr.return_reason_code,sr.return_buyer_memo,sr.store_code,sr.buyer_name,sr.fenxiao_name,sr.is_fenxiao,sr.return_name,sr.sell_record_code,sr.deal_code,sr.return_express_code,sr.return_express_no,IF(sr.is_fenxiao != 2,sr.seller_express_money,sr.fx_express_money) AS seller_express_money,IF(sr.is_fenxiao != 2,sr.adjust_money,"0.000") AS adjust_money,IF(sr.is_fenxiao != 2,sr.compensate_money,"0.000") AS compensate_money,IF(sr.is_fenxiao != 2,sr.should_refunds,(sr.fx_express_money+sr.fx_payable_money)) AS should_refunds,rd.goods_code,bg.goods_name,gk.spec1_name,gk.spec2_name,gk.barcode,rd.recv_num,IF(sr.is_fenxiao != 2,rd.avg_money,rd.fx_amount) AS avg_money,sr.create_time,sr.receive_time,sr.return_avg_money as avg_money_main,sr.refund_total_fee,rd.cost_price,rd.sku ';
        //增值服务
        $arr['where'] .= load_model('base/SaleChannelModel')->get_values_where('sr.sale_channel_code');
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            if ($filter['sell_record_attr'] == 0) {//普通订单
                $arr['where'] .= ' AND sr.is_fenxiao = 0 ';
            }elseif($filter['sell_record_attr'] == 1){ //淘分销
                $arr['where'] .= ' AND sr.is_fenxiao = 1 ';
            }elseif($filter['sell_record_attr'] == 2){
                $arr['where'] .= ' AND sr.is_fenxiao = 2 ';
            }
        }
        $sql = $arr['from'] . $arr['join'] . $arr['where'] . $arr['group_by'] . $arr['order_by'];

        return $this->get_by_page_1($filter, $sql, $arr['params'], $arr['select']);
    }

    /**
     * @todo 售后退货数据统计-明细
     */
    function get_sell_return_count($filter) {
        $filter['group_by'] = 'sr.sell_return_code';
        $arr = $this->get_by_page_sql($filter);
        //增值服务
        $arr['where'] .= load_model('base/SaleChannelModel')->get_values_where('sr.sale_channel_code');

        $sql = "SELECT sum(rd.recv_num) AS return_num
            , IF(sr.is_fenxiao != 2,sum(sr.should_refunds)/count(sr.sell_return_code),(sum(sr.fx_express_money)+sum(sr.fx_payable_money))/count(sr.sell_return_code)) AS return_money
            , IF(sr.is_fenxiao != 2,sum(sr.compensate_money)/count(sr.sell_return_code),0) AS compensate_money
            , IF(sr.is_fenxiao != 2,sum(sr.seller_express_money)/count(sr.sell_return_code),sum(sr.fx_express_money)/count(sr.sell_return_code)) AS seller_express_money
            , IF(sr.is_fenxiao != 2,sum(sr.adjust_money)/count(sr.sell_return_code),0) AS adjust_money
            , IF(sr.is_fenxiao != 2,sum(rd.avg_money),sum(rd.fx_amount)) AS avg_money ";
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            if ($filter['sell_record_attr'] == 0) {//普通订单
                $arr['where'] .= ' AND sr.is_fenxiao = 0 ';
            }elseif($filter['sell_record_attr'] == 1){ //淘分销
                $arr['where'] .= ' AND sr.is_fenxiao = 1 ';
            }elseif($filter['sell_record_attr'] == 2){
                $arr['where'] .= ' AND sr.is_fenxiao = 2 ';
            }
        }       
        $sql .= $arr['from'] . $arr['join'] . $arr['where'] . $arr['group_by'];
        
        $sql = "SELECT sum(return_num) AS return_num,sum(return_money) AS return_money,sum(compensate_money) AS compensate_money,sum(seller_express_money) AS seller_express_money,sum(adjust_money) AS adjust_money,sum(avg_money) AS avg_money FROM ({$sql}) AS total";

        $data = $this->db->get_row($sql, $arr['params']);
        //退款总金额不需要减去换货金额
//        $ytk1 = bcadd($data['avg_money'], $data['seller_express_money'], 3);
//        $ytk2 = bcadd($data['compensate_money'], $data['adjust_money'], 3);
        $data['return_money'] = sprintf("%.2f", $data['return_money']);
        $data['compensate_money'] = sprintf("%.2f", $data['compensate_money']);
        $data['seller_express_money'] = sprintf("%.2f", $data['seller_express_money']);
        $data['adjust_money'] = sprintf("%.2f", $data['adjust_money']);
        $data['avg_money'] = sprintf("%.2f", $data['avg_money']);

        return $data;
    }

    /**
     * @todo 获取售后退货数据-平台
     */
    function get_sale_channel_analysis($filter) {
        return $this->get_by_page_2($filter, 'sale_channel_code');
    }

    /**
     * @todo 售后退货数据统计-平台
     */
    function get_sale_channel_count($filter) {
        return $this->get_sell_return_count($filter);
    }

    /**
     * @todo 获取售后退货数据-店铺
     */
    function get_shop_analusis($filter) {
        return $this->get_by_page_2($filter, 'shop_code');
    }

    /**
     * @todo 售后退货数据统计-店铺
     */
    function get_shop_count($filter) {
        return $this->get_sell_return_count($filter);
    }

    /**
     * @todo 获取售后退货数据-退货原因
     */
    function get_return_reasons_analysis($filter) {
        return $this->get_by_page_2($filter, 'return_reason_code');
    }

    /**
     * @todo 售后退货数据统计-退货原因
     */
    function get_return_reasons_count($filter) {
        return $this->get_sell_return_count($filter);
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
        foreach ($data['data'] as &$value) {
            if ($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
                $value['return_name'] = $this->name_hidden($value['return_name']);
                $value['buyer_name'] = $this->name_hidden($value['buyer_name']);
                $value['receiver_mobile'] = $this->phone_hidden($value['receiver_mobile']);
                $value['receiver_address'] = $this->address_hidden($value['receiver_address']);
            }
            if ($value['is_fenxiao'] == 1) {
                $value['buyer_name'] = isset($value['fenxiao_name']) && !empty($value['fenxiao_name']) ? $value['fenxiao_name'] : $value['buyer_name'];
            } else if($value['is_fenxiao'] == 2) {
                $value['buyer_name'] = isset($value['fenxiao_name']) && !empty($value['fenxiao_name']) ? $value['fenxiao_name'] : $value['buyer_name'];
                $value['refund_total_fee'] = sprintf('%.3f', $value['should_refunds'] - $value['change_fx_amount']);
            }
            $value['goods_cost_price'] = $value['cost_price']*$value['recv_num'];
           // $value['return_money'] =$value['avg_money_main']+ $value['seller_express_money']+ $value['compensate_money']+ $value['adjust_money'];

            if ($value['return_type'] == 1) {//仅退款单
                $value['receive_time'] = '';
                if (empty($value['sku'])) {//不存在明细
                    $value['goods_cost_price'] = '';
                }
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
    public function get_by_page_2($filter, $filed) {
        $filter['group_by'] = 'sr.sell_return_code';
        $arr = $this->get_by_page_sql($filter);
        //增值服务
        $arr['where'] .= load_model('base/SaleChannelModel')->get_values_where('sr.sale_channel_code');

        $select = " {$filed},sum(return_num) AS return_num,sum(return_money) AS return_money,sum(compensate_money) AS compensate_money,sum(seller_express_money) AS seller_express_money,sum(adjust_money) AS adjust_money";
        $sql = "FROM (SELECT sr.{$filed}
            , sum(rd.recv_num) AS return_num
            , IF(sr.is_fenxiao != 2,sum(sr.should_refunds)/count(sr.sell_return_code),(sum(sr.fx_express_money)+sum(sr.fx_payable_money))/count(sr.sell_return_code)) AS return_money
            , IF(sr.is_fenxiao != 2,sum(sr.compensate_money)/count(sr.sell_return_code),0) AS compensate_money
            , IF(sr.is_fenxiao != 2,sum(sr.seller_express_money)/count(sr.sell_return_code),sum(sr.fx_express_money)/count(sr.sell_return_code)) AS seller_express_money
            , IF(sr.is_fenxiao != 2,sum(sr.adjust_money)/count(sr.sell_return_code),0) AS adjust_money ";
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] != '') {
            if ($filter['sell_record_attr'] == 0) {//普通订单
                $arr['where'] .= ' AND sr.is_fenxiao = 0 ';
            }elseif($filter['sell_record_attr'] == 1){ //淘分销
                $arr['where'] .= ' AND sr.is_fenxiao = 1 ';
            }elseif($filter['sell_record_attr'] == 2){
                $arr['where'] .= ' AND sr.is_fenxiao = 2 ';
            }
        }
        $sql .= $arr['from'] . $arr['join'] . $arr['where'] . $arr['group_by'];
        $sql .=") AS total GROUP BY {$filed}";

        $data = $this->get_page_from_sql($filter, $sql, $arr['params'], $select, true);
        foreach ($data['data'] as $key => &$value) {
            $value['return_money'] = sprintf("%.2f", $value['return_money']);
            $value['compensate_money'] = sprintf("%.2f", $value['compensate_money']);
            $value['seller_express_money'] = sprintf("%.2f", $value['seller_express_money']);
            $value['adjust_money'] = sprintf("%.2f", $value['adjust_money']);
            if (isset($value['sale_channel_code'])) {
                $value['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $value['sale_channel_code']));
            }
            if (isset($value['shop_code'])) {
                $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            }
            if (isset($value['return_reason_code'])) {
                $value['return_reason_name'] = oms_tb_val('base_return_reason', 'return_reason_name', array('return_reason_code' => $value['return_reason_code']));
            }
        }
        return $this->format_ret(1, $data);
    }

    /**
     * @todo 公共查询方法
     */
    public function get_by_page_sql($filter) {
        $select = 'sr.*';
        $from = "FROM oms_sell_return AS sr ";
        $join = ' LEFT JOIN oms_sell_return_detail AS rd ON sr.sell_return_code=rd.sell_return_code
                  LEFT JOIN base_goods AS bg ON rd.goods_code=bg.goods_code 
                  LEFT JOIN goods_sku AS gk ON rd.sku=gk.sku';
        $where = " WHERE ((sr.return_type in(2,3) AND sr.return_shipping_status=1) OR (sr.return_type=1 AND sr.finsih_status=1)) ";
        $params = array();
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }

        //仓库
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $where .= load_model('base/StoreModel')->get_sql_purview_store('sr.store_code', $filter_store_code);
        //店铺
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $where .= load_model('base/ShopModel')->get_sql_purview_shop('sr.shop_code', $filter_shop_code);

        //退单号
        if (isset($filter['sell_return_code']) && $filter['sell_return_code'] !== '') {
            $where .= " AND sr.sell_return_code LIKE :sell_return_code ";
            $params[':sell_return_code'] = '%' . $filter['sell_return_code'] . '%';
        }
        //退单物流单号
        if (isset($filter['return_express_no']) && $filter['return_express_no'] !== '') {
            $where .= " AND sr.return_express_no LIKE :return_express_no ";
            $params[':return_express_no'] = '%' . $filter['return_express_no'] . '%';
        }
        //原单号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
            $where .= " AND sr.sell_record_code LIKE :sell_record_code ";
            $params[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }
        //昵称
        if (isset($filter['buyer_name']) && $filter['buyer_name'] !== '') {

             $customer_code_arr= load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                if(!empty($customer_code_arr)){
                         $customer_code_str = "'".implode("','", $customer_code_arr)."'";
                         $where .= " AND ( sr.customer_code in ({$customer_code_str}) ) ";  
                }else{
                          $where .= " AND sr.buyer_name LIKE :buyer_name ";
                        $params[':buyer_name'] = '%' . $filter['buyer_name'] . '%';
                }            
            
            
        }
        //退货人
        if (isset($filter['return_name']) && $filter['return_name'] !== '') {
   
                $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['return_name'],'name');
                if(!empty($customer_address_id)){
                        $customer_address_id_str = implode(",", $customer_address_id);
                        $where .= " AND ( sr.return_name LIKE :return_name  OR sr.customer_address_id in ({$customer_address_id_str}) ) "; 
                        $params[':return_name'] = '%' . $filter['return_name'] . '%';
                }else{
                $where .= " AND sr.return_name LIKE :return_name ";
              $params[':return_name'] = '%' . $filter['return_name'] . '%';
                }    
            
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $where .= " AND rd.goods_code LIKE :goods_code ";
            $params[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $where .= " AND bg.goods_name LIKE :goods_name ";
            $params[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $where .= " AND gk.barcode LIKE :barcode ";
            $params[':barcode'] = '%' . $filter['barcode'] . '%';
        }
        //原单交易号
        if (isset($filter['deal_code']) && $filter['deal_code'] !== '') {
            $where .= " AND sr.deal_code LIKE :deal_code ";
            $params[':deal_code'] = '%' . $filter['deal_code'] . '%';
        }
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
           $arr = explode(',', $filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $params);
            $where .= " AND sr.sale_channel_code in ({$str}) ";
        }
        //退单类型
        if (isset($filter['return_type']) && $filter['return_type'] !== '') {
            $where .= " AND sr.return_type = :return_type ";
            $params[':return_type'] = $filter['return_type'];
        }
        //退单说明
        if (isset($filter['return_buyer_memo']) && $filter['return_buyer_memo'] !== '') {
            $where .= " AND sr.return_buyer_memo LIKE :return_buyer_memo ";
            $params[':return_buyer_memo'] = '%' . $filter['return_buyer_memo'] . '%';
        }
        //退单原因
        if (isset($filter['return_reason_code']) && $filter['return_reason_code'] !== '') {
                $arr = explode(',', $filter['return_reason_code']);
            $str = $this->arr_to_in_sql_value($arr, 'return_reason_code', $params);
            $where .= " AND sr.return_reason_code in ({$str}) ";
        }
        //入库时间-开始
        if (isset($filter['receive_time_start']) && $filter['receive_time_start'] !== '') {
            $where .= " AND sr.receive_time >= :receive_time_start ";
            $params[':receive_time_start'] = $filter['receive_time_start'];
        }
        //入库时间-结束
        if (isset($filter['receive_time_end']) && $filter['receive_time_end'] !== '') {
            $where .= " AND sr.receive_time <= :receive_time_end ";
            $params[':receive_time_end'] = $filter['receive_time_end'];
        }
        //创建时间-开始
        if (isset($filter['create_time_start']) && $filter['create_time_start'] !== '') {
            $where .= " AND sr.create_time >= :create_time_start ";
            $params[':create_time_start'] = $filter['create_time_start'];
        }
        //创建时间-结束
        if (isset($filter['create_time_end']) && $filter['create_time_end'] !== '') {
            $where .= " AND sr.create_time <= :create_time_end ";
            $params[':create_time_end'] = $filter['create_time_end'];
        }

        $group_by = '';
        if (!empty($filter['group_by'])) {
            $group_by = " GROUP BY " . $filter['group_by'] . " ";
        }

        $order_by = " ORDER BY sr.sell_return_id DESC ";

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
            $value['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $value['sale_channel_code']));
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['return_express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['return_express_code']));
            //原单快递公司
            $value['yl_express_code'] = oms_tb_val("oms_sell_record", 'express_code', array('sell_record_code' => $value['sell_record_code']));
            $value['yl_express_name'] = oms_tb_val("base_express", 'express_name', array('express_code' => $value['yl_express_code']));
            //原单快递单号
            $value['yl_express_no'] = oms_tb_val("oms_sell_record", 'express_no', array('sell_record_code' => $value['sell_record_code']));
            $value['return_reason_name'] = oms_tb_val('base_return_reason', 'return_reason_name', array('return_reason_code' => $value['return_reason_code']));
            $value['return_type'] = $this->return_type[$value['return_type']];
        }
    }

}
