<?php

$ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
$property_power = $ret_cfg['property_power'];
if ($property_power) {
    $property_data = load_model('prm/GoodsPropertyModel')->get_property_val('property_val_title,property_val');
}
$goods_sub_barcode = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_sub_barcode'));
if ($goods_sub_barcode['goods_sub_barcode'] == 1) {
    $inv_export = array(
        'store_code_name' =>
        array(
            'title' => '仓库名称',
        ),
        'goods_name' =>
        array(
            'title' => '商品名称', 'type' => 1
        ),
        'goods_code' =>
        array(
            'title' => '商品编码', 'type' => 1
        ),
        'spec1_name' =>
        array(
            'title' => '规格1名称', 'type' => 1
        ),
        'spec1_code' =>
        array(
            'title' => '规格1代码',
            'type' => 1
        ),
        'spec2_name' =>
        array(
            'title' => '规格2名称', 'type' => 1
        ),
        'spec2_code' =>
        array(
            'title' => '规格2代码',
            'type' => 1
        ),
        'barcode' =>
        array(
            'title' => '商品条形码', 'type' => 1
        ),
        'goods_sub_barcode' =>
        array(
            'title' => '商品子条码', 'type' => 1
        ),
        'category_name' =>
        array(
            'title' => '商品分类',
        ),
        'season_name' =>
        array(
            'title' => '季节',
        ),
        'year_name' =>
        array(
            'title' => '年份',
        ),
        'brand_name' =>
        array(
            'title' => '品牌',
        ),
        'sell_price' =>
        array(
            'title' => '吊牌价',
        ),
        'cost_price' =>
        array(
            'title' => '成本价',
        ),
        'effec_num' =>
        array(
            'title' => '可用库存',
        ),
        'lock_num' =>
        array(
            'title' => '实物锁定',
        ),
        'stock_num' =>
        array(
            'title' => '实物库存',
        ),
        'road_num' =>
        array(
            'title' => '在途库存',
        ),
        'out_num' =>
        array(
            'title' => '缺货库存',
        ),
        'safe_num' =>
        array(
            'title' => '安全库存',
        ),
        'goods_self_name' =>
        array(
            'title' => '商品库位',
        ),
        'remark' =>
        array(
            'title' => '条码备注', 'type' => 1
        ),
    );
} else {
    $inv_export = array(
        'store_code_name' =>
        array(
            'title' => '仓库名称',
        ),
        'goods_name' =>
        array(
            'title' => '商品名称', 'type' => 1
        ),
        'goods_code' =>
        array(
            'title' => '商品编码', 'type' => 1
        ),
        'spec1_name' =>
        array(
            'title' => '规格1名称', 'type' => 1
        ),
        'spec1_code' =>
        array(
            'title' => '规格1代码',
            'type' => 1
        ),
        'spec2_name' =>
        array(
            'title' => '规格2名称', 'type' => 1
        ),
        'spec2_code' =>
        array(
            'title' => '规格2代码',
            'type' => 1
        ),
        'barcode' =>
        array(
            'title' => '商品条形码', 'type' => 1
        ),
        'category_name' =>
        array(
            'title' => '商品分类',
        ),
        'season_name' =>
        array(
            'title' => '季节',
        ),
        'year_name' =>
        array(
            'title' => '年份',
        ),
        'brand_name' =>
        array(
            'title' => '品牌',
        ),
        'sell_price' =>
        array(
            'title' => '吊牌价',
        ),
        'cost_price' =>
        array(
            'title' => '成本价',
        ),
        'effec_num' =>
        array(
            'title' => '可用库存',
        ),
        'lock_num' =>
        array(
            'title' => '实物锁定',
        ),
        'stock_num' =>
        array(
            'title' => '实物库存',
        ),
        'road_num' =>
        array(
            'title' => '在途库存',
        ),
        'out_num' =>
        array(
            'title' => '缺货库存',
        ),
        'safe_num' =>
        array(
            'title' => '安全库存',
        ),
        'goods_self_name' =>
        array(
            'title' => '商品库位',
        ),
        'remark' =>
        array(
            'title' => '条码备注', 'type' => 1
        ),
    );
}
if ($property_power) {
    foreach ($property_data as $val) {
        $inv_export[$val['property_val']]['title'] = $val['property_val_title'];
    }
}
return $inv_export;
