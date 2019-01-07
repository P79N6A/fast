<?php
/**
 * 配发货模型类
 * 2014/12/19
 * @author jia.ceng
 */
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
class DeliverModel extends TbModel {
    /**
     * @var string 表名
     */
    protected $table = 'oms_deliver_record';
    
    /**
     * 根据条件查询数据
     * @param $filter
     * @return array
     */
    function get_by_page($filter) {
        $sql_values = array();
    	$sql_join = " left join oms_deliver_record_detail r2 on r1.deliver_record_id = r2.deliver_record_id";
    	$sql_main = "FROM {$this->table} r1 $sql_join WHERE 1 ";
     $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
                $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        //是否打印快递单
        if (isset($filter['is_print_express'])&&$filter['is_print_express'] != 'all') {
            $sql_main .= " AND r1.is_print_express = :is_print_express ";
            $sql_values[':is_print_express'] = $filter['is_print_express'];
        }
        //是否打印订单
        if (isset($filter['is_print_sellrecord'])&&$filter['is_print_sellrecord'] != 'all') {
            $sql_main .= " AND r1.is_print_sellrecord = :is_print_sellrecord ";
            $sql_values[':is_print_sellrecord'] = $filter['is_print_sellrecord'];
        }
        //生产波次
        if (isset($filter['waves_record_id'])&&$filter['waves_record_id'] != 'all') {
            if($filter['waves_record_id'] == '0'){
                $sql_main .= " AND r1.waves_record_id = 0";
            }else{
                $sql_main .= " AND r1.waves_record_id <> 0";
            }
        }
        //发货
        if (isset($filter['is_deliver'])&&$filter['is_deliver'] != 'all') {
            $sql_main .= " AND r1.is_deliver = :is_deliver ";
            $sql_values[':is_deliver'] = $filter['is_deliver'];
        }
        //订单号
        if (!empty($filter['record_code'])) {
            $sql_main .= " AND r1.record_code LIKE :record_code ";
            $sql_values[':record_code'] = '%' . $filter['record_code'] . '%';
        }
        //交易号
        if (!empty($filter['deal_code'])) {
            $sql_main .= " AND r1.deal_code LIKE :deal_code ";
            $sql_values[':deal_code'] = '%' . $filter['deal_code'] . '%';
        }

        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] != '') {
            $sql_main .= " AND r1.express_code in (:express_code) ";
            $sql_values[':express_code'] = explode(',', $filter['express_code']);
        }
        //商品编码
        if (!empty($filter['goods_code'])) {
            $sql_main .= " AND r2.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条形码
        if (!empty($filter['barcode'])) {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if(empty($sku_arr)){
                   $sql_main .= " AND 1=2 ";
            }else{
             $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND r2.sku in({$sku_str}) ";
            }
        }
        //sku种类数
        if (!empty($filter['sku_num'])) {
            $sql_main .= " AND r1.sku_num = :sku_num ";
            $sql_values[':sku_num'] = $filter['sku_num'];
        }
        //商品数量
        if (!empty($filter['num_start'])) {
            $sql_main .= " AND r1.num >= :num_start ";
            $sql_values[':num_start'] = $filter['num_start'];
        }
        if (!empty($filter['num_end'])) {
            $sql_main .= " AND r1.num <= :num_end ";
            $sql_values[':num_end'] = $filter['num_end'];
        }
        //货到付款
        if (!empty($filter['pay_code'])) {
            $sql_main .= " AND r1.pay_code = :pay_code";
            $sql_values[':pay_code'] = $filter['pay_code'];
        }
        //发票
        if (!empty($filter['invoice_type'])) {
            if($filter['invoice_type'] == '1'){
                $sql_main .= " AND r1.invoice_type <> ''";
            }else{
                $sql_main .= " AND r1.invoice_type = ''";
            }
        }
    	//销售平台
    	if (!empty($filter['source'])) {
    	    $sql_main .= " AND r1.source in (:source) ";
            $sql_values[':source'] = explode(',', $filter['source']);
    	}
        //店铺
    	if (!empty($filter['shop_code'])) {
    	    $sql_main .= " AND r1.shop_code in (:shop_code) ";
            $sql_values[':shop_code'] = explode(',', $filter['shop_code']);
    	}
    	//付款时间
    	if (!empty($filter['pay_time_start'])) {
    		$sql_main .= " AND r1.is_pay_time >= :pay_time_start ";
    		$sql_values[':pay_time_start'] = $filter['pay_time_start'].' 00:00:00';
    	}
    	if (!empty($filter['pay_time_end'])) {
    		$sql_main .= " AND r1.is_pay_time <= :pay_time_end ";
    		$sql_values[':pay_time_end'] = $filter['pay_time_end'].' 23:59:59';
    	}
    	//计划发货时间
    	if (!empty($filter['plan_time_start'])) {
    		$sql_main .= " AND r1.is_plan_send_time >= :plan_time_start ";
    		$sql_values[':plan_time_start'] = $filter['plan_time_start'].' 00:00:00';
    	}
    	if (!empty($filter['plan_time_end'])) {
    		$sql_main .= " AND r1.is_plan_send_time <= :plan_time_end ";
    		$sql_values[':plan_time_end'] = $filter['plan_time_end'].' 23:59:59';
    	}
    	//下单时间
    	if (!empty($filter['record_time_start'])) {
    		$sql_main .= " AND r1.record_time >= :record_time_start ";
    		$sql_values[':record_time_start'] = $filter['record_time_start'].' 00:00:00';
    	}
    	if (!empty($filter['record_time_end'])) {
    		$sql_main .= " AND r1.record_time <= :record_time_end ";
    		$sql_values[':record_time_end'] = $filter['record_time_end'].' 23:59:59';
    	}

    	$select = 'r1.*';
        $sql_main .= " ORDER BY r1.deliver_record_id DESC ";
    	$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        
    	foreach($data['data'] as $key => &$value){
            $value['checkbox_html'] = "<input type='checkbox' name='ckb_record_id' value='{$value['deliver_record_id']}'>";

            $url = "?app_act=oms/deliver_record/get_detail_by_pid&deliver_record_id={$value['deliver_record_id']}";
            $value['record_code_href'] = "<a onclick=\"show_detail('{$url}',this)\" param1 = '{$value['deliver_record_id']}' class='bui-grid-button-bar'><span class='bar-btn-add'></span></a>".$value['record_code']."";

            $value['waves_record_id'] = $value['waves_record_id'] == '0'?'0':'1';
            $value['paid_money'] = sprintf("%.2f", $value['paid_money']);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code'=>$value['express_code']));
            $value['barcode'] =  load_model('goods/SkuCModel')->get_barcode($value['sku']);
            
    	}
    	return $this->format_ret(1, $data);
    }
    
    function get_detail_by_pid($deliver_record_id){
        $sql = "select * from oms_deliver_record_detail where deliver_record_id = :deliver_record_id";
        $data = $this->db->get_all($sql, array("deliver_record_id"=>$deliver_record_id));
       // filter_fk_name($data, array('spec1_code|spec1_code','spec2_code|spec2_code','sku|barcode'));
        //todo:new_sku_cache
        foreach($data as $key => &$value){
            $//value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code'=>$value['goods_code']));
            $key_arr = array('spec1_code','spec1_name','spec2_code','spec2_name','gooods_name','barcode');
            $sku_info =  load_model('goods/SkuCModel')->get_sku_info($value['sku'],$keys);
            $value = array_merge($value,$sku_info);
        }
        return $data;
    }
}
