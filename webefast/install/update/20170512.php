<?php

$u['1275'] = array(
    "ALTER TABLE fx_goods_adjust_price_detail ADD UNIQUE KEY `idx_code_sku`(`record_code`, `sku`);",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8080501', '8080500', 'act', '启用', 'fx/goods_adjust_price/do_enable', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8080502', '8080500', 'act', '停用', 'fx/goods_adjust_price/do_disable', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8080503', '8080500', 'act', '删除', 'fx/goods_adjust_price/do_delete', '3', '1', '0', '1', '0');"
);

$u['1188'] = array(
        "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('express_ploy', 'op', '快递策略（新）', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', now(), '开启后，请至运营->策略管理->快递适配策略中进行配置。');",
        "UPDATE `sys_action` SET `status`=1 WHERE `action_id`='3020300';"
);


$u['1291'] = array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'stm_record_lock_obj', 'app', 'S008_011 库存锁定单应用于网络店铺', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '2017-05-05 17:08:58', '启用后，库存锁定单增加锁定对象：网络店铺，且可以选择具体的网络店铺进行库存锁定，且同步至销售平台。');",
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'cainiao_intelligent_delivery', 'app', 'S008_008 匹配菜鸟智能发货', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '2017-05-05 15:58:09', '启用菜鸟智能发货，调用菜鸟接口获取最优的快递配送方式以及快递单号。<a target=\'_blank\' href=\'https://z.cainiao.com/delivery/sdeStrategyConfig.htm\'>菜鸟配置</a>');",
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'cainiao_intelligent_shop', 'app', 'S008_010 智能发货淘宝店铺', 'select', '', '', '0.00', '', '2017-05-05 16:01:43', '请配置智能发货淘宝店铺');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5030210', '5030200', 'act', '导出', 'prm/goods_unique_code/export', '1', '1', '0', '1', '0');"
);


$u['bug_1209'] = array(
    "ALTER table api_taobao_order MODIFY `sku_id` VARCHAR(30)  DEFAULT NULL COMMENT '商品的最小库存单位Sku的id.可以通过taobao.item.sku.get获取详细的Sku信息';"
);
$u['bug_1248'] = array(
    "ALTER TABLE `jxc_info`
MODIFY COLUMN `sku`  varchar(128)  DEFAULT '' COMMENT '商品编码' ;",
);
$u['1289'] = array(
    "ALTER TABLE stm_stock_lock_record ADD COLUMN `sync_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '库存同步策略代码';",
);

$u['1279'] = array(
    "UPDATE oms_sell_return_detail AS rd,api_taobao_fx_refund AS fr SET rd.fx_amount = rd.avg_money, rd.trade_price = rd.avg_money / rd.note_num WHERE rd.deal_code = fr.purchase_order_id AND rd.fx_amount = 0 AND rd.trade_price = 0;",
    "UPDATE oms_sell_return AS sr,(SELECT rd.sell_return_code FROM oms_sell_return_detail AS rd,api_taobao_fx_refund AS fr WHERE rd.deal_code = fr.purchase_order_id GROUP BY rd.deal_code) AS dc SET sr.is_fenxiao = 1 WHERE dc.sell_return_code = sr.sell_return_code;",
    "UPDATE oms_sell_return AS sr,(SELECT sell_return_code,sum(fx_amount) AS sum_money FROM oms_sell_return_detail WHERE fx_amount <> 0 AND trade_price <> 0 GROUP BY sell_return_code) AS rd SET sr.fx_payable_money = rd.sum_money WHERE sr.sell_return_code = rd.sell_return_code AND is_fenxiao = 1;",
    "UPDATE oms_sell_return AS rn,oms_sell_record AS rd SET rn.fenxiao_code = rd.fenxiao_code, rn.fenxiao_name = rd.fenxiao_name WHERE rn.sell_record_code = rd.sell_record_code AND rn.is_fenxiao = 1 AND rn.fenxiao_code = '';",
);

$u['bug_1253'] = array(
    "UPDATE oms_sell_record AS sr,(SELECT buyer_name,fenxiao_code,fenxiao_name,combine_new_order FROM oms_sell_record WHERE is_combine = 1 AND is_combine_new = 0) AS cm SET sr.buyer_name = cm.buyer_name,sr.fenxiao_code = cm.fenxiao_code,sr.fenxiao_name = cm.fenxiao_name WHERE sr.sell_record_code = cm.combine_new_order AND sr.is_combine_new = 1 AND sr.is_fenxiao = 1 AND sr.fenxiao_code = '';"
);

$u['bug_1286'] = array(
	"update sys_params set memo='默认关闭，开启后，商品列表/商品库存查询/销售商品分析显示扩展属性并支持导出' where param_code='property_power';",
);

$u['1287'] = array(
	"ALTER TABLE crm_activity add stock_lock_record varchar(255) DEFAULT '' COMMENT '库存锁定单号';",
);

$u['1314'] = array(
    "UPDATE sys_action SET action_name = '扫描验货(后置打单)' WHERE action_name = '波次单扫描验货';",
);
$u['1298'] = array(
    "DROP TABLE IF EXISTS wms_custom_goods_sku",
    "CREATE TABLE `wms_custom_goods_sku` (
        `custom_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `barcode` VARCHAR(128) NOT NULL COMMENT '商品条形码',
        `sku` VARCHAR(128) NOT NULL COMMENT '商品sku',
        `wms_config_id` int(11)  NOT NULL COMMENT 'wms配置id',
        `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        PRIMARY KEY (`custom_id`),
        UNIQUE KEY `wms_sku` (`barcode`,`wms_config_id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='wms自定义商品sku';",

    "INSERT INTO `sys_params` (`param_code`,`parent_code`,`param_name`,`type`,`form_desc`,`value`,`sort`,`remark`,`memo`) VALUES(
            'wms_split_goods_source',
            'app',
            'S008_012 WMS多货主下发区分商品',
            'radio',
            '[\"关闭\",\"开启\"]',
            '0',
            '10.00',
            '1-开启 0-关闭',
            '启用后，在WMS配置中维护下发商品，仅以维护的商品下发，未维护则不下发');"
    );

$u['1317'] = array("UPDATE `sys_schedule` SET  `loop_time` = '900' WHERE (`code` = 'cli_down_wms_stock');");