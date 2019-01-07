<?php

return array(
    // 对外开放的接口，只有配置在这里方法才会对外开放调用。
    'api' => array(
        'market/ValueModel' => array(
            'test' => '',
            'get_valueserver' => '',
            'get_value_by_page' => '',
            'add_shopping_cart' => '',
            'add_server_order' => '',
            'get_shopping_cart' => '',
            'immediate_pay' => '',
            'delete_shopping_cart' => '',
            'get_service_goods' => '',
        ),
        'market/ValueorderModel' => array(
            'get_server_order_list' => '',
            'server_order_ali_pay' => '',
            'server_pay_handle_info' => '',
            'check_pay_status' => '',
            'add_order_remark' => '',
            'get_order_detail' => '',
            'do_order_delete' => '',
            'get_valorder_info' => '',
            'do_delete_order_detail' => '',
            'front_add_detail_action' => '',
        ),
        'market/ValueorderMainModel' => array(
            'get_order_info' => '',
            'get_log_by_page' => '',
            'front_edit_order_action'=>'',
        ),
        'market/ValueorderServerModel' => array(
            'get_kh_server' => '',
            'renew_ali_pay'=>'',
        ),
     'market/OspValueauthKeyModel' => array(
            'get_kh_api_auth' => '',
        ),
    ),
    // 设置别名，可以根据接口别名路由到对应的model方法。
    'alias' => array(
        'market.value.list' => 'market/ValueModel::get_valueserver',
        'get.service.goods' => 'market/ValueModel::get_service_goods',//前端选择查询服务
        'value.server.list' => 'market/ValueModel::get_value_by_page', //平台订购接口数据
        'add.shopping.cart' => 'market/ValueModel::add_shopping_cart', //添加购物车
        'get.shopping.cart' => 'market/ValueModel::get_shopping_cart', //获取购物车数据
        'delete.shopping.cart' => 'market/ValueModel::delete_shopping_cart', //购物车删除
        'add.server.order' => 'market/ValueModel::add_server_order', //立即订购
        'shopping.immediate.pay' => 'market/ValueModel::immediate_pay', //购物车支付
        'server.order.list' => 'market/ValueorderModel::get_server_order_list', //订购订单
        'server.order.ali.pay' => 'market/ValueorderModel::server_order_ali_pay', //支付宝支付
        'server_pay.handle.info' => 'market/ValueorderModel::server_pay_handle_info', //更新支付状态
        'check.pay.status' => 'market/ValueorderModel::check_pay_status', //验证支付状态
        'add.order.remark' => 'market/ValueorderModel::add_order_remark', //增加订单评价
        'get.order.detail' => 'market/ValueorderModel::get_order_detail', //获取订单明细
        'do.order.delete' => 'market/ValueorderModel::do_order_delete', //删除订单
        'add.detail.action' => 'market/ValueorderModel::front_add_detail_action', //前端增加订单明细
        'do.order_detail.delete' => 'market/ValueorderModel::do_delete_order_detail', //删除订单明细
        'get.order_detail.info' => 'market/ValueorderModel::get_valorder_info', //获取订单明细
        'get.order.info' => 'market/ValueorderMainModel::get_order_info', //订单详情，获取订单性
        'get.log.by_page' => 'market/ValueorderMainModel::get_log_by_page', //前端，订单日志
        'edit.order.action' => 'market/ValueorderMainModel::front_edit_order_action', //前端，详情编辑
        'get.kh.server' => 'market/ValueorderServerModel::get_kh_server', //前端，获取客户已订购服务
        'renew.ali.pay' => 'market/ValueorderServerModel::renew_ali_pay', //续费
        'get.kh.apiauth' => 'market/OspValueauthKeyModel::get_kh_api_auth', //获取客户授权API key
    )
);
