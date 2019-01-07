<?php
/**
 * 会员相关业务
 *
 * @author huanghy
 *
 */
require_model('tb/TbModel');
require_lang('crm');
require_lib('util/oms_util', true);

class MonthlyAnaslysisModel extends TbModel {
    function get_month_info($params){
               
        if(empty($params['month']) || empty($params['shop_code'])){
            return $this->format_ret(-1,'','请填写要查询店铺及月份');
        }
        $report = array();
        $all_report_data = $this->get_all_report($params);
        $report['all_report_data'] = $all_report_data;
        
        $report['sell_well_report'] = $this->sell_well_report($params);
        $report['sell_unsalable_report'] = $this->sell_unsalable_report($params);
        
        $report['categorys'] = $this->monthly_category_percentage_report($params);
        $report['brand'] = $this->monthly_brand_percentage_report($params);
        $report['sell_curves'] = $this->get_month_sell_report($params);
        $report['return_curves'] = $this->get_month_return_report($params);
        return $this->format_ret(1,$report);
        
    }
            
    //总体情况
    function get_all_report($params){
        $delivery_time_start = $params['month'].'-01 00:00:00';
        $delivery_time_end = date("Y-m-t", strtotime($params['month']));
        $delivery_time_end = $delivery_time_end.' 23:59:59';
        $sell_info_sql = "select 
                sum(payable_money) as all_sell_money, sum(goods_num) as all_sell_goods_num,count(sell_record_code) as all_sell_order_num from oms_sell_record
                where shipping_status = 4 and shop_code = :shop_code and delivery_time >= :delivery_time_start and delivery_time < :delivery_time_end";
        $sell_info = $this->db->get_row($sell_info_sql, array(":shop_code" => $params['shop_code'],":delivery_time_start" => $delivery_time_start,":delivery_time_end" => $delivery_time_end));
        
        $receive_time_start = $params['month'].'-01 00:00:00';
        $receive_time_end = date("Y-m-t", strtotime($params['month']));
        $receive_time_end = $delivery_time_end.' 23:59:59';
        $receive_info_sql = "select sum(d.avg_money) as all_return_money,sum(d.recv_num) as all_return_goods_num, count(DISTINCT r.sell_return_code) as all_return_order_num
                    from oms_sell_return r
                    LEFT JOIN oms_sell_return_detail d on r.sell_return_code = d.sell_return_code
                    where r.return_shipping_status=1 and r.shop_code=:shop_code and r.receive_time >= :receive_time_start and r.receive_time < :receive_time_end ";
        $return_info = $this->db->get_row($receive_info_sql,array(":shop_code" => $params['shop_code'],":receive_time_start" => $receive_time_start,":receive_time_end" =>$receive_time_end ));
        $sell_info['all_sell_money'] = !empty($sell_info['all_sell_money']) ? $sell_info['all_sell_money'] : 0;
        $sell_info['all_sell_goods_num'] = !empty($sell_info['all_sell_goods_num']) ? $sell_info['all_sell_goods_num'] : 0;
        $sell_info['all_sell_order_num'] = !empty($sell_info['all_sell_order_num']) ? $sell_info['all_sell_order_num'] : 0;
        $return_info['all_return_money'] = !empty($return_info['all_return_money']) ? $return_info['all_return_money'] : 0;
        $return_info['all_return_goods_num'] = !empty($return_info['all_return_goods_num']) ? $return_info['all_return_goods_num'] : 0;
        $return_info['all_return_order_num'] = !empty($return_info['all_return_order_num']) ? $return_info['all_return_order_num'] : 0;
        return array('sell_info' => $sell_info,'return_info' => $return_info);
    }
    
