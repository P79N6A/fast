<?php

$u['2104']=array(
    "UPDATE `base_area` SET `name` = '竞秀区' WHERE `id` = '130602000000';"
);
//商品税务编码添加商品编码简称字段
$u['2110']=array(
    "ALTER TABLE `goods_tax` ADD COLUMN `goods_code_short`  varchar(64) NOT NULL DEFAULT '' COMMENT '商品名称简称';"
);
$u['2064']=array(
    "CREATE TABLE `wms_express_action` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(30) NOT NULL DEFAULT '' COMMENT '操作人姓名',
  `user_code` varchar(30) NOT NULL,
  `wms_id` int(11) unsigned NOT NULL COMMENT '映射表主键',
  `action_name` varchar(225) NOT NULL DEFAULT '' COMMENT '操作名称',
  `action_note` text NOT NULL COMMENT '操作描述',
  `lastchanged` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='wms映射操作日志';",
    "CREATE TABLE `wms_express_config` (
  `wms_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wms_config_id` varchar(64) NOT NULL DEFAULT '' COMMENT '对接wms系统',
  `express_code` varchar(128) NOT NULL DEFAULT '' COMMENT '系统快递代码',
  `out_express_code` varchar(128) NOT NULL DEFAULT '' COMMENT '外部系统快递代码',
  `desc` text NOT NULL COMMENT '描述',
  `lastchanged` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`wms_id`),
  UNIQUE KEY `_key` (`wms_config_id`,`out_express_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='wms配送方式映射';",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('11010200', '11010000', 'url', '配送方式映射管理', 'sys/wms_express_config/do_list', '2', '1', '0', '1', '1');",
    "insert into sys_role_action (select role_id , 11010200 as action_id from sys_role_action where action_id = 11000000);"
);
//系统参数表添加发票参数
$u['2111']=array(
    "INSERT INTO `sys_params` ( `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `memo`) VALUES ('default_invoice', 'app', 'S008_015 每张订单默认开票', 'radio', '[\"关闭\",\"开启\"]', '0', '30.00', '1-开启 0-关闭', '默认关闭，开启后，请选择“开票类型”（默认为电子发票）。所有识别为不开票的订单或导入的订单（不含已发货订单），都默认开个人抬头且为指定开票类型的发票');"
);

$u['2057']=array(
    "ALTER TABLE oms_sell_record ADD `wms_request_time` INT(11) DEFAULT 0 COMMENT 'wms上传请求时间，用于订单状态条';",
    //刷数据
    "update oms_sell_record sr,wms_oms_trade wt set sr.wms_request_time=UNIX_TIMESTAMP(wt.upload_request_time) where sr.sell_record_code=wt.record_code and sr.shipping_status>=1 and wt.upload_request_time<>'' and wt.upload_request_time is not null and wt.upload_request_time<>'0000-00-00 00:00:00';"
);