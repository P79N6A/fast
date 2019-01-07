<?php
$spec1_name=load_model("sys/SysParamsModel")->get_val_by_code('goods_spec1');
$spec2_name=load_model("sys/SysParamsModel")->get_val_by_code('goods_spec2');
return array(
    'goods_code' =>
        array(
            'title' => '组装商品编码','type'=>1
        ),
    'goods_name' =>
        array(
            'title' => '组装商品名称','type'=>1
        ),
    'goods_short_name' =>
        array(
            'title' => '组装商品简称','type'=>1
        ),
    'barcode' =>
        array(
            'title' => '组装商品条形码','type'=>1
        ),
    'spec1_name' =>
        array(
            'title' => $spec1_name['goods_spec1'],'type'=>1
        ),
    'spec2_name' =>
        array(
            'title' =>$spec2_name['goods_spec2'],'type'=>1
        ),
    'sku' =>
    array(
        'title' => '系统SKU码','type'=>1
    ),
    'goods_name_diy' =>
        array(
            'title' => '商品名称(组装商品明细)','type'=>1
        ),
    'goods_code_diy'=>
    array(
        'title'=>'商品编码(组装商品明细)','type'=>1
    ),
    'spec1_name_diy' =>
        array(
            'title' => $spec1_name['goods_spec1'].'(组装商品明细)','type'=>1
        ),
    'spec2_name_diy' =>
        array(
            'title' =>$spec2_name['goods_spec2']. '(组装商品明细)','type'=>1
        ),
    'barcode_diy' =>
        array(
            'title' => '商品条形码(组装商品明细)','type'=>1
        ),
    'price_diy' =>
        array(
            'title' => '吊牌价(组装商品明细)','type'=>1
        ),
    'num' =>
        array(
            'title' => '数量(组装商品明细)','type'=>1
        ),

);