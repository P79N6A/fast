<?php

return array(
    // 对外开放的接口，只有配置在这里方法才会对外开放调用。
    'api' => array(
        'products/ProductorderauthModel' => array(
            'get_strategytype' => '',
        ),
        'servicenter/ProductxqissueModel' => array(
            'api_suggest_list_get' => '',
            'api_suggest_detail_get' => '',
        ),
    ),
    // 设置别名，可以根据接口别名路由到对应的model方法。
    'alias' => array(
        'products.strategytype.list' => 'products/ProductorderauthModel::get_strategytype',
        'products.suggest.list.get' => 'servicenter/ProductxqissueModel::api_suggest_list_get',
        'products.suggest.detail.get' => 'servicenter/ProductxqissueModel::api_suggest_detail_get',
    )
);
