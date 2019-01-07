<?php

return array(
    // 对外开放的接口，只有配置在这里方法才会对外开放调用。
    'api' => array(
        'base/PaymentModel' => array(
            'get_by_page' => ''
        ),
        'base/ShopModel' => array(
            'get_by_code' => 'shop_code',
            'api_shop_get' => ''
        ),
        'base/SeasonModel' => array(
            'api_season_update' => '',
            'api_season_get' => ''
        ),
        'base/YearModel' => array(
            'api_year_update' => '',
            'api_year_get'=>''
        ),
        'base/StoreModel' => array(
            'api_store_update' => '',
            'api_store_list_get' => '',
        ),
        'base/ShelfModel' => array(
            'api_shelf_update' => ''
        ),
        'base/ShippingModel' => array(
            'api_get_by_page' => ''
        ),
        'base/ShopApiModel' => array(
            'api_get_apiinfo' => ''
        ),
        'crm/CustomerModel' => array(
            'api_get_customer' => ''
        ),
        'base/CustomModel' => array(
            'api_get_custom' => ''
        ),
        'base/SupplierApiModel' => array(
            'api_archives_get' => ''
        ),
        'prm/BrandApiModel' => array(
            'api_archives_get' => ''
        ),
    ),
    // 设置别名，可以根据接口别名路由到对应的model方法。
    'alias' => array(
        'base.shop.get' => 'base/ShopModel::api_shop_get',
        'base.payment.get_by_page' => 'base/PaymentModel::get_by_page',
        'base.shop.detail' => 'base/ShopModel::get_by_code',
        'base.season.update' => 'base/SeasonModel::api_season_update',
        'base.year.update' => 'base/YearModel::api_year_update',
        'base.store.update' => 'base/StoreModel::api_store_update',
        'base.shelf.update' => 'base/ShelfModel::api_shelf_update',
        'base.sotre.list.get' => 'base/StoreModel::api_store_list_get', //获取仓库列表
        'base.shipping.list.get' => 'base/ShippingModel::api_get_by_page', //获取配送方式列表
        'base.shop.api.get' => 'base/ShopApiModel::api_get_apiinfo',
        'crm.customer.get' => 'crm/CustomerModel::api_get_customer',
        'base.custom.get' => 'base/CustomModel::api_get_custom',
        
        'base.supplier.get' => 'base/SupplierApiModel::api_archives_get',
        'base.brand.get'=>'prm/BrandApiModel::api_archives_get',//商品品牌获取
        'base.year.get' => 'base/YearModel::api_year_get',//年份获取
        'base.season.get' => 'base/SeasonModel::api_season_get',//季节获取
    )
);
