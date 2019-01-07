<?php

/**
 * 配置格式：
 * -------------------------------------------------------------------------------------
 *     ( 表名     |  主键       |  名称字段     |    代码字段 )
 * -------------------------------------------------------------------------------------
 * ***[名称(字段)]是一般对外显示给人看的，比如颜色表(color_id,color_code,color_name...)，那么名称字段就是color_name
 *
 */
return array(
    'category_id' => array('base_category', 'category_id', 'category_name', 'category_code'), // 分类
    'spec1_id' => array('base_spec1', 'spec1_id', 'spec1_name', 'spec1_code'), // 颜色
    'spec2_id' => array('base_spec2', 'spec2_id', 'spec2_name', 'spec2_code'), // 尺码
    'spec1_code' => array('base_spec1', 'spec1_code', 'spec1_name'), // 颜色
    'spec2_code' => array('base_spec2', 'spec2_code', 'spec2_name'), // 尺码
    'record_type' => array('base_record_type', 'record_type_code', 'record_type_name'), //单据类型
    'store' => array('base_store', 'store_code', 'store_name'), //仓库类型
    'custom' => array('base_custom', 'custom_code', 'custom_name'),
    'barcode' => array('goods_barcode', 'sku', 'barcode'),
    'goods_combo' => array('goods_combo', 'goods_code', 'goods_name'),
    'goods_code' => array('base_goods', 'goods_code', 'goods_name'),
    'shop' => array('base_shop', 'shop_code', 'shop_name'), //店铺
    'express_company' => array('base_express_company', 'company_code', 'company_name'), //配送方式
    'refund_type' => array('base_refund_type', 'refund_type_code', 'refund_type_name'), //退款方式
    'supplier' => array('base_supplier', 'supplier_code', 'supplier_name'), //供应商类型
    'category_code' => array('base_brand', 'brand_code', 'brand_name'),
    'category_code' => array('base_category', 'category_code', 'category_name'),
    'season_code' => array('base_season', 'season_code', 'season_name'),
    'brand_code' => array('base_brand', 'brand_code', 'brand_name'),
    'express' => array('base_express', 'express_code', 'express_name'), //配送方式
    'pay' => array('base_pay_type', 'pay_type_code', 'pay_type_name'), //支付方式
    'sale_channel' => array('base_sale_channel', 'sale_channel_code', 'sale_channel_name'), //支付方式
    'source' =>array('base_sale_channel', 'sale_channel_code', 'sale_channel_name'),
);
