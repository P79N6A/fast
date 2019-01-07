<?php
return array(
    // 选择窗体数据源标识 => array(url: 对应的页面,title: 对应页面选择数据的`名称`字段)
    'sys/user' => array('url' => get_app_url('common/select/user'), 'title' => '选择用户'),
    'sys/org' => array('url' => get_app_url('common/select/org'), 'title' => '选择组织机构'),
    'clients/clientinfo' => array('url' => get_app_url('common/select/clientinfo'), 'title' => '选择客户'),
    'clients/shopinfo' => array('url' => get_app_url('common/select/shopinfo'), 'title' => '选择店铺'),
    'sys/orguser' => array('url' => get_app_url('common/select/orguser'), 'title' => '选择组织机构用户'),
    'basedata/sellchannel' => array('url' => get_app_url('common/select/sellchannel'), 'title' => '选择销售渠道'),
    'products/edition' => array('url' => get_app_url('common/select/edition'), 'title' => '产品版本'),
    'products/productmodule' => array('url' => get_app_url('common/select/productmodule'), 'title' => '产品模块'),
    'market/planprice' => array('url' => get_app_url('common/select/market_price'), 'title' => '报价方案'),
    'market/valueserver' => array('url' => get_app_url('common/select/valueserver'), 'title' => '增值服务'),
    'products/vhostinfo' => array('url' => get_app_url('common/select/vhostinfo'), 'title' => '选择管理主机'),
    'products/vminfo_1' => array('url' => get_app_url('common/select/vminfo&type=1'), 'title' => 'VM列表'),//默认独享
    'products/vminfo_2' => array('url' => get_app_url('common/select/vminfo&type=2'), 'title' => 'VM列表'),//默认共享
    'products/rdsinfo_1' => array('url' => get_app_url('common/select/rdsinfo&type=1'), 'title' => 'RDS列表'),//默认独享
    'products/rdsinfo_2' => array('url' => get_app_url('common/select/rdsinfo&type=2'), 'title' => 'RDS列表'),//默认共享
    'products/dbextinfo' => array('url' => get_app_url('common/select/dbextinfo'), 'title' => '绑定数据库'),//绑定数据库
);