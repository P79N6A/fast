<?php

return array(
    'goods_from_id' =>
    array(
        'title' => '标识符numiid',
    ),
    'goods_name' =>
    array(
        'title' => '商品标题', 'type' => 1
    ),
    'goods_code' =>
    array(
        'title' => '商品编码', 'type' => 1
    ),
    'status' =>
    array(
        'title' => '商品状态',
        'type' => 4, 'func' => 'api_goods_status'
    ),
    'sku_properties_name' =>
    array(
        'title' => '规格描述', 'type' => 1
    ),
    'goods_barcode' =>
    array(
        'title' => 'SKU规格编码',
    ),
    'sys_goods_barcode' =>
    array(
        'title' => '商品条形码',
    )
);
