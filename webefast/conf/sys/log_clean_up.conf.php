<?php
/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!                      R  E  A  D  M  E                      !!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * *_LOG:     日志操作类型
 * INTERVAL:  清除日志的间隔天数,如果设置成0，将不会执行清除操作
 * DENY:      设置拒绝清除操作的时间
 *            string '2016-09-13 00:00:00|2016-09-14 00:00:00' （一段时间）
 *                   '2016-09-13 00:00:00' （限制当天）
 *            array  array(
 *                       '2016-09-13 00:00:00'
 *                       '2016-09-13 00:00:00|2016-09-14 00:00:00',
 *                       '2016-09-15 00:00:00|2016-09-16 00:00:00'
 *            )
 * ENABLE:    是否开启，是：1，否：0
 * GROUP:     按个数分批删除数据
 * LOG_GROUP: 针对订单日志条数分组
 * 注意:      键名必须大写
 *            如果键名（INTERVAL，DENY）都不设置，将不执行清除操作
 *            如果订单操作日志的INTERVAL要修改，请先清空sys_log_clean_up_log
 *            type='ORDER_LOG'的数据
 */
return array(
    // 订单操作日志配置
    'ORDER_LOG'    => array(
        'ENABLE'    => '1',
        'INTERVAL'  => '90',
        'DENY'      => '',
        'GROUP'     => '2000', // 订单
    ),
    // 标准操作日志配置
    'STANDARD_LOG' => array(
        'ENABLE'   => '1',
        'INTERVAL' => '3',
        'DENY'     => '',
        'GROUP'    => '5000'
    ),
    // 系统操作日志配置
    'SYS_LOG'      => array(
        'ENABLE'   => '1',
        'INTERVAL' => '90',
        'DENY'     => '',
        'GROUP'    => '5000'
    ),
    // 登录操作日志配置
    'LOGIN_LOG'    => array(
        'ENABLE'   => '1',
        'INTERVAL' => '90',
        'DENY'     => '',
        'GROUP'    => '5000'
    ),
    // api_order/api_order_detail配置
    'API_ORDER'    => array(
        'ENABLE'   => '1',
        'INTERVAL' => '30',
        'DENY'     => '',
        'GROUP'    => '2000',
    ),
     // api_taobao_trade/api_taobao_order配置
    'API_TAOBAO'    => array(
        'ENABLE'   => '1',
        'INTERVAL' => '30',
        'DENY'     => '',
        'GROUP'    => '2000',
    ),
    // api_taobao_trade_trace配置
    'API_TAOBAO_TRACE'    => array(
        'ENABLE'   => '1',
        'INTERVAL' => '90',
        'DENY'     => '',
        'GROUP'    => '10000'
    ),
    //wms_oms_trade/wms_oms_order/wms_oms_log 配置
    'WMS_OMS'    => array(
        'ENABLE'   => '1',
        'INTERVAL' => '30',
        'DENY'     => '',
        'GROUP'    => '2000',
    ),
    //goods_inv_api_sync_log 配置
    'GOODS_INV_API'    => array(
        'ENABLE'   => '1',
        'INTERVAL' => '3',
        'DENY'     => '',
        'GROUP'    => '2000',
    ),
     //api_order_send配置
    'API_ORDER_SEND'    => array(
        'ENABLE'   => '1',
        'INTERVAL' => '90',
        'DENY'     => '',
        'GROUP'    => '2000',
    ),
    //oms_waves_record,oms_deliver_record,oms_deliver_record
    'OMS_DELIVER'    => array(
        'ENABLE'   => '1',
        'INTERVAL' => '90',
        'DENY'     => '',
        'GROUP'    => '2000',
    ),

);

