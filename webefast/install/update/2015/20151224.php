<?php
$u = array();
$u['FSF-1909'] = array(
	"CREATE TABLE IF NOT EXISTS `op_policy_express_log` (
	  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `pid` varchar(128) NOT NULL DEFAULT '' COMMENT '策略id',
	  `user_id` varchar(64) DEFAULT '' COMMENT '用户ID',
	  `user_code` varchar(64) DEFAULT '' COMMENT '用户代码',
	  `action_name` varchar(64) DEFAULT '' COMMENT '操作内容',
	  `add_time` datetime DEFAULT NULL COMMENT '操作时间',
	  PRIMARY KEY (`log_id`),
	  KEY `_key` (`pid`) USING BTREE
	) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='赠品策略操作日志';",
);


$u['FSF-1919'] = array(
    "ALTER TABLE `api_jingdong_trade`
ADD COLUMN `return_order`  varchar(10) NULL DEFAULT 0 COMMENT '售后订单标记 0:不是换货订单 1' AFTER `modified`;",
    
    "INSERT INTO `base_question_label` VALUES('','CHANGE_TRADE_JD','京东换货订单',1,1,'京东平台转单，识别到换货单，订单将自动设问',now());
",
);

$u['FSF-1910'] = array(
	"INSERT INTO `sys_action` VALUES ('3020600', '3020000', 'url', '订单审核规则', 'oms/order_check_strategy/do_list', '5', '1', '0', '1','0');",
	"CREATE TABLE `order_check_strategy` (
	  `strategy_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `check_strategy_code` varchar(128) NOT NULL DEFAULT '' COMMENT '规则code',
	  `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：不启用 1：启用',
	  `instructions` varchar(255) NOT NULL DEFAULT '0' COMMENT '规则说明',
	  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
	  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	  PRIMARY KEY (`strategy_id`),
	  UNIQUE KEY `check_strategy_code` (`check_strategy_code`) USING BTREE
	) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='订单审核规则表';",
		"CREATE TABLE `order_check_strategy_detail` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `check_strategy_code` varchar(128) NOT NULL DEFAULT '' COMMENT '规则code',
	  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '商品barcode',
	  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `check_strategy_code_content` (`check_strategy_code`,`content`) USING BTREE
	) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='订单审核规则详细表';",
		"INSERT INTO `order_check_strategy` (`check_strategy_code`, `is_active`, `instructions`, `content`, `lastchanged`) VALUES ('not_auto_confirm_with_goods', '0', '配置商品后，即订单包含此商品不会自动确认', '', '2015-12-15 11:18:02');",
		"INSERT INTO `order_check_strategy` (`check_strategy_code`, `is_active`, `instructions`, `content`, `lastchanged`) VALUES ('not_auto_confirm_with_shop', '0', '配置平台/店铺后，即此平台/店铺下订单不会自动确认', '', '2015-12-17 10:14:47');",
		"INSERT INTO `order_check_strategy` (`check_strategy_code`, `is_active`, `instructions`, `content`, `lastchanged`) VALUES ('auto_confirm_time', '1', '配置时间点后，即系统会按此时间点执行自动确认服务', '', '2015-12-16 17:53:53');",
		"INSERT INTO `order_check_strategy` (`check_strategy_code`, `is_active`, `instructions`, `content`, `lastchanged`) VALUES ('protect_time', '0', '配置保护期后，即系统在此时间内不会自动确认', '', '2015-12-17 10:03:11');",
		"INSERT INTO `order_check_strategy_detail` (`check_strategy_code`, `content`, `lastchanged`) VALUES ('auto_confirm_time', '08:00', '2015-12-17 10:46:19');",
		"INSERT INTO `order_check_strategy_detail` (`check_strategy_code`, `content`, `lastchanged`) VALUES ('auto_confirm_time', '10:00', '2015-12-17 10:46:19');",
		"INSERT INTO `order_check_strategy_detail` (`check_strategy_code`, `content`, `lastchanged`) VALUES ('auto_confirm_time', '14:00', '2015-12-17 10:46:19');",
		"INSERT INTO `order_check_strategy_detail` (`check_strategy_code`, `content`, `lastchanged`) VALUES ('auto_confirm_time', '16:00', '2015-12-17 10:46:19');",
		"INSERT INTO `order_check_strategy_detail` (`check_strategy_code`, `content`, `lastchanged`) VALUES ('protect_time', '120', '2015-12-17 10:46:43');",
                "ALTER TABLE `sys_schedule` ADD COLUMN `plan_exec_data`  text NULL AFTER `plan_exec_time`;",
    
                "ALTER TABLE `api_order`
            MODIFY COLUMN `order_money`  double(10,2) NULL DEFAULT 0.00 COMMENT '平台实付金额' AFTER `seller_flag`,
            MODIFY COLUMN `delivery_money`  double(10,2) NULL DEFAULT 0.00 COMMENT '配送手续费' AFTER `express_money`,
            MODIFY COLUMN `buyer_money`  double(10,2) NULL DEFAULT 0.00 COMMENT '买家已付款，买家真实付款' AFTER `gift_money`;
            ",
    "update sys_schedule set plan_exec_data = '{\"time\":[[\"08:00\",\"10:00\",\"14:00\",\"16:00\"]]}' where code='auto_confirm'",
        
    );

$u['FSF-1913'] = array("INSERT INTO `base_question_label` VALUES('','WMS_SHORT_ORDER','第三方WMS缺货',1,1,'对接第三方仓储、若仓库反馈订单实物缺货，订单将自动设问',now());");

$u['FSF-1915'] = array("INSERT INTO `sys_action` VALUES ('21010400', '21010000', 'url', '销售商品毛利分析', 'rpt/sell_goods_profit_rate/data_analyse', '2', '1', '0', '1','0');");

