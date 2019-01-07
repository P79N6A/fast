<?php

return array(
    'delivery_id' => array('title' => '出库单号', 'type' => 1),
    'storage_no' => array('title' => '入库编号', 'type' => 1),
    'insert_time' => array('title' => '创建时间'),
    'delivery_method' => array('title' => '配送模式', 'type' => 1),
    'arrival_time' => array('title' => '预计到货时间'),
    'express' => array('title' => '快递单号', 'type' => 1),
    'warehouse_name' => array('title' => '唯品会仓库', 'type' => 1),
    'shop_code_name' => array('title' => '店铺', 'type' => 1),
    'brand_code_name' => array('title' => '品牌', 'type' => 1),
    'goods_amount' => array('title' => '商品总数量'),
    'is_delivery' => array('title' => '出库单状态', 'type' => 4, 'func' => 'delivery_state'),
    'pick_no' => array('title' => '拣货单号', 'type' => 1),
    'record_code' => array('title' => '批发销货单号', 'type' => 1),
    'goods_code' => array('title' => '商品编码', 'type' => 1),
    'goods_code_name' => array('title' => '商品名称', 'type' => 1),
    'barcode' => array('title' => '商品条码', 'type' => 1),
    'spec1_name' => array('title' => '规格1', 'type' => 1),
    'spec2_name' => array('title' => '规格2', 'type' => 1),
    'goods_num' => array('title' => '商品数量'),
);
