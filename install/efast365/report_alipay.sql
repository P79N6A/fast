DROP TABLE IF EXISTS `report_alipay`;
CREATE TABLE `report_alipay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(20) NOT NULL,
  `account_item` varchar(20) NOT NULL,
  `is_account_in` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1是收入 2是支出 3收入分解',
  `account_month_ym` varchar(7) NOT NULL,
  `je` decimal(11,3) DEFAULT '0.000' COMMENT '金额',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `account_item_code` varchar(20) DEFAULT '' COMMENT '会计科目编号',
  PRIMARY KEY (`id`),
  KEY `idx_account_item_code` (`account_item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
