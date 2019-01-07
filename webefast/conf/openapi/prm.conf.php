<?php

return array(
    // 对外开放的接口，只有配置在这里方法才会对外开放调用。
    'api' => array(
        'prm/GoodsModel' => array(
            'api_goods_add' => '',
            'api_goods_update' => '',
            'api_goods_barcode_update' => '',
            'api_goods_list' => '',
        ),
        'prm/GoodsBarcodeModel' => array(
            'api_goods_barcode_update' => '',
            'api_goods_barcode_add' => '',
        ),
        'prm/GoodsPriceModel' => array(
            'api_goods_price_update' => '',
        ),
        'prm/GoodsDiyModel' => array(
            'api_goods_diy_update' => '',
        ),
        'prm/Spec1Model' => array(
            'api_goods_spec1_update' => '',
        ),
        'prm/Spec2Model' => array(
            'api_goods_spec2_update' => '',
        ),
        'prm/CategoryModel' => array(
            'api_goods_category_update' => '',
            'api_goods_category_get' => '',
        ),
        'prm/BrandModel' => array(
            'api_goods_brand_update' => '',
        ),
        'prm/InvModel' => array(
            'api_goods_inv_update' => '',
        ),
        'prm/GoodsShelfModel' => array(
            'api_goods_shelf_update' => '',
            'api_goods_shelf_unbind' => '',
            'api_goods_shelf_get' => '',
            'api_shelf_goods_get' => '',
        ),
        'prm/SkuModel' => array(
            'api_goods_sku_update' => '',
        ),
        'api/BaseInvModel' => array(
            'get_shop_inv' => '',
        ),
        'prm/InvApiModel' => array(
            'api_goods_inv_get' => '',
            'api_goods_inv_batch_update' => '',
        ),
        'stm/StockAdjustRecordModel' => array(
            'get_shop_inv' => '',
        ),
        'prm/GoodsComboModel' => array(
            'api_goods_combo_add' => '',
        ),
        'prm/GoodsBarcodeChildModel' => array(
            'api_goods_barcode_child_add' => '',
        ),
        'prm/GoodsUniqueCodeLogModel' => array(
            'api_unique_log_get' => '',//唯一码跟踪查询接口
        ),
    ),
    // 设置别名，可以根据接口别名路由到对应的model方法。
    'alias' => array(
        'prm.goods.add' => 'prm/GoodsModel::api_goods_add',
        'prm.goods.update' => 'prm/GoodsModel::api_goods_update',
        'prm.goods.barcode.add' => 'prm/GoodsBarcodeModel::api_goods_barcode_add',
        'prm.goods.barcode.update' => 'prm/GoodsBarcodeModel::api_goods_barcode_update',
        'prm.goods.price.update' => 'prm/GoodsPriceModel::api_goods_price_update',
        'prm.goods.diy.update' => 'prm/GoodsDiyModel::api_goods_diy_update',
        'prm.goods.spec1.update' => 'prm/Spec1Model::api_goods_spec1_update',
        'prm.goods.spec2.update' => 'prm/Spec2Model::api_goods_spec2_update',
        'prm.goods.category.update' => 'prm/CategoryModel::api_goods_category_update',
        'prm.goods.brand.update' => 'prm/BrandModel::api_goods_brand_update',
        'prm.goods.inv.update' => 'prm/InvModel::api_goods_inv_update',
        'prm.goods.shelf.add' => 'prm/GoodsShelfModel::api_goods_shelf_update',
        'prm.goods.shelf.unbind' => 'prm/GoodsShelfModel::api_goods_shelf_unbind',
        'prm.goods.shelf.get' => 'prm/GoodsShelfModel::api_goods_shelf_get',
        'prm.shelf.goods.get' => 'prm/GoodsShelfModel::api_shelf_goods_get',
        'prm.goods.sku.update' => 'prm/SkuModel::api_goods_sku_update',
        'prm.goods.list' => 'prm/GoodsModel::api_goods_list',
        'prm.goods.shop.inv' => 'api/BaseInvModel::get_shop_inv',
        'prm.goods.combo.add' => 'prm/GoodsComboModel::api_goods_combo_add',
        'prm.goods.barcode.child.add' => 'prm/GoodsBarcodeChildModel::api_goods_barcode_child_add',
        'prm.goods.inv.get' => 'prm/InvApiModel::api_goods_inv_get',
        'prm.goods.inv.batch.update' => 'prm/InvApiModel::api_goods_inv_batch_update',
        'prm.goods.unique.log.get' => 'prm/GoodsUniqueCodeLogModel::api_unique_log_get',
        'prm.goods.category.get' => 'prm/CategoryModel::api_goods_category_get',//获取分类
    )
);
