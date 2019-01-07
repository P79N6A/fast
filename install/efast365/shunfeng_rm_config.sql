DROP TABLE IF EXISTS `shunfeng_rm_config`;
CREATE TABLE `shunfeng_rm_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `j_custid` varchar(255) DEFAULT NULL COMMENT '月结卡账号',
  `checkword` varchar(255) DEFAULT NULL COMMENT '校验码',
  `api_url` varchar(255) DEFAULT NULL COMMENT '接口url',
  `express_code` varchar(255) DEFAULT NULL COMMENT '配送方式',
  `express_id` int(11) DEFAULT NULL COMMENT '配送方式id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `j_custid` (`j_custid`,`express_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
