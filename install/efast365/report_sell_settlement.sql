DROP TABLE IF EXISTS `report_sell_settlement`;
CREATE TABLE `report_sell_settlement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(20) NOT NULL,
  `account_month_ym` varchar(7) NOT NULL,
  `ds_je` decimal(11,3) DEFAULT '0.000' COMMENT '本月已核销的应收金额',
  `ys_je` decimal(11,3) DEFAULT '0.000' COMMENT '期末应收款',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
