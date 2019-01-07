<?php

return array(
    // 对外开放的接口，只有配置在这里方法才会对外开放调用。
    'api' => array(
        'clients/ShopModel' => array(
            'save_shop_info' => '',
        ),
    ),
    // 设置别名，可以根据接口别名路由到对应的model方法。
    'alias' => array(
        'shop.value.list' => 'clients/ShopModel::save_shop_info',
    )
);
