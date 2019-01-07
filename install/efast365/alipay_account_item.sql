DROP TABLE IF EXISTS `alipay_account_item`;
CREATE TABLE `alipay_account_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `account_item` varchar(20) NOT NULL,
  `in_out_flag` tinyint(1) NOT NULL,
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