    //  月度畅销商品
    function sell_well_report($params){
        $delivery_time_start = $params['month'].'-01 00:00:00';
        $delivery_time_end = date("Y-m-t", strtotime($params['month']));
        $delivery_time_end = $delivery_time_end.' 23:59:59';
        $sell_well_report_type = $params['sell_well_report'];
        if($sell_well_report_type == 'money'){
            $field = 'd.avg_money';
        } else {
            $field = 'd.num';
        }
        $sql = "SELECT
                    d.sku,
                    sum({$field}) AS all_sell_goods_num
                FROM
                    oms_sell_record r INNER JOIN oms_sell_record_detail d ON r.sell_record_code = d.sell_record_code
                WHERE
                    shipping_status = 4
                AND shop_code = :shop_code
                AND delivery_time >= :delivery_time_start
                AND delivery_time < :delivery_time_end 
                GROUP BY d.sku
                ORDER BY all_sell_goods_num DESC
                LIMIT 5";
        $sell_well = $this->db->get_all($sql,array(":shop_code" => $params['shop_code'],":delivery_time_start" => $delivery_time_start,":delivery_time_end" =>$delivery_time_end ));
        foreach($sell_well as &$record){
            $key_arr = array('goods_name','barcode');
            $sku_info =  load_model('goods/SkuCModel')->get_sku_info($record['sku'],$key_arr);
            $record = array_merge($record,$sku_info);
        }
        return $sell_well;
    }
    //月度滞销商品    
    function sell_unsalable_report($params){
        $delivery_time_start = $params['month'].'-01 00:00:00';
        $delivery_time_end = date("Y-m-t", strtotime($params['month']));
        $delivery_time_end = $delivery_time_end.' 23:59:59';
        $sql = "SELECT
                    inv.sku,
                    sum(inv.stock_num) as stock_num_all
                FROM
                    goods_inv inv
                WHERE
                    inv.sku NOT IN (
                        SELECT
                            sku
                        FROM
                            oms_sell_record r
                        INNER JOIN oms_sell_record_detail rr ON r.sell_record_code = rr.sell_record_code
                        WHERE
                            r.record_time >= :delivery_time_start
                        AND r.record_time <= :delivery_time_end
                        AND r.shop_code = :shop_code
                    ) and inv.sku in (
                        select DISTINCT r1.sku from goods_sku r1
                        left join api_goods_sku r3 on r3.goods_barcode = r1.barcode
                        left join api_goods r4 on r4.goods_from_id = r3.goods_from_id
                        where r4.status = 1
                    )
                group by  inv.sku
                ORDER BY
                    stock_num_all DESC
                LIMIT 5";
        $sell_unsalable = $this->db->get_all($sql,array(":shop_code" => $params['shop_code'],":delivery_time_start" => $delivery_time_start,":delivery_time_end" =>$delivery_time_end ));
        foreach($sell_unsalable as &$record){
            $key_arr = array('goods_name','barcode');
            $sku_info =  load_model('goods/SkuCModel')->get_sku_info($record['sku'],$key_arr);
            $record = array_merge($record,$sku_info);
        }
        return $sell_unsalable;
    }
    
    //活动情况
    function sales_promotion_report($params){
        
    }
    //月度销售分类占比
    function monthly_category_percentage_report($params){
        $delivery_time_start = $params['month'].'-01 00:00:00';
        $delivery_time_end = date("Y-m-t", strtotime($params['month']));
        $delivery_time_end = $delivery_time_end.' 23:59:59';
        $sql = "SELECT
                    bg.category_name,
                    sum(d.num) AS all_sell_goods_num
                FROM
                    oms_sell_record r 
                INNER JOIN oms_sell_record_detail d ON r.sell_record_code = d.sell_record_code
                INNER JOIN base_goods bg ON d.goods_code = bg.goods_code
                WHERE
                    shipping_status = 4
                AND shop_code = :shop_code
                AND delivery_time >= :delivery_time_start
                AND delivery_time < :delivery_time_end 
                GROUP BY bg.category_code
                ORDER BY all_sell_goods_num DESC";
        $categorys = $this->db->get_all($sql,array(":shop_code" => $params['shop_code'],":delivery_time_start" => $delivery_time_start,":delivery_time_end" =>$delivery_time_end ));
        $all_sell_goods_num = 0;
        foreach($categorys as $category){
            $all_sell_goods_num += $category['all_sell_goods_num'];
        }
        $new_categorys = array();
        foreach($categorys as $key => $category){
            $new_categorys[$key]['category_name'] = $category['category_name'];
            $new_categorys[$key]['percentage'] = (float)sprintf("%.2f",$category['all_sell_goods_num']/$all_sell_goods_num*100);
        }
        return $new_categorys;
    }
    //月度销售品牌占比
    function monthly_brand_percentage_report($params){
        $delivery_time_start = $params['month'].'-01 00:00:00';
        $delivery_time_end = date("Y-m-t", strtotime($params['month']));
        $delivery_time_end = $delivery_time_end.' 23:59:59';
        $sql = "SELECT
                    bg.brand_name,
                    sum(d.num) AS all_sell_goods_num
                FROM
                    oms_sell_record r 
                INNER JOIN oms_sell_record_detail d ON r.sell_record_code = d.sell_record_code
                INNER JOIN base_goods bg ON d.goods_code = bg.goods_code
                WHERE
                    shipping_status = 4
                AND shop_code = :shop_code
                AND delivery_time >= :delivery_time_start
                AND delivery_time < :delivery_time_end 
                GROUP BY bg.brand_name
                ORDER BY all_sell_goods_num DESC";
        $categorys = $this->db->get_all($sql,array(":shop_code" => $params['shop_code'],":delivery_time_start" => $delivery_time_start,":delivery_time_end" =>$delivery_time_end ));
        $all_sell_goods_num = 0;
        foreach($categorys as $category){
            $all_sell_goods_num += $category['all_sell_goods_num'];
        }
        $new_categorys = array();
        foreach($categorys as $key => $category){
            $new_categorys[$key]['brand_name'] = $category['brand_name'];
            $new_categorys[$key]['percentage'] = (float)sprintf("%.2f",$category['all_sell_goods_num']/$all_sell_goods_num*100);
        }
        return $new_categorys;
    }
    
