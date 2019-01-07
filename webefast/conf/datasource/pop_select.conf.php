<?php
return array(
    // 选择窗体数据源标识 => array(url: 对应的页面,		title: 对应页面选择数据的`名称`字段)
    'sys/role' => array('url' => get_app_url('common/select/role'), 'title' => '选择角色'),
    'sys/user' => array('url' => get_app_url('common/select/user'), 'title' => '选择用户'),
    'prm/category' => array('url' => get_app_url('common/select/category'), 'title' => '选择分类'),
    'base/shelf' => array('url' => get_app_url('common/select/shelf'), 'title' => '选择库位'),
    'base/brand' => array('url' => get_app_url('common/select/brand'), 'title' => '选择品牌'),
    'oms/sell_return' => array('url' => get_app_url('common/select/sell_return'), 'title' => '选择退单号'),
    'oms/sell_record' => array('url' => get_app_url('common/select/sell_record'), 'title' => '选择订单号'),
    'oms/deal_code' => array('url' => get_app_url('common/select/deal_code'), 'title' => '选择交易号'),
    'base/custom' => array('url'=>get_app_url('common/select/custom'), 'title'=>'选择分销商'),
    'base/shop_custom' => array('url'=>get_app_url('common/select/shop_custom'), 'title'=>'选择分销商'),
    'base/custom_multi' => array('url'=>get_app_url('common/select/custom&is_multi=1'), 'title'=>'选择分销商'),
    'base/issue_goods' => array('url'=>get_app_url('common/select/issue_goods'), 'title'=>'选择系统商品'),
    'base/shop' => array('url'=>get_app_url('common/select/shop'), 'title'=>'选择店铺'),
    'api/jit_po' => array('url'=>get_app_url('common/select/jit_po'), 'title'=>'选择档期'),
);
