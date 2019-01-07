<?php
/**
 * 常用下拉列表列表所需的数据源配置
 * 如果指定了table，则使用改值作为table，否者使用键名作为表名
 */

//'gonghuoshang'=>array('from'=>'model', 'fields'=>'Id,ghsdm')
$_select_datasource_cfg = array(
    'brand' => array('table' => 'base_brand', 'fields' => 'brand_id,brand_name',), //品牌
    'brand_code' => array('table' => 'base_brand', 'fields' => 'brand_code,brand_name',), //品牌
    'season' => array('table' => 'base_season', 'fields' => 'season_id,season_name',),
    'season_code' => array('table' => 'base_season', 'fields' => 'season_code,season_name',),
    'store' => array('table' => 'base_store', 'fields' => 'store_code,store_name',),
    'year' => array('table' => 'base_year', 'fields' => 'year_code,year_name',),
    'record_type' => array('table' => 'base_record_type', 'fields' => 'record_type_code,record_type_name'),
    'shop' => array('table' => 'base_shop', 'fields' => 'shop_code,shop_name'),
    'supplier' => array('table' => 'base_supplier', 'fields' => 'supplier_code,supplier_name',),
	'custom' => array('table' => 'base_custom', 'fields' => 'custom_code,custom_name',),
    'shipping' => array('table' => 'base_express', 'fields' => 'express_code,express_name',),
    'sys_user' => array('table' => 'sys_user', 'fields' => 'user_code,user_name',),
    'express' => array('table' => 'base_express', 'fields' => 'express_code,express_name','params'=>array('status'=>1)),
    'source' => array('table' => 'base_sale_channel', 'fields' => 'sale_channel_code,sale_channel_name'),
    'express_company' => array('table' => 'base_express_company', 'fields' => 'company_code,company_name'),//快递公司
    'problem_type' => array('table' => 'base_question_label', 'fields' => 'question_label_code,question_label_name,remark'),//问题单类型
    'pay_type' => array('table' => 'base_pay_type', 'fields' => 'pay_type_code,pay_type_name'),//支付方式
    'pay_code' => array('table' => 'base_pay_type', 'fields' => 'pay_type_code,pay_type_name'),//支付方式
    'refund_type' => array('table' => 'base_refund_type', 'fields' => 'refund_type_code,refund_type_name'),//退款方式
    'return_reason' => array('table' => 'base_return_reason', 'fields' => 'return_reason_code,return_reason_name'),//退货原因
    'pending_label' => array('table' => 'base_suspend_label', 'fields' => 'suspend_label_code,suspend_label_name'),//挂起原因
    'country' => array('table' => 'base_area','fields'=>'id,name','params'=>array('type'=>1)),//国家
    'order_label' => array('table' => 'base_order_label', 'fields' => 'order_label_code,order_label_name',),//订单标签
    'record_type_code' => array('table' => 'base_record_type', 'fields' => 'record_type_code,record_type_name',),//退货类型
    'spec1' => array('table' => 'base_spec1', 'fields' => 'spec1_code,spec1_name',),//规格1
    'spec2' => array('table' => 'base_spec2', 'fields' => 'spec2_code,spec2_name',),//规格2
);

return $_select_datasource_cfg;