    //月度销售曲线
    function get_month_sell_report($params){
        $delivery_month = $params['month'];
        $days = date("t",strtotime($delivery_month));
        $month_sell_curves_type = $params['month_sell_curves'];
        if($month_sell_curves_type == 'money'){
            $field = 'payable_money';
        } else {
            $field = 'goods_num';
        }
        $report_info = array();
        $key = array();
        for($i=1;$i<=$days;$i++){
            $delivery_start = $delivery_month."-".$i." 00:00:00";
            $delivery_end = $delivery_month."-".$i." 23:59:59";
            $report_info[$i] = $this->get_everyday_sell_info($delivery_start,$delivery_end,$field,$params['shop_code']);
            $key[] = $i;
        }
        return array('days' => $key,'report_info' => $report_info);
    }
    
    function get_everyday_sell_info($delivery_start,$delivery_end,$field,$shop_code){
        $sql = "select sum({$field}) from oms_sell_record where delivery_time>=:delivery_time_start and delivery_time <=:delivery_time_end and shipping_status = 4 AND shop_code = :shop_code";
        $money = $this->db->get_value($sql, array(':delivery_time_start' => $delivery_start,':delivery_time_end'=> $delivery_end,':shop_code'=>$shop_code));
        return !empty($money)?(float)sprintf("%.2f",$money):0;
    }
    
    //月度退货曲线
    function get_month_return_report($params){
        $receive_month = $params['month'];
        $days = date("t",strtotime($receive_month));
        $report_info = array();
        $key = array();
        $sql = '';
        $month_return_curves_type = $params['month_return_curves'];
        if($month_return_curves_type == 'money'){
            $field = 'r.refund_total_fee';
            $sql = "select sum(r.refund_total_fee) from oms_sell_return r where receive_time>=:receive_time_start and receive_time <=:receive_time_end and return_shipping_status = 1 and shop_code = :shop_code";
        } else {
            $field = 'rr.recv_num';
            $sql = "select sum(rr.recv_num) from oms_sell_return r
                left join oms_sell_return_detail rr on r.sell_return_code = rr.sell_return_code
                where receive_time>=:receive_time_start and receive_time <=:receive_time_end and return_shipping_status = 1 and r.shop_code = :shop_code";
        }
        for($i=1;$i<=$days;$i++){
            $receive_time_start = $receive_month."-".$i." 00:00:00";
            $receive_time_end = $receive_month."-".$i." 23:59:59";
            $report_info[$i] = $this->get_everyday_return_info($receive_time_start,$receive_time_end,$field,$sql,$params['shop_code']);
            $key[] = $i;
        }
        return array('days' => $key,'report_info' => $report_info);
    }
    
    function get_everyday_return_info($receive_time_start,$receive_time_end,$field,$sql,$shop_code){
        $money = $this->db->get_value($sql, array(':receive_time_start' => $receive_time_start,':receive_time_end'=> $receive_time_end,':shop_code'=>$shop_code));
        return !empty($money)?(float)sprintf("%.2f",$money):0;
    }
}


